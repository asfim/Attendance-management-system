<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'serial_number',
        'ip_address',
        'port',
        'comm_key',
        'location',
        'is_active',
        'status',
        'last_connected_at',
        'last_sync_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_active' => 'boolean',
            'last_connected_at' => 'datetime',
            'last_sync_at' => 'datetime',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(BiometricDeviceLog::class, 'device_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(DeviceSyncLog::class, 'device_id');
    }
}
