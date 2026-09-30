@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card glass-card border-0 p-4 mb-4">
            <h5 class="fw-bold mb-4 border-bottom pb-3"><i class="fa-solid fa-address-card me-2"></i>Application Details</h5>
            
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Applicant Name</p>
                    <p class="fw-semibold">{{ $application->name }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Applicant Email</p>
                    <p class="fw-semibold">{{ $application->email ?? 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Date of Birth</p>
                    <p class="fw-semibold">{{ $application->dob ? $application->dob->format('d M, Y') : 'N/A' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Gender & Blood Group</p>
                    <p class="fw-semibold">{{ $application->gender }} / {{ $application->blood_group ?? 'Unknown' }}</p>
                </div>
            </div>
            
            <h6 class="fw-bold text-primary mb-3 mt-4 border-bottom pb-2">Parent / Guardian Information</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Guardian Name</p>
                    <p class="fw-semibold">{{ $application->parent_name }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Guardian Phone</p>
                    <p class="fw-semibold">{{ $application->parent_phone }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Guardian Email</p>
                    <p class="fw-semibold">{{ $application->parent_email }}</p>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Occupation</p>
                    <p class="fw-semibold">{{ $application->parent_occupation ?? 'N/A' }}</p>
                </div>
                <div class="col-12">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Home Address</p>
                    <p class="fw-semibold">{{ $application->parent_address }}</p>
                </div>
            </div>

            <h6 class="fw-bold text-primary mb-3 mt-4 border-bottom pb-2">Academic Request</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Session</p>
                    <p class="fw-semibold">{{ $application->academicSession->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Class</p>
                    <p class="fw-semibold">{{ $application->schoolClass->name ?? 'N/A' }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted mb-1 text-uppercase fw-bold" style="font-size: 0.75rem;">Section</p>
                    <p class="fw-semibold">{{ $application->section->name ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4 border-bottom pb-3">Actions</h5>

            @if(session('error'))
                <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                    {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm" style="border-radius: 12px;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                    <ul class="mb-0 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($application->status === 'pending')
                <form action="{{ route('admin.admissions.merge', $application->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Assign Admission No <span class="text-danger">*</span></label>
                        <input type="text" name="admission_no" class="form-control" value="ADM-{{ rand(1000, 9999) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Assign Roll No <span class="text-danger">*</span></label>
                        <input type="text" name="roll_no" class="form-control" placeholder="e.g. 1" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Assign Password <span class="text-danger">*</span></label>
                        <input type="text" name="password" class="form-control" value="student123" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 shadow-sm" onclick="return confirm('Are you sure you want to merge this application and create a student profile?')">
                        <i class="fa-solid fa-code-merge me-2"></i>Merge to Students
                    </button>
                </form>
            @else
                <div class="alert alert-success d-flex align-items-center mb-0" style="border-radius: 12px;">
                    <i class="fa-solid fa-check-circle fs-4 me-3"></i>
                    <div>
                        <h6 class="mb-0 fw-bold">Merged successfully</h6>
                        <small>This application has been processed.</small>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
