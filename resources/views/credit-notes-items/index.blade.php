@extends('layout.app')

@section('title', 'Credit Note Items List')

@push('css')
    <style>
        #creditNoteItemsTable tbody tr td {
            /* white-space: normal !important; */
            word-break: break-word;
            word-wrap: break-word;
        }

        /* ✅ Hide default DataTables search/paging */
        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
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
            top: 7px !important;
        }

        /* Responsive Table Styling */
        @media screen and (max-width: 767px) {
            .table-responsive {
                overflow-x: auto !important;
            }

            #creditNoteItemsTable thead th:nth-child(n+3),
            #creditNoteItemsTable tbody td:nth-child(n+3) {
                display: none !important;
            }

            #creditNoteItemsTable thead th:first-child,
            #creditNoteItemsTable tbody td:first-child {
                display: table-cell !important;
                width: calc(100% - 60px) !important;
                word-wrap: break-word;
            }

            #creditNoteItemsTable thead th.details-column,
            #creditNoteItemsTable tbody td.details-control {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 60px !important;
            }
        }

        @media (min-width: 768px) {

            #creditNoteItemsTable thead th.details-column,
            #creditNoteItemsTable tbody td.details-control {
                display: none !important;
            }
        }

        .toggle-details i {
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
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

        .inventory-toggle-btn-table {
            background: transparent;
            border: none;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            padding: 0;
        }

        .credit-mobile {
            background-color: #f8f9fa;
            color: #ff9f43;
            font-weight: bold;
            padding: 7px 10px;
            border: 1px solid #d1d1d1;
            border-radius: 8px;
            /* margin-right: 5px; */
            /* margin-top: 8px; */
        }

        .inventory-details-row {
            display: none;
        }

        .inventory-details-row.show {
            display: table-row;
        }

        .inventory-details-content {
            padding: 15px;
            background: #f8f9fa;
            border-top: 2px solid #ff9f43;
        }

        .inventory-detail-row-simple {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .inventory-detail-label-simple {
            font-weight: bold;
            color: #333;
        }

        @media (max-width: 575px) {
            .page-header .page-btn {
                margin-top: 0px !important;
            }
        }

        @media (max-width: 420px) {
            .page-actions {
                display: flex !important;
                flex-direction: row !important;
                justify-content: end !important;
                align-items: center !important;
                /* width: 100%; */
            }

            .credit-mobile {
                background-color: #f8f9fa;
                color: #ff9f43;
                font-weight: bold;
                padding: 3px 10px;
                border: 1px solid #d1d1d1;
                border-radius: 8px;
                margin-right: 5px;
                margin-top: 8px;
            }

            .page-actions>div {
                margin-right: 0 !important;
            }

            .page-btn a {
                width: auto !important;
                padding: 8px 15px !important;
                height: auto !important;
                margin-bottom: 0 !important;
            }

            .page-header .page-title h4 {
                margin-bottom: 10px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Credit Note List</h4>
            </div>
            <div class="d-flex align-items-center flex-wrap page-actions">
                <div class="me-3 mb-2 mb-sm-0">
                    <div class="d-flex align-items-center credit-mobile">
                        <span style="color: #495057; margin-right: 8px; font-size: 14px; ">Total:</span>
                        <span style="font-size: 16px;">{{ $currencySymbol }}<span
                                id="total_settlement_amount">0.00</span></span>
                    </div>
                </div>
                <div class="page-btn">
                    @if (app('hasPermission')(27, 'add'))
                        <a href="{{ route('credit-notes-items.create') }}" class="btn btn-added">
                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img"
                                class="me-1">
                            Add <span class="d-none d-sm-inline">Credit Note Item</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-top mb-3">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
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

                    <table class="table" id="creditNoteItemsTable" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Order / Invoice Number</th>
                                <th class="details-column">Details</th>
                                <th>Customer Name / Vendor Name</th>
                                <th>Type</th>
                                <th>Total Amount</th>
                                {{-- <th>Paid Amount</th> --}}
                                {{-- <th>Remaining Amount</th> --}}
                                <th>Settlement Amount</th>
                                <!-- <th>Final Total</th> -->
                                {{-- <th>Reason</th> --}}
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Data will be loaded via AJAX --}}
                        </tbody>
                    </table>
                </div>

                <!-- ✅ Pagination Controls -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
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
        let table;
        const canViewCreditNoteItem = @json(app('hasPermission')(27, 'view'));
        const canEditCreditNoteItem = @json(app('hasPermission')(27, 'edit'));
        const canDeleteCreditNoteItem = @json(app('hasPermission')(27, 'delete'));

        function buildActionLinks(id) {
            let actions = '';

            if (canViewCreditNoteItem) {
                actions += `
                    <a href="/view-credit-note-items/${id}">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/eye.svg' }}">
                    </a>
                `;
            }

            if (canEditCreditNoteItem) {
                actions += `
                    <a href="/edit-credit-note-items/${id}">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}">
                    </a>
                `;
            }

            if (canDeleteCreditNoteItem) {
                actions += `
                    <a class="confirm-text" data-id="${id}" href="javascript:void(0);">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}">
                    </a>
                `;
            }

            return actions || '<span class="text-muted">-</span>';
        }

        $(document).ready(function() {
            let currencySymbol = "{{ $currencySymbol }}";
            let currencyPosition = "{{ $currencyPosition }}";
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Pagination & Search state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function formatCurrency(amount) {
                let formatted = parseFloat(amount).toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return currencyPosition === 'left' ?
                    currencySymbol + formatted :
                    formatted + currencySymbol;
            }

            // Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchCreditNoteItems(1);
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchCreditNoteItems(1);
            });

            fetchCreditNoteItems();

            function fetchCreditNoteItems(page = 1) {
                currentPage = page;
                let ajaxUrl = "/api/credit-note-items-api";
                let ajaxData = {
                    page: currentPage,
                    per_page: perPage,
                    search: searchQuery
                };
                if (selectedSubAdminId) {
                    ajaxData.selectedSubAdminId = selectedSubAdminId;
                }

                $.ajax({
                    url: ajaxUrl,
                    type: "GET",
                    data: ajaxData,
                    headers: {
                        "Authorization": "Bearer " + localStorage.getItem("authToken")
                    },
                    success: function(response) {
                        // Destroy DataTable before injecting new HTML
                        if ($.fn.DataTable.isDataTable('#creditNoteItemsTable')) {
                            $('#creditNoteItemsTable').DataTable().clear().destroy();
                        }

                        let tableBody = "";
                        if (response.data && response.data.length > 0) {
                            if (!window.creditNoteDataMap) window.creditNoteDataMap = {};

                            $.each(response.data, function(index, row) {
                                window.creditNoteDataMap[row.id] = row;

                                let transNumber = "N/A";
                                if (row.type_id == 2 || row.type_id == 'payment') {
                                    transNumber = row.purchase_invoice ? row.purchase_invoice.invoice_number : "N/A";
                                } else {
                                    transNumber = row.order ? row.order.order_number : "N/A";
                                }

                                let displayName = "N/A";
                                if (row.type_id == 2 || row.type_id == 'payment') {
                                    displayName = row.purchase_invoice && row.purchase_invoice.vendor ? row.purchase_invoice.vendor.name : "N/A";
                                } else {
                                    displayName = row.order && row.order.user ? row.order.user.name : "N/A";
                                }

                                let typeName = row.credit_note ? row.credit_note.type_name : "N/A";

                                tableBody += `
                                    <tr>
                                        <td>${transNumber}</td>
                                        <td class="details-control">
                                            <button class="inventory-toggle-btn-table" onclick="toggleCreditNoteRowDetails('${row.id}')" data-id="${row.id}">
                                                <span class="toggle-icon"><i class="fas fa-plus-circle" style="color: #ff9f43; font-size: 20px;"></i></span>
                                            </button>
                                        </td>
                                        <td>${displayName}</td>
                                        <td>${typeName}</td>
                                        <td>${formatCurrency(row.total_amt)}</td>
                                        <td>${formatCurrency(row.settlement_amount)}</td>
                                        <td>${buildActionLinks(row.id)}</td>
                                    </tr>
                                `;
                            });

                            $('#total_settlement_amount').text(response.total_settlement);
                        } else {
                            tableBody = '<tr><td colspan="7" class="text-center">No items found.</td></tr>';
                            $('#total_settlement_amount').text('0.00');
                        }

                        $('#creditNoteItemsTable tbody').html(tableBody);

                        // Update Pagination
                        renderPagination(response.pagination);

                        // Reinitialize DataTable
                        $('#creditNoteItemsTable').DataTable({
                            responsive: true,
                            paging: false,
                            ordering: false,
                            info: false,
                            searching: false,
                            language: {
                                emptyTable: "No items found."
                            }
                        });
                    }
                });
            }

            // function renderPagination(pagination) {
            //     lastPage = pagination.last_page;
            //     currentPage = pagination.current_page;
            //     let total = pagination.total;
            //     let perPage = pagination.per_page;

            //     let from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
            //     let to = Math.min(currentPage * perPage, total);

            //     $("#pagination-from").text(from);
            //     $("#pagination-to").text(to);
            //     $("#pagination-total").text(total);

            //     let paginationHtml = "";

            //     paginationHtml += `
            //         <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            //             <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentPage - 1})">Previous</a>
            //         </li>
            //     `;

            //     let startPage = Math.max(1, currentPage - 2);
            //     let endPage = Math.min(lastPage, startPage + 4);
            //     if (endPage - startPage < 4) {
            //         startPage = Math.max(1, endPage - 4);
            //     }

            //     for (let i = startPage; i <= endPage; i++) {
            //         paginationHtml += `
            //             <li class="page-item ${i === currentPage ? 'active' : ''}">
            //                 <a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>
            //             </li>
            //         `;
            //     }

            //     paginationHtml += `
            //         <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
            //             <a class="page-link" href="javascript:void(0);" onclick="changePage(${currentPage + 1})">Next</a>
            //         </li>
            //     `;

            //     $("#pagination-numbers").html(paginationHtml);
            // }

            function renderPagination(pagination) {
    lastPage = pagination.last_page;
    currentPage = pagination.current_page;
    let total = pagination.total;
    let perPage = pagination.per_page;

    let from = total > 0 ? (currentPage - 1) * perPage + 1 : 0;
    let to = Math.min(currentPage * perPage, total);

    $("#pagination-from").text(from);
    $("#pagination-to").text(to);
    $("#pagination-total").text(total);

    let paginationHtml = "";

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(lastPage, startPage + 4);

    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }

    for (let i = startPage; i <= endPage; i++) {
        paginationHtml += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="javascript:void(0);" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }

    $("#pagination-numbers").html(paginationHtml);
}
            window.changePage = function(page) {
                if (page < 1 || page > lastPage) return;
                fetchCreditNoteItems(page);
            };

            $(document).on('click', '.confirm-text', function() {
                if (!canDeleteCreditNoteItem) {
                    return;
                }

                let id = $(this).data('id');

                Swal.fire({
                    title: "Are you sure?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/credit-note-items-api/delete/${id}`,
                            type: "POST",
                            headers: {
                                "Authorization": "Bearer " + localStorage.getItem(
                                    "authToken")
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire("Deleted!", res.message, "success");
                                    fetchCreditNoteItems(currentPage);
                                }
                            }
                        });
                    }
                });
            });
        });

        window.toggleCreditNoteRowDetails = function(id) {
            const btn = $(`.inventory-toggle-btn-table[data-id="${id}"]`);
            const row = btn.closest('tr');
            const iconContainer = btn.find('.toggle-icon');

            let detailsRow = $(`#details-${id}`);

            if (detailsRow.length > 0) {
                detailsRow.remove();
                iconContainer.html('<i class="fas fa-plus-circle" style="color: #ff9f43; font-size: 20px;"></i>');
                btn.removeClass('minus');
            } else {
                const data = window.creditNoteDataMap[id];

                // Helper to format currency inside this function if needed
                const currencySymbol = "{{ $currencySymbol }}";
                const currencyPosition = "{{ $currencyPosition }}";

                function formatCurrencyLocal(amount) {
                    let formatted = parseFloat(amount).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    return currencyPosition === 'left' ?
                        currencySymbol + formatted :
                        formatted + currencySymbol;
                }

                const content = `
                    <tr class="inventory-details-row show" id="details-${id}">
                        <td colspan="10">
                            <div class="inventory-details-content">
                                <div class="inventory-detail-row-simple">
                                    <span class="inventory-detail-label-simple">${(data.type_id == 2 || data.type_id == 'payment') ? 'Invoice Number:' : 'Order Number:'}</span>
                                    <span class="inventory-detail-value-simple">${(data.type_id == 2 || data.type_id == 'payment') ? (data.purchase_invoice?.invoice_number || 'N/A') : (data.order?.order_number || 'N/A')}</span>
                                </div>
                                <div class="inventory-detail-row-simple">
                                    <span class="inventory-detail-label-simple">${(data.type_id == 2 || data.type_id == 'payment') ? 'Vendor Name:' : 'Hospital Name:'}</span>
                                    <span class="inventory-detail-value-simple">${(data.type_id == 2 || data.type_id == 'payment') ? (data.purchase_invoice?.vendor?.name || 'N/A') : (data.order?.user?.name || 'N/A')}</span>
                                </div>
                                <div class="inventory-detail-row-simple">
                                    <span class="inventory-detail-label-simple">Type:</span>
                                    <span class="inventory-detail-value-simple">${data.credit_note?.type_name || 'N/A'}</span>
                                </div>
                                <div class="inventory-detail-row-simple">
                                    <span class="inventory-detail-label-simple">Total Amount:</span>
                                    <span class="inventory-detail-value-simple">${formatCurrencyLocal(data.total_amt)}</span>
                                </div>
                                <div class="inventory-detail-row-simple">
                                    <span class="inventory-detail-label-simple">Settlement Amount:</span>
                                    <span class="inventory-detail-value-simple">${formatCurrencyLocal(data.settlement_amount)}</span>
                                </div>

                                <div class="inventory-detail-row-simple" style="border-bottom: none; padding-top: 15px;">
                                    <span class="inventory-detail-label-simple">Actions:</span>
                                    <div class="inventory-detail-value-simple">${buildActionLinks(id)}</div>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                row.after(content);
                iconContainer.html('<i class="fas fa-minus-circle" style="color: red; font-size: 20px;"></i>');
                btn.addClass('minus');
            }
        };
    </script>
@endpush
