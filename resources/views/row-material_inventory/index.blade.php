@extends('layout.app')

@section('title', 'Row Material Inventory List')

@section('content')
    <style>
        .inventory-tabs {
            display: flex;
            gap: 12px;
            margin: 12px 0 20px;
            flex-wrap: wrap;
        }

        .inventory-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            border-radius: 10px;
            border: 1px solid #d9dce3;
            background: #f7f8fa;
            color: #212b36;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .inventory-tab:hover {
            color: #ff9f43;
            border-color: #ff9f43;
        }

        .inventory-tab.active {
            background: #ff9f43;
            border-color: #ff9f43;
            color: #fff;
        }

        #inventory-table thead th:nth-child(1),
        #inventory-table tbody td:nth-child(1),
        #inventory-table thead th:nth-child(8),
        #inventory-table tbody td:nth-child(8) {
            text-align: center;
            vertical-align: middle;
        }

        .inventory-table-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table tbody tr td {
            white-space: normal !important;
        }

        .sorting_1 {
            /* display: flex !important; */
            /* align-items: center !important;
                                            gap: 5px !important; */
        }

        .table-scroll-top {
            overflow-x: auto;
            overflow-y: hidden;
            height: 20px;
            width: 100%;
            margin-bottom: 5px;
            display: none;
        }

        .table-scroll-top div {
            height: 1px;
        }


        /* Product Name column word wrap only */
        #inventory-table tbody td:nth-child(1),
        #inventory-table thead th:nth-child(1) {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: break-word;
            max-width: 180px;
        }

        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            /* Filter Section Mobile Styles */
            /* .card-body > .d-flex { */
            /* flex-direction: column !important; */
            /* gap: 10px !important; */
            /* } */

            .search-set {
                width: 100% !important;
                margin: 0 0 10px 0 !important;
            }

            .search-set.d-flex.mt-2 {
                width: 100% !important;
                margin: 0 0 15px 0 !important;
            }

            .search-set.d-flex.mt-2>.d-flex {
                flex-direction: column !important;
                width: 100% !important;
                gap: 10px !important;
            }

            .search-set.d-flex.mt-2>.d-flex>div {
                width: 100% !important;
            }

            .search-set.d-flex.mt-2 label {
                font-size: 13px;
                margin-bottom: 5px;
            }

            .search-set.d-flex.mt-2 input[type="date"] {
                width: 100% !important;
                font-size: 14px;
                padding: 8px;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table-scroll-top {
                display: block;
            }

            .table {
                font-size: 11px;
            }

            .table th,
            .table td {
                padding: 6px 3px;
            }

            /* Show only Sr No, Material Name and Details */
            .table thead th:nth-child(4),
            .table tbody td:nth-child(4),
            .table thead th:nth-child(5),
            .table tbody td:nth-child(5),
            .table thead th:nth-child(6),
            .table tbody td:nth-child(6),
            .table thead th:nth-child(7),
            .table tbody td:nth-child(7),
            .table thead th:nth-child(8),
            .table tbody td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .table thead th:nth-child(3),
            .table tbody td:nth-child(3) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .inventory-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }

            select#filterCategory,
            select#filterBrand {
                font-size: 12px !important;
            }

            .form-control-sm {
                min-height: calc(1.5em + .5rem + 2px);
                padding: .25rem .5rem;
                font-size: 13px !important;
                border-radius: .2rem;
            }

            div#DataTables_Table_0_filter {
                margin-top: 10px !important;
            }

            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: .1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {

            /* Filter Section Small Mobile Styles */
            .card-body>.d-flex {
                flex-wrap: wrap !important;
                gap: 10px !important;
            }

            .search-set.d-flex.mt-2>.d-flex {
                flex-direction: row !important;
                gap: 10px !important;
            }

            .search-set.d-flex.mt-2>.d-flex>div {
                flex: 1;
                min-width: 0;
            }

            .search-set.d-flex.mt-2 input[type="date"] {
                width: 100% !important;
                font-size: 14px;
            }

            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .table-scroll-top {
                display: block;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px 4px;
            }

            /* Show Sr No, Material Name, Details, SKU, Price */
            .table thead th:nth-child(6),
            .table tbody td:nth-child(6),
            .table thead th:nth-child(7),
            .table tbody td:nth-child(7),
            .table thead th:nth-child(8),
            .table tbody td:nth-child(8) {
                display: none;
            }

            /* Center Details column */
            .table thead th:nth-child(3),
            .table tbody td:nth-child(3) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .inventory-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .table-responsive {
                display: block !important;
                overflow-x: auto;
            }

            .table-scroll-top {
                display: block;
            }

            .table {
                font-size: 13px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
            }

            /* Hide Details and Action columns on tablet and above */
            .table thead th:nth-child(3),
            .table tbody td:nth-child(3),
            .table thead th:nth-child(8),
            .table tbody td:nth-child(8) {
                display: none;
            }

            /* Disable expandable rows on tablets */
            .inventory-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            .table-responsive {
                display: block !important;
            }

            .table {
                font-size: 14px;
            }

            .table th,
            .table td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop */
            .table thead th:nth-child(3),
            .table tbody td:nth-child(3) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .inventory-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .inventory-details-row {
            display: none;
        }

        .inventory-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .inventory-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .inventory-details-list {
            margin-bottom: 15px;
        }

        .inventory-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .inventory-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .inventory-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .inventory-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .inventory-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        /* Toggle button styles */
        .inventory-toggle-btn-table {
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

        .inventory-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .inventory-toggle-btn-table.minus {
            background: #dc3545;
        }

        .inventory-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        .modal-content {
            width: 80% !important;
        }

        /* Custom Pagination Styling */
        .pagination {
            gap: 8px;
        }

        .pagination .page-item {
            margin: 0;
        }

        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            /* Dark gray for other pages */
            color: #fff;
            border: none;
            margin: 0;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #d7dce1;
            color: #7a8794;
            cursor: not-allowed;
            pointer-events: none;
        }

        .pagination .page-item.ellipsis .page-link {
            background-color: transparent;
            color: #5d6d7e;
            pointer-events: none;
            padding: 6px 4px;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            /* Orange for active page */
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
            top: 18px !important;
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

        /* Remove unwanted search input alignment space */
        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
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

    <!-- Quantity History Modal (unchanged) -->
    <div class="modal fade" id="quantityHistoryModal" tabindex="-1" aria-labelledby="quantityHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityHistoryLabel">Quantity History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quantityHistoryContent"></div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Row Material Inventory</h4>
                <div class="inventory-tabs">
                    <a href="{{ route('inventory.list') }}" class="inventory-tab">Stock Inventory</a>
                    <a href="{{ route('row_material.inventory') }}" class="inventory-tab active">Material Inventory</a>
                </div>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(1, 'add'))
                <a href="{{ route('row_material.add') }}" class="btn btn-sm btn-added">
                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/plus.svg' }}" alt="img" class="me-1">New
                    Row Material
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <!-- SINGLE ROW FILTER SECTION -->
                <div class="row g-2 align-items-end mb-3">
                    <!-- Search (left) -->
                    <div class="col-md-3 col-12">
                        <div class="search-input position-relative">
                            <a class="btn btn-searchset position-absolute"
                                style="left: 10px; top: 50%; transform: translateY(-50%); z-index: 10;">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img">
                            </a>
                            <input type="text" id="inventory-search-input" class="form-control form-control-sm"
                                placeholder="Search..." style="padding-left: 35px;">
                        </div>
                    </div>

                    <!-- Right-aligned date filters -->
                    <div class="col-md-auto ms-auto">
                        <div class="d-flex  row ">
                            <div class="col-6">
                                <label class="form-label mb-1">Start Date</label>
                                <input type="date" id="startDate" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">End Date</label>
                                <input type="date" id="endDate" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>


                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="table" id="inventory-table">
                        <thead>
                            <tr>
                                <th class="text-center">Sr No</th>
                                <th>Material Name</th>
                                <th class="text-center">Details</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Initial Stock</th>
                                <th>Current Stock</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <!-- PAGINATION CONTROLS -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"
                    style="display: none;">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select-bottom" class="form-select form-select-sm"
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
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal (unchanged) -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Row Material</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">x</button>
                </div>
                <div class="modal-body">
                    <form id="editProductForm">
                        <input type="hidden" name="id" id="editProductId">
                        <div class="mb-3">
                            <label class="form-label">Material Name</label>
                            <input type="text" class="form-control" name="name" id="editProductName" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">SKU</label>
                            <input type="text" class="form-control" name="sku" id="editProductSku" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="editProductPrice" readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Stock</label>
                                <input type="number" class="form-control" name="initial_stock"
                                    id="editProductinitialStock" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Current Stock</label>
                                <input type="number" class="form-control" name="current_stock" id="editProductStock"
                                    readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock Update</label>
                            <div>
                                <input type="radio" name="stock_action" id="addStock" value="add">
                                <label for="addStock">Add Quantity</label>
                                <input type="radio" name="stock_action" id="minusStock" value="minus"
                                    class="ms-3">
                                <label for="minusStock">Minus Quantity</label>
                            </div>
                            <div id="stockError" class="text-danger mb-2" style="display:none;"></div>
                        </div>
                        <div class="mb-3 d-none" id="stockQuantityBox">
                            <label class="form-label" id="stockQuantityLabel">Quantity</label>
                            <input type="number" class="form-control" name="stock_quantity" id="stockQuantity">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary me-2">Save Changes</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    {{-- <script>
        // Global variables
        var inventoryTable;

        function formatInventoryPrice(value) {
            if (value === null || value === undefined || value === '' || value === 'N/A') {
                return 'N/A';
            }

            const numericValue = Number(value);
            if (Number.isNaN(numericValue)) {
                return value;
            }

            return numericValue.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Helper function to build expandable row content
        function buildInventoryExpandableRowContent(product) {
            let actionBtns = `
                <a class="btn btn-sm btn-primary me-2" href="/material-inventory-view/${product.id}" style="color: white;font-size: 13px;">
                    View History
                </a>
                <a class="btn btn-sm btn-primary edit-product" data-id="${product.id}" href="javascript:void(0);" style="color: white;font-size: 13px;">
                    Add / Edit Stock
                </a>
            `;

            return `
                <td colspan="7" class="inventory-details-content">
                    <div class="inventory-details-list">
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">SKU:</span>
                            <span class="inventory-detail-value-simple">${product.sku || 'N/A'}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Price:</span>
                            <span class="inventory-detail-value-simple">${formatInventoryPrice(product.price)}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Initial Stock:</span>
                            <span class="inventory-detail-value-simple">${product.initial_stock ?? 'N/A'}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Current Stock:</span>
                            <span class="inventory-detail-value-simple">${product.current_stock ?? 'N/A'}</span>
                        </div>
                    </div>
                    <div class="inventory-action-buttons-simple">
                        ${actionBtns}
                    </div>
                </td>
            `;
        }

        // Toggle function for table rows - must be global
        window.toggleInventoryRowDetails = function(productId) {
            const btn = $(`.inventory-toggle-btn-table[data-product-id="${productId}"]`);
            if (btn.length === 0) {
                // console.error('Toggle button not found for product:', productId);
                return;
            }

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.inventory-details-row[data-product-id="${productId}"]`);
            const icon = btn.find('.toggle-icon');

            // If expandable row doesn't exist, create it
            if (detailsRow.length === 0) {
                const productData = window.inventoryDataMap && window.inventoryDataMap[productId];
                if (productData) {
                    detailsRow = $('<tr>')
                        .addClass('inventory-details-row')
                        .attr('data-product-id', productId)
                        .html(buildInventoryExpandableRowContent(productData));
                    row.after(detailsRow);
                } else {
                    // console.error('Product data not found for product:', productId);
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
        window.addInventoryExpandableRows = function(dt) {
            if (!dt) return;

            const currentWidth = $(window).width();
            const isMobileOrTablet = currentWidth <= 1024;

            if (!isMobileOrTablet) {
                // Remove expandable rows on desktop
                $('tr.inventory-details-row').remove();
                return;
            }

            dt.rows().every(function() {
                const row = this.node();
                const toggleBtn = $(row).find('.inventory-toggle-btn-table');
                if (toggleBtn.length > 0) {
                    const productId = toggleBtn.data('product-id');
                    const productData = window.inventoryDataMap && window.inventoryDataMap[productId];
                    if (productData && !$(row).next('tr.inventory-details-row[data-product-id="' + productId +
                            '"]').length) {
                        const expandableRow = $('<tr>')
                            .addClass('inventory-details-row')
                            .attr('data-product-id', productId)
                            .html(buildInventoryExpandableRowContent(productData));
                        $(row).after(expandableRow);
                    }
                }
            });
        };

        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            inventoryTable = null;

            // Listen for changes on startDate and endDate
            $(document).on("change", "#startDate, #endDate", function() {
                let startDate = $("#startDate").val();
                let endDate = $("#endDate").val();
                var subBranchId1 = localStorage.getItem('selectedSubAdminId');

                // Run only if both dates are selected
                if (!startDate || !endDate) {
                    return;
                }

                $.ajax({
                    url: "/api/products/filter",
                    type: "GET",
                    data: {
                        start_date: startDate,
                        end_date: endDate,
                        sub_branch_id: subBranchId1
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        // console.log(response);

                        if (response.inventory && response.inventory.length > 0) {
                            let tableBody = [];

                            // Store product data for expandable rows
                            if (!window.inventoryDataMap) {
                                window.inventoryDataMap = {};
                            }

                            response.inventory.forEach((p, index) => {
                                let productName = p.product_name || 'N/A';
                                let sku = p.sku || 'N/A';
                                let initialStock = p.initial_stock ?? 'N/A';
                                let currentStock = p.current_stock ?? 'N/A';
                                let rawPrice = p.price;
                                let price = formatInventoryPrice(rawPrice);

                                // Store product data for expandable row
                                const productData = {
                                    ...p,
                                    sku: sku,
                                    price: rawPrice,
                                    initial_stock: initialStock,
                                    current_stock: currentStock
                                };
                                window.inventoryDataMap[p.id] = productData;

                                let actionBtns = `
                            <div class="inventory-table-actions">
                                <a class="btn btn-primary" href="/material-inventory-view/${p.id}" style="color: white;font-size: 13px;">
                                    View History
                                </a>
                                <a class="edit-product btn btn-primary" data-id="${p.id}" href="javascript:void(0);" style="color: white;font-size: 13px;">
                                    Add / Edit Stock
                                </a>
                            </div>
                        `;

                                // Toggle button for Details column
                                let detailsColumn = `
                            <button class="inventory-toggle-btn-table" onclick="toggleInventoryRowDetails('${p.id}')" data-product-id="${p.id}">
                                <span class="toggle-icon">+</span>
                            </button>
                        `;

                                tableBody.push([
                                    index + 1,
                                    productName, // 0 Product Name
                                    detailsColumn, // 1 Details (toggle button)
                                    sku, // 2
                                    price, // 3
                                    initialStock, // 4
                                    currentStock, // 5
                                    actionBtns // 6
                                ]);

                            });

                            if (inventoryTable) {
                                inventoryTable.clear().rows.add(tableBody).draw();
                                inventoryTable.off('draw').on('draw', function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(
                                            inventoryTable);
                                    }
                                });
                                setTimeout(function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(
                                            inventoryTable);
                                    }
                                }, 100);
                            } else {
                                table.clear().rows.add(tableBody).draw();
                            }
                        } else {
                            table.clear().draw();
                        }
                    },
                    error: function(xhr) {
                        // console.error(xhr.responseText);
                    }
                });
            });


            // Capitalize helper
            function capitalizeWords(str) {
                if (!str || str.trim() === '') return 'N/A';
                return str.replace(/\b\w/g, function(char) {
                    return char.toUpperCase();
                });
            }
            const stockBox = $("#stockQuantityBox");
            const stockLabel = $("#stockQuantityLabel");

            $("#addStock").on("change", function() {
                stockBox.removeClass("d-none");
                stockLabel.text("How much to Add?");
            });

            $("#minusStock").on("change", function() {
                stockBox.removeClass("d-none");
                stockLabel.text("How much to Minus?");
            });

            function fetchinventory() {
                var categoryId = $('#filterCategory').val();
                var brandId = $('#filterBrand').val();
                var subBranchId = localStorage.getItem('selectedSubAdminId');

                $.ajax({
                    url: "/api/row-material-inventory-list",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        category_id: categoryId,
                        brand_id: brandId,
                        sub_branch_id: subBranchId
                    },
                    success: function(response) {


                        if (response.status) {
                            let products = response.inventory || [];
                            let lowStockThreshold = response.lowStockThreshold || 0;
                            let tableBody = [];

                            // Store product data for expandable rows
                            if (!window.inventoryDataMap) {
                                window.inventoryDataMap = {};
                            }

                            $.each(products, function(index, product) {
                                let productName = capitalizeWords(product.product_name ||
                                    'N/A');
                                let sku = product.sku || 'N/A';
                                let rawPrice = product.price;
                                let price = formatInventoryPrice(rawPrice);
                                let initialStock = product.initial_stock ?? 'N/A';
                                let currentStock = product.current_stock ?? 'N/A';
                                let id = product.id;

                                // Store product data for expandable row
                                const productData = {
                                    ...product,
                                    sku: sku,
                                    price: rawPrice,
                                    initial_stock: initialStock,
                                    current_stock: currentStock
                                };
                                window.inventoryDataMap[id] = productData;

                                // Low stock badge
                                let stockDisplay =
                                    `<span class="quantity-history" data-id="${id}" style="cursor:pointer;">${currentStock}</span>`;
                                if (currentStock !== 'N/A' && parseInt(currentStock) <= response
                                    .lowStockThreshold) {
                                    stockDisplay +=
                                        ` <span class="badge bg-danger ms-1">Low Stock</span>`;
                                }

                                // Action buttons
                                let actionBtns = `
        <div class="inventory-table-actions">
            <a class="btn btn-primary" href="/material-inventory-view/${id}" style="color: white;font-size: 13px;">View History</a>
            <a class="edit-product btn btn-primary" data-id="${id}" href="javascript:void(0);" style="color: white;font-size: 13px;">Add / Edit Stock</a>
        </div>
                    `;

                                // Toggle button for Details column
                                let detailsColumn = `
        <button class="inventory-toggle-btn-table" onclick="toggleInventoryRowDetails('${id}')" data-product-id="${id}">
            <span class="toggle-icon">+</span>
        </button>
                         `;

                                // ✅ Match thead columns exactly (7 columns)
                                tableBody.push([
                                    index + 1,
                                    productName, // Product Name
                                    detailsColumn, // Details (toggle button)
                                    sku,
                                    price,
                                    initialStock,
                                    stockDisplay,
                                    actionBtns
                                ]);
                            });

                            // Populate DataTable
                            if (inventoryTable) {
                                inventoryTable.clear().rows.add(tableBody).draw();
                                inventoryTable.off('draw').on('draw', function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(inventoryTable);
                                    }
                                });
                                setTimeout(function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(inventoryTable);
                                    }
                                }, 100);
                            } else {
                                table.clear().rows.add(tableBody).draw();
                            }
                        } else {
                            table.clear().draw();
                            $(".table tbody").html('<tr><td colspan="6">No inventory found</td></tr>');
                        }
                    },
                    error: function() {
                        alert("Error fetching inventory.");
                    }
                });
            }

            // ✅ Initialize DataTable
            inventoryTable = $('#inventory-table').DataTable({
                responsive: true,
                searching: true,
                paging: true,
                info: true,
                autoWidth: false,
                ordering: false,
                 dom: '<"top"f>rt<"bottom"lip><"clear">'
            });

            // Set table reference for date filter
            table = inventoryTable;


            // ✅ Load products on page load
            fetchinventory();

            // ✅ Reload when filters change
            $('#filterCategory, #filterBrand').on('change', function() {
                fetchinventory();
            });
            // ✅ Open Edit Modal
            $(document).on("click", ".edit-product", function() {
                var authToken = localStorage.getItem("authToken");
                let productId = $(this).data("id");

                // alert(productId)
                // Fetch product details from API
                $.ajax({
                    url: "/api/row-materials_edit_inventory/" + productId,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let product = response.product;
                            // console.log(product);

                            // Fill modal fields
                            $("#editProductId").val(product.id);
                            $("#editProductName").val(product.product_name);
                            $("#editProductSku").val(product.sku);
                            $("#editProductPrice").val(product.price);
                            $("#editProductinitialStock").val(product.initial_stock);
                            $("#editProductStock").val(product.current_stock);

                            // Show modal
                            $("#editProductModal").modal("show");
                        } else {
                            alert("Row Material not found!");
                        }
                    }
                });
            });

            // ✅ Handle Save Changes
            // $("#editProductForm").on("submit", function(e) {
            //     e.preventDefault();

            //     let productId = $("#editProductId").val();
            //     const sub_branch_id = localStorage.getItem('selectedSubAdminId');
            //     var authToken = localStorage.getItem("authToken");
            //     let stockAction = $("input[name='stock_action']:checked").val(); // 'add' or 'minus'
            //     let stockQuantity = $("#stockQuantity").val(); // the textbox value
            //     let formData = {
            //         name: $("#editProductName").val(),
            //         sku: $("#editProductSku").val(),
            //         price: $("#editProductPrice").val(),
            //         current_stock: $("#editProductStock").val(),
            //         initial_stock: $("#editProductinitialStock").val(),
            //         sub_branch_id: sub_branch_id,
            //         stock_action: stockAction, // <-- add this
            //         stock_quantity: stockQuantity // <-- and this
            //     };

            //     $.ajax({
            //         url: "/api/inventory_update/" + productId,
            //         type: "POST",
            //         headers: {
            //             "Authorization": "Bearer " + authToken
            //         },
            //         data: formData,
            //         success: function(response) {
            //             if (response.status) {
            //                 $("#editProductModal").modal("hide");
            //                 fetchinventory(); // refresh table
            //             } else {
            //                 alert("Update failed!");
            //             }
            //         }
            //     });
            // });
            $("#editProductForm").off("submit.rowMaterialInventory").on("submit.rowMaterialInventory", function(e) {
                e.preventDefault();
                const $submitBtn = $(this).find('button[type="submit"]');
                if ($submitBtn.prop('disabled')) {
                    return;
                }
                if (!$submitBtn.data('default-html')) {
                    $submitBtn.data('default-html', $submitBtn.html());
                }
                function toggleEditProductSubmitLoading(isLoading) {
                    if (isLoading) {
                        $submitBtn
                            .prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                            );
                    } else {
                        $submitBtn
                            .prop('disabled', false)
                            .html($submitBtn.data('default-html'));
                    }
                }

                let productId = $("#editProductId").val();
                let stockAction = $("input[name='stock_action']:checked").val();
                let stockQuantity = $("#stockQuantity").val();

                $("#stockError").hide().text("");

                // Validate radio
                if (!stockAction) {
                    $("#stockError").text("Please select Add Quantity or Minus Quantity").show();
                    return;
                }

                // Validate quantity
                if (!stockQuantity || stockQuantity <= 0) {
                    $("#stockError").text("Please enter quantity").show();
                    return;
                }

                const sub_branch_id = normalizeSubAdminId(localStorage.getItem('selectedSubAdminId'));
                var authToken = localStorage.getItem("authToken");

                let formData = {
                    name: $("#editProductName").val(),
                    sku: $("#editProductSku").val(),
                    price: $("#editProductPrice").val(),
                    current_stock: $("#editProductStock").val(),
                    initial_stock: $("#editProductinitialStock").val(),
                    sub_branch_id: sub_branch_id,
                    stock_action: stockAction,
                    stock_quantity: stockQuantity
                };

                $.ajax({
                    url: "/api/row-material-inventory_update/" + productId,
                    type: "POST",
                    beforeSend: function() {
                        toggleEditProductSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: formData,
                    success: function(response) {

                        if (response.status) {

                            $("#editProductModal").modal("hide");

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Quantity successfully updated",
                                confirmButtonColor: "#ff9f43"
                            }).then(() => {
                                location.reload();
                            });

                        } else {

                            $("#stockError").text("Update failed").show();

                        }
                    },
                    complete: function() {
                        toggleEditProductSubmitLoading(false);
                    }
                });

            });

            // Resize handler for responsive behavior
            let resizeTimer;
            let lastWidth = $(window).width();

            function forceInventoryCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleInventoryResize() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastWidth = currentWidth;

                    // Force CSS recalculation
                    forceInventoryCSSRecalculation();

                    const table = document.getElementById('inventory-table');
                    const tableResponsive = document.querySelector('.table-responsive');

                    [table, tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    if (inventoryTable && table) {
                        $('tr.inventory-details-row').remove();

                        try {
                            inventoryTable.columns.adjust();
                            inventoryTable.draw(false);

                            setTimeout(function() {
                                inventoryTable.columns.adjust();
                                inventoryTable.draw(false);

                                setTimeout(function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(inventoryTable);
                                    }
                                    forceInventoryCSSRecalculation();
                                    void table.offsetHeight;
                                }, 100);
                            }, 100);
                        } catch (e) {
                            // console.error('DataTables adjustment error:', e);
                            inventoryTable.draw(false);
                            setTimeout(function() {
                                if (window.addInventoryExpandableRows) {
                                    window.addInventoryExpandableRows(inventoryTable);
                                }
                                forceInventoryCSSRecalculation();
                            }, 150);
                        }
                    } else {
                        forceInventoryCSSRecalculation();
                    }
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.inventory').on('resize.inventory', handleInventoryResize);

            if (window.inventoryResizeHandler) {
                window.removeEventListener('resize', window.inventoryResizeHandler);
            }
            window.inventoryResizeHandler = handleInventoryResize;
            window.addEventListener('resize', window.inventoryResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.inventory').on('orientationchange.inventory', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const queries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            queries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleInventoryResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleInventoryResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 500);
            });

            setTimeout(function() {
                if (inventoryTable) {
                    handleInventoryResize();
                }
            }, 1000);

            window.handleInventoryResize = handleInventoryResize;

        });
    </script> --}}

    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            function normalizeSubAdminId(value) {
                if (!value || value === 'null' || value === 'undefined') {
                    return '';
                }

                return value;
            }

            var selectedSubAdminId = normalizeSubAdminId(localStorage.getItem("selectedSubAdminId"));

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let currentStartDate = '';
            let currentEndDate = '';

            // Helper functions
            function capitalizeWords(str) {
                if (!str || str.trim() === '') return 'N/A';
                return str.replace(/\b\w/g, function(char) {
                    return char.toUpperCase();
                });
            }

            function formatInventoryPrice(value) {
                if (value === null || value === undefined || value === '' || value === 'N/A') return 'N/A';
                const numericValue = Number(value);
                if (Number.isNaN(numericValue)) return value;
                return numericValue.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Initialize DataTable (display only, no search/paging)
            let inventoryTable = $('#inventory-table').DataTable({
                responsive: true,
                searching: false,
                paging: false,
                info: false,
                lengthChange: false,
                ordering: false,
                autoWidth: false,
                dom: 't',
                columnDefs: [{
                    targets: [1, 2, 3, 4, 5, 6],
                    className: 'inventory-column'
                }]
            });

            // Store product data for expandable rows
            window.inventoryDataMap = {};

            // Function to build expandable row content
            function buildInventoryExpandableRowContent(product) {
                let actionBtns = `
                <a class="btn btn-sm btn-primary me-2" href="/material-inventory-view/${product.id}" style="color: white;font-size: 13px;">
                    View History
                </a>
                <a class="btn btn-sm btn-primary edit-product" data-id="${product.id}" href="javascript:void(0);" style="color: white;font-size: 13px;">
                    Add / Edit Stock
                </a>
            `;
                return `
                <td colspan="7" class="inventory-details-content">
                    <div class="inventory-details-list">
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">SKU:</span>
                            <span class="inventory-detail-value-simple">${product.sku || 'N/A'}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Price:</span>
                            <span class="inventory-detail-value-simple">${formatInventoryPrice(product.price)}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Initial Stock:</span>
                            <span class="inventory-detail-value-simple">${product.initial_stock ?? 'N/A'}</span>
                        </div>
                        <div class="inventory-detail-row-simple">
                            <span class="inventory-detail-label-simple">Current Stock:</span>
                            <span class="inventory-detail-value-simple">${product.current_stock ?? 'N/A'}</span>
                        </div>
                    </div>
                    <div class="inventory-action-buttons-simple">
                        ${actionBtns}
                    </div>
                </td>
            `;
            }

            // Toggle function for expandable rows
            window.toggleInventoryRowDetails = function(productId) {
                const btn = $(`.inventory-toggle-btn-table[data-product-id="${productId}"]`);
                if (btn.length === 0) return;
                const row = btn.closest('tr');
                let detailsRow = row.next(`tr.inventory-details-row[data-product-id="${productId}"]`);
                const icon = btn.find('.toggle-icon');
                if (detailsRow.length === 0) {
                    const productData = window.inventoryDataMap[productId];
                    if (productData) {
                        detailsRow = $('<tr>')
                            .addClass('inventory-details-row')
                            .attr('data-product-id', productId)
                            .html(buildInventoryExpandableRowContent(productData));
                        row.after(detailsRow);
                    } else {
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
            };

            // Add expandable rows after table draw (for mobile/tablet)
            function addInventoryExpandableRows() {
                const currentWidth = $(window).width();
                const isMobileOrTablet = currentWidth <= 1024;
                if (!isMobileOrTablet) {
                    $('tr.inventory-details-row').remove();
                    return;
                }
                inventoryTable.rows().every(function() {
                    const row = this.node();
                    const toggleBtn = $(row).find('.inventory-toggle-btn-table');
                    if (toggleBtn.length > 0) {
                        const productId = toggleBtn.data('product-id');
                        const productData = window.inventoryDataMap[productId];
                        if (productData && !$(row).next('tr.inventory-details-row[data-product-id="' +
                                productId + '"]').length) {
                            const expandableRow = $('<tr>')
                                .addClass('inventory-details-row')
                                .attr('data-product-id', productId)
                                .html(buildInventoryExpandableRowContent(productData));
                            $(row).after(expandableRow);
                        }
                    }
                });
            }

            // Fetch inventory with pagination
            function fetchInventory(page = 1) {
                let url = `/api/row-material-inventory-list?page=${page}&per_page=${perPage}`;
                if (selectedSubAdminId) url += `&sub_branch_id=${selectedSubAdminId}`;
                if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;
                if (currentStartDate) url += `&start_date=${currentStartDate}`;
                if (currentEndDate) url += `&end_date=${currentEndDate}`;
                // Include category and brand filters if present (they exist in the original blade but might not be used)
                let categoryId = $('#filterCategory').val();
                let brandId = $('#filterBrand').val();
                if (categoryId) url += `&category_id=${categoryId}`;
                if (brandId) url += `&brand_id=${brandId}`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let products = response.inventory || [];
                            let pagination = response.pagination;

                            // Update pagination state
                            currentPage = pagination.current_page;
                            lastPage = pagination.last_page;
                            updatePaginationUI(pagination);

                            // Build table rows
                            let tableBody = [];
                            const rowStart = ((pagination.current_page - 1) * pagination.per_page) + 1;

                            products.forEach((product, index) => {
                                let productName = capitalizeWords(product.product_name ||
                                'N/A');
                                let sku = product.sku || 'N/A';
                                let rawPrice = product.price;
                                let price = formatInventoryPrice(rawPrice);
                                let initialStock = product.initial_stock ?? 'N/A';
                                let currentStock = product.current_stock ?? 'N/A';
                                let id = product.id;

                                // Store product data for expandable rows
                                window.inventoryDataMap[id] = {
                                    ...product,
                                    sku: sku,
                                    price: rawPrice,
                                    initial_stock: initialStock,
                                    current_stock: currentStock
                                };

                                let stockDisplay =
                                    `<span class="quantity-history" data-id="${id}" style="cursor:pointer;">${currentStock}</span>`;
                                if (currentStock !== 'N/A' && parseInt(currentStock) <= (
                                        response.lowStockThreshold || 0)) {
                                    stockDisplay +=
                                        ` <span class="badge bg-danger ms-1">Low Stock</span>`;
                                }

                                let actionBtns = `
                                <div class="inventory-table-actions">
                                    <a class="btn btn-primary" href="/material-inventory-view/${id}" style="color: white;font-size: 13px;">View History</a>
                                    <a class="edit-product btn btn-primary" data-id="${id}" href="javascript:void(0);" style="color: white;font-size: 13px;">Add / Edit Stock</a>
                                </div>
                            `;

                                let detailsColumn = `
                                <button class="inventory-toggle-btn-table" onclick="toggleInventoryRowDetails('${id}')" data-product-id="${id}">
                                    <span class="toggle-icon">+</span>
                                </button>
                            `;

                                tableBody.push([
                                    rowStart + index,
                                    productName, // Product Name
                                    detailsColumn, // Details (toggle button)
                                    sku,
                                    price,
                                    initialStock,
                                    stockDisplay,
                                    actionBtns
                                ]);
                            });

                            inventoryTable.clear().rows.add(tableBody).draw();
                            // After draw, add expandable rows if needed
                            setTimeout(() => addInventoryExpandableRows(), 100);
                            $('.pagination-controls').show();
                        } else {
                            inventoryTable.clear().draw();
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr);
                        inventoryTable.clear().draw();
                        $('.pagination-controls').hide();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load inventory data',
                            confirmButtonColor: '#ff9f43'
                        });
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
                const totalPages = pagination.last_page;
                const currentPage = pagination.current_page;
                let pagesToShow = [];

                if (totalPages <= 7) {
                    for (let i = 1; i <= totalPages; i++) {
                        pagesToShow.push(i);
                    }
                } else {
                    pagesToShow = [1];

                    if (currentPage > 4) {
                        pagesToShow.push('ellipsis-start');
                    }

                    const middleStart = Math.max(2, currentPage - 1);
                    const middleEnd = Math.min(totalPages - 1, currentPage + 1);

                    for (let i = middleStart; i <= middleEnd; i++) {
                        if (!pagesToShow.includes(i)) {
                            pagesToShow.push(i);
                        }
                    }

                    if (currentPage < totalPages - 3) {
                        pagesToShow.push('ellipsis-end');
                    }

                    if (!pagesToShow.includes(totalPages)) {
                        pagesToShow.push(totalPages);
                    }
                }

                if (totalPages > 1) {
                    paginationHtml += `
                    <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage - 1}">Prev</a>
                    </li>
                `;
                }

                pagesToShow.forEach((page) => {
                    if (typeof page === 'string') {
                        paginationHtml += `
                        <li class="page-item ellipsis">
                            <span class="page-link">...</span>
                        </li>
                    `;
                    } else {
                        paginationHtml += `
                        <li class="page-item ${page === currentPage ? 'active' : ''}">
                            <a class="page-link" href="javascript:void(0);" data-page="${page}">${page}</a>
                        </li>
                    `;
                    }
                });

                if (totalPages > 1) {
                    paginationHtml += `
                    <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${currentPage + 1}">Next</a>
                    </li>
                `;
                }
                $('#pagination-numbers').html(paginationHtml);
            }

            // Event handlers
            $('#inventory-search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchInventory(1);
            });

            $('#per-page-select-bottom').on('change', function() {
                perPage = $(this).val();
                fetchInventory(1);
            });

            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchInventory(page);
                }
            });

            // Date filters
            $('#startDate, #endDate').on('change', function() {
                currentStartDate = $('#startDate').val();
                currentEndDate = $('#endDate').val();
                fetchInventory(1);
            });

            // Category and brand filters (if they exist)
            $('#filterCategory, #filterBrand').on('change', function() {
                fetchInventory(1);
            });

            // Initial load
            fetchInventory(1);

            // ✅ Open Edit Modal
            $('#editProductModal').on('show.bs.modal', function() {
                $('input[name="stock_action"]').prop('checked', false);
                $('#stockQuantityBox').addClass('d-none');
                $('#stockQuantity').val('');
                $('#stockError').hide().text('');
            });

            // Show/hide quantity box based on radio selection
            $("#addStock, #minusStock").on("change", function() {
                const stockBox = $("#stockQuantityBox");
                const stockLabel = $("#stockQuantityLabel");
                if ($(this).val() === "add") {
                    stockBox.removeClass("d-none");
                    stockLabel.text("How much to Add?");
                } else if ($(this).val() === "minus") {
                    stockBox.removeClass("d-none");
                    stockLabel.text("How much to Minus?");
                }
            });

            // Open Edit Modal (unchanged)
            $(document).off("click.rowMaterialInventoryEdit", ".edit-product").on("click.rowMaterialInventoryEdit", ".edit-product", function() {
                var authToken = localStorage.getItem("authToken");
                let productId = $(this).data("id");
                $("#stockError").hide().text("");
                $.ajax({
                    url: "/api/row-materials_edit_inventory/" + productId,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            let product = response.product;
                            $("#editProductId").val(product.id);
                            $("#editProductName").val(product.product_name);
                            $("#editProductSku").val(product.sku);
                            $("#editProductPrice").val(product.price);
                            $("#editProductinitialStock").val(product.initial_stock);
                            $("#editProductStock").val(product.current_stock);
                            $("#editProductModal").modal("show");
                        } else {
                            $("#stockError").text(response.message || "Row Material not found!").show();
                        }
                    },
                    error: function(xhr) {
                        let message = "Failed to load row material details.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $("#stockError").text(message).show();
                    }
                });
            });

            // Save Changes (unchanged)
            $("#editProductForm").on("submit", function(e) {
                e.preventDefault();
                const $submitBtn = $(this).find('button[type="submit"]');
                if ($submitBtn.prop('disabled')) {
                    return;
                }
                if (!$submitBtn.data('default-html')) {
                    $submitBtn.data('default-html', $submitBtn.html());
                }
                function toggleEditProductSubmitLoading(isLoading) {
                    if (isLoading) {
                        $submitBtn
                            .prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                            );
                    } else {
                        $submitBtn
                            .prop('disabled', false)
                            .html($submitBtn.data('default-html'));
                    }
                }

                let productId = $("#editProductId").val();
                let stockAction = $("input[name='stock_action']:checked").val();
                let stockQuantity = $("#stockQuantity").val();

                $("#stockError").hide().text("");
                if (!stockAction) {
                    $("#stockError").text("Please select Add Quantity or Minus Quantity").show();
                    return;
                }
                if (!stockQuantity || stockQuantity <= 0) {
                    $("#stockError").text("Please enter quantity").show();
                    return;
                }

                const sub_branch_id = localStorage.getItem('selectedSubAdminId');
                var authToken = localStorage.getItem("authToken");
                let formData = {
                    name: $("#editProductName").val(),
                    sku: $("#editProductSku").val(),
                    price: $("#editProductPrice").val(),
                    current_stock: $("#editProductStock").val(),
                    initial_stock: $("#editProductinitialStock").val(),
                    sub_branch_id: sub_branch_id,
                    stock_action: stockAction,
                    stock_quantity: stockQuantity
                };

                $.ajax({
                    url: "/api/row-material-inventory_update/" + productId,
                    type: "POST",
                    beforeSend: function() {
                        toggleEditProductSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: formData,
                    success: function(response) {
                        if (response.status) {
                            $("#editProductModal").modal("hide");
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: "Quantity successfully updated",
                                confirmButtonColor: "#ff9f43"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            $("#stockError").text(response.message || "Update failed").show();
                        }
                    },
                    error: function(xhr) {
                        let message = "Update failed";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $("#stockError").text(message).show();
                    },
                    complete: function() {
                        toggleEditProductSubmitLoading(false);
                    }
                });
            });
            // Resize handler for responsive behavior
            let resizeTimer;
            let lastWidth = $(window).width();

            function forceInventoryCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleInventoryResize() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastWidth = currentWidth;

                    // Force CSS recalculation
                    forceInventoryCSSRecalculation();

                    const table = document.getElementById('inventory-table');
                    const tableResponsive = document.querySelector('.table-responsive');

                    [table, tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    if (inventoryTable && table) {
                        $('tr.inventory-details-row').remove();

                        try {
                            inventoryTable.columns.adjust();
                            inventoryTable.draw(false);

                            setTimeout(function() {
                                inventoryTable.columns.adjust();
                                inventoryTable.draw(false);

                                setTimeout(function() {
                                    if (window.addInventoryExpandableRows) {
                                        window.addInventoryExpandableRows(inventoryTable);
                                    }
                                    forceInventoryCSSRecalculation();
                                    void table.offsetHeight;
                                }, 100);
                            }, 100);
                        } catch (e) {
                            // console.error('DataTables adjustment error:', e);
                            inventoryTable.draw(false);
                            setTimeout(function() {
                                if (window.addInventoryExpandableRows) {
                                    window.addInventoryExpandableRows(inventoryTable);
                                }
                                forceInventoryCSSRecalculation();
                            }, 150);
                        }
                    } else {
                        forceInventoryCSSRecalculation();
                    }
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.inventory').on('resize.inventory', handleInventoryResize);

            if (window.inventoryResizeHandler) {
                window.removeEventListener('resize', window.inventoryResizeHandler);
            }
            window.inventoryResizeHandler = handleInventoryResize;
            window.addEventListener('resize', window.inventoryResizeHandler, {
                passive: true
            });

            // Orientation change handler
            $(window).off('orientationchange.inventory').on('orientationchange.inventory', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const queries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            queries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleInventoryResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleInventoryResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastWidth = $(window).width();
                    handleInventoryResize();
                }, 500);
            });

            setTimeout(function() {
                if (inventoryTable) {
                    handleInventoryResize();
                }
            }, 1000);

            window.handleInventoryResize = handleInventoryResize;

        });
    </script>
@endpush
