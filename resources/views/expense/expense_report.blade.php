@extends('layout.app')

@section('title', 'Expense Report')

@section('content')
<style>
    .purchase_report_table tr td {
        vertical-align: middle !important;
    }

    @media screen and (max-width: 768px) {
        .page-header {
            flex-direction: row;
        }
        .page-header .page-btn {
            margin-top: 0 !important;
        }
        .card-sales-split ul {
            margin-left: 14rem;
        }
        .purchase_report_head img {
            max-width: 100px !important;
        }
        .purchase_report_head h2 {
            font-size: 17px !important;
        }
        .purchase_report_table1 tr td:first-child {
            font-size: 11px !important;
        }
        .purchase_report_table1 tr {
            display: flex;
            justify-content: space-between;
        }
        .purchase_report_table1 tr td:last-child {
            padding: 10px 5px !important;
            font-size: 11px !important;
        }
        .invoice-box {
            overflow-x: auto;
        }

        .heading td:first-child {
            padding: 10px 40px !important;

        }
    }
</style>
    <div class="content">
        
        <div class="page-header">
            <div class="page-title">
                <h4>Expense Report</h4>

            </div>
            <div class="page-btn d-flex gap-2">
                <a href="{{ route('expense.report') }}" class="btn btn-added">
                    Back
                </a>
                @if (!empty($ids))
                    <a href="{{ url('/expense/report/' . $ids . '/export-pdf') }}" class="btn btn-danger">
                        Export PDF
                    </a>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <input type="hidden" name="expense_id" id="expense_id" value="">
        
                <tr class="top">
                    <td colspan="2">
                        <div class="row pdf-header purchase_report_head">
                            <div class="col-6">
                                <img src="{{ $settings->logo ? asset('/public/storage/' . $settings->logo) : asset('/public/admin/assets/img/logso.png') }}"
                                    style="max-width: 150px;">
                            </div>
                            <div class="col-6 mt-4" style="text-align: end;">
                                <h2>Expense Report</h2>
                            </div>
                        </div>
                        <hr>
                    </td>
                </tr>
                <div class="download_pdf">
                    <div class="invoice-box table-height"
                        style="max-width: 1600px; width:100%; margin:15px auto; padding: 0; font-size: 14px; line-height: 24px; color: #555;">
                        <!-- First Table: Company Info & Report Info -->
                        <table style="width: 100%; line-height: inherit; text-align: left;" class="purchase_report_table1">

                            <tr>
                                <td style="padding: 10px; vertical-align: top;">
                                    <strong style="font-size:14px; color:#7367F0; font-weight:600;">Company
                                        Info</strong><br>
                                    {{ $settings->name ?? 'Company Name' }}<br>
                                    {{ $settings->email ?? 'N/A' }}<br>
                                    {{ $settings->phone ?? 'N/A' }}<br>
                                    {{ $settings->address ?? 'N/A' }}<br>
                                    GST: {{ $settings->gst_num ?? 'N/A' }}
                                </td>

                                <td style="padding: 10px; text-align: right; vertical-align: top;">
                                    <strong style="font-size:14px; color:#7367F0; font-weight:600;">Report Info</strong><br>
                                    Total Expense: {{ $expenses->count() }}<br> 
                                    Report Date: {{ \Carbon\Carbon::now()->format('d M Y') }}
                                </td>
                            </tr>
                        </table>

                        <br>

                        <!-- Second Table: Expense Details -->
                        <table style="width: 100%; line-height: inherit; text-align: left; border-collapse: collapse;">
                            <tr class="heading" style="background: #F3F2F7;">
                                <td style="padding: 10px;"><strong>Expense Name</strong></td>
                                <td style="padding: 10px;"><strong>Amount</strong></td>
                                <td style="padding: 10px;"><strong>Date</strong></td>
                                <td style="padding: 10px;"><strong>Expense For</strong></td>
                            </tr>

                            @foreach($expenses as $expense)
                                <tr class="details" style="border-bottom: 1px solid #E9ECEF;">
                                    <td style="padding: 10px;">{{ $expense->expense_name }}</td>
                                    <td style="padding: 10px;">
                                        {{ $currencyPosition === 'left' ? $currencySymbol . number_format($expense->amount, 2) : number_format($expense->amount, 2) . $currencySymbol }}
                                    </td>

                                    <td style="padding: 10px;">
                                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                                    <td style="padding: 10px; white-space: normal; word-break: break-word; max-width: 400px;">
                                        {{ $expense->description ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </table>



                        <div class="row mt-3">
                            <div class="col-lg-6"></div>
                            <div class="col-lg-6">
                                <div class="total-order w-100 max-widthauto m-auto mb-4">
                                    <ul>
                                        <li>
                                            <h4>Total Amount</h4>
                                            <h5>
                                                {{ $currencyPosition === 'left' ? $currencySymbol . number_format($expenses->sum('amount'), 2) : number_format($expenses->sum('amount'), 2) . $currencySymbol }}
                                            </h5>
                                        </li>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
    // Both URLs are rendered by Blade
    const customLogoUrl = "{{ $settings->logo ? asset('/storage/' . $settings->logo) : '' }}";
    const defaultLogoUrl = "{{ asset('/admin/assets/img/logso.png') }}";

    // Try to load the custom logo, fallback to default if it fails
    loadImageAsBase64WithFallback(customLogoUrl, defaultLogoUrl, function (logoBase64, logoWidth, logoHeight) {
        const element = document.querySelector('.download_pdf');
        const opt = {
            margin: [40, 10, 30, 10],
            filename: 'Expense Report.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { avoid: 'tr' }
        };

        html2pdf().set(opt).from(element).toPdf().get('pdf').then(function (pdf) {
            const pageCount = pdf.internal.getNumberOfPages();
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            for (let i = 1; i <= pageCount; i++) {
                pdf.setPage(i);
                const logoTop = 10;
                const logoBottomMargin = 5;
                const headerEndY = logoTop + logoHeight + logoBottomMargin;

                // === HEADER START ===
                if (logoBase64) {
                    pdf.addImage(logoBase64, 'JPEG', 10, logoTop, logoWidth, logoHeight);
                }
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(100);
                pdf.text("Expense Report", pageWidth - 10, logoTop + (logoHeight / 2), { align: 'right' });
                pdf.setDrawColor(200);
                pdf.line(10, headerEndY, pageWidth - 10, headerEndY);
                // === HEADER END ===

                // === FOOTER START ===
                pdf.setFontSize(9);
                pdf.setFont('helvetica', 'normal');
                pdf.setTextColor(150);
                pdf.line(10, pageHeight - 15, pageWidth - 10, pageHeight - 15);
                pdf.text(`© {{ now()->year }} {{ $settings->name ?? 'Company Name' }} - All rights reserved.`, 10, pageHeight - 10);
                pdf.text(`Page ${i} of ${pageCount}`, pageWidth - 10, pageHeight - 10, { align: 'right' });
                // === FOOTER END ===
            }
        }).save();
    });
}

// Helper: Try to load the custom logo, fallback to default if it fails
function loadImageAsBase64WithFallback(primaryUrl, fallbackUrl, callback) {
    loadImageAsBase64(primaryUrl, function (base64, width, height, success) {
        if (success) {
            callback(base64, width, height);
        } else {
            loadImageAsBase64(fallbackUrl, function (base642, width2, height2, success2) {
                if (success2) {
                    callback(base642, width2, height2);
                } else {
                    alert("Logo could not be loaded.");
                    callback('', 0, 0);
                }
            });
        }
    });
}

// Helper: Load image as base64, report success/failure
function loadImageAsBase64(url, callback) {
    if (!url) {
        callback('', 0, 0, false);
        return;
    }
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function () {
        const canvas = document.createElement('canvas');
        canvas.width = img.width;
        canvas.height = img.height;
        const ctx = canvas.getContext('2d');
        ctx.fillStyle = "#FFFFFF";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.drawImage(img, 0, 0);
        const base64 = canvas.toDataURL('image/jpeg');
        const desiredWidth = 30;
        const ratio = img.height / img.width;
        const desiredHeight = desiredWidth * ratio;
        callback(base64, desiredWidth, desiredHeight, true);
    };
    img.onerror = function () {
        callback('', 0, 0, false);
    };
    img.src = url;
}
    </script>
@endpush