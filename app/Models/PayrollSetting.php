<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollSetting extends Model
{
    protected $fillable = [
        'school_id',
        'working_days',
        'office_start_time',
        'office_end_time',
        'grace_time_minutes',
        'late_deduction_minutes',
        'late_deduction_per_day',
        'absent_deduction_enabled',
        'half_day_deduction_enabled',
        'half_day_deduction_rate',
    ];

    protected $casts = [
        'absent_deduction_enabled'   => 'boolean',
        'half_day_deduction_enabled' => 'boolean',
        'late_deduction_per_day'     => 'decimal:2',
        'half_day_deduction_rate'    => 'decimal:2',
    ];

    /**
     * Get the active settings (school-scoped or global fallback).
     */
    public static function current(): self
    {
        $schoolId = auth()->user()?->school_id;

        return static::where('school_id', $schoolId)->first()
            ?? static::whereNull('school_id')->first()
            ?? new static([
                'working_days'               => 26,
                'office_start_time'          => '09:00:00',
                'office_end_time'            => '17:00:00',
                'grace_time_minutes'         => 15,
                'late_deduction_minutes'     => 30,
                'late_deduction_per_day'     => 0,
                'absent_deduction_enabled'   => true,
                'half_day_deduction_enabled' => true,
                'half_day_deduction_rate'    => 50,
            ]);
    }
}
