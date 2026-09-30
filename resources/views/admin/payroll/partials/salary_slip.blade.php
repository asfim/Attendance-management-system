<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Slip — {{ $salary->user->name }} — {{ date('F Y', mktime(0,0,0,$salary->month,1,$salary->year)) }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', sans-serif; color: #1e293b; background: #f8fafc; }

        .slip-wrapper {
            max-width: 800px; margin: 2rem auto;
            background: #fff; border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* Header */
        .slip-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: #fff; padding: 2rem;
            display: flex; align-items: center; gap: 1.5rem;
        }
        .school-logo {
            width: 64px; height: 64px; border-radius: 12px;
            background: rgba(255,255,255,.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem;
        }
        .school-info h2 { font-size: 1.3rem; font-weight: 800; }
        .school-info p  { font-size: .82rem; opacity: .7; margin-top: .2rem; }
        .slip-title {
            margin-left: auto; text-align: right;
        }
        .slip-title h3 {
            font-size: 1rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: .08em;
            background: rgba(99,102,241,.3);
            padding: .5rem 1rem; border-radius: 8px;
            margin-bottom: .3rem;
        }
        .slip-title p { font-size: .8rem; opacity: .7; }

        /* Employee Info */
        .emp-section {
            padding: 1.5rem 2rem;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
        }
        .info-item label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #9ca3af; display: block; margin-bottom: .2rem; }
        .info-item span  { font-size: .88rem; font-weight: 600; color: #1e293b; }

        /* Salary Table */
        .sal-section { padding: 1.5rem 2rem; }
        .sal-section h4 {
            font-size: .78rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: .08em; color: #6366f1;
            margin-bottom: 1rem; padding-bottom: .5rem;
            border-bottom: 2px solid #e2e8f0;
        }
        .sal-table { width: 100%; border-collapse: collapse; }
        .sal-table th {
            font-size: .7rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .05em; color: #9ca3af;
            padding: .5rem; border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }
        .sal-table th:last-child { text-align: right; }
        .sal-table td {
            padding: .55rem .5rem; font-size: .85rem;
            border-bottom: 1px dashed #f1f5f9;
        }
        .sal-table td:last-child { text-align: right; font-weight: 600; }
        .sal-table .positive { color: #22c55e; }
        .sal-table .negative { color: #ef4444; }
        .sal-table .total-row td { font-weight: 800; border-top: 2px solid #e2e8f0; }

        /* Net Salary Banner */
        .net-banner {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff; margin: 0 2rem 1.5rem;
            border-radius: 14px; padding: 1.25rem 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .net-banner .label { font-size: .85rem; opacity: .85; }
        .net-banner .amount { font-size: 1.6rem; font-weight: 900; }

        /* Attendance */
        .att-section {
            padding: 0 2rem 1.5rem;
            display: grid; grid-template-columns: repeat(6,1fr); gap: .75rem;
        }
        .att-chip {
            text-align: center; padding: .75rem .5rem;
            border-radius: 10px; font-size: .75rem;
        }
        .att-chip .num { font-size: 1.3rem; font-weight: 800; display: block; }
        .att-chip.present  { background: #dcfce7; color: #166534; }
        .att-chip.absent   { background: #fee2e2; color: #991b1b; }
        .att-chip.late     { background: #ffedd5; color: #9a3412; }
        .att-chip.leave    { background: #dbeafe; color: #1e40af; }
        .att-chip.half     { background: #f3e8ff; color: #6b21a8; }
        .att-chip.working  { background: #f1f5f9; color: #475569; }

        /* Payment History */
        .pay-hist-section { padding: 0 2rem 1.5rem; }
        .pay-hist-section h4 { font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#6366f1;margin-bottom:.75rem;padding-bottom:.5rem;border-bottom:2px solid #e2e8f0; }
        .pay-hist-item { display:flex;gap:1rem;align-items:center;padding:.45rem 0;border-bottom:1px dashed #f1f5f9;font-size:.82rem; }
        .pay-hist-item .amount { font-weight:700;color:#22c55e; }

        /* Signature */
        .signature-section {
            padding: 2rem; display: flex; justify-content: space-between;
            border-top: 1px solid #e2e8f0;
        }
        .sig-box { text-align: center; width: 160px; }
        .sig-line { border-top: 2px solid #1e293b; margin-bottom: .4rem; }
        .sig-box span { font-size: .75rem; color: #6b7280; font-weight: 600; }

        /* Footer */
        .slip-footer {
            background: #f8fafc; padding: 1rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex; justify-content: space-between;
            align-items: center; font-size: .72rem; color: #9ca3af;
        }

        /* Print button */
        .print-btn {
            position: fixed; bottom: 2rem; right: 2rem;
            background: linear-gradient(135deg,#6366f1,#8b5cf6);
            color: #fff; border: none; padding: .75rem 1.5rem;
            border-radius: 12px; font-size: .9rem; font-weight: 700;
            cursor: pointer; box-shadow: 0 8px 24px rgba(99,102,241,.4);
            transition: all .2s;
        }
        .print-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(99,102,241,.5); }

        @media print {
            body { background: #fff; }
            .slip-wrapper { box-shadow: none; margin: 0; border-radius: 0; max-width: 100%; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

<div class="slip-wrapper">
    {{-- Header --}}
    <div class="slip-header">
        <div class="school-logo">🏫</div>
        <div class="school-info">
            <h2>School Management System</h2>
            <p>Official Payroll Document</p>
        </div>
        <div class="slip-title">
            <h3>Salary Slip</h3>
            <p>{{ date('F Y', mktime(0,0,0,$salary->month,1,$salary->year)) }}</p>
            <p>Ref: SLIP-{{ str_pad($salary->id, 6, '0', STR_PAD_LEFT) }}</p>
        </div>
    </div>

    {{-- Employee Info --}}
    <div class="emp-section">
        <div class="info-item">
            <label>Employee Name</label>
            <span>{{ $salary->user->name }}</span>
        </div>
        @if($salary->user->staffProfile)
        <div class="info-item">
            <label>Employee ID</label>
            <span>{{ $salary->user->staffProfile->employeeId() }}</span>
        </div>
        <div class="info-item">
            <label>Designation</label>
            <span>{{ $salary->user->staffProfile->designation ?? '—' }}</span>
        </div>
        <div class="info-item">
            <label>Department</label>
            <span>{{ $salary->user->staffProfile->department ?? '—' }}</span>
        </div>
        <div class="info-item">
            <label>Joining Date</label>
            <span>{{ $salary->user->staffProfile->joining_date?->format('d M Y') ?? '—' }}</span>
        </div>
        @endif
        <div class="info-item">
            <label>Pay Period</label>
            <span>{{ date('F Y', mktime(0,0,0,$salary->month,1,$salary->year)) }}</span>
        </div>
        <div class="info-item">
            <label>Payment Status</label>
            <span>{{ ucfirst($salary->status) }}</span>
        </div>
        @if($salary->payment_date)
        <div class="info-item">
            <label>Payment Date</label>
            <span>{{ $salary->payment_date->format('d M Y') }}</span>
        </div>
        @endif
    </div>

    {{-- Attendance Summary --}}
    <div class="sal-section">
        <h4>Attendance Summary</h4>
    </div>
    <div class="att-section">
        <div class="att-chip present"><span class="num">{{ $salary->present_days }}</span>Present</div>
        <div class="att-chip absent"><span class="num">{{ $salary->absent_days }}</span>Absent</div>
        <div class="att-chip late"><span class="num">{{ $salary->late_days }}</span>Late</div>
        <div class="att-chip leave"><span class="num">{{ $salary->leave_days }}</span>Leave</div>
        <div class="att-chip half"><span class="num">{{ $salary->half_days }}</span>Half Day</div>
        <div class="att-chip working"><span class="num">{{ $salary->working_days }}</span>Working Days</div>
    </div>

    {{-- Salary Breakdown --}}
    <div class="sal-section">
        <h4>Salary Breakdown</h4>
        <table class="sal-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Type</th>
                    <th>Amount (৳)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $earnings = [
                        'Basic Salary'       => $salary->basic_salary,
                        'House Allowance'    => $salary->house_allowance,
                        'Medical Allowance'  => $salary->medical_allowance,
                        'Transport Allowance'=> $salary->transport_allowance,
                        'Food Allowance'     => $salary->food_allowance,
                        'Mobile Allowance'   => $salary->mobile_allowance,
                        'Internet Allowance' => $salary->internet_allowance,
                        'Special Allowance'  => $salary->special_allowance,
                        'Festival Allowance' => $salary->festival_allowance,
                        'Other Allowances'   => $salary->other_allowances,
                        'Bonus'              => $salary->bonus,
                        'Overtime'           => $salary->overtime,
                    ];
                    $deductions = [
                        'Absent Deduction'   => $salary->absent_deduction,
                        'Late Deduction'     => $salary->late_deduction,
                        'Loan Deduction'     => $salary->loan_deduction,
                        'Advance Deduction'  => $salary->advance_deduction,
                        'Other Deduction'    => $salary->other_deduction,
                        'Tax'                => $salary->tax,
                        'Provident Fund'     => $salary->provident_fund,
                    ];
                @endphp

                @foreach($earnings as $label => $amount)
                @if($amount > 0)
                <tr>
                    <td>{{ $label }}</td>
                    <td>Earning</td>
                    <td class="positive">+ {{ number_format($amount, 2) }}</td>
                </tr>
                @endif
                @endforeach

                @foreach($deductions as $label => $amount)
                @if($amount > 0)
                <tr>
                    <td>{{ $label }}</td>
                    <td>Deduction</td>
                    <td class="negative">− {{ number_format($amount, 2) }}</td>
                </tr>
                @endif
                @endforeach

                <tr class="total-row">
                    <td colspan="2">Gross Salary</td>
                    <td>৳ {{ number_format($salary->grossSalary(), 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2">Total Deductions</td>
                    <td class="negative">− ৳ {{ number_format($salary->totalDeductions(), 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Net Salary --}}
    <div class="net-banner">
        <div>
            <div class="label">Net Salary (Take Home)</div>
            <div style="font-size:.75rem;opacity:.7;">{{ date('F Y', mktime(0,0,0,$salary->month,1,$salary->year)) }}</div>
        </div>
        <div class="amount">৳ {{ number_format($salary->net_salary, 2) }}</div>
    </div>

    {{-- Payment History --}}
    @if($salary->payments && $salary->payments->count())
    <div class="pay-hist-section">
        <h4>Payment History</h4>
        @foreach($salary->payments as $payment)
        <div class="pay-hist-item">
            <div class="amount">৳ {{ number_format($payment->amount, 2) }}</div>
            <div>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</div>
            <div style="color:#9ca3af;">{{ $payment->payment_date->format('d M Y') }}</div>
            @if($payment->reference_number)
            <div style="color:#9ca3af;"># {{ $payment->reference_number }}</div>
            @endif
            <div style="color:#9ca3af;">by {{ $payment->paid_by ?? '—' }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Signature --}}
    <div class="signature-section">
        <div class="sig-box">
            <div class="sig-line"></div>
            <span>Employee Signature</span>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <span>HR Manager</span>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <span>Principal / Director</span>
        </div>
    </div>

    {{-- Footer --}}
    <div class="slip-footer">
        <div>Generated: {{ now()->format('d M Y, h:i A') }}</div>
        <div>Ref: SLIP-{{ str_pad($salary->id, 6, '0', STR_PAD_LEFT) }} • This is a computer-generated document.</div>
    </div>
</div>

<button class="print-btn" onclick="window.print()">
    🖨️ Print / Download PDF
</button>

</body>
</html>
