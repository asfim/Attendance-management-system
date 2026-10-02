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

        $userIds = $staffList->pluck('user_id');
        $salaries = Salary::whereIn('user_id', $userIds)
            ->where('month', $month)
            ->where('year', $year)
            ->get()
            ->keyBy('user_id');
            
        $advances = AdvanceSalary::whereIn('user_id', $userIds)
            ->where('status', 'active')
            ->selectRaw('user_id, SUM(amount - recovered_amount) as balance')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

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
            
            if (isset($salaries[$staff->user_id]) && $salaries[$staff->user_id]->is_manual_overtime) {
                $overtimePay = (float) $salaries[$staff->user_id]->overtime;
            } else {
                $overtimePay = round($overtimeHours * $overtimeRate, 2);
            }

            $grossSalary = $basicSalary + (float)$staff->allowances->sum('amount');
            
            $availableForDeduction = $grossSalary + $overtimePay - $absentDeduction - $lateDeduction;
            $advanceDeduction = 0;
            if (isset($advances[$staff->user_id]) && $advances[$staff->user_id]->balance > 0) {
                $advanceDeduction = min($advances[$staff->user_id]->balance, max(0, $availableForDeduction));
            }
            
            $netSalary   = max(0, $availableForDeduction - $advanceDeduction);

            $status = 'pending';
            if (isset($salaries[$staff->user_id]) && $salaries[$staff->user_id]->status === 'paid') {
                $status = 'paid';
            }

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
                'advance_deduction'=> $advanceDeduction,
                'net_salary'       => $netSalary,
                'status'           => $status,
                'paid_amount'      => isset($salaries[$staff->user_id]) ? (float)$salaries[$staff->user_id]->paid_amount : 0,
                'advance_balance'  => isset($advances[$staff->user_id]) ? (float)$advances[$staff->user_id]->balance : 0,
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

        $existingSalary = Salary::where('user_id', $staff->user_id)->where('month', $month)->where('year', $year)->first();
        if ($existingSalary && $existingSalary->is_manual_overtime) {
            $overtimePay = (float) $existingSalary->overtime;
        } else {
            $overtimePay = round($overtimeHours * $overtimeRate, 2);
        }
        $allowancesTotal = (float) $staff->allowances->sum('amount');
        $grossSalary     = $basicSalary + $allowancesTotal;

        $advances = AdvanceSalary::where('user_id', $staff->user_id)
            ->where('status', 'active')
            ->selectRaw('SUM(amount - recovered_amount) as balance')
            ->first();
        $advanceBalance = $advances ? (float) $advances->balance : 0;
        
        $availableForDeduction = $grossSalary + $overtimePay - $absentDeduction - $lateDeduction;
        $advanceDeduction = 0;
        if ($advanceBalance > 0) {
            $advanceDeduction = min($advanceBalance, max(0, $availableForDeduction));
        }

        $netSalary       = max(0, $availableForDeduction - $advanceDeduction);

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

    public function paySalary(Request $request, $staffId)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $staff = StaffProfile::with(['user', 'allowances'])->findOrFail($staffId);
        
        $existingSalary = Salary::where('user_id', $staff->user_id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $paymentAmount = (float) $request->input('payment_amount');
        $paymentMethod = $request->input('payment_method');
        $notes = $request->input('notes');

        if ($existingSalary && $existingSalary->status === 'paid') {
            return back()->with('error', 'Salary is already fully paid for this month.');
        }

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
        $dailyRate     = $workingDaysInMonth > 0 ? ($basicSalary / $workingDaysInMonth) : 0;
        $overtimeRate  = $staff->overtime_rate > 0 ? (float) $staff->overtime_rate : (($dailyRate / 8) * 1.5);

        $absentDeduction = round($absentDays * $dailyRate, 2);
        $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2);
        
        if ($existingSalary && $existingSalary->is_manual_overtime) {
            $overtimePay = (float) $existingSalary->overtime;
        } else {
            $overtimePay = round($overtimeHours * $overtimeRate, 2);
        }
        $allowancesTotal = (float) $staff->allowances->sum('amount');
        $grossSalary     = $basicSalary + $allowancesTotal;

        $advances = AdvanceSalary::where('user_id', $staff->user_id)
            ->where('status', 'active')
            ->get();
        $advanceBalance = $advances->sum(function($a) { return $a->amount - $a->recovered_amount; });

        $availableForDeduction = $grossSalary + $overtimePay - $absentDeduction - $lateDeduction;
        $advanceDeduction = 0;
        if ($advanceBalance > 0) {
            $advanceDeduction = min($advanceBalance, max(0, $availableForDeduction));
        }

        $netSalary       = max(0, $availableForDeduction - $advanceDeduction);

        if (!$existingSalary) {
            $existingSalary = Salary::create([
                'user_id' => $staff->user_id,
                'month' => $month,
                'year' => $year,
                'basic_salary' => $basicSalary,
                'absent_deduction' => $absentDeduction,
                'late_deduction' => $lateDeduction,
                'advance_deduction' => $advanceDeduction,
                'overtime' => $overtimePay,
                'net_salary' => $netSalary,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays,
                'status' => 'pending',
                'paid_amount' => 0
            ]);

            // Recover advance if any
            if ($advanceDeduction > 0) {
                $deducted = 0;
                foreach ($advances as $adv) {
                    if ($deducted >= $advanceDeduction) break;
                    $remainingAdv = $adv->amount - $adv->recovered_amount;
                    if ($remainingAdv > 0) {
                        $toDeduct = min($remainingAdv, $advanceDeduction - $deducted);
                        $adv->recovered_amount += $toDeduct;
                        if ($adv->recovered_amount >= $adv->amount) {
                            $adv->status = 'completed';
                        }
                        $adv->save();
                        $deducted += $toDeduct;
                    }
                }
            }
        }

        $remaining = max(0, $existingSalary->net_salary - $existingSalary->paid_amount);

        if ($paymentAmount > $remaining) {
            // Overpayment goes to advance
            $advanceAmount = $paymentAmount - $remaining;
            AdvanceSalary::create([
                'user_id' => $staff->user_id,
                'amount' => $advanceAmount,
                'reason' => 'Overpayment during payroll',
                'date' => now()->toDateString(),
                'recovery_method' => 'next_month',
                'installments' => 1,
                'monthly_deduction' => $advanceAmount,
                'status' => 'active',
            ]);
            
            // Record payment up to remaining
            $actualPayment = $remaining;
        } else {
            $actualPayment = $paymentAmount;
        }

        if ($actualPayment > 0) {
            \App\Models\PayrollPayment::create([
                'salary_id' => $existingSalary->id,
                'amount' => $actualPayment,
                'payment_method' => $paymentMethod,
                'paid_by' => auth()->user()?->name ?? 'Admin',
                'notes' => $notes,
            ]);

            $existingSalary->paid_amount += $actualPayment;
        }

        if ($existingSalary->paid_amount >= $existingSalary->net_salary) {
            $existingSalary->status = 'paid';
            $existingSalary->payment_date = now();
            $existingSalary->payment_method = $paymentMethod;
        } else {
            $existingSalary->status = 'partial';
        }

        $existingSalary->save();

        return back()->with('success', 'Payment processed successfully.');
    }

    public function updateOvertime(Request $request, $staffId)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $amount = (float) $request->input('overtime_pay');

        $staff = StaffProfile::findOrFail($staffId);
        
        $existingSalary = Salary::where('user_id', $staff->user_id)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingSalary && $existingSalary->status === 'paid') {
            return back()->with('error', 'Cannot edit overtime for a fully paid salary.');
        }

        if (!$existingSalary) {
            // We need to create a skeleton salary record to hold the manual overtime
            $workingDaysInMonth = 26;
            $basicSalary   = (float) $staff->salary;
            $dailyRate     = $workingDaysInMonth > 0 ? ($basicSalary / $workingDaysInMonth) : 0;
            
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
            
            $absentDeduction = round($absentDays * $dailyRate, 2);
            $lateDeduction   = round(($lateDays / 3) * $dailyRate, 2);

            $existingSalary = Salary::create([
                'user_id' => $staff->user_id,
                'month' => $month,
                'year' => $year,
                'basic_salary' => $basicSalary,
                'absent_deduction' => $absentDeduction,
                'late_deduction' => $lateDeduction,
                'advance_deduction' => 0,
                'overtime' => $amount,
                'is_manual_overtime' => true,
                'net_salary' => 0, // Will be recalculated dynamically later
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'leave_days' => $leaveDays,
                'status' => 'pending',
                'paid_amount' => 0
            ]);
        } else {
            $existingSalary->overtime = $amount;
            $existingSalary->is_manual_overtime = true;
            
            // Recalculate net salary dynamically
            $staff->load('allowances');
            $grossSalary = (float)$existingSalary->basic_salary + (float)$staff->allowances->sum('amount');
            $available = $grossSalary + $amount - (float)$existingSalary->absent_deduction - (float)$existingSalary->late_deduction;
            
            // Recalculate advance if needed
            $advances = AdvanceSalary::where('user_id', $staff->user_id)->where('status', 'active')->get();
            $advanceBalance = $advances->sum(function($a) { return $a->amount - $a->recovered_amount; });
            $advanceDeduction = 0;
            if ($advanceBalance > 0) {
                $advanceDeduction = min($advanceBalance, max(0, $available));
            }
            
            $existingSalary->advance_deduction = $advanceDeduction;
            $existingSalary->net_salary = max(0, $available - $advanceDeduction);
            $existingSalary->save();
        }

        return back()->with('success', 'Overtime manually updated.');
    }
}
