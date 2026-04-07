<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Row Materials PDF</title>
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

        table,
        table td,
        table th {
            font-size: inherit;
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

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="pdf-wrapper">
        <div class="card-body">
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
                <h4 style="text-transform: uppercase;">Row Material Details</h4>
            </div>

            <table class="table-bordered">
                <thead>
                    <tr>
                        <th style="width:5%; background-color:#ff9f43; color:#fff;">Sr No</th>
                        <th style="width:15%; background-color:#ff9f43; color:#fff;">Row Material Name</th>
                        <th style="width:15%; background-color:#ff9f43; color:#fff;">SKU</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Barcode</th>
                        <th style="width:20%; background-color:#ff9f43; color:#fff;">Category</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Brand</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Quantity</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Unit</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Price</th>
                        <th style="width:10%; background-color:#ff9f43; color:#fff;">Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($row_materials as $index => $rowMaterial)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">
                                {{ !empty($rowMaterial->row_material_name) ? ucfirst(strtolower($rowMaterial->row_material_name)) : 'N/A' }}
                            </td>
                            <td class="text-center">{{ $rowMaterial->SKU ?? 'N/A' }}</td>
                            <td class="text-center">{{ $rowMaterial->barcode ?? 'N/A' }}</td>
                            <td class="text-center">
                                {{ !empty($rowMaterial->category_name) ? ucfirst(strtolower($rowMaterial->category_name)) : 'N/A' }}
                            </td>
                            <td class="text-center">
                                {{ !empty($rowMaterial->brand_name) ? ucfirst(strtolower($rowMaterial->brand_name)) : 'N/A' }}
                            </td>
                            <td class="text-center">{{ number_format((float) ($rowMaterial->quantity ?? 0), 0) }}</td>
                            <td class="text-center">
                                {{ !empty($rowMaterial->unit_name) ? ucfirst(strtolower($rowMaterial->unit_name)) : 'N/A' }}
                            </td>
                            <td class="text-center">{{ number_format((float) ($rowMaterial->price ?? 0), 2) }}</td>
                            <td class="text-center">
                                {{ !empty($rowMaterial->created_at) ? \Carbon\Carbon::parse($rowMaterial->created_at)->format('d-m-Y') : 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
