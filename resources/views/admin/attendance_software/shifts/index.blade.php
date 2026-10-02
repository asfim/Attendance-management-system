@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-clock text-primary me-2"></i>Shift & Timing Management</h3>
            <p class="text-muted small mb-0">General, Morning, Evening, Night, Flexible Shifts with Grace Time, Late Thresholds & Overtime rules</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                <i class="fa-solid fa-plus me-1"></i> Add Shift
            </button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#assignShiftModal">
                <i class="fa-solid fa-user-gear me-1"></i> <span class="d-none d-md-inline">Assign Shift to Staff</span><span class="d-md-none">Assign</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Shift Cards Grid -->
    <div class="row g-4">
        @forelse($shifts as $shift)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold text-uppercase">{{ $shift->shift_type ?? 'General' }} Shift</span>
                        <span class="badge bg-success rounded-pill">Active</span>
                    </div>

                    <h4 class="fw-bold text-dark mb-1">{{ $shift->name }}</h4>
                    <p class="text-muted small mb-3">{{ $shift->description ?? 'Standard office shift schedule' }}</p>

                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Shift Schedule:</span>
                            <span class="fw-bold text-dark fs-6"><i class="fa-regular fa-clock me-1 text-primary"></i>{{ date('h:i A', strtotime($shift->start_time)) }} - {{ date('h:i A', strtotime($shift->end_time)) }}</span>
                        </div>
                        <div class="row text-center border-top pt-2 g-2 small">
                            <div class="col-4 border-end">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Grace Time</span>
                                <strong class="text-success">{{ $shift->grace_time_minutes ?? 15 }} mins</strong>
                            </div>
                            <div class="col-4 border-end">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Late After</span>
                                <strong class="text-warning">{{ $shift->late_mark_after_minutes ?? 30 }} mins</strong>
                            </div>
                            <div class="col-4">
                                <span class="text-muted d-block" style="font-size: 0.75rem;">Overtime After</span>
                                <strong class="text-info">{{ $shift->overtime_start_after_minutes ?? 30 }} mins</strong>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto">
                        <span class="small text-muted"><i class="fa-solid fa-users me-1"></i>{{ $shift->staff_count }} Staff Assigned</span>
                        <form action="{{ route('admin.attendance-suite.shifts.destroy', $shift->id) }}" method="POST" onsubmit="return confirm('Delete this shift?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No shifts configured yet.</div>
        @endforelse
    </div>
</div>

<!-- Modal 1: Add Shift -->
<div class="modal fade" id="addShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.attendance-suite.shifts.store') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-clock text-primary me-2"></i>Create New Shift</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shift Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Morning Shift A" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Shift Type <span class="text-danger">*</span></label>
                        <select name="shift_type" class="form-select" required>
                            <option value="general">General Shift</option>
                            <option value="morning">Morning Shift</option>
                            <option value="evening">Evening Shift</option>
                            <option value="night">Night Shift</option>
                            <option value="flexible">Flexible Shift</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                        <input type="time" name="start_time" class="form-control" value="09:00" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                        <input type="time" name="end_time" class="form-control" value="17:00" required>
                    </div>

                    <h6 class="fw-bold text-primary mt-3 mb-0"><i class="fa-solid fa-sliders me-1"></i>Shift Rules & Thresholds</h6>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Grace Time (Minutes)</label>
                        <input type="number" name="grace_time_minutes" class="form-control" value="15">
                        <span class="text-muted small">No late penalty if inside grace</span>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Late Mark After (Minutes)</label>
                        <input type="number" name="late_mark_after_minutes" class="form-control" value="30">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Early Leave Before (Minutes)</label>
                        <input type="number" name="early_leave_before_minutes" class="form-control" value="15">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Overtime Calculation Start After (Minutes)</label>
                        <input type="number" name="overtime_start_after_minutes" class="form-control" value="30">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Half Day Hours Threshold</label>
                        <input type="number" step="0.5" name="half_day_hours" class="form-control" value="4.0">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief details about shift policy"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Shift Configuration</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Assign Shift -->
<div class="modal fade" id="assignShiftModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.shifts.assign') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-gear text-primary me-2"></i>Assign Shift to Employees</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Shift <span class="text-danger">*</span></label>
                    <select name="shift_id" class="form-select" required>
                        <option value="">-- Choose Shift --</option>
                        @foreach($shifts as $sh)
                            <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->start_time }} - {{ $sh->end_time }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Staff Members <span class="text-danger">*</span></label>
                    <select name="staff_profile_ids[]" class="form-select" multiple style="height: 180px;" required>
                        @foreach($staffMembers as $s)
                            <option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->employeeId() }})</option>
                        @endforeach
                    </select>
                    <span class="text-muted small">Hold Ctrl or Cmd to select multiple staff</span>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Assign Shift</button>
            </div>
        </form>
    </div>
</div>
@endsection
