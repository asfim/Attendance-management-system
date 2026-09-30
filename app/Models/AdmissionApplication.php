<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'class_id',
        'section_id',
        'name',
        'email',
        'admission_date',
        'dob',
        'gender',
        'blood_group',
        'medical_info',
        'photo_path',
        'signature_path',
        'parent_name',
        'parent_email',
        'parent_phone',
        'parent_occupation',
        'parent_address',
        'status',
        'shift_id',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'dob' => 'date',
    ];

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }
}
