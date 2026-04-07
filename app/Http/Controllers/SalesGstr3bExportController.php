<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use App\Models\TaxRate;

class SalesGstr3bExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        try {
            // === 1) Resolve Period ===
            [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/Sales_GSTR_3B_Excel.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-3B template not found at storage/app/templates/Sales_GSTR_3B_Excel.xlsx');
        }
        $spreadsheet = IOFactory::load($template);

                    // === 3) Fetch Orders (Sales) ===
            $orders = DB::table('orders as o')
                ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
                ->select(
                    'o.id',
                    'o.order_number',
                    'o.created_at',
                    'o.total_amount',
                    'o.tax_id',
                    'u.name as customer_name',
                    'u.gst_number as customer_gstin'
                )
                ->whereBetween('o.created_at', [$from, $to])
                ->where('o.payment_status', '=', 'completed')
                ->get();

            // Debug: Log the count of orders found
            Log::info('GSTR3B Export - Orders found: ' . $orders->count());

        $orders = $orders->map(function ($o) {
            $taxes = [];
            if (!empty($o->tax_id)) {
                $ids = json_decode($o->tax_id, true);
                if (is_array($ids)) {
                    $taxes = TaxRate::whereIn('id', $ids)->get()->map(function ($t) use ($o) {
                        $rate = $t->tax_rate ?? 0;
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

                    // === 4) Fetch Custom Invoices ===
            $customInvoices = DB::table('custom_invoice as ci')
                ->select(
                    'ci.id',
                    'ci.invoice_number',
                    'ci.created_at',
                    'ci.total_amount as taxable_value',
                    'ci.taxes',
                    'ci.grand_total'
                )
                ->whereBetween('ci.created_at', [$from, $to])
                ->where('ci.status', '=', 'completed')
                ->get();

            // Debug: Log the count of custom invoices found
            Log::info('GSTR3B Export - Custom Invoices found: ' . $customInvoices->count());

        $customInvoices = $customInvoices->map(function ($ci) {
            return (object)[
                'created_at'    => $ci->created_at,
                'taxable_value' => $ci->taxable_value,
                'taxes'         => $ci->taxes ?? '[]',
                'grand_total'   => $ci->grand_total,
            ];
        });

                    // === 5) Merge datasets ===
            $invoices = $orders->merge($customInvoices);

            // Debug: Log the total count
            Log::info('GSTR3B Export - Total invoices: ' . $invoices->count());

        // === 6) Fill Summary Sheet ===
        $this->fillSummary($spreadsheet->getActiveSheet(), $invoices);

        // === 7) Stream Download ===
        $fileName = 'GSTR3B_Sales_' . now()->format('Ymd_His') . '.xlsx';
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control'       => 'max-age=0',
        ]);
        } catch (\Exception $e) {
            Log::error('GSTR3B Export Error: ' . $e->getMessage());
            Log::error('GSTR3B Export Error Stack: ' . $e->getTraceAsString());
            abort(500, 'Error generating GSTR-3B export: ' . $e->getMessage());
        }
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

    private function fillSummary(?Worksheet $sheet, $invoices): void
    {
        if (!$sheet) return;

        $totalTaxable = 0;
        $totalIGST = $totalCGST = $totalSGST = $totalCESS = 0;

        foreach ($invoices as $inv) {
            $taxes = json_decode($inv->taxes, true) ?? [];
            $totalTaxable += $inv->taxable_value;

            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);

                if ($name === 'IGST') $totalIGST += $amount;
                if ($name === 'CGST') $totalCGST += $amount;
                if ($name === 'SGST') $totalSGST += $amount;
                if ($name === 'CESS') $totalCESS += $amount;
            }
        }

        // Fill summary cells in your GSTR-3B template
        $sheet->setCellValue("B5", $totalTaxable); // taxable value
        $sheet->setCellValue("C5", $totalIGST);    // IGST
        $sheet->setCellValue("D5", $totalCGST);    // CGST
        $sheet->setCellValue("E5", $totalSGST);    // SGST
        $sheet->setCellValue("F5", $totalCESS);    // CESS
    }
}
