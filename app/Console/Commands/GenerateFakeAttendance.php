<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\Attendance;
use Carbon\Carbon;

class GenerateFakeAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:fake-attendance {--months=2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake attendance data for students and staff';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $months = (int) $this->option('months');
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        $endDate = Carbon::now();
        
        $this->info("Generating fake attendance from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

        $staffs = StaffProfile::all();
        $students = StudentProfile::all();
        
        $dates = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (!$currentDate->isWeekend()) {
                $dates[] = $currentDate->copy();
            }
            $currentDate->addDay();
        }

        $bar = $this->output->createProgressBar((count($staffs) + count($students)) * count($dates));
        $bar->start();

        $statuses = [
            'present' => 70, 
            'absent' => 5, 
            'late' => 15, 
            'leave' => 5, 
            'half_day' => 5
        ];

        // Process Staff
        foreach ($staffs as $staff) {
            foreach ($dates as $date) {
                $status = $this->getRandomStatus($statuses);
                
                $remarks = null;
                $lateReason = null;
                $leaveReason = null;
                $deduction = 0;
                
                if ($status === 'late') {
                    $lateReason = 'Traffic jam';
                    $deduction = 50; // Random deduction for late
                } elseif ($status === 'leave') {
                    $leaveReason = 'Sick leave';
                } elseif ($status === 'absent') {
                    $deduction = 500; // Random deduction for absent
                    $remarks = 'Uninformed absence';
                }

                Attendance::updateOrCreate(
                    [
                        'attendance_date' => $date->format('Y-m-d'),
                        'attendable_type' => StaffProfile::class,
                        'attendable_id' => $staff->id,
                    ],
                    [
                        'status' => $status,
                        'remarks' => $remarks,
                        'late_reason' => $lateReason,
                        'leave_reason' => $leaveReason,
                        'salary_deduction' => $deduction,
                    ]
                );
                
                $bar->advance();
            }
        }

        // Process Students
        foreach ($students as $student) {
            foreach ($dates as $date) {
                $status = $this->getRandomStatus($statuses);
                
                $remarks = null;
                $lateReason = null;
                $leaveReason = null;
                
                if ($status === 'late') {
                    $lateReason = 'Missed bus';
                } elseif ($status === 'leave') {
                    $leaveReason = 'Family function';
                } elseif ($status === 'absent') {
                    $remarks = 'Did not attend class';
                }

                Attendance::updateOrCreate(
                    [
                        'attendance_date' => $date->format('Y-m-d'),
                        'attendable_type' => StudentProfile::class,
                        'attendable_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'remarks' => $remarks,
                        'late_reason' => $lateReason,
                        'leave_reason' => $leaveReason,
                        'salary_deduction' => 0, // No deduction for students
                    ]
                );
                
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("Successfully generated fake attendance for all staff and students.");
    }

    private function getRandomStatus(array $statuses)
    {
        $rand = mt_rand(1, 100);
        $cumulative = 0;
        foreach ($statuses as $status => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $status;
            }
        }
        return 'present';
    }
}
