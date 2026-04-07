@extends('layout.app')

@section('title', 'Expense Report')

@section('content')
    <style>
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            /* Dark gray for other pages */
            color: #fff;
            border: none;
            margin: 0 4px;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            /* Orange for active page */
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

        .search-set {
            margin-bottom: 5px;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 4px !important;
        }

        .dataTables_info {
            float: left;
            padding-right: 15px;
            font-size: 12px;
            color: #5e5873;
            font-weight: 600;
        }

        .table-scroll-top {
            display: none;
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
        }

        .table-scroll-top div {
            height: 1px;
        }

        #expenseChart {
            width: 100% !important;
            height: 100% !important;
            max-height: 320px;
        }

        .total_expense {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
        }

        /* Force hide toggle-details by default */
        .toggle-details {
            display: none !important;
        }

        /* Word wrapping for Expense Name and Expense For columns */
        .datanew td:nth-child(2),
        .datanew td:nth-child(5) {
            white-space: normal !important;
            word-wrap: break-word;
            /* word-break: break-word; */
            max-width: 100px;
            /* adjust as needed */
        }

        .datanew th:nth-child(2),
        .datanew th:nth-child(5) {
            white-space: normal;
            word-wrap: break-word;
        }

        /* Desktop: show all columns normally, HIDE details column */
        @media (min-width: 769px) {

            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew thead th.details-column,
            table.datanew tbody td.details-column {
                display: none !important;
            }

            /* Ensure checkbox column is visible */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: table-cell !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {

            /* Override DataTables responsive classes */
            table.datanew th.dt-control,
            table.datanew td.dt-control {
                display: none !important;
            }

            /* Hide columns 3, 4, 5 on mobile (Amount, Date, Expense For) */
            table.datanew thead th:nth-child(3),
            table.datanew tbody td:nth-child(3),
            table.datanew thead th:nth-child(4),
            table.datanew tbody td:nth-child(4),
            table.datanew thead th:nth-child(5),
            table.datanew tbody td:nth-child(5) {
                display: none !important;
            }

            /* Show checkbox column on mobile */
            table.datanew thead th:first-child,
            table.datanew tbody td:first-child {
                display: table-cell !important;
                width: 40px;
                min-width: 40px;
                max-width: 40px;
            }

            /* Show Expense Name column on mobile */
            table.datanew thead th:nth-child(2),
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                min-width: calc(80vw - 175px) !important;
                max-width: calc(80vw - 150px) !important;
                margin-top: 11px;

            }

            /* Show details column on mobile */
            table.datanew thead th.details-column,
            table.datanew tbody td.details-column {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
                /* min-width: 65px; */
                max-width: 65px;
            }

            /* Show toggle-details only on mobile */
            .toggle-details {
                display: inline-block !important;
            }

            .toggle-details i {
                font-size: 24px;
                padding-left: 20px;
            }

            /* Style for expense name wrapping */
            table.datanew tbody td:nth-child(2) {
                display: flex !important;
                align-items: center !important;
            }

            table.datanew tbody td:nth-child(2) a {
                align-items: center !important;
                text-align: left !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                line-height: 1.3 !important;
                text-decoration: none !important;
                color: #212529 !important;
                font-weight: 500;
                width: 100%;
            }

            .expense-name {
                display: inline-block !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
                -webkit-hyphens: auto !important;
                -ms-hyphens: auto !important;
                width: 100%;
            }

            /* Limit to 2 lines with ellipsis */
            .expense-name.truncated {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            /* Make sure checkbox is visible and properly aligned */
            table.datanew tbody td:first-child .checkboxs {
                display: block;
                margin: 0 auto;
                text-align: center;
            }

            table.datanew tbody td:first-child {
                padding: 8px 5px !important;
            }
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }

            .search-set {
                margin-right: 1rem !important;
            }

            /* Adjust filter layout for mobile */
            .table-top .search-set {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start !important;
                width: 100%;
            }

            .search-set .form-select-sm {
                flex: 1;
                min-width: 120px;
            }

            .wordset {
                width: 100%;
                margin-top: 10px !important;
            }

            .wordset ul {
                width: 100%;
            }

            .wordset ul li {
                width: 100%;
            }

            .wordset ul li a button {
                width: 100%;
            }

            #expenseType-filter {
                min-width: 150px !important;
            }
        }

        /* Tablet specific fixes */
        @media screen and (width: 768px) {
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td.details-column {
                display: table-cell !important;
                width: 60px !important;
                min-width: 60px !important;
                max-width: 60px !important;
            }

            .toggle-details {
                display: inline-block !important;
                padding: 8px !important;
                z-index: 10 !important;
            }

            .toggle-details i {
                font-size: 20px !important;
                width: 24px !important;
                height: 24px !important;
                line-height: 24px !important;
            }
        }

        /* Fade out animation for error messages */
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
        }

        /* Collapsible details styling */
        .collapse-details {
            margin-top: 10px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #ff9f43;
        }

        .detail-item {
            display: flex;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: #495057;
        }

        .detail-value {
            color: #212529;
            flex: 1;
        }

        @media screen and (max-width: 575.98px) {
            .page-header {
                display: flex;
                justify-content: space-between;
                /* 🔥 main fix */
                align-items: center;
                flex-direction: row;
                flex-wrap: nowrap;
                gap: 10px;
            }

            .page-title h4 {
                margin: 0;
                /* font-size: 18px; */
                white-space: nowrap;
            }

            .total-expense-summary {
                width: auto;
                /* ❌ remove 100% */
                margin-top: 0;
                /* ❌ remove spacing */
            }

            .total-expense-summary h5 {
                margin: 0 !important;
                width: auto;
                /* ❌ remove 70% */
                text-align: right;
                /* align amount right */
                white-space: nowrap;
            }

            /* Chart body - adjust height */
            .card-body[style*="height: 360px"] {
                height: 280px !important;
            }

            #productChart {
                max-height: 250px !important;
            }

            /* Filters section - stack vertically */
            .table-top .search-set {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start !important;
                width: 100%;
            }

            .search-set .form-select-sm {
                flex: 1;
                min-width: 120px;
            }

            /* Action buttons - stack */
            .wordset {
                width: 100%;
            }

            .wordset ul {
                flex-direction: column !important;
                gap: 10px !important;
                width: 100%;
            }

            .wordset ul li {
                width: 100%;
                margin: 0 !important;
            }

            .wordset ul li .btn,
            .wordset ul li .dropdown {
                width: 100%;
            }

            .wordset ul li .btn {
                width: 100% !important;
            }

            .wordset ul li a {
                width: 100%;
                display: flex;
                justify-content: center;
                padding: 10px;
            }

            /* GST Reports dropdown - mobile responsive */
            .wordset ul li .dropdown {
                width: 100%;
            }

            .wordset ul li .dropdown .btn {
                width: 100% !important;
                text-align: center;
            }

            .wordset ul li .dropdown-menu {
                width: 100% !important;
                max-width: 100% !important;
            }

            .wordset ul li .dropdown-menu li {
                width: 100%;
            }

            .wordset ul li .dropdown-menu .dropdown-item {
                padding: 12px 15px;
                text-align: center;
                font-size: 14px;
            }

            /* Ensure dropdown opens on mobile */
            @media screen and (max-width: 767.98px) {
                .wordset ul li .dropdown-menu {
                    left: 0 !important;
                    right: 0 !important;
                    transform: translate3d(0, 0, 0) !important;
                    position: absolute !important;
                    margin-top: 5px;
                }
            }

            /* Table responsive styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .datanew1 {
                font-size: 11px;
            }

            .datanew1 th,
            .datanew1 td {
                padding: 6px 3px;
            }

            /* Show only Checkbox, Product Name and Details */
            .datanew1 thead th:nth-child(3),
            .datanew1 tbody td:nth-child(3),
            .datanew1 thead th:nth-child(4),
            .datanew1 tbody td:nth-child(4),
            .datanew1 thead th:nth-child(5),
            .datanew1 tbody td:nth-child(5) {
                display: none;
            }

            /* Center Details column */
            .datanew1 thead th:nth-child(6),
            .datanew1 tbody td:nth-child(6) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .purchase-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Mobile view for total expense */
        @media (max-width: 768px) {
            .page-header {
                flex-wrap: wrap;
            }


            #total-expense-amount {
                display: inline-block;
                margin-left: 0 !important;
            }
        }

        /* Expense specific styling */
        .expense-amount-badge {
            display: inline-block;
            padding: 4px 10px;
            background-color: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            font-weight: 600;
            font-size: 13px;
            margin-left: 8px;
        }
    </style>

    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>
    @endif

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Expense Report</h4>
            </div>
            <div class="total-expense-summary">
                <h5 id="total-expense-amount" class="mb-0 px-3 py-1 mx-3 rounded bg-light total_expense">
                    <!-- Gets updated by JS -->Total:
                </h5>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <h6 class="fw-bold mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-chart-column me-2 text-primary"></i>
                    Expense Summary
                </h6>

                <div id="expense-filter-summary"
                    style="
                        display:none;
                        background:#f9fafb;
                        border:1px solid #e5e7eb;
                        border-radius:6px;
                        padding:6px 10px;
                        font-size:14px;
                        font-weight:500;
                        color:#374151;
                        align-items:center;
                        justify-content:center;
                        gap:6px;
                        white-space:nowrap;
                        transition:all 0.3s ease;
                    ">
                </div>
            </div>

            <div class="card-body d-flex align-items-center justify-content-center" style="height: 350px;">
                <canvas id="expenseChart" style="max-height: 320px; width: 100%;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-top">
                    <div class="search-set">
                        <select id="date-filter" class="form-select form-select-sm">
                            <option value="">All Time</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_6_months">Last 6 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="previous_year">Previous Year</option>
                        </select>
                        <select id="month-filter" class="form-select form-select-sm"
                            style="margin-right:8px; margin-left: 8px;">
                            <option value="">All Months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select id="year-filter" class="form-select form-select-sm" style="margin-right:8px;">
                            <option value="">All Years</option>
                            @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>

                        <select id="expenseType-filter" class="form-select form-select-sm"
                            style="margin-right:8px; min-width: 180px;">
                            <option value="">All Expense Type</option>
                            @isset($expenseTypes)
                                @foreach ($expenseTypes as $expenseType)
                                    <option value="{{ $expenseType->id }}">{{ $expenseType->type }}</option>
                                @endforeach
                            @endisset
                        </select>
                    </div>
                    <div class="wordset">
                        <ul>
                            <li>
                                <a id="generate-pdf" href="javascript:void(0);" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Download PDF">
                                    <button class="btn btn-primary btn-sm">View PDF</button>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                {{-- <div class="table-scroll-top">
                    <div></div>
                </div> --}}
                <div class="search-set">
                    <!-- Your existing filters -->
                    <div class="search-input">
                        <a class="btn btn-searchset">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                        </a>
                        <input type="text" id="search-input" class="form-control" placeholder="Search..."
                            style="height: 30px;">
                    </div>
                </div>
                {{-- <div class="table-container"> --}}
                    <div class="table-responsive expense-table-wrapper">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Expense Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Expense For</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
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

