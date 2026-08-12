@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('homepagestyle/home.css') }}">
@endpush

@section('navbar')
    @include('partials.navbar')
@endsection

@section('content')
<!-- ═════════════════════════════ HERO SECTION ═════════════════════════════ -->
<section class="hero-section" id="home">
    <div class="hero-background">
        <div class="hero-pattern"></div>
        <div class="hero-image-overlay">
            <img src="{{ asset('illustration/illustration.png') }}" alt="Barangay Services Illustration" class="hero-bg-image">
        </div>
    </div>

    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-badge">
                ✨ {{ __('messages.hero_badge') }}
            </div>
            <h1 class="hero-title">{{ __('messages.hero_title') }}</h1>
            <p class="hero-subtitle">{{ __('messages.hero_subtitle') }}</p>

            <div class="hero-buttons">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary-cta">
                        <i class="fas fa-arrow-right"></i> {{ __('messages.go_to_dashboard') }}
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn btn-primary-cta">
                        <i class="fas fa-user-plus"></i> {{ __('messages.create_account') }}
                    </a>
                    <a href="{{ route('login') }}" class="btn btn-secondary-outline">
                        <i class="fas fa-play-circle"></i> {{ __('messages.login') }}
                    </a>
                @endauth
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-image-wrapper">
                <img src="{{ asset('illustration/illustration.png') }}" alt="Barangay Services Illustration" class="hero-main-image">
                <div class="hero-image-glow"></div>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════ ABOUT SECTION ═════════════════════════════ -->
