@extends('layout.app')
@section('title', 'Attendance View')
@section('content')
    <style>
  #saveBtn {
        position: relative;
        min-width: 80px;
    }

    .btn-text {
        display: inline-block;
    }

    #saveSpinner {
        margin-left: 5px;
    }

    /* You can also add this to your existing style section */
    .btn-submit:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    /* Staff Name column word wrap */
.staff-name-cell {
    white-space: normal !important;
    word-break: break-word;
    overflow-wrap: anywhere;
    max-width: 160px;
    line-height: 1.3;
}
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>All Attendance</h4>
            </div>
            <div class="page-btn">
                @if(auth()->user()->role !== 'staff')
                <a href="javascript:void(0);" class="btn btn-added" id="addAllBtn">
                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/plus.svg' }}" class="me-1" alt="img">
                    Add All Attendance
                </a>
                @endif
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <h5 class="mb-0" id="month-year-header">{{ date('F Y', strtotime($currentMonth)) }} Attendance</h5>
                 <div class="d-flex align-items-center flex-wrap gap-3 p-2 rounded shadow-sm border bg-light">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">P</span>
                        <span class="fw-semibold text-dark">Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-danger px-3">A</span>
                        <span class="fw-semibold text-dark">Absent</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">HP</span>
                        <span class="fw-semibold text-dark">Half Day Present</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge rounded-pill bg-success px-3">2P</span>
                        <span class="fw-semibold text-dark">Extra Day Present</span>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div style="width: 150px;">
                        <input type="month" class="form-control form-control-sm" id="select-date" name="month"
                            value="{{ $currentMonth }}">
                    </div>
                    <div style="width: 146px;">
                        <input type="text" id="searchStaff" class="form-control form-control-sm"
                            placeholder="Search Staff...">
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0" style="max-height: 600px;">
                <div id="attendance-table-wrapper">
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead class="sticky-top"
                style="z-index:1 !important; top: 0; background-color: #1b2850; color: #fff;">
                <tr>
                    <th style="position: sticky; left: 0; z-index: 10; background-color: #1b2850; color: #fff;">
                        Staff Name
                    </th>

                    @for ($i = 1; $i <= $daysInMonth; $i++)
                        <th class="text-white">{{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody id="attendance-table-body">
                @foreach ($staffUsers as $staff)
                    <tr>
                        <td style="position: sticky; left: 0; background-color: #fff; cursor: pointer;"
                            class="text-start ps-2 staff-name-cell"
                            data-user-id="{{ $staff->id }}"
                            data-user-name="{{ ucwords($staff->name) }}">
                            {{ ucwords($staff->name) }}
                        </td>
                        @for ($i = 1; $i <= $daysInMonth; $i++)
                            @php
                                $date = $year . '-' . $month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                                $key = $staff->id . '_' . $date;
                                $attendance = $attendances[$key][0] ?? null;
                                $status = $attendance->status ?? 'A';
                            @endphp
                            @php
                                $displayStatus = $status;
                                if ($status == 'P' && ($attendance->extraday ?? 0) == 1) {
                                    $displayStatus = '2P';
                                }
                                if ($status == 'H') {
                                    $displayStatus = 'H';
                                }
                            @endphp
                            <td class="attendance-cell text-center" data-user-id="{{ $staff->id }}"
                                data-date="{{ $date }}" data-status="{{ $status }}"
                                data-checkin="{{ $attendance->check_in_time ?? '' }}"
                                data-checkout="{{ $attendance->check_out_time ?? '' }}"
                                data-reason="{{ $attendance->reason ?? '' }}"
                                data-extraday="{{ $attendance->extraday ?? '0' }}" style="cursor:pointer;">
                                @if ($displayStatus == 'P' || $displayStatus == '2P')
                                    <strong class="text-success">{{ $displayStatus }}</strong>
                                @elseif ($displayStatus == 'H')
                                    <strong class="text-success">HP</strong>
                                @else
                                    <strong class="text-danger">A</strong>
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

            </div>
        </div>
    </div>
    <!-- Attendance Modal -->
    <div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="attendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Attendance</h5>
                        <div class="mb-2 d-flex fw-bold ">
                            <label class="me-2">Date:</label>
                            <div id="display_date" class="fw-bold"></div>
                        </div>
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal"></button> --}}
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="user_id">
                        <input type="hidden" name="date" id="date">
                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="status">
                                <option value="P">Present</option>
                                <option value="H">Half Day</option>
                                <option value="A">Absent</option>
                            </select>
                            <div class="text-danger" id="error_status"></div>

                        </div>
                        <div class="mb-2">
                            <label>Check-In Time</label>
                            <input type="time" class="form-control" name="check_in_time" id="check_in_time">
                            <div class="text-danger" id="error_check_in_time"></div>

                        </div>
                        <div class="mb-2">
                            <label>Check-Out Time</label>
                            <input type="time" class="form-control" name="check_out_time" id="check_out_time">
                            <div class="text-danger" id="error_check_out_time"></div>
                        </div>
                        <div class="mb-2" id="extraday-field" style="display: none;">
                            <label>Extra Day</label>
                            <select class="form-select" name="extraday" id="extraday">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <div class="text-danger" id="error_extraday"></div>
                        </div>
                        <div class="mb-2">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" id="reason"></textarea>
                            <div class="text-danger" id="error_reason"></div>
                        </div>
                    </div>
                    {{-- <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit">Save</button>
                    </div> --}}
                    <!-- In your modal footer section -->
                    <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="saveBtn">
                            <span class="btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" id="saveSpinner"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @if(auth()->user()->role !== 'staff')
    <!-- Bulk Attendance Modal -->
    <div class="modal fade" id="bulkAttendanceModal" tabindex="-1" aria-labelledby="bulkAttendanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="bulkAttendanceForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkModalTitle">All Attendance Update</h5>
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> --}}
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="bulk_user_id">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label>Start Date</label>
                                <div class="input-groupicon">
                                    <input type="text" class="form-control datetimepicker-bulk" name="start_date"
                                        id="bulk_start_date" placeholder="DD/MM/YYYY" required>
                                    {{-- <div class="addonset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/calendars.svg' }}"
                                            alt="img">
                                    </div> --}}
                                </div>
                                <div class="text-danger" id="error_bulk_start_date"></div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label>End Date</label>
                                <div class="input-groupicon">
                                    <input type="text" class="form-control datetimepicker-bulk" name="end_date"
                                        id="bulk_end_date" placeholder="DD/MM/YYYY" required>
                                    {{-- <div class="addonset">
                                        <img src="{{ env('ImagePath') . 'admin/assets/img/icons/calendars.svg' }}"
                                            alt="img">
                                    </div> --}}
                                </div>
                                <div class="text-danger" id="error_bulk_end_date"></div>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>Status</label>
                            <select class="form-select" name="status" id="bulk_status">
                                <option value="P">Present</option>
                                <option value="H">Half Day</option>
                                <option value="A">Absent</option>
                            </select>
                            <div class="text-danger" id="error_bulk_status"></div>
                        </div>
                        <div class="mb-2 bulk-time-fields">
                            <label>Check-In Time</label>
                            <input type="time" class="form-control" name="check_in_time" id="bulk_check_in_time">
                            <div class="text-danger" id="error_bulk_check_in_time"></div>
                        </div>
                        <div class="mb-2 bulk-time-fields">
                            <label>Check-Out Time</label>
                            <input type="time" class="form-control" name="check_out_time" id="bulk_check_out_time">
                            <div class="text-danger" id="error_bulk_check_out_time"></div>
                        </div>
                        <div class="mb-2" id="bulk-extraday-field">
                            <label>Extra Day</label>
                            <select class="form-select" name="extraday" id="bulk_extraday">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                            <div class="text-danger" id="error_bulk_extraday"></div>
                        </div>
                        <div class="mb-2" id="bulk-reason-field" style="display: none;">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" id="bulk_reason"></textarea>
                            <div class="text-danger" id="error_bulk_reason"></div>
                        </div>
                    </div>
                    <div style="padding: 1rem;">
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-submit" id="bulkSaveBtn">
                            <span class="bulk-btn-text">Save</span>
                            <span class="spinner-border spinner-border-sm d-none" role="status" id="bulkSaveSpinner"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            // console.log(selectedSubAdminId);

            // Function to update the month-year header
            function updateMonthYearHeader() {
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August",
                    "September", "October", "November", "December"
                ];
                const selectedDate = $('#select-date').val();
                const [year, month] = selectedDate.split('-');
                const monthName = monthNames[parseInt(month, 10) - 1];
                $('#month-year-header').text(`${monthName} ${year} Attendance`);
            }

            // Initialize month-year header
            updateMonthYearHeader();

            // Initialize bulk date pickers
            if ($('.datetimepicker-bulk').length > 0) {
                $('.datetimepicker-bulk').datetimepicker({
                    format: 'DD/MM/YYYY',
                    useCurrent: false, // Prevent automatic date selection
                    icons: {
                        up: "fas fa-angle-up",
                        down: "fas fa-angle-down",
                        next: 'fas fa-angle-right',
                        previous: 'fas fa-angle-left'
                    }
                });
            }

            // Function to fetch and update attendance data
            function fetchAttendanceData() {
                const month = $('#select-date').val();
                const search = $('#searchStaff').val();

                $.ajax({
                    url: "{{ route('attendance.list') }}",
                    method: 'GET',
                    data: {
                        month: month,
                        search: search
                    },
                    success: function(response) {
                        $('#attendance-table-wrapper').html($(response).find('#attendance-table-wrapper').html());
                        updateMonthYearHeader();
                    },
                    error: function(xhr) {
                        console.log(xhr.responseText);
                    }
                });
            }

            // Listen for changes on the month input and search input
            $('#select-date, #searchStaff').on('change input', function() {
                fetchAttendanceData();
            });

            $('#addAllBtn').on('click', function() {
                $('#bulk_user_id').val('');
                $('#bulkModalTitle').text('All Attendance Update');
                $('#bulkAttendanceModal').modal('show');
            });

            $(document).on('click', '.staff-name-cell', function() {
                const currentUserRole = '{{ auth()->user()->role }}';
                if (currentUserRole === 'Staff') {
                    return;
                }

                const userId = $(this).data('user-id');
                const userName = $(this).data('user-name');

                $('#bulk_user_id').val(userId);
                $('#bulkModalTitle').text(`${userName} Attendance Update`);
                $('#bulkAttendanceModal').modal('show');
            });

            $('#bulk_status').on('change', function() {
                const status = $(this).val();
                if (status === 'P' || status === 'H') {
                    $('.bulk-time-fields').show();
                    $('#bulk-extraday-field').show();
                    $('#bulk-reason-field').hide();
                } else {
                    $('.bulk-time-fields').hide();
                    $('#bulk-extraday-field').hide();
                    $('#bulk-reason-field').show();
                }
            });

            $('#bulkAttendanceForm').on('submit', function(e) {
                e.preventDefault();

                // Clear any inline errors before new request
                $('#bulkAttendanceForm .text-danger').html('');

                // Client-side validation
                let hasError = false;
                const startDate = $('#bulk_start_date').val();
                const endDate = $('#bulk_end_date').val();

                if (!startDate) {
                    $('#error_bulk_start_date').text('Start Date is required');
                    hasError = true;
                }
                if (!endDate) {
                    $('#error_bulk_end_date').text('End Date is required');
                    hasError = true;
                }

                if (hasError) {
                    return false;
                }

                const saveBtn = $('#bulkSaveBtn');
                const btnText = $('.bulk-btn-text');
                const saveSpinner = $('#bulkSaveSpinner');

                saveBtn.prop('disabled', true);
                btnText.text('Saving...');
                saveSpinner.removeClass('d-none');

                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = new FormData(this);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                $.ajax({
                    url: "{{ route('attendance.bulk-store') }}",
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(res) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        $('#bulkAttendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        let response = xhr.responseJSON;
                        let errors = response ? response.errors : null;

                        if (errors) {
                            let allErrors = [];
                            for (let key in errors) {
                                // Display specific error message in the corresponding div
                                $(`#error_bulk_${key}`).text(errors[key][0]);
                                allErrors.push(errors[key][0]);
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>'),
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    }
                });
            });

            // Function to toggle fields based on status
            function toggleAttendanceFields(status) {
                if (status === 'P' || status === 'H') {
                    $('#check_in_time').closest('.mb-2').show();
                    $('#check_out_time').closest('.mb-2').show();
                    $('#extraday-field').show(); // Show extraday field
                    $('#reason').closest('.mb-2').hide();
                } else {
                    $('#check_in_time').closest('.mb-2').hide();
                    $('#check_out_time').closest('.mb-2').hide();
                    $('#extraday-field').hide(); // Hide extraday field
                    $('#reason').closest('.mb-2').show();
                }
            }

            // When status changes in the modal
            $('#status').on('change', function() {
                toggleAttendanceFields($(this).val());
            });

            // When opening modal
            $(document).on('click', '.attendance-cell', function() {
                // Replace 'staff' with whatever your staff role identifier is
                const currentUserRole = '{{ auth()->user()->role }}';

                if (currentUserRole === 'staff') {
                    // Staff should not open modal
                    return; // exit function, do nothing
                }

                const status = $(this).data('status');

                $('#user_id').val($(this).data('user-id'));
                $('#date').val($(this).data('date'));
                $('#status').val(status);
                $('#check_in_time').val($(this).data('checkin'));
                $('#check_out_time').val($(this).data('checkout'));
                $('#reason').val($(this).data('reason'));
                $('#extraday').val($(this).data('extraday'));

                toggleAttendanceFields(status);

                $('#status').prop('disabled', false);
                $('#check_in_time').prop('disabled', false);
                $('#check_out_time').prop('disabled', false);
                $('#reason').prop('disabled', false);
                $('#attendanceForm button[type="submit"]').show();

                const dateVal = $(this).data('date');
                if (dateVal) {
                    const parts = dateVal.split('-');
                    $('#display_date').text(parts[2] + '/' + parts[1] + '/' + parts[0]);
                }

                $('#attendanceForm .text-danger').html('');
                $('#attendanceModal').modal('show');
            });

            // Submit attendance form
            $('#attendanceForm').on('submit', function(e) {
                e.preventDefault();

                // Get button and spinner elements
                const saveBtn = $('#saveBtn');
                const btnText = $('.btn-text');
                const saveSpinner = $('#saveSpinner');

                // Disable button and show loader
                saveBtn.prop('disabled', true);
                btnText.text('Saving...');
                saveSpinner.removeClass('d-none');

                var authToken = localStorage.getItem("authToken");
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                let formData = new FormData($('#attendanceForm')[0]);
                formData.append('selectedSubAdminId', selectedSubAdminId);

                // Clear any inline errors before new request
                $('#attendanceForm .text-danger').html('');

                $.ajax({
                    url: "{{ route('attendance.store') }}",
                    method: 'POST',
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    data: formData,
                    processData: false, // important for FormData
                    contentType: false, // important for FormData
                    success: function(res) {
                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        $('#attendanceModal').modal('hide');
                        Swal.fire({
                            title: 'Success!',
                            text: res.message,
                            icon: 'success',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // reload to show updated table
                        });
                    },
                    error: function(xhr) {

                        saveBtn.prop('disabled', false);
                        btnText.text('Save');
                        saveSpinner.addClass('d-none');
                        let response = xhr.responseJSON;
                        let errors = response ? response.errors : null;

                        if (errors) {
                            // Combine all errors into one string for Swal
                            let allErrors = [];
                            for (let key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    // Display specific error message in the corresponding div
                                    $(`#error_${key}`).text(errors[key][0]);
                                    allErrors.push(errors[key][0]);
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: allErrors.join('<br>'),
                                confirmButtonColor: '#ff9f43'
                            });
                        } else {
                            Swal.fire('Error', 'Something went wrong.', 'error');
                        }
                    }
                });
            });
// Also reset button state when modal is closed
$('#attendanceModal').on('hidden.bs.modal', function () {
    const saveBtn = $('#saveBtn');
    const btnText = $('.btn-text');
    const saveSpinner = $('#saveSpinner');

    saveBtn.prop('disabled', false);
    btnText.text('Save');
    saveSpinner.addClass('d-none');
});
        });
    </script>
@endpush
