<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Transaction;

class AccountingService
{
    public function getCashBook(): array
    {
        $cashLedger = Ledger::where('code', '1001')->first();
        if (!$cashLedger) {
            return [];
        }

        return Transaction::where('ledger_id', $cashLedger->id)
            ->orderBy('date')
            ->orderBy('id')
            ->get()
            ->toArray();
    }

    public function getProfitAndLoss(): array
    {
        $ledgers = Ledger::with('transactions')->get();
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0.00;
        $totalExpense = 0.00;

        foreach ($ledgers as $ledger) {
            if ($ledger->type === 'revenue') {
                $balance = $ledger->balance;
                $revenues[] = ['name' => $ledger->name, 'balance' => $balance];
                $totalRevenue += $balance;
            } elseif ($ledger->type === 'expense') {
                $balance = $ledger->balance;
                $expenses[] = ['name' => $ledger->name, 'balance' => $balance];
                $totalExpense += $balance;
            }
        }

        $netProfit = $totalRevenue - $totalExpense;

        return [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'total_revenue' => $totalRevenue,
            'total_expense' => $totalExpense,
            'net_profit' => $netProfit,
        ];
    }

    public function getBalanceSheet(): array
    {
        $ledgers = Ledger::with('transactions')->get();
        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0.00;
        $totalLiabilities = 0.00;
        $totalEquity = 0.00;

        foreach ($ledgers as $ledger) {
            $balance = $ledger->balance;
            if ($ledger->type === 'asset') {
                $assets[] = ['name' => $ledger->name, 'balance' => $balance];
                $totalAssets += $balance;
            } elseif ($ledger->type === 'liability') {
                $liabilities[] = ['name' => $ledger->name, 'balance' => $balance];
                $totalLiabilities += $balance;
            } elseif ($ledger->type === 'equity') {
                $equity[] = ['name' => $ledger->name, 'balance' => $balance];
                $totalEquity += $balance;
            }
        }

        // Add net profit to equity (Retained Earnings)
        $pl = $this->getProfitAndLoss();
        $retainedEarnings = $pl['net_profit'];
        $equity[] = ['name' => 'Retained Earnings (Net Profit)', 'balance' => $retainedEarnings];
        $totalEquity += $retainedEarnings;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
        ];
    }
}
