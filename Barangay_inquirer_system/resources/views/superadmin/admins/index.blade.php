@extends('superadmin.layouts.app')

@section('title', 'Manage Admins')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-shield-alt text-primary"></i> Administrators</h1>
            <p class="text-muted">Manage system administrators and their permissions</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Admin
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">All Administrators</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Last Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                    <tr>
                        <td>
                            <strong>{{ $admin->name }}</strong>
                        </td>
                        <td>{{ $admin->email }}</td>
                        <td>
                            @if($admin->updated_at->diffInDays() <= 7)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $admin->updated_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('superadmin.admins.show', $admin) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('superadmin.admins.edit', $admin) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('superadmin.admins.destroy', $admin) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">
                            <p class="text-muted">No administrators found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($admins->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $admins->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection
