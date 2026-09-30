<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\Timetable;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class AcademicController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::all();
        $classes = SchoolClass::with('sections')->get();
        $sections = Section::with('schoolClass')->get();
        $subjects = Subject::all();
        $classrooms = Classroom::all();
        $teachers = StaffProfile::whereHas('user.role', function($q) { $q->where('name', 'teacher'); })->with('user')->get();
        $timetables = Timetable::with(['academicSession', 'schoolClass', 'section', 'subject', 'staffProfile.user', 'classroom'])->get();
        $showSessionInReg = \App\Models\Setting::get('show_session_in_registration', '1');

        return view('admin.academics.index', compact('sessions', 'classes', 'sections', 'subjects', 'classrooms', 'teachers', 'timetables', 'showSessionInReg'));
    }

    public function updateSettings(Request $request)
    {
        \App\Models\Setting::set('show_session_in_registration', $request->has('show_session_in_registration') ? '1' : '0');
        return back()->with('success', 'Academic settings updated successfully!');
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:academic_sessions,name',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->is_active) {
            // Deactivate all sessions first
            AcademicSession::query()->update(['is_active' => false]);
        }

        AcademicSession::create([
            'name' => $request->name,
            'is_active' => $request->is_active ?? false,
        ]);

        return back()->with('success', 'Academic session created successfully!');
    }

    public function storeClass(Request $request)
    {
        if (!$request->has('code') || empty($request->code)) {
            $request->merge(['code' => strtolower(str_replace(' ', '-', $request->name))]);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'name' => 'required|string|unique:classes,name',
            'code' => 'required|string|unique:classes,code',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Failed! This class name already exists.');
        }

        SchoolClass::create($request->all());

        return back()->with('success', 'Class created successfully!');
    }

    public function storeSection(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string',
            'capacity' => 'required|integer|min:1',
        ]);

        Section::create($request->all());

        return back()->with('success', 'Section created successfully!');
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:subjects,code',
            'type' => 'required|in:theory,practical',
            'class_ids' => 'required|array',
            'class_ids.*' => 'exists:classes,id',
        ]);

        $subject = Subject::create($request->only(['name', 'code', 'type']));
        $subject->classes()->attach($request->class_ids);

        return back()->with('success', 'Subject created and assigned to classes!');
    }

    public function storeTimetable(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'classroom_id' => 'required|exists:classrooms,id',
            'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        Timetable::create($request->all());

        return back()->with('success', 'Timetable entry added successfully!');
    }

    public function destroyClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        
        // Optionally check if class has sections, students, etc before deleting
        if ($class->sections()->count() > 0) {
            return back()->with('error', 'Cannot delete class with existing sections. Please delete the sections first.');
        }

        $class->delete();
        
        return back()->with('success', 'Class deleted successfully!');
    }
}
