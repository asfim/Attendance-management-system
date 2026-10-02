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
            <table class="table table-sm table-hover align-middle mb-0 text-nowrap">
                <thead class="table-light">
                    <tr>
                        <th>Staff Name</th>
                        <th>Earnings (Basic + Allowances)</th>
                        <th>Attendance (P/L/A/Lve)</th>
                        <th>Overtime (Hrs/Pay)</th>
                        <th>Deductions (Late/Absent/Adv)</th>
                        <th>Net Payable</th>
                        <th class="text-end">Payslip Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrollSheets as $item)
                        @php $staff = $item['staff']; @endphp
                        <tr>
                            <td class="py-2">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $staff->photoUrl() }}" class="rounded-circle" width="28" height="28" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark mb-0 lh-1">{{ $staff->user?->name }}</div>
                                        <span class="text-muted" style="font-size: 0.75rem;">{{ $staff->employeeId() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2">
                                <div class="fw-bold text-dark small">Basic: ৳ {{ number_format($item['basic_salary'], 2) }}</div>
                                @if($item['gross_salary'] > $item['basic_salary'])
                                    <div class="text-muted small" style="font-size: 0.75rem;">Allow: +৳ {{ number_format($item['gross_salary'] - $item['basic_salary'], 2) }}</div>
                                @endif
                                <div class="text-primary small fw-bold mt-1">Gross: ৳ {{ number_format($item['gross_salary'], 2) }}</div>
                            </td>
                            <td class="py-2">
                                <div class="small">
                                    <span class="badge bg-success me-1">P: {{ $item['present_days'] }}</span>
                                    <span class="badge bg-warning text-dark me-1">L: {{ $item['late_days'] }}</span>
                                    <span class="badge bg-danger me-1">A: {{ $item['absent_days'] }}</span>
                                    <span class="badge bg-primary">Lve: {{ $item['leave_days'] }}</span>
                                </div>
                            </td>
                            <td class="py-2">
                                <div class="d-flex align-items-center">
                                    <div>
                                        @if($item['overtime_hours'] > 0 || $item['overtime_pay'] > 0)
                                            <div class="text-success fw-bold small">+ ৳ {{ number_format($item['overtime_pay'], 2) }}</div>
                                            @if($item['overtime_hours'] > 0)
                                                <span class="text-muted" style="font-size: 0.75rem;">({{ $item['overtime_hours'] }} hrs)</span>
                                            @endif
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </div>
                                    @if($item['status'] !== 'paid')
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-2 text-primary" data-bs-toggle="modal" data-bs-target="#editOvertimeModal{{ $staff->id }}" title="Edit Overtime">
                                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.85rem;"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2">
                                @php
                                    $advCut = $item['advance_deduction'] ?? 0;
                                    $totDed = $item['late_deduction'] + $item['absent_deduction'] + $advCut;
                                @endphp
                                @if($totDed > 0)
                                    @if($item['late_deduction'] > 0)
                                        <div class="text-danger small" style="font-size: 0.75rem;">Late: -৳ {{ number_format($item['late_deduction'], 2) }}</div>
                                    @endif
                                    @if($item['absent_deduction'] > 0)
                                        <div class="text-danger small" style="font-size: 0.75rem;">Absent: -৳ {{ number_format($item['absent_deduction'], 2) }}</div>
                                    @endif
                                    @if($advCut > 0)
                                        <div class="text-danger small" style="font-size: 0.75rem;">Advance: -৳ {{ number_format($advCut, 2) }}</div>
                                    @endif
                                    <div class="text-danger fw-bold mt-1" style="font-size: 0.8rem; border-top: 1px solid #f8d7da; padding-top: 2px;">Total: -৳ {{ number_format($totDed, 2) }}</div>
                                @else
                                    <span class="text-muted">--</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill fw-bold">৳ {{ number_format($item['net_salary'], 2) }}</span>
                                @if($item['advance_balance'] > 0)
                                    <div class="text-warning small mt-1 fw-bold">Adv: ৳ {{ number_format($item['advance_balance'], 2) }}</div>
                                @endif
                            </td>
                            <td class="text-end py-2">
                                @if($item['status'] !== 'paid')
                                    <button type="button" class="btn btn-sm btn-success rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#payModal{{ $staff->id }}">
                                        <i class="fa-solid fa-money-bill-wave me-1"></i> Pay
                                    </button>

                                <!-- Pay Modal Trigger -->
                                @else
                                    <span class="badge bg-success px-3 py-2 rounded-pill me-1"><i class="fa-solid fa-check-circle me-1"></i> Paid Fully</span>
                                @endif
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

<!-- Modals rendered outside of table -->
@foreach($payrollSheets as $item)
    @php $staff = $item['staff']; @endphp
    @if($item['status'] !== 'paid')
        <!-- Edit Overtime Modal -->
        <div class="modal fade" id="editOvertimeModal{{ $staff->id }}" tabindex="-1" aria-labelledby="editOvertimeModalLabel{{ $staff->id }}" aria-hidden="true">
            <div class="modal-dialog modal-sm">
            <div class="modal-content text-start">
                <div class="modal-header">
                <h5 class="modal-title" id="editOvertimeModalLabel{{ $staff->id }}">Edit Overtime</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.attendance-suite.payroll.overtime', [$staff->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Current Amount: ৳ {{ number_format($item['overtime_pay'], 2) }}</label>
                        <br>
                        <label class="form-label fw-bold">New Overtime Pay (৳)</label>
                        <input type="number" name="overtime_pay" class="form-control" value="{{ $item['overtime_pay'] }}" min="0" step="0.01" required>
                        <small class="text-muted d-block mt-1">This will override the automatically calculated overtime.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Overtime</button>
                </div>
                </form>
            </div>
            </div>
        </div>

        <!-- Pay Modal -->
        <div class="modal fade" id="payModal{{ $staff->id }}" tabindex="-1" aria-labelledby="payModalLabel{{ $staff->id }}" aria-hidden="true">
            <div class="modal-dialog">
            <div class="modal-content text-start">
                <div class="modal-header">
                <h5 class="modal-title" id="payModalLabel{{ $staff->id }}">Pay Salary: {{ $staff->user?->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.attendance-suite.payroll.pay', [$staff->id]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">Total Net Salary</label>
                            <div class="fw-bold fs-5">৳ {{ number_format($item['net_salary'], 2) }}</div>
                        </div>
                        <div class="col-6">
                            <label class="form-label text-muted small mb-0">Active Advance Taken</label>
                            <div class="fw-bold fs-5 text-warning">৳ {{ number_format($item['advance_balance'], 2) }}</div>
                        </div>
                    </div>
                    
                    @php $remaining = max(0, $item['net_salary'] - ($item['paid_amount'] ?? 0)); @endphp
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Already Paid</label>
                        <div class="text-success fw-bold">৳ {{ number_format($item['paid_amount'] ?? 0, 2) }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-0">Remaining Balance</label>
                        <div class="text-danger fw-bold fs-5">৳ {{ number_format($remaining, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="payment_amount_{{ $staff->id }}" class="form-label">Payment Amount (৳)</label>
                        <input type="number" step="0.01" class="form-control form-control-lg" id="payment_amount_{{ $staff->id }}" name="payment_amount" value="{{ $remaining }}" required>
                        <div class="form-text text-warning"><i class="fa-solid fa-circle-info"></i> You can enter less to make a partial payment. Any amount greater than the remaining balance will be recorded as an Advance Salary for this employee.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method_{{ $staff->id }}" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method_{{ $staff->id }}" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Banking (bKash/Nagad/Rocket)</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes_{{ $staff->id }}" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes_{{ $staff->id }}" name="notes" rows="2" placeholder="e.g. Transaction ID, Check number..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check me-1"></i> Confirm Payment</button>
                </div>
                </form>
            </div>
            </div>
        </div>
    @endif
@endforeach

@endsection
