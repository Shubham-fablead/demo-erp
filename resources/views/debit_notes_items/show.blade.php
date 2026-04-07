@extends('layout.app')

@section('title', 'View Debit Note')

@section('content')
    <style>
        .invoice-box {
            max-width: 100%;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
            background: #fff;
            overflow: hidden;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:last-child {
            border-top: 2px solid #eee;
            font-weight: bold;
            text-align: right;
        }

        .debit-note-title {
            color: #ff9f43;
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1;
        }

        .note-meta-row {
            display: flex;
            justify-content: space-between;
            gap: 24px;
        }

        .note-meta-left {
            width: 50%;
        }

        .note-meta-right {
            width: 50%;
            text-align: right;
        }

        .note-meta-right table {
            width: auto;
            margin-left: auto;
            margin-top: 8px;
        }

        .note-meta-right table td {
            text-align: right;
            white-space: nowrap;
            padding: 3px 0 3px 12px;
        }

        .note-meta-right table td:first-child {
            padding-left: 0;
        }

        @media only screen and (max-width: 768px) {
            .invoice-box {
                padding: 16px;
                font-size: 14px;
                line-height: 1.5;
            }

            .debit-note-title {
                font-size: 38px;
                margin-bottom: 12px;
            }

            .note-meta-row {
                flex-direction: column;
                gap: 12px;
            }

            .note-meta-left,
            .note-meta-right {
                width: 100%;
            }

            .note-meta-right,
            .note-meta-right table,
            .note-meta-right table td {
                text-align: left !important;
                margin-left: 0;
            }

            .invoice-box > table > tbody > tr.heading {
                display: none;
            }

            .invoice-box > table > tbody > tr.item {
                display: block;
                border: 1px solid #eee;
                border-radius: 8px;
                margin-top: 12px;
                padding: 8px 10px;
            }

            .invoice-box > table > tbody > tr.item td {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                gap: 10px;
                width: 100%;
                text-align: left !important;
                border-bottom: 1px solid #f1f1f1;
                padding: 6px 0;
            }

            .invoice-box > table > tbody > tr.item td:last-child {
                border-bottom: none;
            }

            .invoice-box > table > tbody > tr.item td::before {
                content: attr(data-label);
                font-weight: 700;
                color: #333;
                white-space: nowrap;
            }

            .invoice-box > table > tbody > tr.total td:first-child {
                display: none;
            }

            .invoice-box > table > tbody > tr.total td:last-child {
                display: block;
                width: 100%;
                text-align: left !important;
                font-size: 24px !important;
                padding-top: 12px;
            }
        }

        .rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .rtl table {
            text-align: right;
        }

        .rtl .invoice-box table tr.total td:last-child {
            text-align: left;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>View Debit Note</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(27, 'edit'))
                <a href="{{ route('debit-notes-items.index') }}" class="btn btn-added">Back to List</a>
                @endif
            </div>
        </div>

        <div class="invoice-box">
            <table cellpadding="0" cellspacing="0">
                <tr class="top">
                    <td colspan="2">
                        <table>
                            <tr>
                                <td class="title">
                                    <img src="{{ $compenyinfo->logo ? env('ImagePath') . 'storage/' . $compenyinfo->logo : env('ImagePath') . '/admin/assets/img/logo.png' }}"
                                        style="width:100%; max-width:150px;">
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="6">
                        <table style="width: 100%;">
                            <tr class="note-meta-row">
                                <td class="note-meta-left">
                                    <div class="debit-note-title">Debit Note</div>
                                    <br> <br>
                                    <strong id="displayName"></strong><br>
                                    <span style="font-weight: bold;">Phone: </span><span id="displayPhone"></span>
                                </td>
                                <td class="note-meta-right">
                                    <strong>{{ $compenyinfo->name ?? '' }}</strong><br>
                                    {!! nl2br(e($compenyinfo->address ?? '')) !!}
                                    <br> <br>
                                    <table>
                                        <tr>
                                            <td style="font-weight: bold;">Date:</td>
                                            <td id="debitNoteDate"></td>
                                        </tr>
                                        <tr>
                                            <td id="transactionLabel" style="font-weight: bold;">Invoice Number:</td>
                                            <td id="transactionNumber"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr class="heading">
                    <td> Reason</td>
                    <td style="text-align: center;">Type</td>
                    <td style="text-align: center;">Total Amount</td>
                    <td style="text-align: center;">Settlement Amount</td>
                    <td style="text-align: center;">Final Total</td>
                </tr>

                <tr class="item">
                    <td id="reason" data-label="Reason"></td>
                    <td style="text-align: center;" id="typeName" data-label="Type"></td>
                    <td style="text-align: center;" id="totalAmt" data-label="Total Amount"></td>
                    <td style="text-align: center;" id="settlementAmt" data-label="Settlement Amount"></td>
                    <td style="text-align: center;" id="finalTotal" data-label="Final Total"></td>
                </tr>

                <tr class="total">
                    <td colspan="3"></td>
                    <td colspan="2" style="font-size: 20px;">
                        TOTAL:
                        <span id="totalAmount"></span>
                    </td>
                </tr>

            </table>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {

            let id = "{{ $id }}";
            let currencySymbol = "{{ $currencySymbol ?? '₹' }}";
            let currencyPosition = "{{ $currencyPosition ?? 'left' }}";
            let selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            function formatCurrency(amount) {
                let formatted = parseFloat(amount || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                return currencyPosition === 'left' ?
                    currencySymbol + formatted :
                    formatted + currencySymbol;
            }

            $.ajax({
                url: `/api/debit-note-items/${id}`,
                type: "GET",
                headers: {
                    "Authorization": "Bearer " + localStorage.getItem("authToken")
                },
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                success: function(res) {
                    if (res.status !== 'success') return;

                    let d = res.data;

                    if (d.transaction_type === 'receipt') {
                        $('#displayName').text(d.order?.user?.name ?? 'N/A');
                        $('#displayPhone').text(d.order?.user?.phone ?? 'N/A');
                        $('#transactionLabel').text('Order Number:');
                        $('#transactionNumber').text(d.order?.order_number ?? 'N/A');
                    } else {
                        $('#displayName').text(d.purchase_invoice?.vendor?.name ?? 'N/A');
                        $('#displayPhone').text(d.purchase_invoice?.vendor?.phone ?? 'N/A');
                        $('#transactionLabel').text('Invoice Number:');
                        $('#transactionNumber').text(d.invoice_number ?? 'N/A');
                    }

                    // ✅ Date
                    let dateObj = new Date(d.created_at);
                    let formattedDate = dateObj.toLocaleDateString('en-IN', {
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    $('#debitNoteDate').text(formattedDate);

                    // ✅ Debit note row
                    $('#reason').text(d.reason);
                    $('#typeName').text(d.credit_note_type?.type_name ?? 'N/A');
                    $('#totalAmt').text(formatCurrency(d.grand_total));
                    $('#settlementAmt').text(formatCurrency(d.settlement_amount));
                    $('#finalTotal').text(formatCurrency(d.total));

                    // ✅ Totals
                    $('#totalAmount').text(formatCurrency(d.total));
                },
                error: function(err) {
                    console.error(err);
                    alert('Unable to load debit note');
                }
            });
        });
    </script>
@endpush
