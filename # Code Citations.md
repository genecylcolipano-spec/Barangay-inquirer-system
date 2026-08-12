# Code Citations

## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```


## License: MIT
https://github.com/dudeteam/dude-panel/blob/94b0a7f74ae7b102a3ce5f5f4c2c31afbd40dc8e/dude-panel.html

```
I'll help you implement a clean error handler for your Laravel Blade view. Here's a solution that intercepts API errors and displays user-friendly messages with a countdown timer for 429 errors:

````blade
// filepath: c:\Barangay_inquirer_system\Barangay_inquirer_system\resources\views\resident\my-requests.blade.php
@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Error Alert Component -->
    <div id="errorAlert" class="error-alert hidden" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="error-alert-content">
            <div class="error-alert-icon" id="errorIcon">⚠️</div>
            <div class="error-alert-message">
                <div class="error-alert-title" id="errorTitle">Error</div>
                <div class="error-alert-text" id="errorText"></div>
                <div class="error-alert-timer" id="errorTimer" style="display: none; margin-top: 8px; font-size: 0.9em; opacity: 0.9;">
                    Retry available in <span id="countdownValue">0</span>s
                </div>
            </div>
            <button id="errorClose" class="error-alert-close" aria-label="Close error message">✕</button>
        </div>
    </div>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary" id="newRequestBtn">+ New Request</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background: #e0ffe0; border: 1px solid #b3ffb3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <p style="color: #27ae60; font-weight: 600; margin: 0;">✓ {{ session('success') }}</p>
    </div>
    @endif

    @if($requests->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Requests</h2>
            <span class="badge badge-info">{{ $requests->total() }} requests</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Requested On</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $request)
                    <tr>
                        <td>
                            <span class="doc-icon">📋</span>
                            {{ ucfirst(str_replace('_', ' ', $request->document_type)) }}
                        </td>
                        <td>
                            @php
                                $status = $request->status ?? 'pending';
                                $statusConfig = match($status) {
                                    'pending' => [
                                        'emoji' => '🟡',
                                        'label' => 'Pending',
                                        'description' => 'waiting for review',
                                        'class' => 'badge-pending'
                                    ],
                                    'processing' => [
                                        'emoji' => '🔵',
                                        'label' => 'Processing',
                                        'description' => 'being prepared',
                                        'class' => 'badge-processing'
                                    ],
                                    'approved' => [
                                        'emoji' => '🟢',
                                        'label' => 'Approved',
                                        'description' => 'ready for pickup',
                                        'class' => 'badge-approved'
                                    ],
                                    'rejected' => [
                                        'emoji' => '🔴',
                                        'label' => 'Rejected',
                                        'description' => 'see notes',
                                        'class' => 'badge-danger'
                                    ],
                                    'completed' => [
                                        'emoji' => '✅',
                                        'label' => 'Completed',
                                        'description' => 'document obtained',
                                        'class' => 'badge-completed'
                                    ],
                                    default => [
                                        'emoji' => 'ℹ️',
                                        'label' => ucfirst($status),
                                        'description' => 'in progress',
                                        'class' => 'badge-info'
                                    ]
                                };
                            @endphp
                            <div class="badge {{ $statusConfig['class'] }}" style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 6px; width: fit-content;">
                                <span style="font-size: 1.1em;">{{ $statusConfig['emoji'] }}</span>
                                <div style="text-align: left;">
                                    <div style="font-weight: 600; font-size: 0.9em;">{{ $statusConfig['label'] }}</div>
                                    <div style="font-size: 0.75em; opacity: 0.8;">{{ $statusConfig['description'] }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>{{ $request->updated_at->format('M d, Y H:i') }}</td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link api-trigger" data-action="view" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link api-trigger" data-action="download" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <button type="button" class="action-link api-trigger" data-action="delete" data-request-id="{{ $request->id }}" onclick="handleDelete(event)" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Requests Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't submitted any document requests yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Submit Your First Request</a>
        </div>
    </div>
    @endif
</div>

<style>
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    flex-wrap: wrap;
    gap: 20px;
}

.greeting h1 {
    font-size: 2.5em;
    color: #2c3e50;
    margin-bottom: 8px;
    font-weight: 600;
}

.date-time {
    color: #7f8c8d;
    font-size: 0.95em;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.95em;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    text-decoration: none;
    display: inline-block;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn {
    border: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
    padding: 12px 28px;
    font-size: 0.95em;
    text-decoration: none;
    display: inline-block;
}

.card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
}

.card.large {
    grid-column: 1 / -1;
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
    font-size: 1.4em;
    color: #2c3e50;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
}

.requests-table {
    width: 100%;
    border-collapse: collapse;
}

.requests-table thead {
    background: #f8f9fc;
    border-bottom: 2px solid #e8ecf1;
}

.requests-table th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #7f8c8d;
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.requests-table tbody tr {
    border-bottom: 1px solid #ecf0f5;
    transition: background 0.3s ease;
}

.requests-table tbody tr:hover {
    background: #f8f9fc;
}

.requests-table td {
    padding: 16px 15px;
    color: #2c3e50;
}

.doc-icon {
    margin-right: 8px;
    font-size: 1.2em;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-approved {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-pending {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    color: white;
}

.badge-processing {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.badge-danger {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.action-link {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s ease;
}

.action-link:hover:not(:disabled) {
    color: #764ba2;
}

.action-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
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

/* Error Alert Styles */
.error-alert {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

.error-alert.hidden {
    display: none;
}

.error-alert-content {
    background: white;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    border-left: 4px solid #ef4444;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.error-alert-content.warning {
    border-left-color: #f59e0b;
}

.error-alert-content.success {
    border-left-color: #10b981;
}

.error-alert-content.info {
    border-left-color: #3b82f6;
}

.error-alert-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    min-width: 24px;
}

.error-alert-message {
    flex: 1;
}

.error-alert-title {
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
    font-size: 0.95em;
}

.error-alert-text {
    color: #4b5563;
    font-size: 0.9em;
    line-height: 1.4;
}

.error-alert-close {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 1.2em;
    cursor: pointer;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: color 0.2s ease;
}

.error-alert-close:hover {
    color: #1f2937;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.error
```

