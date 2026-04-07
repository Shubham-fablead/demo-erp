@extends('layout.app')

@section('title', 'WhatsApp App Configuration')

@section('content')
    <style>
        /* Responsive breakpoints for all screen sizes */

        /* Extra small devices (phones, less than 576px) */
        @media screen and (max-width: 575.98px) {
            .form-group {
                margin-bottom: 15px !important;
            }

            /* Form fields - full width on mobile */
            #configForm .row {
                flex-direction: column !important;
            }

            #configForm .col-lg-6,
            #configForm .col-sm-6,
            #configForm .col-lg-12 {
                width: 100% !important;
                margin-bottom: 10px;
            }

            /* Save button - full width */
            #btn-save-config {
                width: 100% !important;
            }

            /* Status badge - stack below title */
            .d-flex.align-items-center.mb-3.mt-3 {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .status-badge {
                margin-left: 0 !important;
                margin-top: 10px;
            }

            /* Tabs - full width on mobile */
            .nav-tabs {
                flex-direction: column;
            }

            .nav-tabs .nav-item {
                width: 100%;
            }

            .nav-tabs .nav-link {
                width: 100%;
                text-align: center;
                padding: 12px;
            }

            /* Templates header - stack on mobile */
            .d-flex.align-items-center.justify-content-between.mb-3.mt-3 {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 10px;
            }

            #btn-refresh-templates {
                width: 100%;
            }

            /* Table responsive styles */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            #templatesTable {
                font-size: 11px;
            }

            #templatesTable th,
            #templatesTable td {
                padding: 6px 3px;
            }

            /* Show only Templates and Details */
            #templatesTable thead th:nth-child(2),
            #templatesTable tbody td:nth-child(2),
            #templatesTable thead th:nth-child(3),
            #templatesTable tbody td:nth-child(3),
            #templatesTable thead th:nth-child(4),
            #templatesTable tbody td:nth-child(4) {
                display: none;
            }

            /* Center Details column */
            #templatesTable thead th:nth-child(5),
            #templatesTable tbody td:nth-child(5) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .whatsapp-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Small devices (landscape phones, 576px and up) */
        @media screen and (min-width: 576px) and (max-width: 767.98px) {
            .form-group {
                margin-bottom: 15px !important;
            }

            #configForm .row {
                flex-wrap: wrap;
                gap: 10px;
            }

            #configForm .col-lg-6 {
                flex: 0 0 calc(50% - 5px);
            }

            #configForm .col-lg-12 {
                width: 100%;
            }

            /* Tabs - side by side */
            .nav-tabs {
                flex-direction: row;
            }

            .nav-tabs .nav-item {
                flex: 1;
            }

            #templatesTable {
                font-size: 12px;
            }

            #templatesTable th,
            #templatesTable td {
                padding: 8px 4px;
            }

            /* Show Templates, Details, and Status */
            #templatesTable thead th:nth-child(2),
            #templatesTable tbody td:nth-child(2),
            #templatesTable thead th:nth-child(4),
            #templatesTable tbody td:nth-child(4) {
                display: none;
            }

            /* Center Details column */
            #templatesTable thead th:nth-child(5),
            #templatesTable tbody td:nth-child(5) {
                text-align: center;
                width: 60px;
                min-width: 60px;
            }

            .whatsapp-toggle-btn-table {
                margin: 0 auto;
                display: block;
            }
        }

        /* Medium devices (tablets, 768px and up to 1024px) */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            .form-group {
                margin-bottom: 15px !important;
            }

            #templatesTable {
                font-size: 13px;
            }

            #templatesTable th,
            #templatesTable td {
                padding: 8px 6px;
            }

            /* Hide Details column on tablets */
            #templatesTable thead th:nth-child(5),
            #templatesTable tbody td:nth-child(5) {
                display: none;
            }

            /* Hide expandable rows on tablets */
            .whatsapp-details-row {
                display: none !important;
            }
        }

        /* Large devices (desktops, 1025px and up) */
        @media screen and (min-width: 1025px) {
            #templatesTable {
                font-size: 14px;
            }

            #templatesTable th,
            #templatesTable td {
                padding: 12px 10px;
            }

            /* Hide Details column on desktop */
            #templatesTable thead th:nth-child(5),
            #templatesTable tbody td:nth-child(5) {
                display: none;
            }

            /* Hide expandable rows on larger screens */
            .whatsapp-details-row {
                display: none !important;
            }
        }

        /* Expandable row details - available for all screen sizes */
        .whatsapp-details-row {
            display: none;
        }

        .whatsapp-details-row.show {
            display: table-row;
        }

        /* Expandable content styles */
        .whatsapp-details-content {
            padding: 15px;
            background: #fff;
            border-top: 2px solid #e0e0e0;
        }

        .whatsapp-details-list {
            margin-bottom: 15px;
        }

        .whatsapp-detail-row-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .whatsapp-detail-row-simple:last-of-type {
            border-bottom: none;
        }

        .whatsapp-detail-label-simple {
            font-weight: 600;
            color: #595b5d;
            font-size: 14px;
        }

        .whatsapp-detail-value-simple {
            color: #1b2850;
            font-size: 14px;
            text-align: right;
        }

        /* Toggle button styles */
        .whatsapp-toggle-btn-table {
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

        .whatsapp-toggle-btn-table:hover {
            background: #ff8c2e;
        }

        .whatsapp-toggle-btn-table.minus {
            background: #dc3545;
        }

        .whatsapp-toggle-btn-table.minus:hover {
            background: #c82333;
        }

        /* Existing styles */
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important;
            }
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            margin-left: 10px;
        }
        .status-connected {
            background-color: #10b981;
            color: white;
        }
        .status-disconnected {
            background-color: #ef4444;
            color: white;
        }
        .info-message {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: #6c757d;
            padding: 12px 24px;
            font-weight: 500;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: #ff9f43;
        }
        .nav-tabs .nav-link.active {
            color: #ff9f43;
            border-bottom-color: #ff9f43;
            background-color: transparent;
        }
        .template-item {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: #fff;
            transition: all 0.3s ease;
        }
        .template-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .template-item.active-template {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        .template-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .template-name {
            font-weight: 600;
            color: #1f2937;
            font-size: 16px;
        }
        .template-status {
            font-size: 12px;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
        }
        .status-APPROVED {
            background-color: #10b981;
            color: white;
        }
        .status-PENDING {
            background-color: #f59e0b;
            color: white;
        }
        .status-REJECTED {
            background-color: #ef4444;
            color: white;
        }
        .template-details {
            color: #6b7280;
            font-size: 14px;
            margin-top: 8px;
        }
        .template-language {
            display: inline-block;
            background-color: #e5e7eb;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 8px;
        }
        .loading-spinner {
            text-align: center;
            padding: 40px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            border-width: 0.3em;
        }
        .template-content {
            font-size: 13px;
            line-height: 1.6;
        }
        .template-content strong {
            color: #374151;
            font-weight: 600;
            margin-right: 8px;
        }
        @media screen and (max-width: 768px) {
            .template-header {
                flex-direction: column;
            }
            .template-header .form-check {
                margin-top: 10px;
                margin-left: 0 !important;
            }
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Configure your WhatsApp API settings</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div id="configFormSection" style="display: none;">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs" id="configTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="configuration-tab" data-bs-toggle="tab" data-bs-target="#configuration" type="button" role="tab" aria-controls="configuration" aria-selected="true">
                                Configuration
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" type="button" role="tab" aria-controls="templates" aria-selected="false">
                                Message Templates
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="configTabContent">
                        <!-- Configuration Tab -->
                        <div class="tab-pane fade show active" id="configuration" role="tabpanel" aria-labelledby="configuration-tab">
                            <div class="d-flex align-items-center mb-3 mt-3">
                                <h5 class="mb-0">WhatsApp App Configuration</h5>
                                <span class="status-badge status-disconnected" id="statusBadge">Disconnected</span>
                            </div>

                            <p class="text-muted mb-3">Enter your WhatsApp App details and WhatsApp Business credentials</p>

                            <div class="info-message">
                                <small>Credentials are stored locally. Use backend storage for production.</small>
                            </div>

                            <form id="configForm">
                                <input type="hidden" id="configId" name="id">
                                <div class="row">
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>WhatsApp App ID<span class="manitory">*</span></label>
                                            <input type="text" id="facebook_app_id" name="facebook_app_id" class="form-control" placeholder="Enter WhatsApp App ID" required>
                                            <div class="text-danger mt-1" id="facebook_app_id_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>WhatsApp App Secret<span class="manitory">*</span></label>
                                            <input type="password" id="facebook_app_secret" name="facebook_app_secret" class="form-control" placeholder="Enter WhatsApp App Secret" required>
                                            <div class="text-danger mt-1" id="facebook_app_secret_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Phone Number ID<span class="manitory">*</span></label>
                                            <input type="text" id="phone_number_id" name="phone_number_id" class="form-control" placeholder="Enter Phone Number ID" required>
                                            <div class="text-danger mt-1" id="phone_number_id_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>WhatsApp Business Account ID<span class="manitory">*</span></label>
                                            <input type="text" id="whatsapp_business_account_id" name="whatsapp_business_account_id" class="form-control" placeholder="Enter WhatsApp Business Account ID" required>
                                            <div class="text-danger mt-1" id="whatsapp_business_account_id_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Access Token<span class="manitory">*</span></label>
                                            <input type="text" id="access_token" name="access_token" class="form-control" placeholder="Enter Access Token" required>
                                            <div class="text-danger mt-1" id="access_token_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="form-group">
                                            <label>Webhook URL</label>
                                            <input type="url" id="webhook_url" name="webhook_url" class="form-control" placeholder="Enter Webhook URL">
                                            <div class="text-danger mt-1" id="webhook_url_error"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <button type="button" class="btn btn-submit" id="btn-save-config">Save Configuration</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Message Templates Tab -->
                        <div class="tab-pane fade" id="templates" role="tabpanel" aria-labelledby="templates-tab">
                            <div class="d-flex align-items-center justify-content-between mb-3 mt-3">
                                <h5 class="mb-0">WhatsApp Message Templates</h5>
                                <button type="button" class="btn btn-submit btn-sm" id="btn-refresh-templates">
                                    <i class="fas fa-sync-alt"></i> Refresh Templates
                                </button>
                            </div>

                            <p class="text-muted mb-3">View and manage your WhatsApp message templates from WhatsApp</p>

                            <div id="templatesLoading" class="loading-spinner" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading templates...</p>
                            </div>

                            <div id="templatesError" class="alert alert-danger" style="display: none;"></div>

                            <div class="table-responsive mt-3">
                                <table class="table datanew" id="templatesTable">
                                    <thead>
                                        <tr>
                                            <th>Templates</th>
                                            <th>Use for Module</th>
                                            <th>Status</th>
                                            <th>Active/Inactive</th>
                                            <th class="text-center">Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Templates will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="noConfigMessage" class="text-center py-5" style="display: none;">
                    <p class="text-muted">Please select a branch from the header to configure WhatsApp App settings.</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        // Global variables
        var whatsappTemplateDataMap = {};

        // Helper function to build expandable row content
        function buildWhatsAppTemplateExpandableRowContent(template) {
            const templateStatus = template.status || 'UNKNOWN';
            const isApproved = templateStatus === 'APPROVED';
            const onOff = template.on_off || 'inactive';

            const statusBadge = '<span class="template-status status-' + templateStatus + '">' + templateStatus + '</span>';

            // Only make toggle clickable if status is APPROVED
            let onOffBadge = '';
            if (isApproved) {
                onOffBadge = onOff === 'active'
                    ? '<span class="badge bg-success toggle-on-off" style="cursor: pointer;" data-template-id="' + template.id + '" data-current-status="active" data-status="' + templateStatus + '">Active</span>'
                    : '<span class="badge bg-secondary toggle-on-off" style="cursor: pointer;" data-template-id="' + template.id + '" data-current-status="inactive" data-status="' + templateStatus + '">Inactive</span>';
            } else {
                onOffBadge = onOff === 'active'
                    ? '<span class="badge bg-success" style="cursor: not-allowed; opacity: 0.6;" data-template-id="' + template.id + '" data-current-status="active" data-status="' + templateStatus + '" title="Only APPROVED templates can be toggled">Active</span>'
                    : '<span class="badge bg-secondary" style="cursor: not-allowed; opacity: 0.6;" data-template-id="' + template.id + '" data-current-status="inactive" data-status="' + templateStatus + '" title="Only APPROVED templates can be toggled">Inactive</span>';
            }

            // Get all currently used options (excluding current template)
            const usedOptions = [];
            if (window.allTemplatesData) {
                window.allTemplatesData.forEach(function(t) {
                    if (t.id !== template.id && t.use_for_template) {
                        usedOptions.push(t.use_for_template);
                    }
                });
            }

            // Build select dropdown for use_for_template
            const currentValue = template.use_for_template || '';
            let selectHtml = '<select class="form-control form-control-sm use-for-module-select" data-template-id="' + template.id + '" style="min-width: 150px;">';
            selectHtml += '<option value="">-- Select --</option>';

            const options = ['Birthday', 'Complete order', 'Pending order', 'Return order'];
            options.forEach(function(option) {
                const isAvailable = option === currentValue || !usedOptions.includes(option);
                const selected = option === currentValue ? 'selected' : '';
                const disabled = !isAvailable ? 'disabled' : '';
                const label = !isAvailable ? option + ' (Already assigned)' : option;
                selectHtml += '<option value="' + escapeHtml(option) + '" ' + selected + ' ' + disabled + '>' + escapeHtml(label) + '</option>';
            });

            selectHtml += '</select>';

            return `
                <td colspan="5" class="whatsapp-details-content">
                    <div class="whatsapp-details-list">
                        <div class="whatsapp-detail-row-simple">
                            <span class="whatsapp-detail-label-simple">Use for Module:</span>
                            <span class="whatsapp-detail-value-simple">${selectHtml}</span>
                        </div>
                        <div class="whatsapp-detail-row-simple">
                            <span class="whatsapp-detail-label-simple">Status:</span>
                            <span class="whatsapp-detail-value-simple">${statusBadge}</span>
                        </div>
                        <div class="whatsapp-detail-row-simple">
                            <span class="whatsapp-detail-label-simple">Active/Inactive:</span>
                            <span class="whatsapp-detail-value-simple">${onOffBadge}</span>
                        </div>
                    </div>
                </td>
            `;
        }

        // Helper function to escape HTML
        function escapeHtml(text) {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        // Toggle function for template rows
        window.toggleWhatsAppTemplateRowDetails = function(templateId) {
            const btn = $(`.whatsapp-toggle-btn-table[data-template-id="${templateId}"]`);
            if (btn.length === 0) return;

            const row = btn.closest('tr');
            let detailsRow = row.next(`tr.whatsapp-details-row[data-template-id="${templateId}"]`);
            const icon = btn.find('.toggle-icon');

            if (detailsRow.length === 0) {
                const templateData = whatsappTemplateDataMap[templateId];
                if (templateData) {
                    detailsRow = $('<tr>')
                        .addClass('whatsapp-details-row')
                        .attr('data-template-id', templateId)
                        .html(buildWhatsAppTemplateExpandableRowContent(templateData));
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

        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");

            // Get current branch ID from header selector
            function getCurrentBranchId() {
                const headerBranchSelect = document.getElementById('selectedSubAdminId');
                if (headerBranchSelect) {
                    return headerBranchSelect.value || '';
                }
                return localStorage.getItem('selectedSubAdminId') || '';
            }

            // Load configuration for current branch
            function loadConfiguration() {
                const branchId = getCurrentBranchId();

                // Build URL with selectedSubAdminId parameter for backend to handle Main Branch
                let url = "/api/facebook-app-configurations";
                if (branchId) {
                    url += "?branch_id=" + branchId + "&selectedSubAdminId=" + branchId;
                } else {
                    url += "?selectedSubAdminId=";
                }
                // console.log(url);
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status && response.data.length > 0) {
                            // Configuration exists - load it
                            const config = response.data[0];

                            $("#configId").val(config.id);
                            $("#facebook_app_id").val(config.facebook_app_id);
                            $("#facebook_app_secret").val(config.facebook_app_secret);
                            $("#phone_number_id").val(config.phone_number_id);
                            $("#whatsapp_business_account_id").val(config.whatsapp_business_account_id);
                            $("#access_token").val(config.access_token);
                            $("#webhook_url").val(config.webhook_url);

                            // Update status badge
                            $("#statusBadge").removeClass('status-disconnected').addClass('status-connected').text('Connected');

                            // Update configuration status
                            updateConfigurationStatus(config);

                            $("#configFormSection").show();
                            $("#noConfigMessage").hide();
                        } else {
                            // No configuration - show empty form
                            $("#configForm")[0].reset();
                            $("#configId").val('');
                            $("#statusBadge").removeClass('status-connected').addClass('status-disconnected').text('Disconnected');

                            // Update configuration status
                            updateConfigurationStatus(null);

                            $("#configFormSection").show();
                            $("#noConfigMessage").hide();
                        }
                    },
                    error: function() {
                        // Show empty form on error
                        $("#configForm")[0].reset();
                        $("#configId").val('');
                        $("#statusBadge").removeClass('status-connected').addClass('status-disconnected').text('Disconnected');

                        // Update configuration status
                        updateConfigurationStatus(null);

                        $("#configFormSection").show();
                        $("#noConfigMessage").hide();
                    }
                });
            }

            // Listen to header branch selector changes
            $(document).on('change', '#subBrandSelect', function() {
                // Small delay to ensure localStorage is updated
                setTimeout(function() {
                    loadConfiguration();
                }, 200);
            });

            // Save configuration
            $("#btn-save-config").on("click", function() {
                // Clear previous errors
                $(".text-danger").text('');

                // Get branch ID from header selector
                const branchId = getCurrentBranchId();

                const formData = {
                    id: $("#configId").val(),
                    branch_id: branchId || null,
                    selectedSubAdminId: branchId || '',
                    facebook_app_id: $("#facebook_app_id").val(),
                    facebook_app_secret: $("#facebook_app_secret").val(),
                    phone_number_id: $("#phone_number_id").val(),
                    whatsapp_business_account_id: $("#whatsapp_business_account_id").val(),
                    access_token: $("#access_token").val(),
                    webhook_url: $("#webhook_url").val(),
                    _token: "{{ csrf_token() }}"
                };

                // Validate required fields
                let hasError = false;
                const requiredFields = [
                    { id: 'facebook_app_id', name: 'WhatsApp App ID' },
                    { id: 'facebook_app_secret', name: 'WhatsApp App Secret' },
                    { id: 'phone_number_id', name: 'Phone Number ID' },
                    { id: 'whatsapp_business_account_id', name: 'WhatsApp Business Account ID' },
                    { id: 'access_token', name: 'Access Token' }
                ];

                requiredFields.forEach(function(field) {
                    if (!$("#" + field.id).val()) {
                        $("#" + field.id + "_error").text(field.name + ' is required');
                        hasError = true;
                    }
                });

                if (hasError) {
                    return;
                }

                const url = formData.id ? "/api/facebook-app-configurations/" + formData.id : "/api/facebook-app-configurations";
                const method = formData.id ? "PUT" : "POST";

                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                // Reload configuration to update status
                                loadConfiguration();
                                // If templates tab is active, reload templates too
                                if ($('#templates-tab').hasClass('active')) {
                                    loadMessageTemplates();
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $("#" + key + "_error").text(value[0]);
                            });
                        } else {
                            Swal.fire("Error!", xhr.responseJSON.message || "Something went wrong!", "error");
                        }
                    }
                });
            });

            // Load message templates
            function loadMessageTemplates() {
                const branchId = getCurrentBranchId();
                const whatsapp_business_account_id = $("#whatsapp_business_account_id").val();
                const access_token = $("#access_token").val();

                // Check if configuration exists
                if (!whatsapp_business_account_id || !access_token) {
                    $("#templatesLoading").hide();
                    $("#templatesError").hide();
                    $("#templatesList").html(
                        '<div class="alert alert-warning">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' +
                        '<strong>Configuration Required:</strong> ' +
                        'Please configure your WhatsApp App settings in the Configuration tab first before viewing message templates.' +
                        '</div>'
                    );
                    return;
                }

                $("#templatesLoading").show();
                $("#templatesError").hide();
                $("#templatesList").empty();

                // Step 1: Fetch templates from WhatsApp API
                const facebookApiUrl = `https://graph.facebook.com/v23.0/${whatsapp_business_account_id}/message_templates`;

                $.ajax({
                    url: facebookApiUrl,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + access_token
                    },
                    success: function(response) {
                        // Step 2: Store templates in database
                        if (response.data && response.data.length > 0) {
                            storeTemplatesInDatabase(response.data, branchId);
                        } else {
                            $("#templatesLoading").hide();
                            $("#templatesList").html('<div class="alert alert-info">No message templates found from WhatsApp.</div>');
                            // Still try to load stored templates
                            loadStoredTemplates(branchId);
                        }
                    },
                    error: function(xhr) {
                        $("#templatesLoading").hide();
                        // console.error("WhatsApp API Error:", xhr);

                        // Check for access token expiration
                        if (xhr.responseJSON && xhr.responseJSON.error && xhr.responseJSON.error.code == 190) {
                            $("#templatesError").html(
                                '<div class="alert alert-danger">' +
                                '<i class="fas fa-exclamation-triangle"></i> ' +
                                '<strong>Access Token Expired:</strong> ' +
                                'Your WhatsApp access token has expired. Please update it in the Configuration tab. ' +
                                '<a href="#configuration" class="alert-link" onclick="$(\'#configuration-tab\').click();">Go to Configuration</a>' +
                                '</div>'
                            ).show();
                        } else {
                            // If WhatsApp API fails, try to load stored templates from DB
                            loadStoredTemplates(branchId);
                        }
                    }
                });
            }

            // Store templates in database
            function storeTemplatesInDatabase(templates, branchId) {
                let url = "/api/facebook-app-configurations/store-templates";

                $.ajax({
                    url: url,
                    type: "POST",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json"
                    },
                    data: JSON.stringify({
                        templates: templates,
                        branch_id: branchId,
                        selectedSubAdminId: branchId || ''
                    }),
                    success: function(response) {
                        if (response.status) {
                            // console.log("Templates stored:", response.saved_count);
                            // Step 3: Load stored templates from database
                            loadStoredTemplates(branchId);
                        } else {
                            $("#templatesLoading").hide();
                            $("#templatesError").text(response.message || "Failed to store templates").show();
                            // Still try to display templates
                            displayTemplates(templates);
                        }
                    },
                    error: function(xhr) {
                        // console.error("Store templates error:", xhr);
                        $("#templatesLoading").hide();
                        // If storing fails, still display the templates
                        displayTemplates(templates);
                        // Also try to load stored templates
                        loadStoredTemplates(branchId);
                    }
                });
            }

            // Load stored templates from database
            function loadStoredTemplates(branchId) {
                let url = "/api/facebook-app-configurations/stored-templates";
                if (branchId) {
                    url += "?branch_id=" + branchId + "&selectedSubAdminId=" + branchId;
                } else {
                    url += "?selectedSubAdminId=";
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $("#templatesLoading").hide();

                        if (response.status && response.data && response.data.length > 0) {
                            displayTemplates(response.data);
                        } else {
                            if (templatesTable) {
                                templatesTable.clear().draw();
                            }
                        }
                    },
                    error: function(xhr) {
                        $("#templatesLoading").hide();
                        let errorMessage = "Failed to load stored templates.";
                        let showTokenError = false;

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            // Check for access token expiration (error code 190)
                            if (xhr.responseJSON.error_code == 190 || (xhr.responseJSON.error && xhr.responseJSON.error.code == 190)) {
                                showTokenError = true;
                                errorMessage = 'Access token has expired. Please update your WhatsApp App Configuration access token.';
                            }
                        }

                        if (showTokenError) {
                            $("#templatesError").html(
                                '<div class="alert alert-danger">' +
                                '<i class="fas fa-exclamation-triangle"></i> ' +
                                '<strong>Access Token Expired:</strong> ' + errorMessage + ' ' +
                                '<a href="#configuration" class="alert-link" onclick="$(\'#configuration-tab\').click();">Go to Configuration</a>' +
                                '</div>'
                            ).show();
                        } else {
                            $("#templatesError").text(errorMessage).show();
                        }

                        if (templatesTable) {
                            templatesTable.clear().draw();
                        }
                    }
                });
            }

            // Initialize DataTable
            var templatesTable = null;

            function initializeTemplatesTable() {
                if (templatesTable) {
                    templatesTable.destroy();
                }

                templatesTable = $('#templatesTable').DataTable({
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10,
                    ordering: true,
                    searching: true,
                    paging: true,
                    destroy: true,
                    language: {
                        emptyTable: "No templates found.",
                        zeroRecords: "No template record found.",
                    },
                    columns: [
                        { data: 'name', name: 'name' },
                        { data: 'use_for_template', name: 'use_for_template', orderable: false },
                        { data: 'status', name: 'status' },
                        { data: 'on_off', name: 'on_off', orderable: false },
                        { data: 'details', name: 'details', orderable: false, searchable: false }
                    ]
                });
            }

            // Display templates in DataTable
            function displayTemplates(templates) {
                if (!templates || templates.length === 0) {
                    if (templatesTable) {
                        templatesTable.clear().draw();
                    }
                    allTemplatesData = [];
                    whatsappTemplateDataMap = {};
                    return;
                }

                // Store templates data for checking used options
                allTemplatesData = templates;
                window.allTemplatesData = templates;

                // Clear and store template data map
                whatsappTemplateDataMap = {};

                // Initialize DataTable if not already done
                if (!templatesTable) {
                    initializeTemplatesTable();
                }

                const tableData = templates.map(function(template) {
                    // Store template data for expandable row
                    whatsappTemplateDataMap[template.id] = template;

                    const onOff = template.on_off || 'inactive';
                    const templateStatus = template.status || 'UNKNOWN';
                    const isApproved = templateStatus === 'APPROVED';

                    const statusBadge = '<span class="template-status status-' + templateStatus + '">' + templateStatus + '</span>';

                    // Only make toggle clickable if status is APPROVED
                    let onOffBadge = '';
                    if (isApproved) {
                        // APPROVED - make it clickable
                        onOffBadge = onOff === 'active'
                            ? '<span class="badge bg-success toggle-on-off" style="cursor: pointer;" data-template-id="' + template.id + '" data-current-status="active" data-status="' + templateStatus + '">Active</span>'
                            : '<span class="badge bg-secondary toggle-on-off" style="cursor: pointer;" data-template-id="' + template.id + '" data-current-status="inactive" data-status="' + templateStatus + '">Inactive</span>';
                    } else {
                        // Not APPROVED - show but not clickable
                        onOffBadge = onOff === 'active'
                            ? '<span class="badge bg-success" style="cursor: not-allowed; opacity: 0.6;" data-template-id="' + template.id + '" data-current-status="active" data-status="' + templateStatus + '" title="Only APPROVED templates can be toggled">Active</span>'
                            : '<span class="badge bg-secondary" style="cursor: not-allowed; opacity: 0.6;" data-template-id="' + template.id + '" data-current-status="inactive" data-status="' + templateStatus + '" title="Only APPROVED templates can be toggled">Inactive</span>';
                    }

                    // Get all currently used options (excluding current template)
                    const usedOptions = [];
                    templates.forEach(function(t) {
                        if (t.id !== template.id && t.use_for_template) {
                            usedOptions.push(t.use_for_template);
                        }
                    });

                    // Build select dropdown for use_for_template
                    const currentValue = template.use_for_template || '';
                    let selectHtml = '<select class="form-control form-control-sm use-for-module-select" data-template-id="' + template.id + '" style="min-width: 150px;">';
                    selectHtml += '<option value="">-- Select --</option>';

                    const options = ['Birthday', 'Complete order', 'Pending order', 'Return order'];
                    options.forEach(function(option) {
                        // Show option if it's available or if it's the current template's value
                        const isAvailable = option === currentValue || !usedOptions.includes(option);
                        const selected = option === currentValue ? 'selected' : '';
                        const disabled = !isAvailable ? 'disabled' : '';
                        const label = !isAvailable ? option + ' (Already assigned)' : option;
                        selectHtml += '<option value="' + escapeHtml(option) + '" ' + selected + ' ' + disabled + '>' + escapeHtml(label) + '</option>';
                    });

                    selectHtml += '</select>';

                    // Toggle button for Details column
                    const detailsToggle = `
                        <button class="whatsapp-toggle-btn-table" onclick="toggleWhatsAppTemplateRowDetails('${template.id}')" data-template-id="${template.id}">
                            <span class="toggle-icon">+</span>
                        </button>
                    `;

                    return {
                        name: template.name || 'N/A',
                        use_for_template: selectHtml,
                        status: statusBadge,
                        on_off: onOffBadge,
                        details: detailsToggle
                    };
                });

                templatesTable.clear().rows.add(tableData).draw();
            }

            // Refresh templates button
            $("#btn-refresh-templates").on("click", function() {
                loadMessageTemplates();
            });

            // Store configuration status
            var hasConfiguration = false;

            // Update configuration status when loading config
            function updateConfigurationStatus(config) {
                hasConfiguration = config && config.id;
            }

            // Load templates when templates tab is clicked
            $(document).on('shown.bs.tab', '#templates-tab', function() {
                // Check if configuration exists before loading templates
                const configId = $("#configId").val();
                if (!configId || configId === '') {
                    $("#templatesLoading").hide();
                    $("#templatesError").hide();
                    $("#templatesList").html(
                        '<div class="alert alert-warning">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' +
                        '<strong>Configuration Required:</strong> ' +
                        'Please save your WhatsApp App Configuration in the Configuration tab first before viewing message templates.' +
                        '</div>'
                    );
                    return;
                }
                loadMessageTemplates();
            });

            // Store all templates data for checking used options
            var allTemplatesData = [];

            // Handle use_for_module select change directly in table
            $(document).on('change', '.use-for-module-select', function() {
                const templateId = $(this).data('template-id');
                const useForTemplate = $(this).val();
                const selectElement = $(this);

                // Disable select while updating
                selectElement.prop('disabled', true);

                updateTemplateUseFor(templateId, useForTemplate, selectElement);
            });

            // Update template use_for_template
            function updateTemplateUseFor(templateId, useForTemplate, selectElement) {
                const branchId = getCurrentBranchId();

                $.ajax({
                    url: `/api/facebook-app-configurations/templates/${templateId}/update-use-for`,
                    type: "PUT",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json"
                    },
                    data: JSON.stringify({
                        use_for_template: useForTemplate,
                        branch_id: branchId,
                        selectedSubAdminId: branchId || ''
                    }),
                    success: function(response) {
                        if (response.status) {
                            // Reload templates to refresh all dropdowns
                            loadStoredTemplates(branchId);
                        }
                        if (selectElement) {
                            selectElement.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        if (selectElement) {
                            selectElement.prop('disabled', false);
                            // Revert to previous value on error
                            loadStoredTemplates(branchId);
                        }
                        let errorMessage = "Failed to update template.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire("Error!", errorMessage, "error");
                    }
                });
            }

            // Handle toggle on_off badge click - only for APPROVED templates
            $(document).on('click', '.toggle-on-off', function() {
                const templateStatus = $(this).data('status');

                // Only allow toggle if status is APPROVED
                if (templateStatus !== 'APPROVED') {
                    Swal.fire({
                        title: "Not Allowed!",
                        text: "Only APPROVED templates can be toggled to active/inactive.",
                        icon: "warning",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#ff9f43"
                    });
                    return;
                }

                const templateId = $(this).data('template-id');
                const currentStatus = $(this).data('current-status');
                const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                const branchId = getCurrentBranchId();
                const badge = $(this);

                badge.prop('disabled', true);

                $.ajax({
                    url: `/api/facebook-app-configurations/templates/${templateId}/toggle-status`,
                    type: "PUT",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json"
                    },
                    data: JSON.stringify({
                        on_off: newStatus,
                        branch_id: branchId,
                        selectedSubAdminId: branchId || ''
                    }),
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                                timer: 2000,
                                showConfirmButton: false
                            });
                            // Reload templates
                            loadStoredTemplates(branchId);
                        }
                        badge.prop('disabled', false);
                    },
                    error: function(xhr) {
                        badge.prop('disabled', false);
                        let errorMessage = "Failed to update template status.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire("Error!", errorMessage, "error");
                    }
                });
            });

            // Handle template checkbox changes - toggle on_off status
            $(document).on('change', '.template-checkbox', function() {
                const templateId = $(this).data('template-id');
                const templateName = $(this).data('template-name');
                const isChecked = $(this).is(':checked');
                const branchId = getCurrentBranchId();
                const checkbox = $(this);
                const templateItem = checkbox.closest('.template-item');

                // Disable checkbox while updating
                checkbox.prop('disabled', true);

                // Determine on_off value
                const onOffValue = isChecked ? 'active' : 'inactive';

                // Call API to toggle status
                $.ajax({
                    url: `/api/facebook-app-configurations/templates/${templateId}/toggle-status`,
                    type: "PUT",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                        "Content-Type": "application/json"
                    },
                    data: JSON.stringify({
                        on_off: onOffValue,
                        branch_id: branchId,
                        selectedSubAdminId: branchId || ''
                    }),
                    success: function(response) {
                        if (response.status) {
                            // Update visual state
                            if (isChecked) {
                                templateItem.addClass('active-template');
                                checkbox.next('label').text('Active');
                            } else {
                                templateItem.removeClass('active-template');
                                checkbox.next('label').text('Inactive');
                            }

                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                        checkbox.prop('disabled', false);
                    },
                    error: function(xhr) {
                        // Revert checkbox state on error
                        checkbox.prop('checked', !isChecked);
                        checkbox.prop('disabled', false);

                        let errorMessage = "Failed to update template status.";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire("Error!", errorMessage, "error");
                    }
                });
            });

            // Initialize - load configuration for current branch
            // Wait a bit for header branch selector to be initialized
            setTimeout(function() {
                loadConfiguration();
            }, 500);

            // Resize handler for responsive behavior
            let whatsappResizeTimer;
            let lastWhatsappWidth = $(window).width();

            function forceWhatsAppCSSRecalculation() {
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

            function handleWhatsAppResize() {
                clearTimeout(whatsappResizeTimer);
                whatsappResizeTimer = setTimeout(function() {
                    const currentWidth = $(window).width();
                    lastWhatsappWidth = currentWidth;

                    // Force CSS recalculation
                    forceWhatsAppCSSRecalculation();

                    const templatesTableEl = document.getElementById('templatesTable');
                    const tableResponsive = document.querySelectorAll('.table-responsive');

                    [templatesTableEl, ...tableResponsive].forEach(function(el) {
                        if (el) {
                            void el.offsetHeight;
                            void el.offsetWidth;
                            el.style.display = 'none';
                            void el.offsetHeight;
                            el.style.display = '';
                        }
                    });

                    // Adjust DataTable columns if table exists
                    if (templatesTable) {
                        templatesTable.columns.adjust().draw();
                    }

                    forceWhatsAppCSSRecalculation();
                }, 50);
            }

            // Window resize handler
            $(window).off('resize.whatsapp').on('resize.whatsapp', handleWhatsAppResize);

            if (window.whatsappResizeHandler) {
                window.removeEventListener('resize', window.whatsappResizeHandler);
            }
            window.whatsappResizeHandler = handleWhatsAppResize;
            window.addEventListener('resize', window.whatsappResizeHandler, { passive: true });

            // Orientation change handler
            $(window).off('orientationchange.whatsapp').on('orientationchange.whatsapp', function() {
                setTimeout(function() {
                    lastWhatsappWidth = $(window).width();
                    handleWhatsAppResize();
                }, 300);
            });

            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    lastWhatsappWidth = $(window).width();
                    handleWhatsAppResize();
                }, 500);
            });

            // MatchMedia listeners for breakpoint changes
            const whatsappQueries = [
                window.matchMedia('(max-width: 575.98px)'),
                window.matchMedia('(min-width: 576px) and (max-width: 767.98px)'),
                window.matchMedia('(min-width: 768px) and (max-width: 1024px)'),
                window.matchMedia('(min-width: 1025px)')
            ];

            whatsappQueries.forEach(function(query) {
                if (query.addEventListener) {
                    query.addEventListener('change', function() {
                        setTimeout(handleWhatsAppResize, 100);
                    });
                } else if (query.addListener) {
                    query.addListener(function() {
                        setTimeout(handleWhatsAppResize, 100);
                    });
                }
            });

            // Initial width set and call
            lastWhatsappWidth = $(window).width();

            $(window).on('load', function() {
                setTimeout(function() {
                    lastWhatsappWidth = $(window).width();
                    handleWhatsAppResize();
                }, 500);
            });

            window.handleWhatsAppResize = handleWhatsAppResize;
        });
    </script>
@endpush
