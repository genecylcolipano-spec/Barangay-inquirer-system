@extends('admin.layouts.app')

@section('title', 'View Document Request')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h2">Document Request #{{ $request->id }}</h1>
        <p class="text-muted">Request Details and Management</p>
    </div>
    <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Requests
    </a>
</div>

<div class="row">
    <!-- Request Details -->
    <div class="col-md-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Request Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Status</label>
                        <div>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <span class="badge {{ $statusConfig['class'] }}" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 6px; font-size: 1em;">
                                <span style="font-size: 1.4em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 700;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.8em; opacity: 0.9;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Document Type</label>
                        <div>
                            <strong>{{ ucfirst(str_replace('_', ' ', $request->document_type)) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted small">Full Name</label>
                        <div>
                            <strong>{{ $request->full_name ?? 'Not provided' }}</strong>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label text-muted small">Address</label>
                        <div class="bg-light p-2 rounded" style="white-space: pre-wrap;">{{ $request->address ?? 'Not provided' }}</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Date Requested</label>
                        <div>{{ $request->created_at->format('M d, Y H:i A') }}</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted small">Last Updated</label>
                        <div>{{ $request->updated_at->format('M d, Y H:i A') }}</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Purpose</label>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0">{{ $request->details ?? 'No purpose provided.' }}</p>
                    </div>
                </div>

                @if($request->attachment)
                    <div class="mb-3">
                        <label class="form-label text-muted small">ID Document</label>
                        <div>
                            <a href="{{ route('admin.requests.download', $request) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-download"></i> Download ID
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notes Section -->
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Admin Notes</h5>
                @if($request->notes)
                    <small class="text-muted">
                        <i class="fas fa-clock"></i> Last updated: {{ $request->updated_at->format('M d, Y H:i A') }}
                    </small>
                @endif
            </div>
            <div class="card-body">
                @if (session('success') && str_contains(session('success'), 'Resident has been notified'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-bell"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle"></i> {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('admin.requests.update-notes', $request->id) }}" method="POST" id="notesForm">
                    @csrf
                    <div class="mb-3">
                        <textarea class="form-control" name="notes" rows="4" placeholder="Add notes about this request...">{{ $request->notes }}</textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> Notes will be visible to the resident and they'll receive a notification when updated.
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-save"></i> Save & Notify Resident
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" onclick="resetNotes()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Resident & Action Sidebar -->
    <div class="col-md-4">
        <!-- Resident Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Resident Information</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div style="width: 80px; height: 80px; background-color: #e9ecef; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user fa-2x text-muted"></i>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Name</label>
                    <div><strong>{{ optional($request->user)->name ?? 'N/A' }}</strong></div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <div><small>{{ optional($request->user)->email ?? 'N/A' }}</small></div>
                </div>

                @if($request->user)
                <a href="{{ route('admin.users.show', $request->user->id) }}" class="btn btn-sm btn-outline-primary w-100">
                    <i class="fas fa-user"></i> View Full Profile
                </a>
                @endif
            </div>
        </div>

        <!-- Admin Review Checklist -->
        <div class="card shadow-sm mb-4 border-info">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-clipboard-check text-info"></i> Review Checklist
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle"></i> Complete these checks before approving:
                </p>
                <form id="reviewForm">
                    @csrf
                    <div class="form-check mb-2">
                        <input class="form-check-input review-checkbox" type="checkbox" id="verify_identity" value="identity">
                        <label class="form-check-label" for="verify_identity">
                            <i class="fas fa-id-card text-primary"></i> Resident identity verified
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input review-checkbox" type="checkbox" id="documents_complete" value="documents">
                        <label class="form-check-label" for="documents_complete">
                            <i class="fas fa-file-contract text-primary"></i> All supporting documents provided
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input review-checkbox" type="checkbox" id="eligibility_met" value="eligibility">
                        <label class="form-check-label" for="eligibility_met">
                            <i class="fas fa-check-double text-primary"></i> Eligibility requirements met
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input review-checkbox" type="checkbox" id="no_issues" value="issues">
                        <label class="form-check-label" for="no_issues">
                            <i class="fas fa-thumbs-up text-primary"></i> No issues or concerns
                        </label>
                    </div>

                    <div id="checklist-progress" class="mt-3">
                        <small class="text-muted">Progress: <span id="checked-count">0</span>/4 items</small>
                        <div class="progress" style="height: 6px;">
                            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Request Status Actions -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Status Actions</h5>
            </div>
            <div class="card-body">
                @if($request->status !== 'approved')
                    <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST" class="mb-2" id="approveForm" onsubmit="return validateApprove()">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 btn-sm" id="approveBtn">
                            <i class="fas fa-check-circle"></i> Approve Request
                        </button>
                        <small class="text-muted d-block mt-2" id="approveHint">
                            <i class="fas fa-info-circle"></i> Complete checklist above to approve
                        </small>
                    </form>
                @endif

                @if($request->status !== 'rejected')
                    <button type="button" class="btn btn-danger w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="fas fa-times-circle"></i> Reject Request
                    </button>
                @endif

                @if($request->status !== 'processing')
                    <form action="{{ route('admin.requests.processing', $request->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-info w-100 btn-sm">
                            <i class="fas fa-spinner"></i> Mark as Processing
                        </button>
                    </form>
                @endif

                @if($request->status !== 'pending')
                    <form action="{{ route('admin.requests.pending', $request->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 btn-sm">
                            <i class="fas fa-clock"></i> Mark as Pending
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-times-circle"></i> Reject Request
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" onsubmit="return validateReject()">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Please provide detailed reasons for rejection.</strong> The resident will see these notes.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="notes" id="rejectNotes" rows="4" 
                                  placeholder="Explain why this request is being rejected..." required></textarea>
                        <div class="form-text">
                            <i class="fas fa-check"></i> Clear explanation helps residents understand and possibly resubmit.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>
</div>

<script>
function resetNotes() {
    const textarea = document.querySelector('textarea[name="notes"]');
    const originalValue = '{{ addslashes($request->notes ?? '') }}';
    textarea.value = originalValue;
    textarea.focus();
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.querySelector('textarea[name="notes"]');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        // Initial resize
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }

    // Checklist tracking
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateChecklistProgress);
    });
    updateChecklistProgress();
});

