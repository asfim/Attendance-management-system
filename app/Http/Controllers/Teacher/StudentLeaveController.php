<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveApplication;


class StudentLeaveController extends Controller
{
    public function index()
    {
        // For now, let class teachers view all student leaves or we can filter it later.
        $applications = LeaveApplication::where('applicant_type', \App\Models\StudentProfile::class)
            ->with(['leaveType', 'applicant.user'])
            ->latest()
            ->paginate(15);

        return view('teacher.student_leaves.index', compact('applications'));
    }

    public function update(Request $request, LeaveApplication $studentLeaf)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $validated['approved_by'] = auth()->id();

        $studentLeaf->update($validated);
        return back()->with('success', 'Student Leave Application ' . $validated['status'] . ' successfully.');
    }
}
