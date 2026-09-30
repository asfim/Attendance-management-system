<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentFood extends Model
{
    protected $table = 'student_foods';

    protected $fillable = [
        'student_profile_id',
        'food_plan_id',
        'monthly_fee',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'start_date'  => 'date',
        'end_date'    => 'date',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function foodPlan(): BelongsTo
    {
        return $this->belongsTo(FoodPlan::class, 'food_plan_id');
    }
}
