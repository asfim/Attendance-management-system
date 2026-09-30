<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        // Exclude core non-admin roles since they have their own dedicated dashboards and don't use dynamic admin permissions
        $roles = Role::withCount('permissions')
            ->whereNotIn('name', ['student', 'parent'])
            ->get();
        $permissions = Permission::all();
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function create()
    {
        $groupedPermissions = $this->getGroupedPermissions();
        $roles = Role::whereNotIn('name', ['super_admin'])->get();
        return view('admin.roles.create', compact('groupedPermissions', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'display_name' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        $roleName = \Illuminate\Support\Str::slug($request->name);

        $role = Role::updateOrCreate(
            ['name' => $roleName],
            ['display_name' => $request->display_name]
        );

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role saved successfully.');
    }

    public function edit(Role $role)
    {
        $groupedPermissions = $this->getGroupedPermissions();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'groupedPermissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'display_name' => 'required|string|max:255',
            'permissions' => 'array'
        ]);

        // Don't allow changing the name of core roles easily, just display name
        $role->update([
            'display_name' => $request->display_name,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            $role->permissions()->detach();
        }

        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['super_admin', 'admin', 'teacher', 'student', 'parent'])) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete core system roles.');
        }

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    // Quick method to add a new permission if needed
    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'display_name' => 'required|string|max:255',
        ]);

        Permission::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return redirect()->route('admin.roles.index')->with('success', 'Permission created successfully.');
    }

    private function getGroupedPermissions()
    {
        $groups = [
            'Dashboard' => [
                ['name' => 'menu_dashboard', 'display_name' => 'View Dashboard Menu'],
            ],
            'Students' => [
                ['name' => 'menu_students', 'display_name' => 'View Students Menu'],
                ['name' => 'menu_student_list', 'display_name' => 'View Student List Submenu'],
                ['name' => 'menu_student_attendance', 'display_name' => 'View Student Attendance Submenu'],
                ['name' => 'menu_student_history', 'display_name' => 'View Student History Submenu'],
                ['name' => 'menu_student_promotion', 'display_name' => 'View Student Promotion Submenu'],
                ['name' => 'create_student', 'display_name' => 'Add Student'],
                ['name' => 'edit_student', 'display_name' => 'Edit Student'],
                ['name' => 'delete_student', 'display_name' => 'Delete Student'],
                ['name' => 'view_student', 'display_name' => 'View Student Details'],
                ['name' => 'student_promotion.promote', 'display_name' => 'Promote Students'],
                ['name' => 'student_promotion.rollback', 'display_name' => 'Rollback Promotions'],
            ],
            'Staff' => [
                ['name' => 'menu_staff', 'display_name' => 'View Staff Directory Menu'],
                ['name' => 'menu_staff_list', 'display_name' => 'View Staff List Submenu'],
                ['name' => 'menu_staff_attendance', 'display_name' => 'View Staff Attendance Submenu'],
                ['name' => 'create_staff_attendance', 'display_name' => 'Mark Staff Attendance'],
                ['name' => 'menu_staff_history', 'display_name' => 'View Staff History Submenu'],
                ['name' => 'create_staff', 'display_name' => 'Add Staff Member'],
                ['name' => 'edit_staff', 'display_name' => 'Edit Staff Member'],
                ['name' => 'delete_staff', 'display_name' => 'Delete Staff Member'],
            ],
            'Academics' => [
                ['name' => 'menu_academics', 'display_name' => 'View Academics Menu'],
                ['name' => 'menu_academic_class', 'display_name' => 'View Class Submenu'],
                ['name' => 'menu_academic_subjects', 'display_name' => 'View Subjects Submenu'],
                ['name' => 'menu_academic_routine', 'display_name' => 'View Class Routine Submenu'],
                ['name' => 'menu_academic_classroom', 'display_name' => 'View Class Room Submenu'],
                ['name' => 'menu_academic_exam', 'display_name' => 'View Exam Submenu'],
                ['name' => 'menu_academic_holidays', 'display_name' => 'View Holidays Submenu'],
                ['name' => 'menu_academic_marks', 'display_name' => 'View Add Marks Submenu'],
                ['name' => 'menu_academic_results', 'display_name' => 'View Result Submenu'],
                ['name' => 'create_academic', 'display_name' => 'Create Academic Data'],
                ['name' => 'edit_academic', 'display_name' => 'Edit Academic Data'],
                ['name' => 'delete_academic', 'display_name' => 'Delete Academic Data'],
            ],
            'Attendance' => [
                ['name' => 'menu_attendance', 'display_name' => 'View Attendance Menu'],
                ['name' => 'mark_attendance', 'display_name' => 'Mark Attendance'],
                ['name' => 'edit_attendance', 'display_name' => 'Edit Attendance'],
            ],
            'Biometric' => [
                ['name' => 'menu_biometric', 'display_name' => 'View Biometric Menu'],
                ['name' => 'view_biometric_log', 'display_name' => 'View Biometric Logs'],
                ['name' => 'manage_biometric', 'display_name' => 'Manage Biometric Devices'],
            ],
            'Fees' => [
                ['name' => 'menu_fees', 'display_name' => 'View Fees Menu'],
                ['name' => 'menu_fee_setup', 'display_name' => 'View Fee Setup Submenu'],
                ['name' => 'menu_fee_collection', 'display_name' => 'View Fee Collection Submenu'],
                ['name' => 'menu_fee_due_report', 'display_name' => 'View Due Report Submenu'],
                ['name' => 'create_fee', 'display_name' => 'Create Fee/Collection'],
                ['name' => 'edit_fee', 'display_name' => 'Edit Fee/Collection'],
                ['name' => 'delete_fee', 'display_name' => 'Delete Fee/Collection'],
            ],
            'Accounts' => [
                ['name' => 'menu_accounts', 'display_name' => 'View Accounts Menu'],
                ['name' => 'create_account', 'display_name' => 'Create Account Entry'],
                ['name' => 'edit_account', 'display_name' => 'Edit Account Entry'],
                ['name' => 'delete_account', 'display_name' => 'Delete Account Entry'],
            ],
            'Payroll' => [
                ['name' => 'menu_payroll', 'display_name' => 'View Payroll Menu'],
                ['name' => 'menu_payroll_process', 'display_name' => 'View Process Payroll Submenu'],
                ['name' => 'menu_payroll_history', 'display_name' => 'View Payroll History Submenu'],
                ['name' => 'create_payroll', 'display_name' => 'Create Payroll'],
                ['name' => 'edit_payroll', 'display_name' => 'Edit Payroll'],
                ['name' => 'delete_payroll', 'display_name' => 'Delete Payroll'],
            ],
            'Library' => [
                ['name' => 'menu_library', 'display_name' => 'View Library Menu'],
                ['name' => 'menu_book_catalog', 'display_name' => 'View Book Catalog Submenu'],
                ['name' => 'menu_book_issues', 'display_name' => 'View Book Issues Submenu'],
                ['name' => 'create_book', 'display_name' => 'Add Book'],
                ['name' => 'edit_book', 'display_name' => 'Edit Book'],
                ['name' => 'delete_book', 'display_name' => 'Delete Book'],
                ['name' => 'issue_book', 'display_name' => 'Issue Book'],
                ['name' => 'return_book', 'display_name' => 'Return Book'],
            ],
            'Hostel' => [
                ['name' => 'menu_hostel', 'display_name' => 'View Hostel Menu'],
                ['name' => 'menu_hostel_halls', 'display_name' => 'View Halls Submenu'],
                ['name' => 'menu_hostel_rooms', 'display_name' => 'View Rooms Submenu'],
                ['name' => 'menu_hostel_beds', 'display_name' => 'View Beds Submenu'],
                ['name' => 'menu_hostel_allocations', 'display_name' => 'View Allocations Submenu'],
                ['name' => 'menu_hostel_report', 'display_name' => 'View Hostel Report Submenu'],
                ['name' => 'create_hostel', 'display_name' => 'Add Hostel Data'],
                ['name' => 'edit_hostel', 'display_name' => 'Edit Hostel Data'],
                ['name' => 'delete_hostel', 'display_name' => 'Delete Hostel Data'],
            ],
            'Transport' => [
                ['name' => 'menu_transport', 'display_name' => 'View Transport Menu'],
                ['name' => 'menu_transport_routes', 'display_name' => 'View Routes & Stops Submenu'],
                ['name' => 'menu_transport_allocations', 'display_name' => 'View Allocations Submenu'],
                ['name' => 'menu_transport_history', 'display_name' => 'View Transport History Submenu'],
                ['name' => 'create_transport', 'display_name' => 'Add Transport Data'],
                ['name' => 'edit_transport', 'display_name' => 'Edit Transport Data'],
                ['name' => 'delete_transport', 'display_name' => 'Delete Transport Data'],
            ],
            'Food Management' => [
                ['name' => 'menu_food', 'display_name' => 'View Food Menu'],
                ['name' => 'menu_food_dashboard', 'display_name' => 'View Food Dashboard Submenu'],
                ['name' => 'menu_food_meals', 'display_name' => 'View Meal Setup Submenu'],
                ['name' => 'menu_food_plans', 'display_name' => 'View Food Fee Setup Submenu'],
                ['name' => 'menu_food_attendance', 'display_name' => 'View Food Attendance Submenu'],
                ['name' => 'menu_food_allocations', 'display_name' => 'View Food Allocation Submenu'],
                ['name' => 'menu_food_reports', 'display_name' => 'View Food Reports Submenu'],
                ['name' => 'menu_food_settings', 'display_name' => 'View Food Settings Submenu'],
                ['name' => 'create_food', 'display_name' => 'Add Food Data'],
                ['name' => 'edit_food', 'display_name' => 'Edit Food Data'],
                ['name' => 'delete_food', 'display_name' => 'Delete Food Data'],
            ],
            'Inventory' => [
                ['name' => 'menu_inventory', 'display_name' => 'View Inventory Menu'],
                ['name' => 'create_inventory', 'display_name' => 'Add Inventory Data'],
                ['name' => 'edit_inventory', 'display_name' => 'Edit Inventory Data'],
                ['name' => 'delete_inventory', 'display_name' => 'Delete Inventory Data'],
            ],
            'Notices' => [
                ['name' => 'menu_notices', 'display_name' => 'View Notices Menu'],
                ['name' => 'create_notice', 'display_name' => 'Add Notice'],
                ['name' => 'edit_notice', 'display_name' => 'Edit Notice'],
                ['name' => 'delete_notice', 'display_name' => 'Delete Notice'],
            ],
            // 'Certificates' => [
            //     ['name' => 'menu_certificates', 'display_name' => 'View Certificates Menu'],
            //     ['name' => 'create_certificate', 'display_name' => 'Add Certificate'],
            //     ['name' => 'issue_certificate', 'display_name' => 'Issue Certificate'],
            //     ['name' => 'delete_certificate', 'display_name' => 'Delete Certificate'],
            // ],
            'Reports' => [
                ['name' => 'manage_reports', 'display_name' => 'View Reports Menu'],
                ['name' => 'view_report', 'display_name' => 'View Report Details'],
                ['name' => 'create_report', 'display_name' => 'Generate/Create Report'],
                ['name' => 'edit_report', 'display_name' => 'Edit Report'],
                ['name' => 'delete_report', 'display_name' => 'Delete Report'],
            ],
            'Roles & Permissions' => [
                ['name' => 'menu_roles', 'display_name' => 'View Roles Menu'],
                ['name' => 'create_role', 'display_name' => 'Add Role'],
                ['name' => 'edit_role', 'display_name' => 'Edit Role & Assign Permissions'],
                ['name' => 'delete_role', 'display_name' => 'Delete Role'],
            ],
            'Settings' => [
                ['name' => 'menu_settings', 'display_name' => 'View Settings Menu'],
                ['name' => 'edit_settings', 'display_name' => 'Update Settings'],
            ],
            'SMS Management' => [
                ['name' => 'menu_sms', 'display_name' => 'View SMS Menu'],
                ['name' => 'create_sms', 'display_name' => 'Send/Create SMS'],
                ['name' => 'edit_sms', 'display_name' => 'Edit SMS Settings'],
                ['name' => 'delete_sms', 'display_name' => 'Delete SMS Log'],
            ],
            'Website CMS' => [
                ['name' => 'menu_cms', 'display_name' => 'View Website CMS Menu'],
                ['name' => 'create_cms', 'display_name' => 'Create CMS Content'],
                ['name' => 'edit_cms', 'display_name' => 'Edit CMS Content'],
                ['name' => 'delete_cms', 'display_name' => 'Delete CMS Content'],
            ],
        ];

        // Ensure all these permissions exist in the database
        $registeredGroups = [];
        foreach ($groups as $groupName => $perms) {
            $registeredPerms = [];
            foreach ($perms as $perm) {
                $registeredPerms[] = Permission::firstOrCreate(
                    ['name' => $perm['name']],
                    ['display_name' => $perm['display_name']]
                );
            }
            $registeredGroups[$groupName] = $registeredPerms;
        }

        return $registeredGroups;
    }
}
