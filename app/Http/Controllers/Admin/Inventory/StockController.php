<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $items = InventoryItem::orderBy('name')->get();
        $transactions = InventoryTransaction::with('item')->orderBy('transaction_date', 'desc')->paginate(20);

        return view('admin.inventory.stock.index', compact('items', 'transactions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:inventory_items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'department' => 'nullable|string',
            'purpose' => 'nullable|string',
            'issued_by' => 'nullable|string',
            'received_by' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $item = InventoryItem::findOrFail($request->item_id);

            if ($request->type == 'out' && $item->stock_qty < $request->quantity) {
                throw new \Exception('Insufficient stock for this item. Available: ' . $item->stock_qty);
            }

            InventoryTransaction::create($request->all());

            if ($request->type == 'in') {
                $item->increment('stock_qty', $request->quantity);
            } else {
                $item->decrement('stock_qty', $request->quantity);
            }
        });

        return back()->with('success', 'Stock transaction recorded successfully!');
    }
}
