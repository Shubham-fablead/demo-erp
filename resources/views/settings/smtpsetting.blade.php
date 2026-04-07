@extends('layout.app')

@section('title', 'Smtp Settings')

@section('content')
<style>
      a.btn.back-button {
    background: #ff9f43;
    color: #fff;
}
</style>
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>SMTP Settings</h4>
            </div>
            <div class="back-button">
                <a href="{{ route('auth.dashboard') }}" class="btn back-button"> <i class="fa-solid fa-arrow-left"></i> Back</a></br>
                            <span class="success_submit text-danger"></span>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>SMTP Host<span class="manitory">*</span></label>
                            <input type="text" id="smtp_host" class="form-control" placeholder="smtp.hostinger.com">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>SMTP Username<span class="manitory">*</span></label>
                            <input type="text" id="smtp_username" class="form-control" placeholder="support@example.com">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <label>Password</label>

                        <div class="input-group mt-2">
                            <input type="password" id="smtp_password" class="form-control"
                                placeholder="Leave blank to keep old password">

                            <span class="input-group-text" id="togglePassword" style="cursor:pointer;">
                                <i class="fa fa-eye" id="eyeIcon"></i>
                            </span>
                        </div>

                        {{-- <small class="text-muted">Leave blank to keep current password</small> --}}
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>SMTP Port<span class="manitory">*</span></label>
                            <input type="number" id="smtp_port" class="form-control" placeholder="465">
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Encryption</label>
                            <select id="smtp_encryption" class="form-select">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                                <option value="none">None</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3 col-sm-6 col-6">
                        <div class="form-group">
                            <label>Status</label>
                            <select id="smtp_status" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-12 mt-3">
                        <a href="javascript:void(0);" class="btn btn-submit me-2" id="btn-smtp-submit">Update Settings</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // SHOW / HIDE PASSWORD
            $("#togglePassword").on("click", function() {

                let passwordInput = $("#smtp_password");
                let icon = $("#eyeIcon");

                if (passwordInput.attr("type") === "password") {
                    passwordInput.attr("type", "text");
                    icon.removeClass("fa-eye").addClass("fa-eye-slash");
                } else {
                    passwordInput.attr("type", "password");
                    icon.removeClass("fa-eye-slash").addClass("fa-eye");
                }
            });

            const authToken = localStorage.getItem("authToken");
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
            const $smtpSubmitBtn = $("#btn-smtp-submit");
            const smtpSubmitBtnDefaultHtml = $smtpSubmitBtn.html();

            function toggleSmtpSubmitLoading(isLoading) {
                if (isLoading) {
                    $smtpSubmitBtn
                        .addClass("disabled")
                        .attr("aria-disabled", "true")
                        .css("pointer-events", "none")
                        .html(
                            '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Saving...'
                        );
                } else {
                    $smtpSubmitBtn
                        .removeClass("disabled")
                        .removeAttr("aria-disabled")
                        .css("pointer-events", "")
                        .html(smtpSubmitBtnDefaultHtml);
                }
            }

            // ─── LOAD SMTP SETTINGS ───────────────────────────────────────────
            function loadSmtpSettings() {
                let url = "{{ route('smtp-settings.show') }}";
                if (selectedSubAdminId) url += "?selectedSubAdminId=" + selectedSubAdminId;

                $.ajax({
                    url: url,
                    type: "GET",
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        const s = response.settings;
                        if (!s) return;

                        $("#smtp_host").val(s.host ?? '');
                        $("#smtp_username").val(s.username ?? '');
                        $("#smtp_port").val(s.port ?? '');
                        $("#smtp_encryption").val(s.encryption ?? 'ssl');
                        $("#smtp_status").val(s.status ?? 1);
                        // password intentionally not filled
                    }
                });
            }

            loadSmtpSettings();

            // ─── UPDATE SMTP SETTINGS ─────────────────────────────────────────
            $("#btn-smtp-submit").on("click", function(e) {
                e.preventDefault();
                if ($smtpSubmitBtn.hasClass("disabled")) {
                    return;
                }
                $(".text-danger").remove();

                let hasError = false;
                const required = [{
                        id: "smtp_host",
                        name: "SMTP Host"
                    },
                    {
                        id: "smtp_username",
                        name: "SMTP Username"
                    },
                    {
                        id: "smtp_port",
                        name: "SMTP Port"
                    },
                ];

                required.forEach(function(field) {
                    if (!$("#" + field.id).val()) {
                        $("#" + field.id).after(
                            `<div class="text-danger mt-1">${field.name} is required</div>`);
                        hasError = true;
                    }
                });

                if (hasError) return;

                let formData = new FormData();
                formData.append("host", $("#smtp_host").val());
                formData.append("username", $("#smtp_username").val());
                formData.append("port", $("#smtp_port").val());
                formData.append("encryption", $("#smtp_encryption").val());
                formData.append("status", $("#smtp_status").val());
                formData.append("selectedSubAdminId", selectedSubAdminId ?? '');
                formData.append("_token", "{{ csrf_token() }}");

                const password = $("#smtp_password").val();
                if (password) formData.append("password", password);

                $.ajax({
                    url: "{{ route('smtp-settings.update') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        toggleSmtpSubmitLoading(true);
                    },
                    headers: {
                        "Authorization": "Bearer " + authToken
                    },
                    success: function(response) {
                        Swal.fire({
                            title: "Success!",
                            text: response.message,
                            icon: "success",
                            confirmButtonText: "OK",
                            confirmButtonColor: "#ff9f43",
                        }).then(() => window.location.reload());
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                const fieldMap = {
                                    host: "smtp_host",
                                    username: "smtp_username",
                                    port: "smtp_port",
                                    password: "smtp_password",
                                };
                                const fieldId = fieldMap[key] || key;
                                $("#" + fieldId).after(
                                    `<div class="text-danger mt-1">${value[0]}</div>`
                                    );
                            });
                        } else {
                            Swal.fire("Error!", xhr.responseJSON?.message ??
                                "Something went wrong!", "error");
                        }
                    },
                    complete: function() {
                        toggleSmtpSubmitLoading(false);
                    }
                });
            });
        });
    </script>
@endpush
