<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class AttendanceShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::withCount('staff')->get();
        $staffMembers = StaffProfile::with('user')->where('status', 'active')->get();

        return view('admin.attendance_software.shifts.index', compact('shifts', 'staffMembers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'code'                         => 'nullable|string|max:50',
            'shift_type'                   => 'nullable|in:general,morning,evening,night,flexible',
            'start_time'                   => 'required',
            'end_time'                     => 'required',
            'grace_time_minutes'           => 'nullable|integer|min:0',
            'late_mark_after_minutes'      => 'nullable|integer|min:0',
            'early_leave_before_minutes'   => 'nullable|integer|min:0',
            'overtime_start_after_minutes' => 'nullable|integer|min:0',
            'half_day_hours'               => 'nullable|numeric|min:0',
            'description'                  => 'nullable|string|max:500',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = strtoupper(\Illuminate\Support\Str::slug($request->name) . '-' . \Illuminate\Support\Str::random(4));
        }

        Shift::create($validated);

        return redirect()->back()->with('success', 'Shift created successfully!');
    }

    public function update(Request $request, $id)
    {
        $shift = Shift::findOrFail($id);
        $validated = $request->validate([
            'name'                         => 'required|string|max:255',
            'code'                         => 'nullable|string|max:50',
            'shift_type'                   => 'nullable|in:general,morning,evening,night,flexible',
            'start_time'                   => 'required',
            'end_time'                     => 'required',
            'grace_time_minutes'           => 'nullable|integer|min:0',
            'late_mark_after_minutes'      => 'nullable|integer|min:0',
            'early_leave_before_minutes'   => 'nullable|integer|min:0',
            'overtime_start_after_minutes' => 'nullable|integer|min:0',
            'half_day_hours'               => 'nullable|numeric|min:0',
            'description'                  => 'nullable|string|max:500',
        ]);

        if (empty($validated['code']) && empty($shift->code)) {
            $validated['code'] = strtoupper(\Illuminate\Support\Str::slug($request->name) . '-' . \Illuminate\Support\Str::random(4));
        }

        $shift->update($validated);

        return redirect()->back()->with('success', 'Shift updated successfully!');
    }

    public function assignShift(Request $request)
    {
        $request->validate([
            'shift_id'         => 'required|exists:shifts,id',
            'staff_profile_ids' => 'required|array',
            'staff_profile_ids.*' => 'exists:staff_profiles,id',
        ]);

        $shift = Shift::findOrFail($request->shift_id);
        foreach ($request->staff_profile_ids as $staffId) {
            $staff = StaffProfile::findOrFail($staffId);
            $staff->shifts()->syncWithoutDetaching([$shift->id]);
        }

        return redirect()->back()->with('success', 'Shift assigned to selected staff members!');
    }

    public function destroy($id)
    {
        $shift = Shift::findOrFail($id);
        $shift->delete();

        return redirect()->back()->with('success', 'Shift deleted successfully!');
    }
}
