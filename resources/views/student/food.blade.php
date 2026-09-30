@extends('layouts.app')

@section('title', 'My Food Plan')

@section('content')
<style>
.food-card {
    border-radius: 16px;
    border: 1px solid var(--bs-border-color);
}
.info-pill-card {
    background: rgba(34, 197, 94, 0.03);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 16px 20px;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
}
[data-bs-theme="dark"] .info-pill-card {
    background: rgba(255, 255, 255, 0.03);
}
.info-pill-card:hover {
    transform: translateY(-2px);
    border-color: rgba(34, 197, 94, 0.3);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
.icon-shape {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-utensils text-success me-2"></i>My Food Plan</h5>
                <p class="text-muted fs-7 mb-0">Your current food plan and meal details</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if($activeFoodPlan)
            {{-- Active Allocation Card --}}
            <div class="card food-card shadow-sm mb-4">
                <div class="card-body p-4">
                    {{-- Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-utensils"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Active Food Plan</h6>
                                <div class="text-muted fs-8">Registered Meal Facility</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-8">
                            <i class="fa-solid fa-circle-check me-1"></i>Active Subscription
                        </span>
                    </div>

                    {{-- Info Cards --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-pizza-slice"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Plan Name</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $activeFoodPlan->foodPlan->name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-info bg-opacity-10 text-info" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Billing Cycle</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $activeFoodPlan->foodPlan->billing_days ?? '30' }} Days
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Monthly Fee</div>
                                </div>
                                <div class="fw-bold fs-6 text-success ms-1">
                                    ৳{{ number_format($activeFoodPlan->monthly_fee ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Start Date</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $activeFoodPlan->start_date ? $activeFoodPlan->start_date->format('d M, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-utensils text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h6 class="text-muted fw-semibold">No Active Food Plan</h6>
                    <p class="text-muted fs-7 mb-0">You don't have an active food plan subscription. Please contact the administration.</p>
                </div>
            </div>
        @endif

        {{-- History --}}
        @if($allFoodPlans->count())
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Food Plan History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="fs-7 text-secondary">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Plan Name</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Monthly Fee</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                @foreach($allFoodPlans as $i => $plan)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-bold text-dark-emphasis">{{ $plan->foodPlan->name ?? 'N/A' }}</td>
                                        <td class="text-muted">
                                            {{ $plan->start_date ? $plan->start_date->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-muted">
                                            {{ $plan->end_date ? $plan->end_date->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td class="text-muted">
                                            ৳{{ number_format($plan->monthly_fee, 2) }}
                                        </td>
                                        <td>
                                            @if($plan->status === 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                                    Inactive
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        {{-- Attendance Calendar --}}
        <div class="card border-0 shadow-sm mt-4" style="border-radius: 15px;">
            <div class="card-header bg-transparent border-bottom py-3 px-4">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-calendar-days text-secondary me-2"></i>Food Attendance Calendar</h6>
            </div>
            <div class="card-body p-4">
                <div id="food-calendar"></div>
            </div>
        </div>

    </div>
</div>

@php
    $calendarEvents = [];
    if(isset($foodAttendances)) {
        foreach ($foodAttendances as $attendance) {
            $color = '#6c757d'; // secondary (default)
            if ($attendance->status === 'taken') $color = '#198754'; // success
            elseif ($attendance->status === 'not_taken') $color = '#dc3545'; // danger
            elseif ($attendance->status === 'leave') $color = '#ffc107'; // warning
            elseif ($attendance->status === 'holiday') $color = '#0dcaf0'; // info
            
            $calendarEvents[] = [
                'title' => ($attendance->meal->name ?? 'Meal') . ' (' . ucfirst(str_replace('_', ' ', $attendance->status)) . ')',
                'start' => $attendance->attendance_date->format('Y-m-d'),
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'meal' => $attendance->meal->name ?? 'N/A',
                    'time' => $attendance->meal->time ?? '',
                    'status' => ucfirst(str_replace('_', ' ', $attendance->status)),
                    'remarks' => $attendance->remarks ?? 'None',
                ]
            ];
        }
    }
@endphp

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('food-calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listWeek'
            },
            height: 'auto',
            events: {!! json_encode($calendarEvents) !!},
            eventContent: function(arg) {
                let status = arg.event.extendedProps.status;
                let icon = '';
                if(status === 'Taken') icon = '<i class="fa-solid fa-check text-white me-1"></i>';
                else if(status === 'Not taken') icon = '<i class="fa-solid fa-xmark text-white me-1"></i>';
                else if(status === 'Leave') icon = '<i class="fa-solid fa-person-walking-arrow-right text-white me-1"></i>';
                else if(status === 'Holiday') icon = '<i class="fa-solid fa-umbrella-beach text-white me-1"></i>';
                
                return {
                    html: `
                        <div class="p-1 text-white text-truncate" style="font-size: 0.75rem;" title="${arg.event.title}">
                            ${icon} ${arg.event.title}
                        </div>
                    `
                };
            },
            eventDidMount: function(info) {
                // Initialize a simple tooltip using standard HTML title attribute 
                // (or bootstrap tooltip if you prefer, but title is safer without extra deps)
                let tooltipContent = `Meal: ${info.event.extendedProps.meal}\nTime: ${info.event.extendedProps.time}\nStatus: ${info.event.extendedProps.status}\nRemarks: ${info.event.extendedProps.remarks}`;
                info.el.setAttribute('title', tooltipContent);
            }
        });
        calendar.render();
    });
</script>
@endsection
