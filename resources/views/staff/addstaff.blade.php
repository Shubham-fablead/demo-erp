@extends('layout.app')

@section('title', 'Add Staff')

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

        .gst-input-wrap {
            position: relative;
        }

        .gst-loader {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            display: none;
            color: #ff9f43;
            pointer-events: none;
        }

        #gst_number.loading {
            padding-right: 36px;
        }

        .staff-submit-loader {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .staff-submit-loader .loader-box {
            background: #fff;
            border-radius: 8px;
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
            color: #1b2850;
            font-weight: 600;
        }
    </style>
    <div class="content">
        {{-- <div class="page-header">
        <div class="page-title">
            <h4>Add Staff</h4>

            </div>
        </div>
    </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Add Staff</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('staff.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                    Back</a></br>
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="customerForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- Customer Name -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                {{-- <label>Staff Name</label> --}}
                                <label>Staff Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" id="customer_name" maxlength="80"
                                    class="form-control">
                                <div class="text-danger error-customer_name"></div>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" autocomplete="off">
                                <div class="text-danger error-email"></div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="password" name="password" id="password" class="form-control"
                                        autocomplete="off">
                                    <span class="position-absolute top-50 end-0 translate-middle-y me-2"
                                        style="cursor: pointer;" onclick="togglePasswordVisibility()">
                                        <i id="togglePasswordIcon" class="fa fa-eye"></i>
                                    </span>
                                </div>
                                <div class="text-danger error-password"></div>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" id="phone" maxlength="10" pattern="\d{10}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)"
                                    class="form-control">
                                <div class="text-danger error-phone"></div>
                            </div>
                        </div>

                        <!-- Country -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" id="country" class="form-control">
                                <div class="text-danger error-country"></div>
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" id="city" class="form-control">
                                <div class="text-danger error-city"></div>
                            </div>
                        </div>

                        <!-- PAN Number -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>PAN Number</label>
                                <input type="text" name="pan_number" id="pan_number" class="form-control">
                                <span class="text-danger error-pan_number"></span>
                            </div>
                        </div>

                        <!-- GST Number -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>GST Number</label>
                                <div class="gst-input-wrap">
                                    <input type="text" name="gst_number" id="gst_number" class="form-control">
                                    <span id="gst-loader" class="gst-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <span class="text-danger error-gst_number"></span>
                            </div>
                        </div>

                        <!-- Staff Type -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Staff Type</label>
                                <select name="staff_type" id="staff_type" class="form-control">
                                    <option value="">-- Select Staff Type --</option>
                                    <option value="raw_material">Raw Material</option>
                                    <option value="product">Product</option>
                                    <option value="other">Other</option>
                                </select>
                                <span class="text-danger error-staff_type"></span>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                                <div class="text-danger error-address"></div>
                            </div>
                        </div>

                        <!-- Avatar Upload -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Photo</label>
                                <div class="image-upload">
                                    <input type="file" name="avatar" id="avatar" class="form-control"
                                        accept="image/*">
                                    <div class="image-uploads">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                            alt="Upload Icon">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                <div class="text-danger error-avatar"></div>

                                <!-- Avatar Preview -->
                                <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                    <img id="avatar-preview" src="" alt="Avatar Preview"
                                        style="max-width: 100px; border-radius: 8px;padding:4px;">
                                </div>
                            </div>
                        </div>

                        <!-- Submit & Cancel Buttons -->
                        <div class="col-lg-12">
                            <button type="submit" id="submitCustomerBtn" class="btn btn-submit me-2">Submit</button>
                            <a href="{{ route('staff.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>
                    <hr>
                    <div class="text-danger error-permissions mt-2"></div>

                    <div class="d-flex align-items-center mt-4">
                        <h5 class="fw-bold mb-0 me-3">PERMISSION:</h5>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                                value="1">
                            <label class="form-check-label" for="withPermission">With Permission</label>
                        </div>

                        <div class="form-check form-check-inline mb-0">
                            <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                                value="0">
                            <label class="form-check-label" for="withoutPermission">Without Permission</label>
                        </div>
                    </div>

                    <!-- Permissions Section -->
                    <div id="permissionsSection" style="display:none; margin-top:15px;">
                        <div class="form-group mb-3">
                            <label class="fw-normal">
                                <input type="checkbox" id="select_all_module"> Select All Module
                            </label>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th class="text-start">Module Name</th>
                                        <th>View</th>
                                        <th>Insert</th>
                                        <th>Edit</th>
                                        <th>Delete</th>
                                        <th>All</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($modules as $module)
                                        @if ($module->module !== 'Staff')
                                            <tr>
                                                <td class="align-middle">
                                                    <i class="fa fa-folder-open text-primary me-2"></i>
                                                    {{ $module->module }}
                                                    <input type="hidden" name="modules[{{ $module->id }}][module_id]"
                                                        value="{{ $module->id }}">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][view]"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][add]"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][edit]"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" name="modules[{{ $module->id }}][delete]"
                                                        class="permission-checkbox form-check-input">
                                                </td>
                                                <td class="text-center">
                                                    <input type="checkbox" class="check-all-row form-check-input">
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="staffSubmitLoader" class="staff-submit-loader">
            <div class="loader-box">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <span>Please wait...</span>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
    <script>
        $(document).ready(function() {

            $('#avatar').on('change', function(e) {
                const file = e.target.files[0];
                const $previewContainer = $('#avatar-preview-container');
                const $previewImage = $('#avatar-preview');
                const $errorDiv = $('.error-avatar');

                // Clear previous errors
                $errorDiv.html('');

                if (!file) {
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    $errorDiv.html('Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed.');
                    $(this).val(''); // Clear the input
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Validate file size (max 2MB)
                if (file.size > 2 * 1024 * 1024) {
                    $errorDiv.html('Image must be less than 2MB.');
                    $(this).val('');
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Show preview using FileReader
                const reader = new FileReader();
                reader.onload = function(event) {
                    $previewImage.attr('src', event.target.result);
                    $previewContainer.fadeIn(); // or .show() if you prefer
                };
                reader.onerror = function() {
                    $errorDiv.html('Error reading file.');
                    $previewContainer.hide();
                };
                reader.readAsDataURL(file);
            });

            $('input[name="permission_type"]').on('change', function() {
                if ($('#withPermission').is(':checked')) {
                    $('#permissionsSection').slideDown();
                } else {
                    $('#permissionsSection').slideUp();
                    // Optional: uncheck all checkboxes when hiding
                    $('#permissionsSection').find('input[type="checkbox"]').prop('checked', false);
                }
            });
        });
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            let $gstLoader = $('#gst-loader');

            const $loader = $("#staffSubmitLoader");

            function showSubmitLoader() {
                $loader.css("display", "flex");
            }

            function hideSubmitLoader() {
                $loader.hide();
            }

            $("#customerForm").submit(function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                $(".text-danger").html("");
                $(".error-permissions").html("");

                let hasError = false;

                // ✅ Permission check
                if (!$('input[name="permission_type"]:checked').length) {
                    $(".error-permissions").html("** Please select With or Without Permission. **");
                    hasError = true;
                }

                // ✅ Staff Name validation
                let customer_name = $("#customer_name").val().trim();
                if (customer_name === "") {
                    $(".error-customer_name").html(" Staff name is required. ");
                    hasError = true;
                } else if (customer_name.length < 3) {
                    $(".error-customer_name").html(" Staff name must be at least 3 characters. ");
                    hasError = true;
                } else if (customer_name.length > 80) {
                    $(".error-customer_name").html(" Staff name must not exceed 80 characters. ");
                    hasError = true;
                }

                // ✅ Email validation
                let email = $("#email").val().trim();
                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email === "") {
                    $(".error-email").html(" Email is required. ");
                    hasError = true;
                } else if (!emailPattern.test(email)) {
                    $(".error-email").html(" Please enter a valid email address. ");
                    hasError = true;
                }

                // ✅ Password validation
                let password = $("#password").val().trim();
                if (password === "") {
                    $(".error-password").html(" Password is required. ");
                    hasError = true;
                } else if (password.length < 8) {
                    $(".error-password").html(" Password must be at least 8 characters. ");
                    hasError = true;
                }

                // ✅ Phone validation
                let phone = $("#phone").val().trim();
                let phonePattern = /^[0-9]{10}$/; // exactly 10 digits
                if (phone === "") {
                    $(".error-phone").html(" Phone number is required. ");
                    hasError = true;
                } else if (!phonePattern.test(phone)) {
                    $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                    hasError = true;
                }

                // ✅ File upload validation
                let avatar = $("#avatar")[0].files[0];
                if (avatar) {
                    let allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
                    if (!allowedTypes.includes(avatar.type)) {
                        $(".error-avatar").html(
                            " Only image files (JPEG, PNG, JPG, WEBP, GIF) are allowed. ");
                        hasError = true;
                    } else if (avatar.size > 2 * 1024 * 1024) { // 2MB
                        $(".error-avatar").html(" Image size must not exceed 2MB. ");
                        hasError = true;
                    }
                }

                // Stop form submission if errors exist
                if (hasError) {
                    return false;
                }

                const $btn = $("#submitCustomerBtn");
                const originalText = $btn.html();

                // Show spinner and disable button
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop("disabled", true);

                if ($('#withPermission').is(':checked')) {
                    let permissionSelected = $('.permission-checkbox:checked').length > 0;

                    if (!permissionSelected) {
                        $(".error-permissions").html("** Please select at least one permission. **");
                        $btn.html(originalText).prop("disabled", false);
                        return false;
                    } else {
                        $(".error-permissions").html("");
                    }
                } else {
                    // If "Without Permission" is selected, clear error
                    $(".error-permissions").html("");
                }

                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                if (selectedSubAdminId) {
                    formData.append("sub_admin_id", selectedSubAdminId);
                }
                $.ajax({
                    url: "/api/createStaff", // Ensure API route is correct
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        $btn.html(originalText).prop("disabled", false); // Restore button
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Staff added successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/staff";
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop("disabled", false); // Restore button
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $(".error-" + key).html(value[
                                    0]); // Show error below each field
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: "Something went wrong. Please try again.",
                                icon: "error",
                                confirmButtonText: "OK"
                            });
                        }
                    },
                });
            });

              $('#gst_number').on('input', function() {
                let $gstInput = $(this);
                let $errorDiv = $('.error-gst_number');

                var normalizedGst = $(this).val().toUpperCase().replace(/[^0-9A-Z]/g, '').substring(0, 15);
                $(this).val(normalizedGst);

                var gst = normalizedGst.trim();
                if (!/^[0-9A-Z]{15}$/.test(gst)) {
                    $gstLoader.hide();
                    $gstInput.removeClass('loading');
                    return;
                }

                $.ajax({
                    url: '/api/fetch-gst-details',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        gst_number: gst
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Authorization": "Bearer " + authToken,
                    },
                    beforeSend: function() {
                        $gstLoader.show();
                        $gstInput.addClass('loading');
                        $gstInput.prop('readonly', true);
                        $errorDiv.html(
                            '<span style="color: #1B2850;"><i class="fas fa-spinner fa-spin"></i> Fetching details...</span>'
                        );
                    },
                    success: function(res) {
                        $errorDiv.html('');
                        if (res.error) {
                            $errorDiv.html(
                                '<span class="text-danger">GST details not found.</span>');
                            return;
                        }

                        // Populate fields based on API response
                        $('#customer_name').val(res.legal_name || '');
                        $('#address').val(res.primary_address || '');
                        $('#city').val(res.city || '');
                        // $('#state_code').val(res.state || '');
                        $('#country').val(res.country || '');

                        // Extract PAN from GST (chars 3 to 12)
                        if (gst.length >= 12) {
                            const pan = gst.substring(2, 12);
                            $('#pan_number').val(pan);
                        }
                    },
                    error: function(xhr, status, error) {
                        $errorDiv.html(
                            '<span class="text-danger">Failed to fetch GST details.</span>');
                        console.error('GST fetch failed', xhr, status, error);
                    },
                    complete: function() {
                        $gstLoader.hide();
                        $gstInput.removeClass('loading');
                        $gstInput.prop('readonly', false);
                    }
                });
            });

        });
    </script>
    <script>
        $(document).ready(function() {
            // When 'Select All Module' is clicked
            $('#select_all_module').on('change', function() {
                const isChecked = $(this).is(':checked');
                $('.permission-checkbox, .check-all-row').prop('checked', isChecked);
            });

            // When 'All' checkbox in each row is clicked
            $('.check-all-row').on('change', function() {
                const isChecked = $(this).is(':checked');
                const row = $(this).closest('tr');
                row.find('.permission-checkbox').prop('checked', isChecked);
            });

            // If any permission checkbox is changed manually, update the 'All' checkbox in that row
            $('.permission-checkbox').on('change', function() {
                const row = $(this).closest('tr');
                const allChecked = row.find('.permission-checkbox').length === row.find(
                    '.permission-checkbox:checked').length;
                row.find('.check-all-row').prop('checked', allChecked);
            });

            // Sync master checkbox if all checkboxes are checked or unchecked
            function syncSelectAllModule() {
                const totalPermissions = $('.permission-checkbox').length;
                const totalChecked = $('.permission-checkbox:checked').length;
                $('#select_all_module').prop('checked', totalPermissions === totalChecked);
            }

            // Call sync function when any permission checkbox is changed
            $('.permission-checkbox, .check-all-row').on('change', syncSelectAllModule);
        });
    </script>
@endpush
