<?php

use App\Models\Bed;
use App\Models\Hostel;
use App\Models\HostelAllocation;
use App\Models\Room;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can view hostel halls page', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $response = $this->actingAs($admin)->get('/admin/hostel/halls');

    $response->assertSuccessful();
    $response->assertSee('Hostel Halls / Houses');
});

test('admin can create a hostel hall', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();

    $hallData = [
        'name' => 'Nazrul Hall',
        'type' => 'Boys',
        'address' => 'East Wing, Main Campus',
    ];

    $response = $this->actingAs($admin)->post('/admin/hostel/halls', $hallData);

    $response->assertRedirect('/admin/hostel/halls');
    $this->assertDatabaseHas('hostels', [
        'name' => 'Nazrul Hall',
        'type' => 'Boys',
    ]);
});

test('admin can create a room and beds are auto generated', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $hostel = Hostel::create([
        'name' => 'Fazlul Huq Hall',
        'type' => 'Boys',
    ]);

    $roomData = [
        'hostel_id' => $hostel->id,
        'room_number' => '105-A',
        'room_type' => 'Standard',
        'capacity' => 3,
        'cost_per_bed' => 2000.00,
    ];

    $response = $this->actingAs($admin)->post('/admin/hostel/rooms', $roomData);

    $response->assertRedirect('/admin/hostel/rooms');
    $this->assertDatabaseHas('rooms', [
        'hostel_id' => $hostel->id,
        'room_number' => '105-A',
        'capacity' => 3,
    ]);

    $room = Room::where('room_number', '105-A')->first();
    expect($room->beds->count())->toBe(3);
});

test('admin can allocate a bed to a student', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $student = StudentProfile::first();
    $hostel = Hostel::create(['name' => 'Roquia Hall', 'type' => 'Girls']);
    $room = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '201',
        'room_type' => 'Deluxe',
        'capacity' => 1,
        'cost_per_bed' => 2500.00,
    ]);
    $bed = Bed::create([
        'room_id' => $room->id,
        'bed_number' => 'Bed 1',
        'status' => 'available',
    ]);

    $allocData = [
        'student_profile_id' => $student->id,
        'bed_id' => $bed->id,
        'allocation_date' => now()->format('Y-m-d'),
    ];

    $response = $this->actingAs($admin)->post('/admin/hostel/allocations', $allocData);

    $response->assertRedirect('/admin/hostel/allocations');
    $this->assertDatabaseHas('hostel_allocations', [
        'student_profile_id' => $student->id,
        'bed_id' => $bed->id,
        'status' => 'active',
    ]);

    expect($bed->fresh()->status)->toBe('occupied');
});

test('available rooms endpoint excludes full rooms', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $hostel = Hostel::create(['name' => 'Test Hall', 'type' => 'Boys']);
    
    // Full room
    $fullRoom = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '999-FULL',
        'room_type' => 'Standard',
        'capacity' => 1,
        'cost_per_bed' => 1000,
    ]);
    Bed::create(['room_id' => $fullRoom->id, 'bed_number' => 'Bed 1', 'status' => 'occupied']);

    // Available room
    $availRoom = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '999-AVAIL',
        'room_type' => 'Standard',
        'capacity' => 1,
        'cost_per_bed' => 1000,
    ]);
    Bed::create(['room_id' => $availRoom->id, 'bed_number' => 'Bed 1', 'status' => 'available']);

    $response = $this->actingAs($admin)->getJson("/admin/hostel/available-rooms/{$hostel->id}");

    $response->assertSuccessful();
    $roomIds = collect($response->json())->pluck('id')->all();

    expect($roomIds)->toContain($availRoom->id);
    expect($roomIds)->not->toContain($fullRoom->id);
});

test('admin can view hostel report page', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $response = $this->actingAs($admin)->get('/admin/hostel/report');

    $response->assertSuccessful();
    $response->assertSee('Hostel Occupancy & Availability Report', false);
});

