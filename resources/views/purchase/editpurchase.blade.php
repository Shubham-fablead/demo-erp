@extends('layout.app')

@section('title', 'Edit Purchase')

@section('content')
    <style>
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
                margin-bottom: 10px !important
            }
        }

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
        $user = Auth()->user();
        $branchId = $user->id ?? null;
        $userRole = $user->role ?? '';
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            $branchId = $user->id; // sub-admin uses own id
        } elseif ($userRole === 'admin' && !empty($subAdminId)) {
            $branchId = $subAdminId; // admin chooses sub-admin
        } elseif ($userRole === 'staff') {
            $branchId = $user->branch_id; // admin with no sub-admins
        } else {
            $branchId = $user->id; // for other roles, use own id
        }
        $vendors = App\Models\User::where('role', 'vendor')
            ->where('isDeleted', 0) // Only non-deleted vendors
            ->where('branch_id', $branchId) // Filter by branch_id
            ->get();
        $taxes = App\Models\TaxRate::where('status', 'active')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->get();
        $products = App\Models\Product::where('isDeleted', 0)->where('branch_id', $branchId)->get();

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
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
        <div class="page-header purchase-header">
            <div class="page-title">
                <h4>Edit Purchase</h4>
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
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Vendor Name</label>
                            <select id="vendor_name" name="vendor_id" class="form-control select2 vendor-select" disabled>
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
                            <label>Bill No</label>
                            <input type="text" id="bill_no" name="bill_no" class="form-control" placeholder="Bill No"
                            >
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 d-none">
                        <div class="form-group">
                            <label>Vendor Phone</label>
                            <input type="number" id="vendor_phone" name="phone" class="form-control"
                                placeholder="Enter Phone" readonly>
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
                            <label>Product Name</label>
                            <select id="product_name" name="product_name[]" class="form-control select2 product-select"
                                disabled>
                                <option value="">Product Name</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                        data-category="{{ $product->category_id }}"
                                        data-gst-option="{{ $product->gst_option }}" data-gst='{!! $product->product_gst !!}'>
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
                            <input type="text" name="price[]" class="form-control price-input" placeholder="Enter Price">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-1 col-sm-12 col-6">
                        <div class="form-group">
                            <label> Qty</label>
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
                            <input type="number" name="product_discount_amount[]"
                                class="form-control product-discount-amount-input" placeholder="0.00" value="0"
                                min="0">
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
                {{-- </div> --}}

                <div class="row">
                    <!-- <div class="col-lg-3 col-sm-6 col-12">
                                                                                                                                                            <div class="form-group">
                                                                                                                                                                <label>Discount</label>
                                                                                                                                                                <input type="number" name="discount" id="discount" class="form-control" placeholder="0.00%">
                                                                                                                                                                <span class="error text-danger"></span>
                                                                                                                                                            </div>
                                                                                                                                                        </div> -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="text" name="shipping" id="shipping" class="form-control"
                                placeholder="0.00">
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Purchase Status</label>
                            <select name="status" class="form-control" disabled>
                                <option value="">Choose Status</option>
                                <option value="completed">Completed</option>
                                <option value="partially">Partially</option>
                                <option value="pending">Pending</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select name="payment_status" class="form-control payment_status" disabled>
                                <option value="">Choose Status</option>
                                <option value="completed">Completed</option>
                                <option value="partially">Partially</option>
                                <option value="pending">Pending</option>
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
        const products = @json($productsArray);
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
            $(".vendor-select,.product-select,.category-select").select2({});
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
                                                                                            <label>Product Name</label>
                                                                                            <select name="product_name[]" class="form-control select2 product-select" disabled>
                                                                                                <option value="">Product Name</option>
                                                                                                @foreach ($products as $product)
                                                                                                    <option value="{{ $product->id }}"
                                                                                                        data-price="{{ $product->price }}"
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
                                                                                            <input type="text" name="price[]" class="form-control price-input" placeholder="Enter Price">
                                                                                            <span class="error text-danger"></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-1 col-sm-12 col-6">
                                                                                        <div class="form-group">
                                                                                            <label> Qty</label>
                                                                                            <input type="number" name="quantity[]" class="form-control quantity-input" placeholder="Qty" value="1" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                                                            <span class="error text-danger"></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-1 col-sm-12 col-6">
                                                                                        <div class="form-group">
                                                                                            <label>Disc%</label>
                                                                                            <input type="number" name="product_discount[]" class="form-control product-discount-input" placeholder="0.00" value="0" min="0" max="100" oninput="this.value = this.value < 0 ? 0 : (this.value > 100 ? 100 : this.value)">
                                                                                            <span class="error text-danger"></span>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="col-lg-1 col-sm-12 col-6">
                                                                                        <div class="form-group">
                                                                                            <label>Disc Amt</label>
                                                                                            <input type="number" name="product_discount_amount[]" class="form-control product-discount-amount-input" placeholder="0.00" value="0" min="0">
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
                newRow.find(".select2, .category-select").select2({});

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

            // Event Listeners
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
                        $productDropdown.append(
                            `<option value="${product.id}"
                                    data-price="${product.price}"
                                    data-gst-option="${product.gst_option}"
                                    data-gst='${JSON.stringify(product.product_gst)}'>
                                    ${product.name}
                                </option>`
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
                $row.find(".price-input").val(parseFloat(price)).trigger("input");
                updateProductGstInfo($row);
            });

            $(document).on('change', 'input[name="gst_option"]', function() {

                $(".form-row").each(function() {

                    let row = $(this);

                    // update GST display
                    updateProductGstInfo(row);

                    // 🔹 recalc discount percent → amount
                    row.find(".product-discount-input").trigger("input");

                    // 🔹 recalc discount amount → percent
                    row.find(".product-discount-amount-input").trigger("input");

                });

                calculateTotal();
            });

            function formatIndianNumber(num) {
                if (isNaN(num) || num === null) return "0.00";
                return parseFloat(num).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function parseIndianNumber(str) {
                if (typeof str === 'number') return str;
                if (!str) return 0;
                return parseFloat(str.toString().replace(/,/g, '')) || 0;
            }

            function updateProductGstInfo($row) {
                let selectedOption = $row.find(".product-select option:selected");
                let gstOption = selectedOption.data("gst-option");
                let gstData = selectedOption.data("gst");
                let price = parseIndianNumber($row.find(".price-input").val()) || 0;
                let quantity = parseInt($row.find(".quantity-input").val()) || 0;
                let total = price * quantity;

                let $gstContainer = $row.find(".product-gst-info");
                $gstContainer.empty();

                // Check global toggle first
                if (!$('#with_gst').is(':checked')) {
                    return;
                }

                if (gstOption === 'with_gst' && gstData) {
                    try {
                        let taxes = gstData;
                        if (typeof taxes === "string") {
                            if (taxes === "undefined" || taxes === "") taxes = "[]";
                            taxes = JSON.parse(taxes);
                        }
                        if (typeof taxes === "string") taxes = JSON.parse(taxes);

                        if (Array.isArray(taxes)) {
                            let totalTaxAmount = 0;
                            let taxDetails = taxes.map(tax => {
                                let taxName = tax.tax_name || tax.name;
                                let taxRate = parseFloat(tax.tax_rate || tax.rate) || 0;
                                let taxAmount = (total * taxRate) / 100;
                                totalTaxAmount += taxAmount;
                                return `${taxName}: ${taxRate}%`;
                            }).join(', ');

                            let gstHtml = `<div style="font-size: 11px; color: #666; background: #f8f9fa; padding: 5px; border-radius: 4px; border-left: 3px solid #1b2850; margin-top: 5px;">
                                        <div><strong>Total GST: {{ $currencySymbol }}${formatIndianNumber(totalTaxAmount)}</strong></div>
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

            $(document).on("input", ".price-input, .quantity-input, .product-discount-input", function() {
                let row = $(this).closest(".form-row");
                let price = parseIndianNumber(row.find(".price-input").val()) || 0;
                let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                let discountPercent = parseFloat(row.find(".product-discount-input").val()) || 0;

                let baseTotal = price * quantity;

                // Calculate GST first (to match create order logic)
                let gstAmount = 0;
                if ($('#with_gst').is(':checked')) {
                    let selectedOption = row.find(".product-select option:selected");
                    let gstData = selectedOption.data("gst");
                    if (gstData) {
                        try {
                            let taxes = gstData;
                            if (typeof taxes === "string") {
                                if (taxes === "undefined" || taxes === "") taxes = "[]";
                                taxes = JSON.parse(taxes);
                            }
                            if (typeof taxes === "string") taxes = JSON.parse(taxes);

                            if (Array.isArray(taxes)) {
                                taxes.forEach(tax => {
                                    let taxRate = parseFloat(tax.tax_rate || tax.rate) || 0;
                                    gstAmount += (baseTotal * taxRate) / 100;
                                });
                            }
                        } catch (e) {}
                    }
                }

                let totalWithGst = baseTotal + gstAmount;
                let discountAmount = (totalWithGst * discountPercent) / 100;
                let finalRowTotal = totalWithGst - discountAmount;

                row.find(".product-discount-amount-input").val(formatIndianNumber(discountAmount));
                row.find(".total-input").val(formatIndianNumber(finalRowTotal));
                updateProductGstInfo(row);
                calculateTotal();
            });

            $(document).on("input", ".product-discount-amount-input", function() {
                let row = $(this).closest(".form-row");
                let price = parseIndianNumber(row.find(".price-input").val()) || 0;
                let quantity = parseInt(row.find(".quantity-input").val()) || 0;
                let discountAmount = parseIndianNumber(row.find(".product-discount-amount-input").val()) ||
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
                            if (typeof taxes === "string") {
                                if (taxes === "undefined" || taxes === "") taxes = "[]";
                                taxes = JSON.parse(taxes);
                            }
                            if (typeof taxes === "string") taxes = JSON.parse(taxes);

                            if (Array.isArray(taxes)) {
                                taxes.forEach(tax => {
                                    let taxRate = parseFloat(tax.tax_rate || tax.rate) || 0;
                                    gstAmount += (baseTotal * taxRate) / 100;
                                });
                            }
                        } catch (e) {}
                    }
                }

                let totalWithGst = baseTotal + gstAmount;

                // Avoid division by zero
                let discountPercent = 0;
                if (totalWithGst > 0) {
                    discountPercent = (discountAmount / totalWithGst) * 100;
                    if (discountPercent > 100) discountPercent = 100;
                    if (discountAmount > totalWithGst) {
                        discountAmount = totalWithGst;
                        $(this).val(formatIndianNumber(discountAmount));
                    }
                }

                let finalRowTotal = totalWithGst - discountAmount;

                row.find(".product-discount-input").val(discountPercent.toFixed(2));
                row.find(".total-input").val(formatIndianNumber(finalRowTotal));
                updateProductGstInfo(row);
                calculateTotal();
            });

            function calculateTotal() {
                let totalAmount = 0;
                let products = [];
                let taxTotals = {};
                let totalTaxAmount = 0;

                $(".form-row").each(function() {
                    let productOption = $(this).find(".product-select option:selected");
                    let productId = productOption.val();
                    let categoryId = $(this).find(".category-select").val();
                    let price = parseIndianNumber($(this).find(".price-input").val()) || 0;
                    let quantity = parseInt($(this).find(".quantity-input").val()) || 0;
                    let discountPercent = parseFloat($(this).find(".product-discount-input").val()) || 0;

                    let subTotal = price * quantity;
                    let rowGstAmount = 0;

                    // Product-wise GST calculation
                    if ($('#with_gst').is(':checked')) {
                        let gstData = productOption.data("gst");
                        if (gstData) {
                            try {
                                let taxes = gstData;
                                if (typeof taxes === "string") {
                                    if (taxes === "undefined" || taxes === "") taxes = "[]";
                                    taxes = JSON.parse(taxes);
                                }
                                if (typeof taxes === "string") taxes = JSON.parse(taxes);

                                if (Array.isArray(taxes)) {
                                    taxes.forEach(tax => {
                                        let taxName = tax.tax_name || tax.name;
                                        let taxRate = parseFloat(tax.tax_rate || tax.rate) || 0;
                                        let taxAmount = (subTotal * taxRate) / 100;
                                        rowGstAmount += taxAmount;
                                        totalTaxAmount += taxAmount;

                                        if (!taxTotals[taxName]) {
                                            taxTotals[taxName] = {
                                                id: tax.tax_id || tax.id,
                                                rate: taxRate,
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

                    let rowTotalWithGst = subTotal + rowGstAmount;
                    let discountAmount = (rowTotalWithGst * discountPercent) / 100;
                    let finalRowTotal = rowTotalWithGst - discountAmount;

                    if (productId) {
                        products.push({
                            id: productId,
                            category_id: categoryId,
                            price: price,
                            quantity: quantity,
                            discount_percent: discountPercent,
                            discount_amount: discountAmount,
                            total: finalRowTotal,
                        });
                    }

                    totalAmount += subTotal;
                    $(this).find(".total-input").val(formatIndianNumber(finalRowTotal));
                });

                // ✅ Update Total Product Amount
                $("#total-product-amount").text(formatIndianNumber(totalAmount));

                let shipping = parseIndianNumber($("#shipping").val()) || 0;

                if ($('#with_gst').is(':checked')) {
                    $('#gst-section').show();
                    // $("#total-gst-amount").text(totalTaxAmount.toFixed(2));
                    $("#total-gst-amount").text(formatIndianNumber(totalTaxAmount));
                } else {
                    $('#gst-section').hide();
                    $("#total-gst-amount").text('0.00');
                }

                let grandTotal = totalAmount + shipping + totalTaxAmount;

                // Subtract individual item discounts from grand total
                let totalDiscountAmount = 0;
                products.forEach(p => {
                    totalDiscountAmount += p.discount_amount;
                });
                grandTotal -= totalDiscountAmount;

                // $("#grand-total").text(grandTotal.toFixed(2));
                // $("#shipping-amount").text(shipping.toFixed(2));
                // $("#total-discount-amount").text(totalDiscountAmount.toFixed(2));
                // $("#price-after-discount").text((totalAmount - totalDiscountAmount).toFixed(2));

                $("#grand-total").text(formatIndianNumber(grandTotal));
                $("#shipping-amount").text(formatIndianNumber(shipping));
                $("#total-discount-amount").text(formatIndianNumber(totalDiscountAmount));
                $("#price-after-discount").text(formatIndianNumber(totalAmount - totalDiscountAmount));

                return {
                    products: products,
                    totalProductAmount: totalAmount,
                    totalAmount: totalAmount,
                    taxAmount: totalTaxAmount,
                    taxTotals: taxTotals,
                    shipping: shipping,
                    grandTotal: grandTotal,
                    gstOption: $('#with_gst').is(':checked') ? 'with' : 'without'
                };
            }


            // ✅ Format price only when user leaves the field
            $(document).on("focus", ".price-input", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(val > 0 ? val : "");
            });

            $(document).on("blur", ".price-input", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(formatIndianNumber(val));
                calculateTotal();
            });

            $(document).on("focus", ".product-discount-amount-input", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(val > 0 ? val : "");
            });

            $(document).on("blur", ".product-discount-amount-input", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(formatIndianNumber(val));
                calculateTotal();
            });

            $(document).on("focus", "#shipping", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(val > 0 ? val : "");
            });

            $(document).on("blur", "#shipping", function() {
                let val = parseIndianNumber($(this).val());
                $(this).val(formatIndianNumber(val));
                calculateTotal();
            });

            // ✅ Recalculate when inputs change
            $(document).on("input", "#shipping", function() {
                calculateTotal();
            });
            calculateTotal();


            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");

                // Validate Vendor
                if ($("#vendor_name").val() === "") {
                    $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                    isValid = false;
                }
                  if ($("#bill_no").val() === "") {
                    $("#bill_no").closest(".form-group").find(".error").text("Bill Number is required.");
                    isValid = false;
                }

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
                        if (priceInput.val().trim() === "" || parseIndianNumber(priceInput.val()) <= 0) {
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
                let shipping = parseIndianNumber($("#shipping").val()) || 0;

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
            // ✅ Function to load purchase data
            function loadPurchaseData(invoiceId) {
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: "/api/purchase_get/" + invoiceId,
                    type: "GET",
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.success) {
                            let taxes = response.taxes; // ✅ Get taxes from API response
                            let data = response.data;
                            let invoice = response.invoice;

                            // console.log('xfdsf',invoice.bill_no);

                            $("#vendor_phone").val(data.vendor_phone);
                            $("#bill_no").val(invoice.bill_no);
                            // ✅ Set Shipping Cost
                            $("#shipping").val(formatIndianNumber(data.shipping));
                            $("#shipping-amount").text(formatIndianNumber(data.shipping));

                            // ✅ Set Purchase Status
                            $("select[name='status']").val(data.status);

                            // ✅ Set Total Product Amount
                            $("#total-product-amount").text(formatIndianNumber(data.total_amount));

                            // ✅ Populate vendor details
                            $("#vendor_name").val(data.vendor_id).trigger("change");
                            $(".payment_status").val(data.payment_status).trigger("change");
                            $("#vendor_phone").val(data.vendor_phone);

                            if (data.gst_option) {
                                let val = (data.gst_option === 'with_gst' || data.gst_option ===
                                    'with') ? 'with' : 'without';
                                $("input[name='gst_option'][value='" + val + "']")
                                    .prop("checked", true)
                                    .trigger("change"); // 🔥 This triggers GST toggle
                            }

                            $("#total-product-amount").text(parseFloat(data.total_amount).toFixed(2));

                            // ✅ Show Tax Amounts from DB
                            // ✅ Show Tax Amounts from DB
                            if (data.gst_option === "with_gst") {
                                $(".tax-section").show();

                                taxes.forEach(function(tax) {
                                    let $el = $("#tax-" + tax.id);
                                    if ($el.length) {
                                        $el.text(parseFloat(tax.amount).toFixed(2));
                                        $el.attr("data-rate", tax.rate); // keep rate synced
                                    }
                                });
                            } else {
                                $(".tax-section").hide();
                            }


                            // ✅ Show Grand Total from DB (already includes taxes)
                            $("#grand-total").text(parseFloat(data.grand_total).toFixed(2));


                            // ✅ Split product data
                            let productIds = data.product_ids.split(", ");
                            let productNames = data.product_names.split(", ");
                            let productPrices = data.product_prices.split(", ");
                            let productQuantities = data.product_quantities.split(", ");
                            let discountPercents = data.discount_percents ? data.discount_percents
                                .split(", ") : [];
                            let discountAmounts = data.discount_amounts ? data.discount_amounts.split(
                                ", ") : [];
                            let categoryIds = data.category_ids.split(", ");
                            let categoryNames = data.category_names.split(", ");
                            let productGstDetails = data.product_gst_details ? data.product_gst_details
                                .split("|||") : [];

                            // ✅ Clear previous product rows
                            $(".form-row").remove();
                            $("#form-container").empty();

                            // ✅ Loop through products & add rows
                            for (let i = 0; i < productIds.length; i++) {
                                let productObj = products.find(p => p.id == productIds[i]);
                                let gstOption = productObj ? productObj.gst_option : "";
                                let discountPercent = discountPercents[i] || 0;
                                let discountAmount = discountAmounts[i] || 0;

                                // Priority: 1. Saved GST details from purchase, 2. Master product GST
                                let gstData = "[]";
                                let itemGstTotal = 0;
                                if (productGstDetails[i] && productGstDetails[i] !== "null" &&
                                    productGstDetails[i] !== "[]") {
                                    gstData = productGstDetails[i];
                                    try {
                                        let parsedGst = JSON.parse(gstData);
                                        if (Array.isArray(parsedGst)) {
                                            let subTotal = parseFloat(productPrices[i]) * parseFloat(
                                                productQuantities[i]);
                                            parsedGst.forEach(tg => {
                                                itemGstTotal += (subTotal * parseFloat(tg
                                                    .tax_rate || tg.rate || 0)) / 100;
                                            });
                                        }
                                    } catch (e) {}
                                } else if (productObj && productObj.product_gst) {
                                    gstData = typeof productObj.product_gst === 'string' ?
                                        productObj.product_gst :
                                        JSON.stringify(productObj.product_gst);
                                    try {
                                        let parsedGst = JSON.parse(gstData);
                                        if (Array.isArray(parsedGst)) {
                                            let subTotal = parseFloat(productPrices[i]) * parseFloat(
                                                productQuantities[i]);
                                            parsedGst.forEach(tg => {
                                                itemGstTotal += (subTotal * parseFloat(tg
                                                    .tax_rate || tg.rate || 0)) / 100;
                                            });
                                        }
                                    } catch (e) {}
                                }

                                // Get all products for this category to populate dropdown
                                let categoryProducts = products.filter(p => p.category_id ==
                                    categoryIds[i]);
                                let productOptions = `<option value="">Product Name</option>`;

                                // If current product is not in master list (e.g. deleted or custom), add it manually
                                if (!categoryProducts.find(p => p.id == productIds[i])) {
                                    productOptions +=
                                        `<option value="${productIds[i]}" selected data-price="${productPrices[i]}" data-gst-option="${gstOption}" data-gst='${gstData}'>${productNames[i]}</option>`;
                                }

                                categoryProducts.forEach(p => {
                                    let selected = p.id == productIds[i] ? 'selected' : '';
                                    let pGst = typeof p.product_gst === 'string' ? p
                                        .product_gst : JSON.stringify(p.product_gst);
                                    productOptions +=
                                        `<option value="${p.id}" ${selected} data-price="${p.price}" data-gst-option="${p.gst_option}" data-gst='${pGst}'>${p.name}</option>`;
                                });

                                let buttonHtml =
                                    i === 0 ?
                                    `<button type="button" class="btn btn-success add-row">+</button>` :
                                    `<button type="button" class="btn btn-danger remove-row">-</button>`;

                                let subTotal = parseFloat(productPrices[i]) * parseFloat(
                                    productQuantities[i]);
                                let rowTotalWithGst = subTotal + itemGstTotal;
                                let discountAmt = (rowTotalWithGst * parseFloat(discountPercent)) / 100;
                                let finalRowTotal = rowTotalWithGst - discountAmt;

                                let rowHtml = `
                                    <div class="row form-row">
                                        <div class="col-lg-2 col-sm-12 col-6">
                                            <div class="form-group">
                                                <label>Category Name</label>
                                                <select name="category_name[]" class="form-control select2 category-select">
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
                                                <label>Product Name</label>
                                                <select name="product_name[]" class="form-control select2 product-select">
                                                    ${productOptions}
                                                </select>
                                                <div class="product-gst-info mt-1"></div>
                                                <span class="error text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-12 col-6">
                                            <div class="form-group">
                                                <label>Product Price</label>
                                                <input type="text" name="price[]" class="form-control price-input"
                                                    value="${formatIndianNumber(productPrices[i])}">
                                                <span class="error text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-sm-12 col-6">
                                            <div class="form-group">
                                                <label>Quantity</label>
                                                <input type="number" name="quantity[]" class="form-control quantity-input"
                                                    value="${productQuantities[i]}" min="0" oninput="this.value = this.value < 0 ? 0 : this.value">
                                                <span class="error text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-sm-12 col-6">
                                            <div class="form-group">
                                                <label>Disc%</label>
                                                <input type="number" name="product_discount[]" class="form-control product-discount-input"
                                                    value="${discountPercent}" min="0" max="100" oninput="this.value = this.value < 0 ? 0 : (this.value > 100 ? 100 : this.value)">
                                                <span class="error text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-sm-12 col-6">
                                            <div class="form-group">
                                                <label>Disc-Amt</label>
                                                <input type="text" name="product_discount_amount[]" class="form-control product-discount-amount-input"
                                                    value="${formatIndianNumber(discountAmount)}">
                                                <span class="error text-danger"></span>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-sm-12 col-12">
                                            <div class="form-group">
                                                <label>Total Amount</label>
                                                <input type="text" name="total[]" class="form-control total-input"
                                                    value="${formatIndianNumber(finalRowTotal)}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-lg-1 col-sm-12 add-row-btn">
                                            ${buttonHtml}
                                        </div>
                                    </div>`;

                                let $newRow = $(rowHtml).appendTo("#form-container");

                                // ✅ Set the category value
                                $newRow.find(".category-select").val(categoryIds[i]);

                                // ✅ Reinitialize Select2 for the new row
                                $newRow.find(".select2, .category-select").select2({});
                            }

                            $(".form-row").each(function() {
                                updateProductGstInfo($(this));
                            });
                            calculateTotal();

                        } else {
                            Swal.fire("Error!", "Purchase not found!", "error");
                        }
                    },
                    error: function(xhr) {
                        Swal.fire("Error!", "Purchase not found!", "error");
                        // window.location.href = "{{ route('purchase.lists') }}";
                    }
                });
            }

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
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = calculateTotal();
                formData.vendor_id = $("#vendor_name").val();
                formData.vendor_phone = $("#vendor_phone").val();
                formData.status = $("select[name='status']").val();
                formData.payment_status = $("select[name='payment_status']").val();
                formData.bill_no =$("#bill_no").val();

                // ✅ Collect Tax Data
                let taxArray = [];
                for (let taxName in formData.taxTotals) {
                    let taxData = formData.taxTotals[taxName];
                    taxArray.push({
                        name: taxName,
                        id: taxData.id,
                        rate: taxData.rate,
                        amount: taxData.amount,
                    });
                }

                formData.taxes = taxArray;

                $.ajax({
                    url: "/api/purchase_update/" + invoiceId,
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
                        payment_status: formData.payment_status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        bill_no:formData.bill_no,
                        grand_total: formData.grandTotal,
                        taxes: formData.taxes,
                        products: formData.products,
                        gst_option: formData.gstOption,
                        selectedSubAdminId: selectedSubAdminId
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
                                        "{{ route('purchase.lists') }}";
                                }
                            });
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.html(originalText).prop('disabled', false);


                        // console.error(xhr.responseText);
                    }
                });
            });



            $(document).on("input", ".quantity-input", function() {
                let value = parseFloat($(this).val()) || 0;
                if (value < 0) value = 0; // no negatives
                $(this).val(value);
            });




            calculateTotal();
        });
    </script>
@endpush
