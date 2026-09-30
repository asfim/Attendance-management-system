@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-id-card text-primary me-2"></i>Employee Profile</h3>
            <p class="text-muted small mb-0">{{ $employee->user?->name }} ({{ $employee->employeeId() }})</p>
        </div>
        <a href="{{ route('admin.attendance-suite.employees.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Employees
        </a>
    </div>

    <div class="row g-4">
        <!-- Sidebar Summary -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 text-center p-4">
                <img src="{{ $employee->photoUrl() }}" class="rounded-circle mx-auto mb-3 shadow" width="100" height="100" style="object-fit: cover;">
                <h4 class="fw-bold text-dark mb-1">{{ $employee->user?->name }}</h4>
                <p class="badge bg-primary rounded-pill px-3 py-1 mb-3">{{ $employee->designationTitle }}</p>

                <div class="border-top pt-3 text-start small">
                    <div class="mb-2"><strong>Employee ID:</strong> <span class="text-primary fw-bold">{{ $employee->employeeId() }}</span></div>
                    <div class="mb-2"><strong>Email:</strong> {{ $employee->user?->email }}</div>
                    <div class="mb-2"><strong>Phone:</strong> {{ $employee->phone }}</div>
                    <div class="mb-2"><strong>Branch:</strong> {{ $employee->branchName }}</div>
                    <div class="mb-2"><strong>Department:</strong> {{ $employee->departmentName }}</div>
                    <div class="mb-2"><strong>Joining Date:</strong> {{ $employee->joining_date?->format('d M, Y') ?? '--' }}</div>
                    <div class="mb-2"><strong>Status:</strong> <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($employee->status) }}</span></div>
                </div>

                <hr>

                <h6 class="fw-bold text-start text-dark mb-2"><i class="fa-solid fa-fingerprint me-1 text-primary"></i> Biometric Specs</h6>
                <div class="bg-light p-3 rounded-3 text-start small">
                    <div>Machine ID: <strong>{{ $employee->biometric_id ?? 'N/A' }}</strong></div>
                    <div>Fingerprint Ref: <strong>{{ $employee->fingerprint_id ?? 'N/A' }}</strong></div>
                    <div>Face ID Ref: <strong>{{ $employee->face_id ?? 'N/A' }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Main Details & Attendance Log -->
        <div class="col-12 col-lg-8">
            <!-- Leave Balance Overview -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-umbrella-beach text-success me-2"></i>Leave Quota & Balance ({{ date('Y') }})</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row g-3 text-center">
                        <div class="col-3">
                            <div class="bg-light p-3 rounded-3">
                                <span class="text-muted d-block small">Casual Leave</span>
                                <h4 class="fw-bold text-primary mb-0">{{ $employee->currentLeaveBalance?->casual_remaining ?? 10 }} / 10</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-3 rounded-3">
                                <span class="text-muted d-block small">Sick Leave</span>
                                <h4 class="fw-bold text-warning mb-0">{{ $employee->currentLeaveBalance?->sick_remaining ?? 14 }} / 14</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-3 rounded-3">
                                <span class="text-muted d-block small">Annual Leave</span>
                                <h4 class="fw-bold text-success mb-0">{{ $employee->currentLeaveBalance?->annual_remaining ?? 15 }} / 15</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="bg-light p-3 rounded-3">
                                <span class="text-muted d-block small">Emergency</span>
                                <h4 class="fw-bold text-info mb-0">{{ $employee->currentLeaveBalance?->emergency_remaining ?? 5 }} / 5</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Attendance Log -->
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-history text-primary me-2"></i>Recent Attendance Records</h5>
                </div>
                <div class="table-responsive px-4 pb-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Status</th>
                                <th>Overtime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($employee->attendances as $att)
                                <tr>
                                    <td>{{ $att->attendance_date?->format('d M, Y (D)') }}</td>
                                    <td>{{ $att->entry_time ? date('h:i A', strtotime($att->entry_time)) : '--' }}</td>
                                    <td>{{ $att->exit_time ? date('h:i A', strtotime($att->exit_time)) : '--' }}</td>
                                    <td><span class="badge {{ $att->badgeClass() }}">{{ strtoupper($att->status) }}</span></td>
                                    <td>{{ $att->overtime_minutes > 0 ? round($att->overtime_minutes / 60, 1) . ' hrs' : '--' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No attendance logs found for this employee yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
