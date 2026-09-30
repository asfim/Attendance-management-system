@extends('layouts.app')

@section('title', 'Transport History')

@section('content')
<div class="container-fluid px-2">


    <div class="card shadow mb-4 border-0" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Student</th>
                            <th class="py-3">Date</th>
                            <th class="py-3 text-center">Action</th>
                            <th class="py-3">Details</th>
                            <th class="py-3">Reason</th>
                            <th class="px-4 py-3 text-end">Performed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $log)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $log->studentProfile ? $log->studentProfile->photoUrl() : 'https://ui-avatars.com/api/?name=Student&background=random&color=fff' }}" class="rounded-circle me-3" width="40" height="40" alt="Student Photo">
                                        <div>
                                            @if($log->studentProfile)
                                                <div class="fw-bold ">{{ $log->studentProfile->user->name ?? 'Deleted Student' }}</div>
                                                <div class="text-muted fs-7">Roll: {{ $log->studentProfile->roll_no }} | Class: {{ $log->studentProfile->schoolClass->name ?? 'N/A' }}</div>
                                            @else
                                                <div class="fw-bold text-muted">Deleted Student (ID: {{ $log->student_profile_id }})</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $log->action_date ? $log->action_date->format('d M, Y') : $log->created_at->format('d M, Y') }}
                                </td>
                                <td class="py-3 text-center">
                                    @php
                                        $badgeClass = match($log->action) {
                                            'Allocated' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                            'Changed' => 'bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25',
                                            'Cancelled' => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                            'Fee Updated' => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
                                            'Re-activated' => 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25',
                                            default => 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25'
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="fw-semibold ">
                                        @if($log->route)
                                            <i class="fa-solid fa-route text-muted me-1" style="font-size: 0.85rem;"></i> {{ $log->route->route_name }}
                                        @endif
                                        @if($log->stop)
                                            <span class="text-muted fs-7">({{ $log->stop->stop_name }})</span>
                                        @endif
                                    </div>
                                    <div class="fs-7 text-muted">
                                        @if($log->old_value)
                                            <span class="text-danger"><del>{{ $log->old_value }}</del></span> &rarr;
                                        @endif
                                        @if($log->new_value)
                                            <span class="text-success">{{ $log->new_value }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-muted text-wrap" style="max-width: 200px; font-size: 0.9rem;">
                                    {{ $log->reason ?? 'No reason provided' }}
                                </td>
                                <td class="px-4 py-3 text-end  fw-medium">
                                    <i class="fa-solid fa-user-tie text-secondary me-1" style="font-size: 0.85rem;"></i> {{ $log->user->name ?? 'System' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-clock-rotate-left fa-3x mb-3" style="opacity: 0.2"></i>
                                    <h5>No History Logs Found</h5>
                                    <p>History logs will appear here when student transport details are modified.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        {{ $history->links() }}
    </div>
</div>
@endsection
