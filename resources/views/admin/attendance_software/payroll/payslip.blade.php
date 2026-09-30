@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.attendance-suite.payroll.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Payroll Sheet
        </a>
        <a href="{{ route('admin.attendance-suite.payroll.slip', [$staff->id, 'month' => $month, 'year' => $year, 'download' => 'pdf']) }}" class="btn btn-primary btn-sm rounded-pill">
            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF Payslip
        </a>
    </div>

    <!-- Printable Payslip Card -->
    <div class="card border-0 shadow rounded-4 p-4 bg-white" style="max-width: 800px; margin: 0 auto;">
        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h3 class="fw-bold text-primary mb-1"><i class="fa-solid fa-building me-2"></i>Attendance & HR Software</h3>
                <p class="text-muted small mb-0">{{ $staff->branchName }} | Official Payslip</p>
            </div>
            <div class="text-end">
                <h5 class="fw-bold text-dark mb-0">MONTHLY PAYSLIP</h5>
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill">{{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</span>
            </div>
        </div>

        <!-- Employee Info Grid -->
        <div class="row g-3 bg-light p-3 rounded-3 mb-4 text-start">
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Employee ID:</span>
                <strong class="text-primary">{{ $staff->employeeId() }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Employee Name:</span>
                <strong>{{ $staff->user?->name }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Department:</span>
                <strong>{{ $staff->departmentName }}</strong>
            </div>
            <div class="col-6 col-md-3">
                <span class="text-muted small d-block">Designation:</span>
                <strong>{{ $staff->designationTitle }}</strong>
            </div>
        </div>

        <!-- Attendance Summary -->
        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-clock me-1 text-primary"></i> Attendance Summary</h6>
        <div class="row text-center g-2 mb-4 small">
            <div class="col-3"><div class="border p-2 rounded-3">Present Days<h5 class="fw-bold text-success mb-0 mt-1">{{ $presentDays }}</h5></div></div>
            <div class="col-3"><div class="border p-2 rounded-3">Late Days<h5 class="fw-bold text-warning mb-0 mt-1">{{ $lateDays }}</h5></div></div>
            <div class="col-3"><div class="border p-2 rounded-3">Absent Days<h5 class="fw-bold text-danger mb-0 mt-1">{{ $absentDays }}</h5></div></div>
            <div class="col-3"><div class="border p-2 rounded-3">Overtime<h5 class="fw-bold text-info mb-0 mt-1">{{ $overtimeHours }} hrs</h5></div></div>
        </div>

        <!-- Earnings & Deductions Table -->
        <div class="row g-4 mb-4">
            <!-- Earnings -->
            <div class="col-md-6">
                <h6 class="fw-bold text-success border-bottom pb-2 mb-3">Earnings</h6>
                <div class="d-flex justify-content-between mb-2"><span>Basic Salary</span><strong>৳ {{ number_format($basicSalary, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Allowances</span><strong>৳ {{ number_format($allowancesTotal, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Overtime Pay</span><strong>৳ {{ number_format($overtimePay, 2) }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold text-dark"><span>Total Gross Earnings</span><strong>৳ {{ number_format($grossSalary + $overtimePay, 2) }}</strong></div>
            </div>

            <!-- Deductions -->
            <div class="col-md-6">
                <h6 class="fw-bold text-danger border-bottom pb-2 mb-3">Deductions</h6>
                <div class="d-flex justify-content-between mb-2"><span>Late Deduction (3 Late = 1 Day)</span><strong class="text-danger">- ৳ {{ number_format($lateDeduction, 2) }}</strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Absent Deduction</span><strong class="text-danger">- ৳ {{ number_format($absentDeduction, 2) }}</strong></div>
                <hr>
                <div class="d-flex justify-content-between fw-bold text-danger"><span>Total Deductions</span><strong>- ৳ {{ number_format($lateDeduction + $absentDeduction, 2) }}</strong></div>
            </div>
        </div>

        <!-- Net Salary Total Banner -->
        <div class="p-3 bg-success bg-opacity-10 border border-success rounded-3 d-flex justify-content-between align-items-center mb-4">
            <span class="fw-bold text-success fs-5">NET PAYABLE AMOUNT</span>
            <span class="fw-bold text-success fs-3">৳ {{ number_format($netSalary, 2) }}</span>
        </div>

        <!-- Signatures Footer -->
        <div class="row text-center mt-5 pt-4 border-top">
            <div class="col-6">
                <div class="border-top border-dark w-50 mx-auto pt-1 small fw-semibold">Employee Signature</div>
            </div>
            <div class="col-6">
                <div class="border-top border-dark w-50 mx-auto pt-1 small fw-semibold">HR / Authorised Signature</div>
            </div>
        </div>
    </div>
</div>
@endsection
