@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Attendance Log History</h3>
            <p class="text-muted small mb-0">Search historical attendance records by Month, Year, and Staff member</p>
        </div>
        <a href="{{ route('admin.attendance-suite.attendance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Live Grid
        </a>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.attendance-suite.attendance.history') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Month</label>
                    <select name="month" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        @for($y=date('Y'); $y>=date('Y')-2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Employee</label>
                    <select name="staff_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        @foreach($staffMembers as $s)
                            <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->user?->name }} ({{ $s->employeeId() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filter History</button>
                </div>
            </form>
        </div>
    </div>

    <!-- History Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee Name</th>
                        <th class="table-hide-xs">Branch / Dept</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th class="table-hide-xs">Late / Early</th>
                        <th class="table-hide-xs">Overtime</th>
                        <th class="table-hide-xs">Working Hours</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyLogs as $log)
                        <tr>
                            <td class="fw-bold text-dark">{{ $log->attendance_date?->format('d M, Y (D)') }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $log->attendable?->user?->name }}</div>
                                <span class="text-muted small">{{ $log->attendable?->employeeId() }}</span>
                            </td>
                            <td>{{ $log->attendable?->branchName }} / {{ $log->attendable?->departmentName }}</td>
                            <td class="fw-bold text-success">{{ $log->entry_time ? date('h:i A', strtotime($log->entry_time)) : '--' }}</td>
                            <td class="fw-bold text-danger">{{ $log->exit_time ? date('h:i A', strtotime($log->exit_time)) : '--' }}</td>
                            <td><span class="badge {{ $log->badgeClass() }}">{{ strtoupper($log->status) }}</span></td>
                            <td>
                                @if($log->late_minutes > 0)
                                    <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fa-solid fa-clock me-1"></i>Late: {{ $log->late_minutes }} Minutes</span>
                                @endif
                                @if($log->early_leave_minutes > 0)
                                    <span class="badge bg-info text-dark fw-bold px-2 py-1"><i class="fa-solid fa-person-walking-arrow-right me-1"></i>Early: {{ $log->early_leave_minutes }} Mins</span>
                                @endif
                                @if(!$log->late_minutes && !$log->early_leave_minutes)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1"><i class="fa-solid fa-check me-1"></i>On Time</span>
                                @endif
                            </td>
                            <td>{{ $log->overtime_minutes > 0 ? round($log->overtime_minutes / 60, 1) . ' hrs' : '--' }}</td>
                            <td>{{ $log->working_hours > 0 ? $log->working_hours . ' hrs' : '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-5">No attendance history found for the selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($historyLogs->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $historyLogs->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