<section class="services-section" id="about" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);">
    <div class="services-container">
        <div class="section-header">
            <h2 class="section-title">{{ __('messages.about_title') }}</h2>
            <p class="section-subtitle">{{ __('messages.about_subtitle') }}</p>
        </div>

        <div class="about-features-grid">
            <div class="feature-item">
                <div class="feature-icon feature-icon-shield">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>{{ __('messages.secure_reliable') }}</h3>
                <p>{{ __('messages.secure_description') }}</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon feature-icon-bolt">
                    <i class="fas fa-bolt"></i>
                </div>
                <h3>{{ __('messages.lightning_fast') }}</h3>
                <p>{{ __('messages.fast_description') }}</p>
            </div>

            <div class="feature-item">
                <div class="feature-icon feature-icon-users">
                    <i class="fas fa-users"></i>
                </div>
                <h3>{{ __('messages.community_focused') }}</h3>
                <p>{{ __('messages.community_description') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- ═════════════════════════════ SERVICES SECTION ═════════════════════════════ -->
<section class="services-section" id="services">
    <div class="services-container">
        <div class="section-header">
            <h2 class="section-title">{{ __('messages.powerful_features') }}</h2>
            <p class="section-subtitle">{{ __('messages.features_subtitle') }}</p>
        </div>

        <div class="services-grid">
            <a href="{{ auth()->check() ? route('resident.requests') : route('login') }}" class="service-card">
                <div class="icon orange"><i class="fas fa-file-lines"></i></div>
                <h3>{{ __('messages.easy_request_management') }}</h3>
                <p>{{ __('messages.request_description') }}</p>
            </a>

            <a href="{{ route('announcements.index') }}" class="service-card">
                <div class="icon blue"><i class="fas fa-bell"></i></div>
                <h3>{{ __('messages.instant_announcements') }}</h3>
                <p>{{ __('messages.announcements_description') }}</p>
            </a>

            <a href="#about" class="service-card">
                <div class="icon purple"><i class="fas fa-users"></i></div>
                <h3>{{ __('messages.community_directory') }}</h3>
                <p>{{ __('messages.directory_description') }}</p>
            </a>

            <a href="{{ auth()->check() ? route('resident.dashboard') : route('login') }}" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(16, 185, 129, 0.05)); color: #10b981;"><i class="fas fa-chart-line"></i></div>
                <h3>{{ __('messages.smart_analytics') }}</h3>
                <p>{{ __('messages.analytics_description') }}</p>
            </a>
        </div>
    </div>
</section>

<!-- ═════════════════════════════ ANNOUNCEMENTS SECTION ═════════════════════════════ -->
<section class="services-section" id="announcements" style="background: linear-gradient(135deg, #fff8f1 0%, #fffaf5 40%, #fef3c7 100%);">
    <div class="services-container">
        <div class="section-header">
            <h2 class="section-title">{{ __('messages.latest_announcements') }}</h2>
            <p class="section-subtitle">{{ __('messages.announcements_subtitle') }}</p>
        </div>

        <div class="announcements-grid">
            @php
                $homepageAnnouncements = \App\Models\Announcement::getHomepageAnnouncements();
            @endphp

            @forelse($homepageAnnouncements as $announcement)
            <div class="announcement-card announcement-{{ $announcement->tag }}">
                <div class="announcement-meta">
                    <span class="announcement-badge {{ $announcement->getTagBadgeClass() }}">
                        {{ $announcement->getTagDisplayText() }}
                    </span>
                    <span class="announcement-date">{{ $announcement->getDisplayDate()->format('M d, Y') }}</span>
                </div>
                <h3>{{ $announcement->title }}</h3>
                <p>{{ $announcement->excerpt ?: Str::limit(strip_tags($announcement->content), 120) }}</p>
                <a href="{{ route('announcements.index') }}" class="announcement-link">
                    Read more <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @empty
            <!-- Fallback hardcoded announcements if none are configured -->
            <div class="announcement-card announcement-today">
                <div class="announcement-meta">
                    <span class="announcement-badge">TODAY</span>
                    <span class="announcement-date">February 15, 2026</span>
                </div>
                <h3>System Maintenance Notice</h3>
                <p>The barangay inquirer system will undergo scheduled maintenance this weekend. We apologize for any inconvenience.</p>
                <a href="{{ route('announcements.index') }}" class="announcement-link">Read more <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="announcement-card announcement-featured">
                <div class="announcement-meta">
                    <span class="announcement-badge">FEATURED</span>
                    <span class="announcement-date">February 10, 2026</span>
                </div>
                <h3>New Document Request Feature</h3>
                <p>We've launched an enhanced document request system with faster processing and real-time tracking capabilities.</p>
                <a href="{{ route('announcements.index') }}" class="announcement-link">Read more <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="announcement-card announcement-success">
                <div class="announcement-meta">
                    <span class="announcement-badge">SUCCESS</span>
                    <span class="announcement-date">February 5, 2026</span>
                </div>
                <h3>Community Program Launch</h3>
                <p>New community development program starts this month with supporting documents now available on the system.</p>
                <a href="{{ route('announcements.index') }}" class="announcement-link">Read more <i class="fas fa-arrow-right"></i></a>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- ═════════════════════════════ CONTACT FORM SECTION ═════════════════════════════ -->
<section class="services-section" id="contact" style="background: linear-gradient(135deg, #f0f9ff 0%, #f8fafc 100%);">
    <div class="services-container">
        <div class="section-header">
            <h2 class="section-title">{{ __('messages.get_in_touch') }}</h2>
            <p class="section-subtitle">{{ __('messages.contact_subtitle') }}</p>
        </div>

        <div class="contact-form-container">
            <form id="contactForm" method="POST" action="{{ route('contact.submit') }}" class="contact-form">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name *</label>
                        <input type="text" id="name" name="name" required class="form-input"
                            placeholder="Your full name">
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" id="email" name="email" required class="form-input"
                            placeholder="your.email@example.com">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input"
                            placeholder="+63 (XXX) XXX-XXXX">
                    </div>

                    <div class="form-group">
                        <label for="subject" class="form-label">Subject *</label>
                        <input type="text" id="subject" name="subject" required class="form-input"
                            placeholder="What is this regarding?">
                    </div>
                </div>

                <!-- Message Field -->
                <div class="form-group">
                    <label for="message" class="form-label">Message *</label>
                    <textarea id="message" name="message" rows="6" required class="form-textarea"
                        placeholder="Please describe your inquiry or concern in detail..."></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary contact-submit-btn">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>

                <p class="form-required-note">
                    <span class="required-asterisk">*</span> Required fields
                </p>
            </form>

            @if($errors->any())
                <div class="form-message form-error">
                    <strong>Please fix the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="form-message form-success">
                    <strong><i class="fas fa-check-circle"></i> Success!</strong> Your message has been sent. We'll get back to you soon.
                </div>
            @endif
        </div>
    </div>
</section>


<!-- Back to Top Button -->
<button id="backToTop" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- ═════════════════════════════ FOOTER ═════════════════════════════ -->
<footer class="main-footer">
    <div class="footer-wrapper">
        <div class="footer-container">
            <!-- Footer Column 1: About -->
            <div class="footer-column">
                <div class="footer-logo">
                    <i class="fas fa-landmark"></i> <span class="footer-brand">Barangay Inquirer System</span>
                </div>
                <p class="footer-description">
                    Modernizing barangay office operations with intelligent document management and community engagement solutions.
                    Trusted by multiple barangay offices.
                </p>
                <div class="footer-social">
                    @if(\App\Models\Setting::get('footer_facebook'))
                        <a href="{{ \App\Models\Setting::get('footer_facebook') }}" class="social-link" title="Facebook" target="_blank">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                    @endif
                    @if(\App\Models\Setting::get('footer_twitter'))
                        <a href="{{ \App\Models\Setting::get('footer_twitter') }}" class="social-link" title="Twitter" target="_blank">
                            <i class="fab fa-twitter"></i>
                        </a>
                    @endif
                    @if(\App\Models\Setting::get('footer_linkedin'))
                        <a href="{{ \App\Models\Setting::get('footer_linkedin') }}" class="social-link" title="LinkedIn" target="_blank">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    @endif
                    @if(\App\Models\Setting::get('footer_instagram'))
                        <a href="{{ \App\Models\Setting::get('footer_instagram') }}" class="social-link" title="Instagram" target="_blank">
                            <i class="fab fa-instagram"></i>
                        </a>
                    @endif
                    <a href="mailto:{{ \App\Models\Setting::get('footer_email', 'info@barangay.gov.ph') }}" class="social-link" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                </div>
            </div>

            <!-- Footer Column 2: Quick Links -->
            <div class="footer-column">
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="/"><i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i> <span>Home</span></a></li>
                    <li><a href="/#services"><i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i> <span>Services</span></a></li>
                    <li><a href="{{ route('login') }}"><i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i> <span>Login</span></a></li>
                    <li><a href="{{ route('register') }}"><i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i> <span>Register</span></a></li>
                </ul>
            </div>

            <!-- Footer Column 3: Contact -->
            <div class="footer-column">
                <h4>Get In Touch</h4>
                <div class="footer-contact">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>{{ \App\Models\Setting::get('footer_address', 'Barangay Hall, Your City, Your Province') }}</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <p>{{ \App\Models\Setting::get('footer_phone', '+63 (XXX) XXX-XXXX') }}</p>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <p>{{ \App\Models\Setting::get('footer_email', 'info@barangay.gov.ph') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
        <p>&copy; 2026 Barangay Inquirer System. All rights reserved.</p>
        <div class="footer-bottom-links">
            @if(\App\Models\Setting::get('privacy_policy'))
                <a href="{{ route('privacy-policy') }}" style="color: #9ca3af; text-decoration: none; transition: var(--transition);"
                   onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#9ca3af'">
                    Privacy Policy
                </a>
            @endif
            @if(\App\Models\Setting::get('terms_of_service'))
                <a href="{{ route('terms-of-service') }}" style="color: #9ca3af; text-decoration: none; transition: var(--transition);"
                   onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='#9ca3af'">
                    Terms of Service
                </a>
            @endif
        </div>
    </div>
</footer>


@endsection

@push('scripts')
<script src="{{ asset('homepagestyle/navbar.js') }}"></script>
<script src="{{ asset('homepagestyle/scroll-animate.js') }}"></script>
<script>
    // Smooth scroll navigation
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            var href = this.getAttribute('href');
            if (!href || href === '#') return;
            if (href.startsWith('#')) {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
    });

    // Back to top button
    const backToTopBtn = document.getElementById('backToTop');
    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });

    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // Add scroll animation to elements
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.service-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
        observer.observe(card);
    });
</script>
@endpush
