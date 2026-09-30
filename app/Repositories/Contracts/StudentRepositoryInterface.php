<?php

namespace App\Repositories\Contracts;

use App\Models\StudentProfile;
use Illuminate\Database\Eloquent\Collection;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveStudents(): Collection;

    public function findByAdmissionNumber(string $admissionNo): ?StudentProfile;

    public function promoteStudents(array $studentIds, int $targetClassId, int $targetSectionId, int $targetSessionId): bool;
}
