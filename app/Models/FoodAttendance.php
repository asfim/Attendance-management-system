<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodAttendance extends Model
{
    protected $fillable = [
        'student_profile_id',
        'meal_id',
        'attendance_date',
        'status', // taken, not_taken, leave, holiday, not_applicable
        'remarks',
    ];

    protected $casts = [
        'attendance_date' => 'date',
    ];

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class, 'meal_id');
    }
}
