@extends('layouts.app')

@section('title', 'ZKTeco Device Sync History Logs')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Row -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-clock-history text-primary me-2"></i>Biometric Sync History</h3>
            <p class="text-muted mb-0">Audit history of automatic scheduled and manual ZKTeco device synchronizations.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.biometric-devices.index') }}" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-router-fill me-1"></i> Device Manager
            </a>
            <a href="{{ route('admin.attendance.biometric-logs') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-journal-text me-1"></i> View Punch Logs
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <form method="GET" action="{{ route('admin.device-sync-logs') }}" class="row g-3 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Filter by Device</label>
                <select name="device_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Registered Devices</option>
                    @foreach($devices as $dev)
                        <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>{{ $dev->name }} ({{ $dev->ip_address }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Filter by Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Sync Statuses</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="running" {{ request('status') == 'running' ? 'selected' : '' }}>Running</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4">Filter</button>
                @if(request('device_id') || request('status'))
                    <a href="{{ route('admin.device-sync-logs') }}" class="btn btn-outline-danger rounded-pill px-3"><i class="bi bi-x-circle"></i> Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sync Logs Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Log #</th>
                        <th>Device Name</th>
                        <th>Type</th>
                        <th>Started At</th>
                        <th>Completed At</th>
                        <th>Records (Total / New / Duplicate / Unmapped / Failed)</th>
                        <th>Status</th>
                        <th class="pe-4">Error Message</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($syncLogs as $log)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $log->id }}</td>
                        <td>
                            <span class="fw-bold text-body">{{ $log->device->name ?? 'Unknown Device' }}</span>
                            <div class="text-muted small">{{ $log->device->ip_address ?? '' }}</div>
                        </td>
                        <td>
                            @if($log->sync_type === 'scheduled')
                                <span class="badge bg-purple text-dark border px-2 py-1"><i class="bi bi-clock me-1"></i>Scheduled</span>
                            @else
                                <span class="badge bg-info text-dark px-2 py-1"><i class="bi bi-hand-index me-1"></i>Manual</span>
                            @endif
                        </td>
                        <td class="small">{{ $log->started_at ? $log->started_at->format('d M Y, h:i:s A') : '-' }}</td>
                        <td class="small">{{ $log->completed_at ? $log->completed_at->format('d M Y, h:i:s A') : '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border me-1">Total: {{ $log->total_records }}</span>
                            <span class="badge bg-success bg-opacity-10 text-success me-1">New: {{ $log->new_records }}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary me-1">Dup: {{ $log->duplicate_records }}</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning me-1">Unmapped: {{ $log->unmapped_records }}</span>
                            @if($log->failed_records > 0)
                                <span class="badge bg-danger text-white me-1">Fail: {{ $log->failed_records }}</span>
                            @endif
                        </td>
                        <td>
                            @if($log->status === 'completed')
                                <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-check-circle me-1"></i>Completed</span>
                            @elseif($log->status === 'failed')
                                <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-x-circle me-1"></i>Failed</span>
                            @else
                                <span class="badge bg-info rounded-pill px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i>Running</span>
                            @endif
                        </td>
                        <td class="pe-4 small text-danger">
                            {{ $log->error_message ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block text-secondary mb-2"></i>
                            <h5 class="fw-bold">No Device Sync History Found</h5>
                            <p class="mb-0">Sync logs will automatically record every time a ZKTeco device synchronizes data.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body py-3">
            {{ $syncLogs->links() }}
        </div>
    </div>
</div>
@endsection
