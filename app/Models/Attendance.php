<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attendance extends Model
{
    protected $fillable = [
        'branch_id',
        'department_id',
        'shift_id',
        'attendance_date',
        'entry_time',
        'exit_time',
        'attendable_type',
        'attendable_id',
        'status', // present, absent, late, early_leave, half_day, leave, holiday, work_from_home, missing_punch
        'is_missing_punch',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'working_hours',
        'is_corrected',
        'correction_reason',
        'corrected_by',
        'remarks',
        'late_reason',
        'leave_reason',
        'leave_attachment',
        'approved_by',
        'salary_deduction',
    ];

    protected $casts = [
        'attendance_date'     => 'date',
        'salary_deduction'    => 'decimal:2',
        'working_hours'       => 'decimal:2',
        'is_missing_punch'    => 'boolean',
        'is_corrected'        => 'boolean',
        'late_minutes'        => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes'    => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function corrector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    /** Status color for calendar display */
    public function calendarColor(): string
    {
        return match ($this->status) {
            'present'        => '#22c55e',
            'absent'         => '#ef4444',
            'late'           => '#f97316',
            'early_leave'    => '#eab308',
            'half_day'       => '#a855f7',
            'leave'          => '#3b82f6',
            'holiday'        => '#6b7280',
            'work_from_home' => '#06b6d4',
            'missing_punch'  => '#dc2626',
            default          => '#9ca3af',
        };
    }

    /** Badge class for display */
    public function badgeClass(): string
    {
        return match ($this->status) {
            'present'        => 'bg-success',
            'absent'         => 'bg-danger',
            'late'           => 'bg-warning text-dark',
            'early_leave'    => 'bg-info text-dark',
            'half_day'       => 'bg-purple',
            'leave'          => 'bg-primary',
            'holiday'        => 'bg-secondary',
            'work_from_home' => 'bg-info text-dark',
            'missing_punch'  => 'bg-danger text-white fw-bold',
            default          => 'bg-secondary',
        };
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
