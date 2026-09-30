<?php

use App\Models\User;
use App\Models\Role;
use App\Models\BiometricDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin can list and create biometric devices', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $response = $this->actingAs($admin)->get('/admin/biometric-devices');
    $response->assertSuccessful();

    $response = $this->actingAs($admin)->post('/admin/biometric-devices', [
        'name' => 'Branch 1 Gate K40',
        'ip_address' => '192.168.1.205',
        'port' => 4370,
        'location' => 'Branch 1 - Gate A',
        'device_sn' => 'K40_SN_999',
        'status' => 'offline',
    ]);

    $response->assertRedirect('/admin/biometric-devices');
    $this->assertDatabaseHas('biometric_devices', [
        'name' => 'Branch 1 Gate K40',
        'ip_address' => '192.168.1.205',
        'location' => 'Branch 1 - Gate A',
    ]);
});

test('admin can update biometric device configuration', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $device = BiometricDevice::create([
        'name' => 'Old Device Name',
        'ip_address' => '192.168.1.200',
        'port' => 4370,
        'status' => 'offline',
    ]);

    $response = $this->actingAs($admin)->put("/admin/biometric-devices/{$device->id}", [
        'name' => 'New Updated Device Name',
        'ip_address' => '192.168.1.210',
        'port' => 4370,
        'location' => 'Main Gate',
        'status' => 'online',
    ]);

    $response->assertRedirect('/admin/biometric-devices');
    $this->assertDatabaseHas('biometric_devices', [
        'id' => $device->id,
        'name' => 'New Updated Device Name',
        'ip_address' => '192.168.1.210',
        'status' => 'online',
    ]);
});

test('admin can test device connection and handle unreachable IP cleanly', function () {
    $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $device = BiometricDevice::create([
        'name' => 'Test Machine',
        'ip_address' => '127.0.0.1',
        'port' => 4370,
        'status' => 'online',
    ]);

    $response = $this->actingAs($admin)->postJson("/admin/biometric-devices/{$device->id}/test");
    
    // Will return 422 or 500 when port 4370 on 127.0.0.1 fails connection
    expect(in_array($response->status(), [422, 500]))->toBeTrue();
    $response->assertJson([
        'success' => false,
        'status' => 'offline',
    ]);

    expect($device->fresh()->status)->toBe('offline');
});
