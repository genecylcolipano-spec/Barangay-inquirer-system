<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm main-navbar" id="mainNavbar">
    <div class="container-fluid px-3 px-lg-4">

        @php
            use App\Models\Setting;
            $siteLogo = Setting::get('site_logo');
            $siteName = Setting::get('site_name', 'Barangay Inquirer System');
        @endphp

        <a href="{{ url('') }}" class="navbar-brand d-flex align-items-center">
            @if($siteLogo)
                <img src="{{ asset('storage/settings/' . $siteLogo) }}" 
                     alt="{{ $siteName }}" 
                     class="me-2 img-fluid navbar-logo"
                     style="height: 40px; max-height: 45px; max-width:150px";>
            @else
                <i class="fas fa-landmark me-2 fs-4"></i>
            @endif

            <span> {{ $siteName }}</span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" 
                type="button" 
                data-toggle="collapse" 
                data-target="#navbarNav"
                aria-controls="navbarNav" 
                aria-expanded="false" 
                aria-label="Toggle navigation">
            <i class="fas fa-bars fs-5"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto text-center text-lg-start mt-3 mt-lg-0">
                <li class="nav-item">
                    <a href="#about" class="nav-link px-3">{{ __('messages.about') }}</a>
                </li>
                <li class="nav-item">
                    <a href="#services" class="nav-link px-3">{{ __('messages.services') }}</a>
                </li>
                <li class="nav-item">
                    <a href="#announcements" class="nav-link px-3">{{ __('messages.announcements') }}</a>
                </li>
                <li class="nav-item">
                    <a href="#contact" class="nav-link px-3">{{ __('messages.contact') }}</a>
                </li>
            </ul>

            <div class="d-flex flex-column flex-lg-row align-items-center gap-3 mt-3 mt-lg-0 button-container">
                @auth
                    <a href="{{ url('/dashboard') }}" 
                       class="btn btn-primary btn-dashboard">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('messages.dashboard') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" 
                       class="btn btn-outline-primary btn-login-custom">
                        {{ __('messages.login') }}
                    </a>

                    <a href="{{ route('register') }}" 
                       class="btn btn-primary btn-signup-custom">
                        <i class="fas fa-user-plus me-1"></i>
                        {{ __('messages.register') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const navbar = document.getElementById('mainNavbar');
        const navbarCollapse = document.getElementById('navbarNav');
        const navLinks = document.querySelectorAll('.nav-link');

        // 1. Scroll Effect
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // 2. Close navbar when clicking on nav links (anchor links only)
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                const href = link.getAttribute('href');
                // Only auto-close if it's an internal anchor link
                if (href && href.startsWith('#')) {
                    if (navbarCollapse.classList.contains('show')) {
                        navbarCollapse.classList.add('fade-out-close');
                        setTimeout(() => {
                            // Use Bootstrap 4 collapse API
                            $(navbarCollapse).collapse('hide');
                            navbarCollapse.classList.remove('fade-out-close');
                        }, 300);
                    }
                }
            });
        });

        // 3. Close menu when clicking outside navbar
        document.addEventListener('click', (e) => {
            const isClickInsideNavbar = navbar.contains(e.target);
            if (!isClickInsideNavbar && navbarCollapse.classList.contains('show')) {
                $(navbarCollapse).collapse('hide');
            }
        });
    });
</script>