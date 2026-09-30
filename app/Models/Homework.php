<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    protected $fillable = [
        'session_id', 'class_id', 'section_id', 'subject_id', 'staff_profile_id',
        'title', 'description', 'file_path', 'homework_date', 'due_date', 'max_marks'
    ];

    protected $casts = [
        'homework_date' => 'date',
        'due_date' => 'date',
    ];

    public function session() { return $this->belongsTo(AcademicSession::class); }
    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function section() { return $this->belongsTo(Section::class); }
    public function subject() { return $this->belongsTo(Subject::class); }
    public function staff() { return $this->belongsTo(StaffProfile::class, 'staff_profile_id'); }
    public function submissions() { return $this->hasMany(HomeworkSubmission::class); }
}
