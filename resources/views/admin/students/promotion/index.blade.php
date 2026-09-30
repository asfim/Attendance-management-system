@extends('layouts.app')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-bold mb-0 "><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Student Promotion
                System</h4>
            <p class="text-secondary mb-0">Manage annual student promotions, calculate next rolls, and preserve academic
                history.</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Promotion Filter Card -->
        <div class="col-12">
            <div class="card border-0 shadow-sm ">
                <div class="card-header  border-secondary border-opacity-50 py-3">
                    <h6 class="fw-bold m-0 "><i class="fa-solid fa-filter me-2 text-info"></i>Promotion
                        Configuration</h6>
                </div>
                <div class="card-body  ">
                    <form action="{{ route('admin.students.promotion.index') }}" method="GET"
                        class="row g-3 align-items-end">

                        <div class="col-md-5">
                            <div class="p-3 border border-secondary border-opacity-25 rounded ">
                                <h6 class="text-warning mb-3 fw-bold"><i
                                        class="fa-solid fa-arrow-up-from-bracket me-2"></i>Promote From:</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fs-7">Academic Year</label>
                                        <select name="from_session_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            required>
                                            <option value="">Select</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}"
                                                    {{ request('from_session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Class</label>
                                        <select name="from_class_id" id="from_class_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            onchange="handleFromClassChange()" required>
                                            <option value="">Select</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ request('from_class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Section</label>
                                        <select name="from_section_id" id="from_section_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            data-selected="{{ request('from_section_id') }}" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Shift (Optional)</label>
                                        <select name="from_shift_id" id="from_shift_id"
                                            class="form-select form-select-sm border-secondary border-opacity-50">
                                            <option value="">All Shifts</option>
                                            @foreach ($shifts as $shift)
                                                <option value="{{ $shift->id }}"
                                                    {{ request('from_shift_id') == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2 text-center d-flex align-items-center justify-content-center h-100">
                            <i class="fa-solid fa-arrow-right-long text-primary fa-2x mt-4"></i>
                        </div>

                        <div class="col-md-5">
                            <div class="p-3 border border-secondary border-opacity-25 rounded ">
                                <h6 class="text-success mb-3 fw-bold"><i
                                        class="fa-solid fa-arrow-right-to-bracket me-2"></i>Promote To:</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label fs-7">Academic Year</label>
                                        <select name="to_session_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            required>
                                            <option value="">Select</option>
                                            @foreach ($sessions as $session)
                                                <option value="{{ $session->id }}"
                                                    {{ request('to_session_id') == $session->id ? 'selected' : '' }}>
                                                    {{ $session->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Class</label>
                                        <select name="to_class_id" id="to_class_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            onchange="updateSections('to')" required>
                                            <option value="">Select</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}"
                                                    {{ request('to_class_id') == $class->id ? 'selected' : '' }}>
                                                    {{ $class->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Section</label>
                                        <select name="to_section_id" id="to_section_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50"
                                            data-selected="{{ request('to_section_id') }}" required>
                                            <option value="">Select</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fs-7">Shift (Optional)</label>
                                        <select name="to_shift_id" id="to_shift_id"
                                            class="form-select form-select-sm   border-secondary border-opacity-50">
                                            <option value="">Same Shift</option>
                                            @foreach ($shifts as $shift)
                                                <option value="{{ $shift->id }}"
                                                    {{ request('to_shift_id') == $shift->id ? 'selected' : '' }}>
                                                    {{ $shift->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mt-3 text-end">
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search me-2"></i>Load
                                Students</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Student List Card -->
        @if (isset($students) && $students->count() > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm ">
                    <div
                        class="card-header  border-secondary border-opacity-50 py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0 "><i
                                class="fa-solid fa-users-viewfinder me-2 text-info"></i>Eligible Students</h6>
                        <div>
                            <span class="badge bg-secondary">Total: {{ $students->count() }}</span>
                        </div>
                    </div>
                    <div class="card-body  p-0">
                        <form id="promotionForm" action="{{ route('admin.students.promotion.store') }}" method="POST">
                            @csrf
                            <!-- Hidden fields to pass the target data -->
                            <input type="hidden" name="from_session_id" value="{{ request('from_session_id') }}">
                            <input type="hidden" name="from_class_id" value="{{ request('from_class_id') }}">
                            <input type="hidden" name="from_section_id" value="{{ request('from_section_id') }}">
                            <input type="hidden" name="from_shift_id" value="{{ request('from_shift_id') }}">

                            <input type="hidden" name="to_session_id" value="{{ request('to_session_id') }}">
                            <input type="hidden" name="to_class_id" value="{{ request('to_class_id') }}">
                            <input type="hidden" name="to_section_id" value="{{ request('to_section_id') }}">
                            <input type="hidden" name="to_shift_id" value="{{ request('to_shift_id') }}">

                            <div class="table-responsive">
                                <table class="table  table-hover table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th width="50" class="text-center">
                                                <div class="form-check d-flex justify-content-center">
                                                    <input class="form-check-input border-secondary" type="checkbox"
                                                        id="selectAll" checked>
                                                </div>
                                            </th>
                                            <th>Student</th>
                                            <th>Student ID / Roll</th>
                                            <th>Result (Marks)</th>
                                            <th>New Roll (Projected)</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($students as $student)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="form-check d-flex justify-content-center">
                                                        <input class="form-check-input border-secondary student-checkbox"
                                                            type="checkbox" name="student_ids[]"
                                                            value="{{ $student->id }}"
                                                            {{ $student->calculated_status == 'Eligible' ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if ($student->photo_path)
                                                            <img src="{{ asset('storage/' . $student->photo_path) }}"
                                                                class="rounded-circle me-3" width="40" height="40"
                                                                style="object-fit: cover;">
                                                        @else
                                                            <div class="rounded-circle bg-secondary bg-opacity-25 d-flex align-items-center justify-content-center me-3"
                                                                style="width: 40px; height: 40px;">
                                                                <i class="fa-solid fa-user text-secondary"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0  fw-bold">
                                                                {{ $student->user->name ?? 'Unknown' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-secondary d-block fs-7">Adm:
                                                        {{ $student->admission_no }}</span>
                                                    <span class=" fw-semibold">Roll:
                                                        {{ $student->roll_no }}</span>
                                                </td>
                                                <td>
                                                    <span
                                                        class="text-info fw-bold">{{ $student->total_marks ?? '0' }}</span>
                                                </td>
                                                <td>
                                                    @if (isset($student->projected_roll))
                                                        <span
                                                            class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fs-6">{{ $student->projected_roll }}</span>
                                                    @else
                                                        <span class="text-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($student->calculated_status == 'Eligible')
                                                        <span
                                                            class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25"><i
                                                                class="fa-solid fa-check me-1"></i>Eligible</span>
                                                    @else
                                                        <span
                                                            class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25"><i
                                                                class="fa-solid fa-xmark me-1"></i>Not Eligible</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 border-top border-secondary border-opacity-25 text-end">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-toggle="modal" data-bs-target="#confirmPromotionModal">
                                    <i class="fa-solid fa-check-double me-2"></i>Promote Selected Students
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @elseif(request()->has('from_session_id'))
            <div class="col-12">
                <div
                    class="alert alert-info bg-info bg-opacity-10 text-info border border-info border-opacity-25 d-flex align-items-center">
                    <i class="fa-solid fa-circle-info fa-lg me-3"></i>
                    <div>
                        <strong>No students found!</strong><br>
                        There are no active students in the selected class and section for this academic year.
                    </div>
                </div>
            </div>
        @endif

        <!-- Promotion History/Logs -->
        @if (isset($logs) && $logs->count() > 0)
            <div class="col-12">
                <div class="card border-0 shadow-sm ">
                    <div class="card-header  border-secondary border-opacity-50 py-3">
                        <h6 class="fw-bold m-0 "><i
                                class="fa-solid fa-clock-rotate-left me-2 text-info"></i>Recent Promotions & Rollbacks</h6>
                    </div>
                    <div class="card-body  p-0">
                        <div class="table-responsive">
                            <table class="table  table-hover table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                        <tr>
                                            <td>{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <span
                                                    class="fw-bold ">{{ $log->student->user->name ?? 'N/A' }}</span><br>
                                                <small class="text-secondary">Adm:
                                                    {{ $log->student->admission_no ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <span class="">{{ $log->oldClass->name ?? 'N/A' }}
                                                    ({{ $log->oldSection->name ?? 'N/A' }})</span><br>
                                                <small class="text-secondary">Session:
                                                    {{ $log->oldSession->name ?? 'N/A' }} | Roll:
                                                    {{ $log->old_roll_no }}</small>
                                            </td>
                                            <td>
                                                <span class="">{{ $log->newClass->name ?? 'N/A' }}
                                                    ({{ $log->newSection->name ?? 'N/A' }})</span><br>
                                                <small class="text-secondary">Session:
                                                    {{ $log->newSession->name ?? 'N/A' }} | Roll:
                                                    {{ $log->new_roll_no }}</small>
                                            </td>
                                            <td>
                                                @if ($log->status == 'promoted')
                                                    <span
                                                        class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Promoted</span>
                                                @elseif($log->status == 'rolled_back')
                                                    <span
                                                        class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Rolled
                                                        Back</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($log->status == 'promoted' && Auth::user()->can('student_promotion.rollback'))
                                                    <form
                                                        action="{{ route('admin.students.promotion.rollback', $log->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to rollback this promotion? The student will be restored to their previous class and their new enrollment will be deleted.');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger"><i
                                                                class="fa-solid fa-rotate-left me-1"></i>Rollback</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Confirmation Modal -->
    @if (isset($students) && $students->count() > 0)
        <div class="modal fade" id="confirmPromotionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content  border border-secondary border-opacity-50">
                    <div class="modal-header border-secondary border-opacity-25">
                        <h5 class="modal-title  fw-bold"><i
                                class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Confirm Promotion</h5>
                        <button type="button" class="btn-close " data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body ">
                        <div class="alert bg-primary bg-opacity-10 text-primary border-0 mb-4">
                            <p class="mb-2">You are about to promote selected students.</p>
                            <div class="row text-center mb-2">
                                <div class="col-5">
                                    <strong>Class {{ request('from_class_id') }}</strong><br>
                                    <small>Session: {{ request('from_session_id') }}</small>
                                </div>
                                <div class="col-2 d-flex align-items-center justify-content-center">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div class="col-5">
                                    <strong>Class {{ request('to_class_id') }}</strong><br>
                                    <small>Session: {{ request('to_session_id') }}</small>
                                </div>
                            </div>
                        </div>

                        <ul class="list-group list-group-flush mb-0">
                            <li
                                class="list-group-item bg-transparent  border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                Selected Students to Promote
                                <span class="badge bg-primary rounded-pill fs-6" id="selectedCountBadge">0</span>
                            </li>
                            <li
                                class="list-group-item bg-transparent  border-secondary border-opacity-25 border-bottom-0 pb-0 pt-3">
                                <div class="d-flex align-items-center text-info mb-2">
                                    <i class="fa-solid fa-circle-info me-2"></i>
                                    <small>Roll numbers will be automatically assigned starting from the next available
                                        number in the target class.</small>
                                </div>
                                <div class="d-flex align-items-center text-success">
                                    <i class="fa-solid fa-clock-rotate-left me-2"></i>
                                    <small>Academic history will be perfectly preserved. You can rollback if you make a
                                        mistake.</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-25">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success"
                            onclick="document.getElementById('promotionForm').submit();">
                            <i class="fa-solid fa-check me-2"></i>Confirm Promotion
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.student-checkbox');
            const badge = document.getElementById('selectedCountBadge');

            function updateCount() {
                if (badge) {
                    const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
                    badge.textContent = checkedCount;
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    checkboxes.forEach(cb => cb.checked = this.checked);
                    updateCount();
                });

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', function() {
                        const allChecked = document.querySelectorAll('.student-checkbox:checked')
                            .length === checkboxes.length;
                        selectAll.checked = allChecked;
                        updateCount();
                    });
                });

                // Initial count
                updateCount();
            }

            // Initialize dynamic sections
            updateSections('from');
            updateSections('to');
        });

        function handleFromClassChange() {
            updateSections('from');
            
            const fromClassSelect = document.getElementById('from_class_id');
            const toClassSelect = document.getElementById('to_class_id');
            const selectedIndex = fromClassSelect.selectedIndex;
            
            // Show all options first
            Array.from(toClassSelect.options).forEach(option => {
                option.style.display = '';
                option.hidden = false;
            });
            
            if (selectedIndex > 0) {
                // If it's not the last option, next class is index + 1. If it is the last, keep it same.
                const nextIndex = selectedIndex < fromClassSelect.options.length - 1 ? selectedIndex + 1 : selectedIndex;
                
                // Hide all options except the target one
                Array.from(toClassSelect.options).forEach((option, index) => {
                    if (index !== nextIndex) {
                        option.style.display = 'none';
                        option.hidden = true;
                    }
                });
                
                toClassSelect.selectedIndex = nextIndex;
            } else {
                toClassSelect.selectedIndex = 0;
            }

            updateSections('to');
        }

        const classSections = {
            @foreach ($classes as $class)
                "{{ $class->id }}": [
                    @foreach ($class->sections as $section)
                        {
                            id: "{{ $section->id }}",
                            name: "{{ $section->name }}"
                        },
                    @endforeach
                ],
            @endforeach
        };

        function updateSections(prefix) {
            const classId = document.getElementById(prefix + '_class_id').value;
            const sectionSelect = document.getElementById(prefix + '_section_id');
            const selectedValue = sectionSelect.getAttribute('data-selected');

            sectionSelect.innerHTML = '<option value="">Select</option>';

            if (classId && classSections[classId]) {
                classSections[classId].forEach(function(section) {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.text = section.name;
                    if (selectedValue == section.id) {
                        option.selected = true;
                    }
                    sectionSelect.add(option);
                });
            }
        }
    </script>
@endpush
