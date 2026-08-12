@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📋 My Requests</h1>
            <p class="date-time">Track all your document requests</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('resident.request.create') }}" class="btn-primary">+ New Request</a>
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
                                <a href="{{ route('resident.request.show', $request) }}" class="action-link" style="padding: 6px 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">View Details</a>
                                @if($request->status === 'completed' && $request->file_path)
                                <a href="{{ route('resident.document.download', $request) }}" class="action-link" style="padding: 6px 12px; background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em;">Download</a>
                                @endif
                                @if($request->status === 'pending')
                                <form action="{{ route('resident.request.destroy', $request) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-link" onclick="return confirm('Are you sure? This will permanently delete the request.')" style="padding: 6px 12px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border-radius: 4px; text-decoration: none; font-weight: 600; font-size: 0.85em; border: none; cursor: pointer;">Delete</button>
                                </form>
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

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
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

.action-link:hover {
    color: #764ba2;
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

/* Status Badge Styles */
.badge {
    font-size: 0.85em;
    font-weight: 600;
}

.badge-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 1px solid #fcd34d;
}

.badge-processing {
    background: linear-gradient(135deg, #bfdbfe 0%, #93c5fd 100%);
    color: #1e40af;
    border: 1px solid #60a5fa;
}

.badge-approved {
    background: linear-gradient(135deg, #bbf7d0 0%, #86efac 100%);
    color: #166534;
    border: 1px solid #4ade80;
}

.badge-danger {
    background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
    color: #991b1b;
    border: 1px solid #f87171;
}

.badge-completed {
    background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%);
    color: #1f2937;
    border: 1px solid #6b7280;
}

.badge-info {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border: 1px solid #60a5fa;
}

@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .greeting h1 {
        font-size: 1.8em;
    }

    .requests-table {
        font-size: 0.9em;
    }

    .requests-table td,
    .requests-table th {
        padding: 12px 8px;
    }
}
</style>
@endsection
