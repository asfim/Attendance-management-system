<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'session_id');
    }

    public function examTypes(): HasMany
    {
        return $this->hasMany(ExamType::class, 'session_id');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'session_id');
    }
}
