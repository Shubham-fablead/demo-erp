@extends('layout.app')

@section('title', 'Edit Staff')

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
                <h4>Edit Staff</h4>

            </div>
        </div> --}}
         <div class="page-header ">
            <div class="page-title">
                <h4>Edit Staff</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('staff.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Customer Name -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            {{-- <label>Staff Name</label> --}}
                            <label>Staff Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" maxlength="80" id="customer_name"
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
                    <div class="col-lg-3 col-sm-6 col-6 d-none">
                        <div class="form-group ">
                            <label>Password <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="password" name="password" id="password" class="form-control"
                                    autocomplete="new-password" placeholder="Leave blank to keep current">
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
                                oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)" class="form-control">
                            <div class="text-danger error-phone"></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" id="country" name="country" class="form-control">
                            <div class="text-danger error-country"></div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" id="city" name="city" class="form-control">
                            <div class="text-danger error-city"></div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" id="pan_number" class="form-control">
                            <span class="text-danger error-pan_number"></span>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>GST Number</label>
                            <input type="text" id="gst_number" class="form-control">
                            <span class="text-danger error-gst_number"></span>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="address" name="address" class="form-control"></textarea>
                            <div class="text-danger error-address"></div>

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

                    <!-- Photo -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload">
                                <input type="file" id="avatar-input" name="avatar" accept="image/*">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                        alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <div class="text-danger error-avatar"></div>

                            <!-- Profile Image Preview -->
                            <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                <img id="avatar-preview" src="" alt="Profile Image"
                                    style="max-width: 100px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                        <a class="btn btn-cancel" href="{{ route('staff.list') }}">Cancel</a>
                    </div>
                </div>
                <hr>
                <div class="text-danger error-permissions mt-2"></div>
                {{--
                <h5 class="mt-4 fw-bold d-flex align-items-center">
                    PERMISSION :
                    <div class="form-check form-check-inline ms-3 mb-0">
                        <input class="form-check-input" type="radio" name="permission_type" id="withPermission"
                            value="1" required>
                        <label class="form-check-label" for="withPermission">With Permission</label>
                    </div>

                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="permission_type" id="withoutPermission"
                            value="0">
                        <label class="form-check-label" for="withoutPermission">Without Permission</label>
                    </div>
                </h5> --}}

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

                <div id="permissionsSection" style="display:none; margin-top:15px;">
                    <div class="form-group mb-3">
                        <label class="fw-normal">
                            <input type="checkbox" id="select_all_module"> Select All Module
                        </label>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="module-permission-table">
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
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
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
        const IMAGE_PATH = "{{ rtrim(env('ImagePath'), '/') }}";
        $(document).ready(function() {
            // Show/hide permission section based on radio selection
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
            let url = window.location.pathname;
            let customerId = url.split("/").pop();

            // AJAX call to get customer data
            $.ajax({
                url: `/api/getStaff/${customerId}`,
                type: "GET",
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    if (response.status) {
                        let customer = response.customer;

                        // Populate form fields
                        $("#role").val(customer.role || "");
                        // if (customer.haspermission) {
                        // console.log(customer.haspermission);
                        // console.log(customer.haspermission);

                        $('input[name="permission_type"][value="' + String(customer.haspermission) +
                            '"]').prop('checked', true);
                        // Use the shared change handler to toggle UI consistently
                        $('input[name="permission_type"]').trigger('change');
                        // }
                        $("#customer_name").val(customer.name);
                        $("#email").val(customer.email || ""); // Use empty string if email is null
                        $("#phone").val(customer.phone);
                        $("#gst_number").val(customer.gst_number);
                        $("#pan_number").val(customer.pan_number);
                        $("#staff_type").val(customer.staff_type || "");
                        $("#country").val(customer.details.country ||
                            ""); // Use empty string if country is null
                        $("#city").val(customer.details.city || ""); // Use empty string if city is null
                        $("#address").val(customer.details.address ||
                            ""); // Use empty string if address is null

                        // Show profile image if exists
                        if (customer.profile_image) {
                            $("#avatar-preview").attr("src", `${IMAGE_PATH}/storage/${customer.profile_image}`);
                            $("#avatar-preview-container").show(); // Make sure preview is visible
                        }

                        let modules = response.modules;
                        let permissions = response.permissions;

                        let tbody = "";
                        modules.forEach(module => {
                            let perm = permissions.find(p => p.module_id === module.id) || {};
                            if (module.module === "Staff") {
                                return; // continue to next loop
                            }
                            // Check if all 4 permissions are enabled
                            let isAllChecked = perm.view && perm.add && perm.edit && perm
                                .delete;

                            tbody += `
                                        <tr>
                            <td class="align-middle">
                                <i class="fa fa-folder-open text-primary me-2"></i> ${module.module}
                                <input type="hidden" name="modules[${module.id}][module_id]" value="${module.id}">
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][view]" value="0">
                                <input type="checkbox" name="modules[${module.id}][view]" value="1"
                                    data-module="${module.id}" data-type="view"
                                    class="permission-checkbox form-check-input" ${perm.view ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][add]" value="0">
                                <input type="checkbox" name="modules[${module.id}][add]" value="1"
                                    data-module="${module.id}" data-type="add"
                                    class="permission-checkbox form-check-input" ${perm.add ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][edit]" value="0">
                                <input type="checkbox" name="modules[${module.id}][edit]" value="1"
                                    data-module="${module.id}" data-type="edit"
                                    class="permission-checkbox form-check-input" ${perm.edit ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="hidden" name="modules[${module.id}][delete]" value="0">
                                <input type="checkbox" name="modules[${module.id}][delete]" value="1"
                                    data-module="${module.id}" data-type="delete"
                                    class="permission-checkbox form-check-input" ${perm.delete ? 'checked' : ''}>
                            </td>

                            <td class="text-center">
                                <input type="checkbox" class="check-all-row form-check-input" ${isAllChecked ? 'checked' : ''}>
                            </td>
                        </tr>`;
                        });


                        $("#module-permission-table tbody").html(tbody);

                        // Select all module checkboxes
                        $(document).on("change", "#select_all_module", function() {
                            $(".permission-checkbox").prop("checked", $(this).is(":checked"));
                        });

                        // Row-level "All" checkbox
                        $(document).on("change", ".check-all-row", function() {
                            const row = $(this).closest("tr");
                            const isChecked = $(this).is(":checked");
                            row.find(".permission-checkbox").prop("checked", isChecked);
                        });

                    } else {
                        Swal.fire("Error!", "Customer not found!", "error");

                    }
                },
                error: function() {
                    Swal.fire("Error!", "Customer not found!", "error");

                },
            });

            // Handle File Input Change (Live Preview)
            $("#avatar-input").change(function(event) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $("#avatar-preview").attr("src", e.target.result);
                    $("#avatar-preview-container").show();
                };
                reader.readAsDataURL(event.target.files[0]);
            });

            $("#updateCustomer").on("click", function(e) {
                e.preventDefault();
                $(".text-danger").html("");
                $(".error-permissions").html("");

                let hasError = false;

                // Staff Name validation
                let staffName = $("#customer_name").val().trim();
                if (staffName === "") {
                    $(".error-customer_name").html(" Staff name is required. ");
                    hasError = true;
                } else if (staffName.length < 3) {
                    $(".error-customer_name").html(" Staff name must be at least 3 characters. ");
                    hasError = true;
                } else if (staffName.length > 80) {
                    $(".error-customer_name").html(" Staff name must not exceed 80 characters. ");
                    hasError = true;
                }

                // Email validation
                let email = $("#email").val().trim();
                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email === "") {
                    $(".error-email").html(" Email is required. ");
                    hasError = true;
                } else if (!emailPattern.test(email)) {
                    $(".error-email").html(" Please enter a valid email address. ");
                    hasError = true;
                }

                // Password validation (optional)
                let password = $("#password").val().trim();
                if (password !== "") {
                    if (password.length < 8) {
                        $(".error-password").html(" Password must be at least 8 characters. ");
                        hasError = true;
                    }
                }

                // Phone validation
                let phone = $("#phone").val().trim();
                let phonePattern = /^[0-9]{10}$/;
                if (phone === "") {
                    $(".error-phone").html(" Phone number is required. ");
                    hasError = true;
                } else if (!phonePattern.test(phone)) {
                    $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                    hasError = true;
                }


                // Permission check
                if (!$('input[name="permission_type"]:checked').length) {
                    $(".error-permissions").html("** Please select With or Without Permission. **");
                    hasError = true;
                }

                // If errors exist, stop
                if (hasError) {
                    return false;
                }

                // If "With Permission" selected, ensure at least one permission checkbox is checked
                if ($('#withPermission').is(':checked')) {
                    if ($('.permission-checkbox:checked').length === 0) {
                        $(".error-permissions").html("** Please select at least one permission. **");
                        return false;
                    }
                }


                const $btn = $(this);
                const originalText = $btn.html();

                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop("disabled", true);

                let formData = new FormData();
                if (password !== "") {
                    formData.append("password", password);
                }
                formData.append("password", $("#password").val());
                formData.append("role", $("#role").val());
                formData.append("staff_name", $("#customer_name").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("gst_number", $("#gst_number").val());
                formData.append("pan_number", $("#pan_number").val());
                formData.append("staff_type", $("#staff_type").val());
                formData.append("country", $("#country").val());
                formData.append("city", $("#city").val());
                formData.append("address", $("#address").val());
                let permissionType = $('input[name="permission_type"]:checked').val();

                // Append to FormData
                formData.append("permission_type", permissionType);

                let avatar = $("#avatar-input")[0].files[0];
                if (avatar) {
                    formData.append("avatar", avatar);
                }

                // Collect modules and permissions
                let modules = {};
                $(".permission-checkbox").each(function() { // remove :checked
                    let moduleId = $(this).data("module");
                    let type = $(this).data("type");

                    if (!modules[moduleId]) {
                        modules[moduleId] = {
                            module_id: moduleId,
                            view: 0,
                            add: 0,
                            edit: 0,
                            delete: 0
                        };
                    }

                    // Always set 1 if checked, 0 if unchecked
                    modules[moduleId][type] = $(this).is(":checked") ? 1 : 0;
                });

                // Append module permissions to formData
                let i = 0;
                Object.values(modules).forEach((mod) => {
                    formData.append(`modules[${i}][module_id]`, mod.module_id);
                    formData.append(`modules[${i}][view]`, mod.view);
                    formData.append(`modules[${i}][add]`, mod.add);
                    formData.append(`modules[${i}][edit]`, mod.edit);
                    formData.append(`modules[${i}][delete]`, mod.delete);
                    i++;
                });

                $.ajax({
                    url: `/api/updateStaff/${customerId}`,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        Authorization: `Bearer ${authToken}`,
                    },
                    success: function(response) {
                        $btn.html(originalText).prop("disabled", false);
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Staff updated successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/staff";
                            });
                        }
                    },
                    error: function(xhr) {
                        $btn.html(originalText).prop("disabled", false);
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $(".error-" + key).html(value[0]);
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
