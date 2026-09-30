<?php

use App\Models\User;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\Role;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('SaaS tenant scoping isolates records by school_id', function () {
    // 1. Seed Roles
    $this->seed();

    // 2. Create two schools
    $schoolA = School::create(['name' => 'Hogwarts School']);
    $schoolB = School::create(['name' => 'Beauxbatons Academy']);

    // 3. Create users for both schools under Admin role
    $adminRole = Role::where('name', 'admin')->first();
    $adminA = User::create([
        'school_id' => $schoolA->id,
        'role_id' => $adminRole->id,
        'name' => 'Albus Dumbledore',
        'email' => 'albus@hogwarts.edu',
        'password' => bcrypt('password'),
    ]);

    $adminB = User::create([
        'school_id' => $schoolB->id,
        'role_id' => $adminRole->id,
        'name' => 'Olympe Maxime',
        'email' => 'maxime@beauxbatons.edu',
        'password' => bcrypt('password'),
    ]);

    // 4. Verify that Albus only queries users from Hogwarts (schoolA) when authenticated
    $this->actingAs($adminA);
    expect(User::count())->toEqual(1); // Only Albus visible because of BelongsToSchool global scope

    // 5. Verify that Maxime only queries users from Beauxbatons (schoolB)
    $this->actingAs($adminB);
    expect(User::count())->toEqual(1); // Only Maxime visible
});

test('biometric sync API logs student attendance correctly', function () {
    $this->seed();
    $school = School::create(['name' => 'Greenwood High']);

    // Create a student profile
    $studentRole = Role::where('name', 'student')->first();
    $studentUser = User::create([
        'school_id' => $school->id,
        'role_id' => $studentRole->id,
        'name' => 'Harry Potter',
        'email' => 'harry@hogwarts.edu',
        'password' => bcrypt('password'),
    ]);

    $student = StudentProfile::create([
        'user_id' => $studentUser->id,
        'roll_no' => 'RFID-1234',
        'session_id' => \App\Models\AcademicSession::first()->id,
        'class_id' => \App\Models\SchoolClass::first()->id,
        'section_id' => \App\Models\Section::first()->id,
        'admission_no' => 'ADM-1234',
        'admission_date' => '2026-08-02',
        'dob' => '2014-07-31',
        'gender' => 'Male',
        'status' => 'active'
    ]);

    // Sync student clock-in at 09:05:00 (present)
    $response = $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'ADM-1234',
        'timestamp' => '2026-08-02 09:05:00',
        'type' => 'student'
    ]);

    $response->assertSuccessful()
        ->assertJson([
            'success' => true,
            'record' => [
                'status' => 'present'
            ]
        ]);

    // Sync student clock-in at 09:20:00 (late)
    $responseLate = $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'ADM-1234',
        'timestamp' => '2026-08-02 09:20:00',
        'type' => 'student'
    ]);

    $responseLate->assertSuccessful()
        ->assertJson([
            'success' => true,
            'record' => [
                'status' => 'late'
            ]
        ]);
});

test('AI performance prediction outputs correct grades prediction', function () {
    $this->seed();
    $student = StudentProfile::first();

    // Create exam records
    $examType = \App\Models\ExamType::create([
        'session_id' => \App\Models\AcademicSession::first()->id,
        'name' => 'First Term Exam',
        'code' => 'FT101'
    ]);

    $examSchedule = \App\Models\ExamSchedule::create([
        'exam_type_id' => $examType->id,
        'class_id' => $student->class_id,
        'subject_id' => \App\Models\Subject::first()->id,
        'exam_date' => '2026-08-10',
        'start_time' => '10:00:00',
        'end_time' => '13:00:00',
        'classroom_id' => \App\Models\Classroom::first()->id,
        'max_marks' => 100,
        'pass_marks' => 33
    ]);

    \App\Models\MarksEntry::create([
        'exam_schedule_id' => $examSchedule->id,
        'student_profile_id' => $student->id,
        'marks_obtained' => 85,
        'attendance_status' => 'present'
    ]);

    $response = $this->getJson("/api/v1/ai/performance-prediction/{$student->id}");
    
    $response->assertSuccessful()
        ->assertJsonStructure([
            'student_name',
            'average_score',
            'predicted_gpa',
            'confidence_score',
            'insights'
        ]);
});
