<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Barangay Inquirer System')</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta name="description" content="@yield('meta_description', 'Official Barangay Online Services System for document requests, announcements, and resident records.')">
    <meta name="keywords" content="Barangay Services, Barangay Clearance, LGU System, Barangay Online, Philippines">
    <meta name="author" content="Barangay Local Government Unit">
    <meta name="robots" content="index, follow">

    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:title" content="@yield('title', 'Barangay Inquirer System')">
    <meta property="og:description" content="@yield('meta_description', 'Official Barangay Online Services System')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/og-image.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Barangay Inquirer System')">
    <meta name="twitter:description" content="@yield('meta_description', 'Official Barangay Online Services System')">
    <meta name="twitter:image" content="{{ asset('images/og-image.png') }}">

    <meta name="application-name" content="Barangay Inquirer System">
    <meta name="theme-color" content="#0d3b66">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link rel="stylesheet" href="{{ asset('process/documents.css') }}">
    <link rel="stylesheet" href="{{ asset('process/purok.css') }}">
    <link rel="stylesheet" href="{{ asset('authstyle/login.css') }}">
    <link rel="stylesheet" href="{{ asset('authstyle/register.css') }}">

    @stack('styles')

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/js/app.js'])
    @endif
</head>

<body>

    @yield('navbar')

    <div class="main-content">
        @yield('content')
    </div>

    <script>
        $(document).ready(function() {
            // Target links inside the mobile navbar
            $('.main-navbar .nav-link').on('click', function() {
                var $menu = $('.navbar-collapse');
                
                // Only animate if the menu is currently open (mobile view)
                if ($menu.hasClass('show')) {
                    // Add the custom fade-out class from your CSS
                    $menu.addClass('menu-closing');

                    // Wait for the CSS transition (400ms) before hiding via Bootstrap
                    setTimeout(function() {
                        $menu.collapse('hide');
                        $menu.removeClass('menu-closing');
                    }, 400);
                }
            });
        });
    </script>

    @stack('scripts')

</body>
</html>