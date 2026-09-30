@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('admin.finance.expenses.index') }}" class="btn btn-sm btn-outline-secondary mb-2"><i class="fa-solid fa-arrow-left me-1"></i>Back to Expenses</a>
            <h4 class="fw-bold m-0">Add New Expense</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card glass-card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form action="{{ route('admin.finance.expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Expense Date *</label>
                                <input type="date" name="expense_date" class="form-control" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0">Category *</label>
                                    <a href="{{ route('admin.finance.expenses.categories') }}" class="fs-8 text-primary text-decoration-none"><i class="fa-solid fa-plus me-1"></i>New Category</a>
                                </div>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Expense Purpose / Name *</label>
                                <input type="text" name="purpose" class="form-control" required placeholder="e.g. Electricity Bill - October">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Amount (৳) *</label>
                                <input type="number" step="0.01" name="amount" class="form-control" required placeholder="0.00">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Mobile Banking">Mobile Banking</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Paid By (Person)</label>
                                <input type="text" name="paid_by" class="form-control" placeholder="Name of person">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Ref / Invoice No</label>
                                <input type="text" name="reference_no" class="form-control" placeholder="Optional">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="More details about the expense..."></textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Upload Bill / Receipt</label>
                                <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <div class="form-text fs-8">Max size: 5MB. Formats: JPG, PNG, PDF</div>
                            </div>

                            <div class="col-12 text-end pt-3 border-top border-light mt-4">
                                <button type="reset" class="btn btn-light border me-2">Clear</button>
                                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-2"></i>Save Expense</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
