@extends('layout.app')
@section('title', 'Account Ledger')
@section('content')
    <style>
        /* Responsive breakpoints for all screen sizes */
        @media screen and (max-width:575.98px){
                #appointmentForm .select2-container{
                    width:100% !important;
                    max-width:100% !important;
                }
            }
        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {

            /* Filter Section Mobile Styles */
            #appointmentForm .row {
                flex-direction: column !important;
                gap: 10px !important;
            }

            #appointmentForm .col-lg-3,
            #appointmentForm .col-sm-6,
            #appointmentForm .col-6 {
                width: 100% !important;
                /* margin-bottom: 10px; */
            }

            /* Export buttons - full width on mobile */
            .d-flex.justify-content-end.mb-3 {
                flex-direction: column;
                gap: 8px;
            }

            .d-flex.justify-content-end.mb-3 button {
                width: 100% !important;
            }

            /* Table responsive styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table {
                font-size: 11px;
            }

            .table th,
            .table td {
                padding: 6px 3px;
            }

            /* Paid Payments Table - Show only Invoice No. and Details */
            #paid-payments-table thead th:nth-child(3),
            #paid-payments-table tbody td:nth-child(3),
            #paid-payments-table thead th:nth-child(4),
            #paid-payments-table tbody td:nth-child(4),
            #paid-payments-table thead th:nth-child(5),
            #paid-payments-table tbody td:nth-child(5),
            #paid-payments-table thead th:nth-child(6),
            #paid-payments-table tbody td:nth-child(6),
            #paid-payments-table thead th:nth-child(7),
            #paid-payments-table tbody td:nth-child(7) {
                display: none;  
            }

            /* Pending Payments Table - Show only Invoice No. and Details */
            #pending-payments-table thead th:nth-child(3),
            #pending-payments-table tbody td:nth-child(3),
            #pending-payments-table thead th:nth-child(4),
            #pending-payments-table tbody td:nth-child(4),
            #pending-payments-table thead th:nth-child(5),
            #pending-payments-table tbody td:nth-child(5) {
                display: none;
            }

            /* Center Details column (2nd column) */
            #paid-payments-table thead th:nth-child(2),
            #paid-payments-table tbody td:nth-child(2),
            #pending-payments-table thead th:nth-child(2),
            #pending-payments-table tbody td:nth-child(2) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .ledger-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            #appointmentForm .row {
                flex-wrap: wrap;
                gap: 10px;
            }

            #appointmentForm .col-lg-3 {
                flex: 0 0 calc(50% - 5px);
            }

            .d-flex.justify-content-end.mb-3 {
                flex-direction: row;
                gap: 10px;
            }

            .d-flex.justify-content-end.mb-3 button {
                flex: 1;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px 4px;
            }

            /* Paid Payments Table - Show Invoice No., Details, Product(s), Total Amount */
            #paid-payments-table thead th:nth-child(5),
            #paid-payments-table tbody td:nth-child(5),
            #paid-payments-table thead th:nth-child(6),
            #paid-payments-table tbody td:nth-child(6),
            #paid-payments-table thead th:nth-child(7),
            #paid-payments-table tbody td:nth-child(7) {
                display: none;
            }

            /* Pending Payments Table - Show Invoice No., Details, Product(s) */
            #pending-payments-table thead th:nth-child(4),
            #pending-payments-table tbody td:nth-child(4),
            #pending-payments-table thead th:nth-child(5),
            #pending-payments-table tbody td:nth-child(5) {
                display: none;
            }

            /* Center Details column (2nd column) */
            #paid-payments-table thead th:nth-child(2),
            #paid-payments-table tbody td:nth-child(2),
            #pending-payments-table thead th:nth-child(2),
            #pending-payments-table tbody td:nth-child(2) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .ledger-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .table {
                font-size: 13px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets (2nd column) */
            #paid-payments-table thead th:nth-child(2),
            #paid-payments-table tbody td:nth-child(2),
            #pending-payments-table thead th:nth-child(2),
            #pending-payments-table tbody td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on tablets */
            .ledger-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            .table {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop (2nd column) */
            #paid-payments-table thead th:nth-child(2),
            #paid-payments-table tbody td:nth-child(2),
            #pending-payments-table thead th:nth-child(2),
            #pending-payments-table tbody td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .ledger-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .ledger-details-row {
            display: none;
        }

        .ledger-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .ledger-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .ledger-details-list {
            margin-bottom: 15px;
        }

        .ledger-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .ledger-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .ledger-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .ledger-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        /* Toggle button styles */
        .ledger-toggle-btn-table {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .ledger-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .ledger-toggle-btn-table.minus {
            background: #dc3545;
        }

        .ledger-toggle-btn-table.minus:hover {
            background: #c82333;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Account Ledger</h4>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <!-- View PDF & Excel buttons -->
                {{-- <div class="d-flex justify-content-end mb-3">
                    <button type="button" class="btn btn-sm me-2 text-white btn-danger" id="viewPdfBtn">
                        <i class="fas fa-file-pdf"></i> Export PDF
                    </button>
                    <button type="button" class="btn btn-sm text-white btn-success" id="viewCsvBtn">
                        <i class="fas fa-file-csv"></i> Export Excel
                    </button>
                </div> --}}
                <div class="row g-2 justify-content-end mb-3">
                    <div class="col-6 col-lg-1 text-end">
                        <button type="button" class="btn btn-sm w-100 text-white btn-danger" id="viewPdfBtn">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>

                    <div class="col-6 col-lg-1 text-end">
                        <button type="button" class="btn btn-sm w-100 text-white btn-success" id="viewCsvBtn">
                            <i class="fas fa-file-csv"></i> Excel
                        </button>
                    </div>
                </div>

                <form id="appointmentForm">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" id="type" class="form-control select2 Name">
                                    <option value="">Select Type</option>
                                    <option value="customer">Customer</option>
                                    <option value="vendor">Vendor</option>
                                </select>
                            </div>
                        </div>

                        <!-- Customer Dropdown -->
                        <div class="col-12 col-sm-6 col-lg-3" id="customerDropdown" style="display: none;">
                            <div class="form-group">
                                <label>Customer Name</label>
                                <select name="customer_id" id="customer_id" class="form-control select2 Name">
                                    <option value="">Select Customer</option>
                                    <option value="all">All Customers</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id ?? 'null' }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-customer_id"></span>
                            </div>
                        </div>

                        <!-- Vendor Dropdown -->
                        <div class="col-12 col-sm-6 col-lg-3" id="vendorDropdown" style="display: none;">
                            <div class="form-group">
                                <label>Vendor Name</label>
                                <select name="vendor_id" id="vendor_id" class="form-control select2 Name">
                                    <option value="">Select Vendor</option>
                                    <option value="all">All Vendors</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{ $vendor->id ?? 'null' }}">
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-vendor_id"></span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label>Month</label>
                                <select name="month" id="month" class="form-control select2 Name">
                                    <option value="">Select Month</option>
                                    <option value="all">All Months</option> {{-- ✅ Added --}}
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                <span class="text-danger error-month"></span>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="form-group">
                                <label>Year</label>
                                <select name="year" id="year" class="form-control select2 Name">
                                    <option value="">Select Year</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-year"></span>
                            </div>
                        </div>
                        {{-- <div class="col-lg-2 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Vehicle Number</label>
                                <input type="text" name="vehicle_number" id="vehicle_number" class="form-control">
                                <span class="text-danger error-vehicle_number"></span>
                            </div>
                        </div> --}}
                    </div>
                </form>
                <div id="paymentDetails">
                    <!-- Payment details will be displayed here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Global variables
        var ledgerDataMap = {};

        // Helper function to build expandable row content for Paid Payments
        function buildPaidPaymentExpandableRowContent(payment, order, productNames, paymentMethod, currency,
            currencyPosition) {
            const formatCurrency = (amount) => currencyPosition === 'left' ? `${currency}${amount}` :
            `${amount}${currency}`;

            return `
                <td colspan="7" class="ledger-details-content">
                    <div class="ledger-details-list">
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Product(s):</span>
                            <span class="ledger-detail-value-simple">${productNames}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Total Amount:</span>
                            <span class="ledger-detail-value-simple">${formatCurrency(order.total_amount || order.grand_total || 0)}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Paid Amount:</span>
                            <span class="ledger-detail-value-simple" style="font-weight: bold; color: #28a745;">${formatCurrency(payment.total_amount || order.amount_total || 0)}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Payment Date:</span>
                            <span class="ledger-detail-value-simple">${payment.payment_date || order.created_at || '-'}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Payment Method:</span>
                            <span class="ledger-detail-value-simple">${paymentMethod}</span>
                        </div>
                    </div>
                </td>
            `;
        }

        // Helper function to build expandable row content for Pending Payments
        function buildPendingPaymentExpandableRowContent(payment, order, productNames, currency, currencyPosition) {
            const formatCurrency = (amount) => currencyPosition === 'left' ? `${currency}${amount}` :
            `${amount}${currency}`;

            return `
                <td colspan="5" class="ledger-details-content">
                    <div class="ledger-details-list">
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Product(s):</span>
                            <span class="ledger-detail-value-simple">${productNames}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Total Amount:</span>
                            <span class="ledger-detail-value-simple">${formatCurrency(order.total_amount || order.grand_total || 0)}</span>
                        </div>
                        <div class="ledger-detail-row-simple">
                            <span class="ledger-detail-label-simple">Remaining Amount:</span>
                            <span class="ledger-detail-value-simple" style="font-weight: bold; color: #dc3545;">${formatCurrency(payment.amount_total || order.total_amount || 0)}</span>
                        </div>
                    </div>
                </td>
            `;
        }

        // Toggle function for Paid Payments table rows
        window.togglePaidPaymentRowDetails = function(paymentIndex) {
            const btn = $(`.ledger-toggle-btn-table[data-paid-index="${paymentIndex}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.ledger-details-row[data-paid-index="${paymentIndex}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const paymentData = ledgerDataMap.paidPayments && ledgerDataMap.paidPayments[paymentIndex];
                if (paymentData) {
                    detailsRow = $('<tr>')
                        .addClass('ledger-details-row')
                        .attr('data-paid-index', paymentIndex)
                        .html(buildPaidPaymentExpandableRowContent(
                            paymentData.payment,
                            paymentData.order,
                            paymentData.productNames,
                            paymentData.paymentMethod,
                            paymentData.currency,
                            paymentData.currencyPosition
                        ));
                    row.after(detailsRow);
                } else {
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('−');
            }
        };

        // Toggle function for Pending Payments table rows
        window.togglePendingPaymentRowDetails = function(paymentIndex) {
            const btn = $(`.ledger-toggle-btn-table[data-pending-index="${paymentIndex}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.ledger-details-row[data-pending-index="${paymentIndex}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const paymentData = ledgerDataMap.pendingPayments && ledgerDataMap.pendingPayments[paymentIndex];
                if (paymentData) {
                    detailsRow = $('<tr>')
                        .addClass('ledger-details-row')
                        .attr('data-pending-index', paymentIndex)
                        .html(buildPendingPaymentExpandableRowContent(
                            paymentData.payment,
                            paymentData.order,
                            paymentData.productNames,
                            paymentData.currency,
                            paymentData.currencyPosition
                        ));
                    row.after(detailsRow);
                } else {
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('−');
            }
        };

        // Helper function to get product names (will be defined in displayPaymentDetails scope)
        function getProductNames(order) {
            if (order.items && order.items.length > 0) {
                return order.items.map(i => i.product?.name || '-').join(', ');
            } else if (order.products_with_names && order.products_with_names.length > 0) {
                return order.products_with_names.map(p => p.product_name || '-').join(', ');
            }
            return '-';
        }

        // Helper function to capitalize
        function capitalize(str) {
            if (!str) return '-';
            return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
        }

        $(document).ready(function() {
            $('#type').on('change', function() {
                if (this.value === "customer") {
                    $('#customerDropdown').show();
                    $('#vendorDropdown').hide();
                } else if (this.value === "vendor") {
                    $('#vendorDropdown').show();
                    $('#customerDropdown').hide();
                } else {
                    $('#customerDropdown').hide();
                    $('#vendorDropdown').hide();
                }
            });
        });
        $(document).ready(function() {
            // localStorage.getItem('selectedSubAdminId');
            // console.log(localStorage.getItem("selectedSubAdminId"));


            $('.Name').select2({
                placeholder: 'Select an option',
                width: '100%'
            });

            // Trigger fetch when any filter is changed
            $('#type, #customer_id, #vendor_id, #month, #year').on('change', fetchPayments);
            let debounceTimer;

            // $('#vehicle_number').on('input', function() {
            //     clearTimeout(debounceTimer);
            //     debounceTimer = setTimeout(fetchPayments, 500); // Debounce typing
            // });

            $('#customer_id, #vendor_id').on('change', function() {
                const selectedOption = $(this).find(':selected');
                const subbranchId = selectedOption.data('subbranch-id');

                if (subbranchId) {
                    // console.log("Selected Sub Branch ID:", subbranchId);

                    // Optional: store it in localStorage for API calls
                    localStorage.setItem("selectedSubBranchId", subbranchId);
                } else {
                    // console.log("No subbranch ID found.");
                    localStorage.removeItem("selectedSubBranchId");
                }
            });

            function fetchPayments() {
                var authToken = localStorage.getItem("authToken");
                var type = $('#type').val(); // customer | vendor
                var selectedId = type === 'customer' ? $('#customer_id').val() : $('#vendor_id').val();
                var month = $('#month').val();
                var year = $('#year').val();
                var selectedId = type === 'customer' ? $('#customer_id').val() : $('#vendor_id').val();
                // if (!selectedId) {
                //     $('#paymentDetails').html('<div class="text-danger">Please select an option.</div>');
                //     return;
                // }
                if (!selectedId) {
                    let message = 'Please select an option.';

                    if (type === 'customer') {
                        message = 'Please select customer name.';
                    } else if (type === 'vendor') {
                        message = 'Please select vendor name.';
                    }

                    $('#paymentDetails').html('<div class="text-danger">' + message + '</div>');
                    return;
                }

                $.ajax({
                    url: '/api/get-payment-details',
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: {
                        type: type, // send this!
                        customer_id: type === 'customer' ? $('#customer_id').val() : null,
                        vendor_id: type === 'vendor' ? $('#vendor_id').val() : null,
                        month: month,
                        year: year,
                        // vehicle_number: vehicleNumber,
                        selectedSubAdminId: localStorage.getItem("selectedSubAdminId")
                    },
                    success: function(response) {
                        displayPaymentDetails(response);
                    },
                    error: function(xhr) {
                        $('#paymentDetails').html(
                            '<div class="text-danger">Failed to fetch payment details.</div>');
                    }
                });
            }

            function displayPaymentDetails(data) {
                const currency = data.settings.currency_symbol || '₹';
                const currencyPosition = data.settings.currency_position || 'left';

                // function formatCurrency(amount) {
                //     return currencyPosition === 'left' ? `${currency}${amount}` : `${amount}${currency}`;
                // }

                function formatCurrency(amount) {
                    amount = parseFloat(amount || 0);

                    // Indian number format with commas
                    const formatted = amount.toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });

                    return currencyPosition === 'left' ? `${currency}${formatted}` : `${formatted}${currency}`;
                }

                function formatDate(dateString) {
                    if (!dateString) return '-';
                    const date = new Date(dateString);
                    return date.toLocaleDateString('en-GB', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric'
                    }) + ' ' + date.toLocaleTimeString('en-GB', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }

                function capitalize(str) {
                    if (!str) return '-';
                    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
                }

                // ✅ Common product name extractor
                function getProductNames(order) {
                    if (order.items && order.items.length > 0) {
                        return order.items.map(i => i.product?.name || '-').join(', ');
                    } else if (order.products_with_names && order.products_with_names.length > 0) {
                        return order.products_with_names.map(p => p.product_name || '-').join(', ');
                    }
                    return '-';
                }

                let html = '';

                // Store payment data for expandable rows
                ledgerDataMap.paidPayments = {};
                ledgerDataMap.pendingPayments = {};

                // === Paid Payments ===
                html += `
    <div class="card shadow mb-4">
        <div class="card-header text-dark">
            <h5 class="mb-0 fw-bold">Paid Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0" id="paid-payments-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice No.</th>
                            <th class="text-center">Details</th>
                            <th>Product(s)</th>
                            <th>Total Amount</th>
                            <th>Paid Amount</th>
                            <th>Payment Date</th>
                            <th>Payment Method</th>
                        </tr>
                    </thead>
                    <tbody>`;
                let totalPaid = 0;
                if (data.paidPayments.length > 0) {
                    data.paidPayments.forEach(function(payment, index) {
                        const order = payment.order || payment;
                        const productNames = getProductNames(order);
                        const paymentMethod = capitalize(payment.payment_method || order.payment_status ||
                            '-');

                        // Store payment data for expandable row
                        ledgerDataMap.paidPayments[index] = {
                            payment: payment,
                            order: order,
                            productNames: productNames,
                            paymentMethod: paymentMethod,
                            currency: currency,
                            currencyPosition: currencyPosition
                        };

                        // Toggle button for Details column
                        const detailsToggle = `
                            <button class="ledger-toggle-btn-table" onclick="togglePaidPaymentRowDetails('${index}')" data-paid-index="${index}">
                                <span class="toggle-icon">+</span>
                            </button>
                        `;

                        html += `
            <tr>
                <td>${order.order_number || order.invoice_number || '-'}</td>
                <td>${detailsToggle}</td>
                <td>${productNames}</td>
                <td>${formatCurrency(order.total_amount || order.grand_total || 0)}</td>
                <td>${formatCurrency(payment.total_amount || order.amount_total || 0)}</td>
                <td>${(payment.payment_date || order.created_at)}</td>
                <td>${capitalize(payment.payment_method || order.payment_status || '-')}</td>
            </tr>`;
                        totalPaid += parseFloat(payment.total_amount || order.amount_total || 0);
                    });
                } else {
                    html += `<tr><td colspan="7" class="text-center text-muted">No record available</td></tr>`;
                }
                html += `
    </tbody>
    </table>
    </div>
    <div class="d-flex justify-content-end pe-3 mt-2 mb-2">
        <div style="border: 1px solid #1b2850; border-radius: 5px; padding: 4px 10px; display: inline-block;">
            <span style="font-weight: bold; font-size: 15px; color: #1b2850;">
                Total Paid:
            </span>
            <span style="font-weight: bold; font-size: 15px; color: orange;">
                ${formatCurrency(totalPaid.toFixed(2))}
            </span>
        </div>
    </div>
    </div>`;

                // === Pending Payments ===
                html += `
    <div class="card shadow mb-4">
        <div class="card-header text-dark">
            <h5 class="mb-0 fw-bold">Pending Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped mb-0" id="pending-payments-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Invoice No.</th>
                            <th class="text-center">Details</th>
                            <th>Product(s)</th>
                            <th>Total Amount</th>
                            <th>Remaining Amount</th>
                        </tr>
                    </thead>
                    <tbody>`;
                let totalPending = 0;
                if (data.pendingPayments.length > 0) {
                    data.pendingPayments.forEach(function(payment, index) {
                        const order = payment.order || payment;
                        const productNames = getProductNames(order);

                        // Store payment data for expandable row
                        ledgerDataMap.pendingPayments[index] = {
                            payment: payment,
                            order: order,
                            productNames: productNames,
                            currency: currency,
                            currencyPosition: currencyPosition
                        };

                        // Toggle button for Details column
                        const detailsToggle = `
                            <button class="ledger-toggle-btn-table" onclick="togglePendingPaymentRowDetails('${index}')" data-pending-index="${index}">
                                <span class="toggle-icon">+</span>
                            </button>
                        `;

                        html += `
            <tr>
                <td>${order.order_number || order.invoice_number || payment.invoice_id || '-'}</td>
                <td>${detailsToggle}</td>
                <td>${productNames}</td>
                <td>${formatCurrency(order.total_amount || order.grand_total || 0)}</td>
                <td>${formatCurrency(payment.amount_total || order.total_amount || 0)}</td>
            </tr>`;
                        totalPending += parseFloat(payment.amount_total || order.amount_total || 0);
                    });
                } else {
                    html += `<tr><td colspan="5" class="text-center text-muted">No record available</td></tr>`;
                }
                html += `
    </tbody>
    </table>
    </div>
    <div class="d-flex justify-content-end pe-3 mt-2 mb-2">
        <div style="border: 1px solid #1b2850; border-radius: 5px; padding: 4px 10px; display: inline-block;">
            <span style="font-weight: bold; font-size: 15px; color: #1b2850;">
                Total Pending:
            </span>
            <span style="font-weight: bold; font-size: 15px; color: orange;">
                ${formatCurrency(totalPending.toFixed(2))}
            </span>
        </div>
    </div>
    </div>`;

                $('#paymentDetails').html(html);
            }



            $('#viewCsvBtn').on('click', function() {
                const type = $('#type').val();
                const customer_id = $('#customer_id').val();
                const vendor_id = $('#vendor_id').val();
                const month = $('#month').val();
                const year = $('#year').val();
                // const vehicleNumber = $('#vehicle_number').val().trim();

                if (!type) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Type',
                        text: 'Please select a type before exporting.',
                    });
                    return;
                }

                if (type === 'customer') {
                    const selectedValue = $('#customer_id').val();
                    if (!selectedValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Customer',
                            text: 'Please select a customer before exporting.',
                        });
                        return;
                    }
                    userId = selectedValue === "all" ? "all" : selectedValue.split('|')[0];
                } else if (type === 'vendor') {
                    const selectedValue = $('#vendor_id').val();
                    if (!selectedValue) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Vendor',
                            text: 'Please select a vendor before exporting.',
                        });
                        return;
                    }
                    userId = selectedValue === "all" ? "all" : selectedValue.split('|')[0];
                }

                // const parts = selectedValue.split('|');
                // const userId = parts[0] !== 'null' ? parts[0] : '';
                // const customerName = parts[1] || '';

                const authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // console.log(selectedSubAdminId);

                Swal.fire({
                    title: 'Generating Excel...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '/api/export-account-ledger',
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + authToken
                    },
                    data: {
                        type: type,
                        customer_id: customer_id,
                        vendor_id: vendor_id,
                        month: month,
                        year: year,
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    success: function(res) {
                        Swal.close(); // ✅ close the loader
                        if (res.status) {
                            const a = document.createElement('a');
                            a.href = res.file_url;
                            a.download = res.file_name;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function(err) {
                        Swal.close();
                        Swal.fire('Error', 'Failed to generate Excel file.', 'error');
                        console.error(err);
                    }
                });

            });


            $('#viewPdfBtn').on('click', function() {
                const type = $('#type').val();
                const customer_id = $('#customer_id').val();
                const vendor_id = $('#vendor_id').val();
                const month = $('#month').val();
                const year = $('#year').val();
                const authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                if (!type) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Type',
                        text: 'Please select a type before exporting.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Generating PDF...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '/api/account-ledger/download-pdf',
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        type: type,
                        customer_id: customer_id,
                        vendor_id: vendor_id,
                        month: month,
                        year: year,
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status && response.file_url) {
                            // ✅ Create temporary link to trigger download
                            const link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || 'account_ledger.pdf';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Download Failed',
                                text: 'File URL not found in response.'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Download Failed',
                            text: 'Failed to generate PDF.'
                        });
                    }
                });
            });

            // Resize handler for responsive behavior
            let resizeTimer;
            let lastWidth = $(window).width();

            function forceLedgerCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleLedgerResize() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastWidth = currentWidth;

                    // Force CSS recalculation
                    forceLedgerCSSRecalculation();

                    const paidTable = document.getElementById('paid-payments-table');
                    const pendingTable = document.getElementById('pending-payments-table');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [paidTable, pendingTable, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    forceLedgerCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.ledger').on('resize.ledger', handleLedgerResize);

            if (window.ledgerResizeHandler) {
                window.removeEventListener('resize', window.ledgerResizeHandler);
            }
            window.ledgerResizeHandler = handleLedgerResize;
            window.addEventListener('resize', window.ledgerResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.ledger').on('orientationchange.ledger', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleLedgerResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleLedgerResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const queries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            queries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleLedgerResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleLedgerResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleLedgerResize();
                }, 500);
            });

            window.handleLedgerResize = handleLedgerResize;

        });
    </script>
@endpush
