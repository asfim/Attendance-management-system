@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<style>
:root {
    --att-present:  #22c55e;
    --att-absent:   #ef4444;
    --att-late:     #f97316;
    --card-bg:      #ffffff;
    --card-border:  #e2e8f0;
    --text-muted:   #64748b;
    --text-main:    #0f172a;
}
[data-bs-theme="dark"] {
    --card-bg:      #1e293b;
    --card-border:  #334155;
    --text-muted:   #94a3b8;
    --text-main:    #f8fafc;
}

.summary-box {
    background-color: #f8f9fa; /* Light mode bg-light */
}

[data-bs-theme="dark"] .summary-box {
    background-color: rgba(255, 255, 255, 0.05); /* Dark mode darker background */
}

.report-header {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.matrix-table-container {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: 0;
    overflow-x: auto;
}

.matrix-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.matrix-table th, .matrix-table td {
    border: 1px solid var(--card-border);
    padding: 8px 4px;
    text-align: center;
    color: var(--text-main);
}

.matrix-table th {
    background-color: rgba(0,0,0,0.02);
    font-weight: 600;
}
[data-bs-theme="dark"] .matrix-table th {
    background-color: rgba(255,255,255,0.02);
}

.matrix-table td.stu-name {
    text-align: left;
    padding: 8px 12px;
    white-space: nowrap;
    position: sticky;
    left: 0;
    background: var(--card-bg);
    z-index: 1;
    border-right: 2px solid var(--card-border);
}
.matrix-table th.stu-name-th {
    position: sticky;
    left: 0;
    background: var(--card-bg);
    z-index: 2;
    border-right: 2px solid var(--card-border);
}

.att-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    color: #fff;
    font-weight: bold;
    font-size: 0.75rem;
}
.att-status.p { background-color: var(--att-present); }
.att-status.a { background-color: var(--att-absent); }
.att-status.l { background-color: var(--att-late); }
.att-status.none { background-color: transparent; color: var(--text-muted); }

