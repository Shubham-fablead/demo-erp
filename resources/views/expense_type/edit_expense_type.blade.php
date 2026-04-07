@extends('layout.app')

@section('title', 'Expense Type Edit')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Expense Type Edit</h4>
            <!-- <h6>Add/Update Expenses</h6> -->
        </div>
    </div>
    <form id="expenseTypeEditForm">
        @csrf
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Expense Type <span class="text-danger">*</span></label>
                            <div class="input-groupicon">
                                <input type="text" name="type" id="type" placeholder="Expense Name" class="form-control">
                                <span class="text-danger error" id="type_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('expensetype.list') }}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>


</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");

        // Extract expenseTypeId from URL, e.g. /expense-type/edit/5
        const urlParts = window.location.pathname.split('/');
        const expenseTypeId = urlParts[urlParts.length - 1];

        // Fetch the existing data via AJAX
        $.ajax({
            url: `/api/expense-types/${expenseTypeId}`, // API endpoint to get one expense type
            method: 'GET',
            headers: {
                "Authorization": "Bearer " + authToken,
            },
            success: function(response) {
                // Assuming response.data contains the expense type object
                $('#type').val(response.data.type);
            },
            error: function() {
                alert('Failed to fetch expense type data.');
            }
        });

        // Handle form submission via AJAX
        $('#expenseTypeEditForm').on('submit', function(e) {
            e.preventDefault();

            // Clear previous errors
            $('#type_error').text('');

            var formData = {
                type: $('#type').val()
            };
            // Cache submit button
            var $btn = $('#expenseTypeEditForm button[type="submit"]');
            var originalText = $btn.html();

            // Show loader and disable button
            $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
                .prop('disabled', true);

            $.ajax({
                url: `/api/expense-types/${expenseTypeId}`, // PUT or PATCH API for updating
                method: 'PUT', // or 'PATCH' depending on your API
                headers: {
                    "Authorization": "Bearer " + authToken,
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                data: formData,
                success: function(response) {
                    $btn.html(originalText).prop('disabled', false);

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Expense type updated successfully!',
                        confirmButtonColor: '#ff9f43',
                    }).then(() => {
                        window.location.href = "{{ route('expensetype.list') }}"; // Redirect back to list
                    });
                },
                error: function(xhr) {
                    $btn.html(originalText).prop('disabled', false);

                    if (xhr.status === 422) { // Validation error
                        var errors = xhr.responseJSON.errors;
                        if (errors.type) {
                            $('#type_error').text(errors.type[0]);
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong, please try again!',
                            confirmButtonColor: '#ff9f43',
                        });
                    }
                }
            });
        });
    });
</script>
@endpush
