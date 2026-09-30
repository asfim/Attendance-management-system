<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Classroom;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffProfile;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\Shift;
use Illuminate\Http\Request;

class RoutineController extends Controller
{
    public function index(Request $request)
    {
        $sessions = AcademicSession::orderBy('is_active', 'desc')->get();
        $classes = SchoolClass::with('sections')->get();
        $shifts = Shift::where('status', 'active')->get();

        $selectedClassId = $request->get('class_id', $classes->first()?->id);
        $selectedShiftId = $request->get('shift_id');

        $sections = $selectedClassId
            ? Section::where('class_id', $selectedClassId)->get()
            : Section::all();

        $requestedSectionId = $request->get('section_id');
        
        if (!$request->has('section_id') && $sections->isNotEmpty()) {
            // Initial page load, default to first section
            $selectedSectionId = $sections->first()->id;
        } elseif ($request->has('section_id') && $requestedSectionId !== null && $requestedSectionId !== '') {
            // A specific section was requested
            if (!$sections->contains('id', $requestedSectionId) && $sections->isNotEmpty()) {
                $selectedSectionId = $sections->first()->id;
            } else {
                $selectedSectionId = $requestedSectionId;
            }
        } else {
            // "All sections" explicitly requested via dropdown
            $selectedSectionId = null;
        }

        $subjects = $selectedClassId
            ? Subject::whereHas('classes', function ($q) use ($selectedClassId) {
                $q->where('classes.id', $selectedClassId);
            })->get()
            : Subject::all();

        // Fallback to all subjects if none attached to specific class
        if ($subjects->isEmpty()) {
            $subjects = Subject::all();
        }

        $teachers = StaffProfile::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with('user')->get();

        $classrooms = Classroom::all();

        $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $query = Timetable::with([
            'academicSession',
            'schoolClass',
            'section',
            'subject',
            'staffProfile.user',
            'classroom'
        ]);

        if ($selectedClassId) {
            $query->where('class_id', $selectedClassId);
        }

        if ($selectedSectionId === null) {
            $query->whereNull('section_id');
        } else {
            $query->where(function($q) use ($selectedSectionId) {
                $q->where('section_id', $selectedSectionId)
                  ->orWhereNull('section_id');
            });
        }

        if ($selectedShiftId) {
            $query->where('shift_id', $selectedShiftId);
        }

        $timetables = $query->orderBy('start_time', 'asc')->get();

        // Group timetables by day_of_week
        $groupedRoutines = [];
        foreach ($daysOfWeek as $day) {
            $groupedRoutines[$day] = $timetables->filter(function ($item) use ($day) {
                return strtolower($item->day_of_week) === strtolower($day);
            })->values();
        }

        return view('admin.routines.index', compact(
            'sessions',
            'classes',
            'sections',
            'shifts',
            'subjects',
            'teachers',
            'classrooms',
            'timetables',
            'groupedRoutines',
            'selectedClassId',
            'selectedSectionId',
            'selectedShiftId',
            'daysOfWeek'
        ));
    }

    public function teacherTimetable(Request $request)
    {
        $teachers = StaffProfile::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with('user')->get();

        $selectedTeacherId = $request->get('staff_profile_id');
        $selectedDay = $request->get('day_of_week');

        $timetables = collect();
        if ($selectedTeacherId) {
            $query = Timetable::with([
                'academicSession',
                'schoolClass',
                'section',
                'subject',
                'staffProfile.user',
                'classroom'
            ])->where('staff_profile_id', $selectedTeacherId);

            if ($selectedDay) {
                $query->where('day_of_week', $selectedDay);
            }

            $timetables = $query->orderBy('start_time', 'asc')->get();
        }

        $allDaysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $daysOfWeek = $selectedDay ? [$selectedDay] : $allDaysOfWeek;
        
        $groupedRoutines = [];
        foreach ($daysOfWeek as $day) {
            $groupedRoutines[$day] = $timetables->filter(function ($item) use ($day) {
                return strtolower($item->day_of_week) === strtolower($day);
            })->values();
        }

        return view('admin.routines.teacher', compact(
            'teachers',
            'timetables',
            'groupedRoutines',
            'selectedTeacherId',
            'selectedDay',
            'allDaysOfWeek',
            'daysOfWeek'
        ));
    }

    public function create()
    {
        $sessions = AcademicSession::orderBy('is_active', 'desc')->get();
        $classes = SchoolClass::with('sections')->get();
        $subjects = Subject::all();
        $teachers = StaffProfile::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with('user')->get();
        $classrooms = Classroom::all();
        $shifts = Shift::where('status', 'active')->get();
        $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('admin.routines.create', compact(
            'sessions', 'classes', 'subjects', 'teachers', 'classrooms', 'shifts', 'daysOfWeek'
        ));
    }

