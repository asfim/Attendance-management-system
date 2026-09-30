@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="fa-solid fa-calendar-check me-2"></i>Edit Routine Slot
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0 mt-2 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routines.index') }}" class="text-decoration-none">Routines</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Routine</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.routines.index') }}" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left me-2"></i>Back to Routines
        </a>
    </div>

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 text-white" role="alert" style="background-color: #ef4444 !important;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 text-white" role="alert" style="background-color: #ef4444 !important;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Please check the form below for errors.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.routines.update', $timetable->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Academic Session <span class="text-danger">*</span></label>
                        <select name="session_id" class="form-select" required>
                            @foreach ($sessions as $s)
                                <option value="{{ $s->id }}" {{ $timetable->session_id == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_date)->format('Y') }} - {{ \Carbon\Carbon::parse($s->end_date)->format('Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select" required onchange="filterSections()">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ $timetable->class_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Section (Optional)</label>
                        <select name="section_id" id="section_id" class="form-select">
                            <option value="">All Sections</option>
                            @foreach ($classes as $c)
                                @foreach ($c->sections as $sec)
                                    <option value="{{ $sec->id }}" class="section-option class-{{ $c->id }}" {{ $timetable->section_id == $sec->id ? 'selected' : '' }}>
                                        {{ $sec->name }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Shift (Optional)</label>
                        <select name="shift_id" class="form-select">
                            <option value="">Select Shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ $timetable->shift_id == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}" {{ $timetable->subject_id == $sub->id ? 'selected' : '' }}>
                                    {{ $sub->name }} ({{ $sub->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Teacher <span class="text-danger">*</span></label>
                        <select name="staff_profile_id" class="form-select" required>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}" {{ $timetable->staff_profile_id == $t->id ? 'selected' : '' }}>
                                    {{ $t->user->name }} ({{ $t->designation }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Classroom / Room (Optional)</label>
                        <select name="classroom_id" class="form-select">
                            <option value="">Select Room</option>
                            @foreach ($classrooms as $rm)
                                <option value="{{ $rm->id }}" {{ $timetable->classroom_id == $rm->id ? 'selected' : '' }}>
                                    Room {{ $rm->room_number }} (Cap: {{ $rm->capacity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Day of Week <span class="text-danger">*</span></label>
                        <select name="day_of_week" class="form-select" required>
                            @foreach ($daysOfWeek as $d)
                                <option value="{{ $d }}" {{ $timetable->day_of_week == $d ? 'selected' : '' }}>{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" required value="{{ \Carbon\Carbon::parse($timetable->start_time)->format('H:i') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" required value="{{ \Carbon\Carbon::parse($timetable->end_time)->format('H:i') }}">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete this routine slot?')) { document.getElementById('deleteRoutineForm').submit(); }">
                        <i class="fa-solid fa-trash-can me-1"></i>Delete Routine
                    </button>
                    <div>
                        <a href="{{ route('admin.routines.index') }}" class="btn btn-secondary me-2">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Routine Slot</button>
                    </div>
                </div>
            </form>
            
            <form id="deleteRoutineForm" method="POST" action="{{ route('admin.routines.destroy', $timetable->id) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
function filterSections() {
    const classId = document.getElementById('class_id').value;
    const sectionSelect = document.getElementById('section_id');
    const options = sectionSelect.querySelectorAll('.section-option');

    // Hide all section options initially
    options.forEach(opt => {
        opt.style.display = 'none';
        opt.disabled = true;
    });

    // Show only the options belonging to the selected class
    if (classId) {
        const classOptions = sectionSelect.querySelectorAll('.class-' + classId);
        classOptions.forEach(opt => {
            opt.style.display = 'block';
            opt.disabled = false;
        });
    }

    // Check if currently selected section is still visible
    const selectedOption = sectionSelect.options[sectionSelect.selectedIndex];
    if (selectedOption && selectedOption.disabled && selectedOption.value !== '') {
        sectionSelect.value = ''; // Reset to "All Sections" if invalid
    }
}

document.addEventListener('DOMContentLoaded', function() {
    filterSections();
});
</script>
@endsection
