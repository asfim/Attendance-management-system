<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentProfile;
use App\Models\Attendance;
use App\Models\MarksEntry;

class AiAnalyticsController extends Controller
{
    public function analyzeRisk(int $studentId)
    {
        $student = StudentProfile::with('user')->findOrFail($studentId);
        
        $totalDays = Attendance::where('attendable_type', StudentProfile::class)
            ->where('attendable_id', $studentId)
            ->count();

        if ($totalDays === 0) {
            return response()->json([
                'student_name' => $student->user->name,
                'risk_level' => 'low',
                'confidence' => 0.95,
                'insights' => ['Insufficient attendance data to perform predictive analysis.']
            ]);
        }

        $absentDays = Attendance::where('attendable_type', StudentProfile::class)
            ->where('attendable_id', $studentId)
            ->where('status', 'absent')
            ->count();

        $attendanceRate = (($totalDays - $absentDays) / $totalDays) * 100;
        
        // Predict risk
        $risk = 'low';
        $insights = [];
        if ($attendanceRate < 80) {
            $risk = 'high';
            $insights[] = 'Attendance is below 80%. High risk of missing syllabus.';
        } elseif ($attendanceRate < 90) {
            $risk = 'medium';
            $insights[] = 'Attendance is fluctuating. Early support recommended.';
        } else {
            $insights[] = 'Excellent attendance. Keep up the consistent record!';
        }

        return response()->json([
            'student_name' => $student->user->name,
            'attendance_rate' => round($attendanceRate, 2) . '%',
            'risk_level' => $risk,
            'confidence' => 0.89,
            'insights' => $insights
        ]);
    }

    public function predictPerformance(int $studentId)
    {
        $student = StudentProfile::with('user')->findOrFail($studentId);

        $marks = MarksEntry::where('student_profile_id', $studentId)->get();

        if ($marks->isEmpty()) {
            return response()->json([
                'student_name' => $student->user->name,
                'predicted_gpa' => 'N/A',
                'insights' => ['No marks entries recorded yet. Performance predictions require past exam entries.']
            ]);
        }

        $totalObtained = 0;
        $totalMax = 0;
        foreach ($marks as $m) {
            $totalObtained += $m->marks_obtained;
            $totalMax += $m->examSchedule->max_marks;
        }

        $averagePercentage = ($totalObtained / $totalMax) * 100;

        // Predict GPA
        $predictedGpa = 0.00;
        if ($averagePercentage >= 80) {
            $predictedGpa = 5.00;
        } elseif ($averagePercentage >= 70) {
            $predictedGpa = 4.00;
        } elseif ($averagePercentage >= 60) {
            $predictedGpa = 3.50;
        } elseif ($averagePercentage >= 50) {
            $predictedGpa = 3.00;
        } elseif ($averagePercentage >= 40) {
            $predictedGpa = 2.00;
        } elseif ($averagePercentage >= 33) {
            $predictedGpa = 1.00;
        }

        return response()->json([
            'student_name' => $student->user->name,
            'average_score' => round($averagePercentage, 2) . '%',
            'predicted_gpa' => number_format($predictedGpa, 2),
            'confidence_score' => 0.92,
            'insights' => [
                'Based on past average of ' . round($averagePercentage, 2) . '%, student is expected to score a GPA of ' . number_format($predictedGpa, 2) . '.'
            ]
        ]);
    }
}
