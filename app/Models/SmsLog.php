<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class SmsLog extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'phone_number', 'message', 'status'];
}
