@extends('layout.app')

@section('title', 'Expense Type List')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            div#DataTables_Table_0_filter {
                margin-top: 10px !important;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .table-top .wordset {
                margin-top: 0 !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: .1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }
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


        .table.datanew tbody td:nth-child(2),
        .table.datanew thead th:nth-child(2) {
            max-width: 400px;
            /* control width */
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            text-align: left;
        }

        /* Prevent action column shrinking */
        .table.datanew tbody td:nth-child(3) {
            white-space: nowrap;
        }

        /* Mobile responsive wrap */
        @media (max-width: 768px) {
            .table.datanew tbody td:nth-child(2) {
                max-width: calc(100vw - 140px);
                font-size: 14px;
                line-height: 1.4;
            }

            .search-set {
                margin-right: 1rem !important;
            }
        }

        /* Fade out animation for error messages (optional) */
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
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

            .dataTables_info {
                float: left !important;
            }

            @media screen and (max-width: 768px) {
                input.form-control.form-control-sm {
                    margin-top: 10px;
                }
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
                <h4>All Expenses Type</h4>
                <!--<h6>Manage your purchases</h6>-->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(5, 'view'))
                    <a href="{{ route('expensetype.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-2" alt="img">New
                        Expense Type</a>
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

                <div class="table-container">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Expense Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="expense-table-body">
                            <!-- Dynamic rows here -->
                        </tbody>
                    </table>
                </div>
                {{-- Pagination Controls (same as category) --}}
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
                            {{-- Page numbers inserted here --}}
                        </ul>
                    </nav>
                </div>

            </div>
        </div>

    </div>
@endsection

@push('js')
    <script>
        const userRole = "{{ auth()->user()->role }}";
    </script>

    {{-- <script>
        $(document).ready(function () {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false
            }); // Initialize DataTable
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // Initialize DataTable with search logic enabled
            // var table = $('.datanew').DataTable({
            //     responsive: true,
            //     autoWidth: false,
            //     pageLength: 10,
            //     ordering: true,
            //     searching: true, // Enable search logic
            //     paging: true,
            //     destroy: true,
            //     language: {
            //         emptyTable: "No expenses found.",
            //         zeroRecords: "No expense record found.",
            //     },
            // });

            fetchExpenses();

            function fetchExpenses() {
                $.ajax({
                    url: '/api/expense-types', // Make sure this returns only isDeleted = 0
                    method: 'GET',
                    data: { selectedSubAdminId: selectedSubAdminId },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function (response) {
                        let tableRows = [];

                        response.data.forEach(function (item, index) {
                               let deleteBtn = '';

                                if (
                                    userRole !== 'sales-manager' &&
                                    userRole !== 'purchase-manager' &&
                                    userRole !== 'inventory-manager'
                                ) {
                                        @if (app('hasPermission')(5, 'delete'))
                                    deleteBtn = `
                                        <a class="me-3 delete-btn" href="javascript:void(0);" data-id="${item.id}">
                                            <img src="{{ env('ImagePath').'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                        </a>`;
                                        @endif
                                }
                            tableRows.push([
                                index + 1, // S.No.
                                item.type,
                                `
                            @if (app('hasPermission')(5, 'view'))
                        <a class="me-3" href="/edit-expense-type/${item.id}">
                            <img src="{{ env('ImagePath').'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                        </a>
                        @endif
                       ${deleteBtn}
                        `
                            ]);
                        });

                        table.clear().rows.add(tableRows).draw();
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch expense types!',
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
            }

            $(document).on("click", ".delete-btn", function () {
                let id = $(this).data("id");

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
                            url: `/api/expenses-type/${id}`,
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function (response) {
                                // Swal.fire("Deleted!", response.message, "success");
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Delete Record successfully",
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43", // Custom OK button color
                                    confirmButtonText: "OK"
                                });
                                fetchExpenses(); // Refresh the table after deletion
                            },
                            error: function (xhr) {

                                let message = 'Something went wrong.';

                                if (xhr.responseJSON) {
                                    if (xhr.responseJSON.message) {
                                        message = xhr.responseJSON.message;
                                    }
                                }

                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Cannot Delete',
                                    text: message,
                                    confirmButtonColor: '#ff9f43'
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

            // Initialize DataTable WITHOUT built-in search/pagination
            var table = $('.datanew').DataTable({
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

            // Initial fetch
            fetchExpenseTypes(currentPage);

            // Search input handler
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchExpenseTypes(1);
            });

            // Per‑page change handler
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchExpenseTypes(1);
            });

            // Page number click handler
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchExpenseTypes(page);
                }
            });

            function capitalizeWords(text) {
                if (!text) return '';
                return text.replace(/\b\w/g, char => char.toUpperCase());
            }

            function fetchExpenseTypes(page = 1) {
                let url = `/api/expense-types?page=${page}&per_page=${perPage}`;
                if (selectedSubAdminId) {
                    url += `&selectedSubAdminId=${selectedSubAdminId}`;
                }
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let items = response.data;
                            let pagination = response.pagination;

                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;

                            updatePaginationUI(pagination);

                            // Build table rows
                            let tableRows = [];
                            items.forEach(function(item, index) {
                                // Calculate correct serial number based on current page and perPage
                                let serial = (pagination.current_page - 1) * pagination
                                    .per_page + (index + 1);

                                // Delete button permission (same as before)
                                let deleteBtn = '';
                                if (
                                    userRole !== 'sales-manager' &&
                                    userRole !== 'purchase-manager' &&
                                    userRole !== 'inventory-manager'
                                ) {
                                    @if (app('hasPermission')(5, 'delete'))
                                        deleteBtn = `
                                        <a class="me-3 delete-btn" href="javascript:void(0);" data-id="${item.id}">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                        </a>`;
                                    @endif
                                }

                                // Action buttons (edit always shown if permission)
                                let actionButtons = `
                                @if (app('hasPermission')(5, 'view'))
                                    <a class="me-3" href="/edit-expense-type/${item.id}">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                    </a>
                                @endif
                                ${deleteBtn}
                            `;

                                tableRows.push([
                                    serial,
                                    capitalizeWords(item.type),
                                    actionButtons
                                ]);
                            });

                            table.clear().rows.add(tableRows).draw();
                            $('.pagination-controls').show();
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html(
                                '<tr><td colspan="3">No expense types found</td></tr>');
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch expense types!',
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

            // Delete handler (unchanged)
            $(document).on("click", ".delete-btn", function() {
                let id = $(this).data("id");

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
                            url: `/api/expenses-type/${id}`,
                            type: "POST",
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content"),
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: "Delete Record successfully",
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });
                                fetchExpenseTypes(currentPage); // Refresh current page
                            },
                            error: function(xhr) {
                                let message = 'Something went wrong.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Cannot Delete',
                                    text: message,
                                    confirmButtonColor: '#ff9f43'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
