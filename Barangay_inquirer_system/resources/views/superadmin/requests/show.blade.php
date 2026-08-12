@extends('superadmin.layouts.app')

@section('title', 'Request Details')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-file-alt text-primary"></i> Request #{{ $request->id }}</h1>
            <p class="text-muted">View and manage document request details</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.requests.index') }}" class="btn btn-outline-secondary">
                Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Request Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Request ID</small>
                        <p class="fw-bold">#{{ $request->id }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Document Type</small>
                        <p class="fw-bold"><span class="badge bg-info">{{ ucfirst($request->document_type ?? 'N/A') }}</span></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Resident Name</small>
                        <p class="fw-bold">{{ $request->user->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Email</small>
                        <p class="fw-bold">{{ $request->user->email ?? 'N/A' }}</p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Submitted Date</small>
                        <p class="fw-bold">{{ $request->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Last Updated</small>
                        <p class="fw-bold">{{ $request->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block">Request Details</small>
                    <p class="fw-bold">{{ $request->details ?? 'No additional details provided' }}</p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Admin Notes</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.requests.update-notes', $request) }}" method="POST">
                    @csrf
                    @method('POST')
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Add/Edit Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" placeholder="Add internal notes here...">{{ $request->notes ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> Save Notes
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Current Status</h6>
            </div>
            <div class="card-body text-center">
                @if($request->status === 'pending')
                    <div style="font-size: 2rem; color: #ffc107; margin-bottom: 15px;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <p class="fw-bold mb-3"><span class="badge bg-warning">Pending</span></p>
                    <p class="text-muted">Awaiting review and approval</p>
                @elseif($request->status === 'approved')
                    <div style="font-size: 2rem; color: #28a745; margin-bottom: 15px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="fw-bold mb-3"><span class="badge bg-success">Approved</span></p>
                    <p class="text-muted">Request has been approved</p>
                @elseif($request->status === 'rejected')
                    <div style="font-size: 2rem; color: #dc3545; margin-bottom: 15px;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <p class="fw-bold mb-3"><span class="badge bg-danger">Rejected</span></p>
                    <p class="text-muted">Request has been rejected</p>
                @endif
            </div>
        </div>

        @if($request->status === 'pending')
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Actions</h6>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.requests.approve', $request) }}" method="POST" class="d-grid gap-2 mb-2">
                    @csrf
                    @method('POST')
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Approve Request
                    </button>
                </form>

                <button class="btn btn-danger d-grid gap-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fas fa-times"></i> Reject Request
                </button>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('superadmin.requests.reject', $request) }}" method="POST">
                @csrf
                @method('POST')
                
                <div class="modal-header">
                    <h5 class="modal-title">Reject Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label fw-bold">Reason for Rejection</label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="4" required placeholder="Provide reason for rejection..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
