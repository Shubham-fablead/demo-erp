@extends('layout.app')

@section('title', 'Balance Sheet')

@section('content')
    @php
        $sym = $currency_symbol ?? '₹';
        $pos = $currency_position ?? 'left';
        function money_fmt($v, $sym, $pos)
        {
            $v = number_format((float) $v, 2);
            return $pos === 'left' ? $sym . $v : $v . $sym;
        }
    @endphp

    <style>
        .bs-container {
            background: #fff;
            padding: 30px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            max-width: 1000px;
            margin: auto;
        }

        .bs-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .bs-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .bs-header small {
            color: #666;
        }

        .bs-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .bs-table th,
        .bs-table td {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        .section-title {
            background: #f3f4f6;
            font-weight: 600;
        }

        .text-end {
            text-align: right;
        }

        .subtotal {
            font-weight: 600;
        }

        .total {
            font-weight: 700;
            background: #f9fafb;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Balance Sheet</h4>
                <small class="d-none">As at {{ $period['to'] ?? date('d/m/Y') }}</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="bs-table">
                    <!-- Assets -->
                    <tr class="section-title">
                        <td colspan="2">Assets</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Current Assets</b></td>
                    </tr>
                    <tr>
                        <td>Cash</td>
                        <td class="text-end">{{ money_fmt($assets['cash'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr>
                        <td>Bank</td>
                        <td class="text-end">{{ money_fmt($assets['bank'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr class="d-none">
                        <td>Accounts Receivable</td>
                        <td class="text-end">{{ money_fmt($assets['accounts_receivable'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr>
                        <td>Inventory</td>
                        <td class="text-end">{{ money_fmt($assets['inventory'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    @if (($assets['input_tax_credit'] ?? 0) > 0)
                        <tr class="d-none">
                            <td>Input Tax Credit (GST)</td>
                            <td class="text-end">{{ money_fmt($assets['input_tax_credit'] ?? 0, $sym, $pos) }}</td>
                        </tr>
                    @endif
                    <tr class="subtotal">
                        <td>Total Current Assets</td>
                        <td class="text-end">{{ money_fmt($totals['assets'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr class="total">
                        <td><b>Total Assets</b></td>
                        <td class="text-end"><b>{{ money_fmt($totals['assets'] ?? 0, $sym, $pos) }}</b></td>
                    </tr>

                    <!-- Liabilities -->
                    <tr class="section-title">
                        <td colspan="2">Liabilities</td>
                    </tr>
                    <tr>
                        <td colspan="2"><b>Current Liabilities</b></td>
                    </tr>
                    <tr>
                        <td>Accounts Payable</td>
                        <td class="text-end">{{ money_fmt($liabilities['accounts_payable'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    @if (($liabilities['gst_payable'] ?? 0) > 0)
                        <tr>
                            <td>GST Payable</td>
                            <td class="text-end">{{ money_fmt($liabilities['gst_payable'] ?? 0, $sym, $pos) }}</td>
                        </tr>
                    @endif
                    <tr class="subtotal">
                        <td>Total Current Liabilities</td>
                        <td class="text-end">{{ money_fmt($totals['liabilities'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr class="total">
                        <td><b>Total Liabilities</b></td>
                        <td class="text-end"><b>{{ money_fmt($totals['liabilities'] ?? 0, $sym, $pos) }}</b></td>
                    </tr>

                    <!-- Equity -->
                    <tr class="section-title">
                        <td colspan="2">Equity</td>
                    </tr>
                    <tr>
                        <td>Retained Earnings</td>
                        <td class="text-end">{{ money_fmt($equity['retained_earnings'] ?? 0, $sym, $pos) }}</td>
                    </tr>
                    <tr class="total">
                        <td><b>Total Equity</b></td>
                        <td class="text-end"><b>{{ money_fmt($equity['retained_earnings'] ?? 0, $sym, $pos) }}</b></td>
                    </tr>

                    <!-- Final Total -->
                    <tr class="total">
                        <td><b>Total Liabilities & Equity</b></td>
                        <td class="text-end"><b>{{ money_fmt($totals['liabilities_equity'] ?? 0, $sym, $pos) }}</b></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
@endsection
