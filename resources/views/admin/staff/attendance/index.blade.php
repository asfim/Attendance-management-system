@extends('layouts.app')

@section('title', 'Staff Attendance')

@section('content')
<!-- Flatpickr CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
/* ─── Variables ──────────────────────────────────────────────── */
:root {
    --att-present:  #22c55e;
    --att-absent:   #ef4444;
    --att-late:     #f97316;
    --att-half:     #a855f7;
    --att-leave:    #3b82f6;
    --att-holiday:  #6b7280;
    --att-wfh:      #06b6d4;
    --card-bg:      #ffffff;
    --card-border:  #e2e8f0;
    --sidebar-w:    320px;
}
[data-bs-theme="dark"] {
    --card-bg:      #1e293b;
    --card-border:  #334155;
}

/* ─── Layout ─────────────────────────────────────────────────── */
.sa-layout {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
}
.sa-main  { flex: 1; min-width: 0; }
.sa-sidebar {
    width: var(--sidebar-w);
    flex-shrink: 0;
    position: sticky;
    top: 80px;
}

/* ─── Header Controls ────────────────────────────────────────── */
.sa-header {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.sa-header h5 { margin: 0; font-weight: 700; }

/* ─── Staff Card ──────────────────────────────────────────────── */
.staff-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    margin-bottom: 1rem;
    overflow: hidden;
    transition: box-shadow .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.05);
}
.staff-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.1); }

.staff-card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    cursor: pointer;
    user-select: none;
    transition: background .15s;
}
.staff-card-header:hover { background: hsla(var(--primary-hsl), 0.08); }

.staff-avatar {
    width: 52px; height: 52px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    border: 2px solid var(--card-border);
}

