@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.finance.expenses.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fa-solid fa-arrow-left me-1"></i>Back to Expenses</a>
            <h4 class="fw-bold m-0">Expense Categories</h4>
        </div>
    </div>

    <div class="row g-4">
        <!-- Add Category -->
        <div class="col-md-4">
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Add Category</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.finance.expenses.categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Category Name *</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Utility Bills">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Optional"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-2"></i>Add Category</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Categories List -->
        <div class="col-md-8">
            <div class="card glass-card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent py-3">
                    <h6 class="fw-bold m-0">Categories List</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle m-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Total Expenses</th>
                                    <th>Total Amount</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                <tr>
                                    <td class="ps-4 fw-medium text-primary">{{ $category->name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $category->expenses_count }} records</span></td>
                                    <td class="fw-bold text-danger">৳{{ number_format($category->expenses_sum_amount ?? 0, 2) }}</td>
                                    <td class="text-end pe-4">
                                        <form action="{{ route('admin.finance.expenses.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Delete this category? Ensure no expenses are attached to it.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No categories created yet.</td>
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
