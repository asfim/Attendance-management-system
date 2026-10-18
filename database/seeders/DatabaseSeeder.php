<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database for Office Attendance Suite.
     */
    public function run(): void
    {
        // 1. Create Roles for Office Attendance Suite
        $roles = [
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'hr'          => 'HR Manager',
            'manager'     => 'Department Manager',
            'accountant'  => 'Accountant',
            'staff'       => 'Staff',
            'employee'    => 'Employee',
            'receptionist'=> 'Receptionist',
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
            'view_dashboard'       => 'View Dashboard',
            'manage_employees'     => 'Manage Employees',
            'manage_attendance'    => 'Manage Attendance',
            'manage_shifts'        => 'Manage Shifts',
            'manage_leaves'        => 'Manage Leaves',
            'manage_holidays'      => 'Manage Holidays',
            'manage_branches'      => 'Manage Branches & Departments',
            'manage_payroll'       => 'Manage Payroll & Salary',
            'manage_biometrics'    => 'Manage Biometric Devices',
            'view_reports'         => 'View Reports & Exports',
            'manage_notifications' => 'Manage Notifications & SMS',
            'manage_settings'      => 'Manage System Settings',
        ];

        foreach ($permissions as $name => $displayName) {
            $perm = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $displayName]
            );

            // Assign all permissions to super_admin and admin
            $roleModels['super_admin']->permissions()->syncWithoutDetaching([$perm->id]);
            $roleModels['admin']->permissions()->syncWithoutDetaching([$perm->id]);
        }

        // 3. Create Admin Users
        User::firstOrCreate(['email' => 'admin@company.com'], [
            'role_id'  => $roleModels['super_admin']->id,
            'name'     => 'Super Admin',
            'email'    => 'admin@company.com',
            'password' => Hash::make('admin123'),
            'status'   => 'active',
        ]);

        // Keep fallback so any previous session works
        User::firstOrCreate(['email' => 'admin@school.com'], [
            'role_id'  => $roleModels['super_admin']->id,
            'name'     => 'Super Admin',
            'email'    => 'admin@school.com',
            'password' => Hash::make('admin123'),
            'status'   => 'active',
        ]);

        // 4. Seed Attendance Software Suite data (Branches, Departments, Designations, Shifts, Employees, Holidays)
        $this->call(AttendanceSoftwareSeeder::class);
    }
}
