@extends('admin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col d-flex align-items-center gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1"><i class="fas fa-bell text-primary"></i> Notifications</h1>
                <p class="text-muted mb-0">View and manage your notifications</p>
            </div>
            @if($unreadCount > 0)
                <span class="badge rounded-pill bg-danger">
                    {{ $unreadCount }}
                </span>
            @endif
        </div>
        <div class="col-auto">
            @if($unreadCount > 0)
            <form action="{{ route('admin.notifications.read-all') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            </form>
            @endif
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">All Notifications</h5>
    </div>
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        <div class="border-bottom p-3 notification-item" style="background-color: {{ is_null($notification->read_at) ? '#f8f9fa' : '#fff' }};">
            <div class="row align-items-start">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold d-flex align-items-center gap-2">
                                <span>{{ $notification->data['title'] ?? 'Notification' }}</span>
                                @if(is_null($notification->read_at))
                                    <span class="badge bg-primary">1</span>
                                @endif
                            </h6>
                            @if(isset($notification->data['type']) && $notification->data['type'] === 'contact_message')
                                <div class="text-muted small mb-2">
                                    <div><strong>From:</strong> {{ $notification->data['name'] ?? 'N/A' }} ({{ $notification->data['email'] ?? 'N/A' }})</div>
                                    <div><strong>Phone:</strong> {{ $notification->data['phone'] ?? 'N/A' }}</div>
                                    <div><strong>Subject:</strong> {{ $notification->data['subject'] ?? 'N/A' }}</div>
                                    <div><strong>Message:</strong> {{ $notification->data['message'] ?? '' }}</div>
                                </div>
                            @elseif(isset($notification->data['request_id']))
                                @php
                                    $req = \App\Models\DocumentRequest::with('user')->find($notification->data['request_id'] ?? null);
                                @endphp
                                @if($req)
                                    <div class="text-muted small mb-2">
                                        <div><strong>Request ID:</strong> {{ $req->id }}</div>
                                        <div><strong>Document:</strong> {{ str_replace('_',' ', $req->document_type) }}</div>
                                        <div><strong>Status:</strong> {{ $notification->data['status'] ?? $req->status }}</div>
                                        <div><strong>Requested By:</strong> {{ $req->user->name ?? $req->resident_name ?? 'Unknown' }} ({{ $req->user->email ?? 'N/A' }})</div>
                                    </div>
                                @else
                                    <p class="mb-2 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                                @endif
                            @else
                                <p class="mb-2 text-muted">{{ $notification->data['message'] ?? '' }}</p>
                            @endif
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> {{ $notification->created_at->format('M d, Y') }} at {{ $notification->created_at->format('h:i A') }}
                                ({{ $notification->created_at->diffForHumans() }})
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="btn-group" role="group">
                        @if(is_null($notification->read_at))
                        <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this notification?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5">
            <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No notifications yet</p>
        </div>
        @endforelse
    </div>
</div>

@if(method_exists($notifications, 'hasPages') && $notifications->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>
@endif
@endsection