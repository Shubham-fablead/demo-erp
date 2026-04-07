@extends('layout.app')

@section('title', 'Edit Branch')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Branch</h4>

            </div>
            <div class="page-btn">
                <a href="{{ route('subbranch.list') }}" class="btn btn-added">
                    Back
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <input type="hidden" name="id" id="id">
                    <!-- Customer Name -->
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Branch Name <span class="text-danger">*</span></label>
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
                    <div class="col-lg-3 col-sm-6 col-6">
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

                    <!-- Address -->
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" id="address" class="form-control"></textarea>
                            <div class="text-danger error-address"></div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Photo</label>
                            <div class="image-upload">
                                <input type="file" id="avatar-input" name="avatar"accept=".jpg,.jpeg,.png">
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
                        <a class="btn btn-cancel" href="{{ route('subbranch.list') }}">Cancel</a>
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
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            let url = window.location.pathname;
            let customerId = url.split("/").pop();

            $.ajax({
                url: `/api/getSubbranch/${customerId}`,
                type: "GET",
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    if (response.status && response.data) {
                        const customer = response.data;
                        const details = customer.user_detail ?? {};

                        // Populate form fields
                        $("#customer_name").val(customer.name || "");
                        $("#id").val(customer.id || "");
                        $("#email").val(customer.email || "");
                        $("#phone").val(customer.phone || "");
                        $("#country").val(details.country || "");
                        $("#city").val(details.city || "");
                        $("#address").val(details.address || "");

                        // Show profile image if it exists
                        if (customer.profile_image) {
                            $("#avatar-preview").attr("src",
                                `{{ env('ImagePath') . '/storage/' }}${customer.profile_image}`);
                            // $("#avatar-preview").attr("src", `/storage/${customer.profile_image}`);
                            $("#avatar-preview-container").show();
                        } else {
                            $("#avatar-preview-container").hide();
                        }
                    } else {
                        Swal.fire("Error!", "Branch not found!", "error").then(() => {
                            window.location.href = "{{ route('subbranch.list') }}";
                        });
                    }
                },
                error: function() {
                    Swal.fire("Error!", "Branch not found!", "error").then(() => {
                        window.location.href = "{{ route('subbranch.list') }}";
                    });
                },
            });

            // Handle File Input Change (Live Preview)
            $("#avatar-input").change(function(event) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $("#avatar-preview").attr("src", e.target.result);
                    $("#avatar-preview-container").show();
                };
                reader.readAsDataURL(event.target.files[0]);
            });

            $("#updateCustomer").on("click", function(e) {
                e.preventDefault();
                const $btn = $(this);
                const originalText = $btn.html();

                // Disable button and show loader
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop("disabled", true);
                // console.log($("#country").val());

                let formData = new FormData();
                formData.append("password", $("#password").val());
                formData.append("id", $("#id").val());
                formData.append("customer_name", $("#customer_name").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("country", $("#country").val());
                formData.append("city", $("#city").val());
                formData.append("address", $("#address").val());

                let avatar = $("#avatar-input")[0].files[0];
                // console.log(avatar);
                if (avatar) {
                    formData.append("avatar", avatar);
                }

                $.ajax({
                    url: `/api/updateSubbranch`,
                    type: "POST", // Laravel doesn't support `PUT` directly with FormData, so use POST with `_method` as PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        Authorization: `Bearer ${authToken}`,
                    },
                    success: function(response) {
                        $btn.html(originalText).prop("disabled", false); // Restore button
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Branch update successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/sub-branch";
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


                $("#updateCustomer").on("click", function(e) {
                    e.preventDefault();
                    $(".text-danger").html("");
                    $(".error-permissions").html("");

                    let hasError = false;

                    // Staff Name validation
                    let BranchName = $("#customer_name").val().trim();
                    if (BranchName === "") {
                        $(".error-customer_name").html(" Branch name is required. ");
                        hasError = true;
                    } else if (BranchName.length < 3) {
                        $(".error-customer_name").html(
                            " Branch name must be at least 3 characters. ");
                        hasError = true;
                    } else if (BranchName.length > 80) {
                        $(".error-customer_name").html(
                            " Branch name must not exceed 80 characters. ");
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
                    if (hasError) {
                        return false;
                    }
                });

                $("#avatar-input").on("change", function() {

                    let file = this.files[0];
                    if (!file) return;

                    let allowedTypes = [
                        "image/jpeg",
                        "image/png",
                        "image/jpg",
                        "image/webp"
                    ];

                    if (!allowedTypes.includes(file.type)) {

                        $(".error-avatar").html(
                            "Only JPG, JPEG, PNG, WEBP images allowed."
                        );

                        $(this).val("");
                        $("#avatar-preview-container").hide();
                        return;
                    }

                    $(".error-avatar").html("");

                    // preview
                    let reader = new FileReader();
                    reader.onload = function(e) {
                        $("#avatar-preview").attr("src", e.target.result);
                        $("#avatar-preview-container").show();
                    };
                    reader.readAsDataURL(file);
                });
            });




        });
    </script>
@endpush
