@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.staff.history.index') }}" class="btn btn-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to List
    </a>
</div>

<div class="row">
    <!-- Staff Profile Card -->
    <div class="col-md-4 mb-4">
        <div class="card glass-card border p-4 text-center">
            @if ($staff->photo)
                <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo"
                    class="rounded-circle object-fit-cover border border-primary border-opacity-25 mx-auto mb-3"
                    style="width: 80px; height: 80px;">
            @else
                <div class="mx-auto bg-primary bg-opacity-10 d-flex align-items-center justify-content-center text-primary fw-bold rounded-circle mb-3 border border-primary border-opacity-25" style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                </div>
            @endif
            <h5 class="fw-bold mb-1">{{ $staff->user->name }}</h5>
            <p class="text-muted mb-2">{{ $staff->user->email }}</p>
            <span class="badge bg-secondary mb-3">{{ $staff->user->role->display_name ?? 'N/A' }}</span>

            <ul class="list-group list-group-flush text-start">
                <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                    <span class="text-muted">Employee ID</span>
                    <span class="fw-semibold">{{ $staff->employee_id ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                    <span class="text-muted">Department</span>
                    <span class="fw-semibold">{{ $staff->department ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item bg-transparent text-light border-secondary border-opacity-25 d-flex justify-content-between">
                    <span class="text-muted">Designation</span>
                    <span class="fw-semibold">{{ $staff->designation ?? 'N/A' }}</span>
                </li>
                <li class="list-group-item bg-transparent text-light border-0 d-flex justify-content-between">
                    <span class="text-muted">Joining Date</span>
                    <span class="fw-semibold">{{ $staff->joining_date ? $staff->joining_date->format('M d, Y') : 'N/A' }}</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Attendance History Tabs Content -->
    <div class="col-md-8">
        <div class="card glass-card border p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="fw-bold mb-0">Attendance Records</h6>
                
                <!-- Month/Year Selector -->
                <form action="{{ route('admin.staff.history.show', $staff->id) }}" method="GET" class="d-flex gap-2 align-items-center">
                    <select name="month" class="form-select form-select-sm bg-dark text-light border-secondary" style="width:120px;" onchange="this.form.submit()">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                    
                    <select name="year" class="form-select form-select-sm bg-dark text-light border-secondary" style="width:100px;" onchange="this.form.submit()">
                        @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                {{ $y }}
                            </option>
                        @endfor
                    </select>
                </form>
            </div>

            <!-- Attendance Legend -->
            <div class="d-flex flex-wrap align-items-center justify-content-center gap-3 mb-3 fs-8 text-muted py-2 px-3 rounded-3" style="background: rgba(0,0,0,0.15); border: 1px solid var(--bs-border-color);">
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:#22c55e; display:inline-block; box-shadow: 0 0 6px rgba(34,197,94,0.5);"></span>
                    <span>Present</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:#ef4444; display:inline-block; box-shadow: 0 0 6px rgba(239,68,68,0.5);"></span>
                    <span>Absent</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:#f97316; display:inline-block; box-shadow: 0 0 6px rgba(249,115,22,0.5);"></span>
                    <span>Late</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:#3b82f6; display:inline-block; box-shadow: 0 0 6px rgba(59,130,246,0.5);"></span>
                    <span>Leave</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:#a855f7; display:inline-block; box-shadow: 0 0 6px rgba(168,85,247,0.5);"></span>
                    <span>Half Day</span>
                </div>
                <div class="d-flex align-items-center gap-1">
                    <span style="width:12px; height:12px; border-radius:3px; background:rgba(107,114,128,0.25); border:1px dashed #6b7280; display:inline-block;"></span>
                    <span>Holiday / Off</span>
                </div>
            </div>

            <!-- Visual Calendar Grid -->
            <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="table-responsive p-3">
                    <table class="table align-middle text-center mb-0 mx-auto" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05); max-width: 520px;">
                        <thead>
                            <tr style="border-bottom: 1px solid var(--bs-border-color);">
                                @foreach(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'] as $dayName)
                                    <th class="text-uppercase" style="font-size: 0.75rem; color: #9ca3af; font-weight: 700; padding: 10px 4px; border: none;">{{ $dayName }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($weeks as $week)
                                <tr>
                                    @foreach($week as $day)
                                        <td class="p-1 border-0" style="width:14.28%;">
                                            @if($day['in_month'])
                                                @php
                                                    $st = $day['status'] ?? null;
                                                    $cellStyle = match($st) {
                                                        'present' => 'background-color: #22c55e; color: #fff; box-shadow: 0 0 10px rgba(34, 197, 94, 0.4);',
                                                        'absent'  => 'background-color: #ef4444; color: #fff; box-shadow: 0 0 10px rgba(239, 68, 68, 0.4);',
                                                        'late'    => 'background-color: #f97316; color: #fff; box-shadow: 0 0 10px rgba(249, 115, 22, 0.4);',
                                                        'leave'   => 'background-color: #3b82f6; color: #fff; box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);',
                                                        'half_day'=> 'background-color: #a855f7; color: #fff; box-shadow: 0 0 10px rgba(168, 85, 247, 0.4);',
                                                        'holiday' => 'background-color: rgba(107, 114, 128, 0.2); color: #9ca3af; border: 1px dashed #6b7280;',
                                                        default   => 'background-color: transparent; color: inherit;',
                                                    };
                                                    $titleText = $day['date'] . ' - ' . ucfirst(str_replace('_', ' ', $st ?? 'No Record'));
                                                @endphp
                                                <div class="d-flex justify-content-center align-items-center mx-auto" 
                                                    title="{{ $titleText }}"
                                                    style="width: 36px; height: 36px; border-radius: 10px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: transform 0.15s, box-shadow 0.15s; {{ $cellStyle }} {{ $day['is_today'] ? 'outline: 2px solid #0d6efd; outline-offset: 2px;' : '' }}">
                                                    {{ $day['day'] }}
                                                </div>
                                            @else
                                                <span class="text-muted opacity-25" style="font-size: 0.85rem;">{{ $day['day'] }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Details Table -->
            <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
                @php
                    $present = $monthAttendances->where('status', 'present')->count();
                    $absent = $monthAttendances->where('status', 'absent')->count();
                    $late = $monthAttendances->where('status', 'late')->count();
                    $leave = $monthAttendances->where('status', 'leave')->count();
                    $halfDay = $monthAttendances->where('status', 'half_day')->count();
                    $holiday = 0;
                    foreach($weeks as $week) {
                        foreach($week as $day) {
                            if ($day['in_month'] && $day['status'] === 'holiday') {
                                $holiday++;
                            }
                        }
                    }
                    $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
                @endphp
                
                <div class="p-4">
                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-list-check me-2"></i>{{ $monthName }} Details</h6>
                    
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Present: {{ $present }}</span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">Absent: {{ $absent }}</span>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">Late: {{ $late }}</span>
                        @if($leave > 0)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">Leave: {{ $leave }}</span>
                        @endif
                        @if($halfDay > 0)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">Half Day: {{ $halfDay }}</span>
                        @endif
                        @if($holiday > 0)
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2">Holiday: {{ $holiday }}</span>
                        @endif
                    </div>
                    
                    @if($monthAttendances->count() > 0)
                    <div class="table-responsive">
                        <table class="table align-middle table-sm text-light mb-0"
                            style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                            <thead style="background-color: rgba(0,0,0,0.2);">
                                <tr>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-2 px-3 border-0">Date</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Status</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Reason / Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($monthAttendances->sortByDesc('attendance_date') as $att)
                                    <tr class="border-bottom border-secondary border-opacity-10 hover-bg-secondary transition-all">
                                        <td class="px-3 py-2 fw-semibold">
                                            {{ \Carbon\Carbon::parse($att->attendance_date)->format('F d, Y') }}
                                            <div class="text-muted fs-8">
                                                {{ \Carbon\Carbon::parse($att->attendance_date)->format('l') }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge {{ $att->badgeClass() }} bg-opacity-10 border border-opacity-25 rounded-pill px-2 py-1 fs-7" style="color: {{ $att->calendarColor() }}; border-color: {{ $att->calendarColor() }} !important;">
                                                {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-7">
                                            @if($att->status === 'late' && $att->late_reason)
                                                <strong class="text-warning">Reason:</strong> {{ $att->late_reason }} <br>
                                            @endif
                                            @if($att->status === 'leave' && $att->leave_reason)
                                                <strong class="text-primary">Reason:</strong> {{ $att->leave_reason }} <br>
                                            @endif
                                            @if($att->remarks)
                                                <strong>Note:</strong> {{ $att->remarks }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-calendar-xmark fs-2 mb-3 d-block"></i>
                            No attendance records found for {{ $monthName }}.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
