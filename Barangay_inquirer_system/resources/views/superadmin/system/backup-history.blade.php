@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Backup History</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="mb-3">
        <a href="{{ route('superadmin.settings') }}" class="btn btn-secondary">Back to Settings</a>
    </div>

    @if(count($backups) === 0)
        <div class="alert alert-info">No backups found yet. Create one from the backup settings tab.</div>
    @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Type</th>
                    <th>Created</th>
                    <th>Size</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($backups as $backup)
                    @php
                        $extension = strtolower(pathinfo($backup['name'], PATHINFO_EXTENSION));
                        $type = match($extension) {
                            'zip' => 'Backup Archive',
                            'sql' => 'SQL Dump',
                            'txt' => 'Metadata',
                            default => strtoupper($extension) . ' File',
                        };
                        $canRestore = in_array($extension, ['zip', 'sql'], true);
                        $canDownload = in_array($extension, ['zip', 'sql'], true);
                    @endphp
                    <tr>
                        <td>{{ $backup['name'] }}</td>
                        <td>{{ $type }}</td>
                        <td>{{ date('Y-m-d H:i:s', $backup['updated_at']) }}</td>
                        <td>{{ number_format($backup['size'] / 1024, 2) }} KB</td>
                        <td>
                            @if($canDownload)
                                <a href="{{ route('superadmin.settings.backup.history', ['file' => $backup['name']]) }}" class="btn btn-sm btn-outline-success">Download</a>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled>No Download</button>
                            @endif

                            @if($canRestore)
                                <form action="{{ route('superadmin.settings.backup.restore') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="backup_file" value="{{ $backup['name'] }}">
                                    <button type="submit" class="btn btn-sm btn-outline-warning ms-1">Restore</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary ms-1" disabled>Metadata Only</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection