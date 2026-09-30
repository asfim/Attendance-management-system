<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransportRoute extends Model
{
    protected $fillable = [
        'route_name', 
        'route_code', 
        'start_point', 
        'end_point', 
        'description', 
        'default_monthly_fee', 
        'status', 
        'remarks'
    ];

    protected $casts = [
        'default_monthly_fee' => 'decimal:2',
    ];

    public function stops(): HasMany
    {
        return $this->hasMany(TransportRouteStop::class, 'route_id')->orderBy('stop_order');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(TransportAllocation::class, 'route_id');
    }
}
