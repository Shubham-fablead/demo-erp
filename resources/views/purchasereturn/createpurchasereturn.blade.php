@extends('layout.app')

@section('title', 'Purchase Return')

@section('content')
    <style>
        .d-none {
            display: none !important;
        }

        @media screen and (max-width: 768px) {
            span.pro_purhcaes {
                white-space: normal;
            }

            .form-group {
                margin-bottom: 15px !important
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Create Purchase Return</h4>
            </div>
            <div class="page-btn">
                <a href="{{ route('purchasereturn.list') }}" class="btn btn-added">
                    Back
                </a>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Invoice Number Select -->
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Invoice Number</label>
                            <select class="form-control select2 invoice_id" name="invoice_id" id="invoice_id" required>
                                <option value="">Select Invoice Number</option>
                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->id }}">{{ $invoice->invoice_number }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Vendor Name Input -->
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Vendor Name</label>
                            <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                                placeholder="Vendor Name" readonly>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="table-responsive mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Purchased Qty</th>
                                    <th>Already Returned</th>
                                    <th>Return Qty</th>
                                    <th>Price</th>
                                    <th>Discount Amt</th>
                                    <th>GST</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    {{-- <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Additional Discount (%)</label>
                            <input type="number" class="form-control" id="discount-input" value="0" min="0"
                                max="100" step="0.01">
                            <div id="discount-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                    </div> --}}

                    <div class="row justify-content-end">
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="order-subtotal">
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }} <span id="subtotal">0.00</span>
                                            @else
                                                <span id="subtotal">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="discount-row">
                                        <h4>Discount</h4>
                                        <h5 id="discount-display">0.00</h5>
                                    </li>

                                    <li class="after-discount">
                                        <h4>After Discount</h4>
                                        <h5>
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }} <span id="after-discount">0.00</span>
                                            @else
                                                <span id="after-discount">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="shipping-row">
                                        <h4>Shipping</h4>
                                        <h5>
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }} <span id="shipping-amount">0.00</span>
                                            @else
                                                <span id="shipping-amount">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total">
                                        <h4>Total Return</h4>
                                        <h5>
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }} <span id="grand-total">0.00</span>
                                            @else
                                                <span id="grand-total">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li style="border-top:1px dashed #ddd; padding-top:10px;">
                                        <h4 style="color:#2E7D32;">Paid Amount</h4>
                                        <h5 id="paidAmountText" style="color:#2E7D32;font-weight:600;">
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }}0.00
                                            @else
                                                0.00{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li>
                                        <h4 style="color:#C62828;">Pending Amount</h4>
                                        <h5 id="pendingAmountText" style="color:#C62828;font-weight:600;">
                                            @if ($currencyPosition === 'left')
                                                {{ $currencySymbol }}0.00
                                            @else
                                                0.00{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <button type="button" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('purchase.lists') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('.invoice_id').select2({
                placeholder: "Select Invoice Number",
                allowClear: true,
                width: '100%'
            });
        });

        let currencySymbol = @json($currencySymbol);
        let currencyPosition = @json($currencyPosition);
        let authToken = localStorage.getItem("authToken");
        let originalPaidAmount = 0;
        let originalTotalAmount = 0;
        let shippingCharge = 0;
        const $submitBtn = $('.btn-submit');
        const submitBtnDefaultHtml = $submitBtn.html();

        function toggleSubmitLoading(isLoading) {
            if (isLoading) {
                $submitBtn
                    .prop('disabled', true)
                    .html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                    );
            } else {
                $submitBtn
                    .prop('disabled', false)
                    .html(submitBtnDefaultHtml);
            }
        }

        function formatCurrency(amount) {
            let num = parseFloat(amount || 0);
            let formatted = num.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return currencyPosition === 'left' ?
                `${currencySymbol}${formatted}` :
                `${formatted}${currencySymbol}`;
        }

        function calculateTotals() {
            let subtotal = 0;
            let totalProductDiscount = 0;
            let totalGst = 0;

            $('#product-table-body tr').each(function() {
                let $row = $(this);
                let quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                let availableQty = parseFloat($row.data('available-qty')) || 0;
                let purchaseQty = parseFloat($row.data('purchase-qty')) || 0;
                let alreadyReturned = purchaseQty - availableQty;

                let price = parseFloat($row.data('price')) || 0;
                let originalPurchaseQty = purchaseQty || 1;

                // Get original discount amount from the purchase
                let originalDiscountAmount = parseFloat($row.data('original-discount-amount')) || 0;

                // Calculate pro-rated discount amount based on return quantity
                let proRatedDiscountAmount = (originalDiscountAmount / originalPurchaseQty) * quantity;

                if (quantity > availableQty) {
                    quantity = availableQty;
                    $row.find('.quantity-input').val(availableQty);
                }
                if (quantity < 0) {
                    quantity = 0;
                    $row.find('.quantity-input').val(0);
                }

                let lineTotal = price * quantity;
                subtotal += lineTotal;

                // Add to product discount total
                totalProductDiscount += proRatedDiscountAmount;

                // Update discount amount column with pro-rated value
                // Find the discount column (7th column, index 6)
                let discountColumn = $row.find('td').eq(6);
                if (proRatedDiscountAmount > 0) {
                    let originalDiscountPercentage = parseFloat($row.data('original-discount-percentage')) || 0;
                    if (originalDiscountPercentage > 0) {
                        discountColumn.html(
                            `${formatCurrency(proRatedDiscountAmount)} (${originalDiscountPercentage.toFixed(2)}%)`
                        );
                    } else {
                        discountColumn.html(formatCurrency(proRatedDiscountAmount));
                    }
                } else {
                    discountColumn.html(formatCurrency('0.00'));
                }

                // Pro-rate GST
                let gstDetailsRaw = $row.data('gst-details');
                let gstDetails = typeof gstDetailsRaw === 'string' ? JSON.parse(gstDetailsRaw) : gstDetailsRaw;
                let gstHtml = '';
                let rowGst = 0;

                if (gstDetails) {
                    $.each(gstDetails, function(key, gst) {
                        let name = gst.tax_name || gst.name || key;
                        let rate = gst.tax_rate || gst.rate || 0;
                        let amount = gst.tax_amount !== undefined ? gst.tax_amount : (gst.amount !==
                            undefined ? gst.amount : (typeof gst === 'number' ? gst : 0));
                        let proRatedAmount = (parseFloat(amount) / originalPurchaseQty) * quantity;
                        rowGst += proRatedAmount;
                        gstHtml +=
                            `<div><small>${name}(${rate}%): ${formatCurrency(proRatedAmount)}</small></div>`;
                    });
                } else {
                    gstHtml = formatCurrency(0);
                }
                totalGst += rowGst;

                // Find GST column (8th column, index 7)
                $row.find('td').eq(7).html(gstHtml);

                // Find subtotal column (9th column, index 8)
                $row.find('td').eq(8).text(formatCurrency(lineTotal));

                $row.find('.quantity-input').attr('data-gst-total', rowGst);
            });

            // Rest of your calculateTotals function remains the same...
            $('#subtotal').text(
                subtotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2
                })
            );

            // Apply additional discount from input field
            let additionalDiscountPercent = parseFloat($('#discount-input').val()) || 0;
            let additionalDiscountAmount = (subtotal * additionalDiscountPercent) / 100;

            // Total discount (product discounts + additional discount)
            let totalDiscount = totalProductDiscount + additionalDiscountAmount;

            // Update discount display
            let discountDisplayText = '';
            if (totalProductDiscount > 0 && additionalDiscountAmount > 0) {
                discountDisplayText =
                    `${formatCurrency(totalProductDiscount)} + ${formatCurrency(additionalDiscountAmount)} = ${formatCurrency(totalDiscount)}`;
            } else if (totalProductDiscount > 0) {
                discountDisplayText = formatCurrency(totalProductDiscount);
            } else if (additionalDiscountAmount > 0) {
                discountDisplayText = `${additionalDiscountPercent}% ${formatCurrency(additionalDiscountAmount)}`;
            } else {
                discountDisplayText = formatCurrency('0.00');
            }
            $('#discount-display').text(discountDisplayText);

            let afterDiscount = subtotal - totalDiscount;
            $('#after-discount').text(
                afterDiscount.toLocaleString('en-IN', {
                    minimumFractionDigits: 2
                })
            );

            // Remove existing tax rows
            $('.total-order ul').find('li.tax-row').remove();

            // Add GST row if total GST > 0
            if (totalGst > 0) {
                let taxRow = `<li class="tax-row"><h4>Total GST</h4><h5>${formatCurrency(totalGst)}</h5></li>`;
                $('.total-order ul .after-discount').after(taxRow);
            }

            // Check if all items are fully returned
            let allItemsFullReturn = true;
            $('#product-table-body tr').each(function() {
                let $row = $(this);
                let quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                let availableQty = parseFloat($row.data('available-qty')) || 0;
                let purchaseQty = parseFloat($row.data('purchase-qty')) || 0;
                let alreadyReturned = purchaseQty - availableQty;

                if ((quantity + alreadyReturned) < purchaseQty) {
                    allItemsFullReturn = false;
                }
            });

            let currentShipping = allItemsFullReturn ? shippingCharge : 0;
            $('#shipping-amount').text(
                currentShipping.toLocaleString('en-IN', {
                    minimumFractionDigits: 2
                })
            );

            let grandTotal = afterDiscount + totalGst + currentShipping;
            $('#grand-total').text(
                grandTotal.toLocaleString('en-IN', {
                    minimumFractionDigits: 2
                })
            );

            let newPending = Math.max(0, (originalTotalAmount - grandTotal) - originalPaidAmount);
            $('#pendingAmountText').text(formatCurrency(newPending));
        }

        // Discount input validation
        // $(document).on("input", "#discount-input", function() {
        //     let value = parseFloat($(this).val());
        //     let errorDiv = $("#discount-error");

        //     if (value > 100) {
        //         $(this).val(100);
        //         errorDiv.text("Discount cannot be greater than 100%").show();
        //     } else if (value < 0) {
        //         $(this).val(0);
        //         errorDiv.text("Discount cannot be less than 0%").show();
        //     } else {
        //         errorDiv.hide();
        //         calculateTotals();
        //     }
        // });

        $('#invoice_id').on('change', function() {
            let invoiceId = $(this).val();
            if (invoiceId) {
                $.ajax({
                    url: '{{ url('api/get-invoice-products') }}/' + invoiceId,
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        console.log('API Response:', response); // Debug: Check API response

                        currencySymbol = response.currencySymbol;
                        currencyPosition = response.currencyPosition;
                        $('#vendor_name').val(response.invoice.vendor_name);
                        $('#discount-input').val(response.invoice.discount || 0);

                        shippingCharge = parseFloat(response.invoice.shipping) || 0;
                        $('#shipping-display').val(
                            shippingCharge.toLocaleString('en-IN', {
                                minimumFractionDigits: 2
                            })
                        );

                        originalPaidAmount = parseFloat(response.invoice.paid_amount) || 0;
                        originalTotalAmount = parseFloat(response.invoice.total_amount) || 0;

                        $('#paidAmountText').text(formatCurrency(originalPaidAmount));
                        $('#pendingAmountText').text(formatCurrency(response.invoice.remaining_amount));

                        let $tableBody = $('#product-table-body').empty();
                        $.each(response.products, function(index, item) {
                            console.log('Product Discount Data:', {
                                name: item.product_name,
                                discount_amount: item.discount_amount,
                                discount_percentage: item.discount_percentage
                            }); // Debug: Check product discount data

                            let returnedQty = item.purchase_qty - item.quantity;
                            let discountAmount = parseFloat(item.discount_amount) || 0;
                            let discountPercentage = parseFloat(item.discount_percentage) || 0;

                            // Calculate discount percentage if not provided but discount amount exists
                            if (discountPercentage === 0 && discountAmount > 0 && item.price >
                                0) {
                                discountPercentage = (discountAmount / item.price) * 100;
                            }

                            // Format discount display for initial load
                            let discountDisplay = formatCurrency('0.00');
                            if (discountAmount > 0) {
                                if (discountPercentage > 0) {
                                    discountDisplay =
                                        `${formatCurrency(discountAmount)} (${discountPercentage.toFixed(2)}%)`;
                                } else {
                                    discountDisplay = formatCurrency(discountAmount);
                                }
                            } else {
                                // If no discount, show 0.00
                                discountDisplay = formatCurrency('0.00');
                            }

                            let row = `
                        <tr data-purchase-id="${item.purchase_id}"
                            data-product-id="${item.product_id}"
                            data-available-qty="${item.quantity}"
                            data-price="${item.price}"
                            data-purchase-qty="${item.purchase_qty}"
                            data-gst-details='${JSON.stringify(item.product_gst_details)}'
                            data-original-discount-amount="${discountAmount}"
                            data-original-discount-percentage="${discountPercentage}">
                            <td>${index + 1}</td>
                            <td class="productimgname">
                                <a href="javascript:void(0);" style="display: flex; align-items: center; text-decoration: none; color: inherit;">
                                    <img src="${item.image}" alt="${item.product_name}" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                    <span class="pro_purhcaes">${item.product_name}</span>
                                </a>
                            </td>
                            <td>${item.purchase_qty}</td>
                            <td>${returnedQty}</td>
                            <td>
                                <input type="number" class="form-control quantity-input" style="width: 80px;"
                                    step="1" min="0" max="${item.quantity}" value="0"
                                    data-price="${item.price}"
                                    data-discount-amount="${discountAmount}"
                                    data-discount-percentage="${discountPercentage}"
                                    data-original-qty="${item.purchase_qty}"/>
                            </td>
                            <td>${formatCurrency(item.price)}</td>
                            <td class="discount-column">${discountDisplay}</td>
                            <td class="item-gst"></td>
                            <td class="item-subtotal">${formatCurrency(0)}</td>
                        </tr>`;
                            $tableBody.append(row);
                        });
                        calculateTotals();
                    },
                    error: function(xhr) {
                        console.error('API Error:', xhr);
                        Swal.fire('Error', 'Failed to fetch invoice products', 'error');
                    }
                });
            } else {
                $('#vendor_name').val('');
                $('#product-table-body').empty();
                calculateTotals();
            }
        });

        $(document).on('input', '.quantity-input', function() {
            calculateTotals();
        });

        $('.btn-submit').on('click', function() {
            if ($submitBtn.prop('disabled')) {
                return;
            }
            let products = [];
            let totalProductDiscount = 0;

            $('#product-table-body tr').each(function() {
                let $row = $(this);
                let quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                if (quantity > 0) {
                    let price = parseFloat($row.data('price'));
                    let purchaseQty = parseFloat($row.data('purchase-qty')) || 1;

                    // Get original discount amount and percentage
                    let originalDiscountAmount = parseFloat($row.data('original-discount-amount')) || 0;
                    let originalDiscountPercentage = parseFloat($row.data(
                        'original-discount-percentage')) || 0;

                    // Calculate pro-rated discount
                    let proRatedDiscountAmount = (originalDiscountAmount / purchaseQty) * quantity;
                    totalProductDiscount += proRatedDiscountAmount;

                    let gstDetailsRaw = $row.data('gst-details');
                    let gstDetails = typeof gstDetailsRaw === 'string' ? JSON.parse(gstDetailsRaw) :
                        gstDetailsRaw;
                    let proRatedGstDetails = [];
                    let proRatedGstTotal = 0;

                    if (gstDetails) {
                        $.each(gstDetails, function(key, gst) {
                            let name = gst.tax_name || gst.name || key;
                            let rate = gst.tax_rate || gst.rate || 0;
                            let amount = gst.tax_amount !== undefined ? gst.tax_amount : (gst
                                .amount !== undefined ? gst.amount : (typeof gst === 'number' ?
                                    gst : 0));
                            let proRatedAmount = (parseFloat(amount) / purchaseQty) * quantity;

                            proRatedGstDetails.push({
                                name: name,
                                rate: rate,
                                amount: proRatedAmount
                            });
                            proRatedGstTotal += proRatedAmount;
                        });
                    }

                    products.push({
                        purchase_id: $row.data('purchase-id'),
                        product_id: $row.data('product-id'),
                        quantity: quantity,
                        price: price,
                        subtotal: price * quantity,
                        discount_percentage: originalDiscountPercentage, // Send discount percentage
                        discount_amount: proRatedDiscountAmount, // Send discount amount
                        product_gst_details: proRatedGstDetails,
                        product_gst_total: proRatedGstTotal
                    });
                }
            });

            if (products.length === 0) {
                Swal.fire('Warning', 'Please enter return quantity for at least one product', 'warning');
                return;
            }

            let additionalDiscountPercent = parseFloat($('#discount-input').val()) || 0;
            let subtotal = 0;
            products.forEach(p => {
                subtotal += p.subtotal;
            });
            let additionalDiscountAmount = (subtotal * additionalDiscountPercent) / 100;

            let data = {
                _token: '{{ csrf_token() }}',
                invoice_id: $('#invoice_id').val(),
                discount: additionalDiscountPercent,
                discount_amount: additionalDiscountAmount,
                product_discount_total: totalProductDiscount,
                shipping: shippingCharge,
                products: products
            };

            console.log('Sending data to server:', data); // Debug log

            $.ajax({
                url: '{{ url('api/return-purchase') }}',
                method: 'POST',
                beforeSend: function() {
                    toggleSubmitLoading(true);
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                data: data,
                success: function(res) {
                    if (res.success) {
                        Swal.fire('Success', res.message, 'success').then(() => {
                            window.location.href = "{{ route('purchasereturn.list') }}";
                        });
                    } else {
                        Swal.fire('Error', res.error || 'Failed to process return', 'error');
                    }
                },
                error: function(xhr) {
                    console.error('Error response:', xhr);
                    Swal.fire('Error', 'An error occurred: ' + (xhr.responseJSON?.error ||
                        'Unknown error'), 'error');
                },
                complete: function() {
                    toggleSubmitLoading(false);
                }
            });
        });
    </script>
@endpush
