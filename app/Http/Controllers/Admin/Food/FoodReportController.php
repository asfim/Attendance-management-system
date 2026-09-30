<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\SchoolClass;
use App\Models\Meal;
use App\Models\FoodAttendance;
use App\Services\FoodService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FoodReportController extends Controller
{
    public function __construct(protected FoodService $foodService) {}

    public function index(Request $request)
    {
        if ($request->filled('month_year')) {
            $parts = explode('-', $request->input('month_year'));
            $year  = (int) ($parts[0] ?? now()->year);
            $month = (int) ($parts[1] ?? now()->month);
        } else {
            $month = (int) $request->input('month', now()->month);
            $year  = (int) $request->input('year', now()->year);
        }
        $tab     = $request->input('tab', 'student');

        $classes = SchoolClass::all();
        $meals   = Meal::all();

        // Student-wise Report Data
        $students = StudentProfile::with(['user', 'schoolClass', 'foodAllocation.foodPlan'])
            ->where('is_food_enabled', true)
            ->get();

        $studentReport = [];
        foreach ($students as $student) {
            $calculation = $this->foodService->calculateStudentFoodFee($student->id, $month, $year);
            $studentReport[] = [
                'student'     => $student,
                'calculation' => $calculation,
            ];
        }

        // Class-wise Report Data
        $classReport = [];
        foreach ($classes as $cls) {
            $clsStudents = $students->where('class_id', $cls->id);
            $totalFee = 0;
            $totalDeduction = 0;

            foreach ($clsStudents as $st) {
                $calc = $this->foodService->calculateStudentFoodFee($st->id, $month, $year);
                $totalFee += $calc['monthly_fee'];
                $totalDeduction += $calc['calculated_deduction'];
            }

            $classReport[] = [
                'class_name'      => $cls->name,
                'student_count'   => $clsStudents->count(),
                'total_fee'       => $totalFee,
                'total_deduction' => $totalDeduction,
                'net_payable'     => max(0, $totalFee - $totalDeduction),
            ];
        }

        // Meal-wise Consumption Report Data
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        $mealReport = [];
        foreach ($meals as $meal) {
            $taken = FoodAttendance::where('meal_id', $meal->id)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'taken')
                ->count();

            $notTaken = FoodAttendance::where('meal_id', $meal->id)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'not_taken')
                ->count();

            $mealReport[] = [
                'meal_name' => $meal->name,
                'taken'     => $taken,
                'not_taken' => $notTaken,
            ];
        }

        return view('admin.food.reports.index', compact(
            'month', 'year', 'tab', 'classes', 'meals',
            'studentReport', 'classReport', 'mealReport'
        ));
    }
}
