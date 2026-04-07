    <link rel="stylesheet" href="{{ env('ImagePath') . '/admin/assets/css/style.css' }}">

    <div class="card">
        <div class="card-body">
            <tr>
                <td colspan="6">
                    <div class="row purchase_report_head">
                        <div class="col-6">
                            <img src="{{ $settings->logo ? env('ImagePath') . 'storage/' . $settings->logo : env('ImagePath') . 'admin/assets/img/logso.png' }}"
                                style="max-width: 150px;" alt="Logo">

                        </div>
                        <div class="col-6 mt-4" style="text-align: end;">
                            <h2>Purchase Report</h2>
                        </div>
                    </div>
                    <hr>
                </td>
            </tr>
            <div class="download_pdf">
                <div class="invoice-box table-height"
                    style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                    <table style="width: 100%; line-height: inherit; text-align: left;">
                        <tr>
                            <td colspan="6">
                                <table style="width: 100%;" class="purchase_report_table1">
                                    <tr>
                                        <td
                                            style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                            <font style="vertical-align: inherit; margin-bottom:25px;">
                                                <font
                                                    style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                    Vendor Info
                                                </font>
                                            </font><br>

                                            @if (!empty($vendor->name))
                                                <font>
                                                    <font class="vendor-name">{{ $vendor->name }}</font>
                                                </font><br>
                                            @endif

                                            @if (!empty($vendor->email))
                                                <font>
                                                    <font>{{ $vendor->email }}</font>
                                                </font><br>
                                            @endif

                                            @if (!empty($vendor->phone))
                                                <font>
                                                    <font class="vendor-phone">{{ $vendor->phone }}</font>
                                                </font><br>
                                            @endif

                                            <font>
                                                <strong>GST No : </strong>
                                                <font class="gst-no">{{ $vendor->gst_number ?? '--' }}</font>
                                            </font><br>
                                            <font>
                                                <strong>PAN No : </strong>
                                                <font class="pan-no">{{ $vendor->pan_number ?? '--' }}</font>
                                            </font><br>
                                        </td>


                                        <td style="padding: 10px; float: left;">
                                            <strong
                                                style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">Company
                                                Info</strong><br>
                                            {{ $settings->name ?? 'Company Name' }}<br>
                                            {{ $settings->email ?? 'N/A' }}<br>
                                            {{ $settings->phone ?? 'N/A' }}<br>
                                            {{ $settings->address ?? 'N/A' }}<br>
                                            <strong>GST No :</strong> {{ $settings->gst_num ?? 'N/A' }}
                                        </td>

                                        <td style="padding: 10px; float: right;">
                                            <strong
                                                style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">Report
                                                Info</strong><br>
                                            Total Purchases: {{ count($purchases) }}<br>
                                            Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr class="heading" style="background: #F3F2F7;">
                            <td style="padding: 10px;"><strong>Product</strong></td>
                            <td style="padding: 10px;"><strong>Category</strong></td>
                            <td style="padding: 10px;"><strong>Unit Price</strong></td>
                            <td style="padding: 10px;"><strong>Qty</strong></td>
                            <td style="padding: 10px;"><strong>Shipping</strong></td>
                            <td style="padding: 10px;"><strong>Taxes</strong></td>
                            <td style="padding: 10px;"><strong>Total</strong></td>
                        </tr>
                        @php
                            if (!function_exists('formatCurrency')) {
                                function formatCurrency($amount, $symbol = '₹', $position = 'left')
                                {
                                    $formatted = number_format($amount, 2);
                                    return $position === 'left' ? $symbol . $formatted : $formatted . $symbol;
                                }
                            }
                        @endphp

                        @php
                            $subtotal = 0;
                            $shownInvoices = []; // To track which invoices already showed shipping/tax
                            $totalAmountSum = 0;
                        @endphp
                        @foreach ($purchases as $purchase)
                            @php
                                $images = json_decode($purchase->product->images, true);
                                $imagePath =
                                    isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                        ? env('ImagePath') . 'storage/' . $images[0]
                                        : env('ImagePath') . 'admin/assets/img/product/noimage.png';

                                $total = $purchase->amount_total;
                                $subtotal += $total;

                                $unitPrice = $purchase->quantity ? $purchase->amount_total / $purchase->quantity : 0;

                                $invoiceId = $purchase->invoice->id ?? null;
                                $displayShipping = '-';
                                $displayTax = '-';
                                $displayTotal = $purchase->amount_total;

                                $numericTotal = $purchase->amount_total;

                                if ($invoiceId && !in_array($invoiceId, $shownInvoices)) {
                                    $invoiceShipping = $purchase->invoice->shipping ?? 0;
                                    $taxes = json_decode($purchase->invoice->taxes, true) ?? [];
                                    $totalTax = array_sum(array_column($taxes, 'amount'));

                                    $displayShipping = formatCurrency(
                                        $invoiceShipping,
                                        $currencySymbol,
                                        $currencyPosition,
                                    );
                                    $displayTax = formatCurrency($totalTax, $currencySymbol, $currencyPosition);
                                    $displayTotal = formatCurrency(
                                        $purchase->amount_total + $invoiceShipping + $totalTax,
                                        $currencySymbol,
                                        $currencyPosition,
                                    );

                                    $numericTotal += $invoiceShipping + $totalTax;

                                    $shownInvoices[] = $invoiceId; // mark invoice as shown AFTER using its shipping/tax
                                }

                                $totalAmountSum += $numericTotal;
                            @endphp



                            <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                                <td style="padding: 10px; vertical-align: middle;">
                                    <a href="{{ url('product-view/' . ($purchase->product->id ?? '')) }}"
                                        style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                                        <img src="{{ $imagePath }}" alt="Product Image"
                                            style="width: 50px; height: 50px;">
                                        <div style="font-weight: 500;">{{ $purchase->product->name ?? '-' }}</div>
                                    </a>
                                </td>

                                <td style="padding: 10px; vertical-align: middle;">
                                    {{ $purchase->product->category->name ?? 'N/A' }}</td>
                                <td style="padding: 10px; vertical-align: middle;">
                                    {{ formatCurrency($unitPrice, $currencySymbol, $currencyPosition) }}
                                </td>
                                <td style="padding: 10px; vertical-align: middle;">{{ $purchase->quantity }}</td>
                                <td style="padding: 10px; vertical-align: middle;">{!! $displayShipping !!}</td>
                                <td style="padding: 10px; vertical-align: middle;">{!! $displayTax !!}</td>
                                <td style="padding: 10px; vertical-align: middle;">{!! $displayTotal !!}</td>
                            </tr>
                        @endforeach

                    </table>

                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <!-- You can add extra content here if needed -->
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="total">
                                        <h4>Total Amount</h4>
                                        <h5>
                                            @if ($currencyPosition == 'left')
                                                {{ $currencySymbol }}{{ number_format($totalAmountSum, 2) }}
                                            @else
                                                {{ number_format($totalAmountSum, 2) }}{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Move this outside the invoice content -->
            </div>

        </div>
    </div>
