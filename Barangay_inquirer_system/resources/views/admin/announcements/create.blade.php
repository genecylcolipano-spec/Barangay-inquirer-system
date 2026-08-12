@extends('admin.layouts.app')

@section('title', 'Create Announcement')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-plus-circle text-primary"></i> Create New Announcement</h1>
            <p class="text-muted">Create and publish a system announcement</p>
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
                <form action="{{ route('admin.announcements.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Announcement Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required placeholder="Enter announcement title">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="excerpt" class="form-label fw-bold">Short Excerpt (for Homepage)</label>
                                <input type="text" class="form-control @error('excerpt') is-invalid @enderror" id="excerpt" name="excerpt" value="{{ old('excerpt') }}" placeholder="Brief summary for homepage display">
                                @error('excerpt')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">This will be shown on the homepage if "Show on Homepage" is enabled</small>
                            </div>

                            <div class="mb-3">
                                <label for="content" class="form-label fw-bold">Full Content</label>
                                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="8" required placeholder="Enter announcement content...">{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tag" class="form-label fw-bold">Tag/Badge</label>
                                <select class="form-select @error('tag') is-invalid @enderror" id="tag" name="tag">
                                    <option value="info" {{ old('tag', 'info') == 'info' ? 'selected' : '' }}>Info</option>
                                    <option value="today" {{ old('tag', 'today') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="featured" {{ old('tag', 'featured') == 'featured' ? 'selected' : '' }}>Featured</option>
                                    <option value="success" {{ old('tag', 'success') == 'success' ? 'selected' : '' }}>Success</option>
                                    <option value="warning" {{ old('tag', 'warning') == 'warning' ? 'selected' : '' }}>Warning</option>
                                    <option value="danger" {{ old('tag', 'danger') == 'danger' ? 'selected' : '' }}>Urgent</option>
                                </select>
                                @error('tag')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="category" class="form-label fw-bold">Category</label>
                                <select class="form-select @error('category') is-invalid @enderror" id="category" name="category">
                                    <option value="general" {{ old('category', 'general') == 'general' ? 'selected' : '' }}>General</option>
                                    <option value="maintenance" {{ old('category', 'maintenance') == 'maintenance' ? 'selected' : '' }}>System Maintenance</option>
                                    <option value="feature" {{ old('category', 'feature') == 'feature' ? 'selected' : '' }}>New Feature</option>
                                    <option value="policy" {{ old('category', 'policy') == 'policy' ? 'selected' : '' }}>Policy Change</option>
                                    <option value="event" {{ old('category', 'event') == 'event' ? 'selected' : '' }}>Event/Update</option>
                                    <option value="document" {{ old('category', 'document') == 'document' ? 'selected' : '' }}>Document</option>
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="priority" class="form-label fw-bold">Priority</label>
                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">
                                    <option value="low" {{ old('priority', 'normal') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ old('priority', 'normal') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ old('priority', 'high') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="announcement_date" class="form-label fw-bold">Display Date</label>
                                <input type="date" class="form-control @error('announcement_date') is-invalid @enderror" id="announcement_date" name="announcement_date" value="{{ old('announcement_date') }}">
                                @error('announcement_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Leave empty to use current date</small>
                            </div>

                            <div class="mb-3">
                                <label for="icon" class="form-label fw-bold">Icon (FontAwesome)</label>
                                <input type="text" class="form-control @error('icon') is-invalid @enderror" id="icon" name="icon" value="{{ old('icon', 'fas fa-bullhorn') }}" placeholder="fas fa-bullhorn">
                                @error('icon')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">FontAwesome icon class</small>
                            </div>

                            <div class="mb-3">
                                <label for="display_order" class="form-label fw-bold">Display Order</label>
                                <input type="number" class="form-control @error('display_order') is-invalid @enderror" id="display_order" name="display_order" value="{{ old('display_order', 0) }}" min="0">
                                @error('display_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Lower numbers appear first</small>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_published" name="is_published" value="1" checked>
                                <label class="form-check-label" for="is_published">
                                    <strong>Publish immediately</strong>
                                </label>
                                <small class="text-muted d-block">If unchecked, announcement will be saved as draft</small>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="show_on_homepage" name="show_on_homepage" value="1" {{ old('show_on_homepage') ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_on_homepage">
                                    <strong>Show on Homepage</strong>
                                </label>
                                <small class="text-muted d-block">Display in latest announcements section</small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Announcement
                        </button>
                        <a href="{{ route('admin.announcements.index') }}" class="btn btn-outline-secondary">
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
                <h6 class="mb-0 fw-bold">Preview</h6>
            </div>
            <div class="card-body">
                <div id="preview" class="border rounded p-3 bg-light">
                    <p class="text-muted text-center">Preview will appear here</p>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Be clear and concise</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Use proper formatting</li>
                    <li class="mb-2"><i class="fas fa-check text-success"></i> Proofread before publishing</li>
                    <li><i class="fas fa-check text-success"></i> All users will be notified</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Simple preview update
    document.getElementById('title').addEventListener('input', updatePreview);
    document.getElementById('content').addEventListener('input', updatePreview);

    function updatePreview() {
        const title = document.getElementById('title').value || 'Announcement Title';
        const content = document.getElementById('content').value || 'Announcement content...';
        document.getElementById('preview').innerHTML = `
            <h5 class="fw-bold mb-2">${title}</h5>
            <p class="text-muted">${content.substring(0, 100)}...</p>
        `;
    }
</script>
@endsection