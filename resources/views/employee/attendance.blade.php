@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-user-clock text-success me-2"></i>My Attendance</h3>
            <p class="text-muted small mb-0">View your daily attendance and monthly summaries</p>
        </div>
        
        <form action="{{ route('employee.attendance') }}" method="GET" class="d-flex gap-2">
            <select name="month" class="form-select rounded-pill" onchange="this.form.submit()">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
            <select name="year" class="form-select rounded-pill" onchange="this.form.submit()">
                @for($y=date('Y'); $y>=date('Y')-2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    <!-- Monthly Counters -->
    <div class="row g-3 text-center mb-4">
        <div class="col-3">
            <div class="p-3 rounded-4" style="background-color: #d1e7dd;">
                <span class="text-success small fw-semibold d-block">Present</span>
                <h3 class="fw-bold text-success mb-0">{{ $presentDays }}</h3>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 rounded-4" style="background-color: #fff3cd;">
                <span class="text-warning small fw-semibold d-block" style="color: #856404 !important;">Late</span>
                <h3 class="fw-bold mb-0" style="color: #856404;">{{ $lateDays }}</h3>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 rounded-4" style="background-color: #f8d7da;">
                <span class="text-danger small fw-semibold d-block">Absent</span>
                <h3 class="fw-bold text-danger mb-0">{{ $absentDays }}</h3>
            </div>
        </div>
        <div class="col-3">
            <div class="p-3 rounded-4" style="background-color: #cfe2ff;">
                <span class="text-primary small fw-semibold d-block" style="color: #084298 !important;">Leave</span>
                <h3 class="fw-bold mb-0" style="color: #084298;">{{ $leaveDays }}</h3>
            </div>
        </div>
    </div>

    <!-- Attendance History Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="border-bottom">
                        <th class="text-body fw-bold py-3">Date</th>
                        <th class="text-body fw-bold py-3">Check-In</th>
                        <th class="text-body fw-bold py-3">Check-Out</th>
                        <th class="text-body fw-bold py-3">Status</th>
                        <th class="text-body fw-bold py-3">Late / Overtime</th>
                    </tr>
                </thead>
                <tbody class="text-body">
                    @forelse($monthlyAttendances as $att)
                        <tr>
                            <td>{{ $att->attendance_date?->format('d M, Y (D)') }}</td>
                            <td>{{ $att->entry_time ? date('h:i A', strtotime($att->entry_time)) : '--' }}</td>
                            <td>{{ $att->exit_time ? date('h:i A', strtotime($att->exit_time)) : '--' }}</td>
                            <td><span class="badge {{ $att->badgeClass() }}">{{ strtoupper($att->status) }}</span></td>
                            <td>
                                @if($att->late_minutes > 0) <span class="badge bg-warning text-dark me-1">Late {{ $att->late_minutes }}m</span> @endif
                                @if($att->overtime_minutes > 0) <span class="badge bg-success">OT +{{ round($att->overtime_minutes/60, 1) }}h</span> @endif
                                @if(!$att->late_minutes && !$att->overtime_minutes) <span class="text-muted">--</span> @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No attendance records for this month.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
