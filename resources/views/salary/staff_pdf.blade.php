<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Salary Slip</title>
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
            background: #bc1e2e;
            color: #fff;
            font-weight: 600;
        }

        .table-style tr:nth-child(even) {
            background: #fafafa;
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

        /* SECTION TITLE */
        h3.section-title {
            margin-top: 12px;
            font-size: 13px;
            font-weight: bold;
            border-left: 3px solid #b3b6b9;
            padding-left: 6px;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="card-body">
            <!-- HEADER WITH LOGO AND TITLE -->
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

            <div class="report-info d-flex justify-content-between align-items-center">
                <div>
                    Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                </div>
                <div>
                    @foreach ($data as $item)
                        Before Advance Pay: {{ $item['old_advance_pay'] }}
                    @endforeach
                </div>

            </div>



            <!-- SALARY TABLE -->
            <table class="table-style">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Month</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Extra</th>
                        <th>Monthly Salary</th>
                        <th>Extra Amt</th>
                        <th>Paid Advance</th>
                        <th>Pending Advance</th>
                        <th>Total Salary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr>
                            <td>{{ ucwords($item['staff_name']) }}</td>
                            <td>{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</td>
                            <td>{{ $item['present'] }}</td>
                            <td>{{ $item['absent'] }}</td>
                            <td>{{ $item['extra_present'] }}</td>
                            <td>{{ number_format($item['monthly_salary'], 2) }}</td>
                            <td>{{ number_format($item['extra_amount'], 2) }}</td>
                            <td>{{ number_format($item['paid_advance'], 2) }}</td>
                            <td>{{ number_format($item['pending_advance'], 2) }}</td>
                            <td>{{ number_format($item['total_salary'], 2) }}</td>
                            <td>{{ $item['status'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- TOTAL AMOUNT BOX -->
            <div class="total-box" style="text-align: right;">
                <table>
                    <tr>
                        <td class="total-label">Total Amount</td>
                        <td class="total-amount">
                            {{ number_format(collect($data)->sum('total_salary'), 2) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>