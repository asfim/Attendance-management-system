@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-file-invoice text-primary me-2"></i>Attendance Reports & Exports</h3>
            <p class="text-muted small mb-0">Daily, Monthly, Department, Late, Absent, Overtime, Leave, Early Leave, Missing Punch & Salary Reports</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="btn btn-outline-success btn-sm rounded-pill">
                <i class="fa-solid fa-file-excel me-1"></i> Export Excel / CSV
            </a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-primary btn-sm rounded-pill">
                <i class="fa-solid fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filter & Report Type Selector Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.attendance-suite.reports.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Select Report Type</label>
                    <select name="type" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="daily" {{ $reportType == 'daily' ? 'selected' : '' }}>1. Daily Attendance Report</option>
                        <option value="monthly" {{ $reportType == 'monthly' ? 'selected' : '' }}>2. Monthly Attendance Report</option>
                        <option value="late" {{ $reportType == 'late' ? 'selected' : '' }}>3. Late Coming Report</option>
                        <option value="absent" {{ $reportType == 'absent' ? 'selected' : '' }}>4. Absenteeism Report</option>
                        <option value="overtime" {{ $reportType == 'overtime' ? 'selected' : '' }}>5. Overtime Hours Report</option>
                        <option value="early_leave" {{ $reportType == 'early_leave' ? 'selected' : '' }}>6. Early Leave Report</option>
                        <option value="missing_punch" {{ $reportType == 'missing_punch' ? 'selected' : '' }}>7. Missing Punch Report</option>
                        <option value="leave" {{ $reportType == 'leave' ? 'selected' : '' }}>8. Leave Take Report</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm rounded-pill" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Month / Year</label>
                    <div class="d-flex gap-1">
                        <select name="month" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                            @for($m=1; $m<=12; $m++)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endfor
                        </select>
                        <select name="year" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                            @for($y=date('Y'); $y>=date('Y')-2; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Branch</label>
                    <select name="branch_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Department</label>
                    <select name="department_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-3 px-4 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-check text-primary me-2"></i>Report Results: <span class="text-primary text-uppercase">{{ str_replace('_', ' ', $reportType) }}</span></h5>
            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $records->count() }} Records Found</span>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee ID</th>
                        <th>Staff Name</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Late / Early</th>
                        <th>Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        <tr>
                            <td class="fw-bold text-dark">{{ $rec->attendance_date?->format('d M, Y') }}</td>
                            <td class="fw-bold text-primary">{{ $rec->attendable?->employeeId() }}</td>
                            <td>{{ $rec->attendable?->user?->name }}</td>
                            <td>{{ $rec->attendable?->branchName }}</td>
                            <td>{{ $rec->attendable?->departmentName }}</td>
                            <td>{{ $rec->entry_time ? date('h:i A', strtotime($rec->entry_time)) : '--' }}</td>
                            <td>{{ $rec->exit_time ? date('h:i A', strtotime($rec->exit_time)) : '--' }}</td>
                            <td><span class="badge {{ $rec->badgeClass() }}">{{ strtoupper($rec->status) }}</span></td>
                            <td>
                                @if($rec->late_minutes > 0) <span class="badge bg-warning text-dark me-1">Late {{ $rec->late_minutes }}m</span> @endif
                                @if($rec->early_leave_minutes > 0) <span class="badge bg-info text-dark">Early {{ $rec->early_leave_minutes }}m</span> @endif
                                @if(!$rec->late_minutes && !$rec->early_leave_minutes) <span class="text-muted">--</span> @endif
                            </td>
                            <td>{{ $rec->overtime_minutes > 0 ? round($rec->overtime_minutes / 60, 1) . ' hrs' : '--' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-5">No records matched for the selected filter parameters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
