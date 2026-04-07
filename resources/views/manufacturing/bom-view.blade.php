@extends('layout.app')

@section('title', 'BOM Details')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>BOM Details</h4>
                <h6>Review the finished product recipe and material requirements</h6>
            </div>
            <div class="page-btn d-flex gap-2">
                <a href="{{ route('inventory.bom.list') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Back
                </a>
                <a href="{{ route('inventory.bom.edit', $id) }}" class="btn btn-added">
                    <i class="fa fa-edit me-2"></i>Edit BOM
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" id="bom-details-wrapper">
                <div class="text-center text-muted">Loading BOM details...</div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const bomId = @json($id);

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
                    const item = response.data;
                    const rows = (item.items || []).map(function(material, index) {
                        return `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${material.raw_material?.row_materialname || '-'}</td>
                                <td>${parseFloat(material.qty || 0).toFixed(3)} ${material.unit?.unit_name || material.raw_material?.unit?.unit_name || ''}</td>
                                <td>${parseFloat(material.raw_material?.price || 0).toFixed(2)}</td>
                                <td>${material.notes || '-'}</td>
                            </tr>
                        `;
                    }).join('');

                    $('#bom-details-wrapper').html(`
                        <div class="row mb-4">
                            <div class="col-md-4"><strong>BOM Code:</strong> ${item.bom_code || '-'}</div>
                            <div class="col-md-4"><strong>Finished Product:</strong> ${item.product?.name || '-'}</div>
                            <div class="col-md-4"><strong>Status:</strong> <span class="badges ${item.status === 'active' ? 'bg-lightgreen' : 'bg-lightred'}">${item.status || '-'}</span></div>
                            <div class="col-md-4 mt-2"><strong>Base Qty:</strong> ${parseFloat(item.base_quantity || 0).toFixed(3)} ${item.product?.unit?.unit_name || ''}</div>
                            <div class="col-md-4 mt-2"><strong>Wastage %:</strong> ${parseFloat(item.wastage_percentage || 0).toFixed(2)}</div>
                            <div class="col-md-4 mt-2"><strong>Materials:</strong> ${(item.items || []).length}</div>
                            <div class="col-md-12 mt-2"><strong>Notes:</strong> ${item.notes || '-'}</div>
                        </div>

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Raw Material</th>
                                        <th>Required Qty</th>
                                        <th>Rate</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows || '<tr><td colspan="5" class="text-center text-muted">No materials found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    `);
                },
                error: function(xhr) {
                    $('#bom-details-wrapper').html(`<div class="text-center text-danger">${xhr.responseJSON?.message || 'Failed to load BOM details.'}</div>`);
                }
            });
        });
    </script>
@endpush
