<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveApplication;


class LeaveApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = LeaveApplication::with(['leaveType', 'applicant.user', 'approver']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $applications = $query->latest()->paginate(15);
        return view('admin.leave_applications.index', compact('applications'));
    }

    public function update(Request $request, LeaveApplication $leaveApplication)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'remarks' => 'nullable|string'
        ]);

        $validated['approved_by'] = auth()->id();

        $leaveApplication->update($validated);
        return back()->with('success', 'Leave Application ' . $validated['status'] . ' successfully.');
    }
}
