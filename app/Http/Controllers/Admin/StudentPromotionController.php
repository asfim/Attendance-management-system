<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\PromotionLog;
use App\Services\PromotionService;
use Illuminate\Support\Facades\Auth;

class StudentPromotionController extends Controller
{
    protected $promotionService;

    public function __construct(PromotionService $promotionService)
    {
        $this->promotionService = $promotionService;
    }

    public function index(Request $request)
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $classes = SchoolClass::with('sections')->orderBy('name')->get();
        $sections = Section::orderBy('name')->get();

        $students = collect();
        $targetSession = null;
        $targetClass = null;
        $targetSection = null;

        // If the admin submitted the filter form to load students
        if ($request->has('from_session_id')) {
            $students = $this->promotionService->getEligibleStudents(
                $request->from_session_id,
                $request->from_class_id,
                $request->from_section_id,
                $request->to_session_id,
                $request->to_class_id,
                $request->to_section_id,
                $request->from_shift_id
            );

            $targetSession = AcademicSession::find($request->to_session_id);
            $targetClass = SchoolClass::find($request->to_class_id);
            $targetSection = Section::find($request->to_section_id);
        }

        // Get recent logs to show history and rollback options
        $logs = PromotionLog::with(['student.user', 'oldSession', 'oldClass', 'newSession', 'newClass', 'promoter'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $shifts = \App\Models\Shift::where('status', 'active')->get();

        return view('admin.students.promotion.index', compact('sessions', 'classes', 'sections', 'students', 'targetSession', 'targetClass', 'targetSection', 'logs', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_session_id' => 'required|exists:academic_sessions,id',
            'from_class_id' => 'required|exists:classes,id',
            'from_section_id' => 'required|exists:sections,id',
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_class_id' => 'required|exists:classes,id',
            'to_section_id' => 'required|exists:sections,id',
            'to_shift_id' => 'nullable|exists:shifts,id',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:student_profiles,id',
        ]);

        // Prevent promoting to the exact same class/section/session
        if ($request->from_session_id == $request->to_session_id &&
            $request->from_class_id == $request->to_class_id &&
            $request->from_section_id == $request->to_section_id) {
            return back()->with('error', 'Cannot promote students to the exact same class, section, and academic year.');
        }

        try {
            $promotedCount = $this->promotionService->promoteStudents(
                $request->student_ids,
                $request->from_session_id,
                $request->from_class_id,
                $request->from_section_id,
                $request->to_session_id,
                $request->to_class_id,
                $request->to_section_id,
                $request->to_shift_id
            );

            return redirect()->route('admin.students.promotion.index')->with('success', "{$promotedCount} students promoted successfully!");
        } catch (\Exception $e) {
            return back()->with('error', 'Promotion failed: ' . $e->getMessage());
        }
    }

    public function rollback(Request $request, $logId)
    {
        $log = PromotionLog::findOrFail($logId);

        if ($log->status !== 'promoted') {
            return back()->with('error', 'This promotion log cannot be rolled back (already rolled back or graduated).');
        }

        try {
            $this->promotionService->rollbackPromotion($log);
            return back()->with('success', 'Promotion rolled back successfully for student: ' . $log->student->user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Rollback failed: ' . $e->getMessage());
        }
    }
}
