@extends('admin.layouts.app')

@section('title', 'Settings')

@section('content')
<div class="settings-container">
    <div class="settings-header mb-5">
        <h1 class="h2">
            <i class="fas fa-cog"></i> Account Settings
        </h1>
        <p class="text-muted">Manage your account preferences and security</p>
    </div>

    <!-- Settings Layout -->
    <div class="row">
        <!-- Sidebar Navigation -->
        <div class="col-md-3 mb-4">
            <div class="settings-nav" style="position: sticky; top: 20px;">
                <div class="nav flex-column nav-tabs" role="tablist">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-settings" type="button" role="tab">
                        <i class="fas fa-user"></i> Profile
                    </button>
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-settings" type="button" role="tab">
                        <i class="fas fa-lock"></i> Password
                    </button>
                    <button class="nav-link" id="photo-tab" data-bs-toggle="tab" data-bs-target="#photo-settings" type="button" role="tab">
                        <i class="fas fa-image"></i> Photo
                    </button>
                    <button class="nav-link" id="footer-tab" data-bs-toggle="tab" data-bs-target="#footer-settings" type="button" role="tab">
                        <i class="fas fa-globe"></i> Footer
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="col-md-9">
            <div class="tab-content">
                <!-- Profile Settings Tab -->
                <div class="tab-pane fade show active" id="profile-settings" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-circle"></i> Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> Please fix the errors below:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.settings.profile') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user"></i> Full Name
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        id="name" 
                                        name="name" 
                                        value="{{ auth()->user()->name }}" 
                                        required
                                    >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope"></i> Email Address
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control @error('email') is-invalid @enderror" 
                                        id="email" 
                                        name="email" 
                                        value="{{ auth()->user()->email }}" 
                                        required
                                    >
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Settings Tab -->
                <div class="tab-pane fade" id="password-settings" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-lock"></i> Change Password
                            </h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> Please fix the errors below:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.settings.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">
                                        <i class="fas fa-key"></i> Current Password
                                    </label>
                                    <div class="password-input-container">
                                        <input 
                                            type="password" 
                                            class="form-control @error('current_password') is-invalid @enderror" 
                                            id="current_password" 
                                            name="current_password" 
                                            required
                                            onblur="autoHidePassword('current_password')"
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock"></i> New Password
                                    </label>
                                    <div class="password-input-container">
                                        <input 
                                            type="password" 
                                            class="form-control @error('password') is-invalid @enderror" 
                                            id="password" 
                                            name="password" 
                                            minlength="8" 
                                            required
                                            onblur="autoHidePassword('password')"
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                    @error('password')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="fas fa-check-double"></i> Confirm Password
                                    </label>
                                    <div class="password-input-container">
                                        <input 
                                            type="password" 
                                            class="form-control" 
                                            id="password_confirmation" 
                                            name="password_confirmation" 
                                            minlength="8" 
                                            required
                                            onblur="autoHidePassword('password_confirmation')"
                                        >
                                        <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-redo"></i> Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Photo Settings Tab -->
                <div class="tab-pane fade" id="photo-settings" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-image"></i> Profile Photo
                            </h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> Please fix the errors below:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="text-center mb-4">
                                <div class="profile-photo-preview mb-3">
                                    @if(auth()->user()->profile_photo)
                                        <img 
                                            src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}" 
                                            alt="Profile Photo" 
                                            class="img-fluid rounded-circle"
                                            style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #e9ecef;"
                                        >
                                    @else
                                        <div 
                                            style="width: 150px; height: 150px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid #e9ecef;"
                                        >
                                            <i class="fas fa-user" style="font-size: 3rem; color: #999;"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <form action="{{ route('admin.settings.photo') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="profile_photo" class="form-label">
                                        <i class="fas fa-upload"></i> Upload New Photo
                                    </label>
                                    <input 
                                        type="file" 
                                        class="form-control @error('profile_photo') is-invalid @enderror" 
                                        id="profile_photo" 
                                        name="profile_photo" 
                                        accept="image/*"
                                        onchange="previewPhoto(this)"
                                        required
                                    >
                                    <small class="form-text text-muted">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                                    @error('profile_photo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="photoPreview" class="mb-3" style="display: none;">
                                    <label class="form-label">Preview</label>
                                    <div class="text-center">
                                        <img id="previewImage" src="" alt="Photo Preview" class="img-fluid rounded" style="max-width: 300px; max-height: 300px;">
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Upload Photo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Footer Settings Tab -->
                <div class="tab-pane fade" id="footer-settings" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-globe"></i> Footer Settings
                            </h5>
                        </div>
                        <div class="card-body">
                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fas fa-exclamation-circle"></i> Please fix the errors below:
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('admin.settings.footer') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <h6 class="mb-3 text-primary"><i class="fas fa-address-card"></i> Contact Information</h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_address" class="form-label">
                                                <i class="fas fa-map-marker-alt"></i> Address
                                            </label>
                                            <textarea
                                                class="form-control @error('footer_address') is-invalid @enderror"
                                                id="footer_address"
                                                name="footer_address"
                                                rows="3"
                                                required
                                            >{{ old('footer_address', $settings['footer_address']) }}</textarea>
                                            @error('footer_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_phone" class="form-label">
                                                <i class="fas fa-phone"></i> Phone Number
                                            </label>
                                            <input
                                                type="text"
                                                class="form-control @error('footer_phone') is-invalid @enderror"
                                                id="footer_phone"
                                                name="footer_phone"
                                                value="{{ old('footer_phone', $settings['footer_phone']) }}"
                                                required
                                            >
                                            @error('footer_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label for="footer_email" class="form-label">
                                                <i class="fas fa-envelope"></i> Email Address
                                            </label>
                                            <input
                                                type="email"
                                                class="form-control @error('footer_email') is-invalid @enderror"
                                                id="footer_email"
                                                name="footer_email"
                                                value="{{ old('footer_email', $settings['footer_email']) }}"
                                                required
                                            >
                                            @error('footer_email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3 text-primary"><i class="fas fa-share-alt"></i> Social Media Links</h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_facebook" class="form-label">
                                                <i class="fab fa-facebook-f"></i> Facebook URL
                                            </label>
                                            <input
                                                type="url"
                                                class="form-control @error('footer_facebook') is-invalid @enderror"
                                                id="footer_facebook"
                                                name="footer_facebook"
                                                value="{{ old('footer_facebook', $settings['footer_facebook']) }}"
                                                placeholder="https://facebook.com/yourpage"
                                            >
                                            @error('footer_facebook')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Leave empty to hide Facebook link</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="footer_twitter" class="form-label">
                                                <i class="fab fa-twitter"></i> Twitter URL
                                            </label>
                                            <input
                                                type="url"
                                                class="form-control @error('footer_twitter') is-invalid @enderror"
                                                id="footer_twitter"
                                                name="footer_twitter"
                                                value="{{ old('footer_twitter', $settings['footer_twitter']) }}"
                                                placeholder="https://twitter.com/yourhandle"
                                            >
                                            @error('footer_twitter')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Leave empty to hide Twitter link</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="footer_linkedin" class="form-label">
                                                <i class="fab fa-linkedin-in"></i> LinkedIn URL
                                            </label>
                                            <input
                                                type="url"
                                                class="form-control @error('footer_linkedin') is-invalid @enderror"
                                                id="footer_linkedin"
                                                name="footer_linkedin"
                                                value="{{ old('footer_linkedin', $settings['footer_linkedin']) }}"
                                                placeholder="https://linkedin.com/company/yourcompany"
                                            >
                                            @error('footer_linkedin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Leave empty to hide LinkedIn link</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="footer_instagram" class="form-label">
                                                <i class="fab fa-instagram"></i> Instagram URL
                                            </label>
                                            <input
                                                type="url"
                                                class="form-control @error('footer_instagram') is-invalid @enderror"
                                                id="footer_instagram"
                                                name="footer_instagram"
                                                value="{{ old('footer_instagram', $settings['footer_instagram']) }}"
                                                placeholder="https://instagram.com/youraccount"
                                            >
                                            @error('footer_instagram')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Leave empty to hide Instagram link</small>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <h6 class="mb-3 text-primary"><i class="fas fa-file-contract"></i> Legal Pages</h6>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="privacy_policy" class="form-label">
                                                <i class="fas fa-shield-alt"></i> Privacy Policy
                                            </label>
                                            <textarea
                                                class="form-control @error('privacy_policy') is-invalid @enderror"
                                                id="privacy_policy"
                                                name="privacy_policy"
                                                rows="6"
                                                placeholder="Enter your privacy policy content here..."
                                            >{{ old('privacy_policy', $settings['privacy_policy']) }}</textarea>
                                            @error('privacy_policy')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">HTML content allowed for formatting</small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="terms_of_service" class="form-label">
                                                <i class="fas fa-file-signature"></i> Terms of Service
                                            </label>
                                            <textarea
                                                class="form-control @error('terms_of_service') is-invalid @enderror"
                                                id="terms_of_service"
                                                name="terms_of_service"
                                                rows="6"
                                                placeholder="Enter your terms of service content here..."
                                            >{{ old('terms_of_service', $settings['terms_of_service']) }}</textarea>
                                            @error('terms_of_service')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">HTML content allowed for formatting</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Footer Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

<style>
.settings-container {
    background: #f8f9fa;
    padding: 30px 0;
}

.settings-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 20px;
}

.settings-nav .nav {
    border-right: 1px solid #e9ecef;
    padding-right: 15px;
}

.settings-nav .nav-link {
    color: #6c757d;
    border-left: 3px solid transparent;
    padding-left: 12px;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.settings-nav .nav-link:hover {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
    border-left-color: #0d6efd;
}

.settings-nav .nav-link.active {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
    border-left-color: #0d6efd;
    font-weight: 600;
}

.card {
    margin-bottom: 30px;
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}

.card-title {
    font-weight: 600;
    color: #2c3e50;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.form-control {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

@media (max-width: 768px) {
    .settings-nav .nav {
        border-right: none;
        border-bottom: 1px solid #e9ecef;
        padding-right: 0;
        padding-bottom: 15px;
        display: flex;
        gap: 10px;
        overflow-x: auto;
    }

    .settings-nav .nav-link {
        border-left: none;
        border-bottom: 3px solid transparent;
        padding-left: 0;
        padding-bottom: 10px;
        white-space: nowrap;
    }

    .settings-nav .nav-link.active {
        border-left: none;
        border-bottom-color: #0d6efd;
    }
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.3s ease;
    font-size: 1rem;
}

.password-toggle:hover {
    color: #495057;
}
</style>

<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('photoPreview').style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function autoHidePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.nextElementSibling;
    const icon = button.querySelector('i');
    
    // Only hide if it's currently visible
    if (input.type === 'text') {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Update dropdown profile photo after successful upload
document.addEventListener('DOMContentLoaded', function() {
    const photoForm = document.querySelector('form[action="{{ route('admin.settings.photo') }}"]');
    
    if (photoForm) {
        photoForm.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('profile_photo');
            if (!fileInput.files || !fileInput.files[0]) {
                return;
            }

            // Show a temporary message
            const previewImage = document.getElementById('previewImage');
            if (previewImage && previewImage.src) {
                // Store the preview temporarily to update dropdown
                const tempPhotoUrl = previewImage.src;
                
                // After form submission, update dropdown after a short delay
                setTimeout(function() {
                    const dropdownPhoto = document.getElementById('dropdownProfilePhoto');
                    if (dropdownPhoto && tempPhotoUrl) {
                        // Add cache buster to force refresh
                        dropdownPhoto.src = tempPhotoUrl + '?t=' + new Date().getTime();
                    }
                }, 500);
            }
        });
    }

    // Also update on page load if photo was changed (refresh from server)
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(function() {
            location.reload();
        }, 2000);
    }
});
</script>
@endsection
