<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffProfile::with(['user', 'user.role', 'allowances']);

        if ($request->has('role') && $request->role != 'all') {
            $query->whereHas('user.role', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('gender') && $request->gender != '') {
            $query->where('gender', $request->gender);
        }

        $staff = $query->paginate(15)->appends($request->all());
        $totalStaff = StaffProfile::count();
        
        $roles = Role::whereNotIn('name', ['student', 'parent', 'super_admin', 'admin', 'principal', 'vice_principal'])->get();
        
        return view('admin.staff.index', compact('staff', 'totalStaff', 'roles'));
    }

    public function create()
    {
        // Get roles that are typically considered staff roles
        $roles = Role::whereNotIn('name', ['student', 'parent', 'super_admin', 'admin', 'principal', 'vice_principal'])->get();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        return view('admin.staff.create', compact('roles', 'shifts'));
    }

    public function show($id)
    {
        $staff = StaffProfile::with(['user', 'user.role', 'allowances', 'shifts'])->findOrFail($id);
        return view('admin.staff.show', compact('staff'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'nullable|string|min:8',
            'biometric_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bangla_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'religion' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'national_id' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'status' => 'nullable|string',
            'experience' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'exists:shifts,id',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('staff_photos', 'public');
        }

        $signaturePath = null;
        if ($request->hasFile('signature_path')) {
            $signaturePath = $request->file('signature_path')->store('staff_signatures', 'public');
        }

        $generatedPassword = $request->password ?? \Illuminate\Support\Str::random(8);
        $generatedEmail = $request->email;
        
        if (empty($generatedEmail)) {
            $baseEmail = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $request->name));
            $generatedEmail = $baseEmail . rand(100, 999) . '@school.com';
            
            // Ensure unique
            while(User::where('email', $generatedEmail)->exists()) {
                $generatedEmail = $baseEmail . rand(100, 999) . '@school.com';
            }
        }
        
        // Find or create role
        $roleName = $request->role_name;
        $role = Role::where('display_name', $roleName)
                    ->orWhere('name', \Illuminate\Support\Str::slug($roleName))
                    ->first();
                    
        if (!$role) {
            $role = Role::create([
                'name' => \Illuminate\Support\Str::slug($roleName),
                'display_name' => $roleName,
                'description' => 'Automatically created staff role.'
            ]);
        }

        DB::transaction(function () use ($request, $photoPath, $signaturePath, $generatedEmail, $generatedPassword, $role) {
            $user = User::create([
                'role_id' => $role->id,
                'name' => $request->name,
                'email' => $generatedEmail,
                'password' => Hash::make($generatedPassword),
            ]);

            $staffProfile = StaffProfile::create([
                'user_id' => $user->id,
                'biometric_id' => $request->biometric_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'department' => $request->department,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary ?? 0,
                'status' => $request->status ?? 'active',
                'photo' => $photoPath,
                'signature_path' => $signaturePath,
                'bangla_name' => $request->bangla_name,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'marital_status' => $request->marital_status,
                'national_id' => $request->national_id,
                'permanent_address' => $request->permanent_address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'employment_type' => $request->employment_type,
                'experience' => $request->experience,
                'qualifications' => $request->qualifications,
            ]);

            if ($request->has('allowances') && is_array($request->allowances['name'] ?? null)) {
                $allowances = [];
                foreach ($request->allowances['name'] as $index => $name) {
                    if (!empty($name)) {
                        $allowances[] = [
                            'name' => $name,
                            'amount' => $request->allowances['amount'][$index] ?? 0,
                        ];
                    }
                }
                if (!empty($allowances)) {
                    $staffProfile->allowances()->createMany($allowances);
                }
            }

            if ($request->has('shift_ids')) {
                $staffProfile->shifts()->sync($request->shift_ids);
            }
        });

        return redirect()->route('admin.staff.index')->with('success', "Staff member created successfully! \nEmail: {$generatedEmail}\nPassword: {$generatedPassword}");
    }

    public function edit($id)
    {
        $staff = StaffProfile::with('user', 'allowances', 'shifts')->findOrFail($id);
        $roles = Role::whereNotIn('name', ['student', 'parent', 'super_admin', 'admin', 'principal', 'vice_principal'])->get();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        return view('admin.staff.edit', compact('staff', 'roles', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $staff = StaffProfile::findOrFail($id);
        $user = $staff->user;

        $request->validate([
            'role_name' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'biometric_id' => 'nullable|string|max:100',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'department' => 'nullable|string',
            'designation' => 'nullable|string',
            'joining_date' => 'required|date',
            'salary' => 'nullable|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signature_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'bangla_name' => 'nullable|string|max:255',
            'gender' => 'nullable|string',
            'dob' => 'nullable|date',
            'blood_group' => 'nullable|string',
            'religion' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'national_id' => 'nullable|string',
            'permanent_address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'employment_type' => 'nullable|string',
            'status' => 'nullable|string',
            'experience' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'shift_ids' => 'nullable|array',
            'shift_ids.*' => 'exists:shifts,id',
        ]);

        $photoPath = $staff->photo;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('staff_photos', 'public');
        }

        $signaturePath = $staff->signature_path;
        if ($request->hasFile('signature_path')) {
            if ($signaturePath && Storage::disk('public')->exists($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            $signaturePath = $request->file('signature_path')->store('staff_signatures', 'public');
        }

        // Find or create role
        $roleName = $request->role_name;
        $role = Role::where('display_name', $roleName)
                    ->orWhere('name', \Illuminate\Support\Str::slug($roleName))
                    ->first();
                    
        if (!$role) {
            $role = Role::create([
                'name' => \Illuminate\Support\Str::slug($roleName),
                'display_name' => $roleName,
                'description' => 'Automatically created staff role.'
            ]);
        }

        DB::transaction(function () use ($request, $photoPath, $signaturePath, $staff, $user, $role) {
            $userData = [
                'role_id' => $role->id,
                'name' => $request->name,
                'email' => $request->email,
            ];
            
            if ($request->filled('password')) {
                $userData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
            }

            $user->update($userData);

            $staff->update([
                'biometric_id' => $request->biometric_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'department' => $request->department,
                'designation' => $request->designation,
                'joining_date' => $request->joining_date,
                'salary' => $request->salary ?? 0,
                'status' => $request->status ?? 'active',
                'photo' => $photoPath,
                'signature_path' => $signaturePath,
                'bangla_name' => $request->bangla_name,
                'gender' => $request->gender,
                'dob' => $request->dob,
                'blood_group' => $request->blood_group,
                'religion' => $request->religion,
                'marital_status' => $request->marital_status,
                'national_id' => $request->national_id,
                'permanent_address' => $request->permanent_address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'employment_type' => $request->employment_type,
                'experience' => $request->experience,
                'qualifications' => $request->qualifications,
            ]);

            $staff->allowances()->delete();
            if ($request->has('allowances') && is_array($request->allowances['name'] ?? null)) {
                $allowances = [];
                foreach ($request->allowances['name'] as $index => $name) {
                    if (!empty($name)) {
                        $allowances[] = [
                            'name' => $name,
                            'amount' => $request->allowances['amount'][$index] ?? 0,
                        ];
                    }
                }
                if (!empty($allowances)) {
                    $staff->allowances()->createMany($allowances);
                }
            }

            if ($request->has('shift_ids')) {
                $staff->shifts()->sync($request->shift_ids);
            } else {
                $staff->shifts()->sync([]);
            }
        });

        return redirect()->route('admin.staff.index')->with('success', 'Staff profile updated.');
    }

    public function destroy($id)
    {
        $staff = StaffProfile::findOrFail($id);
        $user = $staff->user;
        
        DB::transaction(function () use ($staff, $user) {
            $staff->delete();
            $user->delete();
        });

        return redirect()->route('admin.staff.index')->with('success', 'Staff member deleted.');
    }
}
