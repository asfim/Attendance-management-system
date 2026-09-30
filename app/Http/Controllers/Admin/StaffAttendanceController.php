<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\PayrollSetting;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StaffAttendanceController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    /**
     * Main page: all staff cards with month/year selector.
     */
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);
        $shiftId = $request->input('shift_id');

        $staffMembers = StaffProfile::with(['user.role', 'allowances', 'shifts'])
            ->whereHas('user', fn($q) => $q->where('status', 'active'))
            ->when($shiftId, function ($query, $shiftId) {
                return $query->whereHas('shifts', function ($q) use ($shiftId) {
                    $q->where('shifts.id', $shiftId);
                });
            })
            ->orderBy('department')
            ->get();

        $shifts = \App\Models\Shift::where('status', 'active')->get();

        return view('admin.staff.attendance.index', compact('staffMembers', 'month', 'year', 'shifts'));
    }

    /**
     * AJAX: Return calendar data for a staff member (all attendance records for the month).
     */
    public function calendar(Request $request, int $staffId): JsonResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $staff = StaffProfile::with('user')->findOrFail($staffId);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staffId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        // Fetch Global Holidays
        $globalHolidays = \App\Models\Holiday::whereBetween('date', [$start->copy()->subDays(7)->format('Y-m-d'), $end->copy()->addDays(7)->format('Y-m-d')])
            ->pluck('name', 'date')
            ->toArray();

        // Build calendar weeks
        $weeks  = [];
        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        while (true) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dateStr    = $cursor->format('Y-m-d');
                $inMonth    = $cursor->month === $month;
                $attendance = $attendances[$dateStr] ?? null;
                $isGlobalHoliday = isset($globalHolidays[$dateStr]);

                $status = $attendance?->status;
                $color = $attendance?->calendarColor();

                $holidayName = null;
                if (!$attendance) {
                    if ($isGlobalHoliday) {
                        $status = 'holiday';
                        $color = '#6b7280';
                        $holidayName = $globalHolidays[$dateStr];
                    } elseif ($cursor->isWeekend()) {
                        $status = 'holiday';
                        $color = '#6b7280';
                        $holidayName = 'Weekend';
                    }
                } elseif ($status === 'holiday') {
                    $holidayName = $isGlobalHoliday ? $globalHolidays[$dateStr] : ($cursor->isWeekend() ? 'Weekend' : 'Holiday');
                }

                $week[]     = [
                    'date'         => $dateStr,
                    'day'          => $cursor->day,
                    'in_month'     => $inMonth,
                    'is_today'     => $cursor->isToday(),
                    'is_future'    => $cursor->isFuture(),
                    'status'       => $status,
                    'color'        => $color,
                    'holiday_name' => $holidayName,
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
            if ($cursor->gt($end) && $cursor->dayOfWeek === Carbon::MONDAY) break;
        }

        // Summary counts
        $summary = $this->buildSummary($attendances, PayrollSetting::current()->working_days);

        // Payroll sidebar
        $settings     = PayrollSetting::current();
        $workingDays  = $settings->working_days;
        $perDaySalary = $staff->perDaySalary($workingDays);

        $salary = Salary::where('user_id', $staff->user_id)
            ->where('month', $month)->where('year', $year)->first();

        return response()->json([
            'weeks'        => $weeks,
            'summary'      => $summary,
            'per_day'      => $perDaySalary,
            'working_days' => $workingDays,
            'salary'       => $salary,
        ]);
    }

    /**
     * AJAX: Return form data for a specific date (for inline form).
     */
    public function getDay(int $staffId, string $date): JsonResponse
    {
        $staff      = StaffProfile::with('user')->findOrFail($staffId);
        $attendance = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staffId)
            ->whereDate('attendance_date', $date)
            ->first();

        $settings     = PayrollSetting::current();
        $perDaySalary = $staff->perDaySalary($settings->working_days);

        $globalHoliday = \App\Models\Holiday::where('date', $date)->first();
        $isHoliday = false;
        $holidayName = null;
        
        if ($globalHoliday) {
            $isHoliday = true;
            $holidayName = $globalHoliday->name;
        } elseif (\Carbon\Carbon::parse($date)->isWeekend()) {
            $isHoliday = true;
            $holidayName = 'Weekend';
        }

        return response()->json([
            'date'             => $date,
            'attendance'       => $attendance,
            'per_day_salary'   => $perDaySalary,
            'default_deduction'=> $perDaySalary,
            'is_holiday'       => $isHoliday,
            'holiday_name'     => $holidayName,
        ]);
    }

    /**
     * AJAX: Save a single day's attendance.
     */
    public function saveDay(Request $request): JsonResponse
    {
        $request->validate([
            'staff_id'         => 'required|exists:staff_profiles,id',
            'date'             => 'required|date',
            'status'           => 'required|in:present,absent,late,half_day,leave,holiday,work_from_home',
            'entry_time'       => 'nullable|date_format:H:i',
            'exit_time'        => 'nullable|date_format:H:i',
            'remarks'          => 'nullable|string|max:500',
            'late_reason'      => 'nullable|string|max:500',
            'leave_reason'     => 'nullable|string|max:500',
            'leave_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'approved_by'      => 'nullable|string|max:100',
            'salary_deduction' => 'nullable|numeric|min:0',
        ]);

        $staffId         = $request->staff_id;
        $staff           = StaffProfile::findOrFail($staffId);
        $attachmentPath  = null;

        if ($request->hasFile('leave_attachment')) {
            $attachmentPath = $request->file('leave_attachment')->store('leave_attachments', 'public');
        }

        $data = [
            'status'           => $request->status,
            'entry_time'       => $request->entry_time,
            'exit_time'        => $request->exit_time,
            'remarks'          => $request->remarks,
            'late_reason'      => $request->late_reason,
            'leave_reason'     => $request->leave_reason,
            'approved_by'      => $request->approved_by,
            'salary_deduction' => $request->salary_deduction,
        ];

        if ($attachmentPath) {
            $data['leave_attachment'] = $attachmentPath;
        }

        $attendance = Attendance::updateOrCreate(
            [
                'attendable_type' => StaffProfile::class,
                'attendable_id'   => $staffId,
                'attendance_date' => $request->date,
            ],
            $data
        );

        // Rebuild monthly summary
        $dateParsed = Carbon::parse($request->date);
        $month      = $dateParsed->month;
        $year       = $dateParsed->year;
        $start      = Carbon::create($year, $month, 1)->startOfMonth();
        $end        = $start->copy()->endOfMonth();

        $attendances = Attendance::where('attendable_type', StaffProfile::class)
            ->where('attendable_id', $staffId)
            ->whereBetween('attendance_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn($a) => $a->attendance_date->format('Y-m-d'));

        $settings = PayrollSetting::current();
        $summary  = $this->buildSummary($attendances, $settings->working_days);

        return response()->json([
            'success'    => true,
            'color'      => $attendance->calendarColor(),
            'status'     => $attendance->status,
            'badge'      => $attendance->badgeClass(),
            'summary'    => $summary,
            'attendance' => $attendance,
        ]);
    }

    /**
     * AJAX: Return the payroll sidebar data for a staff member.
     */
    public function payrollSidebar(int $staffId, Request $request): JsonResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $staff = StaffProfile::with(['user', 'allowances'])->findOrFail($staffId);

        $calc = $this->payrollService->calculateSalary($staff, $month, $year);
        $calc['raw_allowances'] = $staff->allowances;

        return response()->json($calc);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function buildSummary($attendances, int $workingDays): array
    {
        $present  = $attendances->whereIn('status', ['present', 'work_from_home'])->count();
        $absent   = $attendances->where('status', 'absent')->count();
        $late     = $attendances->where('status', 'late')->count();
        $leave    = $attendances->where('status', 'leave')->count();
        $halfDay  = $attendances->where('status', 'half_day')->count();
        $holiday  = $attendances->where('status', 'holiday')->count();
        $recorded = $attendances->count();
        $pct      = $workingDays > 0 ? round(($present / $workingDays) * 100, 1) : 0;

        return [
            'present'      => $present,
            'absent'       => $absent,
            'late'         => $late,
            'leave'        => $leave,
            'half_day'     => $halfDay,
            'holiday'      => $holiday,
            'working_days' => $workingDays,
            'recorded'     => $recorded,
            'percentage'   => $pct,
        ];
    }
}
