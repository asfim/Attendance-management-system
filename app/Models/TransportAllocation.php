<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportAllocation extends Model
{
    protected $fillable = [
        'student_profile_id',
        'route_id',
        'stop_id',
        'vehicle_id',
        'driver_id',
        'monthly_fee',
        'effective_from',
        'effective_to',
        'status',
        'remarks',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'monthly_fee' => 'decimal:2',
    ];

    public function stop(): BelongsTo
    {
        return $this->belongsTo(TransportRouteStop::class, 'stop_id');
    }

    public function studentProfile(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id');
    }
}
