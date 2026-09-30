<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryPurchase;
use App\Models\InventoryPurchaseItem;
use App\Models\InventorySupplier;
use App\Models\InventoryItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = InventoryPurchase::with(['supplier', 'items.item'])->orderBy('purchase_date', 'desc')->paginate(20);
        $suppliers = InventorySupplier::orderBy('name')->get();
        $items = InventoryItem::orderBy('name')->get();

        return view('admin.inventory.purchases.index', compact('purchases', 'suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:inventory_suppliers,id',
            'purchase_date' => 'required|date',
            'payment_status' => 'required|string',
            'payment_method' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }

            $purchase = InventoryPurchase::create([
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'grand_total' => $total,
                'status' => 'received', // Auto receive for simplicity in this flow
                'payment_status' => $request->payment_status,
                'payment_method' => $request->payment_method,
                'invoice_number' => $request->invoice_number,
                'notes' => $request->notes,
            ]);

            foreach ($request->items as $itemData) {
                InventoryPurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);

                // Increment stock qty
                $item = InventoryItem::findOrFail($itemData['item_id']);
                $item->increment('stock_qty', $itemData['quantity']);
            }

            // Record as Expense if paid
            if ($request->payment_status == 'paid') {
                $category = ExpenseCategory::firstOrCreate(
                    ['name' => 'Purchases & Inventory'],
                    ['description' => 'Auto-generated category for inventory purchases']
                );

                Expense::create([
                    'category_id' => $category->id,
                    'expense_date' => $request->purchase_date,
                    'purpose' => 'Inventory Purchase - Inv#' . $request->invoice_number,
                    'description' => 'Supplier: ' . InventorySupplier::find($request->supplier_id)->name,
                    'amount' => $total,
                    'payment_method' => $request->payment_method,
                    'reference_no' => $request->invoice_number,
                    'created_by' => Auth::id()
                ]);
            }
        });

        return back()->with('success', 'Purchase recorded successfully and stock updated!');
    }
}
