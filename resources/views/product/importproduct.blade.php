@extends('layout.app')

@section('title', 'Import Product')

@section('content')
<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Import Products</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- <div class="requiredfield">
                <h4>Field must be in CSV format</h4>
            </div> -->
            <div class="page-header">
                <div class="requiredfield">
                    <h4>Upload CSV File</h4>
                    <!-- <h6>Full details of a product</h6> -->
                </div>
                <div class="page-btn">
                    <a href="{{ env('ImagePath').'admin/assets/csvfile/Productimportfile.csv' }}" class="btn btn-submit w-100" download>Download Sample File</a>
                </div>
            </div>

            <div class="row">
                <!-- Left Column: Upload & Download -->
                <div class="col-lg-6 col-sm-12">
                    <div class="row">

                        <div class="col-lg-12">
                            <div class="form-group">
                                <!-- <label> Upload CSV File</label> -->
                                <div class="image-upload">
                                    <input type="file" name="csv_file" id="csv_file">
                                    <div class="image-uploads">
                                        <img src="{{ env('ImagePath').'admin/assets/img/icons/upload.svg'}}" alt="upload icon">
                                        <h4 class="upload-message">Drag and drop a file to upload</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Submit & Cancel -->
                        <div class="col-lg-12 text-end">
                            <div class="form-group mb-3">
                                <a href="javascript:void(0);" class="btn btn-submit submit me-2">Submit</a>
                                <a href="{{ route('product.list') }}" class="btn btn-cancel">Cancel</a>
                            </div>
                        </div>
                        <!-- <div class="col-lg-12   col-sm-6 mb-3">
                            <div class="form-group">
                                <a href="{{ url('admin/assets/csvfile/Productimportfile.csv') }}" class="btn btn-submit w-100" download>Download Sample File</a>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- Right Column: Product Field Details -->
                <div class="col-lg-6 col-sm-12">
                    <div class="row">
                        <!-- Required Fields -->
                        <div class="col-lg-12">
                            <div class="productdetails productdetailnew mb-3">
                                <ul class="product-bar">
                                    <li>
                                        <h4>Product Name</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                    <li>
                                        <h4>Category</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                    <li>
                                        <h4>SKU Code</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                    <li>
                                        <h4>Product Price</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                    <li>
                                        <h4>Product Quantity</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                     <li>
                                        <h4>Unit</h4>
                                        <h6 class="manitorygreen">This Field is required</h6>
                                    </li>
                                    <li>
                                        <h4>Product Status</h4>
                                        <h6 class="manitorygreen">This Field is required<br>active or inactive</h6>
                                    </li>
                                    <li>
                                        <h4>Product Availability</h4>
                                        <h6 class="manitorygreen">This Field is required<br>in_stock or out_stock</h6>
                                    </li>
                                    <li>
                                        <h4>Description</h4>
                                        <h6 class="manitoryblue">Field optional</h6>
                                    </li>
                                    <li>
                                        <h4>Brand</h4>
                                        <h6 class="manitoryblue">Field optional</h6>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $(document).on('click', '.submit', function(e) {
            e.preventDefault();

            var authToken = localStorage.getItem("authToken");
            var csv_file = $('#csv_file')[0].files[0];

            if (!csv_file) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select a CSV file.',
                    confirmButtonColor: '#ff9f43',
                    confirmButtonText: 'OK'
                });
                return;
            }

            var formData = new FormData();
            formData.append("csv_file", csv_file);

            $.ajax({
                url: "/api/importProducts",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    "Authorization": "Bearer " + authToken,
                },
                success: function(response) {
                    if (response.status) {
                        let messageParts = [];

                        // Add inserted message if any
                        if (response.message.includes("imported successfully")) {
                            messageParts.push(`<strong>${response.message}</strong>`);
                        }

                        // Add updated SKUs if any
                        if (response.updated_skus && response.updated_skus.length > 0) {
                            messageParts.push(`Existing products updated with additional quantity:<br><strong>${response.updated_skus.join(', ')}</strong>`);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Import Summary',
                            html: messageParts.join('<br><br>'),
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = "{{ route('product.list') }}";
                        });

                    } else {
                        // ❌ Show error message for invalid SKUs
                        let message = response.message;

                        if (response.invalid_skus && response.invalid_skus.length > 0) {
                            message += `<br><strong>Invalid Format SKUs:</strong> ${response.invalid_skus.join(', ')}<br>
            <strong>SKU must be numeric only.</strong>`;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            html: message,
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorList = "";

                        $.each(errors, function(key, value) {
                            errorList += value[0] + "\n";
                        });

                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: errorList,
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An unexpected error occurred.',
                            confirmButtonColor: '#ff9f43',
                            confirmButtonText: 'OK'
                        });
                    }
                }
            });
        });
    });
</script>
<script>
    document.getElementById('csv_file').addEventListener('change', function() {
        const fileName = this.files[0]?.name;
        const messageDiv = document.querySelector('.upload-message');
        if (fileName) {
            messageDiv.textContent = fileName;
        } else {
            messageDiv.textContent = 'Drag and drop a file to upload';
        }
    });
</script>

@endpush