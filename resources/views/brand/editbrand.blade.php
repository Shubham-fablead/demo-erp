@extends('layout.app')

@section('title', 'Edit Brand')

@push('css')
    <style>
        .brand-preview-box {
            width: 220px;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            padding: 10px;
            position: relative;
        }

        .brand-preview-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 6px;
        }

        .image-preview-container {
            position: relative;
            display: inline-block;
        }

        .remove-image {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            z-index: 10;
            transition: all 0.3s;
        }

        .remove-image:hover {
            background: #c82333;
            transform: scale(1.1);
        }
    </style>
@endpush

@section('content')
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Edit Brand</h4>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="editbrand" enctype="multipart/form-data">
                    <input type="hidden" name="brand_id" id="brand_id" value="{{ $brand->id ?? '' }}">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Brand Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ $brand->name ?? '' }}">
                                <span class="error_name text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label>Brand Image</label>
                                <div class="image-upload">
                                    <input type="file" name="image" id="image" class="form-control"
                                        accept="image/*">
                                    <div class="image-uploads">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                            alt="img">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                {{-- <small class="text-muted">Leave empty to keep current image. Max size: 2MB</small> --}}
                            </div>
                        </div>

                        <div class="col-12 mt-2">
                            <div class="image-preview-container">
                                <div class="brand-preview-box">
                                    <img id="image-preview"
                                        src="{{ $brand->logo ? env('ImagePath') . '/storage/' . $brand->logo : '' }}"
                                        alt="Brand Image">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-12 mt-3">
                            <a href="javascript:void(0);" class="btn btn-submit submit me-2">Update Brand</a>
                            <a href="{{ route('brand.list') }}" class="btn btn-cancel">Cancel</a>
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
            const $brandSubmitBtn = $('#editbrand .submit');
            const brandSubmitBtnDefaultHtml = $brandSubmitBtn.html();

            function toggleBrandSubmitLoading(isLoading) {
                if (isLoading) {
                    $brandSubmitBtn
                        .addClass('disabled')
                        .attr('aria-disabled', 'true')
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $brandSubmitBtn
                        .removeClass('disabled')
                        .removeAttr('aria-disabled')
                        .html(brandSubmitBtnDefaultHtml);
                }
            }

            // ✅ Image preview functionality
            $('#image').on('change', function(e) {
                const file = e.target.files[0];

                if (!file) return;

                const allowedTypes = [
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/gif',
                    'image/webp'
                ];

                if (!allowedTypes.includes(file.type)) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select valid image (JPEG, PNG, GIF, WEBP)',
                        icon: 'error',
                        confirmButtonColor: '#ff9f43'
                    });

                    $(this).val('');
                    return;
                }

                const maxSize = 2 * 1024 * 1024;
                if (file.size > maxSize) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Image size should not exceed 2MB',
                        icon: 'error',
                        confirmButtonColor: '#ff9f43'
                    });

                    $(this).val('');
                    return;
                }

                // ✅ Replace old image with new preview
                const reader = new FileReader();

                reader.onload = function(e) {
                    $('#image-preview')
                        .hide()
                        .attr('src', e.target.result)
                        .fadeIn(200);
                };

                reader.readAsDataURL(file);
            });
            // ✅ Form submission
            $(document).on('click', '.submit', function(e) {
                e.preventDefault();
                if ($brandSubmitBtn.hasClass('disabled')) {
                    return;
                }

                var authToken = localStorage.getItem("authToken");
                let formData = new FormData($('#editbrand')[0]);

                // Clear previous errors
                $('.error_name').text('');

                $.ajax({
                    url: "/api/updateBrand",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        toggleBrandSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            Swal.fire({
                                title: "Success!",
                                text: response.message || "Brand updated successfully!",
                                icon: "success",
                                confirmButtonColor: "#ff9f43",
                                confirmButtonText: "OK"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('brand.list') }}";
                                }
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: response.message || "Failed to update brand",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('.error_' + key).text(value[0]);
                            });

                            Swal.fire({
                                title: "Validation Error!",
                                text: "Please check the form for errors",
                                icon: "warning",
                                confirmButtonColor: "#ff9f43"
                            });
                        } else if (xhr.status === 401) {
                            Swal.fire({
                                title: "Unauthorized!",
                                text: "Please login again",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            }).then(() => {
                                window.location.href = "/login";
                            });
                        } else {
                            Swal.fire({
                                title: "Error!",
                                text: xhr.responseJSON?.message ||
                                    "Something went wrong. Please try again.",
                                icon: "error",
                                confirmButtonColor: "#ff9f43"
                            });
                        }
                    },
                    complete: function() {
                        toggleBrandSubmitLoading(false);
                    }
                });
            });
        });

        // ✅ Function to remove current image
        function removeCurrentImage() {
            Swal.fire({
                title: "Remove Image?",
                text: "Are you sure you want to remove the current brand image?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, remove it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    // Set hidden field to indicate image should be removed
                    $('#remove_image').val('1');
                    // Hide the current image preview
                    $('.image-preview-container').fadeOut(300);

                    Swal.fire({
                        title: "Removed!",
                        text: "Current image will be removed when you update the brand.",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }
    </script>
@endpush
