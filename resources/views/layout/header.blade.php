@php
    $settings = App\Models\Setting::first();
    $admin = App\Models\User::where('role', 'admin')->first();
@endphp

<style>
    .select2-container--default .select2-selection--single .select2-selection__clear {
        display: none;
    }

    .web_button {
        width: 25px;
        height: 24px;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .bar-icon span {
        display: block;
        height: 3px;
        width: 100%;
        background-color: #333;
        margin: 3px 0;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    .search-view {
        width: 465px;
    }

    @media (max-width: 991px) {
        .web_button {
            display: none;
        }
    }

    /* iPad landscape specific fixes */
    @media (min-width: 992px) and (max-width: 1024px) {
        .web_button {
            display: flex !important;
            z-index: 1000;
            position: relative;
        }

        #toggle_btn1 {
            pointer-events: auto !important;
            cursor: pointer !important;
        }
    }

    /* iPad Mini / iPad Air portrait header alignment */
    @media (min-width: 768px) and (max-width: 991px) {
        .header {
            position: relative;
            display: flex;
            align-items: center;
            min-height: 60px;
            padding: 0 14px;
        }

        .header-left {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            width: auto !important;
            margin: 0;
            padding: 0;
            z-index: 2;
        }

        .header-left .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .header-left .logo .logo-view {
            max-width: 130px !important;
            margin: 0 auto !important;
        }

        #mobile_btn {
            display: flex !important;
            margin-right: auto;
            z-index: 3;
        }

        #toggle_btn1 {
            display: none !important;
        }

        .nav.user-menu {
            margin-left: auto;
            align-items: center;
            z-index: 3;
        }

        .nav.user-menu .user-img img {
            width: 34px;
            height: 34px;
            object-fit: cover;
            border-radius: 50%;
        }
    }

    /* iPad Pro specific (1024px) */
    @media screen and (width: 1024px) {
        .web_button {
            display: flex !important;
        }

        .mobile_btn {
            display: none !important;
        }
    }

    @media (max-width: 983px) and (min-width: 575px) {
        .logo-view {
            float: left;
            margin-left: 3rem;
        }

        .search-view {
            width: 366px;
        }
    }

    div#subBranchContainer {
        width: 200px !important;
    }

    /* Mobile Bottom Navigation */
    .mobile-bottom-nav {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: #ff9f43;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        display: none;
        justify-content: space-around;
        align-items: center;
        padding: 8px 0;
        z-index: 1050;
    }

    .mobile-bottom-nav .nav-item {
        text-align: center;
        color: #fff;
        text-decoration: none;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: color 0.3s;
    }

    .mobile-bottom-nav .nav-item i {
        font-size: 18px;
        margin-bottom: 4px;
    }

    .mobile-bottom-nav .nav-item span {
        font-size: 11px;
        font-weight: 500;
    }

    .mobile-bottom-nav .nav-item.active {
        color: #1b2850;
    }

    .notification-wrapper {
        position: relative;
    }

    /* Bell */
    .notification-bell {
        font-size: 20px;
        color: #1b2850;
        transition: all 0.3s ease;
    }

    .notification-bell:hover {
        color: #ff9f43;
    }

    /* Badge */
    .notification-badge {
        position: absolute;
        top: -4px;
        right: -0px;
        background: #ff3b30;
        color: #fff;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 50px;
        min-width: 18px;
        text-align: center;
        font-weight: bold;
        animation: pulse 2s infinite;
    }

    /* Dropdown */
    .notification-dropdown {
        position: absolute;
        top: 45px;
        right: 0;
        width: 350px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        display: none;
        overflow: hidden;
        z-index: 9999;
        border: 1px solid #eaeaea;
    }

    /* Header */
    .notification-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        font-weight: 600;
        border-bottom: 1px solid #eaeaea;
        background: #fff;
    }

    .notification-header a {
        font-size: 12px;
        color: #ff9f43;
        text-decoration: none;
        font-weight: 500;
    }

    .notification-header a:hover {
        text-decoration: underline;
    }

    /* Body */
    .notification-body {
        max-height: 380px;
        overflow-y: auto;
        background: #fff;
    }

    /* Empty State */
    .empty-notification {
        padding: 40px 20px;
        text-align: center;
        color: #8c8c8c;
        font-size: 14px;
    }

    .empty-notification i {
        font-size: 40px;
        margin-bottom: 10px;
        display: block;
        color: #ddd;
    }

    /* Notification Item */
    .notification-item {
        padding: 12px 15px;
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: 0.2s;
        position: relative;
    }

    .notification-item:hover {
        background: #fef9f0;
    }

    .notification-item.unread-notification {
        background: #fff9f0;
        border-left: 3px solid #ff9f43;
    }

    .notification-dot {
        width: 8px;
        height: 8px;
        background-color: #ff3b30;
        border-radius: 50%;
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .notification-title {
        font-size: 14px;
        font-weight: 600;
        color: #1b2850;
        display: block;
        margin-bottom: 4px;
    }

    .notification-message {
        font-size: 12px;
        color: #6c757d;
        display: block;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .notification-time {
        font-size: 11px;
        color: #adb5bd;
    }

    .notification-time i {
        font-size: 10px;
        margin-right: 3px;
    }

    .notification-footer {
        padding: 10px;
        border-top: 1px solid #eaeaea;
        text-align: center;
        background: #fff;
        position: sticky;
        bottom: 0;
    }

    .notification-footer button {
        color: #ff9f43;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
    }

    .notification-footer button:hover {
        text-decoration: underline;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.1);
        }
        100% {
            transform: scale(1);
        }
    }

    @media (max-width: 991px) {
        .mobile-bottom-nav {
            display: flex;
        }

        body {
            padding-bottom: 60px !important;
        }

        .sidebar {
            bottom: 60px !important;
        }

        .sidebar-inner,
        .slimScrollDiv {
            height: calc(100vh - 120px) !important;
        }

        .tab-view {
            display: none !important;
        }

        .notification-dropdown {
            width: 320px;
            right: -10px;
        }
    }
