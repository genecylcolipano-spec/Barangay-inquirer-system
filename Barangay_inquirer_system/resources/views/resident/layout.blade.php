<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Resident Dashboard - Barangay Inquirer System</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('residentsdashstyle/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('residentsdashstyle/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('residentsdashstyle/components.css') }}">

    <style>
        .top-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            border-bottom: 1px solid #e9ecef;
            z-index: 1050;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 20px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            color: #6c757d;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .sidebar-toggle:hover {
            background-color: #f8f9fa;
        }

        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-bell, .user-menu {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            transition: background-color 0.2s;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(0, 0, 0, 0.15);
        }

        .notification-bell:hover, .user-menu:hover {
            background-color: #f8f9fa;
            color: #495057;
        }

        .notification-badge {
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }

        .notification-dropdown {
            min-width: 350px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item-dropdown {
            padding: 12px 16px;
            border-bottom: 1px solid #f8f9fa;
        }

        .notification-item-dropdown:last-child {
            border-bottom: none;
        }

        .notification-item-dropdown.unread {
            background-color: #f8f9fa;
        }

        .notification-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 4px;
            color: #2c3e50;
        }

        .notification-message {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .notification-time {
            font-size: 11px;
            color: #adb5bd;
        }

        .main-content {
            margin-top: 60px !important;
        }

        /* Badge pulse animation for new notifications */
        .badge-pulse {
            animation: pulse 0.5s ease-in-out 3;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
        }

        @media (max-width: 768px) {
            .page-title {
                display: none;
            }

            .notification-dropdown {
                min-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Top Header with Notifications -->
        <header class="top-header">
            <div class="header-content">
                <div class="header-left">
                    <button class="sidebar-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title">Barangay Inquirer System</h1>
                </div>
                <div class="header-right">
                    <!-- Notification Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            @php
                                $unreadCount = auth()->user()->unreadNotifications()->count();
                            @endphp
                            <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill" id="notificationBadgeContainer" style="{{ $unreadCount > 0 ? '' : 'display: none;' }}">
                                <span id="notificationBadgeCount">{{ $unreadCount }}</span>
                            </span>
                        </button>
                        <ul class="dropdown-menu notification-dropdown" aria-labelledby="notificationDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notificationList">
                                <a class="dropdown-item text-center text-muted" href="#">Loading...</a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="{{ route('resident.notifications') }}">View All Notifications</a></li>
                        </ul>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="btn btn-link user-menu" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            @if(auth()->user()->profile_photo)
                                <img src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}?t={{ time() }}" alt="Profile" class="user-avatar">
                            @else
                                <i class="fas fa-user-circle"></i>
                            @endif
                            <span>{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="{{ route('resident.profile') }}"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('resident.settings') }}"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </header>

        @include('resident.sidebar')

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.querySelector('.main-content');

            if (window.innerWidth <= 768) {
                // Mobile: toggle overlay
                sidebar.classList.toggle('active');
            } else {
                // Desktop: toggle collapsed/expanded
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const toggleBtn = document.querySelector('.sidebar-toggle');

            if (window.innerWidth <= 768 && sidebar.classList.contains('active') && !sidebar.contains(event.target) && !toggleBtn.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Load notifications when dropdown is shown
        document.getElementById('notificationDropdown').addEventListener('show.bs.dropdown', function () {
            loadNotifications();
        });

        // Poll for notifications every 15 seconds (auto-refresh)
        setInterval(loadNotifications, 15000);

        // Function to load recent notifications
        function loadNotifications() {
            fetch('{{ route("resident.notifications.recent") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch notifications: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const notificationList = document.getElementById('notificationList');
                
                // Only update if data is an array
                if (!Array.isArray(data)) {
                    console.error('Invalid notification data:', data);
                    notificationList.innerHTML = '<a class="dropdown-item text-center text-muted" href="#">Invalid data received</a>';
                    return;
                }

                if (data.length === 0) {
                    notificationList.innerHTML = '<a class="dropdown-item text-center text-muted" href="#">No notifications</a>';
                } else {
                    let html = '';
                    data.forEach(notification => {
                        const unreadClass = notification.read ? '' : 'unread';
                        html += `
                            <a class="dropdown-item notification-item-dropdown ${unreadClass}" href="${notification.url}" onclick="markAsRead('${notification.id}')">
                                <div class="notification-title">${notification.title}</div>
                                <div class="notification-message">${notification.message}</div>
                                <small class="notification-time">${notification.time}</small>
                            </a>
                        `;
                    });
                    notificationList.innerHTML = html;
                }
            })
            .catch(error => {
                console.error('Error loading notifications:', error);
                document.getElementById('notificationList').innerHTML = '<a class="dropdown-item text-center text-muted" href="#">Error loading notifications</a>';
            });
        }

        // Function to mark notification as read
        function markAsRead(notificationId) {
            console.log('Marking notification as read:', notificationId);
            fetch(`/resident/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to mark notification as read: ' + response.status);
                }
                return response.json();
            })
            .then(() => {
                console.log('Notification marked as read');
                updateUnreadCount();
                loadNotifications();
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }

        // Function to update unread notification count
        function updateUnreadCount() {
            fetch('{{ route("resident.notifications.check-unread") }}', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch unread count: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                const badge = document.getElementById('notificationBadgeCount');
                const badgeContainer = document.getElementById('notificationBadgeContainer');

                if (!data || typeof data.unread_count === 'undefined') {
                    console.error('Invalid unread count data:', data);
                    return;
                }

                const currentCount = parseInt(badge.textContent) || 0;
                const newCount = data.unread_count;

                // Update badge visibility and count
                if (newCount > 0) {
                    badge.textContent = newCount;
                    badgeContainer.style.display = '';

                    // Animate badge if count increased
                    if (newCount > currentCount && currentCount > 0) {
                        console.log('New notifications received: ' + (newCount - currentCount));
                        badgeContainer.classList.add('badge-pulse');
                        setTimeout(() => {
                            badgeContainer.classList.remove('badge-pulse');
                        }, 2000);
                    }
                } else {
                    badgeContainer.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error updating unread count:', error);
            });
        }

        // Update unread count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateUnreadCount();

            // Poll for new notifications every 10 seconds
            setInterval(function() {
                updateUnreadCount();
                console.log('Checking for new notifications...');
            }, 10000);

            // Initial notification load
            loadNotifications();
        });
    </script>
</body>
</html>
