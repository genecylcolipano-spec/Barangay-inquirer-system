<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <span class="logo-icon">🏛️</span>
            <h2>Resident Dashboard</h2>
        </div>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section">
            <p class="menu-label">MAIN</p>
            <a href="{{ route('resident.dashboard') }}" class="nav-link {{ request()->routeIs('resident.dashboard') ? 'active' : '' }}">
                <span class="icon">📊</span>
                <span class="text">Dashboard</span>
            </a>
            <a href="{{ route('resident.documents') }}" class="nav-link {{ request()->routeIs('resident.documents*') ? 'active' : '' }}">
                <span class="icon">📄</span>
                <span class="text">My Documents</span>
            </a>
            <a href="{{ route('resident.requests') }}" class="nav-link {{ request()->routeIs('resident.requests*', 'resident.request.*') ? 'active' : '' }}">
                <span class="icon">📋</span>
                <span class="text">My Requests</span>
            </a>
        </div>

        <div class="menu-section">
            <p class="menu-label">COMMUNITY</p>
            <a href="{{ route('resident.announcements') }}" class="nav-link {{ request()->routeIs('resident.announcements*') ? 'active' : '' }}">
                <span class="icon">📢</span>
                <span class="text">Announcements</span>
            </a>
            <a href="{{ url('/') }}#services" class="nav-link">
                <span class="icon">🛠️</span>
                <span class="text">Services</span>
            </a>
            <a href="{{ url('/') }}#about" class="nav-link">
                <span class="icon">ℹ️</span>
                <span class="text">About</span>
            </a>
        </div>

        <div class="menu-section">
            <p class="menu-label">ACCOUNT</p>
            <a href="{{ route('resident.profile') }}" class="nav-link {{ request()->routeIs('resident.profile*') ? 'active' : '' }}">
                <span class="icon">
                    @if(auth()->user()->profile_photo)
                        <img 
                            src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}?t={{ time() }}" 
                            alt="Profile Photo" 
                            style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover; display: block;"
                        >
                    @else
                        👤
                    @endif
                </span>
                <span class="text">My Profile</span>
            </a>
            <a href="{{ route('resident.settings') }}" class="nav-link {{ request()->routeIs('resident.settings*') ? 'active' : '' }}">
                <span class="icon">⚙️</span>
                <span class="text">Settings</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <form action="{{ route('resident.logout') }}" method="POST" style="width: 100%;">
            @csrf
            <button type="submit" class="logout-btn">
                <span class="icon">🚪</span>
                <span class="text">Logout</span>
            </button>
        </form>
    </div>
</aside>
