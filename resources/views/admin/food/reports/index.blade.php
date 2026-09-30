@extends('layouts.app')

@section('title', 'Food Reports')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-file-export text-primary me-2"></i>Food Management Reports
            </h5>
            <p class="text-muted fs-8 mb-0">Comprehensive reports by Student, Class, Month, and Meal type</p>
        </div>
    </div>

    {{-- Filter Form --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="{{ route('admin.food.reports.index') }}" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="col-md-4">
                    <label class="form-label text-secondary fs-8 fw-semibold mb-1">
                        <i class="fa-regular fa-calendar-days me-1 text-primary"></i>Select Month & Year
                    </label>
                    <input type="month" name="month_year" class="form-control form-control-sm bg-body text-body border-secondary-subtle fw-semibold" 
                           value="{{ sprintf('%04d-%02d', $year, $month) }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="fa-solid fa-filter me-1"></i>Generate Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Report Tabs --}}
    <ul class="nav nav-tabs mb-3 border-bottom fs-7" id="reportTab">
        <li class="nav-item">
            <a href="{{ route('admin.food.reports.index', ['month' => $month, 'year' => $year, 'tab' => 'student']) }}" class="nav-link {{ $tab === 'student' ? 'active fw-bold' : '' }}">
                <i class="fa-solid fa-user me-1"></i>Student-wise Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.food.reports.index', ['month' => $month, 'year' => $year, 'tab' => 'class']) }}" class="nav-link {{ $tab === 'class' ? 'active fw-bold' : '' }}">
                <i class="fa-solid fa-school me-1"></i>Class-wise Report
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.food.reports.index', ['month' => $month, 'year' => $year, 'tab' => 'meal']) }}" class="nav-link {{ $tab === 'meal' ? 'active fw-bold' : '' }}">
                <i class="fa-solid fa-bowl-food me-1"></i>Meal-wise Report
            </a>
        </li>
    </ul>

    {{-- Tab Content --}}
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-0">
            @if($tab === 'student')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light fs-8 text-secondary">
                            <tr>
                                <th class="ps-4 py-2">#</th>
                                <th class="py-2">Student Name</th>
                                <th class="py-2">Class</th>
                                <th class="py-2">Food Plan</th>
                                <th class="py-2 text-end">Base Fee</th>
                                <th class="py-2 text-center">Absent Days</th>
                                <th class="py-2 text-end">Adjustment</th>
                                <th class="pe-4 py-2 text-end">Final Payable</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($studentReport as $i => $row)
                                @php
                                    $st = $row['student'];
                                    $c = $row['calculation'];
                                @endphp
                                <tr>
                                    <td class="ps-4 py-2 text-muted">{{ $i + 1 }}</td>
                                    <td class="py-2 fw-bold text-dark-emphasis">{{ $st->user->name ?? 'Student' }}</td>
                                    <td class="py-2 text-muted">{{ $st->schoolClass->name ?? '-' }}</td>
                                    <td class="py-2"><span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">{{ $st->foodAllocation->foodPlan->name ?? 'Default' }}</span></td>
                                    <td class="py-2 text-end">৳{{ number_format($c['monthly_fee'], 2) }}</td>
                                    <td class="py-2 text-center">{{ $c['non_consumption_days'] }} Days</td>
                                    <td class="py-2 text-end text-danger">-৳{{ number_format($c['calculated_deduction'] + $c['manual_adjustment'], 2) }}</td>
                                    <td class="pe-4 py-2 text-end fw-bold text-success">৳{{ number_format($c['final_food_fee'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-4 text-muted">No records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @elseif($tab === 'class')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light fs-8 text-secondary">
                            <tr>
                                <th class="ps-4 py-2">Class Name</th>
                                <th class="py-2 text-center">Total Food Students</th>
                                <th class="py-2 text-end">Total Base Fee</th>
                                <th class="py-2 text-end">Total Adjustments</th>
                                <th class="pe-4 py-2 text-end">Net Payable Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classReport as $cr)
                                <tr>
                                    <td class="ps-4 py-2 fw-bold text-dark-emphasis">{{ $cr['class_name'] }}</td>
                                    <td class="py-2 text-center"><span class="badge bg-primary px-2 py-1">{{ $cr['student_count'] }}</span></td>
                                    <td class="py-2 text-end">৳{{ number_format($cr['total_fee'], 2) }}</td>
                                    <td class="py-2 text-end text-danger">-৳{{ number_format($cr['total_deduction'], 2) }}</td>
                                    <td class="pe-4 py-2 text-end fw-bold text-success">৳{{ number_format($cr['net_payable'], 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($tab === 'meal')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-7">
                        <thead class="bg-light fs-8 text-secondary">
                            <tr>
                                <th class="ps-4 py-2">Meal Type</th>
                                <th class="py-2 text-center">Total Meals Consumed (Taken)</th>
                                <th class="pe-4 py-2 text-center">Total Meals Missed (Not Taken)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mealReport as $mr)
                                <tr>
                                    <td class="ps-4 py-2 fw-bold text-dark-emphasis">{{ $mr['meal_name'] }}</td>
                                    <td class="py-2 text-center"><span class="badge bg-success px-3 py-1 fs-8">{{ $mr['taken'] }}</span></td>
                                    <td class="pe-4 py-2 text-center"><span class="badge bg-danger px-3 py-1 fs-8">{{ $mr['not_taken'] }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
