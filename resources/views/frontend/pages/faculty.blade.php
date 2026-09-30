@extends('frontend.layouts.app')

@push('styles')
<style>
    .faculty-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 80px 0 60px;
        position: relative;
        overflow: hidden;
    }
    .faculty-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') repeat;
        opacity: 0.5;
    }
    .faculty-card-premium {
        background: #fff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        height: 100%;
        border: 1px solid rgba(0,0,0,0.03);
        position: relative;
    }
    .faculty-card-premium:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12);
    }
    .faculty-img-wrapper {
        position: relative;
        padding-top: 100%; /* 1:1 Aspect Ratio */
        overflow: hidden;
    }
    .faculty-img-wrapper img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .faculty-card-premium:hover .faculty-img-wrapper img {
        transform: scale(1.08);
    }
    .faculty-overlay {
        position: absolute;
        bottom: 0; left: 0; right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%);
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
        display: flex;
        justify-content: center;
        gap: 15px;
    }
    .faculty-card-premium:hover .faculty-overlay {
        opacity: 1;
    }
    .faculty-social-btn {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }
    .faculty-social-btn:hover {
        background: var(--accent);
        color: #111;
        transform: translateY(-3px);
    }
    .faculty-info {
        padding: 25px 20px;
        text-align: center;
        position: relative;
        z-index: 2;
        background: #fff;
    }
    .faculty-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 5px;
    }
    .faculty-designation {
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .faculty-dept {
        display: inline-block;
        padding: 4px 12px;
        background: var(--light);
        color: #666;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }
    .department-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.9);
        color: var(--primary);
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
        backdrop-filter: blur(5px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        z-index: 2;
    }
</style>
@endpush

@section('content')
<!-- Header Section -->
<div class="faculty-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">আমাদের গর্ব</span>
        <h1 class="display-4 fw-bold mb-3">অভিজ্ঞ শিক্ষকবৃন্দ</h1>
        <p class="lead text-white-50 max-w-700 mx-auto" style="max-width: 700px;">
            আমাদের রয়েছেন একঝাঁক মেধাবী, অভিজ্ঞ এবং নিবেদিতপ্রাণ শিক্ষক, যারা শিক্ষার্থীদের সুন্দর ভবিষ্যৎ গড়ায় নিরলস কাজ করে যাচ্ছেন।
        </p>
    </div>
</div>

<!-- Faculty Grid Section -->
<section class="section bg-light py-5">
    <div class="container">
        
        @if($teachers->isNotEmpty())
        <div class="row g-4 justify-content-center">
            @foreach($teachers as $teacher)
            <div class="col-sm-6 col-md-4 col-lg-3">
                <div class="faculty-card-premium">
                    
                    @if($teacher->department)
                    <div class="department-badge">
                        {{ $teacher->department }}
                    </div>
                    @endif

                    <div class="faculty-img-wrapper">
                        <img src="{{ $teacher->photoUrl() }}" 
                             alt="{{ $teacher->user->name ?? 'Staff' }}">
                        
                        <div class="faculty-overlay">
                            <a href="#" class="faculty-social-btn"><i class="bi bi-envelope-fill"></i></a>
                            <a href="#" class="faculty-social-btn"><i class="bi bi-linkedin"></i></a>
                        </div>
                    </div>
                    
                    <div class="faculty-info">
                        <h3 class="faculty-name">{{ $teacher->user->name ?? 'Unknown' }}</h3>
                        <div class="faculty-designation">{{ $teacher->designation ?? 'Staff' }}</div>
                        
                        @if($teacher->qualifications)
                        <div class="faculty-dept mt-2">
                            <i class="bi bi-mortarboard me-1"></i> {{ $teacher->qualifications }}
                        </div>
                        @endif
                    </div>
                    
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="row justify-content-center py-5">
            <div class="col-md-6 text-center">
                <div class="card border-0 shadow-sm rounded-4 p-5">
                    <i class="bi bi-people text-muted mb-3" style="font-size: 3rem;"></i>
                    <h3 class="fw-bold text-dark">শিক্ষকদের তথ্য আপডেট হচ্ছে</h3>
                    <p class="text-muted">খুব শিগগিরই আমাদের সম্মানীত শিক্ষকদের তালিকা এখানে প্রকাশ করা হবে।</p>
                </div>
            </div>
        </div>
        @endif

    </div>
</section>
@endsection
