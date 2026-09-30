<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StudentAdmissionRequest;
use App\Repositories\Contracts\StudentRepositoryInterface;
use App\Repositories\Contracts\AcademicRepositoryInterface;
use App\Models\User;
use App\Models\Role;
use App\Models\ParentProfile;
use App\Models\StudentProfile;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    protected StudentRepositoryInterface $studentRepository;
    protected AcademicRepositoryInterface $academicRepository;

    public function __construct(
        StudentRepositoryInterface $studentRepository,
        AcademicRepositoryInterface $academicRepository
    ) {
        $this->studentRepository = $studentRepository;
        $this->academicRepository = $academicRepository;
    }

    public function index(Request $request)
    {
        $classes = $this->academicRepository->getAllClasses();
        $sessions = \App\Models\AcademicSession::all();

        $query = StudentProfile::with(['user', 'schoolClass', 'section']);

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('shift_id')) {
            $query->where('shift_id', $request->shift_id);
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($studentQuery) use ($search) {
                $studentQuery->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('admission_no', 'like', "%{$search}%");
            });
        }

        $students = $query->paginate(15);
        $shifts = Shift::where('status', 'active')->get();

        return view('admin.students.index', compact('students', 'classes', 'sessions', 'shifts'));
    }

    public function create()
    {
        $classes = $this->academicRepository->getAllClasses();
        $sessions = \App\Models\AcademicSession::all();
        $hostels = \App\Models\Hostel::all();
        $transportRoutes = \App\Models\TransportRoute::where('status', 'active')->get();
        $foodPlans = \App\Models\FoodPlan::where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        return view('admin.students.create', compact('classes', 'sessions', 'hostels', 'transportRoutes', 'foodPlans', 'shifts'));
    }

    public function store(StudentAdmissionRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Find or create Parent User
            $parentRole = Role::where('name', 'parent')->firstOrFail();
            $parentUser = User::where('email', $request->parent_email)->first();

            if (!$parentUser) {
                $parentUser = User::create([
                    'role_id' => $parentRole->id,
                    'name' => $request->parent_name,
                    'email' => $request->parent_email,
                    'password' => Hash::make('parent123'), // Default password
                ]);

                $parentProfile = ParentProfile::create([
                    'user_id' => $parentUser->id,
                    'phone' => $request->parent_phone,
                    'occupation' => $request->parent_occupation,
                    'address' => $request->parent_address,
                ]);
            } else {
                $parentProfile = $parentUser->parentProfile;
            }

            // Create Student User
            $studentRole = Role::where('name', 'student')->firstOrFail();
            $studentUser = User::create([
                'role_id' => $studentRole->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Generate Mock QR Code
            $qrPayload = json_encode([
                'admission_no' => $request->admission_no,
                'name' => $request->name,
                'class' => $request->class_id,
            ]);

            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('students', 'public');
            }

            $sigPath = null;
            if ($request->hasFile('signature')) {
                $sigPath = $request->file('signature')->store('signatures', 'public');
            }

            // Create Student Profile
            $studentProfile = $this->studentRepository->create([
                'user_id' => $studentUser->id,
                'parent_id' => $parentProfile->id,
                'roll_no' => $request->roll_no,
                'biometric_id' => $request->biometric_id,
                'session_id' => $request->session_id,
                'class_id' => $request->class_id,
                'section_id' => $request->section_id,
                'shift_id' => $request->shift_id,
                'admission_no' => $request->admission_no,
                'admission_date' => $request->admission_date,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'blood_group' => $request->blood_group,
                'medical_info' => $request->medical_info,
                'qr_code' => $qrPayload,
                'photo_path' => $photoPath,
                'signature_path' => $sigPath,
                'plain_password' => $request->password,
                'status' => 'active',
            ]);

            // Handle optional Hostel Allocation
            if ($request->has('assign_hostel') && $request->assign_hostel == '1' && $request->filled('bed_id')) {
                $bed = \App\Models\Bed::with('room')->find($request->bed_id);
                if ($bed && $bed->status === 'available') {
                    \App\Models\HostelAllocation::create([
                        'student_profile_id' => $studentProfile->id,
                        'bed_id' => $bed->id,
                        'allocation_date' => $request->admission_date,
                        'status' => 'active',
                    ]);

                    $bed->update(['status' => 'occupied']);

                    // Create Hostel Fee Structure
                    if ($bed->room) {
                        $feeCat = \App\Models\FeeCategory::firstOrCreate(
                            ['name' => 'Hostel Fee'],
                            ['type' => 'monthly', 'installments_count' => 12]
                        );

                        \App\Models\FeeStructure::firstOrCreate(
                            [
                                'class_id' => $studentProfile->class_id,
                                'fee_category_id' => $feeCat->id,
                            ],
                            [
                                'amount' => $bed->room->cost_per_bed,
                            ]
                        );
                    }
                }
            }
            // Handle optional Transport Allocation
            if ($request->has('assign_transport') && $request->assign_transport == '1' && $request->filled('route_id')) {
                $stopId = $request->filled('stop_id') ? $request->stop_id : null;
                $stop = \App\Models\TransportRouteStop::find($stopId);
                $fee = $stop ? $stop->additional_fee : 0.00;
                $effectiveFrom = $request->filled('transport_effective_from') ? $request->transport_effective_from : date('Y-m-d');

                \App\Models\TransportAllocation::create([
                    'student_profile_id' => $studentProfile->id,
                    'route_id' => $request->route_id,
                    'stop_id' => $stopId,
                    'monthly_fee' => $fee,
                    'effective_from' => $effectiveFrom,
                    'status' => 'active',
                ]);

                // Create Transport History log
                \App\Models\TransportHistory::create([
                    'student_profile_id' => $studentProfile->id,
                    'route_id' => $request->route_id,
                    'stop_id' => $stopId,
                    'action' => 'Allocated',
                    'new_value' => 'Status: Active, Fee: ' . $fee,
                    'action_date' => $effectiveFrom,
                    'reason' => 'Initial allocation during admission',
                    'performed_by' => auth()->id(),
                ]);

                // Create Transport Fee Structure if not exists
                $feeCat = \App\Models\FeeCategory::firstOrCreate(
                    ['name' => 'Transport Fee'],
                    ['type' => 'monthly', 'installments_count' => 12]
                );

                \App\Models\FeeStructure::firstOrCreate(
                    [
                        'class_id' => $studentProfile->class_id,
                        'fee_category_id' => $feeCat->id,
                    ],
                    [
                        'amount' => $fee,
                    ]
                );
            }

            // Handle optional Food Allocation
            $isFood = $request->has('is_food_enabled') && $request->is_food_enabled == '1';
            $studentProfile->update(['is_food_enabled' => $isFood]);

            if ($isFood && $request->filled('food_plan_id')) {
                $foodPlan = \App\Models\FoodPlan::find($request->food_plan_id);
                if ($foodPlan) {
                    \App\Models\StudentFood::create([
                        'student_profile_id' => $studentProfile->id,
                        'food_plan_id'       => $foodPlan->id,
                        'monthly_fee'        => $foodPlan->monthly_fee,
                        'start_date'         => $request->food_start_date ?? date('Y-m-d'),
                        'status'             => 'active',
                    ]);

                    app(\App\Services\FoodService::class)->generateMonthlyFoodFees(now()->month, now()->year);
                }
            }
        });

        return redirect()->route('admin.students.index')->with('success', 'Student admitted successfully!');
    }

    public function promote(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:student_profiles,id',
            'target_class_id' => 'required|exists:classes,id',
            'target_section_id' => 'required|exists:sections,id',
            'target_session_id' => 'required|exists:academic_sessions,id',
        ]);

        $promoted = $this->studentRepository->promoteStudents(
            $request->student_ids,
            $request->target_class_id,
            $request->target_section_id,
            $request->target_session_id
        );

        if ($promoted) {
            return back()->with('success', 'Selected students promoted successfully!');
        }

        return back()->with('error', 'Failed to promote students.');
    }

    public function show($id)
    {
        $student = StudentProfile::with([
            'user', 'schoolClass', 'section', 'parent.user', 'academicSession',
            'hostelAllocation.bed.room.hostel', 'transportAllocation.stop.route', 'foodAllocation.foodPlan'
        ])->findOrFail($id);

        // Attendance calendar data — grouped by year-month
        $attendances = $student->attendances()
            ->orderBy('attendance_date', 'asc')
            ->get(['attendance_date', 'status', 'remarks', 'late_reason']);

        $attendanceByDate = $attendances->keyBy(function ($a) {
            return \Carbon\Carbon::parse($a->attendance_date)->format('Y-m-d');
        });

        // Available months for the filter dropdown
        $attendanceMonths = $attendances->map(function ($a) {
            return \Carbon\Carbon::parse($a->attendance_date)->format('Y-m');
        })->unique()->values();

        // Default to current month or the last month with data
        $selectedMonth = request('att_month', $attendanceMonths->last() ?? now()->format('Y-m'));

        // Marks / Results
        $marksEntries = \App\Models\MarksEntry::with([
            'examSchedule.examType',
            'examSchedule.subject',
        ])->where('student_profile_id', $id)->get();

        // Group marks by exam type
        $marksByExam = $marksEntries->groupBy(function ($m) {
            return $m->examSchedule->examType->name ?? 'Unknown';
        });

        // Calculate GPA per exam type
        $gradeRules = \App\Models\GradeRule::orderBy('min_percent')->get();
        $examResults = [];
        foreach ($marksByExam as $examName => $marks) {
            $totalObtained = $marks->sum('marks_obtained');
            $totalMax = $marks->sum(function ($m) { return $m->examSchedule->max_marks ?? 0; });
            $percentage = $totalMax > 0 ? round(($totalObtained / $totalMax) * 100, 2) : 0;
            $grade = $gradeRules->first(function ($g) use ($percentage) {
                return $percentage >= $g->min_percent && $percentage <= $g->max_percent;
            });
            $examResults[$examName] = [
                'marks' => $marks,
                'totalObtained' => $totalObtained,
                'totalMax' => $totalMax,
                'percentage' => $percentage,
                'grade' => $grade,
            ];
        }

        return view('admin.students.show', compact(
            'student',
            'attendanceByDate',
            'attendanceMonths',
            'selectedMonth',
            'examResults',
            'marksEntries'
        ));
    }

    public function edit($id)
    {
        $student  = StudentProfile::with(['user', 'schoolClass', 'section', 'parent.user', 'academicSession', 'hostelAllocation.bed.room.hostel', 'transportAllocation.stop.route', 'foodAllocation.foodPlan'])->findOrFail($id);
        $classes  = $this->academicRepository->getAllClasses();
        $sessions = \App\Models\AcademicSession::all();
        $hostels  = \App\Models\Hostel::all();
        $transportRoutes = \App\Models\TransportRoute::where('status', 'active')->get();
        $foodPlans = \App\Models\FoodPlan::where('status', 'active')->get();
        $shifts = Shift::where('status', 'active')->get();
        return view('admin.students.edit', compact('student', 'classes', 'sessions', 'hostels', 'transportRoutes', 'foodPlans', 'shifts'));
    }

    public function update(Request $request, $id)
    {
        $student = StudentProfile::with(['user', 'parent', 'hostelAllocation.bed'])->findOrFail($id);

        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $student->user_id,
            'password'      => 'nullable|string|min:8',
            'roll_no'       => 'required',
            'class_id'      => 'required|exists:classes,id',
            'section_id'    => 'required|exists:sections,id',
            'shift_id'      => 'nullable|exists:shifts,id',
            'session_id'    => 'required|exists:academic_sessions,id',
            'dob'           => 'nullable|date',
            'gender'        => 'required|in:male,female,other',
            'blood_group'   => 'nullable|string|max:5',
            'status'        => 'required|in:active,inactive,transferred,graduated',
            'photo'         => 'nullable|image|max:2048',
            'signature'     => 'nullable|image|max:1024',
            'assign_hostel' => 'nullable|boolean',
            'hostel_id'     => 'nullable|required_if:assign_hostel,1',
            'room_id'       => 'nullable|required_if:assign_hostel,1',
            'bed_id'        => 'nullable|required_if:assign_hostel,1',
        ]);

        // Check if we are trying to cancel transport via unchecking
        if (!$request->has('assign_transport') || $request->assign_transport != '1') {
            if ($student->transportAllocation && $student->transportAllocation->status !== 'cancelled') {
                if ($this->checkTransportDue($student)) {
                    return back()->with('error', 'Cannot cancel transport allocation. The student has pending transport dues up to the current month. Please clear all dues first.');
                }
            }
        }

        // Check if we are trying to cancel food via unchecking
        if (!$request->has('is_food_enabled') || $request->is_food_enabled != '1') {
            if ($student->foodAllocation && $student->foodAllocation->status !== 'inactive') {
                if ($this->checkFoodDue($student)) {
                    return back()->with('error', 'Cannot disable food service. The student has pending food dues. Please clear all dues first.');
                }
            }
        }

        try {
            DB::transaction(function () use ($request, $student) {
                // Update user
                $userUpdate = [
                    'name'  => $request->name,
                    'email' => $request->email,
                ];
                if ($request->filled('password')) {
                    $userUpdate['password'] = bcrypt($request->password);
                }
                $student->user->update($userUpdate);

                // Update plain_password if a new password was provided
                if ($request->filled('password')) {
                    $student->update(['plain_password' => $request->password]);
                }

                $data = [
                    'roll_no'        => $request->roll_no,
                    'biometric_id'   => $request->biometric_id,
                    'class_id'       => $request->class_id,
                    'section_id'     => $request->section_id,
                    'shift_id'       => $request->shift_id,
                    'session_id'     => $request->session_id,
                    'admission_no'   => $request->admission_no,
                    'admission_date' => $request->admission_date,
                    'dob'            => $request->dob,
                    'gender'         => $request->gender,
                    'blood_group'    => $request->blood_group,
                    'medical_info'   => $request->medical_info,
                    'status'         => $request->status,
                ];

                if ($request->hasFile('photo')) {
                    $data['photo_path'] = $request->file('photo')->store('students', 'public');
                }
                if ($request->hasFile('signature')) {
                    $data['signature_path'] = $request->file('signature')->store('signatures', 'public');
                }

                $student->update($data);

                // Update parent if provided
                if ($student->parent) {
                    if ($request->filled('parent_phone')) {
                        $student->parent->update([
                            'phone'      => $request->parent_phone,
                            'occupation' => $request->parent_occupation,
                            'address'    => $request->parent_address,
                        ]);
                    }
                } else {
                    if ($request->filled('parent_name') && $request->filled('parent_email')) {
                        $parentRole = Role::where('name', 'parent')->first();
                        $parentUser = User::where('email', $request->parent_email)->first();
            
                        if (!$parentUser) {
                            $parentUser = User::create([
                                'role_id' => $parentRole->id,
                                'name' => $request->parent_name,
                                'email' => $request->parent_email,
                                'password' => \Hash::make('parent123'),
                            ]);
            
                            $parentProfile = ParentProfile::create([
                                'user_id' => $parentUser->id,
                                'phone' => $request->parent_phone,
                                'occupation' => $request->parent_occupation,
                                'address' => $request->parent_address,
                            ]);
                        } else {
                            $parentProfile = $parentUser->parentProfile;
                        }
                        
                        $student->update(['parent_id' => $parentProfile->id]);
                    }
                }

                // Handle Hostel Allocation in Student Edit
                $currentAlloc = $student->hostelAllocation;

                if ($request->has('assign_hostel') && $request->assign_hostel == '1' && $request->filled('bed_id')) {
                    $newBedId = $request->bed_id;

                    if (!$currentAlloc || $currentAlloc->bed_id != $newBedId) {
                        // Release old bed if allocated previously
                        if ($currentAlloc) {
                            if ($this->checkHostelDue($student)) {
                                throw new \Exception('Cannot change/release hostel allocation. The student has pending hostel dues up to the current month. Please clear all dues first.');
                            }
                            if ($currentAlloc->bed) {
                                $currentAlloc->bed->update(['status' => 'available']);
                            }
                            $currentAlloc->update(['status' => 'released']);
                        }

                        // Allocate new bed
                        $newBed = \App\Models\Bed::with('room')->find($newBedId);
                        if ($newBed && $newBed->status === 'available') {
                            \App\Models\HostelAllocation::create([
                                'student_profile_id' => $student->id,
                                'bed_id'             => $newBed->id,
                                'allocation_date'    => now()->format('Y-m-d'),
                                'status'             => 'active',
                            ]);

                            $newBed->update(['status' => 'occupied']);

                            // Create Hostel Fee Structure if not existing
                            if ($newBed->room) {
                                $feeCat = \App\Models\FeeCategory::firstOrCreate(
                                    ['name' => 'Hostel Fee'],
                                    ['description' => 'Hostel Accommodation Monthly/Term Fee']
                                );

                                \App\Models\FeeStructure::firstOrCreate(
                                    [
                                        'class_id'        => $student->class_id,
                                        'fee_category_id' => $feeCat->id,
                                    ],
                                    [
                                        'amount' => $newBed->room->cost_per_bed,
                                    ]
                                );
                            }
                        }
                    }
                } else {
                    // If switch is unchecked or no bed selected, release any existing hostel allocation
                    if ($currentAlloc) {
                        if ($this->checkHostelDue($student)) {
                            throw new \Exception('Cannot release student from hostel. They have pending hostel dues up to the current month. Please clear all dues first.');
                        }
                        if ($currentAlloc->bed) {
                            $currentAlloc->bed->update(['status' => 'available']);
                        }
                        $currentAlloc->update(['status' => 'released']);
                    }
                }

                // Handle Transport Allocation in Student Edit
                $currentTransport = $student->transportAllocation;

                if ($request->has('assign_transport') && $request->assign_transport == '1' && $request->filled('route_id')) {
                    $newRouteId = $request->route_id;
                    $newStopId = $request->filled('stop_id') ? $request->stop_id : null;
                    $stop = \App\Models\TransportRouteStop::find($newStopId);
                    $fee = $stop ? $stop->additional_fee : 0.00;
                    $effectiveFrom = $request->filled('transport_effective_from') ? $request->transport_effective_from : date('Y-m-d');

                    if (!$currentTransport || $currentTransport->route_id != $newRouteId || $currentTransport->stop_id != $newStopId) {

                        // Release old transport if allocated previously
                        if ($currentTransport) {
                            if ($this->checkTransportDue($student)) {
                                throw new \Exception('Cannot change transport allocation. The student has pending transport dues up to the current month. Please clear all dues first.');
                            }

                            $currentTransport->update([
                                'status' => 'cancelled',
                                'effective_to' => date('Y-m-d', strtotime('-1 day', strtotime($effectiveFrom)))
                            ]);

                            \App\Models\TransportHistory::create([
                                'student_profile_id' => $student->id,
                                'route_id' => $currentTransport->route_id,
                                'stop_id' => $currentTransport->stop_id,
                                'action' => 'Changed',
                                'old_value' => 'Route: ' . $currentTransport->route_id,
                                'new_value' => 'Route: ' . $newRouteId,
                                'action_date' => $effectiveFrom,
                                'reason' => 'Route changed via student edit',
                                'performed_by' => auth()->id(),
                            ]);
                        }

                        // Allocate new transport
                        \App\Models\TransportAllocation::create([
                            'student_profile_id' => $student->id,
                            'route_id' => $newRouteId,
                            'stop_id' => $newStopId,
                            'monthly_fee' => $fee,
                            'effective_from' => $effectiveFrom,
                            'status' => 'active',
                        ]);

                        if (!$currentTransport) {
                            \App\Models\TransportHistory::create([
                                'student_profile_id' => $student->id,
                                'route_id' => $newRouteId,
                                'stop_id' => $newStopId,
                                'action' => 'Allocated',
                                'new_value' => 'Status: Active, Fee: ' . $fee,
                                'action_date' => $effectiveFrom,
                                'reason' => 'Allocated via student edit',
                                'performed_by' => auth()->id(),
                            ]);
                        }

                        // Ensure Transport Fee Structure exists
                        $feeCat = \App\Models\FeeCategory::firstOrCreate(
                            ['name' => 'Transport Fee'],
                            ['description' => 'Transport Monthly Fee']
                        );

                        \App\Models\FeeStructure::firstOrCreate(
                            [
                                'class_id' => $student->class_id,
                                'fee_category_id' => $feeCat->id,
                            ],
                            [
                                'amount' => $fee,
                            ]
                        );
                    } else if ($currentTransport && $currentTransport->monthly_fee != $fee) {
                        // Just fee changed
                        $oldFee = $currentTransport->monthly_fee;
                        $currentTransport->update(['monthly_fee' => $fee]);

                        \App\Models\TransportHistory::create([
                            'student_profile_id' => $student->id,
                            'route_id' => $newRouteId,
                            'stop_id' => $newStopId,
                            'action' => 'Fee Updated',
                            'old_value' => 'Fee: ' . $oldFee,
                            'new_value' => 'Fee: ' . $fee,
                            'action_date' => date('Y-m-d'),
                            'reason' => 'Fee updated via student edit',
                            'performed_by' => auth()->id(),
                        ]);
                    }
                } else {
                    // If switch is unchecked, cancel any existing transport
                    if ($currentTransport && $currentTransport->status !== 'cancelled') {
                        $currentTransport->update([
                            'status' => 'cancelled',
                            'effective_to' => date('Y-m-d')
                        ]);

                        \App\Models\TransportHistory::create([
                            'student_profile_id' => $student->id,
                            'route_id' => $currentTransport->route_id,
                            'stop_id' => $currentTransport->stop_id,
                            'action' => 'Cancelled',
                            'new_value' => 'Status: Cancelled',
                            'action_date' => date('Y-m-d'),
                            'reason' => 'Transport disabled via student edit',
                            'performed_by' => auth()->id(),
                        ]);
                    }
                }

                // Handle Food Allocation in Student Edit
                $currentFood = $student->foodAllocation;
                $isFoodEnabled = $request->has('is_food_enabled') && $request->is_food_enabled == '1';
                
                $student->update(['is_food_enabled' => $isFoodEnabled]);
                
                if ($isFoodEnabled && $request->filled('food_plan_id')) {
                    $newPlanId = $request->food_plan_id;
                    $foodPlan = \App\Models\FoodPlan::find($newPlanId);
                    
                    if ($foodPlan) {
                        if (!$currentFood || $currentFood->food_plan_id != $newPlanId || $currentFood->status == 'inactive') {
                            if ($currentFood && $currentFood->status === 'active') {
                                // Deactivate old
                                $currentFood->update(['status' => 'inactive', 'end_date' => date('Y-m-d')]);
                            }
                            
                            \App\Models\StudentFood::create([
                                'student_profile_id' => $student->id,
                                'food_plan_id'       => $foodPlan->id,
                                'monthly_fee'        => $foodPlan->monthly_fee,
                                'start_date'         => $request->food_start_date ?? date('Y-m-d'),
                                'status'             => 'active',
                            ]);
                            
                            app(\App\Services\FoodService::class)->generateMonthlyFoodFees(now()->month, now()->year);
                        }
                    }
                } else {
                    if ($currentFood && $currentFood->status !== 'inactive') {
                        $currentFood->update([
                            'status' => 'inactive',
                            'end_date' => date('Y-m-d')
                        ]);
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->route('admin.students.show', $student->id)->with('error', $e->getMessage());
        }

        return redirect()->route('admin.students.show', $student->id)->with('success', 'Student profile updated successfully!');
    }

    public function destroy($id)
    {
        $profile = StudentProfile::findOrFail($id);
        $user = $profile->user;
        $profile->delete();
        $user->delete();

        return redirect()->route('admin.students.index')->with('success', 'Student profile deleted.');
    }

    private function checkTransportDue($student)
    {
        $transportCategory = \App\Models\FeeCategory::where('name', 'Transport Fee')->first();
        if (!$transportCategory) return false;

        foreach ($student->invoices as $invoice) {
            if ($invoice->status !== 'paid' && $invoice->status !== 'refunded') {
                foreach ($invoice->items as $item) {
                    if ($item->fee_category_id == $transportCategory->id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    private function checkFoodDue($student)
    {
        $foodCategory = \App\Models\FeeCategory::where('name', 'Food Fee')->first();
        if (!$foodCategory) return false;

        foreach ($student->invoices as $invoice) {
            if ($invoice->status !== 'paid' && $invoice->status !== 'refunded') {
                foreach ($invoice->items as $item) {
                    if ($item->fee_category_id == $foodCategory->id) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
