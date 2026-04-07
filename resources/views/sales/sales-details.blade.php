@extends('layout.app')

@section('title', 'Sale Details')

@section('content')
    <style>
        .logo_img {
            max-width: 150px;
            height: auto;
        }

        .invoice-box table td {
            white-space: nowrap;
        }

        /* Product Name Column - Allow Wrapping */
        .invoice-box table tbody tr.details td:first-child,
        .invoice-box table tbody tr.details td:nth-child(1) {
            white-space: normal !important;
            word-wrap: break-word;
            word-break: break-word;
            max-width: 300px;
            min-width: 200px;
        }

        .invoice-box table tbody tr.details td:first-child a,
        .invoice-box table tbody tr.details td:nth-child(1) a {
            word-wrap: break-word;
            word-break: break-word;
            white-space: normal;
            max-width: 100%;
        }

        .invoice-box table tr.heading td:first-child {
            min-width: 200px;
            max-width: 300px;
        }

        @media (max-width: 767px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .page-btn {
                display: grid !important;
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 5px !important;
                width: 100% !important;
                margin-top: 10px !important;
            }

            .page-btn a,
            .page-btn button {
                margin: 0 !important;
                padding: 8px 2px !important;
                font-size: 11px !important;
                display: flex !important;
                align-items: center;
                justify-content: center;
                text-align: center;
                width: 100% !important;
                white-space: normal !important;
                line-height: 1.1;
            }

            .invoice-box {
                overflow-x: auto;
            }

            .card-sales-split {
                flex-direction: row;
            }

            .row {
                display: flex;
                flex-wrap: wrap;
            }

            .col-lg-6.table_view {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .col-lg-6.total-order {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .invoice-box,
            .invoice-box table,
            .invoice-box td,
            .invoice-box h4,
            .invoice-box h5 {
                font-size: 12px;
                line-height: 1.2;
            }

            .invoice-box table tr.heading td:first-child,
            .invoice-box table tr.details td:first-child {
                min-width: 200px !important;
            }

            .logo_img {
                max-width: 130px;
                height: auto;
            }

            font {
                line-height: 22px !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Sale Details</h4>
            </div>

            <div class="page-btn d-flex gap-2">
                @if (app('hasPermission')(2, 'add'))
                <a href="{{ route('sales.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-1"></i> Add Sale
                </a>
                @endif

                @if (app('hasPermission')(2, 'edit') && !$hasReturnStarted)
                    <a href="{{ route('sales.edit', $view_id) }}" class="btn btn-primary">
                        <i class="fa fa-edit me-1"></i> Edit
                    </a>
                @endif

                @if (app('hasPermission')(2, 'view'))
                <a href="{{ route('sales.invoice', $view_id) }}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-file-invoice"></i> Invoice
                </a>
                @endif

                @if (app('hasPermission')(2, 'view'))
                <a href="{{ route('sales.invoice.pdf', $view_id) }}" target="_blank" class="btn btn-primary">
                    <i class="fa fa-print me-1"></i> Print
                </a>
                @endif

                @if (app('hasPermission')(2, 'view'))
                <a href="{{ route('sales.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                    <i class="fa fa-arrow-left me-1"></i> Back
                </a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <input type="hidden" name="selse_id" id="selse_id" value="{{ $view_id }}">

                <div class="download_pdf">
                    <div class="invoice-box table-height"
                        style="max-width: 1600px;width:100%;margin:15px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                            <tbody id="product-details"></tbody>
                        </table>
                    </div>

                    <div id="quotation-items-section" class="invoice-box"
                        style="display:none; max-width: 1600px; width:100%; margin:30px auto; padding:0; font-size:14px; line-height:24px; color:#555;">
                        <table cellpadding="0" cellspacing="0"
                            style="width:100%; line-height: inherit; text-align:left; border-collapse: collapse;"
                            border="1">
                            <thead>
                                <tr style="background: #F3F2F7;">
                                    <th style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Labour Name</th>
                                    <th style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Qty</th>
                                    <th style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Price</th>
                                </tr>
                            </thead>
                            <tbody id="quotation-items-details"></tbody>
                        </table>
                    </div>

                    <!-- Return History Section -->
                    <div id="return-history-section" class="invoice-box"
                        style="display:none; max-width: 1600px;width:100%;margin:30px auto;padding: 0;font-size: 14px;line-height: 24px;color: #555;">
                        <table cellpadding="0" cellspacing="0" style="width: 100%;line-height: inherit;text-align: left;">
                            <tbody id="return-history-details"></tbody>
                        </table>
                    </div>

                    <div class="row">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                </div>
                            </div>

                            @php
                                $subtotal = 0;
                                $totalDiscountAmount = 0;
                                $totalTaxAmount = 0;

                                foreach ($orderItems as $item) {
                                    $lineSubtotal = $item->price * $item->quantity;
                                    $subtotal += $lineSubtotal;
                                    $totalDiscountAmount += $item->discount_amount ?? 0;
                                    $totalTaxAmount += $item->product_gst_total ?? 0;
                                }

                                $afterDiscount = $subtotal - $totalDiscountAmount;
                                $finalTotal = $afterDiscount + $totalTaxAmount;

                                $totalTaxAmount = 0;
                                $taxDetails = [];
                                foreach ($orderItems as $item) {
                                    $totalTaxAmount += $item->product_gst_total ?? 0;
                                }
                            @endphp

                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                    <ul>
                                        @php
                                            if (!function_exists('formatCurrency')) {
                                                function formatCurrency($amount, $symbol, $position = 'left')
                                                {
                                                    return $position === 'right'
                                                        ? number_format($amount, 2) . $symbol
                                                        : $symbol . number_format($amount, 2);
                                                }
                                            }
                                        @endphp

                                        <li>
                                            <h4>Subtotal</h4>
                                            <h5>{{ formatCurrency($subtotal, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                        </li>

                                        <li>
                                            <h4>Discount Amount</h4>
                                            <h5>{{ formatCurrency($totalDiscountAmount, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                        </li>

                                        <li>
                                            <h4>Price After Discount</h4>
                                            <h5>{{ formatCurrency($afterDiscount, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                        </li>

                                        @foreach ($taxDetails as $tax)
                                            <li>
                                                <h4>{{ $tax['name'] }} Tax</h4>
                                                <h5>{{ $tax['rate'] }}%
                                                    ({{ formatCurrency($tax['amount'], $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }})
                                                </h5>
                                            </li>
                                        @endforeach

                                        <li>
                                            <h4>Total GST Amount</h4>
                                            <h5>{{ formatCurrency($totalTaxAmount, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                        </li>

                                        @if (isset($sales))
                                            <li>
                                                <h4>Shipping Charge</h4>
                                                <h5>{{ formatCurrency($sales->shipping ?? 0, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                            </li>
                                        @endif

                                        {{-- ✅ Return Amount: dynamically updated by JS --}}
                                        <li id="returnAmountRow" style="display:none;">
                                            <h4 style="color:#ea5455;">Return Amount</h4>
                                            <h5 id="returnamount" style="color:#ea5455; font-weight:600;">₹0.00</h5>
                                        </li>

                                        {{-- ✅ Return Status: dynamically updated by JS --}}
                                        <li id="returnStatusRow">
                                            <h4>Return Status</h4>
                                            <h5 id="returnstatus">—</h5>
                                        </li>

                                        <li class="laborcharge">
                                            <h4>Labour Charge</h4>
                                            <h5 id="labourChargeText">₹0.00</h5>
                                        </li>

                                        <li class="total">
                                            <h4>Total</h4>
                                            <h5>{{ formatCurrency($finalTotal, $setting->currency_symbol ?? '₹', $setting->currency_position ?? 'left') }}</h5>
                                        </li>

                                        <li style="border-top:1px dashed #ddd; padding-top:10px;">
                                            <h4 style="color:#2E7D32;">Paid Amount</h4>
                                            <h5 id="paidAmountText" style="color:#2E7D32;font-weight:600;">₹0.00</h5>
                                        </li>

                                        <li>
                                            <h4 style="color:#C62828;">Pending Amount</h4>
                                            <h5 id="pendingAmountText" style="color:#C62828;font-weight:600;">₹0.00</h5>
                                        </li>

                                        <li id="extraPaidRow" style="display: none;">
                                            <h4 style="color:#dc3545;">Extra Paid</h4>
                                            <h5 id="extraPaidText" style="color:#dc3545;font-weight:600;">₹0.00</h5>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="text-align: center; margin-top: 100px;">
                        <h2 style="color: #7367F0; font-size: 24px; margin-bottom: 10px;">Thank You for Your Purchase!</h2>
                        <p style="font-size: 16px; color: #555;">We appreciate your business and hope to serve you again soon.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        const userAddress = @json($userAddress);
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.querySelector('.download_pdf');
            const opt = {
                margin: 10,
                filename: 'invoice_SL0101.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        function formatCurrency(amount, symbol = '₹', position = 'left') {
            let formattedAmount = parseFloat(amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return position === 'left' ? `${symbol}${formattedAmount}` : `${formattedAmount}${symbol}`;
        }

        $(document).ready(function () {
            var authToken        = localStorage.getItem("authToken");
            let salse_id         = $("#selse_id").val();
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

            if (salse_id) {
                fetchsalseDetails(salse_id);
            }

            function fetchsalseDetails(salse_id) {
                $.ajax({
                    url: `/api/getsalseById/${salse_id}`,
                    type: "GET",
                    data: { selectedSubAdminId: selectedSubAdminId },
                    dataType: "json",
                    headers: { "Authorization": "Bearer " + authToken },

                    success: function (response) {

                        // ── Page title ───────────────────────────────────────
                        if (response.sales.quotation_status === 'quotation') {
                            $(".page-title h4").text("Sale Quotation Details");
                        } else {
                            $(".page-title h4").text("Sale Details");
                        }

                        const currSym = response.currency_symbol;
                        const currPos = response.currency_position;

                        // ── Labour charge ────────────────────────────────────
                        let labourSubTotal = 0;
                        (response.labour_items || []).forEach(item => {
                            labourSubTotal += parseFloat(item.price || 0) * parseFloat(item.qty || 0);
                        });
                        $("#labourChargeText").text(formatCurrency(labourSubTotal, currSym, currPos));

                        // ── Grand total ──────────────────────────────────────
                        let grandTotal = parseFloat(response.sales.total_amount || 0);
                        $(".total h5").text(formatCurrency(grandTotal, currSym, currPos));

                        // ── Paid / Pending ───────────────────────────────────
                        const sale = response.sales;
                        $("#paidAmountText").text(formatCurrency(sale.paid_amount ?? 0, currSym, currPos));
                        $("#pendingAmountText").text(formatCurrency(sale.pending_amount ?? 0, currSym, currPos));

                        if (parseFloat(sale.extra_paid) > 0) {
                            $("#extraPaidText").text(formatCurrency(sale.extra_paid, currSym, currPos));
                            $("#extraPaidRow").show();
                        } else {
                            $("#extraPaidRow").hide();
                        }

                        // ── Return Amount & Return Status ────────────────────
                        //
                        // Logic mirrors the Purchase Invoice blade:
                        //   - totalReturnAmount = Σ (price × qty) - discount + gst  per return item
                        //   - isFullyReturned   = every sold item has been fully returned
                        //   - if fully returned → add shipping to return amount
                        //   - Status: No return | Partially Returned | Fully Returned
                        //
                        let totalReturnAmount = 0;
                        const returns = response.returns || [];

                        returns.forEach(ret => {
                            (ret.items || []).forEach(retItem => {
                                const lineTotal      = parseFloat(retItem.price      || 0) * parseFloat(retItem.quantity || 0);
                                const discountAmt    = parseFloat(retItem.discount_amount || 0);
                                const gstAmt         = parseFloat(retItem.product_gst_total || 0);
                                totalReturnAmount   += (lineTotal - discountAmt + gstAmt);
                            });
                        });

                        // Check if all sold items are fully returned
                        const items      = response.order_items || [];
                        const shippingCharge = parseFloat(response.order?.shipping || sale.shipping || 0);

                        let allItemsFullyReturned = true;
                        if (returns.length === 0) {
                            allItemsFullyReturned = false;
                        } else {
                            items.forEach(item => {
                                const soldQty = parseFloat(item.quantity || 0);
                                let returnedQty = 0;

                                returns.forEach(ret => {
                                    (ret.items || []).forEach(retItem => {
                                        // Match by order_item_id or product_id depending on your API
                                        if (
                                            (retItem.order_item_id && retItem.order_item_id == item.id) ||
                                            (retItem.product_id    && retItem.product_id    == item.product_id)
                                        ) {
                                            returnedQty += parseFloat(retItem.quantity || 0);
                                        }
                                    });
                                });

                                if (returnedQty < soldQty) {
                                    allItemsFullyReturned = false;
                                }
                            });
                        }

                        // Add shipping to return amount only when fully returned
                        const totalReturnWithShipping = allItemsFullyReturned
                            ? totalReturnAmount + shippingCharge
                            : totalReturnAmount;

                        // Update Return Amount row
                        if (totalReturnAmount > 0) {
                            let returnAmtHtml = formatCurrency(totalReturnWithShipping, currSym, currPos);
                            // if (allItemsFullyReturned && shippingCharge > 0) {
                            //     returnAmtHtml += ` <small style="font-size:10px;">(Incl. Shipping)</small>`;
                            // }
                            $("#returnamount").html(returnAmtHtml);
                            $("#returnAmountRow").show();
                        } else {
                            $("#returnAmountRow").hide();
                        }

                        // Update Return Status row
                        if (totalReturnAmount === 0) {
                            $("#returnstatus").html(`<span style="color:#28c76f; font-weight:600;">No return</span>`);
                        } else if (allItemsFullyReturned) {
                            $("#returnstatus").html(`<span style="color:#ea5455; font-weight:600;">Fully Returned</span>`);
                        } else {
                            $("#returnstatus").html(`<span style="color:#ff9f43; font-weight:600;">Partially Returned</span>`);
                        }

                        // ── Quotation / Labour table ─────────────────────────
                        const quotationSection = document.getElementById('quotation-items-section');
                        const tbody            = document.getElementById('quotation-items-details');
                        tbody.innerHTML        = '';

                        const qoutation  = response.labour_items || [];
                        const isQuotation = response.sales.quotation_status === 'quotation';

                        if (qoutation.length > 0) {
                            qoutation.forEach(item => {
                                const tr = document.createElement('tr');

                                const tdName = document.createElement('td');
                                tdName.textContent = item.labour_item_name ?? 'N/A';
                                tdName.style.padding = '8px';
                                tr.appendChild(tdName);

                                const tdQty = document.createElement('td');
                                tdQty.textContent = item.qty;
                                tdQty.style.padding = '8px';
                                tr.appendChild(tdQty);

                                const tdPrice = document.createElement('td');
                                tdPrice.textContent = formatCurrency(item.price, currSym, currPos);
                                tdPrice.style.padding = '8px';
                                tr.appendChild(tdPrice);

                                tbody.appendChild(tr);
                            });
                            quotationSection.style.display = 'block';
                        } else {
                            quotationSection.style.display = 'none';
                        }

                        if (response.error) return;

                        const company_info = response.company_info;

                        let hasGst = items.some(item =>
                            Array.isArray(item.product_tax) && item.product_tax.length > 0
                        );

                        const taxHeaderColumns = hasGst
                            ? `<td class="tax-col" style="padding:10px; text-align:center; white-space:normal; vertical-align:middle; font-weight:600; color:#5E5873; font-size:14px;">Product Taxes</td>`
                            : '';

                        let productRows = `
                            <tr class="top col-12">
                                <td colspan="12" style="padding: 10px;vertical-align: top;">
                                    <table style="width: 100%;line-height: inherit;text-align: left;" class="product-list">
                                        <div class="row">
                                            <div class="col-6">
                                                <img src="{{ $setting->logo ? env('ImagePath') . 'storage/' . $setting->logo : env('ImagePath') . '/admin/assets/img/logso.png' }}"
                                                    alt="logo" class="logo_img">
                                            </div>
                                            <div class="col-6 mt-4" style="text-align: end;">
                                                <h1>Invoice</h1>
                                                <h4 class="mt-3">#${sale.order_number}</h4>
                                            </div>
                                        </div>
                                        <hr>
                                        <tbody>
                                            <tr>
                                                <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px; width: 25%;">
                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                        <font style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height:35px;">Customer Info</font>
                                                    </font><br>
                                                    <font style="vertical-align: inherit;">
                                                        <font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-name">${sale.user_name || "walk-in-customer"}</font>
                                                    </font><br>
                                                    ${sale.user_email ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-email">${sale.user_email}</font></font><br>` : ''}
                                                    ${userAddress ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-address">${userAddress}</font></font><br>` : ''}
                                                    ${sale.user_phone ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;" class="customer-phone">${sale.user_phone}</font></font><br>` : ''}
                                                    ${sale.user_gst_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">GST: ${sale.user_gst_number}</font></font><br>` : ''}
                                                    ${sale.user_pan_number ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">PAN: ${sale.user_pan_number}</font></font><br>` : ''}
                                                </td>
                                                <td style="padding:5px; vertical-align:top; text-align:left; padding-bottom:20px; width: 30%;">
                                                    <font style="vertical-align: inherit; margin-bottom:25px;">
                                                        <font style="vertical-align: inherit; font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">Company Info</font>
                                                    </font><br>
                                                    ${company_info.name ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">${company_info.name}</font></font><br>` : ''}
                                                    ${company_info.email ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">${company_info.email}</font></font><br>` : ''}
                                                    ${company_info.phone ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">${company_info.phone}</font></font><br>` : ''}
                                                    ${company_info.address ? `<font style="vertical-align: inherit; display: block; max-width: 300px; word-wrap: break-word; white-space: normal;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">${company_info.address}</font></font>` : ''}
                                                    ${company_info.gst_num ? `<font style="vertical-align: inherit;"><font style="vertical-align: inherit; font-size: 14px; font-weight: 400;">GST: ${company_info.gst_num}</font></font><br>` : ''}
                                                </td>
                                                <td style="padding:5px;vertical-align:top;text-align:left;padding-bottom:20px; width: 45%;" colspan="2">
                                                    <font style="vertical-align: inherit;margin-bottom:25px;">
                                                        <font style="vertical-align: inherit;font-size:14px;color:#7367F0;font-weight:600;line-height: 35px;">${isQuotation ? 'Quotation Info' : 'Order Info'}</font>
                                                    </font><br>
                                                    <table style="width: 100%; border-collapse: collapse;">
                                                        ${isQuotation ? `
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Quotation Number</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none;">#${sale.order_number}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Quotation Date</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none;">${sale.created_at}</td>
                                                            </tr>
                                                        ` : `
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Order Number</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none;" class="invoice-id">#${sale.order_number}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Order Date</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none;">${sale.created_at}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Payment Status</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none; color:#2E7D32;" class="payment-status">${sale.payment_status.charAt(0).toUpperCase() + sale.payment_status.slice(1)}</td>
                                                            </tr>
                                                            <tr>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; border: none;">Payment Method</td>
                                                                <td style="padding: 2px 0; font-size: 14px; font-weight: 400; text-align: right; border: none; text-transform: capitalize; color:#2E7D32;" class="order-method">${sale.payment_method}</td>
                                                            </tr>
                                                            ${allItemsFullyReturned ? `
                                                                <tr>
                                                                    <td style="padding: 2px 0; font-size: 14px; color: #ea5455;">Order Status</td>
                                                                    <td style="padding: 2px 0; font-weight: bold; text-align: right; color: #ea5455;">Fully Returned</td>
                                                                </tr>
                                                            ` : ''}
                                                        `}
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr class="heading" style="background: #F3F2F7;">
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Product Name</td>
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Unit</td>
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Qty</td>
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Price</td>
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px;">Discount Amount</td>
                                ${taxHeaderColumns}
                                <td style="padding: 5px;vertical-align: middle;font-weight: 600;color: #5E5873;font-size: 14px;padding: 10px; text-align: right;">Total (Excl. GST)</td>
                            </tr>`;

                        items.forEach(item => {
                            const product               = item.product;
                            const discountAmount        = Number(item.discount_amount ?? 0);
                            const discountPercentageVal = parseFloat(item.discount_percentage ?? 0);
                            const showDiscountPct       = discountPercentageVal > 0;
                            const totalWithoutGst       = item.price * item.quantity;

                            let taxesHtml = '';
                            if (Array.isArray(item.product_tax) && item.product_tax.length > 0) {
                                item.product_tax.forEach(tax => {
                                    taxesHtml += `<div>${tax.tax_name} (${tax.tax_rate}%): ${formatCurrency(tax.tax_amount, currSym, currPos)}</div>`;
                                });
                            }

                            let imagePath = '';
                            const imageBasePath = '{{ env('ImagePath') }}';
                            try {
                                const images = JSON.parse(product.images);
                                imagePath = (images && images.length > 0 && images[0])
                                    ? `${imageBasePath}/public/storage/${images[0]}`
                                    : `${imageBasePath}/public/admin/assets/img/product/noimage.png`;
                            } catch (e) {
                                imagePath = `${imageBasePath}/public/admin/assets/img/product/noimage.png`;
                            }

                            const taxBodyColumns = hasGst
                                ? `<td class="tax-col" style="padding:10px; white-space:normal; text-align:center; word-wrap:break-word;">${taxesHtml && taxesHtml.trim() !== '' ? taxesHtml : 'N/A'}</td>`
                                : '';

                            productRows += `
                                <tr class="product-row">
                                    <td class="text-capitalize" style="padding: 10px; align:left; vertical-align: top;">
                                        <a href="/product-view/${product.id}" class="d-flex align-items-center text-decoration-none text-dark">
                                            <img src="${imagePath}" alt="img" class="me-2" style="width:40px; height:40px;">
                                            ${product.name}
                                        </a>
                                    </td>
                                    <td class="text-capitalize" style="padding: 10px;vertical-align: top;">${item.product?.unit?.unit_name ?? 'N/A'}</td>
                                    <td style="padding: 10px;vertical-align: top;">${item.quantity}</td>
                                    <td style="padding: 10px;vertical-align: top;">${formatCurrency(item.price, company_info.currency_symbol, company_info.currency_position)}</td>
                                    <td style="padding:10px; vertical-align:top;">
                                        ${formatCurrency(discountAmount, company_info.currency_symbol, company_info.currency_position)}
                                        ${showDiscountPct ? `<small>(${discountPercentageVal.toFixed(2)}%)</small>` : ''}
                                    </td>
                                    ${taxBodyColumns}
                                    <td style="padding: 10px;vertical-align: top; text-align: right;">${formatCurrency(totalWithoutGst, company_info.currency_symbol, company_info.currency_position)}</td>
                                </tr>`;
                        });

                        // ── Return History Table ──────────────────────────────
                        if (returns.length > 0) {
                            let hasReturnTax = false;
                            returns.forEach(ret => {
                                (ret.items || []).forEach(retItem => {
                                    if (parseFloat(retItem.product_gst_total || 0) > 0) hasReturnTax = true;
                                });
                            });

                            const colCount = hasReturnTax ? 8 : 7;

                            let returnRows = `
                                <tr class="heading" style="background: #F3F2F7;">
                                    <td colspan="${colCount}" style="padding: 10px; font-weight: 600; color: #7367F0; font-size: 16px; text-align: center;">Return History</td>
                                </tr>
                                <tr class="heading" style="background: #F8F9FA;">
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Return ID</td>
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Return Date</td>
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Product Name</td>
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Return Qty</td>
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Price</td>
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Discount Amt</td>
                                    ${hasReturnTax ? '<td style="padding: 10px; font-weight: 600; font-size: 14px;">Tax</td>' : ''}
                                    <td style="padding: 10px; font-weight: 600; font-size: 14px;">Total</td>
                                </tr>`;

                            returns.forEach(ret => {
                                const retDate         = new Date(ret.created_at);
                                const formattedRetDate = retDate.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
                                    + ' ' + retDate.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: true });

                                (ret.items || []).forEach(retItem => {
                                    const lineTotal     = retItem.price * retItem.quantity;
                                    const discountAmt   = parseFloat(retItem.discount_amount   || 0);
                                    const gstAmount     = parseFloat(retItem.product_gst_total || 0);
                                    const finalTotal    = lineTotal - discountAmt + gstAmount;

                                    returnRows += `
                                        <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                                            <td style="padding: 10px; vertical-align: top;">#${ret.return_number}</td>
                                            <td style="padding: 10px; vertical-align: top;">${formattedRetDate}</td>
                                            <td style="padding: 10px; vertical-align: top;">${retItem.product ? retItem.product.name : 'N/A'}</td>
                                            <td style="padding: 10px; vertical-align: top;">${retItem.quantity}</td>
                                            <td style="padding: 10px; vertical-align: top;">${formatCurrency(retItem.price, currSym, currPos)}</td>
                                            <td style="padding: 10px; vertical-align: top;">
                                                ${formatCurrency(discountAmt, currSym, currPos)}
                                                ${retItem.discount > 0 ? `<small>(${parseFloat(retItem.discount).toFixed(2)}%)</small>` : ''}
                                            </td>
                                            ${hasReturnTax ? `<td style="padding: 10px; vertical-align: top;">${formatCurrency(gstAmount, currSym, currPos)}</td>` : ''}
                                            <td style="padding: 10px; vertical-align: top;">${formatCurrency(finalTotal, currSym, currPos)}</td>
                                        </tr>`;
                                });
                            });

                            $("#return-history-details").html(returnRows);
                            $("#return-history-section").show();
                        } else {
                            $("#return-history-section").hide();
                        }

                        $("#product-details").html(productRows);

                        // Update customer display fields
                        $(".customer-name").text(sale.user_name || "walk-in-customer");
                        $(".customer-phone").text(sale.user_phone || "N/A");
                        $(".invoice-id").text(sale.order_number);
                        $(".payment-status").text(sale.payment_status.charAt(0).toUpperCase() + sale.payment_status.slice(1));
                        $(".order-status").text("Completed");
                    },

                    error: function (xhr, status, error) {
                        // console.error("Error fetching sales details:", error);
                    }
                });
            }
        });
    </script>
@endpush
