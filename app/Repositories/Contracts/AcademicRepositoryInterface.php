<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AcademicRepositoryInterface
{
    public function getActiveSession();

    public function getAllClasses(): Collection;

    public function getSectionsByClass(int $classId): Collection;

    public function getSubjectsByClass(int $classId): Collection;

    public function getTimetableForSection(int $sectionId, int $sessionId): Collection;
}
