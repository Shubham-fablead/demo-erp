<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Salary Report</title>
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

        /* Table design */
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
            background: #f9f9f9;
        }

        /* Report title */
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
            text-align: center;
            text-transform: uppercase;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .badge.paid {
            background: #eafaf1;
            color: #1e824c;
            border: 1px solid #1e824c;
        }

        .badge.pending {
            background: #fff8e5;
            color: #b26a00;
            border: 1px solid #b26a00;
        }

        .badge.rejected {
            background: #fdeaea;
            color: #c0392b;
            border: 1px solid #c0392b;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 10px;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 6px;
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

            <!-- Report Title -->
            <div class="report-title">
                Salary Report – {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            </div>

            <!-- Table -->
            <table class="table-style">
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Present</th>
                        <th>Absent</th>
                        <th>Extra</th>
                        <th>Paid Advance</th>
                        <th>Pending Advance</th>
                        <th>Salary</th>
                        <th>Extra Amt</th>
                        <th>Total Salary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($data) && count($data) > 0)
                    @foreach ($data as $row)
                    <tr>
                        <td style="text-align: left;">{{ $row['staff_name'] }}</td>
                        <td>{{ $row['present'] }}</td>
                        <td>{{ $row['absent'] }}</td>
                        <td>{{ $row['extra_present'] }}</td>
                        <td>{{ number_format($row['paid_advance'], 2) }}</td>
                        <td>{{ number_format($row['pending_advance'], 2) }}</td>
                        <td>{{ number_format($row['monthly_salary'], 2) }}</td>
                        <td>{{ number_format($row['extra_amount'], 2) }}</td>
                        <td><strong>{{ number_format($row['total_salary'], 2) }}</strong></td>
                        <td>
                            <span class="badge {{ strtolower($row['status']) }}">
                                {{ ucfirst($row['status']) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="10" style="text-align:center; font-weight:bold;">
                            No salary records found for this month.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>

            <!-- Footer -->
            <div class="footer">
                Generated on: {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}
            </div>
        </div>
    </div>
</body>

</html>
