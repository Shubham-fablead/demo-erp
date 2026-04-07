@extends('layout.app')

@php
    $purchaseType = $purchaseType ?? 'product';
    $isRowMaterialPurchase = $purchaseType === 'row-material';
    $pageTitle = $isRowMaterialPurchase ? 'Add Row Material Purchase' : 'Add Purchase';
    $itemLabel = $isRowMaterialPurchase ? 'Row Material Name' : 'Product Name';
    $itemPriceLabel = $isRowMaterialPurchase ? 'Row Material Price' : 'Product Price';
    $totalItemLabel = $isRowMaterialPurchase ? 'Total Row Material Amount' : 'Total Product Amount';
    $purchaseSubmitUrl = $isRowMaterialPurchase ? '/api/row-material-purchase_order' : '/api/purchase_order';
    $purchaseRedirectUrl = $isRowMaterialPurchase ? route('purchase.row_material.lists') : '/print-purchase/';
    $itemOptions = $isRowMaterialPurchase ? ($rowMaterialsArray ?? []) : ($productsArray ?? []);
@endphp

@section('title', $pageTitle)

@section('content')

    <style>
        .gst-header {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            padding: 8px 0 0;
        }

        .custom-radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #333;
            user-select: none;
            position: relative;
            padding-left: 24px;
            margin: 0;
            white-space: nowrap;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: color 0.2s ease;
        }

        .custom-radio-label input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #888;
            border-radius: 50%;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .custom-radio-label input[type="radio"]:checked {
            border-color: #0056b3;
            background-color: #0056b3;
        }

        .custom-radio-label input[type="radio"]:checked::after {
            content: "";
            display: block;
            width: 7px;
            height: 7px;
            background: white;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .custom-radio-label:hover input[type="radio"] {
            border-color: #5a50cc;
        }

        .custom-radio-label:hover {
            color: #0056b3;
        }

        .manage_btn {
            color: #fff;
            background: #1b2850;
        }

        .manage_btn:hover {
            color: #e6e4e4ff;
        }

        .form-row {
            border: 1px solid #fdc794;
            padding: 20px 10px 10px 10px;
            margin-bottom: 20px;
            border-radius: 10px;
            background: #fdfdfd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .add-row-btn {
            margin-top: 28px;
        }

        .row-material-hint {
            margin-top: 8px;
            font-size: 12px;
            color: #5b6670;
            line-height: 1.5;
        }

        .row-material-chip {
            display: inline-block;
            margin: 4px 6px 0 0;
            padding: 4px 10px;
            border-radius: 999px;
            background: #fff3e6;
            color: #c66a00;
            font-weight: 600;
            font-size: 11px;
        }

        .purchase-tabs {
            display: flex;
            gap: 12px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .purchase-tab {
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

        .purchase-tab:hover {
            color: #ff9f43;
            border-color: #ff9f43;
        }

        .purchase-tab.active {
            background: #ff9f43;
            border-color: #ff9f43;
            color: #fff;
        }

        @media screen and (max-width: 768px) {
            .purchase-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                flex-wrap: wrap;
                gap: 8px;
            }

            .purchase-header .page-title h4 {
                font-size: 16px;
                margin-bottom: 0;
            }

            .gst-header {
                display: flex;
                align-items: center;
                width: auto;
                padding: 0;
                gap: 14px;
                flex-wrap: wrap;
            }

            .custom-radio-label {
                font-size: 13px;
                font-weight: 500;
                padding-left: 22px;
                gap: 6px;
            }

            .custom-radio-label input[type="radio"] {
                width: 15px;
                height: 15px;
            }

            .custom-radio-label input[type="radio"]:checked::after {
                width: 6px;
                height: 6px;
            }

            .add-row-btn {
                margin-top: 0;
                margin-bottom: 1rem;
            }

            .add-row {
                width: 100%;
                text-align: center;
            }

            .remove-row {
                width: 100%;
                text-align: center;
            }

            .form-group {
                margin-bottom: 10px !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header purchase-header">
            <div class="page-title">
                <h4>{{ $pageTitle }}</h4>
                <div class="purchase-tabs">
                    <a href="{{ route('purchase.add') }}" class="purchase-tab {{ $isRowMaterialPurchase ? '' : 'active' }}">Product Purchase</a>
                    <a href="{{ route('purchase.row_material.add') }}" class="purchase-tab {{ $isRowMaterialPurchase ? 'active' : '' }}">Row Material Purchase</a>
                </div>
            </div>
            <div class="gst-header">

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

        <div class="card">

            <div class="card-body">
                <div class="">
            <div class="row d-flex ">

    <div class="col-lg-6 col-sm-6 col-6">
        <div class="form-group">
            <label>Vendor Name</label>
            <select id="vendor_name" name="vendor_id"
                class="form-control select2 vendor-select w-100">
                <option value="">Select Vendor</option>
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}" data-phone="{{ $vendor->phone }}">
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            <span class="error text-danger"></span>
        </div>
    </div>

    <div class="col-lg-6 col-sm-6 col-6">
        <div class="form-group">
            <label>Bill No.</label>
            <input type="text" name="bill_no" id="bill_no"
                class="form-control" placeholder="Bill No">
            <span class="error text-danger"></span>
        </div>
    </div>

</div>

                    <div class="col-lg-6 col-sm-12 d-none">
                        <div class="form-group">
                            <label>Vendor Phone</label>
                            <input type="number" id="vendor_phone" name="phone" class="form-control"
                                placeholder="Enter Phone">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                </div>
                {{-- <div class="mt-2"> --}}
                <div class="row form-row">

                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Category Name</label>
                            <select id="category_name" name="category_name[]" class="form-control select2 category-select">
                                <option value="">Category Name</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" data-price="{{ $category->price }}">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label>{{ $itemLabel }}</label>
                            <select id="product_name" name="product_name[]" class="form-control select2 product-select"
                                disabled>
                                <option value="">{{ $itemLabel }}</option>
                                @foreach ($itemOptions as $product)
                                    <option value="{{ $product['id'] }}" data-price="{{ $product['price'] }}"
                                        data-category="{{ $product['category_id'] }}"
                                        data-gst-option="{{ $product['gst_option'] ?? 'without_gst' }}" data-gst='@json($product['product_gst'] ?? null)'>
                                        {{ $product['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="product-gst-info mt-1"></div>

                            <span class="error text-danger product-error"></span>
                            @unless ($isRowMaterialPurchase)
                                <div class="row-material-hint"></div>
                            @endunless
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-6">
                        <div class="form-group">
                            <label>{{ $itemPriceLabel }}</label>
                            <input type="text" name="price[]" class="form-control price-input" placeholder="Enter Price"
                                min="0" oninput="this.value = this.value < 0 ? 0 : this.value" inputmode="decimal"
                                step="0.01">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Qty</label>
                            <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Qty"
                                value="1" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Disc%</label>
                            <input type="number" name="product_discount[]" class="form-control product-discount-input"
                                placeholder="0.00" value="0" min="0" max="100"
                                oninput="this.value = this.value < 0 ? 0 : (this.value > 100 ? 100 : this.value)">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Disc-Amt</label>
                            <input type="text" name="product_discount_amount[]"
                                class="form-control product-discount-amount-input" placeholder="0.00" value="0"
                                min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-12 col-12">
                        <div class="form-group">
                            <label>Total Amount</label>
                            <input type="text" name="total[]" class="form-control total-input" placeholder="0"
                                readonly>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12 add-row-btn">
                        <button type="button" class="btn btn-success add-row">+</button>
                    </div>
                </div>
                <div id="form-container"></div>
                {{-- </div> --}}

                <div class="row">

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="text" name="shipping" id="shipping" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Purchase Status</label>
                            <select name="status" class="form-control purchase-status-select">
                                <option value="">Choose Status</option>
                                <option value="pending">Pending</option>
                                <option value="partially">Partially</option>
                                <option value="completed">Completed</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-2 col-6">
                        <div class="form-group">
                            <label>Payment Mode</label>
                            <select name="payment_mode" id="payment_mode" class="form-control payment-mode-select">
                                <option value="">Select Payment Mode</option>
                                <option value="pending">Pending</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cashonline">Cash + Online</option>
                            </select>
                            <div class="text-danger error-payment_mode"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="paid_type_container">
                        <div class="form-group">
                            <label>Paid Type</label>
                            <select id="paid_type" name="paid_type" class="form-control paid-type-container">
                                <option value="">Select Paid Type</option>
                                <option value="full">Fully Paid</option>
                                <option value="partial">Partially Paid</option>
                            </select>
                            <div class="text-danger error-paid_type"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="cash_amount_input_container">
                        <div class="form-group">
                            <label>Cash Amount</label>
                            <input type="text" id="cash_amount_input" name="cash_amount" class="form-control"
                                placeholder="Enter Cash Amount">
                            <span class="text-danger error-cash-amount" id="cash_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="amount_input_container">
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="text" id="amount_input" name="amount" class="form-control"
                                placeholder="Enter Amount">
                            <span class="text-danger error-cash-amount" id="amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="upi_amount_input_container">
                        <div class="form-group">
                            <label>Online Amount</label>
                            <input type="text" id="upi_amount_input" name="upi_amount" class="form-control"
                                placeholder="Enter Online Amount">
                            <span class="text-danger error-upi-amount" id="upi_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="pending_amount_container">
                        <div class="form-group">
                            <label>Pending Amount</label>
                            <input type="text" id="pending_amount" name="pending_amount" class="form-control"
                                readonly>
                            <span id="pending_error" style="color:red; font-size:12px;"></span>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="bank_container">
                        <div class="form-group">
                            <label>Select Bank</label>
                            <select name="bank_id" id="bank_id" class="form-control select2">
                                <option value="">Select Bank</option>

                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                            <div class="text-danger error-bank_id"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 float-md-right">
                        <div class="total-order">
                            <ul>
                                <li>
                                    <h4>{{ $totalItemLabel }}</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-product-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-product-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>


                                <li>
                                    <h4>Discount Amount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-discount-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-discount-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li>
                                    <h4>Price after Discount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="price-after-discount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="price-after-discount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li id="gst-section" style="display:none;">
                                    <h4>Total GST</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-gst-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-gst-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li>
                                    <h4>Shipping</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="shipping-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="shipping-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total">
                                    <h4>Grand Total</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="grand-total">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="grand-total">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <a href="javascript:void(0);" class="btn btn-submit me-2">Submit</a>
                    <a href="{{ route('purchase.lists') }}" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const products = @json($itemOptions);
        const rowMaterials = @json($rowMaterialsArray ?? []);
        const isRowMaterialPurchase = @json($isRowMaterialPurchase);
        const purchaseSubmitUrl = @json($purchaseSubmitUrl);
        const purchaseRedirectUrl = @json($purchaseRedirectUrl);
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            // Initialize Select2
            $(".vendor-select,.product-select,.category-select,#bank_id,.payment-mode-select,.purchase-status-select,.paid-type-container")
                .select2({
                    tags: true,
                });

            // ✅ Common formatting handlers
            $(document).on("focus", ".price-input, .product-discount-amount-input, #shipping, #amount_input, #cash_amount_input, #upi_amount_input", function() {
                let val = $(this).val();
                if (val) {
                    $(this).val(parseIndianNumber(val) || '');
                }
            });

            $(document).on("blur", ".price-input, .product-discount-amount-input, #shipping, #amount_input, #cash_amount_input, #upi_amount_input", function() {
                let val = $(this).val();
                if (val !== "" && !isNaN(parseIndianNumber(val))) {
                    $(this).val(formatIndianNumber(parseIndianNumber(val)));
                }
            });
            //  $(".select2, .category-select").select2({
            //     tags: true,
            // });
            let invalid = false;

            $('#upi_amount_input').on('keyup input', function() {
                let value = parseIndianNumber($(this).val());
                let $errorSpan = $('#upi_amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });
            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('price-input')) {
                    let val = e.target.value;

                    // allow empty while typing
                    if (val === '') return;

                    // prevent negative numbers
                    if (parseIndianNumber(val) < 0) {
                        e.target.value = 0;
                    }
                }
            });
            $('#amount_input').on('keyup input', function() {
                let value = parseIndianNumber($(this).val());
                let $errorSpan = $('#amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });
            $('#cash_amount_input').on('keyup input', function() {
                let value = parseIndianNumber($(this).val());
                let $errorSpan = $('#cash_amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });

            $('#payment_mode').prop('disabled', true);

            // Watch for changes in the payable amount field
            const target = document.getElementById('grand-total');

            const observer = new MutationObserver(() => {
                let amount = parseIndianNumber($('#grand-total').text());
                if (!isNaN(amount) && amount > 0) {
                    $('#payment_mode').prop('disabled', false);
                } else {
                    $('#payment_mode').val('').prop('disabled', true);
                }
            });

            observer.observe(target, {
                childList: true,
                characterData: true,
                subtree: true
            });

            $("#payment_mode").change(function() {
                const selectedMode = $(this).val();

                // Reset values
                $("#paid_type").val("");
                $("#bank_id").val("").trigger('change');
                $("#amount_input, #cash_amount_input, #upi_amount_input, #pending_amount").val("");

                // Hide everything first
                $("#paid_type_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #bank_container")
                    .addClass("d-none");

                // ✅ ONLY show Paid Type for real payment modes
                if (selectedMode === "cash" || selectedMode === "online" || selectedMode === "cashonline") {
                    $("#paid_type_container").removeClass("d-none");
                }

                if (selectedMode === "online" || selectedMode === "cashonline") {
                    $("#bank_container").removeClass("d-none");
                }

                // ✅ Pending = no paid type, no amount, no validation
                if (selectedMode === "pending") {
                    return; // stop here
                }
            });


            $("#paid_type").change(function() {
                const type = $(this).val();
                const selectedMode = $("#payment_mode").val();

                // Clear all fields and errors
                $("#amount_input, #cash_amount_input, #upi_amount_input, #pending_amount").val("");
                $("#cash_amount_error, #upi_amount_error").text("");

                // Hide all input containers first
                $("#amount_input_container, #pending_amount_container, #cash_amount_input_container, #upi_amount_input_container")
                    .addClass("d-none");

                // Remove required attributes initially
                $("#amount_input, #cash_amount_input, #upi_amount_input, #pending_amount").prop("required",
                    false);

                if (type === "full") {
                    invalid1 = false;
                    if (selectedMode === "cash") {
                        // $("#amount_input_container").removeClass("d-none");
                        // $("#amount_input").prop("required", true);

                    } else if (selectedMode === "online") {
                        // $("#amount_input_container").removeClass("d-none");
                        // $("#amount_input").prop("required", true);

                    } else if (selectedMode === "cashonline") {
                        // Show both if cash + online
                        $("#cash_amount_input_container").removeClass("d-none");
                        $("#upi_amount_input_container").removeClass("d-none");

                        $("#cash_amount_input").prop("required", true);
                        $("#upi_amount_input").prop("required", true);
                    }

                } else if (type === "partial") {
                    if (selectedMode === "cash" || selectedMode === "online") {
                        // Normal partial
                        $("#amount_input_container").removeClass("d-none");
                        $("#pending_amount_container").removeClass("d-none");

                        $("#amount_input").prop("required", true);
                        $("#pending_amount").prop("required", true);

                    } else if (selectedMode === "cashonline") {
                        // Partial with cash + online
                        $("#cash_amount_input_container").removeClass("d-none");
                        $("#upi_amount_input_container").removeClass("d-none");
                        $("#pending_amount_container").removeClass("d-none");

                        $("#cash_amount_input").prop("required", true);
                        $("#upi_amount_input").prop("required", true);
                        $("#pending_amount").prop("required", true);
                    }
                }
            });

            $("#amount_input").on("input", function() {
                const partialPaid = parseIndianNumber($(this).val());
                const payable = parseIndianNumber($("#grand-total").text());
                const pending = payable - partialPaid;

                $("#pending_amount").val(pending > 0 ? formatIndianNumber(pending) : "0.00");
            });
            let invalid1 = false;
            $("#cash_amount_input, #upi_amount_input").on("input", function() {
                const cashPaid = parseIndianNumber($("#cash_amount_input").val());
                const onlinePaid = parseIndianNumber($("#upi_amount_input").val());
                const payable = parseIndianNumber($("#grand-total").text());

                const totalPaid = cashPaid + onlinePaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending >= 0 ? formatIndianNumber(pending) : "0.00");


                // Validation
                if (pending < 0 || isNaN(pending)) {
                    invalid1 = true;
                    $("#pending_error").text("Enter correct amount cash + online"); // <-- your error span
                } else {
                    invalid1 = false;
                    $("#pending_error").text("");
                }
            });

            $("#amount_input").on("input", function() {
                const cashPaid = parseIndianNumber($("#amount_input").val());

                const payable = parseIndianNumber($("#grand-total").text());

                const totalPaid = cashPaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending > 0 ? formatIndianNumber(pending) : "0.00");

                // Validation
                if (pending <= 0 || isNaN(pending)) {
                    invalid1 = true;
                    $("#pending_error").text(
                        "Cannot enter more than payable amount"); // <-- your error span
                } else {
                    invalid1 = false;
                    $("#pending_error").text("");
                }
            });

            function parseIndianNumber(value) {
                return parseFloat(String(value).replace(/,/g, '')) || 0;
            }

            function formatIndianNumber(num) {
                num = parseFloat(num) || 0;
                return num.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function validateFullPayment() {
                const payable = parseIndianNumber($("#grand-total").text());
                const cash = parseIndianNumber($("#cash_amount_input").val());
                const upi = parseIndianNumber($("#upi_amount_input").val());
                const total = cash + upi;

                // Clear previous messages
                $("#cash_amount_error").text("");
                $("#upi_amount_error").text("");

                if ($("#paid_type").val() === "full") {
                    if (total !== payable) {
                        const msg = `Cash + UPI must equal payable amount (₹${payable})`;
                        $("#cash_amount_error").text(msg);
                        $("#upi_amount_error").text(msg);
                        return false;
                    }
                }

                return true;
            }


            // Bind validation on input
            $("#cash_amount_input, #upi_amount_input").on("input", function() {
                validateFullPayment();
            });
            // Add new form row dynamically
            $(document).on("click", ".add-row", function() {
                let row = `
                                            <div class="row form-row">

                                                <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Category Name</label>
                                                        <select name="category_name[]" class="form-control category-select">
                                                            <option value="">Category Name</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}" data-price="{{ $category->price }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>{{ $itemLabel }}</label>
                                                        <select name="product_name[]" class="form-control select2 product-select" disabled>
                                                            <option value="">{{ $itemLabel }}</option>
                                                            @foreach ($itemOptions as $product)
                                                                <option value="{{ $product['id'] }}" data-price="{{ $product['price'] }}" data-category="{{ $product['category_id'] }}" data-gst-option="{{ $product['gst_option'] ?? 'without_gst' }}" data-gst='@json($product['product_gst'] ?? null)'>{{ $product['name'] }}</option>
                                                            @endforeach
                                                        </select>
                                                        <div class="product-gst-info mt-1"></div>
                                                        <span class="error text-danger product-error"></span>
                                                        @unless ($isRowMaterialPurchase)
                                                            <div class="row-material-hint"></div>
                                                        @endunless
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>{{ $itemPriceLabel }}</label>
                                                        <input type="text"  inputmode="decimal" name="price[]" class="form-control price-input" placeholder="Enter Price" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Quantity</label>
                                                        <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Qty" value="1" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Disc %</label>
                                                        <input type="number" name="product_discount[]" class="form-control product-discount-input" placeholder="0.00" value="0" min="0" max="100" oninput="this.value = this.value < 0 ? 0 : (this.value > 100 ? 100 : this.value)">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1 col-sm-12 col-6">
                                                    <div class="form-group">
                                                        <label>Disc-Amt</label>
                                                        <input type="text" name="product_discount_amount[]" class="form-control product-discount-amount-input" placeholder="0.00" value="0" min="0">
                                                        <span class="error text-danger"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-2 col-sm-12 col-12">
                                                    <div class="form-group">
                                                        <label>Total Amount</label>
                                                        <input type="text" name="total[]" class="form-control total-input" placeholder="0" readonly>
                                                    </div>
                                                </div>
                                                <div class="col-lg-1 col-sm-12 add-row-btn">
                                                    <button type="button" class="btn btn-danger remove-row">-</button>
                                                </div>
                                            </div>`;

                let newRow = $(row).appendTo("#form-container");

                // Reinitialize Select2 for dynamically added elements
                newRow.find(".select2, .category-select").select2({
                    tags: true
                });

                // Scroll to the new row
                newRow[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            });

            // Remove form row dynamically
            $(document).on("click", ".remove-row", function() {
                $(this).closest(".form-row").remove();
                calculateTotal();
            });

            // Update Vendor Phone when Vendor is selected
            $("#vendor_name").on("change", function() {
                let phone = $(this).find(":selected").data("phone");
                $("#vendor_phone").val(phone ? phone : "");
            });



            // Update Product Price when selecting a Product
            $(document).ready(function() {

                $('.category-select').on('change', function() {
                    var selectedCategory = $(this).val();
                    var $row = $(this).closest('.form-row');
                    var $productDropdown = $(this).closest('.col-lg-3, .col-sm-12').siblings().find(
                        '.product-select');

                    // Clear current options
                    let currentValue = $productDropdown.val();
                    $productDropdown.empty();
                    $row.find(".product-gst-info").empty();

                    if (currentValue && isNaN(currentValue)) {
                        // preserve typed product
                        $productDropdown.append(
                            `<option value="${currentValue}" selected>${currentValue}</option>`
                        );
                    }


                    if (selectedCategory) {
                        // Enable dropdown when category is selected
                        $productDropdown.prop('disabled', false);

                        // Add default placeholder
                        $productDropdown.append('<option value="">{{ $itemLabel }}</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            // Ensure product_gst is a string if it's an object, or use it directly if it's already a string
                            let gstVal = (typeof product.product_gst === 'object') ? JSON
                                .stringify(product.product_gst) : product.product_gst;
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}" data-gst-option="${product.gst_option}" data-gst='${gstVal}'>${product.name}</option>`
                            );
                        });
                    } else {
                        // No category selected, disable product dropdown and add placeholder
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">{{ $itemLabel }}</option>');
                    }

                    // Refresh Select2
                    $productDropdown.trigger('change.select2');
                });
            });



            $(document).ready(function() {
                // Delegated change event for category-select
                // $(document).on('change', '.category-select', function() {
                //     var selectedCategory = $(this).val();

                //     var $row = $(this).closest('.form-row'); // find the row of this select
                //     var $productDropdown = $row.find('.product-select');

                //     // Clear current options
                //     let currentValue = $productDropdown.val();
                //     $productDropdown.empty();
                //     $row.find(".product-gst-info").empty();

                //     if (currentValue && isNaN(currentValue)) {
                //         // preserve typed product
                //         $productDropdown.append(
                //             `<option value="${currentValue}" selected>${currentValue}</option>`
                //         );
                //     }

                //     if (selectedCategory) {
                //         $productDropdown.prop('disabled', false);

                //         $productDropdown.append('<option value="">Product Name</option>');

                //         // Filter products by category
                //         var filteredProducts = products.filter(p => p.category_id ==
                //             selectedCategory);

                //         filteredProducts.forEach(function(product) {
                //             // Ensure product_gst is a string if it's an object, or use it directly if it's already a string
                //             let gstVal = (typeof product.product_gst === 'object') ? JSON.stringify(product.product_gst) : product.product_gst;
                //             $productDropdown.append(
                //                 `<option value="${product.id}" data-price="${product.price}" data-gst-option="${product.gst_option}" data-gst='${gstVal}'>${product.name}</option>`
                //             );
                //         });
                //     } else {
                //         $productDropdown.prop('disabled', true);
                //         $productDropdown.append('<option value="">Product Name</option>');
                //     }

                //     // Refresh Select2
                //     $productDropdown.trigger('change.select2');
                // });
                function renderRowMaterialHint($row, selectedCategory) {
                    if (isRowMaterialPurchase) {
                        return;
                    }

                    const $hint = $row.find('.row-material-hint');
                    $hint.empty();

                    if (!selectedCategory || isNaN(selectedCategory)) {
                        return;
                    }

                    const filteredMaterials = rowMaterials.filter(material => material.category_id == selectedCategory);

                    if (!filteredMaterials.length) {
                        return;
                    }

                    const materialHtml = filteredMaterials.map(material =>
                        `<span class="row-material-chip">${material.name} (${material.quantity ?? 0})</span>`
                    ).join('');

                    $hint.html(`<strong>Row Materials:</strong> ${materialHtml}`);
                }

                $(document).on('change', '.category-select', function() {

                    let selectedCategory = $(this).val();
                    let $row = $(this).closest('.form-row');
                    let $productDropdown = $row.find('.product-select');
                    let $error = $row.find('.product-error');

                    // Reset
                    $productDropdown.empty();
                    $error.text('');
                    renderRowMaterialHint($row, selectedCategory);
                    $row.find(".product-gst-info").empty();

                    // 👉 NEW CATEGORY (typed by user)
                    if (selectedCategory && isNaN(selectedCategory)) {
                        $productDropdown.prop('disabled', false);

                        $productDropdown.append('<option value="">{{ $itemLabel }}</option>');
                        $productDropdown.select2({
                            tags: true
                        });

                        return; // stop here
                    }

                    // 👉 NO CATEGORY
                    if (!selectedCategory) {
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">{{ $itemLabel }}</option>');
                        return;
                    }

                    // 👉 EXISTING CATEGORY
                    let filteredProducts = products.filter(p => p.category_id == selectedCategory);

                    $productDropdown.prop('disabled', false);
                        $productDropdown.append('<option value="">{{ $itemLabel }}</option>');

                    if (filteredProducts.length === 0) {
                        // ❌ Category has NO products
                        $error.text('No {{ strtolower($itemLabel) }} available for this category{{ $isRowMaterialPurchase ? '' : '. Row materials are shown below.' }}');
                        return;
                    }

                    // ✅ Category has products
                    filteredProducts.forEach(product => {
                        let gstVal = typeof product.product_gst === 'object' ?
                            JSON.stringify(product.product_gst) :
                            product.product_gst;

                        $productDropdown.append(
                            `<option value="${product.id}"
                data-price="${product.price}"
                data-gst-option="${product.gst_option}"
                data-gst='${gstVal}'>
                ${product.name}
            </option>`
                        );
                    });

                    $productDropdown.trigger('change.select2');
                });

                $('.form-row').each(function() {
                    renderRowMaterialHint($(this), $(this).find('.category-select').val());
                });




                // Delegated change event for product-select
                $(document).on('change', '.product-select', function() {

                    let selectedOption = $(this).find("option:selected");
                    let price = selectedOption.data("price") || 0;

                    let $row = $(this).closest('.form-row');

                    // set price
                    $row.find(".price-input").val(price);

                    // get quantity
                    let quantity = parseInt($row.find(".quantity-input").val()) || 0;

                    // calculate base total
                    let baseTotal = price * quantity;

                    // set total immediately
                    $row.find(".total-input").val(baseTotal.toFixed(2));

                    // update gst
                    updateProductGstInfo($row);

                    // recalc discount & gst properly
                    $row.find(".product-discount-input").trigger("input");

                    // update grand total
                    calculateTotal();
                });
                $(document).on("input", ".price-input", function() {
                    let val = this.value;

                    // allow only digits + one dot
                    val = val.replace(/[^0-9.]/g, '');
                    if ((val.match(/\./g) || []).length > 1) {
                        val = val.slice(0, -1);
                    }

                    this.value = val;

                    // calculate totals WITHOUT touching price again
                    let row = $(this).closest(".form-row");
                    let price = parseFloat(val) || 0;
                    let quantity = parseInt(row.find(".quantity-input").val()) || 0;

                    row.find(".total-input").val((price * quantity).toFixed(2));
                    updateProductGstInfo(row);
                    calculateTotal();
                });
                $(document).on("blur", ".price-input", function() {
                    let val = parseFloat(this.value);
                    if (!isNaN(val)) {
                        this.value = val.toFixed(2);
                    }
                });

                $(document).on("input", ".price-input, .quantity-input, .product-discount-input",
                    function() {
                        let row = $(this).closest(".form-row");
                        let price = parseFloat(row.find(".price-input").val()) || 0;
                        let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                        let discountPercent = parseFloat(row.find(".product-discount-input").val()) ||
                            0;

                        let baseTotal = price * quantity;

                        // Calculate GST first (to match create order logic)
                        let gstAmount = 0;
                        if ($('#with_gst').is(':checked')) {
                            let selectedOption = row.find(".product-select option:selected");
                            let gstData = selectedOption.data("gst");
                            if (gstData) {
                                try {
                                    let taxes = gstData;
                                    if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                    if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                    if (Array.isArray(taxes)) {
                                        taxes.forEach(tax => {
                                            let taxRate = parseFloat(tax.tax_rate) || 0;
                                            gstAmount += (baseTotal * taxRate) / 100;
                                        });
                                    }
                                } catch (e) {}
                            }
                        }

                        let amountForDiscount = $('#with_gst').is(':checked') ?
                            baseTotal + gstAmount :
                            baseTotal;

                        let discountAmount = (amountForDiscount * discountPercent) / 100;
                        let finalRowTotal = amountForDiscount - discountAmount;

                        row.find(".product-discount-amount-input").val(discountAmount.toFixed(2));
                        row.find(".total-input").val(finalRowTotal.toFixed(2));
                        updateProductGstInfo(row);
                        calculateTotal();
                    });

                $(document).on("input", ".product-discount-amount-input", function() {
                    let row = $(this).closest(".form-row");
                    let price = parseFloat(row.find(".price-input").val()) || 0;
                    let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                    let discountAmount = parseFloat(row.find(".product-discount-amount-input")
                        .val()) || 0;

                    let baseTotal = price * quantity;

                    // Calculate GST first (to match create order logic)
                    let gstAmount = 0;
                    if ($('#with_gst').is(':checked')) {
                        let selectedOption = row.find(".product-select option:selected");
                        let gstData = selectedOption.data("gst");
                        if (gstData) {
                            try {
                                let taxes = gstData;
                                if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                if (Array.isArray(taxes)) {
                                    taxes.forEach(tax => {
                                        let taxRate = parseFloat(tax.tax_rate) || 0;
                                        gstAmount += (baseTotal * taxRate) / 100;
                                    });
                                }
                            } catch (e) {}
                        }
                    }

                    let amountForDiscount = $('#with_gst').is(':checked') ?
                        baseTotal + gstAmount :
                        baseTotal;
                    let discountPercent = 0;
                    if (amountForDiscount > 0) {
                        discountPercent = (discountAmount / amountForDiscount) * 100;
                        // if (discountPercent > 100) discountPercent = 100;
                        // if (discountAmount > totalWithGst) {
                        //     discountAmount = totalWithGst;
                        //     $(this).val(discountAmount.toFixed(2));
                        // }
                    }
                    let finalRowTotal = amountForDiscount - discountAmount;
                    // let finalRowTotal = totalWithGst - discountAmount;

                    row.find(".product-discount-input").val(discountPercent.toFixed(2));
                    row.find(".total-input").val(finalRowTotal.toFixed(2));
                    updateProductGstInfo(row);
                    calculateTotal();
                });
                $('input[name="gst_option"]').on('change', function() {

                    $(".form-row").each(function() {

                        let row = $(this);

                        // GST info update
                        updateProductGstInfo(row);

                        // 🔹 recalc discount percent → amount
                        row.find(".product-discount-input").trigger("input");

                        // 🔹 recalc discount amount → percent
                        row.find(".product-discount-amount-input").trigger("input");

                    });

                    calculateTotal();
                });

                function updateProductGstInfo($row) {
                    let selectedOption = $row.find(".product-select option:selected");
                    let gstOption = selectedOption.data("gst-option");
                    let gstData = selectedOption.data("gst");
                    let price = parseFloat($row.find(".price-input").val()) || 0;
                    let quantity = parseInt($row.find(".quantity-input").val()) || 0;
                    let discountPercent = parseFloat($row.find(".product-discount-input").val()) || 0;

                    let baseTotal = price * quantity;

                    let $gstContainer = $row.find(".product-gst-info");
                    $gstContainer.empty();

                    // Check global toggle first
                    if (!$('#with_gst').is(':checked')) {
                        return;
                    }

                    if (gstOption === 'with_gst' && gstData) {
                        try {
                            let taxes = gstData;
                            if (typeof taxes === "string") taxes = JSON.parse(taxes);
                            if (typeof taxes === "string") taxes = JSON.parse(taxes);

                            if (Array.isArray(taxes)) {
                                let totalTaxAmount = 0;
                                let taxDetails = taxes.map(tax => {
                                    let taxRate = parseFloat(tax.tax_rate) || 0;
                                    let taxAmount = (baseTotal * taxRate) / 100;
                                    totalTaxAmount += taxAmount;
                                    return `${tax.tax_name}: ${tax.tax_rate}%`;
                                }).join(', ');

                                let gstHtml = `<div style="font-size: 11px; color: #666; background: #f8f9fa; padding: 5px; border-radius: 4px; border-left: 3px solid #1b2850; margin-top: 5px;">
                                    <div><strong>Total GST: {{ $currencySymbol }}${totalTaxAmount.toFixed(2)}</strong></div>
                                    <div style="font-size: 10px;">(${taxDetails})</div>
                                </div>`;
                                $gstContainer.html(gstHtml);
                            }
                        } catch (e) {
                            // console.error("Error parsing GST data", e);
                        }
                    } else if (gstOption === 'without_gst') {
                        $gstContainer.html(
                            '<small class="text-muted" style="font-size: 11px;">No GST for this product</small>'
                        );
                    }
                }
            });





            // Calculate Total when Price or Quantity is updated


            function calculateTotal() {
                let totalBaseAmount = 0;
                let totalDiscountAmount = 0;
                let totalTaxAmount = 0;
                let products = [];
                let taxTotals = {};

                $(".form-row").each(function() {
                    let productOption = $(this).find(".product-select option:selected");
                    let productId = productOption.val();
                    let categoryId = $(this).find(".category-select").val();
                    let price = parseIndianNumber($(this).find(".price-input").val());
                    let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
                    let discountPercent = parseIndianNumber($(this).find(".product-discount-input").val());

                    let baseTotal = price * quantity;
                    let itemGstAmount = 0;

                    // Product-wise GST calculation
                    if ($('#with_gst').is(':checked')) {
                        let gstData = productOption.data("gst");
                        if (gstData) {
                            try {
                                let taxes = gstData;
                                if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                if (typeof taxes === "string") taxes = JSON.parse(taxes);

                                if (Array.isArray(taxes)) {
                                    taxes.forEach(tax => {
                                        let taxName = tax.tax_name;
                                        let taxRate = parseFloat(tax.tax_rate) || 0;
                                        let taxAmount = (baseTotal * taxRate) / 100;
                                        itemGstAmount += taxAmount;

                                        if (!taxTotals[taxName]) {
                                            taxTotals[taxName] = {
                                                id: tax.tax_id,
                                                rate: tax.tax_rate,
                                                amount: 0
                                            };
                                        }
                                        taxTotals[taxName].amount += taxAmount;
                                    });
                                }
                            } catch (e) {
                                // console.error("Error parsing GST data in calculation", e);
                            }
                        }
                    }

                    let totalWithGst = baseTotal + itemGstAmount;
                    let itemDiscountAmount = (totalWithGst * discountPercent) / 100;
                    let finalRowTotal = totalWithGst - itemDiscountAmount;

                    if (productId) {
                        products.push({
                            id: productId,
                            category_id: categoryId,
                            price: price,
                            quantity: quantity,
                            discount_percent: discountPercent,
                            discount_amount: itemDiscountAmount,
                            total: finalRowTotal,
                        });
                    }

                    totalBaseAmount += baseTotal;
                    totalTaxAmount += itemGstAmount;
                    totalDiscountAmount += itemDiscountAmount;

                    $(this).find(".total-input").val(formatIndianNumber(finalRowTotal));
                });

                // **Update Summary Display**
                $("#total-product-amount").text(formatIndianNumber(totalBaseAmount));

                // let shipping = parseFloat($("#shipping").val()) || 0;
                let shipping = parseIndianNumber($("#shipping").val());

                if ($('#with_gst').is(':checked')) {
                    $('#gst-section').show();
                    $("#total-gst-amount").text(formatIndianNumber(totalTaxAmount));
                } else {
                    $('#gst-section').hide();
                    $("#total-gst-amount").text('0.00');
                }

                // Grand Total = Base + GST - ItemDiscounts + Shipping
                let grandTotal = (totalBaseAmount + totalTaxAmount - totalDiscountAmount) + shipping;

                $("#grand-total").text(formatIndianNumber(grandTotal));
                $("#shipping-amount").text(formatIndianNumber(shipping));
                $("#total-discount-amount").text(formatIndianNumber(totalDiscountAmount));
                $("#price-after-discount").text(formatIndianNumber(totalBaseAmount - totalDiscountAmount));

                return {
                    products: products,
                    totalProductAmount: totalBaseAmount,
                    totalAmount: totalBaseAmount,
                    discount: 0, // General discount removed/not used here
                    discountAmount: totalDiscountAmount,
                    taxAmount: totalTaxAmount,
                    taxTotals: taxTotals,
                    shipping: shipping,
                    grandTotal: grandTotal,
                    gstOption: $('#with_gst').is(':checked') ? 'with' : 'without'
                };
            }

            // Update calculation when any relevant input changes
            $(document).on("input",
                "#discount, #shipping, .price-input, .quantity-input, .product-discount-input, .product-discount-amount-input",
                function() {
                    calculateTotal();
                });

            function validatePaymentSection() {
                $(".error-payment_mode, .error-paid_type").text("");
                $("#amount_error, #cash_amount_error, #upi_amount_error, #pending_error").text("");

                const paymentMode = $("#payment_mode").val();
                const paidType = $("#paid_type").val();
                const grandTotal = parseIndianNumber($("#grand-total").text());
                const amount = parseIndianNumber($("#amount_input").val());
                // const amount = parseFloat($("#amount_input").val()) || 0;
                const cash = parseIndianNumber($("#cash_amount_input"));
                const online = parseIndianNumber($("#upi_amount_input"));
                const totalPaid = cash + online;

                // ❌ Payment mode missing
                if (!paymentMode) {
                    $(".error-payment_mode").text("Payment mode is required");
                    return false;
                }

                // ✅ Pending → skip everything
                if (paymentMode === "pending") {
                    return true;
                }

                // ❌ Paid type required
                if (!paidType) {
                    $(".error-paid_type").text("Paid type is required");
                    return false;
                }

                // 🔸 PARTIAL
                if (paidType === "partial") {

                    if (paymentMode === "cash" || paymentMode === "online") {
                        if (amount <= 0) {
                            $("#amount_error").text("Amount is required");
                            return false;
                        }
                        if (amount >= grandTotal) {
                            $("#amount_error").text("Amount must be less than total");
                            return false;
                        }
                    }

                    if (paymentMode === "cashonline") {
                        if (cash <= 0) {
                            $("#cash_amount_error").text("Cash amount is required");
                            return false;
                        }
                        if (online <= 0) {
                            $("#upi_amount_error").text("Online amount is required");
                            return false;
                        }
                        if (totalPaid >= grandTotal) {
                            $("#pending_error").text("Cash + Online must be less than total");
                            return false;
                        }
                    }
                }

                // 🔸 FULL
                if (paidType === "full" && paymentMode === "cashonline") {
                    if (totalPaid !== grandTotal) {
                        $("#pending_error").text("Cash + Online must equal total");
                        return false;
                    }
                }

                if ((paymentMode === "online" || paymentMode === "cashonline") && !$("#bank_id").val()) {
                    $(".error-bank_id").text("Please select a bank");
                    return false;
                }

                return true;
            }

            // Form Validation
            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");

                // Validate Vendor
                if ($("#vendor_name").val() === "") {
                    $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                    isValid = false;
                }
                if ($("#bill_no").val().trim() === "") {
                    $("#bill_no").closest(".form-group").find(".error").text("Bill number is required.");
                    isValid = false;
                }

                // Validate Product Fields
                $(".form-row").each(function() {
                    let productSelect = $(this).find(".product-select");
                    let categorySelect = $(this).find(".category-select");
                    let priceInput = $(this).find(".price-input");
                    let quantityInput = $(this).find(".quantity-input");
                    let discountInput = $(this).find(".product-discount-input");

                    if (productSelect.val() === "") {
                        productSelect.closest(".form-group").find(".error").text("Product is required.");
                        isValid = false;
                    }
                    if (categorySelect.val() === "") {
                        categorySelect.closest(".form-group").find(".error").text("Category is required.");
                        isValid = false;
                    }
                    if (priceInput.val().trim() === "" || parseFloat(priceInput.val()) <= 0) {
                        priceInput.closest(".form-group").find(".error").text("Valid price is required.");
                        isValid = false;
                    }
                    if (quantityInput.val().trim() === "" || parseInt(quantityInput.val()) <= 0) {
                        quantityInput.closest(".form-group").find(".error").text(
                            "Valid quantity is required.");
                        isValid = false;
                    }
                    if (discountInput.val().trim() !== "" && (parseFloat(discountInput.val()) < 0 ||
                            parseFloat(discountInput.val()) > 100)) {
                        discountInput.closest(".form-group").find(".error").text(
                            "Discount must be 0-100%.");
                        isValid = false;
                    }
                });

                // Validate Discount & Shipping
                let discount = parseFloat($("#discount").val()) || 0;
                // let shipping = parseFloat($("#shipping").val()) || 0;
                let shipping = parseIndianNumber($("#shipping").val());

                if (discount < 0 || discount > 100) {
                    $("#discount").closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
                if (shipping < 0) {
                    $("#shipping").closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }

                // Validate Status
                if ($("select[name='status']").val() === "") {
                    $("select[name='status']").closest(".form-group").find(".error").text(
                        "Please select a status.");
                    isValid = false;
                }

                return isValid;
            }


            // Submit Form via AJAX
            $(".btn-submit").click(function(e) {
                e.preventDefault();

                let $btn = $(this);
                let originalContent = $btn.html();

                // Show spinner and disable button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).css("pointer-events", "none");

                if (!validateForm()) {
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }

                if (!validatePaymentSection()) {
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }

                let formData = calculateTotal();
                formData.vendor_id = $("#vendor_name").val();
                formData.vendor_phone = $("#vendor_phone").val();
                formData.status = $("select[name='status']").val();

                let taxes = [];
                let gstOption = $("input[name='gst_option']:checked").val();

                if (gstOption === "with") {
                    for (let taxName in formData.taxTotals) {
                        let taxData = formData.taxTotals[taxName];
                        taxes.push({
                            id: taxData.id,
                            name: taxName,
                            rate: taxData.rate,
                            amount: taxData.amount
                        });
                    }
                }
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // Get GST option (radio)
                let gst_option = $("input[name='gst_option']:checked").val();
                // Get payment details
                let bill_no = $("#bill_no").val();
                let payment_mode = $("#payment_mode").val();
                let bank_id = $("#bank_id").val();
                let paid_type = $("#paid_type").val();
                let cash_amount = $("#cash_amount_input").val();
                let upi_amount = $("#upi_amount_input").val();
                let amount = null;

                if (paid_type === 'full') {
                    amount = formData.grandTotal;
                } else {
                    amount = $("#amount_input").val();
                }

                // console.log(amount);
                // console.log(paid_type);
                // console.log(formData.grandTotal);
                let remaining_amount = $("#pending_amount").val();

                $.ajax({
                    url: purchaseSubmitUrl,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        vendor_id: formData.vendor_id,
                        vendor_phone: formData.vendor_phone,
                        status: formData.status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        products: formData.products,
                        taxes: taxes,
                        gst_option: gst_option,
                        payment_mode: payment_mode,
                        bank_id: bank_id,
                        bill_no: bill_no,
                        paid_type: paid_type,
                        cash_amount: cash_amount,
                        upi_amount: upi_amount,
                        amount: amount,
                        remaining_amount: remaining_amount,
                        selectedSubAdminId: selectedSubAdminId
                    }),
                    success: function(response) {
                        $btn.html(originalContent).css("pointer-events", "auto");

                        const purchaseId = response.purchase_id;

                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // Set custom button color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Open the PDF in a new tab
                                    window.open("/purchase/invoice/pdf/" + purchaseId,
                                        "_blank");

                                    if (isRowMaterialPurchase) {
                                        window.location.href = purchaseRedirectUrl;
                                    } else {
                                        window.location.href = purchaseRedirectUrl + purchaseId;
                                    }
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalContent).css("pointer-events", "auto");
                        $(".error").text(""); // Clear previous

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            let message = xhr.responseJSON.message;

                            if (message.includes("phone number")) {
                                $("#vendor_phone").closest(".form-group").find(".error").text(
                                    message);
                            } else {
                                Swal.fire({
                                    title: "Error",
                                    text: message,
                                    icon: "error",
                                    confirmButtonText: "OK"
                                });
                            }
                        } else {
                            alert("Something went wrong!");
                        }

                        // console.error(xhr.responseText); // For debugging
                    }

                });
            });
        });
    </script>
@endpush
