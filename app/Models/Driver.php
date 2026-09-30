<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = ['name', 'phone', 'license_no'];

    public function allocations(): HasMany
    {
        return $this->hasMany(TransportAllocation::class, 'driver_id');
    }
}
