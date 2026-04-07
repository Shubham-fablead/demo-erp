<!DOCTYPE html>
<html>
<head>
    <title>Invoice PDF</title>
    <meta charset="utf-8">
    <style>
        @page {
            size: 88mm 200mm;
            margin: 0;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            width: 80mm;
            background: #fff;
            -webkit-print-color-adjust: exact;
        }

        body {
            font-family: 'DejaVu Sans', monospace;
            font-size: 8px;
            line-height: 1.2;
            color: #000;
        }

        .invoice-wrapper {
            width: 80mm;
            padding: 4mm;
            box-sizing: border-box;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }

        .company-name {
            font-size: 13px;
            margin-bottom: 2px;
            display: block;
        }

        .company-info {
            font-size: 8px;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .invoice-title {
            font-size: 11px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
            margin: 8px 0;
            padding: 4px 0;
        }

        .info-table {
            margin-bottom: 8px;
        }

        .info-table td {
            font-size: 8px;
            padding: 1px 0;
        }

        .item-table {
            margin-bottom: 5px;
        }

        .item-table th {
            font-size: 8px;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            text-transform: uppercase;
        }

        .item-table td {
            padding: 5px 0;
            vertical-align: top;
            font-size: 8px;
            word-wrap: break-word;
        }

        .item-table th:first-child, .item-table td:first-child { width: 55%; }
        .item-table th:nth-child(2), .item-table td:nth-child(2) { width: 15%; text-align: center; }
        .item-table th:last-child, .item-table td:last-child { width: 30%; text-align: right; }

        .totals-table td {
            padding: 2px 0;
            font-size: 9px;
        }

        .grand-total {
            border-top: 1px double #000;
            border-bottom: 1px double #000;
            font-size: 11px;
            margin: 6px 0;
            padding: 5px 0;
        }

        .footer {
            margin-top: 15px;
            font-size: 8px;
            border-top: 1px dashed #000;
            padding-top: 10px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
    </style>
</head>

<body>
    <div class="invoice-wrapper">
        <div class="text-center">
            <span class="company-name bold uppercase">{{ $setting->name ?? '' }}</span>
            <div class="company-info">
                {{ $setting->address ?? '' }}<br>
                Ph: {{ $setting->phone ?? '' }} | GST: {{ $setting->gst_num ?? '' }}
            </div>
        </div>

        <div class="invoice-title text-center bold uppercase">
            Tax Invoice
        </div>

        <table class="info-table">
            <tr>
                <td width="35%">Bill No:</td>
                <td class="bold text-right">{{ $sales->order_number }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td class="text-right">{{ date('d-m-Y H:i', strtotime($sales->created_at)) }}</td>
            </tr>
            <tr>
                <td>Customer:</td>
                <td class="text-right bold">{{ $customer['name'] ?? 'Walk-in' }}</td>
            </tr>
            @if (isset($customer['email']) && $customer['email'] != 'Walk-in')
                <tr>
                    <td>Email:</td>
                    <td class="text-right">{{ $customer['email'] }}</td>
                </tr>
            @endif
        </table>

        @php
            $subTotal = 0;
            $totalDiscount = 0;
            $totalGst = 0;
            $labourTotal = 0;

            foreach ($orderItems as $item) {
                $lineTotal = $item->price * $item->quantity;
                $subTotal += $lineTotal;
                $totalDiscount += (float) ($item->discount_amount ?? 0);
                $totalGst += (float) ($item->product_gst_total ?? 0);
            }

            if (isset($labourItems)) {
                foreach ($labourItems as $labour) {
                    $labourTotal += $labour->price * $labour->qty;
                }
            }

            $shippingCharge = (float) ($sales->shipping ?? 0);
            $afterDiscount = $subTotal - $totalDiscount;
            $grandTotal = $afterDiscount + $totalGst + $labourTotal + $shippingCharge;
            $paidAmount = (float) ($paidAmount ?? 0);
            $pendingAmount = $grandTotal - $paidAmount;
        @endphp

        <table class="item-table">
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orderItems as $item)
                    @php $amount = $item->price * $item->quantity; @endphp
                    <tr>
                        <td class="text-left">
                            {{ $item->product->name ?? 'Product' }}
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($labourTotal > 0)
            <div class="divider"></div>
            <table class="item-table">
                <thead>
                    <tr>
                        <th class="text-left">Labour Item</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($labourItems as $labour)
                        <tr>
                            <td class="text-left">{{ $labour->labourItem->item_name }}</td>
                            <td class="text-center">{{ $labour->qty }}</td>
                            <td class="text-right">{{ number_format($labour->price * $labour->qty, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="divider"></div>

        <table class="totals-table">
            <tr>
                <td class="text-left">Subtotal</td>
                <td class="text-right">{{ number_format($subTotal, 2) }}</td>
            </tr>
            @if ($totalDiscount > 0)
                <tr>
                    <td class="text-left">Discount</td>
                    <td class="text-right">-{{ number_format($totalDiscount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-left">Price After Discount</td>
                    <td class="text-right">{{ number_format($afterDiscount, 2) }}</td>
                </tr>
            @endif
            @if ($totalGst > 0)
                <tr>
                    <td class="text-left">Tax (GST)</td>
                    <td class="text-right">{{ number_format($totalGst, 2) }}</td>
                </tr>
            @endif
            @if ($labourTotal > 0)
                <tr>
                    <td class="text-left">Labour Charges</td>
                    <td class="text-right">{{ number_format($labourTotal, 2) }}</td>
                </tr>
            @endif
            @if ($shippingCharge > 0)
                <tr>
                    <td class="text-left">Shipping</td>
                    <td class="text-right">{{ number_format($shippingCharge, 2) }}</td>
                </tr>
            @endif
        </table>

        <div class="grand-total bold">
            <table width="100%">
                <tr>
                    <td class="text-left uppercase">Grand Total</td>
                    <td class="text-right">₹{{ number_format($grandTotal, 2) }}</td>
                </tr>
            </table>
        </div>

        <table class="totals-table">
            <tr>
                <td class="text-left">Paid Amount</td>
                <td class="text-right">{{ number_format($paidAmount, 2) }}</td>
            </tr>
            @if ($pendingAmount > 0)
                <tr class="bold">
                    <td class="text-left">Balance Due</td>
                    <td class="text-right">{{ number_format($pendingAmount, 2) }}</td>
                </tr>
            @endif
        </table>

        <div class="footer text-center uppercase bold">
            {{ $setting->footer_text ?? 'Thank You! Visit Again..' }}
        </div>
    </div>
</body>

</html>
