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
                                            <img src="{{ env('ImagePath') . 'admin/assets/img/icons/edit-set.svg' }}" alt="img">
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
                                <input type="text" name="email" placeholder="william@example.com" value="{{ $user->email }}">
                                <div class="invalid-feedback d-block field-error" data-field="email"></div>
                            </div>
                        </div>
                        <div class="col-lg-6 col-sm-12">
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" placeholder="+1452 876 5432" value="{{ $user->phone }}">
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
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ============================================================
         STAFF PRODUCTION MODAL
         - Regular/Product/Other staff  → today's productions
         - Raw Material staff           → tomorrow's productions
    ============================================================ --}}
    @if(auth()->user() && auth()->user()->role === 'staff')
    <div class="modal fade" id="staffProductionModal" tabindex="-1" aria-labelledby="staffProductionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background:#1b2850; color:#fff;">
                    <h5 class="modal-title" id="staffProductionModalLabel">
                        <i class="fas fa-industry me-2"></i>
                        <span id="productionModalTitle">Today's Productions</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="productionModalBody">
                    <div class="text-center py-4">
                        <span class="spinner-border text-primary" role="status"></span>
                        <p class="mt-2 text-muted">Loading productions...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif

@endsection

@push('js')
    <script>
        $(document).ready(function () {
            var authToken = localStorage.getItem("authToken");
            const $profileSubmitBtn = $("#updateProfileForm button[type='submit']");
            const profileSubmitBtnDefaultHtml = $profileSubmitBtn.html();

            function toggleProfileSubmitLoading(isLoading) {
                if (isLoading) {
                    $profileSubmitBtn
                        .prop("disabled", true)
                        .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...');
                } else {
                    $profileSubmitBtn
                        .prop("disabled", false)
                        .html(profileSubmitBtnDefaultHtml);
                }
            }

            $("#password").val("");

            $("#imgInp").on("change", function () {
                var file = this.files && this.files[0] ? this.files[0] : null;
                var $fieldError = $('.field-error[data-field="profile_image"]');
                if (!file) { $fieldError.text(""); return; }
                if (!file.type || file.type.indexOf("image/") !== 0) {
                    this.value = "";
                    $fieldError.text("Please select a valid image file.");
                    Swal.fire({ icon: "error", title: "Invalid file", text: "Only image files are allowed." });
                    return;
                }
                $fieldError.text("");
            });

            $("#updateProfileForm").submit(function (e) {
                e.preventDefault();
                if ($profileSubmitBtn.prop("disabled")) return;

                var formData = new FormData(this);
                var $form = $(this);
                var $validationErrors = $("#validationErrors");
                var $validationErrorList = $("#validationErrorList");

                $validationErrors && $validationErrors.addClass("d-none");
                $validationErrorList && $validationErrorList.html("");
                $form.find(".field-error").text("");
                $form.find(".is-invalid").removeClass("is-invalid");

                $.ajax({
                    url: "/api/updateProfile",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function () { toggleProfileSubmitLoading(true); },
                    headers: { "Authorization": "Bearer " + authToken },
                    success: function (response) {
                        if (response.status) {
                            Swal.fire({
                                icon: "success",
                                title: "Profile Updated!",
                                text: response.message,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => { window.location.reload(); });
                        } else {
                            Swal.fire({ icon: "error", title: "Update Failed", text: "Failed to update profile." });
                        }
                    },
                    error: function (xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function (field) {
                                var msgs = errors[field];
                                var inputField = $form.find('[name="' + field + '"]');
                                var fieldError = $form.find('.field-error[data-field="' + field + '"]');
                                if (inputField.length) inputField.addClass("is-invalid");
                                if (fieldError.length) fieldError.text(msgs.join(" "));
                            });
                            return;
                        }
                        Swal.fire({ icon: "error", title: "Error", text: "Error updating profile!" });
                    },
                    complete: function () { toggleProfileSubmitLoading(false); }
                });
            });

            // ── Staff Production Modal ──────────────────────────────────────
            @if(auth()->user() && auth()->user()->role === 'staff')
            // Sync fresh server-side token into localStorage
            @if($apiToken)
            localStorage.setItem("authToken", "{{ $apiToken }}");
            @endif
            loadStaffProductionModal();

            function loadStaffProductionModal() {
                // Use server-injected token (fresh) or fallback to localStorage
                var token = "{{ $apiToken ?? '' }}" || localStorage.getItem("authToken");
                if (!token) {
                    console.warn("No API token available for staff production modal");
                    return;
                }
                $.ajax({
                    url: "/api/staff/today-productions",
                    type: "GET",
                    headers: { "Authorization": "Bearer " + token },
                    success: function (res) {
                        if (!res.status || !res.productions || res.productions.length === 0) {
                            return; // nothing scheduled — don't show modal
                        }

                        var isTomorrow  = res.is_tomorrow;
                        var dateLabel   = res.date;
                        var productions = res.productions;
                        var staffType   = res.staff_type; // 'raw_material', 'product', 'other', or null

                        // Update title
                        var titleHtml = isTomorrow
                            ? '<i class="fas fa-industry me-2"></i>Tomorrow\'s Productions <small class="ms-2 fw-normal" style="font-size:13px;">(' + dateLabel + ')</small>'
                            : '<i class="fas fa-industry me-2"></i>Today\'s Productions <small class="ms-2 fw-normal" style="font-size:13px;">(' + dateLabel + ')</small>';
                        $("#productionModalTitle").html(titleHtml);

                        // Build body
                        var html = isTomorrow
                            ? '<div class="alert alert-info mb-3"><i class="fas fa-info-circle me-1"></i> These are <strong>tomorrow\'s scheduled productions</strong>. Please prepare the raw materials listed below.</div>'
                            : '<div class="alert alert-warning mb-3"><i class="fas fa-exclamation-circle me-1"></i> You have <strong>' + productions.length + ' production(s)</strong> scheduled for today.</div>';

                        // Only raw_material staff see raw materials
                        var showRawMaterials = (staffType === 'raw_material');

                        productions.forEach(function (prod) {
                            var statusBadge = getStatusBadge(prod.status);
                            html += '<div class="card mb-3 border">';
                            html += '  <div class="card-header d-flex justify-content-between align-items-center" style="background:#f8f9fa;">';
                            html += '    <div>';
                            html += '      <strong class="me-2">#' + (prod.production_no || prod.id) + '</strong>';
                            html += '      <span class="fw-semibold">' + prod.product_name + '</span>';
                            html += '      <span class="text-muted ms-1" style="font-size:12px;">' + prod.product_unit + '</span>';
                            html += '    </div>';
                            html += '    <div class="d-flex align-items-center gap-2">';
                            html += '      ' + statusBadge;
                            html += '      <a href="/inventory/productions/' + prod.id + '" class="btn btn-sm btn-primary" target="_blank">';
                            html += '        <i class="fas fa-eye me-1"></i>View';
                            html += '      </a>';
                            html += '    </div>';
                            html += '  </div>';
                            html += '  <div class="card-body">';

                            // Production details summary
                            html += '<div class="row mb-2">';
                            html += '  <div class="col-md-3"><small class="text-muted">Production Qty:</small><br><strong>' + prod.production_qty + ' ' + prod.product_unit + '</strong></div>';
                            if (prod.output_qty) {
                                html += '  <div class="col-md-3"><small class="text-muted">Output Qty:</small><br><strong>' + prod.output_qty + ' ' + prod.product_unit + '</strong></div>';
                            }
                            if (prod.total_cost) {
                                html += '  <div class="col-md-3"><small class="text-muted">Total Cost:</small><br><strong>₹' + parseFloat(prod.total_cost).toFixed(2) + '</strong></div>';
                            }
                            html += '  <div class="col-md-3"><small class="text-muted">Date:</small><br><strong>' + prod.production_date + '</strong></div>';
                            html += '</div>';

                            if (prod.notes) {
                                html += '<p class="text-muted mb-2" style="font-size:13px;"><i class="fas fa-sticky-note me-1"></i>' + prod.notes + '</p>';
                            }

                            // Raw material staff → show raw materials table
                            if (showRawMaterials) {
                                if (prod.raw_materials && prod.raw_materials.length > 0) {
                                    html += '<h6 class="mb-2 mt-3" style="font-size:13px;font-weight:600;"><i class="fas fa-boxes me-1 text-warning"></i>Raw Materials</h6>';
                                    html += '<div class="table-responsive"><table class="table table-sm table-bordered mb-0">';
                                    html += '<thead class="table-light"><tr><th>#</th><th>Material</th><th>Required Qty</th></tr></thead><tbody>';
                                    prod.raw_materials.forEach(function (rm, i) {
                                        html += '<tr><td>' + (i + 1) + '</td><td>' + rm.name + '</td><td>' + rm.required_qty + ' ' + rm.unit + '</td></tr>';
                                    });
                                    html += '</tbody></table></div>';
                                } else {
                                    html += '<p class="text-muted mb-0 mt-2" style="font-size:13px;">No raw materials linked.</p>';
                                }
                            }
                            // Product / other staff → show only production info (no raw materials)

                            html += '  </div></div>';
                        });

                        $("#productionModalBody").html(html);

                        var modal = new bootstrap.Modal(document.getElementById('staffProductionModal'), {
                            backdrop: 'static',
                            keyboard: false
                        });
                        modal.show();
                    },
                    error: function (xhr, status, error) {
                        console.error("Staff production modal error:", {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            response: xhr.responseJSON || xhr.responseText,
                            error: error
                        });
                        // silently fail — don't block the user
                    }
                });
            }

            function getStatusBadge(status) {
                var map = {
                    'draft':         '<span class="badge bg-secondary">Draft</span>',
                    'in_production': '<span class="badge bg-warning text-dark">In Production</span>',
                    'completed':     '<span class="badge bg-success">Completed</span>',
                };
                return map[status] || '<span class="badge bg-light text-dark">' + status + '</span>';
            }
            @endif
            // ── End Staff Production Modal ──────────────────────────────────
        });
    </script>
@endpush
