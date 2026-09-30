<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recruitment extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'job_title', 'department', 'no_of_vacancies', 'status'];

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
