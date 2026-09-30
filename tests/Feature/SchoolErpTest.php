<?php

use App\Models\User;
use App\Models\Role;
use App\Models\StudentProfile;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('guest can access the login page', function () {
    $response = $this->get('/login');
    $response->assertSuccessful();
});

test('guest cannot log in with invalid credentials', function () {
    $response = $this->post('/login', [
        'email' => 'unknown@school.com',
        'password' => 'wrongpass',
    ]);
    
    $response->assertSessionHasErrors('email');
});

test('admin can log in and view dashboard', function () {
    // Seed roles and default admin first (using seed)
    $this->seed();

    $response = $this->post('/login', [
        'email' => 'admin@school.com',
        'password' => 'admin123',
    ]);

    $response->assertRedirect('/admin/dashboard');

    $admin = User::where('email', 'admin@school.com')->first();
    $dashboardResponse = $this->actingAs($admin)->get('/admin/dashboard');
    $dashboardResponse->assertSuccessful();
});

test('student cannot view admin dashboard', function () {
    $this->seed();

    $student = User::where('email', 'student@school.com')->first();
    
    $response = $this->actingAs($student)->get('/admin/dashboard');
    $response->assertForbidden();
});

test('admitting student creates user and profile records', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();

    $studentData = [
        'name' => 'Harry Potter',
        'email' => 'harry@school.com',
        'password' => 'seeker123',
        'roll_no' => '99',
        'session_id' => \App\Models\AcademicSession::first()->id,
        'class_id' => \App\Models\SchoolClass::first()->id,
        'section_id' => \App\Models\Section::first()->id,
        'admission_no' => 'ADM-9999',
        'admission_date' => '2026-08-02',
        'dob' => '2014-07-31',
        'gender' => 'Male',
        'parent_name' => 'James Potter',
        'parent_email' => 'james@example.com',
        'parent_phone' => '0188888888',
        'parent_address' => 'Godrics Hollow',
    ];

    $response = $this->actingAs($admin)->post('/admin/students', $studentData);

    $response->assertRedirect('/admin/students');

    $this->assertDatabaseHas('users', [
        'email' => 'harry@school.com',
    ]);

    $this->assertDatabaseHas('student_profiles', [
        'admission_no' => 'ADM-9999',
    ]);
});
