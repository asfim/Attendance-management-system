<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    protected $fillable = ['name', 'type', 'installments_count'];

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class, 'fee_category_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'fee_category_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FeeInstallment::class, 'fee_category_id');
    }
}
