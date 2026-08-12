<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Admin Dashboard</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    
    <link href="{{ asset('css/admin-dashboard.css') }}" rel="stylesheet">
    
    <style>
        /* Profile Styling Refinements */
        #userDropdown {
            background: none;
            border: none;
            color: #333;
            transition: all 0.3s ease;
            padding: 5px 10px;
            border-radius: 8px;
        }

        #userDropdown:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .dropdown-item {
            transition: all 0.2s ease;
            padding: 10px 20px;
        }

        /* Mobile specific adjustments */
        @media (max-width: 768px) {
            .navbar-user-name {
                display: none; 
            }
            
            .sidebar-close-mobile {
                display: block !important;
                position: absolute;
                right: 15px;
                top: 20px;
                color: white;
                font-size: 1.5rem;
                cursor: pointer;
            }
        }

        .sidebar-close-mobile {
            display: none;
        }

        /* Overlay for Mobile Sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            top: 0;
            left: 0;
            backdrop-filter: blur(2px);
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* Stat Cards Styling */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border-left: 5px solid #ccc;
        }

        .stat-card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .stat-icon {
            font-size: 2.5rem;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background-size: cover;
            background-position: center;
        }

        .stat-content h3.stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 0 0 5px 0;
        }

        .stat-label {
            font-size: 0.95rem;
            font-weight: 500;
            margin: 0;
            white-space: nowrap;
        }

        .stat-change {
            font-size: 0.85rem;
            opacity: 0.7;
        }

        /* Color variants */
        .stat-card-primary { border-left-color: #007bff; }
        .stat-card-primary .stat-icon { background-color: rgba(0, 123, 255, 0.15); color: #007bff; }
        .stat-card-success { border-left-color: #28a745; }
        .stat-card-success .stat-icon { background-color: rgba(40, 167, 69, 0.15); color: #28a745; }
        .stat-card-warning { border-left-color: #ffc107; }
        .stat-card-warning .stat-icon { background-color: rgba(255, 193, 7, 0.15); color: #ffc107; }
        .stat-card-info { border-left-color: #17a2b8; }
        .stat-card-info .stat-icon { background-color: rgba(23, 162, 184, 0.15); color: #17a2b8; }
        .stat-card-danger { border-left-color: #dc3545; }
        .stat-card-danger .stat-icon { background-color: rgba(220, 53, 69, 0.15); color: #dc3545; }

        /* Notification Dropdown Styles - UPDATED */
        .notification-dropdown {
            max-height: 400px;
            overflow-y: auto;
            width: 350px;
            margin-top: 10px !important;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 9999 !important;
        }

        /* Align to right edge to prevent overflow */
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }

        .notification-dropdown .dropdown-item {
            white-space: normal; /* Allow text wrapping */
            border-bottom: 1px solid #f1f1f1;
            padding: 12px 15px;
        }

        .notification-dropdown .dropdown-item.unread {
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
        }

        .notification-dropdown::-webkit-scrollbar { width: 6px; }
        .notification-dropdown::-webkit-scrollbar-track { background: #f1f1f1; }
        .notification-dropdown::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 3px; }

        @media (max-width: 768px) {
            .notification-dropdown {
                position: fixed !important;
                top: 70px !important;
                left: 10px !important;
                right: 10px !important;
                width: auto !important;
                max-height: 70vh;
                transform: none !important;
            }
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="wrapper">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>
                    <i class="fas fa-city"></i>
                    <span>Barangay Admin</span>
                </h3>
                <i class="fas fa-times sidebar-close-mobile" id="mobileClose"></i>
            </div>

            <ul class="list-unstyled components">
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.requests.index') }}" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Document Requests</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.announcements.index') }}" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-info">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="dropdown">
                            <button class="btn position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                                <i class="fas fa-bell"></i>
                                <i class="fas fa-chevron-down ms-1 small" id="notificationChevron" style="font-size: 0.7rem;"></i>
                                @php($unread = Auth::user()->unreadNotifications()->count())
                                @if($unread > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                <li><h6 class="dropdown-header">Notifications</h6></li>
                                @php($recentNotifications = Auth::user()->notifications()->orderByRaw('read_at IS NULL DESC')->latest()->take(5)->get())
                                @forelse($recentNotifications as $notification)
                                <li>
                                    <a class="dropdown-item {{ is_null($notification->read_at) ? 'unread' : '' }}" href="{{ route('admin.notifications.index') }}">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold">{{ $notification->data['title'] ?? 'Notification' }}</div>
                                                <small class="text-muted">{{ $notification->data['message'] ?? '' }}</small>
                                                <div class="small text-muted mt-1">{{ $notification->created_at->diffForHumans() }}</div>
                                            </div>
                                            @if(is_null($notification->read_at))
                                            <div class="ms-2">
                                                <span class="badge bg-primary">New</span>
                                            </div>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                                @empty
                                <li><span class="dropdown-item text-muted">No notifications</span></li>
                                @endforelse
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center text-primary" href="{{ route('admin.notifications.index') }}">View All Notifications</a></li>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <button class="btn dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                @if(Auth::user()->profile_photo)
                                    <img src="{{ asset('storage/uploads/profiles/' . Auth::user()->profile_photo) }}" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #e9ecef;">
                                @else
                                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                                @endif
                                <span class="navbar-user-name">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('admin.settings.index') }}"><i class="fas fa-cog"></i> Settings</a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user"></i> My Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="main-content p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.querySelector('.content'); // Updated to selector
            const overlay = document.getElementById('sidebarOverlay');
            const collapseBtn = document.getElementById('sidebarCollapse');
            const mobileClose = document.getElementById('mobileClose');

            function toggleSidebar() {
                const isMobile = window.innerWidth <= 768;
                sidebar.classList.toggle('active');
                if (isMobile) {
                    overlay.classList.toggle('active');
                    content.classList.remove('active');
                } else {
                    content.classList.toggle('active');
                    overlay.classList.remove('active');
                }
            }

            collapseBtn.addEventListener('click', toggleSidebar);

            [mobileClose, overlay].forEach(element => {
                element.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                });
            });

            // Notification dropdown chevron toggle logic
            const notificationBtn = document.getElementById('notificationDropdown');
            const chevron = document.getElementById('notificationChevron');

            if (notificationBtn && chevron) {
                notificationBtn.addEventListener('show.bs.dropdown', () => {
                    chevron.classList.replace('fa-chevron-down', 'fa-chevron-up');
                });
                notificationBtn.addEventListener('hide.bs.dropdown', () => {
                    chevron.classList.replace('fa-chevron-up', 'fa-chevron-down');
                });
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>