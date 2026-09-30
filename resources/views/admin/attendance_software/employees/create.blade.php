@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-user-plus text-primary me-2"></i>Register Employee / Staff</h3>
            <p class="text-muted small mb-0">Create new staff account with Biometric IDs, Branch, Department, Shift & Salary structure</p>
        </div>
        <a href="{{ route('admin.attendance-suite.employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Employee List
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-3 shadow-sm mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.attendance-suite.employees.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Section 1: Basic Information -->
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-id-card me-2"></i>Basic Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Tanvir Ahmed" value="{{ old('name') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="tanvir@company.com" value="{{ old('email') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Account Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Mobile Phone Number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control" placeholder="01700000000" value="{{ old('phone') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Joining Date <span class="text-danger">*</span></label>
                        <input type="date" name="joining_date" class="form-control" value="{{ old('joining_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Profile Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>
                </div>

                <!-- Section 2: Branch, Department & Shift -->
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-sitemap me-2"></i>Branch, Department & Shift</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Branch</label>
                        <select name="branch_id" class="form-select">
                            <option value="">Select Branch</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Department</label>
                        <select name="department_id" class="form-select">
                            <option value="">Select Department</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Designation</label>
                        <select name="designation_id" class="form-select">
                            <option value="">Select Designation</option>
                            @foreach($designations as $ds)
                                <option value="{{ $ds->id }}">{{ $ds->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Assigned Shift</label>
                        <select name="shift_id" class="form-select">
                            <option value="">General Shift</option>
                            @foreach($shifts as $sh)
                                <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->start_time }} - {{ $sh->end_time }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Section 3: Biometric & Hardware Integration -->
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-fingerprint me-2"></i>Biometric Device Integration</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Biometric User ID (Machine ID)</label>
                        <input type="text" name="biometric_id" class="form-control" placeholder="e.g. 1001" value="{{ old('biometric_id') }}">
                        <span class="text-muted small">Must match the ID enrolled in ZKTeco device</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Fingerprint ID / Template Ref</label>
                        <input type="text" name="fingerprint_id" class="form-control" placeholder="e.g. FP-1001" value="{{ old('fingerprint_id') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Face ID / Camera Ref</label>
                        <input type="text" name="face_id" class="form-control" placeholder="e.g. FC-1001" value="{{ old('face_id') }}">
                    </div>
                </div>

                <!-- Section 4: Salary & Payroll Structure -->
                <h5 class="fw-bold text-primary mb-3"><i class="fa-solid fa-money-bill-wave me-2"></i>Salary & Payroll Structure</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Basic Monthly Salary (BDT) <span class="text-danger">*</span></label>
                        <input type="number" name="salary" step="0.01" class="form-control" placeholder="e.g. 50000" value="{{ old('salary') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Overtime Hourly Rate (BDT)</label>
                        <input type="number" name="overtime_rate" step="0.01" class="form-control" placeholder="e.g. 250 (0 for default)" value="{{ old('overtime_rate', 0) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.attendance-suite.employees.index') }}" class="btn btn-light rounded-pill px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">Register Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
