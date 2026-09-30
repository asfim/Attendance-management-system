<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift',
                'code' => 'MORNING',
                'start_time' => '08:00:00',
                'end_time' => '13:00:00',
                'description' => 'Morning shift for primary and secondary classes',
                'status' => 'active',
            ],
            [
                'name' => 'Day Shift',
                'code' => 'DAY',
                'start_time' => '09:00:00',
                'end_time' => '16:00:00',
                'description' => 'Standard day shift',
                'status' => 'active',
            ],
            [
                'name' => 'Evening Shift',
                'code' => 'EVENING',
                'start_time' => '14:00:00',
                'end_time' => '19:00:00',
                'description' => 'Evening shift for special classes',
                'status' => 'active',
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::updateOrCreate(
                ['code' => $shift['code']],
                $shift
            );
        }
    }
}
