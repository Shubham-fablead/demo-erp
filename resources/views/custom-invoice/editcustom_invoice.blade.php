@extends('layout.app')

@section('title', 'Edit Invoice')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
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
                margin-bottom: 10px !important
            }
        }

        .gst-header {
            display: flex;
            align-items: center;
            gap: 25px;
            padding: 12px 20px;
        }

        .gst-header .header-title {
            min-width: 140px;
            font-weight: bold;
            margin: 0;
            font-size: 18px;
            color: #444;
        }

        .custom-radio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            font-weight: 600;
            color: #333;
            user-select: none;
            position: relative;
            padding-left: 28px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: color 0.2s ease;
        }

        .custom-radio-label input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 18px;
            height: 18px;
            border: 2.5px solid #888;
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
            width: 8px;
            height: 8px;
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
    </style>
    @php
        $settings = \DB::table('settings')->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        $productsArray = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'price' => $product->price,
                'gst_option' => $product->gst_option,
                'product_gst' => $product->product_gst,
            ];
        });
        $productsJson = json_encode($productsArray);
    @endphp
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Invoice</h4>
            </div>
            <div class="gst-header">

                <label class="custom-radio-label">
                    <input type="radio" name="gst_option" id="without_gst" value="without_gst" />
                    Without GST
                </label>

                <label class="custom-radio-label">
                    <input type="radio" name="gst_option" id="with_gst" value="with_gst" />
                    With GST
                </label>

            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Choose Customer/Vendor <span class="text-danger">*</span></label>
                            <select id="choose_people" name="choose_people" class="form-control">
                                <option value="">Select Customer/Vendor</option>
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 col-6 vendor-section" style="display: none;">
                        <div class="form-group">
                            <label>Vendor Name <span class="text-danger">*</span></label>
                            <select id="vendor_name" name="vendor_id" class="form-control select2 vendor-select">
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

                    <div class="col-lg-6 col-sm-12 customer-section" style="display: none;">
                        <div class="form-group">
                            <label>Customer Name <span class="text-danger">*</span></label>
                            <select id="customer_name" name="customer_id" class="form-control select2 customer-select">
                                <option value="">Select Customer</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="row form-row">
                        <div class="col-lg-3 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Category Name</label>
                                <select id="category_name" name="category_name[]"
                                    class="form-control select2 category-select">
                                    <option value="">Category Name <span class="text-danger">*</span></option>
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
                                <label>Product Name <span class="text-danger">*</span></label>
                                <select id="product_name" name="product_name[]" class="form-control select2 product-select"
                                    disabled>
                                    <option value="">Product Name</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                            data-category="{{ $product->category_id }}"
                                            data-gst-option="{{ $product->gst_option }}"
                                            data-gst='{!! $product->product_gst !!}'>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="product-gst-info mt-1"></div>

                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Price</label>
                                <input type="text" name="price[]" class="form-control price-input"
                                    placeholder="Enter Price" oninput="this.value = this.value < 0 ? 0 : this.value"
                                    min="0" inputmode="decimal" step="0.01">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-6">
                            <div class="form-group">
                                <label>Product Quantity</label>
                                <input type="number" name="quantity[]" class="form-control quantity-input"
                                    placeholder="Enter Quantity" value="1" min="1"
                                    oninput="this.value = this.value < 0 ? 0 : this.value">
                                <span class="error text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-12 col-12">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <input type="text" name="total[]" class="form-control total-input" placeholder="0"
                                    min="0" readonly>
                            </div>
                        </div>
                        <div class="col-lg-1 col-sm-12 add-row-btn">
                            <button type="button" class="btn btn-success add-row">+</button>
                        </div>
                    </div>

                    <!-- Placeholder for additional rows -->
                    <div id="form-container"></div>
                </div>

                <div class="row">
                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                                            <div class="form-group">
                                                            <label>Discount</label>
                                                            <input type="number" name="discount" id="discount" class="form-control" placeholder="0.00%">
                                                        <span class="error text-danger"></span>
                                                        </div>
                                                        </div> -->
                    <div class="col-lg-3 col-sm-6 col-6" id="shipping-section">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="text" name="shipping" id="shipping" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6" id="discount-section">
                        <div class="form-group">
                            <label>Discount</label>
                            <input type="number" name="discount" id="discount" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Status</label>
                            <select name="status" id="status" class="form-control status-select2" disabled>
                                <option value="">Choose Status</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                                <option value="partially">Partially</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12 float-md-right">
                        <div class="total-order">
                            <ul>
                                <li>
                                    <h4>Total Product Amount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-product-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-product-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li id="discount-amount-li" style="display: none;">
                                    <h4>Discount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            -<span id="discount-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            -{{ $currencySymbol }}<span id="discount-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                                <li id="after-discount-li" style="display: none;">
                                    <h4>After Discount Amount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="after-discount-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="after-discount-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                                <li id="gst-section" style="display: none;">
                                    <h4>Total GST</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="total-gst-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="total-gst-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>


                                <li id="shipping-amount-li" style="display: none;">
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
                    <a href="{{ route('custom_invoice.lists') }}" class="btn btn-cancel">Cancel</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        const products = @json($productsArray);
    </script>
    <script>
        $(document).ready(function() {
            $(".vendor-select,.category-select,.customer-select,.product-select,.payment_mode-select2,.paid_type-select2,.bank-select2,.status-select2,.choose_people-select2")
                .select2({
                    tags: true,
                });
            $("#choose_people").on("change", function() {
                let selected = $(this).val();

                if (selected === "vendor") {
                    $(".vendor-section").show();
                    $(".customer-section").hide();
                } else if (selected === "customer") {
                    $(".vendor-section").hide();
                    $(".customer-section").show();
                } else {
                    $(".vendor-section").hide();
                    $(".customer-section").hide();
                }
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            // ✅ Extract invoice ID from URL
            let urlSegments = window.location.pathname.split("/");
            let invoiceId = urlSegments[urlSegments.length - 1]; // Get last segment

            if (!isNaN(invoiceId) && invoiceId > 0) {
                loadPurchaseData(invoiceId); // Fetch purchase details
            }


            // Initialize Select2
            // $(".select2, .category-select").select2({
            //     tags: true,
            // });
            $(".vendor-select,.customer-select,.product-select,.category-select,#bank_id").select2({
                tags: true,
            });

            // Add new form row dynamically
            $(document).on("click", ".add-row", function() {
                let row = `
                            <div class="row form-row">

                                <div class="col-lg-3 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Category Name <span class="text-danger">*</span></label>
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
                                        <label>Product Name <span class="text-danger">*</span></label>
                                        <select name="product_name[]" class="form-control select2 product-select" disabled>
                                            <option value="">Product Name</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-category="{{ $product->category_id }}" data-gst-option="{{ $product->gst_option }}" data-gst='{!! $product->product_gst !!}'>{{ $product->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="product-gst-info mt-1"></div>
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Product Price</label>
                                        <input type="text" name="price[]" class="form-control price-input" placeholder="Enter Price" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                        <span class="error text-danger"></span>
                                    </div>
                                </div>
                                <div class="col-lg-2 col-sm-12 col-6">
                                    <div class="form-group">
                                        <label>Product Quantity</label>
                                        <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Enter Quantity" value="1" min="1" oninput="this.value = this.value < 0 ? 0 : this.value">
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
            });

            // Remove form row dynamically
            $(document).on("click", ".remove-row", function() {
                $(this).closest(".form-row").remove();
                calculateTotal();
            });

            $(document).ready(function() {

                $('.category-select').on('change', function() {
                    var selectedCategory = $(this).val();
                    var $productDropdown = $(this).closest('.col-lg-3, .col-sm-12').siblings().find(
                        '.product-select');

                    // Clear current options
                    $productDropdown.empty();

                    if (selectedCategory) {
                        // Enable dropdown when category is selected
                        $productDropdown.prop('disabled', false);

                        // Add default placeholder
                        $productDropdown.append('<option value="">Product Name</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            let gstVal = (typeof product.product_gst === 'object') ? JSON
                                .stringify(product.product_gst) : product.product_gst;
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}" data-gst-option="${product.gst_option}" data-gst='${gstVal}'>${product.name}</option>`
                            );
                        });
                    } else {
                        // No category selected, disable product dropdown and add placeholder
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                    }

                    // Refresh Select2
                    $productDropdown.select2('destroy').select2();
                });
            });

            $(document).on("change", ".product-select", function() {
                let selectedOption = $(this).find("option:selected");
                let price = selectedOption.data("price") || 0;
                let categoryId = selectedOption.data("category") || ""; // Get category ID

                let row = $(this).closest(".form-row");

                // Set price in input field
                row.find(".price-input").val(parseInt(price)).trigger("input");

            });

            $(document).ready(function() {
                // Delegated change event for category-select
                $(document).on('change', '.category-select', function() {
                    var selectedCategory = $(this).val();

                    var $row = $(this).closest('.form-row'); // find the row of this select
                    var $productDropdown = $row.find('.product-select');

                    // Clear current options
                    $productDropdown.empty();

                    if (selectedCategory) {
                        $productDropdown.prop('disabled', false);

                        $productDropdown.append('<option value="">Product Name</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            let gstVal = (typeof product.product_gst === 'object') ? JSON
                                .stringify(product.product_gst) : product.product_gst;
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}" data-gst-option="${product.gst_option}" data-gst='${gstVal}'>${product.name}</option>`
                            );
                        });
                    } else {
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                    }

                    // Refresh Select2
                    $productDropdown.select2('destroy').select2();
                });

                // Delegated change event for product-select
                $(document).on('change', '.product-select', function() {
                    let selectedOption = $(this).find("option:selected");
                    let price = selectedOption.data("price") || 0;

                    let $row = $(this).closest('.form-row');
                    $row.find(".price-input").val(parseInt(price)).trigger("input");


                    updateProductGstInfo($row);
                });
            });

            function updateProductGstInfo($row) {

                let selectedOption = $row.find(".product-select option:selected");
                let gstOption = selectedOption.data("gst-option");

                let gstData = selectedOption.attr("data-gst");
                gstData = normalizeTaxes(gstData);

                let price = parseFloat($row.find(".price-input").val()) || 0;
                let quantity = parseInt($row.find(".quantity-input").val()) || 0;
                let total = price * quantity;

                let $gstContainer = $row.find(".product-gst-info");
                $gstContainer.empty();

                // ✅ Global GST toggle
                if (!$('#with_gst').is(':checked')) {
                    return;
                }

                if (gstOption === 'with_gst' && gstData) {
                    try {

                        let taxes = gstData;

                        // ✅ parse safely
                        if (typeof taxes === "string") {
                            taxes = JSON.parse(taxes);
                        }

                        if (!Array.isArray(taxes)) {
                            taxes = [taxes];
                        }

                        let totalTaxAmount = 0;

                        let taxDetails = taxes.map(tax => {

                            // ✅ SUPPORT BOTH STRUCTURES
                            let taxName = tax.name || tax.tax_name || "GST";
                            let taxRate = parseFloat(tax.rate || tax.tax_rate || 0);

                            let taxAmount = (total * taxRate) / 100;
                            totalTaxAmount += taxAmount;

                            return `${taxName}: ${taxRate}%`;

                        }).join(', ');

                        let gstHtml = `
                <div style="font-size:11px;color:#666;background:#f8f9fa;
                    padding:5px;border-radius:4px;border-left:3px solid #1b2850;margin-top:5px;">

                    <div><strong>Total GST: {{ $currencySymbol }}${formatIndianCurrency(totalTaxAmount)}</strong></div>
                    <div style="font-size:10px;">(${taxDetails})</div>

                </div>
            `;

                        $gstContainer.html(gstHtml);

                    } catch (e) {
                        console.error("Error parsing GST data", e);
                    }

                } else if (gstOption === 'without_gst') {

                    $gstContainer.html(
                        '<small class="text-muted" style="font-size:11px;">No GST for this product</small>'
                    );
                }
            }

            // function updateProductGstInfo($row) {
            //     let selectedOption = $row.find(".product-select option:selected");
            //     let gstOption = selectedOption.data("gst-option");
            //     // let gstData = selectedOption.data("gst");
            //     let gstData = selectedOption.attr("data-gst");
            //     gstData = normalizeTaxes(gstData);

            //     let price = parseFloat($row.find(".price-input").val()) || 0;
            //     let quantity = parseInt($row.find(".quantity-input").val()) || 0;
            //     let total = price * quantity;

            //     let $gstContainer = $row.find(".product-gst-info");
            //     $gstContainer.empty();

            //     // Check global toggle first
            //     if (!$('#with_gst').is(':checked')) {
            //         return;
            //     }

            //     if (gstOption === 'with_gst' && gstData) {
            //         try {
            //             let taxes = gstData;
            //             if (typeof taxes === "string") taxes = JSON.parse(taxes);
            //             if (typeof taxes === "string") taxes = JSON.parse(taxes);

            //             if (Array.isArray(taxes)) {
            //                 let totalTaxAmount = 0;
            //                 let taxDetails = taxes.map(tax => {
            //                     let taxRate = parseFloat(tax.rate) || 0;
            //                     let taxAmount = (total * taxRate) / 100;
            //                     totalTaxAmount += taxAmount;
            //                     return `${tax.tax_name}: ${tax.rate}%`;
            //                 }).join(', ');

            //                 let gstHtml = `<div style="font-size: 11px; color: #666; background: #f8f9fa; padding: 5px; border-radius: 4px; border-left: 3px solid #1b2850; margin-top: 5px;">
        //                         <div><strong>Total GST: {{ $currencySymbol }}${formatIndianCurrency(totalTaxAmount)}</strong></div>
        //                         <div style="font-size: 10px;">(${taxDetails})</div>
        //                     </div>`;
            //                 $gstContainer.html(gstHtml);
            //             }
            //         } catch (e) {
            //             console.error("Error parsing GST data", e);
            //         }
            //     } else if (gstOption === 'without_gst') {
            //         $gstContainer.html(
            //             '<small class="text-muted" style="font-size: 11px;">No GST for this product</small>'
            //         );
            //     }
            // }

            $(document).on("input", ".price-input, .quantity-input", function() {
                let row = $(this).closest(".form-row");
                let price = parseIndianNumber(row.find(".price-input").val());
                let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                let total = price * quantity;
                // row.find(".total-input").val(total.toFixed(2));
                row.find(".total-input").val(total.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));
                updateProductGstInfo(row);
                calculateTotal();
            });

            // function calculateTotal() {
            //     let totalAmount = 0;
            //     let products = [];

            //     $(".form-row").each(function () {
            //         let productId = $(this).find(".product-select").val();
            //         let categoryId = $(this).find(".category-select").val();
            //         let price = parseFloat($(this).find(".price-input").val()) || 0;
            //         let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
            //         let total = price * quantity;

            //         if (productId) {
            //             products.push({
            //                 id: productId,
            //                 category_id: categoryId,
            //                 price: price,
            //                 quantity: quantity,
            //                 total: total,
            //             });
            //         }

            //         totalAmount += total;
            //         $(this).find(".total-input").val(total.toFixed(2));
            //     });

            //     // **Update Total Product Amount**
            //     $("#total-product-amount").text(totalAmount.toFixed(2));

            //     // ✅ Only calculate discount if visible
            //     let discountAmount = 0;
            //     let discountPercent = 0;
            //     if ($("#discount-section").is(":visible")) {
            //         discountPercent = parseFloat($("#discount").val()) || 0;
            //         discountAmount = (totalAmount * discountPercent) / 100;
            //         $("#discount-amount").text(discountAmount.toFixed(2)); // update UI
            //     }

            //     // ✅ Only calculate shipping if visible
            //     let shipping = 0;
            //     if ($("#shipping-section").is(":visible")) {
            //         shipping = parseFloat($("#shipping").val()) || 0;
            //         $("#shipping-amount").text(shipping.toFixed(2)); // update UI
            //     }

            //     // **Calculate Multiple Taxes**
            //     let totalTaxAmount = 0;
            //     $(".tax-amount").each(function () {
            //         let taxRate = parseFloat($(this).data("rate")) || 0;
            //         let taxAmount = (totalAmount * taxRate) / 100;
            //         totalTaxAmount += taxAmount;
            //         $(this).text(taxAmount.toFixed(2)); // update UI
            //     });

            //     // ✅ Calculate grand total with dynamic discount & shipping
            //     let grandTotal = totalAmount + shipping - discountAmount + totalTaxAmount;
            //     $("#grand-total").text(grandTotal.toFixed(2));

            //     return {
            //         products: products,
            //         totalProductAmount: totalAmount,
            //         totalAmount: totalAmount,
            //         discount: discountPercent,
            //         discountAmount: discountAmount,
            //         taxAmount: totalTaxAmount,
            //         shipping: shipping,
            //         grandTotal: grandTotal,
            //     };
            // }
            $(document).on("blur", ".price-input", function() {
                let value = parseIndianNumber($(this).val());
                $(this).val(formatIndianCurrency(value));
            });

            function parseIndianNumber(value) {
                if (!value) return 0;
                return parseFloat(value.toString().replace(/,/g, "")) || 0;
            }

            // ✅ Indian Number Format (1,23,456.00)
            function formatIndianCurrency(amount) {
                amount = parseFloat(amount) || 0;

                return amount.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function normalizeTaxes(taxes) {

                if (!taxes) return [];

                // string JSON → object
                if (typeof taxes === "string") {
                    try {
                        taxes = JSON.parse(taxes);
                    } catch (e) {
                        return [];
                    }
                }

                // single object → array
                if (!Array.isArray(taxes)) {
                    taxes = [taxes];
                }

                return taxes;
            }

            function calculateTotal() {
                let totalAmount = 0;
                let products = [];
                let taxTotals = {};
                let totalTaxAmount = 0;

                // Loop through each product row
                $(".form-row").each(function() {
                    let productOption = $(this).find(".product-select option:selected");
                    let productId = productOption.val();
                    let categoryId = $(this).find(".category-select").val();
                    let price = parseIndianNumber($(this).find(".price-input").val());
                    let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
                    let total = price * quantity;

                    if (productId) {
                        products.push({
                            id: productId,
                            category_id: categoryId,
                            price: price,
                            quantity: quantity,
                            total: total,
                        });

                        // Product-wise GST calculation
                        if ($('#with_gst').is(':checked')) {
                            let gstData = productOption.data("gst");
                            if (gstData) {
                                try {
                                    // let taxes = gstData;
                                    let taxes = normalizeTaxes(gstData);
                                    if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                    if (Array.isArray(taxes)) {
                                        // taxes.forEach(tax => {
                                        //     let taxName = tax.name;
                                        //     let taxRate = parseFloat(tax.rate) || 0;
                                        //     let taxAmount = (total * taxRate) / 100;

                                        //     totalTaxAmount += taxAmount;

                                        //     if (!taxTotals[taxName]) {
                                        //         taxTotals[taxName] = {
                                        //             id: tax.tax_id,
                                        //             rate: tax.tax_rate,
                                        //             amount: 0
                                        //         };
                                        //     }

                                        //     taxTotals[taxName].amount += taxAmount;
                                        // });
                                        taxes.forEach(tax => {

                                            let taxName = tax.name || tax.tax_name || "GST";
                                            let taxRate = parseFloat(tax.rate || tax.tax_rate || 0);

                                            let taxAmount = (total * taxRate) / 100;

                                            totalTaxAmount += taxAmount;

                                            if (!taxTotals[taxName]) {
                                                taxTotals[taxName] = {
                                                    id: tax.id || tax.tax_id,
                                                    rate: taxRate,
                                                    amount: 0
                                                };
                                            }

                                            taxTotals[taxName].amount += taxAmount;
                                        });
                                    }
                                } catch (e) {
                                    console.error("Error parsing GST data in calculation", e);
                                }
                            }
                        }
                    }

                    totalAmount += total;
                    // $(this).find(".total-input").val(total.toFixed(2));
                    $(this).find(".total-input").val(formatIndianCurrency(total));
                });

                // Update total product amount
                $("#total-product-amount").text(formatIndianCurrency(totalAmount));

                // Discount & shipping
                let discountPercent = 0;
                let discountAmount = 0;
                let discountBase = totalAmount;


                if ($("#discount-section").is(":visible")) {
                    discountPercent = parseFloat($("#discount").val()) || 0;
                    // discountAmount = (totalAmount * discountPercent) / 100;
                    // Decide discount base depending on GST
                    let discountBase = totalAmount;


                    if ($('#with_gst').is(':checked')) {
                        discountBase = totalAmount + totalTaxAmount;
                    }

                    // discount value
                    discountAmount = (discountBase * discountPercent) / 100;

                    // ✅ After discount shown ONLY on product total
                    afterDiscountAmount = totalAmount - discountAmount;

                    if (afterDiscountAmount < 0) afterDiscountAmount = 0;

                    $("#discount-amount-li").show();
                    $("#discount-amount").text(formatIndianCurrency(discountAmount));

                    $("#after-discount-li").show();
                    $("#after-discount-amount").text(
                        formatIndianCurrency(afterDiscountAmount)
                    );

                } else {
                    $("#discount-amount-li").hide();
                    $("#after-discount-li").hide();
                }
                if ($("#shipping-section").is(":visible")) {
                    shipping = parseIndianNumber($("#shipping").val());
                    $("#shipping-amount-li").show();
                } else {
                    shipping = 0;
                    $("#shipping-amount-li").hide();
                }

                if ($('#with_gst').is(':checked') && totalTaxAmount > 0) {
                    $("#gst-section").show();
                    $("#total-gst-amount").text(formatIndianCurrency(totalTaxAmount));
                } else {
                    $("#gst-section").hide();
                    $("#total-gst-amount").text("0.00");
                    totalTaxAmount = 0;
                }

                // let grandTotal = totalAmount + shipping - discountAmount + totalTaxAmount;
                let grandTotal;

                if ($('#with_gst').is(':checked')) {
                    grandTotal = (totalAmount + totalTaxAmount) - discountAmount + shipping;
                } else {
                    grandTotal = totalAmount - discountAmount + shipping;
                }
                $("#grand-total").text(formatIndianCurrency(grandTotal));
                // $("#shipping-amount").text(shipping.toFixed(2));
                $("#shipping-amount").text(shipping.toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }));

                return {
                    products: products,
                    totalProductAmount: totalAmount,
                    discount: discountPercent,
                    discountAmount: discountAmount,
                    taxAmount: totalTaxAmount,
                    taxTotals: taxTotals,
                    shipping: shipping,
                    grandTotal: grandTotal,
                    gstOption: $('#with_gst').is(':checked') ? 'with_gst' : 'without_gst'
                };
            }


            $(document).on("change", "input[name='gst_option']", function() {
                $(".form-row").each(function() {
                    updateProductGstInfo($(this));
                });
                calculateTotal(); // this will already handle showing/hiding & calculation
            });

            // Update calculation when any relevant input changes
            $(document).on("input", "#discount, #shipping, .price-input, .quantity-input", function() {
                calculateTotal();
            });
            calculateTotal();



            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");



                // Validate Product Fields only if category is selected
                $(".form-row").each(function() {
                    let productSelect = $(this).find(".product-select");
                    let categorySelect = $(this).find(".category-select");
                    let priceInput = $(this).find(".price-input");
                    let quantityInput = $(this).find(".quantity-input");

                    let categoryVal = categorySelect.val();

                    // 🌟 If category is selected, validate product, price, and quantity
                    if (categoryVal !== "") {
                        if (productSelect.val() === "") {
                            productSelect.closest(".form-group").find(".error").text(
                                "Product is required.");
                            isValid = false;
                        }
                        if (priceInput.val().trim() === "" || parseFloat(priceInput.val()) <= 0) {
                            priceInput.closest(".form-group").find(".error").text(
                                "Valid price is required.");
                            isValid = false;
                        }
                        if (quantityInput.val().trim() === "" || parseInt(quantityInput.val()) <= 0) {
                            quantityInput.closest(".form-group").find(".error").text(
                                "Valid quantity is required.");
                            isValid = false;
                        }
                    }
                });

                // Validate Discount & Shipping
                let discount = parseFloat($("#discount").val()) || 0;
                shipping = parseIndianNumber($("#shipping").val());

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
            $(document).on("blur", "#shipping", function() {
                let value = parseIndianNumber($(this).val());
                $(this).val(formatIndianCurrency(value));
            });

            function createProductRow(isFirstRow = false) {

                let actionBtn = isFirstRow ?
                    `<button type="button" class="btn btn-success add-row">+</button>` :
                    `<button type="button" class="btn btn-danger remove-row">−</button>`;

                let row = `
    <div class="row form-row">

        <div class="col-lg-3 col-sm-12 col-6">
            <div class="form-group">
                <label>Category Name <span class="text-danger">*</span></label>
                <select name="category_name[]" class="form-control category-select">
                    <option value="">Category Name</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-2 col-sm-12 col-6">
            <div class="form-group">
                <label>Product Name <span class="text-danger">*</span></label>
                <select name="product_name[]" class="form-control product-select" disabled>
                    <option value="">Product Name</option>
                </select>
                <div class="product-gst-info mt-1"></div>
            </div>
        </div>

        <div class="col-lg-2 col-sm-12 col-6">
            <div class="form-group">
                <label>Product Price</label>
                <input type="text" class="form-control price-input">
            </div>
        </div>

        <div class="col-lg-2 col-sm-12 col-6">
            <div class="form-group">
                <label>Product Qty</label>
                <input type="number" class="form-control quantity-input" value="1">
            </div>
        </div>

        <div class="col-lg-2 col-sm-12">
            <div class="form-group">
                <label>Total Amount</label>
                <input type="text" class="form-control total-input" readonly>
            </div>
        </div>

        <div class="col-lg-1 col-sm-12 add-row-btn">
            ${actionBtn}
        </div>

    </div>`;

                let newRow = $(row).appendTo("#form-container");

                newRow.find(".category-select,.product-select").select2({
                    tags: true
                });

                return newRow;
            }

            function refreshRowButtons() {

                $(".form-row").each(function(index) {

                    let btnArea = $(this).find(".add-row-btn");

                    if (index === 0) {
                        btnArea.html(
                            '<button type="button" class="btn btn-success add-row">+</button>'
                        );
                    } else {
                        btnArea.html(
                            '<button type="button" class="btn btn-danger remove-row">−</button>'
                        );
                    }
                });
            }
            // ✅ Function to load purchase data
            function loadPurchaseData(invoiceId) {
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: "/api/custom_invoice_get/" + invoiceId,
                    type: "GET",
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        // console.log(response);
                        let vendorId = response.data.vendor_id ?? '';
                        let customerId = response.data.customer_id ?? '';
                        // let taxes = response.taxes;
                        // let taxes = response.taxes || [];
                        let taxes = normalizeTaxes(response.taxes);

                        // convert string JSON → array
                        if (typeof taxes === "string") {
                            try {
                                taxes = JSON.parse(taxes);
                            } catch (e) {
                                taxes = [];
                            }
                        }

                        // if single object → make array
                        if (!Array.isArray(taxes)) {
                            taxes = [taxes];
                        }
                        // ✅ If vendor
                        if (vendorId) {
                            $('#choose_people').val('vendor').trigger('change');
                            $('.vendor-section').show();
                            $('.customer-section').hide();

                            $('#vendor_name').val(vendorId).trigger('change').prop('disabled', true);
                            $('#choose_people').prop('disabled', true);

                            // Show shipping section, hide discount section
                            $('#shipping-section').show();
                            $('#discount-section').hide();

                            // Show shipping in summary, hide discount in summary
                            $('#shipping-amount-li').show();
                            let shippingcost = parseFloat(response.data.shipping) || 0;
                            $('#shipping').val(shippingcost.toFixed(2));
                            $('#discount-amount-li').hide();

                            if (response.data.gst_option) {
                                $("input[name='gst_option'][value='" + response.data.gst_option + "']")
                                    .prop(
                                        "checked",
                                        true).trigger('change');
                            }
                            taxes.forEach(function(tax) {
                                $("#tax-" + tax.tax_id).text(parseFloat(tax.amount).toFixed(2));
                            });
                        } else if (customerId) {
                            $('#choose_people').val('customer').trigger('change');
                            $('.customer-section').show();
                            $('.vendor-section').hide();

                            $('#customer_name').val(customerId).trigger('change').prop('disabled',
                                true);
                            $('#choose_people').prop('disabled', true);

                            // Show discount section, hide shipping section
                            $('#discount-section').show();
                            $('#shipping-section').hide();

                            // Show discount in summary, hide shipping in summary
                            $('#discount-amount-li').show();
                            $('#shipping-amount-li').hide();

                            // ✅ Calculate and show discount amount as number
                            let discountPercent = parseFloat(response.data.discount) || 0;

                            // ✅ Show discount amount in the discount input field
                            $('#discount').val(discountPercent.toFixed(2));

                            if (response.data.gst_option) {
                                $("input[name='gst_option'][value='" + response.data.gst_option + "']")
                                    .prop(
                                        "checked",
                                        true).trigger('change');
                            }
                            taxes.forEach(function(tax) {
                                $("#tax-" + tax.tax_id).text(parseFloat(tax.amount).toFixed(2));
                            });
                        } else {
                            $('.vendor-section').hide();
                            $('.customer-section').hide();
                            $('#shipping-section').hide();
                            $('#discount-section').hide();
                            $('#discount-amount-li').hide();
                            $('#shipping-amount-li').hide();
                        }

                        // User-initiated changes (optional)
                        $('#choose_people').on('change', function() {
                            let selected = $(this).val();
                            if (selected === 'vendor') {
                                $('.vendor-section').show();
                                $('.customer-section').hide();
                            } else if (selected === 'customer') {
                                $('.customer-section').show();
                                $('.vendor-section').hide();
                            } else {
                                $('.vendor-section').hide();
                                $('.customer-section').hide();
                            }
                        });

                        if (response.success) {
                            // let taxes = response.taxes;
                            let taxes = normalizeTaxes(response.taxes);
                            let data = response.data;

                            // Set phone, status, etc.
                            $("#vendor_phone").val(data.vendor_phone);
                            $("#status").val(data.status).trigger("change");
                            if (data.bank_id) {
                                $("#bank_id").val(data.bank_id).trigger("change");
                            }
                            // console.log("Invoice Status:", data.status);
                            $("#total-product-amount").text(data.total_amount);

                            // Convert to numbers
                            let totalAmount = parseFloat(data.total_amount);
                            let discountPercentage = parseFloat(data.discount) || 0;
                            let shipping = parseFloat(data.shipping) || 0;

                            // ✅ Calculate discount amount (percentage-based)
                            let discountAmount = (totalAmount * discountPercentage) / 100;

                            // ✅ Subtotal after discount
                            let subtotalAfterDiscount = totalAmount - discountAmount;

                            // ✅ Show calculated discount & shipping
                            $("#discount-amount").text(formatIndianCurrency(discountAmount));
                            $("#shipping-amount").text(formatIndianCurrency(shipping));

                            // ✅ Calculate taxes on discounted subtotal
                            let totalTaxAmount = 0;
                            taxes.forEach(function(tax) {
                                let taxRate = parseFloat(tax.rate);
                                let taxAmount = (totalAmount * taxRate) / 100;
                                totalTaxAmount += taxAmount;
                                $("#tax-" + tax.id).text(taxAmount.toFixed(2));
                            });

                            // ✅ Calculate final grand total
                            let grandTotalfinal = subtotalAfterDiscount + shipping + totalTaxAmount;
                            $("#grand-total").text(formatIndianCurrency(grandTotalfinal));

                            // ✅ Split product data
                            let productIds = data.product_ids.split(", ");
                            let productNames = data.product_names.split(", ");
                            let productPrices = data.product_prices.split(", ");
                            let productQuantities = data.product_quantities.split(", ");
                            let productTotals = data.product_totals.split(", ");
                            let categoryIds = data.category_ids.split(", ");
                            let categoryNames = data.category_names.split(", ");

                            // ✅ Clear previous product rows
                            // $("#form-container").empty();

                            // ✅ Loop through products & add rows
                            // for (let i = 0; i < productIds.length; i++) {
                            //     let product = products.find(p => p.id == productIds[i]);
                            //     let gstVal = "";
                            //     let gstOption = "";
                            //     if (product) {
                            //         gstVal = (typeof product.product_gst === 'object') ? JSON.stringify(
                            //             product.product_gst) : product.product_gst;
                            //         gstOption = product.gst_option;
                            //     }
                            //     let rowHtml = `
                        //                                                                     <div class="row form-row">

                        //                                                                         <div class="col-lg-3 col-sm-12 col-6">
                        //                                                                             <div class="form-group">
                        //                                                                                 <label>Category Name</label>
                        //                                                                                 <select name="category_name[]" class="form-control select2 category-select" disabled>
                        //                                                                                     <option value="${categoryIds[i]}" selected>${categoryNames[i]}</option>
                        //                                                                                 </select>
                        //                                                                             </div>
                        //                                                                         </div>
                        //                                                                         <div class="col-lg-2 col-sm-12 col-6">
                        //                                                                             <div class="form-group">
                        //                                                                                 <label>Product Name</label>
                        //                                                                                 <select name="product_name[]" class="form-control select2 product-select" disabled>
                        //                                                                                     <option value="${productIds[i]}" data-gst-option="${gstOption}" data-gst='${gstVal}' selected>${productNames[i]}</option>
                        //                                                                                 </select>
                        //                                                                                 <div class="product-gst-info mt-1"></div>
                        //                                                                             </div>
                        //                                                                         </div>
                        //                                                                         <div class="col-lg-2 col-sm-12 col-6">
                        //                                                                             <div class="form-group">
                        //                                                                                 <label>Product Price</label>
                        //                                                                                 <input type="number" name="price[]" class="form-control price-input"
                        //                                                                                     value="${parseInt(productPrices[i])}" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                        //                                                                             </div>
                        //                                                                         </div>
                        //                                                                         <div class="col-lg-2 col-sm-12 col-6">
                        //                                                                             <div class="form-group">
                        //                                                                                 <label>Product Quantity</label>
                        //                                                                                 <input type="number" name="quantity[]" class="form-control quantity-input"
                        //                                                                                     value="${productQuantities[i]}" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                        //                                                                             </div>
                        //                                                                         </div>
                        //                                                                         <div class="col-lg-2 col-sm-12 col-12">
                        //                                                                             <div class="form-group">
                        //                                                                             <label>Total Amount</label>
                        //                                                                             <input type="text" name="total[]" class="form-control total-input"
                        //                                                                                 value="${parseFloat(productTotals[i]).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}" readonly>
                        //                                                                         </div>
                        //                                                                         </div><div class="col-lg-1 col-sm-12 add-row-btn">
                        //                                                                             <button type="button" class="btn btn-danger remove-row">−</button>
                        //                                                                         </div>
                        //                                                                     </div>`;

                            //     $("#form-container").append(rowHtml);
                            // }

                            // remove extra rows but KEEP first row
                            $(".form-row").remove();
                            $("#form-container").empty();

                            // get first existing row
                            let firstRow = $(".form-row").first();

                            for (let i = 0; i < productIds.length; i++) {

                                let row = createProductRow();

                                // set category
                                row.find(".category-select")
                                    .val(categoryIds[i])
                                    .trigger("change");

                                // find product
                                let product = products.find(p => p.id == productIds[i]);

                                let gstVal = "";
                                let gstOption = "";

                                if (product) {
                                    gstVal = typeof product.product_gst === 'object' ?
                                        JSON.stringify(product.product_gst) :
                                        product.product_gst;

                                    gstOption = product.gst_option;
                                }

                                // set product option
                                row.find(".product-select")
                                    .prop("disabled", false)
                                    .html(`
            <option value="${productIds[i]}"
                data-gst-option="${gstOption}"
                data-gst='${gstVal}'
                selected>
                ${productNames[i]}
            </option>
        `)
                                    .trigger("change");

                                row.find(".price-input").val(productPrices[i]);

                                let qty = parseInt(productQuantities[i]) || 1;
                                row.find(".quantity-input").val(qty);

                                row.find(".total-input").val(
                                    formatIndianCurrency(productTotals[i])
                                );
                            }

                            setTimeout(function() {
                                refreshRowButtons();
                                $(".form-row").each(function() {
                                    updateProductGstInfo($(this));
                                });

                                calculateTotal();

                            }, 100);
                        } else {
                            Swal.fire("Error!", "Invoice not found!", "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Invoice not found!", "error");

                    }
                });
            }
            $(document)
                .off("click", ".add-row")
                .on("click", ".add-row", function() {

                    createProductRow(false);
                    refreshRowButtons();
                });
            $(document)
                .off("click", ".remove-row")
                .on("click", ".remove-row", function() {

                    $(this).closest(".form-row").remove();
                    refreshRowButtons();
                    calculateTotal();
                });

            $(document).on("click", ".btn-submit", function(e) {
                e.preventDefault();

                const $btn = $(this);
                const originalText = $btn.html();


                // Show loader
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop('disabled', true);

                if (!validateForm()) {
                    $btn.html(originalText).prop('disabled', false);

                    return;
                }

                let formData = calculateTotal();
                let selectedType = $("#choose_people").val(); // e.g., "vendor" or "customer"
                if (selectedType === "vendor") {
                    formData.vendor_id = $("#vendor_name").val();
                    formData.vendor_phone = $("#vendor_phone").val();
                    formData.customer_id = null; // optional, explicitly set to null
                } else if (selectedType === "customer") {
                    formData.customer_id = $("#customer_name").val();
                    formData.vendor_id = null; // optional, explicitly set to null
                }
                formData.status = $("#status").val();
                formData.bank_id = $("#bank_id").val();
                formData.payment_mode = $("#payment_mode").val();
                formData.paid_type = $("#paid_type").val();
                formData.amount = $("#amount_input").val();
                formData.cash_amount = $("#cash_amount_input").val();
                formData.upi_amount = $("#upi_amount_input").val();

                // ✅ Collect Tax Data
                let taxArray = [];
                if (formData.gstOption === "with_gst") {
                    for (let taxName in formData.taxTotals) {
                        taxArray.push({
                            id: formData.taxTotals[taxName].id,
                            name: taxName,
                            rate: formData.taxTotals[taxName].rate,
                            amount: formData.taxTotals[taxName].amount,
                        });
                    }
                }

                formData.taxes = taxArray;

                $.ajax({
                    url: "/api/custom_invoice_update/" + invoiceId,
                    type: "POST",
                    headers: {
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        "Authorization": "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        vendor_id: formData.vendor_id,
                        customer_id: formData.customer_id,
                        vendor_phone: formData.vendor_phone,
                        status: formData.status,
                        bank_id: formData.bank_id,
                        payment_mode: formData.payment_mode,
                        paid_type: formData.paid_type,
                        amount: formData.amount,
                        cash_amount: formData.cash_amount,
                        upi_amount: formData.upi_amount,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        taxes: formData.taxes,
                        products: formData.products,
                        gst_option: formData.gstOption
                    }),
                    success: function(response) {
                        // Reset button
                        $btn.html(originalText).prop('disabled', false);

                        if (response.success) {
                            Swal.fire({
                                title: "Success!",
                                text: "Purchase updated successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43" // your custom color
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('custom_invoice.lists') }}";
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);

                        alert("Something went wrong!");
                        console.error(xhr.responseText);
                    }
                });
            });
            $(document).on("input", ".price-input, .quantity-input", function() {
                let value = parseInt($(this).val()) || 0;
                if (value < 0) value = 0; // no negatives
                $(this).val(value);
            });
        });
    </script>
@endpush
