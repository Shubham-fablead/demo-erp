@extends('layout.app')

@section('title', 'Edit Customer')

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
            <h4>Edit Customer</h4>

            </div>
        </div>
    </div> --}}
    <div class="page-header ">
            <div class="page-title">
                <h4>Edit Customer</h4>
            </div>
             <div class="back-button">
                <a href="{{ route('customer.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="customer_name" maxlength="80" class="form-control">
                            <div class="text-danger error-customer_name"></div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" id="email" class="form-control">
                            <div class="text-danger error-email"></div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Phone <span class="text-danger">*</span></label>
                            <input type="text" id="phone" maxlength="10" class="form-control">
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
                            <label>State Code</label>
                            <input type="text" id="state_code" name="state_code" class="form-control">
                            <div class="text-danger error-state_code"></div>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>PAN Number</label>
                            <input type="text" id="pan_number" maxlength="10" class="form-control">
                            <span class="text-danger error-pan_number"></span>

                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>GST Number</label>
                            <input type="text" id="gst_number" maxlength="15" class="form-control">
                            <span class="text-danger error-gst_number"></span>

                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-12 col-12">
                        <div class="form-group">
                            <label>Address</label>
                            <textarea id="address" name="address" class="form-control"></textarea>
                            <div class="text-danger error-address"></div>

                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-12 col-12">
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
                                    style="max-width: 150px; border-radius: 8px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <button type="submit" class="btn btn-submit me-2" id="updateCustomer">Update</button>
                        <a class="btn btn-cancel" href="{{ route('customer.list') }}">Cancel</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var authToken = localStorage.getItem("authToken");
            let url = window.location.pathname;
            let customerId = url.split("/").pop();

            // AJAX call to get customer data
            $.ajax({
                url: `/api/getCustomer/${customerId}`,
                type: "GET",
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                success: function(response) {
                    if (response.status) {
                        let customer = response.customer;

                        // Populate form fields
                        $("#customer_name").val(customer.name);
                        $("#email").val(customer.email || ""); // Use empty string if email is null
                        $("#phone").val(customer.phone);
                        $("#pan_number").val(customer.pan_number ||
                        ""); // Use empty string if pan_number is null
                        $("#gst_number").val(customer.gst_number || ""); // Use empty string if gst
                        $("#country").val(customer.details.country ||
                        ""); // Use empty string if country is null
                        $("#city").val(customer.details.city || ""); // Use empty string if city is null
                        $("#state_code").val(customer.state_code ||
                        ""); // Use empty string if state_code is null
                        $("#address").val(customer.details.address ||
                        ""); // Use empty string if address is null

                        // Show profile image if exists
                        if (customer.profile_image) {
                            $("#avatar-preview").attr("src", `/storage/${customer.profile_image}`);
                            $("#avatar-preview-container").show(); // Make sure preview is visible
                        }
                    } else {
                        Swal.fire("Error!", "Customer not found!", "error");
                        window.location.href = "{{ route('customer.list') }}";
                    }
                },
                error: function() {
                    Swal.fire("Error!", "Customer not found!", "error");
                    window.location.href = "{{ route('customer.list') }}";
                },
            });

            // Handle File Input Change (Live Preview)
            // $("#avatar-input").change(function(event) {
            //     let reader = new FileReader();
            //     reader.onload = function(e) {
            //         $("#avatar-preview").attr("src", e.target.result);
            //         $("#avatar-preview-container").show();
            //     };
            //     reader.readAsDataURL(event.target.files[0]);
            // });
            $("#avatar-input").change(function(event) {

                let file = event.target.files[0];

                if (!file) return;

                const allowedTypes = [
                    "image/jpeg",
                    "image/png",
                    "image/jpg",
                    "image/webp",
                    "image/gif"
                ];

                if (!allowedTypes.includes(file.type)) {

                    $(".error-avatar").html("Only image files are allowed.");

                    $(this).val("");
                    $("#avatar-preview-container").hide();
                    return;
                }

                $(".error-avatar").html("");

                let reader = new FileReader();
                reader.onload = function(e) {
                    $("#avatar-preview").attr("src", e.target.result);
                    $("#avatar-preview-container").show();
                };

                reader.readAsDataURL(file);
            });

            $('#state_code').on('input', function() {

                if (this.value.includes(' - ')) return;

                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);

            });

            $("#customer_name").on("input", function() {
                let value = $(this).val();

                if (value.length > 80) {
                    $(this).val(value.substring(0, 80));
                }
            });

            $("#phone").on("input", function() {
                let value = $(this).val();

                // remove non-numbers
                value = value.replace(/\D/g, '');

                // limit 10 digits
                if (value.length > 10) {
                    value = value.substring(0, 10);
                }

                $(this).val(value);
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
                formData.append("customer_name", $("#customer_name").val());
                formData.append("email", $("#email").val());
                formData.append("phone", $("#phone").val());
                formData.append("pan_number", $("#pan_number").val());
                formData.append("gst_number", $("#gst_number").val());
                formData.append("country", $("#country").val());
                formData.append("city", $("#city").val());
                formData.append("state_code", $("#state_code").val());
                formData.append("address", $("#address").val());

                let avatar = $("#avatar-input")[0].files[0];
                // console.log(avatar);
                if (avatar) {
                    formData.append("avatar", avatar);
                }

                $.ajax({
                    url: `/api/updateCustomer/${customerId}`,
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
                                text: "Customer update successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/customer";
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
        });
    </script>
@endpush
