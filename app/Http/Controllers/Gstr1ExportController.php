<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class Gstr1ExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/Gsrt_1_excel_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-1 template not found at storage/app/templates/Gsrt_1_excel_template.xlsx');
        }
        $spreadsheet = IOFactory::load($template);

        // === 3) Fetch Data ===
        $invoices = DB::table('purchase_invoice as pi')
            ->join('users as u', 'u.id', '=', 'pi.vendor_id')
            ->select(
                'pi.id',
                'pi.invoice_number',
                'pi.created_at',
                'pi.grand_total',
                'pi.products',
                'pi.taxes',
                'u.name as customer_name',
                'u.gst_number as customer_gstin',
                'u.pan_number as customer_pan'
            )
            ->whereBetween('pi.created_at', [$from, $to])
            ->orderBy('pi.created_at')
            ->get();

        // === 4) Fill B2B Sheet ===
        $this->fillB2B($spreadsheet->getSheetByName('Sheet1'), $invoices);

        // === 5) Update Summary ===
        $this->fillSummary($spreadsheet->getSheetByName('Sheet1'), $invoices);

        // === 6) Stream Download ===
        $fileName = 'GSTR1_' . now()->format('Ymd_His') . '.xlsx';
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
        $row = 5; // Data starts at row 5 (headers at row 4)

        foreach ($invoices as $inv) {
            $products = json_decode($inv->products, true) ?? [];
            $taxes    = json_decode($inv->taxes, true) ?? [];

            $taxableValue = collect($products)->sum('total');
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
            $sheet->setCellValue("C{$row}", $inv->invoice_number);
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
            $sheet->setCellValue("K{$row}", $rate);
            $sheet->setCellValue("L{$row}", $taxableValue);
            $sheet->setCellValue("M{$row}", $cess);

            $row++;
        }
    }

    private function fillSummary(?Worksheet $sheet, $invoices): void
    {
        if (!$sheet) return;

        $totalRecipients = $invoices->pluck('customer_gstin')->filter()->unique()->count();
        $totalInvoices   = $invoices->count();
        $totalInvoiceVal = $invoices->sum('grand_total');

        $totalTaxable = 0;
        $totalCess    = 0;
        foreach ($invoices as $inv) {
            $products = json_decode($inv->products, true) ?? [];
            $taxes    = json_decode($inv->taxes, true) ?? [];
            $totalTaxable += collect($products)->sum('total');
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
