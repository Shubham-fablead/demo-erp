@extends('layout.app')

@section('title', 'Labour Items')

@section('content')
    <style>
        /* === Pagination styling === */
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

        /* Search input */
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

        /* Hide default DataTables UI */
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

        /* =============================================
                               TABLE BASE STYLES
                            ============================================= */
        .table-container {
            overflow-x: auto;
            width: 100%;
        }

        table.datanew {
            width: 100%;
            min-width: 300px;
            border-collapse: collapse;
        }

        table.datanew thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            /* font-size: 13px; */
            padding: 12px 20px;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
            vertical-align: middle;
        }

        table.datanew tbody tr {
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.15s ease;
        }

        table.datanew tbody tr:hover {
            background-color: #f9f9f9;
        }

        table.datanew tbody td {
            padding: 12px 16px;
            font-size: 14px;
            color: #2c3e50;
            vertical-align: middle;
        }

        /* Item name */
        .item-name {
            font-size: 14px;
            line-height: 1.4;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
            color: #2c3e50;
        }

        /* =============================================
                               DESKTOP ACTION BUTTONS
                            ============================================= */
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .action-buttons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
            cursor: pointer;
            text-decoration: none;
        }

        .action-buttons a:hover {
            background-color: #f0f0f0;
        }

        .action-buttons img {
            width: 18px;
            height: 18px;
        }

        /* =============================================
                               MOBILE TOGGLE BUTTON
                            ============================================= */
        .toggle-details {
            display: inline-flex;
            align-items: center;
            justify-content: flex-end;
            width: 44px;
            height: 44px;
            text-decoration: none;
            cursor: pointer;
        }

        .toggle-details i {
            font-size: 24px;
            /* transition: all 0.2s ease; */
        }

        /* =============================================
                               MOBILE (≤768px)
                            ============================================= */
        @media (max-width: 768px) {

            /* Hide Price, Created At, Action columns */
            table.datanew thead th:nth-child(3),
            table.datanew thead th:nth-child(4),
            table.datanew thead th:nth-child(5),
            table.datanew tbody td:nth-child(3),
            table.datanew tbody td:nth-child(4),
            table.datanew tbody td:nth-child(5) {
                display: none !important;
            }

            /* First column */
            table.datanew tbody td:first-child {
                max-width: calc(100vw - 80px);
                padding: 12px 8px;
            }

            .item-name {
                display: block !important;
                width: 100% !important;
                white-space: normal !important;
                word-break: break-word !important;
                overflow-wrap: anywhere !important;
                padding-right: 8px;
            }

            /* Collapsible card */
            .collapse .card.card-body {
                background-color: #ffffff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                margin-top: 8px;
                padding: 12px;
                width: 105%;
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

            /* Mobile action buttons inside collapse */
            .mobile-action-buttons {
                display: flex !important;
                flex-direction: row;
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
                padding: 8px 16px;
                border-radius: 6px;
                text-decoration: none;
                font-size: 13px;
                font-weight: 500;
                transition: background-color 0.2s;
                cursor: pointer;
            }

            .mobile-action-buttons a:hover {
                background-color: #e9ecef;
            }

            .mobile-action-buttons img {
                width: 18px;
                height: 18px;
            }

            /* Hide desktop-only action column on mobile */
            .desktop-actions {
                display: none !important;
            }

            /* Pagination */
            .pagination-controls {
                flex-direction: column;
                gap: 15px;
            }

            .pagination .page-item .page-link {
                padding: 4px 12px;
                font-size: 12px;
            }

            .details-col-th {
                width: unset !important;
            }
        }

        /* =============================================
                               DESKTOP (≥769px)
                            ============================================= */
        @media (min-width: 769px) {

            /* Mobile action buttons must NEVER show on desktop */
            .mobile-action-buttons {
                display: none !important;
            }

            /* Show desktop actions */
            .desktop-actions {
                display: flex !important;
            }

            /* Collapse content must NEVER show on desktop */
            .item-wrapper .collapse {
                display: none !important;
            }
        }

        /* Fade out */
        .fade-out {
            opacity: 1;
            transition: opacity 0.5s ease-out;
        }

        .fade-out.hidden {
            opacity: 0;
        }

        .table tbody tr td {
            /* padding: 10px; */
            color: #637381 !important;
            font-weight: 500 !important;
            border-bottom: 1px solid #e9ecef !important;
            vertical-align: middle !important;
            /* white-space: nowrap; */
        }
    </style>

    @if (session('error'))
        <div class="alert alert-danger" id="error-message">{{ session('error') }}</div>
        <script>
            setTimeout(function() {
                let el = document.getElementById('error-message');
                if (el) {
                    el.classList.add('hidden');
                    setTimeout(() => el.style.display = 'none', 500);
                }
            }, 4000);
        </script>
    @endif

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Labour Items</h4>
            </div>
            <div class="page-btn">
                <button class="btn btn-sm btn-added" id="addLabourItemBtn">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">
                    New Labour Item
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-start w-100">
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
                    <table class="table datanew" id="labourTable">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th class="details-col-th d-flex justify-content-end">Details</th>
                                <th>Price</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size:14px;color:#555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width:auto;border:1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size:14px;color:#555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    <div class="modal fade" id="labourItemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="labourItemForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="labourItemModalLabel">Add Labour Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">x</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="labour_item_id">
                        <div class="form-group mb-3">
                            <label>Item Name <span class="text-danger">*</span></label>
                            <input type="text" name="item_name" id="item_name" class="form-control"
                                placeholder="Enter item name">
                            <span class="text-danger" id="item_name_error"></span>
                        </div>
                        <div class="form-group mb-3">
                            <label>Price <span class="text-danger">*</span></label>
                            <input type="number" name="price" id="price" class="form-control"
                                placeholder="Enter price" step="0.01">
                            <span class="text-danger" id="price_error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const $labourSubmitBtn = $('#labourItemForm button[type="submit"]');
            const labourSubmitBtnDefaultHtml = $labourSubmitBtn.html();

            function toggleLabourSubmitLoading(isLoading) {
                if (isLoading) {
                    $labourSubmitBtn
                        .prop('disabled', true)
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $labourSubmitBtn
                        .prop('disabled', false)
                        .html(labourSubmitBtnDefaultHtml);
                }
            }

            // ── DataTable init — Details column (index 1) starts hidden ──
            var table = $('#labourTable').DataTable({
                destroy: true,
                bFilter: false,
                paging: false,
                info: false,
                searching: false,
                dom: 't',
                ordering: false,
                columnDefs: [{
                    targets: 1, // "Details" toggle column — 0-based
                    visible: false, // hidden by default (desktop)
                    searchable: false
                }]
            });

            // Show/hide Details column based on viewport
            function syncDetailsColumn() {
                var isMobile = window.innerWidth <= 768;
                table.column(1).visible(isMobile);
                setTimeout(function() {
                    if (table) {
                        table.columns.adjust();
                        table.draw(false);
                    }
                }, 100);
            }
            syncDetailsColumn();
            $(window).on('resize', syncDetailsColumn);

            // ── Pagination state ──
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            fetchLabourItems(currentPage);

            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchLabourItems(1);
            });

            // ── Fetch & render ──
            function fetchLabourItems(page = 1) {
                let url = `/api/get-all-labour-items?page=${page}&per_page=${perPage}`;
                if (selectedSubAdminId) url += `&selectedSubAdminId=${selectedSubAdminId}`;
                if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;

                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            currentPage = response.pagination.current_page;
                            lastPage = response.pagination.last_page;
                            updatePaginationUI(response.pagination);

                            let rows = [];
                            response.data.forEach(function(item) {
                                let createdAt = 'N/A';
                                if (item.created_at) {
                                    let p = item.created_at.split('T')[0].split('-');
                                    if (p.length === 3) createdAt = p[2] + '/' + p[1] + '/' + p[
                                        0];
                                }

                                let itemName = item.item_name ?
                                    item.item_name.charAt(0).toUpperCase() + item.item_name
                                    .slice(1) :
                                    'N/A';

                                let price = parseFloat(item.price).toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });

                                rows.push([
                                    // ── Col 0: Item Name + collapsible mobile block ──
                                    `<div class="item-wrapper">
                                <a href="javascript:void(0);" style="text-decoration:none;color:inherit;display:flex;align-items:center;">
                                    <span class="item-name">${escapeHtml(itemName)}</span>
                                </a>
                                <div class="collapse" id="details-${item.id}">
                                    <div class="card card-body mt-2">
                                        <p class="mb-2"><strong>Price:</strong> ₹ ${price}</p>
                                        <p class="mb-2"><strong>Created At:</strong> ${createdAt}</p>
                                        <div class="mobile-action-buttons">
                                            <a class="edit-labour-item" href="javascript:void(0);"
                                               data-id="${item.id}"
                                               data-name="${escapeHtml(item.item_name)}"
                                               data-price="${item.price}">
                                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                            </a>
                                            <a class="delete-labour-item" href="javascript:void(0);" data-id="${item.id}">
                                                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>`,

                                    // ── Col 1: Toggle icon (shown only on mobile via DataTables) ──
                                    `<a href="javascript:void(0);" class="toggle-details" data-target="details-${item.id}" style="padding-left:80px;">
                                <i class="fas fa-plus-circle" style="color:#ff9f43;"></i>
                            </a>`,

                                    // ── Col 2: Price ──
                                    `₹ ${price}`,

                                    // ── Col 3: Created At ──
                                    createdAt,

                                    // ── Col 4: Action (desktop) ──
                                    `<div class="action-buttons desktop-actions">
                                <a class="edit-labour-item" href="javascript:void(0);"
                                   data-id="${item.id}"
                                   data-name="${escapeHtml(item.item_name)}"
                                   data-price="${item.price}">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                </a>
                                <a class="delete-labour-item" href="javascript:void(0);" data-id="${item.id}">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                </a>
                            </div>`
                                ]);
                            });

                            table.clear().rows.add(rows).draw();

                            // Re-apply column visibility after draw
                            syncDetailsColumn();
                            initCollapseEvents();

                        } else {
                            table.clear().draw();
                            $("#labourTable tbody").html(
                                '<tr><td colspan="5" class="text-center">No labour items found</td></tr>'
                            );
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr);
                        $("#labourTable tbody").html(
                            '<tr><td colspan="5" class="text-center text-danger">Error loading labour items</td></tr>'
                        );
                    }
                });
            }

            // ── Collapse toggle events ──
            function initCollapseEvents() {
                $('.toggle-details').off('click').on('click', function(e) {
                    e.preventDefault();
                    $('#' + $(this).data('target')).collapse('toggle');
                });

                $('.collapse')
                    .off('show.bs.collapse').on('show.bs.collapse', function() {
                        $(`.toggle-details[data-target="${$(this).attr('id')}"]`)
                            .find('i').removeClass('fa-plus-circle').addClass('fa-minus-circle').css('color',
                                'red');
                    })
                    .off('hide.bs.collapse').on('hide.bs.collapse', function() {
                        $(`.toggle-details[data-target="${$(this).attr('id')}"]`)
                            .find('i').removeClass('fa-minus-circle').addClass('fa-plus-circle').css('color',
                                '#ff9f43');
                    });
            }

            // ── Pagination UI ──
            function updatePaginationUI(pagination) {
                let from = pagination.total === 0 ? 0 : (pagination.current_page - 1) * pagination.per_page + 1;
                let to = Math.min(pagination.current_page * pagination.per_page, pagination.total);
                $('#pagination-from').text(from);
                $('#pagination-to').text(to);
                $('#pagination-total').text(pagination.total);

                let start = Math.max(1, pagination.current_page - 2);
                let end = Math.min(pagination.last_page, start + 4);
                if (end - start < 4) start = Math.max(1, end - 4);

                let html = '';
                for (let i = start; i <= end; i++) {
                    html += `<li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
            </li>`;
                }
                $('#pagination-numbers').html(html);
                $('.pagination-controls').show();
            }

            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) fetchLabourItems(page);
            });

            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchLabourItems(1);
            });

            // ── Modal: Add ──
            $('#addLabourItemBtn').click(function() {
                $('#labourItemForm')[0].reset();
                $('#labour_item_id').val('');
                $('#item_name_error, #price_error').text('');
                $('#labourItemModalLabel').text('Add Labour Item');
                $('#labourItemModal').modal('show');
            });

            // ── Modal: Edit ──
            function openEditModal(id, name, price) {
                $('#labour_item_id').val(id);
                $('#item_name').val(name);
                $('#price').val(price);
                $('#item_name_error, #price_error').text('');
                $('#labourItemModalLabel').text('Edit Labour Item');
                $('#labourItemModal').modal('show');
            }
            $(document).on('click', '.edit-labour-item', function() {
                openEditModal($(this).data('id'), $(this).data('name'), $(this).data('price'));
            });

            // ── Form Submit ──
            $('#labourItemForm').on('submit', function(e) {
                e.preventDefault();
                if ($labourSubmitBtn.prop('disabled')) {
                    return;
                }
                $('#item_name_error, #price_error').text('');

                let id = $('#labour_item_id').val();
                let formData = new FormData(this);
                if (selectedSubAdminId) formData.append("sub_admin_id", selectedSubAdminId);

                $.ajax({
                    url: id ? `/api/update-labour-item/${id}` : "/api/add-labour-item",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        toggleLabourSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#ff9f43"
                        });
                        $('#labourItemModal').modal('hide');
                        fetchLabourItems(currentPage);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let e = xhr.responseJSON.errors;
                            if (e.item_name) $('#item_name_error').text(e.item_name[0]);
                            if (e.price) $('#price_error').text(e.price[0]);
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: "Something went wrong!",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    },
                    complete: function() {
                        toggleLabourSubmitLoading(false);
                    }
                });
            });

            // ── Delete ──
            $(document).on('click', '.delete-labour-item', function() {
                let id = $(this).data('id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "This will permanently delete the labour item!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    confirmButtonColor: "#ff9f43",
                    cancelButtonColor: "#6c757d"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/delete-labour-item/${id}`,
                            type: "POST",
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(response) {
                                let icon = response.status ? "success" : "error";
                                let title = response.status ? "Deleted!" : "Error!";
                                Swal.fire({
                                    title: title,
                                    text: response.message,
                                    icon: icon,
                                    confirmButtonColor: "#ff9f43"
                                });
                                if (response.status) fetchLabourItems(currentPage);
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    title: "Error!",
                                    text: xhr.responseJSON?.message ||
                                        "Something went wrong!",
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43"
                                });
                            }
                        });
                    }
                });
            });

            // ── Helper ──
            function escapeHtml(str) {
                if (!str) return '';
                return String(str).replace(/[&<>]/g, function(m) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;'
                    } [m] || m;
                });
            }
        });
    </script>
@endpush
