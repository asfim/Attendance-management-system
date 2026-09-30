<?php

namespace App\Repositories\Eloquent;

use App\Models\ExamSchedule;
use App\Models\MarksEntry;
use App\Repositories\Contracts\ExamRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExamRepository implements ExamRepositoryInterface
{
    public function getExamSchedulesByClass(int $classId, int $examTypeId): Collection
    {
        return ExamSchedule::where('class_id', $classId)
            ->where('exam_type_id', $examTypeId)
            ->with(['subject', 'classroom'])
            ->get();
    }

    public function recordMarks(int $examScheduleId, array $marksData): bool
    {
        return DB::transaction(function () use ($examScheduleId, $marksData) {
            foreach ($marksData as $data) {
                MarksEntry::updateOrCreate(
                    [
                        'exam_schedule_id' => $examScheduleId,
                        'student_profile_id' => $data['student_profile_id'],
                    ],
                    [
                        'marks_obtained' => $data['marks_obtained'] ?? null,
                        'attendance_status' => $data['attendance_status'] ?? 'present',
                        'remarks' => $data['remarks'] ?? null,
                    ]
                );
            }
            return true;
        });
    }

    public function getStudentResults(int $studentProfileId, int $examTypeId): Collection
    {
        return MarksEntry::where('student_profile_id', $studentProfileId)
            ->whereHas('examSchedule', function ($query) use ($examTypeId) {
                $query->where('exam_type_id', $examTypeId);
            })
            ->with(['examSchedule.subject', 'examSchedule.examType'])
            ->get();
    }
}
