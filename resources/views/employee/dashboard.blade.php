@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header / Welcome Banner -->
    <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 mb-4" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-normal"><i class="fa-solid fa-user me-1"></i>Employee Portal</span>
                <h2 class="fw-bold mb-1">Welcome back, {{ $staff->user?->name }}! 👋</h2>
                <p class="mb-0 text-white-50">{{ $staff->designationTitle }} | {{ $staff->departmentName }} ({{ $staff->branchName }})</p>
            </div>
            <div class="text-end bg-white bg-opacity-10 p-3 rounded-4 backdrop-blur">
                <div class="small text-white-50 text-uppercase fw-semibold">Employee ID</div>
                <div class="fs-4 fw-bold text-white">{{ $staff->employeeId() }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- 1. Today's Punch Card & Quick Actions -->
    <div class="row g-4 mb-4">
        <!-- Live Check-in / Check-out Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-fingerprint text-primary me-2"></i>Today's Attendance Punch</h5>
                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill">{{ date('d M, Y') }}</span>
                </div>

                <div class="row g-3 text-center my-2">
                    <div class="col-6">
                        <div class="bg-light p-3 rounded-4">
                            <span class="text-muted d-block small mb-1"><i class="fa-solid fa-right-to-bracket text-success me-1"></i>Check-In Time</span>
                            <h4 class="fw-bold text-dark mb-0">{{ $todayAttendance?->entry_time ? date('h:i A', strtotime($todayAttendance->entry_time)) : '--:--' }}</h4>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-3 rounded-4">
                            <span class="text-muted d-block small mb-1"><i class="fa-solid fa-right-from-bracket text-danger me-1"></i>Check-Out Time</span>
                            <h4 class="fw-bold text-dark mb-0">{{ $todayAttendance?->exit_time ? date('h:i A', strtotime($todayAttendance->exit_time)) : '--:--' }}</h4>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2">
                    <div>
                        <span class="text-muted small">Today's Status: </span>
                        @if($todayAttendance)
                            <span class="badge {{ $todayAttendance->badgeClass() }} px-3 py-1">{{ strtoupper($todayAttendance->status) }}</span>
                        @else
                            <span class="badge bg-secondary px-3 py-1">NOT MARKED</span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if(!$todayAttendance || !$todayAttendance->entry_time)
                            <form action="{{ route('employee.check-in') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i> Check In Now
                                </button>
                            </form>
                        @elseif(!$todayAttendance->exit_time)
                            <form action="{{ route('employee.check-out') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-right-from-bracket me-1"></i> Check Out Now
                                </button>
                            </form>
                        @else
                            <button class="btn btn-secondary rounded-pill px-4" disabled>
                                <i class="fa-solid fa-circle-check me-1"></i> Punches Complete
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Counters Card -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-chart-simple text-success me-2"></i>My Monthly Attendance ({{ date('F Y') }})</h5>
                <div class="row g-3 text-center my-auto">
                    <div class="col-3">
                        <div class="p-3 rounded-4" style="background-color: #d1e7dd;">
                            <span class="text-success small fw-semibold d-block">Present</span>
                            <h3 class="fw-bold text-success mb-0">{{ $presentDays }}</h3>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-3 rounded-4" style="background-color: #fff3cd;">
                            <span class="text-warning small fw-semibold d-block" style="color: #856404 !important;">Late</span>
                            <h3 class="fw-bold mb-0" style="color: #856404;">{{ $lateDays }}</h3>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-3 rounded-4" style="background-color: #f8d7da;">
                            <span class="text-danger small fw-semibold d-block">Absent</span>
                            <h3 class="fw-bold text-danger mb-0">{{ $absentDays }}</h3>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="p-3 rounded-4" style="background-color: #cff4fc;">
                            <span class="text-info small fw-semibold d-block" style="color: #0c5460 !important;">Overtime</span>
                            <h3 class="fw-bold mb-0" style="color: #0c5460;">{{ $overtimeHours }}h</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Leave Balance & Applications Row -->
    <div class="row g-4 mb-4">
        <!-- Leave Balance & Application Button -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-umbrella-beach text-primary me-2"></i>Leave Balances ({{ date('Y') }})</h5>
                    <button class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#empApplyLeaveModal">
                        <i class="fa-solid fa-plus me-1"></i> Apply Leave
                    </button>
                </div>

                <div class="list-group list-group-flush border-0">
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-primary me-2" style="font-size: 0.6rem;"></i>Casual Leave</span>
                        <strong class="text-primary">{{ $leaveBalance->casual_remaining }} / {{ $leaveBalance->casual_leave_quota }} Days Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-warning me-2" style="font-size: 0.6rem;"></i>Sick Leave</span>
                        <strong class="text-warning">{{ $leaveBalance->sick_remaining }} / {{ $leaveBalance->sick_leave_quota }} Days Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-success me-2" style="font-size: 0.6rem;"></i>Annual Leave</span>
                        <strong class="text-success">{{ $leaveBalance->annual_remaining }} / {{ $leaveBalance->annual_leave_quota }} Days Remaining</strong>
                    </div>
                    <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center border-0 px-0 py-2">
                        <span><i class="fa-solid fa-circle text-info me-2" style="font-size: 0.6rem;"></i>Emergency Leave</span>
                        <strong class="text-info">{{ $leaveBalance->emergency_remaining }} / {{ $leaveBalance->emergency_leave_quota }} Days Remaining</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Leave Applications -->
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>My Recent Leave Applications</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Dates</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myLeaveApplications as $app)
                                <tr>
                                    <td><span class="badge rounded-pill" style="background-color: #cfe2ff; color: #084298;">{{ $app->leaveType?->name ?? 'Casual' }}</span></td>
                                    <td class="small">{{ $app->start_date?->format('d M') }} - {{ $app->end_date?->format('d M, Y') }}</td>
                                    <td class="small">{{ Str::limit($app->reason, 30) }}</td>
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
            </div>
        </div>
    </div>

    <!-- 3. Monthly Attendance History Log & Salary Overview -->
    <div class="row g-4">
        <!-- Monthly Attendance History -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-0 pt-3 px-4">
                    <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-calendar-days text-primary me-2"></i>My Attendance Records ({{ date('F Y') }})</h5>
                </div>
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Status</th>
                                <th>Late / Overtime</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyAttendances as $att)
                                <tr>
                                    <td>{{ $att->attendance_date?->format('d M, Y (D)') }}</td>
                                    <td>{{ $att->entry_time ? date('h:i A', strtotime($att->entry_time)) : '--' }}</td>
                                    <td>{{ $att->exit_time ? date('h:i A', strtotime($att->exit_time)) : '--' }}</td>
                                    <td><span class="badge {{ $att->badgeClass() }}">{{ strtoupper($att->status) }}</span></td>
                                    <td>
                                        @if($att->late_minutes > 0) <span class="badge bg-warning text-dark me-1">Late {{ $att->late_minutes }}m</span> @endif
                                        @if($att->overtime_minutes > 0) <span class="badge bg-success">OT +{{ round($att->overtime_minutes/60, 1) }}h</span> @endif
                                        @if(!$att->late_minutes && !$att->overtime_minutes) <span class="text-muted">--</span> @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No attendance records for this month yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Salary / Payroll Information Card -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="fa-solid fa-wallet text-success me-2"></i>Salary & Payroll Info</h5>
                <div class="bg-light p-3 rounded-4 mb-3">
                    <div class="d-flex justify-content-between mb-2"><span>Basic Salary:</span><strong>৳ {{ number_format($basicSalary, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2 text-success"><span>Overtime Earnings:</span><strong>+ ৳ {{ number_format($overtimePay, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2 text-danger"><span>Late Deduction:</span><strong>- ৳ {{ number_format($lateDeduction, 2) }}</strong></div>
                    <div class="d-flex justify-content-between mb-2 text-danger"><span>Absent Deduction:</span><strong>- ৳ {{ number_format($absentDeduction, 2) }}</strong></div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold text-success fs-5"><span>Estimated Net Pay:</span><strong>৳ {{ number_format($netSalary, 2) }}</strong></div>
                </div>

                <a href="{{ route('admin.attendance-suite.payroll.slip', [$staff->id, 'month' => $month, 'year' => $year]) }}" class="btn btn-outline-primary rounded-pill w-100" target="_blank">
                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> View Official Payslip
                </a>
            </div>

            <!-- Upcoming Holidays -->
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-calendar-day text-info me-2"></i>Upcoming Holidays</h5>
                @if($upcomingHolidays->count() > 0)
                    <ul class="list-group list-group-flush mb-0">
                        @foreach($upcomingHolidays as $holiday)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="fw-bold text-dark d-block">{{ $holiday->name }}</span>
                                    <span class="text-muted small">{{ $holiday->date->format('l, d F Y') }}</span>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $holiday->date->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted py-3">No upcoming holidays scheduled.</div>
                @endif
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
