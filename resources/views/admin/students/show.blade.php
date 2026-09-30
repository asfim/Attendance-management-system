@extends('layouts.app')

@section('content')
<style>
/* ========================================
   STUDENT SHOW PAGE — PREMIUM STYLES
========================================= */
.profile-hero {
    background: #ffffff;
    border: 1px solid var(--bs-border-color);
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
}
[data-bs-theme="dark"] .profile-hero {
    background: #111827;
    color: #f3f4f6;
}
.profile-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(0, 0, 0, 0.02) 0%, transparent 70%);
    border-radius: 50%;
}
.profile-hero::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, rgba(0, 0, 0, 0.01) 0%, transparent 70%);
    border-radius: 50%;
}
.student-avatar-wrapper {
    position: relative;
    width: 110px;
    height: 110px;
    flex-shrink: 0;
}
.student-avatar-wrapper img,
.student-avatar-wrapper .avatar-fallback {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid #0d6efd;
    object-fit: cover;
}
.avatar-ring {
    position: absolute;
    inset: -6px;
    border-radius: 50%;
    border: 2px dashed rgba(13, 110, 253, 0.4);
    animation: spin-slow 12s linear infinite;
}
@keyframes spin-slow {
    to { transform: rotate(360deg); }
}
.stat-pill {
    background: #ffffff;
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 10px 16px;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}
[data-bs-theme="dark"] .stat-pill {
    background: #1f2937;
    color: #f3f4f6;
}
.info-card {
    background: #ffffff;
    border: 1px solid var(--bs-border-color);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.02);
    color: #1e293b;
    transition: border-color 0.2s, box-shadow 0.2s;
}
[data-bs-theme="dark"] .info-card {
    background: #111827;
    color: #f3f4f6;
}
.info-card:hover {
    border-color: var(--bs-border-color);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.1);
}
.info-card .section-title {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #0f172a;
    font-weight: 700;
    border-bottom: 2px solid var(--bs-border-color);
    padding-bottom: 10px;
    margin-bottom: 14px;
}
[data-bs-theme="dark"] .info-card .section-title {
    color: #f3f4f6;
}
.info-row {
    display: flex;
    justify-content: space-between;
    padding: 7px 0;
    border-bottom: 1px solid var(--bs-border-color);
    font-size: 0.8rem;
}
.info-row:last-child {
    border-bottom: none;
}
.info-row .label {
    color: #475569;
    font-weight: 600;
}
[data-bs-theme="dark"] .info-row .label {
    color: #9ca3af;
}
.info-row .value {
    color: #0f172a;
    font-weight: 500;
    text-align: right;
    max-width: 60%;
}
[data-bs-theme="dark"] .info-row .value {
    color: #f3f4f6;
}
.info-card .form-control, 
.info-card .form-select {
    border: 1px solid var(--bs-border-color);
    background-color: #ffffff;
    color: #0f172a;
}
[data-bs-theme="dark"] .info-card .form-control, 
[data-bs-theme="dark"] .info-card .form-select {
    background-color: #1f2937;
    color: #f3f4f6;
}
.info-card option {
    background-color: #ffffff;
    color: #0f172a;
}
[data-bs-theme="dark"] .info-card option {
    background-color: #1f2937;
    color: #f3f4f6;
}
.info-card .form-control:focus, 
.info-card .form-select:focus {
    border-color: var(--bs-border-color);
    box-shadow: 0 0 0 0.15rem rgba(109, 109, 109, 0.15);
}


