<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salary extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        // Basic
        'basic_salary',
        // Allowances
        'house_allowance',
        'medical_allowance',
        'transport_allowance',
        'food_allowance',
        'mobile_allowance',
        'internet_allowance',
        'special_allowance',
        'festival_allowance',
        'other_allowances',
        // Deductions
        'absent_deduction',
        'late_deduction',
        'loan_deduction',
        'advance_deduction',
        'other_deduction',
        'tax',
        'provident_fund',
        // Bonus/Extra
        'bonus',
        'overtime',
        'is_manual_overtime',
        // Legacy (kept for backward compat)
        'deductions',
        'net_salary',
        // Attendance counters
        'working_days',
        'present_days',
        'absent_days',
        'late_days',
        'leave_days',
        'half_days',
        // Payment
        'status',
        'payment_date',
        'payment_method',
        'payment_reference',
        'paid_amount',
        'is_locked',
        'notes',
    ];

    protected $casts = [
        'payment_date'       => 'date',
        'basic_salary'       => 'decimal:2',
        'house_allowance'    => 'decimal:2',
        'medical_allowance'  => 'decimal:2',
        'transport_allowance'=> 'decimal:2',
        'food_allowance'     => 'decimal:2',
        'mobile_allowance'   => 'decimal:2',
        'internet_allowance' => 'decimal:2',
        'special_allowance'  => 'decimal:2',
        'festival_allowance' => 'decimal:2',
        'other_allowances'   => 'decimal:2',
        'absent_deduction'   => 'decimal:2',
        'late_deduction'     => 'decimal:2',
        'loan_deduction'     => 'decimal:2',
        'advance_deduction'  => 'decimal:2',
        'other_deduction'    => 'decimal:2',
        'tax'                => 'decimal:2',
        'provident_fund'     => 'decimal:2',
        'bonus'              => 'decimal:2',
        'overtime'           => 'decimal:2',
        'deductions'         => 'decimal:2',
        'net_salary'         => 'decimal:2',
        'paid_amount'        => 'decimal:2',
        'is_locked'          => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayrollPayment::class);
    }

    /** Calculate gross salary (basic + all allowances + bonus + overtime) */
    public function grossSalary(): float
    {
        return (float) $this->basic_salary
            + (float) $this->house_allowance
            + (float) $this->medical_allowance
            + (float) $this->transport_allowance
            + (float) $this->food_allowance
            + (float) $this->mobile_allowance
            + (float) $this->internet_allowance
            + (float) $this->special_allowance
            + (float) $this->festival_allowance
            + (float) $this->other_allowances
            + (float) $this->bonus
            + (float) $this->overtime;
    }

    /** Calculate total deductions */
    public function totalDeductions(): float
    {
        return (float) $this->absent_deduction
            + (float) $this->late_deduction
            + (float) $this->loan_deduction
            + (float) $this->advance_deduction
            + (float) $this->other_deduction
            + (float) $this->tax
            + (float) $this->provident_fund;
    }

    /** Remaining balance to pay */
    public function remainingBalance(): float
    {
        return max(0, (float) $this->net_salary - (float) $this->paid_amount);
    }

    /** Status badge color */
    public function statusBadge(): string
    {
        return match ($this->status) {
            'paid'         => 'success',
            'partial'      => 'warning',
            'pending'      => 'secondary',
            'unpaid'       => 'danger',
            'locked'       => 'dark',
            default        => 'secondary',
        };
    }
}
