<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;

class ClassroomController extends Controller
{
    public function index()
    {
        $classrooms = Classroom::withCount(['timetables', 'examSchedules'])
            ->orderBy('room_number', 'asc')
            ->get();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_number' => 'required|string|max:50|unique:classrooms,room_number',
            'capacity' => 'required|integer|min:1',
        ]);

        Classroom::create([
            'room_number' => $request->room_number,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Classroom created successfully!');
    }

    public function update(Request $request, $id)
    {
        $classroom = Classroom::findOrFail($id);

        $request->validate([
            'room_number' => 'required|string|max:50|unique:classrooms,room_number,'.$id,
            'capacity' => 'required|integer|min:1',
        ]);

        $classroom->update([
            'room_number' => $request->room_number,
            'capacity' => $request->capacity,
        ]);

        return redirect()->route('admin.classrooms.index')->with('success', 'Classroom updated successfully!');
    }

    public function destroy($id)
    {
        $classroom = Classroom::findOrFail($id);

        if ($classroom->timetables()->exists() || $classroom->examSchedules()->exists()) {
            return redirect()->route('admin.classrooms.index')->with('error', 'Cannot delete classroom because it has assigned class routines or exam schedules.');
        }

        $classroom->delete();

        return redirect()->route('admin.classrooms.index')->with('success', 'Classroom deleted successfully!');
    }
}
