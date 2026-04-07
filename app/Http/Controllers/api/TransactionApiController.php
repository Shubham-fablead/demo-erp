<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentStore;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionApiController extends Controller
{
    private function resolveBranchId(Request $request)
    {
        $user               = Auth::guard('api')->user();
        $selectedSubAdminId = $request->selectedSubAdminId;

        if (!$user) {
            return null;
        }

        if ($user->role === 'staff' && $user->branch_id) {
            return $user->branch_id;
        }

        if ($user->role === 'admin' && !empty($selectedSubAdminId)) {
            return $selectedSubAdminId;
        }

        return $user->id;
    }

    /**
     * BANK BOOK API
     */
    // public function bankBook(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);
    //     $user = Auth::guard('api')->user();

    //     // Base query for Grand Totals
    //     // $baseQuery = PaymentStore::where('isDeleted', 0);
    //     $baseQuery = PaymentStore::where('isDeleted', 0)
    //         ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']); // ✅ ONLINE & SCAN


    //     // Apply branch filter if not admin, OR if admin and selectedSubAdminId is provided
    //     if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
    //         $baseQuery->where(function ($q) use ($branchId) {
    //             $q->whereHas('bank', function ($b) use ($branchId) {
    //                 $b->where('branch_id', $branchId);
    //             })
    //                 ->orWhere(function ($sub) use ($branchId) {
    //                     $sub->whereNull('bank_id')
    //                         ->whereHas('user', function ($u) use ($branchId) {
    //                             $u->where('branch_id', $branchId)->orWhere('id', $branchId);
    //                         });
    //                 });
    //         });
    //     }

    //     $grandTotalDebit = (float) (clone $baseQuery)->where('status', 'debit')->sum('payment_amount');
    //     $grandTotalCredit = (float) (clone $baseQuery)->where('status', 'credit')->sum('payment_amount');

    //     $query = PaymentStore::with([
    //         'order:id,order_number',
    //         'customInvoice:id,invoice_number',
    //         'purchase.invoice:id,invoice_number',
    //         'purchase.vendor:id,name',
    //         'user:id,name,branch_id',
    //         'bank:id,bank_name,branch_id'
    //     ])
    //         ->where('isDeleted', 0)
    //         ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']); // ✅ ONLINE & SCAN

    //     if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
    //         $query->where(function ($q) use ($branchId) {
    //             $q->whereHas('bank', function ($b) use ($branchId) {
    //                 $b->where('branch_id', $branchId);
    //             })
    //                 ->orWhere(function ($sub) use ($branchId) {
    //                     $sub->whereNull('bank_id')
    //                         ->whereHas('user', function ($u) use ($branchId) {
    //                             $u->where('branch_id', $branchId)->orWhere('id', $branchId);
    //                         });
    //                 });
    //         });
    //     }

    //     // Apply filters
    //     // if ($request->filled('date')) {
    //     //     $query->whereDate('payment_date', $request->date);
    //     // }
    //     if ($request->filled('month')) {
    //         $query->whereMonth('payment_date', $request->month);
    //     }
    //     if ($request->filled('year')) {
    //         $query->whereYear('payment_date', $request->year);
    //     }
    //     // ✅ DATE RANGE FILTER (FIX)
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $query->whereBetween('payment_date', [
    //             $request->from_date . ' 00:00:00',
    //             $request->to_date . ' 23:59:59'
    //         ]);
    //     } elseif ($request->filled('from_date')) {
    //         $query->whereDate('payment_date', '>=', $request->from_date);
    //     } elseif ($request->filled('to_date')) {
    //         $query->whereDate('payment_date', '<=', $request->to_date);
    //     }

    //     if ($request->filled('bank_id')) {
    //         $query->where('bank_id', $request->bank_id);
    //     }
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     // Apply search
    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('order', function ($o) use ($search) {
    //                 $o->where('order_number', 'like', "%{$search}%");
    //             })
    //                 ->orWhereHas('purchase.invoice', function ($pi) use ($search) {
    //                     $pi->where('invoice_number', 'like', "%{$search}%");
    //                 })
    //                 ->orWhereHas('user', function ($u) use ($search) {
    //                     $u->where('name', 'like', "%{$search}%");
    //                 })
    //                 ->orWhereHas('purchase.vendor', function ($v) use ($search) {
    //                     $v->where('name', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     // $payments = $query->orderBy('payment_date', 'asc')->get();
    //     $payments = $query
    //         ->orderBy('payment_date', 'desc')   // latest first
    //         ->orderBy('id', 'desc')              // safety for same date
    //         ->get();

    //     $data = $payments->map(function ($p) {
    //         // Invoice No = order_number from orders table
    //         $invoiceNo = $p->order->order_number ?? '-';

    //         // Order No = invoice_number from purchase_invoice table
    //         $orderNo = $p->purchase->invoice->invoice_number ?? '-';

    //         // Particulars Name
    //         $name = '-';
    //         if ($p->status === 'credit') {
    //             // Receipt Side - Customer Name
    //             $name = $p->user->name ?? '-';
    //         } else {
    //             // Payment Side - Vendor Name
    //             $name = $p->purchase->vendor->name ?? ($p->user->name ?? '-');
    //         }

    //         return [
    //             'invoice_no'     => $invoiceNo,
    //             'order_no'       => $orderNo,
    //             'particulars'    => $name,
    //             'payment_amount' => number_format((float) $p->payment_amount, 2, '.', ''),
    //             'payment_date'   => Carbon::parse($p->payment_date)->format('d-m-Y'),
    //             'bank_name'      => $p->bank->bank_name ?? 'Cash',
    //             'status'         => $p->status,
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'count'   => $data->count(),
    //         'data'    => $data,
    //         'grand_total_debit'  => number_format($grandTotalDebit, 2, '.', ''),
    //         'grand_total_credit' => number_format($grandTotalCredit, 2, '.', ''),
    //         'grand_closing_balance' =>
    //         number_format($grandTotalCredit - $grandTotalDebit, 2, '.', ''),
    //     ], 200);
    // }
    // public function bankBook(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);
    //     $user = Auth::guard('api')->user();

    //     $perPage = $request->get('per_page', 10);
    //     $search  = $request->get('search', '');

    //     // Base query for totals (unchanged)
    //     $baseQuery = PaymentStore::where('isDeleted', 0)
    //         ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']);

    //     if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
    //         $baseQuery->where(function ($q) use ($branchId) {
    //             $q->whereHas('bank', function ($b) use ($branchId) {
    //                 $b->where('branch_id', $branchId);
    //             })->orWhere(function ($sub) use ($branchId) {
    //                 $sub->whereNull('bank_id')
    //                     ->whereHas('user', function ($u) use ($branchId) {
    //                         $u->where('branch_id', $branchId)->orWhere('id', $branchId);
    //                     });
    //             });
    //         });
    //     }

    //     $grandTotalDebit  = (float) (clone $baseQuery)->where('status', 'debit')->sum('payment_amount');
    //     $grandTotalCredit = (float) (clone $baseQuery)->where('status', 'credit')->sum('payment_amount');

    //     // Main query with relations
    //     $query = PaymentStore::with([
    //         'order:id,order_number',
    //         'customInvoice:id,invoice_number',
    //         'purchase.invoice:id,invoice_number',
    //         'purchase.vendor:id,name',
    //         'user:id,name,branch_id',
    //         'bank:id,bank_name,branch_id'
    //     ])
    //         ->where('isDeleted', 0)
    //         ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']);

    //     if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
    //         $query->where(function ($q) use ($branchId) {
    //             $q->whereHas('bank', function ($b) use ($branchId) {
    //                 $b->where('branch_id', $branchId);
    //             })->orWhere(function ($sub) use ($branchId) {
    //                 $sub->whereNull('bank_id')
    //                     ->whereHas('user', function ($u) use ($branchId) {
    //                         $u->where('branch_id', $branchId)->orWhere('id', $branchId);
    //                     });
    //             });
    //         });
    //     }

    //     // Apply filters (date range, year, bank, status)
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $query->whereBetween('payment_date', [
    //             $request->from_date . ' 00:00:00',
    //             $request->to_date . ' 23:59:59'
    //         ]);
    //     } elseif ($request->filled('from_date')) {
    //         $query->whereDate('payment_date', '>=', $request->from_date);
    //     } elseif ($request->filled('to_date')) {
    //         $query->whereDate('payment_date', '<=', $request->to_date);
    //     }

    //     if ($request->filled('year')) {
    //         $query->whereYear('payment_date', $request->year);
    //     }
    //     if ($request->filled('bank_id')) {
    //         $query->where('bank_id', $request->bank_id);
    //     }
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     // Apply search
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('order', function ($o) use ($search) {
    //                 $o->where('order_number', 'like', "%{$search}%");
    //             })->orWhereHas('purchase.invoice', function ($pi) use ($search) {
    //                 $pi->where('invoice_number', 'like', "%{$search}%");
    //             })->orWhereHas('user', function ($u) use ($search) {
    //                 $u->where('name', 'like', "%{$search}%");
    //             })->orWhereHas('purchase.vendor', function ($v) use ($search) {
    //                 $v->where('name', 'like', "%{$search}%");
    //             })->orWhereHas('bank', function ($b) use ($search) {
    //                 $b->where('bank_name', 'like', "%{$search}%");
    //             })->orWhere(DB::raw("CAST(payment_amount AS CHAR)"), 'LIKE', "%{$search}%");
    //         });
    //     }
    //     // Paginate
    //     $payments = $query->orderBy('payment_date', 'desc')
    //         ->orderBy('id', 'desc')
    //         ->paginate($perPage);

    //     $data = $payments->map(function ($p) {
    //         $invoiceNo = $p->order->order_number ?? '-';
    //         $orderNo   = $p->purchase->invoice->invoice_number ?? '-';
    //         $name = '-';
    //         if ($p->status === 'credit') {
    //             $name = $p->user->name ?? '-';
    //         } else {
    //             $name = $p->purchase->vendor->name ?? ($p->user->name ?? '-');
    //         }
    //         return [
    //             'invoice_no'     => $invoiceNo,
    //             'order_no'       => $orderNo,
    //             'particulars'    => $name,
    //             'payment_amount' => number_format((float) $p->payment_amount, 2, '.', ''),
    //             'payment_date'   => Carbon::parse($p->payment_date)->format('d-m-Y'),
    //             'bank_name'      => $p->bank->bank_name ?? 'Cash',
    //             'status'         => $p->status,
    //         ];
    //     });

    //     return response()->json([
    //         'success' => true,
    //         'data'    => $data,
    //         'pagination' => [
    //             'current_page' => $payments->currentPage(),
    //             'last_page'    => $payments->lastPage(),
    //             'per_page'     => $payments->perPage(),
    //             'total'        => $payments->total(),
    //             'from'         => $payments->firstItem(),
    //             'to'           => $payments->lastItem(),
    //         ],
    //         'grand_total_debit'  => number_format($grandTotalDebit, 2, '.', ''),
    //         'grand_total_credit' => number_format($grandTotalCredit, 2, '.', ''),
    //         'grand_closing_balance' => number_format($grandTotalCredit - $grandTotalDebit, 2, '.', ''),
    //     ], 200);
    // }
    public function bankBook(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $perPage = $request->get('per_page', 10);
        $search  = $request->get('search', '');

        // ------------------------------------------------------------
        // 1. BASE QUERY (for grand totals, unfiltered)
        // ------------------------------------------------------------
        $baseQuery = PaymentStore::where('isDeleted', 0)
            ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']);

        // Branch/user filtering (applies to all queries)
        if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
            $baseQuery->where(function ($q) use ($branchId) {
                $q->whereHas('bank', function ($b) use ($branchId) {
                    $b->where('branch_id', $branchId);
                })->orWhere(function ($sub) use ($branchId) {
                    $sub->whereNull('bank_id')
                        ->whereHas('user', function ($u) use ($branchId) {
                            $u->where('branch_id', $branchId)->orWhere('id', $branchId);
                        });
                });
            });
        }

        // Grand totals (unfiltered)
        $grandTotalDebit  = (float) (clone $baseQuery)->where('status', 'debit')->sum('payment_amount');
        $grandTotalCredit = (float) (clone $baseQuery)->where('status', 'credit')->sum('payment_amount');

        // ------------------------------------------------------------
        // 2. MAIN QUERY (with all filters + search + pagination)
        // ------------------------------------------------------------
        $query = PaymentStore::with([
            'order:id,order_number',
            'customInvoice:id,invoice_number',
            'purchaseInvoice:id,invoice_number',
            'purchase:id,invoice_id',
            'purchase.vendor:id,name',
            'user:id,name,branch_id',
            'bank:id,bank_name,branch_id'
        ])
            ->where('isDeleted', 0)
            ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']);

        // Apply branch/user filtering
        if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('bank', function ($b) use ($branchId) {
                    $b->where('branch_id', $branchId);
                })->orWhere(function ($sub) use ($branchId) {
                    $sub->whereNull('bank_id')
                        ->whereHas('user', function ($u) use ($branchId) {
                            $u->where('branch_id', $branchId)->orWhere('id', $branchId);
                        });
                });
            });
        }

        // Date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('year')) {
            $query->whereYear('payment_date', $request->year);
        }
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($o) use ($search) {
                    $o->where('order_number', 'like', "%{$search}%");
                })->orWhereHas('purchaseInvoice', function ($pi) use ($search) {
                    $pi->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhereHas('purchase.vendor', function ($v) use ($search) {
                    $v->where('name', 'like', "%{$search}%");
                })->orWhereHas('bank', function ($b) use ($search) {
                    $b->where('bank_name', 'like', "%{$search}%");
                })->orWhere(DB::raw("CAST(payment_amount AS CHAR)"), 'LIKE', "%{$search}%");
            });
        }

        // ------------------------------------------------------------
        // 3. TOTAL FOR CURRENT TAB (including all filters + search)
        // ------------------------------------------------------------
        $currentTabTotal = (float) (clone $query)->sum('payment_amount');

        // ------------------------------------------------------------
        // 4. PAGINATE
        // ------------------------------------------------------------
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        // ------------------------------------------------------------
        // 5. MAP DATA
        // ------------------------------------------------------------
        $data = $payments->map(function ($p) {
            $refNo = '-';
            if ($p->status === 'debit') {
                if ($p->purchaseInvoice?->invoice_number) {
                    $refNo = $p->purchaseInvoice->invoice_number;
                } elseif ($p->customInvoice?->invoice_number) {
                    $refNo = $p->customInvoice->invoice_number;
                } elseif ($p->order?->order_number) {
                    $refNo = $p->order->order_number;
                }
            } else {
                if ($p->order?->order_number) {
                    $refNo = $p->order->order_number;
                } elseif ($p->customInvoice?->invoice_number) {
                    $refNo = $p->customInvoice->invoice_number;
                }
            }

            $name = '-';
            if ($p->status === 'credit') {
                $name = $p->user->name ?? '-';
            } else {
                $name = $p->purchase->vendor->name ?? ($p->user->name ?? '-');
            }

            return [
                'invoice_no'     => ($p->status === 'credit') ? $refNo : '-',
                'order_no'       => ($p->status === 'debit') ? $refNo : '-',
                'particulars'    => $name,
                'payment_amount' => number_format((float) $p->payment_amount, 2, '.', ''),
                'payment_date'   => Carbon::parse($p->payment_date)->format('d-m-Y'),
                'bank_name'      => $p->bank->bank_name ?? 'Cash',
                'status'         => $p->status,
            ];
        });

        // ------------------------------------------------------------
        // 6. RESPONSE
        // ------------------------------------------------------------
        return response()->json([
            'success' => true,
            'data'    => $data,
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page'    => $payments->lastPage(),
                'per_page'     => $payments->perPage(),
                'total'        => $payments->total(),
                'from'         => $payments->firstItem(),
                'to'           => $payments->lastItem(),
            ],
            'grand_total_debit'     => number_format($grandTotalDebit, 2, '.', ''),
            'grand_total_credit'    => number_format($grandTotalCredit, 2, '.', ''),
            'grand_closing_balance' => number_format($grandTotalCredit - $grandTotalDebit, 2, '.', ''),
            'current_tab_total'     => number_format($currentTabTotal, 2, '.', ''),
        ], 200);
    }
    // public function export_bankbook_pdf(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);
    //     $user = Auth::guard('api')->user();

    //     $query = PaymentStore::with([
    //         'order:id,order_number',
    //         'customInvoice:id,invoice_number',
    //         'purchase.invoice:id,invoice_number',
    //         'purchase.vendor:id,name',
    //         'user:id,name,branch_id',
    //         'bank:id,bank_name,branch_id'
    //     ])
    //         ->where('isDeleted', 0)
    //         ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan']);

    //     if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
    //         $query->where(function ($q) use ($branchId) {
    //             $q->whereHas('bank', function ($b) use ($branchId) {
    //                 $b->where('branch_id', $branchId);
    //             })
    //                 ->orWhere(function ($sub) use ($branchId) {
    //                     $sub->whereNull('bank_id')
    //                         ->whereHas('user', function ($u) use ($branchId) {
    //                             $u->where('branch_id', $branchId)->orWhere('id', $branchId);
    //                         });
    //                 });
    //         });
    //     }

    //     if ($request->filled('month')) {
    //         $query->whereMonth('payment_date', $request->month);
    //     }
    //     if ($request->filled('year')) {
    //         $query->whereYear('payment_date', $request->year);
    //     }
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $query->whereBetween('payment_date', [
    //             $request->from_date . ' 00:00:00',
    //             $request->to_date . ' 23:59:59'
    //         ]);
    //     } elseif ($request->filled('from_date')) {
    //         $query->whereDate('payment_date', '>=', $request->from_date);
    //     } elseif ($request->filled('to_date')) {
    //         $query->whereDate('payment_date', '<=', $request->to_date);
    //     }

    //     if ($request->filled('bank_id')) {
    //         $query->where('bank_id', $request->bank_id);
    //     }
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     if ($request->filled('search')) {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->whereHas('order', function ($o) use ($search) {
    //                 $o->where('order_number', 'like', "%{$search}%");
    //             })
    //                 ->orWhereHas('purchase.invoice', function ($pi) use ($search) {
    //                     $pi->where('invoice_number', 'like', "%{$search}%");
    //                 })
    //                 ->orWhereHas('user', function ($u) use ($search) {
    //                     $u->where('name', 'like', "%{$search}%");
    //                 })
    //                 ->orWhereHas('purchase.vendor', function ($v) use ($search) {
    //                     $v->where('name', 'like', "%{$search}%");
    //                 });
    //         });
    //     }

    //     $payments = $query->orderBy('payment_date', 'asc')->get();
    //     $grandTotal = 0;

    //     $data = $payments->map(function ($p) use (&$grandTotal) {
    //         $invoiceNo = $p->order->order_number ?? '-';
    //         $orderNo = $p->purchase->invoice->invoice_number ?? '-';
    //         $name = ($p->status === 'credit') ? ($p->user->name ?? '-') : ($p->purchase->vendor->name ?? ($p->user->name ?? '-'));
    //         $grandTotal += (float) $p->payment_amount;

    //         return [
    //             'invoice_no'     => $invoiceNo,
    //             'order_no'       => $orderNo,
    //             'particulars'    => $name,
    //             'payment_amount' => (float) $p->payment_amount,
    //             'payment_date'   => Carbon::parse($p->payment_date)->format('d-m-Y'),
    //             'bank_name'      => $p->bank->bank_name ?? 'Cash',
    //             'status'         => $p->status,
    //         ];
    //     });

    //     $settings = DB::table('settings')->where('branch_id', $branchId)->first();

    //     $pdf = Pdf::loadView('transaction.bankbook_pdf', [
    //         'data'       => $data,
    //         'grandTotal' => $grandTotal,
    //         'settings'   => $settings,
    //         'status'     => $request->status ?? 'all',
    //     ]);

    //     $fileName = 'BankBook_' . now()->format('Ymd_His') . '.pdf';
    //     $relativePath = 'bankbook-reports/' . $fileName;
    //     Storage::disk('public')->put($relativePath, $pdf->output());

    //     $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

    //     return response()->json([
    //         'success' => true,
    //         'file_url' => $fileUrl,
    //     ], 200);
    // }
    public function export_bankbook_pdf(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $status = $request->get('status', 'credit'); // default to credit

        $query = PaymentStore::with([
            'order:id,order_number',
            'customInvoice:id,invoice_number',
            'purchaseInvoice:id,invoice_number',        // direct relation to purchase_invoice
            'purchase:id,invoice_id',
            'purchase.vendor:id,name',
            'user:id,name,branch_id',
            'bank:id,bank_name,branch_id'
        ])
            ->where('isDeleted', 0)
            ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan']); // maybe include debit card?

        // Branch/user filtering
        if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('bank', function ($b) use ($branchId) {
                    $b->where('branch_id', $branchId);
                })->orWhere(function ($sub) use ($branchId) {
                    $sub->whereNull('bank_id')
                        ->whereHas('user', function ($u) use ($branchId) {
                            $u->where('branch_id', $branchId)->orWhere('id', $branchId);
                        });
                });
            });
        }

        // Apply filters
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('year')) {
            $query->whereYear('payment_date', $request->year);
        }
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($o) use ($search) {
                    $o->where('order_number', 'like', "%{$search}%");
                })->orWhereHas('purchaseInvoice', function ($pi) use ($search) {
                    $pi->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhereHas('purchase.vendor', function ($v) use ($search) {
                    $v->where('name', 'like', "%{$search}%");
                });
            });
        }

        $payments = $query->orderBy('payment_date', 'asc')->get();

        $grandTotal = 0;
        $data = $payments->map(function ($p) use (&$grandTotal) {
            // Determine reference number with fallbacks (same as API)
            $refNo = '-';
            if ($p->status === 'debit') {
                // Payment side
                if ($p->purchaseInvoice && $p->purchaseInvoice->invoice_number) {
                    $refNo = $p->purchaseInvoice->invoice_number;
                } elseif ($p->customInvoice && $p->customInvoice->invoice_number) {
                    $refNo = $p->customInvoice->invoice_number;
                } elseif ($p->order && $p->order->order_number) {
                    $refNo = $p->order->order_number; // last resort
                }
            } else {
                // Receipt side
                if ($p->order && $p->order->order_number) {
                    $refNo = $p->order->order_number;
                } elseif ($p->customInvoice && $p->customInvoice->invoice_number) {
                    $refNo = $p->customInvoice->invoice_number;
                }
            }

            // Particulars
            $name = '-';
            if ($p->status === 'credit') {
                $name = $p->user->name ?? '-';
            } else {
                $name = $p->purchase->vendor->name ?? ($p->user->name ?? '-');
            }

            $amount = (float) $p->payment_amount;
            $grandTotal += $amount;

            return [
                'invoice_no'     => ($p->status === 'credit') ? $refNo : '-',
                'order_no'       => ($p->status === 'debit') ? $refNo : '-',
                'particulars'    => $name,
                'payment_amount' => $amount,
                'payment_date'   => Carbon::parse($p->payment_date)->format('d-m-Y'),
                'bank_name'      => $p->bank->bank_name ?? 'Cash',
                'status'         => $p->status,
            ];
        });

        $settings = DB::table('settings')->where('branch_id', $branchId)->first();

        $pdf = Pdf::loadView('transaction.bankbook_pdf', [
            'data'       => $data,
            'grandTotal' => $grandTotal,
            'settings'   => $settings,
            'status'     => $request->status ?? 'all', // pass status to view if needed
        ]);

        $fileName = 'BankBook_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'bankbook-reports/' . $fileName;
        Storage::disk('public')->put($relativePath, $pdf->output());

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'success' => true,
            'file_url' => $fileUrl,
        ], 200);
    }
    public function export_bankbook_excel(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        // Get status from request, default to 'credit' (Receipts)
        $status = $request->get('status', 'credit');
        // First column header based on status
        $firstColumnHeader = ($status === 'debit') ? 'Invoice No' : 'Order No';

        $query = PaymentStore::with([
            'order:id,order_number',
            'customInvoice:id,invoice_number',
            'purchaseInvoice:id,invoice_number',        // direct relation to purchase_invoice
            'purchase:id,invoice_id',
            'purchase.vendor:id,name',
            'user:id,name,branch_id',
            'bank:id,bank_name,branch_id'
        ])
            ->where('isDeleted', 0)
            ->whereIn(DB::raw('LOWER(payment_method)'), ['online', 'scan', 'debit card']);

        // Branch/user filtering
        if (($user && $user->role !== 'admin') || ($user && $user->role === 'admin' && $request->filled('selectedSubAdminId'))) {
            $query->where(function ($q) use ($branchId) {
                $q->whereHas('bank', function ($b) use ($branchId) {
                    $b->where('branch_id', $branchId);
                })->orWhere(function ($sub) use ($branchId) {
                    $sub->whereNull('bank_id')
                        ->whereHas('user', function ($u) use ($branchId) {
                            $u->where('branch_id', $branchId)->orWhere('id', $branchId);
                        });
                });
            });
        }

        // Apply filters
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('payment_date', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('from_date')) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->filled('year')) {
            $query->whereYear('payment_date', $request->year);
        }
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($o) use ($search) {
                    $o->where('order_number', 'like', "%{$search}%");
                })->orWhereHas('purchaseInvoice', function ($pi) use ($search) {
                    $pi->where('invoice_number', 'like', "%{$search}%");
                })->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                })->orWhereHas('purchase.vendor', function ($v) use ($search) {
                    $v->where('name', 'like', "%{$search}%");
                })->orWhereHas('bank', function ($b) use ($search) {
                    $b->where('bank_name', 'like', "%{$search}%");
                });
            });
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $filename = 'Bank Book' . '.xls';

        $headers = [
            "Content-Type" => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Cache-Control" => "max-age=0",
        ];

        $callback = function () use ($payments, $firstColumnHeader) {
            $totalAmount = 0;

            echo '<?xml version="1.0" encoding="UTF-8"?>';
        ?>
            <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

                <Styles>
                    <Style ss:ID="Header">
                        <Font ss:Bold="1" /><Alignment ss:Horizontal="Center" />
                    </Style>
                    <Style ss:ID="Text">
                        <NumberFormat ss:Format="@" />
                    </Style>
                    <Style ss:ID="Currency">
                        <NumberFormat ss:Format="#,##0.00" />
                    </Style>
                    <Style ss:ID="DateStyle">
                        <NumberFormat ss:Format="dd-mm-yyyy" />
                    </Style>
                </Styles>

                <Worksheet ss:Name="Bank Book">
                    <Table>
                        <!-- Title row (optional) -->
                        <Row>
                            <Cell ss:MergeAcross="4" ss:StyleID="Header">
                                <Data ss:Type="String">Bank Book</Data>
                            </Cell>
                        </Row>
                        <!-- Header row -->
                        <Row>
                            <Cell ss:StyleID="Header"><Data ss:Type="String"><?= $firstColumnHeader ?></Data></Cell>
                            <Cell ss:StyleID="Header"><Data ss:Type="String">Date</Data></Cell>
                            <Cell ss:StyleID="Header"><Data ss:Type="String">Particulars</Data></Cell>
                            <Cell ss:StyleID="Header"><Data ss:Type="String">Bank Name</Data></Cell>
                            <Cell ss:StyleID="Header"><Data ss:Type="String">Amount</Data></Cell>
                        </Row>

                        <?php
                        foreach ($payments as $p) {
                            // Determine reference number with same logic as API
                            $refNo = '-';
                            if ($p->status === 'debit') {
                                // Payment side
                                if ($p->purchaseInvoice && $p->purchaseInvoice->invoice_number) {
                                    $refNo = $p->purchaseInvoice->invoice_number;
                                } elseif ($p->customInvoice && $p->customInvoice->invoice_number) {
                                    $refNo = $p->customInvoice->invoice_number;
                                } elseif ($p->order && $p->order->order_number) {
                                    $refNo = $p->order->order_number; // last resort
                                }
                            } else {
                                // Receipt side
                                if ($p->order && $p->order->order_number) {
                                    $refNo = $p->order->order_number;
                                } elseif ($p->customInvoice && $p->customInvoice->invoice_number) {
                                    $refNo = $p->customInvoice->invoice_number;
                                }
                            }

                            // Date as dd-mm-YYYY string
                            $date = \Carbon\Carbon::parse($p->payment_date)->format('d-m-Y');

                            // Particulars
                            $particulars = $p->status === 'credit'
                                ? ($p->user->name ?? '-')
                                : ($p->purchase->vendor->name ?? ($p->user->name ?? '-'));

                            $bankName = $p->bank->bank_name ?? 'Cash';

                            $amount = (float) $p->payment_amount;
                            $totalAmount += $amount;

                            // Format amount with ₹ and thousands separator
                            $formattedAmount = '₹ ' . number_format($amount, 2);
                        ?>
                            <Row>
                                <Cell ss:StyleID="Text"><Data ss:Type="String"><?= htmlspecialchars($refNo) ?></Data></Cell>
                                <Cell ss:StyleID="DateStyle"><Data ss:Type="String"><?= $date ?></Data></Cell>
                                <Cell><Data ss:Type="String"><?= htmlspecialchars($particulars) ?></Data></Cell>
                                <Cell><Data ss:Type="String"><?= htmlspecialchars($bankName) ?></Data></Cell>
                                <Cell ss:StyleID="Currency"><Data ss:Type="String"><?= $formattedAmount ?></Data></Cell>
                            </Row>
                        <?php
                        }
                        // Total row
                        $totalFormatted = '₹ ' . number_format($totalAmount, 2);
                        ?>
                        <Row>
                            <Cell ss:Index="4" ss:StyleID="Header"><Data ss:Type="String">Total</Data></Cell>
                            <Cell ss:StyleID="Currency"><Data ss:Type="String"><?= $totalFormatted ?></Data></Cell>
                        </Row>
                    </Table>
                </Worksheet>
            </Workbook>
        <?php
        };

        return response()->stream($callback, 200, $headers);
    }
}
