<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\Meal;
use App\Models\FoodAttendance;
use App\Models\FoodFeeAdjustment;
use App\Models\InvoiceItem;
use App\Models\FeeCategory;
use App\Services\FoodService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FoodDashboardController extends Controller
{
    public function __construct(protected FoodService $foodService) {}

    public function index(Request $request)
    {
        $today = Carbon::today()->format('Y-m-d');
        $currentMonth = Carbon::now()->month;
        $currentYear  = Carbon::now()->year;

        $totalFoodStudents = StudentProfile::where('is_food_enabled', true)
            ->where('status', 'active')
            ->count();

        // Today's Food Attendance Metrics
        $todayTaken = FoodAttendance::where('attendance_date', $today)
            ->where('status', 'taken')
            ->count();

        $todayNotTaken = FoodAttendance::where('attendance_date', $today)
            ->where('status', 'not_taken')
            ->count();

        // Food Fee Category
        $foodCategory = FeeCategory::where('name', 'Food Fee')->first();

        $monthlyRevenue = 0;
        $monthlyDue = 0;

        if ($foodCategory) {
            $monthName = Carbon::now()->format('F Y');
            $foodItems = InvoiceItem::where('fee_category_id', $foodCategory->id)
                ->where('installment_name', $monthName)
                ->get();

            $monthlyRevenue = $foodItems->sum('paid_amount');
            $monthlyDue = $foodItems->sum(fn($i) => max(0, $i->amount - $i->paid_amount));
        }

        $totalAdjustments = FoodFeeAdjustment::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->sum('calculated_deduction');

        // Recent meals
        $activeMeals = Meal::where('status', 'active')->get();

        return view('admin.food.dashboard', compact(
            'totalFoodStudents', 'todayTaken', 'todayNotTaken',
            'monthlyRevenue', 'monthlyDue', 'totalAdjustments',
            'activeMeals'
        ));
    }
}
