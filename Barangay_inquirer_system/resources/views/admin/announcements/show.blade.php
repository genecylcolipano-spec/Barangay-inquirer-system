@extends('admin.layouts.app')

@section('title', 'View Announcement')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-bullhorn text-primary"></i> {{ $announcement->title }}</h1>
            <p class="text-muted">View and manage announcement</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
                Back to List
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Announcement Content</h6>
            </div>
            <div class="card-body">
                <h4 class="fw-bold mb-3">{{ $announcement->title }}</h4>
                <div class="mb-4">
                    {!! nl2br($announcement->content) !!}
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Created By</small>
                        <p class="fw-bold">{{ $announcement->creator->name ?? 'System' }}</p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Created Date</small>
                        <p class="fw-bold">{{ $announcement->created_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted">Status</small>
                        <p class="fw-bold">
                            @if($announcement->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Last Updated</small>
                        <p class="fw-bold">{{ $announcement->updated_at->format('M d, Y H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Status Info</h6>
            </div>
            <div class="card-body text-center">
                @if($announcement->is_published)
                    <div style="font-size: 2rem; color: #28a745; margin-bottom: 15px;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="fw-bold"><span class="badge bg-success">Published</span></p>
                    <p class="text-muted">This announcement is visible to all users</p>
                @else
                    <div style="font-size: 2rem; color: #6c757d; margin-bottom: 15px;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <p class="fw-bold"><span class="badge bg-secondary">Draft</span></p>
                    <p class="text-muted">This announcement is not visible to users</p>
                @endif
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.announcements.edit', $announcement) }}" class="btn btn-warning btn-sm d-grid mb-2">
                    <i class="fas fa-edit"></i> Edit Content
                </a>

                <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this announcement?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm d-grid w-100">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection