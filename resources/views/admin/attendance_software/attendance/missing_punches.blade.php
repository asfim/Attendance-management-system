@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Missing Punch Records</h3>
            <p class="text-muted small mb-0">Staff who checked in but forgot to check out or have incomplete biometric punches</p>
        </div>
        <a href="{{ route('admin.attendance-suite.attendance.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th class="table-hide-xs">Department</th>
                        <th>Check-In Time</th>
                        <th class="table-hide-xs">Check-Out Time</th>
                        <th>Status</th>
                        <th class="text-end">Resolve Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($missingPunches as $item)
                        <tr>
                            <td>{{ $item->attendance_date?->format('d M, Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->attendable?->user?->name }}</div>
                                <span class="text-muted small">{{ $item->attendable?->employeeId() }}</span>
                            </td>
                            <td>{{ $item->attendable?->departmentName }}</td>
                            <td class="fw-bold text-success">{{ $item->entry_time ? date('h:i A', strtotime($item->entry_time)) : 'MISSING' }}</td>
                            <td class="fw-bold text-danger">{{ $item->exit_time ? date('h:i A', strtotime($item->exit_time)) : 'MISSING EXIT PUNCH' }}</td>
                            <td><span class="badge bg-danger">Missing Punch</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-primary rounded-pill" onclick="resolveMissingModal({{ $item->id }}, '{{ addslashes($item->attendable?->user?->name) }}', '{{ $item->entry_time }}')">
                                    <i class="fa-solid fa-wrench me-1"></i> Fix Punch
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5"><i class="fa-solid fa-circle-check text-success fa-2x d-block mb-2"></i> No missing punch issues currently! All punches are complete.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($missingPunches->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $missingPunches->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Resolve Missing Punch -->
<div class="modal fade" id="resolveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" id="resolveForm" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-wrench text-primary me-2"></i>Resolve Missing Punch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Employee</label>
                    <input type="text" id="res_staff_name" class="form-control bg-light" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Entry Time</label>
                        <input type="time" name="entry_time" id="res_entry" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Exit Time <span class="text-danger">*</span></label>
                        <input type="time" name="exit_time" value="17:00" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="early_leave">Early Leave</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Correction Reason</label>
                    <textarea name="correction_reason" class="form-control" rows="2" placeholder="e.g. Employee forgot to punch out" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Update & Complete Punch</button>
            </div>
        </form>
    </div>
</div>

<script>
function resolveMissingModal(id, name, entryTime) {
    document.getElementById('res_staff_name').value = name;
    document.getElementById('res_entry').value = entryTime || '09:00';
    document.getElementById('resolveForm').action = "{{ url('admin/attendance-suite/attendance/approve-correction') }}/" + id;

    const modal = new bootstrap.Modal(document.getElementById('resolveModal'));
    modal.show();
}
</script>
@endsection
