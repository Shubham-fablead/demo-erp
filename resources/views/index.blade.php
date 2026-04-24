@extends('layout.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $role = auth()->user()->role ?? '';
    @endphp
    {{-- <style>
        .color_box {
            display: block;
            width: 100%;
            text-decoration: none;
            color: inherit;
        }

        /* Dashboard widget styles remain the same */
        @media screen and (min-width: 768px) and (max-width: 1400px) {
            .dash-count .dash-imgs svg {
                width: 40px !important;
                height: 36px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 17px;
            }
        }

        @media screen and (max-width: 767px) {
            .dash-widget {
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-bottom: 15px !important;
                text-align: center;
                padding: 12px 20px !important;
            }

            .col-4 {
                display: flex;
                justify-content: center;
            }

            .dash-widgetcontent {
                margin-left: 0 !important;
                width: 68px !important;
            }

            .dash-widgetcontent h5 {
                margin-top: .5rem !important;
                font-size: 13px !important;
            }

            .dash-widgetcontent h6 {
                font-size: 10px !important;
            }

            .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

            .table-responsive {
                font-size: 12px !important;
            }

            /* NEW: Mobile table styles for sales/purchases */
            .dataview table thead th:nth-child(n+3),
            .dataview table tbody td:nth-child(n+3) {
                display: none !important;
            }

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: table-cell !important;
                text-align: center;
                vertical-align: middle;
                width: 50px;
            }

            .dataview .toggle-details i {
                font-size: 18px;
                transition: transform 0.3s ease;
            }

            /* Order ID column styling */
            .dataview table tbody td:first-child {
                display: flex !important;
                align-items: center !important;
                max-width: calc(100vw - 100px) !important;
            }

            .dataview .order-id {
                display: inline-block !important;
                max-width: calc(100% - 50px) !important;
                margin-left: 8px !important;
                font-size: 14px !important;
                word-break: break-word !important;
            }
        }

        /* Desktop: hide details toggle column */
        @media (min-width: 769px) {

            .dataview table thead th.details-column,
            .dataview table tbody td:nth-child(2) {
                display: none !important;
            }
        }

        /* Mobile collapse styles */
        .mobile-details-collapse {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-top: 10px;
            padding: 12px;
            background-color: #f8f9fa;
        }

        .mobile-details-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dashed #dee2e6;
        }

        .mobile-details-label {
            font-weight: 600;
            color: #495057;
        }

        .mobile-details-value {
            color: #212529;
            text-align: right;
        }
    </style> --}}
    <style>
    .color_box {
        display: block;
        width: 100%;
        text-decoration: none;
        color: inherit;
    }

    /* Dashboard widget styles */
    .dash-widget {
        transition: all 0.3s ease;
        border-radius: 8px !important;
    }

    .dash-widget:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    /* Tablet view (768px - 1400px) */
    @media screen and (min-width: 768px) and (max-width: 1400px) {
        .dash-widget {
            padding: 15px !important;
        }
        
        .dash-widgetimg span img {
            width: 40px !important;
            height: 36px !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
        }

        .dash-widgetcontent h5 {
            font-size: 10.5px !important;
            margin-bottom: 5px !important;
        }

        .dash-widget {
            min-height: 90px !important;
            margin: 0 0 15px !important;
        }
        
        /* Ensure proper alignment in tablet */
        .col-lg-4.col-sm-6.col-4 {
            padding-left: 7.5px !important;
            padding-right: 7.5px !important;
        }
    }

    /* Mobile view (max-width: 767px) */
    @media screen and (max-width: 767px) {

         .dash-count {
                min-height: 90px !important;
                margin: 0 0 15px !important;
                padding: 15px;
            }

            .dash-counts h5 {
                font-size: 10.5px !important;
            }

            .dash-count .dash-imgs svg {
                width: 37px !important;
                height: 37px !important;
            }

            .dash-count h4 {
                font-size: 19px !important;
            }

        /* Dashboard widgets container */
        .row {
            margin-left: -5px !important;
            margin-right: -5px !important;
            display: flex !important;
            flex-wrap: wrap !important;
        }
        
        /* First row (Purchase, Expense, Sales) - 3 in one row */
        .col-lg-4.col-sm-6.col-4:first-child,
        .col-lg-4.col-sm-6.col-4:nth-child(2),
        .col-lg-4.col-sm-6.col-4:nth-child(3) {
            width: 33.333% !important; /* Equal width for 3 widgets */
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }
        
        /* Remaining widgets (Invoice counts, Vendors, Customers) - 2 per row */
        .col-lg-4.col-sm-6.col-4:nth-child(4),
        .col-lg-4.col-sm-6.col-4:nth-child(5),
        .col-lg-4.col-sm-6.col-4:nth-child(6),
        .col-lg-4.col-sm-6.col-4:nth-child(7) {
            width: 50% !important; 
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 10px !important;
            float: left !important;
        }
        
        /* Clear floats */
        .row::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Dashboard widget styling */
        .dash-widget {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 12px 5px !important;
            margin-bottom: 0 !important;
            height: 110px !important; /* Slightly shorter for 3 in a row */
            justify-content: center !important;
        }

        .dash-widgetimg {
            margin-bottom: 6px !important;
        }

        .dash-widgetimg span img {
            width: 30px !important;
            height: 30px !important;
        }

        .dash-widgetcontent {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 3px !important;
        }

        .dash-widgetcontent h5 {
            margin-top: 0.2rem !important;
            margin-bottom: 0.2rem !important;
            font-size: 12px !important;
            line-height: 1.2 !important;
            word-break: break-word !important;
        }

        .dash-widgetcontent h6 {
            font-size: 10px !important;
            line-height: 1.2 !important;
            margin-bottom: 0 !important;
            color: #6c757d !important;
        }
        
        /* Currency symbol and amount styling */
        .dash-widgetcontent h5 .counters {
            font-size: 12px !important;
            font-weight: 600 !important;
        }
        
        /* Adjust for first row (financial amounts) */
        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h5,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h5 {
            font-size: 11px !important; /* Slightly smaller for amounts */
        }
        
        .col-lg-4.col-sm-6.col-4:first-child .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(2) .dash-widgetcontent h6,
        .col-lg-4.col-sm-6.col-4:nth-child(3) .dash-widgetcontent h6 {
            font-size: 9px !important; /* Slightly smaller labels */
        }
        
        /* Adjust for count widgets (4th onwards) */
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h5 {
            font-size: 14px !important; /* Larger for counts */
        }
        
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widgetcontent h6 {
            font-size: 11px !important;
        }
        
        .col-lg-4.col-sm-6.col-4:nth-child(n+4) .dash-widget {
            height: 100px !important; /* Slightly shorter for count widgets */
        }

        /* Table responsive styles */
        .table-responsive {
            font-size: 12px !important;
        }

        /* Mobile table styles for sales/purchases */
        .dataview table thead th:nth-child(n+3),
        .dataview table tbody td:nth-child(n+3) {
            display: none !important;
        }

        .dataview table thead th.details-column,
        .dataview table tbody td:nth-child(2) {
            display: table-cell !important;
            text-align: center;
            vertical-align: middle;
            width: 50px;
        }

        .dataview .toggle-details i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        /* Order ID column styling */
        .dataview table tbody td:first-child {
            display: flex !important;
            align-items: center !important;
            max-width: calc(100vw - 100px) !important;
        }

        .dataview .order-id {
            display: inline-block !important;
            max-width: calc(100% - 50px) !important;
            margin-left: 8px !important;
            font-size: 14px !important;
            word-break: break-word !important;
        }
    }

    /* Desktop: hide details toggle column */
    @media (min-width: 768px) {
        .dataview table thead th.details-column,
        .dataview table tbody td:nth-child(2) {
            display: none !important;
        }
        
        /* Desktop widget spacing */
        .col-lg-4.col-sm-6.col-4 {
            /* margin-bottom: 25px !important; */
        }
    }

    @media screen and (min-width: 768px) and (max-width: 1180px) {
        .content > .row:first-child > div.col-lg-4.col-sm-6.col-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding-left: 8px !important;
            padding-right: 8px !important;
            margin-bottom: 12px !important;
        }

        .content > .row:first-child > div.col-lg-4.col-sm-6.col-4 .dash-widget {
            min-height: 86px;
            margin: 0 !important;
        }

        .content > .row:first-child > div.d-flex {
            flex: 0 0 50%;
            max-width: 50%;
            padding-left: 8px !important;
            padding-right: 8px !important;
            margin-bottom: 12px !important;
        }

        .content > .row:first-child > div.d-flex .color_box {
            width: 100%;
            height: 100%;
        }

        .content > .row:first-child > div.d-flex .dash-count {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 86px;
            height: 100%;
            margin: 0 !important;
            padding: 14px 16px;
        }

        .content > .row:first-child > div.d-flex .dash-counts h4 {
            margin-bottom: 4px;
        }

        .content > .row:first-child > div.d-flex .dash-counts h5 {
            margin-bottom: 0;
            line-height: 1.25;
        }
    }

    /* Mobile collapse styles */
    .mobile-details-collapse {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        margin-top: 10px;
        padding: 12px;
        background-color: #f8f9fa;
    }

    .mobile-details-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        padding-bottom: 8px;
        border-bottom: 1px dashed #dee2e6;
    }

    .mobile-details-label {
        font-weight: 600;
        color: #495057;
    }

    .mobile-details-value {
        color: #212529;
        text-align: right;
    }

    /* Style for chart select2 dropdowns */
    .chart-select-container .select2-container--default .select2-selection--single {
        height: 31px !important;
        border: 1px solid #ced4da !important;
        border-radius: 4px !important;
    }

    .chart-select-container .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 29px !important;
        font-size: 13px !important;
        padding-left: 10px !important;
        padding-right: 25px !important;
    }

    .chart-select-container .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 29px !important;
        width: 10px !important;
    }
    
    /* Clearfix for mobile grid */
    @media screen and (max-width: 767px) {
        .dashboard-widgets-row::after {
            content: "";
            display: table;
            clear: both;
        }
    }
