@extends('layout.app')

@php
    $editId = $id ?? null;
@endphp

@section('title', $editId ? 'Edit BOM' : 'Create BOM')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>{{ $editId ? 'Edit BOM' : 'Create BOM' }}</h4>
                <h6>{{ $editId ? 'Update the raw materials needed for a finished product' : 'Define the raw materials needed for a finished product' }}</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('inventory.bom.list') }}" class="btn btn-secondary">
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
                            <select id="product_id" class="form-control select2">
                                <option value="">Select Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }}{{ $product->unit ? ' (' . $product->unit->unit_name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Base Qty</label>
                            <input type="number" id="base_quantity" class="form-control" min="0.001" step="0.001" value="1">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Wastage %</label>
                            <input type="number" id="wastage_percentage" class="form-control" min="0" step="0.01" value="0">
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label>Status</label>
                            <select id="bom_status" class="form-control">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-2 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" id="add-material-row">Add Material</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="bom_notes" class="form-control" rows="3" placeholder="Optional recipe notes"></textarea>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Raw Material</th>
                                <th>Qty for Base Batch</th>
                                <th>Unit</th>
                                <th>Notes</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="bom-item-table"></tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="button" class="btn btn-submit btn-primary" id="save-bom">{{ $editId ? 'Update BOM' : 'Save BOM' }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const bomId = @json($editId);
            const isEditMode = !!bomId;
            const materials = {!! json_encode(array_map(function($m) {
                return [
                    'id' => $m['id'],
                    'name' => $m['row_materialname'],
                    'unit_id' => $m['unit_id'],
                    'unit_name' => isset($m['unit']['unit_name']) ? $m['unit']['unit_name'] : null,
                ];
            }, $materials->toArray())) !!};
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');

            function materialOptions() {
                return materials.map(function(material) {
                    return `<option value="${material.id}" data-unit-id="${material.unit_id || ''}" data-unit-name="${material.unit_name || ''}">${material.name}</option>`;
                }).join('');
            }

            function addRow(item = null) {
                $('#bom-item-table').append(`
                    <tr>
                        <td>
                            <select class="form-control material-select">
                                <option value="">Select Material</option>
                                ${materialOptions()}
                            </select>
                        </td>
                        <td><input type="number" class="form-control item-qty" min="0.001" step="0.001" value="1"></td>
                        <td><input type="text" class="form-control item-unit" readonly></td>
                        <td><input type="text" class="form-control item-notes" placeholder="Optional"></td>
                        <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                    </tr>
                `);

                const row = $('#bom-item-table tr:last');
                if (item) {
                    row.find('.material-select').val(item.raw_material_id);
                    row.find('.item-qty').val(item.qty);
                    row.find('.item-notes').val(item.notes || '');
                    row.find('.material-select').trigger('change');
                }
            }

            addRow();

            $('#add-material-row').on('click', function() {
                addRow();
            });

            $(document).on('change', '.material-select', function() {
                const option = $(this).find(':selected');
                $(this).closest('tr').find('.item-unit').val(option.data('unit-name') || '');
            });

            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            $('#save-bom').on('click', function() {
                const items = [];

                $('#bom-item-table tr').each(function() {
                    const materialId = $(this).find('.material-select').val();
                    const unitId = $(this).find('.material-select option:selected').data('unit-id') || null;
                    const qty = $(this).find('.item-qty').val();
                    const notes = $(this).find('.item-notes').val();

                    if (materialId && qty) {
                        items.push({
                            raw_material_id: materialId,
                            unit_id: unitId,
                            qty: qty,
                            notes: notes
                        });
                    }
                });

                $.ajax({
                    url: isEditMode ? `/api/manufacturing/boms/${bomId}` : '/api/manufacturing/boms',
                    type: isEditMode ? 'PUT' : 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        Authorization: 'Bearer ' + authToken
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        selectedSubAdminId: selectedSubAdminId,
                        product_id: $('#product_id').val(),
                        base_quantity: $('#base_quantity').val(),
                        wastage_percentage: $('#wastage_percentage').val(),
                        status: $('#bom_status').val(),
                        notes: $('#bom_notes').val(),
                        items: items
                    }),
                    success: function(response) {
                        Swal.fire({
                            title: isEditMode ? 'Updated' : 'Saved',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(function() {
                            window.location.href = '{{ route('inventory.bom.list') }}';
                        });
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || (isEditMode ? 'Failed to update BOM.' : 'Failed to save BOM.');
                        Swal.fire('Error', message, 'error');
                    }
                });
            });

            if (isEditMode) {
                $.ajax({
                    url: `/api/manufacturing/boms/${bomId}`,
                    type: 'GET',
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        const bom = response.data;
                        $('#product_id').val(bom.product_id).trigger('change');
                        $('#base_quantity').val(bom.base_quantity);
                        $('#wastage_percentage').val(bom.wastage_percentage || 0);
                        $('#bom_status').val(bom.status);
                        $('#bom_notes').val(bom.notes || '');
                        $('#bom-item-table').empty();

                        (bom.items || []).forEach(function(item) {
                            addRow({
                                raw_material_id: item.raw_material_id,
                                qty: item.qty,
                                notes: item.notes
                            });
                        });

                        if (!(bom.items || []).length) {
                            addRow();
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Failed to load BOM details.', 'error');
                    }
                });
            }
        });
    </script>
@endpush
