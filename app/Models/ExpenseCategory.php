<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'description'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    // Helper attribute for dashboard/reports
    public function getTotalExpensesAttribute()
    {
        return $this->expenses()->sum('amount');
    }
}
