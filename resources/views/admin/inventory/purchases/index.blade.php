@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-cart-shopping text-warning me-2"></i>Purchases</h4>
            <p class="text-muted fs-7 mb-0">Manage inventory and asset purchases</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                <i class="fa-solid fa-plus me-2"></i>Record Purchase
            </button>
        </div>
    </div>

    <div class="card glass-card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Invoice No</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th class="text-end">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_date->format('d M, Y') }}</td>
                            <td class="fw-medium">{{ $purchase->invoice_number ?? '-' }}</td>
                            <td>
                                {{ $purchase->supplier->name ?? 'Unknown' }}<br>
                                <span class="fs-8 text-muted">{{ $purchase->supplier->phone ?? '' }}</span>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 fs-8">
                                    @foreach($purchase->items as $pi)
                                        <li>{{ $pi->quantity }}x {{ $pi->item->name ?? 'Unknown' }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                <span class="badge bg-{{ $purchase->status == 'received' ? 'success' : 'secondary' }}">{{ ucfirst($purchase->status) }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $purchase->payment_status == 'paid' ? 'success' : ($purchase->payment_status == 'partial' ? 'warning' : 'danger') }}">{{ ucfirst($purchase->payment_status) }}</span>
                                <div class="fs-8 text-muted mt-1">{{ $purchase->payment_method }}</div>
                            </td>
                            <td class="text-end fw-bold">৳{{ number_format($purchase->grand_total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No purchases recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Purchase Modal -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold">Record Inventory Purchase</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.inventory.purchases.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold mb-0">Supplier *</label>
                                <a href="{{ route('admin.inventory.suppliers.index') }}" class="fs-8 text-primary text-decoration-none"><i class="fa-solid fa-plus me-1"></i>New</a>
                            </div>
                            <select name="supplier_id" class="form-select" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Purchase Date *</label>
                            <input type="date" name="purchase_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Invoice No</label>
                            <input type="text" name="invoice_number" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Payment Status *</label>
                            <select name="payment_status" class="form-select" required>
                                <option value="unpaid">Unpaid</option>
                                <option value="paid">Paid (Creates Expense)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Mobile Banking">Mobile Banking</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-semibold">Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional notes">
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3 border-bottom pb-2">Purchase Items</h6>
                    <div id="purchaseItemsContainer">
                        <div class="row g-2 mb-2 purchase-item-row align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fs-8 text-muted mb-1">Item *</label>
                                <select name="items[0][item_id]" class="form-select form-select-sm" required>
                                    <option value="">Select Item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fs-8 text-muted mb-1">Quantity *</label>
                                <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty" required min="1" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fs-8 text-muted mb-1">Unit Price (৳) *</label>
                                <input type="number" step="0.01" name="items[0][unit_price]" class="form-control form-control-sm item-price" required min="0" value="0.00">
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-sm btn-outline-danger border-0 w-100" onclick="this.closest('.purchase-item-row').remove(); calculateTotal();"><i class="fa-solid fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-light">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPurchaseItemRow()">
                            <i class="fa-solid fa-plus me-1"></i> Add Another Item
                        </button>
                        <h5 class="fw-bold m-0 text-danger" id="grandTotalDisplay">Total: ৳0.00</h5>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Confirm Purchase</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let itemIndex = 1;
    function addPurchaseItemRow() {
        const container = document.getElementById('purchaseItemsContainer');
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 purchase-item-row align-items-end';
        row.innerHTML = `
            <div class="col-md-5">
                <select name="items[${itemIndex}][item_id]" class="form-select form-select-sm" required>
                    <option value="">Select Item</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} ({{ $item->unit }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm item-qty" required min="1" value="1">
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm item-price" required min="0" value="0.00">
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-sm btn-outline-danger border-0 w-100" onclick="this.closest('.purchase-item-row').remove(); calculateTotal();"><i class="fa-solid fa-times"></i></button>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
        
        // Re-bind events
        bindCalculationEvents();
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.purchase-item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            total += qty * price;
        });
        document.getElementById('grandTotalDisplay').innerText = 'Total: ৳' + total.toFixed(2);
    }

    function bindCalculationEvents() {
        document.querySelectorAll('.item-qty, .item-price').forEach(input => {
            input.removeEventListener('input', calculateTotal);
            input.addEventListener('input', calculateTotal);
        });
    }

    document.addEventListener('DOMContentLoaded', bindCalculationEvents);
</script>
@endsection
