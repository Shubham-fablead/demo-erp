<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomInvoice;
use App\Models\CustomInvoiceItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomInvoiceController extends Controller
{
    public function store(Request $request)
    {
        if ($request->has('payment') && is_array($request->payment)) {
            $request->merge($request->payment);
        }

        $user         = Auth::guard('api')->user();
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $role         = $user->role;

        if ($role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'customer_id'            => 'nullable',
            'vendor_id'              => 'nullable',
            'status'                 => 'required',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'required',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.total'       => 'required|numeric|min:0',
            'taxes'                  => 'nullable|array',
            'taxes.*.id'             => 'required|numeric',
            'taxes.*.name'           => 'required|string',
            'taxes.*.rate'           => 'required|numeric',
            'taxes.*.amount'         => 'required|numeric',
            'bank_id'                => 'required_if:payment_mode,online,cashonline|nullable|exists:bank_master,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // ✅ Generate unique invoice number
            do {
                $invoice_number = 'INV-' . mt_rand(10000000, 99999999);
            } while (CustomInvoice::where('invoice_number', $invoice_number)->exists());

            $vendor_id   = null;
            $customer_id = null;

            // ✅ Check vendor
            if (! empty($request->vendor_id)) {
                if (! is_numeric($request->vendor_id)) {
                    $existingVendor = User::where('name', $request->vendor_id)->first();
                    if ($existingVendor) {
                        $vendor_id = $existingVendor->id;
                    } else {
                        $vendor = User::create([
                            'name'       => $request->vendor_id,
                            'phone'      => null,
                            'role'       => 'vendor',
                            'status'     => $request->status,
                            'branch_id'  => $userBranchId,
                            'created_by' => $userId,
                        ]);
                        $vendor_id = $vendor->id;

                        UserDetail::create([
                            'user_id'   => $vendor_id,
                            'address'   => '',
                            'city'      => '',
                            'state'     => '',
                            'country'   => '',
                            'pincode'   => '',
                            'branch_id' => $userBranchId,
                        ]);
                    }
                } else {
                    $vendor_id = $request->vendor_id;
                }
            }
            // ✅ Check customer
            if (! empty($request->customer_id)) {
                if (! is_numeric($request->customer_id)) {
                    $existingCustomer = User::where('name', $request->customer_id)->first();
                    if ($existingCustomer) {
                        $customer_id = $existingCustomer->id;
                    } else {
                        $customer = User::create([
                            'name'       => $request->customer_id,
                            'phone'      => null,
                            'role'       => 'customer',
                            'status'     => $request->status,
                            'branch_id'  => $userBranchId,
                            'created_by' => $userId,
                        ]);
                        $customer_id = $customer->id;

                        UserDetail::create([
                            'user_id'   => $customer_id,
                            'address'   => '',
                            'city'      => '',
                            'state'     => '',
                            'country'   => '',
                            'pincode'   => '',
                            'branch_id' => $userBranchId,
                        ]);
                    }
                } else {
                    $customer_id = $request->customer_id;
                }
            }
            // ✅ OPTIMIZED: Process Products with bulk operations
            $processedProducts = [];
            $productIds        = array_filter(array_column($request->products, 'id'), 'is_numeric');

            // ✅ OPTIMIZED: Bulk load existing products
            $existingProducts = ! empty($productIds)
                ? Product::whereIn('id', $productIds)->get()->keyBy('id')
                : collect();

            // ✅ OPTIMIZED: Get all last inventories in one query
            $lastInventories = ! empty($productIds)
                ? ProductInventory::whereIn('product_id', $productIds)
                ->orderBy('product_id')
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('product_id')
                ->map(function ($group) {
                    return $group->first();
                })
                : collect();

            $invoiceItemsData = [];
            $inventoryData    = [];
            $productUpdates   = [];
            $now              = now();

            foreach ($request->products as $product) {
                if (! is_numeric($product['category_id'])) {
                    $category    = Category::create(['name' => $product['category_id']]);
                    $category_id = $category->id;
                } else {
                    $category_id = $product['category_id'];
                }

                if (! is_numeric($product['id'])) {
                    do {
                        $sku = mt_rand(10000000, 99999999);
                    } while (Product::where('SKU', $sku)->exists());

                    $newProduct = Product::create([
                        'name'          => $product['id'],
                        'category_id'   => $category_id,
                        'price'         => $product['price'],
                        'quantity'      => $product['quantity'],
                        'vendor_id'     => $vendor_id ?? $customer_id,
                        'availablility' => 'in_stock',
                        'status'        => 'active',
                        'SKU'           => $sku,
                        'branch_id'     => $userBranchId,
                    ]);
                    $product_id = $newProduct->id;
                } else {
                    $existingProduct = $existingProducts->get($product['id']);
                    if ($existingProduct) {
                        $newQuantity                          = $existingProduct->quantity + $product['quantity'];
                        $productUpdates[$existingProduct->id] = [
                            'quantity'    => $newQuantity,
                        ];
                        $product_id = $existingProduct->id;
                    } else {
                        continue;
                    }
                }

                $processedProducts[] = [
                    'product_id' => $product_id,
                    'price'      => $product['price'],
                    'quantity'   => $product['quantity'],
                    'total'      => $product['total'],
                ];
            }

            // ✅ Determine total paid and remaining amount properly
            $paymentMode = strtolower($request->payment_mode ?? '');
            $totalPaid   = 0;

            if ($paymentMode === 'cashonline') {
                $totalPaid = (float) ($request->cash_amount ?? 0) + (float) ($request->upi_amount ?? 0);
            } elseif ($paymentMode === 'cash') {
                $totalPaid = (float) ($request->cash_amount ?: ($request->amount ?? 0));
            } elseif ($paymentMode === 'online') {
                $totalPaid = (float) ($request->upi_amount ?: ($request->amount ?? 0));
            }

            $grandTotal      = (float) ($request->grand_total ?? 0);
            $remainingAmount = max(0, $grandTotal - $totalPaid);

            // ✅ Store CustomInvoice
            $customInvoice = CustomInvoice::create([
                'invoice_number'   => $invoice_number,
                'vendor_id'        => $vendor_id ?? null,
                'customer_id'      => $customer_id ?? null,
                'products'         => json_encode($processedProducts),
                'total_amount'     => collect($processedProducts)->sum('total'),
                'paid'             => $totalPaid,
                'discount'         => $request->discount ?? 0,
                'shipping'         => $request->shipping,
                'grand_total'      => $grandTotal,
                'remaining_amount' => $remainingAmount,
                'gst_option'       => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
                'status'           => $request->status,
                'taxes'      => $request->gst_option === 'with_gst'
                    ? $request->taxes
                    : [],
                'branch_id'        => $userBranchId,
                'created_by'       => $userId,
            ]);

            // ✅ Determine overall statuses
            $purchaseStatus = 'pending';
            $paymentStatus  = 'pending';

            if ($totalPaid > 0) {
                if ($remainingAmount <= 0) {
                    $purchaseStatus = 'completed';
                    $paymentStatus  = 'completed';
                } else {
                    $purchaseStatus = 'partially';
                    $paymentStatus  = 'partially';
                }
            }

            if ($request->payment_mode === 'pending') {
                $purchaseStatus = 'pending';
                $paymentStatus  = 'pending';
            }

            // ✅ Decide payment status automatically
            if (!empty($customer_id)) {
                $paymentStatusType = 'credit';   // Customer paying you
            } elseif (!empty($vendor_id)) {
                $paymentStatusType = 'debit';    // You paying vendor
            } else {
                $paymentStatusType = 'debit';    // fallback
            }

            // ✅ Prepare Bulk Data for Items and Inventory
            foreach ($processedProducts as $item) {
                $product_id   = $item['product_id'];
                $productTotal = $item['total'];
                $gst_details  = [];
                $gst_total    = 0;

                // Product-wise GST calculation
                if ($request->gst_option === 'with_gst') {
                    $prod = $existingProducts->get($product_id);
                    if ($prod && $prod->gst_option === 'with_gst' && $prod->product_gst) {
                        $itemTaxes = json_decode($prod->product_gst, true);
                        if (is_array($itemTaxes)) {
                            foreach ($itemTaxes as $tax) {
                                $taxRate        = floatval($tax['tax_rate'] ?? 0);
                                $taxAmount      = ($productTotal * $taxRate) / 100;
                                $gst_total     += $taxAmount;
                                $gst_details[]  = [
                                    'name'   => $tax['tax_name'] ?? '',
                                    'rate'   => $taxRate,
                                    'amount' => $taxAmount,
                                ];
                            }
                        }
                    }
                }

                $invoiceItemsData[] = [
                    'invoice_id'          => $customInvoice->id,
                    'item'                => $product_id,
                    'quantity'            => $item['quantity'],
                    'price'               => $item['price'],
                    'amount_total'        => $item['total'],
                    'product_gst_details' => json_encode($gst_details),
                    'product_gst_total'   => $gst_total,
                    'vendor_id'           => $vendor_id,
                    'customer_id'         => $customer_id,
                    'purchase_status'     => $purchaseStatus,
                    'invoice_status'      => $request->status,
                    'payment_status'      => $paymentStatus,
                    'branch_id'           => $userBranchId,
                    'created_by'          => $userId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                // Prepare inventory data
                $lastInventory = $lastInventories->get($product_id);
                $currentStock  = $lastInventory
                    ? ($lastInventory->current_stock + $item['quantity'])
                    : $item['quantity'];

                $inventoryData[] = [
                    'product_id'    => $product_id,
                    'initial_stock' => $lastInventory->initial_stock ?? $currentStock - $item['quantity'],
                    'current_stock' => $currentStock,
                    'branch_id'     => $userBranchId,
                    'create_by'     => $userId,
                    'type'          => 'Purchase', // Assuming it behaves like a purchase for inventory
                    'date'          => $now,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
            // ✅ Bulk Inserts and Updates
            if (! empty($invoiceItemsData)) {
                CustomInvoiceItem::insert($invoiceItemsData);
            }

            foreach ($productUpdates as $productId => $updateData) {
                Product::where('id', $productId)->update($updateData);
            }

            if (! empty($inventoryData)) {
                ProductInventory::insert($inventoryData);
            }
            // ✅ Payment handling
            if (! empty($paymentMode) && $paymentMode !== 'pending') {
                $paidType    = strtolower($request->paid_type ?? 'full');
                $cashAmount  = (float) ($request->cash_amount ?: ($paymentMode === 'cash' ? ($request->amount ?? 0) : 0));
                $upiAmount   = (float) ($request->upi_amount ?: ($paymentMode === 'online' ? ($request->amount ?? 0) : 0));

                // Use the already calculated $remainingAmount as $pending
                $pending = $remainingAmount;

                // ✅ CASE 1: Cash + Online (both)
                if ($paymentMode === 'cashonline') {
                    // 1️⃣ Insert Cash Payment
                    if ($cashAmount > 0) {
                        PaymentStore::create([
                            'user_id'           => $vendor_id ?? $customer_id,
                            'custom_invoice_id' => $customInvoice->id,
                            'payment_amount'    => $cashAmount,
                            'payment_date'      => now(),
                            'payment_method'    => 'Cash',
                            'payment_type'      => $paidType,
                            'cash_amount'       => $cashAmount,
                            'upi_amount'        => 0,
                            'remaining_amount'  => $pending,
                            'status' => $paymentStatusType,
                            'bank_id'           => $request->bank_id,
                            'emi_month'         => null,
                            'order_id'          => null,
                            'jobcard_id'        => 0,
                            'isDeleted'         => 0,
                        ]);
                    }

                    // 2️⃣ Insert Online Payment
                    if ($upiAmount > 0) {
                        PaymentStore::create([
                            'user_id'           => $vendor_id ?? $customer_id,
                            'custom_invoice_id' => $customInvoice->id,
                            'payment_amount'    => $upiAmount,
                            'payment_date'      => now(),
                            'payment_method'    => 'Online',
                            'payment_type'      => $paidType,
                            'cash_amount'       => 0,
                            'upi_amount'        => $upiAmount,
                            'remaining_amount'  => $pending,
                            'status' => $paymentStatusType,
                            'bank_id'           => $request->bank_id,
                            'emi_month'         => null,
                            'order_id'          => null,
                            'jobcard_id'        => 0,
                            'isDeleted'         => 0,
                        ]);
                    }
                }

                // ✅ CASE 2: Cash Only
                elseif ($paymentMode === 'cash') {
                    PaymentStore::create([
                        'user_id'           => $vendor_id ?? $customer_id,
                        'custom_invoice_id' => $customInvoice->id,
                        'payment_amount'    => $cashAmount,
                        'payment_date'      => now(),
                        'payment_method'    => 'Cash',
                        'payment_type'      => $paidType,
                        'cash_amount'       => $cashAmount,
                        'upi_amount'        => 0,
                        'remaining_amount'  => $pending,
                        'status' => $paymentStatusType,
                        'bank_id'           => $request->bank_id,
                        'emi_month'         => null,
                        'order_id'          => null,
                        'jobcard_id'        => 0,
                        'isDeleted'         => 0,
                    ]);
                }

                // ✅ CASE 3: Online Only
                elseif ($paymentMode === 'online') {
                    PaymentStore::create([
                        'user_id'           => $vendor_id ?? $customer_id,
                        'custom_invoice_id' => $customInvoice->id,
                        'payment_amount'    => $upiAmount,
                        'payment_date'      => now(),
                        'payment_method'    => 'Online',
                        'payment_type'      => $paidType,
                        'cash_amount'       => 0,
                        'upi_amount'        => $upiAmount,
                        'remaining_amount'  => $pending,
                        'status' => $paymentStatusType,
                        'bank_id'           => $request->bank_id,
                        'emi_month'         => null,
                        'order_id'          => null,
                        'jobcard_id'        => 0,
                        'isDeleted'         => 0,
                    ]);
                }
            }
            DB::commit();

            // Fetch latest payment info for this invoice
            $payment = PaymentStore::where('custom_invoice_id', $customInvoice->id)
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'success'        => true,
                'message'        => 'Custom invoice and invoice items created successfully!',
                'invoice_number' => $invoice_number,
                'invoice_id'     => $customInvoice->id,
                'payment'        => $payment,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function list(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;

        // ✅ Determine branch ID
        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // Pagination params
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);
        $search  = $request->input('search');

        $query = DB::table('custom_invoice')
            ->join('custom_invoice_item', function ($join) {
                $join->on('custom_invoice.id', '=', 'custom_invoice_item.invoice_id')
                    ->where('custom_invoice_item.isDeleted', '=', 0);
            })
            ->leftJoin('users as vendors', 'custom_invoice.vendor_id', '=', 'vendors.id')
            ->leftJoin('users as customers', 'custom_invoice.customer_id', '=', 'customers.id')
            ->join('products', 'custom_invoice_item.item', '=', 'products.id')
            ->select(
                'custom_invoice.id',
                DB::raw("COALESCE(vendors.name, customers.name) as vendor_name"),
                'custom_invoice.invoice_number',
                'custom_invoice.grand_total',
                'custom_invoice.remaining_amount',
                'custom_invoice_item.purchase_status',
                'custom_invoice_item.payment_status',
                'custom_invoice_item.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(custom_invoice_item.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(custom_invoice_item.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.custom_invoice_id = custom_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('custom_invoice.isDeleted', '=', 0);

        // ✅ Role-based data restriction
        if ($role === 'staff') {
            $query->where('custom_invoice.branch_id', '=', $userBranchId)
                ->where('custom_invoice.created_by', '=', $user->id);
        } else {
            $query->where('custom_invoice.branch_id', '=', $branch_id);
        }

        // ✅ Search filter
        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('custom_invoice.invoice_number', 'LIKE', "%{$search}%")
                    ->orWhere('vendors.name', 'LIKE', "%{$search}%")
                    ->orWhere('customers.name', 'LIKE', "%{$search}%")
                    ->orWhere('products.name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('date') && ! empty($request->date)) {
            $query->whereDate('custom_invoice_item.created_at', $request->date);
        }

        // ✅ Month filter
        if ($request->filled('month')) {
            $query->whereMonth('custom_invoice_item.created_at', $request->month);
        }

        // ✅ Year filter
        if ($request->filled('year')) {
            $query->whereYear('custom_invoice_item.created_at', $request->year);
        }

        $groupedQuery = (clone $query)
            ->groupBy(
                'custom_invoice.id',
                'vendors.name',
                'customers.name',
                'custom_invoice.invoice_number',
                'custom_invoice.grand_total',
                'custom_invoice.remaining_amount',
                'custom_invoice_item.purchase_status',
                'custom_invoice_item.payment_status',
                'custom_invoice_item.created_at'
            );

        $overallGrandTotal = DB::query()
            ->fromSub($groupedQuery, 'filtered_invoices')
            ->sum('grand_total');

        $purchases = $groupedQuery
            ->orderBy('custom_invoice_item.created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Fetch currency settings from DB
        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'success'           => true,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'overall_grand_total' => (float) $overallGrandTotal,
            'data'              => $purchases->items(),
            'pagination'        => [
                'current_page'  => $purchases->currentPage(),
                'last_page'     => $purchases->lastPage(),
                'per_page'      => $purchases->perPage(),
                'total'         => $purchases->total(),
                'next_page_url' => $purchases->nextPageUrl(),
                'prev_page_url' => $purchases->previousPageUrl(),
            ],
        ]);
    }

    public function delete(Request $request)
    {
        try {
            DB::beginTransaction(); // Start transaction

            // Find the custom invoice
            $invoice = CustomInvoice::find($request->id);
            if (! $invoice) {
                return response()->json(['status' => false, 'message' => 'Invoice not found.'], 404);
            }

            // Check if all related custom_invoice_items have status 'completed'
            $nonCompletedCount = CustomInvoiceItem::where('invoice_id', $request->id)
                ->where('purchase_status', '!=', 'completed')
                ->where('isDeleted', 0)
                ->count();

            if ($nonCompletedCount > 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cannot delete. These purchases are not completed.',
                ], 422);
            }

            // Soft delete related items
            CustomInvoiceItem::where('invoice_id', $request->id)
                ->where('purchase_status', 'completed')
                ->update(['isDeleted' => 1]);

            // Soft delete the invoice
            $invoice->isDeleted = 1;
            $invoice->save();

            DB::commit(); // Commit transaction

            return response()->json([
                'status'  => true,
                'message' => 'Invoice deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function get($id, Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $branch_id    = $user->id;

        // ✅ Determine branch_id properly
        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        try {
            $invoiceDataQuery = DB::table('custom_invoice_item')
                ->join('products', 'custom_invoice_item.item', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('custom_invoice', 'custom_invoice_item.invoice_id', '=', 'custom_invoice.id')
                ->leftJoin('users as vendors', 'custom_invoice.vendor_id', '=', 'vendors.id')
                ->leftJoin('users as customers', 'custom_invoice.customer_id', '=', 'customers.id')
                // ✅ Join orders to fetch order_number
                ->leftJoin('orders', 'custom_invoice.id', '=', 'orders.order_invoice')
                ->where('custom_invoice.id', $id)
                // ->where('custom_invoice.branch_id', '=', $branch_id)
                ->where('custom_invoice.isDeleted', 0)
                ->select(
                    'custom_invoice.id as invoice_id',
                    'custom_invoice.invoice_number', // ✅ Get invoice number
                    'orders.order_number',           // ✅ Get order number
                    'custom_invoice.vendor_id',
                    'custom_invoice.customer_id',
                    'custom_invoice.status',
                    'custom_invoice.shipping',
                    'custom_invoice.taxes',
                    'custom_invoice.discount',
                    'custom_invoice.paid',
                    'custom_invoice.gst_option',
                    'custom_invoice.grand_total',

                    // ✅ Vendor details
                    'vendors.id as vendor_id',
                    'vendors.name as vendor_name',
                    'vendors.phone as vendor_phone',
                    'vendors.email as vendor_email',
                    'vendors.gst_number as vendor_gst',
                    'vendors.pan_number as vendor_pan',

                    // ✅ Customer details
                    'customers.id as customer_id',
                    'customers.name as customer_name',
                    'customers.phone as customer_phone',
                    'customers.email as customer_email',
                    'customers.gst_number as customer_gst',
                    'customers.pan_number as customer_pan',

                    // ✅ Only 1 status based on unpaid
                    DB::raw("IF(SUM(custom_invoice_item.payment_status = 'unpaid') > 0, 'unpaid', 'paid') as payment_status"),

                    // ✅ Product & Category data
                    DB::raw("GROUP_CONCAT(products.id ORDER BY products.id SEPARATOR ', ') as product_ids"),
                    DB::raw("GROUP_CONCAT(products.name ORDER BY products.id SEPARATOR ', ') as product_names"),
                    DB::raw("GROUP_CONCAT(custom_invoice_item.price ORDER BY products.id SEPARATOR ', ') as product_prices"),
                    DB::raw("GROUP_CONCAT(products.images ORDER BY products.id SEPARATOR ', ') as product_images"),
                    DB::raw("GROUP_CONCAT(custom_invoice_item.quantity ORDER BY products.id SEPARATOR ', ') as product_quantities"),
                    DB::raw("GROUP_CONCAT(custom_invoice_item.amount_total ORDER BY products.id SEPARATOR ', ') as product_totals"),
                    DB::raw("GROUP_CONCAT(categories.id ORDER BY products.id SEPARATOR ', ') as category_ids"),
                    DB::raw("GROUP_CONCAT(categories.name ORDER BY products.id SEPARATOR ', ') as category_names"),

                    // ✅ Totals
                    DB::raw("SUM(custom_invoice_item.amount_total) as total_amount"),
                    // DB::raw("(SUM(custom_invoice_item.price * custom_invoice_item.quantity) + custom_invoice.shipping - custom_invoice.discount) as grand_total")
                )
                ->groupBy(
                    'custom_invoice.id',
                    'custom_invoice.invoice_number',
                    'orders.order_number',
                    'custom_invoice.vendor_id',
                    'custom_invoice.customer_id',
                    'custom_invoice.status',
                    'custom_invoice.shipping',
                    'custom_invoice.taxes',
                    'custom_invoice.discount',
                    'custom_invoice.grand_total',
                    'custom_invoice.paid',
                    'custom_invoice.gst_option',
                    'vendors.id',
                    'vendors.name',
                    'vendors.phone',
                    'vendors.email',
                    'vendors.gst_number',
                    'vendors.pan_number',
                    'customers.id',
                    'customers.name',
                    'customers.phone',
                    'customers.email',
                    'customers.gst_number',
                    'customers.pan_number'
                );
            // ->first();
            // ✅ Role-based restriction
            if ($role === 'staff') {
                $invoiceDataQuery->where('custom_invoice.branch_id', '=', $userBranchId)
                    ->where('custom_invoice.created_by', '=', $user->id);
            } else {
                $invoiceDataQuery->where('custom_invoice.branch_id', '=', $branch_id);
            }

            $invoiceData = $invoiceDataQuery->first();
            if (! $invoiceData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found',
                ], 404);
            }

            $productImages = explode(',', $invoiceData->product_images ?? '');
            $basePath      = env('ImagePath', '/');

            $productImageUrls = [];

            if (! empty($productImages)) {
                $productImageUrls = array_map(function ($img) use ($basePath) {
                    // Remove extra brackets, quotes, or spaces
                    $img = trim($img, " []\"'");
                    return url(rtrim($basePath, '/') . '/storage/' . ltrim($img, '/'));
                }, $productImages);

                // Remove any empty values
                $productImageUrls = array_filter($productImageUrls);
            }

            if (empty($productImageUrls)) {
                $productImageUrls = [url(rtrim($basePath, '/') . '/admin/assets/img/product/noimage.png')];
            }

            $settings = DB::table('settings')->where('branch_id', $branch_id)->first();

            // ✅ Decode taxes JSON
            $taxes = json_decode($invoiceData->taxes, true) ?? [];

            return response()->json([
                'success'            => true,
                'data'               => $invoiceData,
                'taxes'              => $taxes,
                'companyInfo'        => $settings,
                'product_image_urls' => array_values($productImageUrls),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user         = Auth::guard('api')->user();
        $userId       = $user->id;
        $userBranchId = $user->branch_id;
        $role         = $user->role;

        if ($role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        $validator = Validator::make($request->all(), [
            'customer_id'            => 'nullable',
            'vendor_id'              => 'nullable',
            'status'                 => 'required',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'required',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.total'       => 'required|numeric|min:0',
            'taxes'                  => 'nullable|array',
            'taxes.*.id'             => 'required|numeric',
            'taxes.*.name'           => 'required|string',
            'taxes.*.rate'           => 'required|numeric',
            'taxes.*.amount'         => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $customInvoice = CustomInvoice::find($id);
            if (! $customInvoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invoice not found.',
                ], 404);
            }

            // ✅ REVERSE OLD INVENTORY
            $oldItems = CustomInvoiceItem::where('invoice_id', $id)->get();
            foreach ($oldItems as $oldItem) {
                $oldProduct = Product::find($oldItem->item);
                if ($oldProduct) {
                    $oldProduct->increment('quantity', $oldItem->quantity);

                    // Log inventory reversal
                    ProductInventory::create([
                        'product_id'    => $oldItem->item,
                        'initial_stock' => $oldProduct->quantity - $oldItem->quantity,
                        'current_stock' => $oldProduct->quantity,
                        'branch_id'     => $userBranchId,
                        'create_by'     => $userId,
                        'type'          => 'Invoice Update (Reversed)',
                        'date'          => now(),
                    ]);
                }
            }

            $vendor_id   = null;
            $customer_id = null;

            // ✅ Vendor Handling
            if (! empty($request->vendor_id)) {
                if (! is_numeric($request->vendor_id)) {
                    $existingVendor = User::where('id', $request->vendor_id)->first();
                    if ($existingVendor) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A vendor with this phone number already exists.',
                        ], 409);
                    }
                    $vendor = User::create([
                        'name'       => $request->vendor_id,
                        'phone'      => null,
                        'role'       => 'vendor',
                        'status'     => $request->status,
                        'branch_id'  => $userBranchId,
                        'created_by' => $userId,
                    ]);
                    $vendor_id = $vendor->id;

                    UserDetail::create([
                        'user_id'   => $vendor_id,
                        'address'   => '',
                        'city'      => '',
                        'state'     => '',
                        'country'   => '',
                        'pincode'   => '',
                        'branch_id' => $userBranchId,
                    ]);
                } else {
                    $vendor_id = $request->vendor_id;
                }
            }

            // ✅ Customer Handling
            if (! empty($request->customer_id)) {
                if (! is_numeric($request->customer_id)) {
                    $existingCustomer = User::where('id', $request->customer_id)->first();
                    if ($existingCustomer) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A customer with this phone number already exists.',
                        ], 409);
                    }
                    $customer = User::create([
                        'name'       => $request->customer_id,
                        'phone'      => null,
                        'role'       => 'customer',
                        'status'     => $request->status,
                        'branch_id'  => $userBranchId,
                        'created_by' => $userId,
                    ]);
                    $customer_id = $customer->id;

                    UserDetail::create([
                        'user_id'   => $customer_id,
                        'address'   => '',
                        'city'      => '',
                        'state'     => '',
                        'country'   => '',
                        'pincode'   => '',
                        'branch_id' => $userBranchId,
                    ]);
                } else {
                    $customer_id = $request->customer_id;
                }
            }

            // ✅ Process Products
            $processedProducts = [];
            foreach ($request->products as $product) {
                if (! is_numeric($product['category_id'])) {
                    $category    = Category::create(['name' => $product['category_id']]);
                    $category_id = $category->id;
                } else {
                    $category_id = $product['category_id'];
                }

                if (! is_numeric($product['id'])) {
                    do {
                        $sku = mt_rand(10000000, 99999999);
                    } while (Product::where('SKU', $sku)->exists());

                    $newProduct = Product::create([
                        'name'          => $product['id'],
                        'category_id'   => $category_id,
                        'price'         => $product['price'],
                        'quantity'      => 0, // start with 0, will subtract below
                        'vendor_id'     => $vendor_id ?? $customer_id,
                        'availablility' => 'in_stock',
                        'status'        => 'active',
                        'SKU'           => $sku,
                        'branch_id'     => $userBranchId,
                    ]);
                    $product_id      = $newProduct->id;
                    $existingProduct = $newProduct;
                } else {
                    $existingProduct = Product::find($product['id']);
                    if ($existingProduct) {
                        $product_id = $existingProduct->id;
                    } else {
                        continue;
                    }
                }

                // ✅ UPDATE INVENTORY FOR NEW QUANTITY
                $existingProduct->decrement('quantity', $product['quantity']);

                ProductInventory::create([
                    'product_id'    => $product_id,
                    'initial_stock' => $existingProduct->quantity + $product['quantity'],
                    'current_stock' => $existingProduct->quantity,
                    'branch_id'     => $userBranchId,
                    'create_by'     => $userId,
                    'type'          => 'Invoice Update',
                    'date'          => now(),
                ]);

                $processedProducts[] = [
                    'product_id' => $product_id,
                    'price'      => $product['price'],
                    'quantity'   => $product['quantity'],
                    'total'      => $product['total'],
                ];
            }

            $totalAmount     = collect($processedProducts)->sum('total');
            $discountPercent = $request->discount ?? 0;
            $discountAmount  = ($totalAmount * $discountPercent) / 100;
            $afterDiscount   = $totalAmount - $discountAmount;

            $totalTaxAmount = 0;
            $taxData        = [];
            if ($request->gst_option === 'with_gst' && ! empty($request->taxes)) {
                foreach ($request->taxes as $tax) {
                    $rate            = $tax['rate'] ?? 0;
                    $taxAmount       = ($afterDiscount * $rate) / 100;
                    $totalTaxAmount += $taxAmount;
                    $taxData[]       = [
                        'tax_id'   => $tax['id'] ?? null,
                        'tax_name' => $tax['name'] ?? '',
                        'rate'     => $rate,
                        'amount'   => $taxAmount,
                    ];
                }
            }

            $shipping   = $request->shipping ?? 0;
            $grandTotal = round($afterDiscount + $shipping + $totalTaxAmount, 2);

            // Fetch current paid amount from PaymentStore
            $totalPaid = PaymentStore::where('custom_invoice_id', $id)->where('isDeleted', 0)->sum('payment_amount');
            $remaining = max(round($grandTotal - $totalPaid, 2), 0);

            // Determine Status
            $paymentStatus  = $remaining <= 0 ? 'paid' : ($totalPaid > 0 ? 'partially' : 'pending');
            $purchaseStatus = $request->status ?? ($remaining <= 0 ? 'completed' : 'pending');

            // ✅ Update CustomInvoice
            $customInvoice->update([
                'vendor_id'        => $vendor_id ?? null,
                'customer_id'      => $customer_id ?? null,
                'products'         => json_encode($processedProducts),
                'total_amount'     => $totalAmount,
                'discount'         => $discountPercent,
                'shipping'         => $shipping,
                'status'           => $purchaseStatus,
                'taxes'            => json_encode($taxData),
                'gst_option'       => $request->gst_option === 'with_gst' ? 'with_gst' : 'without_gst',
                'grand_total'      => $grandTotal,
                'remaining_amount' => $remaining,
            ]);

            // ✅ Update CustomInvoiceItems
            CustomInvoiceItem::where('invoice_id', $customInvoice->id)->delete();

            foreach ($processedProducts as $item) {
                // Item-wise GST calculation
                $gst_details = [];
                $gst_total   = 0;
                if ($request->gst_option === 'with_gst') {
                    $prod = Product::find($item['product_id']);
                    if ($prod && $prod->gst_option === 'with_gst' && $prod->product_gst) {
                        $prodTaxes = json_decode($prod->product_gst, true);
                        if (is_array($prodTaxes)) {
                            foreach ($prodTaxes as $ptax) {
                                $trate          = floatval($ptax['tax_rate'] ?? 0);
                                $tamt           = ($item['total'] * $trate) / 100;
                                $gst_total     += $tamt;
                                $gst_details[]  = [
                                    'name'   => $ptax['tax_name'] ?? '',
                                    'rate'   => $trate,
                                    'amount' => $tamt,
                                ];
                            }
                        }
                    }
                }

                CustomInvoiceItem::create([
                    'invoice_id'          => $customInvoice->id,
                    'item'                => $item['product_id'],
                    'quantity'            => $item['quantity'],
                    'price'               => $item['price'],
                    'amount_total'        => $item['total'],
                    'product_gst_details' => json_encode($gst_details),
                    'product_gst_total'   => $gst_total,
                    'vendor_id'           => $vendor_id,
                    'customer_id'         => $customer_id,
                    'purchase_status'     => $purchaseStatus,
                    'payment_status'      => $paymentStatus,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'    => true,
                'message'    => 'Invoice updated successfully!',
                'invoice_id' => $customInvoice->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getHistory1($job_card_id)
    {
        $history = PaymentStore::where('custom_invoice_id', $job_card_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    public function makePaymentSubmit(Request $request)
    {
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([

            'custom_invoice_id' => 'nullable|integer',
            'payment_amount'    => 'nullable|numeric',
            'payment_date'      => 'nullable|date',
            'payment_type'      => 'nullable|string',
            'emi_month'         => 'nullable|integer',
            'pending_date'      => 'nullable|date',
            'new_emi_value'     => 'nullable',
            'emi_paid_value'    => 'nullable|numeric',
        ]);

        if ($request->filled('custom_invoice_id')) {
            // Determine payment amount
            $paymentAmount = $request->emi_total_new ?? $request->emi_total ?? $request->amount ?? $request->upi_online_amount ?? 0;

            if ($request->filled('cash_amount') && $request->filled('online_amount')) {
                $paymentAmount = (float) $request->cash_amount + (float) $request->online_amount;
            } elseif ($request->filled('fully_cash_amount') && $request->filled('full_online_amount')) {
                $paymentAmount = (float) $request->fully_cash_amount + (float) $request->full_online_amount;
            } elseif ($request->cashAmount) {
                $paymentAmount = $request->cashAmount;
            } elseif ($request->upi_online_amount) {
                $paymentAmount = $request->upi_online_amount;
            } elseif ($request->emi_monthly) {
                $paymentAmount = $request->emi_monthly;
            }

            // Determine payment type
            if (
                in_array($request->paid_type, ['cash_partially']) ||
                in_array($request->online_type, ['online_partially']) ||
                in_array($request->cash_online_type, ['cash_online_partially'])
            ) {
                $type = 'partially';
            } elseif (
                in_array($request->paid_type, ['cash_fully']) ||
                in_array($request->online_type, ['online_fully']) ||
                in_array($request->cash_online_type, ['cash_online_fully']) ||
                $request->payment_type === 'fully'

            ) {
                $type = 'fully';
            } elseif (
                in_array($request->emi_type, ['emi'])
            ) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            // Fetch Invoice if exists
            $invoice = $request->custom_invoice_id ? CustomInvoice::find($request->custom_invoice_id) : null;

            // Calculate remaining amount
            $currentRemaining = $invoice->remaining_amount ?? 0;
            $newRemaining     = max(0, $currentRemaining - $paymentAmount);
            // dd($currentRemaining,$newRemaining);

            $payment = PaymentStore::create([
                'user_id'           => $user_id,
                // 'order_id' => 0,
                'custom_invoice_id' => $request->custom_invoice_id,
                'payment_amount'    => $paymentAmount,
                'remaining_amount'  => $newRemaining,
                'payment_method'    => $request->payment_method ?? $request->payment_type ?? '',
                'payment_date'      => \Carbon\Carbon::now(),
                'payment_type'      => $type,
                'cash_amount'       => $request->cash_amount,
                'upi_amount'        => $request->online_amount,
                'emi_month'         => $request->emi_month ?? 1,
                'isDeleted'         => 0,
            ]);

            // Update invoice
            if ($invoice) {
                $updateData = ['remaining_amount' => $newRemaining];

                // If new EMI is being set
                if ($request->filled('new_emi_value') && $request->payment_method == 'emi' || $request->filled('emi_paid_value')) {
                    //  dd($request->all());
                    $updateData['payment_method'] = 'EMI';
                    $updateData['emi_duration']   = $request->emi_month_new ?? $request->emi_month;
                    $updateData['emi_months']     = $request->emi_total_new ?? 1;
                } else {
                    // dd($request->filled('emi_paid_value'));
                    // Otherwise, keep the actual payment method (cash, online, cash_online)
                    $method = $request->payment_method ?? $request->payment_type ?? $updateData['payment_method'] ?? 'Cash';

                    // Normalize to proper values
                    if (strtolower($method) === 'online' || strtolower($method) === 'upi') {
                        $method = 'Online';
                    } elseif (strtolower($method) === 'cash') {
                        $method = 'Cash';
                    } elseif (strtolower($method) === 'emi') {
                        $method = 'EMI';
                    } else {
                        $method = 'Cash';
                    }

                    $updateData['payment_method'] = $method;
                }

                if ($request->emi_duration) {
                    $updateData['emi_duration'] = $request->emi_duration;
                }

                // Update next pending date if provided
                if ($request->pending_date) {
                    $updateData['next_pending_date'] = $request->pending_date;
                }

                $invoice->update($updateData);

                // ✅ Determine status
                $paymentStatus  = 'pending';
                $purchaseStatus = 'pending';

                if ($newRemaining <= 0) {
                    $paymentStatus  = 'completed';
                    $purchaseStatus = 'completed';
                } elseif ($newRemaining > 0 && $newRemaining < ($invoice->grand_total ?? 0)) {
                    $paymentStatus  = 'partially';
                    $purchaseStatus = 'partially';
                }

                // ✅ Update invoice
                $invoice->update([
                    'payment_status'   => $paymentStatus,
                    'status'           => $purchaseStatus,
                    'remaining_amount' => $newRemaining,
                    'updated_at'       => now(),
                ]);

                // ✅ Update all invoice items too
                CustomInvoiceItem::where('invoice_id', $invoice->id)
                    ->update([
                        'payment_status'  => $paymentStatus,
                        'purchase_status' => $purchaseStatus,
                        'updated_at'      => now(),
                    ]);
            }
        }
        return response()->json([
            'status'  => 'success',
            'message' => 'Payment submitted successfully.',
            'data'    => $payment,
        ]);
    }

    public function custom_invoice_pdf_download($id)
    {
        $user = Auth::guard('api')->user();

        $selectedSubAdminId = session('selectedSubAdminId');

        // Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $invoice = CustomInvoice::find($id);
        $setting = Setting::where('branch_id', $branch_id)->first();

        if (! $invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Invoice not found.',
            ], 404);
        }

        // Load vendor or customer
        $vendor   = $invoice->vendor_id ? User::with('details')->find($invoice->vendor_id) : null;
        $customer = $invoice->customer_id ? User::with('details')->find($invoice->customer_id) : null;
        $party    = $vendor ?: $customer;

        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        $taxDetails   = json_decode($invoice->taxes, true) ?? [];
        $invoiceItems = CustomInvoiceItem::where('invoice_id', $id)->with('product')->get();

        $subtotal           = $invoiceItems->sum('amount_total');
        $discountPercentage = $invoice->discount ?? 0;
        $discountAmount     = ($subtotal * $discountPercentage) / 100;
        $afterDiscount      = $subtotal - $discountAmount;
        $shipping           = $invoice->shipping ?? 0;
        $totalTaxAmount     = collect($taxDetails)->sum('amount');
        $finalTotal         = $afterDiscount + $totalTaxAmount + $shipping;

        // Party details
        $partyDetails = [
            'role'       => ucfirst($party->role ?? 'Party'),
            'name'       => $party->name ?? '--',
            'email'      => $party->email ?? '--',
            'phone'      => $party->phone ?? '--',
            'gst_number' => $party->gst_number ?? '--',
            'pan_number' => $party->pan_number ?? '--',
            'address'    => optional($party->details)->address ?? '--',
            'city'       => optional($party->details)->city ?? '--',
            'country'    => optional($party->details)->country ?? '--',
        ];

        $pdfData = [
            'view_id'        => $id,
            'sales'          => $invoice,
            'setting'        => $setting,
            'orderItems'     => $invoiceItems,
            'salesItems'     => $invoiceItems,
            'partyDetails'   => $partyDetails,
            'subtotal'       => $formatCurrency($subtotal),
            'discountAmount' => $formatCurrency($discountAmount),
            'afterDiscount'  => $formatCurrency($afterDiscount),
            'shipping'       => $formatCurrency($shipping),
            'finalTotal'     => $formatCurrency($finalTotal),
            'paymentStatus'  => $invoiceItems->first()?->payment_status ?? 'unpaid',
            'taxDetails'     => $taxDetails,
        ];

        // Generate PDF
        $pdf = PDF::loadView('custom-invoice.custom-invoice-pdf', $pdfData);

        // Save PDF to storage
        $fileName = 'custom_invoice_' . $id . '.pdf';

        $relativePath = 'custom-invoices/' . $fileName;

        \Storage::disk('public')->put($relativePath, $pdf->output());

        // Public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // Return JSON
        return response()->json([
            'status'    => true,
            'message'   => 'Custom Invoice PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function exportInvoicePDFAPI(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role   = $user->role;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

        $setting = Setting::where('branch_id', $selectedSubAdminId)->first();

        try {
            $query = CustomInvoice::with(['vendor:id,name', 'customer:id,name', 'payments'])
                ->where('branch_id', $selectedSubAdminId);

            if ($request->filled('year')) {
                $query->whereYear('created_at', $request->year);
            }

            if ($request->filled('month')) {
                $query->whereMonth('created_at', $request->month);
            }

            if ($request->filled('date')) {
                $inputDate = trim($request->date);
                if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $inputDate)) {
                    [$day, $month, $year] = explode('-', $inputDate);
                    $formattedDate        = "$year-$month-$day";
                } else {
                    $formattedDate = $inputDate;
                }
                $query->whereDate('created_at', $formattedDate);
            }

            // role based
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } else {
                $query->where('branch_id', $userId);
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No invoices found for the given criteria.',
                ], 404);
            }

            $payments = PaymentStore::whereIn('custom_invoice_id', $invoices->pluck('id'))->get();

            $pdf = Pdf::loadView('custom-invoice.custom_invoice_pdf', [
                'invoices' => $invoices,
                'payments' => $payments,
                'setting'  => $setting,
            ])->setPaper('A4', 'portrait');

            $fileName     = 'CustomInvoices_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'Custom-Invoices/' . $fileName;

            \Storage::disk('public')->put($relativePath, $pdf->output());

            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status'    => true,
                'message'   => 'Custom Invoices PDF generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $fileName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function exportCustom_invoice_api(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId             = $user->id;
        $role               = $user->role;
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $userId;

        $year  = $request->query('year');
        $month = $request->query('month');
        $date  = $request->query('date');

        try {
            $query = CustomInvoice::query()
                ->with([
                    'vendor:id,name,phone',
                    'customer:id,name,phone',
                ])
                ->where('isDeleted', 0);

            // 🔹 Role-based filter
            if ($role === 'sub-admin') {
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin' && $selectedSubAdminId) {
                $query->where('branch_id', $selectedSubAdminId);
            } else {
                $query->where('branch_id', $userId);
            }

            // 🔹 Filters
            if (! empty($year)) {
                $query->whereYear('created_at', $year);
            }
            if (! empty($month)) {
                $query->whereMonth('created_at', $month);
            }
            if (! empty($date)) {
                $dateParts = explode('-', $date); // d-m-Y
                if (count($dateParts) === 3) {
                    $formattedDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                    $query->whereDate('created_at', $formattedDate);
                }
            }

            $invoices = $query->orderBy('created_at', 'desc')->get();

            $formatIndian = function ($num) {
                $num = (float)$num;
                $explode = explode(".", number_format($num, 2, '.', ''));
                $whole = $explode[0];
                $decimal = $explode[1];

                $lastThree = substr($whole, -3);
                $restUnits = substr($whole, 0, -3);
                if ($restUnits != '') {
                    $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
                    $whole = $restUnits . "," . $lastThree;
                }
                return $whole . "." . $decimal;
            };

            // 🔹 Excel generation
            $spreadsheet = new Spreadsheet();
            $sheet       = $spreadsheet->getActiveSheet();

            // ✅ Headers
            $headers = [
                'A1' => 'Invoice Number',
                'B1' => 'Vendor Name',
                'C1' => 'Vendor Phone',
                'D1' => 'Customer Name',
                'E1' => 'Customer Phone',
                'F1' => 'Total Amount',
                'G1' => 'Paid',
                'H1' => 'Discount',
                'I1' => 'Shipping',
                'J1' => 'Grand Total',
                'K1' => 'Remaining Amount',
                'L1' => 'Status',
                'M1' => 'Created At',
            ];
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $sheet->getStyle('A1:M1')->getFont()->setBold(true);

            // ✅ Rows
            $row = 2;
            foreach ($invoices as $invoice) {
                $sheet->setCellValue('A' . $row, $invoice->invoice_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $invoice->vendor->name ?? 'N/A');
                $sheet->setCellValue('C' . $row, $invoice->vendor->phone ?? 'N/A');
                $sheet->setCellValue('D' . $row, $invoice->customer->name ?? 'N/A');
                $sheet->setCellValue('E' . $row, $invoice->customer->phone ?? 'N/A');
                $sheet->setCellValue('F' . $row, $formatIndian($invoice->total_amount ?? 0));
                $sheet->setCellValue('G' . $row, $formatIndian($invoice->paid ?? 0));
                $sheet->setCellValue('H' . $row, $formatIndian($invoice->discount ?? 0));
                $sheet->setCellValue('I' . $row, $formatIndian($invoice->shipping ?? 0));
                $sheet->setCellValue('J' . $row, $formatIndian($invoice->grand_total ?? 0));
                $sheet->setCellValue('K' . $row, $formatIndian($invoice->remaining_amount ?? 0));
                $sheet->setCellValue('L' . $row, $invoice->status ?? 'N/A');
                $sheet->setCellValue('M' . $row, $invoice->created_at->format('Y-m-d'));
                $row++;
            }

            $writer = new Xlsx($spreadsheet);

            // ✅ Generate filename and relative path
            $filename     = "CustomInvoices_" . date('Ymd_His') . ".xlsx";
            $relativePath = 'Custom-Invoices/' . $filename;

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
                'message'   => 'Custom Invoices Excel generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $filename,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
