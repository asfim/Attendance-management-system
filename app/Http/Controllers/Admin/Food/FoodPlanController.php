<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\FoodPlan;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class FoodPlanController extends Controller
{
    public function index()
    {
        $plans    = FoodPlan::with(['academicSession', 'schoolClass'])->latest()->get();
        $sessions = AcademicSession::all();
        $classes  = SchoolClass::all();

        return view('admin.food.plans.index', compact('plans', 'sessions', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:255',
            'monthly_fee'          => 'required|numeric|min:0',
            'billing_days'         => 'required|integer|min:1|max:31',
            'academic_session_id'  => 'nullable|exists:academic_sessions,id',
            'school_class_id'      => 'nullable|exists:classes,id',
            'student_category'     => 'nullable|string|max:100',
            'status'               => 'required|in:active,inactive',
        ]);

        FoodPlan::create($request->all());

        return redirect()->route('admin.food.plans.index')->with('success', 'Food Plan created successfully!');
    }

    public function update(Request $request, $id)
    {
        $plan = FoodPlan::findOrFail($id);
        $request->validate([
            'name'                 => 'required|string|max:255',
            'monthly_fee'          => 'required|numeric|min:0',
            'billing_days'         => 'required|integer|min:1|max:31',
            'academic_session_id'  => 'nullable|exists:academic_sessions,id',
            'school_class_id'      => 'nullable|exists:classes,id',
            'student_category'     => 'nullable|string|max:100',
            'status'               => 'required|in:active,inactive',
        ]);

        $plan->update($request->all());

        return redirect()->route('admin.food.plans.index')->with('success', 'Food Plan updated successfully!');
    }

    public function destroy($id)
    {
        $plan = FoodPlan::findOrFail($id);
        $plan->delete();

        return redirect()->route('admin.food.plans.index')->with('success', 'Food Plan deleted successfully!');
    }
}
