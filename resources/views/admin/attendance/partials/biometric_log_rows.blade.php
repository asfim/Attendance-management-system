@forelse($logs as $log)
@php
    $roll = '-';
    $classSection = '-';
    $lateMin = '-';
    $stateStr = 'Punch';
    $stateClass = 'badge-state-check-in';
    $stateIcon = 'bi-fingerprint text-warning';
    $shiftName = 'N/A';
    $shift = null;

    if ($log->user_type == 'student') {
        $student = \App\Models\StudentProfile::where('biometric_id', $log->biometric_id)->with(['schoolClass', 'section', 'shift'])->first();
        if ($student) {
            $roll = $student->roll_no ?? '-';
            $classSection = ($student->schoolClass->name ?? 'N/A') . ' / ' . ($student->section->name ?? 'N/A');
            $shift = $student->shift;
        }
    } elseif ($log->user_type == 'staff') {
        $staff = \App\Models\StaffProfile::where('biometric_id', $log->biometric_id)->with('shifts')->first();
        if ($staff) {
            $roll = $staff->designation ?? '-';
            $classSection = $staff->department ?? 'Staff';
            $shifts = $staff->shifts->sortBy('start_time');
            if ($shifts->count() > 0) {
                $firstShift = $shifts->first();
                $lastShift = $shifts->last();
                
                $shift = new \stdClass();
                $shift->name = $firstShift->name . ($shifts->count() > 1 ? ' & Others' : '');
                $shift->start_time = $firstShift->start_time;
                $shift->end_time = $lastShift->end_time;
            }
        }
    }

    if ($shift) {
        $shiftName = $shift->name;
    }

    if ($log->punch_time) {
        // Fallback odd/even
        $punchCount = \App\Models\BiometricDeviceLog::where('biometric_id', $log->biometric_id)
            ->whereDate('punch_time', $log->punch_time->format('Y-m-d'))
            ->where('status', 'success')
            ->where('id', '<=', $log->id)
            ->count();
        $computedState = ($punchCount % 2 === 1) ? 'check_in' : 'check_out';
        
        $computedLateMins = 0;

        if ($shift) {
            $punchTime = clone $log->punch_time;
            $date = $punchTime->format('Y-m-d');
            $shiftStart = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
            $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);
            
            // Midpoint of shift
            $midpoint = $shiftStart->copy()->addMinutes($shiftStart->diffInMinutes($shiftEnd) / 2);
            $isCheckInPhase = $punchTime <= $midpoint;
            
            // Check previous punches to find if we already have an IN or OUT
            $previousPunches = \App\Models\BiometricDeviceLog::where('biometric_id', $log->biometric_id)
                ->whereDate('punch_time', $date)
                ->where('status', 'success')
                ->where('id', '<', $log->id)
                ->get();
                
            $alreadyHasIn = false;
            $alreadyHasOut = false;
            foreach ($previousPunches as $pp) {
                if ($pp->punch_time <= $midpoint) $alreadyHasIn = true;
                else $alreadyHasOut = true;
            }
            
            if ($isCheckInPhase) {
                if ($alreadyHasIn) {
                    $computedState = 'duplicate';
                } else {
                    $computedState = 'check_in';
                    // Calculate late
                    $lateDiff = $shiftStart->diffInMinutes($punchTime, false);
                    if ($lateDiff > 0) $computedLateMins = (int)$lateDiff;
                }
            } else {
                if ($alreadyHasOut) {
                    $computedState = 'duplicate';
                } else {
                    $computedState = 'check_out';
                }
            }
        }
        
        if ($computedState === 'check_in') {
            $stateStr = 'Check-In';
            $stateClass = 'badge-state-check-in';
            $stateIcon = 'bi-box-arrow-in-right text-success';
            
            if ($computedLateMins > 0) {
                $hours = floor($computedLateMins / 60);
                $mins = $computedLateMins % 60;
                $timeString = '';
                if ($hours > 0) $timeString .= $hours . ' hr ';
                if ($mins > 0 || $hours == 0) $timeString .= $mins . ' min';
                
                $lateMin = "<span class='badge bg-danger rounded-pill'>{$timeString} late</span>";
            } else {
                $lateMin = "<span class='badge bg-success rounded-pill'>On Time</span>";
            }
        } elseif ($computedState === 'check_out') {
            $stateStr = 'Check-Out';
            $stateClass = 'badge-state-check-out';
            $stateIcon = 'bi-box-arrow-right text-danger';
        } else {
            $stateStr = 'Duplicate';
            $stateClass = 'badge-state-duplicate bg-secondary bg-opacity-25 text-secondary border border-secondary';
            $stateIcon = 'bi-files text-secondary';
        }
    }
@endphp
<tr>
    <td class="ps-4 fw-bold text-body">{{ $roll }}</td>
    <td><span class="badge bg-secondary bg-opacity-25 text-body border border-secondary">{{ $classSection }}</span></td>
    <td><code class="fw-bold fs-6 text-primary">{{ $log->biometric_id }}</code></td>
    <td>
        @if($log->user_name)
            <div class="fw-bold text-body">{{ $log->user_name }}</div>
            @if($log->user_type == 'student')
                <span class="badge badge-user-student rounded-pill px-2 py-1"><i class="bi bi-mortarboard-fill me-1"></i> Student</span>
            @elseif($log->user_type == 'staff')
                <span class="badge badge-user-staff rounded-pill px-2 py-1"><i class="bi bi-person-badge-fill me-1"></i> Teacher/Staff</span>
            @endif
        @else
            <span class="text-muted italic">Unknown User</span>
        @endif
    </td>
    <td>
        <div class="fw-bold text-body">{{ $log->punch_time ? $log->punch_time->format('h:i:s A') : '-' }}</div>
        <small class="text-muted">{{ $log->punch_time ? $log->punch_time->format('d M, Y') : '-' }}</small>
    </td>
    <td>
        <span class="badge {{ $stateClass }} px-3 py-1 rounded-pill"><i class="bi {{ $stateIcon }} me-1"></i> {{ $stateStr }}</span>
    </td>
    <td>
        <span class="badge bg-info bg-opacity-25 text-info border border-info rounded-pill px-3 py-1"><i class="bi bi-clock-history me-1"></i> {{ $shiftName }}</span>
    </td>
    <td class="pe-4 small text-muted font-monospace">{!! $lateMin !!}</td>
</tr>
@empty
@if(request('page', 1) == 1)
<tr>
    <td colspan="8" class="text-center py-5 text-muted">
        <i class="bi bi-fingerprint fs-1 d-block text-secondary mb-2"></i>
        No biometric punch logs found for the selected criteria.
    </td>
</tr>
@endif
@endforelse
