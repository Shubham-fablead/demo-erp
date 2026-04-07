
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Invoice PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            /* Set base font size here */
            margin: 0;
            padding: 0;
            background: white;
        }

        .pdf-wrapper {
            margin-top: 3mm;
            /* ensures top border is visible */
        }

        .card-body {
            width: 95%;
            min-height: 95%;
            padding: 3mm;
            margin: auto;
            box-sizing: border-box;
            background: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border: 1px solid black;
            font-size: 12px;
        }

        table,
        table td,
        table th {
            font-size: inherit;
            /* Make sure tables inherit the font size */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* margin-bottom: 20px; */
        }

        .header-table {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            margin-bottom: 30px;
        }

        .header-table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
        }

        .table-bordered thead tr {
            background-color: #e9ecf0ff;
            color: #333;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-bordered tfoot tr {
            /* font-weight: bold; */
            /* background-color: #e9ecef; */
        }

        .text-end {
            /* text-align: right; */
        }

        .text-center {
            text-align: center;
        }

        .mb-0 {
            /* margin-bottom: 0; */
        }

        h3,
        h4 {
            margin: 0 0 10px 0;
            color: #343a40;
        }

        .invoice-title {
            /* text-transform: uppercase; */
            /* letter-spacing: 2px; */
            /* border-bottom: 2px solid #e5e7ebff; */
            /* display: inline-block; */
            /* padding-bottom: 5px; */
            /* margin-bottom: 20px; */
        }

        .logo-container {
            /* position: relative; */
            /* min-height: 50px; */
            /* margin-bottom: 10px; */
        }

        /* .logo-container .qr-code {
            height: 60px;
            position: absolute;
            top: 0;
            left: 0;
        } */

        /* .logo-container .company-logo {
            height: 50px;
            position: absolute;
            top: 0;
            left: 0;
        } */

        /* .logo-container .company-details {
            text-align: center;
        } */


        /* .signature-section img {
            height: 50px;
            margin-top: 5px;
        } */

        /* .signature-section {
            margin-top: 50px;
            text-align: right;
        } */
         /* ===== PRODUCT NAME WRAP FIX ===== */



        @php
            $totalthing = count($orderItems);
            $footerBottom = ($totalthing > 5) ? 60 : 45;
        @endphp

        .footer-section {
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="card-body">
        <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
            <tr>
                <td style="width: 150px; vertical-align: top;">
                    @if (isset($setting->logo) && file_exists(storage_path('app/public/' . $setting->logo)))
                        @php
                            $logoPath = storage_path('app/public/' . $setting->logo);
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        @endphp
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                            style="height: 100px; width: auto;"> {{-- adjust height as needed --}}
                    @endif
                </td>
                <td style="vertical-align: middle; padding-left: 15px; text-align: right; word-wrap: break-word; white-space: normal; max-width: 300px;">
                    <h3 style="margin: 0; text-transform: uppercase;">{{ $setting->name ?? '' }}</h3>
                    <small style="text-transform: uppercase; display: block;">
                        {{ $setting->address ?? '' }}<br>
                        Phone: {{ $setting->phone ?? '' }} |
                        Email: <span style="text-transform: none;">{{ $setting->email ?? '' }}</span>
                    </small>
                </td>
            </tr>
        </table>

        <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">


        <div class="text-center mb-1">
            <!-- <h3 class="mb-3" style="text-transform: uppercase;">Bill of Supply</h3> -->
            <div style="margin-bottom: 10px; width: 100%; text-align: center;">
                <div style="display: inline-block; width: 49%; text-align: left;">
                    <strong>INVOICE NO : {{ $sales->order_number ?? '-' }}</strong>
                </div>
                <div style="display: inline-block; width: 49%; text-align: right;">
                    <strong>GST NO : {{ $setting->gst_num ?? ' N/A ' }}</strong>
                </div>
            </div>
        </div>

        <table style="width:100%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px; table-layout: fixed;">
            <tr>
                <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Customer
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Name :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">
                                {{ $customer['name'] ?? 'walk-in-customer' }}
                            </td>
                        </tr>
                        @if (!empty($customer['phone']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $customer['phone'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['email']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $customer['email'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['address']))
                            <tr>
                                <td style="padding: 0 0 8px 0; vertical-align: top;">Address :</td>
                                <td style="text-align: right; padding: 0 0 8px 0; word-wrap: break-word; white-space: normal;">{{ $customer['address'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['gst_number']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">GST :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $customer['gst_number'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($customer['pan_number']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">PAN :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $customer['pan_number'] }}</td>
                            </tr>
                        @endif
                    </table>

                    <!-- Half vertical line -->
                    <div style="position: absolute; right: 0; top: 2%; height: 12%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>

                <!-- Vehicle Details -->
                <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Company
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        @if (!empty($setting->name))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Name :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->name }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->email))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->email }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->phone))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->phone }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->address))
                            <tr>
                                <td style="padding: 0 0 8px 0; vertical-align: top;">Address :</td>
                                <td style="text-align: right; padding: 0 0 8px 0; word-wrap: break-word; white-space: normal;">{{ $setting->address }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->gst_num))
                            <tr>
                                <td style="padding: 0 0 8px 0;">GST :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->gst_num }}</td>
                            </tr>
                        @endif
                    </table>

                    <div style="position: absolute; right: 0; top: 2%; height: 12%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>
                @php
                    $isQuotation = ($sales->quotation_status ?? '') === 'quotation';
                @endphp


                <!-- Invoice Details -->
                                    <td
                        style="width:34%; border: 0px solid #dee2e6; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">

                        <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">
                            {{ $isQuotation ? 'Quotation Info:' : 'Order Details:' }}
                        </strong>

                        <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">

                            {{-- Number --}}
                            <tr>
                                <td style="padding: 0 0 8px 0;">
                                    {{ $isQuotation ? 'Quotation Number :' : 'Order Number :' }}
                                </td>
                                <td style="text-align: right; padding: 0 0 8px 0;">
                                    {{ $sales->order_number ?? '-' }}
                                </td>
                            </tr>

                            {{-- Date --}}
                            <tr>
                                <td style="padding: 0 0 8px 0;">
                                    {{ $isQuotation ? 'Quotation Date :' : 'Order Date :' }}
                                </td>
                                <td style="text-align: right; padding: 0 0 8px 0;">
                                    {{ !empty($sales->created_at)
                                        ? date('d M Y, h:i A', strtotime($sales->created_at))
                                        : '-' }}
                                </td>
                            </tr>

                            {{-- Show payment info ONLY for orders --}}
                            @if(!$isQuotation)
                            <tr>
                                <td style="padding: 0 0 8px 0;">Payment Status :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">
                                    {{ ucfirst($sales->payment_status ?? '-') }}
                                </td>
                            </tr>

                            <tr>
                                <td style="padding: 0 0 8px 0;">Payment Method :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">
                                    {{ ucfirst($sales->payment_method ?? '-') }}
                                </td>
                            </tr>
                            @endif

                        </table>
                    </td>
            </tr>
        </table>

        @php
            $hasGst = false;

            foreach ($orderItems as $item) {
                if (
                    (!empty($item->product_gst_total) && $item->product_gst_total > 0) ||
                    (!empty($item->product_gst_details) && is_array($item->product_gst_details))
                ) {
                    $hasGst = true;
                    break;
                }
            }
        @endphp
        <div class="text-center">
            <h4 style="text-transform: uppercase;">Product</h4>
        </div>

        <table class="table-bordered"
            style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 10px 0 7px 0;">
            <thead>
                <tr style="background-color:#ff9f43; color:#fff;">
                    <th style="width:10%; padding: 8px; text-align:center;">Sr No</th>
                    <th style="padding: 8px;width:20%;  text-align:left;">Product Name</th>
                    <th style="padding: 8px; text-align:left;">Unit</th>
                    <th style="width:8%; padding: 8px; text-align:center;">Qty</th>
                    <th style="padding: 8px; text-align:center;">Price</th>
                    <th style="padding: 8px; text-align:center;">Discount Amount</th>

                    @if($hasGst)
                        <th style="width:20%; padding: 8px; text-align:center;">Product Taxes</th>
                        <th style="width:12%; text-align:center;">Tax Amount</th>
                     @endif
                    <th style="width:22%; text-align:center;">Total (Excl.GST)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orderItems as $item)
                    <tr>
                        <td style="text-align:center; padding:8px;">{{ $loop->iteration }}</td>
                       <td style="padding:8px; text-align:left;">
                        @php
                            $images = json_decode($item->product->images ?? '[]');
                            $firstImage = !empty($images) ? $images[0] : null;
                            $base64 = null;

                            if ($firstImage) {
                                $imagePath = storage_path('app/public/' . $firstImage);
                            } else {
                                $imagePath = public_path('/admin/assets/img/product/noimage.png');
                            }

                            if (file_exists($imagePath)) {
                                $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                                $data = file_get_contents($imagePath);
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                            }
                        @endphp

                        <table style="border-collapse: collapse; width:100%; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 30px;">
                                <col style="width: 70px;">
                            </colgroup>
                            <tr>
                                <td style="padding: 0; width: 20%; vertical-align: middle;">
                                    <img src="{{ $base64 }}" alt="img"
                                        style="width: 30px; height: 40px; object-fit: cover; border-radius: 4px; display: block;">
                                </td>
                                <td style="width: 50px;
                                    padding: 0 0 0 6px;
                                    vertical-align: middle;
                                    word-wrap: break-word;
                                    word-break: break-word;
                                    white-space: normal;
                                    overflow-wrap: break-word;
                                    width: 70%;
                                ">
                                    {{ ucfirst($item->product->name ?? 'Product') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                          <td class="product-name">
                            {{ ucfirst($item->product->unit->unit_name ?? 'N/A') }}
                        </td>
                         <td style="padding:8px; text-align:center;">
                            {{ $item->quantity }}
                        </td>
                        <td style="padding:8px; text-align:center;">
                            @if ($setting->currency_position === 'right')
                                {{ $item->price }}{{ $setting->currency_symbol }}
                            @else
                                {{ $setting->currency_symbol }}{{ $item->price }}
                            @endif
                        </td>
                        <td style="padding:8px; text-align:center;">

                    @php
                        $discountAmount = (float)($item->discount_amount ?? 0);
                        $discountPercentage = (float)($item->discount_percentage ?? 0);
                    @endphp

                    {{-- Discount Amount --}}
                    {{ $setting->currency_symbol }}{{ number_format($discountAmount, 2) }}

                    {{-- Show percentage only if applied --}}
                    @if($discountPercentage > 0)
                        <small>({{ rtrim(rtrim(number_format($discountPercentage,2), '0'), '.') }}%)</small>
                    @endif

                </td>
                    @php
                        // ✅ GST total per product
                        $productGstTotal = $item->product_gst_total ?? 0;

                        // ✅ Excluding GST total
                        $totalExclGst = $item->price * $item->quantity;
                        $rowGstDetails = $item->product_gst_details;
                        if (is_string($rowGstDetails)) {
                            $rowGstDetails = json_decode($rowGstDetails, true);
                            if (is_string($rowGstDetails)) {
                                $rowGstDetails = json_decode($rowGstDetails, true);
                            }
                        }
                        if (is_array($rowGstDetails) && isset($rowGstDetails['tax_name'])) {
                            $rowGstDetails = [$rowGstDetails];
                        }
                    @endphp
                         @if($hasGst)
                        <!-- Product Taxes -->
                       <td style="padding:8px; text-align:center; font-size:10px;">
                            @if(!empty($rowGstDetails) && is_array($rowGstDetails))
                                @foreach($rowGstDetails as $tax)
                                    <div>
                                        {{ $tax['tax_name'] ?? 'GST' }} ({{ $tax['tax_rate'] ?? 0 }}%)
                                        : {{ $setting->currency_symbol }}{{ number_format((float)($tax['tax_amount'] ?? 0), 2) }}
                                    </div>
                                @endforeach
                            @else
                                N/A
                            @endif
                        </td>
                        {{-- <td style="padding:8px; text-align:right;">
                            @if ($setting->currency_position === 'right')
                                {{ $item->total_amount }}{{ $setting->currency_symbol }}
                            @else
                                {{ $setting->currency_symbol }}{{ $item->total_amount }}
                            @endif
                        </td> --}}


                    <!-- ✅ TAX AMOUNT COLUMN -->
                    <td style="padding:8px; text-align:right; font-weight:bold;">
                        {{ $setting->currency_symbol }}{{ number_format($productGstTotal, 2) }}
                    </td>
                     @endif
                    <!-- ✅ TOTAL (EXCL. GST) -->
                    <td style="padding:8px; text-align:center;">
                        {{ $setting->currency_symbol }}{{ number_format($totalExclGst, 2) }}
                    </td>
                </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:8px;">No product data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>


                {{-- ✅ Show only when quotation AND labour items exist --}}
                @if( isset($labourItems) && $labourItems->isNotEmpty())

                <div class="text-center">
                    <h4 style="text-transform: uppercase;">Labour Items</h4>
                </div>

                <table class="table-bordered"
                    style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 10px 0 7px 0;">

                    <thead>
                        <tr style="background-color:#ff9f43; color:#fff;">
                            <th style="width:10%; padding:8px; text-align:center;">Sr No</th>
                            <th style="padding:8px; text-align:left;">Labour Name</th>
                            <th style="width:8%; padding:8px; text-align:center;">Qty</th>
                            <th style="padding:8px; text-align:center;">Price</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($labourItems as $labour)
                        <tr>
                            <td style="text-align:center;">{{ $loop->iteration }}</td>

                            <td>
                                {{ $labour->labourItem->item_name ?? 'Labour' }}
                            </td>

                            <td style="text-align:center;">
                                {{ $labour->qty ?? 0 }}
                            </td>

                            <td style="text-align:center;">
                                {{ $setting->currency_symbol }}
                                {{ number_format($labour->price ?? 0, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                </table>

                @endif


            {{-- GST Details --}}
            {{-- @if ($sales->gst_option == 'with_gst' && !empty($taxDetails1))
                @foreach ($taxDetails1 as $tax)
                    <tr>
                        <td
                            style="padding: 6px 12px; text-align: left; background-color: #ff9f43; color:#fff; font-weight:bold; border: 1px solid #e0e0e0;">
                            {{ $tax['name'] }} ({{ $tax['rate'] }}%)
                        </td>
                        <td style="padding: 6px 12px; text-align: right; border: 1px solid #e0e0e0;">
                            {{ $tax['formatted_amount'] }}
                        </td>
                    </tr>
                @endforeach
            @endif --}}


        @php
            function convertNumberToWords($number)
            {
                $hyphen = '-';
                $conjunction = ' and ';
                $negative = 'negative ';
                $dictionary = [
                    0 => 'zero',
                    1 => 'one',
                    2 => 'two',
                    3 => 'three',
                    4 => 'four',
                    5 => 'five',
                    6 => 'six',
                    7 => 'seven',
                    8 => 'eight',
                    9 => 'nine',
                    10 => 'ten',
                    11 => 'eleven',
                    12 => 'twelve',
                    13 => 'thirteen',
                    14 => 'fourteen',
                    15 => 'fifteen',
                    16 => 'sixteen',
                    17 => 'seventeen',
                    18 => 'eighteen',
                    19 => 'nineteen',
                    20 => 'twenty',
                    30 => 'thirty',
                    40 => 'forty',
                    50 => 'fifty',
                    60 => 'sixty',
                    70 => 'seventy',
                    80 => 'eighty',
                    90 => 'ninety',
                ];

                if (!is_numeric($number)) {
                    return false;
                }

                if ($number < 0) {
                    return $negative . convertNumberToWords(abs($number));
                }

                $string = '';
                if ($number < 21) {
                    $string = $dictionary[$number];
                } elseif ($number < 100) {
                    $tens = ((int) ($number / 10)) * 10;
                    $units = $number % 10;
                    $string = $dictionary[$tens];
                    if ($units) {
                        $string .= $hyphen . $dictionary[$units];
                    }
                } elseif ($number < 1000) {
                    $hundreds = (int) ($number / 100);
                    $remainder = $number % 100;
                    $string = $dictionary[$hundreds] . ' hundred';
                    if ($remainder) {
                        $string .= $conjunction . convertNumberToWords($remainder);
                    }
                } else {
                    $baseUnits = [10000000 => 'crore', 100000 => 'lakh', 1000 => 'thousand'];
                    foreach ($baseUnits as $divisor => $label) {
                        if ($number >= $divisor) {
                            $units = (int) ($number / $divisor);
                            $remainder = $number % $divisor;
                            $string = convertNumberToWords($units) . ' ' . $label;
                            if ($remainder) {
                                $string .= $conjunction . convertNumberToWords($remainder);
                            }
                            break;
                        }
                    }
                }
                return $string;
            }
            @endphp


         @php
            $totalthing = count($orderItems);
        @endphp

        @if($totalthing > 5 && $totalthing <= 15)
            <div style="page-break-before: always;"></div>
        @endif
        @if($totalthing > 15)
            <div style="page-break-before: auto;"></div>
        @endif
        <div class="footer-section">
            <!-- Bank Details + Totals: table layout -->
            <table style="width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 12px; color: #000;">
                <tr>
                    <!-- Bank Details -->
                    <td
                        style="width:40%; border: 1px solid #ff9f43; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                        <strong style="display: block; margin-bottom: 10px; text-transform: uppercase;">Bank
                            Details:</strong>
                        <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                            <tr>
                                <td style="padding: 0 0 5px 0;">Bank Name :</td>
                                <td style="text-align: right; padding: 0 0 5px 0;">
                                    {{ $setting->bank_name ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0 0 5px 0;">Branch :</td>
                                <td style="text-align: right; padding: 0 0 5px 0;">{{ $setting->branch ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0 0 5px 0;">A/C No :</td>
                                <td style="text-align: right; padding: 0 0 5px 0;">{{ $setting->ac_no ?? 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 0 0 5px 0;">IFSC Code :</td>
                                <td style="text-align: right; padding: 0 0 5px 0;">
                                    {{ $setting->ifsc_code ?? 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </td>
                  @php
                            $subTotal = 0;
                            $totalDiscount = 0;
                            $totalGstAmount = 0;

                            foreach ($orderItems as $item) {
                                $lineTotal = $item->price * $item->quantity;
                                $subTotal += $lineTotal;
                                $totalDiscount += (float)($item->discount_amount ?? 0);
                                $totalGstAmount += (float)($item->product_gst_total ?? 0);
                            }

                            // Labour total
                            $labourTotal = 0;
                            if(!empty($labourItems)){
                                foreach ($labourItems as $labour) {
                                    $labourTotal += ($labour->price * $labour->qty);
                                }
                            }

                            $shippingCharge = (float)($sales->shipping ?? 0);

                            // Calculate after discount
                            $afterDiscount = $subTotal - $totalDiscount;

                            // Calculate GRAND TOTAL (Original Total)
                            $grandTotal = $afterDiscount + $labourTotal + $shippingCharge + $totalGstAmount;

                            // Calculate RETURN AMOUNT
                            $totalReturnAmount = 0;
                            $allItemsFullyReturned = false;

                            if (isset($returns) && $returns->isNotEmpty()) {
                                foreach ($returns as $ret) {
                                    $totalReturnAmount += (float)($ret->total_amount ?? 0);
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

                            // Calculate return amount with shipping if fully returned
                            $totalReturnWithShipping = $totalReturnAmount;
                            if ($allItemsFullyReturned && $totalReturnAmount > 0) {
                                $totalReturnWithShipping = $totalReturnAmount + $shippingCharge;
                            }

                            // Calculate PAID AMOUNT
                            $paidAmount = (float)($paidAmount ?? 0);

                            // Calculate PENDING AMOUNT = Grand Total - Return Amount - Paid Amount
                            $pendingAmount = max(0, $grandTotal - $totalReturnWithShipping - $paidAmount);

                            // Calculate EXTRA PAID (if any)
                            $extraPaid = max(0, $paidAmount - ($grandTotal - $totalReturnWithShipping));

                            // Amount in words
                            $amountInWords = ucwords(convertNumberToWords((int)$grandTotal)) . ' Rupees';

                            // Format currency function
                            function formatCurrency($amount, $setting) {
                                $num = (float)$amount;
                                $explode = explode(".", number_format($num, 2, '.', ''));
                                $whole = $explode[0];
                                $decimal = $explode[1];

                                $lastThree = substr($whole, -3);
                                $restUnits = substr($whole, 0, -3);
                                if ($restUnits != '') {
                                    $restUnits = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $restUnits);
                                    $whole = $restUnits . "," . $lastThree;
                                }
                                $formatted = $whole . "." . $decimal;
                                if ($setting->currency_position === 'right') {
                                    return $formatted . $setting->currency_symbol;
                                } else {
                                    return $setting->currency_symbol . $formatted;
                                }
                            }
                        @endphp

                    <!-- QR Code -->
                    <td
                        style="width: 20%; border: 1px solid #ff9f43; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                        <table style="width: 100%; font-size: 12px; border-collapse: collapse; color: inherit;">
                            <tr>
                                <td style="text-align: center; vertical-align: middle;">
                                    @if (isset($setting->qr_code) && file_exists(storage_path('app/public/' . $setting->qr_code)))
                                        @php
                                            $imageData = base64_encode(
                                                file_get_contents(storage_path('app/public/' . $setting->qr_code)),
                                            );
                                            $mimeType = mime_content_type(
                                                storage_path('app/public/' . $setting->qr_code),
                                            );
                                        @endphp
                                        <img src="data:{{ $mimeType }};base64,{{ $imageData }}"
                                            style="width:80px; height:80px;">
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>

                    <!-- Totals -->
                <td style="width: 40%; border: 1px solid #22b428; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="display: block; margin-bottom: 10px; text-transform: uppercase;">Totals:</strong>
                    <table style="width: 100%; font-size: 12px; border-collapse: collapse; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 5px 0;">Total Amount :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                {{ formatCurrency($subTotal, $setting) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 0 5px 0;">Discount Amount:</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                {{ formatCurrency($totalDiscount, $setting) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 0 5px 0;">After Discount Amount:</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                {{ formatCurrency($afterDiscount, $setting) }}
                            </td>
                        </tr>

                        @if($hasGst)
                        <tr>
                            <td style="padding: 0 0 5px 0; font-weight:bold;">
                                Total GST :
                            </td>
                            <td style="text-align: right; padding: 0 0 5px 0; font-weight:bold;">
                                {{ formatCurrency($totalGstAmount, $setting) }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td style="padding: 0 0 5px 0;">Shipping Charge :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                {{ formatCurrency($shippingCharge, $setting) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 0 5px 0;">Labour Charge :</td>
                            <td style="text-align: right; padding: 0 0 5px 0;">
                                {{ formatCurrency($labourTotal, $setting) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 0 5px 0; font-weight:bold;">Grand Total :</td>
                            <td style="text-align: right; padding: 0 0 5px 0; font-weight:bold;">
                                {{ formatCurrency($grandTotal, $setting) }}
                            </td>
                        </tr>

                        @if($totalReturnAmount > 0)
                        <tr>
                            <td style="padding: 0 0 5px 0; color:#ea5455; font-weight:bold;">
                                Return Amount :
                            </td>
                            <td style="text-align: right; padding: 0 0 5px 0; color:#ea5455; font-weight:bold;">
                                {{ formatCurrency($totalReturnWithShipping, $setting) }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td style="padding: 5px 0 5px 0; color:#2E7D32; font-weight:bold;">
                                Paid Amount :
                            </td>
                            <td style="text-align: right; padding: 5px 0 5px 0; color:#2E7D32; font-weight:bold;">
                                {{ formatCurrency($paidAmount, $setting) }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                Pending Amount :
                            </td>
                            <td style="text-align: right; padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                {{ formatCurrency($pendingAmount, $setting) }}
                            </td>
                        </tr>

                        @if(!empty($extraPaid) && $extraPaid > 0)
                        <tr>
                            <td style="padding: 0 0 5px 0; color:#d81414; font-weight:bold;">
                                Extra Paid :
                            </td>
                            <td style="text-align: right; padding: 0 0 5px 0; color:#d81414; font-weight:bold;">
                                {{ formatCurrency($extraPaid, $setting) }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>

                </tr>
            </table>

                <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                    <tr>
                        <td style="width: 60%; border: 2px solid #dee2e6; padding: 8px 12px; vertical-align: top; font-size: 11px;">
                            <strong>{{ $amountInWords }} Only</strong>
                        </td>
                        <td style="width: 40%; border: 2px solid #dee2e6; padding: 8px 12px; text-align: right; vertical-align: top; background-color:#ff9f43; color:#fff;">
                            <strong>Grand Total : {{ formatCurrency($grandTotal, $setting) }}</strong>
                        </td>
                    </tr>
                </table>

            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                <tr>
                    <!-- Remarks -->
                    <td style="width: 50%; border: 2px solid #dee2e6; padding: 8px 12px; vertical-align: top;">
                        <strong>Remarks :</strong><br>
                        <span>{{ 'N/A' }}</span>
                    </td>

                    <!-- Authorized Signatory -->
                    <td
                        style="width: 50%; border: 2px solid #dee2e6; padding: 8px 12px; text-align: right; vertical-align: top;">
                        <p style="margin: 0;">For, {{ $setting->name ?? ' Auto Care' }}</p>
                        <br><br><br>
                        <p style="margin: 0;">(Authorized Signatory)</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>
