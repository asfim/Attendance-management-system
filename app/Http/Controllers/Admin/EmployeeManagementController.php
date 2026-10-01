<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Shift;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EmployeeManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $branchId = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $query = StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel', 'shifts']);

        if ($search) {
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('biometric_id', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        }

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $employees = $query->orderBy('id', 'desc')->paginate(15);

        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();

        return view('admin.attendance_software.employees.index', compact('employees', 'branches', 'departments', 'designations', 'shifts', 'search', 'branchId', 'departmentId', 'status'));
    }

    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();

        return view('admin.attendance_software.employees.create', compact('branches', 'departments', 'designations', 'shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:6',
            'phone'          => 'required|string|max:20',
            'branch_id'      => 'nullable|exists:branches,id',
            'department_id'  => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date'   => 'required|date',
            'biometric_id'   => 'nullable|string|max:50',
            'fingerprint_id' => 'nullable|string|max:50',
            'face_id'        => 'nullable|string|max:50',
            'salary'         => 'required|numeric|min:0',
            'overtime_rate'  => 'nullable|numeric|min:0',
            'photo'          => 'nullable|image|max:2048',
            'shift_id'       => 'nullable|exists:shifts,id',
        ]);

        $staffRole = \App\Models\Role::where('name', 'staff')->first();

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role_id'  => $staffRole ? $staffRole->id : 2,
            'status'   => $request->input('status', 'active'),
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff_photos', 'public');
        }

        $profile = StaffProfile::create([
            'user_id'        => $user->id,
            'branch_id'      => $request->branch_id,
            'department_id'  => $request->department_id,
            'designation_id' => $request->designation_id,
            'biometric_id'   => $request->biometric_id ?? (string)(1000 + $user->id),
            'fingerprint_id' => $request->fingerprint_id,
            'face_id'        => $request->face_id,
            'phone'          => $request->phone,
            'department'     => 'IT',
            'designation'    => 'Staff',
            'joining_date'   => $request->joining_date,
            'salary'         => $request->salary,
            'overtime_rate'  => $request->overtime_rate ?? 0,
            'status'         => $request->input('status', 'active'),
            'photo'          => $photoPath,
        ]);

        if ($request->shift_id) {
            $profile->shifts()->sync([$request->shift_id]);
        }

        // Initialize Leave Balance for current year
        LeaveBalance::create([
            'staff_profile_id'     => $profile->id,
            'year'                 => now()->year,
            'casual_leave_quota'   => 10,
            'casual_leave_used'    => 0,
            'sick_leave_quota'     => 14,
            'sick_leave_used'      => 0,
            'annual_leave_quota'   => 15,
            'annual_leave_used'    => 0,
            'emergency_leave_quota' => 5,
            'emergency_leave_used' => 0,
        ]);

        return redirect()->route('admin.attendance-suite.employees.index')->with('success', 'Employee registered successfully!');
    }

    public function show($id)
    {
        $employee = StaffProfile::with(['user', 'branch', 'departmentRel', 'designationRel', 'shifts', 'currentLeaveBalance', 'attendances' => function($q) {
            $q->orderBy('attendance_date', 'desc')->take(30);
        }])->findOrFail($id);

        return view('admin.attendance_software.employees.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee = StaffProfile::with(['user', 'shifts'])->findOrFail($id);
        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();
        $designations = Designation::where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();

        return view('admin.attendance_software.employees.edit', compact('employee', 'branches', 'departments', 'designations', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $employee = StaffProfile::with('user')->findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $employee->user_id,
            'password'       => 'nullable|string|min:6',
            'phone'          => 'required|string|max:20',
            'branch_id'      => 'nullable|exists:branches,id',
            'department_id'  => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date'   => 'required|date',
            'biometric_id'   => 'nullable|string|max:50',
            'fingerprint_id' => 'nullable|string|max:50',
            'face_id'        => 'nullable|string|max:50',
            'salary'         => 'required|numeric|min:0',
            'overtime_rate'  => 'nullable|numeric|min:0',
            'photo'          => 'nullable|image|max:2048',
            'shift_id'       => 'nullable|exists:shifts,id',
        ]);

        $user = clone $employee->user;
        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->status = $request->input('status', 'active');
        $user->save();

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $employee->photo = $request->file('photo')->store('staff_photos', 'public');
        }

        $employee->branch_id = $request->branch_id;
        $employee->department_id = $request->department_id;
        $employee->designation_id = $request->designation_id;
        $employee->biometric_id = $request->biometric_id;
        $employee->fingerprint_id = $request->fingerprint_id;
        $employee->face_id = $request->face_id;
        $employee->phone = $request->phone;
        $employee->joining_date = $request->joining_date;
        $employee->salary = $request->salary;
        $employee->overtime_rate = $request->overtime_rate ?? 0;
        $employee->status = $request->input('status', 'active');
        $employee->save();

        if ($request->shift_id) {
            $employee->shifts()->sync([$request->shift_id]);
        } else {
            $employee->shifts()->detach();
        }

        return redirect()->route('admin.attendance-suite.employees.index')->with('success', 'Employee updated successfully!');
    }

    public function toggleStatus($id)
    {
        $employee = StaffProfile::findOrFail($id);
        $employee->status = ($employee->status === 'active') ? 'inactive' : 'active';
        $employee->save();

        if ($employee->user) {
            $employee->user->status = $employee->status;
            $employee->user->save();
        }

        return redirect()->back()->with('success', "Employee status changed to {$employee->status}!");
    }
}
