@extends('layouts.app')

@section('title', 'Biometric / Fingerprint Attendance Logs')

@section('content')
<style>
.bio-stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.bio-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
[data-bs-theme="dark"] .bio-stat-card {
    background: #1e293b;
    border-color: #334155;
}
.badge-state-check-in {
    background-color: #dcfce7;
    color: #15803d;
    font-weight: 600;
}
.badge-state-check-out {
    background-color: #e0f2fe;
    color: #0369a1;
    font-weight: 600;
}
.badge-user-student {
    background-color: #fef3c7;
    color: #b45309;
    font-weight: 600;
}
.badge-user-staff {
    background-color: #f3e8ff;
    color: #6b21a8;
    font-weight: 600;
}
.endpoint-box {
    background: #0f172a;
    color: #38bdf8;
    padding: 0.75rem 1rem;
    border-radius: 10px;
    font-family: monospace;
    font-size: 0.875rem;
    word-break: break-all;
}
</style>

<div class="container-fluid px-4 py-3">
    <!-- Top Header & Navigation -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-fingerprint text-primary me-2"></i>Biometric & Fingerprint Attendance</h3>
            <p class="text-muted mb-0">Real-time attendance punch logs synced from ZKTeco devices, HTTP API, and biometrics.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.biometric-devices.index') }}" class="btn btn-outline-info rounded-pill px-3 shadow-sm">
                <i class="bi bi-cpu-fill me-1"></i> Device Manager
            </a>
            <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#syncDeviceModal">
                <i class="bi bi-arrow-repeat me-1"></i> Quick Sync
            </button>
            <a href="{{ route('admin.attendance.live-monitor') }}" target="_blank" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="bi bi-display me-1"></i> Live Kiosk
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-person-lines-fill me-1"></i> Student Attendance
            </a>
        </div>
    </div>

    <!-- Stats Summary Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="bio-stat-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Punches {{ $startDate ? '(' . \Carbon\Carbon::parse($startDate)->format('d M') . ($endDate && $endDate != $startDate ? ' - ' . \Carbon\Carbon::parse($endDate)->format('d M') : '') . ')' : 'Today' }}</span>
                    <h2 class="fw-extrabold text-primary mb-0 mt-1" id="stat-total">{{ number_format($totalPunchesToday) }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3">
                    <i class="bi bi-hand-index-thumb"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bio-stat-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Successful Syncs</span>
                    <h2 class="fw-extrabold text-success mb-0 mt-1" id="stat-success">{{ number_format($successfulPunchesToday) }}</h2>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success fs-3">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bio-stat-card d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Unmatched / Failed</span>
                    <h2 class="fw-extrabold text-danger mb-0 mt-1" id="stat-failed">{{ number_format($failedPunchesToday) }}</h2>
                </div>
                <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger fs-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
    </div>



    <!-- Log Category Filter Tabs & Date Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <!-- Category Tabs -->
            <ul class="nav nav-pills bg-body p-1 rounded-pill border">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 {{ ($type ?? 'all') == 'all' ? 'active' : '' }}" href="{{ route('admin.attendance.biometric-logs', array_filter(['type' => 'all', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search])) }}">
                        <i class="bi bi-list-stars me-1"></i> All Punch Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 {{ ($type ?? '') == 'student' ? 'active fw-bold' : '' }}" href="{{ route('admin.attendance.biometric-logs', array_filter(['type' => 'student', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search])) }}">
                        <i class="bi bi-mortarboard-fill me-1"></i> Student Biometric Logs
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-4 {{ ($type ?? '') == 'staff' ? 'active fw-bold' : '' }}" href="{{ route('admin.attendance.biometric-logs', array_filter(['type' => 'staff', 'start_date' => $startDate, 'end_date' => $endDate, 'search' => $search])) }}">
                        <i class="bi bi-person-badge-fill me-1"></i> Teacher & Staff Biometric Logs
                    </a>
                </li>
            </ul>

            <!-- Date & Search Filter Form -->
            <form method="GET" action="{{ route('admin.attendance.biometric-logs') }}" class="d-flex flex-wrap align-items-center gap-2" id="filter-form">
                <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
                
                <div class="input-group" style="width: auto;">
                    <span class="input-group-text bg-body border-end-0"><i class="bi bi-calendar text-primary"></i></span>
                    <input type="date" name="start_date" class="form-control border-start-0" value="{{ $startDate ?? '' }}" placeholder="Start Date">
                </div>
                
                <div class="input-group" style="width: auto;">
                    <span class="input-group-text bg-body border-end-0">To</span>
                    <input type="date" name="end_date" class="form-control border-start-0" value="{{ $endDate ?? '' }}" placeholder="End Date">
                </div>

                <div class="input-group" style="width: 220px;">
                    <span class="input-group-text bg-body border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" value="{{ $search ?? '' }}" placeholder="Search name or ID...">
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-3 shadow-sm">
                    Filter
                </button>
                
                <button type="submit" name="download" value="csv" class="btn btn-success rounded-pill px-3 shadow-sm">
                    <i class="bi bi-download me-1"></i> Download CSV
                </button>

                @if($startDate || $endDate || $search)
                <a href="{{ route('admin.attendance.biometric-logs', ['type' => $type]) }}" class="btn btn-outline-danger rounded-pill px-3">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Live Punch Logs Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-body py-3 d-flex align-items-center justify-content-between border-0">
            <h5 class="fw-bold mb-0">
                @if(($type ?? '') == 'student')
                    <i class="bi bi-mortarboard-fill me-2 text-primary"></i>Student Biometric Punch Logs
                @elseif(($type ?? '') == 'staff')
                    <i class="bi bi-person-badge-fill me-2 text-purple" style="color: #6b21a8;"></i>Teacher & Staff Biometric Punch Logs
                @else
                    <i class="bi bi-journal-text me-2 text-primary"></i>All Live Biometric Punch Logs
                @endif
                @if($startDate)
                    <span class="text-primary small fw-normal ms-2">({{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} {{ $endDate && $endDate != $startDate ? '- ' . \Carbon\Carbon::parse($endDate)->format('d M, Y') : '' }})</span>
                @endif
            </h5>
            <div>
                <span class="badge bg-primary rounded-pill px-3 py-2 me-2">Updated Real-Time</span>
                <form action="{{ route('admin.attendance.biometric-logs.clear') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear all punch logs?');">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type ?? 'all' }}">
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm">
                        <i class="bi bi-trash-fill me-1"></i> Clear Logs
                    </button>
                </form>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Roll</th>
                        <th>Class & Section</th>
                        <th>Biometric ID</th>
                        <th>User Name & Role</th>
                        <th>Punch Time</th>
                        <th>State</th>
                        <th>Shift</th>
                        <th class="pe-4">Late (Min)</th>
                    </tr>
                </thead>
                <tbody id="log-table-body">
                    @include('admin.attendance.partials.biometric_log_rows', ['logs' => $logs])
                </tbody>
            </table>
        </div>

        <!-- Load More Section -->
        <div class="text-center py-3 border-top bg-body rounded-bottom-4" id="load-more-wrapper" style="{{ $logs->hasMorePages() ? '' : 'display: none;' }}">
            <button type="button" class="btn btn-outline-primary rounded-pill px-5 shadow-sm py-2" id="btn-load-more" data-next-page="{{ $logs->currentPage() + 1 }}">
                <span class="spinner-border spinner-border-sm me-2 d-none" id="load-more-spinner" role="status" aria-hidden="true"></span>
                <i class="bi bi-arrow-down-circle fs-6 me-1" id="load-more-icon"></i>
                <span id="load-more-text" class="fw-bold">Load More Punch Logs</span>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnLoadMore = document.getElementById('btn-load-more');
    const wrapperLoadMore = document.getElementById('load-more-wrapper');
    const tableBody = document.getElementById('log-table-body');
    const spinner = document.getElementById('load-more-spinner');
    const icon = document.getElementById('load-more-icon');
    const btnText = document.getElementById('load-more-text');

    if (btnLoadMore) {
        btnLoadMore.addEventListener('click', function () {
            const nextPage = this.getAttribute('data-next-page');
            const type = "{{ $type ?? 'all' }}";
            const date = "{{ $date ?? '' }}";
            const search = "{{ $search ?? '' }}";

            // Show Loading State
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');
            btnText.textContent = 'Loading More Logs...';
            btnLoadMore.disabled = true;

            const url = new URL("{{ route('admin.attendance.biometric-logs') }}");
            url.searchParams.set('page', nextPage);
            url.searchParams.set('type', type);
            if (date) url.searchParams.set('date', date);
            if (search) url.searchParams.set('search', search);

            fetch(url.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.html) {
                    tableBody.insertAdjacentHTML('beforeend', data.html);

                    if (data.has_more) {
                        btnLoadMore.setAttribute('data-next-page', data.next_page);
                        wrapperLoadMore.style.display = 'block';
                    } else {
                        wrapperLoadMore.style.display = 'none';
                    }

                    // Update stats if present
                    if (data.total_punches) document.getElementById('stat-total').textContent = data.total_punches;
                    if (data.success_punches) document.getElementById('stat-success').textContent = data.success_punches;
                    if (data.failed_punches) document.getElementById('stat-failed').textContent = data.failed_punches;
                }
            })
            .catch(err => {
                console.error('Error loading logs:', err);
            })
            .finally(() => {
                spinner.classList.add('d-none');
                icon.classList.remove('d-none');
                btnText.textContent = 'Load More Punch Logs';
                btnLoadMore.disabled = false;
            });
        });
    }
});

