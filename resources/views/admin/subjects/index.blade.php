@extends('layouts.app')

@section('content')
<div class="container-fluid p-0">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-light"><i class="fa-solid fa-book text-primary me-2"></i>Subject Management</h4>
            <p class="text-muted fs-7 mb-0">Manage all subjects and assign them to classes.</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_academic'))
<button class="btn btn-primary px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal" style="border-radius: 8px;">
            <i class="fa-solid fa-plus me-2"></i> Add Subject
        </button>
@endif
    </div>

    <!-- Subject Table -->
    <div class="card glass-card border shadow-sm rounded-4">
        <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 p-4 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-light">All Subjects</h6>
            <span class="badge bg-primary bg-opacity-25 text-primary rounded-pill px-3 py-2 fs-7">{{ $subjects->count() }} Total</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle text-light mb-0" style="--bs-table-bg: transparent; --bs-table-border-color: rgba(255,255,255,0.05);">
                <thead style="background-color: rgba(0,0,0,0.2);">
                    <tr>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0">#</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Subject Name</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Code</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Type</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0">Assigned Classes</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subjects as $i => $subject)
                        <tr class="border-bottom border-secondary border-opacity-10">
                            <td class="px-4 py-3 text-muted">{{ $i + 1 }}</td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-3 d-flex justify-content-center align-items-center fw-bold me-3" style="width: 36px; height: 36px; font-size: 0.8rem;">
                                        {{ strtoupper(substr($subject->name, 0, 2)) }}
                                    </div>
                                    <div class="fw-semibold text-light">{{ $subject->name }}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-25 rounded-pill px-2 py-1 fs-7">{{ $subject->code }}</span>
                            </td>
                            <td>
                                @if($subject->type === 'theory')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-2 py-1 fs-7">Theory</span>
                                @else
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-2 py-1 fs-7">Practical</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($subject->classes as $cls)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 fs-8">{{ $cls->name }}</span>
                                    @empty
                                        <span class="text-muted fs-7">Not assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-end px-4">
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_academic'))
<button type="button" class="btn btn-sm btn-outline-primary me-1" style="border-radius: 8px;" title="Edit"
                                    onclick="openEditModal({{ $subject->id }}, '{{ addslashes($subject->name) }}', '{{ addslashes($subject->code) }}', '{{ $subject->type }}', {{ json_encode($subject->classes->pluck('id')->toArray()) }})">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
@endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_academic'))
<form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
@endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-book-open fs-1 mb-3 d-block opacity-50"></i>
                                No subjects found. Click "Add Subject" to create one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Subject Modal -->
<div class="modal fade" id="addSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background: var(--bs-body-bg);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4">
                <h5 class="modal-title fw-bold text-light"><i class="fa-solid fa-plus-circle text-primary me-2"></i>Add New Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subjects.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7 fw-semibold">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control bg-body text-body border-secondary border-opacity-50" placeholder="e.g. Mathematics" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7 fw-semibold">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control bg-body text-body border-secondary border-opacity-50" placeholder="e.g. MATH101" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-light fs-7 fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select bg-body text-body border-secondary border-opacity-50" required>
                                <option value="theory">Theory</option>
                                <option value="practical">Practical</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-light fs-7 fw-semibold">Assign to Classes <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2 p-3 rounded-3 border border-secondary border-opacity-25" style="background: rgba(0,0,0,0.1);">
                                @foreach($classes as $class)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="class_ids[]" value="{{ $class->id }}" id="class_{{ $class->id }}">
                                        <label class="form-check-label text-light fs-7" for="class_{{ $class->id }}">{{ $class->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm" style="border-radius: 8px;"><i class="fa-solid fa-save me-2"></i>Save Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Subject Modal -->
<div class="modal fade" id="editSubjectModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; background: var(--bs-body-bg);">
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 pt-4">
                <h5 class="modal-title fw-bold text-light"><i class="fa-solid fa-pen text-primary me-2"></i>Edit Subject</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editSubjectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7 fw-semibold">Subject Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editSubjectName" class="form-control bg-body text-body border-secondary border-opacity-50" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-light fs-7 fw-semibold">Subject Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" id="editSubjectCode" class="form-control bg-body text-body border-secondary border-opacity-50" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-light fs-7 fw-semibold">Type <span class="text-danger">*</span></label>
                            <select name="type" id="editSubjectType" class="form-select bg-body text-body border-secondary border-opacity-50" required>
                                <option value="theory">Theory</option>
                                <option value="practical">Practical</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-light fs-7 fw-semibold">Assign to Classes <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2 p-3 rounded-3 border border-secondary border-opacity-25" style="background: rgba(0,0,0,0.1);">
                                @foreach($classes as $class)
                                    <div class="form-check">
                                        <input class="form-check-input edit-class-checkbox" type="checkbox" name="class_ids[]" value="{{ $class->id }}" id="edit_class_{{ $class->id }}">
                                        <label class="form-check-label text-light fs-7" for="edit_class_{{ $class->id }}">{{ $class->name }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25 px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm text-white" style="border-radius: 8px;"><i class="fa-solid fa-save me-2"></i>Update Subject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditModal(id, name, code, type, classIds) {
        document.getElementById('editSubjectForm').action = '{{ url('admin/subjects') }}/' + id;
        document.getElementById('editSubjectName').value = name;
        document.getElementById('editSubjectCode').value = code;
        document.getElementById('editSubjectType').value = type;

        document.querySelectorAll('.edit-class-checkbox').forEach(cb => {
            cb.checked = classIds.includes(parseInt(cb.value));
        });

        new bootstrap.Modal(document.getElementById('editSubjectModal')).show();
    }
</script>
@endsection
