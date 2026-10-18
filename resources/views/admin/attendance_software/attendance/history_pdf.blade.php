<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $isIndividual ? 'Employee Attendance Statement' : 'All Employees Attendance Log' }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }
        .system-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 11px;
            color: #475569;
            text-align: right;
            margin: 2px 0 0 0;
        }

        /* Employee Info Box */
        .info-card {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 12px;
            padding: 8px 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 6px;
            font-size: 10.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: bold;
            width: 15%;
        }
        .info-val {
            color: #0f172a;
            font-weight: 600;
            width: 35%;
        }

        /* KPI Summary Grid */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .kpi-table td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            text-align: center;
            background-color: #ffffff;
        }
        .kpi-num {
            font-size: 13px;
            font-weight: bold;
            display: block;
        }
        .kpi-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        /* Attendance Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .data-table td {
            padding: 5px 5px;
            font-size: 9.5px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-left {
            text-align: left !important;
        }
        .text-right {
            text-align: right !important;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-present { background-color: #dcfce7; color: #15803d; }
        .badge-late { background-color: #fef3c7; color: #b45309; }
        .badge-absent { background-color: #fee2e2; color: #b91c1c; }
        .badge-early { background-color: #e0f2fe; color: #0369a1; }
        .badge-leave { background-color: #f3e8ff; color: #7e22ce; }
        .badge-holiday { background-color: #f1f5f9; color: #475569; }

        /* Signatures Section */
        .signatures-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding-top: 30px;
        }
        .sig-line {
            border-top: 1px solid #94a3b8;
            margin: 0 20px;
            padding-top: 4px;
            font-size: 10px;
            font-weight: 600;
            color: #334155;
        }

        .footer-note {
            font-size: 8.5px;
            color: #94a3b8;
            text-align: right;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <!-- Header Table -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                @php
                    $logoBase64 = null;
                    if (!empty($companyLogo)) {
                        $candidate = public_path(ltrim($companyLogo, '/'));
                        if (!file_exists($candidate)) {
                            $candidate = storage_path('app/public/' . preg_replace('/^\/?(storage\/)?/', '', $companyLogo));
                        }
                        if (file_exists($candidate) && is_file($candidate)) {
                            $mime = @mime_content_type($candidate) ?: 'image/png';
                            $content = @file_get_contents($candidate);
                            if ($content !== false) {
                                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($content);
                            }
                        }
                    }
                @endphp
                @if($logoBase64)
                    <div style="margin-bottom: 6px;">
                        <img src="{{ $logoBase64 }}" style="max-height: 48px; max-width: 170px;" alt="{{ $companyName }}">
                    </div>
                @endif
                <h1 class="company-name">{{ $companyName }}</h1>
                <p class="system-subtitle">Office Attendance & Workforce Management System</p>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">
                    @if(!empty($companyAddress))
                        <span>{{ $companyAddress }}</span>
                    @endif
                    @if(!empty($companyRegNo))
                        <span style="margin-left: 10px;">Reg No: {{ $companyRegNo }}</span>
                    @endif
                </div>
            </td>
            <td style="width: 40%; vertical-align: top; text-align: right;">
                <div class="report-title">{{ $isIndividual ? 'Employee Attendance Statement' : 'All Employees Attendance Log' }}</div>
                <div class="report-period">Period: <strong>{{ $monthName }} {{ $year }}</strong></div>
                @if($isIndividual && $selectedStaff)
                    <div style="font-size: 8.5px; color: #64748b; margin-top: 4px;">Branch: <strong>{{ $selectedStaff->branchName }}</strong></div>
                @endif
            </td>
        </tr>
    </table>

    @if($isIndividual && $selectedStaff)
        @php
            $staffPhotoBase64 = null;
            if (!empty($selectedStaff->photo)) {
                $pCandidate = public_path('storage/' . ltrim($selectedStaff->photo, '/'));
                if (!file_exists($pCandidate)) {
                    $pCandidate = storage_path('app/public/' . ltrim($selectedStaff->photo, '/'));
                }
                if (file_exists($pCandidate) && is_file($pCandidate)) {
                    $pMime = @mime_content_type($pCandidate) ?: 'image/jpeg';
                    $pContent = @file_get_contents($pCandidate);
                    if ($pContent !== false) {
                        $staffPhotoBase64 = 'data:' . $pMime . ';base64,' . base64_encode($pContent);
                    }
                }
            }
        @endphp
        <!-- Individual Employee Profile Info Card -->
        <div class="info-card">
            <table class="info-table">
                <tr>
                    @if($staffPhotoBase64)
                        <td rowspan="3" style="width: 70px; text-align: center; vertical-align: middle; padding-right: 12px;">
                            <img src="{{ $staffPhotoBase64 }}" style="width: 60px; height: 60px; border-radius: 4px; border: 1px solid #cbd5e1; object-fit: cover;" alt="Photo">
                        </td>
                    @endif
                    <td class="info-label">Employee Name:</td>
                    <td class="info-val">{{ $selectedStaff->user?->name ?? 'N/A' }}</td>
                    <td class="info-label">Employee ID:</td>
                    <td class="info-val">{{ $selectedStaff->employeeId() }}</td>
                </tr>
                <tr>
                    <td class="info-label">Biometric ID:</td>
                    <td class="info-val">{{ $selectedStaff->biometric_id ?? 'Not Assigned' }}</td>
                    <td class="info-label">Designation:</td>
                    <td class="info-val">{{ $selectedStaff->designationTitle }}</td>
                </tr>
                <tr>
                    <td class="info-label">Department:</td>
                    <td class="info-val">{{ $selectedStaff->departmentName }}</td>
                    <td class="info-label">Branch:</td>
                    <td class="info-val">{{ $selectedStaff->branchName }}</td>
                </tr>
            </table>
        </div>

        @if($staffMonthlySummary)
            <!-- KPI Summary Table -->
            <table class="kpi-table">
                <tr>
                    <td>
                        <span class="kpi-num" style="color: #2563eb;">{{ $staffMonthlySummary['total_days'] }}</span>
                        <span class="kpi-label">Recorded Days</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #16a34a;">{{ $staffMonthlySummary['present'] }}</span>
                        <span class="kpi-label">Present</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #d97706;">{{ $staffMonthlySummary['late'] }}</span>
                        <span class="kpi-label">Late Coming</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #0284c7;">{{ $staffMonthlySummary['early_leave'] }}</span>
                        <span class="kpi-label">Early Leave</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #dc2626;">{{ $staffMonthlySummary['absent'] }}</span>
                        <span class="kpi-label">Absent</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #9333ea;">{{ $staffMonthlySummary['leave'] }}</span>
                        <span class="kpi-label">On Leave</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #059669;">{{ round($staffMonthlySummary['total_overtime_min'] / 60, 1) }}h</span>
                        <span class="kpi-label">Overtime</span>
                    </td>
                    <td>
                        <span class="kpi-num" style="color: #0f172a;">{{ $staffMonthlySummary['total_working_hrs'] }}h</span>
                        <span class="kpi-label">Work Hours</span>
                    </td>
                </tr>
            </table>
        @endif

        <!-- Individual Daily Attendance Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Date</th>
                    <th style="width: 10%;">Day</th>
                    <th style="width: 16%;">Shift</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 10%;">In Time</th>
                    <th style="width: 10%;">Out Time</th>
                    <th style="width: 8%;">Late</th>
                    <th style="width: 8%;">Early</th>
                    <th style="width: 8%;">Overtime</th>
                    <th style="width: 8%;">Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $row)
                    <tr>
                        <td class="text-left fw-bold">{{ $row->attendance_date?->format('d M, Y') }}</td>
                        <td>{{ $row->attendance_date?->format('D') }}</td>
                        <td class="text-left">{{ $row->shift?->name ?? 'General' }}</td>
                        <td>
                            @php
                                $badgeCls = match($row->status) {
                                    'present', 'work_from_home' => 'badge-present',
                                    'late' => 'badge-late',
                                    'absent' => 'badge-absent',
                                    'early_leave' => 'badge-early',
                                    'leave' => 'badge-leave',
                                    default => 'badge-holiday',
                                };
                            @endphp
                            <span class="badge {{ $badgeCls }}">{{ strtoupper($row->status) }}</span>
                        </td>
                        <td style="color: #15803d; font-weight: bold;">{{ $row->entry_time ? date('h:i A', strtotime($row->entry_time)) : '--' }}</td>
                        <td style="color: #b91c1c; font-weight: bold;">{{ $row->exit_time ? date('h:i A', strtotime($row->exit_time)) : '--' }}</td>
                        <td>{{ $row->late_minutes > 0 ? $row->late_minutes . 'm' : '-' }}</td>
                        <td>{{ $row->early_leave_minutes > 0 ? $row->early_leave_minutes . 'm' : '-' }}</td>
                        <td>{{ $row->overtime_minutes > 0 ? round($row->overtime_minutes / 60, 1) . 'h' : '-' }}</td>
                        <td style="font-weight: bold;">{{ $row->working_hours > 0 ? $row->working_hours . 'h' : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; color: #94a3b8; padding: 25px;">No attendance records found for this employee in {{ $monthName }} {{ $year }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    @else
        <!-- All Employees Table -->
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 11%;">Date</th>
                    <th style="width: 8%;">Emp ID</th>
                    <th style="width: 17%;">Employee Name</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 11%;">Status</th>
                    <th style="width: 10%;">In Time</th>
                    <th style="width: 10%;">Out Time</th>
                    <th style="width: 6%;">Late</th>
                    <th style="width: 6%;">Early</th>
                    <th style="width: 6%;">OT</th>
                    <th style="width: 6%;">Hours</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $row)
                    @php
                        $staff = $row->attendable;
                        $badgeCls = match($row->status) {
                            'present', 'work_from_home' => 'badge-present',
                            'late' => 'badge-late',
                            'absent' => 'badge-absent',
                            'early_leave' => 'badge-early',
                            'leave' => 'badge-leave',
                            default => 'badge-holiday',
                        };
                    @endphp
                    <tr>
                        <td>{{ $row->attendance_date?->format('d M, Y (D)') }}</td>
                        <td><strong>{{ $staff?->employeeId() }}</strong></td>
                        <td class="text-left" style="font-weight: bold;">{{ $staff?->user?->name ?? 'N/A' }}</td>
                        <td class="text-left">{{ $staff?->departmentName }}</td>
                        <td><span class="badge {{ $badgeCls }}">{{ strtoupper($row->status) }}</span></td>
                        <td style="color: #15803d; font-weight: bold;">{{ $row->entry_time ? date('h:i A', strtotime($row->entry_time)) : '--' }}</td>
                        <td style="color: #b91c1c; font-weight: bold;">{{ $row->exit_time ? date('h:i A', strtotime($row->exit_time)) : '--' }}</td>
                        <td>{{ $row->late_minutes > 0 ? $row->late_minutes . 'm' : '-' }}</td>
                        <td>{{ $row->early_leave_minutes > 0 ? $row->early_leave_minutes . 'm' : '-' }}</td>
                        <td>{{ $row->overtime_minutes > 0 ? round($row->overtime_minutes / 60, 1) . 'h' : '-' }}</td>
                        <td style="font-weight: bold;">{{ $row->working_hours > 0 ? $row->working_hours . 'h' : '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; color: #94a3b8; padding: 25px;">No attendance logs found for {{ $monthName }} {{ $year }}.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Signatures Section -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sig-line">Prepared By</div>
            </td>
            <td>
                <div class="sig-line">Verified By (HR Dept)</div>
            </td>
            <td>
                <div class="sig-line">Authorized Signatory</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Generated automatically by {{ $companyName }} Attendance Suite on {{ now()->format('d M, Y h:i A') }}
    </div>

</body>
</html>
