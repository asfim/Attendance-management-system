<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffProfile;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Branch;
use App\Models\LeaveApplication;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $reportType   = $request->input('type', 'daily');
        $date         = $request->input('date', now()->toDateString());
        $month        = (int) $request->input('month', now()->month);
        $year         = (int) $request->input('year', now()->year);
        $branchId     = $request->input('branch_id');
        $departmentId = $request->input('department_id');
        $staffId      = $request->input('staff_id');

        $query = Attendance::with(['attendable.user', 'attendable.departmentRel', 'branch'])
            ->where('attendable_type', StaffProfile::class);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }
        if ($staffId) {
            $query->where('attendable_id', $staffId);
        }

        switch ($reportType) {
            case 'daily':
                $query->whereDate('attendance_date', $date);
                break;
            case 'monthly':
                $query->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'late':
                $query->where('status', 'late')->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'absent':
                $query->where('status', 'absent')->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'overtime':
                $query->where('overtime_minutes', '>', 0)->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'early_leave':
                $query->where('early_leave_minutes', '>', 0)->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'missing_punch':
                $query->where('is_missing_punch', true)->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            case 'leave':
                $query->where('status', 'leave')->whereMonth('attendance_date', $month)->whereYear('attendance_date', $year);
                break;
            default:
                $query->whereDate('attendance_date', $date);
                break;
        }

        $records = $query->orderBy('attendance_date', 'desc')->get();

        if ($request->input('export') === 'csv') {
            return $this->exportCsv($records, $reportType);
        }

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('admin.attendance_software.reports.pdf', compact('records', 'reportType', 'date', 'month', 'year'));
            return $pdf->download("Attendance_Report_{$reportType}_{$date}.pdf");
        }

        $branches = Branch::where('status', 'active')->get();
        $departments = Department::where('status', 'active')->get();
        $staffMembers = StaffProfile::with('user')->where('status', 'active')->get();

        return view('admin.attendance_software.reports.index', compact(
            'records',
            'reportType',
            'date',
            'month',
            'year',
            'branches',
            'departments',
            'staffMembers',
            'branchId',
            'departmentId',
            'staffId'
        ));
    }

    private function exportCsv($records, $reportType)
    {
        $fileName = "attendance_report_{$reportType}_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Employee ID', 'Employee Name', 'Branch', 'Department', 'Status', 'Check In', 'Check Out', 'Late (Mins)', 'Early Leave (Mins)', 'Overtime (Mins)', 'Working Hours'];

        $callback = function() use ($records, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($records as $row) {
                $staff = $row->attendable;
                fputcsv($file, [
                    $row->attendance_date?->format('Y-m-d'),
                    $staff?->employeeId(),
                    $staff?->user?->name,
                    $staff?->branchName,
                    $staff?->departmentName,
                    strtoupper($row->status),
                    $row->entry_time ?? '--',
                    $row->exit_time ?? '--',
                    $row->late_minutes,
                    $row->early_leave_minutes,
                    $row->overtime_minutes,
                    $row->working_hours,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
