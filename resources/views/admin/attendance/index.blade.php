@extends('layouts.app')

@section('title', 'Student Attendance')

@section('content')
<style>
/* ─── Variables ──────────────────────────────────────────────── */
:root {
    --att-present:  #22c55e;
    --att-absent:   #ef4444;
    --att-late:     #f97316;
    --card-bg:      #ffffff;
    --card-border:  #e2e8f0;
    --sidebar-w:    320px;
    --text-muted:   #64748b;
    --text-main:    #0f172a;
}
[data-bs-theme="dark"] {
    --card-bg:      #1e293b;
    --card-border:  #334155;
    --text-muted:   #94a3b8;
    --text-main:    #f8fafc;
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

/* ─── Header Card ────────────────────────────────────────────── */
.bulk-header {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 16px 16px 0 0;
    padding: 1.25rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.bulk-header h4 {
    margin: 0 0 0.25rem 0;
    font-weight: 700;
    color: var(--text-main);
    font-size: 1.2rem;
}
.bulk-header p {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-muted);
}
.bulk-header-icon {
    font-size: 1.5rem;
    color: var(--text-muted);
    opacity: 0.7;
}

/* ─── Summary Badges ─────────────────────────────────────────── */
.bulk-summary {
    background: var(--card-bg);
    border-left: 1px solid var(--card-border);
    border-right: 1px solid var(--card-border);
    border-bottom: 1px solid var(--card-border);
    padding: 1rem 1.5rem;
    display: flex;
    gap: 1rem;
}
.bulk-badge {
    flex: 1;
    padding: 0.5rem;
    border-radius: 8px;
    text-align: center;
    font-weight: 700;
    font-size: 0.9rem;
}
.bulk-badge.present { background: #15803d; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(21, 128, 61, 0.4); }
.bulk-badge.absent  { background: #b91c1c; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(185, 28, 28, 0.4); }
.bulk-badge.late    { background: #d97706; color: #ffffff; box-shadow: 0 4px 6px -1px rgba(217, 119, 6, 0.4); }

/* ─── Student List ───────────────────────────────────────────── */
.student-list-container {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-top: none;
    border-radius: 0 0 16px 16px;
    padding: 0;
}
.student-row {
    display: flex;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid var(--card-border);
    transition: background 0.15s;
}
.student-row:last-child {
    border-bottom: none;
}
.student-row:hover {
    background: rgba(0,0,0,0.02);
}
[data-bs-theme="dark"] .student-row:hover {
    background: rgba(255,255,255,0.02);
}

.student-avatar {
    width: 44px; height: 44px;
    border-radius: 50%;
    background: #2563eb;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    margin-right: 1rem;
    flex-shrink: 0;
}
.student-info { flex: 1; min-width: 0; }
.student-name {
    margin: 0;
    font-weight: 700;
    color: var(--text-main);
    font-size: 1rem;
}
.student-roll {
    margin: 0 0 0.25rem 0;
    font-size: 0.8rem;
    color: var(--text-muted);
}
.student-stats {
    display: flex;
    gap: 0.4rem;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
}
.student-stats:hover {
    opacity: 0.8;
}
.stat-badge {
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
}
.stat-p { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
.stat-a { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
.stat-l { background: rgba(249, 115, 22, 0.1); color: #f97316; }

/* ─── Note Input ─────────────────────────────────────────────── */
.note-input {
    width: 140px;
    margin-right: 1rem;
    border-radius: 8px;
    border: 1px solid var(--card-border);
    padding: 0.35rem 0.75rem;
    font-size: 0.85rem;
    background: transparent;
    color: var(--text-main);
}
.note-input:focus {
    outline: none;
    border-color: #3b82f6;
}

/* ─── Radio Buttons ──────────────────────────────────────────── */
.att-options {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}
.att-opt {
    position: relative;
    cursor: pointer;
    margin: 0;
}
.att-opt input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
}
.att-opt span {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 34px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    border: 1px solid var(--card-border);
    color: var(--text-muted);
    transition: all 0.2s;
    user-select: none;
}
.att-opt input:focus ~ span {
    box-shadow: 0 0 0 2px rgba(100, 116, 139, 0.3);
}
/* Selected States */
.att-opt.opt-p input:checked ~ span { background: var(--att-present); color: white; border-color: var(--att-present); }
.att-opt.opt-a input:checked ~ span { background: var(--att-absent); color: white; border-color: var(--att-absent); }
.att-opt.opt-l input:checked ~ span { background: var(--att-late); color: white; border-color: var(--att-late); }

/* ─── Submit Button ──────────────────────────────────────────── */
.btn-submit-att {
    display: block;
    width: 100%;
    background: linear-gradient(to right, #d97757, #b04e33);
    color: white;
    font-weight: 700;
    font-size: 1.1rem;
    padding: 1rem;
    border: none;
    border-radius: 12px;
    margin-top: 1.5rem;
    cursor: pointer;
    transition: opacity 0.2s;
    text-align: center;
}
.btn-submit-att:hover {
    opacity: 0.9;
    color: white;
}
</style>

<div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
    <div>
        <h3 class="fw-bold mb-0"><i class="bi bi-person-check-fill text-primary me-2"></i>Student Attendance</h3>
    </div>
    <a href="{{ route('admin.attendance.biometric-logs', ['type' => 'student']) }}" class="btn btn-primary rounded-pill px-3 shadow-sm">
        <i class="bi bi-fingerprint me-1"></i> Student Biometric Logs & ADMS
    </a>
</div>

<div class="sa-layout">
    {{-- ──────────────────────────── LEFT: Main Content ────────────────────────────── --}}
    <div class="sa-main">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($students->count() > 0)
            @php
                $cls = \App\Models\SchoolClass::find(request('class_id'));
                $sec = \App\Models\Section::find(request('section_id'));
                
                // Calculate summaries based on current view loaded attendances
                $totalPresent = 0;
                $totalAbsent = 0;
                $totalLate = 0;
                
                foreach($students as $s) {
                    $att = $s->attendances->first();
                    if ($att) {
                        if ($att->status == 'present') $totalPresent++;
                        elseif ($att->status == 'absent') $totalAbsent++;
                        elseif ($att->status == 'late') $totalLate++;
                    }
                }
            @endphp
            
            <div class="bulk-header">
                <div>
                    <h4>Class {{ $cls->name }} - Section {{ $sec->name }}</h4>
                    <p>{{ \Carbon\Carbon::parse($date)->format('d M Y') }} &bull; {{ $students->count() }} students</p>
                </div>
                <div class="bulk-header-icon">
                    <i class="fa-regular fa-calendar-check"></i>
                </div>
            </div>
            
            <div class="bulk-summary">
                <div class="bulk-badge present" id="sum-present">
                    <span id="count-present">{{ $totalPresent }}</span> present
                </div>
                <div class="bulk-badge absent" id="sum-absent">
                    <span id="count-absent">{{ $totalAbsent }}</span> absent
                </div>
                <div class="bulk-badge late" id="sum-late">
                    <span id="count-late">{{ $totalLate }}</span> late
                </div>
            </div>
            
            <form action="{{ route('admin.attendance.bulk-save') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                
                <div class="student-list-container">
                    @foreach($students as $student)
                        @php
                            $att = $student->attendances->first();
                            $status = $att ? $att->status : 'absent'; // Default to absent
                            
                            // Generate initials for avatar
                            $nameParts = explode(' ', $student->user->name);
                            $initials = '';
                            if(count($nameParts) > 0) $initials .= strtoupper(substr($nameParts[0], 0, 1));
                            if(count($nameParts) > 1) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                            if($initials == '') $initials = 'S';
                        @endphp
                        <div class="student-row">
                            @if ($student->photo_path)
                                <img src="{{ asset('storage/' . $student->photo_path) }}" alt="Photo" class="student-avatar" style="object-fit: cover; border: 1px solid var(--card-border);">
                            @else
                                <div class="student-avatar" style="background-color: hsl({{ crc32($student->user->name) % 360 }}, 70%, 40%);">
                                    {{ $initials }}
                                </div>
                            @endif
                            <div class="student-info">
                                <p class="student-name">{{ $student->user->name }}</p>
                                <p class="student-roll">Roll {{ str_pad($student->roll_no ?? 0, 2, '0', STR_PAD_LEFT) }}</p>
                                <div class="student-stats" onclick="showHistory({{ $student->id }}, '{{ addslashes($student->user->name) }}')">
                                    <span class="stat-badge stat-p" title="Total Present">{{ $student->total_p }} P</span>
                                    <span class="stat-badge stat-a" title="Total Absent">{{ $student->total_a }} A</span>
                                    <span class="stat-badge stat-l" title="Total Late">{{ $student->total_l }} L</span>
                                </div>
                            </div>
                            <input type="text" name="remarks[{{ $student->id }}]" class="note-input" placeholder="Add note..." value="{{ $att ? $att->remarks : '' }}">
                            <div class="att-options">
                                <label class="att-opt opt-p">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="present" class="att-radio" data-type="present" {{ $status == 'present' ? 'checked' : '' }}>
                                    <span>P</span>
                                </label>
                                <label class="att-opt opt-a">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="absent" class="att-radio" data-type="absent" {{ $status == 'absent' ? 'checked' : '' }}>
                                    <span>A</span>
                                </label>
                                <label class="att-opt opt-l">
                                    <input type="radio" name="attendance[{{ $student->id }}]" value="late" class="att-radio" data-type="late" {{ $status == 'late' ? 'checked' : '' }}>
                                    <span>L</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <button type="submit" class="btn-submit-att">
                    Submit Attendance <i class="fa-solid fa-arrow-down ms-2"></i>
                </button>
            </form>

        @else
            <div class="text-center text-muted py-5" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:16px;">
                <i class="fa-solid fa-users-slash fs-2 mb-3 d-block"></i>
                @if(request('class_id'))
                    No students found for this class & section.
                @else
                    Please select a class and section to mark attendance.
                @endif
            </div>
        @endif
    </div>

    {{-- ──────────────────────────── RIGHT: Filters Sidebar ──────────────────────── --}}
    <div class="sa-sidebar">
        <div class="card glass-card border">
            <div class="card-body">
                <h6 class="fw-bold mb-3"><i class="fa-solid fa-filter me-2"></i>Filters</h6>
                <form action="{{ route('admin.attendance.index') }}" method="GET">
                    
                    <div class="mb-3">
                        <label class="form-label fs-7">Date</label>
                        <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}" required onchange="this.form.submit()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7">Class</label>
                        <select name="class_id" id="class_id" class="form-select form-select-sm" required onchange="fetchSections()">
                            <option value="">Select Class</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls->id }}" {{ request('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7">Section</label>
                        <select name="section_id" id="section_id" class="form-select form-select-sm" required onchange="this.form.submit()">
                            <option value="">Select Section</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fs-7">Shift (Optional)</label>
                        <select name="shift_id" id="shift_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Shifts</option>
                            @foreach($shifts as $shift)
                                <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>
                                    {{ $shift->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm w-100 mt-2">
                        <i class="fa-solid fa-magnifying-glass me-1"></i> Load Students
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ─── History Modal ────────────────────────────────────────── --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title fw-bold" id="historyModalTitle">Attendance History</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="historyModalBody">
      </div>
    </div>
  </div>
</div>

<script>
const URL_CALENDAR = '{{ url("/admin/attendance/calendar/") }}/';
const classesData = @json($classes);

/* ── Fetch Sections ──────────────────────────────────── */
function fetchSections() {
    const classId = document.getElementById('class_id').value;
    const sectionSelect = document.getElementById('section_id');
    sectionSelect.innerHTML = '<option value="">Select Section</option>';
    
    if (!classId) return;
    
    const selectedClass = classesData.find(c => c.id == classId);
    if (selectedClass && selectedClass.sections) {
        selectedClass.sections.forEach(section => {
            const selected = (section.id == {{ request('section_id', '0') }}) ? 'selected' : '';
            sectionSelect.innerHTML += `<option value="${section.id}" ${selected}>${section.name}</option>`;
        });
    }
}
document.addEventListener('DOMContentLoaded', function() {
    if(document.getElementById('class_id').value) fetchSections();
    
    // Live update summary numbers
    const radios = document.querySelectorAll('.att-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', updateSummary);
    });
});

function updateSummary() {
    let p = 0, a = 0, l = 0;
    const radios = document.querySelectorAll('.att-radio:checked');
    radios.forEach(r => {
        if(r.dataset.type === 'present') p++;
        if(r.dataset.type === 'absent') a++;
        if(r.dataset.type === 'late') l++;
    });
    
    document.getElementById('count-present').innerText = p;
    document.getElementById('count-absent').innerText = a;
    document.getElementById('count-late').innerText = l;
}

function showHistory(studentId, studentName) {
    const dateVal = document.querySelector('input[name="date"]').value;
    if (!dateVal) return;
    
    const d = new Date(dateVal);
    const m = d.getMonth() + 1;
    const y = d.getFullYear();

    const modalTitle = document.getElementById('historyModalTitle');
    const modalBody = document.getElementById('historyModalBody');
    
    modalTitle.innerText = studentName;
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><div class="mt-2 small text-muted">Loading history...</div></div>';
    
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    historyModal.show();

    fetch(`${URL_CALENDAR}${studentId}?month=${m}&year=${y}`)
        .then(r => r.json())
        .then(data => {
            let pDates = [];
            let aDates = [];
            let lDates = [];

            data.weeks.forEach(week => {
                week.forEach(day => {
                    if (day.in_month && !day.is_future) {
                        if (day.status === 'present') pDates.push(day.day);
                        else if (day.status === 'absent') aDates.push(day.day);
                        else if (day.status === 'late') lDates.push(day.day);
                    }
                });
            });

            const monthName = d.toLocaleString('default', { month: 'short' });

            let html = `
                <div class="mb-3">
                    <span class="badge stat-p me-2" style="color:#ffffff;background:#15803d;box-shadow: 0 2px 4px rgba(21,128,61,0.3);">Present (${pDates.length})</span>
                    <div class="small text-muted mt-1">${pDates.length > 0 ? pDates.join(', ') + ' ' + monthName : '—'}</div>
                </div>
                <div class="mb-3">
                    <span class="badge stat-a me-2" style="color:#ffffff;background:#b91c1c;box-shadow: 0 2px 4px rgba(185,28,28,0.3);">Absent (${aDates.length})</span>
                    <div class="small text-muted mt-1">${aDates.length > 0 ? aDates.join(', ') + ' ' + monthName : '—'}</div>
                </div>
                <div class="mb-3">
                    <span class="badge stat-l me-2" style="color:#ffffff;background:#d97706;box-shadow: 0 2px 4px rgba(217,119,6,0.3);">Late (${lDates.length})</span>
                    <div class="small text-muted mt-1">${lDates.length > 0 ? lDates.join(', ') + ' ' + monthName : '—'}</div>
                </div>
            `;
            modalBody.innerHTML = html;
        })
        .catch(() => {
            modalBody.innerHTML = '<div class="text-danger text-center">Failed to load history.</div>';
        });
}
</script>
@endsection
