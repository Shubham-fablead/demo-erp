@extends('layout.app')

@section('title', 'Purchase List')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
        }

        .table-scroll-top {
            display: none;
        }

        .input-groupicon.me-2.dateFilterclass {
            width: 105px !important;
        }

        .form-control {
            color: #595b5d !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
            display: inline-block;
            color: white !important;
            min-width: 80px;
            text-align: center;
        }

        .status-pending {
            background-color: #ea5455 !important;
        }

        .status-completed {
            background-color: #28c76f !important;
        }

        /* Vendor Name column - word wrap */
        .datanew td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px;
            min-width: 120px;
        }

        .datanew th:nth-child(4) {
            white-space: normal;
            word-wrap: break-word;
        }

        /* Desktop view - show header buttons, hide filter row buttons */
        @media screen and (min-width: 768px) {
            .desktop-export-buttons {
                display: flex !important;
            }

            .mobile-export-buttons {
                display: none !important;
            }
        }

        /* Mobile view - hide header buttons, show filter row buttons */
        @media screen and (max-width: 767px) {
            .desktop-export-buttons {
                display: none !important;
            }

            .mobile-export-buttons {
                display: flex !important;
            }
        }

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .search-set {
                width: 95% !important;
                margin: 0 0 10px 0 !important;
            }

            .search-input {
                width: 100%;
                margin-top: 10px;
            }

            .input-groupicon {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .input-groupicon select,
            .input-groupicon input {
                width: 100% !important;
                font-size: 14px;
            }

            .dateFilterclass {
                width: 100% !important;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                width: 100% !important;
            }

            .datanew {
                width: 100% !important;
                table-layout: fixed;
            }

            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: 70% !important;
                text-align: left;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 30% !important;
                text-align: center;
                display: table-cell !important;
            }

            .mobile-order-card {
                display: none;
            }

            #filter_inputs {
                display: none;
            }

            .datanew {
                font-size: 11px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

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
            .datanew td:nth-child(8),
            .datanew th:nth-child(9),
            .datanew td:nth-child(9) {
                display: none;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                display: table-cell !important;
            }

            .filter-row .col-md-2 {
                flex: 0 0 calc(50% - 5px);
                max-width: calc(50% - 5px);
                padding: 0;
            }

            .filter-row .export-buttons-row {
                flex: 0 0 100%;
                max-width: 100%;
                margin-top: 5px;
            }

            .filter-row .export-buttons-row .d-flex {
                justify-content: flex-start !important;
                gap: 10px;
            }

            .filter-row .export-buttons-row button {
                flex: 1;
                font-size: 9px;
                padding: 5px 50px;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .search-set {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .input-groupicon {
                flex: 0 0 calc(50% - 5px);
                margin: 0 0 10px 0 !important;
            }

            .input-groupicon select,
            .input-groupicon input {
                width: 100% !important;
                font-size: 14px;
            }

            .dateFilterclass {
                width: 100% !important;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .datanew {
                width: 100% !important;
                table-layout: fixed;
            }

            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: 50% !important;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 20% !important;
                text-align: center;
                display: table-cell !important;
            }

            .datanew th:nth-child(3),
            .datanew td:nth-child(3) {
                width: 30% !important;
            }

            .mobile-order-card {
                display: none;
            }

            #filter_inputs {
                display: none;
            }

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

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
            .datanew td:nth-child(8),
            .datanew th:nth-child(9),
            .datanew td:nth-child(9) {
                display: none;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
                display: table-cell !important;
            }

            .filter-row .export-buttons-row {
                margin-top: 10px;
            }
        }

        /* Medium devices (tablets, 768px and up to 991px) */
        @media screen and (min-width: 768px) and (max-width: 991.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 13px;
                width: 100% !important;
                table-layout: auto;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
            }

            .order-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 992px and up) */
        @media screen and (min-width: 992px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
                width: 100% !important;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
            }

            .order-details-row {
                display: none !important;
            }
        }

        /* Expandable row details */
        .order-details-row {
            display: none;
        }

        .order-details-row.show {
            display: table-row;
        }

        .order-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .order-details-list {
            margin-bottom: 15px;
        }

        .order-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .order-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .order-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .mobile-action-buttons-simple {
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

        .mobile-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .mobile-toggle-btn-table {
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

        .mobile-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .mobile-toggle-btn-table.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        @media screen and (min-width: 992px) {
            .order-details-content {
                padding: 20px;
            }

            .order-detail-label-simple,
            .order-detail-value-simple {
                font-size: 15px;
            }

            .btn-icon-mobile {
                width: 42px;
                height: 42px;
            }

            .btn-icon-mobile i {
                font-size: 17px;
            }
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }
        }

        /* FILTER UI SAME SIZE DESIGN */
        .total-box {
            color: #1b2850;
            border: 1px solid #0d1b3e;
            border-radius: 5px;
            padding: 4px 8px;
            font-size: 14px;
            font-weight: bold;
            height: 32px;
            display: flex;
            align-items: center;
        }

        #filtered-total {
            color: #ff9f43;
            margin-left: 5px;
        }

        .filter-row input.form-control-sm,
        .filter-row select.form-control-sm {
            height: 32px !important;
            border-radius: 5px;
            font-size: 14px;
        }

        .filter-row .select2-container--default .select2-selection--single {
            height: 32px !important;
            border-radius: 5px !important;
            padding: 2px 8px;
        }

        .filter-row .select2-selection__rendered {
            line-height: 28px !important;
        }

        .filter-row .select2-selection__arrow {
            height: 30px !important;
        }

        .filter-row .mb-1 {
            margin-bottom: 4px !important;
        }

        /* MOBILE HEADER */
        @media (max-width: 767px) {
            .page-header {
                display: flex !important;
                flex-direction: row !important;
                /* justify-content: center !important; */
                align-items: center !important;
                flex-wrap: nowrap !important;
                gap: 20px;
                white-space: nowrap;
            }

            .page-header .page-title {
                width: auto !important;
                margin: 0 !important;
            }

            .page-header .page-title h4 {
                font-size: 16px;
                margin: 0;
                white-space: nowrap;
            }

            .page-header .header-actions {
                display: flex !important;
                flex-wrap: nowrap !important;
                justify-content: center !important;
                align-items: center !important;
                gap: 6px;
            }

            .page-header .btn {
                padding: 5px 8px;
                font-size: 12px;
                white-space: nowrap;
            }

            .page-header .btn i,
            .page-header .btn img {
                margin-right: 3px;
            }

            .filter-row .col-6 {
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }

            #filter-date,
            .datetimepicker,
            input.datetimepicker {
                width: 95% !important;
                min-width: 90% !important;
                display: block !important;
                box-sizing: border-box !important;
            }

            #filter-date+span,
            .input-group,
            .input-groupicon {
                width: 110% !important;
            }
        }

        /* Search input styling */
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
            top: 7px !important;
        }

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
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

        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
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
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4>Purchases List</h4>
            </div>

            <div class="header-actions d-flex align-items-center gap-2">
                @if (app('hasPermission')(3, 'add'))
                    <a href="{{ route('purchase.add') }}" class="btn btn-sm btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img">
                        New Purchases
                    </a>
                @endif
                {{-- <button id="exportAllChallan" class="btn btn-sm btn-success desk-res">
                    <i class="fas fa-file-excel"></i> Excel
                </button>

                <button id="exportPdf" class="btn btn-sm btn-danger">
                    <i class="fas fa-file-pdf"></i> PDF
                </button> --}}
                <!-- Desktop Export Buttons (visible only on desktop) -->
                <div class="desktop-export-buttons d-flex gap-2">
                    <button id="exportAllChallanDesktop" class="btn btn-sm btn-success">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button id="exportPdfDesktop" class="btn btn-sm btn-danger">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                </div>
            </div>

        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-top mb-3">
                    <div class="row w-100 align-items-center filter-row">
                        <!-- Search -->
                        <div class="col-md-2 col-12 d-flex align-items-center mb-1 mb-md-0">
                            <div class="search-set w-100">
                                <div class="search-path"></div>
                                <div class="search-input d-flex align-items-center">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="search-input" class="form-control" placeholder="Search..."
                                        style="height: 35px">
                                </div>
                            </div>
                        </div>

                        <!-- Month Filter -->
                        <div class="col-md-2 col-6">
                            <!-- Total (only for admin/sub-admin) -->
                            {{-- @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                                <div class="mb-1"
                                    style="color: #1b2850;border: 1px solid #0d1b3e;border-radius: 5px;padding: 4px;font-size: 14px;     font-weight: bold;">
                                    Total: <span style="color: #ff9f43" id="filtered-total">₹0.00</span>
                                </div>
                            @endif --}}

                            @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                                <div class="mb-1"
                                    style="color: #1b2850;border: 1px solid #0d1b3e;border-radius: 5px;padding: 4px;font-size: 14px;font-weight: bold;width:100%;">
                                    Total: <span style="color: #ff9f43" id="filtered-total">₹0.00</span>
                                </div>
                            @endif


                        </div>


                        <!-- Month Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1">
                                <!-- <label for="filter-month" class="form-label">Month</label> -->
                                <select id="filter-month" class="form-control form-control-sm ">
                                    <option value="all">All Months</option>
                                    <option value="01">January</option>
                                    <option value="02">February</option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>
                        </div>

                        <!-- Year Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1">
                                <!-- <label for="filter-year" class="form-label">Year</label> -->
                                <select id="filter-year" class="form-control form-control-sm">
                                    <option value="all">All Year</option>
                                </select>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1" style="width: 100%;">
                                <!-- <div class="form-group mb-0"> -->
                                <!-- <label for="filter-date" class="form-label">Date</label> -->
                                <input type="text" id="filter-date" placeholder="Date"
                                    class="datetimepicker form-control form-control-sm w-100">
                            </div>
                        </div>
                        <div class="col-md-2 col-6">
                            <div class="mb-1">
                                <!-- <div class="form-group mb-0"> -->
                                <!-- <label for="filter-date" class="form-label">Date</label> -->
                                <select id="filter-customer" class="form-control form-control-sm">
                                    <option value="">-- Select Vendor --</option>
                                </select>
                            </div>
                        </div>
                        <!-- Mobile Export Buttons Row (visible only on mobile) -->
                        <div class="col-12 export-buttons-row mobile-export-buttons">
                            <div class="d-flex gap-3 justify-content-between align-items-center w-100">
                                <button id="exportAllChallanMobile" class="btn btn-sm btn-success flex-grow-1">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button id="exportPdfMobile" class="btn btn-sm btn-danger flex-grow-1">
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
                                        <img src="admin/assets/img/icons/search-whites.svg" alt="img">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                {{-- <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div> --}}

                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="order-table" style="max-width: 2000px;">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th class="text-center">Details</th>
                                <th>Date</th>
                                <th>Vendor Name</th>
                                <th>Grand Total</th>
                                <th>Purchase Status</th>
                                <th>Payment Status</th>
                                <th>Return Status</th>
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

                <!-- Mobile Order Cards -->
                <div class="mobile-order-card mt-3" id="mobile-order-container">
                    <!-- JS will populate this -->
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
                            <strong>Remaining Amount:</strong> ₹<span id="remainingAmountDisplay">0.00</span><br>
                            <strong>Return Amount:</strong> ₹<span id="returnAmountDisplay">0.00</span>
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

                        <div class="mb-3 d-none" id="bank_container">
                            <label for="bank_id" class="form-label">Select Bank</label>
                            <select name="bank_id" id="bank_id" class="form-select">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger" id="bankError"></div>
                        </div>

                        <!-- Fully Paid Fields -->
                        <div class="mb-3 d-none" id="fullyPaidFields">
                            <label class="form-label">Cash Amount</label>
                            <input type="text" class="form-control" id="cashAmount" name="cashAmount">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>

                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="purchase_id">
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
        // Global variables
        var purchaseTable;
        let currentPage = 1;
        let lastPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let isLoading = false;
        let currentRequest = null;
        let debounceTimer = null;

        function formatCurrency(amount) {
            return parseFloat(amount || 0).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function parseCurrency(value) {

            if (!value) return 0;

            // remove ₹ symbol and commas
            value = value.toString().replace(/[₹,\s]/g, '');

            let number = parseFloat(value);

            return isNaN(number) ? 0 : number;
        }


        // Helper function to build expandable row content
        function buildPurchaseExpandableRowContent(purchase, currencySymbol, currencyPosition) {
            const amount = formatCurrency(purchase.grand_total || 0);
            const displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            let actionBtns = '';

            // Make Payment button (orange button) - only if there's remaining amount
            if (parseFloat(purchase.remaining_amount || 0) > 0) {
                actionBtns += `<button type="button" class="btn btn-sm btn-primary me-3 make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${purchase.id}"
                    data-amount="${purchase.remaining_amount}"
                    data-method="${purchase.payment_mode || ''}"
                    data-total-amount="${purchase.grand_total || 0}"
                    data-remaining-amount="${purchase.remaining_amount}"
                    style="background-color: #ff9f43; border-color: #ff9f43; color: white;">
                    Make Payment
                </button>`;
            }

            // Edit icon button
            @if (app('hasPermission')(3, 'edit'))
                if (parseFloat(purchase.total_return || 0) === 0) {
                    actionBtns += `<a class="btn-icon-mobile btn-edit" href="/edit-purchase/${purchase.id}" title="Edit Purchase">
                        <i class="fas fa-edit"></i>
                    </a>`;
                }
            @endif

            // History icon button
            actionBtns += `<button class="btn-icon-mobile btn-history open-history" data-id="${purchase.id}" title="Payment History">
                <i class="fas fa-history"></i>
            </button>`;

            // Download icon button
            @if (app('hasPermission')(3, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-download" href="/print-purchase/${purchase.id}" title="Download Invoice">
                    <i class="fas fa-download"></i>
                </a>`;
            @endif

            // Print icon button
            @if (app('hasPermission')(3, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-print" href="javascript:void(0);" onclick="window.open('/purchase/invoice/pdf/' + ${purchase.id});" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>`;
            @endif

            // Delete icon button
            @if (app('hasPermission')(3, 'delete'))
                actionBtns += `<a class="btn-icon-mobile btn-delete delete-order" href="javascript:void(0);" data-id="${purchase.id}" title="Delete">
                    <i class="fas fa-trash"></i>
                </a>`;
            @endif

            return `
                <td colspan="8" class="order-details-content">
                    <div class="order-details-list">
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Date:</span>
                            <span class="order-detail-value-simple">${purchase.invoice_date || purchase.date || purchase.created_at || 'N/A'}</span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Vendor Name:</span>
                            <span class="order-detail-value-simple">${purchase.vendor_name || 'N/A'}</span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Purchase Status:</span>
                            <span class="order-detail-value-simple">
                                <span class="mobile-badge bg-lightgreen">${purchase.purchase_status || purchase.status || 'N/A'}</span>
                            </span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Payment Status:</span>
                            <span class="order-detail-value-simple">
                                ${parseFloat(purchase.extra_paid || 0) > 0 ?
                                    `<span class="mobile-badge bg-lightred">Extra Paid: ${currencySymbol}${formatCurrency(purchase.extra_paid)}</span>` :
                                    `<span class="mobile-badge bg-lightgreen">${purchase.payment_status || 'N/A'}</span>`}
                            </span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Return Status:</span>
                            <span class="order-detail-value-simple">
                                ${parseFloat(purchase.total_return || 0) > 0 ?
                                    `<span class="status-badge status-pending">Returned</span>` :
                                    '<span class="status-badge status-completed">No return</span>'}
                            </span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Grand Total:</span>
                            <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                        </div>
                        <div class="order-detail-row-simple">
                            <span class="order-detail-label-simple">Return Amount:</span>
                            <span class="order-detail-value-simple" style="font-weight: bold; color: #ea5455;">
                                ${currencySymbol}${formatCurrency(purchase.total_return || 0)}
                            </span>
                        </div>
                        ${parseFloat(purchase.remaining_amount || 0) > 0 ? `
                                                            <div class="order-detail-row-simple">
                                                                <span class="order-detail-label-simple">Remaining:</span>
                                                                <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                                                    ${currencySymbol}${formatCurrency(purchase.remaining_amount || 0)}
                                                                </span>
                                                            </div>
                                                            ` : ''}
                        ${parseFloat(purchase.extra_paid || 0) > 0 ? `
                                                            <div class="order-detail-row-simple">
                                                                <span class="order-detail-label-simple">Extra Paid:</span>
                                                                <span class="order-detail-value-simple" style="color: #28c76f; font-weight: bold;">
                                                                    ${currencySymbol}${parseFloat(purchase.extra_paid || 0).toFixed(2)}
                                                                </span>
                                                            </div>
                                                            ` : ''}
                    </div>
                    <div class="mobile-action-buttons-simple">
                        ${actionBtns}
                    </div>
                </td>
            `;
        }

        // Toggle function for table rows - must be global
        window.togglePurchaseRowDetails = function(purchaseId) {
            // Find the button that was clicked
            const btn = $(`.mobile-toggle-btn-table[data-purchase-id="${purchaseId}"]`);
            if (btn.length === 0) {
                // console.error('Toggle button not found for purchase:', purchaseId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.order-details-row[data-purchase-id="${purchaseId}"]`);
            const icon = btn.find('.toggle-icon');

            // If expandable row doesn't exist, create it
            if (detailsRow.length === 0) {
                const purchaseData = window.purchaseDataMap && window.purchaseDataMap[purchaseId];
                if (purchaseData) {
                    detailsRow = $('<tr>')
                        .addClass('order-details-row')
                        .attr('data-purchase-id', purchaseId)
                        .html(buildPurchaseExpandableRowContent(purchaseData, purchaseData.currencySymbol, purchaseData
                            .currencyPosition));
                    row.after(detailsRow);
                } else {
                    // console.error('Purchase data not found for purchase:', purchaseId);
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
        }

        // Function to add expandable rows - must be global
        window.addPurchaseExpandableRows = function(dt) {
            if (!dt) return;

            const currentWidth = $(window).width();
            const isMobileOrTablet = currentWidth <= 1024;

            if (!isMobileOrTablet) {
                // Remove expandable rows on desktop
                $('tr.order-details-row').remove();
                return;
            }

            dt.rows().every(function() {
                const row = this.node();
                const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                if (toggleBtn.length > 0) {
                    const purchaseId = toggleBtn.data('purchase-id');
                    const purchaseData = window.purchaseDataMap && window.purchaseDataMap[purchaseId];
                    if (purchaseData && !$(row).next('tr.order-details-row[data-purchase-id="' + purchaseId +
                            '"]').length) {
                        const expandableRow = $('<tr>')
                            .addClass('order-details-row')
                            .attr('data-purchase-id', purchaseId)
                            .html(buildPurchaseExpandableRowContent(purchaseData, purchaseData.currencySymbol,
                                purchaseData.currencyPosition));
                        $(row).after(expandableRow);
                    }
                }
            });
        };

        // Function to calculate total for visible rows - must be global
        function calculatePurchaseFilteredTotal() {
            if (!purchaseTable) {
                purchaseTable = $('.datanew').DataTable();
            }

            let total = 0;

            // Find the Grand Total column index by header name
            let totalColumnIndex = -1;
            purchaseTable.columns().every(function() {
                const header = $(this.header());
                if (header.text().trim() === 'Grand Total') {
                    totalColumnIndex = this.index();
                    return false; // break
                }
            });

            // If column not found by name, use index 4 as fallback
            if (totalColumnIndex === -1) {
                totalColumnIndex = 4;
            }

            purchaseTable.rows({
                filter: 'applied'
            }).every(function() {
                const row = this.data();
                if (row[totalColumnIndex]) {
                    const amountText = row[totalColumnIndex];
                    const rawAmount = parseFloat(amountText.replace(/[^0-9.-]+/g, '')) || 0;
                    total += rawAmount;
                }
            });

            const currencySymbol = "₹";
            $('#filtered-total').text(`${currencySymbol}${formatCurrency(total.toFixed(2))}`);
        }

        $(document).on('focus',
            '#partialAmount, #cashAmount, #cashOnlineFullAmount, #cashOnlinePartialAmount, #upiOnlinePartialAmount',
            function() {
                let val = parseCurrency($(this).val());
                $(this).val(val > 0 ? val : '');
            });

        // $(document).on('blur',
        //     '#partialAmount, #cashAmount, #cashOnlineFullAmount, #cashOnlinePartialAmount, #upiOnlinePartialAmount',
        //     function() {
        //         let val = parseCurrency($(this).val());
        //         $(this).val(formatCurrency(val));
        //     });

        $(document).ready(function() {
            // Initialize table after data is loaded
            let currentYear = new Date().getFullYear();
            for (let i = 0; i < 4; i++) {
                let year = currentYear - i;
                $("#filter-year").append(`<option value="${year}">${year}</option>`);
            }

            $("#filter-month").select2({
                placeholder: "-- Select Month --",
                allowClear: true,
                width: "100%"
            });
            $("#filter-year").select2({
                placeholder: "-- Year --",
                allowClear: true,
                width: "100%"
            });
            $("#filter-customer").select2({
                placeholder: "-- Select Vendor --",
                allowClear: true,
                width: "100%"
            });

            // ✅ On dropdown change → filter table
            $("#filter-customer").on("change", function() {
                let selectedCustomer = $(this).val();
                if (purchaseTable) {
                    if (selectedCustomer) {
                        purchaseTable.column(3) // column index for vendor name (after Details column)
                            .search("^" + selectedCustomer + "$", true, false) // exact match
                            .draw();
                    } else {
                        purchaseTable.column(3).search("").draw(); // clear filter
                    }
                    setTimeout(() => calculatePurchaseFilteredTotal(), 100);
                }
            });


            // Month short names
            const monthNames = {
                "01": "Jan",
                "02": "Feb",
                "03": "Mar",
                "04": "Apr",
                "05": "May",
                "06": "Jun",
                "07": "Jul",
                "08": "Aug",
                "09": "Sep",
                "10": "Oct",
                "11": "Nov",
                "12": "Dec"
            };

            // Apply month/year filter whenever dropdown changes
            $("#filter-month, #filter-year").on("change", function() {
                applyMonthYearFilter();
                $(this).trigger("change.select2");
            });

            // function applyMonthYearFilter() {
            //     const selectedMonth = $("#filter-month").val();
            //     const selectedYear = $("#filter-year").val();

            //     let regex = "";
            //     if (selectedMonth && selectedYear) {
            //         regex = `${monthNames[selectedMonth]}.*${selectedYear}`;
            //     } else if (selectedMonth) {
            //         regex = `${monthNames[selectedMonth]}`;
            //     } else if (selectedYear) {
            //         regex = `${selectedYear}`;
            //     }

            //     console.log("Applied Filter Regex:", regex);

            //     // Use the DataTable instance
            //     if (purchaseTable) {
            //         purchaseTable.column(2).search(regex, true, false).draw(); // Date column is now index 2
            //         setTimeout(() => calculatePurchaseFilteredTotal(), 100);
            //     }
            // }
            function applyMonthYearFilter() {

                const selectedMonth = $("#filter-month").val();
                const selectedYear = $("#filter-year").val();

                let regex = "";

                // If both are ALL → show all
                if (selectedMonth === "all" && selectedYear === "all") {
                    regex = "";
                }

                // Only year selected
                else if (selectedMonth === "all" && selectedYear !== "all") {
                    regex = selectedYear;
                }

                // Only month selected
                else if (selectedMonth !== "all" && selectedYear === "all") {
                    regex = `${monthNames[selectedMonth]}`;
                }

                // Both specific
                else if (selectedMonth && selectedYear) {
                    regex = `${monthNames[selectedMonth]}.*${selectedYear}`;
                }

                if (purchaseTable) {
                    purchaseTable.column(2).search(regex, true, false).draw();
                    setTimeout(() => calculatePurchaseFilteredTotal(), 100);
                }
            }

            // alert("Purchase List Page Loaded");
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // console.log("Selected Sub Admin ID:", selectedSubAdminId);




            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let returnAmount = $(this).data('return-amount') || 0;
                let method = $(this).data('method') || '';

                // ✅ Fill modal hidden inputs + text spans
                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(parseFloat(totalAmount).toFixed(2));
                $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toFixed(2));
                $('#returnAmountDisplay').text(parseFloat(returnAmount).toFixed(2));
                $('#remainingAmountHidden').val(remainingAmount);
                $('#paymentMethodHidden').val(method);

                // ✅ Reset payment method dropdown to default
                $('#paymentMethodSelect').val('');

                // ✅ Hide history box initially
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // ✅ Bind View History button
                $('#viewHistoryBtn').off('click').on('click', function() {
                    $.ajax({
                        url: '/api/purchase/payment-history/' + jobCardId,
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
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

                // ✅ Close history
                $('#closeHistoryBtn').off('click').on('click', function() {
                    $('#paymentHistoryBox').addClass('d-none');
                });
            });

            $('#paymentMethodSelect').on('change', function() {
                let method = $(this).val();

                // Hide all optional sections first
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields, #bank_container')
                    .addClass('d-none');
                $('#bank_id').val('');

                if (method === 'cash') {
                    $('#paidTypeDiv').removeClass('d-none'); // Show paid type options

                } else if (method === 'online') {
                    $('#onlineTypeDiv').removeClass('d-none'); // Show online type dropdown
                    $('#bank_container').removeClass('d-none');

                } else if (method === 'cash_online') {
                    $('#cashOnlineTypeDiv').removeClass('d-none'); // Show Cash + Online type dropdown
                    $('#bank_container').removeClass('d-none');
                }
            });

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
                    $('#pendingAmount').val(parseFloat(remaining).toFixed(2));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseCurrency($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(parseFloat(remaining).toFixed(2)).prop('readonly', true);

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
                    $('#pendingAmount').val(parseFloat(remaining).toFixed(2));

                    // Clear and disable UPI field
                    $('#upiAmountInput').val('').prop('readonly', false).prop('disabled', true);

                    // Live pending update
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'online_fully') {
                    // Show fully online section
                    $('#upiAmountDiv').removeClass('d-none');
                    $('#upiAmountDiv input').prop('disabled', false);

                    // Fill with remaining and lock editing
                    $('#upiAmountInput').val(parseFloat(remaining)).prop('readonly', true);

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
                $('#fullyCashOnlineFields input, #partialCashOnlineFields input').prop('disabled', true);

                if (type === 'cash_online_fully') {
                    // Show fully section
                    $('#fullyCashOnlineFields').removeClass('d-none');
                    $('#fullyCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlineFullAmount').val('');
                    $('#upiOnlineFullAmount').val(parseFloat(remaining).toFixed(2));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = parseCurrency($(this).val()) || 0;
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
                    $('#remainingCashOnlineAmount').val(parseFloat(remaining).toFixed(2));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = parseCurrency($(this).val()) || 0;
                        let online = parseCurrency($('#upiOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = parseCurrency($(this).val()) || 0;
                        let cash = parseCurrency($('#cashOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
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
                let pendingAmount = $('#pendingAmount').val();

                // console.log(pendingAmount);
                // console.log(onlineType);

                // return false;
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
                        let partialAmount = parseCurrency($('#partialAmount').val()) || 0;
                        let remainingAmount = parseCurrency($('#remainingAmountHidden').val()) || 0;

                        // console.log("Cash partially selected, entered amount:", partialAmount, "Remaining:",
                        //     remainingAmount);

                        if (!partialAmount || isNaN(partialAmount) || partialAmount <= 0) {
                            isValid = false;
                            $('#partialAmountError').text("Enter a valid positive partial cash amount.");
                            return false;
                        }

                        if (partialAmount > remainingAmount) {
                            isValid = false;
                            $('#partialAmountError').text(
                                "Partial cash amount cannot exceed remaining amount (" + remainingAmount
                                .toFixed(2) + ")."
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
                        let cashAmount = parseCurrency($('#cashAmount').val()) || 0;
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

                    if (!$("#bank_id").val()) {
                        isValid = false;
                        $("#bankError").text("Please select a bank");
                        return false;
                    }

                    let onlineAmount = parseCurrency($('#partialAmount').val()) || parseCurrency($(
                        '#upiAmountInput').val()) || 0;
                    let remainingAmount = parseCurrency($('#remainingAmountHidden').val()) || 0;

                    // console.log("Online amount entered:", onlineAmount, "Remaining:", remainingAmount);

                    // ✅ Check 1: Must be a valid positive number
                    if (!onlineAmount || isNaN(onlineAmount) || onlineAmount <= 0) {
                        isValid = false;
                        if (onlineType === 'online_partially') {
                            // console.log("Validation failed: Invalid partial online amount");
                            $('#partialAmountError').text("Enter a valid positive online partial amount.");
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
                            "Partial online amount cannot exceed remaining amount (" + remainingAmount
                            .toFixed(2) + ")."
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

                    if (!$("#bank_id").val()) {
                        isValid = false;
                        $("#bankError").text("Please select a bank");
                        return false;
                    }

                    if (cashOnlineType === 'cash_online_fully') {
                        let cashAmt = parseCurrency($('#cashOnlineFullAmount').val()) || 0;
                        let onlineAmt = parseCurrency($('#upiOnlineFullAmount').val()) || 0;
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
                        let cashAmt = parseCurrency($('#cashOnlinePartialAmount').val()) || 0;
                        let onlineAmt = parseCurrency($('#upiOnlinePartialAmount').val()) || 0;

                        // Clean pending amount
                        let rawPending = $('#remainingCashOnlineAmount').val() || "0";
                        rawPending = rawPending.replace(/[₹,]/g, '').trim();
                        let pendingAmt = parseCurrency(rawPending) || 0;

                        // console.log("Cash+Online partially amounts:", cashAmt, onlineAmt, "Pending:",
                        //     pendingAmt);

                        // ✅ Check for invalid or negative input
                        if ((cashAmt <= 0 && onlineAmt <= 0)) {
                            isValid = false;
                            // console.log("Validation failed: Invalid partially cash + online amounts");
                            $('#cashOnlinePartialAmountError').text("Enter at least one valid amount.");
                            $('#upiOnlinePartialAmountError').text("Enter at least one valid amount.");
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
                                "Total payment cannot exceed pending amount (" + pendingAmt.toFixed(2) +
                                ").");
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt.toFixed(2) +
                                ").");
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
                    url: "{{ route('make-payment.purchase') }}",
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

            // Reset Make Payment Modal when closed
            $('#makePaymentModal').on('hidden.bs.modal', function() {

                // Reset form
                $('#makePaymentForm')[0].reset();

                // Hide all dynamic sections
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields, #bank_container')
                    .addClass('d-none');

                // Clear error messages
                $('.text-danger').text('');

                // Clear payment history
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // Clear input values
                $('#cashOnlineFullAmount, #upiOnlineFullAmount, #cashOnlinePartialAmount, #upiOnlinePartialAmount, #remainingCashOnlineAmount')
                    .val('');
                $('#partialAmount, #pendingAmount, #cashAmount, #upiAmountInput').val('');

                // Reset dropdowns
                $('#paymentMethodSelect').val('').trigger('change');
                $('#cashOnlineTypeSelect').val('');
                $('#onlineTypeSelect').val('');
                $('#paidTypeSelect').val('');
                $('#bank_id').val('');

                // Reset display values
                $('#emiTotal').text('0.00');
                $('#remainingAmountDisplay').text('0.00');
                $('#returnAmountDisplay').text('0.00');

                // Reset hidden fields
                $('#paymentJobCardId').val('');
                $('#remainingAmountHidden').val('');
                $('#paymentMethodHidden').val('');
            });

            $(function() {

                function formatDate(dateStr) {
                    if (!dateStr) return 'N/A';
                    const d = new Date(dateStr);
                    const day = String(d.getDate()).padStart(2, '0');
                    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct",
                        "Nov", "Dec"
                    ];
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
                    const isPositive = positiveStatuses.includes(String(text).toLowerCase());
                    const cls = isPositive ? okClass : warnClass;
                    return `<span class="badges ${cls}" style="text-transform:capitalize;">${text}</span>`;
                }

                function actionLinks(o) {


                    let buttons = '';

                    // Show "Make Payment" button only if remaining amount > 0
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
                                                                                    data-remaining-amount="${o.remaining_amount}"
                                                                                    data-return-amount="${o.total_return || 0}">
                                                                                    Make Payment
                                                                                </button>
                                                                            `;
                    }
                    // console.log(o);

                    // Other actions (always visible)
                    buttons += `
                                    <button class="btn open-history" data-id="${o.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>
                                                                            <a class="me-3" href="/print-purchase/${o.id}">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" viewBox="0 0 32 32"><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" fill="#092C4C"></path></svg>
                                                                            </a>`;

                    @if (app('hasPermission')(3, 'edit'))
                        if (parseFloat(o.total_return || 0) === 0) {
                            buttons += `
                        <a class="me-3" href="/edit-purchase/${o.id}">
                                <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"></path>
                                    </svg>
                            </a>`;
                        }
                    @endif


                    buttons += `
                                                                            @if (app('hasPermission')(3, 'view'))

                                                                            <a class="me-3" href="javascript:void(0);" onclick="window.open('/purchase/invoice/pdf/' + ${o.id});">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                                                                                    <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
                                                                                </svg>
                                                                            </a>
                                                                            @endif

                                                                            @if (app('hasPermission')(3, 'delete'))

                                                                            <a class="me-3 confirm-text delete-order" data-id="${o.id}" href="javascript:void(0);">
                                                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                                                    <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                                                                </svg>
                                                                            </a>
                                                                            @endif
                                                                        `;

                    return buttons;
                }

                // function loadPurchases() {
                //     const rawDate = $('#filter-date').val();
                //     const [day, month, year] = rawDate ? rawDate.split('-') : [null, null, null];
                //     const formattedDate = rawDate ? `${year}-${month}-${day}` : '';

                //     const filters = {
                //         date: formattedDate,
                //         selectedSubAdminId: selectedSubAdminId || null,
                //         vendor_name: $('#filter_inputs input[placeholder="Enter Name"]').val().trim(),
                //         reference_no: $('#filter_inputs input[placeholder="Enter Reference No"]').val()
                //             .trim(),
                //         status: $('#filter_inputs select').val()
                //     };

                //     $.ajax({
                //         url: "/api/purchase_list",
                //         type: "GET",
                //         dataType: "json",
                //         data: filters,
                //         headers: {
                //             "Authorization": "Bearer " + authToken
                //         },
                //         success: res => {
                //             const rows = [];

                //             if (res.success && Array.isArray(res.data)) {
                //                 const currencySymbol = res.currency_symbol || '₹';
                //                 const currencyPosition = res.currency_position || 'left';

                //                 // Store purchase data for expandable rows
                //                 if (!window.purchaseDataMap) {
                //                     window.purchaseDataMap = {};
                //                 }

                //                 res.data.forEach(o => {
                //                     const amount = formatCurrency(o.grand_total || 0);
                //                     const formattedAmount = currencyPosition ===
                //                         'right' ?
                //                         `${amount}${currencySymbol}` :
                //                         `${currencySymbol}${amount}`;

                //                     // Store purchase data for expandable row
                //                     const purchaseData = {
                //                         ...o,
                //                         invoice_date: formatDate(o.invoice_date || o
                //                             .date || o.created_at),
                //                         displayAmount: formattedAmount,
                //                         currencySymbol: currencySymbol,
                //                         currencyPosition: currencyPosition
                //                     };
                //                     window.purchaseDataMap[o.id] = purchaseData;

                //                     rows.push([
                //                         `<a href="/purchase-view/${o.id}" style="text-decoration: none;">${o.invoice_number || ''}</a>`,
                //                         `<button class="mobile-toggle-btn-table" onclick="togglePurchaseRowDetails('${o.id}')" data-purchase-id="${o.id}">
            //                             <span class="toggle-icon">+</span>
            //                         </button>`,
                //                         formatDate(o.invoice_date || o.date || o
                //                             .created_at),
                //                         `<span style="text-transform:capitalize;">${o.vendor_name || ''}</span>`,
                //                         formattedAmount,
                //                         badge((o.purchase_status || o.status ||
                //                                 ''), 'bg-lightgreen',
                //                             'bg-lightred'),
                //                         (parseFloat(o.extra_paid || 0) > 0) ?
                //                         `<span class="badges bg-lightred" style="text-transform:capitalize;">Extra Paid: ${currencySymbol}${formatCurrency(o.extra_paid)}</span>` :
                //                         badge(o.payment_status, 'bg-lightgreen',
                //                             'bg-lightred'),
                //                         parseFloat(o.total_return || 0) > 0 ?
                //                         `<span class="status-badge status-pending">Returned</span>` :
                //                         `<span class="status-badge status-completed">No return</span>`,
                //                         actionLinks(o)
                //                     ]);
                //                 });

                //                 const $tbl = $('#order-table');

                //                 function updateTotal(dt) {
                //                     setTimeout(() => calculatePurchaseFilteredTotal(), 100);
                //                 }

                //                 if ($.fn.DataTable.isDataTable($tbl)) {
                //                     const dt = $tbl.DataTable();
                //                     dt.clear().rows.add(rows).draw();
                //                     dt.off('draw').on('draw', () => {
                //                         updateTotal(dt);
                //                         if (window.addPurchaseExpandableRows) {
                //                             window.addPurchaseExpandableRows(dt);
                //                         }
                //                     });
                //                     updateTotal(dt);
                //                     setTimeout(() => {
                //                         if (window.addPurchaseExpandableRows) {
                //                             window.addPurchaseExpandableRows(dt);
                //                         }
                //                     }, 100);
                //                 } else {
                //                     const dt = $tbl.DataTable({
                //                         data: rows,
                //                         responsive: true,
                //                         autoWidth: false,
                //                         pageLength: 10,
                //                         ordering: true,
                //                         searching: true,
                //                         language: {
                //                             searchPlaceholder: "Search purchases...",
                //                             search: ""
                //                         }
                //                     });
                //                     dt.on('draw', () => {
                //                         updateTotal(dt);
                //                         if (window.addPurchaseExpandableRows) {
                //                             window.addPurchaseExpandableRows(dt);
                //                         }
                //                     });
                //                     updateTotal(dt);
                //                     setTimeout(() => {
                //                         if (window.addPurchaseExpandableRows) {
                //                             window.addPurchaseExpandableRows(dt);
                //                         }
                //                     }, 100);
                //                 }

                //                 // Update global purchaseTable reference
                //                 purchaseTable = $tbl.DataTable();

                //                 // Populate customer dropdown after first draw
                //                 purchaseTable.one('draw', function() {
                //                     let customers = new Set();
                //                     purchaseTable.column(3, {
                //                         search: 'applied'
                //                     }).data().each(function(d) {
                //                         let name = $('<div>').html(d).text()
                //                             .trim();
                //                         customers.add(name);
                //                     });

                //                     let $filter = $("#filter-customer");
                //                     $filter.empty().append(
                //                         '<option value="">-- Select Vendor --</option>'
                //                     );
                //                     customers.forEach(function(name) {
                //                         $filter.append('<option value="' +
                //                             name + '">' + name + '</option>'
                //                         );
                //                     });
                //                     $filter.trigger('change');
                //                 });

                //                 // Scroll sync
                //                 //    const topScroll = document.querySelector('.table-scroll-top');
                //                 //    const tableResponsive = document.querySelector(
                //                 //       '.table-responsive');
                //                 //    const table = document.getElementById('order-table');
                //                 //     topScroll.querySelector('div').style.width = table.scrollWidth +
                //                 //       'px';
                //                 //
                //                 //  topScroll.onscroll = () => {
                //                 //       tableResponsive.scrollLeft = topScroll.scrollLeft;
                //                 //    };
                //                 //    tableResponsive.onscroll = () => {
                //                 //      topScroll.scrollLeft = tableResponsive.scrollLeft;
                //                 //  };
                //             }
                //         },
                //         error: xhr => console.error('purchase_list error:', xhr.responseText)
                //     });
                // }// Pagination state
                // let currentPage = 1;
                // let lastPage = 1;
                // let perPage = 10;
                // let searchQuery = '';

                function loadPurchases(page = 1) {
                    // ✅ Prevent concurrent requests
                    if (isLoading) {
                        if (currentRequest) {
                            currentRequest.abort();
                        }
                    }

                    isLoading = true;

                    const rawDate = $('#filter-date').val();
                    let formattedDate = '';
                    if (rawDate) {
                        const parts = rawDate.split(/[-\/]/);
                        if (parts.length === 3) {
                            if (parts[0].length <= 2 && parts[2].length === 4) {
                                // dd-mm-yyyy → yyyy-mm-dd
                                formattedDate =
                                    `${parts[2]}-${parts[1].padStart(2,'0')}-${parts[0].padStart(2,'0')}`;
                            } else {
                                formattedDate = rawDate;
                            }
                        }
                    }
                    const selectedMonth = $('#filter-month').val();
                    const selectedYear = $('#filter-year').val();

                    const filters = {
                        page: page,
                        per_page: perPage,
                        date: formattedDate,
                        month: (selectedMonth && selectedMonth !== 'all') ? selectedMonth : '',
                        year: (selectedYear && selectedYear !== 'all') ? selectedYear : '',
                        selectedSubAdminId: selectedSubAdminId || null,
                        search: searchQuery || ''
                    };

                    currentRequest = $.ajax({
                        url: "/api/purchase_list",
                        type: "GET",
                        dataType: "json",
                        data: filters,
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(res) {
                            isLoading = false;
                            currentRequest = null;

                            const rows = [];

                            if (res.success && Array.isArray(res.data)) {
                                const currencySymbol = res.currency_symbol || '₹';
                                const currencyPosition = res.currency_position || 'left';
                                const pagination = res.pagination;

                                if (pagination) {
                                    currentPage = pagination.current_page;
                                    lastPage = pagination.last_page;
                                    updatePaginationUI(pagination);
                                }

                                if (!window.purchaseDataMap) {
                                    window.purchaseDataMap = {};
                                }

                                res.data.forEach(function(o) {
                                    const amount = formatCurrency(o.grand_total || 0);
                                    const formattedAmount = currencyPosition ===
                                        'right' ?
                                        `${amount}${currencySymbol}` :
                                        `${currencySymbol}${amount}`;

                                    const purchaseData = {
                                        ...o,
                                        invoice_date: formatDate(o.invoice_date || o
                                            .date || o.created_at),
                                        displayAmount: formattedAmount,
                                        currencySymbol: currencySymbol,
                                        currencyPosition: currencyPosition
                                    };
                                    window.purchaseDataMap[o.id] = purchaseData;

                                    rows.push([
                                        `<a href="/purchase-view/${o.id}" style="text-decoration: none;">${o.invoice_number || ''}</a>`,
                                        `<button class="mobile-toggle-btn-table" onclick="togglePurchaseRowDetails('${o.id}')" data-purchase-id="${o.id}">
                            <span class="toggle-icon">+</span>
                        </button>`,
                                        formatDate(o.invoice_date || o.date || o
                                            .created_at),
                                        `<span style="text-transform:capitalize;">${o.vendor_name || ''}</span>`,
                                        formattedAmount,
                                        badge((o.purchase_status || o.status ||
                                                ''), 'bg-lightgreen',
                                            'bg-lightred'),
                                        (parseFloat(o.extra_paid || 0) > 0) ?
                                        `<span class="badges bg-lightred" style="text-transform:capitalize;">Extra Paid: ${currencySymbol}${formatCurrency(o.extra_paid)}</span>` :
                                        badge(o.payment_status, 'bg-lightgreen',
                                            'bg-lightred'),
                                        parseFloat(o.total_return || 0) > 0 ?
                                        `<span class="status-badge status-pending">Returned</span>` :
                                        `<span class="status-badge status-completed">No return</span>`,
                                        actionLinks(o)
                                    ]);
                                });

                                const $tbl = $('#order-table');

                                if ($.fn.DataTable.isDataTable($tbl)) {
                                    const dt = $tbl.DataTable();
                                    dt.clear().rows.add(rows).draw();
                                } else {
                                    const dt = $tbl.DataTable({
                                        data: rows,
                                        responsive: true,
                                        autoWidth: false,
                                        // ✅ Disable built-in pagination/search since we handle it server-side
                                        paging: false,
                                        ordering: true,
                                        searching: false,
                                        info: false,
                                        language: {
                                            searchPlaceholder: "Search purchases...",
                                            search: ""
                                        }
                                    });
                                    dt.on('draw', function() {
                                        setTimeout(function() {
                                            calculatePurchaseFilteredTotal();
                                        }, 100);
                                        if (window.addPurchaseExpandableRows) {
                                            window.addPurchaseExpandableRows(dt);
                                        }
                                    });
                                }

                                purchaseTable = $tbl.DataTable();
                                setTimeout(function() {
                                    calculatePurchaseFilteredTotal();
                                }, 100);

                                // Populate vendor dropdown from current page results
                                let $filter = $("#filter-customer");
                                let currentVal = $filter.val();
                                $filter.empty().append(
                                    '<option value="">-- Select Vendor --</option>');
                                let vendors = new Set();
                                res.data.forEach(function(o) {
                                    if (o.vendor_name) vendors.add(o.vendor_name);
                                });
                                vendors.forEach(function(name) {
                                    $filter.append('<option value="' + name + '">' +
                                        name + '</option>');
                                });
                                if (currentVal) $filter.val(currentVal);
                                $filter.trigger('change.select2');
                            }
                        },
                        error: function(xhr) {
                            isLoading = false;
                            currentRequest = null;
                            if (xhr.statusText !== 'abort') {
                                console.error('purchase_list error:', xhr.responseText);
                            }
                        }
                    });
                }

                // ============================================================
                // 3. REPLACE updatePaginationUI function:
                // ============================================================
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

                    if (endPage - startPage < 4) {
                        startPage = Math.max(1, endPage - 4);
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        paginationHtml += `
            <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
            </li>
        `;
                    }

                    $('#pagination-numbers').html(paginationHtml);
                    $('.pagination-controls').show();
                }

                // ============================================================
                // 4. REPLACE all event bindings (put these ONCE inside $(document).ready):
                // ============================================================

                // ✅ Pagination click - single binding with .off() first
                $(document).off('click', '#pagination-numbers .page-link')
                    .on('click', '#pagination-numbers .page-link', function(e) {
                        e.preventDefault();
                        let page = parseInt($(this).data('page'));
                        if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                            loadPurchases(page);
                        }
                    });

                // ✅ Per-page selector
                $('#per-page-select').off('change').on('change', function() {
                    perPage = parseInt($(this).val());
                    loadPurchases(1);
                });

                // ✅ Search input with debounce (500ms) - ONLY fire after user stops typing
                $('#search-input').off('keyup input').on('keyup input', function() {
                    clearTimeout(debounceTimer);
                    const val = $(this).val();
                    debounceTimer = setTimeout(function() {
                        if (val !== searchQuery) {
                            searchQuery = val;
                            loadPurchases(1);
                        }
                    }, 500); // ✅ Wait 500ms after user stops typing
                });

                // ✅ Month/Year filter - single binding
                $('#filter-month, #filter-year').off('change.purchase').on('change.purchase', function() {
                    // Clear date filter when month/year is used
                    loadPurchases(1);
                });

                // ✅ Date filter - use change event, not dp.change (to avoid double firing)
                $('#filter-date').off('change.purchase').on('change.purchase', function() {
                    // Clear month/year when date is used
                    loadPurchases(1);
                });

                // ✅ Vendor/Customer filter
                $('#filter-customer').off('change.purchase').on('change.purchase', function() {
                    loadPurchases(1);
                });

                // ✅ Filter button
                $('.btn-filters').off('click.purchase').on('click.purchase', function(e) {
                    e.preventDefault();
                    loadPurchases(1);
                });

                // ✅ Initial load - called ONCE
                loadPurchases(1);


                $('#filter-date').off('change.purchase dp.change').on('change.purchase dp.change',
                    function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(function() {
                            loadPurchases(1);
                        }, 300);
                    });
            });


            // Handle delete action
            $(document).on("click", ".delete-order", function() {
                let orderId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43", // Orange confirm button
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/api/purchase_delete",
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
                                        location
                                            .reload(); // ✅ Reload page after user clicks OK
                                    });
                                } else {
                                    // ❌ Show backend error if success = false
                                    Swal.fire({
                                        title: "Error!",
                                        text: response.message,
                                        icon: "error",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            },
                            error: function(xhr) {
                                // Try to show backend message from response JSON
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

                                // console.error(xhr.responseText); // For debugging
                            }
                        });
                    }
                });
            });

            // Global history modal open
            $(document).on('click', '.open-history', function() {
                const jobCardId = $(this).data('id');
                $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
                $.ajax({
                    url: '/api/purchase/payment-history/' + jobCardId,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {

                        const history = response.data || [];
                        const summary = response.summary || {};
                        let html = '';

                        if (history.length === 0) {
                            html = `<li class="list-group-item">No payment history found.</li>`;
                        } else {
                            html = history.map(p => `
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>${p.payment_date || p.created_at}</span>
                <span>
                    <strong>₹${formatCurrency(p.payment_amount)}</strong>
                    (${p.payment_method || '-'})
                </span>
            </li>
        `).join('');
                        }

                        // 🔹 Summary section (same as sales)
                        html += `
        <li class="list-group-item mt-2 bg-light">
            <strong>Purchase Total:</strong>
            ₹${formatCurrency(summary.order_total)}
        </li>

        <li class="list-group-item bg-light">
            <strong>Total Paid:</strong>
            ₹${formatCurrency(summary.total_paid)}
        </li>

        <li class="list-group-item bg-light">
            <strong>Total Return:</strong>
            ₹${formatCurrency(summary.total_return)}
        </li>
    `;

                        if (parseFloat(summary.extra_paid) > 0) {
                            html += `
            <li class="list-group-item bg-warning">
                <strong>Extra Paid:</strong>
                ₹${formatCurrency(summary.extra_paid)}
                <span class="text-danger">(Advance / Refund)</span>
            </li>
        `;
                        } else {
                            html += `
            <li class="list-group-item bg-light">
                <strong>Remaining:</strong>
                ₹${formatCurrency(summary.remaining)}
            </li>
        `;
                        }

                        $('#globalPaymentHistoryList').html(html);

                        new bootstrap.Modal(
                            document.getElementById('paymentHistoryModal')
                        ).show();
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

            // Function to force CSS media query recalculation
            function forceCSSRecalculation() {
                // Create a temporary element to force reflow
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                // Force reflow on viewport
                void window.innerWidth;
                void window.innerHeight;

                // Force reflow on document
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            // Resize handler for responsive behavior
            let resizeTimer;
            let lastWidth = $(window).width();

            function handlePurchaseResize() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();

                    // Always process resize to ensure CSS updates
                    lastWidth = currentWidth;

                    // Force CSS media query recalculation first
                    forceCSSRecalculation();

                    // Force CSS recalculation by triggering multiple reflows
                    const table = document.getElementById('order-table');
                    const tableResponsive = document.querySelector('.table-responsive');
                    const tableTop = document.querySelector('.table-top');
                    const cardBody = document.querySelector('.card-body');
                    const card = document.querySelector('.card');

                    // Method 1: Force reflow on all key elements
                    [table, tableResponsive, tableTop, cardBody, card].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            // Force style recalculation
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Method 2: Force CSS media query recalculation
                    if (document.body) {
                        const originalDisplay = document.body.style.display;
                        document.body.style.display = 'none';
                        void document.body.offsetHeight;
                        document.body.style.display = originalDisplay;
                    }

                    // Method 3: Force table and DataTables recalculation
                    if (purchaseTable && table) {
                        // Remove all existing expandable rows first
                        $('tr.order-details-row').remove();

                        // Force DataTables to recalculate
                        try {
                            // Multiple adjustments to ensure columns update
                            purchaseTable.columns.adjust();
                            void table.offsetHeight;

                            purchaseTable.draw(false);
                            void table.offsetHeight;

                            // Additional adjustment after draw
                            setTimeout(function() {
                                purchaseTable.columns.adjust();
                                purchaseTable.draw(false);

                                // Force one more reflow
                                void table.offsetHeight;

                                // Re-add expandable rows if needed
                                setTimeout(function() {
                                    if (window.addPurchaseExpandableRows) {
                                        window.addPurchaseExpandableRows(purchaseTable);
                                    }
                                    calculatePurchaseFilteredTotal();

                                    // Final CSS recalculation
                                    forceCSSRecalculation();
                                    void table.offsetHeight;
                                }, 100);
                            }, 100);
                        } catch (e) {
                            // console.error('DataTables adjustment error:', e);
                            // Fallback: just redraw
                            purchaseTable.draw(false);
                            setTimeout(function() {
                                if (window.addPurchaseExpandableRows) {
                                    window.addPurchaseExpandableRows(purchaseTable);
                                }
                                calculatePurchaseFilteredTotal();
                                forceCSSRecalculation();
                            }, 150);
                        }
                    } else {
                        // Even if no table, force CSS recalculation
                        forceCSSRecalculation();
                    }
                }, 50);
            }

            // Window resize handler with throttling - multiple listeners for reliability
            $(window).off('resize.purchase').on('resize.purchase', handlePurchaseResize);

            // Also add native window resize listener (remove old one if exists, then add new)
            if (window.purchaseResizeHandler) {
                window.removeEventListener('resize', window.purchaseResizeHandler);
            }
            window.purchaseResizeHandler = handlePurchaseResize;
            window.addEventListener('resize', window.purchaseResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.purchase').on('orientationchange.purchase', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 300);
            });

            // Also add native orientation change listener
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 500);
            });

            // MatchMedia listeners for all breakpoint changes
            const queries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            queries.forEach(function(query) {
                // Modern browsers
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handlePurchaseResize, 100);
                    });
                }
                // Legacy browsers
                else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handlePurchaseResize, 100);
                    });
                }
            });

            // Initial width set
            lastWidth = $(window).width();

            // Call on initial load to ensure correct state
            $(window).on('load', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handlePurchaseResize();
                }, 500);
            });

            // Also call after a short delay to ensure DOM is ready
            setTimeout(function() {
                if (purchaseTable) {
                    handlePurchaseResize();
                }
            }, 1000);

            // Make handler globally accessible for debugging
            window.handlePurchaseResize = handlePurchaseResize;

        });
        // const topScroll = document.querySelector('.table-scroll-top');
        // const tableResponsive = document.querySelector('.table-responsive');
        // const table = document.getElementById('order-table');

        // // Set top scrollbar inner div width to match table scroll width
        // function updateTopScrollbarWidth() {
        //     const topInnerDiv = topScroll.querySelector('div');
        //     topInnerDiv.style.width = table.scrollWidth + 'px';
        // }

        // // Update on load and window resize
        // window.addEventListener('load', updateTopScrollbarWidth);
        // window.addEventListener('resize', updateTopScrollbarWidth);

        // // Sync scrolling
        // topScroll.addEventListener('scroll', function() {
        //     tableResponsive.scrollLeft = topScroll.scrollLeft;
        // });
        // tableResponsive.addEventListener('scroll', function() {
        //     topScroll.scrollLeft = tableResponsive.scrollLeft;
        // });
        // $('#exportAllChallan').click(function() {
        //     let selectedYear = $('#filter-year').val() || '';
        //     let selectedMonth = $('#filter-month').val() || '';
        //     let selectedDate = $('#filter-date').val() || '';
        //     let selectedVendorId = $('#filter-customer').val() || '';
        //     const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        //     let authToken = localStorage.getItem("authToken");

        //     let url =
        //         `/api/export-purchase?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}`;

        //     $.ajax({
        //         url: url,
        //         method: "GET",
        //         headers: {
        //             "Authorization": "Bearer " + authToken
        //         },
        //         success: function(response) {
        //             if (response.status && response.file_url) {
        //                 // Trigger download
        //                 const link = document.createElement('a');
        //                 link.href = response.file_url;
        //                 link.download = response.file_name; // optional
        //                 document.body.appendChild(link);
        //                 link.click();
        //                 document.body.removeChild(link);
        //             } else {
        //                 Swal.fire({
        //                     icon: "warning",
        //                     title: "No Data Found",
        //                     text: response.message ||
        //                         "No purchase data available for selected filters."
        //                 });
        //             }
        //         },
        //         error: function(xhr) {
        //             // console.error("Export failed:", xhr.responseText);
        //             alert("Export failed. Please try again.");
        //         }
        //     });
        // });

        // $('#exportPdf').click(function() {
        //     let selectedYear = $('#filter-year').val() || '';
        //     let selectedMonth = $('#filter-month').val() || '';
        //     let selectedDate = $('#filter-date').val() || '';
        //     let selectedVendorId = $('#filter-customer').val() || '';
        //     const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        //     let authToken = localStorage.getItem("authToken");

        //     $.ajax({
        //         url: `/api/export-purchase-pdf`,
        //         method: "GET",
        //         headers: {
        //             "Authorization": "Bearer " + authToken
        //         },
        //         data: {
        //             year: selectedYear,
        //             month: selectedMonth,
        //             date: selectedDate,
        //             customer_id: selectedVendorId,
        //             selectedSubAdminId: selectedSubAdminId
        //         },
        //         success: function(response) {
        //             if (response.status && response.file_url) {
        //                 // Download the PDF
        //                 let link = document.createElement('a');
        //                 link.href = response.file_url;
        //                 link.download = response.file_name || 'Purchases.pdf';
        //                 document.body.appendChild(link);
        //                 link.click();
        //                 document.body.removeChild(link);
        //             } else {
        //                 Swal.fire({
        //                     icon: "warning",
        //                     title: "No Data Found",
        //                     text: response.message ||
        //                         "No purchase data available for selected filters."
        //                 });
        //             }
        //         },
        //         error: function(xhr) {
        //             // console.error(xhr);
        //             alert("Failed to generate PDF. Please try again.");
        //         }
        //     });
        // });
        $(document).ready(function() {
            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $downloadButtons = $(
                "#exportAllChallanDesktop, #exportAllChallanMobile, #exportPdfDesktop, #exportPdfMobile"
            );

            function toggleDownloadLoader(isLoading, message) {
                if (isLoading) {
                    $downloadLoaderText.text(message || "Generating report...");
                    $downloadLoader.removeClass("d-none");
                    $downloadButtons.prop("disabled", true).addClass("disabled").attr("aria-disabled", "true");
                } else {
                    $downloadLoader.addClass("d-none");
                    $downloadButtons.prop("disabled", false).removeClass("disabled").removeAttr(
                        "aria-disabled");
                }
            }

            // Function to handle Excel export
            function handleExcelExport() {
                let selectedYear = $('#filter-year').val() || '';
                let selectedMonth = $('#filter-month').val() || '';
                let selectedDate = $('#filter-date').val() || '';
                let selectedVendorId = $('#filter-customer').val() || '';
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                let authToken = localStorage.getItem("authToken");

                let url =
                    `/api/export-purchase?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}`;

                $.ajax({
                    url: url,
                    method: "GET",
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating Excel...");
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status && response.file_url) {
                            const link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "No Data Found",
                                text: response.message ||
                                    "No purchase data available for selected filters."
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Export Failed",
                            text: "Failed to export data. Please try again."
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            }

            // Function to handle PDF export
            function handlePdfExport() {
                let selectedYear = $('#filter-year').val() || '';
                let selectedMonth = $('#filter-month').val() || '';
                let selectedDate = $('#filter-date').val() || '';
                let selectedVendorId = $('#filter-customer').val() || '';
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                let authToken = localStorage.getItem("authToken");

                $.ajax({
                    url: `/api/export-purchase-pdf`,
                    method: "GET",
                    beforeSend: function() {
                        toggleDownloadLoader(true, "Generating PDF...");
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: {
                        year: selectedYear,
                        month: selectedMonth,
                        date: selectedDate,
                        customer_id: selectedVendorId,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    success: function(response) {
                        if (response.status && response.file_url) {
                            let link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || 'Purchases.pdf';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "No Data Found",
                                text: response.message ||
                                    "No purchase data available for selected filters."
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Export Failed",
                            text: "Failed to generate PDF. Please try again."
                        });
                    },
                    complete: function() {
                        toggleDownloadLoader(false);
                    }
                });
            }

            // Bind desktop export buttons
            $('#exportAllChallanDesktop, #exportAllChallanMobile').off('click').on('click', function(e) {
                e.preventDefault();
                handleExcelExport();
            });

            // Bind PDF export buttons
            $('#exportPdfDesktop, #exportPdfMobile').off('click').on('click', function(e) {
                e.preventDefault();
                handlePdfExport();
            });
        });
    </script>
@endpush
