@extends('superadmin.layouts.app')

@section('title', 'Edit Announcement')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-edit text-primary"></i> Edit Announcement</h1>
            <p class="text-muted">Update announcement content and settings</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0 fw-bold">Announcement Details</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('superadmin.announcements.update', $announcement) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Announcement Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label fw-bold">Short Excerpt (for Homepage)</label>
                                <input type="text" class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" value="{{ old('excerpt', $announcement->excerpt) }}" placeholder="Brief summary for homepage display">
                                @error('excerpt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">This will be shown on the homepage if "Show on Homepage" is enabled</small>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label fw-bold">Full Content</label>
                                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required>{{ old('content', $announcement->content) }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tag" class="form-label fw-bold">Tag/Badge</label>
                                <select class="form-select @error('tag') is-invalid @enderror" id="tag" name="tag">
                                    <option value="info" {{ old('tag', $announcement->tag) == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="today" {{ old('tag', $announcement->tag) == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="featured" {{ old('tag', $announcement->tag) == 'featured' ? 'selected' : '' }}>Featured</option>
                                    <option value="success" {{ old('tag', $announcement->tag) == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="warning" {{ old('tag', $announcement->tag) == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="danger" {{ old('tag', $announcement->tag) == 'danger' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                    <option value="general" {{ old('category', $announcement->category) == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="maintenance" {{ old('category', $announcement->category) == 'maintenance' ? 'selected' : '' }}>System Maintenance</option>
                                    <option value="feature" {{ old('category', $announcement->category) == 'feature' ? 'selected' : '' }}>New Feature</option>
                                    <option value="policy" {{ old('category', $announcement->category) == 'policy' ? 'selected' : '' }}>Policy Change</option>
                                    <option value="event" {{ old('category', $announcement->category) == 'event' ? 'selected' : '' }}>Event/Update</option>
                                    <option value="document" {{ old('category', $announcement->category) == 'document' ? 'selected' : '' }}>Document</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label fw-bold">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="low" {{ old('priority', $announcement->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', $announcement->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', $announcement->priority) == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="announcement_date" class="form-label fw-bold">Display Date</label>
                                <input type="date" class="form-control @error('announcement_date') is-invalid @enderror" id="announcement_date" name="announcement_date" value="{{ old('announcement_date', $announcement->announcement_date ? $announcement->announcement_date->format('Y-m-d') : '') }}">
                                @error('announcement_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to use creation date</small>
                            </div>

                            <div class="mb-3">
                                <label for="icon" class="form-label fw-bold">Icon (FontAwesome)</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', $announcement->icon) }}" placeholder="fas fa-bullhorn">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">FontAwesome icon class (e.g., fas fa-bullhorn)</small>
                            </div>

                            <div class="mb-3">
                                <label for="display_order" class="form-label fw-bold">Display Order</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order', $announcement->display_order ?? 0) }}" min="0">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Lower numbers appear first on homepage</small>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" {{ old('is_published', $announcement->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    <strong>Published</strong>
                                </label>
                                <small class="text-muted d-block">Uncheck to save as draft</small>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="show_on_homepage" name="show_on_homepage" value="1" {{ old('show_on_homepage', $announcement->show_on_homepage) ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_on_homepage">
                                    <strong>Show on Homepage</strong>
                                </label>
                                <small class="text-muted d-block">Display in latest announcements section</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="{{ route('superadmin.announcements.show', $announcement) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Announcement Info</h6>
            </div>
            <div class="card-body">
                <div class="system-info-item">
                    <small class="text-muted">Created</small>
                    <p class="mb-2 fw-bold">{{ $announcement->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Created By</small>
                    <p class="mb-2 fw-bold">{{ $announcement->creator->name ?? 'System' }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Last Updated</small>
                    <p class="mb-2 fw-bold">{{ $announcement->updated_at->format('M d, Y H:i') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Status</small>
                    <p class="mb-0 fw-bold">
                        @if($announcement->is_published)
                            <span class="badge bg-success">Published</span>
                        @else
                            <span class="badge bg-secondary">Draft</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
