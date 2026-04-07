@extends('layout.app')

@section('title', 'Add Sale')

@section('content')
    <style>
        .setvaluecash ul li a {
            border: 1px solid #e9ecef;
            color: #000;
            font-size: 12px;
            font-weight: 600;
            min-height: 59px;
            border-radius: 5px;
            padding: 8px 7px;
        }

        .disabled-product {
            opacity: 0.6;
            pointer-events: none;
            /* disables clicking inside */
        }

        .paymentmethod.active {
            /* background-color: #1b2850; */
            color: white;
            /* border-radius: 5px; */
        }

        .paymentmethod.active:hover {
            /* background-color: white; */
            color: white;
            /* Dark text for contrast */
            /* border: 1px solid #1b2850; */
            /* Optional: border to define shape */
        }

        .tabs_wrapper ul.tabs {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding: 0;
            margin: 0;
            list-style: none;
            scrollbar-width: thin;
            width: auto;
            /* scrollbar-color: #ff9f43 #f1f1f1; */
        }

        .tabs_wrapper ul.tabs::-webkit-scrollbar {
            display: none;
        }

        .tabs_wrapper ul.tabs li {
            flex: 0 0 auto;
            cursor: pointer;
            white-space: nowrap;
            width: auto;
            /* ðŸ‘ˆ Prevent tab name from wrapping */
            padding: 0;
        }

        .product-details {
            background: #fff;
            padding: 10px 16px;
            box-shadow: none;
            border: none !important;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: 0.3s ease;
            min-width: max-content;
            /* ðŸ‘ˆ Ensure box width fits content */
        }

        .product-details h6 {
            font-size: 14px;
            color: #000;
            margin: 0;
            text-align: center;
            white-space: nowrap;
            /* ðŸ‘ˆ Prevent text wrap */
            overflow: hidden;
            text-overflow: ellipsis;
            /* ðŸ‘ˆ Optional: show ... if too long */
            max-width: 100%;
            /* ðŸ‘ˆ Avoid overflow outside parent */
        }

        .tabs_wrapper ul.tabs li.active .product-details {
            background: transparent;
            /* border-bottom: 2px solid #ff9f43 !important; */
        }

        .tabs_wrapper ul.tabs li.active .product-details h6 {
            color: #ff9f43;
        }

        .payment_panel {
            position: fixed;
            bottom: 0;
            background-color: white;
            width: 37%;
        }

        .body_space {
            /*margin-bottom: 2rem;*/
        }

        .paymentmethod.active {
            background-color: #1b2850;
            color: #ffffff;
        }

        .paymentmethod.active svg {
            fill: #ffffff;
        }

        .productsetimgin {
            height: 100px;
            object-fit: contain;
            margin: 1rem auto 0;
            display: block;
        }

        .productsetbtn button {
            display: none;
        }

        .search_custom_product {
            width: 100%;
        }

        .scanner-search-wrap {
            width: 100%;
        }

        .scanner-search-wrap .header-search {
            margin-bottom: 4px !important;
        }

        .mobile-scanner-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            line-height: 1.2;
            border-radius: 4px;
            border: 1px solid #d1d5db;
            padding: 3px 8px;
            background: #ffffff;
            color: #6b7280;
            min-height: 22px;
        }

        .mobile-scanner-status::before {
            content: "";
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.8;
        }

        .mobile-scanner-status.is-connected {
            color: #166534;
            border-color: #86efac;
            background: #f0fdf4;
        }

        .mobile-scanner-status.is-disconnected {
            color: #6b7280;
            border-color: #d1d5db;
            background: #ffffff;
        }

        .mobile-scanner-status.is-checking {
            color: #475569;
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .payment_panel {
            z-index: 999;
        }

        .price {
            display: flex;
            flex-direction: column;
            width: 180px;
            font-size: 12px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .sub-total span {
            color: #ff9f43;
        }

        .gst-inc span {
            color: #007bff;
        }

        .discount span {
            color: red;
        }

        .final-total {
            font-weight: bold;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px solid #ddd;
        }

        .final-total span {
            color: green;
        }

        .responsive-mobile-view-1 {
            display: none !important;
        }

        .pos-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #ff9f43;
            background: #ff9f43;
            color: #fff;
            border-radius: 4px;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 600;
            line-height: 1;
            height: 28px;
        }

        .pos-back-btn:hover {
            background: #ff9f43;
            color: #fff;
        }

        .pos-back-btn i {
            font-size: 12px;
            line-height: 1;
        }

        .pos-top-controls {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            width: 100%;
            margin-bottom: 12px;
            flex-wrap: wrap;
        }

        .pos-top-controls-left {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .pos-gst-options {
            display: inline-flex;
            align-items: center;
            gap: 14px;
        }

        .pos-gst-options .custom-radio-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            white-space: nowrap;
            font-size: 14px;
        }

        .pos-quotation-toggle {
            margin: 0;
            padding-left: 0;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }

        .pos-quotation-toggle .form-check-input {
            margin: 0;
            margin-left: 0;
            float: none;
        }

        .pos-quotation-toggle .form-check-label {
            margin: 0;
            font-weight: 500;
        }

        @media screen and (max-width: 992px) {
            .pos-top-controls {
                align-items: flex-start;
            }
        }

        @media screen and (max-width: 768px) {
            .payment_panel {
                position: fixed;
                bottom: 50px;
                background-color: white;
                width: 90%;
                margin-left: -6px;
            }


            .responsive-mobile-view {
                display: none !important;
            }

            .responsive-mobile-view-1 {
                display: block !important;
            }


            .productset {
                display: flex;
            }

            .productset .productsetimg {

                width: 30%;
            }

            .productsetimgin {
                object-fit: cover;
                height: 90px;
                margin: 0;
                max-width: 110px;
            }

            .productsetcontent {
                text-align: left !important;
            }



            .productsetbtn {
                position: absolute;
                right: 10px;
                top: 43px;
            }

            .productsetbtn button {
                display: block;
                width: 100%;
                height: 33px;
                background-color: #1b2850;
                color: white !important;
                border: none;
                border-radius: 5px;
            }

            .productsetbtn button:active {
                color: white;
            }

            .product-lists {
                flex-direction: row !important;
            }

            /* .body_space_two {
                                margin-bottom: 4rem;
                            } */

            a.confirm-text.remove-item {
                /* font-size: 116px; */
                margin: 6px;
            }


            .mobile-top-options {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                background: #ffffff;
                padding: 10px 15px;
                z-index: 9999;
                border-bottom: 1px solid #eee;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            /* Give space below so content not hide */
            .card-order .card-body {
                margin-top: 10px;
            }

            /* Make radio inline properly */
            .mobile-top-options label {
                font-size: 13px;
                font-weight: 600;
                margin-right: 10px;
            }

            .mobile-top-options input[type="radio"],
            .mobile-top-options input[type="checkbox"] {
                margin-right: 4px;
            }

        }

        .gst-info {
            margin: 4px 0;
            padding: 3px 8px;
            background: #f8f9fa;
            border-radius: 4px;
            border-left: 3px solid #4caf50;
        }

        .product-lists .gst-info small {
            display: block;
            line-height: 1.3;
        }

        .productset .productsetimg .gst-hover-badge {
            position: absolute;
            color: #fff;
            font-size: 10px;
            padding: 5px;
            border-radius: 5px;
            top: 55px;
            right: 20px;
            transform: translatey(-100px);
            transition: all .5s;
            z-index: 11;
            font-weight: 600;
        }

        .productset:hover .productsetimg .gst-hover-badge {
            transform: translatey(0);
        }

        .gst-hover-badge.with-gst {
            background: #4caf50;
        }

        .gst-hover-badge.no-gst {
            background: #9e9e9e;
        }

        .product-discount-box {
            display: flex;
            gap: 12px;
            align-items: flex-end;
            margin-top: 6px;
            /* padding-right: 20px; */
        }

        .discount-field {
            display: flex;
            flex-direction: column;
        }

        .discount-field label {
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 3px;
            color: #444;
        }

        .product-discount-percentage,
        .product-discount-amount {
            width: 50px;
            height: 30px;
            padding: 4px 6px;
            font-size: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: #f9f9f9;
            text-align: center;
        }

        .product-discount-percentage:focus,
        .product-discount-amount:focus {
            border-color: #ff9f43;
            background: #fff;
            box-shadow: 0 0 3px rgba(255, 159, 67, 0.3);
        }

        .product-lists {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4px 10px 4px 0;
            border-bottom: 1px solid #eee;
            /* width: 600px; */
            gap: 10px;
        }

        .product-lists>li {
            list-style: none;
        }

        .product-lists li:last-child {
            width: 50px;
            text-align: center;
            flex-shrink: 0;
        }

        .remove-item img {
            width: 18px;
            height: 18px;
            cursor: pointer;
            transition: 0.2s ease;
        }

        .remove-item img:hover {
            transform: scale(1.1);
            opacity: 0.7;
        }

        .setvalue ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .product-table {
            max-height: 400px;
            overflow: auto;
        }

        .product-table::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .product-table::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .product-table::-webkit-scrollbar-thumb {
            background: #FF9F43;
            border-radius: 4px;
        }

        .product-table::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .setvalue ul li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            border-bottom: 1px dashed #eee;
        }

        .setvalue ul li:last-child {
            border-bottom: none;
        }

        .setvalue h5 {
            font-size: 14px;
            font-weight: 500;
            margin: 0;
            color: #555;
        }

        .setvalue h6 {
            font-size: 15px;
            font-weight: 600;
            margin: 0;
            color: #222;
        }

        .setvalue {
            padding: 0 0 40px 0 !important;
        }

        .setvalue .total-value h6 {
            font-size: 18px;
            font-weight: 700;
            color: #28a745;
        }

        /* Fix customer row layout */
        .row.select-group.w-100 {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            gap: 15px;
            /* space between boxes */
            margin: 0;
        }

        /* Ensure both columns use equal width */
        .row.select-group .col-md-6 {
            flex: 1;
            max-width: 50%;
            padding: 0;
        }

        /* Remove unnecessary nested width conflicts */
        .select-split,
        .select-group {
            width: 100%;
        }

        /* Improve input/select appearance */
        #customer_name,
        #customer_phone {
            width: 100%;
        }

        /* Fix for labour items select */
        .select2-labour+.select2-container {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            padding-top: 4px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
        }

        #qr-reader {
            min-height: 300px;
            width: 100%;
            background: #f5f5f5;
            /* optional: shows a background while loading */
        }

        .productcontet h4 {
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: break-word;
            font-size: 13px;
            line-height: 1.3;
            max-width: 150px;
        }
    </style>
    <div class="content">
        <input type="hidden" id="quotation_status" name="quotation_status" value="sales">

        <div class="d-flex align-items-center justify-content-between mb-3  responsive-mobile-view-1">

            <!-- Left Side -->

            <!-- Center / Right Controls -->
            {{-- <div class="d-flex align-items-center gap-3"> --}}

            <div class=" d-flex justify-content-between ">

                <div class="form-check m-0">
                    <input class="form-check-input quotationToggle" type="checkbox" id="quotationToggle1" value="quotation">
                    <label class="form-check-label" for="quotationToggle1">Quotation</label>
                </div>
                <div class="d-flex gap-3">
                    <label class="custom-radio-label m-0">
                        <input type="radio" name="gst_option" value="without" checked>
                        Without GST
                    </label>
                    <label class="custom-radio-label m-0">
                        <input type="radio" name="gst_option" value="with">
                        With GST
                    </label>
                </div>

            </div>

        </div>

        <div class="row">
            <div class="col-lg-6 col-sm-12 tabs_wrapper">


                @php
                    $hasProducts = false; // start as false
                @endphp

                <ul class="tabs border-0 mb-4">
                    @foreach ($categories as $cat)
                        @php
                            $availableProducts = $cat->products->where('quantity', '>', 0);
                        @endphp

                        @if ($availableProducts->count() > 0)
                            @php
                                $hasProducts = true; // at least one product exists
                            @endphp
                            <li id="{{ $cat->id }}">
                                <div class="product-details">
                                    <h6 style="text-transform: capitalize;">{{ $cat->name }}</h6>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>

                @if (!$hasProducts)
                    <div class="product-details text-center">
                        <h6>No data available</h6>
                        <a href="{{ url('add-product') }}" class="btn btn-primary mt-2">
                            + Add Product
                        </a>
                    </div>
                @endif
                <div class="tabs_container">

                </div>
            </div>
            <div class="col-lg-6 col-sm-12 ">

                <div class="card card-order">
                    <div class="card-body body_space">

                        @php
                            $user = auth()->user();
                        @endphp
                        @php
                            $subAdminId = session('selectedSubAdminId');
                            // Ensure $role is set - use from controller if available, otherwise get from auth
                            if (empty($role ?? '')) {
                                $role = Auth::user()->role ?? '';
                            }
                        @endphp
                        <div class="pos-top-controls responsive-mobile-view">
                            <div class="pos-top-controls-left">
                                @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                                    <div class="form-check pos-quotation-toggle">
                                        <input class="form-check-input quotationToggle" type="checkbox" id="quotationToggle2"
                                            value="quotation">
                                        <label class="form-check-label" for="quotationToggle2">Quotation</label>
                                    </div>
                                @endif
                                <div class="pos-gst-options">
                                    <label class="custom-radio-label">
                                        <input type="radio" name="gst_option" id="without_gst" value="without" checked />
                                        Without GST
                                    </label>

                                    <label class="custom-radio-label">
                                        <input type="radio" name="gst_option" id="with_gst" value="with" />
                                        With GST
                                    </label>
                                </div>
                            </div>
                            <a href="{{ route('sales.list') }}" class="pos-back-btn">
                                <i class="fa-solid fa-arrow-left"></i>
                                Back
                            </a>
                        </div>
                        <div class="row">

                            <div class="col-lg-12">
                                <div class="scanner-search-wrap me-3 mb-3 search_custom_product">
                                    <div class="header-search d-flex align-items-center position-relative">
                                        <!-- Scanner Button (New) -->
                                        <button type="button" id="scanBarcodeBtn" class="btn btn-sm"
                                            style="background: #f0f0f0; border: 1px solid #ccc; border-radius: 4px; padding: 6px 8px; margin-right: 8px; color: #333; display: inline-flex; align-items: center; justify-content: center;min-width: 40px;height: 40px;"
                                            title="Scan Barcode">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                <path
                                                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                <path d="M12 9h2V8h-2z" />
                                            </svg>
                                        </button>
                                        <!-- Search Icon and Input (existing) -->
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}"
                                            alt="Search"
                                            style="position: absolute; left: 55px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">
                                        <input type="text" id="customerSearch1"
                                            class="form-control form-control-sm rounded px-3 ps-5"
                                            placeholder="Search..." autocomplete="off"
                                            style="height: 38px; font-size: 14px; padding-left: 42px;">
                                        <!-- Search Results (existing) -->
                                        <div id="searchResults1"
                                            class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                                            style="z-index: 1050; max-height: 300px; overflow-y: auto; display: none; top: 100%; left: 0;">
                                        </div>
                                    </div>
                                    <div id="mobileScannerStatus" class="mobile-scanner-status is-checking">
                                        Mobile scanner: checking...
                                    </div>
                                </div>

                                <!-- Barcode Scanner Modal -->
                                <div class="modal fade" id="barcodeScannerModal" tabindex="-1"
                                    aria-labelledby="barcodeScannerModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="barcodeScannerModalLabel">Scan Barcode</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close">x</button>
                                            </div>
                                            <div class="modal-body">
                                                <div id="qr-reader" style="width:100%; min-height:300px;"></div>
                                                <div id="scan-message" class="text-center mt-2 small text-muted">
                                                    Initializing camera...</div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row select-group w-100">
                                    <!-- Customer Name -->
                                    <div class="col-md-6 col-6">
                                        <div class="select-split select-group w-100">
                                            <div class="select-group w-100">
                                                <label>Customer Name</label>
                                                <select id="customer_name" name="customer_name" style="z-index:1;"
                                                    class="form-control select2">
                                                    <option value="">Select Customer</option>
                                                    @foreach ($customers as $username)
                                                        <option value="{{ $username->id }}"
                                                            data-phone="{{ $username->phone }}">
                                                            {{ $username->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <span class="error_customername text-danger"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Customer Phone -->
                                    <div class="col-md-6 col-6">
                                        <div class="select-split">
                                            <div class="select-group w-100">
                                                <label>Customer Phone</label>
                                                <input type="tel" id="customer_phone" class="form-control"
                                                    placeholder="Customer number" name="customer_phone">
                                                <span class="error_customerphone text-danger"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.getElementById('customer_name').addEventListener('change', function() {
                                var phone = this.options[this.selectedIndex].getAttribute('data-phone');
                                document.getElementById('customer_phone').value = phone || '';
                            });
                        </script>
                        <div class="split-card">

                        </div>
                        <div class="pt-0">
                            <div class="totalitem">
                                <h4>Total items : 4</h4>
                                <a href="javascript:void(0);" class="clear_items">Clear all</a>
                            </div>
                            <div class="product-table">

                            </div>
                        </div>
                        <div class="split-card">
                        </div>
                        <div class="pt-0 pb-2 body_space_two select-group w-100">
                            @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                                <!-- Labour Items Section -->
                                <div class="col-lg-12 mb-3">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <hr>
                                            <h5 style=" font-weight: 400; font-size: 16px; ">Labour Items</h5>
                                            <div id="labour-items-container">
                                                <!-- Labour items will be added here dynamically -->
                                            </div>
                                            <!-- <button type="button" class="btn btn-primary mt-2" id="add-labour-item">
                                                                                                                                            <i class="fas fa-plus"></i> Add Labour Item
                                                                                                                                        </button> -->
                                            <hr>
                                        </div>
                                    </div>
                                </div>
                                <!-- End Labour Items Section -->
                            @endif
                            <div class="row">

                            </div>
                            <div class="row ">
                                <div class="col-lg-12">
                                    <div class="select-split ">
                                        <div class="select-group w-100">
                                            <label for="shipping">Shipping Cost</label>
                                            <input type="number" class="form-control" placeholder="Shipping Cost."
                                                name="shipping" id="shipping" min="0" step="0.01">
                                            <span class="error_shipping"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 ">
                            <div class="select-split ">
                                <div class="select-group w-100">
                                    <label for="remarks">Remarks (Optional)</label>
                                    <textarea class="form-control" name="remarks" id="remarks" rows="4" cols="50"
                                        placeholder="Enter remarks"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="setvalue">
                            <ul>
                                <li>

                                    <h5>Subtotal</h5>
                                    <h6 style="color: green;">
                                        @if ($currency_position === 'right')
                                            <span class="subtotal-value">0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span class="subtotal-value">0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="subtotal" value="" class="tax-hidden">
                                </li>

                                <li>
                                    <h5>Discount Amount</h5>
                                    <h6 class="discount-amount">
                                        @if ($currency_position === 'right')
                                            <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="discount_amount" value="0.00" class="tax-hidden">
                                </li>

                                <li>
                                    <h5>Price After Discount</h5>
                                    <h6 class="price-after-discount">
                                        @if ($currency_position === 'right')
                                            <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="price_after_discount" value="0.00" class="tax-hidden">
                                </li>

                                <li>
                                    <h5>Shipping Cost</h5>
                                    <h6 class="shipping-cost-summary">
                                        @if ($currency_position === 'right')
                                            <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                </li>

                                {{-- @foreach ($taxRates as $tax)
                                        <li class="taxList">
                                            <h5>{{ $tax->tax_name }} ({{ $tax->tax_rate }}%) Tax</h5>
                                            <h6 class="tax-value" data-rate="{{ $tax->tax_rate }}"
                                                data-symbol="{{ $currency_symbol }}"
                                                data-position="{{ $currency_position }}">
                                                @if ($currency_position === 'right')
                                                    <span>0.00</span>{{ $currency_symbol }}
                                                @else
                                                    {{ $currency_symbol }}<span>0.00</span>
                                                @endif
                                            </h6>
                                        </li>
                                    @endforeach --}}

                                <li class="gst-summary-row" style="display: none;">
                                    <h5>Total GST Amount</h5>
                                    <h6 class="gst-total-amount">
                                        @if ($currency_position === 'right')
                                            <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                </li>

                                <li class="labour-summary-row" style="display: none;">
                                    <h5>Labour Charge</h5>
                                    <h6 class="labour-total-amount">
                                        @if ($currency_position === 'right')
                                            <span>0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span>0.00</span>
                                        @endif
                                    </h6>
                                </li>

                                <li class="total-value">
                                    <h5>Total</h5>
                                    <h6>
                                        @if ($currency_position === 'right')
                                            <span class="total-amount">0.00</span>{{ $currency_symbol }}
                                        @else
                                            {{ $currency_symbol }}<span class="total-amount">0.00</span>
                                        @endif
                                    </h6>
                                    <input type="hidden" name="total" value="" class="tax-hidden">
                                </li>

                                <li class="error-message-container">
                                    <span class="error_total" style="color:red; display:none;"></span>
                                </li>
                            </ul>
                        </div>
                        <div class="payment_panel">
                            <div class="setvaluecash" id="paymentSection">
                                <ul>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="pending" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Pay Later
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="cash" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Cash
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="debit card" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-credit-card" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1z" />
                                                <path
                                                    d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1z" />
                                            </svg>
                                            Debit
                                        </a>
                                    </li>
                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="cash+online" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-cash" viewBox="0 0 16 16">
                                                <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4" />
                                                <path
                                                    d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2z" />
                                            </svg>
                                            Cash+Online
                                        </a>
                                    </li>

                                    <li>
                                        <a href="javascript:void(0);" class="paymentmethod">
                                            <input type="radio" name="payment_method" value="scan" hidden>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="#1b2850" class="bi bi-qr-code-scan" viewBox="0 0 16 16">
                                                <path
                                                    d="M0 .5A.5.5 0 0 1 .5 0h3a.5.5 0 0 1 0 1H1v2.5a.5.5 0 0 1-1 0zm12 0a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0V1h-2.5a.5.5 0 0 1-.5-.5M.5 12a.5.5 0 0 1 .5.5V15h2.5a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H15v-2.5a.5.5 0 0 1 .5-.5M4 4h1v1H4z" />
                                                <path d="M7 2H2v5h5zM3 3h3v3H3zm2 8H4v1h1z" />
                                                <path d="M7 9H2v5h5zm-4 1h3v3H3zm8-6h1v1h-1z" />
                                                <path
                                                    d="M9 2h5v5H9zm1 1v3h3V3zM8 8v2h1v1H8v1h2v-2h1v2h1v-1h2v-1h-3V8zm2 2H9V9h1zm4 2h-1v1h-2v1h3zm-4 2v-1H8v1z" />
                                                <path d="M12 9h2V8h-2z" />
                                            </svg>
                                            Scan
                                        </a>
                                    </li>

                                </ul>
                                <div id="cashOnlineBox" style="display:none; margin-top:10px;">
                                    <div class="form-group">
                                        <label>Cash Amount</label>
                                        <input type="number" class="form-control" id="cashAmount"
                                            placeholder="Enter Cash Amount" placeholder="0">
                                    </div>
                                    <div class="form-group">
                                        <label>Online Amount</label>
                                        <input type="number" class="form-control" id="onlineAmount"
                                            placeholder="Enter Online Amount" value="0" readonly>
                                    </div>
                                </div>
                                <div id="bankSelectionBox" style="display:none; margin-top:10px;">
                                    <div class="form-group">
                                        <label>Select Bank</label>
                                        <select name="bank_id" id="bank_id" class="form-control">
                                            <option value="">Select Bank</option>
                                            @foreach ($banks as $bank)
                                                <option value="{{ $bank->id }}">{{ $bank->bank_name }}
                                                    ({{ $bank->account_number }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="error_bank text-danger"></span>
                                    </div>
                                </div>
                                <span class="error_peymentmethod"></span>
                            </div>

                            <div class="btn-totallabel">
                                <h6>Total Amount : 60.00$</h6>
                                <h5>Pay Now <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"
                                        style="margin-bottom: 0.1rem;">
                                        <path fill-rule="evenodd"
                                            d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8" />
                                    </svg></h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('js')
    {{-- <script src="https://unpkg.com/html5-qrcode@2.3.4/minified/html5-qrcode.min.js"></script> --}}
    <script>
        if (typeof Html5Qrcode === 'undefined') {
            var script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js';
            script.onload = function() {
                console.log('Library loaded from fallback');
            };
            document.head.appendChild(script);
        }
    </script>
    <script>
        // ==================== BARCODE SCANNER ====================
        // let html5QrCode = null;
        // ✅ MUST be defined BEFORE initScanner calls it
        // async function onScanSuccess(decodedText) {
        //     console.log("Scan success:", decodedText);
        //     stopScanner();

        //     document.activeElement && document.activeElement.blur();
        //     $('#barcodeScannerModal').modal('hide');
        //     $('#scan-message').text('');

        //     try {
        //         const product = await fetchProductByBarcode(decodedText);
        //         if (product) {
        //             addProductToCart(product);
        //             Swal.fire({
        //                 icon: 'success',
        //                 title: 'Product Added',
        //                 text: product.name + ' added to cart',
        //                 timer: 1500,
        //                 showConfirmButton: false
        //             });
        //         } else {
        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Product Not Found',
        //                 text: 'No product with barcode: ' + decodedText
        //             });
        //         }
        //     } catch (error) {
        //         Swal.fire({
        //             icon: 'error',
        //             title: 'Error',
        //             text: error || 'Failed to fetch product. Please try again.'
        //         });
        //     }
        // }
        let html5QrCode = null;

        // 🔊 ADD THIS FUNCTION HERE
        function playBeep() {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

            function beep(time, freq) {
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                osc.connect(gain);
                gain.connect(audioCtx.destination);

                osc.type = "square";
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime + time);

                gain.gain.setValueAtTime(1, audioCtx.currentTime + time);

                osc.start(audioCtx.currentTime + time);
                osc.stop(audioCtx.currentTime + time + 0.2);
            }

            // 🔥 DOUBLE BEEP
            beep(0, 1200);
            beep(0.25, 1500);
        }
        async function onScanSuccess(decodedText) {
            console.log("Scan success:", decodedText);

            // 🔊 NEW SOUND
            playBeep();

            stopScanner();

            document.activeElement && document.activeElement.blur();
            $('#barcodeScannerModal').modal('hide');
            $('#scan-message').text('');

            try {
                const product = await fetchProductByBarcode(decodedText);
                if (product) {
                    addProductToCart(product);
                }
            } catch (error) {
                console.log(error);
            }
        }
        function onScanError(errorMessage) {
            console.debug("Scan error:", errorMessage);
        }

        function stopScanner() {
            if (!html5QrCode) return;

            if (html5QrCode.isScanning) {
                html5QrCode.stop()
                    .then(() => {
                        html5QrCode = null;
                    })
                    .catch(err => {
                        console.error("Stop error:", err);
                        html5QrCode = null;
                    });
            } else {
                html5QrCode = null;
            }
            $('#scan-message').text('');
        }

        function waitForLibrary(callback, retries = 20) {
            if (typeof Html5Qrcode !== "undefined") {
                callback();
            } else if (retries > 0) {
                setTimeout(() => waitForLibrary(callback, retries - 1), 200);
            } else {
                $('#scan-message').text('Scanner library failed to load. Please refresh.').css('color', 'red');
            }
        }

        function startScanner() {
            console.log("startScanner called");
            waitForLibrary(function() {
                if (html5QrCode) {
                    let stopPromise = html5QrCode.isScanning ?
                        html5QrCode.stop() :
                        Promise.resolve();
                    stopPromise.then(() => {
                        html5QrCode = null;
                        initScanner();
                    }).catch(() => {
                        html5QrCode = null;
                        initScanner();
                    });
                } else {
                    initScanner();
                }
            });
        }

        // function initScanner() {
        //     try {
        //         html5QrCode = new Html5Qrcode("qr-reader");
        //     } catch (e) {
        //         console.error("Failed to create Html5Qrcode:", e);
        //         $('#scan-message').text('Failed to initialize scanner.').css('color', 'red');
        //         return;
        //     }

        //     const config = {
        //         fps: 10,
        //         qrbox: {
        //             width: 250,
        //             height: 250
        //         }
        //     };
        //     $('#scan-message').text('Starting camera...').css('color', '');

        //     // Try rear camera first
        //     html5QrCode.start({
        //             facingMode: "environment"
        //         },
        //         config,
        //         onScanSuccess,
        //         onScanError
        //     ).then(() => {
        //         $('#scan-message').text('Point camera at barcode').css('color', 'green');
        //     }).catch(() => {
        //         console.warn("Rear camera failed, trying front camera...");
        //         html5QrCode.start({
        //                 facingMode: "user"
        //             },
        //             config,
        //             onScanSuccess,
        //             onScanError
        //         ).then(() => {
        //             $('#scan-message').text('Point camera at barcode').css('color', 'green');
        //         }).catch(() => {
        //             console.warn("Front camera also failed, trying any camera...");
        //             Html5Qrcode.getCameras().then(cameras => {
        //                 if (cameras && cameras.length > 0) {
        //                     html5QrCode.start(
        //                         cameras[0].id,
        //                         config,
        //                         onScanSuccess,
        //                         onScanError
        //                     ).then(() => {
        //                         $('#scan-message').text('Point camera at barcode').css(
        //                             'color', 'green');
        //                     }).catch(err => {
        //                         console.error("All camera attempts failed:", err);
        //                         html5QrCode = null;
        //                         showManualBarcodeInput();
        //                     });
        //                 } else {
        //                     html5QrCode = null;
        //                     showManualBarcodeInput();
        //                 }
        //             }).catch(() => {
        //                 html5QrCode = null;
        //                 showManualBarcodeInput();
        //             });
        //         });
        //     });
        // }
        function initScanner() {
            try {
                html5QrCode = new Html5Qrcode("qr-reader");
            } catch (e) {
                console.error("Failed to create Html5Qrcode:", e);
                return;
            }

            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            };

            $('#scan-message').text('Starting camera...');

            // 🔥 NEW CODE (IMPORTANT)
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {

                    let cameraId = devices.find(device =>
                        device.label.toLowerCase().includes('back') ||
                        device.label.toLowerCase().includes('rear')
                    )?.id;

                    // fallback
                    if (!cameraId) {
                        cameraId = devices[0].id;
                    }

                    html5QrCode.start(
                        cameraId,
                        config,
                        onScanSuccess,
                        onScanError
                    ).then(() => {
                        $('#scan-message').text('Point camera at barcode').css('color', 'green');
                    }).catch(err => {
                        console.error("Camera start failed:", err);
                    });

                } else {
                    console.log("No camera found");
                }
            }).catch(err => {
                console.error("Camera error:", err);
            });
        }

        function showManualBarcodeInput() {
            $('#scan-message').text('').hide();
            $('#qr-reader').html(`
        <div style="text-align:center; padding: 30px 20px;">
            <div style="font-size: 48px; margin-bottom: 10px;margin-top: 60px;"></div>
            <p style="color:#666; font-size:14px; margin-bottom:16px;">
                No camera found on this device.<br>Enter barcode manually below:
            </p>
            <div style="display:flex; gap:8px; justify-content:center;">
                <input type="text" id="manualBarcodeInput" class="form-control"
                    placeholder="Enter barcode / product code"
                    style="max-width:260px; font-size:14px;">
                <button type="button" class="btn btn-primary" id="manualBarcodeSubmit">Search</button>
            </div>
            <div id="manualBarcodeError" class="text-danger" style=" font-size:14px; margin-top:8px;"></div>
        </div>
                `);

            $('#qr-reader').off('click', '#manualBarcodeSubmit').on('click', '#manualBarcodeSubmit', function() {
                handleManualBarcode();
            });

            $('#qr-reader').off('keydown', '#manualBarcodeInput').on('keydown', '#manualBarcodeInput', function(e) {
                if (e.key === 'Enter') {
                    handleManualBarcode();
                }
            });
        }

        async function handleManualBarcode() {
            let barcode = $('#manualBarcodeInput').val().trim();

            if (!barcode) {
                $('#manualBarcodeError').text('Please enter a barcode.');
                return;
            }

            $('#manualBarcodeSubmit').prop('disabled', true).text('Searching...');
            $('#manualBarcodeError').text('');

            try {
                const product = await fetchProductByBarcode(barcode);

                document.activeElement && document.activeElement.blur();
                $('#barcodeScannerModal').modal('hide');
                $('#manualBarcodeInput').val('');

                if (product) {
                    addProductToCart(product);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Product Not Found',
                        text: 'No product with barcode: ' + barcode
                    });
                }
            } catch (error) {
                $('#manualBarcodeError').text('Product not found or error occurred.');
                // Swal.fire({
                //     icon: 'error',
                //     title: 'Error',
                //     text: 'Failed to fetch product. Please try again.'
                // });
            } finally {
                $('#manualBarcodeSubmit').prop('disabled', false).text('Search');
            }
        }

        function fetchProductByBarcode(barcode) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/api/product-by-barcode/' + encodeURIComponent(barcode),
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        resolve(response.status && response.product ? response.product : null);
                    },
                    error: function(xhr) {
                        reject(xhr.responseJSON?.message || 'Network error');
                    }
                });
            });
        }

        /**
         * Add product to cart (works for both barcode and manual search)
         * FIX: Ensure product ID is stored as a string so that Map keys match
         */
        /**
         * Add product to cart (works for barcode scan, manual entry, or any product source)
         * @param {Object} product - Product object from API or search
         */
        function addProductToCart(product) {
            // ----------------------------------------------------------------------
            // 1. Convert ID to both string (Map key) and number (DOM targeting)
            //    Trim any whitespace to avoid mismatches
            // ----------------------------------------------------------------------
            const rawId = String(product.id || '').trim();
            const numericId = parseInt(rawId, 10) || 0; // use parseInt for integers
            const stringId = String(numericId); // string key for Map

            // ----------------------------------------------------------------------
            // 2. Basic product data
            // ----------------------------------------------------------------------
            const productName = product.name || 'Unknown';
            const rawPrice = parseFloat(product.price) || 0;

            // ----------------------------------------------------------------------
            // 3. Price formatting (use global currency settings)
            // ----------------------------------------------------------------------
            const currencySymbol = '{{ $currency_symbol }}';
            const currencyPosition = '{{ $currency_position }}';
            const productPrice = currencyPosition === 'right' ?
                rawPrice.toFixed(2) + currencySymbol :
                currencySymbol + rawPrice.toFixed(2);

            // ----------------------------------------------------------------------
            // 4. Image handling (fallback to noimage.png)
            // ----------------------------------------------------------------------
            let productImage = '{{ env('ImagePath') }}/admin/assets/img/product/noimage.png';
            if (product.image) {
                try {
                    let cleanImage = product.image.replace(/\\/g, '').replace(/^"(.*)"$/, '$1');
                    let basePath = '{{ env('ImagePath') }}';
                    if (cleanImage.startsWith('[')) {
                        let imagesArray = JSON.parse(cleanImage);
                        if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                            productImage = `${basePath}/storage/${imagesArray[0]}`;
                        }
                    } else {
                        productImage = `${basePath}/storage/${cleanImage}`;
                    }
                } catch (e) {
                    console.warn('Image parse error, using default');
                }
            }

            // ----------------------------------------------------------------------
            // 5. GST option – ensure it's exactly "with_gst" or "without_gst"
            // ----------------------------------------------------------------------
            let gstOption = product.gst_option || 'without_gst';
            if (gstOption !== 'with_gst' && gstOption !== 'without_gst') {
                gstOption = 'without_gst';
            }

            // ----------------------------------------------------------------------
            // 6. GST data – parse JSON string to array if needed
            // ----------------------------------------------------------------------
            let productGst = product.product_gst || null;
            if (productGst) {
                if (typeof productGst === 'string') {
                    productGst = productGst.replace(/\\/g, '');
                    if (productGst === 'null' || productGst === '' || productGst === '[]') {
                        productGst = null;
                    } else {
                        try {
                            productGst = JSON.parse(productGst);
                        } catch (e) {
                            console.error('Failed to parse product_gst:', e);
                            productGst = null;
                        }
                    }
                }
                if (!Array.isArray(productGst) || productGst.length === 0) {
                    productGst = null;
                }
            }

            // If no valid GST data, force gst_option to 'without_gst'
            if (!productGst) {
                gstOption = 'without_gst';
            }

            // ----------------------------------------------------------------------
            // 7. Stock and category
            //    NOTE: If your API uses 'stock' instead of 'quantity', change here
            // ----------------------------------------------------------------------
            const stock = parseInt(product.quantity) || 0; // adjust field name if needed
            const categoryId = product.category_id || 0;

            // ----------------------------------------------------------------------
            // 8. Check if product already exists in cart (use string ID as key)
            // ----------------------------------------------------------------------
            if (selectedItems.has(stringId)) {
                let item = selectedItems.get(stringId);

                if (item.quantity < item.stock) {
                    item.quantity++;

                    let price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    let baseAmount = price * item.quantity;

                    if (item.discount_percentage > 0) {
                        item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                    }

                    selectedItems.set(stringId, item);
                    updateTotalItems();

                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit',
                        text: 'Cannot add more than available stock (' + item.stock + ')'
                    });
                }

                return;
            }

            // ----------------------------------------------------------------------
            // 9. Add new item (store both string ID for Map and numeric productId)
            // ----------------------------------------------------------------------
            selectedItems.set(stringId, {
                id: stringId, // string key (for Map)
                productId: numericId, // numeric original ID (for DOM targeting)
                categoryId: categoryId,
                name: productName,
                price: productPrice,
                image: productImage,
                code: "PT001", // placeholder, adjust if you have real code
                quantity: 1,
                stock: stock,
                gst_option: gstOption,
                product_gst: productGst,
                discount_percentage: 0,
                discount_amount: 0
            });

            // ----------------------------------------------------------------------
            // 10. Refresh the cart UI
            // ----------------------------------------------------------------------
            updateTotalItems();
        }

        let deviceScanPollTimer = null;
        let deviceScanRequestInFlight = false;
        let mobileScannerConnected = false;

        function setMobileScannerStatus(connected, deviceName = '') {
            const $status = $('#mobileScannerStatus');
            if (!$status.length) return;

            mobileScannerConnected = !!connected;
            $status.removeClass('is-checking is-connected is-disconnected');

            if (mobileScannerConnected) {
                const label = deviceName ? `Mobile scanner: ${deviceName}` : 'Mobile scanner: connected';
                $status
                    .text(label)
                    .addClass('is-connected');
            } else {
                $status
                    .text('Mobile scanner: disconnected')
                    .addClass('is-disconnected');
            }
        }

        async function processConnectedDeviceScans(scans) {
            if (!Array.isArray(scans) || scans.length === 0) {
                return;
            }

            for (const scan of scans) {
                const barcode = String(scan?.barcode || '').trim();
                if (!barcode) {
                    continue;
                }

                try {
                    const product = await fetchProductByBarcode(barcode);
                    if (product) {
                        addProductToCart(product);
                    }
                } catch (error) {
                    console.warn('Failed to process mobile scan:', barcode, error);
                }
            }
        }

        function pullConnectedDeviceScans() {
            if (deviceScanRequestInFlight) {
                return;
            }

            deviceScanRequestInFlight = true;

            $.ajax({
                url: '/pull-device-scans',
                type: 'GET',
                data: {
                    limit: 15
                },
                success: async function(response) {
                    if (!response || response.connected !== true) {
                        setMobileScannerStatus(false);
                        return;
                    }

                    setMobileScannerStatus(true, response.device_name || '');
                    await processConnectedDeviceScans(response.scans || []);
                },
                error: function() {
                    // Keep previous status; next polling cycle will refresh.
                },
                complete: function() {
                    deviceScanRequestInFlight = false;
                }
            });
        }

        function startConnectedDeviceScannerSync() {
            if (deviceScanPollTimer) {
                return;
            }

            $.get('/get-session-device', function(res) {
                setMobileScannerStatus(!!res.connected, res.device_name || '');
            }).fail(function() {
                // Keep initial "checking" state if this check fails once.
            });

            pullConnectedDeviceScans();
            deviceScanPollTimer = setInterval(pullConnectedDeviceScans, 1200);
        }

        // ==================== MODAL EVENT LISTENERS ====================
        $('#scanBarcodeBtn').on('click', function() {
            if (mobileScannerConnected) {
                Swal.fire({
                    icon: 'info',
                    title: 'Mobile scanner is active',
                    text: 'Scan products from your connected phone in Setting > Connected Devices. Products will auto-add here.',
                    timer: 1800,
                    showConfirmButton: false
                });
                return;
            }
            $('#barcodeScannerModal').modal('show');
        });

        $('#barcodeScannerModal').on('shown.bs.modal', function() {
            setTimeout(startScanner, 400);
        });

        $('#barcodeScannerModal').on('hidden.bs.modal', function() {
            stopScanner();
            $('#qr-reader').html('').css('min-height', '300px');
            $('#scan-message').text('Initializing camera...').show().css('color', '');
        });
    </script>
    <script>
        let labourItemsList = [];
        let authToken = localStorage.getItem("authToken");
        let selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        function loadLabourItems() {

            $.ajax({
                url: "/api/get-all-labour-items",
                type: "GET",
                dataType: "json",
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    if (response.status) {
                        labourItemsList = response.data;

                        $("#labour-items-container").html(""); // clear
                        addLabourRow(); // first row
                    }
                },
                error: function() {
                    // console.log("Labour items load failed");
                }
            });
        }

        function labourOptionsHtml() {

            let html = `<option value="">Select Labour</option>`;

            labourItemsList.forEach(item => {
                html += `
            <option value="${item.id}" data-price="${item.price}">
                ${item.item_name}
            </option>`;
            });

            return html;
        }

        function addLabourRow() {

            let row = `
                <div class="row gx-1 align-items-center labour-row mb-2">
                    <div class="col-5">
                        <select class="form-control labour-select select2-labour">
                            ${labourOptionsHtml()}
                        </select>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control labour-qty"
                            value="1" min="1">
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control labour-price"
                            placeholder="Price">
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-danger  remove-labour" style="display:none; width: 40px; height: 38px; padding: 0;">×</button>
                        <button type="button" class="btn btn-success  add-labour" style="width: 40px; height: 38px; padding: 0;">+</button>
                    </div>
                </div>
            `;

            let $row = $(row);
            $("#labour-items-container").append($row);
            $row.find(".select2-labour").select2();
            updateLabourButtons();
        }

        function updateLabourButtons() {
            let rows = $(".labour-row");
            rows.each(function(index) {
                if (index === rows.length - 1) {
                    $(this).find(".add-labour").show();
                    $(this).find(".remove-labour").hide();
                } else {
                    $(this).find(".add-labour").hide();
                    $(this).find(".remove-labour").show();
                }
            });
        }

        $(document).on("click", ".remove-labour", function() {
            $(this).closest(".labour-row").remove();
            updateLabourButtons();
            calculateTotals();
        });

        $(document).on("input change", ".labour-qty, .labour-price", function() {
            calculateTotals();
        });

        $(document).on("change", ".labour-select", function() {

            let price = $(this).find(":selected").data("price") || 0;

            $(this)
                .closest(".labour-row")
                .find(".labour-price")
                .val(price);

            calculateTotals(); // recalc bill
        });
        $(document).on("click", ".add-labour", function() {
            addLabourRow();
        });
        loadLabourItems();
    </script>
    <script>
        let selectedItems = new Map();

        document.querySelectorAll('.paymentmethod').forEach(el => {
            el.addEventListener('click', function() {
                document.querySelectorAll('input[name="payment_method"]').forEach(radio => radio.checked =
                    false);
                this.querySelector('input[type="radio"]').checked = true;
                document.querySelectorAll('.paymentmethod').forEach(item => item.classList.remove(
                    'active'));
                this.classList.add('active');
            });
        });
        $(document).ready(function() {
            $(".paymentmethod").on("click", function() {
                let value = $(this).find("input[name='payment_method']").val();

                $("input[name='payment_method']").prop("checked", false);
                $(this).find("input[name='payment_method']").prop("checked", true);

                // Clear bank error when switching payment methods
                $(".error_bank").text("");

                if (value === "cash+online") {
                    $("#cashOnlineBox").slideDown(); // show textboxes
                } else {
                    $("#cashOnlineBox").slideUp(); // hide textboxes
                }

                if (value === "debit card" || value === "scan" || value === "cash+online") {
                    $("#bankSelectionBox").slideDown();
                } else {
                    $("#bankSelectionBox").slideUp();
                }
            });

        });

        $(document).ready(function() {

            // hide payment buttons when quotation checked
            $(document).ready(function() {

                // $("#quotationToggle").on("change", function() {

                //     if ($(this).is(":checked")) {

                //         // Hide payment section
                //         $("#paymentSection").slideUp();

                //         // Optional: clear selected payment
                //         $("input[name='payment_method']").prop("checked", false);
                //         $(".paymentmethod").removeClass("active");

                //         // Hide extra boxes
                //         $("#cashOnlineBox").hide();
                //         $("#bankSelectionBox").hide();

                //     } else {

                //         // Show payment section again
                //         $("#paymentSection").slideDown();
                //     }
                // });
                $("#quotationToggle").on("change", function() {

                    if ($(this).is(":checked")) {

                        // ✅ mark quotation
                        $("#quotation_status").val("quotation");

                        // Hide payment UI
                        $("#paymentSection").slideUp();

                        // Clear payment selection
                        $("input[name='payment_method']").prop("checked", false);
                        $(".paymentmethod").removeClass("active");

                        $("#cashOnlineBox").hide();
                        $("#bankSelectionBox").hide();

                    } else {

                        // ✅ normal sale
                        $("#quotation_status").val("sales");

                        $("#paymentSection").slideDown();
                    }
                });

            });

            selectedItems = new Map();
            // console.log('authToken', authToken);
            // console.log('selectedSubAdminId', selectedSubAdminId);
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize tax rows as hidden on page load
            $(".taxList").hide();

            function formatCurrency(amount, symbol, position) {
                return position === 'right' ? amount + symbol : symbol + amount;
            }

            window.calculateTotals = function() {

                const firstTaxElement = $(".tax-value").first();
                const currencySymbol = firstTaxElement.data("symbol") || '{{ $currency_symbol }}';
                const currencyPosition = firstTaxElement.data("position") || '{{ $currency_position }}';

                let baseSubtotal = 0;
                let subtotalAfterDiscount = 0;
                let totalProductDiscount = 0;
                let totalProductGst = 0;
                let labourTotal = 0;

                const gstOption = $("input[name='gst_option']:checked").val();

                // Calculate Labour Total
                $(".labour-row").each(function() {
                    let qty = parseFloat($(this).find(".labour-qty").val()) || 0;
                    let price = parseFloat($(this).find(".labour-price").val()) || 0;
                    labourTotal += qty * price;
                });

                selectedItems.forEach(function(item) {

                    let price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    let baseAmount = price * item.quantity;

                    let productGst = 0;

                    // ===== GST CALCULATION =====
                    if (gstOption === "with" && item.gst_option === "with_gst" && item.product_gst) {
                        try {
                            const gstData = Array.isArray(item.product_gst) ?
                                item.product_gst :
                                JSON.parse(item.product_gst);

                            gstData.forEach(tax => {
                                productGst += baseAmount * (parseFloat(tax.tax_rate) / 100);
                            });

                        } catch (e) {
                            // console.error("GST error:", e);
                        }
                    }

                    // ✅ GST INCLUDED AMOUNT
                    let gstIncludedAmount = baseAmount + productGst;

                    // ===== DISCOUNT ON GST INCLUDED PRICE =====
                    let discountAmount = 0;

                    if (item.discount_percentage > 0) {
                        discountAmount =
                            (gstIncludedAmount * item.discount_percentage) / 100;
                    }

                    item.discount_amount = discountAmount;
                    selectedItems.set(item.id, item);

                    // ✅ FINAL PRODUCT AMOUNT AFTER DISCOUNT
                    let afterDiscountAmount = gstIncludedAmount - discountAmount;

                    // ===== TOTALS =====
                    baseSubtotal += baseAmount;
                    subtotalAfterDiscount += afterDiscountAmount;
                    totalProductDiscount += discountAmount;
                    totalProductGst += productGst;
                });
                // ================= GLOBAL DISCOUNT (REMOVED) =================
                let globalDiscountAmount = 0;
                let priceAfterGlobalDiscount = subtotalAfterDiscount - totalProductGst;

                // ================= FINAL TOTAL =================
                let shipping = parseFloat($("#shipping").val()) || 0;
                let finalTotal = priceAfterGlobalDiscount + totalProductGst + shipping + labourTotal;

                // ================= UI UPDATE =================

                $(".setvalue li:nth-child(1) h6").html(
                    formatCurrency(baseSubtotal.toFixed(2), currencySymbol, currencyPosition)
                );

                $(".discount-amount").html(
                    formatCurrency((totalProductDiscount + globalDiscountAmount).toFixed(2), currencySymbol,
                        currencyPosition)
                );

                $(".price-after-discount").html(
                    formatCurrency(priceAfterGlobalDiscount.toFixed(2), currencySymbol, currencyPosition)
                );

                $(".shipping-cost-summary").html(
                    formatCurrency(shipping.toFixed(2), currencySymbol, currencyPosition)
                );

                $(".total-value h6").html(
                    formatCurrency(finalTotal.toFixed(2), currencySymbol, currencyPosition)
                );

                $(".btn-totallabel h6").html(
                    'Total Amount : ' + formatCurrency(finalTotal.toFixed(2), currencySymbol,
                        currencyPosition)
                );

                $("input[name='subtotal']").val(baseSubtotal.toFixed(2));
                $("input[name='discount_amount']").val((totalProductDiscount + globalDiscountAmount).toFixed(
                    2));
                $("input[name='price_after_discount']").val(priceAfterGlobalDiscount.toFixed(2));
                $("input[name='total']").val(finalTotal.toFixed(2));
                $("#onlineAmount").val(finalTotal.toFixed(2));

                // ================= GST DISPLAY =================

                if (gstOption === "with") {
                    if ($(".gst-summary-row").length) {
                        $(".gst-summary-row")
                            .show()
                            .find("h6")
                            .html(formatCurrency(totalProductGst.toFixed(2), currencySymbol, currencyPosition));
                    }
                } else {
                    $(".gst-summary-row").hide();
                }

                // ================= LABOUR DISPLAY =================

                if (labourTotal > 0) {
                    $(".labour-summary-row")
                        .show()
                        .find("h6")
                        .html(formatCurrency(labourTotal.toFixed(2), currencySymbol, currencyPosition));
                } else {
                    $(".labour-summary-row").hide();
                }
            }


            window.updateTotalItems = function() {
                let totalQty = 0;
                selectedItems.forEach(item => {
                    totalQty += parseInt(item.quantity) || 0;
                });
                $(".totalitem h4").text("Total items : " + totalQty);
                renderSelectedItems();
                calculateTotals();
            }

            function renderSelectedItems() {
                try {
                    var $productTable = $(".product-table");
                    var productHtml = "";

                    // Get the global GST option
                    const globalGstOption = $("input[name='gst_option']:checked").val();

                    selectedItems.forEach(function(item) {
                        const hasProductGST = item.gst_option === "with_gst";
                        const gstRate = item.product_gst || null;

                        // Parse GST data if available AND global option is "with"
                        let gstDisplay = '';
                        let productGstTotal = 0;

                        const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                        const productTotal = price * item.quantity;

                        let gstIncludedTotal = productTotal;
                        let discountAmount = 0;
                        let finalProductTotal = productTotal;

                        if (globalGstOption === "with" && hasProductGST && gstRate) {

                            try {
                                const gstData = Array.isArray(gstRate) ? gstRate : JSON.parse(gstRate);

                                gstDisplay = gstData.map(tax => {
                                    const taxRate = parseFloat(tax.tax_rate) / 100;
                                    const taxAmount = productTotal * taxRate;


                                productGstTotal += taxAmount;
                                // console.log(productGstTotal);
                                return `<small class="d-block" style="font-size: 11px; color: #666;">
                                ${tax.tax_name}: ${tax.tax_rate}%
                                (${formatCurrency(taxAmount.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')})
                            </small>`;
                                }).join('');

                                // ✅ GST Included Total
                                gstIncludedTotal = productTotal + productGstTotal;

                                // ✅ Discount on GST Included Amount
                                discountAmount = (gstIncludedTotal * (item.discount_percentage || 0)) / 100;

                                finalProductTotal = gstIncludedTotal - discountAmount;

                            } catch (e) {
                                console.error("GST parse error:", e);
                            }

                        } else {

                            // ✅ Discount on Base Amount
                            discountAmount = (productTotal * (item.discount_percentage || 0)) / 100;

                            finalProductTotal = productTotal - discountAmount;
                        }



                        // Build HTML for the product
                        productHtml += `
                                    <ul class="product-lists">
                                        <li>
                                            <div class="productimg">
                                                <div class="productimgs">
                                                    <img src="${item.image}" alt="${item.name}">
                                                </div>
                                                <div class="productcontet">
                                                    <h4 style="text-transform: capitalize;">${item.name}
                                                        <a href="javascript:void(0);" class="ms-2 edit-item" data-id="${item.id}">
                                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit-5.svg' }}" alt="edit">
                                                        </a>
                                                    </h4>

                                                    ${(globalGstOption === "with" && hasProductGST && gstDisplay) ?
                                                        `<div class="gst-info" style="margin: 4px 0; padding: 8px; background: #f8f9fa; border-radius: 6px;">
                                                                                                                          ${gstDisplay}
                                                                                                                                     <small class="d-block" id="gstrates"
                                                                                                                                    data-value="${productGstTotal.toFixed(2)}" style="font-weight: bold; color: #333; margin-top: 4px; font-size: 12px;">
                                                                                                                         Product GST Total: ${formatCurrency(productGstTotal.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}
                                                                                                                                                        </small>
                                                                                                                             <small
                                                                                                                             class="d-block"
                                                                                                                                 id="gstamount"
                                                                                                                                data-value="${gstIncludedTotal.toFixed(2)}"
                                                                                                                               style="font-weight: bold; color: #333; margin-top: 4px; font-size: 12px;"
                                                                                                                          >
                                                                                                                                Product GST WITH Total:
                                                                                                                                    ${formatCurrency(gstIncludedTotal.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}
                                                                                                                                    </small>
                                                                                                                          </div>`
                                                        : ''
                                                    }

                                                    <div class="increment-decrement">
                                                        <div class="input-groups">
                                                            <input type="button" value="-" class="button-minus dec button" data-id="${item.id}">
                                                            <input type="text" name="quantity" value="${item.quantity}" class="quantity-field" data-id="${item.id}" data-stock="${item.stock}">
                                                            <input type="button" value="+" class="button-plus inc button" data-id="${item.id}">
                                                        </div>
                                                        <div style="font-size: 14px; color: #333; margin-bottom: 5px; margin-top:5px;">
                                                            <h4> ${item.price} </h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="product-discount-box">
                                                <div class="discount-field">
                                                    <label>Disc %</label>
                                                    <input type="text" class="product-discount-percentage" data-id="${item.id}" value="${item.discount_percentage || 0}" min="0" max="100">
                                                </div>
                                                   <div class="discount-field">
                                                <label>Disc Amt</label>
                                                <input type="text"
                                                    class="product-discount-amount"
                                                    data-id="${item.id}"
                                                    value="${item.discount_amount || 0}"
                                                    min="0"
                                                    step="0.01">
                                            </div>
                                            </div>
                                        </li>
                                     <li class="price">

    <div class="price-row sub-total" style="color:">
        <span>Sub Total:</span>
        <span>${formatCurrency(productTotal.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}</span>
    </div>

    ${(globalGstOption === "with" && hasProductGST) ? `
                                        <div class="price-row gst-inc">
                                            <span>GST Inc:</span>
                                            <span>${formatCurrency(gstIncludedTotal.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}</span>
                                        </div>` : ''}

    ${discountAmount > 0 ? `
                                        <div class="price-row discount">
                                            <span>Disc Amt:</span>
                                            <span>- ${formatCurrency(discountAmount.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}</span>
                                        </div>` : ''}

    <div class="price-row final-total">
        <span>Final Total:</span>
        <span>${formatCurrency(finalProductTotal.toFixed(2), '{{ $currency_symbol }}', '{{ $currency_position }}')}</span>
    </div>

</li>

                                        <li class="delete-col">
                                            <a href="javascript:void(0);" class="remove-item" data-id="${item.id}" title="Remove item">
                                                <img src="{{ env('ImagePath') }}/admin/assets/img/icons/delete-2.svg" alt="delete">
                                            </a>
                                        </li>
                                    </ul>
                                `;
                    });

                    $productTable.html(productHtml);
                } catch (e) {
                    console.error("Error in renderSelectedItems:", e);
                }
            }

            // Product Discount Percentage
            $(document).on("change", ".product-discount-percentage", function() {

                // let id = $(this).data("id");
                let id = $(this).attr('data-id');
                let percent = parseFloat($(this).val()) || 0;

                if (percent < 0) percent = 0;
                if (percent > 100) percent = 100;

                let item = selectedItems.get(id);

                let baseTotal = getItemBaseAmount(item); // ✅ GST aware

                let discountAmount = (baseTotal * percent) / 100;

                item.discount_percentage = percent;
                item.discount_amount = discountAmount;

                selectedItems.set(id, item);

                $(`.product-discount-amount[data-id="${id}"]`)
                    .val(discountAmount.toFixed(2));

                updateTotalItems();
            });

            // Product Discount Amount
            $(document).on("change", ".product-discount-amount", function() {

                // let id = $(this).data("id");
                let id = $(this).attr('data-id');
                let amount = parseFloat($(this).val()) || 0;

                if (amount < 0) amount = 0;

                let item = selectedItems.get(id);

                let baseTotal = getItemBaseAmount(item); // ✅ GST aware

                if (amount > baseTotal) amount = baseTotal;

                let percent = (amount / baseTotal) * 100;

                item.discount_percentage = percent;
                item.discount_amount = amount;

                selectedItems.set(id, item);

                $(`.product-discount-percentage[data-id="${id}"]`)
                    .val(percent.toFixed(2));

                updateTotalItems();
            });

            // Add this helper function if not already defined
            function formatCurrency(amount, symbol, position) {
                // Ensure the amount is a float and format it with commas
                let formattedAmount = parseFloat(amount).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                if (position === 'right') {
                    return formattedAmount + symbol;
                } else {
                    return symbol + formattedAmount;
                }
            }
            $(document).on("click", ".productset", function() {
                var $product = $(this);
                var productIdNum = $product.data("id");
                var productIdStr = String(productIdNum);
                var productName = $product.find("h4").text().trim();
                var productPrice = $product.find(".productsetcontent h6").text().trim();
                var categoryId = $product.find(".productsetcontent h1").text().trim();
                var productImage = $product.find("img").attr("src");
                var $checkIcon = $product.find(".check-product i");
                var productStock = parseInt($product.data("stock"));
                var gstOption = $product.data("gst-option") || "without_gst";

                // Get product GST data and parse it
                var productGst = $product.data("product-gst");
                if (productGst && productGst !== "null") {
                    try {
                        productGst = JSON.parse(productGst);
                    } catch (e) {
                        productGst = productGst;
                    }
                } else {
                    productGst = null;
                }

                // Check if product already exists in cart
                if (selectedItems.has(productIdStr)) {
                    let item = selectedItems.get(productIdStr);
                    if (item.quantity < item.stock) {
                        item.quantity++;

                        // Recalculate discount
                        let baseAmount = getItemBaseAmount(item);
                        if (item.discount_percentage > 0) {
                            item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                        }

                        selectedItems.set(productIdStr, item);
                        updateTotalItems();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stock Limit',
                            text: 'Cannot add more than available stock (' + item.stock + ')'
                        });
                    }
                    return;
                }

                // var uniqueId = productId;
                selectedItems.set(productIdStr, {
                    id: productIdStr,
                    productId: productIdNum,
                    categoryId: categoryId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    code: "PT001",
                    quantity: 1,
                    stock: productStock,
                    gst_option: gstOption,
                    product_gst: productGst,
                    discount_percentage: 0,
                    discount_amount: 0
                });

                // console.log("Selected item with GST:", {
                //     name: productName,
                //     gst_option: gstOption,
                //     product_gst: productGst
                // });

                if ($checkIcon.length) {
                    $checkIcon.addClass("selected");
                }
                updateTotalItems();
            });

            function getItemBaseAmount(item) {

                let price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                let productTotal = price * item.quantity;

                const globalGstOption = $("input[name='gst_option']:checked").val();

                if (
                    globalGstOption === "with" &&
                    item.gst_option === "with_gst" &&
                    item.product_gst
                ) {
                    try {
                        const gstData = Array.isArray(item.product_gst) ?
                            item.product_gst :
                            JSON.parse(item.product_gst);

                        let productGstTotal = 0;

                        gstData.forEach(tax => {
                            productGstTotal += productTotal * (parseFloat(tax.tax_rate) / 100);
                        });

                        return productTotal + productGstTotal; // ✅ GST Included Amount

                    } catch (e) {
                        return productTotal;
                    }
                }

                return productTotal; // Without GST
            }

            $(document).on("click", ".button-plus", function() {

                // var itemId = $(this).data("id");
                var itemId = $(this).attr('data-id');

                if (!selectedItems.has(itemId)) return;

                let item = selectedItems.get(itemId);

                if (item.quantity >= item.stock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Limit Reached',
                        text: 'Cannot exceed available stock (' + item.stock + ')',
                        confirmButtonColor: '#ff9f43',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Increase quantity
                item.quantity++;

                // 🔥 Recalculate discount properly
                let baseAmount = getItemBaseAmount(item);

                if (item.discount_percentage > 0) {
                    item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                } else if (item.discount_amount > 0) {
                    item.discount_percentage = (item.discount_amount / baseAmount) * 100;
                }

                selectedItems.set(itemId, item);

                updateTotalItems();
            });

            $(document).on("change", ".quantity-field", function() {

                // var itemId = $(this).data("id");
                var itemId = $(this).attr('data-id');
                var newQty = parseInt($(this).val());

                if (!selectedItems.has(itemId)) return;

                let item = selectedItems.get(itemId);

                if (isNaN(newQty) || newQty < 1) {
                    newQty = 1;
                }

                if (newQty > item.stock) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Quantity Exceeded',
                        text: 'Only ' + item.stock + ' quantity are available.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    newQty = item.stock;
                }

                item.quantity = newQty;

                // 🔥 Recalculate discount properly
                let baseAmount = getItemBaseAmount(item);

                if (item.discount_percentage > 0) {
                    item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                } else if (item.discount_amount > 0) {
                    item.discount_percentage = (item.discount_amount / baseAmount) * 100;
                }

                selectedItems.set(itemId, item);

                $(this).val(newQty);

                updateTotalItems();
            });

            $(document).on("click", ".button-minus", function() {

                // var itemId = $(this).data("id");
                var itemId = $(this).attr('data-id');

                if (!selectedItems.has(itemId)) return;

                let item = selectedItems.get(itemId);

                if (item.quantity <= 1) return;

                // Decrease quantity
                item.quantity--;

                // 🔥 Recalculate discount properly
                let baseAmount = getItemBaseAmount(item);

                if (item.discount_percentage > 0) {
                    item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                } else if (item.discount_amount > 0) {
                    item.discount_percentage = (item.discount_amount / baseAmount) * 100;
                }

                selectedItems.set(itemId, item);

                updateTotalItems();
            });

            $(document).on("click", ".remove-item", function(e) {
                e.preventDefault();
                var itemId = $(this).attr('data-id'); // string key
                var item = selectedItems.get(itemId);
                if (item) {
                    $(".productset[data-id='" + item.productId + "'] .check-product i").removeClass(
                        "selected");
                    selectedItems.delete(itemId);
                    updateTotalItems();
                }
            });

            $(".clear_items").click(function() {
                selectedItems.clear();
                $(".check-product i").removeClass("selected");
                updateTotalItems();
            });

            $("#shipping").on("input", function() {
                calculateTotals();
            });



            $(document).on("change", "input[name='gst_option']", function() {

                selectedItems.forEach(function(item, id) {

                    let baseAmount = getItemBaseAmount(item); // GST aware amount

                    if (item.discount_percentage > 0) {
                        item.discount_amount = (baseAmount * item.discount_percentage) / 100;
                    } else if (item.discount_amount > 0) {
                        item.discount_percentage = (item.discount_amount / baseAmount) * 100;
                    }

                    selectedItems.set(id, item);
                });

                updateTotalItems(); // 🔥 re-render + re-calc totals
            });
            $(document).on("change", "input[name='gst_option']", function() {
                let gstOption = $("input[name='gst_option']:checked").val();


                if (gstOption === "with") {
                    $(".taxList").show();
                } else {
                    $(".taxList").hide();
                }

                // Update product display and recalc totals
                renderSelectedItems();
                calculateTotals();
            });
            $(".btn-totallabel").click(function(event) {
                // ================= LABOUR ITEMS =================
                let labourItems = [];

                $(".labour-row").each(function() {

                    let labourId = $(this).find(".labour-select").val();
                    let qty = parseFloat($(this).find(".labour-qty").val()) || 0;
                    let price = parseFloat($(this).find(".labour-price").val()) || 0;

                    if (labourId && qty > 0) {

                        labourItems.push({
                            labour_item_id: labourId,
                            qty: qty,
                            price: price,
                            total: qty * price
                        });
                    }
                });

                event.preventDefault();
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Placing Order...'
                ).prop('disabled', true);

                // Reset errors
                $(".error_customername, .error_customerphone, .error_peymentmethod, .error_discount, .error_bank")
                    .text(
                        "");
                $(".total-value .error_total").remove();

                // Get form values
                // let name = $("input[name='customer_name']").val().trim();
                let name = $("#customer_name option:selected").val();
                let phone = $("input[name='customer_phone']").val().trim();
                let selectedPayment = $("input[name='payment_method']:checked").val();
                let bank_id = $("#bank_id").val();
                let subtotal = parseFloat($("input[name='subtotal']").val()) || 0;
                let total = parseFloat($("input[name='total']").val()) || 0;
                let discount_amount = parseFloat($("input[name='discount_amount']").val()) || 0;
                let remarks = $("#remarks").val().trim();
                let shipping = parseFloat($("#shipping").val()) || 0;
                // Validation
                let isValid = true;

                // if (name === "") {
                //     $(".error_customername").text("Customer name is required").css("color", "red");
                //     isValid = false;
                // }

                if (phone === "") {
                    // $(".error_customerphone").text("Customer number is required").css("color", "red");
                    // isValid = false;
                } else if (!/^\d{10}$/.test(phone)) {
                    $(".error_customerphone").text("Enter a valid 10-digit phone number").css("color",
                        "red");
                    isValid = false;
                }

                // Determine if this is a quotation or a sale
                const quotationStatus = $(".quotationToggle:checked").length > 0 ? 'quotation' : 'sales';

                // only require a payment method for actual sales
                if (quotationStatus !== 'quotation') {
                    if (!selectedPayment) {
                        $(".error_peymentmethod").text("Please select a payment method").css("color",
                            "red");
                        isValid = false;
                    }
                }

                // when quotation, we intentionally skip the generic check below
                // so do not run the unconditional validation block


                if (quotationStatus !== 'quotation' &&
                    (selectedPayment === "debit card" || selectedPayment === "scan" || selectedPayment ===
                        "cash+online") &&
                    !bank_id) {

                    $(".error_bank").text("Please select a bank").css("color", "red");
                    isValid = false;
                }

                // if (subtotal <= 0) {
                //     $(".total-value").append("<span class='error_total' style='color:red; display:block;'>Please select at least one product</span>");
                //     isValid = false;
                // }
                if (subtotal <= 0) {
                    $(".error_total")
                        .text("Please select at least one product")
                        .show();
                    isValid = false;
                } else {
                    $(".error_total").hide().text("");
                }


                // if (!isValid) return;
                if (!isValid) {
                    $btn.html(originalText).prop('disabled', false); // <-- important line
                    return;
                }


                // Prepare tax data
                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                // Prepare order items data
                // In the order submission section
                let orderItems = [];
                selectedItems.forEach(function(item) {
                    const price = parseFloat(item.price.replace(/[^0-9.-]+/g, ""));
                    const itemTotal = price * item.quantity;

                    // Calculate product GST if applicable
                    let productGstDetails = [];
                    let productGstTotal = 0;

                    if (gstOption === "with" && item.gst_option === "with_gst" && item
                        .product_gst) {
                        try {

                            const gstData = Array.isArray(item.product_gst) ? item.product_gst :
                                JSON.parse(item.product_gst);
                            gstData.forEach(tax => {
                                const taxRate = parseFloat(tax.tax_rate) / 100;
                                const taxAmount = itemTotal * taxRate;
                                productGstTotal += taxAmount;

                                productGstDetails.push({
                                    tax_name: tax.tax_name,
                                    tax_rate: tax.tax_rate,
                                    tax_amount: taxAmount
                                });
                            });
                        } catch (e) {
                            // console.error("Error calculating product GST:", e);
                        }
                    }

                    orderItems.push({
                        product_id: item.productId,
                        quantity: item.quantity,
                        categoryId: item.categoryId,
                        price: price,
                        discount_percentage: item.discount_percentage || 0,
                        discount_amount: item.discount_amount || 0,
                        total: itemTotal,
                        gst_option: item.gst_option,
                        product_gst_details: productGstDetails,
                        product_gst_total: productGstTotal,
                        final_price: itemTotal + productGstTotal
                    });
                });

                // Prepare tax data
                // let taxes = [];
                // $(".taxList").each(function () {

                //     const taxName = $(this).find("h5").text().replace(" Tax", "");
                //     const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g, ""));


                //     const taxRate = parseFloat($(this).find(".tax-value").data("rate"));
                //     taxes.push({
                //         tax_name: taxName,
                //         rate: taxRate,
                //         amount: taxAmount
                //     });
                // });
                if (gstOption === "with") {
                    $(".taxList").each(function() {
                        const taxName = $(this).find("h5").text().replace(" Tax", "");
                        const taxAmount = parseFloat($(this).find("h6").text().replace(/[^0-9.]/g,
                            "")) || 0;
                        const taxRate = parseFloat($(this).find(".tax-value").data("rate")) || 0;

                        taxes.push({
                            tax_name: taxName,
                            rate: taxRate,
                            amount: taxAmount
                        });
                    });
                } else {
                    taxes = []; // ✅ explicitly empty when "without GST"
                }


                let cash = parseFloat($("#cashAmount").val()) || 0;
                let online = parseFloat($("#onlineAmount").val()) || 0;



                // Prepare order data
                // In the order submission section, add gst_total to orderData
                // build base payload
                let orderData = {
                    selectedSubAdminId: selectedSubAdminId,
                    customer_id: name,
                    customer_phone: phone,
                    subtotal: subtotal,
                    discount: 0,
                    discount_amount: discount_amount,
                    tax: taxes,
                    gst_option: gstOption,
                    total: total,
                    items: orderItems,
                    remarks: remarks,
                    shipping: shipping,
                    labour_items: labourItems,
                    quotation_status: quotationStatus, // ✅ IMPORTANT
                };

                // add payment fields only when this is a sale
                if (quotationStatus === 'sales') {
                    orderData.payment_method = selectedPayment;
                    orderData.bank_id = bank_id;
                    orderData.cash_amount = cash;
                    orderData.online_amount = online;
                }



                // Submit order via AJAX
                $.ajax({
                    url: "/api/order_sale",
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function(xhr) {
                        // Ensure CSRF token is included
                        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr(
                            'content'));
                    },
                    data: JSON.stringify(orderData),
                    success: function(response) {
                        // Re-enable and reset the button
                        $btn.html(originalText).prop('disabled', false);
                        if (response.status) {
                            orderId = response.order_id;
                            // alert("Order placed successfully!");
                            // location.reload();
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // Set custom button color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open("/sales/invoice/pdf/" + orderId,
                                        "_blank");
                                    window.location.href = "/sales-invoice/" + orderId;
                                }
                            });
                            // Reset form
                            selectedItems.clear();
                            $(".check-product i").removeClass("selected");
                            $("input[name='customer_name'], input[name='customer_phone']").val(
                                "");
                            $("input[name='payment_method']").prop("checked", false);
                            $(".paymentmethod").removeClass("active");
                            updateTotalItems();
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    // error: function(xhr, status, error) {
                    //     // Re-enable and reset the button
                    //     $btn.html(originalText).prop('disabled', false);

                    //     alert("Error placing order. Please try again.");
                    // }
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        // Try to extract error message from response
                        let response = xhr.responseJSON;

                        if (response && response.message && response.message.includes(
                                "Duplicate entry")) {
                            // Detect duplicate phone number error
                            $(".error_customerphone").text(
                                "This phone number is already registered. Please use another one."
                            ).css("color", "red");
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response && response.message ? response.message :
                                    "Error placing order. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            $(".owl-carousel").owlCarousel({
                items: 5,
                loop: false,
                nav: true,
                dots: false,
                margin: 10
            });

            // Handle category click
            $(".tabs li").click(function() {
                var categoryId = $(this).attr('id');


                // Remove active class from all tabs and tab contents
                $(".tabs li").removeClass('active');
                $(".tab_content").removeClass('active');

                // Add active class to clicked tab
                $(this).addClass('active');

                // Find the corresponding tab content
                var tabContentSelector = '.tab_content[data-tab="' + categoryId + '"]';
                var $tabContent = $(tabContentSelector);

                if ($tabContent.length) {
                    $tabContent.addClass('active');


                    // Check if content already loaded
                    if ($tabContent.find('.productset').length === 0) {
                        loadProductsByCategory(categoryId);
                    }
                } else {

                    // Create new tab content if not found
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + categoryId +
                        '"></div>');
                    loadProductsByCategory(categoryId);
                }
            });

            // Function to load products by category
            function loadProductsByCategory(categoryId) {

                $.ajax({
                    url: "/api/getProductsByCategory/" + categoryId,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        // console.log('respone:', response)
                        const currencySymbol = response.currency_symbol || 'Ã¢â€šÂ¹';
                        const currencyPosition = response.currency_position || 'left';


                        if (response.status) {
                            renderProducts(response.data, categoryId, currencySymbol, currencyPosition);
                        } else {
                            renderProducts([], categoryId, currencySymbol, currencyPosition);
                        }
                    },
                    error: function(xhr, status, error) {

                        renderProducts([], categoryId);
                    }
                });
            }

            // Function to render products
            function renderProducts(products, categoryId, currencySymbol, currencyPosition) {

                var productsHtml = '';

                if (products.length === 0) {
                    productsHtml = '<div class="col-12"><p>No products found in this category</p></div>';
                } else {
                    products.forEach(function(product) {
                        // Default image
                        let productImage =
                            '{{ env('ImagePath') . '/admin/assets/img/product/noimage.png' }}';

                        // Handle different image formats
                        if (product.image) {
                            try {
                                // Clean the image string
                                let cleanImageString = product.image.replace(/\\/g, '');
                                cleanImageString = cleanImageString.replace(/^"(.*)"$/, '$1');

                                let imageBasePath =
                                    '{{ env('ImagePath') }}'; // or config('app.url') if you prefer

                                if (cleanImageString.startsWith('[')) {
                                    let imagesArray = JSON.parse(cleanImageString);
                                    if (Array.isArray(imagesArray) && imagesArray.length > 0) {
                                        productImage = `${imageBasePath}/storage/${imagesArray[0]}`;
                                    }
                                } else {
                                    productImage = `${imageBasePath}/storage/${cleanImageString}`;
                                }
                            } catch (e) {

                            }
                        }
                        // Parse GST data
                        let gstBadge = '';
                        let gstOption = product.gst_option || "without_gst";
                        let productGst = product.product_gst || null;

                        if (gstOption === "with_gst" && productGst) {
                            try {
                                const gstData = JSON.parse(productGst);
                                const totalRate = gstData.reduce((sum, tax) => sum + parseFloat(tax
                                    .tax_rate), 0);
                                gstBadge = `<span class="gst-hover-badge with-gst">
                        GST: ${totalRate}%
                    </span>`;
                            } catch (e) {
                                gstBadge = `<span class="gst-hover-badge with-gst">
                        With GST
                    </span>`;
                            }
                        } else {
                            gstBadge = `<span class="gst-hover-badge no-gst">
                    No GST
                </span>`;
                        }

                        // Format price
                        let displayPrice = formatCurrency(product.price || 0, currencySymbol,
                            currencyPosition);

                        // Check if product is out of stock
                        let outOfStockBadge = '';
                        let disabledClass = '';
                        let addButtonDisabled = '';

                        if (Number(product.quantity) === 0) {
                            outOfStockBadge = `<div style="
                position: absolute;
                top: 10px;
                left: 10px;
                background: rgba(255,0,0,0.85);
                color: white;
                font-weight: 700;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 10;
                ">Out of Stock</div>`;

                            disabledClass = 'disabled-product';
                            addButtonDisabled = 'disabled';
                        }

                        // ✅ ADD data-gst-option and data-product-gst ATTRIBUTES HERE
                        productsHtml += `
                <div class="col-lg-3 col-sm-6 d-flex position-relative">
                    <div class="productset flex-fill text-center ${disabledClass}"
                         data-id="${product.id}"
                         data-stock="${product.quantity}"
                         data-gst-option="${gstOption}"
                         data-product-gst='${JSON.stringify(productGst).replace(/'/g, "&#39;")}'
                         style="position: relative;">
                        ${outOfStockBadge}
                        <div class="productsetimg">
                            <img src="${productImage}" alt="${product.name}" class="productsetimgin">
                            <h6>Qty: ${product.quantity || '0'}</h6>
                            ${gstBadge}
                        </div>
                        <div class="productsetcontent" style="padding: 0.5rem;">
                            <h4 style="font-size: 14px;text-transform: capitalize; font-weight: 600; margin: 0.5rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                ${product.name}
                            </h4>
                             <h5 class="text-dark" style="text-transform: capitalize; ">${product.unit || ''}</h5>
                            <h1 class="d-none">${product.categoryId || 'Uncategorized'}</h1>
                            <h5 style="text-transform: capitalize;">${product.category_name || 'Uncategorized'}</h5>

                            <h6>${displayPrice}</h6>
                        </div>
                        <div class="productsetbtn">
                            <button class="btn btn-added" ${addButtonDisabled}>+ Add</button>
                        </div>
                    </div>
                </div>
            `;
                    });
                }

                // Update the tab content
                var $tabContent = $('.tab_content[data-tab="' + categoryId + '"]');
                if ($tabContent.length) {
                    $tabContent.html('<div class="row">' + productsHtml + '</div>');

                } else {

                }
            }



            // Load products for the first category by default
            var firstCategory = $(".tabs li:first");
            if (firstCategory.length) {
                var firstCategoryId = firstCategory.attr('id');
                firstCategory.addClass('active');
                var firstTabContent = $('.tab_content[data-tab="' + firstCategoryId + '"]');

                if (firstTabContent.length) {
                    firstTabContent.addClass('active');
                } else {
                    // Create tab content if it doesn't exist
                    $('.tabs_container').append('<div class="tab_content active" data-tab="' + firstCategoryId +
                        '"></div>');
                }

                // Load data for first category
                loadProductsByCategory(firstCategoryId);
            }


            // Initialize with empty state
            startConnectedDeviceScannerSync();
            updateTotalItems();
        });
        $(".select2").select2({
            tags: true,
        });
        // $(document).ready(function() {
        $('#customer_name').on('change', function() {
            var phone = $(this).find(':selected').data('phone');
            $('#customer_phone').val(phone || '');
        });
        // });
        $(document).ready(function() {
            const $searchInput = $('#customerSearch1');
            const $resultBox = $('#searchResults1');

            $searchInput.on('input', function() {
                const query = $(this).val().trim();

                if (query.length < 1) {
                    $resultBox.hide();
                    return;
                }

                $.ajax({
                    url: `/search-users`,
                    method: 'GET',
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    data: {
                        query: query
                    },
                    dataType: 'json',
                    success: function(data) {
                        $resultBox.empty();

                        let hasResults = false;

                        // Users section
                        // (add user search results here if needed)

                        // Products section
                        if (data.products && data.products.length > 0) {
                            hasResults = true;
                            $resultBox.append(
                                `<div class="list-group-item fw-bold mt-2">Products</div>`);
                            data.products.forEach(product => {
                                // In the search AJAX success handler, update the product button:
                                $resultBox.append(`
                                <div class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center">
                                        <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:30px; height:30px; object-fit: cover;">
                                        <div>
                                            <strong>${product.name ?? 'N/A'}</strong><br>
                                            <small>Price: ${product.price ?? 'N/A'}</small>
                                            ${product.gst_option === 'with_gst' ?
                                                `<br><small style="color: green;">With GST</small>` :
                                                `<br><small style="color: gray;">Without GST</small>`
                                            }
                                        </div>
                                    </div>
                                    <button class="btn btn-sm btn-primary add-product"
                                        data-id="${product.id}"
                                        data-name="${product.name}"
                                        data-price="${product.price}"
                                        data-image="${product.image}"
                                        data-stock="${product.stock ?? 10}"
                                        data-category="${product.category_id ?? 0}"
                                        data-gst-option="${product.gst_option || 'without_gst'}"
                                        data-product-gst='${product.product_gst || ''}'>+ Add</button>
                                </div>
                            `);
                            });
                        }

                        // Orders section
                        // (add orders section if needed)

                        if (!hasResults) {
                            $resultBox.html(
                                '<div class="list-group-item">No results found</div>');
                        }

                        $resultBox.show();
                    },
                    error: function(xhr, status, error) {

                        $resultBox.hide();
                    }
                });
            });

            $(document).on("click", ".add-product", function() {
                const $btn = $(this);
                const productIdNum = $btn.data("id"); // number
                const productIdStr = String(productIdNum); // string key
                const productName = $btn.data("name");
                const productPrice = $btn.data("price");
                const productImage = $btn.data("image");
                const productStock = parseInt($btn.data("stock"));
                const categoryId = $btn.data("category");
                const gstOptionItem = $btn.data("gst-option") || "without_gst";
                let productGst = $btn.data("product-gst") || null;

                if (productGst && productGst !== "null" && typeof productGst === 'string' && productGst
                    .startsWith('[')) {
                    try {
                        productGst = JSON.parse(productGst);
                    } catch (e) {
                        // console.error("Error parsing GST data:", e);
                    }
                }

                if (!selectedItems.has(productIdStr)) {
                    selectedItems.set(productIdStr, {
                        id: productIdStr,
                        productId: productIdNum,
                        categoryId: categoryId,
                        name: productName,
                        price: productPrice,
                        image: productImage,
                        code: "PT001",
                        quantity: 1,
                        stock: productStock,
                        gst_option: gstOptionItem,
                        product_gst: productGst,
                        discount_percentage: 0,
                        discount_amount: 0
                    });
                    $btn.prop("disabled", true).text("Added");
                    updateTotalItems();
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Already Added',
                        text: 'This product is already in the list!',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            });

            $(document).on('change', '.quotationToggle', function() {

                let isChecked = $(this).is(':checked');

                $('.quotationToggle').prop('checked', isChecked);

                if (isChecked) {
                    $('.setvaluecash').hide();
                } else {
                    $('.setvaluecash').show();
                }

            });
            // Optional: Hide dropdown on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest($searchInput).length && !$(e.target).closest($resultBox).length) {
                    $resultBox.hide();
                }
            });
        });
    </script>
@endpush
 
