@extends('layouts.app')

@section('title', 'My Fees')

@section('content')
<style>
.fee-summary-card {
    border-radius: 12px;
    padding: 16px 20px;
    border: 1px solid var(--bs-border-color);
}
.fee-category-card {
    border: 1px solid var(--bs-border-color);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 14px;
    transition: box-shadow 0.2s;
}
.fee-category-card:hover {
    box-shadow: 0 4px 20px rgba(0,0,0,0.07);
}
.fee-category-header {
    padding: 14px 20px;
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--bs-body-bg);
    border-bottom: 1px solid transparent;
    transition: background 0.15s;
}
.fee-category-header:hover {
    background: rgba(13, 110, 253, 0.03);
}
.fee-category-header.open {
    border-bottom: 1px solid var(--bs-border-color);
}
.fee-category-body {
    display: none;
    background: var(--bs-body-bg);
}
.fee-category-body.show {
    display: block;
}
.fee-installment-row {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid var(--bs-border-color);
    font-size: 0.82rem;
    transition: background 0.12s;
}
.fee-installment-row:last-child {
    border-bottom: none;
}
.fee-installment-row:hover {
    background: rgba(13, 110, 253, 0.025);
}
.progress-thin {
    height: 5px;
    border-radius: 3px;
    background: rgba(13, 110, 253, 0.12);
}
.progress-thin .progress-bar {
    border-radius: 3px;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>My Fees</h5>
                <p class="text-muted fs-7 mb-0">Category-wise fee breakdown with discount &amp; payment details</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif

        {{-- Summary Bar --}}
        @php
            $pct = $totalNetBilled > 0 ? round(($totalPaid / $totalNetBilled) * 100) : 0;
            if ($pct > 100) $pct = 100;
        @endphp
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
            <div class="card-body p-4">
                {{-- Student Info Strip --}}
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary"
                        style="width: 44px; height: 44px; font-size: 1.2rem; font-weight: 700; color: #fff; flex-shrink: 0;">
                        {{ strtoupper(substr($student->user->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <div class="fw-bold">{{ $student->user->name ?? 'Student' }}</div>
                        <div class="text-muted fs-8">
                            # Roll: {{ $student->roll_no }}
                            &nbsp;·&nbsp; <i class="fa-solid fa-graduation-cap me-1"></i>{{ $student->schoolClass->name ?? 'N/A' }}
                            &nbsp;·&nbsp; {{ $student->section->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                {{-- Summary Cards --}}
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md">
                        <div class="fee-summary-card">
                            <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">
                                <i class="fa-solid fa-receipt me-1"></i>Base Billed
                            </div>
                            <div class="fw-bold fs-6">৳{{ number_format($totalSubtotal, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="fee-summary-card" style="border-color: #22c55e44; background: rgba(34, 197, 94, 0.03);">
                            <div class="text-success fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">
                                <i class="fa-solid fa-tags me-1"></i>Discount
                            </div>
                            <div class="fw-bold fs-6 text-success">-৳{{ number_format($totalDiscount, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="fee-summary-card" style="border-color: #0ea5e944; background: rgba(14, 165, 233, 0.03);">
                            <div class="text-info fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">
                                <i class="fa-solid fa-calculator me-1"></i>Net Payable
                            </div>
                            <div class="fw-bold fs-6 text-info">৳{{ number_format($totalNetBilled, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="fee-summary-card" style="border-color: #22c55e33;">
                            <div class="text-success fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">
                                <i class="fa-solid fa-circle-check me-1"></i>Paid
                            </div>
                            <div class="fw-bold fs-6 text-success">৳{{ number_format($totalPaid, 0) }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="fee-summary-card" style="border-color: #ef444433;">
                            <div class="text-danger fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Due
                            </div>
                            <div class="fw-bold fs-6 text-danger">৳{{ number_format($totalDue, 0) }}</div>
                        </div>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="progress progress-thin">
                    <div class="progress-bar bg-primary" style="width: {{ $pct }}%"></div>
                </div>
                <div class="text-end text-muted fs-8 mt-1">{{ $pct }}% cleared</div>
            </div>
        </div>

        {{-- Category Breakdown --}}
        @if($grouped->count())
            <div class="mb-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold m-0 text-secondary">Fee Categories</h6>
                <button class="btn btn-link btn-sm text-muted p-0 fs-8" id="expand-all-btn">
                    <i class="fa-solid fa-angles-down me-1"></i>Expand All
                </button>
            </div>

            @foreach($grouped as $catIndex => $cat)
                @php
                    $catIcon = match(strtolower($cat['name'])) {
                        'hostel fee'    => 'fa-hotel',
                        'transport fee' => 'fa-bus',
                        'tuition fee'   => 'fa-graduation-cap',
                        default         => 'fa-file-invoice-dollar',
                    };
                    $catColor = match(strtolower($cat['name'])) {
                        'hostel fee'    => 'text-success',
                        'transport fee' => 'text-primary',
                        'tuition fee'   => 'text-warning',
                        default         => 'text-secondary',
                    };
                @endphp
                <div class="fee-category-card" id="cat-card-{{ $catIndex }}">
                    {{-- Category Header --}}
                    <div class="fee-category-header" onclick="toggleCategory({{ $catIndex }})">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fa-solid {{ $catIcon }} {{ $catColor }}" style="width: 18px;"></i>
                            <div>
                                <div class="fw-bold fs-7">{{ $cat['name'] }}</div>
                                <div class="text-muted fs-8">{{ $cat['count'] }} installment(s)</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3 gap-md-4">
                            <div class="text-end d-none d-md-block">
                                <div class="text-muted fs-8 text-uppercase" style="letter-spacing: 1px;">Base Billed</div>
                                <div class="fw-bold fs-7">৳{{ number_format($cat['totalBase'], 0) }}</div>
                            </div>
                            @if($cat['totalDiscount'] > 0)
                                <div class="text-end d-none d-md-block">
                                    <div class="text-success fs-8 text-uppercase" style="letter-spacing: 1px;">Discount</div>
                                    <div class="fw-bold fs-7 text-success">-৳{{ number_format($cat['totalDiscount'], 0) }}</div>
                                </div>
                            @endif
                            <div class="text-end">
                                <div class="text-muted fs-8 text-uppercase" style="letter-spacing: 1px;">Net Payable</div>
                                <div class="fw-bold fs-7 text-info">৳{{ number_format($cat['totalNet'], 0) }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted fs-8 text-uppercase" style="letter-spacing: 1px;">Paid</div>
                                <div class="fw-bold fs-7 text-success">৳{{ number_format($cat['totalPaid'], 0) }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-muted fs-8 text-uppercase" style="letter-spacing: 1px;">Due</div>
                                <div class="fw-bold fs-7 {{ $cat['totalDue'] > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳{{ number_format($cat['totalDue'], 0) }}
                                </div>
                            </div>
                            <div>
                                <i class="fa-solid fa-chevron-down text-muted cat-chevron-{{ $catIndex }}" style="font-size: 0.75rem; transition: transform 0.2s;"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Category Body — Month by Month --}}
                    <div class="fee-category-body" id="cat-body-{{ $catIndex }}">
                        {{-- Sub-header --}}
                        <div style="display: flex; padding: 8px 20px; background: rgba(13,110,253,0.04); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #64748b;">
                            <div style="flex: 2;">Month / Installment</div>
                            <div style="flex: 1; text-align: right;">Base Price</div>
                            <div style="flex: 1; text-align: right;">Discount</div>
                            <div style="flex: 1; text-align: right;">Payable</div>
                            <div style="flex: 1; text-align: right;">Paid</div>
                            <div style="flex: 1; text-align: right;">Due</div>
                            <div style="flex: 1; text-align: right;">Status</div>
                            <div style="flex: 1; text-align: right;">Action</div>
                        </div>

                        @foreach($cat['items'] as $item)
                            @php
                                $statusBadge = match($item->calculated_status) {
                                    'paid'    => ['bg-success bg-opacity-10 text-success border border-success border-opacity-25', 'fa-circle-check', 'Paid'],
                                    'partial' => ['bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25', 'fa-circle-half-stroke', 'Partial'],
                                    default   => ['bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25', 'fa-circle-xmark', 'Unpaid'],
                                };
                            @endphp
                            <div class="fee-installment-row">
                                <div style="flex: 2;">
                                    <div class="fw-semibold">{{ $item->installment_name ?? ('Installment #' . ($loop->index + 1)) }}</div>
                                    @if($item->due_date)
                                        <div class="text-muted fs-8">Due: {{ $item->due_date->format('d M Y') }}</div>
                                    @endif
                                </div>
                                <div style="flex: 1; text-align: right;" class="fw-semibold text-muted">৳{{ number_format($item->amount, 0) }}</div>
                                <div style="flex: 1; text-align: right;">
                                    @if($item->discount_calculated > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                            -৳{{ number_format($item->discount_calculated, 0) }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-8">৳0</span>
                                    @endif
                                </div>
                                <div style="flex: 1; text-align: right;" class="fw-bold text-info">৳{{ number_format($item->net_amount, 0) }}</div>
                                <div style="flex: 1; text-align: right;" class="text-success fw-bold">৳{{ number_format($item->calculated_paid, 0) }}</div>
                                <div style="flex: 1; text-align: right;" class="{{ $item->calculated_due > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    ৳{{ number_format($item->calculated_due, 0) }}
                                </div>
                                <div style="flex: 1; text-align: right;">
                                    <span class="badge fs-8 {{ $statusBadge[0] }}">
                                        <i class="fa-solid {{ $statusBadge[1] }} me-1"></i>{{ $statusBadge[2] }}
                                    </span>
                                </div>
                                <div style="flex: 1; text-align: right;">
                                    @if($item->calculated_due > 0 && $item->invoice)
                                        <a href="{{ route('student.fees.pay', $item->invoice_id) }}"
                                            class="btn btn-xs btn-success py-0 px-2" style="font-size: 0.72rem;">
                                            <i class="fa-solid fa-credit-card me-1"></i>Pay
                                        </a>
                                    @else
                                        <span class="text-success fs-8"><i class="fa-solid fa-circle-check"></i></span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-body p-5 text-center">
                    <i class="fa-solid fa-file-invoice fa-3x text-muted mb-3 d-block" style="opacity: 0.25;"></i>
                    <h6 class="text-muted">No fee records found.</h6>
                    <p class="text-muted fs-7 mb-0">Your fee invoices will appear here once generated.</p>
                </div>
            </div>
        @endif

        {{-- ===== Payment History ===== --}}
        @if($paymentHistory->count())
            <div class="card border-0 shadow-sm mt-4" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3 px-4" style="border-radius: 15px 15px 0 0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0">
                            <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Payment History
                        </h6>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fs-8">
                            {{ $paymentHistory->count() }} transaction(s)
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light fs-7 text-secondary">
                                <tr>
                                    <th class="ps-4">#</th>

                                    <th>Invoice</th>
                                    <th>Purpose / Fee Type</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Transaction ID</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                @foreach($paymentHistory as $i => $pmt)
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>

                                        <td class="text-muted">{{ $pmt->invoice->invoice_number ?? '—' }}</td>
                                        <td>
                                            @if($pmt->invoice && $pmt->invoice->items->count())
                                                @foreach($pmt->invoice->items as $item)
                                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8 me-1 mb-1">
                                                        <i class="fa-solid fa-tag me-1"></i>{{ $item->feeCategory->name ?? 'Fee' }}
                                                        @if($item->installment_name)
                                                            ({{ $item->installment_name }})
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-muted fs-8">General Fee</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">
                                            {{ $pmt->payment_date ? $pmt->payment_date->format('d M Y') : '—' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                                {{ ucfirst($pmt->payment_method ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td class="text-muted fs-8">{{ $pmt->transaction_id ?? '—' }}</td>
                                        <td class="text-end pe-4 fw-bold text-success">
                                            ৳{{ number_format($pmt->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="">
                                <tr>
                                    <td colspan="6" class="text-end fw-bold ps-4 py-2 fs-7">Total Paid:</td>
                                    <td class="text-end pe-4 fw-bold text-success">
                                        ৳{{ number_format($paymentHistory->sum('amount'), 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

<script>
function toggleCategory(idx) {
    const body    = document.getElementById('cat-body-' + idx);
    const chevron = document.querySelector('.cat-chevron-' + idx);
    const header  = body.previousElementSibling;
    const isOpen  = body.classList.contains('show');

    body.classList.toggle('show', !isOpen);
    header.classList.toggle('open', !isOpen);
    chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
}

document.getElementById('expand-all-btn')?.addEventListener('click', function () {
    const bodies  = document.querySelectorAll('.fee-category-body');
    const allOpen = [...bodies].every(b => b.classList.contains('show'));

    bodies.forEach((body, idx) => {
        const chevron = document.querySelector('.cat-chevron-' + idx);
        const header  = body.previousElementSibling;
        body.classList.toggle('show', !allOpen);
        header.classList.toggle('open', !allOpen);
        if (chevron) chevron.style.transform = allOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    });

    this.innerHTML = allOpen
        ? '<i class="fa-solid fa-angles-down me-1"></i>Expand All'
        : '<i class="fa-solid fa-angles-up me-1"></i>Collapse All';
});

// Auto-open first category
document.addEventListener('DOMContentLoaded', () => {
    const first = document.getElementById('cat-body-0');
    if (first) {
        first.classList.add('show');
        const chevron = document.querySelector('.cat-chevron-0');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        first.previousElementSibling?.classList.add('open');
    }
});
</script>
@endsection
