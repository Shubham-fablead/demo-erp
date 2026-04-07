@extends('layout.app')
@section('title', 'Account Ledger')
@section('content')
    <style>
        .text_blance {
            font-weight: 600 !important;
            color: #1b2850 !important;
            font-size: 17px !important;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Account Ledger</h4>
            </div>
            <div class="d-flex justify-content-end">
                <div class="bg-light shadow-sm"
                    style="min-width: 300px; max-width: 400px; padding: 8px; border: 1px solid #1b2850; border-radius: 0.25rem;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text_blance">Total Balance</h6>
                        <h5 class="mb-0 fw-bold" style="color:#ff9f43"><span id="totalBalance">0.00</span></h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Payment Date</th>
                                    <th>Job Card No.</th>
                                    <th>Payment Method</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable();

            fetchAccountLedger();

            function fetchAccountLedger() {
                $.ajax({
                    url: "/api/account-ledger",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let ledgerData = response.data;
                            let currency = response.currency || "₹";
                            let tableBody = [];
                            let totalBalance = 0;

                            ledgerData.forEach((ledger) => {
                                // Extract balance value (remove symbol for math)
                                let balance = parseFloat(ledger.balance.replace(/[^\d.-]/g,
                                    '')) || 0;
                                totalBalance += balance;

                                tableBody.push([
                                    ledger.customer_name,
                                    ledger.date,
                                    ledger.details,
                                    ledger.payment_method,
                                    ledger.debit,
                                    ledger.credit,
                                    ledger.balance,
                                ]);
                            });

                            table.clear().rows.add(tableBody).draw();

                            // Update total balance display with currency
                            $("#totalBalance").text(currency + totalBalance.toFixed(2));
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html(
                                '<tr><td colspan="7">No data found</td></tr>'
                            );
                            $("#totalBalance").text("0.00");
                        }
                    },
                    error: function(xhr) {
                        console.log("Error:", xhr);
                    }
                });
            }
        });
    </script>
@endpush
