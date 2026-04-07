<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CreditNoteItem;
use App\Models\DebitNoteItem;
use App\Models\PurchaseReturn;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\SalesReturn;
use App\Models\TaxRate;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
// use PDF;

class GstController extends Controller
{
    // GET /api/gstr-3b
    // Query params:
    //   filter: this_week | this_month | last_6_months | this_year | previous_year
    //   or from_date=YYYY-MM-DD & to_date=YYYY-MM-DD
    public function gstr3b(Request $request)
    {
        // Date range resolution
        [$from, $to] = $this->resolveDateRange($request);

        // Currency Settings
        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates (used for sales calc)
        $activeTaxes      = TaxRate::where('status', 'active')->get();
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        $cgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;

        // 1) Outward supplies (Sales)
        $salesQuery = Order::with(['orderItems' => function ($q) {
            $q->where('isDeleted', 0);
        }])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $sales = $salesQuery->get();

        $outwardTaxable = 0.0;
        $outwardCGST    = 0.0;
        $outwardSGST    = 0.0;
        $outwardIGST    = 0.0;

        foreach ($sales as $order) {
            $subtotal = $order->orderItems->sum(function ($item) {
                return (float) $item->price * (float) $item->quantity;
            });

            $discountPercent = (float) ($order->discount ?? 0);
            $discountAmount  = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount   = max(0, $subtotal - $discountAmount);

            // Compute tax based on active rates
            $outwardTaxable += $taxableAmount;

            // Split into CGST/SGST/IGST as per available rates
            if ($cgstRate > 0) {
                $outwardCGST += ($taxableAmount * $cgstRate) / 100.0;
            }
            if ($sgstRate > 0) {
                $outwardSGST += ($taxableAmount * $sgstRate) / 100.0;
            }
            if ($igstRate > 0) {
                $outwardIGST += ($taxableAmount * $igstRate) / 100.0;
            }

            // If no named split provided but we still have a total rate, allocate proportionally
            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax  = ($taxableAmount * $totalRatePercent) / 100.0;
                // Default split equally to CGST/SGST when IGST not defined
                $outwardCGST += $totalTax / 2.0;
                $outwardSGST += $totalTax / 2.0;
            }
        }

        // 2) Inward supplies (Purchases) - ITC from purchase invoices
        $purchaseQuery = PurchaseInvoice::query()->where('isDeleted', 0);
        if ($from && $to) {
            $purchaseQuery->whereBetween('created_at', [$from, $to]);
        }
        $purchaseInvoices  = $purchaseQuery->get();

        $itcCGST = 0.0;
        $itcSGST = 0.0;
        $itcIGST = 0.0;

        foreach ($purchaseInvoices as $pi) {
            if (empty($pi->taxes)) {
                continue;
            }

            $taxes = json_decode($pi->taxes, true);
            if (! is_array($taxes)) {
                continue;
            }

            foreach ($taxes as $tax) {
                // tax array may vary in shape; try both 'name' and 'tax_name'
                $name   = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                $amount = (float) ($tax['amount'] ?? 0);
                if ($amount <= 0) {
                    continue;
                }

                // Resolve name via tax_id/id when missing
                if ($name === '' && isset($tax['tax_id'])) {
                    $tr = TaxRate::find($tax['tax_id']);
                    if ($tr) {
                        $name = strtoupper(trim($tr->tax_name));
                    }

                }
                if ($name === '' && isset($tax['id'])) {
                    $tr = TaxRate::find($tax['id']);
                    if ($tr) {
                        $name = strtoupper(trim($tr->tax_name));
                    }

                }

                if ($name === 'CGST') {
                    $itcCGST += $amount;
                } elseif ($name === 'SGST') {
                    $itcSGST += $amount;
                } elseif ($name === 'IGST') {
                    $itcIGST += $amount;
                } else {
                    // If still unnamed, and typical CGST/SGST scenario, split equally
                    if ($name === '' && $cgstRate > 0 && $sgstRate > 0 && $igstRate === 0) {
                        $itcCGST += $amount / 2.0;
                        $itcSGST += $amount / 2.0;
                    }
                }
            }
        }

        $outwardTax  = [
            'taxable_value' => round($outwardTaxable, 2),
            'cgst'          => round($outwardCGST, 2),
            'sgst'          => round($outwardSGST, 2),
            'igst'          => round($outwardIGST, 2),
            'cess'          => 0.00,
        ];

        $eligibleITC = [
            'cgst'       => round($itcCGST, 2),
            'sgst'       => round($itcSGST, 2),
            'igst'       => round($itcIGST, 2),
            'cess'       => 0.00,
            'total'      => round($itcCGST + $itcSGST + $itcIGST, 2),
            'ineligible' => 0.00,
        ];

        // Net tax payable per component (cannot go below zero)
        $netPayable = [
            'cgst'  => round(max(0, $outwardCGST - $itcCGST), 2),
            'sgst'  => round(max(0, $outwardSGST - $itcSGST), 2),
            'igst'  => round(max(0, $outwardIGST - $itcIGST), 2),
            'cess'  => 0.00,
            'total' => 0.00,
        ];
        $netPayable['total'] = round($netPayable['cgst'] + $netPayable['sgst'] + $netPayable['igst'] + $netPayable['cess'], 2);

