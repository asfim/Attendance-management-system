<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Meal;
use App\Models\FoodPlan;
use App\Models\StudentProfile;
use App\Models\StudentFood;
use App\Models\FoodAttendance;
use App\Services\FoodService;
use Carbon\Carbon;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Configured Meals
        $meals = [
            [
                'name'        => 'Breakfast',
                'code'        => 'BF-01',
                'time'        => '08:00 AM',
                'description' => 'Morning fresh breakfast & tea',
                'status'      => 'active',
            ],
            [
                'name'        => 'Lunch',
                'code'        => 'LN-01',
                'time'        => '01:30 PM',
                'description' => 'Full lunch meal (Rice, Curry, Fish/Meat)',
                'status'      => 'active',
            ],
            [
                'name'        => 'Afternoon Snack',
                'code'        => 'SN-01',
                'time'        => '04:30 PM',
                'description' => 'Afternoon light snacks & milk',
                'status'      => 'active',
            ],
            [
                'name'        => 'Dinner',
                'code'        => 'DN-01',
                'time'        => '08:30 PM',
                'description' => 'Night dinner meal',
                'status'      => 'active',
            ],
        ];

        $mealModels = [];
        foreach ($meals as $m) {
            $mealModels[] = Meal::updateOrCreate(['code' => $m['code']], $m);
        }

        // 2. Create Food Plans
        $plan1 = FoodPlan::updateOrCreate(
            ['name' => 'Residential Full Plan'],
            [
                'student_category' => 'Residential',
                'monthly_fee'      => 3000.00,
                'billing_days'     => 30,
                'status'           => 'active',
            ]
        );

        $plan2 = FoodPlan::updateOrCreate(
            ['name' => 'Day Care Lunch Plan'],
            [
                'student_category' => 'Non-Residential',
                'monthly_fee'      => 1500.00,
                'billing_days'     => 30,
                'status'           => 'active',
            ]
        );

        $plan3 = FoodPlan::updateOrCreate(
            ['name' => 'Special Diet Plan'],
            [
                'student_category' => 'Special',
                'monthly_fee'      => 2500.00,
                'billing_days'     => 30,
                'status'           => 'active',
            ]
        );

        // 3. Assign Food Plans to Students
        $students = StudentProfile::take(10)->get();
        if ($students->isEmpty()) {
            return;
        }

        $foodService = app(FoodService::class);

        foreach ($students as $index => $student) {
            $student->update(['is_food_enabled' => true]);

            $chosenPlan = match($index % 3) {
                0 => $plan1,
                1 => $plan2,
                default => $plan3,
            };

            StudentFood::updateOrCreate(
                ['student_profile_id' => $student->id],
                [
                    'food_plan_id' => $chosenPlan->id,
                    'monthly_fee'  => $chosenPlan->monthly_fee,
                    'start_date'   => '2026-08-01',
                    'status'       => 'active',
                ]
            );

            // 4. Generate Fake Attendance for August 2026 (days 1 to 10)
            // For student #1, mark 12 absent days to demonstrate > 10 min days auto deduction (৳1,200)
            $absentCount = ($index === 0) ? 12 : ($index % 4);

            for ($day = 1; $day <= 10; $day++) {
                $dateStr = Carbon::create(2026, 8, $day)->format('Y-m-d');
                $isAbsentDay = ($day <= $absentCount);
                $status = $isAbsentDay ? 'not_taken' : 'taken';

                foreach ($mealModels as $meal) {
                    FoodAttendance::updateOrCreate(
                        [
                            'student_profile_id' => $student->id,
                            'meal_id'            => $meal->id,
                            'attendance_date'    => $dateStr,
                        ],
                        [
                            'status' => $status,
                        ]
                    );
                }
            }
        }

        // 5. Generate Monthly Food Fee Invoices & Adjustments
        $foodService->generateMonthlyFoodFees(8, 2026);
    }
}
