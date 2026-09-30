@extends('layouts.app')

@section('title', 'Payroll Management')

@section('content')
<style>
/* ─── Variables ──────────────────────────────────────── */
:root {
    --card-bg:     #ffffff;
    --card-border: #e2e8f0;
    --pr-purple:   #6366f1;
    --pr-green:    #22c55e;
    --pr-red:      #ef4444;
    --pr-orange:   #f97316;
    --pr-blue:     #3b82f6;
}
[data-bs-theme="dark"] {
    --card-bg:     #1e293b;
    --card-border: #334155;
}

/* ─── Page Layout ────────────────────────────────────── */
.pr-wrap { display: flex; gap: 1.5rem; align-items: flex-start; min-height: 80vh; }
.pr-list { width: 360px; flex-shrink: 0; }
.pr-detail { flex: 1; min-width: 0; }

/* ─── Filter Card ────────────────────────────────────── */
.filter-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.filter-card h6 { font-weight: 700; margin-bottom: 1rem; }

/* ─── Employee List Item ─────────────────────────────── */
.emp-item {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 12px;
    padding: .85rem 1rem;
    margin-bottom: .5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: .85rem;
    transition: all .18s;
}
.emp-item:hover { border-color: var(--pr-purple); box-shadow: 0 4px 16px rgba(99,102,241,.12); }
.emp-item.active {
    border-color: var(--pr-purple);
    background: rgba(99,102,241,.06);
    box-shadow: 0 4px 16px rgba(99,102,241,.18);
}
.emp-avatar {
    width: 42px; height: 42px;
    border-radius: 10px;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid var(--card-border);
}
.emp-name { font-weight: 700; font-size: .88rem; margin: 0; }
.emp-sub  { font-size: .73rem; color: #9ca3af; margin: 0; }
.emp-status-badge {
    margin-left: auto;
    flex-shrink: 0;
    font-size: .68rem;
    font-weight: 700;
    padding: .25rem .55rem;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: .04em;
}

/* ─── Detail Panel ───────────────────────────────────── */
.detail-placeholder {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 400px; color: #9ca3af; gap: 1rem;
    background: var(--card-bg); border: 1px dashed var(--card-border); border-radius: 20px;
}

/* ─── Payroll Card ───────────────────────────────────── */
.pr-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    margin-bottom: 1rem;
}
.pr-card-header {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--card-border);
    display: flex; align-items: center; justify-content: space-between;
    font-weight: 700;
}
.pr-card-body { padding: 1.25rem; }

/* ─── Employee Info Header ───────────────────────────── */
.emp-header-card {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    border-radius: 16px;
    padding: 1.5rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    position: relative;
    overflow: hidden;
}
[data-bs-theme="dark"] .emp-header-card { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
.emp-header-card::before {
    content: '';
    position: absolute; top: -30px; right: -30px;
    width: 120px; height: 120px;
    background: rgba(99,102,241,.2);
    border-radius: 50%;
}
.emp-header-avatar {
    width: 68px; height: 68px;
    border-radius: 14px;
    object-fit: cover;
    border: 3px solid rgba(255,255,255,.3);
    flex-shrink: 0;
    position: relative; z-index: 1;
}
.emp-header-info { position: relative; z-index: 1; }
.emp-header-info h5 { font-weight: 800; margin: 0; font-size: 1.1rem; }
.emp-header-info p  { font-size: .8rem; opacity: .75; margin: .2rem 0 0; }
.emp-header-actions { margin-left: auto; display: flex; gap: .5rem; flex-shrink: 0; position: relative; z-index: 1; }

/* ─── Salary Row ─────────────────────────────────────── */
.sal-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: .45rem 0;
    border-bottom: 1px dashed var(--card-border);
    font-size: .83rem;
}
.sal-row:last-child { border-bottom: none; }
.sal-row label { color: #6b7280; font-size: .78rem; }
.sal-row input[type=number] {
    width: 110px; text-align: right;
    border: 1px solid var(--card-border);
    border-radius: 7px; padding: .25rem .5rem;
    font-size: .83rem; font-weight: 600;
    background: var(--card-bg);
    color: inherit;
    transition: border-color .15s;
}
.sal-row input[type=number]:focus {
    outline: none; border-color: var(--pr-purple);
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
}
.sal-section-head {
    font-size: .7rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .08em;
    color: var(--pr-purple);
    padding: .8rem 0 .3rem;
}
.sal-total {
    background: #52A8FF;
    color: #fff; border-radius: 12px;
    padding: .9rem 1rem;
    display: flex; justify-content: space-between;
    align-items: center; font-weight: 800;
    font-size: 1rem; margin-top: .75rem;
}

/* ─── Attendance Summary Row ─────────────────────────── */
.att-chips {
    display: flex; flex-wrap: wrap; gap: .5rem;
}
.att-chip {
    display: flex; align-items: center; gap: .4rem;
    padding: .3rem .7rem; border-radius: 20px;
    font-size: .75rem; font-weight: 700; color: #fff;
}

/* ─── Payment History ────────────────────────────────── */
.pay-hist-item {
    display: flex; align-items: center; gap: 1rem;
    padding: .6rem 0; border-bottom: 1px dashed var(--card-border);
    font-size: .82rem;
}
.pay-hist-item:last-child { border-bottom: none; }
.pay-hist-icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    background: rgba(34,197,94,.1);
    color: var(--pr-green);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: .9rem;
}

