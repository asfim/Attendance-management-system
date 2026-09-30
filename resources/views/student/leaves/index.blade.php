@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-umbrella-beach text-primary me-2"></i>My Leaves</h4>
    <a href="{{ route('student.leaves.create') }}" class="btn btn-primary rounded-pill px-4">
        <i class="fa-solid fa-plus me-2"></i>Apply for Leave
    </a>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Leave Type</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td class="fw-medium">{{ $leave->leaveType->name ?? 'N/A' }}</td>
                        <td>
                            {{ $leave->start_date->format('d M') }} - {{ $leave->end_date->format('d M, Y') }}
                        </td>
                        <td>{{ Str::limit($leave->reason, 50) }}</td>
                        <td>
                            @if($leave->status === 'approved')
                                <span class="badge bg-success">Approved</span>
                            @elseif($leave->status === 'rejected')
                                <span class="badge bg-danger">Rejected</span>
                            @else
                                <span class="badge bg-warning text-dark">Pending</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No leave applications found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3 border-top">
            {{ $leaves->links() }}
        </div>
    </div>
</div>
@endsection
