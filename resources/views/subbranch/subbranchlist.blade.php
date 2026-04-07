@extends('layout.app')

@section('title', 'Branch List')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
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
            top: 4px !important;
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

        /* Branch name styling with image */
        .branch-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 12px;
        }

        .branch-name {
            display: inline-block;
            font-weight: 500;
            font-size: 14px;
            line-height: 1.4;
            word-break: break-word;
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {
            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 768px) {
            .collapse .card-body .mt-2 {
                display: flex;
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .collapse .card-body .mt-2 a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 44px;
                min-height: 44px;
                padding: 8px;
                background-color: #f8f9fa;
                border-radius: 6px;
                transition: background-color 0.2s;
            }

            .collapse .card-body .mt-2 a:hover,
            .collapse .card-body .mt-2 a:active {
                background-color: #e9ecef;
            }

            .collapse .card-body .mt-2 img {
                width: 20px;
                height: 20px;
            }

            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
            }

            .toggle-details i {
                font-size: 24px;
            }

            /* Hide all columns except Branch Name and Details toggle */
            table.datanew thead th:nth-child(n+3),
            table.datanew tbody td:nth-child(n+3) {
                display: none !important;
            }

            /* Show Details toggle column */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            /* Branch name column styling for mobile */
            table.datanew tbody td:first-child {
                max-width: calc(100vw - 100px) !important;
                white-space: normal !important;
            }

            table.datanew tbody td:first-child a {
                display: flex !important;
                align-items: center !important;
                text-align: left !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
            }

            .branch-name {
                display: inline-block !important;
                max-width: calc(100% - 70px) !important;
                font-size: 14px !important;
                word-break: break-word !important;
            }

            .branch-img {
                width: 40px;
                height: 40px;
                margin-right: 10px;
                flex-shrink: 0;
            }
        }

        /* Tablet specific fixes (769px–1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {
            table.datanew tbody td,
            table.datanew thead th {
                display: table-cell !important;
                vertical-align: middle;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }

            table.datanew tbody td:first-child a {
                display: flex !important;
                align-items: center;
                gap: 10px;
            }

            .branch-name {
                max-width: 250px;
                white-space: normal !important;
                word-break: break-word !important;
            }

            .table-container {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .pagination-controls {
                flex-wrap: wrap;
                gap: 10px;
            }
        }

        @media screen and (max-width: 767px) {
            .table-scroll-top {
                display: block;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .table-top .search-set {
                display: flex;
                justify-content: flex-start !important;
                width: 100%;
            }

            .table-top .search-input {
                margin-left: 0 !important;
            }
        }

        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Branches</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(8, 'add'))
                    <a href="{{ route('subbranch.add') }}" class="btn btn-added btn-sm">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img">
                        New Branch
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img" style="margin-top: 5px;">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>

                <div class="table-container">
                    <table class="table datanew">
                        <thead>
                                <th>Branch Name</th>
                                <th class="details-column">Details</th>
                                <th>Branch Role</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Country</th>
                                <th>City</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm" style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span id="pagination-total">0</span> items
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
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const imagePath = "{{ env('ImagePath') }}";

            // Initialize DataTable
            var table = $('.datanew').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false
            });

            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            // Helper function to capitalize first letter
            function capitalizeFirstLetter(str) {
                if (!str) return 'N/A';
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            // Helper function to get action buttons HTML
            function getActionButtons(branchId) {
                return `
                    <a class="me-3" href="/view-subbranch/${branchId}">
                        <img src="${imagePath}admin/assets/img/icons/eye.svg" alt="View">
                    </a>
                    <a class="me-3" href="/edit-subbranch/${branchId}">
                        <img src="${imagePath}admin/assets/img/icons/edit.svg" alt="Edit">
                    </a>
                    <a class="me-3 confirm-text delete-btn" href="javascript:void(0);" data-id="${branchId}">
                        <img src="${imagePath}admin/assets/img/icons/delete.svg" alt="Delete">
                    </a>
                `;
            }

            // Fetch branches with pagination
            function fetchBranches(page = 1) {
                let url = `/api/getAllSubbranch?page=${page}&per_page=${perPage}`;
                if (selectedSubAdminId) {
                    url += `&selectedSubAdminId=${selectedSubAdminId}`;
                }
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    beforeSend: function() {
                        $(".datanew tbody").html(
                            `<tr><td colspan="8" class="text-center">Loading...</td></tr>`
                        );
                    },
                    success: function(res) {
                        if (res.status) {
                            let branches = res.data;
                            let pagination = res.pagination;

                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;
                            updatePaginationUI(pagination);

                            renderTable(branches);
                        } else {
                            $(".datanew tbody").html(
                                `<tr><td colspan="8" class="text-center">No branches found.</td></tr>`
                            );
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching branches:", xhr);
                        $(".datanew tbody").html(
                            `<tr><td colspan="8" class="text-center text-danger">Error loading data.</td></tr>`
                        );
                    }
                });
            }

            // Render table with expandable details for mobile
            function renderTable(branches) {
                let tableBody = [];

                branches.forEach(branch => {
                    let profileImage = branch.profile_image ?
                        imagePath + '/storage/' + branch.profile_image :
                        imagePath + 'admin/assets/img/customer/customer5.jpg';

                    let branchName = capitalizeFirstLetter(branch.name);
                    let branchRole = capitalizeFirstLetter(branch.role);
                    let country = capitalizeFirstLetter(branch.user_detail?.country);
                    let city = capitalizeFirstLetter(branch.user_detail?.city);

                    // Format phone and email
                    let phone = branch.phone ?? 'N/A';
                    let email = branch.email ?? 'N/A';

                    // Details toggle for mobile
                    let detailsToggle = `
                        <a href="#branch-details-${branch.id}" class="toggle-details" data-bs-toggle="collapse">
                            <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                        </a>
                    `;

                    // Mobile expandable details content
                    let mobileDetails = `
                        <div class="collapse mt-2 d-lg-none" id="branch-details-${branch.id}">
                            <div class="card card-body p-3 bg-light border">
                                <div class="mb-2">
                                    <strong>Branch Role:</strong> ${branchRole}
                                </div>
                                <div class="mb-2">
                                    <strong>Phone:</strong> ${phone}
                                </div>
                                <div class="mb-2">
                                    <strong>Email:</strong> ${email}
                                </div>
                                <div class="mb-2">
                                    <strong>Country:</strong> ${country}
                                </div>
                                <div class="mb-2">
                                    <strong>City:</strong> ${city}
                                </div>
                                <div class="mt-3">
                                    <strong>Actions:</strong>
                                    <div class="mt-2">
                                        ${getActionButtons(branch.id)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    tableBody.push([
                        // Column 1: Branch Name with Image and Mobile Details
                        `<div>
                            <div style="display: flex; align-items: center;">
                                <a href="/view-subbranch/${branch.id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                    <img src="${profileImage}" alt="Branch" class="branch-img">
                                    <span class="branch-name">${branchName}</span>
                                </a>
                            </div>
                            ${mobileDetails}
                        </div>`,
                        // Column 2: Details Toggle (only for mobile)
                        detailsToggle,
                        // Column 3: Branch Role
                        branchRole,
                        // Column 4: Phone
                        phone,
                        // Column 5: Email
                        email,
                        // Column 6: Country
                        country,
                        // Column 7: City
                        city,
                        // Column 8: Action Buttons
                        getActionButtons(branch.id)
                    ]);
                });

                table.clear().rows.add(tableBody).draw();
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
                    fetchBranches(page);
                }
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchBranches(1);
            });

            // Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchBranches(1);
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

            // Handle Delete
            $(document).on("click", ".delete-btn", function() {
                let branchId = $(this).data("id");

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
                            url: `/api/deleteSubbranch/${branchId}`,
                            type: "POST",
                            headers: {
                                "Authorization": "Bearer " + authToken,
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: response.status ? "Deleted!" : "Notice",
                                    text: response.message,
                                    icon: response.status ? "success" : "info",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });

                                if (response.status) {
                                    fetchBranches(currentPage);
                                }
                            },
                            error: function(xhr) {
                                let errorMessage = "Something went wrong.";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }

                                Swal.fire({
                                    title: "Error!",
                                    text: errorMessage,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });
                            },
                        });
                    }
                });
            });

            // Initial fetch
            fetchBranches();
        });
    </script>
@endpush
