@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="page-header mb-4">
    <h1 class="h2">Dashboard</h1>
    <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalRequests }}</h3>
                <p class="stat-label">Total Requests</p>
                <small class="stat-change">+{{ $thisMonthRequests }} this month</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $pendingRequests }}</h3>
                <p class="stat-label">Pending Requests</p>
                <small class="stat-change">Awaiting review</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalUsers }}</h3>
                <p class="stat-label">Total Users</p>
                <small class="stat-change">+{{ $thisMonthUsers }} this month</small>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">{{ $totalAnnouncements }}</h3>
                <p class="stat-label">Announcements</p>
                <small class="stat-change">Active posts</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row mb-4">
    <!-- Requests by Status Chart -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Requests by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Requests by Type Chart -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Requests by Type</h5>
            </div>
            <div class="card-body">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Pending Requests Table -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pending Requests</h5>
                <a href="{{ route('admin.requests.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($pendingRequestsList->isEmpty())
                    <p class="text-muted p-3 mb-0">No pending requests</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Document Type</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingRequestsList as $request)
                                    <tr>
                                        <td>
                                            <small>{{ $request->user->name ?? 'Unknown' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ ucfirst($request->document_type) }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $request->created_at->format('M d, Y') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Announcements -->
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Announcements</h5>
                <a href="{{ route('admin.announcements.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentAnnouncements->isEmpty())
                    <p class="text-muted p-3 mb-0">No announcements yet</p>
                @else
                    <div class="list-group list-group-flush">
                        @foreach($recentAnnouncements as $announcement)
                            <div class="list-group-item py-3">
                                <h6 class="mb-1">{{ $announcement->title }}</h6>
                                <p class="text-muted small mb-2">{{ Str::limit($announcement->content, 100) }}</p>
                                <small class="text-secondary">{{ $announcement->created_at->format('M d, Y') }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Request Status Summary -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Request Status Summary</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="status-box">
                            <div class="status-count text-primary">{{ $approvedRequests }}</div>
                            <div class="status-label">Approved</div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-success" style="width: {{ ($approvedRequests / $totalRequests * 100) ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-box">
                            <div class="status-count text-warning">{{ $pendingRequests }}</div>
                            <div class="status-label">Pending</div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-warning" style="width: {{ ($pendingRequests / $totalRequests * 100) ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-box">
                            <div class="status-count text-danger">{{ $rejectedRequests }}</div>
                            <div class="status-label">Rejected</div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-danger" style="width: {{ ($rejectedRequests / $totalRequests * 100) ?? 0 }}%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-box">
                            <div class="status-count text-info">{{ $totalRequests }}</div>
                            <div class="status-label">Total</div>
                            <div class="progress mt-2" style="height: 4px;">
                                <div class="progress-bar bg-info" style="width: 100%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [{{ $requestsByStatus['approved'] }}, {{ $requestsByStatus['pending'] }}, {{ $requestsByStatus['rejected'] }}],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                borderColor: ['#fff', '#fff', '#fff'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Type Chart
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    const typeLabels = {!! json_encode($requestsByType->pluck('document_type')->toArray()) !!};
    const typeCounts = {!! json_encode($requestsByType->pluck('count')->toArray()) !!};
    
    new Chart(typeCtx, {
        type: 'bar',
        data: {
            labels: typeLabels,
            datasets: [{
                label: 'Number of Requests',
                data: typeCounts,
                backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                borderColor: ['#0056b3', '#1e7e34', '#e0a800', '#bd2130', '#0c5460'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Live auto-refresh dashboard statistics every 10 seconds
    setInterval(function() {
        console.log('Starting dashboard live refresh...');
        fetch('{{ route("admin.dashboard") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            console.log('Received dashboard HTML response');
            // Parse the new HTML and extract updated content
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Update statistics cards (.stat-number)
            const newStatCards = newDoc.querySelectorAll('.stat-number');
            const currentStatCards = document.querySelectorAll('.stat-number');
            console.log('Dashboard stat cards found:', { newCount: newStatCards.length, currentCount: currentStatCards.length });
            newStatCards.forEach((newCard, index) => {
                if (currentStatCards[index]) {
                    currentStatCards[index].innerHTML = newCard.innerHTML;
                    console.log(`Dashboard stat card ${index} updated to:`, newCard.innerHTML);
                }
            });

            // Update status summary counts (.status-count)
            const newStatusCounts = newDoc.querySelectorAll('.status-count');
            const currentStatusCounts = document.querySelectorAll('.status-count');
            console.log('Dashboard status counts found:', { newCount: newStatusCounts.length, currentCount: currentStatusCounts.length });
            newStatusCounts.forEach((newCount, index) => {
                if (currentStatusCounts[index]) {
                    currentStatusCounts[index].innerHTML = newCount.innerHTML;
                    console.log(`Dashboard status count ${index} updated to:`, newCount.innerHTML);
                }
            });

            // Update progress bars
            const newProgressBars = newDoc.querySelectorAll('.progress-bar');
            const currentProgressBars = document.querySelectorAll('.progress-bar');
            console.log('Dashboard progress bars found:', { newCount: newProgressBars.length, currentCount: currentProgressBars.length });
            newProgressBars.forEach((newBar, index) => {
                if (currentProgressBars[index]) {
                    const newWidth = newBar.style.width;
                    currentProgressBars[index].style.width = newWidth;
                    console.log(`Dashboard progress bar ${index} width updated to:`, newWidth);
                }
            });
        })
        .catch(error => console.log('Dashboard auto-refresh error:', error));
    }, 10000); // Refresh every 10 seconds
</script>
@endsection
