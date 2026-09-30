<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamType;
use App\Models\ExamSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExamController extends Controller
{
    public function index(Request $request)
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $activeSession = AcademicSession::where('is_active', true)->first() ?? $sessions->first();
        $selectedSessionId = $request->get('session_id', $activeSession?->id);

        $examTypes = ExamType::with(['academicSession', 'classes', 'examSchedules'])
            ->where('session_id', $selectedSessionId)
            ->get();

        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        $classrooms = Classroom::orderBy('room_number')->get();

        $schedules = collect();
        $selectedExamType = null;
        $selectedClass = null;

        if ($request->has('exam_type_id') && $request->has('class_id')) {
            $selectedExamType = $request->exam_type_id;
            $selectedClass = $request->class_id;
            $schedules = ExamSchedule::with(['examType', 'schoolClass', 'subject', 'classroom'])
                ->where('exam_type_id', $selectedExamType)
                ->where('class_id', $selectedClass)
                ->orderBy('exam_date')
                ->get();
        }

        $gradeRules = \App\Models\GradeRule::orderBy('point', 'desc')->get();
        $shifts = \App\Models\Shift::where('status', 'active')->get();

        return view('admin.exams.index', compact(
            'examTypes', 'classes', 'subjects', 'classrooms', 'sessions',
            'schedules', 'selectedExamType', 'selectedClass', 'selectedSessionId', 'gradeRules', 'shifts'
        ));
    }

    public function storeExamType(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exam_types')->where('session_id', $request->session_id)
            ],
            'session_id' => 'required|exists:academic_sessions,id',
            'status' => 'required|in:upcoming,ongoing,completed',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'exam_date' => 'required|date',
            'max_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:0',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'shift_id' => 'nullable|exists:shifts,id',
        ], [
            'name.unique' => 'An exam with this name already exists in the selected session.'
        ]);

        $examType = ExamType::create([
            'name' => $request->name,
            'session_id' => $request->session_id,
            'start_date' => $request->exam_date,
            'end_date' => $request->exam_date,
            'status' => $request->status,
        ]);
        
        $examType->classes()->attach($request->class_ids);

        foreach ($request->class_ids as $classId) {
            $schoolClass = SchoolClass::with('subjects')->find($classId);
            if ($schoolClass && $schoolClass->subjects->count() > 0) {
                foreach ($schoolClass->subjects as $subject) {
                    ExamSchedule::create([
                        'exam_type_id' => $examType->id,
                        'class_id' => $classId,
                        'subject_id' => $subject->id,
                        'exam_date' => $request->exam_date,
                        'max_marks' => $request->max_marks,
                        'pass_marks' => $request->pass_marks,
                        'classroom_id' => $request->classroom_id,
                        'shift_id' => $request->shift_id,
                    ]);
                }
            } else {
                ExamSchedule::create([
                    'exam_type_id' => $examType->id,
                    'class_id' => $classId,
                    'exam_date' => $request->exam_date,
                    'max_marks' => $request->max_marks,
                    'pass_marks' => $request->pass_marks,
                    'classroom_id' => $request->classroom_id,
                    'shift_id' => $request->shift_id,
                ]);
            }
        }

        return back()->with('success', 'Exam created successfully!');
    }

    public function updateExamType(Request $request, $id)
    {
        $examType = ExamType::findOrFail($id);
        
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('exam_types')->where('session_id', $examType->session_id)->ignore($examType->id)
            ],
            'status' => 'required|in:upcoming,ongoing,completed',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'exam_date' => 'required|date',
            'max_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:0',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'shift_id' => 'nullable|exists:shifts,id',
        ], [
            'name.unique' => 'An exam with this name already exists in the selected session.'
        ]);

        $examType->update($request->only(['name', 'status']));
        $examType->classes()->sync($request->class_ids);
        
        // Remove schedules for classes that are no longer assigned
        ExamSchedule::where('exam_type_id', $examType->id)
            ->whereNotIn('class_id', $request->class_ids)
            ->delete();

        // Create or update schedules for the assigned classes
        foreach ($request->class_ids as $classId) {
            $schoolClass = SchoolClass::with('subjects')->find($classId);
            
            if ($schoolClass && $schoolClass->subjects->count() > 0) {
                foreach ($schoolClass->subjects as $subject) {
                    ExamSchedule::updateOrCreate(
                        ['exam_type_id' => $examType->id, 'class_id' => $classId, 'subject_id' => $subject->id],
                        [
                            'exam_date' => $request->exam_date,
                            'max_marks' => $request->max_marks,
                            'pass_marks' => $request->pass_marks,
                            'classroom_id' => $request->classroom_id,
                            'shift_id' => $request->shift_id,
                        ]
                    );
                }
            } else {
                ExamSchedule::updateOrCreate(
                    ['exam_type_id' => $examType->id, 'class_id' => $classId, 'subject_id' => null],
                    [
                        'exam_date' => $request->exam_date,
                        'max_marks' => $request->max_marks,
                        'pass_marks' => $request->pass_marks,
                        'classroom_id' => $request->classroom_id,
                        'shift_id' => $request->shift_id,
                    ]
                );
            }
        }

        return back()->with('success', 'Exam updated successfully!');
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'exam_type_id' => 'required|exists:exam_types,id',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
            'exam_date' => 'required|date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'max_marks' => 'required|numeric|min:1',
            'pass_marks' => 'required|numeric|min:0',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'shift_id' => 'nullable|exists:shifts,id',
        ]);

        foreach ($request->class_ids as $classId) {
            ExamSchedule::create([
                'exam_type_id' => $request->exam_type_id,
                'class_id' => $classId,
                'exam_date' => $request->exam_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'max_marks' => $request->max_marks,
                'pass_marks' => $request->pass_marks,
                'classroom_id' => $request->classroom_id,
                'shift_id' => $request->shift_id,
            ]);
        }

        // Attach classes to the ExamType without detaching existing ones
        $examType = ExamType::find($request->exam_type_id);
        if ($examType) {
            $examType->classes()->syncWithoutDetaching($request->class_ids);
        }

        return back()->with('success', 'Exam schedules created successfully!');
    }

    public function destroyExamType($id)
    {
        $examType = ExamType::findOrFail($id);
        $examType->classes()->detach();
        $examType->examSchedules()->delete();
        $examType->delete();

        return back()->with('success', 'Exam deleted successfully!');
    }
}
