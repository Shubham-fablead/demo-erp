@extends('layout.app')

@section('title', 'Purchase Details')

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
            <h4>Purchase Details</h4>
            <!-- <h6>View Purchase details</h6> -->
        </div>
        <div class="page-btn">
            <a href="{{ route('purchase.lists') }}" class="btn btn-added">
                Back
            </a>
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

                                        <!-- <hr> -->
                                        <td colspan="6" style="padding: 5px;vertical-align: top;">
                                            <table style="width: 100%;line-height: inherit;text-align: left;"
                                                class="product-list">
                                                <tbody>
                                                    <tr>
                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    Vendor Info</font>
                                                            </font><br>
                                                            <font>
                                                                <font class="customer-name">{{ $vendor->name ?? '--' }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font>{{ $vendor->email ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font class="customer-phone">
                                                                    {{ $vendor->phone ?? '--' }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="customer-phone">
                                                                   <strong>GST No :</strong> {{ $vendor->gst_number ?? '--' }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="customer-phone">
                                                                   <strong>PAN No :</strong> {{ $vendor->pan_number ?? '--' }}
                                                                </font>
                                                            </font><br>

                                                        </td>

                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    Company Info</font>
                                                            </font><br>
                                                            <font>
                                                                <font>{{ $compenyinfo->name ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font>{{ $compenyinfo->email ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font>{{ $compenyinfo->phone ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font>{{ $compenyinfo->address ?? '--' }}</font>
                                                            </font><br>
                                                            <font>
                                                                <font><strong>GST No :</strong> {{ $compenyinfo->gst_num ?? '--' }}</font>
                                                            </font><br>
                                                        </td>

                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    Invoice Info</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Invoice Number</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Payment Status</font>
                                                            </font><br>
                                                            <font>
                                                                <font>Order Status</font>
                                                            </font><br>
                                                        </td>

                                                        <td
                                                            style="padding:5px;vertical-align:top;text-align:right;padding-bottom:20px">
                                                            <font style="vertical-align: inherit;margin-bottom:25px;">
                                                                <font
                                                                    style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">
                                                                    &nbsp;</font>
                                                            </font><br>
                                                            <font>
                                                                <font class="invoice-id">{{ $invoice->invoice_number }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="payment-status">
                                                                    {{ $invoice->paid ? 'Paid' : 'Unpaid' }}
                                                                </font>
                                                            </font><br>
                                                            <font>
                                                                <font class="order-status">
                                                                    {{ ucfirst($invoice->status) }}
                                                                </font>
                                                            </font><br>
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
                                <td style="padding: 10px;">Subtotal</td>
                            </tr>

                            @foreach($invoice->products as $product)
                            <tr class="details" style="border-bottom:1px solid #E9ECEF;">
                                <td style="padding: 10px; vertical-align: top;">
                                    <a href="{{ url('/product-view/' . $product['product_id']) }}"
                                        style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                        <img src="{{ $product['product_image'] }}" alt="img" class="me-2"
                                            style="width:40px; height:40px; object-fit: cover; border-radius: 4px;">
                                        {{ $product['product_name'] }}
                                    </a>
                                </td>

                                <td>
                                    @if ($currencyPosition === 'right')
                                    {{ number_format($product['price'], 2) }}{{ $currencySymbol }}
                                    @else
                                    {{ $currencySymbol }}{{ number_format($product['price'], 2) }}
                                    @endif
                                </td>
                                <td>{{ $product['quantity'] }}</td>
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
                            <ul>
                                <li>
                                    <h4>Subtotal</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->total_amount, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}
                                        @endif
                                    </h5>
                                </li>

                                {{-- Taxes --}}
                                @if(!empty($invoice->taxes) && is_array($invoice->taxes))
                                @foreach($invoice->taxes as $tax)
                                <li>
                                    <h4>{{ $tax['name'] ?? 'Tax' }}
                                        @if(isset($tax['rate']))
                                        ({{ $tax['rate'] }}%)
                                        @endif
                                    </h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($tax['amount'] ?? 0, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($tax['amount'] ?? 0, 2) }}
                                        @endif
                                    </h5>
                                </li>
                                @endforeach
                                @endif

                                <li>
                                    <h4>Shipping</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->shipping, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->shipping, 2) }}
                                        @endif
                                    </h5>
                                </li>
                                <li class="total">
                                    <h4>Total</h4>
                                    <h5>
                                        @if($currencyPosition === 'right')
                                        {{ number_format($invoice->grand_total, 2) }}{{ $currencySymbol }}
                                        @else
                                        {{ $currencySymbol }}{{ number_format($invoice->grand_total, 2) }}
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 100px;">
                    <h2 style="color: #7367F0; font-size: 24px; margin-bottom: 10px;">Thank You for Your Business!</h2>
                    <p style="font-size: 16px; color: #555;">We appreciate your business and hope to serve you again soon.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection