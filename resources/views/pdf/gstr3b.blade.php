<!DOCTYPE html>
<html>
<head>
    <style>
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 10px; 
            margin: 20px;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 15px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 15px; 
        }
        th, td { 
            border: 1px solid #000; 
            padding: 4px; 
            text-align: right; 
            font-size: 9px;
        }
        th { 
            background: #f2f2f2; 
            text-align: center;
            font-weight: bold;
        }
        .left { text-align: left; }
        .center { text-align: center; }
        .section-title {
            font-weight: bold;
            margin: 10px 0 5px 0;
            border-bottom: 1px solid #000;
        }
        .total-row {
            font-weight: bold;
            background: #e6e6e6;
        }
        .dashed-line {
            border-top: 1px dashed #000;
            height: 1px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>GSTR-3B MONTHLY RETURN</h2>
    </div>
    
    <div class="company-info">
        <strong>{{ $data['company_name'] ?? 'SNEHTRADING' }}</strong><br>
        <strong>FROM {{ $data['from_date'] ?? '01/04/2025' }} TO {{ $data['to_date'] ?? '20/08/2025' }}</strong><br>
        <strong>GSTIN: {{ $data['gstin'] ?? '' }}</strong><br>
        <strong>PAGE NO. 1</strong>
    </div>

    <!-- Sales Section -->
    <div class="section-title">SALES</div>
    
    <!-- BOOK -->
    <table>
        <thead>
            <tr>
                <th class="left">BOOK</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($data['sales']['book']))
                @foreach($data['sales']['book'] as $sale)
                <tr>
                    <td class="left">{{ $sale['invoice_no'] }} - {{ $sale['date'] }}</td>
                    <td>{{ number_format($sale['taxable_value'], 2) }}</td>
                    <td>{{ number_format($sale['igst'], 2) }}</td>
                    <td>{{ number_format($sale['cgst'], 2) }}</td>
                    <td>{{ number_format($sale['sgst'], 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="left" colspan="5">
                        <div class="dashed-line"></div>
                        <div class="dashed-line"></div>
                        <div class="dashed-line"></div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- STATEWISE - UNREGISTERED AND COMPOSITION -->
    <table>
        <thead>
            <tr>
                <th class="left">STATEWISE - UNREGISTERED AND COMPOSITION</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TOTAL SALES -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>TOTAL SALES</strong></td>
            <td><strong>{{ number_format($data['total_sales']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_sales']['igst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_sales']['cgst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_sales']['sgst_payable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- SALES RETURN -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>SALES RETURN</strong></td>
            <td><strong>{{ number_format($data['sales_return']['total']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['sales_return']['total']['igst'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['sales_return']['total']['cgst'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['sales_return']['total']['sgst'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- SALES RETURN UN-REGISTERED -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>SALES RETURN UN-REGISTERED ( - )</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
        </tr>
    </table>

    <!-- SALES RETURN UN -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>SALES RETURN UN</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
            <td><strong>0.00</strong></td>
        </tr>
    </table>

    <!-- CREDIT NOTE -->
    <table>
        <thead>
            <tr>
                <th class="left">CREDIT NOTE ( - )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- CREDIT NOTE UN-REGISTERED -->
    <table>
        <thead>
            <tr>
                <th class="left">CREDIT NOTE UN-REGISTERED ( )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DEBIT NOTE -->
    <table>
        <thead>
            <tr>
                <th class="left">DEBIT NOTE ( + )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- DEBIT NOTE UN-REGISTERED -->
    <table>
        <thead>
            <tr>
                <th class="left">DEBIT NOTE UN-REGISTERED ( + )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- OTHER INCOME -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>OTHER INCOME</strong></td>
            <td><strong>{{ number_format($data['other_income']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['other_income']['igst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['other_income']['cgst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['other_income']['sgst_payable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- TOTAL PAYABLE -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>TOTAL PAYABLE</strong></td>
            <td><strong>{{ number_format($data['total_payable']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_payable']['igst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_payable']['cgst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_payable']['sgst_payable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- RCM PURCHASE BOOK -->
    <div class="section-title">RCM PURCHASE BOOK</div>
    <table>
        <thead>
            <tr>
                <th class="left">RCM PURCHASE BOOK</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST</th>
                <th>CGST</th>
                <th>SGST</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- RCM PURCHASE BOOK TOTAL -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>RCM PURCHASE BOOK ( TOTAL )</strong></td>
            <td><strong>{{ number_format($data['rcm_purchase']['total']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['rcm_purchase']['total']['igst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['rcm_purchase']['total']['cgst_payable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['rcm_purchase']['total']['sgst_payable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- PURCHASE (ALL) -->
    <div class="section-title">PURCHASE (ALL)</div>
    <table>
        <thead>
            <tr>
                <th class="left">BOOK</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST RECEIVABLE</th>
                <th>CGST RECEIVABLE</th>
                <th>SGST RECEIVABLE</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($data['purchase']['book']))
                @foreach($data['purchase']['book'] as $purchase)
                <tr>
                    <td class="left">{{ $purchase['invoice_no'] }} - {{ $purchase['date'] }}</td>
                    <td>{{ number_format($purchase['taxable_value'], 2) }}</td>
                    <td>{{ number_format($purchase['igst_receivable'], 2) }}</td>
                    <td>{{ number_format($purchase['cgst_receivable'], 2) }}</td>
                    <td>{{ number_format($purchase['sgst_receivable'], 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td class="left" colspan="5">
                        <div class="dashed-line"></div>
                        <div class="dashed-line"></div>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- TOTAL PURCHASE -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>TOTAL PURCHASE</strong></td>
            <td><strong>{{ number_format($data['total_purchase']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_purchase']['igst_receivable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_purchase']['cgst_receivable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_purchase']['sgst_receivable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- DEBIT NOTE ( - ) -->
    <table>
        <thead>
            <tr>
                <th class="left">DEBIT NOTE ( - )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST RECEIVABLE</th>
                <th>CGST RECEIVABLE</th>
                <th>SGST RECEIVABLE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- CREDIT NOTE ( + ) -->
    <table>
        <thead>
            <tr>
                <th class="left">CREDIT NOTE ( + )</th>
                <th>TAXABLE.VALUE</th>
                <th>IGST RECEIVABLE</th>
                <th>CGST RECEIVABLE</th>
                <th>SGST RECEIVABLE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="left" colspan="5">
                    <div class="dashed-line"></div>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- TOTAL RECEIVABLE -->
    <table>
        <tr class="total-row">
            <td class="left"><strong>TOTAL RECEIVABLE</strong></td>
            <td><strong>{{ number_format($data['total_receivable']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_receivable']['igst_receivable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_receivable']['cgst_receivable'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['total_receivable']['sgst_receivable'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <!-- SUMMARY -->
    <div class="section-title">SUMMARY</div>
    <table>
        <tr class="total-row">
            <td class="left"><strong>NET PAYBLE/RECEIVABLE :-</strong></td>
            <td><strong>{{ number_format($data['net_balance']['taxable_value'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['net_balance']['igst'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['net_balance']['cgst'] ?? 0, 2) }}</strong></td>
            <td><strong>{{ number_format($data['net_balance']['sgst'] ?? 0, 2) }}</strong></td>
        </tr>
    </table>

    <table>
        <tr class="total-row">
            <td class="left"><strong>BALANCE :- 0</strong></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</body>
</html>
