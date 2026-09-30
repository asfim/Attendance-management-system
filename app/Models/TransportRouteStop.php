<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportRouteStop extends Model
{
    protected $fillable = [
        'route_id',
        'stop_name',
        'stop_order',
        'pickup_time',
        'drop_time',
        'additional_fee',
        'status'
    ];

    protected $casts = [
        'additional_fee' => 'decimal:2',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }
}
