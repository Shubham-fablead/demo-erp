@extends('layout.app')

@section('title', 'Row Material Inventory List')

@section('content')
<style>
    .sorting_1 {
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
    }

    .table-scroll-top {
        overflow-x: auto;
        overflow-y: hidden;
        height: 20px;
        width: 100%;
        margin-bottom: 5px;
        display: none;
    }

    /* Responsive breakpoints for all screen sizes */

    /* Extra small devices (phones, less than 576px) */
    @media screen and (max-width: 575.98px) {
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

        /* Show only Type and Details - hide Current Stock, Quantity, Create By, Date */
        .table thead th:nth-child(2),
        .table tbody td:nth-child(2),
        .table thead th:nth-child(3),
        .table tbody td:nth-child(3),
        .table thead th:nth-child(4),
        .table tbody td:nth-child(4),
        .table thead th:nth-child(5),
        .table tbody td:nth-child(5) {
            display: none;
        }

        /* Center Details column (6th column) */
        .table thead th:nth-child(6),
        .table tbody td:nth-child(6) {
            text-align: center;
            width: 60px;
            min-width: 60px;
        }

        .history-toggle-btn-table {
            margin: 0 auto;
            display: block;
        }
    }

    /* Small devices (landscape phones, 576px and up) */
    @media screen and (min-width: 576px) and (max-width: 767.98px) {
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

        /* Show Type, Current Stock, Quantity, Details - hide Create By and Date */
        .table thead th:nth-child(4),
        .table tbody td:nth-child(4),
        .table thead th:nth-child(5),
        .table tbody td:nth-child(5) {
            display: none;
        }

        /* Center Details column (6th column) */
        .table thead th:nth-child(6),
        .table tbody td:nth-child(6) {
            text-align: center;
            width: 60px;
            min-width: 60px;
        }

        .history-toggle-btn-table {
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

        /* Show Details column on tablets - keep it visible */
        /* Hide Create By and Date */
        .table thead th:nth-child(4),
        .table tbody td:nth-child(4),
        .table thead th:nth-child(5),
        .table tbody td:nth-child(5) {
            display: none;
        }

        /* Center Details column (6th column) */
        .table thead th:nth-child(6),
        .table tbody td:nth-child(6) {
            text-align: center;
            width: 60px;
            min-width: 60px;
        }

        .history-toggle-btn-table {
            margin: 0 auto;
            display: block;
        }

        /* Show expandable rows on tablets */
        .history-details-row {
            display: none;
        }

        .history-details-row.show {
            display: table-row;
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

        /* Hide Details column (6th column) on 1025px and above */
        .table thead th:nth-child(6),
        .table tbody td:nth-child(6) {
            display: none;
        }

        /* Hide expandable rows on larger screens */
        .history-details-row {
            display: none !important;
        }
    }

    /* Expandable row details - available for all screen sizes */
    .history-details-row {
        display: none;
    }

    .history-details-row.show {
        display: table-row;
    }

    /* Expandable content styles */
    .history-details-content {
        padding: 15px;
        background: #fff;
        border-top: 2px solid #e0e0e0;
    }

    .history-details-list {
        margin-bottom: 15px;
    }

    .history-detail-row-simple {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .history-detail-row-simple:last-of-type {
        border-bottom: none;
    }

    .history-detail-label-simple {
        font-weight: 600;
        color: #595b5d;
        font-size: 14px;
    }

    .history-detail-value-simple {
        color: #1b2850;
        font-size: 14px;
        text-align: right;
    }

    /* Toggle button styles */
    .history-toggle-btn-table {
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

    .history-toggle-btn-table:hover {
        background: #ff8c2e;
    }

    .history-toggle-btn-table.minus {
        background: #dc3545;
    }

    .history-toggle-btn-table.minus:hover {
        background: #c82333;
    }

    .modal-content {
        width: 80% !important;
    }

      /* Custom Pagination Styling */
        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            /* Dark gray for other pages */
            color: #fff;
            border: none;
            margin: 0 4px;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
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
            /* padding-left: 35px !important; */
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
@endif

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Row Material Inventory</h4>
        </div>
        <div class="page-btn">
            @if (app('hasPermission')(17, 'view'))
            <a href="{{ route('row_material.inventory') }}" class="btn btn-sm btn-added">Back</a>
            @endif
        </div>
    </div>


    <div class="card">
        <div class="card-body">
            {{-- <div class="table-scroll-top">
                <div></div>
            </div> --}}

            <div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="search-input position-relative">
            <a class="btn btn-searchset position-absolute" >
                {{-- <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}" alt="img"> --}}
            </a>
            <input type="text" id="history-search-input" class="form-control form-control-sm" placeholder="Search..." >
        </div>
    </div>
</div>
            <div class="table-responsive">
                <table class="table table-bordered" id="history-table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Current Stock</th>
                            <th>Quantity</th>
                            <th>Create By</th>
                            <th>Date</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3" style="display: none;">
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
        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
    </nav>
</div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    //   // Global variables
    var historyTable;
    let currentPage = 1;
    let lastPage = 1;
    let perPage = 10;
    let searchQuery = '';

    // Helper function to build expandable row content
    function buildHistoryExpandableRowContent(item) {
        return `
                <td colspan="6" class="history-details-content">
                    <div class="history-details-list">
                        <div class="history-detail-row-simple">
                            <span class="history-detail-label-simple">Quantity:</span>
                            <span class="history-detail-value-simple">${item.signedQty || 'N/A'}</span>
                        </div>
                        <div class="history-detail-row-simple">
                            <span class="history-detail-label-simple">Initial Stock:</span>
                            <span class="history-detail-value-simple">${item.initialStock || 'N/A'}</span>
                        </div>
                        <div class="history-detail-row-simple">
                            <span class="history-detail-label-simple">Current Stock:</span>
                            <span class="history-detail-value-simple">${item.currentStock || 'N/A'}</span>
                        </div>
                        <div class="history-detail-row-simple">
                            <span class="history-detail-label-simple">Create By:</span>
                            <span class="history-detail-value-simple">${item.createdBy || 'N/A'}</span>
                        </div>
                        <div class="history-detail-row-simple">
                            <span class="history-detail-label-simple">Date:</span>
                            <span class="history-detail-value-simple">${item.date || 'N/A'}</span>
                        </div>
                    </div>
                 </td>
            `;
    }

    // Toggle function for table rows - must be global
    window.toggleHistoryRowDetails = function(itemIndex) {
        const btn = $(`.history-toggle-btn-table[data-item-index="${itemIndex}"]`);
        if (btn.length === 0) return;

        const row = btn.closest('tr');
        let detailsRow = row.next(`tr.history-details-row[data-item-index="${itemIndex}"]`);
        const icon = btn.find('.toggle-icon');

        if (detailsRow.length === 0) {
            const itemData = window.historyDataMap && window.historyDataMap[itemIndex];
            if (itemData) {
                detailsRow = $('<tr>')
                    .addClass('history-details-row')
                    .attr('data-item-index', itemIndex)
                    .html(buildHistoryExpandableRowContent(itemData));
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
    }

    // Function to add expandable rows - must be global
    window.addHistoryExpandableRows = function(dt) {
        if (!dt) return;
        const currentWidth = $(window).width();
        const isMobileOrTablet = currentWidth <= 1024;
        if (!isMobileOrTablet) {
            $('tr.history-details-row').remove();
            return;
        }
        dt.rows().every(function() {
            const row = this.node();
            const toggleBtn = $(row).find('.history-toggle-btn-table');
            if (toggleBtn.length > 0) {
                const itemIndex = toggleBtn.data('item-index');
                const itemData = window.historyDataMap && window.historyDataMap[itemIndex];
                if (itemData && !$(row).next('tr.history-details-row[data-item-index="' + itemIndex + '"]').length) {
                    const expandableRow = $('<tr>')
                        .addClass('history-details-row')
                        .attr('data-item-index', itemIndex)
                        .html(buildHistoryExpandableRowContent(itemData));
                    $(row).after(expandableRow);
                }
            }
        });
    };

    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");
        let url = window.location.href;
        let productId = url.substring(url.lastIndexOf('/') + 1);

        // Initialize DataTable (display only)
        historyTable = $('#history-table').DataTable({
            responsive: true,
            searching: false,
            paging: false,
            info: false,
            lengthChange: false,
            ordering: false,
            autoWidth: false,
            dom: 't'
        });

        // Fetch history with pagination
        function fetchHistory(page = 1) {
            let apiUrl = `/api/row-material/getQuantityHistory_inventory/${productId}?page=${page}&per_page=${perPage}`;
            if (searchQuery) {
                apiUrl += `&search=${encodeURIComponent(searchQuery)}`;
            }

            $.ajax({
                url: apiUrl,
                type: "GET",
                headers: { "Authorization": "Bearer " + authToken },
                success: function(response) {
                    if (response.status) {
                        let history = response.history || [];
                        let pagination = response.pagination;

                        currentPage = pagination.current_page;
                        lastPage = pagination.last_page;
                        updatePaginationUI(pagination);

                        let tableBody = [];
                        if (!window.historyDataMap) window.historyDataMap = {};

                        $.each(history, function(index, item) {
                            let initialStock = item.initial_stock ?? '-';
                            let currentStock = item.current_stock ?? '-';
                            let type = item.type ?? 'System';
                            let createdBy = item.name ?? 'System';
                            let signedQty = item.quantity ?? 'N/A';
                            let date = item.date ?? '-';

                            // Store for expandable row
                            const historyData = {
                                signedQty: signedQty,
                                initialStock: initialStock,
                                currentStock: currentStock,
                                createdBy: createdBy,
                                date: date
                            };
                            // Use a unique index (page + index) to avoid conflicts across pages
                            let uniqueIndex = `${currentPage}_${index}`;
                            window.historyDataMap[uniqueIndex] = historyData;

                            let detailsColumn = `
                                <button class="history-toggle-btn-table" onclick="toggleHistoryRowDetails('${uniqueIndex}')" data-item-index="${uniqueIndex}">
                                    <span class="toggle-icon">+</span>
                                </button>
                            `;

                            tableBody.push([
                                type,
                                currentStock,
                                signedQty,
                                createdBy,
                                date,
                                detailsColumn
                            ]);
                        });

                        historyTable.clear().rows.add(tableBody).draw();
                        setTimeout(() => addHistoryExpandableRows(historyTable), 100);
                        $('.pagination-controls').show();
                    } else {
                        historyTable.clear().draw();
                        $('.pagination-controls').hide();
                    }
                },
                error: function() {
                    historyTable.clear().draw();
                    $('.pagination-controls').hide();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load history', confirmButtonColor: '#ff9f43' });
                }
            });
        }

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
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            for (let i = startPage; i <= endPage; i++) {
                paginationHtml += `
                    <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                    </li>
                `;
            }
            $('#pagination-numbers').html(paginationHtml);
        }

        // Search input
        $('#history-search-input').on('keyup', function() {
            searchQuery = $(this).val();
            fetchHistory(1);
        });

        // Per page change
        $('#per-page-select').on('change', function() {
            perPage = $(this).val();
            fetchHistory(1);
        });

        // Pagination clicks
        $(document).on('click', '#pagination-numbers .page-link', function(e) {
            e.preventDefault();
            let page = $(this).data('page');
            if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                fetchHistory(page);
            }
        });

        // Initial load
        fetchHistory(1);

        // Resize handler for responsive behavior
        let resizeTimer;
        let lastWidth = $(window).width();

        function forceHistoryCSSRecalculation() {
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

        function handleHistoryResize() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const currentWidth = $(window).width();
                lastWidth = currentWidth;

                // Force CSS recalculation
                forceHistoryCSSRecalculation();

                const table = document.getElementById('history-table');
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

                if (historyTable && table) {
                    $('tr.history-details-row').remove();

                    try {
                        historyTable.columns.adjust();
                        historyTable.draw(false);

                        setTimeout(function() {
                            historyTable.columns.adjust();
                            historyTable.draw(false);

                            setTimeout(function() {
                                if (window.addHistoryExpandableRows) {
                                    window.addHistoryExpandableRows(historyTable);
                                }
                                forceHistoryCSSRecalculation();
                                void table.offsetHeight;
                            }, 100);
                        }, 100);
                    } catch (e) {
                        // console.error('DataTables adjustment error:', e);
                        historyTable.draw(false);
                        setTimeout(function() {
                            if (window.addHistoryExpandableRows) {
                                window.addHistoryExpandableRows(historyTable);
                            }
                            forceHistoryCSSRecalculation();
                        }, 150);
                    }
                } else {
                    forceHistoryCSSRecalculation();
                }
            }, 50);
        }

        // Window resize handler
        $(window).off('resize.history').on('resize.history', handleHistoryResize);

        if (window.historyResizeHandler) {
            window.removeEventListener('resize', window.historyResizeHandler);
        }
        window.historyResizeHandler = handleHistoryResize;
        window.addEventListener('resize', window.historyResizeHandler, {
            passive: true
        });

        // Orientation change handler
        $(window).off('orientationchange.history').on('orientationchange.history', function() {
            setTimeout(function() {
                lastWidth = $(window).width();
                handleHistoryResize();
            }, 300);
        });

        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                lastWidth = $(window).width();
                handleHistoryResize();
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
                    setTimeout(handleHistoryResize, 100);
                });
            } else if (query.addListener) {
                query.addListener(function() {
                    setTimeout(handleHistoryResize, 100);
                });
            }
        });

        // Initial width set and call
        lastWidth = $(window).width();

        $(window).on('load', function() {
            setTimeout(function() {
                lastWidth = $(window).width();
                handleHistoryResize();
            }, 500);
        });

        setTimeout(function() {
            if (historyTable) {
                handleHistoryResize();
            }
        }, 1000);

        window.handleHistoryResize = handleHistoryResize;
    });
</script>
@endpush
