@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-3 page-header-row">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-bell text-primary me-2"></i>Automated Notifications Hub</h3>
            <p class="text-muted small mb-0">SMS, Email, & WhatsApp Notifications for Late Arrivals, Absentees, Leave Approvals & Corrections</p>
        </div>
        <div>
            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#sendNotifModal">
                <i class="fa-solid fa-paper-plane me-1"></i> <span class="d-none d-sm-inline">Send Manual Notification</span><span class="d-sm-none">Send</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Notification Channels Overview -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary"><i class="fa-solid fa-comment-sms fa-xl"></i></div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">SMS Gateway</h6>
                        <span class="text-muted small">Status: <strong>Active (SSL / BulkSMS BD)</strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success"><i class="fa-brands fa-whatsapp fa-xl"></i></div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">WhatsApp Integration</h6>
                        <span class="text-muted small">Status: <strong>Active (WhatsApp Cloud API)</strong></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info"><i class="fa-solid fa-envelope fa-xl"></i></div>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Email Server</h6>
                        <span class="text-muted small">Status: <strong>Active (SMTP Mailer)</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notification Sent Logs Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-transparent border-0 pt-3 px-4">
            <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Notification Transmission Logs</h5>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date & Time</th>
                        <th>Employee</th>
                        <th>Notification Type</th>
                        <th>Channel</th>
                        <th>Recipient</th>
                        <th>Message Content</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $n)
                        <tr>
                            <td>{{ $n->sent_at?->format('d M, Y h:i A') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $n->staff?->user?->name }}</div>
                                <span class="text-muted small">{{ $n->staff?->employeeId() }}</span>
                            </td>
                            <td>
                                @php
                                    $typeBadge = match($n->type) {
                                        'late'                  => 'bg-warning text-dark',
                                        'absent'                => 'bg-danger',
                                        'leave_approval'        => 'bg-success',
                                        'attendance_correction' => 'bg-info text-dark',
                                        default                 => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $typeBadge }} text-uppercase px-3 py-1">{{ str_replace('_', ' ', $n->type) }}</span>
                            </td>
                            <td>
                                @if($n->channel === 'whatsapp')
                                    <span class="badge bg-success"><i class="fa-brands fa-whatsapp me-1"></i> WhatsApp</span>
                                @elseif($n->channel === 'email')
                                    <span class="badge bg-info text-dark"><i class="fa-solid fa-envelope me-1"></i> Email</span>
                                @else
                                    <span class="badge bg-primary"><i class="fa-solid fa-comment-sms me-1"></i> SMS</span>
                                @endif
                            </td>
                            <td class="small">{{ $n->recipient }}</td>
                            <td class="small text-muted">{{ Str::limit($n->message, 50) }}</td>
                            <td><span class="badge bg-success rounded-pill px-3 py-1">SENT</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No notification log entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($notifications->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Send Notification -->
<div class="modal fade" id="sendNotifModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.notifications.send') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-paper-plane text-primary me-2"></i>Trigger Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                    <select name="staff_profile_id" class="form-select" required>
                        <option value="">-- Choose Employee --</option>
                        @foreach($staffMembers as $s)
                            <option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->employeeId() }}) - {{ $s->phone }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Notification Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="late">Late Arrival Alert</option>
                            <option value="absent">Absent Notification</option>
                            <option value="leave_approval">Leave Approval</option>
                            <option value="attendance_correction">Attendance Correction</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Channel <span class="text-danger">*</span></label>
                        <select name="channel" class="form-select" required>
                            <option value="sms">SMS</option>
                            <option value="whatsapp">WhatsApp API</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Message Text <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="3" placeholder="Type notification message" required>Dear Employee, your attendance status for today has been recorded.</textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Send Notification</button>
            </div>
        </form>
    </div>
</div>
@endsection
