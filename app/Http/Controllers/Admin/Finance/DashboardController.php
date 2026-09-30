<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Donation;
use App\Models\InventoryPurchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $startOfYear = Carbon::now()->startOfYear();

        // 1. Total Expenses
        $totalExpenses = Expense::sum('amount');
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        $monthExpenses = Expense::whereDate('expense_date', '>=', $startOfMonth)->sum('amount');
        $yearExpenses = Expense::whereDate('expense_date', '>=', $startOfYear)->sum('amount');

        // 2. Purchases
        $totalPurchases = InventoryPurchase::sum('grand_total');
        $pendingPayments = InventoryPurchase::where('payment_status', 'unpaid')->sum('grand_total');

        // 3. Donations
        $totalDonations = Donation::sum('amount');

        // Available Balance = Total Donations - (Total Expenses + Total Purchases)
        $availableBalance = $totalDonations - ($totalExpenses + $totalPurchases);

        // Category-wise Expenses for Chart
        $categories = ExpenseCategory::withSum('expenses', 'amount')->get();
        $categoryLabels = $categories->pluck('name');
        $categoryData = $categories->pluck('expenses_sum_amount');

        // Monthly Expenses for Chart
        $monthlyExpenses = Expense::select(
            DB::raw('sum(amount) as sums'),
            DB::raw("DATE_FORMAT(expense_date,'%Y-%m') as months")
        )
        ->whereDate('expense_date', '>=', Carbon::now()->subMonths(6))
        ->groupBy('months')
        ->orderBy('months')
        ->get();

        $monthlyLabels = $monthlyExpenses->pluck('months');
        $monthlyData = $monthlyExpenses->pluck('sums');

        return view('admin.finance.dashboard', compact(
            'totalExpenses', 'todayExpenses', 'monthExpenses', 'yearExpenses',
            'totalPurchases', 'pendingPayments', 'totalDonations', 'availableBalance',
            'categoryLabels', 'categoryData', 'monthlyLabels', 'monthlyData'
        ));
    }
}
