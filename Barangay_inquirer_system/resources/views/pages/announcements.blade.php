@extends('layouts.app')

@section('content')

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <a href="{{ url('/') }}" class="btn btn-outline-secondary mb-3">
                <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_home') }}
            </a>
            <h2 class="mb-3 display-4">{{ __('messages.announcements') }}</h2>
            <p class="lead text-muted">{{ __('messages.announcements_subtitle') }}</p>
        </div>
    </div>

    @if($announcements->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> {{ __('messages.no_announcements') }}
        </div>
    @else
        <div class="row">
            @foreach($announcements as $announcement)
                <div class="col-md-12 mb-4">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h3 class="card-title h5">{{ $announcement->title }}</h3>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        {{ $announcement->created_at->format('M d, Y H:i A') }}
                                    </small>
                                </div>
                            </div>

                            @if(strlen($announcement->content) > 300)
                                <p class="card-text text-dark preview-text" data-announcement-id="{{ $announcement->id }}">
                                    {{ Str::limit($announcement->content, 300) }}
                                </p>

                                <a href="#announcement-{{ $announcement->id }}" class="btn btn-sm btn-primary" data-toggle="collapse">
                                    {{ __('messages.read_more') }} <i class="fas fa-chevron-down"></i>
                                </a>

                                <div class="collapse mt-3" id="announcement-{{ $announcement->id }}">
                                    <div class="card card-body border-0 bg-light">
                                        {!! nl2br($announcement->content) !!}
                                    </div>
                                </div>
                            @else
                                <p class="card-text text-dark">
                                    {!! nl2br($announcement->content) !!}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            {{ $announcements->links() }}
        </div>
    @endif
</div>

<style>
    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }

    .card-title {
        color: #2c3e50;
        font-weight: 600;
    }

    .text-muted {
        color: #7f8c8d !important;
    }

    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    /* Hide preview text when collapse is expanded */
    .preview-text {
        display: block;
    }

    .collapse.show + .preview-text {
        display: none;
    }
</style>

@endsection