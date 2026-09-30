<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AcademicSession;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\ParentProfile;
use App\Models\Timetable;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Book;
use App\Models\Notice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $roles = [
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'principal' => 'Principal',
            'vice_principal' => 'Vice Principal',
            'teacher' => 'Teacher',
            'accountant' => 'Accountant',
            'librarian' => 'Librarian',
            'receptionist' => 'Receptionist',
            'hr' => 'HR',
            'staff' => 'Staff',
            'student' => 'Student',
            'parent' => 'Parent'
        ];

        $roleModels = [];
        foreach ($roles as $name => $displayName) {
            $roleModels[$name] = Role::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );
        }

        // 2. Create Permissions
        $permissions = [
            'view_dashboard' => 'View Dashboard',
            'manage_users' => 'Manage Users',
            'manage_academics' => 'Manage Academics',
            'manage_students' => 'Manage Students',
            'manage_teachers' => 'Manage Teachers',
            'take_attendance' => 'Take Attendance',
            'manage_fees' => 'Manage Fees',
            'manage_accounts' => 'Manage Accounts',
            'manage_payroll' => 'Manage Payroll',
            'manage_library' => 'Manage Library',
            'manage_hostel' => 'Manage Hostel',
            'manage_transport' => 'Manage Transport',
            'manage_inventory' => 'Manage Inventory',
            'manage_notices' => 'Manage Notices',
            'manage_certificates' => 'Manage Certificates',
            'manage_settings' => 'Manage Settings'
        ];

        foreach ($permissions as $name => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );

            // Assign all permissions to super_admin and admin
            $roleModels['super_admin']->permissions()->attach($perm->id);
            $roleModels['admin']->permissions()->attach($perm->id);
        }

        // 3. Create Users & Profiles
        // Super Admin
        User::create([
            'role_id' => $roleModels['super_admin']->id,
            'name' => 'Super Admin',
            'email' => 'admin@school.com',
            'password' => Hash::make('admin123'),
            'status' => 'active',
        ]);

        // Principal
        User::create([
            'role_id' => $roleModels['principal']->id,
            'name' => 'Principal Principal',
            'email' => 'principal@school.com',
            'password' => Hash::make('principal123'),
            'status' => 'active',
        ]);

        // Teacher 1
        $t1User = User::create([
            'role_id' => $roleModels['teacher']->id,
            'name' => 'John Doe (Math Teacher)',
            'email' => 'teacher@school.com',
            'password' => Hash::make('teacher123'),
            'status' => 'active',
        ]);

        $t1 = StaffProfile::create([
            'user_id' => $t1User->id,
            'phone' => '01712345678',
            'address' => 'Mirpur, Dhaka',
            'qualifications' => 'MSc in Mathematics',
            'designation' => 'Senior Mathematics Teacher',
            'joining_date' => '2024-01-01',
            'salary' => 50000.00,
            'status' => 'active',
        ]);

        // Parent 1
        $p1User = User::create([
            'role_id' => $roleModels['parent']->id,
            'name' => 'Rahim Ali',
            'email' => 'parent@school.com',
            'password' => Hash::make('parent123'),
            'status' => 'active',
        ]);

        $p1 = ParentProfile::create([
            'user_id' => $p1User->id,
            'phone' => '01987654321',
            'occupation' => 'Businessman',
            'address' => 'Uttara, Dhaka',
        ]);

        // 4. Academics Setup
        $session = AcademicSession::create([
            'name' => '2025-2026',
            'is_active' => true,
        ]);

        $class6 = SchoolClass::create([
            'name' => 'Class 6',
            'code' => 'C6',
        ]);

        $secA = Section::create([
            'class_id' => $class6->id,
            'name' => 'Section A',
            'capacity' => 45,
        ]);

        $subjectMath = Subject::create([
            'name' => 'Mathematics',
            'code' => 'MATH101',
            'type' => 'theory',
        ]);
        $subjectMath->classes()->attach($class6->id);

        $classroom101 = Classroom::create([
            'room_number' => '101',
            'capacity' => 50,
        ]);

        // 5. Student Profile
        $s1User = User::create([
            'role_id' => $roleModels['student']->id,
            'name' => 'Sabbir Rahim (Student)',
            'email' => 'student@school.com',
            'password' => Hash::make('student123'),
            'status' => 'active',
        ]);

        $studentQr = json_encode([
            'admission_no' => 'ADM-2026001',
            'name' => $s1User->name,
            'class' => $class6->id,
        ]);

        StudentProfile::create([
            'user_id' => $s1User->id,
            'parent_id' => $p1->id,
            'roll_no' => '01',
            'session_id' => $session->id,
            'class_id' => $class6->id,
            'section_id' => $secA->id,
            'admission_no' => 'ADM-2026001',
            'admission_date' => '2025-01-10',
            'dob' => '2014-05-15',
            'gender' => 'Male',
            'blood_group' => 'O+',
            'qr_code' => $studentQr,
            'status' => 'active',
        ]);

        // 6. Timetable Setup
        Timetable::create([
            'session_id' => $session->id,
            'class_id' => $class6->id,
            'section_id' => $secA->id,
            'subject_id' => $subjectMath->id,
            'staff_profile_id' => $t1->id,
            'classroom_id' => $classroom101->id,
            'day_of_week' => 'Sunday',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        // 7. Fees Setup
        $feeCat = FeeCategory::create(['name' => 'Tuition Fee']);
        FeeStructure::create([
            'fee_category_id' => $feeCat->id,
            'class_id' => $class6->id,
            'amount' => 1500.00,
        ]);

        // 8. Library Books
        Book::create([
            'title' => 'Introduction to Algebra',
            'author' => 'G. Chrystal',
            'isbn' => '978-0-123456-78-9',
            'publisher' => 'Academic Press',
            'rack_no' => 'Rack A1',
            'quantity' => 10,
            'available_qty' => 10,
        ]);

        // 9. Notices
        Notice::create([
            'title' => 'Annual Sports Meet 2026',
            'content' => 'The Annual Sports Meet of the school will take place on February 20th. All students are invited to register.',
            'target_audience' => 'all',
            'published_at' => now(),
        ]);

        // 10. Grade Rules
        $this->call(GradeRuleSeeder::class);
    }
}
