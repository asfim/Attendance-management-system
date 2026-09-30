<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricDeviceLog extends Model
{
    protected $fillable = [
        'device_id',
        'device_sn',
        'biometric_id',
        'punch_time',
        'punch_state',
        'verify_type',
        'unique_key',
        'user_type',
        'user_name',
        'status',
        'message',
        'raw_payload',
        'ip_address',
    ];

    protected $casts = [
        'punch_time' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }
}
