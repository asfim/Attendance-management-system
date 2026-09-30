<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAllowance extends Model
{
    protected $fillable = [
        'staff_profile_id',
        'name',
        'amount',
    ];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }
}
