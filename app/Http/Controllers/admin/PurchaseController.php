<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
// use PDF;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\RowMaterial;
use App\Models\PurchaseInvoice;
use App\Models\Purchases;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;

class PurchaseController extends Controller
{
    private function getPurchaseFormData(int $branch_id): array
    {
        $vendorsQuery = User::where('role', 'vendor')
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0);

        $vendors = $vendorsQuery->get();
        $categories = Category::where('branch_id', $branch_id)->where('isDeleted', 0)->get();
        $taxes = TaxRate::where('branch_id', $branch_id)->where('isDeleted', 0)->where('status', 'active')->get();
        $banks = \App\Models\BankMaster::where('branch_id', $branch_id)->where('isDeleted', 0)->where('status', 1)->get();
        $products = Product::where('isDeleted', 0)
            ->where('status', 'active')
            ->get();
        $rowMaterials = RowMaterial::where('isDeleted', 0)
            ->where('status', 'active')
            ->where('branch_id', $branch_id)
            ->get();

        $productsArray = $products->map(function ($product) {
            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'category_id' => $product->category_id,
                'price'       => $product->price,
                'product_gst' => $product->product_gst,
                'gst_option'  => $product->gst_option,
            ];
        });

        $rowMaterialsArray = $rowMaterials->map(function ($material) {
            return [
                'id'          => $material->id,
                'name'        => $material->row_materialname,
                'category_id' => $material->category_id,
                'price'       => $material->price,
                'quantity'    => $material->quantity,
                'product_gst' => null,
                'gst_option'  => 'without_gst',
            ];
        });

        $settings = DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return compact(
            'vendors',
            'categories',
            'taxes',
            'products',
            'settings',
            'currencySymbol',
            'currencyPosition',
            'productsArray',
            'rowMaterialsArray',
            'banks'
        );
    }

    public function purchase_list(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $years = Purchases::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $banks = \App\Models\BankMaster::where('branch_id', $branch_id)->where('isDeleted', 0)->where('status', 1)->get();

        return view('purchase/purchaselist', compact('years', 'banks'));
    }

    public function add_purchase(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        // If a sub-admin is selected by admin, use that branch_id
        // if (!empty($selectedSubAdminId))
        // {
        //     $branch_id = $selectedSubAdminId;
        // }

        // $vendors = User::where('role', 'vendor')
        //     ->where('branch_id', $branch_id)
        //     ->where('isDeleted', 0) // Only non-deleted vendors
        //     ->get();
        // 🔹 Vendors logic
        $data = $this->getPurchaseFormData($branch_id);
        $data['purchaseType'] = 'product';

        return view('purchase/addpurchase', $data);
    }

    public function add_row_material_purchase(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $data = $this->getPurchaseFormData($branch_id);
        $data['purchaseType'] = 'row-material';

        return view('purchase/addpurchase', $data);
    }

    public function row_material_purchase_list(Request $request)
    {
        return view('purchase.row-material-purchaselist');
    }
    public function edit_purchase(Request $request)
    {
        $user     = Auth()->user();
        $branchId = $user->id ?? null;
        $userRole = $user->role ?? '';

        $selectedSubAdminId = (session('selectedSubAdminId'));

        if ($user->role === 'staff' && $user->branch_id) {
            $branchId = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branchId = $selectedSubAdminId;
        } else {
            $branchId = $user->id;
        }
        $vendorsQuery = User::where('role', 'vendor')
            ->where('isDeleted', 0);

        // if ($userRole === 'staff') {
        //         // 🔹 Staff sees only vendors created by them
        //         $vendorsQuery->where('created_by', $user->id);
        //     } else {
        //         // Admin / Sub-admin sees vendors in their branch
        //         $vendorsQuery->where('branch_id', $branchId);
        // }

        if ($userRole === 'sub-admin' && $branchId) {
            $vendorsQuery->where('branch_id', $branchId);
            $products   = Product::where('isDeleted', 0)->where('branch_id', $branchId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $branchId)->get();
            $settings   = DB::table('settings')->where('branch_id', $branchId)->first();
        } elseif ($userRole === 'admin' && $selectedSubAdminId) {
            $vendorsQuery->where('branch_id', $selectedSubAdminId);
            $products   = Product::where('isDeleted', 0)->where('branch_id', $selectedSubAdminId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $selectedSubAdminId)->get();
            $settings   = DB::table('settings')->where('branch_id', $selectedSubAdminId)->first();
        } else {
            $products   = Product::where('isDeleted', 0)->where('branch_id', $branchId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $branchId)->get();
            $settings   = DB::table('settings')->where('branch_id', $branchId)->first();
        }

        $vendors  = $vendorsQuery->get();
        $purchase = PurchaseInvoice::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->first(); // 👈 instead of get()

        // $settings = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $productsArray = $products->map(function ($product) {
            return [
                'id'          => $product->id,
                'name'        => $product->name,
                'category_id' => $product->category_id,
                'price'       => $product->price,
                'product_gst' => $product->product_gst,
                'gst_option'  => $product->gst_option,
            ];
        });
        $productsJson = json_encode($productsArray);

        return view('purchase/editpurchase', compact('vendors', 'products', 'purchase', 'categories', 'settings', 'currencySymbol', 'currencyPosition', 'productsArray'));
    }
    public function purchase_order_report(Request $request)
    {
        return view('purchase/purchaseorderreport');
    }
    public function import_purchase(Request $request)
    {
        return view('purchase/importpurchase');
    }

    public function purchase_report(Request $request)
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

        // Fetch vendors
        if ($userRole === 'staff') {
            // Only vendors created by this staff
            $vendors = User::where('role', 'vendor')
                ->where('created_by', $user->id)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        } else {
            // Admin / sub-admin sees all vendors in branch
            $vendors = User::where('role', 'vendor')
                ->where('branch_id', $branchIdToUse)
                ->where('isDeleted', 0)
                ->orderBy('name')
                ->get();
        }

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

        return view('purchase.purchasereport', compact('vendors', 'categories'));
    }

    public function purchase_invoice(Request $request)
    {
        return view('purchase/purchase_invoice');
    }

    public function printPurchase(Request $request, $id)
    {

        $authUser   = Auth()->user();
        $subAdminId = session('selectedSubAdminId') ?? $authUser->id;

        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branchIdToUse = $authUser->branch_id;
        } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
            $branchIdToUse = $subAdminId;
        } else {
            $branchIdToUse = $authUser->id;
        }
        $invoice = PurchaseInvoice::find($id);

        if (! $invoice) {
            return redirect()->route('purchase.lists')->with('error', 'Purchase invoice not found.');
        }

        $vendor = User::where('id', $invoice->vendor_id)
            ->where('role', 'vendor')
            ->first();

        // 🔹 Fetch purchase items from Purchases table to get tax details
        $purchaseItems = Purchases::with('product')->where('invoice_id', $id)->get();

        $processedProducts = [];
        foreach ($purchaseItems as $item) {
            $product = $item->product;

            $images = $product && $product->images ? json_decode($product->images, true) : [];
            $imagePath = ! empty($images)
                ? env('ImagePath') . 'storage/' . $images[0]
                : env('ImagePath') . '/admin/assets/img/product/noimage.png';
            // dd($processedProducts);
            $processedProducts[] = [
                'product_id'          => $item->item,
                'product_name'        => $product ? $product->name : 'Unknown Product',
                'product_image'       => $imagePath,
                'quantity'            => $item->quantity,
                'price'               => $item->price,
                'discount_percent'    => $item->discount_percent,
                'discount_amount'     => $item->discount_amount,
                'product_gst_total'   => $item->product_gst_total,
                'product_gst_details' => $item->product_gst_details, // Cast to array in model
                'total'               => $item->price * $item->quantity, // Pre-tax total
            ];
        }
        $invoice->products = $processedProducts;

        // ✅ Ensure subtotal is pre-tax
        $invoice->total_amount = collect($processedProducts)->sum('total');

        $compenyinfo       = Setting::where('branch_id', $branchIdToUse)->first();

        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        $subTotal = collect($processedProducts)->sum('total'); // pre-tax

        $totalGST = $purchaseItems->sum('product_gst_total'); // GST total
        $shipping = (float) $invoice->shipping;


        $grandTotal = $subTotal + $totalGST + $shipping;
        $paidAmount = PaymentStore::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        $returnAmount = PurchaseReturn::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('total_amount');

        // $totalInvoiceAmount = $invoice->total_amount ?? 0;
        // dd($grandTotal);
        $payableAmount = $grandTotal - $returnAmount;
        // ✅ Correct calculations
        $pendingAmount = max(0, $payableAmount - $paidAmount);
        $extraPaid     = max(0, $paidAmount - $payableAmount);

        // ✅ Attach for blade usage
        $invoice->total_paid = $paidAmount;
        $invoice->total_return = $returnAmount;
        $invoice->pending_amount = $pendingAmount;
        $invoice->extra_paid = $extraPaid;
        $purchaseReturns = PurchaseReturn::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->with(['items' => function ($q) {
                $q->where('isDeleted', 0)->with('product');
            }])
            ->get();
        $invoice->purchase_returns = $purchaseReturns;

        return view('purchase.purchase_invoice', compact('invoice', 'vendor', 'compenyinfo', 'currencySymbol', 'currencyPosition', 'paidAmount', 'pendingAmount', 'extraPaid'));
    }
    public function purchase_invoice_pdf($id)
{
    $authUser   = Auth()->user();
    $subAdminId = session('selectedSubAdminId') ?? $authUser->id;
    if ($authUser->role === 'staff' && $authUser->branch_id) {
        $branchIdToUse = $authUser->branch_id;
    } elseif ($authUser->role === 'admin' && ! empty($subAdminId)) {
        $branchIdToUse = $subAdminId;
    } else {
        $branchIdToUse = $authUser->id;
    }
    $invoice = PurchaseInvoice::find($id);

    $setting = Setting::where('branch_id', $branchIdToUse)->first();

    if (! $invoice) {
        return redirect()->route('purchase.lists')->with('error', 'Purchase invoice not found.');
    }

    // Fetch vendor info (replace with actual relation if exists)
    $vendor = $invoice->vendor ?? [
        'name'    => 'Unknown Vendor',
        'email'   => '',
        'phone'   => '',
        'address' => '',
    ];

    // Fetch purchase items
    $purchaseItems = Purchases::where('invoice_id', $invoice->id)->get();

    // Get payment status & method from purchases table
    $paymentStatus = $purchaseItems->first()->payment_status ?? 'Pending';
    $paymentMethod = $purchaseItems->first()->payment_method ?? 'N/A';

    // ✅ Paid Amount (from payment_store)
    $paidAmount = PaymentStore::where('purchase_id', $invoice->id)
        ->where('isDeleted', 0)
        ->sum('payment_amount');

    // ✅ Return Amount
    $returnAmount = PurchaseReturn::where('purchase_id', $invoice->id)
        ->where('isDeleted', 0)
        ->sum('total_amount');

    // Fetch purchase returns with items
    $purchaseReturns = PurchaseReturn::where('purchase_id', $invoice->id)
        ->where('isDeleted', 0)
        ->with(['items' => function ($q) {
            $q->where('isDeleted', 0)->with('product');
        }])
        ->get();

    // Format currency helper
    $formatCurrency = fn($amt) => $setting->currency_position === 'right'
        ? number_format($amt, 2) . $setting->currency_symbol
        : $setting->currency_symbol . number_format($amt, 2);

    $pdfData = [
        'invoice'           => $invoice,
        'vendor'            => $vendor,
        'purchaseItems'     => $purchaseItems,
        'purchaseReturns'   => $purchaseReturns,
        'setting'           => $setting,
        'payment_status'    => ucfirst($paymentStatus),
        'payment_method'    => ucfirst($paymentMethod),
        'paidAmount'        => $paidAmount,
        'returnAmount'      => $returnAmount, // Add this to pass to view
        'totalReturnAmount' => $returnAmount, // Alternative name for consistency
    ];

    $pdf = PDF::loadView('purchase.purchase-invoice-pdf', $pdfData);
    return $pdf->stream('purchase_invoice_' . $id . '.pdf');
}

    public function purchases_report($ids)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            $branchId = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchId = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchId = $UserBranchId;
        } else {
            $branchId = $branchId;
        }
        $idsArray = explode(',', $ids);

        // Eager load vendor + details
        $purchases = Purchases::with('product.category', 'invoice', 'vendor.userDetail')
            ->whereIn('id', $idsArray)
            ->get();

        if ($purchases->isEmpty()) {
            return redirect()->route('purchase.report')->with('error', 'No purchase data found.');
        }

        $settings = Setting::where('branch_id', $branchId)->first();
        $subtotal = $purchases->sum('amount_total');

        // Calculate total tax
        $taxRates       = TaxRate::where('status', 'active')->where('branch_id', $branchId)->where('isDeleted', 0)->get();
        $taxDetails     = [];
        $totalTaxAmount = 0;

        foreach ($taxRates as $tax) {
            $amount       = $subtotal * ($tax->tax_rate / 100);
            $taxDetails[] = [
                'name'   => $tax->tax_name,
                'rate'   => $tax->tax_rate,
                'amount' => $amount,
            ];
            $totalTaxAmount += $amount;
        }

        // Get total shipping from all unique invoices
        $invoiceIds = [];
        $shipping   = 0;
        foreach ($purchases as $purchase) {
            if ($purchase->invoice && ! in_array($purchase->invoice->id, $invoiceIds)) {
                $invoiceIds[] = $purchase->invoice->id;
                $shipping += $purchase->invoice->shipping;
            }
        }

        $totalAmount = $subtotal + $totalTaxAmount + $shipping;

        $compenyinfo = $settings;

        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        // Collect vendor details (from the first purchase for example, or loop if multiple vendors)
        $vendor        = $purchases->first()->vendor ?? null;
        $vendorDetails = $vendor ? $vendor->userDetail : null;

        return view('purchase.purchase_report', compact(
            'purchases',
            'settings',
            'subtotal',
            'taxDetails',
            'shipping',
            'totalTaxAmount',
            'totalAmount',
            'currencySymbol',
            'currencyPosition',
            'vendor',
            'vendorDetails',
            'ids'
        ));
    }

    public function export_purchases_report_pdf($ids)
    {
        $user         = Auth()->user();
        $branchId     = $user->id ?? null;
        $UserBranchId = $user->branch_id ?? null;
        $userRole     = $user->role ?? '';
        $subAdminId   = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            $branchId = $branchId;
        } elseif ($userRole === 'admin' && $subAdminId) {
            $branchId = $subAdminId;
        } elseif ($userRole === 'staff') {
            $branchId = $UserBranchId;
        } else {
            $branchId = $branchId;
        }

        $idsArray = explode(',', $ids);

        $purchases = Purchases::with('product', 'invoice', 'vendor')
            ->whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($purchases->isEmpty()) {
            return redirect()->route('purchase.report')->with('error', 'No purchase data found.');
        }

        $setting = Setting::where('branch_id', $branchId)->first();

        // Subtotal of selected purchases
        $subtotalRaw = (float) $purchases->sum('amount_total');

        // Assuming no additional discount at report level
        $discountPercent   = 0.0;
        $discountAmountRaw = ($discountPercent / 100.0) * $subtotalRaw;
        $afterDiscountRaw  = $subtotalRaw - $discountAmountRaw;

        // Shipping from unique invoices
        $invoiceIds  = [];
        $shippingRaw = 0.0;
        foreach ($purchases as $purchase) {
            if ($purchase->invoice && ! in_array($purchase->invoice->id, $invoiceIds)) {
                $invoiceIds[] = $purchase->invoice->id;
                $shippingRaw += (float) ($purchase->invoice->shipping ?? 0);
            }
        }

        // Taxes from active tax rates
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
            ->first(); // pick the first invoice or handle multiple

        $invoice = (object) [
            'invoice_number'   => $invoiceRecord->invoice_number ?? 'PR-' . now()->format('YmdHis'),
            'created_at'       => $invoiceRecord->created_at ?? now()->format('Y-m-d H:i:s'),
            'paid'             => $invoiceRecord->paid ?? false,
            'status'           => $invoiceRecord->status ?? 'completed',
            'remaining_amount' => $invoiceRecord->remaining_amount ?? 0,
            'gst_option'       => $invoiceRecord->gst_option ?? 'without_gst',
        ];
        $compenyinfo = $setting;

        // Get currency settings
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

        // $pdf = Pdf::loadView('purchase.purchase-report-pdf', $pdfData)->setPaper('A4', 'portrait');
        // return $pdf->download('purchase_report.pdf');
        $pdf = PDF::loadView('purchase.purchase-report-pdf', $pdfData)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
            ]);

        return $pdf->download('purchase_report.pdf');
    }

    public function purchaseDetails()
    {
        return view('purchase.purchase-details');
    }
    public function getPurchaseDetails(Request $request, $id)
    {
        $invoice = PurchaseInvoice::findOrFail($id);

        // Properly decode taxes (double decode if needed)
        if (! empty($invoice->taxes) && is_string($invoice->taxes)) {
            $invoice->taxes = json_decode($invoice->taxes, true);
        }
        $vendor = User::where('id', $invoice->vendor_id)
            ->where('role', 'vendor')
            ->first();

        $productsData = json_decode($invoice->products, true);
        $productIds   = collect($productsData)->pluck('product_id')->unique()->toArray();
        $products     = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($productsData as &$item) {
            $product              = $products->get($item['product_id']);
            $item['product_name'] = $product ? $product->name : 'Unknown Product';

            $images                = $product && $product->images ? json_decode($product->images, true) : [];
            $item['product_image'] = ! empty($images)
                ? env('ImagePath') . 'storage/' . $images[0]
                : env('ImagePath') . '/admin/assets/img/product/noimage.png';
        }

        $invoice->products = $productsData;
        $compenyinfo       = Setting::first();

        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        return view('purchase.purchase-details', compact('invoice', 'vendor', 'compenyinfo', 'currencySymbol', 'currencyPosition'));
    }

    public function show_purchase_report_page(Request $request)
    {
        try {
            $ids      = explode(',', $request->query('ids'));
            $branchId = $request->query('branch');

            // Fetch purchases and related data
            $purchases = Purchases::with('product.category', 'invoice', 'vendor.userDetail')
                ->whereIn('id', $ids)
                ->get();

            if ($purchases->isEmpty()) {
                return "<h4 style='color:red;text-align:center;'>No purchase data found.</h4>";
            }

            // Get branch or global settings
            $settings = Setting::where('branch_id', $branchId)->first() ?? Setting::first();

            $subtotal = $purchases->sum('amount_total');

            // Get tax details
            $taxRates = TaxRate::where('status', 'active')
                ->where('isDeleted', 0)
                ->where(function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId)->orWhereNull('branch_id');
                })
                ->get();

            $taxDetails     = [];
            $totalTaxAmount = 0;
            foreach ($taxRates as $tax) {
                $amount       = $subtotal * ($tax->tax_rate / 100);
                $taxDetails[] = [
                    'name'   => $tax->tax_name,
                    'rate'   => $tax->tax_rate,
                    'amount' => $amount,
                ];
                $totalTaxAmount += $amount;
            }

            // Calculate total shipping (unique invoices only)
            $invoiceIds = [];
            $shipping   = 0;
            foreach ($purchases as $purchase) {
                if ($purchase->invoice && ! in_array($purchase->invoice->id, $invoiceIds)) {
                    $invoiceIds[] = $purchase->invoice->id;
                    $shipping += $purchase->invoice->shipping ?? 0;
                }
            }

            $totalAmount = $subtotal + $totalTaxAmount + $shipping;

            $currencySymbol   = $settings->currency_symbol ?? '₹';
            $currencyPosition = $settings->currency_position ?? 'left';

            $vendor        = $purchases->first()->vendor ?? null;
            $vendorDetails = $vendor ? $vendor->userDetail : null;

            return view('purchase.web_purchase_report', compact(
                'purchases',
                'settings',
                'subtotal',
                'taxDetails',
                'shipping',
                'totalTaxAmount',
                'totalAmount',
                'currencySymbol',
                'currencyPosition',
                'vendor',
                'vendorDetails'
            ));
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to show purchase report page.',
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