/* ─── Buttons ────────────────────────────────────────── */
.btn-pr {
    border-radius: 10px; font-size: .8rem; font-weight: 600;
    padding: .45rem 1rem;
    transition: all .18s;
}
.btn-pr:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.15); }
.btn-pr-primary { background: #52A8FF; color:#fff; border:none; }
.btn-pr-success { background: linear-gradient(135deg,#22c55e,#16a34a); color:#fff; border:none; }
.btn-pr-danger  { background: linear-gradient(135deg,#ef4444,#dc2626); color:#fff; border:none; }
.btn-pr-outline { background: transparent; border: 1.5px solid var(--card-border); color: inherit; text-decoration: none; }
.btn-pr-outline:hover { border-color: var(--pr-purple); color: var(--pr-purple); }

/* ─── Modal ──────────────────────────────────────────── */
.modal-content { border-radius: 18px; border: 1px solid var(--card-border); }
.modal-header  { border-bottom: 1px solid var(--card-border); padding: 1.25rem 1.5rem; }
.modal-body    { padding: 1.5rem; }
.modal-footer  { border-top: 1px solid var(--card-border); padding: 1rem 1.5rem; }

/* ─── Progress Bar ───────────────────────────────────── */
.pay-progress-wrap { margin-top: .5rem; }
.pay-progress-bar  { height: 8px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
[data-bs-theme="dark"] .pay-progress-bar { background: #334155; }
.pay-progress-fill { height: 100%; border-radius: 99px; transition: width .4s ease; }

/* ─── Remaining Badge ────────────────────────────────── */
.remaining-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(239,68,68,.1); color: var(--pr-red);
    border: 1px solid rgba(239,68,68,.2);
    border-radius: 20px; font-size: .78rem; font-weight: 700;
    padding: .25rem .75rem;
}
</style>

<div class="pr-wrap">
    {{-- ──────── LEFT: Employee List ──────── --}}
    <div class="pr-list">
        {{-- Filters --}}
        <div class="filter-card">
            <h6><i class="fa-solid fa-sliders me-2 text-primary"></i>Filter Payroll</h6>
            <form method="GET" action="{{ route('admin.payroll.index') }}" id="filterForm">
                <div class="mb-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                        placeholder="🔍 Search employee…" value="{{ request('search') }}">
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-6">
                        <select name="month" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach(range(1,12) as $m)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                            @foreach(range(2024, now()->year+1) as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-2">
                    <select name="department" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d }}" {{ request('department') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending"       {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial"       {{ request('status') == 'partial' ? 'selected' : '' }}>Partial Paid</option>
                        <option value="paid"          {{ request('status') == 'paid'    ? 'selected' : '' }}>Paid</option>
                        <option value="not_generated" {{ request('status') == 'not_generated' ? 'selected' : '' }}>Not Generated</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Apply</button>
                    <a href="{{ route('admin.payroll.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </form>

            <hr style="border-color:var(--card-border);">

            {{-- Bulk Generate --}}
            <form method="POST" action="{{ route('admin.payroll.bulk-generate') }}">
                @csrf
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year"  value="{{ $year }}">
                <button type="submit" class="btn btn-pr btn-pr-primary w-100">
                    <i class="fa-solid fa-bolt me-1"></i>Bulk Generate {{ date('F', mktime(0,0,0,$month,1)) }} {{ $year }}
                </button>
            </form>
        </div>

        {{-- Employee Items --}}
        <div id="empListScroll" style="max-height:calc(100vh - 340px);overflow-y:auto;padding-right:2px;">
            @forelse($staffList as $staff)
            @php
                $sal = $salaries[$staff->user_id] ?? null;
                $statusLabel = match($sal?->status) {
                    'paid'    => ['Paid',    '#22c55e'],
                    'partial' => ['Partial', '#f97316'],
                    'pending' => ['Pending', '#6b7280'],
                    default   => ['—',       '#9ca3af'],
                };
            @endphp
            <div class="emp-item" id="emp-{{ $staff->id }}"
                onclick="loadEmployee({{ $staff->id }}, {{ $month }}, {{ $year }})">
                <img src="{{ $staff->photoUrl() }}" class="emp-avatar" alt="{{ $staff->user->name }}">
                <div class="min-w-0">
                    <p class="emp-name">{{ $staff->user->name }}</p>
                    <p class="emp-sub">{{ $staff->department ?? '—' }} • {{ $staff->designation ?? '—' }}</p>
                </div>
                <span class="emp-status-badge" style="background:{{ $statusLabel[1] }}20;color:{{ $statusLabel[1] }};border:1px solid {{ $statusLabel[1] }}40;">
                    {{ $statusLabel[0] }}
                </span>
            </div>
            @empty
            <div class="text-center text-muted py-4" style="font-size:.85rem;">No employees found.</div>
            @endforelse
        </div>
    </div>

    {{-- ──────── RIGHT: Payroll Detail ──────── --}}
    <div class="pr-detail">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 rounded-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div id="detailArea">
            <div class="detail-placeholder">
                <i class="fa-solid fa-arrow-left fa-2x"></i>
                <div>
                    <div class="fw-bold">Select an employee</div>
                    <div style="font-size:.82rem;">Click any name in the list to view payroll details</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Advance Salary Modal ──────────────────────────── --}}
<div class="modal fade" id="advanceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i>Take Advance Salary</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="advanceForm">
                    <input type="hidden" id="adv-user-id" name="user_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" name="amount" class="form-control" step="0.01" min="1" placeholder="5000" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Medical / Emergency / Personal…"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Recovery Method</label>
                            <select name="recovery_method" class="form-select" onchange="toggleInstallments(this)">
                                <option value="next_month">Next Month</option>
                                <option value="multiple_months">Multiple Months</option>
                                <option value="installments">Custom Installments</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3" id="installmentsField">
                        <label class="form-label fw-semibold">Number of Installments</label>
                        <input type="number" name="installments" class="form-control" value="1" min="1" max="24">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Approved By</label>
                        <input type="text" name="approved_by" class="form-control" placeholder="Principal / HR Manager…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="1" placeholder="Additional notes…"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-pr btn-pr-primary" onclick="submitAdvance()">
                    <i class="fa-solid fa-check me-1"></i>Submit Advance
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ─── Payment Modal ──────────────────────────────────── --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Record Payment</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm">
                    <input type="hidden" id="pay-salary-id" name="salary_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" id="pay-amount" name="amount" class="form-control" step="0.01" min="0.01" required>
                        </div>
                        <div id="pay-remaining-hint" class="form-text text-danger fw-semibold mt-1"></div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="mobile_banking">Mobile Banking</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Reference Number</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="TXN ID / Cheque No.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paid By</label>
                        <input type="text" name="paid_by" class="form-control" value="{{ auth()->user()->name }}" placeholder="Cashier / HR Manager…">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Payment notes…"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn-pr btn-pr-success" onclick="submitPayment()">
                    <i class="fa-solid fa-circle-check me-1"></i>Record Payment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const CSRF  = '{{ csrf_token() }}';
let currentStaffId = null;
let currentMonth   = {{ $month }};
let currentYear    = {{ $year }};
let currentSalary  = null;

// Base URL paths – avoids UrlGenerationException from route() with missing required params
const URL_EMPLOYEE_SHOW      = '/admin/payroll/employee/';
const URL_SALARY_STRUCTURE   = '{{ route("admin.payroll.salary-structure.update") }}';
const URL_ADVANCE_SALARY     = '{{ route("admin.payroll.advance-salary.store") }}';
const URL_PAYMENT            = '{{ route("admin.payroll.payment.store") }}';
const URL_BULK_GENERATE      = '{{ route("admin.payroll.bulk-generate") }}';
const URL_RECALCULATE_BASE   = '/admin/payroll/recalculate/';
const URL_LOCK_BASE          = '/admin/payroll/lock/';
const URL_UNLOCK_BASE        = '/admin/payroll/unlock/';
const URL_SLIP_BASE          = '/admin/payroll/slip/';

/* ── Load Employee Detail ────────────────────── */
async function loadEmployee(staffId, month, year) {
    // Highlight active
    document.querySelectorAll('.emp-item').forEach(e => e.classList.remove('active'));
    document.getElementById('emp-' + staffId)?.classList.add('active');

    currentStaffId = staffId;
    const area = document.getElementById('detailArea');
    area.innerHTML = `<div class="detail-placeholder"><div class="spinner-border text-primary"></div><div>Loading payroll data…</div></div>`;

    try {
        const resp = await fetch(`${URL_EMPLOYEE_SHOW}${staffId}?month=${month}&year=${year}`);
        const data = await resp.json();
        renderDetail(data, month, year);
    } catch (e) {
        area.innerHTML = `<div class="detail-placeholder text-danger"><i class="fa-solid fa-triangle-exclamation fa-2x"></i><div>Failed to load data. Please try again.</div></div>`;
    }
}

/* ── Render Detail Panel ─────────────────────── */
function renderDetail(data, month, year) {
    const { staff, salary, calc, advances, payments } = data;
    currentSalary = salary;
    const area = document.getElementById('detailArea');
    const fmt  = v => '৳' + parseFloat(v || 0).toLocaleString('en-BD', {minimumFractionDigits:2});
    const salId = salary?.id;

    const statusMap = {
        paid:    ['Paid',         '#22c55e'],
        partial: ['Partial Paid', '#f97316'],
        pending: ['Pending',      '#6b7280'],
        unpaid:  ['Unpaid',       '#ef4444'],
        locked:  ['Locked',       '#1e293b'],
    };
    const [statusLabel, statusColor] = statusMap[salary?.status] ?? ['Not Generated', '#9ca3af'];

    // Payment progress
    const net  = parseFloat(salary?.net_salary || calc.net_salary);
    const paid = parseFloat(salary?.paid_amount || 0);
    const pct  = net > 0 ? Math.min(100, Math.round((paid / net) * 100)) : 0;

    let html = `
    {{-- Employee Header --}}
    <div class="emp-header-card">
        <img src="${staff.photo ? '/storage/' + staff.photo : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(staff.user.name) + '&background=6366f1&color=fff&size=80'}" class="emp-header-avatar" alt="${staff.user.name}">
        <div class="emp-header-info">
            <h5>${staff.user.name}</h5>
            <p>EMP-${String(staff.id).padStart(4,'0')} • ${staff.designation || '—'} • ${staff.department || '—'}</p>
            <p>Monthly: <strong>${fmt(staff.salary)}</strong></p>
        </div>
        <div class="emp-header-actions">
            ${salId ? `
            <a href="${URL_SLIP_BASE}${salId}" target="_blank" class="btn-pr btn-pr-outline" style="color:#fff;border-color:rgba(255,255,255,.3);">
                <i class="fa-solid fa-file-pdf me-1"></i>Salary Slip
            </a>
            ` : ''}
            <button class="btn-pr btn-pr-outline" style="color:#fff;border-color:rgba(255,255,255,.3);"
                onclick="openAdvanceModal(${staff.user_id})">
                <i class="fa-solid fa-hand-holding-dollar me-1"></i>Advance
            </button>

            ${salId ? `
            <button class="btn-pr ${salary?.is_locked ? 'btn-pr-danger' : 'btn-pr-success'}" style="color:#fff;border-color:rgba(255,255,255,.3);"
                onclick="${salary?.is_locked ? 'unlockPayroll' : 'lockPayroll'}(${salId})">
                <i class="fa-solid fa-${salary?.is_locked ? 'lock' : 'lock-open'} me-1"></i>${salary?.is_locked ? 'Locked' : 'Unlocked'}
            </button>
            ` : ''}
        </div>
    </div>

    {{-- Two Columns: Salary Structure + Attendance --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="pr-card">
                <div class="pr-card-header">
                    <span><i class="fa-solid fa-coins me-2 text-warning"></i>Salary Structure</span>
                    <span style="background:${statusColor}20;color:${statusColor};border:1px solid ${statusColor}40;font-size:.72rem;font-weight:700;padding:.2rem .6rem;border-radius:20px;">${statusLabel}</span>
                </div>
                <div class="pr-card-body">
                    <form id="salStructureForm" onsubmit="saveSalaryStructure(event)">
                        ${salId ? `<input type="hidden" name="salary_id" value="${salId}">` : ''}
                        <div class="sal-section-head">Allowances</div>
                        ${salRow('Basic Salary', 'basic_salary', salary?.basic_salary ?? calc.basic_salary)}
                        ${(function(){
                            let html = '';
                            let renderedKeys = [];

                            // 1. Render specific profile allowances
                            let customCounter = 0;
                            if (staff && staff.allowances && staff.allowances.length > 0) {
                                staff.allowances.forEach(a => {
                                    let n = a.name.toLowerCase();
                                    let key = 'other_allowances';
                                    if (n.includes('house')) key = 'house_allowance';
                                    else if (n.includes('medical')) key = 'medical_allowance';
                                    else if (n.includes('transport')) key = 'transport_allowance';
                                    else if (n.includes('food')) key = 'food_allowance';
                                    else if (n.includes('mobile')) key = 'mobile_allowance';
                                    else if (n.includes('internet')) key = 'internet_allowance';
                                    else if (n.includes('special')) key = 'special_allowance';
                                    else if (n.includes('festival')) key = 'festival_allowance';

                                    if (key === 'other_allowances') {
                                        // This is a truly custom allowance not standard mapped
                                        // We use a dummy name so it doesn't overwrite 'other_allowances'
                                        customCounter++;
                                        let dummyKey = 'custom_other_' + customCounter;
                                        html += salRow(a.name, dummyKey, a.amount);
                                    } else if (!renderedKeys.includes(key)) {
                                        let val = (salary && parseFloat(salary[key] || 0) > 0) ? salary[key] : a.amount;
                                        html += salRow(a.name, key, val);
                                        renderedKeys.push(key);
                                    }
                                });
                            }

                            // 2. Render any remaining standard allowances ONLY if they have a > 0 value
                            const stdAllowances = [
                                { key: 'house_allowance', name: 'House Allowance' },
                                { key: 'medical_allowance', name: 'Medical Allowance' },
                                { key: 'transport_allowance', name: 'Transport Allowance' },
                                { key: 'food_allowance', name: 'Food Allowance' },
                                { key: 'mobile_allowance', name: 'Mobile Allowance' },
                                { key: 'internet_allowance', name: 'Internet Allowance' },
                                { key: 'special_allowance', name: 'Special Allowance' },
                                { key: 'festival_allowance', name: 'Festival Allowance' }
                            ];

                            stdAllowances.forEach(std => {
                                if (!renderedKeys.includes(std.key)) {
                                    let val = salary ? parseFloat(salary[std.key] || 0) : parseFloat(calc[std.key] || 0);
                                    if (val > 0) html += salRow(std.name, std.key, val);
                                }
                            });

                            // 3. Only show extra earnings if they are > 0 to keep the UI strictly limited to profile allowances
                            let otherVal = salary ? parseFloat(salary.other_allowances || 0) : parseFloat(calc.other_allowances || 0);
                            if (otherVal > 0) html += salRow('Other Allowances', 'other_allowances', otherVal);

                            let bonusVal = salary ? parseFloat(salary.bonus || 0) : parseFloat(calc.bonus || 0);
                            if (bonusVal > 0) html += salRow('Bonus', 'bonus', bonusVal);

                            let otVal = salary ? parseFloat(salary.overtime || 0) : parseFloat(calc.overtime || 0);
                            if (otVal > 0) html += salRow('Overtime', 'overtime', otVal);

                            return html;
                        })()}

                        <div class="sal-section-head">Deductions</div>
                        ${salRow('Absent Deduction', 'absent_deduction', salary?.absent_deduction ?? calc.absent_deduction, true)}
                        ${salRow('Late Deduction', 'late_deduction', salary?.late_deduction ?? calc.late_deduction, true)}
                        ${salRow('Loan Deduction', 'loan_deduction', salary?.loan_deduction ?? 0, true)}
                        ${salRow('Other Deduction', 'other_deduction', salary?.other_deduction ?? 0, true)}
                        ${salRow('Tax', 'tax', salary?.tax ?? 0, true)}
                        ${salRow('Provident Fund', 'provident_fund', salary?.provident_fund ?? 0, true)}

                        ${advances?.length ? `
                        <div class="sal-row">
                            <label class="text-danger">Advance Deduction</label>
                            <span class="text-danger fw-bold">−${fmt(salary?.advance_deduction ?? calc.advance_deduction)}</span>
                        </div>` : ''}

                        <div class="sal-total" id="netSalaryDisplay">
                            <span><i class="fa-solid fa-building-columns me-2"></i>Net Salary</span>
                            <span>${fmt(salary?.net_salary ?? calc.net_salary)}</span>
                        </div>

                        ${!salary?.is_locked ? `
                        <div class="mt-3 d-flex gap-2">
                            ${!salId ? `
                            <button type="button" class="btn-pr btn-pr-primary" onclick="generateForEmployee()">
                                <i class="fa-solid fa-bolt me-1"></i>Generate Payroll
                            </button>` : `
                            <button type="submit" class="btn-pr btn-pr-primary">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Save Structure
                            </button>`}
                        </div>` : '<div class="text-warning text-center mt-2" style="font-size:.8rem;"><i class="fa-solid fa-lock me-1"></i>Payroll is locked</div>'}
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Attendance Summary --}}
            <div class="pr-card mb-3">
                <div class="pr-card-header"><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Attendance Summary</div>
                <div class="pr-card-body">
                    <div class="att-chips mb-3">
                        <div class="att-chip" style="background:#22c55e;">${calc.present_days} Present</div>
                        <div class="att-chip" style="background:#ef4444;">${calc.absent_days} Absent</div>
                        <div class="att-chip" style="background:#f97316;">${calc.late_days} Late</div>
                        <div class="att-chip" style="background:#3b82f6;">${calc.leave_days} Leave</div>
                        <div class="att-chip" style="background:#a855f7;">${calc.half_days} Half Day</div>
                        <div class="att-chip" style="background:#1e293b;">${calc.working_days} Working Days</div>
                    </div>
                    <div class="sal-row">
                        <label>Per Day Salary</label>
                        <span class="fw-bold text-primary">${fmt(calc.per_day_salary)}</span>
                    </div>
                    <div class="sal-row">
                        <label>Gross Salary</label>
                        <span class="fw-bold text-success">${fmt(calc.gross_salary)}</span>
                    </div>
                    <div class="sal-row">
                        <label>Total Deductions</label>
                        <span class="fw-bold text-danger">−${fmt((calc.absent_deduction||0) + (calc.late_deduction||0) + (calc.advance_deduction||0))}</span>
                    </div>
                </div>
            </div>

            {{-- Advance Salary --}}
            ${advances?.length ? `
            <div class="pr-card mb-3">
                <div class="pr-card-header"><i class="fa-solid fa-hand-holding-dollar me-2 text-warning"></i>Advance Salary</div>
                <div class="pr-card-body">
                    ${advances.map(a => `
                    <div class="sal-row">
                        <div>
                            <div style="font-size:.82rem;font-weight:600;">${fmt(a.amount)} advance</div>
                            <div style="font-size:.73rem;color:#9ca3af;">${a.recovery_method.replace('_',' ')} • ${a.installments} installment(s)</div>
                        </div>
                        <div class="text-end">
                            <div class="text-danger fw-bold" style="font-size:.82rem;">−${fmt(a.monthly_deduction)}/mo</div>
                            <div style="font-size:.72rem;color:#9ca3af;">Remaining: ${fmt(a.amount - a.recovered_amount)}</div>
                        </div>
                    </div>`).join('')}
                </div>
            </div>` : ''}
        </div>
    </div>

    {{-- Payment Section --}}
    <div class="pr-card">
        <div class="pr-card-header">
            <span><i class="fa-solid fa-money-bill-wave me-2 text-success"></i>Payment</span>
            ${salId && salary?.status !== 'paid' && !salary?.is_locked ? `
            <button class="btn-pr btn-pr-success btn-sm" onclick="openPaymentModal(${salId}, ${net - paid})">
                <i class="fa-solid fa-plus me-1"></i>Record Payment
            </button>` : ''}
        </div>
        <div class="pr-card-body">
            ${salId ? `
            <div class="row g-3 mb-3">
                <div class="col-md-4 text-center">
                    <div style="font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">Net Salary</div>
                    <div class="fw-bold text-primary" style="font-size:1.1rem;">${fmt(net)}</div>
                </div>
                <div class="col-md-4 text-center">
                    <div style="font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">Paid</div>
                    <div class="fw-bold text-success" style="font-size:1.1rem;">${fmt(paid)}</div>
                </div>
                <div class="col-md-4 text-center">
                    <div style="font-size:.75rem;color:#9ca3af;margin-bottom:.25rem;">Remaining</div>
                    <div class="fw-bold text-danger" style="font-size:1.1rem;">${fmt(net - paid)}</div>
                </div>
            </div>
            <div class="pay-progress-wrap mb-3">
                <div class="pay-progress-bar">
                    <div class="pay-progress-fill" style="width:${pct}%;background:${pct>=100?'#22c55e':'#6366f1'};"></div>
                </div>
                <div style="font-size:.72rem;color:#9ca3af;text-align:right;margin-top:.25rem;">${pct}% paid</div>
            </div>

            ${payments?.length ? `
            <div style="font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:.5rem;">Payment History</div>
            ${payments.map(p => `
            <div class="pay-hist-item">
                <div class="pay-hist-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="flex-fill">
                    <div style="font-weight:600;">${fmt(p.amount)}</div>
                    <div style="font-size:.72rem;color:#9ca3af;">${p.payment_method.replace('_',' ')} • ${p.payment_date} ${p.reference_number ? '• #'+p.reference_number : ''}</div>
                </div>
                <div style="font-size:.72rem;color:#9ca3af;text-align:right;">
                    Paid by: ${p.paid_by || '—'}<br>${p.notes || ''}
                </div>
            </div>`).join('')}
            ` : '<div style="font-size:.82rem;color:#9ca3af;text-align:center;padding:1rem;">No payments recorded yet.</div>'}
            ` : `
            <div style="font-size:.82rem;color:#9ca3af;text-align:center;padding:2rem;">
                <i class="fa-solid fa-triangle-exclamation fa-2x d-block mb-2 text-warning"></i>
                Payroll not generated for this month. Click "Generate Payroll" above.
            </div>`}
        </div>
    </div>
    `;

    area.innerHTML = html;
}

/* ── Salary Row Helper ───────────────────────── */
function salRow(label, name, value, isDeduction = false) {
    const v = parseFloat(value || 0).toFixed(2);
    const colorClass = isDeduction ? 'style="color:var(--pr-red);"' : 'style="color:var(--pr-green);"';
    return `<div class="sal-row">
        <label>${isDeduction ? '−' : '+'} ${label}</label>
        <input type="number" name="${name}" value="${v}" step="0.01" min="0" oninput="liveNetCalc()" ${colorClass}>
    </div>`;
}

/* ── Live Net Calculation ────────────────────── */
function liveNetCalc() {
    const f   = document.getElementById('salStructureForm');
    if (!f) return;
    const fd  = new FormData(f);
    const earnings = ['basic_salary','house_allowance','medical_allowance','transport_allowance','food_allowance','mobile_allowance','internet_allowance','special_allowance','festival_allowance','other_allowances','bonus','overtime'];
    const deducts  = ['absent_deduction','late_deduction','loan_deduction','other_deduction','tax','provident_fund'];
    let gross = 0, deduction = 0;
    earnings.forEach(k => { gross     += parseFloat(fd.get(k) || 0); });
    deducts.forEach(k  => { deduction += parseFloat(fd.get(k) || 0); });

    // Sum dynamically added custom profile allowances
    f.querySelectorAll('input[name^="custom_other_"]').forEach(inp => {
        gross += parseFloat(inp.value || 0);
    });

    const net = Math.max(0, gross - deduction);
    const el  = document.getElementById('netSalaryDisplay');
    if (el) el.querySelector('span:last-child').textContent = '৳' + net.toLocaleString('en-BD', {minimumFractionDigits:2});
}

/* ── Save Salary Structure ───────────────────── */
async function saveSalaryStructure(e) {
    e.preventDefault();
    const f    = document.getElementById('salStructureForm');
    const fd   = new FormData(f);

    // Combine all dynamic custom profile allowances into the DB's `other_allowances` column
    let customSum = 0;
    f.querySelectorAll('input[name^="custom_other_"]').forEach(inp => {
        customSum += parseFloat(inp.value || 0);
    });
    let currentOther = parseFloat(fd.get('other_allowances') || 0);
    fd.set('other_allowances', customSum + currentOther);

    fd.append('_token', CSRF);
    try {
        const resp = await fetch(URL_SALARY_STRUCTURE, {method:'POST', body:fd});
        const data = await resp.json();
        if (data.success) {
            showToast('Salary structure saved!', 'success');
            loadEmployee(currentStaffId, currentMonth, currentYear);
        } else {
            showToast(data.error || 'Failed to save.', 'danger');
        }
    } catch (err) {
        showToast('Network error.', 'danger');
    }
}

/* ── Generate For Single Employee ───────────── */
async function generateForEmployee() {
    const resp = await fetch(URL_BULK_GENERATE, {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({month: currentMonth, year: currentYear}),
    });
    const data = await resp.json();
    if (data.success) {
        showToast('Payroll generated!', 'success');
        loadEmployee(currentStaffId, currentMonth, currentYear);
    }
}

/* ── Recalculate ─────────────────────────────── */
async function recalculate(salaryId) {
    if (!confirm('Recalculate payroll from attendance data?')) return;
    const resp = await fetch(`${URL_RECALCULATE_BASE}${salaryId}`, {
        method:'POST', headers:{'X-CSRF-TOKEN':CSRF}
    });
    const data = await resp.json();
    if (data.success) { showToast('Recalculated!','success'); loadEmployee(currentStaffId, currentMonth, currentYear); }
    else showToast(data.error,'danger');
}

/* ── Lock / Unlock ───────────────────────────── */
async function lockPayroll(salaryId) {
    if (!confirm('Lock this payroll? No further edits will be allowed.')) return;
    await fetch(`${URL_LOCK_BASE}${salaryId}`, {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
    showToast('Payroll locked.', 'success');
    loadEmployee(currentStaffId, currentMonth, currentYear);
}
async function unlockPayroll(salaryId) {
    await fetch(`${URL_UNLOCK_BASE}${salaryId}`, {method:'POST',headers:{'X-CSRF-TOKEN':CSRF}});
    showToast('Payroll unlocked.', 'success');
    loadEmployee(currentStaffId, currentMonth, currentYear);
}

/* ── Advance Modal ───────────────────────────── */
function openAdvanceModal(userId) {
    document.getElementById('adv-user-id').value = userId;
    new bootstrap.Modal(document.getElementById('advanceModal')).show();
}
function toggleInstallments(sel) {
    document.getElementById('installmentsField').style.display = sel.value === 'next_month' ? 'none' : 'block';
    if (sel.value === 'next_month') document.querySelector('[name=installments]').value = 1;
}
async function submitAdvance() {
    const fd = new FormData(document.getElementById('advanceForm'));
    fd.append('_token', CSRF);
    const resp = await fetch('{{ route("admin.payroll.advance-salary.store") }}', {method:'POST', body:fd});
    const data = await resp.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('advanceModal')).hide();
        showToast('Advance salary recorded!', 'success');
        loadEmployee(currentStaffId, currentMonth, currentYear);
    } else { showToast(data.error || 'Failed.', 'danger'); }
}

/* ── Payment Modal ───────────────────────────── */
function openPaymentModal(salaryId, remaining) {
    document.getElementById('pay-salary-id').value = salaryId;
    document.getElementById('pay-amount').value    = parseFloat(remaining).toFixed(2);
    document.getElementById('pay-remaining-hint').textContent = 'Remaining: ৳' + parseFloat(remaining).toFixed(2);
    new bootstrap.Modal(document.getElementById('paymentModal')).show();
}
async function submitPayment() {
    const fd = new FormData(document.getElementById('paymentForm'));
    fd.append('_token', CSRF);
    const resp = await fetch('{{ route("admin.payroll.payment.store") }}', {method:'POST', body:fd});
    const data = await resp.json();
    if (data.success) {
        bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
        showToast('Payment recorded!', 'success');
        loadEmployee(currentStaffId, currentMonth, currentYear);
        // Refresh list item badge
        refreshListBadge(data.salary);
    } else { showToast(data.error || 'Failed.', 'danger'); }
}

/* ── Refresh List Badge ──────────────────────── */
function refreshListBadge(salary) {
    const badge  = document.querySelector(`#emp-${currentStaffId} .emp-status-badge`);
    const colors = {paid:'#22c55e',partial:'#f97316',pending:'#6b7280'};
    const labels = {paid:'Paid',partial:'Partial',pending:'Pending'};
    if (badge && salary) {
        const c = colors[salary.status] || '#9ca3af';
        badge.style.background = c + '20';
        badge.style.color      = c;
        badge.style.borderColor= c + '40';
        badge.textContent      = labels[salary.status] || '—';
    }
}

/* ── Toast ───────────────────────────────────── */
function showToast(msg, type = 'success') {
    let el = document.getElementById('pr-toast');
    if (!el) {
        el = document.createElement('div');
        el.id = 'pr-toast';
        el.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;';
        document.body.appendChild(el);
    }
    el.innerHTML = `<div class="alert alert-${type} shadow-lg" style="min-width:220px;border-radius:12px;font-size:.85rem;">
        <i class="fa-solid fa-${type==='success'?'check-circle':'triangle-exclamation'} me-2"></i>${msg}
    </div>`;
    setTimeout(() => { el.innerHTML = ''; }, 3500);
}
</script>
@endsection
