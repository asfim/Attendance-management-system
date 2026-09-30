<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\Department;
use App\Models\AttendanceNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceManagementController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $staffQuery = StaffProfile::with(['user', 'branch', 'departmentRel', 'shifts']);
        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }
        if ($departmentId) {
            $staffQuery->where('department_id', $departmentId);
        }
        $staffMembers = $staffQuery->get();

        $attendances = Attendance::where('attendance_date', $date)
            ->where('attendable_type', StaffProfile::class)
            ->get()
            ->keyBy('attendable_id');

        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();

        return view('admin.attendance_software.attendance.index', compact('staffMembers', 'attendances', 'date', 'branches', 'departments', 'branchId', 'departmentId', 'status'));
    }

    // Manual Punch Check-in / Check-out or Save Attendance
    public function markAttendance(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'attendance_date'  => 'required|date',
            'status'           => 'required|in:present,absent,late,early_leave,half_day,leave,holiday,work_from_home,missing_punch',
            'entry_time'       => 'nullable|string',
            'exit_time'        => 'nullable|string',
            'remarks'          => 'nullable|string|max:500',
        ]);

        $staff = StaffProfile::with(['shifts', 'user'])->findOrFail($request->staff_profile_id);
        $shift = $staff->shifts->first() ?? Shift::first();

        // Calculate status, late minutes, early leave minutes, working hours
        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;
        $workingHours = 0;
        $isMissingPunch = false;

        $entry = $request->entry_time;
        $exit  = $request->exit_time;

        if ($entry && !$exit) {
            $isMissingPunch = true;
        }

        if ($entry && $exit && $shift) {
            $shiftStart = Carbon::parse($request->attendance_date . ' ' . $shift->start_time);
            $shiftEnd   = Carbon::parse($request->attendance_date . ' ' . $shift->end_time);
            $userEntry  = Carbon::parse($request->attendance_date . ' ' . $entry);
            $userExit   = Carbon::parse($request->attendance_date . ' ' . $exit);

            // Late Calculation
            if ($userEntry->gt($shiftStart->copy()->addMinutes($shift->grace_time_minutes ?? 15))) {
                $lateMinutes = $userEntry->diffInMinutes($shiftStart);
            }

            // Early Leave Calculation
            if ($userExit->lt($shiftEnd->copy()->subMinutes($shift->early_leave_before_minutes ?? 15))) {
                $earlyLeaveMinutes = $shiftEnd->diffInMinutes($userExit);
            }

            // Overtime Calculation
            if ($userExit->gt($shiftEnd->copy()->addMinutes($shift->overtime_start_after_minutes ?? 30))) {
                $overtimeMinutes = $userExit->diffInMinutes($shiftEnd);
            }

            // Working Hours
            $workingHours = round($userExit->diffInMinutes($userEntry) / 60, 2);
        }

        $attendance = Attendance::updateOrCreate(
            [
                'attendable_type' => StaffProfile::class,
                'attendable_id'   => $staff->id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'branch_id'           => $staff->branch_id,
                'department_id'       => $staff->department_id,
                'shift_id'            => $shift?->id,
                'status'              => $request->status,
                'entry_time'          => $entry,
                'exit_time'           => $exit,
                'is_missing_punch'    => $isMissingPunch,
                'late_minutes'        => $lateMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'overtime_minutes'    => $overtimeMinutes,
                'working_hours'       => $workingHours,
                'remarks'             => $request->remarks,
            ]
        );

        // Auto Trigger Notification if Late or Absent
        if (in_array($request->status, ['late', 'absent'])) {
            AttendanceNotification::create([
                'staff_profile_id' => $staff->id,
                'type'             => $request->status,
                'channel'          => 'sms',
                'recipient'        => $staff->phone,
                'message'          => "Dear {$staff->user?->name}, your attendance status for {$request->attendance_date} is marked as " . strtoupper($request->status) . ".",
                'status'           => 'sent',
            ]);
        }

        return redirect()->back()->with('success', 'Attendance marked successfully!');
    }

    // Missing Punches View
    public function missingPunches()
    {
        $missingPunches = Attendance::with([
            'attendable' => function ($morphTo) {
                $morphTo->morphWith([
                    StaffProfile::class => ['user', 'branch', 'departmentRel', 'designationRel'],
                ]);
            },
            'branch'
        ])
            ->where('attendable_type', StaffProfile::class)
            ->where(function($q) {
                $q->where('is_missing_punch', true)
                  ->orWhereNull('exit_time');
            })
            ->where('status', '!=', 'absent')
            ->orderBy('attendance_date', 'desc')
            ->paginate(20);

        return view('admin.attendance_software.attendance.missing_punches', compact('missingPunches'));
    }

    // Attendance Correction Request / Adjustment Approval
    public function corrections()
    {
        $corrections = Attendance::with([
            'attendable' => function ($morphTo) {
                $morphTo->morphWith([
                    StaffProfile::class => ['user', 'branch', 'departmentRel', 'designationRel'],
                ]);
            },
            'branch',
            'corrector'
        ])
            ->where('attendable_type', StaffProfile::class)
            ->where(function($q) {
                $q->where('is_corrected', true)
                  ->orWhereNotNull('correction_reason');
            })
            ->orderBy('attendance_date', 'desc')
            ->paginate(20);

        return view('admin.attendance_software.attendance.corrections', compact('corrections'));
    }

    public function approveCorrection(Request $request, $id)
    {
        $request->validate([
            'status'            => 'required|in:present,late,early_leave,half_day,leave',
            'entry_time'        => 'nullable|string',
            'exit_time'         => 'nullable|string',
            'correction_reason' => 'required|string|max:500',
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->status = $request->status;
        $attendance->entry_time = $request->entry_time ?? $attendance->entry_time;
        $attendance->exit_time = $request->exit_time ?? $attendance->exit_time;
        $attendance->is_corrected = true;
        $attendance->is_missing_punch = false;
        $attendance->correction_reason = $request->correction_reason;
        $attendance->corrected_by = auth()->id();
        $attendance->save();

        return redirect()->back()->with('success', 'Attendance correction applied successfully!');
    }

    // Full Attendance History Log
    public function history(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year  = $request->input('year', now()->year);
        $staffId = $request->input('staff_id');

        $staffMembers = StaffProfile::with(['user', 'branch', 'departmentRel'])->where('status', 'active')->get();

        $query = Attendance::with([
            'attendable' => function ($morphTo) {
                $morphTo->morphWith([
                    StaffProfile::class => ['user', 'branch', 'departmentRel', 'designationRel'],
                ]);
            },
            'branch',
            'department',
            'shift'
        ])
            ->where('attendable_type', StaffProfile::class)
            ->whereMonth('attendance_date', $month)
            ->whereYear('attendance_date', $year);

        if ($staffId) {
            $query->where('attendable_id', $staffId);
        }

        $historyLogs = $query->orderBy('attendance_date', 'desc')->paginate(30);

        return view('admin.attendance_software.attendance.history', compact('historyLogs', 'staffMembers', 'month', 'year', 'staffId'));
    }
}
