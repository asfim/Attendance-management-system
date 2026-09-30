<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeDiscount extends Model
{
    protected $fillable = ['name', 'type', 'value'];

    protected $casts = [
        'value' => 'decimal:2',
    ];
}
