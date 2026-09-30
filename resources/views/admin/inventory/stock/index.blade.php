@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-right-left text-info me-2"></i>Stock Transactions</h4>
            <p class="text-muted fs-7 mb-0">Record manual Stock In and Stock Out</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#stockTransactionModal">
                <i class="fa-solid fa-plus me-2"></i>New Transaction
            </button>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card glass-card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Item Name</th>
                            <th>Qty</th>
                            <th>Department / Purpose</th>
                            <th>Issued / Received By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td>{{ $txn->transaction_date->format('d M, Y') }}</td>
                            <td>
                                @if($txn->type == 'in')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-solid fa-arrow-down me-1"></i>Stock In</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fa-solid fa-arrow-up me-1"></i>Stock Out</span>
                                @endif
                            </td>
                            <td class="fw-medium text-primary">{{ $txn->item->name ?? 'Unknown Item' }}</td>
                            <td class="fw-bold">{{ $txn->quantity }} {{ $txn->item->unit ?? '' }}</td>
                            <td>
                                <div class="fs-8 fw-semibold">{{ $txn->department ?? '-' }}</div>
                                <div class="fs-8 text-muted">{{ $txn->purpose ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fs-8 text-muted">Iss: {{ $txn->issued_by ?? '-' }}</div>
                                <div class="fs-8 text-muted">Rec: {{ $txn->received_by ?? '-' }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No stock transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Stock Transaction Modal -->
<div class="modal fade" id="stockTransactionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Manual Stock Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.inventory.stock.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Transaction Type *</label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check border rounded px-3 py-2 flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="type" id="typeIn" value="in" required>
                                    <label class="form-check-label fw-semibold text-success w-100" for="typeIn">
                                        <i class="fa-solid fa-arrow-down me-1"></i>Stock In
                                    </label>
                                </div>
                                <div class="form-check border rounded px-3 py-2 flex-fill">
                                    <input class="form-check-input ms-0 me-2" type="radio" name="type" id="typeOut" value="out" required>
                                    <label class="form-check-label fw-semibold text-danger w-100" for="typeOut">
                                        <i class="fa-solid fa-arrow-up me-1"></i>Stock Out
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date *</label>
                            <input type="date" name="transaction_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Select Item *</label>
                            <select name="item_id" class="form-select" required>
                                <option value="">Select Item (Available Stock)</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }} - Stock: {{ $item->stock_qty }} {{ $item->unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Quantity *</label>
                            <input type="number" name="quantity" class="form-control" required min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Department</label>
                            <input type="text" name="department" class="form-control" placeholder="e.g. Science Lab">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Purpose</label>
                            <input type="text" name="purpose" class="form-control" placeholder="e.g. Class Experiment">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Issued By</label>
                            <input type="text" name="issued_by" class="form-control" placeholder="Name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Received By</label>
                            <input type="text" name="received_by" class="form-control" placeholder="Name">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
