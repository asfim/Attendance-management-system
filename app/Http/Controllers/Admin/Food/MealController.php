<?php

namespace App\Http\Controllers\Admin\Food;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use Illuminate\Http\Request;

class MealController extends Controller
{
    public function index()
    {
        $meals = Meal::latest()->get();
        return view('admin.food.meals.index', compact('meals'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50',
            'time'   => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        Meal::create($request->all());

        return redirect()->route('admin.food.meals.index')->with('success', 'Meal created successfully!');
    }

    public function update(Request $request, $id)
    {
        $meal = Meal::findOrFail($id);
        $request->validate([
            'name'   => 'required|string|max:255',
            'code'   => 'nullable|string|max:50',
            'time'   => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ]);

        $meal->update($request->all());

        return redirect()->route('admin.food.meals.index')->with('success', 'Meal updated successfully!');
    }

    public function destroy($id)
    {
        $meal = Meal::findOrFail($id);
        $meal->delete();

        return redirect()->route('admin.food.meals.index')->with('success', 'Meal deleted successfully!');
    }
}
