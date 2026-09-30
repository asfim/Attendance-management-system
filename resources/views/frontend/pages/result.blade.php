@extends('frontend.layouts.app')

@section('content')
<!-- Page Header -->
<div class="py-5 bg-primary text-white text-center">
    <div class="container">
        <h1 class="display-5 fw-bold">ফলাফল</h1>
        <p class="lead mb-0">শিক্ষার্থীদের পরীক্ষার ফলাফল</p>
    </div>
</div>

<!-- ================= RESULT ================= -->
<section class="section bg-light py-5">
    <div class="container">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase tracking-wider">Academic Result</span>
            <h2 class="display-6 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">পরীক্ষার ফলাফল</h2>
            <p class="text-muted">শ্রেণী, পরীক্ষা এবং Roll Number ব্যবহার করে আপনার ফলাফল খুঁজে দেখুন।</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm p-4 p-md-5 mb-5 rounded-4">
                    <form action="{{ url('/result') }}" method="GET">
                        <div class="row g-4 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary">শ্রেণী (Class)</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">শ্রেণী নির্বাচন করুন</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-secondary">পরীক্ষা (Exam)</label>
                                <select class="form-select" name="exam_id" required>
                                    <option value="">পরীক্ষা নির্বাচন করুন</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}" {{ request('exam_id') == $exam->id ? 'selected' : '' }}>{{ $exam->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-secondary">রোল (Roll Number)</label>
                                <input type="text" name="roll" class="form-control" placeholder="Roll Number" value="{{ request('roll') }}" required>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100 fw-bold">
                                    <i class="bi bi-search me-1"></i> খুঁজুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                @if(isset($error))
                    <div class="alert alert-danger text-center rounded-3 shadow-sm border-0 py-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $error }}
                    </div>
                @endif

                @if(isset($result) && $result->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="card-header bg-white p-4 border-bottom">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-1 fw-bold text-dark">{{ $student->user->name ?? 'শিক্ষার্থী' }}</h4>
                                    <p class="text-muted mb-0">
                                        <strong>রোল:</strong> {{ request('roll') }} &nbsp;|&nbsp; 
                                        <strong>শ্রেণী:</strong> {{ $classes->firstWhere('id', request('class_id'))->name ?? '' }}
                                    </p>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0 d-flex flex-column align-items-md-end gap-2">
                                    <span class="badge bg-primary px-3 py-2 fs-6 rounded-3 text-wrap shadow-sm text-center">
                                        {{ $exams->firstWhere('id', request('exam_id'))->name ?? '' }}
                                    </span>
                                    <a href="{{ route('result.download', request()->all()) }}" class="btn btn-danger text-white rounded-pill fw-bold px-3 py-2 text-nowrap shadow-sm w-auto">
                                        <i class="bi bi-file-earmark-pdf-fill"></i> Download PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-center align-middle">
                                    <thead class="table-light text-secondary">
                                        <tr>
                                            <th class="py-3 text-start ps-4">বিষয় (Subject)</th>
                                            <th class="py-3">পূর্ণমান (Full Marks)</th>
                                            <th class="py-3">প্রাপ্ত নম্বর (Obtained)</th>
                                            <th class="py-3">গ্রেড (Grade)</th>
                                            <th class="py-3 pe-4">জিপিএ (GPA)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalMarks = 0;
                                            $obtainedMarks = 0;
                                            $totalGpaPoints = 0;
                                            $subjectCount = 0;
                                            $hasFailed = false;
                                        @endphp
                                        @foreach($result as $mark)
                                            @php
                                                $fm = $mark->examSchedule->max_marks ?? 100;
                                                $totalMarks += $fm;
                                                $obtainedMarks += $mark->marks_obtained ?? 0;
                                                
                                                // Grade and GPA logic
                                                $percentage = $fm > 0 ? (($mark->marks_obtained ?? 0) / $fm) * 100 : 0;
                                                $grade = 'F'; $badge = 'danger'; $gpa = 0.00;
                                                if($percentage >= 80) { $grade = 'A+'; $badge = 'success'; $gpa = 5.00; }
                                                elseif($percentage >= 70) { $grade = 'A'; $badge = 'success'; $gpa = 4.00; }
                                                elseif($percentage >= 60) { $grade = 'A-'; $badge = 'primary'; $gpa = 3.50; }
                                                elseif($percentage >= 50) { $grade = 'B'; $badge = 'info'; $gpa = 3.00; }
                                                elseif($percentage >= 40) { $grade = 'C'; $badge = 'warning'; $gpa = 2.00; }
                                                elseif($percentage >= 33) { $grade = 'D'; $badge = 'secondary'; $gpa = 1.00; }
                                                
                                                if($grade == 'F') { $hasFailed = true; }
                                                $totalGpaPoints += $gpa;
                                                $subjectCount++;
                                            @endphp
                                            <tr>
                                                <td class="text-start ps-4 fw-semibold">{{ $mark->examSchedule->subject->name ?? 'Unknown' }}</td>
                                                <td>{{ (float)$fm }}</td>
                                                <td class="fw-bold">{{ (float)($mark->marks_obtained ?? 0) }}</td>
                                                <td><span class="badge bg-{{ $badge }}">{{ $grade }}</span></td>
                                                <td class="pe-4 fw-bold text-{{ $badge }}">{{ number_format($gpa, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light fw-bold">
                                        <tr>
                                            <td class="text-start ps-4 py-3 text-uppercase">Total</td>
                                            <td>{{ $totalMarks }}</td>
                                            <td class="text-primary fs-5">{{ $obtainedMarks }}</td>
                                            @php
                                                if ($subjectCount > 0) {
                                                    $finalGpa = $hasFailed ? 0.00 : ($totalGpaPoints / $subjectCount);
                                                } else {
                                                    $finalGpa = 0.00;
                                                }
                                                
                                                $finalGrade = 'F'; $finalBadge = 'danger';
                                                
                                                if ($finalGpa >= 5.00) { $finalGrade = 'A+'; $finalBadge = 'success'; }
                                                elseif ($finalGpa >= 4.00) { $finalGrade = 'A'; $finalBadge = 'success'; }
                                                elseif ($finalGpa >= 3.50) { $finalGrade = 'A-'; $finalBadge = 'primary'; }
                                                elseif ($finalGpa >= 3.00) { $finalGrade = 'B'; $finalBadge = 'info'; }
                                                elseif ($finalGpa >= 2.00) { $finalGrade = 'C'; $finalBadge = 'warning'; }
                                                elseif ($finalGpa >= 1.00) { $finalGrade = 'D'; $finalBadge = 'secondary'; }
                                                if ($hasFailed) {
                                                    $finalGrade = 'F';
                                                    $finalBadge = 'danger';
                                                }
                                            @endphp
                                            <td>
                                                <span class="badge bg-{{ $finalBadge }} fs-6">{{ $finalGrade }}</span>
                                            </td>
                                            <td class="pe-4 text-{{ $finalBadge }} fs-5">
                                                {{ number_format($finalGpa, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
