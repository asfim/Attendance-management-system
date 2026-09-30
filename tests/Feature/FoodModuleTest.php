<?php

use App\Models\User;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\Meal;
use App\Models\FoodPlan;
use App\Models\StudentFood;
use App\Models\FoodAttendance;
use App\Services\FoodService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->seed();

    $this->adminUser = User::where('email', 'admin@school.com')->first();
    if (!$this->adminUser) {
        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $this->adminUser = User::factory()->create(['role_id' => $this->adminRole->id]);
    }

    $this->studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student']);
    $this->studentUser = User::factory()->create(['role_id' => $this->studentRole->id]);

    $session = \App\Models\AcademicSession::first();
    $class   = \App\Models\SchoolClass::first();
    $section = \App\Models\Section::first();

    $this->studentProfile = StudentProfile::create([
        'user_id'         => $this->studentUser->id,
        'session_id'      => $session?->id ?? 1,
        'class_id'        => $class?->id ?? 1,
        'section_id'      => $section?->id ?? 1,
        'roll_no'         => '101',
        'admission_no'    => 'ADM-FOOD-001',
        'admission_date'  => '2026-08-01',
        'dob'             => '2010-01-01',
        'gender'          => 'male',
        'status'          => 'active',
        'is_food_enabled' => true,
    ]);
});

test('admin can access food management dashboard', function () {
    $this->actingAs($this->adminUser)
        ->get(route('admin.food.dashboard'))
        ->assertStatus(200)
        ->assertSee('Food Management Dashboard');
});

test('admin can create a meal and a food plan', function () {
    $mealResponse = $this->actingAs($this->adminUser)
        ->post(route('admin.food.meals.store'), [
            'name'   => 'Lunch Special',
            'code'   => 'LN-99',
            'time'   => '01:30 PM',
            'status' => 'active',
        ]);
    
    $mealResponse->assertRedirect(route('admin.food.meals.index'));
    $this->assertDatabaseHas('meals', ['name' => 'Lunch Special']);

    $planResponse = $this->actingAs($this->adminUser)
        ->post(route('admin.food.plans.store'), [
            'name'         => 'Residential High Plan',
            'monthly_fee'  => 3000,
            'billing_days' => 30,
            'status'       => 'active',
        ]);

    $planResponse->assertRedirect(route('admin.food.plans.index'));
    $this->assertDatabaseHas('food_plans', ['name' => 'Residential High Plan']);
});

test('food fee adjustment calculates deduction when non-consumption days >= min days', function () {
    $foodPlan = FoodPlan::create([
        'name'         => 'Standard Plan',
        'monthly_fee'  => 3000,
        'billing_days' => 30,
        'status'       => 'active',
    ]);

    StudentFood::create([
        'student_profile_id' => $this->studentProfile->id,
        'food_plan_id'       => $foodPlan->id,
        'monthly_fee'        => 3000,
        'status'             => 'active',
    ]);

    $meal = Meal::create(['name' => 'Lunch', 'status' => 'active']);

    $foodService = app(FoodService::class);

    // Record 12 days as not_taken
    for ($day = 1; $day <= 12; $day++) {
        $date = Carbon::create(2026, 8, $day)->format('Y-m-d');
        $foodService->markFoodAttendance($this->studentProfile->id, $meal->id, $date, 'not_taken');
    }

    $calculation = $foodService->calculateStudentFoodFee($this->studentProfile->id, 8, 2026);

    // Per day cost = 3000 / 30 = 100.
    // 12 days absent >= 10 min days -> 12 * 100 = 1200 deduction.
    // Final fee = 3000 - 1200 = 1800.
    expect($calculation['monthly_fee'])->toEqual(3000.00);
    expect($calculation['non_consumption_days'])->toEqual(12);
    expect($calculation['per_day_cost'])->toEqual(100.00);
    expect($calculation['calculated_deduction'])->toEqual(1200.00);
    expect($calculation['final_food_fee'])->toEqual(1800.00);
});

test('food fee deduction does not apply when non-consumption days < min days', function () {
    $foodPlan = FoodPlan::create([
        'name'         => 'Standard Plan',
        'monthly_fee'  => 3000,
        'billing_days' => 30,
        'status'       => 'active',
    ]);

    StudentFood::create([
        'student_profile_id' => $this->studentProfile->id,
        'food_plan_id'       => $foodPlan->id,
        'monthly_fee'        => 3000,
        'status'             => 'active',
    ]);

    $meal = Meal::create(['name' => 'Lunch', 'status' => 'active']);
    $foodService = app(FoodService::class);

    // Record only 8 days as not_taken (< 10 min days)
    for ($day = 1; $day <= 8; $day++) {
        $date = Carbon::create(2026, 8, $day)->format('Y-m-d');
        $foodService->markFoodAttendance($this->studentProfile->id, $meal->id, $date, 'not_taken');
    }

    $calculation = $foodService->calculateStudentFoodFee($this->studentProfile->id, 8, 2026);

    expect($calculation['non_consumption_days'])->toEqual(8);
    expect($calculation['calculated_deduction'])->toEqual(0.00);
    expect($calculation['final_food_fee'])->toEqual(3000.00);
});

test('admin can mark all students food attendance in bulk', function () {
    $meal = Meal::create(['name' => 'Dinner', 'status' => 'active']);

    $response = $this->actingAs($this->adminUser)
        ->post(route('admin.food.attendance.mark-all'), [
            'attendance_date' => '2026-08-15',
            'meal_id'         => $meal->id,
            'status'          => 'taken',
        ]);

    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('food_attendances', [
        'student_profile_id' => $this->studentProfile->id,
        'meal_id'            => $meal->id,
        'attendance_date'    => '2026-08-15 00:00:00',
        'status'             => 'taken',
    ]);
});
