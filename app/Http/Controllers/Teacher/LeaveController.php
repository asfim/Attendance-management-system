<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LeaveApplication;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Storage;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = LeaveApplication::where('applicant_type', \App\Models\StaffProfile::class)
            ->where('applicant_id', auth()->user()->staffProfile->id ?? 0)
            ->with(['leaveType', 'approver'])
            ->latest()
            ->paginate(10);

        return view('teacher.leaves.index', compact('leaves'));
    }

    public function create()
    {
        $leaveTypes = LeaveType::whereIn('applicable_to', ['staff', 'both'])->get();
        return view('teacher.leaves.create', compact('leaveTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,jpg,png|max:2048'
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('leaves/staff', 'public');
        }

        LeaveApplication::create([
            'leave_type_id' => $validated['leave_type_id'],
            'applicant_type' => \App\Models\StaffProfile::class,
            'applicant_id' => auth()->user()->staffProfile->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'file_path' => $filePath,
            'status' => 'pending'
        ]);

        return redirect()->route('teacher.leaves.index')->with('success', 'Leave application submitted successfully.');
    }
}