</style>

@php
    $user = auth()->user();
    $logoRedirectRoute = $user && $user->role === 'staff' ? route('auth.profile') : route('auth.dashboard');
@endphp

<div class="header">
    <div class="header-left active">
        <a href="{{ $logoRedirectRoute }}" class="logo">
            <img src="{{ !empty($settings) && !empty($settings->logo) ? env('ImagePath') . '/storage/' . $settings->logo : 'https://fableadtechnolabs.com/static/media/250x150%20(1).b3f5a4db48c7770366ef.webp' }}"
                alt="" style="max-width: 80% !important;" class="logo-view">
        </a>
        <a href="{{ $logoRedirectRoute }}" class="logo-small">
            <img src="{{ !empty($settings) && !empty($settings->favicon) ? env('ImagePath') . '/storage/' . $settings->favicon : 'https://fableadtechnolabs.com/favicon-192x192.webp' }}"
                alt="">
        </a>

        <div id="toggle_btn1" class="web_button">
            <span class="bar-icon">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </div>
    </div>

    <a id="mobile_btn" class="mobile_btn" href="#sidebar">
        <span class="bar-icon">
            <span></span>
            <span></span>
            <span></span>
        </span>
    </a>

    <ul class="nav user-menu">
        <div class="d-flex align-items-center header-search-container tab-view">
            @if (in_array($user->role, ['admin']))
                <div class="me-3" id="subBranchContainer" style="display: none;">
                    <div class="d-flex align-items-center">
                        <select id="subBrandSelect" class="form-select form-select-sm" style="width: 300px;">
                        </select>
                        <div id="currentSelection" class="ms-2 text-muted d-none" style="font-size: 12px;"></div>
                    </div>
                </div>
            @endif

            <!-- Search Field Container -->
            <div class="header-search d-flex align-items-center position-relative me-3 ">
                <!-- Search Icon -->
                <img src="{{ env('ImagePath') . '/admin/assets/img/icons/search.svg' }}" alt="Search"
                    style="position: absolute; left: 12px; width: 18px; height: 18px; z-index: 10; opacity: 0.6;">

                <!-- Input Field -->
                <input type="text" id="customerSearch" class="form-control form-control-sm rounded px-3 ps-5"
                    placeholder="Search..." autocomplete="off" style="height: 38px; font-size: 14px;">

                <!-- Search Results -->
                <div id="searchResults" class="list-group bg-white position-absolute rounded shadow mt-1 w-100"
                    style="z-index: 1050; max-height: 400px; overflow-y: auto; display: none; top: 100%; left: 0;">
                </div>
            </div>

           <!-- Notifications -->
<li class="nav-item dropdown me-3 notification-wrapper">
    <a href="javascript:void(0);" class="nav-link position-relative" id="notificationToggle">
        <i class="fa fa-bell notification-bell"></i>
        <span id="notificationCount" class="notification-badge d-none">0</span>
    </a>

    <!-- Notification Dropdown -->
    <div id="notificationMenu" class="notification-dropdown">
        <div class="notification-header">
            <span>Notifications</span>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('notifications.index') }}" class="text-decoration-none">View All</a>
                {{-- <button onclick="markAllNotificationsAsRead()" id="markAllReadBtn" class="btn btn-link btn-sm p-0 text-decoration-none" style="display: none;">Mark all as read</button> --}}
            </div>
        </div>
        <div class="notification-body" id="notificationList">
            <div class="empty-notification">
                <i class="fa fa-bell-slash"></i>
                No notifications
            </div>
        </div>
        <div class="notification-footer">
            <a href="{{ route('notifications.index') }}" class="text-decoration-none">View all notifications →</a>
        </div>
    </div>
