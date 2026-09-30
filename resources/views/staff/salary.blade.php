@extends('layouts.app')

@section('title', 'My Salary')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-money-check-dollar text-primary me-2"></i> My Salary
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">View your payslips and salary history.</p>
        </div>
    </div>

    <div class="card glass-card border border-secondary border-opacity-25 rounded-3 bg-transparent p-4 mb-4">
        <h6 class="fw-bold text-light mb-3">Salary History</h6>
        @if($salaries->isEmpty())
            <div class="text-center p-5">
                <i class="fa-solid fa-receipt text-muted fs-1 mb-3"></i>
                <p class="text-muted">No salary records found.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-dark table-hover table-borderless align-middle mb-0">
                    <thead class="border-bottom border-secondary border-opacity-50">
                        <tr>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-start">Month/Year</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-end">Basic Salary</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-end">Allowances</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-end">Deductions</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-end">Net Salary</th>
                            <th class="text-muted fw-semibold fs-7 pb-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($salaries as $salary)
                            <tr class="border-bottom border-secondary border-opacity-10">
                                <td class="py-3 text-light fs-7">
                                    {{ date("F", mktime(0, 0, 0, $salary->month, 10)) }} {{ $salary->year }}
                                </td>
                                <td class="py-3 text-end text-light fs-7">
                                    {{ number_format($salary->basic_salary, 2) }}
                                </td>
                                <td class="py-3 text-end text-light fs-7">
                                    {{ number_format($salary->house_allowance + $salary->medical_allowance + $salary->transport_allowance + $salary->food_allowance + $salary->other_allowances, 2) }}
                                </td>
                                <td class="py-3 text-end text-light fs-7">
                                    {{ number_format($salary->absent_deduction + $salary->late_deduction + $salary->other_deduction + $salary->tax, 2) }}
                                </td>
                                <td class="py-3 text-end fw-bold text-primary fs-7">
                                    {{ number_format($salary->net_salary, 2) }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($salary->status === 'paid')
                                        <span class="badge bg-success text-success bg-opacity-10 border border-current fs-8 fw-bold">Paid</span>
                                    @elseif($salary->status === 'unpaid')
                                        <span class="badge bg-warning text-warning bg-opacity-10 border border-current fs-8 fw-bold">Unpaid</span>
                                    @elseif($salary->status === 'partially_paid')
                                        <span class="badge bg-info text-info bg-opacity-10 border border-current fs-8 fw-bold">Partially Paid</span>
                                    @else
                                        <span class="badge bg-secondary text-secondary bg-opacity-10 border border-current fs-8 fw-bold">{{ ucfirst($salary->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
