@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<section class="page-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="page-header text-center mb-5">
                    <h1 class="h2 fw-bold">Privacy Policy</h1>
                    <p class="text-muted">Last updated: {{ date('F j, Y') }}</p>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <div class="content">
                            {!! nl2br(e($content)) !!}
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.page-section {
    padding: 80px 0;
    background: #f8f9fa;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.content {
    line-height: 1.7;
    color: #555;
}

.content h2, .content h3, .content h4 {
    color: #2c3e50;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.content p {
    margin-bottom: 1rem;
}

.content ul, .content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.card {
    border: none;
    border-radius: 10px;
}

.btn-outline-primary {
    border-color: #667eea;
    color: #667eea;
}

.btn-outline-primary:hover {
    background-color: #667eea;
    border-color: #667eea;
}
</style>
@endsection