<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'item_id',
        'type', // 'in' or 'out'
        'quantity',
        'transaction_date',
        'reference',
        'department',
        'purpose',
        'issued_by',
        'received_by',
        'notes'
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
}
