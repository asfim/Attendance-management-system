<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSalary extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'reason',
        'date',
        'recovery_method',
        'installments',
        'monthly_deduction',
        'recovered_amount',
        'approved_by',
        'remarks',
        'status',
    ];

    protected $casts = [
        'date'              => 'date',
        'amount'            => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'recovered_amount'  => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remainingAmount(): float
    {
        return (float) $this->amount - (float) $this->recovered_amount;
    }

    public function isFullyRecovered(): bool
    {
        return $this->recovered_amount >= $this->amount;
    }
}
