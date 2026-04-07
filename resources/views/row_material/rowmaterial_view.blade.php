@extends('layout.app')

@section('title', 'Row Material View')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<style>
    .product-gallery {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        overflow: hidden;
    }

    .product-main-img {
        height: 350px;
        object-fit: contain;
        width: 100%;
    }

    .product-thumb-img {
        height: 60px;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .product-thumb-img:hover {
        border-color: #0d6efd;
    }

    @media (max-width: 767.98px) {
        .col-md-7 {
            margin-left: 10px;
        }

        .col-md-5 {
            margin-left: 8px;
        }

        .bg-white.p-3.rounded.border {
            margin-bottom: 12px;
        }
    }

    /* Barcode container wrapper for layout */
    .product-barcode .barcode-wrapper {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        text-align: center;
    }

    /* Make sure SVG or IMG inside barcode scales well */
    .product-barcode svg,
    .product-barcode img {
        width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }

    /* Optional: improve spacing for barcode text */
    .product-barcode .text-muted {
        margin-top: 0.5rem;
    }

    /* Optional: Responsive tweaks for smaller screens */
    @media (max-width: 576px) {
        .product-barcode {
            width: 100%;
            overflow-x: auto;
            /* Enables horizontal scroll if needed */
            padding: 0 10px;
            /* Adds some padding on small screens */
            text-align: center;
        }

        /* Barcode SVG or image scales responsively */
        .product-barcode svg,
        .product-barcode img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Barcode label (like PRD388788907KK) spacing */
        .product-barcode .text-muted {
            margin-top: 0.5rem;
            word-break: break-word;
            /* Wrap long barcode text */
            font-size: 14px;
        }

    }
</style>

<div class="content">
    <div class="page-header">
        <div class="page-title">
            <h4>Row Material Details</h4>
            <!-- <h6>Full details of a row material</h6> -->
        </div>
        <div class="page-btn d-flex gap-2">
            @if (app('hasPermission')(1, 'edit'))
              <a href="{{ route('row_material.edit', $view_id) }}" class="btn btn-added">
                <i class="bi bi-pencil-square me-1"></i> Edit
            </a>
            @endif
            @if (app('hasPermission')(1, 'view'))
            <a href="{{ route('row_material.list') }}" class="btn" style="background: #1b2850; color: #fff;">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
            @endif
        </div>
    </div>


    <div class="card shadow-sm border-0">
        <div class="card-body ">
            <div class="row align-items-start" id="product-details">

                <!-- Product Images -->



            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<script>
    $(document).ready(function() {
        var authToken = localStorage.getItem("authToken");

        let productId = "{{ $view_id }}";

        $.ajax({
            url: "/api/rowmaterial-view/" + productId,
            type: "GET",
            dataType: "json",
            headers: {
                "Authorization": "Bearer " + authToken,
            },
            success: function(data) {
                // Function to capitalize first letter of each word
                // console.log("API Response:", data); // Debugging log
                function capitalizeWords(str) {
                    if (!str || str.trim() === '') return 'N/A';
                    return str.replace(/\b\w/g, function(char) {
                        return char.toUpperCase();
                    });
                }
                // Extract row material object
                let rowMaterial = data.row_material || {};

                // Parse images from row material
                let images = JSON.parse(rowMaterial.images || "[]");

                if (images.length === 0) {
                    images = ["/admin/assets/img/product/noimage.png"];
                }

                let imageHTML = `
        <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
    `;

                images.forEach((img, i) => {
                    const imagePath = "{{ env('ImagePath') }}";

                    let imgSrc = img.startsWith('/admin/assets/') || img.startsWith(
                            'admin/assets') ?
                        `${imagePath}${img}` :
                        `${imagePath}/storage/${img}`;
                    imageHTML += `
            <div class="carousel-item ${i === 0 ? 'active' : ''}">
                <img src="${imgSrc}" class="d-block w-100 product-main-img rounded mb-3"
                    onerror="this.onerror=null;this.src='/admin/assets/img/product/noimage.png';"
                >
            </div>
        `;
                });

                imageHTML += `
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    `;

                // Get currency info from root data
                let currencySymbol = data.currencySymbol || '₹';
                let currencyPosition = data.currencyPosition || 'left';

                // Format GST details
                let gstDetailsHTML = 'N/A';
                if (rowMaterial.gst_option === 'with_gst' && rowMaterial.product_gst) {
                    try {
                        let gstData = JSON.parse(rowMaterial.product_gst);
                        gstDetailsHTML = gstData.map(gst => `${gst.tax_name} (${gst.tax_rate}%)`).join(', ');
                    } catch (e) {
                        console.error("Error parsing product_gst:", e);
                    }
                }

                // Format price dynamically
                let price = rowMaterial.price ?? 'N/A';
                let formattedPrice = 'N/A';

                if (price !== 'N/A') {
                    let priceVal = parseFloat(price).toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    formattedPrice = currencyPosition === 'left' ?
                        `${currencySymbol}${priceVal}` :
                        `${priceVal}${currencySymbol}`;
                }

                let html = `
            <div class="row align-items-start mt-3">
                <div class="col-md-5">
                    <div class="bg-white p-3 rounded border">
                        ${imageHTML}
                    </div>
                </div>
                <div class="col-md-6 mx-3">
                    <h2 class="fw-bold mb-4"><i class="bi bi-box-seam me-2"></i>${capitalizeWords(rowMaterial.row_materialname || rowMaterial.name)}</h2>
                <p class="text-muted mb-2"><i class="bi bi-tags me-1"></i>Category: <strong>${capitalizeWords(rowMaterial.category?.name)}</strong></p>
                <div class="d-flex align-items-center mb-3">
                    <span class="h4 text-success fw-semibold me-3">${formattedPrice}</span>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>${formatAvailability(rowMaterial.availablility ?? 'N/A')}</span>
                    <span class="badge bg-primary ms-2"><i class="bi bi-lightning-fill me-1"></i>${capitalizeWords(rowMaterial.status)}</span>

                </div>
                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-boxes me-2"></i><strong>Quantity:</strong> ${rowMaterial.quantity ?? 'N/A'} Units</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-upc-scan me-2"></i><strong>SKU:</strong> ${rowMaterial.SKU ?? 'N/A'}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-hash me-2"></i><strong>HSN Code:</strong> ${rowMaterial.hsn_code ?? 'N/A'}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-building me-2"></i><strong>Brand:</strong> ${capitalizeWords(rowMaterial.brand?.name)}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-percent me-2"></i><strong>GST Option:</strong> ${formatAvailability(rowMaterial.gst_option ?? 'N/A')}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-receipt me-2"></i><strong>Product GST:</strong> ${gstDetailsHTML}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-arrow-repeat me-2"></i><strong>Stock Status:</strong> ${formatAvailability(rowMaterial.availablility ?? 'N/A')}</p>
                        <hr class="my-1">
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><i class="bi bi-check-circle me-2"></i><strong>Status:</strong> ${capitalizeWords(rowMaterial.status ?? 'N/A')}</p>
                        <hr class="my-1">
                    </div>
                     <div class="col-6">
                        <p class="mb-1"><i class="bi bi-grid-fill me-2"></i><strong>Unit:</strong> ${capitalizeWords(rowMaterial.unit?.unit_name ?? 'N/A')}</p>
                        <hr class="my-1">
                    </div>
                </div>
                        <div class="mb-2 mt-3">
                        <div class="d-flex flex-column flex-sm-row align-items-start">
                            <strong class="me-2"><i class="bi bi-upc me-1 mt-1"></i>Barcode:</strong>
                            <div class="product-barcode ms-sm-3 mt-2 mt-sm-0">
    <div class="barcode-wrapper">
        ${data.barcode_html}
    </div>
    <div class="text-muted small text-center mt-2">${rowMaterial.barcode ?? 'N/A'}</div>
</div>

                        </div>
                    </div>

                    </div>
                </div>
            </div>
        `;

                $('#product-details').html(html);
            },

            error: function() {
                $('#product-details').html('<p class="text-danger">Product not found.</p>');
            }
        });
    });

    function formatAvailability(str) {
        if (!str) return "";
        return str
            .split('_') // Split by underscore
            .map(word => word.charAt(0).toUpperCase() + word.slice(1)) // Capitalize each word
            .join(' '); // Join with space
    }
</script>
@endpush
