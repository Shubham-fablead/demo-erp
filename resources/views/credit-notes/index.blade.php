@extends('layout.app')
@section('title', 'Notes List')

@push('css')
    <style>
        .table tbody tr td {
            white-space: normal !important;
            word-break: break-word;
            word-wrap: break-word;
        }

        /* Responsive Table Styling */
        @media screen and (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }
        }

        @media (min-width: 768px) {

            .datanew1 thead th.details-column,
            .datanew1 tbody td.details-control {
                display: none !important;
            }
        }

        @media (max-width: 767px) {

            .datanew1 thead th:nth-child(n+2):not(.details-column),
            .datanew1 tbody td:nth-child(n+2):not(.details-control) {
                display: none !important;
            }

            .datanew1 thead th:first-child,
            .datanew1 tbody td:first-child {
                display: table-cell !important;
            }

            .datanew1 thead th.details-column,
            .datanew1 tbody td.details-control {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }
        }

        .toggle-details i {
            font-size: 18px;
            color: #ff9f43;
            cursor: pointer;
        }

        .collapse-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 3px solid #ff9b44;
        }

        .detail-item {
            display: flex;
            margin-bottom: 8px;
            font-size: 16px;
            gap:10px;
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

        /* Prevent action column shrinking */
        .datanew1 tbody td:nth-child(3) {
            white-space: nowrap;
        }

        /* Mobile responsive wrap */
        @media (max-width: 768px) {
            .datanew1 tbody td:nth-child(2) {
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
@endpush

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Credit Notes</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(27, 'add'))
                    <a href="javascript:void(0);" class="btn btn-added btn-sm" data-bs-toggle="modal"
                        data-bs-target="#addCreditNoteModal">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1"
                            alt="img">New
                        Credit Notes
                    </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {{-- Search Input --}}
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

                {{-- <div class="table-responsive">
                    <table class="table datanew1"> --}}

                <div class="table-container">
                    <table class="table datanew1">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Created At</th>
                                <th class="">Action</th>
                                <th class="details-column">Details</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
                            {{-- Page numbers inserted here --}}
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Credit Note Modal -->
    <div class="modal fade" id="addCreditNoteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Credit Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Type Name<span class="manitory">*</span></label>
                                <input type="text" id="add_type_name" class="form-control" placeholder="Enter type name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-add">Save</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Credit Note Modal -->
    <div class="modal fade" id="editCreditNoteModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Credit Note</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_credit_note_id">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Type Name<span class="manitory">*</span></label>
                                <input type="text" id="edit_type_name" class="form-control"
                                    placeholder="Enter type name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-edit">Update</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const canAddCreditNoteType = @json(app('hasPermission')(27, 'add'));
            const canEditCreditNoteType = @json(app('hasPermission')(27, 'edit'));
            const canDeleteCreditNoteType = @json(app('hasPermission')(27, 'delete'));

            // Initialize DataTable WITHOUT built-in search/pagination
            var table = $('.datanew1').DataTable({
                destroy: true,
                paging: false,
                info: false,
                searching: false,
                dom: 't',
                ordering: false,
                columnDefs: [{
                    targets: 3,
                    className: 'details-control',
                    orderable: false
                }]
            });

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function buildTypeActionButtons(itemId) {
                let actions = '';

                if (canEditCreditNoteType) {
                    actions += `
                        <a href="javascript:void(0);" class="edit-credit-note mt-1" data-id="${itemId}">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                        </a>
                    `;
                }

                if (canDeleteCreditNoteType) {
                    actions += `
                        <a href="javascript:void(0);" class="delete-credit-note" data-id="${itemId}">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                        </a>
                    `;
                }

                return actions || '<span class="text-muted">-</span>';
            }

            // Initial fetch
            fetchCreditNotes(currentPage);

            // Search input handler
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchCreditNotes(1);
            });

            // Per‑page change handler
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchCreditNotes(1);
            });

            // Page number click handler
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchCreditNotes(page);
                }
            });

            function fetchCreditNotes(page = 1) {
                let url = "{{ route('credit-notes-types.index') }}";
                url += `?page=${page}&per_page=${perPage}`;
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
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let items = response.creditNotes;
                            let pagination = response.pagination;

                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;

                            updatePaginationUI(pagination);

                            let rows = [];
                            items.forEach(function(item, index) {
                                // Calculate correct serial based on current page
                                let serial = (pagination.current_page - 1) * pagination
                                    .per_page + (index + 1);

                                let createdAt = item.created_at ? new Date(item.created_at)
                                    .toLocaleDateString() : "N/A";
                                let detailsId =
                                `details-${item.id}`; // use item.id to avoid conflicts

                                // Build collapsible details content
                                let detailsContent = `
                                    <div class="collapse-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Created At:</span>
                                            <span class="detail-value">${createdAt}</span>
                                        </div>
                                        <div class="detail-item g-3">${buildTypeActionButtons(item.id)}</div>
                                    </div>
                                `;

                                // First column: Type Name + hidden collapsible div
                                let firstColumn = `
                                    <div>
                                        <span>${item.type_name}</span>
                                        <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                            ${detailsContent}
                                        </div>
                                    </div>
                                `;

                                // Action buttons for desktop
                                let actionButtons = `<div class="detail-item">${buildTypeActionButtons(item.id)}</div>`;

                                // Details toggle for mobile
                                let detailsToggle = `
                                    <a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse">
                                        <i class="fas fa-plus-circle"></i>
                                    </a>
                                `;

                                rows.push([
                                    firstColumn,
                                    createdAt,
                                    actionButtons,
                                    detailsToggle
                                ]);
                            });

                            table.clear().rows.add(rows).draw();
                            $('.pagination-controls').show();
                        } else {
                            table.clear().draw();
                            $(".datanew1 tbody").html(
                                '<tr><td colspan="4">No credit notes found</td></tr>');
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to fetch credit notes!',
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

            // Reset details when switching to desktop
            $(window).on('resize', function() {
                if ($(window).width() > 768) {
                    $('.collapse').removeClass('show');
                    $('.toggle-details i')
                        .removeClass('fa-minus-circle')
                        .addClass('fa-plus-circle')
                        .css('color', '#ff9f43');
                }
            });

            // --- CRUD operations (unchanged except refresh uses currentPage) ---

            function clearErrors() {
                $(".error-text").remove();
                $("input").removeClass("is-invalid");
            }

            function validateForm(modalId) {
                clearErrors();
                let isValid = true;
                let typeName = $(modalId + " [id$='_type_name']").val().trim();

                if (typeName === "") {
                    $(modalId + " [id$='_type_name']").addClass("is-invalid");
                    $(modalId + " [id$='_type_name']").after(
                        '<small class="text-danger error-text">Type Name is required.</small>');
                    isValid = false;
                }
                return isValid;
            }

            $(".btn-submit-add").on("click", function() {
                if (!canAddCreditNoteType) return;
                if (!validateForm("#addCreditNoteModal")) return;

                let data = {
                    type_name: $("#add_type_name").val(),
                    sub_admin_id: selectedSubAdminId,
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                    url: "{{ route('credit-notes-types.store') }}",
                    type: "POST",
                    data: data,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $("#addCreditNoteModal").modal("hide");
                        resetForm("#addCreditNoteModal");
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                        });
                        fetchCreditNotes(currentPage);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                $("#add_" + key).addClass("is-invalid");
                                $("#add_" + key).after(
                                    `<small class="text-danger error-text">${errors[key][0]}</small>`
                                    );
                            }
                        }
                    }
                });
            });

            $(document).on("click", ".edit-credit-note", function() {
                if (!canEditCreditNoteType) return;
                let id = $(this).data("id");
                $.ajax({
                    url: `/api/credit-notes-types/${id}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $("#edit_credit_note_id").val(response.data.id);
                        $("#edit_type_name").val(response.data.type_name);
                        $("#editCreditNoteModal").modal("show");
                    }
                });
            });

            $(".btn-submit-edit").on("click", function() {
                if (!canEditCreditNoteType) return;
                if (!validateForm("#editCreditNoteModal")) return;

                let id = $("#edit_credit_note_id").val();
                let data = {
                    type_name: $("#edit_type_name").val(),
                    _token: "{{ csrf_token() }}"
                };

                $.ajax({
                    url: `/api/credit-notes-types/update/${id}`,
                    type: "POST",
                    data: data,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $("#editCreditNoteModal").modal("hide");
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                        });
                        fetchCreditNotes(currentPage);
                    }
                });
            });

            $(document).on("click", ".delete-credit-note", function() {
                if (!canDeleteCreditNoteType) return;
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
                            url: `/api/credit-notes-types/delete/${id}`,
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}"
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(response) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                });
                                fetchCreditNotes(currentPage);
                            }
                        });
                    }
                });
            });

            function resetForm(modalId) {
                $(modalId).find("input").val("");
                clearErrors();
            }

            $("#addCreditNoteModal, #editCreditNoteModal").on("hidden.bs.modal", function() {
                resetForm("#" + this.id);
            });
        });
    </script>
@endpush