</li>

            <!-- New Order Button -->
            @if (in_array($user->role, ['sales-manager', 'inventory-manager', 'admin']))
                <a href="/add-sales"
                    class="btn btn-sm d-flex align-items-center justify-content-center me-3 header-new-order-button"
                    style="height: 38px; background-color: #ff9f43; color: white; border-radius: 6px;">
                    <i class="fa fa-plus me-1"></i> New Order
                </a>
            @endif
        </div>

        <li class="nav-item dropdown has-arrow main-drop">
            <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                <span class="user-img">
                    <img src="{{ !empty($user->profile_image) ? env('ImagePath') . '/storage/' . $user->profile_image : env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}"
                        alt="">
                    <span class="status online"></span>
                </span>
            </a>
            <div class="dropdown-menu menu-drop-user">
                <div class="profilename">
                    <div class="profileset">
                        <span class="user-img">
                            <img src="{{ !empty($user->profile_image) ? env('ImagePath') . '/storage/' . $user->profile_image : env('ImagePath') . '/admin/assets/img/customer/customer5.jpg' }}"
                                alt="">
                        </span>
                        <div class="profilesets">
                            <h6>{{ $user->name ?? 'User' }}</h6>
                            <h5>{{ ucfirst($user->role ?? 'user') }}</h5>
                        </div>
                    </div>
                    <hr class="m-0">
                    <a class="dropdown-item" href="{{ route('auth.profile') }}">
                        <i class="me-2" data-feather="user"></i> My Profile
                    </a>
                    @if ($user->role === 'admin')
                        <a class="dropdown-item" href="{{ route('setting.generalsettings') }}">
                            <i class="me-2" data-feather="settings"></i> Settings
                        </a>
                        <a class="dropdown-item new_branch" href="{{ route('subbranch.list') }}">
                            <i class="me-2" data-feather="layers"></i> My Branch
                        </a>
                    @endif
                    <hr class="m-0">
                    <a class="dropdown-item logout pb-0" href="{{ route('logout') }}">
                        <img src="{{ env('ImagePath') . '/admin/assets/img/icons/log-out.svg' }}" class="me-2"
                            alt="img"> Logout
                    </a>
                </div>
            </div>
        </li>
    </ul>

    <div class="dropdown mobile-user-menu">
        <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
            aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('auth.profile') }}">My Profile</a>
            @if ($user->role === 'admin')
                <a class="dropdown-item" href="{{ route('setting.generalsettings') }}">Settings</a>
                <a class="dropdown-item new_branch" href="{{ route('subbranch.list') }}">My Branch</a>
            @endif
            <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
        </div>
    </div>
</div>

@push('js')
<script>
    const currentUserRole = "{{ auth()->user()->role }}";
    const currentUserId = "{{ auth()->user()->id }}";
</script>

