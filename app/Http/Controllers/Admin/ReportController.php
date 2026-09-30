<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Donation;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentProfile;
use App\Models\Attendance;
use App\Models\InventoryPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function financeReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Income (Revenues - Credit to Revenue Ledgers)
        $incomeRecords = \App\Models\Transaction::with('ledger')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('ledger', function ($query) {
                $query->where('type', 'revenue');
            })
            ->where('type', 'credit')
            ->get();
            
        $income = $incomeRecords->sum('amount');

        // Expenses (Debits to Expense Ledgers)
        $expenseRecords = \App\Models\Transaction::with('ledger')
            ->whereBetween('date', [$startDate, $endDate])
            ->whereHas('ledger', function ($query) {
                $query->where('type', 'expense');
            })
            ->where('type', 'debit')
            ->get();
            
        $expenses = $expenseRecords->sum('amount');
        
        $purchaseRecords = collect(); // Not using this anymore as purchases should be routed through accounts

        return view('admin.reports.finance', compact('startDate', 'endDate', 'income', 'expenses', 'incomeRecords', 'expenseRecords', 'purchaseRecords'));
    }

    public function attendanceReport(Request $request)
    {
        $classes = SchoolClass::with('sections')->get();
        $classId = $request->input('class_id');
        $sectionId = $request->input('section_id');

        // Month selection for matrix
        $monthStr = $request->input('month', Carbon::now()->format('Y-m'));
        $month = Carbon::parse($monthStr . '-01');
        $daysInMonth = $month->daysInMonth;

        $students = collect();
        $attendances = [];

        $classTotalPresent = 0;
        $classTotalAbsent = 0;
        $classTotalLate = 0;

        if ($classId && $sectionId) {
            $students = StudentProfile::where('class_id', $classId)
                ->where('section_id', $sectionId)
                ->with('user')
                ->get();

            $studentIds = $students->pluck('id')->toArray();

            // Get all attendances for these students for the selected month
            $attendanceRecords = Attendance::where('attendable_type', StudentProfile::class)
                ->whereIn('attendable_id', $studentIds)
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->get();

            // Format into a matrix [student_id][day] = status
            foreach ($attendanceRecords as $record) {
                $day = Carbon::parse($record->attendance_date)->day;
                $attendances[$record->attendable_id][$day] = $record->status;
                
                if ($record->status == 'present') {
                    $classTotalPresent++;
                } elseif ($record->status == 'absent') {
                    $classTotalAbsent++;
                } elseif ($record->status == 'late') {
                    $classTotalLate++;
                }
            }
        }

        return view('admin.reports.attendance', compact('classes', 'classId', 'sectionId', 'month', 'daysInMonth', 'students', 'attendances', 'monthStr', 'classTotalPresent', 'classTotalAbsent', 'classTotalLate'));
    }
}
