@extends('superadmin.layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-bell text-primary"></i> Notifications</h1>
            <p class="text-muted">View and manage all your notifications</p>
        </div>
        <div class="col-auto">
            @if($unreadCount > 0)
            <form action="{{ route('superadmin.notifications.read-all') }}" method="POST" style="display: inline;">
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

<!-- Filter Options -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-12">
                <p class="mb-0 text-muted">
                    <strong>{{ $unreadCount }} unread</strong> notification(s) • 
                    <strong>{{ $notifications->count() }} total</strong>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">All Notifications</h5>
    </div>
    <div class="card-body p-0">
        @forelse($notifications as $notification)
        <div class="border-bottom p-3 notification-item" style="background-color: {{ is_null($notification->read_at) ? '#faf8f8' : '#fff' }};">
            <div class="row align-items-start">
                <div class="col">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1 fw-bold">
                                {{ $notification->data['title'] ?? 'Notification' }}
                                @if(is_null($notification->read_at))
                                    <span class="badge bg-danger">New</span>
                                @endif
                            </h6>
                            <p class="mb-2 text-muted">{{ $notification->data['message'] ?? '' }}</p>
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
                        <form action="{{ route('superadmin.notifications.read', $notification->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Mark as read">
                                <i class="fas fa-check"></i>
                            </button>
                        </form>
                        @endif
                        <form action="{{ route('superadmin.notifications.destroy', $notification->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this notification?')">
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

<!-- Pagination -->
@if(method_exists($notifications, 'hasPages') && $notifications->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $notifications->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection
