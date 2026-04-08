@extends('layout.app')

@section('title', 'Profile')

@section('content')
    <style>
        .field-error {
            font-size: 13px;
            margin-top: 6px;
            line-height: 1.3;
        }

        .field-error:empty {
            display: none !important;
        }

        #validationErrors {
            margin-bottom: 16px;
        }

        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 15px !important
            }

            .profile-set {
                margin-bottom: 0 !important;
            }
        }
    </style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">    
                <h4>Profile</h4>
                <h6>User Profile</h6>
            </div>
            <a href="{{ route('auth.dashboard') }}" class="btn" style="background: #1b2850; color: #fff;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
        <form id="updateProfileForm" enctype="multipart/form-data" autocomplete="off">
            @csrf
            {{-- <div id="validationErrors" class="alert alert-danger d-none" role="alert">
                <ul id="validationErrorList" class="mb-0"></ul>
            </div> --}}
            <div class="card">
                <div class="card-body">
                    <div class="profile-set">
                        <div class="profile-head"></div>
                        <div class="profile-top">
                            <div class="profile-content">
                                <div class="profile-contentimg">
                                    <img src="{{ !empty($user->profile_image) ? $user->profile_image_url : asset('admin/assets/img/customer/customer5.jpg') }}"
                                        alt="img" id="blah">

                                    <div class="profileupload">
                                        <input type="file" name="profile_image" id="imgInp" accept=".jpg,.jpeg,.png,.gif,.webp,.bmp,image/*">
                                        <a href="javascript:void(0);">
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit-set.svg' }}"
                                                alt="img">
                                        </a>
                                    </div>
                                    <div class="invalid-feedback d-block field-error" data-field="profile_image"></div>
                                </div>
                                <div class="profile-contentname">
                                    <h2>{{ $user->name }}</h2>
                                    <h4>Update Your Photo and Personal Details.</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Name</label>
                                <input type="text" name="name" placeholder="William" value="{{ $user->name }}">
                                <div class="invalid-feedback d-block field-error" data-field="name"></div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="text" name="email" placeholder="william@example.com"
                                    value="{{ $user->email }}">
                                <div class="invalid-feedback d-block field-error" data-field="email"></div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" placeholder="+1452 876 5432"
                                    value="{{ $user->phone }}">
                                <div class="invalid-feedback d-block field-error" data-field="phone"></div>
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" name="password" class="pass-input" id="password"
                                        placeholder="********" autocomplete="new-password" value="">
                                    <span class="fas toggle-password fa-eye-slash"></span>
                                </div>
                                <div class="invalid-feedback d-block field-error" data-field="password"></div>
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-submit me-2">Submit</button>
                            <!-- <button type="reset" class="btn btn-cancel">Cancel</button> -->
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
            const $profileSubmitBtn = $("#updateProfileForm button[type='submit']");
            const profileSubmitBtnDefaultHtml = $profileSubmitBtn.html();

            function toggleProfileSubmitLoading(isLoading) {
                if (isLoading) {
                    $profileSubmitBtn
                        .prop("disabled", true)
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $profileSubmitBtn
                        .prop("disabled", false)
                        .html(profileSubmitBtnDefaultHtml);
                }
            }

            $("#password").val("");

            $("#imgInp").on("change", function() {
                var file = this.files && this.files[0] ? this.files[0] : null;
                var $fieldError = $('.field-error[data-field="profile_image"]');

                if (!file) {
                    $fieldError.text("");
                    return;
                }

                if (!file.type || file.type.indexOf("image/") !== 0) {
                    this.value = "";
                    $fieldError.text("Please select a valid image file.");
                    Swal.fire({
                        icon: "error",
                        title: "Invalid file",
                        text: "Only image files are allowed.",
                    });
                    return;
                }

                $fieldError.text("");
            });

            $("#updateProfileForm").submit(function(e) {
                e.preventDefault();

                if ($profileSubmitBtn.prop("disabled")) {
                    return;
                }

                var formData = new FormData(this);
                var $form = $(this);
                var $validationErrors = $("#validationErrors");
                var $validationErrorList = $("#validationErrorList");

                $validationErrors.addClass("d-none");
                $validationErrorList.html("");
                $form.find(".field-error").text("");
                $form.find(".is-invalid").removeClass("is-invalid");

                $.ajax({
                    url: "/api/updateProfile",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        toggleProfileSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                icon: "success",
                                title: "Profile Updated!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Update Failed",
                                text: "Failed to update profile.",
                            });
                        }
                    },
                    error: function(xhr) {
                        // console.log(xhr.responseJSON);
                        let errorMessage = "Error updating profile!";

                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;

                            Object.keys(errors).forEach(function(field) {
                                var fieldMessages = errors[field];
                                var inputField = $form.find('[name="' + field + '"]');
                                var fieldError = $form.find('.field-error[data-field="' + field + '"]');

                                fieldMessages.forEach(function(message) {
                                    $validationErrorList.append("<li>" + message + "</li>");
                                });

                                if (inputField.length) {
                                    inputField.addClass("is-invalid");
                                }

                                if (fieldError.length) {
                                    fieldError.text(fieldMessages.join(" "));
                                }
                            });

                            $validationErrors.removeClass("d-none");
                            return;
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error updating profile!",
                        });
                    },
                    complete: function() {
                        toggleProfileSubmitLoading(false);
                    }
                });
            });
        });
    </script>
@endpush
