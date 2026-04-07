@extends('layout.app')

@section('title', 'Income Statement')

@section('content')
    <style>
        .download-loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1060;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .download-loader-overlay.d-none {
            display: none !important;
        }

        .download-loader-box {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 8px;
            padding: 24px 20px;
            text-align: center;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.2);
        }

        .download-loader-box h4 {
            margin: 0 0 18px 0;
            font-size: 34px;
            color: #2c3e50;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .download-loader-box h4 {
                font-size: 28px;
            }
        }
    </style>

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Income Statement</h3>
                </div>

            </div>
        </div>

        <!-- Search Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('income-statement.index') }}" method="GET">
                    <div class="row g-3 align-items-end">

                        <!-- Start Date -->
                        <div class="col-6 col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>

                        <!-- End Date -->
                        <div class="col-6 col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control"
                                value="{{ request('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d')) }}">
                        </div>

                        <!-- Filter -->
                        <div class="col-12 col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>

                        <!-- 🔹 MOBILE LINE BREAK -->
                        <div class="w-100 d-md-none"></div>

                        <!-- PDF -->
                        <div class="col-6 col-md-2 d-grid">
                            <a href="{{ route('income-statement.pdf', request()->only(['start_date', 'end_date'])) }}"
                                class="btn btn-danger w-100 income-download-btn" data-download-type="pdf">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>

                        <!-- Excel -->
                        <div class="col-6 col-md-2 d-grid">
                            <a href="{{ route('income-statement.excel', request()->only(['start_date', 'end_date'])) }}"
                                class="btn btn-success w-100 income-download-btn" data-download-type="excel">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>



        <!-- Income Statement -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table">
                                <thead>
                                    <tr>
                                        <th class="text-left" colspan="2">
                                            For the Period:
                                            {{ \Carbon\Carbon::parse($data['period']['start'])->format('M d, Y') }}
                                            to {{ \Carbon\Carbon::parse($data['period']['end'])->format('M d, Y') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Revenue Section -->
                                    <tr class="table" style="background: #ff9f43 !important; color:white;">
                                        <td colspan="2"><strong style="color:white;">Revenue</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Sales in Cash</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales_cash'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales_cash'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sales in Online</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales_online'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales_online'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Sales Revenue</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['sales'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['sales'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Revenue</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['revenue']['total_revenue'], 2) }}
                                            @else
                                                {{ number_format($data['revenue']['total_revenue'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Cost of Goods Sold -->
                                    <tr class="table-info">
                                        <td colspan="2"><strong>Cost of Goods Sold</strong></td>
                                    </tr>
                                    <tr>
                                        <td>Purchases in Cash</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchase_cash'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Purchases in Online</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchase_online'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Purchases</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['purchases'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Cost of Goods Sold</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['cost_of_goods_sold']['total_cogs'], 2) }}
                                            @else
                                                {{ number_format($data['cost_of_goods_sold']['total_cogs'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Gross Profit -->
                                    <tr class="table-success font-weight-bold">
                                        <td>Gross Profit</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['gross_profit'], 2) }}
                                            @else
                                                {{ number_format($data['gross_profit'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Operating Expenses -->
                                    <tr class="table-info">
                                        <td colspan="2"><strong>Operating Expenses</strong></td>
                                    </tr>
                                    <tr>
                                        <td>General Expenses</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_expenses']['general_expenses'], 2) }}
                                            @else
                                                {{ number_format($data['operating_expenses']['general_expenses'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr class="font-weight-bold">
                                        <td>Total Operating Expenses</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_expenses']['total_operating_expenses'], 2) }}
                                            @else
                                                {{ number_format($data['operating_expenses']['total_operating_expenses'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Operating Income -->
                                    <tr class="table-primary font-weight-bold">
                                        <td>Operating Income (EBIT)</td>
                                        <td class="text-right">
                                            @if ($data['currency']['position'] === 'left')
                                                {{ $data['currency']['symbol'] }}{{ number_format($data['operating_income'], 2) }}
                                            @else
                                                {{ number_format($data['operating_income'], 2) }}{{ $data['currency']['symbol'] }}
                                            @endif
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="downloadLoaderOverlay" class="download-loader-overlay d-none" aria-live="polite" aria-busy="true">
        <div class="download-loader-box">
            <h4 id="downloadLoaderText">Generating PDF...</h4>
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
        </div>
    </div>
@endsection
@push('js')
    {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
    <script>
        $(document).ready(function() {
            const authToken = localStorage.getItem("authToken");
            var selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // console.log(selectedSubAdminId);

            // Dynamically get start and end of the current month
            let now = new Date();
            let startDate = new Date(now.getFullYear(), now.getMonth(), 1); // first day of month
            let endDate = new Date(now.getFullYear(), now.getMonth() + 1, 0); // last day of month

            // Format dates as YYYY-MM-DD
            const formatDate = (date) => {
                let month = (date.getMonth() + 1).toString().padStart(2, '0');
                let day = date.getDate().toString().padStart(2, '0');
                return `${date.getFullYear()}-${month}-${day}`;
            };

            startDate = formatDate(startDate);
            endDate = formatDate(endDate);

            // Make the API call immediately
            $.ajax({
                url: '/api/income-statement', // Your API route
                method: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    selectedSubAdminId: selectedSubAdminId,
                },
                headers: {
                    'Authorization': 'Bearer ' + authToken // if using auth
                },
                success: function(response) {
                    if (response.status) {
                        // console.log('Income Statement Data:', response.data);
                    } else {
                        console.error('Failed: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error: ' + xhr.responseText);
                }
            });

            const $downloadLoader = $("#downloadLoaderOverlay");
            const $downloadLoaderText = $("#downloadLoaderText");
            const $downloadButtons = $(".income-download-btn");

            function toggleDownloadLoader(isLoading, message) {
                if (isLoading) {
                    $downloadLoaderText.text(message || "Generating report...");
                    $downloadLoader.removeClass("d-none");
                    $downloadButtons.addClass("disabled").attr("aria-disabled", "true");
                } else {
                    $downloadLoader.addClass("d-none");
                    $downloadButtons.removeClass("disabled").removeAttr("aria-disabled");
                }
            }

            function getFileNameFromDisposition(contentDisposition, fallbackName) {
                if (!contentDisposition) return fallbackName;

                const utf8Match = contentDisposition.match(/filename\\*=UTF-8''([^;]+)/i);
                if (utf8Match && utf8Match[1]) {
                    return decodeURIComponent(utf8Match[1]);
                }

                const asciiMatch = contentDisposition.match(/filename=\"?([^\";]+)\"?/i);
                if (asciiMatch && asciiMatch[1]) {
                    return asciiMatch[1];
                }

                return fallbackName;
            }

            async function downloadStatement(url, type) {
                const isPdf = type === "pdf";
                const fallbackName = isPdf ? "income-statement.pdf" : "income-statement.xlsx";
                toggleDownloadLoader(true, isPdf ? "Generating PDF..." : "Generating Excel...");

                try {
                    const response = await fetch(url, {
                        method: "GET",
                        credentials: "same-origin",
                    });

                    if (!response.ok) {
                        throw new Error("Download request failed");
                    }

                    const blob = await response.blob();
                    const contentDisposition = response.headers.get("content-disposition");
                    const fileName = getFileNameFromDisposition(contentDisposition, fallbackName);

                    const downloadUrl = window.URL.createObjectURL(blob);
                    const link = document.createElement("a");
                    link.href = downloadUrl;
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(downloadUrl);
                } catch (error) {
                    Swal.fire({
                        icon: "error",
                        title: "Download Failed",
                        text: "Unable to generate the file right now. Please try again.",
                        confirmButtonColor: "#ff9f43",
                    });
                } finally {
                    toggleDownloadLoader(false);
                }
            }

            $downloadButtons.on("click", function(e) {
                e.preventDefault();
                if ($(this).hasClass("disabled")) {
                    return;
                }
                const url = $(this).attr("href");
                const type = $(this).data("download-type");
                downloadStatement(url, type);
            });
        });
    </script>
@endpush
