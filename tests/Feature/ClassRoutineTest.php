<?php

use App\Models\AcademicSession;
use App\Models\Classroom;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffProfile;
use App\Models\Subject;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can view class routines page', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $response = $this->actingAs($admin)->get('/admin/routines');

    $response->assertSuccessful();
    $response->assertSee('Class Routine');
});

test('admin can create class routine slot', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $session = AcademicSession::first();
    $class = SchoolClass::first();
    $section = Section::first();
    $subject = Subject::first();
    $teacher = StaffProfile::first();
    $classroom = Classroom::first();

    $routineData = [
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'staff_profile_id' => $teacher->id,
        'classroom_id' => $classroom->id,
        'day_of_week' => 'Monday',
        'start_time' => '10:00',
        'end_time' => '10:45',
    ];

    $response = $this->actingAs($admin)->post('/admin/routines', $routineData);

    $response->assertRedirect();
    $this->assertDatabaseHas('timetables', [
        'session_id' => $session->id,
        'class_id' => $class->id,
        'day_of_week' => 'Monday',
        'start_time' => '10:00:00',
        'end_time' => '10:45:00',
    ]);
});

test('admin can update class routine slot', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $session = AcademicSession::first();
    $class = SchoolClass::first();
    $section = Section::first();
    $subject = Subject::first();
    $teacher = StaffProfile::first();

    $timetable = Timetable::create([
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'staff_profile_id' => $teacher->id,
        'day_of_week' => 'Tuesday',
        'start_time' => '11:00',
        'end_time' => '11:45',
    ]);

    $updateData = [
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'staff_profile_id' => $teacher->id,
        'day_of_week' => 'Tuesday',
        'start_time' => '12:00',
        'end_time' => '12:45',
    ];

    $response = $this->actingAs($admin)->put("/admin/routines/{$timetable->id}", $updateData);

    $response->assertRedirect();
    $this->assertDatabaseHas('timetables', [
        'id' => $timetable->id,
        'start_time' => '12:00:00',
        'end_time' => '12:45:00',
    ]);
});

test('admin can delete class routine slot', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();
    $session = AcademicSession::first();
    $class = SchoolClass::first();
    $section = Section::first();
    $subject = Subject::first();
    $teacher = StaffProfile::first();

    $timetable = Timetable::create([
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'subject_id' => $subject->id,
        'staff_profile_id' => $teacher->id,
        'day_of_week' => 'Wednesday',
        'start_time' => '09:00',
        'end_time' => '09:45',
    ]);

    $response = $this->actingAs($admin)->delete("/admin/routines/{$timetable->id}");

    $response->assertRedirect();
    $this->assertDatabaseMissing('timetables', [
        'id' => $timetable->id,
    ]);
});
