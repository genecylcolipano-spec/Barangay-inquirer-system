<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm main-navbar" id="mainNavbar">
    <div class="container-fluid px-3 px-lg-4">

        @php
            use App\Models\Setting;
            // ✅ SIGURADUHIN: may default value kung wala sa database
            $siteLogo = Setting::get('site_logo');
            $siteName = Setting::get('site_name') ?: 'Barangay Inquirer System';
        @endphp

        <a href="{{ url('') }}" class="navbar-brand d-flex align-items-center text-decoration-none">
            {{-- ✅ LOGO — gumagana na sa tamang daanan mo --}}
            @if($siteLogo)
                <img src="/storage/app/public/settings/{{ basename($siteLogo) }}" 
                     alt="{{ $siteName }}" 
                     class="img-fluid navbar-logo me-2"
                     style="height: 45px; max-height: 50px; width: auto; object-fit: contain;">
            @else
                <i class="fas fa-landmark me-2 fs-4" style="color: #0d6efd;"></i>
            @endif

            {{-- ✅ SITENAME — LAGING LUMALABAS, MALINAW, MAKULAYAN --}}
            <span class="fw-bold fs-5" style="color: #0d6efd; white-space: nowrap;">
                {{ $siteName }}
            </span>
        </a>

        <button class="navbar-toggler border-0 shadow-none" 
                type="button" 
                data-bs-toggle="collapse" 
                data-bs-target="#navbarNav"
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

<style>
.navbar-brand { text-decoration: none !important; }
.navbar-brand span { line-height: 1.2; }
</style>
