<?php

namespace App\Services;

use App\Repositories\Contracts\AttendanceRepositoryInterface;
use Illuminate\Support\Carbon;

class AttendanceService
{
    protected AttendanceRepositoryInterface $attendanceRepository;

    public function __construct(AttendanceRepositoryInterface $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function takeBulkAttendance(string $attendableType, array $attendanceData, string $dateStr): bool
    {
        $date = Carbon::parse($dateStr);
        $success = true;

        foreach ($attendanceData as $record) {
            $attendableId = $record['attendable_id'];
            $status = $record['status'];
            $remarks = $record['remarks'] ?? null;

            if (!$this->attendanceRepository->saveAttendance($attendableType, $attendableId, $date, $status, $remarks)) {
                $success = false;
            }
        }

        return $success;
    }

    public function getAttendanceReport(string $attendableType, int $classId, int $sectionId, string $dateStr, ?int $shiftId = null)
    {
        $date = Carbon::parse($dateStr);
        return $this->attendanceRepository->getAttendanceReport($attendableType, $classId, $sectionId, $date, $shiftId);
    }
}
