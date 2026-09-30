<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\Bed;
use App\Models\HostelAllocation;
use App\Models\StudentProfile;
use App\Models\School;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Faker\Factory as Faker;
use Illuminate\Support\Carbon;

class HostelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        if (Hostel::count() > 0) {
            $this->command->info('Hostels already seeded. Skipping.');
            return;
        }

        $school = School::first();
        $schoolId = $school ? $school->id : null;

        $hostelData = [
            [
                'name' => 'Nazrul Hall',
                'type' => 'Boys',
                'address' => 'East Wing, Main Campus',
            ],
            [
                'name' => 'Begum Rokeya Hall',
                'type' => 'Girls',
                'address' => 'West Wing, Main Campus',
            ],
            [
                'name' => 'Main Campus Residence',
                'type' => 'Combined',
                'address' => 'North Gate Road',
            ],
        ];

        $hostels = [];
        foreach ($hostelData as $hd) {
            $hostels[] = Hostel::create([
                'school_id' => $schoolId,
                'name' => $hd['name'],
                'type' => $hd['type'],
                'address' => $hd['address'],
            ]);
        }

        // Keep track of allocated student IDs so they don't get duplicate assignments
        $allocatedStudentIds = [];

        foreach ($hostels as $hostel) {
            // Create 5 rooms for each hostel
            $roomTemplates = [
                ['number' => '101', 'type' => 'Standard', 'capacity' => 4, 'cost' => 1500],
                ['number' => '102', 'type' => 'Standard', 'capacity' => 4, 'cost' => 1500],
                ['number' => '201', 'type' => 'Deluxe', 'capacity' => 2, 'cost' => 2500],
                ['number' => '202', 'type' => 'Deluxe', 'capacity' => 2, 'cost' => 2500],
                ['number' => '301', 'type' => 'Premium', 'capacity' => 1, 'cost' => 4000],
            ];

            foreach ($roomTemplates as $template) {
                $room = Room::create([
                    'hostel_id' => $hostel->id,
                    'room_number' => $template['number'],
                    'room_type' => $template['type'],
                    'capacity' => $template['capacity'],
                    'cost_per_bed' => $template['cost'],
                ]);

                // Create beds
                for ($b = 1; $b <= $room->capacity; $b++) {
                    $bed = Bed::create([
                        'room_id' => $room->id,
                        'bed_number' => 'Bed ' . $b,
                        'status' => 'available',
                    ]);

                    // Allocate beds with a 40% probability
                    if ($faker->boolean(40)) {
                        // Find an active student who is not yet allocated
                        $query = StudentProfile::query()->whereNotIn('id', $allocatedStudentIds);

                        if ($hostel->type === 'Boys') {
                            $query->where('gender', 'Male');
                        } elseif ($hostel->type === 'Girls') {
                            $query->where('gender', 'Female');
                        }

                        $studentToAllocate = $query->first();

                        if ($studentToAllocate) {
                            $allocatedStudentIds[] = $studentToAllocate->id;

                            // Create allocation
                            HostelAllocation::create([
                                'student_profile_id' => $studentToAllocate->id,
                                'bed_id' => $bed->id,
                                'allocation_date' => Carbon::now()->subDays($faker->numberBetween(10, 60))->format('Y-m-d'),
                                'status' => 'active',
                            ]);

                            // Update bed status
                            $bed->update(['status' => 'occupied']);

                            // Auto-create fee category and structure for hostel fees to keep systems integrated
                            $feeCat = FeeCategory::firstOrCreate(
                                ['name' => 'Hostel Fee']
                            );

                            FeeStructure::firstOrCreate(
                                [
                                    'class_id' => $studentToAllocate->class_id,
                                    'fee_category_id' => $feeCat->id,
                                ],
                                [
                                    'amount' => $room->cost_per_bed,
                                ]
                            );
                        }
                    }
                }
            }
        }

        $this->command->info('Fake hostel data seeded successfully.');
    }
}
