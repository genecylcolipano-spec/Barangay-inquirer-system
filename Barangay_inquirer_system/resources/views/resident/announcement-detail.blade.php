@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Back Button -->
    <a href="{{ route('resident.announcements') }}" style="color: #667eea; font-weight: 600; margin-bottom: 20px; display: inline-block; text-decoration: none;">
        ← Back to Announcements
    </a>

    <div id="apiFeedback" class="alert d-none" role="alert" style="margin-bottom: 20px; display: none;">
        <span id="apiFeedbackText"></span>
        <button id="apiRetryButton" type="button" class="btn btn-sm btn-light ms-3" style="display: none;">Retry</button>
    </div>

    <div class="card large" style="margin-top: 20px;">
        <div class="card-header">
            <div>
                <h2>{{ $announcement->title }}</h2>
                <p style="color: #7f8c8d; margin-top: 5px;">
                    {{ $announcement->created_at->format('F d, Y') }} 
                    • {{ $announcement->created_at->diffForHumans() }}
                </p>
            </div>
        </div>

        <!-- Announcement Content -->
        <div style="color: #2c3e50; line-height: 1.8; font-size: 1.05em; margin-bottom: 30px;">
            {!! nl2br($announcement->content) !!}
        </div>

        <!-- Footer -->
        <div style="border-top: 1px solid #ecf0f5; padding-top: 20px; display: flex; justify-content: space-between; align-items: center;">
            <p style="color: #7f8c8d; font-size: 0.9em; margin: 0;">
                📧 For more information, contact the Barangay Office
            </p>
            <a href="{{ route('resident.announcements') }}" style="padding: 10px 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.3s ease;">
                View All Announcements
            </a>
        </div>
    </div>
</div>

<style>
.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f3f7;
}

.card-header h2 {
    font-size: 1.8em;
    color: #2c3e50;
    margin: 0;
}

.card.large {
    grid-column: 1 / -1;
}
</style>
@endsection
