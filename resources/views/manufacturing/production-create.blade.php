@extends('layout.app')

@php
    $editId = $id ?? null;
@endphp

@section('title', $editId ? 'Edit Production' : 'New Production')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>{{ $editId ? 'Edit Production' : 'New Production' }}</h4>
                <h6>Automatic BOM-based material planning and costing</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('inventory.production.list') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Finished Product</label>
                            <select id="product_id" class="form-control select2"></select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Production Qty</label>
                            <input type="number" id="production_qty" class="form-control" min="0.001" step="0.001" value="1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Unit</label>
                            <input type="text" id="product_unit" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Production Date</label>
                            <input type="date" id="production_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Wastage %</label>
                            <input type="number" id="wastage_percentage" class="form-control" min="0" max="100" step="0.01" value="0" placeholder="0">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select id="production_status" class="form-control">
                                <option value="completed" {{ $editId ? '' : 'selected' }}>Completed</option>
                                <option value="in_production">In Production</option>
                                <option value="draft" {{ $editId ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Labor Cost</label>
                            <input type="number" id="labor_cost" class="form-control" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Electricity Cost</label>
                            <input type="number" id="electricity_cost" class="form-control" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Other Cost</label>
                            <input type="number" id="extra_cost" class="form-control" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Batch No</label>
                            <input type="text" id="batch_no" class="form-control" placeholder="Optional">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label>Expiry Date</label>
                            <input type="date" id="expiry_date" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Production Notes</label>
                    <textarea id="notes" class="form-control" rows="3" placeholder="Optional notes"></textarea>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="preview-production">Preview Calculation</button>
                </div>

                <div class="row" id="summary-cards" style="display: none;">
                    <div class="col-lg-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Material Cost</h6>
                                <h4 id="material_cost">0.00</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Wastage Qty</h6>
                                <h4 id="wastage_qty">0.000</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Output Qty</h6>
                                <h4 id="output_qty">0.000</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6>Cost / Unit</h6>
                                <h4 id="cost_per_unit">0.0000</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Raw Material</th>
                                <th>Required Qty</th>
                                <th>Available Stock</th>
                                <th>Rate</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="preview-items">
                            <tr>
                                <td colspan="6" class="text-center text-muted">Choose a product and preview the BOM.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-submit btn-primary" id="save-production">{{ $editId ? 'Update Draft' : 'Save Production' }}</button>
                    <small class="text-muted d-block mt-2" id="production-status-note">Completed production will consume raw materials and update inventory stock.</small>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const productionId = @json($editId);
            const isEditMode = !!productionId;
            let currentPreview = null;

            function extraCostTotal() {
                return (parseFloat($('#labor_cost').val()) || 0) +
                    (parseFloat($('#electricity_cost').val()) || 0) +
                    (parseFloat($('#extra_cost').val()) || 0);
            }

            function renderSummary(preview) {
                const totalCost = (parseFloat(preview.material_cost) || 0) + extraCostTotal();
                const cpu = parseFloat(preview.output_qty) > 0 ? (totalCost / parseFloat(preview.output_qty)) : 0;

                $('#summary-cards').show();
                $('#material_cost').text(totalCost.toFixed(2));
                $('#wastage_qty').text(parseFloat(preview.wastage_qty).toFixed(3));
                $('#output_qty').text(parseFloat(preview.output_qty).toFixed(3));
                $('#cost_per_unit').text(cpu.toFixed(4));
            }

            function syncProductionActionState() {
                const status = $('#production_status').val();
                const isCompleted = status === 'completed';
                const isInProduction = status === 'in_production';

                if (isEditMode) {
                    if (isCompleted) {
                        $('#save-production').text('Complete Production');
                        $('#production-status-note').text('Completing this production will add finished goods to product inventory.');
                    } else if (isInProduction) {
                        $('#save-production').text('Start Production');
                        $('#production-status-note').text('Starting production will consume raw materials from inventory. Finished goods will be added when completed.');
                    } else {
                        $('#save-production').text('Update Draft');
                        $('#production-status-note').text('Draft production is saved without changing material inventory stock.');
                    }
                    return;
                }

                if (isCompleted) {
                    $('#save-production').text('Save Production');
                    $('#production-status-note').text('Completed production will consume raw materials and update inventory stock.');
                } else if (isInProduction) {
                    $('#save-production').text('Start Production');
                    $('#production-status-note').text('Starting production will consume raw materials from inventory. Finished goods will be added when completed.');
                } else {
                    $('#save-production').text('Save Draft');
                    $('#production-status-note').text('Draft production is saved without changing material inventory stock.');
                }
            }

            function formatDate(value) {
                if (!value) return '';
                // Handle full ISO datetime strings like "2026-04-10T00:00:00.000000Z"
                return value.toString().substring(0, 10);
            }

            function fillForm(data) {
                // For select2: set value then trigger change for select2 to update display
                const $productSelect = $('#product_id');
                $productSelect.val(String(data.product_id)).trigger('change.select2');
                // Also update unit label from the selected option
                const selectedOption = $productSelect.find('option[value="' + data.product_id + '"]');
                $('#product_unit').val(selectedOption.data('unit') || '');

                $('#production_qty').val(data.production_qty);
                $('#production_date').val(formatDate(data.production_date));
                $('#wastage_percentage').val(data.wastage_percentage ?? 0);
                $('#production_status').val(data.status || 'draft');
                $('#labor_cost').val(data.labor_cost || 0);
                $('#electricity_cost').val(data.electricity_cost || 0);
                $('#extra_cost').val(data.extra_cost || 0);
                $('#batch_no').val(data.batch_no || '');
                $('#expiry_date').val(formatDate(data.expiry_date));
                $('#notes').val(data.notes || '');
                syncProductionActionState();
                fetchPreview();
            }

            function loadProducts(callback = null) {
                $.ajax({
                    url: '/api/manufacturing/products',
                    type: 'GET',
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        const options = ['<option value="">Select Product</option>'];

                        (response.data || []).forEach(function(product) {
                            options.push(`<option value="${product.id}" data-unit="${product.unit?.unit_name || ''}">${product.name}</option>`);
                        });

                        $('#product_id').html(options.join(''));

                        if (typeof callback === 'function') {
                            callback();
                        }
                    }
                });
            }

            function fetchPreview() {
                $.ajax({
                    url: '/api/manufacturing/production-preview',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        Authorization: 'Bearer ' + authToken
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        selectedSubAdminId: selectedSubAdminId,
                        product_id: $('#product_id').val(),
                        production_qty: $('#production_qty').val(),
                        wastage_percentage: $('#wastage_percentage').val()
                    }),
                    success: function(response) {
                        currentPreview = response.data;
                        renderSummary(response.data);

                        // Show BOM default wastage as placeholder hint
                        const bomWastage = response.data.bom?.wastage_percentage ?? 0;
                        $('#wastage_percentage').attr('placeholder', 'BOM default: ' + bomWastage);

                        const rows = (response.data.items || []).map(function(item) {
                            return `
                                <tr>
                                    <td>${item.name}</td>
                                    <td>${parseFloat(item.required_qty).toFixed(3)} ${item.unit_name || ''}</td>
                                    <td>${parseFloat(item.available_stock).toFixed(3)} ${item.unit_name || ''}</td>
                                    <td>${parseFloat(item.rate).toFixed(2)}</td>
                                    <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                                    <td><span class="badges ${item.has_stock ? 'bg-lightgreen' : 'bg-lightred'}">${item.has_stock ? 'OK' : 'Insufficient stock'}</span></td>
                                </tr>
                            `;
                        }).join('');

                        $('#preview-items').html(rows || '<tr><td colspan="6" class="text-center text-muted">No materials found in BOM.</td></tr>');
                    },
                    error: function(xhr) {
                        currentPreview = null;
                        $('#summary-cards').hide();
                        $('#preview-items').html('<tr><td colspan="6" class="text-center text-danger">' + (xhr.responseJSON?.message || 'Preview failed.') + '</td></tr>');
                    }
                });
            }

            loadProducts(function() {
                if (isEditMode) {
                    $.ajax({
                        url: `/api/manufacturing/productions/${productionId}`,
                        type: 'GET',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            const item = response.data;
                            if (item.status === 'completed') {
                                Swal.fire('Info', 'Completed production cannot be edited. You can only view it.', 'info').then(function() {
                                    window.location.href = `/inventory/productions/${productionId}`;
                                });
                                return;
                            }
                            // Disable status options that would be a downgrade for in_production
                            if (item.status === 'in_production') {
                                $('#production_status option[value="draft"]').prop('disabled', true);
                            }
                            fillForm(item);
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to load production details.', 'error');
                        }
                    });
                }
            });

            $('#product_id').on('change', function() {
                const unit = $(this).find('option:selected').data('unit') || '';
                $('#product_unit').val(unit);
            });

            $('#preview-production').on('click', fetchPreview);
            $('#production_status').on('change', syncProductionActionState);
            $('#wastage_percentage').on('input', fetchPreview);
            $('#labor_cost, #electricity_cost, #extra_cost').on('input', function() {
                if (currentPreview) {
                    renderSummary(currentPreview);
                }
            });

            $('#save-production').on('click', function() {
                $.ajax({
                    url: isEditMode ? `/api/manufacturing/productions/${productionId}` : '/api/manufacturing/productions',
                    type: isEditMode ? 'PUT' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        Authorization: 'Bearer ' + authToken
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        selectedSubAdminId: selectedSubAdminId,
                        product_id: $('#product_id').val(),
                        production_qty: $('#production_qty').val(),
                        production_date: $('#production_date').val(),
                        status: $('#production_status').val(),
                        wastage_percentage: $('#wastage_percentage').val(),
                        labor_cost: $('#labor_cost').val(),
                        electricity_cost: $('#electricity_cost').val(),
                        extra_cost: $('#extra_cost').val(),
                        batch_no: $('#batch_no').val(),
                        expiry_date: $('#expiry_date').val(),
                        notes: $('#notes').val()
                    }),
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(function() {
                            window.location.href = '{{ route('inventory.production.list') }}';
                        });
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || (isEditMode ? 'Failed to update production.' : 'Failed to save production.'), 'error');
                    }
                });
            });

            syncProductionActionState();
        });
    </script>
@endpush
