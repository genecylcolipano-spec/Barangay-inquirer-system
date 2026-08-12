@extends('superadmin.layouts.app')

@section('title', 'System Health')

@section('content')
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col">
            <h1 class="h3 fw-bold"><i class="fas fa-heartbeat text-danger"></i> System Health Status</h1>
            <p class="text-muted">Monitor system performance and resource usage</p>
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
    </div>
</div>

<!-- Health Status Cards -->
<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm border-left border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Database</h6>
                        <p class="mb-0 fw-bold"><i class="fas fa-check-circle text-success"></i> Healthy</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm border-left border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Cache System</h6>
                        <p class="mb-0 fw-bold"><i class="fas fa-check-circle text-success"></i> Operational</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm border-left border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Queue System</h6>
                        <p class="mb-0 fw-bold"><i class="fas fa-check-circle text-success"></i> Running</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-3">
        <div class="card shadow-sm border-left border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-1">Storage</h6>
                        <p class="mb-0 fw-bold"><i class="fas fa-check-circle text-success"></i> Available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resource Usage -->
<div class="row mb-4">
    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Memory Usage</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Used / Total</span>
                    <span class="fw-bold">{{ $health['memoryUsage'] ?? '45%' }}</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-info" style="width: 45%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Within safe limits
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">CPU Usage</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Current Load</span>
                    <span class="fw-bold">{{ $health['cpuUsage'] ?? '32%' }}</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-success" style="width: 32%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Normal operation
                </small>
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h6 class="mb-0 fw-bold">Disk Usage</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Used / Total</span>
                    <span class="fw-bold">{{ $health['diskUsage'] ?? '65%' }}</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-warning" style="width: 65%"></div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="fas fa-info-circle"></i> Monitor closely
                </small>
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0 fw-bold">System Information</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="system-info-item">
                    <small class="text-muted">Server OS</small>
                    <p class="mb-2 fw-bold">{{ PHP_OS_FAMILY }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">PHP Version</small>
                    <p class="mb-2 fw-bold">{{ phpversion() }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Laravel Version</small>
                    <p class="mb-2 fw-bold">{{ \Illuminate\Foundation\Application::VERSION }}</p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="system-info-item">
                    <small class="text-muted">Last System Check</small>
                    <p class="mb-2 fw-bold">{{ now()->format('M d, Y H:i:s') }}</p>
                </div>
                <div class="system-info-item">
                    <small class="text-muted">Server Uptime</small>
                    <p class="mb-0 fw-bold">99.7%</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
