@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0 text-primary">
                <i class="fa-solid fa-calendar-plus me-2"></i>Add New Routine Slot
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 m-0 mt-2 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.routines.index') }}" class="text-decoration-none">Routines</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Routine</li>
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
            <form action="{{ route('admin.routines.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Academic Session <span class="text-danger">*</span></label>
                        <select name="session_id" class="form-select" required>
                            @foreach ($sessions as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ \Carbon\Carbon::parse($s->start_date)->format('Y') }} - {{ \Carbon\Carbon::parse($s->end_date)->format('Y') }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Section (Optional)</label>
                        <select name="section_id" id="section_id" class="form-select">
                            <option value="both">Both (All Sections)</option>
                            @foreach ($classes as $c)
                                @foreach ($c->sections as $sec)
                                    <option value="{{ $sec->id }}" data-class-id="{{ $c->id }}" class="section-option d-none">
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
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Subject <span class="text-danger">*</span></label>
                        <select name="subject_id" class="form-select" required>
                            @foreach ($subjects as $sub)
                                <option value="{{ $sub->id }}">{{ $sub->name }} ({{ $sub->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Teacher <span class="text-danger">*</span></label>
                        <select name="staff_profile_id" class="form-select" required>
                            @foreach ($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->user->name }} ({{ $t->designation }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold fs-7">Classroom / Room (Optional)</label>
                        <select name="classroom_id" class="form-select">
                            <option value="">Select Room</option>
                            @foreach ($classrooms as $rm)
                                <option value="{{ $rm->id }}">Room {{ $rm->room_number }} (Cap: {{ $rm->capacity }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Day of Week <span class="text-danger">*</span></label>
                        <select name="day_of_week" class="form-select" required>
                            @foreach ($daysOfWeek as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" required value="09:00">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold fs-7">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" required value="09:45">
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                    <a href="{{ route('admin.routines.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Routine Slot</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        const sectionOptions = sectionSelect.querySelectorAll('.section-option');

        function updateSections() {
            const classId = classSelect.value;
            
            // Hide all sections first
            sectionOptions.forEach(opt => {
                opt.classList.add('d-none');
                opt.disabled = true;
            });

            // Show sections for the selected class
            if (classId) {
                let hasSections = false;
                sectionOptions.forEach(opt => {
                    if (opt.getAttribute('data-class-id') === classId) {
                        opt.classList.remove('d-none');
                        opt.disabled = false;
                        hasSections = true;
                    }
                });
            }
            
            // Reset to "Both" option
            sectionSelect.value = 'both';
        }

        classSelect.addEventListener('change', updateSections);
        updateSections(); // Run on load
    });
</script>
@endpush
