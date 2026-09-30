<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = ['recruitment_id', 'candidate_name', 'candidate_email', 'scheduled_at', 'score', 'status'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'score' => 'decimal:2',
    ];

    public function recruitment(): BelongsTo
    {
        return $this->belongsTo(Recruitment::class);
    }
}
