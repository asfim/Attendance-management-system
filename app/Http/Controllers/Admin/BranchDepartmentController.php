<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\StaffProfile;
use App\Models\EmployeeTransfer;
use Illuminate\Http\Request;

class BranchDepartmentController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['departments', 'staff'])->get();
        $departments = Department::with(['branch'])->withCount('staff')->get();
        $designations = Designation::with('department')->withCount('staff')->get();
        $transfers = EmployeeTransfer::with(['staff.user', 'fromBranch', 'toBranch', 'fromDepartment', 'toDepartment', 'approver'])
            ->latest()
            ->take(20)
            ->get();
        $allStaff = StaffProfile::with('user')->where('status', 'active')->get();

        return view('admin.attendance_software.branches.index', compact('branches', 'departments', 'designations', 'transfers', 'allStaff'));
    }

    // Branch Store
    public function storeBranch(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'code'    => 'nullable|string|max:50|unique:branches,code',
            'phone'   => 'nullable|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        Branch::create($request->all());

        return redirect()->back()->with('success', 'Branch created successfully!');
    }

    // Department Store
    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'code'      => 'nullable|string|max:50',
        ]);

        Department::create($request->all());

        return redirect()->back()->with('success', 'Department created successfully!');
    }

    // Designation Store
    public function storeDesignation(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        Designation::create($request->all());

        return redirect()->back()->with('success', 'Designation created successfully!');
    }

    // Employee Transfer
    public function storeTransfer(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'to_branch_id'     => 'nullable|exists:branches,id',
            'to_department_id' => 'nullable|exists:departments,id',
            'transfer_date'    => 'required|date',
            'reason'           => 'nullable|string|max:500',
        ]);

        $staff = StaffProfile::findOrFail($request->staff_profile_id);

        EmployeeTransfer::create([
            'staff_profile_id'   => $staff->id,
            'from_branch_id'     => $staff->branch_id,
            'to_branch_id'       => $request->to_branch_id,
            'from_department_id' => $staff->department_id,
            'to_department_id'   => $request->to_department_id,
            'transfer_date'      => $request->transfer_date,
            'reason'             => $request->reason,
            'approved_by'        => auth()->id(),
        ]);

        // Update staff profile with new branch & department
        if ($request->to_branch_id) {
            $staff->branch_id = $request->to_branch_id;
        }
        if ($request->to_department_id) {
            $staff->department_id = $request->to_department_id;
        }
        $staff->save();

        return redirect()->back()->with('success', 'Employee transferred successfully!');
    }
}