// Direct ZKTeco Device Sync Handler
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btn-run-zk-sync')?.addEventListener('click', function () {
        const ip = document.getElementById('modal-device-ip').value.trim();
        const port = document.getElementById('modal-device-port').value.trim() || 4370;
        const alertBox = document.getElementById('zk-sync-alert');
        const spinner = document.getElementById('zk-sync-spinner');
        const btn = this;

    if (!ip) {
        alertBox.className = 'alert alert-danger mb-3';
        alertBox.textContent = 'Please enter a valid Device IP address.';
        alertBox.classList.remove('d-none');
        return;
    }

    alertBox.className = 'alert alert-info mb-3';
    alertBox.textContent = 'Connecting to ZKTeco device and fetching logs...';
    alertBox.classList.remove('d-none');
    spinner.classList.remove('d-none');
    btn.disabled = true;

    fetch("{{ route('admin.attendance.biometric-direct-sync') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ device_ip: ip, device_port: port })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.className = 'alert alert-success mb-3';
            alertBox.textContent = data.message;
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alertBox.className = 'alert alert-danger mb-3';
            alertBox.textContent = data.message || 'Error connecting to ZKTeco device.';
            btn.disabled = false;
        }
    })
    .catch(err => {
        alertBox.className = 'alert alert-danger mb-3';
        alertBox.textContent = 'Network or connection error: ' + err.message;
        btn.disabled = false;
    })
    .finally(() => {
        spinner.classList.add('d-none');
    });
    });
});
</script>

