@extends('layout.app')

@section('title', 'Product List')

@section('content')
    <style>
        .sorting_1 {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }

        /* .category-width{
                    width: 207px;
                } */

        #filterCategory,
        #filterBrand {
            width: 100% !important;
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

        /* Word wrapping + compact desktop layout */
        table.datanew {
            table-layout: auto !important;
            width: 100% !important;
        }

        table.datanew td,
        table.datanew th {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            vertical-align: middle;
            padding: 10px 8px !important;
        }

        /* Keep short-value columns compact so extra blank gaps are reduced */
        table.datanew thead th:nth-child(3),
        table.datanew tbody td:nth-child(3),
        table.datanew thead th:nth-child(5),
        table.datanew tbody td:nth-child(5),
        table.datanew thead th:nth-child(7),
        table.datanew tbody td:nth-child(7),
        table.datanew thead th:nth-child(8),
        table.datanew tbody td:nth-child(8) {
            width: 1%;
            white-space: nowrap !important;
        }

        /* Long text columns should wrap cleanly */
        table.datanew tbody td:nth-child(1),
        table.datanew tbody td:nth-child(4),
        table.datanew tbody td:nth-child(6),
        table.datanew tbody td:nth-child(9) {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            line-height: 1.3;
        }


        @media (min-width: 1200px) {
            .category-width {
                width: 207px;
            }

            /* .product-toolbar {
                flex-wrap: nowrap;
            }

            .product-toolbar-filters {
                flex-wrap: nowrap;
            }

            .product-toolbar-filters .form-group,
            .product-toolbar-filters .category-width {
                flex: 0 0 207px;
                max-width: 207px;
            } */
        }

        @media screen and (max-width: 1199px) {
            .table-scroll-top {
                display: block;
                -webkit-overflow-scrolling: touch !important;
                /* smooth scrolling on iOS */
            }

            select#filterCategory {
                font-size: 12px !important;
            }

            select#filterBrand {
                font-size: 12px !important;
                width: 164px !important;
            }

            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch !important;
            }

            .form-control-sm {
                min-height: calc(1.5em + .5rem + 2px);
                padding: .25rem .5rem;
                font-size: 13px !important;
                border-radius: .2rem;
            }

            .search-set {
                margin-right: 1rem !important;
            }
        }

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

        .product-toolbar {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .product-toolbar-filters {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            flex-wrap: wrap;
            flex: 1 1 auto;
            min-width: 0;
        }

        .product-toolbar-search {
            flex: 1 1 320px;
            min-width: 220px;
            max-width: 260px;
        }
 .product-toolbar-filters .form-group {
            flex: 0 0 180px;
 }
        /* .product-toolbar-filters .form-group,
        .product-toolbar-filters .category-width {
            flex: 1 1 200px;
            min-width: 180px;
            max-width: 260px;
        }

        .product-toolbar-filters .select2-container {
            width: 100% !important;
        } */

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
            width: 100%;
        }

        .search-input {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 7px !important;
        }

        .product-toolbar-actions {
            display: flex;
            gap: 8px;
            margin-left: auto;
            flex-wrap: wrap;
        }

        .product-toolbar-actions .btn {
            white-space: nowrap;
        }

        /* Desktop: show all columns normally */
        @media (min-width: 1200px) {
            .product-toolbar {
                flex-wrap: nowrap;
                align-items: flex-end;
            }

            .product-toolbar-filters {
                flex-wrap: nowrap;
            }

            .product-toolbar-actions {
                margin-left: auto;
                flex-wrap: nowrap;
            }

            table.datanew thead th,
            table.datanew tbody td {
                display: table-cell !important;
            }

            /* Hide the Details toggle column on desktop */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: none !important;
            }

            /* Desktop column sizing to avoid extra white gaps */
            table.datanew thead th:nth-child(1),
            table.datanew tbody td:nth-child(1) {
                width: 28% !important;
            }

            table.datanew thead th:nth-child(3),
            table.datanew tbody td:nth-child(3) {
                width: 8% !important;
            }

            table.datanew thead th:nth-child(4),
            table.datanew tbody td:nth-child(4) {
                width: 14% !important;
            }

            table.datanew thead th:nth-child(5),
            table.datanew tbody td:nth-child(5) {
                width: 8% !important;
            }

            table.datanew thead th:nth-child(6),
            table.datanew tbody td:nth-child(6) {
                width: 14% !important;
            }

            table.datanew thead th:nth-child(7),
            table.datanew tbody td:nth-child(7) {
                width: 10% !important;
            }

            table.datanew thead th:nth-child(8),
            table.datanew tbody td:nth-child(8) {
                width: 8% !important;
            }

            table.datanew thead th:nth-child(9),
            table.datanew tbody td:nth-child(9) {
                width: 10% !important;
            }
        }

        /* Mobile: hide non-essential columns, show Details toggle */
        /* Mobile: hide non-essential columns, show Details toggle */
        @media (max-width: 1199px) {

            table.datanew thead th:nth-child(n+3),
            table.datanew tbody td:nth-child(n+3) {
                display: none !important;
            }

            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            .toggle-details i {
                font-size: 18px;
            }

            /* Add these styles for product name wrapping */
            table.datanew tbody td:first-child {
                /* display: flex !important; */
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
                /* Adjust based on your layout */
            }

            table.datanew tbody td:first-child a {
                /* display: -webkit-box !important; */
                display: -ms-flexbox !important;
                /* display: flex !important; */
                -webkit-box-align: center !important;
                -ms-flex-align: center !important;
                align-items: center !important;
                text-align: left !important;
                max-width: 100% !important;
                word-wrap: break-word !important;
                word-break: break-word !important;
                overflow-wrap: break-word !important;
                white-space: normal !important;
                line-height: 1.3 !important;
            }

            /* For the product name text specifically */
            table.datanew tbody td:first-child a[style*="font-weight: 500"] {
                display: inline-block !important;
                max-width: calc(100% - 70px) !important;
                /* Account for image width */
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
                hyphens: auto !important;
                -webkit-hyphens: auto !important;
                -ms-hyphens: auto !important;
            }

            /* If you want to limit to 2 lines with ellipsis */
            table.datanew tbody td:first-child a.product-name {
                display: -webkit-box !important;
                -webkit-line-clamp: 2 !important;
                -webkit-box-orient: vertical !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }

            .product-toolbar {
                gap: 10px;
                align-items: stretch;
            }

            .product-toolbar-filters {
                width: 100%;
                gap: 10px;
            }

            .product-toolbar-filters .form-group,
            .product-toolbar-filters .category-width {
                flex: 1 1 calc(50% - 5px);
                min-width: 150px;
                max-width: none;
            }

            .product-toolbar-search {
                flex: 1 1 100%;
                min-width: 100%;
            }

            .product-toolbar-actions {
                width: 100%;
                margin-left: 0;
                justify-content: space-between;
            }

            #exportAllChallan,
            #exportPdf {
                width: 48% !important;
                flex: 0 0 48% !important;
                max-width: 48% !important;
                min-width: 48% !important;
                margin: 0 !important;
                padding: 6px 0px !important;
                font-size: 13px !important;
            }

            #exportAllChallan i,
            #exportPdf i {
                font-size: 12px !important;
                margin-right: 4px !important;
            }
        }

        /* Tablet specific fixes */
        @media screen and (min-width: 769px) and (max-width: 1199px) {

            /* Ensure table is properly responsive */
            .table-responsive {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch !important;
            }

            /* Make sure details column is visible and properly sized */
            table.datanew thead th.details-column,
            table.datanew tbody td:nth-child(2) {
                display: table-cell !important;
                width: 60px !important;
                min-width: 60px !important;
                max-width: 60px !important;
            }

            /* Ensure toggle icon is visible and clickable */
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

            /* Adjust product name width for tablet */
            table.datanew tbody td:first-child a[style*="font-weight: 500"] {
                max-width: calc(100% - 80px) !important;
                font-size: 14px !important;
            }
        }

        /* ===== Action Column UI Fix ===== */

        table.datanew td:last-child {
            white-space: normal !important;
        }

        /* Action column fix - keep in one row */
        .action-buttons {
            display: flex;
            flex-wrap: nowrap;
            /* ✅ Prevent wrapping */
            align-items: center;
            gap: 10px;
            white-space: nowrap;
            /* ✅ Keep buttons in one line */
        }

        .action-buttons .btn {
            font-size: 12px;
            padding: 4px 8px;
        }

        .icon-btn {
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #f5f5f5;
        }

        .icon-btn:hover {
            background: #ececec;
        }

        #quantityHistoryModal .modal-content {
            max-height: 85vh;
        }

        #quantityHistoryModal .modal-body,
        #quantityHistoryModal #quantityHistoryContent {
            max-height: calc(85vh - 130px);
            overflow-y: auto;
            overflow-x: hidden;
        }

        #quantityHistoryModal .quantity-history-close {
            border: 0;
            background: transparent;
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            line-height: 1;
            color: #000;
            opacity: 1;
        }

        #quantityHistoryModal .quantity-history-close:hover {
            background: #ea5455;
            color: #fff;
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

    <!-- Quantity History Modal -->
    <div class="modal fade" id="quantityHistoryModal" tabindex="-1" aria-labelledby="quantityHistoryLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityHistoryLabel">Quantity History</h5>
                    <button type="button" class="close quantity-history-close" data-dismiss="modal" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="quantityHistoryContent">
                    <!-- History content will be injected here -->
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Products</h4>
                <!--<h6>Manage your products</h6>-->
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(1, 'add'))
                    <a href="{{ route('product.add') }}" class="btn btn-sm btn-added"><img
                            src="{{ env('ImagePath') . '/admin/assets/img/icons/plus.svg' }}" alt="img"
                            class="me-1">New
                        Product</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="product-toolbar">
                    <div class="product-toolbar-filters">
                        <div class="product-toolbar-search">
                        <label for="search-input" class="form-label mb-1">Search</label>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img">
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                        <div class="form-group mb-0 category-width">
                            <label for="filterCategory" class="form-label mb-1">Category</label>
                            <select id="filterCategory" class="form-control form-control-sm select-filter">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mb-0 category-width">
                            <label for="filterBrand" class="form-label mb-1">Brand</label>
                            <select id="filterBrand" class="form-control form-control-sm select-filter">
                                <option value="">All Brands</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>



                    <div class="product-toolbar-actions">
                        @if (app('hasPermission')(1, 'view'))
                        <button id="exportAllChallan" class="btn btn-sm btn-success">
                            <i class="fas fa-file-excel me-1"></i> Excel
                        </button>
                        <button id="exportPdf" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf me-1"></i> PDF
                        </button>
                        @endif
                    </div>
                </div>

                <div class="table-scroll-top">
                    <div></div>
                </div>
                <div class="table-responsive">
                    <table class="table  datanew">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th class="details-column">Details</th>
                                <th>SKU</th>
                                <th>Category </th>
                                <th>Unit</th>
                                <th>Brand</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th style="width: 145px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>


                        </tbody>
                    </table>
                </div>

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
            var table = $('.datanew').DataTable({
                "destroy": true,
                "bFilter": false,
                "paging": false,
                "info": false,
                "searching": false,
                "ordering": false,
                "dom": 't'
            });

            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            // Initialize Select2 for custom class
            $('.select-filter').select2();

            // Define the function FIRST
            function fetchProducts(page = 1) {
                var categoryId = $('#filterCategory').val();
                var brandId = $('#filterBrand').val();

                $.ajax({
                    url: "/api/getAllProduct",
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        page: page,
                        per_page: perPage,
                        search: searchQuery,
                        category_id: categoryId,
                        brand_id: brandId,
                        sub_branch_id: selectedSubAdminId
                    },
                    success: function(response) {
                        if (response.status) {
                            let products = response.data;
                            let currencySymbol = response.currencySymbol || '₹';
                            let currencyPosition = response.currencyPosition || 'left';
                            let tableBody = [];

                            let lowStockThreshold = response.lowStockThreshold || 0;
                            let pagination = response.pagination || null;

                            if (pagination) {
                                currentPage = pagination.current_page;
                                lastPage = pagination.last_page;
                                updatePaginationUI(pagination);
                            }

                            // Function to capitalize first letter of each word
                            function capitalizeWords(str) {
                                if (!str || str.trim() === '') return 'N/A';
                                return str.replace(/\b\w/g, function(char) {
                                    return char.toUpperCase();
                                });
                            }
                            $.each(products, function(index, product) {
                                let productName = capitalizeWords(product.name || 'N/A');
                                let sku = product.SKU || 'N/A';
                                let quantity = product.quantity ?? 'N/A';
                                let categoryName = capitalizeWords(product.category ? product
                                    .category.name : "N/A");
                                let unitName = capitalizeWords(product.unit ? product.unit
                                    .unit_name : "N/A");
                                let brandName = capitalizeWords(product.brand ? product.brand
                                    .name : "N/A");

                                let createdAt = capitalizeWords(product.product_type ? product
                                    .product_type
                                    .name : "N/A");
                                let detailsToggle = `
    <a href="#details-${product.id}" class="toggle-details" data-bs-toggle="collapse">
        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
    </a>
`;

                                let productImage =
                                    '{{ env('ImagePath') . 'admin/assets/img/product/noimage.png' }}';
                                const ImagePath = "{{ env('ImagePath') . 'storage/' }}";
                                if (product.images) {
                                    let imagesArray = typeof product.images === "string" ? JSON
                                        .parse(product.images) : product.images;
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${ImagePath}${imagesArray[0]}`;
                                    }
                                }

                                let formattedPrice = 'N/A';
                                if (product.price !== undefined && product.price !== null &&
                                    product.price !== '') {
                                    let priceVal = parseFloat(product.price).toLocaleString(
                                        undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    if (currencyPosition === 'left') {
                                        formattedPrice = currencySymbol + priceVal;
                                    } else {
                                        formattedPrice = priceVal + currencySymbol;
                                    }
                                }

                                const isLowStock = quantity !== 'N/A' && parseInt(quantity) <=
                                    lowStockThreshold;
                                let quantityDisplay =
                                    `<span class="quantity-history" data-id="${product.id}" style="cursor:pointer; border: 1px solid #ececec; padding: 4px 8px; border-radius: 4px; display: inline-block; color: ${isLowStock ? '#ea5455' : 'inherit'}; font-weight: ${isLowStock ? '600' : '400'};">${quantity}</span>`;
                                if (isLowStock) {
                                    quantityDisplay +=
                                        ` <span class="badge bg-danger ms-1">Low Stock</span>`;
                                }

                                tableBody.push([
                                    `<div style="display: flex;">

                                    <a href="/product-view/${product.id}" class="product-img mb-1">
                                        <img src="${productImage}" alt="product" style="max-width: 60px;">
                                    </a>
                                    <a href="/product-view/${product.id}" style="color: #1b2850; font-weight: 500; margin-left: 8px;">
                                        ${productName}
                                    </a>

                                </div>

                                  <!-- Collapsible Details (visible only on mobile) -->
    <div class="collapse mt-2 d-xl-none" id="details-${product.id}">
        <div class="card card-body p-2 bg-light border">
            <p class="mb-1"><strong>SKU:</strong> ${sku}</p>
            <p class="mb-1"><strong>Category:</strong> ${categoryName}</p>
            <p class="mb-1"><strong>Unit:</strong> ${unitName}</p>
            <p class="mb-1"><strong>Brand:</strong> ${brandName}</p>
            <p class="mb-1"><strong>Price:</strong> ${formattedPrice}</p>
            <p class="mb-1"><strong>Quantity:</strong> ${quantityDisplay}</p>
            <div class="mt-2">
                ${`<div class="action-buttons">
                    @if (app('hasPermission')(17, 'view'))
                                                                                         <a class=" btn btn-sm btn-primary" style="color:white; font-size: 13px;" href="/inventory-View/${product.id}">
                                                                                            History
                                                                                        </a>
                                                                                        @endif
                                                                                        @if (app('hasPermission')(1, 'view'))
                                                                                        <a class=" icon-btn" href="/product-view/${product.id}">
                                                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
                                                <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
                                                </svg>
                                                                                        </a>
                                                                                        @endif
                                                                                        @if (app('hasPermission')(1, 'edit'))
                                                                                        <a class=" icon-btn" href="/edit-product/${product.id}">
                                                                                            <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
                                                </svg>
                                                                                        </a>
                                                                                        @endif
                                                                                        @if (app('hasPermission')(1, 'delete'))
                                                                                        <a class="confirm-text delete-product" data-id="${product.id}" href="javascript:void(0);">
                                                                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                                    <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                                                                    <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                                                                </svg>
                                                                                        </a>
                                                                                        @endif </div>`}
            </div>
        </div>
    </div>`,
                                    detailsToggle,
                                    sku,
                                    categoryName,
                                    unitName,
                                    brandName,
                                    formattedPrice,
                                    quantityDisplay,
                                    // createdAt,
                                    `<div class="action-buttons">
                                                @if (app('hasPermission')(17, 'view'))  
                                                 <a class=" btn btn-sm btn-primary" style="color:white; font-size: 13px;" href="/inventory-View/${product.id}">
                                                    History
                                                </a>
                                                @endif
                                                @if (app('hasPermission')(1, 'view'))
                                                <a class="icon-btn" href="/product-view/${product.id}">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12 9C11.206 9.00524 10.4459 9.32299 9.88447 9.88447C9.32299 10.4459 9.00524 11.206 9 12C9 13.642 10.358 15 12 15C13.641 15 15 13.642 15 12C15 10.359 13.641 9 12 9Z" fill="#092C4C"/>
        <path d="M12 5C4.36704 5 2.07304 11.617 2.05204 11.684L1.94604 12L2.05105 12.316C2.07305 12.383 4.36704 19 12 19C19.633 19 21.927 12.383 21.948 12.316L22.054 12L21.949 11.684C21.927 11.617 19.633 5 12 5ZM12 17C6.64904 17 4.57604 13.154 4.07404 12C4.57804 10.842 6.65204 7 12 7C17.351 7 19.424 10.846 19.926 12C19.422 13.158 17.348 17 12 17Z" fill="#092C4C"/>
        </svg>
                                                </a>
                                                @endif
                                                @if (app('hasPermission')(1, 'edit'))
                                                <a class="icon-btn" href="/edit-product/${product.id}">
                                                    <svg width="16" height="20" viewBox="0 0 16 20" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M15.045 5.401C15.423 5.023 15.631 4.521 15.631 3.987C15.631 3.453 15.423 2.951 15.045 2.573L13.459 0.987001C13.081 0.609001 12.579 0.401001 12.045 0.401001C11.511 0.401001 11.009 0.609001 10.632 0.986001L0 11.585V16H4.413L15.045 5.401ZM12.045 2.401L13.632 3.986L12.042 5.57L10.456 3.985L12.045 2.401ZM2 14V12.415L9.04 5.397L10.626 6.983L3.587 14H2ZM0 18H16V20H0V18Z" fill="#092C4C"/>
        </svg>
                                                </a>
                                                @endif
                                                @if (app('hasPermission')(1, 'delete'))
                                                <a class="confirm-text delete-product" data-id="${product.id}" href="javascript:void(0);">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5 20C5 20.5304 5.21071 21.0391 5.58579 21.4142C5.96086 21.7893 6.46957 22 7 22H17C17.5304 22 18.0391 21.7893 18.4142 21.4142C18.7893 21.0391 19 20.5304 19 20V8H21V6H17V4C17 3.46957 16.7893 2.96086 16.4142 2.58579C16.0391 2.21071 15.5304 2 15 2H9C8.46957 2 7.96086 2.21071 7.58579 2.58579C7.21071 2.96086 7 3.46957 7 4V6H3V8H5V20ZM9 4H15V6H9V4ZM8 8H17V20H7V8H8Z" fill="#092C4C"/>
                                            <path d="M9 10H11V18H9V10ZM13 10H15V18H13V10Z" fill="#092C4C"/>
                                        </svg>
                                                </a>
                                                @endif
                                                </div>
                                                `
                                ]);
                            });
                            table.clear().rows.add(tableBody).draw();

                            // Sync top scrollbar
                            const topScroll = document.querySelector('.table-scroll-top');
                            const tableResponsive = document.querySelector('.table-responsive');
                            const tableElement = document.querySelector('.datanew');

                            if (topScroll && tableResponsive && tableElement) {
                                const topInnerDiv = topScroll.querySelector('div');
                                topInnerDiv.style.width = tableElement.scrollWidth + 'px';

                                topScroll.onscroll = () => {
                                    tableResponsive.scrollLeft = topScroll.scrollLeft;
                                };
                                tableResponsive.onscroll = () => {
                                    topScroll.scrollLeft = tableResponsive.scrollLeft;
                                };
                            }

                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html('<tr><td colspan="9">No products found</td></tr>');
                            $('#pagination-from').text(0);
                            $('#pagination-to').text(0);
                            $('#pagination-total').text(0);
                            $('#pagination-numbers').html('');
                            $('.pagination-controls').hide();
                        }
                    },
                    error: function() {
                        alert("Error fetching products.");
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
                $('.pagination-controls').show();
            }

            $(document).on('click', '.quantity-history', function() {
                const productId = $(this).data('id');

                $.ajax({
                    url: `/api/product-quantity-history/${productId}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(res) {
                        if (res.status) {
                            let content = `<h5>${res.product}</h5><ul class="list-group">`;
                            res.history.forEach(item => {
                                content += `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${item.type}</strong> - ${item.note}<br>
                                                <small>${item.date}</small>
                                            </div>
                                        <span class="badge bg-${item.type === 'Added' ? 'success' : 'danger'} px-4 py-2 small">${item.quantity}</span>
                                        </li>
                                    `;
                            });
                            content += `</ul>`;
                            $('#quantityHistoryContent').html(content);
                            $('#quantityHistoryModal').modal('show');
                        } else {
                            alert(res.message || "Failed to load quantity history.");
                        }
                    },
                    error: function() {
                        alert("Error fetching quantity history.");
                    }
                });
            });

            // Initial fetch
            fetchProducts(currentPage);

            // Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchProducts(1);
            });

            // Handle page number clicks
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchProducts(page);
                }
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchProducts(1);
            });

            // Filter change
            $('#filterCategory, #filterBrand').on('change', function() {
                fetchProducts(1);
            });
        })


        $(document).on('click', '.delete-product', function() {
            var productId = $(this).data('id'); // Get product ID
            var authToken = localStorage.getItem("authToken");
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
                        url: `/api/deleteProduct/${productId}`, // Adjust based on your route
                        type: 'POST',
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: {
                            _token: $('meta[name="csrf-token"]').attr(
                                'content') // CSRF token for security
                        },
                        success: function(response) {
                            if (response.status) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });

                                $(`.delete-product[data-id="${productId}"]`).closest('tr')
                                    .remove();
                            } else {
                                // Use response.error here, fallback to response.message just in case
                                Swal.fire("Error!", response.error || response.message ||
                                    "Unknown error", "error");
                            }
                        },

                        error: function(xhr) {
                            // Parse the JSON error message sent by API in HTTP error response
                            let message = "Something went wrong!";
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                message = xhr.responseJSON.error;
                            }
                            // Swal.fire("Error!", message, "error");
                            Swal.fire({
                                title: "Error!",
                                text: message,
                                icon: "error",
                                confirmButtonColor: "#ff9f43", // custom orange color
                                confirmButtonText: "OK"
                            });

                        }
                    });
                }
            });
        });

        $('#exportAllChallan').click(function() {
            let selectedCategory = $('#filterCategory').val() || '';
            let selectedBrand = $('#filterBrand').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-product?category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },

                beforeSend: function() {
                    Swal.fire({
                        title: "Exporting Excel...",
                        text: "Please wait while we generate your file.",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },

                success: function(response) {
                    Swal.close();

                    if (response.status && response.file_url) {
                        Swal.fire({
                            icon: "success",
                            title: "Excel Exported!",
                            text: "Click the button below to download your Excel file.",
                            showConfirmButton: true,
                            confirmButtonColor: "#28a745",
                            confirmButtonText: "Download File"
                        }).then(() => {
                            let link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || "Products.xlsx";
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Export Failed!",
                            text: "Unable to export Excel file. Please try again."
                        });
                    }
                },

                error: function(xhr, status, error) {
                    Swal.close();
                    console.error("Export failed:", error);

                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Export failed. Please try again."
                    });
                }
            });
        });


        $('#exportPdf').click(function() {
            let selectedCategory = $('#filterCategory').val() || '';
            let selectedBrand = $('#filterBrand').val() || '';
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let authToken = localStorage.getItem("authToken");

            let url =
                `/api/export-product-pdf?category_id=${selectedCategory}&brand_id=${selectedBrand}&selectedSubAdminId=${selectedSubAdminId}`;

            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken
                },

                beforeSend: function() {
                    Swal.fire({
                        title: "Generating PDF...",
                        text: "Please wait",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },

                success: function(response) {
                    Swal.close();

                    if (response.status && response.file_url) {
                        Swal.fire({
                            icon: "success",
                            title: "PDF Generated Successfully!",
                            text: "Your product PDF is ready.",
                            showConfirmButton: true,
                            confirmButtonColor: "#28a745",
                            confirmButtonText: "Download PDF"
                        }).then(() => {
                            let link = document.createElement('a');
                            link.href = response.file_url;
                            link.download = response.file_name || "products.pdf";
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Failed!",
                            text: "Could not generate the PDF. Please try again."
                        });
                    }
                },

                error: function(xhr, status, error) {
                    Swal.close();
                    console.error("Export PDF failed:", error);

                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Export PDF failed. Please try again."
                    });
                }
            });
        });


        $(document).on('click', '.toggle-details', function() {
            let icon = $(this).find('i');
            if (icon.hasClass('fa-plus-circle')) {
                icon.removeClass('fa-plus-circle')
                    .addClass('fa-minus-circle')
                    .css('color', 'red'); // or any color you want for minus icon
            } else {
                icon.removeClass('fa-minus-circle')
                    .addClass('fa-plus-circle')
                    .css('color', '#ff9f43'); // your desired orange color
            }
        });
    </script>
@endpush
