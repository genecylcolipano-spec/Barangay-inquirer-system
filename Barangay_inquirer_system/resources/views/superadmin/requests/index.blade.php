@extends('superadmin.layouts.app')

@section('title', 'Document Requests')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-file-alt text-primary"></i> Document Requests</h1>
            <p class="text-muted">Monitor and manage all document requests from residents</p>
        </div>
    </div>
</div>

<!-- Filter Options -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Filter by Status</label>
                <div>
                    <a href="{{ route('superadmin.requests.index') }}" class="btn btn-sm btn-outline-primary me-2">All</a>
                    <a href="{{ route('superadmin.requests.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-warning me-2">
                        <i class="fas fa-hourglass-half"></i> Pending
                    </a>
                    <a href="{{ route('superadmin.requests.index', ['status' => 'approved']) }}" class="btn btn-sm btn-outline-success me-2">
                        <i class="fas fa-check"></i> Approved
                    </a>
                    <a href="{{ route('superadmin.requests.index', ['status' => 'rejected']) }}" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times"></i> Rejected
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">All Requests</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Request ID</th>
                        <th>Resident</th>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                    <tr>
                        <td><strong>#{{ $request->id }}</strong></td>
                        <td>{{ $request->user->name ?? 'N/A' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $request->document_type ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($request->status === 'pending')
                                <span class="badge bg-warning">Pending</span>
                            @elseif($request->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @else
                                <span class="badge bg-danger">Rejected</span>
                            @endif
                        </td>
                        <td>{{ $request->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('superadmin.requests.show', $request) }}" class="btn btn-sm btn-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <p class="text-muted">No requests found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($requests->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $requests->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection
