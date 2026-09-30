@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-light"><i class="fa-solid fa-chart-column text-primary me-2"></i>Marks Entry</h4>
            <p class="text-muted fs-7 mb-0">Enter and manage student marks for exams.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card glass-card border shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.marks.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label text-light fs-7 fw-semibold">Exam Type</label>
                    <select name="exam_type_id" class="form-select bg-body text-body border-secondary border-opacity-50" required onchange="this.form.submit()">
                        <option value="">-- Select Exam --</option>
                        @foreach($examTypes as $type)
                            <option value="{{ $type->id }}" {{ $selectedExamType == $type->id ? 'selected' : '' }}>{{ $type->name }} ({{ $type->academicSession->name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-light fs-7 fw-semibold">Class</label>
                    <select name="class_id" class="form-select bg-body text-body border-secondary border-opacity-50" required onchange="this.form.submit()">
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClass == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-light fs-7 fw-semibold">Subject</label>
                    <select name="subject_id" class="form-select bg-body text-body border-secondary border-opacity-50" {{ $schedules->isEmpty() ? 'disabled' : '' }}>
                        <option value="">-- Select Subject --</option>
                        @foreach($schedules as $sch)
                            <option value="{{ $sch->subject_id }}" {{ $selectedSubject == $sch->subject_id ? 'selected' : '' }}>{{ $sch->subject->name ?? '-' }} (Max: {{ $sch->max_marks }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="border-radius: 8px;"><i class="fa-solid fa-search me-2"></i>Load</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Marks Entry -->
    @if($schedule && $students->count() > 0)
    <form action="{{ route('admin.marks.store') }}" method="POST">
        @csrf
        <input type="hidden" name="exam_schedule_id" value="{{ $schedule->id }}">

        <div class="card glass-card border shadow-sm rounded-4">
            <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-light">
                    <i class="fa-solid fa-pen-to-square text-success me-2"></i>Marks Entry
                    <span class="text-muted fs-7 fw-normal ms-2">— Max: {{ $schedule->max_marks }} | Pass: {{ $schedule->pass_marks }}</span>
                </h6>
                <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;"><i class="fa-solid fa-save me-2"></i>Save Marks</button>
            </div>
            <div class="table-responsive">
                <table class="table align-middle text-light mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                    <thead style="background-color: rgba(0,0,0,0.2);">
                        <tr>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0">#</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Student</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Roll</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 text-center">Marks Obtained</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 text-center">Attendance</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 text-center">Grade & Point</th>
                            <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $i => $student)
                            @php
                                $marksObtained = $student->marks_obtained;
                                $isPassed = $marksObtained !== null && $marksObtained >= $schedule->pass_marks;
                                $grade = null;
                                if($marksObtained !== null) {
                                    $percentage = ($marksObtained / $schedule->max_marks) * 100;
                                    $grade = \App\Models\GradeRule::getGradeForPercentage($percentage);
                                }
                            @endphp
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="px-4 py-3 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($student->photo_path)
                                            <img src="{{ asset('storage/' . $student->photo_path) }}" class="rounded-circle object-fit-cover border border-secondary border-opacity-25 me-3" style="width: 32px; height: 32px;">
                                        @else
                                            <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 32px; height: 32px; font-size: 0.7rem;">
                                                {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="fw-semibold text-light">{{ $student->user->name ?? 'N/A' }}</div>
                                    </div>
                                </td>
                                <td class="text-muted fs-7">{{ $student->roll_no ?? '-' }}</td>
                                <td class="text-center" style="width: 140px;">
                                    <input type="number" name="marks[{{ $student->id }}][obtained]" value="{{ $marksObtained }}" class="form-control form-control-sm bg-body text-body border-secondary border-opacity-50 text-center" min="0" max="{{ $schedule->max_marks }}" step="0.01" placeholder="0">
                                </td>
                                <td class="text-center" style="width: 140px;">
                                    <select name="marks[{{ $student->id }}][attendance]" class="form-select form-select-sm bg-body text-body border-secondary border-opacity-50">
                                        <option value="present" {{ $student->attendance_status === 'present' ? 'selected' : '' }}>Present</option>
                                        <option value="absent" {{ $student->attendance_status === 'absent' ? 'selected' : '' }}>Absent</option>
                                    </select>
                                </td>
                                <td class="text-center">
                                    @if($marksObtained !== null)
                                        @if($grade)
                                            <span class="badge {{ $isPassed ? 'bg-success text-success border-success' : 'bg-danger text-danger border-danger' }} bg-opacity-10 border border-opacity-25 rounded-pill px-2 py-1 fs-7">
                                                {{ $grade->grade }} ({{ number_format($grade->point, 2) }})
                                            </span>
                                        @else
                                            @if($isPassed)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 fs-7">Pass</span>
                                            @else
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 fs-7">Fail</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="text-muted fs-7">—</span>
                                    @endif
                                </td>
                                <td class="px-4" style="width: 180px;">
                                    <input type="text" name="marks[{{ $student->id }}][remarks]" value="{{ $student->remarks }}" class="form-control form-control-sm bg-body text-body border-secondary border-opacity-50" placeholder="Optional">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-transparent border-top border-secondary border-opacity-25 p-4 text-end">
                <button type="submit" class="btn btn-primary px-5 py-2 shadow-sm" style="border-radius: 8px;"><i class="fa-solid fa-save me-2"></i>Save All Marks</button>
            </div>
        </div>
    </form>
    @elseif($selectedSubject)
    <div class="card glass-card border shadow-sm rounded-4">
        <div class="card-body p-5 text-center text-muted">
            <i class="fa-solid fa-circle-exclamation fs-1 mb-3 d-block opacity-50"></i>
            <p class="mb-0">No exam schedule found for the selected combination, or no students enrolled in this class.</p>
        </div>
    </div>
    @else
    <div class="card glass-card border shadow-sm rounded-4">
        <div class="card-body p-5 text-center text-muted">
            <i class="fa-solid fa-chart-column fs-1 mb-3 d-block opacity-50"></i>
            <p class="mb-0">Select Exam Type, Class, and Subject above to start entering marks.</p>
        </div>
    </div>
    @endif
</div>
@endsection
