@extends('superadmin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-cog text-primary"></i> System Settings</h1>
            <p class="text-muted">Configure system-wide settings and preferences</p>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Settings Navigation Tabs -->
<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button">
            <i class="fas fa-cogs"></i> General Settings
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="maintenance-tab" data-bs-toggle="tab" data-bs-target="#maintenance" type="button">
            <i class="fas fa-tools"></i> Maintenance Mode
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer" type="button">
            <i class="fas fa-globe"></i> Footer Settings
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">
            <i class="fas fa-lock"></i> Security
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button">
            <i class="fas fa-envelope"></i> Email Settings
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" id="backup-tab" data-bs-toggle="tab" data-bs-target="#backup" type="button">
            <i class="fas fa-database"></i> Backup
        </button>
    </li>
</ul>

<!-- Settings Content -->
<div class="tab-content">
    <!-- General Settings -->
    <div class="tab-pane fade show active" id="general">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">General System Settings</h6>
            </div>
            <div class="card-body">
                @if(auth()->user()->role === 'super_admin')
                    <form action="{{ route('superadmin.settings.general') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="siteName" class="form-label fw-bold">Site Name</label>
                            <input type="text" class="form-control @error('site_name') is-invalid @enderror" id="siteName" name="site_name" value="{{ $settings['site_name'] ?? 'Barangay Inquirer System' }}" required>
                            @error('site_name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="siteLogo" class="form-label fw-bold">Site Logo</label>
                            @if($settings['site_logo'] ?? null)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/settings/' . $settings['site_logo']) }}" alt="Current Logo" style="max-height: 100px;" class="img-thumbnail">
                                </div>
                            @endif
                            <input type="file" class="form-control @error('site_logo') is-invalid @enderror" id="siteLogo" name="site_logo" accept="image/*">
                            <small class="text-muted">PNG, JPG, GIF (Max 2MB)</small>
                            @error('site_logo')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                @else
                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-lock me-2"></i> <strong>Access Restricted</strong><br>
                        Only Super Administrators can modify site name and logo settings.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Maintenance Mode -->
    <div class="tab-pane fade" id="maintenance">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Maintenance Mode</h6>
            </div>
            <div class="card-body">
                <p class="text-muted">Enable or disable maintenance mode for the application.</p>
                <form action="{{ route('superadmin.settings.maintenance') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <input type="checkbox" name="enable_maintenance_mode" value="1" {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}> Enable Maintenance Mode
                        </label>
                        <small class="text-muted d-block">When enabled, regular users will see a maintenance message. Super administrators can still access the site.</small>
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-tools"></i> Toggle Maintenance Mode
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer Settings -->
    <div class="tab-pane fade" id="footer">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Footer Settings</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.settings.footer') }}" method="POST">
                    @csrf

                    <h6 class="mb-3 text-primary"><i class="fas fa-address-card"></i> Contact Information</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer_address" class="form-label fw-bold">Address</label>
                                <textarea
                                    class="form-control @error('footer_address') is-invalid @enderror"
                                    id="footer_address"
                                    name="footer_address"
                                    rows="3"
                                    required
                                >{{ old('footer_address', $settings['footer_address']) }}</textarea>
                                @error('footer_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer_phone" class="form-label fw-bold">Phone Number</label>
                                <input
                                    type="text"
                                    class="form-control @error('footer_phone') is-invalid @enderror"
                                    id="footer_phone"
                                    name="footer_phone"
                                    value="{{ old('footer_phone', $settings['footer_phone']) }}"
                                    required
                                >
                                @error('footer_phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="footer_email" class="form-label fw-bold">Email Address</label>
                                <input
                                    type="email"
                                    class="form-control @error('footer_email') is-invalid @enderror"
                                    id="footer_email"
                                    name="footer_email"
                                    value="{{ old('footer_email', $settings['footer_email']) }}"
                                    required
                                >
                                @error('footer_email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3 text-primary"><i class="fas fa-share-alt"></i> Social Media Links</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer_facebook" class="form-label fw-bold">Facebook URL</label>
                                <input
                                    type="url"
                                    class="form-control @error('footer_facebook') is-invalid @enderror"
                                    id="footer_facebook"
                                    name="footer_facebook"
                                    value="{{ old('footer_facebook', $settings['footer_facebook']) }}"
                                    placeholder="https://facebook.com/yourpage"
                                >
                                @error('footer_facebook')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">Leave empty to hide Facebook link</small>
                            </div>

                            <div class="mb-3">
                                <label for="footer_twitter" class="form-label fw-bold">Twitter URL</label>
                                <input
                                    type="url"
                                    class="form-control @error('footer_twitter') is-invalid @enderror"
                                    id="footer_twitter"
                                    name="footer_twitter"
                                    value="{{ old('footer_twitter', $settings['footer_twitter']) }}"
                                    placeholder="https://twitter.com/yourhandle"
                                >
                                @error('footer_twitter')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">Leave empty to hide Twitter link</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="footer_linkedin" class="form-label fw-bold">LinkedIn URL</label>
                                <input
                                    type="url"
                                    class="form-control @error('footer_linkedin') is-invalid @enderror"
                                    id="footer_linkedin"
                                    name="footer_linkedin"
                                    value="{{ old('footer_linkedin', $settings['footer_linkedin']) }}"
                                    placeholder="https://linkedin.com/company/yourcompany"
                                >
                                @error('footer_linkedin')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">Leave empty to hide LinkedIn link</small>
                            </div>

                            <div class="mb-3">
                                <label for="footer_instagram" class="form-label fw-bold">Instagram URL</label>
                                <input
                                    type="url"
                                    class="form-control @error('footer_instagram') is-invalid @enderror"
                                    id="footer_instagram"
                                    name="footer_instagram"
                                    value="{{ old('footer_instagram', $settings['footer_instagram']) }}"
                                    placeholder="https://instagram.com/youraccount"
                                >
                                @error('footer_instagram')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">Leave empty to hide Instagram link</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h6 class="mb-3 text-primary"><i class="fas fa-file-contract"></i> Legal Pages</h6>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="privacy_policy" class="form-label fw-bold">Privacy Policy</label>
                                <textarea
                                    class="form-control @error('privacy_policy') is-invalid @enderror"
                                    id="privacy_policy"
                                    name="privacy_policy"
                                    rows="6"
                                    placeholder="Enter your privacy policy content here..."
                                >{{ old('privacy_policy', $settings['privacy_policy']) }}</textarea>
                                @error('privacy_policy')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">HTML content allowed for formatting</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="terms_of_service" class="form-label fw-bold">Terms of Service</label>
                                <textarea
                                    class="form-control @error('terms_of_service') is-invalid @enderror"
                                    id="terms_of_service"
                                    name="terms_of_service"
                                    rows="6"
                                    placeholder="Enter your terms of service content here..."
                                >{{ old('terms_of_service', $settings['terms_of_service']) }}</textarea>
                                @error('terms_of_service')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                <small class="text-muted">HTML content allowed for formatting</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Footer Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="tab-pane fade" id="security">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Security Settings</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.settings.security') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="sessionTimeout" class="form-label fw-bold">Session Timeout (minutes)</label>
                        <input type="number" class="form-control @error('session_timeout') is-invalid @enderror" id="sessionTimeout" name="session_timeout" value="{{ $settings['session_timeout'] ?? 60 }}" min="5" max="1440">
                        @error('session_timeout')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <input type="checkbox" name="require_2fa" value="1" {{ ($settings['require_2fa'] ?? true) ? 'checked' : '' }}> Require 2FA for Admins
                        </label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <input type="checkbox" name="enable_ip_whitelist" value="1" {{ ($settings['enable_ip_whitelist'] ?? true) ? 'checked' : '' }}> Enable IP Whitelist
                        </label>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Last security audit: 2 days ago
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Email Settings -->
    <div class="tab-pane fade" id="email">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Email Configuration</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.settings.email') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="emailDriver" class="form-label fw-bold">Email Driver</label>
                        <select class="form-select @error('email_driver') is-invalid @enderror" id="emailDriver" name="email_driver">
                            <option value="smtp" {{ ($settings['email_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="mailgun" {{ ($settings['email_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                            <option value="sendgrid" {{ ($settings['email_driver'] ?? '') == 'sendgrid' ? 'selected' : '' }}>SendGrid</option>
                        </select>
                        @error('email_driver')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="emailFrom" class="form-label fw-bold">From Email Address</label>
                        <input type="email" class="form-control @error('email_from') is-invalid @enderror" id="emailFrom" name="email_from" value="{{ $settings['email_from'] ?? 'noreply@barangayinquirer.com' }}" required>
                        @error('email_from')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> SMTP connection verified
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Backup Settings -->
    <div class="tab-pane fade" id="backup">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Database Backup</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Last Backup:</strong> {{ $settings['last_backup'] ? $settings['last_backup'] : 'Never' }}
                    <br>
                    <strong>Last Restore:</strong> {{ $settings['last_restore'] ? $settings['last_restore'] : 'Never' }}
                </div>

                <form action="{{ route('superadmin.settings.backup') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Automatic Backups</label>
                            <select class="form-select" name="backup_frequency">
                                <option value="daily" {{ ($settings['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ ($settings['backup_frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ ($settings['backup_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Backup Location</label>
                        <p class="form-control-plaintext">storage/backups/</p>
                    </div>

                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save"></i> Save Backup Settings
                    </button>
                </form>

                <form action="{{ route('superadmin.settings.backup.manual') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="fas fa-download"></i> Create Manual Backup
                    </button>
                </form>

                <a href="{{ route('superadmin.settings.backup.history') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-history"></i> View Backup History
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
