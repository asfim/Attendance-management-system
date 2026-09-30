@extends('layouts.app')

@section('title', 'Finance Report')

@section('content')
    <style>
        :root {
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text-muted: #64748b;
            --text-main: #0f172a;
            --income-color: #22c55e;
            --expense-color: #ef4444;
        }

        [data-bs-theme="dark"] {
            --card-bg: #1e293b;
            --card-border: #334155;
            --text-muted: #94a3b8;
            --text-main: #f8fafc;
        }

        .report-header {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            height: 100%;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .icon-income {
            background: rgba(34, 197, 94, 0.1);
            color: var(--income-color);
        }

        .icon-expense {
            background: rgba(239, 68, 68, 0.1);
            color: var(--expense-color);
        }

        .icon-balance {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
        }

        .section-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .section-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--card-border);
            font-weight: 600;
            font-size: 1.1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table {
            margin-bottom: 0;
            color: var(--text-main);
        }

        .table th {
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid var(--card-border);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        [data-bs-theme="dark"] .table th {
            background-color: rgba(255, 255, 255, 0.02);
        }

        .table td {
            border-bottom: 1px solid var(--card-border);
            vertical-align: middle;
        }

        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .sidebar, .navbar-custom, .navbar, header, footer, .breadcrumb {
                display: none !important;
            }
            .main-content {
                margin-left: 0 !important;
                padding-top: 0 !important;
                width: 100% !important;
            }
            .d-print-none {
                display: none !important;
            }
            * {
                color: #000 !important;
            }
            .summary-card, .section-card {
                border: 1px solid #ccc !important;
                box-shadow: none !important;
                page-break-inside: avoid;
            }
            .badge {
                border: 1px solid #000 !important;
                color: #000 !important;
                background: transparent !important;
            }
            .summary-icon {
                border: 1px solid #000 !important;
                background: transparent !important;
                color: #000 !important;
            }
            h4.text-primary {
                color: #000 !important;
                text-align: center;
                margin-bottom: 20px !important;
            }
        }
    </style>

    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-4 d-print-none">
            <div>
                <h4 class="mb-1 fw-semibold text-primary"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Finance Report
                </h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 fs-7">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"
                                class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item">Reports</li>
                        <li class="breadcrumb-item active" aria-current="page">Finance</li>
                    </ol>
                </nav>
            </div>
            <div>
                <button class="btn btn-outline-secondary" onclick="window.print()"><i
                        class="fa-solid fa-print me-2"></i>Print Report</button>
            </div>
        </div>

        <!-- Filter Header -->
        <div class="report-header shadow-sm d-print-none">
            <form action="{{ route('admin.reports.finance') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label text-muted fs-7">Start Date</label>
                    <input type="date" name="start_date" class="form-control border-secondary border-opacity-25"
                        value="{{ $startDate }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-muted fs-7">End Date</label>
                    <input type="date" name="end_date" class="form-control border-secondary border-opacity-25"
                        value="{{ $endDate }}" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter me-2"></i>Filter
                        Report</button>
                </div>
            </form>
        </div>

        <div id="pdf-content">
            @include('admin.reports.partials.print_header', [
                'title' => 'Finance Report',
                'subtitle' => 'Period: ' . \Carbon\Carbon::parse($startDate)->format('d M, Y') . ' &mdash; ' . \Carbon\Carbon::parse($endDate)->format('d M, Y')
            ])

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <div class="summary-icon icon-income">
                            <i class="fa-solid fa-arrow-trend-up"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold mb-1">Total Income</div>
                            <div class="fs-4 fw-bold" style="color: var(--income-color);">৳{{ number_format($income, 2) }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <div class="summary-icon icon-expense">
                            <i class="fa-solid fa-arrow-trend-down"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold mb-1">Total Expenses</div>
                            <div class="fs-4 fw-bold" style="color: var(--expense-color);">৳{{ number_format($expenses, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card shadow-sm">
                        <div class="summary-icon icon-balance">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </div>
                        <div>
                            <div class="text-muted fs-7 text-uppercase fw-semibold mb-1">Net Balance</div>
                            @php $balance = $income - $expenses; @endphp
                            <div class="fs-4 fw-bold {{ $balance >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($balance, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Expense Section -->
                <div class="col-12 mb-4">
                    <div class="section-card shadow-sm">
                        <div class="section-header text-danger">
                            <span><i class="fa-solid fa-file-invoice me-2"></i>Expense Details</span>
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger rounded-pill border border-danger border-opacity-25">৳{{ number_format($expenses, 2) }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Category</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expenseRecords as $record)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</td>
                                            <td class="fw-medium">{{ $record->description }}</td>
                                            <td><span
                                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">{{ $record->ledger->name ?? 'Expense' }}</span>
                                            </td>
                                            <td class="text-end fw-bold text-danger">৳{{ number_format($record->amount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if ($expenseRecords->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No expense records found for
                                                this period.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Income Section -->
                <div class="col-12">
                    <div class="section-card shadow-sm">
                        <div class="section-header text-success">
                            <span><i class="fa-solid fa-hand-holding-dollar me-2"></i>Income Details</span>
                            <span
                                class="badge bg-success bg-opacity-10 text-success rounded-pill border border-success border-opacity-25">৳{{ number_format($income, 2) }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Ledger</th>
                                        <th>Reference</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($incomeRecords as $record)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($record->date)->format('d M, Y') }}</td>
                                            <td class="fw-medium">{{ $record->ledger->name ?? 'Unknown Ledger' }}</td>
                                            <td class="text-muted fs-7">{{ $record->reference_no ?? '-' }} <br><small>{{ $record->description }}</small></td>
                                            <td class="text-end fw-bold text-success">৳{{ number_format($record->amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No income records found for
                                                this period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.reports.partials.print_footer')
        </div>
    </div>
@endsection


