@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Page Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Attendance Log History</h3>
            <p class="text-muted small mb-0"><span class="fw-semibold text-primary">{{ $companyName }}</span> &bull; Search, monitor and download historical attendance records for all staff or individual employees</p>
        </div>
        <div class="d-flex flex-wrap gap-2 d-print-none">
            <!-- Download All Dropdown -->
            <div class="dropdown">
                <button class="btn btn-success btn-sm rounded-pill px-3 shadow-sm dropdown-toggle fw-semibold" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Download All History
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    <li><h6 class="dropdown-header text-uppercase small fw-bold">All Employees ({{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }})</h6></li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'export' => 'csv']) }}">
                            <i class="fa-solid fa-file-excel text-success me-2"></i> Download All as Excel / CSV
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'export' => 'pdf']) }}">
                            <i class="fa-solid fa-file-pdf text-danger me-2"></i> Download All as PDF Report
                        </a>
                    </li>
                </ul>
            </div>

            <a href="{{ route('admin.attendance-suite.attendance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Live Grid
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
        <div class="card-body p-3">
            <form action="{{ route('admin.attendance-suite.attendance.history') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1 text-secondary">Month</label>
                    <select name="month" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1 text-secondary">Year</label>
                    <select name="year" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        @for($y=date('Y')+1; $y>=date('Y')-3; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1 text-secondary">Filter by Employee</label>
                    <select name="staff_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Employees (Full Company)</option>
                        @foreach($staffMembers as $s)
                            <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>
                                {{ $s->user?->name }} ({{ $s->employeeId() }}) - {{ $s->departmentName }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 flex-grow-1">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if($staffId)
                        <a href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-light border rounded-pill px-2" title="Reset to All Employees">
                            <i class="fa-solid fa-rotate-left"></i> All
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($selectedStaff)
        <!-- Print Header for Browser Print -->
        <div class="d-none d-print-block mb-4 p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start">
                <div class="d-flex align-items-center gap-3">
                    @if(!empty($companyLogo) && file_exists(public_path(ltrim($companyLogo, '/'))))
                        <img src="{{ asset(ltrim($companyLogo, '/')) }}" style="max-height: 48px; max-width: 150px;" alt="{{ $companyName }}">
                    @endif
                    <div>
                        <h2 class="fw-bold mb-0 text-dark">{{ $companyName }}</h2>
                        <div class="text-secondary small">Office Attendance & Workforce Management System</div>
                        @if(!empty($companyAddress))
                            <div class="text-muted small">{{ $companyAddress }} @if(!empty($companyRegNo)) &bull; Reg No: {{ $companyRegNo }} @endif</div>
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <h5 class="fw-bold text-uppercase text-primary mb-0">Employee Attendance Statement</h5>
                    <div class="small text-muted">Period: <strong>{{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}</strong></div>
                    <div class="small text-muted">Branch: {{ $selectedStaff->branchName }}</div>
                </div>
            </div>
        </div>

        <!-- Individual Employee Full Month Profile & Summary Card -->
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white border-start border-4 border-primary">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3 pb-3 border-bottom">
                    <div class="d-flex align-items-center gap-3">
                        @if($selectedStaff->photo && file_exists(public_path('storage/' . $selectedStaff->photo)))
                            <img src="{{ asset('storage/' . $selectedStaff->photo) }}" class="rounded-circle border shadow-sm" style="width: 54px; height: 54px; object-fit: cover;" alt="{{ $selectedStaff->user?->name }}">
                        @else
                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-4" style="width: 54px; height: 54px;">
                                {{ strtoupper(substr($selectedStaff->user?->name ?? 'E', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $selectedStaff->user?->name }}</h5>
                            <div class="text-muted small mt-1">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border me-1">{{ $selectedStaff->employeeId() }}</span>
                                @if($selectedStaff->biometric_id)
                                    <span class="badge bg-info bg-opacity-10 text-info border me-1"><i class="fa-solid fa-fingerprint me-1"></i>Bio: {{ $selectedStaff->biometric_id }}</span>
                                @endif
                                <span class="fw-semibold text-primary">{{ $selectedStaff->designationTitle }}</span> &bull; 
                                <span>{{ $selectedStaff->departmentName }}</span> &bull; 
                                <span class="text-muted">{{ $selectedStaff->branchName }}</span> &bull;
                                <span class="badge bg-primary bg-opacity-10 text-primary border"><i class="fa-solid fa-building me-1"></i>{{ $companyName }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Download Buttons -->
                    <div class="d-flex flex-wrap gap-2 d-print-none">
                        <a href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'staff_id' => $selectedStaff->id, 'export' => 'csv']) }}" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                            <i class="fa-solid fa-file-excel me-1"></i> Download Month CSV
                        </a>
                        <a href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'staff_id' => $selectedStaff->id, 'export' => 'pdf']) }}" class="btn btn-danger btn-sm rounded-pill px-3 shadow-sm fw-semibold">
                            <i class="fa-solid fa-file-pdf me-1"></i> Download Month PDF Card
                        </a>
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-print me-1"></i> Print Statement
                        </button>
                    </div>
                </div>

                @if($staffMonthlySummary)
                    <!-- Monthly KPI Quick Stats for Selected Employee -->
                    <div class="row g-2 text-center">
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Recorded</span>
                                <h5 class="fw-bold text-primary mb-0 mt-1">{{ $staffMonthlySummary['total_days'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Present</span>
                                <h5 class="fw-bold text-success mb-0 mt-1">{{ $staffMonthlySummary['present'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Late</span>
                                <h5 class="fw-bold text-warning mb-0 mt-1">{{ $staffMonthlySummary['late'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Early Leave</span>
                                <h5 class="fw-bold text-info mb-0 mt-1">{{ $staffMonthlySummary['early_leave'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Absent</span>
                                <h5 class="fw-bold text-danger mb-0 mt-1">{{ $staffMonthlySummary['absent'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-4 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">On Leave</span>
                                <h5 class="fw-bold text-purple mb-0 mt-1">{{ $staffMonthlySummary['leave'] }} <small class="fs-7 text-muted">days</small></h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Overtime</span>
                                <h5 class="fw-bold text-success mb-0 mt-1">{{ round($staffMonthlySummary['total_overtime_min'] / 60, 1) }} <small class="fs-7 text-muted">hrs</small></h5>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 col-xl">
                            <div class="bg-light p-2 rounded-3 border">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Work Hours</span>
                                <h5 class="fw-bold text-dark mb-0 mt-1">{{ $staffMonthlySummary['total_working_hrs'] }} <small class="fs-7 text-muted">hrs</small></h5>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- History Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 px-4 pt-3 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                @if($selectedStaff)
                    <i class="fa-solid fa-calendar-days text-primary me-2"></i>{{ $selectedStaff->user?->name }}'s Attendance History for {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
                @else
                    <i class="fa-solid fa-list-check text-primary me-2"></i>Monthly Attendance Logs ({{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }})
                @endif
            </h5>
            <span class="text-muted small">Showing {{ $historyLogs->total() }} records</span>
        </div>

        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th class="table-hide-xs">Branch & Dept</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th class="table-hide-xs">Late / Early</th>
                        <th class="table-hide-xs">Overtime</th>
                        <th class="table-hide-xs">Work Hours</th>
                        <th class="text-end">Full Month Download</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyLogs as $log)
                        @php
                            $staff = $log->attendable;
                        @endphp
                        <tr>
                            <td class="fw-bold text-dark" style="white-space: nowrap;">
                                {{ $log->attendance_date?->format('d M, Y') }}
                                <span class="text-muted small d-block font-monospace">({{ $log->attendance_date?->format('D') }})</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($staff?->photo && file_exists(public_path('storage/' . $staff->photo)))
                                        <img src="{{ asset('storage/' . $staff->photo) }}" class="rounded-circle border" style="width: 32px; height: 32px; object-fit: cover;" alt="">
                                    @else
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($staff?->user?->name ?? 'E', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark">{{ $staff?->user?->name ?? 'N/A' }}</div>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border small">{{ $staff?->employeeId() }}</span>
                                        @if($staff?->biometric_id)
                                            <span class="badge bg-info bg-opacity-10 text-info border small"><i class="fa-solid fa-fingerprint me-1"></i>{{ $staff->biometric_id }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="table-hide-xs">
                                <div class="fw-semibold text-secondary">{{ $staff?->departmentName }}</div>
                                <span class="text-muted small">{{ $staff?->branchName }}</span>
                            </td>
                            <td>
                                @if($log->entry_time)
                                    <span class="fw-bold text-success"><i class="fa-solid fa-arrow-right-to-bracket me-1"></i>{{ date('h:i A', strtotime($log->entry_time)) }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($log->exit_time)
                                    <span class="fw-bold text-danger"><i class="fa-solid fa-arrow-right-from-bracket me-1"></i>{{ date('h:i A', strtotime($log->exit_time)) }}</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $log->badgeClass() }} px-2 py-1">{{ strtoupper($log->status) }}</span>
                                @if($log->is_corrected)
                                    <span class="badge bg-secondary text-white ms-1" title="Manually corrected"><i class="fa-solid fa-pen-nib"></i></span>
                                @endif
                            </td>
                            <td class="table-hide-xs">
                                @if($log->late_minutes > 0)
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Late: {{ $log->late_minutes }}m</span>
                                @endif
                                @if($log->early_leave_minutes > 0)
                                    <span class="badge bg-info text-dark fw-bold px-2 py-1"><i class="fa-solid fa-person-walking-arrow-right me-1"></i>Early: {{ $log->early_leave_minutes }}m</span>
                                @endif
                                @if(!$log->late_minutes && !$log->early_leave_minutes && in_array($log->status, ['present', 'work_from_home']))
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="fa-solid fa-check me-1"></i>On Time</span>
                                @endif
                            </td>
                            <td class="table-hide-xs">
                                {{ $log->overtime_minutes > 0 ? round($log->overtime_minutes / 60, 1) . ' hrs' : '--' }}
                            </td>
                            <td class="table-hide-xs fw-semibold">
                                {{ $log->working_hours > 0 ? $log->working_hours . ' hrs' : '--' }}
                            </td>
                            <td class="text-end" style="white-space: nowrap;">
                                <!-- Individual Row Action for Downloading this Employee's Month -->
                                @if($staff)
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-light border rounded-pill px-3 py-1 shadow-sm dropdown-toggle fw-semibold text-primary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-solid fa-file-arrow-down me-1"></i> Monthly
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                                            <li><h6 class="dropdown-header small text-muted text-uppercase fw-bold">{{ $staff->user?->name }}</h6></li>
                                            <li>
                                                <a class="dropdown-item py-2 small" href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'staff_id' => $staff->id, 'export' => 'csv']) }}">
                                                    <i class="fa-solid fa-file-excel text-success me-2"></i> Download Month CSV / Excel
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 small" href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'staff_id' => $staff->id, 'export' => 'pdf']) }}">
                                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i> Download Month PDF Card
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <a class="dropdown-item py-2 small text-primary" href="{{ route('admin.attendance-suite.attendance.history', ['month' => $month, 'year' => $year, 'staff_id' => $staff->id]) }}">
                                                    <i class="fa-solid fa-filter me-2"></i> Filter This Employee Only
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                @else
                                    --
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-5">
                                <i class="fa-regular fa-folder-open fs-2 d-block mb-2 text-secondary"></i>
                                No attendance history logs found for the selected period.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historyLogs->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-muted small">Page {{ $historyLogs->currentPage() }} of {{ $historyLogs->lastPage() }}</span>
                {{ $historyLogs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
