<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\Meal;
use App\Models\FoodAttendance;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Services\FoodService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FoodAttendanceController extends Controller
{
    public function __construct(protected FoodService $foodService) {}

    public function index(Request $request)
    {
        $classes  = SchoolClass::all();
        $sections = Section::all();
        $meals    = Meal::where('status', 'active')->get();

        if ($request->filled('month_year')) {
            $parts = explode('-', $request->input('month_year'));
            $year  = (int) ($parts[0] ?? now()->year);
            $month = (int) ($parts[1] ?? now()->month);
        } else {
            $month = (int) $request->input('month', now()->month);
            $year  = (int) $request->input('year', now()->year);
        }
        $classId  = $request->input('class_id');
        $sectionId = $request->input('section_id');
        $mealId   = $request->input('meal_id', $meals->first()?->id);

        $query = StudentProfile::with(['user', 'foodAllocation.foodPlan'])
            ->where('is_food_enabled', true)
            ->where('status', 'active');

        if ($classId) {
            $query->where('class_id', $classId);
        }
        if ($sectionId) {
            $query->where('section_id', $sectionId);
        }

        $students = $query->get();

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        // Load attendance matrix for the month
        $attendances = FoodAttendance::whereIn('student_profile_id', $students->pluck('id'))
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $startDate->copy()->endOfMonth()->format('Y-m-d')])
            ->when($mealId, fn($q) => $q->where('meal_id', $mealId))
            ->get()
            ->groupBy(fn($a) => $a->student_profile_id . '_' . $a->attendance_date->format('Y-m-d'));

        return view('admin.food.attendance.index', compact(
            'classes', 'sections', 'meals',
            'month', 'year', 'classId', 'sectionId', 'mealId',
            'students', 'startDate', 'daysInMonth', 'attendances'
        ));
    }

    public function mark(Request $request)
    {
        $request->validate([
            'student_profile_id' => 'required|exists:student_profiles,id',
            'meal_id'            => 'nullable|exists:meals,id',
            'attendance_date'    => 'required|date',
            'status'             => 'required|in:taken,not_taken,leave,holiday,not_applicable',
        ]);

        $attendance = $this->foodService->markFoodAttendance(
            $request->student_profile_id,
            $request->meal_id,
            $request->attendance_date,
            $request->status,
            $request->remarks
        );

        return response()->json([
            'success' => true,
            'status'  => $attendance->status,
            'message' => 'Food attendance recorded!',
        ]);
    }

    public function markAll(Request $request)
    {
        $request->validate([
            'attendance_date' => 'nullable|date',
            'month'           => 'nullable|integer',
            'year'            => 'nullable|integer',
            'mark_all_days'   => 'nullable|boolean',
            'meal_id'         => 'nullable',
            'status'          => 'required|in:taken,not_taken,leave,holiday,not_applicable',
            'class_id'        => 'nullable',
            'section_id'      => 'nullable',
        ]);

        $status = $request->status;
        $mealId = $request->filled('meal_id') ? $request->meal_id : null;
        if (!$mealId) {
            $mealId = \App\Models\Meal::where('status', 'active')->first()?->id;
        }

        $query = StudentProfile::where('is_food_enabled', true)->where('status', 'active');
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        $students = $query->get();

        if ($request->boolean('mark_all_days') && $request->filled('month') && $request->filled('year')) {
            $month = (int) $request->month;
            $year  = (int) $request->year;
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $daysInMonth = $startDate->daysInMonth;

            foreach ($students as $student) {
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $dateStr = Carbon::create($year, $month, $day)->format('Y-m-d');
                    $this->foodService->markFoodAttendance($student->id, $mealId, $dateStr, $status);
                }
            }

            return response()->json([
                'success' => true,
                'count'   => $students->count() * $daysInMonth,
                'message' => "All {$students->count()} student(s) marked as " . ucfirst($status) . " for all days in " . $startDate->format('F Y') . "!",
            ]);
        }

        $date = $request->attendance_date ?? date('Y-m-d');
        foreach ($students as $student) {
            $this->foodService->markFoodAttendance($student->id, $mealId, $date, $status);
        }

        return response()->json([
            'success' => true,
            'count'   => $students->count(),
            'message' => "All {$students->count()} student(s) marked as " . ucfirst($status) . " for {$date}!",
        ]);
    }
}
