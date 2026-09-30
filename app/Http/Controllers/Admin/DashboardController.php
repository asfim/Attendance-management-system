<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\StaffProfile;
use App\Models\ParentProfile;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'total_students' => StudentProfile::where('status', 'active')->count(),
            'total_teachers' => StaffProfile::whereHas('user.role', function($q){ $q->where('name', 'teacher'); })->where('status', 'active')->count(),
            'total_staff' => StaffProfile::where('status', 'active')->count(),
            'total_parents' => ParentProfile::count(),
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
            'total_shifts' => \App\Models\Shift::count(),
            
            // Financial KPI
            'total_fee_collected' => Invoice::sum('paid_amount'),
            'total_invoiced' => Invoice::sum('grand_total'),
            
            // Attendance chart percentage
            'today_student_attendance' => $this->getTodayAttendancePercentage(),
            
            // Chart Data
            'currentMonth' => now()->format('F Y'),
            'currentSession' => $this->getCurrentSessionName(),
            'monthlyChartData' => $this->getMonthlyChartData(),
            'yearlyChartData' => $this->getYearlyChartData(),
            'incomeByCategory' => $this->getIncomeByCategory(),
            'expenseByCategory' => $this->getExpenseByCategory(),
            
            // Bottom Panels Data
            'feesOverview' => $this->getFeesOverview(),
            'attendanceOverview' => $this->getAttendanceOverview(),
            'roleCounts' => $this->getRoleCounts(),
            'monthlyFeesCollection' => Transaction::where('type', 'credit')->whereMonth('date', now()->format('m'))->whereYear('date', now()->format('Y'))->sum('amount'),
            'monthlyExpenses' => Transaction::where('type', 'debit')->whereMonth('date', now()->format('m'))->whereYear('date', now()->format('Y'))->sum('amount'),
        ];

        return view('admin.dashboard', $data);
    }
    
    private function getCurrentSessionName(): string
    {
        $year = date('Y');
        $month = date('m');
        if ($month >= 7) {
            return $year . '-' . substr($year + 1, 2);
        }
        return ($year - 1) . '-' . substr($year, 2);
    }
    
    private function getMonthlyChartData()
    {
        $daysInMonth = now()->daysInMonth;
        $month = now()->format('m');
        $year = now()->format('Y');
        
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $labels[] = sprintf('%02d', $i);
            $incomeData[$i] = 0;
            $expenseData[$i] = 0;
        }
        
        $transactions = Transaction::select(
            DB::raw('DAY(date) as day'),
            'type',
            DB::raw('SUM(amount) as total')
        )
        ->whereMonth('date', $month)
        ->whereYear('date', $year)
        ->groupBy('day', 'type')
        ->get();
        
        foreach ($transactions as $t) {
            if ($t->type == 'credit') {
                $incomeData[$t->day] = (float) $t->total;
            } else {
                $expenseData[$t->day] = (float) $t->total;
            }
        }
        
        return [
            'labels' => $labels,
            'income' => array_values($incomeData),
            'expense' => array_values($expenseData),
        ];
    }
    
    private function getYearlyChartData()
    {
        $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $year = now()->format('Y');
        
        $incomeData = array_fill(1, 12, 0);
        $expenseData = array_fill(1, 12, 0);
        
        $transactions = Transaction::select(
            DB::raw('MONTH(date) as month'),
            'type',
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('date', $year)
        ->groupBy('month', 'type')
        ->get();
        
        foreach ($transactions as $t) {
            if ($t->type == 'credit') {
                $incomeData[$t->month] = (float) $t->total;
            } else {
                $expenseData[$t->month] = (float) $t->total;
            }
        }
        
        return [
            'labels' => $labels,
            'income' => array_values($incomeData),
            'expense' => array_values($expenseData),
        ];
    }
    
    private function getIncomeByCategory()
    {
        $month = now()->format('m');
        $year = now()->format('Y');
        
        $data = Transaction::join('ledgers', 'transactions.ledger_id', '=', 'ledgers.id')
            ->where('transactions.type', 'credit')
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->select('ledgers.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('ledgers.name')
            ->pluck('total', 'name')
            ->toArray();
            
        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }
    
    private function getExpenseByCategory()
    {
        $month = now()->format('m');
        $year = now()->format('Y');
        
        $data = Transaction::join('ledgers', 'transactions.ledger_id', '=', 'ledgers.id')
            ->where('transactions.type', 'debit')
            ->whereMonth('transactions.date', $month)
            ->whereYear('transactions.date', $year)
            ->select('ledgers.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('ledgers.name')
            ->pluck('total', 'name')
            ->toArray();
            
        return [
            'labels' => array_keys($data),
            'data' => array_values($data),
        ];
    }

    private function getTodayAttendancePercentage(): float
    {
        $today = now()->format('Y-m-d');
        $totalStudents = StudentProfile::where('status', 'active')->count();

        if ($totalStudents === 0) {
            return 0.0;
        }

        $presentCount = Attendance::where('attendance_date', $today)
            ->where('attendable_type', StudentProfile::class)
            ->whereIn('status', ['present', 'late'])
            ->count();

        return round(($presentCount / $totalStudents) * 100, 2);
    }
    
    private function getFeesOverview()
    {
        $total = Invoice::count();
        $unpaid = Invoice::where('status', 'unpaid')->count();
        $partial = Invoice::where('status', 'partial')->count();
        $paid = Invoice::where('status', 'paid')->count();
        
        return [
            'total' => $total,
            'unpaid' => $unpaid,
            'partial' => $partial,
            'paid' => $paid,
        ];
    }
    
    private function getAttendanceOverview()
    {
        $today = now()->format('Y-m-d');
        $total = StudentProfile::where('status', 'active')->count();
        
        $present = Attendance::where('attendance_date', $today)->where('attendable_type', StudentProfile::class)->where('status', 'present')->count();
        $late = Attendance::where('attendance_date', $today)->where('attendable_type', StudentProfile::class)->where('status', 'late')->count();
        $absent = Attendance::where('attendance_date', $today)->where('attendable_type', StudentProfile::class)->where('status', 'absent')->count();
        $half_day = Attendance::where('attendance_date', $today)->where('attendable_type', StudentProfile::class)->where('status', 'half_day')->count();
        
        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'half_day' => $half_day,
        ];
    }
    
    private function getRoleCounts()
    {
        return Role::withCount('users')->get()->pluck('users_count', 'name')->toArray();
    }
}
