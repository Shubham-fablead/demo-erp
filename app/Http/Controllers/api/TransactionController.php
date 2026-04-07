<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PaymentStore;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TransactionController extends Controller
{
    // public function getCashbookData(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     $userBranchId = $user->branch_id;
    //     $userRole = $user->role;
    //     $selectedSubAdminId = $request->selectedSubAdminId;

    //     if ($userRole === 'staff' && $userBranchId) {
    //         $branchId = $userBranchId;
    //     } elseif ($userRole === 'admin' && $selectedSubAdminId) {
    //         $branchId = $selectedSubAdminId;
    //     } else {
    //         $branchId = $user->id;
    //     }

    //     $query = $this->getCashbookQuery($request, $branchId);

    //     $data = $query->orderBy('payment_store.id', 'desc')
    //         ->get()
    //         ->map(function($item) {
    //             return $this->formatCashbookItem($item);
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'data' => $data
    //     ]);
    // }

    public function getCashbookData(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = $user->branch_id;
        $userRole = $user->role;
        $selectedSubAdminId = $request->selectedSubAdminId;

        if ($userRole === 'staff' && $userBranchId) {
            $branchId = $userBranchId;
        } elseif ($userRole === 'admin' && $selectedSubAdminId) {
            $branchId = $selectedSubAdminId;
        } else {
            $branchId = $user->id;
        }

        $query = $this->getCashbookQuery($request, $branchId);

        // // Apply search if provided (example: search in order_number, user_name)
        // if ($request->filled('search')) {
        //     $search = $request->search;
        //     $query->where(function($q) use ($search) {
        //         $q->where('order_number', 'like', "%{$search}%")
        //           ->orWhere('user_name', 'like', "%{$search}%");
        //     });
        // }
        // Apply search if provided
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                    ->orWhere('order_users.name', 'like', "%{$search}%")
                    ->orWhere('vendors.name', 'like', "%{$search}%")
                    ->orWhere('custom_users.name', 'like', "%{$search}%")
                    ->orWhere('custom_vendors.name', 'like', "%{$search}%")
                    ->orWhere('purchase_invoice.invoice_number', 'like', "%{$search}%")
                    ->orWhere('custom_invoice.invoice_number', 'like', "%{$search}%")
                    ->orWhere('payment_store.payment_amount', 'like', "%{$search}%");
            });
        }

        // ✅ Get total AFTER search filter is applied
        $totalAmount = (clone $query)->sum('payment_store.payment_amount');

        // Paginate
        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderBy('payment_store.id', 'desc')
            ->paginate($perPage, ['*'], 'page', $request->get('page', 1));
        // Get total amount for the filtered dataset (before pagination)
        $totalAmount = $query->sum('payment_amount');

        // Paginate
        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderBy('payment_store.id', 'desc')
            ->paginate($perPage, ['*'], 'page', $request->get('page', 1));

        // Transform items
        $data = $paginated->map(function ($item) {
            return $this->formatCashbookItem($item);
        });

        return response()->json([
            'status' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'total_amount' => $totalAmount,
        ]);
    }

    private function getCashbookQuery(Request $request, $branchId)
    {
        $query = PaymentStore::query()
            ->leftJoin('orders', 'payment_store.order_id', '=', 'orders.id')
            ->leftJoin('users as order_users', 'orders.user_id', '=', 'order_users.id')
            ->leftJoin('purchase_invoice', 'payment_store.purchase_id', '=', 'purchase_invoice.id')
            ->leftJoin('users as vendors', 'purchase_invoice.vendor_id', '=', 'vendors.id')
            ->leftJoin('custom_invoice', 'payment_store.custom_invoice_id', '=', 'custom_invoice.id')
            ->leftJoin('users as custom_users', 'custom_invoice.customer_id', '=', 'custom_users.id')
            ->leftJoin('users as custom_vendors', 'custom_invoice.vendor_id', '=', 'custom_vendors.id')
            ->where('payment_store.isDeleted', 0)
            ->where('payment_store.payment_method', 'cash')
            ->where(function ($q) use ($branchId) {
                $q->where('orders.branch_id', $branchId)
                    ->orWhere('purchase_invoice.branch_id', $branchId)
                    ->orWhere('custom_invoice.branch_id', $branchId);
            });

        if ($request->filled('from_date')) {
            $query->whereDate('payment_store.payment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('payment_store.payment_date', '<=', $request->to_date);
        }
        if ($request->filled('year')) {
            $query->whereYear('payment_store.payment_date', $request->year);
        }

        if ($request->filled('status')) {
            if ($request->status === 'credit') {
                $query->where(function ($q) {
                    $q->where('payment_store.order_id', '>', 0)
                        ->orWhere(function ($sq) {
                            $sq->where('payment_store.custom_invoice_id', '>', 0)
                                ->whereNotNull('custom_invoice.customer_id');
                        });
                });
            } elseif ($request->status === 'debit') {
                $query->where(function ($q) {
                    $q->where('payment_store.purchase_id', '>', 0)
                        ->orWhere(function ($sq) {
                            $sq->where('payment_store.custom_invoice_id', '>', 0)
                                ->whereNotNull('custom_invoice.vendor_id');
                        });
                });
            }
        }

        if ($request->filled('total')) {
            $query->where('payment_store.payment_amount', 'like', '%' . $request->total . '%');
        }

        return $query->select(
            'payment_store.*',
            'orders.order_number',
            'order_users.name as order_user_name',
            'vendors.name as vendor_name',
            'custom_users.name as custom_user_name',
            'custom_vendors.name as custom_vendor_name',
            'custom_invoice.invoice_number as custom_invoice_number',
            'custom_invoice.customer_id as custom_customer_id',
            'custom_invoice.vendor_id as custom_vendor_id',
            'purchase_invoice.invoice_number as purchase_invoice_number'
        );
    }

    private function formatCashbookItem($item)
    {
        // Determine Credit/Debit status
        if ($item->order_id > 0) {
            $item->transaction_status = 'Credit';
            $item->user_name = $item->order_user_name;
            $item->order_number = $item->order_number;
        } elseif ($item->custom_invoice_id > 0) {
            if ($item->custom_customer_id > 0) {
                $item->transaction_status = 'Credit';
                $item->user_name = $item->custom_user_name;
                $item->order_number = ''; // Hide invoice no in Receipts
            } else {
                $item->transaction_status = 'Debit';
                $item->user_name = $item->custom_vendor_name;
                $item->order_number = $item->custom_invoice_number; // Show invoice no in Payments
            }
        } elseif ($item->purchase_id > 0) {
            $item->transaction_status = 'Debit';
            $item->user_name = $item->vendor_name;
            $item->order_number = $item->purchase_invoice_number;
        } else {
            $item->transaction_status = 'N/A';
            $item->user_name = 'N/A';
            $item->order_number = 'N/A';
        }

        /* ✅ FORMAT DATE HERE */
        if (!empty($item->payment_date)) {
            $item->payment_date = Carbon::parse($item->payment_date)
                ->format('d-m-Y'); // 24-02-2025
        }
        return $item;
    }

    public function exportCashbookPdf(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = $user->branch_id;
        $userRole = $user->role;
        $selectedSubAdminId = $request->selectedSubAdminId;

        if ($userRole === 'staff' && $userBranchId) {
            $branchId = $userBranchId;
        } elseif ($userRole === 'admin' && $selectedSubAdminId) {
            $branchId = $selectedSubAdminId;
        } else {
            $branchId = $user->id;
        }

        $query = $this->getCashbookQuery($request, $branchId);
        $data = $query->orderBy('payment_store.id', 'desc')
            ->get()
            ->map(function ($item) {
                return $this->formatCashbookItem($item);
            });

        $settings = DB::table('settings')->first();

        $pdf = Pdf::loadView('transaction.cashbook_pdf', [
            'data' => $data,
            'settings' => $settings,
            'status' => $request->status,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'year' => $request->year
        ]);

        $fileName = 'Cashbook_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'cashbook-reports/' . $fileName;
        Storage::disk('public')->put($relativePath, $pdf->output());

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status' => true,
            'success' => true,
            'message' => 'Cashbook PDF generated successfully.',
            'file_url' => $fileUrl,
            'file_name' => $fileName,
        ]);
    }
    public function exportCashbookExcel(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = $user->branch_id;
        $userRole = $user->role;
        $selectedSubAdminId = $request->selectedSubAdminId;

        if ($userRole === 'staff' && $userBranchId) {
            $branchId = $userBranchId;
        } elseif ($userRole === 'admin' && $selectedSubAdminId) {
            $branchId = $selectedSubAdminId;
        } else {
            $branchId = $user->id;
        }

        $status = $request->get('status', 'credit');
        $firstColumnHeader = ($status === 'debit') ? 'Invoice No' : 'Order No';

        $query = $this->getCashbookQuery($request, $branchId);

        // Apply search (same as getCashbookData)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                    ->orWhere('order_users.name', 'like', "%{$search}%")
                    ->orWhere('vendors.name', 'like', "%{$search}%")
                    ->orWhere('custom_users.name', 'like', "%{$search}%")
                    ->orWhere('custom_vendors.name', 'like', "%{$search}%")
                    ->orWhere('purchase_invoice.invoice_number', 'like', "%{$search}%")
                    ->orWhere('custom_invoice.invoice_number', 'like', "%{$search}%")
                    ->orWhere('payment_store.payment_amount', 'like', "%{$search}%");
            });
        }

        $payments = $query->orderBy('payment_store.id', 'desc')
            ->get()
            ->map(function ($item) {
                return $this->formatCashbookItem($item);
            });

        $filename = 'Cash Book' . '.xls';

        $headers = [
            "Content-Type"        => "application/vnd.ms-excel; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Cache-Control"       => "max-age=0",
        ];

        $callback = function () use ($payments, $firstColumnHeader) {
            $totalAmount = 0;
            echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
            <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
                xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

                <Styles>
                    <Style ss:ID="Title">
                        <Font ss:Bold="1" /><Alignment ss:Horizontal="Center" />
                    </Style>
                    <Style ss:ID="Header">
                        <Font ss:Bold="1" /><Alignment ss:Horizontal="Center" />
                    </Style>
                    <Style ss:ID="Text">
                        <NumberFormat ss:Format="@" /><Alignment ss:Vertical="Center" />
                    </Style>
                    <Style ss:ID="AmountStyle">
                        <Alignment ss:Horizontal="Right" ss:Vertical="Center" />
                    </Style>
                    <Style ss:ID="TotalLabel">
                        <Font ss:Bold="1" /><Alignment ss:Horizontal="Right" ss:Vertical="Center" />
                    </Style>
                    <Style ss:ID="TotalAmount">
                        <Font ss:Bold="1" /><Alignment ss:Horizontal="Right" ss:Vertical="Center" />
                    </Style>
                </Styles>

                <Worksheet ss:Name="Cashbook">
                    <Table>
                        <!-- Title Row -->
                        <Row>
                            <Cell ss:MergeAcross="3" ss:StyleID="Header">
                                <Data ss:Type="String">Cash Book</Data>
                            </Cell>
                        </Row>

                        <!-- Header Row -->
                        <Row ss:Height="20">
                            <Cell ss:StyleID="Header">
                                <Data ss:Type="String"><?= htmlspecialchars($firstColumnHeader) ?></Data>
                            </Cell>
                            <Cell ss:StyleID="Header">
                                <Data ss:Type="String">Date</Data>
                            </Cell>
                            <Cell ss:StyleID="Header">
                                <Data ss:Type="String">Particulars</Data>
                            </Cell>
                            <Cell ss:StyleID="Header">
                                <Data ss:Type="String">Amount</Data>
                            </Cell>
                        </Row>

                        <?php foreach ($payments as $item):
                            $refNo       = $item['order_number']   ?? '-';
                            $date        = $item['payment_date']   ?? '-';
                            $particulars = $item['user_name']      ?? '-';
                            $amount      = (float) ($item['payment_amount'] ?? 0);
                            $totalAmount += $amount;
                            $formattedAmount = '₹' . number_format($amount, 2);
                        ?>
                            <Row>
                                <Cell ss:StyleID="Text">
                                    <Data ss:Type="String"><?= htmlspecialchars($refNo) ?></Data>
                                </Cell>
                                <Cell ss:StyleID="Text">
                                    <Data ss:Type="String"><?= htmlspecialchars($date) ?></Data>
                                </Cell>
                                <Cell ss:StyleID="Text">
                                    <Data ss:Type="String"><?= htmlspecialchars($particulars) ?></Data>
                                </Cell>
                                <Cell ss:StyleID="AmountStyle">
                                    <Data ss:Type="String"><?= htmlspecialchars($formattedAmount) ?></Data>
                                </Cell>
                            </Row>
                        <?php endforeach; ?>

                        <!-- Total Row -->
                        <Row ss:Height="20">
                            <Cell ss:Index="3" ss:StyleID="TotalLabel">
                                <Data ss:Type="String">Total</Data>
                            </Cell>
                            <Cell ss:StyleID="TotalAmount">
                                <Data ss:Type="String">₹<?= number_format($totalAmount, 2) ?></Data>
                            </Cell>
                        </Row>

                    </Table>
                </Worksheet>
            </Workbook>
<?php
        };

        return response()->stream($callback, 200, $headers);
    }
}
