@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-money-check-dollar text-primary me-2"></i>Attendance-Based Salary & Payroll Sheet</h3>
            <p class="text-muted small mb-0">Monthly Salary Calculation based on Present, Late Deductions, Absent Deductions, & Overtime Pay</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.attendance-suite.payroll.index') }}" method="GET" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endfor
                </select>
                <select name="year" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    @for($y=date('Y'); $y>=date('Y')-2; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <select name="branch_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    <!-- Salary Sheet Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Staff Name</th>
                        <th>Basic Salary</th>
                        <th>Attendance (P/L/A/Lve)</th>
                        <th>Overtime (Hrs/Pay)</th>
                        <th>Late Deduction</th>
                        <th>Absent Deduction</th>
                        <th>Net Payable</th>
                        <th class="text-end">Payslip Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrollSheets as $item)
                        @php $staff = $item['staff']; @endphp
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $staff->photoUrl() }}" class="rounded-circle" width="34" height="34" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $staff->user?->name }}</div>
                                        <span class="text-muted small">{{ $staff->employeeId() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-dark">৳ {{ number_format($item['basic_salary'], 2) }}</td>
                            <td>
                                <div class="small">
                                    <span class="badge bg-success me-1">P: {{ $item['present_days'] }}</span>
                                    <span class="badge bg-warning text-dark me-1">L: {{ $item['late_days'] }}</span>
                                    <span class="badge bg-danger me-1">A: {{ $item['absent_days'] }}</span>
                                    <span class="badge bg-primary">Lve: {{ $item['leave_days'] }}</span>
                                </div>
                            </td>
                            <td>
                                @if($item['overtime_hours'] > 0)
                                    <div class="text-success fw-bold">+ ৳ {{ number_format($item['overtime_pay'], 2) }}</div>
                                    <span class="text-muted small">({{ $item['overtime_hours'] }} hrs)</span>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td>
                                @if($item['late_deduction'] > 0)
                                    <span class="text-danger fw-bold">- ৳ {{ number_format($item['late_deduction'], 2) }}</span>
                                @else
                                    <span class="text-muted">৳ 0.00</span>
                                @endif
                            </td>
                            <td>
                                @if($item['absent_deduction'] > 0)
                                    <span class="text-danger fw-bold">- ৳ {{ number_format($item['absent_deduction'], 2) }}</span>
                                @else
                                    <span class="text-muted">৳ 0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success bg-opacity-10 text-success fs-6 px-3 py-2 rounded-pill fw-bold">৳ {{ number_format($item['net_salary'], 2) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.attendance-suite.payroll.slip', [$staff->id, 'month' => $month, 'year' => $year]) }}" class="btn btn-sm btn-outline-primary rounded-pill me-1" target="_blank">
                                    <i class="fa-solid fa-eye me-1"></i> View Slip
                                </a>
                                <a href="{{ route('admin.attendance-suite.payroll.slip', [$staff->id, 'month' => $month, 'year' => $year, 'download' => 'pdf']) }}" class="btn btn-sm btn-primary rounded-pill">
                                    <i class="fa-solid fa-download me-1"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">No salary sheet generated for this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
