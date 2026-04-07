<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Purchase Report PDF</title>
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

        .footer-section {
            width: 95%;
            position: fixed;
            bottom: 45px;
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
                    <strong>INVOICE NO : {{ $sales->order_number ?? '-' }}</strong>
                </div>
                <div style="display: inline-block; width: 49%; text-align: right;">
                    <strong>GST NO : {{ $setting->gst_num ?? ' -- ' }}</strong>
                </div>
            </div>
        </div>

        <table style="width:100%; border-collapse: collapse; font-size: 12px; margin-bottom: 10px;">
            <tr>
                <!-- Vehicle Details -->
                <td
                    style="width:33%; position: relative; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Company
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                        @if (!empty($setting->name))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Name :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->name }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->email))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Email :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->email }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->phone))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Phone :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->phone }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->address))
                            <tr>
                                <td style="padding: 0 0 8px 0;">Address :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->address }}</td>
                            </tr>
                        @endif

                        @if (!empty($setting->gst_num))
                            <tr>
                                <td style="padding: 0 0 8px 0;">GST :</td>
                                <td style="text-align: right; padding: 0;">{{ $setting->gst_num }}</td>
                            </tr>
                        @endif
                    </table>

                    <div style="position: absolute; right: 0; top: 2%; height: 11%; border-right: 1px solid #ff9f43;">
                    </div>
                </td>

                <!-- Invoice Details -->
                <td
                    style="width:34%; border: 0px solid #dee2e6; padding: 8px 12px; vertical-align: top; background-color: #eaedf0;">
                    <strong style="text-transform: uppercase; display: block; margin-bottom: 1rem;">Report
                        Details:</strong>
                    <table style="width:100%; border-collapse: collapse; font-size: 12px; color: inherit;">
                        <tr>
                            <td style="padding: 0 0 8px 0;">Total Purchases :</td>
                            <td style="text-align: right; padding: 0;">{{ isset($purchases) ? $purchases->count() : '-' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 0 0 8px 0;">Report Date :</td>
                            <td style="text-align: right; padding: 0;">
                                {{ \Carbon\Carbon::now()->format('d M Y') }}
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>


        <div class="text-center">
            <h4 style="text-transform: uppercase;">Products</h4>
        </div>

        <table class="table-bordered"
            style="width: 100%; border-collapse: collapse; font-family: DejaVu Sans, sans-serif; font-size: 12px; margin: 10px 0;">
            <thead>
                <tr style="background-color:#ff9f43; color:#fff;">
                    <td style="padding: 10px;"><strong>Product</strong></td>
                    <td style="padding: 10px;"><strong>Category</strong></td>
                    <td style="padding: 10px;"><strong>Unit Price</strong></td>
                    <td style="padding: 10px;"><strong>Qty</strong></td>
                    <td style="padding: 10px;"><strong>Shipping</strong></td>
                    <td style="padding: 10px;"><strong>Taxes</strong></td>
                    <td style="padding: 10px;"><strong>Total</strong></td>
                </tr>
            </thead>
            <tbody>
                @php
                    if (!function_exists('formatCurrency')) {
                        function formatCurrency($amount, $symbol = '₹', $position = 'left')
                        {
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
                        $imagePath = isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                                ? asset(env('ImagePath').'storage/' . $images[0])
                                : asset(env('ImagePath').'admin/assets/img/product/noimage.png');

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

                            $displayShipping = formatCurrency($invoiceShipping, $currencySymbol, $currencyPosition);
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
                                <img src="{{ $imagePath }}" alt="Product Image" style="width: 50px; height: 50px;">
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

            </tbody>
        </table>

        <br>

        <table style="width: 300px; margin-left: auto; border-collapse: collapse; font-size: 12px; color: #333;">
            <tr>
                <td
                    style="padding: 6px 12px; text-align: left; background-color: #ff9f43; color:#fff; font-weight:bold; border: 1px solid #e0e0e0;">
                    Total Amount
                </td>
                <td style="padding: 6px 12px; text-align: right; border: 1px solid #e0e0e0;">
                    @if ($currencyPosition == 'left')
                                                    {{ formatCurrency($totalAmountSum, $currencySymbol, 'left') }}
                                                @else
                                                    {{ formatCurrency($totalAmountSum, $currencySymbol, 'right') }}
                                                @endif
                </td>
            </tr>


        </table>
    </div>

</body>

</html>