@media print {
    body { background-color: #fff !important; }
    .sidebar, .navbar-custom, .d-print-none, header, footer, .breadcrumb { display: none !important; }
    .main-content { margin-left: 0 !important; padding-top: 0 !important; width: 100% !important; }
    .matrix-table-container { border: none !important; box-shadow: none !important; }
    .matrix-table th, .matrix-table td { border-color: #000 !important; color: #000 !important; }
    .summary-box { border: 1px solid #000 !important; background: transparent !important; }
    .att-status { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>

<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 d-print-none">
        <div>
            <h4 class="mb-1 fw-semibold text-primary"><i class="fa-solid fa-calendar-check me-2"></i>Class Attendance Report</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 fs-7">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item">Reports</li>
                    <li class="breadcrumb-item active" aria-current="page">Attendance</li>
                </ol>
            </nav>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm"><i class="fa-solid fa-print me-2"></i>Print Report</button>
        </div>
    </div>

    <!-- Filter Header -->
    <div class="report-header shadow-sm d-print-none">
        <form action="{{ route('admin.reports.attendance') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted fs-7">Class</label>
                <select name="class_id" id="class_id" class="form-select border-secondary border-opacity-25" required>
                    <option value="" disabled selected>Select Class</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ $classId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fs-7">Section</label>
                <select name="section_id" id="section_id" class="form-select border-secondary border-opacity-25" required>
                    <option value="" disabled selected>Select Section</option>
                    @if($classId)
                        @php
                            $selectedClass = $classes->where('id', $classId)->first();
                        @endphp
                        @if($selectedClass)
                            @foreach($selectedClass->sections as $sec)
                                <option value="{{ $sec->id }}" {{ $sectionId == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                            @endforeach
                        @endif
                    @endif
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted fs-7">Month</label>
                <input type="month" name="month" class="form-control border-secondary border-opacity-25" value="{{ $monthStr }}" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-search me-2"></i>Generate Report</button>
            </div>
        </form>
    </div>

    @if($classId && $sectionId)
        <div class="matrix-table-container shadow-sm mt-4">
            @php
                $selClass = $classes->where('id', $classId)->first();
                $selSection = $selClass ? $selClass->sections->where('id', $sectionId)->first() : null;
            @endphp
            @include('admin.reports.partials.print_header', [
                'title' => 'Class Attendance Report',
                'subtitle' => 'Month: ' . $month->format('F Y') . ' &mdash; Class: ' . ($selClass->name ?? '') . ' &mdash; Section: ' . ($selSection->name ?? '')
            ])
            <div class="p-3 border-bottom border-secondary border-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h6 class="m-0 fw-bold"><i class="fa-solid fa-list me-2"></i>Attendance Matrix ({{ $month->format('F Y') }})</h6>
                <div class="d-flex gap-3 rounded px-3 py-1 border border-secondary border-opacity-10 summary-box">
                    <span class="fs-7 fw-semibold"><span class="text-success">Total Present:</span> {{ $classTotalPresent }}</span>
                    <span class="fs-7 text-secondary">|</span>
                    <span class="fs-7 fw-semibold"><span class="text-danger">Total Absent:</span> {{ $classTotalAbsent }}</span>
                    <span class="fs-7 text-secondary">|</span>
                    <span class="fs-7 fw-semibold"><span class="text-warning">Total Late:</span> {{ $classTotalLate }}</span>
                </div>

                <div class="d-flex gap-3">
                    <span class="fs-7 text-muted"><span class="att-status p me-1">P</span> Present</span>
                    <span class="fs-7 text-muted"><span class="att-status a me-1">A</span> Absent</span>
                    <span class="fs-7 text-muted"><span class="att-status l me-1">L</span> Late</span>
                </div>
            </div>
            <table class="matrix-table">
                <thead>
                    <tr>
                        <th class="stu-name-th">Student Name</th>
                        @for($i = 1; $i <= $daysInMonth; $i++)
                            <th>{{ sprintf('%02d', $i) }}</th>
                        @endfor
                        <th>Total(%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        @php
                            $presentCount = 0;
                            $totalWorkingDays = 0;
                        @endphp
                        <tr>
                            <td class="stu-name">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-sm rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width:24px; height:24px; font-size:0.7rem;">
                                        {{ substr($student->user->name, 0, 1) }}
                                    </div>
                                    <span>{{ $student->user->name }}</span>
                                </div>
                            </td>
                            @for($i = 1; $i <= $daysInMonth; $i++)
                                @php
                                    $status = $attendances[$student->id][$i] ?? null;
                                    $classStatus = 'none';
                                    $label = '-';
                                    if ($status == 'present') { $classStatus = 'p'; $label = 'P'; $presentCount++; $totalWorkingDays++; }
                                    elseif ($status == 'absent') { $classStatus = 'a'; $label = 'A'; $totalWorkingDays++; }
                                    elseif ($status == 'late') { $classStatus = 'l'; $label = 'L'; $presentCount += 0.5; $totalWorkingDays++; }
                                @endphp
                                <td>
                                    <span class="att-status {{ $classStatus }}">{{ $label }}</span>
                                </td>
                            @endfor
                            <td class="fw-bold text-primary">
                                {{ $totalWorkingDays > 0 ? round(($presentCount / $totalWorkingDays) * 100) : 0 }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $daysInMonth + 2 }}" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-inbox fs-1 mb-3"></i>
                                <p>No students found in this section.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @include('admin.reports.partials.print_footer')
        </div>
    @else
        <div class="text-center py-5 text-muted" style="background: var(--card-bg); border-radius: 12px; border: 1px dashed var(--card-border);">
            <i class="fa-solid fa-chart-bar fs-1 mb-3 text-secondary opacity-50"></i>
            <p>Select a class, section, and month to generate the report.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class_id');
    const sectionSelect = document.getElementById('section_id');
    const classes = @json($classes);

    classSelect.addEventListener('change', function() {
        const classId = this.value;
        sectionSelect.innerHTML = '<option value="" disabled selected>Select Section</option>';
        
        const selectedClass = classes.find(c => c.id == classId);
        if (selectedClass && selectedClass.sections) {
            selectedClass.sections.forEach(section => {
                const option = document.createElement('option');
                option.value = section.id;
                option.textContent = section.name;
                sectionSelect.appendChild(option);
            });
        }
    });
});
</script>
@endsection
