@extends('layout.app')

@section('title', 'Expense Add')

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
            <h4>Expense Add</h4>
            <!-- <h6>Add/Update Expenses</h6> -->
        </div>
    </div> --}}
    <div class="page-header ">
            <div class="page-title">
                <h4>Add Expense</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('expense.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>
    <form id="expenseForm">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Name <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_name" placeholder="Expense Name">
                                <span class="text-danger error" id="expense_name_error"></span>

                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Date <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" name="expense_date" placeholder="Choose Date" class="datetimepicker">
                                <span class="text-danger error" id="expense_date_error"></span>
                                <div class="addonset">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/calendars.svg' }}" alt="img">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Amount <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="number" class="form-control" name="amount" placeholder="Enter Amount">
                                <span class="text-danger error" id="amount_error"></span>
                                <!-- <div class="addonset">
                                    <img src="admin/assets/img/icons/dollar.svg" alt="img">
                                </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Expense Type <span class="text-danger">*</span></label>
                            <select class="select" name="expense_type_id" id="expense_type_id">
                                <option value="">Select Expense Type</option>
                                @foreach($expenseTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->type }}</option>
                                @endforeach
                            </select>
                            <span class="error text-danger" id="expense_type_id_error"></span>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Expense for</label>
                            <textarea name="description" id="description" class="form-control" placeholder="Purpose of Expense" rows="3"></textarea>
                            <span class="text-danger error" id="description_error"></span>
                        </div>
                    </div>


                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('expense.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')

<script>
    $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        var authToken = localStorage.getItem("authToken");

        // Cache submit button
        var $btn = $('#expenseForm button[type="submit"]');
        var originalText = $btn.html();

        // Show loader and disable button
        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
            .prop('disabled', true);


        // Clear previous errors
        $('.error').text('');
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");


        $.ajax({
            url: '{{ route("expenses.store") }}',
            method: 'POST',
            data: $(this).serialize() + "&selectedSubAdminId=" + selectedSubAdminId,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                "Authorization": "Bearer " + authToken,
            },
            success: function(response) {
                // Restore button
                $btn.html(originalText).prop('disabled', false);
                // Swal.fire("Expense Added!", response.message, "success");
                // $('#expenseForm')[0].reset();
                Swal.fire({
                    title: "Success",
                    text: "Expense Added successfully",
                    icon: "success",
                    confirmButtonText: "OK",
                    confirmButtonColor: "#ff9f43" // Set custom button color
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('expense.list') }}";

                    }
                });
            },
            error: function(xhr) {
                // Restore button
                $btn.html(originalText).prop('disabled', false);
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    // Loop and show errors
                    for (let field in errors) {
                        $('#' + field + '_error').text(errors[field][0]);
                    }
                }
            }
        });
    });
</script>


@endpush
