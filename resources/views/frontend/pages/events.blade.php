@extends('frontend.layouts.app')

@push('styles')
<style>
    .event-header {
        background: linear-gradient(135deg, var(--navy-deep) 0%, var(--primary) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .event-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') repeat;
        opacity: 0.5;
    }
    
    .event-card-premium {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    .event-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: rgba(13, 71, 161, 0.1);
    }
    .event-img-wrapper {
        height: 200px;
        overflow: hidden;
        position: relative;
        background: #f8f9fa;
    }
    .event-img-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .event-card-premium:hover .event-img-wrapper img {
        transform: scale(1.05);
    }
    .event-date-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        color: var(--navy-deep);
        padding: 8px 12px;
        border-radius: 12px;
        font-weight: 700;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,1);
        min-width: 60px;
    }
    .event-date-badge .day {
        display: block;
        font-size: 1.3rem;
        line-height: 1;
        color: var(--primary);
    }
    .event-date-badge .month {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 3px;
    }
    .event-card-body {
        padding: 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .event-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--navy-deep);
        margin-bottom: 12px;
        line-height: 1.3;
        transition: color 0.3s ease;
    }
    .event-card-premium:hover .event-title {
        color: var(--primary);
    }
    .event-meta {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: var(--slate);
        font-weight: 500;
    }
    .event-meta i {
        color: var(--primary);
        margin-right: 5px;
    }
    .event-excerpt {
        color: var(--slate);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 25px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .event-read-more {
        margin-top: auto;
        border-top: 1px solid #f1f5f9;
        padding-top: 15px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: color 0.3s ease;
    }
    .event-read-more:hover {
        color: var(--accent);
    }
    .event-read-more i {
        transition: transform 0.3s ease;
    }
    .event-read-more:hover i {
        transform: translateX(5px);
    }
</style>
@endpush

@section('content')
<!-- Event Header -->
<div class="event-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">Campus Life</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Upcoming Events</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            Join us in celebrating the vibrant and dynamic student life at our institution.
        </p>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container">
        
        <div class="row g-4">
            @if($events->count() > 0)
                @foreach($events as $event)
                    <div class="col-md-6 col-lg-4">
                        <div class="event-card-premium">
                            <div class="event-img-wrapper">
                                <img src="{{ $event->image_path ? asset($event->image_path) : 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=85' }}" alt="{{ $event->title }}">
                                
                                <div class="event-date-badge">
                                    <span class="day">{{ $event->start_date ? $event->start_date->format('d') : '--' }}</span>
                                    <span class="month">{{ $event->start_date ? $event->start_date->format('M') : 'TBA' }}</span>
                                </div>
                            </div>
                            
                            <div class="event-card-body">
                                <h3 class="event-title">{{ $event->title }}</h3>
                                
                                <div class="event-meta">
                                    @if($event->location)
                                    <span><i class="bi bi-geo-alt-fill"></i> {{ Str::limit($event->location, 20) }}</span>
                                    @endif
                                    @if($event->start_date)
                                    <span><i class="bi bi-clock-history"></i> {{ $event->start_date->format('h:i A') }}</span>
                                    @endif
                                </div>
                                
                                <p class="event-excerpt">{{ strip_tags($event->description) }}</p>
                                
                                <a href="{{ route('event.details', $event->id) }}" class="event-read-more">
                                    View Details
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <div class="bg-white rounded-4 shadow-sm p-5 border">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 font-weight-bold text-dark" style="font-family: 'Playfair Display', serif;">No Upcoming Events</h3>
                        <p class="text-muted">Check back later for new event announcements.</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Pagination -->
        @if($events->hasPages())
        <div class="mt-5 d-flex justify-content-center">
            {{ $events->links('pagination::bootstrap-5') }}
        </div>
        @endif
        
    </div>
</section>
@endsection
