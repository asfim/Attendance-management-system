@extends('layouts.app')

@section('title', 'Unmapped Biometric Attendance Logs')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Top Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-person-exclamation text-warning me-2"></i>Unmapped Biometric Punch Logs</h3>
            <p class="text-muted mb-0">Review punches from unrecognized device User IDs and map them to students or staff.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.biometric-devices.index') }}" class="btn btn-outline-primary rounded-pill px-3">
                <i class="bi bi-router-fill me-1"></i> Device Manager
            </a>
            <a href="{{ route('admin.attendance.biometric-logs') }}" class="btn btn-outline-secondary rounded-pill px-3">
                <i class="bi bi-journal-text me-1"></i> View All Logs
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Unmapped Table -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Log #</th>
                        <th>Device User ID</th>
                        <th>Punch Time</th>
                        <th>Device Name / IP</th>
                        <th>Status</th>
                        <th class="pe-4 text-end">Map to Profile Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($unmappedLogs as $log)
                    <tr>
                        <td class="ps-4 fw-bold">#{{ $log->id }}</td>
                        <td>
                            <code class="bg-warning bg-opacity-10 text-body fw-bold px-2 py-1 rounded fs-6">{{ $log->biometric_id }}</code>
                        </td>
                        <td class="small fw-semibold">{{ $log->punch_time ? $log->punch_time->format('d M Y, h:i:s A') : '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $log->device_sn ?? 'ZKTeco Device' }}</span>
                            <div class="text-muted small">{{ $log->ip_address }}</div>
                        </td>
                        <td>
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1"><i class="bi bi-question-circle me-1"></i>Unmapped</span>
                        </td>
                        <td class="pe-4 text-end">
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#mapModal{{ $log->id }}">
                                <i class="bi bi-person-plus me-1"></i> Map to User
                            </button>
                        </td>
                    </tr>

                    <!-- Map Modal -->
                    <div class="modal fade" id="mapModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow rounded-4 text-start">
                                <form action="{{ route('admin.attendance.map-user') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="biometric_id" value="{{ $log->biometric_id }}">
                                    <div class="modal-header bg-warning text-dark border-0 py-3">
                                        <h5 class="modal-title fw-bold"><i class="bi bi-link-45deg me-2"></i>Map Biometric ID: {{ $log->biometric_id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Select User Category</label>
                                            <select name="user_type" class="form-select user-type-selector" data-target="profile-select-{{ $log->id }}" required>
                                                <option value="student" selected>Student Profile</option>
                                                <option value="staff">Teacher / Staff Profile</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Select Profile</label>
                                            <select name="profile_id" id="profile-select-{{ $log->id }}" class="form-select" required>
                                                <optgroup label="Students" class="student-options">
                                                    @foreach($students as $st)
                                                        <option value="{{ $st->id }}">{{ $st->user->name ?? 'Student #' . $st->id }} (Roll: {{ $st->roll_no }}, Adm: {{ $st->admission_no }})</option>
                                                    @endforeach
                                                </optgroup>
                                                <optgroup label="Teacher & Staff" class="staff-options d-none">
                                                    @foreach($staff as $sf)
                                                        <option value="{{ $sf->id }}">{{ $sf->user->name ?? 'Staff #' . $sf->id }} (Phone: {{ $sf->phone }})</option>
                                                    @endforeach
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 bg-light py-3 rounded-bottom-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">Save Mapping</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                            <h5 class="fw-bold">No Unmapped Biometric Logs</h5>
                            <p class="mb-0">All ZKTeco punch records have been matched successfully to students and staff profiles.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-body py-3">
            {{ $unmappedLogs->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.user-type-selector').forEach(select => {
        select.addEventListener('change', function () {
            const targetId = this.getAttribute('data-target');
            const profileSelect = document.getElementById(targetId);
            if (!profileSelect) return;

            const studentOpt = profileSelect.querySelector('.student-options');
            const staffOpt = profileSelect.querySelector('.staff-options');

            if (this.value === 'student') {
                studentOpt.classList.remove('d-none');
                staffOpt.classList.add('d-none');
                studentOpt.querySelector('option').selected = true;
            } else {
                staffOpt.classList.remove('d-none');
                studentOpt.classList.add('d-none');
                staffOpt.querySelector('option').selected = true;
            }
        });
    });
});
</script>
@endsection
