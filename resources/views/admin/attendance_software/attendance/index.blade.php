@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-clipboard-user text-primary me-2"></i>Attendance Management</h3>
            <p class="text-muted small mb-0">Check-in, Check-out, Present, Absent, Late, Early Leave, Half Day, Overtime & Manual Punching</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.attendance-suite.attendance.missing-punches') }}" class="btn btn-outline-danger btn-sm rounded-pill">
                <i class="fa-solid fa-triangle-exclamation me-1"></i> Missing Punches
            </a>
            <a href="{{ route('admin.attendance-suite.attendance.corrections') }}" class="btn btn-outline-warning btn-sm rounded-pill">
                <i class="fa-solid fa-wrench me-1"></i> Attendance Corrections
            </a>
            <a href="{{ route('admin.attendance-suite.attendance.history') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
                <i class="fa-solid fa-history me-1"></i> Full History Log
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter & Date Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.attendance-suite.attendance.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold mb-1">Attendance Date</label>
                    <input type="date" name="date" class="form-control form-control-sm rounded-pill" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-6 col-md-3">
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
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Load Attendance Grid</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Attendance Grid Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Status</th>
                        <th>Late / Early</th>
                        <th>Overtime</th>
                        <th class="text-end">Manual Punch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $staff)
                        @php
                            $att = $attendances->get($staff->id);
                            $shift = $staff->shifts->first() ?? $staff->shift ?? \App\Models\Shift::first();
                            
                            $lateMins = $att?->late_minutes ?? 0;
                            $earlyMins = $att?->early_leave_minutes ?? 0;
                            
                            // Dynamic fallback late calculation if entry time is after shift start
                            if ($att?->entry_time && $shift?->start_time) {
                                $shiftStart = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
                                $userEntry  = \Carbon\Carbon::parse($date . ' ' . $att->entry_time);
                                if ($userEntry->gt($shiftStart)) {
                                    $diffLate = (int) $userEntry->diffInMinutes($shiftStart);
                                    if ($diffLate > $lateMins) {
                                        $lateMins = $diffLate;
                                    }
                                }
                            } elseif ($att?->status === 'late' && !$lateMins) {
                                $lateMins = 15;
                            }

                            // Dynamic fallback early leave calculation if exit time is before shift end
                            if ($att?->exit_time && $shift?->end_time) {
                                $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);
                                $userExit = \Carbon\Carbon::parse($date . ' ' . $att->exit_time);
                                if ($userExit->lt($shiftEnd)) {
                                    $diffEarly = (int) $shiftEnd->diffInMinutes($userExit);
                                    if ($diffEarly > $earlyMins) {
                                        $earlyMins = $diffEarly;
                                    }
                                }
                            }
                        @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $staff->photoUrl() }}" class="rounded-circle shadow-sm" width="36" height="36" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $staff->user?->name }}</div>
                                        <span class="text-muted small">{{ $staff->employeeId() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $staff->departmentName }}</div>
                                <span class="text-muted small"><i class="fa-regular fa-clock me-1"></i>Shift: {{ $shift?->name ?? 'General' }} ({{ $shift?->start_time ? date('h:i A', strtotime($shift->start_time)) : '09:00 AM' }})</span>
                            </td>
                            <td>
                                @if($att?->entry_time)
                                    <div class="fw-bold text-dark">{{ date('h:i A', strtotime($att->entry_time)) }}</div>
                                    @if($lateMins > 0)
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger rounded-pill px-2 py-1 mt-1" style="font-size: 0.75rem;">
                                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Late by {{ $lateMins }} Min(s)
                                        </span>
                                    @endif
                                @else
                                    <span class="text-muted">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if($att?->exit_time)
                                    <span class="fw-bold text-dark">{{ date('h:i A', strtotime($att->exit_time)) }}</span>
                                @else
                                    <span class="text-muted">--:--</span>
                                @endif
                            </td>
                            <td>
                                @if($att)
                                    <span class="badge {{ $att->badgeClass() }} px-3 py-1 fw-bold" style="letter-spacing: 0.5px;">
                                        {{ strtoupper($att->status) }}
                                        @if(($att->status === 'late' || $lateMins > 0) && $lateMins > 0)
                                            ({{ $lateMins }}m)
                                        @endif
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-3 py-1">NOT MARKED</span>
                                @endif
                            </td>
                            <td>
                                @if($lateMins > 0)
                                    <div class="mb-1">
                                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 shadow-sm" style="font-size: 0.82rem;"><i class="fa-solid fa-hourglass-half me-1"></i>Late: {{ $lateMins }} Mins</span>
                                    </div>
                                @endif
                                @if($earlyMins > 0)
                                    <div>
                                        <span class="badge bg-info text-dark fw-bold px-3 py-1 shadow-sm" style="font-size: 0.82rem;"><i class="fa-solid fa-person-walking-arrow-right me-1"></i>Early: {{ $earlyMins }} Mins</span>
                                    </div>
                                @endif
                                @if(!$lateMins && !$earlyMins)
                                    @if($att?->status === 'present')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success rounded-pill px-3 py-1"><i class="fa-solid fa-circle-check me-1"></i>On Time</span>
                                    @elseif($att?->status === 'absent')
                                        <span class="text-danger small fw-bold"><i class="fa-solid fa-user-xmark me-1"></i>Absent</span>
                                    @elseif($att?->status === 'leave')
                                        <span class="text-primary small fw-bold"><i class="fa-solid fa-umbrella-beach me-1"></i>On Leave</span>
                                    @elseif($att?->status === 'holiday')
                                        <span class="text-secondary small fw-bold"><i class="fa-solid fa-calendar-day me-1"></i>Holiday</span>
                                    @else
                                        <span class="text-muted">--</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($att?->overtime_minutes > 0)
                                    <span class="badge bg-success px-3 py-1">+{{ round($att->overtime_minutes / 60, 1) }} hrs</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="openPunchModal({{ $staff->id }}, '{{ addslashes($staff->user?->name) }}', '{{ $att?->entry_time }}', '{{ $att?->exit_time }}', '{{ $att?->status ?? 'present' }}')">
                                    <i class="fa-solid fa-pen me-1"></i> Punch / Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Manual Punch -->
<div class="modal fade" id="punchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.attendance.mark-manual') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <input type="hidden" name="staff_profile_id" id="modal_staff_id">
            <input type="hidden" name="attendance_date" value="{{ $date }}">

            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock text-primary me-2"></i>Manual Attendance Punch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Employee</label>
                    <input type="text" id="modal_staff_name" class="form-control bg-light" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                    <select name="status" id="modal_status" class="form-select" required>
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="absent">Absent</option>
                        <option value="early_leave">Early Leave</option>
                        <option value="half_day">Half Day</option>
                        <option value="leave">Leave</option>
                        <option value="holiday">Holiday</option>
                        <option value="missing_punch">Missing Punch</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Check-In Time</label>
                        <input type="time" name="entry_time" id="modal_entry" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Check-Out Time</label>
                        <input type="time" name="exit_time" id="modal_exit" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Remarks / Adjustment Reason</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Attendance</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPunchModal(staffId, staffName, entryTime, exitTime, status) {
    document.getElementById('modal_staff_id').value = staffId;
    document.getElementById('modal_staff_name').value = staffName;
    document.getElementById('modal_entry').value = entryTime || '';
    document.getElementById('modal_exit').value = exitTime || '';
    document.getElementById('modal_status').value = status || 'present';

    const modal = new bootstrap.Modal(document.getElementById('punchModal'));
    modal.show();
}
</script>
@endsection
