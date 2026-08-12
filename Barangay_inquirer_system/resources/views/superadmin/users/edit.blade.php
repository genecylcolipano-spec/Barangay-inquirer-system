@extends('superadmin.layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-edit text-primary"></i> Edit User</h1>
            <p class="text-muted">Update user account information and role</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 fw-bold">User Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label fw-bold">User Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="resident" {{ old('role', $user->role) === 'resident' ? 'selected' : '' }}>Resident</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrator</option>
                            @if($user->id === auth()->id())
                            <option value="super_admin" {{ old('role', $user->role) === 'super_admin' ? 'selected' : '' }}>Super Administrator</option>
                            @endif
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Choose the appropriate role for this user</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('superadmin.users.show', $user) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Account Details</h6>
            </div>
            <div class="card-body">
                <div class="system-info-item">
                    <small class="text-muted">User ID</small>
                    <p class="mb-2 fw-bold">#{{ $user->id }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Current Role</small>
                    <p class="mb-2 fw-bold">
                        @if($user->role === 'super_admin')
                            <span class="badge bg-danger">Super Admin</span>
                        @elseif($user->role === 'admin')
                            <span class="badge bg-warning">Admin</span>
                        @else
                            <span class="badge bg-info">Resident</span>
                        @endif
                    </p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Member Since</small>
                    <p class="mb-2 fw-bold">{{ $user->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 fw-bold">{{ $user->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
