@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-wallet text-info me-2"></i>Salary & Payslips</h3>
            <p class="text-muted small mb-0">View your monthly salary breakdown and download payslips</p>
        </div>
        
        <form action="{{ route('employee.salary') }}" method="GET" class="d-flex gap-2">
            <select name="month" class="form-select rounded-pill" onchange="this.form.submit()">
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
            <select name="year" class="form-select rounded-pill" onchange="this.form.submit()">
                @for($y=date('Y'); $y>=date('Y')-2; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </form>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-5">
                <div class="text-center mb-4">
                    <h4 class="fw-bold">Salary Statement</h4>
                    <p class="text-muted mb-0">For the month of {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</p>
                </div>

                <div class="bg-light p-4 rounded-4 mb-4">
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="text-muted">Basic Salary:</span>
                        <strong class="text-body">৳ {{ number_format($basicSalary, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="text-muted">Overtime Earnings:</span>
                        <strong class="text-success">+ ৳ {{ number_format($overtimePay, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="text-muted">Late Deductions:</span>
                        <strong class="text-danger">- ৳ {{ number_format($lateDeduction, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3 fs-5">
                        <span class="text-muted">Absent Deductions:</span>
                        <strong class="text-danger">- ৳ {{ number_format($absentDeduction, 2) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold text-success" style="font-size: 1.5rem;">
                        <span>Net Payable:</span>
                        <span>৳ {{ number_format($netSalary, 2) }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.attendance-suite.payroll.slip', [$staff->id, 'month' => $month, 'year' => $year]) }}" class="btn btn-primary rounded-pill w-100 py-3 fs-5 shadow-sm" target="_blank">
                    <i class="fa-solid fa-file-invoice-dollar me-2"></i> Download Official Payslip
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
