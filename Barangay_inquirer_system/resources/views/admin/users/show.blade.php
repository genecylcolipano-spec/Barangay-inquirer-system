@extends('admin.layouts.app')

@section('title', 'User Details')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h2">User Details</h1>
        <p class="text-muted">{{ $user->name }}'s profile information</p>
    </div>
    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row">
    <!-- User Information -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                @if($user->profile_photo)
                    <img src="/storage/uploads/profiles/{{ $user->profile_photo }}?t={{ time() }}"
                         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; border: 3px solid #007bff; display: block;"
                         alt="Profile Photo"
                         onerror="this.style.display='none'; document.querySelector('.fallback-icon').style.display='flex';">
                    <div class="fallback-icon" style="display: none; width: 100px; height: 100px; background-color: #e9ecef; border-radius: 50%; margin: 0 auto 20px; align-items: center; justify-content: center;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @else
                    <div style="width: 100px; height: 100px; background-color: #e9ecef; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                @endif
                <h5 class="card-title mb-1">{{ $user->name }}</h5>
                <p class="text-muted mb-3">{{ $user->email }}</p>
                <div class="mb-3">
                    <span class="badge bg-primary">{{ ucfirst($user->role) }}</span>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.users.destroy', $user->id) }}"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirmDelete('{{ route('admin.users.destroy', $user->id) }}')">
                        <i class="fas fa-trash"></i> Delete User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Requests -->
    <div class="col-md-8 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Document Requests ({{ $userRequests->count() }})</h5>
            </div>
            <div class="card-body">
                @if($userRequests->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> This user has not made any document requests.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Document Type</th>
                                    <th>Status</th>
                                    <th>Date Requested</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userRequests as $request)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->document_type)) }}</td>
                                        <td>
                                            @if($request->status == 'pending')
                                                <span class="badge bg-warning text-dark">🟡 Pending</span>
                                            @elseif($request->status == 'processing')
                                                <span class="badge bg-info text-white">🔵 Processing</span>
                                            @elseif($request->status == 'approved')
                                                <span class="badge bg-success">🟢 Approved</span>
                                            @elseif($request->status == 'rejected')
                                                <span class="badge bg-danger">🔴 Rejected</span>
                                            @elseif($request->status == 'completed')
                                                <span class="badge bg-primary">✅ Completed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $request->created_at->format('M d, Y') }}</small></td>
                                        <td>
                                            <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-xs btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- User Statistics -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $userRequests->count() }}</h3>
                        <p class="stat-label">Total Requests</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $userRequests->where('status', 'approved')->count() }}</h3>
                        <p class="stat-label">Approved</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number">{{ $userRequests->where('status', 'pending')->count() }}</h3>
                        <p class="stat-label">Pending</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteForm').submit();
        }
    }

    // Live auto-refresh document requests status every 10 seconds
    setInterval(function() {
        console.log('Starting live refresh...');
        fetch('{{ route("admin.users.show", $user->id) }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            console.log('Received HTML response');
            // Parse the new HTML and extract updated content
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');

            // Update the header count - more specific selector
            const newHeader = newDoc.querySelector('.card-header.bg-light h5.mb-0');
            const currentHeader = document.querySelector('.card-header.bg-light h5.mb-0');
            console.log('Header elements found:', { newHeader: !!newHeader, currentHeader: !!currentHeader });
            if (newHeader && currentHeader) {
                currentHeader.innerHTML = newHeader.innerHTML;
                console.log('Header updated to:', newHeader.innerHTML);
            }

            // Update the request table
            const newTable = newDoc.querySelector('table.table-striped');
            const currentTable = document.querySelector('table.table-striped');
            console.log('Table elements found:', { newTable: !!newTable, currentTable: !!currentTable });
            if (newTable && currentTable) {
                currentTable.innerHTML = newTable.innerHTML;
                console.log('Table updated');
            }

            // Update statistics cards
            const newStatCards = newDoc.querySelectorAll('.stat-number');
            const currentStatCards = document.querySelectorAll('.stat-number');
            console.log('Stat cards found:', { newCount: newStatCards.length, currentCount: currentStatCards.length });
            newStatCards.forEach((newCard, index) => {
                if (currentStatCards[index]) {
                    currentStatCards[index].innerHTML = newCard.innerHTML;
                    console.log(`Stat card ${index} updated to:`, newCard.innerHTML);
                }
            });
        })
        .catch(error => console.log('Auto-refresh error:', error));
    }, 10000); // Refresh every 10 seconds
</script>
@endsection
