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
            // Let's create roles
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
                $roleModels[$name] = Role::create([
                    'name' => $name,
                    'display_name' => $displayName
                ]);
            }

            // Create some sample permissions
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
