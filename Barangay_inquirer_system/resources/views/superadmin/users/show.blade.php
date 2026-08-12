@extends('superadmin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-user text-primary"></i> User Details</h1>
            <p class="text-muted">View and manage user information</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('superadmin.users.index') }}" class="btn btn-outline-secondary">
                Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                @if($user->profile_photo)
                    <img src="/storage/uploads/profiles/{{ $user->profile_photo }}?t={{ time() }}" 
                         style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; border: 4px solid #17a2b8; display: block;" 
                         alt="Profile Photo"
                         onerror="this.style.display='none'; document.querySelector('.fallback-icon-superadmin').style.display='block';">
                    <div class="fallback-icon-superadmin" style="display: none; font-size: 3rem; color: #17a2b8; margin-bottom: 15px;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                @else
                    <div style="font-size: 3rem; color: #17a2b8; margin-bottom: 15px;">
                        <i class="fas fa-user-circle"></i>
                    </div>
                @endif
                <h5 class="card-title fw-bold">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                @if($user->role === 'super_admin')
                    <span class="badge bg-danger mb-3">Super Admin</span>
                @elseif($user->role === 'admin')
                    <span class="badge bg-warning mb-3">Administrator</span>
                @else
                    <span class="badge bg-info mb-3">Resident</span>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Account Info</h6>
            </div>
            <div class="card-body">
                <div class="system-info-item">
                    <small class="text-muted">Status</small>
                    <p class="mb-2 fw-bold"><i class="fas fa-check-circle text-success"></i> Active</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Member Since</small>
                    <p class="mb-2 fw-bold">{{ $user->created_at->format('M d, Y') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Last Active</small>
                    <p class="mb-0 fw-bold">{{ $user->updated_at->diffForHumans() }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Personal Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Full Name</small>
                        <p class="fw-bold">{{ $user->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email Address</small>
                        <p class="fw-bold">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">User ID</small>
                        <p class="fw-bold">#{{ $user->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">User Role</small>
                        <p class="fw-bold">
                            @if($user->role === 'super_admin')
                                <span class="badge bg-danger">Super Administrator</span>
                            @elseif($user->role === 'admin')
                                <span class="badge bg-warning">Administrator</span>
                            @else
                                <span class="badge bg-info">Resident</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Account Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('superadmin.users.edit', $user) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i> Edit Information
                </a>

                @if($user->id !== auth()->id())
                <form action="{{ route('superadmin.users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete User
                    </button>
                </form>
                @else
                <span class="text-muted"><i class="fas fa-info-circle"></i> Cannot delete your own account</span>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
