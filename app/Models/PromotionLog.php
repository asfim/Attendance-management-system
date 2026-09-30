<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromotionLog extends Model
{
    protected $fillable = [
        'student_profile_id',
        'old_session_id',
        'old_class_id',
        'old_section_id',
        'old_roll_no',
        'new_session_id',
        'new_class_id',
        'new_section_id',
        'new_roll_no',
        'promoted_by',
        'status',
        'reason',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function oldSession()
    {
        return $this->belongsTo(AcademicSession::class, 'old_session_id');
    }

    public function oldClass()
    {
        return $this->belongsTo(SchoolClass::class, 'old_class_id');
    }

    public function oldSection()
    {
        return $this->belongsTo(Section::class, 'old_section_id');
    }

    public function newSession()
    {
        return $this->belongsTo(AcademicSession::class, 'new_session_id');
    }

    public function newClass()
    {
        return $this->belongsTo(SchoolClass::class, 'new_class_id');
    }

    public function newSection()
    {
        return $this->belongsTo(Section::class, 'new_section_id');
    }

    public function promoter()
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }
}
