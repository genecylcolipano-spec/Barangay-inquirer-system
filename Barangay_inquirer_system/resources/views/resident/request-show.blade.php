@extends('resident.layout')

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 fw-bold">
                    <i class="fas fa-file-alt text-primary"></i>
                    {{ ucfirst(str_replace('_', ' ', $request->document_type)) }} Request
                </h1>
                <p class="text-muted">Request #{{ $request->id }} • Submitted {{ $request->created_at->format('M d, Y') }}</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('resident.requests') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to My Requests
                </a>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Request Details -->
        <div class="col-md-8">
            <!-- Status Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Request Status</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Current Status</label>
                                <div>
                                    @if($request->status == 'pending')
                                        <span class="badge bg-warning text-dark fs-6">
                                            <i class="fas fa-clock"></i> Pending Review
                                        </span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge bg-success fs-6">
                                            <i class="fas fa-check-circle"></i> Approved
                                        </span>
                                    @elseif($request->status == 'rejected')
                                        <span class="badge bg-danger fs-6">
                                            <i class="fas fa-times-circle"></i> Rejected
                                        </span>
                                    @elseif($request->status == 'completed')
                                        <span class="badge bg-info fs-6">
                                            <i class="fas fa-check-double"></i> Completed
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Last Updated</label>
                                <div class="text-muted">
                                    <i class="fas fa-calendar-alt"></i> {{ $request->updated_at->format('M d, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($request->status == 'approved')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <strong>Great news!</strong> Your request has been approved. Please visit the Barangay Hall to pick up your document.
                        </div>
                    @elseif($request->status == 'rejected')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            <strong>Request Rejected</strong> Please check the admin notes below for more information.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Request Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Request Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Document Type</label>
                            <div class="fw-bold">{{ ucfirst(str_replace('_', ' ', $request->document_type)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Date Submitted</label>
                            <div>{{ $request->created_at->format('M d, Y \a\t h:i A') }}</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="fw-bold">{{ $request->full_name ?? 'Not provided' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Address</label>
                            <div style="white-space: pre-wrap;">{{ $request->address ?? 'Not provided' }}</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Purpose</label>
                        <div class="bg-light p-3 rounded">{{ $request->details ?? 'No purpose provided.' }}</div>
                    </div>

                    @if($request->attachment)
                        <div class="mb-3">
                            <label class="form-label text-muted small">ID Document</label>
                            <div>
                                <a href="{{ route('resident.request.download', $request) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-download"></i> View ID
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Admin Notes Section -->
            @if($request->notes)
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-sticky-note text-info"></i> Admin Notes
                            </h5>
                            @php
                                $notificationExists = auth()->user()->notifications()
                                    ->where('type', 'App\\Notifications\\AdminNotesUpdated')
                                    ->where('data->request_id', $request->id)
                                    ->exists();
                                $isRecentUpdate = $request->updated_at->diffInHours(now()) < 24;
                            @endphp
                            @if($isRecentUpdate && $notificationExists)
                                <span class="badge bg-success">
                                    <i class="fas fa-bell"></i> Notification Received
                                </span>
                            @elseif($isRecentUpdate)
                                <span class="badge bg-warning">
                                    <i class="fas fa-exclamation"></i> New Update
                                </span>
                            @endif
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> Updated {{ $request->updated_at->format('M d, Y \a\t h:i A') }} ({{ $request->updated_at->diffForHumans() }})
                        </small>
                    </div>
                    <div class="card-body">
                        <div class="bg-light p-3 rounded border-start border-info border-4">
                            <div class="d-flex align-items-start">
                                <div class="flex-shrink-0 me-3">
                                    <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-info mb-2">Barangay Administrator</div>
                                    <div class="text-dark">{{ $request->notes }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                If you have questions about these notes, please contact the barangay office.
                            </small>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($request->status == 'pending')
                        <form action="{{ route('resident.request.destroy', $request->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this request?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 mb-2">
                                <i class="fas fa-trash"></i> Delete Request
                            </button>
                        </form>
                        <small class="text-muted">You can only delete pending requests</small>
                    @endif

                    <a href="{{ route('resident.request.create') }}" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Submit New Request
                    </a>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0 fw-bold">Need Help?</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        If you have questions about your request or need assistance, please contact us:
                    </p>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                        <span class="small">{{ \App\Models\Setting::get('footer_address', 'Barangay Hall, Main Street') }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone text-primary me-2"></i>
                        <span class="small">{{ \App\Models\Setting::get('footer_phone', '+63 XXX XXX XXXX') }}</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock text-primary me-2"></i>
                        <span class="small">Mon-Fri: 8AM - 5PM</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .page-header h1 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .page-header .text-muted {
        color: #6c757d;
        margin-bottom: 0;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .card-header {
        border-bottom: 1px solid #e9ecef;
        font-weight: 600;
    }

    .badge {
        font-size: 0.8em;
        padding: 0.5em 0.75em;
    }

    .alert {
        border-radius: 8px;
        border: none;
    }
</style>
@endsection