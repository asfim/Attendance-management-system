<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ledger extends Model
{
    protected $fillable = ['code', 'name', 'type'];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'ledger_id');
    }

    public function getBalanceAttribute(): float
    {
        $debits = $this->transactions()->where('type', 'debit')->sum('amount');
        $credits = $this->transactions()->where('type', 'credit')->sum('amount');

        // Assets and Expenses increase on Debit, decrease on Credit.
        // Liabilities, Equity, and Revenue increase on Credit, decrease on Debit.
        if (in_array(strtolower($this->type), ['asset', 'expense'])) {
            return $debits - $credits;
        }

        return $credits - $debits;
    }
}
