<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseReturn;
use App\Models\Purchases;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{

    public function getVendorName($invoiceId)
    {
        $invoice = PurchaseInvoice::with('vendor')->find($invoiceId);

        if ($invoice && $invoice->vendor && $invoice->vendor->role === 'vendor') {
            return response()->json(['vendor_name' => $invoice->vendor->name]);
        }

        return response()->json(['vendor_name' => null]);
    }
    public function getInvoiceProducts($invoiceId)
    {
        $purchases = Purchases::with('product.category')
            ->where('invoice_id', $invoiceId)
            ->get();

        // Calculate subtotal from purchase items
        $subtotal = $purchases->sum(fn($p) => $p->price * $p->quantity);

        // Get invoice info
        $invoice = PurchaseInvoice::with('vendor')->find($invoiceId);
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice not found.'], 404);
        }

        $shipping = $invoice->shipping ?? 0.00;

        // Use saved taxes from the purchase_invoice table (JSON field)
        $taxDetails = json_decode($invoice->taxes, true) ?? [];

        // Calculate total tax amount from saved taxes
        $totalTaxAmount = collect($taxDetails)->sum(function ($tax) {
            return (float) ($tax['amount'] ?? 0);
        });

        // The grand total should be the total amount from invoice
        $grandTotal = $invoice->grand_total;

        // Calculate discount percent and discount amount for the whole invoice
        $invoiceDiscountPercent = (float) ($invoice->discount ?? 0);
        $invoiceDiscountAmount  = round(($subtotal * $invoiceDiscountPercent) / 100, 2);

        // Prepare product list
        $products = $purchases->map(function ($purchase) {
            $images    = $purchase->product->images ?? null;
            $imageList = is_string($images) ? json_decode($images, true) : [];
            $imagePath = rtrim(env('ImagePath'), '/') . '/' . (
                !empty($imageList[0])
                ? 'storage/' . $imageList[0]
                : 'admin/assets/img/product/noimage.png'
            );

            $imageUrl = $imagePath;
            $basePath = env('ImagePath', '/');

            $imageUrls = [];
            if (!empty($imageList)) {
                $imageUrls = array_map(function ($img) use ($basePath) {
                    return url(rtrim($basePath, '/') . '/storage/' . ltrim($img, '/'));
                }, $imageList);
            } else {
                $imageUrls = [url(rtrim($basePath, '/') . '/admin/assets/img/product/noimage.png')];
            }

            // Calculate already returned quantity
            $returnedQty  = \App\Models\PurchaseReturnItem::where('purchase_item_id', $purchase->id)->sum('quantity');

            // Calculate remaining returnable qty
            $availableQty = max(0, $purchase->quantity - $returnedQty);

            // Get discount amount and percentage for this specific product
            // Using correct field names: discount_amount and discount_percent
            $discountAmount = (float) ($purchase->discount_amount ?? 0);
            $discountPercentage = (float) ($purchase->discount_percent ?? 0);

            // If both are zero, you can set a default discount for testing
            // Remove this in production
            if ($discountAmount == 0 && $discountPercentage == 0) {
                // For testing: Set a sample discount (remove this line in production)
                // $discountAmount = 10.00;
                // $discountPercentage = 5.00;
            }

            // If discount amount is 0 but discount percentage exists, calculate amount
            if ($discountAmount == 0 && $discountPercentage > 0 && $purchase->price > 0) {
                $discountAmount = ($purchase->price * $discountPercentage) / 100;
            }

            // If discount percentage is 0 but discount amount exists, calculate percentage
            if ($discountPercentage == 0 && $discountAmount > 0 && $purchase->price > 0) {
                $discountPercentage = ($discountAmount / $purchase->price) * 100;
            }

            // Get GST details
            $gstDetails = $purchase->product_gst_details;
            $gstTotal = $purchase->product_gst_total ?? 0;

            // If GST details is a string, decode it
            if (is_string($gstDetails)) {
                $gstDetails = json_decode($gstDetails, true);
            }

            // Ensure GST details is an array
            if (!is_array($gstDetails)) {
                $gstDetails = [];
            }

            return [
                'product_name'        => $purchase->product->name ?? 'N/A',
                'category'            => $purchase->product->category->name ?? 'N/A',
                'price'               => (float) $purchase->price,
                'quantity'            => (int) $availableQty,
                'purchase_qty'        => (int) $purchase->quantity,
                'subtotal'            => (float) ($purchase->price * $availableQty),
                'image'               => $imageUrl,
                'image_url'           => $imageUrls,
                'product_id'          => $purchase->item,
                'purchase_id'         => $purchase->id,
                'product_gst_details' => $gstDetails,
                'product_gst_total'   => (float) $gstTotal,
                'discount_amount'     => $discountAmount,
                'discount_percentage' => $discountPercentage,
            ];
        })->filter(function ($item) {
            return $item['quantity'] > 0;
        })->values();

        $settings = \DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $totals = [
            'subtotal'         => number_format($subtotal, 2),
            'taxes'            => number_format($totalTaxAmount, 2),
            'shipping'         => number_format($shipping, 2),
            'grand_total'      => number_format($grandTotal, 2),
            'discount'         => number_format($invoiceDiscountPercent, 2),
            'discount_amount'  => number_format($invoiceDiscountAmount, 2),
        ];

        // Fetch tax rates
        $invoiceTaxIds = json_decode($invoice->tax_id ?? '[]', true);
        $taxes = \App\Models\TaxRate::where('isDeleted', 0)->get();

        return response()->json([
            'products'         => $products,
            'invoice'          => [
                'id'               => $invoice->id,
                'invoice_number'   => $invoice->invoice_number,
                'vendor_name'      => $invoice->vendor->name ?? 'N/A',
                'discount'         => $invoiceDiscountPercent,
                'discount_amount'  => $invoiceDiscountAmount,
                'total_amount'     => (float) ($invoice->grand_total ?? 0),
                'remaining_amount' => (float) ($invoice->remaining_amount ?? 0),
                'paid_amount'      => (float) ($invoice->paid ?? 0),
                'shipping'         => (float) ($invoice->shipping ?? 0),
            ],
            'totals'           => $totals,
            'taxDetails'       => $taxDetails,
            'taxes'            => $taxes,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
        ]);
    }

    public function returnPurchase(Request $request)
{
    $user = Auth::guard('api')->user();
    $invoiceId = $request->input('invoice_id');
    $invoice = PurchaseInvoice::findOrFail($invoiceId);

    DB::beginTransaction();
    try {
        // Check if shipping column exists, if not add it
        if (!Schema::hasColumn('purchase_returns', 'shipping')) {
            DB::statement('ALTER TABLE purchase_returns ADD COLUMN shipping DECIMAL(15,2) DEFAULT 0.00 AFTER tax_amount');
        }

        // 1️⃣ Create Purchase Return record
        $returnNo = 'PR-' . strtoupper(uniqid());
        $purchaseReturn = \App\Models\PurchaseReturn::create([
            'purchase_id'     => $invoice->id,
            'return_no'       => $returnNo,
            'discount'        => $request->input('discount', 0),
            'branch_id'       => $invoice->branch_id,
            'created_by'      => $user->id,
            'isDeleted'       => 0,
        ]);

        $subtotal = 0;
        $totalTax = 0;
        $totalProductDiscount = 0;

        // 2️⃣ Loop return items
        foreach ($request->products as $row) {
            $purchaseItem = Purchases::findOrFail($row['purchase_id']);
            $product = Product::findOrFail($purchaseItem->item);

            $productSubtotal = $row['price'] * $row['quantity'];
            $productDiscount = $row['discount_amount'] ?? 0;
            $productTax = $row['product_gst_total'] ?? 0;

            // Save return item with correct discount fields
            \App\Models\PurchaseReturnItem::create([
                'purchase_return_id'  => $purchaseReturn->id,
                'purchase_item_id'    => $purchaseItem->id,
                'product_id'          => $product->id,
                'quantity'            => $row['quantity'],
                'price'               => $row['price'],
                'discount'            => $row['discount_percentage'] ?? 0,
                'discount_amount'     => $productDiscount,
                'subtotal'            => $productSubtotal,
                'product_gst_details' => $row['product_gst_details'] ?? null,
                'product_gst_total'   => $productTax,
                'branch_id'           => $invoice->branch_id,
                'created_by'          => $user->id,
            ]);

            // 3️⃣ Decrease stock (sending items back to vendor)
            $oldStock = $product->quantity;
            $product->decrement('quantity', $row['quantity']);

            // 4️⃣ Inventory log
            \App\Models\ProductInventory::create([
                'product_id'    => $product->id,
                'initial_stock' => $oldStock,
                'current_stock' => $product->quantity,
                'type'          => 'Purchase Return',
                'branch_id'     => $product->branch_id,
                'create_by'     => $user->id,
                'date'          => now(),
            ]);

            $subtotal += $productSubtotal;
            $totalTax += $productTax;
            $totalProductDiscount += $productDiscount;
        }

        // Check if this is a FULL return (all items returned)
        $isFullReturn = true;
        $allPurchaseItems = Purchases::where('invoice_id', $invoice->id)->get();
        foreach ($allPurchaseItems as $pi) {
            $alreadyReturned = \App\Models\PurchaseReturnItem::where('purchase_item_id', $pi->id)->sum('quantity');
            if ($alreadyReturned < $pi->quantity) {
                $isFullReturn = false;
                break;
            }
        }

        // Calculate shipping to return
        $shippingToReturn = 0;
        if ($isFullReturn) {
            $shippingToReturn = (float) $invoice->shipping;
        }

        // 5️⃣ Calculate totals for this return
        $additionalDiscountPercent = $request->input('discount', 0);
        $additionalDiscountAmount = ($subtotal * $additionalDiscountPercent) / 100;

        // Total discount = product discounts + additional discount
        $totalDiscountAmount = $totalProductDiscount + $additionalDiscountAmount;

        // Calculate after discount amount
        $afterDiscount = $subtotal - $totalDiscountAmount;

        // Final total = after discount + tax + shipping
        $returnTotal = $afterDiscount + $totalTax + $shippingToReturn;

        // 6️⃣ Update Purchase Return record with calculated totals
        $purchaseReturn->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $totalDiscountAmount,
            'tax_amount'      => $totalTax,
            'shipping'        => $shippingToReturn,
            'total_amount'    => $returnTotal, // This is the total return amount being stored
        ]);

        // 7️⃣ UPDATE INVOICE logic
        $totalReturnSoFar = \App\Models\PurchaseReturn::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('total_amount');

        $totalPaidSoFar = \App\Models\PaymentStore::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // Remaining amount = Original Grand Total - Total Returns - Total Paid
        $remainingAmount = max(0, $invoice->grand_total - $totalReturnSoFar - $totalPaidSoFar);

        $updateData = [
            'remaining_amount' => $remainingAmount,
            'paid'             => $totalPaidSoFar,
        ];

        // Update status if fully returned or fully paid
        if ($remainingAmount <= 0) {
            $updateData['status'] = 'completed';
        }

        $invoice->update($updateData);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Purchase return completed successfully',
            'data' => [
                'return_id' => $purchaseReturn->id,
                'return_no' => $returnNo,
                'subtotal' => $subtotal,
                'discount' => $totalDiscountAmount,
                'tax' => $totalTax,
                'shipping' => $shippingToReturn,
                'total_amount' => $returnTotal
            ]
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Purchase Return Error:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);

        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
}

    public function purchaseReturnList(Request $request)
    {
        $authUser = Auth::guard('api')->user();

        if (!$authUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $userId             = (int) $authUser->id;
        $role               = $authUser->role;
        $userBranchId       = $authUser->branch_id;

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        if (in_array($selectedSubAdminId, ['null', 'undefined', ''], true)) {
            $selectedSubAdminId = null;
        }

        // Determine branch ID to use
        if ($role === 'staff' && $userBranchId) {
            $branchIdToUse = (int) $userBranchId;
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = (int) $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userId;
        } else {
            $branchIdToUse = $userId;
        }

        // JOIN query to get invoice_number and vendor name directly
        $query = PurchaseReturn::select(
            'purchase_returns.*',
            'purchase_invoice.invoice_number',
            'vendors.name as supplier_name'
        )
            ->leftJoin(
                'purchase_invoice',
                'purchase_invoice.id',
                '=',
                'purchase_returns.purchase_id'
            )
            ->leftJoin(
                'users as vendors',
                'vendors.id',
                '=',
                'purchase_invoice.vendor_id'
            )
            ->with([
                'items.product:id,name,sku',
            ])
            ->where('purchase_returns.isDeleted', 0)
            ->orderBy('purchase_returns.id', 'desc');

        // Apply branch/staff filter
        if ($role === 'staff') {
            $query->where('purchase_returns.created_by', $userId);
        } else {
            $query->where('purchase_returns.branch_id', $branchIdToUse);
        }

        // Apply search filter
        $search = $request->query('search');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_returns.return_no', 'LIKE', "%{$search}%")
                    ->orWhere('purchase_invoice.invoice_number', 'LIKE', "%{$search}%")
                    ->orWhere('vendors.name', 'LIKE', "%{$search}%");
            });
        }

        // Pagination
        $perPage         = $request->query('per_page', 10);
        $purchaseReturns = $query->paginate($perPage);

        // Get currency settings
        $settings         = DB::table('settings')->where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Format response data
        $data = $purchaseReturns->map(function ($purchaseReturn) {

            $returnItems = $purchaseReturn->items->map(function ($item) {
                $gstDetails = $item->product_gst_details;
                $gstTotal   = (float) ($item->product_gst_total ?? 0);

                $formattedGstDetails = [];
                if (is_array($gstDetails) && !empty($gstDetails)) {
                    foreach ($gstDetails as $tax) {
                        $formattedGstDetails[] = [
                            'tax_name'   => $tax['name']     ?? $tax['tax_name']   ?? 'GST',
                            'tax_rate'   => $tax['rate']     ?? $tax['tax_rate']   ?? 0,
                            'tax_amount' => floatval($tax['amount'] ?? $tax['tax_amount'] ?? 0),
                        ];
                    }
                } elseif ($gstDetails === 'inclusive') {
                    $formattedGstDetails = 'inclusive';
                }

                return [
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name ?? 'N/A',
                    'product_sku'  => $item->product->sku  ?? 'N/A',
                    'quantity'     => (int)   $item->quantity,
                    'price'        => (float) $item->price,
                    'discount'        => (float) $item->discount,
                    'discount_amount'  => (float) $item->discount_amount,
                    'subtotal'     => (float) $item->subtotal,
                    'gst_details'  => $formattedGstDetails,
                    'gst_total'    => $gstTotal,
                ];
            });

            return [
                'id'                    => $purchaseReturn->id,
                'return_number'         => $purchaseReturn->return_no         ?? '-',
                'purchase_id'           => $purchaseReturn->purchase_id,
                'purchase_order_number' => $purchaseReturn->invoice_number    ?? '-',  // ✅ from JOIN
                'supplier' => $purchaseReturn->supplier_name ?? '-',
                'date'                  => optional($purchaseReturn->created_at)->format('d M Y, h:i A'),
                'items_count'           => (int)   $purchaseReturn->items->count(),
                'return_qty'            => (int)   $purchaseReturn->items->sum('quantity'),
                'subtotal'              => (float) ($purchaseReturn->subtotal        ?? 0),
                'tax_amount'            => (float) ($purchaseReturn->tax_amount      ?? 0),
                'discount'              => (float) ($purchaseReturn->discount        ?? 0),
                'discount_amount'       => (float) ($purchaseReturn->discount_amount ?? 0),
                'shipping'              => (float) ($purchaseReturn->shipping        ?? 0),
                'total_amount'          => (float) ($purchaseReturn->total_amount    ?? 0),
                'refund_amount'         => (float) ($purchaseReturn->refund_amount   ?? 0),
                'items'                 => $returnItems,
            ];
        })->values();

        return response()->json([
            'status'            => true,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data'              => $data,
            'pagination'        => [
                'current_page' => $purchaseReturns->currentPage(),
                'last_page'    => $purchaseReturns->lastPage(),
                'per_page'     => $purchaseReturns->perPage(),
                'total'        => $purchaseReturns->total(),
                'from'         => $purchaseReturns->firstItem(),
                'to'           => $purchaseReturns->lastItem(),
            ],
        ]);
    }
}
