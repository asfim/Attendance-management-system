<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySupplier extends Model
{
    protected $table = 'inventory_suppliers';

    protected $fillable = ['name', 'phone', 'email', 'address'];

    public function purchases(): HasMany
    {
        return $this->hasMany(InventoryPurchase::class, 'supplier_id');
    }
}
