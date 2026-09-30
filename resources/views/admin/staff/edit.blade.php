@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-link text-light p-0 me-3 fs-5"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-user-pen text-primary me-2"></i> Edit Staff
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">Update staff record for {{ $staff->user->name }}.</p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success border-0 rounded-3 mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.staff.update', $staff->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- Role Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-1">Role</h6>
            <p class="text-muted fs-7 mb-3">What kind of staff member is this?</p>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Staff type <span class="text-danger">*</span></label>
                    <input type="text" name="role_name" list="role-options" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->user->role->display_name ?? '' }}" placeholder="e.g. Teacher, HR, Librarian" required>
                    <datalist id="role-options">
                        @foreach($roles as $role)
                            <option value="{{ $role->display_name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Designation</label>
                    <input type="text" name="designation" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->designation }}" placeholder="e.g. Senior Teacher">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Department</label>
                    <input type="text" name="department" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->department }}" placeholder="e.g. Science">
                </div>
            </div>
        </div>

        <!-- Personal Details Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-3">Personal details</h6>
            
            <div class="row g-3">
                <!-- Photo Upload -->
                <div class="col-md-2">
                    <label class="form-label text-light fs-7">Photo</label>
                    <div class="border border-secondary border-opacity-50 border-dashed rounded-3 d-flex flex-column align-items-center justify-content-center text-muted" style="height: 120px; cursor: pointer; border-style: dashed !important; position: relative; overflow: hidden;" onclick="document.getElementById('photo_upload').click()">
                        @if($staff->photo)
                            <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <i class="fa-solid fa-upload mb-2 fs-5"></i>
                            <span class="fs-7">Upload</span>
                        @endif
                    </div>
                    <input type="file" id="photo_upload" name="photo" class="d-none" accept="image/*">
                </div>

                <!-- Signature Upload -->
                <div class="col-md-2">
                    <label class="form-label text-light fs-7">Signature</label>
                    <div class="border border-secondary border-opacity-50 border-dashed rounded-3 d-flex flex-column align-items-center justify-content-center text-muted" style="height: 120px; cursor: pointer; border-style: dashed !important; position: relative; overflow: hidden;" onclick="document.getElementById('signature_upload').click()">
                        @if($staff->signature_path)
                            <img src="{{ asset('storage/' . $staff->signature_path) }}" alt="Signature" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;">
                        @else
                            <i class="fa-solid fa-signature mb-2 fs-5"></i>
                            <span class="fs-7">Upload</span>
                        @endif
                    </div>
                    <input type="file" id="signature_upload" name="signature_path" class="d-none" accept="image/*">
                </div>
                
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7"><i class="bi bi-fingerprint text-primary me-1"></i> Biometric Machine ID</label>
                            <input type="text" name="biometric_id" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ old('biometric_id', $staff->biometric_id) }}" placeholder="e.g. 2001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Full name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->user->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Bangla name</label>
                            <input type="text" name="bangla_name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->bangla_name }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Date of birth</label>
                            <input type="date" name="dob" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->dob ? \Carbon\Carbon::parse($staff->dob)->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Gender</label>
                            <select name="gender" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" disabled {{ !$staff->gender ? 'selected' : '' }}>—</option>
                                <option value="Male" {{ $staff->gender == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ $staff->gender == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ $staff->gender == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Religion</label>
                            <input type="text" name="religion" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->religion }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Blood group</label>
                            <select name="blood_group" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" {{ !$staff->blood_group ? 'selected' : '' }}>— Select Blood Group —</option>
                                <option value="A+" {{ $staff->blood_group == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ $staff->blood_group == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ $staff->blood_group == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ $staff->blood_group == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ $staff->blood_group == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ $staff->blood_group == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ $staff->blood_group == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ $staff->blood_group == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">National ID</label>
                            <input type="text" name="national_id" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->national_id }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Marital status</label>
                            <select name="marital_status" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" disabled {{ !$staff->marital_status ? 'selected' : '' }}>—</option>
                                <option value="Single" {{ $staff->marital_status == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ $staff->marital_status == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Divorced" {{ $staff->marital_status == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-3">Contact</h6>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Mobile</label>
                    <input type="text" name="phone" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->phone }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->user->email }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Password <small class="text-secondary">(Leave blank to keep unchanged)</small></label>
                    <input type="password" name="password" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="********">
                </div>
                <div class="col-md-6"></div> <!-- spacer -->
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Present address</label>
                    <input type="text" name="address" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->address }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Permanent address</label>
                    <input type="text" name="permanent_address" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->permanent_address }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Emergency contact name</label>
                    <input type="text" name="emergency_contact_name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->emergency_contact_name }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Emergency contact phone</label>
                    <input type="text" name="emergency_contact_phone" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->emergency_contact_phone }}">
                </div>
            </div>
        </div>

        <!-- Employment Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-3">Employment</h6>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Employment type</label>
                    <select name="employment_type" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                        <option value="Full-time" {{ $staff->employment_type == 'Full-time' ? 'selected' : '' }}>Full-time</option>
                        <option value="Part-time" {{ $staff->employment_type == 'Part-time' ? 'selected' : '' }}>Part-time</option>
                        <option value="Contract" {{ $staff->employment_type == 'Contract' ? 'selected' : '' }}>Contract</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Status</label>
                    <select name="status" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                        <option value="active" {{ $staff->status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $staff->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Join date <span class="text-danger">*</span></label>
                    <input type="date" name="joining_date" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->joining_date ? $staff->joining_date->format('Y-m-d') : '' }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Experience</label>
                    <input type="text" name="experience" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->experience }}" placeholder="e.g. 5 years">
                </div>
                <div class="col-12">
                    <label class="form-label text-light fs-7">Qualifications</label>
                    <textarea name="qualifications" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" rows="3">{{ $staff->qualifications }}</textarea>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label text-light fs-7 d-block mb-2">Assigned Shifts (Optional)</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($shifts as $shift)
                            <div class="form-check">
                                <input class="form-check-input border-secondary" type="checkbox" name="shift_ids[]" value="{{ $shift->id }}" id="shift_{{ $shift->id }}" {{ in_array($shift->id, old('shift_ids', $staff->shifts->pluck('id')->toArray())) ? 'checked' : '' }}>
                                <label class="form-check-label text-light fs-7" for="shift_{{ $shift->id }}">
                                    {{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Salary Structure Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-1">Salary structure</h6>
            <p class="text-muted fs-7 mb-3">Optional — set now, or add later from the profile. Used to generate monthly payslips.</p>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Basic salary (৳)</label>
                    <input type="number" step="0.01" name="salary" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $staff->salary }}">
                </div>
            </div>

            <div id="allowances-container">
                @foreach($staff->allowances as $allowance)
                <div class="row g-3 mt-2 align-items-end allowance-row">
                    <div class="col-md-5">
                        <label class="form-label text-light fs-7">Name (e.g. House Rent)</label>
                        <input type="text" name="allowances[name][]" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $allowance->name }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-light fs-7">Amount</label>
                        <input type="number" step="0.01" name="allowances[amount][]" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" value="{{ $allowance->amount }}" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-sm btn-outline-danger border-opacity-50 px-3 py-1 rounded remove-allowance-btn"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" id="add-allowance-btn" class="btn btn-sm btn-outline-secondary border-opacity-50 text-light px-3 py-1 rounded-pill"><i class="fa-solid fa-plus me-1"></i> Allowance</button>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary fw-semibold px-4 py-2 rounded-pill">Cancel</a>
            <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 text-white rounded-pill">
                <i class="fa-solid fa-save me-2"></i> Update staff
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('add-allowance-btn').addEventListener('click', function() {
            const container = document.getElementById('allowances-container');
            const row = document.createElement('div');
            row.className = 'row g-3 mt-2 align-items-end allowance-row';
            row.innerHTML = `
                <div class="col-md-5">
                    <label class="form-label text-light fs-7">Name (e.g. House Rent)</label>
                    <input type="text" name="allowances[name][]" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Amount</label>
                    <input type="number" step="0.01" name="allowances[amount][]" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-danger border-opacity-50 px-3 py-1 rounded remove-allowance-btn"><i class="fa-solid fa-trash"></i></button>
                </div>
            `;
            container.appendChild(row);

            // Bind remove event to newly created row
            row.querySelector('.remove-allowance-btn').addEventListener('click', function() {
                row.remove();
            });
        });

        // Bind remove event to existing rows
        document.querySelectorAll('.remove-allowance-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.allowance-row').remove();
            });
        });

        // Photo preview
        const photoUpload = document.getElementById('photo_upload');
        if (photoUpload) {
            photoUpload.addEventListener('change', function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    const file = e.target.files[0];
                    if (!file.type.startsWith('image/')) {
                        alert('Please upload a valid image file.');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = photoUpload.previousElementSibling;
                        container.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">`;
                    }
                    reader.readAsDataURL(file);
                } else {
                    alert('No image selected for photo.');
                }
            });
        }

        // Signature preview
        const signatureUpload = document.getElementById('signature_upload');
        if (signatureUpload) {
            signatureUpload.addEventListener('change', function(e) {
                if (e.target.files && e.target.files.length > 0) {
                    const file = e.target.files[0];
                    if (!file.type.startsWith('image/')) {
                        alert('Please upload a valid image file.');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const container = signatureUpload.previousElementSibling;
                        container.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 100%; height: 100%; object-fit: contain; padding: 4px;">`;
                    }
                    reader.readAsDataURL(file);
                } else {
                    alert('No image selected for signature.');
                }
            });
        }
    });
</script>
@endsection
