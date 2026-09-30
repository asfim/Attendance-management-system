<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Timetable;
use App\Models\ExamSchedule;
use App\Models\MarksEntry;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = Auth::user()->staffProfile;

        $assignedClasses = Timetable::where('staff_profile_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->get()
            ->groupBy(function ($item) {
                return $item->class_id . '-' . $item->section_id;
            })
            ->map(function ($group) {
                $first = $group->first();
                $first->subjects = $group->pluck('subject.name')->filter()->unique()->values();
                return $first;
            })
            ->values();

        return view('teacher.dashboard', compact('teacher', 'assignedClasses'));
    }

    public function timetable()
    {
        $teacher = Auth::user()->staffProfile;
        $timetable = Timetable::where('staff_profile_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject', 'classroom'])
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('teacher.timetable', compact('timetable'));
    }

    public function attendance(Request $request)
    {
        $teacher = Auth::user()->staffProfile;
        $classes = SchoolClass::all();
        $students = [];

        if ($request->filled('class_id') && $request->filled('section_id')) {
            $students = StudentProfile::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('status', 'active')
                ->with('user')
                ->get();
        }

        return view('teacher.attendance', compact('classes', 'students'));
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_profile_id' => 'required|exists:student_profiles,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        foreach ($request->attendance as $att) {
            Attendance::updateOrCreate(
                [
                    'attendance_date' => $request->date,
                    'attendable_type' => StudentProfile::class,
                    'attendable_id' => $att['student_profile_id'],
                ],
                [
                    'status' => $att['status'],
                    'remarks' => $att['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Attendance taken successfully!');
    }

    public function marks(Request $request)
    {
        $classes = SchoolClass::all();
        $schedules = [];
        $students = [];

        if ($request->filled('class_id')) {
            $schedules = ExamSchedule::where('class_id', $request->class_id)
                ->with('subject')
                ->get();
        }

        if ($request->filled('exam_schedule_id')) {
            $schedule = ExamSchedule::findOrFail($request->exam_schedule_id);
            $students = StudentProfile::where('class_id', $schedule->class_id)
                ->where('status', 'active')
                ->with(['user', 'marksEntries' => function ($q) use ($schedule) {
                    $q->where('exam_schedule_id', $schedule->id);
                }])
                ->get();
        }

        return view('teacher.marks', compact('classes', 'schedules', 'students'));
    }

    public function storeMarks(Request $request)
    {
        $request->validate([
            'exam_schedule_id' => 'required|exists:exam_schedules,id',
            'marks' => 'required|array',
            'marks.*.student_profile_id' => 'required|exists:student_profiles,id',
            'marks.*.marks_obtained' => 'nullable|numeric|min:0',
            'marks.*.attendance_status' => 'required|in:present,absent',
            'marks.*.remarks' => 'nullable|string|max:255',
        ]);

        foreach ($request->marks as $m) {
            MarksEntry::updateOrCreate(
                [
                    'exam_schedule_id' => $request->exam_schedule_id,
                    'student_profile_id' => $m['student_profile_id'],
                ],
                [
                    'marks_obtained' => $m['attendance_status'] === 'present' ? $m['marks_obtained'] : null,
                    'attendance_status' => $m['attendance_status'],
                    'remarks' => $m['remarks'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Exam marks recorded successfully!');
    }
}
