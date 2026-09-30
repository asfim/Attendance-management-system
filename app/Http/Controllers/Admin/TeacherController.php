<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherRegisterRequest;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index()
    {
        $teachers = StaffProfile::whereHas('user.role', function($q) { $q->where('name', 'teacher'); })->with('user')->paginate(15);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(TeacherRegisterRequest $request)
    {
        DB::transaction(function () use ($request) {
            $teacherRole = Role::where('name', 'teacher')->firstOrFail();

            $user = User::create([
                'role_id' => $teacherRole->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            StaffProfile::create([
                'user_id' => $user->id,
                'phone' => $request->phone,
                'address' => $request->address,
                'qualifications' => $request->qualification,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary,
                'status' => 'active',
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher registered successfully!');
    }

    public function edit($id)
    {
        $teacher = StaffProfile::with('user')->findOrFail($id);
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        $teacher = StaffProfile::findOrFail($id);
        $user = $teacher->user;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'required|string',
            'address' => 'required|string',
            'qualification' => 'required|string',
            'designation' => 'required|string',
            'joining_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $teacher, $user) {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
            ]);

            $teacher->update([
                'phone' => $request->phone,
                'address' => $request->address,
                'qualification' => $request->qualification,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary,
            ]);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher profile updated.');
    }

    public function destroy($id)
    {
        $teacher = StaffProfile::findOrFail($id);
        $user = $teacher->user;
        $teacher->delete();
        $user->delete();

        return redirect()->route('admin.teachers.index')->with('success', 'Teacher removed.');
    }
}
