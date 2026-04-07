<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
// use PDF;
use App\Models\CustomInvoice;
use App\Models\CustomInvoiceItem;
use App\Models\PaymentStore;
use App\Models\Product;
use App\Models\Setting;
use App\Models\TaxRate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CustomInvoiceController extends Controller
{
    public function custom_invoice_list(Request $request)
    {
        $years = CustomInvoice::where('isDeleted', 0)
            ->selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('custom-invoice/custom_invoicelist', compact('years'));
    }

    public function getHistory($custom_invoice_id)
    {
        // $history = PaymentStore::where('order_id', $job  _card_id)
        $history = PaymentStore::where('custom_invoice_id', $custom_invoice_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $history,
        ]);
    }

    public function add_custom_invoice(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $vendorQuery = User::where('role', 'vendor')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse);
        // ->get();
        $customerQuery = User::where('role', 'customer')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse);
        // ->get();
        // if ($user->role === 'staff') {
        //     // Staff → Only vendors/customers created by this staff
        //     $vendorQuery->where('created_by', $user->id);
        //     $customerQuery->where('created_by', $user->id);
        // } else {
        //     // Admin/Sub-admin → Filter by branch
        //     $vendorQuery->where('branch_id', $branchIdToUse);
        //     $customerQuery->where('branch_id', $branchIdToUse);
        // }

        $vendors   = $vendorQuery->get();
        $customers = $customerQuery->get();

        $categories = Category::where('branch_id', $branchIdToUse)->where('isDeleted', 0)->get();
        $banks      = \App\Models\BankMaster::where('branch_id', $branchIdToUse)->where('isDeleted', 0)->where('status', 1)->get();

        return view('custom-invoice/addcustom_invoice', compact('vendors', 'customers', 'categories', 'banks'));
    }
    public function edit_custom_invoice(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $vendors = User::where('role', 'vendor')
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->get();
        $customers = User::where('role', 'customer')
            ->where('branch_id', $branch_id)
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->get();
        $taxes      = TaxRate::where('status', 'active')->where('isDeleted', 0)->where('branch_id', $branch_id)->get();
        $products   = Product::where('branch_id', $branch_id)->where('isDeleted', 0)->get();
        $categories = Category::where('branch_id', $branch_id)->where('isDeleted', 0)->get();
        $banks      = \App\Models\BankMaster::where('branch_id', $branch_id)->where('isDeleted', 0)->where('status', 1)->get();

        return view('custom-invoice/editcustom_invoice', compact('vendors', 'customers', 'taxes', 'products', 'categories', 'banks'));
    }

    public function custom_invoice_print(Request $request, $id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $invoice = CustomInvoice::findOrFail($id);

        // Properly decode taxes (double decode if needed)
        if (! empty($invoice->taxes) && is_string($invoice->taxes)) {
            $invoice->taxes = json_decode($invoice->taxes, true);
        }

        // Determine the vendor or customer (vendor_id can be null)
        $vendorId = $invoice->vendor_id ?? $invoice->customer_id;
        $vendor   = User::where('id', $vendorId)
            ->whereIn('role', ['vendor', 'customer'])
            ->first();

        $invoiceItems = CustomInvoiceItem::where('invoice_id', $id)->where('isDeleted', 0)->with('product')->get();
        $productsData = [];

        foreach ($invoiceItems as $item) {
            $product = $item->product;
            $images = $product && $product->images ? json_decode($product->images, true) : [];

            $productsData[] = [
                'product_id'    => $item->item,
                'product_name'  => $product ? $product->name : 'Unknown Product',
                'product_image' => !empty($images)
                    ? env('ImagePath') . 'storage/' . $images[0]
                    : env('ImagePath') . '/admin/assets/img/product/noimage.png',
                'price'         => $item->price,
                'quantity'      => $item->quantity,
                'total'         => $item->amount_total,
               'taxes' => !empty($item->product_gst_details)
    ? (is_array($item->product_gst_details)
        ? $item->product_gst_details
        : json_decode($item->product_gst_details, true))
    : [],
                'tax_amount'    => $item->product_gst_total ?? 0,
                'subtotal_excl_tax' => $item->price * $item->quantity,
            ];
        }

        $hasAnyProductTax = false;
        foreach ($productsData as $product) {
            if (!empty($product['taxes'])) {
                $hasAnyProductTax = true;
                break;
            }
        }


        $invoice->products = $productsData;
        $compenyinfo       = Setting::where('branch_id', $branch_id)->first();

        $hasPaymentStarted = PaymentStore::where('custom_invoice_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        $paidAmount = PaymentStore::where('custom_invoice_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Pending amount (single source of truth)
        $pendingAmount = $invoice->remaining_amount ?? 0;
        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        return view('custom-invoice/download_invoice', compact('invoice', 'vendor', 'compenyinfo', 'currencySymbol', 'currencyPosition', 'hasPaymentStarted', 'paidAmount', 'pendingAmount', 'hasAnyProductTax'));
    }

    public function custom_invoice_view(Request $request, $id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }

        $invoice = CustomInvoice::findOrFail($id);

        // Properly decode taxes (double decode if needed)
        if (! empty($invoice->taxes) && is_string($invoice->taxes)) {
            $invoice->taxes = json_decode($invoice->taxes, true);
        }

        // Determine the vendor or customer (vendor_id can be null)
        $vendorId = $invoice->vendor_id ?? $invoice->customer_id;
        $vendor   = User::where('id', $vendorId)
            ->whereIn('role', ['vendor', 'customer'])
            ->first();

        $invoiceItems = CustomInvoiceItem::where('invoice_id', $id)->where('isDeleted', 0)->with('product')->get();
        $productsData = [];

        foreach ($invoiceItems as $item) {
            $product = $item->product;
            $images = $product && $product->images ? json_decode($product->images, true) : [];

            $productsData[] = [
                'product_id'    => $item->item,
                'product_name'  => $product ? $product->name : 'Unknown Product',
                'product_image' => !empty($images)
                    ? env('ImagePath') . 'storage/' . $images[0]
                    : env('ImagePath') . '/admin/assets/img/product/noimage.png',
                'price'         => $item->price,
                'quantity'      => $item->quantity,
                'total'         => $item->amount_total,
               'taxes' => !empty($item->product_gst_details)
    ? (is_array($item->product_gst_details)
        ? $item->product_gst_details
        : json_decode($item->product_gst_details, true))
    : [],
                'tax_amount'    => $item->product_gst_total ?? 0,
                'subtotal_excl_tax' => $item->price * $item->quantity,
            ];
        }

        $hasAnyProductTax = false;
        foreach ($productsData as $product) {
            if (!empty($product['taxes'])) {
                $hasAnyProductTax = true;
                break;
            }
        }


        $invoice->products = $productsData;
        $compenyinfo       = Setting::where('branch_id', $branch_id)->first();

        $hasPaymentStarted = PaymentStore::where('custom_invoice_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        $paidAmount = PaymentStore::where('custom_invoice_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Pending amount (single source of truth)
        $pendingAmount = $invoice->remaining_amount ?? 0;
        // Get currency settings
        $currencySymbol   = $compenyinfo->currency_symbol ?? '₹';
        $currencyPosition = $compenyinfo->currency_position ?? 'left';

        return view('custom-invoice/custom-invoice-details', compact('invoice', 'vendor', 'compenyinfo', 'currencySymbol', 'currencyPosition', 'hasPaymentStarted', 'paidAmount', 'pendingAmount', 'hasAnyProductTax'));
    }

    public function exportCustom_invoice(Request $request)
    {
        $user = auth()->user();
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

            if ($role === 'staff') {
                // Staff sees only invoices they created
                $query->where('created_by', $userId);
            } elseif ($role === 'sub-admin') {
                // Sub-admin sees invoices for their branch
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin') {
                // Admin can select a sub-admin branch, else default to their own branch
                $branchIdToUse = $selectedSubAdminId ?? $userId;
                $query->where('branch_id', $branchIdToUse);
            } else {
                // Fallback: default to user's own branch
                $query->where('branch_id', $userId);
            }
            // dd($userId);

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
                'G1' => 'Total GST',
                'H1' => 'Discount',
                'I1' => 'Shipping',
                'J1' => 'Grand Total',
                'K1' => 'Paid',
                'L1' => 'Remaining Amount',
                'M1' => 'Status',
                'N1' => 'Created At',
            ];
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            $sheet->getStyle('A1:N1')->getFont()->setBold(true);

            $gstTotal = 0;

            $gstTotal = 0;

            // ✅ Rows
            $row = 2;
            foreach ($invoices as $invoice) {
                $gstTotal = CustomInvoiceItem::where('invoice_id', $invoice->id)
                    ->where('isDeleted', 0)
                    ->sum('product_gst_total');

                $paidAmount = PaymentStore::where('custom_invoice_id', $invoice->id)
                    ->where('isDeleted', 0)
                    ->sum('payment_amount');
                $sheet->setCellValue('A' . $row, $invoice->invoice_number ?? 'N/A');
                $sheet->setCellValue('B' . $row, $invoice->vendor->name ?? 'N/A');
                $sheet->setCellValueExplicit(
                    'C' . $row,
                    $invoice->vendor->phone ?? 'N/A',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $sheet->setCellValue('D' . $row, $invoice->customer->name ?? 'N/A');

                $sheet->setCellValueExplicit(
                    'E' . $row,
                    $invoice->customer->phone ?? 'N/A',
                    \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
                );
                $sheet->setCellValue('F' . $row, $invoice->total_amount ?? 0);
                $sheet->setCellValue('G' . $row, $gstTotal);
                $sheet->setCellValue('H' . $row, $invoice->discount ?? 0);
                $sheet->setCellValue('I' . $row, $invoice->shipping ?? 0);
                $sheet->setCellValue('J' . $row, $invoice->grand_total ?? 0);
                $sheet->setCellValue('K' . $row, $paidAmount ?? 0);
                $sheet->setCellValue('L' . $row, $invoice->remaining_amount ?? 0);
                $sheet->setCellValue('M' . $row, $invoice->status ?? 'N/A');
                $sheet->setCellValue('N' . $row, $invoice->created_at->format('Y-m-d'));
                $row++;
            }

            $lastRow = $row - 1;
            if ($lastRow >= 2) {
                // Apply Indian number format to amount columns F through L
                $sheet->getStyle('F2:L' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##,##0.00');
            }

            $writer   = new Xlsx($spreadsheet);
            $fileName = 'CustomInvoices_' . date('Ymd_His') . '.xlsx';

            return response()->streamDownload(function () use ($writer) {
                $writer->save('php://output');
            }, $fileName, [
                'Content-Type'                  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function exportInvoicePDF(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId = $user->id;
        $role   = $user->role;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? session('selectedSubAdminId') ?? $user->id;

        if ($role === 'staff') {
            // Get the staff's branch ID from their user record
            $branchIdForSetting = $user->branch_id;
        } elseif ($role === 'sub-admin') {
            $branchIdForSetting = $userId; // sub-admin's own branch
        } elseif ($role === 'admin') {
            $branchIdForSetting = $selectedSubAdminId ?? $userId; // admin-selected branch
        } else {
            $branchIdForSetting = $userId; // fallback
        }
        $setting = Setting::where('branch_id', $branchIdForSetting)->first();

        try {
            $query = CustomInvoice::with(['vendor:id,name', 'customer:id,name', 'payments']);
            // ->where('branch_id', $selectedSubAdminId);

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

            // Role-based filtering
            if ($role === 'staff') {
                // Staff sees only invoices they created
                $query->where('created_by', $userId);
            } elseif ($role === 'sub-admin') {
                // Sub-admin sees invoices for their branch
                $query->where('branch_id', $userId);
            } elseif ($role === 'admin') {
                // Admin can select a sub-admin branch, else default to their own branch
                $branchIdToUse = $selectedSubAdminId ?? $userId;
                $query->where('branch_id', $branchIdToUse);
            } else {
                // Fallback: default to user's own branch
                $query->where('branch_id', $userId);
            }
            // dd($userId);

            $invoices = $query->orderBy('created_at', 'desc')->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No invoices found for the given criteria.',
                ], 404);
            }

            foreach ($invoices as $invoice) {
                $invoice->total_gst = CustomInvoiceItem::where('invoice_id', $invoice->id)
                    ->where('isDeleted', 0)
                    ->sum('product_gst_total'); // 👈 SAME FIELD AS EXCEL

                $invoice->paid_amount = PaymentStore::where('custom_invoice_id', $invoice->id)
                    ->where('isDeleted', 0)
                    ->sum('payment_amount');
            }

            $payments = PaymentStore::whereIn('custom_invoice_id', $invoices->pluck('id'))->get();

            $pdf = Pdf::loadView('custom-invoice.custom_invoice_pdf', [
                'invoices' => $invoices,
                'payments' => $payments,
                'setting'  => $setting,
            ])->setPaper('A4', 'portrait');

            $filename = 'CustomInvoices_' . date('Ymd_His') . '.pdf';

            return $pdf->download($filename, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function custom_invoice_pdf($id)
    {
        $user = Auth::user();

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branch_id = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $user->id;
        }
        $view_id = $id;
        $invoice = CustomInvoice::find($view_id);
        $setting = Setting::where('branch_id', $branch_id)->first();

        if (! $invoice) {
            return redirect()->route('sales.list')->with('error', 'Invoice not found.');
        }

        // Load vendor or customer
        $vendor   = $invoice->vendor_id ? User::with('details')->find($invoice->vendor_id) : null;
        $customer = $invoice->customer_id ? User::with('details')->find($invoice->customer_id) : null;

        // Decide party (customer or vendor)
        $party = $vendor ?: $customer;

        $formatCurrency = function ($amount) use ($setting) {
            $amount = number_format($amount, 2);
            return $setting->currency_position === 'right'
                ? $amount . $setting->currency_symbol
                : $setting->currency_symbol . $amount;
        };

        $taxDetails = is_array($invoice->taxes)
    ? $invoice->taxes
    : json_decode($invoice->taxes, true);

        $invoiceItems = CustomInvoiceItem::where('invoice_id', $view_id)->get();

  $subtotal           = $invoiceItems->sum('amount_total');
$discountPercentage = $invoice->discount ?? 0;
$shipping           = $invoice->shipping ?? 0;

// Total GST
$totalTaxAmount = collect($taxDetails)->sum('amount');

// ✅ Decide discount base
if ($invoice->gst_option === 'with_gst') {
    // Discount on subtotal + GST
    $discountBase = $subtotal + $totalTaxAmount;
} else {
    // Discount only on subtotal
    $discountBase = $subtotal;
}

// ✅ Calculate discount
$discountAmount = ($discountBase * $discountPercentage) / 100;

// ✅ Final total calculation
if ($invoice->gst_option === 'with_gst') {
    $afterDiscount = ($subtotal + $totalTaxAmount) - $discountAmount;
} else {
    $afterDiscount = $subtotal - $discountAmount;
}

$finalTotal = $afterDiscount + $shipping;

        // Prepare party info (customer/vendor)
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

        $paidAmount = PaymentStore::where('custom_invoice_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // ✅ Pending amount (single source of truth)
        $pendingAmount = $invoice->remaining_amount ?? 0;

        // dd( $paidAmount);

        $pdfData = [
            'view_id'        => $view_id,
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
            'formatCurrency' => $formatCurrency,
            'paidAmount'     => $formatCurrency($paidAmount),
            'pendingAmount'  => $formatCurrency($pendingAmount),
        ];

        $pdf = PDF::loadView('custom-invoice.custom-invoice-pdf', $pdfData);
        return $pdf->stream('invoice_' . $view_id . '.pdf');
    }

    public function show($id)
    {
        $invoice     = CustomInvoice::findOrFail($id);
        $user        = $invoice->vendor_id ? User::find($invoice->vendor_id) : User::find($invoice->customer_id);
        $companyInfo = Setting::first();

        // Get currency settings
        $currencySymbol   = $companyInfo->currency_symbol ?? '₹';
        $currencyPosition = $companyInfo->currency_position ?? 'left';

        return view('custom-invoice.view', compact('invoice', 'user', 'companyInfo', 'currencySymbol', 'currencyPosition'));
    }
}
