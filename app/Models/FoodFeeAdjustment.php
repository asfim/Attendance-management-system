<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodFeeAdjustment extends Model
{
    protected $fillable = [
        'student_profile_id',
        'month',
        'year',
        'non_consumption_days',
        'per_day_cost',
        'calculated_deduction',
        'manual_adjustment',
        'final_food_fee',
        'adjusted_by',
        'reason',
    ];

    protected $casts = [
        'month'                => 'integer',
        'year'                 => 'integer',
        'non_consumption_days' => 'integer',
        'per_day_cost'         => 'decimal:2',
        'calculated_deduction' => 'decimal:2',
        'manual_adjustment'    => 'decimal:2',
        'final_food_fee'       => 'decimal:2',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