.staff-info { flex: 1; min-width: 0; }
.staff-name  { font-weight: 700; font-size: .95rem; margin: 0; }
.staff-meta  { font-size: .78rem; color: #6b7280; margin: 0; }
.staff-meta span { margin-right: .75rem; }

.staff-badges { display: flex; align-items: center; gap: .5rem; flex-shrink: 0; }
.expand-chevron {
    color: #9ca3af;
    transition: transform .25s ease;
    margin-left: .25rem;
}
.staff-card.expanded .expand-chevron { transform: rotate(180deg); }

/* ─── Collapse Panel ──────────────────────────────────────────── */
.staff-body { display: none; border-top: 1px solid var(--card-border); }
.staff-card.expanded .staff-body { display: block; }

/* ─── Attendance Summary Bar ─────────────────────────────────── */
.att-summary {
    display: flex;
    gap: .5rem;
    padding: 1rem 1.25rem;
    flex-wrap: wrap;
    background: rgba(0,0,0,.015);
    border-bottom: 1px solid var(--card-border);
}
[data-bs-theme="dark"] .att-summary { background: rgba(255,255,255,.03); }
.att-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 56px;
    padding: .35rem .6rem;
    border-radius: 10px;
    font-size: .75rem;
    font-weight: 600;
    color: #fff;
}
.att-stat span { font-size: 1.1rem; font-weight: 800; line-height: 1.2; }
.att-stat.present  { background: var(--att-present); }
.att-stat.absent   { background: var(--att-absent); }
.att-stat.late     { background: var(--att-late); }
.att-stat.leave    { background: var(--att-leave); }
.att-stat.half     { background: var(--att-half); }
.att-stat.holiday  { background: var(--att-holiday); }
.att-stat.working  { background: #1e293b; }
[data-bs-theme="dark"] .att-stat.working { background: #475569; }
.att-stat.pct      { background: var(--primary-bg); }

/* ─── Calendar ──────────────────────────────────────────────────*/
.att-calendar-wrap { padding: 1.25rem; }
.cal-grid { width: 100%; border-collapse: collapse; }
.cal-grid th {
    text-align: center;
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #9ca3af;
    padding: .35rem 0;
    border-bottom: 1px solid var(--card-border);
}
.cal-cell {
    text-align: center;
    padding: .2rem;
    vertical-align: top;
}
.cal-day {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 38px; height: 38px;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
    border: 2px solid transparent;
    color: inherit;
    position: relative;
}
.cal-day:hover:not(.out-month):not(.future) {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    transform: scale(1.1);
}
.cal-day.out-month { opacity: .25; cursor: default; }
.cal-day.future    { opacity: .4; cursor: not-allowed; }
.cal-day.today     { border-color: #6366f1 !important; }
.cal-day.attended  { color: #fff; }

/* ─── Day Form (inline dropdown) ──────────────────────────────── */
.day-form-wrap {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 14px;
    padding: 1rem;
    margin: .5rem 0;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    animation: slideDown .2s ease;
}
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.day-form-wrap .form-label { font-size: .78rem; font-weight: 600; color: #6b7280; }
.day-form-wrap .form-control,
.day-form-wrap .form-select {
    font-size: .85rem;
    border-radius: 8px;
    border: 1px solid var(--card-border);
}

/* ─── Salary Deduction Box ─────────────────────────────────────── */
.deduction-box {
    background: linear-gradient(135deg, rgba(239,68,68,.1), rgba(239,68,68,.05));
    border: 1px solid rgba(239,68,68,.2);
    border-radius: 10px;
    padding: .75rem 1rem;
    font-size: .82rem;
}
.deduction-box strong { color: #ef4444; font-size: 1.05rem; }

/* ─── Save Button ────────────────────────────────────────────── */
.btn-save-att {
    background: var(--primary-bg);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: .5rem 1.25rem;
    font-size: .85rem;
    font-weight: 600;
    transition: all .2s;
}
.btn-save-att:hover {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59,130,246,.3);
}
.btn-save-att:disabled { opacity: .6; transform: none; }

/* ─── Payroll Sidebar ────────────────────────────────────────── */
.payroll-sidebar-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
.sidebar-header {
    background: linear-gradient(135deg, #1e293b, #334155);
    padding: 1rem 1.25rem;
    color: #fff;
}
[data-bs-theme="dark"] .sidebar-header { background: linear-gradient(135deg,#0f172a,#1e293b); }
.sidebar-header h6 { margin: 0; font-weight: 700; font-size: .9rem; letter-spacing: .03em; }
.sidebar-body { padding: .75rem 1.25rem; }

.payroll-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: .4rem 0;
    font-size: .82rem;
    border-bottom: 1px dashed var(--card-border);
}
.payroll-row:last-child { border-bottom: none; }
.payroll-row.section-head {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #9ca3af;
    border-bottom: 1px solid var(--card-border);
    padding-top: .75rem;
    margin-top: .25rem;
}
.payroll-row .val-plus  { color: #22c55e; font-weight: 700; }
.payroll-row .val-minus { color: #ef4444; font-weight: 700; }
.payroll-net {
    background: #52A8FF;
    color: #fff;
    border-radius: 10px;
    padding: .75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 700;
    font-size: .9rem;
    margin: .75rem 0;
}
.payroll-empty {
    text-align: center;
    color: #9ca3af;
    font-size: .82rem;
    padding: 2rem;
}

/* ─── Month Picker ────────────────────────────────────────────── */
.month-nav { display: flex; align-items: center; gap: .5rem; }
.month-nav select { border-radius: 8px; font-size: .85rem; }
</style>

<div class="sa-layout">
    {{-- ──────────────────────────── LEFT: Main Content ────────────────────────────── --}}
    <div class="sa-main">

        {{-- Header --}}
        <div class="sa-header">
            <h5><i class="fa-solid fa-calendar-check me-2 text-primary"></i>Staff Attendance</h5>
            <div class="month-nav ms-auto" style="display: flex; gap: 0.5rem; align-items: center;">
                <select id="shiftSel" class="form-select form-select-sm" onchange="reloadMonth()">
                    <option value="">All Shifts</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                    @endforeach
                </select>
                <select id="monthSel" class="form-select form-select-sm" onchange="reloadMonth()">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
                <select id="yearSel" class="form-select form-select-sm" onchange="reloadMonth()">
                    @foreach(range(2024, now()->year+1) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm" onclick="reloadMonth()">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <a href="{{ route('admin.attendance.biometric-logs', ['type' => 'staff']) }}" class="btn btn-outline-primary btn-sm ms-2">
                    <i class="bi bi-fingerprint me-1"></i> Staff Biometric Logs
                </a>
            </div>
        </div>

        <!-- Staff Cards -->
        @forelse($staffMembers as $staff)
        <div class="staff-card" id="card-{{ $staff->id }}" data-staff="{{ $staff->id }}">
            {{-- Card Header --}}
            <div class="staff-card-header" onclick="toggleCard({{ $staff->id }})">
                <img src="{{ $staff->photoUrl() }}" alt="{{ $staff->user->name }}" class="staff-avatar">
                <div class="staff-info">
                    <p class="staff-name">{{ $staff->user->name }}</p>
                    <p class="staff-meta">
                        <span><i class="fa-solid fa-id-badge me-1"></i>{{ $staff->employeeId() }}</span>
                        <span><i class="fa-solid fa-briefcase me-1"></i>{{ $staff->designation ?? '—' }}</span>
                        <span><i class="fa-solid fa-building me-1"></i>{{ $staff->department ?? '—' }}</span>
                    </p>
                </div>
                <div class="staff-badges">
                    <div class="text-end me-2" style="font-size:.78rem;">
                        <div class="fw-bold text-primary">৳{{ number_format($staff->salary, 0) }}</div>
                        <div class="text-muted">Monthly</div>
                    </div>
                    <span class="badge {{ $staff->status == 'active' ? 'bg-success' : 'bg-danger' }} bg-opacity-10
                        text-{{ $staff->status == 'active' ? 'success' : 'danger' }} border border-{{ $staff->status == 'active' ? 'success' : 'danger' }} border-opacity-25">
                        {{ ucfirst($staff->status) }}
                    </span>
                    <i class="fa-solid fa-chevron-down expand-chevron"></i>
                </div>
            </div>

            {{-- Expandable Body --}}
            <div class="staff-body" id="body-{{ $staff->id }}">
                {{-- Summary Bar (loaded via AJAX) --}}
                <div class="att-summary" id="summary-{{ $staff->id }}">
                    <div class="text-muted" style="font-size:.8rem;padding:.5rem;">Loading attendance summary…</div>
                </div>

                {{-- Calendar (loaded via AJAX) --}}
                <div class="att-calendar-wrap">
                    <div id="calendar-{{ $staff->id }}">
                        <div class="text-center text-muted py-4">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <div class="mt-2" style="font-size:.8rem;">Loading calendar…</div>
                        </div>
                    </div>
                    {{-- Day form container --}}
                    <div id="dayform-{{ $staff->id }}"></div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center text-muted py-5">
            <i class="fa-solid fa-users-slash fs-2 mb-3 d-block"></i>
            No active staff members found.
        </div>
        @endforelse

    </div>

    {{-- ──────────────────────────── RIGHT: Payroll Sidebar ──────────────────────── --}}
    <div class="sa-sidebar">
        <div class="payroll-sidebar-card">
            <div class="sidebar-header">
                <h6><i class="fa-solid fa-wallet me-2"></i>Payroll Summary</h6>
                <div style="font-size:.75rem;opacity:.7;margin-top:.2rem;" id="sidebar-name">Select a staff member</div>
            </div>
            <div id="sidebar-content">
                <div class="payroll-empty">
                    <i class="fa-solid fa-hand-pointer fa-2x mb-2 d-block"></i>
                    Click on a staff card to view the payroll summary
                </div>
            </div>
        </div>

        {{-- Legend --}}
        <div class="mt-3 p-3" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:14px;">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#9ca3af;margin-bottom:.6rem;">Legend</div>
            @foreach([
                ['color'=>'#22c55e','label'=>'Present'],
                ['color'=>'#ef4444','label'=>'Absent'],
                ['color'=>'#f97316','label'=>'Late'],
                ['color'=>'#a855f7','label'=>'Half Day'],
                ['color'=>'#3b82f6','label'=>'Leave'],
                ['color'=>'#6b7280','label'=>'Holiday'],
                ['color'=>'#06b6d4','label'=>'Work From Home'],
            ] as $item)
            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem;font-size:.78rem;">
                <div style="width:12px;height:12px;border-radius:3px;background:{{ $item['color'] }};flex-shrink:0;"></div>
                {{ $item['label'] }}
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
const MONTH = {{ $month }};
const YEAR  = {{ $year }};
const CSRF  = '{{ csrf_token() }}';
const CAN_MARK_ATTENDANCE = {{ (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_staff_attendance')) ? 'true' : 'false' }};

// Base URL paths (avoids Laravel route() with missing required params at render time)
const URL_CALENDAR        = '/admin/staff-attendance/calendar/';
const URL_GET_DAY         = '/admin/staff-attendance/day/';
const URL_SAVE_DAY        = '{{ route("admin.staff-attendance.save-day") }}';
const URL_PAYROLL_SIDEBAR = '/admin/staff-attendance/payroll-sidebar/';
const URL_INDEX           = '{{ route("admin.staff-attendance.index") }}';

let activeSidebar = null;
let activeCard    = null;
let openDayForm   = null; // {staffId, date}

/* ── Month Navigation ────────────────────────────────── */
function reloadMonth() {
    const m = document.getElementById('monthSel').value;
    const y = document.getElementById('yearSel').value;
    const s = document.getElementById('shiftSel').value;
    window.location.href = `${URL_INDEX}?month=${m}&year=${y}&shift_id=${s}`;
}

/* ── Card Toggle ─────────────────────────────────────── */
function toggleCard(staffId) {
    const card  = document.getElementById('card-' + staffId);
    const isExp = card.classList.contains('expanded');

    // Collapse all
    document.querySelectorAll('.staff-card.expanded').forEach(c => c.classList.remove('expanded'));
    // Close any open day form
    openDayForm = null;

    if (!isExp) {
        card.classList.add('expanded');
        loadCalendar(staffId);
        loadSidebar(staffId);
        activeCard = staffId;
    } else {
        activeCard = null;
    }
}

/* ── Load Calendar ───────────────────────────────────── */
function loadCalendar(staffId) {
    const container = document.getElementById('calendar-' + staffId);
    container.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;

    fetch(`${URL_CALENDAR}${staffId}?month=${MONTH}&year=${YEAR}`)
        .then(r => r.json())
        .then(data => {
            renderCalendar(staffId, data);
            renderSummary(staffId, data.summary);
        })
        .catch(() => {
            container.innerHTML = `<div class="text-danger text-center py-3">Failed to load calendar.</div>`;
        });
}

/* ── Render Calendar Grid ────────────────────────────── */
function renderCalendar(staffId, data) {
    const container = document.getElementById('calendar-' + staffId);
    const days      = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];

    let html = `<table class="cal-grid"><thead><tr>`;
    days.forEach(d => { html += `<th>${d}</th>`; });
    html += `</tr></thead><tbody>`;

    data.weeks.forEach(week => {
        html += `<tr>`;
        week.forEach(cell => {
            const classes = ['cal-day'];
            if (!cell.in_month) classes.push('out-month');
            if (cell.is_today)  classes.push('today');
            if (cell.is_future) classes.push('future');
            if (cell.status)    classes.push('attended');

            const bgStyle = cell.color ? `background:${cell.color};` : '';
            const clickFn = cell.in_month && !cell.is_future
                ? `onclick="openDayFormFn(${staffId},'${cell.date}',this)"`
                : '';

            const titleText = cell.holiday_name ? cell.holiday_name : (cell.status || 'Not recorded');

            html += `<td class="cal-cell">
                <div class="${classes.join(' ')}" style="${bgStyle}" data-date="${cell.date}" data-staff="${staffId}" ${clickFn} title="${titleText}">
                    ${cell.day}
                </div>
            </td>`;
        });
        html += `</tr>`;
    });

    html += `</tbody></table>`;
    container.innerHTML = html;

    // Clear day form
    document.getElementById('dayform-' + staffId).innerHTML = '';
}

/* ── Render Summary Bar ──────────────────────────────── */
function renderSummary(staffId, s) {
    const el = document.getElementById('summary-' + staffId);
    el.innerHTML = `
        <div class="att-stat present"><span>${s.present}</span>Present</div>
        <div class="att-stat absent"><span>${s.absent}</span>Absent</div>
        <div class="att-stat late"><span>${s.late}</span>Late</div>
        <div class="att-stat leave"><span>${s.leave}</span>Leave</div>
        <div class="att-stat half"><span>${s.half_day}</span>Half Day</div>
        <div class="att-stat holiday"><span>${s.holiday}</span>Holiday</div>
        <div class="att-stat working"><span>${s.working_days}</span>Working</div>
        <div class="att-stat pct"><span>${s.percentage}%</span>Attendance</div>
    `;
}

/* ── Open Day Form ───────────────────────────────────── */
function openDayFormFn(staffId, date, dayEl) {
    const formWrap = document.getElementById('dayform-' + staffId);

    // If same date clicked again, toggle close
    if (openDayForm && openDayForm.staffId === staffId && openDayForm.date === date) {
        formWrap.innerHTML = '';
        openDayForm = null;
        return;
    }
    openDayForm = { staffId, date };

    formWrap.innerHTML = `<div class="text-center py-2"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;

    fetch(`${URL_GET_DAY}${staffId}/${date}`)
        .then(r => r.json())
        .then(data => {
            renderDayForm(staffId, date, data, formWrap);
        });
}

/* ── Render Day Form ─────────────────────────────────── */
function renderDayForm(staffId, date, data, container) {
    const att = data.attendance;
    const per = data.per_day_salary;

    const statuses = [
        {v:'present',       l:'Present'},
        {v:'absent',        l:'Absent'},
        {v:'late',          l:'Late'},
        {v:'half_day',      l:'Half Day'},
        {v:'leave',         l:'Leave'},
        {v:'holiday',       l:'Holiday'},
        {v:'work_from_home',l:'Work From Home'},
    ];

    let statusOpts = statuses.map(s =>
        `<option value="${s.v}" ${att?.status === s.v ? 'selected' : ''}>${s.l}</option>`
    ).join('');

    const displayDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {weekday:'long', year:'numeric', month:'long', day:'numeric'});
    const disableAttr = CAN_MARK_ATTENDANCE ? '' : 'disabled';

    let holidayAlert = '';
    if (data.is_holiday) {
        holidayAlert = `
            <div class="alert alert-info d-flex align-items-center py-2 mb-3 border-0 bg-info bg-opacity-10" style="border-radius:10px;">
                <i class="fa-solid fa-umbrella-beach text-white fs-5 me-2"></i>
                <div>
                    <div style="font-size:0.75rem; font-weight:700; text-transform:uppercase; color:white; margin-bottom:2px;">Holiday / Day Off</div>
                    <div style="font-size:0.9rem; font-weight:600; color:white;">${data.holiday_name}</div>
                </div>
            </div>
        `;
    }

    container.innerHTML = `
        <div class="day-form-wrap" id="df-${staffId}-${date}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="fw-bold" style="font-size:.9rem;">${displayDate}</div>
                    <div style="font-size:.75rem;color:#9ca3af;">Per Day Salary: <strong class="text-danger">৳ ${parseFloat(per).toFixed(2)}</strong></div>
                </div>
                <button type="button" class="btn-close" onclick="document.getElementById('dayform-${staffId}').innerHTML='';openDayForm=null;"></button>
            </div>

            ${holidayAlert}

            <form onsubmit="saveDay(event, ${staffId}, '${date}')">
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <label class="form-label">Attendance Status</label>
                        <select class="form-select form-select-sm" id="status-${staffId}-${date}" onchange="onStatusChange(${staffId},'${date}')" ${disableAttr}>
                            ${statusOpts}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Approved By</label>
                        <input type="text" class="form-control form-control-sm" id="approved-${staffId}-${date}" value="${att?.approved_by || ''}" placeholder="Supervisor name..." ${disableAttr}>
                    </div>
                </div>

                <div class="row g-2 mb-2" id="time-section-${staffId}-${date}">
                    <div class="col-md-6">
                        <label class="form-label text-secondary" style="font-size:0.75rem;"><i class="fa-solid fa-right-to-bracket me-1"></i>Entry Time</label>
                        <input type="time" class="form-control form-control-sm" id="entry-${staffId}-${date}" value="${att?.entry_time ? att.entry_time.substring(0,5) : ''}" ${disableAttr}>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-secondary" style="font-size:0.75rem;"><i class="fa-solid fa-right-from-bracket me-1"></i>Exit Time</label>
                        <input type="time" class="form-control form-control-sm" id="exit-${staffId}-${date}" value="${att?.exit_time ? att.exit_time.substring(0,5) : ''}" ${disableAttr}>
                    </div>
                </div>

                {{-- Conditional: Late Reason --}}
                <div id="late-section-${staffId}-${date}" class="mb-2" style="display:none;">
                    <label class="form-label">Late Reason</label>
                    <textarea class="form-control form-control-sm" id="late-reason-${staffId}-${date}" rows="2" placeholder="Traffic / Medical / Personal / Other..." ${disableAttr}>${att?.late_reason || ''}</textarea>
                </div>

                {{-- Conditional: Leave Reason --}}
                <div id="leave-section-${staffId}-${date}" style="display:none;">
                    <div class="row g-2 mb-2">
                        <div class="col-md-8">
                            <label class="form-label">Leave Reason</label>
                            <textarea class="form-control form-control-sm" id="leave-reason-${staffId}-${date}" rows="2" placeholder="Reason for leave..." ${disableAttr}>${att?.leave_reason || ''}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Attachment (optional)</label>
                            <input type="file" class="form-control form-control-sm" id="leave-attach-${staffId}-${date}" accept=".jpg,.jpeg,.png,.pdf" ${disableAttr}>
                        </div>
                    </div>
                </div>

                {{-- Conditional: Absent Deduction --}}
                <div id="absent-section-${staffId}-${date}" style="display:none;">
                    <div class="deduction-box mb-2">
                        <div style="font-size:.75rem;color:#ef4444;font-weight:700;text-transform:uppercase;margin-bottom:.3rem;">Salary Deduction</div>
                        <div class="d-flex align-items-center gap-2">
                            <strong>৳</strong>
                            <input type="number" class="form-control form-control-sm" id="deduction-${staffId}-${date}"
                                value="${att?.salary_deduction ?? parseFloat(per).toFixed(2)}"
                                step="0.01" min="0" style="max-width:120px;" ${disableAttr}>
                            <span style="font-size:.75rem;color:#9ca3af;">(editable)</span>
                        </div>
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label">Remarks (optional)</label>
                    <input type="text" class="form-control form-control-sm" id="remarks-${staffId}-${date}" value="${att?.remarks || ''}" placeholder="Any additional notes..." ${disableAttr}>
                </div>

                <div class="d-flex gap-2 mt-3">
                    ${CAN_MARK_ATTENDANCE ? `
                    <button type="submit" class="btn-save-att" id="save-btn-${staffId}-${date}">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Save
                    </button>
                    ` : ''}
                    <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('dayform-${staffId}').innerHTML='';openDayForm=null;">
                        ${CAN_MARK_ATTENDANCE ? 'Cancel' : 'Close'}
                    </button>
                </div>
            </form>
        </div>
    `;

    // Trigger initial visibility
    onStatusChange(staffId, date);
}

/* ── Status → Show/Hide Sections ────────────────────── */
function onStatusChange(staffId, date) {
    const status = document.getElementById(`status-${staffId}-${date}`)?.value;
    const showLate   = status === 'late';
    const showLeave  = status === 'leave';
    const showAbsent = status === 'absent' || status === 'half_day';

    const lateEl   = document.getElementById(`late-section-${staffId}-${date}`);
    const leaveEl  = document.getElementById(`leave-section-${staffId}-${date}`);
    const absentEl = document.getElementById(`absent-section-${staffId}-${date}`);
    const timeEl   = document.getElementById(`time-section-${staffId}-${date}`);

    if (lateEl)   lateEl.style.display   = showLate   ? 'block' : 'none';
    if (leaveEl)  leaveEl.style.display  = showLeave  ? 'block' : 'none';
    if (absentEl) absentEl.style.display = showAbsent ? 'block' : 'none';
    if (timeEl)   timeEl.style.display   = (status === 'present' || status === 'late' || status === 'half_day') ? 'flex' : 'none';
}

/* ── Save Day ────────────────────────────────────────── */
async function saveDay(e, staffId, date) {
    e.preventDefault();
    const btn = document.getElementById(`save-btn-${staffId}-${date}`);
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…';

    const status    = document.getElementById(`status-${staffId}-${date}`)?.value;
    const entryT    = document.getElementById(`entry-${staffId}-${date}`)?.value;
    const exitT     = document.getElementById(`exit-${staffId}-${date}`)?.value;
    const approved  = document.getElementById(`approved-${staffId}-${date}`)?.value;
    const remarks   = document.getElementById(`remarks-${staffId}-${date}`)?.value;
    const lateR     = document.getElementById(`late-reason-${staffId}-${date}`)?.value;
    const leaveR    = document.getElementById(`leave-reason-${staffId}-${date}`)?.value;
    const deduction = document.getElementById(`deduction-${staffId}-${date}`)?.value;
    const fileInput = document.getElementById(`leave-attach-${staffId}-${date}`);

    const fd = new FormData();
    fd.append('_token',          CSRF);
    fd.append('staff_id',        staffId);
    fd.append('date',            date);
    fd.append('status',          status);
    fd.append('entry_time',      entryT   || '');
    fd.append('exit_time',       exitT    || '');
    fd.append('approved_by',     approved || '');
    fd.append('remarks',         remarks  || '');
    fd.append('late_reason',     lateR    || '');
    fd.append('leave_reason',    leaveR   || '');
    if (deduction) fd.append('salary_deduction', deduction);
    if (fileInput?.files[0]) fd.append('leave_attachment', fileInput.files[0]);

    try {
        const resp = await fetch('{{ route("admin.staff-attendance.save-day") }}', {
            method: 'POST',
            body:   fd,
        });
        const data = await resp.json();

        if (data.success) {
            // Update calendar cell color
            const cell = document.querySelector(`[data-staff="${staffId}"][data-date="${date}"]`);
            if (cell) {
                cell.style.background = data.color;
                cell.classList.add('attended');
                cell.title = data.status;
                cell.style.color = '#fff';
            }
            // Refresh summary
            renderSummary(staffId, data.summary);

            // Close form
            document.getElementById('dayform-' + staffId).innerHTML = '';
            openDayForm = null;

            // Refresh sidebar
            loadSidebar(staffId);

            showToast('Attendance saved!', 'success');
        } else {
            showToast(data.message || 'Failed to save.', 'danger');
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'danger');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save';
    }
}

/* ── Load Payroll Sidebar ────────────────────────────── */
function loadSidebar(staffId) {
    const staffName = document.querySelector(`#card-${staffId} .staff-name`)?.textContent;
    document.getElementById('sidebar-name').textContent = staffName || '';

    const content = document.getElementById('sidebar-content');
    content.innerHTML = `<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div></div>`;

    fetch(`${URL_PAYROLL_SIDEBAR}${staffId}?month=${MONTH}&year=${YEAR}`)
        .then(r => r.json())
        .then(data => {
            renderSidebar(data);
            activeSidebar = staffId;
        })
        .catch(() => {
            content.innerHTML = `<div class="payroll-empty text-danger">Failed to load payroll data.</div>`;
        });
}

/* ── Render Payroll Sidebar ──────────────────────────── */
function renderSidebar(d) {
    const fmt = v => '৳' + parseFloat(v || 0).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    const content = document.getElementById('sidebar-content');

    const rows = [
        {label:'Allowances', type:'section'},
        {label:'Basic Salary',          val: fmt(d.basic_salary),        cls:'val-plus'}
    ];

    if (d.raw_allowances && d.raw_allowances.length > 0) {
        d.raw_allowances.forEach(allowance => {
            if (allowance.amount > 0) {
                rows.push({
                    label: allowance.name,
                    val: fmt(allowance.amount),
                    cls: 'val-plus'
                });
            }
        });
    }

    rows.push(
        {label:'<strong>Gross Salary</strong>', val: `<strong>${fmt(d.gross_salary)}</strong>`, cls:''},
        {label:'Deductions', type:'section'}
    );

    if (d.absent_days > 0 || d.absent_deduction > 0) {
        rows.push({label:'Total Absent (days)',    val: d.absent_days,               cls:'val-minus'});
        rows.push({label:'Absent Deduction',       val: '− ' + fmt(d.absent_deduction),  cls:'val-minus'});
    }
    if (d.late_days > 0 || d.late_deduction > 0) {
        rows.push({label:'Total Late (days)',      val: d.late_days,                 cls:'val-minus'});
        rows.push({label:'Late Deduction',         val: '− ' + fmt(d.late_deduction),    cls:'val-minus'});
    }
    if (d.advance_deduction > 0) rows.push({label:'Advance Deduction',      val: '− ' + fmt(d.advance_deduction), cls:'val-minus'});
    if (d.loan_deduction > 0) rows.push({label:'Loan Deduction',         val: '− ' + fmt(d.loan_deduction),    cls:'val-minus'});

    let html = `<div class="sidebar-body">`;
    rows.forEach(r => {
        if (r.type === 'section') {
            html += `<div class="payroll-row section-head"><span>${r.label}</span></div>`;
        } else {
            html += `<div class="payroll-row"><span>${r.label}</span><span class="${r.cls}">${r.val}</span></div>`;
        }
    });
    html += `</div>`;

    // Net salary
    html += `<div style="padding:0 1.25rem .75rem;">
        <div class="payroll-net">
            <span>🏦 Net Salary</span>
            <span>${fmt(d.net_salary)}</span>
        </div>
        <div style="font-size:.72rem;color:#9ca3af;text-align:center;">
            Per Day: ${fmt(d.per_day_salary)} • Working Days: ${d.working_days}
        </div>
    </div>`;

    content.innerHTML = html;
}

/* ── Toast Notification ──────────────────────────────── */
function showToast(msg, type = 'success') {
    let toastEl = document.getElementById('att-toast');
    if (!toastEl) {
        toastEl = document.createElement('div');
        toastEl.id = 'att-toast';
        toastEl.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;';
        document.body.appendChild(toastEl);
    }
    toastEl.innerHTML = `<div class="alert alert-${type} shadow-lg" style="min-width:220px;border-radius:12px;font-size:.85rem;animation:slideDown .2s ease;">
        <i class="fa-solid fa-${type==='success'?'check-circle':'circle-exclamation'} me-2"></i>${msg}
    </div>`;
    setTimeout(() => { toastEl.innerHTML = ''; }, 3500);
}
</script>
@endsection
