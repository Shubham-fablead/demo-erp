    <link rel="stylesheet" href="{{ env('ImagePath') . '/admin/assets/css/style.css' }}">

    <div class="card">
        <div class="card-body">
            <input type="hidden" name="selse_id" id="selse_id" value="">
            <tr class="top">
                <td colspan="6">
                    <div class="row purchase_report_head">
                        <div class="col-6">
                            <img src="{{ $settings->logo ? env('ImagePath') . 'storage/' . $settings->logo : env('ImagePath') . 'admin/assets/img/logso.png' }}"
                                style="max-width: 150px;">

                        </div>
                        <div class="col-6 mt-4" style=" text-align: end;">
                            <h2>Sales Report</h2>
                        </div>
                    </div>

                </td>
            </tr>
            <div class="download_pdf">
                <div class="invoice-box table-height"
                    style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                    <table style="width: 100%; line-height: inherit; text-align: left;">
                        <tr>
                            <td colspan="12">
                                <table style="width: 100%;">
                                    <tr>
                                        <td
                                            style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px">
                                            <font style="vertical-align: inherit; margin-bottom:25px;">
                                                <font
                                                    style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">
                                                    User Info
                                                </font>
                                            </font><br>

                                            @if (!empty($user->name))
                                                <font>
                                                    <font class="vendor-name">{{ $user->name }}</font>
                                                </font><br>
                                            @endif

                                            @if (!empty($user->email))
                                                <font>
                                                    <font>{{ $user->email }}</font>
                                                </font><br>
                                            @endif

                                            @if (!empty($user->phone))
                                                <font>
                                                    <font class="vendor-phone">{{ $user->phone }}</font>
                                                </font><br>
                                            @endif

                                            <font>
                                                <strong>GST No : </strong>
                                                <font class="gst-no">{{ $user->gst_number ?? '--' }}</font>
                                            </font><br>
                                            <font>
                                                <strong>PAN No : </strong>
                                                <font class="pan-no">{{ $user->pan_number ?? '--' }}</font>
                                            </font><br>
                                        </td>

                                        <td style="padding: 10px; float: left;">
                                            <strong style="font-size:14px; color:#7367F0; font-weight:600;">Company
                                                Info</strong><br>
                                            {{ $settings->name ?? 'Company Name' }}<br>
                                            {{ $settings->email ?? 'N/A' }}<br>
                                            {{ $settings->phone ?? 'N/A' }}<br>
                                            {{ $settings->address ?? 'N/A' }}<br>
                                            GST: {{ $settings->gst_num ?? 'N/A' }}<br>
                                        </td>

                                        <td style="padding: 10px; float: right;">
                                            <strong style="font-size:14px; color:#7367F0; font-weight:600;">Report
                                                Info</strong><br>
                                            Total Sales: {{ count($sales) }}<br>
                                            Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr class="heading" style="background: #F3F2F7;">
                            <td style="padding: 10px;"><strong>Product</strong></td>
                            <td style="padding: 10px;"><strong>Category</strong></td>
                            <td style="padding: 10px;"><strong>Original Price</strong></td>
                            <td style="padding: 10px;"><strong>Discount</strong></td>
                            <td style="padding: 10px;"><strong>Final Unit Price</strong></td>
                            <td style="padding: 10px;"><strong>Quantity</strong></td>
                            <td style="padding: 10px;"><strong>Taxes</strong></td>
                            <td style="padding: 10px;"><strong>Total</strong></td>
                        </tr>

                        @php $subtotal = 0; @endphp
                        @foreach ($sales as $sale)
                            @php
                                $discountPercent = $sale->invoice->discount ?? 0;
                                $originalUnitPrice = $sale->quantity ? $sale->total_amount / $sale->quantity : 0;
                                $discountPerUnit = ($originalUnitPrice * $discountPercent) / 100;
                                $finalUnitPrice = $originalUnitPrice - $discountPerUnit;
                                $finalTotal = $finalUnitPrice * $sale->quantity;

                                $subtotal += $finalTotal;

                                $images = json_decode($sale->product->images, true);
                                $imagePath =
                                    isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                        ? env('ImagePath') . 'storage/' . $images[0]
                                        : env('ImagePath') . 'admin/assets/img/product/noimage.png';
                            @endphp

                            <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                                <td style="padding: 10px; white-space: normal;">
                                    <a href="{{ url('product-view/' . ($sale->product->id ?? '')) }}"
                                        style="display: flex; align-items: center; gap: 10px; text-decoration: none; color: inherit;">
                                        <img src="{{ $imagePath }}" alt="Product Image"
                                            style="width: 50px; height: 50px;">
                                        <div style="font-weight: 500;">{{ $sale->product->name ?? '-' }}</div>
                                    </a>
                                </td>
                                <td style="padding: 10px; white-space: normal;">
                                    {{ $sale->product->category->name ?? 'N/A' }}</td>
                                <td style="padding: 10px;">
                                    {{ $currencyPosition === 'left' ? $currencySymbol . number_format($originalUnitPrice, 2) : number_format($originalUnitPrice, 2) . $currencySymbol }}
                                </td>
                                <td style="padding: 10px;">{{ $discountPercent }}%</td>
                                <td style="padding: 10px;">
                                    {{ $currencyPosition === 'left' ? $currencySymbol . number_format($finalUnitPrice, 2) : number_format($finalUnitPrice, 2) . $currencySymbol }}
                                </td>
                                <td style="padding: 10px;">{{ $sale->quantity }}</td>
                                <td style="padding: 10px;">
                                    @if ($sale->rowGSTOption === 'with_gst' && !empty($sale->rowTaxes))
                                        @foreach ($sale->rowTaxes as $t)
                                            <div>
                                                {{ $t['name'] }} ({{ $t['rate'] }}%) :
                                                {{ $currencyPosition === 'left' ? $currencySymbol . number_format($t['amount'], 2) : number_format($t['amount'], 2) . $currencySymbol }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span>N/A</span>
                                    @endif
                                </td>
                                <td style="padding: 10px;">
                                    {{ $currencyPosition === 'left' ? $currencySymbol . number_format($sale->rowFinalTotal, 2) : number_format($sale->rowFinalTotal, 2) . $currencySymbol }}
                                </td>

                            </tr>
                        @endforeach

                    </table>

                    <div class="row mt-3">
                        <div class="col-lg-6"></div>
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="total">
                                        <h4>Total Amount</h4>
                                        <h5>
                                            {{ $currencyPosition === 'left'
                                                ? $currencySymbol . number_format($totalAmount, 2)
                                                : number_format($totalAmount, 2) . $currencySymbol }}
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>



                </div>
            </div>

        </div>
    </div>
