@extends('layout.app')

@section('title', 'Tax Rates')

@section('content')
    <style>
        #DataTables_Table_0_info {
            float: left;
        }

        .dataTables_filter,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }

        .dataTables_wrapper .row:first-child {
            display: none !important;
        }

        .dataTables_wrapper {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .search-input input {
            padding-left: 35px !important;
            border-radius: 5px;
        }

        .search-input {
            position: relative;
            display: flex;
            align-items: center;
        }

        .btn-searchset {
            position: absolute;
            left: 10px;
            z-index: 10;
            padding: 0;
            top: 7px !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            border-color: #ff9f43 !important;
            color: #fff !important;
        }

        .pagination .page-link {
            color: #1b2850;
        }
#tax-rates-table {
    width: 100% !important;
    table-layout: auto;
}
        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }
            .table-top .wordset {
                margin-top: 0 !important;
            }
            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: 1rem !important;
            }
            .dataTables_filter {
                text-align: left !important;
            }

            .form-group {
                margin-bottom: 15px !important;
            }

            /* Page header button - full width on mobile */
            .page-btn .btn {
                width: 100%;
                margin-top: 10px;
            }

            /* Table responsive styles */
            .table-responsive {
    overflow-x: hidden !important;
}

            .datanew {
                font-size: 11px;
            }

            .datanew th,
            .datanew td {
                padding: 6px 3px;
            }

            /* Show only Tax name and Details */
            .datanew thead th:nth-child(2),
            .datanew tbody td:nth-child(2),
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3),
            .datanew thead th:nth-child(4),
            .datanew tbody td:nth-child(4) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .tax-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }

            /* Modal responsive */
            .modal-dialog {
                margin: 10px;
            }

            .modal-content {
                width: 100%;
            }
        }