function updateChecklistProgress() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
    const total = checkboxes.length;
    
    document.getElementById('checked-count').textContent = checked;
    const percentage = (checked / total) * 100;
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = percentage + '%';
    
    // Update progress bar color
    if (percentage === 100) {
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-success');
    } else if (percentage >= 50) {
        progressBar.classList.remove('bg-warning');
        progressBar.classList.add('bg-info');
    } else {
        progressBar.classList.add('bg-warning');
    }
    
    // Enable/disable approve button
    const approveBtn = document.getElementById('approveBtn');
    const approveHint = document.getElementById('approveHint');
    
    if (percentage === 100) {
        approveBtn.disabled = false;
        approveBtn.classList.remove('disabled');
        approveHint.innerHTML = '<i class="fas fa-check-circle text-success"></i> <strong>All checks complete! Ready to approve.</strong>';
        approveHint.classList.remove('text-muted');
        approveHint.classList.add('text-success');
    } else {
        approveBtn.disabled = true;
        approveBtn.classList.add('disabled');
        approveHint.innerHTML = '<i class="fas fa-info-circle"></i> Complete checklist above to approve (' + checked + '/' + total + ')';
        approveHint.classList.remove('text-success');
        approveHint.classList.add('text-muted');
    }
}

function validateApprove() {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    
    if (!allChecked) {
        alert('⚠️ Please complete all checklist items before approving.');
        return false;
    }
    
    const confirmed = confirm('✅ You are about to APPROVE this request.\n\nThe resident will be notified and can pick up their document.');
    return confirmed;
}

function validateReject() {
    const notes = document.getElementById('rejectNotes').value.trim();
    
    if (notes.length < 10) {
        alert('❌ Please provide a detailed rejection reason (at least 10 characters).\n\nThe resident needs to understand why their request was rejected.');
        document.getElementById('rejectNotes').focus();
        return false;
    }
    
    const confirmed = confirm('⚠️ You are about to REJECT this request.\n\nReason:\n' + notes.substring(0, 100) + '...\n\nThe resident will be notified with these notes.');
    return confirmed;
}
</script>

<style>
    /* Status Badge Styles */
    .badge {
        font-size: 0.85em;
        font-weight: 600;
    }

    .badge-pending {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        border: 1px solid #fcd34d;
    }

    .badge-processing {
        background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }

    .badge-approved {
        background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
        color: #166534;
        border: 1px solid #4ade80;
    }

    .badge-danger {
        background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
        color: #991b1b;
        border: 1px solid #f87171;
    }

    .badge-completed {
        background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
        color: #1f2937;
        border: 1px solid #6b7280;
    }

    .badge-info {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #1e40af;
        border: 1px solid #60a5fa;
    }
</style>
@endsection
