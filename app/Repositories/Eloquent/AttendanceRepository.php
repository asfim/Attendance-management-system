<?php

namespace App\Repositories\Eloquent;

use App\Models\Attendance;
use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function saveAttendance(string $attendableType, int $attendableId, Carbon $date, string $status, ?string $remarks): bool
    {
        $attendance = Attendance::updateOrCreate(
            [
                'attendance_date' => $date->format('Y-m-d'),
                'attendable_type' => $attendableType,
                'attendable_id' => $attendableId,
            ],
            [
                'status' => $status,
                'remarks' => $remarks,
            ]
        );

        return (bool) $attendance;
    }

    public function getAttendanceReport(string $attendableType, int $classId, int $sectionId, Carbon $date, ?int $shiftId = null): Collection
    {
        // For student attendance, we join student_profiles
        if ($attendableType === \App\Models\StudentProfile::class) {
            return Attendance::where('attendable_type', $attendableType)
                ->where('attendance_date', $date->format('Y-m-d'))
                ->whereHas('attendable', function ($query) use ($classId, $sectionId, $shiftId) {
                    $query->where('class_id', $classId)->where('section_id', $sectionId);
                    if ($shiftId) {
                        $query->where('shift_id', $shiftId);
                    }
                })
                ->with('attendable.user')
                ->get();
        }

        // For others, return directly
        return Attendance::where('attendable_type', $attendableType)
            ->where('attendance_date', $date->format('Y-m-d'))
            ->with('attendable.user')
            ->get();
    }
}