<!-- Sync ZKTeco Device Modal -->
<div class="modal fade" id="syncDeviceModal" tabindex="-1" aria-labelledby="syncDeviceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header bg-success text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="syncDeviceModalLabel"><i class="bi bi-arrow-repeat me-2"></i>Sync ZKTeco Fingerprint Device</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="zk-sync-alert" class="alert d-none" role="alert"></div>

                <p class="text-muted small mb-3">
                    Connect directly to your ZKTeco K40 device over LAN/Network using the Device IP Address.
                </p>

                <div class="mb-3">
                    <label for="modal-device-ip" class="form-label fw-bold">ZKTeco Device IP Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-body"><i class="bi bi-router"></i></span>
                        <input type="text" id="modal-device-ip" class="form-control" placeholder="e.g. 192.168.1.103" value="192.168.1.103">
                    </div>
                    <div class="form-text">Check your K40 device Menu ➔ Comm. ➔ Ethernet for the Device IP.</div>
                </div>

                <div class="mb-3">
                    <label for="modal-device-port" class="form-label fw-bold">UDP Port</label>
                    <input type="number" id="modal-device-port" class="form-control" value="4370" placeholder="Default: 4370">
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3 rounded-bottom-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm" id="btn-run-zk-sync">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="zk-sync-spinner" role="status" aria-hidden="true"></span>
                    <i class="bi bi-cloud-download me-1"></i> Start Device Sync
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
