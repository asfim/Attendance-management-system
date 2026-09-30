<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryPurchase extends Model
{
    protected $table = 'inventory_purchases';

    protected $fillable = [
        'supplier_id', 'purchase_date', 'grand_total', 'status',
        'payment_status', 'payment_method', 'invoice_number', 'attachment', 'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'grand_total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(InventorySupplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryPurchaseItem::class, 'purchase_id');
    }
}
