@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Admit New Student</h5>

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm" style="border-radius: 12px;">
                    <ul class="mb-0">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.students.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row g-3 mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Academic Allocation</h6>
                    
                    @php
                        $showSessionInReg = \App\Models\Setting::get('show_session_in_registration', '1');
                        $activeSession = $sessions->where('is_active', true)->first() ?? $sessions->first();
                        $colWidth = ($showSessionInReg == '1') ? 'col-md-3' : 'col-md-4';
                    @endphp

                    @if($showSessionInReg == '1')
                        <div class="{{ $colWidth }}">
                            <label class="form-label text-secondary">Academic Session</label>
                            <select name="session_id" class="form-select" required>
                                @foreach($sessions as $sess)
                                    <option value="{{ $sess->id }}" {{ (old('session_id') == $sess->id || (!old('session_id') && $sess->is_active)) ? 'selected' : '' }}>{{ $sess->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="session_id" value="{{ $activeSession->id ?? '' }}">
                    @endif

                    <div class="{{ $colWidth }}">
                        <label class="form-label text-secondary">Admission Date</label>
                        <input type="date" name="admission_date" class="form-control" value="{{ old('admission_date', now()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="{{ $colWidth }}">
                        <label class="form-label text-secondary">Class</label>
                        <select name="class_id" class="form-select" required>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="{{ $colWidth }}">
                        <label class="form-label text-secondary">Section</label>
                        <select name="section_id" class="form-select" required>
                            @foreach($classes->first()->sections ?? [] as $sec)
                                <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="{{ $colWidth }}">
                        <label class="form-label text-secondary">Shift (Optional)</label>
                        <select name="shift_id" class="form-select">
                            <option value="">Select Shift</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }} ({{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Student Personal & Login Account Details</h6>
                    
                    <div class="col-md-3">
                        <label class="form-label text-secondary"><i class="bi bi-fingerprint text-primary me-1"></i> Biometric Machine ID</label>
                        <input type="text" name="biometric_id" class="form-control" value="{{ old('biometric_id') }}" placeholder="e.g. 1001">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-secondary">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="John Doe" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Email Address (Login Account)</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="john@school.com" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Login Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Admission No</label>
                        <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no', 'ADM-' . rand(1000,9999)) }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Roll No</label>
                        <input type="text" name="roll_no" class="form-control" value="{{ old('roll_no') }}" placeholder="e.g. 10" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" value="{{ old('dob') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Gender</label>
                        <select name="gender" class="form-select" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">Select Blood Group</option>
                            <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                            <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                            <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                            <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                            <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                            <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                            <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                            <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Medical Information</label>
                        <input type="text" name="medical_info" class="form-control" placeholder="Allergies, conditions...">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary">Student Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/*">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary">Authorized Signature</label>
                        <input type="file" name="signature" class="form-control" accept="image/*">
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">Guardian Details</h6>
                    
                    <div class="col-md-4">
                        <label class="form-label text-secondary">Parent/Guardian Full Name</label>
                        <input type="text" name="parent_name" class="form-control" value="{{ old('parent_name') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Parent Email Address</label>
                        <input type="email" name="parent_email" class="form-control" value="{{ old('parent_email') }}" placeholder="guardian@example.com" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Parent Phone Number</label>
                        <input type="text" name="parent_phone" class="form-control" value="{{ old('parent_phone') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label text-secondary">Parent Occupation</label>
                        <input type="text" name="parent_occupation" class="form-control" value="{{ old('parent_occupation') }}">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label text-secondary">Home Address</label>
                        <input type="text" name="parent_address" class="form-control" value="{{ old('parent_address') }}" required>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-hotel me-2"></i>Hostel Accommodation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="assign_hostel_switch" name="assign_hostel" value="1" onchange="toggleHostelSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="assign_hostel_switch">Assign Hostel Bed</label>
                        </div>
                    </div>

                    <div id="hostel_fields_container" class="row g-3" style="display: none;">
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Hall / House</label>
                            <select name="hostel_id" id="create_student_hostel_id" class="form-select" onchange="loadRoomsForStudentAdmission(this.value)">
                                <option value="">Select Hall / House</option>
                                @foreach($hostels ?? [] as $h)
                                    <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Room (Available Only)</label>
                            <select name="room_id" id="create_student_room_id" class="form-select" onchange="loadBedsForStudentAdmission(this.value)" disabled>
                                <option value="">Select Room</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label text-secondary">Select Bed</label>
                            <select name="bed_id" id="create_student_bed_id" class="form-select" disabled>
                                <option value="">Select Bed</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-bus me-2"></i>Transport Allocation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="assign_transport_switch" name="assign_transport" value="1" onchange="toggleTransportSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="assign_transport_switch">Transport Required</label>
                        </div>
                    </div>

                    <div id="transport_fields_container" class="row g-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Select Route <span class="text-danger">*</span></label>
                            <select name="route_id" id="create_student_route_id" class="form-select" onchange="loadStopsForStudentTransport(this.value)">
                                <option value="">Select Route</option>
                                @foreach($transportRoutes ?? [] as $route)
                                    <option value="{{ $route->id }}" data-fee="{{ $route->default_monthly_fee }}">{{ $route->route_name }} ({{ $route->start_point }} - {{ $route->end_point }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Pickup/Drop Point <span class="text-danger">*</span></label>
                            <select name="stop_id" id="create_student_stop_id" class="form-select" onchange="updateTransportFee()" disabled>
                                <option value="">Select Stop</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Monthly Transport Fee (৳)</label>
                            <input type="number" step="0.01" name="transport_monthly_fee" id="create_student_transport_fee" class="form-control" placeholder="0.00" value="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Effective From <span class="text-danger">*</span></label>
                            <input type="date" name="transport_effective_from" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                        <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-utensils me-2"></i>Food Allocation (Optional)</h6>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_food_enabled_switch" name="is_food_enabled" value="1" onchange="toggleFoodSection(this.checked)">
                            <label class="form-check-label fw-semibold fs-7" for="is_food_enabled_switch">Food Service [ ON / OFF ]</label>
                        </div>
                    </div>

                    <div id="food_fields_container" class="row g-3" style="display: none;">
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Select Food Plan <span class="text-danger">*</span></label>
                            <select name="food_plan_id" id="create_student_food_plan_id" class="form-select">
                                <option value="">Select Plan</option>
                                @foreach($foodPlans ?? [] as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} (৳{{ number_format($plan->monthly_fee, 2) }}/month)</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary">Food Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="food_start_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-circle-check me-2"></i>Complete Student Admission</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFoodSection(enabled) {
    document.getElementById('food_fields_container').style.display = enabled ? 'flex' : 'none';
}
</script>

<script>
function toggleHostelSection(isCheck) {
    const container = document.getElementById('hostel_fields_container');
    container.style.display = isCheck ? 'flex' : 'none';
}

function loadRoomsForStudentAdmission(hostelId) {
    const roomSelect = document.getElementById('create_student_room_id');
    const bedSelect = document.getElementById('create_student_bed_id');
    
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

function loadBedsForStudentAdmission(roomId) {
    const bedSelect = document.getElementById('create_student_bed_id');
    bedSelect.innerHTML = '<option value="">Loading available beds...</option>';
    bedSelect.disabled = true;

    if (!roomId) return;

    fetch(`/admin/hostel/available-beds/${roomId}`)
        .then(res => res.json())
        .then(beds => {
            if (beds.length === 0) {
                bedSelect.innerHTML = '<option value="">No beds available in this room</option>';
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

function loadStopsForStudentTransport(routeId) {
    const stopSelect = document.getElementById('create_student_stop_id');
    const feeInput = document.getElementById('create_student_transport_fee');
    const routeSelect = document.getElementById('create_student_route_id');
    
    stopSelect.innerHTML = '<option value="">Loading stops...</option>';
    stopSelect.disabled = true;

    if (!routeId) {
        feeInput.value = '0.00';
        return;
    }

    const selectedOption = routeSelect.options[routeSelect.selectedIndex];
    const defaultFee = parseFloat(selectedOption.getAttribute('data-fee') || 0);
    feeInput.value = defaultFee.toFixed(2);

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
                opt.setAttribute('data-additional-fee', s.additional_fee);
                opt.textContent = `${s.stop_name} (Fee: +$${s.additional_fee})`;
                stopSelect.appendChild(opt);
            });
            stopSelect.disabled = false;
        });
}

function updateTransportFee() {
    const routeSelect = document.getElementById('create_student_route_id');
    const stopSelect = document.getElementById('create_student_stop_id');
    const feeInput = document.getElementById('create_student_transport_fee');
    
    if (routeSelect.selectedIndex <= 0) return;
    
    const routeOption = routeSelect.options[routeSelect.selectedIndex];
    let baseFee = parseFloat(routeOption.getAttribute('data-fee') || 0);
    
    if (stopSelect.selectedIndex > 0) {
        const stopOption = stopSelect.options[stopSelect.selectedIndex];
        baseFee += parseFloat(stopOption.getAttribute('data-additional-fee') || 0);
    }
    
    feeInput.value = baseFee.toFixed(2);
}
</script>
@endsection
