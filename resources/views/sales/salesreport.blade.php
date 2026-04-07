@extends('layout.app')

@section('title', 'Sales Report')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
    <style>
        .dataTables_info {
            float: left;
            padding-right: 15px;
            font-size: 12px;
            color: #5e5873;
            font-weight: 600;
        }

        .dataTables_filter {
            display: none !important;
        }

        tbody tr td:nth-child(2) {
            display: flex;
            align-items: center;
        }

        #DataTables_Table_0_info {
            float: left;
        }

        .table-scroll-top {
            display: none;
        }

        .table-scroll-top div {
            height: 1px;
        }

        /* Dropdown fixes for GST menu: closed by default, vertical list, proper alignment */
        .wordset .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            left: auto;
            flex-direction: column;
            z-index: 2000;
        }

        .wordset .dropdown-menu.show {
            display: block;
        }

        .wordset .dropdown-menu li {
            display: block;
            float: none;
        }

        .wordset .dropdown-menu .dropdown-item {
            display: block;
            /* white-space: nowrap; */
        }

        .total_expense {
            font-weight: 600;
            color: #1b2850;
            border: 1px solid #1b2850;
        }

        /* Mobile toggle button styles */
        .mobile-toggle-btn-table {
            background: #ff9f43;
            color: white;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            min-width: 32px;
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s ease;
            padding: 0;
            margin: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .mobile-toggle-btn-table:hover {
            background: #ff8c2e;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .mobile-toggle-btn-table.minus {
            background: #dc3545;
        }

        .mobile-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        /* Ensure Details column is centered */
        .datanew th.text-center,
        .datanew td.text-center {
            text-align: center !important;
        }

        .order-details-row {
            display: none;
        }

        .order-details-row.show {
            display: table-row;
        }

        .order-details-content {
            padding: 15px;
            background: #f8f9fa;
            border-top: 2px solid #ff9f43;
        }

        .order-details-content .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .order-details-content .detail-item:last-child {
            border-bottom: none;
        }

        .order-details-content .detail-label {
            font-weight: 600;
            color: #495057;
        }

        .order-details-content .detail-value {
            color: #212529;
        }

        /* Product Name cell - allow wrapping */
        .datanew td.productimgname {
            white-space: normal !important;
        }

        .datanew td.productimgname>div {
            flex-wrap: wrap;
        }

        .datanew td.productimgname a {
            /* flex-wrap: wrap; */
            min-width: 0;
            /* allow shrinking */
        }

        .datanew td.productimgname span {
            white-space: normal !important;
            word-wrap: break-word;
            min-width: 0;
            flex: 1 1 auto;
        }


        /* Responsive CSS for table columns */
        @media screen and (max-width: 575.98px) {

            /* Extra small devices - show only checkbox, product name, and details */
            .datanew thead th:nth-child(4),
            .datanew thead th:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew thead th:nth-child(7),
            .datanew thead th:nth-child(8),
            .datanew tbody td:nth-child(4),
            .datanew tbody td:nth-child(5),
            .datanew tbody td:nth-child(6),
            .datanew tbody td:nth-child(7),
            .datanew tbody td:nth-child(8) {
                display: none;
            }

            .datanew thead th:nth-child(2),
            .datanew tbody td:nth-child(2) {
                width: auto;
                margin-top: 11px;
            }

            .table-responsive {
                overflow-x: visible !important;
            }
        }

        @media screen and (min-width: 576px) and (max-width: 767.98px) {

            /* Small devices - show checkbox, product name, sold amount, sold qty, and details */
            .datanew thead th:nth-child(4),
            .datanew thead th:nth-child(5),
            .datanew thead th:nth-child(6),
            .datanew tbody td:nth-child(4),
            .datanew tbody td:nth-child(5),
            .datanew tbody td:nth-child(6) {
                display: none;
            }

            .table-responsive {
                overflow-x: visible !important;
            }
        }

        @media screen and (min-width: 768px) and (max-width: 1024px) {

            /* Medium/Tablet - hide details column */
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3) {
                display: none;
            }

            .table-responsive {
                overflow-x: visible !important;
            }
        }

        @media screen and (min-width: 1025px) {

            /* Large/Desktop - hide details column */
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3) {
                display: none;
            }
        }


        @media screen and (max-width: 767.98px) {

            .page-header {
                display: flex;
                justify-content: space-between;
                /* 🔥 main fix */
                align-items: center;
                flex-direction: row;
                flex-wrap: nowrap;
                gap: 10px;
            }

            .page-title h4 {
                margin: 0;
                /* font-size: 18px; */
                white-space: nowrap;
            }

            .total-expense-summary {
                width: auto;
                /* ❌ remove 100% */
                margin-top: 0;
                /* ❌ remove spacing */
            }

            .total-expense-summary h5 {
                margin: 0 !important;
                width: auto;
                /* ❌ remove 70% */
                text-align: right;
                /* align amount right */
                white-space: nowrap;
            }
        }

        /* Responsive chart card */
        @media screen and (max-width: 767.98px) {
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            #sales-filter-summary {
                margin-top: 10px;
                width: 100%;
                white-space: normal !important;
            }

            .card-body {
                /* height: 300px !important; */
                padding: 15px !important;
            }

            #salesProductChart {
                max-height: 280px !important;
            }
        }

        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .card-body {
                height: 320px !important;
            }

            #salesProductChart {
                max-height: 300px !important;
            }
        }

        /* Responsive filters */
        @media screen and (max-width: 767.98px) {
            .table-top .search-set {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                justify-content: flex-start !important;
                width: 100%;
            }

            .search-set .form-select-sm {
                flex: 1;
                min-width: 120px;
            }

            .wordset {
                width: 100%;
                margin-top: 10px !important;
            }

            .wordset ul {
                width: 100%;
            }

            .wordset ul li {
                width: 100%;
            }

            .wordset ul li a button {
                width: 100%;
            }
        }

        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .search-set {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
        }

        @media screen and (max-width: 767px) {
            .table-scroll-top {
                display: none !important;
            }

            .table-responsive {
                overflow-x: visible !important;
                overflow-y: visible !important;
            }

            .datanew {
                width: 100% !important;
                table-layout: auto !important;
            }

            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: 1rem !important;
            }

            .dataTables_filter {
                text-align: left !important;
            }
        }

        .table tbody tr td {
            white-space: normal !important;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sales Report</h4>

            </div>
            <div class="total-expense-summary">
                <h5 id="total-expense-amount" class="mb-0 px-3 py-1 mx-3 rounded bg-light total_expense">
                    <!-- Gets updated by JS -->Total:
                </h5>
            </div>

        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between flex-wrap">
                <h6 class="fw-bold mb-0 text-secondary d-flex align-items-center">
                    <i class="fa-solid fa-chart-column me-2 text-primary"></i>
                    Top Sold Products
                </h6>

                <!-- 🔹 Summary inline with title -->
                <div id="sales-filter-summary"
                    style="display:none;
                   background: #f9fafb;
                   border: 1px solid #e5e7eb;
                   border-radius: 6px;
                   padding: 6px 10px;
                   font-size: 14px;
                   font-weight: 500;
                   color: #374151;
                   display: flex;
                   align-items: center;
                   justify-content: center;
                   gap: 6px;
                   white-space: nowrap;
                   transition: all 0.3s ease;">
                </div>
            </div>

            <div class="card-body d-flex align-items-center justify-content-center" style="height: 350px;">
                <canvas id="salesProductChart" style="max-height: 320px; width: 100%;"></canvas>
            </div>
        </div>

        <div class="card">
            <div class="card-body" style="overflow:auto;">

                <div class="table-top">
                    <div class="search-set">
                        <select id="date-filter" class="form-select form-select-sm">
                            <option value="">All Time</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_6_months">Last 6 Months</option>
                            <option value="this_year">This Year</option>
                            <option value="previous_year">Previous Year</option>
                        </select>

                        <select id="month-filter" class="form-select form-select-sm"
                            style="margin-right:8px; margin-left: 8px;">
                            <option value="">All Months</option>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}">{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                            @endfor
                        </select>

                        <select id="year-filter" class="form-select form-select-sm" style="margin-right:8px;">
                            <option value="">All Years</option>
                            @for ($y = date('Y'); $y >= date('Y') - 10; $y--)
                                <option value="{{ $y }}">{{ $y }}</option>
                            @endfor
                        </select>

                        <select id="vendor-filter" class="form-select form-select-sm"
                            style="margin-right:8px; min-width: 180px;">
                            <option value="">All Customers</option>
                            @isset($customers)
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            @endisset
                        </select>

                        <select id="category-filter" class="form-select form-select-sm"
                            style="margin-right:8px; min-width: 180px;">
                            <option value="">All Categories</option>
                            @isset($categories)
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            @endisset
                        </select>

                    </div>
                    <div class="wordset">
                        <ul>
                            <li>
                                <a id="generate-pdf" href="javascript:void(0);" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Download PDF">

                                    <button class="btn btn-primary btn-sm"><i class="fa-solid fa-file-pdf"></i> View
                                        PDF</button>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                {{-- <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img"> --}}
                            </a>
                            <input type="text" id="search-input" class="form-control" placeholder="Search..." style="height:30px ">
                        </div>
                    </div>
                </div>
                {{-- <div class="table-scroll-top">
                    <div></div>
                </div> --}}
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>
                                    <label class="checkboxs">
                                        <input type="checkbox" id="select-all">
                                        <span class="checkmarks"></span>
                                    </label>
                                </th>
                                <th>Product Name</th>
                                <th class="text-center">Details</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Sold amount</th>
                                <th>Sold qty</th>
                            </tr>
                        </thead>
                        <tbody>


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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const ImagePath = "{{ env('ImagePath') }}";
            const salesReportApiUrl = "/api/order-report";

            // Pagination state
            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let isSelectingAllProducts = false;
            let isAllPagesSelected = false;

            // Global data map for expandable rows
            window.salesReportDataMap = {};

            function getSalesReportFilters(overrides = {}) {
                return {
                    filter: $('#date-filter').val(),
                    month: $('#month-filter').val(),
                    year: $('#year-filter').val(),
                    customer_id: $('#vendor-filter').val(),
                    category_id: $('#category-filter').val(),
                    search: searchQuery,
                    selectedSubAdminId: selectedSubAdminId,
                    ...overrides
                };
            }

            function fetchAllOrderReportItems(filters = {}) {
                const requestFilters = getSalesReportFilters({
                    per_page: 10,
                    page: 1,
                    ...filters
                });
                const allItems = [];

                function fetchPage(page) {
                    requestFilters.page = page;

                    return $.ajax({
                        url: salesReportApiUrl,
                        type: "GET",
                        data: requestFilters,
                        headers: {
                            "Authorization": "Bearer " + authToken
                        }
                    }).then(function(response) {
                        if (!response.status) {
                            return response;
                        }

                        allItems.push(...(response.data || []));

                        const totalPages = response.pagination?.last_page || 1;
                        if (page < totalPages) {
                            return fetchPage(page + 1);
                        }

                        response.all_items = allItems;
                        return response;
                    });
                }

                return fetchPage(1);
            }

            function syncVisiblePageSelection() {
                $('input[name="item_ids[]"]').each(function() {
                    const id = String($(this).val());
                    $(this).prop('checked', allSelectedIds.includes(id));
                });

                const visibleCheckboxes = $('input[name="item_ids[]"]');
                const checkedVisibleCheckboxes = $('input[name="item_ids[]"]:checked');
                $('#select-all').prop(
                    'checked',
                    visibleCheckboxes.length > 0 && visibleCheckboxes.length === checkedVisibleCheckboxes.length
                );
            }

            // 🔹 Initial Load
            fetchOrderReport(currentPage);
            loadSalesProductChart();

            // 🔹 Handle search input
            $('#search-input').on('keyup', function() {
                searchQuery = $(this).val();
                fetchOrderReport(1);
                loadSalesProductChart();
            });


            // 🔹 Trigger filters
            $('#date-filter, #month-filter, #year-filter, #vendor-filter, #category-filter').on('change',
                function() {
                    fetchOrderReport(1);
                    loadSalesProductChart();
                });

            // Handle page number clicks
            $(document).on('click', '#pagination-numbers .page-link', function(e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    fetchOrderReport(page);
                }
            });

            // Handle per-page change
            $('#per-page-select').on('change', function() {
                perPage = $(this).val();
                fetchOrderReport(1);
            });

            /** ----------------------------------------------
             * 📱 Mobile Expandable Row Functions
             * ---------------------------------------------- */
            function buildSalesReportExpandableRowContent(item) {
                return `
                    <div class="order-details-content">
                        <div class="detail-item">
                            <span class="detail-label">SKU:</span>
                            <span class="detail-value">${item.SKU || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Category:</span>
                            <span class="detail-value">${item.category || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Brand:</span>
                            <span class="detail-value">${item.brand || 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Sold Amount:</span>
                            <span class="detail-value" style="color: #28a745; font-weight: 600;">${item.sold_amount || '₹0.00'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Sold Quantity:</span>
                            <span class="detail-value" style="color: #ff9f43; font-weight: 600;">${item.sold_qty || '0'}</span>
                        </div>
                    </div>
                `;
            }

            window.toggleSalesReportRowDetails = function(productId) {
                // Only work on mobile/tablet (≤1024px)
                if ($(window).width() > 1024) {
                    return;
                }

                const row = $(`tr[data-product-id="${productId}"]`);
                if (row.length === 0) return;

                const detailsRow = row.next(`tr.order-details-row[data-product-details-id="${productId}"]`);
                const toggleBtn = row.find('.mobile-toggle-btn-table');

                if (detailsRow.length === 0) {
                    // Create expandable row if it doesn't exist
                    const item = window.salesReportDataMap[productId];
                    if (!item) return;

                    const expandableContent = buildSalesReportExpandableRowContent(item);
                    const newRow = $(`
                        <tr class="order-details-row show" data-product-details-id="${productId}">
                            <td colspan="8">${expandableContent}</td>
                        </tr>
                    `);
                    row.after(newRow);
                    toggleBtn.addClass('minus').html('-');
                } else {
                    // Toggle existing row
                    if (detailsRow.hasClass('show')) {
                        detailsRow.removeClass('show');
                        toggleBtn.removeClass('minus').html('+');
                    } else {
                        detailsRow.addClass('show');
                        toggleBtn.addClass('minus').html('-');
                    }
                }
            };

            window.addSalesReportExpandableRows = function(dt) {
                // Remove existing expandable rows
                $('.order-details-row').remove();

                // Only add expandable rows on mobile/tablet (≤1024px)
                if ($(window).width() <= 1024) {
                    dt.rows().every(function() {
                        const row = $(this.node());
                        const productId = row.attr('data-product-id');
                        if (productId && window.salesReportDataMap[productId]) {
                            const item = window.salesReportDataMap[productId];
                            const expandableContent = buildSalesReportExpandableRowContent(item);
                            const expandableRow = $(`
                                <tr class="order-details-row" data-product-details-id="${productId}">
                                    <td colspan="8">${expandableContent}</td>
                                </tr>
                            `);
                            row.after(expandableRow);
                        }
                    });
                } else {
                    // On desktop, reset all toggle buttons to +
                    $('.mobile-toggle-btn-table').removeClass('minus').html('+');
                }
            };

            // Resize handler with debouncing
            let resizeTimer;
            window.handleSalesReportResize = function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();

                    // Force browser reflow
                    const body = document.body;
                    body.style.display = 'none';
                    body.offsetHeight; // Trigger reflow
                    body.style.display = '';

                    // Get DataTable instance
                    const table = $('.datanew').DataTable();
                    if (table) {
                        // If switching to desktop (width > 1024px), remove all expandable rows
                        if (currentWidth > 1024) {
                            $('.order-details-row').remove();
                            $('.mobile-toggle-btn-table').removeClass('minus').html('+');
                        }

                        // Adjust columns
                        table.columns.adjust().draw(false);

                        // Re-add expandable rows if needed (only on mobile/tablet)
                        if (currentWidth <= 1024) {
                            setTimeout(function() {
                                window.addSalesReportExpandableRows(table);
                            }, 100);
                        }
                    }

                    // Resize chart if it exists
                    if (window.salesChart) {
                        window.salesChart.resize();
                    }
                }, 250);
            };

            // Add resize event listener
            $(window).on('resize', window.handleSalesReportResize);

            // Add matchMedia listeners for breakpoints
            const mediaQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            mediaQueries.forEach(function(mq) {
                mq.addListener(function() {
                    window.handleSalesReportResize();
                });
            });

            /** ----------------------------------------------
             * 📋 Fetch Order Report Table
             * ---------------------------------------------- */
    //         function fetchOrderReport(page = 1) {
    //             const filters = {
    //                 page: page,                // ✅ important
    // per_page: perPage,         // ✅ important
    // search: searchQuery,
    //                 filter: $('#date-filter').val(),
    //                 month: $('#month-filter').val(),
    //                 year: $('#year-filter').val(),
    //                 customer_id: $('#vendor-filter').val(),
    //                 category_id: $('#category-filter').val(),
    //                 selectedSubAdminId: selectedSubAdminId
    //             };

    //             $.ajax({
    //                 url: "/api/order-report",
    //                 type: "GET",
    //                 data: filters,
    //                 headers: {
    //                     "Authorization": "Bearer " + authToken
    //                 },
    //                 success: function(response) {
    //                     // console.log(response);

    //                     if (!response.status) return alert("No orders found.");
    //                     updateSalesSummary(response);

    //                     // Clear previous data map
    //                     window.salesReportDataMap = {};

    //                     let tbody = "";
    //                     $.each(response.data, function(index, item) {
    //                         // Store item data for expandable rows
    //                         window.salesReportDataMap[item.id] = item;

    //                         tbody += `
    //                     <tr data-product-id="${item.id}">
    //                         <td>
    //                             <label class="checkboxs">
    //                                 <input type="checkbox" name="item_ids[]" value="${item.id}">
    //                                 <span class="checkmarks"></span>
    //                             </label>
    //                         </td>
    //                         <td class="productimgname">
    //                             <div style="display: flex; align-items: center; gap: 10px;">
    //                                 <a href="/product-view/${item.product_id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
    //                                     <img src="${ImagePath}${item.image}" alt="${item.name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px;">
    //                                     <span style="margin-left: 10px;">${item.name}</span>
    //                                 </a>
    //                             </div>
    //                         </td>
    //                         <td class="text-center">
    //                             <button class="mobile-toggle-btn-table" onclick="window.toggleSalesReportRowDetails('${item.id}')">+</button>
    //                         </td>
    //                         <td>${item.SKU}</td>
    //                         <td>${item.category}</td>
    //                         <td>${item.brand ?? 'N/A'}</td>
    //                         <td>${item.sold_amount}</td>
    //                         <td>${item.sold_qty}</td>
    //                     </tr>
    //                 `;
    //                     });

    //                     let table = $('.datanew').DataTable();
    //                     table.destroy();
    //                     $(".datanew tbody").html(tbody);
    //                     let dt = $('.datanew').DataTable({
    //                         responsive: true
    //                     });

    //                     // Add expandable rows after table draws
    //                     dt.on('draw', function() {
    //                         window.addSalesReportExpandableRows(dt);
    //                     });

    //                     // Initial add of expandable rows
    //                     setTimeout(function() {
    //                         window.addSalesReportExpandableRows(dt);
    //                         // Trigger resize handler to ensure proper layout
    //                         window.handleSalesReportResize();
    //                     }, 100);
    //                 },
    //                 error: function() {
    //                     alert("Failed to load order report.");
    //                 }
    //             });
    //         }

    function fetchOrderReport(page = 1) {

    const filters = getSalesReportFilters({
        page: page,
        per_page: perPage
    });

            $.ajax({
                url: salesReportApiUrl,
                type: "GET",
                data: filters,
        headers: {
            "Authorization": "Bearer " + authToken
        },
        success: function(response) {

            if (!response.status) return alert("No orders found.");

            updateSalesSummary(response);

            // ✅ UPDATE PAGINATION STATE
            currentPage = response.pagination.current_page;
            lastPage = response.pagination.last_page;

            // ✅ UPDATE PAGINATION TEXT
            $('#pagination-from').text(response.pagination.from || 0);
            $('#pagination-to').text(response.pagination.to || 0);
            $('#pagination-total').text(response.pagination.total);

            // ✅ BUILD PAGINATION BUTTONS
            let paginationHtml = "";

// Only page numbers
for (let i = 1; i <= lastPage; i++) {
    paginationHtml += `
        <li class="page-item ${i == currentPage ? 'active' : ''}">
            <a class="page-link" href="#" data-page="${i}">${i}</a>
        </li>`;
}

$('#pagination-numbers').html(paginationHtml);

            // Clear previous data map
            window.salesReportDataMap = {};

            let tbody = "";
            $.each(response.data, function(index, item) {
                const itemId = String(item.id);

                window.salesReportDataMap[itemId] = item;

                tbody += `
                <tr data-product-id="${itemId}">
                    <td>
                        <label class="checkboxs">
                            <input type="checkbox" name="item_ids[]" value="${itemId}" ${allSelectedIds.includes(itemId) ? 'checked' : ''}>
                            <span class="checkmarks"></span>
                        </label>
                    </td>
                    <td class="productimgname" >
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <a href="/product-view/${item.product_id}" style="display: flex; align-items: center; text-decoration: none; color: inherit;">

                                <span style="margin-left: 10px;">${item.name}</span>
                            </a>
                        </div>
                    </td>
                    <td class="text-center">
                        <button class="mobile-toggle-btn-table" onclick="window.toggleSalesReportRowDetails('${itemId}')">+</button>
                    </td>
                    <td>${item.SKU}</td>
                    <td>${item.category}</td>
                    <td>${item.brand ?? 'N/A'}</td>
                    <td>${item.sold_amount}</td>
                    <td>${item.sold_qty}</td>
                </tr>`;
            });

            // ✅ DESTROY OLD TABLE
            if ($.fn.DataTable.isDataTable('.datanew')) {
                $('.datanew').DataTable().destroy();
            }

            $(".datanew tbody").html(tbody);

            // ✅ RE-INIT WITHOUT PAGINATION
            let dt = $('.datanew').DataTable({
                responsive: true,
                dom: 't',
                paging: false,     // 🔥 IMPORTANT
                info: false,
                searching: false
            });

            dt.on('draw', function() {
                window.addSalesReportExpandableRows(dt);
            });

            setTimeout(function() {
                window.addSalesReportExpandableRows(dt);
                window.handleSalesReportResize();
                syncVisiblePageSelection();
                $('.dataTables_filter').remove();
            }, 100);
        },
        error: function() {
            alert("Failed to load order report.");
        }
    });
}
            let allSelectedIds = []; // Global array to store selected IDs across pages

            // 🔹 Handle Select-All checkbox
            $(document).on('change', '#select-all', function() {
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    if (isSelectingAllProducts) {
                        return;
                    }

                    isSelectingAllProducts = true;
                    $('#select-all').prop('disabled', true);

                    fetchAllOrderReportItems()
                        .done(function(response) {
                            if (!response.status) {
                                allSelectedIds = [];
                                isAllPagesSelected = false;
                                $('#select-all').prop('checked', false);
                                return;
                            }

                            allSelectedIds = (response.all_items || []).map(function(item) {
                                return String(item.id);
                            });
                            allSelectedIds = [...new Set(allSelectedIds)];
                            isAllPagesSelected = true;
                            syncVisiblePageSelection();
                        })
                        .fail(function() {
                            allSelectedIds = [];
                            isAllPagesSelected = false;
                            $('#select-all').prop('checked', false);
                        })
                        .always(function() {
                            isSelectingAllProducts = false;
                            $('#select-all').prop('disabled', false);
                        });
                } else {
                    allSelectedIds = [];
                    isAllPagesSelected = false;
                    $('input[name="item_ids[]"]').prop('checked', false);
                }
            });

            // 🔹 Handle individual checkbox changes
            $(document).on('change', 'input[name="item_ids[]"]', function() {
                const id = String($(this).val());

                if ($(this).is(':checked')) {
                    if (!allSelectedIds.includes(id)) allSelectedIds.push(id);
                } else {
                    allSelectedIds = allSelectedIds.filter(x => x !== id);
                    isAllPagesSelected = false;
                }

                syncVisiblePageSelection();
            });


            /** ----------------------------------------------
             * 📊 Load Sales Product Chart
             * ---------------------------------------------- */
            function loadSalesProductChart() {
                const chartContainer = $("#salesProductChart").parent();

                fetchAllOrderReportItems()
                    .done(function(response) {
                        $("#noSalesMessage").remove();
                        updateSalesSummary(response);
                        const chartItems = response.all_items || [];

                        if (!response.status || !chartItems.length) {
                            if (window.salesChart) window.salesChart.destroy();
                            chartContainer.append(`
                                <div id="noSalesMessage"
                                    style="
                                                display: flex;
                                                align-items: center;
                                                justify-content: center;
                                                height: 100%;
                                                width: 100%;
                                                color: #6b7280;
                                                font-size: 16px;
                                                text-align: center;
                                                margin-left: -1212px
                                            ">
                                    No sales found.
                                </div>
                            `);
                            return;
                        }

                        // ✅ Aggregate by product
                        const productTotals = {};
                        const productAmounts = {};
                        chartItems.forEach(item => {
                            const name = item.name || "Unknown";
                            const qty = parseFloat(item.sold_qty);
                            const amount = parseFloat(String(item.sold_amount).replace(/[₹,]/g, "")) ||
                                0;
                            productTotals[name] = (productTotals[name] || 0) + qty;
                            productAmounts[name] = (productAmounts[name] || 0) + amount;
                        });

                        const sortedEntries = Object.entries(productTotals)
                            .sort((a, b) => b[1] - a[1]);

                        const labels = sortedEntries.map(entry => entry[0]);
                        const values = sortedEntries.map(entry => entry[1]);

                        if (window.salesChart) window.salesChart.destroy();

                        const ctx = document.getElementById("salesProductChart").getContext("2d");
                        window.salesChart = new Chart(ctx, {
                            type: "bar",
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: "Total Quantity Sold",
                                    data: values,
                                    backgroundColor: [
                                        "#4e73df", "#1cc88a", "#36b9cc",
                                        "#f6c23e", "#e74a3b", "#858796",
                                        "#5a5c69", "#20c997", "#6610f2", "#fd7e14"
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    barThickness: 20, // 🔹 Consistent bar width
                                    maxBarThickness: 30
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                resizeDelay: 0,
                                plugins: {
                                    legend: {
                                        display: false
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label;
                                                const qty = context.formattedValue;
                                                const amt = productAmounts[label] || 0;
                                                return `${label}: ${qty} pcs | ₹${amt.toFixed(2)}`;
                                            }
                                        }
                                    }
                                },
                                scales: {
                                    x: {
                                        grid: {
                                            display: false
                                        }
                                    },
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });

                        // Add resize listener for chart
                        $(window).on('resize', function() {
                            if (window.salesChart) {
                                window.salesChart.resize();
                            }
                        });
                    })
                    .fail(function(xhr) {
                        // console.error("Chart load failed:", xhr);
                    });
            }

            $('#generate-pdf').on('click', function() {
                if (allSelectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one product to generate the PDF.',
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const url = `/sales/report/${allSelectedIds.join(',')}`;
                window.open(url, '_blank');
            });


            function updateSalesSummary(response) {
                const customerText = $('#vendor-filter option:selected').text();
                const categoryText = $('#category-filter option:selected').text();
                const monthText = $('#month-filter option:selected').text();
                const yearText = $('#year-filter option:selected').text();
                const dateText = $('#date-filter option:selected').text();

                const hasCustomer = $('#vendor-filter').val() !== "";
                const hasCategory = $('#category-filter').val() !== "";
                const hasMonth = $('#month-filter').val() !== "";
                const hasYear = $('#year-filter').val() !== "";
                const hasDate = $('#date-filter').val() !== "";

                // ✅ Calculate total sold amount
                let totalAmount = parseFloat(String(response.total_sold_amount || 0).replace(/[₹,]/g, '')) || 0;

                // ✅ Update total on top
                $('#total-expense-amount').html(
                    `Total: <span style="color:#ff9f43;">₹${totalAmount.toLocaleString('en-IN', { minimumFractionDigits: 2 })}</span>`
                );

                // Hide summary when no filters selected
                if (!hasCustomer && !hasCategory && !hasMonth && !hasYear && !hasDate) {
                    $('#sales-filter-summary').fadeOut(200);
                    return;
                }

                // Build readable parts
                let parts = [];
                if (hasCustomer) parts.push(`<strong>${customerText}</strong>`);
                if (hasCategory) parts.push(`in <strong>${categoryText}</strong>`);
                if (hasMonth || hasYear) {
                    let timeText = [];
                    if (hasMonth) timeText.push(monthText);
                    if (hasYear) timeText.push(yearText);
                    parts.push(`at <strong>${timeText.join(' ')}</strong>`);
                }
                if (hasDate && dateText !== "All Time") parts.push(`(${dateText})`);

                const message = `Showing sales for ${parts.join(' ')}`;

                // ✅ Show summary beautifully
                $('#sales-filter-summary')
                    .html(`<span>${message}</span>`)
                    .hide()
                    .fadeIn(300);
            }


        });
    </script>
@endpush
