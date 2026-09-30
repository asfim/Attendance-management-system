@extends('layouts.app')

@section('title', 'ZKTeco Biometric Device Manager')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header Row -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-router-fill text-primary me-2"></i>ZKTeco Biometric Device Manager</h3>
            <p class="text-muted mb-0">Manage multiple ZKTeco K40/F18 devices across branches, main gates, and offices.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" id="btn-sync-all">
                <span class="spinner-border spinner-border-sm me-2 d-none" id="sync-all-spinner" role="status"></span>
                <i class="bi bi-arrow-repeat me-1" id="sync-all-icon"></i> Sync All Devices
            </button>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                <i class="bi bi-plus-lg me-1"></i> Add New Device
            </button>
            <a href="{{ route('admin.attendance.biometric-logs') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-journal-text me-1"></i> View Punch Logs
            </a>
        </div>
    </div>

    <!-- Global Alert Box -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div id="ajax-global-alert" class="alert d-none rounded-4 mb-4" role="alert"></div>

    <!-- Stats Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Devices</span>
                    <h2 class="fw-extrabold text-primary mb-0 mt-1">{{ $totalDevices }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3">
                    <i class="bi bi-cpu"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Online Devices</span>
                    <h2 class="fw-extrabold text-success mb-0 mt-1">{{ $onlineDevices }}</h2>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success fs-3">
                    <i class="bi bi-wifi"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Offline / Disabled</span>
                    <h2 class="fw-extrabold text-danger mb-0 mt-1">{{ $offlineDevices }}</h2>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger fs-3">
                    <i class="bi bi-wifi-off"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Devices Grid / Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-body py-3 border-0">
            <h5 class="fw-bold mb-0"><i class="bi bi-hdd-network-fill me-2 text-info"></i>Configured ZKTeco Devices</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Device Name</th>
                        <th>IP Address & Port</th>
                        <th>Location / Branch</th>
                        <th>Device SN</th>
                        <th>Status</th>
                        <th>Last Sync</th>
                        <th class="pe-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr id="device-row-{{ $device->id }}">
                        <td class="ps-4">
                            <div class="fw-bold text-body">{{ $device->name }}</div>
                            <span class="text-muted small">ID: #{{ $device->id }}</span>
                        </td>
                        <td>
                            <code class="bg-dark text-info px-2 py-1 rounded">{{ $device->ip_address }}:{{ $device->port }}</code>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><i class="bi bi-geo-alt me-1"></i>{{ $device->location ?? 'Main Campus' }}</span>
                        </td>
                        <td>
                            <span class="text-muted small">{{ $device->device_sn ?? 'N/A' }}</span>
                        </td>
                        <td>
                            @if($device->status === 'online')
                                <span class="badge bg-success rounded-pill px-3 py-2 status-badge"><i class="bi bi-check-circle me-1"></i>Online</span>
                            @elseif($device->status === 'disabled')
                                <span class="badge bg-secondary rounded-pill px-3 py-2 status-badge"><i class="bi bi-slash-circle me-1"></i>Disabled</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3 py-2 status-badge"><i class="bi bi-exclamation-triangle me-1"></i>Offline</span>
                            @endif
                        </td>
                        <td>
                            <span class="text-muted small last-sync-time">
                                {{ $device->last_sync_at ? $device->last_sync_at->diffForHumans() : 'Never' }}
                            </span>
                        </td>
                        <td class="pe-4 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <button type="button" class="btn btn-sm btn-subtle-info rounded-pill px-3 btn-test-conn" data-device-id="{{ $device->id }}" style="font-size: 0.8rem;">
                                    <i class="bi bi-lightning me-1"></i> Ping / Test
                                </button>
                                <button type="button" class="btn btn-sm btn-subtle-success rounded-pill px-3 btn-sync-single" data-device-id="{{ $device->id }}" style="font-size: 0.8rem;">
                                    <i class="bi bi-arrow-repeat me-1"></i> Sync Logs
                                </button>
                                <button type="button" class="action-btn action-btn-primary" data-bs-toggle="modal" data-bs-target="#editDeviceModal{{ $device->id }}" title="Edit Device">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.biometric-devices.destroy', $device->id) }}" method="POST" class="d-inline m-0 p-0" onsubmit="return confirm('Are you sure you want to delete device {{ $device->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn action-btn-danger" title="Delete Device">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-hdd-network fs-1 d-block text-secondary mb-2"></i>
                            <h5 class="fw-bold">No ZKTeco Biometric Devices Registered</h5>
                            <p class="mb-3">Add your ZKTeco K40, F18, or MB20 devices to manage multi-branch biometric attendance.</p>
                            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addDeviceModal">
                                <i class="bi bi-plus-lg me-1"></i> Add Your First Device
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@foreach($devices as $device)
<div class="modal fade" id="editDeviceModal{{ $device->id }}" tabindex="-1" aria-labelledby="editDeviceModalLabel{{ $device->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('admin.biometric-devices.update', $device->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold" id="editDeviceModalLabel{{ $device->id }}"><i class="bi bi-pencil-square me-2"></i>Edit Biometric Device</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Device Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $device->name }}" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-8">
                            <label class="form-label fw-bold">IP Address</label>
                            <input type="text" name="ip_address" class="form-control" value="{{ $device->ip_address }}" required>
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label fw-bold">Port</label>
                            <input type="number" name="port" class="form-control" value="{{ $device->port }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Location / Branch Name</label>
                        <input type="text" name="location" class="form-control" value="{{ $device->location }}" placeholder="e.g. Branch 1 - Main Gate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Device Serial Number (Optional)</label>
                        <input type="text" name="device_sn" class="form-control" value="{{ $device->device_sn }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="online" {{ $device->status == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ $device->status == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="disabled" {{ $device->status == 'disabled' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-body-tertiary py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('admin.biometric-devices.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Add New ZKTeco Biometric Device</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Device Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Main Gate ZKTeco K40" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-8">
                            <label class="form-label fw-bold">IP Address</label>
                            <input type="text" name="ip_address" class="form-control" placeholder="e.g. 192.168.1.201" required>
                        </div>
                        <div class="col-4">
                            <label class="form-label fw-bold">Port</label>
                            <input type="number" name="port" class="form-control" value="4370" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Location / Branch Name</label>
                        <input type="text" name="location" class="form-control" placeholder="e.g. Branch 1 - Academic Building">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Device Serial Number (Optional)</label>
                        <input type="text" name="device_sn" class="form-control" placeholder="e.g. K40-SN-001">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select">
                            <option value="offline" selected>Offline</option>
                            <option value="online">Online</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-body-tertiary py-3 rounded-bottom-4">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Add Device</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const globalAlert = document.getElementById('ajax-global-alert');

    function showAlert(type, message) {
        globalAlert.className = `alert alert-${type} alert-dismissible fade show rounded-4 mb-4`;
        globalAlert.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        globalAlert.classList.remove('d-none');
    }

    // Ping / Test Connection
    document.querySelectorAll('.btn-test-conn').forEach(btn => {
        btn.addEventListener('click', function () {
            const deviceId = this.getAttribute('data-device-id');
            const row = document.getElementById(`device-row-${deviceId}`);
            const badge = row?.querySelector('.status-badge');

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';

            fetch(`/admin/biometric-devices/${deviceId}/test`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (badge && data.status) {
                    if (data.status === 'online') {
                        badge.className = 'badge bg-success rounded-pill px-3 py-2 status-badge';
                        badge.innerHTML = '<i class="bi bi-check-circle me-1"></i>Online';
                    } else {
                        badge.className = 'badge bg-danger rounded-pill px-3 py-2 status-badge';
                        badge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Offline';
                    }
                }
            })
            .catch(err => {
                showAlert('danger', 'Error testing connection: ' + err.message);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-lightning me-1"></i> Ping / Test';
            });
        });
    });

    // Single Device Sync
    document.querySelectorAll('.btn-sync-single').forEach(btn => {
        btn.addEventListener('click', function () {
            const deviceId = this.getAttribute('data-device-id');
            const row = document.getElementById(`device-row-${deviceId}`);
            const syncTimeSpan = row?.querySelector('.last-sync-time');

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Syncing...';

            fetch(`/admin/biometric-devices/${deviceId}/sync`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (data.success && syncTimeSpan && data.last_sync_at) {
                    syncTimeSpan.textContent = data.last_sync_at;
                }
            })
            .catch(err => {
                showAlert('danger', 'Error syncing device: ' + err.message);
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Sync Logs';
            });
        });
    });

    // Sync All Devices
    const btnSyncAll = document.getElementById('btn-sync-all');
    if (btnSyncAll) {
        btnSyncAll.addEventListener('click', function () {
            const spinner = document.getElementById('sync-all-spinner');
            const icon = document.getElementById('sync-all-icon');
            const btn = this;

            btn.disabled = true;
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');

            fetch("{{ route('admin.biometric-devices.sync-all') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                showAlert(data.success ? 'success' : 'danger', data.message);
                if (data.success) {
                    setTimeout(() => window.location.reload(), 2000);
                }
            })
            .catch(err => {
                showAlert('danger', 'Error syncing all devices: ' + err.message);
            })
            .finally(() => {
                btn.disabled = false;
                spinner.classList.add('d-none');
                icon.classList.remove('d-none');
            });
        });
    }
});
</script>
@endsection
