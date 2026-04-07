@extends('layout.app')
@section('title', 'Purchase Report')

@section('content')
<style>
    .purchase_report_table tr td {
        vertical-align: middle !important;
    }
</style>
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Purchase Report</h4>
            <h6>View Purchase Report</h6>
        </div>
        <div class="page-btn">
            <a href="{{ route('purchase.report') }}" class="btn btn-added">
                Back
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="card-sales-split">
                <h2>Purchase Report</h2>
                <ul>
                    <li>
                        <a href="javascript:void(0);" onclick="downloadPDF()" title="Download Invoice"><img
                                src="{{ asset('admin/assets/img/icons/pdf.svg')}}" alt="img"></a>
                    </li>
                </ul>
            </div>
            <tr class="">
                <td colspan="6">
                    <div class="row">
                        <div class="col-6">
                            <img src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('/admin/assets/img/logso.png') }}"
                                style="max-width: 150px;">
                        </div>
                        <div class="col-6 mt-4" style="text-align: end;">
                            <h2>Purchase Report</h2>
                        </div>
                    </div>
                    <hr>
                </td>
            </tr>
            <div class="download_pdf">
                <div class="invoice-box table-height"
                    style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                    <table style="width: 100%; line-height: inherit; text-align: left;">
                        <tr>
                            <td colspan="6">
                                <table style="width: 100%;">
                                    <tr>
                                        <td style="padding: 10px; float: left;">
                                            <strong
                                                style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">Company
                                                Info</strong><br>
                                            {{ $settings->name ?? 'Company Name' }}<br>
                                            {{ $settings->email ?? 'N/A' }}<br>
                                            {{ $settings->phone ?? 'N/A' }}<br>
                                            {{ $settings->address ?? 'N/A' }}
                                        </td>

                                        <td style="padding: 10px; float: right;">
                                            <strong
                                                style="font-size:14px; color:#7367F0; font-weight:600; line-height: 35px;">Report
                                                Info</strong><br>
                                            Total Purchases: {{ count($purchases) }}<br>
                                            Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr class="heading" style="background: #F3F2F7;">
                            <td style="padding: 10px;"><strong>Product</strong></td>
                            <td style="padding: 10px;"><strong>Category</strong></td>
                            <td style="padding: 10px;"><strong>Unit Price</strong></td>
                            <td style="padding: 10px;"><strong>Qty</strong></td>
                            <td style="padding: 10px;"><strong>Total</strong></td>
                        </tr>

                        @php
                        $subtotal = 0;
                        @endphp

                        @foreach($purchases as $purchase)
                        @php
                        $total = $purchase->amount_total;
                        $subtotal += $total;
                        $unitPrice = $purchase->quantity ? $purchase->amount_total / $purchase->quantity : 0;

                        $images = json_decode($purchase->product->images, true);
                        $imagePath = isset($images[0]) && file_exists(public_path('storage/' . $images[0]))
                        ? asset('storage/' . $images[0])
                        : asset('admin/assets/img/product/noimage.png');

                        // Currency formatter
                        if (!function_exists('formatCurrency')) {
                        function formatCurrency($amount, $symbol = '₹', $position = 'left')
                        {
                        $formatted = number_format($amount, 2);
                        return $position === 'left' ? $symbol . $formatted : $formatted . $symbol;
                        }
                        }
                        @endphp

                        <tr class="details" style="border-bottom: 1px solid #E9ECEF;">

                            <td style="padding: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <img src="{{ $imagePath }}" alt="Product Image" style="width: 65px; height: auto;">
                                    <div style="font-weight: 500;">{{ $purchase->product->name ?? '-' }}</div>
                                </div>
                            </td>

                            <td style="padding: 10px;">{{ $purchase->product->category->name ?? 'N/A' }}</td>
                            <td style="padding: 10px;">
                                {{ formatCurrency($unitPrice, $currencySymbol, $currencyPosition) }}
                            </td>
                            <td style="padding: 10px;">{{ $purchase->quantity }}</td>
                            <td style="padding: 10px;">
                                {{ formatCurrency($total, $currencySymbol, $currencyPosition) }}
                            </td>
                        </tr>
                        @endforeach

                    </table>

                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <!-- You can add extra content here if needed -->
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="total-order w-100 max-widthauto m-auto mb-4">
                                <ul>
                                    <li>
                                        <h4>Subtotal</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                            {{ $currencySymbol }}{{ number_format($subtotal, 2) }}
                                            @else
                                            {{ number_format($subtotal, 2) }}{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    @foreach($taxDetails as $tax)
                                    <li>
                                        <h4>{{ $tax['name'] }} Tax</h4>
                                        <h5>
                                            {{ $tax['rate'] }}%
                                            (
                                            @if($currencyPosition == 'left')
                                            {{ $currencySymbol }}{{ number_format($tax['amount'], 2) }}
                                            @else
                                            {{ number_format($tax['amount'], 2) }}{{ $currencySymbol }}
                                            @endif
                                            )
                                        </h5>
                                    </li>
                                    @endforeach

                                    <li>
                                        <h4>Shipping</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                            {{ $currencySymbol }}{{ number_format($shipping, 2) }}
                                            @else
                                            {{ number_format($shipping, 2) }}{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>

                                    <li class="total">
                                        <h4>Total Amount</h4>
                                        <h5>
                                            @if($currencyPosition == 'left')
                                            {{ $currencySymbol }}{{ number_format($totalAmount, 2) }}
                                            @else
                                            {{ number_format($totalAmount, 2) }}{{ $currencySymbol }}
                                            @endif
                                        </h5>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Move this outside the invoice content -->
            </div>

        </div>
    </div>
</div>
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadPDF() {
        const element = document.querySelector('.download_pdf');
        const logoUrl = "{{ $settings->logo ? asset('/storage/' . $settings->logo) : asset('/admin/assets/img/logso.png') }}";

        loadImageAsBase64(logoUrl, function(logoBase64, logoWidth, logoHeight) {
            const opt = {
                margin: [40, 10, 30, 10],
                filename: 'Purchase_Report.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'mm',
                    format: 'a4',
                    orientation: 'portrait'
                },
                pagebreak: {
                    avoid: 'tr'
                }
            };

            html2pdf().set(opt).from(element).toPdf().get('pdf').then(function(pdf) {
                const pageCount = pdf.internal.getNumberOfPages();
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();

                for (let i = 1; i <= pageCount; i++) {
                    pdf.setPage(i);

                    const logoTop = 10;
                    const logoBottomMargin = 5; // Add margin below logo
                    const headerEndY = logoTop + logoHeight + logoBottomMargin;

                    // === HEADER START ===
                    // Logo
                    pdf.addImage(logoBase64, 'JPEG', 10, logoTop, logoWidth, logoHeight);

                    // Report Title (align with bottom of logo + margin)
                    pdf.setFontSize(14);
                    pdf.setFont('helvetica', 'bold');
                    pdf.setTextColor(100);
                    pdf.text("Purchase Report", pageWidth - 10, logoTop + (logoHeight / 2), {
                        align: 'right'
                    });

                    // Header Line below logo
                    pdf.setDrawColor(200);
                    pdf.line(10, headerEndY, pageWidth - 10, headerEndY);
                    // === HEADER END ===


                    // === FOOTER START ===
                    pdf.setFontSize(9);
                    pdf.setFont('helvetica', 'normal');
                    pdf.setTextColor(150);
                    pdf.line(10, pageHeight - 15, pageWidth - 10, pageHeight - 15);
                    pdf.text(`© {{ now()->year }} {{ $settings->name ?? 'Company Name' }} - All rights reserved.`, 10, pageHeight - 10);
                    pdf.text(`Page ${i} of ${pageCount}`, pageWidth - 10, pageHeight - 10, {
                        align: 'right'
                    });
                    // === FOOTER END ===
                }
            }).save();
        });
    }

    // ✅ Image to base64 with aspect ratio preserved
    function loadImageAsBase64(url, callback) {
        const img = new Image();
        img.crossOrigin = 'anonymous'; // Fix for CORS issues on live

        img.onload = function() {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;

            const ctx = canvas.getContext('2d');

            // ✅ Fill white background before drawing image (fixes black bg)
            ctx.fillStyle = "#FFFFFF";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            ctx.drawImage(img, 0, 0);

            const base64 = canvas.toDataURL('image/jpeg'); // JPEG used in pdf.addImage
            const desiredWidth = 30; // Width in mm
            const ratio = img.height / img.width;
            const desiredHeight = desiredWidth * ratio;

            callback(base64, desiredWidth, desiredHeight);
        };

        img.onerror = function() {
            alert("Logo could not be loaded.");
            callback('', 0, 0);
        };

        img.src = url;
    }
</script>


@endpush