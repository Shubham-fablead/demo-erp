@extends('layout.app')

@section('title', 'Return Sale')

@section('content')
    <style>
        .d-none {
            display: none !important;
        }

        .img-flag {
            vertical-align: middle;
        }

        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important
            }
        }

        .table-responsive table td:nth-child(2),
        .table-responsive table th:nth-child(2) {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            max-width: 250px;
            min-width: 150px;
        }

        .table-responsive table td:nth-child(2) a {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            display: inline-block;
            max-width: 100%;
        }

        .mobile-detail-row .mobile-detail-value {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
        }

        .mobile-order-item .productimgname span {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            display: inline-block;
            max-width: calc(100% - 50px);
        }

        .productimgname {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .productimgname a {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .productimgname a span {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal !important;
            flex: 1;
        }

        .productimgname img {
            flex-shrink: 0;
        }

        .table-responsive table td {
            white-space: nowrap;
        }

        .table-responsive table td:nth-child(2) {
            white-space: normal !important;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Create Return Sale</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(2, 'add') && app('hasPermission')(2, 'edit'))
                    <a href="{{ route('salesreturn.list') }}" class="btn btn-added">Back</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order ID</label>
                            <div class="row">
                                <div class="col-lg-12 col-sm-12 col-12">
                                    <select name="user_id" class="form-control select2-invoices">
                                        <option value="">Select Order ID</option>
                                        @foreach ($invoiceNumbers as $invoiceNumber)
                                            <option value="{{ $invoiceNumber }}">{{ $invoiceNumber }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Name</label>
                            <div class="input-groupicon">
                                <input type="tel" id="customer_phone" class="form-control"
                                    placeholder="Customer Name" name="customer_phone"
                                    value="{{ $sales->user->phone ?? '' }}" readonly>
                                <span class="error_customerphone"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12 col-sm-6 col-12 d-none">
                        <div class="form-group">
                            <label>Product Name</label>
                            <div class="input-groupicon">
                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/scanner.svg' }}" alt="img">
                                </div>
                            </div>
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
                                    <th>Sold Qty</th>
                                    <th>Already Returned</th>
                                    <th>Return Qty</th>
                                    <th>Price</th>
                                    <th>Discount Amt</th>
                                    <th class="gst-column" style="display: none;">GST</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="row justify-content-end">
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="order-subtotal">
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if ($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="subtotal">0.00</span>
                                            @else
                                                <span id="subtotal">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="discount">
                                        <h4>Discount</h4>
                                        <h5 id="discount">0.00</h5>
                                    </li>

                                    <li class="after-discount">
                                        <h4>After Discount</h4>
                                        <h5>
                                            @if ($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="after-discount">0.00</span>
                                            @else
                                                <span id="after-discount">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    {{-- Shipping row: dynamically added/removed by JS --}}

                                    {{-- GST row: dynamically added/removed by JS --}}

                                    <li class="total">
                                        <h4>Total</h4>
                                        <h5>
                                            @if ($currencyPosition == 'left')
                                                {{ $currencySymbol }} <span id="grand-total">0.00</span>
                                            @else
                                                <span id="grand-total">0.00</span> {{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li style="border-top:1px dashed #ddd; padding-top:10px;">
                                        <h4 style="color:#2E7D32;">Paid Amount</h4>
                                        <h5 id="paidAmountText" style="color:#2E7D32;font-weight:600;">
                                            @if ($currencyPosition == 'left')
                                                {{ $currencySymbol }}0.00
                                            @else
                                                0.00{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li>
                                        <h4 style="color:#C62828;">Pending Amount</h4>
                                        <h5 id="pendingAmountText" style="color:#C62828;font-weight:600;">
                                            @if ($currencyPosition == 'left')
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

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">
                            <span class="spinner-border spinner-border-sm d-none" role="status"
                                aria-hidden="true" id="submit-spinner"></span>
                            <span id="submit-text">Update Order</span>
                        </button>
                        <a href="{{ route('sales.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {

    // ─── Global Variables ────────────────────────────────────────────────────────
    const currencySymbol   = @json($currencySymbol);
    const currencyPosition = @json($currencyPosition);
    const authToken        = localStorage.getItem("authToken");

    let originalPaidAmount  = 0;
    let originalTotalAmount = 0;
    let shippingCharge      = 0;   // ← Set when invoice is loaded

    // ─── Helpers ─────────────────────────────────────────────────────────────────
    function formatCurrency(amount) {
        let num       = parseFloat(amount || 0);
        let formatted = num.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        return currencyPosition === 'left'
            ? `${currencySymbol}${formatted}`
            : `${formatted}${currencySymbol}`;
    }

    /**
     * Returns true only when EVERY row's (returnQty + alreadyReturned) === originalQty.
     * This is the condition that unlocks shipping refund.
     */
    function areAllItemsFullyReturned() {
        let allFull = true;

        $('#product-table-body tr').each(function () {
            const $row          = $(this);
            const returnQty     = parseFloat($row.find('.quantity-input').val()) || 0;
            const originalQty   = parseFloat($row.data('original-qty'))          || 0;
            const availableQty  = parseFloat($row.data('available-qty'))         || 0;
            const alreadyReturned = originalQty - availableQty;

            if ((returnQty + alreadyReturned) < originalQty) {
                allFull = false;
                return false; // break $.each
            }
        });

        return allFull;
    }

    // ─── Calculate Totals ────────────────────────────────────────────────────────
    function calculateTotals() {
        let subtotal            = 0;
        let totalDiscountAmount = 0;
        let totalGST            = 0;

        $('#product-table-body tr').each(function () {
            const $row                    = $(this);
            const quantity                = parseFloat($row.find('.quantity-input').val())                      || 0;
            const originalQty             = parseFloat($row.data('original-qty'))                               || 1;
            const price                   = parseFloat($row.find('.quantity-input').data('price'))              || 0;
            const originalDiscountAmount  = parseFloat($row.data('original-discount-amount'))                   || 0;
            const originalDiscountPct     = parseFloat($row.data('original-discount-percentage'))               || 0;
            const gstTotalPerItem         = parseFloat($row.find('.quantity-input').data('gst-total'))          || 0;

            // Pro-rate discount & GST
            const proRatedDiscount = (originalDiscountAmount / originalQty) * quantity;
            const proRatedGST      = (gstTotalPerItem         / originalQty) * quantity;

            const lineTotal = price * quantity;
            subtotal            += lineTotal;
            totalDiscountAmount += proRatedDiscount;
            totalGST            += proRatedGST;

            // ── Update Discount column (index 6) ──
            const $discountCol = $row.find('td:eq(6)');
            if (proRatedDiscount > 0) {
                $discountCol.html(
                    originalDiscountPct > 0
                        ? `${formatCurrency(proRatedDiscount)} (${originalDiscountPct.toFixed(2)}%)`
                        : formatCurrency(proRatedDiscount)
                );
            } else {
                $discountCol.html(formatCurrency('0.00'));
            }

            // ── Update GST column (index 7) ──
            const gstDetailsRaw = $row.data('gst-details');
            const gstDetails    = typeof gstDetailsRaw === 'string'
                ? JSON.parse(gstDetailsRaw)
                : gstDetailsRaw;

            let gstHtml = '';
            if (gstDetails && (Array.isArray(gstDetails) ? gstDetails.length : Object.keys(gstDetails).length)) {
                $.each(gstDetails, function (key, gst) {
                    const name          = gst.tax_name || key;
                    const rate          = gst.tax_rate  || 0;
                    const amount        = parseFloat(gst.tax_amount || 0);
                    const proRatedAmt   = (amount / originalQty) * quantity;
                    gstHtml += `<div><small>${name}(${rate}%): ${formatCurrency(proRatedAmt)}</small></div>`;
                });
            } else {
                gstHtml = proRatedGST > 0
                    ? formatCurrency(proRatedGST)
                    : formatCurrency('0.00');
            }
            $row.find('.item-gst').html(gstHtml);

            // ── Update Subtotal column (index 8) ──
            $row.find('td:eq(8)').text(formatCurrency(lineTotal));
        });

        // ── Summary panel ──────────────────────────────────────────────────────
        $('#subtotal').text(subtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

        // Discount display
        let discountText = '';
        if (totalDiscountAmount > 0) {
            discountText = formatCurrency(totalDiscountAmount);
        } else {
            discountText = formatCurrency('0.00');
        }
        $('#discount').text(discountText);

        const afterDiscount = subtotal - totalDiscountAmount;
        $('#after-discount').text(afterDiscount.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

        // Remove dynamic rows before re-inserting
        $('.total-order ul').find('li.tax-row, li.shipping-row').remove();

        // GST row (inserted after after-discount)
        if (totalGST > 0) {
            $('.total-order ul .after-discount').after(
                `<li class="tax-row"><h4>Total GST</h4><h5>${formatCurrency(totalGST)}</h5></li>`
            );
        }

        // ── Shipping: only add when ALL items are fully returned ──────────────
        const shippingAmount = areAllItemsFullyReturned() ? shippingCharge : 0;

        if (shippingAmount > 0) {
            const shippingRow = `<li class="shipping-row"><h4>Shipping</h4><h5>${formatCurrency(shippingAmount)}</h5></li>`;
            // Insert after GST row if it exists, otherwise after after-discount
            if (totalGST > 0) {
                $('.total-order ul .tax-row').after(shippingRow);
            } else {
                $('.total-order ul .after-discount').after(shippingRow);
            }
        }

        // Grand Total
        const grandTotal = afterDiscount + totalGST + shippingAmount;
        $('#grand-total').text(grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 }));

        // Pending Amount
        const newPending = Math.max(0, (originalTotalAmount - grandTotal) - originalPaidAmount);
        $('#pendingAmountText').text(formatCurrency(newPending));
        $('#paidAmountText').text(formatCurrency(originalPaidAmount));
    }

    // ─── Select2 Init ────────────────────────────────────────────────────────────
    $('.select2-invoices').select2({
        placeholder: 'Select Order ID',
        allowClear:  true,
        width:       '100%'
    });

    // ─── Invoice Change → Load Products (SINGLE handler) ────────────────────────
    $('select[name="user_id"]').on('change', function () {
        const invoiceNumber     = $(this).val();
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

        if (!invoiceNumber) {
            // Reset everything when cleared
            $('#customer_phone').val('');
            $('#product-table-body').empty();
            shippingCharge      = 0;
            originalPaidAmount  = 0;
            originalTotalAmount = 0;
            calculateTotals();
            return;
        }

        $.ajax({
            url:  `/api/getSaleDetails/${invoiceNumber}`,
            type: 'GET',
            headers: { "Authorization": "Bearer " + authToken },
            data: { selectedSubAdminId: selectedSubAdminId },

            success: function (data) {
                if (data.error) { alert(data.error); return; }

                // ── Store order-level values ──────────────────────────────────
                shippingCharge      = parseFloat(data.order.shipping)          || 0;
                originalPaidAmount  = parseFloat(data.order.paid_amount)       || 0;
                originalTotalAmount = parseFloat(data.order.total_amount)      || 0;

                $('#customer_phone').val(data.order.user_name);
                $('#paidAmountText').text(formatCurrency(originalPaidAmount));
                $('#pendingAmountText').text(formatCurrency(parseFloat(data.order.remaining_amount) || 0));

                // ── Build product rows ────────────────────────────────────────
                const $tableBody = $('#product-table-body').empty();
                let hasGst = false;

                $.each(data.items, function (index, item) {
                    const price            = parseFloat(item.price)                  || 0;
                    const gstDetails       = item.product_gst_details;
                    const gstTotal         = parseFloat(item.product_gst_total)      || 0;
                    const soldQuantity     = parseFloat(item.sold_quantity)           || 0;
                    const availableQty     = parseFloat(item.quantity)               || 0;   // returnable
                    const returnedQuantity = parseFloat(item.returned_quantity)       || 0;

                    let discountAmount     = parseFloat(item.discount_amount)        || 0;
                    let discountPct        = parseFloat(item.discount_percentage)    || 0;

                    // Cross-calculate if one is missing
                    if (discountPct === 0 && discountAmount > 0 && price > 0) {
                        discountPct = (discountAmount / price) * 100;
                    }
                    if (discountAmount === 0 && discountPct > 0 && price > 0) {
                        discountAmount = (price * discountPct) / 100;
                    }

                    // Discount display for initial load (qty = 0, so show 0.00)
                    const discountDisplay = formatCurrency('0.00');

                    // GST column
                    if (gstTotal > 0 || (gstDetails && (Array.isArray(gstDetails) ? gstDetails.length : Object.keys(gstDetails).length))) {
                        hasGst = true;
                    }

                    let gstHtml = '';
                    if (gstDetails && (Array.isArray(gstDetails) ? gstDetails.length : Object.keys(gstDetails).length)) {
                        $.each(gstDetails, function (key, gst) {
                            gstHtml += `<div><small>${gst.tax_name || key}(${gst.tax_rate || 0}%): ${formatCurrency('0.00')}</small></div>`;
                        });
                    } else {
                        gstHtml = formatCurrency('0.00');
                    }

                    const row = `
                        <tr data-product-id="${item.id}"
                            data-gst-details='${JSON.stringify(gstDetails)}'
                            data-gst-total="${gstTotal}"
                            data-original-qty="${soldQuantity}"
                            data-available-qty="${availableQty}"
                            data-original-discount-amount="${discountAmount}"
                            data-original-discount-percentage="${discountPct}">
                            <td>${index + 1}</td>
                            <td style="width:100%;">
                                <a href="/product-view/${item.product_id}" style="display:flex;align-items:center;text-decoration:none;color:inherit;">
                                    <img src="${item.product_image
                                        ? '{{ env('ImagePath') }}/storage/' + item.product_image
                                        : '{{ env('ImagePath') }}/admin/assets/img/product/noimage.png'}"
                                        alt="${item.product_name}" width="40" height="40"
                                        style="object-fit:cover;border-radius:4px;margin-right:10px;flex-shrink:0;">
                                    <span style="flex:1;">${item.product_name || 'N/A'}</span>
                                </a>
                            </td>
                            <td>${soldQuantity}</td>
                            <td>${returnedQuantity}</td>
                            <td>
                                <input type="number" class="form-control quantity-input"
                                    value="0" step="1" min="0" max="${availableQty}"
                                    data-price="${price}"
                                    data-gst-total="${gstTotal}"
                                    data-original-discount-amount="${discountAmount}"
                                    data-original-discount-percentage="${discountPct}"
                                    data-original-qty="${soldQuantity}"
                                    style="width:80px;">
                            </td>
                            <td>${formatCurrency(price)}</td>
                            <td class="discount-column">${discountDisplay}</td>
                            <td class="item-gst gst-column">${gstHtml}</td>
                            <td>${formatCurrency(0)}</td>
                            <td></td>
                        </tr>`;
                    $tableBody.append(row);
                });

                // Show/hide GST column header + cells
                if (hasGst) {
                    $('.gst-column').show();
                } else {
                    $('.gst-column').hide();
                }

                calculateTotals();
            },

            error: function () {
                alert('Something went wrong while loading order details!');
            }
        });
    });

    // ─── Quantity Input Change ───────────────────────────────────────────────────
    $(document).on('input', '.quantity-input', function () {
        // Clamp value between 0 and max
        const max = parseFloat($(this).attr('max')) || 0;
        let val   = parseFloat($(this).val())        || 0;
        if (val < 0) { val = 0; $(this).val(0); }
        if (val > max) { val = max; $(this).val(max); }

        calculateTotals();
    });

    // ─── Submit Handler ──────────────────────────────────────────────────────────
    $(document).on('click', '.btn-submit', function (e) {
        e.preventDefault();

        const $btn     = $(this);
        const $spinner = $('#submit-spinner');
        const $text    = $('#submit-text');

        const allFullyReturned = areAllItemsFullyReturned();

        const formData = {
            products:    [],
            discount:    0,   // No additional discount input in this form
            shipping:    allFullyReturned ? shippingCharge : 0,
            grand_total: $('#grand-total').text().replace(/[^\d.]/g, '').trim()
        };

        $('#product-table-body tr').each(function () {
            const $row                   = $(this);
            const productId              = $row.data('product-id');
            const quantity               = parseFloat($row.find('.quantity-input').val()) || 0;
            const originalQty            = parseFloat($row.data('original-qty'))           || 1;
            const price                  = parseFloat($row.find('.quantity-input').data('price')) || 0;
            const originalDiscountAmount = parseFloat($row.data('original-discount-amount'))  || 0;
            const discountPct            = parseFloat($row.data('original-discount-percentage')) || 0;
            const gstTotalPerItem        = parseFloat($row.find('.quantity-input').data('gst-total')) || 0;

            const proRatedDiscount = (originalDiscountAmount / originalQty) * quantity;
            const proRatedGST      = (gstTotalPerItem         / originalQty) * quantity;

            const gstDetailsRaw = $row.data('gst-details');
            const gstDetails    = typeof gstDetailsRaw === 'string'
                ? JSON.parse(gstDetailsRaw)
                : gstDetailsRaw;

            let proRatedGstDetails = [];
            if (gstDetails && (Array.isArray(gstDetails) ? gstDetails.length : Object.keys(gstDetails).length)) {
                $.each(gstDetails, function (key, gst) {
                    const amount      = parseFloat(gst.tax_amount || 0);
                    const proRatedAmt = (amount / originalQty) * quantity;
                    proRatedGstDetails.push({
                        tax_name:   gst.tax_name  || key,
                        tax_rate:   gst.tax_rate  || 0,
                        tax_amount: proRatedAmt.toFixed(2)
                    });
                });
            }

            if (productId && quantity > 0) {
                formData.products.push({
                    order_item_id:       productId,
                    quantity:            quantity,
                    price:               price,
                    subtotal:            (price * quantity).toFixed(2),
                    discount_amount:     proRatedDiscount.toFixed(2),
                    discount_percentage: discountPct,
                    product_gst_details: proRatedGstDetails,
                    product_gst_total:   proRatedGST.toFixed(2)
                });
            }
        });

        if (formData.products.length === 0) {
            Swal.fire({
                title: 'Error',
                text:  'Please enter a return quantity greater than 0 for at least one product.',
                icon:  'error',
                confirmButtonText:  'OK',
                confirmButtonColor: '#ffa957'
            });
            return;
        }

        $btn.prop('disabled', true);
        $spinner.removeClass('d-none');
        $text.text('Updating...');

        $.ajax({
            url:  '/api/return_sale',
            type: 'POST',
            headers: { "Authorization": "Bearer " + authToken },
            data: formData,

            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Success',
                        text:  response.message,
                        icon:  'success',
                        confirmButtonText:  'OK',
                        confirmButtonColor: '#ffa957'
                    }).then(result => {
                        if (result.isConfirmed) {
                            window.location.href = "{{ route('salesreturn.list') }}";
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text:  response.message || 'Something went wrong.',
                        icon:  'error',
                        confirmButtonText:  'OK',
                        confirmButtonColor: '#ffa957'
                    });
                    $btn.prop('disabled', false);
                    $spinner.addClass('d-none');
                    $text.text('Update Order');
                }
            },

            error: function () {
                Swal.fire({
                    title: 'Error',
                    text:  'An error occurred while updating the order.',
                    icon:  'error',
                    confirmButtonText:  'OK',
                    confirmButtonColor: '#ffa957'
                });
                $btn.prop('disabled', false);
                $spinner.addClass('d-none');
                $text.text('Update Order');
            }
        });
    });

}); // end document.ready
</script>
@endpush
