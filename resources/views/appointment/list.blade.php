@extends('layout.app')
@section('title', 'Appointment List')
@section('content')
    <style>
        @media screen and (max-width: 767px) {

            div.dataTables_wrapper div.dataTables_length,
            div.dataTables_wrapper div.dataTables_filter,
            div.dataTables_wrapper div.dataTables_info,
            div.dataTables_wrapper div.dataTables_paginate {
                text-align: start;
            }

            .table-top {
                flex-direction: unset;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Appointments</h4>
            </div>
            <div class="page-btn">
                @if (app('hasPermission')(17, 'add'))
                    <a href="{{ route('appointments.create') }}" class="btn btn-sm btn-added">
                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">New
                        Appointment
                    </a>
                @endif
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-top mb-3">
                    <div class="row g-2 flex-lg-nowrap align-items-end">
                        <!-- Month -->
                        <div class="col-6 col-sm-3 col-md-8">
                            <div class="form-group mb-0">
                                <label for="filter-month" class="form-label">Month</label>
                                <select id="filter-month" class="form-control form-control-sm">
                                    <option value="">All Months</option>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}">
                                            {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <!-- Year -->
                        <div class="col-6 col-sm-3 col-md-8">
                            <div class="form-group mb-0">
                                <label for="filter-year" class="form-label">Year</label>
                                <select id="filter-year" class="form-control form-control-sm">
                                    <option value="">All Years</option>
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Export Button -->
                        <div class="col-6 col-sm-3 col-md-8">
                            <button id="export-excel" class="btn btn-success btn-sm">
                                <i class="bi bi-file-earmark-excel"></i> Export Excel
                            </button>
                        </div>
                        <!-- Date -->
                        {{-- <div class="col-6 col-sm-3 col-lg-4">
                            <!-- <div class="form-group mb-0"> -->
                            <label for="filter-date" class="form-label">Date</label>
                            <input type="text" id="filter-date" placeholder="Choose Date"
                                class="datetimepicker form-control form-control-sm">
                            <!-- </div> -->
                        </div> --}}
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Phone</th>
                                <th>Vehicle Number</th>
                                <th>Appointment Date</th>
                                <th>Appointment Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            var table = $('.datanew').DataTable();
            const selectedSubAdminId = localStorage.getItem('selectedSubAdminId');
            let appointmentUrl = "/api/appointments";

            if (selectedSubAdminId) {
                appointmentUrl += `?selectedSubAdminId=${selectedSubAdminId}`;
            }

            fetchAppointments();

            function fetchAppointments(filters = {}) {
                let url = appointmentUrl;

                // Build query string dynamically
                const queryParams = new URLSearchParams(filters).toString();
                if (queryParams) {
                    url += (url.includes('?') ? '&' : '?') + queryParams;
                }

                $.ajax({
                    url: url,
                    type: "GET",
                    dataType: "json",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let appointments = response.data;
                            let tableBody = [];

                            appointments.forEach((appointment) => {
                                let appointmentName = appointment.name ?
                                    appointment.name.replace(/\b\w/g, c => c.toUpperCase()) :
                                    "";

                                tableBody.push([
                                    appointmentName || "N/A",
                                    appointment.phone || "N/A",
                                    appointment.vehicle_number || "N/A",
                                    appointment.appointment_date || "N/A",
                                    appointment.appointment_time || "N/A",
                                    appointment.status ?
                                    appointment.status.charAt(0).toUpperCase() +
                                    appointment.status.slice(1) :
                                    "Pending",
                                    `
                                @if (app('hasPermission')(17, 'view'))
                                <a class="me-3" href="/appointments/view/${appointment.id}">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/eye.svg' }}" alt="View">
                                </a>
                                @endif

                                @if (app('hasPermission')(17, 'edit'))
                                <a class="me-3" href="/appointments/edit/${appointment.id}">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit.svg' }}" alt="Edit">
                                </a>
                                @endif

                                @if (app('hasPermission')(17, 'delete'))
                                <a class="me-3 delete-appointment" data-id="${appointment.id}" href="javascript:void(0);">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/delete.svg' }}" alt="Delete">
                                </a>
                                @endif
                            `
                                ]);
                            });

                            table.clear().rows.add(tableBody).draw();
                        } else {
                            table.clear().draw();
                            $(".datanew tbody").html(
                                '<tr><td colspan="7">No appointments found</td></tr>'
                            );
                        }
                    },
                    error: function(xhr) {
                        console.log("Error:", xhr);
                    }
                });
            }

            // Event listener for Month & Year filters
            $('#filter-month, #filter-year').on('change', function() {
                const month = $('#filter-month').val();
                const year = $('#filter-year').val();

                const filters = {
                    selectedSubAdminId: selectedSubAdminId
                };
                if (month) filters.month = month;
                if (year) filters.year = year;

                fetchAppointments(filters);
            });

            // Delete appointment
            $(document).on('click', '.delete-appointment', function() {
                var appointmentId = $(this).data('id');
                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/api/appointments/${appointmentId}`,
                            type: 'DELETE',
                            headers: {
                                "Authorization": "Bearer " + authToken
                            },
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: response.message,
                                        icon: "success",
                                        confirmButtonColor: "#ff9f43",
                                        confirmButtonText: "OK"
                                    });
                                    fetchAppointments();
                                } else {
                                    Swal.fire("Error!", response.message, "error");
                                }
                            },
                            error: function(xhr) {
                                let message = "Something went wrong!";
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    message = xhr.responseJSON.message;
                                }
                                Swal.fire({
                                    title: "Error!",
                                    text: message,
                                    icon: "error",
                                    confirmButtonColor: "#ff9f43",
                                    confirmButtonText: "OK"
                                });
                            }
                        });
                    }
                });
            });

            // ✅ Export Excel button
            $("#export-excel").on("click", function() {
                const year = $("#filter-year").val(); // ✅ fixed ID
                const month = $("#filter-month").val(); // ✅ fixed ID

                let apiUrl = `/api/export-appointments?selectedSubAdminId=${selectedSubAdminId}`;
                if (year) apiUrl += `&year=${year}`;
                if (month) apiUrl += `&month=${month}`;

                fetch(apiUrl, {
                        method: "GET",
                        headers: {
                            "Authorization": "Bearer " + authToken
                        }
                    })
                    .then(response => {
                        if (!response.ok) throw new Error("Export failed");
                        return response.blob();
                    })
                    .then(blob => {
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement("a");
                        a.href = url;
                        a.download = "appointments_export.xlsx";
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(err => {
                        console.error("Export failed", err);
                        Swal.fire("Appointment!", "No Record Found", "error");
                    });
            });
        });
    </script>
@endpush
