@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold text-light"><i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Submissions for {{ $homework->title }}</h4>
    <a href="{{ route('teacher.homework.index') }}" class="btn btn-outline-light"><i class="fa-solid fa-arrow-left me-2"></i>Back to Homework</a>
</div>

<div class="card glass-card border-0 mb-4 rounded-4 shadow-sm">
    <div class="card-body p-4">
        <div class="row text-light">
            <div class="col-md-3"><strong>Class:</strong> {{ $homework->schoolClass->name ?? '' }} - {{ $homework->section->name ?? '' }}</div>
            <div class="col-md-3"><strong>Subject:</strong> {{ $homework->subject->name ?? '' }}</div>
            <div class="col-md-3"><strong>Due Date:</strong> {{ $homework->due_date->format('d M, Y') }}</div>
            <div class="col-md-3"><strong>Max Marks:</strong> {{ $homework->max_marks ?? 'N/A' }}</div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 rounded-4 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-light" style="--bs-table-bg: transparent; --bs-table-hover-bg: rgba(255,255,255,0.05); --bs-table-color: #f8fafc;">
                <thead style="background-color: rgba(0,0,0,0.2);">
                    <tr>
                        <th class="py-3 px-4 border-0 text-muted text-uppercase fs-7">Student</th>
                        <th class="py-3 border-0 text-muted text-uppercase fs-7">File / Remarks</th>
                        <th class="py-3 px-4 border-0 text-muted text-uppercase fs-7" style="width: 350px;">Evaluation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $sub)
                    <tr class="border-bottom border-secondary border-opacity-25">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                @if($sub->student->photo_path)
                                    <img src="{{ asset('storage/' . $sub->student->photo_path) }}" class="rounded-circle me-3 object-fit-cover border border-secondary border-opacity-50" style="width: 44px; height: 44px;" alt="Avatar">
                                @else
                                    <div class="rounded-circle me-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-25 text-primary fw-bold border border-primary border-opacity-50" style="width: 44px; height: 44px;">
                                        {{ strtoupper(substr($sub->student->user->name ?? 'S', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $sub->student->user->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-7">Roll: {{ $sub->student->roll_no ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            @if($sub->file_path)
                                <a href="{{ asset('storage/' . $sub->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info mb-2 rounded-pill px-3"><i class="fa-solid fa-download me-1"></i> Download File</a>
                            @else
                                <span class="text-muted fs-7 d-block mb-2">No file attached</span>
                            @endif
                            
                            @if($sub->student_remarks)
                                <div class="fs-7 text-muted border-start border-3 border-info ps-2 mb-2">"{{ $sub->student_remarks }}"</div>
                            @endif
                            <div class="fs-8 text-muted mt-1"><i class="fa-solid fa-clock me-1"></i>Submitted: {{ $sub->created_at->format('d M, Y h:i A') }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <form action="{{ route('teacher.homework.evaluate', $sub->id) }}" method="POST" class="bg-dark bg-opacity-50 border border-secondary border-opacity-50 p-3 rounded-3 shadow-sm">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-5">
                                        <label class="fs-8 text-muted mb-1">Marks</label>
                                        <input type="number" name="marks" class="form-control form-control-sm bg-body text-body border-secondary border-opacity-50" value="{{ $sub->marks }}" min="0" max="{{ $homework->max_marks }}" step="0.01">
                                    </div>
                                    <div class="col-7">
                                        <label class="fs-8 text-muted mb-1">Status</label>
                                        <select name="status" class="form-select form-select-sm bg-body text-body border-secondary border-opacity-50">
                                            <option value="pending" {{ $sub->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="evaluated" {{ $sub->status == 'evaluated' ? 'selected' : '' }}>Evaluated</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="fs-8 text-muted mb-1">Teacher Remarks</label>
                                    <input type="text" name="teacher_remarks" class="form-control form-control-sm bg-body text-body border-secondary border-opacity-50" value="{{ $sub->teacher_remarks }}" placeholder="Optional">
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-sm btn-primary py-1 px-3 rounded-pill"><i class="fa-solid fa-check me-1"></i>Save</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted py-5">
                            <i class="fa-solid fa-folder-open fs-2 mb-3 d-block opacity-50"></i>
                            No submissions yet for this homework.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
