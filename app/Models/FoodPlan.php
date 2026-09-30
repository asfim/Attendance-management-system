<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class FoodPlan extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'academic_session_id',
        'school_class_id',
        'student_category',
        'monthly_fee',
        'billing_days',
        'effective_from',
        'effective_to',
        'status',
    ];

    protected $casts = [
        'monthly_fee'    => 'decimal:2',
        'billing_days'   => 'integer',
        'effective_from' => 'date',
        'effective_to'   => 'date',
    ];

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function studentFoods(): HasMany
    {
        return $this->hasMany(StudentFood::class, 'food_plan_id');
    }
}
