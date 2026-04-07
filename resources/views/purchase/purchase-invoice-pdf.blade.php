<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'DejaVu Sans', 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: white;
        }

        .pdf-wrapper {
            margin-top: 3mm;
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
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            overflow-wrap: break-word;
        }

        .table-bordered thead tr {
            background-color: #e9ecf0ff;
            color: #333;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .table-bordered tfoot tr {
            font-weight: bold;
            background-color: #e9ecef;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        h3,
        h4 {
            margin: 0 0 10px 0;
            color: #343a40;
        }

        .invoice-title {
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 2px solid #e5e7ebff;
            display: inline-block;
            padding-bottom: 5px;
            margin-bottom: 20px;
        }

        .logo-container {
            position: relative;
            min-height: 50px;
            margin-bottom: 10px;
        }

        .logo-container .qr-code {
            height: 60px;
            position: absolute;
        }

        .logo-container .company-logo {
            height: 50px;
            position: absolute;
            top: 0;
            left: 0;
        }

        .logo-container .company-details {
            text-align: center;
        }

        .signature-section img {
            height: 50px;
            margin-top: 5px;
        }

        .signature-section {
            margin-top: 50px;
            text-align: right;
        }

        @php
            $totalthing = count($purchaseItems);
            $footerBottom = ($totalthing > 5) ? 60 : 45;
        @endphp

        .footer-section {
            width: 95%;
            position: fixed;
            bottom: {{ $footerBottom }}px;
            left: 20px;
            right: 0;
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
                            style="height: 100px; width: auto;">
                    @endif
                </td>
                <td style="vertical-align: middle; padding-left: 15px; text-align: right;">
                    <h3 style="margin: 0; text-transform: uppercase;">{{ $setting->name ?? '' }}</h3>
                    <small style="text-transform: uppercase;">
                        {{ $setting->address ?? '' }}<br>
                        Phone: {{ $setting->phone ?? '' }} |
                        Email: <span style="text-transform: none;">{{ $setting->email ?? '' }}</span>
                    </small>
                </td>
            </tr>
        </table>

        <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

        <div class="text-center mb-1">
            <div style="margin-bottom: 10px; width: 100%; text-align: center;">
                <div style="display: inline-block; width: 49%; text-align: left;">
                    <strong>INVOICE NO : {{ $invoice->invoice_number ?? '-' }}</strong>
                </div>
                <div style="display: inline-block; width: 49%; text-align: right;">
                    <strong>GST NO : {{ $setting->gst_num ?? ' -- ' }}</strong>
                </div>
            </div>
        </div>

        <table style="width:100%; border-collapse: collapse; font-size: 10px; margin-bottom: 10px;">
            <tr>
                <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Vendor
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Name :</td>
                            <td style="text-align: right; padding:  0 0 8px 0;">
                                {{ $vendor['name'] ?? 'walk-in-vendor' }}
                            </td>
                        </tr>
                        @if (!empty($vendor['phone']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $vendor['phone'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($vendor['email']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $vendor['email'] }}</td>
                            </tr>
                        @endif
                        @if (!empty($vendor['address']))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Address :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $vendor['address'] }}</td>
                            </tr>
                        @endif
                    </table>

                    <div style="position: absolute; right: 0; top: 2%; height: 12%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>

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
                                <td style="padding: 0 0 8px 0;">Address :</td>
                                <td style="text-align: right; padding: 0 0 8px 0;">{{ $setting->address }}</td>
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

                <td
                    style="width:34%; border: 0px solid #dee2e6; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Invoice
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Invoice No :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $invoice->invoice_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Purchase Date :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">
                                {{ !empty($invoice->created_at) ? date('d M Y, h:i a', strtotime($invoice->created_at)) : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Payment Status :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $payment_status }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Payment Method :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $payment_method }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="text-center">
            <h4 style="text-transform: uppercase;">Product</h4>
        </div>

        <!-- Updated table structure to match sales invoice -->
        <table class="table-bordered"
            style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 10px 0 7px 0;">
            @php
                $hasGst = false;

                foreach ($purchaseItems as $item) {
                    if (($item->product_gst_total ?? 0) > 0) {
                        $hasGst = true;
                        break;
                    }
                }
            @endphp
            <thead>
                <tr style="background-color:#ff9f43; color:#fff;">
                    <th style="width:10%; padding: 8px; text-align:center;">Sr No</th>
                    <th style="padding: 8px;width:30%; text-align:left;">Product Name</th>
                    <th style="width:10%; padding: 8px; text-align:center;">QTY</th>
                    <th style="width:15%; padding: 8px; text-align:center;">Discount Amount</th>
                    <th style="padding: 8px; text-align:right;">Price</th>
                    @if($hasGst)
                    <th style="width:25%; padding: 8px; text-align:center;">Product Taxes</th>
                    <th style="width:12%; text-align:right;">Tax Amount</th>
                    @endif
                    <th style="width:12%; text-align:right;">Total (Excl.GST)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    // Initialize totals
                    $totalExclGstSum = 0;
                    $totalTaxSum = 0;
                    $totalSubtotal = 0;
                    $totalItemDiscount = 0;
                @endphp
                @forelse($purchaseItems as $item)
                    @php
                        // Calculate values for each item
                        $productGstTotal = $item->product_gst_total ?? 0;
                        $totalExclGst = $item->price * $item->quantity;
                        $itemTotal = $totalExclGst + $productGstTotal;
                        $totalItemDiscount += $item->discount_amount ?? 0;

                        // Accumulate totals
                        $totalExclGstSum += $totalExclGst;
                        $totalTaxSum += $productGstTotal;
                        $totalSubtotal += $itemTotal;
                    @endphp
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
                                <td style="padding: 0; width: 30%; vertical-align: middle;">
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
                                    width: 100%;
                                ">
                                    {{ ucfirst($item->product->name ?? 'Product') }}
                                </td>
                            </tr>
                        </table>
                    </td>
                        <td style="padding:8px; text-align:center;">
                            {{ $item->quantity }}
                        </td>
                        <td style="padding:8px; text-align:center;">
                            {{ formatCurrency($item->discount_amount ?? 0, $setting) }}
                            <br>
                            <small>({{ number_format($item->discount_percent ?? 0, 2) }}%)</small>
                        </td>
                        <td style="padding:8px; text-align:right;">
                            {{ formatCurrency($item->price, $setting) }}
                        </td>
                        @if($hasGst)
                        <td style="padding:8px; text-align:center; font-size:10px;">
                            @php
                                // Try different ways to get product tax details
                                $productGstDetails = [];

                                // Method 1: Check if it's stored as JSON string
                                if (isset($item->product_gst_details) && is_string($item->product_gst_details)) {
                                    $productGstDetails = json_decode($item->product_gst_details, true);
                                }
                                // Method 2: Check if it's already an array
                                elseif (isset($item->product_gst_details) && is_array($item->product_gst_details)) {
                                    $productGstDetails = $item->product_gst_details;
                                }
                                // Method 3: Check if taxes are stored in a different field
                                elseif (isset($item->taxes) && !empty($item->taxes)) {
                                    $productGstDetails = $item->taxes;
                                }
                                // Method 4: Check for gst_details
                                elseif (isset($item->gst_details) && !empty($item->gst_details)) {
                                    $productGstDetails = $item->gst_details;
                                }
                                // Method 5: If product has tax information
                                elseif (isset($item->product) && isset($item->product->taxes)) {
                                    $productGstDetails = $item->product->taxes;
                                }

                                // If still empty and we have tax amount, create a default display
                                if (empty($productGstDetails) && $productGstTotal > 0) {
                                    // Based on your screenshot, you might have CGST and SGST
                                    // You should adjust this based on your actual tax structure
                                    $productGstDetails = [
                                        ['tax_name' => 'CGST', 'tax_rate' => 18.00, 'tax_amount' => $productGstTotal/2],
                                        ['tax_name' => 'SGST', 'tax_rate' => 18.00, 'tax_amount' => $productGstTotal/2]
                                    ];
                                }
                            @endphp

                            @if(!empty($productGstDetails) && is_array($productGstDetails))
                                @foreach($productGstDetails as $tax)
                                    @php
                                        // Ensure we have the correct keys
                                        $taxName = $tax['tax_name'] ?? $tax['name'] ?? 'Tax';
                                        $taxRate = $tax['tax_rate'] ?? $tax['rate'] ?? 0;
                                        $taxAmount = $tax['tax_amount'] ?? $tax['amount'] ?? 0;
                                    @endphp
                                    <div>
                                        {{ $taxName }} ({{ number_format($taxRate, 2) }}%)
                                        : {{ formatCurrency($taxAmount, $setting) }}
                                    </div>
                                @endforeach
                            @else
                                <!-- Show N/A only if there's truly no tax -->
                                @if($productGstTotal == 0)
                                    N/A
                                @else
                                    <!-- If there's tax amount but no details, show generic -->
                                    <div>Tax Included: {{ $setting->currency_symbol }}{{ number_format($productGstTotal, 2) }}</div>
                                @endif
                            @endif
                        </td>
                        <td style="padding:8px; text-align:right; font-weight:bold;">
                            {{ formatCurrency($productGstTotal, $setting) }}
                        </td>
                        @endif
                        <td style="padding:8px; text-align:right;">
                            {{ formatCurrency($totalExclGst, $setting) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align:center; padding:8px;">No product data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @php
            // Calculate final totals from the items
            $subtotalExclGst = $totalExclGstSum; // Sum of all product subtotals (price * quantity)
            $totalGstAmount = $totalTaxSum; // Sum of all product GST
            $totalDiscount = $totalItemDiscount; // Sum of all item discounts

            // Calculate after discount amount
            $afterDiscount = $subtotalExclGst - $totalDiscount;

            // Calculate grand total (Subtotal + GST - Discount)
            $grandTotal = $subtotalExclGst + $totalGstAmount - $totalDiscount;

            // Add shipping if any
            $shippingAmount = $invoice->shipping ?? 0;
            $grandTotal += $shippingAmount;

            // Get return amount from controller data
            $returnAmount = $totalReturnAmount ?? 0;

            // Calculate pending amount properly
            // Pending = (Subtotal + GST - Discount + Shipping) - (Paid Amount + Return Amount)
            $pendingAmount = $grandTotal - ($paidAmount + $returnAmount);

            // Extra paid when payment exceeds grand total after returns
            $extraPaid = ($paidAmount + $returnAmount > $grandTotal) ? ($paidAmount + $returnAmount - $grandTotal) : 0;

            // If pending amount is negative, it means extra paid
            if ($pendingAmount < 0) {
                $extraPaid = abs($pendingAmount);
                $pendingAmount = 0;
            }

            // Format currency based on settings
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

            $subtotalFormatted = formatCurrency($subtotalExclGst, $setting);
            $totalGstFormatted = formatCurrency($totalGstAmount, $setting);
            $discountFormatted = formatCurrency($totalDiscount, $setting);
            $afterDiscountFormatted = formatCurrency($afterDiscount, $setting);
            $shippingFormatted = formatCurrency($shippingAmount, $setting);
            $grandTotalFormatted = formatCurrency($grandTotal, $setting);
            $paidAmountFormatted = formatCurrency($paidAmount ?? 0, $setting);
            $returnAmountFormatted = formatCurrency($returnAmount, $setting);
            $pendingAmountFormatted = formatCurrency($pendingAmount, $setting);
            $extraPaidAmountFormatted = formatCurrency($extraPaid, $setting);

            // Convert number to words
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

            $numericValue = str_replace(['₹', ',', $setting->currency_symbol], '', $grandTotalFormatted);
            $amountInWords = ucwords(convertNumberToWords((int)$numericValue)) . ' Rupees';
        @endphp

        @php
            $totalthing = count($purchaseItems);
        @endphp

        @if($totalthing > 5 && $totalthing <= 15)
            <div style="page-break-before: always;"></div>
        @endif
        @if($totalthing > 15)
            <div style="page-break-before: auto;"></div>
        @endif

        <div class="footer-section">
            <!-- Updated footer section to match sales invoice -->
            <table style="width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 12px; color: #000;">
                <tr>
                    <!-- Bank Details -->
                    <td
                        style="width:40%; border: 1px solid #ff9f43; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                        <strong style="display: block; margin-bottom: 1rem; text-transform: uppercase;">Bank
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

                    <!-- Totals - Updated with proper calculations including returns -->
                    <td
                        style="width: 40%; border: 1px solid #22b428; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                        <table style="width: 100%; font-size: 12px; border-collapse: collapse; color: inherit;">
                            <tr>
                                <td style="padding:  0 0 5px 0;">Total Amount :</td>
                                <td style="text-align: right; padding: 0 0 5px 0;">
                                    {{ $subtotalFormatted }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:  0 0 5px 0;">Discount Amount:</td>
                                <td style="text-align: right; padding:  0 0 5px 0;">
                                    {{ $discountFormatted }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:  0 0 5px 0;">Price After Discount :</td>
                                <td style="text-align: right; padding:  0 0 5px 0;">
                                    {{ $afterDiscountFormatted }}
                                </td>
                            </tr>

                            @if($hasGst)
                            <tr>
                                <td style="padding: 0 0 5px 0; font-weight:bold;">
                                    Total GST :
                                </td>
                                <td style="text-align: right; padding: 0 0 5px 0; font-weight:bold;">
                                    {{ $totalGstFormatted }}
                                </td>
                            </tr>
                            @endif
                            @if($shippingAmount > 0)
                            <tr>
                                <td style="padding:  0 0 5px 0;">Shipping :</td>
                                <td style="text-align: right; padding:  0 0 5px 0;">
                                    {{ $shippingFormatted }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td style="padding: 0 0 5px 0; font-weight:bold;">Grand Total :</td>
                                <td style="text-align: right; padding: 0 0 5px 0; font-weight:bold;">
                                    {{ $grandTotalFormatted }}
                                </td>
                            </tr>
                            @if($returnAmount > 0)
                            <tr>
                                <td style="padding: 0 0 5px 0; color:#FF6B6B; font-weight:bold;">
                                    Return Amount :
                                </td>
                                <td style="text-align: right; padding: 0 0 5px 0; color:#FF6B6B; font-weight:bold;">
                                    {{ $returnAmountFormatted }}
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td style="padding: 0 0 5px 0; color:#2E7D32; font-weight:bold;">
                                    Paid Amount :
                                </td>
                                <td style="text-align: right; padding: 0 0 5px 0; color:#2E7D32; font-weight:bold;">
                                    {{ $paidAmountFormatted }}
                                </td>
                            </tr>
                            @if($extraPaid > 0)
                            <tr>
                                <td style="padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                    Extra Paid Amount :
                                </td>
                                <td style="text-align: right; padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                    {{ $extraPaidAmountFormatted }}
                                </td>
                            </tr>
                            @endif
                                <tr>
                                    <td style="padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                        Pending Amount :
                                    </td>
                                    <td style="text-align: right; padding: 0 0 5px 0; color:#C62828; font-weight:bold;">
                                        {{ $pendingAmountFormatted }}
                                    </td>
                                </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                <tr>
                    <td style="width: 60%; border: 2px solid #dee2e6; padding: 8px 12px; vertical-align: top; font-size: 11px;">
                        <strong>{{ $amountInWords }} Only</strong>
                    </td>
                    <td
                        style="width: 40%; border: 2px solid #dee2e6; padding: 8px 12px; text-align: right; vertical-align: top; background-color:#ff9f43; color:#fff;">
                        <strong>Grand Total : {{ $grandTotalFormatted }}</strong>
                    </td>
                </tr>
            </table>

            <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
                <tr>
                    <!-- Remarks -->
                    <td style="width: 50%; border: 2px solid #dee2e6; padding: 8px 12px; vertical-align: top;">
                        <strong>Remarks :</strong><br>
                        <span>{{ $invoice->remarks ?? 'N/A' }}</span>
                    </td>

                    <!-- Authorized Signatory -->
                    <td
                        style="width: 50%; border: 2px solid #dee2e6; padding: 8px 12px; text-align: right; vertical-align: top;">
                        <p style="margin: 0;">For, {{ $setting->name ?? 'Auto Care' }}</p>
                        <br><br><br>
                        <p style="margin: 0;">(Authorized Signatory)</p>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>

</html>
