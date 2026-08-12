@extends('superadmin.layouts.app')

@section('title', 'Edit Admin')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-edit text-primary"></i> Edit Administrator</h1>
            <p class="text-muted">Update administrator account information</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 fw-bold">Admin Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.admins.update', $admin) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="name" class="form-label fw-bold">Full Name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="status" class="form-label fw-bold">Account Status</label>
                        <select class="form-select" id="status" name="status" disabled>
                            <option selected>Active</option>
                        </select>
                        <small class="text-muted">Status management coming soon</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('superadmin.admins.show', $admin) }}" class="btn btn-outline-secondary">
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
                    <p class="mb-2 fw-bold">#{{ $admin->id }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Role</small>
                    <p class="mb-2 fw-bold">
                        <span class="badge bg-primary">{{ ucfirst($admin->role) }}</span>
                    </p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Joined</small>
                    <p class="mb-2 fw-bold">{{ $admin->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-0 fw-bold">{{ $admin->updated_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
