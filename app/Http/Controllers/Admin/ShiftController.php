<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::all();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:shifts,code',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:active,inactive',
        ]);

        Shift::create($request->all());

        return redirect()->route('admin.shifts.index')->with('success', 'Shift created successfully!');
    }


    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:shifts,code,' . $shift->id,
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:active,inactive',
        ]);

        $shift->update($request->all());

        return redirect()->route('admin.shifts.index')->with('success', 'Shift updated successfully!');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->students()->count() > 0 || $shift->timetables()->count() > 0) {
            return redirect()->route('admin.shifts.index')->with('error', 'Cannot delete shift. It is assigned to students or routines.');
        }

        $shift->delete();
        return redirect()->route('admin.shifts.index')->with('success', 'Shift deleted successfully!');
    }
}
