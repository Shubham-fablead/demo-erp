<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentStore;
use App\Models\Purchases;
use App\Models\Setting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AccountLedgerController extends Controller
{

    public function getPaymentDetails(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userId       = $user->id;
        $userBranchId = $user->branch_id;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;
        // dd($selectedSubBranchId);
        // ✅ Branch selection by role
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
        // dd($branch_id);
        try {
            $type       = $request->input('type'); // customer | vendor
            $customerId = $request->input('customer_id');
            $vendorId   = $request->input('vendor_id');
            $month      = $request->input('month');
            $year       = $request->input('year');
            // dd($month);
            $settings = Setting::where('branch_id', $branch_id)
                ->first(['currency_symbol', 'currency_position']);

            $paidPayments    = collect();
            $pendingPayments = collect();

            // === Customer Orders ===
            if ($type === 'customer') {
                $ordersQuery = Order::with('items.product:id,name')
                    ->where('isDeleted', 0);
                // ->where('branch_id', $branch_id);

                // if (!empty($customerId)) {
                //     $ordersQuery->where('user_id', $customerId);
                // }
                // ✅ Staff: see only their created orders
                if ($role === 'staff') {
                    $ordersQuery->where('created_by', $userId);
                } else {
                    $ordersQuery->where('branch_id', $branch_id);
                }
                if (! empty($customerId) && $customerId !== 'all') {
                    $ordersQuery->where('user_id', $customerId);
                }
                if (! empty($month) && $month !== 'all') {
                    $ordersQuery->whereMonth('created_at', $month);
                }
                if (! empty($year)) {
                    $ordersQuery->whereYear('created_at', $year);
                }

                $orders = $ordersQuery->get([
                    'id',
                    'order_number',
                    'total_amount',
                    'remaining_amount',
                    'payment_status',
                    'created_at',
                ]);
                // === Fetch Payments from payment_store ===
                $paymentsQuery = PaymentStore::where('isDeleted', 0)
                    ->whereIn('order_id', $orders->pluck('id'));

                $payments = $paymentsQuery->get([
                    'id',
                    'order_id',
                    'payment_amount',
                    'payment_date',
                    'payment_method',
                    'remaining_amount',
                ]);

                $paidPayments    = collect();
                $pendingPayments = collect();

                foreach ($orders as $order) {
                    // Payments linked to this order
                    $orderPayments = $payments->where('order_id', $order->id);

                    // === Paid ===
                    foreach ($orderPayments as $payment) {
                        $paidPayments->push([
                            'order'          => $order,
                            'total_amount'   => $payment->payment_amount,
                            'payment_date'   => $payment->payment_date,
                            'payment_method' => $payment->payment_method,
                        ]);
                    }

                    // === Pending (remaining) ===
                    if ($order->remaining_amount > 0) {
                        $pendingPayments->push([
                            'order'        => $order,
                            'amount_total' => $order->remaining_amount,
                        ]);
                    }
                }
            } elseif ($type === 'vendor') {
                // === Vendor Purchases ===
                $purchasesQuery = Purchases::query()
                    ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
                    // ->where('purchases.branch_id', $branch_id)
                    ->select(
                        'purchases.id as purchase_id',
                        'purchase_invoice.id as purchase_invoice_id',
                        'purchase_invoice.grand_total',
                        'purchase_invoice.remaining_amount',
                        'purchases.payment_status',
                        'purchases.vendor_id',
                        'purchases.created_at',
                        'purchase_invoice.invoice_number',
                        'purchase_invoice.products as products_json'
                    );
                // ✅ Staff: see only their created purchases
                if ($role === 'staff') {
                    $purchasesQuery->where('purchase_invoice.created_by', $userId);
                } else {
                    $purchasesQuery->where('purchases.branch_id', $branch_id);
                }

                // if (!empty($vendorId)) {
                //     $purchasesQuery->where('purchases.vendor_id', $vendorId);
                // }
                if (! empty($vendorId) && $vendorId !== 'all') {
                    $purchasesQuery->where('purchases.vendor_id', $vendorId);
                }

                if (! empty($month) && $month !== 'all') {
                    $purchasesQuery->whereMonth('purchases.created_at', $month);
                }

                if (! empty($year)) {
                    $purchasesQuery->whereYear('purchases.created_at', $year);
                }

                $purchases = $purchasesQuery->get();

                // === Fetch Payments from payment_store (by purchase_id)
                $paymentsQuery = PaymentStore::where('isDeleted', 0)
                    ->whereIn('purchase_id', $purchases->pluck('purchase_invoice_id'));

                $payments = $paymentsQuery->get([
                    'id',
                    'purchase_id',
                    'payment_amount',
                    'payment_date',
                    'payment_method',
                ]);

                $paidPayments    = collect();
                $pendingPayments = collect();

                foreach ($purchases as $purchase) {
                    // Payments linked to this purchase
                    $purchasePayments = $payments->where('purchase_id', $purchase->purchase_invoice_id);

                    // === Paid Payments ===
                    foreach ($purchasePayments as $payment) {
                        $paidPayments->push([
                            'order'          => $purchase,
                            'total_amount'   => $payment->payment_amount,
                            'payment_date'   => $payment->payment_date,
                            'payment_method' => $payment->payment_method,
                        ]);
                    }
                    // dd($paidPayments);
                    // === Pending (remaining from invoice) ===
                    if ($purchase->remaining_amount > 0) {
                        $pendingPayments->push([
                            'order'        => $purchase,
                            'amount_total' => $purchase->remaining_amount,
                        ]);
                    }
                }

                // ✅ Attach product names (like you already did)
                $mapProductsWithNames = function ($collection) {
                    return $collection->map(function ($purchaseData) {
                        $purchase = $purchaseData['order'];

                        $products = json_decode($purchase->products_json, true);
                        if (is_string($products)) {
                            $products = json_decode($products, true);
                        }
                        if (! is_array($products)) {
                            $products = [];
                        }

                        $productIds   = collect($products)->pluck('product_id')->filter();
                        $productNames = \App\Models\Product::whereIn('id', $productIds)->pluck('name', 'id');

                        $purchase->products_with_names = collect($products)->map(function ($item) use ($productNames) {
                            $productId = $item['product_id'] ?? null;
                            return [
                                'product_id'   => $productId,
                                'product_name' => $productId ? ($productNames[$productId] ?? null) : null,
                                'price'        => $item['price'] ?? 0,
                                'quantity'     => $item['quantity'] ?? 0,
                                'total'        => $item['total'] ?? 0,
                            ];
                        })->values()->toArray();

                        $purchaseData['order'] = $purchase;
                        return $purchaseData;
                    });
                };

                $paidPayments    = $mapProductsWithNames($paidPayments);
                $pendingPayments = $mapProductsWithNames($pendingPayments);
            }

            return response()->json([
                'paidPayments'    => $paidPayments,
                'pendingPayments' => $pendingPayments,
                'settings'        => $settings,
            ]);
        } catch (\Exception $e) {
            \Log::error('getPaymentDetails Error: ' . $e->getMessage() . ' online ' . $e->getLine());
            return response()->json(['error' => 'Failed to fetch payment details.'], 500);
        }
    }

    public function exportAccountLedgerExcel(Request $request)
    {
        try {
            $user         = Auth::guard('api')->user();
            $role         = $user->role;
            $userId       = $user->id;
            $userBranchId = $user->branch_id;

            $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

            // ✅ Branch selection by role
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

            $type       = $request->input('type'); // customer | vendor
            $customerId = $request->input('customer_id');
            $vendorId   = $request->input('vendor_id');
            $month      = $request->input('month');
            $year       = $request->input('year');

            $userName = null;
            $gstNum   = null;

            // ✅ All / Single customer/vendor name
            if ($type === 'customer') {
                if (! empty($customerId) && $customerId !== 'all') {
                    $customer = User::where('id', $customerId)->first(['id', 'name', 'gst_number']);
                    if ($customer) {
                        $userName = $customer->name;
                        $gstNum   = $customer->gst_number;
                    }
                } else {
                    $userName = "All Customers";
                }
            } elseif ($type === 'vendor') {
                if (! empty($vendorId) && $vendorId !== 'all') {
                    $vendor = User::where('id', $vendorId)->first(['id', 'name', 'gst_number']);
                    if ($vendor) {
                        $userName = $vendor->name;
                        $gstNum   = $vendor->gst_number;
                    }
                } else {
                    $userName = "All Vendors";
                }
            }

            $settings = Setting::where('branch_id', $branch_id)->first();

            // === Create Excel ===
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();
            $row         = 1;

            // Title
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->setCellValue("A{$row}", 'Account Ledger Report');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row += 2;

            // Info
            $sheet->setCellValue("A{$row}", 'Type')->setCellValue("B{$row}", ucfirst($type));
            $row++;
            $sheet->setCellValue("A{$row}", 'Name')->setCellValue("B{$row}", $userName ?: '-');
            $row++;
            $sheet->setCellValue("A{$row}", 'GST Number')->setCellValue("B{$row}", $gstNum ?: '-');
            $row++;
            $sheet->setCellValue("A{$row}", 'Month')->setCellValue("B{$row}", $month && $month != 'all' ? date('F', mktime(0, 0, 0, $month, 1)) : 'All');
            $row++;
            $sheet->setCellValue("A{$row}", 'Year')->setCellValue("B{$row}", $year ?: '-');
            $row += 2;

            // ======================
            // CUSTOMER SECTION
            // ======================
            if ($type === 'customer') {
                $ordersQuery = Order::with('items.product:id,name')
                    ->where('isDeleted', 0);
                // ->where('branch_id', $branch_id);

                // Staff-wise filter
                if ($role === 'staff') {
                    $ordersQuery->where('created_by', $userId);
                } else {
                    $ordersQuery->where('branch_id', $branch_id);
                }
                if (! empty($customerId) && $customerId !== 'all') {
                    $ordersQuery->where('user_id', $customerId);
                }
                if (! empty($month) && $month !== 'all') {
                    $ordersQuery->whereMonth('created_at', $month);
                }
                if (! empty($year)) {
                    $ordersQuery->whereYear('created_at', $year);
                }

                $orders = $ordersQuery->get();

                $paidPayments = PaymentStore::where('payment_store.isDeleted', 0)
                    ->join('orders', 'payment_store.order_id', '=', 'orders.id')
                    // ->where('orders.branch_id', $branch_id)
                    ->when($role === 'staff', fn($q) => $q->where('orders.created_by', $userId))
                    ->when(! empty($customerId) && $customerId !== 'all', fn($q) => $q->where('orders.user_id', $customerId))
                    ->when(! empty($month) && $month !== 'all', fn($q) => $q->whereMonth('payment_date', $month))
                    ->when(! empty($year), fn($q) => $q->whereYear('payment_date', $year))
                    ->select([
                        'payment_store.payment_amount',
                        'payment_store.payment_date',
                        'orders.order_number',
                        'orders.total_amount',
                        'orders.user_id',
                    ])
                    ->get();

                $pendingPayments = $orders->filter(fn($o) => $o->remaining_amount > 0)->values();

                // Paid Payments
                $sheet->setCellValue("A{$row}", '--- PAID PAYMENTS ---');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;
                $headers = ['Order Number', 'Total Amount', 'Paid Amount', 'Date'];
                $col     = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}{$row}", $header);
                    $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                    $col++;
                }
                $row++;
                foreach ($paidPayments as $item) {
                    $sheet->setCellValue("A{$row}", $item->order_number);
                    // $sheet->setCellValue("B{$row}", $item->total_amount);
                    // $sheet->setCellValue("C{$row}", $item->payment_amount);
                    $sheet->setCellValue("B{$row}", $item->total_amount);
                    $sheet->setCellValue("C{$row}", $item->payment_amount);

                    $sheet->getStyle("B{$row}:C{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->setCellValue("D{$row}", \Carbon\Carbon::parse($item->payment_date)->format('Y-m-d'));
                    $row++;
                }
                $row++;
                // === Calculate total paid amount ===
                $totalPaid = $paidPayments->sum('payment_amount');

                $sheet->setCellValue("A{$row}", 'Total Paid');
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("C{$row}", $totalPaid);
                $sheet->getStyle("C{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
                $row += 2;

                // Pending Payments
                $sheet->setCellValue("A{$row}", '--- PENDING PAYMENTS ---');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;
                $headers = ['Order Number', 'Total Amount', 'Remaining Amount', 'Date'];
                $col     = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}{$row}", $header);
                    $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                    $col++;
                }
                $row++;
                foreach ($pendingPayments as $item) {
                    $sheet->setCellValue("A{$row}", $item->order_number);
                    // $sheet->setCellValue("B{$row}", $item->total_amount);
                    // $sheet->setCellValue("C{$row}", $item->remaining_amount);
                    $sheet->setCellValue("B{$row}", $item->total_amount);
                    $sheet->setCellValue("C{$row}", $item->remaining_amount);

                    $sheet->getStyle("B{$row}:C{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->setCellValue("D{$row}", $item->created_at->format('Y-m-d'));
                    $row++;
                }
                // === Calculate total pending amount ===
                $totalPending = $pendingPayments->sum('remaining_amount');

                $sheet->setCellValue("A{$row}", 'Total Pending');
                $sheet->mergeCells("A{$row}:B{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("C{$row}", $totalPending);
                $sheet->getStyle("C{$row}")
                ->getNumberFormat()
                ->setFormatCode('#,##0.00');
                $row += 2;
            }

            // ======================
            // VENDOR SECTION
            // ======================
            if ($type === 'vendor') {

                $purchasesQuery = Purchases::query()
                    ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
                    ->select(
                        'purchase_invoice.id as purchase_invoice_id',
                        'purchase_invoice.invoice_number',
                        'purchase_invoice.grand_total',
                        'purchase_invoice.remaining_amount',
                        'purchases.vendor_id',
                        'purchases.created_at',
                        'purchase_invoice.created_by'
                    );

                // Staff-wise filter
                if ($role === 'staff') {
                    $purchasesQuery->where('purchase_invoice.created_by', $userId);
                } else {
                    $purchasesQuery->where('purchase_invoice.branch_id', $branch_id);
                }

                if (! empty($vendorId) && $vendorId !== 'all') {
                    $purchasesQuery->where('purchases.vendor_id', $vendorId);
                }
                if (! empty($month) && $month !== 'all') {
                    $purchasesQuery->whereMonth('purchases.created_at', $month);
                }
                if (! empty($year)) {
                    $purchasesQuery->whereYear('purchases.created_at', $year);
                }

                $purchases = $purchasesQuery->get();

                $paidPaymentsRaw = PaymentStore::where('isDeleted', 0)
                    ->whereIn('purchase_id', $purchases->pluck('purchase_invoice_id'))
                    ->when(! empty($month) && $month !== 'all', fn($q) => $q->whereMonth('payment_date', $month))
                    ->when(! empty($year), fn($q) => $q->whereYear('payment_date', $year))
                    ->get();

                $paidPayments = $paidPaymentsRaw->map(function ($payment) use ($purchases) {
                    $purchase = $purchases->firstWhere('purchase_invoice_id', $payment->purchase_id);
                    $vendor   = \App\Models\User::find($purchase->vendor_id);
                    return (object) [
                        'invoice_number' => $purchase->invoice_number ?? '-',
                        'vendor_name'    => $vendor ? $vendor->name : '-',
                        'total_amount'   => $purchase->grand_total ?? 0,
                        'payment_amount' => $payment->payment_amount ?? 0,
                        'payment_date'   => $payment->payment_date ?? null,
                    ];
                });

                $pendingPayments = $purchases->filter(fn($p) => $p->remaining_amount > 0)
                    ->map(function ($purchase) {
                        $vendor = \App\Models\User::find($purchase->vendor_id);
                        return (object) [
                            'invoice_number'   => $purchase->invoice_number ?? '-',
                            'vendor_name'      => $vendor ? $vendor->name : '-',
                            'total_amount'     => $purchase->grand_total ?? 0,
                            'remaining_amount' => $purchase->remaining_amount ?? 0,
                            'created_at'       => $purchase->created_at,
                        ];
                    });

                // Paid Payments
                $sheet->setCellValue("A{$row}", '--- PAID PAYMENTS ---');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;
                $headers = ['Invoice Number', 'Vendor', 'Total Amount', 'Paid Amount', 'Date'];
                $col     = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}{$row}", $header);
                    $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                    $col++;
                }
                $row++;
                foreach ($paidPayments as $item) {
                    $sheet->setCellValue("A{$row}", $item->invoice_number);
                    $sheet->setCellValue("B{$row}", $item->vendor_name);
                    // $sheet->setCellValue("C{$row}", $item->total_amount);
                    // $sheet->setCellValue("D{$row}", $item->payment_amount);
                    $sheet->setCellValue("C{$row}", $item->total_amount);
                    $sheet->setCellValue("D{$row}", $item->payment_amount);

                    $sheet->getStyle("C{$row}:D{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->setCellValue("E{$row}", \Carbon\Carbon::parse($item->payment_date)->format('Y-m-d'));
                    $row++;
                }
                $row++;

                $totalPaid = $paidPayments->sum('payment_amount');

                $sheet->setCellValue("A{$row}", 'Total Paid');
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("D{$row}", $totalPaid);
                $row += 2;

                // Pending Payments
                $sheet->setCellValue("A{$row}", '--- PENDING PAYMENTS ---');
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $row++;
                $headers = ['Invoice Number', 'Vendor', 'Total Amount', 'Remaining Amount', 'Date'];
                $col     = 'A';
                foreach ($headers as $header) {
                    $sheet->setCellValue("{$col}{$row}", $header);
                    $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
                    $col++;
                }
                $row++;
                foreach ($pendingPayments as $item) {
                    $sheet->setCellValue("A{$row}", $item->invoice_number);
                    $sheet->setCellValue("B{$row}", $item->vendor_name);
                    // $sheet->setCellValue("C{$row}", $item->total_amount);
                    // $sheet->setCellValue("D{$row}", $item->remaining_amount);
                    $sheet->setCellValue("C{$row}", $item->total_amount);
                    $sheet->setCellValue("D{$row}", $item->remaining_amount);

                    $sheet->getStyle("C{$row}:D{$row}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->setCellValue("E{$row}", \Carbon\Carbon::parse($item->created_at)->format('Y-m-d'));
                    $row++;
                }
                $totalPending = $pendingPayments->sum('remaining_amount');

                $sheet->setCellValue("A{$row}", 'Total Pending');
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("D{$row}", $totalPending);
                $row += 2;
            }

            // Auto-size columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // ✅ Save Excel to public folder
            $writer = new Xlsx($spreadsheet);

            // ✅ Generate filename and relative path
            $filename     = "account_ledger_" . preg_replace('/[^a-zA-Z0-9]/', '_', $userName) . ".xlsx";
            $relativePath = 'account-ledgers/' . $filename;

            // ✅ Save Excel to a temporary file first
            $temp_file = tempnam(sys_get_temp_dir(), 'excel');
            $writer->save($temp_file);

            // ✅ Save temporary file contents to public storage
            \Storage::disk('public')->put($relativePath, file_get_contents($temp_file));

            // ✅ Remove temporary file
            unlink($temp_file);

            // ✅ Generate public URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            // ✅ Return JSON response
            return response()->json([
                'status'    => true,
                'message'   => 'Account Ledger Excel generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Error generating Excel file.'], 500);
        }
    }

    public function downloadAccountLedgerPDF(Request $request)
    {
        // dd($request->all());
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $monthInput   = $request->input('month');
        $months       = [];
        if (! empty($monthInput)) {
            $months = is_array($monthInput) ? $monthInput : [$monthInput];
        }

        $selectedSubAdminId = $request->selectedSubAdminId ?? $userId;
        //  $selectedSubAdminId = session('selectedSubAdminId');

        // dd($selectedSubAdminId);
        // ✅ Branch selection by role
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

        // dd($branch_id);
        try {
            $type       = $request->input('type'); // customer | vendor
            $customerId = $request->input('customer_id');
            $vendorId   = $request->input('vendor_id');
            $month      = $request->input('month');
            $year       = $request->input('year');

            // dd($vendorId);
            $userName = null;
            $gstNum   = null;

            $settings = Setting::where('branch_id', $branch_id)
                ->first();
            // dd($settings);
            $paidPayments    = collect();
            $pendingPayments = collect();

            if ($type === 'customer') {
                if (! empty($customerId) && $customerId !== 'all') {
                    $customer = User::where('id', $customerId)->first(['id', 'name', 'gst_number']);
                    if ($customer) {
                        $userName = $customer->name;
                        $gstNum   = $customer->gst_number;
                    }
                } else {
                    $userName = "All Customers";
                }

                // === Customer Orders ===
                $ordersQuery = Order::with('items.product:id,name')
                    ->where('isDeleted', 0);

                // ✅ Staff: filter by created_by
                if ($role === 'staff') {
                    $ordersQuery->where('created_by', $userId);
                } else {
                    $ordersQuery->where('branch_id', $branch_id);
                }
                // ->where('branch_id', $branch_id);

                if (! empty($customerId) && $customerId !== 'all') {
                    $ordersQuery->where('user_id', $customerId);
                }
                if (! empty($month) && $month !== 'all') {
                    $ordersQuery->whereMonth('created_at', $month);
                }
                if (! empty($year)) {
                    $ordersQuery->whereYear('created_at', $year);
                }

                $orders = $ordersQuery->get();

                // === Paid Payments ===
                $paidPayments = PaymentStore::where('payment_store.isDeleted', 0)
                    ->join('orders', 'payment_store.order_id', '=', 'orders.id')
                    // ->where('orders.branch_id', $branch_id)
                    ->when($role === 'staff', fn($q) => $q->where('orders.created_by', $userId))
                    ->when(! empty($customerId) && $customerId !== 'all', fn($q) => $q->where('orders.user_id', $customerId))
                    ->when(! empty($month) && $month !== 'all', fn($q) => $q->whereMonth('payment_date', $month))
                    ->when(! empty($year), fn($q) => $q->whereYear('payment_date', $year))
                    ->select([
                        'payment_store.id',
                        'payment_store.order_id',
                        'payment_store.payment_amount',
                        'payment_store.payment_date',
                        'payment_store.payment_method',
                        'orders.order_number',
                        'orders.total_amount',
                        'orders.user_id',
                    ])
                    ->get();

                // === Pending Payments ===
                $pendingPayments = $orders->filter(fn($o) => $o->remaining_amount > 0)->values();
            } elseif ($type === 'vendor') {
                if (! empty($vendorId) && $vendorId !== 'all') {
                    $vendor = User::find($vendorId, ['id', 'name', 'gst_number']);
                    if ($vendor) {
                        $userName = $vendor->name;
                        $gstNum   = $vendor->gst_number;
                    }
                } else {
                    $userName = "All Vendors";
                }

                $purchases = Purchases::query()
                    ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
                    ->where('purchases.branch_id', $branch_id)
                    ->when(! empty($vendorId) && $vendorId !== 'all', fn($q) => $q->where('purchases.vendor_id', $vendorId))
                    ->when(! empty($month) && $month !== 'all', fn($q) => $q->whereMonth('purchases.created_at', $month))
                    ->when(! empty($year), fn($q) => $q->whereYear('purchases.created_at', $year))
                    ->select(
                        'purchases.id as purchase_id',
                        'purchase_invoice.id as purchase_invoice_id',
                        'purchase_invoice.invoice_number',
                        'purchase_invoice.grand_total',
                        'purchase_invoice.remaining_amount',
                        'purchases.vendor_id',
                        'purchases.created_at',
                        'purchase_invoice.products as products_json'
                    )
                    ->get();

                $paidPaymentsRaw = PaymentStore::where('isDeleted', 0)
                    ->whereIn('purchase_id', $purchases->pluck('purchase_invoice_id'))
                    ->when(! empty($month) && $month !== 'all', fn($q) => $q->whereMonth('payment_date', $month))
                    ->when(! empty($year), fn($q) => $q->whereYear('payment_date', $year))
                    ->get();

                $paidPayments = $paidPaymentsRaw->map(function ($payment) use ($purchases) {
                    $purchase = $purchases->firstWhere('purchase_invoice_id', $payment->purchase_id);
                    $vendor   = \App\Models\User::find($purchase->vendor_id);
                    return (object) [
                        'invoice_number' => $purchase->invoice_number ?? '-',
                        'vendor_name'    => $vendor ? $vendor->name : '-',
                        'total_amount'   => $purchase->grand_total ?? 0,
                        'payment_amount' => $payment->payment_amount ?? 0,
                        'payment_date'   => $payment->payment_date ?? null,
                    ];
                });

                $pendingPayments = $purchases->filter(fn($p) => $p->remaining_amount > 0)
                    ->map(function ($purchase) {
                        $vendor = \App\Models\User::find($purchase->vendor_id);
                        return (object) [
                            'invoice_number'   => $purchase->invoice_number ?? '-',
                            'vendor_name'      => $vendor ? $vendor->name : '-',
                            'total_amount'     => $purchase->grand_total ?? 0,
                            'remaining_amount' => $purchase->remaining_amount ?? 0,
                            'created_at'       => $purchase->created_at,
                        ];
                    });
            }

            // ✅ Generate PDF
            $pdf = Pdf::loadView('account_ledger.pdf', [
                'paidPayments'    => $paidPayments,
                'pendingPayments' => $pendingPayments,
                'settings'        => $settings,
                'userName'        => $userName,
                'months'          => $months,
                'year'            => $year,
                'gstNum'          => $gstNum,
                'type'            => $type,
            ]);

            $filename = "account_ledger_" . preg_replace('/[^a-zA-Z0-9]/', '_', $userName ?? $type) . ".pdf";

            // ✅ Save PDF to storage (public folder)
            $relativePath = 'account-ledgers/' . $filename;
            \Storage::disk('public')->put($relativePath, $pdf->output());

            // Public URL

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status'    => true,
                'message'   => 'Account Ledger PDF generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF.'], 500);
        }
    }
}
