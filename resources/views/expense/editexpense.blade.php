@extends('layout.app')

@section('title', 'Expense Edit')

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
            <h4>Edit Expense</h4>

            </div>
        </div>
    </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Edit Expense</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('expense.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                    Back</a></br>
                <span class="success_submit text-danger"></span>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    {{-- Expense Name --}}
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Name</label>
                            <div class="input-groupicon">
                                <input type="text" id="expense_name" placeholder="Choose Expense Name">
                            </div>
                        </div>
                    </div>

                    {{-- Expense Date --}}
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Date</label>
                            <div class="input-groupicon">
                                <input type="text" id="expense_date" placeholder="Choose Date" class="datetimepicker">
                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/calendars.svg' }}"
                                        alt="img">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Expense Date --}}
                    {{-- <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Date <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" id="expense_date" placeholder="Choose Date" class="datetimepicker">
                                <span class="text-danger error" id="expense_date_error"></span>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/calendars.svg' }}"
                                        alt="img">
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    {{-- Amount --}}
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Amount <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" id="amount">
                                <span class="text-danger error" id="amount_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Type <span class="text-danger">*</span></label>
                            <select class="select" name="expense_type_id" id="expense_type_id">
                                <option value="">Select Expense Type</option>

                            </select>
                            <span class="error text-danger" id="expense_type_id_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Expense for</label>
                            <textarea id="description" name="description" class="form-control" placeholder="Purpose of Expense" rows="3"></textarea>
                            <span class="text-danger error" id="description_error"></span>
                        </div>
                    </div>


                    {{-- Hidden ID --}}
                    <input type="hidden" id="expense_id">

                    {{-- Buttons --}}
                    <div class="col-lg-12">
                        <a class="btn btn-submit me-2" id="updateExpense">Update</a>
                        <a href="{{ route('expense.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            let url = window.location.pathname;
            let expenseId = url.split("/").pop();
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");


            // Fetch and populate data
            $.ajax({
                url: `/api/expenses/${expenseId}/edit`,
                method: 'GET',
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                data: {
                    selectedSubAdminId: selectedSubAdminId
                },
                success: function(response) {
                    const expense = response.data;
                    const expenseTypes = response.expenseTypes;

                    // Build the options list with Expense Type names
                    let options = '<option value="">Select Expense Type</option>';
                    expenseTypes.forEach(function(type) {
                        // Convert both to numbers before comparing
                        const selected = (Number(type.id) === Number(expense.expense_type_id)) ?
                            'selected' : '';
                        options +=
                            `<option value="${type.id}" ${selected}>${type.type}</option>`;
                    });
                    $('#expense_type_id').html(options);
                    $('#expense_name').val(expense.expense_name);
                    $('#expense_date').val(formatDate(expense.expense_date));
                    $('#amount').val(expense.amount);
                    $('#description').val(expense.description);
                    $('#expense_id').val(expense.id);
                },
                // error: function() {
                //     window.location.href = "{{ route('expense.list') }}";
                // }
            });

            // Format date from yyyy-mm-dd to dd-mm-yyyy
            function formatDate(dateStr) {
                const date = new Date(dateStr);
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${day}-${month}-${year}`;
            }

            // Update logic
            $('#updateExpense').click(function() {
                const id = $('#expense_id').val();
                const amount = parseFloat($('#amount').val()) || 0;

                // Validation: Amount cannot be negative
                if (amount < 0) {
                    // Show error message below input
                    if ($("#amountError").length === 0) {
                        $('#amount').after(
                            '<small id="amountError" class="text-danger">Amount cannot be negative.</small>'
                        );
                    }
                    $('#amount').val(0); // reset to 0
                    return; // stop form submission
                } else {
                    $("#amountError").remove(); // remove error if valid
                }

                const $btn = $(this);
                const originalText = $btn.html();

                // Show loader and disable button
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop('disabled', true);

                $.ajax({
                    url: `/api/expenses/${id}`,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        expense_name: $('#expense_name').val(),
                        expense_date: $('#expense_date').val(),
                        amount: $('#amount').val(),
                        description: $('#description').val(),
                        expense_type_id: $('#expense_type_id').val(), // <-- this was missing!
                    },
                    headers: {
                        Authorization: `Bearer ${authToken}`,
                    },
                    success: function(res) {
                        // Restore original button
                        $btn.html(originalText).prop('disabled', false);

                        Swal.fire({
                            title: "Success!",
                            text: "Expense updated successfully!",
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43" // your custom color
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('expense.list') }}";
                            }
                        });
                    },
                    error: function(xhr) {

                        $btn.html(originalText).prop('disabled', false);

                        // Clear old errors
                        $('.error').html('');

                        if (xhr.status === 422) {

                            let errors = xhr.responseJSON.errors;

                            $.each(errors, function(field, messages) {

                                // show error in matching span
                                $('#' + field + '_error').html(messages[0]);

                            });

                        } else {
                            Swal.fire("Error", "Something went wrong. Try again.", "error");
                        }
                    }
                });
            });
        });
    </script>
@endpush
