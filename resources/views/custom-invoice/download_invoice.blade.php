@extends('layout.app')

@section('title', 'Custom Invoice Details')

@section('content')
<style>
    .details td {
        vertical-align: middle !important
    }

    .invoice-box {
        overflow-x: auto;
    }

    .invoice-box table {
        width: 100%;
        border-collapse: collapse;
    }

    .invoice-box table td {
        white-space: nowrap;
    }

    .product-name-column {
        white-space: normal !important;
        min-width: 150px;
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

    .address-wrap {
        white-space: normal !important;
        word-break: break-word;
        line-height: 1.5;
        display: block;
    }

    .page-btn {
        display: flex;
        gap: 8px;
    }

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

    }


    /* Adjust columns for mobile, keep same layout */
    @media (max-width: 767px) {
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

        font {
            line-height: 19px !important;
        }
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Invoice </h4>
            <!-- <h6>View Purchase details</h6> -->
        </div>
        <div class="page-btn d-flex gap-2">
            <!-- ✅ Add Sale -->
            @if (app('hasPermission')(4, 'add'))
            <a href="{{ route('custom_invoice.add') }}" class="btn btn-added">
                <i class="fa fa-plus me-1"></i> Add Invoice
            </a>
            @endif
            
             @if (app('hasPermission')(4, 'edit'))
             @if (!$hasPaymentStarted)
            <a href="{{ url('edit-custom-invoice/' . $invoice->id) }}" class="btn btn-primary">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
            @endif
             @endif

            <!-- ✅ Print Invoice -->
            @if (app('hasPermission')(4, 'view'))
            <a href="{{ route('custom_invoice.pdf', $invoice->id) }}" target="_blank" class="btn btn-primary">
                <i class="fa fa-print me-1"></i> Print
            </a>

            <!-- Back -->
            <a href="{{ route('custom_invoice.lists') }}" class="btn" style="background: #1b2850; color: #fff;">
                Back
            </a>
                @endif
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="download_pdf">
                <div class="invoice-box table-height"
                    style="max-width: 1600px;width:100%;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                    <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                        <tbody id="product-details">

                            <tr class="top">
                                <td colspan="6" style="padding: 5px;vertical-align: top;">
                                    <table style="width: 100%;line-height: inherit;text-align: left;"
                                        class="product-list">
                                        <tbody>
                                            <tr>
                                                <td colspan="3" style="padding: 5px;vertical-align: top;">
                                                    <img src="{{ $compenyinfo->logo ? env('ImagePath') . 'storage/' . $compenyinfo->logo : env('ImagePath') . '/admin/assets/img/logso.png' }}"
                                                        alt="logo" class="logo_img">
                                                </td>
                                                <td colspan="3" style="padding: 5px;vertical-align: top;text-align: right;">
                                                    <h1>Invoice</h1>
                                                    <h4 class="mt-3">#{{ $invoice->invoice_number }}</h4>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6">
                                                    <hr>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" style="padding: 5px;vertical-align: top;">
                                                    <table style="width: 100%;line-height: inherit;text-align: left;"
                                                        class="product-list">
                                                        <tbody>
                                                            <tr>
                                                                <td class="info-column"
                                                                    style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font
                                                                            style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                                            {{ $vendor->role === 'vendor' ? 'Vendor Info' : 'Customer Info' }}
                                                                        </font>
                                                                    </font><br>

                                                                    @if (!empty($vendor->name))
                                                                    <font>
                                                                        <font class="customer-name">{{ $vendor->name }}
                                                                        </font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($vendor->email))
                                                                    <font>
                                                                        <font>{{ $vendor->email }}</font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($vendor->phone))
                                                                    <font>
                                                                        <font class="customer-phone">{{ $vendor->phone }}
                                                                        </font>
                                                                    </font><br>
                                                                    @endif

                                                                    <font>
                                                                        <strong>GST No. : </strong>
                                                                        <font class="gst-no">{{ $vendor->gst_number ?? '--' }}
                                                                        </font>
                                                                    </font><br>
                                                                    <font>
                                                                        <strong>PAN No. : </strong>
                                                                        <font class="pan-no">{{ $vendor->pan_number ?? '--' }}
                                                                        </font>
                                                                    </font><br>
                                                                </td>


                                                                <td class="info-column"
                                                                    style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                                        <font
                                                                            style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                                            Company Info
                                                                        </font>
                                                                    </font><br>

                                                                    @if (!empty($compenyinfo->name))
                                                                    <font>
                                                                        <font>{{ $compenyinfo->name }}</font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($compenyinfo->email))
                                                                    <font>
                                                                        <font>{{ $compenyinfo->email }}</font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($compenyinfo->phone))
                                                                    <font>
                                                                        <font>{{ $compenyinfo->phone }}</font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($compenyinfo->address))
                                                                    <font>
                                                                        <font class="address-wrap">{{ $compenyinfo->address }}</font>
                                                                    </font><br>
                                                                    @endif

                                                                    @if (!empty($compenyinfo->gst_num))
                                                                    <font>
                                                                        <font><strong>GST No. : </strong>
                                                                            {{ $compenyinfo->gst_num }}
                                                                        </font>
                                                                    </font><br>
                                                                    @endif
                                                                </td>


                                                                <td class="info-column"
                                                                    style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                                    <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                        <font
                                                                            style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                            Invoice Info</font>
                                                                    </font><br>
                                                                    <table style="width: 100%; font-size: 14px;">
                                                                        <tr>
                                                                            <td style="text-align: left;">Invoice Number</td>
                                                                            <td style="text-align: right;">{{ $invoice->invoice_number }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="text-align: left;">Payment Status</td>
                                                                            <td style="text-align: right;">{{ $invoice->paid ? 'Paid' : 'Unpaid' }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="text-align: left;">Order Status</td>
                                                                            <td style="text-align: right;">{{ ucfirst($invoice->status) }}</td>
                                                                        </tr>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                    </table>
                                </td>
                            </tr>

                            <tr class="heading" style="background: #F3F2F7;">
                                <td style="padding: 10px;">Product Name</td>
                                <td style="padding: 10px;">Price</td>
                                <td style="padding: 10px;">QTY</td>
                                @if ($hasAnyProductTax)
                                <td style="padding: 10px;">Product Taxes</td>
                                <td style="padding: 10px;">Tax Amount</td>
                                @endif
                                <td style="padding: 10px;">Subtotal</td>
                            </tr>

                            @foreach ($invoice->products as $product)
                            <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                <!-- <td style="padding: 10px;vertical-align: top; display: flex;align-items: center;">
                                                    <img src="{{ $product['product_image'] }}" alt="img" class="me-2"
                                                        style="width:40px;height:40px;">
                                                    {{ $product['product_name'] }}
                                                </td> -->

                                <td class="product-name-column" style="padding: 10px; vertical-align: middle;">
                                    <a href="{{ url('/product-view/' . $product['product_id']) }}"
                                        style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                        <img src="{{ $product['product_image'] }}" alt="img" class="me-2"
                                            style="width:40px;height:40px; flex-shrink: 0;">
                                        <span>{{ $product['product_name'] }}</span>
                                    </a>
                                </td>

                                <td>
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['price'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['price'], 2) }}
                                    @endif
                                </td>
                                <!-- <td>{{ $product['quantity'] }}</td>
                                <td style="padding: 10px;">
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['total'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['total'], 2) }}
                                    @endif
                                </td> -->
                                <td>{{ $product['quantity'] }}</td>
                                @if ($hasAnyProductTax)
                                {{-- Product Taxes --}}
                                <td style="padding: 10px;">
                                    @if (!empty($product['taxes']) && is_array($product['taxes']))
                                    @foreach ($product['taxes'] as $tax)
                                    <div>
                                        {{ $tax['name'] ?? 'Tax' }}
                                        @if(isset($tax['rate']))
                                        ({{ $tax['rate'] }}%)
                                        @endif
                                        :
                                        {{ $currencySymbol }}{{ number_format($tax['amount'] ?? 0, 2) }}
                                    </div>
                                    @endforeach
                                    @else
                                    N/A
                                    @endif
                                </td>

                                {{-- Tax Amount (Total tax for this product) --}}
                                <td style="padding: 10px;">
                                    @php
                                    $productTaxTotal = 0;
                                    if (!empty($product['taxes']) && is_array($product['taxes'])) {
                                    foreach ($product['taxes'] as $tax) {
                                    $productTaxTotal += $tax['amount'] ?? 0;
                                    }
                                    }
                                    @endphp

                                    @if ($currencyPosition === 'right')
                                    {{ number_format($productTaxTotal, 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($productTaxTotal, 2) }}
                                    @endif
                                </td>
                                @endif

                                {{-- Subtotal (Excluding tax) --}}
                                <td style="padding: 10px;">
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['total'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['total'], 2) }}
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="row">
                    <div class="col-lg-6"></div>
                    <div class="col-lg-6">
              <div class="total-order w-100 max-widthauto m-auto mb-4">
    @php
        // =============================
        // BASE VALUES
        // =============================
        $subtotal = $invoice->total_amount ?? 0;
        $shipping = $invoice->shipping ?? 0;
        $discountPercent = $invoice->discount ?? 0;

        // =============================
        // CALCULATE TOTAL GST FROM PRODUCTS (SINGLE SOURCE)
        // =============================
        $totalGST = 0;
        $taxBreakdown = [];

        // Loop through products to calculate total tax
        if (!empty($invoice->products) && is_array($invoice->products)) {
            foreach ($invoice->products as $product) {
                if (!empty($product['taxes']) && is_array($product['taxes'])) {
                    foreach ($product['taxes'] as $tax) {
                        $taxAmount = (float) ($tax['amount'] ?? 0);
                        $totalGST += $taxAmount;

                        // Create tax breakdown for display
                        $taxName = $tax['name'] ?? 'Tax';
                        $taxRate = isset($tax['rate']) ? ' (' . $tax['rate'] . '%)' : '';
                        $key = $taxName . $taxRate;

                        if (!isset($taxBreakdown[$key])) {
                            $taxBreakdown[$key] = 0;
                        }
                        $taxBreakdown[$key] += $taxAmount;
                    }
                }
            }
        }

        // =============================
        // DISCOUNT CALCULATION
        // =============================
        // Calculate discount on subtotal + GST (as shown in your image)
        $discountBase = $subtotal + $totalGST;
        $discountAmount = ($discountBase * $discountPercent) / 100;

        // Amount after discount (subtotal - discount)
        $discountAfterAmount = $subtotal - $discountAmount;
        if ($discountAfterAmount < 0) {
            $discountAfterAmount = 0;
        }

        // =============================
        // GRAND TOTAL
        // =============================
        // Grand total = (Subtotal - Discount) + Shipping + Total GST
        $calculatedGrandTotal = $discountAfterAmount + $shipping + $totalGST;

        // Use invoice grand total if provided, otherwise use calculated
        $displayGrandTotal = $invoice->grand_total ?? $calculatedGrandTotal;

        // Paid amount
        $paidAmount = $paidAmount ?? 0;
        $pendingAmount = $displayGrandTotal - $paidAmount;
    @endphp

    <ul>
        {{-- Subtotal --}}
        <li>
            <h4>Subtotal</h4>
            <h5>
                @if ($currencyPosition === 'right')
                    {{ number_format($subtotal, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                @endif
            </h5>
        </li>

        {{-- Display Individual Taxes --}}
        {{-- @if (!empty($taxBreakdown))
            @foreach ($taxBreakdown as $taxName => $taxAmount)
            <li>
                <h4>{{ $taxName }}</h4>
                <h5>
                    @if ($currencyPosition === 'right')
                        {{ number_format($taxAmount, 2) }}{{ $currencySymbol }}
                    @else
                        {{ $currencySymbol }}{{ number_format($taxAmount, 2) }}
                    @endif
                </h5>
            </li>
            @endforeach
        @endif --}}

        {{-- Discount --}}
        @if (!empty($invoice->discount) && $invoice->discount > 0)
        <li>
            <h4>Discount ({{ $invoice->discount }}%)</h4>
            <h5>
                @if ($currencyPosition === 'right')
                    -{{ number_format($discountAmount, 2) }}{{ $currencySymbol }}
                @else
                    -{{ $currencySymbol }}{{ number_format($discountAmount, 2) }}
                @endif
            </h5>
        </li>

        {{-- Amount After Discount (CORRECT VALUE) --}}
        <li>
            <h4>Amount After Discount</h4>
            <h5 style="font-weight:600;">
                @if ($currencyPosition === 'right')
                    {{ number_format($discountAfterAmount, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($discountAfterAmount, 2) }}
                @endif
            </h5>
        </li>
        @endif

        {{-- Total GST (Summary) --}}
        @if ($totalGST > 0)
        <li>
            <h4>Total GST</h4>
            <h5>
                @if ($currencyPosition === 'right')
                    {{ number_format($totalGST, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($totalGST, 2) }}
                @endif
            </h5>
        </li>
        @endif

        {{-- Shipping --}}
        @if (!empty($invoice->shipping) && $invoice->shipping > 0)
        <li>
            <h4>Shipping</h4>
            <h5>
                @if ($currencyPosition === 'right')
                    {{ number_format($invoice->shipping, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($invoice->shipping, 2) }}
                @endif
            </h5>
        </li>
        @endif

        {{-- Grand Total --}}
        <li class="total">
            <h4>Grand Total</h4>
            <h5>
                @if ($currencyPosition === 'right')
                    {{ number_format($displayGrandTotal, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($displayGrandTotal, 2) }}
                @endif
            </h5>
        </li>

        {{-- Paid Amount --}}
        <li style="border-top:1px dashed #ddd; padding-top:10px;">
            <h4 style="color:#2E7D32;">Paid Amount</h4>
            <h5 style="color:#2E7D32;font-weight:600;">
                @if ($currencyPosition === 'right')
                    {{ number_format($paidAmount, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($paidAmount, 2) }}
                @endif
            </h5>
        </li>

        {{-- Pending Amount --}}
        <li>
            <h4 style="color:#C62828;">Pending Amount</h4>
            <h5 style="color:#C62828;font-weight:600;">
                @if ($currencyPosition === 'right')
                    {{ number_format($pendingAmount, 2) }}{{ $currencySymbol }}
                @else
                    {{ $currencySymbol }}{{ number_format($pendingAmount, 2) }}
                @endif
            </h5>
        </li>
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
