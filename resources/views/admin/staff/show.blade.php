@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h4 class="mb-1 text-primary"><i class="fa-solid fa-id-badge me-2"></i>Staff Details</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}" class="text-decoration-none">Staff</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $staff->user->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.staff.edit', $staff->id) }}" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-pen me-1"></i> Edit Profile
            </a>
        </div>
    </div>

    <!-- PROFILE HERO SECTION -->
    <div class="profile-hero p-4 mb-4">
        <div class="d-flex align-items-center flex-wrap gap-4 position-relative" style="z-index: 10;">
            <div class="student-avatar-wrapper">
                <div class="avatar-ring"></div>
                @if($staff->photo)
                    <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo">
                @else
                    <div class="avatar-fallback d-flex align-items-center justify-content-center bg-light text-secondary fs-1 fw-bold">
                        {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                    </div>
                @endif
            </div>

            <div class="flex-grow-1">
                <div>
                    <h4 class="fw-bold mb-1" style="color: inherit;">{{ $staff->user->name }}</h4>
                    <p class="text-muted fs-7 mb-2">{{ $staff->user->email }}</p>
                    <span class="badge me-1" style="background: rgba(13,110,253,0.1); color:#0d6efd; border: 1px solid rgba(13,110,253,0.2); font-size:0.65rem;">
                        {{ $staff->user->role ? $staff->user->role->display_name : 'Unassigned' }}
                    </span>
                    <span class="badge" style="background: {{ $staff->status === 'active' ? 'rgba(34,197,94,0.15)' : 'rgba(239,68,68,0.15)' }}; color: {{ $staff->status === 'active' ? '#22c55e' : '#ef4444' }}; border: 1px solid {{ $staff->status === 'active' ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)' }}; font-size:0.65rem;">
                        {{ ucfirst($staff->status) }}
                    </span>
                </div>
            </div>

            <div class="col-md-5">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">ID Number</div>
                            <div class="fw-bold" style="font-size:1.1rem; color: inherit;">#STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-pill">
                            <div style="font-size:0.6rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px;">Department</div>
                            <div class="fw-bold text-primary" style="font-size:0.8rem;">{{ $staff->department ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="row g-4">
        <!-- LEFT COLUMN: Personal Info -->
        <div class="col-md-6">
            <div class="info-card p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-circle-info me-2"></i>Personal Details</div>
                <div class="info-row"><span class="label">Full Name</span><span class="value">{{ $staff->user->name }}</span></div>
                <div class="info-row"><span class="label">Bangla Name</span><span class="value">{{ $staff->bangla_name ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Email</span><span class="value">{{ $staff->user->email }}</span></div>
                <div class="info-row"><span class="label">Phone</span><span class="value">{{ $staff->phone ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Date of Birth</span><span class="value">{{ $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('M d, Y') : '-' }}</span></div>
                <div class="info-row"><span class="label">Gender</span><span class="value">{{ ucfirst($staff->gender ?? '-') }}</span></div>
                <div class="info-row"><span class="label">Blood Group</span><span class="value text-danger fw-bold">{{ $staff->blood_group ?? 'N/A' }}</span></div>
                <div class="info-row"><span class="label">Religion</span><span class="value">{{ $staff->religion ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Marital Status</span><span class="value">{{ $staff->marital_status ?? '-' }}</span></div>
                <div class="info-row"><span class="label">National ID</span><span class="value">{{ $staff->national_id ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Present Address</span><span class="value">{{ $staff->address ?? '-' }}</span></div>
                <div class="info-row" style="border:none;"><span class="label">Permanent Address</span><span class="value">{{ $staff->permanent_address ?? '-' }}</span></div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Employment & Payroll -->
        <div class="col-md-6 d-flex flex-column gap-4">
            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-briefcase me-2"></i>Employment Info</div>
                <div class="info-row"><span class="label">Role</span><span class="value">{{ $staff->user->role ? $staff->user->role->display_name : '-' }}</span></div>
                <div class="info-row"><span class="label">Department</span><span class="value">{{ $staff->department ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Designation</span><span class="value">{{ $staff->designation ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Employment Type</span><span class="value">{{ $staff->employment_type ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Joining Date</span><span class="value">{{ $staff->joining_date ? \Carbon\Carbon::parse($staff->joining_date)->format('M d, Y') : '-' }}</span></div>
                <div class="info-row"><span class="label">Qualifications</span><span class="value">{{ $staff->qualifications ?? '-' }}</span></div>
                <div class="info-row"><span class="label">Experience</span><span class="value">{{ $staff->experience ?? '-' }}</span></div>
                
                @if($staff->shifts->count() > 0)
                    <div class="info-row" style="border:none;">
                        <span class="label">Assigned Shifts</span>
                        <span class="value">
                            @foreach($staff->shifts as $shift)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 me-1">{{ $shift->name }}</span>
                            @endforeach
                        </span>
                    </div>
                @else
                    <div class="info-row" style="border:none;"><span class="label">Assigned Shifts</span><span class="value text-muted">No shifts assigned</span></div>
                @endif
            </div>

            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-money-check-dollar me-2"></i>Salary & Allowances</div>
                <div class="info-row"><span class="label">Basic Salary</span><span class="value fw-bold text-success">${{ number_format($staff->salary, 2) }}</span></div>
                @if($staff->allowances->count() > 0)
                    <div class="mt-2 pt-2 border-top">
                        <span class="fs-8 fw-semibold text-secondary mb-2 d-block">Allowances</span>
                        @foreach($staff->allowances as $allowance)
                            <div class="info-row py-1 border-0">
                                <span class="label fs-8"><i class="fa-solid fa-plus text-muted me-2"></i>{{ $allowance->name }}</span>
                                <span class="value fs-8">${{ number_format($allowance->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="info-row mt-2" style="border-top: 2px solid var(--bs-border-color); border-bottom: none;">
                    <span class="label fw-bold">Gross Salary</span>
                    <span class="value fw-bold text-primary">${{ number_format($staff->salary + $staff->allowances->sum('amount'), 2) }}</span>
                </div>
            </div>
            
            <div class="info-card p-4">
                <div class="section-title"><i class="fa-solid fa-truck-medical me-2"></i>Emergency Contact</div>
                <div class="info-row"><span class="label">Contact Name</span><span class="value">{{ $staff->emergency_contact_name ?? '-' }}</span></div>
                <div class="info-row" style="border:none;"><span class="label">Contact Phone</span><span class="value">{{ $staff->emergency_contact_phone ?? '-' }}</span></div>
            </div>
        </div>
    </div>

    <!-- THIRD ROW: Generated ID Card & Customize ID Card Design (Side-by-side) -->
    <div class="row g-4 mt-2">
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
                                        <i class="fa-solid fa-briefcase" style="font-size:0.6rem; color:#fff;"></i>
                                    </div>
                                    <span class="inst-name-text id-primary-color" style="font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:1px;">EduERP Academy</span>
                                </div>
                                <span style="background:linear-gradient(135deg,#0ea5e9,#6366f1); color:#fff; font-size:0.55rem; font-weight:700; padding:3px 8px; border-radius:20px; letter-spacing:1px;">STAFF</span>
                            </div>

                            <!-- Body -->
                            <div class="d-flex gap-3 align-items-center my-1" style="flex:1;">
                                <div class="photo-box">
                                    @if($staff->photo)
                                        <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo">
                                    @else
                                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05);">
                                            <i class="fa-solid fa-user" style="color:rgba(255,255,255,0.3); font-size:1.5rem;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div style="flex:1; min-width:0;">
                                    <div class="fw-bold text-truncate student-name-text id-primary-color" style="font-size:0.85rem; margin-bottom:3px;">{{ $staff->user->name }}</div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Desig: <span class="id-val" style="font-weight:600;">{{ $staff->designation ?? 'N/A' }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Dept: <span class="id-val" style="font-weight:600;">{{ $staff->department ?? 'N/A' }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem; margin-bottom:2px;">Phone: <span class="id-val" style="font-weight:600;">{{ $staff->phone ?? 'N/A' }}</span> | Blood: <span class="id-blood-color" style="font-weight:700;">{{ $staff->blood_group ?? 'N/A' }}</span></div>
                                    <div class="id-lbl" style="font-size:0.62rem;">ID No: <span class="id-primary-color" style="font-weight:700;">STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</span></div>
                                </div>
                            </div>

                            <!-- Footer: QR + Signature -->
                            <div class="d-flex align-items-end justify-content-between pt-2" style="border-top: 1px solid rgba(255,255,255,0.06);">
                                <div style="background:#fff; border-radius:5px; padding:3px; display:flex; align-items:center; justify-content:center;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=36x36&data=STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}" alt="QR" style="width:36px; height:36px; display:block;">
                                </div>
                                <div style="text-align:center; line-height:1;">
                                    @if($staff->signature_path)
                                        <img src="{{ asset('storage/' . $staff->signature_path) }}" alt="Signature" style="height: 20px; object-fit: contain; margin-bottom: 2px;">
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
                                <span class="id-lbl" style="font-size:0.55rem; letter-spacing:2px; font-family:monospace;">{{ str_pad($staff->id, 8, '0', STR_PAD_LEFT) }}-{{ substr(md5('STF-'.$staff->id), 0, 6) }}</span>
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
                            <div class="stripe"></div>
                        </div>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="text-center mt-auto pt-3">
                    <button class="btn btn-success btn-sm px-4 py-2 fw-semibold" onclick="printIDCard()">
                        <i class="fa-solid fa-print me-1"></i> Print ID Card
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
                        <h6 class="fs-7 fw-bold text-primary mb-3">Colors &amp; Background</h6>
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

<style>
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
    box-shadow: 0 8px 30px rgba(0,0,0,0.5);
    color: #fff;
    font-family: 'Inter', sans-serif;
}
.id-blood-color { color: var(--id-card-blood-color, #ef4444); }
.id-lbl { color: var(--id-card-label-text, #94a3b8); font-size: 0.62rem; }
.id-val { color: var(--id-card-value-text, #e2e8f0); }
.id-primary-color { color: var(--id-card-primary-text, #38bdf8); }
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
.id-card-front .stripe, .id-card-back .stripe {
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
.photo-box img { width:100%; height:100%; object-fit:cover; }
.barcode-wrapper { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.barcode-lines { display:flex; gap:2px; align-items:flex-end; height:24px; }
.barcode-lines span { background:rgba(148,163,184,0.7); width:2px; border-radius:1px; }

/* Customizer Panel */
.customizer-panel {
    background: #ffffff;
    border: 1px solid var(--bs-border-color);
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
    overflow: hidden;
}
[data-bs-theme="dark"] .customizer-panel {
    background: #111827;
    color: #f3f4f6;
}
.customizer-section {
    padding: 12px 16px;
    border-bottom: 1px solid var(--bs-border-color);
}
.customizer-section:last-child { border-bottom: none; }
.customizer-section-title {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    font-weight: 700;
    color: #64748b;
    margin-bottom: 10px;
}
.color-swatch-row { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
.color-swatch-item { display: flex; flex-direction: column; align-items: center; gap: 3px; font-size: 0.6rem; color: #64748b; }
.color-swatch-item input[type=color] {
    width: 28px; height: 28px; border-radius: 50%; border: 2px solid var(--bs-border-color);
    cursor: pointer; padding: 2px; background: none;
}
.btn-save-design {
    background: linear-gradient(135deg, #0ea5e9, #6366f1);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 20px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-save-design:hover { opacity: 0.85; }
.btn-reset-design {
    background: transparent;
    color: #ef4444;
    border: 1px solid #ef4444;
    border-radius: 10px;
    padding: 7px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-reset-design:hover { background: #ef4444; color: #fff; }
.btn-print-id {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 8px 20px;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.btn-print-id:hover { opacity: 0.85; }
</style>
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
    const savedConfig = localStorage.getItem('staff_id_card_template_config');
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
        localStorage.setItem('staff_id_card_template_config', JSON.stringify(config));
        alert("ID Card Design template has been saved! This custom design template will be applied for all Staff ID Cards.");
    });

    // Initializer
    updateControls();
    applyConfig();
});

function printIDCard() {
    const printArea = document.getElementById('printArea');
    const printStyles = printArea.getAttribute('style') || '';
    const pw = window.open('', '_blank', 'width=900,height=700');
    pw.document.write('<ht' + 'ml><he' + 'ad><ti' + 'tle>Staff ID Card — {{ addslashes($staff->user->name) }}</ti' + 'tle>');
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
</script>

@endsection
