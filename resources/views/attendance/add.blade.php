@extends('layout.app')

@section('title', 'Add Attendance')

@section('content')

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Add Attendance</h4>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <form id="attendanceForm">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Staff</label>
                                <select name="user_id" id="user_id" class="form-control">
                                    <option value="">Select Staff</option>
                                    @foreach ($staffUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-user_id"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" id="date" class="form-control"
                                    value="{{ \Carbon\Carbon::today()->toDateString() }}" readonly>
                                <span class="text-danger error-date"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">Select Status</option>
                                    <option value="P">Present</option>
                                    <option value="A">Absent</option>
                                    <option value="L">Leave</option>
                                </select>
                                <span class="text-danger error-status"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Check In Time</label>
                                <input type="time" name="check_in_time" id="check_in_time" class="form-control">
                                <span class="text-danger error-check_in_time"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Check Out Time</label>
                                <input type="time" name="check_out_time" id="check_out_time" class="form-control">
                                <span class="text-danger error-check_out_time"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Reason</label>
                                <textarea name="reason" id="reason" class="form-control"></textarea>
                                <span class="text-danger error-reason"></span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                            <a href="{{ route('attendance.list') }}" class="btn btn-cancel">Cancel</a>
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
            $('select').select2({
                placeholder: 'Select an option',
                width: '100%'
            });
            // Function to toggle fields based on status
            function toggleFields(status) {
                if (status === 'P') {
                    $('#check_in_time').closest('.form-group').show();
                    $('#check_out_time').closest('.form-group').show();
                    $('#reason').closest('.form-group').hide();
                } else if (status === 'A' || status === 'L') {
                    $('#check_in_time').closest('.form-group').hide();
                    $('#check_out_time').closest('.form-group').hide();
                    $('#reason').closest('.form-group').show();
                } else {
                    // Hide all optional fields initially
                    $('#check_in_time').closest('.form-group').hide();
                    $('#check_out_time').closest('.form-group').hide();
                    $('#reason').closest('.form-group').hide();
                }

                // Clear old errors and inputs
                $('.text-danger').text('');
                if (status !== 'P') {
                    $('#check_in_time').val('');
                    $('#check_out_time').val('');
                }
                if (status === 'P') {
                    $('#reason').val('');
                }
            }

            // Initial hide on page load
            toggleFields($('#status').val());

            // Listen for status change
            $('#status').on('change', function() {
                toggleFields($(this).val());
            });

            $(document).on('submit', '#attendanceForm', function(e) {
                e.preventDefault();
                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // console.log(selectedSubAdminId);

                let formData = new FormData($('#attendanceForm')[0]);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                $.ajax({
                    url: "/api/attendance/store",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.message) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message,
                                icon: "success",
                                confirmButtonColor: "#ff9f43",
                                confirmButtonText: "OK"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('attendance.list') }}";
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        $('.text-danger').text(''); // Clear all previous errors
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.error-' + key).text(value[0]);
                            });
                        }
                    }

                });
            });
        });
    </script>
@endpush
