@extends('resident.layout')

@section('content')
<div class="dashboard">
    <!-- Back Button -->
    <a href="{{ route('resident.documents') }}" style="color: #667eea; font-weight: 600; margin-bottom: 20px; display: inline-block; text-decoration: none;">
        ← Back to Documents
    </a>

    <div class="card large" style="margin-top: 20px;">
        <div class="card-header">
            <div>
                <h2>📄 {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</h2>
                <p style="color: #7f8c8d; margin-top: 5px;">Document Details</p>
            </div>
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
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
            <!-- Info Section -->
            <div>
                <h3 style="color: #2c3e50; margin-bottom: 20px; font-size: 1.1em;">Information</h3>
                
                <div style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em;">Document Type</label>
                    <p style="color: #2c3e50; font-size: 1.05em; margin-top: 5px;">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em;">Status</label>
                    <p style="color: #2c3e50; font-size: 1.05em; margin-top: 5px;">{{ ucfirst($status) }}</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em;">Requested Date</label>
                    <p style="color: #2c3e50; font-size: 1.05em; margin-top: 5px;">{{ $document->created_at->format('F d, Y') }}</p>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="color: #7f8c8d; font-weight: 600; font-size: 0.85em;">Last Updated</label>
                    <p style="color: #2c3e50; font-size: 1.05em; margin-top: 5px;">{{ $document->updated_at->format('F d, Y H:i') }}</p>
                </div>
            </div>

            <!-- Action Section -->
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <h3 style="color: #2c3e50; margin-bottom: 5px; font-size: 1.1em;">Actions</h3>

                @if($document->file_path)
                <a href="{{ route('resident.document.download', $document) }}" style="padding: 12px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; text-align: center; font-weight: 600; transition: all 0.3s ease;">
                    📥 Download Document
                </a>
                @else
                <div style="padding: 12px; background: #ecf0f5; color: #7f8c8d; text-align: center; border-radius: 8px; font-weight: 600;">
                    No file available yet
                </div>
                @endif

                <a href="{{ route('resident.documents') }}" style="padding: 12px; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); color: #2c3e50; text-decoration: none; border-radius: 8px; text-align: center; font-weight: 600; transition: all 0.3s ease;">
                    ← Back to List
                </a>
            </div>
        </div>

        <!-- Notes Section -->
        @if($document->notes)
        <div style="border-top: 1px solid #ecf0f5; padding-top: 30px;">
            <h3 style="color: #2c3e50; margin-bottom: 15px;">📝 Notes</h3>
            <div style="background: #f8f9fc; padding: 20px; border-radius: 8px; border-left: 4px solid #667eea;">
                <p style="color: #2c3e50; line-height: 1.6;">{{ $document->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
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

.badge-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

@media (max-width: 768px) {
    div[style*="grid-template-columns: 1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endsection
