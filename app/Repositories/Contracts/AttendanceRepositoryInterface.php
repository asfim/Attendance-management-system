<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface AttendanceRepositoryInterface
{
    public function saveAttendance(string $attendableType, int $attendableId, Carbon $date, string $status, ?string $remarks): bool;

    public function getAttendanceReport(string $attendableType, int $classId, int $sectionId, Carbon $date, ?int $shiftId = null): Collection;
}
