@extends('superadmin.layouts.app')

@section('title', 'Announcements')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-bullhorn text-primary"></i> Announcements</h1>
            <p class="text-muted">Create and manage system announcements</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Announcement
            </a>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0 fw-bold">All Announcements</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title & Tag</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Homepage</th>
                        <th>Priority</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $announcement)
                    <tr>
                        <td>
                            <strong>{{ $announcement->title }}</strong>
                            <br>
                            <small class="text-muted">
                                <span class="badge {{ $announcement->getTagBadgeClass() }} badge-sm">
                                    {{ $announcement->getTagDisplayText() }}
                                </span>
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ ucfirst($announcement->category) }}</span>
                        </td>
                        <td>
                            @if($announcement->is_published)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Draft</span>
                            @endif
                        </td>
                        <td>
                            @if($announcement->show_on_homepage)
                                <span class="badge bg-info">Yes</span>
                                <br>
                                <small class="text-muted">Order: {{ $announcement->display_order }}</small>
                            @else
                                <span class="badge bg-light text-muted">No</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-{{ $announcement->priority === 'high' ? 'danger' : ($announcement->priority === 'low' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($announcement->priority) }}
                            </span>
                        </td>
                        <td>
                            {{ $announcement->getDisplayDate()->format('M d, Y') }}
                            <br>
                            <small class="text-muted">by {{ $announcement->creator->name ?? 'System' }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('superadmin.announcements.show', $announcement) }}" class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('superadmin.announcements.edit', $announcement) }}" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('superadmin.announcements.destroy', $announcement) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <p class="text-muted">No announcements found</p>
                            <a href="{{ route('superadmin.announcements.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create your first announcement
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($announcements->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $announcements->links('pagination::bootstrap-5') }}
</div>
@endif

@endsection
