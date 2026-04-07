<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Notification;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\PurchaseInvoice;
use App\Models\Purchases;
use App\Models\RowMaterial;
use App\Models\RowMaterialInventory;
use App\Models\RowMaterialPurchase;
use App\Models\RowMaterialPurchaseInvoice;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\UserDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class PurchaseController extends Controller
{
    private function resolvePurchaseBranchId(Request $request, $user): int
    {
        if ($user->role === 'staff' && $user->branch_id) {
            return (int) $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($request->selectedSubAdminId)) {
            return (int) $request->selectedSubAdminId;
        }

        return (int) $user->id;
    }

    private function createOrResolveVendor(Request $request, int $userId, int $branchId): int
    {
        if (is_numeric($request->vendor_id)) {
            return (int) $request->vendor_id;
        }

        $vendorPhone = $request->vendor_phone;

        if ($vendorPhone !== null && $vendorPhone !== '') {
            $existingVendor = User::where('phone', $vendorPhone)->first();

            if ($existingVendor) {
                throw new \RuntimeException('A vendor with this phone number already exists.');
            }
        }

        $vendor = User::create([
            'name'       => $request->vendor_id,
            'phone'      => $request->vendor_phone ?? null,
            'role'       => 'vendor',
            'status'     => $request->status,
            'branch_id'  => $branchId,
            'created_by' => $userId,
        ]);

        UserDetail::create([
            'user_id'   => $vendor->id,
            'address'   => '',
            'city'      => '',
            'state'     => '',
            'country'   => '',
            'pincode'   => '',
            'branch_id' => $branchId,
        ]);

        return (int) $vendor->id;
    }


    public function purchase_order(Request $request)
    {
        // dd($request->all());
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
            'vendor_id'              => 'required',
            'status'                 => 'required',
            'vendor_phone'           => 'nullable|numeric',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'bill_no'                 => 'required',
            'grand_total'            => 'required|numeric',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'required',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'products.*.discount_amount'  => 'nullable|numeric|min:0',
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

            // ✅ Generate a unique invoice number
            do {
                $invoice_number = 'INV-' . mt_rand(10000000, 99999999);
            } while (PurchaseInvoice::where('invoice_number', $invoice_number)->exists());

            // ✅ Check and insert vendor
            if (! is_numeric($request->vendor_id)) {
                // Check if phone already exists
                $vendorPhone = $request->vendor_phone;

                if ($vendorPhone !== null && $vendorPhone !== '') {
                    // Check if phone already exists
                    $existingVendor = User::where('phone', $vendorPhone)->first();

                    if ($existingVendor) {
                        return response()->json([
                            'success' => false,
                            'message' => 'A vendor with this phone number already exists.',
                        ], 409);
                    }
                }
                $vendor = User::create([
                    'name'       => $request->vendor_id,
                    'phone'      => $request->vendor_phone ?? null,
                    'role'       => 'vendor',
                    'status'     => $request->status,
                    'branch_id'  => $userBranchId ?? $userId,
                    'created_by' => $userId,
                ]);
                $vendor_id = $vendor->id;

                // ✅ Create UserDetail record after user is created
                UserDetail::create([
                    'user_id'   => $vendor_id,
                    'address'   => '', // adjust if you collect address info
                    'city'      => '',
                    'state'     => '',
                    'country'   => '',
                    'pincode'   => '',
                    'branch_id' => $userBranchId ?? $userId,
                ]);
            } else {
                $vendor_id = $request->vendor_id;
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

            $purchasesData  = [];
            $inventoryData  = [];
            $productUpdates = [];
            $now            = now();

            foreach ($request->products as $product) {
                // Handle Category
                if (! is_numeric($product['category_id'])) {
                    $category    = Category::create([
                        'name' => $product['category_id'],
                        'branch_id' => $userBranchId ?? $userId
                    ]);
                    $category_id = $category->id;
                } else {
                    $category_id = $product['category_id'];
                }

                if (! is_numeric($product['id'])) {
                    // New product
                    do {
                        $sku = mt_rand(10000000, 99999999);
                    } while (Product::where('SKU', $sku)->exists());

                    $newProduct = Product::create([
                        'name'          => $product['id'],
                        'category_id'   => $category_id,
                        'price'         => $product['price'],
                        'quantity'      => $product['quantity'],
                        'vendor_id'     => $vendor_id,
                        'availablility' => 'in_stock',
                        'status'        => 'active',
                        'SKU'           => $sku,
                        'branch_id'     => $userBranchId ?? $userId,
                    ]);
                    $product_id = $newProduct->id;
                } else {
                    // Existing product
                    $existingProduct = $existingProducts->get($product['id']);
                    if ($existingProduct) {
                        $newQuantity                          = $existingProduct->quantity + $product['quantity'];
                        $productUpdates[$existingProduct->id] = [
                            'quantity'    => $newQuantity,
                            'category_id' => $category_id,
                        ];
                        $product_id = $existingProduct->id;
                    } else {
                        continue; // Skip if product not found
                    }
                }

                $processedProducts[] = [
                    'product_id'       => $product_id,
                    'price'            => $product['price'],
                    'quantity'         => $product['quantity'],
                    'discount_percent' => $product['discount_percent'] ?? 0,
                    'discount_amount'  => $product['discount_amount'] ?? 0,
                    'total'            => $product['total'],
                ];
            }
            // Determine remaining amount
            $remainingAmount = 0;

            if (empty($request->payment_mode)) {
                // No payment made yet
                $remainingAmount = $request->grand_total;
            } elseif (($request->paid_type ?? 'full') === 'full') {
                // Full payment done → no remaining amount
                $remainingAmount = 0;
            } else {
                // Partial payment → remaining = grand_total - paid_amount
                $paidAmount = $request->amount ?? (($request->cash_amount ?? 0) + ($request->upi_amount ?? 0));

                $remainingAmount = $request->grand_total - $paidAmount;
            }

            // ✅ Store purchase invoice
            $purchaseInvoice = PurchaseInvoice::create([
                'invoice_number'   => $invoice_number,
                'vendor_id'        => $vendor_id,
                'products'         => json_encode($processedProducts),
                'total_amount'     => collect($processedProducts)->sum('total'),
                'discount'         => $request->discount ?? 0,
                'shipping'         => $request->shipping,
                'grand_total'      => $request->grand_total,
                'remaining_amount' => $remainingAmount,
                'gst_option'       => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'status'           => $request->status,
                'taxes'            => json_encode($request->taxes),
                'branch_id'        => $userBranchId ?? $userId,
                'created_by'       => $userId,
                'bill_no'          => $request->bill_no,
            ]);

            // ✅ Determine overall payment status dynamically
            // $paymentStatus = 'unpaid';

            // if (! empty($request->payment_mode)) {

            //     if (($request->paid_type ?? 'full') === 'full') {
            //         $paymentStatus = 'paid';
            //     } else {
            //         $paidAmount = $request->amount ?? (($request->upi_amount ?? 0) + ($request->cash_amount ?? 0));

            //         if ($paidAmount > 0 && $paidAmount < $request->grand_total) {
            //             $paymentStatus = 'partial';
            //         }
            //     }
            // }
            // ✅ Default values
            $purchaseStatus = 'pending';
            $paymentStatus  = 'pending';

            // ✅ If payment mode exists
            if (! empty($request->payment_mode)) {

                // FULL PAYMENT → COMPLETED + COMPLETED
                if (($request->paid_type ?? 'full') === 'full') {
                    $purchaseStatus = 'completed';
                    $paymentStatus  = 'completed';
                }

                // PARTIAL PAYMENT
                elseif (($request->paid_type ?? '') === 'partial') {
                    $purchaseStatus = 'pending';
                    $paymentStatus  = 'partially';
                }
            }

            // ✅ Pending payment mode (no payment done)
            if ($request->payment_mode === 'pending') {
                $purchaseStatus = 'pending';
                $paymentStatus  = 'pending';
            }

            // dd($paymentStatus);
            // ✅ OPTIMIZED: Bulk insert purchases and inventory
            foreach ($processedProducts as $item) {
                $product_id   = $item['product_id'];
                $price        = $item['price'];
                $quantity     = $item['quantity'];
                $baseTotal    = $price * $quantity;
                $gst_details  = [];
                $gst_total    = 0;

                // Product-wise GST calculation (on base price)
                if ($request->gst_option === 'with') {
                    $prod = $existingProducts->get($product_id);
                    if ($prod && $prod->gst_option === 'with_gst' && $prod->product_gst) {
                        $taxes = json_decode($prod->product_gst, true);
                        if (is_array($taxes)) {
                            foreach ($taxes as $tax) {
                                $taxRate   = floatval($tax['tax_rate'] ?? 0);
                                $taxAmount = ($baseTotal * $taxRate) / 100;
                                $gst_total += $taxAmount;
                                $gst_details[] = [
                                    'name'   => $tax['tax_name'] ?? '',
                                    'rate'   => $taxRate,
                                    'amount' => $taxAmount,
                                ];
                            }
                        }
                    }
                }

                $purchasesData[] = [
                    'invoice_id'          => $purchaseInvoice->id,
                    'item'                => $product_id,
                    'quantity'            => $quantity,
                    'price'               => $price,
                    'discount_percent'    => $item['discount_percent'] ?? 0,
                    'discount_amount'     => $item['discount_amount'] ?? 0,
                    'amount_total'        => $item['total'],
                    'product_gst_details' => json_encode($gst_details),
                    'product_gst_total'   => $gst_total,
                    'vendor_id'           => $vendor_id,
                    'purchase_status'     => $purchaseStatus,
                    'payment_status'      => $paymentStatus,
                    'branch_id'           => $userBranchId ?? $userId,
                    'created_by'          => $userId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                // Prepare inventory data
                $lastInventory = $lastInventories->get($item['product_id']);
                $currentStock  = $lastInventory
                    ? ($lastInventory->current_stock + $item['quantity'])
                    : $item['quantity'];

                $inventoryData[] = [
                    'product_id'    => $item['product_id'],
                    'initial_stock' => $lastInventory->initial_stock ?? $currentStock - $item['quantity'],
                    'current_stock' => $currentStock,
                    'branch_id'     => $userBranchId ?? $userId,
                    'create_by'     => $userId,
                    'type'          => 'Purchase',
                    'date'          => $now,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            // ✅ OPTIMIZED: Bulk insert purchases
            if (! empty($purchasesData)) {
                Purchases::insert($purchasesData);
            }

            // ✅ OPTIMIZED: Bulk update products
            foreach ($productUpdates as $productId => $updateData) {
                Product::where('id', $productId)->update($updateData);
            }

            // ✅ OPTIMIZED: Bulk insert inventory records
            if (! empty($inventoryData)) {
                ProductInventory::insert($inventoryData);
            }

            // ✅ Payment handling
            if (! empty($request->payment_mode)) {
                $paymentMode = strtolower($request->payment_mode);
                $paidType    = strtolower($request->paid_type ?? 'full');
                $cashAmount  = (float) ($request->cash_amount ?? 0);
                // $upiAmount   = (float) ($request->upi_amount ?? 0);
                $upiAmount  = (float) ($request->upi_amount ?? ($request->amount ?? 0)); // ✅ FIXED HERE
                $grandTotal = (float) ($request->grand_total ?? 0);

                // ✅ Calculate total paid and pending
                $totalPaid = $cashAmount + $upiAmount;
                $pending   = max(0, $grandTotal - $totalPaid); // prevents negative

                // ✅ CASE 1: Cash + Online (both)
                if ($paymentMode === 'cashonline') {
                    $payments = [];

                    // 1️⃣ Insert Cash Payment
                    if ($cashAmount > 0) {
                        $payments[] = PaymentStore::create([
                            'user_id'          => $vendor_id,
                            'purchase_id'      => $purchaseInvoice->id,
                            'payment_amount'   => $cashAmount,
                            'payment_date'     => now(),
                            'payment_method'   => 'Cash',
                            'payment_type'     => $paidType,
                            'cash_amount'      => $cashAmount,
                            'upi_amount'       => 0,
                            'remaining_amount' => $pending,
                            'status'           => 'debit', // ✅ ADD THIS
                            'bank_id'          => $request->bank_id,
                            'emi_month'        => null,
                            'order_id'         => null,
                            'jobcard_id'       => 0,
                            'isDeleted'        => 0,
                        ]);
                    }

                    // 2️⃣ Insert Online Payment
                    if ($upiAmount > 0) {
                        $payments[] = PaymentStore::create([
                            'user_id'          => $vendor_id,
                            'purchase_id'      => $purchaseInvoice->id,
                            'payment_amount'   => $upiAmount,
                            'payment_date'     => now(),
                            'payment_method'   => 'Online',
                            'payment_type'     => $paidType,
                            'cash_amount'      => 0,
                            'upi_amount'       => $upiAmount,
                            'remaining_amount' => $pending,
                            'status'           => 'debit', // ✅ ADD THIS
                            'bank_id'          => $request->bank_id,
                            'emi_month'        => null,
                            'order_id'         => null,
                            'jobcard_id'       => 0,
                            'isDeleted'        => 0,
                        ]);
                    }
                }

                // ✅ CASE 2: Cash Only
                elseif ($paymentMode === 'cash') {
                    PaymentStore::create([
                        'user_id'          => $vendor_id,
                        'purchase_id'      => $purchaseInvoice->id,
                        'payment_amount'   => $cashAmount ?: ($request->amount ?? 0),
                        'payment_date'     => now(),
                        'payment_method'   => 'Cash',
                        'payment_type'     => $paidType,
                        'cash_amount'      => $cashAmount ?: ($request->amount ?? 0),
                        'upi_amount'       => 0,
                        'remaining_amount' => $pending,
                        'status'           => 'debit', // ✅ ADD THIS
                        'emi_month'        => null,
                        'order_id'         => null,
                        'jobcard_id'       => 0,
                        'isDeleted'        => 0,
                    ]);
                }

                // ✅ CASE 3: Online Only
                elseif ($paymentMode === 'online') {
                    PaymentStore::create([
                        'user_id'          => $vendor_id,
                        'purchase_id'      => $purchaseInvoice->id,
                        'payment_amount'   => $upiAmount ?: ($request->amount ?? 0),
                        'payment_date'     => now(),
                        'payment_method'   => 'online',
                        'payment_type'     => $paidType,
                        'cash_amount'      => 0,
                        'upi_amount'       => $upiAmount ?: ($request->amount ?? 0),
                        'remaining_amount' => $pending,
                        'status'           => 'debit', // ✅ ADD THIS
                        'bank_id'          => $request->bank_id,
                        'emi_month'        => null,
                        'order_id'         => null,
                        'jobcard_id'       => 0,
                        'isDeleted'        => 0,
                    ]);
                }

                // ✅ CASE 4: Pending (no immediate payment)
                // elseif ($paymentMode === 'pending') {
                //     PaymentStore::create([
                //         'user_id'          => $vendor_id,
                //         'purchase_id'      => $purchaseInvoice->id,
                //         'payment_amount'   => 0,
                //         'payment_date'     => now(),
                //         'payment_method'   => 'Pending',
                //         'payment_type'     => 'none',
                //         'cash_amount'      => 0,
                //         'upi_amount'       => 0,
                //         'remaining_amount' => $request->grand_total ?? 0,
                //         'emi_month'        => null,
                //         'order_id'         => null,
                //         'jobcard_id'       => 0,
                //         'isDeleted'        => 0,
                //     ]);
                // }
            }
            // ✅ After payment handling and before DB::commit()
            if (! empty($request->payment_mode)) {
                $grandTotal = (float) ($request->grand_total ?? 0);
                $cashAmount = (float) ($request->cash_amount ?? 0);
                // $upiAmount   = (float) ($request->upi_amount ?? 0);
                $upiAmount = (float) ($request->upi_amount ?? ($request->amount ?? 0));
                $totalPaid = $cashAmount + $upiAmount;
                $remaining = max(0, $grandTotal - $totalPaid);

                // ✅ Update remaining_amount in purchase invoice
                $purchaseInvoice->update([
                    'remaining_amount' => $remaining,
                ]);
            }

            // ==============================================
            // 🔔 CREATE NOTIFICATIONS (Similar to order_sale)
            // ==============================================
            $vendor = User::find($vendor_id);
            // 1. Notification for the vendor (if vendor exists)
            if ($vendor) {
                $vendorNotificationTitle = 'New Purchase Order Created';
                $vendorNotificationMessage = "Dear {$vendor->name}, a new purchase order #{$invoice_number} has been created for you. Total amount: " . ($request->grand_total ?? 0);

                Notification::create([
                    'user_id'   => $userId,
                    'type'      => 'purchase_order',
                    'title'     => $vendorNotificationTitle,
                    'message'   => $vendorNotificationMessage,
                    'link'      => '/print-purchase/' . $purchaseInvoice->id,
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $userBranchId ?? $userId,
                ]);
            }



            DB::commit();

            // Fetch latest payment info for this purchase
            $payment = PaymentStore::where('purchase_id', $purchaseInvoice->id)
                ->orderBy('id', 'desc')
                ->first();

            return response()->json([
                'success'        => true,
                'message'        => 'Purchase invoice and purchase records created successfully!',
                'invoice_number' => $invoice_number,
                'purchase_id'    => $purchaseInvoice->id, // ✅ Add this line
                'payment'        => $payment,             // <-- add payment info for frontend
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function row_material_purchase_order(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $branchId = $this->resolvePurchaseBranchId($request, $user);

        $validator = Validator::make($request->all(), [
            'vendor_id'              => 'required',
            'status'                 => 'required',
            'vendor_phone'           => 'nullable|numeric',
            'discount'               => 'nullable|numeric',
            'shipping'               => 'required|numeric',
            'bill_no'                => 'required',
            'grand_total'            => 'required|numeric',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'required',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'products.*.discount_amount'  => 'nullable|numeric|min:0',
            'products.*.total'       => 'required|numeric|min:0',
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

            do {
                $invoiceNumber = 'RMP-' . mt_rand(10000000, 99999999);
            } while (RowMaterialPurchaseInvoice::where('invoice_number', $invoiceNumber)->exists());

            $vendorId = $this->createOrResolveVendor($request, $userId, $branchId);

            $materialIds = array_filter(array_column($request->products, 'id'), 'is_numeric');
            $existingMaterials = ! empty($materialIds)
                ? RowMaterial::whereIn('id', $materialIds)->get()->keyBy('id')
                : collect();

            $lastInventories = ! empty($materialIds)
                ? RowMaterialInventory::whereIn('row_material_id', $materialIds)
                    ->orderBy('row_material_id')
                    ->orderBy('id', 'desc')
                    ->get()
                    ->groupBy('row_material_id')
                    ->map(function ($group) {
                        return $group->first();
                    })
                : collect();

            $processedMaterials = [];
            $purchaseRows = [];
            $inventoryRows = [];
            $materialUpdates = [];
            $now = now();

            foreach ($request->products as $material) {
                if (! is_numeric($material['id'])) {
                    continue;
                }

                $existingMaterial = $existingMaterials->get($material['id']);
                if (! $existingMaterial) {
                    continue;
                }

                $quantity = (float) $material['quantity'];
                $newQuantity = (float) $existingMaterial->quantity + $quantity;
                $materialUpdates[$existingMaterial->id] = [
                    'quantity' => $newQuantity,
                    'category_id' => $material['category_id'],
                    'price' => $material['price'],
                    'availablility' => $newQuantity > 0 ? 'in_stock' : 'out_stock',
                ];

                $processedMaterials[] = [
                    'row_material_id'   => $existingMaterial->id,
                    'price'             => $material['price'],
                    'quantity'          => $quantity,
                    'discount_percent'  => $material['discount_percent'] ?? 0,
                    'discount_amount'   => $material['discount_amount'] ?? 0,
                    'total'             => $material['total'],
                ];
            }

            $remainingAmount = 0;
            if (empty($request->payment_mode)) {
                $remainingAmount = $request->grand_total;
            } elseif (($request->paid_type ?? 'full') === 'full') {
                $remainingAmount = 0;
            } else {
                $paidAmount = $request->amount ?? (($request->cash_amount ?? 0) + ($request->upi_amount ?? 0));
                $remainingAmount = $request->grand_total - $paidAmount;
            }

            $invoice = RowMaterialPurchaseInvoice::create([
                'invoice_number'   => $invoiceNumber,
                'vendor_id'        => $vendorId,
                'materials'        => json_encode($processedMaterials),
                'total_amount'     => collect($processedMaterials)->sum('total'),
                'discount'         => $request->discount ?? 0,
                'shipping'         => $request->shipping,
                'grand_total'      => $request->grand_total,
                'remaining_amount' => $remainingAmount,
                'gst_option'       => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'status'           => $request->status,
                'taxes'            => json_encode($request->taxes),
                'branch_id'        => $branchId,
                'created_by'       => $userId,
                'bill_no'          => $request->bill_no,
            ]);

            $purchaseStatus = 'pending';
            $paymentStatus = 'pending';

            if (! empty($request->payment_mode)) {
                if (($request->paid_type ?? 'full') === 'full') {
                    $purchaseStatus = 'completed';
                    $paymentStatus = 'completed';
                } elseif (($request->paid_type ?? '') === 'partial') {
                    $purchaseStatus = 'pending';
                    $paymentStatus = 'partially';
                }
            }

            if ($request->payment_mode === 'pending') {
                $purchaseStatus = 'pending';
                $paymentStatus = 'pending';
            }

            foreach ($processedMaterials as $item) {
                $materialId = $item['row_material_id'];
                $quantity = $item['quantity'];
                $lastInventory = $lastInventories->get($materialId);
                $currentStock = $lastInventory
                    ? ((float) $lastInventory->current_stock + $quantity)
                    : $quantity;

                $purchaseRows[] = [
                    'invoice_id'          => $invoice->id,
                    'item'                => $materialId,
                    'quantity'            => $quantity,
                    'price'               => $item['price'],
                    'discount_percent'    => $item['discount_percent'] ?? 0,
                    'discount_amount'     => $item['discount_amount'] ?? 0,
                    'amount_total'        => $item['total'],
                    'product_gst_details' => json_encode([]),
                    'product_gst_total'   => 0,
                    'vendor_id'           => $vendorId,
                    'purchase_status'     => $purchaseStatus,
                    'payment_status'      => $paymentStatus,
                    'branch_id'           => $branchId,
                    'created_by'          => $userId,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];

                $inventoryRows[] = [
                    'row_material_id' => $materialId,
                    'initial_stock'   => $lastInventory->current_stock ?? ((float) $currentStock - $quantity),
                    'current_stock'   => $currentStock,
                    'branch_id'       => $branchId,
                    'create_by'       => $userId,
                    'type'            => 'Purchase',
                    'date'            => $now,
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ];
            }

            if (! empty($purchaseRows)) {
                RowMaterialPurchase::insert($purchaseRows);
            }

            foreach ($materialUpdates as $materialId => $updateData) {
                RowMaterial::where('id', $materialId)->update($updateData);
            }

            if (! empty($inventoryRows)) {
                RowMaterialInventory::insert($inventoryRows);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Row material purchase created successfully.',
                'purchase_id' => $invoice->id,
            ]);
        } catch (\RuntimeException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 409);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function row_material_purchase_list(Request $request)
    {
        $user = Auth::guard('api')->user();
        $branchId = $this->resolvePurchaseBranchId($request, $user);
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);
        $search = $request->input('search', '');

        $query = DB::table('row_material_purchase_invoice')
            ->join('row_material_purchases', function ($join) use ($branchId, $user) {
                $join->on('row_material_purchase_invoice.id', '=', 'row_material_purchases.invoice_id')
                    ->where('row_material_purchases.isDeleted', '=', 0);

                if ($user->role === 'staff') {
                    $join->where('row_material_purchases.created_by', '=', $user->id);
                } else {
                    $join->where('row_material_purchases.branch_id', '=', $branchId);
                }
            })
            ->join('users', 'row_material_purchase_invoice.vendor_id', '=', 'users.id')
            ->join('row_material', 'row_material_purchases.item', '=', 'row_material.id')
            ->select(
                'row_material_purchase_invoice.id',
                'users.name as vendor_name',
                'row_material_purchase_invoice.invoice_number',
                'row_material_purchase_invoice.grand_total',
                'row_material_purchase_invoice.remaining_amount',
                'row_material_purchases.purchase_status',
                'row_material_purchases.payment_status',
                'row_material_purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(row_material.row_materialname SEPARATOR ', ') as material_names"),
                DB::raw("GROUP_CONCAT(row_material_purchases.price SEPARATOR ', ') as material_prices"),
                DB::raw("GROUP_CONCAT(row_material_purchases.quantity SEPARATOR ', ') as material_quantities")
            )
            ->where('row_material_purchase_invoice.isDeleted', '=', 0);

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('row_material_purchase_invoice.invoice_number', 'LIKE', "%{$search}%")
                    ->orWhere('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('row_material.row_materialname', 'LIKE', "%{$search}%");
            });
        }

        $countQuery = clone $query;
        $totalCount = $countQuery
            ->groupBy(
                'row_material_purchase_invoice.id',
                'users.name',
                'row_material_purchase_invoice.invoice_number',
                'row_material_purchase_invoice.grand_total',
                'row_material_purchase_invoice.remaining_amount',
                'row_material_purchases.purchase_status',
                'row_material_purchases.payment_status',
                'row_material_purchase_invoice.created_at'
            )
            ->get()
            ->count();

        $records = $query
            ->groupBy(
                'row_material_purchase_invoice.id',
                'users.name',
                'row_material_purchase_invoice.invoice_number',
                'row_material_purchase_invoice.grand_total',
                'row_material_purchase_invoice.remaining_amount',
                'row_material_purchases.purchase_status',
                'row_material_purchases.payment_status',
                'row_material_purchase_invoice.created_at'
            )
            ->orderByDesc('row_material_purchase_invoice.id')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $records,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $perPage > 0 ? (int) ceil($totalCount / $perPage) : 1,
                'per_page' => $perPage,
                'total' => $totalCount,
            ],
        ]);
    }

    // public function purchase_list(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     // dd($user);
    //     $branch_id = $user->id ?? null;

    //     if ($user->role === 'staff' && $user->id) {
    //         $branch_id = $user->id;
    //     } elseif ($user->role === 'sub-admin') {
    //         $branch_id = $user->id;
    //     } elseif ($user->role === 'admin' && ! empty($request->selectedSubAdminId)) {
    //         $branch_id = (int) $request->selectedSubAdminId;
    //     }

    //     // if (!empty($request->selectedSubAdminId)) {
    //     //     $branch_id = $request->selectedSubAdminId;
    //     // }
    //     if ($user->role === 'staff') {
    //         $query = DB::table('purchase_invoice')
    //             ->join('purchases', function ($join) use ($branch_id) {
    //                 $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
    //                     ->where('purchases.isDeleted', '=', 0)
    //                     ->where('purchases.created_by', '=', $branch_id);
    //             });
    //     } else {
    //         $query = DB::table('purchase_invoice')
    //             ->join('purchases', function ($join) use ($branch_id) {
    //                 $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
    //                     ->where('purchases.isDeleted', '=', 0)
    //                     ->where('purchases.branch_id', '=', $branch_id);
    //             });
    //     }

    //     // ✅ Continue common joins + select for both cases
    //     $query = $query
    //         ->join('users', 'purchases.vendor_id', '=', 'users.id')
    //         ->join('products', 'purchases.item', '=', 'products.id')
    //         ->select(
    //             'purchase_invoice.id',
    //             'users.name as vendor_name',
    //             'purchase_invoice.invoice_number',
    //             'purchase_invoice.grand_total',
    //             'purchase_invoice.remaining_amount',
    //             'purchases.purchase_status',
    //             'purchases.payment_status',
    //             'purchase_invoice.created_at as date',
    //             DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
    //             DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
    //             DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
    //             //     DB::raw("(SELECT COUNT(*) FROM payment_store
    //             //       WHERE payment_store.purchase_id = purchase_invoice.id
    //             //       AND payment_store.isDeleted = 0) as has_payment")
    //             // )
    //             DB::raw("(SELECT COALESCE(SUM(payment_store.payment_amount),0)
    //             FROM payment_store
    //             WHERE payment_store.purchase_id = purchase_invoice.id
    //             AND payment_store.isDeleted = 0) as total_paid"),
    //             DB::raw("(SELECT COALESCE(SUM(purchase_returns.total_amount), 0)
    //             FROM purchase_returns
    //             WHERE purchase_returns.purchase_id = purchase_invoice.id
    //             AND purchase_returns.isDeleted = 0) as total_return")
    //         )
    //         ->where('purchase_invoice.isDeleted', '=', 0);
    //     // ✅ Only non-deleted invoices

    //     if ($request->has('date') && ! empty($request->date)) {
    //         // 🟩 Only apply date filter — ignore month & year
    //         $query->whereDate('purchases.created_at', $request->date);
    //     } else {
    //         // 🟦 If date is not set, apply month & year
    //         if ($request->has('month') && ! empty($request->month)) {
    //             $query->whereMonth('purchases.created_at', $request->month);
    //         }

    //         if ($request->has('year') && ! empty($request->year)) {
    //             $query->whereYear('purchases.created_at', $request->year);
    //         }
    //     }

    //     $purchases = $query
    //         ->groupBy(
    //             'purchase_invoice.id',
    //             'users.name',
    //             'purchase_invoice.invoice_number',
    //             'purchase_invoice.grand_total',
    //             'purchase_invoice.remaining_amount',
    //             'purchases.purchase_status',
    //             'purchases.payment_status',
    //             'purchase_invoice.created_at'
    //         )
    //         ->orderBy('purchase_invoice.id', 'desc')
    //         ->get();

    //     foreach ($purchases as $purchase) {

    //         $grandTotal  = (float) $purchase->grand_total;
    //         $totalPaid   = (float) $purchase->total_paid;
    //         $totalReturn = (float) ($purchase->total_return ?? 0);

    //         // ✅ Remaining Amount (accounts for returns)
    //         $purchase->remaining_amount = max(0, $grandTotal - $totalPaid - $totalReturn);

    //         // ✅ Extra Paid
    //         $purchase->extra_paid = max(0, $totalPaid + $totalReturn - $grandTotal);
    //     }
    //     // After fetching $purchases (as a collection or array)
    //     // foreach ($purchases as $purchase) {
    //     //     $payment = \App\Models\PaymentStore::where('purchase_id', $purchase->id)
    //     //         ->orderBy('id', 'desc')
    //     //         ->first();
    //     //     $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
    //     // }

    //     // ✅ OPTIMIZED: Cache currency settings
    //     $settings = cache()->remember("settings_branch_{$branch_id}", 300, function () use ($branch_id) {
    //         return DB::table('settings')->where('branch_id', $branch_id)->first();
    //     });
    //     $currencySymbolRaw = $settings->currency_symbol ?? '₹';
    //     $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    //     $currencyPosition  = $settings->currency_position ?? 'left';

    //     return response()->json([
    //         'success'           => true,
    //         'currency_symbol'   => $currencySymbol,
    //         'currency_position' => $currencyPosition,
    //         'data'              => $purchases,
    //     ]);
    // }
    public function purchase_list(Request $request)
    {
        $user = Auth::guard('api')->user();
        $branch_id = $user->id ?? null;

        if ($user->role === 'staff' && $user->id) {
            $branch_id = $user->id;
        } elseif ($user->role === 'sub-admin') {
            $branch_id = $user->id;
        } elseif ($user->role === 'admin' && !empty($request->selectedSubAdminId)) {
            $branch_id = (int) $request->selectedSubAdminId;
        }

        // Pagination parameters
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');


        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $branch_id);
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("(SELECT COALESCE(SUM(payment_store.payment_amount),0)
                FROM payment_store
                WHERE payment_store.purchase_id = purchase_invoice.id
                AND payment_store.isDeleted = 0) as total_paid"),
                DB::raw("(SELECT COALESCE(SUM(purchase_returns.total_amount), 0)
                FROM purchase_returns
                WHERE purchase_returns.purchase_id = purchase_invoice.id
                AND purchase_returns.isDeleted = 0) as total_return")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);

        // Apply search filter on invoice number or vendor name

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {

                $q->where('purchase_invoice.invoice_number', 'LIKE', "%{$search}%")
                    ->orWhere('users.name', 'LIKE', "%{$search}%")
                    ->orWhere('purchases.purchase_status', 'LIKE', "%{$search}%")
                    ->orWhere('purchases.payment_status', 'LIKE', "%{$search}%")
                    ->orWhere('purchase_invoice.grand_total', 'LIKE', "%{$search}%")

                    // Search by DATE
                    ->orWhereDate('purchase_invoice.created_at', $search)

                    // Search formatted date text
                    ->orWhereRaw("DATE_FORMAT(purchase_invoice.created_at,'%d-%b-%Y') LIKE ?", ["%{$search}%"])

                    // Search Return Status
                    ->orWhereRaw("
                (
                    SELECT COALESCE(SUM(purchase_returns.total_amount),0)
                    FROM purchase_returns
                    WHERE purchase_returns.purchase_id = purchase_invoice.id
                    AND purchase_returns.isDeleted = 0
                ) > 0
                AND ? LIKE '%return%'
          ", [$search])

                    // Search Extra Paid keyword
                    ->orWhereRaw("
                (
                    SELECT COALESCE(SUM(payment_store.payment_amount),0)
                    FROM payment_store
                    WHERE payment_store.purchase_id = purchase_invoice.id
                    AND payment_store.isDeleted = 0
                ) > purchase_invoice.grand_total
                AND ? LIKE '%extra%'
          ", [$search]);
            });
        }

        // Apply date filters

        // ✅ CORRECT - use purchase_invoice.created_at (matches displayed date)
        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('purchase_invoice.created_at', $request->date);
        } else {
            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('purchase_invoice.created_at', $request->month);
            }
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('purchase_invoice.created_at', $request->year);
            }
        }

        // Get total count for pagination
        $totalQuery = clone $query;
        $totalCount = $totalQuery->count(DB::raw('DISTINCT purchase_invoice.id'));

        // Apply pagination
        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        foreach ($purchases as $purchase) {
            $grandTotal  = (float) $purchase->grand_total;
            $totalPaid   = (float) $purchase->total_paid;
            $totalReturn = (float) ($purchase->total_return ?? 0);

            $purchase->remaining_amount = max(0, $grandTotal - $totalPaid - $totalReturn);
            $purchase->extra_paid = max(0, $totalPaid + $totalReturn - $grandTotal);
        }

        // Cache currency settings
        $settings = cache()->remember("settings_branch_{$branch_id}", 300, function () use ($branch_id) {
            return DB::table('settings')->where('branch_id', $branch_id)->first();
        });
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        return response()->json([
            'success'           => true,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data'              => $purchases,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => ceil($totalCount / $perPage),
                'per_page' => (int)$perPage,
                'total' => $totalCount,
                'from' => $purchases->count() > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => $purchases->count() > 0 ? (($page - 1) * $perPage) + $purchases->count() : 0,
            ]
        ]);
    }

    public function showPurchase($id, Request $request)
    {
        $user = Auth::guard('api')->user();
        // $branch_id = $user->id;
        // if (! empty($request->selectedSubAdminId)) {
        //     $branch_id = $request->selectedSubAdminId;
        // }
        // 🧩 Determine branch_id based on role
        if ($user->role === 'staff') {
            // Staff can only access their own branch
            $branch_id = $user->branch_id;
        } elseif (! empty($request->selectedSubAdminId)) {
            // Admin viewing specific sub-admin’s branch
            $branch_id = $request->selectedSubAdminId;
        } else {
            // Default to user's branch (for admin or superadmin)
            $branch_id = $user->branch_id ?? $user->id;
        }

        try {
            $purchase = DB::table('purchases')
                ->join('products', 'purchases.item', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->join('users', 'purchases.vendor_id', '=', 'users.id')
                ->join('purchase_invoice', 'purchases.invoice_id', '=', 'purchase_invoice.id')
                ->where('purchases.invoice_id', $id)
                ->where('purchases.branch_id', '=', $branch_id)
                ->where('purchases.isDeleted', 0)
                ->select(
                    'purchases.vendor_id',
                    'purchases.payment_status',
                    'users.phone as vendor_phone',
                    'purchase_invoice.shipping',
                    'purchase_invoice.status',
                    'purchase_invoice.taxes', // ✅ Fetch taxes as JSON
                    'purchase_invoice.gst_option',
                    'purchase_invoice.grand_total',
                    DB::raw("GROUP_CONCAT(purchases.item ORDER BY purchases.id SEPARATOR ', ') as product_ids"),
                    DB::raw("GROUP_CONCAT(products.name ORDER BY purchases.id SEPARATOR ', ') as product_names"),
                    DB::raw("GROUP_CONCAT(purchases.price ORDER BY purchases.id SEPARATOR ', ') as product_prices"),
                    DB::raw("GROUP_CONCAT(purchases.quantity ORDER BY purchases.id SEPARATOR ', ') as product_quantities"),
                    DB::raw("GROUP_CONCAT(COALESCE(purchases.discount_percent, 0) ORDER BY purchases.id SEPARATOR ', ') as discount_percents"),
                    DB::raw("GROUP_CONCAT(COALESCE(purchases.discount_amount, 0) ORDER BY purchases.id SEPARATOR ', ') as discount_amounts"),
                    DB::raw("GROUP_CONCAT(products.category_id ORDER BY purchases.id SEPARATOR ', ') as category_ids"),
                    DB::raw("GROUP_CONCAT(categories.name ORDER BY purchases.id SEPARATOR ', ') as category_names"),
                    DB::raw("GROUP_CONCAT(products.images ORDER BY purchases.id SEPARATOR ', ') as product_images"),
                    DB::raw("GROUP_CONCAT(COALESCE(purchases.product_gst_details, '[]') ORDER BY purchases.id SEPARATOR '|||') as product_gst_details"),
                    DB::raw("SUM(purchases.price * purchases.quantity) as total_amount"),
                    // DB::raw("(SUM(purchases.price * purchases.quantity) + purchase_invoice.shipping) as grand_total")
                )
                ->groupBy(
                    'purchases.vendor_id',
                    'purchases.payment_status',
                    'users.phone',
                    'purchase_invoice.shipping',
                    'purchase_invoice.status',
                    'purchase_invoice.taxes', // ✅ Include taxes in grouping
                    'purchase_invoice.gst_option',
                    'purchase_invoice.grand_total',
                )
                ->first();

            $invoice = PurchaseInvoice::where('id', $id)
                ->where('branch_id', $branch_id)
                ->where('isDeleted', 0)
                ->first();

            $vendor = User::find($invoice->vendor_id);

            $productImages = explode(',', $purchase->product_images ?? '');
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

            // dd($productImageUrls);
            if (! $purchase) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase not found',
                ], 404);
            }

            // ✅ Decode taxes from JSON format
            $taxes = json_decode($purchase->taxes, true) ?? [];

            // ✅ OPTIMIZED: Cache settings
            $settings = cache()->remember("setting_branch_{$branch_id}", 300, function () use ($branch_id) {
                return DB::table('settings')->where('branch_id', $branch_id)->first();
            });

            return response()->json([
                'success'            => true,
                'data'               => $purchase,
                'taxes'              => $taxes, // ✅ Send taxes to the frontend
                'companyInfo'        => $settings,
                'invoice'            => $invoice,
                'vendor'             => $vendor,
                'product_image_urls' => array_values($productImageUrls),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function purchase_invoice_pdf_download($id)
    {
        $authUser   = Auth::guard('api')->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;

        // ✅ OPTIMIZED: Cache settings and eager load invoice with vendor
        $setting = cache()->remember("setting_branch_{$subAdminId}", 300, function () use ($subAdminId) {
            return Setting::where('branch_id', $subAdminId)->first();
        });

        $invoice = PurchaseInvoice::with('vendor')->find($id);

        if (! $invoice) {
            return response()->json([
                'status'  => false,
                'message' => 'Purchase invoice not found.',
            ], 404);
        }

        // ✅ OPTIMIZED: Use already loaded vendor relationship
        $vendor = $invoice->vendor ?? (object) [
            'name'    => 'Unknown Vendor',
            'email'   => '',
            'phone'   => '',
            'address' => '',
        ];

        // ✅ OPTIMIZED: Eager load purchase items with product
        $purchaseItems = Purchases::with('product')
            ->where('invoice_id', $invoice->id)
            ->get();
        $paymentStatus = $purchaseItems->first()->payment_status ?? 'Pending';
        $paymentMethod = $purchaseItems->first()->payment_method ?? 'N/A';

        // Subtotal
        $subtotal = $purchaseItems->sum(fn($item) => $item->price * $item->quantity);

        // Discount
        $discountPercent = $invoice->discount ?? 0;
        $discountAmount  = ($discountPercent / 100) * $subtotal;
        $afterDiscount   = $subtotal - $discountAmount;

        // Shipping
        $shipping = $invoice->shipping ?? 0;

        // Taxes
        $taxRates   = json_decode($invoice->taxes, true) ?? [];
        $taxDetails = [];
        foreach ($taxRates as $tax) {
            $taxAmount    = ($tax['rate'] / 100) * $afterDiscount;
            $taxDetails[] = [
                'name'   => $tax['name'],
                'rate'   => $tax['rate'],
                'amount' => $taxAmount,
            ];
        }

        // Grand Total
        $grandTotal = $afterDiscount + $shipping + collect($taxDetails)->sum('amount');

        // Format currency
        $formatCurrency = fn($amt) => $setting->currency_position === 'right'
            ? number_format($amt, 2) . $setting->currency_symbol
            : $setting->currency_symbol . number_format($amt, 2);

        // Prepare data for PDF
        $pdfData = [
            'invoice'        => $invoice,
            'vendor'         => $vendor,
            'purchaseItems'  => $purchaseItems,
            'setting'        => $setting,
            'subtotal'       => $formatCurrency($subtotal),
            'discount'       => $invoice->discount,
            'discountAmount' => $formatCurrency($discountAmount),
            'afterDiscount'  => $formatCurrency($afterDiscount),
            'shipping'       => $formatCurrency($shipping),
            'taxDetails'     => $taxDetails,
            'grandTotal'     => $formatCurrency($grandTotal),
            'payment_status' => ucfirst($paymentStatus),
            'payment_method' => ucfirst($paymentMethod),
        ];

        // Generate PDF
        $pdf = PDF::loadView('purchase.purchase-invoice-pdf', $pdfData);

        // Save PDF to storage
        $fileName = 'purchase_invoice_' . $id . '.pdf';
        // $filePath = 'public/storage/purchase-invoices/' . $fileName;

        // if (! file_exists(storage_path('app/public/purchase-invoices'))) {
        //     mkdir(storage_path('app/public/purchase-invoices'), 0777, true);
        // }
        $relativePath = 'purchase-invoices/' . $fileName;

        Storage::disk('public')->put($relativePath, $pdf->output());

        // Generate public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // Return JSON response
        return response()->json([
            'status'    => true,
            'message'   => 'Purchase Invoice PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    // public function purchase_update(Request $request, $invoice_id)
    // {
    //     $user         = Auth::guard('api')->user();
    //     $userId       = $user->id;
    //     $userBranchId = $user->branch_id;
    //     $role         = $user->role;

    //     if ($role === 'staff' && $user->branch_id) {
    //         $userBranchId = $user->branch_id;
    //     } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
    //         $userBranchId = $request->selectedSubAdminId;
    //     } else {
    //         $userBranchId = $user->id;
    //     }
    //     $validator = Validator::make($request->all(), [
    //         'vendor_id'              => 'required',
    //         'status'                 => 'required',
    //         'payment_status'         => 'nullable',
    //         'vendor_phone'           => 'nullable|numeric',
    //         'discount'               => 'nullable|numeric',
    //         'shipping'               => 'required|numeric',
    //         'grand_total'            => 'required|numeric',
    //         'taxes'                  => 'nullable|array',
    //         'taxes.*.id'             => 'nullable',
    //         'taxes.*.rate'           => 'nullable|numeric|min:0',
    //         'products'               => 'required|array|min:1',
    //         'products.*.id'          => 'required',
    //         'products.*.category_id' => 'required',
    //         'products.*.price'       => 'required|numeric|min:0',
    //         'products.*.quantity'    => 'required|numeric|min:1',
    //         'products.*.total'       => 'required|numeric|min:0',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed',
    //             'errors'  => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         DB::beginTransaction();

    //         $purchaseInvoice = PurchaseInvoice::where('id', $invoice_id)->first();
    //         if (! $purchaseInvoice) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Purchase invoice not found',
    //             ], 404);
    //         }

    //         $vendor_id          = $request->vendor_id;
    //         $oldPurchases       = Purchases::where('invoice_id', $invoice_id)->get();
    //         $oldQuantities      = $oldPurchases->pluck('quantity', 'item')->toArray();
    //         $existingProductIds = $oldPurchases->pluck('item')->toArray();

    //         $totalAmount = collect($request->products)->sum('total');
    //         $discount    = $request->discount ?? 0;
    //         $shipping    = $request->shipping;

    //         // Calculate total tax for temporary grand total
    //         $tempTotalTax = 0;
    //         if ($request->gst_option === 'with_gst' || $request->gst_option === 'with') {
    //             if ($request->has('taxes') && is_array($request->taxes)) {
    //                 foreach ($request->taxes as $tax) {
    //                     $tempTotalTax += ($totalAmount * ($tax['rate'] ?? 0)) / 100;
    //                 }
    //             }
    //         }
    //         $tempGrandTotal = $totalAmount - $discount + $shipping + $tempTotalTax;
    //         $alreadyPaid    = $purchaseInvoice->paid ?? 0;

    //         // Determine payment status
    //         if ($alreadyPaid <= 0) {
    //             $paymentStatus = 'pending';
    //         } elseif ($alreadyPaid >= $tempGrandTotal) {
    //             $paymentStatus = 'completed';
    //         } else {
    //             $paymentStatus = 'partially';
    //         }

    //         $processedProducts   = [];
    //         $processedProductIds = [];
    //         foreach ($request->products as $product) {
    //             // Handle Category
    //             if (! is_numeric($product['category_id'])) {
    //                 $category    = Category::create(['name' => $product['category_id']]);
    //                 $category_id = $category->id;
    //             } else {
    //                 $category_id = $product['category_id'];
    //             }

    //             $gst_details = [];
    //             $gst_total   = 0;

    //             // Handle Product
    //             if (! is_numeric($product['id'])) {
    //                 do {
    //                     $sku = mt_rand(10000000, 99999999);
    //                 } while (Product::where('SKU', $sku)->exists());

    //                 $newProduct = Product::create([
    //                     'name'          => $product['id'],
    //                     'category_id'   => $category_id,
    //                     'price'         => $product['price'],
    //                     'quantity'      => $product['quantity'],
    //                     'vendor_id'     => $vendor_id,
    //                     'availablility' => 'in_stock',
    //                     'status'        => 'active',
    //                     'SKU'           => $sku,
    //                 ]);

    //                 $product_id = $newProduct->id;
    //             } else {
    //                 $existingProduct = Product::find($product['id']);
    //                 if ($existingProduct) {
    //                     $oldQuantity    = $oldQuantities[$product['id']] ?? 0;
    //                     $newQuantity    = $product['quantity'];
    //                     $quantityChange = $newQuantity - $oldQuantity;

    //                     if ($quantityChange > 0) {
    //                         $existingProduct->increment('quantity', $quantityChange);
    //                     } elseif ($quantityChange < 0) {
    //                         $existingProduct->decrement('quantity', abs($quantityChange));
    //                     }

    //                     $existingProduct->update([
    //                         'price'       => $product['price'],
    //                         'category_id' => $category_id,
    //                     ]);

    //                     $product_id = $existingProduct->id;

    //                     // Calculate product-wise GST
    //                     if ($request->gst_option === 'with_gst' || $request->gst_option === 'with') {
    //                         if ($existingProduct->gst_option === 'with_gst' && $existingProduct->product_gst) {
    //                             $taxes = json_decode($existingProduct->product_gst, true);
    //                             if (is_array($taxes)) {
    //                                 foreach ($taxes as $tax) {
    //                                     $taxRate   = floatval($tax['tax_rate'] ?? 0);
    //                                     $taxAmount = ($product['total'] * $taxRate) / 100;
    //                                     $gst_total += $taxAmount;
    //                                     $gst_details[] = [
    //                                         'name'   => $tax['tax_name'] ?? '',
    //                                         'rate'   => $taxRate,
    //                                         'amount' => $taxAmount,
    //                                     ];
    //                                 }
    //                             }
    //                         }
    //                     }

    //                     $lastInventory = ProductInventory::where('product_id', $product_id)
    //                         ->orderBy('id', 'desc')
    //                         ->first();

    //                     ProductInventory::create([
    //                         'product_id'    => $product_id,
    //                         'initial_stock' => $lastInventory->current_stock ?? 0,
    //                         'current_stock' => ($lastInventory->current_stock ?? 0) + $quantityChange,
    //                         'branch_id'     => $purchaseInvoice->branch_id,
    //                         'create_by'     => auth()->id(),
    //                         'type'          => 'Purchase Update',
    //                         'date'          => now(),
    //                     ]);
    //                 }
    //             }

    //             Purchases::updateOrCreate(
    //                 ['invoice_id' => $invoice_id, 'item' => $product_id],
    //                 [
    //                     'quantity'            => $product['quantity'],
    //                     'price'               => $product['price'],
    //                     'amount_total'        => $product['total'],
    //                     'product_gst_details' => $gst_details,
    //                     'product_gst_total'   => $gst_total,
    //                     'vendor_id'           => $vendor_id,
    //                     'purchase_status'     => $request->status,
    //                     'payment_status'      => $paymentStatus,
    //                     'branch_id'           => $userBranchId ?? $userId,
    //                 ]
    //             );

    //             // dd($processedProducts);
    //             $processedProducts[] = [
    //                 'product_id' => $product_id,
    //                 'price'      => $product['price'],
    //                 'quantity'   => $product['quantity'],
    //                 'total'      => $product['total'],
    //             ];
    //             $processedProductIds[] = $product_id;

    //         }

    //         // Delete removed products and revert inventory
    //         $idsToDelete = array_diff($existingProductIds, $processedProductIds);
    //         foreach ($idsToDelete as $deleteId) {
    //             $purchaseToDelete = Purchases::where('invoice_id', $invoice_id)->where('item', $deleteId)->first();
    //             if ($purchaseToDelete) {
    //                 $existingProduct = Product::find($deleteId);
    //                 if ($existingProduct) {
    //                     $existingProduct->decrement('quantity', $purchaseToDelete->quantity);

    //                     $lastInventory = ProductInventory::where('product_id', $deleteId)
    //                         ->orderBy('id', 'desc')
    //                         ->first();

    //                     ProductInventory::create([
    //                         'product_id'    => $deleteId,
    //                         'initial_stock' => $lastInventory->current_stock ?? 0,
    //                         'current_stock' => ($lastInventory->current_stock ?? 0) - $purchaseToDelete->quantity,
    //                         'branch_id'     => $purchaseInvoice->branch_id,
    //                         'create_by'     => auth()->id(),
    //                         'type'          => 'Purchase Removed',
    //                         'date'          => now(),
    //                     ]);
    //                 }
    //                 $purchaseToDelete->delete();
    //             }
    //         }

    //         // Handle Taxes (safely if nullable)
    //         $totalTaxAmount = 0;
    //         $taxData        = [];
    //         if ($request->gst_option === 'with_gst' || $request->gst_option === 'with') {
    //             if ($request->has('taxes') && is_array($request->taxes)) {
    //                 foreach ($request->taxes as $tax) {
    //                     $rate      = $tax['rate'] ?? 0;
    //                     $taxAmount = (collect($processedProducts)->sum('total') * $rate) / 100;
    //                     $totalTaxAmount += $taxAmount;
    //                     $taxData[] = [
    //                         'id'     => $tax['id'] ?? null, // keep ID
    //                         'name'   => $tax['name'] ?? '', // ✅ add tax name
    //                         'rate'   => $rate,
    //                         'amount' => $taxAmount,
    //                     ];
    //                 }
    //             }
    //         } else {
    //             $taxData        = [];
    //             $totalTaxAmount = 0;
    //         }
    //         // // dd($request->all());
    //         // $totalAmount = collect($processedProducts)->sum('total');
    //         // // $discount    = $request->discount ?? 0;
    //         // $shipping    = $request->shipping;
    //         // $grandTotal  = $totalAmount - $discount + $shipping + $totalTaxAmount;

    //         // // keep already paid value
    //         // $alreadyPaid = $purchaseInvoice->paid ?? 0;

    //         // // recalc remaining
    //         // $remaining = max($grandTotal - $alreadyPaid, 0);
    //         $totalAmount = collect($processedProducts)->sum('total');
    //         $shipping    = $request->shipping;

    //         $totalPaid = PaymentStore::where('purchase_id', $invoice_id)
    //             ->where('isDeleted', 0)
    //             ->sum('payment_amount');

    //         $grandTotal = $totalAmount + $shipping;
    //         $remaining  = max(0, $grandTotal - $totalPaid);

    //         if ($remaining <= 0 && $totalPaid > 0) {
    //             $purchaseStatus = 'completed';
    //             $paymentStatus  = 'completed';
    //         } elseif ($totalPaid > 0) {
    //             $purchaseStatus = 'pending';
    //             $paymentStatus  = 'partially';
    //         } else {
    //             $purchaseStatus = 'pending';
    //             $paymentStatus  = 'pending';
    //         }

    //         $purchaseInvoice->update([
    //             'products'         => json_encode($processedProducts),
    //             'total_amount'     => $totalAmount,
    //             'discount'         => $discount,
    //             'shipping'         => $shipping,
    //             'taxes'            => json_encode($taxData),
    //             'gst_option'       => ($request->gst_option === 'with_gst' || $request->gst_option === 'with') ? 'with_gst' : 'without_gst',
    //             'grand_total'      => $grandTotal,
    //             'remaining_amount' => $remaining,
    //             'status'           => $purchaseStatus,
    //         ]);
    //         // dd($purchaseInvoice);

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Purchase order updated successfully!',
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function purchase_update(Request $request, $invoice_id)
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
            'vendor_id'              => 'required',
            // 'status' => 'required',
            'bill_no'                => 'required',
            'shipping'               => 'required|numeric',
            'grand_total'            => 'required|numeric',
            'gst_option'             => 'required',
            'discount'               => 'nullable|numeric',
            'taxes'                  => 'nullable|array',
            'products'               => 'required|array|min:1',
            'products.*.id'          => 'required',
            'products.*.category_id' => 'required',
            'products.*.price'       => 'required|numeric|min:0',
            'products.*.quantity'    => 'required|numeric|min:1',
            'products.*.total'       => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $purchaseInvoice = PurchaseInvoice::find($invoice_id);
            if (! $purchaseInvoice) {
                return response()->json(['success' => false, 'message' => 'Invoice not found'], 404);
            }

            /* ===============================
           1️⃣ GET OLD PURCHASE DATA
        =============================== */
            $oldPurchases   = Purchases::where('invoice_id', $invoice_id)->get();
            $oldPurchaseMap = $oldPurchases->keyBy('item');

            $newProductIds     = [];
            $processedProducts = [];

            // ✅ Bulk load products for GST calculations
            $productIds = array_filter(array_column($request->products, 'id'), 'is_numeric');
            $existingProducts = ! empty($productIds)
                ? Product::whereIn('id', $productIds)->get()->keyBy('id')
                : collect();

            /* ===============================
           2️⃣ LOOP NEW PRODUCTS
        =============================== */
            foreach ($request->products as $product) {

                // Category
                $category_id = is_numeric($product['category_id'])
                    ? $product['category_id']
                    : Category::create(['name' => $product['category_id']])->id;

                // Product
                if (! is_numeric($product['id'])) {
                    $sku        = mt_rand(10000000, 99999999);
                    $newProduct = Product::create([
                        'name'        => $product['id'],
                        'category_id' => $category_id,
                        'price'       => $product['price'],
                        'quantity'    => $product['quantity'],
                        'vendor_id'   => $request->vendor_id,
                        'status'      => 'active',
                        'SKU'         => $sku,
                    ]);
                    $product_id = $newProduct->id;
                    $oldQty     = 0;
                } else {
                    $product_id = $product['id'];
                    $oldQty     = $oldPurchaseMap[$product_id]->quantity ?? 0;

                    $productModel = Product::find($product_id);
                    $qtyDiff      = $product['quantity'] - $oldQty;

                    if ($qtyDiff > 0) {
                        $productModel->increment('quantity', $qtyDiff);
                    } elseif ($qtyDiff < 0) {
                        $productModel->decrement('quantity', abs($qtyDiff));
                    }

                    ProductInventory::create([
                        'product_id'    => $product_id,
                        'initial_stock' => $productModel->quantity - $qtyDiff,
                        'current_stock' => $productModel->quantity,
                        'branch_id'     => $purchaseInvoice->branch_id,
                        'create_by'     => auth()->id(),
                        'type'          => 'Purchase Update',
                        'date'          => now(),
                    ]);
                }

                $newProductIds[] = $product_id;

                // Product-wise GST calculation
                $gst_details = [];
                $gst_total   = 0;
                $baseTotal   = $product['price'] * $product['quantity'];

                if ($request->gst_option === 'with') {
                    $prod = $existingProducts->get($product_id);
                    if ($prod && $prod->gst_option === 'with_gst' && $prod->product_gst) {
                        $taxes = json_decode($prod->product_gst, true);
                        if (is_array($taxes)) {
                            foreach ($taxes as $tax) {
                                $taxRate   = floatval($tax['tax_rate'] ?? 0);
                                $taxAmount = ($baseTotal * $taxRate) / 100;
                                $gst_total += $taxAmount;
                                $gst_details[] = [
                                    'name'   => $tax['tax_name'] ?? '',
                                    'rate'   => $taxRate,
                                    'amount' => $taxAmount,
                                ];
                            }
                        }
                    }
                }

                Purchases::updateOrCreate(
                    ['invoice_id' => $invoice_id, 'item' => $product_id],
                    [
                        'quantity'            => $product['quantity'],
                        'price'               => $product['price'],
                        'discount_percent'    => $product['discount_percent'] ?? 0,
                        'discount_amount'     => $product['discount_amount'] ?? 0,
                        'amount_total'        => $product['total'],
                        'product_gst_details' => $gst_details,
                        'product_gst_total'   => $gst_total,
                        'vendor_id'           => $request->vendor_id,
                        'branch_id'           => $userBranchId,
                        'created_by'          => $userId,
                    ]
                );

                $processedProducts[] = [
                    'product_id'       => $product_id,
                    'price'            => $product['price'],
                    'quantity'         => $product['quantity'],
                    'discount_percent' => $product['discount_percent'] ?? 0,
                    'discount_amount'  => $product['discount_amount'] ?? 0,
                    'total'            => $product['total'],
                ];
            }

            /* ===============================
           3️⃣ DELETE REMOVED PRODUCTS
        =============================== */
            $deletedProducts = $oldPurchases->whereNotIn('item', $newProductIds);

            foreach ($deletedProducts as $deleted) {
                $product = Product::find($deleted->item);
                if ($product) {
                    $product->decrement('quantity', $deleted->quantity);

                    ProductInventory::create([
                        'product_id'    => $product->id,
                        'initial_stock' => $product->quantity + $deleted->quantity,
                        'current_stock' => $product->quantity,
                        'branch_id'     => $purchaseInvoice->branch_id,
                        'create_by'     => auth()->id(),
                        'type'          => 'Purchase Item Removed',
                        'date'          => now(),
                    ]);
                }
            }

            Purchases::where('invoice_id', $invoice_id)
                ->whereNotIn('item', $newProductIds)
                ->delete();

            /* ===============================
           4️⃣ TOTAL / PAYMENT LOGIC
        =============================== */
            $totalAmount = collect($processedProducts)->sum('total');
            $shipping    = $request->shipping;

            $totalPaid = PaymentStore::where('purchase_id', $invoice_id)
                ->where('isDeleted', 0)
                ->sum('payment_amount');

            $grandTotal = $request->grand_total;
            $remaining  = max(0, $grandTotal - $totalPaid);

            if ($remaining <= 0 && $totalPaid > 0) {
                $purchaseStatus = 'completed';
                $paymentStatus  = 'completed';
            } elseif ($totalPaid > 0) {
                $purchaseStatus = 'partially';
                $paymentStatus  = 'partially';
            } else {
                $purchaseStatus = 'pending';
                $paymentStatus  = 'pending';
            }

            /* ===============================
           5️⃣ UPDATE INVOICE & ITEMS
        =============================== */
            $purchaseInvoice->update([
                'products'         => json_encode($processedProducts),
                'total_amount'     => $totalAmount,
                'discount'         => $request->discount ?? 0,
                'shipping'         => $shipping,
                'grand_total'      => $grandTotal,
                'remaining_amount' => $remaining,
                'gst_option'       => $request->gst_option === 'with' ? 'with_gst' : 'without_gst',
                'taxes'            => json_encode($request->taxes),
                'status'           => $purchaseStatus,
                'bill_no'          => $request->bill_no
            ]);

            Purchases::where('invoice_id', $invoice_id)->update([
                'purchase_status' => $purchaseStatus,
                'payment_status'  => $paymentStatus,
                'updated_at'      => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    // public function fetch_purchase_report(Request $request)
    // {
    //     $user         = Auth::guard('api')->user();
    //     $role         = $user->role;
    //     $userBranchId = $user->branch_id;
    //     $userId       = $user->id;
    //     if ($role === 'staff' && $userBranchId) {
    //         $branchIdToUse = $userBranchId;
    //     } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
    //         $branchIdToUse = $request->selectedSubAdminId;
    //     } else {
    //         $branchIdToUse = $user->id;
    //     }

    //     $query = Purchases::with(['vendor', 'product'])
    //         ->where('purchase_status', 'completed')
    //         ->where('branch_id', $branchIdToUse)
    //         ->where('isDeleted', 0);

    //     // 🔹 If staff, filter using created_by (from PurchaseInvoice)
    //     if ($role === 'staff') {
    //         $query->whereHas('invoice', function ($q) use ($userId) {
    //             $q->where('created_by', $userId);
    //         });
    //     } else {
    //         $query->where('branch_id', $branchIdToUse);
    //     }

    //     // Month / Year filter
    //     $monthParam = $request->month;
    //     $yearParam  = $request->year;

    //     if (! empty($monthParam)) {
    //         $query->whereMonth('created_at', (int) $monthParam);
    //     }
    //     if (! empty($yearParam)) {
    //         $query->whereYear('created_at', (int) $yearParam);
    //     }

    //     // Date / Preset filter (only if month/year not provided)
    //     if (empty($monthParam) && empty($yearParam)) {
    //         $now = Carbon::now();
    //         switch ($request->filter) {
    //             case 'this_week':
    //                 $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
    //                 break;
    //             case 'this_month':
    //                 $query->whereMonth('created_at', $now->month)
    //                     ->whereYear('created_at', $now->year);
    //                 break;
    //             case 'last_6_months':
    //                 $query->whereBetween('created_at', [$now->copy()->subMonths(6)->startOfMonth(), $now->endOfMonth()]);
    //                 break;
    //             case 'this_year':
    //                 $query->whereYear('created_at', $now->year);
    //                 break;
    //             case 'previous_year':
    //                 $query->whereYear('created_at', $now->year - 1);
    //                 break;
    //             default:
    //                 if ($request->from_date && $request->to_date) {
    //                     $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    //                 }
    //                 break;
    //         }
    //     }

    //     // Optional vendor filter
    //     if ($request->filled('vendor_id')) {
    //         $vendorId = (int) $request->vendor_id;
    //         $query->where('vendor_id', $vendorId);
    //     }

    //     if ($request->filled('category_id')) {
    //         $categoryId = (int) $request->category_id;
    //         $query->whereHas('product', function ($q) use ($categoryId) {
    //             $q->where('category_id', $categoryId);
    //         });
    //     }

    //     $purchases = $query->latest()->get();

    //     // ✅ OPTIMIZED: Load all invoices in one query instead of N+1
    //     $invoiceIds = $purchases->pluck('invoice_id')->filter()->unique()->toArray();
    //     $invoices   = ! empty($invoiceIds)
    //         ? PurchaseInvoice::whereIn('id', $invoiceIds)
    //         ->get()
    //         ->keyBy('id')
    //         : collect();

    //     // ✅ OPTIMIZED: Process purchases with pre-loaded invoices
    //     $processedPurchases = $purchases->map(function ($purchase) use ($invoices) {
    //         $cgstAmount = 0;
    //         $sgstAmount = 0;
    //         $discount   = 0;
    //         $shipping   = 0;
    //         $grandTotal = 0;

    //         if (! empty($purchase->invoice_id)) {
    //             $invoiceId = is_numeric($purchase->invoice_id) ? (int) $purchase->invoice_id : $purchase->invoice_id;
    //             $invoice   = $invoices->get($invoiceId);

    //             if ($invoice) {
    //                 $discount   = $invoice->discount ?? 0;
    //                 $shipping   = $invoice->shipping ?? 0;
    //                 $grandTotal = $invoice->grand_total ?? 0;

    //                 if ($invoice->taxes) {
    //                     $taxes = json_decode($invoice->taxes, true);
    //                     if (is_array($taxes)) {
    //                         foreach ($taxes as $tax) {
    //                             if (isset($tax['name'], $tax['amount'])) {
    //                                 if (strtoupper($tax['name']) === 'CGST') {
    //                                     $cgstAmount = $tax['amount'];
    //                                 } elseif (strtoupper($tax['name']) === 'SGST') {
    //                                     $sgstAmount = $tax['amount'];
    //                                 }
    //                             }
    //                         }
    //                     }
    //                 }
    //             }
    //         }

    //         $purchase->cgst_amount = $cgstAmount;
    //         $purchase->sgst_amount = $sgstAmount;
    //         $purchase->discount    = $discount;
    //         $purchase->shipping    = $shipping;
    //         $purchase->grand_total = $grandTotal;

    //         return $purchase;
    //     });

    //     // ✅ OPTIMIZED: Cache currency settings
    //     $settings = cache()->remember("settings_branch_{$branchIdToUse}", 300, function () use ($branchIdToUse) {
    //         return DB::table('settings')->where('branch_id', $branchIdToUse)->first();
    //     });
    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     return response()->json([
    //         'status'           => true,
    //         'data'             => $processedPurchases,
    //         'currencySymbol'   => $currencySymbol,
    //         'currencyPosition' => $currencyPosition,
    //     ]);
    // }
    public function fetch_purchase_report(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->branch_id;
        $userId = $user->id;

        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branchIdToUse = $request->selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // Pagination parameters
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search', '');

        $query = Purchases::with(['vendor', 'product'])
            ->where('purchase_status', 'completed')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        // If staff, filter using created_by
        if ($role === 'staff') {
            $query->whereHas('invoice', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                })->orWhereHas('vendor', function ($subQ) use ($search) {
                    $subQ->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Month / Year filter
        $monthParam = $request->month;
        $yearParam  = $request->year;

        if (! empty($monthParam)) {
            $query->whereMonth('created_at', (int) $monthParam);
        }
        if (! empty($yearParam)) {
            $query->whereYear('created_at', (int) $yearParam);
        }

        // Date / Preset filter (only if month/year not provided)
        if (empty($monthParam) && empty($yearParam)) {
            $now = Carbon::now();
            switch ($request->filter) {
                case 'this_week':
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                        ->whereYear('created_at', $now->year);
                    break;
                case 'last_6_months':
                    $query->whereBetween('created_at', [$now->copy()->subMonths(6)->startOfMonth(), $now->endOfMonth()]);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
                case 'previous_year':
                    $query->whereYear('created_at', $now->year - 1);
                    break;
                default:
                    if ($request->from_date && $request->to_date) {
                        $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
                    }
                    break;
            }
        }

        // Optional vendor filter
        if ($request->filled('vendor_id')) {
            $vendorId = (int) $request->vendor_id;
            $query->where('vendor_id', $vendorId);
        }

        // Optional category filter
        if ($request->filled('category_id')) {
            $categoryId = (int) $request->category_id;
            $query->whereHas('product', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        // Get total count for pagination
        $totalCount = $query->count();

        // Get paginated results
        $purchases = $query->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Load invoices in one query
        $invoiceIds = $purchases->pluck('invoice_id')->filter()->unique()->toArray();
        $invoices = ! empty($invoiceIds)
            ? PurchaseInvoice::whereIn('id', $invoiceIds)->get()->keyBy('id')
            : collect();

        // Process purchases with pre-loaded invoices
        $processedPurchases = $purchases->map(function ($purchase) use ($invoices) {
            $cgstAmount = 0;
            $sgstAmount = 0;
            $discount = 0;
            $shipping = 0;
            $grandTotal = 0;

            if (! empty($purchase->invoice_id)) {
                $invoiceId = is_numeric($purchase->invoice_id) ? (int) $purchase->invoice_id : $purchase->invoice_id;
                $invoice = $invoices->get($invoiceId);

                if ($invoice) {
                    $discount = $invoice->discount ?? 0;
                    $shipping = $invoice->shipping ?? 0;
                    $grandTotal = $invoice->grand_total ?? 0;

                    if ($invoice->taxes) {
                        $taxes = json_decode($invoice->taxes, true);
                        if (is_array($taxes)) {
                            foreach ($taxes as $tax) {
                                if (isset($tax['name'], $tax['amount'])) {
                                    if (strtoupper($tax['name']) === 'CGST') {
                                        $cgstAmount = $tax['amount'];
                                    } elseif (strtoupper($tax['name']) === 'SGST') {
                                        $sgstAmount = $tax['amount'];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $purchase->cgst_amount = $cgstAmount;
            $purchase->sgst_amount = $sgstAmount;
            $purchase->discount = $discount;
            $purchase->shipping = $shipping;
            $purchase->grand_total = $grandTotal;

            return $purchase;
        });

        // Cache currency settings
        $settings = cache()->remember("settings_branch_{$branchIdToUse}", 300, function () use ($branchIdToUse) {
            return DB::table('settings')->where('branch_id', $branchIdToUse)->first();
        });
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'status' => true,
            'data' => $processedPurchases,
            'currencySymbol' => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => ceil($totalCount / $perPage),
                'per_page' => (int)$perPage,
                'total' => $totalCount,
                'from' => $processedPurchases->count() > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => $processedPurchases->count() > 0 ? (($page - 1) * $perPage) + $processedPurchases->count() : 0,
            ]
        ]);
    }

    public function export_purchase_excel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'purchase_ids'   => 'required|array|min:1',
                'purchase_ids.*' => 'required|integer|exists:purchases,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $purchases = Purchases::with(['vendor', 'product'])
                ->whereIn('id', $request->purchase_ids)
                ->where('purchase_status', 'completed')
                ->where('isDeleted', 0)
                ->get();

            if ($purchases->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid purchases found for export',
                ], 404);
            }

            // ✅ OPTIMIZED: Load all invoices in one query instead of N+1
            $invoiceIds = $purchases->pluck('invoice_id')->filter()->unique()->toArray();
            $invoices   = ! empty($invoiceIds)
                ? PurchaseInvoice::whereIn('id', $invoiceIds)
                ->get()
                ->keyBy('id')
                : collect();

            // ✅ OPTIMIZED: Process purchases with pre-loaded invoices
            $processedPurchases = $purchases->map(function ($purchase) use ($invoices) {
                $cgstAmount = 0;
                $sgstAmount = 0;
                $discount   = 0;
                $shipping   = 0;
                $grandTotal = 0;

                if ($purchase->invoice_id && ! empty($purchase->invoice_id)) {
                    $invoiceId = is_numeric($purchase->invoice_id) ? (int) $purchase->invoice_id : $purchase->invoice_id;
                    $invoice   = $invoices->get($invoiceId);

                    if ($invoice) {
                        $discount   = $invoice->discount ?? 0;
                        $shipping   = $invoice->shipping ?? 0;
                        $grandTotal = $invoice->grand_total ?? 0;

                        if ($invoice->taxes) {
                            $taxes = json_decode($invoice->taxes, true);
                            if (is_array($taxes)) {
                                foreach ($taxes as $tax) {
                                    if (isset($tax['name']) && isset($tax['amount'])) {
                                        if (strtoupper($tax['name']) === 'CGST') {
                                            $cgstAmount = $tax['amount'];
                                        } elseif (strtoupper($tax['name']) === 'SGST') {
                                            $sgstAmount = $tax['amount'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                $purchase->cgst_amount = $cgstAmount;
                $purchase->sgst_amount = $sgstAmount;
                $purchase->discount    = $discount;
                $purchase->shipping    = $shipping;
                $purchase->grand_total = $grandTotal;

                return $purchase;
            });

            // ✅ OPTIMIZED: Cache currency settings
            $settings = cache()->remember("settings_default", 300, function () {
                return DB::table('settings')->first();
            });
            $currencySymbolRaw = $settings->currency_symbol ?? '₹';
            // Decode HTML entities and trim
            $currencySymbol = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            // Fix common mojibake cases (UTF-8 bytes read as Windows-1252)
            $currencySymbol = strtr($currencySymbol, [
                'â‚¬' => '€', // euro
                'Â£'  => '£',    // pound
                'â‚¹' => '₹', // rupee
                'Â¥'  => '¥',    // yen
            ]);
            // As a fallback, try interpreting original as Windows-1252 and converting to UTF-8
            if (! preg_match('/[€£₹¥$]/u', $currencySymbol)) {
                $maybeUtf8 = @mb_convert_encoding($currencySymbolRaw, 'UTF-8', 'Windows-1252');
                if ($maybeUtf8 && preg_match('/[€£₹¥$]/u', $maybeUtf8)) {
                    $currencySymbol = $maybeUtf8;
                }
            }
            $currencyPosition = $settings->currency_position ?? 'left';

            // Map common currency symbols to Unicode codepoints for robust Excel rendering via UNICHAR()
            $symbolToCodepoint = [
                '€' => 8364,
                '£' => 163,
                '₹' => 8377,
                '¥' => 165,
                '$' => 36,
            ];
            $currencyCodepoint = $symbolToCodepoint[$currencySymbol] ?? null;

            // Generate CSV file
            $filename = 'purchase_report_' . date('Y-m-d_H-i-s') . '.csv';
            $filepath = storage_path('app/public/exports/' . $filename);

            // Create exports directory if it doesn't exist
            if (! file_exists(storage_path('app/public/exports'))) {
                mkdir(storage_path('app/public/exports'), 0755, true);
            }

            // Create CSV file (UTF-16LE with BOM; include delimiter hint for Excel)
            $file = fopen($filepath, 'w');
            // Write UTF-16LE BOM and delimiter hint (converted)
            fwrite($file, "\xFF\xFE");
            fwrite($file, mb_convert_encoding("sep=,\r\n", 'UTF-16LE', 'UTF-8'));

            // Set headers
            $headers = [
                'Product Name',
                'Vendor Name',
                'Currency',
                'Purchased Amount',
                'Purchased QTY',
                'CGST',
                'SGST',
                'Discount',
                'Shipping',
                'Grand Total',
                'Purchase Date',
            ];

            // Write header row (converted)
            $escapedHeaders = array_map(function ($v) {
                $v = (string) $v;
                return '"' . str_replace('"', '""', $v) . '"';
            }, $headers);
            $headerLine = implode(',', $escapedHeaders) . "\r\n";
            fwrite($file, mb_convert_encoding($headerLine, 'UTF-16LE', 'UTF-8'));

            // Add data rows
            foreach ($processedPurchases as $purchase) {
                // Numeric values (no thousands separator) so Excel parses as numbers
                $amountFormatted     = number_format((float) $purchase->amount_total, 2, '.', '');
                $cgstFormatted       = number_format((float) $purchase->cgst_amount, 2, '.', '');
                $sgstFormatted       = number_format((float) $purchase->sgst_amount, 2, '.', '');
                $discountFormatted   = number_format((float) $purchase->discount, 2, '.', '');
                $shippingFormatted   = number_format((float) $purchase->shipping, 2, '.', '');
                $grandTotalFormatted = number_format((float) $purchase->grand_total, 2, '.', '');

                $row = [
                    $purchase->product ? $purchase->product->name : 'N/A',
                    $purchase->vendor ? $purchase->vendor->name : 'N/A',
                    $currencySymbol,
                    $amountFormatted,
                    (string) $purchase->quantity,
                    $cgstFormatted,
                    $sgstFormatted,
                    $discountFormatted,
                    $shippingFormatted,
                    $grandTotalFormatted,
                    $purchase->created_at ? $purchase->created_at->format('Y-m-d H:i:s') : '',
                ];
                // Escape and write row (converted)
                $escaped = array_map(function ($v) {
                    $v = (string) $v;
                    return '"' . str_replace('"', '""', $v) . '"';
                }, $row);
                $line = implode(',', $escaped) . "\r\n";
                fwrite($file, mb_convert_encoding($line, 'UTF-16LE', 'UTF-8'));
            }

            // Close CSV file
            fclose($file);

            // Generate download URL
            $downloadUrl = url('storage/exports/' . $filename);

            return response()->json([
                'success'      => true,
                'message'      => 'CSV file generated successfully',
                'filename'     => $filename,
                'download_url' => $downloadUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting purchase CSV: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSV file: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete purchase order
    public function purchase_delete(Request $request)
    {
        try {
            DB::beginTransaction(); // Start transaction

            // Find the purchase invoice
            $purchase = PurchaseInvoice::find($request->id);
            if (! $purchase) {
                return response()->json(['status' => false, 'message' => 'Purchase order not found.'], 404);
            }

            // Check if all related purchases have status 'completed'
            $nonCompletedCount = Purchases::where('invoice_id', $request->id)
                ->where('purchase_status', '!=', 'completed')
                ->where('isDeleted', 0)
                ->count();

            if ($nonCompletedCount > 0) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Cannot delete. This purchases are not completed.',
                ], 422);
            }

            // Soft delete purchases
            Purchases::where('invoice_id', $request->id)
                ->where('purchase_status', 'completed')
                ->update(['isDeleted' => 1]);

            // Soft delete the purchase invoice
            $purchase->isDeleted = 1;
            $purchase->save();

            DB::commit(); // Commit transaction

            return response()->json([
                'status'  => true,
                'message' => 'Purchase order deleted successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            return response()->json([
                'status'  => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $query = Purchases::with(['vendor', 'product']);

        // Filters
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        if ($request->vendor_id) {
            $query->where('vendor_id', $request->vendor_id);
        }

        $purchases = $query->latest()->get();
        $vendors   = \App\Models\User::where('role', 'vendor')->get();

        return view('purchase.purchasereport', compact('purchases', 'vendors'));
    }

    // public function getHistory1($job_card_id)
    // {
    //     $history = PaymentStore::where('purchase_id', $job_card_id)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => 'success',
    //         'data'   => $history,
    //     ]);
    // }
    public function getHistory1($job_card_id)
    {
        // All payments for this purchase
        $history = PaymentStore::where('purchase_id', $job_card_id)
            ->where('isDeleted', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        // Purchase invoice
        $purchase = PurchaseInvoice::findOrFail($job_card_id);

        $totalPaid  = $history->sum('payment_amount');
        $orderTotal = $purchase->grand_total ?? 0;

        $totalReturn = \App\Models\PurchaseReturn::where('purchase_id', $job_card_id)
            ->where('isDeleted', 0)
            ->sum('total_amount');

        // ✅ Dynamic calculations (accounts for returns)
        $extraPaid = max(0, $totalPaid + $totalReturn - $orderTotal);
        $remaining = max(0, $orderTotal - $totalPaid - $totalReturn);

        return response()->json([
            'status'  => 'success',
            'data'    => $history,
            'summary' => [
                'order_total' => $orderTotal,
                'total_paid'  => $totalPaid,
                'remaining'   => $remaining,
                'extra_paid'  => $extraPaid,
                'total_return' => $totalReturn,
            ],
        ]);
    }


    public function make_payment(Request $request)
    {
        $user_id = Auth::guard('api')->user()->id;

        $request->validate([
            'purchase_id'    => 'nullable|integer',
            'payment_amount' => 'nullable|numeric',
            'payment_date'   => 'nullable|date',
            'payment_type'   => 'nullable|string',
            'emi_month'      => 'nullable|integer',
            'pending_date'   => 'nullable|date',
            'new_emi_value'  => 'nullable',
            'emi_paid_value' => 'nullable|numeric',
        ]);

        if ($request->filled('purchase_id')) {
            // ✅ Find purchase (JobCard)
            $jobCard = PurchaseInvoice::find($request->purchase_id);
            if (! $jobCard) {
                return response()->json(['status' => 'error', 'message' => 'Purchase not found'], 404);
            }

            // ✅ Calculate current total paid from database
            $totalPaidSoFar = PaymentStore::where('purchase_id', $jobCard->id)
                ->where('isDeleted', 0)
                ->sum('payment_amount');

            $totalReturnSoFar = \App\Models\PurchaseReturn::where('purchase_id', $jobCard->id)
                ->where('isDeleted', 0)
                ->sum('total_amount');

            // ✅ Calculate total payment amount for this request
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

            $grandTotal   = $jobCard->grand_total ?? 0;
            $newTotalPaid = $totalPaidSoFar + $paymentAmount;
            $newRemaining = max(0, $grandTotal - $newTotalPaid - $totalReturnSoFar);

            // ✅ Determine type
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
            } elseif (in_array($request->emi_type, ['emi'])) {
                $type = 'emi';
            } else {
                $type = 'fully';
            }

            $payments = [];

            // ✅ Handle cash_online separate entries
            if (in_array($request->cash_online_type, ['cash_online_partially', 'cash_online_fully'])) {
                // 1️⃣ Cash entry
                $cashValue = $request->cash_online_type === 'cash_online_partially'
                    ? ($request->cash_amount ?? 0)
                    : ($request->fully_cash_amount ?? 0);

                if ($cashValue > 0) {
                    $payments[] = PaymentStore::create([
                        'user_id'          => $user_id,
                        'purchase_id'      => $jobCard->id,
                        'payment_amount'   => $cashValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method'   => 'cash',
                        'payment_date'     => now(),
                        'payment_type'     => $type,
                        'cash_amount'      => $cashValue,
                        'upi_amount'       => 0,
                        'status'           => 'debit',
                        'bank_id'          => $request->bank_id,
                        'emi_month'        => $request->emi_month ?? 1,
                        'isDeleted'        => 0,
                    ]);
                }

                // 2️⃣ Online entry
                $onlineValue = $request->cash_online_type === 'cash_online_partially'
                    ? ($request->online_amount ?? 0)
                    : ($request->full_online_amount ?? 0);

                if ($onlineValue > 0) {
                    $payments[] = PaymentStore::create([
                        'user_id'          => $user_id,
                        'purchase_id'      => $jobCard->id,
                        'payment_amount'   => $onlineValue,
                        'remaining_amount' => $newRemaining,
                        'payment_method'   => 'online',
                        'payment_date'     => now(),
                        'payment_type'     => $type,
                        'cash_amount'      => 0,
                        'upi_amount'       => $onlineValue,
                        'status'           => 'debit',
                        'bank_id'          => $request->bank_id,
                        'emi_month'        => $request->emi_month ?? 1,
                        'isDeleted'        => 0,
                    ]);
                }
            } else {
                // ✅ Default single payment record
                $payments[] = PaymentStore::create([
                    'user_id'          => $user_id,
                    'purchase_id'      => $jobCard->id,
                    'payment_amount'   => $paymentAmount,
                    'remaining_amount' => $newRemaining,
                    'payment_method'   => $request->payment_method ?? $request->payment_type ?? '',
                    'payment_date'     => now(),
                    'payment_type'     => $type,
                    'cash_amount'      => $request->cash_amount ?? 0,
                    'upi_amount'       => $request->online_amount ?? 0,
                    'status'           => 'debit',
                    'bank_id'          => $request->bank_id,
                    'emi_month'        => $request->emi_month ?? 1,
                    'isDeleted'        => 0,
                ]);
            }

            // ✅ Update JobCard remaining and paid columns
            $jobCard->update([
                'remaining_amount' => $newRemaining,
                'paid'             => $newTotalPaid,
            ]);

            // ✅ Determine and update status
            // $paymentStatus  = 'unpaid';
            // $purchaseStatus = 'pending';

            // if ($newRemaining <= 0) {
            //     $paymentStatus  = 'paid';
            //     $purchaseStatus = 'completed';
            // } elseif ($newRemaining > 0 && $newRemaining < ($jobCard->grand_total ?? 0)) {
            //     $paymentStatus = 'partial';
            // }
            if ($newRemaining <= 0 && $paymentAmount > 0) {
                $paymentStatus  = 'completed';
                $purchaseStatus = 'completed';
            } elseif ($newRemaining > 0 && $paymentAmount > 0) {
                $paymentStatus  = 'partially';
                $purchaseStatus = 'partially';
            } else {
                $paymentStatus  = 'pending';
                $purchaseStatus = 'pending';
            }

            Purchases::where('invoice_id', $jobCard->id)->update([
                'payment_status'  => $paymentStatus,
                'purchase_status' => $purchaseStatus,
                'updated_at'      => now(),
            ]);

            PurchaseInvoice::where('id', $jobCard->id)->update([
                // 'payment_status'  => $paymentStatus,
                'status'     => $purchaseStatus,
                'updated_at' => now(),
            ]);

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment submitted successfully.',
                'data'    => $payments,
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Purchase ID is required'], 400);
    }

    public function paymentHistory($purchaseId)
    {
        $history = PaymentStore::where('purchase_id', $purchaseId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    public function export_purchase(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $branch_id    = $user->id;

        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // ✅ Staff vs others
        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($user, $userBranchId) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $user->id)     // staff's own records
                        ->where('purchases.branch_id', '=', $userBranchId); // same branch
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        // ✅ Common joins
        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.purchase_id = purchase_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);

        // ✅ Date filter
        if ($request->has('date') && ! empty($request->date)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
                $query->whereDate('purchases.created_at', $date);
            } catch (\Exception $e) {
                $query->whereDate('purchases.created_at', $request->date);
            }
        }

        // ✅ Month filter
        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('purchases.created_at', $request->month);
        }

        if ($request->filled('year') && $request->year !== 'all') {
            $query->whereYear('purchases.created_at', $request->year);
        }
        // ✅ Vendor filter
        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->where('users.name', $request->customer_id);
        }

        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->get();

        if ($purchases->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No purchase data found for selected month and year.'
            ]);
        }

        // ✅ Add pending amount
        foreach ($purchases as $purchase) {
            $payment = \App\Models\PaymentStore::where('purchase_id', $purchase->id)
                ->orderBy('id', 'desc')
                ->first();
            $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
        }

        // ✅ OPTIMIZED: Cache currency settings
        $settings = cache()->remember("settings_branch_{$branch_id}", 300, function () use ($branch_id) {
            return DB::table('settings')->where('branch_id', $branch_id)->first();
        });
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

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

        // ✅ Create Excel
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Invoice Number');
        $sheet->setCellValue('B1', 'Vendor');
        $sheet->setCellValue('C1', 'Date');
        $sheet->setCellValue('D1', 'Products');
        $sheet->setCellValue('E1', 'Quantities');
        $sheet->setCellValue('F1', 'Prices');
        $sheet->setCellValue('G1', 'Grand Total');
        $sheet->setCellValue('H1', 'Purchase Status');
        $sheet->setCellValue('I1', 'Payment Status');
        $sheet->setCellValue('J1', 'Pending Amount');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $row = 2;

        foreach ($purchases as $purchase) {
            $sheet->setCellValue('A' . $row, $purchase->invoice_number);
            $sheet->setCellValue('B' . $row, $purchase->vendor_name);
            $sheet->setCellValue('C' . $row, \Carbon\Carbon::parse($purchase->date)->format('Y-m-d'));
            $sheet->setCellValue('D' . $row, $purchase->product_names);
            $sheet->setCellValue('E' . $row, $purchase->product_quantities);
            $sheet->setCellValue('F' . $row, $purchase->product_prices);
            $sheet->setCellValue('G' . $row, $currencyPosition === 'left'
                ? $currencySymbol . ' ' . $formatIndian($purchase->grand_total)
                : $formatIndian($purchase->grand_total) . ' ' . $currencySymbol);
            $sheet->setCellValue('H' . $row, ucfirst($purchase->purchase_status));
            $sheet->setCellValue('I' . $row, ucfirst($purchase->payment_status));
            $sheet->setCellValue('J' . $row, $currencyPosition === 'left'
                ? $currencySymbol . ' ' . $formatIndian($purchase->pending_amount)
                : $formatIndian($purchase->pending_amount) . ' ' . $currencySymbol);
            $row++;
        }

        // $writer   = new Xlsx($spreadsheet);
        // $fileName = 'Purchases_' . date('Ymd_His') . '.xlsx';

        // return response()->streamDownload(function () use ($writer) {
        //     $writer->save('php://output');
        // }, $fileName, [
        //     'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        //     'Access-Control-Expose-Headers' => 'Content-Disposition',
        // ]);
        $writer       = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename     = 'Purchases_' . date('Ymd_His') . '.xlsx';
        $relativePath = 'exports/' . $filename;

        // Save temporary file
        $temp_file = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($temp_file);
        Storage::disk('public')->put($relativePath, file_get_contents($temp_file));
        unlink($temp_file);

        // Generate public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Purchases Excel generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $filename,
        ]);
    }

    public function export_purchase_pdf(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $branch_id    = $user->id;

        if ($role === 'staff' && $userBranchId) {
            $branch_id = $userBranchId;
        } elseif ($role === 'admin' && ! empty($request->selectedSubAdminId)) {
            $branch_id = $request->selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }
        // dd($branch_id);

        if ($user->role === 'staff') {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($userBranchId, $user) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.created_by', '=', $user->id)     // ✅ staff’s own user ID
                        ->where('purchases.branch_id', '=', $userBranchId); // ✅ ensure same branch
                });
        } else {
            $query = DB::table('purchase_invoice')
                ->join('purchases', function ($join) use ($branch_id) {
                    $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                        ->where('purchases.isDeleted', '=', 0)
                        ->where('purchases.branch_id', '=', $branch_id);
                });
        }

        $query = $query
            ->join('users', 'purchases.vendor_id', '=', 'users.id')
            ->join('products', 'purchases.item', '=', 'products.id')
            ->select(
                'purchase_invoice.id',
                'users.name as vendor_name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at as date',
                DB::raw("GROUP_CONCAT(products.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(purchases.price SEPARATOR ', ') as product_prices"),
                DB::raw("GROUP_CONCAT(purchases.quantity SEPARATOR ', ') as product_quantities"),
                DB::raw("(SELECT COUNT(*) FROM payment_store
                  WHERE payment_store.purchase_id = purchase_invoice.id
                  AND payment_store.isDeleted = 0) as has_payment")
            )
            ->where('purchase_invoice.isDeleted', '=', 0);

        // ✅ Date filter
        if ($request->has('date') && ! empty($request->date)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $request->date)->format('Y-m-d');
                $query->whereDate('purchases.created_at', $date);
            } catch (\Exception $e) {
                $query->whereDate('purchases.created_at', $request->date);
            }
        }

        // ✅ Month filter
        if ($request->filled('month') && $request->month !== 'all') {
            $query->whereMonth('purchases.created_at', $request->month);
        }

        if ($request->filled('year') && $request->year !== 'all') {
            $query->whereYear('purchases.created_at', $request->year);
        }
        // ✅ Vendor filter
        if ($request->has('customer_id') && ! empty($request->customer_id)) {
            $query->where('users.name', $request->customer_id);
        }

        $purchases = $query
            ->groupBy(
                'purchase_invoice.id',
                'users.name',
                'purchase_invoice.invoice_number',
                'purchase_invoice.grand_total',
                'purchase_invoice.remaining_amount',
                'purchases.purchase_status',
                'purchases.payment_status',
                'purchase_invoice.created_at'
            )
            ->orderBy('purchase_invoice.id', 'desc')
            ->get();

        if ($purchases->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No purchase data found for selected filters.'
            ]);
        }
        // ✅ OPTIMIZED: Bulk load payments instead of N+1 queries
        $purchaseIds = $purchases->pluck('id')->toArray();
        $payments    = ! empty($purchaseIds)
            ? PaymentStore::whereIn('purchase_id', $purchaseIds)
            ->where('isDeleted', 0)
            ->orderBy('purchase_id')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('purchase_id')
            ->map(function ($group) {
                return $group->first();
            })
            : collect();

        $totalPending     = 0;
        $totalGrandAmount = 0;
        foreach ($purchases as $purchase) {
            $payment                  = $payments->get($purchase->id);
            $purchase->pending_amount = $payment ? $payment->remaining_amount : 0;
            $totalPending += $purchase->pending_amount;
            $totalGrandAmount += $purchase->grand_total;
        }

        // ✅ OPTIMIZED: Cache currency settings
        $settings = cache()->remember("settings_branch_{$branch_id}", 300, function () use ($branch_id) {
            return DB::table('settings')->where('branch_id', $branch_id)->first();
        });
        $currencySymbolRaw = $settings->currency_symbol ?? '₹';
        $currencySymbol    = trim(html_entity_decode($currencySymbolRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $currencyPosition  = $settings->currency_position ?? 'left';

        // Get user info for PDF
        $userName = $user->name ?? 'N/A';
        $gstNum   = $user->gst_number ?? 'N/A';

        // ✅ Generate PDF using a Blade view
        $pdf = Pdf::loadView('purchase.purchase_pdf', [
            'purchases'        => $purchases,
            'totalPending'     => $totalPending,
            'totalGrandAmount' => $totalGrandAmount,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'settings'         => $settings,
            'userName'         => $userName,
            'gstNum'           => $gstNum,
        ]);

        // 🔹 Save PDF to storage
        $fileName     = 'Purchases_' . now()->format('Ymd_His') . '.pdf';
        $relativePath = 'purchase-reports/' . $fileName;
        Storage::disk('public')->put($relativePath, $pdf->output());

        // 🔹 Generate full public URL

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        // 🔹 Return JSON response with PDF info
        return response()->json([
            'status'    => true,
            'message'   => 'Purchase PDF generated successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);
    }

    public function export_purchases_report_pdf_api(Request $request)
    {
        try {
            $user               = Auth::guard('api')->user();
            $branchId           = $user->id ?? null;
            $UserBranchId       = $user->branch_id ?? null;
            $userRole           = $user->role ?? '';
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // ✅ Determine branch_id based on role
            if ($userRole === 'sub-admin') {
                $branchId = $branchId;
            } elseif ($userRole === 'admin' && $selectedSubAdminId) {
                $branchId = $selectedSubAdminId;
            } elseif ($userRole === 'staff') {
                $branchId = $UserBranchId;
            }

            // ✅ Collect IDs from form-data (ids[] = 1, ids[] = 2, etc.)
            $idsArray = $request->input('ids', []); // ensures an array, even if empty
            // ✅ Store all IDs into one variable (as comma-separated string, if needed)
            $idsString = implode(',', $idsArray);
            // dd($idsArray);

            if (empty($idsArray)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase IDs provided.',
                ]);
            }

            // ✅ Fetch purchase data
            $purchases = Purchases::with('product', 'invoice', 'vendor')
                ->whereIn('id', $idsArray)
                ->where('branch_id', $branchId)
                ->get();

            if ($purchases->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase data found.',
                ]);
            }

            // ✅ OPTIMIZED: Cache settings
            $setting = cache()->remember("setting_branch_{$branchId}", 300, function () use ($branchId) {
                return Setting::where('branch_id', $branchId)->first();
            });
            $subtotalRaw = (float) $purchases->sum('amount_total');

            $discountPercent   = 0.0;
            $discountAmountRaw = ($discountPercent / 100.0) * $subtotalRaw;
            $afterDiscountRaw  = $subtotalRaw - $discountAmountRaw;

            $invoiceIds  = [];
            $shippingRaw = 0.0;
            foreach ($purchases as $purchase) {
                if ($purchase->invoice && ! in_array($purchase->invoice->id, $invoiceIds)) {
                    $invoiceIds[] = $purchase->invoice->id;
                    $shippingRaw += (float) ($purchase->invoice->shipping ?? 0);
                }
            }

            $taxRates = TaxRate::where('status', 'active')
                ->where('branch_id', $branchId)
                ->where('isDeleted', 0)
                ->get();

            $taxDetails     = [];
            $formatCurrency = function ($amt) use ($setting) {
                return $setting->currency_position === 'right'
                    ? number_format($amt, 2) . $setting->currency_symbol
                    : $setting->currency_symbol . number_format($amt, 2);
            };

            foreach ($taxRates as $tax) {
                $amount       = ($tax->tax_rate / 100.0) * $afterDiscountRaw;
                $taxDetails[] = [
                    'name'             => $tax->tax_name,
                    'rate'             => $tax->tax_rate,
                    'amount'           => $amount,
                    'formatted_amount' => $formatCurrency($amount),
                ];
            }

            $grandTotalRaw = $afterDiscountRaw + $shippingRaw + collect($taxDetails)->sum('amount');

            $invoiceRecord = PurchaseInvoice::whereIn('id', $idsArray)
                ->where('branch_id', $branchId)
                ->first();

            $invoice = (object) [
                'invoice_number'   => $invoiceRecord->invoice_number ?? 'PR-' . now()->format('YmdHis'),
                'created_at'       => $invoiceRecord->created_at ?? now()->format('Y-m-d H:i:s'),
                'paid'             => $invoiceRecord->paid ?? false,
                'status'           => $invoiceRecord->status ?? 'completed',
                'remaining_amount' => $invoiceRecord->remaining_amount ?? 0,
                'gst_option'       => $invoiceRecord->gst_option ?? 'without_gst',
            ];

            $compenyinfo      = $setting;
            $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
            $currencyPosition = $compenyinfo->currency_position ?? 'left';
            $vendor           = $purchases->first()->vendor ?? null;

            $pdfData = [
                'invoice'          => $invoice,
                'vendor'           => [
                    'name'    => $vendor->name ?? 'Walk-in Vendor',
                    'email'   => $vendor->email ?? '',
                    'phone'   => $vendor->phone ?? '',
                    'address' => $vendor->address ?? '',
                ],
                'purchases'        => $purchases,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'setting'          => $setting,
                'subtotal'         => $formatCurrency($subtotalRaw),
                'discount'         => $discountPercent,
                'discountAmount'   => $formatCurrency($discountAmountRaw),
                'afterDiscount'    => $formatCurrency($afterDiscountRaw),
                'shipping'         => $formatCurrency($shippingRaw),
                'taxDetails'       => $taxDetails,
                'grandTotal'       => $formatCurrency($grandTotalRaw),
                'payment_status'   => ucfirst($purchases->first()->payment_status ?? 'Pending'),
                'payment_method'   => ucfirst($purchases->first()->payment_method ?? 'N/A'),
            ];

            // ✅ Generate the PDF
            $pdf = PDF::loadView('purchase.purchase-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont'          => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // ✅ Generate a unique filename
            $filename     = 'purchase_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'purchase-reports/' . $filename;

            // ✅ Save to storage/public/purchase-reports/
            Storage::disk('public')->put($relativePath, $pdf->output());

            // ✅ Build public URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status'    => true,
                'message'   => 'Purchase report PDF generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $filename,
                'ids_used'  => $idsString, // ✅ shows all IDs used
            ]);
        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Purchase Report PDF.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function purchaseProductChart(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $role         = $user->role;
        $userBranchId = $user->branch_id;
        $userId       = $user->id;
        $subAdminId   = $request->selectedSubAdminId;

        // 🔹 Determine branch logic
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = $userBranchId;
        } elseif ($role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $query = Purchases::with('product')
            ->where('purchase_status', 'completed')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        if ($role === 'staff') {
            $query->whereHas('invoice', function ($q) use ($userId) {
                $q->where('created_by', $userId);
            });
        }

        // 🔹 Apply filters
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        // dd($request->category_id);
        // 🔹 Aggregate totals
        $data = $query->selectRaw('item, SUM(quantity) as total_qty, SUM(amount_total) as total_amount')
            ->groupBy('item')
            ->with('product:id,name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        $labels     = $data->pluck('product.name')->toArray();
        $totals     = $data->pluck('total_qty')->toArray();
        $grandTotal = $data->sum('total_amount');

        return response()->json([
            'status'      => true,
            'labels'      => $labels,
            'totals'      => $totals,
            'grand_total' => $grandTotal,
        ]);
    }

    public function view_purchase_report(Request $request)
    {
        try {
            $ids                = $request->input('ids');
            $selectedSubAdminId = $request->input('selectedSubAdminId');
            $branchIdToUse      = $request->input('branch');

            if (empty($ids)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No purchase IDs provided.',
                ], 400);
            }

            // Convert IDs array to string if needed
            $idsString = is_array($ids) ? implode(',', $ids) : $ids;

            $authUser = Auth::guard('api')->user();

            // 🔹 Determine branch ID logic
            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && ! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // 🔹 No authentication — require branch ID from frontend
                if (! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } elseif (! empty($branchIdToUse)) {
                    $branchIdToUse = $branchIdToUse;
                } else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // 🔹 Generate report view URL
            $reportUrl = url('purchase/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status'    => true,
                'message'   => 'Purchase report link generated successfully.',
                'view_link' => $reportUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate purchase report link.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
