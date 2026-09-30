@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Homework</h4>
    <a href="{{ route('teacher.homework.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card glass-card border-0 p-4">
    <form action="{{ route('teacher.homework.update', $homework->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required value="{{ old('title', $homework->title) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date *</label>
                <input type="date" name="due_date" class="form-control" required value="{{ old('due_date', $homework->due_date ? $homework->due_date->format('Y-m-d') : '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Max Marks</label>
                <input type="number" name="max_marks" class="form-control" value="{{ old('max_marks', $homework->max_marks) }}">
            </div>

            <div class="col-md-12">
                <label class="form-label">Attachment (Update file or leave empty to keep current)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                @if($homework->file_path)
                    <div class="mt-2 text-info fs-7"><i class="fa-solid fa-paperclip me-1"></i> Current file: {{ basename($homework->file_path) }}</div>
                @endif
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $homework->description) }}</textarea>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-2"></i>Update Homework</button>
            </div>
        </div>
    </form>
</div>
@endsection