<script>
    // // ==================== SEARCH FUNCTIONALITY ====================
    // document.addEventListener('DOMContentLoaded', function() {
    //     const searchInput = document.getElementById('customerSearch');
    //     const resultBox = document.getElementById('searchResults');

    //     if (searchInput) {
    //         searchInput.addEventListener('input', function() {
    //             const query = this.value.trim();

    //             if (query.length < 1) {
    //                 resultBox.style.display = 'none';
    //                 return;
    //             }

    //             fetch(`/search-users?query=${encodeURIComponent(query)}`)
    //                 .then(response => response.json())
    //                 .then(data => {
    //                     resultBox.innerHTML = '';
    //                     let hasResults = false;

    //                     // Customers section
    //                     if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.users?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light">Customers</div>`;
    //                         data.users.forEach(user => {
    //                             const profileUrl = `/customer-view/${user.id}`;
    //                             resultBox.innerHTML += `
    //                                 <a href="${profileUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${user.profile_image}" alt="User Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(user.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">${escapeHtml(user.email) ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Vendors section
    //                     if (['admin', 'purchase-manager', 'inventory-manager'].includes(currentUserRole) && data.vendors?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Vendors</div>`;
    //                         data.vendors.forEach(vendor => {
    //                             const vendorUrl = `/vendor-view/${vendor.id}`;
    //                             resultBox.innerHTML += `
    //                                 <a href="${vendorUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${vendor.profile_image}" alt="Vendor Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(vendor.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">${escapeHtml(vendor.email) ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Products section
    //                     if (['admin', 'purchase-manager', 'inventory-manager'].includes(currentUserRole) && data.products?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Products</div>`;
    //                         data.products.forEach(product => {
    //                             resultBox.innerHTML += `
    //                                 <a href="/product-view/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center">
    //                                     <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:35px; height:35px; object-fit: cover;">
    //                                     <div>
    //                                         <strong>${escapeHtml(product.name) ?? 'N/A'}</strong><br>
    //                                         <small class="text-muted">Price: ₹${product.price ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     // Orders section
    //                     if (['admin', 'sales-manager', 'inventory-manager'].includes(currentUserRole) && data.orders?.length > 0) {
    //                         hasResults = true;
    //                         resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Orders</div>`;
    //                         data.orders.forEach(order => {
    //                             resultBox.innerHTML += `
    //                                 <a href="/sales-details/${order.id}" class="list-group-item list-group-item-action">
    //                                     <div class="d-flex align-items-center mb-1">
    //                                         <img src="{{ env('ImagePath') . '/admin/assets/img/icons/cart.svg' }}" width="20" height="20" class="me-2" alt="cart">
    //                                         <strong>Order #: ${order.order_number ?? 'N/A'}</strong>
    //                                     </div>
    //                                     <div>
    //                                         <small>Customer: ${escapeHtml(order.user_name) ?? 'N/A'}</small><br>
    //                                         <small>Total: ₹${order.total_amount ?? 'N/A'} | Status: ${order.payment_status ?? 'N/A'}</small>
    //                                     </div>
    //                                 </a>
    //                             `;
    //                         });
    //                     }

    //                     if (!hasResults) {
    //                         resultBox.innerHTML = '<div class="list-group-item text-center text-muted">No results found</div>';
    //                     }

    //                     resultBox.style.display = 'block';
    //                 })
    //                 .catch(error => {
    //                     console.error('Search error:', error);
    //                     resultBox.innerHTML = '<div class="list-group-item text-center text-danger">Error loading results</div>';
    //                     resultBox.style.display = 'block';
    //                 });
    //         });
    //     }

    //     // Hide dropdown on outside click
    //     document.addEventListener('click', function(e) {
    //         if (searchInput && resultBox && !searchInput.contains(e.target) && !resultBox.contains(e.target)) {
    //             resultBox.style.display = 'none';
    //         }
    //     });
    // });

    // // Helper function to escape HTML
    // function escapeHtml(text) {
    //     if (!text) return '';
    //     const div = document.createElement('div');
    //     div.textContent = text;
    //     return div.innerHTML;
    // }

    // // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
    // $(document).on('mouseover', function(e) {
    //     const $toggleBtn = $('#toggle_btn1');
    //     const isTabletSize = $(window).width() >= 768 && $(window).width() <= 1024;
    //     const isButtonAvailable = $toggleBtn.length > 0 && (isTabletSize || $toggleBtn.is(':visible'));

    //     if ($('body').hasClass('mini-sidebar') && isButtonAvailable) {
    //         const isInsideSidebar = $(e.target).closest('.sidebar').length;
    //         if (isInsideSidebar) {
    //             $('body').addClass('expand-menu');
    //             $('.subdrop + ul').slideDown();
    //         } else {
    //             $('body').removeClass('expand-menu');
    //             $('.subdrop + ul').slideUp();
    //         }
    //     }
    // });

    // // Toggle button handler
    // $(document).on('click', '#toggle_btn1', function(e) {
    //     e.preventDefault();
    //     e.stopPropagation();

    //     if ($(this).data('processing')) {
    //         return false;
    //     }
    //     $(this).data('processing', true);

    //     const body = $('body');
    //     const $btn = $(this);

    //     if (body.hasClass('mini-sidebar')) {
    //         body.removeClass('mini-sidebar');
    //         $btn.addClass('active');
    //         $('.subdrop + ul').slideDown();
    //         localStorage.setItem('screenModeNightTokenState', 'night');
    //         setTimeout(function() {
    //             body.removeClass('mini-sidebar');
    //             $('.header-left').addClass('active');
    //         }, 100);
    //     } else {
    //         body.addClass('mini-sidebar');
    //         $btn.removeClass('active');
    //         $('.subdrop + ul').slideUp();
    //         localStorage.removeItem('screenModeNightTokenState');
    //         setTimeout(function() {
    //             body.addClass('mini-sidebar');
    //             $('.header-left').removeClass('active');
    //         }, 100);
    //     }

    //     setTimeout(() => {
    //         $btn.data('processing', false);
    //     }, 300);

    //     return false;
    // });

    // // ==================== BRANCH DROPDOWN FUNCTIONALITY ====================
    // (function initializeBranchDropdown() {
    //     const container = document.getElementById('subBranchContainer');
    //     const select = document.getElementById('subBrandSelect');
    //     if (container) {
    //         container.style.display = 'block';
    //     }
    //     if (select) {
    //         select.innerHTML = "";
    //         const mainOption = document.createElement('option');
    //         mainOption.value = "";
    //         mainOption.textContent = 'Main Branch';
    //         select.appendChild(mainOption);
    //         if (window.$ && $.fn && $.fn.select2) {
    //             $('#subBrandSelect').select2({
    //                 placeholder: 'Select a branch',
    //                 allowClear: true
    //             });
    //         }
    //     }
    // })();

    // fetch('/api/get_subadmin', {
    //     headers: {
    //         "Authorization": "Bearer " + localStorage.getItem("authToken"),
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // })
    // .then(res => res.json())
    // .then(response => {
    //     if (!response) {
    //         console.warn('No response from /api/get_subadmin');
    //         return;
    //     }

    //     const select = document.getElementById("subBrandSelect");
    //     if (!select) return;

    //     select.innerHTML = "";
    //     document.getElementById('subBranchContainer').style.display = 'block';

    //     const mainOption = document.createElement("option");
    //     mainOption.value = "";
    //     mainOption.textContent = "Main Branch";
    //     select.appendChild(mainOption);

    //     (response.data || []).forEach(function(item) {
    //         const option = document.createElement("option");
    //         option.value = item.id;
    //         option.textContent = item.name;
    //         select.appendChild(option);
    //     });

    //     if (window.$ && $.fn && $.fn.select2) {
    //         $('#subBrandSelect').select2({
    //             placeholder: 'Select a branch',
    //             allowClear: true
    //         });
    //     }

    //     const savedId = localStorage.getItem('selectedSubAdminId');
    //     if (savedId) {
    //         const exists = (response.data || []).some(function(item) {
    //             return String(item.id) === String(savedId);
    //         });
    //         if (exists) {
    //             $('#subBrandSelect').val(savedId).trigger('change.select2');
    //         } else {
    //             $.post('/clear-subadmin-session', {
    //                 _token: $('meta[name="csrf-token"]').attr('content')
    //             }, function() {
    //                 localStorage.removeItem('selectedSubAdminId');
    //             });
    //         }
    //     }
    // })
    // .catch(function(error) {
    //     console.error('Failed to load sub-admin list:', error);
    // });

    // $('#subBrandSelect').on('change', function() {
    //     const selectedId = $(this).val();
    //     const selectedText = $(this).find('option:selected').text() || '';
    //     $('#currentSelection').text(selectedId ? ('Selected: ' + selectedText) : '');

    //     if (selectedId) {
    //         localStorage.setItem('selectedSubAdminId', selectedId);
    //         $.post('/set-subadmin-session', {
    //             _token: $('meta[name="csrf-token"]').attr('content'),
    //             subAdminId: selectedId
    //         }, function() {
    //             window.location.href = "{{ route('auth.dashboard') }}";
    //         });
    //     } else if (selectedText === "Main Branch") {
    //         $.post('/clear-subadmin-session', {
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         }, function() {
    //             localStorage.removeItem('selectedSubAdminId');
    //             window.location.href = '/dashboard';
    //         });
    //     }
    // });

    // // ==================== NOTIFICATION FUNCTIONALITY ====================
    // let notificationRefreshInterval = null;

    // document.addEventListener('DOMContentLoaded', function() {
    //     initializeNotifications();

    //     // Auto-refresh every 30 seconds
    //     if (notificationRefreshInterval) {
    //         clearInterval(notificationRefreshInterval);
    //     }
    //     notificationRefreshInterval = setInterval(() => {
    //         const menu = document.getElementById('notificationMenu');
    //         if (menu && menu.style.display === 'block') {
    //             loadNotifications();
    //         }
    //     }, 30000);
    // });

    // function initializeNotifications() {
    //     const notificationToggle = document.getElementById('notificationToggle');
    //     const notificationMenu = document.getElementById('notificationMenu');

    //     if (notificationToggle) {
    //         // Remove any existing event listeners
    //         const newToggle = notificationToggle.cloneNode(true);
    //         notificationToggle.parentNode.replaceChild(newToggle, notificationToggle);

    //         newToggle.addEventListener('click', function(e) {
    //             e.preventDefault();
    //             e.stopPropagation();

    //             if (notificationMenu.style.display === 'block') {
    //                 notificationMenu.style.display = 'none';
    //             } else {
    //                 loadNotifications();
    //                 notificationMenu.style.display = 'block';
    //             }
    //         });
    //     }

    //     // Close dropdown when clicking outside
    //     document.addEventListener('click', function(e) {
    //         if (notificationToggle && notificationMenu) {
    //             if (!notificationToggle.contains(e.target) && !notificationMenu.contains(e.target)) {
    //                 notificationMenu.style.display = 'none';
    //             }
    //         }
    //     });

    //     // Initial load
    //     loadNotifications();
    // }

    // function loadNotifications() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) {
    //         console.error('CSRF token not found');
    //         return;
    //     }

    //     fetch('/notifications', {
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json',
    //             'Accept': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => {
    //         if (!response.ok) {
    //             throw new Error('Network response was not ok');
    //         }
    //         return response.json();
    //     })
    //     .then(response => {
    //         const list = document.getElementById('notificationList');
    //         const count = document.getElementById('notificationCount');
    //         const markAllBtn = document.getElementById('markAllReadBtn');

    //         if (!list) return;

    //         list.innerHTML = '';

    //         if (!response.status || !response.data || response.data.length === 0) {
    //             list.innerHTML = `
    //                 <div class="empty-notification">
    //                     <i class="fa fa-bell-slash"></i>
    //                     No notifications
    //                 </div>
    //             `;
    //             if (count) count.classList.add('d-none');
    //             if (markAllBtn) markAllBtn.style.display = 'none';
    //             return;
    //         }

    //         // Update count badge
    //         if (count) {
    //             const unreadCount = response.count || response.data.filter(n => !n.is_read).length;
    //             if (unreadCount > 0) {
    //                 count.innerText = unreadCount > 99 ? '99+' : unreadCount;
    //                 count.classList.remove('d-none');
    //             } else {
    //                 count.classList.add('d-none');
    //             }
    //         }

    //         // Show mark all button if there are unread notifications
    //         if (markAllBtn) {
    //             const hasUnread = response.data.some(n => !n.is_read);
    //             markAllBtn.style.display = hasUnread ? 'block' : 'none';
    //         }

    //         // Render notifications
    //         response.data.forEach(item => {
    //             const notificationItem = document.createElement('div');
    //             notificationItem.className = `notification-item ${!item.is_read ? 'unread-notification' : ''}`;
    //             notificationItem.setAttribute('data-id', item.id);
    //             notificationItem.onclick = function() {
    //                 handleNotificationClick(item.id, item.link);
    //             };

    //             notificationItem.innerHTML = `
    //                 <div class="d-flex justify-content-between align-items-start">
    //                     <div class="flex-grow-1">
    //                         <strong class="notification-title">${escapeHtml(item.title)}</strong>
    //                         <span class="notification-message">${escapeHtml(item.message)}</span>
    //                         <small class="notification-time">
    //                             <i class="fa fa-clock-o"></i>
    //                             ${formatDate(item.created_at)}
    //                         </small>
    //                     </div>
    //                     ${!item.is_read ? '<span class="notification-dot"></span>' : ''}
    //                 </div>
    //             `;

    //             list.appendChild(notificationItem);
    //         });
    //     })
    //     .catch(error => {
    //         console.error('Error loading notifications:', error);
    //         const list = document.getElementById('notificationList');
    //         if (list) {
    //             list.innerHTML = `
    //                 <div class="empty-notification text-danger">
    //                     <i class="fa fa-exclamation-circle"></i>
    //                     Failed to load notifications
    //                 </div>
    //             `;
    //         }
    //     });
    // }

    // function handleNotificationClick(id, link) {
    //     // Mark as read
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch(`/notifications/${id}/read`, {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(() => {
    //         // Update the UI
    //         const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
    //         if (notificationItem) {
    //             notificationItem.classList.remove('unread-notification');
    //             const dot = notificationItem.querySelector('.notification-dot');
    //             if (dot) dot.remove();
    //         }

    //         // Update count
    //         updateNotificationCount();

    //         // Redirect if link exists
    //         if (link && link !== '#') {
    //             window.location.href = link;
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error marking notification as read:', error);
    //         // Still redirect even if marking fails
    //         if (link && link !== '#') {
    //             window.location.href = link;
    //         }
    //     });
    // }

    // function markAllNotificationsAsRead() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch('/notifications/mark-all-read', {
    //         method: 'POST',
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => response.json())
    //     .then(() => {
    //         // Reload notifications to update UI
    //         loadNotifications();
    //     })
    //     .catch(error => {
    //         console.error('Error marking all as read:', error);
    //     });
    // }

    // function updateNotificationCount() {
    //     const token = document.querySelector('meta[name="csrf-token"]');
    //     if (!token) return;

    //     fetch('/notifications', {
    //         headers: {
    //             'X-CSRF-TOKEN': token.getAttribute('content'),
    //             'Content-Type': 'application/json'
    //         },
    //         credentials: 'same-origin'
    //     })
    //     .then(response => response.json())
    //     .then(response => {
    //         const count = document.getElementById('notificationCount');
    //         if (count) {
    //             const unreadCount = response.count || (response.data ? response.data.filter(n => !n.is_read).length : 0);
    //             if (unreadCount > 0) {
    //                 count.innerText = unreadCount > 99 ? '99+' : unreadCount;
    //                 count.classList.remove('d-none');
    //             } else {
    //                 count.classList.add('d-none');
    //             }
    //         }
    //     })
    //     .catch(error => {
    //         console.error('Error updating count:', error);
    //     });
    // }

    // function formatDate(dateString) {
    //     const date = new Date(dateString);
    //     const now = new Date();
    //     const diffMs = now - date;
    //     const diffMins = Math.floor(diffMs / 60000);
    //     const diffHours = Math.floor(diffMs / 3600000);
    //     const diffDays = Math.floor(diffMs / 86400000);

    //     if (diffMins < 1) return 'Just now';
    //     if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    //     if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    //     if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    //     return date.toLocaleDateString('en-IN', {
    //         day: 'numeric',
    //         month: 'short',
    //         year: 'numeric'
    //     });
    // }

// ==================== SEARCH FUNCTIONALITY ====================
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('customerSearch');
        const resultBox = document.getElementById('searchResults');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (query.length < 1) {
                    resultBox.style.display = 'none';
                    return;
                }

                fetch(`/search-users?query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        resultBox.innerHTML = '';
                        let hasResults = false;

                        // ✅ Customers section
                        if (['admin', 'sales-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.users?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light">Customers</div>`;
                            data.users.forEach(user => {
                                const profileUrl = `/customer-view/${user.id}`;
                                resultBox.innerHTML += `
                                    <a href="${profileUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${user.profile_image}" alt="User Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(user.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">${escapeHtml(user.email) ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Vendors section
                        if (['admin', 'purchase-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.vendors?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Vendors</div>`;
                            data.vendors.forEach(vendor => {
                                const vendorUrl = `/vendor-view/${vendor.id}`;
                                resultBox.innerHTML += `
                                    <a href="${vendorUrl}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${vendor.profile_image}" alt="Vendor Image" class="rounded-circle me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(vendor.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">${escapeHtml(vendor.email) ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Products section
                        if (['admin', 'purchase-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.products?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Products</div>`;
                            data.products.forEach(product => {
                                resultBox.innerHTML += `
                                    <a href="/product-view/${product.id}" class="list-group-item list-group-item-action d-flex align-items-center">
                                        <img src="${product.image}" alt="Product Image" class="rounded me-2" style="width:35px; height:35px; object-fit: cover;">
                                        <div>
                                            <strong>${escapeHtml(product.name) ?? 'N/A'}</strong><br>
                                            <small class="text-muted">Price: ₹${product.price ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        // ✅ Orders section
                        if (['admin', 'sales-manager', 'inventory-manager', 'staff'].includes(currentUserRole) && data.orders?.length > 0) {
                            hasResults = true;
                            resultBox.innerHTML += `<div class="list-group-item fw-bold bg-light mt-2">Orders</div>`;
                            data.orders.forEach(order => {
                                resultBox.innerHTML += `
                                    <a href="/sales-details/${order.id}" class="list-group-item list-group-item-action">
                                        <div class="d-flex align-items-center mb-1">
                                            <img src="{{ env('ImagePath') . '/admin/assets/img/icons/cart.svg' }}" width="20" height="20" class="me-2" alt="cart">
                                            <strong>Order #: ${order.order_number ?? 'N/A'}</strong>
                                        </div>
                                        <div>
                                            <small>Customer: ${escapeHtml(order.user_name) ?? 'N/A'}</small><br>
                                            <small>Total: ₹${order.total_amount ?? 'N/A'} | Status: ${order.payment_status ?? 'N/A'}</small>
                                        </div>
                                    </a>
                                `;
                            });
                        }

                        if (!hasResults) {
                            resultBox.innerHTML = '<div class="list-group-item text-center text-muted">No results found</div>';
                        }

                        resultBox.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        resultBox.innerHTML = '<div class="list-group-item text-center text-danger">Error loading results</div>';
                        resultBox.style.display = 'block';
                    });
            });
        }

        // Hide dropdown on outside click
        document.addEventListener('click', function(e) {
            if (searchInput && resultBox && !searchInput.contains(e.target) && !resultBox.contains(e.target)) {
                resultBox.style.display = 'none';
            }
        });
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // ==================== SIDEBAR TOGGLE FUNCTIONALITY ====================
    $(document).on('mouseover', function(e) {
        const $toggleBtn = $('#toggle_btn1');
        const isTabletSize = $(window).width() >= 768 && $(window).width() <= 1024;
        const isButtonAvailable = $toggleBtn.length > 0 && (isTabletSize || $toggleBtn.is(':visible'));

        if ($('body').hasClass('mini-sidebar') && isButtonAvailable) {
            const isInsideSidebar = $(e.target).closest('.sidebar').length;
            if (isInsideSidebar) {
                $('body').addClass('expand-menu');
                $('.subdrop + ul').slideDown();
            } else {
                $('body').removeClass('expand-menu');
                $('.subdrop + ul').slideUp();
            }
        }
    });

    // Toggle button handler
    $(document).on('click', '#toggle_btn1', function(e) {
        e.preventDefault();
        e.stopPropagation();

        if ($(this).data('processing')) {
            return false;
        }
        $(this).data('processing', true);

        const body = $('body');
        const $btn = $(this);

        if (body.hasClass('mini-sidebar')) {
            body.removeClass('mini-sidebar');
            $btn.addClass('active');
            $('.subdrop + ul').slideDown();
            localStorage.setItem('screenModeNightTokenState', 'night');
            setTimeout(function() {
                body.removeClass('mini-sidebar');
                $('.header-left').addClass('active');
            }, 100);
        } else {
            body.addClass('mini-sidebar');
            $btn.removeClass('active');
            $('.subdrop + ul').slideUp();
            localStorage.removeItem('screenModeNightTokenState');
            setTimeout(function() {
                body.addClass('mini-sidebar');
                $('.header-left').removeClass('active');
            }, 100);
        }

        setTimeout(() => {
            $btn.data('processing', false);
        }, 300);

        return false;
    });

    // ==================== BRANCH DROPDOWN FUNCTIONALITY ====================
    (function initializeBranchDropdown() {
        const container = document.getElementById('subBranchContainer');
        const select = document.getElementById('subBrandSelect');
        if (container) {
            container.style.display = 'block';
        }
        if (select) {
            select.innerHTML = "";
            const mainOption = document.createElement('option');
            mainOption.value = "";
            mainOption.textContent = 'Main Branch';
            select.appendChild(mainOption);
            if (window.$ && $.fn && $.fn.select2) {
                $('#subBrandSelect').select2({
                    placeholder: 'Select a branch',
                    allowClear: true
                });
            }
        }
    })();

    fetch('/api/get_subadmin', {
        headers: {
            "Authorization": "Bearer " + localStorage.getItem("authToken"),
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    })
    .then(res => res.json())
    .then(response => {
        if (!response) {
            console.warn('No response from /api/get_subadmin');
            return;
        }

        const select = document.getElementById("subBrandSelect");
        if (!select) return;

        select.innerHTML = "";
        document.getElementById('subBranchContainer').style.display = 'block';

        const mainOption = document.createElement("option");
        mainOption.value = "";
        mainOption.textContent = "Main Branch";
        select.appendChild(mainOption);

        (response.data || []).forEach(function(item) {
            const option = document.createElement("option");
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });

        if (window.$ && $.fn && $.fn.select2) {
            $('#subBrandSelect').select2({
                placeholder: 'Select a branch',
                allowClear: true
            });
        }

        const savedId = localStorage.getItem('selectedSubAdminId');
        if (savedId) {
            const exists = (response.data || []).some(function(item) {
                return String(item.id) === String(savedId);
            });
            if (exists) {
                $('#subBrandSelect').val(savedId).trigger('change.select2');
            } else {
                $.post('/clear-subadmin-session', {
                    _token: $('meta[name="csrf-token"]').attr('content')
                }, function() {
                    localStorage.removeItem('selectedSubAdminId');
                });
            }
        }
    })
    .catch(function(error) {
        console.error('Failed to load sub-admin list:', error);
    });

    $('#subBrandSelect').on('change', function() {
        const selectedId = $(this).val();
        const selectedText = $(this).find('option:selected').text() || '';
        $('#currentSelection').text(selectedId ? ('Selected: ' + selectedText) : '');

        if (selectedId) {
            localStorage.setItem('selectedSubAdminId', selectedId);
            $.post('/set-subadmin-session', {
                _token: $('meta[name="csrf-token"]').attr('content'),
                subAdminId: selectedId
            }, function() {
                window.location.href = "{{ route('auth.dashboard') }}";
            });
        } else if (selectedText === "Main Branch") {
            $.post('/clear-subadmin-session', {
                _token: $('meta[name="csrf-token"]').attr('content')
            }, function() {
                localStorage.removeItem('selectedSubAdminId');
                window.location.href = '/dashboard';
            });
        }
    });


    // ==================== NOTIFICATION FUNCTIONALITY ====================
let notificationRefreshInterval = null;

document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();

    // Auto-refresh every 30 seconds
    if (notificationRefreshInterval) {
        clearInterval(notificationRefreshInterval);
    }
    notificationRefreshInterval = setInterval(() => {
        const menu = document.getElementById('notificationMenu');
        if (menu && menu.style.display === 'block') {
            loadNotifications();
        }
    }, 30000);
});

function initializeNotifications() {
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationMenu = document.getElementById('notificationMenu');

    if (notificationToggle) {
        // Remove any existing event listeners
        const newToggle = notificationToggle.cloneNode(true);
        notificationToggle.parentNode.replaceChild(newToggle, notificationToggle);

        newToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if (notificationMenu.style.display === 'block') {
                notificationMenu.style.display = 'none';
            } else {
                loadNotifications();
                notificationMenu.style.display = 'block';
            }
        });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (notificationToggle && notificationMenu) {
            if (!notificationToggle.contains(e.target) && !notificationMenu.contains(e.target)) {
                notificationMenu.style.display = 'none';
            }
        }
    });

    // Initial load
    loadNotifications();
}

function loadNotifications() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        console.error('CSRF token not found');
        return;
    }

    // Get selected subadmin ID from localStorage if exists
    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(response => {
        const list = document.getElementById('notificationList');
        const count = document.getElementById('notificationCount');
        const markAllBtn = document.getElementById('markAllReadBtn');

        if (!list) return;

        list.innerHTML = '';

        if (!response.status || !response.data || response.data.length === 0) {
            list.innerHTML = `
                <div class="empty-notification">
                    <i class="fa fa-bell-slash"></i>
                    No notifications
                </div>
            `;
            if (count) count.classList.add('d-none');
            if (markAllBtn) markAllBtn.style.display = 'none';
            return;
        }

        // Update count badge
        if (count) {
            const unreadCount = response.count || response.data.filter(n => !n.is_read).length;
            if (unreadCount > 0) {
                count.innerText = unreadCount > 99 ? '99+' : unreadCount;
                count.classList.remove('d-none');
            } else {
                count.classList.add('d-none');
            }
        }

        // Show mark all button if there are unread notifications
        if (markAllBtn) {
            const hasUnread = response.data.some(n => !n.is_read);
            markAllBtn.style.display = hasUnread ? 'block' : 'none';
        }

        // Render notifications
        response.data.forEach(item => {
            const notificationItem = document.createElement('div');
            notificationItem.className = `notification-item ${!item.is_read ? 'unread-notification' : ''}`;
            notificationItem.setAttribute('data-id', item.id);
            notificationItem.onclick = function(e) {
                // Prevent click if clicking on action buttons
                if (e.target.closest('.notification-action')) {
                    return;
                }
                handleNotificationClick(item.id, item.link);
            };

            notificationItem.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <strong class="notification-title">${escapeHtml(item.title)}</strong>
                        <span class="notification-message">${escapeHtml(item.message)}</span>
                        <small class="notification-time">
                            <i class="fa fa-clock-o"></i>
                            ${formatDate(item.created_at)}
                        </small>
                    </div>
                    ${!item.is_read ? '<span class="notification-dot"></span>' : ''}
                </div>
            `;

            list.appendChild(notificationItem);
        });
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        const list = document.getElementById('notificationList');
        if (list) {
            list.innerHTML = `
                <div class="empty-notification text-danger">
                    <i class="fa fa-exclamation-circle"></i>
                    Failed to load notifications
                </div>
            `;
        }
    });
}

function handleNotificationClick(id, link) {
    // Mark as read
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(() => {
        // Update the UI
        const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
        if (notificationItem) {
            notificationItem.classList.remove('unread-notification');
            const dot = notificationItem.querySelector('.notification-dot');
            if (dot) dot.remove();
        }

        // Update count
        updateNotificationCount();

        // Redirect if link exists
        if (link && link !== '#') {
            window.location.href = link;
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
        // Still redirect even if marking fails
        if (link && link !== '#') {
            window.location.href = link;
        }
    });
}

function markAllNotificationsAsRead() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications/mark-all-read?selectedSubAdminId=' + selectedSubAdminId, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(() => {
        // Reload notifications to update UI
        loadNotifications();
    })
    .catch(error => {
        console.error('Error marking all as read:', error);
    });
}

function updateNotificationCount() {
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) return;

    const selectedSubAdminId = localStorage.getItem('selectedSubAdminId') || '';

    fetch('/notifications?selectedSubAdminId=' + selectedSubAdminId, {
        headers: {
            'X-CSRF-TOKEN': token.getAttribute('content'),
            'Content-Type': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(response => {
        const count = document.getElementById('notificationCount');
        if (count) {
            const unreadCount = response.count || 0;
            if (unreadCount > 0) {
                count.innerText = unreadCount > 99 ? '99+' : unreadCount;
                count.classList.remove('d-none');
            } else {
                count.classList.add('d-none');
            }
        }
    })
    .catch(error => {
        console.error('Error updating count:', error);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    if (diffHours < 24) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
    if (diffDays < 7) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;

    return date.toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<style>
    /* Additional styles for search results */
    .list-group-item-action {
        transition: all 0.2s ease;
    }

    .list-group-item-action:hover {
        background-color: #fef9f0 !important;
        transform: translateX(2px);
    }

    /* Notification animation */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .notification-dropdown {
        animation: slideDown 0.2s ease;
    }

    /* Scrollbar styling */
    .notification-body::-webkit-scrollbar {
        width: 5px;
    }

    .notification-body::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .notification-body::-webkit-scrollbar-thumb {
        background: #ff9f43;
        border-radius: 5px;
    }

    .notification-body::-webkit-scrollbar-thumb:hover {
        background: #ff8c2e;
    }

    /* Search input focus */
    #customerSearch:focus {
        border-color: #ff9f43;
        box-shadow: 0 0 0 0.2rem rgba(255, 159, 67, 0.25);
    }
</style>
@endpush
