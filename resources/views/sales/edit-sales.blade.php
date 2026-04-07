@extends('layout.app')

@section('title', 'Edit Sales')

@section('content')
    <style>
        .d-none {
            display: none !important;
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

        .img-flag {
            vertical-align: middle;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 29px !important;
        }

        .gst-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }

        .gst-badge.with {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .gst-badge.without {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e9ecef;
        }

        .product-gst-details {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 8px;
            margin-top: 5px;
            font-size: 12px;
        }

        .product-gst-details small {
            display: block;
            line-height: 1.4;
        }
        .page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap:1px;
}

.page-header .page-title {
    display: flex;
    align-items: center;
}

.page-header .form-check {
    display: flex;
    align-items: center;
    margin-bottom: 0;
}

.gst-header {
    display: flex;
    align-items: center;
    margin-bottom: 0 !important;
}

.gst-header .d-flex {
    display: flex;
    align-items: center;
    
}

.custom-radio-label {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-bottom: 0;
}

.form-check-input {
    margin-top: 0;
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
    margin-left: 6px;
}

.pos-back-btn:hover {
    background: #ff9f43;
    color: #fff;
}

.pos-back-btn i {
    font-size: 12px;
    line-height: 1;
}

@media (max-width: 767.98px) {
    .page-header {
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 10px;
    }

    .page-header .form-check {
        margin-left: 0 !important;
    }

    .gst-header {
        width: 100%;
        flex-wrap: wrap;
        gap: 10px;
    }

    .gst-header .d-flex {
        flex-wrap: wrap;
        row-gap: 8px;
    }

    .custom-radio-label {
        white-space: nowrap;
    }

    .pos-back-btn {
        margin-left: auto;
        min-width: 70px;
        justify-content: center;
    }
}
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Sales</h4>
            </div>
            @php
                $user = auth()->user();
            @endphp
            @php
                $subAdminId = session('selectedSubAdminId');
                $role = Auth::user()->role;
            @endphp
            @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                <div class="form-check ms-3">
                    <input class="form-check-input me-2" type="checkbox" id="quotationToggle" value="quotation"
                        {{ ($sales->quotation_status ?? 'sales') === 'quotation' ? 'checked' : '' }}>
                    <label class="form-check-label" for="quotationToggle">Quotation</label>
                </div>
            @endif
            <div class="gst-header mb-4" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex">
                    <label class="custom-radio-label" style="margin-right: 1rem;">
                        <input type="radio" name="gst_option" id="without_gst" value="without_gst"
                            {{ $sales->gst_option === 'without_gst' ? 'checked' : '' }} />
                        Without GST
                    </label>

                    <label class="custom-radio-label">
                        <input type="radio" name="gst_option" id="with_gst" value="with_gst"
                            {{ $sales->gst_option === 'with_gst' ? 'checked' : '' }} />
                        With GST
                    </label>
                </div>
                <a href="{{ route('sales.list') }}" class="pos-back-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back
                </a>
            </div>
        </div>



        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <input type="hidden" name="update_selse_id" id="update_selse_id" value="{{ $update_id }}">
                        <div class="form-group">
                            <label>Customer</label>
                            <select name="customer_id" id="customer_id" class="form-control select2">
                                <option value="">Select Customer</option>
                                @foreach ($usernames as $user)
                                    <option value="{{ $user->id }}" data-phone="{{ $user->phone }}"
                                        {{ $sales->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Number</label>
                            <div class="input-groupicon">
                                <input type="tel" id="customer_phone" class="form-control" placeholder="Customer number"
                                    name="customer_phone" value="{{ $sales->user->phone ?? '' }}" readonly>
                                <span class="error_customerphone"></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Order Date</label>
                            <div class="input-groupicon">
                                <input type="date" class="form-control" name="order_date" {{-- value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required> --}}
                                    value="{{ \Carbon\Carbon::parse($sales->created_at)->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Product Name</label>
                            <div class="input-groupicon">
                                <select name="product_id[]" class="form-control product-select" multiple="multiple">
                                    @php
                                        $user = auth()->user();
                                        $branchIdToUse = $user->role === 'staff' ? $user->branch_id : $user->id;
                                        $settings = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
                                        $currencySymbol = $settings->currency_symbol ?? '₹';
                                        $currencyPosition = $settings->currency_position ?? 'left';
                                        $selectedProductIds = old(
                                            'product_id',
                                            $sales->order_items->pluck('product_id')->toArray(),
                                        );
                                        $selectedProductIds = array_map('strval', $selectedProductIds);
                                    @endphp

                                    @foreach ($products as $product)
                                        @php
                                            $images = json_decode($product->images ?? '', true);
                                            $imageUrl =
                                                !empty($images) && isset($images[0])
                                                    ? env('ImagePath') . 'storage/' . $images[0]
                                                    : env('ImagePath') . '/admin/assets/img/product/noimage.png';
                                            $priceFormatted = number_format($product->price, 2);
                                            $displayPrice =
                                                $currencyPosition === 'right'
                                                    ? $priceFormatted . ' ' . $currencySymbol
                                                    : $currencySymbol . $priceFormatted;

                                            // Get product GST info
                                            $gstOption = $product->gst_option ?? 'without_gst';
                                            $gstDetails = null;
                                            if ($gstOption === 'with_gst' && $product->product_gst) {
                                                try {
                                                    $gstDetails = json_decode($product->product_gst, true);
                                                } catch (\Exception $e) {
                                                    $gstDetails = null;
                                                }
                                            }
                                        @endphp

                                        <option value="{{ $product->id }}" data-image="{{ $imageUrl }}"
                                            data-price="{{ $product->price }}" data-name="{{ $product->name }}"
                                            data-unit="{{ $product->unit->unit_name ?? 'N/A' }}"
                                            data-gst-option="{{ $gstOption }}"
                                            data-product-gst="{{ $product->product_gst ?? '[]' }}"
                                            data-discount="{{ $product->discount ?? 0 }}"
                                            {{ in_array((string) $product->id, $selectedProductIds) ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ $displayPrice }}
                                            @if ($gstOption === 'with_gst')
                                                (With GST)
                                            @else
                                                (Without GST)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/scanner.svg' }}"
                                        alt="img">
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
                                    <th>Unit</th>
                                    <th>QTY</th>
                                    <th>Price</th>
                                    <th>Discount %</th>
                                    <th>GST Details</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="product-table-body">
                                @php
                                    $currencySymbol = $settings->currency_symbol ?? '₹';
                                    $currencyPosition = $settings->currency_position ?? 'left';
                                @endphp

                                @forelse ($sales->order_items as $index => $item)
                                    @php
                                        $product = $item->product;
                                        $gstOption = $product->gst_option ?? 'without_gst';

                                        // Get GST details from order_item, not from product
                                        $gstDetails = null;
                                        $productGstTotal = $item->product_gst_total ?? 0;

                                        if (!empty($item->product_gst_details)) {
                                            if (is_array($item->product_gst_details)) {
                                                $gstDetails = $item->product_gst_details;
                                            } else {
                                                try {
                                                    $gstDetails = json_decode($item->product_gst_details, true);
                                                    // Handle double-encoded JSON payloads.
                                                    if (is_string($gstDetails)) {
                                                        $gstDetails = json_decode($gstDetails, true);
                                                    }
                                                } catch (\Exception $e) {
                                                    $gstDetails = null;
                                                }
                                            }
                                        }

                                        // Handle single GST object shape.
                                        if (is_array($gstDetails) && isset($gstDetails['tax_name'])) {
                                            $gstDetails = [$gstDetails];
                                        }

                                        // Fallback to product GST if order_item doesn't have details
if (
    empty($gstDetails) &&
    $gstOption === 'with_gst' &&
    !empty($product->product_gst)
) {
    if (is_array($product->product_gst)) {
        $gstDetails = $product->product_gst;
    } else {
        try {
            $gstDetails = json_decode($product->product_gst, true);
        } catch (\Exception $e) {
            $gstDetails = null;
        }
    }
}

// Calculate base total
$baseTotal = $item->price * $item->quantity;
$finalTotal = $item->total_amount;

// Prepare GST data for data attribute
$gstDataForAttribute = '[]';
                                        if (!empty($gstDetails) && is_array($gstDetails)) {
                                            $gstDataForAttribute = json_encode($gstDetails);
                                        }
                                    @endphp

                                    <tr data-product-id="{{ $item->product_id }}" data-gst-option="{{ $gstOption }}"
                                        data-product-gst="{{ $gstDataForAttribute }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="">
                                            @php
                                                $images = json_decode($item->product->images ?? '', true);
                                            @endphp
                                            <a class="product-img">
                                                @if (!empty($images) && isset($images[0]))
                                                    <img src="{{ env('ImagePath') . 'storage/' . $images[0] }}"
                                                        alt="product" width="40">
                                                @else
                                                    <img src="{{ env('ImagePath') . '/admin/assets/img/product/noimage.png' }}"
                                                        alt="No image" width="40">
                                                @endif
                                            </a>
                                            <a href="javascript:void(0);">{{ $item->product->name ?? 'N/A' }}</a>
                                            <span class="gst-badge {{ $gstOption === 'with_gst' ? 'with' : 'without' }}">
                                                {{ $gstOption === 'with_gst' ? 'With GST' : 'Without GST' }}
                                            </span>
                                        </td>
                                        <td data-label="Unit">
                                            {{ $item->product->unit->unit_name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            <input type="number" name="quantities[{{ $item->product_id }}]"
                                                class="form-control quantity-input"
                                                value="{{ number_format($item->quantity, 2, '.', '') }}" step="1"
                                                min="0" data-price="{{ $item->price }}" style="width: 80px;">
                                        </td>
                                        <td>{{ number_format($item->price, 2) }}</td>
                                        <td>
                                            <input type="number" name="discounts[{{ $item->product_id }}]"
                                                class="form-control discount-input"
                                                value="{{ number_format($item->discount_percentage ?? 0, 2, '.', '') }}"
                                                min="0" max="100" step="0.01" style="width: 80px;">
                                        </td>
                                        <td class="gst-details-cell">
                                            @if ($gstDetails && is_array($gstDetails))
                                                <div class="product-gst-details">
                                                    @foreach ($gstDetails as $tax)
                                                        <small>
                                                            {{ $tax['tax_name'] ?? 'GST' }}: {{ $tax['tax_rate'] ?? 0 }}%
                                                            @if (isset($tax['tax_amount']))
                                                                ({{ number_format($tax['tax_amount'], 2) }})
                                                            @endif
                                                        </small>
                                                    @endforeach
                                                    @if ($productGstTotal > 0)
                                                        <small style="font-weight: bold; color: #333;">
                                                            GST Total:
                                                            {{ $currencyPosition === 'right' ? number_format($productGstTotal, 2) . $currencySymbol : $currencySymbol . number_format($productGstTotal, 2) }}
                                                        </small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">No GST</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="total-amount">
                                                <div style="color:#ff9f43;">
                                                    <strong>Sub Total:</strong>
                                                    @if ($currencyPosition === 'right')
                                                        {{ number_format($baseTotal, 2) }}{{ $currencySymbol }}
                                                    @else
                                                        {{ $currencySymbol }}{{ number_format($baseTotal, 2) }}
                                                    @endif
                                                </div>

                                                @if ($gstOption === 'with_gst')
                                                    <div style="color:#007bff;">
                                                        <strong>GST Included:</strong>
                                                        @if ($currencyPosition === 'right')
                                                            {{ number_format($baseTotal + $productGstTotal, 2) }}{{ $currencySymbol }}
                                                        @else
                                                            {{ $currencySymbol }}{{ number_format($baseTotal + $productGstTotal, 2) }}
                                                        @endif
                                                    </div>
                                                @endif

                                                @php
                                                    $discountAmt = $item->price * $item->quantity * (($item->discount_percentage ?? 0) / 100);
                                                @endphp

                                                @if ($discountAmt > 0)
                                                    <div style="color:red;">
                                                        <strong>Discount:</strong> -
                                                        @if ($currencyPosition === 'right')
                                                            {{ number_format($discountAmt, 2) }}{{ $currencySymbol }}
                                                        @else
                                                            {{ $currencySymbol }}{{ number_format($discountAmt, 2) }}
                                                        @endif
                                                    </div>
                                                @endif

                                                <div
                                                    style="font-weight:bold; margin-top:4px; border-top:1px solid #ddd; padding-top:3px;color:green;">
                                                    Final Total:
                                                    @if ($currencyPosition === 'right')
                                                        {{ number_format($item->total_amount, 2) }}{{ $currencySymbol }}
                                                    @else
                                                        {{ $currencySymbol }}{{ number_format($item->total_amount, 2) }}
                                                    @endif
                                                </div>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" class="delete-set">
                                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}"
                                                    alt="svg">
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="no-products-row">
                                        <td colspan="7" class="text-center">No products selected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>



                <div class="row">

                    <div class="col-lg-4 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Shipping</label>
                            <input type="number" class="form-control" name="shipping" id="shipping-input"
                                value="{{ $sales->shipping ?? 0 }}" min="0" step="0.01">
                            <div id="shipping-error" class="text-danger mt-1" style="display:none;"></div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6" id="payment_method_col">
                        <div class="form-group">
                            <label>Payment Method</label>
                            <select class="select form-control" name="payment_method">
                                <option value="pending" {{ $sales->payment_method == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>
                                <option value="cash" {{ $sales->payment_method == 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="debit" {{ $sales->payment_method == 'debit' ? 'selected' : '' }}>Debit
                                </option>
                                <option value="scan" {{ $sales->payment_method == 'scan' ? 'selected' : '' }}>Scan
                                </option>
                                <option value="cheque" {{ $sales->payment_method == 'cheque' ? 'selected' : '' }}>Cheque
                                </option>
                                <option value="cash+online"
                                    {{ $sales->payment_method == 'cash+online' ? 'selected' : '' }}>Cash+Online
                                </option>
                                <option value="online" {{ $sales->payment_method == 'online' ? 'selected' : '' }}>Online
                                </option>
                            </select>
                        </div>
                    </div>



                    <div class="col-lg-4 col-sm-6 col-6" id="payment_status_col">
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="select form-control" name="status">
                                <option value="pending" {{ $sales->payment_status == 'pending' ? 'selected' : '' }}>
                                    Pending
                                </option>

                                <option value="partially" {{ $sales->payment_status == 'partially' ? 'selected' : '' }}>
                                    Partially Paid
                                </option>

                                <option value="completed" {{ $sales->payment_status == 'completed' ? 'selected' : '' }}>
                                    Completed
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="row ">
                        <div class="col-lg-6 mb-3">
                            <div class="form-group">
                                <label>Remarks</label>
                                <textarea class="form-control" name="remarks" id="remarks" rows="6" placeholder="Enter any remarks">{{ old('remarks', $sales->remarks ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="col-lg-6 justify-content-end">
                            @php
                                // 1. Calculate Product Subtotal (Gross)
                                $productsSubtotal = $sales->order_items->sum(function ($item) {
                                    return $item->price * $item->quantity;
                                });

                                // Total product-level discounts
                                $totalProductDiscounts = $sales->order_items->sum(function ($item) {
                                    return $item->price * $item->quantity * (($item->discount_percentage ?? 0) / 100);
                                });

                                // Products Net Subtotal (after product discounts but before order discount)
                                $productsNetSubtotal = $productsSubtotal - $totalProductDiscounts;

                                // 2. Order Discount is now removed
                                $discountPercent = 0;
                                $discountAmount = 0;

                                // 3. After Discount (All Product discounts)
                                $productsAfterDiscount = $productsNetSubtotal;

                                // 4. Calculate Labour Subtotal
                                $labourSubtotal = 0;
                                if (isset($sales) && $sales->labour_items) {
                                    $labourSubtotal = $sales->labour_items->sum(function ($item) {
                                        return $item->qty * $item->price;
                                    });
                                }

                                // 5. Calculate Shipping
                                $shippingCost = $sales->shipping ?? 0;

                                // 6. Calculate Taxes on (Products After Discount) only
                                $taxRates = $TaxRate;
                                $totalTaxAmount = 0;
                                $taxDetails = [];

                                foreach ($taxRates as $tax) {
                                    $taxAmount = ($productsAfterDiscount * $tax->tax_rate) / 100;
                                    $taxDetails[] = [
                                        'name' => $tax->tax_name,
                                        'rate' => $tax->tax_rate,
                                        'amount' => $taxAmount,
                                    ];
                                    $totalTaxAmount += $taxAmount;
                                }

                                // 7. Grand Total
                                $grandTotal =
                                    $productsAfterDiscount + $labourSubtotal + $shippingCost + $totalTaxAmount;
                            @endphp

                            <!-- Labour Items Section -->
                            @if ($role == 'admin' || $role == 'staff' || $role == 'sub-admin')
                                <div class="col-lg-12 mb-3">
                                    <div class="select-split">
                                        <div class="select-group w-100">
                                            <hr>
                                            <h5 style=" font-weight: 600; font-size: 19px; ">Labour Items</h5>
                                            <div id="labour-items-container">
                                                @if (isset($sales->labour_items) && $sales->labour_items->count() > 0)
                                                    @foreach ($sales->labour_items as $index => $item)
                                                        <div class="row mb-2 labour-item-row">
                                                            <div class="col-lg-5 col-sm-4 col-4">
                                                                <select name="labour_item_id[]"
                                                                    class="form-control select2 select2-labour">
                                                                    <option value="">Select Labour Item</option>
                                                                    @foreach ($labourItems as $lItem)
                                                                        <option value="{{ $lItem->id }}"
                                                                            data-price="{{ $lItem->price }}"
                                                                            {{ $item->labour_item_id == $lItem->id ? 'selected' : '' }}>
                                                                            {{ $lItem->item_name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-3 col-sm-3 col-3">
                                                                <input type="number" name="labour_qty[]"
                                                                    class="form-control labour-qty" placeholder="Qty"
                                                                    value="{{ $item->qty }}" min="0">
                                                            </div>
                                                            <div class="col-lg-3 col-sm-3 col-3">
                                                                <input type="number" name="labour_price[]"
                                                                    class="form-control labour-price" placeholder="Price"
                                                                    value="{{ $item->price }}" min="0">
                                                            </div>
                                                            <div class="col-lg-1 col-sm-2 col-2">
                                                                @if ($loop->last)
                                                                    <button type="button" class="btn btn-success add-labour-item">
                                                                        <i class="fas fa-plus"></i>
                                                                    </button>
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-danger remove-labour-item">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="row mb-2 labour-item-row">
                                                        <div class="col-lg-5 col-sm-4 col-4">
                                                            <select name="labour_item_id[]"
                                                                class="form-control select2 select2-labour">
                                                                <option value="">Select Labour Item</option>
                                                                @foreach ($labourItems as $lItem)
                                                                    <option value="{{ $lItem->id }}" data-price="{{ $lItem->price }}">
                                                                        {{ $lItem->item_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-lg-3 col-sm-3 col-3">
                                                            <input type="number" name="labour_qty[]"
                                                                class="form-control labour-qty" placeholder="Qty" value="1"
                                                                min="0">
                                                        </div>
                                                        <div class="col-lg-3 col-sm-3 col-3">
                                                            <input type="number" name="labour_price[]"
                                                                class="form-control labour-price" placeholder="Price"
                                                                value="0" min="0">
                                                        </div>
                                                        <div class="col-lg-1 col-sm-2 col-2">
                                                            <button type="button" class="btn btn-success add-labour-item">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <hr>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- End Labour Items Section -->

                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li class="subtotal">
                                        <h4>Subtotal (Products)</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="subtotal-display">{{ number_format($productsSubtotal, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="subtotal-display">{{ number_format($productsSubtotal, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="product-discount">
                                        <h4>Discounts</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="product-discount-total-display">{{ number_format($totalProductDiscounts, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="product-discount-total-display">{{ number_format($totalProductDiscounts, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <!-- <li class="discount">
                                            <h4>Discount</h4>
                                            <h5>
                                                <span
                                                    id="discount-percent">{{ number_format($discountPercent, 2) }}</span>%
                                                (
                                                @if ($setting->currency_position === 'right')
    <span
                                                        id="discount-amount">{{ number_format($discountAmount, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
@else
    {{ $setting->currency_symbol ?? '₹' }}<span
                                                        id="discount-amount">{{ number_format($discountAmount, 2) }}</span>
    @endif
                                                )
                                            </h5>
                                        </li> -->

                                    <li class="after-discount">
                                        <h4>After Discount</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="after-discount-display">{{ number_format($productsAfterDiscount, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="after-discount-display">{{ number_format($productsAfterDiscount, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>



                                    {{-- <div class="tax-section">
                                        @foreach ($TaxRate as $tax)
                                            <li>
                                                <h4>{{ $tax->tax_name }}</h4>
                                                <h5>{{ number_format($tax->tax_rate, 2) }}% (
                                                    @if ($setting->currency_position === 'right')
                                                        <span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                                    @else
                                                        {{ $setting->currency_symbol ?? '₹' }}<span class="tax-amount"
                                                            data-rate="{{ $tax->tax_rate }}">0.00</span>
                                                    @endif
                                                    )
                                                </h5>
                                            </li>
                                        @endforeach
                                    </div> --}}
                                    <li class="labour-cost">
                                        <h4>Labour Cost</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="labour-cost-display">{{ number_format($labourSubtotal, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="labour-cost-display">{{ number_format($labourSubtotal, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="shipping-cost">
                                        <h4>Shipping Cost</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="shipping-cost-display">{{ number_format($shippingCost, 2) }}</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="shipping-cost-display">{{ number_format($shippingCost, 2) }}</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total-gst" style="display: none;">
                                        <h4>Total GST</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span
                                                    id="total-gst-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span
                                                    id="total-gst-amount">0.00</span>
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total">
                                        <h4>Grand Total</h4>
                                        <h5>
                                            @if ($setting->currency_position === 'right')
                                                <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                            @else
                                                {{ $setting->currency_symbol ?? '₹' }}<span id="grand-total">0.00</span>
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>

                            {{-- <div class="row justify-content-end">
                    <div class="col-lg-6">
                        <div class="total-order w-100 max-widthauto m-auto mb-4">
                            <ul>
                                <li class="subtotal">
                                    <h4>Subtotal</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="subtotal-display">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="subtotal-display">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="discount">
                                    <h4>Discount</h4>
                                    <h5>
                                        <span id="discount-percent">0.00</span>%
                                        (
                                        @if ($setting->currency_position === 'right')
                                            <span id="discount-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="discount-amount">0.00</span>
                                        @endif
                                        )
                                    </h5>
                                </li>

                                <li class="after-discount">
                                    <h4>After Discount</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span
                                                id="after-discount-display">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span
                                                id="after-discount-display">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total-gst" style="display: none;">
                                    <h4>Total GST</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="total-gst-amount">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="total-gst-amount">0.00</span>
                                        @endif
                                    </h5>
                                </li>

                                <li class="total">
                                    <h4>Grand Total</h4>
                                    <h5>
                                        @if ($setting->currency_position === 'right')
                                            <span id="grand-total">0.00</span>{{ $setting->currency_symbol ?? '₹' }}
                                        @else
                                            {{ $setting->currency_symbol ?? '₹' }}<span id="grand-total">0.00</span>
                                        @endif
                                    </h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div> --}}

                            <div class="col-lg-12">
                                <button type="submit" class="btn btn-submit me-2" id="update-order-btn">
                                    <span class="spinner-border spinner-border-sm d-none" role="status"
                                        aria-hidden="true" id="btn-loader"></span>
                                    <span id="btn-text">Update Order</span>
                                </button>
                                <a href="{{ route('sales.list') }}" class="btn btn-cancel">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endsection

            @push('js')
                <script>
                    $(document).ready(function() {
                        const currencySymbol = '{{ $setting->currency_symbol ?? '₹' }}';
                        const currencyPosition = '{{ $setting->currency_position ?? 'left' }}';

                        function formatNumber(amount) {
                            return parseFloat(amount).toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }

                        function formatCurrency(amount) {
                            const formatted = formatNumber(amount);
                            return currencyPosition === 'right' ?
                                formatted + currencySymbol :
                                currencySymbol + formatted;
                        }

                        // Initialize Select2 with images and GST info
                        function formatProduct(product) {
                            if (!product.id) return product.text;

                            const $option = $(product.element);
                            const image = $option.data('image');
                            const name = $option.data('name');
                            const gstOption = $option.data('gst-option');
                            const productGst = $option.data('product-gst');

                            let gstBadge = '';
                            let gstDetails = '';

                            if (gstOption === 'with_gst' && productGst) {
                                try {
                                    const gstData = JSON.parse(productGst);
                                    if (Array.isArray(gstData) && gstData.length > 0) {
                                        const totalRate = gstData.reduce((sum, tax) => sum + parseFloat(tax.tax_rate || 0), 0);
                                        gstBadge = `<span class="badge bg-success ms-2">GST: ${totalRate}%</span>`;
                                        gstDetails = `<div class="small text-muted">`;
                                        gstData.forEach(tax => {
                                            gstDetails += `${tax.tax_name || 'GST'}: ${tax.tax_rate}%<br>`;
                                        });
                                        gstDetails += `</div>`;
                                    }
                                } catch (e) {
                                    gstBadge = `<span class="badge bg-success ms-2">With GST</span>`;
                                }
                            } else {
                                gstBadge = `<span class="badge bg-secondary ms-2">No GST</span>`;
                            }

                            return $(
                                `<span>
                <img src="${image}" class="img-flag" style="width: 20px; margin-right: 10px;" />
                ${name}
                ${gstBadge}
                ${gstDetails}
            </span>`
                            );
                        }

                        $('.product-select').select2({
                            templateResult: formatProduct,
                            templateSelection: formatProduct,
                            closeOnSelect: false
                        });

                        $('.select2-labour').select2({
                            width: '100%'
                        });

                        // Initialize Customer Select2
                        $('#customer_id').select2({
                            placeholder: "Select Customer",
                            allowClear: true
                        });

                        // Handle customer selection change
                        $('#customer_id').on('change', function() {
                            var phone = $(this).find(':selected').data('phone');
                            $('#customer_phone').val(phone || '');
                        });




                        // Add product to table
                        function addProductToTable(productId, productName, productImage, productPrice, gstOption, productGst,
                            existingGstDetails = null, unit = 'N/A', discount = 0) {
                            const rowCount = $('tbody tr[data-product-id]').length + 1;
                            let gstBadge = '';
                            let gstDetailsHtml = '';
                            let gstDetails = [];

                            // Use existing GST details if available (from order_items), otherwise use product GST
                            if (existingGstDetails) {
                                gstDetails = existingGstDetails;
                            } else if (gstOption === 'with_gst' && productGst) {
                                if (typeof productGst === 'string') {
                                    try {
                                        gstDetails = JSON.parse(productGst);
                                    } catch (e) {
                                        gstDetails = [];
                                    }
                                } else if (Array.isArray(productGst)) {
                                    gstDetails = productGst;
                                } else {
                                    gstDetails = [];
                                }
                            }

                            if (gstOption === 'with_gst' && gstDetails.length > 0) {
                                const totalRate = gstDetails.reduce((sum, tax) => sum + parseFloat(tax.tax_rate || 0), 0);
                                gstBadge = `<span class="gst-badge with">With GST (${totalRate}%)</span>`;

                                gstDetailsHtml = '<div class="product-gst-details">';
                                gstDetails.forEach(tax => {
                                    gstDetailsHtml += `<small>${tax.tax_name || 'GST'}: ${tax.tax_rate}%</small>`;
                                });
                                gstDetailsHtml += '</div>';
                            } else {
                                gstBadge = `<span class="gst-badge without">Without GST</span>`;
                                gstDetails = [];
                            }

                            const newRow = `
            <tr data-product-id="${productId}" data-gst-option="${gstOption}" data-product-gst='${JSON.stringify(gstDetails).replace(/'/g, "&#39;")}'>
                <td>${rowCount}</td>
                <td class="">
                    <a class="product-img">
                        <img src="${productImage}" alt="product" width="40">
                    </a>
                    <a href="javascript:void(0);">${productName}</a>
                    ${gstBadge}
                </td>
                <td data-label="Unit">${unit}</td>
                <td>
                    <input type="number"
                           name="quantities[${productId}]"
                           class="form-control quantity-input"
                           value="1"
                           step="1"
                           min="0"
                           data-price="${productPrice}"
                           style="width: 80px;">
                </td>
                <td>${formatNumber(productPrice)}</td>
                <td>
                    <input type="number"
                           name="discounts[${productId}]"
                           class="form-control discount-input"
                           value="${discount}"
                           step="0.01"
                           min="0"
                           max="100"
                           style="width: 80px;">
                </td>
                <td class="gst-details-cell">
                    ${gstDetailsHtml || '<span class="text-muted">No GST</span>'}
                </td>
                <td>
                    <span class="total-amount">${formatNumber(productPrice)}</span>
                </td>
                <td>
                    <a href="javascript:void(0);" class="delete-set">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="svg">
                    </a>
                </td>
            </tr>
        `;

                            $('#no-products-row').remove();
                            $('tbody').append(newRow);

                            // Add event listeners
                            $('tbody tr[data-product-id="' + productId + '"] .quantity-input').on('input', calculateAllTotals);
                            $('tbody tr[data-product-id="' + productId + '"] .discount-input').on('input', calculateAllTotals);
                            $('tbody tr[data-product-id="' + productId + '"] .delete-set').on('click', function() {
                                $(this).closest('tr').remove();
                                updateProductSelection();
                                toggleNoProductsMessage();
                                calculateAllTotals();
                            });
                        }

                        // Update product selection in dropdown
                        function updateProductSelection() {
                            const selectedProducts = $('tbody tr[data-product-id]').map(function() {
                                return $(this).data('product-id').toString();
                            }).get();
                            $('.product-select').val(selectedProducts).trigger('change');
                        }

                        // Toggle "no products" message
                        function toggleNoProductsMessage() {
                            if ($('tbody tr[data-product-id]').length === 0) {
                                $('tbody').append(
                                    '<tr id="no-products-row"><td colspan="7" class="text-center">No products selected</td></tr>'
                                );
                            }
                        }

                        // Calculate all totals including product-wise GST
                        function calculateAllTotals() {
                            let grossSubtotal = 0;
                            let totalPerItemDiscount = 0;
                            let netSubtotal = 0;
                            let totalGst = 0;
                            const gstOption = $('input[name="gst_option"]:checked').val();
                            const hasGlobalGst = gstOption === 'with_gst';

                            // Calculate for each product
                            $('tbody tr[data-product-id]').each(function() {
                                const $row = $(this);
                                const quantity = parseFloat($row.find('.quantity-input').val()) || 0;
                                const price = parseFloat($row.find('.quantity-input').data('price')) || 0;
                                const discountPercent = parseFloat($row.find('.discount-input').val()) || 0;
                                const productGstOption = $row.data('gst-option');
                                let productGstData = $row.data('product-gst') || [];

                                // If productGstData is a string, parse it
                                if (typeof productGstData === 'string' && productGstData.trim() !== '') {
                                    try {
                                        productGstData = JSON.parse(productGstData);
                                    } catch (e) {
                                        // console.error('Error parsing GST data', e);
                                        productGstData = [];
                                    }
                                }

                                if (!Array.isArray(productGstData)) {
                                    productGstData = [];
                                }

                                // Base product total (Gross)
                                const baseProductTotal = quantity * price;
                                grossSubtotal += baseProductTotal;

                                // 1. Calculate product GST if applicable (on FULL base price)
                                let productGstAmount = 0;
                                if (hasGlobalGst && productGstOption === 'with_gst' && Array.isArray(productGstData) &&
                                    productGstData.length > 0) {
                                    productGstData.forEach(tax => {
                                        const taxRate = parseFloat(tax.tax_rate || 0) / 100;
                                        productGstAmount += baseProductTotal * taxRate;
                                    });
                                    totalGst += productGstAmount;

                                    // Update GST details display
                                    const $gstCell = $row.find('.gst-details-cell');
                                    let gstHtml = '<div class="product-gst-details">';
                                    productGstData.forEach(tax => {
                                        const taxRate = parseFloat(tax.tax_rate || 0);
                                        const taxAmount = baseProductTotal * (taxRate / 100);
                                        gstHtml +=
                                            `<small>${tax.tax_name || 'GST'}: ${taxRate}% (${formatNumber(taxAmount)})</small>`;
                                    });
                                    gstHtml +=
                                        `<small style="font-weight: bold;">Total GST: ${formatNumber(productGstAmount)}</small>`;
                                    gstHtml += '</div>';
                                    $gstCell.html(gstHtml);
                                } else {
                                    // Update GST details display for non-GST products
                                    const $gstCell = $row.find('.gst-details-cell');
                                    if (hasGlobalGst && productGstOption === 'with_gst') {
                                        $gstCell.html('<span class="text-muted">With GST (0%)</span>');
                                    } else {
                                        $gstCell.html('<span class="text-muted">No GST</span>');
                                    }
                                }

                                const totalWithGst = baseProductTotal + productGstAmount;

                                // 2. Apply per-item discount on (Base + GST)
                                const itemDiscountAmount = totalWithGst * (discountPercent / 100);
                                totalPerItemDiscount += itemDiscountAmount;

                                const rowFinalTotal = totalWithGst - itemDiscountAmount;
                                netSubtotal += rowFinalTotal;

                                // Update row total display (all parts)
                                let rowTotalHtml = `
                                    <div style="color:#ff9f43;">
                                        <strong>Sub Total:</strong> ${formatCurrency(baseProductTotal)}
                                    </div>`;

                                if (productGstOption === 'with_gst') {
                                    rowTotalHtml += `
                                        <div style="color:#007bff;">
                                            <strong>GST Included:</strong> ${formatCurrency(totalWithGst)}
                                        </div>`;
                                }

                                if (itemDiscountAmount > 0) {
                                    rowTotalHtml += `
                                        <div style="color:red;">
                                            <strong>Discount:</strong> -${formatCurrency(itemDiscountAmount)}
                                        </div>`;
                                }

                                rowTotalHtml += `
                                    <div style="font-weight:bold; margin-top:4px; border-top:1px solid #ddd; padding-top:3px;color:green;">
                                        Final Total: ${formatCurrency(rowFinalTotal)}
                                    </div>`;

                                $row.find('.total-amount').html(rowTotalHtml);
                            });

                            // Update summary display
                            $('#subtotal-display').text(formatNumber(grossSubtotal));

                            // Total Discounts
                            $('#product-discount-total-display').text(formatNumber(totalPerItemDiscount));

                            // After discount (Total with GST - Discount)
                            const afterDiscount = (grossSubtotal + totalGst) - totalPerItemDiscount;
                            $('#after-discount-display').text(formatNumber(afterDiscount));

                            // Calculate labour cost
                            let labourSubtotal = 0;
                            $('.labour-item-row').each(function() {
                                const qty = parseFloat($(this).find('.labour-qty').val()) || 0;
                                const price = parseFloat($(this).find('.labour-price').val()) || 0;
                                labourSubtotal += qty * price;
                            });
                            $('#labour-cost-display').text(formatNumber(labourSubtotal));

                            // Shipping cost
                            const shippingCost = parseFloat($('#shipping-input').val()) || 0;
                            $('#shipping-cost-display').text(formatNumber(shippingCost));

                            // Show/hide GST total
                            const $gstTotalLi = $('.total-gst');
                            if (hasGlobalGst && totalGst > 0) {
                                $gstTotalLi.show();
                                $('#total-gst-amount').text(formatNumber(totalGst));
                            } else {
                                $gstTotalLi.hide();
                            }

                            // Grand Total (After Discount + Labour + Shipping)
                            const grandTotal = afterDiscount + labourSubtotal + shippingCost;
                            $('#grand-total').text(formatNumber(grandTotal));
                        }

                        // Bind events for recalculation
                        $(document).on('input', '.quantity-input, .discount-input, #shipping-input, .labour-qty, .labour-price', calculateAllTotals);
                        $(document).on('change', 'input[name="gst_option"], .select2-labour', calculateAllTotals);

                        $(document).on('change', '.select2-labour', function() {
                            const price = $(this).find(':selected').data('price') || 0;
                            $(this).closest('.labour-item-row').find('.labour-price').val(price);
                            calculateAllTotals();
                        });

                        // Labour items dynamic row handling
                        $(document).on('click', '.add-labour-item', function() {
                            const newRow = `
                                <div class="row mb-2 labour-item-row">
                                    <div class="col-lg-5 col-sm-4 col-4">
                                        <select name="labour_item_id[]" class="form-control select2-labour-new">
                                            <option value="">Select Labour Item</option>
                                            @foreach ($labourItems as $lItem)
                                                <option value="{{ $lItem->id }}" data-price="{{ $lItem->price }}">{{ $lItem->item_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3">
                                        <input type="number" name="labour_qty[]" class="form-control labour-qty" placeholder="Qty" value="1" min="0">
                                    </div>
                                    <div class="col-lg-3 col-sm-3 col-3">
                                        <input type="number" name="labour_price[]" class="form-control labour-price" placeholder="Price" value="0" min="0">
                                    </div>
                                    <div class="col-lg-1 col-sm-2 col-2">
                                        <button type="button" class="btn btn-success add-labour-item">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            `;

                            // Change current plus button to minus
                            $(this).removeClass('btn-success add-labour-item').addClass('btn-danger remove-labour-item')
                                .html('<i class="fas fa-times"></i>');

                            $('#labour-items-container').append(newRow);

                            // Initialize select2 for the new row
                            $('.select2-labour-new').select2({
                                width: '100%'
                            }).removeClass('select2-labour-new').addClass('select2-labour');
                        });

                        $(document).on('click', '.remove-labour-item', function() {
                            $(this).closest('.labour-item-row').remove();
                            calculateAllTotals();
                        });

                        // Handle Quotation Toggle
                        function togglePaymentFields() {
                            if ($('#quotationToggle').is(':checked')) {
                                $('#payment_method_col').hide();
                                $('#payment_status_col').show();
                            } else {
                                $('#payment_method_col').show();
                                $('#payment_status_col').show();
                            }
                        }

                        $('#quotationToggle').on('change', togglePaymentFields);
                        togglePaymentFields(); // Initial call

                        // Handle product selection changes
                        $('.product-select').on('change', function() {
                            const selectedProducts = $(this).val() || [];
                            const existingProducts = $('tbody tr[data-product-id]').map(function() {
                                return $(this).data('product-id').toString();
                            }).get();

                            // Add new products
                            selectedProducts.forEach(productId => {
                                if (!existingProducts.includes(productId)) {
                                    const option = $(this).find('option[value="' + productId + '"]');
                                    addProductToTable(
                                        productId,
                                        option.data('name'),
                                        option.data('image'),
                                        option.data('price'),
                                        option.data('gst-option'),
                                        option.data('product-gst'),
                                        null,
                                        option.data('unit'),
                                        option.data('discount')
                                    );
                                }
                            });

                            // Remove unselected products
                            $('tbody tr[data-product-id]').each(function() {
                                const rowProductId = $(this).data('product-id').toString();
                                if (!selectedProducts.includes(rowProductId)) {
                                    $(this).remove();
                                }
                            });

                            toggleNoProductsMessage();
                            calculateAllTotals();
                        });

                        // Initialize existing rows with their GST details from order_items
                        $('tbody tr[data-product-id]').each(function() {
                            const $row = $(this);
                            const productId = $row.data('product-id');

                            // Get GST details from the existing data attributes
                            let productGstData = $row.data('product-gst');

                            if (typeof productGstData === 'string' && productGstData.trim() !== '') {
                                try {
                                    const parsedGst = JSON.parse(productGstData);
                                    $row.data('product-gst', parsedGst);
                                } catch (e) {
                                    // console.error('Error parsing GST data:', e);
                                    $row.data('product-gst', []);
                                }
                            } else if (!productGstData) {
                                $row.data('product-gst', []);
                            }

                            // Add event listeners for existing rows
                            $row.find('.quantity-input').on('input', calculateAllTotals);
                            $row.find('.discount-input').on('input', calculateAllTotals);
                            $row.find('.delete-set').on('click', function() {
                                $(this).closest('tr').remove();
                                updateProductSelection();
                                toggleNoProductsMessage();
                                calculateAllTotals();
                            });
                        });



                        // Form submission handler
                        $(document).on("click", '.btn-submit', function(e) {
                            var authToken = localStorage.getItem("authToken");
                            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                            e.preventDefault();

                            // Collect all form data
                            const formData = {
                                update_id: $('#update_selse_id').val(),
                                customer_id: $('#customer_id').val(),
                                customer_phone: $('#customer_phone').val(),
                                product_ids: $('.product-select').val(),
                                quantities: {},
                                discounts: {},
                                discount: 0,
                                grand_total: $('#grand-total').text().replace(/[^0-9.]/g, ''),
                                gst_option: $('input[name="gst_option"]:checked').val(),
                                selectedSubAdminId: selectedSubAdminId || null,
                                remarks: $('#remarks').val(),
                                status: $('#payment_status').val(),
                                shipping: $('#shipping-input').val(),
                                quotation_status: $('#quotationToggle').is(':checked') ? 'quotation' : 'sales',
                                labour_item_ids: [],
                                labour_qtys: [],
                                labour_prices: []
                            };

                            // Collect labour items
                            $('.labour-item-row').each(function() {
                                const itemId = $(this).find('select[name="labour_item_id[]"]').val();
                                if (itemId) {
                                    formData.labour_item_ids.push(itemId);
                                    formData.labour_qtys.push($(this).find('.labour-qty').val());
                                    formData.labour_prices.push($(this).find('.labour-price').val());
                                }
                            });

                            // Collect quantities
                            $('.quantity-input').each(function() {
                                const productId = $(this).closest('tr').data('product-id');
                                formData.quantities[productId] = $(this).val();
                            });

                            // Collect discounts
                            $('.discount-input').each(function() {
                                const productId = $(this).closest('tr').data('product-id');
                                formData.discounts[productId] = $(this).val();
                            });

                            // Validate
                            if (!formData.customer_id) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please select a customer",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            if (!formData.product_ids || formData.product_ids.length === 0) {
                                Swal.fire({
                                    title: "Error",
                                    text: "Please select at least one product",
                                    icon: "error",
                                    confirmButtonText: "OK",
                                    confirmButtonColor: "#ff9f43"
                                });
                                return;
                            }

                            // Show loader and disable button
                            const $btn = $('#update-order-btn');
                            const $loader = $('#btn-loader');
                            const $btnText = $('#btn-text');

                            $btn.prop('disabled', true);
                            $loader.removeClass('d-none');
                            $btnText.text('Updating...');

                            // Send AJAX request to update order
                            $.ajax({
                                url: `/api/update_sale`,
                                type: "POST",
                                headers: {
                                    "Authorization": "Bearer " + authToken,
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                },
                                data: formData,
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: "Success!",
                                            text: "Order updated successfully!",
                                            icon: "success",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                window.location.href = "{{ route('sales.list') }}";
                                            }
                                        });
                                    } else {
                                        // Reset button on failure
                                        $btn.prop('disabled', false);
                                        $loader.addClass('d-none');
                                        $btnText.text('Update Order');

                                        Swal.fire({
                                            title: "Error",
                                            text: response.message,
                                            icon: "error",
                                            confirmButtonText: "OK",
                                            confirmButtonColor: "#ff9f43"
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    // Reset button on error
                                    $btn.prop('disabled', false);
                                    $loader.addClass('d-none');
                                    $btnText.text('Update Order');

                                    let message = 'An error occurred while updating the order';
                                    try {
                                        const res = xhr.responseJSON;
                                        if (res.message) {
                                            message = res.message;
                                        } else if (res.errors) {
                                            message = Object.values(res.errors).join('<br>');
                                        }
                                    } catch (e) {
                                        // console.error('Failed to parse error message:', e);
                                    }

                                    Swal.fire({
                                        title: "Error",
                                        html: message,
                                        icon: "error",
                                        confirmButtonText: "OK",
                                        confirmButtonColor: "#ff9f43"
                                    });
                                }
                            });
                        });

                        // Initial calculation
                        calculateAllTotals();

                        // Initialize customer phone on page load
                        const selectedCustomer = $('#customer_id').find(':selected');
                        if (selectedCustomer.length) {
                            $('#customer_phone').val(selectedCustomer.data('phone') || '');
                        }
                    });
                </script>

            @endpush
