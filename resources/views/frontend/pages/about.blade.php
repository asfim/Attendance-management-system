@extends('frontend.layouts.app')

@push('styles')
<style>
    .about-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .about-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('{!! \App\Models\Setting::get('about_hero_bg', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2000&auto=format&fit=crop') !!}') center/cover;
        opacity: 0.15;
        mix-blend-mode: overlay;
    }
    .about-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
        opacity: 0.7;
    }

    .about-image-wrapper {
        position: relative;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0,0,0,0.15);
    }
    .about-image-wrapper img {
        width: 100%;
        height: 500px;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    .about-image-wrapper:hover img {
        transform: scale(1.05);
    }
    .years-badge {
        position: absolute;
        bottom: -20px;
        right: 20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 1.5rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        text-align: center;
        width: 180px;
        border: 1px solid rgba(255,255,255,0.5);
    }
    .years-num {
        font-family: 'Playfair Display', serif;
        font-size: 3rem;
        font-weight: 800;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 5px;
    }
    .years-text {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--slate);
        font-weight: 700;
        margin: 0;
    }

    .mission-vision-box {
        background: #fff;
        border-radius: 1.5rem;
        padding: 30px;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        height: 100%;
        transition: all 0.3s ease;
    }
    .mission-vision-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.06);
    }
    .mv-icon {
        width: 50px;
        height: 50px;
        background: rgba(249, 168, 37, 0.1);
        color: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
    }

    .core-values-section {
        background: linear-gradient(135deg, var(--navy-deep) 0%, var(--primary) 100%);
        position: relative;
        padding: 100px 0;
        overflow: hidden;
    }
    .core-values-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.1) 1px, transparent 0);
        background-size: 30px 30px;
    }
    .value-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        padding: 40px 30px;
        height: 100%;
        transition: all 0.3s ease;
    }
    .value-card:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateY(-10px);
        border-color: rgba(255, 255, 255, 0.2);
    }
    .value-icon {
        width: 60px;
        height: 60px;
        background: rgba(249, 168, 37, 0.15);
        color: var(--accent);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 25px;
    }

    .timeline-section {
        position: relative;
    }
    .timeline-container {
        border-left: 2px solid rgba(249, 168, 37, 0.3);
        padding-left: 40px;
        margin-left: 20px;
    }
    .timeline-item {
        position: relative;
        margin-bottom: 50px;
    }
    .timeline-item:last-child {
        margin-bottom: 0;
    }
    .timeline-dot {
        position: absolute;
        left: -49px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: var(--accent);
        box-shadow: 0 0 0 6px #fff;
    }
    .timeline-year {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--accent);
        margin-bottom: 5px;
    }
    
    .leadership-card {
        background: #fff;
        border-radius: 2rem;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0,0,0,0.05);
        border: 1px solid rgba(0,0,0,0.03);
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .leadership-quote-icon {
        position: absolute;
        top: -10px;
        right: -10px;
        font-size: 8rem;
        color: rgba(0,0,0,0.02);
        line-height: 1;
    }
</style>
@endpush

@section('content')

<!-- Hero Section -->
<div class="about-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ \App\Models\Setting::get('about_hero_badge', 'আমাদের গল্প জানুন') }}</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_page_title', 'আমাদের সম্পর্কে -') }} <span class="text-warning">{{ \App\Models\Setting::get('school_name', 'সানরাইজ মডেল স্কুল') }}</span></h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ \App\Models\Setting::get('about_hero_subtitle', 'শিক্ষায় উৎকর্ষের এক অনন্য ঐতিহ্য, চরিত্র গঠনে প্রতিশ্রুতিবদ্ধ এবং এমন একটি শিক্ষাঙ্গন যা শিক্ষার্থীদের দ্রুত পরিবর্তনশীল বিশ্বের জন্য প্রস্তুত করে।') }}
        </p>
    </div>
</div>

