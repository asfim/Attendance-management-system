@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0">Edit Student Profile</h5>
                <p class="text-muted fs-7 mb-0">Update personal, academic &amp; guardian details</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back to Profile
                </a>
            </div>
        </div>

        <div class="card glass-card border-0 p-4">

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- === ACADEMIC DETAILS === --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary border-bottom border-opacity-25 pb-2 mb-3">
                            <i class="fa-solid fa-graduation-cap me-2"></i>Academic Allocation
                        </h6>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Academic Session</label>
                        <select name="session_id" class="form-select" required>
                            @foreach($sessions as $sess)
                                <option value="{{ $sess->id }}" {{ $student->session_id == $sess->id ? 'selected' : '' }}>
                                    {{ $sess->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Admission Date</label>
                        <input type="date" name="admission_date" class="form-control"
                            value="{{ old('admission_date', $student->admission_date?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Class</label>
                        <select name="class_id" class="form-select" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ $student->class_id == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Section</label>
                        <select name="section_id" class="form-select" required>
                            @foreach($classes as $class)
                                @foreach($class->sections as $sec)
                                    <option value="{{ $sec->id }}" {{ $student->section_id == $sec->id ? 'selected' : '' }}>
                                        {{ $class->name }} — {{ $sec->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mt-3">
                        <label class="form-label text-secondary">Shift (Optional)</label>
                        <select name="shift_id" class="form-select">
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ $student->shift_id == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- === STUDENT PERSONAL DETAILS === --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary border-bottom border-opacity-25 pb-2 mb-3">
                            <i class="fa-solid fa-user me-2"></i>Personal &amp; Account Details
                        </h6>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary"><i class="bi bi-fingerprint text-primary me-1"></i> Biometric Machine ID</label>
                        <input type="text" name="biometric_id" class="form-control"
                            value="{{ old('biometric_id', $student->biometric_id) }}" placeholder="e.g. 1001">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Full Name</label>
                        <input type="text" name="name" class="form-control"
                            value="{{ old('name', $student->user->name) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Email Address</label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $student->user->email) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">
                            Password
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="student-password" class="form-control"
                                placeholder="Enter password" value="{{ old('password', $student->plain_password) }}">
                            <button type="button" class="btn btn-outline-secondary" id="toggle-password" title="Show/Hide Password">
                                <i class="fa-solid fa-eye" id="toggle-password-icon"></i>
                            </button>
                        </div>
                        <div class="fs-8 text-muted mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>Min. 8 characters
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Admission No</label>
                        <input type="text" name="admission_no" class="form-control"
                            value="{{ old('admission_no', $student->admission_no) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Roll No</label>
                        <input type="text" name="roll_no" class="form-control"
                            value="{{ old('roll_no', $student->roll_no) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Date of Birth</label>
                        <input type="date" name="dob" class="form-control"
                            value="{{ old('dob', $student->dob?->format('Y-m-d')) }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="male"   {{ strtolower($student->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ strtolower($student->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other"  {{ strtolower($student->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Blood Group</label>
                        <input type="text" name="blood_group" class="form-control"
                            value="{{ old('blood_group', $student->blood_group) }}" placeholder="A+">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary">Medical Information</label>
                        <input type="text" name="medical_info" class="form-control"
                            value="{{ old('medical_info', $student->medical_info) }}" placeholder="Allergies, conditions...">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="active"      {{ $student->status === 'active'      ? 'selected' : '' }}>Active</option>
                            <option value="inactive"    {{ $student->status === 'inactive'    ? 'selected' : '' }}>Inactive</option>
                            <option value="transferred" {{ $student->status === 'transferred' ? 'selected' : '' }}>Transferred</option>
                            <option value="graduated"   {{ $student->status === 'graduated'   ? 'selected' : '' }}>Graduated</option>
                        </select>
                    </div>
                </div>

                {{-- === PHOTO & SIGNATURE === --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary border-bottom border-opacity-25 pb-2 mb-3">
                            <i class="fa-solid fa-camera me-2"></i>Photo &amp; Signature
                        </h6>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary">Student Photo <span class="text-muted">(Leave blank to keep existing)</span></label>
                        @if($student->photo_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $student->photo_path) }}" alt="Current Photo"
                                    style="height:60px; width:60px; border-radius:8px; object-fit:cover; border:2px solid rgba(56,189,248,0.4);">
                                <small class="text-muted ms-2">Current photo</small>
                            </div>
                        @endif
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary">Authorized Signature <span class="text-muted">(Leave blank to keep existing)</span></label>
                        @if($student->signature_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $student->signature_path) }}" alt="Current Signature"
                                    style="height:40px; max-width:120px; object-fit:contain; border:1px solid rgba(255,255,255,0.1); border-radius:6px; background:rgba(255,255,255,0.05); padding:4px;">
                                <small class="text-muted ms-2">Current signature</small>
                            </div>
                        @endif
                        <input type="file" name="signature" class="form-control" accept="image/*">
                    </div>
                </div>

                {{-- === GUARDIAN DETAILS === --}}
                {{-- === GUARDIAN DETAILS === --}}
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-primary border-bottom border-opacity-25 pb-2 mb-3">
                            <i class="fa-solid fa-people-roof me-2"></i>Guardian Details
                        </h6>
                    </div>

                    @if($student->parent)
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Parent Name</label>
                            <input type="text" class="form-control" value="{{ $student->parent->user->name }}" disabled>
                            <small class="text-muted">To change name, update parent account separately.</small>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Phone Number</label>
                            <input type="text" name="parent_phone" class="form-control"
                                value="{{ old('parent_phone', $student->parent->phone) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Occupation</label>
                            <input type="text" name="parent_occupation" class="form-control"
                                value="{{ old('parent_occupation', $student->parent->occupation) }}">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label text-secondary">Home Address</label>
                            <input type="text" name="parent_address" class="form-control"
                                value="{{ old('parent_address', $student->parent->address) }}">
                        </div>
                    @else
                        <div class="col-12 mb-2">
                            <div class="alert alert-warning py-2 mb-0 border-0 rounded-3 text-dark">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i> This student has no parent assigned. Fill in the details below to create one.
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Parent Name <span class="text-danger">*</span></label>
                            <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Parent Email <span class="text-danger">*</span></label>
                            <input type="email" name="parent_email" class="form-control" value="{{ old('parent_email') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="parent_phone" class="form-control" value="{{ old('parent_phone') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Occupation</label>
                            <input type="text" name="parent_occupation" class="form-control" value="{{ old('parent_occupation') }}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-secondary">Home Address <span class="text-danger">*</span></label>
                            <input type="text" name="parent_address" class="form-control" value="{{ old('parent_address') }}">
                        </div>
                    @endif
                </div>

                {{-- === HOSTEL ACCOMMODATION === --}}
                @php
                    $hasHostel = $student->hostelAllocation && $student->hostelAllocation->status === 'active';
                    $currentBed = $hasHostel ? $student->hostelAllocation->bed : null;
                    $currentRoom = $currentBed ? $currentBed->room : null;
                    $currentHostel = $currentRoom ? $currentRoom->hostel : null;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-opacity-25 pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-hotel me-2"></i>Hostel Accommodation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="assign_hostel_switch" name="assign_hostel" value="1" {{ $hasHostel ? 'checked' : '' }} onchange="toggleHostelSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="assign_hostel_switch">Assign Hostel Bed</label>
                        </div>
                    </div>

                    @if($hasHostel && $currentHostel)
                        <div class="col-12 mb-2">
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center py-2 px-3 mb-0" style="border-radius: 10px;">
                                <i class="fa-solid fa-bed me-2 fs-5"></i>
                                <div>
                                    <span class="fw-bold">Current Bed Allocation:</span> 
                                    {{ $currentHostel->name }} — Room {{ $currentRoom->room_number }} ({{ $currentBed->bed_number }})
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="hostel_fields_container" class="row g-3" style="display: {{ $hasHostel ? 'flex' : 'none' }};">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Hall / House</label>
                            <select name="hostel_id" id="edit_student_hostel_id" class="form-select" onchange="loadRoomsForStudentEdit(this.value)">
                                <option value="">Select Hall / House</option>
                                @foreach($hostels ?? [] as $h)
                                    <option value="{{ $h->id }}" {{ ($currentHostel && $currentHostel->id == $h->id) ? 'selected' : '' }}>
                                        {{ $h->name }} ({{ $h->type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Room (Available Only)</label>
                            <select name="room_id" id="edit_student_room_id" class="form-select" onchange="loadBedsForStudentEdit(this.value)" {{ $hasHostel ? '' : 'disabled' }}>
                                @if($hasHostel && $currentRoom)
                                    <option value="{{ $currentRoom->id }}" selected>Room {{ $currentRoom->room_number }} (Current)</option>
                                @else
                                    <option value="">Select Room</option>
                                @endif
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Bed</label>
                            <select name="bed_id" id="edit_student_bed_id" class="form-select" {{ $hasHostel ? '' : 'disabled' }}>
                                @if($hasHostel && $currentBed)
                                    <option value="{{ $currentBed->id }}" selected>{{ $currentBed->bed_number }} (Current)</option>
                                @else
                                    <option value="">Select Bed</option>
                                @endif
                            </select>
                    </div>
                </div>

                {{-- === TRANSPORT ALLOCATION === --}}
                @php
                    $hasTransport = $student->transportAllocation && $student->transportAllocation->status === 'active';
                    $currentTransportAlloc = $hasTransport ? $student->transportAllocation : null;
                    $currentRoute = $currentTransportAlloc ? $currentTransportAlloc->route : null;
                    $currentStop = $currentTransportAlloc ? $currentTransportAlloc->stop : null;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-opacity-25 pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-bus me-2"></i>Transport Allocation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="assign_transport_switch" name="assign_transport" value="1" {{ $hasTransport ? 'checked' : '' }} onchange="toggleTransportSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="assign_transport_switch">Transport Required</label>
                        </div>
                    </div>

                    @if($hasTransport && $currentRoute)
                        <div class="col-12 mb-2">
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center py-2 px-3 mb-0" style="border-radius: 10px;">
                                <i class="fa-solid fa-bus-simple me-2 fs-5"></i>
                                <div>
                                    <span class="fw-bold">Current Transport Allocation:</span> 
                                    Route: {{ $currentRoute->route_name }}
                                    @if($currentStop) — Stop: {{ $currentStop->stop_name }} @endif
                                    (Fee: ৳{{ number_format($currentTransportAlloc->monthly_fee, 2) }})
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="transport_fields_container" class="row g-3" style="display: {{ $hasTransport ? 'flex' : 'none' }};">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Select Route <span class="text-danger">*</span></label>
                            <select name="route_id" id="edit_student_route_id" class="form-select" onchange="loadStopsForStudentTransport(this.value)">
                                <option value="">Select Route</option>
                                @foreach($transportRoutes ?? [] as $route)
                                    <option value="{{ $route->id }}" data-fee="{{ $route->default_monthly_fee }}" {{ ($currentRoute && $currentRoute->id == $route->id) ? 'selected' : '' }}>
                                        {{ $route->route_name }} ({{ $route->start_point }} - {{ $route->end_point }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Pickup/Drop Point <span class="text-danger">*</span></label>
                            <select name="stop_id" id="edit_student_stop_id" class="form-select" onchange="updateTransportFee()" {{ $hasTransport ? '' : 'disabled' }}>
                                @if($hasTransport && $currentStop)
                                    <option value="{{ $currentStop->id }}" data-fee="{{ $currentStop->additional_fee }}" selected>{{ $currentStop->stop_name }} (Fee: ৳{{ number_format($currentStop->additional_fee, 2) }})</option>
                                @else
                                    <option value="">Select Stop</option>
                                @endif
                            </select>
                        </div>

                        <input type="hidden" name="transport_monthly_fee" id="edit_student_transport_fee" value="{{ $currentTransportAlloc ? $currentTransportAlloc->monthly_fee : '0.00' }}">

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="transport_effective_from" class="form-control" value="{{ $currentTransportAlloc ? $currentTransportAlloc->effective_from->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                {{-- === FOOD ALLOCATION === --}}
                @php
                    $isFoodEnabled = $student->is_food_enabled;
                    $currentFoodAlloc = $isFoodEnabled ? $student->foodAllocation : null;
                    $currentFoodPlan = $currentFoodAlloc ? $currentFoodAlloc->foodPlan : null;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom border-opacity-25 pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-utensils me-2"></i>Food Allocation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_food_enabled_switch" name="is_food_enabled" value="1" {{ $isFoodEnabled ? 'checked' : '' }} onchange="toggleFoodSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="is_food_enabled_switch">Food Service [ ON / OFF ]</label>
                        </div>
                    </div>

                    @if($isFoodEnabled && $currentFoodPlan)
                        <div class="col-12 mb-2">
                            <div class="alert alert-info border-0 shadow-sm d-flex align-items-center py-2 px-3 mb-0" style="border-radius: 10px;">
                                <i class="fa-solid fa-pizza-slice me-2 fs-5"></i>
                                <div>
                                    <span class="fw-bold">Current Food Plan:</span> 
                                    {{ $currentFoodPlan->name }} (Fee: ৳{{ number_format($currentFoodAlloc->monthly_fee ?? $currentFoodPlan->monthly_fee, 2) }}/month)
                                </div>
                            </div>
                        </div>
                    @endif

                    <div id="food_fields_container" class="row g-3" style="display: {{ $isFoodEnabled ? 'flex' : 'none' }};">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Select Food Plan <span class="text-danger">*</span></label>
                            <select name="food_plan_id" id="edit_student_food_plan_id" class="form-select">
                                <option value="">Select Plan</option>
                                @foreach($foodPlans ?? [] as $plan)
                                    <option value="{{ $plan->id }}" {{ ($currentFoodPlan && $currentFoodPlan->id == $plan->id) ? 'selected' : '' }}>
                                        {{ $plan->name }} (৳{{ number_format($plan->monthly_fee, 2) }}/month)
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Food Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="food_start_date" class="form-control" value="{{ $currentFoodAlloc && $currentFoodAlloc->start_date ? \Carbon\Carbon::parse($currentFoodAlloc->start_date)->format('Y-m-d') : date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-primary px-5">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Save Changes
                    </button>
                    <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-outline-secondary px-4">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleHostelSection(isCheck) {
    const container = document.getElementById('hostel_fields_container');
    container.style.display = isCheck ? 'flex' : 'none';
}

function loadRoomsForStudentEdit(hostelId) {
    const roomSelect = document.getElementById('edit_student_room_id');
    const bedSelect = document.getElementById('edit_student_bed_id');
    
    roomSelect.innerHTML = '<option value="">Loading available rooms...</option>';
    roomSelect.disabled = true;
    bedSelect.innerHTML = '<option value="">Select Bed</option>';
    bedSelect.disabled = true;

    if (!hostelId) return;

    fetch(`/admin/hostel/available-rooms/${hostelId}`)
        .then(res => res.json())
        .then(rooms => {
            if (rooms.length === 0) {
                roomSelect.innerHTML = '<option value="">No rooms with available beds in this hall</option>';
                roomSelect.disabled = true;
                return;
            }

            roomSelect.innerHTML = '<option value="">Select Room</option>';
            rooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `Room ${r.room_number} (${r.available_beds} beds available, Fee: $${r.cost_per_bed})`;
                roomSelect.appendChild(opt);
            });
            roomSelect.disabled = false;
        });
}

function loadBedsForStudentEdit(roomId) {
    const bedSelect = document.getElementById('edit_student_bed_id');
    bedSelect.innerHTML = '<option value="">Loading available beds...</option>';
    bedSelect.disabled = true;

    if (!roomId) return;

    fetch(`/admin/hostel/available-beds/${roomId}`)
        .then(res => res.json())
        .then(beds => {
            if (beds.length === 0) {
                bedSelect.innerHTML = '<option value="">No available beds</option>';
                bedSelect.disabled = true;
                return;
            }

            bedSelect.innerHTML = '<option value="">Select Bed</option>';
            beds.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = `Bed ${b.bed_number}`;
                bedSelect.appendChild(opt);
            });
            bedSelect.disabled = false;
        });
}

function toggleTransportSection(isCheck) {
    const container = document.getElementById('transport_fields_container');
    container.style.display = isCheck ? 'flex' : 'none';
}

function toggleFoodSection(enabled) {
    const container = document.getElementById('food_fields_container');
    container.style.display = enabled ? 'flex' : 'none';
}

function loadStopsForStudentTransport(routeId) {
    const stopSelect = document.getElementById('edit_student_stop_id');
    const feeInput = document.getElementById('edit_student_transport_fee');
    const routeSelect = document.getElementById('edit_student_route_id');
    
    stopSelect.innerHTML = '<option value="">Loading stops...</option>';
    stopSelect.disabled = true;

    if (!routeId) {
        feeInput.value = '0.00';
        return;
    }

    feeInput.value = '0.00';

    fetch(`/admin/transport/route-stops/${routeId}`)
        .then(res => res.json())
        .then(stops => {
            if (stops.length === 0) {
                stopSelect.innerHTML = '<option value="">No stops defined for this route</option>';
                stopSelect.disabled = true;
                return;
            }

            stopSelect.innerHTML = '<option value="">Select Stop</option>';
            stops.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.setAttribute('data-fee', s.additional_fee);
                opt.textContent = `${s.stop_name} (Fee: ৳${parseFloat(s.additional_fee).toFixed(2)})`;
                stopSelect.appendChild(opt);
            });
            stopSelect.disabled = false;
        });
}

function updateTransportFee() {
    const stopSelect = document.getElementById('edit_student_stop_id');
    const feeInput = document.getElementById('edit_student_transport_fee');
    
    if (stopSelect.selectedIndex > 0) {
        const stopOption = stopSelect.options[stopSelect.selectedIndex];
        const fee = parseFloat(stopOption.getAttribute('data-fee') || 0);
        feeInput.value = fee.toFixed(2);
    } else {
        feeInput.value = '0.00';
    }
}

// Password show/hide toggle
document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('toggle-password');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            const input = document.getElementById('student-password');
            const icon = document.getElementById('toggle-password-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    }
});
</script>
@endsection