test('admin can assign and update hostel bed in student edit page', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $student = StudentProfile::with('user')->first();
    $hostel = Hostel::create(['name' => 'Sher-e-Bangla Hall', 'type' => 'Boys']);
    $room = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '303',
        'room_type' => 'Standard',
        'capacity' => 1,
        'cost_per_bed' => 1800.00,
    ]);
    $bed = Bed::create([
        'room_id' => $room->id,
        'bed_number' => 'Bed 1',
        'status' => 'available',
    ]);

    // View edit page
    $editResponse = $this->actingAs($admin)->get("/admin/students/{$student->id}/edit");
    $editResponse->assertSuccessful();
    $editResponse->assertSee('Assign Hostel Bed');

    // Submit update with hostel allocation enabled
    $updateData = [
        'name' => $student->user->name,
        'email' => $student->user->email,
        'roll_no' => $student->roll_no,
        'class_id' => $student->class_id,
        'section_id' => $student->section_id,
        'session_id' => $student->session_id,
        'admission_no' => $student->admission_no,
        'admission_date' => '2026-08-01',
        'dob' => '2014-05-15',
        'gender' => 'male',
        'status' => 'active',
        'assign_hostel' => '1',
        'hostel_id' => $hostel->id,
        'room_id' => $room->id,
        'bed_id' => $bed->id,
    ];

    $response = $this->actingAs($admin)->put("/admin/students/{$student->id}", $updateData);

    $response->assertRedirect("/admin/students/{$student->id}");
    $this->assertDatabaseHas('hostel_allocations', [
        'student_profile_id' => $student->id,
        'bed_id' => $bed->id,
        'status' => 'active',
    ]);
    expect($bed->fresh()->status)->toBe('occupied');

    // View profile page to verify hostel details display
    $showResponse = $this->actingAs($admin)->get("/admin/students/{$student->id}");
    $showResponse->assertSuccessful();
    $showResponse->assertSee('Hostel Accommodation');
    $showResponse->assertSee('Sher-e-Bangla Hall');
    $showResponse->assertSee('Room 303');
});

test('admin can update a hostel bed allocation', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $student = StudentProfile::first();
    $hostel = Hostel::create(['name' => 'Fazlul Huq Hall (Edit)', 'type' => 'Boys']);
    
    $room1 = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '101-A',
        'room_type' => 'Standard',
        'capacity' => 1,
        'cost_per_bed' => 1000.00,
    ]);
    $bed1 = Bed::create([
        'room_id' => $room1->id,
        'bed_number' => 'Bed 1',
        'status' => 'occupied',
    ]);

    $room2 = Room::create([
        'hostel_id' => $hostel->id,
        'room_number' => '102-A',
        'room_type' => 'Standard',
        'capacity' => 1,
        'cost_per_bed' => 1200.00,
    ]);
    $bed2 = Bed::create([
        'room_id' => $room2->id,
        'bed_number' => 'Bed 1',
        'status' => 'available',
    ]);

    $allocation = HostelAllocation::create([
        'student_profile_id' => $student->id,
        'bed_id' => $bed1->id,
        'allocation_date' => '2026-08-01',
        'status' => 'active',
    ]);

    $updateData = [
        'student_profile_id' => $student->id,
        'bed_id' => $bed2->id,
        'allocation_date' => '2026-08-05',
        'status' => 'active',
    ];

    $response = $this->actingAs($admin)->put("/admin/hostel/allocations/{$allocation->id}", $updateData);

    $response->assertRedirect('/admin/hostel/allocations');
    
    // Assert bed 1 is now available and bed 2 is now occupied
    expect($bed1->fresh()->status)->toBe('available');
    expect($bed2->fresh()->status)->toBe('occupied');

    // Assert allocation is updated
    $this->assertDatabaseHas('hostel_allocations', [
        'id' => $allocation->id,
        'bed_id' => $bed2->id,
        'allocation_date' => '2026-08-05 00:00:00',
        'status' => 'active',
    ]);
});
