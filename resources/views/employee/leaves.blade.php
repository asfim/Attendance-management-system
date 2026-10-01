@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-umbrella-beach text-teal me-2"></i>My Leaves</h3>
            <p class="text-muted small mb-0">Manage your leaves and view balances</p>
        </div>
        <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#empApplyLeaveModal">
            <i class="fa-solid fa-plus me-1"></i> Apply Leave
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Leave Balances -->
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h5 class="fw-bold mb-4">Balances ({{ date('Y') }})</h5>
                <div class="list-group list-group-flush border-0">
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-primary me-2" style="font-size: 0.6rem;"></i>Casual Leave</span>
                        <strong class="text-primary">{{ $leaveBalance->casual_remaining }} / {{ $leaveBalance->casual_leave_quota }} Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.6rem;"></i>Sick Leave</span>
                        <strong class="text-warning">{{ $leaveBalance->sick_remaining }} / {{ $leaveBalance->sick_leave_quota }} Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-success me-2" style="font-size: 0.6rem;"></i>Annual Leave</span>
                        <strong class="text-success">{{ $leaveBalance->annual_remaining }} / {{ $leaveBalance->annual_leave_quota }} Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-info me-2" style="font-size: 0.6rem;"></i>Emergency</span>
                        <strong class="text-info">{{ $leaveBalance->emergency_remaining }} / {{ $leaveBalance->emergency_leave_quota }} Remaining</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="col-12 col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h5 class="fw-bold mb-4">Leave History</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="border-bottom">
                                <th class="text-body fw-bold py-3">Type</th>
                                <th class="text-body fw-bold py-3">Duration</th>
                                <th class="text-body fw-bold py-3">Reason</th>
                                <th class="text-body fw-bold py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-body">
                            @forelse($myLeaveApplications as $app)
                                <tr>
                                    <td><span class="badge rounded-pill px-3 py-1" style="background-color: #cfe2ff; color: #084298;">{{ $app->leaveType?->name ?? 'Casual' }}</span></td>
                                    <td class="small">{{ $app->start_date?->format('d M, Y') }} - {{ $app->end_date?->format('d M, Y') }}</td>
                                    <td class="small text-muted">{{ Str::limit($app->reason, 40) }}</td>
                                    <td>
                                        @if($app->status === 'approved')
                                            <span class="badge bg-success rounded-pill">Approved</span>
                                        @elseif($app->status === 'rejected')
                                            <span class="badge bg-danger rounded-pill">Rejected</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No leave applications submitted yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $myLeaveApplications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Employee Apply Leave -->
<div class="modal fade" id="empApplyLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('employee.submit-leave') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-umbrella-beach text-primary me-2"></i>Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                    <select name="leave_type_id" class="form-select" required>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="State reason for your leave request" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Application</button>
            </div>
        </form>
    </div>
</div>
@endsection
