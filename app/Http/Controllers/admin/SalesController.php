<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use App\Models\Category;
use App\Models\LabourItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentStore;
use App\Models\Sales_Labour_Items;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    public function sales_list(Request $request)
    {
        $user = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $years = Order::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $banks = BankMaster::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        return view('sales/saleslist', compact('years', 'banks'));
    }

    public function add_sales(Request $request)
    {

        return view('sales/add-sales');
    }
    public function edit_sales($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // 🔹 Load sales record - IMPORTANT: Load product_gst_details from order_items
        $sales = Order::with(['order_items' => function ($query) {
            $query->select(
                'id',
                'order_id',
                'product_id',
                'product_gst_details',
                'product_gst_total',
                'quantity',
                'price',
                'discount_percentage',
                'discount_amount',
                'total_amount'
            );
        }, 'order_items.product'])->find($id);

        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Sales record not found.');
        }

        $update_id = $id;

        // 🔹 Load related data
        $usernames = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0)
            ->get();

        $category = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $TaxRate = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $products = Product::where('status', 'active')
            ->where('availablility', 'in_stock')
            ->where('branch_id', $branchIdToUse)
            ->get();

        $setting = Setting::where('branch_id', $branchIdToUse)->first();
             $labourItems = LabourItem::where('isDeleted', false)
            ->get();

        return view('sales/edit-sales', compact('sales', 'TaxRate', 'category', 'usernames', 'products', 'update_id', 'setting', 'labourItems'));
    }

    public function sales_details($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        // 🔹 Get branch-specific setting
        $setting          = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        // 🔹 Load sales with order items + products
        $sales = Order::with(['order_items.product'])->find($id);

        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }
        $totalPaid = PaymentStore::where('order_id', $id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Final payable amount (IMPORTANT)
        $finalAmount =
            ($sales->total_amount ?? 0)
            + ($sales->total_gst ?? 0)
            - ($sales->discount_amount ?? 0);
        // dd($finalAmount);
        // ✅ Pending & Extra calculation
        $pendingAmount = max(0, $finalAmount - $totalPaid);
        $extraPaid     = max(0, $totalPaid - $finalAmount);
        // dd($pendingAmount);
        // dd($extraPaid);
        // ✅ Attach values for Blade
        $sales->final_amount   = $finalAmount;
        $sales->total_paid     = $totalPaid;
        $sales->pending_amount = $pendingAmount;
        $sales->extra_paid     = $extraPaid;

        // dd($totalPaid);
        // dd($extraPaid);

        // 🔹 Company info (branch-specific setting)
        $compenyinfo = $setting;

        // 🔹 Get taxes (safely handle null/empty tax_id)
        // $taxIds = ! empty($sales->tax_id) ? json_decode($sales->tax_id, true) : [];

        // $taxes  = ! empty($taxIds)
        //     ? TaxRate::where('branch_id', $branchIdToUse)
        //     ->whereIn('id', $taxIds)
        //     ->where('isDeleted', 0)
        //     ->get()
        //     : collect();

        // 🔹 Order items & totals
        $orderItems  = OrderItem::where('order_id', $id)->get();
        $totalAmount = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });


        $view_id = $id; // define view_id for blade
        $user    = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;
        $userAddress = $user && $user->userDetail ? $user->userDetail->address : null;

        if ($user) {
            $sales->customer_role       = ucfirst($user->role ?? 'Customer');
            $sales->customer_name       = $user->name ?? 'Walk-in Customer';
            $sales->customer_email      = $user->email ?? '';
            $sales->customer_phone      = $user->phone ?? '';
            $sales->customer_address    = optional($user->userDetail)->address ?? '';
            $sales->customer_city       = optional($user->userDetail)->city ?? '';
            $sales->customer_country    = optional($user->userDetail)->country ?? '';
            $sales->customer_gst_number = $user->gst_number ?? '';
            $sales->customer_pan_number = $user->pan_number ?? '';
        } else {
            // default values if no user is linked
            $sales->customer_role       = 'Customer';
            $sales->customer_name       = 'Walk-in Customer';
            $sales->customer_email      = '';
            $sales->customer_phone      = '';
            $sales->customer_address    = '';
            $sales->customer_city       = '';
            $sales->customer_country    = '';
            $sales->customer_gst_number = '';
            $sales->customer_pan_number = '';
        }

        // ✅ Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();

        // ✅ Check if return already started for this order
        $hasReturnStarted = SalesReturn::where('order_id', $view_id)->exists();

        return view('sales.sales-details', compact(
            'view_id',
            'sales',
            'totalAmount',
            'compenyinfo',
            'setting',
            'userAddress',
            'orderItems',
            'currencySymbol',
            'currencyPosition',
            'hasPaymentStarted',
            'hasReturnStarted'
        ));
    }

    public function salse_invoice($id)
    {
        $user       = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $user->id;
        }
        $view_id = $id;
        $sales   = Order::find($view_id);
        $setting = Setting::where('branch_id', $branchIdToUse)->first(); // Get currency info
        // dd($setting);
        if (! $sales) {
            return redirect()->route('sales.list')->with('error', 'Order not found.');
        }
        $totalPaid = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $extraPaid = max(0, $totalPaid - ($sales->total_amount ?? 0));

        $sales->total_paid = $totalPaid;
        $sales->extra_paid = $extraPaid;
        // $taxIds      = json_decode($sales->tax_id, true);
        // $taxes       = TaxRate::where('branch_id', $branchIdToUse)->whereIn('id', $taxIds)->where('isDeleted', 0)->get();
        $orderItems  = OrderItem::where('order_id', $view_id)->get();
        $totalAmount = $orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

        $userAddress = $user && $user->userDetail ? $user->userDetail->address : null;

        // ✅ Check if payment already started for this order
        $hasPaymentStarted = PaymentStore::where('order_id', $view_id)
            ->where('isDeleted', 0)
            ->exists();

        // ✅ Check if return already started for this order
        $hasReturnStarted = SalesReturn::where('order_id', $view_id)->exists();

        // dd($view_id,  $sales,  $totalAmount, $setting, $userAddress);
        // dd($orderItems);
        return view('sales/salse-invoice', compact('view_id', 'sales', 'totalAmount', 'setting', 'userAddress', 'orderItems', 'hasPaymentStarted', 'hasReturnStarted'));
    }

    // public function salse_invoice_pdf($id)
    // {
    //     $view_id    = $id;
    //     $sales      = Order::find($view_id);
    //     $user       = Auth::user();
    //     $subAdminId = session('selectedSubAdminId') ?? $user->id;

    //     if ($user->role === 'staff' && $user->branch_id) {
    //         $setting = Setting::where('branch_id', $user->branch_id)->first();
    //     } else {
    //         $setting = Setting::where('branch_id', $subAdminId)->first();
    //     }

    //     if (! $sales) {
    //         return redirect()->route('sales.list')->with('error', 'Order not found.');
    //     }
    //     $labourItems = Sales_Labour_Items::where('order_id', $id)
    //         ->with('labourItem')
    //         ->get();
    //         // dd($labourItems);
    //     $labourCost = 0;
    //     if ($labourItems && $labourItems->isNotEmpty()) {
    //         foreach ($labourItems as $labourItem) {
    //             $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
    //         }
    //     }

    //     // Fetch user data (assuming 'user_id' in orders table)
    //     $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

    //     // Helper function for currency formatting
    //     $formatCurrency = function ($amount) use ($setting) {
    //         $amount = number_format($amount, 2);
    //         return $setting->currency_position === 'right'
    //             ? $amount . $setting->currency_symbol
    //             : $setting->currency_symbol . $amount;
    //     };

    //     // ✅ Subtotal (Amount before GST)
    //     $orderItems = OrderItem::where('order_id', $view_id)->get();
    //     $subtotal   = $orderItems->sum(function ($item) {
    //         return $item->price * $item->quantity;
    //     });

    //     // dd($orderItems);

    //     // ✅ Discount
    //     $discountPercent = $sales->discount ?? 0;
    //     $discountAmount  = ($discountPercent / 100) * $subtotal;
    //     $afterDiscount   = $subtotal - $discountAmount;

    //     // ✅ Tax calculation only if gst_option = 'with'
    //     // $taxDetails = [];
    //     // if ($sales->gst_option === 'with_gst') {
    //     //     $taxIds = json_decode($sales->tax_id, true) ?? [];
    //     //     if (! empty($taxIds)) {
    //     //         $taxes = TaxRate::whereIn('id', $taxIds)->get();
    //     //         foreach ($taxes as $tax) {
    //     //             $taxAmount    = ($tax->tax_rate / 100) * $afterDiscount;
    //     //             $taxDetails[] = [
    //     //                 'name'             => $tax->tax_name,
    //     //                 'rate'             => $tax->tax_rate,
    //     //                 'amount'           => $taxAmount,
    //     //                 'formatted_amount' => $formatCurrency($taxAmount),
    //     //             ];
    //     //         }
    //     //     }
    //     // }
    //     $totalGstAmount = 0;
    //     $taxSummary = [];

    //     foreach ($orderItems as $item) {
    //         $totalGstAmount += (float) ($item->product_gst_total ?? 0);
    //         $gstDetails = $item->product_gst_details;

    //         // Handle legacy/double-encoded JSON payloads.
    //         if (is_string($gstDetails)) {
    //             $gstDetails = json_decode($gstDetails, true);
    //             if (is_string($gstDetails)) {
    //                 $gstDetails = json_decode($gstDetails, true);
    //             }
    //         }

    //         if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
    //             $gstDetails = [$gstDetails];
    //         }

    //         if (!empty($gstDetails) && is_array($gstDetails)) {
    //             foreach ($gstDetails as $tax) {
    //                 if (!is_array($tax)) {
    //                     continue;
    //                 }

    //                 $taxName = $tax['tax_name'] ?? 'GST';
    //                 $taxRate = $tax['tax_rate'] ?? 0;
    //                 $taxAmount = (float) ($tax['tax_amount'] ?? 0);
    //                 $key = $taxName . '_' . $taxRate;

    //                 if (!isset($taxSummary[$key])) {
    //                     $taxSummary[$key] = [
    //                         'name'   => $taxName,
    //                         'rate'   => $taxRate,
    //                         'amount' => 0,
    //                     ];
    //                 }

    //                 $taxSummary[$key]['amount'] += $taxAmount;
    //             }
    //         }
    //     }

    //     // format GST summary
    //     $taxDetails = [];
    //     foreach ($taxSummary as $tax) {
    //         $taxDetails[] = [
    //             'name'             => $tax['name'],
    //             'rate'             => $tax['rate'],
    //             'amount'           => $tax['amount'],
    //             'formatted_amount' => $formatCurrency($tax['amount']),
    //         ];
    //     }

    //     // final total
    //     $finalTotal = $afterDiscount + $totalGstAmount;


    //     // ✅ Final total
    //     $finalTotal = $afterDiscount + collect($taxDetails)->sum('amount');

    //     // ✅ Prepare formatted values
    //     $formattedSubtotal       = $formatCurrency($subtotal);
    //     $formattedDiscountAmount = $formatCurrency($discountAmount);
    //     $formattedAfterDiscount  = $formatCurrency($afterDiscount);
    //     $formattedFinalTotal     = $formatCurrency($finalTotal);

    //     // ✅ Retrieve customer data
    //     $customer = $user ? [
    //         'name'       => $user->name ?? 'walk-in-customer',
    //         'email'      => $user->email ?? '',
    //         'phone'      => $user->phone ?? '',
    //         'address'    => optional($user->userDetail)->address ?? '',
    //         'gst_number' => $user->gst_number ?? '',
    //         'pan_number' => $user->pan_number ?? '',
    //     ] : [
    //         'name'       => 'walk-in-customer',
    //         'email'      => '',
    //         'phone'      => '',
    //         'address'    => '',
    //         'gst_number' => '',
    //         'pan_number' => '',
    //     ];

    //     $paidAmount = PaymentStore::where('order_id', $sales->id)
    //         ->where('isDeleted', 0)
    //         ->sum('payment_amount');

    //     // ✅ Fetch returns
    //     $returns = \App\Models\SalesReturn::with('items.product')
    //         ->where('order_id', $view_id)
    //         ->get();

    //     // ✅ Pending amount (single source of truth)
    //     $pendingAmount = $sales->remaining_amount ?? 0;

    //     // ✅ Extra Paid calculation
    //         $totalOrderAmount = $sales->total_amount ?? $finalTotal; // fallback safety
    //         $extraPaid = max(0, $paidAmount - $totalOrderAmount);

    //     // ✅ Prepare data for view
    //     $pdfData = [
    //         'view_id'                => $view_id,
    //         'sales'                  => $sales,
    //         'setting'                => $setting,
    //         'orderItems'             => $orderItems,
    //         'salesItems'             => $orderItems,
    //         'labourItems'            => $labourItems,
    //         'returns'                => $returns,
    //         'taxDetails1'            => $taxDetails,
    //         'totalGst'      => $formatCurrency($totalGstAmount),
    //         'finalTotal'    => $formatCurrency($finalTotal),
    //         'customer'               => [
    //             'name'    => $user->name ?? 'walk-in-customer',
    //             'email'   => $user->email ?? '',
    //             'phone'   => $user->phone ?? '',
    //             'pan_number'   => $user->pan_number ?? '',
    //             'gst_number'   => $user->gst_number ?? '',
    //             'address' => optional($user->userDetail)->address ?? 'arga',
    //         ],
    //         'user'                   => $user ? $user->toArray() : null,
    //         'subtotal'               => $formattedSubtotal,
    //         'discountPercent'        => $discountPercent,
    //         'discountAmount'         => $formattedDiscountAmount,
    //         'afterDiscount'          => (float) $afterDiscount,  // numeric
    //         'formattedAfterDiscount' => $formattedAfterDiscount, // formatted
    //         'finalTotal'             => $formattedFinalTotal,
    //         'taxDetails1'            => $taxDetails,
    //         'paidAmount'             => $paidAmount,
    //         'pendingAmount'          => $pendingAmount,
    //         'extraPaid'              => $extraPaid,
    //     ];
    //     // dd($pdfData);

    //     // ✅ Load and render PDF
    //     // $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData);

    //     // return $pdf->stream('invoice_' . $view_id . '.pdf');
    //     // ===== Invoice size condition =====
    //         if ($setting && $setting->invoice_size === 'small') {

    //             $pdf = PDF::loadView('sales.salse-invoice-small-pdf', $pdfData)
    //                     ->setPaper('A5', 'portrait');

    //         } else {

    //             $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData)
    //                     ->setPaper('A4', 'portrait');
    //         }
    //         // ==================================

    //     return $pdf->stream('invoice_' . $view_id . '.pdf');
    // }
    public function salse_invoice_pdf($id)
{
    $view_id    = $id;
    $sales      = Order::find($view_id);
    $user       = Auth::user();
    $subAdminId = session('selectedSubAdminId') ?? $user->id;

    if ($user->role === 'staff' && $user->branch_id) {
        $setting = Setting::where('branch_id', $user->branch_id)->first();
    } else {
        $setting = Setting::where('branch_id', $subAdminId)->first();
    }

    if (! $sales) {
        return redirect()->route('sales.list')->with('error', 'Order not found.');
    }

    $labourItems = Sales_Labour_Items::where('order_id', $id)
        ->with('labourItem')
        ->get();

    $labourCost = 0;
    if ($labourItems && $labourItems->isNotEmpty()) {
        foreach ($labourItems as $labourItem) {
            $labourCost += ($labourItem->qty ?? 0) * ($labourItem->price ?? 0);
        }
    }

    // Fetch user data
    $user = $sales->user_id ? User::with('userDetail')->find($sales->user_id) : null;

    // Helper function for currency formatting
    $formatCurrency = function ($amount) use ($setting) {
        $amount = number_format($amount, 2);
        return $setting->currency_position === 'right'
            ? $amount . $setting->currency_symbol
            : $setting->currency_symbol . $amount;
    };

    // Subtotal
    $orderItems = OrderItem::where('order_id', $view_id)->get();
    $subtotal   = $orderItems->sum(function ($item) {
        return $item->price * $item->quantity;
    });

    // Discount
    $discountPercent = $sales->discount ?? 0;
    $discountAmount  = ($discountPercent / 100) * $subtotal;
    $afterDiscount   = $subtotal - $discountAmount;

    // Calculate GST
    $totalGstAmount = 0;
    $taxSummary = [];

    foreach ($orderItems as $item) {
        $totalGstAmount += (float) ($item->product_gst_total ?? 0);
        $gstDetails = $item->product_gst_details;

        if (is_string($gstDetails)) {
            $gstDetails = json_decode($gstDetails, true);
            if (is_string($gstDetails)) {
                $gstDetails = json_decode($gstDetails, true);
            }
        }

        if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
            $gstDetails = [$gstDetails];
        }

        if (!empty($gstDetails) && is_array($gstDetails)) {
            foreach ($gstDetails as $tax) {
                if (!is_array($tax)) {
                    continue;
                }

                $taxName = $tax['tax_name'] ?? 'GST';
                $taxRate = $tax['tax_rate'] ?? 0;
                $taxAmount = (float) ($tax['tax_amount'] ?? 0);
                $key = $taxName . '_' . $taxRate;

                if (!isset($taxSummary[$key])) {
                    $taxSummary[$key] = [
                        'name'   => $taxName,
                        'rate'   => $taxRate,
                        'amount' => 0,
                    ];
                }

                $taxSummary[$key]['amount'] += $taxAmount;
            }
        }
    }

    // Format GST summary
    $taxDetails = [];
    foreach ($taxSummary as $tax) {
        $taxDetails[] = [
            'name'             => $tax['name'],
            'rate'             => $tax['rate'],
            'amount'           => $tax['amount'],
            'formatted_amount' => $formatCurrency($tax['amount']),
        ];
    }

    // Calculate Return Amount
    $totalReturnAmount = 0;
    $returns = \App\Models\SalesReturn::with('items.product')
        ->where('order_id', $view_id)
        ->get();

    $allItemsFullyReturned = false;

    if ($returns->isNotEmpty()) {
        foreach ($returns as $ret) {
            $totalReturnAmount += (float) ($ret->total_amount ?? 0);
        }

        // Check if all items are fully returned
        $orderItemsQuantities = [];
        foreach ($orderItems as $item) {
            $orderItemsQuantities[$item->id] = $item->quantity;
        }

        $returnedQuantities = [];
        foreach ($returns as $ret) {
            foreach ($ret->items as $retItem) {
                if (!isset($returnedQuantities[$retItem->order_item_id])) {
                    $returnedQuantities[$retItem->order_item_id] = 0;
                }
                $returnedQuantities[$retItem->order_item_id] += $retItem->quantity;
            }
        }

        $allItemsFullyReturned = true;
        foreach ($orderItemsQuantities as $orderItemId => $originalQty) {
            $returnedQty = $returnedQuantities[$orderItemId] ?? 0;
            if ($returnedQty < $originalQty) {
                $allItemsFullyReturned = false;
                break;
            }
        }
    }

    // Get shipping charge
    $shippingCharge = (float) ($sales->shipping ?? 0);

    // Calculate return amount with shipping if fully returned
    $totalReturnWithShipping = $totalReturnAmount;
    if ($allItemsFullyReturned && $totalReturnAmount > 0) {
        $totalReturnWithShipping = $totalReturnAmount + $shippingCharge;
    }

    // Final total
    $finalTotal = $afterDiscount + $totalGstAmount + $shippingCharge + $labourCost;

    // Calculate pending amount after returns
    $paidAmount = PaymentStore::where('order_id', $sales->id)
        ->where('isDeleted', 0)
        ->sum('payment_amount');

    // Pending amount = Final Total - Total Returns - Paid Amount
    $pendingAmount = max(0, $finalTotal - $totalReturnWithShipping - $paidAmount);

    // Extra Paid calculation
    $extraPaid = max(0, $paidAmount - ($finalTotal - $totalReturnWithShipping));

    // Prepare formatted values
    $formattedSubtotal       = $formatCurrency($subtotal);
    $formattedDiscountAmount = $formatCurrency($discountAmount);
    $formattedAfterDiscount  = $formatCurrency($afterDiscount);
    $formattedTotalGstAmount = $formatCurrency($totalGstAmount);
    $formattedShippingCharge = $formatCurrency($shippingCharge);
    $formattedLabourCost     = $formatCurrency($labourCost);
    $formattedFinalTotal     = $formatCurrency($finalTotal);
    $formattedReturnAmount   = $formatCurrency($totalReturnWithShipping);
    $formattedPaidAmount     = $formatCurrency($paidAmount);
    $formattedPendingAmount  = $formatCurrency($pendingAmount);
    $formattedExtraPaid      = $formatCurrency($extraPaid);

    // Determine return status
    $returnStatus = 'No return';
    $returnStatusColor = '#28c76f';
    if ($totalReturnAmount > 0) {
        if ($totalReturnWithShipping >= $finalTotal) {
            $returnStatus = 'Fully Returned';
            $returnStatusColor = '#ea5455';
        } else {
            $returnStatus = 'Partially Returned';
            $returnStatusColor = '#ff9f43';
        }
    }

    // Prepare customer data
    $customer = $user ? [
        'name'       => $user->name ?? 'walk-in-customer',
        'email'      => $user->email ?? '',
        'phone'      => $user->phone ?? '',
        'address'    => optional($user->userDetail)->address ?? '',
        'gst_number' => $user->gst_number ?? '',
        'pan_number' => $user->pan_number ?? '',
    ] : [
        'name'       => 'walk-in-customer',
        'email'      => '',
        'phone'      => '',
        'address'    => '',
        'gst_number' => '',
        'pan_number' => '',
    ];

    // Prepare data for view
    $pdfData = [
        'view_id'                => $view_id,
        'sales'                  => $sales,
        'setting'                => $setting,
        'orderItems'             => $orderItems,
        'salesItems'             => $orderItems,
        'labourItems'            => $labourItems,
        'returns'                => $returns,
        'taxDetails1'            => $taxDetails,
        'totalGst'               => $formattedTotalGstAmount,
        'finalTotal'             => $formattedFinalTotal,
        'subtotal'               => $formattedSubtotal,
        'discountPercent'        => $discountPercent,
        'discountAmount'         => $formattedDiscountAmount,
        'afterDiscount'          => (float) $afterDiscount,
        'formattedAfterDiscount' => $formattedAfterDiscount,
        'shippingCharge'         => $formattedShippingCharge,
        'labourCost'             => $formattedLabourCost,
        'returnAmount'           => $formattedReturnAmount,
        'returnStatus'           => $returnStatus,
        'returnStatusColor'      => $returnStatusColor,
        'totalReturnAmount'      => $totalReturnWithShipping,
        'allItemsFullyReturned'  => $allItemsFullyReturned,
        'customer'               => $customer,
        'user'                   => $user ? $user->toArray() : null,
        'paidAmount'             => $formattedPaidAmount,
        'pendingAmount'          => $formattedPendingAmount,
        'extraPaid'              => $formattedExtraPaid,
        'pendingAmountNumeric'   => $pendingAmount,
    ];

    // Load and render PDF
    if ($setting && $setting->invoice_size === 'small') {
        $pdf = PDF::loadView('sales.salse-invoice-small-pdf', $pdfData)
                ->setPaper('A5', 'portrait');
    } else {
        $pdf = PDF::loadView('sales.salse-invoice-pdf', $pdfData)
                ->setPaper('A4', 'portrait');
    }

    return $pdf->stream('invoice_' . $view_id . '.pdf');
}


    public function sales_report(Request $request)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');

        // Decide branch based on role
        if ($userRole === 'sub-admin') {
            $branchIdToUse = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchIdToUse = $UserBranchId;
        } else {
            $branchIdToUse = $branchId;
        }

        // Fetch customer
        if ($userRole === 'staff') {
            // Only customer created by this staff
            $customers = User::where('role', 'customer')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        } else {
            // Admin / sub-admin sees all customer in branch
            $customers = User::where('role', 'customer')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        }

        // ✅ Fetch categories based on branch
        if ($userRole === 'staff') {
            $categories = Category::where('isDeleted', 0)
                ->where('branch_id', $UserBranchId)
                ->orderBy('name')
                ->get();
        } else {
            $categories = Category::where('isDeleted', 0)
                ->where('branch_id', $branchIdToUse)
                ->orderBy('name')
                ->get();
        }

        return view('sales/salesreport', compact('customers', 'categories'));
    }

    public function pos()
    {
        $user       = auth()->user();
        $userRole   = $user->role ?? '';
        $userId     = $user->id ?? null;
        $branchId   = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide which branch_id to use
        if ($userRole === 'sub-admin' && $userId) {
            $branchIdToUse = $userId;
        } elseif ($userRole === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } elseif ($userRole === 'staff' && $branchId) {
            $branchIdToUse = $branchId;
        } else {
            $branchIdToUse = $userId;
        }
        // dd($branchIdToUse);
        // 🔹 Get settings
        $setting           = Setting::where('branch_id', $branchIdToUse)->first();
        $currency_symbol   = $setting->currency_symbol ?? '₹';
        $currency_position = $setting->currency_position ?? 'left';

        // 🔹 Common queries
        $categories = Category::where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        $taxRates = TaxRate::where('status', 'active')->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();

        // 🔹 Banks
        $banks = BankMaster::where('branch_id', $branchIdToUse)
            ->where('status', 1)
            ->where('isDeleted', 0)
            ->get();

        // $customers = User::where('role', 'customer')
        //     ->where('branch_id', $branchIdToUse)
        //     ->where('isDeleted', 0)
        //     ->get();
        // 🔹 Customers: filter by created_by if staff
        $customersQuery = User::where('role', 'customer')
            ->where('branch_id', $branchIdToUse)
            ->where('isDeleted', 0);

        if (strtolower($userRole) === 'staff') {
            $customersQuery->where('branch_id', $branchIdToUse);
        }

        $customers = $customersQuery->get();
// dd($customers);
        // 🔹 Vendors only for Staff or default case
        $vendors = collect(); // empty collection if not needed
        if ($userRole === 'staff' || $userRole === 'admin' || $userRole === 'sub-admin') {
            $vendors = User::where('role', 'vendor')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->get();
        }

        return view('sales.pos', compact(
            'categories',
            'taxRates',
            'customers',
            'vendors',
            'currency_symbol',
            'currency_position',
            'setting',
            'banks'
        ));
    }

    public function sale_report($ids)
    {
        $authUser   = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
            ->whereIn('id', $idsArray)
            ->get();

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // Get settings
        $settings         = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Process each sale
        $totalAmount    = 0;
        $discountAmount = 0;
        $taxDetails     = [];
        // 🔹 GST / Tax Calculation
        // $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                $rowTaxRates = TaxRate::where('status', 'active')
                    ->where('branch_id', $branchIdToUse)
                    ->where('isDeleted', 0)
                    ->whereIn('id', $rowTaxIdsArray)
                    ->get();
            }

            $unitPrice = $sale->price;
            // Apply discount per unit
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $unitPrice -= ($unitPrice * $discountPercent) / 100;
            }

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount × qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            // $totalAmount += $rowTaxAmount;
            $totalAmount = $sales->sum('rowFinalTotal');
        }

        // Customer info (from first sale)
        $customer    = $sales->first()->user ?? null;
        $userDetails = $customer ? $customer->userDetail : null;

        return view('sales.sale_report', compact(
            'sales',
            'settings',
            'discountAmount',
            'totalAmount',
            'taxDetails',
            'currencySymbol',
            'currencyPosition',
            'customer',
            'userDetails',
            'ids'
        ));
    }

    public function export_sales_report_pdf($ids)
    {
        $authUser   = auth()->user();
        $subAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }

        $idsArray = explode(',', $ids);

        // 🔹 Eager load related models
        $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
            ->whereIn('id', $idsArray)
            ->get();

        if ($sales->isEmpty()) {
            return redirect()->route('sales.index')->with('error', 'No sales data found.');
        }

        // 🔹 Get settings
        $setting          = Setting::where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $setting->currency_symbol ?? '₹';
        $currencyPosition = $setting->currency_position ?? 'left';

        $subtotal = $sales->sum('total_amount');

        // 🔹 Discount calculation
        $discountAmount = 0;
        foreach ($sales as $sale) {
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $discountAmount += ($sale->total_amount * $discountPercent) / 100;
            }
        }

        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 🔹 GST / Tax Calculation
        $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($sales as $sale) {
            $rowTaxes     = [];
            $rowTaxAmount = 0;

            $rowGSTOption = $sale->invoice->gst_option ?? 'without_gst';
            $rowTaxIds    = $sale->invoice->tax_id ?? '[]';

            // Decode JSON tax array
            $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

            $rowTaxRates = collect();
            if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                $rowTaxRates = TaxRate::where('status', 'active')
                    ->where('branch_id', $branchIdToUse)
                    ->where('isDeleted', 0)
                    ->whereIn('id', $rowTaxIdsArray)
                    ->get();
            }

            $unitPrice = $sale->price;
            // Apply discount per unit
            if ($sale->invoice && $sale->invoice->discount) {
                $discountPercent = $sale->invoice->discount;
                $unitPrice -= ($unitPrice * $discountPercent) / 100;
            }

            // Calculate row taxes
            foreach ($rowTaxRates as $tax) {
                $taxBase = $unitPrice * $sale->quantity;
                $amount  = $taxBase * ($tax->tax_rate / 100);

                $rowTaxes[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];

                $rowTaxAmount += $amount;

                // accumulate overall tax totals
                if (! isset($taxDetails[$tax->id])) {
                    $taxDetails[$tax->id] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => 0,
                    ];
                }
                $taxDetails[$tax->id]['amount'] += $amount;
            }

            // Attach to sale row
            $sale->rowGSTOption = $rowGSTOption;
            $sale->rowTaxes     = $rowTaxes;
            $sale->rowTaxAmount = $rowTaxAmount;

            // Final total per row = (unit price after discount × qty) + row taxes
            $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;

            $totalTaxAmount += $rowTaxAmount;
        }

        // 🔹 Total after discount + taxes
        // Total after discount + taxes
        $totalAmount = $sales->sum('rowFinalTotal');

        $pdfData = [
            'sales'            => $sales,
            'setting'          => $setting,
            'currencySymbol'   => $currencySymbol,
            'currencyPosition' => $currencyPosition,
            'subtotal'         => $subtotal,
            'discountAmount'   => $discountAmount,
            // 'afterDiscount'    => $afterDiscount,
            'taxDetails'       => $taxDetails,
            'totalTaxAmount'   => $totalTaxAmount,
            'totalAmount'      => $totalAmount,
            // 'ids' => $ids
        ];

        // Load PDF
        $pdf = PDF::loadView('sales.sales-invoice-report-pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download('sales_report.pdf');
    }

    public function show_sales_report_page(Request $request)
    {
        try {
            // 🔹 Get inputs directly from the request
            $ids      = $request->input('ids');
            $branchId = $request->input('branch');

            // 🔹 Validate required parameters
            if (empty($ids)) {
                abort(404, 'No sales selected.');
            }
            if (empty($branchId)) {
                abort(404, 'Branch ID is missing.');
            }

            // 🔹 Convert to array if comma-separated
            $idsArray = explode(',', $ids);

            // 🔹 Fetch sales data with relationships
            $sales = OrderItem::with('product.category', 'invoice', 'user.userDetail')
                ->whereIn('id', $idsArray)
                ->get();

            if ($sales->isEmpty()) {
                abort(404, 'No sales found.');
            }

            // 🔹 Fetch settings for branch
            $settings         = Setting::where('branch_id', $branchId)->first();
            $currencySymbol   = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            // 🔹 Initialize totals
            $taxDetails     = [];
            $totalAmount    = 0;
            $discountAmount = 0;

            foreach ($sales as $sale) {
                $rowTaxes     = [];
                $rowTaxAmount = 0;

                $rowGSTOption   = $sale->invoice->gst_option ?? 'without_gst';
                $rowTaxIds      = $sale->invoice->tax_id ?? '[]';
                $rowTaxIdsArray = json_decode($rowTaxIds, true) ?: [];

                // 🔹 Fetch applicable tax rates
                $rowTaxRates = collect();
                if ($rowGSTOption === 'with_gst' && ! empty($rowTaxIdsArray)) {
                    $rowTaxRates = TaxRate::where('status', 'active')
                        ->where('branch_id', $branchId)
                        ->where('isDeleted', 0)
                        ->whereIn('id', $rowTaxIdsArray)
                        ->get();
                }

                // 🔹 Apply discount (if any)
                $unitPrice = $sale->price;
                if ($sale->invoice && $sale->invoice->discount) {
                    $discountPercent = $sale->invoice->discount;
                    $discountPerUnit = ($unitPrice * $discountPercent) / 100;
                    $unitPrice -= $discountPerUnit;
                    $discountAmount += $discountPerUnit * $sale->quantity;
                }

                // 🔹 Calculate taxes per item
                foreach ($rowTaxRates as $tax) {
                    $taxBase = $unitPrice * $sale->quantity;
                    $amount  = $taxBase * ($tax->tax_rate / 100);

                    $rowTaxes[] = [
                        'name'   => $tax->tax_name,
                        'rate'   => $tax->tax_rate,
                        'amount' => $amount,
                    ];

                    $rowTaxAmount += $amount;

                    // Accumulate total tax details
                    if (! isset($taxDetails[$tax->id])) {
                        $taxDetails[$tax->id] = [
                            'name'   => $tax->tax_name,
                            'rate'   => $tax->tax_rate,
                            'amount' => 0,
                        ];
                    }
                    $taxDetails[$tax->id]['amount'] += $amount;
                }

                // 🔹 Attach row-level summary
                $sale->rowGSTOption = $rowGSTOption;
                $sale->rowTaxes     = $rowTaxes;
                $sale->rowTaxAmount = $rowTaxAmount;

                // 🔹 Final total per item
                $sale->rowFinalTotal = ($unitPrice * $sale->quantity) + $rowTaxAmount;
            }

            // 🔹 Calculate final grand total
            $totalAmount = $sales->sum('rowFinalTotal');

            // 🔹 Customer info (first sale user)
            $customer    = $sales->first()->user ?? null;
            $userDetails = $customer ? $customer->userDetail : null;

            // 🔹 Prepare data for Blade view
            $data = [
                'sales'            => $sales,
                'settings'         => $settings,
                'discountAmount'   => $discountAmount,
                'totalAmount'      => $totalAmount,
                'taxDetails'       => $taxDetails,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
                'customer'         => $customer,
                'userDetails'      => $userDetails,
                'ids'              => $ids,
            ];

            // ✅ Return view without requiring authentication
            return view('sales.web_sale_report', $data);
        } catch (\Throwable $e) {
            abort(500, 'Error loading sales report: ' . $e->getMessage());
        }
    }

}
