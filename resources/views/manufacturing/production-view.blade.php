@extends('layout.app')

@section('title', 'Production Details')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Production Details</h4>
                <h6>Review production output, costs, and consumed materials</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('inventory.production.list') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" id="production-details-wrapper">
                <div class="text-center text-muted">Loading production details...</div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const productionId = @json($id);

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
                    const rows = (item.items || []).map(function(material, index) {
                        return `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${material.raw_material?.row_materialname || '-'}</td>
                                <td>${parseFloat(material.required_qty || 0).toFixed(3)} ${material.raw_material?.unit?.unit_name || ''}</td>
                                <td>${parseFloat(material.consume_qty || 0).toFixed(3)} ${material.raw_material?.unit?.unit_name || ''}</td>
                                <td>${parseFloat(material.rate || 0).toFixed(2)}</td>
                                <td>${parseFloat(material.total_cost || 0).toFixed(2)}</td>
                            </tr>
                        `;
                    }).join('');

                    $('#production-details-wrapper').html(`
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>Production No:</strong> ${item.production_no || '-'}</div>
                            <div class="col-md-4"><strong>Product:</strong> ${item.product?.name || '-'}</div>
                            <div class="col-md-4"><strong>BOM:</strong> ${item.bom?.bom_code || '-'}</div>
                            <div class="col-md-4 mt-2"><strong>Production Qty:</strong> ${parseFloat(item.production_qty || 0).toFixed(3)} ${item.product?.unit?.unit_name || ''}</div>
                            <div class="col-md-4 mt-2"><strong>Output Qty:</strong> ${parseFloat(item.output_qty || 0).toFixed(3)} ${item.product?.unit?.unit_name || ''}</div>
                            <div class="col-md-4 mt-2"><strong>Wastage Qty:</strong> ${parseFloat(item.wastage_qty || 0).toFixed(3)}</div>
                            <div class="col-md-4 mt-2"><strong>Total Cost:</strong> ${parseFloat(item.total_cost || 0).toFixed(2)}</div>
                            <div class="col-md-4 mt-2"><strong>Cost / Unit:</strong> ${parseFloat(item.cost_per_unit || 0).toFixed(4)}</div>
                            <div class="col-md-4 mt-2"><strong>Status:</strong> <span class="badges ${item.status === 'completed' ? 'bg-lightgreen' : 'bg-lightyellow'}">${item.status || '-'}</span></div>
                            <div class="col-md-4 mt-2"><strong>Production Date:</strong> ${item.production_date || '-'}</div>
                            <div class="col-md-4 mt-2"><strong>Batch No:</strong> ${item.batch_no || '-'}</div>
                            <div class="col-md-4 mt-2"><strong>Expiry Date:</strong> ${item.expiry_date || '-'}</div>
                            <div class="col-md-12 mt-2"><strong>Notes:</strong> ${item.notes || '-'}</div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Raw Material</th>
                                        <th>Required Qty</th>
                                        <th>Consumed Qty</th>
                                        <th>Rate</th>
                                        <th>Total Cost</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows || '<tr><td colspan="6" class="text-center text-muted">No consumed materials found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    `);
                },
                error: function(xhr) {
                    $('#production-details-wrapper').html(`<div class="text-center text-danger">${xhr.responseJSON?.message || 'Failed to load production details.'}</div>`);
                }
            });
        });
    </script>
@endpush
