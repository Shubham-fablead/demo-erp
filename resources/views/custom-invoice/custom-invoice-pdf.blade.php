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
            /* top: 0;
            left: 0; */
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

        /* .footer-section {
            width: 95%;
            position: fixed;
            bottom: 45px;
            left: 20px;
            right: 0;
        } */
        @php
            $totalthing = count($orderItems);
            $footerBottom = ($totalthing > 5) ? 60 : 45;

            if (!function_exists('formatCurrency')) {
                function formatCurrency($amount, $setting) {
                    if ($setting->currency_position === 'right') {
                        return number_format((float)$amount, 2) . $setting->currency_symbol;
                    } else {
                        return $setting->currency_symbol . number_format((float)$amount, 2);
                    }
                }
            }
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
                        style="height: 100px; width: auto;"> {{-- adjust height as needed --}}
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
            <!-- <h3 class="mb-3" style="text-transform: uppercase;">Bill of Supply</h3> -->
            <div style="margin-bottom: 10px; width: 100%; text-align: center;">
                <div style="display: inline-block; width: 49%; text-align: left;">
                    <strong><b>INVOICE NO :</b> {{ $sales->invoice_number ?? '' }}</strong>
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
                    <strong
                        style="text-transform: uppercase; display: block; margin-bottom: 1rem;">{{ $partyDetails['role'] }}
                        :</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Name :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">
                                {{ $partyDetails['name'] ?? 'walk-in-vendor' }}
                            </td>
                        </tr>
                        @if (!empty($partyDetails['phone']))
                        <tr>
                            <td style="padding: 0 0 8px 0;">Phone :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $partyDetails['phone'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($partyDetails['email']))
                        <tr>
                            <td style="padding: 0 0 8px 0;">Email :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $partyDetails['email'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($partyDetails['address']))
                        <tr>
                            <td style="padding: 0 0 8px 0;">Address :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $partyDetails['address'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($partyDetails['address']))
                        <tr>
                            <td style="padding: 0 0 8px 0;"> GST No :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $partyDetails['gst_number'] }}</td>
                        </tr>
                        @endif
                        @if (!empty($partyDetails['address']))
                        <tr>
                            <td style="padding: 0 0 8px 0;">PAN No :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $partyDetails['pan_number'] }}</td>
                        </tr>
                        @endif
                    </table>

                    <!-- Half vertical line -->
                    <div style="position: absolute; right: 0; top: 2%; height: 15%; border-right: 1px solid #ff9f43;">
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

                    <div style="position: absolute; right: 0; top: 2%; height: 15%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>


                <!-- Invoice Details -->
                <td
                    style="width:34%; border: 0px solid #dee2e6; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Invoice
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 10px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Order Number :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ $sales->invoice_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Order Date :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">
                                {{ date('d M Y', strtotime($sales->created_at)) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Payment Status :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ ucfirst($paymentStatus) ?? 'Pending' }}
                            </td>
                        </tr>
                       {{-- <tr>
                            <td style="padding: 0 0 8px 0;">Payment Method :</td>
                            <td style="text-align: right; padding: 0 0 8px 0;">{{ ucfirst($sales->payment_method ?? '-') }}
                            </td>
                        </tr> --}}
                    </table>
                </td>
            </tr>
        </table>
