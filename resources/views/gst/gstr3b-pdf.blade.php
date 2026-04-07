<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>GSTR-3B Report</title>
    <style>
        body {
            font-family: 'Courier', monospace;
            font-size: 11px;
            margin: 10px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            padding: 4px 2px;
            text-align: left;
            vertical-align: top;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .bold {
            font-weight: bold;
        }

        .border-y {
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .border-top {
            border-top: 1px dashed #000;
        }

        .border-bottom {
            border-bottom: 1px dashed #000;
        }

        .border-double-y {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        .border-double-bottom {
            border-bottom: 2px solid #000;
        }

        .col-label {
            width: 25%;
        }

        .col-val {
            width: 18.75%;
        }
    </style>
</head>

<body>

    <div style="width: 100%;">
        <div style="float: left; font-size: 14px;" class="bold">{{ strtoupper($data['settings']->name ?? 'Fablead') }}</div>
        <div style="float: right;" class="bold">PAGE NO. 1</div>
        <div style="clear: both;"></div>
    </div>

    <div class="bold" style="font-size: 14px;">Regular DATE : FROM {{ $data['from_date'] }} TO {{ $data['to_date'] }}</div>
    <div class="bold" style="font-size: 14px;">GSTIN : {{ $data['settings']->gst_num ?? '' }}</div>

    <div class="text-center bold" style="margin: 15px 0; font-size: 14px;">GSTR-3B MONTHLY RETURN</div>

    <table>
        <thead>
            <tr class="border-y bold">
                <th class="col-label">BOOK</th>
                <th class="col-val text-right">TAXABLE.VALUE</th>
                <th class="col-val text-right">IGST PAYABLE</th>
                <th class="col-val text-right">CGST PAYABLE</th>
                <th class="col-val text-right">SGST PAYABLE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="bold" style="padding-top: 10px;">STATEWISE - REGISTERED</td>
            </tr>
            <tr class="border-bottom">
                <td class="col-label">TOTAL SALES :</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_reg']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_reg']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_reg']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_reg']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" class="bold" style="padding-top: 10px;">STATEWISE - UNREGISTERED AND COMPOSITION
                </td>
            </tr>
            <tr class="border-bottom">
                <td class="col-label">TOTAL SALES :</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_unreg']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_unreg']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_unreg']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_sales_unreg']['sgst'], 2) }}</td>
            </tr>
            <tr class="border-bottom bold">
                <td class="col-label">FINAL TOTAL SALES :</td>
                <td class="col-val text-right">
                    {{ number_format($data['total_sales_reg']['taxable'] + $data['total_sales_unreg']['taxable'], 2) }}
                </td>
                <td class="col-val text-right">
                    {{ number_format($data['total_sales_reg']['igst'] + $data['total_sales_unreg']['igst'], 2) }}</td>
                <td class="col-val text-right">
                    {{ number_format($data['total_sales_reg']['cgst'] + $data['total_sales_unreg']['cgst'], 2) }}</td>
                <td class="col-val text-right">
                    {{ number_format($data['total_sales_reg']['sgst'] + $data['total_sales_unreg']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td colspan="5" style="height: 10px;"></td>
            </tr>
            <tr>
                <td class="col-label">SALES RETURN ( - )</td>
                <td class="col-val text-right">{{ number_format($data['sales_return']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">SALES RETURN UN-REG ( - )</td>
                <td class="col-val text-right">{{ number_format($data['sales_return_unreg']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return_unreg']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return_unreg']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['sales_return_unreg']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">CREDIT NOTE ( - )</td>
                <td class="col-val text-right">{{ number_format($data['credit_note']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">CREDIT NOTE UN-REG ( - )</td>
                <td class="col-val text-right">{{ number_format($data['credit_note_unreg']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note_unreg']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note_unreg']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['credit_note_unreg']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">DEBIT NOTE ( + )</td>
                <td class="col-val text-right">{{ number_format($data['debit_note']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">DEBIT NOTE UN-REG ( + )</td>
                <td class="col-val text-right">{{ number_format($data['debit_note_unreg']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note_unreg']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note_unreg']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['debit_note_unreg']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">OTHER INCOME</td>
                <td class="col-val text-right">{{ number_format($data['other_income']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['other_income']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['other_income']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['other_income']['sgst'], 2) }}</td>
            </tr>
            <tr class="border-y bold">
                <td class="col-label">SALES TOTAL PAYABLE</td>
                <td class="col-val text-right">{{ number_format($data['net_payable']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_payable']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_payable']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_payable']['sgst'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="bold" style="margin-top: 20px; margin-bottom: 10px;">PURCHASE (ALL)</div>
    <table>
        <thead>
            <tr class="border-y bold">
                <th class="col-label">BOOK</th>
                <th class="col-val text-right">TAXABLE.VALUE</th>
                <th class="col-val text-right">IGST RECEIVABLE</th>
                <th class="col-val text-right">CGST RECEIVABLE</th>
                <th class="col-val text-right">SGST RECEIVABLE</th>
            </tr>
        </thead>
        <tbody>
            <tr class="border-bottom">
                <td class="col-label">TOTAL PURCHASE</td>
                <td class="col-val text-right">{{ number_format($data['purchase']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">PURCHASE RETURN ACCOUNT</td>
                <td class="col-val text-right">{{ number_format($data['purchase_return']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_return']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_return']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_return']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">DEBIT NOTE ( - )</td>
                <td class="col-val text-right">{{ number_format($data['purchase_debit']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_debit']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_debit']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_debit']['sgst'], 2) }}</td>
            </tr>
            <tr>
                <td class="col-label">CREDIT NOTE ( + )</td>
                <td class="col-val text-right">{{ number_format($data['purchase_credit']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_credit']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_credit']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['purchase_credit']['sgst'], 2) }}</td>
            </tr>
            <tr class="border-y bold">
                <td class="col-label">TOTAL RECEIVABLE </td>
                <td class="col-val text-right">{{ number_format($data['total_receivable']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_receivable']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_receivable']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['total_receivable']['sgst'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table style="margin-top: 20px;">
        <tbody>
            <tr class="border-double-y bold">
                <td class="col-label">NET PAYABLE/RECEIVABLE :-</td>
                <td class="col-val text-right">{{ number_format($data['net_diff']['taxable'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_diff']['igst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_diff']['cgst'], 2) }}</td>
                <td class="col-val text-right">{{ number_format($data['net_diff']['sgst'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="bold" style="margin-top: 10px;">
        BALANCE :- {{ number_format($data['final_net_tax'], 2) }}
    </div>

</body>

</html>
