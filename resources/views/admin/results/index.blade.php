@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h5 class="text-white fw-bold mb-0">
                <i class="fa-solid fa-chart-column me-2"></i>Student Results
            </h5>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card border shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.results.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-light fs-7 fw-semibold">Exam</label>
                    <select name="exam_type_id" class="form-select bg-body text-body border-secondary border-opacity-50" required>
                        <option value="">-- Select Exam --</option>
                        @foreach($examTypes as $et)
                            <option value="{{ $et->id }}" {{ $selectedExamType == $et->id ? 'selected' : '' }}>
                                {{ $et->name }} ({{ $et->academicSession->name ?? 'N/A' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label text-light fs-7 fw-semibold">Class</label>
                    <select name="class_id" class="form-select bg-body text-body border-secondary border-opacity-50" required>
                        <option value="">-- Select Class --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClass == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm" style="border-radius: 8px;">
                        <i class="fa-solid fa-search me-2"></i>View Results
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($selectedExamType && $selectedClass)
        @if($students->count() > 0)
            <div class="card glass-card border shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-light">Final Results</h6>
                    <div class="search-box" style="width: 250px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body text-muted border-secondary border-opacity-50"><i class="fa-solid fa-search"></i></span>
                            <input type="text" id="liveSearch" class="form-control bg-body text-body border-secondary border-opacity-50" placeholder="Search student name or roll...">
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="accordion accordion-flush" id="resultsAccordion">
                        @foreach($students as $student)
                            <div class="accordion-item bg-transparent border-bottom border-secondary border-opacity-25">
                                <h2 class="accordion-header" id="heading-{{ $student->id }}">
                                    <button class="accordion-button collapsed bg-transparent text-light shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $student->id }}" aria-expanded="false" aria-controls="collapse-{{ $student->id }}">
                                        <div class="d-flex align-items-center w-100 me-3">
                                            @if($student->photo_path)
                                                <img src="{{ asset('storage/' . $student->photo_path) }}" class="rounded-circle me-3 border border-secondary border-opacity-50" style="width: 40px; height: 40px; object-fit: cover;" alt="Avatar">
                                            @else
                                                <div class="rounded-circle me-3 border border-secondary border-opacity-50 d-flex align-items-center justify-content-center bg-secondary bg-opacity-25 text-light" style="width: 40px; height: 40px;">
                                                    <i class="fa-solid fa-user"></i>
                                                </div>
                                            @endif
                                            
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0 fw-bold">{{ $student->user->name ?? 'N/A' }}</h6>
                                                <span class="text-muted fs-7">Roll: {{ $student->roll_no ?? 'N/A' }} | Reg: {{ $student->admission_no ?? 'N/A' }}</span>
                                            </div>

                                            @if($student->subject_count > 0)
                                                <div class="text-end me-3">
                                                    <div class="fs-7 text-muted">GPA / Grade</div>
                                                    <div class="fw-bold {{ $student->has_failed ? 'text-danger' : 'text-success' }}">
                                                        {{ number_format($student->gpa, 2) }} / {{ $student->overall_grade }}
                                                    </div>
                                                </div>
                                                <div class="text-end me-4">
                                                    <div class="fs-7 text-muted">Total Marks</div>
                                                    <div class="fw-bold text-light">
                                                        {{ $student->total_marks_obtained }} / {{ $student->total_max_marks }}
                                                    </div>
                                                </div>
                                                <div>
                                                    @if($student->has_failed)
                                                        <span class="badge bg-danger">Fail</span>
                                                    @else
                                                        <span class="badge bg-success">Pass</span>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-warning">
                                                    <i class="fa-solid fa-circle-exclamation me-1"></i>No Exams
                                                </div>
                                            @endif
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse-{{ $student->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $student->id }}" data-bs-parent="#resultsAccordion">
                                    <div class="accordion-body p-4 bg-body bg-opacity-50">
                                        @if($student->subject_results && $student->subject_results->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-dark table-hover table-striped align-middle border border-secondary border-opacity-25 rounded-3 overflow-hidden mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="py-3 px-4">Subject</th>
                                                            <th class="py-3 text-center">Total Marks</th>
                                                            <th class="py-3 text-center">Marks Obtained</th>
                                                            <th class="py-3 text-center">Grade</th>
                                                            <th class="py-3 text-center">Point</th>
                                                            <th class="py-3 text-end px-4">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($student->subject_results as $res)
                                                            <tr>
                                                                <td class="px-4 fw-medium">{{ $res->subject }}</td>
                                                                <td class="text-center">{{ $res->max_marks }}</td>
                                                                <td class="text-center">
                                                                    @if($res->is_absent)
                                                                        <span class="badge bg-danger">Absent</span>
                                                                    @elseif($res->obtained !== null)
                                                                        {{ $res->obtained }}
                                                                    @else
                                                                        <span class="text-muted">-</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center fw-bold text-info">{{ $res->grade ?? '-' }}</td>
                                                                <td class="text-center fw-bold">{{ $res->point !== null ? number_format($res->point, 2) : '-' }}</td>
                                                                <td class="text-end px-4">
                                                                    @if($res->is_absent || $res->obtained === null)
                                                                        <span class="badge bg-secondary">N/A</span>
                                                                    @elseif($res->is_pass)
                                                                        <span class="badge bg-success bg-opacity-25 text-success">Pass</span>
                                                                    @else
                                                                        <span class="badge bg-danger bg-opacity-25 text-danger">Fail</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-3">
                                                <i class="fa-solid fa-folder-open fs-3 mb-2 d-block opacity-50"></i>
                                                No results found for this student.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="card glass-card border shadow-sm rounded-4 text-center p-5">
                <i class="fa-solid fa-users-slash fs-1 text-muted mb-3"></i>
                <h5 class="text-light">No students found in this class.</h5>
                <p class="text-muted">Ensure students are enrolled in the selected class.</p>
            </div>
        @endif
    @endif
</div>

<style>
    .accordion-button::after {
        filter: invert(1);
    }
    .accordion-button:not(.collapsed) {
        background-color: rgba(13, 110, 253, 0.1) !important;
        color: #fff !important;
        box-shadow: inset 0 -1px 0 rgba(0,0,0,.125);
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('liveSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                const filter = this.value.toLowerCase();
                const items = document.querySelectorAll('#resultsAccordion .accordion-item');
                
                items.forEach(function (item) {
                    const header = item.querySelector('.accordion-header');
                    if (header) {
                        const textContent = header.textContent.toLowerCase();
                        if (textContent.includes(filter)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    }
                });
            });
        }
    });
</script>
@endpush
@endsection
