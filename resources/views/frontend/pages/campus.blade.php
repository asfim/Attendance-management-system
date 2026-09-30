@extends('frontend.layouts.app')

@push('styles')
<style>
    .campus-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .campus-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('{{ \App\Models\Setting::get('campus_hero_bg', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2000&auto=format&fit=crop') }}') center/cover;
        opacity: 0.2;
        mix-blend-mode: overlay;
    }
    .campus-header::after {
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
    }
</style>
@endpush

@section('content')

<!-- Campus Header -->
<div class="campus-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">Campus Life</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('campus_hero_title', 'A Space to Inspire') }}</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ \App\Models\Setting::get('campus_hero_subtitle', 'Explore our state-of-the-art facilities, vibrant student life, and the spaces where our community comes together.') }}
        </p>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container">
        
        <!-- Filters -->
        <div class="d-flex flex-wrap justify-content-center gap-3 mb-5 campusFilters">
            <button data-filter="all" class="btn filter-btn-premium active">All Photos</button>
            @php
                $gallery = json_decode(\App\Models\Setting::get('campus_gallery', '[]'), true);
                if(empty($gallery)) {
                    $uniqueCats = ['Academic', 'Sports & Fitness', 'Library', 'Events'];
                } else {
                    $uniqueCats = array_unique(array_column($gallery, 'category'));
                }
            @endphp
            @foreach($uniqueCats as $cat)
                @php
                    $filterId = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $cat));
                    if (str_contains($filterId, 'sports')) $filterId = 'sports';
                    if (str_contains($filterId, 'event')) $filterId = 'events';
                @endphp
                <button data-filter="{{ $filterId }}" class="btn filter-btn-premium">{{ $cat }}</button>
            @endforeach
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid">
            @php
                if (empty($gallery)) {
                    $gallery = [
                        ['image' => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=1000&auto=format&fit=crop', 'category' => 'Academic'],
                        ['image' => 'https://images.unsplash.com/photo-1583468982228-19f19164aee2?q=80&w=600&auto=format&fit=crop', 'category' => 'Academic'],
                        ['image' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=600&auto=format&fit=crop', 'category' => 'Academic'],
                        ['image' => 'https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=600&auto=format&fit=crop', 'category' => 'Library'],
                        ['image' => 'https://images.unsplash.com/photo-1568667256549-094345857637?q=80&w=800&auto=format&fit=crop', 'category' => 'Library'],
                        ['image' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?q=80&w=800&auto=format&fit=crop', 'category' => 'Sports & Fitness'],
                        ['image' => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?q=80&w=600&auto=format&fit=crop', 'category' => 'Sports & Fitness'],
                        ['image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?q=80&w=800&auto=format&fit=crop', 'category' => 'Events'],
                    ];
                }
            @endphp
            
            @foreach($gallery as $index => $item)
                @php
                    $i = $index + 1;
                    $img = $item['image'];
                    $catName = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $item['category']));
                    if (str_contains($catName, 'sports')) $catName = 'sports';
                    if (str_contains($catName, 'event')) $catName = 'events';

                    $extraClass = "";
                    if($i == 1) $extraClass = "item-featured";
                    elseif($i == 5) $extraClass = "item-wide";
                    elseif($i == 8) $extraClass = "item-tall";
                @endphp
                <div class="gallery-item-premium {{ $catName }} {{ $extraClass }}">
                    <img src="{{ $img }}" alt="{{ $item['category'] }} Image"/>
                    <div class="gallery-overlay">
                        <span class="gallery-cat-badge">{{ $item['category'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const filterBtns = document.querySelectorAll('.campusFilters .filter-btn-premium');
        const items = document.querySelectorAll('.gallery-item-premium');

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // Remove active class from all
                filterBtns.forEach(b => b.classList.remove('active'));
                
                // Add active class to clicked
                btn.classList.add('active');

                const filter = btn.getAttribute('data-filter');
                
                items.forEach(item => {
                    if(filter === 'all' || item.classList.contains(filter)) {
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
