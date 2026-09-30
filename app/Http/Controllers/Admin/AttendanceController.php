<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AttendanceService;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index(Request $request)
    {
        $classes = SchoolClass::with('sections')->get();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        $date = $request->input('date', now()->format('Y-m-d'));
        $students = collect();

        // If no class_id or section_id is provided, default to the class with the highest total attendance
        if (!$request->filled('class_id') || !$request->filled('section_id')) {
            $highestClassQuery = StudentProfile::join('attendances', function ($join) {
                    $join->on('student_profiles.id', '=', 'attendances.attendable_id')
                         ->where('attendances.attendable_type', '=', StudentProfile::class)
                         ->where('attendances.status', '=', 'present');
                })
                ->select('student_profiles.class_id', \DB::raw('count(attendances.id) as attendance_count'))
                ->groupBy('student_profiles.class_id')
                ->orderBy('attendance_count', 'desc')
                ->first();

            if ($highestClassQuery) {
                $classId = $highestClassQuery->class_id;
                $firstSection = Section::where('class_id', $classId)->first();
                if ($firstSection) {
                    $request->merge([
                        'class_id' => $classId,
                        'section_id' => $firstSection->id,
                    ]);
                }
            } else {
                // Fallback to first class and its section if no records exist yet
                $firstClass = SchoolClass::first();
                if ($firstClass) {
                    $firstSection = Section::where('class_id', $firstClass->id)->first();
                    if ($firstSection) {
                        $request->merge([
                            'class_id' => $firstClass->id,
                            'section_id' => $firstSection->id,
                        ]);
                    }
                }
            }
        }

        if ($request->filled('class_id') && $request->filled('section_id')) {
            $startOfMonth = \Carbon\Carbon::parse($date)->startOfMonth()->format('Y-m-d');
            $endOfMonth = \Carbon\Carbon::parse($date)->endOfMonth()->format('Y-m-d');

            $query = StudentProfile::where('class_id', $request->class_id)
                ->where('section_id', $request->section_id)
                ->where('status', 'active')
                ->with(['user', 'schoolClass', 'section', 'attendances' => function ($q) use ($date) {
                    $q->where('attendance_date', $date);
                }])
                ->withCount([
                    'attendances as total_p' => function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])->where('status', 'present');
                    },
                    'attendances as total_a' => function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])->where('status', 'absent');
                    },
                    'attendances as total_l' => function ($q) use ($startOfMonth, $endOfMonth) {
                        $q->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])->where('status', 'late');
                    }
                ]);
                
            if ($request->filled('shift_id')) {
                $query->where('shift_id', $request->shift_id);
            }
            
            $students = $query->orderBy('roll_no')->get();
        }

        return view('admin.attendance.index', compact('classes', 'shifts', 'students', 'date'));
    }

    public function history(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        
        $classes = SchoolClass::with('sections')->get();
        $history = collect();

        foreach ($classes as $cls) {
            foreach ($cls->sections as $sec) {
                // Get students for this class and section
                $students = StudentProfile::where('class_id', $cls->id)
                    ->where('section_id', $sec->id)
                    ->where('status', 'active')
                    ->get();

                $totalStudents = $students->count();
                if ($totalStudents == 0) continue;

                // Get attendance for these students on the selected date
                $studentIds = $students->pluck('id')->toArray();
                
                $attendances = Attendance::where('attendable_type', StudentProfile::class)
                    ->whereIn('attendable_id', $studentIds)
                    ->whereDate('attendance_date', $date)
                    ->get();

                $present = $attendances->where('status', 'present')->count();
                $late = $attendances->where('status', 'late')->count();
                $absent = $attendances->where('status', 'absent')->count();

                $history->push([
                    'class_name' => $cls->name,
                    'section_name' => $sec->name,
                    'total_students' => $totalStudents,
                    'present' => $present,
                    'late' => $late,
                    'absent' => $absent
                ]);
            }
        }

        return view('admin.attendance.history', compact('history', 'date'));
    }

    public function bulkSave(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*' => 'in:present,absent,late,leave,half_day',
            'remarks' => 'nullable|array',
            'remarks.*' => 'nullable|string|max:255',
        ]);

        $date = $request->input('date');
        $attendances = $request->input('attendance');
        $remarks = $request->input('remarks', []);
        
        $savedCount = 0;

        \DB::transaction(function () use ($date, $attendances, $remarks, &$savedCount) {
            foreach ($attendances as $studentId => $status) {
                if (in_array($status, ['present', 'absent', 'late', 'leave', 'half_day'])) {
                    Attendance::updateOrCreate(
                        [
                            'attendable_type' => StudentProfile::class,
                            'attendable_id'   => $studentId,
                            'attendance_date' => $date,
                        ],
                        [
                            'status'      => $status,
                            'remarks'     => $remarks[$studentId] ?? null,
                            'approved_by' => auth()->id(),
                        ]
                    );
                    $savedCount++;
                }
            }
        });

        return redirect()->back()->with('success', "Attendance saved for {$savedCount} students on " . \Carbon\Carbon::parse($date)->format('M d, Y') . ".");
    }

    public function calendar(Request $request, int $studentId): \Illuminate\Http\JsonResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $student = StudentProfile::findOrFail($studentId);

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::where('attendable_type', StudentProfile::class)
            ->where('attendable_id', $studentId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        // Fetch Global Holidays
        $globalHolidays = \App\Models\Holiday::whereBetween('date', [$start->copy()->subDays(7)->format('Y-m-d'), $end->copy()->addDays(7)->format('Y-m-d')])
            ->pluck('name', 'date')
            ->toArray();

        // Build calendar weeks
        $weeks  = [];
        $cursor = $start->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        while (true) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dateStr    = $cursor->format('Y-m-d');
                $inMonth    = $cursor->month === $month;
                $attendance = $attendances[$dateStr] ?? null;
                $isGlobalHoliday = isset($globalHolidays[$dateStr]);

                $status = $attendance?->status;
                $color = $attendance?->calendarColor();

                if (!$attendance) {
                    if ($isGlobalHoliday) {
                        $status = 'holiday';
                        $color = '#6b7280';
                    } elseif ($cursor->isWeekend()) {
                        $status = 'holiday';
                        $color = '#6b7280';
                    }
                }

                $week[]     = [
                    'date'      => $dateStr,
                    'day'       => $cursor->day,
                    'in_month'  => $inMonth,
                    'is_today'  => $cursor->isToday(),
                    'is_future' => $cursor->isFuture(),
                    'status'    => $status,
                    'color'     => $color,
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
            if ($cursor->gt($end) && $cursor->dayOfWeek === \Carbon\Carbon::MONDAY) break;
        }

        // Summary counts
        $summary = [
            'present' => $attendances->where('status', 'present')->count(),
            'absent'  => $attendances->where('status', 'absent')->count(),
            'late'    => $attendances->where('status', 'late')->count(),
            'half'    => $attendances->where('status', 'half_day')->count(),
            'leave'   => $attendances->where('status', 'leave')->count(),
        ];

        return response()->json([
            'weeks'   => $weeks,
            'summary' => $summary,
        ]);
    }

    public function getDay(int $studentId, string $date): \Illuminate\Http\JsonResponse
    {
        $student = StudentProfile::with('user')->findOrFail($studentId);
        $attendance = Attendance::where('attendable_type', StudentProfile::class)
            ->where('attendable_id', $studentId)
            ->whereDate('attendance_date', $date)
            ->first();

        return response()->json([
            'date'       => $date,
            'attendance' => $attendance,
        ]);
    }

    public function saveDay(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'student_id'       => 'required|exists:student_profiles,id',
            'date'             => 'required|date',
            'status'           => 'required|in:present,absent,late,half_day,leave,holiday',
            'remarks'          => 'nullable|string|max:500',
            'late_reason'      => 'nullable|string|max:500',
            'leave_reason'     => 'nullable|string|max:500',
            'leave_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $studentId       = $request->student_id;
        $attachmentPath  = null;

        if ($request->hasFile('leave_attachment')) {
            $attachmentPath = $request->file('leave_attachment')->store('leave_attachments', 'public');
        }

        $data = [
            'status'           => $request->status,
            'remarks'          => $request->remarks,
            'late_reason'      => $request->late_reason,
            'leave_reason'     => $request->leave_reason,
            'approved_by'      => auth()->user()->name ?? 'System',
        ];

        if ($attachmentPath) {
            $data['leave_attachment'] = $attachmentPath;
        }

        $attendance = Attendance::updateOrCreate(
            [
                'attendable_type' => StudentProfile::class,
                'attendable_id'   => $studentId,
                'attendance_date' => $request->date,
            ],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_profile_id' => 'required|exists:student_profiles,id',
            'attendance.*.status' => 'required|in:present,absent,late,half_day',
            'attendance.*.remarks' => 'nullable|string|max:255',
        ]);

        $attendanceData = [];
        foreach ($request->attendance as $att) {
            $attendanceData[] = [
                'attendable_id' => $att['student_profile_id'],
                'status' => $att['status'],
                'remarks' => $att['remarks'] ?? null,
            ];
        }

        $this->attendanceService->takeBulkAttendance(
            StudentProfile::class,
            $attendanceData,
            $request->date
        );

        return back()->with('success', 'Attendance recorded successfully!');
    }

    public function report(Request $request)
    {
        $classes = SchoolClass::all();
        $shifts = \App\Models\Shift::where('status', 'active')->get();
        $report = [];

        if ($request->filled('class_id') && $request->filled('section_id') && $request->filled('date')) {
            $report = $this->attendanceService->getAttendanceReport(
                StudentProfile::class,
                $request->class_id,
                $request->section_id,
                $request->date,
                $request->shift_id
            );
        }

        return view('admin.attendance.report', compact('classes', 'shifts', 'report'));
    }

    public function studentHistory($id)
    {
        $student = StudentProfile::with(['user', 'attendances' => function ($query) {
            $query->orderBy('attendance_date', 'desc');
        }])->findOrFail($id);

        return view('admin.attendance.partials.history_modal', compact('student'));
    }

    public function biometricLogs(Request $request)
    {
        $type = $request->input('type', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        
        // Default to today if no dates provided and not searching all
        if (!$startDate && !$endDate && !$request->has('search') && !$request->has('start_date')) {
            $startDate = now()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        $search = $request->input('search');

        $query = \App\Models\BiometricDeviceLog::orderBy('punch_time', 'desc');

        if ($type === 'student') {
            $query->where('user_type', 'student');
        } elseif ($type === 'staff') {
            $query->where('user_type', 'staff');
        }

        if ($startDate) {
            $query->where('punch_time', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate) {
            $query->where('punch_time', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('biometric_id', 'like', "%{$search}%")
                  ->orWhere('device_sn', 'like', "%{$search}%");
            });
        }

        if ($request->input('download') === 'csv') {
            $allLogs = $query->get();
            
            $studentIds = $allLogs->where('user_type', 'student')->pluck('biometric_id')->unique()->toArray();
            $staffIds = $allLogs->where('user_type', 'staff')->pluck('biometric_id')->unique()->toArray();
            
            $students = \App\Models\StudentProfile::with('shift')->whereIn('biometric_id', $studentIds)->get()->keyBy('biometric_id');
            $staffs = \App\Models\StaffProfile::with('shifts')->whereIn('biometric_id', $staffIds)->get()->keyBy('biometric_id');
            
            $csvData = [];
            $csvData[] = ['Log ID', 'Biometric ID', 'Name', 'User Type', 'Assigned In Time', 'Assigned Out Time', 'Punch Time', 'State', 'Late Time'];
            
            foreach ($allLogs as $log) {
                $shiftStartStr = 'N/A';
                $shiftEndStr = 'N/A';
                $state = 'Punch';
                $lateText = 'N/A';
                
                $shift = null;
                
                if ($log->user_type === 'student' && isset($students[$log->biometric_id])) {
                    $shift = $students[$log->biometric_id]->shift;
                } elseif ($log->user_type === 'staff' && isset($staffs[$log->biometric_id])) {
                    $staffObj = $staffs[$log->biometric_id];
                    $shifts = $staffObj->shifts->sortBy('start_time');
                    if ($shifts->count() > 0) {
                        $shift = new \stdClass();
                        $shift->start_time = $shifts->first()->start_time;
                        $shift->end_time = $shifts->last()->end_time;
                    }
                }
                
                if ($shift && $log->punch_time) {
                    $shiftStartStr = \Carbon\Carbon::parse($shift->start_time)->format('h:i A');
                    $shiftEndStr = \Carbon\Carbon::parse($shift->end_time)->format('h:i A');
                    
                    $punchTime = clone $log->punch_time;
                    $date = $punchTime->format('Y-m-d');
                    $shiftStart = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
                    $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);
                    
                    $midpoint = $shiftStart->copy()->addMinutes($shiftStart->diffInMinutes($shiftEnd) / 2);
                    
                    if ($punchTime <= $midpoint) {
                        $state = 'Check-In';
                        $lateDiff = $shiftStart->diffInMinutes($punchTime, false);
                        if ($lateDiff > 0) {
                            $hours = floor($lateDiff / 60);
                            $mins = $lateDiff % 60;
                            $lateText = '';
                            if ($hours > 0) $lateText .= $hours . ' HR ';
                            $lateText .= $mins . ' MIN';
                        } else {
                            $lateText = 'On Time';
                        }
                    } else {
                        $state = 'Check-Out';
                    }
                }
                
                $csvData[] = [
                    $log->id,
                    $log->biometric_id,
                    $log->user_name,
                    ucfirst($log->user_type),
                    $shiftStartStr,
                    $shiftEndStr,
                    $log->punch_time ? $log->punch_time->format('d M Y, h:i:s A') : '',
                    $state,
                    $lateText,
                ];
            }
            
            $filename = "biometric_logs_" . ($startDate ?? 'all') . "_to_" . ($endDate ?? 'all') . ".csv";
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            
            $callback = function() use($csvData) {
                $file = fopen('php://output', 'w');
                foreach ($csvData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $logs = $query->paginate(10)->appends($request->all());

        // Stats calculation based on current filter or selected date
        $statsQuery = \App\Models\BiometricDeviceLog::query();
        if ($startDate) $statsQuery->where('punch_time', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
        if ($endDate) $statsQuery->where('punch_time', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
        
        if ($type === 'student') {
            $statsQuery->where('user_type', 'student');
        } elseif ($type === 'staff') {
            $statsQuery->where('user_type', 'staff');
        }

        $totalPunchesToday = (clone $statsQuery)->count();
        $successfulPunchesToday = (clone $statsQuery)->where('status', 'success')->count();
        $failedPunchesToday = (clone $statsQuery)->where('status', 'failed')->count();

        if ($request->ajax()) {
            $html = view('admin.attendance.partials.biometric_log_rows', compact('logs'))->render();
            return response()->json([
                'success' => true,
                'html' => $html,
                'has_more' => $logs->hasMorePages(),
                'next_page' => $logs->currentPage() + 1,
                'total_punches' => number_format($totalPunchesToday),
                'success_punches' => number_format($successfulPunchesToday),
                'failed_punches' => number_format($failedPunchesToday),
            ]);
        }

        return view('admin.attendance.biometric_logs', compact(
            'logs', 
            'totalPunchesToday', 
            'successfulPunchesToday', 
            'failedPunchesToday', 
            'type', 
            'startDate',
            'endDate',
            'search'
        ));
    }

    public function directZkSync(Request $request)
    {
        $request->validate([
            'device_ip' => 'required|string',
            'device_port' => 'nullable|integer',
        ]);

        $ip = trim($request->input('device_ip'));
        $port = (int) $request->input('device_port', 4370);

        try {
            $zk = new \App\Services\ZKTecoService($ip, $port);
            if (!$zk->connect()) {
                return response()->json([
                    'success' => false,
                    'message' => "Could not connect to ZKTeco device at {$ip}:{$port}. Please verify device IP and LAN connection."
                ], 422);
            }

            $logs = $zk->getAttendanceLogs();
            $processed = 0;

            foreach ($logs as $log) {
                $biometricId = $log['device_user_id'];
                $punchTimeStr = $log['timestamp'];

                try {
                    $punchTime = \Illuminate\Support\Carbon::parse($punchTimeStr);
                    $dateStr = $punchTime->format('Y-m-d');
                    $timeStr = $punchTime->format('H:i:s');

                    $existingLog = \App\Models\BiometricDeviceLog::where('biometric_id', $biometricId)
                        ->where('punch_time', $punchTime->toDateTimeString())
                        ->first();

                    if ($existingLog) {
                        continue;
                    }

                    $profile = \App\Models\StudentProfile::where('biometric_id', $biometricId)
                        ->orWhere('admission_no', $biometricId)
                        ->orWhere('roll_no', $biometricId)
                        ->first();
                    $userCategory = 'student';
                    $attendableType = \App\Models\StudentProfile::class;

                    if (!$profile) {
                        $profile = \App\Models\StaffProfile::where('biometric_id', $biometricId)
                            ->orWhere('phone', $biometricId)
                            ->first();
                        $userCategory = 'staff';
                        $attendableType = \App\Models\StaffProfile::class;
                    }

                    if ($profile) {
                        $existingAttendance = Attendance::whereDate('attendance_date', $dateStr)
                            ->where('attendable_type', $attendableType)
                            ->where('attendable_id', $profile->id)
                            ->first();

                        $lateCutoff = '09:15:00';
                        $action = 'check_in';
                        $status = 'present';

                        if (!$existingAttendance) {
                            $status = ($timeStr > $lateCutoff) ? 'late' : 'present';
                            Attendance::create([
                                'attendance_date' => $dateStr,
                                'entry_time' => $timeStr,
                                'attendable_type' => $attendableType,
                                'attendable_id' => $profile->id,
                                'status' => $status,
                                'remarks' => "Web Direct ZKTeco Sync at " . $punchTime->format('h:i A'),
                            ]);
                        } else {
                            $action = 'check_out';
                            $existingAttendance->update([
                                'exit_time' => $timeStr,
                                'remarks' => ($existingAttendance->remarks ? $existingAttendance->remarks . ' | ' : '') . "Web Direct ZKTeco Exit at " . $punchTime->format('h:i A'),
                            ]);
                        }

                        $userName = $profile->user->name ?? 'User #' . $profile->id;
                        \App\Models\BiometricDeviceLog::create([
                            'device_sn' => 'ZK_' . $ip,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => $action,
                            'user_type' => $userCategory,
                            'user_name' => $userName,
                            'status' => 'success',
                            'message' => "Web LAN direct punch recorded as {$status} ({$action})",
                            'raw_payload' => json_encode($log),
                            'ip_address' => $ip,
                        ]);

                        $processed++;
                    } else {
                        \App\Models\BiometricDeviceLog::create([
                            'device_sn' => 'ZK_' . $ip,
                            'biometric_id' => $biometricId,
                            'punch_time' => $punchTime,
                            'punch_state' => 'check_in',
                            'user_type' => 'unknown',
                            'status' => 'failed',
                            'message' => "Unrecognized Biometric ID: {$biometricId}",
                            'raw_payload' => json_encode($log),
                            'ip_address' => $ip,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error("ZKTeco Web Sync error: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully fetched device logs! Processed {$processed} new attendance records.",
                'processed_count' => $processed,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to device: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function liveMonitor(Request $request)
    {
        return view('admin.attendance.live_monitor');
    }

    public function liveFeed(Request $request)
    {
        $lastLog = \App\Models\BiometricDeviceLog::where('status', 'success')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastLog) {
            return response()->json([
                'has_punch' => false,
            ]);
        }

        $details = [
            'id' => $lastLog->id,
            'biometric_id' => $lastLog->biometric_id,
            'punch_time' => $lastLog->punch_time ? $lastLog->punch_time->format('h:i:s A') : '',
            'punch_date' => $lastLog->punch_time ? $lastLog->punch_time->format('d M, Y') : '',
            'user_type' => $lastLog->user_type ?? 'student',
            'name' => $lastLog->user_name ?? 'Student',
            'photo_url' => asset('images/default-avatar.png'),
            'class_name' => '',
            'section_name' => '',
            'roll_no' => '',
            'admission_no' => '',
            'department' => '',
            'designation' => '',
            'attendance_status' => 'present',
            'late_minutes' => 0,
        ];

        // Enrich with Student or Staff details
        $shift = null;
        if ($lastLog->user_type === 'student') {
            $student = \App\Models\StudentProfile::with(['user', 'schoolClass', 'section', 'shift'])
                ->where('biometric_id', $lastLog->biometric_id)
                ->orWhere('admission_no', $lastLog->biometric_id)
                ->orWhere('roll_no', $lastLog->biometric_id)
                ->first();

            if ($student) {
                $shift = $student->shift;
                $details['name'] = $student->user->name ?? $lastLog->user_name;
                $details['class_name'] = $student->schoolClass->name ?? '';
                $details['section_name'] = $student->section->name ?? '';
                $details['roll_no'] = $student->roll_no ?? '';
                $details['admission_no'] = $student->admission_no ?? '';
                if ($student->photo_path) {
                    $details['photo_url'] = asset('storage/' . $student->photo_path);
                }
            }
        } elseif ($lastLog->user_type === 'staff') {
            $staff = \App\Models\StaffProfile::with(['user', 'shifts'])
                ->where('biometric_id', $lastLog->biometric_id)
                ->orWhere('phone', $lastLog->biometric_id)
                ->first();

            if ($staff) {
                $shifts = $staff->shifts->sortBy('start_time');
                if ($shifts->count() > 0) {
                    $firstShift = $shifts->first();
                    $lastShift = $shifts->last();
                    
                    $shift = new \stdClass();
                    $shift->name = $firstShift->name . ($shifts->count() > 1 ? ' & Others' : '');
                    $shift->start_time = $firstShift->start_time;
                    $shift->end_time = $lastShift->end_time;
                }
                
                $details['name'] = $staff->user->name ?? $lastLog->user_name;
                $details['department'] = $staff->department ?? '';
                $details['designation'] = $staff->designation ?? '';
                if ($staff->photo) {
                    $details['photo_url'] = asset('storage/' . $staff->photo);
                }
            }
        }

        $punchCount = \App\Models\BiometricDeviceLog::where('biometric_id', $lastLog->biometric_id)
            ->whereDate('punch_time', $lastLog->punch_time->format('Y-m-d'))
            ->where('status', 'success')
            ->where('id', '<=', $lastLog->id)
            ->count();

        // Calculate state based on shift if available, else fallback to odd/even count
        $computedState = ($punchCount % 2 === 1) ? 'check_in' : 'check_out';
        $lateMinutes = 0;
        
        if ($shift && $lastLog->punch_time) {
            $punchTime = $lastLog->punch_time;
            $date = $punchTime->format('Y-m-d');
            $shiftStart = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
            $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);
            
            // Midpoint of shift
            $midpoint = $shiftStart->copy()->addMinutes($shiftStart->diffInMinutes($shiftEnd) / 2);
            $isCheckInPhase = $punchTime <= $midpoint;
            
            // Check previous punches to find if we already have an IN or OUT
            $previousPunches = \App\Models\BiometricDeviceLog::where('biometric_id', $lastLog->biometric_id)
                ->whereDate('punch_time', $date)
                ->where('status', 'success')
                ->where('id', '<', $lastLog->id)
                ->get();
                
            $alreadyHasIn = false;
            $alreadyHasOut = false;
            foreach ($previousPunches as $pp) {
                if ($pp->punch_time <= $midpoint) $alreadyHasIn = true;
                else $alreadyHasOut = true;
            }
            
            if ($isCheckInPhase) {
                if ($alreadyHasIn) {
                    $computedState = 'duplicate';
                } else {
                    $computedState = 'check_in';
                    // Calculate late
                    $lateDiff = $shiftStart->diffInMinutes($punchTime, false);
                    if ($lateDiff > 0) {
                        $lateMinutes = (int)$lateDiff;
                    }
                }
            } else {
                if ($alreadyHasOut) {
                    $computedState = 'duplicate';
                } else {
                    $computedState = 'check_out';
                }
            }
        }

        $details['punch_state'] = $computedState;
        $details['late_minutes'] = $lateMinutes;

        // Get recent 5 punches for ticker list
        $recentPunches = \App\Models\BiometricDeviceLog::where('status', 'success')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                // Compute state dynamically for recent list too
                $punchCount = \App\Models\BiometricDeviceLog::where('biometric_id', $log->biometric_id)
                    ->whereDate('punch_time', $log->punch_time->format('Y-m-d'))
                    ->where('status', 'success')
                    ->where('id', '<=', $log->id)
                    ->count();
                $computedState = ($punchCount % 2 === 1) ? 'check_in' : 'check_out';
                
                // Fetch profile shift
                $shift = null;
                if ($log->user_type === 'student') {
                    $student = \App\Models\StudentProfile::with('shift')
                        ->where('biometric_id', $log->biometric_id)
                        ->orWhere('admission_no', $log->biometric_id)
                        ->orWhere('roll_no', $log->biometric_id)
                        ->first();
                    if ($student) $shift = $student->shift;
                } else {
                    $staff = \App\Models\StaffProfile::with('shifts')
                        ->where('biometric_id', $log->biometric_id)
                        ->orWhere('phone', $log->biometric_id)
                        ->first();
                    if ($staff) {
                        $shifts = $staff->shifts->sortBy('start_time');
                        if ($shifts->count() > 0) {
                            $firstShift = $shifts->first();
                            $lastShift = $shifts->last();
                            
                            $shift = new \stdClass();
                            $shift->name = $firstShift->name . ($shifts->count() > 1 ? ' & Others' : '');
                            $shift->start_time = $firstShift->start_time;
                            $shift->end_time = $lastShift->end_time;
                        }
                    }
                }
                
                if ($shift && $log->punch_time) {
                    $punchTime = $log->punch_time;
                    $date = $punchTime->format('Y-m-d');
                    $shiftStart = \Carbon\Carbon::parse($date . ' ' . $shift->start_time);
                    $shiftEnd = \Carbon\Carbon::parse($date . ' ' . $shift->end_time);
                    
                    // Midpoint of shift
                    $midpoint = $shiftStart->copy()->addMinutes($shiftStart->diffInMinutes($shiftEnd) / 2);
                    
                    if ($punchTime <= $midpoint) {
                        $computedState = 'check_in';
                    } else {
                        $computedState = 'check_out';
                    }
                }

                return [
                    'id' => $log->id,
                    'name' => $log->user_name,
                    'user_type' => $log->user_type,
                    'time' => $log->punch_time ? $log->punch_time->format('h:i:s A') : '',
                    'state' => $computedState,
                ];
            });

        return response()->json([
            'has_punch' => true,
            'punch' => $details,
            'recent' => $recentPunches,
        ]);
    }

    public function unmapped(Request $request)
    {
        $unmappedLogs = \App\Models\BiometricDeviceLog::where('status', 'unmapped')
            ->orWhere('user_type', 'unknown')
            ->orderBy('id', 'desc')
            ->paginate(20);

        $students = \App\Models\StudentProfile::with('user')->where('status', 'active')->get();
        $staff = \App\Models\StaffProfile::with('user')->where('status', 'active')->get();

        return view('admin.attendance.unmapped', compact('unmappedLogs', 'students', 'staff'));
    }

    public function mapBiometricUser(Request $request)
    {
        $request->validate([
            'biometric_id' => 'required|string',
            'user_type' => 'required|in:student,staff',
            'profile_id' => 'required|integer',
        ]);

        $biometricId = trim($request->biometric_id);
        $userType = $request->user_type;
        $profileId = $request->profile_id;

        if ($userType === 'student') {
            $profile = \App\Models\StudentProfile::findOrFail($profileId);
            $profile->update(['biometric_id' => $biometricId]);
            $userName = $profile->user->name ?? "Student #{$profile->id}";
        } else {
            $profile = \App\Models\StaffProfile::findOrFail($profileId);
            $profile->update(['biometric_id' => $biometricId]);
            $userName = $profile->user->name ?? "Staff #{$profile->id}";
        }

        // Update past unmapped logs for this biometric_id
        \App\Models\BiometricDeviceLog::where('biometric_id', $biometricId)
            ->where('status', 'unmapped')
            ->update([
                'status' => 'success',
                'user_type' => $userType,
                'user_name' => $userName,
                'message' => "Mapped manually to {$userName}",
            ]);

        return redirect()->back()->with('success', "Biometric ID '{$biometricId}' successfully mapped to {$userName}!");
    }

    public function clearBiometricLogs(Request $request)
    {
        $type = $request->input('type', 'all');
        $query = \App\Models\BiometricDeviceLog::query();
        
        if ($type === 'student') {
            $query->where('user_type', 'student');
        } elseif ($type === 'staff') {
            $query->where('user_type', 'staff');
        }
        
        $count = $query->count();
        $query->delete();
        
        return redirect()->back()->with('success', "Successfully cleared {$count} biometric punch logs.");
    }
}
