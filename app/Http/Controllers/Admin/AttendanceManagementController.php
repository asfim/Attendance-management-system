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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Setting;

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

    // Full Attendance History Log & Export
    public function history(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $staffId = $request->input('staff_id');
        $export = $request->input('export'); // 'csv' or 'pdf'

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

        // Export handling
        if ($export === 'csv') {
            $records = $query->orderBy('attendance_date', 'asc')->get();
            return $this->exportHistoryCsv($records, $month, $year, $staffId);
        }

        if ($export === 'pdf') {
            $records = $query->orderBy('attendance_date', 'asc')->get();
            return $this->exportHistoryPdf($records, $month, $year, $staffId);
        }

        $historyLogs = $query->orderBy('attendance_date', 'desc')->paginate(30);

        // Calculate individual summary if an employee is selected
        $selectedStaff = $staffId ? StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel', 'shifts'])->find($staffId) : null;
        $staffMonthlySummary = null;
        if ($selectedStaff) {
            $allStaffLogs = Attendance::where('attendable_type', StaffProfile::class)
                ->where('attendable_id', $staffId)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year)
                ->get();

            $staffMonthlySummary = [
                'total_days'        => $allStaffLogs->count(),
                'present'           => $allStaffLogs->whereIn('status', ['present', 'work_from_home'])->count(),
                'late'              => $allStaffLogs->where('status', 'late')->count(),
                'early_leave'       => $allStaffLogs->where('status', 'early_leave')->count(),
                'absent'            => $allStaffLogs->where('status', 'absent')->count(),
                'leave'             => $allStaffLogs->where('status', 'leave')->count(),
                'half_day'          => $allStaffLogs->where('status', 'half_day')->count(),
                'holiday'           => $allStaffLogs->where('status', 'holiday')->count(),
                'total_overtime_min'=> $allStaffLogs->sum('overtime_minutes'),
                'total_working_hrs' => round($allStaffLogs->sum('working_hours'), 1),
            ];
        }

        $companyName    = Setting::companyName();
        $companyAddress = Setting::companyAddress();
        $companyRegNo   = Setting::companyRegNo();
        $companyLogo    = Setting::companyLogo();

        return view('admin.attendance_software.attendance.history', compact(
            'historyLogs',
            'staffMembers',
            'month',
            'year',
            'staffId',
            'selectedStaff',
            'staffMonthlySummary',
            'companyName',
            'companyAddress',
            'companyRegNo',
            'companyLogo'
        ));
    }

    /**
     * Export attendance history as CSV (All or Individual Employee).
     */
    private function exportHistoryCsv($records, int $month, int $year, $staffId = null)
    {
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $selectedStaff = $staffId ? StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel'])->find($staffId) : null;
        $companyName   = Setting::companyName();
        $companyAddress = Setting::companyAddress();

        if ($selectedStaff) {
            $cleanName = \Illuminate\Support\Str::slug($selectedStaff->user?->name ?? 'employee', '_');
            $fileName = "attendance_history_{$cleanName}_{$monthName}_{$year}.csv";
        } else {
            $fileName = "attendance_history_all_{$monthName}_{$year}.csv";
        }

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=\"{$fileName}\"",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($records, $monthName, $year, $selectedStaff, $companyName, $companyAddress) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens it with proper encoding
            fputs($file, "\xEF\xBB\xBF");

            if ($selectedStaff) {
                // Individual Employee Header Details
                fputcsv($file, [$companyName]);
                if (!empty($companyAddress)) {
                    fputcsv($file, [$companyAddress]);
                }
                fputcsv($file, ['EMPLOYEE MONTHLY ATTENDANCE STATEMENT']);
                fputcsv($file, ['Employee Name:', $selectedStaff->user?->name ?? 'N/A', 'Employee ID:', $selectedStaff->employeeId(), 'Biometric ID:', $selectedStaff->biometric_id ?? 'N/A']);
                fputcsv($file, ['Department:', $selectedStaff->departmentName, 'Branch:', $selectedStaff->branchName, 'Designation:', $selectedStaff->designationTitle]);
                fputcsv($file, ['Month & Year:', "{$monthName} {$year}", 'Total Records:', $records->count()]);
                
                $presentCount = $records->whereIn('status', ['present', 'work_from_home'])->count();
                $lateCount    = $records->where('status', 'late')->count();
                $absentCount  = $records->where('status', 'absent')->count();
                $earlyCount   = $records->where('status', 'early_leave')->count();
                $leaveCount   = $records->where('status', 'leave')->count();
                $totOtHrs     = round($records->sum('overtime_minutes') / 60, 1);
                $totWorkHrs   = round($records->sum('working_hours'), 1);

                fputcsv($file, [
                    'Summary:',
                    "Present: {$presentCount}",
                    "Late: {$lateCount}",
                    "Early Leave: {$earlyCount}",
                    "Absent: {$absentCount}",
                    "Leave: {$leaveCount}",
                    "Overtime: {$totOtHrs} hrs",
                    "Total Working Hours: {$totWorkHrs} hrs"
                ]);
                fputcsv($file, []); // Blank separator row

                // Table Columns for Individual
                fputcsv($file, ['Date', 'Day', 'Shift', 'Status', 'Check In', 'Check Out', 'Late (Mins)', 'Early Leave (Mins)', 'Overtime (Mins)', 'Working Hours', 'Remarks']);

                foreach ($records as $row) {
                    fputcsv($file, [
                        $row->attendance_date?->format('Y-m-d'),
                        $row->attendance_date?->format('l'),
                        $row->shift?->name ?? 'General Shift',
                        strtoupper($row->status),
                        $row->entry_time ? date('h:i A', strtotime($row->entry_time)) : '--',
                        $row->exit_time ? date('h:i A', strtotime($row->exit_time)) : '--',
                        $row->late_minutes ?? 0,
                        $row->early_leave_minutes ?? 0,
                        $row->overtime_minutes ?? 0,
                        $row->working_hours ?? 0,
                        $row->remarks ?? '',
                    ]);
                }
            } else {
                // All Employees Header Details
                fputcsv($file, [$companyName]);
                if (!empty($companyAddress)) {
                    fputcsv($file, [$companyAddress]);
                }
                fputcsv($file, ['ALL EMPLOYEES ATTENDANCE HISTORY LOG']);
                fputcsv($file, ['Month & Year:', "{$monthName} {$year}", 'Total Records:', $records->count(), 'Exported At:', now()->toDateTimeString()]);
                fputcsv($file, []); // Blank separator row

                // Table Columns for All
                fputcsv($file, ['Date', 'Day', 'Employee ID', 'Biometric ID', 'Employee Name', 'Branch', 'Department', 'Designation', 'Shift', 'Status', 'Check In', 'Check Out', 'Late (Mins)', 'Early Leave (Mins)', 'Overtime (Mins)', 'Working Hours']);

                foreach ($records as $row) {
                    $staff = $row->attendable;
                    fputcsv($file, [
                        $row->attendance_date?->format('Y-m-d'),
                        $row->attendance_date?->format('l'),
                        $staff?->employeeId(),
                        $staff?->biometric_id ?? '--',
                        $staff?->user?->name ?? 'N/A',
                        $staff?->branchName ?? 'N/A',
                        $staff?->departmentName ?? 'N/A',
                        $staff?->designationTitle ?? 'N/A',
                        $row->shift?->name ?? 'General Shift',
                        strtoupper($row->status),
                        $row->entry_time ? date('h:i A', strtotime($row->entry_time)) : '--',
                        $row->exit_time ? date('h:i A', strtotime($row->exit_time)) : '--',
                        $row->late_minutes ?? 0,
                        $row->early_leave_minutes ?? 0,
                        $row->overtime_minutes ?? 0,
                        $row->working_hours ?? 0,
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export attendance history as PDF (All or Individual Employee).
     */
    private function exportHistoryPdf($records, int $month, int $year, $staffId = null)
    {
        $monthName = date('F', mktime(0, 0, 0, $month, 1));
        $selectedStaff = $staffId ? StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel', 'shifts'])->find($staffId) : null;
        $isIndividual = (bool) $selectedStaff;

        $companyName    = Setting::companyName();
        $companyAddress = Setting::companyAddress();
        $companyRegNo   = Setting::companyRegNo();
        $companyLogo    = Setting::companyLogo();

        $staffMonthlySummary = null;
        if ($selectedStaff) {
            $staffMonthlySummary = [
                'total_days'        => $records->count(),
                'present'           => $records->whereIn('status', ['present', 'work_from_home'])->count(),
                'late'              => $records->where('status', 'late')->count(),
                'early_leave'       => $records->where('status', 'early_leave')->count(),
                'absent'            => $records->where('status', 'absent')->count(),
                'leave'             => $records->where('status', 'leave')->count(),
                'half_day'          => $records->where('status', 'half_day')->count(),
                'holiday'           => $records->where('status', 'holiday')->count(),
                'total_overtime_min'=> $records->sum('overtime_minutes'),
                'total_working_hrs' => round($records->sum('working_hours'), 1),
            ];
            $cleanName = \Illuminate\Support\Str::slug($selectedStaff->user?->name ?? 'employee', '_');
            $fileName = "attendance_history_{$cleanName}_{$monthName}_{$year}.pdf";
        } else {
            $fileName = "attendance_history_all_{$monthName}_{$year}.pdf";
        }

        $pdf = Pdf::loadView('admin.attendance_software.attendance.history_pdf', compact(
            'records',
            'month',
            'year',
            'monthName',
            'selectedStaff',
            'isIndividual',
            'staffMonthlySummary',
            'companyName',
            'companyAddress',
            'companyRegNo',
            'companyLogo'
        ))
        ->setPaper('a4', $isIndividual ? 'portrait' : 'landscape')
        ->setOption('isRemoteEnabled', true)
        ->setOption('isHtml5ParserEnabled', true);

        return $pdf->download($fileName);
    }
}
