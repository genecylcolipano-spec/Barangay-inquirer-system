@extends('admin.layouts.app')

@section('title', 'Users')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h2">Users</h1>
        <p class="text-muted">Manage system users</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5 class="mb-0">All Users</h5>
            </div>
            <div class="col-md-6">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search users..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-outline-primary">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if($users->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No users found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th style="width: 5%">#</th>
                            <th style="width: 20%">Name</th>
                            <th style="width: 25%">Email</th>
                            <th style="width: 15%">Requests</th>
                            <th style="width: 15%">Joined Date</th>
                            <th style="width: 20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td><small>#{{ $user->id }}</small></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <small>{{ $user->email }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $user->documentRequests->count() ?? 0 }} requests</span>
                                </td>
                                <td>
                                    <small>{{ $user->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button class="btn btn-outline-danger" onclick="confirmDelete('{{ route('admin.users.destroy', $user->id) }}')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    function confirmDelete(url) {
        if (confirm('Are you sure you want to delete this user?')) {
            document.getElementById('deleteForm').action = url;
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endsection
