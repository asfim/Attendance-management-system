@extends('layouts.app')

@section('content')
    <div class="container-fluid p-0">
        <style>
            [data-bs-theme="light"] .exam-class-badge { color: #000000 !important; font-weight: 500 !important; }
            [data-bs-theme="dark"] .exam-class-badge { color: #f8f9fa !important; }
        </style>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-light d-flex align-items-center gap-2">
                    <i class="fa-regular fa-file-lines text-primary"></i> Exams &amp; Results
                </h4>
                <p class="text-muted fs-7 mb-0">Create exams, enter marks, and publish graded results.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <!-- Session Selector -->
                <form method="GET" action="{{ route('admin.exams.index') }}" id="sessionForm">
                    <select name="session_id"
                        class="form-select form-select-sm bg-body text-body border-secondary border-opacity-50"
                        style="border-radius: 8px; min-width: 130px;"
                        onchange="document.getElementById('sessionForm').submit()">
                        @foreach ($sessions as $session)
                            <option value="{{ $session->id }}" {{ $selectedSessionId == $session->id ? 'selected' : '' }}>
                                {{ $session->name }}</option>
                        @endforeach
                    </select>
                </form>
                <button class="btn btn-sm btn-outline-secondary px-3" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#gradeRulesModal">
                    <i class="fa-solid fa-ranking-star me-1"></i> Grade rules
                </button>
                <button class="btn btn-primary px-3" style="border-radius: 8px;" data-bs-toggle="modal"
                    data-bs-target="#newExamModal">
                    <i class="fa-solid fa-plus me-1"></i> New exam
                </button>
            </div>
        </div>

        <!-- Exam Cards Grid -->
        <div class="row g-3">
            @forelse($examTypes as $exam)
                <div class="col-lg-4 col-md-6">
                    <div class="card glass-card border exam-card h-100"
                        style="border-radius: 12px; padding: 18px 18px 0 18px; display: flex; flex-direction: column;">
                        <!-- Top: Name + Status Badge -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-light mb-0" style="font-size: 1rem;">{{ $exam->name }}</h6>
                            @if ($exam->status === 'upcoming')
                                <span class="d-flex align-items-center gap-1 px-2 py-1 rounded-pill fs-8 fw-semibold"
                                    style="background: rgba(59,130,246,0.12); color: #60a5fa; border: 1px solid rgba(59,130,246,0.2); white-space: nowrap;">
                                    <span
                                        style="width: 7px; height: 7px; border-radius: 50%; background: #3b82f6; display: inline-block;"></span>
                                    Upcoming
                                </span>
                            @elseif($exam->status === 'ongoing')
                                <span class="d-flex align-items-center gap-1 px-2 py-1 rounded-pill fs-8 fw-semibold"
                                    style="background: rgba(34,197,94,0.12); color: #4ade80; border: 1px solid rgba(34,197,94,0.2); white-space: nowrap;">
                                    <span
                                        style="width: 7px; height: 7px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                                    Ongoing
                                </span>
                            @else
                                <span class="d-flex align-items-center gap-1 px-2 py-1 rounded-pill fs-8 fw-semibold"
                                    style="background: rgba(148,163,184,0.12); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); white-space: nowrap;">
                                    <span
                                        style="width: 7px; height: 7px; border-radius: 50%; background: #94a3b8; display: inline-block;"></span>
                                    Completed
                                </span>
                            @endif
                        </div>

                        <!-- Date Row -->
                        <div class="d-flex align-items-center gap-2 mb-3 text-muted" style="font-size: 0.78rem;">
                            <i class="fa-regular fa-calendar-days" style="font-size: 0.85rem;"></i>
                            <span>
                                {{ $exam->start_date ? $exam->start_date->format('d M Y') : 'N/A' }}
                                &nbsp;→&nbsp;
                                {{ $exam->end_date ? $exam->end_date->format('d M Y') : 'N/A' }}
                            </span>
                        </div>

                        <!-- Class Badges -->
                        <div class="d-flex flex-wrap gap-1 mb-4">
                            @foreach ($exam->classes as $cls)
                                <span class="px-2 py-1 fs-8 fw-normal exam-class-badge"
                                    style="background: transparent; border: 1px solid rgba(148,163,184,0.3); border-radius: 6px; font-size: 0.72rem;">{{ $cls->name }}</span>
                            @endforeach
                            @if ($exam->classes->isEmpty())
                                <span class="text-muted" style="font-size: 0.75rem;">No classes assigned</span>
                            @endif
                        </div>

                        <!-- Bottom Action Bar -->
                        <div class="d-flex align-items-center gap-2 mt-auto pb-4 pt-2"
                            style="border-top: 1px solid rgba(var(--bs-border-color-rgb), 0.15); margin-left: -18px; margin-right: -18px; padding-left: 18px; padding-right: 18px;">
                            <button onclick="viewMarks({{ $exam->id }})"
                                class="btn btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-2 fw-semibold"
                                style="background: rgba(var(--bs-border-color-rgb), 0.05); border: 1px solid rgba(var(--bs-border-color-rgb), 0.15); color: var(--bs-body-color); border-radius: 8px; padding: 7px 14px; font-size: 0.82rem; transition: background 0.15s;">
                                <i class="fa-regular fa-rectangle-list" style="font-size: 0.9rem;"></i>
                                Marks &amp; Results
                            </button>
                            @php
                                $firstSchedule = $exam->examSchedules->first();
                            @endphp
                            <button onclick="openEditExamModal({{ $exam->id }}, '{{ addslashes($exam->name) }}', '{{ $exam->status }}', {{ $exam->classes->pluck('id') }}, '{{ $firstSchedule?->exam_date?->format('Y-m-d') }}', '{{ $firstSchedule?->max_marks }}', '{{ $firstSchedule?->pass_marks }}', '{{ $firstSchedule?->classroom_id }}')"
                                class="btn btn-sm d-flex align-items-center justify-content-center text-secondary"
                                style="width: 34px; height: 34px; background: rgba(var(--bs-border-color-rgb), 0.05); border: 1px solid rgba(var(--bs-border-color-rgb), 0.15); border-radius: 8px; transition: all 0.15s;"
                                title="Edit Exam">
                                <i class="fa-solid fa-pen" style="font-size: 0.78rem;"></i>
                            </button>
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_academic'))
<form action="{{ route('admin.exams.types.destroy', $exam->id) }}" method="POST"
                                onsubmit="return confirm('Delete this exam and all its schedules?');" class="mb-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm d-flex align-items-center justify-content-center text-secondary"
                                    style="width: 34px; height: 34px; background: rgba(var(--bs-border-color-rgb), 0.05); border: 1px solid rgba(var(--bs-border-color-rgb), 0.15); border-radius: 8px; transition: all 0.15s;"
                                    title="Delete">
                                    <i class="fa-regular fa-trash-can" style="font-size: 0.78rem;"></i>
                                </button>
                            </form>
@endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card glass-card border shadow-sm rounded-4">
                        <div class="card-body p-5 text-center text-muted">
                            <i class="fa-regular fa-file-lines fs-1 mb-3 d-block opacity-40"></i>
                            <p class="fw-semibold mb-1 text-light">No exams yet</p>
                            <p class="fs-7 mb-3">Click "+ New exam" to create your first exam.</p>
                            <button class="btn btn-primary px-4" style="border-radius: 8px;" data-bs-toggle="modal"
                                data-bs-target="#newExamModal">
                                <i class="fa-solid fa-plus me-2"></i> New exam
                            </button>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>




        <!-- New Exam Modal -->
        <div class="modal fade" id="newExamModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-xl" style="border-radius: 16px; background: var(--bs-body-bg);">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4 pb-3">
                        <div>
                            <h5 class="modal-title fw-bold text-light mb-0" id="examModalTitle">New exam</h5>
                            <div class="text-muted fs-7 mt-1">
                                {{ $sessions->firstWhere('id', $selectedSessionId)?->name ?? '' }}
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('admin.exams.types.store') }}" method="POST" id="examModalForm">
                        @csrf
                        <div id="examMethodField"></div>
                        <input type="hidden" name="session_id" value="{{ $selectedSessionId }}">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Exam name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="examModalName"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="e.g. Mid Term, Final" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Status</label>
                                    <select name="status" id="examModalStatus"
                                        class="form-select bg-body text-body border-secondary border-opacity-50"
                                        style="border-radius: 8px;">
                                        <option value="upcoming">Upcoming</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light fs-7 fw-semibold">Classes <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 mt-1" id="examModalClassesContainer">
                                        @foreach ($classes as $cls)
                                            <label class="class-toggle-btn" for="cls_{{ $cls->id }}">
                                                <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                                    id="cls_{{ $cls->id }}" class="d-none class-checkbox">
                                                <span class="px-3 py-2 fs-7 fw-normal exam-class-badge"
                                                    style="display:inline-block; border-radius: 8px; border: 1px solid rgba(148,163,184,0.3); cursor: pointer; transition: all 0.15s; background: transparent;">{{ $cls->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Exam Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="exam_date" id="examModalDate"
                                        class="form-control bg-body text-body border-secondary border-opacity-50" required
                                        style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Max Marks <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="max_marks" id="examModalMaxMarks"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="100" value="100" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Pass Marks <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="pass_marks" id="examModalPassMarks"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="33" value="33" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Shift</label>
                                    <select name="shift_id" id="examModalShift"
                                        class="form-select bg-body text-body border-secondary border-opacity-50"
                                        style="border-radius: 8px;">
                                        <option value="">All Shifts / Any</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div
                            class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4 pt-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                                style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;" id="examModalSubmitBtn"><i
                                    class="fa-solid fa-save me-2"></i>Save exam</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Grade Rules Modal -->
        <div class="modal fade" id="gradeRulesModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-xl" style="border-radius: 16px; background: var(--bs-body-bg);">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4 pb-3">
                        <div>
                            <h5 class="modal-title fw-bold text-light mb-0">Grade rules</h5>
                            <div class="text-muted fs-7 mt-1">
                                Percentage bands used to grade marks and compute GPA
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="gradeRuleForm" action="{{ route('admin.exams.grade-rules.store') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="row g-2 align-items-end">
                                <div class="col">
                                    <label class="form-label text-light fs-7 fw-semibold">Grade</label>
                                    <input type="text" name="grade" id="gradeRuleGrade" class="form-control bg-body text-body border-secondary border-opacity-50" placeholder="A+" required style="border-radius: 8px;">
                                </div>
                                <div class="col">
                                    <label class="form-label text-light fs-7 fw-semibold">Min %</label>
                                    <input type="number" step="0.01" name="min_percent" id="gradeRuleMin" class="form-control bg-body text-body border-secondary border-opacity-50" required style="border-radius: 8px;">
                                </div>
                                <div class="col">
                                    <label class="form-label text-light fs-7 fw-semibold">Max %</label>
                                    <input type="number" step="0.01" name="max_percent" id="gradeRuleMax" class="form-control bg-body text-body border-secondary border-opacity-50" required style="border-radius: 8px;">
                                </div>
                                <div class="col">
                                    <label class="form-label text-light fs-7 fw-semibold">Point</label>
                                    <input type="number" step="0.01" name="point" id="gradeRulePoint" class="form-control bg-body text-body border-secondary border-opacity-50" required style="border-radius: 8px;">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-secondary px-4" style="border-radius: 8px;" id="addGradeRuleBtn">Add</button>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive" id="gradeRulesTableContainer" style="display: {{ isset($gradeRules) && $gradeRules->count() > 0 ? 'block' : 'none' }}">
                            <table class="table table-sm text-light mb-0">
                                <thead>
                                    <tr>
                                        <th class="border-bottom border-secondary border-opacity-25 text-muted fw-semibold">Grade</th>
                                        <th class="border-bottom border-secondary border-opacity-25 text-muted fw-semibold text-center">Range</th>
                                        <th class="border-bottom border-secondary border-opacity-25 text-muted fw-semibold text-center">Point</th>
                                        <th class="border-bottom border-secondary border-opacity-25 text-muted fw-semibold text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="gradeRulesTableBody">
                                    @if(isset($gradeRules))
                                        @foreach($gradeRules as $rule)
                                            <tr id="grade-rule-row-{{ $rule->id }}">
                                                <td class="align-middle fw-bold">{{ $rule->grade }}</td>
                                                <td class="align-middle text-center">{{ number_format($rule->min_percent, 0) }}% - {{ number_format($rule->max_percent, 0) }}%</td>
                                                <td class="align-middle text-center">{{ number_format($rule->point, 2) }}</td>
                                                <td class="align-middle text-end">
                                                    <button type="button" class="btn btn-sm text-danger p-1 border-0 bg-transparent delete-grade-rule" data-id="{{ $rule->id }}" data-url="{{ route('admin.exams.grade-rules.destroy', $rule->id) }}">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="noGradeRulesMsg" class="text-center py-4 text-muted" style="display: {{ isset($gradeRules) && $gradeRules->count() > 0 ? 'none' : 'block' }}">
                            No grade rules yet. Add your grading scale above.
                        </div>
                    </div>
                    <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4 pt-3 d-flex justify-content-end">
                        <button type="button" class="btn text-light fw-semibold px-4" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Exam Modal -->
        <div class="modal fade" id="editExamModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow-xl" style="border-radius: 16px; background: var(--bs-body-bg);">
                    <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4 pb-3">
                        <h5 class="modal-title fw-bold text-light mb-0" id="editExamModalTitle">Edit exam</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    
                    <form id="editExamModalForm" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Exam Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" id="editExamModalName"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="e.g. Mid Term, Final" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Status</label>
                                    <select name="status" id="editExamModalStatus"
                                        class="form-select bg-body text-body border-secondary border-opacity-50"
                                        style="border-radius: 8px;">
                                        <option value="upcoming">Upcoming</option>
                                        <option value="ongoing">Ongoing</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-light fs-7 fw-semibold">Classes <span
                                            class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 mt-1">
                                        @foreach ($classes as $cls)
                                            <label class="class-toggle-btn" for="edit_cls_{{ $cls->id }}">
                                                <input type="checkbox" name="class_ids[]" value="{{ $cls->id }}"
                                                    id="edit_cls_{{ $cls->id }}" class="d-none edit-class-checkbox">
                                                <span class="px-3 py-2 fs-7 fw-normal exam-class-badge"
                                                    style="display:inline-block; border-radius: 8px; border: 1px solid rgba(148,163,184,0.3); cursor: pointer; transition: all 0.15s; background: transparent;">{{ $cls->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Exam Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="exam_date" id="editExamModalDate"
                                        class="form-control bg-body text-body border-secondary border-opacity-50" required
                                        style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Max Marks <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="max_marks" id="editExamModalMaxMarks"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="100" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Pass Marks <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="pass_marks" id="editExamModalPassMarks"
                                        class="form-control bg-body text-body border-secondary border-opacity-50"
                                        placeholder="33" required style="border-radius: 8px;">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-light fs-7 fw-semibold">Shift</label>
                                    <select name="shift_id" id="editExamModalShift"
                                        class="form-select bg-body text-body border-secondary border-opacity-50"
                                        style="border-radius: 8px;">
                                        <option value="">All Shifts / Any</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4 pt-3 d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal"
                                style="border-radius: 8px;">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px;"><i
                                    class="fa-solid fa-save me-2"></i>Update exam</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <style>
            .class-checkbox:checked+span,
            .sched-class-checkbox:checked+span,
            .edit-class-checkbox:checked+span {
                background: rgba(13, 110, 253, 0.15) !important;
                color: #0d6efd !important;
                border-color: #0d6efd !important;
            }

            .class-toggle-btn span:hover {
                background: rgba(13, 110, 253, 0.08) !important;
                border-color: rgba(13, 110, 253, 0.5) !important;
                color: #0d6efd !important;
            }
        </style>

        <script>
            const storeUrl = '{{ route('admin.exams.types.store') }}';
            const updateUrlBase = '{{ url('admin/exams/types') }}';

            // Reset modal back to CREATE mode when closed
            document.getElementById('newExamModal').addEventListener('hidden.bs.modal', function() {
                resetExamModal();
            });

            function resetExamModal() {
                document.getElementById('examModalTitle').textContent = 'New exam';
                document.getElementById('examModalForm').action = storeUrl;
                document.getElementById('examMethodField').innerHTML = '';
                document.getElementById('examModalName').value = '';
                document.getElementById('examModalStatus').value = 'upcoming';
                document.querySelectorAll('.class-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('examModalDate').value = '';
                document.getElementById('examModalMaxMarks').value = '100';
                document.getElementById('examModalPassMarks').value = '33';
                document.getElementById('examModalSubmitBtn').innerHTML = '<i class="fa-solid fa-save me-2"></i>Save exam';
            }

            function openEditExamModal(id, name, status, classIds, examDate, maxMarks, passMarks, classroomId, shiftId) {
                document.getElementById('editExamModalForm').action = updateUrlBase + '/' + id;

                // Fill values
                document.getElementById('editExamModalName').value = name;
                document.getElementById('editExamModalStatus').value = status;
                document.getElementById('editExamModalDate').value = examDate || '';
                document.getElementById('editExamModalMaxMarks').value = maxMarks || '100';
                document.getElementById('editExamModalPassMarks').value = passMarks || '33';
                document.getElementById('editExamModalShift').value = shiftId || '';
                
                // Reset classes

                // Check assigned classes
                document.querySelectorAll('.edit-class-checkbox').forEach(cb => {
                    cb.checked = classIds.includes(parseInt(cb.value));
                });

                new bootstrap.Modal(document.getElementById('editExamModal')).show();
            }

            document.getElementById('editExamModal').addEventListener('hidden.bs.modal', function() {
                // Clear checkboxes or any state if needed
            });

            function viewMarks(examTypeId) {
                window.location.href = '{{ route('admin.results.index') }}?exam_type_id=' + examTypeId;
            }

            // Grade Rules AJAX Logic
            document.getElementById('gradeRuleForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const formData = new FormData(form);
                const btn = document.getElementById('addGradeRuleBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                btn.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success && data.rule) {
                        // Append to table
                        const tbody = document.getElementById('gradeRulesTableBody');
                        const row = document.createElement('tr');
                        row.id = 'grade-rule-row-' + data.rule.id;
                        row.innerHTML = `
                            <td class="align-middle fw-bold">${data.rule.grade}</td>
                            <td class="align-middle text-center">${parseFloat(data.rule.min_percent).toFixed(0)}% - ${parseFloat(data.rule.max_percent).toFixed(0)}%</td>
                            <td class="align-middle text-center">${parseFloat(data.rule.point).toFixed(2)}</td>
                            <td class="align-middle text-end">
                                <button type="button" class="btn btn-sm text-danger p-1 border-0 bg-transparent delete-grade-rule" data-id="${data.rule.id}" data-url="${form.action}/${data.rule.id}">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);

                        document.getElementById('gradeRulesTableContainer').style.display = 'block';
                        document.getElementById('noGradeRulesMsg').style.display = 'none';

                        // Reset form
                        document.getElementById('gradeRuleGrade').value = '';
                        document.getElementById('gradeRuleMin').value = '';
                        document.getElementById('gradeRuleMax').value = '';
                        document.getElementById('gradeRulePoint').value = '';
                    } else {
                        alert('Error adding grade rule.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please check your inputs.');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            });

            document.getElementById('gradeRulesTableBody').addEventListener('click', function(e) {
                const btn = e.target.closest('.delete-grade-rule');
                if(btn) {
                    if(confirm('Remove this grade rule?')) {
                        const rowId = btn.getAttribute('data-id');
                        const url = btn.getAttribute('data-url');
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
                        btn.disabled = true;

                        fetch(url, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                document.getElementById('grade-rule-row-' + rowId).remove();
                                if(document.getElementById('gradeRulesTableBody').children.length === 0) {
                                    document.getElementById('gradeRulesTableContainer').style.display = 'none';
                                    document.getElementById('noGradeRulesMsg').style.display = 'block';
                                }
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            });
        </script>
    @endsection
