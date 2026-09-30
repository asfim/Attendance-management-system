<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;

class Complaint extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'complainant_type', 'complainant_id', 'subject', 'description', 'resolution', 'status'];
}
