@extends('admin.layouts.app')

@section('title', isset($announcement) ? 'Edit Announcement' : 'Create Announcement')

@section('content')
<div class="page-header mb-4">
    <h1 class="h2">{{ isset($announcement) ? 'Edit Announcement' : 'Create New Announcement' }}</h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ isset($announcement) ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST">
            @csrf
            @if(isset($announcement))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="title" class="form-label">
                    <strong>Announcement Title</strong>
                </label>
                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" 
                       value="{{ old('title', $announcement->title ?? '') }}" placeholder="Enter announcement title" required>
                @error('title')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="content" class="form-label">
                    <strong>Content</strong>
                </label>
                <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" 
                          rows="8" placeholder="Enter announcement content" required>{{ old('content', $announcement->content ?? '') }}</textarea>
                @error('content')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Maximum 5000 characters</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($announcement) ? 'Update' : 'Create' }} Announcement
                </button>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
