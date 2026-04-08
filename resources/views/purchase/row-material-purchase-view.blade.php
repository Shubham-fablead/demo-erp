@extends('layout.app')

@section('title', 'View Row Material Purchase')

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Row Material Purchase Details</h4>
                <h6>Review vendor, invoice, and purchased row materials</h6>
            </div>
            <div class="page-btn d-flex gap-2">
                <a href="{{ route('purchase.row_material.edit', $id) }}" class="btn btn-added">
                    <i class="fa fa-edit me-2"></i>Edit
                </a>
                <a href="{{ route('purchase.row_material.lists') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body" id="row-material-purchase-view">
                <div class="text-center text-muted">Loading purchase details...</div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(function() {
            const authToken = localStorage.getItem('authToken');
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            const purchaseId = @json($id);

            $.ajax({
                url: `/api/row-material-purchase/${purchaseId}`,
                type: 'GET',
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                headers: {
                    Authorization: 'Bearer ' + authToken
                },
                success: function(response) {
                    const data = response.data || {};
                    const invoice = data.invoice || {};
                    const vendor = data.vendor || {};
                    const items = data.items || [];

                    const rows = items.map(function(item, index) {
                        return `
                            <tr>
                                <td>${index + 1}</td>
                                <td>${item.row_material_name || '-'}</td>
                                <td>${parseFloat(item.quantity || 0).toFixed(2)}</td>
                                <td>${parseFloat(item.price || 0).toFixed(2)}</td>
                                <td>${parseFloat(item.discount_percent || 0).toFixed(2)}%</td>
                                <td>${parseFloat(item.discount_amount || 0).toFixed(2)}</td>
                                <td>${parseFloat(item.amount_total || 0).toFixed(2)}</td>
                            </tr>
                        `;
                    }).join('');

                    $('#row-material-purchase-view').html(`
                        <div class="row g-3 mb-4">
                            <div class="col-md-4"><strong>Invoice No:</strong> ${invoice.invoice_number || '-'}</div>
                            <div class="col-md-4"><strong>Bill No:</strong> ${invoice.bill_no || '-'}</div>
                            <div class="col-md-4"><strong>Date:</strong> ${invoice.created_at || '-'}</div>
                            <div class="col-md-4"><strong>Vendor:</strong> ${vendor.name || '-'}</div>
                            <div class="col-md-4"><strong>Phone:</strong> ${vendor.phone || '-'}</div>
                            <div class="col-md-4"><strong>Status:</strong> ${invoice.status || '-'}</div>
                            <div class="col-md-4"><strong>Grand Total:</strong> ${parseFloat(invoice.grand_total || 0).toFixed(2)}</div>
                            <div class="col-md-4"><strong>Remaining:</strong> ${parseFloat(invoice.remaining_amount || 0).toFixed(2)}</div>
                            <div class="col-md-4"><strong>Payment Status:</strong> ${data.payment_status || '-'}</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr No</th>
                                        <th>Row Material</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Disc %</th>
                                        <th>Disc Amt</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${rows || '<tr><td colspan="7" class="text-center text-muted">No items found.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    `);
                },
                error: function(xhr) {
                    $('#row-material-purchase-view').html(`<div class="text-center text-danger">${xhr.responseJSON?.message || 'Failed to load purchase details.'}</div>`);
                }
            });
        });
    </script>
@endpush
