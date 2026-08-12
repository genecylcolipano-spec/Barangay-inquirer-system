@extends('superadmin.layouts.app')

@section('title', 'Admin Details')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-user-shield text-primary"></i> Administrator Details</h1>
            <p class="text-muted">View and manage administrator information</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('superadmin.admins.index') }}" class="btn btn-outline-secondary">
                Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm text-center">
            <div class="card-body p-4">
                <div style="font-size: 3rem; color: #007bff; margin-bottom: 15px;">
                    <i class="fas fa-user-shield"></i>
                </div>
                <h5 class="card-title fw-bold">{{ $admin->name }}</h5>
                <p class="text-muted mb-3">{{ $admin->email }}</p>
                <span class="badge bg-primary mb-3">Administrator</span>
                <div class="d-flex gap-2 flex-column">
                    <span class="badge bg-success">Active</span>
                </div>
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
                    <small class="text-muted">Joined</small>
                    <p class="mb-2 fw-bold">{{ $admin->created_at->format('M d, Y') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Last Active</small>
                    <p class="mb-0 fw-bold">{{ $admin->updated_at->diffForHumans() }}</p>
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
                        <p class="fw-bold">{{ $admin->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email Address</small>
                        <p class="fw-bold">{{ $admin->email }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted d-block">User ID</small>
                        <p class="fw-bold">#{{ $admin->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Role</small>
                        <p class="fw-bold"><span class="badge bg-primary">{{ ucfirst($admin->role) }}</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit"></i> Edit Information
                </a>

                @if($admin->id !== auth()->id())
                <form action="{{ route('superadmin.admins.destroy', $admin) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this admin? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Admin
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
