@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-box text-primary me-2"></i>Inventory Setup</h4>
            <p class="text-muted fs-7 mb-0">Manage Inventory Items</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Add Item Card -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Add Inventory Item</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.inventory.items.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Item Name *</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Printer Ink">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Unit *</label>
                                <input type="text" name="unit" class="form-control" required placeholder="e.g. pcs, box, kg">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">SKU</label>
                                <input type="text" name="sku" class="form-control" placeholder="Optional">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Min Stock *</label>
                                <input type="number" name="min_stock" class="form-control" value="0" min="0" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Storage Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Room 101 Rack 3">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4"><i class="fa-solid fa-plus me-2"></i>Save Item</button>
                    </form>
                </div>
            </div>

            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Inventory Catalog</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Item Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td class="ps-4 fw-medium text-primary">{{ $item->name }} <br><span class="fs-8 text-muted">{{ $item->sku }}</span></td>
                                        <td>{{ $item->category->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge {{ $item->stock_qty <= $item->min_stock ? 'bg-danger' : 'bg-success' }}">
                                                {{ $item->stock_qty }} {{ $item->unit }}
                                            </span>
                                        </td>
                                        <td>{{ $item->location ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No items in catalog.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
