<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function index()
    {
        $teacher = \Illuminate\Support\Facades\Auth::user()->staffProfile;
        $homeworks = \App\Models\Homework::where('staff_profile_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->latest()
            ->paginate(15);
            
        return view('teacher.homework.index', compact('homeworks'));
    }

    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $sections = \App\Models\Section::all();
        $subjects = \App\Models\Subject::all();
        $sessions = \App\Models\AcademicSession::all(); // simplified
        return view('teacher.homework.create', compact('classes', 'sections', 'subjects', 'sessions'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'homework_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:homework_date',
            'max_marks' => 'nullable|numeric|min:0',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:5120',
        ]);

        $path = null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('homeworks', 'public');
        }

        $teacher = \Illuminate\Support\Facades\Auth::user()->staffProfile;

        \App\Models\Homework::create([
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'staff_profile_id' => $teacher->id,
            'title' => $request->title,
            'description' => $request->description,
            'file_path' => $path,
            'homework_date' => $request->homework_date,
            'due_date' => $request->due_date,
            'max_marks' => $request->max_marks,
        ]);

        return redirect()->route('teacher.homework.index')->with('success', 'Homework created successfully.');
    }

    public function edit(\App\Models\Homework $homework)
    {
        // Add authorization check later
        $classes = \App\Models\SchoolClass::all();
        $sections = \App\Models\Section::all();
        $subjects = \App\Models\Subject::all();
        $sessions = \App\Models\AcademicSession::all();
        return view('teacher.homework.edit', compact('homework', 'classes', 'sections', 'subjects', 'sessions'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\Homework $homework)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'max_marks' => 'nullable|numeric|min:0',
            'file' => 'nullable|file|max:5120',
        ]);

        $path = $homework->file_path;
        if ($request->hasFile('file')) {
            if ($path) \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            $path = $request->file('file')->store('homeworks', 'public');
        }

        $homework->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'max_marks' => $request->max_marks,
            'file_path' => $path,
        ]);

        return redirect()->route('teacher.homework.index')->with('success', 'Homework updated.');
    }

    public function destroy(\App\Models\Homework $homework)
    {
        if ($homework->file_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($homework->file_path);
        }
        $homework->delete();
        return redirect()->route('teacher.homework.index')->with('success', 'Homework deleted.');
    }

    public function submissions(\App\Models\Homework $homework)
    {
        $submissions = $homework->submissions()->with('student.user')->get();
        return view('teacher.homework.submissions', compact('homework', 'submissions'));
    }

    public function evaluate(\Illuminate\Http\Request $request, \App\Models\HomeworkSubmission $submission)
    {
        $request->validate([
            'marks' => 'nullable|numeric|min:0|max:' . $submission->homework->max_marks,
            'teacher_remarks' => 'nullable|string',
            'status' => 'required|in:evaluated,pending'
        ]);

        $submission->update([
            'marks' => $request->marks,
            'teacher_remarks' => $request->teacher_remarks,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Submission evaluated successfully.');
    }
}
