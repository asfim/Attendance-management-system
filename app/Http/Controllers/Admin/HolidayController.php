<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Holiday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Http;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $date = Carbon::createFromDate($year, $month, 1);

        $startOfCalendar = $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfCalendar = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $period = CarbonPeriod::create($startOfCalendar, $endOfCalendar);

        // Fetch all holidays for this month range
        $holidays = Holiday::whereBetween('date', [$startOfCalendar->format('Y-m-d'), $endOfCalendar->format('Y-m-d')])
            ->pluck('name', 'date')
            ->toArray();

        $weeks = [];
        $currentWeek = [];

        foreach ($period as $day) {
            $dateStr = $day->format('Y-m-d');
            $isHoliday = isset($holidays[$dateStr]);
            $holidayName = $holidays[$dateStr] ?? null;

            $currentWeek[] = [
                'day'       => $day->day,
                'date'      => $dateStr,
                'in_month'  => $day->month == $month,
                'is_today'  => $day->isToday(),
                'is_holiday'=> $isHoliday,
                'name'      => $holidayName,
            ];

            if (count($currentWeek) == 7) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        return view('admin.holidays.index', compact('month', 'year', 'weeks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'name' => 'nullable|string'
        ]);

        $holiday = Holiday::where('date', $request->date)->first();

        if ($holiday) {
            $holiday->delete();
            return response()->json(['success' => true, 'action' => 'removed', 'message' => 'Holiday removed.']);
        } else {
            Holiday::create([
                'date' => $request->date,
                'name' => $request->name ?? 'Custom Holiday'
            ]);
            return response()->json(['success' => true, 'action' => 'added', 'message' => 'Holiday added.']);
        }
    }

    public function syncGoogle(Request $request)
    {
        $year = $request->input('year', now()->year);
        
        // Bangladesh public holidays iCal URL from Google
        // Using bn.bd#holiday@group.v.calendar.google.com for Bengali holidays, en.bd#holiday for English
        $url = 'https://calendar.google.com/calendar/ical/en.bd%23holiday%40group.v.calendar.google.com/public/basic.ics';
        
        try {
            $response = Http::get($url);
            
            if (!$response->successful()) {
                return back()->with('error', 'Failed to fetch calendar from Google.');
            }

            $icsData = $response->body();
            
            // Basic parsing of ICS
            $lines = explode("\n", $icsData);
            $event = [];
            $events = [];
            
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line == 'BEGIN:VEVENT') {
                    $event = [];
                } elseif ($line == 'END:VEVENT') {
                    $events[] = $event;
                } elseif (strpos($line, 'DTSTART;VALUE=DATE:') === 0) {
                    $event['date'] = substr($line, 19, 8); // YYYYMMDD
                } elseif (strpos($line, 'SUMMARY:') === 0) {
                    $event['name'] = substr($line, 8);
                }
            }

            $count = 0;
            foreach ($events as $e) {
                if (isset($e['date']) && isset($e['name'])) {
                    // Filter by year if we only want this year, but syncing all is fine since it's just `holidays` table
                    $dateStr = Carbon::createFromFormat('Ymd', $e['date'])->format('Y-m-d');
                    
                    // Update or create
                    Holiday::updateOrCreate(
                        ['date' => $dateStr],
                        ['name' => $e['name']]
                    );
                    $count++;
                }
            }

            return back()->with('success', "Successfully synced {$count} holidays from Google Calendar.");

        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred while syncing: ' . $e->getMessage());
        }
    }
}
