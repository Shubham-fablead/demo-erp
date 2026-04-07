<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;

class PurchaseGSTR9CController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        // === 1) Resolve Period ===
        [$from, $to] = $this->resolveDateRange($request);

        // === 2) Load Template ===
        $template = storage_path('app/templates/Purchase_GSTR_9C_template.xlsx');
        if (!file_exists($template)) {
            abort(404, 'GSTR-9C Purchase template not found at storage/app/templates/Purchase_GSTR_9C_template.xlsx');
        }
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();

        // === 3) Fetch Purchases ===
        $purchases = DB::table('purchase_invoice as pi')
            ->leftJoin('users as u', 'u.id', '=', 'pi.vendor_id')
            ->select(
                'pi.id',
                'pi.invoice_number',
                'pi.created_at',
                'pi.total_amount as taxable_value',
                'pi.grand_total',
                'pi.products',
                'pi.taxes',
                'u.name as vendor_name',
                'u.gst_number as vendor_gstin',
                'u.pan_number as vendor_pan'
            )
            ->whereBetween('pi.created_at', [$from, $to])
            ->get();

        // === 4) Fill Excel ===
        $this->fillSummary($sheet, $purchases);
        $this->fillDetails($sheet, $purchases);

        // === 5) Stream Download ===
        $fileName = 'GSTR9C_Purchase_' . now()->format('Ymd_His') . '.xlsx';
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

    private function fillSummary(?Worksheet $sheet, $purchases): void
    {
        if (!$sheet) return;

        $totalVendors = $purchases->pluck('vendor_name')->filter()->unique()->count();
        $totalGSTINs  = $purchases->pluck('vendor_gstin')->filter()->unique()->count();
        $totalInvoices = $purchases->count();
        $totalTaxable = $purchases->sum('taxable_value');
        $totalGrand   = $purchases->sum('grand_total');

        $totalCGST = $totalSGST = $totalIGST = $totalCESS = 0;
        foreach ($purchases as $p) {
            $taxes = json_decode($p->taxes, true) ?? [];
            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                if ($name === 'CGST') $totalCGST += $amount;
                if ($name === 'SGST') $totalSGST += $amount;
                if ($name === 'IGST') $totalIGST += $amount;
                if ($name === 'CESS') $totalCESS += $amount;
            }
        }

        // Adjust cell positions based on your template
        $sheet->setCellValue("A2", $totalVendors);
        $sheet->setCellValue("B2", $totalGSTINs);
        $sheet->setCellValue("C2", $totalInvoices);
        $sheet->setCellValue("E2", $totalTaxable);
        $sheet->setCellValue("F2", $totalGrand);
        $sheet->setCellValue("G2", $totalCGST);
        $sheet->setCellValue("H2", $totalSGST);
        $sheet->setCellValue("I2", $totalIGST);
        $sheet->setCellValue("J2", $totalCESS);
    }

    private function fillDetails(?Worksheet $sheet, $purchases): void
    {
        if (!$sheet) return;

        $row = 5; // Start after header
        foreach ($purchases as $p) {
            $taxes = json_decode($p->taxes, true) ?? [];
            $cgst = $sgst = $igst = $cess = $rate = 0;

            foreach ($taxes as $t) {
                $name = strtoupper($t['name'] ?? $t['tax_name'] ?? '');
                $amount = (float)($t['amount'] ?? 0);
                $rate   = $t['rate'] ?? $rate;
                if ($name === 'CGST') $cgst += $amount;
                if ($name === 'SGST') $sgst += $amount;
                if ($name === 'IGST') $igst += $amount;
                if ($name === 'CESS') $cess += $amount;
            }

            $sheet->setCellValue("A{$row}", $p->vendor_name);
            $sheet->setCellValue("B{$row}", $p->vendor_gstin);
            $sheet->setCellValue("C{$row}", $p->vendor_pan);
            $sheet->setCellValue("D{$row}", $p->invoice_number);
            $sheet->setCellValue("E{$row}", Carbon::parse($p->created_at)->format('d-M-Y'));
            $sheet->setCellValue("F{$row}", $rate);
            $sheet->setCellValue("G{$row}", $cgst);
            $sheet->setCellValue("H{$row}", $sgst);
            $sheet->setCellValue("I{$row}", $igst);
            $sheet->setCellValue("J{$row}", $cess);
            $sheet->setCellValue("K{$row}", $p->taxable_value);
            $sheet->setCellValue("L{$row}", $p->grand_total);

            $row++;
        }
    }
}
