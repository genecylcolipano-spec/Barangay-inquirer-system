@extends('superadmin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="dashboard-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-chart-pie text-primary"></i> System Overview</h1>
            <p class="text-muted mb-0">Welcome back, <strong>{{ Auth::user()->name }}</strong>! Here's what's happening in your system.</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" onclick="refreshDashboard()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Key Metrics Row -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card metric-card-primary">
            <div class="metric-header">
                <h6 class="metric-label">Total Users</h6>
                <i class="fas fa-users metric-icon"></i>
            </div>
            <div class="metric-value">{{ $totalUsers ?? 0 }}</div>
            <div class="metric-footer">
                <small class="text-success"><i class="fas fa-arrow-up"></i> +{{ $newUsersThisMonth ?? 0 }} this month</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card metric-card-success">
            <div class="metric-header">
                <h6 class="metric-label">Total Admins</h6>
                <i class="fas fa-shield-alt metric-icon"></i>
            </div>
            <div class="metric-value">{{ $totalAdmins ?? 0 }}</div>
            <div class="metric-footer">
                <small class="text-info">{{ $activeAdmins ?? 0 }} active</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card metric-card-warning">
            <div class="metric-header">
                <h6 class="metric-label">Pending Requests</h6>
                <i class="fas fa-hourglass-half metric-icon"></i>
            </div>
            <div class="metric-value">{{ $pendingRequests ?? 0 }}</div>
            <div class="metric-footer">
                <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Needs attention</small>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="metric-card metric-card-danger">
            <div class="metric-header">
                <h6 class="metric-label">System Health</h6>
                <i class="fas fa-heartbeat metric-icon"></i>
            </div>
            <div class="metric-value">{{ $systemHealth ?? '95%' }}</div>
            <div class="metric-footer">
                <small class="text-success">All systems operational</small>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Analytics Row -->
<div class="row mb-4">
    <!-- User Growth Chart -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center border-0">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-line-chart text-primary"></i> User Growth Trend
                </h5>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" id="userTrendDropdown" data-bs-toggle="dropdown">
                        Last 7 Days
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userTrendDropdown">
                        <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                        <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                        <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Request Status Distribution -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-light border-0">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-pie-chart text-success"></i> Request Status Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="requestStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- System Activity and Recent Items Row -->
<div class="row mb-4">
    <!-- System Activity Log -->
    <div class="col-lg-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-history text-info"></i> Recent Activity
                </h5>
                <a href="{{ route('superadmin.activity-logs') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="activity-timeline">
                    @forelse($recentActivities as $act)
                    <div class="activity-item">
                        <div class="activity-time">{{ $act->created_at->diffForHumans() }}</div>
                        <div class="activity-icon bg-primary">
                            <i class="fas fa-bell text-white"></i>
                        </div>
                        <div class="activity-content">
                            <p class="mb-0"><strong>{{ $act->message }}</strong></p>
                            @if($act->user_id)
                                <small class="text-muted">by {{ optional($act->user)->name }}</small>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-4">No recent activity</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-alt text-primary"></i> Request Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Pending</span>
                    <span class="badge bg-warning">{{ $pendingRequests ?? 0 }}</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: 35%"></div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Approved</span>
                    <span class="badge bg-success">{{ $approvedRequests ?? 0 }}</span>
                </div>
                <div class="progress mb-3" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: 60%"></div>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span>Rejected</span>
                    <span class="badge bg-danger">{{ $rejectedRequests ?? 0 }}</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-danger" style="width: 5%"></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-light border-0">
                <h6 class="mb-0 fw-bold"><i class="fas fa-server text-success"></i> System Info</h6>
            </div>
            <div class="card-body">
                <div class="system-info-item">
                    <small class="text-muted">PHP Version</small>
                    <p class="mb-2 fw-bold">{{ phpversion() }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Database</small>
                    <p class="mb-2 fw-bold">MySQL 8.0</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Server Uptime</small>
                    <p class="mb-0 fw-bold">99.7%</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Actions and Latest Users -->
<div class="row">
    <!-- Pending Approvals -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-clock text-warning"></i> Pending Approvals
                </h5>
                <a href="{{ route('superadmin.requests.index', ['status' => 'pending']) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request ID</th>
                                <th>Type</th>
                                <th>Resident</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                                    @forelse($pendingApprovals as $req)
                            <tr>
                                <td>#{{ $req->id }}</td>
                                <td><span class="badge bg-primary">{{ ucfirst($req->document_type) }}</span></td>
                                <td>{{ $req->resident_name ?? $req->user->name ?? 'N/A' }}</td>
                                <td>{{ $req->created_at->diffForHumans() }}</td>
                                <td>
                                    <button class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No pending approvals</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Users -->
    <div class="col-lg-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light border-0 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user-plus text-success"></i> Latest Registered Users
                </h5>
                <a href="{{ route('superadmin.users.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latestUsers as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge bg-secondary">{{ ucfirst($user->role) }}</span></td>
                                <td>{{ $user->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No users found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@php
    // generate last 7 day labels for charts
    $labels = [];
    for ($i = 6; $i >= 0; $i--) {
        $labels[] = now()->subDays($i)->format('D');
    }
@endphp
<script>
    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const growthLabels = {!! json_encode($labels ?? []) !!};
    const growthData = {!! json_encode($userGrowthData ?? []) !!};
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: growthLabels.length ? growthLabels : ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
            datasets: [{
                label: 'New Users',
                data: growthData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Request Status Chart
    const requestStatusCtx = document.getElementById('requestStatusChart').getContext('2d');
    const statusData = {!! json_encode([$requestStatusData['approved'] ?? 0, $requestStatusData['pending'] ?? 0, $requestStatusData['rejected'] ?? 0]) !!};
    new Chart(requestStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: statusData,
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
                ],
                borderColor: '#fff',
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

    // Refresh dashboard function
    function refreshDashboard() {
        const btn = event.target.closest('button');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
</script>
@endsection
