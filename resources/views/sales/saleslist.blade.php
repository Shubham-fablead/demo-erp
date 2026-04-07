@extends('layout.app')

@section('title', 'Sales List')

@section('content')
    <style>
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

        /* Payment Status Colors - Using only 4 colors */
        .status-pending {
            background-color: #ea5455 !important;
            /* Danger - Red */
        }

        .status-completed {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        .status-partially {
            background-color: #f90 !important;
            /* Info - Teal */
        }

        /* Payment Method Colors - Using only 4 colors */
        .status-cash {
            background-color: #f90 !important;
            /* Info - Teal */
        }

        .status-online {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        .status-emi {
            background-color: #ea5455 !important;
            /* Danger - Red */
        }

        .status-cash_online {
            background-color: #28c76f !important;
            /* Success - Green */
        }

        a.btn.btn-sm.btn-success.me-2 {
            color: white;
            border: none;
            padding: 4px;
            font-size: 11px;
        }

        /* Quotation / Sales badges */
        .status-quotation {
            background-color: #ff9f43 !important;
            /* Orange */
        }

        .status-sales {
            background-color: #28c76f !important;
            /* Green */
        }

        /* Unknown/Other payment method */
        .status-other {
            background-color: #8f99a2 !important;
            /* Gray */
        }

        /* Mobile View Status Styles */
        .mobile-badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            text-transform: capitalize;
            font-weight: 500;
            color: white !important;
            display: inline-block;
            min-width: 70px;
            text-align: center;
        }

        .table-scroll-top {
            display: none;
        }

        /* For Customer Name column */
        .datanew td:nth-child(4) {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px;
            /* optional */
        }

        /* For Biller column */
        .datanew td:nth-child(10) {
            max-width: 260px;
        }

        .biller-wrap {
            display: inline-block;
            max-width: 260px;
            white-space: normal !important;
            overflow-wrap: anywhere;
            word-break: break-word;
            line-height: 1.3;
        }

        @media screen and (max-width: 768px) {
            .table-scroll-top {
                display: block;
            }
        }

        .form-control {
            color: #595b5d !important;
            /* Bootstrap's default placeholder/input text color */
        }

        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        .dataTables_wrapper .row:first-child {
            display: none !important;
        }

        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .custom-select2 .select2-container--default .select2-selection--single {
            height: 31px !important;
            border: 1px solid #ced4da !important;
            border-radius: 4px !important;
        }

        .custom-select2 .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px !important;
            font-size: 14px !important;
            color: #595b5d !important;
            padding-left: 8px !important;
        }

        .custom-select2 .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        /* Mobile View Styles */
        .mobile-order-card {
            display: none;
        }





        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 11px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            /* Show only Order Number and Details */
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
            .datanew td:nth-child(9),
            .datanew th:nth-child(10),
            .datanew td:nth-child(10) {
                display: none;
            }

            .datanew th:nth-child(n+3),
            .datanew td:nth-child(n+3) {
                display: none;
            }
        }

        /* Center Details column */
        .datanew th:nth-child(2),
        .datanew td:nth-child(2) {
            text-align: center;
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Show only Order Number and Details */
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
            .datanew td:nth-child(9),
            .datanew th:nth-child(10),
            .datanew td:nth-child(10) {
                display: none;
            }

            /* Center Details column */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                text-align: center;
            }
        }

        /* Medium devices (tablets, 768px and up) */
        @media screen and (min-width: 768px) and (max-width: 991.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 13px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 992px and up) */
        @media screen and (min-width: 992px) and (max-width: 1199.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 10px 8px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Extra large devices (large desktops, 1200px and up) */
        @media screen and (min-width: 1200px) {
            .table-responsive {
                display: block !important;
            }

            .mobile-order-card {
                display: none;
            }

            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column (2nd column) on 768px and above */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .order-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .order-details-row {
            display: none;
        }

        .order-details-row.show {
            display: table-row;
        }


        /* Toggle button styles - available for all screen sizes */
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

        /* Expandable content styles - available for all screen sizes */
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

        .btn-icon-mobile.btn-view i,
        .btn-icon-mobile.btn-edit i,
        .btn-icon-mobile.btn-download i,
        .btn-icon-mobile.btn-print i,
        .btn-icon-mobile.btn-delete i,
        .btn-icon-mobile.btn-payment i {
            font-size: 16px;
        }

        /* Additional responsive adjustments for larger screens */
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

        .mobile-order-header-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            background: #f8f9fa;
            border-radius: 8px 8px 0 0;
            padding: 12px 15px;
            margin-bottom: 10px;
            font-weight: bold;
            color: #1b2850;
            border-bottom: 2px solid #e0e0e0;
        }

        .mobile-order-header-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .mobile-order-header-cell:first-child {
            width: 70%;
        }

        .mobile-order-header-cell:last-child {
            width: 30%;
            text-align: center;
        }

        .mobile-order-item {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .mobile-order-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        .mobile-order-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .mobile-order-number {
            font-weight: bold;
            font-size: 16px;
            color: #1b2850;
            width: 70%;
        }

        .mobile-order-details-cell {
            text-align: center;
            width: 30%;
        }

        .mobile-toggle-btn {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 20px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .mobile-toggle-btn:hover {
            background: #ff8c2e;
        }

        .mobile-toggle-btn.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn.minus:hover {
            background: #c82333;
        }

        .mobile-order-details {
            display: none;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .mobile-order-details.active {
            display: block;
        }

        .mobile-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-detail-row:last-child {
            border-bottom: none;
        }

        .mobile-detail-label {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .mobile-detail-value {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .mobile-action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .mobile-action-buttons a,
        .mobile-action-buttons button {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .mobile-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            text-transform: capitalize;
        }

        .bg-lightgreen {
            background-color: #d4edda;
            color: #155724;
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
            width: 100%;
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
                <h4>All Orders</h4>
                <!-- <h6>Manage your sales</h6> -->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(2, 'add'))
                    <a href="{{ route('sales.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img" class="me-1">New
                        Order</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- Top Search and Date Filter Row -->

                <div class="table-top mb-3">
                    <div class="row w-100 align-items-center">
                        <!-- Search -->
                        <div class="col-md-2 col-12 mb-1 mb-md-0">
                            {{-- <div class="search-set w-100">
                                <div class="search-path"></div>
                                <div class="search-input d-flex align-items-center" style="width:170px;">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="sales-search-input" class="form-control mb-1"
                                        style="height:30px" placeholder="Search...">
                                </div>
                            </div> --}}
                            <div class="search-set">
                                <!-- Your existing filters -->
                                <div class="search-input">
                                    <a class="btn btn-searchset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                            alt="img">
                                    </a>
                                    <input type="text" id="search-input" class="form-control" placeholder="Search..."
                                        style="height: 30px;width:100%;">
                                </div>
                            </div>
                        </div>

                        <!-- Total Filter -->
                        <div class="col-md-2 col-6">
                            @if (in_array(auth()->user()->role, ['admin', 'sub-admin']))
                                <div class="mb-1 d-flex align-items-center"
                                    style="color: #1b2850; border: 1px solid #ced4da; border-radius: 4px; padding: 0 8px; font-size: 14px; font-weight: bold; height: 31px; background: #fff;">
                                    Total: <span style="color: #ff9f43" class="ms-1" id="filtered-total">₹0.00</span>
                                </div>
                            @endif
                        </div>


                        <!-- Month Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1 custom-select2">
                                <!-- <label for="filter-month" class="form-label">Month</label> -->
                                <select id="filter-month" data-placeholder="All Months"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="all">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year Filter -->
                        <div class="col-md-2 col-6">
                            <div class="mb-1 custom-select2">
                                <!-- <label for="filter-year" class="form-label">Year</label> -->
                                <select id="filter-year" data-placeholder="All Years"
                                    class="form-control form-control-sm filter-select2">
                                    <option value="all">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Date Filter -->
                        <!-- <div class="col-md-2 col-6">
                                                                                                            <div class="form-group mb-0">
                                                                                                                <label for="filter-date" class="form-label">Date</label>
                                                                                                                <input type="text" id="filter-date" placeholder="Choose Date"
                                                                                                                    class="datetimepicker form-control form-control-sm">
                                                                                                            </div>
                                                                                                        </div> -->
                        <!-- Date -->
                        <div class="col-md-2 col-6">
                            <!-- <div class="form-group mb-0"> -->
                            <!-- <label for="filter-date" class="form-label">Date</label> -->
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm" style="width: 145px;">
                            <!-- </div> -->
                        </div>

                        <!-- Export Buttons -->
                        <div class="col-md-2 col-12 mb-1">
                            <div class="d-flex justify-content-md-end gap-2 mt-1">
                                <button id="exportAllChallan" class="btn btn-sm btn-success flex-fill">
                                    <i class="fas fa-file-excel"></i> Excel
                                </button>
                                <button id="exportPdf" class="btn btn-sm btn-danger flex-fill">
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
                                <div class="form-group custom-select2">
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
                <div class="table-scroll-top"
                    style="overflow-x: auto; overflow-y: hidden; height: 20px; margin-bottom: 5px;">
                    <div style="height: 1px;"></div> <!-- Adjust width to match your table width -->
                </div>
                <!-- Orders Table -->
                <div class="table-responsive mt-3" style="overflow-x: auto;">
                    <table class="table datanew" id="order-table" style="max-width: 2000px;">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th class="text-center">Details</th>
                                <th>Date</th>
                                <th>Customer Name</th>
                                <th>Quotation/Sale</th>
                                <th>Payment Status</th>
                                <th>Payment Method</th>
                                <th>Return Status</th>
                                <th>Total</th>
                                <th>Biller</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS will populate this -->
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Order Cards -->
                <div class="mobile-order-card mt-3" id="mobile-order-container">
                    <!-- JS will populate this -->
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="sales-per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="sales-pagination-from">0</span> - <span id="sales-pagination-to">0</span> of
                            <span id="sales-pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Sales pagination">
                        <ul class="pagination pagination-sm mb-0" id="sales-pagination-numbers"></ul>
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
                                    x <!-- This renders an “×” symbol -->
                                </button>
                            </div>

                            <ul id="paymentHistoryList" class="list-unstyled mb-0"
                                style="max-height: 200px; overflow-y: auto;">
                                <!-- Populated via JavaScript -->
                            </ul>
                        </div>



                        <div class="border p-2 rounded bg-light">
                            <strong>Total Amount:</strong> ₹<span id="emiTotal"></span><br>
                            <div id="returnAmountSection" class="d-none">
                                <strong>Return Amount:</strong> ₹<span id="returnAmountDisplay">0.00</span><br>
                            </div>
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
                            <input type="number" id="cashOnlineFullAmount" name="fully_cash_amount"
                                class="form-control" min="0" step="0.01">
                            <div class="text-danger" id="cashOnlineFullAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlineFullAmount" name="full_online_amount"
                                class="form-control" readonly>
                            <div class="text-danger" id="upiOnlineFullAmountError"></div>
                        </div>

                        <!-- Partial Cash + Online -->
                        <div class="mb-3 d-none" id="partialCashOnlineFields">
                            <label>Cash Amount</label>
                            <input type="number" id="cashOnlinePartialAmount" name="cash_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="cashOnlinePartialAmountError"></div>
                            <label class="mt-2">Online Amount</label>
                            <input type="number" id="upiOnlinePartialAmount" name="online_amount" class="form-control"
                                min="0" step="0.01">
                            <div class="text-danger" id="upiOnlinePartialAmountError"></div>
                            <label class="mt-2">Remaining Amount</label>
                            <input type="number" id="remainingCashOnlineAmount" name="remaining_amount"
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
                            <input type="number" class="form-control" id="upiAmountInput" name="upi_online_amount"
                                readonly>
                            <div class="text-danger" id="upiAmountError"></div>
                        </div>



                        <!-- Partially Paid Fields -->
                        <div class="mb-3 d-none" id="partialPaidFields">
                            <label for="partialAmount" class="form-label">Enter Amount</label>
                            <input type="number" class="form-control mb-2" id="partialAmount" name="amount"
                                min="1" step="0.01">
                            <div class="text-danger" id="partialAmountError"></div>

                            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                                <div style="flex: 1;">
                                    <label for="pendingAmount" class="form-label">Pending Amount</label>
                                    <input type="number" class="form-control" id="pendingAmount"
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
                            <input type="number" class="form-control" id="cashAmount" name="cashAmount" min="0"
                                step="0.01">
                            <div class="text-danger" id="cashAmountError"></div>
                        </div>













                        <!-- Cleaned Hidden Inputs (no duplicate name attributes) -->
                        <input type="hidden" id="paymentJobCardId" name="order_id">
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
    </script>

    <script>
        // Helper function to get status badge HTML
        // Helper function to get status badge HTML
        // In your JavaScript code, update the getStatusBadge and getMobileStatusBadge functions:

        // Helper function to get status badge HTML
        function getStatusBadge(status, type = 'payment', extraPaid = 0) {
            status = status ? status.toLowerCase() : '';

            if (type === 'quotation') {
                switch (status) {
                    case 'quotation':
                        return `<span class="status-badge status-quotation">Quotation</span>`;
                    case 'sales':
                    case 'sale':
                    case 'sales':
                        return `<span class="status-badge status-sales">Sales</span>`;
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`;
                }
            }

            if (type === 'payment') {
                // Check if there's extra paid (advance/overpayment)
                if (extraPaid > 0) {
                    return `<span class="status-badge status-pending">Extra Paid: ₹${extraPaid.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`; // Red for extra paid
                }

                // Payment Status badges
                switch (status) {
                    case 'pending':
                        return `<span class="status-badge status-pending">Pending</span>`; // Red
                    case 'completed':
                    case 'paid':
                        return `<span class="status-badge status-completed">Completed</span>`; // Green
                    case 'partially':
                    case 'partial':
                        return `<span class="status-badge status-partially">Partially</span>`; // Teal
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            } else if (type === 'return') {
                switch (status) {
                    case 'returned':
                        return `<span class="status-badge status-pending">Returned</span>`; // Red
                    default:
                        return `<span class="status-badge status-completed">No Return</span>`; // Gray
                }
            } else {
                // Payment Method badges
                switch (status) {
                    case 'cash':
                        return `<span class="status-badge status-cash">Cash</span>`; // Teal
                    case 'online':
                        return `<span class="status-badge status-online">Online</span>`; // Green
                    case 'emi':
                        return `<span class="status-badge status-emi">EMI</span>`; // Red
                    case 'cash_online':
                    case 'cash + online':
                        return `<span class="status-badge status-cash_online">Cash+Online</span>`; // Green
                    default:
                        return `<span class="status-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            }
        }

        // Helper function for mobile view
        function getMobileStatusBadge(status, type = 'payment', extraPaid = 0) {
            status = status ? status.toLowerCase() : '';

            if (type === 'quotation') {
                switch (status) {
                    case 'quotation':
                        return `<span class="mobile-badge status-quotation">Quotation</span>`;
                    case 'sales':
                    case 'sale':
                        return `<span class="mobile-badge status-sales">Sales</span>`;
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`;
                }
            }

            if (type === 'payment') {
                // Check if there's extra paid (advance/overpayment)
                if (extraPaid > 0) {
                    return `<span class="mobile-badge status-pending">Extra: ₹${extraPaid.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>`; // Red for extra paid
                }

                switch (status) {
                    case 'pending':
                        return `<span class="mobile-badge status-pending">Pending</span>`; // Red
                    case 'completed':
                    case 'paid':
                        return `<span class="mobile-badge status-completed">Paid</span>`; // Green
                    case 'partially':
                    case 'partial':
                        return `<span class="mobile-badge status-partially">Partially</span>`; // Teal
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            } else if (type === 'return') {
                switch (status) {
                    case 'returned':
                        return `<span class="mobile-badge status-pending">Returned</span>`; // Red
                    default:
                        return `<span class="mobile-badge status-other">No Return</span>`; // Gray
                }
            } else {
                switch (status) {
                    case 'cash':
                        return `<span class="mobile-badge status-cash">Cash</span>`; // Teal
                    case 'online':
                        return `<span class="mobile-badge status-online">Online</span>`; // Green
                    case 'emi':
                        return `<span class="mobile-badge status-emi">EMI</span>`; // Red
                    case 'cash_online':
                    case 'cash + online':
                        return `<span class="mobile-badge status-cash_online">Cash+Online</span>`; // Green
                    default:
                        return `<span class="mobile-badge status-other">${status || 'N/A'}</span>`; // Gray
                }
            }
        }
        // Function to render mobile order cards
        function renderMobileOrders(orders, currencySymbol, currencyPosition) {
            const container = $('#mobile-order-container');
            container.html('');

            if (!orders || orders.length === 0) {
                container.html('<div class="text-center p-4">No orders found</div>');
                return;
            }

            // Add header row
            const headerHtml = `
                <div class="mobile-order-header-row">
                    <div class="mobile-order-header-cell">Order Number</div>
                    <div class="mobile-order-header-cell">Details</div>
                </div>
            `;
            container.append(headerHtml);

            orders.forEach((order, index) => {
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);
                const amount = parseFloat(order.total_amount || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const displayAmount = currencyPosition === 'right' ?
                    amount + currencySymbol : currencySymbol + amount;


                // Build action buttons HTML
                let actionBtns = '';
                @if (app('hasPermission')(2, 'view'))
                // if (status === 'sales') {
                if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                    actionBtns += `<a href="javascript:void(0);" class="btn btn-sm btn-primary make-payment-btn"
                        data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                        data-id="${order.id}" data-amount="${order.remaining_amount}"
                        data-method="${order.payment_method || ''}"
                        data-emi-months="${order.remaining_emi_months}"
                        data-emi-duration="${order.emi_duration || 0}"
                        data-total-amount="${order.total_amount || 0}"
                        data-remaining-amount="${order.remaining_amount}"
                        data-return-amount="${order.total_return || 0}"
                        data-remaining-emi-months="${order.remaining_emi_months}"
                        title="Make Payment">
                        <i class="fas fa-money-bill"></i> Pay
                    </a>`;
                }
                @endif

                @if (app('hasPermission')(2, 'view'))
                actionBtns += `<button class="btn btn-sm btn-secondary open-history" data-id="${order.id}" title="Payment History">
                    <i class="fas fa-history"></i> History
                </button>`;
                @endif

                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<a class="btn btn-sm btn-success convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        Convert to Sales
                    </a>`;
                }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="btn btn-sm btn-info" href="/sales-details/${order.id}">
                        <i class="fas fa-eye"></i> View
                    </a>`;
                @endif

                // if (!order.has_payment || order.has_payment === 0) {
                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `<a class="btn btn-sm btn-warning" href="/edit-sales/${order.id}">
                                <i class="fas fa-edit"></i> Edit
                            </a>`;
                    }
                @endif
                // }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="btn btn-sm btn-success" href="/sales-invoice/${order.id}">
                        <i class="fas fa-file-invoice"></i> Invoice
                    </a>`;
                @endif

                if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !==
                    'inventory-manager') {
                    @if (app('hasPermission')(2, 'delete'))
                        actionBtns += `<a class="btn btn-sm btn-danger delete-order" href="javascript:void(0);" data-id="${order.id}">
                            <i class="fas fa-trash"></i> Delete
                        </a>`;
                    @endif
                }

                const cardHtml = `
                    <div class="mobile-order-item" data-order-id="${order.id}">
                        <div class="mobile-order-row">
                            <div class="mobile-order-cell mobile-order-number">${order.order_number || 'N/A'}</div>
                            <div class="mobile-order-cell mobile-order-details-cell">
                                <button class="mobile-toggle-btn" onclick="toggleMobileDetails('${order.id}')" data-order-id="${order.id}">
                                    <span class="toggle-icon">+</span>
                                </button>
                            </div>
                        </div>
                        <div class="mobile-order-details" id="mobile-details-${order.id}">
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Date:</span>
                                <span class="mobile-detail-value">${order.created_date || 'N/A'}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Customer Name:</span>
                                <span class="mobile-detail-value">${order.user?.name || 'N/A'}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Quotation/Sale:</span>
                                <span class="mobile-detail-value">${getMobileStatusBadge(order.quotation_status || 'sales', 'quotation')}</span>
                            </div>
                            <div class="mobile-detail-row">
                <span class="mobile-detail-label">Payment Status:</span>
                <span class="mobile-detail-value">
                    ${getMobileStatusBadge(order.payment_status, 'payment', order.extra_paid || 0)}
                </span>
            </div>
            <div class="mobile-detail-row">
                <span class="mobile-detail-label">Return Status:</span>
                <span class="mobile-detail-value">
                    ${getMobileStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return')}
                </span>
            </div>
            ${order.extra_paid > 0 ? `
                                                    <div class="mobile-detail-row">
                                                        <span class="mobile-detail-label" style="color: #dc3545;">Extra Paid:</span>
                                                        <span class="mobile-detail-value" style="color: #dc3545; font-weight: bold;">
                                                            ${currencySymbol}${parseFloat(order.extra_paid || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                        </span>
                                                    </div>
                                                ` : ''}
            <div class="mobile-detail-row">
                <span class="mobile-detail-label">Payment Method:</span>
                <span class="mobile-detail-value">
                    ${getMobileStatusBadge(order.payment_method, 'method')}
                </span>
            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Total:</span>
                                <span class="mobile-detail-value" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                            </div>
                            <div class="mobile-detail-row">
                                <span class="mobile-detail-label">Biller:</span>
                                <span class="mobile-detail-value">${order.biller || 'Admin'}</span>
                            </div>
                            ${parseFloat(order.remaining_amount || 0) > 0 &&
                            (order.quotation_status || 'sales').toLowerCase() === 'sales' ? `
                                                                    <div class="mobile-detail-row">
                                                                        <span class="mobile-detail-label">Remaining:</span>
                                                                        <span class="mobile-detail-value" style="color: #dc3545; font-weight: bold;">
                                                                            ${currencySymbol}${parseFloat(order.remaining_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                                                                        </span>
                                                                    </div>
                                                                    ` : ''}
                            <div class="mobile-action-buttons">
                                ${actionBtns}
                            </div>
                        </div>
                    </div>
                `;
                container.append(cardHtml);
            });
        }

        // Toggle mobile details function
        function toggleMobileDetails(orderId) {
            const details = $(`#mobile-details-${orderId}`);
            const btn = $(`.mobile-toggle-btn[data-order-id="${orderId}"]`);
            const icon = btn.find('.toggle-icon');

            if (details.hasClass('active')) {
                details.removeClass('active');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                details.addClass('active');
                btn.addClass('minus');
                icon.text('−');
            }
        }

        // Helper function to build expandable row content
        function buildExpandableRowContent(order, currencySymbol, currencyPosition) {
            const amount = parseFloat(order.total_amount || 0).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            const displayAmount = currencyPosition === 'right' ?
                amount + currencySymbol : currencySymbol + amount;

            let actionBtns = '';

            // History icon button
            actionBtns += `<button class="btn-icon-mobile btn-history open-history" data-id="${order.id}" title="Payment History">
                <i class="fas fa-history"></i>
            </button>`;

            // View icon button
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-view" href="/sales-details/${order.id}" title="View">
                    <i class="fas fa-eye"></i>
                </a>`;
            @endif

            // Edit icon button
            // if (!order.has_payment || order.has_payment === 0) {
            @if (app('hasPermission')(2, 'edit'))
                if (parseFloat(order.total_return || 0) === 0) {
                    actionBtns += `<a class="btn-icon-mobile btn-edit" href="/edit-sales/${order.id}" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>`;
                }
            @endif
            // }

            // Download icon button (Invoice)
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-download" href="/sales-invoice/${order.id}" title="Download Invoice">
                    <i class="fas fa-download"></i>
                </a>`;
            @endif

            // Print icon button
            @if (app('hasPermission')(2, 'view'))
                actionBtns += `<a class="btn-icon-mobile btn-print" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});" title="Print Invoice">
                    <i class="fas fa-print"></i>
                </a>`;
            @endif

            // Delete icon button
            if (userRole !== 'sales-manager' && userRole !== 'purchase-manager' && userRole !== 'inventory-manager') {
                @if (app('hasPermission')(2, 'delete'))
                    actionBtns += `<a class="btn-icon-mobile btn-delete delete-order" href="javascript:void(0);" data-id="${order.id}" title="Delete">
                        <i class="fas fa-trash"></i>
                    </a>`;
                @endif
            }

            // Payment icon button if there's remaining amount

            if (
                (order.quotation_status || 'sales').toLowerCase() === 'sales'
            ) {
                actionBtns += `<a href="javascript:void(0);" class="btn-icon-mobile btn-payment make-payment-btn"
                    data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                    data-id="${order.id}" data-amount="${order.remaining_amount}"
                    data-method="${order.payment_method || ''}"
                    data-emi-months="${order.remaining_emi_months}"
                    data-emi-duration="${order.emi_duration || 0}"
                    data-total-amount="${order.total_amount || 0}"
                    data-remaining-amount="${order.remaining_amount}"
                    data-return-amount="${order.total_return || 0}"
                    data-remaining-emi-months="${order.remaining_emi_months}"
                    title="Make Payment">
                    <i class="fas fa-money-bill-wave"></i>
                </a>`;
            }

            return `
        <td colspan="9" class="order-details-content">
            <div class="order-details-list">
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Date:</span>
                    <span class="order-detail-value-simple">${order.created_date || 'N/A'}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Customer Name:</span>
                    <span class="order-detail-value-simple">${order.user?.name || 'N/A'}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Payment Status:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0)}
                    </span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Return Status:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return')}
                    </span>
                </div>
                ${(order.extra_paid || 0) > 0 ? `
                                                        <div class="order-detail-row-simple">
                                                            <span class="order-detail-label-simple" style="color: #dc3545;">Extra Paid:</span>
                                                            <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                                                ${currencySymbol}${parseFloat(order.extra_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (Advance/Refund)
                                                            </span>
                                                        </div>
                                                    ` : ''}
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Payment Method:</span>
                    <span class="order-detail-value-simple">
                        ${getStatusBadge(order.payment_method, 'method')}
                    </span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Total:</span>
                    <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">${displayAmount}</span>
                </div>
                <div class="order-detail-row-simple">
                    <span class="order-detail-label-simple">Biller:</span>
                    <span class="order-detail-value-simple">${order.biller || 'Admin'}</span>
                </div>
                ${parseFloat(order.remaining_amount || 0) > 0 &&
(order.quotation_status || 'sales').toLowerCase() === 'sales' ? `
                                                        <div class="order-detail-row-simple">
                                                            <span class="order-detail-label-simple">Remaining:</span>
                                                            <span class="order-detail-value-simple" style="color: #dc3545; font-weight: bold;">
                                                                ${currencySymbol}${parseFloat(order.remaining_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
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

        // Toggle function for table rows
        function toggleTableRowDetails(orderId) {
            // Find the button that was clicked
            const btn = $(`.mobile-toggle-btn-table[data-order-id="${orderId}"]`);
            if (btn.length === 0) {
                // console.error('Toggle button not found for order:', orderId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.order-details-row[data-order-id="${orderId}"]`);
            const icon = btn.find('.toggle-icon');

            // If expandable row doesn't exist, create it
            if (detailsRow.length === 0) {
                const orderData = window.orderDataMap && window.orderDataMap[orderId];
                if (orderData) {
                    detailsRow = $('<tr>')
                        .addClass('order-details-row')
                        .attr('data-order-id', orderId)
                        .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData.currencyPosition));
                    row.after(detailsRow);
                } else {
                    // console.error('Order data not found for order:', orderId);
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

        // Global variables
        var table;

        // Function to calculate total for visible rows - must be global
        function calculateFilteredTotal() {
            if (!table) {
                table = $('.datanew').DataTable();
            }

            let total = 0;

            // Find the Total column index by header name
            let totalColumnIndex = -1;
            table.columns().every(function() {
                const header = $(this.header());
                if (header.text().trim() === 'Total') {
                    totalColumnIndex = this.index();
                    return false; // break
                }
            });

            // If column not found by name, use index 6 as fallback
            if (totalColumnIndex === -1) {
                totalColumnIndex = 6;
            }

            table.rows({
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
            $('#filtered-total').text(
                `${currencySymbol}${total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
            );
        }

        // Normalize filter values: treat "all" or empty as no-filter ('')
        function normalizeFilterValue(val) {
            if (typeof val === 'undefined' || val === null) return '';
            return (String(val) === '' || String(val) === 'all') ? '' : val;
        }

        // Helper function to update Select2 display for filters
        function updateSelect2Display(selectedMonth, selectedYear) {
            try {
                let paddedMonth = '';
                if (typeof selectedMonth !== 'undefined' && selectedMonth !== null && selectedMonth !== '') {
                    paddedMonth = (String(selectedMonth).length === 1) ? ('0' + String(selectedMonth)) : String(
                        selectedMonth);
                }

                if (paddedMonth !== '') {
                    $('#filter-month').val(paddedMonth).trigger('change.select2');
                } else {
                    $('#filter-month').val('all').trigger('change.select2');
                }

                if (typeof selectedYear !== 'undefined' && selectedYear !== null && selectedYear !== '') {
                    $('#filter-year').val(String(selectedYear)).trigger('change.select2');
                } else {
                    $('#filter-year').val('all').trigger('change.select2');
                }

                // Update Select2 rendered text directly to ensure UI is in sync
                const monthText = $('#filter-month option:selected').text() || 'All Months';
                const yearText = $('#filter-year option:selected').text() || 'All Years';
                const monthRendered = $('#filter-month').next('.select2-container').find('.select2-selection__rendered');
                const yearRendered = $('#filter-year').next('.select2-container').find('.select2-selection__rendered');
                if (monthRendered.length) monthRendered.text(monthText.trim());
                if (yearRendered.length) yearRendered.text(yearText.trim());
            } catch (e) {
                // console.warn('Failed to update Select2 display:', e);
            }
        }

        $(document).ready(function() {
            // Initialize each Select2 with its own placeholder so empty value shows label
            $('.filter-select2').each(function() {
                const placeholder = $(this).data('placeholder') || '';
                $(this).select2({
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: true,
                    templateSelection: function(state) {
                        return (state && state.text) ? state.text.trim() : placeholder;
                    },
                    templateResult: function(state) {
                        return (state && state.text) ? state.text.trim() : '';
                    }
                });
            });

            const syncFilterSelect2Display = () => {
                $('#filter-month, #filter-year').each(function() {
                    $(this).trigger('change.select2');
                });
            };
            syncFilterSelect2Display();

            let skipDateResetFromMonthYear = false;


            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            table = $('.datanew').DataTable();

            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                loadOrders(1);
            });

            $('#sales-per-page-select').on('change', function() {
                perPage = $(this).val();
                table.page.len(parseInt(perPage, 10)).draw(false);
                loadOrders(1);
            });

            $(document).on('click', '.make-payment-btn', function() {
                let jobCardId = $(this).data('id');
                let totalAmount = $(this).data('total-amount');
                let remainingAmount = $(this).data('remaining-amount');
                let returnAmount = parseFloat($(this).data('return-amount')) || 0;
                let method = $(this).data('method') || '';

                // ✅ Fill modal hidden inputs + text spans
                $('#paymentJobCardId').val(jobCardId);
                $('#emiTotal').text(parseFloat(totalAmount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                $('#remainingAmountDisplay').text(parseFloat(remainingAmount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                if (returnAmount > 0) {
                    $('#returnAmountDisplay').text(returnAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }));
                    $('#returnAmountSection').removeClass('d-none');
                } else {
                    $('#returnAmountSection').addClass('d-none');
                }

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
                        url: '/api/sales/payment-history/' + jobCardId,
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken,
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
                                                                <strong>Amount:</strong> ₹${parseFloat(payment.payment_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}<br>
                                                                <strong>Date:</strong> ${payment.payment_date}<br>
                                                                <strong>Method:</strong> ${payment.payment_method}<br>
                                                                <strong>Payment Type:</strong> ${payment.payment_type ? payment.payment_type : 'N/A'}<br>
                                                                ${payment.payment_type === 'emi' ? `<strong>EMI Months:</strong> ${payment.emi_month || 0}<br>` : ''}
                                                            </li>
                                                            <hr class="my-1"/>
                                                        `;
                                });
                                $('#paymentHistoryList').html(historyHtml);

                                // Add Summary
                                if (response.summary) {
                                    let summaryHtml = `
                                        <hr class="my-2"/>
                                        <li class="mb-1"><strong>Order Total:</strong> ₹${parseFloat(response.summary.order_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Total Paid:</strong> ₹${parseFloat(response.summary.total_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Return Amount:</strong> ₹${parseFloat(response.summary.return_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                        <li class="mb-1"><strong>Remaining:</strong> ₹${parseFloat(response.summary.remaining).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</li>
                                    `;
                                    $('#paymentHistoryList').append(summaryHtml);
                                }
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
                $("#bankError").text("");

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

            $('#bank_id').on('change', function() {
                if ($(this).val()) {
                    $("#bankError").text("");
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
                    $('#pendingAmount').val(remaining.toFixed(2));

                    // Remove any full cash amount
                    $('#cashAmount').val('').prop('readonly', false).prop('disabled', true);

                    // Live calculation for pending
                    $('#partialAmount').off('input').on('input', function() {
                        let entered = parseFloat($(this).val()) || 0;
                        let newPending = Math.max(remaining - entered, 0);
                        $('#pendingAmount').val(newPending.toFixed(2));
                    });

                } else if (type === 'cash_fully') {
                    // Show fully fields
                    $('#fullyPaidFields').removeClass('d-none');
                    $('#fullyPaidFields input').prop('disabled', false);

                    // Fill full amount & disable editing
                    $('#cashAmount').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial fields
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

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
                    $('#pendingAmount').val(remaining.toFixed(2));

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
                    $('#upiAmountInput').val(remaining.toFixed(2)).prop('readonly', true);

                    // Reset partial section
                    $('#partialAmount, #pendingAmount').val('');
                    $('#partialPaidFields input').prop('disabled', true);
                }
            });

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
                    $('#upiOnlineFullAmount').val(remaining.toFixed(2));

                    // Live adjustment of online amount
                    $('#cashOnlineFullAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let online = Math.max(remaining - cash, 0);
                        $('#upiOnlineFullAmount').val(online.toFixed(2));
                    });

                    // Disable partial fields
                    $('#partialCashOnlineFields input').prop('disabled', true);

                } else if (type === 'cash_online_partially') {
                    // Show partial section
                    $('#partialCashOnlineFields').removeClass('d-none');
                    $('#partialCashOnlineFields input').prop('disabled', false);

                    // Reset values
                    $('#cashOnlinePartialAmount, #upiOnlinePartialAmount').val('');
                    $('#remainingCashOnlineAmount').val(remaining.toFixed(2));

                    // Live update on cash input
                    $('#cashOnlinePartialAmount').off('input').on('input', function() {
                        let cash = parseFloat($(this).val()) || 0;
                        let newRemaining = Math.max(remaining - cash, 0);
                        $('#remainingCashOnlineAmount').val(newRemaining.toFixed(2));
                    });

                    // Live update on online input
                    $('#upiOnlinePartialAmount').off('input').on('input', function() {
                        let online = parseFloat($(this).val()) || 0;
                        let cash = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let newRemaining = Math.max(remaining - cash - online, 0);
                        // console.log("newRemaining:", newRemaining);
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



                // EMI Validation



                // Payment Method validation


                if (!paymentMethod) {
                    isValid = false;
                    // console.log("Validation failed: Payment method not selected");
                    $('#paymentMethodError').text("Please select a payment method.");
                    return false;
                } else {
                    console.log("Payment method selected:", paymentMethod);
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
                        let partialAmount = parseFloat($('#partialAmount').val()) || 0;
                        let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

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
                                .toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ")."
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
                        let cashAmount = $('#cashAmount').val();
                        // console.log("Cash fully selected, amount:", cashAmount);
                        if (!cashAmount || parseFloat(cashAmount) <= 0) {
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

                    let onlineAmount = parseFloat($('#partialAmount').val()) || parseFloat($(
                        '#upiAmountInput').val()) || 0;
                    let remainingAmount = parseFloat($('#remainingAmountHidden').val()) || 0;

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
                            .toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ")."
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
                        let cashAmt = $('#cashOnlineFullAmount').val();
                        let onlineAmt = $('#upiOnlineFullAmount').val();
                        // console.log("Cash+Online fully amounts:", cashAmt, onlineAmt);

                        if (!cashAmt || parseFloat(cashAmt) <= 0 || !onlineAmt || parseFloat(onlineAmt) <=
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
                        let cashAmt = parseFloat($('#cashOnlinePartialAmount').val()) || 0;
                        let onlineAmt = parseFloat($('#upiOnlinePartialAmount').val()) || 0;

                        // Clean pending amount
                        let rawPending = $('#remainingCashOnlineAmount').val() || "0";
                        rawPending = rawPending.replace(/[₹,]/g, '').trim();
                        let pendingAmt = parseFloat(rawPending) || 0;

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
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
                            $('#upiOnlinePartialAmountError').text(
                                "Total payment cannot exceed pending amount (" + pendingAmt + ").");
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
                    url: "/api/sales/make-payment",
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
            $('#makePaymentModal').on('hidden.bs.modal', function() {

                // Reset entire form
                $('#makePaymentForm')[0].reset();

                // Hide all dynamic sections
                $('#cashOnlineTypeDiv, #fullyCashOnlineFields, #partialCashOnlineFields, #onlineTypeDiv, #paidTypeDiv, #upiAmountDiv, #partialPaidFields, #fullyPaidFields, #bank_container')
                    .addClass('d-none');

                // Clear error messages
                $('.text-danger').text('');

                // Hide payment history
                $('#paymentHistoryBox').addClass('d-none');
                $('#paymentHistoryList').html('');

                // Reset hidden fields
                $('#paymentJobCardId').val('');
                $('#remainingAmountHidden').val('');
                $('#paymentMethodHidden').val('');
            });

            function loadOrders(page = 1) {
                currentPage = page;
                $.ajax({
                    url: "/api/get_orders",
                    method: "GET",
                    data: {
                        page: currentPage,
                        per_page: perPage,
                        search: searchQuery,
                        selectedSubAdminId: selectedSubAdminId || ''
                    },
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {

                        // console.log('response ', response);
                        if (response.status) {
                            currentPage = response.pagination?.current_page || 1;
                            lastPage = response.pagination?.last_page || 1;
                            updateSalesPaginationUI(response.pagination || {
                                current_page: 1,
                                last_page: 1,
                                per_page: perPage,
                                total: response.data?.length || 0
                            });
                            let tableBody = [];
                            const currencySymbol = response.currency_symbol || '₹';
                            const currencyPosition = response.currency_position || 'left';
                            const totalAmount = parseFloat(response.total_amount || 0).toLocaleString(
                                undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            const totalDisplay = currencyPosition === 'right' ?
                                totalAmount + currencySymbol :
                                currencySymbol + totalAmount;
                            $('#filtered-total').text(totalDisplay);


                            response.data.forEach(order => {

                                const status = String(order.quotation_status || 'sales')
                                    .toLowerCase();
                                const remaining = parseFloat(order.remaining_amount || 0);
                                let amount = parseFloat(order.total_amount).toLocaleString(
                                    undefined, {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                let displayAmount = currencyPosition === 'right' ?
                                    amount + currencySymbol // No space before/after
                                    :
                                    currencySymbol + amount;


                                let actionBtns = ``;

                                // if (status === 'sales') {
                                if (parseFloat(order.remaining_amount || 0) > 0 && status ===
                                    'sales') {
                                    actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}" title="Make Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                            <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                        </svg>
                                    </a>`;

                                }
                                actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;
                                if ((order.quotation_status || '').toLowerCase() ===
                                    'quotation') {
                                    actionBtns += `<a class="btn btn-sm btn-success me-2 convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                                    Convert to Sales
                                </a>`;
                                }
                                @if (app('hasPermission')(2, 'view'))
                                    actionBtns += `<a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>


                                        </a>`;
                                @endif
                                // if (!order.has_payment || order.has_payment === 0) {
                                @if (app('hasPermission')(2, 'edit'))
                                    if (parseFloat(order.total_return || 0) === 0) {
                                        actionBtns += `
                                            <a class="me-3" href="/edit-sales/${order.id}">
                                                <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                </svg>

                                            </a>`;
                                    }
                                @endif
                                // }
                                @if (app('hasPermission')(2, 'view'))
                                    actionBtns += `
                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>`;
                                @endif
                                let deleteButton = '';

                                if (
                                    userRole !== 'sales-manager' &&
                                    userRole !== 'purchase-manager' &&
                                    userRole !== 'inventory-manager'
                                ) {
                                    @if (app('hasPermission')(2, 'delete'))
                                        actionBtns += `
                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                            </svg>
                                        </a>
                                    `;
                                    @endif
                                }
                                // Store order data for expandable row
                                const orderData = {
                                    ...order,
                                    displayAmount: displayAmount,
                                    currencySymbol: currencySymbol,
                                    currencyPosition: currencyPosition
                                };

                                tableBody.push([
                                    `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                                    <span class="toggle-icon">+</span>
                                </button>`,
                                    order.created_date,
                                    order.user?.name || '',
                                    getStatusBadge(order.quotation_status || 'sales',
                                        'quotation'),
                                    getStatusBadge(order.payment_status, 'payment',
                                        order
                                        .extra_paid || 0),
                                    getStatusBadge(order.payment_method, 'method'),
                                    getStatusBadge(parseFloat(order.total_return || 0) >
                                        0 ?
                                        'returned' : '', 'return'),
                                    displayAmount || '0.00',
                                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                                    actionBtns
                                ]);

                                // Store order data for later use in expandable row
                                if (!window.orderDataMap) {
                                    window.orderDataMap = {};
                                }
                                window.orderDataMap[order.id] = orderData;

                            });


                            table.clear().rows.add(tableBody).draw();

                            // Add expandable rows after table is drawn
                            function addExpandableRows() {
                                const tbody = $('#order-table tbody');
                                table.rows().every(function() {
                                    const row = this.node();
                                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                                    if (toggleBtn.length > 0) {
                                        const orderId = toggleBtn.data('order-id');
                                        const orderData = window.orderDataMap[orderId];
                                        if (orderData && !$(row).next(
                                                'tr.order-details-row[data-order-id="' +
                                                orderId +
                                                '"]').length) {
                                            const expandableRow = $('<tr>')
                                                .addClass('order-details-row')
                                                .attr('data-order-id', orderId)
                                                .html(buildExpandableRowContent(orderData,
                                                    orderData
                                                    .currencySymbol, orderData
                                                    .currencyPosition
                                                ));
                                            $(row).after(expandableRow);
                                        }
                                    }
                                });
                            }

                            setTimeout(addExpandableRows, 100);

                            // Re-add expandable rows on table redraw
                            table.on('draw', function() {
                                setTimeout(addExpandableRows, 50);
                            });
                            // Render mobile cards
                            renderMobileOrders(response.data, currencySymbol, currencyPosition);
                            calculateFilteredTotal();
                            setTimeout(() => {
                                const topScroll = document.querySelector('.table-scroll-top');
                                const tableResponsive = document.querySelector(
                                    '.table-responsive');
                                const orderTable = document.getElementById('order-table');

                                if (topScroll && tableResponsive && orderTable) {
                                    const topInnerDiv = topScroll.querySelector('div');

                                    if (topInnerDiv) {
                                        topInnerDiv.style.width = orderTable.scrollWidth + 'px';

                                        // Avoid duplicate listeners
                                        topScroll.onscroll = () => {
                                            tableResponsive.scrollLeft = topScroll
                                                .scrollLeft;
                                        };
                                        tableResponsive.onscroll = () => {
                                            topScroll.scrollLeft = tableResponsive
                                                .scrollLeft;
                                        };
                                    }
                                }
                            }, 100);
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="9">No order found</td></tr>');
                            $('#filtered-total').text('₹0.00');
                            $('#mobile-order-container').html(
                                '<div class="text-center p-4">No orders found</div>');
                        }
                    },
                    error: function() {
                        alert("Failed to load orders.");
                    }
                });
            }

            function updateSalesPaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;

                if (to > pagination.total) {
                    to = pagination.total;
                }

                if (pagination.total === 0) {
                    from = 0;
                }

                $('#sales-pagination-from').text(from);
                $('#sales-pagination-to').text(to);
                $('#sales-pagination-total').text(pagination.total);

                let paginationHtml = '';
                let startPage = Math.max(1, pagination.current_page - 2);
                let endPage = Math.min(pagination.last_page, startPage + 4);

                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link sales-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                $('#sales-pagination-numbers').html(paginationHtml);
                $('.pagination-controls').toggle(pagination.total > 0);
            }

            $(document).on('click', '.sales-page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    loadOrders(page);
                }
            });

            loadOrders(currentPage);

            // Function to calculate the total for visible (filtered) rows

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

            // Recalculate total after table redraw
            table.on('draw', function() {
                calculateFilteredTotal();
            });


            $('#filter-date').on('dp.change', function() {
                if (skipDateResetFromMonthYear) {
                    skipDateResetFromMonthYear = false;
                    return;
                }

                let selectedDate = $(this).val().trim(); // e.g. DD-MM-YYYY

                // Clear month & year dropdowns
                updateSelect2Display('', '');

                if (selectedDate === '') {
                    fetchAllOrders();
                    return;
                }

                // Convert to YYYY-MM-DD
                if (selectedDate.includes('-')) {
                    let parts = selectedDate.split('-');
                    if (parts.length === 3) {
                        selectedDate = `${parts[2]}-${parts[1]}-${parts[0]}`;
                    }
                }

                $.ajax({
                    url: '/api/orders/filter',
                    method: 'GET',
                    data: {
                        date: selectedDate,
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            $('.pagination-controls').hide();
                            renderOrders(response.data, selectedDate);
                            calculateFilteredTotal();
                        }
                    }
                });
            });

            // Listen to both native change and Select2 events so UI and programmatic
            // updates all trigger the same handler.
            $('#filter-month, #filter-year').off('change select2:select select2:unselect').on(
                'change select2:select select2:unselect',
                function(e) {
                    syncFilterSelect2Display();

                    const selectedMonthRaw = $('#filter-month').val() || '';
                    const selectedYearRaw = $('#filter-year').val() || '';
                    const selectedMonth = normalizeFilterValue(selectedMonthRaw);
                    const selectedYear = normalizeFilterValue(selectedYearRaw);
                    const selectedDate = $('#filter-date').val() || '';

                    // If both month and year are empty, treat as "no filter" and fetch all orders.
                    if (selectedMonth === '' && selectedYear === '') {
                        updateSelect2Display('', '');
                        // Only fetch all if date is also empty
                        if (!selectedDate) {
                            fetchAllOrders();
                            setTimeout(function() {
                                calculateFilteredTotal();
                            }, 200);
                        }
                        return;
                    }

                    // If month/year is selected, clear the date filter to avoid confusion
                    if (selectedMonth || selectedYear) {
                        if ($('#filter-date').val()) {
                            skipDateResetFromMonthYear = true;
                            $('#filter-date').val('');
                        }
                    }

                    $.ajax({
                        url: '/api/orders/filter',
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        data: {
                            month: selectedMonth,
                            year: selectedYear,
                            selectedSubAdminId: selectedSubAdminId
                        },
                        success: function(response) {
                            if (response.status) {
                                $('.pagination-controls').hide();
                                renderOrdersByMonthAndYear(response.data, selectedMonth,
                                    selectedYear); // render table with filters

                                updateSelect2Display(selectedMonth, selectedYear);
                                // Recalculate total after rendering
                                setTimeout(function() {
                                    calculateFilteredTotal();
                                }, 200);
                            } else {
                                // console.error('Filter failed:', response.message ||
                                //     'Unknown error');
                            }
                        },
                        error: function(xhr, status, error) {
                            // console.error('AJAX error:', error);
                            alert('Failed to filter orders. Please try again.');
                        }
                    });
                });

            function fetchAllOrders() {
                loadOrders(1);
            }

            // Window resize handler to automatically apply responsive CSS
            let resizeTimer;
            let lastWidth = $(window).width();

            function handleResize() {
                const currentWidth = $(window).width();

                // Only process if width actually changed significantly (more than 5px)
                if (Math.abs(currentWidth - lastWidth) < 5) {
                    return;
                }
                lastWidth = currentWidth;

                // Trigger DataTables to recalculate column visibility and table layout
                if (table) {
                    // Remove all expandable rows first
                    $('tr.order-details-row').remove();

                    // Force DataTables to completely recalculate
                    try {
                        table.columns.adjust();
                        // Try responsive extension if available
                        if (table.responsive && typeof table.responsive.recalc === 'function') {
                            table.responsive.recalc();
                        }
                    } catch (e) {
                        // Fallback if responsive extension not available
                        table.columns.adjust();
                    }

                    // Force a reflow to ensure CSS media queries are recalculated
                    const orderTable = $('#order-table')[0];
                    if (orderTable) {
                        void orderTable.offsetHeight;
                    }

                    // Redraw table and recalculate
                    setTimeout(function() {
                        // Redraw table completely
                        table.draw(false);

                        // Force another recalculation after draw
                        table.columns.adjust();

                        // Check if we're on mobile/tablet (1024px or below)
                        const isMobileOrTablet = currentWidth <= 1024;

                        // Re-add expandable rows only if on mobile/tablet
                        if (isMobileOrTablet && window.orderDataMap) {
                            setTimeout(function() {
                                table.rows().every(function() {
                                    const row = this.node();
                                    const toggleBtn = $(row).find(
                                        '.mobile-toggle-btn-table');
                                    if (toggleBtn.length > 0) {
                                        const orderId = toggleBtn.data('order-id');
                                        const orderData = window.orderDataMap[orderId];
                                        if (orderData) {
                                            const expandableRow = $('<tr>')
                                                .addClass('order-details-row')
                                                .attr('data-order-id', orderId)
                                                .html(buildExpandableRowContent(orderData,
                                                    orderData.currencySymbol, orderData
                                                    .currencyPosition));
                                            $(row).after(expandableRow);
                                        }
                                    }
                                });
                            }, 50);
                        }
                    }, 100);
                }
            }

            // Make handleResize available globally for manual triggering
            window.refreshTableLayout = handleResize;

            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                // Use shorter delay for more responsive feel
                resizeTimer = setTimeout(handleResize, 100);
            });

            // Force initial check after page load
            setTimeout(function() {
                handleResize();
            }, 500);

            // Also trigger on orientation change for mobile devices
            $(window).on('orientationchange', function() {
                setTimeout(handleResize, 300);
            });

            // Use matchMedia to detect breakpoint changes more reliably
            const mobileQuery = window.matchMedia('(max-width: 767px)');
            const tabletQuery = window.matchMedia('(min-width: 768px) and (max-width: 1024px)');
            const desktopQuery = window.matchMedia('(min-width: 1025px)');

            function handleMediaChange() {
                handleResize();
            }

            // Listen for media query changes
            if (mobileQuery.addEventListener) {
                mobileQuery.addEventListener('change', handleMediaChange);
                tabletQuery.addEventListener('change', handleMediaChange);
                desktopQuery.addEventListener('change', handleMediaChange);
            } else {
                // Fallback for older browsers
                mobileQuery.addListener(handleMediaChange);
                tabletQuery.addListener(handleMediaChange);
                desktopQuery.addListener(handleMediaChange);
            }
        });

        function renderOrdersByMonthAndYear(data, selectedMonth, selectedYear) {
            let tableBody = [];
            let mobileOrders = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                const formattedDate = order.created_date || order.created_at || 'N/A';
                const amount = parseFloat(order.total_amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                const displayAmount = currencyPosition === 'right' ? amount + currencySymbol : currencySymbol +
                    amount;
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);

                let actionBtns = ``;

                // if (status === 'sales') {
                if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                    actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}" title="Make Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                            <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                        </svg>
                                    </a>`;

                }
                actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;
                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<a class="btn btn-sm btn-success me-2 convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        Convert to Sales
                    </a>`;
                }
                @if (app('hasPermission')(2, 'view'))

                    actionBtns += `
                                        <a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>
                                        </a>`;
                @endif
                // if (!order.has_payment || order.has_payment === 0) {
                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `
                                            <a class="me-3" href="/edit-sales/${order.id}">
                                                <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                </svg>

                                            </a>`;
                    }
                @endif
                // }
                actionBtns += `
                @if (app('hasPermission')(2, 'view'))

                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>

                @endif
                                        ${!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole) ? `
                                                                                        @if (app('hasPermission')(2, 'delete'))
                                                                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                                                                            </svg>
                                                                                        </a>
                                                                                        @endif

                                                                                    ` : ''}
    `
                // Your existing HTML table rendering here...
                const orderData = {
                    ...order,
                    created_date: formattedDate,
                    displayAmount: displayAmount,
                    currencySymbol: currencySymbol,
                    currencyPosition: currencyPosition
                };

                tableBody.push([
                    `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                        <span class="toggle-icon">+</span>
                    </button>`,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    getStatusBadge(order.quotation_status || 'sales', 'quotation'),
                    getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0),
                    getStatusBadge(order.payment_method, 'method'),
                    getStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return'),
                    displayAmount || '0.00',
                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                    actionBtns
                ]);

                // Store order data for expandable row
                if (!window.orderDataMap) {
                    window.orderDataMap = {};
                }
                window.orderDataMap[order.id] = orderData;

                // Add to mobile orders array
                const mobileOrder = {
                    ...order
                };
                mobileOrder.created_date = formattedDate;
                mobileOrders.push(mobileOrder);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();

            // Add expandable rows
            function addExpandableRowsForMonthYear() {
                table.rows().every(function() {
                    const row = this.node();
                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                    if (toggleBtn.length > 0) {
                        const orderId = toggleBtn.data('order-id');
                        const orderData = window.orderDataMap[orderId];
                        if (orderData && !$(row).next('tr.order-details-row[data-order-id="' + orderId + '"]')
                            .length) {
                            const expandableRow = $('<tr>')
                                .addClass('order-details-row')
                                .attr('data-order-id', orderId)
                                .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData
                                    .currencyPosition));
                            $(row).after(expandableRow);
                        }
                    }
                });
            }

            setTimeout(addExpandableRowsForMonthYear, 100);

            // Re-add expandable rows on table redraw
            table.off('draw').on('draw', function() {
                setTimeout(addExpandableRowsForMonthYear, 50);
            });

            // Render mobile cards
            renderMobileOrders(mobileOrders, currencySymbol, currencyPosition);
        }

        function renderOrders(data, selectedDate) {
            let tableBody = [];
            let mobileOrders = [];
            const currencySymbol = '₹';
            const currencyPosition = 'left';

            data.forEach(order => {
                if (!order.created_date) return;

                const orderDate = new Date(order.created_date).toISOString().split('T')[0]; // 'YYYY-MM-DD'

                // If selectedDate exists and doesn't match, skip this
                if (selectedDate && selectedDate !== orderDate) return;

                // Format and push to table
                let date = new Date(order.created_date);
                let day = String(date.getDate()).padStart(2, '0');
                let monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
                ];
                let month = monthNames[date.getMonth()];
                let year = date.getFullYear();

                let hours = date.getHours();
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;

                let amount = parseFloat(order.total_amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                let displayAmount = currencyPosition === 'right' ?
                    amount + currencySymbol : currencySymbol + amount;
                const status = String(order.quotation_status || 'sales').toLowerCase();
                const remaining = parseFloat(order.remaining_amount || 0);

                const formattedDate = `${day}-${month}-${year} ${hours}:${minutes} ${ampm}`;

                // 🔹 Build action buttons properly
                let actionBtns = ``;

                // if (status === 'sales') {
                if (parseFloat(order.remaining_amount || 0) > 0 && status === 'sales') {
                    actionBtns += `<a href="javascript:void(0);" class="me-3 make-payment-btn" data-bs-toggle="modal" data-bs-target="#makePaymentModal"
                                        data-id="${order.id}" data-amount="${order.remaining_amount}" data-method="${order.payment_method || ''}"
                                        data-emi-months="${order.remaining_emi_months}" data-emi-duration="${order.emi_duration || 0}"
                                        data-total-amount="${order.total_amount || 0}" data-remaining-amount="${order.remaining_amount}"
                                        data-return-amount="${order.total_return || 0}"
                                        data-remaining-emi-months="${order.remaining_emi_months}" title="Make Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#092C4C" viewBox="0 0 24 24">
                                            <path d="M21 7H3V5h18v2zm0 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9h18zm-2 4H5v6h14v-6zM8 12h2v2H8v-2zm6 0h2v2h-2v-2z"/>
                                        </svg>
                                    </a>`;

                }
                actionBtns += `<button class="btn open-history" data-id="${order.id}" title="Payment History">
                                                <i class="fas fa-history" style="font-size: 16px;"></i>
                                            </button>`;
                if ((order.quotation_status || '').toLowerCase() === 'quotation') {
                    actionBtns += `<a class="btn btn-sm btn-success me-2 convert-to-sales" href="javascript:void(0);" data-id="${order.id}" title="Convert to Sales">
                        Convert to Sales
                    </a>`;
                }

                @if (app('hasPermission')(2, 'view'))
                    actionBtns += `<a class="me-3" href="/sales-details/${order.id}">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
            </svg>


                                        </a>`;
                @endif
                // if (!order.has_payment || order.has_payment === 0) {
                @if (app('hasPermission')(2, 'edit'))
                    if (parseFloat(order.total_return || 0) === 0) {
                        actionBtns += `
                                            <a class="me-3" href="/edit-sales/${order.id}">
                                                <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                </svg>

                                            </a>`;
                    }
                @endif
                // }
                actionBtns += `
                @if (app('hasPermission')(2, 'view'))
                                        <a class="me-3" href="/sales-invoice/${order.id}">


            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" x="0" y="0" viewBox="0 0 32 32" style="enable-background:new 0 0 512 512" xml:space="preserve" class=""><g><path d="M28 24v-4a1 1 0 0 0-2 0v4a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1v-4a1 1 0 0 0-2 0v4a3 3 0 0 0 3 3h18a3 3 0 0 0 3-3zm-6.38-5.22-5 4a1 1 0 0 1-1.24 0l-5-4a1 1 0 0 1 1.24-1.56l3.38 2.7V6a1 1 0 0 1 2 0v13.92l3.38-2.7a1 1 0 1 1 1.24 1.56z" data-name="Download" fill="#092C4C" opacity="1" data-original="#092C4C" class=""></path></g></svg>
                                        </a>
                                        <a class="me-3" href="javascript:void(0);" onclick="window.open('/sales/invoice/pdf/' + ${order.id});"  title="Print Invoice">
            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="#092C4C" viewBox="0 0 24 24">
                <path d="M19 7V4a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v3H3a1 1 0 0 0-1 1v9a2 2 0 0 0 2 2h2v3h12v-3h2a2 2 0 0 0 2-2V8a1 1 0 0 0-1-1h-2zM7 4h10v3H7V4zm10 16H7v-4h10v4zm3-6a1 1 0 0 1-1 1h-2v-2H7v2H5a1 1 0 0 1-1-1V9h16v5z"/>
            </svg>
        </a>
                        @endif

                                         ${!['sales-manager', 'purchase-manager', 'inventory-manager'].includes(userRole) ? `
                                                                                                                                 @if (app('hasPermission')(2, 'delete'))
                                                                                        <a class="me-3 delete-order" href="javascript:void(0);" data-id="${order.id}">
                                                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                                <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                                                                <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                                                                            </svg>
                                                                                        </a>
                                                                                        @endif
                                                                                    ` : ''}
    `

                const orderData = {
                    ...order,
                    created_date: formattedDate,
                    displayAmount: displayAmount,
                    currencySymbol: currencySymbol,
                    currencyPosition: currencyPosition
                };

                tableBody.push([
                    `<a href="/sales-details/${order.id}" class="">${order.order_number || ''}</a>`,
                    `<button class="mobile-toggle-btn-table" onclick="toggleTableRowDetails('${order.id}')" data-order-id="${order.id}">
                        <span class="toggle-icon">+</span>
                    </button>`,
                    // formattedDate,
                    order.created_date || 'N/A',
                    order.user?.name || 'N/A',
                    getStatusBadge(order.quotation_status || 'sales', 'quotation'),
                    getStatusBadge(order.payment_status, 'payment', order.extra_paid || 0),
                    getStatusBadge(order.payment_method, 'method'),
                    getStatusBadge(parseFloat(order.total_return || 0) > 0 ? 'returned' : '', 'return'),
                    displayAmount || '0.00',
                    `<span class="biller-wrap">${order.biller || 'Admin'}</span>`,
                    actionBtns
                ]);

                // Store order data for expandable row
                if (!window.orderDataMap) {
                    window.orderDataMap = {};
                }
                window.orderDataMap[order.id] = orderData;

                // Add to mobile orders array (order already passed date filter above)
                const mobileOrder = {
                    ...order
                };
                mobileOrder.created_date = formattedDate;
                mobileOrders.push(mobileOrder);
            });

            const table = $('#order-table').DataTable();
            table.clear().rows.add(tableBody).draw();

            // Add expandable rows
            function addExpandableRowsForDate() {
                table.rows().every(function() {
                    const row = this.node();
                    const toggleBtn = $(row).find('.mobile-toggle-btn-table');
                    if (toggleBtn.length > 0) {
                        const orderId = toggleBtn.data('order-id');
                        const orderData = window.orderDataMap[orderId];
                        if (orderData && !$(row).next('tr.order-details-row[data-order-id="' + orderId + '"]')
                            .length) {
                            const expandableRow = $('<tr>')
                                .addClass('order-details-row')
                                .attr('data-order-id', orderId)
                                .html(buildExpandableRowContent(orderData, orderData.currencySymbol, orderData
                                    .currencyPosition));
                            $(row).after(expandableRow);
                        }
                    }
                });
            }

            setTimeout(addExpandableRowsForDate, 100);

            // Re-add expandable rows on table redraw
            table.off('draw').on('draw', function() {
                setTimeout(addExpandableRowsForDate, 50);
            });

            // Render mobile cards
            renderMobileOrders(mobileOrders, currencySymbol, currencyPosition);

            // Recalculate total after rendering
            setTimeout(function() {
                calculateFilteredTotal();
            }, 200);
        }
        // Global history modal open
        $(document).on('click', '.open-history', function() {
            var authToken = localStorage.getItem("authToken");

            const jobCardId = $(this).data('id');
            $('#globalPaymentHistoryList').html('<li class="list-group-item">Loading...</li>');
            $.ajax({
                url: '/api/order/payment-history/' + jobCardId,
                method: 'GET',
                headers: {
                    "Authorization": "Bearer " + authToken
                },
                success: function(response) {

                    const history = response.data || [];
                    const summary = response.summary || {};

                    let html = '';

                    if (history.length === 0) {
                        html = '<li class="list-group-item">No payment history found.</li>';
                    } else {
                        html = history.map(p => `
            <li class="list-group-item d-flex justify-content-between">
                <span>${p.payment_date || p.created_at}</span>
                <span>
                    <strong>₹${parseFloat(p.payment_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong>
                    (${p.payment_method || ''})
                </span>
            </li>
        `).join('');
                    }

                    html += `
        <li class="list-group-item mt-2 bg-light">
            <strong>Order Total:</strong> ₹${parseFloat(summary.order_total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
        <li class="list-group-item bg-light">
            <strong>Total Paid:</strong> ₹${parseFloat(summary.total_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
        <li class="list-group-item bg-light">
            <strong>Return Amount:</strong> ₹${parseFloat(summary.return_amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
        </li>
    `;

                    if (summary.extra_paid > 0) {
                        html += `
            <li class="list-group-item bg-warning">
                <strong>Extra Paid:</strong>
                ₹${parseFloat(summary.extra_paid).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
                <span class="text-danger">(Advance / Refund)</span>
            </li>
        `;
                    } else {
                        html += `
            <li class="list-group-item bg-light">
                <strong>Remaining:</strong>
                ₹${parseFloat(summary.remaining).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}
            </li>
        `;
                    }

                    $('#globalPaymentHistoryList').html(html);

                    new bootstrap.Modal(document.getElementById('paymentHistoryModal'))
                        .show();
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
        $(document).on('click', '.delete-order', function() {
            var orderId = $(this).data('id'); // ✅ Correct usage
            var authToken = localStorage.getItem("authToken");

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
                        url: 'api/delete/' + orderId,
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                            "Authorization": "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status === true) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: '#ff9f43', // Set OK button color here
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: response.error,
                                    icon: "error",
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = "An error occurred while deleting the order";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            Swal.fire({
                                title: "Error",
                                text: errorMessage,
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }

                    });
                }
            });
        });

        $(document).on('click', '.convert-to-sales', function() {
            const orderId = $(this).data('id');
            const authToken = localStorage.getItem("authToken");

            Swal.fire({
                title: "Are you sure?",
                text: "You want to convert this quotation to sales!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, convert it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: '/api/convert-quotation-to-sale/' + orderId,
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: "Converted!",
                                text: response.message ||
                                    "Quotation converted to sales successfully.",
                                icon: "success",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response.message ||
                                    "Failed to convert quotation.",
                                icon: "error",
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "Failed to convert quotation.";
                        if (xhr.responseJSON && (xhr.responseJSON.message || xhr.responseJSON
                                .error)) {
                            errorMessage = xhr.responseJSON.message || xhr.responseJSON.error;
                        }

                        Swal.fire({
                            title: "Error",
                            text: errorMessage,
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });

        const $downloadLoader = $("#downloadLoaderOverlay");
        const $downloadLoaderText = $("#downloadLoaderText");
        const $exportButtons = $("#exportPdf, #exportAllChallan");

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

        $('#exportPdf').click(function() {
            var authToken = localStorage.getItem("authToken");
            let selectedYearRaw = $('#filter-year').val() || '';
            let selectedMonthRaw = $('#filter-month').val() || '';
            let selectedYear = normalizeFilterValue(selectedYearRaw);
            let selectedMonth = normalizeFilterValue(selectedMonthRaw);
            let selectedDate = $('#filter-date').val() || '';
            let selectedCustomerId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            $.ajax({
                url: `/api/pdf-orders`,
                type: 'GET',
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
                    customerId: selectedCustomerId,
                    selectedSubAdminId: selectedSubAdminId,
                    type: 'pdf'
                },
                success: function(response) {
                    if (response.status && response.file_url) {
                        // Open PDF in new tab or trigger download
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name || 'sales_report.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Failed to generate PDF: " + response.message,
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        title: "Error",
                        text: "PDF export failed: " + (xhr.responseJSON?.message ??
                            "Unknown error"),
                        icon: "error",
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    toggleDownloadLoader(false);
                }
            });
        });
        $('#exportAllChallan').click(function() {
            let selectedYearRaw = $('#filter-year').val() || '';
            let selectedMonthRaw = $('#filter-month').val() || '';
            let selectedYear = normalizeFilterValue(selectedYearRaw);
            let selectedMonth = normalizeFilterValue(selectedMonthRaw);
            let selectedDate = $('#filter-date').val() || '';
            let selectedVendorId = $('#filter-customer').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-order?year=${selectedYear}&month=${selectedMonth}&date=${selectedDate}&customer_id=${selectedVendorId}&selectedSubAdminId=${selectedSubAdminId}&format_currency=indian`;
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
                        // Trigger download via file_url
                        const link = document.createElement('a');
                        link.href = response.file_url;
                        link.download = response.file_name; // optional
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        Swal.fire({
                            title: "Error",
                            text: "Export failed: " + (response.message || "Unknown error"),
                            icon: "error",
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    // console.error("Export failed:", xhr.responseText);
                    Swal.fire({
                        title: "Error",
                        text: "Export failed. Please try again.",
                        icon: "error",
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                },
                complete: function() {
                    toggleDownloadLoader(false);
                }
            });
        });
    </script>
@endpush
