<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamType;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\ExamSchedule;
use App\Models\MarksEntry;
use App\Models\GradeRule;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $examTypes = ExamType::with('academicSession')->get();
        $classes = SchoolClass::orderBy('name')->get();

        $students = collect();
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;

        if ($selectedExamType && $selectedClass) {
            // Get all schedules (subjects) for this exam and class
            $schedules = ExamSchedule::with('subject')
                ->where('exam_type_id', $selectedExamType)
                ->where('class_id', $selectedClass)
                ->get();
                
            $scheduleIds = $schedules->pluck('id')->toArray();

            if (count($scheduleIds) > 0) {
                $students = StudentProfile::with(['user'])
                    ->where('class_id', $selectedClass)
                    ->get()
                    ->map(function ($student) use ($schedules, $scheduleIds) {
                        $marks = MarksEntry::whereIn('exam_schedule_id', $scheduleIds)
                            ->where('student_profile_id', $student->id)
                            ->get()
                            ->keyBy('exam_schedule_id');
                            
                        $student->subject_results = collect();
                        $student->total_marks_obtained = 0;
                        $student->total_max_marks = 0;
                        $student->total_point = 0;
                        $student->has_failed = false;

                        foreach ($schedules as $schedule) {
                            $entry = $marks->get($schedule->id);
                            
                            $maxMarks = $schedule->max_marks;
                            $obtained = $entry ? $entry->marks_obtained : null;
                            
                            $result = [
                                'subject' => $schedule->subject->name ?? 'Unknown',
                                'max_marks' => $maxMarks,
                                'obtained' => $obtained,
                                'is_absent' => $entry && $entry->attendance_status === 'absent',
                                'grade' => null,
                                'point' => null,
                                'is_pass' => false,
                            ];

                            if ($obtained !== null) {
                                $percentage = ($obtained / $maxMarks) * 100;
                                $grade = GradeRule::getGradeForPercentage($percentage);
                                
                                $result['grade'] = $grade ? $grade->grade : '-';
                                $result['point'] = $grade ? $grade->point : 0;
                                $result['is_pass'] = $obtained >= $schedule->pass_marks;
                                
                                if (!$result['is_pass']) {
                                    $student->has_failed = true;
                                }

                                $student->total_marks_obtained += $obtained;
                                $student->total_max_marks += $maxMarks;
                                $student->total_point += $result['point'];
                            } else {
                                $student->has_failed = true; // Incomplete
                            }
                            
                            $student->subject_results->push((object)$result);
                        }

                        // Calculate overall
                        $student->subject_count = $schedules->count();
                        if ($student->subject_count > 0 && $student->total_max_marks > 0) {
                            $student->gpa = $student->total_point / $student->subject_count;
                            $overallPercentage = ($student->total_marks_obtained / $student->total_max_marks) * 100;
                            $overallGrade = GradeRule::getGradeForPercentage($overallPercentage);
                            $student->overall_grade = $student->has_failed ? 'F' : ($overallGrade ? $overallGrade->grade : 'N/A');
                        } else {
                            $student->gpa = 0;
                            $student->overall_grade = 'N/A';
                        }
                        
                        return $student;
                    });
            }
        }

        return view('admin.results.index', compact(
            'examTypes', 'classes', 'students',
            'selectedExamType', 'selectedClass'
        ));
    }
}
