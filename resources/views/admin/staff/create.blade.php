@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('admin.staff.index') }}" class="btn btn-link text-light p-0 me-3 fs-5"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-user-plus text-primary me-2"></i> Add Staff
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">Create a teacher or non-teaching staff record.</p>
        </div>
    </div>

    <form action="{{ route('admin.staff.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Role Section -->
        <div class="card glass-card border border-secondary border-opacity-25 rounded-3 mb-4 bg-transparent p-4">
            <h6 class="fw-bold text-light mb-1">Role</h6>
            <p class="text-muted fs-7 mb-3">What kind of staff member is this?</p>
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Staff type <span class="text-danger">*</span></label>
                    <input type="text" name="role_name" list="role-options" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. Teacher, HR, Librarian" required>
                    <datalist id="role-options">
                        @foreach($roles as $role)
                            <option value="{{ $role->display_name }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Designation</label>
                    <input type="text" name="designation" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. Senior Teacher">
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7">Department</label>
                    <input type="text" name="department" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. Science">
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
                    <div id="photo_preview_container" class="border border-secondary border-opacity-50 border-dashed rounded-3 d-flex flex-column align-items-center justify-content-center text-muted overflow-hidden position-relative" style="height: 120px; cursor: pointer; border-style: dashed !important;" onclick="document.getElementById('photo_upload').click()">
                        <img id="photo_preview" src="" alt="Preview" class="w-100 h-100 object-fit-cover d-none position-absolute top-0 start-0">
                        <i id="photo_icon" class="fa-solid fa-upload mb-2 fs-5"></i>
                        <span id="photo_text" class="fs-7">Upload</span>
                    </div>
                    <input type="file" id="photo_upload" name="photo" class="d-none" accept="image/*" onchange="previewImage(this, 'photo_preview', 'photo_icon', 'photo_text')">
                </div>

                <!-- Signature Upload -->
                <div class="col-md-2">
                    <label class="form-label text-light fs-7">Signature</label>
                    <div id="signature_preview_container" class="border border-secondary border-opacity-50 border-dashed rounded-3 d-flex flex-column align-items-center justify-content-center text-muted overflow-hidden position-relative" style="height: 120px; cursor: pointer; border-style: dashed !important;" onclick="document.getElementById('signature_upload').click()">
                        <img id="signature_preview" src="" alt="Preview" class="w-100 h-100 object-fit-contain d-none position-absolute top-0 start-0 p-1">
                        <i id="signature_icon" class="fa-solid fa-signature mb-2 fs-5"></i>
                        <span id="signature_text" class="fs-7">Upload</span>
                    </div>
                    <input type="file" id="signature_upload" name="signature_path" class="d-none" accept="image/*" onchange="previewImage(this, 'signature_preview', 'signature_icon', 'signature_text')">
                </div>
                    
                    <script>
                        function previewImage(input, previewId, iconId, textId) {
                            if (input.files && input.files[0]) {
                                var reader = new FileReader();
                                reader.onload = function (e) {
                                    document.getElementById(previewId).src = e.target.result;
                                    document.getElementById(previewId).classList.remove('d-none');
                                    document.getElementById(iconId).classList.add('d-none');
                                    document.getElementById(textId).classList.add('d-none');
                                }
                                reader.readAsDataURL(input.files[0]);
                            }
                        }
                    </script>
                </div>
                
                <div class="col-md-10">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7"><i class="bi bi-fingerprint text-primary me-1"></i> Biometric Machine ID</label>
                            <input type="text" name="biometric_id" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. 2001">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Full name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Bangla name</label>
                            <input type="text" name="bangla_name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Date of birth</label>
                            <input type="date" name="dob" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Gender</label>
                            <select name="gender" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" disabled selected>—</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Religion</label>
                            <input type="text" name="religion" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Blood group</label>
                            <select name="blood_group" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" disabled selected>— Select Blood Group —</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">National ID</label>
                            <input type="text" name="national_id" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7">Marital status</label>
                            <select name="marital_status" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                                <option value="" disabled selected>—</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
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
                    <input type="text" name="phone" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Email <small class="text-secondary">(Leave blank to auto-generate)</small></label>
                    <input type="email" name="email" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. john.doe@school.com">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Password <small class="text-secondary">(Leave blank to auto-generate)</small></label>
                    <input type="password" name="password" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" minlength="8" placeholder="********">
                </div>
                <div class="col-md-6"></div> <!-- spacer -->
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Present address</label>
                    <input type="text" name="address" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Permanent address</label>
                    <input type="text" name="permanent_address" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Emergency contact name</label>
                    <input type="text" name="emergency_contact_name" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Emergency contact phone</label>
                    <input type="text" name="emergency_contact_phone" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
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
                        <option value="Full-time">Full-time</option>
                        <option value="Part-time">Part-time</option>
                        <option value="Contract">Contract</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Status</label>
                    <select name="status" class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Join date <span class="text-danger">*</span></label>
                    <input type="date" name="joining_date" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-light fs-7">Experience</label>
                    <input type="text" name="experience" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" placeholder="e.g. 5 years">
                </div>
                <div class="col-12">
                    <label class="form-label text-light fs-7">Qualifications</label>
                    <textarea name="qualifications" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light" rows="3"></textarea>
                </div>

                <div class="col-12 mt-3">
                    <label class="form-label text-light fs-7 d-block mb-2">Assigned Shifts (Optional)</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach($shifts as $shift)
                            <div class="form-check">
                                <input class="form-check-input border-secondary" type="checkbox" name="shift_ids[]" value="{{ $shift->id }}" id="shift_{{ $shift->id }}">
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
                    <input type="number" step="0.01" name="salary" class="form-control form-control-sm bg-transparent border-secondary border-opacity-50 text-light">
                </div>
            </div>

            <div id="allowances-container">
                <!-- Allowances will be appended here -->
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="button" id="add-allowance-btn" class="btn btn-sm btn-outline-secondary border-opacity-50 text-light px-3 py-1" style="border-radius: 8px;"><i class="fa-solid fa-plus me-1"></i> Allowance</button>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="d-flex justify-content-end gap-3 mb-5">
            <a href="{{ route('admin.staff.index') }}" class="btn btn-link text-light text-decoration-none fw-semibold">Cancel</a>
            <button type="submit" class="btn btn-light fw-semibold px-4 py-2 text-dark" style="border-radius: 8px;">
                <i class="fa-solid fa-user-plus me-2"></i> Create staff
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

            row.querySelector('.remove-allowance-btn').addEventListener('click', function() {
                row.remove();
            });
        });
    });
</script>
@endsection
