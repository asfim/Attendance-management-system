<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'sync_type',
        'started_at',
        'completed_at',
        'total_records',
        'new_records',
        'duplicate_records',
        'unmapped_records',
        'failed_records',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'total_records' => 'integer',
            'new_records' => 'integer',
            'duplicate_records' => 'integer',
            'unmapped_records' => 'integer',
            'failed_records' => 'integer',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'device_id');
    }
}
