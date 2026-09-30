<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class MarkDefaultAbsentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent {date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark all active students and staff as absent for the day. (Run early morning e.g. 1:00 AM)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateStr = $this->argument('date') ?: Carbon::today()->format('Y-m-d');
        $this->info("Marking default absent for date: {$dateStr}");

        $students = StudentProfile::where('status', 'active')->get();
        $staff = StaffProfile::where('status', 'active')->get();

        $count = 0;

        // Process Students
        foreach ($students as $student) {
            $attendance = Attendance::firstOrCreate(
                [
                    'attendance_date' => $dateStr,
                    'attendable_type' => StudentProfile::class,
                    'attendable_id'   => $student->id,
                ],
                [
                    'status' => 'absent',
                    'remarks' => 'Auto-marked as absent. Waiting for punch.',
                ]
            );

            if ($attendance->wasRecentlyCreated) {
                $count++;
            }
        }

        // Process Staff
        foreach ($staff as $st) {
            $attendance = Attendance::firstOrCreate(
                [
                    'attendance_date' => $dateStr,
                    'attendable_type' => StaffProfile::class,
                    'attendable_id'   => $st->id,
                ],
                [
                    'status' => 'absent',
                    'remarks' => 'Auto-marked as absent. Waiting for punch.',
                ]
            );

            if ($attendance->wasRecentlyCreated) {
                $count++;
            }
        }

        $this->info("Successfully marked {$count} profiles as absent for {$dateStr}.");
        return 0;
    }
}
