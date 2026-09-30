<?php

namespace App\Repositories\Eloquent;

use App\Models\StudentProfile;
use App\Repositories\Contracts\StudentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    public function __construct(StudentProfile $studentProfile)
    {
        parent::__construct($studentProfile);
    }

    public function getActiveStudents(): Collection
    {
        return $this->model->where('status', 'active')->with(['user', 'schoolClass', 'section'])->get();
    }

    public function findByAdmissionNumber(string $admissionNo): ?StudentProfile
    {
        return $this->model->where('admission_no', $admissionNo)->with(['user', 'schoolClass', 'section'])->first();
    }

    public function promoteStudents(array $studentIds, int $targetClassId, int $targetSectionId, int $targetSessionId): bool
    {
        return $this->model->whereIn('id', $studentIds)->update([
            'class_id' => $targetClassId,
            'section_id' => $targetSectionId,
            'session_id' => $targetSessionId,
        ]) > 0;
    }
}
