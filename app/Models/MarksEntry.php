<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarksEntry extends Model
{
    protected $fillable = [
        'exam_schedule_id',
        'student_profile_id',
        'marks_obtained',
        'attendance_status',
        'remarks',
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
    ];

    public function examSchedule(): BelongsTo
    {
        return $this->belongsTo(ExamSchedule::class, 'exam_schedule_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }
}
