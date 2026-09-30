@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Global System Settings</h5>
            <form action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label text-secondary">School Name</label>
                    <input type="text" name="school_name" class="form-control" value="{{ $settings['school_name'] ?? 'Enterprise School ERP' }}" required>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-secondary">EIIN Number</label>
                        <input type="text" name="school_eiin" class="form-control" value="{{ $settings['school_eiin'] ?? '123456' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary">Established Year</label>
                        <input type="text" name="site_established_year" class="form-control" value="{{ $settings['site_established_year'] ?? '1999' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-secondary">Short Location (e.g. Dhaka)</label>
                        <input type="text" name="school_address_short" class="form-control" value="{{ $settings['school_address_short'] ?? 'Dhaka, Bangladesh' }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-secondary">Admissions Notice Text</label>
                    <input type="text" name="site_admission_text" class="form-control" value="{{ $settings['site_admission_text'] ?? 'Admissions Open — Session 2026-27' }}">
                </div>
                
                <h6 class="fw-bold mt-4 mb-3">Website & SEO</h6>
                <div class="mb-3">
                    <label class="form-label text-secondary">Meta Title</label>
                    <input type="text" name="site_meta_title" class="form-control" value="{{ $settings['site_meta_title'] ?? 'Meridian International School & College' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Meta Description</label>
                    <textarea name="site_meta_description" class="form-control" rows="2">{{ $settings['site_meta_description'] ?? 'Meridian International School & College — shaping future leaders through excellence in academics, character and innovation.' }}</textarea>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-secondary">Website Logo</label>
                        <input type="file" name="site_logo" class="form-control" accept="image/*">
                        @if(isset($settings['site_logo']))
                            <div class="mt-2 p-2 bg-dark rounded d-inline-block">
                                <img src="{{ $settings['site_logo'] }}" style="max-height: 40px;" alt="Logo">
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary">Favicon</label>
                        <input type="file" name="site_favicon" class="form-control" accept="image/x-icon,image/png">
                        @if(isset($settings['site_favicon']))
                            <div class="mt-2 p-2 border rounded d-inline-block">
                                <img src="{{ $settings['site_favicon'] }}" style="max-height: 32px;" alt="Favicon">
                            </div>
                        @endif
                    </div>
                </div>

                <h6 class="fw-bold mt-4 mb-3">Contact Information</h6>
                <div class="mb-3">
                    <label class="form-label text-secondary">Contact Email</label>
                    <input type="email" name="school_email" class="form-control" value="{{ $settings['school_email'] ?? 'admin@school.com' }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Contact Phone</label>
                    <input type="text" name="school_phone" class="form-control" value="{{ $settings['school_phone'] ?? '+1 234 567 890' }}" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-secondary">School Address</label>
                    <textarea name="school_address" class="form-control" rows="3" required>{{ $settings['school_address'] ?? '123 Education Lane, Learning City' }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2"><i class="fa-solid fa-floppy-disk me-2"></i>Save System Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection
