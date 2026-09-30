@extends('layouts.app')

@section('title', 'Transport Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-bus-simple text-primary me-2"></i>Transport System
            </h2>
            <p class="text-muted mb-0">Overview of transport routes, allocations, and revenue</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.transport.routes') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-route me-1"></i> Manage Routes
            </a>
            <a href="{{ route('admin.transport.allocations') }}" class="btn btn-primary">
                <i class="fa-solid fa-users me-1"></i> View Allocations
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <!-- Total Routes -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white shadow h-100 border-0" style="border-radius: 15px; border-left: 5px solid #3b82f6 !important;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #93c5fd; letter-spacing: 1px;">Total Routes</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $totalRoutes }}</div>
                            <div class="mt-2 text-sm">
                                <span class="text-success"><i class="fas fa-check-circle me-1"></i>{{ $activeRoutes }} Active</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-route fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Stops -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white shadow h-100 border-0" style="border-radius: 15px; border-left: 5px solid #10b981 !important;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #6ee7b7; letter-spacing: 1px;">Total Stops</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $totalStops }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-map-pin fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Allocations -->
        <div class="col-xl-3 col-md-6">
            <div class="card bg-dark text-white shadow h-100 border-0" style="border-radius: 15px; border-left: 5px solid #8b5cf6 !important;">
                <div class="card-body py-4">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #c4b5fd; letter-spacing: 1px;">Active Allocations</div>
                            <div class="h3 mb-0 font-weight-bold">{{ $totalAllocations }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-users fa-2x text-gray-300" style="opacity: 0.5;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Route Overview -->
    <div class="card shadow mb-4 border-0" style="border-radius: 15px;">
        <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between" style="border-radius: 15px 15px 0 0; border-bottom: 1px solid rgba(0,0,0,0.05);">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-list me-2"></i>Route Overview</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Route Name</th>
                            <th class="py-3">Points</th>
                            <th class="py-3">Monthly Fee (৳)</th>
                            <th class="py-3 text-center">Stops</th>
                            <th class="py-3 text-center">Students</th>
                            <th class="py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                            <tr>
                                <td class="px-4 py-3 fw-bold">
                                    <a href="{{ route('admin.transport.routes.stops', $route->id) }}" class="text-decoration-none">
                                        {{ $route->route_name }}
                                        @if($route->route_code)
                                            <span class="badge bg-secondary ms-1">{{ $route->route_code }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="py-3 text-muted">
                                    {{ $route->start_point }} <i class="fa-solid fa-arrow-right mx-1" style="font-size: 0.8em"></i> {{ $route->end_point }}
                                </td>
                                <td class="py-3 fw-semibold text-success">
                                    {{ number_format($route->default_monthly_fee, 2) }}
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-info rounded-pill">{{ $route->stops_count }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-primary rounded-pill">{{ $route->allocations_count }}</span>
                                </td>
                                <td class="py-3 text-center">
                                    @if($route->status == 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="fa-solid fa-circle-check me-1"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-2">
                                            <i class="fa-solid fa-ban me-1"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-route fa-3x mb-3" style="opacity: 0.2"></i>
                                    <h5>No Routes Found</h5>
                                    <p>Start by adding your first transport route.</p>
                                    <a href="{{ route('admin.transport.routes') }}" class="btn btn-primary mt-2">Add Route</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
