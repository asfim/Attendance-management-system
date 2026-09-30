@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-plus text-primary me-2"></i>Create Homework</h4>
    <a href="{{ route('teacher.homework.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card glass-card border-0 p-4">
    <form action="{{ route('teacher.homework.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Academic Session *</label>
                <select name="session_id" class="form-select" required>
                    <option value="">Select Session</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}">{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Class *</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select Class</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Section *</label>
                <select name="section_id" class="form-select" required>
                    <option value="">Select Section</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" data-class-id="{{ $sec->class_id }}">{{ $sec->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Subject *</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">Select Subject</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required placeholder="E.g., Math Chapter 5 Exercises">
            </div>
            <div class="col-md-3">
                <label class="form-label">Homework Date *</label>
                <input type="date" name="homework_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Due Date *</label>
                <input type="date" name="due_date" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Max Marks</label>
                <input type="number" name="max_marks" class="form-control" placeholder="E.g., 10">
            </div>
            <div class="col-md-8">
                <label class="form-label">Attachment (Optional)</label>
                <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
            </div>

            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Detailed instructions..."></textarea>
            </div>

            <div class="col-12 mt-4 text-end">
                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-2"></i>Save Homework</button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const classSelect = document.querySelector('select[name="class_id"]');
        const sectionSelect = document.querySelector('select[name="section_id"]');
        
        if (classSelect && sectionSelect) {
            // Store original options
            const originalOptions = Array.from(sectionSelect.options).map(opt => ({
                value: opt.value,
                text: opt.text,
                classId: opt.getAttribute('data-class-id')
            }));

            classSelect.addEventListener('change', function () {
                const selectedClassId = this.value;
                
                // Clear current options
                sectionSelect.innerHTML = '';
                
                // Add back default option
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.text = 'Select Section';
                sectionSelect.appendChild(defaultOption);
                
                // Filter and add relevant options
                if (selectedClassId) {
                    const filteredOptions = originalOptions.filter(opt => opt.classId === selectedClassId && opt.value !== '');
                    filteredOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.value;
                        option.text = opt.text;
                        option.setAttribute('data-class-id', opt.classId);
                        sectionSelect.appendChild(option);
                    });
                }
            });
            
            // Trigger change on load if a class is already selected
            if (classSelect.value) {
                classSelect.dispatchEvent(new Event('change'));
            }
        }
    });
</script>
@endpush
@endsection
