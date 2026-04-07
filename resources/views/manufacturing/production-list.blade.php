@extends('layout.app')

@section('title', 'Production')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Production</h4>
                <h6>Convert raw materials into finished goods</h6>
            </div>
            <div class="page-btn">
                <a href="{{ route('inventory.production.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-2"></i>New Production
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Production No</th>
                                <th>Product</th>
                                <th>BOM</th>
                                <th>Production Qty</th>
                                <th>Output Qty</th>
                                <th>Total Cost</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="production-table-body">
                            <tr>
                                <td colspan="8" class="text-center text-muted">Loading productions...</td>
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
                url: '/api/manufacturing/productions',
                type: 'GET',
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    Authorization: 'Bearer ' + authToken
                },
                success: function(response) {
                    const rows = (response.data || []).map(function(item) {
                        return `
                            <tr>
                                <td>${item.production_no}</td>
                                <td>${item.product?.name || 'N/A'}</td>
                                <td>${item.bom?.bom_code || '-'}</td>
                                <td>${parseFloat(item.production_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                                <td>${parseFloat(item.output_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                                <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                                <td><span class="badges ${item.status === 'completed' ? 'bg-lightgreen' : 'bg-lightyellow'}">${item.status}</span></td>
                                <td>${item.production_date}</td>
                            </tr>
                        `;
                    }).join('');

                    $('#production-table-body').html(rows || '<tr><td colspan="8" class="text-center text-muted">No production records found.</td></tr>');
                },
                error: function() {
                    $('#production-table-body').html('<tr><td colspan="8" class="text-center text-danger">Failed to load production records.</td></tr>');
                }
            });
        });
    </script>
@endpush
