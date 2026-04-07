@extends('layout.app')

@section('title', 'Row Material Add')

@section('content')

    <style>
        @media screen and (max-width: 768px) {
            .form-group {
                margin-bottom: 10px !important
            }
        }

        .image-upload .image-uploads h4 {
            font-size: 12px !important;
        }

        a.btn.back-button {
            background: #ff9f43;
            color: #fff;
        }

        .form-label-icon {
            display: flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .form-label-icon i {
            color: #ff9f43;
            font-size: 13px;
            width: 14px;
            text-align: center;
        }

        .form-label-icon .required {
            color: #dc3545;
            margin-left: 2px;
        }
    </style>
    <div class="content">
        <div class="page-header ">
            <div class="page-title">
                <h4>Add Row Material</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('row_material.list') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i>
                    Back</a></br>
                <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form id="productForm">
                    <div class="row">
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-box"></i> Row Material Name <span
                                        class="required">*</span></label>
                                <input type="text" name="name" id="name">
                                <span class="error_name text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-layer-group"></i> Category <span
                                        class="required">*</span></label>
                                <select class="select" name="category_id" id="category_id">
                                    <option value="">Choose Category</option>
                                </select>
                                <span class="error_category_id text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-tag"></i> Brand</label>
                                <select class="select" name="brand_id" id="brand_id">
                                    <option value="">Choose Brand</option>

                                </select>
                                <span class="error_brand_id text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-barcode"></i> SKU <span
                                        class="required">*</span></label>
                                <input type="number" class="form-control" name="SKU" id="SKU">
                                <span class="error_SKU text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-hashtag"></i> HSN Code</label>
                                <input type="text" class="form-control" name="hsn_code" id="hsn_code">
                                <span class="error_hsn_code text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-percent"></i> GST Option</label>
                                <select class="select" name="gst_option" id="gst_option">
                                    <option value="">Choose GST Option</option>
                                    <option value="without_gst">Without GST</option>
                                    <option value="with_gst">With GST</option>
                                </select>
                                <span class="error_gst_option text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6" id="gst_dropdown_container" style="display: none;">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-receipt"></i> Product GST</label>
                                <select class="select" name="product_gst[]" id="product_gst" multiple>
                                    <option value="">Choose GST Rate</option>
                                </select>
                                <span class="error_product_gst text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-cubes"></i> Quantity <span
                                        class="required">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control" min="0"
                                    step="1">
                                <span class="error_quantity text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-ruler-combined"></i> Unit <span
                                        class="required">*</span></label>
                                <select class="select" name="unit_id" id="unit_id">
                                    <option value="">Choose Unit</option>
                                </select>
                                <span class="error_unit_id text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-indian-rupee-sign"></i> Price <span
                                        class="required">*</span></label>
                                <input type="number" name="price" id="price" class="form-control" min="0"
                                    step="0.01">
                                <span class="error_price text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-toggle-on"></i> Status</label>
                                <select class="select" name="status" id="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">InActive</option>
                                </select>
                                <span class="error_status text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-warehouse"></i> Stock</label>
                                <select class="select" name="availablility" id="availablility" disabled>
                                    <option value="in_stock">In Stock</option>
                                    <option value="out_stock">Out Of Stock</option>
                                </select>
                                <span class="error_availablility text-danger"></span>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const qtyInput = document.getElementById('quantity');

                                if (qtyInput) {
                                    qtyInput.addEventListener('input', function() {
                                        const qty = parseInt(this.value);

                                        // Update using jQuery + Select2
                                        if (!isNaN(qty) && qty === 0) {
                                            $('#availablility').val('out_stock').trigger('change');
                                        } else {
                                            $('#availablility').val('in_stock').trigger('change');
                                        }

                                        // console.log("Quantity changed to", qty);
                                    });
                                }
                            });
                        </script>


                        <div class="col-lg-3 col-sm-6 col-6">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-qrcode"></i> Barcode</label>
                                <input type="text" name="barcode" id="barcode" class="form-control">
                                <span class="error_barcode text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-6 col-12">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-align-left"></i> Description</label>
                                <textarea class="form-control" name="description" id="description" rows="1"></textarea>
                                <span class="error_description text-danger"></span>
                            </div>
                        </div>

                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label class="form-label-icon"><i class="fa-solid fa-image"></i> Row Material
                                    Image</label>
                                <div class="image-upload">
                                    <input type="file" name="images[]" id="images" accept="image/*" multiple>
                                    <div class="image-uploads">
                                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/upload.svg' }}"
                                            alt="img">
                                        <h4>Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                                <div class="image-preview" style="margin-top: 10px;"></div>
                                <span class="error_images text-danger"></span>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <a href="javascript:void(0);" class="btn btn-submit me-2 submit">Submit</a>
                            <a href="{{ route('product.list') }}" class="btn btn-cancel">Cancel</a></br>
                            <span class="success_submit text-danger"></span>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
    <!-- Custom Modal -->
    <div class="modal fade" id="customModal" tabindex="-1" aria-labelledby="customModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customModalLabel">Add New</h5>
                    <button type="button" class="btn-close bg-white text-black" data-bs-dismiss="modal"
                        aria-label="Close" onclick="modalOpen=false;">x</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="custom_name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="custom_name" placeholder="Enter name">
                        <span class="error_custom_name text-danger"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="modalOpen=false;">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomBtn">Save</button>
                </div>
                <span class="text-danger error_model"></span>
            </div>
        </div>
    </div>

