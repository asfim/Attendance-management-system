@extends('layouts.app')

@section('title', 'My Hostel')

@section('content')
<style>
.hostel-card {
    border-radius: 16px;
    border: 1px solid var(--bs-border-color);
}
.info-pill-card {
    background: rgba(34, 197, 94, 0.03);
    border: 1px solid var(--bs-border-color);
    border-radius: 12px;
    padding: 16px 20px;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
}
[data-bs-theme="dark"] .info-pill-card {
    background: rgba(255, 255, 255, 0.03);
}
.info-pill-card:hover {
    transform: translateY(-2px);
    border-color: rgba(34, 197, 94, 0.3);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
.icon-shape {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-hotel text-success me-2"></i>My Hostel</h5>
                <p class="text-muted fs-7 mb-0">Your current hostel accommodation and bed details</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if($allocation)
            {{-- Active Allocation Card --}}
            <div class="card hostel-card shadow-sm mb-4">
                <div class="card-body p-4">
                    {{-- Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-success bg-opacity-10 text-success">
                                <i class="fa-solid fa-hotel"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Active Hostel Allocation</h6>
                                <div class="text-muted fs-8">Registered Hall &amp; Room Facility</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-8">
                            <i class="fa-solid fa-circle-check me-1"></i>Active Accommodation
                        </span>
                    </div>

                    {{-- Info Cards --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Hall / Hostel</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $allocation->bed?->room?->hostel?->name ?? 'N/A' }}
                                </div>
                                <div class="text-muted fs-8 ms-1">{{ $allocation->bed?->room?->hostel?->type ?? '' }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-info bg-opacity-10 text-info" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-door-open"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Room Details</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    Room {{ $allocation->bed?->room?->room_number ?? 'N/A' }}
                                </div>
                                <div class="text-muted fs-8 ms-1">{{ $allocation->bed?->room?->room_type ?? '' }}</div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-bed"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Bed Number</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    Bed {{ $allocation->bed?->bed_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Monthly Bed Fee</div>
                                </div>
                                <div class="fw-bold fs-6 text-success ms-1">
                                    ৳{{ number_format($allocation->bed?->room?->cost_per_bed ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Allocation Date</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $allocation->allocation_date ? $allocation->allocation_date->format('d M, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-hotel text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h6 class="text-muted fw-semibold">No Active Hostel Allocation</h6>
                    <p class="text-muted fs-7 mb-0">You don't have an active hostel bed allocation. Please contact the administration.</p>
                </div>
            </div>
        @endif

        {{-- History --}}
        @if($allAllocations->count())
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Hostel Allocation History</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="fs-7 text-secondary">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Hostel</th>
                                    <th>Room / Bed</th>
                                    <th>Allocation Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                @foreach($allAllocations as $i => $alloc)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-bold text-dark-emphasis">{{ $alloc->bed?->room?->hostel?->name ?? 'N/A' }}</td>
                                        <td class="text-muted">
                                            Room {{ $alloc->bed?->room?->room_number ?? '-' }} / Bed {{ $alloc->bed?->bed_number ?? '-' }}
                                        </td>
                                        <td class="text-muted">
                                            {{ $alloc->allocation_date ? $alloc->allocation_date->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            @if($alloc->status === 'active')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8">
                                                    <i class="fa-solid fa-circle-check me-1"></i>Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                                    Released
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
