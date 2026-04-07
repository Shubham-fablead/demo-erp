@extends('layout.app')

@section('title', 'Sub Branch')

@section('content')
    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }

            .password-group .input-group-text {
                /* background: #fff; */
                border-left: 0;
            }

            .password-group input {
                border-right: 0;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Add Sub Branch</h4>
            </div>
            <div class="breadcrumb">
                <a class="btn btn-primary btn-sm" href="{{ route('subbranch.list') }}">Back</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="subbranchForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
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
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10)" maxlength="10"
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

                        <!-- Address -->
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                                <div class="text-danger error-address"></div>
                            </div>
                        </div>

                        <!-- Avatar Upload -->
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Photo</label>
                                <div class="image-upload">
                                    <input type="file" id="avatar" name="avatar"
                                        accept="image/jpeg,image/png,image/jpg,image/webp">
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
                                        style="max-width: 100px; border-radius: 8px;">
                                </div>
                            </div>
                        </div>

                        <!-- Submit & Cancel Buttons -->
                        <div class="col-lg-12">
                            <button type="submit" id="submitCustomerBtn" class="btn btn-submit me-2">Submit</button>
                            <a href="{{ route('subbranch.list') }}" class="btn btn-cancel">Cancel</a>
                        </div>
                    </div>

                </form>
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


            // Live image preview
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

                // Validate file type (optional – you can reuse the same rules as in form validation)
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
                    $errorDiv.html('Image size must not exceed 2MB.');
                    $(this).val('');
                    $previewContainer.hide();
                    $previewImage.attr('src', '');
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(ev) {
                    $previewImage.attr('src', ev.target.result);
                    $previewContainer.fadeIn(); // or .show()
                };
                reader.onerror = function() {
                    $errorDiv.html('Error reading file.');
                    $previewContainer.hide();
                };
                reader.readAsDataURL(file);
            });
            // Handle form submission
            $("#subbranchForm").submit(function(e) {
                e.preventDefault(); // Prevent default form submission
                const $btn = $("#submitCustomerBtn");
                const originalText = $btn.html();
                if ($btn.prop("disabled")) {
                    return;
                }

                let formData = new FormData(this);
                $(".text-danger").html(""); // Clear previous error messages


                let hasError = false;
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
                let email = $("#email").val().trim();
                let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email === "") {
                    $(".error-email").html(" Email is required. ");
                    hasError = true;
                } else if (!emailPattern.test(email)) {
                    $(".error-email").html(" Please enter a valid email address. ");
                    hasError = true;
                }

                let phone = $("#phone").val().trim();
                let phonePattern = /^[0-9]{10}$/; // exactly 10 digits
                if (phone === "") {
                    $(".error-phone").html(" Phone number is required. ");
                    hasError = true;
                } else if (!phonePattern.test(phone)) {
                    $(".error-phone").html(" Please enter a valid 10-digit phone number. ");
                    hasError = true;
                }


                let password = $("#password").val().trim();
                if (password === "") {
                    $(".error-password").html(" Password is required. ");
                    hasError = true;
                } else if (password.length < 8) {
                    $(".error-password").html(" Password must be at least 8 characters. ");
                    hasError = true;
                }

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

                $.ajax({
                    url: "/api/createSubbranch", // Ensure API route is correct
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $btn.html(
                                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                            )
                            .prop("disabled", true);
                    },
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: "Sub Branch added successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/sub-branch";
                            });
                        }
                    },
                    error: function(xhr) {
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
                    complete: function() {
                        $btn.html(originalText).prop("disabled", false);
                    }
                });
            });

        });
    </script>
@endpush
