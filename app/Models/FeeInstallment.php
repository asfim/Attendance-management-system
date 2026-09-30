<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_category_id',
        'name',
        'due_date',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'due_date' => 'date',
    ];

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }
}
