<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Repositories\Contracts\AcademicRepositoryInterface;
use Illuminate\Http\Request;

class StudentHistoryController extends Controller
{
    protected AcademicRepositoryInterface $academicRepository;

    public function __construct(AcademicRepositoryInterface $academicRepository)
    {
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
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            })->orWhere('admission_no', 'like', "%{$search}%");
        }

        $students = $query->paginate(15);

        return view('admin.students.history.index', compact('students', 'classes', 'sessions'));
    }

    public function show(Request $request, $id)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        // Carbon start and end for the selected month
        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $student = StudentProfile::with([
            'user', 
            'schoolClass', 
            'section', 
            'parent.user',
            'attendances' => function($query) use ($startDate, $endDate, $monthStart, $monthEnd) {
                if ($startDate && $endDate) {
                    $query->whereBetween('attendance_date', [$startDate, $endDate])
                          ->orderBy('attendance_date', 'asc');
                } else {
                    $query->whereBetween('attendance_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                          ->orderBy('attendance_date', 'asc');
                }
            },
            'marksEntries' => function($query) use ($startDate, $endDate) {
                if ($startDate || $endDate) {
                    $query->whereHas('examSchedule', function($q) use ($startDate, $endDate) {
                        if ($startDate) {
                            $q->where('exam_date', '>=', $startDate);
                        }
                        if ($endDate) {
                            $q->where('exam_date', '<=', $endDate);
                        }
                    });
                }
            },
            'marksEntries.examSchedule.examType',
            'marksEntries.examSchedule.subject',
            'invoices' => function($query) use ($startDate, $endDate) {
                if ($startDate) {
                    $query->where('issue_date', '>=', $startDate);
                }
                if ($endDate) {
                    $query->where('issue_date', '<=', $endDate);
                }
                $query->orderBy('issue_date', 'desc');
            },
            'invoices.items',
            'invoices.payments'
        ])->findOrFail($id);

        $monthAttendances = $student->attendances->keyBy(function($att) {
            return $att->attendance_date->format('Y-m-d');
        });

        // Fetch Global Holidays
        $globalHolidays = \App\Models\Holiday::whereBetween('date', [$monthStart->copy()->subDays(7)->format('Y-m-d'), $monthEnd->copy()->addDays(7)->format('Y-m-d')])
            ->pluck('name', 'date')
            ->toArray();

        // Build calendar weeks
        $weeks = [];
        $cursor = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        while (true) {
            $week = [];
            for ($d = 0; $d < 7; $d++) {
                $dateStr = $cursor->format('Y-m-d');
                $inMonth = $cursor->month === $month;
                $attendance = $monthAttendances[$dateStr] ?? null;
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
                
                $week[] = [
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
            if ($cursor->gt($monthEnd) && $cursor->dayOfWeek === \Carbon\Carbon::MONDAY) break;
        }

        return view('admin.students.history.show', compact('student', 'startDate', 'endDate', 'month', 'year', 'weeks', 'monthAttendances'));
    }
}
