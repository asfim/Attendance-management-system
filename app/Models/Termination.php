<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Termination extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'user_id', 'notice_date', 'termination_date', 'reason'];

    protected $casts = [
        'notice_date' => 'date',
        'termination_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
