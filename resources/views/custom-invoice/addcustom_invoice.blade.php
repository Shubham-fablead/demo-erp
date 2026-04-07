@extends('layout.app')

@section('title', 'Add Invoice')

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
    </style>
    @php
        $user = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = session('selectedSubAdminId');

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && !empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }

        $taxes = App\Models\TaxRate::where('status', 'active')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchIdToUse)
            ->get();
        $products = App\Models\Product::where('isDeleted', 0)->where('branch_id', $branchIdToUse)->get();

        $settings = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
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
                <h4>Add Invoice</h4>
            </div>
            <div class="gst-header" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex">
                    <label class="custom-radio-label" style="margin-right: 1rem;">
                        <input type="radio" name="gst_option" id="without_gst" value="without_gst" checked />
                        Without GST
                    </label>

                    <label class="custom-radio-label">
                        <input type="radio" name="gst_option" id="with_gst" value="with_gst" />
                        With GST
                    </label>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-12 col-6">
                        <div class="form-group">
                            <label>Choose Customer/Vendor <span class="text-danger">*</span> </label>
                            <select id="choose_people" name="choose_people" class="form-control choose_people-select2">
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

                    <div class="col-lg-6 col-sm-12 col-6 customer-section" style="display: none;">
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
                                <label>Category Name <span class="text-danger">*</span></label>
                                <select id="category_name" name="category_name[]"
                                    class="form-control select2 category-select">
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
                                <label>Product Price <span class="text-danger">*</span></label>
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
                        <div class="col-lg-2 col-sm-12 col-6">
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

                    <!-- Placeholder for additional rows -->
                    <div id="form-container"></div>
                </div>

                <div class="row">

                    <div class="col-lg-3 col-sm-6 col-6" id="shipping-section">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="number" name="shipping" id="shipping" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger"></span>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6" id="discount-section">
                        <div class="form-group">
                            <label>Discount</label>
                            <input type="number" name="discount" id="discount" class="form-control"
                                placeholder="0.00" min="0">
                            <span class="error text-danger" id="discount-error"></span>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control status-select2">
                                <option value="">Choose Status</option>
                                <option value="pending">Pending</option>
                                <option value="partially">Partially</option>
                                <option value="completed">Completed</option>
                            </select>
                            <span class="error text-danger"></span>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-12 col-lg-2 col-6">
                        <div class="form-group">
                            <label>Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_mode" id="payment_mode" class="form-control payment_mode-select2">
                                <option value="">Select Payment Mode</option>
                                <option value="pending">Pending</option>
                                <option value="cash">Cash</option>
                                <option value="online">Online</option>
                                <option value="cashonline">Cash + Online</option>
                            </select>
                            <div class="error text-danger"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="paid_type_container">
                        <div class="form-group">
                            <label>Paid Type <span class="text-danger">*</span></label>
                            <select id="paid_type" name="paid_type" class="form-control paid_type-select2">
                                <option value="">Select Paid Type</option>
                                <option value="full">Fully Paid</option>
                                <option value="partial">Partially Paid</option>
                            </select>
                            <div class="error text-danger"></div>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="cash_amount_input_container">
                        <div class="form-group">
                            <label>Cash Amount</label>
                            <input type="number" id="cash_amount_input" name="cash_amount" class="form-control"
                                placeholder="Enter Cash Amount">
                            <span class="error text-danger" id="cash_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="amount_input_container">
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" id="amount_input" name="amount" class="form-control"
                                placeholder="Enter Amount">
                            <span class="error text-danger" id="amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="upi_amount_input_container">
                        <div class="form-group">
                            <label>Online Amount</label>
                            <input type="number" id="upi_amount_input" name="upi_amount" class="form-control"
                                placeholder="Enter Online Amount">
                            <span class="error text-danger" id="upi_amount_error"></span>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12 col-lg-2 col-6 d-none" id="pending_amount_container">
                        <div class="form-group">
                            <label>Pending Amount <span class="text-danger">*</span></label>
                            <input type="text" id="pending_amount" name="pending_amount" class="form-control"
                                readonly>
                            <span class="text-danger" id="pending_error" ></span>
                        </div>
                    </div>

                    <div class="col-md-2 col-sm-12 col-lg-2 col-6 d-none" id="bank_container">
                        <div class="form-group">
                            <label>Select Bank <span class="text-danger">*</span></label>
                            <select name="bank_id" id="bank_id" class="form-control bank-select2">
                                <option value="">Select Bank</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                @endforeach
                            </select>
                            <div class="error text-danger error-bank_id"></div>
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
                                <li>
                                    <h4>After Discount Amount</h4>
                                    <h5 style="color: green;">
                                        @if ($currencyPosition === 'right')
                                            <span id="discount-after-amount">0.00</span>{{ $currencySymbol }}
                                        @else
                                            {{ $currencySymbol }}<span id="discount-after-amount">0.00</span>
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

            let invalid = false;

            $('#upi_amount_input').on('keyup input', function() {
                let value = parseFloat($(this).val());
                let $errorSpan = $('#upi_amount_error');

                if (!isNaN(value) && value < 0) {
                    invalid = true;
                    $errorSpan.text('Negative amount is not valid');
                } else {
                    invalid = false;
                    $errorSpan.text('');
                }
            });
            $('#amount_input').on('keyup input', function() {
                let value = parseFloat($(this).val());
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
                let value = parseFloat($(this).val());
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
                let amount = parseFloat($('#grand-total').text());
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

                // Reset all
                $("#paid_type").val("");
                $("#amount_input").val("");
                $("#cash_amount_input").val("");
                $("#upi_amount_input").val("");
                $("#pending_amount").val("");
                $("#bank_id").val("").trigger("change");

                if (selectedMode === "pending") {
                    // If pending, hide everything related to payment
                    $("#paid_type_container, #method_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #emi_container, #bank_container")
                        .addClass("d-none");
                } else {

                    if (selectedMode === "cash" || selectedMode === "online" || selectedMode ===
                        "cashonline") {
                        $("#paid_type_container").removeClass("d-none");
                        $("#emi_container").addClass("d-none");

                        if (selectedMode === "online" || selectedMode === "cashonline") {
                            $("#bank_container").removeClass("d-none");
                        } else {
                            $("#bank_container").addClass("d-none");
                        }
                    } else {
                        $("#paid_type_container, #method_container, #amount_input_container, #cash_amount_input_container, #upi_amount_input_container, #pending_amount_container, #emi_container, #bank_container")
                            .addClass("d-none");
                    }

                    if (selectedMode === "emi") {
                        $("#emi_container").removeClass("d-none").show();
                    }
                }
                // Hide all specific inputs initially
                $("#cash_amount_input_container").addClass("d-none");
                $("#upi_amount_input_container").addClass("d-none");
                $("#amount_input_container").addClass("d-none");
                $("#pending_amount_container").addClass("d-none");
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
                const partialPaid = parseFloat($(this).val()) || 0;
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;
                const pending = payable - partialPaid;

                $("#pending_amount").val(pending > 0 ? pending.toFixed(2) : "0.00");
            });
            let invalid1 = false;
            $("#cash_amount_input, #upi_amount_input").on("input", function() {
                const cashPaid = parseFloat($("#cash_amount_input").val()) || 0;
                const onlinePaid = parseFloat($("#upi_amount_input").val()) || 0;
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;

                const totalPaid = cashPaid + onlinePaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending >= 0 ? pending.toFixed(2) : "0.00");


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
                const cashPaid = parseFloat($("#amount_input").val()) || 0;

                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;

                const totalPaid = cashPaid;
                const pending = payable - totalPaid;

                // Update pending field
                $("#pending_amount").val(pending > 0 ? pending.toFixed(2) : "0.00");

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

            function validateFullPayment() {
                const payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, '')) || 0;
                const cash = parseFloat($("#cash_amount_input").val()) || 0;
                const upi = parseFloat($("#upi_amount_input").val()) || 0;
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

            // Initially hide both
            $("#shipping-section").hide();
            $("#discount-section").hide();

            $("#choose_people").change(function() {
                let selected = $(this).val();

                if (selected === "vendor") {
                    // Show shipping, hide discount
                    $("#shipping-section").show();
                    $("#discount-section").hide();
                } else if (selected === "customer") {
                    // Show discount, hide shipping
                    $("#discount-section").show();
                    $("#shipping-section").hide();
                } else {
                    // Hide both if no selection
                    $("#shipping-section").hide();
                    $("#discount-section").hide();
                }
            });

            let discountInput = $("#discount");
            let shippingInput = $("#shipping");

            // Validate Discount if visible
            if ($("#discount-section").is(":visible")) {
                let discount = parseFloat(discountInput.val()) || 0;
                if (discount < 0 || discount > 100) {
                    discountInput.closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
            }

            // Validate Shipping if visible
            if ($("#shipping-section").is(":visible")) {
                let shipping = parseFloat(shippingInput.val()) || 0;
                if (shipping < 0) {
                    shippingInput.closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }
            }
        });
        $(document).on("input", "#discount", function() {
            let value = parseFloat($(this).val());
            let errorDiv = $("#discount-error");

            if (value > 100) {
                $(this).val(100);
                errorDiv.text("Discount cannot be greater than 100%").show();
            } else if (value < 0) {
                $(this).val(0);
                errorDiv.text("Discount cannot be less than 0%").show();
            } else {
                errorDiv.hide(); // hide error when value is valid
            }
        });

        $(document).ready(function() {
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
            // Initialize Select2
            // $(".select2, .category-select").select2({
            //     tags: true,
            // });

            // Add new form row dynamically
            $(document).on("click", ".add-row", function() {
                let row = `
                        <div class="row form-row">
                        <div class="col-lg-3 col-sm-12 col-6">
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
                                    <input type="text" name="price[]" class="form-control price-input" placeholder="Enter Price" oninput="this.value = this.value < 0 ? 0 : this.value" inputmode="decimal" step="0.01">
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
                            <div class="col-lg-2 col-sm-12 col-6">
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

            // Update Vendor Phone when Vendor is selected
            $("#vendor_name").on("change", function() {
                let phone = $(this).find(":selected").data("phone");
                $("#vendor_phone").val(phone ? phone : "");
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

            // $(document).on("input", ".quantity-input", function() {
            //     let value = parseInt($(this).val()) || 0;
            //     if (value < 0) value = 0; // no negatives
            //     $(this).val(value);
            // });
            $(document).on("input", ".quantity-input", function() {

                let row = $(this).closest(".form-row");

                let price = parseFloat(row.find(".price-input").val()) || 0;
                let quantity = parseInt($(this).val()) || 1;

                if (quantity <= 0) quantity = 1;

                $(this).val(quantity);

                row.find(".total-input").val((price * quantity).toFixed(2));

                updateProductGstInfo(row);
                calculateTotal();
            });


            // Update Product Price when selecting a Product
            $(document).ready(function() {

                $('.category-select').on('change', function() {
                    var selectedCategory = $(this).val();
                    var $productDropdown = $(this).closest('.col-lg-3, .col-sm-12').siblings().find(
                        '.product-select');

                    // Clear current options
                    let currentValue = $productDropdown.val();
                    $productDropdown.empty();

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
                        $productDropdown.append('<option value="">Product Name</option>');

                        // Filter products by category
                        var filteredProducts = products.filter(p => p.category_id ==
                            selectedCategory);

                        filteredProducts.forEach(function(product) {
                            $productDropdown.append(
                                `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`
                            );
                        });

                        // Show error if no products available for this category
                        if (filteredProducts.length === 0) {
                            $productDropdown.closest('.form-group').find('.error').text(
                                'No products available for this category');
                        } else {
                            $productDropdown.closest('.form-group').find('.error').text('');
                        }
                    } else {
                        // No category selected, disable product dropdown and add placeholder
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                        $productDropdown.closest('.form-group').find('.error').text('');
                    }

                    // Refresh Select2
                    $productDropdown.trigger('change.select2');
                });
            });

            // $(document).on("change", ".product-select", function() {
            //     let selectedOption = $(this).find("option:selected");
            //     let price = selectedOption.data("price") || 0;
            //     let categoryId = selectedOption.data("category") || ""; // Get category ID

            //     let row = $(this).closest(".form-row");

            //     // Set price in input field
            //     row.find(".price-input").val(parseFloat(price).toFixed(2)).trigger("input");

            // });
            $(document).on("change", ".product-select", function() {

                let selectedOption = $(this).find("option:selected");
                let price = selectedOption.data("price") || 0;

                let $row = $(this).closest(".form-row");

                // ✅ set price
                $row.find(".price-input")
                    .val(parseFloat(price).toFixed(2));

                // ✅ AUTO SET QUANTITY = 1
                let qtyInput = $row.find(".quantity-input");
                let qty = parseInt(qtyInput.val()) || 0;

                if (qty <= 0) {
                    qtyInput.val(1);
                }

                // ✅ calculate total immediately
                let quantity = parseInt(qtyInput.val()) || 1;
                let total = price * quantity;

                $row.find(".total-input").val(total.toFixed(2));

                updateProductGstInfo($row);
                calculateTotal(); // ⭐ VERY IMPORTANT
            });

            $(document).ready(function() {
                // Delegated change event for category-select
                $(document).on('change', '.category-select', function() {
                    var selectedCategory = $(this).val();

                    var $row = $(this).closest('.form-row'); // find the row of this select
                    var $productDropdown = $row.find('.product-select');

                    // Clear current options
                    let currentValue = $productDropdown.val();
                    $productDropdown.empty();

                    if (currentValue && isNaN(currentValue)) {
                        // preserve typed product
                        $productDropdown.append(
                            `<option value="${currentValue}" selected>${currentValue}</option>`
                        );
                    }
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

                        // Show error if no products available for this category
                        if (filteredProducts.length === 0) {
                            $row.find('.product-select').closest('.form-group').find('.error').text(
                                'No products available for this category');
                        } else {
                            $row.find('.product-select').closest('.form-group').find('.error').text(
                                '');
                        }
                    } else {
                        $productDropdown.prop('disabled', true);
                        $productDropdown.append('<option value="">Product Name</option>');
                        $row.find('.product-select').closest('.form-group').find('.error').text('');
                    }

                    // Refresh Select2
                    $productDropdown.trigger('change.select2');
                });

                // Delegated change event for product-select
                $(document).on('change', '.product-select', function() {
                    let selectedOption = $(this).find("option:selected");
                    let price = selectedOption.data("price") || 0;

                    let $row = $(this).closest('.form-row');
                    $row.find(".price-input").val(parseFloat(price).toFixed(2)).trigger("input");

                    updateProductGstInfo($row);
                });

                $('input[name="gst_option"]').on('change', function() {
                    $(".form-row").each(function() {
                        updateProductGstInfo($(this));
                    });
                    calculateTotal();
                });
            });

            function updateProductGstInfo($row) {
                let selectedOption = $row.find(".product-select option:selected");
                let gstOption = selectedOption.data("gst-option");
                let gstData = selectedOption.data("gst");
                let price = parseFloat($row.find(".price-input").val()) || 0;
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
                        if (typeof taxes === "string") taxes = JSON.parse(taxes);
                        if (typeof taxes === "string") taxes = JSON.parse(taxes);

                        if (Array.isArray(taxes)) {
                            let totalTaxAmount = 0;
                            let taxDetails = taxes.map(tax => {
                                let taxRate = parseFloat(tax.tax_rate) || 0;
                                let taxAmount = (total * taxRate) / 100;
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
                        console.error("Error parsing GST data", e);
                    }
                } else if (gstOption === 'without_gst') {
                    $gstContainer.html(
                        '<small class="text-muted" style="font-size: 11px;">No GST for this product</small>'
                    );
                }
            }

            function getRawNumber(value) {
                if (!value) return 0;
                return parseFloat(value.toString().replace(/,/g, '')) || 0;
            }

            function formatIndianCurrency(amount) {
                return parseFloat(amount || 0).toLocaleString('en-IN', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            // Calculate Total when Price or Quantity is updated
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
                    let price = parseFloat($(this).find(".price-input").val()) || 0;
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
                                    let taxes = gstData;
                                    if (typeof taxes === "string") taxes = JSON.parse(taxes);
                                    if (typeof taxes === "string") taxes = JSON.parse(taxes);

                                    if (Array.isArray(taxes)) {
                                        taxes.forEach(tax => {
                                            let taxName = tax.tax_name;
                                            let taxRate = parseFloat(tax.tax_rate) || 0;
                                            let taxAmount = (total * taxRate) / 100;
                                            totalTaxAmount += taxAmount;

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
                                    console.error("Error parsing GST data in calculation", e);
                                }
                            }
                        }
                    }

                    totalAmount += total;
                    // $(this).find(".total-input").val(total.toFixed(2));
                    $(this).find(".total-input")
                        .val(formatIndianCurrency(total));
                });

                // Update total product amount
                $("#total-product-amount").text(formatIndianCurrency(totalAmount));

                // DISCOUNT & SHIPPING LOGIC
                // =============================

                let discountPercent = 0;
                let discountAmount = 0;
                let shipping = 0;

                // 👉 Decide discount base
                // WITH GST → discount on (product + GST)
                // WITHOUT GST → discount on product only

                let discountBaseAmount = totalAmount;

                if ($('#with_gst').is(':checked')) {
                    discountBaseAmount = totalAmount + totalTaxAmount;
                }

                if ($("#discount-section").is(":visible")) {

                    discountPercent = parseFloat($("#discount").val()) || 0;

                    discountAmount = (discountBaseAmount * discountPercent) / 100;

                    $("#discount-amount-li").show();
                    $("#discount-amount").text(formatIndianCurrency(discountAmount));

                } else {
                    discountAmount = 0;
                    $("#discount-amount-li").hide();
                }

                // Shipping
                if ($("#shipping-section").is(":visible")) {
                    shipping = parseFloat($("#shipping").val()) || 0;
                    $("#shipping-amount-li").show();
                    $("#shipping-amount").text(formatIndianCurrency(shipping));
                } else {
                    shipping = 0;
                    $("#shipping-amount-li").hide();
                }


                if ($("#shipping-section").is(":visible")) {
                    shipping = parseFloat($("#shipping").val()) || 0;
                    $("#shipping-amount-li").show();
                } else {
                    shipping = 0;
                    $("#shipping-amount-li").hide();
                }

                if ($('#with_gst').is(':checked')) {
                    $('#gst-section').show();
                    $("#total-gst-amount").text(formatIndianCurrency(totalTaxAmount));
                } else {
                    $('#gst-section').hide();
                    $("#total-gst-amount").text("0.00");
                    totalTaxAmount = 0;
                }

                let discountAfterAmount = totalAmount;

                if ($('#with_gst').is(':checked')) {
                    discountAfterAmount = totalAmount;
                }

                // subtract discount
                discountAfterAmount = discountAfterAmount - discountAmount;

                // safety (never negative)
                if (discountAfterAmount < 0) {
                    discountAfterAmount = 0;
                }

                // ✅ SHOW VALUE
                $("#discount-after-amount").text(
                    formatIndianCurrency(discountAfterAmount)
                );


                // =============================
                // GRAND TOTAL
                // =============================
                let grandTotal = discountAfterAmount + shipping + totalTaxAmount;

                $("#grand-total").text(formatIndianCurrency(grandTotal));
                $("#shipping-amount").text(formatIndianCurrency(shipping));
                // $("#shipping-amount").text(shipping.toFixed(2));

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

            $(document).on("input", "#discount, #shipping", function() {
                calculateTotal();
            });

            //             $(document).on('input', `
        //     #amount_input,
        //     #cash_amount_input,
        //     #upi_amount_input,
        //     #cashOnlineFullAmount,
        //     #upiOnlineFullAmount,
        //     #cashOnlinePartialAmount,
        //     #upiOnlinePartialAmount
        // `, function() {

            //                 let cursorPos = this.selectionStart;

            //                 let raw = getRawNumber($(this).val());

            //                 if (!isNaN(raw)) {
            //                     $(this).val(formatIndianCurrency(raw));
            //                 }

            //             });
            // Form Validation
            function validateForm() {
                let isValid = true;

                // Clear previous errors
                $(".error").text("");

                // === Choose People Validation ===
                let choosePeople = $("#choose_people").val();
                if (choosePeople === "") {
                    $("#choose_people").closest(".form-group").find(".error").text(
                        "Please select Customer/Vendor.");
                    isValid = false;
                } else if (choosePeople === "vendor" && $("#vendor_name").val() === "") {
                    $("#vendor_name").closest(".form-group").find(".error").text("Vendor is required.");
                    isValid = false;
                } else if (choosePeople === "customer" && $("#customer_name").val() === "") {
                    $("#customer_name").closest(".form-group").find(".error").text("Customer is required.");
                    isValid = false;
                }

                // === Product Validation ===
                $(".form-row").each(function() {
                    let productSelect = $(this).find(".product-select");
                    let categorySelect = $(this).find(".category-select");
                    let priceInput = $(this).find(".price-input");
                    let quantityInput = $(this).find(".quantity-input");

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
                });

                // === Discount / Shipping ===
                let discount = parseFloat($("#discount").val()) || 0;
                let shipping = parseFloat($("#shipping").val()) || 0;

                if ($("#discount-section").is(":visible") && (discount < 0 || discount > 100)) {
                    $("#discount").closest(".form-group").find(".error").text("Discount must be between 0-100%.");
                    isValid = false;
                }
                if ($("#shipping-section").is(":visible") && shipping < 0) {
                    $("#shipping").closest(".form-group").find(".error").text("Shipping cost cannot be negative.");
                    isValid = false;
                }

                // === Status Validation ===
                if ($("select[name='status']").val() === "") {
                    $("select[name='status']").closest(".form-group").find(".error").text(
                        "Please select a status.");
                    isValid = false;
                }

                // === Payment Mode Validation ===
                let paymentMode = $("#payment_mode").val();
                let paidType = $("#paid_type").val();
                let bankId = $("#bank_id").val();
                // let payable = parseFloat($("#grand-total").text().replace(/[^\d.-]/g, "")) || 0;
                let payable = getRawNumber($("#grand-total").text());

                if (paymentMode === "") {
                    $("#payment_mode").closest(".form-group").find(".error").text("Please select payment mode.");
                    isValid = false;
                } else {
                    $("#payment_mode").closest(".form-group").find(".error").text("");
                    if (paymentMode != "pending") {
                        if (paidType === "") {
                            $("#paid_type").closest(".form-group").find(".error").text("Please select Paid Type.");
                            isValid = false;
                        }

                        if (paymentMode === "online" || paymentMode === "cashonline") {
                            if (bankId === "") {
                                $("#bank_id").closest(".form-group").find(".error").text("Please select a bank.");
                                isValid = false;
                            }
                        }
                    }

                    if (paidType === "full") {
                        if (paymentMode === "cash" || paymentMode === "online") {
                            // let amt = parseFloat($("#amount_input").val()) || 0;
                            // if (amt !== payable) {
                            //     $("#amount_error").text(`Must be exactly ₹${payable}`);
                            //     isValid = false;
                            // }
                        } else if (paymentMode === "cashonline") {
                            // let cash = parseFloat($("#cash_amount_input").val()) || 0;
                            // let cash = getRawNumber($("#cash_amount_input").val());
                            // let upi = parseFloat($("#upi_amount_input").val()) || 0;
                            let cash = getRawNumber($("#cash_amount_input").val());
                            let upi = getRawNumber($("#upi_amount_input").val());
                            if (cash + upi !== payable) {
                                $("#cash_amount_error, #upi_amount_error").text(
                                    `Cash + Online must equal ₹${payable}`
                                );
                                isValid = false;
                            }
                        }
                    }

                    if (paidType === "partial") {
                        if (paymentMode === "cash" || paymentMode === "online") {
                            // let amt = parseFloat($("#amount_input").val()) || 0;
                            let amt = getRawNumber($("#amount_input").val());
                            if (amt <= 0 || amt >= payable) {
                                $("#amount_error").text("Partial must be less than payable amount.");
                                isValid = false;
                            }
                        } else if (paymentMode === "cashonline") {
                            // let cash = parseFloat($("#cash_amount_input").val()) || 0;
                            // let upi = parseFloat($("#upi_amount_input").val()) || 0;
                            let cash = getRawNumber($("#cash_amount_input").val());
                            let upi = getRawNumber($("#upi_amount_input").val());
                            let total = cash + upi;
                            if (total <= 0 || total >= payable) {
                                $("#pending_error").text("Partial cash + online must be less than payable.");
                                isValid = false;
                            }
                        }
                    }
                }

                return isValid;
            }


            // Submit Form via AJAX
            $(".btn-submit").click(function(e) {
                e.preventDefault();

                let $btn = $(this);
                let originalContent = $btn.html();

                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).css("pointer-events", "none");

                if (!validateForm()) {
                    $btn.html(originalContent).css("pointer-events", "auto");
                    return;
                }

                let formData = calculateTotal();
                formData.status = $("select[name='status']").val();

                // Determine if vendor or customer is selected
                let selectedType = $("#choose_people").val(); // e.g., "vendor" or "customer"
                if (selectedType === "vendor") {
                    formData.vendor_id = $("#vendor_name").val();
                    formData.vendor_phone = $("#vendor_phone").val();
                    formData.customer_id = null; // optional, explicitly set to null
                } else if (selectedType === "customer") {
                    formData.customer_id = $("#customer_name").val();
                    formData.vendor_id = null; // optional, explicitly set to null
                }

                let taxes = [];
                let gstOptionSelection = $("input[name='gst_option']:checked").val();

                if (gstOptionSelection === "with_gst") {
                    for (let taxName in formData.taxTotals) {
                        taxes.push({
                            id: formData.taxTotals[taxName].id,
                            name: taxName,
                            rate: formData.taxTotals[taxName].rate,
                            amount: formData.taxTotals[taxName].amount,
                        });
                    }
                }

                let paid_type = $("#paid_type").val();
                let amount = 0;
                if (paid_type === 'full') {
                    amount = formData.grandTotal;
                } else {
                    amount = parseFloat($("#amount_input").val()) || 0;
                }

                let paymentData = {
                    payment_mode: $("#payment_mode").val(),
                    paid_type: paid_type,
                    bank_id: $("#bank_id").val(),
                    cash_amount: parseFloat($("#cash_amount_input").val()) || 0,
                    amount: amount,
                    upi_amount: parseFloat($("#upi_amount_input").val()) || 0,
                    pending_amount: parseFloat($("#pending_amount").val()) || 0,
                };
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: "/api/customer_invoice/store", // Adjust as needed
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        Authorization: "Bearer " + authToken,
                    },
                    contentType: "application/json",
                    data: JSON.stringify({
                        status: formData.status,
                        discount: formData.discount,
                        shipping: formData.shipping,
                        grand_total: formData.grandTotal,
                        products: formData.products,
                        taxes: taxes,
                        gst_option: gstOptionSelection,
                        vendor_id: formData.vendor_id,
                        vendor_phone: formData.vendor_phone,
                        customer_id: formData.customer_id,
                        selectedSubAdminId: selectedSubAdminId,
                        payment: paymentData,
                    }),
                    success: function(response) {
                        var invoice_id = response.invoice_id;
                        $btn.html(originalContent).css("pointer-events", "auto");
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.open("/custom-invoice/pdf/" + invoice_id,
                                        "_blank");
                                    window.location.href =
                                        "{{ route('custom_invoice.lists') }}";
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

                        console.error(xhr.responseText);
                    }
                });
            });

        });
    </script>
@endpush
