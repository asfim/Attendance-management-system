<?php

namespace App\Repositories\Eloquent;

use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Timetable;
use App\Repositories\Contracts\AcademicRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AcademicRepository implements AcademicRepositoryInterface
{
    public function getActiveSession()
    {
        return AcademicSession::where('is_active', true)->first();
    }

    public function getAllClasses(): Collection
    {
        return SchoolClass::with('sections')->get();
    }

    public function getSectionsByClass(int $classId): Collection
    {
        return Section::where('class_id', $classId)->get();
    }

    public function getSubjectsByClass(int $classId): Collection
    {
        $class = SchoolClass::find($classId);
        return $class ? $class->subjects : new Collection();
    }

    public function getTimetableForSection(int $sectionId, int $sessionId): Collection
    {
        return Timetable::where('section_id', $sectionId)
            ->where('session_id', $sessionId)
            ->with(['subject', 'staffProfile.user', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }
}
