@extends('layout.app')

@section('title', 'All Invoices')

@section('content')
    <style>
        /* Hide DataTables default elements completely */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        /* Remove DataTables wrapper spacing */
        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Your existing styles remain the same */
        #DataTables_Table_0_info {
            float: left;
        }

        #order-table_wrapper .dataTables_length,
        #order-table_wrapper .dataTables_info,
        #order-table_wrapper .dataTables_paginate {
            display: none !important;
        }

        .table-scroll-top {
            display: none;
        }

        .form-select {
            color: #595b5d !important;
        }

        .form-control {
            color: #595b5d !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        .invoice-filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: 12px;
            width: 100%;
        }

        .invoice-filter-item {
            flex: 1 1 160px;
            min-width: 0;
        }

        .invoice-filter-item.search {
            flex-basis: 240px;
        }

        .invoice-filter-item.total {
            flex-basis: 170px;
        }

        .invoice-filter-item.date {
            flex-basis: 180px;
        }

        .invoice-filter-total {
            color: #1b2850;
            border: 2px solid #0d1b3e;
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 14px;
            font-weight: bold;
            min-height: 30px;
            display: flex;
            align-items: center;
            height: 30px;
        }

        .invoice-filter-actions {
            display: flex;
            gap: 8px;
            width: 100%;
        }

        .invoice-filter-actions .btn {
            flex: 1 1 0;
            /* min-width: 0; */
            white-space: nowrap;
        }

        /* Name column - word wrap */
        .datanew td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px;
            /* adjust as needed */
            min-width: 120px;
        }

        .datanew th:nth-child(4) {
            white-space: normal;
            word-wrap: break-word;
        }
        \  /* Custom Pagination Styling */
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


        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            /* Filter Section Mobile Styles */
            /* .table-top {
                                            padding: 10px 0;
                                        }

                                        .table-top > .row {
                                            flex-direction: column !important;
                                            gap: 10px !important;
                                        }

                                        .table-top .col-md-2,
                                        .table-top .col-12,
                                        .table-top .col-4,
                                        .table-top .col-6 {
                                            width: 100% !important;
                                            margin-bottom: 10px;
                                        }

                                        .search-set {
                                            width: 100% !important;
                                            margin: 0 !important;
                                        }

                                        .table-top .me-2 {
                                            margin-right: 0 !important;
                                            margin-bottom: 8px;
                                        }

                                        /* Total display on mobile */
            /* .table-top > .row > div[style*="border"] {
                                            width: 100% !important;
                                            text-align: center;
                                            padding: 8px !important;
                                            margin: 0 0 10px 0 !important;
                                        }  */

            /* Filter dropdowns - full width on mobile */
            .date-filter {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .date-filter select,
            .date-filter input {
                width: 100% !important;
                font-size: 14px;
            }

            .invoice-filter-bar {
                gap: 10px;
            }

            .invoice-filter-item,
            .invoice-filter-item.search,
            .invoice-filter-item.total,
            .invoice-filter-item.date,
            .invoice-filter-item.actions {
                flex: 1 1 calc(50% - 5px);
            }

            .invoice-filter-item.search {
                flex-basis: 100%;
            }

            .invoice-filter-actions {
                flex-wrap: wrap;
            }

            .invoice-filter-actions .btn {
                flex-basis: calc(50% - 4px);
            }

            /* Export buttons - full width on mobile */
            /* .table-top .d-flex.gap-2 {
                                            width: 100% !important;
                                            flex-direction: column;
                                            gap: 8px !important;
                                        }

                                        .table-top .d-flex.gap-2 button {
                                            width: 100% !important;
                                            padding: 10px;
                                            font-size: 14px;
                                        } */

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

            /* Show only Invoice Number and Details - hide Date, Name, Grand Total, Invoice Status, Payment Status, Action */
            .datanew th:nth-child(3),
            .datanew td:nth-child(3),
            .datanew th:nth-child(4),
            .datanew td:nth-child(4),
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8) {
                display: none;
            }

            /* Center Details column (2nd column) */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .invoice-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }

            /* Hide filter inputs card on mobile */
            #filter_inputs {
                display: none;
            }

            @media screen and (max-width:575.98px) {

                .datanew th:nth-child(1),
                .datanew td:nth-child(1) {
                    display: table-cell !important;
                }

                .datanew th:nth-child(2),
                .datanew td:nth-child(2) {
                    display: table-cell !important;
                    text-align: center;
                }

            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            /* Filter Section Small Mobile Styles */
            /* .table-top {
                                            padding: 12px 0;
                                        }

                                        .table-top > .row {
                                            flex-wrap: wrap !important;
                                            gap: 10px !important;
                                        }

                                        .table-top .col-md-2 {
                                            flex: 0 0 calc(50% - 5px);
                                            margin-bottom: 10px;
                                        } */

            .date-filter {
                width: 100% !important;
            }

            .date-filter select,
            .date-filter input {
                width: 100% !important;
                font-size: 14px;
            }

            .invoice-filter-item.search {
                flex-basis: 100%;
            }

            .invoice-filter-item.actions {
                flex-basis: 220px;
            }

            /* Export buttons side by side */
            /* .table-top .d-flex.gap-2 {
                                            width: 100% !important;
                                            flex-direction: row;
                                            gap: 10px !important;
                                        }

                                        .table-top .d-flex.gap-2 button {
                                            flex: 1;
                                            padding: 10px;
                                            font-size: 14px;
                                        } */

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

            /* Show Invoice Number, Details, Date, Name - hide Grand Total, Invoice Status, Payment Status, Action */
            .datanew th:nth-child(5),
            .datanew td:nth-child(5),
            .datanew th:nth-child(6),
            .datanew td:nth-child(6),
            .datanew th:nth-child(7),
            .datanew td:nth-child(7),
            .datanew th:nth-child(8),
            .datanew td:nth-child(8) {
                display: none;
            }

            /* Center Details column (2nd column) */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .invoice-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }

            /* Hide filter inputs card on small mobile */
            #filter_inputs {
                display: none;
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

            /* Hide Details column on tablets - same as desktop */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on tablets */
            .invoice-details-row {
                display: none !important;
            }

        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) and (max-width: 1199.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 10px 8px;
            }

            /* Hide Details column (2nd column) on 1025px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .invoice-details-row {
                display: none !important;
            }
        }

        /* Extra large devices (large desktops, 1200px and up) */
        @media screen and (min-width: 1200px) {
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

            /* Hide Details column (2nd column) on 1025px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .invoice-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .invoice-details-row {
            display: none;
        }

        .invoice-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .invoice-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .invoice-details-list {
            margin-bottom: 15px;
        }

        .invoice-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .invoice-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .invoice-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .invoice-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .invoice-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile,
        button.btn-icon-mobile {
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

        button.btn-icon-mobile {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile i {
            font-size: 16px;
        }

        .btn-icon-mobile.btn-history i {
            font-size: 18px;
        }

        .btn-icon-mobile.btn-view i,
        .btn-icon-mobile.btn-edit i,
        .btn-icon-mobile.btn-download i,
        .btn-icon-mobile.btn-print i,
        .btn-icon-mobile.btn-delete i,
        .btn-icon-mobile.btn-payment i {
            font-size: 16px;
        }

        /* Toggle button styles */
        .invoice-toggle-btn-table {
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

        .invoice-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .invoice-toggle-btn-table.minus {
            background: #dc3545;
        }

        .invoice-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        .mobile-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .select2-container {
            width: 100% !important;
            min-width: 150px;
        }

        #filterMonth+.select2-container .select2-selection--single,
        #filterYear+.select2-container .select2-selection--single {
            height: 30px !important;
            padding: 0 8px;
            width: 100% !important;
            /* border: 1px solid #0d1b3e !important; */
            border-radius: 5px !important;
            display: flex !important;
            align-items: center;
            /* color: #1b2850 !important; */
            /* font-weight: bold; */
            font-size: 14px;
        }

        #filterMonth,
        #filterYear {
            line-height: 33px !important;
            padding-left: 0 !important;
            width: 100%;
            /* color: #1b2850 !important; */
        }

        #filterMonth+.select2-container .select2-selection__arrow,
        #filterYear+.select2-container .select2-selection__arrow {
            height: 33px !important;
        }

        /* #filter-date {
                        height: 35px !important;
                        border: 1px solid #0d1b3e !important;
                        border-radius: 5px !important;
                        font-size: 14px;
                        font-weight: bold;
                        color: #1b2850 !important;
                        padding: 0 8px;
                    } */

        /* .select2-dropdown {
                        border: 1px solid #0d1b3e !important;
                        z-index: 9999;
                    } */

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            /* color: #1b2850 !important; */
            /* font-weight: bold; */
        }

        .download-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .download-loader-overlay.d-none {
            display: none !important;
        }

        .download-loader-box {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 8px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .download-loader-box h4 {
            margin: 0 0 18px 0;
            font-size: 34px;
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .download-loader-box h4 {
                font-size: 28px;
            }
        }
    </style>
    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>

        <style>
            .fade-out {
                opacity: 1;
                transition: opacity 0.5s ease-out;
            }

            .fade-out.hidden {
                opacity: 0;
            }
        </style>

        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden'); // Triggers the fade-out transition
                    // Remove the element from DOM after fadeout (optional)
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500); // match the CSS transition duration (0.5s)
                }
            }, 4000);
        </script>
    @endif
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Invoices</h4>
                <!-- <h6>Manage your purchases</h6> -->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(4, 'add'))
                    <a href="{{ route('custom_invoice.add') }}" class="btn btn-sm btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img">New Invoice
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div> --}}
                <div class="table-top mb-3">
                    <div class="row g-2 align-items-end">
                        <!-- Search -->
                        <div class="col-md-3 col-sm-6 col-12">
                            <div class=" w-100">
                                <div class="search-input">
                                    <input type="text" id="search-input" class="form-control" placeholder="Search..."
                                        style="height: 30px;">
                                </div>
                            </div>
                        </div>

                        <!-- Total (only for admin/sub-admin) -->
                        @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                            <div class="col-md-2 col-sm-6 col-12">
                                <div class="invoice-filter-total">
                                    Total: <span style="color: #ff9f43" id="filtered-total">₹0.00</span>
                                </div>
                            </div>
                        @endif

                        <!-- Filter by Month -->
                        <div class="col-md-2 col-sm-6 col-6">
                            <div class="date-filter w-100">
                                <select id="filterMonth" class="form-select form-select-sm"
                                    style="font-size: 0.875rem; height: 38px;">
                                    <option value="">All Months</option>
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filter by Year -->
                        <div class="col-md-2 col-sm-6 col-6">
                            <div class="date-filter w-100">
                                <select id="filterYear" class="form-select form-select-sm"
                                    style="font-size: 0.875rem; height: 38px;">
                                    <option value="">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date Filter -->
                        <div class="col-md-2 col-sm-6 col-6">
                            <div class="date-filter w-100">
                                <input type="text" id="filter-date" placeholder="Choose Date"
                                    class="datetimepicker form-control form-control-sm" style="height: 30px;">
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-md-1 col-sm-6 col-12">
                            <div class="invoice-filter-actions d-flex gap-2">
                                <button id="exportAllChallan" class="btn btn-sm btn-success w-100"
                                    style="white-space: nowrap;">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button id="exportPdf" class="btn btn-sm btn-danger w-100" style="white-space: nowrap;">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Inputs Card -->
                <div class="card" id="filter_inputs">
                    <div class="card-body pb-0">
                        <div class="row">
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Name" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <input type="text" placeholder="Enter Reference No" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group">
                                    <select class="form-select">
                                        <option>Completed</option>
                                        <option>Paid</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6 col-12">
                                <div class="form-group d-flex justify-content-end">
                                    <a class="btn btn-filters ms-auto">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-whites.svg' }}"
                                            alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div>

                <div class="table-responsive mt-3" style="overflow-x: auto;">

                    <table class="table datanew" id="order-table" style="max-width: 2000px;">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th class="text-center">Details</th>
                                <th>Date</th>
                                <th>Name</th>
                                <th>Grand Total</th>
                                <th>Invoice Status</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
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

    <form id="makePaymentForm">
        <div class="modal fade" id="makePaymentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="text-end mb-3">
                            <button type="button" class="btn btn-sm btn-cancel text-white" id="viewHistoryBtn">View
                                Payment History</button>
                        </div>

                        <!-- ✅ Payment History Container -->
                        <div id="paymentHistoryBox" class="border p-2 rounded bg-white d-none mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="mb-0">Payment History</h6>

                                <!-- 👇 Visible Close Button -->
                                <button type="button" class="btn btn-sm btn-danger" id="closeHistoryBtn">
                                    &times; <!-- This renders an “×” symbol -->
                                </button>
                            </div>

                            <ul id="paymentHistoryList" class="list-unstyled mb-0"
                                style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated via JavaScript -->
                            </ul>
                        </div>
                        <div class="border p-2 rounded bg-light">
                            <strong>Total Amount:</strong> ₹<span id="emiTotal"></span><br>
                            <strong>Remaining Amount:</strong> ₹<span id="remainingAmountDisplay">0.00</span>

                        </div>
                        <!-- ✅ View Payment History Button -->
                        <br>
                        <!-- Payment Method -->
                        <div class="mb-3" id="paymentMethodDiv">
                            <label for="paymentMethodSelect" class="form-label">Select Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="payment_method">
                                <option value="" selected disabled>Select</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cash_online">Cash + Online</option>

                            </select>
                            <div class="text-danger" id="paymentMethodError"></div>
                        </div>

                        <div class="mb-3 d-none" id="cashOnlineTypeDiv">
                            <label for="cashOnlineTypeSelect" class="form-label">Select Cash + Online Type</label>
                            <select class="form-select" id="cashOnlineTypeSelect" name="cash_online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_online_fully">Cash + Online Fully</option>
                                <option value="cash_online_partially">Cash + Online Partially</option>
                            </select>
                            <div class="text-danger" id="cashOnlineTypeError"></div>
                        </div>

                        <!-- Fully Cash + Online -->
                        <!-- Fully Cash + Online -->
                        <div class="mb-3 d-none" id="fullyCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="text" id="cashOnlineFullAmount" name="fully_cash_amount"
                                class="form-control">
                            <div class="text-danger" id="cashOnlineFullAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="text" id="upiOnlineFullAmount" name="full_online_amount"
                                class="form-control" readonly>
                            <div class="text-danger" id="upiOnlineFullAmountError"></div>
                        </div>

                        <!-- Partial Cash + Online -->
                        <div class="mb-3 d-none" id="partialCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="text" id="cashOnlinePartialAmount" name="cash_amount" class="form-control">
                            <div class="text-danger" id="cashOnlinePartialAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="text" id="upiOnlinePartialAmount" name="online_amount" class="form-control">
                            <div class="text-danger" id="upiOnlinePartialAmountError"></div>
                            <label class="mt-2">Remaining Amount</label>
                            <input type="text" id="remainingCashOnlineAmount" name="remaining_amount"
                                class="form-control" readonly>
                        </div>

                        <div class="mb-3 d-none" id="onlineTypeDiv">
                            <label for="onlineTypeSelect" class="form-label">Select Online Type</label>
                            <select class="form-select" id="onlineTypeSelect" name="online_type">
                                <option value="" selected disabled>Select</option>
                                <option value="online_fully">Online Fully</option>
                                <option value="online_partially">Online Partially</option>
                            </select>
                            <div class="text-danger" id="onlineTypeError"></div>
                        </div>

                        <!-- Paid Type Dropdown (Hidden by default) -->
                        <div class="mb-3 d-none" id="paidTypeDiv">
                            <label for="paidTypeSelect" class="form-label">Paid Type</label>
                            <select class="form-select" id="paidTypeSelect" name="paid_type">
                                <option value="" selected disabled>Select</option>
                                <option value="cash_partially">Cash Partially</option>
                                <option value="cash_fully">Cash Fully</option>
                            </select>
                            <div class="text-danger" id="paidTypeError"></div>
                        </div>

                        <!-- UPI Amount Input -->
                        <div class="mb-3 d-none" id="upiAmountDiv">
                            <label for="upiAmountInput" class="form-label">Online Amount</label>
                            <input type="text" class="form-control" id="upiAmountInput" name="upi_online_amount"
                                readonly>
                            <div class="text-danger" id="upiAmountError"></div>
                        </div>

                        <!-- Partially Paid Fields -->
                        <div class="mb-3 d-none" id="partialPaidFields">
                            <label for="partialAmount" class="form-label">Enter Amount</label>
                            <input type="text" class="form-control mb-2" id="partialAmount" name="amount">
                            <div class="text-danger" id="partialAmountError"></div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <div style="flex: 1;">
                                    <label for="pendingAmount" class="form-label">Pending Amount</label>
                                    <input type="text" class="form-control" id="pendingAmount"
                                        name="cash_pending_amount" readonly>
                                </div>

                            </div>
                        </div>

                        <!-- Fully Paid Fields -->
                        <div class="mb-3 d-none" id="fullyPaidFields">
                            <label class="form-label">Cash Amount</label>
                            <input type="text" class="form-control" id="cashAmount" name="cashAmount">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>

                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="custom_invoice_id">
                        <input type="hidden" id="remainingAmountHidden" name="remaining_amount">
                        <input type="hidden" id="paymentMethodHidden" name="payment_type">

                        <div class="text-end">
                            <button type="submit" class="btn btn-submit text-white"
                                style="background-color: #ff9f43;">Submit Payment</button>
                            <button type="button" class="btn btn-secondary btn-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Global Payment History Modal -->
    <div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentHistoryLabel">Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <ul id="globalPaymentHistoryList" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderText">Generating PDF...</h4>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>
