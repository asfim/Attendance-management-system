<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentEnrollment extends Model
{
    protected $fillable = [
        'student_profile_id',
        'session_id',
        'class_id',
        'section_id',
        'roll_no',
        'admission_date',
        'status',
        'promotion_status',
        'previous_enrollment_id',
    ];

    protected $casts = [
        'admission_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(StudentProfile::class, 'student_profile_id');
    }

    public function session()
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function previousEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'previous_enrollment_id');
    }
}
