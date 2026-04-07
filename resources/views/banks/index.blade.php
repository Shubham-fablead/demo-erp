@extends('layout.app')

@section('title', 'Banks List')

@section('content')
    <style>
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
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

        .table-scroll-top {
            display: none;
        }

        .toggle-details {
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .toggle-details i {
            transition: transform 0.2s ease;
        }

        .toggle-details:hover i {
            transform: scale(1.15);
        }

        /* Prevent unnecessary word breaking */
        /* table.datanew1 td,
            table.datanew1 th {
                white-space: nowrap;
                word-break: keep-all;
            } */

        /* Allow wrapping only when really needed */
        table.datanew1 td:nth-child(1),
        /* Bank Name */
        table.datanew1 td:nth-child(5) {
            /* Branch */
            white-space: normal;
            word-break: normal;
            overflow-wrap: break-word;
        }

        .bank-text {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
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
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {

            table.datanew1 thead th,
            table.datanew1 tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew1 thead th.details-column,
            table.datanew1 tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {

            table.datanew1 thead th:nth-child(n+3),
            table.datanew1 tbody td:nth-child(n+3) {
                display: none !important;
            }

            /* Show only Staff Name and Details columns on mobile */
            table.datanew1 thead th:first-child,
            table.datanew1 tbody td:first-child {
                display: table-cell !important;
                max-width: calc(100vw - 110px);
            }

            table.datanew1 thead th.details-column,
            table.datanew1 tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* Style for staff name wrapping */
            table.datanew1 tbody td:first-child {
                /* display: flex !important; */
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
            }

            table.datanew1 tbody td:first-child a {
                align-items: center !important;
                text-align: left !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                line-height: 1.3 !important;
            }

            .staff-name {
                display: inline-block !important;
                /* max-width: calc(100% - 60px) !important; */
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
                -webkit-hyphens: auto !important;
                -ms-hyphens: auto !important;
            }

            /* Limit to 2 lines with ellipsis */
            .staff-name.truncated {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
        }

        /* Tablet specific fixes */
        @media screen and (width: 768px) {
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            table.datanew1 thead th.details-column,
            table.datanew1 tbody td:nth-child(2) {
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

        @media (max-width: 767.98px) {
            .table-top .search-set {
                display: flex;
                justify-content: flex-start !important;
                width: 100%;
            }

            .table-top .search-input {
                margin-left: 0 !important;
            }
        }

        /* Staff specific styling */
        .staff-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }

        /* Collapsible details styling */
        .collapse-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #ff9f43;
        }

        .detail-item {
            display: flex;
            margin-bottom: 8px;
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

        .mobile-actions {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
        }

        .mobile-actions a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }

        .mobile-actions a:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }

        .mobile-actions svg {
            width: 18px;
            height: 18px;
        }

        /* ✅ Hide default DataTables search box completely */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        /* Remove extra top spacing created by DataTables */
        .dataTables_wrapper .row:first-child {
            display: none !important;
        }

        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* Search input styling */
        .search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            top: 7px !important;
            padding: 0;
        }

        /* Custom Pagination Styling (same as category) */
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

        /* Default table behaviour */
        table.datanew1 td,
        table.datanew1 th {
            white-space: normal;
            vertical-align: middle;
        }

        /* Keep numeric/action columns single line */
        table.datanew1 td:nth-child(3),
        table.datanew1 td:nth-child(4),
        table.datanew1 td:nth-child(6),
        table.datanew1 td:nth-child(7),
        table.datanew1 td:nth-child(8) {
            white-space: nowrap;
        }

        /* ✅ Bank Name + Branch wrap properly */
        table.datanew1 td:nth-child(1),
        table.datanew1 td:nth-child(5) {
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.35;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Bank Master</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(16, 'create'))
                <a href="{{ route('banks.create') }}" class="btn btn-added btn-sm">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">New
                    Banks
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
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
                </div>


                {{-- <div class="table-scroll-top">
                    <div></div>
                </div> --}}
                {{-- <div class="table-responsive">
                    <table class="table datanew1"> --}}
                <div class="table-container">
                    <table class="table datanew1">
                        <thead>
                            <tr>
                                <th>Bank Name</th>
                                <th class="text-center details-column">Details</th>
                                <th>Account Number</th>
                                <th>IFSC Code</th>
                                <th>Branch</th>
                                <th>Opening Balance</th>

                                <th>Status</th>
                                <th>Action</th>


                            </tr>
                        </thead>
                        <tbody>


                    </table>
                </div>
                {{-- Pagination Controls --}}
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"
                    style="display: none;">
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
                            {{-- page numbers inserted here --}}
                        </ul>
                    </nav>
                </div>

            @endsection
            @push('js')
                {{-- <script>
                    $(document).ready(function() {

                        let table = $('.datanew1').DataTable({
                            order: [
                                [0, "asc"]
                            ],
                            pageLength: 10
                        });

                        fetchBanks();

                        // Detail Toggle logic
                        // $(document).on('click', '.toggle-details', function() {
                        //     let tr = $(this).closest('tr');
                        //     let bankId = $(this).data('id');
                        //     let bank = window.bankDataMap[bankId];

                        //     if (tr.next().hasClass('collapse-details-row')) {
                        //         tr.next().remove();
                        //         $(this).html('<i class="fas fa-plus-circle"></i>');
                        //     } else {
                        //         let detailsHtml = buildExpandableRowContent(bank);
                        //         tr.after(`<tr class="collapse-details-row"><td colspan="2">${detailsHtml}</td></tr>`);
                        //         $(this).html('<i class="fas fa-minus"></i>');
                        //     }
                        // });
                        $(document).on('click', '.toggle-details', function() {
                            let tr = $(this).closest('tr');
                            let bankId = $(this).data('id');
                            let bank = window.bankDataMap[bankId];
                            let icon = $(this).find('i');

                            if (tr.next().hasClass('collapse-details-row')) {
                                tr.next().remove();
                                icon.removeClass('fa-minus-circle')
                                    .addClass('fa-plus-circle')
                                    .css('color', '#ff9f43');
                            } else {
                                let detailsHtml = buildExpandableRowContent(bank);
                                tr.after(`<tr class="collapse-details-row">
                    <td colspan="2">${detailsHtml}</td>
                  </tr>`);
                                icon.removeClass('fa-plus-circle')
                                    .addClass('fa-minus-circle')
                                    .css('color', 'red');
                            }
                        });


                        function buildExpandableRowContent(bank) {
                            let statusBadge = bank.status == 1 ?
                                `<span class="badges bg-lightgreen">Active</span>` :
                                `<span class="badges bg-lightred">Inactive</span>`;

                            let actions = `
                                <div class="mobile-actions">
                                    <a href="/edit-bank/${bank.id}" class="me-2">
                                        <svg width="18" height="18" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                                        </svg>
                                    </a>
                                    <a href="javascript:void(0);" class="delete-bank" data-id="${bank.id}">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                        </svg>
                                    </a>
                                </div>`;

                            return `
                                <div class="collapse-details">
                                    <div class="detail-item">
                                        <div class="detail-label">A/C Number:</div>
                                        <div class="detail-value">${bank.account_number ?? 'N/A'}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">IFSC Code:</div>
                                        <div class="detail-value">${bank.ifsc_code ?? 'N/A'}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Branch:</div>
                                        <div class="detail-value">${bank.branch_name ?? 'N/A'}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Opening Bal:</div>
                                        <div class="detail-value">${formatCurrencyIN(bank.opening_balance)}</div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Status:</div>
                                        <div class="detail-value">${statusBadge}</div>
                                    </div>
                                    ${actions}
                                </div>
                            `;
                        }

                        function formatCurrencyIN(amount) {
                            let num = parseFloat(amount || 0);

                            return '₹' + num.toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }

                        function fetchBanks() {
                            let authToken = localStorage.getItem("authToken");
                            let selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                            let data = {};

                            if (selectedSubAdminId) {
                                data.selectedSubAdminId = selectedSubAdminId;
                            }

                            $.ajax({
                                url: "{{ route('banks.data') }}", // existing API route
                                type: "GET",
                                data: data,
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                },
                                dataType: "json",

                                success: function(response) {

                                    if (response.status) {
                                        let banks = response.data;
                                        let rows = [];
                                        window.bankDataMap = {}; // Global map to store bank data

                                        banks.forEach(bank => {
                                            window.bankDataMap[bank.id] = bank;
                                            let statusBadge = bank.status == 1 ?
                                                `<span class="badges bg-lightgreen">Active</span>` :
                                                `<span class="badges bg-lightred">Inactive</span>`;

                                            let toggleBtn = `
                                            <a href="javascript:void(0);"
                                            class="toggle-details"
                                            data-id="${bank.id}">
                                                <i class="fas fa-plus-circle" style="color:#ff9f43;"></i>
                                            </a>`;

                                            let actions = `
                                    <a class="me-2" href="/edit-bank/${bank.id}">
                                        <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z"
                                                fill="#092C4C"/>
                                        </svg>
                                    </a>

                                    <a class="me-2 confirm-text delete-bank" data-id="${bank.id}" href="javascript:void(0);">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z"
                                                fill="#092C4C"/>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z"
                                                fill="#092C4C"/>
                                        </svg>
                                    </a>
                                `;



                                            rows.push([
                                                bank.bank_name ?? 'N/A',
                                                toggleBtn,
                                                bank.account_number ?? 'N/A',
                                                bank.ifsc_code ?? 'N/A',
                                                bank.branch_name ?? 'N/A',
                                                // bank.opening_balance ?? '0',
                                                formatCurrencyIN(bank.opening_balance),
                                                statusBadge,
                                                actions
                                            ]);
                                        });
                                        // console.log("Rows to display:", rows);

                                        table.clear().rows.add(rows).draw();
                                    } else {
                                        table.clear().draw();
                                    }
                                },
                                error: function(xhr) {
                                    // console.log(xhr.responseText);
                                    table.clear().draw();
                                }
                            });
                        }

                        // DELETE BANK
                        $(document).on('click', '.delete-bank', function() {
                            var bankId = $(this).data('id');
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
                                        url: `/api/delete/banks/${bankId}`, // Make sure route matches your destroy route
                                        type: 'DELETE', // Use DELETE method
                                        headers: {
                                            "Authorization": "Bearer " + authToken,
                                        },
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr(
                                                'content') // CSRF token
                                        },
                                        success: function(response) {
                                            if (response.status) {
                                                Swal.fire({
                                                    title: "Deleted!",
                                                    text: response.message,
                                                    icon: "success",
                                                    confirmButtonColor: "#ff9f43",
                                                    confirmButtonText: "OK"
                                                }).then(() => {
                                                    fetchBanks(); // refresh table
                                                });
                                            } else {
                                                Swal.fire({
                                                    title: "Error!",
                                                    text: response.message,
                                                    icon: "error",
                                                    confirmButtonColor: "#ff9f43",
                                                    confirmButtonText: "OK"
                                                });
                                            }
                                        },
                                        error: function(xhr) {
                                            let message = "Something went wrong!";
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                message = xhr.responseJSON.message;
                                            }
                                            Swal.fire({
                                                title: "Error!",
                                                text: message,
                                                icon: "error",
                                                confirmButtonColor: "#ff9f43",
                                                confirmButtonText: "OK"
                                            });
                                        }
                                    });
                                }
                            });
                        });

                    });
                </script> --}}

                <script>
                    $(document).ready(function() {
                        var authToken = localStorage.getItem("authToken");
                        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                        // Initialize DataTable WITHOUT built-in paging/search
                        var table = $('.datanew1').DataTable({
                            destroy: true,
                            paging: false,
                            info: false,
                            searching: false,
                            dom: 't',
                            ordering: false
                        });

                        // Pagination state
                        let currentPage = 1;
                        let lastPage = 1;
                        let perPage = 10;
                        let searchQuery = '';

                        // Global map for bank data (used by toggle)
                        window.bankDataMap = {};

                        // Initial fetch
                        fetchBanks(currentPage);

                        // Search input handler
                        $('#search-input').on('keyup', function() {
                            searchQuery = $(this).val();
                            fetchBanks(1);
                        });

                        // Per‑page change handler
                        $('#per-page-select').on('change', function() {
                            perPage = $(this).val();
                            fetchBanks(1);
                        });

                        // Page number click handler
                        $(document).on('click', '#pagination-numbers .page-link', function(e) {
                            e.preventDefault();
                            let page = $(this).data('page');
                            if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                                fetchBanks(page);
                            }
                        });

                        // Helper: format currency in Indian format
                        function formatCurrencyIN(amount) {
                            let num = parseFloat(amount || 0);
                            return '₹' + num.toLocaleString('en-IN', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }

                        // Build expandable row content (same as before)
                        function buildExpandableRowContent(bank) {
                            let statusBadge = bank.status == 1 ?
                                `<span class="badges bg-lightgreen">Active</span>` :
                                `<span class="badges bg-lightred">Inactive</span>`;

                            let actions = `
            <div class="mobile-actions">
                @if (app('hasPermission')(16, 'edit'))
                <a href="/edit-bank/${bank.id}" class="me-2">
                    <svg width="18" height="18" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                    </svg>
                </a>
                @endif
                @if (app('hasPermission')(16, 'delete'))
                <a href="javascript:void(0);" class="delete-bank" data-id="${bank.id}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                        <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                    </svg>
                </a>
                @endif
            </div>`;

                            return `
            <div class="collapse-details">
                <div class="detail-item">
                    <div class="detail-label">A/C Number:</div>
                    <div class="detail-value">${bank.account_number ?? 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">IFSC Code:</div>
                    <div class="detail-value">${bank.ifsc_code ?? 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Branch:</div>
                    <div class="detail-value">${bank.branch_name ?? 'N/A'}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Opening Bal:</div>
                    <div class="detail-value">${formatCurrencyIN(bank.opening_balance)}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">${statusBadge}</div>
                </div>
                ${actions}
            </div>
        `;
                        }

                        // Main fetch function
                        function fetchBanks(page = 1) {
                            let url = `{{ route('banks.data') }}?page=${page}&per_page=${perPage}`;
                            if (selectedSubAdminId) {
                                url += `&selectedSubAdminId=${selectedSubAdminId}`;
                            }
                            if (searchQuery) {
                                url += `&search=${encodeURIComponent(searchQuery)}`;
                            }

                            $.ajax({
                                url: url,
                                type: 'GET',
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                },
                                success: function(response) {
                                    if (response.status) {
                                        let banks = response.data;
                                        let pagination = response.pagination;

                                        // Update state
                                        currentPage = pagination.current_page;
                                        lastPage = pagination.last_page;

                                        // Update pagination UI
                                        updatePaginationUI(pagination);

                                        // Clear old map and rebuild
                                        window.bankDataMap = {};
                                        let rows = [];

                                        banks.forEach(bank => {
                                            window.bankDataMap[bank.id] = bank;

                                            let statusBadge = bank.status == 1 ?
                                                `<span class="badges bg-lightgreen">Active</span>` :
                                                `<span class="badges bg-lightred">Inactive</span>`;

                                            let toggleBtn = `
                            <a href="javascript:void(0);" class="toggle-details" data-id="${bank.id}">
                                <i class="fas fa-plus-circle" style="color:#ff9f43;"></i>
                            </a>`;

                                            let actions = `
                            @if (app('hasPermission')(16, 'edit'))                
                            <a class="me-2" href="/edit-bank/${bank.id}">
                                <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z"
                                          fill="#092C4C"/>
                                </svg>
                            </a>
                            @endif
                            @if (app('hasPermission')(16, 'delete'))
                            <a class="me-2 confirm-text delete-bank" data-id="${bank.id}" href="javascript:void(0);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z"
                                          fill="#092C4C"/>
                                    <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z"
                                          fill="#092C4C"/>
                                </svg>
                            </a>
                            @endif
                        `;

                                            rows.push([
                                                bank.bank_name ?? 'N/A',
                                                toggleBtn,
                                                bank.account_number ?? 'N/A',
                                                bank.ifsc_code ?? 'N/A',
                                                bank.branch_name ?? 'N/A',
                                                formatCurrencyIN(bank.opening_balance),
                                                statusBadge,
                                                actions
                                            ]);
                                        });

                                        table.clear().rows.add(rows).draw();
                                        $('.pagination-controls').show();
                                    } else {
                                        table.clear().draw();
                                        $('.pagination-controls').hide();
                                    }
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'Failed to fetch bank data!',
                                        confirmButtonColor: '#ff9f43'
                                    });
                                    $('.pagination-controls').hide();
                                }
                            });
                        }

                        function updatePaginationUI(pagination) {
                            let from = pagination.from || 0;
                            let to = pagination.to || 0;
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
                        }

                        // Toggle details (delegated event)
                        $(document).on('click', '.toggle-details', function() {
                            let tr = $(this).closest('tr');
                            let bankId = $(this).data('id');
                            let bank = window.bankDataMap[bankId];
                            let icon = $(this).find('i');

                            if (tr.next().hasClass('collapse-details-row')) {
                                tr.next().remove();
                                icon.removeClass('fa-minus-circle')
                                    .addClass('fa-plus-circle')
                                    .css('color', '#ff9f43');
                            } else {
                                let detailsHtml = buildExpandableRowContent(bank);
                                tr.after(`<tr class="collapse-details-row"><td colspan="2">${detailsHtml}</td></tr>`);
                                icon.removeClass('fa-plus-circle')
                                    .addClass('fa-minus-circle')
                                    .css('color', 'red');
                            }
                        });

                        // Delete bank (unchanged)
                        $(document).on('click', '.delete-bank', function() {
                            var bankId = $(this).data('id');
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
                                        url: `/api/delete/banks/${bankId}`,
                                        type: 'DELETE',
                                        headers: {
                                            "Authorization": "Bearer " + authToken,
                                        },
                                        data: {
                                            _token: $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function(response) {
                                            if (response.status) {
                                                Swal.fire({
                                                    title: "Deleted!",
                                                    text: response.message,
                                                    icon: "success",
                                                    confirmButtonColor: "#ff9f43",
                                                    confirmButtonText: "OK"
                                                }).then(() => {
                                                    fetchBanks(
                                                        currentPage
                                                        ); // refresh current page
                                                });
                                            } else {
                                                Swal.fire({
                                                    title: "Error!",
                                                    text: response.message,
                                                    icon: "error",
                                                    confirmButtonColor: "#ff9f43",
                                                    confirmButtonText: "OK"
                                                });
                                            }
                                        },
                                        error: function(xhr) {
                                            let message = "Something went wrong!";
                                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                                message = xhr.responseJSON.message;
                                            }
                                            Swal.fire({
                                                title: "Error!",
                                                text: message,
                                                icon: "error",
                                                confirmButtonColor: "#ff9f43",
                                                confirmButtonText: "OK"
                                            });
                                        }
                                    });
                                }
                            });
                        });

                        // Reset details when switching to desktop
                        $(window).on('resize', function() {
                            if ($(window).width() > 768) {
                                $('.collapse-details-row').remove();
                                $('.toggle-details i')
                                    .removeClass('fa-minus-circle')
                                    .addClass('fa-plus-circle')
                                    .css('color', '#ff9f43');
                            }
                        });
                    });
                </script>
            @endpush
