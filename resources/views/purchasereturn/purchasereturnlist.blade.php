@extends('layout.app')

@section('title', 'Purchase Return List')

@section('content')
    <style>
        .items-modal-table {
            font-size: 13px;
        }

        .items-modal-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .view-items-btn {
            background: #ff9f43;
            color: white;
            border: none;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .view-items-btn:hover {
            background: #ff8c2e;
            transform: scale(1.05);
        }

        .modal-header .btn-close {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
        }

        .modal-header .btn-close:hover {
            background: #c82333;
        }

        /* Hide default DataTables search box completely */
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

        /* Action button styling */
        .icon-btn {
            background: #ff9f43;
            border-radius: 4px;
            padding: 4px 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .icon-btn:hover {
            background: #ff8c2e;
            transform: scale(1.05);
        }

        .icon-btn svg {
            width: 18px;
            height: 18px;
        }

        .icon-btn svg path {
            fill: white;
        }

        /* =============================================
                               MOBILE ORDER CARD STYLES
                            ============================================= */
        .mobile-order-card {
            display: none;
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

        .mobile-order-row {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 10px;
        }

        .mobile-order-cell {
            display: table-cell;
            vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }

        .mobile-order-number {
            font-size: 16px;
            color: #1b2850;
            width: 70%;
            padding: 15px;
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
            margin: 0 auto;
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

        #itemsModal .table-responsive {
            display: block !important;
        }

        /* =============================================
                               RESPONSIVE BREAKPOINTS
                            ============================================= */

        /* Below 768px: hide table, show mobile cards */
        @media screen and (max-width: 767.98px) {
            .table-responsive {
                display: none !important;
            }

            .mobile-order-card {
                display: block;
            }

            .pagination-controls {
                flex-wrap: wrap;
                gap: 10px;
            }

            .pagination .page-item .page-link {
                padding: 4px 10px;
                font-size: 12px;
            }
        }

        /* 768px and above: show table, hide mobile cards */
        @media screen and (min-width: 768px) {
            .mobile-order-card {
                display: none !important;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }
        }

        #itemsModal .modal-body {
            max-height: 60vh;
            overflow-y: auto;
        }

        /* Ensure table visible */
        #itemsModal .table-responsive {
            overflow-x: auto;
            overflow-y: visible !important;
        }

        /* Prevent collapse */
        #itemsModalBody tr td {
            white-space: normal !important;
            max-width: 100px;
        }

        /* Word wrapping for supplier name in desktop table */
        .table-responsive table td:nth-child(5),
        .table-responsive table th:nth-child(5) {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            max-width: 200px;
            min-width: 100px;
        }

        /* Ensure all other cells maintain normal behavior */
        .table-responsive table td {
            white-space: nowrap;
        }

        /* Only supplier column should wrap */
        .table-responsive table td:nth-child(5) {
            white-space: normal !important;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Purchase Return List</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(3, 'add'))
                <a href="{{ route('purchasereturn.add') }}" class="btn btn-added">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" alt="img" class="me-2">Add
                    Purchase Return
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- Search Section -->
                <div class="mb-3">
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

                <!-- ========================
                                         DESKTOP TABLE VIEW
                                    ========================= -->
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <th>#</th>
                            <th>Return No</th>
                            <th>Invoice Number</th>
                            <th>Date</th>
                            <th>vendor</th>
                            <th>Products</th>
                            <th>Return Qty</th>
                            <th>Subtotal</th>
                            <th>GST</th>
                            <th>Shipping</th>
                            <th>Total</th>
                            <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>

                <!-- ========================
                                         MOBILE CARD VIEW
                                    ========================= -->
                <div class="mobile-order-card mt-3" id="mobile-return-container">
                    <!-- JS will populate this -->
                </div>

                <!-- Pagination Controls (shared by both views) -->
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
                            <!-- Page numbers populated by JS -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Modal -->
    <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemsModalLabel">Return Items Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered items-modal-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Discount Amt</th>
                                    <th>Subtotal</th>
                                    <th>GST</th>
                                </tr>
                            </thead>
                            <tbody id="itemsModalBody">
                                <!-- Items populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            // Initialize DataTable
            const table = $('.datanew').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "dom": 't',
                "ordering": false
            });

            let currencySymbol = '₹';
            let currencyPosition = 'left';
            let allReturnData = [];

            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function formatCurrency(amount) {
                const number = Number(amount || 0);
                const formattedValue = number.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                return currencyPosition === 'left' ?
                    `${currencySymbol}${formattedValue}` :
                    `${formattedValue}${currencySymbol}`;
            }

            function renderEmpty(message) {
                table.clear().draw();
                $(".datanew tbody").html(`<tr><td colspan="12" class="text-center">${message}</td></tr>`);
                $('#mobile-return-container').html(`<div class="text-center p-4">${message}</div>`);
                $('.pagination-controls').hide();
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // =============================================
            // Show Items Modal (global)
            // =============================================
            // =============================================
            // Show Items Modal (global) - UPDATED for better GST display
            // =============================================
            window.showItemsModal = function(returnId) {
                const returnData = allReturnData.find(item => item.id === returnId);

                if (!returnData || !returnData.items || returnData.items.length === 0) {
                    Swal.fire({
                        title: 'No Items',
                        text: 'No items found for this purchase return.',
                        icon: 'info',
                        confirmButtonColor: '#ff9f43'
                    });
                    return;
                }

                let itemsHtml = '';
                returnData.items.forEach((item, index) => {
                    let gstText = 'N/A';

                    // Handle different GST formats
                    if (item.gst_details === 'inclusive') {
                        gstText = '<span class="badge bg-info">Inclusive GST</span>';
                    } else if (Array.isArray(item.gst_details) && item.gst_details.length > 0) {
                        // Format multiple GST entries
                        const gstLines = item.gst_details.map(tax => {
                            const taxName = tax.tax_name || tax.name || 'GST';
                            const taxRate = tax.tax_rate || tax.rate || 0;
                            const taxAmount = tax.tax_amount || tax.amount || 0;

                            return `${taxName}: ${taxRate}% (${formatCurrency(parseFloat(taxAmount))})`;
                        }).join('<br>');
                        gstText = gstLines;
                    } else if (item.gst_total > 0) {
                        // If only total GST is available
                        gstText = `Total GST: ${formatCurrency(item.gst_total)}`;
                    }

                    // Calculate if GST is included in price
                    const priceWithoutGst = item.gst_total > 0 ? item.price - (item.gst_total / item
                        .quantity) : item.price;

                    itemsHtml += `
            <tr>
                <td>${index + 1}</td>
                <td style="word-wrap: break-word; min-width: 150px;">${escapeHtml(item.product_name)}</td>
                <td class="">${item.quantity}</td>
                <td class="">${formatCurrency(item.price)}</td>
                <td class="">${formatCurrency(item.discount_amount)} (${item.discount}%)</td>
                <td class="">${formatCurrency(item.subtotal)}</td>
                <td style="min-width: 150px;">${gstText}</td>
            </tr>
        `;
                });

                // Add a summary row for total GST if needed
                // const totalGst = returnData.items.reduce((sum, item) => sum + (item.gst_total || 0), 0);
                // if (totalGst > 0) {
                //     itemsHtml += `
            //         <tr style="background-color: #f8f9fa; font-weight: bold;">
            //             <td colspan="5" class="text-end"><strong>Total GST Amount:</strong></td>
            //             <td><strong>${formatCurrency(totalGst)}</strong></td>
            //         </tr>
            //     `;
                // }

                $('#itemsModalBody').html(itemsHtml);

                setTimeout(() => {
                    const modal = new bootstrap.Modal(
                        document.getElementById('itemsModal')
                    );
                    modal.show();
                }, 50);
            }

            if (!authToken) {
                renderEmpty('Authentication token not found. Please login again.');
                return;
            }

            // =============================================
            // Toggle Mobile Card Details
            // =============================================
            window.toggleMobileReturnDetails = function(returnId) {
                const details = $(`#mobile-return-details-${returnId}`);
                const btn = $(`.mobile-toggle-btn[data-return-id="${returnId}"]`);
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
            };

            // =============================================
            // Render Mobile Return Cards
            // =============================================
            function renderMobileReturnCards(data) {
                const container = $('#mobile-return-container');
                container.html('');

                if (!data || data.length === 0) {
                    container.html('<div class="text-center p-4">No purchase return records found.</div>');
                    return;
                }

                // Header row
                const headerHtml = `
                    <div class="mobile-order-header-row">
                        <div class="mobile-order-header-cell">Return No</div>
                        <div class="mobile-order-header-cell">Details</div>
                    </div>
                `;
                container.append(headerHtml);

                data.forEach((item, index) => {
                    const serialNumber = ((currentPage - 1) * perPage) + (index + 1);
                    const discount = Number(item.discount || 0).toFixed(2);
                    const discountAmount = formatCurrency(item.discount_amount);
                    const purchaseLink = item.purchase_order_number ?
                        `<a href="/print-purchase/${item.purchase_id}" style="color:#ff9f43;font-weight:600;">${escapeHtml(item.purchase_order_number)}</a>` :
                        '-';

                    const cardHtml = `
                        <div class="mobile-order-item" data-return-id="${item.id}">

                            <!-- Always visible: Return No + toggle button -->
                            <div class="mobile-order-row">
                                <div class="mobile-order-cell mobile-order-number">
                                    ${escapeHtml(item.return_number) || '-'}
                                </div>
                                <div class="mobile-order-cell mobile-order-details-cell">
                                    <button class="mobile-toggle-btn"
                                        onclick="toggleMobileReturnDetails('${item.id}')"
                                        data-return-id="${item.id}">
                                        <span class="toggle-icon">+</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable details (hidden by default) -->
                            <div class="mobile-order-details" id="mobile-return-details-${item.id}">

                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Invoice Number:</span>
                                    <span class="mobile-detail-value">${purchaseLink}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Date:</span>
                                    <span class="mobile-detail-value">${escapeHtml(item.date) || '-'}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Vendor:</span>
                                    <span class="mobile-detail-value">${escapeHtml(item.supplier) || '-'}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Products:</span>
                                    <span class="mobile-detail-value">${Number(item.items_count || 0)}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Return Qty:</span>
                                    <span class="mobile-detail-value">${Number(item.return_qty || 0)}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Subtotal:</span>
                                    <span class="mobile-detail-value">${formatCurrency(item.subtotal)}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Tax:</span>
                                    <span class="mobile-detail-value">${formatCurrency(item.tax_amount)}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Shipping:</span>
                                    <span class="mobile-detail-value">${formatCurrency(item.shipping)}</span>
                                </div>
                                <div class="mobile-detail-row">
                                    <span class="mobile-detail-label">Total:</span>
                                    <span class="mobile-detail-value" style="color:#28a745;font-weight:bold;">
                                        ${formatCurrency(item.total_amount)}
                                    </span>
                                </div>

                                <!-- Action button -->
                                <div class="mobile-action-buttons">
                                    <button class="btn btn-sm btn-dark"
                                        onclick="window.showItemsModal(${item.id})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="white"/>
                                            <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="white"/>
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    `;
                    container.append(cardHtml);
                });
            }

            // =============================================
            // Fetch Purchase Returns
            // =============================================
            function fetchPurchaseReturns(page = 1) {
                let url = `/api/purchase-return-list?page=${page}&per_page=${perPage}`;

                if (selectedSubAdminId && selectedSubAdminId !== "null" && selectedSubAdminId !== "undefined") {
                    url += `&selectedSubAdminId=${encodeURIComponent(selectedSubAdminId)}`;
                }

                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (!response.status) {
                            renderEmpty(response.message || "No purchase return records found.");
                            $('.pagination-controls').hide();
                            return;
                        }

                        currencySymbol = response.currency_symbol || currencySymbol;
                        currencyPosition = response.currency_position || currencyPosition;
                        allReturnData = response.data || [];

                        const pagination = response.pagination;
                        if (pagination) {
                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;
                            updatePaginationUI(pagination);
                        }

                        // Desktop table rows
                        const rows = allReturnData.map((item, index) => {
                            const discount = Number(item.discount || 0).toFixed(2);
                            const discountAmount = formatCurrency(item.discount_amount);
                            const serialNumber = ((currentPage - 1) * perPage) + (index + 1);

                            return [
                                serialNumber,
                                item.return_number || "-",
                                item.purchase_order_number ?
                                `<a href="/print-purchase/${item.purchase_id}" class="order-link">${item.purchase_order_number}</a>` :
                                "-",
                                item.date || "-",
                                item.supplier || "-",
                                `<span>${Number(item.items_count || 0)}</span>`,
                                `<span>${Number(item.return_qty || 0)}</span>`,
                                formatCurrency(item.subtotal),
                                formatCurrency(item.tax_amount),
                                formatCurrency(item.shipping),
                                `<strong class="text-success">${formatCurrency(item.total_amount)}</strong>`,
                                `<a href="javascript:void(0);" onclick="window.showItemsModal(${item.id})" title="View Return Items">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
                                        <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
                                    </svg>
                                </a>`
                            ];
                        });

                        table.clear();
                        if (rows.length > 0) {
                            table.rows.add(rows).draw();
                            $('.pagination-controls').show();
                        } else {
                            table.draw();
                            renderEmpty("No purchase return records found.");
                            $('.pagination-controls').hide();
                        }

                        // Render mobile cards
                        renderMobileReturnCards(allReturnData);
                    },
                    error: function(xhr) {
                        console.error("Purchase return list API error:", xhr);
                        renderEmpty("Unable to load purchase return records.");
                        $('.pagination-controls').hide();
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

                if (pagination.current_page > 1) {
                    paginationHtml +=
                        `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page - 1}">«</a></li>`;
                }

                let startPage = Math.max(1, pagination.current_page - 2);
                let endPage = Math.min(pagination.last_page, startPage + 4);
                if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                if (pagination.current_page < pagination.last_page) {
                    paginationHtml +=
                        `<li class="page-item"><a class="page-link" href="javascript:void(0);" data-page="${pagination.current_page + 1}">»</a></li>`;
                }

                $('#pagination-numbers').html(paginationHtml);
            }

            // Search
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchPurchaseReturns(1);
            });

            // Pagination click
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchPurchaseReturns(page);
                }
            });

            // Per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchPurchaseReturns(1);
            });

            // Initial fetch
            fetchPurchaseReturns(currentPage);
        });
    </script>
@endpush
