<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    protected $fillable = [
        'homework_id', 'student_profile_id', 'file_path',
        'student_remarks', 'teacher_remarks', 'marks', 'status'
    ];

    public function homework() { return $this->belongsTo(Homework::class); }
    public function student() { return $this->belongsTo(StudentProfile::class, 'student_profile_id'); }
}
