@extends('layout.app')
@section('title', 'Salary List')
@section('content')
    <style>
        .form-control {
            color: #595b5d !important;
        }

        .form-group {
            margin-bottom: 8px !important;
        }

        /* Staff Name column - word wrap */
        .datanew td:nth-child(1) {
            white-space: normal !important;
            word-wrap: break-word;
            max-width: 200px;
            /* adjust as needed */
            min-width: 120px;
        }

        .datanew th:nth-child(1) {
            white-space: normal;
            word-wrap: break-word;
        }

        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            /* Filter Section Mobile Styles */
            /* .card.p-4.shadow-sm .row {
                                                flex-direction: column !important;
                                                gap: 10px !important;
                                            }

                                            .card.p-4.shadow-sm .col-lg-2,
                                            .card.p-4.shadow-sm .col-md-3,
                                            .card.p-4.shadow-sm .col-sm-6,
                                            .card.p-4.shadow-sm .col-lg-6,
                                            .card.p-4.shadow-sm .col-md-6,
                                            .card.p-4.shadow-sm .col-sm-12 {
                                                width: 100% !important;
                                                margin-bottom: 10px;
                                            }

                                            .card.p-4.shadow-sm .d-flex.gap-2 {
                                                flex-direction: column;
                                                width: 100%;
                                            }

                                            .card.p-4.shadow-sm .d-flex.gap-2 button {
                                                width: 100% !important;
                                                margin-bottom: 8px;
                                            }

                                            .table-responsive {
                                                display: block !important;
                                                overflow-x: auto;
                                                -webkit-overflow-scrolling: touch;
                                                width: 100% !important;
                                            } */

            .datanew {
                font-size: 11px;
                width: 100% !important;
                table-layout: fixed;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Make Staff Name column take more space */
            .datanew th:nth-child(1),
            .datanew td:nth-child(1) {
                width: 70% !important;
                text-align: left;
            }

            /* Make Details column take less space */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                width: 30% !important;
                text-align: center;
                display: table-cell !important;
            }

            /* Show only Staff Name and Details - hide all other columns including Action */
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
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            /* .card.p-4.shadow-sm .row {
                                                flex-wrap: wrap !important;
                                                gap: 10px !important;
                                            }

                                            .card.p-4.shadow-sm .col-lg-2,
                                            .card.p-4.shadow-sm .col-md-3 {
                                                flex: 0 0 calc(50% - 5px);
                                            }

                                            .card.p-4.shadow-sm .d-flex.gap-2 {
                                                width: 100% !important;
                                                flex-direction: row;
                                            }

                                            .card.p-4.shadow-sm .d-flex.gap-2 button {
                                                flex: 1;
                                            }

                                            .table-responsive {
                                                display: block !important;
                                                overflow-x: auto;
                                                width: 100% !important;
                                            } */

            .datanew {
                font-size: 12px;
                width: 100% !important;
                table-layout: fixed;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Make columns take appropriate space */
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

            /* Show Staff Name, Details, Present - hide Action and other columns */
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
        }

        /* Medium devices (tablets, 768px and up to 991px) */
        @media screen and (min-width: 768px) and (max-width: 991.98px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
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

            .datanew td:last-child {
                white-space: nowrap !important;
                min-width: 180px !important;
            }

            .datanew td:last-child .btn,
            .datanew td:last-child button {
                padding: 4px 8px !important;
                font-size: 11px !important;
                margin: 2px 2px !important;
            }

            /* Hide Details column on tablets */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
            }

            /* Show all other columns on tablets - no hiding */
            /* All columns (Staff Name, Present, Absent, Extra Present, Pending Advance Pay, Paid Advance Pay, Status, Action) will be visible */

            /* Hide expandable rows on tablets */
            .order-details-row {
                display: none !important;
            }
        }


        @media screen and (max-width: 767.98px) {
            .datanew td:last-child {
                white-space: normal !important;
                min-width: auto !important;
            }

            .datanew td:last-child .btn,
            .datanew td:last-child button {
                display: block !important;
                width: 100% !important;
                margin: 5px 0 !important;
                text-align: center !important;
            }

            .datanew td:last-child .make-bonus-payment {
                margin-right: 0 !important;
            }
        }

        /* Large devices (desktops, 992px and up) */
        @media screen and (min-width: 992px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
                width: 100% !important;
            }

            .datanew {
                font-size: 14px;
                width: 100% !important;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column (2nd column) on desktop (992px and above) */
            .datanew th:nth-child(2),
            .datanew td:nth-child(2) {
                display: none !important;
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

        .bg-lightyellow {
            background-color: #fff3cd;
            color: #856404;
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

        /* ✅ Hide default DataTables search box completely */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        /* Action Buttons Container Styling */
        .datanew td:last-child {
            white-space: nowrap !important;
            min-width: 200px;
        }

        /* Action buttons container - make them inline */
        .datanew td:last-child .btn,
        .datanew td:last-child button {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            margin: 2px 4px !important;
            white-space: nowrap !important;
        }

        /* Make Payment button specific styling */
        .datanew td:last-child .make-payment {
            background-color: #ff9f43 !important;
            border-color: #ff9f43 !important;
            color: white !important;
            padding: 5px 12px !important;
            font-size: 12px !important;
        }

        /* Edit Salary button styling */
        .datanew td:last-child .make-bonus-payment {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            color: white !important;
            padding: 5px 12px !important;
            font-size: 12px !important;
            margin-right: 8px !important;
        }

        /* Download button styling */
        .datanew td:last-child .download-slip {
            background-color: #dc3545 !important;
            border-color: #dc3545 !important;
            color: white !important;
            padding: 5px 12px !important;
            font-size: 12px !important;
        }

        /* ======================================
           SALARY FILTER CARD - RESPONSIVE FIX
        ====================================== */

        /* Remove extra padding on mobile */
        @media (max-width: 767.98px) {
            .card.p-4.shadow-sm {
                padding: 12px !important;
            }

            .card.p-4.shadow-sm .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            .card-body {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }

            #selectedMonthYearDisplay {
                font-size: 13px;
                margin-bottom: 8px !important;
            }

            .mob {
                width: 50%;
            }
        }

        /* ======================================
           SALARY ACTION BUTTONS
        ====================================== */
        .salary-btn-group {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            width: 100%;
        }

        .salary-action-btn {
            white-space: nowrap;
            font-size: 12px;
            font-weight: 500;
            padding: 5px 10px;
            flex-shrink: 0;
        }

        /* Desktop >= 992px: all in one row, no wrap */
        @media (min-width: 992px) {
            .salary-btn-group {
                flex-wrap: nowrap;
            }

            .salary-action-btn {
                font-size: 13px;
                padding: 6px 12px;
            }
        }

        /* Tablet 768–991px: Excel+PDF same row, History full width below */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .salary-action-btn {
                font-size: 12px;
                padding: 5px 10px;
            }

            .salary-history-btn {
                flex: 1 1 100%;
                text-align: center;
                justify-content: center;
            }
        }

        /* Mobile < 768px: Excel+PDF in same row, History full width */
        @media (max-width: 767.98px) {
            .salary-btn-group {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 5px;
            }

            /* Excel and PDF side by side */
            #exportCSV,
            #exportPDF {
                flex: 1 1 calc(50% - 3px) !important;
                text-align: center;
                justify-content: center;
                display: flex !important;
                align-items: center;
                font-size: 12px !important;
                padding: 6px 5px !important;
            }

            /* History full width below */
            #advanceHistory {
                flex: 1 1 100% !important;
                width: 100% !important;
                text-align: center;
                justify-content: center;
                display: flex !important;
                align-items: center;
                font-size: 12px !important;
                padding: 6px 5px !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Salaries</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- <div class="mb-4">
                    <div class="card p-4 shadow-sm">
                        <div class="row g-3 align-items-end">

                            <!-- Staff Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterStaff" class="form-label">Staff</label>
                                <select class="form-select form-select-sm" id="filterStaff">
                                    <option value="">All Staff</option>
                                    <!-- Filled via JS -->
                                </select>
                            </div>

                            <!-- Month Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterMonth" class="form-label">Month</label>
                                <select class="form-select form-select-sm" id="filterMonth">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}"
                                            {{ $m == now()->subMonth()->month ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Year Filter -->
                            <div class="col-lg-2 col-md-3 col-sm-6">
                                <label for="filterYear" class="form-label">Year</label>
                                <select class="form-select form-select-sm" id="filterYear">
                                    <option value="">Select Year</option>
                                    <!-- Filled via JS -->
                                </select>
                            </div>

                            <!-- Export Buttons -->
                            <div class="col-lg-6 col-md-6 col-sm-12 text-lg-end text-md-start">
                                <strong id="selectedMonthYearDisplay" class="text-dark">
                                    Salaries for {{ \Carbon\Carbon::now()->format('F Y') }}
                                </strong>
                                <label class="form-label d-none d-lg-block">&nbsp;</label>
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <button id="exportCSV" class="btn btn-sm btn-success">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button id="exportPDF" class="btn btn-sm btn-danger">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                    <button id="advanceHistory" class="btn btn-sm btn-primary">
                                        <i class="fas fa-history me-1"></i> Staff Advance Payment History
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div> --}}

                <div class="mb-4">
                    <div class="card p-4 shadow-sm">
                        <div class="row g-3 align-items-end">



                            <!-- Staff -->
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label">Staff</label>
                                <select class="form-select form-select-sm" id="filterStaff">
                                    <option value="">All Staff</option>
                                </select>
                            </div>

                            <!-- Month -->
                            <div class="col-lg-2 col-md-3 col-6">
                                <label class="form-label">Month</label>
                                <select class="form-select form-select-sm" id="filterMonth">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Year (full width on mobile) -->
                            <div class="col-lg-2 col-md-3 col-12">
                                <label class="form-label">Year</label>
                                <select class="form-select form-select-sm" id="filterYear">
                                    <option value="">Select Year</option>
                                </select>
                            </div>

                            <!-- Buttons Section -->
                            <div class="col-12 col-lg-6 col-md-6">
                                <strong id="selectedMonthYearDisplay" class="text-dark d-block mb-2">
                                    Salaries for {{ \Carbon\Carbon::now()->format('F Y') }}
                                </strong>
                                <div class="salary-btn-group">
                                    <button id="exportCSV" class="btn btn-sm btn-success salary-action-btn">
                                        <i class="fas fa-file-excel me-1"></i> Export Excel
                                    </button>
                                    <button id="exportPDF" class="btn btn-sm btn-danger salary-action-btn">
                                        <i class="fas fa-file-pdf me-1"></i> Export PDF
                                    </button>
                                    <button id="advanceHistory"
                                        class="btn btn-sm btn-primary salary-action-btn salary-history-btn">
                                        <i class="fas fa-history me-1"></i> Staff Advance Payment History
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- Bootstrap Nav Tabs -->
                <ul class="nav nav-tabs mb-3" id="salaryTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending"
                            type="button" role="tab">Pending Salary</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="paid-tab" data-bs-toggle="tab" data-bs-target="#paid" type="button"
                            role="tab">Paid Salary</button>
                    </li>
                </ul>

                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-12">
                    <div class="search-input">
                        <a class="btn btn-searchset">
                            <img class="mt-1" src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                alt="img">
                        </a>
                        <input type="text" id="search-input" class="form-control mob" placeholder="Search...">
                    </div>
                </div>


                <div class="tab-content pt-3">
                    <!-- Pending Salary Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datanew" id="pendingTable">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th class="text-center">Details</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Extra Present</th>
                                        <th>Pending Advance Pay</th>
                                        <th>Paid Advance Pay</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paid Salary Tab -->
                    <div class="tab-pane fade" id="paid" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table datanew" id="paidTable">
                                <thead>
                                    <tr>
                                        <th>Staff Name</th>
                                        <th class="text-center">Details</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Extra Present</th>
                                        <th>Pending Advance Pay</th>
                                        <th>Paid Advance Pay</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
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
    <!-- Modal -->
    <!-- Salary Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-dark">
                    <h5 class="modal-title" id="paymentModalLabel">Make Salary Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>
                <div class="modal-body">
                    <!-- Display Staff Info -->
                    <div class="d-flex justify-content-between mb-3 gap-3">
                        <div class="flex-fill">
                            <label class="mb-1">Staff Name:</label>
                            <div class="bg-light p-2 rounded border" id="staffNameDisplay"></div>
                        </div>
                        <div class="flex-fill">
                            <label class="mb-1">Advance Payment:</label>
                            <div class="bg-light p-2 rounded border" id="advancePaymentDisplay"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="mb-1">Present Days:</label>
                            <div class="bg-light p-2 rounded border" id="presentDaysDisplay"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Absent Days:</label>
                            <div class="bg-light p-2 rounded border" id="absentDaysDisplay"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Extra Days:</label>
                            <div class="bg-light p-2 rounded border" id="extraDaysDisplay"></div>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <form id="paymentForm">
                        <input type="hidden" id="staffId">
                        <input type="hidden" id="old_advance_pay">

                        <div class="d-flex justify-content-between mb-3 gap-3">
                            <div class="flex-fill">
                                <label for="salaryAmount" class="form-label">Salary Amount</label>
                                <input type="number" class="form-control" id="salaryAmount"
                                    placeholder="Enter salary amount">
                                <div class="text-danger small" id="errorSalaryAmount"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="extraAmount" class="form-label">Extra Amount</label>
                                <input type="number" class="form-control" id="extraAmount" value="0">
                                <div class="text-danger small" id="errorExtraAmount"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="advancePaid" class="form-label">Advance Paid</label>
                                <input type="number" class="form-control" id="advancePaid" value="0">
                                <div class="text-danger small" id="errorAdvancePaid"></div>
                            </div>
                            <div class="flex-fill">
                                <label class="mb-1">Pending Advance</label>
                                <input type="number" class="form-control" id="pendingAdvance" value="0" disabled>
                            </div>

                        </div>

                        <div class="mb-3">
                            <label for="grandTotal" class="form-label">Grand Total</label>
                            <input type="number" class="form-control" id="grandTotal" readonly>
                            <div class="text-danger small" id="errorGrandTotal"></div>
                        </div>
                    </form>
                </div>
                <div style="padding: 1rem;">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-submit" id="savePayment">Save</button>
                    {{-- <button type="button" class="btn btn-danger" id="printPayment">Print</button> --}}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal2" tabindex="-1" aria-labelledby="paymentModalLabel2" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-dark">
                    <h5 class="modal-title" id="paymentModalLabel2">Edit Salary</h5>
                </div>
                <div class="modal-body">
                    <!-- Display Staff Info -->
                    <div class="d-flex justify-content-between mb-3 gap-3">
                        <div class="flex-fill">
                            <label class="mb-1">Staff Name:</label>
                            <div class="bg-light p-2 rounded border" id="staffNameDisplay2"></div>
                        </div>
                        <div class="flex-fill">
                            <label class="mb-1">Advance Payment:</label>
                            <div class="bg-light p-2 rounded border" id="advancePaymentDisplay2"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label class="mb-1">Present Days:</label>
                            <div class="bg-light p-2 rounded border" id="presentDaysDisplay2"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Absent Days:</label>
                            <div class="bg-light p-2 rounded border" id="absentDaysDisplay2"></div>
                        </div>
                        <div class="col">
                            <label class="mb-1">Extra Days:</label>
                            <div class="bg-light p-2 rounded border" id="extraDaysDisplay2"></div>
                        </div>
                    </div>

                    <!-- Input Fields -->
                    <form id="paymentForm2">
                        <input type="hidden" id="staffId2">
                        <input type="hidden" id="salaryId2">

                        <div class="d-flex justify-content-between mb-3 gap-3">
                            <div class="flex-fill">
                                <label for="salaryAmount2" class="form-label">Salary Amount</label>
                                <input type="number" class="form-control" id="salaryAmount2"
                                    placeholder="Enter salary amount">
                                <div class="text-danger small" id="errorSalaryAmount2"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="extraAmount2" class="form-label">Extra Amount</label>
                                <input type="number" class="form-control" id="extraAmount2" value="0">
                                <div class="text-danger small" id="errorExtraAmount2"></div>
                            </div>
                            <div class="flex-fill">
                                <label for="advancePaid2" class="form-label">Advance Paid</label>
                                <input type="number" class="form-control" id="advancePaid2" value="0">
                                <div class="text-danger small" id="errorAdvancePaid2"></div>
                            </div>
                            <div class="flex-fill">
                                <label class="mb-1">Pending Advance</label>
                                <input type="number" class="form-control" id="pendingAdvance2" value="0" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="grandTotal2" class="form-label">Grand Total</label>
                            <input type="number" class="form-control" id="grandTotal2" readonly>
                            <div class="text-danger small" id="errorGrandTotal2"></div>
                        </div>
                    </form>
                </div>
                <div style="padding: 1rem;">
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-submit" id="savePayment2">Save</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Advance Payment History Modal -->
    <div class="modal fade" id="advanceHistoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Staff Advance Payment History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                </div>

                <div class="modal-body">
                    <!-- Staff Dropdown -->
                    <div class="mb-3">
                        <label for="historyStaff" class="form-label">Select Staff</label>
                        <select class="form-select" id="historyStaff">
                            <option value="">-- Select Staff --</option>
                        </select>
                    </div>

                    <!-- History Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered" id="historyTable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Pending Amount</th>
                                    <th>Method</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No history available</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="padding: 1rem;">
                    {{-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> --}}
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Close</button>
                    {{-- <button type="button" class="btn btn-submit" id="savePayment">Save</button> --}}
                    {{-- <button type="button" class="btn btn-danger" id="printPayment">Print</button> --}}
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script>
        $(document).ready(function() {
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            // console.log(selectedSubAdminId);
            // Handle payment action

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let selectedMonth = $('#filterMonth').val() || (new Date().getMonth() + 1);
            let selectedYear = $('#filterYear').val() || new Date().getFullYear();
            let selectedStaffId = '';

            // Initialize DataTables without pagination
            const pendingTable = $('#pendingTable').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false
            });
            const paidTable = $('#paidTable').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false
            });

            // Global salary data map
            // window.salaryDataMap = {};



            $(document).on('click', '.make-payment', function() {
                const staffId = $(this).data('staff-id');
                const staffName = $(this).data('staff-name');
                const presentDays = $(this).data('present');
                const absentDays = $(this).data('absent');
                const extraDays = $(this).data('extra-present');
                const salary = $(this).data('salary');
                const extra_amount = $(this).data('extra-amount');
                const advance_pay = $(this).data('advance-paid');
                const pendingAdvance = $(this).data('pending-advance');

                // Display values in modal
                $('#staffNameDisplay').text(staffName);
                $('#presentDaysDisplay').text(`${presentDays} day(s)`).data('value', presentDays);
                $('#absentDaysDisplay').text(`${absentDays} day(s)`).data('value', absentDays);
                $('#extraDaysDisplay').text(`${extraDays} day(s)`).data('value', extraDays);

                $('#advancePaymentDisplay').text(pendingAdvance);
                $('#advancePaid').val(0).prop('disabled', pendingAdvance == 0);
                $('#pendingAdvance').val(pendingAdvance);
                $('#old_advance_pay').val(pendingAdvance);

                // ✅ Disable Extra Amount input if no extra days
                if (extraDays == 0) {
                    $('#extraAmount').val(0).prop('readonly', true);
                } else {
                    $('#extraAmount').prop('disabled', false);
                }

                // Reset others
                $('#staffId').val(staffId);
                $('#salaryAmount').val('');
                $('#grandTotal').val('');

                $('#paymentModal').modal('show');
            });

            $(document).on('click', '.make-bonus-payment', function() {
                const staffId = $(this).data('staff-id');
                const salaryId = $(this).data('salary-id');
                const staffName = $(this).data('staff-name');
                const presentDays = $(this).data('present');
                const absentDays = $(this).data('absent');
                const extraDays = $(this).data('extra-present');
                const salary = parseFloat($(this).data('salary')) || 0;
                const extra_amount = parseFloat($(this).data('extra-amount')) || 0;
                const advance_pay = parseFloat($(this).data('advance-paid')) || 0;
                const pendingAdvance = parseFloat($(this).data('pending-advance')) || 0;

                // Display info
                $('#staffNameDisplay2').text(staffName);
                $('#presentDaysDisplay2').text(`${presentDays} day(s)`);
                $('#absentDaysDisplay2').text(`${absentDays} day(s)`);
                $('#extraDaysDisplay2').text(`${extraDays} day(s)`);

                $('#advancePaymentDisplay2').text(pendingAdvance.toFixed(2));
                $('#pendingAdvance2').val(pendingAdvance.toFixed(2));

                // Set input values
                $('#salaryAmount2').val(salary);
                $('#advancePaid2').val(advance_pay);
                $('#extraAmount2').val(extra_amount);

                // Disable if needed
                $('#advancePaid2').prop('disabled', pendingAdvance == 0);
                $('#extraAmount2').prop('readonly', extraDays == 0);

                // Hidden ID
                $('#staffId2').val(staffId);
                $('#salaryId2').val(salaryId);

                // ✅ Trigger calculation once when modal opens
                calculateGrandTotal2();

                $('#paymentModal2').modal('show');
            });

            // ✅ Function to calculate totals for modal 2
            function calculateGrandTotal2() {
                const salaryAmount = parseFloat($('#salaryAmount2').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount2').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid2').val()) || 0;
                const advancePayment = parseFloat($('#advancePaymentDisplay2').text()) || 0;

                const grandTotal = salaryAmount + extraAmount - advancePaid;
                $('#grandTotal2').val(grandTotal.toFixed(2));

                const pendingAdvance = advancePayment - advancePaid;
                $('#pendingAdvance2').val(pendingAdvance >= 0 ? pendingAdvance.toFixed(2) : 0);
            }

            // ✅ Live update when typing
            $(document).on('input', '#salaryAmount2, #extraAmount2, #advancePaid2', function() {
                calculateGrandTotal2();
            });




            // Calculate grand total
            // Calculate grand total and pending advance
            // $('#salaryAmount, #extraAmount, #advancePaid').on('input', function() {
            //     const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
            //     const extraAmount = parseFloat($('#extraAmount').val()) || 0;
            //     const advancePaid = parseFloat($('#advancePaid').val()) || 0;
            //     const advancePayment = parseFloat($('#advancePaymentDisplay').text()) || 0;

            //     const grandTotal = salaryAmount + extraAmount - advancePaid;
            //     $('#grandTotal').val(grandTotal);

            //     const pendingAdvance = advancePayment - advancePaid;
            //     $('#pendingAdvance').val(pendingAdvance >= 0 ? pendingAdvance : 0);
            // });

            $(document).on('input', '#salaryAmount, #extraAmount, #advancePaid', function() {
                const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid').val()) || 0;
                const advancePayment = parseFloat($('#advancePaymentDisplay').text()) || 0;

                const grandTotal = salaryAmount + extraAmount - advancePaid;
                $('#grandTotal').val(grandTotal);

                const pendingAdvance = advancePayment - advancePaid;
                $('#pendingAdvance').val(pendingAdvance >= 0 ? pendingAdvance : 0);
            });




            $('#savePayment').on('click', function() {
                // ✅ Clear previous errors
                $('.text-danger.small').text('');
                $('.form-control').removeClass('is-invalid');

                const $btn = $(this);

                const staffId = $('#staffId').val();
                const salaryAmount = parseFloat($('#salaryAmount').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid').val()) || 0;
                const total = parseFloat($('#grandTotal').val()) || 0;
                const present = $('#presentDaysDisplay').data('value');
                const absent = $('#absentDaysDisplay').data('value');
                const extraPresent = $('#extraDaysDisplay').data('value');

                const monthselected = $('#filterMonth').val();
                let selectedYear1 = $('#filterYear').val();
                let old_advance_pay = $('#old_advance_pay').val();

                // console.log(old_advance_pay);


                // ✅ Check if present days = 0
                // if (present === 0) {
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Error',
                //         text: 'Payment cannot be processed because present days are 0.',
                //     });
                //     return;
                // }
                if (present === 0 && extraPresent === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Payment cannot be processed because both present days and extra present days are 0.',
                    });
                    return;
                }

                // Show loading spinner
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop('disabled', true);

                // $.ajax({
                //     url: "/api/salaries/pay",
                //     type: "POST",
                //     headers: {
                //         "Authorization": "Bearer " + localStorage.getItem("authToken"),
                //     },
                //     data: {
                //         staff_id: staffId,
                //         present,
                //         absent,
                //         extra_present: extraPresent,
                //         salary: salaryAmount,
                //         extra_amount: extraAmount,
                //         advance_pay: advancePaid,
                //         total_salary: total,
                //         selectedSubAdminId: selectedSubAdminId,
                //         monthselected: monthselected,
                //         selectedYear1: selectedYear1,
                //         old_advance_pay: old_advance_pay,
                //     },
                //     success: function(res) {
                //         if (res.status) {
                //             Swal.fire({
                //                 icon: 'success',
                //                 title: 'Success',
                //                 text: res.message,
                //                 timer: 2000,
                //                 showConfirmButton: false
                //             }).then(() => {
                //                 if (res.pdf) {
                //                     const byteCharacters = atob(res.pdf);
                //                     const byteNumbers = new Array(byteCharacters
                //                         .length);
                //                     for (let i = 0; i < byteCharacters.length; i++) {
                //                         byteNumbers[i] = byteCharacters.charCodeAt(i);
                //                     }
                //                     const byteArray = new Uint8Array(byteNumbers);
                //                     const blob = new Blob([byteArray], {
                //                         type: 'application/pdf'
                //                     });
                //                     const blobUrl = URL.createObjectURL(blob);
                //                     const a = document.createElement('a');
                //                     a.href = blobUrl;
                //                     a.download =
                //                         `salary_slip_${staffId}_${new Date().getMonth() + 1}_${new Date().getFullYear()}.pdf`;
                //                     document.body.appendChild(a);
                //                     a.click();
                //                     document.body.removeChild(a);
                //                     URL.revokeObjectURL(blobUrl);
                //                 }else if (res.file_url) {
                //         // If PDF URL is returned
                //         window.open(res.file_url, '_blank');
                //     }
                //                 location.reload();
                //             });
                //             $('#paymentModal').modal('hide');
                //         } else {
                //             Swal.fire({
                //                 icon: 'error',
                //                 title: 'Error',
                //                 text: res.message,
                //             });
                //         }
                //     },
                //     error: function(xhr) {
                //         const response = xhr.responseJSON;

                //         if (xhr.status === 422 && response.errors) {
                //             const errors = response.errors;

                //             if (errors.salary) {
                //                 $('#errorSalaryAmount').text(errors.salary[0]);
                //                 $('#salaryAmount').addClass('is-invalid');
                //             }
                //             if (errors.extra_amount) {
                //                 $('#errorExtraAmount').text(errors.extra_amount[0]);
                //                 $('#extraAmount').addClass('is-invalid');
                //             }
                //             if (errors.advance_pay) {
                //                 $('#errorAdvancePaid').text(errors.advance_pay[0]);
                //                 $('#advancePaid').addClass('is-invalid');
                //             }
                //             if (errors.total_salary) {
                //                 $('#errorGrandTotal').text(errors.total_salary[0]);
                //                 $('#grandTotal').addClass('is-invalid');
                //             }
                //         } else {
                //             Swal.fire({
                //                 icon: 'error',
                //                 title: 'Error',
                //                 text: response?.message ||
                //                     'Error occurred while saving salary.',
                //             });
                //         }
                //     },
                //     complete: function() {
                //         $btn.html('Save').prop('disabled', false);
                //     }
                // });

                $.ajax({
                    url: "/api/salaries/pay",
                    type: "POST",
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem("authToken"),
                    },
                    data: {
                        staff_id: staffId,
                        present,
                        absent,
                        extra_present: extraPresent,
                        salary: salaryAmount,
                        extra_amount: extraAmount,
                        advance_pay: advancePaid,
                        total_salary: total,
                        selectedSubAdminId: selectedSubAdminId,
                        monthselected: monthselected,
                        selectedYear1: selectedYear1,
                        old_advance_pay: old_advance_pay,
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Check if PDF data is returned
                                if (res.pdf_url) {
                                    // If PDF URL is returned, download it
                                    window.open(res.pdf_url, '_blank');
                                } else if (res.pdf) {
                                    // If PDF is returned as base64
                                    try {
                                        const byteCharacters = atob(res.pdf);
                                        const byteNumbers = new Array(byteCharacters
                                            .length);
                                        for (let i = 0; i < byteCharacters
                                            .length; i++) {
                                            byteNumbers[i] = byteCharacters.charCodeAt(
                                                i);
                                        }
                                        const byteArray = new Uint8Array(byteNumbers);
                                        const blob = new Blob([byteArray], {
                                            type: 'application/pdf'
                                        });
                                        const blobUrl = URL.createObjectURL(blob);
                                        const a = document.createElement('a');
                                        a.href = blobUrl;
                                        a.download =
                                            `salary_slip_${staffId}_${monthselected}_${selectedYear1}.pdf`;
                                        document.body.appendChild(a);
                                        a.click();
                                        document.body.removeChild(a);
                                        URL.revokeObjectURL(blobUrl);
                                    } catch (e) {
                                        console.error('Error downloading PDF:', e);
                                    }
                                }

                                // Refresh the page to show updated data
                                location.reload();
                            });
                            $('#paymentModal').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;

                        if (xhr.status === 422 && response.errors) {
                            const errors = response.errors;

                            if (errors.salary) {
                                $('#errorSalaryAmount').text(errors.salary[0]);
                                $('#salaryAmount').addClass('is-invalid');
                            }
                            if (errors.extra_amount) {
                                $('#errorExtraAmount').text(errors.extra_amount[0]);
                                $('#extraAmount').addClass('is-invalid');
                            }
                            if (errors.advance_pay) {
                                $('#errorAdvancePaid').text(errors.advance_pay[0]);
                                $('#advancePaid').addClass('is-invalid');
                            }
                            if (errors.total_salary) {
                                $('#errorGrandTotal').text(errors.total_salary[0]);
                                $('#grandTotal').addClass('is-invalid');
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.message ||
                                    'Error occurred while saving salary.',
                            });
                        }
                    },
                });

            });

            $('#savePayment2').on('click', function() {
                const $btn = $(this);
                const salaryId = $('#salaryId2').val(); // Hidden field for ID

                // Clear previous errors
                $('.text-danger.small').text('');
                $('.form-control').removeClass('is-invalid');

                const staffId = $('#staffId2').val();
                const salaryAmount = parseFloat($('#salaryAmount2').val()) || 0;
                const extraAmount = parseFloat($('#extraAmount2').val()) || 0;
                const advancePaid = parseFloat($('#advancePaid2').val()) || 0;
                const total = parseFloat($('#grandTotal2').val()) || 0;
                const present = $('#presentDaysDisplay2').data('value') || 0;
                const absent = $('#absentDaysDisplay2').data('value') || 0;
                const extraPresent = $('#extraDaysDisplay2').data('value') || 0;

                const monthselected = $('#filterMonth').val();
                const selectedYear1 = $('#filterYear').val();

                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...'
                    )
                    .prop('disabled', true);

                $.ajax({
                    url: `/api/salaries/update/${salaryId}`,
                    type: "PUT",
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem("authToken"),
                    },
                    data: {
                        staff_id: staffId,
                        present: present,
                        absent: absent,
                        extra_present: extraPresent,
                        salary: salaryAmount,
                        extra_amount: extraAmount,
                        advance_pay: advancePaid,
                        total_salary: total,
                        selectedSubAdminId: selectedSubAdminId,
                        monthselected: monthselected,
                        selectedYear1: selectedYear1,
                    },
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Updated!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {

                                location.reload();
                            });
                            $('#paymentModal2').modal('hide');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message,
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        if (xhr.status === 422 && response.errors) {
                            const errors = response.errors;
                            for (const key in errors) {
                                const inputId = `#${key}2`;
                                $(`${inputId}`).addClass('is-invalid');
                                $(`#error${key.charAt(0).toUpperCase() + key.slice(1)}2`).text(
                                    errors[key][0]);
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response?.message ||
                                    'Error occurred while updating salary.',
                            });
                        }
                    },
                    complete: function() {
                        $btn.html('Update').prop('disabled', false);
                    }
                });
            });




            $('#paymentModal').on('hidden.bs.modal', function() {
                // Clear input values
                $('#paymentForm')[0].reset();

                // Remove validation error messages
                $('#errorSalaryAmount').text('');
                $('#errorExtraAmount').text('');
                $('#errorAdvancePaid').text('');
                $('#errorGrandTotal').text('');

                // Remove 'is-invalid' class from inputs
                $('#salaryAmount').removeClass('is-invalid');
                $('#extraAmount').removeClass('is-invalid');
                $('#advancePaid').removeClass('is-invalid');
                $('#grandTotal').removeClass('is-invalid');

                // Reset read-only fields
                $('#grandTotal').val('');
            });

            // Global salary data map
            window.salaryDataMap = {};

            // Function to build expandable row content for salary
            // function buildSalaryExpandableRowContent(salary) {
            //     let actionBtns = '';

            //     // Make Payment button (for pending salaries)
            //     if (salary.status === 'Pending') {
            //         actionBtns += `<button type="button" class="btn btn-sm btn-primary make-payment me-2"
        //             data-staff-id="${salary.staff_id}"
        //             data-staff-name="${salary.staff_name}"
        //             data-present="${salary.present}"
        //             data-absent="${salary.absent}"
        //             data-extra-present="${salary.extra_present}"
        //             data-pending-advance="${salary.pending_advance}"
        //             style="background-color: #ff9f43; border-color: #ff9f43; color: white;">
        //             Make Payment
        //         </button>`;
            //     } else {
            //         // Edit Salary button (for paid salaries)
            //         actionBtns += `<button type="button" class="btn btn-sm btn-success make-bonus-payment me-2"
        //             data-staff-id="${salary.staff_id}"
        //             data-salary-id="${salary.salary_id}"
        //             data-staff-name="${salary.staff_name}"
        //             data-present="${salary.present}"
        //             data-absent="${salary.absent}"
        //             data-extra-present="${salary.extra_present}"
        //             data-salary="${salary.monthly_salary}"
        //             data-advance-paid="${salary.paid_advance}"
        //             data-extra-amount="${salary.extra_amount}"
        //             data-pending-advance="${salary.old_advance_pay}"
        //             data-bs-toggle="modal"
        //             data-bs-target="#paymentModal2">
        //             Edit Salary
        //         </button>`;

            //         // Download Salary Slip button
            //         actionBtns += `<button class="btn-icon-mobile btn-download download-slip me-2"
        //             data-staff-id="${salary.staff_id}"
        //             data-month="${$('#filterMonth').val()}"
        //             data-year="${$('#filterYear').val()}"
        //             title="Download Salary Slip">
        //             <i class="fas fa-download"></i>
        //         </button>`;
            //     }

            //     const statusBadge = salary.status === 'Pending' ?
            //         '<span class="mobile-badge bg-lightyellow">Pending</span>' :
            //         '<span class="mobile-badge bg-lightgreen">Paid</span>';

            //     return `
        //         <td colspan="9" class="order-details-content">
        //             <div class="order-details-list">
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Present Days:</span>
        //                     <span class="order-detail-value-simple">${salary.present} Day(s)</span>
        //                 </div>
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Absent Days:</span>
        //                     <span class="order-detail-value-simple">${salary.absent} Day(s)</span>
        //                 </div>
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Extra Present Days:</span>
        //                     <span class="order-detail-value-simple">${salary.extra_present} Day(s)</span>
        //                 </div>
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Pending Advance Pay:</span>
        //                <span class="order-detail-value-simple">₹${parseFloat(salary.pending_advance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
        //                     </div>
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Paid Advance Pay:</span>
        //                     <span class="order-detail-value-simple">₹${parseFloat(salary.paid_advance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
        //                   </div>
        //                 ${salary.status === 'Paid' ? `
            //                                     <div class="order-detail-row-simple">
            //                                         <span class="order-detail-label-simple">Monthly Salary:</span>
            //                                        <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">₹${parseFloat(salary.monthly_salary || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
            //                                     </div>
            //                                     <div class="order-detail-row-simple">
            //                                         <span class="order-detail-label-simple">Extra Amount:</span>
            //                                         <span class="order-detail-value-simple">₹${parseFloat(salary.extra_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
            //                                     </div>
            //                                     ` : ''}
        //                 <div class="order-detail-row-simple">
        //                     <span class="order-detail-label-simple">Status:</span>
        //                     <span class="order-detail-value-simple">${statusBadge}</span>
        //                 </div>
        //             </div>
        //             <div class="mobile-action-buttons-simple">
        //                 ${actionBtns}
        //             </div>
        //         </td>
        //     `;
            // }
            // Function to build expandable row content for salary
            function buildSalaryExpandableRowContent(salary) {
                let actionBtns = '';

                if (salary.status === 'Pending') {
                    actionBtns += `<button type="button" class="btn btn-sm btn-primary make-payment me-2"
                data-staff-id="${salary.staff_id}"
                data-staff-name="${salary.staff_name}"
                data-present="${salary.present}"
                data-absent="${salary.absent}"
                data-extra-present="${salary.extra_present}"
                data-pending-advance="${salary.pending_advance}"
                style="background-color: #ff9f43; border-color: #ff9f43; color: white;">
                Make Payment
            </button>`;
                } else {
                    actionBtns += `<button type="button" class="btn btn-sm btn-success make-bonus-payment me-2"
                data-staff-id="${salary.staff_id}"
                data-salary-id="${salary.salary_id}"
                data-staff-name="${salary.staff_name}"
                data-present="${salary.present}"
                data-absent="${salary.absent}"
                data-extra-present="${salary.extra_present}"
                data-salary="${salary.monthly_salary}"
                data-advance-paid="${salary.paid_advance}"
                data-extra-amount="${salary.extra_amount}"
                data-pending-advance="${salary.old_advance_pay}"
                data-bs-toggle="modal"
                data-bs-target="#paymentModal2">
                Edit Salary
            </button>`;

                    actionBtns += `<button class="btn-icon-mobile btn-download download-slip me-2"
                data-staff-id="${salary.staff_id}"
                data-month="${selectedMonth}"
                data-year="${selectedYear}"
                title="Download Salary Slip">
                <i class="fas fa-download"></i>
            </button>`;
                }

                const statusBadge = salary.status === 'Pending' ?
                    '<span class="mobile-badge bg-lightyellow">Pending</span>' :
                    '<span class="mobile-badge bg-lightgreen">Paid</span>';

                return `
            <td colspan="9" class="order-details-content">
                <div class="order-details-list">
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Present Days:</span>
                        <span class="order-detail-value-simple">${salary.present} Day(s)</span>
                    </div>
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Absent Days:</span>
                        <span class="order-detail-value-simple">${salary.absent} Day(s)</span>
                    </div>
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Extra Present Days:</span>
                        <span class="order-detail-value-simple">${salary.extra_present} Day(s)</span>
                    </div>
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Pending Advance Pay:</span>
                        <span class="order-detail-value-simple">₹${parseFloat(salary.pending_advance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                    </div>
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Paid Advance Pay:</span>
                        <span class="order-detail-value-simple">₹${parseFloat(salary.paid_advance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                    </div>
                    ${salary.status === 'Paid' ? `
                                <div class="order-detail-row-simple">
                                    <span class="order-detail-label-simple">Monthly Salary:</span>
                                    <span class="order-detail-value-simple" style="font-weight: bold; color: #ff9f43;">₹${parseFloat(salary.monthly_salary || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                                <div class="order-detail-row-simple">
                                    <span class="order-detail-label-simple">Extra Amount:</span>
                                    <span class="order-detail-value-simple">₹${parseFloat(salary.extra_amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span>
                                </div>
                                ` : ''}
                    <div class="order-detail-row-simple">
                        <span class="order-detail-label-simple">Status:</span>
                        <span class="order-detail-value-simple">${statusBadge}</span>
                    </div>
                </div>
                <div class="mobile-action-buttons-simple">
                    ${actionBtns}
                </div>
            </td>
        `;
            }

            // Toggle function for salary rows - must be global
            window.toggleSalaryRowDetails = function(salaryId) {
                const btn = $(`.mobile-toggle-btn-table[data-salary-id="${salaryId}"]`);
                if (btn.length === 0) {
                    // console.error('Toggle button not found for salary:', salaryId);
                    return;
                }

                const row = btn.closest('tr');
                let detailsRow = row.next(`tr.order-details-row[data-salary-id="${salaryId}"]`);
                const icon = btn.find('.toggle-icon');

                // If expandable row doesn't exist, create it
                if (detailsRow.length === 0) {
                    const salaryData = window.salaryDataMap && window.salaryDataMap[salaryId];
                    if (salaryData) {
                        detailsRow = $('<tr>')
                            .addClass('order-details-row')
                            .attr('data-salary-id', salaryId)
                            .html(buildSalaryExpandableRowContent(salaryData));
                        row.after(detailsRow);
                    } else {
                        // console.error('Salary data not found for salary:', salaryId);
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
            window.addSalaryExpandableRows = function(dt) {
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
                        const salaryId = toggleBtn.data('salary-id');
                        const salaryData = window.salaryDataMap && window.salaryDataMap[salaryId];
                        if (salaryData && !$(row).next('tr.order-details-row[data-salary-id="' +
                                salaryId + '"]').length) {
                            const expandableRow = $('<tr>')
                                .addClass('order-details-row')
                                .attr('data-salary-id', salaryId)
                                .html(buildSalaryExpandableRowContent(salaryData));
                            $(row).after(expandableRow);
                        }
                    }
                });
            };

            $(document).ready(function() {
                const authToken = localStorage.getItem("authToken");
                const pendingTable = $('#pendingTable').DataTable();
                const paidTable = $('#paidTable').DataTable();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                // console.log(selectedSubAdminId);

                // Default to current month/year
                const currentDate = new Date();
                const currentMonth = currentDate.getMonth();
                const currentYear = currentDate.getFullYear();

                $('#filterMonth').val(currentMonth);
                loadSalaryYears();
                populateStaffDropdown();

                // function fetchSalaries() {
                //     let selectedMonth = $('#filterMonth').val() || (new Date().getMonth());



                //     let selectedYear = $('#filterYear').val() || new Date().getFullYear(); // current year
                //     const selectedStaffId = $('#filterStaff').val();

                //     let url = "/api/salaries";
                //     if (selectedSubAdminId) {
                //         url += `?selectedSubAdminId=${selectedSubAdminId}`;
                //     }

                //     const monthName = new Date(selectedYear, selectedMonth - 1).toLocaleString('default', {
                //         month: 'long'
                //     });
                //     $('#selectedMonthYearDisplay').text(`Salaries for ${monthName} ${selectedYear}`);


                //     $.ajax({
                //         url: url,
                //         method: "GET",
                //         headers: {
                //             Authorization: "Bearer " + authToken,
                //         },
                //         data: {
                //             month: selectedMonth,
                //             year: selectedYear,
                //             staff_id: selectedStaffId,
                //         },
                //         success: function(response) {
                //             if (response.status) {
                //                 const salaries = response.data;
                //                 const pendingRows = [];
                //                 const paidRows = [];

                //                 salaries.forEach((salary) => {
                //                     const staffName = salary.staff_name ?
                //                         salary.staff_name.replace(/\b\w/g, c => c
                //                             .toUpperCase()) :
                //                         "";

                //                     // Create unique ID for salary (use salary_id if available, otherwise staff_id + month + year)
                //                     const salaryId = salary.salary_id ||
                //                         `salary_${salary.staff_id}_${$('#filterMonth').val()}_${$('#filterYear').val()}`;

                //                     // Store salary data in map for expandable rows
                //                     window.salaryDataMap[salaryId] = {
                //                         ...salary,
                //                         staff_name: staffName
                //                     };

                //                     // Build action buttons
                //                     let actionButtons = '';
                //                     if (salary.status === 'Pending') {
                //                         actionButtons = `<button class="btn btn-sm btn-primary make-payment"
            //                             data-staff-id="${salary.staff_id}"
            //                             data-staff-name="${salary.staff_name}"
            //                             data-present="${salary.present}"
            //                             data-absent="${salary.absent}"
            //                             data-extra-present="${salary.extra_present}"
            //                             data-pending-advance="${salary.pending_advance}">
            //                             Make Payment
            //                         </button>`;
                //                     } else {
                //                         actionButtons = `<button class="btn btn-sm btn-success make-bonus-payment mx-2"
            //                             data-staff-id="${salary.staff_id}"
            //                             data-salary-id="${salary.salary_id}"
            //                             data-staff-name="${salary.staff_name}"
            //                             data-present="${salary.present}"
            //                             data-absent="${salary.absent}"
            //                             data-extra-present="${salary.extra_present}"
            //                             data-salary="${salary.monthly_salary}"
            //                             data-advance-paid="${salary.paid_advance}"
            //                             data-extra-amount="${salary.extra_amount}"
            //                             data-pending-advance="${salary.old_advance_pay}"
            //                             data-bs-toggle="modal"
            //                             data-bs-target="#paymentModal2">
            //                             Edit Salary
            //                         </button><button class="btn btn-sm btn-outline-danger download-slip"
            //                             data-staff-id="${salary.staff_id}"
            //                             data-month="${$('#filterMonth').val()}"
            //                             data-year="${$('#filterYear').val()}"
            //                             title="Download Salary Slip">
            //                             <i class="fas fa-download"></i>
            //                         </button>`;
                //                     }

                //                     const row = [
                //                         staffName,
                //                         `<button class="mobile-toggle-btn-table" onclick="toggleSalaryRowDetails('${salaryId}')" data-salary-id="${salaryId}">
            //                             <span class="toggle-icon">+</span>
            //                         </button>`,
                //                         `${salary.present} Day(s) Present`,
                //                         `${salary.absent} Day(s) Absent`,
                //                         `${salary.extra_present} Extra Day(s)`,
                //                         // "₹" + parseFloat(salary.pending_advance ||
                //                         //     0).toFixed(2),
                //                         // "₹" + parseFloat(salary.paid_advance || 0)
                //                         // .toFixed(2),
                //                         "₹" + parseFloat(salary.pending_advance ||
                //                             0).toLocaleString('en-IN', {
                //                             minimumFractionDigits: 2,
                //                             maximumFractionDigits: 2
                //                         }),
                //                         "₹" + parseFloat(salary.paid_advance || 0)
                //                         .toLocaleString('en-IN', {
                //                             minimumFractionDigits: 2,
                //                             maximumFractionDigits: 2
                //                         }),
                //                         salary.status,
                //                         actionButtons,
                //                     ];

                //                     if (salary.status === 'Pending') {
                //                         pendingRows.push(row);
                //                     } else {
                //                         paidRows.push(row);
                //                     }
                //                 });


                //                 pendingTable.clear().rows.add(pendingRows).draw();
                //                 paidTable.clear().rows.add(paidRows).draw();

                //                 // Add expandable rows after drawing
                //                 pendingTable.off('draw').on('draw', function() {
                //                     if (window.addSalaryExpandableRows) {
                //                         window.addSalaryExpandableRows(pendingTable);
                //                     }
                //                 });
                //                 paidTable.off('draw').on('draw', function() {
                //                     if (window.addSalaryExpandableRows) {
                //                         window.addSalaryExpandableRows(paidTable);
                //                     }
                //                 });

                //                 // Initial call to add expandable rows
                //                 setTimeout(() => {
                //                     if (window.addSalaryExpandableRows) {
                //                         window.addSalaryExpandableRows(pendingTable);
                //                         window.addSalaryExpandableRows(paidTable);
                //                     }
                //                 }, 100);
                //             }
                //         },
                //         error: function(xhr) {
                //             // console.error("Error fetching salaries", xhr);
                //         }
                //     });
                // }

                function fetchSalaries(page = 1) {
                    let url = "/api/salaries";
                    if (selectedSubAdminId) {
                        url += `?selectedSubAdminId=${selectedSubAdminId}&`;
                    } else {
                        url += `?`;
                    }

                    // Add pagination and search parameters
                    url += `page=${page}&per_page=${perPage}`;

                    if (searchQuery) {
                        url += `&search=${encodeURIComponent(searchQuery)}`;
                    }

                    if (selectedMonth) {
                        url += `&month=${selectedMonth}`;
                    }

                    if (selectedYear) {
                        url += `&year=${selectedYear}`;
                    }

                    if (selectedStaffId) {
                        url += `&staff_id=${selectedStaffId}`;
                    }

                    const monthName = new Date(selectedYear, selectedMonth - 1).toLocaleString('default', {
                        month: 'long'
                    });
                    $('#selectedMonthYearDisplay').text(`Salaries for ${monthName} ${selectedYear}`);

                    $.ajax({
                        url: url,
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status) {
                                const salaries = response.data;
                                const pagination = response.pagination;

                                // Update pagination UI
                                if (pagination) {
                                    currentPage = pagination.current_page;
                                    lastPage = pagination.last_page;
                                    updatePaginationUI(pagination);
                                }

                                const pendingRows = [];
                                const paidRows = [];

                                salaries.forEach((salary) => {
                                    const staffName = salary.staff_name ? salary
                                        .staff_name.replace(/\b\w/g, c => c
                                            .toUpperCase()) : "";
                                    const salaryId = salary.salary_id ||
                                        `salary_${salary.staff_id}_${selectedMonth}_${selectedYear}`;

                                    window.salaryDataMap = window.salaryDataMap || {};
                                    window.salaryDataMap[salaryId] = {
                                        ...salary,
                                        staff_name: staffName
                                    };

                                    let actionButtons = '';
                                    if (salary.status === 'Pending') {
                                        actionButtons = `<div class="action-buttons-wrapper" style="display: inline-flex; gap: 5px; flex-wrap: wrap;">
        <button class="btn btn-sm make-payment"
            data-staff-id="${salary.staff_id}"
            data-staff-name="${salary.staff_name}"
            data-present="${salary.present}"
            data-absent="${salary.absent}"
            data-extra-present="${salary.extra_present}"
            data-pending-advance="${salary.pending_advance}"
            style="background-color: #ff9f43; border-color: #ff9f43; color: white; padding: 5px 12px; font-size: 12px;">
            Make Payment
        </button>
    </div>`;
                                    } else {
                                        actionButtons = `<div class="action-buttons-wrapper" style="display: inline-flex; gap: 5px; flex-wrap: wrap; align-items: center;">
        <button class="btn btn-sm make-bonus-payment"
            data-staff-id="${salary.staff_id}"
            data-salary-id="${salary.salary_id}"
            data-staff-name="${salary.staff_name}"
            data-present="${salary.present}"
            data-absent="${salary.absent}"
            data-extra-present="${salary.extra_present}"
            data-salary="${salary.monthly_salary}"
            data-advance-paid="${salary.paid_advance}"
            data-extra-amount="${salary.extra_amount}"
            data-pending-advance="${salary.old_advance_pay}"
            data-bs-toggle="modal"
            data-bs-target="#paymentModal2"
            style="background-color: #28a745; border-color: #28a745; color: white; padding: 5px 12px; font-size: 12px;">
            Edit Salary
        </button>
        <button class="btn btn-sm download-slip"
            data-staff-id="${salary.staff_id}"
            data-month="${selectedMonth}"
            data-year="${selectedYear}"
            title="Download Salary Slip"
            style="background-color: #dc3545; border-color: #dc3545; color: white; padding: 5px 12px; font-size: 12px;">
            <i class="fas fa-download"></i> Download
        </button>
    </div>`;
                                    }

                                    const row = [
                                        staffName,
                                        `<button class="mobile-toggle-btn-table" onclick="toggleSalaryRowDetails('${salaryId}')" data-salary-id="${salaryId}">
                                <span class="toggle-icon">+</span>
                            </button>`,
                                        `${salary.present} Day(s)`,
                                        `${salary.absent} Day(s)`,
                                        `${salary.extra_present} Extra Day(s)`,
                                        "₹" + parseFloat(salary.pending_advance ||
                                            0).toLocaleString('en-IN', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }),
                                        "₹" + parseFloat(salary.paid_advance || 0)
                                        .toLocaleString('en-IN', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }),
                                        salary.status,
                                        actionButtons,
                                    ];

                                    if (salary.status === 'Pending') {
                                        pendingRows.push(row);
                                    } else {
                                        paidRows.push(row);
                                    }
                                });

                                pendingTable.clear().rows.add(pendingRows).draw();
                                paidTable.clear().rows.add(paidRows).draw();

                                // Add expandable rows after drawing
                                setTimeout(() => {
                                    if (window.addSalaryExpandableRows) {
                                        window.addSalaryExpandableRows(pendingTable);
                                        window.addSalaryExpandableRows(paidTable);
                                    }
                                }, 100);
                            }
                        },
                        error: function(xhr) {
                            console.error("Error fetching salaries", xhr);
                        }
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

                // Handle page number clicks
                $(document).on('click', '#pagination-numbers .page-link', function(e) {
                    e.preventDefault();
                    let page = $(this).data('page');
                    if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                        fetchSalaries(page);
                    }
                });

                // Handle per-page change
                $('#per-page-select').on('change', function() {
                    perPage = $(this).val();
                    fetchSalaries(1);
                });

                // Handle search input
                $('#search-input').on('keyup', function() {
                    searchQuery = $(this).val();
                    fetchSalaries(1);
                });

                $('#filterMonth, #filterYear, #filterStaff').on('change', function() {
                    selectedMonth = $('#filterMonth').val();
                    selectedYear = $('#filterYear').val();
                    selectedStaffId = $('#filterStaff').val();
                    fetchSalaries(1);
                });


                // Load all staff
                // function populateStaffDropdown() {
                //     $.ajax({
                //         url: "/api/staff",
                //         method: "GET",
                //         headers: {
                //             Authorization: "Bearer " + authToken,
                //         },
                //         data: {
                //             selectedSubAdminId: selectedSubAdminId || '', // Pass if available
                //         },
                //         success: function(response) {
                //             if (response.status) {
                //                 const staffSelect = $('#filterStaff');
                //                 staffSelect.empty().append(
                //                     '<option value="">All Staff</option>');
                //                 response.staff.forEach(function(staff) {
                //                     // Capitalize each word in staff name
                //                     const staffName = staff.name ?
                //                         staff.name.replace(/\b\w/g, c => c
                //                             .toUpperCase()) :
                //                         "";

                //                     staffSelect.append(
                //                         `<option value="${staff.id}">${staffName}</option>`
                //                     );
                //                 });
                //                 // console.log('asd');

                //                 // Default fetch after populating
                //                 fetchSalaries();
                //             }
                //         },
                //         error: function(xhr) {
                //             // console.error("Error loading staff list", xhr);
                //         }
                //     });
                // }
                function populateStaffDropdown() {
                    let url = "/api/staff";
                    if (selectedSubAdminId) {
                        url += `?selectedSubAdminId=${selectedSubAdminId}`;
                    }

                    $.ajax({
                        url: url,
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status) {
                                const staffSelect = $('#filterStaff');
                                staffSelect.empty().append(
                                    '<option value="">All Staff</option>');
                                response.staff.forEach(function(staff) {
                                    const staffName = staff.name ? staff.name.replace(
                                        /\b\w/g, c => c.toUpperCase()) : "";
                                    staffSelect.append(
                                        `<option value="${staff.id}">${staffName}</option>`
                                    );
                                });
                                fetchSalaries(1);
                            }
                        },
                        error: function(xhr) {
                            console.error("Error loading staff list", xhr);
                        }
                    });
                }


                // Load salary years
                function loadSalaryYears() {
                    $.ajax({
                        url: "/api/salaries/years",
                        method: "GET",
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                        success: function(response) {
                            if (response.status) {
                                const yearSelect = $('#filterYear');
                                yearSelect.empty();
                                response.years.forEach(function(year) {
                                    const selected = year == currentYear ? 'selected' :
                                        '';
                                    yearSelect.append(
                                        `<option value="${year}" ${selected}>${year}</option>`
                                    );
                                });
                            }
                        },
                        error: function() {
                            // console.error("Error loading years");
                        }
                    });
                }

                // On filter change
                $('#filterMonth, #filterYear, #filterStaff').on('change', fetchSalaries);

                // Handle window resize for expandable rows
                $(window).on('resize', function() {
                    if (window.addSalaryExpandableRows) {
                        window.addSalaryExpandableRows(pendingTable);
                        window.addSalaryExpandableRows(paidTable);
                    }
                });
            });
            $('#exportCSV').on('click', function() {
                const selectedMonth = $('#filterMonth').val();
                const selectedYear = $('#filterYear').val();
                const selectedStaffId = $('#filterStaff').val();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");

                let url = `/api/salaries/export`;
                const params = new URLSearchParams();
                if (selectedMonth) params.append('month', selectedMonth);
                if (selectedYear) params.append('year', selectedYear);
                if (selectedStaffId) params.append('staff_id', selectedStaffId);
                if (selectedSubAdminId) params.append('selectedSubAdminId', selectedSubAdminId);

                if (params.toString()) url += `?${params.toString()}`;

                $('#exportCSV').attr('disabled', true).html(
                    '<i class="spinner-border spinner-border-sm"></i> Exporting...');

                fetch(url, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken
                        }
                    })
                    .then(async (response) => {
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            const res = await response.json();
                            if (!res.status) throw new Error(res.message);

                            // ✅ Trigger file download
                            const a = document.createElement('a');
                            a.href = res.file_url;
                            a.download = res.file_name || 'salary_export.xlsx';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message
                            });
                        } else {
                            // fallback if API returns blob
                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `salary_export.xlsx`;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(blobUrl);
                        }
                    })
                    .catch(error => Swal.fire('Error', error.message, 'error'))
                    .finally(() => {
                        $('#exportCSV').attr('disabled', false).html(
                            '<i class="fas fa-file-excel"></i> Export Excel');
                    });
            });


            $('#exportPDF').on('click', function() {
                const selectedMonth = $('#filterMonth').val();
                const selectedYear = $('#filterYear').val();
                const selectedStaffId = $('#filterStaff').val();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");

                if (!selectedMonth || !selectedYear) {
                    Swal.fire('Error', 'Please select both month and year to export data.', 'error');
                    return;
                }

                $('#exportPDF').attr('disabled', true).html(
                    '<i class="spinner-border spinner-border-sm"></i> Exporting...');

                // ✅ Step 1: Check salaries exist
                let checkUrl = `/api/salaries?month=${selectedMonth}&year=${selectedYear}`;
                if (selectedStaffId) checkUrl += `&staff_id=${selectedStaffId}`;
                if (selectedSubAdminId) checkUrl += `&selectedSubAdminId=${selectedSubAdminId}`;

                fetch(checkUrl, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken
                        },
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (!response.status || !response.data || response.data.length === 0) {
                            Swal.fire('Error', 'Salary not generated yet.', 'error');
                            $('#exportPDF').attr('disabled', false).text('Export PDF');
                            throw new Error('No salary data');
                        }

                        // ✅ Step 2: Request PDF
                        let pdfUrl = `/api/salaries/pdf?month=${selectedMonth}&year=${selectedYear}`;
                        if (selectedStaffId) pdfUrl += `&staff_id=${selectedStaffId}`;
                        if (selectedSubAdminId) pdfUrl += `&selectedSubAdminId=${selectedSubAdminId}`;

                        return fetch(pdfUrl, {
                            method: 'GET',
                            headers: {
                                Authorization: "Bearer " + authToken
                            },
                        });
                    })
                    .then(async (response) => {
                        // ✅ Handle both JSON or Blob response
                        const contentType = response.headers.get('content-type');

                        if (contentType && contentType.includes('application/json')) {
                            const json = await response.json();
                            if (json.status && json.file_url) {
                                // 🔽 Download PDF from file_url
                                const a = document.createElement('a');
                                a.href = json.file_url;
                                a.download = json.file_name ||
                                    `salary_export_${selectedMonth}_${selectedYear}.pdf`;
                                document.body.appendChild(a);
                                a.click();
                                a.remove();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: json.message,
                                });
                            } else {
                                throw new Error(json.message || 'Failed to generate PDF');
                            }
                        } else {
                            // 🔽 Binary response (PDF blob)
                            const blob = await response.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `salary_export_${selectedMonth}_${selectedYear}.pdf`;
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(blobUrl);
                        }
                    })
                    .catch(err => Swal.fire('Error', err.message, 'error'))
                    .finally(() => {
                        $('#exportPDF').attr('disabled', false).html(
                            '<i class="fas fa-file-pdf"></i> Export PDF');
                    });
            });
            $('#printPayment').on('click', function() {
                const staffId = $('#staffId').val();
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();

                if (!staffId || !month || !year) {
                    Swal.fire('Error', 'Please ensure staff, month, and year are selected.', 'error');
                    return;
                }

                const authToken = localStorage.getItem("authToken");
                let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;

                // Disable the button and show loading
                $('#printPayment').attr('disabled', true).text('Printing...');

                // Make fetch request with Authorization header
                fetch(url, {
                        method: 'GET',
                        headers: {
                            Authorization: "Bearer " + authToken,
                        },
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to download file.');
                        return response.blob();
                    })
                    .then(blob => {
                        const blobUrl = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = `salary_slip_${month}_${year}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(blobUrl);
                    })
                    .catch(error => {
                        Swal.fire('Error', error.message, 'error');
                    })
                    .finally(() => {
                        $('#printPayment').attr('disabled', false).text('Print');
                    });
            });

            // $(document).on('click', '.download-slip', function() {
            //     const staffId = $(this).data('staff-id');
            //     const month = $(this).data('month') || $('#filterMonth').val();
            //     const year = $(this).data('year') || $('#filterYear').val();
            //     const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            //     const authToken = localStorage.getItem("authToken");

            //     if (!staffId || !month || !year) {
            //         Swal.fire('Error', 'Invalid staff, month, or year.', 'error');
            //         return;
            //     }

            //     let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;
            //     if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;

            //     $(this).prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

            //     fetch(url, {
            //             method: 'GET',
            //             headers: {
            //                 Authorization: "Bearer " + authToken
            //             }
            //         })
            //         .then(res => res.json())
            //         .then(res => {
            //             if (!res.status) throw new Error(res.message);

            //             // ✅ Create temporary link to download PDF
            //             const a = document.createElement('a');
            //             a.href = res.file_url;
            //             a.download = res.file_name; // Use the filename from API
            //             document.body.appendChild(a);
            //             a.click();
            //             a.remove();

            //             Swal.fire({
            //                 icon: 'success',
            //                 title: 'Success',
            //                 text: 'Salary slip downloaded successfully.',
            //                 showConfirmButton: true
            //             });
            //         })
            //         .catch(err => Swal.fire('Error', err.message, 'error'))
            //         .finally(() => {
            //             $(this).prop('disabled', false).html('<i class="fas fa-download"></i>');
            //         });
            // });
            $(document).on('click', '.download-slip', function() {
                const staffId = $(this).data('staff-id');
                const month = $(this).data('month') || $('#filterMonth').val();
                const year = $(this).data('year') || $('#filterYear').val();
                const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");

                if (!staffId || !month || !year) {
                    Swal.fire('Error', 'Invalid staff, month, or year.', 'error');
                    return;
                }

                let url = `/api/salaries/staff/pdf?staff_id=${staffId}&month=${month}&year=${year}`;
                if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;

                // Show loading state on the button
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

                // Fetch PDF directly
                fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': "Bearer " + authToken
                        }
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');

                        if (!response.ok) {
                            if (contentType && contentType.includes('application/json')) {
                                const error = await response.json();
                                throw new Error(error.message || 'Failed to download PDF');
                            } else {
                                throw new Error('Failed to download PDF');
                            }
                        }

                        // Check if response is PDF
                        if (contentType && contentType.includes('application/pdf')) {
                            // Get the PDF blob
                            const blob = await response.blob();

                            // Create download link
                            const blobUrl = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = blobUrl;
                            a.download = `salary_slip_${staffId}_${month}_${year}.pdf`;
                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                            window.URL.revokeObjectURL(blobUrl);

                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Salary slip downloaded successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        } else {
                            // Try to parse as JSON for error message
                            const data = await response.json();
                            throw new Error(data.message || 'Failed to generate PDF');
                        }
                    })
                    .catch(err => {
                        console.error('Download error:', err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: err.message || 'Failed to download salary slip.',
                        });
                    })
                    .finally(() => {
                        $btn.prop('disabled', false).html(originalHtml);
                    });
            });


        });
        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Open Modal
            $("#advanceHistory").on("click", function() {
                $("#advanceHistoryModal").modal("show");

                // Load staff in dropdown
                $.ajax({
                    url: "/api/staff",
                    method: "GET",
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    data: {
                        selectedSubAdminId: selectedSubAdminId || ''
                    },
                    success: function(response) {
                        if (response.status) {
                            const staffSelect = $("#historyStaff");
                            staffSelect.empty().append(
                                '<option value="">-- Select Staff --</option>');
                            response.staff.forEach(function(staff) {
                                const staffName = staff.name ? staff.name.replace(
                                    /\b\w/g, c => c.toUpperCase()) : "";
                                staffSelect.append(
                                    `<option value="${staff.id}">${staffName}</option>`
                                );
                            });
                        }
                    }
                });
            });

            // Fetch History when staff selected
            $("#historyStaff").on("change", function() {
                const staffId = $(this).val();
                if (!staffId) return;

                $.ajax({
                    url: "/api/advance-history",
                    method: "GET",
                    headers: {
                        Authorization: "Bearer " + authToken
                    },
                    data: {
                        staff_id: staffId
                    },
                    success: function(response) {
                        const tbody = $("#historyTable tbody");
                        tbody.empty();

                        if (response.status && response.history.length > 0) {
                            response.history.forEach(function(row) {
                                // const amount = parseFloat(row.amount) || 0;
                                // const paid = parseFloat(row.paid_amount) || 0;
                                // const pending = amount - paid;
                                const amount = parseFloat(row.amount || 0)
                                    .toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    });
                                const paid = parseFloat(row.paid_amount || 0)
                                    .toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    });
                                const pending = (parseFloat(row.amount || 0) -
                                        parseFloat(row.paid_amount || 0))
                                    .toLocaleString('en-IN', {
                                        minimumFractionDigits: 2
                                    });

                                tbody.append(`
                                    <tr>
                                        <td>${row.date ?? '-'}</td>
                                        <td>${amount}</td>
                                        <td>${paid}</td>
                                        <td>${pending}</td>
                                        <td>${row.method ?? '-'}</td>
                                        <td>${row.reason ?? '-'}</td>
                                    </tr>
                                `);
                            });
                        } else {
                            tbody.append(
                                `<tr><td colspan="6" class="text-center text-muted">No history found</td></tr>`
                            );
                        }
                    },
                    error: function() {
                        alert("Error loading advance history");
                    }
                });
            });
        });
    </script>
@endpush
