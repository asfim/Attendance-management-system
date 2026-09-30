@extends('layouts.app')

@section('title', 'My Transport')

@section('content')
<style>
.transport-card {
    border-radius: 16px;
    border: 1px solid var(--bs-border-color);
}
.info-pill-card {
    background: rgba(13, 110, 253, 0.03);
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
    border-color: rgba(13, 110, 253, 0.3);
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
                <h5 class="fw-bold m-0"><i class="fa-solid fa-bus text-primary me-2"></i>My Transport</h5>
                <p class="text-muted fs-7 mb-0">Your current transport route allocation and history</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if($allocation && $allocation->status === 'active')
            {{-- Active Allocation Card --}}
            <div class="card transport-card shadow-sm mb-4">
                <div class="card-body p-4">
                    {{-- Status Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-shape bg-primary bg-opacity-10 text-primary">
                                <i class="fa-solid fa-bus"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">Active Transport Allocation</h6>
                                <div class="text-muted fs-8">Registered Transport Service</div>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 fs-8">
                            <i class="fa-solid fa-circle-check me-1"></i>Active Service
                        </span>
                    </div>

                    {{-- 4 Info Pills --}}
                    <div class="row g-3">
                        {{-- Route --}}
                        <div class="col-md-3">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-primary bg-opacity-10 text-primary" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-route"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Route</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $allocation->route->route_name ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        {{-- Stop --}}
                        <div class="col-md-3">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-info bg-opacity-10 text-info" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-location-dot"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Stop / Stoppage</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $allocation->stop->stop_name ?? 'No specific stop' }}
                                </div>
                            </div>
                        </div>

                        {{-- Monthly Fee --}}
                        <div class="col-md-3">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-success bg-opacity-10 text-success" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-bangladeshi-taka-sign"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Monthly Fee</div>
                                </div>
                                <div class="fw-bold fs-6 text-success ms-1">
                                    ৳{{ number_format($allocation->monthly_fee, 2) }}
                                </div>
                            </div>
                        </div>

                        {{-- Effective From --}}
                        <div class="col-md-3">
                            <div class="info-pill-card h-100">
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="icon-shape bg-warning bg-opacity-10 text-warning" style="width: 32px; height: 32px; font-size: 0.9rem;">
                                        <i class="fa-solid fa-calendar-check"></i>
                                    </div>
                                    <div class="text-muted fs-8 text-uppercase fw-semibold" style="letter-spacing: 1px;">Effective From</div>
                                </div>
                                <div class="fw-bold fs-7 text-dark-emphasis ms-1">
                                    {{ $allocation->effective_from ? $allocation->effective_from->format('d M, Y') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-bus-slash text-muted mb-3" style="font-size: 3rem; opacity: 0.3;"></i>
                    <h6 class="text-muted fw-semibold">No Active Transport Allocation</h6>
                    <p class="text-muted fs-7 mb-0">You don't have an active transport route allocation at the moment. Please contact administration.</p>
                </div>
            </div>
        @endif

        {{-- History --}}
        @if($histories->count())
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Transport History Log</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="fs-7 text-secondary">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Action</th>
                                    <th>Route</th>
                                    <th>Stop</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                @foreach($histories as $i => $h)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="badge fs-8
                                                @if($h->action === 'allocated') bg-success bg-opacity-10 text-success border border-success border-opacity-25
                                                @elseif($h->action === 'released') bg-secondary bg-opacity-10 text-secondary border
                                                @else bg-info bg-opacity-10 text-info border border-info border-opacity-25
                                                @endif">
                                                {{ ucfirst($h->action) }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark-emphasis">{{ $h->route->route_name ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ $h->stop->stop_name ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ $h->action_date ? \Carbon\Carbon::parse($h->action_date)->format('d M, Y') : 'N/A' }}</td>
                                        <td class="text-muted fs-8">{{ $h->reason ?? 'Routine Allocation' }}</td>
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
