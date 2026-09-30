@extends('layouts.app')

@section('content')
    <style>
        /* FullCalendar Premium Styles */
        #routine-calendar {
            background: var(--bs-body-bg);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--bs-border-color);
        }

        .fc-theme-standard th {
            border: none;
            padding: 15px 0;
            background: transparent !important;
            /* Fix background for dark mode */
            color: var(--bs-heading-color) !important;
            /* Fix text color for dark mode */
        }

        .fc-col-header-cell-cushion {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 1px;
            text-decoration: none !important;
            color: var(--bs-heading-color) !important;
            /* Force color */
        }

        .fc-theme-standard td,
        .fc-theme-standard th {
            border-color: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .fc-theme-standard td,
        [data-bs-theme="dark"] .fc-theme-standard th {
            border-color: rgba(255, 255, 255, 0.05);
        }

        /* Hide the vertical time axis text, but maintain table structure */
        .fc .fc-timegrid-axis-cushion,
        .fc .fc-timegrid-slot-label-cushion {
            display: none !important;
        }

        .fc .fc-timegrid-axis,
        .fc .fc-timegrid-slot-label {
            width: 0 !important;
            padding: 0 !important;
            border: none !important;
        }

        .fc-timegrid-slot {
            height: 5.5em !important;
            /* Taller slots to fit 4 lines of text */
        }

        .fc-timegrid-axis-cushion {
            font-size: 0.8rem;
            color: var(--bs-secondary-color);
        }

        /* Beautiful Event Blocks */
        .fc-timegrid-event {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            cursor: pointer;
        }

        .fc-timegrid-event:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
            z-index: 10 !important;
        }

        .fc-v-event .fc-event-main {
            padding: 6px;
            color: #fff !important;
        }

        .fc-v-event {
            color: #fff !important;
        }

        .calendar-scroll-container {
            overflow-x: auto;
            padding-bottom: 15px;
            scrollbar-width: thin;
        }

        .calendar-scroll-container::-webkit-scrollbar {
            height: 8px;
        }

        .calendar-scroll-container::-webkit-scrollbar-thumb {
            background-color: rgba(var(--bs-secondary-rgb), 0.3);
            border-radius: 10px;
        }
    </style>

    <div class="container-fluid px-0">
        <!-- Header -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h4 class="fw-bold m-0"><i class="fa-solid fa-calendar-days text-primary me-2"></i>Class Routine</h4>
                <p class="text-muted fs-7 mb-0">Create, manage and organize weekly class routines per class, subject and
                    teacher</p>
            </div>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_academic'))
<a href="{{ route('admin.routines.create') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Add Routine Slot
            </a>
@endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 text-white" role="alert"
                style="background-color: #ef4444 !important;">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Filter Card -->
        <div class="card glass-card mb-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.routines.index') }}" method="GET" class="row g-3 align-items-center">
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Select Class</label>
                        <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ $selectedClassId == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Filter Section</label>
                        <select name="section_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Sections</option>
                            @foreach ($sections as $sec)
                                <option value="{{ $sec->id }}" {{ $selectedSectionId == $sec->id ? 'selected' : '' }}>
                                    {{ $sec->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label fs-7 fw-semibold text-secondary mb-1">Filter Shift</label>
                        <select name="shift_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Shifts</option>
                            @foreach ($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ $selectedShiftId == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-12 d-flex align-items-end justify-content-md-end">
                        <a href="{{ route('admin.routines.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset Filter
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- FullCalendar Schedule View -->
        <div class="calendar-scroll-container">
            <div id="routine-calendar" style="min-width: 1000px;"></div>
        </div>
    </div>

    @php
        $dayMap = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];
        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
        $calendarEvents = [];
        foreach ($timetables as $index => $slot) {
            $calendarEvents[] = [
                'id' => $slot->id,
                'daysOfWeek' => [$dayMap[$slot->day_of_week]],
                'startTime' => $slot->start_time,
                'endTime' => $slot->end_time,
                'backgroundColor' => $colors[$index % count($colors)],
                'borderColor' => $colors[$index % count($colors)],
                'extendedProps' => [
                    'slot_data' => $slot,
                ],
            ];
        }
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('routine-calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                headerToolbar: false, // No need for next/prev since it's a weekly static routine
                dayHeaderFormat: {
                    weekday: 'long'
                },
                allDaySlot: false,
                slotMinTime: '08:00:00',
                slotMaxTime: '18:00:00',
                height: 'auto',
                events: {!! json_encode($calendarEvents) !!},
                eventClick: function(info) {
                    let slotId = info.event.extendedProps.slot_data.id;
                    window.location.href = `/admin/routines/${slotId}/edit`;
                },
                eventContent: function(arg) {
                    let slot = arg.event.extendedProps.slot_data;
                    let subjectName = slot.subject ? slot.subject.name : 'N/A';
                    let teacherName = slot.staff_profile && slot.staff_profile.user ? slot.staff_profile
                        .user.name : 'Unassigned';
                    let roomName = slot.classroom ? ' | Rm: ' + slot.classroom.room_number : '';
                    let sectionName = slot.section ? slot.section.name : 'All';
                    let shiftName = slot.shift ? slot.shift.name : '';
                    let sectionShiftName = sectionName + (shiftName ? ' (' + shiftName + ')' : '');

                    const formatTime = (timeStr) => {
                        if (!timeStr) return '';
                        let [h, m] = timeStr.split(':');
                        let ampm = h >= 12 ? 'PM' : 'AM';
                        h = h % 12 || 12;
                        return `${h}:${m} ${ampm}`;
                    };
                    let timeText = `${formatTime(slot.start_time)} - ${formatTime(slot.end_time)}`;

                    return {
                        html: `
                        <div class="p-2 h-100 d-flex flex-column gap-1 text-white" style="font-size: 0.75rem; line-height: 1.2; white-space: normal; overflow: hidden;">
                            <div class="fw-bold" style="font-size: 0.85rem; word-break: break-word; ">${subjectName}</div>
                            <div class="fw-normal" style="opacity: 0.9;"><i class="fa-regular fa-clock me-1"></i>${timeText}</div>
                            <div style="opacity: 0.9; word-break: break-word;"><i class="fa-solid fa-user-tie me-1"></i>${teacherName}</div>
                            <div style="opacity: 0.9; word-break: break-word;"><i class="fa-solid fa-users me-1"></i>${sectionShiftName}${roomName}</div>
                        </div>
                        `
                    };
                }
            });
            calendar.render();
        });
    </script>
@endsection
