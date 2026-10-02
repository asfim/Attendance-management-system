@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-wrench text-warning me-2"></i>Attendance Corrections & Adjustments</h3>
            <p class="text-muted small mb-0">Audit trail of corrected punch times, manual overrides, and adjustment requests</p>
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
                        <th>Status</th>
                        <th class="table-hide-xs">Correction Reason</th>
                        <th class="table-hide-xs">Corrected By</th>
                        <th class="table-hide-xs">Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($corrections as $item)
                        <tr>
                            <td>{{ $item->attendance_date?->format('d M, Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->attendable?->user?->name }}</div>
                                <span class="text-muted small">{{ $item->attendable?->employeeId() }}</span>
                            </td>
                            <td>{{ $item->attendable?->departmentName }}</td>
                            <td><span class="badge {{ $item->badgeClass() }}">{{ strtoupper($item->status) }}</span></td>
                            <td>{{ $item->correction_reason ?? 'Manual adjustment' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $item->corrector?->name ?? 'Admin' }}</span></td>
                            <td>{{ $item->updated_at->format('d M, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No attendance corrections recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($corrections->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $corrections->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
