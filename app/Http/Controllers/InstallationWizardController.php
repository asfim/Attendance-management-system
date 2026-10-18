<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InstallationWizardController extends Controller
{
    public function showWizard()
    {
        // If super admin already exists, don't allow running installation again
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole) {
            $adminExists = User::where('role_id', $superAdminRole->id)->exists();
            if ($adminExists) {
                return redirect()->route('login')->with('info', 'System already installed. Please login.');
            }
        }

        return view('install.wizard');
    }

    public function install(Request $request)
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        if ($superAdminRole && User::where('role_id', $superAdminRole->id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'The application is already installed.',
            ], 403);
        }

        $request->validate([
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:6|confirmed',
        ]);

        try {
            // Run migrations and seeds
            Artisan::call('migrate:fresh');
            
            // Seed base tables (Roles and Permissions)
            // Create roles for Office Attendance Suite
            $roles = [
                'super_admin' => 'Super Admin',
                'admin' => 'Admin',
                'hr' => 'HR Manager',
                'manager' => 'Department Manager',
                'accountant' => 'Accountant',
                'staff' => 'Staff',
                'employee' => 'Employee',
                'receptionist' => 'Receptionist',
            ];

            $roleModels = [];
            foreach ($roles as $name => $displayName) {
                $roleModels[$name] = Role::create([
                    'name' => $name,
                    'display_name' => $displayName
                ]);
            }

            // Create permissions for Office Attendance Suite
            $permissions = [
                'view_dashboard' => 'View Dashboard',
                'manage_employees' => 'Manage Employees',
                'manage_attendance' => 'Manage Attendance',
                'manage_shifts' => 'Manage Shifts',
                'manage_leaves' => 'Manage Leaves',
                'manage_holidays' => 'Manage Holidays',
                'manage_branches' => 'Manage Branches & Departments',
                'manage_payroll' => 'Manage Payroll & Salary',
                'manage_biometrics' => 'Manage Biometric Devices',
                'view_reports' => 'View Reports & Exports',
                'manage_notifications' => 'Manage Notifications & SMS',
                'manage_settings' => 'Manage System Settings',
            ];

            foreach ($permissions as $name => $displayName) {
                $perm = Permission::create([
                    'name' => $name,
                    'display_name' => $displayName
                ]);

                // Map all to Super Admin and Admin
                $roleModels['super_admin']->permissions()->attach($perm->id);
                $roleModels['admin']->permissions()->attach($perm->id);
            }

            // Create Super Admin user
            User::create([
                'role_id' => $roleModels['super_admin']->id,
                'name' => $request->admin_name,
                'email' => $request->admin_email,
                'password' => Hash::make($request->admin_password),
                'status' => 'active',
            ]);

            // Seed initial office structure (branches, departments, shifts, holidays)
            Artisan::call('db:seed', ['--class' => 'AttendanceSoftwareSeeder']);

            return response()->json([
                'success' => true,
                'message' => 'Installation completed successfully! You will be redirected to login.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Installation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
