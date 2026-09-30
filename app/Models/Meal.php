<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToSchool;

class Meal extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'time',
        'description',
        'status',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(FoodAttendance::class, 'meal_id');
    }
}
