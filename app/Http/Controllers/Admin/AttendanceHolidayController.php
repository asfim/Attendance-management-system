<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\Branch;
use Illuminate\Http\Request;

class AttendanceHolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('date', 'asc')->get();
        $branches = Branch::where('status', 'active')->get();

        return view('admin.attendance_software.holidays.index', compact('holidays', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'type'      => 'required|in:government,company,weekly,festival,custom',
            'date'      => 'required|date|unique:holidays,date',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        Holiday::create($request->all());

        return redirect()->back()->with('success', 'Holiday added to calendar successfully!');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        return redirect()->back()->with('success', 'Holiday removed successfully!');
    }

    public function syncBdHolidays(Request $request)
    {
        $year = now()->year;
        $syncedCount = 0;

        // Standard Official Bangladesh Government & Festival Holidays
        $bdHolidays = [
            ['name' => 'Language Martyrs\' Day / International Mother Language Day', 'date' => "{$year}-02-21", 'type' => 'government'],
            ['name' => 'Sheikh Mujibur Rahman\'s Birthday & Children\'s Day', 'date' => "{$year}-03-17", 'type' => 'government'],
            ['name' => 'Independence and National Day', 'date' => "{$year}-03-26", 'type' => 'government'],
            ['name' => 'Shab-e-Qadr', 'date' => "{$year}-03-27", 'type' => 'festival'],
            ['name' => 'Jumatul Wida', 'date' => "{$year}-03-28", 'type' => 'festival'],
            ['name' => 'Eid-ul-Fitr (Day 1)', 'date' => "{$year}-03-31", 'type' => 'festival'],
            ['name' => 'Eid-ul-Fitr Holiday (Day 2)', 'date' => "{$year}-04-01", 'type' => 'festival'],
            ['name' => 'Eid-ul-Fitr Holiday (Day 3)', 'date' => "{$year}-04-02", 'type' => 'festival'],
            ['name' => 'Bengali New Year (Pohela Boishakh)', 'date' => "{$year}-04-14", 'type' => 'festival'],
            ['name' => 'May Day (International Workers\' Day)', 'date' => "{$year}-05-01", 'type' => 'government'],
            ['name' => 'Buddha Purnima', 'date' => "{$year}-05-11", 'type' => 'festival'],
            ['name' => 'Eid-ul-Azha (Day 1)', 'date' => "{$year}-06-06", 'type' => 'festival'],
            ['name' => 'Eid-ul-Azha Holiday (Day 2)', 'date' => "{$year}-06-07", 'type' => 'festival'],
            ['name' => 'Eid-ul-Azha Holiday (Day 3)', 'date' => "{$year}-06-08", 'type' => 'festival'],
            ['name' => 'Holy Ashura', 'date' => "{$year}-07-06", 'type' => 'festival'],
            ['name' => 'National Mourning Day', 'date' => "{$year}-08-15", 'type' => 'government'],
            ['name' => 'Janmashtami', 'date' => "{$year}-09-05", 'type' => 'festival'],
            ['name' => 'Eid-e-Miladunnabi', 'date' => "{$year}-09-16", 'type' => 'festival'],
            ['name' => 'Durga Puja (Vijaya Dashami)', 'date' => "{$year}-10-02", 'type' => 'festival'],
            ['name' => 'Victory Day', 'date' => "{$year}-12-16", 'type' => 'government'],
            ['name' => 'Christmas Day', 'date' => "{$year}-12-25", 'type' => 'festival'],
        ];

        // Attempt HTTP fetch from public Nager.Date API for BD
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/BD");
            if ($response->successful() && is_array($response->json()) && count($response->json()) > 0) {
                foreach ($response->json() as $apiHoliday) {
                    $holidayDate = $apiHoliday['date'] ?? null;
                    $holidayName = $apiHoliday['localName'] ?? $apiHoliday['name'] ?? null;
                    if ($holidayDate && $holidayName) {
                        $holiday = Holiday::firstOrCreate(
                            ['date' => $holidayDate],
                            [
                                'name' => $holidayName,
                                'type' => 'government',
                                'description' => 'Synced from Public Holidays API',
                            ]
                        );
                        if ($holiday->wasRecentlyCreated) {
                            $syncedCount++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback to static official BD list if offline or API unavailable
        }

        // Sync official list
        foreach ($bdHolidays as $item) {
            $h = Holiday::firstOrCreate(
                ['date' => $item['date']],
                [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'description' => 'Official Bangladesh Government Holiday',
                ]
            );
            if ($h->wasRecentlyCreated) {
                $syncedCount++;
            }
        }

        return redirect()->back()->with('success', "Bangladesh Public Holidays synced successfully for {$year}! ({$syncedCount} new holidays added)");
    }
}