        return response()->json([
            'success'           => true,
            'filter'            => $request->query('filter'),
            'from_date'         => $from?->toDateString(),
            'to_date'           => $to?->toDateString(),
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'section_3_1'       => [
                'a_outward_taxable_supplies' => $outwardTax, // other than zero/nil/exempt
                'b_zero_rated'               => 0.00,
                'c_other_exempt_nil'         => 0.00,
                'd_inward_rcm'               => 0.00,
                'e_non_gst_outward'          => 0.00,
            ],
            'eligible_itc'      => $eligibleITC,
            'net_tax_payable'   => $netPayable,
        ]);
    }

    public function exportGstr3b(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $selectedSubAdminId = $request->selectedSubAdminId ?? $userId;
         if ($role === 'sub-admin') {
            $branch_id = $userId;
        } elseif ($role === 'admin' && ! empty($selectedSubAdminId)) {
            $subAdmin  = User::find($selectedSubAdminId);
            $branch_id = $subAdmin ? $subAdmin->id : $userId;
        } elseif ($role === 'staff') {
            $branch_id = $userBranchId;
        } else {
            $branch_id = $userId;
        }
        [$from, $to] = $this->resolveDateRange($request);
        $settings = DB::table('settings')->where('branch_id', $branch_id)->first();

        /* ================= SALES ================= */
        $sales = [
            'with_gst'    => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
            'without_gst' => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
        ];

        $orders = Order::with([
            'orderItems' => fn($q) => $q->where('isDeleted', 0),
            'user'
        ])
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($orders as $order) {
            $gstNumber = trim($order->user->gst_number ?? '');
            $type = ($gstNumber !== '') ? 'with_gst' : 'without_gst';

            // Calculate taxable value (total amount - total gst)
            $orderGst = ['igst' => 0, 'cgst' => 0, 'sgst' => 0];
            foreach ($order->orderItems as $item) {
                $gst = parseGstJson($item->product_gst_details);
                $orderGst['igst'] += $gst['igst'];
                $orderGst['cgst'] += $gst['cgst'];
                $orderGst['sgst'] += $gst['sgst'];
            }

            $taxableValue = (float)$order->total_amount - ($orderGst['igst'] + $orderGst['cgst'] + $orderGst['sgst']);
            
            $sales[$type]['taxable'] += $taxableValue;
            $sales[$type]['igst']    += $orderGst['igst'];
            $sales[$type]['cgst']    += $orderGst['cgst'];
            $sales[$type]['sgst']    += $orderGst['sgst'];
        }

        /* ================= SALES RETURN ================= */
        $salesReturn = [
            'with_gst'    => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
            'without_gst' => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
        ];

        $salesReturns = SalesReturn::with(['order.user', 'items'])
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($salesReturns as $sr) {
            if (!$sr->order) continue;
            $gstNumber = trim($sr->order->user->gst_number ?? '');
            $type = ($gstNumber !== '') ? 'with_gst' : 'without_gst';

            $srGst = ['igst' => 0, 'cgst' => 0, 'sgst' => 0];
            foreach ($sr->items as $item) {
                $gst = parseGstJson($item->product_gst_details);
                $srGst['igst'] += $gst['igst'];
                $srGst['cgst'] += $gst['cgst'];
                $srGst['sgst'] += $gst['sgst'];
            }

            $taxableValue = (float)$sr->total_amount - ($srGst['igst'] + $srGst['cgst'] + $srGst['sgst']);

            $salesReturn[$type]['taxable'] += $taxableValue;
            $salesReturn[$type]['igst']    += $srGst['igst'];
            $salesReturn[$type]['cgst']    += $srGst['cgst'];
            $salesReturn[$type]['sgst']    += $srGst['sgst'];
        }

        /* ================= CREDIT NOTE ================= */
        $creditNote = [
            'with_gst'    => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
            'without_gst' => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
        ];

        $creditNotes = CreditNoteItem::with('order.user')
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereNotNull('order_id')
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($creditNotes as $cn) {
            if (!$cn->order) continue;
            $gstNumber = trim($cn->order->user->gst_number ?? '');
            $type = ($gstNumber !== '') ? 'with_gst' : 'without_gst';
            $creditNote[$type]['taxable'] += (float)$cn->settlement_amount;
        }

        /* ================= DEBIT NOTE ================= */
        $debitNote = [
            'with_gst'    => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
            'without_gst' => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
        ];
        $debitNotes = DebitNoteItem::with('order.user')
            ->where('transaction_type', 'receipt')
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->whereNotNull('order_id')
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($debitNotes as $dn) {
            if (!$dn->order) continue;
            $gstNumber = trim($dn->order->user->gst_number ?? '');
            $type = ($gstNumber !== '') ? 'with_gst' : 'without_gst';
            $debitNote[$type]['taxable'] += (float)$dn->settlement_amount;
        }

        /* ================= PURCHASE ================= */
        $purchaseData = ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0];
        $purchaseInvoices = PurchaseInvoice::where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($purchaseInvoices as $pi) {
            $piTaxTotal = 0;
            $taxes = json_decode($pi->taxes, true);
            if (is_array($taxes)) {
                foreach ($taxes as $tax) {
                    $name = strtoupper($tax['name'] ?? $tax['tax_name'] ?? '');
                    $amount = (float)($tax['amount'] ?? 0);
                    if ($name === 'IGST') $purchaseData['igst'] += $amount;
                    if ($name === 'CGST') $purchaseData['cgst'] += $amount;
                    if ($name === 'SGST') $purchaseData['sgst'] += $amount;
                    $piTaxTotal += $amount;
                }
            }
            $purchaseData['taxable'] += (float)$pi->grand_total - $piTaxTotal;
        }

        /* ================= PURCHASE RETURN ================= */
        $purchaseReturnData = ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0];
        $purchaseReturns = PurchaseReturn::with('items')
            ->where('isDeleted', 0)
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($purchaseReturns as $pr) {
            $purchaseReturnData['taxable'] += (float)($pr->subtotal ?? 0) + (float)($pr->shipping ?? 0);
            foreach ($pr->items as $item) {
                $gst = parseGstJson($item->product_gst_details);
                $purchaseReturnData['igst'] += $gst['igst'];
                $purchaseReturnData['cgst'] += $gst['cgst'];
                $purchaseReturnData['sgst'] += $gst['sgst'];
            }
        }

        /* ================= PURCHASE DEBIT NOTE ================= */
        $purchaseDebitData = ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0];
        $pDebitNotes = DebitNoteItem::where('isDeleted', 0) 
            ->whereNotNull('purchase_id')
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($pDebitNotes as $pdn) {
            $purchaseDebitData['taxable'] += (float)$pdn->settlement_amount;
        }

        /* ================= PURCHASE CREDIT NOTE ================= */
        $purchaseCreditData = ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0];
        $pCreditNotes = CreditNoteItem::where('isDeleted', 0)
            ->whereNotNull('purchase_id')
            ->where('branch_id', $branch_id)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->get();

        foreach ($pCreditNotes as $pcn) {
            $purchaseCreditData['taxable'] += (float)$pcn->settlement_amount;
        }

        /* ================= NET PAYABLE ================= */
        $totalOutputTaxable = $sales['with_gst']['taxable'] + $sales['without_gst']['taxable'] 
                            - ($salesReturn['with_gst']['taxable'] + $salesReturn['without_gst']['taxable'])
                            - ($creditNote['with_gst']['taxable'] + $creditNote['without_gst']['taxable'])
                            + ($debitNote['with_gst']['taxable'] + $debitNote['without_gst']['taxable']);

        $totalOutputIGST = $sales['with_gst']['igst'] + $sales['without_gst']['igst'] 
                         - ($salesReturn['with_gst']['igst'] + $salesReturn['without_gst']['igst'])
                         - ($creditNote['with_gst']['igst'] + $creditNote['without_gst']['igst'])
                         + ($debitNote['with_gst']['igst'] + $debitNote['without_gst']['igst']);

        $totalOutputCGST = $sales['with_gst']['cgst'] + $sales['without_gst']['cgst'] 
                         - ($salesReturn['with_gst']['cgst'] + $salesReturn['without_gst']['cgst'])
                         - ($creditNote['with_gst']['cgst'] + $creditNote['without_gst']['cgst'])
                         + ($debitNote['with_gst']['cgst'] + $debitNote['without_gst']['cgst']);

        $totalOutputSGST = $sales['with_gst']['sgst'] + $sales['without_gst']['sgst'] 
                         - ($salesReturn['with_gst']['sgst'] + $salesReturn['without_gst']['sgst'])
                         - ($creditNote['with_gst']['sgst'] + $creditNote['without_gst']['sgst'])
                         + ($debitNote['with_gst']['sgst'] + $debitNote['without_gst']['sgst']);

        $totalITCTaxable = $purchaseData['taxable'] - $purchaseReturnData['taxable'] - $purchaseDebitData['taxable'] + $purchaseCreditData['taxable'];
        $totalITCIGST    = $purchaseData['igst'] - $purchaseReturnData['igst'] - $purchaseDebitData['igst'] + $purchaseCreditData['igst'];
        $totalITCCGST    = $purchaseData['cgst'] - $purchaseReturnData['cgst'] - $purchaseDebitData['cgst'] + $purchaseCreditData['cgst'];
        $totalITCSGST    = $purchaseData['sgst'] - $purchaseReturnData['sgst'] - $purchaseDebitData['sgst'] + $purchaseCreditData['sgst'];

        $totalOutputTax = $totalOutputIGST + $totalOutputCGST + $totalOutputSGST;
        $totalITCTax    = $totalITCIGST + $totalITCCGST + $totalITCSGST;
        $finalNetTax    = $totalOutputTax - $totalITCTax;

        $netDiff = [
            'taxable' => $totalOutputTaxable - $totalITCTaxable,
            'igst'    => $totalOutputIGST - $totalITCIGST,
            'cgst'    => $totalOutputCGST - $totalITCCGST,
            'sgst'    => $totalOutputSGST - $totalITCSGST,
        ];

        /* ================= FINAL DATA ================= */
        $data = [
            'from_date'          => $from?->toDateString(),
            'to_date'            => $to?->toDateString(),

            'total_sales_reg'    => $sales['with_gst'],
            'total_sales_unreg'  => $sales['without_gst'],

            'sales_return'       => $salesReturn['with_gst'],
            'sales_return_unreg' => $salesReturn['without_gst'],

            'credit_note'        => $creditNote['with_gst'],
            'credit_note_unreg'  => $creditNote['without_gst'],

            'debit_note'         => $debitNote['with_gst'],
            'debit_note_unreg'   => $debitNote['without_gst'],

            'other_income'       => ['taxable' => 0, 'igst' => 0, 'cgst' => 0, 'sgst' => 0],
            'purchase'           => $purchaseData,
            'purchase_return'    => $purchaseReturnData,
            'purchase_debit'     => $purchaseDebitData,
            'purchase_credit'    => $purchaseCreditData,
            'total_receivable'   => [
                'taxable' => $totalITCTaxable,
                'igst'    => $totalITCIGST,
                'cgst'    => $totalITCCGST,
                'sgst'    => $totalITCSGST,
            ],
            'settings'           => $settings,

            'net_payable'        => [
                'taxable' => $totalOutputTaxable,
                'igst'    => $totalOutputIGST,
                'cgst'    => $totalOutputCGST,
                'sgst'    => $totalOutputSGST,
            ],
            'final_net_tax'      => $finalNetTax,
            'net_diff'           => $netDiff,
        ];

        $pdf = PDF::loadView('gst.gstr3b-pdf', compact('data'))
            ->setPaper('A4', 'portrait');

        $filename = "GSTR-3B-MONTHLY-RETURN-" . time() . ".pdf";
        $relativePath = 'gst-reports/' . $filename;

        Storage::disk('public')->put($relativePath, $pdf->output());

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'GSTR-3B Summary PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $filename,
        ]);
    }

    public function gstr1(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates
        $activeTaxes      = TaxRate::where('status', 'active')->get();
        $cgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        // Sales orders (completed only)
        $salesQuery = Order::with(['orderItems' => function ($q) {
            $q->where('isDeleted', 0);
        }, 'user'])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');

        if ($from && $to) {
            $salesQuery->whereBetween('created_at', [$from, $to]);
        }

        $orders = $salesQuery->get();

        $b2b = [
            'invoice_count' => 0,
            'taxable_value' => 0.0,
            'cgst'          => 0.0,
            'sgst'          => 0.0,
            'igst'          => 0.0,
        ];
        $b2c = [
            'invoice_count' => 0,
            'taxable_value' => 0.0,
            'cgst'          => 0.0,
            'sgst'          => 0.0,
            'igst'          => 0.0,
        ];

        foreach ($orders as $order) {
            $subtotal = $order->orderItems->sum(function ($item) {
                return (float) $item->price * (float) $item->quantity;
            });
            $discountPercent = (float) ($order->discount ?? 0);
            $discountAmount  = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount   = max(0, $subtotal - $discountAmount);

            $cgstAmount = $cgstRate > 0 ? ($taxableAmount * $cgstRate) / 100.0 : 0.0;
            $sgstAmount = $sgstRate > 0 ? ($taxableAmount * $sgstRate) / 100.0 : 0.0;
            $igstAmount = $igstRate > 0 ? ($taxableAmount * $igstRate) / 100.0 : 0.0;

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax   = ($taxableAmount * $totalRatePercent) / 100.0;
                $cgstAmount = $totalTax / 2.0;
                $sgstAmount = $totalTax / 2.0;
            }

            $isB2B = false;
            if ($order->relationLoaded('user') && $order->user) {
                $gstNum = trim((string) ($order->user->gst_number ?? ''));
                $isB2B  = $gstNum !== '';
            }

            if ($isB2B) {
                $b2b['invoice_count'] += 1;
                $b2b['taxable_value'] += $taxableAmount;
                $b2b['cgst']          += $cgstAmount;
                $b2b['sgst']          += $sgstAmount;
                $b2b['igst']          += $igstAmount;
            } else {
                $b2c['invoice_count'] += 1;
                $b2c['taxable_value'] += $taxableAmount;
                $b2c['cgst']          += $cgstAmount;
                $b2c['sgst']          += $sgstAmount;
                $b2c['igst']          += $igstAmount;
            }
        }

        $summary = [
            'total_invoices' => $b2b['invoice_count'] + $b2c['invoice_count'],
            'taxable_value'  => round($b2b['taxable_value'] + $b2c['taxable_value'], 2),
            'cgst'           => round($b2b['cgst'] + $b2c['cgst'], 2),
            'sgst'           => round($b2b['sgst'] + $b2c['sgst'], 2),
            'igst'           => round($b2b['igst'] + $b2c['igst'], 2),
        ];

        $b2b = array_map(function ($v) {return is_numeric($v) ? round($v, 2) : $v;}, $b2b);
        $b2c = array_map(function ($v) {return is_numeric($v) ? round($v, 2) : $v;}, $b2c);

        return response()->json([
            'success'           => true,
            'filter'            => $request->query('filter'),
            'from_date'         => $from?->toDateString(),
            'to_date'           => $to?->toDateString(),
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'b2b'               => $b2b,
            'b2c'               => $b2c,
            'summary'           => $summary,
        ]);
    }

    public function exportGstr1(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        // Reuse gstr1() logic
        $data = $this->gstr1($request)->getData(true);

        $html  = '<h2 style="text-align:center;">GSTR-1 Report</h2>';
        $html .= '<p><b>Period:</b> ' . ($data['from_date'] ?? '-') . ' to ' . ($data['to_date'] ?? '-') . '</p>';

        // 🔹 Summary
        $summary  = $data['summary'];
        $html    .= '<h3>Summary</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f2f2f2;">
                <th>Total Invoices</th><th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $summary['total_invoices'] . '</td>
                <td>' . $summary['taxable_value'] . '</td>
                <td>' . $summary['cgst'] . '</td>
                <td>' . $summary['sgst'] . '</td>
                <td>' . $summary['igst'] . '</td>
            </tr>
        </table>';

        // 🔹 B2B
        $b2b   = $data['b2b'];
        $html .= '<h3>B2B Supplies</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f9f9f9;">
                <th>Invoices</th><th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $b2b['invoice_count'] . '</td>
                <td>' . $b2b['taxable_value'] . '</td>
                <td>' . $b2b['cgst'] . '</td>
                <td>' . $b2b['sgst'] . '</td>
                <td>' . $b2b['igst'] . '</td>
            </tr>
        </table>';

        // 🔹 B2C
        $b2c   = $data['b2c'];
        $html .= '<h3>B2C Supplies</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f9f9f9;">
                <th>Invoices</th><th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $b2c['invoice_count'] . '</td>
                <td>' . $b2c['taxable_value'] . '</td>
                <td>' . $b2c['cgst'] . '</td>
                <td>' . $b2c['sgst'] . '</td>
                <td>' . $b2c['igst'] . '</td>
            </tr>
        </table>';

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        return $pdf->stream('gstr1.pdf');
    }

    public function gstr2(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates to help classify amounts
        $activeTaxes = TaxRate::where('status', 'active')->get();
        $cgstRate    = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate    = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate    = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;

        // Fetch purchases (purchase invoices)
        $purchaseQuery = PurchaseInvoice::with('vendor')->where('isDeleted', 0);
        if ($from && $to) {
            $purchaseQuery->whereBetween('created_at', [$from, $to]);
        }
        $invoices = $purchaseQuery->get();

        $registered = [
            'invoice_count' => 0,
            'taxable_value' => 0.0,
            'cgst'          => 0.0,
            'sgst'          => 0.0,
            'igst'          => 0.0,
        ];
        $unregistered = [
            'invoice_count' => 0,
            'taxable_value' => 0.0,
            'cgst'          => 0.0,
            'sgst'          => 0.0,
            'igst'          => 0.0,
        ];

        foreach ($invoices as $inv) {
            $taxableAmount = (float) ($inv->total_amount ?? 0);
            $cgstAmount    = 0.0;
            $sgstAmount    = 0.0;
            $igstAmount    = 0.0;

            // Parse taxes JSON to accumulate components
            if (! empty($inv->taxes)) {
                $taxes = json_decode($inv->taxes, true);
                if (is_array($taxes)) {
                    foreach ($taxes as $tax) {
                        $name   = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                        $amount = (float) ($tax['amount'] ?? 0);
                        if ($name === '' && isset($tax['tax_id'])) {
                            $tr = TaxRate::find($tax['tax_id']);
                            if ($tr) {
                                $name = strtoupper(trim($tr->tax_name));
                            }
                        }
                        if ($name === '' && isset($tax['id'])) {
                            $tr = TaxRate::find($tax['id']);
                            if ($tr) {
                                $name = strtoupper(trim($tr->tax_name));
                            }
                        }

                        if ($name === 'CGST') {
                            $cgstAmount += $amount;
                        } elseif ($name === 'SGST') {
                            $sgstAmount += $amount;
                        } elseif ($name === 'IGST') {
                            $igstAmount += $amount;
                        }
                    }
                }
            }

            $isRegistered = false;
            if ($inv->relationLoaded('vendor') && $inv->vendor) {
                $gstNum       = trim((string) ($inv->vendor->gst_number ?? ''));
                $isRegistered = $gstNum !== '';
            }

            if ($isRegistered) {
                $registered['invoice_count'] += 1;
                $registered['taxable_value'] += $taxableAmount;
                $registered['cgst']          += $cgstAmount;
                $registered['sgst']          += $sgstAmount;
                $registered['igst']          += $igstAmount;
            } else {
                $unregistered['invoice_count'] += 1;
                $unregistered['taxable_value'] += $taxableAmount;
                $unregistered['cgst']          += $cgstAmount;
                $unregistered['sgst']          += $sgstAmount;
                $unregistered['igst']          += $igstAmount;
            }
        }

        $summary = [
            'total_invoices' => $registered['invoice_count'] + $unregistered['invoice_count'],
            'taxable_value'  => round($registered['taxable_value'] + $unregistered['taxable_value'], 2),
            'cgst'           => round($registered['cgst'] + $unregistered['cgst'], 2),
            'sgst'           => round($registered['sgst'] + $unregistered['sgst'], 2),
            'igst'           => round($registered['igst'] + $unregistered['igst'], 2),
        ];

        $registered = array_map(function ($v) {return is_numeric($v) ? round($v, 2) : $v;}, $registered);
        $unregistered = array_map(function ($v) {return is_numeric($v) ? round($v, 2) : $v;}, $unregistered);

        return response()->json([
            'success'           => true,
            'filter'            => $request->query('filter'),
            'from_date'         => $from?->toDateString(),
            'to_date'           => $to?->toDateString(),
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'registered'        => $registered,
            'unregistered'      => $unregistered,
            'summary'           => $summary,
        ]);
    }

    public function gstr2Pdf(Request $request)
    {
        // reuse the logic from gstr2()
        $response = $this->gstr2($request);
        $data     = $response->getData(true); // convert JsonResponse to array

        $pdf = PDF::loadView('gst.gstr2_pdf', ['data' => $data])
            ->setPaper('A4', 'portrait');

        return $pdf->download('gstr2_report.pdf');
    }

    public function gstr9c(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates
        $activeTaxes      = TaxRate::where('status', 'active')->get();
        $cgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        // Outward (sales)
        $salesQuery = Order::with(['orderItems' => function ($q) {$q->where('isDeleted', 0);}])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');
        if ($from && $to) {$salesQuery->whereBetween('created_at', [$from, $to]);}
        $orders = $salesQuery->get();

        $turnoverTaxable = 0.0;
        $outCGST         = 0.0;
        $outSGST         = 0.0;
        $outIGST         = 0.0;
        foreach ($orders as $order) {
            $subtotal         = $order->orderItems->sum(fn($i) => (float) $i->price * (float) $i->quantity);
            $discountPercent  = (float) ($order->discount ?? 0);
            $discountAmount   = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount    = max(0, $subtotal - $discountAmount);
            $turnoverTaxable += $taxableAmount;

            if ($cgstRate > 0) {
                $outCGST += ($taxableAmount * $cgstRate) / 100.0;
            }

            if ($sgstRate > 0) {
                $outSGST += ($taxableAmount * $sgstRate) / 100.0;
            }

            if ($igstRate > 0) {
                $outIGST += ($taxableAmount * $igstRate) / 100.0;
            }

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax  = ($taxableAmount * $totalRatePercent) / 100.0;
                $outCGST  += $totalTax / 2.0;
                $outSGST  += $totalTax / 2.0;
            }
        }

        // Inward (purchases)
        $purchaseQuery = PurchaseInvoice::query()->where('isDeleted', 0);
        if ($from && $to) {$purchaseQuery->whereBetween('created_at', [$from, $to]);}
        $purchaseInvoices = $purchaseQuery->get();

        $purchaseTaxable = 0.0;
        $itcCGST         = 0.0;
        $itcSGST         = 0.0;
        $itcIGST         = 0.0;
        foreach ($purchaseInvoices as $pi) {
            $purchaseTaxable += (float) ($pi->total_amount ?? 0);
            if (empty($pi->taxes)) {
                continue;
            }

            $taxes = json_decode($pi->taxes, true);
            if (! is_array($taxes)) {
                continue;
            }

            foreach ($taxes as $tax) {
                $name   = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                $amount = (float) ($tax['amount'] ?? 0);
                if ($name === '' && isset($tax['tax_id'])) {$tr = TaxRate::find($tax['tax_id']);if ($tr) {
                    $name = strtoupper(trim($tr->tax_name));
                }}
                if ($name === '' && isset($tax['id'])) {$tr = TaxRate::find($tax['id']);if ($tr) {
                    $name = strtoupper(trim($tr->tax_name));
                }}

                if ($name === 'CGST') {
                    $itcCGST += $amount;
                } elseif ($name === 'SGST') {
                    $itcSGST += $amount;
                } elseif ($name === 'IGST') {
                    $itcIGST += $amount;
                } else {
                    if ($name === '' && $cgstRate > 0 && $sgstRate > 0 && $igstRate === 0) {
                        $itcCGST += $amount / 2.0;
                        $itcSGST += $amount / 2.0;
                    }
                }
            }
        }

        $turnover = [
            'taxable_value' => round($turnoverTaxable, 2),
            'cgst'          => round($outCGST, 2),
            'sgst'          => round($outSGST, 2),
            'igst'          => round($outIGST, 2),
        ];
        $itc = [
            'taxable_value' => round($purchaseTaxable, 2),
            'cgst'          => round($itcCGST, 2),
            'sgst'          => round($itcSGST, 2),
            'igst'          => round($itcIGST, 2),
        ];

        // Simple reconciliation indicators
        $recon  = [
            'payable_cgst' => round(max(0, $outCGST - $itcCGST), 2),
            'payable_sgst' => round(max(0, $outSGST - $itcSGST), 2),
            'payable_igst' => round(max(0, $outIGST - $itcIGST), 2),
            'note'         => 'System-generated reconciliation summary for reference only.',
        ];

        return response()->json([
            'success'           => true,
            'filter'            => $request->query('filter'),
            'from_date'         => $from?->toDateString(),
            'to_date'           => $to?->toDateString(),
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'turnover'          => $turnover,
            'itc'               => $itc,
            'reconciliation'    => $recon,
        ]);
    }

    public function exportGstr9c(Request $request)
    {
        // Reuse existing logic
        $data = $this->gstr9c($request)->getData(true);

        $html  = '<h2 style="text-align:center;">GSTR-9C Reconciliation Report</h2>';
        $html .= '<p><b>Period:</b> ' . ($data['from_date'] ?? '-') . ' to ' . ($data['to_date'] ?? '-') . '</p>';

        // Turnover
        $t     = $data['turnover'];
        $html .= '<h3>Outward Supplies (Turnover)</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f2f2f2;">
                <th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $t['taxable_value'] . '</td>
                <td>' . $t['cgst'] . '</td>
                <td>' . $t['sgst'] . '</td>
                <td>' . $t['igst'] . '</td>
            </tr>
        </table>';

        // ITC
        $i     = $data['itc'];
        $html .= '<h3>Inward Supplies (ITC)</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f9f9f9;">
                <th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $i['taxable_value'] . '</td>
                <td>' . $i['cgst'] . '</td>
                <td>' . $i['sgst'] . '</td>
                <td>' . $i['igst'] . '</td>
            </tr>
        </table>';

        // Reconciliation
        $r     = $data['reconciliation'];
        $html .= '<h3>Reconciliation</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#e9ecef;">
                <th>Payable CGST</th><th>Payable SGST</th><th>Payable IGST</th><th>Note</th>
            </tr>
            <tr>
                <td>' . $r['payable_cgst'] . '</td>
                <td>' . $r['payable_sgst'] . '</td>
                <td>' . $r['payable_igst'] . '</td>
                <td>' . $r['note'] . '</td>
            </tr>
        </table>';

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        return $pdf->download('gstr9c_report.pdf');
    }

    public function gstr9(Request $request)
    {
        [$from, $to] = $this->resolveDateRange($request);

        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Active tax rates
        $activeTaxes      = TaxRate::where('status', 'active')->get();
        $cgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'CGST')->tax_rate ?? 0;
        $sgstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'SGST')->tax_rate ?? 0;
        $igstRate         = $activeTaxes->firstWhere(fn($t) => strtoupper($t->tax_name) === 'IGST')->tax_rate ?? 0;
        $totalRatePercent = $activeTaxes->sum('tax_rate');

        // Outward supplies (sales)
        $salesQuery = Order::with(['orderItems' => function ($q) {$q->where('isDeleted', 0);}])
            ->where('isDeleted', 0)
            ->where('payment_status', 'completed');
        if ($from && $to) {$salesQuery->whereBetween('created_at', [$from, $to]);}
        $orders = $salesQuery->get();

        $outInvoices = $orders->count();
        $outTaxable  = 0.0;
        $outCGST     = 0.0;
        $outSGST     = 0.0;
        $outIGST     = 0.0;
        foreach ($orders as $order) {
            $subtotal         = $order->orderItems->sum(fn($i) => (float) $i->price * (float) $i->quantity);
            $discountPercent  = (float) ($order->discount ?? 0);
            $discountAmount   = ($subtotal * $discountPercent) / 100.0;
            $taxableAmount    = max(0, $subtotal - $discountAmount);
            $outTaxable      += $taxableAmount;

            if ($cgstRate > 0) {
                $outCGST += ($taxableAmount * $cgstRate) / 100.0;
            }

            if ($sgstRate > 0) {
                $outSGST += ($taxableAmount * $sgstRate) / 100.0;
            }

            if ($igstRate > 0) {
                $outIGST += ($taxableAmount * $igstRate) / 100.0;
            }

            if ($cgstRate === 0 && $sgstRate === 0 && $igstRate === 0 && $totalRatePercent > 0) {
                $totalTax  = ($taxableAmount * $totalRatePercent) / 100.0;
                $outCGST  += $totalTax / 2.0;
                $outSGST  += $totalTax / 2.0;
            }
        }

        // Inward supplies (purchases)
        $purchaseQuery = PurchaseInvoice::query()->where('isDeleted', 0);
        if ($from && $to) {$purchaseQuery->whereBetween('created_at', [$from, $to]);}
        $purchases  = $purchaseQuery->get();
        $inInvoices = $purchases->count();

        $inTaxable = 0.0;
        $itcCGST   = 0.0;
        $itcSGST   = 0.0;
        $itcIGST   = 0.0;
        foreach ($purchases as $pi) {
            $inTaxable += (float) ($pi->total_amount ?? 0);
            if (empty($pi->taxes)) {
                continue;
            }

            $taxes = json_decode($pi->taxes, true);
            if (! is_array($taxes)) {
                continue;
            }

            foreach ($taxes as $tax) {
                $name   = strtoupper(trim($tax['name'] ?? $tax['tax_name'] ?? ''));
                $amount = (float) ($tax['amount'] ?? 0);
                if ($name === '' && isset($tax['tax_id'])) {$tr = TaxRate::find($tax['tax_id']);if ($tr) {
                    $name = strtoupper(trim($tr->tax_name));
                }}
                if ($name === '' && isset($tax['id'])) {$tr = TaxRate::find($tax['id']);if ($tr) {
                    $name = strtoupper(trim($tr->tax_name));
                }}

                if ($name === 'CGST') {
                    $itcCGST += $amount;
                } elseif ($name === 'SGST') {
                    $itcSGST += $amount;
                } elseif ($name === 'IGST') {
                    $itcIGST += $amount;
                } else {
                    if ($name === '' && $cgstRate > 0 && $sgstRate > 0 && $igstRate === 0) {
                        $itcCGST += $amount / 2.0;
                        $itcSGST += $amount / 2.0;
                    }
                }
            }
        }

        $outward = [
            'invoice_count' => $outInvoices,
            'taxable_value' => round($outTaxable, 2),
            'cgst'          => round($outCGST, 2),
            'sgst'          => round($outSGST, 2),
            'igst'          => round($outIGST, 2),
        ];
        $inward = [
            'invoice_count' => $inInvoices,
            'taxable_value' => round($inTaxable, 2),
            'cgst'          => round($itcCGST, 2),
            'sgst'          => round($itcSGST, 2),
            'igst'          => round($itcIGST, 2),
        ];

        $net  = [
            'payable_cgst' => round(max(0, $outCGST - $itcCGST), 2),
            'payable_sgst' => round(max(0, $outSGST - $itcSGST), 2),
            'payable_igst' => round(max(0, $outIGST - $itcIGST), 2),
        ];

        return response()->json([
            'success'           => true,
            'filter'            => $request->query('filter'),
            'from_date'         => $from?->toDateString(),
            'to_date'           => $to?->toDateString(),
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'outward'           => $outward,
            'inward'            => $inward,
            'net'               => $net,
        ]);
    }

    public function exportGstr9(Request $request)
    {
        // Get the same data JSON as API
        $data = $this->gstr9($request)->getData(true);

        $html  = '<h2 style="text-align:center;">GSTR-9 Annual Return</h2>';
        $html .= '<p><b>Period:</b> ' . ($data['from_date'] ?? '-') . ' to ' . ($data['to_date'] ?? '-') . '</p>';

        // Outward Supplies
        $o     = $data['outward'];
        $html .= '<h3>Outward Supplies</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f2f2f2;">
                <th>Invoices</th><th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $o['invoice_count'] . '</td>
                <td>' . $o['taxable_value'] . '</td>
                <td>' . $o['cgst'] . '</td>
                <td>' . $o['sgst'] . '</td>
                <td>' . $o['igst'] . '</td>
            </tr>
        </table>';

        // Inward Supplies
        $i     = $data['inward'];
        $html .= '<h3>Inward Supplies (ITC)</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#f9f9f9;">
                <th>Invoices</th><th>Taxable Value</th><th>CGST</th><th>SGST</th><th>IGST</th>
            </tr>
            <tr>
                <td>' . $i['invoice_count'] . '</td>
                <td>' . $i['taxable_value'] . '</td>
                <td>' . $i['cgst'] . '</td>
                <td>' . $i['sgst'] . '</td>
                <td>' . $i['igst'] . '</td>
            </tr>
        </table>';

        // Net Tax Payable
        $n     = $data['net'];
        $html .= '<h3>Net Tax Payable</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr style="background:#e9ecef;">
                <th>Payable CGST</th><th>Payable SGST</th><th>Payable IGST</th>
            </tr>
            <tr>
                <td>' . $n['payable_cgst'] . '</td>
                <td>' . $n['payable_sgst'] . '</td>
                <td>' . $n['payable_igst'] . '</td>
            </tr>
        </table>';

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');
        return $pdf->download('gstr9_report.pdf');
    }

    private function resolveDateRange(Request $request): array
    {
        $now    = Carbon::now();
        $filter = $request->query('filter');
        $from   = null;
        $to     = null;

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = Carbon::parse($request->query('from_date'))->startOfDay();
            $to   = Carbon::parse($request->query('to_date'))->endOfDay();
            return [$from, $to];
        }

        if ($request->filled('year')) {
            $year = $request->query('year');
            $from = Carbon::create($year, 1, 1)->startOfDay();
            $to   = Carbon::create($year, 12, 31)->endOfDay();
            return [$from, $to];
        }

        switch ($filter) {
            case 'this_week':
                $from = $now->copy()->startOfWeek();
                $to   = $now->copy()->endOfWeek();
                break;
            case 'this_month':
                $from = $now->copy()->startOfMonth();
                $to   = $now->copy()->endOfMonth();
                break;
            case 'last_6_months':
                $from = $now->copy()->subMonths(6)->startOfDay();
                $to   = $now->copy()->endOfDay();
                break;
            case 'this_year':
                $from = $now->copy()->startOfYear();
                $to   = $now->copy()->endOfYear();
                break;
            case 'previous_year':
                $from = $now->copy()->subYear()->startOfYear();
                $to   = $now->copy()->subYear()->endOfYear();
                break;
            default:
                // Default to current month if no filter/dates provided
                $from = $now->copy()->startOfMonth();
                $to   = $now->copy()->endOfMonth();
        }
        return [$from, $to];
    }
}