@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Study Material</h4>
    <a href="{{ route('teacher.study-materials.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card glass-card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('teacher.study-materials.update', $studyMaterial->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                    <select name="session_id" class="form-select" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}" {{ $studyMaterial->session_id == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required>
                        <option value="">Select Class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $studyMaterial->class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select name="section_id" id="section_id" class="form-select" required>
                        <option value="">Select Section</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" data-class-id="{{ $s->class_id }}" {{ $studyMaterial->section_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}" {{ $studyMaterial->subject_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-9">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="{{ old('title', $studyMaterial->title) }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="material_type" class="form-select" required onchange="toggleFileLink()">
                        <option value="document" {{ $studyMaterial->type == 'document' ? 'selected' : '' }}>Document</option>
                        <option value="pdf" {{ $studyMaterial->type == 'pdf' ? 'selected' : '' }}>PDF</option>
                        <option value="image" {{ $studyMaterial->type == 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ $studyMaterial->type == 'video' ? 'selected' : '' }}>Video (File)</option>
                        <option value="link" {{ $studyMaterial->type == 'link' ? 'selected' : '' }}>External Link / Youtube</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $studyMaterial->description) }}</textarea>
                </div>
                
                <div class="col-md-12" id="file_group">
                    <label class="form-label">Update File (Leave empty to keep current file)</label>
                    <input type="file" name="file" class="form-control">
                    <small class="text-muted">Max size 10MB.</small>
                    @if($studyMaterial->file_path && !filter_var($studyMaterial->file_path, FILTER_VALIDATE_URL))
                        <div class="mt-2 text-info fs-7"><i class="fa-solid fa-paperclip me-1"></i> Current file: {{ basename($studyMaterial->file_path) }}</div>
                    @endif
                </div>
                
                <div class="col-md-12 d-none" id="link_group">
                    <label class="form-label">External Link URL</label>
                    <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link', filter_var($studyMaterial->file_path, FILTER_VALIDATE_URL) ? $studyMaterial->file_path : '') }}">
                </div>
                
                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill"><i class="fa-solid fa-save me-2"></i>Update Study Material</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleFileLink() {
        const type = document.getElementById('material_type').value;
        const fileGroup = document.getElementById('file_group');
        const linkGroup = document.getElementById('link_group');
        
        if (type === 'link') {
            fileGroup.classList.add('d-none');
            linkGroup.classList.remove('d-none');
        } else {
            fileGroup.classList.remove('d-none');
            linkGroup.classList.add('d-none');
        }
    }
    
    // Store original sections for filtering
    let originalSections = [];
    
    // Run on load to set correct state
    document.addEventListener('DOMContentLoaded', () => {
        toggleFileLink();
        
        const classSelect = document.getElementById('class_id');
        const sectionSelect = document.getElementById('section_id');
        
        // Save original options
        originalSections = Array.from(sectionSelect.options).map(opt => ({
            value: opt.value,
            text: opt.text,
            classId: opt.getAttribute('data-class-id')
        })).filter(opt => opt.classId); // Only keep real sections
        
        // Function to filter
        const filterSections = function() {
            const classId = classSelect.value;
            const currentSelected = sectionSelect.value; // Remember selection if possible
            
            // Clear dropdown
            sectionSelect.innerHTML = '';
            
            // Add default
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.text = classId ? 'Select Section' : 'Select Class First';
            sectionSelect.appendChild(defaultOpt);
            
            if (!classId) {
                sectionSelect.disabled = true;
                return;
            }
            
            sectionSelect.disabled = false;
            let hasSections = false;
            
            // Add filtered options
            originalSections.forEach(opt => {
                if (opt.classId === classId) {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.text = opt.text;
                    newOpt.setAttribute('data-class-id', opt.classId);
                    
                    // Keep previously selected section if it matches the class
                    if (opt.value === currentSelected || (currentSelected === '' && opt.value === "{{ $studyMaterial->section_id }}")) {
                        newOpt.selected = true;
                    }
                    
                    sectionSelect.appendChild(newOpt);
                    hasSections = true;
                }
            });
            
            if (!hasSections) {
                defaultOpt.text = 'No Sections Found';
                sectionSelect.disabled = true;
            }
        };
        
        // Trigger filter on change
        classSelect.addEventListener('change', filterSections);
        
        // Trigger initial filter on load
        filterSections();
    });
</script>
@endsection
