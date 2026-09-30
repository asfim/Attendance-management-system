@extends('layouts.app')

@section('title', 'Global Holidays')

@section('content')
<style>
/* ─── Variables ──────────────────────────────────────────────── */
:root {
    --card-bg:      #ffffff;
    --card-border:  #e2e8f0;
}
[data-bs-theme="dark"] {
    --card-bg:      #1e293b;
    --card-border:  #334155;
}

/* ─── Layout ─────────────────────────────────────────────────── */
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

.month-nav { display: flex; align-items: center; gap: .5rem; }
.month-nav select { border-radius: 8px; font-size: .85rem; }

/* ─── Calendar ──────────────────────────────────────────────────*/
.card-cal {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.05);
}
.cal-grid { width: 100%; border-collapse: collapse; }
.cal-grid th {
    text-align: center;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #9ca3af;
    padding: .5rem 0;
    border-bottom: 1px solid var(--card-border);
}
.cal-cell {
    text-align: center;
    padding: .5rem;
    vertical-align: top;
}
.cal-day {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 46px; height: 46px;
    border-radius: 10px;
    font-size: .9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    border: 2px solid transparent;
    background: rgba(0,0,0,0.02);
    color: inherit;
    position: relative;
}
[data-bs-theme="dark"] .cal-day { background: #334155; }
.cal-day:hover:not(.out-month):not(.future) {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99,102,241,.15);
    transform: scale(1.1);
}
.cal-day.out-month { opacity: .25; cursor: default; }
.cal-day.today     { border-color: #6366f1 !important; color: #6366f1; }
.cal-day.holiday   { background: #22c55e !important; color: #fff; border-color: #22c55e !important; }
.cal-day.holiday:hover:not(.out-month) {
    box-shadow: 0 0 0 3px rgba(34,197,94,.3);
    border-color: #22c55e;
}
.holiday-name {
    position: absolute;
    bottom: -18px;
    font-size: 0.6rem;
    color: #9ca3af;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    max-width: 60px;
}
</style>

<div class="sa-header">
    <h5><i class="fa-solid fa-umbrella-beach me-2 text-primary"></i>Global Holidays</h5>
    <div class="ms-auto d-flex gap-2">
        <form action="{{ route('admin.holidays.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill fw-bold" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Syncing...'; this.disabled=true; this.form.submit();">
                <i class="fa-brands fa-google me-1"></i> Sync BD Holidays
            </button>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card glass-card border p-3 mb-4">
            <h6 class="fw-bold mb-3"><i class="fa-solid fa-filter me-2"></i>Filter Month</h6>
            <div class="month-nav d-flex flex-column gap-2 mb-3">
                <select id="monthSel" class="form-select form-select-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                    @endforeach
                </select>
                <select id="yearSel" class="form-select form-select-sm">
                    @foreach(range(2024, now()->year+2) as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm w-100 mt-2" onclick="reloadMonth()">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> View
                </button>
            </div>
            
            <div class="alert bg-info bg-opacity-10 border border-info border-opacity-25 text-info fs-7 mt-3">
                <i class="fa-solid fa-circle-info me-1"></i>
                Click on any date to toggle it as a holiday for all students and staff.
            </div>
        </div>
    </div>
    
    <div class="col-md-9">
        <div class="card-cal">
            <table class="cal-grid">
                <thead>
                    <tr>
                        <th>Mon</th>
                        <th>Tue</th>
                        <th>Wed</th>
                        <th>Thu</th>
                        <th>Fri</th>
                        <th>Sat</th>
                        <th>Sun</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeks as $week)
                    <tr>
                        @foreach($week as $day)
                            @php
                                $classes = ['cal-day'];
                                if(!$day['in_month']) $classes[] = 'out-month';
                                if($day['is_today']) $classes[] = 'today';
                                if($day['is_holiday']) $classes[] = 'holiday';
                            @endphp
                            <td class="cal-cell">
                                <div class="{{ implode(' ', $classes) }}" 
                                     @if($day['in_month']) onclick="toggleHoliday(this, '{{ $day['date'] }}', {{ $day['is_holiday'] ? 'true' : 'false' }})" @endif
                                     title="{{ $day['name'] ?? 'Click to toggle holiday' }}">
                                    {{ $day['day'] }}
                                    @if($day['is_holiday'] && $day['name'])
                                        <div class="holiday-name">{{ Str::limit($day['name'], 10) }}</div>
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
const CSRF = '{{ csrf_token() }}';

function reloadMonth() {
    const m = document.getElementById('monthSel').value;
    const y = document.getElementById('yearSel').value;
    window.location.href = `{{ route('admin.holidays.index') }}?month=${m}&year=${y}`;
}

async function toggleHoliday(element, date, isHoliday) {
    // If it's already a holiday, we'll remove it. If not, add it.
    let name = isHoliday ? '' : 'Holiday';

    // Optimistically update the UI
    const isNowHoliday = !isHoliday;
    
    if (isNowHoliday) {
        element.classList.add('holiday');
        // Update the onclick attribute so next click reverses it
        element.setAttribute('onclick', `toggleHoliday(this, '${date}', true)`);
    } else {
        element.classList.remove('holiday');
        element.setAttribute('onclick', `toggleHoliday(this, '${date}', false)`);
        
        // Remove the name div if it exists
        const nameDiv = element.querySelector('.holiday-name');
        if(nameDiv) nameDiv.remove();
    }

    try {
        const fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('date', date);
        fd.append('name', name);

        const resp = await fetch('{{ route("admin.holidays.toggle") }}', {
            method: 'POST',
            body: fd
        });
        const data = await resp.json();
        
        if (!data.success) {
            // Revert on failure
            alert(data.message || 'Failed to toggle holiday.');
            window.location.reload();
        }
    } catch (e) {
        alert('An error occurred.');
        window.location.reload();
    }
}
</script>
@endsection
