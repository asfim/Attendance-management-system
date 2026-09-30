<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $fillable = [
        'leave_type_id', 'applicant_type', 'applicant_id',
        'start_date', 'end_date', 'reason', 'file_path',
        'status', 'approved_by', 'remarks'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function applicant()
    {
        return $this->morphTo();
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
