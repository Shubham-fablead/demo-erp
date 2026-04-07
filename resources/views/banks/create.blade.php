@extends('layout.app')

@section('title', 'Banks Add')
@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }
        }
             a.btn.back-button {
    background: #ff9f43;
    color: #fff;
}
    </style>
    <div class="content">
        {{-- <div class="page-header">
            <div class="page-title">
                <h4>Add Banks</h4>
            </div>
        </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Add Banks</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('banks.index') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form id="create-bank-form">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Bank Name <span class="text-danger">*</span></label>
                                <input type="text" name="bank_name" id="bank_name">
                                <span class="error_bank_name text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Account Number <span class="text-danger">*</span></label>
                                <input type="text" name="account_number" id="account_number">
                                <span class="error_account_number text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>IFSC Code <span class="text-danger">*</span></label>
                                <input type="text" name="ifsc_code" id="ifsc_code">
                                <span class="error_ifsc_code text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Branch Name <span class="text-danger">*</span></label>
                                <input type="text" name="branch_name" id="branch_name">
                                <span class="error_branch_name text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Opening Balance <span class="text-danger">*</span></label>
                                {{-- <input type="number" name="opening_balance" id="opening_balance" class="form-control" min="0"
                                    step="1"> --}}
                                <input type="text" name="opening_balance" id="opening_balance" class="form-control">
                                <span class="error_opening_balance text-danger"></span>
                            </div>
                        </div>


                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Status</label>
                                <select class="status" name="status" id="status">
                                    <option value="1" selected>Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                <span class="error_status text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Submit</a>
                            <a href="{{ route('banks.index') }}" class="btn btn-cancel">Cancel</a></br>
                            <span class="success_submit text-danger"></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @push('js')
        <script>
            $(document).ready(function() {
                const $submitBtn = $('.btn-submit');
                const submitBtnDefaultHtml = $submitBtn.html();

                function toggleSubmitLoading(isLoading) {
                    if (isLoading) {
                        $submitBtn
                            .addClass('disabled')
                            .attr('aria-disabled', 'true')
                            .css('pointer-events', 'none')
                            .html(
                                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                            );
                    } else {
                        $submitBtn
                            .removeClass('disabled')
                            .removeAttr('aria-disabled')
                            .css('pointer-events', '')
                            .html(submitBtnDefaultHtml);
                    }
                }

                // Indian number formatter
                function formatIndianNumber(value) {
                    let number = value.replace(/,/g, '');
                    if (number === '' || isNaN(number)) return '';
                    return Number(number).toLocaleString('en-IN');
                }
                // Format while typing
                $('#opening_balance').on('input', function() {
                    let cursorPos = this.selectionStart;
                    let value = $(this).val();

                    let formatted = formatIndianNumber(value);
                    $(this).val(formatted);

                    this.setSelectionRange(cursorPos, cursorPos);
                });

                // Initialize Select2 for status
                $('#status').select2({
                    placeholder: "Select Status",
                    width: '100%',
                    allowClear: true,
                    minimumResultsForSearch: Infinity
                });

                // 1️⃣ Define clearValidationErrors function
                function clearValidationErrors() {
                    $('.text-danger').text('');
                }

                // 2️⃣ Bind click event to the submit button
                $('.btn-submit').on('click', function(e) {
                    e.preventDefault();
                    if ($submitBtn.hasClass('disabled')) {
                        return;
                    }
                    clearValidationErrors();

                    const form = $('#create-bank-form')[0];
                    const formData = new FormData(form);
                    let cleanAmount = $('#opening_balance').val().replace(/,/g, '');
                    formData.set('opening_balance', cleanAmount);
                    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    var authToken = localStorage.getItem("authToken");
                    const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                    formData.append("selectedSubAdminId", selectedSubAdminId);

                    // 3️⃣ Use normal jQuery AJAX
                    $.ajax({
                        url: "{{ route('banks.store') }}",
                        type: "POST",
                        beforeSend: function() {
                            toggleSubmitLoading(true);
                        },
                        headers: {
                            "Authorization": "Bearer " + authToken,
                        },
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            if (response.status) {
                                Swal.fire('Success', response.message, 'success')
                                    .then(() => {
                                        window.location.href = "{{ route('banks.index') }}";
                                    });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                $.each(xhr.responseJSON.errors, function(key, value) {
                                    $('.error_' + key).text(value[0]);
                                });
                            } else {
                                Swal.fire('Error', 'Something went wrong', 'error');
                            }
                        },
                        complete: function() {
                            toggleSubmitLoading(false);
                        }
                    });
                });

            });
        </script>
    @endpush
