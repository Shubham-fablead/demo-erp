<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\TaxRate;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    // public function salesReturnList(Request $request)
    // {
    //     $authUser = Auth::guard('api')->user();

    //     if (! $authUser) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthenticated.',
    //         ], 401);
    //     }

    //     $userId       = (int) $authUser->id;
    //     $role         = $authUser->role;
    //     $userBranchId = $authUser->branch_id;

    //     $selectedSubAdminId = $request->query('selectedSubAdminId');
    //     if (in_array($selectedSubAdminId, ['null', 'undefined', ''], true)) {
    //         $selectedSubAdminId = null;
    //     }

    //     if ($role === 'staff' && $userBranchId) {
    //         $branchIdToUse = (int) $userBranchId;
    //     } elseif ($role === 'admin' && ! empty($selectedSubAdminId)) {
    //         $branchIdToUse = (int) $selectedSubAdminId;
    //     } elseif ($role === 'sub-admin') {
    //         $branchIdToUse = $userId;
    //     } else {
    //         $branchIdToUse = $userId;
    //     }

    //     $query = SalesReturn::with([
    //         'order:id,order_number,user_id',
    //         'order.user:id,name',
    //         'items:id,sales_return_id,order_item_id,quantity',
    //     ])->orderBy('id', 'desc');

    //     if ($role === 'staff') {
    //         $query->where('created_by', $userId);
    //     } else {
    //         $query->where('branch_id', $branchIdToUse);
    //     }

    //     $salesReturns = $query->get();

    //     $legacyOrderRefs = $salesReturns
    //         ->pluck('order_id')
    //         ->filter(function ($value) {
    //             return ! empty($value);
    //         })
    //         ->map(function ($value) {
    //             return (string) $value;
    //         })
    //         ->unique()
    //         ->values();

    //     $numericOrderIds = $legacyOrderRefs
    //         ->filter(function ($value) {
    //             return ctype_digit($value);
    //         })
    //         ->map(function ($value) {
    //             return (int) $value;
    //         })
    //         ->unique()
    //         ->values();

    //     $derivedOrderIdsBySalesReturnId = collect();

    //     if ($salesReturns->isNotEmpty()) {
    //         $derivedOrderIdsBySalesReturnId = DB::table('sales_return_items')
    //             ->join('order_items', 'order_items.id', '=', 'sales_return_items.order_item_id')
    //             ->whereIn('sales_return_items.sales_return_id', $salesReturns->pluck('id'))
    //             ->select('sales_return_items.sales_return_id', 'order_items.order_id')
    //             ->get()
    //             ->groupBy('sales_return_id')
    //             ->map(function ($rows) {
    //                 return (int) optional($rows->first())->order_id;
    //             });
    //     }

    //     $allOrderIds = $numericOrderIds
    //         ->merge($derivedOrderIdsBySalesReturnId->values())
    //         ->filter(function ($value) {
    //             return ! empty($value);
    //         })
    //         ->unique()
    //         ->values();

    //     $fallbackOrdersById = collect();
    //     $fallbackOrdersByNumber = collect();

    //     if ($allOrderIds->isNotEmpty() || $legacyOrderRefs->isNotEmpty()) {
    //         $fallbackOrders = Order::select('id', 'order_number', 'user_id')
    //             ->with('user:id,name')
    //             ->where(function ($query) use ($allOrderIds, $legacyOrderRefs) {
    //                 if ($allOrderIds->isNotEmpty()) {
    //                     $query->orWhereIn('id', $allOrderIds);
    //                 }
    //                 if ($legacyOrderRefs->isNotEmpty()) {
    //                     $query->orWhereIn('order_number', $legacyOrderRefs);
    //                 }
    //             })
    //             ->get();

    //         $fallbackOrdersById = $fallbackOrders->keyBy(function ($order) {
    //             return (string) $order->id;
    //         });

    //         $fallbackOrdersByNumber = $fallbackOrders->keyBy(function ($order) {
    //             return (string) $order->order_number;
    //         });
    //     }

    //     $settings = DB::table('settings')->where('branch_id', $branchIdToUse)->first();
    //     $currencySymbol = $settings->currency_symbol ?? '?';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $data = $salesReturns->map(function ($salesReturn) use ($fallbackOrdersById, $fallbackOrdersByNumber, $derivedOrderIdsBySalesReturnId) {
    //         $order = $salesReturn->order;

    //         if (! $order && ! empty($salesReturn->order_id)) {
    //             $orderRef = (string) $salesReturn->order_id;
    //             $order = $fallbackOrdersById->get($orderRef) ?? $fallbackOrdersByNumber->get($orderRef);
    //         }

    //         if (! $order) {
    //             $derivedOrderId = $derivedOrderIdsBySalesReturnId->get($salesReturn->id);
    //             if (! empty($derivedOrderId)) {
    //                 $order = $fallbackOrdersById->get((string) $derivedOrderId);
    //             }
    //         }

    //         return [
    //             'id'              => $salesReturn->id,
    //             'return_number'   => $salesReturn->return_number ?? '-',
    //             'order_id'        => $salesReturn->order_id,
    //             'order_number'    => $order->order_number ?? ((string) ($salesReturn->order_id ?? '-')),
    //             'date'            => optional($salesReturn->created_at)->format('d M Y, h:i A'),
    //             'customer'        => data_get($order, 'user.name', '-'),
    //             'items_count'     => (int) $salesReturn->items->count(),
    //             'return_qty'      => (int) $salesReturn->items->sum('quantity'),
    //             'subtotal'        => (float) ($salesReturn->subtotal ?? 0),
    //             'tax_amount'      => (float) ($salesReturn->tax_amount ?? 0),
    //             'discount'        => (float) ($salesReturn->discount ?? 0),
    //             'discount_amount' => (float) ($salesReturn->discount_amount ?? 0),
    //             'total_amount'    => (float) ($salesReturn->total_amount ?? 0),
    //         ];
    //     })->values();

    //     return response()->json([
    //         'status'            => true,
    //         'currency_symbol'   => $currencySymbol,
    //         'currency_position' => $currencyPosition,
    //         'data'              => $data,
    //     ]);
    // }

