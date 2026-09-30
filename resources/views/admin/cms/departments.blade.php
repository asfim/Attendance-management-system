@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-building-columns me-2"></i> Departments Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('admin.cms.departments.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        
                        <!-- Hero Section -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Hero Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Badge Text</label>
                            <input type="text" name="departments_hero_badge" class="form-control" value="{{ $settings['departments_hero_badge'] ?? 'Areas of Study' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Background Image</label>
                            <input type="file" name="departments_hero_bg" class="form-control" accept="image/*">
                            @if(isset($settings['departments_hero_bg']))
                                <img src="{{ $settings['departments_hero_bg'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title Line 1</label>
                            <input type="text" name="departments_hero_title1" class="form-control" value="{{ $settings['departments_hero_title1'] ?? 'Academic' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title Line 2 (Gold Text)</label>
                            <input type="text" name="departments_hero_title2" class="form-control" value="{{ $settings['departments_hero_title2'] ?? 'Departments' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Hero Subtitle</label>
                            <textarea name="departments_hero_subtitle" class="form-control" rows="2">{{ $settings['departments_hero_subtitle'] ?? 'Explore our diverse academic departments, offering comprehensive curricula designed to equip students with knowledge and skills for the future.' }}</textarea>
                        </div>

                        <!-- Departments List -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Departments List</h6></div>
                        
                        @php
                            $departments = json_decode($settings['departments_list'] ?? '[]', true);
                            if (empty($departments)) {
                                $departments = [
                                    [
                                        'icon' => 'bi-rocket-takeoff',
                                        'title' => 'Department of Science',
                                        'desc' => 'Fostering analytical thinking and innovation through hands-on laboratory experiences and theoretical physics, chemistry, and biology.',
                                        'subject1' => 'Physics', 'subject2' => 'Chemistry', 'subject3' => 'Biology', 'subject4' => 'Adv. Math'
                                    ],
                                    [
                                        'icon' => 'bi-bar-chart-fill',
                                        'title' => 'Department of Commerce',
                                        'desc' => 'Preparing future business leaders with a strong foundation in economics, accounting, and modern business management strategies.',
                                        'subject1' => 'Accounting', 'subject2' => 'Economics', 'subject3' => 'Finance', 'subject4' => 'Business Org.'
                                    ]
                                ];
                            }
                        @endphp

                        <div class="col-12">
                            <div id="departments-container" class="row">
                                @foreach($departments as $index => $dept)
                                    <div class="col-md-4 dept-item mb-3">
                                        <div class="card bg-light border-0 h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 text-secondary">Department <span class="dept-number">{{ $index + 1 }}</span></h6>
                                                    <button type="button" class="btn btn-sm btn-danger remove-dept"><i class="fa-solid fa-times"></i></button>
                                                </div>
                                                <label class="form-label text-secondary small">Icon Class (e.g. bi-rocket-takeoff)</label>
                                                <input type="text" name="departments_list[{{ $index }}][icon]" class="form-control form-control-sm mb-2" value="{{ $dept['icon'] ?? 'bi-book' }}" required>
                                                
                                                <label class="form-label text-secondary small">Department Title</label>
                                                <input type="text" name="departments_list[{{ $index }}][title]" class="form-control form-control-sm mb-2" value="{{ $dept['title'] ?? '' }}" required>
                                                
                                                <label class="form-label text-secondary small">Description</label>
                                                <textarea name="departments_list[{{ $index }}][desc]" class="form-control form-control-sm mb-2" rows="3" required>{{ $dept['desc'] ?? '' }}</textarea>
                                                
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <label class="form-label text-secondary small">Subject 1</label>
                                                        <input type="text" name="departments_list[{{ $index }}][subject1]" class="form-control form-control-sm" value="{{ $dept['subject1'] ?? '' }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label text-secondary small">Subject 2</label>
                                                        <input type="text" name="departments_list[{{ $index }}][subject2]" class="form-control form-control-sm" value="{{ $dept['subject2'] ?? '' }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label text-secondary small">Subject 3</label>
                                                        <input type="text" name="departments_list[{{ $index }}][subject3]" class="form-control form-control-sm" value="{{ $dept['subject3'] ?? '' }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label text-secondary small">Subject 4</label>
                                                        <input type="text" name="departments_list[{{ $index }}][subject4]" class="form-control form-control-sm" value="{{ $dept['subject4'] ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-dept" class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-plus me-1"></i> Add Department</button>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Departments Page Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let deptIndex = {{ isset($departments) ? count($departments) : 0 }};
    document.getElementById('add-dept').addEventListener('click', function() {
        let container = document.getElementById('departments-container');
        let html = `
            <div class="col-md-4 dept-item mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-secondary">Department <span class="dept-number">${deptIndex + 1}</span></h6>
                            <button type="button" class="btn btn-sm btn-danger remove-dept"><i class="fa-solid fa-times"></i></button>
                        </div>
                        <label class="form-label text-secondary small">Icon Class (e.g. bi-book)</label>
                        <input type="text" name="departments_list[${deptIndex}][icon]" class="form-control form-control-sm mb-2" value="bi-book" required>
                        
                        <label class="form-label text-secondary small">Department Title</label>
                        <input type="text" name="departments_list[${deptIndex}][title]" class="form-control form-control-sm mb-2" required>
                        
                        <label class="form-label text-secondary small">Description</label>
                        <textarea name="departments_list[${deptIndex}][desc]" class="form-control form-control-sm mb-2" rows="3" required></textarea>
                        
                        <div class="row g-1">
                            <div class="col-6">
                                <label class="form-label text-secondary small">Subject 1</label>
                                <input type="text" name="departments_list[${deptIndex}][subject1]" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small">Subject 2</label>
                                <input type="text" name="departments_list[${deptIndex}][subject2]" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small">Subject 3</label>
                                <input type="text" name="departments_list[${deptIndex}][subject3]" class="form-control form-control-sm">
                            </div>
                            <div class="col-6">
                                <label class="form-label text-secondary small">Subject 4</label>
                                <input type="text" name="departments_list[${deptIndex}][subject4]" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        deptIndex++;
        updateDeptNumbers();
    });

    document.getElementById('departments-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-dept')) {
            e.target.closest('.dept-item').remove();
            updateDeptNumbers();
        }
    });

    function updateDeptNumbers() {
        let items = document.querySelectorAll('.dept-item');
        items.forEach((item, index) => {
            item.querySelector('.dept-number').textContent = index + 1;
        });
    }
</script>
@endpush
