@extends('layout.app')
@section('title', 'Add Appointment')
@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Appointment</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form id="appointmentForm">
                <div class="row">
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" id="name" class="form-control">
                            <span class="text-danger error-name"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" id="phone" class="form-control">
                            <span class="text-danger error-phone"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" id="address" class="form-control">
                            <span class="text-danger error-address"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Vehicle Number</label>
                            <input type="text" name="vehicle_number" id="vehicle_number" class="form-control">
                            <span class="text-danger error-vehicle_number"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Vehicle Type</label>
                            <select name="vehicle_type" id="vehicle_type" class="form-control">
                                <option value="">Select Type</option>
                                <option value="Two Wheeler">Two Wheeler</option>
                                <option value="Three Wheeler">Three Wheeler</option>
                                <option value="Four Wheeler">Four Wheeler</option>
                                <option value="Heavy Vehicle">Heavy Vehicle</option>
                            </select>
                            <span class="text-danger error-vehicle_type"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Vehicle Model</label>
                            <select name="vehicle_model" id="vehicle_model" class="form-control">
                                <option value="">Select Model</option>
                                @foreach ($modals as $modal)
                                <option value="{{ $modal->id }}" data-brand="{{ $modal->model_brand }}">
                                    {{ $modal->model_name }}
                                </option>
                                @endforeach
                            </select>
                            <span class="text-danger error-vehicle_model"></span>
                        </div>
                    </div>

                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Appointment Date</label>
                            <input type="date" name="appointment_date" id="appointment_date" class="form-control">
                            <span class="text-danger error-appointment_date"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Appointment Time</label>
                            <input type="time" name="appointment_time" id="appointment_time" class="form-control">
                            <span class="text-danger error-appointment_time"></span>
                        </div>
                    </div>
                    <div class="col-lg-2 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="confirmed">Confirmed</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <span class="text-danger error-status"></span>
                        </div>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Remarks</label>
                            <textarea type="text" name="remarks" id="remarks" class="form-control"></textarea>
                            <span class="text-danger error-remarks"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2">Submit</button>
                        <a href="{{ route('appointments.index') }}" class="btn btn-cancel">Cancel</a>
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
    });
</script>

<script>
    $(document).ready(function() {
        $(document).on('submit', '#appointmentForm', function(e) {
            e.preventDefault();
            var authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            let formData = new FormData($('#appointmentForm')[0]);
            formData.append('selectedSubAdminId', selectedSubAdminId);

            $.ajax({
                url: "/api/appointments",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            title: "Success!",
                            text: "Appointment added successfully!",
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href =
                                    "{{ route('appointments.index') }}";
                            }
                        });
                    }
                },
                error: function(xhr) {
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
    document.addEventListener("DOMContentLoaded", function() {
        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');
        let todayStr = yyyy + '-' + mm + '-' + dd;

        document.getElementById("appointment_date").setAttribute("min", todayStr);
    });
</script>
@endpush
