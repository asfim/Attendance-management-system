<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\StaffProfile;
use App\Models\AdvanceSalary;
use App\Models\PayrollPayment;
use App\Models\PayrollSetting;
use App\Models\User;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    /**
     * Employee payroll dashboard with filters.
     */
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $query = StaffProfile::with(['user.role', 'allowances'])
            ->whereHas('user', fn($q) => $q->where('status', 'active'));

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$s}%"));
        }
        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }
        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        $staffList = $query->orderBy('department')->get();

        // Load salary status for each staff for selected month
        $userIds      = $staffList->pluck('user_id');
        $salaries     = Salary::whereIn('user_id', $userIds)
            ->where('month', $month)->where('year', $year)
            ->get()->keyBy('user_id');

        // Filter by payment status
        if ($request->filled('status')) {
            $status = $request->status;
            $staffList = $staffList->filter(fn($s) => ($salaries[$s->user_id]?->status ?? 'not_generated') === $status);
        }

        $departments  = StaffProfile::whereNotNull('department')->distinct()->pluck('department');
        $designations = StaffProfile::whereNotNull('designation')->distinct()->pluck('designation');
        $settings     = PayrollSetting::current();

        return view('admin.payroll.index', compact(
            'staffList', 'salaries', 'month', 'year', 'departments', 'designations', 'settings'
        ));
    }

    /**
     * AJAX: Get full payroll detail for one employee.
     */
    public function show(Request $request, int $staffId): JsonResponse
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $staff   = StaffProfile::with(['user', 'allowances'])->findOrFail($staffId);
        $salary  = Salary::with(['payments'])
            ->where('user_id', $staff->user_id)
            ->where('month', $month)->where('year', $year)
            ->first();

        $calc    = $this->payrollService->calculateSalary($staff, $month, $year);

        $advances = AdvanceSalary::where('user_id', $staff->user_id)
            ->where('status', 'active')->get();

        return response()->json([
            'staff'    => $staff->load('user'),
            'salary'   => $salary,
            'calc'     => $calc,
            'advances' => $advances,
            'payments' => $salary?->payments ?? [],
        ]);
    }

    /**
     * AJAX: Update salary structure (editable allowances).
     */
    public function updateSalaryStructure(Request $request): JsonResponse
    {
        $request->validate([
            'salary_id'           => 'required|exists:salaries,id',
            'basic_salary'        => 'required|numeric|min:0',
            'house_allowance'     => 'nullable|numeric|min:0',
            'medical_allowance'   => 'nullable|numeric|min:0',
            'transport_allowance' => 'nullable|numeric|min:0',
            'food_allowance'      => 'nullable|numeric|min:0',
            'mobile_allowance'    => 'nullable|numeric|min:0',
            'internet_allowance'  => 'nullable|numeric|min:0',
            'special_allowance'   => 'nullable|numeric|min:0',
            'festival_allowance'  => 'nullable|numeric|min:0',
            'other_allowances'    => 'nullable|numeric|min:0',
            'absent_deduction'    => 'nullable|numeric|min:0',
            'late_deduction'      => 'nullable|numeric|min:0',
            'loan_deduction'      => 'nullable|numeric|min:0',
            'other_deduction'     => 'nullable|numeric|min:0',
            'tax'                 => 'nullable|numeric|min:0',
            'provident_fund'      => 'nullable|numeric|min:0',
            'bonus'               => 'nullable|numeric|min:0',
            'overtime'            => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string',
        ]);

        $salary = Salary::findOrFail($request->salary_id);

        if ($salary->is_locked) {
            return response()->json(['error' => 'This payroll is locked and cannot be edited.'], 422);
        }

        $fields = $request->except(['salary_id', '_token']);
        $gross  = array_sum(array_filter([
            $fields['basic_salary'] ?? 0,
            $fields['house_allowance'] ?? 0,
            $fields['medical_allowance'] ?? 0,
            $fields['transport_allowance'] ?? 0,
            $fields['food_allowance'] ?? 0,
            $fields['mobile_allowance'] ?? 0,
            $fields['internet_allowance'] ?? 0,
            $fields['special_allowance'] ?? 0,
            $fields['festival_allowance'] ?? 0,
            $fields['other_allowances'] ?? 0,
            $fields['bonus'] ?? 0,
            $fields['overtime'] ?? 0,
        ]));

        $deductions = array_sum(array_filter([
            $fields['absent_deduction'] ?? 0,
            $fields['late_deduction'] ?? 0,
            $fields['loan_deduction'] ?? 0,
            $fields['other_deduction'] ?? 0,
            $fields['tax'] ?? 0,
            $fields['provident_fund'] ?? 0,
            $salary->advance_deduction, // keep advance deduction as-is
        ]));

        $fields['net_salary'] = max(0, $gross - $deductions);
        $salary->update($fields);

        return response()->json(['success' => true, 'net_salary' => $salary->net_salary, 'salary' => $salary]);
    }

    /**
     * POST: Take advance salary for an employee.
     */
    public function takeAdvanceSalary(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'          => 'required|exists:users,id',
            'amount'           => 'required|numeric|min:1',
            'reason'           => 'nullable|string|max:500',
            'date'             => 'required|date',
            'recovery_method'  => 'required|in:next_month,multiple_months,installments',
            'installments'     => 'required|integer|min:1|max:24',
            'approved_by'      => 'nullable|string|max:100',
            'remarks'          => 'nullable|string|max:500',
        ]);

        $monthly = $request->amount / max(1, $request->installments);

        $advance = AdvanceSalary::create([
            'user_id'          => $request->user_id,
            'amount'           => $request->amount,
            'reason'           => $request->reason,
            'date'             => $request->date,
            'recovery_method'  => $request->recovery_method,
            'installments'     => $request->installments,
            'monthly_deduction'=> round($monthly, 2),
            'recovered_amount' => 0,
            'approved_by'      => $request->approved_by,
            'remarks'          => $request->remarks,
            'status'           => 'active',
        ]);

        return response()->json(['success' => true, 'advance' => $advance]);
    }

    /**
     * POST: Record a salary payment (supports partial payments).
     */
    public function makePayment(Request $request): JsonResponse
    {
        $request->validate([
            'salary_id'        => 'required|exists:salaries,id',
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:cash,bank_transfer,cheque,mobile_banking',
            'reference_number' => 'nullable|string|max:100',
            'paid_by'          => 'nullable|string|max:100',
            'notes'            => 'nullable|string|max:500',
        ]);

        $salary = Salary::findOrFail($request->salary_id);
        if (round((float) $request->amount, 2) > round($salary->remainingBalance(), 2)) {
            return response()->json(['error' => 'Payment amount exceeds remaining balance.'], 422);
        }

        $payment = $this->payrollService->recordPayment(
            $request->salary_id,
            $request->amount,
            $request->payment_method,
            $request->reference_number ?? '',
            $request->paid_by ?? (auth()->user()?->name ?? 'Admin'),
            $request->notes ?? ''
        );

        $salary->refresh();

        return response()->json([
            'success'   => true,
            'payment'   => $payment,
            'salary'    => $salary,
            'remaining' => $salary->remainingBalance(),
        ]);
    }

    /**
     * GET: Generate print-friendly salary slip (HTML).
     */
    public function generateSlip(int $salaryId)
    {
        $salary = Salary::with(['user.staffProfile.allowances', 'payments'])->findOrFail($salaryId);

        return view('admin.payroll.partials.salary_slip', compact('salary'));
    }

    /**
     * POST: Bulk generate payroll for all employees for a month/year.
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020|max:2030',
        ]);

        $generated = $this->payrollService->generateMonthlyPayroll($request->month, $request->year);

        if ($request->expectsJson()) {
            return response()->json(['success' => $generated]);
        }

        return back()->with($generated ? 'success' : 'error',
            $generated ? 'Payroll generated successfully!' : 'No active employees found.'
        );
    }

    /**
     * POST: Recalculate a salary record from attendance.
     */
    public function recalculate(int $salaryId): JsonResponse
    {
        $salary = Salary::findOrFail($salaryId);

        if ($salary->is_locked) {
            return response()->json(['error' => 'Cannot recalculate a locked payroll.'], 422);
        }

        $staff = StaffProfile::where('user_id', $salary->user_id)->firstOrFail();
        $calc  = $this->payrollService->calculateSalary($staff, $salary->month, $salary->year);

        $salary->update(array_merge($calc, ['status' => $salary->paid_amount > 0 ? 'partial' : 'pending']));

        return response()->json(['success' => true, 'salary' => $salary->fresh()]);
    }

    /**
     * POST: Lock a payroll.
     */
    public function lock(int $salaryId): JsonResponse
    {
        $salary = Salary::findOrFail($salaryId);
        $salary->update(['is_locked' => true]);

        return response()->json(['success' => true]);
    }

    /**
     * POST: Unlock a payroll.
     */
    public function unlock(int $salaryId): JsonResponse
    {
        $salary = Salary::findOrFail($salaryId);
        $salary->update(['is_locked' => false]);

        return response()->json(['success' => true]);
    }

    /**
     * Payroll settings page.
     */
    public function settings()
    {
        $settings = PayrollSetting::current();
        return view('admin.payroll.settings', compact('settings'));
    }

    /**
     * Save payroll settings.
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'working_days'             => 'required|integer|min:1|max:31',
            'office_start_time'        => 'required|date_format:H:i',
            'office_end_time'          => 'required|date_format:H:i',
            'grace_time_minutes'       => 'required|integer|min:0|max:60',
            'late_deduction_per_day'   => 'nullable|numeric|min:0',
            'half_day_deduction_rate'  => 'required|numeric|min:0|max:100',
        ]);

        $schoolId = auth()->user()?->school_id;

        PayrollSetting::updateOrCreate(
            ['school_id' => $schoolId],
            $request->only([
                'working_days', 'office_start_time', 'office_end_time',
                'grace_time_minutes', 'late_deduction_minutes',
                'late_deduction_per_day', 'half_day_deduction_rate',
                'absent_deduction_enabled', 'half_day_deduction_enabled',
            ])
        );

        return back()->with('success', 'Payroll settings saved successfully.');
    }

    /**
     * Legacy generate method (redirects to bulkGenerate).
     */
    public function generate(Request $request)
    {
        return $this->bulkGenerate($request);
    }

    /**
     * Legacy pay method.
     */
    public function pay(int $id)
    {
        $paid = $this->payrollService->paySalary($id);
        return back()->with(
            $paid ? 'success' : 'error',
            $paid ? 'Salary paid successfully!' : 'Failed or already paid.'
        );
    }
}
