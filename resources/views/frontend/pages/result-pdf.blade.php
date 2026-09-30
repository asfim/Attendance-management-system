<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Academic Result</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #333; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { background-color: #fff; padding: 30px; border-radius: 8px; border-top: 6px solid #0d6efd; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #0d6efd; padding-bottom: 15px; background-color: #f8f9fa; border-radius: 5px; padding-top: 15px; }
        .header h1 { margin: 0; color: #004085; font-size: 28px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .header p { margin: 5px 0 0; color: #0056b3; font-weight: bold; font-size: 16px; }
        .student-info { margin-bottom: 20px; background-color: #e9ecef; padding: 15px; border-radius: 5px; border-left: 4px solid #17a2b8; }
        .student-info table { width: 100%; }
        .student-info td { padding: 8px; color: #495057; font-size: 15px; }
        .result-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .result-table th, .result-table td { border: 1px solid #dee2e6; padding: 12px; text-align: center; }
        .result-table th { background-color: #0d6efd; color: #fff; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        .result-table tr:nth-child(even) { background-color: #f8f9fa; }
        .result-table td.text-left { text-align: left; font-weight: 500; }
        .total-row { font-weight: bold; background-color: #d1e7dd !important; color: #0f5132 !important; font-size: 15px; }
        
        .grade-A-plus { color: #198754; font-weight: bold; }
        .grade-A { color: #20c997; font-weight: bold; }
        .grade-A-minus { color: #0dcaf0; font-weight: bold; }
        .grade-B { color: #0d6efd; font-weight: bold; }
        .grade-C { color: #6610f2; font-weight: bold; }
        .grade-D { color: #fd7e14; font-weight: bold; }
        .grade-F { color: #dc3545; font-weight: bold; }

        .footer { text-align: center; margin-top: 50px; font-size: 12px; color: #6c757d; border-top: 1px solid #dee2e6; padding-top: 15px; }
        .signature-table { margin-top: 60px; width: 100%; border-collapse: collapse; }
        .signature-table td { width: 33%; text-align: center; vertical-align: bottom; }
        .signature-line { border-top: 2px dashed #6c757d; margin: 0 auto; width: 80%; padding-top: 8px; font-weight: bold; color: #495057; }
        
        .logo-container { margin-bottom: 15px; text-align: center; }
        .default-logo { width: 80px; height: 80px; background-color: #0d6efd; color: white; border-radius: 40px; display: inline-block; font-size: 30px; font-weight: bold; text-transform: uppercase; margin: 0 auto 10px auto; line-height: 80px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        @php
            $logo = \App\Models\Setting::get('site_logo');
            $schoolName = \App\Models\Setting::get('school_name') ?? config('app.name', 'School Management System');
            $logoSrc = null;
            if($logo) {
                $path = public_path($logo);
                if (!file_exists($path) && !str_starts_with($logo, '/')) {
                    $path = public_path('/' . $logo);
                }
                if(file_exists($path) && !is_dir($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $logoSrc = 'data:image/' . $type . ';base64,' . base64_encode($data);
                }
            }
        @endphp
        
        <div class="header">
            <div class="logo-container">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="School Logo" style="max-height: 80px; margin-bottom: 10px;">
                @else
                    <div class="default-logo">{{ substr($schoolName, 0, 1) }}</div>
                @endif
            </div>
            <h1>{{ $schoolName }}</h1>
            <p>Academic Result Statement</p>
        </div>

        <div class="student-info">
            <table>
                <tr>
                    <td><strong>Student Name:</strong> {{ $student->user->name ?? 'Unknown' }}</td>
                    <td><strong>Roll Number:</strong> {{ $request->roll }}</td>
                </tr>
                <tr>
                    <td><strong>Class:</strong> {{ $schoolClass->name ?? '' }}</td>
                    <td><strong>Examination:</strong> {{ $examType->name ?? '' }}</td>
                </tr>
            </table>
        </div>

        <table class="result-table">
            <thead>
                <tr>
                    <th class="text-left">Subject</th>
                    <th>Full Marks</th>
                    <th>Obtained Marks</th>
                    <th>Grade</th>
                    <th>GPA</th>
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
                        
                        $percentage = $fm > 0 ? (($mark->marks_obtained ?? 0) / $fm) * 100 : 0;
                        $grade = 'F';
                        $gpa = 0.00;
                        $gradeClass = 'grade-F';
                        if($percentage >= 80) { $grade = 'A+'; $gpa = 5.00; $gradeClass = 'grade-A-plus'; }
                        elseif($percentage >= 70) { $grade = 'A'; $gpa = 4.00; $gradeClass = 'grade-A'; }
                        elseif($percentage >= 60) { $grade = 'A-'; $gpa = 3.50; $gradeClass = 'grade-A-minus'; }
                        elseif($percentage >= 50) { $grade = 'B'; $gpa = 3.00; $gradeClass = 'grade-B'; }
                        elseif($percentage >= 40) { $grade = 'C'; $gpa = 2.00; $gradeClass = 'grade-C'; }
                        elseif($percentage >= 33) { $grade = 'D'; $gpa = 1.00; $gradeClass = 'grade-D'; }
                        
                        if($grade == 'F') { $hasFailed = true; }
                        $totalGpaPoints += $gpa;
                        $subjectCount++;
                    @endphp
                    <tr>
                        <td class="text-left">{{ $mark->examSchedule->subject->name ?? 'Unknown' }}</td>
                        <td>{{ (float)$fm }}</td>
                        <td>{{ (float)($mark->marks_obtained ?? 0) }}</td>
                        <td><span class="{{ $gradeClass }}">{{ $grade }}</span></td>
                        <td><span class="{{ $gradeClass }}">{{ number_format($gpa, 2) }}</span></td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td class="text-left">TOTAL</td>
                    <td>{{ $totalMarks }}</td>
                    <td>{{ $obtainedMarks }}</td>
                    @php
                        if ($subjectCount > 0) {
                            $finalGpa = $hasFailed ? 0.00 : ($totalGpaPoints / $subjectCount);
                        } else {
                            $finalGpa = 0.00;
                        }
                        
                        $finalGrade = 'F';
                        $finalGradeClass = 'grade-F';
                        
                        if ($finalGpa >= 5.00) { $finalGrade = 'A+'; $finalGradeClass = 'grade-A-plus'; }
                        elseif ($finalGpa >= 4.00) { $finalGrade = 'A'; $finalGradeClass = 'grade-A'; }
                        elseif ($finalGpa >= 3.50) { $finalGrade = 'A-'; $finalGradeClass = 'grade-A-minus'; }
                        elseif ($finalGpa >= 3.00) { $finalGrade = 'B'; $finalGradeClass = 'grade-B'; }
                        elseif ($finalGpa >= 2.00) { $finalGrade = 'C'; $finalGradeClass = 'grade-C'; }
                        elseif ($finalGpa >= 1.00) { $finalGrade = 'D'; $finalGradeClass = 'grade-D'; }
                        if ($hasFailed) {
                            $finalGrade = 'F';
                            $finalGradeClass = 'grade-F';
                        }
                    @endphp
                    <td>
                        <span class="{{ $finalGradeClass }}">{{ $finalGrade }}</span>
                    </td>
                    <td>
                        <span class="{{ $finalGradeClass }}">{{ number_format($finalGpa, 2) }}</span>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="signature-table">
            <tr>
                <td><div class="signature-line">Class Teacher</div></td>
                <td><div class="signature-line">Exam Controller</div></td>
                <td><div class="signature-line">Principal</div></td>
            </tr>
        </table>

        <div class="footer">
            Generated on {{ date('d M, Y h:i A') }} | This is a computer-generated document.
        </div>
    </div>
</body>
</html>
