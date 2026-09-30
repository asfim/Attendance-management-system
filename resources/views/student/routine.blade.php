@extends('layouts.app')

@section('title', 'Class Routine')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-calendar-days text-primary me-2"></i>Class Routine</h5>
                <p class="text-muted fs-7 mb-0">Weekly class timetable and schedule</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if($routine->count())
            @php
                $groupedDays = $routine->groupBy('day_of_week');
            @endphp
            <div class="row g-4">
                @foreach($groupedDays as $day => $slots)
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100" style="border-radius: 15px;">
                            <div class="card-header bg-white border-0 py-3 px-4" style="border-radius: 15px 15px 0 0;">
                                <h6 class="fw-bold m-0 text-primary">
                                    <i class="fa-solid fa-calendar-day me-2"></i>{{ ucfirst($day) }}
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    @foreach($slots as $slot)
                                        <div class="list-group-item p-3 border-light border-opacity-10">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <div class="fw-bold text-dark-emphasis fs-7">
                                                    <i class="fa-solid fa-book-bookmark text-primary me-1"></i>
                                                    {{ $slot->subject->name ?? 'Subject' }}
                                                </div>
                                                <span class="badge  text-secondary border fs-8">
                                                    <i class="fa-regular fa-clock me-1"></i>
                                                    {{ $slot->start_time }} - {{ $slot->end_time }}
                                                </span>
                                            </div>
                                            <div class="d-flex justify-content-between text-muted fs-8 mt-2">
                                                <span>
                                                    <i class="fa-solid fa-chalkboard-user me-1"></i>
                                                    {{ $slot->staffProfile->user->name ?? 'Teacher' }}
                                                </span>
                                                <span>
                                                    <i class="fa-solid fa-door-open me-1"></i>
                                                    {{ $slot->classroom->room_number ?? 'Room N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-calendar-xmark fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h6 class="text-muted">No class routine available.</h6>
                    <p class="text-muted fs-7 mb-0">Your class routine will appear here once configured by administration.</p>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
