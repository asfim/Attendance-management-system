<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudyMaterialController extends Controller
{
    public function index()
    {
        $teacher = \Illuminate\Support\Facades\Auth::user()->staffProfile;
        $materials = \App\Models\StudyMaterial::where('staff_profile_id', $teacher->id)
            ->with(['schoolClass', 'section', 'subject'])
            ->latest()
            ->paginate(15);
            
        return view('teacher.study_materials.index', compact('materials'));
    }

    public function create()
    {
        $classes = \App\Models\SchoolClass::all();
        $sections = \App\Models\Section::all();
        $subjects = \App\Models\Subject::all();
        $sessions = \App\Models\AcademicSession::all();
        return view('teacher.study_materials.create', compact('classes', 'sections', 'subjects', 'sessions'));
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
            'type' => 'required|in:pdf,video,link,document,image',
            'file' => 'nullable|file|max:10240',
            'link' => 'nullable|url',
        ]);

        $path = $request->link;
        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('study_materials', 'public');
        }

        $teacher = \Illuminate\Support\Facades\Auth::user()->staffProfile;

        \App\Models\StudyMaterial::create([
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'staff_profile_id' => $teacher->id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $path,
        ]);

        return redirect()->route('teacher.study-materials.index')->with('success', 'Study material uploaded successfully.');
    }

    public function edit(\App\Models\StudyMaterial $studyMaterial)
    {
        $classes = \App\Models\SchoolClass::all();
        $sections = \App\Models\Section::all();
        $subjects = \App\Models\Subject::all();
        $sessions = \App\Models\AcademicSession::all();
        return view('teacher.study_materials.edit', compact('studyMaterial', 'classes', 'sections', 'subjects', 'sessions'));
    }

    public function update(\Illuminate\Http\Request $request, \App\Models\StudyMaterial $studyMaterial)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:pdf,video,link,document,image',
            'file' => 'nullable|file|max:10240',
            'link' => 'nullable|url',
        ]);

        $path = $studyMaterial->file_path;
        if ($request->type === 'link') {
            if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            $path = $request->link;
        } elseif ($request->hasFile('file')) {
            if ($path && !filter_var($path, FILTER_VALIDATE_URL)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
            }
            $path = $request->file('file')->store('study_materials', 'public');
        }

        $studyMaterial->update([
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'subject_id' => $request->subject_id,
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'file_path' => $path,
        ]);

        return redirect()->route('teacher.study-materials.index')->with('success', 'Study material updated successfully.');
    }

    public function destroy(\App\Models\StudyMaterial $studyMaterial)
    {
        if ($studyMaterial->file_path && !filter_var($studyMaterial->file_path, FILTER_VALIDATE_URL)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($studyMaterial->file_path);
        }
        $studyMaterial->delete();
        return redirect()->route('teacher.study-materials.index')->with('success', 'Study material deleted.');
    }
}
