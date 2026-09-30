<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPayment extends Model
{
    protected $fillable = [
        'salary_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'paid_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class);
    }
}
