<?php

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can view classroom management page', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $response = $this->actingAs($admin)->get('/admin/classrooms');

    $response->assertSuccessful();
    $response->assertSee('Class Room Management');
});

test('admin can create a classroom', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();

    $roomData = [
        'room_number' => '305-B',
        'capacity' => 55,
    ];

    $response = $this->actingAs($admin)->post('/admin/classrooms', $roomData);

    $response->assertRedirect('/admin/classrooms');
    $this->assertDatabaseHas('classrooms', [
        'room_number' => '305-B',
        'capacity' => 55,
    ]);
});

test('admin can update a classroom', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $room = Classroom::create([
        'room_number' => '401',
        'capacity' => 40,
    ]);

    $updateData = [
        'room_number' => '401-A',
        'capacity' => 50,
    ];

    $response = $this->actingAs($admin)->put("/admin/classrooms/{$room->id}", $updateData);

    $response->assertRedirect('/admin/classrooms');
    $this->assertDatabaseHas('classrooms', [
        'id' => $room->id,
        'room_number' => '401-A',
        'capacity' => 50,
    ]);
});

test('admin can delete an unassigned classroom', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $room = Classroom::create([
        'room_number' => '501-TEMP',
        'capacity' => 30,
    ]);

    $response = $this->actingAs($admin)->delete("/admin/classrooms/{$room->id}");

    $response->assertRedirect('/admin/classrooms');
    $this->assertDatabaseMissing('classrooms', [
        'id' => $room->id,
    ]);
});
