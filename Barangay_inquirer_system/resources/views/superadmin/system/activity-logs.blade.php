@extends('superadmin.layouts.app')

@section('title', 'Activity Logs')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-history text-primary"></i> Activity Logs</h1>
            <p class="text-muted">Track all system activities and user actions</p>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">System Activity Log</h5>
    </div>
    <div class="card-body">
        <div class="activity-timeline">
            @forelse($activities as $act)
            <div class="activity-item">
                <div class="activity-time">{{ $act->created_at->diffForHumans() }}</div>
                <div class="activity-icon bg-primary">
                    <i class="fas fa-bell text-white"></i>
                </div>
                <div class="activity-content">
                    <p class="mb-0"><strong>{{ $act->message }}</strong></p>
                    @if($act->user_id)
                        <small class="text-muted">by {{ optional($act->user)->name }}</small>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center text-muted py-4">No activity found</div>
            @endforelse

            <div class="mt-3">
                {{ $activities->links() }}
            </div>
        </div>
    </div>
</div>

@endsection
