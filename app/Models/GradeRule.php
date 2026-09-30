<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeRule extends Model
{
    protected $fillable = [
        'grade',
        'min_percent',
        'max_percent',
        'point'
    ];

    protected $casts = [
        'min_percent' => 'decimal:2',
        'max_percent' => 'decimal:2',
        'point' => 'decimal:2',
    ];

    /**
     * Get the grade and point for a given percentage.
     * 
     * @param float $percentage
     * @return \App\Models\GradeRule|null
     */
    public static function getGradeForPercentage($percentage)
    {
        return self::where('min_percent', '<=', $percentage)
            ->where('max_percent', '>=', $percentage)
            ->orderBy('point', 'desc')
            ->first();
    }
}
