<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GatePass extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'user_id', 'pass_number', 'purpose', 'issued_at', 'expires_at'];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
