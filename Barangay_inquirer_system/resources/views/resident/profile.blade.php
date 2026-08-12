@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <div class="profile-header">
                @if(auth()->user()->profile_photo)
                    <img 
                        src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}?t={{ time() }}" 
                        alt="Profile Photo" 
                        class="profile-avatar"
                    >
                @else
                    <div class="profile-avatar profile-avatar-placeholder">
                        <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h1>My Profile</h1>
                    <p class="date-time">View your personal information</p>
                </div>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.profile.edit') }}" class="btn-primary">Edit Profile</a>
        </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr;">
        <div class="card">
            <div class="card-header">
                <h2>Personal Information</h2>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                <!-- Name -->
                <div>
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em; text-transform: uppercase;">Full Name</label>
                    <p style="color: #2c3e50; font-size: 1.1em; margin-top: 10px;">{{ $user->name }}</p>
                </div>

                <!-- Email -->
                <div>
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em; text-transform: uppercase;">Email Address</label>
                    <p style="color: #2c3e50; font-size: 1.1em; margin-top: 10px;">{{ $user->email }}</p>
                </div>

                <!-- Phone (if exists) -->
                @if($user->phone)
                <div>
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em; text-transform: uppercase;">Phone Number</label>
                    <p style="color: #2c3e50; font-size: 1.1em; margin-top: 10px;">{{ $user->phone }}</p>
                </div>
                @endif

                <!-- Address (if exists) -->
                @if($user->address)
                <div>
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em; text-transform: uppercase;">Address</label>
                    <p style="color: #2c3e50; font-size: 1.1em; margin-top: 10px;">{{ $user->address }}</p>
                </div>
                @endif

                <!-- Member Since -->
                <div>
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em; text-transform: uppercase;">Member Since</label>
                    <p style="color: #2c3e50; font-size: 1.1em; margin-top: 10px;">{{ $user->created_at->format('F d, Y') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ecf0f5; display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="{{ route('resident.profile.edit') }}" style="padding: 12px 28px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    ✏️ Edit Information
                </a>
                <a href="{{ route('resident.settings') }}" style="padding: 12px 28px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #2c3e50; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                    ⚙️ Account Settings
                </a>
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

.profile-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.profile-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #e9ecef;
}

.profile-avatar-placeholder {
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #999;
}

.greeting h1 {
    font-size: 2rem;
    color: #2c3e50;
    margin-bottom: 4px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f3f7;
}

.card-header h2 {
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.content-grid {
    display: grid;
    gap: 25px;
    margin-bottom: 30px;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .greeting h1 {
        font-size: 1.8em;
    }

    div[style*="grid-template-columns: repeat(auto-fit"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection
