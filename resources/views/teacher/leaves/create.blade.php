@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('teacher.leaves.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
        <i class="fa-solid fa-arrow-left me-1"></i>Back to My Leaves
    </a>
    <h4 class="fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Apply for Leave</h4>
</div>

<div class="card glass-card border-0 max-w-800 mx-auto">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('teacher.leaves.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <div class="mb-4">
                <label class="form-label text-secondary fw-medium">Leave Type <span class="text-danger">*</span></label>
                <select name="leave_type_id" class="form-select form-select-lg bg-light border-0" required>
                    <option value="">Select leave type</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} (Max: {{ $type->max_days }} days)</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <label class="form-label text-secondary fw-medium">Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" class="form-control form-control-lg bg-light border-0" required min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label text-secondary fw-medium">End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" class="form-control form-control-lg bg-light border-0" required min="{{ date('Y-m-d') }}">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label text-secondary fw-medium">Reason <span class="text-danger">*</span></label>
                <textarea name="reason" rows="4" class="form-control bg-light border-0" required placeholder="State the reason for your leave..."></textarea>
            </div>
            
            <div class="mb-4">
                <label class="form-label text-secondary fw-medium">Attachment (Optional, e.g., Medical Certificate)</label>
                <input type="file" name="file" class="form-control form-control-lg bg-light border-0" accept=".pdf,.jpg,.png">
            </div>

            <div class="text-end mt-5">
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold">Submit Application</button>
            </div>
        </form>
    </div>
</div>
@endsection
