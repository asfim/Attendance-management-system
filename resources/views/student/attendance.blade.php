@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 text-primary"><i class="fa-solid fa-calendar-check me-2"></i>My Attendance</h4>
            <p class="text-muted fs-7 mb-0">View your daily attendance records.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Status</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($attendances as $att)
                            <tr>
                                <td class="ps-4 text-nowrap">
                                    <i class="fa-regular fa-calendar me-2 text-muted"></i>
                                    {{ $att->attendance_date->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $att->attendance_date->format('l') }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ $att->badgeClass() }} border px-2 py-1">
                                        {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($att->remarks || $att->late_reason || $att->leave_reason)
                                        <div class="fs-8">
                                            @if($att->remarks)<div class="mb-1"><strong>Remarks:</strong> {{ $att->remarks }}</div>@endif
                                            @if($att->late_reason)<div class="mb-1 text-warning"><strong>Late:</strong> {{ $att->late_reason }}</div>@endif
                                            @if($att->leave_reason)<div class="text-info"><strong>Leave:</strong> {{ $att->leave_reason }}</div>@endif
                                        </div>
                                    @else
                                        <span class="text-muted fs-8">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fa-solid fa-calendar-xmark fs-2 mb-3"></i>
                                        <p class="mb-0">No attendance records found.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($attendances->hasPages())
                <div class="card-footer bg-white border-top border-light p-3">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
