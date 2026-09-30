<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = ['vehicle_no', 'vehicle_model', 'capacity'];

    public function allocations(): HasMany
    {
        return $this->hasMany(TransportAllocation::class, 'vehicle_id');
    }
}