@endsection
@push('js')
    <script>
        document.getElementById("images").addEventListener("change", function(event) {
            var files = Array.from(event.target.files);
            var previewDiv = document.querySelector(".image-preview");
            var errorSpan = document.querySelector(".error_images");
            var validFiles = files.filter(file => file.type.startsWith("image/"));

            previewDiv.innerHTML = "";
            errorSpan.textContent = "";

            if (validFiles.length !== files.length) {
                errorSpan.textContent = "Only image files are allowed.";
                event.target.value = "";
                return;
            }

            validFiles.forEach((file) => {
                var reader = new FileReader();

                reader.onload = function(e) {
                    var imageContainer = document.createElement("div");
                    imageContainer.style.position = "relative";
                    imageContainer.style.display = "inline-block";

                    var img = document.createElement("img");
                    img.src = e.target.result;
                    img.alt = "Preview";
                    img.style.maxWidth = "100px";
                    img.style.maxHeight = "100px";
                    img.style.borderRadius = "5px";
                    img.style.boxShadow = "2px 2px 10px rgba(0,0,0,0.1)";
                    img.style.marginRight = "5px";

                    var removeBtn = document.createElement("button");
                    removeBtn.innerHTML = "&times;";
                    removeBtn.style.position = "absolute";
                    removeBtn.style.top = "0";
                    removeBtn.style.right = "0";
                    removeBtn.style.background = "red";
                    removeBtn.style.color = "white";
                    removeBtn.style.border = "none";
                    removeBtn.style.borderRadius = "50%";
                    removeBtn.style.width = "20px";
                    removeBtn.style.height = "20px";
                    removeBtn.style.cursor = "pointer";
                    removeBtn.style.display = "flex";
                    removeBtn.style.alignItems = "center";
                    removeBtn.style.justifyContent = "center";
                    removeBtn.style.fontSize = "14px";

                    removeBtn.addEventListener("click", function() {
                        imageContainer.remove();
                    });

                    imageContainer.appendChild(img);
                    imageContainer.appendChild(removeBtn);
                    previewDiv.appendChild(imageContainer);
                };

                reader.readAsDataURL(file);
            });
        });
        $(document).on("input", "#price", function() {
            let value = parseFloat($(this).val());
            let errorSpan = $(".error_price");

            if (value < 0) {
                $(this).val(""); // clear invalid value
                errorSpan.text("Price cannot be negative.");
            } else {
                errorSpan.text(""); // clear error if valid
            }
        });
        $(document).ready(function() {
            $(document).on('click', '.submit', function(e) {
                e.preventDefault();
                const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
                // console.log(selectedSubAdminId);
                var $btn = $(this); // cache the button
                var originalText = $btn.html();
                // Show loading text and disable the button
                $btn.html(
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...'
                ).prop('disabled', true);

                var authToken = localStorage.getItem("authToken");
                let formData = new FormData($('#productForm')[0]);

                // Remove product_gst if "without_gst" is selected
                const gstOption = $('#gst_option').val();
                if (gstOption !== 'with_gst') {
                    formData.delete('product_gst[]');
                }

                if (selectedSubAdminId) {
                    formData.append("sub_admin_id", selectedSubAdminId);
                }
                // Clear error texts
                $('.error_name, .error_category_id, .error_brand_id, .error_SKU, .error_hsn_code, .error_gst_option, .error_product_gst, .error_quantity, .error_unit_id, .error_price, .error_status, .error_barcode, .error_description, .error_availablility, .error_images')
                    .text('');

                $.ajax({
                    url: "/api/create-row-material",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        // Re-enable and reset the button
                        $btn.html(originalText).prop('disabled', false);

                        if (response.status) {
                            Swal.fire({
                                title: "Success",
                                text: "Row material added successfully",
                                icon: "success",
                                confirmButtonText: "OK",
                                confirmButtonColor: "#ff9f43"
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href =
                                        "{{ route('row_material.list') }}";
                                }
                            });
                        }
                    },
                    error: function(xhr) {
                        // Re-enable and reset the button
                        $btn.html(originalText).prop('disabled', false);

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $('.error_name, .error_category_id, .error_brand_id, .error_SKU, .error_hsn_code, .error_gst_option, .error_product_gst, .error_quantity, .error_unit_id, .error_price, .error_status, .error_barcode, .error_description, .error_availablility, .error_images')
                                .text('');

                            $.each(errors, function(key, value) {
                                let errorKey = key.split('.')[0];
                                let errorMsg = value.join(' ');
                                $('.error_' + errorKey).text(errorMsg);
                            });
                        }
                    }
                });
            });
        });

        $(document).ready(function() {
            const sub_branch_id = localStorage.getItem('selectedSubAdminId');
            var authToken = localStorage.getItem("authToken");

            // 🔹 Fetch Units
            function get_units(selectedId = null) {
                let url = sub_branch_id ?
                    `/api/get-units?sub_branch_id=${sub_branch_id}` :
                    `/api/get-units`;
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#unit_id");
                            select.empty().append('<option value="">Choose Unit</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.unit_name}</option>`);
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        } else {
                            console.warn("No Units found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Units:", xhr.responseText);
                    }
                });
            }

            // 🔹 Fetch Brands
            function get_brand(selectedId = null) {
                let url = sub_branch_id ?
                    `/api/get-brand?sub_branch_id=${sub_branch_id}` :
                    `/api/get-brand`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#brand_id");
                            select.empty().append('<option value="">Choose Brand</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        } else {
                            console.warn("No Brand found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Brand:", xhr.responseText);
                    }
                });
            }

            // 🔹 Fetch Categories
            function get_category(selectedId = null) {
                let url = sub_branch_id ?
                    `/api/get-category?sub_branch_id=${sub_branch_id}` :
                    `/api/get-category`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#category_id");
                            select.empty().append('<option value="">Choose Category</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        } else {
                            console.warn("No Category found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Category:", xhr.responseText);
                    }
                });
            }

            // 🔹 Select2 Init
            $('#brand_id').select2({
                placeholder: "Select or Add Brand",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#category_id').select2({
                placeholder: "Select or Add Category",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#product_type_id').select2({
                placeholder: "Select or Add Product Type",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#unit_id').select2({
                placeholder: "Select or Add Unit",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            // 🔹 Modal Logic
            let modalOpen = false;
            let currentCustomValue = null;
            let currentType = null;

            // Brand event
            $('#brand_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "brand";
                    $('#customModalLabel').text("Add New Brand");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            // Category event
            $('#category_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "category";
                    $('#customModalLabel').text("Add New Category");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            // Product Type event
            $('#product_type_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "product_type";
                    $('#customModalLabel').text("Add New Product Type");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            // Unit event
            $('#unit_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "unit";
                    $('#customModalLabel').text("Add New Unit");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });
            // 🔹 Save custom item from modal
            $('#saveCustomBtn').on('click', function() {
                const name = $('#custom_name').val().trim();
                let sub_admin_id = sub_branch_id;

                $(".error_model").text(""); // reset error

                if (name === "") {
                    $(".error_model").text("Please enter a name");
                    return;
                }

                let url = "";
                let targetDropdown = "";
                let postData = {
                    sub_admin_id: sub_admin_id,
                    name: name

                };

                if (currentType === "brand") {
                    url = "api/addBrand";
                    targetDropdown = "#brand_id";
                } else if (currentType === "category") {
                    url = "api/addcategory";
                    targetDropdown = "#category_id";
                } else if (currentType === "unit") {
                    url = "api/add-units";
                    targetDropdown = "#unit_id";
                    postData = {
                        unitname: name,
                        selectedSubAdminId: sub_branch_id
                    };
                    // postData.status = "Active";
                }

                //  else if (currentType === "product_type") {
                //     url = "api/product-type-add";
                //     targetDropdown = "#product_type_id";
                //     postData.status = "Active"; // ✅ Default Active
                // }

                // 🔹 Show loader on button
                let btn = $("#saveCustomBtn");
                btn.prop("disabled", true).html("Saving... <i class='fa fa-spinner fa-spin'></i>");

                $.ajax({
                    url: url,
                    type: "POST",
                    data: postData,
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        // // console.log(response);

                        let id = null;
                        let text = null;
                        let brandData = response.brand ?? response.data ?? null;
                        // ✅ Brand Response
                        // if (currentType === "brand" && response.brand) {
                        //     id = response.brand.id;
                        //     text = response.brand.name;
                        // }
                        if (currentType === "brand" && brandData) {
                            id = brandData.id;
                            text = brandData.name;
                        }

                        // ✅ Category Response
                        else if (currentType === "category" && response.category) {
                            id = response.category.id;
                            text = response.category.name;
                        }
                        // ✅ Product Type Response
                        else if (currentType === "product_type" && response.data) {
                            id = response.data.id;
                            text = response.data.name;
                        }
                        // ✅ Unit Response
                        else if (currentType === "unit") {
                            let unitData = response.data ?? response.unit ??
                                null; // handle both keys
                            if (unitData) {
                                id = unitData.id;
                                text = unitData.unit_name ?? unitData.name ?? null; // fallback
                            }
                        }

                        if (!id) {
                            $(".error_model").text("Invalid response format");
                            btn.prop("disabled", false).text("Save");
                            return;
                        }

                        // remove temp option
                        if (currentCustomValue && isNaN(parseInt(currentCustomValue))) {
                            $(targetDropdown).find(`option[value="${currentCustomValue}"]`)
                                .remove();
                        }

                        // add new option
                        if ($(targetDropdown).find(`option[value="${id}"]`).length === 0) {
                            $(targetDropdown).append(new Option(text, id, true, true));
                        }

                        // select new
                        $(targetDropdown).val(id).trigger('change');

                        // refresh dropdowns
                        if (currentType === "brand") {
                            get_brand(id);
                        } else if (currentType === "category") {
                            get_category(id);
                        } else if (currentType === "unit") {
                            get_units(id);
                        }
                        //  else if (currentType === "product_type") {
                        //     get_product_type(id);
                        // }

                        // ✅ Success loader reset + modal close
                        btn.prop("disabled", false).text("Save");
                        $('#customModal').modal('hide');
                        modalOpen = false;
                        currentCustomValue = null;
                        currentType = null;
                    },
                    // error: function(xhr) {
                    //     let msg = "Error while saving " + currentType;
                    //     if (xhr.responseJSON && xhr.responseJSON.message) {
                    //         msg = xhr.responseJSON.message;
                    //     }
                    //     $(".error_model").text(msg);

                    //     // reset loader
                    //     btn.prop("disabled", false).text("Save");
                    //     console.error(xhr.responseText);
                    // }
                    error: function(xhr) {
                        let msg = "Error while saving " + currentType;

                        // ✅ Handle Laravel validation errors
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors) {
                                // Take the first validation error from the object
                                let firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                msg = xhr.responseJSON.errors[firstKey][0];
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                        }

                        $(".error_model").text(msg);

                        // reset loader
                        btn.prop("disabled", false).text("Save");
                        console.error(xhr.responseText);
                    }

                });
            });

            $('#unit_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "unit";
                    $('#customModalLabel').text("Add New Unit");
                    $('#custom_name').val(name);
                    $(".error_model").text('');
                    $('#customModal').modal('show');
                }
            });

            // Reset flag when modal closes
            $('#customModal').on('hidden.bs.modal', function() {
                modalOpen = false;
                currentCustomValue = null;
                currentType = null;
            });

            // ✅ Page load par call karo
            get_brand();
            get_category();
            get_units();
            // get_product_type();
        });
        $(document).ready(function() {
            const sub_branch_id = localStorage.getItem('selectedSubAdminId');
            var authToken = localStorage.getItem("authToken");

            function get_units(selectedId = null) {
                const sub_branch_id = localStorage.getItem('selectedSubAdminId');
                const authToken = localStorage.getItem("authToken");
                let url = sub_branch_id ?
                    `/api/get-units?sub_branch_id=${sub_branch_id}` :
                    `/api/get-units`;
                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#unit_id");
                            select.empty().append('<option value="">Choose Unit</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.unit_name}</option>`);
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }

                            // Initialize Select2
                            if (!select.hasClass('select2-hidden-accessible')) {
                                select.select2({
                                    placeholder: "Select or Add Unit",
                                    tags: true,
                                    width: '100%',
                                    allowClear: true,
                                });
                            }
                        } else {
                            console.warn("No Units found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Units:", xhr.responseText);
                    }
                });
            }
            // 🔹 Fetch Brands
            function get_brand(selectedId = null) {
                let url = sub_branch_id ?
                    `/api/get-brand?sub_branch_id=${sub_branch_id}` :
                    `/api/get-brand`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#brand_id");
                            select.empty().append('<option value="">Choose Brand</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        } else {
                            console.warn("No Brand found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Brand:", xhr.responseText);
                    }
                });
            }

            // 🔹 Fetch Categories
            function get_category(selectedId = null) {
                let url = sub_branch_id ?
                    `/api/get-category?sub_branch_id=${sub_branch_id}` :
                    `/api/get-category`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#category_id");
                            select.empty().append('<option value="">Choose Category</option>');

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.name}</option>`
                                );
                            });

                            if (selectedId) {
                                select.val(selectedId).trigger('change');
                            }
                        } else {
                            console.warn("No Category found.");
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching Category:", xhr.responseText);
                    }
                });
            }

            // 🔹 Select2 Init
            $('#brand_id').select2({
                placeholder: "Select or Add Brand",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#category_id').select2({
                placeholder: "Select or Add Category",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#product_type_id').select2({
                placeholder: "Select or Add Product Type",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            $('#unit_id').select2({
                placeholder: "Select or Add Unit",
                tags: true,
                width: '100%',
                allowClear: true,
            });

            // 🔹 Modal Logic
            let modalOpen = false;
            let currentCustomValue = null;
            let currentType = null;

            function openCustomModal(type, value = null, name = '') {
                modalOpen = true;
                currentType = type; // brand, category, product_type
                currentCustomValue = value; // temporary value if editing
                $('#customModalLabel').text("Add New " + capitalizeFirstLetter(type));
                $('#custom_name').val(name);
                $(".error_model").text('');
                $('#customModal').modal('show');
            }

            // Capitalize helper
            function capitalizeFirstLetter(str) {
                return str.charAt(0).toUpperCase() + str.slice(1);
            }

            // Trigger modal on select2 custom option
            $('#brand_id, #category_id, #product_type_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    let type = '';
                    if ($(this).attr('id') === 'brand_id') type = 'brand';
                    else if ($(this).attr('id') === 'category_id') type = 'category';
                    else if ($(this).attr('id') === 'product_type_id') type = 'product_type';

                    openCustomModal(type, value, name);
                }
            });
            // Brand event
            $('#brand_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "brand";
                    $('#customModalLabel').text("Add New Brand");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            // Category event
            $('#category_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "category";
                    $('#customModalLabel').text("Add New Category");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            // Product Type event
            $('#product_type_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "product_type";
                    $('#customModalLabel').text("Add New Product Type");
                    $('#custom_name').val(name);
                    $('#customModal').modal('show');
                }
            });

            $('#unit_id').on('select2:select', function(e) {
                if (modalOpen) return;

                const data = e.params.data;
                const value = data.id;
                const name = data.text;
                const isCustom = isNaN(parseInt(value));

                if (isCustom) {
                    modalOpen = true;
                    currentCustomValue = value;
                    currentType = "unit";
                    $('#customModalLabel').text("Add New Unit");
                    $('#custom_name').val(name);
                    $(".error_model").text('');
                    $('#customModal').modal('show');
                }
            });

            // 🔹 Save custom item from modal

            // Reset flag when modal closes
            $('#customModal').on('hidden.bs.modal', function() {
                modalOpen = false;
                currentCustomValue = null;
                currentType = null;
            });

            // 🔹 GST Option Change Handler
            $(document).on('change', '#gst_option', function() {
                const gstOption = $(this).val();
                const gstContainer = $('#gst_dropdown_container');

                if (gstOption === 'with_gst') {
                    gstContainer.show();
                    fetch_gst_rates();
                    $('.error_product_gst').text('');
                } else {
                    gstContainer.hide();
                    $('#product_gst').val(null).trigger('change');
                    $('.error_product_gst').text('');
                }
            });

            // 🔹 Fetch GST Rates
            function fetch_gst_rates() {
                const sub_branch_id = localStorage.getItem('selectedSubAdminId');
                let url = sub_branch_id ?
                    `/api/get-tax-rates?sub_branch_id=${sub_branch_id}` :
                    `/api/get-tax-rates`;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken,
                    },
                    success: function(response) {
                        if (response.status) {
                            let select = $("#product_gst");
                            select.empty();

                            $.each(response.data, function(key, item) {
                                select.append(
                                    `<option value="${item.id}">${item.tax_name} (${item.tax_rate}%)</option>`
                                );
                            });

                            if (select.hasClass('select2-hidden-accessible')) {
                                select.trigger('change');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error("Error fetching GST rates:", xhr.responseText);
                    }
                });
            }

            // ✅ Page load par call karo
            get_brand();
            get_category();
            get_units();
            // get_product_type();
        });
    </script>
@endpush