</style>
    <div class="content">
        <div class="row">
           
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg-4 col-sm-6 col-4">
                    <a href="{{ route('sales.list') }}">
                        <div class="dash-widget dash1">
                            <div class="dash-widgetimg">
                                <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash2.svg' }}"
                                        alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters" data-count="{{ $totalSalesAmount }}">
                                        {{ number_format($totalSalesAmount, 2) }}</span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h5>
                                <h6>Total Sales Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
             @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg-4 col-sm-6 col-4">
                    <a href="{{ route('purchase.lists') }}">
                        <div class="dash-widget">
                            <div class="dash-widgetimg">
                                <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash1.svg' }}"
                                        alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters" data-count="{{ $totalPurchaseAmount }}">
                                        {{ number_format($totalPurchaseAmount, 2) }}
                                    </span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h5>
                                <h6>Total Purchase Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'admin', 'sub-admin']))
                <div class="col-lg-4 col-sm-6 col-4">
                    <a href="{{ route('expense.list') }}">
                        <div class="dash-widget dash2">
                            <div class="dash-widgetimg">
                                <span><img src="{{ env('ImagePath') . 'admin/assets/img/icons/dash3.svg' }}"
                                        alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters"
                                        data-count="{{ $totalExpenseAmount }}">{{ number_format($totalExpenseAmount, 2) }}</span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h5>
                                <h6>Total Expense Amount</h6>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['sales-manager']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                ])>
                    <a href="{{ route('sales.list') }}" class="color_box box4">
                        <div class="dash-count das1">
                            <div class="dash-counts">
                                <h4>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters"
                                        data-count="{{ $totalSalesAmount }}">{{ number_format($totalSalesAmount, 2) }}</span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h4>
                                <h5>Total Sales Amount</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user-check"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                ])>
                    <a href="{{ route('sales.list') }}" class="color_box box1">
                        <div class="dash-count das3">
                            <div class="dash-counts">
                                <h4>{{ number_format($salesInvoiceCount, 0) }}</h4>
                                <h5>Sales Invoice</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['purchase-manager']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'purchase-manager',
                ])>
                    <a href="{{ route('purchase.lists') }}" class="color_box box1">
                        <div class="dash-count das3">
                            <div class="dash-counts">
                                <h4>
                                    @if ($currencyPosition === 'left')
                                        {{ $currencySymbol }}
                                    @endif
                                    <span class="counters" data-count="{{ $totalPurchaseAmount }}">
                                        {{ number_format($totalPurchaseAmount, 2) }}
                                    </span>
                                    @if ($currencyPosition === 'right')
                                        {{ $currencySymbol }}
                                    @endif
                                </h4>
                                <h5>Total Purchase Amount</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'purchase-manager',
                ])>
                    <a href="{{ route('purchase.lists') }}" class="color_box box2">
                        <div class="dash-count das2">
                            <div class="dash-counts">
                                <h4>{{ number_format($purchaseInvoiceCount, 0) }}</h4>
                                <h5>Purchase Invoice</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file-text"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'sales-manager',
                ])>
                    <a href="{{ route('customer.list') }}" class="color_box box3">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4>{{ number_format($customerCount, 0) }}</h4>
                                <h5>Customers</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div @class([
                    'col-lg-3 col-sm-6 col-6 d-flex' => in_array($role, [
                        'inventory-manager',
                        'admin',
                        'sub-admin',
                    ]),
                    'col-lg-4 col-sm-6 col-6 d-flex' => $role === 'purchase-manager',
                ])>
                    <a href="{{ route('vendor.list') }}" class="color_box box4">
                        <div class="dash-count das1">
                            <div class="dash-counts">
                                <h4>{{ number_format($vendorCount, 0) }}</h4>
                                <h5>Vendors</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user-check"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endif

        </div>

        @php
            use Carbon\Carbon;
            $currentYear = Carbon::now()->year;
            $previousYear = $currentYear - 1;
        @endphp

        <div class="row">
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="col-lg-6 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">Sales</div>
                            <div class="chart-select-container">
                                <select class="form-control form-control-sm chart-select2" id="salesYearSelect" style="width: 130px;">
                                    <option value="month">This month ({{ date('F') }})</option>
                                    <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                    <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body chart-set">
                            <div class="h-250" style="width: 100%;" id="saleschart"></div>
                        </div>
                    </div>
                </div>
            @endif
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div class="col-lg-6 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="card-title mb-0">Purchases</div>
                            <div class="chart-select-container">
                                <select class="form-control form-control-sm chart-select2" id="purchaseYearSelect" style="width: 130px;">
                                    <option value="month">This month ({{ date('F') }})</option>
                                    <option value="{{ $previousYear }}">Previous year ({{ $previousYear }})</option>
                                    <option value="{{ $currentYear }}" selected>This year ({{ $currentYear }})</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body chart-set">
                            <div class="h-250" style="width: 100%;" id="purchasechart"></div>
                        </div>
                    </div>
                </div>
            @endif

            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                <div class="col-md-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0">Latest Sales</h4>
                                <a href="{{ route('sales.list') }}" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="table-responsive dataview">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th class="details-column">Details</th>
                                            <th>Product Name</th>
                                            <th>Grand Total</th>
                                            <th>Sale Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestSales as $index => $item)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div style="display: flex; align-items: center;">
                                                            <a href="/sales-details/{{ $item->order_id }}"
                                                                class="d-flex align-items-center text-decoration-none">
                                                                <span
                                                                    class="order-id">{{ $item->order_number ?? 'N/A' }}</span>
                                                            </a>
                                                        </div>

                                                        <!-- Collapsible Details for Mobile -->
                                                        <div class="collapse mobile-details-collapse d-md-none"
                                                            id="sales-details-{{ $item->order_id ?? $index }}">
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Product:</span>
                                                                <span class="mobile-details-value">
                                                                    @php
                                                                        $images = json_decode($item->images, true);
                                                                        $imagePath =
                                                                            !empty($images) && isset($images[0])
                                                                                ? env('ImagePath') .
                                                                                    'storage/' .
                                                                                    $images[0]
                                                                                : env('ImagePath') .
                                                                                    '/admin/assets/img/product/noimage.png';
                                                                    @endphp
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                                                        <span>{{ $item->product_name ?? 'N/A' }}</span>
                                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                                            style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                                    </div>
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Grand Total:</span>
                                                                <span class="mobile-details-value">
                                                                    @if ($currencyPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                    {{ number_format($item->total_amount ?? 0, 2) }}
                                                                    @if ($currencyPosition === 'right')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Sale Date:</span>
                                                                <span class="mobile-details-value">
                                                                    {{ Carbon::parse($item->order_date)->format('d F Y h:i A') }}
                                                                </span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="details-column">
                                                    <a href="#sales-details-{{ $item->order_id ?? $index }}"
                                                        class="toggle-details" data-bs-toggle="collapse">
                                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                    </a>
                                                </td>

                                                <td class="productimgname d-none d-md-table-cell">
                                                    <a href="/product-view/{{ $item->product_id }}"
                                                        class="d-flex align-items-center"
                                                        style="max-width: 250px; text-decoration: none; color: inherit;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product Image"
                                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px; flex-shrink: 0;">
                                                        <span
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                                            {{ $item->product_name ?? 'N/A' }}
                                                        </span>
                                                    </a>
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    @if ($currencyPosition === 'left')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                    {{ number_format($item->total_amount ?? 0, 2) }}
                                                    @if ($currencyPosition === 'right')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    {{ Carbon::parse($item->order_date)->format('d F Y h:i A') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No sales records found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Latest Purchases Table - UPDATED -->
            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                <div class="col-md-6">
                    <div class="card mb-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0">Latest Purchases</h4>
                                <a href="{{ route('purchase.lists') }}" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="table-responsive dataview">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Purchase ID</th>
                                            <th class="details-column">Details</th>
                                            <th>Product Name</th>
                                            <th>Grand Total</th>
                                            <th>Purchase Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($latestPurchases as $index => $item)
                                            <tr>
                                                <td>
                                                    <div>
                                                        <div style="display: flex; align-items: center;">
                                                            <a href="/print-purchase/{{ $item->invoice_id }}"
                                                                class="d-flex align-items-center text-decoration-none">
                                                                <span
                                                                    class="order-id">{{ $item->invoice_number ?? 'N/A' }}</span>
                                                            </a>
                                                        </div>

                                                        <!-- Collapsible Details for Mobile -->
                                                        <div class="collapse mobile-details-collapse d-md-none"
                                                            id="purchase-details-{{ $item->invoice_id ?? $index }}">
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Product:</span>
                                                                <span class="mobile-details-value">
                                                                    @php
                                                                        $images = json_decode($item->images, true);
                                                                        $imagePath = !empty($images[0])
                                                                            ? env('ImagePath') . 'storage/' . $images[0]
                                                                            : env('ImagePath') .
                                                                                'admin/assets/img/product/noimage.png';
                                                                    @endphp
                                                                    <div
                                                                        style="display: flex; align-items: center; justify-content: flex-end; gap: 8px;">
                                                                        <span>{{ $item->product_name ?? 'N/A' }}</span>
                                                                        <img src="{{ asset($imagePath) }}" alt="Product"
                                                                            style="width: 30px; height: 30px; object-fit: cover; border-radius: 4px;">
                                                                    </div>
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Grand Total:</span>
                                                                <span class="mobile-details-value">
                                                                    @if ($currencyPosition === 'left')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                    {{ number_format($item->grand_total ?? 0, 2) }}
                                                                    @if ($currencyPosition === 'right')
                                                                        {{ $currencySymbol }}
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <div class="mobile-details-row">
                                                                <span class="mobile-details-label">Purchase Date:</span>
                                                                <span class="mobile-details-value">
                                                                    {{ \Carbon\Carbon::parse($item->purchase_date)->format('d F Y h:i A') }}
                                                                </span>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </td>

                                                <td class="details-column">
                                                    <a href="#purchase-details-{{ $item->invoice_id ?? $index }}"
                                                        class="toggle-details" data-bs-toggle="collapse">
                                                        <i class="fas fa-plus-circle" style="color: #ff9f43;"></i>
                                                    </a>
                                                </td>

                                                <td class="productimgname d-none d-md-table-cell">
                                                    <a href="/product-view/{{ $item->product_id }}"
                                                        style="display: flex; align-items: center; max-width: 250px; text-decoration: none; color: inherit;">
                                                        <img src="{{ asset($imagePath) }}" alt="Product Image"
                                                            style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px; flex-shrink: 0;">
                                                        <span
                                                            style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">
                                                            {{ $item->product_name ?? 'N/A' }}
                                                        </span>
                                                    </a>
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    @if ($currencyPosition === 'left')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                    {{ number_format($item->grand_total ?? 0, 2) }}
                                                    @if ($currencyPosition === 'right')
                                                        {{ $currencySymbol }}
                                                    @endif
                                                </td>

                                                <td class="d-none d-md-table-cell">
                                                    {{ \Carbon\Carbon::parse($item->purchase_date)->format('d F Y h:i A') }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No purchase records
                                                    found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Toggle details icon animation
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
        });
    </script>
    <script>
        const currentYear = {{ $currentYear }};
        const previousYear = {{ $previousYear }};
        const salesThisMonth = @json($salesChartThisMonth);
        const purchaseThisMonth = @json($purchaseChartThisMonth);

        const salesChartData = {
            [currentYear]: @json($salesChartthisyear),
            [previousYear]: @json($salesChartpreviousyear),
            'thisMonth': salesThisMonth
        };

        const purchaseChartData = {
            [currentYear]: @json($purchaseChartthisyear),
            [previousYear]: @json($purchaseChartpreviousyear),
            'thisMonth': purchaseThisMonth
        };

        // ✅ Updated chart options
        const options = {
            grid: {
                borderWidth: 1,
                borderColor: 'rgba(67, 87, 133, .09)',
                hoverable: true
            },
            xaxis: {
                ticks: [], // dynamically set below
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 9
                },
                autoscaleMargin: 0.02
            },
            yaxis: {
                tickColor: 'rgba(67, 87, 133, .09)',
                font: {
                    color: '#8e9cad',
                    size: 10
                },
                tickFormatter: function(val, axis) {
                    return val.toLocaleString();
                }
            },
            legend: {
                show: true,
                position: "nw"
            },
            tooltip: true,
            tooltipOpts: {
                content: function(label, xval, yval, flotItem) {
                    return label + ": " + yval.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                shifts: {
                    x: 10,
                    y: 20
                },
                defaultTheme: false
            }
        };

        // ✅ Fixed: Tick generator for day labels 1–31
        function getDayTicks(length) {
            return Array.from({
                length
            }, (_, i) => [i, (i + 1).toString()]);
        }

        // Optional: Only show every 2nd or 5th tick (use if labels are still crowded)
        // function getDayTicks(length) {
        //     return Array.from({ length }, (_, i) => [i, (i + 1).toString()])
        //         .filter(([, label]) => parseInt(label) % 2 === 1); // odd days only
        // }

        function renderChart(containerId, label, data, barColor, isMonthly = true) {
            const series = data.map((val, idx) => [idx, val]);

            options.xaxis.ticks = isMonthly ? [
                    [0, 'Jan'],
                    [1, 'Feb'],
                    [2, 'Mar'],
                    [3, 'Apr'],
                    [4, 'May'],
                    [5, 'Jun'],
                    [6, 'Jul'],
                    [7, 'Aug'],
                    [8, 'Sep'],
                    [9, 'Oct'],
                    [10, 'Nov'],
                    [11, 'Dec']
                ] :
                getDayTicks(data.length); // ✅ this ensures 1–31 labels

            $.plot(containerId, [{
                label,
                data: series,
                bars: {
                    show: true,
                    barWidth: 0.3, // ✅ adjusted for tight spacing
                    align: "center",
                    fillColor: barColor
                },
                color: barColor
            }], options);
        }

        // $(document).ready(function () {
        //     renderChart('#saleschart', 'Sales', salesChartData[currentYear], '#44c4fa');
        //     renderChart('#purchasechart', 'Purchases', purchaseChartData[currentYear], '#fa6c7c');

        //     $('#salesYearSelect').on('change', function () {
        //         const year = $(this).val();
        //         if (year === 'month') {
        //             renderChart('#saleschart', 'Sales (This Month)', salesChartData['thisMonth'], '#44c4fa', false);
        //         } else {
        //             renderChart('#saleschart', 'Sales', salesChartData[year], '#44c4fa');
        //         }
        //     });

        //     $('#purchaseYearSelect').on('change', function () {
        //         const year = $(this).val();
        //         if (year === 'month') {
        //             renderChart('#purchasechart', 'Purchases (This Month)', purchaseChartData['thisMonth'], '#fa6c7c', false);
        //         } else {
        //             renderChart('#purchasechart', 'Purchases', purchaseChartData[year], '#fa6c7c');
        //         }
        //     });
        // });

        $(document).ready(function() {
            $('.chart-select2').select2({
                minimumResultsForSearch: -1,
                width: '100%'
            });
            @if (in_array($role, ['inventory-manager', 'sales-manager', 'admin', 'sub-admin']))
                renderChart('#saleschart', 'Sales', salesChartData[currentYear], '#44c4fa');
                $('#salesYearSelect').on('change', function() {
                    const year = $(this).val();
                    if (year === 'month') {
                        renderChart('#saleschart', 'Sales (This Month)', salesChartData['thisMonth'],
                            '#44c4fa', false);
                    } else {
                        renderChart('#saleschart', 'Sales', salesChartData[year], '#44c4fa');
                    }
                });
            @endif

            @if (in_array($role, ['inventory-manager', 'purchase-manager', 'admin', 'sub-admin']))
                renderChart('#purchasechart', 'Purchases', purchaseChartData[currentYear], '#fa6c7c');
                $('#purchaseYearSelect').on('change', function() {
                    const year = $(this).val();
                    if (year === 'month') {
                        renderChart('#purchasechart', 'Purchases (This Month)', purchaseChartData[
                            'thisMonth'], '#fa6c7c', false);
                    } else {
                        renderChart('#purchasechart', 'Purchases', purchaseChartData[year], '#fa6c7c');
                    }
                });
            @endif

        });
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let url = "{{ url('/api/dashboard-api') }}";

            if (selectedSubAdminId) {
                url += `?selectedSubAdminId=${selectedSubAdminId}`;
            }
            $.ajax({
                url: url,
                method: "GET",
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                dataType: "json",
                success: function(response) {
                    // console.log("Branch:", response.branch_id);
                    if (response.status) {
                        // console.log(response.data);

                        // Example: Update totals
                        $('#totalPurchase').text(parseFloat(response.data.totals.purchase).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#totalSales').text(parseFloat(response.data.totals.sales).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                        $('#totalExpense').text(parseFloat(response.data.totals.expense).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));

                        // ✅ Low Stock Alert
                        const lowStock = response.data.lowStock;
                        if (lowStock && lowStock.threshold > 0 && lowStock.products && lowStock.products.length > 0) {
                            const sessionKey = 'lowStockAlertShown_' + new Date().toDateString();
                            if (!sessionStorage.getItem(sessionKey)) {
                                sessionStorage.setItem(sessionKey, '1');

                                const rows = lowStock.products.map(function(p) {
                                    const qty = parseFloat(p.quantity);
                                    const badgeClass = qty <= 0 ? 'bg-lightred' : 'bg-lightyellow';
                                    const label = qty <= 0 ? 'Out of Stock' : 'Low Stock';
                                    return `<tr>
                                        <td style="text-align:left;padding:6px 10px;">${p.name}</td>
                                        <td style="text-align:center;padding:6px 10px;">${qty.toFixed(3)}</td>
                                        <td style="text-align:center;padding:6px 10px;"><span class="badges ${badgeClass}" style="font-size:11px;">${label}</span></td>
                                    </tr>`;
                                }).join('');

                                Swal.fire({
                                    title: '⚠️ Low Stock Alert',
                                    html: `
                                        <p style="margin-bottom:10px;color:#555;">The following products are below the threshold of <strong>${lowStock.threshold}</strong> units:</p>
                                        <div style="max-height:300px;overflow-y:auto;">
                                            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                                                <thead>
                                                    <tr style="background:#f5f5f5;">
                                                        <th style="text-align:left;padding:8px 10px;border-bottom:1px solid #ddd;">Product</th>
                                                        <th style="text-align:center;padding:8px 10px;border-bottom:1px solid #ddd;">Current Qty</th>
                                                        <th style="text-align:center;padding:8px 10px;border-bottom:1px solid #ddd;">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>${rows}</tbody>
                                            </table>
                                        </div>`,
                                    icon: 'warning',
                                    confirmButtonText: 'View Products',
                                    showCancelButton: true,
                                    cancelButtonText: 'Dismiss',
                                    confirmButtonColor: '#ff9f43',
                                    width: '600px',
                                }).then(function(result) {
                                    if (result.isConfirmed) {
                                        window.location.href = '/products';
                                    }
                                });
                            }
                        }
                    }
                },
                error: function(xhr) {
                    // console.error(xhr.responseText);
                }
            });
        });
    </script>
@endpush
