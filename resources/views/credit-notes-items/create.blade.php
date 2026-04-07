@extends('layout.app')

@section('title', 'Create Credit Note')
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
                <h4>Create Credit Note</h4>
            </div>
        </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Create Credit Note</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('credit-notes-items.index') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form method="POST" id="credit-note-item-form">
                    @csrf
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="purchase_id" id="purchase_id">
                    <input type="hidden" name="user_id" id="user_id">
                    <div class="row">
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Transaction Type</label>
                                <select name="transaction_type" id="transaction_type" class="form-control select2">
                                    <option value="receipt">Receipt</option>
                                    <option value="payment">Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6" id="order_number_div">
                            <div class="form-group">
                                <label>Order Number <span class="manitory">*</span></label>
                                <select name="order_number" id="order_number" class="form-control select2 ordder-number">
                                    <option value="">Select Order Number</option>
                                    @foreach ($invoiceNumbers as $number)
                                        <option value="{{ $number }}">{{ $number }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-order_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6" id="purchase_number_div" style="display: none;">
                            <div class="form-group">
                                <label>Invoice Number <span class="manitory">*</span></label>
                                <select name="purchase_number" id="purchase_number" class="form-control select2">
                                    <option value="">Select Invoice Number</option>
                                    @foreach ($purchaseInvoiceNumbers as $number)
                                        <option value="{{ $number }}">{{ $number }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-purchase_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label id="user_label">Customer Name</label>
                                <input type="text" id="user_name" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Credit Note Type <span class="manitory">*</span></label>
                                <select name="credite_note_id" id="credite_note_id" class="form-control select2 credit-note-type">
                                    <option value="">Select Type</option>
                                    @foreach ($creditNoteTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                                    @endforeach
                                </select>
                                <div class="text-danger error-credite_note_id"></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Total Amount</label>
                                <div class="input-group">
                                    {{-- <span class="input-group-text"></span> --}}
                                    <input type="text" name="total_amt" id="total_amt" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Paid Amount</label>
                                <div class="input-group">
                                    {{-- <span class="input-group-text"></span> --}}
                                    <input type="text" name="total_paid" id="total_paid" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Remaining Amount</label>
                                <div class="input-group">
                                    {{-- <span class="input-group-text"></span> --}}
                                    <input type="text" name="remaining_amt" id="remaining_amt" class="form-control"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Settlement Amount <span class="manitory">*</span></label>
                                <div class="input-group">
                                    {{-- <span class="input-group-text">{{ $currencySymbol }}</span> --}}
                                    <input type="number" name="settlement_amount" id="settlement_amount"
                                        class="form-control" step="0.01">
                                </div>
                                <div class="text-danger error-settlement_amount"></div>

                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Final Total</label>
                                <div class="input-group">
                                    {{-- <span class="input-group-text">{{ $currencySymbol }}</span> --}}
                                    <input type="number" name="total" id="total" class="form-control" step="0.01"
                                        readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Reason <span class="manitory">*</span></label>
                                <textarea name="reason" id="reason" class="form-control"></textarea>
                                <div class="text-danger error-reason"></div>
                            </div>

                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
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
        $(document).ready(function() {
            $('.credit-note-type,.ordder-number,#transaction_type,#purchase_number').select2({
                placeholder: "Select option",
                allowClear: true,
                width: '100%'
            });

            const authToken = localStorage.getItem("authToken");

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

            /* ===============================
               ORDER CHANGE → FETCH DETAILS
            ================================ */
            $('#order_number').on('change', function() {

                let invoiceNumber = $(this).val();

                // Clear fields if empty
                if (!invoiceNumber) {
                    $('#order_id, #user_id, #user_name, #total_amt, #total_paid, #remaining_amt, #total')
                        .val('');
                    return;
                }

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                $.ajax({
                    url: `/api/getSaleDetails/${invoiceNumber}`,
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

                        $('#order_id').val(data.order.id);
                        $('#user_id').val(data.order.user_id);
                        $('#user_name').val(data.order.user_name);
                        $('#total_amt').val(data.order.total_amount);
                        $('#total_paid').val(data.order.paid_amount);
                        $('#remaining_amt').val(data.order.remaining_amount);

                        $('#settlement_amount').val(0);
                        $('#total').val(data.order.total_amount);
                    },
                    error: function() {
                        Swal.fire({
                            title: "Error!",
                            text: "Failed to fetch order details.",
                            icon: "error",
                            confirmButtonColor: "#ff9f43"
                        });
                    }
                });
               
            });

            /* ===============================
               PURCHASE CHANGE → FETCH DETAILS
            ================================ */
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

            /* ===============================
               AUTO CALCULATE FINAL TOTAL
            ================================ */
            $('#settlement_amount').on('input', function() {
                let totalAmt = parseFloat($('#total_amt').val()) || 0;
                let settlement = parseFloat($(this).val()) || 0;
                $('#total').val((totalAmt - settlement).toFixed(2));
            });

            /* ===============================
               FORM SUBMIT (AJAX)
            ================================ */
            $('#credit-note-item-form').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $(".text-danger").html(""); // clear old errors

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                formData.append("selectedSubAdminId", selectedSubAdminId);

                const $btn = $(this).find('button[type="submit"]');
                const originalText = $btn.html();

                // Spinner + disable
                $btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...')
                    .prop("disabled", true);

                $.ajax({
                    url: "/api/credit-note-items-api/store",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        $btn.html(originalText).prop("disabled", false);

                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonColor: "#ff9f43"
                            }).then(() => {
                                window.location.href =
                                    "{{ route('credit-notes-items.index') }}";
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop("disabled", false);

                        // ✅ Laravel validation errors
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $(".error-" + key).html(value[0]);
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Something went wrong!",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    }
                });
            });

            /* ===============================
               CLEAR ERROR ON INPUT
            ================================ */
            // $('input, select, textarea').on('input change', function() {
            //     let fieldName = $(this).attr('name');
            //     $('.error-' + fieldName).html('');
            // });

            $('input, textarea').on('input', function() {
                let fieldName = $(this).attr('name');
                $('.error-' + fieldName).html('');
            });

            $('select').on('change', function() {
                let fieldName = $(this).attr('name');
                $('.error-' + fieldName).html('');
            });


        });
    </script>
@endpush
