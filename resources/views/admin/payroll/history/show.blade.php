@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <!-- Header with Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-light"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Staff
                    History</h4>
                <p class="text-muted fs-7 mb-0">Detailed payroll and attendance records.</p>
            </div>
            <a href="{{ route('admin.payroll.history.index') }}" class="btn btn-sm btn-outline-secondary px-3"
                style="border-radius: 8px;">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <!-- Staff Profile Card -->
        <div class="card glass-card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4 d-flex align-items-center gap-4">
                @if ($staff->photo)
                    <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo"
                        class="rounded-circle object-fit-cover border border-secondary border-opacity-25"
                        style="width: 80px; height: 80px;">
                @else
                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold fs-3"
                        style="width: 80px; height: 80px;">
                        {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <h5 class="fw-bold text-light mb-1">{{ $staff->user->name }}</h5>
                    <div class="d-flex align-items-center gap-3 text-muted fs-7">
                        <span><i class="fa-solid fa-id-badge me-1"></i>
                            #STF-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span><i class="fa-solid fa-briefcase me-1"></i> {{ $staff->designation ?? 'N/A' }}
                            ({{ $staff->user->role ? $staff->user->role->display_name : 'Unassigned' }})</span>
                        <span><i class="fa-solid fa-phone me-1"></i> {{ $staff->phone ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nav Tabs -->
        <ul class="nav nav-pills mb-3 gap-2" id="history-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill px-4" id="salary-tab" data-bs-toggle="pill"
                    data-bs-target="#salary-content" type="button" role="tab">
                    <i class="fa-solid fa-money-check-dollar me-2"></i>Salary History
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="history-tabContent">

            <!-- Salary Tab -->
            <div class="tab-pane fade show active" id="salary-content" role="tabpanel">
                <div class="card glass-card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-4">
                        <h6 class="fw-bold mb-0 text-light">Salary Records</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle text-light mb-0"
                            style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                            <thead style="background-color: rgba(0,0,0,0.2);">
                                <tr>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0">Month & Year
                                    </th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Basic</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Allowances/Bonus
                                    </th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Deductions</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Net Salary</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Status</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Paid On</th>
                                    <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaries as $salary)
                                    <tr class="border-bottom border-secondary border-opacity-10 hover-bg-secondary transition-all">
                                        <td class="px-4 py-3 fw-semibold">
                                            {{ date('F Y', mktime(0, 0, 0, $salary->month, 1, $salary->year)) }}
                                        </td>
                                        <td>৳{{ number_format($salary->basic_salary, 2) }}</td>
                                        <td class="text-success">+৳{{ number_format($salary->grossSalary() - $salary->basic_salary, 2) }}</td>
                                        <td class="text-danger">-৳{{ number_format($salary->totalDeductions(), 2) }}</td>
                                        <td class="fw-bold text-primary">৳{{ number_format($salary->net_salary, 2) }}</td>
                                        <td>
                                            <span class="badge bg-{{ $salary->statusBadge() }} bg-opacity-10 text-{{ $salary->statusBadge() }} border border-{{ $salary->statusBadge() }} border-opacity-25 rounded-pill px-2 py-1 fs-7">
                                                {{ ucfirst($salary->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-7">
                                            {{ $salary->payment_date ? $salary->payment_date->format('d M, Y') : '-' }}
                                        </td>
                                        <td class="px-4 text-end">
                                            <a href="{{ route('admin.payroll.slip', $salary->id) }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:6px;font-size:0.75rem;">
                                                <i class="fa-solid fa-file-invoice me-1"></i>Slip
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">No salary records found for this staff member.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .nav-pills .nav-link {
            color: var(--bs-body-color);
            opacity: 0.7;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .nav-pills .nav-link:not(.active):hover {
            color: var(--bs-body-color);
            opacity: 1;
        }

        .nav-pills .nav-link.active {
            background-color: var(--bs-primary);
            color: white !important;
            opacity: 1;
        }

        /* Theme-specific Hover Backgrounds */
        [data-bs-theme="light"] .nav-pills .nav-link:not(.active):hover,
        [data-bs-theme="light"] .hover-bg-secondary:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }

        [data-bs-theme="dark"] .nav-pills .nav-link:not(.active):hover,
        [data-bs-theme="dark"] .hover-bg-secondary:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
        }

        .transition-all {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endsection
