@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.students.history.index') }}" class="text-decoration-none text-muted">
        <i class="fa-solid fa-arrow-left me-2"></i>Back to Search
    </a>
</div>

<div class="row g-4">
    <!-- Student Info Sidebar -->
    <div class="col-md-4">
        <div class="card glass-card border p-4 sticky-top" style="top: 2rem;">
            <div class="text-center mb-4">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" alt="Photo"
                        class="rounded-circle object-fit-cover border border-primary border-opacity-25 mx-auto mb-3"
                        style="width: 100px; height: 100px;">
                @else
                    <div class="avatar-circle mb-3 mx-auto" style="width: 100px; height: 100px; font-size: 2.5rem;">
                        {{ strtoupper(substr($student->user->name, 0, 1)) }}
                    </div>
                @endif
                <h5 class="fw-bold mb-1">{{ $student->user->name }}</h5>
                <p class="text-muted mb-0">{{ $student->admission_no }}</p>
            </div>
            
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Class</span>
                    <span class="fw-semibold">{{ $student->schoolClass->name }} - {{ $student->section->name }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Roll No</span>
                    <span class="fw-semibold">{{ $student->roll_no }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Parent</span>
                    <span class="fw-semibold">{{ $student->parent ? $student->parent->user->name : 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- History Tabs -->
    <div class="col-md-8">
        <div class="card glass-card border p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold m-0">History Details</h5>
                <form method="GET" action="{{ route('admin.students.history.show', $student->id) }}" class="d-flex gap-2 align-items-center">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
                    <span class="text-muted">to</span>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
                    <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                    @if(request('start_date') || request('end_date'))
                        <a href="{{ route('admin.students.history.show', $student->id) }}" class="btn btn-sm btn-light text-nowrap">Clear</a>
                    @endif
                </form>
            </div>

            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="historyTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">Attendance</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="fees-tab" data-bs-toggle="tab" data-bs-target="#fees" type="button" role="tab">Fees</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="results-tab" data-bs-toggle="tab" data-bs-target="#results" type="button" role="tab">Results</button>
                </li>
            </ul>

            <div class="tab-content" id="historyTabsContent">
                <!-- Attendance Tab -->
                <div class="tab-pane fade show active" id="attendance" role="tabpanel">
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0">Attendance Records</h6>
                        
                        <!-- Month/Year Selector -->
                        <form action="{{ route('admin.students.history.show', $student->id) }}" method="GET" class="d-flex gap-2 align-items-center">
                            @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
                            @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif
                            
                            <select name="month" class="form-select form-select-sm bg-dark text-light border-secondary" style="width:120px;" onchange="this.form.submit()">
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endfor
                            </select>
                            
                            <select name="year" class="form-select form-select-sm bg-dark text-light border-secondary" style="width:100px;" onchange="this.form.submit()">
                                @for($y = date('Y') - 5; $y <= date('Y') + 1; $y++)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                        {{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </form>
                    </div>
                    @php $isDateRange = request('start_date') && request('end_date'); @endphp
                    
                    @if($isDateRange)
                        <!-- Accordion for Date Range -->
                        <div class="accordion accordion-flush mb-4" id="attendanceAccordion">
                            @php
                                $groupedByMonth = $student->attendances->groupBy(function($att) {
                                    return \Carbon\Carbon::parse($att->attendance_date)->format('F Y');
                                });
                            @endphp
                            
                            @forelse($groupedByMonth as $monthLabel => $atts)
                                @php
                                    $monthId = \Illuminate\Support\Str::slug($monthLabel);
                                    $present = $atts->where('status', 'present')->count();
                                    $absent = $atts->where('status', 'absent')->count();
                                    $late = $atts->where('status', 'late')->count();
                                    $leave = $atts->where('status', 'leave')->count();
                                    $halfDay = $atts->where('status', 'half_day')->count();
                                @endphp
                                <div class="accordion-item bg-transparent border-0 mb-3 glass-card rounded-4 overflow-hidden">
                                    <h2 class="accordion-header" id="heading-{{ $monthId }}">
                                        <button class="accordion-button collapsed bg-transparent text-light fw-bold shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $monthId }}" aria-expanded="false" aria-controls="collapse-{{ $monthId }}">
                                            <i class="fa-solid fa-calendar-days text-primary me-2"></i> {{ $monthLabel }} 
                                            <span class="ms-3 badge bg-secondary bg-opacity-25">{{ $atts->count() }} Records</span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $monthId }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $monthId }}" data-bs-parent="#attendanceAccordion">
                                        <div class="accordion-body p-4 pt-0">
                                            <div class="d-flex flex-wrap gap-2 mb-3 mt-2">
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">Present: {{ $present }}</span>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">Absent: {{ $absent }}</span>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1">Late: {{ $late }}</span>
                                                @if($leave > 0) <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">Leave: {{ $leave }}</span> @endif
                                                @if($halfDay > 0) <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">Half Day: {{ $halfDay }}</span> @endif
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table align-middle table-sm text-light mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                                                    <thead style="background-color: rgba(0,0,0,0.2);">
                                                        <tr>
                                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 px-3 border-0">Date</th>
                                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Status</th>
                                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Reason / Remarks</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($atts->sortByDesc('attendance_date') as $att)
                                                            <tr class="border-bottom border-secondary border-opacity-10 hover-bg-secondary transition-all">
                                                                <td class="px-3 py-2 fw-semibold">
                                                                    {{ \Carbon\Carbon::parse($att->attendance_date)->format('F d, Y') }}
                                                                    <div class="text-muted fs-8">{{ \Carbon\Carbon::parse($att->attendance_date)->format('l') }}</div>
                                                                </td>
                                                                <td>
                                                                    <span class="badge {{ $att->badgeClass() }} bg-opacity-10 border border-opacity-25 rounded-pill px-2 py-1 fs-7" style="color: {{ $att->calendarColor() }}; border-color: {{ $att->calendarColor() }} !important;">
                                                                        {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-muted fs-7">
                                                                    @if($att->status === 'late' && $att->late_reason) <strong class="text-warning">Reason:</strong> {{ $att->late_reason }} <br> @endif
                                                                    @if($att->status === 'leave' && $att->leave_reason) <strong class="text-primary">Reason:</strong> {{ $att->leave_reason }} <br> @endif
                                                                    @if($att->remarks) <strong>Note:</strong> {{ $att->remarks }} <br> @endif
                                                                    @if($att->leave_attachment)
                                                                        <a href="{{ asset('storage/' . $att->leave_attachment) }}" target="_blank" class="badge bg-secondary bg-opacity-25 text-light text-decoration-none border border-secondary mt-1">
                                                                            <i class="fa-solid fa-paperclip me-1"></i>View Attachment
                                                                        </a>
                                                                    @endif
                                                                    @if(!$att->late_reason && !$att->leave_reason && !$att->remarks && !$att->leave_attachment) <span class="text-muted">-</span> @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5 text-muted glass-card rounded-4">
                                    <i class="fa-solid fa-calendar-xmark fs-2 mb-3 d-block"></i>
                                    No attendance records found in this date range.
                                </div>
                            @endforelse
                        </div>
                    @else
                        <!-- Visual Calendar Grid -->
                        <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                            <div class="table-responsive p-2">
                                <table class="table align-middle text-center text-light mb-0 mx-auto" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05); max-width: 500px;">
                                    <thead style="background-color: rgba(0,0,0,0.2);">
                                        <tr>
                                            @foreach(['MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'] as $dayName)
                                                <th class="text-uppercase" style="font-size: 0.75rem; color: #a1a1aa; font-weight: 600; padding: 10px 4px; border: none;">{{ $dayName }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                <tbody>
                                    @foreach($weeks as $week)
                                        <tr>
                                            @foreach($week as $day)
                                                <td class="p-1 border-0" style="width:14.28%;">
                                                    @if($day['in_month'])
                                                        @php
                                                            $bg = $day['color'] ?? 'transparent';
                                                            $textColor = $day['color'] ? '#fff' : 'var(--bs-body-color)';
                                                        @endphp
                                                        <div class="d-flex justify-content-center align-items-center mx-auto" 
                                                            style="width: 32px; height: 32px; border-radius: 8px; background-color: {{ $bg }}; color: {{ $textColor }}; font-weight: 600; font-size: 0.85rem; cursor: default; transition: all 0.2s;">
                                                            {{ $day['day'] }}
                                                        </div>
                                                    @else
                                                        <span class="text-muted opacity-50" style="font-size: 0.85rem;">{{ $day['day'] }}</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Details Table -->
                    <div class="card glass-card border-0 shadow-sm rounded-4 overflow-hidden">
                        @php
                            $present = $monthAttendances->where('status', 'present')->count();
                            $absent = $monthAttendances->where('status', 'absent')->count();
                            $late = $monthAttendances->where('status', 'late')->count();
                            $leave = $monthAttendances->where('status', 'leave')->count();
                            $halfDay = $monthAttendances->where('status', 'half_day')->count();
                            $holiday = 0;
                            foreach($weeks as $week) {
                                foreach($week as $day) {
                                    if ($day['in_month'] && $day['status'] === 'holiday') {
                                        $holiday++;
                                    }
                                }
                            }
                            $monthName = \Carbon\Carbon::create($year, $month, 1)->format('F Y');
                        @endphp
                        
                        <div class="p-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-list-check me-2"></i>{{ $monthName }} Details</h6>
                            
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">Present: {{ $present }}</span>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">Absent: {{ $absent }}</span>
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">Late: {{ $late }}</span>
                                @if($leave > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-2">Leave: {{ $leave }}</span>
                                @endif
                                @if($halfDay > 0)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">Half Day: {{ $halfDay }}</span>
                                @endif
                                @if($holiday > 0)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2">Holiday: {{ $holiday }}</span>
                                @endif
                            </div>
                            
                            @if($monthAttendances->count() > 0)
                            <div class="table-responsive">
                                <table class="table align-middle table-sm text-light mb-0"
                                    style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                                    <thead style="background-color: rgba(0,0,0,0.2);">
                                        <tr>
                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 px-3 border-0">Date</th>
                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Status</th>
                                            <th class="text-uppercase fs-7 text-muted fw-semibold py-2 border-0">Reason / Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($monthAttendances->sortByDesc('attendance_date') as $att)
                                            <tr class="border-bottom border-secondary border-opacity-10 hover-bg-secondary transition-all">
                                                <td class="px-3 py-2 fw-semibold">
                                                    {{ \Carbon\Carbon::parse($att->attendance_date)->format('F d, Y') }}
                                                    <div class="text-muted fs-8">
                                                        {{ \Carbon\Carbon::parse($att->attendance_date)->format('l') }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $att->badgeClass() }} bg-opacity-10 border border-opacity-25 rounded-pill px-2 py-1 fs-7" style="color: {{ $att->calendarColor() }}; border-color: {{ $att->calendarColor() }} !important;">
                                                        {{ ucfirst(str_replace('_', ' ', $att->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-muted fs-7">
                                                    @if($att->status === 'late' && $att->late_reason)
                                                        <strong class="text-warning">Reason:</strong> {{ $att->late_reason }} <br>
                                                    @endif
                                                    @if($att->status === 'leave' && $att->leave_reason)
                                                        <strong class="text-primary">Reason:</strong> {{ $att->leave_reason }} <br>
                                                    @endif
                                                    @if($att->remarks)
                                                        <strong>Note:</strong> {{ $att->remarks }} <br>
                                                    @endif
                                                    @if($att->leave_attachment)
                                                        <a href="{{ asset('storage/' . $att->leave_attachment) }}" target="_blank" class="badge bg-secondary bg-opacity-25 text-light text-decoration-none border border-secondary mt-1">
                                                            <i class="fa-solid fa-paperclip me-1"></i>View Attachment
                                                        </a>
                                                    @endif
                                                    @if(!$att->late_reason && !$att->leave_reason && !$att->remarks && !$att->leave_attachment)
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-calendar-xmark fs-2 mb-3 d-block"></i>
                                    No attendance records found for {{ $monthName }}.
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Fees Tab -->
                <div class="tab-pane fade" id="fees" role="tabpanel">
                    <h6 class="fw-bold mb-3">Fee Invoices & Payments</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                    <th>Due Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($student->invoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }}</td>
                                        <td>{{ $invoice->title }}</td>
                                        <td>{{ number_format($invoice->total_amount, 2) }}</td>
                                        <td>{{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td>
                                            @if($invoice->status === 'paid')
                                                <span class="badge bg-success bg-opacity-10 text-success">Paid</span>
                                            @elseif($invoice->status === 'partial')
                                                <span class="badge bg-warning bg-opacity-10 text-warning">Partial</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger">Unpaid</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No fee records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Results Tab -->
                <div class="tab-pane fade" id="results" role="tabpanel">
                    <h6 class="fw-bold mb-3">Exam Results</h6>
                    @php
                        $groupedMarks = $student->marksEntries->groupBy(function($mark) {
                            return $mark->examSchedule->examType->name ?? 'Unknown Exam';
                        });
                    @endphp

                    @if($groupedMarks->count() > 0)
                        <!-- Nested Tabs for Exams -->
                        <ul class="nav nav-pills mb-3" id="exam-tabs" role="tablist">
                            @foreach($groupedMarks as $examName => $marks)
                                @php $safeExamId = Str::slug($examName); @endphp
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link {{ $loop->first ? 'active' : '' }}" 
                                            id="tab-{{ $safeExamId }}" 
                                            data-bs-toggle="pill" 
                                            data-bs-target="#content-{{ $safeExamId }}" 
                                            type="button" 
                                            role="tab">
                                        {{ $examName }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        <div class="tab-content" id="exam-tabs-content">
                            @foreach($groupedMarks as $examName => $marks)
                                @php $safeExamId = Str::slug($examName); @endphp
                                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" 
                                     id="content-{{ $safeExamId }}" 
                                     role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-3">
                                            <thead>
                                                <tr>
                                                    <th>Subject</th>
                                                    <th>Marks Obtained</th>
                                                    <th>Total Marks</th>
                                                    <th>Grade</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalPoints = 0;
                                                    $totalSubjects = 0;
                                                    $hasFailed = false;
                                                @endphp
                                                @foreach($marks as $mark)
                                                    <tr>
                                                        <td>{{ $mark->examSchedule->subject->name ?? 'N/A' }}</td>
                                                        <td>{{ $mark->marks_obtained }}</td>
                                                        <td>{{ $mark->examSchedule->max_marks ?? 'N/A' }}</td>
                                                        <td>
                                                            @if($mark->attendance_status === 'absent')
                                                                <span class="badge bg-danger">Absent</span>
                                                                @php $hasFailed = true; @endphp
                                                            @elseif($mark->marks_obtained !== null && $mark->examSchedule->max_marks > 0)
                                                                @php
                                                                    $percentage = ($mark->marks_obtained / $mark->examSchedule->max_marks) * 100;
                                                                    $grade = \App\Models\GradeRule::getGradeForPercentage($percentage);
                                                                @endphp
                                                                @if($grade)
                                                                    <span class="badge bg-primary">{{ $grade->grade }} ({{ number_format($grade->point, 2) }})</span>
                                                                    @php
                                                                        if ($grade->point <= 0) $hasFailed = true;
                                                                        $totalPoints += $grade->point;
                                                                        $totalSubjects++;
                                                                    @endphp
                                                                @else
                                                                    <span class="badge {{ $mark->marks_obtained >= $mark->examSchedule->pass_marks ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $mark->marks_obtained >= $mark->examSchedule->pass_marks ? 'Pass' : 'Fail' }}
                                                                    </span>
                                                                    @php
                                                                        if ($mark->marks_obtained < $mark->examSchedule->pass_marks) $hasFailed = true;
                                                                        $totalSubjects++;
                                                                    @endphp
                                                                @endif
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        
                                        {{-- GPA & Final Grade Summary --}}
                                        @php
                                            if ($hasFailed) {
                                                $finalGpa = 0.00;
                                                $finalGrade = 'F';
                                                $badgeColor = 'danger';
                                            } elseif ($totalSubjects > 0) {
                                                $finalGpa = $totalPoints / $totalSubjects;
                                                $finalGradeRule = \App\Models\GradeRule::where('point', '<=', round($finalGpa, 2))->orderBy('point', 'desc')->first();
                                                $finalGrade = $finalGradeRule ? $finalGradeRule->grade : 'N/A';
                                                $badgeColor = 'success';
                                            } else {
                                                $finalGpa = 0.00;
                                                $finalGrade = 'N/A';
                                                $badgeColor = 'secondary';
                                            }
                                        @endphp
                                        <div class="d-flex align-items-center gap-4 p-3 bg-{{ $badgeColor }} bg-opacity-10 border border-{{ $badgeColor }} border-opacity-25 rounded-3 mb-2">
                                            <div>
                                                <div class="text-muted fs-8 text-uppercase fw-bold mb-1">Total GPA</div>
                                                <div class="fs-4 fw-bold text-{{ $badgeColor }}">{{ number_format($finalGpa, 2) }}</div>
                                            </div>
                                            <div class="vr bg-{{ $badgeColor }} opacity-25"></div>
                                            <div>
                                                <div class="text-muted fs-8 text-uppercase fw-bold mb-1">Final Grade</div>
                                                <div class="fs-4 fw-bold text-{{ $badgeColor }}">{{ $finalGrade }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-5">
                            <i class="fa-solid fa-file-signature fs-2 mb-3 d-block"></i>
                            No result records found.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
