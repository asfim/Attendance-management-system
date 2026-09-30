<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportHistory extends Model
{
    protected $fillable = [
        'student_profile_id',
        'route_id',
        'stop_id',
        'action',
        'old_value',
        'new_value',
        'action_date',
        'reason',
        'performed_by',
    ];

    protected $casts = [
        'action_date' => 'date',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function stop()
    {
        return $this->belongsTo(TransportRouteStop::class, 'stop_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
