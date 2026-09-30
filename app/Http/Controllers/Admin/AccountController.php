<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AccountingService;
use App\Models\Ledger;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $ledgers = Ledger::all();
        $transactions = Transaction::with('ledger')->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(20);
        $cashBook = $this->accountingService->getCashBook();
        $pl = $this->accountingService->getProfitAndLoss();
        $bs = $this->accountingService->getBalanceSheet();

        return view('admin.accounts.index', compact('ledgers', 'transactions', 'cashBook', 'pl', 'bs'));
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'debit_ledger_id' => 'required|exists:ledgers,id|different:credit_ledger_id',
            'credit_ledger_id' => 'required|exists:ledgers,id',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
        ]);

        $reference_no = 'JNL-' . strtoupper(substr(uniqid(), -6));

        // Debit Entry
        if ($request->debit_ledger_id) {
            Transaction::create([
                'ledger_id' => $request->debit_ledger_id,
                'date' => $request->date,
                'type' => 'debit',
                'amount' => $request->amount,
                'description' => $request->description,
                'reference_no' => $reference_no,
            ]);
        }

        // Credit Entry
        if ($request->credit_ledger_id) {
            Transaction::create([
                'ledger_id' => $request->credit_ledger_id,
                'date' => $request->date,
                'type' => 'credit',
                'amount' => $request->amount,
                'description' => $request->description,
                'reference_no' => $reference_no,
            ]);
        }

        return back()->with('success', 'Journal transaction recorded successfully!');
    }

    public function storeLedger(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:ledgers,name',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
        ]);

        Ledger::create([
            'name' => $request->name,
            'code' => 'L-' . strtoupper(substr(uniqid(), -6)),
            'type' => $request->type,
        ]);

        return back()->with('success', 'New Ledger created successfully!');
    }
}
