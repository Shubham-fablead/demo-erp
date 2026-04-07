<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SalesGSTR9Controller extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/sales_gsrt9_excel_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-9 Sales template not found at storage/app/templates/sales_gsrt9_excel_template.xlsx');
        }
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getSheetByName('Sheet1');

        // === 3) Fetch Sales Orders ===
        $orders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->select(
                'o.id',
                'o.order_number',
                'o.created_at',
                'o.total_amount',
                'o.tax_id',
                'o.payment_status',
                'u.name as customer_name',
                'u.gst_number as customer_gstin',
                'u.pan_number as customer_pan'
            )
            ->whereBetween('o.created_at', [$from, $to])
            ->where('o.payment_status', '=', 'completed')
            ->get();

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
                            'amount' => ($o->total_amount * $rate) / 100,
                        ];
                    })->toArray();
                }
            }

            return (object)[
                'order_id'      => $o->id,
                'invoice_id'    => $o->order_number,
                'created_at'    => $o->created_at,
                'customer_name' => $o->customer_name,
                'customer_gstin'=> $o->customer_gstin,
                'customer_pan'  => $o->customer_pan,
                'taxable_value' => $o->total_amount,
                'taxes'         => json_encode($taxes),
                'grand_total'   => $o->total_amount + collect($taxes)->sum('amount'),
                'payment_status'=> $o->payment_status,
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
                'ci.grand_total',
                'ci.status',
                'u.name as customer_name',
                'u.gst_number as customer_gstin',
                'u.pan_number as customer_pan'
            )
            ->whereBetween('ci.created_at', [$from, $to])
            ->where('ci.status', '=', 'completed')
            ->get();

        $customInvoices = $customInvoices->map(function ($ci) {
            return (object)[
                'order_id'      => $ci->id,
                'invoice_id'    => $ci->invoice_number,
                'created_at'    => $ci->created_at,
                'customer_name' => $ci->customer_name,
                'customer_gstin'=> $ci->customer_gstin,
                'customer_pan'  => $ci->customer_pan,
                'taxable_value' => $ci->taxable_value,
                'taxes'         => $ci->taxes ?? '[]',
                'grand_total'   => $ci->grand_total,
                'payment_status'=> $ci->status,
            ];
        });

        // === 5) Merge Sales Data ===
        $sales = $orders->merge($customInvoices);

        // === 6) Fill Excel ===
        $this->fillSummary($sheet, $sales);
        $this->fillDetails($sheet, $sales);

        // === 7) Stream Download ===
        $fileName = 'GSTR9_Sales_' . now()->format('Ymd_His') . '.xlsx';
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

    private function fillSummary(?Worksheet $sheet, $sales): void
    {
        if (!$sheet) return;

        $totalCustomers = $sales->pluck('customer_name')->filter()->unique()->count();
        $totalGSTINs    = $sales->pluck('customer_gstin')->filter()->unique()->count();
        $totalOrders    = $sales->pluck('order_id')->count();
        $totalInvoices  = $sales->pluck('invoice_id')->count();
        $totalGrand     = $sales->sum('grand_total');
        $totalTaxable   = $sales->sum('taxable_value');

        $totalCGST = $totalSGST = $totalIGST = 0;
        foreach ($sales as $s) {
            $taxes = json_decode($s->taxes, true) ?? [];
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                if ($name === 'CGST') $totalCGST += $amount;
                if ($name === 'SGST') $totalSGST += $amount;
                if ($name === 'IGST') $totalIGST += $amount;
            }
        }

        // Fill Row 3 summary values
        $sheet->setCellValue("A3", $totalCustomers);
        $sheet->setCellValue("B3", $totalGSTINs);
        $sheet->setCellValue("D3", $totalOrders);
        $sheet->setCellValue("E3", $totalInvoices);
        $sheet->setCellValue("I3", $totalCGST);
        $sheet->setCellValue("J3", $totalSGST);
        $sheet->setCellValue("K3", $totalIGST);
        $sheet->setCellValue("L3", $totalTaxable);
        $sheet->setCellValue("M3", $totalGrand);
    }

    private function fillDetails(?Worksheet $sheet, $sales): void
    {
        if (!$sheet) return;

        $row = 5; // Data starts at row 5
        foreach ($sales as $s) {
            $taxes = json_decode($s->taxes, true) ?? [];
            $cgst = $sgst = $igst = $rate = 0;
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                $rate   = $t['rate'] ?? $rate;
                if ($name === 'CGST') $cgst += $amount;
                if ($name === 'SGST') $sgst += $amount;
                if ($name === 'IGST') $igst += $amount;
            }

            $sheet->setCellValue("A{$row}", $s->customer_name);
            $sheet->setCellValue("B{$row}", $s->customer_gstin);
            $sheet->setCellValue("C{$row}", $s->customer_pan);
            $sheet->setCellValue("D{$row}", $s->order_id);
            $sheet->setCellValue("E{$row}", $s->invoice_id);
            $sheet->setCellValue("F{$row}", Carbon::parse($s->created_at)->format('d-M-Y'));
            $sheet->setCellValue("G{$row}", ""); // Product Name not directly stored in orders
            $sheet->setCellValue("H{$row}", $rate);
            $sheet->setCellValue("I{$row}", $cgst);
            $sheet->setCellValue("J{$row}", $sgst);
            $sheet->setCellValue("K{$row}", $igst);
            $sheet->setCellValue("L{$row}", $s->taxable_value);
            $sheet->setCellValue("M{$row}", $s->grand_total);
            $sheet->setCellValue("N{$row}", $s->payment_status);

            $row++;
        }
    }
}
