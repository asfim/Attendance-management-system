<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DonationCampaign extends Model
{
    protected $fillable = [
        'name',
        'goal_amount',
        'start_date',
        'end_date',
        'description',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'goal_amount' => 'decimal:2',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'campaign_id');
    }

    public function getCollectedAmountAttribute()
    {
        return $this->donations()->sum('amount');
    }

    public function getRemainingAmountAttribute()
    {
        return max(0, $this->goal_amount - $this->collected_amount);
    }
}
