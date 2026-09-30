@extends('layouts.app', ['title' => 'Accounts - EduERP', 'header' => 'Accounts Dashboard'])

@section('content')

    <style>
        .fs-10 {
            font-size: 10px !important;
            border-radius: 8px !important;
            background-color: #2a61f7 !important;
            color: white !important;
            border: none !important;
            padding: 5px 10px;
            transition: background-color 0.3s ease;
        }

        .fs-10:hover {
            background-color: #3b7ac2 !important;
            /* slightly darker for hover */
            color: white !important;
        }
    </style>
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert"
            style="border-radius: 12px;">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please fix the following errors:
            </h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Finance Reports Quick Links -->
        <div class="col-12">
            <div class="card glass-card border-0 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-file-invoice me-2"></i>Finance Reports</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('admin.reports.finance') }}" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-chart-pie me-2"></i>General Finance Report
                        </a>
                        <a href="{{ route('admin.finance.reports.donations') }}" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-hand-holding-heart me-2"></i>Donations Report
                        </a>
                        <a href="{{ route('admin.finance.reports.expenses') }}" class="btn btn-outline-danger rounded-pill px-4 py-2 fw-semibold">
                            <i class="fa-solid fa-money-bill-transfer me-2"></i>Expenses Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Profit & Loss Card -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 h-100 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-chart-line me-2"></i>Profit & Loss</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Revenues -->
                    <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-arrow-trend-up me-2"></i>Revenues</h6>
                    @if (count($pl['revenues']) > 0)
                        @foreach ($pl['revenues'] as $rev)
                            <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                                <span>{{ $rev['name'] }}</span>
                                <span>৳{{ number_format($rev['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No revenue records found.</p>
                    @endif
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-2 fw-bold text-success">
                        <span>Total Revenues:</span>
                        <span>৳{{ number_format($pl['total_revenue'], 2) }}</span>
                    </div>

                    <!-- Expenses -->
                    <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-arrow-trend-down me-2"></i>Expenses</h6>
                    @if (count($pl['expenses']) > 0)
                        @foreach ($pl['expenses'] as $exp)
                            <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                                <span>{{ $exp['name'] }}</span>
                                <span>৳{{ number_format($exp['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No expense records found.</p>
                    @endif
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-2 fw-bold text-danger">
                        <span>Total Expenses:</span>
                        <span>৳{{ number_format($pl['total_expense'], 2) }}</span>
                    </div>

                    <!-- Net Profit -->
                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <span class="fw-bold fs-5">Net Profit/Loss:</span>
                        <span
                            class="fw-bold fs-4 text-{{ $pl['net_profit'] >= 0 ? 'success' : 'danger' }} bg-{{ $pl['net_profit'] >= 0 ? 'success' : 'danger' }} bg-opacity-10 px-3 py-1 rounded-pill">
                            ৳{{ number_format($pl['net_profit'], 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Sheet Card -->
        <div class="col-lg-6">
            <div class="card glass-card border-0 h-100 shadow-sm rounded-4">
                <div class="card-header bg-transparent border-bottom p-4">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-scale-balanced me-2"></i>Balance Sheet</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Assets -->
                    <h6 class="fw-bold text-info mb-3"><i class="fa-solid fa-building-columns me-2"></i>Assets</h6>
                    @if (count($bs['assets']) > 0)
                        @foreach ($bs['assets'] as $asset)
                            <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                                <span>{{ $asset['name'] }}</span>
                                <span>৳{{ number_format($asset['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No asset records found.</p>
                    @endif
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-2 fw-bold text-info">
                        <span>Total Assets:</span>
                        <span>৳{{ number_format($bs['total_assets'], 2) }}</span>
                    </div>

                    <!-- Liabilities -->
                    <h6 class="fw-bold text-danger mb-3"><i class="fa-solid fa-hand-holding-dollar me-2"></i>Liabilities
                    </h6>
                    @if (count($bs['liabilities']) > 0)
                        @foreach ($bs['liabilities'] as $liab)
                            <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                                <span>{{ $liab['name'] }}</span>
                                <span>৳{{ number_format($liab['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No liability records found.</p>
                    @endif
                    <div class="d-flex justify-content-between mb-4 border-bottom pb-2 fw-bold text-danger">
                        <span>Total Liabilities:</span>
                        <span>৳{{ number_format($bs['total_liabilities'], 2) }}</span>
                    </div>

                    <!-- Equity -->
                    <h6 class="fw-bold text-warning mb-3"><i class="fa-solid fa-coins me-2"></i>Equity</h6>
                    @if (count($bs['equity']) > 0)
                        @foreach ($bs['equity'] as $eq)
                            <div class="d-flex justify-content-between mb-2 text-secondary" style="font-size: 0.9rem;">
                                <span>{{ $eq['name'] }}</span>
                                <span>৳{{ number_format($eq['balance'], 2) }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted small">No equity records found.</p>
                    @endif
                    <div class="d-flex justify-content-between mt-auto pt-3 border-top fw-bold text-warning mb-2">
                        <span class="fs-6">Total Equity:</span>
                        <span class="fs-5">৳{{ number_format($bs['total_equity'], 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between mt-3 pt-3 border-top fw-bold text-primary" style="border-top-width: 2px !important;">
                        <span class="fs-5">Total Liab. & Equity:</span>
                        <span class="fs-4">৳{{ number_format($bs['total_liabilities'] + $bs['total_equity'], 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Transactions List -->
        <div class="col-lg-8">
            <div class="card glass-card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>Recent Transactions
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary" style="font-size: 0.85rem;">
                                <tr>
                                    <th class="ps-4">Date</th>
                                    <th>Description (Voucher)</th>
                                    <th>From (Credit)</th>
                                    <th>To (Debit)</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedTxns = collect($transactions->items())->groupBy('reference_no');
                                @endphp
                                @forelse($groupedTxns as $ref => $group)
                                    @php
                                        $firstTxn = $group->first();
                                        $debitTxn = $group->where('type', 'debit')->first();
                                        $creditTxn = $group->where('type', 'credit')->first();
                                    @endphp
                                    <tr>
                                        <td class="ps-4" style="font-size: 0.85rem;">
                                            <div class="fw-bold">{{ \Carbon\Carbon::parse($firstTxn->date)->format('M d, Y') }}</div>
                                        </td>
                                        <td>
                                            <div class="text-body fw-semibold" style="font-size: 0.85rem;">{{ $firstTxn->description }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">{{ $ref }}</div>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                {{ $creditTxn->ledger->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                {{ $debitTxn->ledger->name ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold text-primary pe-4">
                                            ৳{{ number_format($firstTxn->amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center p-4 text-secondary">No recent transactions found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($transactions->hasPages())
                    <div class="card-footer bg-transparent p-3">
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Post Journal Entry -->
        <div class="col-lg-4">
            <div class="card glass-card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Post Journal Entry
                    </h5>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_account'))
<button type="button" class="btn btn-sm btn-outline-primary rounded-pill fs-10" data-bs-toggle="modal"
                        data-bs-target="#addLedgerModal">
                        <i class="fa-solid fa-plus"></i> New Ledger
                    </button>
@endif
                </div>
                <div class="card-body p-4">

                    <form action="{{ route('admin.accounts.transactions.store') }}" method="POST">
                        @csrf
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-secondary fw-semibold small">Debit Account</label>
                                <select name="debit_ledger_id" class="form-select bg-body text-body border-secondary border-opacity-50 shadow-sm rounded-3">
                                    <option value="">-- Select Debit Ledger --</option>
                                    @php $groupedLedgers = $ledgers->groupBy('type'); @endphp
                                    @foreach ($groupedLedgers as $type => $group)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach ($group as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary fw-semibold small">Credit Account</label>
                                <select name="credit_ledger_id" class="form-select bg-body text-body border-secondary border-opacity-50 shadow-sm rounded-3">
                                    <option value="">-- Select Credit Ledger --</option>
                                    @foreach ($groupedLedgers as $type => $group)
                                        <optgroup label="{{ ucfirst($type) }}">
                                            @foreach ($group as $ledger)
                                                <option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Transaction Date</label>
                            <input type="date" name="date" class="form-control border shadow-sm rounded-3"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Amount (৳)</label>
                            <input type="number" name="amount" class="form-control border shadow-sm rounded-3"
                                step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary fw-semibold small">Description</label>
                            <input type="text" name="description" class="form-control border shadow-sm rounded-3"
                                placeholder="e.g., Office Supplies" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2 fw-bold">
                            <i class="fa-solid fa-file-invoice-dollar me-2"></i>Post Entry
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Ledger Modal -->
    <div class="modal fade" id="addLedgerModal" tabindex="-1" aria-labelledby="addLedgerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow rounded-4">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary" id="addLedgerModalLabel"><i
                            class="fa-solid fa-folder-plus me-2"></i>Create New Ledger</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.accounts.ledgers.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary fw-semibold small">Ledger Name</label>
                            <input type="text" name="name" class="form-control border shadow-sm rounded-3"
                                placeholder="e.g. Internet Bill" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary fw-semibold small">Ledger Type</label>
                            <select name="type" class="form-select border shadow-sm rounded-3" required>
                                <option value="">-- Select Type --</option>
                                <option value="asset">Asset (e.g. Cash, Bank)</option>
                                <option value="liability">Liability (e.g. Payable)</option>
                                <option value="equity">Equity (e.g. Capital)</option>
                                <option value="revenue">Revenue (e.g. Income)</option>
                                <option value="expense">Expense (e.g. Bills, Salary)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2 fw-bold">Save
                            Ledger</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
