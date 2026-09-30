<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Branch;
use Illuminate\Http\Request;

class AttendanceHolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.attendance_software.holidays.index', compact('holidays', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:government,company,weekly,festival,custom',
            'date'      => 'required|date|unique:holidays,date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Holiday::create($request->all());

        return redirect()->back()->with('success', 'Holiday added to calendar successfully!');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return redirect()->back()->with('success', 'Holiday removed successfully!');
    }
}
