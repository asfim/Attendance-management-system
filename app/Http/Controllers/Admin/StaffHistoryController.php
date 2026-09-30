<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use Illuminate\Http\Request;

class StaffHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffProfile::with(['user']);
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $staffs = $query->paginate(15);
        return view('admin.staff.history.index', compact('staffs'));
    }

    public function show(Request $request, $id)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $monthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $staff = StaffProfile::with([
            'user',
            'user'
        ])->findOrFail($id);

        $monthAttendances = $staff->attendances()
            ->whereBetween('attendance_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->get()
            ->keyBy(fn($a) => \Carbon\Carbon::parse($a->attendance_date)->format('Y-m-d'));

        // Fetch Global Holidays
        $globalHolidays = \App\Models\Holiday::whereBetween('date', [$monthStart->copy()->subDays(7)->format('Y-m-d'), $monthEnd->copy()->addDays(7)->format('Y-m-d')])
            ->pluck('name', 'date')
            ->toArray();

        // Build Calendar Grid (Monday to Sunday)
        $cursor = $monthStart->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $weeks = [];

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
                        $color = '#6b7280'; // grey for holiday
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

        return view('admin.staff.history.show', compact('staff', 'month', 'year', 'weeks', 'monthAttendances'));
    }
}
