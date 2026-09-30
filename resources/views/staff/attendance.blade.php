@extends('layouts.app')

@section('title', 'My Attendance')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-user-clock text-primary me-2"></i> My Attendance
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">View your daily attendance records.</p>
        </div>
    </div>

    <div class="card glass-card border border-secondary border-opacity-25 rounded-3 bg-transparent p-4 mb-4">
        <h6 class="fw-bold text-light mb-3">Recent Attendance (Last 30 Days)</h6>
        @if($attendances->isEmpty())
            <div class="text-center p-5">
                <i class="fa-regular fa-calendar-xmark text-muted fs-1 mb-3"></i>
                <p class="text-muted">No attendance records found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle mb-0">
                    <thead class="border-bottom border-secondary border-opacity-50">
                        <tr>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-start" style="width: 25%">Date</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-start" style="width: 25%">Status</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-start">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances->take(30) as $att)
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="py-3 text-light fs-7">
                                    {{ \Carbon\Carbon::parse($att->attendance_date)->format('D, M d, Y') }}
                                </td>
                                <td class="py-3">
                                    @if($att->status === 'present')
                                        <span class="badge bg-success text-success bg-opacity-10 border border-current fs-8 fw-bold">Present</span>
                                    @elseif($att->status === 'absent')
                                        <span class="badge bg-danger text-danger bg-opacity-10 border border-current fs-8 fw-bold">Absent</span>
                                    @elseif($att->status === 'late')
                                        <span class="badge bg-warning text-warning bg-opacity-10 border border-current fs-8 fw-bold">Late</span>
                                    @elseif($att->status === 'half_day')
                                        <span class="badge bg-info text-info bg-opacity-10 border border-current fs-8 fw-bold">Half Day</span>
                                    @elseif($att->status === 'leave')
                                        <span class="badge bg-primary text-primary bg-opacity-10 border border-current fs-8 fw-bold">Leave</span>
                                    @else
                                        <span class="badge bg-secondary text-secondary bg-opacity-10 border border-current fs-8 fw-bold">{{ ucfirst($att->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 text-muted fs-7">
                                    {{ $att->remarks ?: '--' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
