<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FoodAllocationController extends Controller
{
    public function index()
    {
        $allocations = \App\Models\StudentFood::with(['studentProfile.user', 'studentProfile.schoolClass', 'studentProfile.section', 'foodPlan'])->latest()->get();
        
        // For the modal
        $students = \App\Models\StudentProfile::with(['user', 'schoolClass'])
            ->where('status', 'active')
            ->where('is_food_enabled', false)
            ->get();
        $foodPlans = \App\Models\FoodPlan::where('status', 'active')->get();

        return view('admin.food.allocations.index', compact('allocations', 'students', 'foodPlans'));
    }

    public function allocate(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'student_profile_ids' => 'required|array',
            'student_profile_ids.*' => 'exists:student_profiles,id',
            'food_plan_id' => 'required|exists:food_plans,id',
            'start_date' => 'required|date',
        ]);

        $foodPlan = \App\Models\FoodPlan::findOrFail($request->food_plan_id);

        foreach ($request->student_profile_ids as $studentId) {
            $studentProfile = \App\Models\StudentProfile::findOrFail($studentId);
            
            // Deactivate any existing food allocations
            \App\Models\StudentFood::where('student_profile_id', $studentId)
                ->where('status', 'active')
                ->update([
                    'status' => 'inactive',
                    'end_date' => now()->subDay()->format('Y-m-d')
                ]);

            // Create new allocation
            \App\Models\StudentFood::create([
                'student_profile_id' => $studentProfile->id,
                'food_plan_id'       => $foodPlan->id,
                'monthly_fee'        => $foodPlan->monthly_fee,
                'start_date'         => $request->start_date,
                'status'             => 'active',
            ]);

            // Enable food for student
            $studentProfile->update(['is_food_enabled' => true]);
        }

        // Generate fees for current month if applicable
        app(\App\Services\FoodService::class)->generateMonthlyFoodFees(now()->month, now()->year);

        return redirect()->back()->with('success', 'Food allocated successfully to ' . count($request->student_profile_ids) . ' student(s).');
    }

    public function update(\Illuminate\Http\Request $request, $id)
    {
        $allocation = \App\Models\StudentFood::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);
        
        $allocation->update([
            'status' => $request->status,
        ]);
        
        if ($request->status == 'active') {
            $allocation->studentProfile->update(['is_food_enabled' => true]);
        }
        
        return redirect()->back()->with('success', 'Food allocation status updated successfully.');
    }

    public function release($id)
    {
        $allocation = \App\Models\StudentFood::findOrFail($id);
        $allocation->update([
            'status' => 'inactive',
            'end_date' => now()->format('Y-m-d')
        ]);
        
        // Disable food for student if no other active plans
        $hasActive = \App\Models\StudentFood::where('student_profile_id', $allocation->student_profile_id)
            ->where('status', 'active')
            ->exists();
            
        if (!$hasActive) {
            $allocation->studentProfile->update(['is_food_enabled' => false]);
        }
        
        return redirect()->back()->with('success', 'Food allocation cancelled/released successfully.');
    }
}
