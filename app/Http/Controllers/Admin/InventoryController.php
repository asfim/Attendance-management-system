<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventorySupplier;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;


class InventoryController extends Controller
{
    public function index()
    {
        $categories = InventoryCategory::all();
        $items = InventoryItem::with('category')->get();
        return view('admin.inventory.index', compact('categories', 'items'));
    }

    public function suppliers()
    {
        $suppliers = InventorySupplier::all();
        return view('admin.inventory.suppliers', compact('suppliers'));
    }

    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
        ]);

        InventorySupplier::create($request->only('name', 'phone', 'email', 'address'));
        return back()->with('success', 'Supplier added successfully!');
    }

    public function storeItem(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:inventory_categories,id',
            'unit' => 'required|string|max:50',
            'sku' => 'nullable|string|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        InventoryItem::create($request->all());
        return back()->with('success', 'Inventory Item added successfully!');
    }
}
