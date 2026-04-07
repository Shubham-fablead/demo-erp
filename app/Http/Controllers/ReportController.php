<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use PhpOffice\PhpSpreadsheet\CachedObjectStorageFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\Order;
use App\Models\TaxRate;


class ReportController extends Controller
{


     /**
     * Resolve from_date and to_date from request
     */
    private function resolveDateRange(Request $request): array
    {
        $from = $request->input('from_date')
            ? date('Y-m-d 00:00:00', strtotime($request->input('from_date')))
            : null;

        $to = $request->input('to_date')
            ? date('Y-m-d 23:59:59', strtotime($request->input('to_date')))
            : null;

        return [$from, $to];
    }

    public function exportGstr1Excel(Request $request): StreamedResponse
    {
        // --------------------------------------------
        // 0) Reuse your summary logic from gstr1()
        // --------------------------------------------
        // NOTE: we’ll also refetch orders to build line items
        [$from, $to] = $this->resolveDateRange($request);

        $settings = DB::table('settings')->first();
        $activeTaxes = TaxRate::where('status', 'active')->get();

        $cgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        // Build orders (only completed)
        $salesQuery = Order::with(['orderItems' => function ($q) {
                $q->where('isDeleted', 0);
            }, 'user'])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $orders = $salesQuery->get();

        // Compute aggregates (same as your gstr1())
        $b2bAgg = ['invoice_count'=>0,'taxable_value'=>0,'cgst'=>0,'sgst'=>0,'igst'=>0];
        $b2cAgg = ['invoice_count'=>0,'taxable_value'=>0,'cgst'=>0,'sgst'=>0,'igst'=>0];

        // And also build line items for sheets
        $b2bRows = []; // for sheet: b2b,sez,de
        $b2csRows = []; // for sheet: b2cs (OE/UR)
        // (Add more arrays if you later want cdnr, cdnur etc.)

        foreach ($orders as $order) {
            $subtotal = $order->orderItems->sum(fn($item) => (float)$item->price * (float)$item->quantity);
            $discountPercent = (float)($order->discount ?? 0);
            $discountAmount  = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount   = max(0, $subtotal - $discountAmount);

            $cgstAmount = $cgstRate > 0 ? ($taxableAmount * $cgstRate) / 100.0 : 0.0;
            $sgstAmount = $sgstRate > 0 ? ($taxableAmount * $sgstRate) / 100.0 : 0.0;
            $igstAmount = $igstRate > 0 ? ($taxableAmount * $igstRate) / 100.0 : 0.0;

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax = ($taxableAmount * $totalRatePercent) / 100.0;
                $cgstAmount = $totalTax / 2.0;
                $sgstAmount = $totalTax / 2.0;
            }

            $isB2B = false;
            $gstNum = '';
            $receiver = '';
            if ($order->relationLoaded('user') && $order->user) {
                $gstNum = trim((string)($order->user->gst_number ?? ''));
                $receiver = trim((string)($order->user->name ?? ''));
                $isB2B = $gstNum !== '';
            }

            // Place of Supply (adjust to your DB fields; fallback to 24-Gujarat as in your screenshot)
            $posCode = $settings->gst_state_code ?? '24';
            $posName = $settings->state_name ?? 'Gujarat';
            $placeOfSupply = "{$posCode}-{$posName}";

            $rateUsed = $igstAmount > 0 ? $igstRate : ($cgstRate + $sgstRate);
            $invoiceValue = round($taxableAmount + $cgstAmount + $sgstAmount + $igstAmount, 2);
            $invoiceDate  = optional($order->created_at)->format('d-M-Y') ?? '';

            if ($isB2B) {
                // --------- b2b,sez,de row format (exact headers below) ----------
                $b2bRows[] = [
                    $gstNum,                  // GSTIN/UIN of Recipient
                    $receiver ?: 'N/A',       // Receiver Name
                    (string)$order->id,       // Invoice Number
                    $invoiceDate,             // Invoice date
                    number_format($invoiceValue, 2, '.', ''), // Invoice Value
                    $placeOfSupply,           // Place Of Supply
                    'N',                      // Reverse Charge (Y/N) – adjust if you handle RC
                    '',                       // Applicable % of Tax Rate (usually blank)
                    'Regular B2B',            // Invoice Type
                    '',                       // E-Commerce GSTIN
                    (float)$rateUsed,         // Rate
                    round($taxableAmount, 2), // Taxable Value
                    0.00,                     // Cess Amount
                ];

                $b2bAgg['invoice_count'] += 1;
                $b2bAgg['taxable_value'] += $taxableAmount;
                $b2bAgg['cgst'] += $cgstAmount;
                $b2bAgg['sgst'] += $sgstAmount;
                $b2bAgg['igst'] += $igstAmount;
            } else {
                // --------- b2cs row format (exact headers below) ----------
                // Type: OE (if through e-com op) or UR (unregistered). Using OE just as example; change as needed.
                $b2csRows[] = [
                    'OE',                      // Type
                    $placeOfSupply,            // Place Of Supply
                    '',                        // Applicable % of Tax Rate
                    (float)$rateUsed,          // Rate
                    round($taxableAmount, 2),  // Taxable Value
                    0.00,                      // Cess Amount
                    '',                        // E-Commerce GSTIN
                ];

                $b2cAgg['invoice_count'] += 1;
                $b2cAgg['taxable_value'] += $taxableAmount;
                $b2cAgg['cgst'] += $cgstAmount;
                $b2cAgg['sgst'] += $sgstAmount;
                $b2cAgg['igst'] += $igstAmount;
            }
        }

