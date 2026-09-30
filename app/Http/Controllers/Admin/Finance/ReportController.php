<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Donation;

class ReportController extends Controller
{
    public function expenses(Request $request)
    {
        $query = Expense::with(['category', 'creator'])->orderBy('expense_date', 'desc');

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        $expenses = $query->get();
        $totalExpense = $expenses->sum('amount');
        
        $categories = \App\Models\ExpenseCategory::all();

        return view('admin.finance.reports.expenses', compact('expenses', 'totalExpense', 'categories'));
    }

    public function donations(Request $request)
    {
        $query = Donation::with('campaign')->orderBy('donation_date', 'desc');

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('donation_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('donation_date', '<=', $request->end_date);
        }

        if ($request->has('campaign_id') && $request->campaign_id != '') {
            $query->where('campaign_id', $request->campaign_id);
        }

        $donations = $query->get();
        $totalDonation = $donations->sum('amount');

        $campaigns = \App\Models\DonationCampaign::all();

        return view('admin.finance.reports.donations', compact('donations', 'totalDonation', 'campaigns'));
    }
}