public function salesReturnList(Request $request)
{
    $authUser = Auth::guard('api')->user();

    if (! $authUser) {
        return response()->json([
            'status'  => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    $userId       = (int) $authUser->id;
    $role         = $authUser->role;
    $userBranchId = $authUser->branch_id;

    $selectedSubAdminId = $request->query('selectedSubAdminId');
    if (in_array($selectedSubAdminId, ['null', 'undefined', ''], true)) {
        $selectedSubAdminId = null;
    }

    if ($role === 'staff' && $userBranchId) {
        $branchIdToUse = (int) $userBranchId;
    } elseif ($role === 'admin' && ! empty($selectedSubAdminId)) {
        $branchIdToUse = (int) $selectedSubAdminId;
    } elseif ($role === 'sub-admin') {
        $branchIdToUse = $userId;
    } else {
        $branchIdToUse = $userId;
    }

    $query = SalesReturn::with([
        'order:id,order_number,user_id',
        'order.user:id,name',
        'items' => function($query) {
            $query->with('product:id,name');
        },
    ])->orderBy('id', 'desc');

    if ($role === 'staff') {
        $query->where('created_by', $userId);
    } else {
        $query->where('branch_id', $branchIdToUse);
    }

    // Apply search filter
    $search = $request->query('search');
    if (!empty($search)) {
        $query->where(function($q) use ($search) {
            $q->where('return_number', 'LIKE', "%{$search}%")
              ->orWhere('order_id', 'LIKE', "%{$search}%")
              ->orWhereHas('order', function($orderQuery) use ($search) {
                  $orderQuery->where('order_number', 'LIKE', "%{$search}%")
                            ->orWhereHas('user', function($userQuery) use ($search) {
                                $userQuery->where('name', 'LIKE', "%{$search}%");
                            });
              });
        });
    }

    // Pagination
    $perPage = $request->query('per_page', 10);
    $salesReturns = $query->paginate($perPage);

    $legacyOrderRefs = $salesReturns
        ->pluck('order_id')
        ->filter(function ($value) {
            return ! empty($value);
        })
        ->map(function ($value) {
            return (string) $value;
        })
        ->unique()
        ->values();

    $numericOrderIds = $legacyOrderRefs
        ->filter(function ($value) {
            return ctype_digit($value);
        })
        ->map(function ($value) {
            return (int) $value;
        })
        ->unique()
        ->values();

    $derivedOrderIdsBySalesReturnId = collect();

    if ($salesReturns->isNotEmpty()) {
        $derivedOrderIdsBySalesReturnId = DB::table('sales_return_items')
            ->join('order_items', 'order_items.id', '=', 'sales_return_items.order_item_id')
            ->whereIn('sales_return_items.sales_return_id', $salesReturns->pluck('id'))
            ->select('sales_return_items.sales_return_id', 'order_items.order_id')
            ->get()
            ->groupBy('sales_return_id')
            ->map(function ($rows) {
                return (int) optional($rows->first())->order_id;
            });
    }

    $allOrderIds = $numericOrderIds
        ->merge($derivedOrderIdsBySalesReturnId->values())
        ->filter(function ($value) {
            return ! empty($value);
        })
        ->unique()
        ->values();

    $fallbackOrdersById = collect();
    $fallbackOrdersByNumber = collect();

    if ($allOrderIds->isNotEmpty() || $legacyOrderRefs->isNotEmpty()) {
        $fallbackOrders = Order::select('id', 'order_number', 'user_id')
            ->with('user:id,name')
            ->where(function ($query) use ($allOrderIds, $legacyOrderRefs) {
                if ($allOrderIds->isNotEmpty()) {
                    $query->orWhereIn('id', $allOrderIds);
                }
                if ($legacyOrderRefs->isNotEmpty()) {
                    $query->orWhereIn('order_number', $legacyOrderRefs);
                }
            })
            ->get();

        $fallbackOrdersById = $fallbackOrders->keyBy(function ($order) {
            return (string) $order->id;
        });

        $fallbackOrdersByNumber = $fallbackOrders->keyBy(function ($order) {
            return (string) $order->order_number;
        });
    }

    $settings = DB::table('settings')->where('branch_id', $branchIdToUse)->first();
    $currencySymbol = $settings->currency_symbol ?? '₹';
    $currencyPosition = $settings->currency_position ?? 'left';

    $data = $salesReturns->map(function ($salesReturn) use ($fallbackOrdersById, $fallbackOrdersByNumber, $derivedOrderIdsBySalesReturnId) {
        $order = $salesReturn->order;

        if (! $order && ! empty($salesReturn->order_id)) {
            $orderRef = (string) $salesReturn->order_id;
            $order = $fallbackOrdersById->get($orderRef) ?? $fallbackOrdersByNumber->get($orderRef);
        }

        if (! $order) {
            $derivedOrderId = $derivedOrderIdsBySalesReturnId->get($salesReturn->id);
            if (! empty($derivedOrderId)) {
                $order = $fallbackOrdersById->get((string) $derivedOrderId);
            }
        }

        // Format return items
        $returnItems = $salesReturn->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name ?? 'N/A',
                'quantity' => (int) $item->quantity,
                'price' => (float) $item->price,
                'discount'=>(float) $item->discount,
                'discount_amount'=>(float) $item->discount_amount,
                'subtotal' => (float) $item->subtotal,
                'gst_details' => $item->product_gst_details,
                'gst_total' => (float) ($item->product_gst_total ?? 0), // Added GST total amount
            ];
        });

        return [
            'id'              => $salesReturn->id,
            'return_number'   => $salesReturn->return_number ?? '-',
            'order_id'        => $salesReturn->order_id,
            'order_number'    => $order->order_number ?? ((string) ($salesReturn->order_id ?? '-')),
            'date'            => optional($salesReturn->created_at)->format('d M Y, h:i A'),
            'customer'        => data_get($order, 'user.name', '-'),
            'items_count'     => (int) $salesReturn->items->count(),
            'return_qty'      => (int) $salesReturn->items->sum('quantity'),
            'subtotal'        => (float) ($salesReturn->subtotal ?? 0),
            'tax_amount'      => (float) ($salesReturn->tax_amount ?? 0),
            'discount'        => (float) ($salesReturn->discount ?? 0),
            'discount_amount' => (float) ($salesReturn->discount_amount ?? 0),
            'total_amount'    => (float) ($salesReturn->total_amount ?? 0),
            'items'           => $returnItems,
        ];
    })->values();

    return response()->json([
        'status'            => true,
        'currency_symbol'   => $currencySymbol,
        'currency_position' => $currencyPosition,
        'data'              => $data,
        'pagination'        => [
            'current_page' => $salesReturns->currentPage(),
            'last_page'    => $salesReturns->lastPage(),
            'per_page'     => $salesReturns->perPage(),
            'total'        => $salesReturns->total(),
            'from'         => $salesReturns->firstItem(),
            'to'           => $salesReturns->lastItem(),
        ],
    ]);
}

    public function getSaleDetails(Request $request, $invoiceNumber)
    {
        $user          = Auth::guard('api')->user();
        $role          = $user->role;
        $userBranchId  = $user->id;
        $BranchId      = $user->branch_id;
        $branchIdToUse = $BranchId;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userBranchId;

        if ($role === 'admin' && $selectedSubAdminId) {
            $branchIdToUse = $selectedSubAdminId;
        } elseif ($role === 'sub-admin') {
            $branchIdToUse = $userBranchId; // sub-admin branch
        }
        // dd($selectedSubAdminId);
        $order = Order::where('order_number', $invoiceNumber)->first();

        if (! $order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        $paidAmount = \App\Models\PaymentStore::where('order_id', $order->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // Fetch user manually
        $user = User::find($order->user_id);

        // Decode JSON array of tax IDs
        $taxIds = json_decode($order->tax_id ?? '[]', true);

        // Fetch multiple tax records
        // $taxes = TaxRate::whereIn('id', $taxIds)->where('branch_id', $selectedSubAdminId)->where('isDeleted', 0)->get();
        $taxes = TaxRate::whereIn('id', $taxIds)
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        // Fetch order items and related product info
        $items = OrderItem::where('order_id', $order->id)->get()->map(function ($item) {
            $product   = Product::find($item->product_id);
            $images    = json_decode($product->images ?? '[]', true);
            $imageUrls = $product->image_url ?? [];

            // ✅ STEP 1: Calculate already returned quantity
            $returnedQty = SalesReturnItem::where('order_item_id', $item->id)->sum('quantity');

            // ✅ STEP 2: Calculate remaining returnable qty
            $availableQty = max(0, $item->quantity - $returnedQty);
            return [
                'id'                  => $item->id,
                'product_id'          => $item->product_id, // ✅ Add this
                'product_name'        => $product->name ?? 'N/A',
                'product_image'       => $images[0] ?? null,
                'product_image_url'   => $imageUrls[0] ?? null,
                                                          // 'quantity'            => $item->quantity,
                'quantity'            => $availableQty,   // remaining qty only
                'sold_quantity'       => $item->quantity, // optional (for reference)
                'returned_quantity'   => $returnedQty,    // optional (for UI/debug)
                'price'               => $item->price,
                'discount_percentage'     =>$item->discount_percentage,
                'discount_amount'     =>$item->discount_amount,
                'product_gst_details' => $item->product_gst_details,
                'product_gst_total'   => $item->product_gst_total,
            ];
        })->filter(function ($item) {
            return $item['quantity'] > 0;
        })->values();

        $orderData = [
            'id'               => $order->id,
            'user_id'          => $order->user_id,
            'order_number'     => $order->order_number,
            'user_name'        => $user->name ?? 'N/A',
            'user_phone'       => $user->phone ?? '',
            'discount'         => $order->discount ?? 0,
            'shipping'         => $order->shipping ?? 0,
            'total_amount'     => $order->total_amount ?? 0,
            'remaining_amount' => $order->remaining_amount ?? 0,
            'paid_amount'      => ($order->total_amount ?? 0) - ($order->remaining_amount ?? 0),
        ];

        $settings         = \DB::table('settings')->where('branch_id', $selectedSubAdminId)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'order'             => $orderData,
            'items'             => $items,
            'taxes'             => $taxes,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
        ]);
    }

    // public function return_sale(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'products'                 => 'required|array|min:1',
    //         'products.*.order_item_id' => 'required|exists:order_items,id',
    //         'products.*.quantity'      => 'required|numeric|min:0',
    //         'products.*.price'         => 'required|numeric|min:0',
    //         'products.*.subtotal'      => 'required|numeric|min:0',
    //         'discount'                 => 'nullable|numeric|min:0|max:100',
    //         'grand_total'              => 'required|numeric|min:0',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation errors',
    //             'errors'  => $validator->errors(),
    //         ], 422);
    //     }

    //     DB::beginTransaction();
    //     try {
    //         $authUser  = Auth::guard('api')->user();
    //         $createdBy = $authUser->id;
    //         $subtotal = 0;
    //         $order_id = null;

    //         // Update existing order items and stock
    //         foreach ($request->products as $item) {
    //             $orderItem = OrderItem::find($item['order_item_id']);
    //             if (!$orderItem) continue;

    //             $order_id = $orderItem->order_id;
    //             $product = Product::find($orderItem->product_id);
    //             if ($product) {
    //                 $newQty = $item['quantity'];
    //                 $oldQty = $orderItem->quantity;
    //                 $difference = $newQty - $oldQty;
    //                 $oldProductQty = $product->quantity;

    //                 // Update stock: if newQty < oldQty (return), difference is negative, so increment stock
    //                 if ($difference > 0) {
    //                     $product->decrement('quantity', $difference);
    //                 } elseif ($difference < 0) {
    //                     $product->increment('quantity', abs($difference));
    //                 }

    //                 // Record inventory change
    //                 \App\Models\ProductInventory::create([
    //                     'product_id'    => $product->id,
    //                     'initial_stock' => $oldProductQty,
    //                     'current_stock' => $product->quantity,
    //                     'type'          => 'Sales Return',
    //                     'branch_id'     => $product->branch_id,
    //                     'create_by'     => $createdBy,
    //                     'date'          => now(),
    //                 ]);
    //             }

    //             // Update the order item instead of deleting
    //             $orderItem->update([
    //                 'quantity'            => $item['quantity'],
    //                 'total_amount'        => $item['subtotal'],
    //                 'product_gst_details' => $item['product_gst_details'] ?? null,
    //                 'product_gst_total'   => $item['product_gst_total'] ?? 0,
    //             ]);

    //             $subtotal += ($orderItem->price * $item['quantity']);
    //         }

    //         $discount       = $request->discount ?? 0;
    //         $discountAmount = $subtotal * ($discount / 100);

    //         $totalTax = 0;
    //         foreach ($request->products as $item) {
    //             $totalTax += $item['product_gst_total'] ?? 0;
    //         }

    //         $order = Order::findOrFail($order_id);

    //         // Calculate new remaining_amount properly
    //         $oldTotalAmount = $order->total_amount;
    //         $oldRemaining   = $order->remaining_amount;
    //         $paidAmount     = $oldTotalAmount - $oldRemaining;
    //         $grandTotal     = $request->grand_total;

    //         if ($grandTotal >= $paidAmount) {
    //             $newRemaining = $grandTotal - $paidAmount;
    //         } else {
    //             $newRemaining = 0;
    //             $refundAmount = $paidAmount - $grandTotal;

    //             // Record refund as a negative payment in PaymentStore if there's a surplus
    //             if ($refundAmount > 0) {
    //                 \App\Models\PaymentStore::create([
    //                     'user_id'          => $order->user_id,
    //                     'order_id'         => $order->id,
    //                     'payment_amount'   => -$refundAmount, // Negative for refund
    //                     'remaining_amount' => 0,
    //                     'payment_method'   => $order->payment_method ?? 'cash',
    //                     'payment_date'     => now(),
    //                     'payment_type'     => 'refund',
    //                     'status'           => 'debit',
    //                     'cash_amount'      => 0,
    //                     'upi_amount'       => 0,
    //                     'emi_month'        => 0,
    //                     'isDeleted'        => 0,
    //                     'branch_id'        => $order->branch_id,
    //                     'created_by'       => $createdBy,
    //                 ]);
    //             }
    //         }

    //         // Update order
    //         $order->update([
    //             'discount'         => $discount,
    //             'discount_amount'  => $discountAmount,
    //             'tax_amount'       => $totalTax,
    //             'subtotal'         => $subtotal,
    //             'total_amount'     => $grandTotal,
    //             'remaining_amount' => $newRemaining,
    //         ]);

    //         DB::commit();

    //         // Send WhatsApp message for return (after DB commit)
    //         // Store refund calculation values before entering try block to ensure they're accessible
    //         $originalOrderTotal = $oldTotalAmount; // Original total before return
    //         $newOrderTotal = $grandTotal; // New total after return (from request)

    //         try {
    //             // Get customer and branch info
    //             $customer = User::select('id', 'name', 'phone')->find($order->user_id);
    //             // Get branch_id from order or from first product
    //             $branchIdToUse = $order->branch_id;
    //             if (!$branchIdToUse && !empty($products)) {
    //                 $branchIdToUse = $products->first()->branch_id ?? null;
    //             }

    //             if ($customer && !empty($customer->phone) && $branchIdToUse) {
    //                 $phoneNumber = preg_replace('/[^0-9]/', '', $customer->phone);

    //                 if (!empty($phoneNumber)) {
    //                     $useForTemplate = 'Return order';
    //                     $templateName = null;
    //                     $templateParams = [];
    //                     $templateLanguage = 'en'; // Default language code

    //                     // Get template for return order
    //                     $template = WhatsAppMessageTemplate::select('name', 'components', 'language')
    //                         ->where('branch_id', $branchIdToUse)
    //                         ->where('use_for_template', $useForTemplate)
    //                         ->where('on_off', 'active')
    //                         ->where('isDeleted', 0)
    //                         ->first();

    //                     if ($template) {
    //                         // Validate template name exists and is not empty
    //                         if (empty($template->name)) {
    //                             Log::warning("WhatsApp template found but name is empty", [
    //                                 'branch_id' => $branchIdToUse,
    //                                 'use_for_template' => $useForTemplate,
    //                                 'template_id' => $template->id ?? null
    //                             ]);
    //                         } else {
    //                             $templateName = trim($template->name);
    //                             // Get language code from template, default to 'en' if not set
    //                             $templateLanguage = !empty($template->language) ? $template->language : 'en';

    //                             // Log template details for debugging
    //                             Log::info("WhatsApp template found for return order notification", [
    //                                 'branch_id' => $branchIdToUse,
    //                                 'use_for_template' => $useForTemplate,
    //                                 'template_name' => $templateName
    //                             ]);

    //                             // Only process components if template name is valid
    //                             if ($templateName) {
    //                                 $components = is_string($template->components) ? json_decode($template->components, true) : $template->components;

    //                                 if (is_array($components)) {
    //                                     foreach ($components as $component) {
    //                                         if (isset($component['type']) && $component['type'] === 'BODY' && isset($component['text'])) {
    //                                             preg_match_all('/\{\{(\d+)\}\}/', $component['text'], $matches);

    //                                             if (!empty($matches[1])) {
    //                                                 // Cache setting query
    //                                                 $setting = cache()->remember("setting_branch_{$branchIdToUse}", 300, function() use ($branchIdToUse) {
    //                                                     return Setting::select('name')->where('branch_id', $branchIdToUse)->first();
    //                                                 });

    //                                                 $customerName = $customer->name ?? 'Customer';
    //                                                 $orderNumber = $order->order_number ?? '';
    //                                                 $companyName = $setting->name ?? 'Company';

    //                                                 // Calculate refund amount: only if paid amount exceeds new total
    //                                                 $paidAmountBefore = $originalOrderTotal - $oldRemaining;
    //                                                 $refundAmountValue = max(0, $paidAmountBefore - $newOrderTotal);

    //                                                 // Ensure refund is positive and properly formatted
    //                                                 if ($refundAmountValue > 0) {
    //                                                     $refundAmount = number_format($refundAmountValue, 2);
    //                                                 } else {
    //                                                     // If somehow negative or zero, set to 0.00
    //                                                     $refundAmount = '0.00';
    //                                                     Log::info("No refund to customer for return order (payment was partial or matches)", [
    //                                                         'paid_amount_before' => $paidAmountBefore,
    //                                                         'new_total_after_return' => $newOrderTotal,
    //                                                         'order_id' => $order->id,
    //                                                         'order_number' => $orderNumber
    //                                                     ]);
    //                                                 }

    //                                                 Log::info("Return order refund calculation", [
    //                                                     'original_total_before_return' => $originalTotalBeforeReturn,
    //                                                     'new_total_after_return' => $newTotalAfterReturn,
    //                                                     'refund_amount' => $refundAmount,
    //                                                     'order_id' => $order->id,
    //                                                     'order_number' => $orderNumber
    //                                                 ]);

    //                                                 foreach ($matches[1] as $varNum) {
    //                                                     $templateParams[] = match((int)$varNum) {
    //                                                         1 => $customerName,           // {{1}}: Customer Name
    //                                                         2 => $companyName,            // {{2}}: Store / Company Name
    //                                                         3 => $orderNumber,            // {{3}}: Order ID
    //                                                         4 => $refundAmount,           // {{4}}: Refund Amount
    //                                                         default => ''
    //                                                     };
    //                                                 }
    //                                             }
    //                                             break;
    //                                         }
    //                                     }
    //                                 }
    //                             }
    //                         }
    //                     } else {
    //                         Log::warning("WhatsApp template not found for return order", [
    //                             'branch_id' => $branchIdToUse,
    //                             'use_for_template' => $useForTemplate
    //                         ]);
    //                     }

    //                     if ($templateName) {
    //                         // Send WhatsApp asynchronously (non-blocking)
    //                         WhatsAppService::sendTemplateMessage($branchIdToUse, $phoneNumber, $templateName, $templateParams, $templateLanguage);
    //                     }
    //                 }
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("WhatsApp message exception for return order", [
    //                 'order_id' => $order->id,
    //                 'error' => $e->getMessage()
    //             ]);
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Order returned successfully',
    //             'data'    => $order,
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to return sale',
    //             'error'   => $e->getMessage(),
    //         ], 500);
    //     }
    // }
 public function return_sale(Request $request)
{
    DB::beginTransaction();

    try {
        $user = Auth::guard('api')->user();

        // Get order
        $firstItem = OrderItem::findOrFail($request->products[0]['order_item_id']);
        $order     = Order::findOrFail($firstItem->order_id);

        // Get order-level discount from the order
        $orderDiscountPercent = $order->discount ?? 0;
        $shippingCharge = $order->shipping ?? 0;

        // 1️⃣ Create Sales Return (MASTER)
        $salesReturn = SalesReturn::create([
            'order_id'      => $order->id,
            'return_number' => 'SR-' . now()->timestamp,
            'discount'      => $orderDiscountPercent,
            'branch_id'     => $order->branch_id,
            'created_by'    => $user->id,
        ]);

        $subtotal = 0;
        $totalTax = 0;
        $totalProductDiscount = 0;

        // Track returned quantities to check if all items are fully returned
        $returnedQuantities = [];

        // 2️⃣ Loop return items
        foreach ($request->products as $row) {
            $orderItem = OrderItem::findOrFail($row['order_item_id']);
            $product   = Product::findOrFail($orderItem->product_id);

            // Get product-level discount from order item
            $productDiscountAmount = $orderItem->discount_amount ?? 0;
            $productDiscountPercent = $orderItem->discount_percentage ?? 0;

            // Calculate pro-rated discount based on returned quantity
            $originalOrderQty = $orderItem->quantity;
            $returnQty = $row['quantity'];
            $proRatedDiscountAmount = ($productDiscountAmount / $originalOrderQty) * $returnQty;
            $proRatedDiscountPercent = $productDiscountPercent;

            // Track returned quantities
            if (!isset($returnedQuantities[$orderItem->id])) {
                $returnedQuantities[$orderItem->id] = 0;
            }
            $returnedQuantities[$orderItem->id] += $returnQty;

            // Save return item
            SalesReturnItem::create([
                'sales_return_id'     => $salesReturn->id,
                'order_item_id'       => $orderItem->id,
                'product_id'          => $product->id,
                'quantity'            => $row['quantity'],
                'price'               => $row['price'],
                'subtotal'            => $row['subtotal'],
                'discount'            => $proRatedDiscountPercent,
                'discount_amount'     => $proRatedDiscountAmount,
                'product_gst_details' => $row['product_gst_details'] ?? null,
                'product_gst_total'   => $row['product_gst_total'] ?? 0,
            ]);

            // 3️⃣ Increase stock (items are being returned to inventory)
            $oldStock = $product->quantity;
            $product->increment('quantity', $row['quantity']);

            // 4️⃣ Inventory log
            ProductInventory::create([
                'product_id'    => $product->id,
                'initial_stock' => $oldStock,
                'current_stock' => $product->quantity,
                'type'          => 'Sales Return',
                'branch_id'     => $product->branch_id,
                'create_by'     => $user->id,
                'date'          => now(),
            ]);

            $subtotal += $row['subtotal'];
            $totalTax += $row['product_gst_total'] ?? 0;
            $totalProductDiscount += $proRatedDiscountAmount;
        }

        // 5️⃣ Check if ALL items are fully returned
        $allItemsFullyReturned = true;
        $orderItems = OrderItem::where('order_id', $order->id)->get();

        foreach ($orderItems as $orderItem) {
            $totalReturnedQty = $returnedQuantities[$orderItem->id] ?? 0;
            // Also check previous returns
            $previousReturns = SalesReturnItem::where('order_item_id', $orderItem->id)->sum('quantity');
            $totalReturnedQty += $previousReturns;

            if ($totalReturnedQty < $orderItem->quantity) {
                $allItemsFullyReturned = false;
                break;
            }
        }

        // 6️⃣ Calculate order-level discount on return
        $orderLevelDiscountAmount = ($subtotal * $orderDiscountPercent) / 100;

        // Total discount = product discounts + order level discount
        $totalDiscount = $totalProductDiscount + $orderLevelDiscountAmount;

        $afterDiscount = $subtotal - $totalDiscount;

        // Calculate shipping to return (only if all items are fully returned)
        $shippingToReturn = 0;
        if ($allItemsFullyReturned) {
            $shippingToReturn = $shippingCharge;
        }

        // Final return total = after discount + tax + shipping (if applicable)
        $returnTotal = $afterDiscount + $totalTax + $shippingToReturn;

        // Update sales return with totals
        $salesReturn->update([
            'subtotal'        => $subtotal,
            'discount_amount' => $totalDiscount,
            'tax_amount'      => $totalTax,
            'shipping'        => $shippingToReturn,
            'total_amount'    => $returnTotal,
        ]);

        // 7️⃣ UPDATE ORDER - Calculate total returns including this one
        $totalReturnSoFar = SalesReturn::where('order_id', $order->id)->sum('total_amount');

        $paidAmount = PaymentStore::where('order_id', $order->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $newOrderTotal = $order->total_amount - $totalReturnSoFar;

        // FINAL remaining amount
        $remainingAmount = max(0, $newOrderTotal - $paidAmount);

        $updateData = [
            'remaining_amount' => $remainingAmount,
        ];

        if ($remainingAmount <= 0) {
            $updateData['payment_status'] = 'completed';
        } elseif ($remainingAmount < $newOrderTotal) {
            $updateData['payment_status'] = 'partially';
        } else {
            $updateData['payment_status'] = 'pending';
        }

        // 8️⃣ UPDATE ORDER
        $order->update($updateData);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Sales return completed successfully',
            'data' => [
                'return_id' => $salesReturn->id,
                'return_number' => $salesReturn->return_number,
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $totalTax,
                'shipping' => $shippingToReturn,
                'total_amount' => $returnTotal,
                'all_items_fully_returned' => $allItemsFullyReturned
            ]
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
        ], 500);
    }
}

}
