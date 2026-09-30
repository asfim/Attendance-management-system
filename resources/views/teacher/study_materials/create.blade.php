@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold"><i class="fa-solid fa-plus text-primary me-2"></i>Upload Study Material</h4>
    <a href="{{ route('teacher.study-materials.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-2"></i>Back</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('teacher.study-materials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required value="{{ old('title') }}">
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Academic Session <span class="text-danger">*</span></label>
                    <select name="session_id" class="form-select" required>
                        <option value="">Select Session</option>
                        @foreach($sessions as $session)
                            <option value="{{ $session->id }}">{{ $session->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Class <span class="text-danger">*</span></label>
                    <select name="class_id" id="class_id" class="form-select" required onchange="filterSections()">
                        <option value="">Select Class</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Section <span class="text-danger">*</span></label>
                    <select name="section_id" id="section_id" class="form-select" required disabled>
                        <option value="">Select Class First</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" data-class-id="{{ $s->class_id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                    <select name="subject_id" class="form-select" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $sub)
                            <option value="{{ $sub->id }}">{{ $sub->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Type <span class="text-danger">*</span></label>
                    <select name="type" id="material_type" class="form-select" required onchange="toggleFileLink()">
                        <option value="document">Document</option>
                        <option value="pdf">PDF</option>
                        <option value="image">Image</option>
                        <option value="video">Video (File)</option>
                        <option value="link">External Link / Youtube</option>
                    </select>
                </div>
                
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                
                <div class="col-md-12" id="file_group">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="file" class="form-control">
                    <small class="text-muted">Max size 10MB</small>
                </div>
                
                <div class="col-md-12 d-none" id="link_group">
                    <label class="form-label">External Link URL</label>
                    <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link') }}">
                </div>
                
                <div class="col-12 mt-4 text-end">
                    <button type="submit" class="btn btn-primary px-4 rounded-pill"><i class="fa-solid fa-cloud-arrow-up me-2"></i>Upload Study Material</button>
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
        
        classSelect.addEventListener('change', function() {
            const classId = this.value;
            
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
                    sectionSelect.appendChild(newOpt);
                    hasSections = true;
                }
            });
            
            if (!hasSections) {
                defaultOpt.text = 'No Sections Found';
                sectionSelect.disabled = true;
            }
        });
        
        // Trigger initial filter if class is already selected
        if (classSelect.value) {
            classSelect.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection
