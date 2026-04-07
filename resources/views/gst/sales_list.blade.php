@extends('layout.app')

@section('title', 'GST Reports')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" />
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>GST Reports</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="mb-3">
                <div class="row">
                    <div class="col-md-2 col-4">
                        <label for="from_date" class="form-label fw-bold">Start Date</label>
                        <input type="date" id="from_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-4">
                        <label for="to_date" class="form-label fw-bold">End Date</label>
                        <input type="date" id="to_date" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2 col-4">
                        <label for="year_select" class="form-label fw-bold">Year</label>
                        <select id="year_select" class="form-control form-control-sm">
                            <option value="">-- Select Year --</option>
                            @for ($i = date('Y'); $i >= 2020; $i--)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th style="width: 70px;">No</th>
                                <th>GST Reports Name</th>
                                <th class="text-end">Download PDF/Excel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Goods and Services Tax - GSTR-2B</td>
                                <td class="text-end">
                                    {{-- <a href="{{ route('gst.gstr3b.export') }}" class="btn btn-primary py-1">
                                    <i class="fa-solid fa-file-pdf"></i> Export PDF
                                    </a> --}}
                                    <!-- <a href="{{ route('gst.sales.report.gstr3b.export') }}"
                                        class="btn btn-success export-link">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a> -->

                                    <a class="btn btn-success" id="exportGSTR2B">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>

                                </td>
                            </tr>

                            <tr>
                                <td>2</td>
                                <td>Goods and Services Tax - GSTR-1</td>
                                <td class="text-end">
                                    {{-- <a class="btn btn-primary py-1">
                                            <i class="fa-solid fa-file-pdf"></i> Export PDF
                                        </a> --}}
                                    <a class="btn btn-success" id="exportExcel">
                                        <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a>



                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>GSTR-3B Summary</td>
                                <td class="text-end">
                                    <a href="#" id="exportGstr3bPdf" class="btn btn-primary py-1">
                                        <i class="fa-solid fa-file-pdf"></i> Export PDF
                                    </a>
                                    {{-- <a href="{{ route('sales.gstr3b.export') }}" class="btn btn-success">
                                    <i class="fa-solid fa-file-excel"></i> Export Excel
                                    </a> --}}
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>

            <style>
                .pdf-btn {
                    color: #ffffff !important;
                    /* Text white */
                }

                .form-select-sm {

                    width: 14% !important;

                }

                .pdf-btn i {
                    color: #ffffff !important;
                    /* Icon white */
                }

                .pdf-btn:hover {
                    background-color: #c82333 !important;
                    /* Hover color (red shade for PDF feel) */
                    color: #ffffff !important;
                    /* Text white on hover */
                }

                .pdf-btn:hover i {
                    color: #ffffff !important;
                    /* Icon white on hover */
                }

                .table tbody tr td a {
                    color: #ffffff !important;
                }
            </style>
            @endsection
            @push('js')
            <script>
                $('#exportGSTR2B').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gstr3b/export-excel', // your new GSTR-2B route
                        method: 'GET',
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        xhrFields: {
                            responseType: 'blob' // Important for binary Excel file
                        },
                        success: function(blob) {
                            if (!(blob instanceof Blob)) {
                                alert('Export failed: Invalid response.');
                                btn.prop('disabled', false).html(originalHtml);
                                return;
                            }

                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'GSTR-2B.xlsx'; // file name
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export Excel!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });


                $('#exportExcel').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gstr1/export-excel',
                        method: 'GET',
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        xhrFields: {
                            responseType: 'blob'
                        },
                        success: function(blob) {
                            if (!(blob instanceof Blob)) {
                                console.error('Expected a Blob but received:', blob);
                                alert('Export failed: Invalid response format.');
                                btn.prop('disabled', false).html(originalHtml);
                                return;
                            }
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'GSTR1_Multiple_Sheets.xlsx';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(url);
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export Excel!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('#exportGstr3bPdf').click(function(e) {
                    e.preventDefault();

                    const authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    const btn = $(this);
                    const originalHtml = btn.html();
                    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');

                    $.ajax({
                        url: '/api/gst/gstr-3b/export',
                        method: 'GET',
                        data: {
                            from_date: from_date,
                            to_date: to_date,
                            year: year,
                            selectedSubAdminId: selectedSubAdminId,
                        },
                        headers: {
                            "Authorization": "Bearer " + authToken
                        },
                        success: function(response) {
                            if (response.status && response.file_url) {
                                const a = document.createElement('a');
                                a.href = response.file_url;
                                a.download = response.file_name || 'GSTR-3B-Summary.pdf';
                                document.body.appendChild(a);
                                a.click();
                                a.remove();
                            } else {
                                alert('Export failed: ' + (response.message || 'Unknown error'));
                            }
                            btn.prop('disabled', false).html(originalHtml);
                        },
                        error: function() {
                            alert('Failed to export PDF!');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                });

                $('.export-link').click(function(e) {
                    e.preventDefault();
                    let url = $(this).attr('href');
                    const from_date = $('#from_date').val();
                    const to_date = $('#to_date').val();
                    const year = $('#year_select').val();

                    let params = [];
                    if (from_date) params.push('from_date=' + from_date);
                    if (to_date) params.push('to_date=' + to_date);
                    if (year) params.push('year=' + year);

                    if (params.length > 0) {
                        url += (url.includes('?') ? '&' : '?') + params.join('&');
                    }

                    window.location.href = url;
                });
            </script>
            @endpush