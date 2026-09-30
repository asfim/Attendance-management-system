@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-clock text-primary me-2"></i>Attendance & HR Dashboard</h3>
            <p class="text-muted small mb-0">Biometric Attendance, Shift Tracking, Leave & Payroll Summary for Today ({{ date('d M, Y') }})</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <form action="{{ route('admin.attendance-suite.dashboard') }}" method="GET" class="d-flex gap-2">
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
                <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Departments</option>
                    @foreach($allDepartments as $d)
                        <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.attendance-suite.attendance.index') }}" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                <i class="fa-solid fa-fingerprint me-1"></i> Live Attendance
            </a>
        </div>
    </div>

    <!-- 1. Stats Counter Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Employees -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Total Staff</span>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $totalEmployees }}</h3>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="fa-solid fa-users fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Present -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Present</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $presentCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                        <i class="fa-solid fa-user-check fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Late -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-warning">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Late Coming</span>
                        <h3 class="fw-bold text-warning mb-0 mt-1">{{ $lateCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="fa-solid fa-user-clock fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Absent -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-danger">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Absent</span>
                        <h3 class="fw-bold text-danger mb-0 mt-1">{{ $absentCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                        <i class="fa-solid fa-user-xmark fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Early Leave -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Early Leave</span>
                        <h3 class="fw-bold text-info mb-0 mt-1">{{ $earlyLeaveCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                        <i class="fa-solid fa-person-walking-arrow-right fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Leave Today -->
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white border-start border-4 border-secondary">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Leave Today</span>
                        <h3 class="fw-bold text-secondary mb-0 mt-1">{{ $leaveTodayCount }}</h3>
                    </div>
                    <div class="rounded-circle bg-secondary bg-opacity-10 p-3 text-secondary">
                        <i class="fa-solid fa-umbrella-beach fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Charts & Analytics Row -->
    <div class="row g-4 mb-4">
        <!-- Today's Attendance Check-in Graph -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-line text-primary me-2"></i>Today's Hourly Check-in Trend</h5>
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Realtime Biometric Feed</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <canvas id="checkinTrendChart" style="max-height: 280px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Breakdown (Doughnut) -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-chart-pie text-success me-2"></i>Monthly Summary</h5>
                    <p class="text-muted small mb-0">{{ date('F Y') }} Attendance Overview</p>
                </div>
                <div class="card-body px-4 pb-4 text-center">
                    <canvas id="monthlySummaryChart" style="max-height: 220px;"></canvas>
                    <div class="d-flex justify-content-around mt-3 text-start small">
                        <div><span class="badge bg-success rounded-circle p-1 me-1"></span>Present: <strong>{{ $monthlyPresent }}</strong></div>
                        <div><span class="badge bg-warning rounded-circle p-1 me-1"></span>Late: <strong>{{ $monthlyLate }}</strong></div>
                        <div><span class="badge bg-danger rounded-circle p-1 me-1"></span>Absent: <strong>{{ $monthlyAbsent }}</strong></div>
                        <div><span class="badge bg-primary rounded-circle p-1 me-1"></span>Leave: <strong>{{ $monthlyLeave }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Tables Row: Late Coming & Department Breakdown -->
    <div class="row g-4">
        <!-- Late Coming Summary -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>Today's Late Coming Summary</h5>
                    <a href="{{ route('admin.attendance-suite.reports.index', ['type' => 'late']) }}" class="btn btn-sm btn-outline-warning rounded-pill">View All Late</a>
                </div>
                <div class="table-responsive px-4 pb-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Staff Name</th>
                                <th>Department</th>
                                <th>Check-in Time</th>
                                <th>Late Delay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lateSummary as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img src="{{ $item->attendable?->photoUrl() }}" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                                            <div>
                                                <div class="fw-bold text-dark mb-0" style="font-size: 0.85rem;">{{ $item->attendable?->user?->name }}</div>
                                                <span class="text-muted" style="font-size: 0.75rem;">{{ $item->attendable?->employeeId() }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->attendable?->departmentName }}</td>
                                    <td class="fw-semibold text-danger">{{ $item->entry_time ? date('h:i A', strtotime($item->entry_time)) : '--' }}</td>
                                    <td><span class="badge bg-warning text-dark">{{ $item->late_minutes }} mins</span></td>
                                    <td><span class="badge bg-warning text-dark">Late</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4"><i class="fa-solid fa-circle-check text-success me-1"></i> No late arrivals recorded today!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Department-wise Attendance -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-sitemap text-info me-2"></i>Department-wise Attendance</h5>
                    <p class="text-muted small mb-0">Present ratio across departments today</p>
                </div>
                <div class="card-body px-4 pb-4">
                    @forelse($departmentStats as $dept)
                        @php
                            $pct = $dept['total'] > 0 ? round(($dept['present'] / $dept['total']) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-dark" style="font-size: 0.85rem;">{{ $dept['department'] }}</span>
                                <span class="small text-muted">{{ $dept['present'] }}/{{ $dept['total'] }} Present ({{ $pct }}%)</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px;">
                                <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $pct }}%" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No departments found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Hourly Trend Chart
    const ctxTrend = document.getElementById('checkinTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'line',
        data: {
            labels: {!! json_encode($hourlyCheckins['labels'] ?? []) !!},
            datasets: [{
                label: 'Check-ins',
                data: {!! json_encode($hourlyCheckins['data'] ?? []) !!},
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 2. Monthly Summary Chart
    const ctxPie = document.getElementById('monthlySummaryChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Late', 'Absent', 'Leave'],
            datasets: [{
                data: [{{ $monthlyPresent }}, {{ $monthlyLate }}, {{ $monthlyAbsent }}, {{ $monthlyLeave }}],
                backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#3b82f6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
});
</script>
@endsection
