@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-gauge text-primary me-2"></i> Dashboard
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">Welcome back, {{ $user->name }}!</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card border border-secondary border-opacity-25 rounded-3 h-100 bg-transparent text-center p-4">
                <i class="fa-solid fa-id-badge text-primary mb-3 fs-3"></i>
                <h6 class="text-light fw-bold">{{ $staff->designation ?? $user->role->display_name }}</h6>
                <p class="text-muted fs-7 mb-0">Designation</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card border border-secondary border-opacity-25 rounded-3 h-100 bg-transparent text-center p-4">
                <i class="fa-solid fa-building text-info mb-3 fs-3"></i>
                <h6 class="text-light fw-bold">{{ $staff->department ?? 'N/A' }}</h6>
                <p class="text-muted fs-7 mb-0">Department</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card border border-secondary border-opacity-25 rounded-3 h-100 bg-transparent text-center p-4">
                <i class="fa-solid fa-envelope text-warning mb-3 fs-3"></i>
                <h6 class="text-light fw-bold fs-7">{{ $user->email }}</h6>
                <p class="text-muted fs-7 mb-0">Email</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="card glass-card border border-secondary border-opacity-25 rounded-3 h-100 bg-transparent text-center p-4">
                <i class="fa-solid fa-phone text-success mb-3 fs-3"></i>
                <h6 class="text-light fw-bold">{{ $staff->phone ?? 'N/A' }}</h6>
                <p class="text-muted fs-7 mb-0">Contact</p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row mt-4">
        <div class="col-12">
            <h6 class="fw-bold text-light mb-3">Quick Links</h6>
        </div>
        <div class="col-md-6">
            <a href="{{ route('staff.attendance') }}" class="text-decoration-none">
                <div class="card glass-card border border-secondary border-opacity-25 rounded-3 bg-transparent p-4 d-flex flex-row align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded text-primary p-3 me-3">
                        <i class="fa-solid fa-user-clock fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-light fw-bold mb-1">My Attendance</h6>
                        <p class="text-muted fs-7 mb-0">View your daily attendance records.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
            <a href="{{ route('staff.salary') }}" class="text-decoration-none">
                <div class="card glass-card border border-secondary border-opacity-25 rounded-3 bg-transparent p-4 d-flex flex-row align-items-center">
                    <div class="bg-success bg-opacity-10 rounded text-success p-3 me-3">
                        <i class="fa-solid fa-money-check-dollar fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-light fw-bold mb-1">My Salary</h6>
                        <p class="text-muted fs-7 mb-0">View your payslips and salary history.</p>
                    </div>
                    <i class="fa-solid fa-chevron-right ms-auto text-muted"></i>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
