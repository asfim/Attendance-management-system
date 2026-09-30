@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-money-bill-wave text-danger me-2"></i>Expenses</h4>
            <p class="text-muted fs-7 mb-0">Manage all school financial expenses</p>
        </div>
        <div>
            <a href="{{ route('admin.finance.expenses.categories') }}" class="btn btn-outline-secondary shadow-sm me-2">Categories</a>
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn btn-primary shadow-sm"><i class="fa-solid fa-plus me-2"></i>Add Expense</a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card glass-card mb-4 border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('admin.finance.expenses.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold text-muted">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold text-muted">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-7 fw-semibold text-muted">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-2"></i>Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary & Data Table -->
    <div class="card glass-card border-0 shadow-sm">
        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Expense Records</h6>
            <h6 class="fw-bold mb-0 text-danger">Filtered Total: ৳{{ number_format($totalFiltered, 2) }}</h6>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Purpose / Name</th>
                            <th>Paid By</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M, Y') }}</td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $expense->category->name ?? 'N/A' }}</span></td>
                            <td class="fw-medium">{{ $expense->purpose }}</td>
                            <td>{{ $expense->paid_by ?? '-' }}</td>
                            <td>{{ $expense->payment_method ?? '-' }}</td>
                            <td class="text-end fw-bold text-danger">৳{{ number_format($expense->amount, 2) }}</td>
                            <td class="text-end">
                                @if($expense->attachment)
                                <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" class="btn btn-sm btn-outline-info border-0 me-1" title="View Attachment"><i class="fa-solid fa-paperclip"></i></a>
                                @endif
                                <form action="{{ route('admin.finance.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this expense record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt fs-2 mb-3 opacity-25"></i>
                                <h5>No expenses found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $expenses->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
