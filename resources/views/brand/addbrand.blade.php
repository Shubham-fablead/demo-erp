@extends('layout.app')

@section('title', 'Add Brand')

@section('content')

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Brand</h4>
            <!-- <h6>Create new Brand</h6> -->
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="addbrand">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name">
                            <span class="error_name text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label> Brand Image</label>
                            <div class="image-upload">
                                <input type="file" name="logo" id="logo" accept="image/*">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/upload.svg'}}" alt="img">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <div class="image-preview" style="margin-top: 10px;"></div>
                            <span class="error_logo text-danger"></span>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <a href="javascript:void(0);" class="btn btn-submit submit me-2">Submit</a>
                        <a href="{{route('brand.list')}}" class="btn btn-cancel">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
@push('js')
<script>
    document.getElementById("logo").addEventListener("change", function(event) {
        var file = event.target.files[0]; // Get the selected file
        var previewDiv = document.querySelector(".image-preview"); // Select the preview div

        if (file) {
            var reader = new FileReader(); // Create a FileReader to read the file
            reader.onload = function(e) {
                previewDiv.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100px; max-height: 100px; border-radius: 5px; box-shadow: 2px 2px 10px rgba(0,0,0,0.1);">`;
            };
            reader.readAsDataURL(file); // Read file as data URL
        } else {
            previewDiv.innerHTML = ""; // Clear preview if no file is selected
        }
    });
    $(document).ready(function() {
        const $brandSubmitBtn = $('#addbrand .submit');
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

        $(document).on('click', '.submit', function(e) {
            e.preventDefault();
            if ($brandSubmitBtn.hasClass('disabled')) {
                return;
            }
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            var authToken = localStorage.getItem("authToken");
            let formData = new FormData($('#addbrand')[0]);
            if (selectedSubAdminId) {
                formData.append("sub_admin_id", selectedSubAdminId);
            }
            $('.error_name').text('');

            $.ajax({
                url: "/api/addBrand", // Update this with your actual route
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
                    if (response.status) { // Check 'status' instead of 'success'
                        Swal.fire({
                            title: "Success!",
                            text: "Brand added successfully!",
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('brand.list') }}";
                            }
                        });
                    }
                },
                // error: function(xhr) {
                //     if (xhr.status === 422) {
                //         let errors = xhr.responseJSON.errors;
                //         $.each(errors, function(key, value) {
                //             $('.error_' + key).text(value[0]);
                //         });
                //     }
                // }
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        // Clear all error texts first
                        $('.error_name, .error_logo').text('');

                        $.each(errors, function(key, value) {
                            // For image array errors like images.0, images.1 etc, map them to 'images'
                            let errorKey = key.split('.')[0]; // Take 'images' from 'images.0'

                            // Append errors if multiple messages for same field
                            let errorMsg = value.join(' ');

                            $('.error_' + errorKey).text(errorMsg);
                        });
                    }
                },
                complete: function() {
                    toggleBrandSubmitLoading(false);
                }
            });
        });
    });
</script>
@endpush
