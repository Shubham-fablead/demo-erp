@extends('layout.app')

@section('title', 'Edit Credit Note')
<style>
     a.btn.back-button {
    background: #ff9f43;
    color: #fff;
}
</style>
@section('content')
    <div class="content">
        {{-- <div class="page-header">
            <div class="page-title">
                <h4>Edit Credit Note</h4>
            </div>
        </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Edit Credit Note</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('credit-notes-items.index') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form action="{{ route('credit-notes-items.update', $creditNoteItem->id) }}" method="POST" id="credit-note-item-form">
                    @csrf
                    <input type="hidden" name="order_id" id="order_id" value="{{ $creditNoteItem->order_id }}">
                    <input type="hidden" name="purchase_id" id="purchase_id" value="{{ $creditNoteItem->purchase_id }}">
                    <input type="hidden" name="user_id" id="user_id" value="{{ $creditNoteItem->user_id }}">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Transaction Type</label>
                                <select name="transaction_type" id="transaction_type" class="form-control transaction_type-select2">
                                    <option value="receipt" {{ in_array($creditNoteItem->type_id, [1, 'receipt']) ? 'selected' : '' }}>Receipt</option>
                                    <option value="payment" {{ in_array($creditNoteItem->type_id, [2, 'payment']) ? 'selected' : '' }}>Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6" id="order_number_div" style="{{ in_array($creditNoteItem->type_id, [2, 'payment']) ? 'display: none;' : '' }}">
                            <div class="form-group">
                                <label>Order Number <span class="manitory">*</span></label>
                                <select name="order_number" id="order_number" class="form-control order_number-select2">
                                    <option value="">Select Order Number</option>
                                    @foreach($invoiceNumbers as $number)
                                        <option value="{{ $number }}" {{ ($creditNoteItem->order->order_number ?? '') == $number ? 'selected' : '' }}>{{ $number }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-order_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6" id="purchase_number_div" style="{{ in_array($creditNoteItem->type_id, [1, 'receipt']) ? 'display: none;' : '' }}">
                            <div class="form-group">
                                <label>Invoice Number <span class="manitory">*</span></label>
                                <select name="purchase_number" id="purchase_number" class="form-control purchase_number-select2">
                                    <option value="">Select Invoice Number</option>
                                    @foreach($purchaseInvoiceNumbers as $number)
                                        <option value="{{ $number }}" {{ ($creditNoteItem->purchaseInvoice->invoice_number ?? '') == $number ? 'selected' : '' }}>{{ $number }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-purchase_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label id="user_label">{{ in_array($creditNoteItem->type_id, [2, 'payment']) ? 'Vendor Name' : 'Customer Name' }}</label>
                                <input type="text" id="user_name" class="form-control" value="{{ in_array($creditNoteItem->type_id, [2, 'payment']) ? ($creditNoteItem->purchaseInvoice->vendor->name ?? 'N/A') : ($creditNoteItem->order->user->name ?? 'N/A') }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Credit Note Type <span class="manitory">*</span></label>
                                <select name="credite_note_id" id="credite_note_id" class="form-control credite_note_id-select2">
                                    <option value="">Select Type</option>
                                    @foreach($creditNoteTypes as $type)
                                        <option value="{{ $type->id }}" {{ $creditNoteItem->credite_note_id == $type->id ? 'selected' : '' }}>{{ $type->type_name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-credite_note_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <div class="input-group">
                                    <input type="text" name="total_amt" id="total_amt" class="form-control" value="{{ $creditNoteItem->total_amt }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Paid Amount</label>
                                <div class="input-group">
                                    <input type="text" name="total_paid" id="total_paid" class="form-control" value="{{ $creditNoteItem->total_paid }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Remaining Amount</label>
                                <div class="input-group">
                                    <input type="text" name="remaining_amt" id="remaining_amt" class="form-control" value="{{ $creditNoteItem->remaining_amt }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Settlement Amount <span class="manitory">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="settlement_amount" id="settlement_amount" class="form-control" step="0.01" value="{{ $creditNoteItem->settlement_amount }}">
                                </div>
                                <div class="text-danger error-settlement_amount"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Final Total</label>
                                <div class="input-group">
                                    <input type="number" name="total" id="total" class="form-control" step="0.01" value="{{ $creditNoteItem->total }}">
                                </div>
                                <div class="text-danger error-total"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Reason <span class="manitory">*</span></label>
                                <textarea name="reason" id="reason" class="form-control">{{ $creditNoteItem->reason }}</textarea>
                                <div class="text-danger error-reason"></div>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Update</button>
                            <a href="{{ route('credit-notes-items.index') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function () {

            var authToken = localStorage.getItem("authToken");
            const $submitBtn = $('#credit-note-item-form button[type="submit"]');
            const submitBtnDefaultHtml = $submitBtn.html();

            function toggleSubmitLoading(isLoading) {
                if (isLoading) {
                    $submitBtn
                        .prop('disabled', true)
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $submitBtn
                        .prop('disabled', false)
                        .html(submitBtnDefaultHtml);
                }
            }

            $('.credite_note_id-select2,.purchase_number-select2,.order_number-select2,.transaction_type-select2').select2({
                width: '100%'
            });

            $('#transaction_type').on('change', function() {
                let type = $(this).val();
                if (type === 'receipt') {
                    $('#order_number_div').show();
                    $('#purchase_number_div').hide();
                    $('#user_label').text('Customer Name');
                } else {
                    $('#order_number_div').hide();
                    $('#purchase_number_div').show();
                    $('#user_label').text('Vendor Name');
                }
                // Clear fields
                $('#order_id, #purchase_id, #user_id, #user_name, #total_amt, #total_paid, #remaining_amt, #total')
                    .val('');
                $('#order_number, #purchase_number').val('').trigger('change');
            });

            $('#order_number').on('change', function () {
                let invoiceNumber = $(this).val();
                if (!invoiceNumber) {
                    $('#order_id, #user_id, #user_name, #total_amt, #total_paid, #remaining_amt, #total').val('');
                    return;
                }

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: `/api/getSaleDetails/${invoiceNumber}`,
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    success: function (data) {
                        if (data.error) {
                            Swal.fire({
                                title: "Error!",
                                text: data.error,
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                            return;
                        }

                        $('#order_id').val(data.order.id);
                        $('#user_id').val(data.order.user_id);
                        $('#user_name').val(data.order.user_name);
                        $('#total_amt').val(data.order.total_amount);
                        $('#total_paid').val(data.order.paid_amount);
                        $('#remaining_amt').val(data.order.remaining_amount);

                        $('#settlement_amount').val(0);
                        $('#total').val(data.order.total_amount);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            title: "Error!",
                            text: 'Something went wrong while fetching order details!',
                            icon: "error",
                            confirmButtonColor: "#ff9f43"
                        });
                    }
                });
            });

            $('#purchase_number').on('change', function() {
                let invoiceNumber = $(this).val();

                if (!invoiceNumber) {
                    $('#purchase_id, #user_id, #user_name, #total_amt, #total_paid, #remaining_amt, #total')
                        .val('');
                    return;
                }

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: `/api/getPurchaseDetails/${invoiceNumber}`,
                    type: 'GET',
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: {
                        selectedSubAdminId: selectedSubAdminId
                    },
                    success: function(data) {
                        if (data.error) {
                            Swal.fire({
                                title: "Error!",
                                text: data.error,
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                            return;
                        }

                        $('#purchase_id').val(data.purchase.id);
                        $('#user_id').val(data.purchase.vendor_id);
                        $('#user_name').val(data.purchase.vendor_name);
                        $('#total_amt').val(data.purchase.total_amount);
                        $('#total_paid').val(data.purchase.paid_amount);
                        $('#remaining_amt').val(data.purchase.remaining_amount);

                        $('#settlement_amount').val(0);
                        $('#total').val(data.purchase.total_amount);
                    },
                    error: function() {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to fetch purchase details.",
                            icon: "error",
                            confirmButtonColor: "#ff9f43"
                        });
                    }
                });
            });

            // Auto calculate total if settlement changes
            $('#settlement_amount').on('input', function() {
                let totalAmt = parseFloat($('#total_amt').val()) || 0;
                let settlement = parseFloat($(this).val()) || 0;
                $('#total').val((totalAmt - settlement).toFixed(2));
            });

            $('#credit-note-item-form').on('submit', function (e) {
                e.preventDefault();
                if ($submitBtn.prop('disabled')) {
                    return;
                }
                let form = $(this);
                let id = "{{ $creditNoteItem->id }}";
                let url = "/api/credit-note-items-api/update/" + id;
                let formData = form.serialize();
                var authToken = localStorage.getItem("authToken");

                // Clear old errors
                $(".text-danger").html("");

                $.ajax({
                    url: url,
                    type: 'POST',
                    beforeSend: function () {
                        toggleSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    data: formData,
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonColor: "#ff9f43",
                                confirmButtonText: "OK"
                            }).then(() => {
                                window.location.href = "{{ route('credit-notes-items.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message,
                                icon: "error",
                                confirmButtonColor: "#ff9f43",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, value) {
                                $(`.error-${key}`).html(value[0]);
                            });
                        } else {
                            let errorMessage = xhr.responseJSON.message || 'Something went wrong!';
                            Swal.fire({
                                title: "Error!",
                                text: errorMessage,
                                icon: "error",
                                confirmButtonColor: "#ff9f43",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                    complete: function () {
                        toggleSubmitLoading(false);
                    }
                });
            });
        });
    </script>
@endpush