@media (min-width: 768px) {
    .table-responsive {
        overflow-x: hidden !important;
    }
}
        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .table-top {
                flex-direction: row;
                margin-bottom: 0 !important;
            }
            .table-top .wordset {
                margin-top: 0 !important;
            }
            .dataTables_length {
                margin-left: .8rem !important;
                margin-bottom: 1rem !important;
            }
            .dataTables_filter {
                text-align: left !important;
            }

            .form-group {
                margin-bottom: 15px !important;
            }

            .datanew {
                font-size: 12px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 4px;
            }

            /* Show Tax name, Details, and Tax (%) */
            .datanew thead th:nth-child(3),
            .datanew tbody td:nth-child(3),
            .datanew thead th:nth-child(4),
            .datanew tbody td:nth-child(4) {
                display: none;
            }

            /* Center Details column */
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .tax-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .datanew {
                font-size: 13px;
            }

            .datanew th,
            .datanew td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets */
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5) {
                display: none;
            }

            /* Hide expandable rows on tablets */
            .tax-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            .datanew {
                font-size: 14px;
            }

            .datanew th,
            .datanew td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop */
            .datanew thead th:nth-child(5),
            .datanew tbody td:nth-child(5) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .tax-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .tax-details-row {
            display: none;
        }

        .tax-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .tax-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .tax-details-list {
            margin-bottom: 15px;
        }

        .tax-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .tax-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .tax-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .tax-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        .tax-action-buttons-simple {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-start;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .btn-icon-mobile-tax,
        button.btn-icon-mobile-tax {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #1b2850;
            background: transparent;
            transition: all 0.3s;
            border: 2px solid #1b2850;
            cursor: pointer;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        button.btn-icon-mobile-tax {
            border: 2px solid #1b2850;
            background: transparent;
        }

        .btn-icon-mobile-tax:hover {
            background: #1b2850;
            color: white;
            transform: scale(1.1);
        }

        .btn-icon-mobile-tax img {
            width: 18px;
            height: 18px;
        }

        /* Toggle button styles */
        .tax-toggle-btn-table {
            background: #ff9f43;
            border: none;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 18px;
            font-weight: bold;
            transition: all 0.3s;
        }

        .tax-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .tax-toggle-btn-table.minus {
            background: #dc3545;
        }

        .tax-toggle-btn-table.minus:hover {
            background: #c82333;
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Tax Rates</h4>

            </div>
            <div class="page-btn">
                @if (app('hasPermission')(15, 'add'))
                <a class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addpayment"><img
                        src="{{env('ImagePath').'admin/assets/img/icons/plus.svg'}}" alt="img" class="me-1">New Tax Rates</a>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="mb-2">
                    <div class="search-set d-flex justify-content-md-start justify-content-start w-100">
                        <div class="search-path"></div>
                        <div class="search-input">
                            <a class="btn btn-searchset">
                                <img src="{{ env('ImagePath') . 'admin/assets/img/icons/search-white.svg' }}"
                                    alt="img">
                            </a>
                            <input type="text" id="tax-search-input" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table datanew" id="tax-rates-table">
                        <thead>
                            <tr>
                                <th>Tax name</th>
                                <th>Tax (%)</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                                <th class="text-center">Details</th>
                            </tr>
                        </thead>
                        <tbody>


                        </tbody>
                    </table>
                </div>

                <div
                    class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="tax-per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="tax-pagination-from">0</span> - <span id="tax-pagination-to">0</span> of
                            <span id="tax-pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Tax pagination">
                        <ul class="pagination pagination-sm mb-0" id="tax-pagination-numbers"></ul>
                    </nav>
                </div>
            </div>
        </div>

    </div>


    <div class="modal fade" id="addpayment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add TAX </h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Name<span class="manitory">*</span></label>
                                <input type="text">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Rate(%)<span class="manitory">*</span></label>
                                <input type="text">
                                <small id="taxRateError" class="text-danger d-none">Tax rate cannot be negative.</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <select class="select">
                                    <option value="active"> Active</option>
                                    <option value="inactive"> DeActive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-add">Confirm</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editpayment" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tax</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Name<span class="manitory">*</span></label>
                                <input type="text">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label>Tax Rate(%)<span class="manitory">*</span></label>
                                <input type="text">
                                <small id="taxRateError1" class="text-danger d-none">Tax rate cannot be negative.</small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                <select class="select">
                                    <option value="active"> Active</option>
                                    <option value="inactive"> DeActive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-submit btn-submit-edit">Update</button>
                    <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('js')
    <script>
        // Global variables
        var taxRatesDataMap = {};

        // Helper function to build expandable row content
        function buildTaxRateExpandableRowContent(tax) {
            const statusBadge = tax.status === "active" ?
                '<span class="badge bg-success">Active</span>' :
                '<span class="badge bg-danger">DeActive</span>';

            // Build action buttons HTML
            let actionButtons = '';
            @if (app('hasPermission')(15, 'edit'))
            actionButtons += `
                <a href="javascript:void(0);" class="btn-icon-mobile-tax edit-tax" data-id="${tax.id}" data-bs-toggle="modal" data-bs-target="#editpayment" title="Edit">
                    <img src="{{env('ImagePath').'admin/assets/img/icons/edit.svg'}}" alt="Edit">
                </a>
            `;
            @endif
            @if (app('hasPermission')(15, 'delete'))
            actionButtons += `
                <button type="button" class="btn-icon-mobile-tax delete-tax" data-id="${tax.id}" title="Delete">
                    <img src="{{env('ImagePath').'admin/assets/img/icons/delete.svg'}}" alt="Delete">
                </button>
            `;
            @endif

            return `
                <td colspan="5" class="tax-details-content">
                    <div class="tax-details-list">
                        <div class="tax-detail-row-simple">
                            <span class="tax-detail-label-simple">Tax Rate:</span>
                            <span class="tax-detail-value-simple">${tax.tax_rate}%</span>
                        </div>
                        <div class="tax-detail-row-simple">
                            <span class="tax-detail-label-simple">Status:</span>
                            <span class="tax-detail-value-simple">${statusBadge}</span>
                        </div>
                    </div>
                    ${actionButtons ? `<div class="tax-action-buttons-simple">${actionButtons}</div>` : ''}
                </td>
            `;
        }

        // Toggle function for tax rate rows
        window.toggleTaxRateRowDetails = function(taxId) {
            const btn = $(`.tax-toggle-btn-table[data-tax-id="${taxId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.tax-details-row[data-tax-id="${taxId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const taxData = taxRatesDataMap[taxId];
                if (taxData) {
                    detailsRow = $('<tr>')
                        .addClass('tax-details-row')
                        .attr('data-tax-id', taxId)
                        .html(buildTaxRateExpandableRowContent(taxData));
                    row.after(detailsRow);
                } else {
                    return;
                }
            }

            if (detailsRow.hasClass('show')) {
                detailsRow.removeClass('show');
                btn.removeClass('minus');
                icon.text('+');
            } else {
                detailsRow.addClass('show');
                btn.addClass('minus');
                icon.text('−');
            }
        };

        $(document).ready(function () {
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $addSubmitBtn = $(".btn-submit-add");
            const $editSubmitBtn = $(".btn-submit-edit");
            const addSubmitBtnDefaultHtml = $addSubmitBtn.html();
            const editSubmitBtnDefaultHtml = $editSubmitBtn.html();

            function toggleModalSubmitLoading(type, isLoading) {
                const isAdd = type === "add";
                const $btn = isAdd ? $addSubmitBtn : $editSubmitBtn;
                const defaultHtml = isAdd ? addSubmitBtnDefaultHtml : editSubmitBtnDefaultHtml;

                if (isLoading) {
                    $btn
                        .prop("disabled", true)
                        .addClass("disabled")
                        .attr("aria-disabled", "true")
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $btn
                        .prop("disabled", false)
                        .removeClass("disabled")
                        .removeAttr("aria-disabled")
                        .html(defaultHtml);
                }
            }

            let currentPage = 1;
            let lastPage = 1;
            let perPage = 10;
            let searchQuery = '';

            var table = $("table.datanew").DataTable({ // Initialize DataTable
                destroy: true,
                bFilter: false,
                paging: false,
                info: false,
                searching: false,
                ordering: true,
                dom: 't',
                language: {
                    emptyTable: "No tax rates found"
                }
            });
            loadTaxRates(currentPage); // Load tax rates when page loads

            $('#tax-search-input').on('keyup', function () {
                searchQuery = $(this).val();
                loadTaxRates(1);
            });

            $('#tax-per-page-select').on('change', function () {
                perPage = $(this).val();
                loadTaxRates(1);
            });

            function loadTaxRates(page = 1) {
                let url = `{{ route('tax-rates.index') }}?page=${page}&per_page=${perPage}`;
                if (selectedSubAdminId) {
                    url += "&selectedSubAdminId=" + encodeURIComponent(selectedSubAdminId);
                }
                if (searchQuery) {
                    url += "&search=" + encodeURIComponent(searchQuery);
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        let tableRows = [];
                        // Clear previous data map
                        taxRatesDataMap = {};
                        const taxRates = response.taxRates || response.data || [];
                        const pagination = response.pagination || {
                            current_page: 1,
                            last_page: 1,
                            per_page: perPage,
                            total: taxRates.length
                        };

                        currentPage = pagination.current_page;
                        lastPage = pagination.last_page;
                        updatePaginationUI(pagination);

                        taxRates.forEach(function (tax) {
                            // Store tax data for expandable row
                            taxRatesDataMap[tax.id] = tax;

                            let statusBadge = tax.status === "active" ?
                                '<span class="badge bg-success">Active</span>' :
                                '<span class="badge bg-danger">DeActive</span>';

                            // Toggle button for Details column
                            const detailsToggle = `
                                <button class="tax-toggle-btn-table" onclick="toggleTaxRateRowDetails('${tax.id}')" data-tax-id="${tax.id}">
                                    <span class="toggle-icon">+</span>
                                </button>
                            `;

                            tableRows.push([
                                `${tax.tax_name}`,
                                `${tax.tax_rate}%`,
                                `${statusBadge}`,
                                `
                                    <div class="text-end">
                                        @if (app('hasPermission')(15, 'edit'))
                                        <a href="javascript:void(0);" class="edit-tax" data-id="${tax.id}" data-bs-toggle="modal" data-bs-target="#editpayment">
                                            <img src="{{env('ImagePath').'admin/assets/img/icons/edit.svg'}}" alt="Edit">
                                        </a>
                                        @endif
                                        @if (app('hasPermission')(15, 'delete'))
                                        <a href="javascript:void(0);" class="delete-tax mx-3" data-id="${tax.id}">
                                            <img src="{{env('ImagePath').'admin/assets/img/icons/delete.svg'}}" alt="Delete">
                                        </a>
                                        @endif
                                    </div>
                                `,
                                `${detailsToggle}`
                            ]);
                        });

                        // Remove all expandable rows before reloading
                        $('.tax-details-row').remove();
                        table.clear().rows.add(tableRows).draw();

                        // Trigger resize handler to ensure proper layout
                        if (window.handleTaxResize) {
                            setTimeout(function() {
                                window.handleTaxResize();
                            }, 100);
                        }
                    }
                });
            }

            function updatePaginationUI(pagination) {
                let from = (pagination.current_page - 1) * pagination.per_page + 1;
                let to = pagination.current_page * pagination.per_page;

                if (to > pagination.total) {
                    to = pagination.total;
                }

                if (pagination.total === 0) {
                    from = 0;
                }

                $('#tax-pagination-from').text(from);
                $('#tax-pagination-to').text(to);
                $('#tax-pagination-total').text(pagination.total);

                let paginationHtml = '';
                let startPage = Math.max(1, pagination.current_page - 2);
                let endPage = Math.min(pagination.last_page, startPage + 4);

                if (endPage - startPage < 4) {
                    startPage = Math.max(1, endPage - 4);
                }

                for (let i = startPage; i <= endPage; i++) {
                    paginationHtml += `
                        <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                            <a class="page-link tax-page-link" href="javascript:void(0);" data-page="${i}">${i}</a>
                        </li>
                    `;
                }

                $('#tax-pagination-numbers').html(paginationHtml);
                $('.pagination-controls').toggle(pagination.total > 0);
            }

            $(document).on('click', '.tax-page-link', function (e) {
                e.preventDefault();
                let page = $(this).data('page');
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    loadTaxRates(page);
                }
            });

            function clearErrors() {
                $(".error-text").remove();
                $("input, select").removeClass("is-invalid");
            }

            function validateForm(modal) {
                clearErrors();
                let isValid = true;

                $(modal).find("input, select").each(function () {
                    let value = $(this).val().trim();
                    let label = $(this).prev("label").text().replace("*", "").trim();

                    if (value === "") {
                        $(this).addClass("is-invalid");
                        $(this).after(`<small class="text-danger error-text">${label} is required.</small>`);
                        isValid = false;
                    }
                });

                return isValid;
            }

            function resetForm(modal) {
                $(modal).find("input[type='text']").val(""); // Clear text inputs
                $(modal).find("select").val("active"); // Reset status to default
                clearErrors(); // Remove error messages
            }

            $(".btn-submit-add").on("click", function () {
                if ($addSubmitBtn.prop("disabled")) return;
                if (!validateForm("#addpayment")) return;

                let taxName = $("#addpayment input[type='text']").eq(0).val();
                let taxRate = $("#addpayment input[type='text']").eq(1).val();
                let taxStatus = $("#addpayment select").val();
                if (taxRate < 0) {
                    $("#taxRateError").removeClass("d-none"); // show error
                    $(this).val(0); // reset to 0
                    return false;
                } else {
                    $("#taxRateError").addClass("d-none"); // hide error
                }
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // $.ajax({
                //     url: "{{ route('tax-rates.store') }}",
                //     type: "POST",
                //     data: {
                //         name: taxName,
                //         rate: taxRate,
                //         status: taxStatus,
                //         selectedSubAdminId: selectedSubAdminId,
                //         _token: "{{ csrf_token() }}"
                //     },
                //     headers: {
                //         "Authorization": "Bearer " + authToken
                //     },
                //     success: function (response) {
                //         $("#addpayment").modal("hide");
                //         resetForm("#addpayment"); // Reset form after success
                //         // Swal.fire("Success", response.message, "success");
                //         Swal.fire({
                //             title: "Success!",
                //             text: response.message,
                //             icon: "success",
                //             confirmButtonText: "OK",
                //             confirmButtonColor: "#ff9f43",
                //         }).then(() => {
                //             // window.location.reload();
                //         });
                //         loadTaxRates();
                //     }
                // });
                $.ajax({
    url: "{{ route('tax-rates.store') }}",
    type: "POST",
    beforeSend: function () {
        toggleModalSubmitLoading("add", true);
    },
    data: {
        name: taxName,
        rate: taxRate,
        status: taxStatus,
        selectedSubAdminId: selectedSubAdminId,
        _token: "{{ csrf_token() }}"
    },
    headers: {
        "Authorization": "Bearer " + authToken
    },

    success: function (response) {

        $("#addpayment").modal("hide");
        resetForm("#addpayment"); // Reset form after success

        Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#ff9f43",
        }).then(() => {
            // window.location.reload();
        });

        loadTaxRates();
    },

    error: function (xhr) {

        // 🔥 Remove old errors
        $(".error-text").remove();
        $("#addpayment input, #addpayment select").removeClass("is-invalid");

        if (xhr.status === 422) {

            let errors = xhr.responseJSON?.errors;

            if (errors) {
                $.each(errors, function (key, value) {

                    if (key === "name") {
                        let input = $("#addpayment input[type='text']").eq(0);
                        input.addClass("is-invalid");
                        input.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                    if (key === "rate") {
                        let input = $("#addpayment input[type='text']").eq(1);
                        input.addClass("is-invalid");
                        input.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                    if (key === "status") {
                        let select = $("#addpayment select");
                        select.addClass("is-invalid");
                        select.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                });
            }

        } else if (xhr.status === 401) {

            Swal.fire({
                title: "Unauthorized!",
                text: "Session expired. Please login again.",
                icon: "warning"
            }).then(() => {
                window.location.href = "/login";
            });

        } else {

            Swal.fire({
                title: "Error!",
                text: xhr.responseJSON?.message || "Something went wrong.",
                icon: "error"
            });

    }
    }
    ,
    complete: function () {
        toggleModalSubmitLoading("add", false);
    }
});
            });

            $(document).on("click", ".edit-tax", function () {
                let taxId = $(this).data("id");
                $.ajax({
                    url: `/api/tax-rates/${taxId}`,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function (response) {
                        console.log(response.taxRate.status);

                        $("#editpayment input[type='text']").eq(0).val(response.taxRate.tax_name);
                        $("#editpayment input[type='text']").eq(1).val(parseInt(response.taxRate.tax_rate));
                        $("#editpayment select").val(response.taxRate.status.toLowerCase()).trigger("change");
                        $("#editpayment").data("id", taxId);
                    }
                });
            });

            $(".btn-submit-edit").on("click", function () {
                if ($editSubmitBtn.prop("disabled")) return;
                if (!validateForm("#editpayment")) return;

                let taxId = $("#editpayment").data("id");
                let taxName = $("#editpayment input[type='text']").eq(0).val();
                let taxRate = $("#editpayment input[type='text']").eq(1).val();
                let taxStatus = $("#editpayment select").val();

                if (taxRate < 0) {
                    $("#taxRateError1").removeClass("d-none"); // show error
                    $(this).val(0); // reset to 0
                    return false;
                } else {
                    $("#taxRateError1").addClass("d-none"); // hide error
                }

                // $.ajax({
                //     url: `/api/tax-rates/update/${taxId}`,
                //     type: "POST",
                //     data: {
                //         name: taxName,
                //         rate: taxRate,
                //         status: taxStatus,
                //         _token: "{{ csrf_token() }}"
                //     },
                //     headers: {
                //         "Authorization": "Bearer " + authToken
                //     },
                //     success: function (response) {
                //         $("#editpayment").modal("hide");
                //         // Swal.fire("Success", response.message, "success");
                //         Swal.fire({
                //             title: "Success!",
                //             text: response.message,
                //             icon: "success",
                //             confirmButtonText: "OK",
                //             confirmButtonColor: "#ff9f43",
                //         }).then(() => {
                //             // window.location.href = "/vendor";
                //         });
                //         loadTaxRates();
                //     }
                // });
                $.ajax({
    url: `/api/tax-rates/update/${taxId}`,
    type: "POST",
    beforeSend: function () {
        toggleModalSubmitLoading("edit", true);
    },
    data: {
        name: taxName,
        rate: taxRate,
        status: taxStatus,
        _token: "{{ csrf_token() }}"
    },
    headers: {
        "Authorization": "Bearer " + authToken
    },

    success: function (response) {

        $("#editpayment").modal("hide");

        Swal.fire({
            title: "Success!",
            text: response.message,
            icon: "success",
            confirmButtonText: "OK",
            confirmButtonColor: "#ff9f43",
        }).then(() => {
            // window.location.href = "/vendor";
        });

        loadTaxRates();
    },

    error: function (xhr) {

        // 🔥 Remove old errors
        $(".error-text").remove();
        $("#editpayment input, #editpayment select").removeClass("is-invalid");

        if (xhr.status === 422) {

            let errors = xhr.responseJSON?.errors;

            if (errors) {
                $.each(errors, function (key, value) {

                    if (key === "name") {
                        let input = $("#editpayment input[type='text']").eq(0);
                        input.addClass("is-invalid");
                        input.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                    if (key === "rate") {
                        let input = $("#editpayment input[type='text']").eq(1);
                        input.addClass("is-invalid");
                        input.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                    if (key === "status") {
                        let select = $("#editpayment select");
                        select.addClass("is-invalid");
                        select.after(`<small class="text-danger error-text">${value[0]}</small>`);
                    }

                });
            }

        } else if (xhr.status === 401) {

            Swal.fire({
                title: "Unauthorized!",
                text: "Session expired. Please login again.",
                icon: "warning"
            }).then(() => {
                window.location.href = "/login";
            });

        } else {

            Swal.fire({
                title: "Error!",
                text: xhr.responseJSON?.message || "Something went wrong.",
                icon: "error"
            });

    }
    }
    ,
    complete: function () {
        toggleModalSubmitLoading("edit", false);
    }
});
            });

            $(document).on("click", ".delete-tax", function () {
                let taxId = $(this).data("id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#ff9f43", // Orange
                    cancelButtonColor: "#6c757d",  // Gray
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/tax-rates/delete/${taxId}`,
                            type: "POST", // or "DELETE" if your Laravel route uses Route::delete
                            data: {
                                _token: "{{ csrf_token() }}",
                            },
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function (response) {
                                Swal.fire({
                                    title: "Deleted!",
                                    text: response.message,
                                    icon: "success",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });
                                if (currentPage > 1 && Object.keys(taxRatesDataMap).length === 1) {
                                    currentPage--;
                                }
                                loadTaxRates(currentPage);
                            },
                            error: function (xhr) {
                                let msg = xhr.responseJSON?.error || xhr.responseJSON?.message || "An error occurred.";
                                Swal.fire({
                                    title: "Error!",
                                    text: msg,
                                    icon: "error",
                                    confirmButtonColor: "#d33",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });

            // Reset form when modal is closed
            $("#addpayment, #editpayment").on("hidden.bs.modal", function () {
                resetForm(this);
            });

            // Resize handler for responsive behavior
            let taxResizeTimer;
            let lastTaxWidth = $(window).width();

            function forceTaxCSSRecalculation() {
                const temp = document.createElement('div');
                temp.style.width = '1px';
                temp.style.height = '1px';
                temp.style.position = 'absolute';
                temp.style.visibility = 'hidden';
                document.body.appendChild(temp);
                void temp.offsetWidth;
                void temp.offsetHeight;
                document.body.removeChild(temp);

                void window.innerWidth;
                void window.innerHeight;
                void document.documentElement.offsetWidth;
                void document.documentElement.offsetHeight;
            }

            function handleTaxResize() {
                clearTimeout(taxResizeTimer);
                taxResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastTaxWidth = currentWidth;

                    // Remove all expandable rows if on desktop/tablet (>= 768px)
                    if (currentWidth >= 768) {
                        $('.tax-details-row').remove();
                        // Reset all toggle buttons to + state
                        $('.tax-toggle-btn-table').removeClass('minus').find('.toggle-icon').text('+');
                    }

                    // Force CSS recalculation
                    forceTaxCSSRecalculation();

                    const taxTable = document.getElementById('tax-rates-table');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [taxTable, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Adjust DataTable columns if table exists
                    if (table) {
                        table.columns.adjust().draw();
                    }

                    forceTaxCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.tax').on('resize.tax', handleTaxResize);

            if (window.taxResizeHandler) {
                window.removeEventListener('resize', window.taxResizeHandler);
            }
            window.taxResizeHandler = handleTaxResize;
            window.addEventListener('resize', window.taxResizeHandler, { passive: true });

            // Orientation change handler
            $(window).off('orientationchange.tax').on('orientationchange.tax', function() {
                setTimeout(function() {
                    lastTaxWidth = $(window).width();
                    handleTaxResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastTaxWidth = $(window).width();
                    handleTaxResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const taxQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            taxQueries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleTaxResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleTaxResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastTaxWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastTaxWidth = $(window).width();
                    handleTaxResize();
                }, 500);
            });

            window.handleTaxResize = handleTaxResize;

        });
    </script>
@endpush