        $summary = [
            'total_invoices' => $b2bAgg['invoice_count'] + $b2cAgg['invoice_count'],
            'taxable_value'  => round($b2bAgg['taxable_value'] + $b2cAgg['taxable_value'], 2),
            'cgst'           => round($b2bAgg['cgst'] + $b2cAgg['cgst'], 2),
            'sgst'           => round($b2bAgg['sgst'] + $b2cAgg['sgst'], 2),
            'igst'           => round($b2bAgg['igst'] + $b2cAgg['igst'], 2),
        ];

        // Round aggregates for neatness
        $b2bAgg = array_map(fn($v) => is_numeric($v) ? round($v, 2) : $v, $b2bAgg);
        $b2cAgg = array_map(fn($v) => is_numeric($v) ? round($v, 2) : $v, $b2cAgg);

        // --------------------------------------------
        // 1) Load the TEMPLATE (keeps exact formatting)
        // --------------------------------------------
        $templatePath = storage_path('app/templates/gstr1_template.xlsx');
        if (file_exists($templatePath)) {
            $spreadsheet = IOFactory::load($templatePath);
        } else {
            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0);

            // Create minimal sheets when template is missing
            $sheetB2B = new Worksheet($spreadsheet, 'b2b,sez,de');
            $spreadsheet->addSheet($sheetB2B, 0);
            $sheetB2B->fromArray([
                ['GSTIN/UIN of Recipient','Receiver Name','Invoice Number','Invoice date','Invoice Value','Place Of Supply','Reverse Charge','Applicable % of Tax Rate','Invoice Type','E-Commerce GSTIN','Rate','Taxable Value','Cess Amount']
            ], null, 'A5');

            $sheetB2CS = new Worksheet($spreadsheet, 'b2cs');
            $spreadsheet->addSheet($sheetB2CS, 1);
            $sheetB2CS->fromArray([
                ['Type','Place Of Supply','Applicable % of Tax Rate','Rate','Taxable Value','Cess Amount','E-Commerce GSTIN']
            ], null, 'A5');
        }

        // --------------------------------------------
        // 2) Fill B2B sheet: "b2b,sez,de"
        // --------------------------------------------
        $sheetB2B = $spreadsheet->getSheetByName('b2b,sez,de');
        if ($sheetB2B) {
            // A quick header reminder (matches your screenshot):
            // A: GSTIN/UIN of Recipient
            // B: Receiver Name
            // C: Invoice Number
            // D: Invoice date
            // E: Invoice Value
            // F: Place Of Supply
            // G: Reverse Charge
            // H: Applicable % of Tax Rate
            // I: Invoice Type
            // J: E-Commerce GSTIN
            // K: Rate
            // L: Taxable Value
            // M: Cess Amount

            $startRow = 6; // adjust if your template starts later
            $r = $startRow;
            foreach ($b2bRows as $row) {
                $sheetB2B->fromArray($row, null, "A{$r}");
                $r++;
            }

            // (Optional) If your template has summary cells on top, set them here by address
            // Example (adjust cell addresses as per your template):
            // $sheetB2B->setCellValue('B2', count(array_unique(array_column($b2bRows, 0)))); // No. of Recipients
            // $sheetB2B->setCellValue('D2', $b2bAgg['invoice_count']);                     // No. of Invoices
            // $sheetB2B->setCellValue('H2', array_sum(array_map(fn($x)=> (float)$x[4], $b2bRows))); // Total Invoice Value
            // $sheetB2B->setCellValue('J2', $b2bAgg['taxable_value']);                    // Total Taxable Value
        }

        // --------------------------------------------
        // 3) Fill B2CS sheet: "b2cs"
        // --------------------------------------------
        $sheetB2CS = $spreadsheet->getSheetByName('b2cs');
        if ($sheetB2CS) {
            // Headers you showed:
            // A: Type
            // B: Place Of Supply
            // C: Applicable % of Tax Rate
            // D: Rate
            // E: Taxable Value
            // F: Cess Amount
            // G: E-Commerce GSTIN

            $startRow = 6; // adjust to your template
            $r = $startRow;
            foreach ($b2csRows as $row) {
                $sheetB2CS->fromArray($row, null, "A{$r}");
                $r++;
            }

            // (Optional summary cells on the top band—map them if you want)
            // $sheetB2CS->setCellValue('E2', $b2cAgg['taxable_value']); // Total Taxable Value
        }

        // --------------------------------------------
        // 4) Other sheets
        // --------------------------------------------
        // All other tabs remain in the file with their exact formatting (because we loaded the template).
        // If you later gather rows for b2ba, b2cl, b2cla, cdnr, cdnra, cdnur, cdnura, exp, etc.,
        // just replicate the fromArray() loop with the correct start row & column mapping.

        // --------------------------------------------
        // 5) Download the file
        // --------------------------------------------
        $filename = 'GSTR1_'.now()->format('Ymd_His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            IOFactory::createWriter($spreadsheet, 'Xlsx')->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Inside your ReportController

    

}
