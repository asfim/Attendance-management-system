<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetable;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\StaffProfile;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimetableSeeder extends Seeder
{
    public function run()
    {
        DB::table('timetables')->truncate();

        $session = AcademicSession::where('is_active', true)->first();
        if (!$session) {
            $session = AcademicSession::first();
        }

        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $teachers = StaffProfile::all();
        $classrooms = Classroom::all();

        if ($classes->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) {
            return;
        }

        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];

        foreach ($classes as $class) {
            $sections = $class->sections;
            if ($sections->isEmpty()) {
                $sections = [null]; // Seed for the class without specific section
            }

            foreach ($sections as $section) {
                foreach ($daysOfWeek as $day) {
                    $currentTime = Carbon::createFromTime(8, 0, 0); // Start at 8:00 AM
                    $endTimeLimit = Carbon::createFromTime(14, 0, 0); // End at 2:00 PM

                    // 4 classes per day per section
                    for ($i = 0; $i < 4; $i++) {
                        if ($currentTime->greaterThanOrEqualTo($endTimeLimit)) {
                            break;
                        }

                        $subject = $subjects->random();
                        $teacher = $teachers->random();
                        $classroom = $classrooms->isNotEmpty() ? $classrooms->random() : null;

                        $startTime = $currentTime->format('H:i:s');
                        $endTimeObj = $currentTime->copy()->addMinutes(45);
                        $endTime = $endTimeObj->format('H:i:s');

                        Timetable::create([
                            'session_id' => $session->id,
                            'class_id' => $class->id,
                            'section_id' => $section ? $section->id : null,
                            'subject_id' => $subject->id,
                            'staff_profile_id' => $teacher->id,
                            'classroom_id' => $classroom ? $classroom->id : null,
                            'day_of_week' => $day,
                            'start_time' => $startTime,
                            'end_time' => $endTime,
                        ]);

                        // Next class starts exactly 30 minutes after this class ends
                        $currentTime = $endTimeObj->addMinutes(30);
                    }
                }
            }
        }
    }
}
