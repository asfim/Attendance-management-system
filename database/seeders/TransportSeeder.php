<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TransportRoute;
use App\Models\TransportRouteStop;
use App\Models\TransportAllocation;
use App\Models\StudentProfile;
use App\Models\TransportHistory;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Carbon\Carbon;

class TransportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create fake Routes
        $route1 = TransportRoute::firstOrCreate(
            ['route_name' => 'Uttara Local'],
            [
                'route_code' => 'UT-01',
                'start_point' => 'House Building',
                'end_point' => 'Madrasa Campus',
                'default_monthly_fee' => 1500.00,
                'description' => 'Covers all Uttara sectors',
                'status' => 'active'
            ]
        );

        $route2 = TransportRoute::firstOrCreate(
            ['route_name' => 'Mirpur Express'],
            [
                'route_code' => 'MP-02',
                'start_point' => 'Mirpur 10',
                'end_point' => 'Madrasa Campus',
                'default_monthly_fee' => 2000.00,
                'description' => 'Fast route from Mirpur',
                'status' => 'active'
            ]
        );

        $route3 = TransportRoute::firstOrCreate(
            ['route_name' => 'Gulshan VIP'],
            [
                'route_code' => 'GL-03',
                'start_point' => 'Gulshan 1',
                'end_point' => 'Madrasa Campus',
                'default_monthly_fee' => 0, // Optional fee test
                'description' => 'Special transport (Free)',
                'status' => 'active'
            ]
        );

        // 2. Create Stops
        $stop1 = TransportRouteStop::firstOrCreate(['route_id' => $route1->id, 'stop_name' => 'Azampur'], ['stop_order' => 1, 'pickup_time' => '07:00:00', 'drop_time' => '15:30:00', 'additional_fee' => 0]);
        $stop2 = TransportRouteStop::firstOrCreate(['route_id' => $route1->id, 'stop_name' => 'Rajlakshmi'], ['stop_order' => 2, 'pickup_time' => '07:15:00', 'drop_time' => '15:15:00', 'additional_fee' => 200.00]);
        
        $stop3 = TransportRouteStop::firstOrCreate(['route_id' => $route2->id, 'stop_name' => 'Mirpur 11'], ['stop_order' => 1, 'pickup_time' => '06:45:00', 'drop_time' => '16:00:00', 'additional_fee' => 0]);
        $stop4 = TransportRouteStop::firstOrCreate(['route_id' => $route2->id, 'stop_name' => 'Kazipara'], ['stop_order' => 2, 'pickup_time' => '07:00:00', 'drop_time' => '15:45:00', 'additional_fee' => 0]);

        $stop5 = TransportRouteStop::firstOrCreate(['route_id' => $route3->id, 'stop_name' => 'Gulshan 2'], ['stop_order' => 1, 'pickup_time' => '07:30:00', 'drop_time' => '15:00:00', 'additional_fee' => 0]);

        // 3. Assign to 5 active students
        $students = StudentProfile::where('status', 'active')->inRandomOrder()->limit(5)->get();

        $feeCat = FeeCategory::firstOrCreate(
            ['name' => 'Transport Fee'],
            ['type' => 'monthly', 'installments_count' => 12]
        );

        foreach ($students as $index => $student) {
            // Assign route1 to first 2, route2 to next 2, route3 to the last one
            if ($index < 2) {
                $route = $route1;
                $stop = $index == 0 ? $stop1 : $stop2;
            } elseif ($index < 4) {
                $route = $route2;
                $stop = $index == 2 ? $stop3 : $stop4;
            } else {
                $route = $route3;
                $stop = $stop5;
            }

            $monthlyFee = $route->default_monthly_fee + $stop->additional_fee;

            TransportAllocation::create([
                'student_profile_id' => $student->id,
                'route_id' => $route->id,
                'stop_id' => $stop->id,
                'monthly_fee' => $monthlyFee,
                'effective_from' => Carbon::now()->subMonths(3)->startOfMonth(), // Started 3 months ago
                'status' => 'active',
            ]);

            TransportHistory::create([
                'student_profile_id' => $student->id,
                'route_id' => $route->id,
                'stop_id' => $stop->id,
                'action' => 'Allocated',
                'new_value' => 'Status: Active, Fee: ' . $monthlyFee,
                'action_date' => Carbon::now()->subMonths(3)->startOfMonth(),
                'reason' => 'Fake data seeded',
                'performed_by' => 1, // Admin user
            ]);

            if ($monthlyFee > 0) {
                FeeStructure::firstOrCreate(
                    [
                        'class_id' => $student->class_id,
                        'fee_category_id' => $feeCat->id,
                    ],
                    [
                        'amount' => $monthlyFee,
                    ]
                );
            }
        }
    }
}
