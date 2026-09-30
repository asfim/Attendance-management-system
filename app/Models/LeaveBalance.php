<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_profile_id',
        'year',
        'casual_leave_quota',
        'casual_leave_used',
        'sick_leave_quota',
        'sick_leave_used',
        'annual_leave_quota',
        'annual_leave_used',
        'emergency_leave_quota',
        'emergency_leave_used',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(StaffProfile::class, 'staff_profile_id');
    }

    public function getCasualRemainingAttribute(): int
    {
        return max(0, $this->casual_leave_quota - $this->casual_leave_used);
    }

    public function getSickRemainingAttribute(): int
    {
        return max(0, $this->sick_leave_quota - $this->sick_leave_used);
    }

    public function getAnnualRemainingAttribute(): int
    {
        return max(0, $this->annual_leave_quota - $this->annual_leave_used);
    }

    public function getEmergencyRemainingAttribute(): int
    {
        return max(0, $this->emergency_leave_quota - $this->emergency_leave_used);
    }

    public function getTotalRemainingAttribute(): int
    {
        return $this->casual_remaining + $this->sick_remaining + $this->annual_remaining + $this->emergency_remaining;
    }
}
