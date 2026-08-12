@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="greeting">
            <h1>📄 My Documents</h1>
            <p class="date-time">Manage and download your documents</p>
        </div>
    </div>

    @if($documents->count() > 0)
    <div class="card large">
        <div class="card-header">
            <h2>Your Documents</h2>
            <span class="badge badge-info">{{ $documents->total() }} documents</span>
        </div>
        
        <div class="table-wrapper">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Document Type</th>
                        <th>Status</th>
                        <th>Date Requested</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $document)
                    <tr>
                        <td>
                            <span class="doc-icon">📄</span>
                            <a href="{{ route('resident.document.show', $document) }}" style="color: #667eea; font-weight: 600;">
                                {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                            </a>
                        </td>
                        <td>
                            @php
                                $status = $document->status ?? 'pending';
                                $badgeClass = match($status) {
                                    'approved' => 'badge-approved',
                                    'pending' => 'badge-pending',
                                    'rejected' => 'badge-danger',
                                    'processing' => 'badge-processing',
                                    default => 'badge-info'
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td>{{ $document->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('resident.document.show', $document) }}" class="action-link">View</a>
                            @if($document->file_path)
                            | <a href="{{ route('resident.document.download', $document) }}" class="action-link">Download</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($documents->hasPages())
        <div class="pagination" style="justify-content: center; margin-top: 30px;">
            {{ $documents->links() }}
        </div>
        @endif
    </div>
    @else
    <div class="card large">
        <div style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 4em; margin-bottom: 20px;">📭</div>
            <h2 style="color: #7f8c8d;">No Documents Yet</h2>
            <p style="color: #95a5a6; margin-bottom: 30px;">You haven't requested any documents yet.</p>
            <a href="{{ route('resident.request.create') }}" class="btn btn-primary">Request a Document</a>
        </div>
    </div>
    @endif
</div>

<style>
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

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}
</style>
@endsection
