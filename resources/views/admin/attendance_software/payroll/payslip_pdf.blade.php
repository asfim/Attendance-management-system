<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip_{{ $staff->employeeId() }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.4; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { color: #1e40af; margin: 0; }
        .header p { margin: 2px 0 0 0; color: #666; font-size: 11px; }
        .box { background: #f8fafc; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { padding: 8px; text-align: left; }
        th { background: #f1f5f9; font-weight: bold; border-bottom: 1px solid #cbd5e1; }
        .total-box { background: #dcfce7; border: 1px solid #22c55e; padding: 12px; font-size: 16px; font-weight: bold; text-align: right; color: #15803d; border-radius: 6px; }
        .footer { margin-top: 50px; text-align: center; }
        .signature-line { display: inline-block; width: 40%; border-top: 1px solid #333; padding-top: 5px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>ATTENDANCE & HR SOFTWARE</h2>
        <p>{{ $staff->branchName }} | Official Payslip for {{ date('F Y', mktime(0,0,0,$month,1,$year)) }}</p>
    </div>

    <div class="box">
        <table style="margin-bottom:0;">
            <tr>
                <td><strong>Employee ID:</strong> {{ $staff->employeeId() }}</td>
                <td><strong>Employee Name:</strong> {{ $staff->user?->name }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong> {{ $staff->departmentName }}</td>
                <td><strong>Designation:</strong> {{ $staff->designationTitle }}</td>
            </tr>
        </table>
    </div>

    <h4>Attendance Summary</h4>
    <table>
        <thead>
            <tr>
                <th>Present Days</th>
                <th>Late Days</th>
                <th>Absent Days</th>
                <th>Overtime Hours</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $presentDays }} Days</td>
                <td>{{ $lateDays }} Days</td>
                <td>{{ $absentDays }} Days</td>
                <td>{{ $overtimeHours }} Hours</td>
            </tr>
        </tbody>
    </table>

    <h4>Salary & Deduction Breakup</h4>
    <table>
        <thead>
            <tr>
                <th>Earnings</th>
                <th>Amount</th>
                <th>Deductions</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td>BDT {{ number_format($basicSalary, 2) }}</td>
                <td>Late Deduction</td>
                <td>- BDT {{ number_format($lateDeduction, 2) }}</td>
            </tr>
            <tr>
                <td>Allowances</td>
                <td>BDT {{ number_format($allowancesTotal, 2) }}</td>
                <td>Absent Deduction</td>
                <td>- BDT {{ number_format($absentDeduction, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime Pay</td>
                <td>BDT {{ number_format($overtimePay, 2) }}</td>
                <td></td>
                <td></td>
            </tr>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td>Gross Earnings</td>
                <td>BDT {{ number_format($grossSalary + $overtimePay, 2) }}</td>
                <td>Total Deductions</td>
                <td>- BDT {{ number_format($lateDeduction + $absentDeduction, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="total-box">
        NET PAYABLE AMOUNT: BDT {{ number_format($netSalary, 2) }}
    </div>

    <div class="footer">
        <div style="width: 100%;">
            <div class="signature-line" style="float: left;">Employee Signature</div>
            <div class="signature-line" style="float: right;">Authorized Signature</div>
        </div>
    </div>
</body>
</html>