@php
    $hasTax = false;

    foreach ($orderItems as $item) {
        if ($item->product_gst_total > 0) {
            $hasTax = true;
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
                    <th style="padding: 8px; text-align:left;">Product Name</th>
                    <th style="padding: 8px; text-align:right;">Price</th>
                    <th style="width:15%; padding: 8px; text-align:center;">QTY</th>
                    @if($hasTax)
                    <th style="width:23%; padding:8px; text-align:left;">Product Taxes</th>
                    <th style="width:15%; padding:8px; text-align:right;">Tax Amount</th>
                    @endif
                    <th style="width:20%; padding: 8px; text-align:right;">Total</th>
                </tr>
            </thead>
            @php
            $totalGST = 0;
            @endphp
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
                        $imagePath = public_path('admin/assets/img/product/product1.jpg');
                        }

                        if (file_exists($imagePath)) {
                        $type = pathinfo($imagePath, PATHINFO_EXTENSION);
                        $data = file_get_contents($imagePath);
                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                        }
                        @endphp

                        <table style="border-collapse: collapse;">
                            <tr>
                                <td style="padding: 0; vertical-align: middle;">
                                    <img src="{{ $base64 }}" alt="img"
                                        style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 5px;">
                                </td>
                                <td style="padding: 0 0 0 5px; vertical-align: middle; white-space: nowrap;">
                                    {{ $item->product->name ?? 'Product' }}
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td style="padding:8px; text-align:right;">
                        {{ formatCurrency($item->price, $setting) }}
                    </td>

                    {{-- <td style="padding:8px; text-align:center;">
                        {{ $item->quantity }}
                    </td>

                    <td style="padding:8px; text-align:right;">
                        @if ($setting->currency_position === 'right')
                        {{ $item->amount_total }}{{ $setting->currency_symbol }}
                        @else
                        {{ $setting->currency_symbol }}{{ $item->amount_total }}
                        @endif
                    </td> --}}
                    @php
                    $taxes = $item->product_gst_details ?? [];
                    if (is_string($taxes)) {
                        $taxes = json_decode($taxes, true) ?? [];
                    }
                    $taxAmount = $item->product_gst_total ?? 0;
                    $totalGST += $taxAmount;
                    @endphp


                    <td style="padding:8px; text-align:center;">
                        {{ $item->quantity }}
                    </td>
                    @if($hasTax)
                    {{-- Product Taxes --}}
                    <td style="padding:8px; font-size:11px;">
                        @if(is_array($taxes))
                        @forelse($taxes as $tax)
                        {{ $tax['name'] }} ({{ $tax['rate'] }}%) :
                        {{ formatCurrency($tax['amount'], $setting) }}<br>
                        @empty
                        N/A
                        @endforelse
                        @else
                        N/A
                        @endif
                    </td>

                    {{-- Tax Amount --}}
                    <td style="padding:8px; text-align:right; font-weight:bold;">
                        {{ formatCurrency($taxAmount, $setting) }}
                    </td>
                    @endif

                    {{-- Total (Excl GST) --}}
                    <td style="padding:8px; text-align:right;">
                        {{ formatCurrency($item->amount_total, $setting) }}
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center; padding:8px;">No product data available</td>
                </tr>
                @endforelse
            </tbody>
        </table>


        @php
            $grandTotalValue = str_replace(['₹', ',', $setting->currency_symbol], '', $finalTotal);
            $subtotalValue = str_replace(['₹', ',', $setting->currency_symbol], '', $subtotal);
            $discountValue = str_replace(['₹', ',', $setting->currency_symbol], '', $discountAmount);
            $shippingValue = str_replace(['₹', ',', $setting->currency_symbol], '', $shipping);
$totalGSTValue = (float) $totalGST;
$discountPercent = (float) ($sales->discount ?? 0);
  $discountBase = $subtotalValue + $totalGSTValue;
    $correctDiscountValue = ($discountBase * $discountPercent) / 100;
     $discountValue = $correctDiscountValue;

$discountAfterAmountValue = ($subtotalValue) - $discountValue;
if ($discountAfterAmountValue < 0) {
    $discountAfterAmountValue = 0;
}
$grandTotalValue = $discountAfterAmountValue + $shippingValue + $totalGSTValue;

$discountAfterAmountFormatted = formatCurrency($discountAfterAmountValue, $setting);
            $grandTotalFormatted = formatCurrency($grandTotalValue, $setting);
            $subtotalFormatted = formatCurrency($subtotalValue, $setting);
            $discountFormatted = formatCurrency($discountValue, $setting);
            $shippingFormatted = formatCurrency($shippingValue, $setting);
            $totalGSTFormatted = formatCurrency($totalGSTValue, $setting);
            /* $paidAmountFormatted = formatCurrency($paidAmount, $setting);
            $pendingAmountFormatted = formatCurrency($pendingAmount, $setting); */

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
            $totalthing = count($orderItems);
            @endphp

            @if($totalthing > 5 && $totalthing <= 15)
                <div style="page-break-before: always;">
    </div>
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

                <!-- Totals -->
                <td
                    style="width: 40%; border: 1px solid #22b428; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <table style="width: 100%; font-size: 12px; border-collapse: collapse; color: inherit;">
                        <tr>
                            <td style="padding: 4px 0;">Total Amount :</td>
                            <td style="text-align: right; padding: 4px 0;">
                                {{ $subtotalFormatted }}
                            </td>
                        </tr>

                        {{-- Show Discount only if exists --}}
                        @if (!empty($sales->discount) && (float) $sales->discount > 0)
                        <tr>
                            <td style="padding: 4px 0;">Discount :</td>
                            <td style="text-align: right; padding: 4px 0;">
                                {{ $discountFormatted }}
                            </td>
                        </tr>
                        @endif

                      <tr>
    <td style="padding: 4px 0;">After Discount Amount :</td>
    <td style="text-align: right; padding: 4px 0;">
        {{ $discountAfterAmountFormatted }}
    </td>
</tr>

                        <tr>
                            <td style="padding: 4px 0;">Shipping :</td>
                            <td style="text-align: right; padding: 4px 0;">
                                {{ $shippingFormatted }}
                            </td>
                        </tr>
                        @if($hasTax)
                        <tr>
                            <td style="padding: 4px 0; font-weight:bold;">Total GST :</td>
                            <td style="text-align: right; padding: 4px 0; font-weight:bold; color:#D84315;">
                                {{ $totalGSTFormatted }}
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td style="padding: 4px 0; font-weight: bold;">Grand Total:</td>
                            <td style="text-align: right; padding: 4px 0; font-weight: bold;">
                                {{ $grandTotalFormatted }}
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0; font-weight:bold;">Paid Amount :</td>
                            <td style="text-align: right; padding: 0 0 8px 0; color:#2E7D32; font-weight:bold;">
                                {{ $paidAmount }}
                            </td>
                        </tr>
                        @php
// Remove currency symbol & commas → numeric value
$grandTotalValue = (float) str_replace(
    ['₹', ',', $setting->currency_symbol],
    '',
    $grandTotalFormatted
);

$paidAmountValue = (float) str_replace(
    ['₹', ',', $setting->currency_symbol],
    '',
    $paidAmount
);

// ✅ Pending Amount Calculation
$pendingAmountValue = $grandTotalValue - $paidAmountValue;

if ($pendingAmountValue < 0) {
    $pendingAmountValue = 0;
}

// Format again for display
$pendingAmountFormatted = formatCurrency($pendingAmountValue, $setting);
@endphp

                        <tr>
                            <td style="padding: 0 0 8px 0; font-weight:bold;">Pending Amount :</td>
                            <td style="text-align: right; padding: 0 0 8px 0; color:#C62828; font-weight:bold;">
                                {{ $pendingAmountFormatted }}
                            </td>
                        </tr>
                    </table>
                </td>

            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; font-size: 12px; color: #000;">
            <tr>
                <td style="width: 60%; border: 2px solid #dee2e6; padding: 8px 12px; vertical-align: top;">
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
