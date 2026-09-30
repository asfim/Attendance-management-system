<?php

namespace App\Services;

use App\Models\User;
use App\Models\Salary;
use App\Models\Ledger;
use App\Models\Transaction;
use App\Models\Attendance;
use App\Models\AdvanceSalary;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\StaffProfile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Calculate a full salary breakdown for a staff member for a given month/year.
     * Returns an array with all components.
     */
    public function calculateSalary(StaffProfile $staff, int $month, int $year): array
    {
        $settings    = PayrollSetting::current();
        $workingDays = $settings->working_days;

        // Load the month's attendances
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staff->id)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $presentDays = $attendances->where('status', 'present')->count()
            + $attendances->where('status', 'work_from_home')->count();
        $absentDays  = $attendances->where('status', 'absent')->count();
        $lateDays    = $attendances->where('status', 'late')->count();
        $leaveDays   = $attendances->where('status', 'leave')->count();
        $halfDays    = $attendances->where('status', 'half_day')->count();

        // Base salary data from staff profile
        $basic       = (float) $staff->salary;
        $allowances  = $staff->allowances->keyBy('name');

        // Per-day salary for deduction calculation
        $perDaySalary = $workingDays > 0 ? $basic / $workingDays : 0;

        // Absent deduction: sum of custom deduction amounts or default per-day calculation
        $absentDeduction = 0;
        foreach ($attendances->where('status', 'absent') as $att) {
            $absentDeduction += $att->salary_deduction ?? $perDaySalary;
        }

        // Half day deduction
        $halfDayDeduction = 0;
        if ($settings->half_day_deduction_enabled) {
            $rate             = $settings->half_day_deduction_rate / 100;
            $halfDayDeduction = $halfDays * $perDaySalary * $rate;
        }

        // Late deduction (using custom or fixed amount)
        $lateDeduction = 0;
        foreach ($attendances->where('status', 'late') as $att) {
            if ($att->salary_deduction !== null) {
                $lateDeduction += $att->salary_deduction;
            } elseif ($settings->late_deduction_per_day > 0) {
                $lateDeduction += $settings->late_deduction_per_day;
            }
        }
        $absentDeduction += $halfDayDeduction;

        // Pending advance salary to deduct this month
        $pendingAdvance   = AdvanceSalary::where('user_id', $staff->user_id)
            ->where('status', 'active')
            ->get();
        $advanceDeduction = 0;
        foreach ($pendingAdvance as $adv) {
            $advanceDeduction += min($adv->monthly_deduction, $adv->remainingAmount());
        }

        $allowances = $staff->allowances;
        
        $houseAllowance = 0;
        $medicalAllowance = 0;
        $transportAllowance = 0;
        $foodAllowance = 0;
        $mobileAllowance = 0;
        $internetAllowance = 0;
        $specialAllowance = 0;
        $festivalAllowance = 0;
        $otherAllowances = 0;

        foreach ($allowances as $a) {
            $n = strtolower($a->name);
            if (str_contains($n, 'house')) { $houseAllowance += $a->amount; }
            elseif (str_contains($n, 'medical')) { $medicalAllowance += $a->amount; }
            elseif (str_contains($n, 'transport')) { $transportAllowance += $a->amount; }
            elseif (str_contains($n, 'food')) { $foodAllowance += $a->amount; }
            elseif (str_contains($n, 'mobile')) { $mobileAllowance += $a->amount; }
            elseif (str_contains($n, 'internet')) { $internetAllowance += $a->amount; }
            elseif (str_contains($n, 'special')) { $specialAllowance += $a->amount; }
            elseif (str_contains($n, 'festival')) { $festivalAllowance += $a->amount; }
            else { $otherAllowances += $a->amount; }
        }

        $grossSalary = $basic
            + $houseAllowance + $medicalAllowance + $transportAllowance
            + $foodAllowance + $mobileAllowance + $internetAllowance
            + $specialAllowance + $festivalAllowance + $otherAllowances;

        $totalDeductions = $absentDeduction + $lateDeduction + $advanceDeduction;

        $netSalary = max(0, $grossSalary - $totalDeductions);

        return [
            'basic_salary'        => round($basic, 2),
            'house_allowance'     => round($houseAllowance, 2),
            'medical_allowance'   => round($medicalAllowance, 2),
            'transport_allowance' => round($transportAllowance, 2),
            'food_allowance'      => round($foodAllowance, 2),
            'mobile_allowance'    => round($mobileAllowance, 2),
            'internet_allowance'  => round($internetAllowance, 2),
            'special_allowance'   => round($specialAllowance, 2),
            'festival_allowance'  => round($festivalAllowance, 2),
            'other_allowances'    => round((float) $otherAllowances, 2),
            'gross_salary'        => round($grossSalary, 2),
            'absent_deduction'    => round($absentDeduction, 2),
            'late_deduction'      => round($lateDeduction, 2),
            'advance_deduction'   => round($advanceDeduction, 2),
            'loan_deduction'      => 0.00,
            'other_deduction'     => 0.00,
            'tax'                 => 0.00,
            'provident_fund'      => 0.00,
            'bonus'               => 0.00,
            'overtime'            => 0.00,
            'net_salary'          => round($netSalary, 2),
            'working_days'        => $workingDays,
            'present_days'        => $presentDays,
            'absent_days'         => $absentDays,
            'late_days'           => $lateDays,
            'leave_days'          => $leaveDays,
            'half_days'           => $halfDays,
            'per_day_salary'      => round($perDaySalary, 2),
        ];
    }

    /**
     * Generate or refresh a salary record for a specific staff member.
     */
    public function generateForStaff(StaffProfile $staff, int $month, int $year): Salary
    {
        $calc = $this->calculateSalary($staff, $month, $year);

        return Salary::updateOrCreate(
            ['user_id' => $staff->user_id, 'month' => $month, 'year' => $year],
            array_merge($calc, ['status' => 'pending', 'paid_amount' => 0])
        );
    }

    /**
     * Generate payroll for ALL active employees.
     */
    public function generateMonthlyPayroll(int $month, int $year): bool
    {
        $employees = User::whereHas('role', function ($query) {
            $query->whereNotIn('name', ['super_admin', 'admin', 'student', 'parent']);
        })->with('staffProfile.allowances')
            ->where('status', 'active')
            ->get();

        if ($employees->isEmpty()) {
            return false;
        }

        DB::transaction(function () use ($employees, $month, $year) {
            $expenseLedger = Ledger::firstOrCreate(
                ['code' => '5001'],
                ['name' => 'Salary Expense', 'type' => 'expense']
            );
            $payableLedger = Ledger::firstOrCreate(
                ['code' => '2001'],
                ['name' => 'Salaries Payable', 'type' => 'liability']
            );

            foreach ($employees as $emp) {
                if (!$emp->staffProfile) continue;

                $exists = Salary::where('user_id', $emp->id)
                    ->where('month', $month)
                    ->where('year', $year)
                    ->where('is_locked', false)
                    ->doesntExist();

                if (!$exists) continue;

                $salary = $this->generateForStaff($emp->staffProfile, $month, $year);

                if ($salary->net_salary <= 0) continue;

                Transaction::create([
                    'ledger_id'    => $expenseLedger->id,
                    'reference_no' => "PAYROLL-{$salary->id}",
                    'date'         => now()->format('Y-m-d'),
                    'type'         => 'debit',
                    'amount'       => $salary->net_salary,
                    'description'  => "Salary provision for {$emp->name} - {$month}/{$year}",
                ]);

                Transaction::create([
                    'ledger_id'    => $payableLedger->id,
                    'reference_no' => "PAYROLL-{$salary->id}",
                    'date'         => now()->format('Y-m-d'),
                    'type'         => 'credit',
                    'amount'       => $salary->net_salary,
                    'description'  => "Salary payable for {$emp->name} - {$month}/{$year}",
                ]);
            }
        });

        return true;
    }

    /**
     * Record a salary payment (supports partial payments).
     */
    public function recordPayment(int $salaryId, float $amount, string $method, string $reference, string $paidBy, string $notes = ''): PayrollPayment
    {
        return DB::transaction(function () use ($salaryId, $amount, $method, $reference, $paidBy, $notes) {
            $salary = Salary::findOrFail($salaryId);

            $payment = PayrollPayment::create([
                'salary_id'        => $salary->id,
                'payment_date'     => now()->toDateString(),
                'amount'           => $amount,
                'payment_method'   => $method,
                'reference_number' => $reference,
                'paid_by'          => $paidBy,
                'notes'            => $notes,
            ]);

            $newPaidAmount = (float) $salary->paid_amount + $amount;
            $newStatus     = $newPaidAmount >= (float) $salary->net_salary ? 'paid' : 'partial';

            $salary->update([
                'paid_amount'  => $newPaidAmount,
                'status'       => $newStatus,
                'payment_date' => now()->toDateString(),
            ]);

            // Update advance salary recovered amounts
            if ($newStatus === 'paid' && $salary->advance_deduction > 0) {
                $pendingAdvance = AdvanceSalary::where('user_id', $salary->user_id)
                    ->where('status', 'active')
                    ->get();
                foreach ($pendingAdvance as $adv) {
                    $recover = min($adv->monthly_deduction, $adv->remainingAmount());
                    $adv->increment('recovered_amount', $recover);
                    if ($adv->isFullyRecovered()) {
                        $adv->update(['status' => 'fully_recovered']);
                    }
                }
            }

            // Post to ledger
            $cashLedger    = Ledger::firstOrCreate(['code' => '1001'], ['name' => 'Cash Account', 'type' => 'asset']);
            $payableLedger = Ledger::firstOrCreate(['code' => '2001'], ['name' => 'Salaries Payable', 'type' => 'liability']);

            Transaction::create([
                'ledger_id'    => $payableLedger->id,
                'reference_no' => "SALPAY-{$payment->id}",
                'date'         => now()->format('Y-m-d'),
                'type'         => 'debit',
                'amount'       => $amount,
                'description'  => "Salary payment to {$salary->user->name}",
            ]);

            Transaction::create([
                'ledger_id'    => $cashLedger->id,
                'reference_no' => "SALPAY-{$payment->id}",
                'date'         => now()->format('Y-m-d'),
                'type'         => 'credit',
                'amount'       => $amount,
                'description'  => "Salary payout to {$salary->user->name} via {$method}",
            ]);

            return $payment;
        });
    }

    /**
     * Legacy pay method (full payment, for backwards compat).
     */
    public function paySalary(int $salaryId): bool
    {
        $salary = Salary::find($salaryId);
        if (!$salary || $salary->status === 'paid') return false;

        $remaining = $salary->remainingBalance();
        if ($remaining <= 0) return false;

        $this->recordPayment($salaryId, $remaining, 'cash', '', auth()->user()?->name ?? 'Admin');
        return true;
    }
}
