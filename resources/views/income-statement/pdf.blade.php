<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income Statement</title>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: white;
            color: #333;
        }

        .card-body {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 8px 10px;
        }

        th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .section-header {
            background-color: #ff9f43;
            color: white;
            font-weight: bold;
        }

        .gross-profit {
            background-color: #d4edda;
            font-weight: bold;
        }

        .operating-income {
            background-color: #cce5ff;
            font-weight: bold;
        }

        .logo {
            height: 80px;
            width: auto;
        }

        .company-header {
            margin-bottom: 15px;
        }

        .company-details {
            font-size: 11px;
        }

        hr {
            border: 1px solid #ddd;
        }

        .period {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="card-body">
        <!-- Company Logo & Info -->
        <table style="border: none; margin-bottom: 10px;">
            <tr style="border: none;">
                <td style="border: none; width: 150px;">
                    @if (isset($data['settings']->logo) && file_exists(storage_path('app/public/' . $data['settings']->logo)))
                        @php
                            $logoPath = storage_path('app/public/' . $data['settings']->logo);
                            $logoData = base64_encode(file_get_contents($logoPath));
                            $logoMime = mime_content_type($logoPath);
                        @endphp
                        <img src="data:{{ $logoMime }};base64,{{ $logoData }}" class="logo">
                    @endif
                </td>
                <td style="border: none; text-align: right; vertical-align: top;">
                    <h3 style="margin: 0;">{{ $data['settings']->name ?? '' }}</h3>
                    <div class="company-details">
                        {{ $data['settings']->address ?? '' }}<br>
                        Phone: {{ $data['settings']->phone ?? '' }} | Email: {{ $data['settings']->email ?? '' }}
                    </div>
                </td>
            </tr>
        </table>

        <hr style="height: 2px; background-color: #d7cdcd; border: none; margin-top: 0; margin-bottom: 20px;">

        <!-- Title & Period -->
        <div class="text-center">
            <h3 style="margin-bottom: 5px;">Income Statement</h3>
            <div class="period">
                Period: {{ \Carbon\Carbon::parse($data['period']['start'])->format('M d, Y') ?? '' }}
                to {{ \Carbon\Carbon::parse($data['period']['end'])->format('M d, Y') ?? '' }}
            </div>
        </div>

        <!-- Income Statement Table -->
        @php
            $currency = $data['currency'] ?? ['symbol' => '₹', 'position' => 'left'];
        @endphp

        <table>
            <!-- Revenue -->
            <tr class="section-header">
                <td colspan="2">Revenue</td>
            </tr>
            <tr>
                <td>Sales in Cash</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['revenue']['sales_cash'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td>Sales in Online</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['revenue']['sales_online'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td>Sales Revenue</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['revenue']['sales'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td><strong>Total Revenue</strong></td>
                <td class="text-right">
                    <strong>{{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['revenue']['total_revenue'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}</strong>
                </td>
            </tr>

            <!-- COGS -->
            <tr class="section-header">
                <td colspan="2">Cost of Goods Sold</td>
            </tr>
            <tr>
                <td>Purchases in Cash</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['cost_of_goods_sold']['purchase_cash'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td>Purchases in Online</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['cost_of_goods_sold']['purchase_online'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td>Purchases</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td><strong>Total COGS</strong></td>
                <td class="text-right">
                    <strong>{{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['cost_of_goods_sold']['total_cogs'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}</strong>
                </td>
            </tr>

            <!-- Gross Profit -->
            <tr class="gross-profit">
                <td>Gross Profit</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['gross_profit'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>

            <!-- Operating Expenses -->
            <tr class="section-header">
                <td colspan="2">Operating Expenses</td>
            </tr>
            <tr>
                <td>General Expenses</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['operating_expenses']['general_expenses'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
            <tr>
                <td><strong>Total Operating Expenses</strong></td>
                <td class="text-right">
                    <strong>{{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['operating_expenses']['total_operating_expenses'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}</strong>
                </td>
            </tr>

            <!-- Operating Income -->
            <tr class="operating-income">
                <td>Operating Income (EBIT)</td>
                <td class="text-right">
                    {{ $currency['position'] == 'left' ? $currency['symbol'] : '' }}{{ number_format($data['operating_income'], 2) }}{{ $currency['position'] == 'right' ? $currency['symbol'] : '' }}
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
