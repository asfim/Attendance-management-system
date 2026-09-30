<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use Illuminate\Support\Carbon;

class AttendanceProcessingService
{
    /**
     * Process daily attendance record for a student or staff given all punches of the day.
     *
     * Rules:
     * - First Punch = Check In
     * - Last Punch = Check Out (if multiple punches exist)
     * - Late Calculation: Start Time + Grace Time
     * - Early Leave Calculation: Exit Time before Shift End
     * - Overtime Calculation: Exit Time after Shift End
     */
    public function processDailyPunches($profile, string $attendableType, string $dateStr, array $punchTimes, int $graceMinutes = 10): Attendance
    {
        sort($punchTimes); // Sort chronologically

        $firstPunch = Carbon::parse($punchTimes[0]);
        $lastPunch = count($punchTimes) > 1 ? Carbon::parse(end($punchTimes)) : null;

        $entryTimeStr = $firstPunch->format('H:i:s');
        $exitTimeStr = $lastPunch ? $lastPunch->format('H:i:s') : null;

        // Default Shift Config (Can be pulled from Shift model if associated)
        $shiftStart = '09:00:00';
        $shiftEnd = '18:00:00';

        if (method_exists($profile, 'shift') && $profile->shift) {
            if (!empty($profile->shift->start_time)) {
                $shiftStart = $profile->shift->start_time;
            }
            if (!empty($profile->shift->end_time)) {
                $shiftEnd = $profile->shift->end_time;
            }
        } elseif (method_exists($profile, 'shifts') && $profile->shifts->count() > 0) {
            $shifts = $profile->shifts->sortBy('start_time');
            $firstShift = $shifts->first();
            $lastShift = $shifts->last();
            
            if (!empty($firstShift->start_time)) {
                $shiftStart = $firstShift->start_time;
            }
            if (!empty($lastShift->end_time)) {
                $shiftEnd = $lastShift->end_time;
            }
        }

        // Late Calculation: Start Time + Grace Time
        $expectedStart = Carbon::parse("{$dateStr} {$shiftStart}");
        $lateThreshold = $expectedStart->copy()->addMinutes($graceMinutes);

        $lateMinutes = 0;
        $status = 'present';

        if ($firstPunch->gt($lateThreshold)) {
            $status = 'late';
            $lateMinutes = (int) $firstPunch->diffInMinutes($expectedStart, true);
        }

        // Early Leave & Overtime Calculation
        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;

        if ($lastPunch) {
            $expectedEnd = Carbon::parse("{$dateStr} {$shiftEnd}");

            if ($lastPunch->lt($expectedEnd)) {
                $earlyLeaveMinutes = (int) $lastPunch->diffInMinutes($expectedEnd, true);
            } elseif ($lastPunch->gt($expectedEnd)) {
                $overtimeMinutes = (int) $lastPunch->diffInMinutes($expectedEnd, true);
            }
        }

        $attendance = Attendance::updateOrCreate(
            [
                'attendance_date' => $dateStr,
                'attendable_type' => $attendableType,
                'attendable_id'   => $profile->id,
            ],
            [
                'entry_time'           => $entryTimeStr,
                'exit_time'            => $exitTimeStr,
                'status'               => $status,
                'late_minutes'         => $lateMinutes,
                'early_leave_minutes'  => $earlyLeaveMinutes,
                'overtime_minutes'     => $overtimeMinutes,
                'remarks'              => "Biometric Auto-Processed. Entry: {$entryTimeStr}" . ($exitTimeStr ? " | Exit: {$exitTimeStr}" : ""),
            ]
        );

        return $attendance;
    }

    /**
     * Reusable Overtime Calculation Helper for Payroll module.
     */
    public function calculateOvertime(Carbon $checkoutTime, string $dateStr, string $shiftEndTime = '18:00:00'): int
    {
        $expectedEnd = Carbon::parse("{$dateStr} {$shiftEndTime}");
        if ($checkoutTime->gt($expectedEnd)) {
            return (int) $checkoutTime->diffInMinutes($expectedEnd);
        }
        return 0;
    }
}
