@extends('frontend.layouts.app')

@push('styles')
<style>
    .notice-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .notice-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') repeat;
        opacity: 0.5;
    }
    
    .notice-card-premium {
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
    .notice-card-premium:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: rgba(13, 71, 161, 0.1);
    }
    .notice-card-top {
        height: 120px;
        position: relative;
    }
    .notice-card-top::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0);
        background-size: 14px 14px;
        opacity: 0.5;
    }
    .notice-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        color: #fff;
        font-size: 0.7rem;
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        border: 1px solid rgba(255,255,255,0.3);
    }
    .notice-date-box {
        position: absolute;
        bottom: -25px;
        left: 25px;
        background: #fff;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        border-radius: 15px;
        width: 60px;
        height: 60px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
        border: 1px solid #f1f5f9;
    }
    .notice-date-day {
        font-size: 1.4rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
    }
    .notice-date-month {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2px;
    }
    .notice-card-body {
        padding: 40px 25px 25px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .notice-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--navy-deep);
        margin-bottom: 15px;
        line-height: 1.3;
        transition: color 0.3s ease;
    }
    .notice-card-premium:hover .notice-title {
        color: var(--primary);
    }
    .notice-excerpt {
        color: var(--slate);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 25px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .notice-read-more {
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
    .notice-read-more:hover {
        color: var(--accent);
    }
    .notice-read-more i {
        transition: transform 0.3s ease;
    }
    .notice-read-more:hover i {
        transform: translateX(5px);
    }
    
    /* Gradients for different target audiences */
    .bg-grad-academic { background: linear-gradient(135deg, var(--primary) 0%, #1c4270 100%); }
    .bg-grad-admission { background: linear-gradient(135deg, #1A362B 0%, #2A5A48 100%); }
    .bg-grad-events { background: linear-gradient(135deg, #4A1C40 0%, #7A2F6A 100%); }
    .bg-grad-general { background: linear-gradient(135deg, var(--navy-deep) 0%, #1c4270 100%); }
</style>
@endpush

@section('content')

<!-- Notice Header -->
<div class="notice-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">Updates & Announcements</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">Notice Board</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            Stay informed with the latest news, academic schedules, admission updates, and event announcements from our institution.
        </p>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container">
        
        <div class="row g-4">
            @if($notices->count() > 0)
                @foreach($notices as $notice)
                    @php
                        $target = strtolower($notice->target_audience ?? 'general');
                        $bgClass = 'bg-grad-general';
                        if ($target == 'academic') $bgClass = 'bg-grad-academic';
                        elseif ($target == 'admission') $bgClass = 'bg-grad-admission';
                        elseif ($target == 'events') $bgClass = 'bg-grad-events';
                    @endphp
                    
                    <div class="col-md-6 col-lg-4">
                        <div class="notice-card-premium">
                            <!-- Card Top with Gradient -->
                            <div class="notice-card-top {{ $bgClass }}">
                                <span class="notice-badge">{{ ucfirst($target) }}</span>
                                
                                @if($notice->published_at)
                                <div class="notice-date-box">
                                    <span class="notice-date-day">{{ $notice->published_at->format('d') }}</span>
                                    <span class="notice-date-month">{{ $notice->published_at->format('M') }}</span>
                                </div>
                                @endif
                            </div>
                            
                            <!-- Card Body -->
                            <div class="notice-card-body">
                                <h3 class="notice-title">{{ $notice->title }}</h3>
                                <p class="notice-excerpt">{{ strip_tags($notice->content) }}</p>
                                
                                <a href="{{ route('notice.details', $notice->id) }}" class="notice-read-more">
                                    Read Notice
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-12 text-center py-5">
                    <div class="bg-white rounded-4 shadow-sm p-5 border">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 font-weight-bold text-dark" style="font-family: 'Playfair Display', serif;">No Notices Available</h3>
                        <p class="text-muted">Check back later for new updates and announcements.</p>
                    </div>
                </div>
            @endif
        </div>
        
        <!-- Pagination -->
        @if($notices->hasPages())
        <div class="mt-5 d-flex justify-content-center">
            {{ $notices->links('pagination::bootstrap-5') }}
        </div>
        @endif
        
    </div>
</section>
@endsection
