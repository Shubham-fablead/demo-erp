<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GSTR2gstController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // Load template
        $template = storage_path('app/templates/GSTR_2_SALES_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-2 Sales template not found at storage/app/templates/GSTR_2_SALES_template.xlsx');
        }

        $spreadsheet = IOFactory::load($template);

        // === 1) Fill B2BUR Sheet ===
        $sheet = $spreadsheet->getSheetByName('b2bur');
        if (!$sheet) {
            abort(500, 'Sheet "b2bur" not found in template.');
        }

        $row = 5; // Start after header row (row 4)

        $orders = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->whereNotNull('o.user_id')
            ->where('u.role', '=', 'customer')
            ->select(
                'o.id',
                'o.order_number',
                'o.created_at',
                'o.total_amount',
                'o.tax_id',
                'u.name as customer_name',
                'u.gst_number as gstin',
                'u.pan_number as pan'
            )
            ->get();

        $totalCustomers = $orders->pluck('customer_name')->unique()->count();
        $totalInvoices  = $orders->count();
        $totalInvoiceValue = 0;
        $totalTaxable   = 0;
        $totalIGST = $totalCGST = $totalSGST = $totalCess = 0;

        foreach ($orders as $order) {
            // No tax breakdown, so set to 0
            $igst = $cgst = $sgst = $cess = 0;

            $taxable = DB::table('order_items')
                ->where('order_id', $order->id)
                ->sum('total_amount');

            // Fill row
            $sheet->setCellValue("A{$row}", $order->customer_name);
            $sheet->setCellValue("B{$row}", $order->gstin);
            $sheet->setCellValue("C{$row}", $order->pan);
            $sheet->setCellValue("D{$row}", $order->order_number);
            $sheet->setCellValue("E{$row}", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($order->created_at)));
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            $sheet->setCellValue("F{$row}", $order->total_amount);
            $sheet->setCellValue("G{$row}", "");
            $sheet->setCellValue("H{$row}", "Intra State");
            $sheet->setCellValue("I{$row}", "");
            $sheet->setCellValue("J{$row}", $taxable);
            $sheet->setCellValue("K{$row}", $igst);
            $sheet->setCellValue("L{$row}", $cgst);
            $sheet->setCellValue("M{$row}", $sgst);
            $sheet->setCellValue("N{$row}", $cess);
            $sheet->setCellValue("O{$row}", "Outputs");
            $sheet->setCellValue("P{$row}", $igst);
            $sheet->setCellValue("Q{$row}", $cgst);
            $sheet->setCellValue("R{$row}", $sgst);
            $sheet->setCellValue("S{$row}", $cess);

            // Totals
            $totalInvoiceValue += $order->total_amount;
            $totalTaxable += $taxable;
            $totalIGST += $igst;
            $totalCGST += $cgst;
            $totalSGST += $sgst;
            $totalCess += $cess;

            $row++;
        }

        // === 2) Fill Totals in Row 3 (b2bur header section) ===
        $sheet->setCellValue("A3", $totalCustomers);
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
        $response->headers->set('Content-Disposition', 'attachment;filename="GSTR_2_SALES_report_filled.xlsx"');
        $response->headers->set('Cache-Control','max-age=0');

        return $response;
    }
}
