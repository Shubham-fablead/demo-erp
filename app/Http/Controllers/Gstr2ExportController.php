<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Gstr2ExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // Load template
        $template = storage_path('app/templates/GSTR_2_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-2 template not found at storage/app/templates/GSTR_2_template.xlsx');
        }

        $spreadsheet = IOFactory::load($template);

        // === 1) Fill B2BUR Sheet ===
        $sheet = $spreadsheet->getSheetByName('b2bur');
        if (!$sheet) {
            abort(500, 'Sheet "b2bur" not found in template.');
        }

        $row = 5; // Start after header row (row 4)

        $invoices = DB::table('custom_invoice as ci')
            ->join('users as u', 'u.id', '=', 'ci.vendor_id')
            ->whereNotNull('ci.vendor_id')
            ->where('u.role', '=', 'vendor')
            ->select(
                'ci.id',
                'ci.invoice_number',
                'ci.created_at',
                'ci.grand_total',
                'ci.taxes',
                'u.name as vendor_name',
                'u.gst_number as gstin',
                'u.pan_number as pan'
            )
            ->get();

        $totalSuppliers = $invoices->pluck('vendor_name')->unique()->count();
        $totalInvoices  = $invoices->count();
        $totalInvoiceValue = 0;
        $totalTaxable   = 0;
        $totalIGST = $totalCGST = $totalSGST = $totalCess = 0;

        foreach ($invoices as $invoice) {
            $taxes = json_decode($invoice->taxes, true) ?? [];
            $igst = $cgst = $sgst = $cess = 0;

            foreach ($taxes as $t) {
                switch (strtoupper($t['name'])) {
                    case 'IGST': $igst += $t['amount']; break;
                    case 'CGST': $cgst += $t['amount']; break;
                    case 'SGST': $sgst += $t['amount']; break;
                    case 'CESS': $cess += $t['amount']; break;
                }
            }

            $taxable = DB::table('custom_invoice_item')
                ->where('invoice_id', $invoice->id)
                ->sum('amount_total');

            // Fill row
            $sheet->setCellValue("A{$row}", $invoice->vendor_name);
            $sheet->setCellValue("B{$row}", $invoice->gstin);
            $sheet->setCellValue("C{$row}", $invoice->pan);
            $sheet->setCellValue("D{$row}", $invoice->invoice_number);
            $sheet->setCellValue("E{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($invoice->created_at)));
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("F{$row}", $invoice->grand_total);
            $sheet->setCellValue("G{$row}", "");
            $sheet->setCellValue("H{$row}", "Intra State");
            $sheet->setCellValue("I{$row}", "");
            $sheet->setCellValue("J{$row}", $taxable);
            $sheet->setCellValue("K{$row}", $igst);
            $sheet->setCellValue("L{$row}", $cgst);
            $sheet->setCellValue("M{$row}", $sgst);
            $sheet->setCellValue("N{$row}", $cess);
            $sheet->setCellValue("O{$row}", "Inputs");
            $sheet->setCellValue("P{$row}", $igst);
            $sheet->setCellValue("Q{$row}", $cgst);
            $sheet->setCellValue("R{$row}", $sgst);
            $sheet->setCellValue("S{$row}", $cess);

            // Totals
            $totalInvoiceValue += $invoice->grand_total;
            $totalTaxable += $taxable;
            $totalIGST += $igst;
            $totalCGST += $cgst;
            $totalSGST += $sgst;
            $totalCess += $cess;

            $row++;
        }

        // === 2) Fill Totals in Row 3 (b2bur header section) ===
        $sheet->setCellValue("A3", $totalSuppliers);
        $sheet->setCellValue("D3", $totalInvoices);
        $sheet->setCellValue("F3", $totalInvoiceValue);
        $sheet->setCellValue("J3", $totalTaxable);
        $sheet->setCellValue("K3", $totalIGST);
        $sheet->setCellValue("L3", $totalCGST);
        $sheet->setCellValue("M3", $totalSGST);
        $sheet->setCellValue("N3", $totalCess);
        $sheet->setCellValue("P3", $totalIGST);
        $sheet->setCellValue("Q3", $totalCGST);
        $sheet->setCellValue("R3", $totalSGST);
        $sheet->setCellValue("S3", $totalCess);

        // === 3) Optionally Fill HSN Summary (hsnsum) ===
        // (You can add logic here to group invoice items by HSN and fill rows)

        // === 4) Export File ===
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $response = new StreamedResponse(function() use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="GSTR_2_report_filled.xlsx"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }

    
}
