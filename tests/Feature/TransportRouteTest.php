<?php

use App\Models\User;
use App\Models\TransportRoute;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('admin can update a transport route', function () {
    $this->seed();

    $admin = User::where('email', 'admin@school.com')->first();

    $route = TransportRoute::create([
        'route_name' => 'Test Route',
        'start_point' => 'A',
        'end_point' => 'B',
        'default_monthly_fee' => 500.00,
        'status' => 'active',
    ]);

    $response = $this->actingAs($admin)
        ->put(route('admin.transport.routes.update', $route->id), [
            'route_name' => 'Updated Route Name',
            'start_point' => 'C',
            'end_point' => 'D',
            'default_monthly_fee' => 600.00,
            'status' => 'inactive',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('transport_routes', [
        'id' => $route->id,
        'route_name' => 'Updated Route Name',
        'start_point' => 'C',
        'end_point' => 'D',
        'default_monthly_fee' => 600.00,
        'status' => 'inactive',
    ]);
});
