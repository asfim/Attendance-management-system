@extends('layouts.app')

@section('content')
<style>
    /* Dark Mode & Theme compatibility overrides for gallery cards */
    .gallery-item-card {
        background-color: var(--bs-body-bg, #f8f9fa);
        border: 1px solid var(--bs-border-color, #e2e8f0);
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    [data-bs-theme="dark"] .gallery-item-card {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .gallery-item-card label {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .gallery-item-card .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }
    [data-bs-theme="dark"] .gallery-item-card .preview-box {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .gallery-item-card .form-control::placeholder {
        color: #64748b !important;
        opacity: 0.8;
    }
</style>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-images me-2"></i> Gallery Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form action="{{ route('admin.cms.gallery.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <!-- Hero Section Settings -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-heading me-1"></i> Hero Section Settings</h6></div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Hero Title</label>
                            <input type="text" name="gallery_hero_title" class="form-control" value="{{ $settings['gallery_hero_title'] ?? 'Photo Gallery' }}" placeholder="Photo Gallery">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Hero Subtitle</label>
                            <input type="text" name="gallery_hero_subtitle" class="form-control" value="{{ $settings['gallery_hero_subtitle'] ?? 'Explore the vibrant life, state-of-the-art facilities, and memorable events that make our institution a premier center for learning.' }}" placeholder="Subtitle text...">
                        </div>

                        <!-- Gallery Items Section Header -->
                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <div>
                                    <h6 class="text-secondary fw-semibold mb-0"><i class="fa-solid fa-photo-film me-1"></i> Dynamic Photo Gallery Items</h6>
                                    <small class="text-muted">Add photos with custom categories (e.g. Campus, Academics, Sports, Events, Cultural) to generate dynamic gallery tabs on the website.</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-success px-3" id="addGalleryBtn"><i class="fa-solid fa-plus me-1"></i> Add Photo</button>
                            </div>
                        </div>

                        <!-- Gallery Container -->
                        <div class="col-12">
                            <div class="row g-3" id="galleryContainer">
                                @php
                                    $gallery = json_decode($settings['gallery_data'] ?? '[]', true);
                                    if (empty($gallery)) {
                                        for ($i = 1; $i <= 12; $i++) {
                                            if (!empty($settings['gallery_image_'.$i])) {
                                                $gallery[] = [
                                                    'image' => $settings['gallery_image_'.$i],
                                                    'title' => 'Photo ' . $i,
                                                    'category' => 'Campus',
                                                    'size' => 'normal',
                                                ];
                                            }
                                        }
                                    }
                                @endphp

                                @foreach($gallery as $index => $item)
                                    <div class="col-md-6 col-lg-4 gallery-item" data-index="{{ $index }}">
                                        <div class="p-3 rounded-3 position-relative gallery-item-card shadow-sm">
                                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-gallery-btn" aria-label="Delete"></button>
                                            <input type="hidden" name="gallery[{{ $index }}][delete]" class="delete-input" value="0">
                                            <input type="hidden" name="gallery[{{ $index }}][old_image]" value="{{ $item['image'] ?? '' }}">

                                            <div class="mb-2">
                                                <label class="form-label fw-semibold small mb-1">Image File</label>
                                                <input type="file" name="gallery[{{ $index }}][image]" class="form-control form-control-sm" accept="image/*">
                                                @if(!empty($item['image']))
                                                    <div class="mt-2 text-center p-1 rounded border preview-box">
                                                        <img src="{{ $item['image'] }}" class="img-fluid rounded" style="height:100px; object-fit:cover; width:100%;" alt="Preview">
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label fw-semibold small mb-1">Photo Title / Caption</label>
                                                <input type="text" name="gallery[{{ $index }}][title]" class="form-control form-control-sm" placeholder="e.g. Main Campus Building" value="{{ $item['title'] ?? '' }}">
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold small mb-1">Category</label>
                                                    <input type="text" name="gallery[{{ $index }}][category]" class="form-control form-control-sm" list="categoryOptions" placeholder="Category" value="{{ $item['category'] ?? 'General' }}">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold small mb-1">Grid Display Size</label>
                                                    <select name="gallery[{{ $index }}][size]" class="form-select form-select-sm">
                                                        <option value="normal" {{ ($item['size'] ?? 'normal') == 'normal' ? 'selected' : '' }}>Standard</option>
                                                        <option value="wide" {{ ($item['size'] ?? '') == 'wide' ? 'selected' : '' }}>Wide (2 col)</option>
                                                        <option value="tall" {{ ($item['size'] ?? '') == 'tall' ? 'selected' : '' }}>Tall (2 row)</option>
                                                        <option value="big" {{ ($item['size'] ?? '') == 'big' ? 'selected' : '' }}>Big (2x2)</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Preset Category Datalist -->
                        <datalist id="categoryOptions">
                            <option value="Campus">
                            <option value="Academics">
                            <option value="Sports">
                            <option value="Events">
                            <option value="Cultural">
                        </datalist>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Gallery Settings</button>
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
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('galleryContainer');
    const addBtn = document.getElementById('addGalleryBtn');
    let itemIndex = {{ count($gallery) > 0 ? max(array_keys($gallery)) + 1 : 0 }};

    addBtn.addEventListener('click', function() {
        const html = `
            <div class="col-md-6 col-lg-4 gallery-item" data-index="${itemIndex}">
                <div class="p-3 rounded-3 position-relative gallery-item-card shadow-sm">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-gallery-btn" aria-label="Delete"></button>
                    <input type="hidden" name="gallery[${itemIndex}][delete]" class="delete-input" value="0">

                    <div class="mb-2">
                        <label class="form-label fw-semibold small mb-1">Image File <span class="text-danger">*</span></label>
                        <input type="file" name="gallery[${itemIndex}][image]" class="form-control form-control-sm" accept="image/*" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small mb-1">Photo Title / Caption</label>
                        <input type="text" name="gallery[${itemIndex}][title]" class="form-control form-control-sm" placeholder="e.g. Science Lab Experiment" value="">
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label fw-semibold small mb-1">Category</label>
                            <input type="text" name="gallery[${itemIndex}][category]" class="form-control form-control-sm" list="categoryOptions" placeholder="Category" value="Campus">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small mb-1">Grid Display Size</label>
                            <select name="gallery[${itemIndex}][size]" class="form-select form-select-sm">
                                <option value="normal" selected>Standard</option>
                                <option value="wide">Wide (2 col)</option>
                                <option value="tall">Tall (2 row)</option>
                                <option value="big">Big (2x2)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        itemIndex++;
    });

    container.addEventListener('click', function(e) {
        if(e.target.classList.contains('remove-gallery-btn')) {
            const item = e.target.closest('.gallery-item');
            item.style.display = 'none';
            item.querySelector('.delete-input').value = '1';
            // If it's a newly added row without existing image, remove completely
            if(!item.querySelector('input[name*="[old_image]"]')) {
                item.remove();
            }
        }
    });
});
</script>
@endpush
