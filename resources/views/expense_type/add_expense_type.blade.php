@extends('layout.app')

@section('title', 'Expense Type Add')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Expense Type Add</h4>
            <!-- <h6>Add/Update Expenses</h6> -->
        </div>
    </div>
    <form id="expenseTypeForm">
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
    $('#expenseTypeForm').on('submit', function(e) {
        var authToken = localStorage.getItem("authToken");

        e.preventDefault();
        $('.error').text(''); // Clear errors



         var type = $('#type').val();
        var _token = '{{ csrf_token() }}';


        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        // Cache submit button
        var $btn = $('#expenseTypeForm button[type="submit"]');
        var originalText = $btn.html();

        // Show loader and disable button
        $btn.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...')
            .prop('disabled', true);

        // Clear previous errors
        $('.error').text('');

        $.ajax({
            url: '{{ route("expense-type.store") }}', // Your route name
            type: 'POST',
            data: {
                type: type,
                selectedSubAdminId: selectedSubAdminId,
                _token: _token
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                "Authorization": "Bearer " + authToken,
            },
            success: function(response) {
                $btn.html(originalText).prop('disabled', false);
                $('#expenseTypeForm')[0].reset();

                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff9f43' // Set button color here
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "{{ route('expensetype.list') }}";

                    }
                });
            },

            error: function(xhr) {
                $btn.html(originalText).prop('disabled', false);

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    if (errors.type) {
                        $('#type_error').text(errors.type[0]);
                    }
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#ff9f43'
                    });
                }
            }

        });
    });
</script>
@endpush
