@extends('layout.app')

@section('title', 'Row Material Purchases')

@section('content')
    <div class="content">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div class="page-title">
                <h4>Row Material Purchases</h4>
            </div>
            <div>
                <a href="{{ route('purchase.row_material.add') }}" class="btn btn-added">
                    <i class="fa fa-plus me-2"></i>New Row Material Purchase
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <input type="text" id="search-input" class="form-control" placeholder="Search by invoice, vendor, material...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Sr No</th>
                                <th>Invoice No</th>
                                <th>Vendor</th>
                                <th>Row Materials</th>
                                <th>Grand Total</th>
                                <th>Remaining</th>
                                <th>Purchase Status</th>
                                <th>Payment Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="row-material-purchase-table-body">
                            <tr>
                                <td colspan="10" class="text-center text-muted">Loading row material purchases...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div id="row-material-purchase-summary" class="text-muted"></div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-secondary" id="prev-page">Prev</button>
                        <button type="button" class="btn btn-sm btn-secondary" id="next-page">Next</button>
                    </div>
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
            let currentPage = 1;
            let lastPage = 1;

            function statusBadge(status) {
                const value = (status || '').toLowerCase();
                let badgeClass = 'bg-secondary';

                if (value === 'completed') badgeClass = 'bg-lightgreen';
                if (value === 'pending') badgeClass = 'bg-lightred';
                if (value === 'partially' || value === 'partial') badgeClass = 'bg-lightyellow';

                return `<span class="badges ${badgeClass}">${status || '-'}</span>`;
            }

            function loadRowMaterialPurchases() {
                $.ajax({
                    url: '/api/row-material-purchase-list',
                    type: 'GET',
                    data: {
                        page: currentPage,
                        per_page: 10,
                        search: $('#search-input').val(),
                        selectedSubAdminId: selectedSubAdminId
                    },
                    headers: {
                        Authorization: 'Bearer ' + authToken
                    },
                    success: function(response) {
                        const rows = (response.data || []).map(function(item, index) {
                            return `
                                <tr>
                                    <td>${((currentPage - 1) * 10) + index + 1}</td>
                                    <td>${item.invoice_number || '-'}</td>
                                    <td>${item.vendor_name || '-'}</td>
                                    <td>${item.material_names || '-'}</td>
                                    <td>${parseFloat(item.grand_total || 0).toFixed(2)}</td>
                                    <td>${parseFloat(item.remaining_amount || 0).toFixed(2)}</td>
                                    <td>${statusBadge(item.purchase_status)}</td>
                                    <td>${statusBadge(item.payment_status)}</td>
                                    <td>${item.date || '-'}</td>
                                    <td>
                                        <a href="/view-row-material-purchase/${item.id}" class="me-2" title="View">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/eye.svg' }}" alt="view">
                                        </a>
                                        <a href="/edit-row-material-purchase/${item.id}" class="me-2" title="Edit">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/edit.svg' }}" alt="edit">
                                        </a>
                                        <a href="javascript:void(0);" class="delete-row-material-purchase" data-id="${item.id}" title="Delete">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/delete.svg' }}" alt="delete">
                                        </a>
                                    </td>
                                </tr>
                            `;
                        }).join('');

                        $('#row-material-purchase-table-body').html(rows || '<tr><td colspan="10" class="text-center text-muted">No row material purchases found.</td></tr>');

                        currentPage = response.pagination?.current_page || 1;
                        lastPage = response.pagination?.last_page || 1;
                        $('#row-material-purchase-summary').text(`Page ${currentPage} of ${lastPage}`);
                        $('#prev-page').prop('disabled', currentPage <= 1);
                        $('#next-page').prop('disabled', currentPage >= lastPage);
                    },
                    error: function() {
                        $('#row-material-purchase-table-body').html('<tr><td colspan="10" class="text-center text-danger">Failed to load row material purchases.</td></tr>');
                    }
                });
            }

            $(document).on('click', '.delete-row-material-purchase', function() {
                const purchaseId = $(this).data('id');

                Swal.fire({
                    title: 'Delete row material purchase?',
                    text: 'This will reverse the purchased stock if it has not already been consumed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        url: `/api/row-material-purchase/${purchaseId}`,
                        type: 'DELETE',
                        data: {
                            selectedSubAdminId: selectedSubAdminId
                        },
                        headers: {
                            Authorization: 'Bearer ' + authToken
                        },
                        success: function(response) {
                            Swal.fire('Deleted', response.message || 'Row material purchase deleted successfully.', 'success');
                            loadRowMaterialPurchases();
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete row material purchase.', 'error');
                        }
                    });
                });
            });

            $('#search-input').on('input', function() {
                currentPage = 1;
                loadRowMaterialPurchases();
            });

            $('#prev-page').on('click', function() {
                if (currentPage > 1) {
                    currentPage--;
                    loadRowMaterialPurchases();
                }
            });

            $('#next-page').on('click', function() {
                if (currentPage < lastPage) {
                    currentPage++;
                    loadRowMaterialPurchases();
                }
            });

            loadRowMaterialPurchases();
        });
    </script>
@endpush
