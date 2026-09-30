<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\StudentFood;
use App\Models\FoodPlan;
use App\Models\FoodAttendance;
use App\Models\FoodFeeAdjustment;
use App\Models\FoodSetting;
use App\Models\FeeCategory;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FoodService
{
    /**
     * Get or initialize Food Settings
     */
    public function getSettings(): array
    {
        return [
            'food_fee_adjustment_enabled' => (bool) FoodSetting::getByKey('food_fee_adjustment_enabled', true),
            'min_non_consumption_days'   => (int) FoodSetting::getByKey('min_non_consumption_days', 10),
            'billing_days'                => (int) FoodSetting::getByKey('billing_days', 30),
            'calculation_method'          => FoodSetting::getByKey('calculation_method', 'food_day'), // food_day, individual_meal
            'allow_manual_adjustment'     => (bool) FoodSetting::getByKey('allow_manual_adjustment', true),
            'auto_generate_monthly_fee'   => (bool) FoodSetting::getByKey('auto_generate_monthly_fee', true),
        ];
    }

    /**
     * Update Food Settings
     */
    public function updateSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            FoodSetting::setKey($key, is_bool($value) ? ($value ? '1' : '0') : $value);
        }
    }

    /**
     * Calculate monthly food fee and non-consumption deduction for a student
     */
    public function calculateStudentFoodFee(int $studentProfileId, int $month, int $year): array
    {
        $student = StudentProfile::with(['foodAllocation.foodPlan'])->find($studentProfileId);
        if (!$student || !$student->is_food_enabled) {
            return [
                'monthly_fee'          => 0.00,
                'non_consumption_days' => 0,
                'per_day_cost'         => 0.00,
                'calculated_deduction' => 0.00,
                'manual_adjustment'    => 0.00,
                'final_food_fee'       => 0.00,
            ];
        }

        $foodAlloc = $student->foodAllocation;
        $monthlyFee = $foodAlloc ? (float) $foodAlloc->monthly_fee : 0.00;
        
        $settings = $this->getSettings();
        $billingDays = max(1, $settings['billing_days']);
        $perDayCost = round($monthlyFee / $billingDays, 2);

        // Count non-consumption days for this month
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate   = $startDate->copy()->endOfMonth();

        if ($settings['calculation_method'] === 'individual_meal') {
            // Count total not_taken meal slots
            $notTakenMeals = FoodAttendance::where('student_profile_id', $studentProfileId)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'not_taken')
                ->count();
            // Assuming 3 meals per day
            $nonConsumptionDays = (int) round($notTakenMeals / 3);
        } else {
            // Count distinct days where at least 1 meal was marked not_taken or whole day not_taken
            $nonConsumptionDays = FoodAttendance::where('student_profile_id', $studentProfileId)
                ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->where('status', 'not_taken')
                ->select('attendance_date')
                ->distinct()
                ->count('attendance_date');
        }

        $calculatedDeduction = 0.00;
        if ($settings['food_fee_adjustment_enabled'] && $nonConsumptionDays >= $settings['min_non_consumption_days']) {
            $calculatedDeduction = round($nonConsumptionDays * $perDayCost, 2);
        }

        // Fetch existing adjustment record for manual adjustment overlay
        $existingAdjustment = FoodFeeAdjustment::where('student_profile_id', $studentProfileId)
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        $manualAdjustment = $existingAdjustment ? (float) $existingAdjustment->manual_adjustment : 0.00;

        $finalFoodFee = max(0.00, round($monthlyFee - $calculatedDeduction - $manualAdjustment, 2));

        // Save / update adjustment log record
        FoodFeeAdjustment::updateOrCreate(
            [
                'student_profile_id' => $studentProfileId,
                'month'              => $month,
                'year'               => $year,
            ],
            [
                'non_consumption_days' => $nonConsumptionDays,
                'per_day_cost'         => $perDayCost,
                'calculated_deduction' => $calculatedDeduction,
                'manual_adjustment'    => $manualAdjustment,
                'final_food_fee'       => $finalFoodFee,
            ]
        );

        return [
            'monthly_fee'          => $monthlyFee,
            'non_consumption_days' => $nonConsumptionDays,
            'per_day_cost'         => $perDayCost,
            'calculated_deduction' => $calculatedDeduction,
            'manual_adjustment'    => $manualAdjustment,
            'final_food_fee'       => $finalFoodFee,
        ];
    }

    /**
     * Generate or update Food Fee Invoices for active food students
     */
    public function generateMonthlyFoodFees(int $month, int $year): int
    {
        $foodCategory = FeeCategory::firstOrCreate(
            ['name' => 'Food Fee'],
            ['type' => 'monthly', 'installments_count' => 12]
        );

        $students = StudentProfile::where('is_food_enabled', true)
            ->where('status', 'active')
            ->get();

        $generatedCount = 0;
        $monthName = Carbon::create($year, $month, 1)->format('F Y');

        foreach ($students as $student) {
            DB::transaction(function () use ($student, $month, $year, $monthName, $foodCategory, &$generatedCount) {
                $calculation = $this->calculateStudentFoodFee($student->id, $month, $year);

                $issueDate = Carbon::create($year, $month, 1)->format('Y-m-d');
                $dueDate   = Carbon::create($year, $month, 10)->format('Y-m-d');

                // Check if an invoice with Food Fee already exists for this student & month
                $existingItem = InvoiceItem::where('fee_category_id', $foodCategory->id)
                    ->where('installment_name', $monthName)
                    ->whereHas('invoice', fn($q) => $q->where('student_profile_id', $student->id))
                    ->first();

                if ($existingItem) {
                    $invoice = $existingItem->invoice;
                    $existingItem->amount = $calculation['final_food_fee'];
                    $existingItem->save();

                    // Recalculate invoice grand total
                    $invoice->subtotal = $invoice->items()->sum('amount');
                    $invoice->grand_total = max(0, $invoice->subtotal - $invoice->discount_amount);
                    $invoice->save();
                } else {
                    $invoiceNo = 'INV-FOOD-' . $student->id . '-' . sprintf('%02d%d', $month, $year);
                    
                    $invoice = Invoice::create([
                        'student_profile_id' => $student->id,
                        'invoice_number'     => $invoiceNo,
                        'issue_date'         => $issueDate,
                        'due_date'           => $dueDate,
                        'subtotal'           => $calculation['final_food_fee'],
                        'discount_amount'    => 0.00,
                        'grand_total'        => $calculation['final_food_fee'],
                        'paid_amount'        => 0.00,
                        'status'             => 'unpaid',
                    ]);

                    InvoiceItem::create([
                        'invoice_id'       => $invoice->id,
                        'fee_category_id'  => $foodCategory->id,
                        'amount'           => $calculation['final_food_fee'],
                        'installment_name' => $monthName,
                        'due_date'         => $dueDate,
                        'status'           => 'unpaid',
                        'paid_amount'      => 0.00,
                    ]);

                    $generatedCount++;
                }
            });
        }

        return $generatedCount;
    }

    /**
     * Mark or update student food attendance
     */
    public function markFoodAttendance(int $studentProfileId, ?int $mealId, string $date, string $status, ?string $remarks = null): FoodAttendance
    {
        $attendance = FoodAttendance::updateOrCreate(
            [
                'student_profile_id' => $studentProfileId,
                'meal_id'            => $mealId,
                'attendance_date'    => $date,
            ],
            [
                'status'  => $status,
                'remarks' => $remarks,
            ]
        );

        // Recalculate food fee adjustment for that month
        $carbonDate = Carbon::parse($date);
        $this->calculateStudentFoodFee($studentProfileId, $carbonDate->month, $carbonDate->year);

        return $attendance;
    }
}