{{-- @push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {



            function formatINR(amount, symbol = '₹', position = 'left') {
                let number = parseFloat(amount || 0);

                let formatted = number.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                return position === 'left' ?
                    `${symbol}${formatted}` :
                    `${formatted}${symbol}`;
            }

            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            var table = $('.datanew').DataTable({
                destroy: true,
                paging: true,
                searching: true,
                ordering: true,
                responsive: true,
                language: {
                    emptyTable: "No expenses found.",
                    zeroRecords: "No expense record found.",
                    search: "Search:",
                    searchPlaceholder: ""
                }
            });

            // Initial load
            fetchExpenses();
            loadExpenseChart();

            // Handle filter changes
            $('#date-filter, #month-filter, #year-filter, #expenseType-filter').on('change', function() {
                const filter = $('#date-filter').val();
                const month = $('#month-filter').val();
                const year = $('#year-filter').val();
                const expenseTypeId = $('#expenseType-filter').val();

                fetchExpenses(filter, month, year, expenseTypeId);
                loadExpenseChart(filter, month, year, expenseTypeId);
            });

            /** ------------------------------------------
             * 📋 Fetch Expense Table Data + Total
             * ------------------------------------------ */
            function fetchExpenses(filter = '', month = '', year = '', expenseTypeId = '') {
                $.ajax({
                    url: '/api/expenses/report',
                    method: 'GET',
                    data: {
                        filter: filter,
                        month: month,
                        year: year,
                        expense_type_id: expenseTypeId,
                        selectedSubAdminId: selectedSubAdminId,
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        table.clear().draw();
                        let totalAmount = 0;

                        // Get currency settings
                        const currencySymbol = response.currency_symbol || '₹';
                        const currencyPosition = response.currency_position || 'left';

                        // ✅ Build rows & sum total
                        $.each(response.data, function(index, expense) {
                            totalAmount += parseFloat(expense.amount || 0);

                            // Details toggle for mobile
                            let detailsToggle = `
                                <a href="#expense-details-${expense.id}" class="toggle-details" data-bs-toggle="collapse">
                                    <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                </a>
                            `;

                            // Format amount with currency
                            // const amountValue = parseFloat(expense.amount || 0).toFixed(2);
                            // const formattedAmount = currencyPosition === 'left' ?
                            //     `${currencySymbol}${amountValue}` :
                            //     `${amountValue}${currencySymbol}`;
                            const formattedAmount = formatINR(
                                expense.amount,
                                currencySymbol,
                                currencyPosition
                            );

                            table.row.add([
                                // Column 1: Checkbox
                                `<label class="checkboxs">
                                    <input type="checkbox" class="expense-check" name="item_ids[]" value="${expense.id}">
                                    <span class="checkmarks"></span>
                                </label>`,

                                // Column 2: Expense Name with collapsible details
                                `<div>
                                    <div style="display: flex; align-items: center;">
                                        <a href="{{ route('expense.list') }}" class="d-flex align-items-center" style="text-decoration: none; color: inherit;">
                                            <span class="expense-name truncated">${expense.expense_name ?? 'N/A'}</span>
                                        </a>
                                    </div>

                                    <!-- Collapsible Details (visible only on mobile) -->
                                    <div class="collapse mt-2 d-lg-none" id="expense-details-${expense.id}">
                                        <div class="collapse-details">
                                            <div class="detail-item">
                                                <span class="detail-label">Amount:</span>
                                                <span class="detail-value">${formattedAmount}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Date:</span>
                                                <span class="detail-value">${formatDate(expense.expense_date)}</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Expense For:</span>
                                                <span class="detail-value">${expense.description ?? 'N/A'}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>`,

                                // Column 3: Amount
                                `<span class="fw-semibold" style="color: #2e7d32;">${formattedAmount}</span>`,

                                // Column 4: Date
                                formatDate(expense.expense_date),

                                // Column 5: Description
                                `<div style="white-space: normal; word-break: break-word; max-width: 400px;">${expense.description ?? 'N/A'}</div>`,

                                // Column 6: Details Toggle (only for mobile)
                                detailsToggle
                            ]).draw(false);
                        });

                        // ✅ Update total expense
                        // const formattedTotal = totalAmount.toFixed(2);
                        // const totalText = currencyPosition === 'left' ?
                        //     `${currencySymbol}${formattedTotal}` : `${formattedTotal}${currencySymbol}`;
                        const totalText = formatINR(
                            totalAmount,
                            currencySymbol,
                            currencyPosition
                        );
                        // $('#total-expense-amount').text(`Total: ${totalText}`);
                        $('#total-expense-amount').html(
                            `Total: <span style="color:#ff9f43;">${totalText}</span>`
                        );

                        // ✅ Show filter summary
                        showExpenseFilterSummary(filter, month, year, expenseTypeId);

                        // ✅ Fix top scroll sync
                        const topScroll = document.querySelector('.table-scroll-top');
                        const tableResponsive = document.querySelector('.table-responsive');
                        const tableElement = document.querySelector('.datanew');

                        if (topScroll && tableResponsive && tableElement) {
                            const topInnerDiv = topScroll.querySelector('div');
                            topInnerDiv.style.width = tableElement.scrollWidth + 'px';

                            topScroll.onscroll = () => {
                                tableResponsive.scrollLeft = topScroll.scrollLeft;
                            };
                            tableResponsive.onscroll = () => {
                                topScroll.scrollLeft = tableResponsive.scrollLeft;
                            };
                        }
                    },
                    error: function() {
                        alert('Failed to fetch expenses.');
                    }
                });
            }

            // 🟢 Global array to track selected expense IDs across all pages
            let allSelectedIds = [];

            /** -------------------------------------------------------
             * ✅ Handle "Select All" checkbox across all DataTable pages
             * ------------------------------------------------------- */
            $(document).on('change', '#select-all', function() {
                const isChecked = $(this).is(':checked');

                // ✅ Check or uncheck all checkboxes across all pages
                table.$('input[name="item_ids[]"]').prop('checked', isChecked);

                if (isChecked) {
                    // Add all expense IDs from all pages
                    allSelectedIds = table.$('input[name="item_ids[]"]').map(function() {
                        return $(this).val();
                    }).get();
                } else {
                    // Clear all selections
                    allSelectedIds = [];
                }

                // console.log("✅ All Selected IDs (All Pages):", allSelectedIds);
            });

            /** -------------------------------------------------------
             * 🟢 Handle individual checkbox selection (per row)
             * ------------------------------------------------------- */
            $(document).on('change', 'input[name="item_ids[]"]', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    if (!allSelectedIds.includes(id)) allSelectedIds.push(id);
                } else {
                    allSelectedIds = allSelectedIds.filter(x => x !== id);
                }

                // ✅ Update Select-All checkbox state based on all pages
                const totalCheckboxes = table.$('input[name="item_ids[]"]').length;
                const checkedCheckboxes = table.$('input[name="item_ids[]"]:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);

                // console.log("🟢 Currently Selected IDs (All Pages):", allSelectedIds);
            });

            // Toggle details icon
            $(document).on('click', '.toggle-details', function() {
                let icon = $(this).find('i');
                if (icon.hasClass('fa-plus-circle')) {
                    icon.removeClass('fa-plus-circle')
                        .addClass('fa-minus-circle')
                        .css('color', 'red');
                } else {
                    icon.removeClass('fa-minus-circle')
                        .addClass('fa-plus-circle')
                        .css('color', '#ff9f43');
                }
            });

            function showExpenseFilterSummary(filter, month, year, expenseTypeId) {
                let message = "All Expenses";

                if (filter) {
                    const filterLabels = {
                        'this_week': 'This Week',
                        'this_month': 'This Month',
                        'last_6_months': 'Last 6 Months',
                        'this_year': 'This Year',
                        'previous_year': 'Previous Year'
                    };
                    message = `Expenses - ${filterLabels[filter] || 'Custom'}`;
                }

                // ✅ Handle month/year logic cleanly
                if (month && year) {
                    const monthName = new Date(year, month - 1).toLocaleString('default', {
                        month: 'long'
                    });
                    message = `Expenses for ${monthName} ${year}`;
                } else if (month && !year) {
                    const monthName = new Date(2025, month - 1).toLocaleString('default', {
                        month: 'long'
                    }); // fallback year
                    message = `Expenses for ${monthName}`;
                } else if (year && !month) {
                    message = `Expenses for ${year}`;
                }

                if (expenseTypeId) {
                    const selectedText = $('#expenseType-filter option:selected').text();
                    message += ` — ${selectedText}`;
                }

                const $summary = $('#expense-filter-summary');
                $summary
                    .css('display', 'flex') // ✅ ensures flexbox layout
                    .hide()
                    .text(message)
                    .fadeIn(200);
            }

            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };
                return date.toLocaleDateString('en-GB', options);
            }

            /** --------------------------------------
             * 📊 Expense Chart Function
             * -------------------------------------- */
            let expenseChart;

            function loadExpenseChart(filter = '', month = '', year = '', expenseTypeId = '') {
                $.ajax({
                    url: '/api/expenses/report',
                    method: 'GET',
                    data: {
                        filter,
                        month,
                        year,
                        expense_type_id: expenseTypeId,
                        selectedSubAdminId,
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const chartContainer = $("#expenseChart").parent();
                        $("#noExpenseMessage").remove();

                        // Destroy previous chart if exists
                        if (expenseChart) {
                            expenseChart.destroy();
                            expenseChart = null;
                        }

                        if (!response.data || response.data.length === 0) {
                            // ✅ Show no-data message
                            chartContainer.append(`
                                <div id="noExpenseMessage"
                                    style="
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        height: 100%;
                                        width: 100%;
                                        color: #6b7280;
                                        font-size: 16px;
                                        text-align: center;
                                    ">
                                    No expense data found.
                                </div>
                            `);
                            return;
                        }

                        // ✅ Aggregate expenses by name
                        const totals = {};
                        response.data.forEach(item => {
                            const name = item.expense_name || 'Unknown';
                            const amount = parseFloat(item.amount) || 0;
                            totals[name] = (totals[name] || 0) + amount;
                        });

                        // ✅ Sort and take top 10
                        const sorted = Object.entries(totals)
                            .sort((a, b) => b[1] - a[1])
                            .slice(0, 15);

                        const labels = sorted.map(item => item[0]);
                        const values = sorted.map(item => item[1]);

                        const ctx = document.getElementById('expenseChart').getContext('2d');
                        expenseChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Total Expense',
                                    data: values,
                                    backgroundColor: [
                                        '#4e73df', '#1cc88a', '#36b9cc',
                                        '#f6c23e', '#e74a3b', '#858796',
                                        '#5a5c69', '#20c997', '#6610f2', '#fd7e14'
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barThickness: 20
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            // label: context => `₹${context.formattedValue}`
                                            label: context => formatINR(context.raw)
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            autoSkip: false,
                                            callback: function(value) {
                                                const label = this.getLabelForValue(value);
                                                return label.length > 15 ? label.substring(
                                                    0, 15) + "..." : label;
                                            }
                                        },
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: "#e5e7eb"
                                        }
                                    }
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error("Expense chart load failed", xhr);
                    }
                });
            }

            document.getElementById('generate-pdf').addEventListener('click', function() {
                if (allSelectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one expense to generate the PDF.',
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const url = `/expense/report/${allSelectedIds.join(',')}`;
                window.open(url, '_blank');
            });

            // Auto-hide error message after 4 seconds
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden');
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }
            }, 4000);
        });
    </script>
