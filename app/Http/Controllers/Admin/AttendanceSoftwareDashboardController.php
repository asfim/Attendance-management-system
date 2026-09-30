<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Branch;
use App\Models\LeaveApplication;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceSoftwareDashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = now()->toDateString();
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');

        // Staff Query
        $staffQuery = StaffProfile::where('status', 'active');
        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }
        if ($departmentId) {
            $staffQuery->where('department_id', $departmentId);
        }
        $totalEmployees = $staffQuery->count();

        // Today's Attendances
        $attendancesQuery = Attendance::where('attendance_date', $today)
            ->where('attendable_type', StaffProfile::class);
        if ($branchId) {
            $attendancesQuery->where('branch_id', $branchId);
        }
        if ($departmentId) {
            $attendancesQuery->where('department_id', $departmentId);
        }
        $todayAttendances = $attendancesQuery->get();

        $presentCount = $todayAttendances->whereIn('status', ['present', 'work_from_home'])->count();
        $lateCount = $todayAttendances->where('status', 'late')->count();
        $earlyLeaveCount = $todayAttendances->where('status', 'early_leave')->count();
        $halfDayCount = $todayAttendances->where('status', 'half_day')->count();
        $absentCount = max(0, $totalEmployees - ($presentCount + $lateCount + $earlyLeaveCount + $halfDayCount));

        // Leave Today
        $leaveTodayCount = LeaveApplication::where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->count();

        // Late Coming Summary
        $lateSummary = Attendance::with(['attendable.user', 'attendable.departmentRel'])
            ->where('attendance_date', $today)
            ->where('attendable_type', StaffProfile::class)
            ->where('status', 'late')
            ->orderBy('entry_time', 'desc')
            ->take(10)
            ->get();

        // Department-wise Attendance Breakdown
        $departments = Department::withCount(['staff' => function($q) {
            $q->where('status', 'active');
        }])->get();

        $departmentStats = [];
        foreach ($departments as $dept) {
            $pCount = Attendance::where('attendance_date', $today)
                ->where('department_id', $dept->id)
                ->whereIn('status', ['present', 'late', 'work_from_home'])
                ->count();
            $aCount = max(0, $dept->staff_count - $pCount);

            $departmentStats[] = [
                'department' => $dept->name,
                'total'      => $dept->staff_count,
                'present'    => $pCount,
                'absent'     => $aCount,
            ];
        }

        // Today's Check-in Graph (Hourly trend)
        $hourlyCheckins = [];
        for ($h = 7; $h <= 18; $h++) {
            $hourFormatted = sprintf('%02d', $h);
            $cnt = $todayAttendances->filter(function($att) use ($hourFormatted) {
                return $att->entry_time && str_starts_with($att->entry_time, $hourFormatted);
            })->count();

            $hourlyCheckins['labels'][] = $h > 12 ? ($h - 12) . ' PM' : $h . ' AM';
            $hourlyCheckins['data'][]   = $cnt;
        }

        // Monthly Attendance Summary
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $monthlyPresent = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('attendable_type', StaffProfile::class)
            ->whereIn('status', ['present', 'work_from_home'])
            ->count();
        $monthlyLate = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('attendable_type', StaffProfile::class)
            ->where('status', 'late')
            ->count();
        $monthlyAbsent = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('attendable_type', StaffProfile::class)
            ->where('status', 'absent')
            ->count();
        $monthlyLeave = Attendance::whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
            ->where('attendable_type', StaffProfile::class)
            ->where('status', 'leave')
            ->count();

        $branches = Branch::where('status', 'active')->get();
        $allDepartments = Department::where('status', 'active')->get();

        return view('admin.attendance_software.dashboard', compact(
            'totalEmployees',
            'presentCount',
            'absentCount',
            'lateCount',
            'earlyLeaveCount',
            'halfDayCount',
            'leaveTodayCount',
            'lateSummary',
            'departmentStats',
            'hourlyCheckins',
            'monthlyPresent',
            'monthlyLate',
            'monthlyAbsent',
            'monthlyLeave',
            'branches',
            'allDepartments',
            'branchId',
            'departmentId'
        ));
    }
}
