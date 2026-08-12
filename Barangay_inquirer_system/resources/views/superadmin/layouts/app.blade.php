<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Super Admin Dashboard</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <link href="{{ asset('css/superadmin-dashboard.css') }}" rel="stylesheet">
    
    <style>
        /* Mobile Overlay Styling */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(2px);
            z-index: 1040;
            display: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* --- Updated Notification Styles --- */
        .notification-dropdown {
            max-height: 400px;
            overflow-y: auto;
            width: 350px;
            margin-top: 10px !important;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            z-index: 9999 !important;
        }

        /* Ensure alignment to the right edge */
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }

        .notification-dropdown .dropdown-item {
            white-space: normal; /* Allow text wrapping */
            border-bottom: 1px solid #f1f1f1;
            padding: 12px 15px;
            transition: background 0.2s;
        }

        .notification-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
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
        <nav class="sidebar">
            <div class="sidebar-header">
                <h3>
                    <i class="fas fa-crown"></i>
                    SuperAdmin
                </h3>
                <p class="sidebar-subtitle">Control Center</p>
            </div>

            <ul class="list-unstyled components">
                <li class="sidebar-section-title">
                    <span>Main</span>
                </li>
                <li class="active">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="sidebar-section-title" style="margin-top: 20px;">
                    <span>Management</span>
                </li>
                <li>
                    <a href="#adminMenu" data-bs-toggle="collapse" class="nav-link collapsed">
                        <i class="fas fa-shield-alt"></i>
                        <span>Admins</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled" id="adminMenu">
                        <li><a href="{{ route('superadmin.admins.index') }}" class="nav-link-sub">View All Admins</a></li>
                        <li><a href="{{ route('superadmin.admins.create') }}" class="nav-link-sub">Add New Admin</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#requestMenu" data-bs-toggle="collapse" class="nav-link collapsed">
                        <i class="fas fa-file-alt"></i>
                        <span>Requests</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled" id="requestMenu">
                        <li><a href="{{ route('superadmin.requests.index') }}" class="nav-link-sub">All Requests</a></li>
                        <li><a href="{{ route('superadmin.requests.index', ['status' => 'pending']) }}" class="nav-link-sub">Pending</a></li>
                        <li><a href="{{ route('superadmin.requests.index', ['status' => 'approved']) }}" class="nav-link-sub">Approved</a></li>
                        <li><a href="{{ route('superadmin.requests.index', ['status' => 'rejected']) }}" class="nav-link-sub">Rejected</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#userMenu" data-bs-toggle="collapse" class="nav-link collapsed">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled" id="userMenu">
                        <li><a href="{{ route('superadmin.users.index') }}" class="nav-link-sub">All Users</a></li>
                        <li><a href="{{ route('superadmin.users.index', ['role' => 'resident']) }}" class="nav-link-sub">Residents</a></li>
                        <li><a href="{{ route('superadmin.users.index', ['role' => 'admin']) }}" class="nav-link-sub">Admins</a></li>
                    </ul>
                </li>

                <li>
                    <a href="#announcementMenu" data-bs-toggle="collapse" class="nav-link collapsed">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                        <i class="fas fa-chevron-down ms-auto"></i>
                    </a>
                    <ul class="collapse list-unstyled" id="announcementMenu">
                        <li><a href="{{ route('superadmin.announcements.index') }}" class="nav-link-sub">All Announcements</a></li>
                        <li><a href="{{ route('superadmin.announcements.create') }}" class="nav-link-sub">Create New</a></li>
                    </ul>
                </li>

                <li class="sidebar-section-title" style="margin-top: 20px;">
                    <span>System</span>
                </li>

                <li>
                    <a href="{{ route('superadmin.activity-logs') }}" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.settings') }}" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('superadmin.system-health') }}" class="nav-link">
                        <i class="fas fa-heartbeat"></i>
                        <span>System Health</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="content">
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-light me-3">
                        <i class="fas fa-bars"></i>
                    </button>
                    
                    <div class="ms-auto d-flex align-items-center gap-3">
                        <div class="navbar-clock me-3">
                            <span id="current-time" class="text-muted fw-500"></span>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                @if(($unreadCount ?? 0) > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">
                                    <span id="notification-count">{{ $unreadCount }}</span>
                                </span>
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                                <li><h6 class="dropdown-header d-flex justify-content-between align-items-center">
                                    Notifications 
                                    <span class="badge bg-danger rounded-pill">{{ $unreadCount ?? 0 }}</span>
                                </h6></li>
                                <li><hr class="dropdown-divider"></li>
                                <div class="notification-list">
                                    @forelse($notifications ?? [] as $note)
                                    <li>
                                        <form action="{{ route('superadmin.notifications.read', $note->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div style="max-width: 80%;">
                                                        <div class="fw-bold small text-dark">{{ $note->data['title'] ?? 'Notification' }}</div>
                                                        <p class="mb-0 small text-muted text-wrap">{{ $note->data['message'] ?? '' }}</p>
                                                    </div>
                                                    <small class="text-muted ms-2" style="font-size: 0.7rem;">{{ $note->created_at->diffForHumans() }}</small>
                                                </div>
                                            </button>
                                        </form>
                                    </li>
                                    @empty
                                    <li><p class="dropdown-item text-muted text-center mb-0 py-3">No new notifications</p></li>
                                    @endforelse
                                </div>
                                <li><hr class="dropdown-divider"></li>
                                @if(($unreadCount ?? 0) > 0)
                                <li>
                                    <form action="{{ route('superadmin.notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-primary text-center small fw-bold">
                                            <i class="fas fa-check-double me-2"></i>Mark all as read
                                        </button>
                                    </form>
                                </li>
                                @endif
                                <li><a class="dropdown-item text-primary text-center small" href="{{ route('superadmin.notifications.index') }}"><i class="fas fa-list me-2"></i>View All Notifications</a></li>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown">
                                <span class="user-avatar" id="profile-dropdown-avatar">
                                    @if(Auth::user()->profile_photo)
                                        <img src="{{ asset('storage/uploads/profiles/' . Auth::user()->profile_photo) }}" alt="Profile" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    @else
                                        <i class="fas fa-user-circle" style="font-size: 28px;"></i>
                                    @endif
                                </span>
                                <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="{{ route('superadmin.profile') }}"><i class="fas fa-user me-2"></i>My Profile</a></li>
                                <li><a class="dropdown-item" href="{{ route('superadmin.settings') }}"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
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
                        <strong><i class="fas fa-exclamation-circle"></i> Error!</strong> 
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
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const sidebar = document.querySelector('.sidebar');
            const content = document.querySelector('.content');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('sidebarCollapse');

            // --- Sidebar Toggle Logic ---
            function toggleSidebar(e) {
                if(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                
                const isMobile = window.innerWidth <= 768;

                if (isMobile) {
                    sidebar.classList.toggle('active');
                    overlay.classList.toggle('active');
                    document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : 'auto';
                } else {
                    sidebar.classList.toggle('collapsed');
                    content.classList.toggle('collapsed');
                }
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', toggleSidebar);
            }

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = 'auto';
            });

            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                    document.body.style.overflow = 'auto';
                } else {
                    sidebar.classList.remove('collapsed');
                    content.classList.remove('collapsed');
                }
            });

            // --- Real-time clock ---
            function updateClock() {
                const now = new Date();
                const clockEl = document.getElementById('current-time');
                if(clockEl) {
                    clockEl.textContent = now.toLocaleTimeString('en-US', { 
                        hour: '2-digit', minute: '2-digit', second: '2-digit'
                    });
                }
            }
            setInterval(updateClock, 1000);
            updateClock();
        });
    </script>
    
    @yield('scripts')
</body>
</html>