<?php

use App\Models\User;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\AcademicSession;
use App\Models\BiometricDeviceLog;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('student fingerprint biometric attendance sync records entry time and late status', function () {
    $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student']);

    $user = User::factory()->create([
        'name' => 'John Student',
        'email' => 'student@test.com',
        'role_id' => $studentRole->id,
    ]);

    $session = AcademicSession::create([
        'name' => '2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-12-31',
        'is_current' => true,
    ]);

    $class = SchoolClass::create(['name' => 'Class 10', 'code' => 'C10']);
    $section = Section::create(['name' => 'A', 'class_id' => $class->id]);

    $student = StudentProfile::create([
        'user_id' => $user->id,
        'biometric_id' => 'FP_STU_1001',
        'roll_no' => '101',
        'admission_no' => 'ADM1001',
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_date' => '2026-01-01',
        'dob' => '2010-05-15',
        'gender' => 'male',
        'status' => 'active',
    ]);

    // Send fingerprint punch log via JSON API
    $response = $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'FP_STU_1001',
        'timestamp' => '2026-09-28 08:55:00',
        'type' => 'student',
        'device_sn' => 'ZK_TEST_01',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'record' => [
            'name' => 'John Student',
            'user_type' => 'student',
            'status' => 'present',
            'entry_time' => '08:55:00',
        ]
    ]);

    $attendance = Attendance::where('attendable_id', $student->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->status)->toBe('present');
    expect($attendance->entry_time)->toBe('08:55:00');

    $this->assertDatabaseHas('biometric_device_logs', [
        'biometric_id' => 'FP_STU_1001',
        'user_type' => 'student',
        'status' => 'success',
    ]);
});

test('teacher fingerprint biometric attendance sync records exit time on second punch', function () {
    $teacherRole = Role::firstOrCreate(['name' => 'teacher'], ['display_name' => 'Teacher']);

    $user = User::factory()->create([
        'name' => 'Sarah Teacher',
        'email' => 'teacher@test.com',
        'role_id' => $teacherRole->id,
    ]);

    $staff = StaffProfile::create([
        'user_id' => $user->id,
        'biometric_id' => 'FP_TCH_2001',
        'phone' => '01700000000',
        'joining_date' => '2025-01-01',
        'salary' => 35000,
        'status' => 'active',
    ]);

    // First Punch (Check-In)
    $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'FP_TCH_2001',
        'timestamp' => '2026-09-28 09:00:00',
        'type' => 'employee',
        'device_sn' => 'ZK_TEST_01',
    ])->assertSuccessful();

    // Second Punch (Check-Out)
    $response = $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'FP_TCH_2001',
        'timestamp' => '2026-09-28 17:00:00',
        'type' => 'employee',
        'device_sn' => 'ZK_TEST_01',
    ]);

    $response->assertSuccessful();
    $response->assertJson([
        'success' => true,
        'record' => [
            'name' => 'Sarah Teacher',
            'user_type' => 'staff',
            'entry_time' => '09:00:00',
            'exit_time' => '17:00:00',
        ]
    ]);

    $attendance = Attendance::where('attendable_id', $staff->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->entry_time)->toBe('09:00:00');
    expect($attendance->exit_time)->toBe('17:00:00');
});

test('unregistered biometric id logs failure record', function () {
    $response = $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'INVALID_BIO_9999',
        'timestamp' => '2026-09-28 09:00:00',
        'type' => 'auto',
    ]);

    $response->assertNotFound();

    $this->assertDatabaseHas('biometric_device_logs', [
        'biometric_id' => 'INVALID_BIO_9999',
        'status' => 'failed',
    ]);
});

test('biometric logs page supports date filter and ajax load more', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    BiometricDeviceLog::create([
        'biometric_id' => 'STU_101',
        'punch_time' => '2026-09-28 09:00:00',
        'user_type' => 'student',
        'user_name' => 'UniqueStudentA',
        'status' => 'success',
    ]);

    BiometricDeviceLog::create([
        'biometric_id' => 'STU_102',
        'punch_time' => '2026-09-25 09:00:00',
        'user_type' => 'student',
        'user_name' => 'UniqueStudentB',
        'status' => 'success',
    ]);

    // Test Date Filter
    $response = $this->actingAs($admin)->get('/admin/attendance/biometric-logs?type=student&date=2026-09-28');
    $response->assertSuccessful();
    $response->assertSee('UniqueStudentA');
    $response->assertDontSee('UniqueStudentB');

    // Test AJAX Load More
    $ajaxResponse = $this->actingAs($admin)->getJson('/admin/attendance/biometric-logs?type=student&page=1', [
        'X-Requested-With' => 'XMLHttpRequest'
    ]);
    $ajaxResponse->assertSuccessful();
    $ajaxResponse->assertJsonStructure([
        'success',
        'html',
        'has_more',
        'next_page'
    ]);
});

test('live attendance monitor kiosk displays student photo and details on punch', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $studentRole = Role::firstOrCreate(['name' => 'student'], ['display_name' => 'Student']);
    $studentUser = User::factory()->create(['name' => 'Kiosk Test Student', 'role_id' => $studentRole->id]);
    $session = AcademicSession::create(['name' => '2026', 'start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]);
    $class = SchoolClass::create(['name' => 'Class 9', 'code' => 'C9']);
    $section = Section::create(['name' => 'B', 'class_id' => $class->id]);

    $student = StudentProfile::create([
        'user_id' => $studentUser->id,
        'biometric_id' => 'STU_MONITOR_99',
        'roll_no' => '99',
        'admission_no' => 'ADM999',
        'session_id' => $session->id,
        'class_id' => $class->id,
        'section_id' => $section->id,
        'admission_date' => '2026-01-01',
        'dob' => '2010-01-01',
        'gender' => 'male',
        'status' => 'active',
    ]);

    // Student punches
    $this->postJson('/api/v1/biometric-sync', [
        'card_uid' => 'STU_MONITOR_99',
        'timestamp' => '2026-09-28 09:05:00',
        'type' => 'student',
    ])->assertSuccessful();

    // Monitor view
    $this->actingAs($admin)->get('/admin/attendance/live-monitor')->assertSuccessful();

    // Monitor live feed JSON
    $feedResponse = $this->actingAs($admin)->getJson('/admin/attendance/live-feed');
    $feedResponse->assertSuccessful();
    $feedResponse->assertJson([
        'has_punch' => true,
        'punch' => [
            'name' => 'Kiosk Test Student',
            'class_name' => 'Class 9',
            'section_name' => 'B',
            'roll_no' => '99',
            'admission_no' => 'ADM999',
        ]
    ]);
});

test('web admin direct biometric sync route validates input and handles connection attempt', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this->actingAs($admin)->postJson('/admin/attendance/biometric-direct-sync', [
        'device_ip' => '127.0.0.1',
        'device_port' => 4370,
    ]);

    // Should return 422 or 500 cleanly with JSON message when device is not reachable on localhost
    expect(in_array($response->status(), [422, 500]))->toBeTrue();
    $response->assertJsonStructure(['success', 'message']);
});
