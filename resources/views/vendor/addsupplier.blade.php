@extends('layout.app')

@section('title', 'Add Vendor')

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
    </style>
    <div class="content">
        {{-- <div class="page-header">
        <div class="page-title">
            <h4>Add Vendor</h4>
        </div>
    </div> --}}
        <div class="page-header ">
            <div class="page-title">
                <h4>Add Vendor</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('vendor.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                    Back</a></br>
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="customerForm">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" maxlength="80" id="supplier_name" class="form-control">
                                <span class="text-danger error-name"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Email </label>
                                <input type="email" name="email" id="email" class="form-control">
                                <span class="text-danger error-email"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Phone <span class="text-danger">*</span></label>
                                <input type="text" name="phone" c id="phone" class="form-control">
                                <span class="text-danger error-phone"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" id="country" class="form-control">
                                <span class="text-danger error-country"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" id="city" class="form-control">
                                <span class="text-danger error-city"></span>
                            </div>
                        </div>

                        <!-- State Code -->
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>State Code</label>
                                <input type="text" name="state_code" id="state_code" class="form-control">
                                <div class="text-danger error-state_code"></div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>PAN Number</label>
                                <input type="text" name="pan_number" maxlength="10" id="pan_number" class="form-control">
                                <span class="text-danger error-pan_number"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label>GST Number</label>
                                <div class="gst-input-wrap">
                                    <input type="text" name="gst_number" maxlength="15" id="gst_number"
                                        class="form-control">
                                    <span id="gst-loader" class="gst-loader">
                                        <i class="fas fa-spinner fa-spin"></i>
                                    </span>
                                </div>
                                <span class="text-danger error-gst_number"></span>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" id="address" class="form-control"></textarea>
                                <span class="text-danger error-address"></span>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Photo</label>
                                <div class="image-upload">
                                    <input type="file" name="avatar" id="avatar-input" class="form-control"
                                        accept="image/*">
                                    <div class="image-uploads">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                            alt="Upload Icon">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                <!-- Profile Image Preview -->
                                <div id="avatar-preview-container" style="display: none; margin-top: 10px;">
                                    <img id="avatar-preview" src="" alt="Profile Image"
                                        style="max-width: 150px; border-radius: 8px;">
                                </div>
                                <span class="text-danger error-avatar"></span>
                            </div>
                        </div>

                        <div class="col-lg-12">
                            <button type="submit" id="submitButton" class="btn btn-submit me-2">Submit</button>
                            <a href="{{ route('vendor.list') }}" class="btn btn-cancel">Cancel</a>
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
            var authToken = localStorage.getItem("authToken");
            let $gstLoader = $('#gst-loader');

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

                const stateMap = {
                    "Jammu and Kashmir": "01",
                    "Himachal Pradesh": "02",
                    "Punjab": "03",
                    "Chandigarh": "04",
                    "Uttarakhand": "05",
                    "Haryana": "06",
                    "Delhi": "07",
                    "Rajasthan": "08",
                    "Uttar Pradesh": "09",
                    "Bihar": "10",
                    "Sikkim": "11",
                    "Arunachal Pradesh": "12",
                    "Nagaland": "13",
                    "Manipur": "14",
                    "Mizoram": "15",
                    "Tripura": "16",
                    "Meghalaya": "17",
                    "Assam": "18",
                    "West Bengal": "19",
                    "Jharkhand": "20",
                    "Odisha": "21",
                    "Chhattisgarh": "22",
                    "Madhya Pradesh": "23",
                    "Gujarat": "24",
                    "Maharashtra": "27",
                    "Karnataka": "29",
                    "Tamil Nadu": "33",
                    "Telangana": "36"
                };
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

                        let stateName = res.state || '';
                        let stateCode = stateMap[stateName] || '';

                        if (stateCode) {
                            $('#state_code').val(stateCode + ' - ' + stateName);
                        } else {
                            $('#state_code').val(stateName);
                        }
                        // Populate fields based on API response
                        $('#supplier_name').val(res.legal_name || '');
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

            $('#state_code').on('input', function() {

                if (this.value.includes(' - ')) return;

                this.value = this.value.replace(/[^0-9]/g, '').substring(0, 3);

            });
            // $('#state_code').on('input', function() {
            //     this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
            // });

            $("#phone").on("input", function() {

                let value = $(this).val().replace(/\D/g, '');

                if (value.length > 10) {
                    value = value.substring(0, 10);
                }

                $(this).val(value);

            });

            $("#supplier_name").on("input", function() {

                let value = $(this).val();

                if (value.length > 80) {
                    $(this).val(value.substring(0, 80));
                }

            });

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

                    $(".error-avatar").html("Only image files are allowed (JPG, PNG, WEBP, GIF)");

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

            // Handle form submission
            $("#customerForm").submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                // Target the submit button
                const $btn = $("#submitButton"); // Make sure your submit button has id="submitButton"
                const originalText = $btn.html();


                // Show loader and disable button
                $btn.html(
                        '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                    )
                    .prop("disabled", true);

                let formData = new FormData(this);
                $(".text-danger").html(""); // Clear previous error messages
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");

                formData.append("selectedSubAdminId", selectedSubAdminId);

                $.ajax({
                    url: "/api/createSupplier", // Ensure API route is correct
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
                                text: "Vendor added successfully!",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43",
                            }).then(() => {
                                window.location.href = "/vendors";
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
                            Swal.fire("Error!", "Something went wrong. Please try again.",
                                "error");
                        }
                    },
                });
            });

            // Remove error message when user starts typing
            $("input, select").on("input", function() {
                let fieldName = $(this).attr("name");
                $(".error-" + fieldName).html("");
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
        });
    </script>
@endpush