@endpush --}}
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(document).ready(function() {
            // Global variables
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let currentFilter = '';
            let currentMonth = '';
            let currentYear = '';
            let currentExpenseTypeId = '';
            let allSelectedIds = [];
            let expenseChart;

            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            // Initialize DataTable without pagination (we'll handle it manually)
            var table = $('.datanew').DataTable({
                destroy: true,
                paging: false,
                searching: false,
                ordering: true,
                responsive: true,
                info: false,
                language: {
                    emptyTable: "No expenses found.",
                    zeroRecords: "No expense record found."
                }
            });

            // Format currency function
            function formatINR(amount, symbol = '₹', position = 'left') {
                let number = parseFloat(amount || 0);
                let formatted = number.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return position === 'left' ? `${symbol}${formatted}` : `${formatted}${symbol}`;
            }

            // Format date function
            function formatDate(dateStr) {
                if (!dateStr) return 'N/A';
                const date = new Date(dateStr);
                const options = {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                };
                return date.toLocaleDateString('en-GB', options);
            }

            // Helper function to escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Initial load
            fetchExpenses();
            loadExpenseChart();

            // Handle filter changes
            $('#date-filter, #month-filter, #year-filter, #expenseType-filter').on('change', function() {
                currentFilter = $('#date-filter').val();
                currentMonth = $('#month-filter').val();
                currentYear = $('#year-filter').val();
                currentExpenseTypeId = $('#expenseType-filter').val();
                currentPage = 1;
                fetchExpenses();
                loadExpenseChart();
            });

            // Handle search input
            let searchDebounce;
            $('#search-input').on('keyup', function() {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    searchQuery = $(this).val();
                    currentPage = 1;
                    fetchExpenses();
                }, 500);
            });

            // Handle per page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                currentPage = 1;
                fetchExpenses();
            });

            // Handle page navigation
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    currentPage = page;
                    fetchExpenses();
                }
            });

            /** ------------------------------------------
             * 📋 Fetch Expense Table Data with Pagination
             * ------------------------------------------ */
            function fetchExpenses() {
                $.ajax({
                    url: '/api/expenses/report',
                    method: 'GET',
                    data: {
                        filter: currentFilter,
                        month: currentMonth,
                        year: currentYear,
                        expense_type_id: currentExpenseTypeId,
                        selectedSubAdminId: selectedSubAdminId,
                        page: currentPage,
                        per_page: perPage,
                        search: searchQuery
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        table.clear().draw();
                        let totalAmount = 0;

                        const currencySymbol = response.currency_symbol || '₹';
                        const currencyPosition = response.currency_position || 'left';
                        const pagination = response.pagination;

                        if (pagination) {
                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;
                            updatePaginationUI(pagination);
                        }

                        if (response.summary && response.summary.total_amount) {
                            totalAmount = response.summary.total_amount;
                        }

                        if (response.data && response.data.length > 0) {
                            $.each(response.data, function(index, expense) {
                                totalAmount += parseFloat(expense.amount || 0);

                                // Generate a unique ID for each expense
                                const uniqueId = 'expense-details-' + expense.id + '-' + Date.now() + '-' + index;

                                const formattedAmount = formatINR(expense.amount, currencySymbol, currencyPosition);

                                // ✅ Fixed: Proper toggle-details with unique ID and Bootstrap collapse
                                let detailsToggle = `
                                    <a href="javascript:void(0);" class="toggle-details" data-target="${uniqueId}" data-id="${expense.id}">
                                        <i class="fas fa-plus-circle" style="color: #ff9f43; font-size: 24px;"></i>
                                    </a>
                                `;

                                table.row.add([
                                    `<label class="checkboxs">
                                        <input type="checkbox" class="expense-check" name="item_ids[]" value="${expense.id}">
                                        <span class="checkmarks"></span>
                                    </label>`,

                                    `<div class="expense-item-wrapper" data-expense-id="${expense.id}">
                                        <div class="expense-name-wrapper" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                                            <span class="expense-name" style="flex: 1; word-break: break-word; padding-right: 25px;">
                                                ${escapeHtml(expense.expense_name ?? 'N/A')}
                                            </span>
                                        </div>
                                        <div class="expense-details-collapse collapse" id="${uniqueId}">
                                            <div class="collapse-details mt-2 p-3" style="background-color: #f8f9fa; border-radius: 8px; border-left: 3px solid #ff9f43;">
                                                <div class="detail-item mb-2">
                                                    <strong class="detail-label" style="min-width: 100px; display: inline-block;">Amount:</strong>
                                                    <span class="detail-value">${formattedAmount}</span>
                                                </div>
                                                <div class="detail-item mb-2">
                                                    <strong class="detail-label" style="min-width: 100px; display: inline-block;">Date:</strong>
                                                    <span class="detail-value">${formatDate(expense.expense_date)}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <strong class="detail-label" style="min-width: 100px; display: inline-block;">Expense For:</strong>
                                                    <span class="detail-value" style="word-break: break-word;">${escapeHtml(expense.description ?? 'N/A')}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`,

                                    `<span class="fw-semibold" style="color: #2e7d32;">${formattedAmount}</span>`,
                                    formatDate(expense.expense_date),
                                    `<div style="white-space: normal; word-break: break-word; max-width: 200px;">${escapeHtml(expense.description ?? 'N/A')}</div>`,
                                    detailsToggle
                                ]).draw(false);
                            });
                        }

                        const totalText = formatINR(totalAmount, currencySymbol, currencyPosition);
                        $('#total-expense-amount').html(`Total: <span style="color:#ff9f43;">${totalText}</span>`);

                        showExpenseFilterSummary(currentFilter, currentMonth, currentYear, currentExpenseTypeId);
                        $('#select-all').prop('checked', false);
                        allSelectedIds = [];
                        fixTopScrollSync();

                        // ✅ Initialize collapse functionality for all toggle buttons
                        initToggleDetails();
                    },
                    error: function(xhr) {
                        console.error('Error fetching expenses:', xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch expenses. Please try again.'
                        });
                    }
                });
            }

            // ✅ Function to initialize toggle-details functionality
            function initToggleDetails() {
                $('.toggle-details').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const targetId = $(this).data('target');
                    const $target = $(`#${targetId}`);
                    const $icon = $(this).find('i');

                    if ($target.hasClass('show')) {
                        $target.collapse('hide');
                        $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                    } else {
                        $target.collapse('show');
                        $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', '#dc3545');
                    }
                });

                // Handle collapse events to sync icon state
                $('.expense-details-collapse').off('show.bs.collapse hide.bs.collapse');
                $('.expense-details-collapse').on('show.bs.collapse', function() {
                    const targetId = $(this).attr('id');
                    const $toggle = $(`.toggle-details[data-target="${targetId}"]`);
                    const $icon = $toggle.find('i');
                    $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color', '#dc3545');
                }).on('hide.bs.collapse', function() {
                    const targetId = $(this).attr('id');
                    const $toggle = $(`.toggle-details[data-target="${targetId}"]`);
                    const $icon = $toggle.find('i');
                    $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color', '#ff9f43');
                });
            }

            // Update pagination UI
            function updatePaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;
                if (to > pagination.total) to = pagination.total;
                if (pagination.total === 0) from = 0;

                $('#pagination-from').text(from);
                $('#pagination-to').text(to);
                $('#pagination-total').text(pagination.total);

                let paginationHtml = '';
                let startPage = Math.max(1, pagination.current_page - 2);
                let endPage = Math.min(pagination.last_page, startPage + 4);

                if (endPage - startPage < 4 && startPage > 1) {
                    startPage = Math.max(1, endPage - 4);
                }

                if (pagination.current_page > 1) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page - 1}">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    `;
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (pagination.current_page < pagination.last_page) {
                    paginationHtml += `
                        <li class="page-item">
                            <a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page + 1}">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    `;
                }

                $('#pagination-numbers').html(paginationHtml);
                $('.pagination-controls').show();
            }

            // Fix top scroll sync
            function fixTopScrollSync() {
                setTimeout(() => {
                    const topScroll = document.querySelector('.table-scroll-top');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const tableElement = document.querySelector('.datanew');

                    if (topScroll && tableResponsive && tableElement) {
                        const topInnerDiv = topScroll.querySelector('div');
                        if (topInnerDiv) {
                            topInnerDiv.style.width = tableElement.scrollWidth + 'px';
                        }

                        topScroll.onscroll = () => {
                            tableResponsive.scrollLeft = topScroll.scrollLeft;
                        };
                        tableResponsive.onscroll = () => {
                            topScroll.scrollLeft = tableResponsive.scrollLeft;
                        };
                    }
                }, 100);
            }

            // Show filter summary
            function showExpenseFilterSummary(filter, month, year, expenseTypeId) {
                let message = "All Expenses";

                if (filter) {
                    const filterLabels = {
                        'this_week': 'This Week',
                        'this_month': 'This Month',
                        'last_6_months': 'Last 6 Months',
                        'this_year': 'This Year',
                        'previous_year': 'Previous Year'
                    };
                    message = `Expenses - ${filterLabels[filter] || 'Custom'}`;
                }

                if (month && year) {
                    const monthName = new Date(year, month - 1).toLocaleString('default', { month: 'long' });
                    message = `Expenses for ${monthName} ${year}`;
                } else if (month && !year) {
                    const monthName = new Date(2025, month - 1).toLocaleString('default', { month: 'long' });
                    message = `Expenses for ${monthName}`;
                } else if (year && !month) {
                    message = `Expenses for ${year}`;
                }

                if (expenseTypeId) {
                    const selectedText = $('#expenseType-filter option:selected').text();
                    message += ` — ${selectedText}`;
                }

                const $summary = $('#expense-filter-summary');
                if ($summary.length) {
                    $summary.css('display', 'flex').hide().text(message).fadeIn(200);
                }
            }

            // Handle Select All checkbox
            $(document).on('change', '#select-all', function() {
                const isChecked = $(this).is(':checked');
                table.$('input[name="item_ids[]"]').prop('checked', isChecked);

                if (isChecked) {
                    allSelectedIds = table.$('input[name="item_ids[]"]').map(function() {
                        return $(this).val();
                    }).get();
                } else {
                    allSelectedIds = [];
                }
            });

            // Handle individual checkbox selection
            $(document).on('change', 'input[name="item_ids[]"]', function() {
                const id = $(this).val();

                if ($(this).is(':checked')) {
                    if (!allSelectedIds.includes(id)) allSelectedIds.push(id);
                } else {
                    allSelectedIds = allSelectedIds.filter(x => x !== id);
                }

                const totalCheckboxes = table.$('input[name="item_ids[]"]').length;
                const checkedCheckboxes = table.$('input[name="item_ids[]"]:checked').length;
                $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            // Load Expense Chart
            function loadExpenseChart() {
                $.ajax({
                    url: '/api/expenses/report',
                    method: 'GET',
                    data: {
                        filter: currentFilter,
                        month: currentMonth,
                        year: currentYear,
                        expense_type_id: currentExpenseTypeId,
                        selectedSubAdminId: selectedSubAdminId,
                        per_page: 1000
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const chartContainer = $("#expenseChart").parent();
                        $("#noExpenseMessage").remove();

                        if (expenseChart) {
                            expenseChart.destroy();
                            expenseChart = null;
                        }

                        if (!response.data || response.data.length === 0) {
                            chartContainer.append(`
                                <div id="noExpenseMessage" style="display: flex; align-items: center; justify-content: center; height: 100%; width: 100%; color: #6b7280; font-size: 16px; text-align: center;">
                                    No expense data found.
                                </div>
                            `);
                            return;
                        }

                        const totals = {};
                        response.data.forEach(item => {
                            const name = item.expense_name || 'Unknown';
                            const amount = parseFloat(item.amount) || 0;
                            totals[name] = (totals[name] || 0) + amount;
                        });

                        const sorted = Object.entries(totals).sort((a, b) => b[1] - a[1]).slice(0, 15);
                        const labels = sorted.map(item => item[0]);
                        const values = sorted.map(item => item[1]);

                        const ctx = document.getElementById('expenseChart').getContext('2d');
                        expenseChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Total Expense',
                                    data: values,
                                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc',
                                        '#f6c23e', '#e74a3b', '#858796', '#5a5c69',
                                        '#20c997', '#6610f2', '#fd7e14'
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barThickness: 20
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: context => formatINR(context.raw)
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            autoSkip: false,
                                            callback: function(value) {
                                                const label = this.getLabelForValue(value);
                                                return label.length > 15 ? label.substring(0, 15) + "..." : label;
                                            }
                                        },
                                        grid: { display: false }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: "#e5e7eb" }
                                    }
                                }
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error("Expense chart load failed", xhr);
                    }
                });
            }

            // Generate PDF
            $('#generate-pdf').on('click', function() {
                if (allSelectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one expense to generate the PDF.',
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
                const url = `/expense/report/${allSelectedIds.join(',')}`;
                window.open(url, '_blank');
            });

            // Auto-hide error message
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden');
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 500);
                }
            }, 4000);

            // ✅ Handle window resize to fix table layout
            $(window).on('resize', function() {
                fixTopScrollSync();
                initToggleDetails();
            });
        });
    </script>
@endpush
