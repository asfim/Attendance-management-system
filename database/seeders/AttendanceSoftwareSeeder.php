<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\LeaveBalance;
use App\Models\Holiday;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class AttendanceSoftwareSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Branches
        $dhaka = Branch::firstOrCreate(['code' => 'BR-DHK'], [
            'name'    => 'Head Office (Dhaka)',
            'code'    => 'BR-DHK',
            'address' => 'Gulshan-2, Dhaka-1212',
            'phone'   => '+8801700000001',
            'email'   => 'dhaka@company.com',
            'status'  => 'active',
        ]);

        $ctg = Branch::firstOrCreate(['code' => 'BR-CTG'], [
            'name'    => 'Chittagong Branch',
            'code'    => 'BR-CTG',
            'address' => 'GEC Circle, Chittagong',
            'phone'   => '+8801700000002',
            'email'   => 'ctg@company.com',
            'status'  => 'active',
        ]);

        $sylhet = Branch::firstOrCreate(['code' => 'BR-SYL'], [
            'name'    => 'Sylhet Branch',
            'code'    => 'BR-SYL',
            'address' => 'Zindabazar, Sylhet',
            'phone'   => '+8801700000003',
            'email'   => 'sylhet@company.com',
            'status'  => 'active',
        ]);

        // 2. Departments
        $deptIT = Department::firstOrCreate(['name' => 'Software & IT'], [
            'branch_id'   => $dhaka->id,
            'name'        => 'Software & IT',
            'code'        => 'IT',
            'description' => 'Software development & IT infrastructure',
            'status'      => 'active',
        ]);

        $deptHR = Department::firstOrCreate(['name' => 'HR & Administration'], [
            'branch_id'   => $dhaka->id,
            'name'        => 'HR & Administration',
            'code'        => 'HR',
            'description' => 'Human Resources & Office Admin',
            'status'      => 'active',
        ]);

        $deptFinance = Department::firstOrCreate(['name' => 'Finance & Accounts'], [
            'branch_id'   => $dhaka->id,
            'name'        => 'Finance & Accounts',
            'code'        => 'FIN',
            'description' => 'Payroll & Financial operations',
            'status'      => 'active',
        ]);

        $deptSales = Department::firstOrCreate(['name' => 'Sales & Marketing'], [
            'branch_id'   => $ctg->id,
            'name'        => 'Sales & Marketing',
            'code'        => 'SALES',
            'description' => 'Business development & customer growth',
            'status'      => 'active',
        ]);

        // 3. Designations
        $desigEng = Designation::firstOrCreate(['title' => 'Senior Software Engineer'], [
            'department_id' => $deptIT->id,
            'title'         => 'Senior Software Engineer',
            'description'   => 'Core dev & system architect',
            'status'        => 'active',
        ]);

        $desigHRM = Designation::firstOrCreate(['title' => 'HR Executive'], [
            'department_id' => $deptHR->id,
            'title'         => 'HR Executive',
            'description'   => 'HR Operations & Payroll assistance',
            'status'        => 'active',
        ]);

        $desigAcc = Designation::firstOrCreate(['title' => 'Senior Accountant'], [
            'department_id' => $deptFinance->id,
            'title'         => 'Senior Accountant',
            'description'   => 'Accounts management',
            'status'        => 'active',
        ]);

        $desigSales = Designation::firstOrCreate(['title' => 'Sales Manager'], [
            'department_id' => $deptSales->id,
            'title'         => 'Sales Manager',
            'description'   => 'Sales operations',
            'status'        => 'active',
        ]);

        // 4. Shifts
        $shiftGeneral = Shift::firstOrCreate(['name' => 'General Shift'], [
            'name'                        => 'General Shift',
            'code'                        => 'S-GEN',
            'shift_type'                  => 'general',
            'start_time'                  => '09:00:00',
            'end_time'                    => '17:00:00',
            'grace_time_minutes'          => 15,
            'late_mark_after_minutes'     => 30,
            'early_leave_before_minutes'  => 15,
            'overtime_start_after_minutes'=> 30,
            'half_day_hours'              => 4.0,
            'description'                 => 'Standard 9 AM to 5 PM office hours',
            'status'                      => 'active',
        ]);

        $shiftMorning = Shift::firstOrCreate(['name' => 'Morning Shift'], [
            'name'                        => 'Morning Shift',
            'code'                        => 'S-MORN',
            'shift_type'                  => 'morning',
            'start_time'                  => '08:00:00',
            'end_time'                    => '16:00:00',
            'grace_time_minutes'          => 10,
            'late_mark_after_minutes'     => 20,
            'early_leave_before_minutes'  => 10,
            'overtime_start_after_minutes'=> 30,
            'half_day_hours'              => 4.0,
            'description'                 => 'Early morning operational shift',
            'status'                      => 'active',
        ]);

        $shiftNight = Shift::firstOrCreate(['name' => 'Night Shift'], [
            'name'                        => 'Night Shift',
            'code'                        => 'S-NIGHT',
            'shift_type'                  => 'night',
            'start_time'                  => '22:00:00',
            'end_time'                    => '06:00:00',
            'grace_time_minutes'          => 15,
            'late_mark_after_minutes'     => 30,
            'early_leave_before_minutes'  => 15,
            'overtime_start_after_minutes'=> 30,
            'half_day_hours'              => 4.0,
            'description'                 => 'Overnight support shift',
            'status'                      => 'active',
        ]);

        // 5. Sample Staff Users
        $staffData = [
            [
                'name'           => 'Tanvir Ahmed',
                'email'          => 'tanvir@company.com',
                'phone'          => '01711112233',
                'biometric_id'   => '1001',
                'fingerprint_id' => 'FP-1001',
                'face_id'        => 'FC-1001',
                'branch_id'      => $dhaka->id,
                'department_id'  => $deptIT->id,
                'designation_id' => $desigEng->id,
                'salary'         => 65000,
                'overtime_rate'  => 300,
            ],
            [
                'name'           => 'Nusrat Jahan',
                'email'          => 'nusrat@company.com',
                'phone'          => '01822223344',
                'biometric_id'   => '1002',
                'fingerprint_id' => 'FP-1002',
                'face_id'        => 'FC-1002',
                'branch_id'      => $dhaka->id,
                'department_id'  => $deptHR->id,
                'designation_id' => $desigHRM->id,
                'salary'         => 45000,
                'overtime_rate'  => 200,
            ],
            [
                'name'           => 'Mahmudul Hasan',
                'email'          => 'mahmud@company.com',
                'phone'          => '01933334455',
                'biometric_id'   => '1003',
                'fingerprint_id' => 'FP-1003',
                'face_id'        => 'FC-1003',
                'branch_id'      => $ctg->id,
                'department_id'  => $deptSales->id,
                'designation_id' => $desigSales->id,
                'salary'         => 50000,
                'overtime_rate'  => 250,
            ],
            [
                'name'           => 'Rahim Uddin',
                'email'          => 'rahim@company.com',
                'phone'          => '01644445566',
                'biometric_id'   => '1004',
                'fingerprint_id' => 'FP-1004',
                'face_id'        => 'FC-1004',
                'branch_id'      => $dhaka->id,
                'department_id'  => $deptFinance->id,
                'designation_id' => $desigAcc->id,
                'salary'         => 55000,
                'overtime_rate'  => 250,
            ],
        ];

        foreach ($staffData as $item) {
            $user = User::firstOrCreate(['email' => $item['email']], [
                'name'     => $item['name'],
                'email'    => $item['email'],
                'password' => Hash::make('password'),
                'role_id'  => 2, // Admin/Staff
                'status'   => 'active',
            ]);

            $profile = StaffProfile::updateOrCreate(['user_id' => $user->id], [
                'user_id'        => $user->id,
                'branch_id'      => $item['branch_id'],
                'department_id'  => $item['department_id'],
                'designation_id' => $item['designation_id'],
                'biometric_id'   => $item['biometric_id'],
                'fingerprint_id' => $item['fingerprint_id'],
                'face_id'        => $item['face_id'],
                'phone'          => $item['phone'],
                'department'     => 'IT',
                'designation'    => 'Staff',
                'joining_date'   => now()->subMonths(6)->toDateString(),
                'salary'         => $item['salary'],
                'overtime_rate'  => $item['overtime_rate'],
                'status'         => 'active',
            ]);

            // Assign General Shift
            $profile->shifts()->syncWithoutDetaching([$shiftGeneral->id]);

            // Leave Balances
            LeaveBalance::firstOrCreate(['staff_profile_id' => $profile->id, 'year' => now()->year], [
                'staff_profile_id'     => $profile->id,
                'year'                 => now()->year,
                'casual_leave_quota'   => 10,
                'casual_leave_used'    => 2,
                'sick_leave_quota'     => 14,
                'sick_leave_used'      => 1,
                'annual_leave_quota'   => 15,
                'annual_leave_used'    => 0,
                'emergency_leave_quota' => 5,
                'emergency_leave_used' => 0,
            ]);

            // Today's attendance seed
            $statuses = ['present', 'late', 'absent', 'early_leave'];
            $status = $statuses[array_rand($statuses)];
            $entry = ($status === 'late') ? '09:42:00' : '08:55:00';
            $exit  = ($status === 'early_leave') ? '15:30:00' : '17:15:00';

            if ($status === 'absent') {
                $entry = null;
                $exit = null;
            }

            Attendance::updateOrCreate([
                'attendable_type' => StaffProfile::class,
                'attendable_id'   => $profile->id,
                'attendance_date' => now()->toDateString(),
            ], [
                'branch_id'       => $profile->branch_id,
                'department_id'   => $profile->department_id,
                'shift_id'        => $shiftGeneral->id,
                'attendance_date' => now()->toDateString(),
                'entry_time'      => $entry,
                'exit_time'       => $exit,
                'status'          => $status,
                'late_minutes'    => ($status === 'late') ? 42 : 0,
                'early_leave_minutes' => ($status === 'early_leave') ? 90 : 0,
                'working_hours'   => ($status === 'absent') ? 0 : 8.0,
            ]);
        }

        // 6. Holidays
        Holiday::firstOrCreate(['date' => '2026-03-26'], [
            'name' => 'Independence Day',
            'type' => 'government',
            'date' => '2026-03-26',
        ]);

        Holiday::firstOrCreate(['date' => '2026-12-16'], [
            'name' => 'Victory Day',
            'type' => 'government',
            'date' => '2026-12-16',
        ]);

        Holiday::firstOrCreate(['date' => '2026-10-15'], [
            'name' => 'Company Foundation Day',
            'type' => 'company',
            'date' => '2026-10-15',
        ]);
    }
}
