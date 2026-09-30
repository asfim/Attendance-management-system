<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AdmissionApplication;
use App\Models\User;
use App\Models\Role;
use App\Models\ParentProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Repositories\Contracts\StudentRepositoryInterface;

class AdmissionApplicationController extends Controller
{
    protected StudentRepositoryInterface $studentRepository;

    public function __construct(StudentRepositoryInterface $studentRepository)
    {
        $this->studentRepository = $studentRepository;
    }

    public function index()
    {
        $applications = AdmissionApplication::with(['academicSession', 'schoolClass', 'section'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.admissions.index', compact('applications'));
    }

    public function show($id)
    {
        $application = AdmissionApplication::with(['academicSession', 'schoolClass', 'section'])->findOrFail($id);
        return view('admin.admissions.show', compact('application'));
    }

    public function merge(Request $request, $id)
    {
        $application = AdmissionApplication::findOrFail($id);

        if ($application->status === 'accepted') {
            return redirect()->back()->with('error', 'This application is already merged.');
        }

        $request->validate([
            'admission_no' => 'required|string|unique:student_profiles,admission_no',
            'roll_no' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        DB::transaction(function () use ($application, $request) {
            // Find or create Parent User
            $parentRole = Role::where('name', 'parent')->firstOrFail();
            $parentUser = User::where('email', $application->parent_email)->first();

            if (!$parentUser) {
                $parentUser = User::create([
                    'role_id' => $parentRole->id,
                    'name' => $application->parent_name,
                    'email' => $application->parent_email,
                    'password' => Hash::make('parent123'), // Default password
                ]);

                $parentProfile = ParentProfile::create([
                    'user_id' => $parentUser->id,
                    'phone' => $application->parent_phone,
                    'occupation' => $application->parent_occupation,
                    'address' => $application->parent_address,
                ]);
            } else {
                $parentProfile = $parentUser->parentProfile;
            }

            // Create Student User
            $studentRole = Role::where('name', 'student')->firstOrFail();
            
            // Fallback email if empty or already exists
            $studentEmail = $application->email;
            if (empty($studentEmail) || User::where('email', $studentEmail)->exists()) {
                $studentEmail = strtolower(str_replace(' ', '.', $application->name)) . rand(1000, 9999) . '@school.com';
            }

            $studentUser = User::create([
                'role_id' => $studentRole->id,
                'name' => $application->name,
                'email' => $studentEmail,
                'password' => Hash::make($request->password),
            ]);

            // Generate Mock QR Code
            $qrPayload = json_encode([
                'admission_no' => $request->admission_no,
                'name' => $application->name,
                'class' => $application->class_id,
            ]);

            // Create Student Profile
            $studentProfile = $this->studentRepository->create([
                'user_id' => $studentUser->id,
                'parent_id' => $parentProfile->id,
                'roll_no' => $request->roll_no,
                'session_id' => $application->session_id,
                'class_id' => $application->class_id,
                'section_id' => $application->section_id,
                'shift_id' => $application->shift_id,
                'admission_no' => $request->admission_no,
                'admission_date' => $application->admission_date,
                'dob' => $application->dob,
                'gender' => $application->gender,
                'blood_group' => $application->blood_group,
                'medical_info' => $application->medical_info,
                'qr_code' => $qrPayload,
                'photo_path' => $application->photo_path,
                'signature_path' => $application->signature_path,
                'plain_password' => $request->password,
                'status' => 'active',
            ]);

            $application->update(['status' => 'accepted']);
        });

        return redirect()->route('admin.admissions.index')->with('success', 'Application merged into student list successfully.');
    }
}
