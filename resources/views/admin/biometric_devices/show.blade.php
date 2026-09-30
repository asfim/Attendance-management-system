@extends('layouts.app')

@section('title', 'ZKTeco Device Details - ' . $device->name)

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-cpu-fill text-primary me-2"></i>{{ $device->name }}</h3>
            <p class="text-muted mb-0">Device Configuration, Health Monitoring & Audit Logs.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.biometric-devices.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Devices List
            </a>
            <button type="button" class="btn btn-outline-info rounded-pill px-3" id="btn-test-conn">
                <i class="bi bi-lightning me-1"></i> Test Connection
            </button>
            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm" id="btn-sync-dev">
                <i class="bi bi-arrow-repeat me-1"></i> Sync Attendance Now
            </button>
        </div>
    </div>

    <div id="ajax-alert" class="alert d-none rounded-4 mb-4" role="alert"></div>

    <!-- Info Overview Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold mb-3 text-body"><i class="bi bi-info-circle me-2 text-primary"></i>Device Information & Status</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Device Name</span>
                        <p class="fw-bold fs-6 mb-0">{{ $device->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">IP Address & Port</span>
                        <p class="mb-0"><code class="bg-dark text-info px-2 py-1 rounded fs-6">{{ $device->ip_address }}:{{ $device->port }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Serial Number</span>
                        <p class="fw-semibold text-body mb-0">{{ $device->serial_number ?? 'Not Set' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Location / Branch</span>
                        <p class="fw-semibold text-body mb-0">{{ $device->location ?? 'Main Campus' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Connection Key</span>
                        <p class="fw-semibold text-body mb-0"><code>{{ $device->comm_key ?? '0' }}</code></p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Active Status</span>
                        <p class="mb-0">
                            @if($device->is_active)
                                <span class="badge bg-success rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary rounded-pill px-3 py-1">Disabled</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Health Status</span>
                        <p class="mb-0" id="health-badge-wrapper">
                            @if($device->status === 'online')
                                <span class="badge bg-success rounded-pill px-3 py-1"><i class="bi bi-wifi me-1"></i>Online</span>
                            @elseif($device->status === 'syncing')
                                <span class="badge bg-info rounded-pill px-3 py-1"><i class="bi bi-arrow-repeat me-1"></i>Syncing</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 py-1"><i class="bi bi-wifi-off me-1"></i>Offline</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Last Connected</span>
                        <p class="fw-semibold text-body mb-0">{{ $device->last_connected_at ? $device->last_connected_at->diffForHumans() : 'Never' }}</p>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small text-uppercase">Last Sync Time</span>
                        <p class="fw-semibold text-body mb-0" id="last-sync-val">{{ $device->last_sync_at ? $device->last_sync_at->format('d M Y, h:i A') : 'Never' }}</p>
                    </div>
                    @if($device->last_error)
                    <div class="col-12">
                        <span class="text-danger small text-uppercase fw-bold">Last Error Log</span>
                        <div class="alert alert-danger mb-0 p-2 small mt-1">{{ $device->last_error }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold mb-3 text-body"><i class="bi bi-gear-wide-connected me-2 text-primary"></i>Device Actions</h5>
                    <p class="text-muted small mb-4">Perform direct hardware synchronization and memory management.</p>
                    
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary rounded-pill py-2" id="btn-sync-users">
                            <i class="bi bi-people me-2"></i> Sync Users with Machine
                        </button>
                        <button type="button" class="btn btn-outline-danger rounded-pill py-2" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="bi bi-trash3 me-2"></i> Clear Device Memory Logs
                        </button>
                    </div>
                </div>

                <div class="bg-light p-3 rounded-4 mt-3">
                    <span class="small fw-bold text-muted d-block mb-1">LAN Connection Info</span>
                    <span class="small text-muted d-block">Protocol: ZKTeco UDP Socket</span>
                    <span class="small text-muted d-block">Port: {{ $device->port }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Recent Logs Table -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-body py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="bi bi-journal-text me-2 text-info"></i>Recent Device Punch Logs</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Punch Time</th>
                        <th>Device User ID</th>
                        <th>User Name</th>
                        <th>State</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($device->logs as $log)
                    <tr>
                        <td class="ps-4 fw-semibold small">{{ $log->punch_time ? $log->punch_time->format('d M Y, h:i:s A') : '-' }}</td>
                        <td><code>{{ $log->biometric_id }}</code></td>
                        <td class="fw-bold">{{ $log->user_name ?? 'Unmapped' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ strtoupper($log->punch_state ?? 'CHECK_IN') }}</span></td>
                        <td>
                            @if($log->status === 'success')
                                <span class="badge bg-success rounded-pill px-3 py-1">Success</span>
                            @elseif($log->status === 'unmapped')
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Unmapped</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 py-1">Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No punch logs recorded for this device yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Clear Device Memory Confirmation Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirm Clear Device Memory</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="fw-semibold text-dark">Are you sure you want to clear all attendance logs stored inside the ZKTeco machine memory?</p>
                <div class="alert alert-warning mb-0 small">
                    <i class="bi bi-shield-exclamation me-1"></i> Make sure you have synchronized all attendance data to Laravel before clearing the device memory.
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3 rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger rounded-pill px-4 shadow-sm" id="btn-confirm-clear">Yes, Clear Device Memory</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.getElementById('ajax-alert');

    function showAlert(type, msg) {
        alertBox.className = `alert alert-${type} alert-dismissible fade show rounded-4 mb-4`;
        alertBox.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        alertBox.classList.remove('d-none');
    }

    // Ping / Test Connection
    document.getElementById('btn-test-conn')?.addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';

        fetch("{{ route('biometric-devices.test', $device->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            showAlert(data.success ? 'success' : 'danger', data.message);
        })
        .catch(err => showAlert('danger', 'Error testing connection: ' + err.message))
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-lightning me-1"></i> Test Connection';
        });
    });

    // Sync Device Attendance Now
    document.getElementById('btn-sync-dev')?.addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

        fetch("{{ route('biometric-devices.sync', $device->id) }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            showAlert(data.success ? 'success' : 'danger', data.message);
            if (data.success) {
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(err => showAlert('danger', 'Error syncing device: ' + err.message))
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sync Attendance Now';
        });
    });

    // Clear Device Logs
    document.getElementById('btn-confirm-clear')?.addEventListener('click', function () {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Clearing...';

        fetch("/admin/biometric-devices/{{ $device->id }}/clear-logs", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            showAlert(data.success ? 'success' : 'danger', data.message);
            const modalEl = document.getElementById('clearLogsModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        })
        .catch(err => showAlert('danger', 'Error clearing device memory: ' + err.message))
        .finally(() => {
            this.disabled = false;
            this.innerHTML = 'Yes, Clear Device Memory';
        });
    });
});
</script>
@endsection
