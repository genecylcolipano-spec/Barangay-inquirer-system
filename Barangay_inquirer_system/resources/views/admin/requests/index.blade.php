@extends('admin.layouts.app')

@section('title', 'Document Requests')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h2">Document Requests</h1>
        <p class="text-muted">Manage resident document requests</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">Filter Requests</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="pending" @selected(request('status') == 'pending')>🟡 Pending - Waiting for review</option>
                        <option value="processing" @selected(request('status') == 'processing')>🔵 Processing - Being prepared</option>
                        <option value="approved" @selected(request('status') == 'approved')>🟢 Approved - Ready for pickup</option>
                        <option value="rejected" @selected(request('status') == 'rejected')>🔴 Rejected - With reason</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Filter</button>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($requests->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No document requests found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 15%">Resident</th>
                            <th style="width: 15%">Document Type</th>
                            <th style="width: 15%">Status</th>
                            <th style="width: 15%">Date Requested</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $request)
                            <tr>
                                <td><small>#{{ $request->id }}</small></td>
                                <td>
                                    <strong>{{ $request->user->name ?? 'Unknown' }}</strong><br>
                                    <small class="text-muted">{{ $request->user->email ?? '' }}</small>
                                </td>
                                <td>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $request->document_type)) }}</strong>
                                </td>
                                <td>
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
                                    <span class="badge {{ $statusConfig['class'] }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px;">
                                        <span>{{ $statusConfig['emoji'] }}</span>
                                        <span style="font-weight: 600;">{{ $statusConfig['label'] }}</span>
                                    </span>
                                    <br>
                                    <small style="opacity: 0.8;">{{ $statusConfig['description'] }}</small>
                                </td>
                                <td>
                                    <small>{{ $request->created_at->format('M d, Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

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
