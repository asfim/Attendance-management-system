<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeworkController extends Controller
{
    public function index()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->studentProfile;
        
        $homeworks = \App\Models\Homework::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['subject', 'staff', 'submissions' => function($q) use ($student) {
                $q->where('student_profile_id', $student->id);
            }])
            ->latest()
            ->paginate(15);

        return view('student.homework.index', compact('homeworks'));
    }

    public function show(\App\Models\Homework $homework)
    {
        $student = \Illuminate\Support\Facades\Auth::user()->studentProfile;
        $submission = $homework->submissions()->where('student_profile_id', $student->id)->first();
        
        return view('student.homework.show', compact('homework', 'submission'));
    }

    public function submit(\Illuminate\Http\Request $request, \App\Models\Homework $homework)
    {
        $request->validate([
            'student_remarks' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png,zip|max:5120',
        ]);

        $student = \Illuminate\Support\Facades\Auth::user()->studentProfile;
        
        // Check if already submitted
        $submission = $homework->submissions()->where('student_profile_id', $student->id)->first();
        
        if ($submission && $submission->status === 'evaluated') {
            return back()->with('error', 'You cannot update an evaluated homework.');
        }

        $path = $submission->file_path ?? null;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('homework_submissions', 'public');
        }

        \App\Models\HomeworkSubmission::updateOrCreate(
            [
                'homework_id' => $homework->id,
                'student_profile_id' => $student->id,
            ],
            [
                'file_path' => $path,
                'student_remarks' => $request->student_remarks,
                'status' => 'submitted',
            ]
        );

        return redirect()->route('student.homework.index')->with('success', 'Homework submitted successfully!');
    }

    public function materials()
    {
        $student = \Illuminate\Support\Facades\Auth::user()->studentProfile;
        
        $materials = \App\Models\StudyMaterial::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->with(['subject', 'staff'])
            ->latest()
            ->paginate(15);

        return view('student.study_materials.index', compact('materials'));
    }

    public function showMaterial($id)
    {
        $student = \Illuminate\Support\Facades\Auth::user()->studentProfile;
        
        $material = \App\Models\StudyMaterial::where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->findOrFail($id);

        return view('student.study_materials.show', compact('material'));
    }
}