/* ID Card Styles */
.id-card-front {
    width: 340px;
    height: 215px;
    background: linear-gradient(var(--id-card-gradient-angle, 135deg), var(--id-card-bg-start, #1e293b) 0%, var(--id-card-bg-end, #0f172a) 100%);
    border: 2px solid var(--id-card-border, #38bdf8);
    border-radius: 16px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.5), 0 0 0 1px rgba(56,189,248,0.1);
    color: #fff;
    font-family: 'Inter', sans-serif;
}
.id-card-back {
    width: 340px;
    height: 215px;
    background: linear-gradient(var(--id-card-gradient-angle, 135deg), var(--id-card-bg-end, #0f172a) 0%, var(--id-card-bg-start, #1e293b) 100%);
    border: 2px solid var(--id-card-border, #38bdf8);
    border-radius: 16px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.5), 0 0 0 1px rgba(56,189,248,0.1);
    color: #fff;
    font-family: 'Inter', sans-serif;
}
.id-lbl {
    color: var(--id-card-label-text, #94a3b8);
}
.id-val {
    color: var(--id-card-value-text, #e2e8f0);
}
.id-primary-color {
    color: var(--id-card-primary-text, #38bdf8);
}
.id-blood-color {
    color: var(--id-card-blood-color, #ef4444);
}
.id-card-front .glow-orb {
    position: absolute;
    width: 100px;
    height: 100px;
    background: rgba(56, 189, 248, 0.1);
    filter: blur(30px);
    border-radius: 50%;
    top: -20px;
    right: -20px;
    pointer-events: none;
}
.id-card-front .stripe {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #38bdf8, #6366f1, #38bdf8);
    background-size: 200% 100%;
    animation: shimmer 3s linear infinite;
}
@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.photo-box {
    width: 65px;
    height: 78px;
    border-radius: 8px;
    border: 1.5px solid rgba(255,255,255,0.15);
    overflow: hidden;
    flex-shrink: 0;
    background: rgba(255,255,255,0.05);
}
.photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.barcode-lines {
    display: flex;
    gap: 2px;
    align-items: flex-end;
    height: 28px;
}
.barcode-lines span {
    background: #94a3b8;
    width: 2px;
    border-radius: 1px;
}
.print-btn {
    background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 100%);
    border: none;
    border-radius: 10px;
    color: #fff;
    padding: 10px 24px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s, transform 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.print-btn:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
</style>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold m-0">Student Profile</h5>
            <p class="text-muted fs-7 mb-0">Full details, ID card &amp; print options</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-pencil me-1"></i>Edit</a>
            <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <!-- HERO SECTION -->
    <div class="profile-hero p-4 mb-4 position-relative">
        <div class="row align-items-center g-4" style="position: relative; z-index: 1;">
            <!-- Avatar & Name -->
            <div class="col-md-5 d-flex align-items-center gap-4">
                <div class="student-avatar-wrapper">
                    <div class="avatar-ring"></div>
                    @if($student->photo_path)
                        <img src="{{ asset('storage/' . $student->photo_path) }}" alt="{{ $student->user->name }}">
                    @else
                        <div class="avatar-fallback d-flex align-items-center justify-content-center bg-gradient" style="background: linear-gradient(135deg, #1e3a5f, #0f172a);">
                            <i class="fa-solid fa-user text-primary" style="font-size: 2.5rem;"></i>
                        </div>
                    @endif
                </div>
                <div>
                    <h4 class="fw-bold mb-1" style="color: inherit;">{{ $student->user->name }}</h4>
                    <p class="text-muted fs-7 mb-2">{{ $student->user->email }}</p>
                    <span class="badge me-1" style="background: rgba(13,110,253,0.1); color:#0d6efd; border: 1px solid rgba(13,110,253,0.2); font-size:0.65rem;">
                        {{ $student->schoolClass->name }} — {{ $student->section->name }}
                    </span>
                    <span class="badge me-1" style="background: rgba(139,92,246,0.1); color:#8b5cf6; border: 1px solid rgba(139,92,246,0.2); font-size:0.65rem;">
                        Shift: {{ $student->shift->name ?? 'N/A' }}
                    </span>
                    <span class="badge" style="background: {{ $student->status === 'active' ? 'rgba(34,197,94,0.15)' : 'rgba(239,68,68,0.15)' }}; color: {{ $student->status === 'active' ? '#22c55e' : '#ef4444' }}; border: 1px solid {{ $student->status === 'active' ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }}; font-size:0.65rem;">
                        {{ ucfirst($student->status) }}
                    </span>
                </div>
            </div>

            <!-- Stats Pills -->
            <div class="col-md-7">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Roll No</div>
                            <div class="fw-bold" style="font-size:1.1rem; color: inherit;">{{ $student->roll_no }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Adm No</div>
                            <div class="fw-bold text-primary" style="font-size:0.8rem;">{{ $student->admission_no }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Session</div>
                            <div class="fw-bold" style="font-size:0.8rem; color: inherit;">{{ $student->academicSession->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Blood</div>
                            <div class="fw-bold" style="color:#ef4444; font-size:1.1rem;">{{ $student->blood_group ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4">
    <!-- FIRST ROW: Personal Information & Parent / Guardian (Side-by-side) -->
    <div class="row g-4 mb-4">
        <!-- LEFT: Personal Information -->
        <div class="col-md-6">
            <div class="info-card p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-circle-info me-2"></i>Personal Information</div>
                <div class="info-row"><span class="label">Full Name</span><span class="value">{{ $student->user->name }}</span></div>
                <div class="info-row"><span class="label">Email</span><span class="value">{{ $student->user->email }}</span></div>
                <div class="info-row">
                    <span class="label">Password</span>
                    <span class="value d-flex align-items-center gap-2">
                        <span id="pw-mask">••••••••</span>
                        <span id="pw-plain" class="d-none fw-mono">{{ $student->plain_password ?? 'Not set' }}</span>
                        <button type="button" class="btn btn-link p-0 text-muted" style="font-size:0.8rem;" id="pw-toggle" title="Show/Hide Password">
                            <i class="fa-solid fa-eye" id="pw-toggle-icon"></i>
                        </button>
                    </span>
                </div>
                <div class="info-row"><span class="label">Date of Birth</span><span class="value">{{ $student->dob ? $student->dob->format('M d, Y') : '-' }}</span></div>
                <div class="info-row"><span class="label">Gender</span><span class="value">{{ $student->gender }}</span></div>
                <div class="info-row"><span class="label">Blood Group</span><span class="value text-danger fw-bold">{{ $student->blood_group ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Medical Info</span><span class="value">{{ $student->medical_info ?? 'None' }}</span></div>
                <div class="info-row"><span class="label">Admission No</span><span class="value"><span class="badge bg-primary">{{ $student->admission_no }}</span></span></div>
                <div class="info-row"><span class="label">Admission Date</span><span class="value">{{ $student->admission_date ? $student->admission_date->format('M d, Y') : '-' }}</span></div>
                <div class="info-row"><span class="label">Class / Section</span><span class="value">{{ $student->schoolClass->name }} / {{ $student->section->name }}</span></div>
                <div class="info-row"><span class="label">Shift</span><span class="value">{{ $student->shift->name ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Academic Session</span><span class="value">{{ $student->academicSession->name ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Roll Number</span><span class="value">{{ $student->roll_no }}</span></div>
                <div class="info-row" style="border:none;"><span class="label">Status</span><span class="value"><span class="badge" style="background: {{ $student->status === 'active' ? '#16a34a' : '#dc2626' }};">{{ ucfirst($student->status) }}</span></span></div>
            </div>
        </div>

        <!-- RIGHT: Parent / Guardian Info & Hostel Accommodation -->
        <div class="col-md-6 d-flex flex-column gap-4">
            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-people-roof me-2"></i>Parent / Guardian</div>
                @if($student->parent)
                    <div class="info-row"><span class="label">Name</span><span class="value">{{ $student->parent->user->name }}</span></div>
                    <div class="info-row"><span class="label">Email</span><span class="value">{{ $student->parent->user->email }}</span></div>
                    <div class="info-row"><span class="label">Phone</span><span class="value">{{ $student->parent->phone }}</span></div>
                    <div class="info-row"><span class="label">Occupation</span><span class="value">{{ $student->parent->occupation ?? '-' }}</span></div>
                    <div class="info-row" style="border:none;"><span class="label">Address</span><span class="value">{{ $student->parent->address ?? '-' }}</span></div>
                @else
                    <p class="text-muted fs-7 mb-0">No parent/guardian record found.</p>
                @endif
            </div>

            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-hotel me-2"></i>Hostel Accommodation</div>
                @php
                    $alloc = $student->hostelAllocation;
                @endphp
                @if($alloc && $alloc->status === 'active' && $alloc->bed)
                    <div class="info-row"><span class="label">Hall / House</span><span class="value fw-bold text-primary">{{ $alloc->bed->room?->hostel?->name ?? 'N/A' }}</span></div>
                    <div class="info-row"><span class="label">Hostel Type</span><span class="value">{{ $alloc->bed->room?->hostel?->type ?? '-' }}</span></div>
                    <div class="info-row"><span class="label">Room Number</span><span class="value">Room {{ $alloc->bed->room?->room_number }} ({{ $alloc->bed->room?->room_type }})</span></div>
                    <div class="info-row"><span class="label">Bed Number</span><span class="value fw-semibold">{{ $alloc->bed->bed_number }}</span></div>
                    <div class="info-row"><span class="label">Cost / Bed Fee</span><span class="value text-success fw-bold">${{ number_format($alloc->bed->room?->cost_per_bed ?? 0, 2) }}</span></div>
                    <div class="info-row" style="border:none;"><span class="label">Allocation Date</span><span class="value">{{ $alloc->allocation_date ? $alloc->allocation_date->format('M d, Y') : '-' }}</span></div>
                @else
                    <div class="text-muted fs-7 py-2">
                        <i class="fa-solid fa-circle-info me-1"></i>No active hostel bed allocated for this student.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- EXTRA ALLOCATIONS ROW: Transport & Food -->
    <div class="row g-4 mb-4">
        <!-- LEFT: Transport Allocation -->
        <div class="col-md-6">
            <div class="info-card p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-bus-simple me-2"></i>Transport Allocation</div>
                @php
                    $transportAlloc = $student->transportAllocation;
                @endphp
                @if($transportAlloc && $transportAlloc->status !== 'cancelled')
                    <div class="info-row"><span class="label">Route</span><span class="value fw-bold text-primary">{{ $transportAlloc->route?->name ?? 'N/A' }}</span></div>
                    <div class="info-row"><span class="label">Stop</span><span class="value">{{ $transportAlloc->stop?->name ?? 'N/A' }}</span></div>
                    <div class="info-row"><span class="label">Monthly Fee</span><span class="value text-success fw-bold">৳{{ number_format($transportAlloc->monthly_fee, 2) }}</span></div>
                    <div class="info-row"><span class="label">Effective From</span><span class="value">{{ $transportAlloc->effective_from ? \Carbon\Carbon::parse($transportAlloc->effective_from)->format('M d, Y') : '-' }}</span></div>
                    <div class="info-row" style="border:none;"><span class="label">Status</span><span class="value"><span class="badge bg-success bg-opacity-10 text-success border">Active</span></span></div>
                @else
                    <div class="text-muted fs-7 py-2">
                        <i class="fa-solid fa-circle-info me-1"></i>No active transport service allocated.
                    </div>
                @endif
            </div>
        </div>

        <!-- RIGHT: Food Allocation -->
        <div class="col-md-6">
            <div class="info-card p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-utensils me-2"></i>Food Service Allocation</div>
                @if($student->is_food_enabled && $student->foodAllocation && $student->foodAllocation->status === 'active')
                    <div class="info-row"><span class="label">Food Plan</span><span class="value fw-bold text-primary">{{ $student->foodAllocation->foodPlan?->name ?? 'N/A' }}</span></div>
                    <div class="info-row"><span class="label">Monthly Fee</span><span class="value text-success fw-bold">৳{{ number_format($student->foodAllocation->monthly_fee, 2) }}</span></div>
                    <div class="info-row"><span class="label">Start Date</span><span class="value">{{ $student->foodAllocation->start_date ? \Carbon\Carbon::parse($student->foodAllocation->start_date)->format('M d, Y') : '-' }}</span></div>
                    <div class="info-row" style="border:none;"><span class="label">Status</span><span class="value"><span class="badge bg-success bg-opacity-10 text-success border">Active</span></span></div>
                @else
                    <div class="text-muted fs-7 py-2">
                        <i class="fa-solid fa-circle-info me-1"></i>No active food service allocated.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== OVERALL ATTENDANCE & CALENDAR ===== --}}
    @php
        $totalPresent = collect($attendanceByDate)->where('status', 'present')->count();
        $totalLate    = collect($attendanceByDate)->where('status', 'late')->count();
        $totalAbsent  = collect($attendanceByDate)->where('status', 'absent')->count();
        $totalLeave   = collect($attendanceByDate)->where('status', 'leave')->count();
        $totalDays    = $totalPresent + $totalLate + $totalAbsent + $totalLeave;
        $attendRate   = $totalDays > 0 ? round((($totalPresent + $totalLate) / $totalDays) * 100, 1) : 0;
    @endphp
    <div class="row g-4 mt-0">
        <div class="col-12">
            <div class="info-card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="section-title mb-0"><i class="fa-solid fa-calendar-check me-2"></i>Attendance Tracker & Calendar</div>
                </div>

                <!-- OVERALL SUMMARY STATS -->
                <div class="row g-3 align-items-stretch mb-4">
                    <!-- Present -->
                    <div class="col-6 col-md-3">
                        <div style="background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.25); border-radius: 14px; padding: 18px 16px; text-align: center; position: relative; overflow: hidden;">
                            <div style="position:absolute;top:-18px;right:-18px;width:70px;height:70px;background:rgba(34,197,94,0.08);border-radius:50%;"></div>
                            <div style="font-size:2rem; font-weight:800; color:#16a34a; line-height:1;">{{ $totalPresent }}</div>
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:#16a34a; opacity:0.8; margin-top:4px; font-weight:600;">
                                <i class="fa-solid fa-circle-check me-1"></i>Present Days
                            </div>
                        </div>
                    </div>
                    <!-- Late -->
                    <div class="col-6 col-md-3">
                        <div style="background: rgba(234,179,8,0.1); border: 1px solid rgba(234,179,8,0.25); border-radius: 14px; padding: 18px 16px; text-align: center; position: relative; overflow: hidden;">
                            <div style="position:absolute;top:-18px;right:-18px;width:70px;height:70px;background:rgba(234,179,8,0.08);border-radius:50%;"></div>
                            <div style="font-size:2rem; font-weight:800; color:#ca8a04; line-height:1;">{{ $totalLate }}</div>
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:#ca8a04; opacity:0.8; margin-top:4px; font-weight:600;">
                                <i class="fa-solid fa-clock me-1"></i>Late Days
                            </div>
                        </div>
                    </div>
                    <!-- Absent -->
                    <div class="col-6 col-md-3">
                        <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.25); border-radius: 14px; padding: 18px 16px; text-align: center; position: relative; overflow: hidden;">
                            <div style="position:absolute;top:-18px;right:-18px;width:70px;height:70px;background:rgba(239,68,68,0.08);border-radius:50%;"></div>
                            <div style="font-size:2rem; font-weight:800; color:#dc2626; line-height:1;">{{ $totalAbsent }}</div>
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:#dc2626; opacity:0.8; margin-top:4px; font-weight:600;">
                                <i class="fa-solid fa-circle-xmark me-1"></i>Absent Days
                            </div>
                        </div>
                    </div>
                    <!-- Attendance Rate -->
                    <div class="col-6 col-md-3">
                        <div style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.25); border-radius: 14px; padding: 18px 16px; text-align: center; position: relative; overflow: hidden;">
                            <div style="position:absolute;top:-18px;right:-18px;width:70px;height:70px;background:rgba(99,102,241,0.08);border-radius:50%;"></div>
                            <div style="font-size:2rem; font-weight:800; color:#6366f1; line-height:1;">{{ $attendRate }}%</div>
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:#6366f1; opacity:0.8; margin-top:4px; font-weight:600;">
                                <i class="fa-solid fa-percent me-1"></i>Attendance Rate
                            </div>
                        </div>
                    </div>
                </div>

                @if($totalDays > 0)
                <!-- Stacked Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.75rem; font-weight:600; color: #fff;">
                        <span>Attendance Breakdown</span>
                        <span>{{ $totalDays }} total recorded days</span>
                    </div>
                    <div style="height:12px; border-radius:20px; overflow:hidden; display:flex; background:rgba(255,255,255,0.1);">
                        @php
                            $pPct = round(($totalPresent / $totalDays) * 100, 1);
                            $lPct = round(($totalLate    / $totalDays) * 100, 1);
                            $aPct = round(($totalAbsent  / $totalDays) * 100, 1);
                            $lvPct= round(($totalLeave   / $totalDays) * 100, 1);
                        @endphp
                        @if($pPct > 0)  <div style="width:{{ $pPct }}%;  background:#22c55e; transition:width .5s;"></div> @endif
                        @if($lPct > 0)  <div style="width:{{ $lPct }}%;  background:#eab308; transition:width .5s;"></div> @endif
                        @if($aPct > 0)  <div style="width:{{ $aPct }}%;  background:#ef4444; transition:width .5s;"></div> @endif
                        @if($lvPct > 0) <div style="width:{{ $lvPct }}%; background:#6366f1; transition:width .5s;"></div> @endif
                    </div>
                    <div class="d-flex gap-3 mt-2 flex-wrap" style="font-size:0.72rem; color: #fff;">
                        <span><span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:3px;margin-right:4px;"></span>Present {{ $pPct }}%</span>
                        <span><span style="display:inline-block;width:10px;height:10px;background:#eab308;border-radius:3px;margin-right:4px;"></span>Late {{ $lPct }}%</span>
                        <span><span style="display:inline-block;width:10px;height:10px;background:#ef4444;border-radius:3px;margin-right:4px;"></span>Absent {{ $aPct }}%</span>
                        @if($totalLeave > 0)
                        <span><span style="display:inline-block;width:10px;height:10px;background:#6366f1;border-radius:3px;margin-right:4px;"></span>Leave {{ $lvPct }}%</span>
                        @endif
                    </div>
                </div>
                <hr style="border-color: rgba(255,255,255,0.1); margin: 2rem 0;">
                @endif

                <div id="attendance-calendar-container">
                    @php
                        $monthStart = \Carbon\Carbon::parse($selectedMonth . '-01');
                        $daysInMonth = $monthStart->daysInMonth;
                        $startDow = $monthStart->dayOfWeek; // 0=Sun
                        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                        $monthPresent = 0; $monthAbsent = 0; $monthLate = 0;
                        for ($d = 1; $d <= $daysInMonth; $d++) {
                            $dt = $monthStart->copy()->day($d)->format('Y-m-d');
                            $rec = $attendanceByDate[$dt] ?? null;
                            if ($rec) {
                                if ($rec->status === 'present') $monthPresent++;
                                elseif ($rec->status === 'absent') $monthAbsent++;
                                elseif ($rec->status === 'late') $monthLate++;
                            }
                        }
                    @endphp

                    {{-- Summary badges & Date Filter --}}
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-3">
                        <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-success text-white px-3 py-2" style="font-size: 0.8rem;"><i class="fa-solid fa-check-circle me-1"></i>Present: {{ $monthPresent }}</span>
                    <span class="badge bg-danger text-white px-3 py-2" style="font-size: 0.8rem;"><i class="fa-solid fa-times-circle me-1"></i>Absent: {{ $monthAbsent }}</span>
                    <span class="badge bg-warning text-white px-3 py-2" style="font-size: 0.8rem;"><i class="fa-solid fa-clock me-1"></i>Late: {{ $monthLate }}</span>
                    @if(($monthPresent + $monthAbsent + $monthLate) > 0)
                    <span class="badge bg-primary text-white px-3 py-2" style="font-size: 0.8rem;">
                        <i class="fa-solid fa-percent me-1"></i>
                            Ratio: {{ round(($monthPresent / ($monthPresent + $monthAbsent + $monthLate)) * 100, 1) }}%
                        </span>
                        @endif
                        </div>
                        
                        <form method="GET" action="{{ route('admin.students.show', $student->id) }}" class="d-flex align-items-center gap-2 flex-wrap m-0" id="att-month-form">
                            <select name="att_month" class="form-select form-select-sm" style="width: auto; background-color: var(--bs-tertiary-bg); color: #fff; border-color: rgba(255,255,255,0.1);" id="att-month-select">
                                @foreach($attendanceMonths as $month)
                                    <option value="{{ $month }}" {{ $selectedMonth === $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::parse($month . '-01')->format('F Y') }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                {{-- Calendar grid --}}
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px;">
                    @foreach($dayNames as $dn)
                        <div style="text-align: center; font-size: 0.7rem; font-weight: 600; color: var(--bs-secondary-color); padding: 4px 0;">{{ $dn }}</div>
                    @endforeach

                    {{-- Empty cells before first day --}}
                    @for($e = 0; $e < $startDow; $e++)
                        <div></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $dt = $monthStart->copy()->day($day)->format('Y-m-d');
                            $rec = $attendanceByDate[$dt] ?? null;
                            $status = $rec ? $rec->status : null;
                            $bgColor = match($status) {
                                'present' => 'background: rgba(34,197,94,0.15); color: #16a34a; border: 1px solid rgba(34,197,94,0.3);',
                                'absent'  => 'background: rgba(239,68,68,0.15); color: #dc2626; border: 1px solid rgba(239,68,68,0.3);',
                                'late'    => 'background: rgba(234,179,8,0.15); color: #ca8a04; border: 1px solid rgba(234,179,8,0.3);',
                                'leave'   => 'background: rgba(99,102,241,0.15); color: #6366f1; border: 1px solid rgba(99,102,241,0.3);',
                                default   => 'background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); border: 1px solid var(--bs-border-color);',
                            };
                            $icon = match($status) {
                                'present' => 'P',
                                'absent'  => 'A',
                                'late'    => 'L',
                                'leave'   => 'LV',
                                default   => $day,
                            };
                        @endphp
                        <div title="{{ $dt }}{{ $status ? ' — ' . ucfirst($status) : ' — No record' }}" style="text-align: center; border-radius: 8px; padding: 6px 4px; font-size: 0.78rem; font-weight: 600; cursor: default; {{ $bgColor }}">
                            <div style="font-size: 0.65rem; opacity: 0.7;">{{ $day }}</div>
                            <div>{{ $icon }}</div>
                        </div>
                    @endfor
                </div>

                {{-- Legend --}}
                <div class="d-flex gap-3 mt-3 flex-wrap" style="font-size: 0.75rem;">
                    <span><span style="background:rgba(34,197,94,0.2); color:#16a34a; border-radius:4px; padding:1px 7px; font-weight:600;">P</span> Present</span>
                    <span><span style="background:rgba(239,68,68,0.2); color:#dc2626; border-radius:4px; padding:1px 7px; font-weight:600;">A</span> Absent</span>
                    <span><span style="background:rgba(234,179,8,0.2); color:#ca8a04; border-radius:4px; padding:1px 7px; font-weight:600;">L</span> Late</span>
                    <span><span style="background:rgba(99,102,241,0.2); color:#6366f1; border-radius:4px; padding:1px 7px; font-weight:600;">LV</span> Leave</span>
                </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== EXAM RESULTS ===== --}}
    @if(!empty($examResults))
    <div class="row g-4 mt-0">
        <div class="col-12">
            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-graduation-cap me-2"></i>Exam Results</div>

                @foreach($examResults as $examName => $result)
                <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2 flex-wrap gap-2">
                        <h6 class="fw-bold mb-0" style="font-size: 0.9rem; color: var(--bs-heading-color);">{{ $examName }}</h6>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary bg-opacity-10 text-primary border" style="font-size: 0.78rem;">
                                Total: {{ $result['totalObtained'] }} / {{ $result['totalMax'] }}
                            </span>
                            <span class="badge bg-info bg-opacity-10 text-info border" style="font-size: 0.78rem;">
                                {{ $result['percentage'] }}%
                            </span>
                            @if($result['grade'])
                            <span class="badge px-3 py-2" style="background: rgba(99,102,241,0.15); color: #6366f1; border: 1px solid rgba(99,102,241,0.3); font-size: 0.82rem; font-weight: 700;">
                                Grade: {{ $result['grade']->grade }} &nbsp;|&nbsp; GPA: {{ number_format($result['grade']->point, 2) }}
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div class="mb-3" style="height: 8px; background: var(--bs-border-color); border-radius: 10px; overflow: hidden;">
                        @php
                            $pct = $result['percentage'];
                            $barColor = $pct >= 80 ? '#22c55e' : ($pct >= 60 ? '#3b82f6' : ($pct >= 40 ? '#f59e0b' : '#ef4444'));
                        @endphp
                        <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 10px; transition: width 0.6s ease;"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0" style="font-size: 0.82rem;">
                            <thead>
                                <tr style="background: var(--bs-tertiary-bg);">
                                    <th style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 12px;">Subject</th>
                                    <th class="text-center" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">Marks</th>
                                    <th class="text-center" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">Max</th>
                                    <th class="text-center" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">%</th>
                                    <th class="text-center" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">Grade</th>
                                    <th class="text-center" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">GPA</th>
                                    <th style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($result['marks'] as $mark)
                                @php
                                    $max = $mark->examSchedule->max_marks;
                                    $pass = $mark->examSchedule->pass_marks;
                                    $obtained = $mark->marks_obtained;
                                    $subPct = $max > 0 ? round(($obtained / $max) * 100, 1) : 0;
                                    $subGrade = \App\Models\GradeRule::getGradeForPercentage($subPct);
                                    $passed = $obtained >= $pass;
                                @endphp
                                <tr style="border-color: var(--bs-border-color);">
                                    <td style="padding: 8px 12px; font-weight: 600;">{{ $mark->examSchedule->subject->name ?? 'N/A' }}</td>
                                    <td class="text-center fw-bold" style="padding: 8px; color: {{ $passed ? '#16a34a' : '#dc2626' }};">{{ $obtained }}</td>
                                    <td class="text-center" style="padding: 8px; color: var(--bs-secondary-color);">{{ $max }}</td>
                                    <td class="text-center" style="padding: 8px;">{{ $subPct }}%</td>
                                    <td class="text-center" style="padding: 8px;">
                                        @if($subGrade)
                                        <span class="badge" style="background: rgba(99,102,241,0.1); color: #6366f1; border: 1px solid rgba(99,102,241,0.25);">{{ $subGrade->grade }}</span>
                                        @else
                                        <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold" style="padding: 8px; font-size: 0.9rem;">
                                        {{ $subGrade ? number_format($subGrade->point, 2) : '—' }}
                                    </td>
                                    <td style="padding: 8px;">
                                        @if($passed)
                                        <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25" style="font-size: 0.72rem;">Pass</span>
                                        @else
                                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25" style="font-size: 0.72rem;">Fail</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(!$loop->last)<hr style="border-color: var(--bs-border-color);">@endif
                @endforeach

            </div>
        </div>
    </div>
    @endif

    <!-- THIRD ROW: Generated ID Card & Customize ID Card Design (Side-by-side) -->
    <div class="row g-4">
        <!-- LEFT: ID Card -->
        <div class="col-lg-6">
            <div class="info-card p-4 h-100 d-flex flex-column justify-content-between">
                <div>
                    <div class="section-title"><i class="fa-solid fa-id-card me-2"></i>Generated ID Card (Front &amp; Back)</div>

                    <div id="printArea" class="d-flex flex-wrap justify-content-center gap-4 mb-4">
                        <!-- ========== CARD FRONT ========== -->
                        <div class="id-card-front">
                            <div class="glow-orb"></div>
                            <!-- Header -->
                            <div class="d-flex align-items-center justify-content-between pb-2" style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:24px; height:24px; background: linear-gradient(135deg,#38bdf8,#6366f1); border-radius:6px; display:flex; align-items:center; justify-content:center;">
                                        <i class="fa-solid fa-graduation-cap" style="font-size:0.6rem; color:#fff;"></i>
                                    </div>
                                    <span class="inst-name-text id-primary-color" style="font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">EduERP Academy</span>
                                </div>
                                <span style="background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff; font-size:0.55rem; font-weight:700; padding:3px 8px; border-radius:20px; letter-spacing:1px;">STUDENT</span>
                            </div>

                            <!-- Body -->
                            <div class="d-flex gap-3 align-items-center my-1" style="flex:1;">
                                <div class="photo-box">
                                    @if($student->photo_path)
                                        <img src="{{ asset('storage/' . $student->photo_path) }}" alt="Photo">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05);">
                                            <i class="fa-solid fa-user" style="color:rgba(255,255,255,0.3); font-size:1.5rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div class="fw-bold text-truncate student-name-text id-primary-color" style="font-size:0.85rem; margin-bottom:3px;">{{ $student->user->name }}</div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Class: <span class="id-val" style="font-weight:600;">{{ $student->schoolClass->name }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Section: <span class="id-val" style="font-weight:600;">{{ $student->section->name }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Roll: <span class="id-val" style="font-weight:600;">{{ $student->roll_no }}</span> | Blood: <span class="id-blood-color" style="font-weight:700;">{{ $student->blood_group ?? 'N/A' }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem;">Adm No: <span class="id-primary-color" style="font-weight:700;">{{ $student->admission_no }}</span></div>
                                </div>
                            </div>

                            <!-- Footer: QR + Signature -->
                            <div class="d-flex align-items-end justify-content-between pt-2" style="border-top: 1px solid rgba(255,255,255,0.06);">
                                <div style="background:#fff; border-radius:5px; padding:3px; display:flex; align-items:center; justify-content:center;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=36x36&data={{ urlencode($student->qr_code) }}" alt="QR" style="width:36px; height:36px; display:block;">
                                </div>
                                <div style="text-align:center; line-height:1;">
                                    @if($student->signature_path)
                                        <img src="{{ asset('storage/' . $student->signature_path) }}" alt="Signature" style="max-height:22px; max-width:80px; display:block; margin:0 auto 2px;">
                                    @else
                                        <span style="font-family:'Brush Script MT',cursive; font-style:italic; font-size:0.95rem; color:#64748b; display:block; margin-bottom:2px;">Authorized Sign</span>
                                    @endif
                                    <span class="id-lbl" style="font-size:0.5rem; text-transform:uppercase; letter-spacing:0.5px; display:block; padding-top:3px; border-top:1px solid rgba(255,255,255,0.07);">Authorized Signature</span>
                                </div>
                            </div>

                            <div class="stripe"></div>
                        </div>

                        <!-- ========== CARD BACK ========== -->
                        <div class="id-card-back">
                            <div class="glow-orb" style="background:rgba(99,102,241,0.1); left:-20px; bottom:-20px; top:auto; right:auto;"></div>

                            <!-- Barcode strip at top -->
                            <div class="barcode-wrapper" style="background:linear-gradient(135deg,#1e3a5f,#0f172a); border-radius:6px; padding:7px 10px; display:flex; align-items:center; justify-content:space-between; border:1px solid rgba(56,189,248,0.1);">
                                <div class="barcode-lines" style="height:24px;">
                                    @php
                                        $heights = [18,12,22,15,20,10,18,24,14,16,22,11,19,13,20,16,24,12,17,21,15,23,11,18,14,20,16,13,22,12];
                                    @endphp
                                    @foreach($heights as $h)
                                        <span style="height:{{ $h }}px; background:rgba(148,163,184,0.7);"></span>
                                    @endforeach
                                </div>
                                <span class="id-lbl" style="font-size:0.55rem; letter-spacing:2px; font-family:monospace;">{{ str_pad($student->id, 8, '0', STR_PAD_LEFT) }}-{{ substr(md5($student->admission_no), 0, 6) }}</span>
                            </div>

                            <!-- Terms -->
                            <div style="text-align:center; padding:4px 0;">
                                <div class="terms-title-text id-primary-color" style="font-size:0.6rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; margin-bottom:5px;">Terms of Use</div>
                                <div class="terms-content-text id-lbl" style="font-size:0.58rem; line-height:1.5;">
                                    This card is the property of EduERP Academy. It must be carried at all times and presented on request. If found, please return to the school office.
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="d-flex justify-content-between align-items-end" style="border-top:1px solid rgba(255,255,255,0.06); padding-top:8px;">
                                <div class="contact-info-text id-lbl" style="font-size:0.55rem; line-height:1.7;">
                                    <div><i class="fa-solid fa-phone id-primary-color" style="font-size:0.5rem; margin-right:3px;"></i> <span class="phone-val id-val">+880 1234-567890</span></div>
                                    <div><i class="fa-solid fa-envelope id-primary-color" style="font-size:0.5rem; margin-right:3px;"></i> <span class="email-val id-val">school@eduerp.com</span></div>
                                    <div><i class="fa-solid fa-globe id-primary-color" style="font-size:0.5rem; margin-right:3px;"></i> <span class="web-val id-val">www.eduerp.school</span></div>
                                </div>
                                <div style="text-align:center;">
                                    <div class="id-primary-color" style="font-family:'Brush Script MT',cursive; font-style:italic; font-size:0.9rem; line-height:1;">Principal</div>
                                    <div class="id-lbl" style="border-top:1px solid var(--id-card-primary-text, #38bdf8); margin-top:3px; padding-top:3px; font-size:0.5rem; text-transform:uppercase; letter-spacing:0.5px;">Principal's Signature</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="text-center mt-auto pt-3">
                    <button class="print-btn" onclick="printIDCard()">
                        <i class="fa-solid fa-print"></i> Print ID Card
                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT: DESIGN CUSTOMIZER PANEL -->
        <div class="col-lg-6">
            <div class="info-card p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-wand-magic-sparkles me-2"></i>Customize ID Card Design</div>
                <div class="row g-3">
                    <!-- Colors Column -->
                    <div class="col-md-6">
                        <h6 class="fs-7 fw-bold text-primary mb-3">Colors & Background</h6>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Card Background Gradient</label>
                            <div class="d-flex gap-2">
                                <input type="color" id="bgStartColor" class="form-control form-control-color w-50" value="#1e293b" title="Start Color">
                                <input type="color" id="bgEndColor" class="form-control form-control-color w-50" value="#0f172a" title="End Color">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Border Color</label>
                            <input type="color" id="borderColor" class="form-control form-control-color w-100" value="#38bdf8" title="Border Color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Primary/Name Text Color</label>
                            <input type="color" id="textPrimaryColor" class="form-control form-control-color w-100" value="#38bdf8" title="Primary Text Color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Label Text Color</label>
                            <input type="color" id="textLabelColor" class="form-control form-control-color w-100" value="#94a3b8" title="Label Text Color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Value Text Color</label>
                            <input type="color" id="textValueColor" class="form-control form-control-color w-100" value="#e2e8f0" title="Value Text Color">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Blood Group Color</label>
                            <input type="color" id="textBloodColor" class="form-control form-control-color w-100" value="#ef4444" title="Blood Group Color">
                        </div>
                    </div>
                    <!-- Text Info Column -->
                    <div class="col-md-6">
                        <h6 class="fs-7 fw-bold text-primary mb-3">Card Information</h6>
                        <div class="mb-2">
                            <label class="form-label fs-7 text-secondary">Academy Name</label>
                            <input type="text" id="custAcademyName" class="form-control form-control-sm" value="EduERP Academy">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-7 text-secondary">School Phone</label>
                            <input type="text" id="custPhone" class="form-control form-control-sm" value="+880 1234-567890">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-7 text-secondary">School Email</label>
                            <input type="text" id="custEmail" class="form-control form-control-sm" value="school@eduerp.com">
                        </div>
                        <div class="mb-2">
                            <label class="form-label fs-7 text-secondary">School Website</label>
                            <input type="text" id="custWeb" class="form-control form-control-sm" value="www.eduerp.school">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fs-7 text-secondary">Terms of Use</label>
                            <textarea id="custTerms" class="form-control form-control-sm" rows="2" style="font-size: 0.75rem;">This card is the property of EduERP Academy. It must be carried at all times and presented on request. If found, please return to the school office.</textarea>
                        </div>
                    </div>
                    <!-- Accents Settings -->
                    <div class="col-12 border-top pt-3">
                        <h6 class="fs-7 fw-bold text-primary mb-2">Display Accents</h6>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchStripe" checked>
                                <label class="form-check-label fs-7 text-secondary" for="switchStripe">Bottom Shimmering Stripe</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchGlow" checked>
                                <label class="form-check-label fs-7 text-secondary" for="switchGlow">Glow Orbs</label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="switchBarcode" checked>
                                <label class="form-check-label fs-7 text-secondary" for="switchBarcode">Barcode Strip</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 text-end mt-2">
                        <button type="button" id="resetCustomizer" class="btn btn-sm btn-outline-danger me-2"><i class="fa-solid fa-rotate-left me-1"></i>Reset to Default</button>
                        <button type="button" id="saveCustomizerTemplate" class="btn btn-sm btn-success"><i class="fa-solid fa-floppy-disk me-1"></i>Save Template</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const printArea = document.getElementById('printArea');

    // Customizer Controls
    const bgStartInput = document.getElementById('bgStartColor');
    const bgEndInput = document.getElementById('bgEndColor');
    const borderInput = document.getElementById('borderColor');
    const primaryTextInput = document.getElementById('textPrimaryColor');
    const labelTextInput = document.getElementById('textLabelColor');
    const valueTextInput = document.getElementById('textValueColor');
    const bloodTextInput = document.getElementById('textBloodColor');

    const academyNameInput = document.getElementById('custAcademyName');
    const phoneInput = document.getElementById('custPhone');
    const emailInput = document.getElementById('custEmail');
    const webInput = document.getElementById('custWeb');
    const termsInput = document.getElementById('custTerms');

    const stripeSwitch = document.getElementById('switchStripe');
    const glowSwitch = document.getElementById('switchGlow');
    const barcodeSwitch = document.getElementById('switchBarcode');

    const resetBtn = document.getElementById('resetCustomizer');
    const saveBtn = document.getElementById('saveCustomizerTemplate');

    // Defaults matching the design specs
    const defaults = {
        bgStart: '#1e293b',
        bgEnd: '#0f172a',
        border: '#38bdf8',
        primaryText: '#38bdf8',
        labelText: '#94a3b8',
        valueText: '#e2e8f0',
        bloodColor: '#ef4444',
        academyName: 'EduERP Academy',
        phone: '+880 1234-567890',
        email: 'school@eduerp.com',
        web: 'www.eduerp.school',
        terms: 'This card is the property of EduERP Academy. It must be carried at all times and presented on request. If found, please return to the school office.',
        stripe: true,
        glow: true,
        barcode: true
    };

    let config = { ...defaults };

    // Load config from LocalStorage
    const savedConfig = localStorage.getItem('id_card_template_config');
    if (savedConfig) {
        try {
            config = { ...defaults, ...JSON.parse(savedConfig) };
        } catch (e) {
            console.error("Failed to parse saved ID Card template config.", e);
        }
    }

    function updateControls() {
        bgStartInput.value = config.bgStart;
        bgEndInput.value = config.bgEnd;
        borderInput.value = config.border;
        primaryTextInput.value = config.primaryText;
        labelTextInput.value = config.labelText;
        valueTextInput.value = config.valueText;
        bloodTextInput.value = config.bloodColor;

        academyNameInput.value = config.academyName;
        phoneInput.value = config.phone;
        emailInput.value = config.email;
        webInput.value = config.web;
        termsInput.value = config.terms;

        stripeSwitch.checked = config.stripe;
        glowSwitch.checked = config.glow;
        barcodeSwitch.checked = config.barcode;
    }

    function applyConfig() {
        // Set CSS custom properties on printArea wrapper
        printArea.style.setProperty('--id-card-bg-start', config.bgStart);
        printArea.style.setProperty('--id-card-bg-end', config.bgEnd);
        printArea.style.setProperty('--id-card-border', config.border);
        printArea.style.setProperty('--id-card-primary-text', config.primaryText);
        printArea.style.setProperty('--id-card-label-text', config.labelText);
        printArea.style.setProperty('--id-card-value-text', config.valueText);
        printArea.style.setProperty('--id-card-blood-color', config.bloodColor);

        // Update elements text values
        document.querySelectorAll('.inst-name-text').forEach(el => el.textContent = config.academyName);
        document.querySelectorAll('.phone-val').forEach(el => el.textContent = config.phone);
        document.querySelectorAll('.email-val').forEach(el => el.textContent = config.email);
        document.querySelectorAll('.web-val').forEach(el => el.textContent = config.web);
        document.querySelectorAll('.terms-content-text').forEach(el => el.textContent = config.terms);

        // Show/hide displaying accents
        document.querySelectorAll('.stripe').forEach(el => el.style.display = config.stripe ? 'block' : 'none');
        document.querySelectorAll('.glow-orb').forEach(el => el.style.display = config.glow ? 'block' : 'none');
        document.querySelectorAll('.barcode-wrapper').forEach(el => el.style.display = config.barcode ? 'flex' : 'none');
    }

    function addCustomizerListener(inputEl, key, isCheckbox = false) {
        if (!inputEl) return;
        inputEl.addEventListener('input', function () {
            config[key] = isCheckbox ? inputEl.checked : inputEl.value;
            applyConfig();
        });
    }

    addCustomizerListener(bgStartInput, 'bgStart');
    addCustomizerListener(bgEndInput, 'bgEnd');
    addCustomizerListener(borderInput, 'border');
    addCustomizerListener(primaryTextInput, 'primaryText');
    addCustomizerListener(labelTextInput, 'labelText');
    addCustomizerListener(valueTextInput, 'valueText');
    addCustomizerListener(bloodTextInput, 'bloodColor');

    addCustomizerListener(academyNameInput, 'academyName');
    addCustomizerListener(phoneInput, 'phone');
    addCustomizerListener(emailInput, 'email');
    addCustomizerListener(webInput, 'web');
    addCustomizerListener(termsInput, 'terms');

    addCustomizerListener(stripeSwitch, 'stripe', true);
    addCustomizerListener(glowSwitch, 'glow', true);
    addCustomizerListener(barcodeSwitch, 'barcode', true);

    resetBtn.addEventListener('click', function () {
        if (confirm("Are you sure you want to reset the design layout to default?")) {
            config = { ...defaults };
            updateControls();
            applyConfig();
        }
    });

    saveBtn.addEventListener('click', function () {
        localStorage.setItem('id_card_template_config', JSON.stringify(config));
        alert("ID Card Design template has been saved! This custom design template will be applied for all Student ID Cards.");
    });

    // Initializer
    updateControls();
    applyConfig();
});

function printIDCard() {
    const printArea = document.getElementById('printArea');
    const printStyles = printArea.getAttribute('style') || '';
    const pw = window.open('', '_blank', 'width=900,height=700');
    pw.document.write('<ht' + 'ml><he' + 'ad><ti' + 'tle>Student ID Card — {{ addslashes($student->user->name) }}</ti' + 'tle>');
    pw.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
    pw.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">');
    pw.document.write('<sty' + 'le>');
    pw.document.write('@media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }');
    pw.document.write('body { margin:0; padding:30px; background:#f8fafc; font-family: Inter, sans-serif; display:flex; flex-direction:column; align-items:center; justify-content:center; min-height:100vh; }');
    pw.document.write('#card-wrap { display:flex; flex-wrap:wrap; gap:24px; justify-content:center; align-items:flex-start; }');
    pw.document.write('.id-card-front, .id-card-back { width:340px; height:215px; border-radius:16px; padding:14px 16px; display:flex; flex-direction:column; justify-content:space-between; overflow:hidden; position:relative; box-sizing:border-box; }');
    pw.document.write('.id-card-front { background:linear-gradient(var(--id-card-gradient-angle, 135deg),var(--id-card-bg-start,#1e293b) 0%,var(--id-card-bg-end,#0f172a) 100%); border:2px solid var(--id-card-border,#38bdf8); color:#fff; box-shadow:0 8px 30px rgba(0,0,0,0.5); }');
    pw.document.write('.id-card-back { background:linear-gradient(var(--id-card-gradient-angle, 135deg),var(--id-card-bg-end,#0f172a) 0%,var(--id-card-bg-start,#1e293b) 100%); border:2px solid var(--id-card-border,#38bdf8); color:#fff; box-shadow:0 8px 30px rgba(0,0,0,0.5); }');
    pw.document.write('.photo-box { width:65px; height:78px; border-radius:8px; border:1.5px solid rgba(255,255,255,0.15); overflow:hidden; flex-shrink:0; background:rgba(255,255,255,0.05); }');
    pw.document.write('.photo-box img { width:100%; height:100%; object-fit:cover; }');
    pw.document.write('.stripe { position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#38bdf8,#6366f1,#38bdf8); }');
    pw.document.write('.barcode-lines { display:flex; gap:2px; align-items:flex-end; height:24px; }');
    pw.document.write('.barcode-lines span { background:rgba(148,163,184,0.7); width:2px; border-radius:1px; }');
    pw.document.write('.id-lbl { color: var(--id-card-label-text, #94a3b8); }');
    pw.document.write('.id-val { color: var(--id-card-value-text, #e2e8f0); }');
    pw.document.write('.id-primary-color { color: var(--id-card-primary-text, #38bdf8); }');
    pw.document.write('.id-blood-color { color: var(--id-card-blood-color, #ef4444); }');
    pw.document.write('</sty' + 'le></he' + 'ad><bo' + 'dy>');
    pw.document.write('<div id="card-wrap" style="' + printStyles + '">' + printArea.innerHTML + '</div>');
    pw.document.write('</bo' + 'dy></ht' + 'ml>');
    pw.document.write('<scr' + 'ipt>window.onload = function(){ setTimeout(function(){ window.print(); }, 800); };</scr' + 'ipt>');
    pw.document.close();
}

// Password show/hide toggle on student profile page
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('pw-toggle');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const mask = document.getElementById('pw-mask');
            const plain = document.getElementById('pw-plain');
            const icon = document.getElementById('pw-toggle-icon');
            if (plain.classList.contains('d-none')) {
                plain.classList.remove('d-none');
                mask.classList.add('d-none');
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                plain.classList.add('d-none');
                mask.classList.remove('d-none');
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }

    // Handle AJAX Date Filter
    document.addEventListener('change', function(e) {
        if (e.target && e.target.id === 'att-month-select') {
            let month = e.target.value;
            let url = '{{ route("admin.students.show", $student->id) }}' + '?att_month=' + month;
            
            let container = document.getElementById('attendance-calendar-container');
            container.style.opacity = '0.5';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    let parser = new DOMParser();
                    let doc = parser.parseFromString(html, 'text/html');
                    let newContent = doc.getElementById('attendance-calendar-container').innerHTML;
                    container.innerHTML = newContent;
                    container.style.opacity = '1';
                })
                .catch(err => {
                    console.error(err);
                    container.style.opacity = '1';
                    alert('Failed to fetch attendance data. Please try again.');
                });
        }
    });
});
</script>
@endsection
