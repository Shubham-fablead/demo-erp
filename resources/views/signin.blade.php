<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Fablead Inventory-Billing Software">
    <meta name="keywords"
        content="inventory management, billing system, invoice generator, purchase orders, inventory control, POS system, admin dashboard">
    <meta name="author" content="Fablead Developers Technolab">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>

    <link rel="shortcut icon" type="image/x-icon" href="https://erp-demo.fableadtech.com/public//storage/favicons/YY2brRHM2c0vlHHzLgHeXKO4Bk9ifMVjMYK71uGg.webp">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/css/bootstrap.min.css' }}">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/plugins/fontawesome/css/fontawesome.min.css'}}">
    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/plugins/fontawesome/css/all.min.css'}}">

    <link rel="stylesheet" href="{{ env('ImagePath') . 'admin/assets/css/style.css' }}">
    <style>
        /* .logo-img {
            margin-left: 9rem;
        } */

        body.account-page {
            padding-bottom: 50px; /* Space for sticky footer */
        }

        @media screen and (max-width: 768px) {
            .login-wrapper .login-content {
                width: 90% !important;
            }

            .logo-img {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
@php
    use App\Models\Setting;
    $settings = Setting::first();
@endphp

<body class="account-page">

    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper">
                <div class="login-content">
                    <div class="login-userset">
                        <div class="login-logo">
                            <img src="{{ env('ImagePath') . '/storage/' . $settings->logo ?? 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp'}}"
                                alt="img" class="logo-img">
                        </div>
                        <div class="login-userheading">
                            <h3>Sign In</h3>
                            <h4>Please login to your account</h4>
                        </div>
                        <form id="loginForm">
                            <div class="form-login">
                                <label>Email</label>
                                <div class="form-addons">
                                    <input type="text" id="email" placeholder="Enter your email address">
                                    <img src="{{ env('ImagePath') . 'admin/assets/img/icons/mail.svg'}}" alt="img">
                                </div>
                                <small id="emailError" class="text-danger"></small>
                            </div>

                            <div class="form-login">
                                <label>Password</label>
                                <div class="pass-group">
                                    <input type="password" id="password" class="pass-input"
                                        placeholder="Enter your password">
                                    <span class="fas toggle-password fa-eye-slash"></span>
                                </div>
                                <small id="passwordError" class="text-danger"></small>
                            </div>

                            <div class="form-login">
                                <div class="alreadyuser">
                                    <!-- <h4><a href="forgetpassword.html" class="hover-a">Forgot Password?</a></h4> -->
                                </div>
                            </div>

                            <div class="form-login">
                                <button type="submit" class="btn btn-login">Sign In</button>
                            </div>

                            <div id="loginMessage" class="text-danger"></div>
                        </form>

                    </div>
                </div>
                <!-- <div class="login-img">
                    <img src="admin/assets/img/login.jpg" alt="img">
                </div> -->
            </div>
        </div>
    </div>
    <footer style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; padding: 10px 0; background-color: #f4f4f4; height: 50px; z-index: 1000; box-shadow: 0 -2px 5px rgba(0,0,0,0.1);">
        <h1 style="font-size: 14px; font-weight: 600; margin: 0;">© <?= date('Y') ?> <a href="https://fableadtechnolabs.com" target="_blank" style="color: inherit; text-decoration: none;">Copyright - Fablead Developers Technolab</a></h1>
    </footer>

    <script src="{{ env('ImagePath') . 'admin/assets/js/jquery-3.6.0.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/feather.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/bootstrap.bundle.min.js'}}"></script>

    <script src="{{ env('ImagePath') . 'admin/assets/js/script.js'}}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            $("#loginForm").submit(function (e) {
            const selectedSubAdminId = localStorage.getItem("selectedSubAdminId");
// console.log(selectedSubAdminId);

        e.preventDefault();

        let email = $("#email").val().trim();
        let password = $("#password").val().trim();
        $("#emailError, #passwordError, #loginMessage").text("");

        if (!email) {
            $("#emailError").text("Email is required.");
            return;
        }
        if (!password) {
            $("#passwordError").text("Password is required.");
            return;
        }

        let $submitBtn = $("#loginForm button[type='submit']");
        let originalText = $submitBtn.html();
        $submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Logging in...').prop("disabled", true);

        function performLogin(forceLogin = false) {
            $.ajax({
                url: "/",
                type: "POST",
                dataType: "json",
                data: {
                    email: email,
                    selectedSubAdminId: selectedSubAdminId,
                    password: password,
                    force: forceLogin ? 1 : 0,
                    _token: $('meta[name="csrf-token"]').attr('content'),
                },
                success: function (response) {
                    // console.log("Response:", response);

                    // ⚠️ Show warning popup if already checked in/out
                    // if (response.warning && response.status === true && !forceLogin) {
                    //     Swal.fire({
                    //         title: "Warning",
                    //         text: response.message,
                    //         icon: "warning",
                    //         showCancelButton: true,
                    //         confirmButtonText: "Yes, log me in",
                    //         cancelButtonText: "Cancel"
                    //     }).then((result) => {
                    //         if (result.isConfirmed) {
                    //             performLogin(true);
                    //         }
                    //     });
                    //     return;
                    // }

                    // ✅ Successful login
                    if (response.status && response.token) {
                        // console.log('response.user.branch_id', response.user.branch_id);
                        var selectedId;
                        if(response.user.branch_id != null){
                            selectedId = response.user.branch_id;
                            // console.log('Using branch_id:', selectedId);
                        } else {
                            selectedId = response.user.id;
                            // console.log('Using user.id:', selectedId);
                        }

                        if (selectedId) {
                            // console.log('selectedId', selectedId);

                            // Set localStorage with the correct selectedId
                            localStorage.setItem('selectedSubAdminId', selectedId);

                            // Get the value from localStorage after setting it (not the old constant)
                            var storedSubAdminId = localStorage.getItem('selectedSubAdminId');
                            // console.log('selectedSubAdminId-get from local storage after setting:', storedSubAdminId);

                            $.post('/set-subadmin-session', {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                subAdminId: selectedId
                            }, function() {
                                // console.log('Session set with subAdminId:', selectedId);
                                // Redirect to dashboard after session is set
                                // window.location.href = "{{ route('auth.dashboard') }}";
                            });

                        } else if (selectedText === "Main Branch") {
                            $.post('/clear-subadmin-session', {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            }, function() {
                                // console.log('Session cleared');
                                localStorage.removeItem('selectedSubAdminId');
                                // window.location.href = '/dashboard';
                            });
                        }
                        localStorage.setItem("authToken", response.token);
                        localStorage.setItem("selectedSubAdminId", selectedId); // Use selectedId instead of response.user.id
                        window.location.href = response.redirect;
                    } else if (response.error) {
                        $("#loginMessage").text(response.error).css("color", "red");
                    }
                },
                error: function (xhr) {
                    // console.error("Error:", xhr.responseText);
                    let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || "Login failed.";
                    $("#loginMessage").text(msg).css("color", "red");
                },
                complete: function () {
                    $submitBtn.html(originalText).prop("disabled", false);
                }
            });
        }

        // 🚀 Start normal login (no force)
        performLogin(false);
    });

        });
    </script>
</body>

</html>
