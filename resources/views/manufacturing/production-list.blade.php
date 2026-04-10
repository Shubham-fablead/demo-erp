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
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="production-table-body">
                            <tr>
                                <td colspan="9" class="text-center text-muted">Loading productions...</td>
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

            function loadProductions() {
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
                            const statusClass = {
                                'completed': 'bg-lightgreen',
                                'in_production': 'bg-lightpurple',
                                'draft': 'bg-lightyellow'
                            }[item.status] || 'bg-lightyellow';

                            const statusLabel = {
                                'completed': 'Completed',
                                'in_production': 'In Production',
                                'draft': 'Draft'
                            }[item.status] || item.status;

                            const editAction = item.status === 'draft'
                                ? `<a href="/inventory/productions/${item.id}/edit" class="me-3" title="Edit">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                   </a>`
                                : (item.status === 'in_production'
                                    ? `<a href="/inventory/productions/${item.id}/edit" class="me-3" title="Complete Production">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                       </a>`
                                    : '');
                            const deleteAction = item.status === 'draft'
                                ? `<a href="javascript:void(0);" class="delete-production me-3" data-id="${item.id}" title="Delete">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="delete">
                                   </a>`
                                : '';

                            return `
                                <tr>
                                    <td>${item.production_no}</td>
                                    <td>${item.product?.name || 'N/A'}</td>
                                    <td>${item.bom?.bom_code || '-'}</td>
                                    <td>${parseFloat(item.production_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                                    <td>${parseFloat(item.output_qty).toFixed(3)} ${item.product?.unit?.unit_name || ''}</td>
                                    <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                                    <td><span class="badges ${statusClass}">${statusLabel}</span></td>
                                    <td>${item.production_date}</td>
                                    <td>
                                        ${editAction}
                                        ${deleteAction}
                                        <a href="/inventory/productions/${item.id}" title="View">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                                        </a>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $('#production-table-body').html(rows || '<tr><td colspan="9" class="text-center text-muted">No production records found.</td></tr>');
                    },
                    error: function() {
                        $('#production-table-body').html('<tr><td colspan="9" class="text-center text-danger">Failed to load production records.</td></tr>');
                    }
                });
            }

            $(document).on('click', '.delete-production', function() {
                const productionId = $(this).data('id');

                Swal.fire({
                    title: 'Delete production?',
                    text: 'This will permanently remove the draft production record.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/manufacturing/productions/${productionId}`,
                        type: 'DELETE',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'Production deleted successfully.', 'success');
                            loadProductions();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete production.', 'error');
                        }
                    });
                });
            });

            loadProductions();
        });
    </script>
@endpush
