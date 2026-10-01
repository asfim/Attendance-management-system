@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="mb-4">
        <h3 class="fw-bold mb-1"><i class="fa-solid fa-calendar-day text-danger me-2"></i>Holiday Calendar</h3>
        <p class="text-muted small mb-0">View all upcoming and past holidays for the year</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="border-bottom">
                        <th class="text-body fw-bold py-3">Date</th>
                        <th class="text-body fw-bold py-3">Day of Week</th>
                        <th class="text-body fw-bold py-3">Holiday Name</th>
                        <th class="text-body fw-bold py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="text-body">
                    @forelse($holidays as $holiday)
                        @php
                            $isPast = $holiday->date->isPast();
                            $isToday = $holiday->date->isToday();
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $holiday->date->format('d M, Y') }}</td>
                            <td>{{ $holiday->date->format('l') }}</td>
                            <td>{{ $holiday->name }}</td>
                            <td>
                                @if($isToday)
                                    <span class="badge bg-success">Today</span>
                                @elseif($isPast)
                                    <span class="badge bg-secondary">Passed</span>
                                @else
                                    <span class="badge bg-info text-dark">Upcoming in {{ $holiday->date->diffForHumans() }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No holidays found for this year.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
