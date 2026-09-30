<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = ['room_number', 'capacity'];

    public function timetables(): HasMany
    {
        return $this->hasMany(Timetable::class, 'classroom_id');
    }

    public function examSchedules(): HasMany
    {
        return $this->hasMany(ExamSchedule::class, 'classroom_id');
    }
}
