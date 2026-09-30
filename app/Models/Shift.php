<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Shift extends Model
{
    protected $fillable = [
        'name',
        'code',
        'shift_type',
        'start_time',
        'end_time',
        'grace_time_minutes',
        'late_mark_after_minutes',
        'early_leave_before_minutes',
        'overtime_start_after_minutes',
        'half_day_hours',
        'description',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function ($shift) {
            if (empty($shift->code)) {
                $slug = Str::slug($shift->name ?? 'SHIFT');
                $shift->code = strtoupper($slug . '-' . Str::random(4));
            }
        });
    }

    public function students(): HasMany
    {
        return $this->hasMany(StudentProfile::class, 'shift_id');
    }

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'shift_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(StaffProfile::class, 'staff_shift', 'shift_id', 'staff_profile_id');
    }
}

