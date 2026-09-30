<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\AdvanceSalary;
use App\Models\Branch;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendancePayrollController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');

        $staffQuery = StaffProfile::with(['user', 'branch', 'departmentRel', 'allowances'])
            ->where('status', 'active');

        if ($branchId) {
            $staffQuery->where('branch_id', $branchId);
        }
        if ($departmentId) {
            $staffQuery->where('department_id', $departmentId);
        }

        $staffList = $staffQuery->get();

        $payrollSheets = [];
        $workingDaysInMonth = 26; // Standard 26 working days

        foreach ($staffList as $staff) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = $start->copy()->endOfMonth();

            $attendances = Attendance::where('attendable_type', StaffProfile::class)
                ->where('attendable_id', $staff->id)
                ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
                ->get();

            $presentDays  = $attendances->whereIn('status', ['present', 'work_from_home'])->count();
            $lateDays     = $attendances->where('status', 'late')->count();
            $absentDays   = $attendances->where('status', 'absent')->count();
            $leaveDays    = $attendances->where('status', 'leave')->count();
            $overtimeMins = $attendances->sum('overtime_minutes');
            $overtimeHours = round($overtimeMins / 60, 2);

            $basicSalary   = (float) $staff->salary;
            $dailyRate     = $workingDaysInMonth > 0 ? ($basicSalary / $workingDaysInMonth) : 0;
            $hourlyRate    = $dailyRate / 8;
            $overtimeRate  = $staff->overtime_rate > 0 ? (float) $staff->overtime_rate : ($hourlyRate * 1.5);

            // Calculations
            $absentDeduction = round($absentDays * $dailyRate, 2);
            $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2); // 3 Late = 1 day deduction rule
            $overtimePay     = round($overtimeHours * $overtimeRate, 2);

            $grossSalary = $basicSalary + (float)$staff->allowances->sum('amount');
            $netSalary   = max(0, $grossSalary + $overtimePay - $absentDeduction - $lateDeduction);

            $payrollSheets[] = [
                'staff'            => $staff,
                'basic_salary'     => $basicSalary,
                'gross_salary'     => $grossSalary,
                'present_days'     => $presentDays,
                'late_days'        => $lateDays,
                'absent_days'      => $absentDays,
                'leave_days'       => $leaveDays,
                'overtime_hours'   => $overtimeHours,
                'overtime_pay'     => $overtimePay,
                'absent_deduction' => $absentDeduction,
                'late_deduction'   => $lateDeduction,
                'net_salary'       => $netSalary,
            ];
        }

        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();

        return view('admin.attendance_software.payroll.index', compact('payrollSheets', 'month', 'year', 'branches', 'departments', 'branchId', 'departmentId'));
    }

    public function generatePayslip(Request $request, $staffId)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $staff = StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel', 'allowances'])->findOrFail($staffId);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays  = $attendances->whereIn('status', ['present', 'work_from_home'])->count();
        $lateDays     = $attendances->where('status', 'late')->count();
        $absentDays   = $attendances->where('status', 'absent')->count();
        $leaveDays    = $attendances->where('status', 'leave')->count();
        $overtimeMins = $attendances->sum('overtime_minutes');
        $overtimeHours = round($overtimeMins / 60, 2);

        $workingDaysInMonth = 26;
        $basicSalary   = (float) $staff->salary;
        $dailyRate     = $basicSalary / $workingDaysInMonth;
        $overtimeRate  = $staff->overtime_rate > 0 ? (float) $staff->overtime_rate : (($dailyRate / 8) * 1.5);

        $absentDeduction = round($absentDays * $dailyRate, 2);
        $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2);
        $overtimePay     = round($overtimeHours * $overtimeRate, 2);
        $allowancesTotal = (float) $staff->allowances->sum('amount');
        $grossSalary     = $basicSalary + $allowancesTotal;
        $netSalary       = max(0, $grossSalary + $overtimePay - $absentDeduction - $lateDeduction);

        $data = compact(
            'staff',
            'month',
            'year',
            'presentDays',
            'lateDays',
            'absentDays',
            'leaveDays',
            'overtimeHours',
            'overtimePay',
            'absentDeduction',
            'lateDeduction',
            'basicSalary',
            'allowancesTotal',
            'grossSalary',
            'netSalary'
        );

        if ($request->input('download') === 'pdf') {
            $pdf = Pdf::loadView('admin.attendance_software.payroll.payslip_pdf', $data);
            return $pdf->download("Payslip_{$staff->employeeId()}_{$month}_{$year}.pdf");
        }

        return view('admin.attendance_software.payroll.payslip', $data);
    }
}