    public function edit($id)
    {
        $timetable = Timetable::findOrFail($id);

        $sessions = AcademicSession::orderBy('is_active', 'desc')->get();
        $classes = SchoolClass::with('sections')->get();
        $subjects = Subject::all();
        $teachers = StaffProfile::whereHas('user', function ($q) {
            $q->where('status', 'active');
        })->with('user')->get();
        $classrooms = Classroom::all();
        $shifts = Shift::where('status', 'active')->get();
        $daysOfWeek = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        return view('admin.routines.edit', compact(
            'timetable', 'sessions', 'classes', 'subjects', 'teachers', 'classrooms', 'shifts', 'daysOfWeek'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => ['required', function ($attribute, $value, $fail) {
                if ($value !== 'both' && !\App\Models\Section::where('id', $value)->exists()) {
                    $fail('The selected section is invalid.');
                }
            }],
            'subject_id' => 'required|exists:subjects,id',
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'day_of_week' => 'required|string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $startTime = date('H:i:s', strtotime($request->start_time));
        $endTime = date('H:i:s', strtotime($request->end_time));

        // Minimum 30 minutes duration validation
        $startTimeObj = \Carbon\Carbon::parse($startTime);
        $endTimeObj = \Carbon\Carbon::parse($endTime);
        if ($startTimeObj->diffInMinutes($endTimeObj) < 30) {
            return back()->withInput()->with('error', 'The class duration you entered is too short. A single class slot must be at least 30 minutes long (e.g., from 09:00 to 09:30).');
        }

        $sectionsToCreate = [];
        if ($request->section_id === 'both') {
            $sectionsToCreate = [null];
        } else {
            $sectionsToCreate = [$request->section_id];
        }

        // We will perform conflict checks for all selected sections before saving any
        foreach ($sectionsToCreate as $secId) {
            // Check class section conflict for this section
            $classConflict = Timetable::where('class_id', $request->class_id)
                ->where('shift_id', $request->shift_id)
                ->where('day_of_week', $request->day_of_week)
                ->where(function ($q) use ($secId) {
                    if ($secId) {
                        $q->where('section_id', $secId)->orWhereNull('section_id');
                    }
                })
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                })->first();

            if ($classConflict) {
                return back()->withInput()->with('error', 'Class section already has a scheduled routine slot during this time period.');
            }
        }

        // Check teacher conflict (once, as they are teaching all these sections together or it's just one slot)
        $teacherConflict = Timetable::where('staff_profile_id', $request->staff_profile_id)
            ->where('shift_id', $request->shift_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })->first();

        if ($teacherConflict) {
            return back()->withInput()->with('error', 'Teacher is already assigned to another class during this time slot ('.$teacherConflict->start_time.' - '.$teacherConflict->end_time.').');
        }

        foreach ($sectionsToCreate as $secId) {
            Timetable::create([
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $secId,
                'shift_id' => $request->shift_id,
                'subject_id' => $request->subject_id,
                'staff_profile_id' => $request->staff_profile_id,
                'classroom_id' => $request->classroom_id,
                'day_of_week' => $request->day_of_week,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);
        }

        return redirect()->route('admin.routines.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id === 'both' ? null : $request->section_id,
        ])->with('success', 'Class routine entry added successfully!');
    }

    public function update(Request $request, $id)
    {
        $timetable = Timetable::findOrFail($id);

        $request->validate([
            'session_id' => 'required|exists:academic_sessions,id',
            'class_id' => 'required|exists:classes,id',
            'section_id' => 'nullable|exists:sections,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'subject_id' => 'required|exists:subjects,id',
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'classroom_id' => 'nullable|exists:classrooms,id',
            'day_of_week' => 'required|string|in:Saturday,Sunday,Monday,Tuesday,Wednesday,Thursday,Friday',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        // Normalize H:i:s format
        $startTime = date('H:i:s', strtotime($request->start_time));
        $endTime = date('H:i:s', strtotime($request->end_time));

        $startTimeObj = \Carbon\Carbon::parse($startTime);
        $endTimeObj = \Carbon\Carbon::parse($endTime);

        if ($endTimeObj <= $startTimeObj) {
            return back()->withInput()->with('error', 'End time must be after start time.');
        }

        if ($startTimeObj->diffInMinutes($endTimeObj) < 30) {
            return back()->withInput()->with('error', 'The class duration you entered is too short. A single class slot must be at least 30 minutes long (e.g., from 09:00 to 09:30).');
        }

        // Check teacher conflict excluding current record
        $teacherConflict = Timetable::where('id', '!=', $id)
            ->where('staff_profile_id', $request->staff_profile_id)
            ->where('shift_id', $request->shift_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })->first();

        if ($teacherConflict) {
            return back()->withInput()->with('error', 'Teacher is already assigned to another class during this time slot.');
        }

        // Check class section conflict excluding current record
        $classConflict = Timetable::where('id', '!=', $id)
            ->where('class_id', $request->class_id)
            ->where('shift_id', $request->shift_id)
            ->where('day_of_week', $request->day_of_week)
            ->where(function ($q) use ($request) {
                if ($request->section_id) {
                    $q->where('section_id', $request->section_id)
                      ->orWhereNull('section_id');
                }
            })
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            })->first();

        if ($classConflict) {
            return back()->withInput()->with('error', 'Class section already has a scheduled routine slot during this time period.');
        }

        $timetable->update([
            'session_id' => $request->session_id,
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
            'shift_id' => $request->shift_id,
            'subject_id' => $request->subject_id,
            'staff_profile_id' => $request->staff_profile_id,
            'classroom_id' => $request->classroom_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        return redirect()->route('admin.routines.index', [
            'class_id' => $request->class_id,
            'section_id' => $request->section_id,
        ])->with('success', 'Class routine entry updated successfully!');
    }

    public function destroy($id)
    {
        $timetable = Timetable::findOrFail($id);
        $classId = $timetable->class_id;
        $sectionId = $timetable->section_id;

        $timetable->delete();

        return redirect()->route('admin.routines.index', [
            'class_id' => $classId,
            'section_id' => $sectionId,
        ])->with('success', 'Class routine slot deleted successfully!');
    }
}
