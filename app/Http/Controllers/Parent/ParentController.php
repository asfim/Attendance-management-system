<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\Invoice;
use App\Models\Timetable;
use App\Models\Notice;
use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentController extends Controller
{
    protected ExamService $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    public function dashboard()
    {
        $parent = Auth::user()->parentProfile;
        
        $children = StudentProfile::where('parent_id', $parent->id)
            ->with(['user', 'schoolClass', 'section'])
            ->get();

        $notices = Notice::whereIn('target_audience', ['all', 'parents'])
            ->orderBy('published_at', 'desc')
            ->take(5)
            ->get();

        return view('parent.dashboard', compact('parent', 'children', 'notices'));
    }

    public function childDetails($studentId)
    {
        $parent = Auth::user()->parentProfile;

        // Verify this student is indeed the parent's child (security check!)
        $student = StudentProfile::where('id', $studentId)
            ->where('parent_id', $parent->id)
            ->with(['user', 'schoolClass', 'section'])
            ->firstOrFail();

        $routine = Timetable::where('section_id', $student->section_id)
            ->where('session_id', $student->session_id)
            ->with(['subject', 'staffProfile.user', 'classroom'])
            ->get();

        $invoices = Invoice::where('student_profile_id', $student->id)->get();
        $examTypes = \App\Models\ExamType::where('session_id', $student->session_id)->get();

        return view('parent.child-details', compact('student', 'routine', 'invoices', 'examTypes'));
    }
}
