@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-building-columns me-2"></i> Campus Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('admin.cms.campus.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Hero Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title</label>
                            <input type="text" name="campus_hero_title" class="form-control" value="{{ $settings['campus_hero_title'] ?? 'Our Campus' }}" placeholder="Our Campus">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Subtitle</label>
                            <input type="text" name="campus_hero_subtitle" class="form-control" value="{{ $settings['campus_hero_subtitle'] ?? 'A world-class learning environment' }}" placeholder="A world-class learning environment">
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label text-secondary">Hero Background Image</label>
                            <input type="file" name="campus_hero_bg" class="form-control" accept="image/*">
                            @if(isset($settings['campus_hero_bg']))
                                <div class="mt-2"><img src="{{ $settings['campus_hero_bg'] }}" height="60" class="rounded"></div>
                            @endif
                        </div>

                        <div class="col-12 mt-4">
                            <div class="d-flex justify-content-between border-bottom pb-2 mb-3">
                                <h6 class="text-secondary fw-semibold mb-0">Campus Gallery (Dynamic)</h6>
                                <button type="button" class="btn btn-sm btn-success" id="addGalleryBtn"><i class="fa-solid fa-plus me-1"></i> Add Image</button>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="row g-3" id="galleryContainer">
                                @php
                                    $gallery = json_decode($settings['campus_gallery'] ?? '[]', true);
                                @endphp
                                @foreach($gallery as $index => $item)
                                    <div class="col-md-6 gallery-item" data-index="{{ $index }}">
                                        <div class="p-3 border rounded position-relative">
                                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-gallery-btn" aria-label="Close"></button>
                                            <input type="hidden" name="gallery[{{ $index }}][delete]" class="delete-input" value="0">
                                            
                                            <label class="form-label text-secondary">Gallery Image</label>
                                            <div class="d-flex gap-2">
                                                <input type="file" name="gallery[{{ $index }}][image]" class="form-control" accept="image/*">
                                                <input type="text" name="gallery[{{ $index }}][category]" class="form-control w-50" list="categoryOptions" placeholder="Category (e.g. Academic)" value="{{ $item['category'] }}">
                                            </div>
                                            <input type="hidden" name="gallery[{{ $index }}][old_image]" value="{{ $item['image'] }}">
                                            <div class="mt-2">
                                                <img src="{{ $item['image'] }}" height="60" class="rounded">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <datalist id="categoryOptions">
                            <option value="Academic">
                            <option value="Library">
                            <option value="Sports & Fitness">
                            <option value="Events">
                        </datalist>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Campus Page Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('galleryContainer');
    const addBtn = document.getElementById('addGalleryBtn');
    let itemIndex = {{ count($gallery) > 0 ? max(array_keys($gallery)) + 1 : 0 }};

    addBtn.addEventListener('click', function() {
        const html = `
            <div class="col-md-6 gallery-item" data-index="${itemIndex}">
                <div class="p-3 border rounded position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-gallery-btn" aria-label="Close"></button>
                    <input type="hidden" name="gallery[${itemIndex}][delete]" class="delete-input" value="0">
                    
                    <label class="form-label text-secondary">Gallery Image</label>
                    <div class="d-flex gap-2">
                        <input type="file" name="gallery[${itemIndex}][image]" class="form-control" accept="image/*" required>
                        <input type="text" name="gallery[${itemIndex}][category]" class="form-control w-50" list="categoryOptions" placeholder="Category (e.g. Academic)" value="Academic">
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
            // if it is a new item without an old image, we can just remove it from DOM
            if(!item.querySelector('input[name*="[old_image]"]')) {
                item.remove();
            }
        }
    });
});
</script>
@endsection
