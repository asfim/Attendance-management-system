@extends('layouts.app')

@section('title', 'Student Food Attendance')

@section('content')
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .food-att-table th,
        .food-att-table td {
            vertical-align: middle;
            text-align: center;
        }

        .food-att-btn {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.68rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.15s, box-shadow 0.15s;
            border: none;
        }

        .food-att-btn:hover {
            transform: scale(1.2);
        }

        .btn-status-taken {
            background-color: #22c55e !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(34, 197, 94, 0.4);
        }

        .btn-status-not_taken {
            background-color: #ef4444 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.4);
        }

        .btn-status-leave {
            background-color: #f97316 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(249, 115, 22, 0.4);
        }

        .btn-status-holiday {
            background-color: rgba(107, 114, 128, 0.25) !important;
            color: var(--bs-body-color, #9ca3af) !important;
            border: 1px dashed var(--bs-border-color, #6b7280) !important;
        }

        .today-col-header {
            background: rgba(13, 110, 253, 0.15) !important;
            border-bottom: 2px solid #0d6efd !important;
            color: var(--bs-primary, #0d6efd) !important;
        }

        .today-col-cell {
            background: rgba(13, 110, 253, 0.05);
        }

        .weekend-col-cell {
            background: rgba(0, 0, 0, 0.05);
        }

        [data-bs-theme="dark"] .weekend-col-cell {
            background: rgba(255, 255, 255, 0.03);
        }

        .day-header-th:hover {
            background-color: rgba(13, 110, 253, 0.25) !important;
            cursor: pointer;
        }

        /* SweetAlert2 Theme Adaptive Styling */
        .swal2-popup {
            background-color: var(--bs-body-bg, #1e293b) !important;
            color: var(--bs-body-color, #f8fafc) !important;
            border: 1px solid var(--bs-border-color, #334155) !important;
            border-radius: 16px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        }

        .swal2-title,
        .swal2-html-container {
            color: var(--bs-body-color, #f8fafc) !important;
        }
    </style>

    <div class="container-fluid px-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-body">
                    <i class="fa-solid fa-calendar-check me-2"></i>Student Food Attendance
                </h5>
                <p class="text-secondary fs-8 mb-0">Mark daily meal attendance for students (Taken, Not Taken, Leave,
                    Holiday)</p>
            </div>

            {{-- Bulk Action Controls --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="input-group input-group-sm" style="width: auto;">
                    <span class="input-group-text bg-body-tertiary text-body border-secondary-subtle fs-8 fw-semibold">Target
                        Date:</span>
                    <input type="date" id="bulk_attendance_date"
                        class="form-control form-control-sm bg-body text-body border-secondary-subtle fw-medium"
                        value="{{ date('Y-m-d') }}">
                </div>

                <button type="button" id="markAllDateBtn" class="btn btn-sm btn-primary text-white shadow-sm fw-bold px-3">
                    <i class="fa-solid fa-calendar-day me-1"></i>Mark Selected Date Present
                </button>

                <button type="button" id="markAllMonthBtn" class="btn btn-sm btn-info text-white shadow-sm fw-bold px-3">
                    <i class="fa-solid fa-square-check me-1"></i>Mark Entire Month Present
                </button>
            </div>
        </div>

        {{-- Filter Form --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
            <div class="card-body p-3">
                <form action="{{ route('admin.food.attendance.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label text-secondary fs-8 fw-semibold mb-1">
                            <i class="fa-regular fa-calendar-days me-1 text-primary"></i>Select Month & Year
                        </label>
                        <input type="month" name="month_year" id="filter_month_year"
                            class="form-control form-control-sm bg-body text-body border-secondary-subtle fw-semibold"
                            value="{{ sprintf('%04d-%02d', $year, $month) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary fs-8 fw-semibold mb-1">Class</label>
                        <select name="class_id" id="filter_class_id"
                            class="form-select form-select-sm bg-body text-body border-secondary-subtle">
                            <option value="">All Classes</option>
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" {{ $c->id == $classId ? 'selected' : '' }}>
                                    {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label text-secondary fs-8 fw-semibold mb-1">Meal Type</label>
                        <select name="meal_id" id="filter_meal_id"
                            class="form-select form-select-sm bg-body text-body border-secondary-subtle">
                            @foreach ($meals as $m)
                                <option value="{{ $m->id }}" {{ $m->id == $mealId ? 'selected' : '' }}>
                                    {{ $m->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                            <i class="fa-solid fa-filter me-1"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Legend --}}
        <div
            class="d-flex flex-wrap align-items-center justify-content-center gap-3 mb-3 fs-8 text-secondary py-2 px-3 rounded-3 border border-secondary-subtle bg-body-tertiary">
            <span class="fw-bold text-body">Status Legend:</span>
            <div class="d-flex align-items-center gap-1"><span class="badge btn-status-taken px-2 py-1">✓ Taken
                    (Present)</span></div>
            <div class="d-flex align-items-center gap-1"><span class="badge btn-status-not_taken px-2 py-1">✕ Not Taken
                    (Absent)</span></div>
            <div class="d-flex align-items-center gap-1"><span class="badge btn-status-leave px-2 py-1">L Leave</span></div>
            <div class="d-flex align-items-center gap-1"><span class="badge btn-status-holiday px-2 py-1">H Holiday</span>
            </div>
            <span class="ms-2 text-muted fw-semibold">(Tip: Click any day header number to mark that day present!)</span>
        </div>

        {{-- Attendance Grid --}}
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
            <div class="card-body p-0">
                @if ($students->count())
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0 text-center fs-8 food-att-table">
                            <thead>
                                <tr class="bg-body-tertiary text-body fw-bold">
                                    <th class="ps-3 text-start fw-bold" style="min-width: 160px;">Student Name</th>
                                    @for ($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $carbonDate = \Carbon\Carbon::create($year, $month, $day);
                                            $dateStr = $carbonDate->format('Y-m-d');
                                            $isToday = $carbonDate->isToday();
                                            $dayName = strtoupper(substr($carbonDate->format('D'), 0, 3));
                                        @endphp
                                        <th class="day-header-th {{ $isToday ? 'today-col-header' : '' }}"
                                            data-day="{{ $day }}" data-date="{{ $dateStr }}"
                                            title="Click to Mark Day {{ $day }} All Present"
                                            style="min-width: 30px; padding: 4px 1px;">
                                            <div style="font-size: 0.8rem; font-weight: 700;">{{ $day }}</div>
                                            <div class="text-secondary" style="font-size: 0.6rem;">{{ $dayName }}
                                            </div>
                                        </th>
                                    @endfor
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    <tr>
                                        <td class="ps-3 text-start fw-bold text-body">
                                            {{ $student->user->name ?? 'Student' }}
                                            <div class="text-secondary fs-8 fw-normal">Roll:
                                                {{ $student->roll_no ?? 'N/A' }}</div>
                                        </td>
                                        @for ($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $carbonDate = \Carbon\Carbon::create($year, $month, $day);
                                                $dateStr = $carbonDate->format('Y-m-d');
                                                $isToday = $carbonDate->isToday();
                                                $isWeekend = $carbonDate->isWeekend();

                                                $key = $student->id . '_' . $dateStr;
                                                $record = isset($attendances[$key])
                                                    ? $attendances[$key]->first()
                                                    : null;
                                                $status = $record ? $record->status : 'taken';

                                                $btnClass = 'btn-status-' . $status;
                                                $icon = match ($status) {
                                                    'taken' => '✓',
                                                    'not_taken' => '✕',
                                                    'leave' => 'L',
                                                    'holiday' => 'H',
                                                    default => '-',
                                                };
                                            @endphp
                                            <td
                                                class="p-1 {{ $isToday ? 'today-col-cell' : ($isWeekend ? 'weekend-col-cell' : '') }}">
                                                <button type="button" class="food-att-btn {{ $btnClass }}"
                                                    data-student-id="{{ $student->id }}"
                                                    data-meal-id="{{ $mealId }}" data-day="{{ $day }}"
                                                    data-date="{{ $dateStr }}" data-status="{{ $status }}"
                                                    title="{{ $student->user->name }} - {{ $dateStr }}: {{ ucfirst(str_replace('_', ' ', $status)) }}">
                                                    {{ $icon }}
                                                </button>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5 text-secondary fs-7">
                        <i class="fa-solid fa-user-xmark fa-2x mb-2 d-block opacity-25"></i>
                        No food enabled students found for the selected class/filter.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusCycle = {
                'taken': {
                    next: 'not_taken',
                    class: 'btn-status-not_taken',
                    icon: '✕'
                },
                'not_taken': {
                    next: 'leave',
                    class: 'btn-status-leave',
                    icon: 'L'
                },
                'leave': {
                    next: 'holiday',
                    class: 'btn-status-holiday',
                    icon: 'H'
                },
                'holiday': {
                    next: 'taken',
                    class: 'btn-status-taken',
                    icon: '✓'
                }
            };

            // Single Cell Toggle
            document.querySelectorAll('.food-att-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const currentStatus = this.getAttribute('data-status') || 'taken';
                    const nextInfo = statusCycle[currentStatus] || statusCycle['taken'];

                    const studentId = this.getAttribute('data-student-id');
                    const mealId = this.getAttribute('data-meal-id');
                    const date = this.getAttribute('data-date');

                    // Optimistic UI update
                    this.setAttribute('data-status', nextInfo.next);
                    this.className = 'food-att-btn ' + nextInfo.class;
                    this.innerText = nextInfo.icon;

                    // Send AJAX request
                    fetch("{{ route('admin.food.attendance.mark') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            },
                            body: JSON.stringify({
                                student_profile_id: studentId,
                                meal_id: mealId,
                                attendance_date: date,
                                status: nextInfo.next
                            })
                        })
                        .then(res => res.json())
                        .catch(err => console.error(err));
                });
            });

            // Helper to send Bulk Attendance Request with Theme-Aware SweetAlert2
            function executeBulkMark(payload, targetButtons, targetText) {
                const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';

                Swal.fire({
                    title: 'Mark All as Taken?',
                    text: `Are you sure you want to mark all students as Taken (Present) for ${targetText}?`,
                    icon: 'question',
                    showCancelButton: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'rounded-4 shadow-lg border-0',
                        confirmButton: 'btn btn-primary px-4 py-2 fw-bold rounded-3 me-2 shadow-sm',
                        cancelButton: 'btn btn-outline-secondary px-4 py-2 fw-bold rounded-3'
                    },
                    confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Yes, Mark All Taken!',
                    cancelButtonText: 'Cancel',
                    background: isDark ? '#0f172a' : '#ffffff',
                    color: isDark ? '#f8fafc' : '#0f172a',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading
                        Swal.fire({
                            title: 'Processing Attendance...',
                            text: 'Saving attendance records, please wait...',
                            allowOutsideClick: false,
                            background: isDark ? '#0f172a' : '#ffffff',
                            color: isDark ? '#f8fafc' : '#0f172a',
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        // Optimistically update target grid buttons in UI to Taken (✓)
                        if (targetButtons && targetButtons.length) {
                            targetButtons.forEach(btn => {
                                btn.setAttribute('data-status', 'taken');
                                btn.className = 'food-att-btn btn-status-taken';
                                btn.innerText = '✓';
                            });
                        }

                        // Send AJAX Request
                        fetch("{{ route('admin.food.attendance.mark-all') }}", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                                },
                                body: JSON.stringify(payload)
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false,
                                        background: isDark ? '#0f172a' : '#ffffff',
                                        color: isDark ? '#f8fafc' : '#0f172a',
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: 'Failed to save bulk attendance.',
                                        icon: 'error',
                                        background: isDark ? '#0f172a' : '#ffffff',
                                        color: isDark ? '#f8fafc' : '#0f172a',
                                    });
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Server error during bulk attendance saving.',
                                    icon: 'error',
                                    background: isDark ? '#0f172a' : '#ffffff',
                                    color: isDark ? '#f8fafc' : '#0f172a',
                                });
                            });
                    }
                });
            }

            // Mark Selected Date All Present
            const markDateBtn = document.getElementById('markAllDateBtn');
            if (markDateBtn) {
                markDateBtn.addEventListener('click', function() {
                    const rawDate = document.getElementById('bulk_attendance_date').value;
                    const mealId = document.getElementById('filter_meal_id').value;
                    const classId = document.getElementById('filter_class_id').value;

                    if (!rawDate) {
                        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                        Swal.fire({
                            title: 'Warning',
                            text: 'Please select a target date first!',
                            icon: 'warning',
                            background: isDark ? '#0f172a' : '#ffffff',
                            color: isDark ? '#f8fafc' : '#0f172a',
                        });
                        return;
                    }

                    const dateParts = rawDate.split('-');
                    const dayNum = parseInt(dateParts[2], 10);

                    // Match buttons by data-date or fallback to data-day
                    let targetButtons = document.querySelectorAll(`.food-att-btn[data-date="${rawDate}"]`);
                    if (!targetButtons.length) {
                        targetButtons = document.querySelectorAll(`.food-att-btn[data-day="${dayNum}"]`);
                    }

                    executeBulkMark({
                        attendance_date: rawDate,
                        meal_id: mealId || null,
                        class_id: classId || null,
                        status: 'taken'
                    }, targetButtons, rawDate);
                });
            }

            // Click Day Column Header to Mark Day All Present
            document.querySelectorAll('.day-header-th').forEach(th => {
                th.addEventListener('click', function() {
                    const dayNum = this.getAttribute('data-day');
                    const dateStr = this.getAttribute('data-date');
                    const mealId = document.getElementById('filter_meal_id').value;
                    const classId = document.getElementById('filter_class_id').value;

                    // Set date input
                    document.getElementById('bulk_attendance_date').value = dateStr;

                    const targetButtons = document.querySelectorAll(
                        `.food-att-btn[data-day="${dayNum}"]`);
                    executeBulkMark({
                        attendance_date: dateStr,
                        meal_id: mealId || null,
                        class_id: classId || null,
                        status: 'taken'
                    }, targetButtons, dateStr);
                });
            });

            // Mark Entire Month All Present
            const markMonthBtn = document.getElementById('markAllMonthBtn');
            if (markMonthBtn) {
                markMonthBtn.addEventListener('click', function() {
                    const month = document.getElementById('filter_month').value;
                    const year = document.getElementById('filter_year').value;
                    const mealId = document.getElementById('filter_meal_id').value;
                    const classId = document.getElementById('filter_class_id').value;

                    const allGridButtons = document.querySelectorAll('.food-att-btn');

                    executeBulkMark({
                        month: month,
                        year: year,
                        mark_all_days: true,
                        meal_id: mealId || null,
                        class_id: classId || null,
                        status: 'taken'
                    }, allGridButtons, `all days in this month`);
                });
            }
        });
    </script>
@endsection
