@extends('layouts.app')

@section('title', 'Attendance History')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-1"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Attendance History</h4>
            <p class="text-muted mb-0">Daily attendance summary report for all classes and sections.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">
            <form action="{{ route('admin.attendance.history') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="date" class="form-label fw-bold mb-0">Select Date:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" onchange="this.form.submit()">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fa-solid fa-filter me-2"></i>Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-5">
        @forelse($history as $row)
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100 position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #3b82f6, #8b5cf6);"></div>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1 text-body">{{ $row['class_name'] }}</h4>
                                <span class="badge bg-secondary bg-opacity-10 text-body border border-secondary border-opacity-25 px-3 py-1 rounded-pill">
                                    <i class="bi bi-bookmark me-1"></i>{{ $row['section_name'] }}
                                </span>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fa-solid fa-users fs-5"></i>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h2 class="fw-bold mb-0 text-body">{{ $row['total_students'] }}</h2>
                            <p class="small text-muted mb-0 fw-medium text-uppercase tracking-wide">Total Students</p>
                        </div>
                        
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-3 rounded-3 border-0 h-100" style="background-color: #15803d; box-shadow: 0 4px 6px -1px rgba(21, 128, 61, 0.4);">
                                    <h4 class="fw-bold mb-1 text-white">{{ $row['present'] }}</h4>
                                    <span class="small fw-bold text-uppercase text-white" style="font-size: 0.75rem; opacity: 0.9;">Present</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 border-0 h-100" style="background-color: #d97706; box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.4);">
                                    <h4 class="fw-bold mb-1 text-white">{{ $row['late'] }}</h4>
                                    <span class="small fw-bold text-uppercase text-white" style="font-size: 0.75rem; opacity: 0.9;">Late</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 border-0 h-100" style="background-color: #b91c1c; box-shadow: 0 4px 6px -1px rgba(185, 28, 28, 0.4);">
                                    <h4 class="fw-bold mb-1 text-white">{{ $row['absent'] }}</h4>
                                    <span class="small fw-bold text-uppercase text-white" style="font-size: 0.75rem; opacity: 0.9;">Absent</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-folder2-open fs-1 d-block text-secondary mb-2" style="font-size: 3rem !important;"></i>
                <h5 class="fw-bold">No Records Found</h5>
                <p>No attendance history found for the selected date.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
