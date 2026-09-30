@extends('layouts.app')

@section('title', 'My Results')

@section('content')
<style>
.results-card {
    border-radius: 16px;
    border: 1px solid var(--bs-border-color);
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-square-poll-vertical text-primary me-2"></i>My Results</h5>
                <p class="text-muted fs-7 mb-0">View exam report cards, subject GPA, and overall grade</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        {{-- Exam Selector Card --}}
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body p-4">
                <form action="{{ route('student.results') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold fs-7 text-secondary">Select Examination</label>
                        <select name="exam_type_id" class="form-select" required>
                            <option value="">-- Choose Exam --</option>
                            @foreach($examTypes as $exam)
                                <option value="{{ $exam->id }}" {{ request('exam_type_id') == $exam->id ? 'selected' : '' }}>
                                    {{ $exam->name }} ({{ $exam->session->name ?? 'Current Session' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>View Report Card
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Report Card Display --}}
        @if(request()->filled('exam_type_id'))
            @php
                $rows = isset($reportCard['results']) ? $reportCard['results'] : (is_array($reportCard) ? $reportCard : []);
                $cgpa = isset($reportCard['cgpa']) ? $reportCard['cgpa'] : null;
                $overallStatus = isset($reportCard['status']) ? $reportCard['status'] : null;
            @endphp

            @if(count($rows))
                @php
                    $totalFull = 0;
                    $totalObtained = 0;
                    $sumGpa = 0;
                    $countSubjects = count($rows);
                    $hasFailed = false;

                    foreach ($rows as $row) {
                        $full = $row['max_marks'] ?? $row['full_marks'] ?? 100;
                        $obt = $row['obtained_marks'] ?? $row['obtained'] ?? 0;
                        $gpa = $row['gpa'] ?? 0;
                        $passMarks = $row['pass_marks'] ?? ($full * 0.4);

                        $totalFull += $full;
                        $totalObtained += $obt;
                        $sumGpa += $gpa;

                        if ($obt < $passMarks || $gpa == 0) {
                            $hasFailed = true;
                        }
                    }

                    $calculatedCgpa = $countSubjects > 0 ? round($sumGpa / $countSubjects, 2) : 0;
                    if ($hasFailed) $calculatedCgpa = 0.00;

                    $finalCgpa = $cgpa !== null ? $cgpa : $calculatedCgpa;
                    $finalStatus = $overallStatus !== null ? $overallStatus : ($hasFailed ? 'Failed' : 'Passed');

                    // Calculate Overall Letter Grade from CGPA
                    $overallGrade = match (true) {
                        $hasFailed || $finalCgpa < 1.00 => 'F',
                        $finalCgpa >= 5.00 => 'A+',
                        $finalCgpa >= 4.00 => 'A',
                        $finalCgpa >= 3.50 => 'A-',
                        $finalCgpa >= 3.00 => 'B',
                        $finalCgpa >= 2.00 => 'C',
                        $finalCgpa >= 1.00 => 'D',
                        default => 'F',
                    };

                    $gradeBadgeColor = match ($overallGrade) {
                        'A+' => 'bg-success text-success',
                        'A', 'A-' => 'bg-primary text-primary',
                        'B', 'C', 'D' => 'bg-info text-info',
                        default => 'bg-danger text-danger',
                    };
                @endphp

                {{-- Summary Header Card --}}
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="card-body p-4">
                        <div class="row g-3 align-items-center text-center">
                            {{-- Overall CGPA --}}
                            <div class="col-6 col-md-3 border-end">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Overall CGPA</div>
                                <div class="fw-bold fs-3 {{ $finalCgpa >= 3.5 ? 'text-success' : ($finalCgpa > 0 ? 'text-primary' : 'text-danger') }}">
                                    {{ number_format($finalCgpa, 2) }} <span class="fs-7 text-muted">/ 5.00</span>
                                </div>
                            </div>

                            {{-- Total Grade --}}
                            <div class="col-6 col-md-2 border-end">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Total Grade</div>
                                <div>
                                    <span class="badge {{ $gradeBadgeColor }} bg-opacity-10 border border-current fs-6 fw-bold px-3 py-1">
                                        {{ $overallGrade }}
                                    </span>
                                </div>
                            </div>

                            {{-- Result Status --}}
                            <div class="col-6 col-md-2 border-end">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Result Status</div>
                                <div>
                                    @if($finalStatus === 'Passed' || $finalStatus === 'passed')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8 px-3 py-2">
                                            <i class="fa-solid fa-trophy me-1"></i>Passed
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-8 px-3 py-2">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>Failed
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Total Marks --}}
                            <div class="col-6 col-md-3 border-end">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Total Marks</div>
                                <div class="fw-bold fs-5 text-dark-emphasis">
                                    {{ $totalObtained }} <span class="fs-7 text-muted">/ {{ $totalFull }}</span>
                                </div>
                            </div>

                            {{-- Percentage --}}
                            <div class="col-12 col-md-2">
                                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Percentage</div>
                                <div class="fw-bold fs-5 text-info">
                                    {{ $totalFull > 0 ? round(($totalObtained / $totalFull) * 100, 2) : 0 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Marks Details Table --}}
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-header bg-transparent border-bottom py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold m-0 text-primary">
                                <i class="fa-solid fa-list-check me-2"></i>Subject-wise Grade &amp; GPA Breakdown
                            </h6>
                            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                                <i class="fa-solid fa-print me-1"></i>Print Report Card
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="fs-7 text-secondary border-bottom">
                                    <tr>
                                        <th class="ps-4" style="width: 50px;">#</th>
                                        <th>Subject</th>
                                        <th class="text-center">Full Marks</th>
                                        <th class="text-center">Marks Obtained</th>
                                        <th class="text-center">Subject GPA</th>
                                        <th class="text-center">Grade</th>
                                        <th class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="fs-7">
                                    @foreach($rows as $index => $row)
                                        @php
                                            $full = $row['max_marks'] ?? $row['full_marks'] ?? 100;
                                            $obt = $row['obtained_marks'] ?? $row['obtained'] ?? 0;
                                            $pass = $row['pass_marks'] ?? ($full * 0.4);
                                            $gpa = $row['gpa'] ?? 0;
                                            $grade = $row['grade'] ?? 'F';
                                            $isPass = ($obt >= $pass) && ($gpa > 0);
                                        @endphp
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $loop->iteration }}</td>
                                            <td class="fw-bold text-dark-emphasis">
                                                <i class="fa-solid fa-book text-primary me-2 fs-8"></i>{{ $row['subject_name'] ?? 'Subject' }}
                                            </td>
                                            <td class="text-center text-muted">{{ $full }}</td>
                                            <td class="text-center fw-bold fs-6 {{ $isPass ? 'text-success' : 'text-danger' }}">
                                                {{ $obt }}
                                            </td>
                                            <td class="text-center fw-bold text-primary fs-7">
                                                {{ number_format($gpa, 2) }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8 fw-bold px-2 py-1">
                                                    {{ $grade }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($isPass)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                                        <i class="fa-solid fa-circle-check me-1"></i>Passed
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i>Failed
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="fw-bold fs-7 border-top">
                                    <tr>
                                        <td colspan="2" class="ps-4 py-3 text-end text-muted">Total Summary:</td>
                                        <td class="text-center text-muted">{{ $totalFull }}</td>
                                        <td class="text-center text-success fs-6">{{ $totalObtained }}</td>
                                        <td class="text-center text-primary fs-6">{{ number_format($finalCgpa, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $gradeBadgeColor }} bg-opacity-10 border border-current fs-8 fw-bold">
                                                {{ $overallGrade }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $finalStatus === 'Passed' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }} border fs-8">
                                                {{ $finalStatus }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-5 text-center">
                        <i class="fa-solid fa-clipboard-question fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                        <h6 class="text-muted">No marks records found for this exam.</h6>
                        <p class="text-muted fs-7 mb-0">Results will be displayed here once published by the administration.</p>
                    </div>
                </div>
            @endif
        @else
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-square-poll-vertical fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                    <h6 class="text-muted">Please select an examination to view your report card.</h6>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
