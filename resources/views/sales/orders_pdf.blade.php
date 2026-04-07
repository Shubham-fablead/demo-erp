<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Sales Orders Report</title>
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

        /* Clean layout - no outer border/shadow */
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

        .logo img {
            height: 50px;
            max-width: 100px;
            object-fit: contain;
        }

        .company-info-header {
            text-align: center;
            line-height: 1.4;
            font-size: 10px !important;
        }

        /* INFO SECTION */
        .info-section {
            display: flex;
            justify-content: space-between;
            line-height: 1.4;
            font-size: 11px;
            margin-bottom: 10px;
        }

        .report-info {
            text-align: right;
            font-weight: bold;
        }

        /* TABLE STYLE */
        .table-style {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12px;
        }

        .table-style th,
        .table-style td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            text-align: center;
        }

        .table-style th {
            background: #ff9f43;
            color: #fff;
            font-weight: 600;
        }

        .table-style tr:nth-child(even) {
            background: #fafafa;
        }

        /* SECTION TITLE */
        h3.section-title {
            margin-top: 12px;
            font-size: 13px;
            font-weight: bold;
            border-left: 3px solid #b3b6b9;
            padding-left: 6px;
            color: #333;
        }

        /* TOTAL BOX */
        .total-box {
            display: flex;
            justify-content: flex-end;
            margin-top: 6px;
        }

        .total-box table {
            width: auto;
            border: none;
            font-size: 11px;
        }

        .total-box td {
            border: none;
            padding: 4px 8px;
        }

        .total-label {
            background: #f7f7f7;
            font-weight: bold;
            border-radius: 4px 0 0 4px;
        }

        .total-amount {
            background: #f0f4ff;
            font-weight: bold;
            color: #333;
            border-radius: 0 4px 4px 0;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="card-body">
            <!-- HEADER -->
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


            <!-- INFO SECTION -->
            <div class="info-section">
                <div>
                    GST: {{ $setting->gst_num ?? 'N/A' }}
                </div>
                <div class="report-info">
                    Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}<br>
                    {{-- Generated By: {{ auth()->user()->name ?? 'System' }} --}}
                </div>
            </div>

            <!-- SALES ORDERS TABLE -->
            <h3 class="section-title">Sales Orders</h3>
            <table class="table-style">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Date</th>
                        <th>Customer Name</th>
                        <th>Total Amount</th>
                        <th>Remaining Amount</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse ($orders as $order)
                        @php $grandTotal += $order->total_amount; @endphp
                        <tr>
                            <td>{{ $order->order_number ?? 'N/A' }}</td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                            <td>{{ $order->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ number_format($order->remaining_amount, 2) }}</td>
                            <td>{{ ucfirst($order->payment_status) ?? 'N/A' }}</td>
                            <td>{{ $order->payment_method ?? 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;">No sales orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- TOTAL BOX -->
            <div class="total-box">
                <table>
                    <tr>
                        <td class="total-label">Grand Total</td>
                        <td class="total-amount">{{ number_format($grandTotal, 2) }}</td> {{-- ✅ No currency --}}
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
