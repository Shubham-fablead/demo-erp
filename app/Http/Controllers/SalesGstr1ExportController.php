<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SalesGstr1ExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/Sales_Gsrt_1_excel_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-1 template not found at storage/app/templates/Sales_Gsrt_1_excel_template.xlsx');
        }
        $spreadsheet = IOFactory::load($template);

        // === 3) Fetch Orders ===
        $orders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->select(
                'o.id',
                'o.order_number as invoice_number',
                'o.created_at',
                'o.total_amount as taxable_value',
                'o.tax_id',
                'u.name as customer_name',
                'u.gst_number as customer_gstin',
                'u.pan_number as customer_pan'
            )
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.payment_status', '=', 'completed')
            ->get();

        // Normalize orders
        $orders = $orders->map(function ($o) {
            $taxes = [];
            if (!empty($o->tax_id)) {
                $ids = json_decode($o->tax_id, true);
                if (is_array($ids)) {
                    $taxes = DB::table('taxes')->whereIn('id', $ids)->get()->map(function ($t) use ($o) {
                        $rate = property_exists($t, 'rate') ? $t->rate : 0;
                        return [
                            'name'   => $t->tax_name ?? '',
                            'rate'   => $rate,
                            'amount' => ($o->taxable_value * $rate) / 100,
                        ];
                    })->toArray();
                }
            }

            // Replace default/empty customer
            $customerName = $o->customer_name;
            if (!$customerName || strtolower($customerName) === 'default customer') {
                $customerName = 'Unregistered Customer';
            }

            return (object)[
                'invoice_number'  => $o->invoice_number,
                'created_at'      => $o->created_at,
                'grand_total'     => $o->taxable_value + collect($taxes)->sum('amount'),
                'taxable_value'   => $o->taxable_value,
                'taxes'           => json_encode($taxes),
                'products'        => '[]',
                'customer_name'   => $customerName,
                'customer_gstin'  => $o->customer_gstin,
                'customer_pan'    => $o->customer_pan,
            ];
        });

        // === 4) Fetch Custom Invoices ===
        $customInvoices = DB::table('custom_invoice as ci')
            ->leftJoin('users as u', 'u.id', '=', 'ci.customer_id')
            ->select(
                'ci.id',
                'ci.invoice_number',
                'ci.created_at',
                'ci.total_amount as taxable_value',
                'ci.taxes',
                'ci.products',
                'ci.grand_total',
                'u.name as customer_name',
                'u.gst_number as customer_gstin',
                'u.pan_number as customer_pan'
            )
            ->whereBetween('ci.created_at', [$from, $to])
            ->where('ci.status', '=', 'completed')
            ->get();

        // Normalize custom invoices
        $customInvoices = $customInvoices->map(function ($ci) {
            $customerName = $ci->customer_name;
            if (!$customerName || strtolower($customerName) === 'default customer') {
                $customerName = 'Unregistered Customer';
            }

            return (object)[
                'invoice_number'  => $ci->invoice_number,
                'created_at'      => $ci->created_at,
                'grand_total'     => $ci->grand_total,
                'taxable_value'   => $ci->taxable_value,
                'taxes'           => $ci->taxes ?? '[]',
                'products'        => $ci->products ?? '[]',
                'customer_name'   => $customerName,
                'customer_gstin'  => $ci->customer_gstin,
                'customer_pan'    => $ci->customer_pan,
            ];
        });

        // === 5) Merge datasets ===
        $invoices = $orders->merge($customInvoices);

        // === 6) Fill B2B Sheet ===
        $this->fillB2B($spreadsheet->getSheetByName('Sheet1'), $invoices);

        // === 7) Update Summary ===
        $this->fillSummary($spreadsheet->getSheetByName('Sheet1'), $invoices);

        // === 8) Stream Download ===
        $fileName = 'GSTR1_Sales_' . now()->format('Ymd_His') . '.xlsx';
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    private function resolveDateRange(Request $request): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        if (!$from || !$to) {
            $today = now()->timezone('Asia/Kolkata');
            $fyStart = $today->month >= 4
                ? $today->copy()->startOfYear()->month(4)->day(1)
                : $today->copy()->subYear()->startOfYear()->month(4)->day(1);
            $fyEnd = $fyStart->copy()->addYear()->subDay();
            $from = $fyStart->toDateString();
            $to   = $fyEnd->toDateString();
        }
        return [$from, $to];
    }

    private function fillB2B(?Worksheet $sheet, $invoices): void
    {
        if (!$sheet) return;
        $row = 5; // Data starts at row 5

        foreach ($invoices as $inv) {
            $products = json_decode($inv->products, true) ?? [];
            $taxes    = json_decode($inv->taxes, true) ?? [];

            $taxableValue = $inv->taxable_value ?? collect($products)->sum('total');
            $igst = $cgst = $sgst = $cess = 0;
            $rate = '';

            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                $rate   = $t['rate'] ?? $rate;

                if ($name === 'IGST') $igst += $amount;
                if ($name === 'CGST') $cgst += $amount;
                if ($name === 'SGST') $sgst += $amount;
                if ($name === 'CESS') $cess += $amount;
            }

            $sheet->setCellValue("A{$row}", $inv->customer_gstin);
            $sheet->setCellValue("B{$row}", $inv->customer_name);

            // Invoice number as text to avoid scientific notation
            $sheet->setCellValueExplicit(
                "C{$row}",
                (string) $inv->invoice_number,
                DataType::TYPE_STRING
            );

            $sheet->setCellValue("D{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(
                Carbon::parse($inv->created_at)->format('Y-m-d')
            ));
            $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode('DD-MMM-YYYY');

            $sheet->setCellValue("E{$row}", round($inv->grand_total, 2));
            $sheet->setCellValue("F{$row}", '');   // Place of Supply
            $sheet->setCellValue("G{$row}", 'N');  // Reverse charge
            $sheet->setCellValue("H{$row}", '');   // Applicable % of Tax Rate
            $sheet->setCellValue("I{$row}", 'Regular');
            $sheet->setCellValue("J{$row}", '');   // E-commerce GSTIN
            $sheet->setCellValue("K{$row}", $rate ?: 0);
            $sheet->setCellValue("L{$row}", $taxableValue);
            $sheet->setCellValue("M{$row}", $cess);

            $row++;
        }
    }

    private function fillSummary(?Worksheet $sheet, $invoices): void
    {
        if (!$sheet) return;

        $totalRecipients = collect($invoices)->pluck('customer_gstin')->filter()->unique()->count();
        $totalInvoices   = $invoices->count();
        $totalInvoiceVal = collect($invoices)->sum('grand_total');

        $totalTaxable = 0;
        $totalCess    = 0;
        foreach ($invoices as $inv) {
            $products = json_decode($inv->products, true) ?? [];
            $taxes    = json_decode($inv->taxes, true) ?? [];
            $totalTaxable += $inv->taxable_value ?? collect($products)->sum('total');
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                if ($name === 'CESS') {
                    $totalCess += (float)($t['amount'] ?? 0);
                }
            }
        }

        $sheet->setCellValue("A3", $totalRecipients);
        $sheet->setCellValue("C3", $totalInvoices);
        $sheet->setCellValue("E3", $totalInvoiceVal);
        $sheet->setCellValue("L3", $totalTaxable);
        $sheet->setCellValue("M3", $totalCess);
    }
}
