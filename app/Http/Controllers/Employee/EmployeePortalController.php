<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Shift;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EmployeePortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::with(['branch', 'departmentRel', 'designationRel', 'shifts', 'currentLeaveBalance'])
            ->where('user_id', $user->id)
            ->first();

        if (!$staff) {
            // Fallback mock profile if user logged in doesn't have a staff record yet
            $staff = StaffProfile::first();
        }

        $today = now()->toDateString();
        $todayAttendance = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->where('attendance_date', $today)
            ->first();

        // Monthly Summary
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $monthlyAttendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays  = $monthlyAttendances->whereIn('status', ['present', 'work_from_home'])->count();
        $lateDays     = $monthlyAttendances->where('status', 'late')->count();
        $absentDays   = $monthlyAttendances->where('status', 'absent')->count();
        $leaveDays    = $monthlyAttendances->where('status', 'leave')->count();
        $overtimeMins = $monthlyAttendances->sum('overtime_minutes');
        $overtimeHours = round($overtimeMins / 60, 2);

        $lateHistory = $monthlyAttendances->where('status', 'late')->sortByDesc('attendance_date');

        // Leave Balance
        $leaveBalance = LeaveBalance::firstOrCreate(
            ['staff_profile_id' => $staff->id, 'year' => $year],
            ['casual_leave_quota' => 10, 'sick_leave_quota' => 14, 'annual_leave_quota' => 15, 'emergency_leave_quota' => 5]
        );

        $leaveTypes = LeaveType::all();
        $myLeaveApplications = LeaveApplication::with('leaveType')
            ->where('applicant_type', StaffProfile::class)
            ->where('applicant_id', $staff->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        // Salary info calculation
        $basicSalary   = (float) $staff->salary;
        $dailyRate     = $basicSalary / 26;
        $overtimeRate  = $staff->overtime_rate > 0 ? (float) $staff->overtime_rate : (($dailyRate / 8) * 1.5);
        $overtimePay   = round($overtimeHours * $overtimeRate, 2);
        $absentDeduction = round($absentDays * $dailyRate, 2);
        $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2);
        $netSalary     = max(0, $basicSalary + $overtimePay - $absentDeduction - $lateDeduction);

        $upcomingHolidays = Holiday::where('date', '>=', $today)
            ->orderBy('date', 'asc')
            ->take(5)
            ->get();

        return view('employee.dashboard', compact(
            'staff',
            'todayAttendance',
            'monthlyAttendances',
            'presentDays',
            'lateDays',
            'absentDays',
            'leaveDays',
            'overtimeHours',
            'lateHistory',
            'leaveBalance',
            'leaveTypes',
            'myLeaveApplications',
            'basicSalary',
            'overtimePay',
            'absentDeduction',
            'lateDeduction',
            'netSalary',
            'month',
            'year',
            'upcomingHolidays'
        ));
    }

    public function checkIn(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::where('user_id', $user->id)->first() ?? StaffProfile::first();

        $today = now()->toDateString();
        $time  = now()->toTimeString();

        $attendance = Attendance::firstOrNew([
            'attendable_type' => StaffProfile::class,
            'attendable_id'   => $staff->id,
            'attendance_date' => $today,
        ]);

        if ($attendance->entry_time) {
            return redirect()->back()->with('error', 'You have already checked in today at ' . $attendance->entry_time);
        }

        $shift = $staff->shifts->first() ?? Shift::first();
        $lateMinutes = 0;

        if ($shift) {
            $shiftStart = Carbon::parse($today . ' ' . $shift->start_time);
            $nowTime    = Carbon::now();
            if ($nowTime->gt($shiftStart->copy()->addMinutes($shift->grace_time_minutes ?? 15))) {
                $lateMinutes = $nowTime->diffInMinutes($shiftStart);
            }
        }

        $attendance->branch_id     = $staff->branch_id;
        $attendance->department_id = $staff->department_id;
        $attendance->shift_id      = $shift?->id;
        $attendance->entry_time    = $time;
        $attendance->status        = $lateMinutes > 0 ? 'late' : 'present';
        $attendance->late_minutes  = $lateMinutes;
        $attendance->save();

        return redirect()->back()->with('success', 'Check-in successful at ' . $time);
    }

    public function checkOut(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::where('user_id', $user->id)->first() ?? StaffProfile::first();

        $today = now()->toDateString();
        $time  = now()->toTimeString();

        $attendance = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->where('attendance_date', $today)
            ->first();

        if (!$attendance || !$attendance->entry_time) {
            return redirect()->back()->with('error', 'Please Check-In first before Checking-Out!');
        }

        $shift = $staff->shifts->first() ?? Shift::first();
        $earlyLeaveMinutes = 0;
        $overtimeMinutes = 0;
        $workingHours = 0;

        $userEntry = Carbon::parse($today . ' ' . $attendance->entry_time);
        $userExit  = Carbon::now();

        if ($shift) {
            $shiftEnd = Carbon::parse($today . ' ' . $shift->end_time);

            if ($userExit->lt($shiftEnd->copy()->subMinutes($shift->early_leave_before_minutes ?? 15))) {
                $earlyLeaveMinutes = $shiftEnd->diffInMinutes($userExit);
            }
            if ($userExit->gt($shiftEnd->copy()->addMinutes($shift->overtime_start_after_minutes ?? 30))) {
                $overtimeMinutes = $userExit->diffInMinutes($shiftEnd);
            }
        }

        $workingHours = round($userExit->diffInMinutes($userEntry) / 60, 2);

        $attendance->exit_time           = $time;
        $attendance->early_leave_minutes = $earlyLeaveMinutes;
        $attendance->overtime_minutes    = $overtimeMinutes;
        $attendance->working_hours       = $workingHours;
        if ($earlyLeaveMinutes > 0 && $attendance->status !== 'late') {
            $attendance->status = 'early_leave';
        }
        $attendance->save();

        return redirect()->back()->with('success', 'Check-out successful at ' . $time);
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:1000',
        ]);

        $user = auth()->user();
        $staff = StaffProfile::where('user_id', $user->id)->first() ?? StaffProfile::first();

        LeaveApplication::create([
            'leave_type_id'  => $request->leave_type_id,
            'applicant_type' => StaffProfile::class,
            'applicant_id'   => $staff->id,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'reason'         => $request->reason,
            'status'         => 'pending',
        ]);

        return redirect()->back()->with('success', 'Leave application submitted successfully for review!');
    }

    public function attendance(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::where('user_id', $user->id)->first() ?? StaffProfile::first();
        
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $monthlyAttendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('attendance_date', 'desc')
            ->get();

        $presentDays  = $monthlyAttendances->whereIn('status', ['present', 'work_from_home'])->count();
        $lateDays     = $monthlyAttendances->where('status', 'late')->count();
        $absentDays   = $monthlyAttendances->where('status', 'absent')->count();
        $leaveDays    = $monthlyAttendances->where('status', 'leave')->count();

        return view('employee.attendance', compact('staff', 'monthlyAttendances', 'month', 'year', 'presentDays', 'lateDays', 'absentDays', 'leaveDays'));
    }

    public function leaves(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::with('currentLeaveBalance')->where('user_id', $user->id)->first() ?? StaffProfile::first();
        
        $year = now()->year;
        $leaveBalance = LeaveBalance::firstOrCreate(
            ['staff_profile_id' => $staff->id, 'year' => $year],
            ['casual_leave_quota' => 10, 'sick_leave_quota' => 14, 'annual_leave_quota' => 15, 'emergency_leave_quota' => 5]
        );

        $leaveTypes = LeaveType::all();
        $myLeaveApplications = LeaveApplication::with('leaveType')
            ->where('applicant_type', StaffProfile::class)
            ->where('applicant_id', $staff->id)
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('employee.leaves', compact('staff', 'leaveBalance', 'leaveTypes', 'myLeaveApplications'));
    }

    public function holidays()
    {
        $holidays = Holiday::where('date', '>=', now()->startOfYear())
            ->orderBy('date', 'asc')
            ->get();
            
        return view('employee.holidays', compact('holidays'));
    }

    public function salary(Request $request)
    {
        $user = auth()->user();
        $staff = StaffProfile::where('user_id', $user->id)->first() ?? StaffProfile::first();
        
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $lateDays     = $attendances->where('status', 'late')->count();
        $absentDays   = $attendances->where('status', 'absent')->count();
        $overtimeMins = $attendances->sum('overtime_minutes');
        $overtimeHours = round($overtimeMins / 60, 2);

        $basicSalary   = (float) $staff->salary;
        $dailyRate     = $basicSalary / 26;
        $overtimeRate  = $staff->overtime_rate > 0 ? (float) $staff->overtime_rate : (($dailyRate / 8) * 1.5);
        $overtimePay   = round($overtimeHours * $overtimeRate, 2);
        $absentDeduction = round($absentDays * $dailyRate, 2);
        $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2);
        $netSalary     = max(0, $basicSalary + $overtimePay - $absentDeduction - $lateDeduction);

        return view('employee.salary', compact('staff', 'month', 'year', 'basicSalary', 'overtimePay', 'absentDeduction', 'lateDeduction', 'netSalary'));
    }
}
