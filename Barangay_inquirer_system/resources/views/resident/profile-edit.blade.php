@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <div style="display: flex; align-items: center; gap: 12px;">
                @if(auth()->user()->profile_photo)
                    <img 
                        src="{{ asset('storage/uploads/profiles/' . auth()->user()->profile_photo) }}?t={{ time() }}" 
                        alt="Profile Photo" 
                        style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 3px solid #e9ecef;"
                    >
                @else
                    <div 
                        style="width: 48px; height: 48px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #999; border: 3px solid #e9ecef;"
                    >
                        <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div>
                    <h1>Edit Profile</h1>
                    <p class="date-time">Update your personal information</p>
                </div>
            </div>
    </div>

    <div class="content-grid" style="grid-template-columns: 1fr; max-width: 600px;">
        <div class="card">
            @if ($errors->any())
            <div style="background: #ffe0e0; border: 1px solid #ffb3b3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <p style="color: #c0392b; font-weight: 600; margin: 0;">Please fix the following errors:</p>
                <ul style="color: #c0392b; margin-top: 10px; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('resident.profile.update') }}" method="POST">
                @csrf

                <!-- Name -->
                <div style="margin-bottom: 20px;">
                    <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Full Name</label>
                    <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                </div>

                <!-- Email -->
                <div style="margin-bottom: 20px;">
                    <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Email Address</label>
                    <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                </div>

                <!-- Phone -->
                <div style="margin-bottom: 20px;">
                    <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Phone Number</label>
                    <input type="text" name="phone" value="{{ $user->phone ?? '' }}" class="form-control" placeholder="(Optional)">
                </div>

                <!-- Address -->
                <div style="margin-bottom: 20px;">
                    <label style="color: #2c3e50; font-weight: 600; display: block; margin-bottom: 8px;">Address</label>
                    <input type="text" name="address" value="{{ $user->address ?? '' }}" class="form-control" placeholder="(Optional)">
                </div>

                <!-- Buttons -->
                <div style="display: flex; gap: 15px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                    <a href="{{ route('resident.profile') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
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

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
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

.btn-secondary {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    color: #2c3e50;
}

.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.content-grid {
    display: grid;
    gap: 25px;
}

@media (max-width: 768px) {
    .greeting h1 {
        font-size: 1.8em;
    }
}
</style>
@endsection
