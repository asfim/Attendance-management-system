@extends('frontend.layouts.app')

@push('styles')
<style>
    .gallery-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .gallery-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('{{ \App\Models\Setting::get('campus_hero_bg', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2000&auto=format&fit=crop') }}') center/cover;
        opacity: 0.2;
        mix-blend-mode: overlay;
    }
    .gallery-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
        opacity: 0.6;
    }

    .filter-btn-premium {
        background: #fff;
        border: 1px solid #e2e8f0;
        color: var(--slate);
        padding: 10px 24px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(0,0,0,0.02);
    }
    .filter-btn-premium:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: #fffaf0;
    }
    .filter-btn-premium.active {
        background: linear-gradient(135deg, var(--accent) 0%, #f57f17 100%);
        color: #fff !important;
        border-color: transparent;
        box-shadow: 0 8px 15px rgba(249, 168, 37, 0.3);
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        grid-auto-rows: 250px;
        gap: 20px;
        grid-auto-flow: dense;
    }

    @media (min-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(3, 1fr);
        }
        .item-featured {
            grid-column: span 2;
            grid-row: span 2;
        }
        .item-wide {
            grid-column: span 2;
        }
        .item-tall {
            grid-row: span 2;
        }
    }

    @media (min-width: 1024px) {
        .gallery-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    .gallery-item-premium {
        border-radius: 16px;
        overflow: hidden;
        position: relative;
        cursor: pointer;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }
    .gallery-item-premium img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.2, 0.8, 0.2, 1);
    }
    .gallery-item-premium:hover img {
        transform: scale(1.08);
    }
    .gallery-overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
        padding: 30px 20px 20px;
        color: #fff;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.4s ease;
    }
    .gallery-item-premium:hover .gallery-overlay {
        opacity: 1;
        transform: translateY(0);
    }
    .gallery-cat-badge {
        background: var(--accent);
        color: #fff;
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: inline-block;
    }
    .gallery-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0;
    }
    .item-featured .gallery-title, .item-wide .gallery-title {
        font-size: 1.5rem;
    }
</style>
@endpush

@section('content')

<!-- Gallery Header -->
<div class="gallery-header text-center text-white">
    <div class="container position-relative z-index-1">
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{!! strip_tags($settings['gallery_hero_title'] ?? 'Photo Gallery') !!}</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ $settings['gallery_hero_subtitle'] ?? 'Explore the vibrant life, state-of-the-art facilities, and memorable events that make our institution a premier center for learning.' }}
        </p>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container">

        <!-- Filters -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5 galleryTabs">
            <button data-tab="all" class="btn filter-btn-premium active">All Photos</button>
            @foreach($categories as $slug => $name)
                <button data-tab="{{ $slug }}" class="btn filter-btn-premium">{{ $name }}</button>
            @endforeach
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid">
            @foreach($rawGallery as $item)
                @php
                    $sizeClass = match($item['size'] ?? 'normal') {
                        'wide' => 'item-wide',
                        'tall' => 'item-tall',
                        'big' => 'item-featured',
                        default => ''
                    };
                @endphp
                <div class="gallery-item-premium cat-{{ $item['cat_slug'] }} {{ $sizeClass }}">
                    <img src="{{ $item['image'] }}" alt="{{ $item['title'] ?? 'Gallery photo' }}">
                    <div class="gallery-overlay">
                        <span class="gallery-cat-badge">{{ $item['category_name'] }}</span>
                        @if(!empty($item['title']))
                            <h3 class="gallery-title">{{ $item['title'] }}</h3>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabs = document.querySelectorAll('.galleryTabs .filter-btn-premium');
        const items = document.querySelectorAll('.gallery-item-premium');

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all
                tabs.forEach(t => t.classList.remove('active'));

                // Add active class to clicked
                tab.classList.add('active');

                const filter = tab.getAttribute('data-tab');

                items.forEach(item => {
                    if(filter === 'all' || item.classList.contains('cat-' + filter)) {
                        item.style.display = 'block';
                        // Simple animation
                        item.style.opacity = '0';
                        item.style.transform = 'scale(0.9)';
                        setTimeout(() => {
                            item.style.transition = 'all 0.4s cubic-bezier(0.2, 0.8, 0.2, 1)';
                            item.style.opacity = '1';
                            item.style.transform = 'scale(1)';
                        }, 50);
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
