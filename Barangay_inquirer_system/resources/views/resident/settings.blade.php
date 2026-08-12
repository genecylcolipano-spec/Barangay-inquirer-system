@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>⚙️ Account Settings</h1>
            <p class="date-time">Manage your account preferences and security</p>
        </div>
    </div>

    <!-- Settings Tabs/Sections -->
    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 30px;">
        <!-- Sidebar Navigation -->
        <div style="position: sticky; top: 30px; height: fit-content;">
            <nav style="display: flex; flex-direction: column; gap: 10px;">
                <a href="#profile-settings" class="settings-nav-link active" onclick="switchTab(event, 'profile-settings')">
                    👤 Profile Settings
                </a>
                <a href="#security" class="settings-nav-link" onclick="switchTab(event, 'security')">
                    🔐 Security
                </a>
                <a href="#photo" class="settings-nav-link" onclick="switchTab(event, 'photo')">
                    📸 Profile Photo
                </a>
                <a href="#preferences" class="settings-nav-link" onclick="switchTab(event, 'preferences')">
                    🎨 Preferences
                </a>
            </nav>
        </div>

        <!-- Settings Content -->
        <div>
            <!-- Profile Settings Section -->
            <div id="profile-settings" class="settings-section">
                <div class="card">
                    <div class="card-header">
                        <h2>👤 Profile Information</h2>
                    </div>

                    @if ($errors->any())
                    <div style="background: #ffe0e0; border: 1px solid #ffb3b3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="color: #c0392b; font-weight: 600; margin: 0;">Please fix the errors below:</p>
                        <ul style="color: #c0392b; margin-top: 10px; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if (session('success'))
                    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
                    </div>
                    @endif

                    <form action="{{ route('resident.settings.profile') }}" method="POST">
                        @csrf

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Full Name</label>
                            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Email Address</label>
                            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Phone Number</label>
                            <input type="text" name="phone" value="{{ $user->phone ?? '' }}" class="form-control" placeholder="(Optional)">
                        </div>

                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    </form>
                </div>
            </div>

            <!-- Security Section -->
            <div id="security" class="settings-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2>🔐 Change Password</h2>
                    </div>

                    <form action="{{ route('resident.settings.password') }}" method="POST">
                        @csrf

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Current Password</label>
                            <div class="password-input-container">
                                <input type="password" name="current_password" class="form-control" id="current_password" required onblur="autoHidePassword('current_password')">
                                <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">New Password</label>
                            <div class="password-input-container">
                                <input type="password" name="password" class="form-control" id="password" required minlength="8" onblur="autoHidePassword('password')">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small style="color: #7f8c8d;">Minimum 8 characters</small>
                        </div>

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Confirm Password</label>
                            <div class="password-input-container">
                                <input type="password" name="password_confirmation" class="form-control" id="password_confirmation" required minlength="8" onblur="autoHidePassword('password_confirmation')">
                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">🔄 Update Password</button>
                    </form>
                </div>
            </div>

            <!-- Preferences Section -->
            <div id="preferences" class="settings-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2>🎨 Preferences</h2>
                    </div>

                    <div style="padding: 20px 0;">
                        <p style="color: #7f8c8d; font-size: 1em; line-height: 1.6;">
                            📧 Email notifications and other preferences can be configured here in future updates.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Photo Section -->
            <div id="photo" class="settings-section" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h2>📸 Profile Photo</h2>
                    </div>

                    <div style="text-align: center; margin-bottom: 30px; padding: 20px 0;">
                        @if(auth()->user()->profile_photo)
                            <img 
                                src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}?t={{ time() }}" 
                                alt="Profile Photo" 
                                class="profile-photo-preview"
                                style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 4px solid #e9ecef;"
                            >
                        @else
                            <div 
                                style="width: 150px; height: 150px; background: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; border: 4px solid #e9ecef; font-size: 3rem; color: #999;"
                            >
                                <i class="fas fa-user"></i>
                            </div>
                        @endif
                    </div>

                    <form action="{{ route('resident.settings.photo') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div style="margin-bottom: 20px;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">
                                <i class="fas fa-upload"></i> Upload New Photo
                            </label>
                            <input 
                                type="file" 
                                name="profile_photo" 
                                class="form-control" 
                                accept="image/*"
                                onchange="previewPhoto(this)"
                                required
                            >
                            <small style="color: #7f8c8d;">Accepted formats: JPEG, PNG, JPG, GIF. Max size: 2MB</small>
                        </div>

                        <div id="photoPreview" style="text-align: center; margin-bottom: 20px; display: none;">
                            <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Preview</label>
                            <img id="previewImage" src="" alt="Photo Preview" style="max-width: 300px; max-height: 300px; border-radius: 8px;">
                        </div>

                        <button type="submit" class="btn btn-primary">💾 Upload Photo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.settings-nav-link {
    display: block;
    padding: 12px 16px;
    color: #7f8c8d;
    text-decoration: none;
    border-radius: 8px;
    border-left: 3px solid transparent;
    transition: all 0.3s ease;
    font-weight: 500;
    cursor: pointer;
}

.settings-nav-link:hover {
    background: #f0f3f7;
    color: #2c3e50;
    border-left-color: #667eea;
}

.settings-nav-link.active {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.1) 0%, transparent 100%);
    color: #667eea;
    border-left-color: #667eea;
    font-weight: 600;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f3f7;
}

.card-header h2 {
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.form-control {
    width: 100%;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 12px 15px;
    font-family: inherit;
    font-size: 1em;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.password-input-container {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.password-toggle:hover {
    color: #667eea;
    background: rgba(102, 126, 234, 0.1);
}

@media (max-width: 968px) {
    div[style*="grid-template-columns: 250px"] {
        grid-template-columns: 1fr !important;
    }

    .settings-nav-link {
        display: inline-block;
        margin-right: 10px;
        margin-bottom: 10px;
    }
}
</style>

<script>
function switchTab(event, tabId) {
    event.preventDefault();
    
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(section => {
        section.style.display = 'none';
    });
    
    // Remove active class from all links
    document.querySelectorAll('.settings-nav-link').forEach(link => {
        link.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(tabId).style.display = 'block';
    
    // Add active class to clicked link
    event.target.classList.add('active');
}

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
</script>
@endsection
