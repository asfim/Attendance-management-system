@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card glass-card border-0 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-gears text-primary me-2"></i>Company & System Settings</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">Attendance Suite</span>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">Company / Organization Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $settings['company_name'] ?? 'Attendify Technologies Ltd.' }}" required>
                </div>

                <div class="row mb-3 g-3">
                    <div class="col-md-4">
                        <label class="form-label text-secondary fw-semibold">Trade License / Reg No</label>
                        <input type="text" name="company_reg_no" class="form-control" value="{{ $settings['company_reg_no'] ?? 'TR-2026-9812' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary fw-semibold">Established Year</label>
                        <input type="text" name="site_established_year" class="form-control" value="{{ $settings['site_established_year'] ?? '2020' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary fw-semibold">Headquarters Location</label>
                        <input type="text" name="company_location" class="form-control" value="{{ $settings['company_location'] ?? 'Dhaka, Bangladesh' }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">Office Notice / Policy Announcement</label>
                    <input type="text" name="office_notice" class="form-control" value="{{ $settings['office_notice'] ?? ($settings['site_admission_text'] ?? 'Standard office working hours: 09:00 AM - 05:00 PM. Please ensure biometric punch on entry & exit.') }}">
                    <input type="hidden" name="site_admission_text" value="{{ $settings['office_notice'] ?? ($settings['site_admission_text'] ?? 'Standard office working hours: 09:00 AM - 05:00 PM.') }}">
                </div>
                
                <h6 class="fw-bold mt-4 mb-3 text-dark"><i class="fa-solid fa-palette text-info me-2"></i>Branding & System Identity</h6>
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">System Title</label>
                    <input type="text" name="site_meta_title" class="form-control" value="{{ $settings['site_meta_title'] ?? 'Attendify - Office Attendance & HR Management System' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">System Description</label>
                    <textarea name="site_meta_description" class="form-control" rows="2">{{ $settings['site_meta_description'] ?? 'Attendify Enterprise Office Attendance Management System — Real-time biometric tracking, shift scheduling, leaves and automated payroll.' }}</textarea>
                </div>
                <div class="row mb-3 g-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary fw-semibold">Company Logo</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        @if(isset($settings['site_logo']))
                            <div class="mt-2 p-2 bg-dark rounded d-inline-block">
                                <img src="{{ $settings['site_logo'] }}" style="max-height: 40px;" alt="Logo">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary fw-semibold">Favicon</label>
                        <input type="file" name="site_favicon" class="form-control" accept="image/x-icon,image/png">
                        @if(isset($settings['site_favicon']))
                            <div class="mt-2 p-2 border rounded d-inline-block">
                                <img src="{{ $settings['site_favicon'] }}" style="max-height: 32px;" alt="Favicon">
                            </div>
                        @endif
                    </div>
                </div>

                <h6 class="fw-bold mt-4 mb-3 text-dark"><i class="fa-solid fa-address-book text-success me-2"></i>Contact Information</h6>
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">Contact Email</label>
                    <input type="email" name="company_email" class="form-control" value="{{ $settings['company_email'] ?? ($settings['school_email'] ?? 'hr@company.com') }}" required>
                    <input type="hidden" name="school_email" value="{{ $settings['company_email'] ?? ($settings['school_email'] ?? 'hr@company.com') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fw-semibold">Contact Phone</label>
                    <input type="text" name="company_phone" class="form-control" value="{{ $settings['company_phone'] ?? ($settings['school_phone'] ?? '+880 1700-000001') }}" required>
                    <input type="hidden" name="school_phone" value="{{ $settings['company_phone'] ?? ($settings['school_phone'] ?? '+880 1700-000001') }}">
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary fw-semibold">Office Address</label>
                    <textarea name="company_address" class="form-control" rows="3" required>{{ $settings['company_address'] ?? ($settings['school_address'] ?? 'Gulshan-2, Dhaka-1212, Bangladesh') }}</textarea>
                    <input type="hidden" name="school_address" value="{{ $settings['company_address'] ?? ($settings['school_address'] ?? 'Gulshan-2, Dhaka-1212, Bangladesh') }}">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="fa-solid fa-floppy-disk me-2"></i>Save System Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
