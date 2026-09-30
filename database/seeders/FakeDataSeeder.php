<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffProfile;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\Attendance;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;

class FakeDataSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $password = Hash::make('password123');
        $session = AcademicSession::where('is_active', true)->first();
        
        $teacherNames = ['Md. Abdur Rahman', 'Syed Hasan Ali', 'Kazi Nazrul Islam', 'Farhana Akter', 'Nusrat Jahan', 'Tariqul Islam', 'Roksana Begum'];
        $parentNames = ['Mohammad Ali', 'Abdul Karim', 'Rafiqul Islam', 'Shafiqur Rahman', 'Golam Mustafa', 'Rina Begum', 'Salma Khatun', 'Ayesha Siddiqua', 'Shirin Akter', 'Fatema Tuz Zohra', 'Kabir Hossain', 'Anwar Sadat', 'Nasima Akhter', 'Mizanur Rahman'];
        $studentMaleNames = ['Rakib Hasan', 'Sakib Al Hasan', 'Mehidi Hasan', 'Tanvir Ahmed', 'Sajid Islam', 'Asif Akbar', 'Ashraful Haque', 'Tawhid Afridi', 'Naimur Rahman', 'Sabbir Hossain'];
        $studentFemaleNames = ['Sadia Afrin', 'Nadia Islam', 'Sumaiya Akter', 'Mariya Sultana', 'Tasnim Rahman', 'Fariha Jannat', 'Mithila Farzana', 'Jannatul Ferdous', 'Nusrat Imrose', 'Sanjida Akter'];

        $teacherRole = Role::where('name', 'teacher')->first();
        $parentRole = Role::where('name', 'parent')->first();
        $studentRole = Role::where('name', 'student')->first();

        // 1. Create 3 Classes and Sections
        $classes = [];
        $sections = [];
        foreach (['Class 7', 'Class 8', 'Class 9'] as $idx => $className) {
            $class = SchoolClass::firstOrCreate(
                ['name' => $className],
                ['code' => 'C' . ($idx + 7)]
            );
            $classes[] = $class;

            $sections[] = Section::firstOrCreate(['class_id' => $class->id, 'name' => 'Section A'], ['capacity' => 40]);
            $sections[] = Section::firstOrCreate(['class_id' => $class->id, 'name' => 'Section B'], ['capacity' => 40]);
        }

        // 2. Create 5 Teachers
        for ($i = 0; $i < 5; $i++) {
            $user = User::create([
                'role_id' => $teacherRole->id,
                'name' => $faker->randomElement($teacherNames),
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'status' => 'active',
            ]);
            StaffProfile::create([
                'user_id' => $user->id,
                'phone' => $faker->phoneNumber,
                'address' => $faker->address,
                'qualifications' => $faker->randomElement(['BSc', 'MSc', 'BEd', 'MEd', 'PhD']),
                'designation' => $faker->jobTitle,
                'joining_date' => $faker->dateTimeBetween('-5 years', '-1 year')->format('Y-m-d'),
                'salary' => 40000.00,
                'status' => 'active',
            ]);
        }

        // 3. Create 20 Parents
        $parents = [];
        for ($i = 0; $i < 20; $i++) {
            $user = User::create([
                'role_id' => $parentRole->id,
                'name' => $faker->randomElement($parentNames),
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'status' => 'active',
            ]);
            $parents[] = ParentProfile::create([
                'user_id' => $user->id,
                'phone' => $faker->phoneNumber,
                'occupation' => $faker->jobTitle,
                'address' => $faker->address,
            ]);
        }

        // 4. Create 50 Students & Attendance
        for ($i = 1; $i <= 50; $i++) {
            $section = $faker->randomElement($sections);
            $parent = $faker->randomElement($parents);
            $gender = $faker->randomElement(['Male', 'Female']);

            $studentName = $gender === 'Male' ? $faker->randomElement($studentMaleNames) : $faker->randomElement($studentFemaleNames);

            $user = User::create([
                'role_id' => $studentRole->id,
                'name' => $studentName,
                'email' => $faker->unique()->safeEmail,
                'password' => $password,
                'status' => 'active',
            ]);

            $studentQr = json_encode([
                'admission_no' => 'ADM-FAKE-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => $user->name,
                'class' => $section->class_id,
            ]);

            $student = StudentProfile::create([
                'user_id' => $user->id,
                'parent_id' => $parent->id,
                'roll_no' => str_pad($i, 3, '0', STR_PAD_LEFT),
                'session_id' => $session->id ?? 1,
                'class_id' => $section->class_id,
                'section_id' => $section->id,
                'admission_no' => 'ADM-FAKE-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'admission_date' => clone $faker->dateTimeBetween('-2 years', 'now'),
                'dob' => clone $faker->dateTimeBetween('-15 years', '-10 years'),
                'gender' => $gender,
                'blood_group' => $faker->randomElement(['A+', 'B+', 'O+', 'AB+']),
                'qr_code' => $studentQr,
                'status' => 'active',
            ]);

            // 5. Generate 30 days of attendance history for each student
            $statuses = ['present', 'present', 'present', 'present', 'absent', 'late']; // 4/6 chance present
            
            for ($d = 0; $d < 30; $d++) {
                $date = Carbon::today()->subDays($d);
                if ($date->isWeekend()) continue; // skip weekends
                
                $status = $faker->randomElement($statuses);
                $remarks = null;
                if ($status === 'absent') $remarks = 'Sick leave / Uninformed';
                if ($status === 'late') $remarks = 'Traffic issue';

                Attendance::create([
                    'attendance_date' => $date->format('Y-m-d'),
                    'attendable_type' => StudentProfile::class,
                    'attendable_id' => $student->id,
                    'status' => $status,
                    'remarks' => $remarks
                ]);
            }
        }

        // 6. Create Fake Subjects
        $subjectNames = ['Mathematics', 'English', 'Science', 'History', 'Geography', 'Physics', 'Chemistry', 'Biology'];
        foreach ($subjectNames as $idx => $subjName) {
            $subj = \App\Models\Subject::firstOrCreate([
                'name' => $subjName
            ], [
                'code' => 'SUB' . str_pad($idx + 1, 3, '0', STR_PAD_LEFT),
                'type' => 'theory'
            ]);
            // Attach subjects to classes
            $subj->classes()->sync($classes);
        }

        // 7. Create Fake Books
        for ($i = 1; $i <= 20; $i++) {
            \App\Models\Book::create([
                'title' => $faker->catchPhrase . ' Book',
                'author' => $faker->name,
                'isbn' => $faker->isbn13,
                'publisher' => $faker->company,
                'rack_no' => 'Rack-' . $faker->numberBetween(1, 10),
                'quantity' => $faker->numberBetween(10, 50),
                'available_qty' => $faker->numberBetween(5, 50),
            ]);
        }

        // 8. Create Fake Hostels, Rooms, Beds, and Allocations
        $this->call(HostelSeeder::class);
    }
}
