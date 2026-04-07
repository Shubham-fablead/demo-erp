@extends('layout.app')

@section('title', 'Unit List')

@section('content')
    <style>
        /* === Pagination styling from category list === */
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
            top: 7px !important;
        }

        /* Hide default DataTables elements */
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

        /* Sorting column alignment */
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* Unit name proper word wrapping */
        table.datanew tbody td:first-child {
            vertical-align: middle;
        }

        table.datanew tbody td:first-child a {
            display: flex;
            align-items: center;
            max-width: 100%;
            text-decoration: none;
            color: inherit;
        }

        /* Correct wrapping behaviour */
        .unit-name {
            display: inline-block;
            font-size: 14px;
            line-height: 1.4;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }

        /* Desktop: show all columns normally */
        @media (min-width: 769px) {

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }

            /* Hide mobile action buttons on desktop */
            .mobile-action-buttons {
                display: none !important;
            }
        }

        /* Mobile responsive design */
        @media (max-width: 768px) {

            /* Hide non-essential columns, show Details toggle */
            table.datanew thead th:nth-child(3),
            table.datanew thead th:nth-child(4),
            table.datanew tbody td:nth-child(3),
            table.datanew tbody td:nth-child(4) {
                display: none !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 60px;
            }

            /* Hide desktop action buttons on mobile */
            .desktop-actions {
                display: none !important;
            }

            /* Fix for first column - Unit Name */
            table.datanew tbody td:first-child {
                display: table-cell !important;
                width: auto;
                max-width: calc(100vw - 80px);
                padding: 12px 8px;
            }

            table.datanew tbody td:first-child>div {
                width: 100%;
            }

            table.datanew tbody td:first-child a {
                display: flex !important;
                align-items: center !important;
                text-align: left !important;
                width: 100%;
                min-height: 44px;
            }

            .unit-name {
                display: block !important;
                width: 100% !important;
                font-size: 14px !important;
                line-height: 1.4 !important;
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
                padding-right: 8px;
            }

            /* Style for toggle icon - always orange */
            .toggle-details {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 44px;
                height: 44px;
                text-decoration: none;
                cursor: pointer;
            }

            .toggle-details i {
                font-size: 24px;
                transition: all 0.2s ease;
            }

            /* Collapsible details styling */
            .collapse .card.card-body {
                background-color: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                margin-top: 8px;
                padding: 12px;
            }

            .collapse .card-body p {
                margin-bottom: 12px;
                font-size: 13px;
                color: #6c757d;
            }

            .collapse .card-body p strong {
                color: #495057;
                font-weight: 600;
            }

            /* Mobile action buttons styling - only in collapsible */
            .mobile-action-buttons {
                display: flex !important;
                flex-direction: row;
                justify-content: flex-start;
                align-items: center;
                gap: 12px;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px solid #e9ecef;
            }

            .mobile-action-buttons a {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                gap: 8px;
                padding: 8px 16px;
                /* background-color: #ffffff;
                    border: 1px solid #e9ecef;
                    border-radius: 6px; */
                text-decoration: none;
                color: #2c3e50;
                font-size: 13px;
                font-weight: 500;
                transition: all 0.2s;
                cursor: pointer;
            }

            .mobile-action-buttons a:hover,
            .mobile-action-buttons a:active {
                background-color: #e9ecef;
            }

            .mobile-action-buttons img {
                width: 25px;
                height: 25px;
            }

            /* Pagination adjustments */
            .pagination-controls {
                flex-direction: column;
                gap: 15px;
            }

            .pagination .page-item .page-link {
                padding: 4px 12px;
                font-size: 12px;
            }
        }

        /* Tablet specific fixes (769px–1024px) */
        @media (min-width: 769px) and (max-width: 1024px) {

            /* Keep table structure intact */
            table.datanew tbody td,
            table.datanew thead th {
                display: table-cell !important;
                vertical-align: middle;
            }

            /* Hide mobile toggle column */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }

            /* Unit column alignment */
            table.datanew tbody td:first-child {
                white-space: normal !important;
            }

            table.datanew tbody td:first-child a {
                display: flex !important;
                align-items: center;
                gap: 10px;
            }

            /* Proper wrapping without breaking layout */
            .unit-name {
                max-width: 300px;
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: anywhere;
            }

            /* Improve spacing */
            .table-container {
                /* overflow-x: auto; */
                -webkit-overflow-scrolling: touch;
            }

            /* Pagination alignment */
            .pagination-controls {
                flex-wrap: wrap;
                gap: 10px;
            }

            /* Hide mobile action buttons on tablet */
            .mobile-action-buttons {
                display: none !important;
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

        /* Desktop action buttons styling */
        .action-buttons {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .action-buttons a {
            display: inline-flex;
            align-items: center;
            transition: 0.2s;
            cursor: pointer;
        }

        .action-buttons img {
            width: 20px;
            height: 20px;
        }

        /* Ensure table container handles overflow */
        .table-container {
            /* overflow-x: auto; */
            width: 100%;
        }

        table.datanew {
            width: 100%;
            min-width: 300px;
        }
    </style>

    @if (session('error'))
        <div class="alert alert-danger" id="error-message">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(function() {
                let alert = document.getElementById('error-message');
                if (alert) {
                    alert.classList.add('hidden');
                    setTimeout(() => alert.style.display = 'none', 500);
                }
            }, 4000);
        </script>
    @endif

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Units</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(6, 'add'))
                    <a href="javascript:void(0);" class="btn btn-sm btn-added" data-bs-toggle="modal"
                        data-bs-target="#addUnitModal">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">
                        New Unit
                    </a>
                @endif
            </div>
        </div>

        <!-- Add Unit Modal -->
        <div class="modal fade" id="addUnitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <form id="addUnitForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" name="unitname" class="form-control" placeholder="Enter Unit Name">
                                <span class="error_unitname text-danger"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="saveUnitBtn">Save Unit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Unit Modal -->
        <div class="modal fade" id="editUnitModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Unit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <form id="editUnitForm">
                        @csrf
                        <input type="hidden" id="edit_unit_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                                <input type="text" id="edit_unit_name" class="form-control">
                                <span class="error_edit_unitname text-danger"></span>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="updateUnitBtn">Update Unit</button>
                        </div>
                    </form>
                </div>
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
                                <th>Unit Name</th>
                                <th class="details-column">Details</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated by JavaScript -->
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

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

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

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            // Initial fetch
            fetchUnits(currentPage);

            // Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchUnits(1);
            });

            // Fetch units with pagination & search
            function fetchUnits(page = 1) {
                let url = `/api/units?page=${page}&per_page=${perPage}`;
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
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let units = response.data;
                            let pagination = response.pagination;

                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;
                            updatePaginationUI(pagination);

                            let tableBody = [];

                            // Function to capitalize first letter
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.charAt(0).toUpperCase() + str.slice(1);
                            }

                            units.forEach(unit => {
                                let createdAt = 'N/A';
                                if (unit.created_at) {
                                    let parts = unit.created_at.split('T')[0].split('-');
                                    if (parts.length === 3) {
                                        createdAt = parts[2] + '/' + parts[1] + '/' + parts[0];
                                    }
                                }

                                let unitName = capitalizeWords(unit.unit_name);

                                // Details toggle for mobile with plus icon (orange)
                                let detailsToggle = `
                                    <a href="javascript:void(0);" class="toggle-details" data-target="details-${unit.id}">
                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                    </a>
                                `;

                                tableBody.push([
                                    // Column 1: Unit Name with collapsible details (action buttons ONLY in collapsible)
                                    `<div class="unit-wrapper">
                                        <a href="javascript:void(0);" class="d-flex align-items-center unit-link">
                                            <span class="unit-name">${escapeHtml(unitName)}</span>
                                        </a>
                                        <!-- Collapsible Details (visible only on mobile) with action buttons -->
                                        <div class="collapse d-lg-none" id="details-${unit.id}">
                                            <div class="card card-body">
                                                <p class="mb-2">
                                                    <strong><i class="far fa-calendar-alt me-1"></i> Created At:</strong>
                                                    ${createdAt}
                                                </p>
                                                <div class="mobile-action-buttons">
                                                    @if (app('hasPermission')(6, 'edit'))
                                                    <a class="edit-unit-mobile" href="javascript:void(0);" data-id="${unit.id}" data-name="${escapeHtml(unit.unit_name)}">
                                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="Edit">

                                                    </a>
                                                    @endif
                                                    @if (app('hasPermission')(6, 'delete'))
                                                    <a class="delete-unit-mobile" href="javascript:void(0);" data-id="${unit.id}">
                                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="Delete">

                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>`,
                                    // Column 2: Details Toggle (only for mobile)
                                    detailsToggle,
                                    // Column 3: Created At (hidden on mobile)
                                    createdAt,
                                    // Column 4: Action Buttons (desktop only)
                                    `<div class="action-buttons desktop-actions">
                                        <a class="edit-unit" href="javascript:void(0);" data-id="${unit.id}" data-name="${escapeHtml(unit.unit_name)}">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                        </a>
                                        <a class="delete-unit" href="javascript:void(0);" data-id="${unit.id}">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                        </a>
                                    </div>`
                                ]);
                            });

                            table.clear().rows.add(tableBody).draw();

                            // Re-initialize collapse functionality for dynamically added elements
                            initializeCollapseEvents();
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html(
                                '<tr><td colspan="4" class="text-center">No units found</td></tr>');
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching units:', xhr);
                        $(".datanew tbody").html(
                            '<tr><td colspan="4" class="text-center text-danger">Error loading units</td></tr>'
                            );
                    }
                });
            }

            // Initialize collapse events for mobile toggle
            function initializeCollapseEvents() {
                // Remove any existing event handlers first
                $('.toggle-details').off('click');

                $('.toggle-details').on('click', function(e) {
                    e.preventDefault();
                    let targetId = $(this).data('target');
                    let $target = $('#' + targetId);
                    let $icon = $(this).find('i');

                    // Toggle the collapse
                    $target.collapse('toggle');
                });

                // Handle collapse show event (when expanding)
                $('.collapse').off('show.bs.collapse').on('show.bs.collapse', function() {
                    let targetId = $(this).attr('id');
                    let $toggleBtn = $(`.toggle-details[data-target="${targetId}"]`);
                    let $icon = $toggleBtn.find('i');

                    // Change to minus icon with orange color when expanding
                    $icon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                    $icon.css('color', 'red');
                });

                // Handle collapse hide event (when collapsing)
                $('.collapse').off('hide.bs.collapse').on('hide.bs.collapse', function() {
                    let targetId = $(this).attr('id');
                    let $toggleBtn = $(`.toggle-details[data-target="${targetId}"]`);
                    let $icon = $toggleBtn.find('i');

                    // Change to plus icon with orange color when collapsing
                    $icon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                    $icon.css('color', '#ff9f43');
                });
            }

            // Helper function to generate action buttons (desktop)
            function getActionButtons(unitId, unitName) {
                return `
                    <a class="edit-unit" href="javascript:void(0);" data-id="${unitId}" data-name="${escapeHtml(unitName)}">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="Edit">
                    </a>
                    <a class="delete-unit" href="javascript:void(0);" data-id="${unitId}">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="Delete">
                    </a>
                `;
            }

            // Update pagination controls
            function updatePaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;
                if (to > pagination.total) to = pagination.total;
                if (pagination.total === 0) from = 0;

                $('#pagination-from').text(from);
                $('#pagination-to').text(to);
                $('#pagination-total').text(pagination.total);

                let startPage = Math.max(1, pagination.current_page - 2);
                let endPage = Math.min(pagination.last_page, startPage + 4);
                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                let html = '';
                for (let i = startPage; i <= endPage; i++) {
                    html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>`;
                }
                $('#pagination-numbers').html(html);
                $('.pagination-controls').show();
            }

            // Handle page number clicks
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchUnits(page);
                }
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchUnits(1);
            });

            // Add Unit form submit
            $('#addUnitForm').submit(function(e) {
                e.preventDefault();
                let $btn = $('#saveUnitBtn');
                $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...').prop(
                    'disabled', true);
                $('.error_unitname').text('');

                let formData = $(this).serialize();
                if (selectedSubAdminId) formData += '&selectedSubAdminId=' + selectedSubAdminId;

                $.ajax({
                    url: '/api/add-units',
                    type: 'POST',
                    data: formData,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                            $('#addUnitModal').modal('hide');
                            $('#addUnitForm')[0].reset();
                            fetchUnits(currentPage);
                        }
                        $btn.html('Save Unit').prop('disabled', false);
                    },
                    error: function(xhr) {
                        $btn.html('Save Unit').prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            if (xhr.responseJSON.errors.unitname) {
                                $('.error_unitname').text(xhr.responseJSON.errors.unitname[0]);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong!',
                                icon: 'error',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });

            // Edit: populate modal for desktop and mobile
            $(document).on('click', '.edit-unit, .edit-unit-mobile', function(e) {
                e.preventDefault();
                let id = $(this).data('id');
                let name = $(this).data('name');
                $('#edit_unit_id').val(id);
                $('#edit_unit_name').val(name);
                $('.error_edit_unitname').text('');
                $('#editUnitModal').modal('show');
            });

            // Update Unit
            $('#editUnitForm').submit(function(e) {
                e.preventDefault();
                let unitId = $('#edit_unit_id').val();
                let unitName = $('#edit_unit_name').val();
                let $btn = $('#updateUnitBtn');
                $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...').prop(
                    'disabled', true);
                $('.error_edit_unitname').text('');

                $.ajax({
                    url: '/api/update-unit/' + unitId,
                    type: 'POST',
                    data: {
                        unitname: unitName,
                        selectedSubAdminId: selectedSubAdminId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: 'Updated!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                            $('#editUnitModal').modal('hide');
                            fetchUnits(currentPage);
                        }
                        $btn.html('Update Unit').prop('disabled', false);
                    },
                    error: function(xhr) {
                        $btn.html('Update Unit').prop('disabled', false);
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            if (xhr.responseJSON.errors.unitname) {
                                $('.error_edit_unitname').text(xhr.responseJSON.errors.unitname[
                                    0]);
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Something went wrong!',
                                icon: 'error',
                                confirmButtonColor: '#ff9f43',
                                confirmButtonText: 'OK'
                            });
                        }
                    }
                });
            });

            // Delete Unit (event delegation for both desktop and mobile)
            $(document).on('click', '.delete-unit, .delete-unit-mobile', function(e) {
                e.preventDefault();
                let unitId = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff9f43',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/api/delete-unit/' + unitId,
                            type: 'DELETE',
                            data: {
                                selectedSubAdminId: selectedSubAdminId,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonColor: '#ff9f43',
                                        confirmButtonText: 'OK'
                                    });
                                    fetchUnits(currentPage);
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: response.message ||
                                            'Failed to delete unit.',
                                        icon: 'error',
                                        confirmButtonColor: '#ff9f43',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr) {
                                let msg = 'Something went wrong!';
                                if (xhr.responseJSON && xhr.responseJSON.message) msg =
                                    xhr.responseJSON.message;
                                Swal.fire({
                                    title: 'Error!',
                                    text: msg,
                                    icon: 'error',
                                    confirmButtonColor: '#ff9f43',
                                    confirmButtonText: 'OK'
                                });
                            }
                        });
                    }
                });
            });

            // Helper function to escape HTML
            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/[&<>]/g, function(m) {
                    if (m === '&') return '&amp;';
                    if (m === '<') return '&lt;';
                    if (m === '>') return '&gt;';
                    return m;
                });
            }
        });
    </script>
@endpush
