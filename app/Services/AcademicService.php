<?php

namespace App\Services;

use App\Repositories\Contracts\AcademicRepositoryInterface;

class AcademicService
{
    protected AcademicRepositoryInterface $academicRepository;

    public function __construct(AcademicRepositoryInterface $academicRepository)
    {
        $this->academicRepository = $academicRepository;
    }

    public function getActiveSession()
    {
        return $this->academicRepository->getActiveSession();
    }

    public function getAllClasses()
    {
        return $this->academicRepository->getAllClasses();
    }

    public function getSectionsForClass(int $classId)
    {
        return $this->academicRepository->getSectionsByClass($classId);
    }

    public function getSubjectsForClass(int $classId)
    {
        return $this->academicRepository->getSubjectsByClass($classId);
    }

    public function getTimetable(int $sectionId, int $sessionId)
    {
        return $this->academicRepository->getTimetableForSection($sectionId, $sessionId);
    }
}
