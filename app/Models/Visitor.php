<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Visitor extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'phone', 'purpose', 'check_in', 'check_out'];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
}
