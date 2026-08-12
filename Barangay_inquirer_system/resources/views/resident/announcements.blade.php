@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📢 Announcements</h1>
            <p class="date-time">Stay updated with barangay news and events</p>
        </div>
    </div>

    @if($announcements->count() > 0)
    <!-- Announcements Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; margin-bottom: 40px;">
        @foreach($announcements as $announcement)
        <div class="card" style="cursor: pointer; position: relative; overflow: hidden;">
            <a href="{{ route('resident.announcement.show', $announcement) }}" style="text-decoration: none; color: inherit; display: block; height: 100%;">
                <!-- Date Badge -->
                <div style="position: absolute; top: 15px; right: 15px;">
                    <span class="badge badge-info">
                        {{ $announcement->created_at->format('M d') }}
                    </span>
                </div>

                <!-- Content -->
                <div style="padding-bottom: 10px;">
                    <h3 style="color: #2c3e50; margin-bottom: 10px; font-size: 1.2em; line-height: 1.4;">
                        {{ $announcement->title }}
                    </h3>
                    
                    <p style="color: #7f8c8d; font-size: 0.95em; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 15px;">
                        {{ $announcement->content }}
                    </p>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #ecf0f5;">
                        <span style="color: #95a5a6; font-size: 0.85em;">
                            {{ $announcement->created_at->diffForHumans() }}
                        </span>
                        <span style="color: #667eea; font-weight: 600;">Read more →</span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($announcements->hasPages())
    <div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
        {{ $announcements->links() }}
    </div>
    @endif
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Announcements Yet</h2>
            <p style="color: #95a5a6;">Check back soon for updates from your barangay.</p>
        </div>
    </div>
    @endif
</div>

<style>
.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.75em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
    transform: translateY(-5px);
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: repeat(auto-fill"] {
        grid-template-columns: 1fr !important;
    }
}

.pagination {
    display: flex;
    gap: 5px;
    justify-content: center;
}

.pagination a,
.pagination span {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #667eea;
}

.pagination a:hover {
    background: #667eea;
    color: white;
}

.pagination .active span {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}
</style>
@endsection
