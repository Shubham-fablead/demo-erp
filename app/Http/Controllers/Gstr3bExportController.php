<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Gstr3bExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
            $template = storage_path('app/templates/GSTR_3B_Excel.xlsx');
            if (!file_exists($template)) {
                abort(404, 'GSTR-3B template not found at storage/app/templates/GSTR_3B_Excel.xlsx');
            }
            $spreadsheet = IOFactory::load($template);

            // Try multiple possible names
            $sheet = $spreadsheet->getSheetByName('3B-EXCEL')
                ?? $spreadsheet->getSheetByName('Sheet1')
                ?? $spreadsheet->getActiveSheet();

            if (!$sheet) {
                abort(500, 'No valid sheet found in GSTR-3B template');
            }

        // === 3) Fetch invoices ===
        $invoices = DB::table('purchase_invoice as pi')
            ->join('users as u', 'u.id', '=', 'pi.vendor_id')
            ->select(
                'pi.id',
                'pi.products',
                'pi.taxes',
                'pi.grand_total',
                'u.gst_number',
                'u.name',
                'u.role'
            )
            ->whereBetween('pi.created_at', [$from, $to])
            ->get();

        // === 4) Accumulators ===
        $taxableB2B = 0; $igstB2B = 0; $cgstB2B = 0; $sgstB2B = 0;
        $taxableB2C = 0; $igstB2C = 0; $cgstB2C = 0; $sgstB2C = 0;

        foreach ($invoices as $inv) {
            $products = json_decode($inv->products, true) ?? [];
            $taxes    = json_decode($inv->taxes, true) ?? [];

            $taxable = collect($products)->sum('total');
            $igst = $cgst = $sgst = 0;

            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                if ($name === 'IGST') $igst += $amount;
                if ($name === 'CGST') $cgst += $amount;
                if ($name === 'SGST') $sgst += $amount;
            }

            if (!empty($inv->gst_number)) {
                // Registered B2B
                $taxableB2B += $taxable;
                $igstB2B += $igst;
                $cgstB2B += $cgst;
                $sgstB2B += $sgst;
            } else {
                // Unregistered B2C
                $taxableB2C += $taxable;
                $igstB2C += $igst;
                $cgstB2C += $cgst;
                $sgstB2C += $sgst;
            }
        }

        // === 5) Fill into Excel ===
        // Row 7 → B2B sales
        $sheet->setCellValue("B7", $taxableB2B);
        $sheet->setCellValue("C7", $igstB2B);
        $sheet->setCellValue("D7", $cgstB2B);
        $sheet->setCellValue("E7", $sgstB2B);

        // Row 10 → Unregistered (B2C interstate)
        $sheet->setCellValue("B10", $taxableB2C);
        $sheet->setCellValue("C10", $igstB2C);
        $sheet->setCellValue("D10", $cgstB2C);
        $sheet->setCellValue("E10", $sgstB2C);

        // Row 12 → Total
        $sheet->setCellValue("B12", $taxableB2B + $taxableB2C);
        $sheet->setCellValue("C12", $igstB2B + $igstB2C);
        $sheet->setCellValue("D12", $cgstB2B + $cgstB2C);
        $sheet->setCellValue("E12", $sgstB2B + $sgstB2C);

        // === 6) Stream Download ===
        $fileName = 'GSTR3B_' . now()->format('Ymd_His') . '.xlsx';
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
}