<!-- Intro & Mission/Vision -->
<section class="section py-5 my-5">
    <div class="container">
        <div class="row g-5 align-items-center">
            
            <div class="col-lg-6">
                <div class="about-image-wrapper mb-5 mb-lg-0">
                    <img src="{{ \App\Models\Setting::get('about_welcome_image', 'https://images.unsplash.com/photo-1580894732444-8ecded7900cd?q=80&w=1200&auto=format&fit=crop') }}" alt="Campus Life">
                    <div class="years-badge">
                        <div class="years-num">{{ \App\Models\Setting::get('about_years', '২৫') }}<span class="text-warning">+</span></div>
                        <p class="years-text">{{ \App\Models\Setting::get('about_years_text', 'বছরের শিক্ষাগত উৎকর্ষ') }}</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6 ps-lg-5">
                <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('about_foundation_title', 'আমাদের ভিত্তি') }}</span>
                <h2 class="display-5 fw-bold text-dark mt-2 mb-4" style="font-family: 'Playfair Display', serif;">
                    {!! \App\Models\Setting::get('about_welcome_title', 'কৌতূহল ও চরিত্র গঠনের এক অনন্য প্রাঙ্গণ') !!}
                </h2>
                
                <p class="text-muted mb-4" style="font-size: 1.05rem; line-height: 1.7;">
                    {{ \App\Models\Setting::get('about_description', '১৯৯৯ সাল থেকে, সানরাইজ মডেল স্কুল হাজার হাজার শিক্ষার্থীকে প্রাথমিক থেকে উচ্চ মাধ্যমিক পর্যন্ত সুশিক্ষা দিয়ে আসছে। আমরা বিশ্বাস করি প্রকৃত শিক্ষা শুধু বইয়ের পাতায় সীমাবদ্ধ নয়।') }}
                </p>
                <p class="text-muted mb-5" style="font-size: 1.05rem; line-height: 1.7;">
                    {{ \App\Models\Setting::get('about_description2', 'আমাদের শ্রেণীকক্ষগুলো এমনভাবে সাজানো হয়েছে যাতে শিক্ষার্থীরা নিজেরাই নতুন কিছু শিখতে পারে। আমরা শিক্ষার্থীদের শুধু ভবিষ্যতের ক্যারিয়ারের জন্যই নয়, বরং একটি অর্থবহ জীবনের জন্যও প্রস্তুত করি।') }}
                </p>
                
                <div class="row g-4 bg-light p-4 rounded-4 border">
                    <div class="col-md-6">
                        <div class="d-flex flex-column h-100">
                            <div class="mv-icon"><i class="bi bi-bullseye"></i></div>
                            <h4 class="fw-bold mb-2 text-dark" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_mission_title', 'আমাদের লক্ষ্য') }}</h4>
                            <p class="text-muted small mb-0">{{ \App\Models\Setting::get('about_mission', 'শিক্ষার্থীদের স্বাধীন চিন্তাবিদ হিসেবে গড়ে তোলা, যারা জ্ঞান, সততা এবং আত্মবিশ্বাসের সাথে বিশ্বায়নের এই যুগে নেতৃত্ব দিতে পারবে।') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-column h-100">
                            <div class="mv-icon"><i class="bi bi-eye"></i></div>
                            <h4 class="fw-bold mb-2 text-dark" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_vision_title', 'আমাদের রূপকল্প') }}</h4>
                            <p class="text-muted small mb-0">{{ \App\Models\Setting::get('about_vision', 'আগামীর ভবিষ্যৎ ও যোগ্য নেতা গড়ার লক্ষ্যে একটি আধুনিক, যুগোপযোগী ও বিশ্বাসযোগ্য শিক্ষাপ্রতিষ্ঠান হিসেবে নিজেদের প্রতিষ্ঠিত করা।') }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</section>

<!-- Core Values -->
<section class="core-values-section">
    <div class="container position-relative z-index-1">
        <div class="text-center mb-5 pb-3">
            <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('about_core_values_subtitle', 'আমাদের অনুপ্রেরণা') }}</span>
            <h2 class="display-5 fw-bold text-white mt-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_core_values_title', 'আমাদের মূলনীতিসমূহ') }}</h2>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-award"></i></div>
                    <h4 class="text-white fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_core_value_1_title', 'উৎকর্ষ') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('about_core_value_1_desc', 'পড়াশোনা এবং অন্যান্য কার্যক্রমে সর্বোচ্চ মান অর্জনের চেষ্টা।') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-shield-check"></i></div>
                    <h4 class="text-white fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_core_value_2_title', 'সততা') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('about_core_value_2_desc', 'সব কাজে সততা, নৈতিকতা ও জবাবদিহিতা বজায় রাখা।') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-people"></i></div>
                    <h4 class="text-white fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_core_value_3_title', 'অন্তর্ভুক্তি') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('about_core_value_3_desc', 'সকলের প্রতি শ্রদ্ধাশীল এবং একটি বৈষম্যহীন শিক্ষাবান্ধব পরিবেশ তৈরি করা।') }}</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="value-card">
                    <div class="value-icon"><i class="bi bi-lightbulb"></i></div>
                    <h4 class="text-white fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_core_value_4_title', 'উদ্ভাবন') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('about_core_value_4_desc', 'শিক্ষাব্যবস্থাকে আরও আধুনিক করতে নতুন ধারণা এবং প্রযুক্তির ব্যবহার।') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Timeline / History -->
<section class="section py-5 my-5">
    <div class="container">
        <div class="text-center mb-5 pb-4">
            <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('about_timeline_subtitle', 'আমাদের ইতিহাস') }}</span>
            <h2 class="display-5 fw-bold text-dark mt-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_years', '২৫') }} {{ \App\Models\Setting::get('about_timeline_title', 'বছরের অর্জন') }}</h2>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="timeline-container">
                    
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">{{ \App\Models\Setting::get('about_timeline_1_year', '১৯৯৯') }}</div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_timeline_1_title', 'প্রতিষ্ঠা') }}</h4>
                        <p class="text-muted">{{ \App\Models\Setting::get('about_timeline_1_desc', 'সানরাইজ মডেল স্কুল মাত্র ১২০ জন শিক্ষার্থী নিয়ে প্রতিষ্ঠিত হয়।') }}</p>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">{{ \App\Models\Setting::get('about_timeline_2_year', '২০০৮') }}</div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_timeline_2_title', 'কলেজ শাখার উদ্বোধন') }}</h4>
                        <p class="text-muted">{{ \App\Models\Setting::get('about_timeline_2_desc', 'মাধ্যমিক পরীক্ষায় অভাবনীয় সাফল্যের পর উচ্চ মাধ্যমিক শাখা চালু করা হয়।') }}</p>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">{{ \App\Models\Setting::get('about_timeline_3_year', '২০১৫') }}</div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_timeline_3_title', 'নতুন ও আধুনিক ক্যাম্পাস') }}</h4>
                        <p class="text-muted">{{ \App\Models\Setting::get('about_timeline_3_desc', 'ডিজিটাল ক্লাসরুম, আধুনিক লাইব্রেরি এবং বিজ্ঞানাগার সমৃদ্ধ নতুন ক্যাম্পাসের উদ্বোধন।') }}</p>
                    </div>
                    
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-year">{{ \App\Models\Setting::get('about_timeline_4_year', '২০২৪') }}</div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('about_timeline_4_title', 'রজত জয়ন্তী') }}</h4>
                        <p class="text-muted">{{ \App\Models\Setting::get('about_timeline_4_desc', 'শিক্ষাক্ষেত্রে ২৫ বছরের সফল পথচলার উদযাপন।') }}</p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</section>



@endsection
