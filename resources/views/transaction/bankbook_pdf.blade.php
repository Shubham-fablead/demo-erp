<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bank Book PDF</title>
    <style>
        @page {
            size: A4;
            margin: 1mm 1mm;
        }

        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
            padding: 4px 8px;
        }

        .table-bordered thead tr {
            background-color: #ff9f43;
            color: #fff;
        }

        .table-bordered tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        h3,
        h4 {
            margin: 0 0 10px 0;
            color: #343a40;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="card-body">

            <!-- Header -->
            <table style="width:100%; margin-bottom: 10px; border-collapse: collapse;">
                <tr>
                    <td style="width: 150px; vertical-align: middle;">
                        @if (isset($settings->logo) && file_exists(storage_path('app/public/' . $settings->logo)))
                            @php
                                $logoPath = storage_path('app/public/' . $settings->logo);
                                $logoData = base64_encode(file_get_contents($logoPath));
                                $logoMime = mime_content_type($logoPath);
                            @endphp
                            <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Company Logo"
                                style="height: 100px; width: auto;">
                        @endif
                    </td>

                    <td style="vertical-align: middle; text-align: right; padding-left: 15px;">
                        <h3 style="margin: 0; text-transform: uppercase; font-size: 16px; color: #000;">
                            {{ $settings->name ?? '' }}
                        </h3>
                        <small style="text-transform: uppercase; font-size: 14px;">
                            {{ $settings->address ?? '' }}<br>
                            Phone: {{ $settings->phone ?? '' }} |
                            Email: <span style="text-transform: none;">{{ $settings->email ?? '' }}</span>
                        </small>
                    </td>
                </tr>
            </table>

            <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

            <div class="text-center">
                <h4 style="text-transform: uppercase;">Bank Book - {{ ucfirst($status) }}s</h4>
            </div>

            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width:5%;">Sr No</th>
                        <th style="width:15%;">{{ $status === 'credit' ? 'Order No' : 'Invoice No' }}</th>
                        <th style="width:15%;">Date</th>
                        <th style="width:30%;">Particulars</th>
                        <th style="width:20%;">Bank Name</th>
                        <th style="width:15%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ $status === 'credit' ? ($item['invoice_no'] ?: '-') : ($item['order_no'] ?: '-') }}</td>
                            <td class="text-center">{{ $item['payment_date'] }}</td>
                            <td class="text-center">{{ $item['particulars'] }}</td>
                            <td class="text-center">{{ $item['bank_name'] }}</td>
                            <td class="text-center">{{ number_format($item['payment_amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totals Section -->
            <div style="margin-top: 20px; text-align: right;">
                <table style="width: 250px; margin-left: auto; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; background-color: #f8f9fa; border: 1px solid #dee2e6; font-weight: bold; text-align: left;">
                            Total Amount
                        </td>
                        <td style="padding: 8px; background-color: #e9ecef; border: 1px solid #dee2e6; font-weight: bold; text-align: right;">
                            {{ number_format($grandTotal, 2) }}
                        </td>
                    </tr>
                </table>
            </div>

        </div>
    </div>
</body>

</html>
