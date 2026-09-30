<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hostel extends Model
{
    protected $fillable = ['name', 'type', 'address'];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class, 'hostel_id');
    }
}
