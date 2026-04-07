@extends('layout.app')

@section('title', 'Category Add')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Add Product Category</h4>
            <!-- <h6>Create new product Category</h6> -->
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form id="categoryForm">
                <div class="row">
                    <!-- <div class="col-12"> -->
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="category_name">Category Name <span class="text-danger">*</span> </label>
                            <input type="text" name="name" id="name" class="form-control" required>
                            <span class="error_name text-danger"></span>
                        </div>
                    </div>
                    <!-- </div> -->
                    <!-- <div class="col-12"> -->
                    <div class="col-lg-12">
                        <div class="form-group">
                            <label for="category_image">Category Image</label>
                            <div class="image-upload">
                                <input type="file" name="image" id="image" class="form-control" accept="image/*">
                                <div class="image-uploads">
                                    <img src="{{ env('ImagePath').'admin/assets/img/icons/upload.svg' }}" alt="Upload Icon">
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                            <div class="image-preview" style="margin-top: 10px;"></div>
                            <span class="error_image text-danger"></span>
                        </div>
                        <!-- </div> -->
                    </div>

                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit submit me-2">Submit</button>
                        <a href="{{route('category.list')}}" class="btn btn-cancel">Cancel</a></br>
                        <span class="success_submit text-danger"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>
@endsection
@push('js')
<script>
    document.getElementById("image").addEventListener("change", function(event) {
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
        const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
        const $categorySubmitBtn = $('#categoryForm .submit');
        const categorySubmitBtnDefaultHtml = $categorySubmitBtn.html();

        function toggleCategorySubmitLoading(isLoading) {
            if (isLoading) {
                $categorySubmitBtn
                    .prop('disabled', true)
                    .addClass('disabled')
                    .attr('aria-disabled', 'true')
                    .html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                    );
            } else {
                $categorySubmitBtn
                    .prop('disabled', false)
                    .removeClass('disabled')
                    .removeAttr('aria-disabled')
                    .html(categorySubmitBtnDefaultHtml);
            }
        }

        $(document).on('click', '.submit', function(e) {
            e.preventDefault();
            if ($categorySubmitBtn.prop('disabled')) {
                return;
            }
            var authToken = localStorage.getItem("authToken");
            let formData = new FormData($('#categoryForm')[0]);
            if (selectedSubAdminId) {
                formData.append("sub_admin_id", selectedSubAdminId);
            }
            $('.error_name').text('');

            $.ajax({
                url: "/api/addcategory", // Update this with your actual route
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    toggleCategorySubmitLoading(true);
                },
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        Swal.fire({
                            title: "Success!",
                            text: "Category added successfully!",
                            icon: "success",
                            confirmButtonColor: "#ff9f43",
                            confirmButtonText: "OK"
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('category.list') }}";
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
                        $('.error_name, .error_image').text('');

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
                    toggleCategorySubmitLoading(false);
                }

            });
        });
    });
</script>
@endpush
