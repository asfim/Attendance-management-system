<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\StudentProfile;

class LeaveController extends Controller
{
    public function index()
    {
        // Get all children of this parent
        $childrenIds = StudentProfile::where('guardian_id', auth()->id())->pluck('id');

        $leaves = LeaveApplication::where('applicant_type', StudentProfile::class)
            ->whereIn('applicant_id', $childrenIds)
            ->with(['leaveType', 'approver', 'applicant.user'])
            ->latest()
            ->paginate(10);

        return view('parent.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $children = StudentProfile::where('guardian_id', auth()->id())->with('user')->get();
        $leaveTypes = LeaveType::whereIn('applicable_to', ['student', 'both'])->get();
        return view('parent.leaves.create', compact('children', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:student_profiles,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,jpg,png|max:2048'
        ]);

        // Verify the student belongs to this parent
        $student = StudentProfile::where('id', $validated['student_id'])->where('guardian_id', auth()->id())->firstOrFail();

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('leaves/student', 'public');
        }

        LeaveApplication::create([
            'leave_type_id' => $validated['leave_type_id'],
            'applicant_type' => StudentProfile::class,
            'applicant_id' => $student->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'file_path' => $filePath,
            'status' => 'pending'
        ]);

        return redirect()->route('parent.leaves.index')->with('success', 'Leave application submitted successfully for ' . $student->user->name);
    }
}