@endsection



@push('js')
    <script>
        const userRole = "{{ auth()->user()->role }}";
        let authToken = localStorage.getItem("authToken");
        let invoiceTable;
        let currentInvoicePage = 1;
        let currentInvoicePerPage = 10;
        let overallInvoiceGrandTotal = 0;
        let invoiceCurrencySymbol = '₹';
        let invoiceCurrencyPosition = 'left';
        let searchQuery = '';
        let filterMonth = '';
        let filterYear = '';
        let filterDate = '';

        // Define loadPurchases as a global function BEFORE it's called
        window.loadPurchases = function(page = 1) {
            currentInvoicePage = page;

            function formatToYMD(dateStr) {
                if (!dateStr || !dateStr.includes('-')) return '';
                const [day, month, year] = dateStr.split('-');
                return `${year}-${month}-${day}`;
            }

            const rawDate = $('#filter-date').val();
            const filterDateVal = formatToYMD(rawDate);
            const selectedMonth = $('#filterMonth').val();
            const selectedYear = $('#filterYear').val();

            const filters = {
                date: filterDateVal,
                month: selectedMonth,
                year: selectedYear,
                search: $('#search-input').val().trim(),
                vendor_name: $('#filter_inputs input[placeholder="Enter Name"]').val().trim(),
                reference_no: $('#filter_inputs input[placeholder="Enter Reference No"]').val().trim(),
                status: $('#filter_inputs select').val()
            };

            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            $.ajax({
                url: "/api/custom_invoice_list",
                type: "GET",
                dataType: "json",
                data: {
                    date: filters.date,
                    month: filters.month,
                    year: filters.year,
                    search: filters.search,
                    vendor_name: filters.vendor_name,
                    reference_no: filters.reference_no,
                    status: filters.status,
                    selectedSubAdminId: selectedSubAdminId,
                    page: currentInvoicePage,
                    per_page: currentInvoicePerPage
                },
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(res) {
                    const rows = [];
                    renderInvoicePagination(res.pagination || {});
                    overallInvoiceGrandTotal = parseFloat(res.overall_grand_total || 0);
                    invoiceCurrencySymbol = res.currency_symbol || '₹';
                    invoiceCurrencyPosition = res.currency_position || 'left';
                    updateOverallInvoiceTotal();

                    if (res.success && Array.isArray(res.data)) {
                        const currencySymbol = res.currency_symbol || '₹';
                        const currencyPosition = res.currency_position || 'left';

                        if (!window.invoiceDataMap) {
                            window.invoiceDataMap = {};
                        }

                        res.data.forEach(function(o) {
                            let amount = formatCurrency(o.grand_total || 0);
                            let formattedAmount = currencyPosition === 'right' ?
                                `${amount}${currencySymbol}` :
                                `${currencySymbol}${amount}`;

                            const invoiceData = {
                                ...o,
                                date: formatDate(o.date),
                                displayAmount: formattedAmount,
                                currencySymbol: currencySymbol,
                                currencyPosition: currencyPosition
                            };
                            window.invoiceDataMap[o.id] = invoiceData;

                            rows.push([
                                `<a href="/custom-invoice-print/${o.id}" class="" style="text-decoration: none;">${o.invoice_number || ''}</a>`,
                                `<button class="invoice-toggle-btn-table" onclick="toggleInvoiceRowDetails('${o.id}')" data-invoice-id="${o.id}">
                                    <span class="toggle-icon">+</span>
                                </button>`,
                                formatDate(o.date),
                                `<span class="text-bolds" style="text-transform:capitalize;">${o.vendor_name || ''}</span>`,
                                formattedAmount,
                                badge(o.purchase_status || '', 'bg-lightgreen',
                                    'bg-lightyellow'),
                                badge(o.payment_status || '', 'bg-lightgreen',
                                    'bg-lightred'),
                                actionLinks(o)
                            ]);
                        });
                    }

                    initializeInvoiceTable(rows);

                    const topScroll = document.querySelector('.table-scroll-top');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const table = document.getElementById('order-table');

                    if (topScroll && table) {
                        const topInnerDiv = topScroll.querySelector('div');
                        if (topInnerDiv) {
                            topInnerDiv.style.width = table.scrollWidth + 'px';
                        }

                        topScroll.onscroll = function() {
                            tableResponsive.scrollLeft = topScroll.scrollLeft;
                        };
                        tableResponsive.onscroll = function() {
                            topScroll.scrollLeft = tableResponsive.scrollLeft;
                        };
                    }
                },
                error: function(xhr) {
                    console.error('purchase_list error:', xhr.responseText);
                }
            });
        };

        // Helper functions
        function formatDate(dateStr) {
            if (!dateStr) return 'N/A';
            const d = new Date(dateStr);
            const day = String(d.getDate()).padStart(2, '0');
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const month = months[d.getMonth()];
            const year = d.getFullYear();
            let hours = d.getHours();
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12 || 12;
            return `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;
        }

        function badge(text, okClass, warnClass) {
            const positiveStatuses = ['paid', 'completed', 'delivered', 'success'];
            const value = String(text).toLowerCase();
            const isPositive = positiveStatuses.includes(value);
            const cls = isPositive ? okClass : warnClass;
            return `<span class="badges ${cls}" style="text-transform:capitalize;">${text}</span>`;
        }

        function actionLinks(o) {
            var buttons = '';
            if (parseFloat(o.remaining_amount) > 0) {
                buttons += `
                    <button type="button"
                        class="btn btn-sm btn-primary me-3 make-payment-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#makePaymentModal"
                        data-id="${o.id}"
                        data-amount="${o.remaining_amount}"
                        data-method="${o.payment_mode || ''}"
                        data-total-amount="${o.grand_total || 0}"
                        data-remaining-amount="${o.remaining_amount}">
                        Make Payment
                    </button>
                `;
            }

            buttons += `
                <button class="btn open-history" data-id="${o.id}" title="Payment History">
                    <i class="fas fa-history" style="font-size: 16px;"></i>
                </button>
            `;

            @if (app('hasPermission')(4, 'view'))
                buttons += `<a class="me-3" href="/invoice-view/${o.id}">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"></path>
                        <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"></path>
                    </svg>
                </a>`;
            @endif

            if (!o.has_payment || o.has_payment === 0) {
                @if (app('hasPermission')(4, 'edit'))
                    buttons += `<a class="me-3" href="/edit-custom-invoice/${o.id}">
                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                        </svg>
                    </a>`;
                @endif
            }

            @if (app('hasPermission')(4, 'view'))
                buttons += `<a class="me-3" href="javascript:void(0);" onclick="window.open('/custom-invoice/pdf/' + ${o.id});" title="Print Invoice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                        <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
                    </svg>
                </a>`;
            @endif

            if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !== 'inventory-manager') {
                @if (app('hasPermission')(4, 'delete'))
                    buttons += `<a class="me-3 confirm-text delete-order" data-id="${o.id}" href="javascript:void(0);">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"></path>
                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"></path>
                        </svg>
                    </a>`;
                @endif
            }

            return buttons;
        }

        function renderInvoicePagination(pagination) {
            const total = Number(pagination?.total || 0);
            const perPage = Number(pagination?.per_page || currentInvoicePerPage || 10);
            const currentPage = Number(pagination?.current_page || 1);
            const lastPage = Number(pagination?.last_page || 1);
            const from = total === 0 ? 0 : ((currentPage - 1) * perPage) + 1;
            const to = total === 0 ? 0 : Math.min(currentPage * perPage, total);

            currentInvoicePage = currentPage;
            currentInvoicePerPage = perPage;

            $('#per-page-select').val(String(perPage));
            $('#pagination-from').text(from);
            $('#pagination-to').text(to);
            $('#pagination-total').text(total);

            const $pagination = $('#pagination-numbers');
            $pagination.empty();

            if (lastPage <= 1) {
                return;
            }

            for (let page = 1; page <= lastPage; page++) {
                const activeClass = page === currentPage ? ' active' : '';
                $pagination.append(`
                    <li class="page-item${activeClass}">
                        <a class="page-link" href="#" data-page="${page}">${page}</a>
                    </li>
                `);
            }
        }

        function initializeInvoiceTable(rows) {
            const $tbl = $('#order-table');

            if ($.fn.DataTable.isDataTable($tbl)) {
                $tbl.DataTable().clear().destroy();
                $tbl.find('tbody').empty();
            }

            $tbl.find('tbody').empty();

            invoiceTable = $tbl.DataTable({
                data: rows,
                responsive: true,
                autoWidth: false,
                paging: false,
                info: false,
                lengthChange: false,
                ordering: true,
                searching: false,
                language: {
                    emptyTable: "No invoices found"
                },
                destroy: true,
                retrieve: true
            });

            invoiceTable.off('draw').on('draw', function() {
                if (window.addInvoiceExpandableRows) {
                    window.addInvoiceExpandableRows(invoiceTable);
                }
                calculateInvoiceFilteredTotal();
            });

            setTimeout(function() {
                if (window.addInvoiceExpandableRows) {
                    window.addInvoiceExpandableRows(invoiceTable);
                }
                calculateInvoiceFilteredTotal();
            }, 100);
        }

        function updateOverallInvoiceTotal() {
            const formattedTotal = formatCurrency(overallInvoiceGrandTotal);
            const displayTotal = invoiceCurrencyPosition === 'right' ?
                `${formattedTotal}${invoiceCurrencySymbol}` :
                `${invoiceCurrencySymbol}${formattedTotal}`;
            $('#filtered-total').text(displayTotal);
        }

        function buildInvoiceExpandableRowContent(invoice, currencySymbol, currencyPosition) {
            const amount = parseFloat(invoice.grand_total || 0).toFixed(2);
            const displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            let actionBtns = '';

            if (parseFloat(invoice.remaining_amount || 0) > 0) {
                actionBtns += `<button type="button" class="btn btn-sm btn-primary me-3 make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${invoice.id}"
                    data-amount="${invoice.remaining_amount}"
                    data-method="${invoice.payment_mode || ''}"
                    data-total-amount="${invoice.grand_total || 0}"
                    data-remaining-amount="${invoice.remaining_amount}"
                    style="background-color: #ff9f43; border-color: #ff9f43; color: white;">
                    Make Payment
                </button>`;
            }

            actionBtns += `<button class="btn-icon-mobile btn-history open-history" data-id="${invoice.id}" title="Payment History">
                <i class="fas fa-history"></i>
            </button>`;

            @if (app('hasPermission')(4, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-view" href="/invoice-view/${invoice.id}" title="View">
                    <i class="fas fa-eye"></i>
                </a>`;
            @endif

            @if (app('hasPermission')(4, 'edit'))
                actionBtns += `<a class="btn-icon-mobile btn-edit" href="/edit-custom-invoice/${invoice.id}" title="Edit">
                    <i class="fas fa-edit"></i>
                </a>`;
            @endif

            @if (app('hasPermission')(4, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-print" href="javascript:void(0);" onclick="window.open('/custom-invoice/pdf/' + ${invoice.id});" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>`;
            @endif

            if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !== 'inventory-manager') {
                @if (app('hasPermission')(4, 'delete'))
                    actionBtns += `<a class="btn-icon-mobile btn-delete delete-order" href="javascript:void(0);" data-id="${invoice.id}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>`;
                @endif
            }

            return `
                <td colspan="8" class="invoice-details-content">
                    <div class="invoice-details-list">
                        <div class="invoice-detail-row-simple">
                            <span class="invoice-detail-label-simple">Date:</span>
                            <span class="invoice-detail-value-simple">${invoice.date || 'N/A'}</span>
                        </div>
                        <div class="invoice-detail-row-simple">
                            <span class="invoice-detail-label-simple">Name:</span>
                            <span class="invoice-detail-value-simple">${invoice.vendor_name || 'N/A'}</span>
                        </div>
                        <div class="invoice-detail-row-simple">
                            <span class="invoice-detail-label-simple">Invoice Status:</span>
                            <span class="invoice-detail-value-simple">
                                <span class="mobile-badge bg-lightgreen">${invoice.purchase_status || 'N/A'}</span>
                            </span>
                        </div>
                        <div class="invoice-detail-row-simple">
                            <span class="invoice-detail-label-simple">Payment Status:</span>
                            <span class="invoice-detail-value-simple">
                                <span class="mobile-badge bg-lightgreen">${invoice.payment_status || 'N/A'}</span>
                            </span>
                        </div>
                        <div class="invoice-detail-row-simple">
                            <span class="invoice-detail-label-simple">Grand Total:</span>
                            <span class="invoice-detail-value-simple" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                        </div>
                        ${parseFloat(invoice.remaining_amount || 0) > 0 ? `
                            <div class="invoice-detail-row-simple">
                                <span class="invoice-detail-label-simple">Remaining:</span>
                                <span class="invoice-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                    ${currencySymbol}${formatCurrency(invoice.remaining_amount || 0)}
                                </span>
                            </div>
                            ` : ''}
                    </div>
                    <div class="invoice-action-buttons-simple">
                        ${actionBtns}
                    </div>
                </td>
            `;
        }

        window.toggleInvoiceRowDetails = function(invoiceId) {
            const btn = $(`.invoice-toggle-btn-table[data-invoice-id="${invoiceId}"]`);
            if (btn.length === 0) {
                console.error('Toggle button not found for invoice:', invoiceId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.invoice-details-row[data-invoice-id="${invoiceId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const invoiceData = window.invoiceDataMap && window.invoiceDataMap[invoiceId];
                if (invoiceData) {
                    detailsRow = $('<tr>')
                        .addClass('invoice-details-row')
                        .attr('data-invoice-id', invoiceId)
                        .html(buildInvoiceExpandableRowContent(invoiceData, invoiceData.currencySymbol, invoiceData
                            .currencyPosition));
                    row.after(detailsRow);
                } else {
                    console.error('Invoice data not found for invoice:', invoiceId);
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

        window.addInvoiceExpandableRows = function(dt) {
            if (!dt) return;
            const currentWidth = $(window).width();
            const isMobileOrTablet = currentWidth <= 1024;

            if (!isMobileOrTablet) {
                $('tr.invoice-details-row').remove();
                return;
            }

            dt.rows().every(function() {
                const row = this.node();
                const toggleBtn = $(row).find('.invoice-toggle-btn-table');
                if (toggleBtn.length > 0) {
                    const invoiceId = toggleBtn.data('invoice-id');
                    const invoiceData = window.invoiceDataMap && window.invoiceDataMap[invoiceId];
                    if (invoiceData && !$(row).next('tr.invoice-details-row[data-invoice-id="' + invoiceId +
                            '"]').length) {
                        const expandableRow = $('<tr>')
                            .addClass('invoice-details-row')
                            .attr('data-invoice-id', invoiceId)
                            .html(buildInvoiceExpandableRowContent(invoiceData, invoiceData.currencySymbol,
                                invoiceData.currencyPosition));
                        $(row).after(expandableRow);
                    }
                }
            });
        };

        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function getRawAmount(val) {
            if (!val) return 0;
            if (typeof val === 'string') {
                return parseFloat(val.replace(/,/g, '')) || 0;
            }
            return parseFloat(val) || 0;
        }

        function calculateInvoiceFilteredTotal() {
            updateOverallInvoiceTotal();
        }

        // Document Ready - Initialize everything
        $(document).ready(function() {
            // Initialize DataTable
            invoiceTable = $('.datanew').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false,
                "responsive": true,
                "autoWidth": false
            });

            // Initialize Select2
            $('#filterMonth').select2({
                placeholder: "All Months",
                allowClear: true,
                width: '100%'
            });

            $('#filterYear').select2({
                placeholder: "All Years",
                allowClear: true,
                width: '100%'
            });

            // Initial load
            window.loadPurchases(1);

            // Search input handler
            let searchTimeout;
            $('#search-input').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    searchQuery = $('#search-input').val().trim();
                    window.loadPurchases(1);
                }, 500);
            });

            // Filter handlers
            $('#filterMonth, #filterYear').on('change', function() {
                window.loadPurchases(1);
            });

            $('#filter-date').on('dp.change', function() {
                window.loadPurchases(1);
            });

            // Per page select
            $('#per-page-select').on('change', function() {
                currentInvoicePerPage = parseInt($(this).val(), 10) || 10;
                window.loadPurchases(1);
            });

            // Pagination click handler
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                const $parent = $(this).closest('.page-item');
                if ($parent.hasClass('disabled') || $parent.hasClass('active')) {
                    return;
                }
                const page = parseInt($(this).data('page'), 10);
                if (page && page >= 1) {
                    window.loadPurchases(page);
                }
            });

            // Delete handler
            $(document).on("click", ".delete-order", function() {
                let orderId = $(this).data("id");
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/api/custom_invoice_delete",
                            type: "POST",
                            data: {
                                id: orderId,
                                _token: "{{ csrf_token() }}"
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: response.message,
                                        icon: "success",
                                        confirmButtonColor: "#ff9f43"
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: "Error!",
                                        text: response.message,
                                        icon: "error",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = "Something went wrong. Try again.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: "Error!",
                                    text: errorMessage,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43"
                                });
                            }
                        });
                    }
                });
            });

            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $exportButtons = $("#exportAllChallan, #exportPdf");

            function toggleDownloadLoader(isLoading, message) {
                if (isLoading) {
                    $downloadLoaderText.text(message || "Generating report...");
                    $downloadLoader.removeClass("d-none");
                    $exportButtons.prop("disabled", true).addClass("disabled").attr("aria-disabled", "true");
                } else {
                    $downloadLoader.addClass("d-none");
                    $exportButtons.prop("disabled", false).removeClass("disabled").removeAttr("aria-disabled");
                }
            }

            // Export handlers
            $('#exportAllChallan').click(function() {
                let selectedYear = $('#filterYear').val() || '';
                let selectedMonth = $('#filterMonth').val() || '';
                let selectedDate = $('#filter-date').val() || '';
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: '/export-invoice',
                    method: 'GET',
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating Excel...");
                    },
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                    },
                    data: {
                        year: selectedYear,
                        month: selectedMonth,
                        date: selectedDate,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(blob, status, xhr) {
                        const disposition = xhr.getResponseHeader('Content-Disposition');
                        let filename = "invoices.xlsx";
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            filename = disposition.split('filename=')[1].replace(/['"]/g, '');
                        }
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                        Swal.fire({
                            icon: 'success',
                            title: 'Download Ready',
                            text: 'The Excel file has been successfully downloaded.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Download Failed',
                            text: 'Failed to generate Excel file. Please try again.',
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            });

            $('#exportPdf').click(function() {
                let selectedYear = $('#filterYear').val() || '';
                let selectedMonth = $('#filterMonth').val() || '';
                let selectedDate = $('#filter-date').val() || '';
                let selectedSubAdminId = $('#subAdminSelect').val() || '';

                $.ajax({
                    url: `/export-invoice-pdf`,
                    type: 'GET',
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating PDF...");
                    },
                    data: {
                        year: selectedYear,
                        month: selectedMonth,
                        date: selectedDate,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data, status, xhr) {
                        let disposition = xhr.getResponseHeader('Content-Disposition');
                        let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(
                        disposition);
                        let filename = (matches && matches[1]) ? matches[1].replace(/['"]/g,
                            '') : 'Export.pdf';
                        let blob = new Blob([data], {
                            type: 'application/pdf'
                        });
                        let link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        Swal.fire({
                            icon: 'success',
                            title: 'Download Ready',
                            text: 'The PDF file has been successfully downloaded.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Download Failed',
                            text: xhr.responseJSON?.message ?? 'Unknown error',
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            });
        });

        // Payment form handlers (keep your existing ones)
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");

            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let method = $(this).data('method') || '';

                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(formatCurrency(totalAmount));
                $('#remainingAmountDisplay').text(formatCurrency(remainingAmount));
                $('#remainingAmountHidden').val(remainingAmount);
                $('#paymentMethodHidden').val(method);
                $('#paymentMethodSelect').val('');
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                $('#viewHistoryBtn').off('click').on('click', function() {
                    $.ajax({
                        url: '/custom-invoice/payment-history/' + jobCardId,
                        method: 'GET',
                        success: function(response) {
                            const history = response.data;
                            if (!history || history.length === 0) {
                                $('#paymentHistoryList').html(
                                    '<li>No payment history found.</li>');
                            } else {
                                let historyHtml = '';
                                history.forEach(function(payment) {
                                    historyHtml += `
                                        <li class="mb-2">
                                            <strong>Amount:</strong> ₹${formatCurrency(payment.payment_amount)}<br>
                                            <strong>Date:</strong> ${payment.payment_date}<br>
                                            <strong>Method:</strong> ${payment.payment_method}<br>
                                            <strong>Payment Type:</strong> ${payment.payment_type ? payment.payment_type : 'N/A'}<br>
                                            ${payment.payment_type === 'emi' ? `<strong>EMI Months:</strong> ${payment.emi_month || 0}<br>` : ''}
                                        </li>
                                        <hr class="my-1"/>
                                    `;
                                });
                                $('#paymentHistoryList').html(historyHtml);
                            }
                            $('#paymentHistoryBox').removeClass('d-none');
                        },
                        error: function() {
                            $('#paymentHistoryList').html(
                                '<li class="text-danger">Failed to load payment history.</li>'
                                );
                            $('#paymentHistoryBox').removeClass('d-none');
                        }
                    });
                });

                $('#closeHistoryBtn').off('click').on('click', function() {
                    $('#paymentHistoryBox').addClass('d-none');
                });
            });

            $('#paymentMethodSelect').on('change', function() {
                let method = $(this).val();

                // Hide all optional sections first
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields')
                    .addClass('d-none');

                if (method === 'cash') {
                    $('#paidTypeDiv').removeClass('d-none'); // Show paid type options

                } else if (method === 'online') {
                    $('#onlineTypeDiv').removeClass('d-none'); // Show online type dropdown

                } else if (method === 'cash_online') {
                    $('#cashOnlineTypeDiv').removeClass(
                        'd-none'); // Show Cash + Online type dropdown
                }
            });

            // ✅ Handle Paid Type (when Payment Method = Cash)
            $('#paidTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#partialPaidFields, #fullyPaidFields').addClass('d-none');

                // Disable all inputs first
                $('#partialPaidFields input, #fullyPaidFields input').prop('disabled', true);

                if (type === 'cash_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Clear & reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(formatCurrency(remaining));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = getRawAmount($(this).val());
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(formatCurrency(newPending));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(formatCurrency(remaining)).prop('readonly', true);

                    // Reset partial fields
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });


            // ✅ Handle Online Type (when Payment Method = Online)
            $('#onlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#upiAmountDiv, #partialPaidFields').addClass('d-none');
                // Disable all inputs first
                $('#upiAmountDiv input, #partialPaidFields input').prop('disabled', true);

                if (type === 'online_partially') {
                    // Show partial fields
                    $('#partialPaidFields').removeClass('d-none');
                    $('#partialPaidFields input').prop('disabled', false);

                    // Reset values
                    $('#partialAmount').val('');
                    $('#pendingAmount').val(formatCurrency(remaining));

                    // Clear and disable UPI field
                    $('#upiAmountInput').val('').prop('readonly', false).prop('disabled', true);

                    // Live pending update
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = getRawAmount($(this).val());
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(formatCurrency(newPending));
                    });

                } else if (type === 'online_fully') {
                    // Show fully online section
                    $('#upiAmountDiv').removeClass('d-none');
                    $('#upiAmountDiv input').prop('disabled', false);

                    // Fill with remaining and lock editing
                    $('#upiAmountInput').val(formatCurrency(remaining)).prop('readonly', true);

                    // Reset partial section
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

            // ✅ Handle Cash + Online Type
            $('#cashOnlineTypeSelect').on('change', function() {
                let type = $(this).val();
                let remaining = parseFloat($('#remainingAmountHidden').val()) || 0;

                // Hide both sections
                $('#fullyCashOnlineFields, #partialCashOnlineFields').addClass('d-none');
                // Disable all inputs first
                $('#fullyCashOnlineFields input, #partialCashOnlineFields input').prop('disabled',
                    true);

                if (type === 'cash_online_fully') {
                    // Show fully section
                    $('#fullyCashOnlineFields').removeClass('d-none');
                    $('#fullyCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlineFullAmount').val('');
                    $('#upiOnlineFullAmount').val(formatCurrency(remaining));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = getRawAmount($(this).val());
                        let online = Math.max(remaining - cash, 0);
                        $('#upiOnlineFullAmount').val(formatCurrency(online));
                    });

                    // Disable partial fields
                    $('#partialCashOnlineFields input').prop('disabled', true);

                } else if (type === 'cash_online_partially') {
                    // Show partial section
                    $('#partialCashOnlineFields').removeClass('d-none');
                    $('#partialCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
                    $('#remainingCashOnlineAmount').val(formatCurrency(remaining));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = getRawAmount($(this).val());
                        let newRemaining = Math.max(remaining - cash, 0);
                        $('#remainingCashOnlineAmount').val(formatCurrency(newRemaining));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = getRawAmount($(this).val());
                        let cash = getRawAmount($('#cashOnlinePartialAmount').val());
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        $('#remainingCashOnlineAmount').val(formatCurrency(newRemaining));
                    });

                    // Disable fully fields
                    $('#fullyCashOnlineFields input').prop('disabled', true);
                }
            });

            $('#makePaymentForm').on('submit', function(e) {
                e.preventDefault();


                let isValid = true;



                // Clear all previous errors
                $('.text-danger').text('');

                // Get values
                let paymentMethod = $('#paymentMethodSelect').val();
                let paymentTypeemionly = $('#paymentType').val();
                let paidType = $('#paidTypeSelect').val();
                let onlineType = $('#onlineTypeSelect').val();
                let cashOnlineType = $('#cashOnlineTypeSelect').val();



                // EMI Validation



                // Payment Method validation


                if (!paymentMethod) {
                    isValid = false;
                    // console.log("Validation failed: Payment method not selected");
                    $('#paymentMethodError').text("Please select a payment method.");
                    return false;
                } else {
                    // console.log("Payment method selected:", paymentMethod);
                }


                // Cash Payment Validation
                if (paymentMethod === 'cash') {
                    // console.log("Cash payment selected");

                    if (!paidType) {
                        isValid = false;
                        // console.log("Validation failed: Paid type not selected");
                        $('#paidTypeError').text("Please select paid type.");
                        return false;
                    } else {
                        // console.log("Paid type selected:", paidType);
                    }

                    if (paidType === 'cash_partially') {
                        let partialAmount = getRawAmount($('#partialAmount').val());
                        let remainingAmount = getRawAmount($('#remainingAmountHidden').val());

                        // console.log("Cash partially selected, entered amount:", partialAmount, "Remaining:",
                        //     remainingAmount);

                        if (!partialAmount || isNaN(partialAmount) || partialAmount <= 0) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Enter a valid positive partial cash amount.");
                            return false;
                        }

                        if (partialAmount > remainingAmount) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Partial cash amount cannot exceed remaining amount (₹" +
                                formatCurrency(remainingAmount) + ")."
                            );
                            return false;
                        }

                        if (partialAmount < 0) {
                            isValid = false;
                            $('#partialAmountError').text("Amount cannot be negative.");
                            return false;
                        }

                        // console.log("Partial cash amount valid");
                    } else {
                        let cashAmount = getRawAmount($('#cashAmount').val());
                        // console.log("Cash fully selected, amount:", cashAmount);
                        if (!cashAmount || cashAmount <= 0) {
                            isValid = false;
                            // console.log("Validation failed: Invalid full cash amount");
                            $('#cashAmountError').text("Enter a valid cash amount.");
                            return false;
                        } else {
                            // console.log("Full cash amount valid");
                        }
                    }
                }

                // Online Payment Validation
                if (paymentMethod === 'online') {
                    // console.log("Online payment selected");

                    if (!onlineType) {
                        isValid = false;
                        // console.log("Validation failed: Online type not selected");
                        $('#onlineTypeError').text("Please select online type.");
                        return false;
                    } else {
                        // console.log("Online type selected:", onlineType);
                    }

                    let onlineAmount = getRawAmount($('#partialAmount').val()) || getRawAmount($(
                        '#upiAmountInput').val());
                    let remainingAmount = getRawAmount($('#remainingAmountHidden').val());

                    // console.log("Online amount entered:", onlineAmount, "Remaining:", remainingAmount);

                    // ✅ Check 1: Must be a valid positive number
                    if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                        isValid = false;
                        if (onlineType === 'online_partially') {
                            // console.log("Validation failed: Invalid partial online amount");
                            $('#partialAmountError').text(
                                "Enter a valid positive online partial amount.");
                        } else {
                            // console.log("Validation failed: Invalid full online amount");
                            $('#upiAmountError').text("Enter a valid positive online amount.");
                        }
                        return false;
                    }

                    // ✅ Check 2: Cannot exceed remaining
                    if (onlineType === 'online_partially' && onlineAmount > remainingAmount) {
                        // console.log(onlineType);
                        isValid = false;
                        // console.log("Validation failed: Partial online amount exceeds remaining");
                        $('#partialAmountError').text(
                            "Partial online amount cannot exceed remaining amount (₹" +
                            formatCurrency(
                                remainingAmount) + ")."
                        );
                        return false;
                    }

                    // ✅ Check 3: Cannot be negative
                    if (onlineAmount < 0) {
                        isValid = false;
                        // console.log("Validation failed: Negative online amount");
                        $('#partialAmountError').text("Amount cannot be negative.");
                        return false;
                    }

                    // console.log("Online amount valid");
                }


                // Cash + Online Validation
                if (paymentMethod === 'cash_online') {
                    // console.log("Cash + Online payment selected");

                    if (!cashOnlineType) {
                        isValid = false;
                        // console.log("Validation failed: Cash + Online type not selected");
                        $('#cashOnlineTypeError').text("Please select Cash + Online type.");
                        return false;
                    } else {
                        // console.log("Cash + Online type selected:", cashOnlineType);
                    }

                    if (cashOnlineType === 'cash_online_fully') {
                        let cashAmt = getRawAmount($('#cashOnlineFullAmount').val());
                        let onlineAmt = getRawAmount($('#upiOnlineFullAmount').val());
                        // console.log("Cash+Online fully amounts:", cashAmt, onlineAmt);

                        if (!cashAmt || cashAmt <= 0 || !onlineAmt || onlineAmt <=
                            0) {
                            isValid = false;
                            // console.log("Validation failed: Invalid fully cash + online amounts");
                            $('#cashOnlineFullAmountError').text("Enter a valid cash amount.");
                            $('#upiOnlineFullAmountError').text("Enter a valid online amount.");
                            return false;
                        } else {
                            // console.log("Fully cash + online amounts valid");
                        }
                    }

                    if (cashOnlineType === 'cash_online_partially') {
                        let cashAmt = getRawAmount($('#cashOnlinePartialAmount').val());
                        let onlineAmt = getRawAmount($('#upiOnlinePartialAmount').val());

                        // Clean pending amount
                        let pendingAmt = getRawAmount($('#remainingCashOnlineAmount').val());

                        // console.log("Cash+Online partially amounts:", cashAmt, onlineAmt, "Pending:",
                        //     pendingAmt);

                        // ✅ Check for invalid or negative input
                        if ((cashAmt <= 0 && onlineAmt <= 0)) {
                            isValid = false;
                            // console.log("Validation failed: Invalid partially cash + online amounts");
                            $('#cashOnlinePartialAmountError').text(
                                "Enter at least one valid amount.");
                            $('#upiOnlinePartialAmountError').text(
                                "Enter at least one valid amount.");
                            return false;
                        }

                        if (cashAmt < 0 || onlineAmt < 0) {
                            isValid = false;
                            // console.log("Validation failed: Negative amounts are not allowed");
                            $('#cashOnlinePartialAmountError').text("Negative amount not allowed.");
                            $('#upiOnlinePartialAmountError').text("Negative amount not allowed.");
                            return false;
                        }

                        // ✅ Total should not exceed pending amount
                        let total = cashAmt + onlineAmt;
                        // console.log("Total payment:", total, "Pending amount:", pendingAmt);

                        if (total > pendingAmt) {
                            isValid = false;
                            // console.log("Validation failed: Total exceeds pending amount");
                            $('#cashOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (₹" +
                                formatCurrency(
                                    pendingAmt) + ")."
                            );
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (₹" +
                                formatCurrency(
                                    pendingAmt) + ")."
                            );
                            return false;
                        }

                        // console.log("Partially cash + online amounts valid");
                    }

                }



                // console.log('done pay');


                if (isValid) {
                    // this.submit(); // submit the form
                }
                let selectedPaymentType = $('#paymentType').val();
                $('#paymentMethodHidden').val(selectedPaymentType);

                if ($("#paymentMethodDiv").hasClass("d-none")) {
                    $("#newEmiHidden").prop("disabled", true).val("");
                }



                let formElement = $(this)[0];
                let formData = new FormData(formElement);

                // Ensure numeric values are sent to the server (strip commas)
                formData.set('fully_cash_amount', getRawAmount($('#cashOnlineFullAmount').val()));
                formData.set('full_online_amount', getRawAmount($('#upiOnlineFullAmount').val()));
                formData.set('cash_amount', getRawAmount($('#cashOnlinePartialAmount').val()));
                formData.set('online_amount', getRawAmount($('#upiOnlinePartialAmount').val()));
                formData.set('upi_online_amount', getRawAmount($('#upiAmountInput').val()));
                formData.set('amount', getRawAmount($('#partialAmount').val()));
                formData.set('cashAmount', getRawAmount($('#cashAmount').val()));

                let submitButton = $(this).find('button[type="submit"]');
                submitButton.prop('disabled', true).text('Processing...');
                if (paymentMethodSelect === 'emi') {
                    let emiTotal = $('#emiTotalCalculated').val();
                    formData.append('emi_paid_value', emiTotal);
                }

                if (selectedPaymentType === 'emi') {
                    let emi_val = $('#emiTotalCalculated').val();
                    formData.append('amount', emi_val);
                }

                $.ajax({
                    url: "{{ route('custom_invoice.make_payment_submit') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $('#makePaymentModal').modal('hide');
                        submitButton.prop('disabled', false).text('Submit Payment');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Payment submitted successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        submitButton.prop('disabled', false).text('Submit Payment');

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMsg = '';
                            $.each(errors, function(key, value) {
                                errorMsg += value + '\n';
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                text: errorMsg
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.'
                            });
                        }
                    }
                });
            });


            $(document).on('click', '.open-history', function() {
                const jobCardId = $(this).data('id');
                $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
                $.ajax({
                    url: '/api/custom_invoice/payment-history/' + jobCardId,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const history = response.data || [];
                        if (history.length === 0) {
                            $('#globalPaymentHistoryList').html(
                                '<li class="list-group-item">No payment history found.</li>'
                            );
                        } else {
                            const items = history.map(p => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Payment Date: ${(p.payment_date || p.created_at)}</span>
                                    <span><strong>${formatCurrency(p.payment_amount || 0)}</strong> via ${p.payment_method || ''}</span>
                                </li>
                            `).join('');
                            $('#globalPaymentHistoryList').html(items);
                        }
                        const modal = new bootstrap.Modal(document.getElementById(
                            'paymentHistoryModal'));
                        modal.show();
                    },
                    error: function() {
                        $('#globalPaymentHistoryList').html(
                            '<li class="list-group-item text-danger">Failed to load payment history.</li>'
                        );
                        const modal = new bootstrap.Modal(document.getElementById(
                            'paymentHistoryModal'));
                        modal.show();
                    }
                });
            });

        });
    </script>
@endpush
