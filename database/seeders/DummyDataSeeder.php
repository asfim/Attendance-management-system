<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\Role;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DummyDataSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        
        // 1. More Classes & Sections
        $classes = ['Class 7', 'Class 8', 'Class 9', 'Class 10'];
        $sections = ['Section A', 'Section B'];
        $subjects = ['English', 'Science', 'History', 'Geography', 'Physics', 'Chemistry'];
        
        $createdClasses = [];
        foreach ($classes as $className) {
            $class = SchoolClass::firstOrCreate([
                'name' => $className,
                'code' => 'C' . str_replace('Class ', '', $className),
            ]);
            $createdClasses[] = $class;
            
            foreach ($sections as $secName) {
                Section::firstOrCreate([
                    'class_id' => $class->id,
                    'name' => $secName,
                    'capacity' => 40,
                ]);
            }
        }
        
        // 2. More Subjects
        foreach ($subjects as $subName) {
            $sub = Subject::firstOrCreate([
                'name' => $subName,
                'code' => strtoupper(substr($subName, 0, 3)) . '101',
                'type' => 'theory',
            ]);
            // attach to random classes
            $sub->classes()->syncWithoutDetaching(SchoolClass::pluck('id')->random(3)->toArray());
        }
        
        // 3. More Classrooms
        for ($i = 102; $i <= 110; $i++) {
            Classroom::firstOrCreate([
                'room_number' => (string)$i,
                'capacity' => 50,
            ]);
        }
        
        // Fetch existing references
        $teachers = StaffProfile::all();
        $students = StudentProfile::all();
        $session_id = DB::table('academic_sessions')->where('is_active', true)->value('id');
        
        // 4. Leave Types & Leaves (for Staff and Students)
        $leaveTypes = ['Sick Leave', 'Casual Leave', 'Emergency Leave'];
        foreach ($leaveTypes as $lt) {
            $typeId = DB::table('leave_types')->insertGetId([
                'name' => $lt,
                'max_days' => 10,
                'applicable_to' => 'both',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Assign some random leaves to teachers
            if ($teachers->count() > 0) {
                for($i = 0; $i < 3; $i++) {
                    DB::table('leave_applications')->insert([
                        'applicant_type' => 'App\Models\StaffProfile',
                        'applicant_id' => $teachers->random()->id,
                        'leave_type_id' => $typeId,
                        'start_date' => $faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
                        'end_date' => $faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
                        'reason' => $faker->sentence,
                        'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // 5. Homework
        if ($teachers->count() > 0 && SchoolClass::count() > 0) {
            for ($i = 0; $i < 10; $i++) {
                $cls = SchoolClass::inRandomOrder()->first();
                $sec = Section::where('class_id', $cls->id)->inRandomOrder()->first();
                $sub = Subject::inRandomOrder()->first();
                
                if ($sec) {
                    DB::table('homework')->insert([
                        'session_id' => $session_id,
                        'class_id' => $cls->id,
                        'section_id' => $sec->id,
                        'subject_id' => $sub->id,
                        'staff_profile_id' => $teachers->random()->id,
                        'title' => $faker->sentence(3),
                        'homework_date' => now()->format('Y-m-d'),
                        'due_date' => now()->addDays(3)->format('Y-m-d'),
                        'description' => $faker->paragraph,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        if ($teachers->count() > 0 && SchoolClass::count() > 0) {
            for ($i = 0; $i < 10; $i++) {
                $cls = SchoolClass::inRandomOrder()->first();
                $sec = Section::where('class_id', $cls->id)->inRandomOrder()->first();
                $sub = Subject::inRandomOrder()->first();
                
                if ($sec) {
                    DB::table('study_materials')->insert([
                        'session_id' => $session_id,
                        'title' => $faker->sentence(3),
                        'class_id' => $cls->id,
                        'section_id' => $sec->id,
                        'subject_id' => $sub->id,
                        'staff_profile_id' => $teachers->random()->id,
                        'description' => $faker->paragraph,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        
        // 7. Exam Types & Schedules
        $examTypeId = DB::table('exam_types')->insertGetId([
            'name' => 'Mid Term Examination 2026',
            'session_id' => $session_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        if (SchoolClass::count() > 0) {
            for ($i = 0; $i < 5; $i++) {
                $cls = SchoolClass::inRandomOrder()->first();
                $sub = Subject::inRandomOrder()->first();
                $room = Classroom::inRandomOrder()->first();
                
                DB::table('exam_schedules')->insert([
                    'exam_type_id' => $examTypeId,
                    'class_id' => $cls->id,
                    'subject_id' => $sub->id,
                    'classroom_id' => $room->id,
                    'exam_date' => $faker->dateTimeBetween('+1 month', '+2 months')->format('Y-m-d'),
                    'start_time' => '10:00:00',
                    'end_time' => '12:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // 8. Notices and Internal Communication
        for ($i = 0; $i < 5; $i++) {
            DB::table('notices')->insert([
                'title' => $faker->sentence,
                'content' => $faker->paragraph,
                'target_audience' => $faker->randomElement(['all', 'student', 'teacher', 'parent']),
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
