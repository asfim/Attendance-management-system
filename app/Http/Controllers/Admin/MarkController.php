<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\ExamSchedule;
use App\Models\MarksEntry;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use Illuminate\Http\Request;

class MarkController extends Controller
{
    public function index(Request $request)
    {
        $examTypes = ExamType::with('academicSession')->get();
        $classes = SchoolClass::orderBy('name')->get();

        $students = collect();
        $schedule = null;
        $schedules = collect();
        $selectedExamType = $request->exam_type_id;
        $selectedClass = $request->class_id;
        $selectedSubject = $request->subject_id;

        if ($selectedExamType && $selectedClass) {
            $schedules = ExamSchedule::with('subject')
                ->where('exam_type_id', $selectedExamType)
                ->where('class_id', $selectedClass)
                ->get();
        }

        if ($selectedExamType && $selectedClass && $selectedSubject) {
            $schedule = ExamSchedule::where('exam_type_id', $selectedExamType)
                ->where('class_id', $selectedClass)
                ->where('subject_id', $selectedSubject)
                ->first();

            if ($schedule) {
                $students = StudentProfile::with('user')
                    ->where('class_id', $selectedClass)
                    ->get()
                    ->map(function($student) use ($schedule) {
                        $entry = MarksEntry::where('exam_schedule_id', $schedule->id)
                            ->where('student_profile_id', $student->id)
                            ->first();
                        $student->marks_obtained = $entry ? $entry->marks_obtained : null;
                        $student->attendance_status = $entry ? $entry->attendance_status : 'present';
                        $student->remarks = $entry ? $entry->remarks : null;
                        return $student;
                    });
            }
        }

        return view('admin.marks.index', compact(
            'examTypes', 'classes', 'students', 'schedule', 'schedules',
            'selectedExamType', 'selectedClass', 'selectedSubject'
        ));
    }

    public function storeMarks(Request $request)
    {
        $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'marks' => 'required|array',
        ]);

        $scheduleId = $request->exam_schedule_id;

        foreach ($request->marks as $studentId => $data) {
            MarksEntry::updateOrCreate(
                [
                    'exam_schedule_id' => $scheduleId,
                    'student_profile_id' => $studentId,
                ],
                [
                    'marks_obtained' => $data['obtained'] ?? 0,
                    'attendance_status' => $data['attendance'] ?? 'present',
                    'remarks' => $data['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Marks saved successfully!');
    }
}
