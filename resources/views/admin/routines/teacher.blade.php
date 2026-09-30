@extends('layouts.app')

@section('content')
<style>
    .timetable-board {
        display: flex;
        overflow-x: auto;
        gap: 1rem;
        padding-bottom: 1rem;
        min-height: 300px;
    }
    .timetable-column {
        flex: 0 0 250px;
        min-width: 250px;
    }
    .timetable-column-header {
        font-weight: 600;
        font-size: 0.95rem;
        margin-bottom: 0.75rem;
        color: var(--bs-heading-color);
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .timetable-card {
        border: none;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        background-color: var(--bs-primary);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        color: #fff;
    }
    .timetable-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .timetable-card-text {
        font-size: 0.85rem;
        color: #fff;
        margin-bottom: 0.5rem;
        line-height: 1.4;
    }
    .timetable-card-icon {
        color: rgba(255, 255, 255, 0.8);
        width: 16px;
        margin-right: 4px;
        text-align: center;
    }
    .not-scheduled {
        color: var(--bs-danger);
        font-size: 0.85rem;
        font-weight: 500;
    }
    .not-scheduled-card {
        border: 1px dashed var(--bs-border-color);
        background-color: transparent;
        box-shadow: none;
    }
    .not-scheduled-card:hover {
        transform: none;
        box-shadow: none;
    }
    
    /* Scrollbar styling */
    .timetable-board::-webkit-scrollbar {
        height: 10px;
    }
    .timetable-board::-webkit-scrollbar-track {
        background: #f1f1f1; 
        border-radius: 10px;
    }
    .timetable-board::-webkit-scrollbar-thumb {
        background: #c1c1c1; 
        border-radius: 10px;
    }
    .timetable-board::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8; 
    }
    
    [data-bs-theme="dark"] .timetable-board::-webkit-scrollbar-track {
        background: #2d3748;
    }
    [data-bs-theme="dark"] .timetable-board::-webkit-scrollbar-thumb {
        background: #4a5568;
    }
</style>

<div class="container-fluid py-4">
    <div class="card glass-card mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold mb-4 text-primary">Teacher Time Table</h5>
            
            <form action="{{ route('admin.routines.teacher') }}" method="GET" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold fs-7 text-secondary">Teachers <span class="text-danger">*</span></label>
                    <select name="staff_profile_id" class="form-select form-select-sm" required>
                        <option value="">Select Teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ $selectedTeacherId == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }} ({{ $teacher->user->employee_id ?? $teacher->id }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold fs-7 text-secondary">Day (Optional)</label>
                    <select name="day_of_week" class="form-select form-select-sm">
                        <option value="">All Days</option>
                        @foreach ($allDaysOfWeek as $day)
                            <option value="{{ $day }}" {{ $selectedDay == $day ? 'selected' : '' }}>
                                {{ $day }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100" style="background-color: #6366f1; border-color: #6366f1;">
                        Search
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    @if($selectedTeacherId)
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.print()" style="color: #6366f1; border-color: #6366f1;">
                        <i class="fa-solid fa-print"></i>
                    </button>
                    @endif
                </div>
            </form>

            @if($selectedTeacherId)
            <div class="timetable-board">
                @foreach($daysOfWeek as $day)
                    <div class="timetable-column">
                        <div class="timetable-column-header">
                            {{ $day }}
                        </div>
                        
                        @if(isset($groupedRoutines[$day]) && $groupedRoutines[$day]->isNotEmpty())
                            @foreach($groupedRoutines[$day] as $routine)
                                <div class="timetable-card">
                                    <div class="timetable-card-text">
                                        <i class="fa-solid fa-book timetable-card-icon"></i>
                                        Class: {{ $routine->schoolClass->name ?? 'N/A' }} 
                                        @if($routine->section_id)
                                            ({{ $routine->section->name }})
                                        @else
                                            (All Sections)
                                        @endif
                                        <br>
                                        Subject: {{ $routine->subject->name ?? 'N/A' }} 
                                        @if($routine->subject && $routine->subject->code)
                                            ({{ $routine->subject->code }})
                                        @endif
                                    </div>
                                    <div class="timetable-card-text">
                                        <i class="fa-regular fa-clock timetable-card-icon"></i>
                                        {{ \Carbon\Carbon::parse($routine->start_time)->format('h:i A') }} - 
                                        {{ \Carbon\Carbon::parse($routine->end_time)->format('h:i A') }}
                                    </div>
                                    <div class="timetable-card-text mb-0">
                                        <i class="fa-solid fa-building timetable-card-icon"></i>
                                        Room No.: {{ $routine->classroom->room_no ?? 'N/A' }}
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="timetable-card not-scheduled-card d-flex align-items-center justify-content-center py-3">
                                <div class="not-scheduled">
                                    <i class="fa-solid fa-circle-xmark me-1"></i> Not Scheduled
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-person-chalkboard fs-1 mb-3 opacity-50"></i>
                    <p>Select a teacher to view their time table</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .timetable-board, .timetable-board * {
            visibility: visible;
        }
        .timetable-board {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            display: block; /* Disable flex for printing to allow normal flow */
        }
        .timetable-column {
            page-break-inside: avoid;
            margin-bottom: 20px;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>
@endsection
