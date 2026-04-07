<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class SalesGSTR9CController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/Sales_GSTR_9C_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-9C template not found at storage/app/templates/Sales_GSTR_9C_template.xlsx');
        }
        $spreadsheet = IOFactory::load($template);

        // === 3) Fetch Sales Data ===
        $orders = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->select('o.id', 'o.order_number', 'o.created_at', 'o.total_amount', 'o.tax_id')
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
                'created_at'    => $o->created_at,
                'taxable_value' => $o->total_amount,
                'taxes'         => json_encode($taxes),
                'grand_total'   => $o->total_amount + collect($taxes)->sum('amount'),
            ];
        });

        $customInvoices = DB::table('custom_invoice as ci')
            ->select('ci.id', 'ci.invoice_number', 'ci.created_at', 'ci.total_amount as taxable_value', 'ci.taxes', 'ci.grand_total')
            ->whereBetween('ci.created_at', [$from, $to])
            ->where('ci.status', '=', 'completed')
            ->get();

        $customInvoices = $customInvoices->map(function ($ci) {
            return (object)[
                'created_at'    => $ci->created_at,
                'taxable_value' => $ci->taxable_value,
                'taxes'         => $ci->taxes ?? '[]',
                'grand_total'   => $ci->grand_total,
            ];
        });

        $sales = $orders->merge($customInvoices);

        // === 4) Fetch Purchase Data ===
        $purchases = DB::table('purchase_invoice as pi')
            ->leftJoin('users as u', 'u.id', '=', 'pi.vendor_id')
            ->select('pi.id', 'pi.invoice_number', 'pi.created_at', 'pi.total_amount as taxable_value', 'pi.grand_total', 'pi.products', 'pi.taxes')
            ->whereBetween('pi.created_at', [$from, $to])
            ->get();

        // === 5) Fill GSTR-9C Summary ===
        $this->fillSummary($spreadsheet->getActiveSheet(), $sales, $purchases);

        // === 6) Stream Download ===
        $fileName = 'GSTR9C_Sales_' . now()->format('Ymd_His') . '.xlsx';
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

    private function fillSummary(?Worksheet $sheet, $sales, $purchases): void
    {
        if (!$sheet) return;

        $salesTaxable = $sales->sum('taxable_value');
        $salesGrand   = $sales->sum('grand_total');

        $purchaseTaxable = $purchases->sum('taxable_value');
        $purchaseGrand   = $purchases->sum('grand_total');

        $totalIGST = $totalCGST = $totalSGST = $totalCESS = 0;
        foreach ($sales as $s) {
            $taxes = json_decode($s->taxes, true) ?? [];
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                if ($name === 'IGST') $totalIGST += $amount;
                if ($name === 'CGST') $totalCGST += $amount;
                if ($name === 'SGST') $totalSGST += $amount;
                if ($name === 'CESS') $totalCESS += $amount;
            }
        }
        foreach ($purchases as $p) {
            $taxes = json_decode($p->taxes, true) ?? [];
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                if ($name === 'IGST') $totalIGST -= $amount; // reduce paid tax
                if ($name === 'CGST') $totalCGST -= $amount;
                if ($name === 'SGST') $totalSGST -= $amount;
                if ($name === 'CESS') $totalCESS -= $amount;
            }
        }

        // Fill some key cells (adjust based on template structure)
        $sheet->setCellValue("B5", $salesTaxable);   // Taxable turnover
        $sheet->setCellValue("C5", $salesGrand);     // Total turnover
        $sheet->setCellValue("D5", $purchaseTaxable);// Purchases taxable
        $sheet->setCellValue("E5", $purchaseGrand);  // Purchases total
        $sheet->setCellValue("F5", $totalIGST);
        $sheet->setCellValue("G5", $totalCGST);
        $sheet->setCellValue("H5", $totalSGST);
        $sheet->setCellValue("I5", $totalCESS);
    }
}
