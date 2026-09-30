<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ExamRepositoryInterface
{
    public function getExamSchedulesByClass(int $classId, int $examTypeId): Collection;

    public function recordMarks(int $examScheduleId, array $marksData): bool;

    public function getStudentResults(int $studentProfileId, int $examTypeId): Collection;
}
