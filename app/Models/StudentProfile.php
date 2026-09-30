<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'biometric_id',
        'parent_id',
        'roll_no',
        'session_id',
        'class_id',
        'section_id',
        'admission_no',
        'admission_date',
        'dob',
        'gender',
        'blood_group',
        'medical_info',
        'qr_code',
        'status',
        'previous_school',
        'nationality',
        'religion',
        'birth_certificate',
        'nid',
        'passport',
        'house',
        'batch',
        'photo_path',
        'signature_path',
        'plain_password',
        'is_food_enabled',
        'shift_id',
    ];

    protected $casts = [
        'admission_date' => 'date',
        'dob' => 'date',
        'is_food_enabled' => 'boolean',
    ];

    public function foodAllocation(): HasOne
    {
        return $this->hasOne(StudentFood::class, 'student_profile_id')->latest();
    }
    
    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentEnrollment::class, 'student_profile_id');
    }
    
    public function promotionLogs(): HasMany
    {
        return $this->hasMany(PromotionLog::class, 'student_profile_id');
    }

    public function foodAllocations(): HasMany
    {
        return $this->hasMany(StudentFood::class, 'student_profile_id');
    }

    public function foodAttendances(): HasMany
    {
        return $this->hasMany(FoodAttendance::class, 'student_profile_id');
    }

    public function foodFeeAdjustments(): HasMany
    {
        return $this->hasMany(FoodFeeAdjustment::class, 'student_profile_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

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

    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function marksEntries(): HasMany
    {
        return $this->hasMany(MarksEntry::class, 'student_profile_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'student_profile_id');
    }

    public function hostelAllocation(): HasOne
    {
        return $this->hasOne(HostelAllocation::class, 'student_profile_id');
    }

    public function hostelAllocations(): HasMany
    {
        return $this->hasMany(HostelAllocation::class, 'student_profile_id');
    }

    public function transportAllocation(): HasOne
    {
        return $this->hasOne(TransportAllocation::class, 'student_profile_id')->latest();
    }

    public function transportAllocations(): HasMany
    {
        return $this->hasMany(TransportAllocation::class, 'student_profile_id');
    }



    public function transportHistories(): HasMany
    {
        return $this->hasMany(TransportHistory::class, 'student_profile_id')->latest('action_date');
    }

    public function photoUrl(): string
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        
        $name = urlencode($this->user->name ?? 'Student');
        return 'https://ui-avatars.com/api/?name=' . $name . '&background=random&color=fff&size=150';
    }
}
