@extends('superadmin.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-user-circle text-primary"></i> My Profile</h1>
            <p class="text-muted">Manage your super admin account settings</p>
        </div>
    </div>
</div>

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

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <div style="margin-bottom: 15px;">
                    @if(auth()->user()->profile_photo)
                        <img 
                            src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}" 
                            alt="Profile Photo" 
                            class="img-fluid rounded-circle"
                            style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e9ecef;"
                        >
                    @else
                        <div 
                            style="width: 120px; height: 120px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid #e9ecef; font-size: 3rem; color: #007bff;"
                        >
                            <i class="fas fa-user-circle"></i>
                        </div>
                    @endif
                </div>
                <h5 class="card-title fw-bold">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <span class="badge bg-danger mb-3">Super Administrator</span>
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#photoModal">
                        <i class="fas fa-camera"></i> Change Photo
                    </button>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Account Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Status</span>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Member Since</span>
                    <span>{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Last Login</span>
                    <span>{{ $user->updated_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-4">
        <!-- Edit Profile -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Edit Profile Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.profile.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('superadmin.profile.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Current Password</label>
                        <div class="password-input-container">
                            <input type="password" class="form-control" id="current_password" name="current_password" onblur="autoHidePassword('current_password')">
                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-bold">New Password</label>
                        <div class="password-input-container">
                            <input type="password" class="form-control" id="password" name="password" onblur="autoHidePassword('password')">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">Confirm New Password</label>
                        <div class="password-input-container">
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" onblur="autoHidePassword('password_confirmation')">
                            <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Profile Photo Modal -->
<div class="modal fade" id="photoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-image"></i> Change Profile Photo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if ($errors->has('profile_photo'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i> {{ $errors->first('profile_photo') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('superadmin.profile.photo') }}" method="POST" enctype="multipart/form-data" id="photoForm">
                    @csrf
                    @method('PUT')

                    <div class="text-center mb-4">
                        <div id="photoPreview">
                            @if(auth()->user()->profile_photo)
                                <img 
                                    id="previewImage"
                                    src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}" 
                                    alt="Profile Photo" 
                                    class="img-fluid rounded"
                                    style="max-width: 100%; max-height: 300px; object-fit: cover;"
                                >
                            @else
                                <div 
                                    id="previewImage"
                                    style="width: 200px; height: 200px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-size: 3rem; color: #999;"
                                >
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profile_photo" class="form-label fw-bold">
                            <i class="fas fa-upload"></i> Select Photo
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
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="photoForm" class="btn btn-primary">
                    <i class="fas fa-save"></i> Upload Photo
                </button>
            </div>
        </div>
    </div>
</div>

<style>
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
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = '<img id="previewImage" src="' + e.target.result + '" alt="Photo Preview" class="img-fluid rounded" style="max-width: 100%; max-height: 300px; object-fit: cover;">';
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
</script>

@endsection
