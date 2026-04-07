@extends('layout.app')

@section('title', 'All Notifications')

@section('content')
    <style>
        /* ── Notification Row ── */
        .notification-list {
            overflow-y: auto;
            max-height: 600px;
        }

        .notification-item-wrapper {
            border-bottom: 1px solid #eaeaea;
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease;
        }

        .notification-item-wrapper:last-child {
            border-bottom: none;
        }

        .notification-item {
            padding: 15px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: #fef9f0;
        }

        .notification-item.unread {
            background-color: #fff9f0;
            border-left: 3px solid #ff9f43;
        }

        .notification-item.read {
            background-color: #fff;
            opacity: 0.9;
        }

        .notification-item.read:hover {
            opacity: 1;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .notification-item.unread .notification-icon {
            background-color: #fff0e0;
        }

        /* ── Pagination ── */
        .pagination-controls {
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }

        @media (min-width: 768px) {
            .pagination-controls {
                flex-direction: row;
            }
        }

        .pagination .page-item .page-link {
            background-color: #5d6d7e;
            color: #fff;
            border: none;
            margin: 0 4px;
            padding: 6px 15px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        .pagination .page-item.active .page-link {
            background-color: #ff9f43 !important;
            color: #fff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #4a5766;
            color: #fff;
        }

        .pagination .page-item.active .page-link:hover {
            background-color: #e68a35 !important;
        }

        .pagination .page-item.disabled .page-link {
            background-color: #adb5bd;
            cursor: not-allowed;
            opacity: 0.6;
        }

        /* ── Branch Filter Section ── */
        .branch-filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .branch-filter-section .form-group {
            margin-bottom: 0;
        }

        /* ── Animations ── */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ── Scrollbar ── */
        .notification-list::-webkit-scrollbar {
            width: 5px;
        }

        .notification-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .notification-list::-webkit-scrollbar-thumb {
            background: #ff9f43;
            border-radius: 5px;
        }

        /* ── Loading spinner ── */
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #ff9f43;
            border-radius: 50%;
            animation: spin 0.5s linear infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ── Toast ── */
        #toastContainer {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
        }

        .toast-notif {
            padding: 11px 18px;
            border-radius: 8px;
            margin-bottom: 8px;
            color: #fff;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            animation: slideRight 0.3s ease;
        }

        @keyframes slideRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* ── Loading overlay ── */
        #loadingOverlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        /* ── Empty state ── */
        .empty-state i {
            font-size: 64px;
            color: #ddd;
        }

        .empty-state h5 {
            font-size: 18px;
            color: #6c757d;
            margin-top: 15px;
        }

        /* ── Type Filter Dropdown ── */
        .type-filter {
            max-width: 200px;
        }

        .filter-badge {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .filter-badge:hover {
            transform: translateY(-2px);
        }

        .filter-badge.active {
            background-color: #ff9f43 !important;
            color: white !important;
        }
    </style>

    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4><i class="fa fa-bell me-2"></i>All Notifications</h4>
                <h6>View and manage all your notifications</h6>
            </div>
            <div class="page-btn">
                <button class="btn btn-danger" id="deleteAllBtn">
                    <i class="fa fa-trash me-1"></i> Delete All
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-body">



                <!-- Header row -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-0">Notifications</h5>
                        <small class="text-muted" id="totalLabel">Loading...</small>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" id="markAllReadBtn">
                        <i class="fa fa-check-circle me-1"></i> Mark all as read
                    </button>
                </div>

                <!-- Notification List -->
                <div class="notification-list" id="notificationContainer">
                    <div class="text-center py-5">
                        <div class="spinner-border text-warning" role="status"></div>
                        <p class="mt-2 text-muted">Loading notifications...</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="pagination-controls d-flex flex-column flex-md-row justify-content-between align-items-center mt-3"
                    id="paginationControls" style="display:none;">
                    <div class="d-flex align-items-center mb-3 mb-md-0">
                        <span class="me-2" style="font-size: 14px; color: #555;">Show per page :</span>
                        <select id="per-page-select" class="form-select form-select-sm"
                            style="width: auto; border: 1px solid #ddd;">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="ms-3" style="font-size: 14px; color: #555;">
                            <span id="pagination-from">0</span> - <span id="pagination-to">0</span> of <span
                                id="pagination-total">0</span> items
                        </span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0" id="pagination-numbers"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay"
        style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.4); z-index:9999; justify-content:center; align-items:center;">
        <div class="spinner-border text-light" role="status"></div>
    </div>

    <!-- Toast container -->
    <div id="toastContainer"></div>

@endsection

@push('js')
<script>
    $(document).ready(function() {

        /* ─── Config ─── */
        var authToken = localStorage.getItem('authToken');
        var currentPage = 1;
        var lastPage = 1;
        var perPage = 10;
        var selectedBranchId = localStorage.getItem('selectedSubAdminId') || ''; // Get from localStorage
        var selectedType = '';
        var userRole = @json(Auth::user()->role ?? 'staff');

        /* ════════════════════════════════════
           1.  LOAD NOTIFICATIONS
        ════════════════════════════════════ */
        function loadNotifications(page) {
            page = page || 1;

            var requestData = {
                page: page,
                per_page: perPage
            };

            // Add branch filter for admin (using selectedSubAdminId from localStorage)
            if (userRole === 'admin' && selectedBranchId) {
                requestData.selectedSubAdminId = selectedBranchId;
            }

            // Add type filter
            if (selectedType) {
                requestData.type = selectedType;
            }

            $.ajax({
                url: '/api/notifications',
                type: 'GET',
                dataType: 'json',
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'Accept': 'application/json'
                },
                data: requestData,

                success: function(res) {

                    if (res.status && res.data && res.data.length > 0) {
                        renderNotifications(res.data);

                        var total = res.pagination ? res.pagination.total : res.data.length;
                        var unread = res.count !== undefined ? res.count :
                            res.data.filter(function(n) { return !n.is_read; }).length;
                        $('#totalLabel').text('Total: ' + total + ' notifications  |  ' + unread + ' unread');

                        if (res.pagination) {
                            currentPage = res.pagination.current_page;
                            lastPage = res.pagination.last_page;
                            updatePaginationUI(res.pagination);
                            $('#paginationControls').show();
                        } else {
                            $('#pagination-from').text(1);
                            $('#pagination-to').text(res.data.length);
                            $('#pagination-total').text(res.data.length);
                            $('#pagination-numbers').html('');
                            $('#paginationControls').show();
                        }
                        updateUnreadButtonVisibility();
                    } else {
                        showEmpty();
                        $('#totalLabel').text('Total: 0 notifications');
                        $('#paginationControls').hide();
                    }
                },

                error: function(xhr) {
                    var msg = 'Failed to load notifications.';
                    if (xhr.status === 401) msg = 'Session expired. Please login again.';
                    if (xhr.status === 404) msg = 'API endpoint not found (/api/notifications).';
                    showError(msg);
                }
            });
        }

        /* ════════════════════════════════════
           2.  RENDER ROWS (same as before)
        ════════════════════════════════════ */
        function renderNotifications(list) {
            var html = '';

            $.each(list, function(i, n) {
                var isUnread = !n.is_read;
                var hasLink = n.link && n.link !== '#';
                var dateLabel = n.formatted_date || formatDate(n.created_at);
                var timeLabel = n.formatted_time || formatTime(n.created_at);

                var typeBadgeClass = 'info';
                if (n.type === 'customer') typeBadgeClass = 'warning';
                if (n.type === 'order') typeBadgeClass = 'success';
                if (n.type === 'login') typeBadgeClass = 'secondary';

                html +=
                    '<div class="notification-item-wrapper" data-id="' + n.id + '" data-link="' + (hasLink ? n.link : '') + '">' +
                    '<div class="notification-item ' + (isUnread ? 'unread' : 'read') + '">' +
                    '<div class="row align-items-center">' +
                    '<div class="col-md-7">' +
                    '<div class="d-flex align-items-start">' +
                    '<div class="notification-icon me-3">' + getIcon(n.type) + '</div>' +
                    '<div class="flex-grow-1">' +
                    '<h6 class="mb-1">' + safe(n.title) + '</h6>' +
                    '<p class="mb-1 text-muted" style="font-size:13px;">' + safe(n.message) + '</p>' +
                    '<small class="text-muted"><i class="fa fa-clock-o me-1"></i>' + safe(dateLabel) +
                    ', ' + safe(timeLabel) + '</small>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                    '<span class="badge bg-' + typeBadgeClass + '">' + safe(n.type ? n.type.charAt(0).toUpperCase() + n.type.slice(1) : 'General') + '</span>' +
                    (isUnread ? ' <span class="badge bg-danger ms-1">New</span>' : '') +
                    '</div>' +
                    '<div class="col-md-2 text-end">' +
                    '<div class="btn-group" role="group">' +
                    (hasLink ?
                        '<button class="btn btn-sm btn-outline-primary btn-view-notif" data-id="' + n.id + '" data-link="' + safe(n.link) + '"><i class="fa fa-eye"></i></button>' :
                        '') +
                    '<button class="btn btn-sm btn-outline-danger btn-del-notif" data-id="' + n.id + '"><i class="fa fa-trash"></i></button>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>';
            });

            $('#notificationContainer').html(html);
        }

        /* ════════════════════════════════════
           3.  MARK SINGLE AS READ
        ════════════════════════════════════ */
        $(document).on('click', '.btn-view-notif', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            var redirectUrl = $(this).data('link');
            markReadAndRedirect(id, redirectUrl);
        });

        $(document).on('click', '.notification-item', function(e) {
            if ($(e.target).closest('.btn-group').length) return;
            var id = $(this).closest('.notification-item-wrapper').data('id');
            var link = $(this).closest('.notification-item-wrapper').data('link');
            markReadAndRedirect(id, link);
        });

        function markReadAndRedirect(id, redirectUrl) {
            var requestData = {};
            if (userRole === 'admin' && selectedBranchId) {
                requestData.selectedSubAdminId = selectedBranchId;
            }

            $.ajax({
                url: '/api/notifications/' + id + '/read',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + authToken,
                    'Accept': 'application/json'
                },
                data: JSON.stringify(requestData),
                contentType: 'application/json',
                success: function(res) {
                    if (res.status) {
                        var wrap = $('.notification-item-wrapper[data-id="' + id + '"]');
                        wrap.find('.notification-item').removeClass('unread').addClass('read');
                        wrap.find('.badge.bg-danger').remove();
                        refreshUnreadCount();

                        if (redirectUrl && redirectUrl !== '#') {
                            setTimeout(function() {
                                window.location.href = redirectUrl;
                            }, 300);
                        }
                    }
                },
                error: function() {
                    if (redirectUrl && redirectUrl !== '#') {
                        setTimeout(function() {
                            window.location.href = redirectUrl;
                        }, 300);
                    }
                }
            });
        }

        /* ════════════════════════════════════
           4.  DELETE SINGLE
        ════════════════════════════════════ */
        $(document).on('click', '.btn-del-notif', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = $(this).data('id');
            var title = $(this).closest('.notification-item-wrapper').find('h6').text() || 'this notification';

            Swal.fire({
                title: 'Delete Notification?',
                html: 'Delete <strong>' + safe(title) + '</strong>? This cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                var wrap = $('.notification-item-wrapper[data-id="' + id + '"]');
                var wasUnread = wrap.find('.notification-item').hasClass('unread');

                wrap.css({
                    transition: 'all 0.3s ease',
                    opacity: 0,
                    transform: 'translateX(-20px)'
                });

                var requestData = {};
                if (userRole === 'admin' && selectedBranchId) {
                    requestData.selectedSubAdminId = selectedBranchId;
                }

                $.ajax({
                    url: '/api/notifications/' + id + '/delete',
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(requestData),
                    contentType: 'application/json',
                    success: function(res) {
                        if (res.status) {
                            setTimeout(function() {
                                wrap.remove();
                                refreshUnreadCount();
                                if ($('.notification-item-wrapper').length === 0) {
                                    loadNotifications(1);
                                }
                            }, 300);
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message || 'Notification deleted.',
                                confirmButtonColor: '#ff9f43',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message || 'Failed to delete notification.',
                                confirmButtonColor: '#ff9f43'
                            });
                            wrap.css({ opacity: 1, transform: '' });
                        }
                    },
                    error: function(xhr) {
                        wrap.css({ opacity: 1, transform: '' });
                        var errorMsg = 'Delete failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#ff9f43'
                        });
                    }
                });
            });
        });

        /* ════════════════════════════════════
           5.  DELETE ALL
        ════════════════════════════════════ */
        $('#deleteAllBtn').on('click', function() {
            var totalCount = $('.notification-item-wrapper').length;

            if (totalCount === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Notifications',
                    text: 'There are no notifications to delete.',
                    confirmButtonColor: '#ff9f43'
                });
                return;
            }

            Swal.fire({
                title: 'Delete All Notifications?',
                text: 'This will delete ' + totalCount + ' notification(s). This action cannot be undone!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete everything!'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                var btn = $('#deleteAllBtn')
                    .html('<i class="fa fa-spinner fa-spin me-1"></i> Deleting...')
                    .prop('disabled', true);

                var requestData = {};
                if (userRole === 'admin' && selectedBranchId) {
                    requestData.selectedSubAdminId = selectedBranchId;
                }

                $.ajax({
                    url: '/api/notifications/delete-all',
                    method: 'DELETE',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(requestData),
                    contentType: 'application/json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: res.message || 'All notifications deleted.',
                                confirmButtonColor: '#ff9f43',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(function() {
                                loadNotifications(1);
                            }, 800);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message || 'Failed to delete notifications.',
                                confirmButtonColor: '#ff9f43'
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Delete all failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function() {
                        btn.html('<i class="fa fa-trash me-1"></i> Delete All').prop('disabled', false);
                    }
                });
            });
        });

        /* ════════════════════════════════════
           6.  MARK ALL AS READ
        ════════════════════════════════════ */
        $('#markAllReadBtn').on('click', function() {
            var unreadCount = $('.notification-item.unread').length;

            if (unreadCount === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Unread Notifications',
                    text: 'There are no unread notifications to mark as read.',
                    confirmButtonColor: '#ff9f43'
                });
                return;
            }

            Swal.fire({
                title: 'Mark All as Read?',
                text: 'Mark ' + unreadCount + ' notification(s) as read?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ff9f43',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, mark all as read'
            }).then(function(result) {
                if (!result.isConfirmed) return;

                var btn = $('#markAllReadBtn')
                    .html('<span class="loading-spinner"></span> Processing...')
                    .prop('disabled', true);

                var requestData = {};
                if (userRole === 'admin' && selectedBranchId) {
                    requestData.selectedSubAdminId = selectedBranchId;
                }

                $.ajax({
                    url: '/api/notifications/read-all',
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + authToken,
                        'Accept': 'application/json'
                    },
                    data: JSON.stringify(requestData),
                    contentType: 'application/json',
                    success: function(res) {
                        if (res.status) {
                            $('.notification-item').removeClass('unread').addClass('read');
                            $('.badge.bg-danger').remove();
                            refreshUnreadCount();

                            Swal.fire({
                                icon: 'success',
                                title: 'Done!',
                                text: res.message || 'All notifications marked as read.',
                                confirmButtonColor: '#ff9f43',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message || 'Failed to mark all as read.',
                                confirmButtonColor: '#ff9f43'
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMsg = 'Failed to mark all as read';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMsg,
                            confirmButtonColor: '#ff9f43'
                        });
                    },
                    complete: function() {
                        btn.html('<i class="fa fa-check-circle me-1"></i> Mark all as read').prop('disabled', false);
                    }
                });
            });
        });

        /* ════════════════════════════════════
           7.  PAGINATION
        ════════════════════════════════════ */
        function updatePaginationUI(pagination) {
            var from = (pagination.current_page - 1) * pagination.per_page + 1;
            var to = pagination.current_page * pagination.per_page;
            if (to > pagination.total) to = pagination.total;
            if (pagination.total === 0) from = 0;

            $('#pagination-from').text(from);
            $('#pagination-to').text(to);
            $('#pagination-total').text(pagination.total);

            var startPage = Math.max(1, pagination.current_page - 2);
            var endPage = Math.min(pagination.last_page, startPage + 4);
            if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

            var html = '';

            for (var i = startPage; i <= endPage; i++) {
                html += '<li class="page-item ' + (i === pagination.current_page ? 'active' : '') + '">' +
                    '<a class="page-link" href="javascript:void(0);" data-page="' + i + '">' + i + '</a>' +
                    '</li>';
            }

            $('#pagination-numbers').html(html);
            $('#paginationControls').show();
        }

        $(document).on('click', '#pagination-numbers .page-link', function(e) {
            e.preventDefault();
            var page = parseInt($(this).data('page'));
            if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                loadNotifications(page);
            }
        });

        $('#per-page-select').on('change', function() {
            perPage = parseInt($(this).val());
            loadNotifications(1);
        });

        /* Type filter change */
        $('#filterType').on('change', function() {
            selectedType = $(this).val();
            loadNotifications(1);
        });

        /* Reset filters */
        $('#resetFilters').on('click', function() {
            $('#filterType').val('');
            selectedType = '';
            loadNotifications(1);
        });

        /* ════════════════════════════════════
           8.  HELPERS
        ════════════════════════════════════ */
        function getIcon(type) {
            var icons = {
                customer: '<i class="fa fa-user-circle" style="font-size:22px; color:#ff9f43;"></i>',
                order: '<i class="fa fa-shopping-cart" style="font-size:22px; color:#28a745;"></i>',
                product: '<i class="fa fa-box" style="font-size:22px; color:#17a2b8;"></i>',
                login: '<i class="fa fa-sign-in" style="font-size:22px; color:#6c757d;"></i>',
            };
            return icons[type] || '<i class="fa fa-bell" style="font-size:22px; color:#6c757d;"></i>';
        }

        function showEmpty() {
            $('#notificationContainer').html(
                '<div class="empty-state text-center py-5">' +
                '<i class="fa fa-bell-slash" style="font-size:64px; color:#ddd;"></i>' +
                '<h5 class="mt-3 text-muted">No notifications found</h5>' +
                '<p class="text-muted">There are no notifications to display</p>' +
                '</div>'
            );
        }

        function showError(msg) {
            $('#notificationContainer').html(
                '<div class="empty-state text-center py-5">' +
                '<i class="fa fa-exclamation-circle" style="font-size:64px; color:#dc3545;"></i>' +
                '<h5 class="mt-3 text-muted">' + safe(msg) + '</h5>' +
                '<button class="btn btn-primary mt-3" onclick="location.reload()"><i class="fa fa-refresh"></i> Retry</button>' +
                '</div>'
            );
        }

        function updateUnreadButtonVisibility() {
            var unreadCount = $('.notification-item.unread').length;
            var markAllReadBtn = $('#markAllReadBtn');

            if (unreadCount > 0) {
                markAllReadBtn.show();
                markAllReadBtn.html('<i class="fa fa-check-circle me-1"></i> Mark all as read (' + unreadCount + ')');
            } else {
                markAllReadBtn.hide();
            }
        }

        function refreshUnreadCount() {
            var unread = $('.notification-item.unread').length;
            var total = $('.notification-item-wrapper').length;
            $('#totalLabel').text('Total: ' + total + ' notifications  |  ' + unread + ' unread');
            updateUnreadButtonVisibility();
        }

        function safe(str) {
            if (!str) return '';
            return String(str).replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[m];
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return 'Unknown';
            var d = new Date(dateStr);
            var now = new Date();
            var dDate = d.toISOString().slice(0, 10);
            var todayD = now.toISOString().slice(0, 10);
            var yest = new Date(now);
            yest.setDate(yest.getDate() - 1);
            var yestD = yest.toISOString().slice(0, 10);
            if (dDate === todayD) return 'Today';
            if (dDate === yestD) return 'Yesterday';
            return d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }

        function formatTime(dateStr) {
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        /* ─── Display current branch info ─── */
        function displayCurrentBranch() {
            if (userRole === 'admin' && selectedBranchId) {
                // You can add a branch name display here if needed
                console.log('Showing notifications for branch ID:', selectedBranchId);
            }
        }

        /* ─── Init ─── */
        displayCurrentBranch();
        loadNotifications(1);
    });
</script>
@endpush
