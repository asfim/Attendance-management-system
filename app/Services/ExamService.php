<?php

namespace App\Services;

use App\Repositories\Contracts\ExamRepositoryInterface;

class ExamService
{
    protected ExamRepositoryInterface $examRepository;

    public function __construct(ExamRepositoryInterface $examRepository)
    {
        $this->examRepository = $examRepository;
    }

    public function getSchedules(int $classId, int $examTypeId)
    {
        return $this->examRepository->getExamSchedulesByClass($classId, $examTypeId);
    }

    public function recordExamMarks(int $examScheduleId, array $marksData): bool
    {
        return $this->examRepository->recordMarks($examScheduleId, $marksData);
    }

    public function getGradeDetails(float $marksPercentage): array
    {
        if ($marksPercentage >= 80) {
            return ['gpa' => 5.00, 'grade' => 'A+', 'remarks' => 'Excellent'];
        } elseif ($marksPercentage >= 70) {
            return ['gpa' => 4.00, 'grade' => 'A', 'remarks' => 'Very Good'];
        } elseif ($marksPercentage >= 60) {
            return ['gpa' => 3.50, 'grade' => 'A-', 'remarks' => 'Good'];
        } elseif ($marksPercentage >= 50) {
            return ['gpa' => 3.00, 'grade' => 'B', 'remarks' => 'Satisfactory'];
        } elseif ($marksPercentage >= 40) {
            return ['gpa' => 2.00, 'grade' => 'C', 'remarks' => 'Pass'];
        } elseif ($marksPercentage >= 33) {
            return ['gpa' => 1.00, 'grade' => 'D', 'remarks' => 'Marginal'];
        } else {
            return ['gpa' => 0.00, 'grade' => 'F', 'remarks' => 'Fail'];
        }
    }

    public function getStudentReportCard(int $studentProfileId, int $examTypeId): array
    {
        $results = $this->examRepository->getStudentResults($studentProfileId, $examTypeId);
        $subjectGrades = [];
        $totalGpa = 0;
        $totalSubjects = 0;
        $hasFailed = false;

        foreach ($results as $result) {
            $obtained = (float) $result->marks_obtained;
            $max = (float) ($result->examSchedule?->max_marks ?? 100);
            $percentage = $max > 0 ? ($obtained / $max) * 100 : 0;
            $gradeInfo = $this->getGradeDetails($percentage);

            if ($gradeInfo['gpa'] == 0) {
                $hasFailed = true;
            }

            $subjectGrades[] = [
                'subject_name'   => $result->examSchedule?->subject?->name ?? 'Subject',
                'obtained_marks' => $obtained,
                'max_marks'      => $max,
                'percentage'     => $percentage,
                'gpa'            => $gradeInfo['gpa'],
                'grade'          => $gradeInfo['grade'],
            ];

            $totalGpa += $gradeInfo['gpa'];
            $totalSubjects++;
        }

        $cgpa = $totalSubjects > 0 ? round($totalGpa / $totalSubjects, 2) : 0.00;
        if ($hasFailed) {
            $cgpa = 0.00;
        }

        return [
            'results' => $subjectGrades,
            'cgpa' => $cgpa,
            'status' => $hasFailed ? 'Failed' : ($totalSubjects > 0 ? 'Passed' : 'N/A'),
        ];
    }
}
