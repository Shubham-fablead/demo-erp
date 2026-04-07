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
    {{-- @foreach ($invoices as $invoice) --}}
    <div class="pdf-wrapper">
        <div class="card-body">

            <!-- Header with Logo + Company Info (only once) -->
            <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                <tr>
                    <!-- Logo -->
                    <td style="width: 150px; vertical-align: middle;">
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

                    <!-- Company Info -->
                    <td style="vertical-align: middle; text-align: right; padding-left: 15px;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 16px; color: #000;">
                            {{ $setting->name ?? '' }}
                        </h3>
                        <small style="text-transform: uppercase; font-size: 14px;">
                            {{ $setting->address ?? '' }}<br>
                            Phone: {{ $setting->phone ?? '' }} |
                            Email: <span style="text-transform: none;">{{ $setting->email ?? '' }}</span>
                        </small>
                    </td>
                </tr>
            </table>

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

            <!-- Purchase Details (Merged) -->
            <div class="text-center">
                <h4 style="text-transform: uppercase;">Custom Invoice Details</h4>
            </div>
            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width:4%; background-color:#ff9f43; color:#fff;">Sr No</th>
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Invoice Number</th>
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Vendor Name</th>
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Customer Name</th>
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Total Amount</th>
                        <th style="width:8%; background-color:#ff9f43; color:#fff;">Total GST</th>
                        <th style="width:8%; background-color:#ff9f43; color:#fff;">Shipping</th>
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Grand Total</th>
                        <!-- <th style="width:8%; background-color:#ff9f43; color:#fff;">Paid Amount</th>
                        <th style="width:8%; background-color:#ff9f43; color:#fff;">Remaining Amount</th> -->
                        <th style="width:9%; background-color:#ff9f43; color:#fff;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $index => $invoice)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">{{ $invoice->invoice_number ?? 'N/A' }}</td>
                        <td class="text-center">{{ $invoice->vendor->name ?? '--' }}</td>
                        <td class="text-center">{{ $invoice->customer->name ?? '--' }}</td>

                        <td class="text-center">
                            {{ number_format($invoice->total_amount, 2) }}
                        </td>
                        <td class="text-center">
                            {{ number_format($invoice->total_gst ?? 0, 2) }}
                        </td>
                        <td class="text-center">
                            {{ number_format($invoice->shipping, 2) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($invoice->grand_total, 2) }}
                        </td>

                        <!-- <td class="text-center">
                            {{ number_format($invoice->paid_amount ?? 0, 2) }}
                        </td>

                        <td class="text-center">
                            {{ number_format($invoice->remaining_amount, 2) }}
                        </td> -->

                        <td class="text-center">
                            {{ $invoice->created_at->format('d-m-Y') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    {{-- @endforeach --}}

</body>

</html>