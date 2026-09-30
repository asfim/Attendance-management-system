@extends('layouts.app')

@section('title', 'Food Management Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-utensils text-primary me-2"></i>Food Management Dashboard
            </h5>
            <p class="text-muted fs-8 mb-0">Daily meal metrics, food revenue, and non-consumption adjustment overview</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.food.attendance.index') }}" class="btn btn-sm btn-primary">
                <i class="fa-solid fa-calendar-check me-1"></i>Mark Attendance
            </a>
            <a href="{{ route('admin.fees.collection') }}" class="btn btn-sm btn-outline-success">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i>Fee Collection
            </a>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #0ea5e9 !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Total Food Students</div>
                <div class="fw-bold fs-4 text-info">{{ $totalFoodStudents }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #22c55e !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Today's Meals Taken</div>
                <div class="fw-bold fs-4 text-success">{{ $todayTaken }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Today's Not Taken</div>
                <div class="fw-bold fs-4 text-danger">{{ $todayNotTaken }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #6366f1 !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Monthly Revenue</div>
                <div class="fw-bold fs-5 text-primary">৳{{ number_format($monthlyRevenue, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #f59e0b !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Monthly Due</div>
                <div class="fw-bold fs-5 text-warning">৳{{ number_format($monthlyDue, 2) }}</div>
            </div>
        </div>
        <div class="col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #a855f7 !important;">
                <div class="text-muted fs-8 text-uppercase fw-semibold mb-1">Total Adjustments</div>
                <div class="fw-bold fs-5" style="color: #a855f7;">৳{{ number_format($totalAdjustments, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Active Meals Grid --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-transparent border-bottom py-3 px-4">
            <h6 class="fw-bold m-0"><i class="fa-solid fa-bowl-food text-primary me-2"></i>Active Configured Meals</h6>
        </div>
        <div class="card-body p-4">
            @if($activeMeals->count())
                <div class="row g-3">
                    @foreach($activeMeals as $meal)
                        <div class="col-md-3">
                            <div class="card border shadow-sm p-3" style="border-radius: 12px;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1">{{ $meal->name }}</h6>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">{{ $meal->code ?? 'N/A' }}</span>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8">Active</span>
                                </div>
                                <div class="mt-2 text-muted fs-8">
                                    <i class="fa-solid fa-clock me-1"></i>Time: {{ $meal->time ?? 'Not Specified' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4 text-muted fs-7">
                    <i class="fa-solid fa-utensils fa-2x mb-2 opacity-25"></i>
                    <p>No active meals configured yet. Go to <a href="{{ route('admin.food.meals.index') }}" class="text-primary">Meal Setup</a> to add meals.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
