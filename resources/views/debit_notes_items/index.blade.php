@extends('layout.app')

@section('title', 'Debit Note Items List')

@push('css')
<style>
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

    .table tbody tr td {
        /* white-space: normal !important; */
        word-break: break-word;
        word-wrap: break-word;
    }

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

    .pagination .page-item.active .page-link {
        background-color: #ff9f43 !important;
        border-color: #ff9f43 !important;
        color: #fff !important;
    }

    .pagination .page-link {
        color: #1b2850;
    }

    /* Responsive Table Styling */
    @media screen and (max-width: 767px) {
        .table-responsive {
            overflow-x: hidden !important;
        }

        .datatable {
            width: 100% !important;
            table-layout: fixed;
        }

        .datatable thead th:nth-child(n+2):not(.details-column),
        .datatable tbody td:nth-child(n+2):not(.details-control) {
            display: none !important;
        }

        .datatable thead th:first-child,
        .datatable tbody td:first-child {
            display: table-cell !important;
            width: calc(100% - 50px) !important;
            word-wrap: break-word;
        }

        .datatable thead th.details-column,
        .datatable tbody td.details-control {
            display: table-cell !important;
            text-align: center;
            vertical-align: middle;
            width: 50px !important;
        }
    }

    @media (min-width: 768px) {
        .datatable thead th.details-column,
        .datatable tbody td.details-control {
            display: none !important;
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
        font-size: 14px;
        gap:25px;
    }

    .detail-label {
        font-weight: 600;
        min-width: 120px;
        color: #495057;
    }

    .detail-value {
        color: #212529;
        flex: 1;
    }
    @media (max-width: 420px) {
    .page-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .page-btn a {
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
       height: 35px;
    }
}


</style>
@endpush

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Debit Note List</h4>
        </div>
        <div class="d-flex align-items-center flex-wrap">
            <div class="me-2 mb-sm-0">
                <div class="d-flex align-items-center" style="background-color: #f8f9fa; color: #ff9f43; font-weight: bold; padding: 7px 10px; border: 1px solid #d1d1d1; border-radius: 8px;">
                    <span style="color: #495057; margin-right: 8px; font-size: 14px; ">Total:</span>
                    <span style="font-size: 16px;">₹<span id="total_settlement_amount">0.00</span></span>
                </div>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(27, 'add'))
                    <a href="{{ route('debit-notes-items.create') }}" class="btn btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"
                            class="me-1">Add <span class="d-none d-sm-inline">Debit Note Item</span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-2">
                <div class="search-set">
                    <div class="search-input">
                        <a class="btn btn-searchset">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                        </a>
                        <input type="text" id="debit-note-search-input" class="form-control" placeholder="Search...">
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>Order / Invoice Number</th>
                            <th>Customer / Vendor Name</th>
                            <th>Type</th>
                            <th>Total Amount</th>
                            <th>Settlement Amount</th>
                            <th>Final Total</th>
                            <th class="text-end">Action</th>
                            <th class="details-column">Details</th>
                        </tr>
                    </thead>
                    <tbody id="debit_note_items_table_body">
                        {{-- Data will be loaded via AJAX --}}
                    </tbody>
                </table>
            </div>
            <div
                class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                    <select id="debit-note-per-page-select" class="form-select form-select-sm"
                        style="width: auto; border: 1px solid #ddd;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="ms-3" style="font-size: 14px; color: #555;">
                        <span id="debit-note-pagination-from">0</span> - <span id="debit-note-pagination-to">0</span> of
                        <span id="debit-note-pagination-total">0</span> items
                    </span>
                </div>
                <nav aria-label="Debit note pagination">
                    <ul class="pagination pagination-sm mb-0" id="debit-note-pagination-numbers"></ul>
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    var authToken = localStorage.getItem("authToken");
    var selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
    const canViewDebitNoteItem = @json(app('hasPermission')(27, 'view'));
    const canEditDebitNoteItem = @json(app('hasPermission')(27, 'edit'));
    const canDeleteDebitNoteItem = @json(app('hasPermission')(27, 'delete'));
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 10;
    let searchQuery = '';
    let currencySymbol = "{{ $currencySymbol ?? '₹' }}";
    let currencyPosition = "{{ $currencyPosition ?? 'left' }}";

    function formatCurrency(amount) {
        let formatted = parseFloat(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return currencyPosition === 'left' ? currencySymbol + formatted : formatted + currencySymbol;
    }

    function buildDebitNoteActionLinks(itemId) {
        let actions = '';

        if (canViewDebitNoteItem) {
            actions += `
                <a href="{{ url('debit-note-items/view') }}/${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/eye.svg') }}">
                </a>
            `;
        }

        if (canEditDebitNoteItem) {
            actions += `
                <a href="{{ url('debit-note-items/edit') }}/${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/edit.svg') }}">
                </a>
            `;
        }

        if (canDeleteDebitNoteItem) {
            actions += `
                <a class="confirm-text delete-item" data-id="${itemId}">
                    <img src="{{ env('ImagePath') . ('admin/assets/img/icons/delete.svg') }}">
                </a>
            `;
        }

        return actions || '<span class="text-muted">-</span>';
    }

    if ($.fn.DataTable.isDataTable('.datatable')) {
        $('.datatable').DataTable().destroy();
    }

    let table = $('.datatable').DataTable({
        bFilter: false,
        destroy: true,
        paging: false,
        info: false,
        searching: false,
        dom: 't',
        ordering: true,
        order: [],
        language: {
            emptyTable: "No debit note items found",
        },
        columnDefs: [
            { targets: 7, className: 'details-control', orderable: false }
        ]
    });

    $('#debit-note-search-input').on('keyup', function() {
        searchQuery = $(this).val();
        loadDebitNoteItems(1);
    });

    $('#debit-note-per-page-select').on('change', function() {
        perPage = $(this).val();
        loadDebitNoteItems(1);
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

    function loadDebitNoteItems(page = 1) {
        let url = "{{ url('api/debit-note-items') }}" + `?page=${page}&per_page=${perPage}`;
        if (selectedSubAdminId && selectedSubAdminId !== 'null') {
            url += `&selectedSubAdminId=${encodeURIComponent(selectedSubAdminId)}`;
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
            success: function (response) {
                if (response.status === 'success') {
                    table.clear();
                    currentPage = response.pagination?.current_page || 1;
                    lastPage = response.pagination?.last_page || 1;

                    response.data.forEach(function (item, index) {
                        let displayName = 'N/A';
                        let transactionNumber = 'N/A';

                        if (item.transaction_type === 'receipt') {
                            displayName = (item.order && item.order.user) ? item.order.user.name : 'N/A';
                            transactionNumber = item.order ? item.order.order_number : 'N/A';
                        } else {
                            displayName = (item.purchase_invoice && item.purchase_invoice.vendor)
                                ? item.purchase_invoice.vendor.name
                                : 'N/A';
                            transactionNumber = item.purchase_invoice ? item.purchase_invoice.invoice_number : 'N/A';
                        }

                        let typeName = item.credit_note_type
                            ? item.credit_note_type.type_name
                            : 'N/A';

                        let detailsId = `details-${index}`;

                        table.row.add([
                            `<div>
                                <span>${transactionNumber}</span>
                                <div class="collapse mt-2 d-lg-none" id="${detailsId}">
                                    <div class="collapse-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Name:</span>
                                            <span class="detail-value">${displayName}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Type:</span>
                                            <span class="detail-value">${typeName}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Total Amount:</span>
                                            <span class="detail-value">${formatCurrency(item.grand_total)}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Settlement Amount:</span>
                                            <span class="detail-value">${formatCurrency(item.settlement_amount)}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Final Total:</span>
                                            <span class="detail-value">${formatCurrency(item.total)}</span>
                                        </div>
                                        <div class="detail-item ">
                                            ${buildDebitNoteActionLinks(item.id)}
                                        </div>
                                    </div>
                                </div>
                            </div>`,
                            displayName,
                            typeName,
                            formatCurrency(item.grand_total),
                            formatCurrency(item.settlement_amount),
                            formatCurrency(item.total),
                            `<div class="">${buildDebitNoteActionLinks(item.id)}</div>`,
                            `<a href="#${detailsId}" class="toggle-details" data-bs-toggle="collapse">
                                <i class="fas fa-plus-circle"></i>
                            </a>`
                        ]);
                    });

                    updatePaginationUI(response.pagination || {
                        current_page: 1,
                        last_page: 1,
                        per_page: perPage,
                        total: response.data.length
                    });

                    $('#total_settlement_amount').text(parseFloat(response.total_settlement || 0).toFixed(2));
                    table.draw();
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    }

    function updatePaginationUI(pagination) {
        let from = (pagination.current_page - 1) * pagination.per_page + 1;
        let to = pagination.current_page * pagination.per_page;

        if (to > pagination.total) {
            to = pagination.total;
        }

        if (pagination.total === 0) {
            from = 0;
        }

        $('#debit-note-pagination-from').text(from);
        $('#debit-note-pagination-to').text(to);
        $('#debit-note-pagination-total').text(pagination.total);

        let paginationHtml = '';
        let startPage = Math.max(1, pagination.current_page - 2);
        let endPage = Math.min(pagination.last_page, startPage + 4);

        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationHtml += `
                <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                    <a class="page-link debit-note-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                </li>
            `;
        }

        $('#debit-note-pagination-numbers').html(paginationHtml);
        $('.pagination-controls').toggle(pagination.total > 0);
    }

    $(document).on('click', '.debit-note-page-link', function(e) {
        e.preventDefault();
        let page = $(this).data('page');
        if (page && page !== currentPage && page >= 1 && page <= lastPage) {
            loadDebitNoteItems(page);
        }
    });

    loadDebitNoteItems(currentPage);

    $(document).on('click', '.delete-item', function() {
        if (!canDeleteDebitNoteItem) return;

        let id = $(this).data('id');
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
                    url: `/api/debit-note-items/delete/${id}`,
                    type: "POST",
                    headers: { "Authorization": "Bearer " + authToken },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire("Deleted!", response.message, "success");
                            if (currentPage > 1 && table.rows().count() === 1) {
                                currentPage--;
                            }
                            loadDebitNoteItems(currentPage);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endpush
