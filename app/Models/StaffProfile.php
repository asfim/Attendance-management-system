<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'department_id',
        'designation_id',
        'biometric_id',
        'fingerprint_id',
        'face_id',
        'phone',
        'address',
        'department',
        'designation',
        'joining_date',
        'salary',
        'overtime_rate',
        'status',
        'photo',
        'signature_path',
        'bangla_name',
        'gender',
        'dob',
        'blood_group',
        'religion',
        'marital_status',
        'national_id',
        'permanent_address',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employment_type',
        'experience',
        'qualifications',
    ];

    protected $casts = [
        'joining_date'  => 'date',
        'salary'        => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function departmentRel(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function designationRel(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function currentLeaveBalance(): HasOne
    {
        return $this->hasOne(LeaveBalance::class)->where('year', now()->year);
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(EmployeeTransfer::class);
    }

    public function allowances(): HasMany
    {
        return $this->hasMany(StaffAllowance::class);
    }

    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AttendanceNotification::class);
    }

    /** Total monthly gross (basic + all allowances) */
    public function grossSalary(): float
    {
        return (float) $this->salary + (float) $this->allowances->sum('amount');
    }

    /** Per-day salary based on working days */
    public function perDaySalary(int $workingDays = 26): float
    {
        return $workingDays > 0 ? round($this->grossSalary() / $workingDays, 2) : 0;
    }

    /** Per-hour salary based on standard 8-hour workday */
    public function perHourSalary(int $workingDays = 26): float
    {
        $daily = $this->perDaySalary($workingDays);
        return round($daily / 8, 2);
    }

    /** Employee ID formatted */
    public function employeeId(): string
    {
        return 'EMP-' . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

    /** Department Name Helper */
    public function getDepartmentNameAttribute(): string
    {
        return $this->departmentRel?->name ?? ($this->department ?? 'General');
    }

    /** Designation Title Helper */
    public function getDesignationTitleAttribute(): string
    {
        return $this->designationRel?->title ?? ($this->designation ?? 'Staff');
    }

    /** Branch Name Helper */
    public function getBranchNameAttribute(): string
    {
        return $this->branch?->name ?? 'Main Branch';
    }

    /** Photo URL with fallback */
    public function photoUrl(): string
    {
        return $this->photo
            ? asset('storage/' . $this->photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->user?->name ?? 'Staff') . '&background=6366f1&color=fff&size=80';
    }

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'staff_shift', 'staff_profile_id', 'shift_id');
    }
}
