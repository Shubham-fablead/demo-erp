<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>GSTR-2 Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: right; }
        th { background: #f0f0f0; }
        td.label { text-align: left; }
    </style>
</head>
<body>
    <h3 style="text-align:center">GSTR-2 (Purchase) Report</h3>
    <p style="text-align:center">
        Period: {{ $data['from_date'] ?? 'All' }} to {{ $data['to_date'] ?? 'All' }}
    </p>

    <h4>Registered Vendors</h4>
    <table>
        <tr><th class="label">Invoices</th><td>{{ $data['registered']['invoice_count'] ?? 0 }}</td></tr>
        <tr><th class="label">Taxable Value</th><td>{{ $data['registered']['taxable_value'] ?? 0 }}</td></tr>
        <tr><th class="label">CGST</th><td>{{ $data['registered']['cgst'] ?? 0 }}</td></tr>
        <tr><th class="label">SGST</th><td>{{ $data['registered']['sgst'] ?? 0 }}</td></tr>
        <tr><th class="label">IGST</th><td>{{ $data['registered']['igst'] ?? 0 }}</td></tr>
    </table>

    <h4>Unregistered Vendors</h4>
    <table>
        <tr><th class="label">Invoices</th><td>{{ $data['unregistered']['invoice_count'] ?? 0 }}</td></tr>
        <tr><th class="label">Taxable Value</th><td>{{ $data['unregistered']['taxable_value'] ?? 0 }}</td></tr>
        <tr><th class="label">CGST</th><td>{{ $data['unregistered']['cgst'] ?? 0 }}</td></tr>
        <tr><th class="label">SGST</th><td>{{ $data['unregistered']['sgst'] ?? 0 }}</td></tr>
        <tr><th class="label">IGST</th><td>{{ $data['unregistered']['igst'] ?? 0 }}</td></tr>
    </table>

    <h4>Total Summary</h4>
    <table>
        <tr><th class="label">Total Invoices</th><td>{{ $data['summary']['total_invoices'] ?? 0 }}</td></tr>
        <tr><th class="label">Taxable Value</th><td>{{ $data['summary']['taxable_value'] ?? 0 }}</td></tr>
        <tr><th class="label">CGST</th><td>{{ $data['summary']['cgst'] ?? 0 }}</td></tr>
        <tr><th class="label">SGST</th><td>{{ $data['summary']['sgst'] ?? 0 }}</td></tr>
        <tr><th class="label">IGST</th><td>{{ $data['summary']['igst'] ?? 0 }}</td></tr>
    </table>
</body>
</html>
