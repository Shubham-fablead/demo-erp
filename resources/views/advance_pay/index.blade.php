@extends('layout.app')

@section('title', 'Advance Payment Details')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
        }

        .table-scroll-top {
            display: none;
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
        }

        .table-scroll-top div {
            height: 1px;
        }

        button#exportBtn {
            padding: 3px;
        }

        .grand-total-box {
            display: inline-block;
            padding: 6px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #f8f9fa;
            font-size: 16px;
            font-weight: bold;
        }

        .grand-total-box strong {
            color: #1b2850;
            margin-right: 4px;
        }

        .grand-total-box span {
            color: orange;
        }

        .form-select-sm {
            min-width: 130px;
            margin-right: 10px;
        }

        .datanew td:nth-child(1),
        .datanew th:nth-child(1) {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            max-width: 150px;
            /* adjust if needed */
        }

        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            /* Filter Section Mobile Styles */
            /* .table-top {
                                            padding: 10px 0;
                                        }

                                        .table-top .row {
                                            flex-direction: column !important;
                                            gap: 10px !important;
                                        }

                                        .table-top .col-6,
                                        .table-top .col-sm-4,
                                        .table-top .col-md-3,
                                        .table-top .col-sm-12 {
                                            width: 100% !important;
                                            margin-bottom: 10px;
                                        } */

            #exportBtn {
                width: 100% !important;
                padding: 10px;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-scroll-top {
                display: block;
            }

            .datanew {
                font-size: 11px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            /* Show only Staff Name and Details */
            .datanew thead th:nth-child(2),
            .datanew tbody td:nth-child(2),
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3),
            .datanew thead th:nth-child(4),
            .datanew tbody td:nth-child(4),
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew tbody td:nth-child(6),
            .datanew thead th:nth-child(7),
            .datanew tbody td:nth-child(7),
            .datanew thead th:nth-child(8),
            .datanew tbody td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .advance-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }

            div#DataTables_Table_0_filter {
                margin-top: 10px !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: .1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {

            /* .table-top .row {
                                            flex-wrap: wrap;
                                            gap: 10px;
                                        }

                                        .table-top .col-sm-4 {
                                            flex: 0 0 calc(50% - 5px);
                                        }
                                         */
            #exportBtn {
                width: 100%;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .table-scroll-top {
                display: block;
            }

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Show Staff Name, Details, Amount, Paid Amount */
            .datanew thead th:nth-child(4),
            .datanew tbody td:nth-child(4),
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew tbody td:nth-child(6),
            .datanew thead th:nth-child(7),
            .datanew tbody td:nth-child(7),
            .datanew thead th:nth-child(8),
            .datanew tbody td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .advance-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .table-scroll-top {
                display: block;
            }

            .datanew {
                font-size: 13px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets */
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                display: none;
            }

            /* Hide expandable rows on tablets */
            .advance-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            .table-responsive {
                display: block !important;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop */
            .datanew thead th:nth-child(9),
            .datanew tbody td:nth-child(9) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .advance-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .advance-details-row {
            display: none;
        }

        .advance-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .advance-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .advance-details-list {
            margin-bottom: 15px;
        }

        .advance-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .advance-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .advance-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .advance-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .advance-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile-advance,
        button.btn-icon-mobile-advance {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1b2850;
            background: transparent;
            transition: all 0.3s;
            border: 2px solid #1b2850;
            cursor: pointer;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        button.btn-icon-mobile-advance {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile-advance:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile-advance img {
            width: 18px;
            height: 18px;
        }

        /* Toggle button styles */
        .advance-toggle-btn-table {
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

        .advance-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .advance-toggle-btn-table.minus {
            background: #dc3545;
        }

        .advance-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        /* ===== TOTAL FOOTER RESPONSIVE ===== */

        .totals-wrapper {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        /* Desktop */
        @media (min-width: 768px) {
            .totals-wrapper {
                flex-direction: row;
            }
        }

        /* Mobile */
        @media (max-width: 767.98px) {

            .totals-wrapper {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
            }

            .grand-total-box {
                width: 100%;
                text-align: center;
                font-size: 14px;
            }
        }

        .total-responsive {
            margin-bottom: 10px !important;
        }

        /* ✅ Pagination & Search Styling */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 4px;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }

        /* Search input styling */
        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 5px !important;
        }
    </style>
    <div class="content">
        <div class="page-header total-responsive">
            <div class="page-title">
                <h4>All Advance Payment Details</h4>
            </div>

            <div class="page-btn">
                @if (app('hasPermission')(23, 'add'))
                    <a href="{{ route('advance_pay.create') }}" class="btn btn-added btn-sm">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"> New Advance
                        Payment
                    </a>
                @endif
            </div>
        </div>
        <tfoot>
            <tr>
                <td colspan="9" class="text-end">

                    <div class="totals-wrapper">

                        <div class="grand-total-box">
                            <strong>Total Paid:</strong>
                            <span id="grandTotalPaid">₹0.00</span>
                        </div>

                        <div class="grand-total-box">
                            <strong>Total Pending:</strong>
                            <span id="grandTotalPending">₹0.00</span>
                        </div>

                        <div class="grand-total-box">
                            <strong>Total Payments:</strong>
                            <span id="grandTotalAmount">₹0.00</span>
                        </div>

                    </div>

                </td>
            </tr>
        </tfoot>

        <div class="card">
            <div class="card-body">



                <div class=" mb-3">
                    <div class="row g-3 align-items-end">
  <div class="col-6 col-sm-4 col-md-3">
                        {{-- <div class="mb-2"> --}}
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search..." style="height:32px">
                        </div>
                    {{-- </div> --}}
                </div>
                </div>

                        <!-- Staff Name -->
                        <div class="col-6 col-sm-4 col-md-3">
                            <label for="filterStaffName" class="form-label mb-1">Staff Name</label>
                            <select id="filterStaffName" class="form-select form-select-sm">
                                <option value="">Select Staff</option>
                                <!-- Dynamic staff options here -->
                            </select>
                        </div>

                        <!-- Year -->
                        <div class="col-6 col-sm-2 col-md-2">
                            <label for="filterYear" class="form-label mb-1">Year</label>
                            <select id="filterYear" class="form-select form-select-sm">
                                <option value="">Select Year</option>
                                <!-- Dynamic year options -->
                            </select>
                        </div>

                        <!-- Month -->
                        <div class="col-6 col-sm-2 col-md-2">
                            <label for="filterMonth" class="form-label mb-1">Month</label>
                            <select id="filterMonth" class="form-select form-select-sm">
                                <option value="">Select Month</option>
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                        {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="col-12 col-sm-2 col-md-2 text-start text-md-end">
                             <button id="exportBtn" class="btn btn-success btn-sm w-100 w-md-auto">
                                <i class="fa fa-download me-1"></i> Export
                            </button>
                        </div>

                        <!-- Export Button -->
                        {{-- <div class="col-6 col-sm-2 col-md-2 text-start text-md-end">
                            <button id="exportBtn" class="btn btn-success btn-sm w-100 w-md-auto">
                                <i class="fa fa-download me-1"></i> Export
                            </button>
                        </div> --}}

                    </div>
                </div>




                {{-- <div class="table-scroll-top">
                    <div></div>
                </div> --}}
                <div class="table-container">
                    <table class="table datanew" id="advance-payments-table">
                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Amount</th>
                                <th>Paid Amount</th>
                                <th>Pending Amount</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Reason</th>
                                <th>Action</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>

                        <tbody id="paymentTableBody">
                            <!-- Data will be loaded dynamically -->
                        </tbody>

                    </table>
                </div>

                <!-- ✅ Pagination Controls -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers">
                            <!-- Page numbers will be populated by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        // Global variables
        var advancePaymentDataMap = {};

        // Helper function to build expandable row content
        function buildAdvancePaymentExpandableRowContent(payment) {
            const staffName = payment.staff_name ? payment.staff_name.replace(/\b\w/g, c => c.toUpperCase()) : 'N/A';
            const method = payment.method ? payment.method.replace(/\b\w/g, c => c.toUpperCase()) : 'N/A';
            const reason = payment.reason ? payment.reason.replace(/\b\w/g, c => c.toUpperCase()) : '-';

            // Build action buttons HTML
            let actionButtons = '';
            @if (app('hasPermission')(23, 'view'))
                actionButtons += `
                <a href="/advance_pay/${payment.id}" class="btn-icon-mobile-advance" title="View">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/eye.svg' }}" alt="View">
                </a>
            `;
            @endif
            @if (app('hasPermission')(23, 'edit'))
                actionButtons += `
                <a href="/advance_pay/${payment.id}/edit" class="btn-icon-mobile-advance" title="Edit">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                </a>
            `;
            @endif
            @if (app('hasPermission')(23, 'delete'))
                actionButtons += `
                <button type="button" class="btn-icon-mobile-advance delete-btn" data-id="${payment.id}" title="Delete">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                </button>
            `;
            @endif

            return `
                <td colspan="9" class="advance-details-content">
                    <div class="advance-details-list">
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Amount:</span>
                            <span class="advance-detail-value-simple">${formatCurrencyIN(payment.amount)}</span>
                        </div>
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Paid Amount:</span>
                            <span class="advance-detail-value-simple" style="font-weight: bold; color: #28a745;">${formatCurrencyIN(payment.paid_amount)}</span>
                        </div>
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Pending Amount:</span>
                            <span class="advance-detail-value-simple" style="font-weight: bold; color: #dc3545;">${formatCurrencyIN(payment.pending)}</span>
                        </div>
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Date:</span>
                            <span class="advance-detail-value-simple">${payment.date || '-'}</span>
                        </div>
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Method:</span>
                            <span class="advance-detail-value-simple">${method}</span>
                        </div>
                        <div class="advance-detail-row-simple">
                            <span class="advance-detail-label-simple">Reason:</span>
                            <span class="advance-detail-value-simple" style="text-align: left; max-width: 60%;">${reason}</span>
                        </div>
                    </div>
                    ${actionButtons ? `<div class="advance-action-buttons-simple">${actionButtons}</div>` : ''}
                </td>
            `;
        }

        // Toggle function for advance payment rows
        window.toggleAdvancePaymentRowDetails = function(paymentId) {
            const btn = $(`.advance-toggle-btn-table[data-payment-id="${paymentId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.advance-details-row[data-payment-id="${paymentId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const paymentData = advancePaymentDataMap[paymentId];
                if (paymentData) {
                    detailsRow = $('<tr>')
                        .addClass('advance-details-row')
                        .attr('data-payment-id', paymentId)
                        .html(buildAdvancePaymentExpandableRowContent(paymentData));
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

        // ✅ Indian Currency Formatter
        function formatCurrencyIN(value) {
            let number = parseFloat(value) || 0;

            return '₹' + number.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }


        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            // Pagination & Search state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let params = {};
            if (selectedSubAdminId) {
                params.selectedSubAdminId = selectedSubAdminId;
            }

            // Fetch filters on page load
            fetchFilters();

            // Fetch table data on page load
            fetchAdvancePayments();

            // Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchAdvancePayments(1);
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchAdvancePayments(1);
            });

            // Fetch filters via AJAX
            function fetchFilters() {
                $.ajax({
                    url: "/api/advance-payment-filters",
                    type: "GET",
                    data: params,
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.staff) {
                            let staffOptions = '<option value="">Select Staff</option>';
                            $.each(response.staff, function(i, staff) {
                                staffOptions +=
                                    `<option value="${staff.id}">${staff.name}</option>`;
                            });
                            $("#filterStaffName").html(staffOptions);
                        }

                        if (response.years) {
                            let yearOptions = '<option value="">Select Year</option>';
                            $.each(response.years, function(i, year) {
                                yearOptions +=
                                    `<option value="${year}">${year}</option>`;
                            });
                            $("#filterYear").html(yearOptions);
                        }
                    }
                });
            }

            // Fetch advance payments
            function fetchAdvancePayments(page = 1) {
                currentPage = page;
                let ajaxData = {
                    ...params,
                    page: currentPage,
                    per_page: perPage,
                    search: searchQuery,
                    staff_id: $("#filterStaffName").val(),
                    year: $("#filterYear").val(),
                    month: $("#filterMonth").val()
                };

                $.ajax({
                    url: "/api/advance-payments",
                    type: "GET",
                    dataType: "json",
                    data: ajaxData,
                    beforeSend: function() {
                        $(".datanew tbody").html(
                            '<tr><td colspan="9" class="text-center">Loading...</td></tr>'
                        );
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status && Array.isArray(response.data)) {
                            let payments = response.data;
                            let tableBody = "";

                            // Clear previous data map
                            advancePaymentDataMap = {};

                            $.each(payments, function(index, payment) {
                                let amount = parseFloat(payment.amount) || 0;
                                let paid = parseFloat(payment.paid_amount) || 0;
                                let pending = parseFloat(payment.pending) || 0;

                                // Store payment data for expandable row
                                advancePaymentDataMap[payment.id] = payment;

                                // Capitalize each word
                                let staffName = payment.staff_name ?
                                    payment.staff_name.replace(/\b\w/g, c => c
                                        .toUpperCase()) :
                                    'N/A';

                                let method = payment.method ?
                                    payment.method.replace(/\b\w/g, c => c
                                        .toUpperCase()) :
                                    'N/A';

                                let reason = payment.reason ?
                                    payment.reason.replace(/\b\w/g, c => c
                                        .toUpperCase()) :
                                    '-';

                                // Toggle button for Details column
                                const detailsToggle = `
                                    <button class="advance-toggle-btn-table" onclick="toggleAdvancePaymentRowDetails('${payment.id}')" data-payment-id="${payment.id}">
                                        <span class="toggle-icon">+</span>
                                    </button>
                                `;

                                tableBody += `
                                    <tr>
                                        <td>${staffName}</td>
                                        <td>${formatCurrencyIN(amount)}</td>
                                        <td>${formatCurrencyIN(paid)}</td>
                                        <td>${formatCurrencyIN(pending)}</td>
                                        <td>${payment.date}</td>
                                        <td>${method}</td>
                                        <td style="white-space: normal; word-wrap: break-word; word-break: break-word;">
                                            ${reason}
                                        </td>
                                        <td>
                                            @if (app('hasPermission')(23, 'view'))
                                            <a href="/advance_pay/${payment.id}" class="me-2">
                                               <img src="{{ env('ImagePath') . 'admin/assets/img/icons/eye.svg' }}" alt="img">
                                            </a>
                                            @endif
                                            @if (app('hasPermission')(23, 'edit'))
                                            <a href="/advance_pay/${payment.id}/edit" class="me-2">
                                                 <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="img">
                                            </a>
                                            @endif
                                            @if (app('hasPermission')(23, 'delete'))
                                            <a href="javascript:void(0);" class="delete-btn" data-id="${payment.id}">
                                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="img">
                                            </a>
                                            @endif
                                        </td>
                                        <td>${detailsToggle}</td>
                                    </tr>
                                `;
                            });

                            // Destroy DataTable before injecting new HTML
                            if ($.fn.DataTable.isDataTable(".datanew")) {
                                $(".datanew").DataTable().clear().destroy();
                            }

                            $(".datanew tbody").html(tableBody);

                            // Update Totals from API
                            $("#grandTotalAmount").text(formatCurrencyIN(response.grandTotal || 0));
                            $("#grandTotalPaid").text(formatCurrencyIN(response.grandTotalPaid || 0));
                            $("#grandTotalPending").text(formatCurrencyIN(response.grandTotalPending || 0));

                            // Update Pagination Info
                            renderPagination(response.pagination);

                            // Reinitialize DataTable after injecting rows
                            $(".datanew").DataTable({
                                responsive: true,
                                paging: false, // Disable built-in paging
                                ordering: false,
                                info: false,
                                searching: false,
                                language: {
                                    emptyTable: "No advance payments found.",
                                    zeroRecords: "No matching records.",
                                },
                            });

                        } else {
                            $(".datanew tbody").html(
                                '<tr><td colspan="9" class="text-center">No advance payments found.</td></tr>'
                            );
                            $("#grandTotalAmount").text("₹0.00");
                            $("#grandTotalPaid").text("₹0.00");
                            $("#grandTotalPending").text("₹0.00");
                            $("#pagination-from").text(0);
                            $("#pagination-to").text(0);
                            $("#pagination-total").text(0);
                            $("#pagination-numbers").empty();
                        }
                    },
                    error: function() {
                        $(".datanew tbody").html(
                            '<tr><td colspan="9" class="text-center text-danger">Error loading data.</td></tr>'
                        );
                        $("#grandTotalAmount").text("₹0.00");
                        $("#grandTotalPaid").text("₹0.00");
                        $("#grandTotalPending").text("₹0.00");
                    }
                });
            }

            // function renderPagination(pagination) {
            //     lastPage = pagination.last_page;
            //     currentPage = pagination.current_page;
            //     let total = pagination.total;
            //     let perPage = pagination.per_page;

            //     let from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
            //     let to = Math.min(currentPage * perPage, total);

            //     $("#pagination-from").text(from);
            //     $("#pagination-to").text(to);
            //     $("#pagination-total").text(total);

            //     let paginationHtml = "";

            //     // Previous button
            //     paginationHtml += `
            //         <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            //             <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentPage - 1})">Previous</a>
            //         </li>
            //     `;

            //     // Page numbers
            //     let startPage = Math.max(1, currentPage - 2);
            //     let endPage = Math.min(lastPage, startPage + 4);
            //     if (endPage - startPage < 4) {
            //         startPage = Math.max(1, endPage - 4);
            //     }

            //     for (let i = startPage; i <= endPage; i++) {
            //         paginationHtml += `
            //             <li class="page-item ${i === currentPage ? 'active' : ''}">
            //                 <a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>
            //             </li>
            //         `;
            //     }

            //     // Next button
            //     paginationHtml += `
            //         <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
            //             <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentPage + 1})">Next</a>
            //         </li>
            //     `;

            //     $("#pagination-numbers").html(paginationHtml);
            // }
            function renderPagination(pagination) {
    lastPage = pagination.last_page;
    currentPage = pagination.current_page;
    let total = pagination.total;
    let perPage = pagination.per_page;

    let from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
    let to = Math.min(currentPage * perPage, total);

    $("#pagination-from").text(from);
    $("#pagination-to").text(to);
    $("#pagination-total").text(total);

    let paginationHtml = "";

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(lastPage, startPage + 4);

    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }

    $("#pagination-numbers").html(paginationHtml);
}

            // Define changePage globally so it can be called from onclick
            window.changePage = function(page) {
                if (page < 1 || page > lastPage) return;
                fetchAdvancePayments(page);
            };

            // When any filter changes, reload data
            $("#filterStaffName, #filterYear, #filterMonth").on("change", function() {
                fetchAdvancePayments(1);
            });


            // Handle Delete
            $(document).on("click", ".delete-btn", function() {
                let paymentId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    confirmButtonColor: "#ff9f43", // Confirm button color (orange)
                    cancelButtonColor: "#6c757d", // Cancel button color (gray)
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/advance-payments/${paymentId}`,
                            type: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function(response) {
                                // Swal.fire("Deleted!", response.message, "success");
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43", // Custom OK button color
                                    confirmButtonText: "OK"
                                });
                                // fetchAdvancePayments(); // Refresh the table after deletion
                                window.location.reload();
                            },
                            error: function(xhr) {
                                let errorMessage = "Failed to delete customer.";
                                if (xhr.responseJSON && xhr.responseJSON.error) {
                                    errorMessage = xhr.responseJSON.error;
                                }
                                Swal.fire({
                                    title: "Error!",
                                    text: errorMessage,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43", // custom orange color
                                    confirmButtonText: "OK"
                                });
                            },
                        });
                    }
                });
            });

            document.getElementById('exportBtn').addEventListener('click', function() {
                let staffId = document.getElementById('filterStaffName')?.value || '';
                let year = document.getElementById('filterYear')?.value || '';
                let month = document.getElementById('filterMonth')?.value || '';

                let params = {};

                if (staffId) params.staff_id = staffId;
                if (year) params.year = year;
                if (month) params.month = month;
                if (typeof selectedSubAdminId !== 'undefined' && selectedSubAdminId) {
                    params.selectedSubAdminId = selectedSubAdminId;
                }

                let query = new URLSearchParams(params).toString();
                if (query.length > 0) query = '?' + query;

                let token = localStorage.getItem("authToken");

                fetch('/api/export-advance-payments' + query, {
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + token
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.status) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Export Failed',
                                text: data.message
                            });
                            return;
                        }

                        // SUCCESS
                        Swal.fire({
                            icon: 'success',
                            title: 'Export Ready',
                            text: 'Download Excel file'
                        }).then(() => {
                            window.location.href = data.file_url; // download
                        });
                    })
                    .catch(err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Unexpected Error',
                            text: 'Something went wrong.'
                        });
                    });
            });

            // Resize handler for responsive behavior
            let advanceResizeTimer;
            let lastAdvanceWidth = $(window).width();

            function forceAdvanceCSSRecalculation() {
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

            function handleAdvanceResize() {
                clearTimeout(advanceResizeTimer);
                advanceResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastAdvanceWidth = currentWidth;

                    // Force CSS recalculation
                    forceAdvanceCSSRecalculation();

                    const advanceTable = document.getElementById('advance-payments-table');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [advanceTable, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    forceAdvanceCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.advance').on('resize.advance', handleAdvanceResize);

            if (window.advanceResizeHandler) {
                window.removeEventListener('resize', window.advanceResizeHandler);
            }
            window.advanceResizeHandler = handleAdvanceResize;
            window.addEventListener('resize', window.advanceResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.advance').on('orientationchange.advance', function() {
                setTimeout(function() {
                    lastAdvanceWidth = $(window).width();
                    handleAdvanceResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastAdvanceWidth = $(window).width();
                    handleAdvanceResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const advanceQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            advanceQueries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleAdvanceResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleAdvanceResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastAdvanceWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastAdvanceWidth = $(window).width();
                    handleAdvanceResize();
                }, 500);
            });

            window.handleAdvanceResize = handleAdvanceResize;

        });
    </script>
@endpush
