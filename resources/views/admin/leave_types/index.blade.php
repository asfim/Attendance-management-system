@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-tags text-primary me-2"></i>Leave Types</h4>
    <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addLeaveTypeModal">
        <i class="fa-solid fa-plus me-2"></i>Add Leave Type
    </button>
</div>

<div class="card glass-card border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Applicable To</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveTypes as $type)
                    <tr>
                        <td class="fw-medium">{{ $type->name }}</td>
                        <td>
                            <span class="badge bg-info text-uppercase">{{ $type->applicable_to }}</span>
                        </td>
                        <td class="text-end">
                            <form action="{{ route('admin.leave-types.destroy', $type->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this leave type?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"><i class="fa-regular fa-trash-can"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No leave types found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addLeaveTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Add Leave Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.leave-types.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-medium">Leave Name</label>
                        <input type="text" name="name" class="form-control form-control-lg bg-light border-0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary fw-medium">Applicable To</label>
                        <select name="applicable_to" class="form-select form-select-lg bg-light border-0" required>
                            <option value="both">Staff & Students</option>
                            <option value="staff">Staff Only</option>
                            <option value="student">Students Only</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Save Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
