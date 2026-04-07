@php
    $typeLabel = $isQuotation ? 'Quotation' : 'Order';
    $createdAt = $order->created_at ? $order->created_at->timezone('Asia/Kolkata')->format('d M Y h:i A') : '-';

    $currencySymbol = $setting?->currency_symbol ?? 'Rs. ';
    $currencyPosition = strtolower($setting?->currency_position ?? 'left');

    $formatAmount = function ($amount) use ($currencySymbol, $currencyPosition) {
        $formatted = number_format((float) $amount, 2);
        return $currencyPosition === 'right' ? "{$formatted} {$currencySymbol}" : "{$currencySymbol}{$formatted}";
    };

    $orderItems = $order
        ->orderItems()
        ->with(['product.unit'])
        ->get();
    $labourItems = $order->labour_items()->with('labourItem')->get();

    $productAmount = 0.0;
    $lineDiscountAmount = 0.0;

    foreach ($orderItems as $item) {
        $qty = (float) ($item->quantity ?? 0);
        $price = (float) ($item->price ?? 0);
        $lineTotalExclGst = $qty * $price;

        $productAmount += $lineTotalExclGst;
        $lineDiscountAmount += (float) ($item->discount_amount ?? 0);
    }

    $globalDiscountPercent = (float) ($order->discount ?? 0);
    $globalDiscountAmount = $globalDiscountPercent > 0 ? ($productAmount * $globalDiscountPercent) / 100 : 0.0;

    $discountAmount = $lineDiscountAmount + $globalDiscountAmount;
    $afterDiscountAmount = max($productAmount - $discountAmount, 0);

    $shippingCharge = (float) ($order->shipping ?? 0);
    $labourCharge = (float) $labourItems->sum(function ($item) {
        return ((float) ($item->price ?? 0)) * ((float) ($item->qty ?? 0));
    });

    $itemGstAmount = (float) $orderItems->sum(function ($item) {
        return (float) ($item->product_gst_total ?? 0);
    });

    $taxRows = [];
    if (!empty($order->tax_id)) {
        $taxRows = is_array($order->tax_id) ? $order->tax_id : (json_decode($order->tax_id, true) ?: []);
    }
    $taxAmountFromTaxRows = collect($taxRows)->sum(function ($tax) {
        return (float) ($tax['amount'] ?? 0);
    });

    $gstAmount = $taxAmountFromTaxRows > 0 ? $taxAmountFromTaxRows : $itemGstAmount;

    $grandTotal = (float) ($order->total_amount ?? $afterDiscountAmount + $shippingCharge + $labourCharge + $gstAmount);

    if ($gstAmount <= 0) {
        $balanceTax = $grandTotal - ($afterDiscountAmount + $shippingCharge + $labourCharge);
        if ($balanceTax > 0) {
            $gstAmount = $balanceTax;
        }
    }

    $paidAmount = (float) $order
        ->payments()
        ->where(function ($query) {
            $query->whereNull('isDeleted')->orWhere('isDeleted', 0);
        })
        ->sum('payment_amount');

    if ($paidAmount <= 0 && !is_null($order->remaining_amount)) {
        $paidAmount = max($grandTotal - (float) $order->remaining_amount, 0);
    }

    $pendingAmount = !is_null($order->remaining_amount)
        ? max((float) $order->remaining_amount, 0)
        : max($grandTotal - $paidAmount, 0);
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $typeLabel }} Created</title>
</head>

