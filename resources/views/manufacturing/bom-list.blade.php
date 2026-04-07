@extends('layout.app')

@section('title', 'Bill of Materials')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Bill of Materials</h4>
                <h6>Link finished goods with raw material recipes</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('inventory.bom.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-2"></i>Create BOM
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>BOM Code</th>
                                <th>Finished Product</th>
                                <th>Base Qty</th>
                                <th>Unit</th>
                                <th>Materials</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="bom-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted">Loading BOMs...</td>
                            </tr>
                        </tbody>
                    </table>
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

            $.ajax({
                url: '/api/manufacturing/boms',
                type: 'GET',
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    Authorization: 'Bearer ' + authToken
                },
                success: function(response) {
                    const rows = (response.data || []).map(function(bom) {
                        return `
                            <tr>
                                <td>${bom.bom_code}</td>
                                <td>${bom.product?.name || 'N/A'}</td>
                                <td>${parseFloat(bom.base_quantity).toFixed(3)}</td>
                                <td>${bom.product?.unit?.unit_name || '-'}</td>
                                <td>${bom.items_count || 0}</td>
                                <td><span class="badges ${bom.status === 'active' ? 'bg-lightgreen' : 'bg-lightred'}">${bom.status}</span></td>
                                <td>
                                    <a href="/inventory/boms/${bom.id}/view" class="me-3" title="View">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                                    </a>
                                    <a href="/inventory/boms/${bom.id}/edit" title="Edit">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                    </a>
                                </td>
                            </tr>
                        `;
                    }).join('');

                    $('#bom-table-body').html(rows || '<tr><td colspan="7" class="text-center text-muted">No BOMs found.</td></tr>');
                },
                error: function() {
                    $('#bom-table-body').html('<tr><td colspan="7" class="text-center text-danger">Failed to load BOMs.</td></tr>');
                }
            });
        });
    </script>
@endpush
