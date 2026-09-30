<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveType;

class LeaveTypeController extends Controller
{
    public function index()
    {
        $leaveTypes = LeaveType::latest()->get();
        return view('admin.leave_types.index', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'applicable_to' => 'required|in:staff,student,both',
        ]);

        LeaveType::create($validated);
        return redirect()->route('admin.leave-types.index')->with('success', 'Leave Type created successfully.');
    }

    public function update(Request $request, LeaveType $leaveType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'applicable_to' => 'required|in:staff,student,both',
        ]);

        $leaveType->update($validated);
        return redirect()->route('admin.leave-types.index')->with('success', 'Leave Type updated successfully.');
    }

    public function destroy(LeaveType $leaveType)
    {
        $leaveType->delete();
        return redirect()->route('admin.leave-types.index')->with('success', 'Leave Type deleted successfully.');
    }
}
