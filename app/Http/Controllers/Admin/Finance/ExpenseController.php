<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'creator'])->orderBy('expense_date', 'desc');

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }
        
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        $expenses = $query->paginate(20);
        $categories = ExpenseCategory::orderBy('name')->get();

        $totalFiltered = $query->sum('amount');

        return view('admin.finance.expenses.index', compact('expenses', 'categories', 'totalFiltered'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('admin.finance.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:expense_categories,id',
            'expense_date' => 'required|date',
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string',
            'paid_by' => 'nullable|string',
            'reference_no' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ]);

        $data = $request->all();
        $data['created_by'] = Auth::id();

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('finance/expenses', 'public');
        }

        Expense::create($data);

        return redirect()->route('admin.finance.expenses.index')->with('success', 'Expense recorded successfully!');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        if ($expense->attachment && \Storage::disk('public')->exists($expense->attachment)) {
            \Storage::disk('public')->delete($expense->attachment);
        }
        $expense->delete();
        return back()->with('success', 'Expense deleted successfully!');
    }

    // Category Methods
    public function categories()
    {
        $categories = ExpenseCategory::withCount('expenses')->withSum('expenses', 'amount')->get();
        return view('admin.finance.expenses.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'description' => 'nullable|string'
        ]);

        ExpenseCategory::create($request->all());
        return back()->with('success', 'Expense Category created successfully!');
    }

    public function destroyCategory($id)
    {
        $category = ExpenseCategory::findOrFail($id);
        if ($category->expenses()->count() > 0) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }
        $category->delete();
        return back()->with('success', 'Category deleted successfully!');
    }
}
