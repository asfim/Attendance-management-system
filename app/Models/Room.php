<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = ['hostel_id', 'room_number', 'room_type', 'capacity', 'cost_per_bed'];

    protected $casts = [
        'cost_per_bed' => 'decimal:2',
    ];

    public function hostel(): BelongsTo
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class, 'room_id');
    }
}
