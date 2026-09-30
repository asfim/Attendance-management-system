<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ExamSchedule;
use App\Models\StudentProfile;
use App\Models\MarksEntry;
use App\Models\AcademicSession;
use Carbon\Carbon;

class FakeExamSeeder extends Seeder
{
    public function run()
    {
        $session = AcademicSession::where('is_active', true)->first() ?? AcademicSession::first();

        // 1. Create Fake Exam Type
        $examType = ExamType::create([
            'name' => 'All Subjects Mega Exam - ' . date('Y-m'),
            'session_id' => $session->id ?? 1,
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(20),
            'status' => 'upcoming'
        ]);

        $this->command->info('Created Fake Exam: ' . $examType->name);

        $classes = SchoolClass::all();
        $totalMarksCount = 0;

        foreach ($classes as $class) {
            // Force all subjects to be used for every class as requested
            $subjects = Subject::all();

            $students = StudentProfile::where('class_id', $class->id)->where('status', 'active')->get();
            
            if ($students->isEmpty()) {
                continue; // Skip class if no students
            }

            $dayCounter = 2;
            foreach ($subjects as $subject) {
                // Create Schedule
                $schedule = ExamSchedule::create([
                    'exam_type_id' => $examType->id,
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'exam_date' => Carbon::now()->addDays($dayCounter),
                    'start_time' => '10:00:00',
                    'end_time' => '12:00:00',
                    'classroom_id' => 1,
                    'max_marks' => 100,
                    'pass_marks' => 33,
                ]);

                // Seed marks for all students in this class for this subject
                foreach ($students as $student) {
                    $isAbsent = rand(1, 100) <= 5; // 5% absent rate
                    $marks = null;
                    if (!$isAbsent) {
                        $rand = rand(1, 100);
                        if ($rand <= 10) {
                            $marks = rand(15, 32); // Fail
                        } else if ($rand <= 50) {
                            $marks = rand(33, 65); // Pass
                        } else {
                            $marks = rand(66, 98); // Good
                        }
                    }

                    MarksEntry::create([
                        'exam_schedule_id' => $schedule->id,
                        'student_profile_id' => $student->id,
                        'marks_obtained' => $marks,
                        'attendance_status' => $isAbsent ? 'absent' : 'present',
                        'remarks' => $isAbsent ? 'Absent' : null,
                    ]);
                    $totalMarksCount++;
                }
                $dayCounter++;
            }
        }

        $this->command->info("Successfully seeded $totalMarksCount fake marks across all subjects and classes!");
    }
}
