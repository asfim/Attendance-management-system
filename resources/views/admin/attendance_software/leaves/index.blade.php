@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-umbrella-beach text-primary me-2"></i>Leave Management Suite</h3>
            <p class="text-muted small mb-0">Leave Applications, Approval / Reject Workflow, Casual, Sick, Annual & Emergency Leave Quotas</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#applyLeaveModal">
                <i class="fa-solid fa-plus me-1"></i> Submit Leave Application
            </button>
            <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
                <i class="fa-solid fa-tags me-1"></i> Add Leave Type
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-4 gap-2" id="leaveTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill fw-semibold" id="apps-tab" data-bs-toggle="pill" data-bs-target="#apps" type="button"><i class="fa-solid fa-clipboard-check me-1"></i> Applications ({{ $applications->total() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill fw-semibold" id="balances-tab" data-bs-toggle="pill" data-bs-target="#balances" type="button"><i class="fa-solid fa-chart-pie me-1"></i> Staff Balances & Quotas</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill fw-semibold" id="types-tab" data-bs-toggle="pill" data-bs-target="#types" type="button"><i class="fa-solid fa-list me-1"></i> Leave Types</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="leaveTabContent">
        <!-- 1. Applications Tab -->
        <div class="tab-pane fade show active" id="apps">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Applicant</th>
                                <th>Leave Type</th>
                                <th>Duration</th>
                                <th>Total Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $app)
                                @php
                                    $days = \Carbon\Carbon::parse($app->start_date)->diffInDays(\Carbon\Carbon::parse($app->end_date)) + 1;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $app->applicant?->user?->name ?? 'Staff' }}</div>
                                        <span class="text-muted small">{{ $app->applicant?->employeeId() }}</span>
                                    </td>
                                    <td><span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">{{ $app->leaveType?->name ?? 'Casual' }}</span></td>
                                    <td>{{ $app->start_date?->format('d M, Y') }} - {{ $app->end_date?->format('d M, Y') }}</td>
                                    <td><span class="fw-bold text-dark">{{ $days }} Day(s)</span></td>
                                    <td class="small">{{ Str::limit($app->reason, 40) }}</td>
                                    <td>
                                        @if($app->status === 'approved')
                                            <span class="badge bg-success rounded-pill px-3 py-1">APPROVED</span>
                                        @elseif($app->status === 'rejected')
                                            <span class="badge bg-danger rounded-pill px-3 py-1">REJECTED</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">PENDING</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($app->status === 'pending')
                                            <button class="btn btn-sm btn-success rounded-pill me-1" onclick="openApprovalModal({{ $app->id }}, 'approved')">Approve</button>
                                            <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="openApprovalModal({{ $app->id }}, 'rejected')">Reject</button>
                                        @else
                                            <span class="text-muted small"><i class="fa-solid fa-lock me-1"></i>Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-5">No leave applications found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($applications->hasPages())
                    <div class="card-footer bg-transparent border-0 px-4 py-3">
                        {{ $applications->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- 2. Balances Tab -->
        <div class="tab-pane fade" id="balances">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Staff Member</th>
                                <th>Casual Leave</th>
                                <th>Sick Leave</th>
                                <th>Annual Leave</th>
                                <th>Emergency Leave</th>
                                <th>Total Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveBalances as $lb)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $lb->staff?->user?->name }} ({{ $lb->staff?->employeeId() }})</td>
                                    <td><span class="text-primary fw-semibold">{{ $lb->casual_remaining }}</span> / {{ $lb->casual_leave_quota }}</td>
                                    <td><span class="text-warning fw-semibold">{{ $lb->sick_remaining }}</span> / {{ $lb->sick_leave_quota }}</td>
                                    <td><span class="text-success fw-semibold">{{ $lb->annual_remaining }}</span> / {{ $lb->annual_leave_quota }}</td>
                                    <td><span class="text-info fw-semibold">{{ $lb->emergency_remaining }}</span> / {{ $lb->emergency_leave_quota }}</td>
                                    <td><span class="badge bg-success rounded-pill px-3 py-1">{{ $lb->total_remaining }} Days</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No staff leave balances calculated yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Leave Types Tab -->
        <div class="tab-pane fade" id="types">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Leave Type Name</th>
                                <th>Allowed Days / Year</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leaveTypes as $lt)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $lt->name }}</td>
                                    <td><span class="badge bg-primary px-3 py-1">{{ $lt->days }} Days</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No leave types configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Submit Leave Application -->
<div class="modal fade" id="applyLeaveModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.leaves.store-application') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-umbrella-beach text-primary me-2"></i>Apply for Leave</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Employee <span class="text-danger">*</span></label>
                    <select name="staff_profile_id" class="form-select" required>
                        <option value="">-- Choose Staff --</option>
                        @foreach($staffMembers as $s)
                            <option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->employeeId() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Leave Type <span class="text-danger">*</span></label>
                    <select name="leave_type_id" class="form-select" required>
                        <option value="">-- Select Type --</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}">{{ $lt->name }} (Max {{ $lt->days }} Days)</option>
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
                    <label class="form-label fw-semibold">Reason for Leave <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Provide reason" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Add Leave Type -->
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.leaves.store-type') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-tags text-primary me-2"></i>Add Leave Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Leave Type Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Annual Leave / Emergency Leave" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Max Days Allowed Per Year <span class="text-danger">*</span></label>
                    <input type="number" name="max_days" class="form-control" placeholder="e.g. 15" required>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Leave Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Approval / Reject -->
<div class="modal fade" id="approvalModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" id="approvalForm" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <input type="hidden" name="status" id="app_status_input">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="app_modal_title">Process Leave Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="app_modal_desc">Are you sure you want to process this application?</p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Admin Remarks / Notes</label>
                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional comments"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="app_submit_btn" class="btn btn-success rounded-pill px-4">Confirm Action</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApprovalModal(id, status) {
    document.getElementById('app_status_input').value = status;
    document.getElementById('approvalForm').action = "{{ url('admin/attendance-suite/leaves/update-status') }}/" + id;

    if (status === 'approved') {
        document.getElementById('app_modal_title').innerText = "Approve Leave Application";
        document.getElementById('app_modal_desc').innerText = "Approving this application will deduct days from staff quota & mark attendance as LEAVE.";
        document.getElementById('app_submit_btn').className = "btn btn-success rounded-pill px-4";
        document.getElementById('app_submit_btn').innerText = "Approve & Mark Leave";
    } else {
        document.getElementById('app_modal_title').innerText = "Reject Leave Application";
        document.getElementById('app_modal_desc').innerText = "Rejecting this leave application.";
        document.getElementById('app_submit_btn').className = "btn btn-danger rounded-pill px-4";
        document.getElementById('app_submit_btn').innerText = "Reject Application";
    }

    const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
    modal.show();
}
</script>
@endsection
