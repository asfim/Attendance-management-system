@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3 d-print-none">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Hostel Occupancy & Availability Report</h4>
            <p class="text-muted fs-7 mb-0">Overview of hall capacity, room breakdown, and real-time seat availability</p>
        </div>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i> Print Report
        </button>
    </div>

    @include('admin.reports.partials.print_header', [
        'title' => 'Hostel Occupancy & Availability Report'
    ])

    <!-- Overview Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-sm-6">
            <div class="card glass-card p-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="fa-solid fa-building-user fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark-emphasis">{{ $totalHalls }}</div>
                        <div class="text-muted fs-8">Total Halls / Houses</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card glass-card p-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="fa-solid fa-door-open fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark-emphasis">{{ $availableRooms }} / {{ $totalRooms }}</div>
                        <div class="text-muted fs-8">Available Rooms / Total Rooms</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-12">
            <div class="card glass-card p-3 border-0">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="fa-solid fa-bed fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-4 fw-bold text-dark-emphasis">{{ $availableBeds }} / {{ $totalBeds }}</div>
                        <div class="text-muted fs-8">Available Seats / Total Beds</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Hall & Room Availability Breakdown -->
    <div class="row g-4">
        @forelse($hostels as $hall)
            @php
                $allBeds = $hall->rooms->flatMap(function($r) { return $r->beds; });
                $hallTotalBeds = $allBeds->count();
                $hallOccupiedBeds = $allBeds->where('status', 'occupied')->count();
                $hallAvailableBeds = $allBeds->where('status', 'available')->count();
                $hallAvailableRooms = $hall->rooms->filter(function($r) {
                    return $r->beds->where('status', 'available')->count() > 0;
                })->count();
            @endphp
            <div class="col-12">
                <div class="card glass-card">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-3 px-4">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8 mb-1">
                                {{ $hall->type }} Hostel
                            </span>
                            <h5 class="fw-bold m-0 text-dark-emphasis"><i class="fa-solid fa-building-user me-2 text-primary"></i>{{ $hall->name }}</h5>
                        </div>
                        <div class="d-flex gap-2">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                <i class="fa-solid fa-bed me-1"></i>{{ $hallAvailableBeds }} Available Beds
                            </span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                <i class="fa-solid fa-door-closed me-1"></i>{{ $hallAvailableRooms }} / {{ $hall->rooms->count() }} Available Rooms
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-4">Room Number</th>
                                        <th>Room Type</th>
                                        <th>Total Beds</th>
                                        <th>Occupied Beds</th>
                                        <th>Available Beds / Seats</th>
                                        <th>Fee per Bed</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hall->rooms as $room)
                                        @php
                                            $roomTotal = $room->beds->count();
                                            $roomOccupied = $room->beds->where('status', 'occupied')->count();
                                            $roomAvailable = $room->beds->where('status', 'available')->count();
                                        @endphp
                                        <tr>
                                            <td class="ps-4 fw-bold text-dark-emphasis">
                                                <i class="fa-solid fa-door-closed text-secondary me-2"></i>Room {{ $room->room_number }}
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                                    {{ $room->room_type }}
                                                </span>
                                            </td>
                                            <td><span class="fw-semibold fs-7">{{ $roomTotal }}</span></td>
                                            <td><span class="text-danger fw-semibold fs-7">{{ $roomOccupied }}</span></td>
                                            <td>
                                                <span class="fw-bold fs-7 text-success">{{ $roomAvailable }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold fs-7">${{ number_format($room->cost_per_bed, 2) }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                @if($roomAvailable > 0)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                                        <i class="fa-solid fa-circle-check me-1"></i>Available
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i>FULL
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                No rooms registered under {{ $hall->name }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card glass-card text-center py-5 text-muted">
                    <i class="fa-solid fa-chart-pie fs-2 mb-3 text-secondary"></i>
                    No hostel data found to display in the report.
                </div>
            </div>
        @endforelse
    </div>
    
    @include('admin.reports.partials.print_footer')
</div>

<style>
    @media print {
        body { background: #fff !important; }
        .sidebar, .navbar-custom, .d-print-none, header, footer, .breadcrumb { display: none !important; }
        .main-content { margin-left: 0 !important; padding-top: 0 !important; width: 100% !important; }
        .card, .glass-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; break-inside: avoid; }
        .card-header { border-bottom: 1px solid #dee2e6 !important; }
        .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
        .table th, .table td { border: 1px solid #dee2e6 !important; padding: 0.5rem !important; color: #000 !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
    }
</style>
@endsection