<body style="margin:0; padding:0; background:#edf1f6; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="padding:24px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="760"
                    style="max-width:760px; width:100%; background:#ffffff; border:1px solid #d9e1ec; border-radius:14px; overflow:hidden;">
                    <tr>
                        <td
                            style="background:#f7fafc; border-bottom:1px solid #d9e1ec; border-top:5px solid #ec8d2f; padding:20px 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td valign="middle" style="width:45%; padding-right:14px;">
                                        @if (!empty($setting?->logo_url))
                                            <img src="{{ $setting->logo_url }}"
                                                alt="{{ $setting->name ?? config('app.name') }}"
                                                style="max-width:170px; height:auto; display:block;">
                                        @else
                                            <div style="font-size:18px; font-weight:700; color:#111827;">
                                                {{ $setting?->name ?? config('app.name') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td valign="middle" align="right"
                                        style="width:55%; font-size:13px; line-height:1.6; color:#334155;">
                                        <div
                                            style="display:inline-block; margin-bottom:10px; background:#ec8d2f; color:#ffffff; border-radius:999px; font-size:13px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; padding:5px 12px;">
                                            {{ $typeLabel }} Confirmation
                                        </div>
                                        <div
                                            style="font-size:18px; font-weight:700; line-height:1.2; color:#0f172a; margin-bottom:4px;">
                                            {{ $setting?->name ?? config('app.name') }}
                                        </div>
                                        @if (!empty($setting?->phone))
                                            <div><strong>Phone:</strong> {{ $setting->phone }}</div>
                                        @endif
                                        @if (!empty($setting?->email))
                                            <div><strong>Email:</strong> {{ $setting->email }}</div>
                                        @endif
                                        @if (!empty($setting?->address))
                                            <div><strong>Address:</strong> {{ $setting->address }}</div>
                                        @endif
                                        @if (!empty($setting?->gst_num))
                                            <div><strong>GST:</strong> {{ $setting->gst_num }}</div>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0; font-size:13px; color:#334155;">Hello
                                {{ $customer->name ?? 'Customer' }},</p>
                            <p style="margin:10px 0 16px 0; font-size:13px; line-height:1.6; color:#334155;">
                                Your {{ strtolower($typeLabel) }} has been created successfully. Please find the
                                details below.
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="margin-bottom:18px; background:#f8fafc; border:1px solid #d9e1ec; border-radius:10px;">
                                <tr>
                                    <td style="padding:11px 12px; font-size:13px; color:#334155;">
                                        <strong>{{ $typeLabel }} No:</strong> {{ $order->order_number ?? '-' }}
                                    </td>
                                    <td align="right" style="padding:11px 12px; font-size:13px; color:#334155;">
                                        <strong>Date:</strong> {{ $createdAt }}</td>
                                </tr>
                            </table>

                            <div
                                style="font-size:14px; font-weight:700; color:#0f172a; text-align:center; margin:4px 0 8px 0; letter-spacing:0.4px; text-transform:uppercase;">
                                Product
                            </div>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="border:1px solid #d9e1ec; background:#ffffff;">
                                <tr style="background:#f79b3e;">
                                    <th align="center"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Sr No</th>
                                    <th align="left"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Product Name</th>
                                    <th align="left"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Unit</th>
                                    <th align="center"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Qty</th>
                                    <th align="right"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Price</th>
                                    <th align="right"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Discount Amount</th>
                                    <th align="right"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Total (Excl.GST)</th>
                                </tr>
                                @forelse($orderItems as $index => $item)
                                    @php
                                        $qty = (float) ($item->quantity ?? 0);
                                        $price = (float) ($item->price ?? 0);
                                        $lineTotalExclGst = $qty * $price;
                                        $lineDiscount = (float) ($item->discount_amount ?? 0);
                                        $lineDiscountPercent = (float) ($item->discount_percentage ?? 0);
                                        $productName =
                                            $item->product?->name ??
                                            ($item->product?->product_name ??
                                                'Product #' . ($item->product_id ?? 'N/A'));
                                        $unitName = $item->product?->unit?->unit_name ?? 'N/A';
                                    @endphp
                                    <tr style="background:#f8fafc;">
                                        <td align="center"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $index + 1 }}</td>
                                        <td
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $productName }}</td>
                                        <td
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $unitName }}</td>
                                        <td align="center"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') }}</td>
                                        <td align="right"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $formatAmount($price) }}</td>
                                        <td align="right"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $formatAmount($lineDiscount) }}
                                            @if ($lineDiscountPercent > 0)
                                                ({{ rtrim(rtrim(number_format($lineDiscountPercent, 2, '.', ''), '0'), '.') }}%)
                                            @endif
                                        </td>
                                        <td align="right"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            <strong>{{ $formatAmount($lineTotalExclGst) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" align="center"
                                            style="padding:10px; font-size:13px; color:#64748b; border:1px solid #d9e1ec;">
                                            No products found in this {{ strtolower($typeLabel) }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </table>

                            <div
                                style="font-size:14px; font-weight:700; color:#0f172a; text-align:center; margin:12px 0 8px 0; letter-spacing:0.4px; text-transform:uppercase;">
                                Labour Items
                            </div>
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                style="border:1px solid #d9e1ec; background:#ffffff;">
                                <tr style="background:#f79b3e;">
                                    <th align="center"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Sr No</th>
                                    <th align="left"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Labour Name</th>
                                    <th align="center"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Qty</th>
                                    <th align="right"
                                        style="padding:9px 8px; font-size:13px; color:#ffffff; border:1px solid #d9e1ec;">
                                        Price</th>
                                </tr>
                                @forelse($labourItems as $index => $labour)
                                    @php
                                        $labourQty = (float) ($labour->qty ?? 0);
                                        $labourLineAmount = ((float) ($labour->price ?? 0)) * $labourQty;
                                    @endphp
                                    <tr style="background:#f8fafc;">
                                        <td align="center"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $index + 1 }}</td>
                                        <td
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ $labour->labourItem?->item_name ?? 'Labour #' . ($labour->labour_item_id ?? 'N/A') }}
                                        </td>
                                        <td align="center"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            {{ rtrim(rtrim(number_format($labourQty, 2, '.', ''), '0'), '.') }}
                                        </td>
                                        <td align="right"
                                            style="padding:8px; font-size:13px; color:#111827; border:1px solid #d9e1ec;">
                                            <strong>{{ $formatAmount($labourLineAmount) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" align="center"
                                            style="padding:10px; font-size:13px; color:#64748b; border:1px solid #d9e1ec;">
                                            No labour items.
                                        </td>
                                    </tr>
                                @endforelse
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:18px;">
                                <tr>
                                    <td align="right">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                            width="100%"
                                            style="max-width:470px; border:1px solid #22c55e; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 8px 20px rgba(15, 23, 42, 0.08);">
                                            <tr>
                                                <td colspan="2"
                                                    style="padding:12px 16px; background:#ecfdf5; border-bottom:1px solid #bbf7d0; font-size:15px; font-weight:700; color:#14532d; letter-spacing:0.6px; text-transform:uppercase;">
                                                    Totals
                                                </td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                    Total Amount</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($productAmount) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                    Discount Amount</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($discountAmount) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                    After Discount Amount</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($afterDiscountAmount) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                    Shipping Charge</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($shippingCharge) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                    Labour Charge</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($labourCharge) }}</td>
                                            </tr>
                                            @if ($gstAmount > 0)
                                                <tr>
                                                    <td
                                                        style="padding:10px 16px; font-size:13px; color:#334155; border-bottom:1px solid #dcfce7;">
                                                        GST Amount</td>
                                                    <td align="right"
                                                        style="padding:10px 16px; font-size:13px; font-weight:600; color:#0f172a; border-bottom:1px solid #dcfce7;">
                                                        {{ $formatAmount($gstAmount) }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td
                                                    style="padding:12px 16px; font-size:16px; font-weight:700; color:#0f172a; background:#f8fafc; border-top:1px solid #dcfce7; border-bottom:1px solid #bbf7d0;">
                                                    Grand Total</td>
                                                <td align="right"
                                                    style="padding:12px 16px; font-size:18px; font-weight:700; color:#0f172a; background:#f8fafc; border-top:1px solid #dcfce7; border-bottom:1px solid #bbf7d0;">
                                                    {{ $formatAmount($grandTotal) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; font-weight:700; color:#16a34a; border-bottom:1px solid #dcfce7;">
                                                    Paid Amount</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:700; color:#16a34a; border-bottom:1px solid #dcfce7;">
                                                    {{ $formatAmount($paidAmount) }}</td>
                                            </tr>
                                            <tr>
                                                <td
                                                    style="padding:10px 16px; font-size:13px; font-weight:700; color:#dc2626;">
                                                    Pending Amount</td>
                                                <td align="right"
                                                    style="padding:10px 16px; font-size:13px; font-weight:700; color:#dc2626;">
                                                    {{ $formatAmount($pendingAmount) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0"
                                width="100%" style="margin-top:10px;">
                                <tr>
                                    <td align="right"
                                        style="background:#ea8b2d; padding:12px 16px; border:1px solid #c9711b; border-radius:8px; font-size:17px; font-weight:700; color:#ffffff;">
                                        Grand Total : {{ $formatAmount($grandTotal) }}
                                    </td>
                                </tr>
                            </table>

                            @if (!empty($order->remarks))
                                <p style="margin:16px 0 0 0; font-size:13px; color:#334155; line-height:1.6;">
                                    <strong>Remarks:</strong> {{ $order->remarks }}
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td align="center"
                            style="background-color:#f4f4f4; padding:12px 16px; border-top:1px solid #e5e7eb;">
                            <div style="font-size:14px; font-weight:600; color:#111827;">
                                &copy; {{ date('Y') }} Copyright -
                                {{ $setting?->name ?? 'Fablead Developers Technolab' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
