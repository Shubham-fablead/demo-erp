@extends('layout.app')

@section('title', 'Purchase Invoice')

@section('content')
    @php
        // Calculate total return amount correctly (Price × Qty - Discount + GST)
        $totalReturnAmount = 0;
        if (isset($invoice->purchase_returns)) {
            foreach ($invoice->purchase_returns as $ret) {
                foreach ($ret->items as $retItem) {
                    $lineTotal = $retItem->price * $retItem->quantity;
                    $itemTotal = $lineTotal - ($retItem->discount_amount ?? 0) + ($retItem->product_gst_total ?? 0);
                    $totalReturnAmount += $itemTotal;
                }
            }
        }

        // Calculate invoice totals
        $subtotal = 0;
        $totalDiscountAmount = 0;
        $totalGST = 0;
        foreach ($invoice->products as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalDiscountAmount += $item['discount_amount'] ?? 0;
            $totalGST += $item['product_gst_total'] ?? 0;
        }

        $afterDiscount = $subtotal - $totalDiscountAmount;
        $shippingCharge = $invoice->shipping ?? 0;
        $grandTotal = $afterDiscount + $totalGST + $shippingCharge;

        // Calculate total return amount including shipping if fully returned
        $totalReturnWithShipping = $totalReturnAmount;
        $isFullyReturned = false;

        // Check if all items are fully returned
        $allItemsReturned = true;
        if (isset($invoice->purchase_returns) && count($invoice->purchase_returns) > 0) {
            foreach ($invoice->products as $product) {
                $totalReturnedQty = 0;
                foreach ($invoice->purchase_returns as $ret) {
                    foreach ($ret->items as $retItem) {
                        if ($retItem->product_id == $product['product_id']) {
                            $totalReturnedQty += $retItem->quantity;
                        }
                    }
                }
                if ($totalReturnedQty < $product['quantity']) {
                    $allItemsReturned = false;
                    break;
                }
            }
        } else {
            $allItemsReturned = false;
        }

        // If all items are fully returned, add shipping to return amount
        if ($allItemsReturned && $totalReturnAmount > 0) {
            $totalReturnWithShipping = $totalReturnAmount + $shippingCharge;
            $isFullyReturned = true;
        }

        // Calculate paid amount (from PaymentStore model)
        $paidAmount = \App\Models\PaymentStore::where('purchase_id', $invoice->id)
            ->where('isDeleted', 0)
            ->sum('payment_amount');

        // Calculate pending amount: Grand Total - (Paid Amount + Return Amount)
        $pendingAmount = $grandTotal - ($paidAmount + $totalReturnWithShipping);

        // Calculate extra paid if any
        $extraPaid = 0;
        if ($pendingAmount < 0) {
            $extraPaid = abs($pendingAmount);
            $pendingAmount = 0;
        }

        // Determine return status text and color
        $returnStatusText = 'No return';
        $returnStatusColor = '#28c76f';

        if ($totalReturnAmount > 0) {
            if ($totalReturnWithShipping >= $grandTotal) {
                $returnStatusText = 'Fully Returned';
                $returnStatusColor = '#ea5455';
            } else {
                $returnStatusText = 'Partially Returned';
                $returnStatusColor = '#ff9f43';
            }
        }
    @endphp

    <style>
        .logo_img {
            max-width: 150px;
            height: auto;
        }

        .invoice-box table td {
            white-space: nowrap;
        }

        .info-column {
            white-space: normal !important;
            word-break: break-word !important;
            vertical-align: top;
            width: 33.33%;
        }

        .info-column table td {
            white-space: normal !important;
            padding: 2px 0;
        }

        /* Product Name Column - Allow Wrapping */
        .invoice-box table tbody tr.details td:first-child,
        .invoice-box table tbody tr.details td:nth-child(1) {
            white-space: normal !important;
            word-wrap: break-word;
            word-break: break-word;
            max-width: 300px;
            min-width: 200px;
        }

        .invoice-box table tbody tr.details td:first-child a,
        .invoice-box table tbody tr.details td:nth-child(1) a {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            max-width: 100%;
        }

        /* Ensure Product Name header column has proper width */
        .invoice-box table tr.heading td:first-child {
            min-width: 200px;
            max-width: 300px;
        }

        /* Adjust columns for mobile, keep same layout */
        @media (max-width: 767px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .page-btn {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 5px !important;
                width: 100% !important;
                margin-top: 10px !important;
            }

            .page-btn a,
            .page-btn button {
                margin: 0 !important;
                padding: 8px 2px !important;
                font-size: 11px !important;
                display: flex !important;
                align-items: center;
                justify-content: center;
                text-align: center;
                width: 100% !important;
                white-space: normal !important;
                line-height: 1.1;
            }

            .invoice-box {
                overflow-x: auto;
            }

            .card-sales-split {
                flex-direction: row;
            }

            .row {
                display: flex;
                flex-wrap: wrap;
            }

            .col-lg-6.table_view {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .col-lg-6.total-order {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .invoice-box,
            .invoice-box table,
            .invoice-box td,
            .invoice-box h4,
            .invoice-box h5 {
                font-size: 12px;
                line-height: 1.2;
            }

            /* Ensure Product Name column has enough space on mobile */
            .invoice-box table tr.heading td:first-child,
            .invoice-box table tr.details td:first-child {
                min-width: 200px !important;
            }

            .logo_img {
                max-width: 130px;
                height: auto;
            }

            font {
                line-height: 22px !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchase Invoice</h4>
            </div>

            <div class="page-btn d-flex gap-2">
                            @if (app('hasPermission')(3, 'add'))
                <a href="{{ route('purchase.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Purchase
                </a>
                                @endif
                          @if (app('hasPermission')(3, 'edit') && (!isset($invoice->purchase_returns) || count($invoice->purchase_returns) == 0))
                    <a href="{{ url('edit-purchase/' . $invoice->id) }}" class="btn btn-primary">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                @endif
                            @if (app('hasPermission')(3, 'view'))
                <a href="{{ route('purchase.invoice.pdf', $invoice->id) }}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-print me-1"></i> Print
                </a>
                <a href="{{ route('purchase.lists') }}" class="btn" style="background: #1b2850; color: #fff;">
                    Back
                </a>
                                @endif

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="download_pdf">
                    <div class="invoice-box"
                        style="max-width: 1600px;width:100%;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                        <!-- Header Section -->
                        <div class="row mb-4">
                            <div class="col-6">
                                <img src="{{ $compenyinfo->logo ? env('ImagePath') . 'storage/' . $compenyinfo->logo : env('ImagePath') . '/admin/assets/img/logso.png' }}"
                                    alt="logo" class="logo_img">
                            </div>
                            <div class="col-6 text-end">
                                <h1 style="margin:0;">Invoice</h1>
                                <h4 class="mt-2">#{{ $invoice->invoice_number }}</h4>
                            </div>
                        </div>

                        <hr>

                        <!-- Info Section -->
                        <table class="info-table mb-4"
                            style="width:100%; line-height:inherit; text-align:left; border-collapse:collapse;">
                            <tr>
                                <td class="info-column" style="padding:5px;">
                                    <div style="font-size:14px; color:#7367F0; font-weight:600; line-height:35px;">Vendor
                                        Info</div>
                                    @if (!empty($vendor->name))
                                        <div>{{ $vendor->name }}</div>
                                    @endif
                                    @if (!empty($vendor->email))
                                        <div>{{ $vendor->email }}</div>
                                    @endif
                                    @if (!empty($vendor->phone))
                                        <div>{{ $vendor->phone }}</div>
                                    @endif
                                 </td>
                                <td class="info-column" style="padding:5px;">
                                    <div style="font-size:14px; color:#7367F0; font-weight:600; line-height:35px;">Company
                                        Info</div>
                                    @if (!empty($compenyinfo->name))
                                        <div>{{ $compenyinfo->name }}</div>
                                    @endif
                                    @if (!empty($compenyinfo->email))
                                        <div>{{ $compenyinfo->email }}</div>
                                    @endif
                                    @if (!empty($compenyinfo->phone))
                                        <div>{{ $compenyinfo->phone }}</div>
                                    @endif
                                    @if (!empty($compenyinfo->address))
                                        <div>{{ $compenyinfo->address }}</div>
                                    @endif
                                    @if (!empty($compenyinfo->gst_num))
                                        <div><strong>GST No : </strong> {{ $compenyinfo->gst_num }}</div>
                                    @endif
                                 </td>
                                <td class="info-column" style="padding:5px;">
                                    <div style="font-size:14px; color:#7367F0; font-weight:600; line-height:35px;">Invoice
                                        Info</div>
                                    <table style="width:100%; font-size: 14px;">
                                        <tr>
                                            <td>Invoice Number</td>
                                            <td style="text-align:right;">{{ $invoice->invoice_number }}</td>
                                        </tr>
                                        <tr>
                                            <td>Purchase Date</td>
                                            <td style="text-align:right;">{{ $invoice->created_at }}</td>
                                        </tr>
                                        <tr>
                                            <td>Payment Status</td>
                                            <td style="text-align:right;">{{ $invoice->paid ? 'Paid' : 'Unpaid' }}</td>
                                        </tr>
                                        <tr>
                                            <td>Purchase Status</td>
                                            <td style="text-align:right;">{{ ucfirst($invoice->status) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Return Status</td>
                                            <td style="text-align:right;">
                                                <span style="color: {{ $returnStatusColor }}; font-weight: bold;">
                                                    {{ $returnStatusText }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                 </td>
                             </tr>
                         </table>

                        @php
                            $hasGST = false;
                            foreach ($invoice->products as $item) {
                                if (
                                    (!empty($item['product_gst_total']) && $item['product_gst_total'] > 0) ||
                                    !empty($item['product_gst_details'])
                                ) {
                                    $hasGST = true;
                                    break;
                                }
                            }
                        @endphp

                        <!-- Product Table -->
                        <table class="product-table mb-4" style="width:100%; border-collapse:collapse; margin-bottom:20px;">
                            <thead>
                                <tr class="heading" style="background: #F3F2F7;">
                                    <th style="padding:10px; text-align:left; font-weight:600;">Product Name</th>
                                    <th class="qty-col" style="padding:10px; font-weight:600;">Qty</th>
                                    <th class="price-col" style="padding:10px; font-weight:600;">Price</th>
                                    <th class="discount-col" style="padding:10px; font-weight:600;">Discount Amount</th>
                                    @if ($hasGST)
                                        <th class="tax-col" style="padding:10px; font-weight:600;">Product Taxes</th>
                                        <th class="taxamount-col" style="padding:10px; font-weight:600;">Tax Amount</th>
                                    @endif
                                    <th class="total-col" style="padding:10px; font-weight:600; text-align:right;">Total
                                        (Excl. Tax)</th>
                                 </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoice->products as $product)
                                    <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                        <td style="padding:10px;">
                                            <div class="product-cell" style="display:flex; align-items:center;">
                                                <img src="{{ $product['product_image'] }}"
                                                    style="width:40px;height:40px; margin-right:10px;">
                                                <span>{{ $product['product_name'] }}</span>
                                            </div>
                                         </td>
                                        <td class="qty-col" style="padding:10px; ">
                                            {{ $product['quantity'] }} </td>
                                        <td class="price-col" style="padding:10px;">
                                            {{ $currencySymbol }}{{ number_format($product['price'], 2) }} </td>
                                        <td class="discount-col" style="padding:10px;">
                                            {{ $currencySymbol }}{{ number_format($product['discount_amount'] ?? 0, 2) }}
                                            <br>
                                            <small>({{ number_format($product['discount_percent'] ?? 0, 2) }}%)</small>
                                         </td>
                                        @if ($hasGST)
                                            <td class="tax-col" style="padding:10px;">
                                                @php
                                                    $gstDetails = $product['product_gst_details'] ?? [];
                                                    if (is_string($gstDetails)) {
                                                        $gstDetails = json_decode($gstDetails, true) ?? [];
                                                    }
                                                @endphp
                                                @if (!empty($gstDetails))
                                                    @foreach ($gstDetails as $tax)
                                                        <div style="font-size:12px;">{{ $tax['name'] ?? 'Tax' }}
                                                            ({{ $tax['rate'] ?? 0 }}%)
                                                            :
                                                            {{ $currencySymbol }}{{ number_format($tax['amount'] ?? 0, 2) }}
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span style="color:#999;">N/A</span>
                                                @endif
                                             </td>
                                            <td class="taxamount-col" style="padding:10px;">
                                                {{ $currencySymbol }}{{ number_format($product['product_gst_total'] ?? 0, 2) }}
                                             </td>
                                        @endif
                                        <td class="total-col" style="padding:10px; text-align:right;">
                                            {{ $currencySymbol }}{{ number_format($product['total'], 2) }} </td>
                                     </tr>
                                @endforeach
                            </tbody>
                         </table>

                        <!-- Return History Section -->
                        @if (isset($invoice->purchase_returns) && count($invoice->purchase_returns) > 0)
                            @php
                                $hasReturnTax = false;
                                foreach ($invoice->purchase_returns as $ret) {
                                    foreach ($ret->items as $retItem) {
                                        if ($retItem->product_gst_total > 0) {
                                            $hasReturnTax = true;
                                            break 2;
                                        }
                                    }
                                }
                            @endphp
                            <div class="return-history-section mb-4">
                                <div
                                    style="background:#F3F2F7; padding:10px; text-align:center; font-weight:600; color:#7367F0; font-size:16px; margin-bottom:1px;">
                                    Return History
                                </div>
                                <table class="return-table" style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr style="background:#F8F9FA;">
                                            <th style="padding:10px; text-align:left; font-weight:600; font-size:14px;">
                                                Return ID</th>
                                            <th style="padding:10px; text-align:left; font-weight:600; font-size:14px;">
                                                Return Date</th>
                                            <th style="padding:10px; text-align:left; font-weight:600; font-size:14px;">
                                                Product Name</th>
                                            <th style="padding:10px; text-align:center; font-weight:600; font-size:14px;">
                                                Return Qty</th>
                                            <th style="padding:10px; text-align:center; font-weight:600; font-size:14px;">
                                                Price</th>
                                            <th style="padding:10px; text-align:center; font-weight:600; font-size:14px;">
                                                Discount Amt</th>
                                            @if ($hasReturnTax)
                                                <th
                                                    style="padding:10px; text-align:center; font-weight:600; font-size:14px;">
                                                    Tax</th>
                                            @endif
                                            <th style="padding:10px; text-align:right; font-weight:600; font-size:14px;">
                                                Total</th>
                                         </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice->purchase_returns as $ret)
                                            @foreach ($ret->items as $retItem)
                                                @php
                                                    $lineTotal = $retItem->price * $retItem->quantity;
                                                    $finalTotal =
                                                        $lineTotal -
                                                        ($retItem->discount_amount ?? 0) +
                                                        ($retItem->product_gst_total ?? 0);
                                                @endphp
                                                <tr style="border-bottom:1px solid #E9ECEF;">
                                                    <td style="padding:10px;">#{{ $ret->return_no }}</td>
                                                    <td style="padding:10px;">
                                                        {{ $ret->created_at->format('d M Y h:i A') }}</td>
                                                    <td style="padding:10px;">{{ $retItem->product->name ?? 'N/A' }}</td>
                                                    <td style="padding:10px; text-align:center;">{{ $retItem->quantity }}
                                                     </td>
                                                    <td style="padding:10px; text-align:center;">
                                                        {{ $currencySymbol }}{{ number_format($retItem->price, 2) }}</td>
                                                    <td style="padding:10px; text-align:center;">
                                                        {{ $currencySymbol }}{{ number_format($retItem->discount_amount, 2) }}
                                                        @if ($retItem->discount > 0)
                                                            <small>({{ number_format($retItem->discount, 2) }}%)</small>
                                                        @endif
                                                     </td>
                                                    @if ($hasReturnTax)
                                                        <td style="padding:10px; text-align:center;">
                                                            {{ $currencySymbol }}{{ number_format($retItem->product_gst_total, 2) }}
                                                         </td>
                                                    @endif
                                                    <td style="padding:10px; text-align:right;">
                                                        {{ $currencySymbol }}{{ number_format($finalTotal, 2) }}
                                                     </td>
                                                 </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                 </table>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-lg-6"></div>
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li>
                                        <h4>Subtotal</h4>
                                        <h5>{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</h5>
                                    </li>

                                    <li>
                                        <h4>Discount Amount</h4>
                                        <h5>{{ $currencySymbol }}{{ number_format($totalDiscountAmount, 2) }}</h5>
                                    </li>

                                    <li>
                                        <h4>Price after Discount</h4>
                                        <h5>{{ $currencySymbol }}{{ number_format($afterDiscount, 2) }}</h5>
                                    </li>

                                    @if ($hasGST)
                                        <li>
                                            <h4>Total GST</h4>
                                            <h5>{{ $currencySymbol }}{{ number_format($totalGST, 2) }}</h5>
                                        </li>
                                    @endif

                                    <li>
                                        <h4>Shipping</h4>
                                        <h5>{{ $currencySymbol }}{{ number_format($shippingCharge, 2) }}</h5>
                                    </li>

                                    @if ($totalReturnAmount > 0)
                                        <li>
                                            <h4 style="color:#ea5455;">Return Amount</h4>
                                            <h5 style="color:#ea5455;">
                                                {{ $currencySymbol }}{{ number_format($totalReturnWithShipping, 2) }}
                                                {{-- @if ($allItemsReturned && $totalReturnAmount > 0 && $shippingCharge > 0)
                                                    <small style="font-size: 10px;">(Incl. Shipping)</small>
                                                @endif --}}
                                            </h5>
                                        </li>
                                    @endif

                                    <li>
                                        <h4>Return Status</h4>
                                        <h5>
                                            <span style="color: {{ $returnStatusColor }}; font-weight: bold;">
                                                {{ $returnStatusText }}
                                            </span>
                                        </h5>
                                    </li>

                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>{{ $currencySymbol }}{{ number_format($grandTotal, 2) }}</h5>
                                    </li>

                                    <li style="border-top:1px dashed #ddd;">
                                        <h4 style="color:#2E7D32;">Paid Amount</h4>
                                        <h5 style="color:#2E7D32;">
                                            {{ $currencySymbol }}{{ number_format($paidAmount, 2) }}
                                        </h5>
                                    </li>

                                    <!-- Return Amount is already shown above, so it's subtracted in pending calculation -->
                                    <li>
                                        <h4 style="color:#C62828;">Pending Amount</h4>
                                        <h5 style="color:#C62828;">
                                            {{ $currencySymbol }}{{ number_format($pendingAmount, 2) }}
                                        </h5>
                                    </li>

                                    @if ($extraPaid > 0)
                                        <li>
                                            <h4 style="color:#d81414;">Extra Paid</h4>
                                            <h5 style="color:#d81414;font-weight:600;">
                                                {{ $currencySymbol }}{{ number_format($extraPaid, 2) }}
                                            </h5>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 100px;">
                        <h2 style="color: #7367F0; font-size: 24px; margin-bottom: 10px;">Thank You for Your Business!</h2>
                        <p style="font-size: 16px; color: #555;">We appreciate your business and hope to serve you again
                            soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.download_pdf');
            const opt = {
                margin: 10,
                filename: '{{ $invoice->invoice_number }}',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };
            html2pdf().set(opt).from(element).save();
        }
    </script>
@endpush
