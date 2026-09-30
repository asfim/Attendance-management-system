@extends('frontend.layouts.app')

@push('styles')
<style>
    .academics-header {
        background: linear-gradient(135deg, var(--navy-deep) 0%, var(--primary) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .academics-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('{!! \App\Models\Setting::get('academics_hero_bg', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2000&auto=format&fit=crop') !!}') center/cover;
        opacity: 0.15;
        mix-blend-mode: overlay;
    }
    .academics-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
        opacity: 0.7;
    }

    .program-card {
        background: #fff;
        border-radius: 1.5rem;
        padding: 40px 30px;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        height: 100%;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .program-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 5px;
        background: var(--accent);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s ease;
        z-index: -1;
    }
    .program-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }
    .program-card:hover::after {
        transform: scaleX(1);
    }
    
    .program-icon {
        width: 60px;
        height: 60px;
        background: rgba(249, 168, 37, 0.1);
        color: var(--accent);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 25px;
    }
    
    .methodology-icon {
        width: 50px;
        height: 50px;
        background: rgba(10, 27, 50, 0.05);
        color: var(--navy-deep);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .image-block {
        position: relative;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
    }
    .image-block img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
    }
    .image-block:hover img {
        transform: scale(1.05);
    }
    .image-badge {
        position: absolute;
        bottom: -20px;
        left: -20px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        padding: 30px;
        border-radius: 2rem;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        border: 1px solid rgba(255,255,255,0.5);
    }

    .resource-card {
        background: rgba(255, 255, 255, 0.05);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 1.5rem;
        padding: 40px 30px;
        height: 100%;
        transition: all 0.3s ease;
    }
    .resource-card:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.3);
    }
    
    .cta-section {
        background: var(--cream);
        position: relative;
        overflow: hidden;
    }
</style>
@endpush

@section('content')

<!-- Hero Section -->
<div class="academics-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ \App\Models\Setting::get('academics_hero_badge', 'শিক্ষায় উৎকর্ষ') }}</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_hero_title1', 'মননশীলতা বিকাশ,') }} <span class="text-warning">{{ \App\Models\Setting::get('academics_hero_title2', 'ভবিষ্যৎ নির্মাণ') }}</span></h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ \App\Models\Setting::get('academics_hero_subtitle', 'আমাদের বিস্তৃত ও যুগোপযোগী শিক্ষাক্রম শিক্ষার্থীদের মধ্যে কৌতূহল জাগাতে, চিন্তাশক্তি বাড়াতে এবং আজীবন শেখার ভিত্তি মজবুত করতে ডিজাইন করা হয়েছে।') }}
        </p>
    </div>
</div>

<!-- Programs Section -->
<section class="section py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5 pb-3">
            <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('academics_programs_subtitle', 'আমাদের শিক্ষাক্রম') }}</span>
            <h2 class="display-5 fw-bold text-dark mt-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_programs_title', 'শিক্ষার স্তরসমূহ') }}</h2>
        </div>

        <div class="row g-4">
            @php
                $programs = json_decode(\App\Models\Setting::get('academics_programs', '[]'), true);
                if (empty($programs)) {
                    $programs = [
                        [
                            'icon' => 'bi-puzzle',
                            'title' => \App\Models\Setting::get('academics_program_1_title', 'প্রাক-প্রাথমিক'),
                            'desc' => \App\Models\Setting::get('academics_program_1_desc', 'ছোট শিশুদের মানসিক, সামাজিক এবং আবেগিক বিকাশের ওপর ফোকাস করা একটি আনন্দদায়ক পদ্ধতি।'),
                            'bullet1' => \App\Models\Setting::get('academics_program_1_bullet1', 'বয়স ৩ - ৫ বছর'),
                            'bullet2' => \App\Models\Setting::get('academics_program_1_bullet2', 'খেলাধুলা ভিত্তিক পদ্ধতি'),
                            'bullet3' => \App\Models\Setting::get('academics_program_1_bullet3', 'সেন্সরি অ্যাক্টিভিটিস')
                        ],
                        [
                            'icon' => 'bi-book',
                            'title' => \App\Models\Setting::get('academics_program_2_title', 'প্রাথমিক'),
                            'desc' => \App\Models\Setting::get('academics_program_2_desc', 'সাক্ষরতা, গাণিতিক দক্ষতা এবং পরিবেশগত সচেতনতার শক্তিশালী ভিত্তি গঠন।'),
                            'bullet1' => \App\Models\Setting::get('academics_program_2_bullet1', 'প্রথম থেকে পঞ্চম শ্রেণি'),
                            'bullet2' => \App\Models\Setting::get('academics_program_2_bullet2', 'প্রজেক্ট-ভিত্তিক লার্নিং'),
                            'bullet3' => \App\Models\Setting::get('academics_program_2_bullet3', 'সহশিক্ষামূলক কার্যক্রম')
                        ],
                        [
                            'icon' => 'bi-compass',
                            'title' => \App\Models\Setting::get('academics_program_3_title', 'নিম্ন মাধ্যমিক'),
                            'desc' => \App\Models\Setting::get('academics_program_3_desc', 'শিক্ষার্থীদের স্বাধীন চিন্তাশক্তি এবং বিভিন্ন বিষয়ে গভীর ধারণার বিকাশ।'),
                            'bullet1' => \App\Models\Setting::get('academics_program_3_bullet1', 'ষষ্ঠ থেকে অষ্টম শ্রেণি'),
                            'bullet2' => \App\Models\Setting::get('academics_program_3_bullet2', 'স্টেম (STEM) শিক্ষা'),
                            'bullet3' => \App\Models\Setting::get('academics_program_3_bullet3', 'নেতৃত্বের প্রোগ্রাম')
                        ],
                        [
                            'icon' => 'bi-mortarboard',
                            'title' => \App\Models\Setting::get('academics_program_4_title', 'মাধ্যমিক ও উচ্চ মাধ্যমিক'),
                            'desc' => \App\Models\Setting::get('academics_program_4_desc', 'জাতীয় বোর্ড পরীক্ষা এবং বিশ্ববিদ্যালয়ে ভর্তির জন্য কঠোর একাডেমিক প্রস্তুতি।'),
                            'bullet1' => \App\Models\Setting::get('academics_program_4_bullet1', 'নবম থেকে দ্বাদশ শ্রেণি'),
                            'bullet2' => \App\Models\Setting::get('academics_program_4_bullet2', 'বিজ্ঞান, কলা, বাণিজ্য'),
                            'bullet3' => \App\Models\Setting::get('academics_program_4_bullet3', 'ক্যারিয়ার গাইডেন্স')
                        ]
                    ];
                }
            @endphp
            
            @foreach($programs as $program)
            <div class="col-md-6 col-lg-3">
                <div class="program-card">
                    <div class="program-icon"><i class="bi {{ $program['icon'] ?? 'bi-book' }}"></i></div>
                    <h4 class="fw-bold text-dark mb-3" style="font-family: 'Playfair Display', serif;">{{ $program['title'] ?? '' }}</h4>
                    <p class="text-muted small mb-4">{{ $program['desc'] ?? '' }}</p>
                    <hr class="text-muted opacity-25">
                    <ul class="list-unstyled text-muted small fw-medium mb-0">
                        @if(!empty($program['bullet1']))
                        <li class="mb-2"><i class="bi bi-check2 text-warning me-2"></i> {{ $program['bullet1'] }}</li>
                        @endif
                        @if(!empty($program['bullet2']))
                        <li class="mb-2"><i class="bi bi-check2 text-warning me-2"></i> {{ $program['bullet2'] }}</li>
                        @endif
                        @if(!empty($program['bullet3']))
                        <li><i class="bi bi-check2 text-warning me-2"></i> {{ $program['bullet3'] }}</li>
                        @endif
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Our Approach -->
<section class="section py-5 my-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('academics_methodology_subtitle', 'আমাদের পদ্ধতি') }}</span>
                <h2 class="display-5 fw-bold text-dark mt-2 mb-4" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_methodology_title', 'শিক্ষায় একটি সামগ্রিক দৃষ্টিভঙ্গি') }}</h2>
                <p class="text-muted fs-5 mb-5" style="line-height: 1.7;">
                    {{ \App\Models\Setting::get('academics_methodology_desc', 'আমাদের বিশ্বাস, প্রকৃত শিক্ষা শুধু পাঠ্যবইয়ে সীমাবদ্ধ নয়। আমরা আধুনিক শিক্ষণ পদ্ধতি এবং নৈতিক মূল্যবোধের সমন্বয়ে শিক্ষার্থীদের প্রস্তুত করি।') }}
                </p>

                <div class="d-flex align-items-start mb-4">
                    <div class="methodology-icon me-4 mt-1"><i class="bi bi-search"></i></div>
                    <div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_methodology_1_title', 'অনুসন্ধানমূলক শিক্ষা') }}</h4>
                        <p class="text-muted small">{{ \App\Models\Setting::get('academics_methodology_1_desc', 'শিক্ষার্থীদের প্রশ্ন করতে, গভীরভাবে ভাবতে এবং সমস্যা সমাধানের দক্ষতা বিকাশে উৎসাহিত করা হয়।') }}</p>
                    </div>
                </div>
                
                <div class="d-flex align-items-start mb-4">
                    <div class="methodology-icon me-4 mt-1"><i class="bi bi-laptop"></i></div>
                    <div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_methodology_2_title', 'প্রযুক্তির ব্যবহার') }}</h4>
                        <p class="text-muted small">{{ \App\Models\Setting::get('academics_methodology_2_desc', 'স্মার্ট ক্লাসরুম এবং আধুনিক ল্যাব সুবিধা নিশ্চিত করে আমাদের শিক্ষার্থীরা ডিজিটাল ভবিষ্যতের জন্য প্রস্তুত।') }}</p>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="methodology-icon me-4 mt-1"><i class="bi bi-person-heart"></i></div>
                    <div>
                        <h4 class="fw-bold text-dark mb-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_methodology_3_title', 'ব্যক্তিগত মনোযোগ') }}</h4>
                        <p class="text-muted small">{{ \App\Models\Setting::get('academics_methodology_3_desc', 'শিক্ষক-শিক্ষার্থীর কম অনুপাত বজায় রেখে আমরা নিশ্চিত করি প্রতিটি শিশু প্রয়োজনীয় দিকনির্দেশনা ও সমর্থন পাচ্ছে।') }}</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="ps-lg-5 position-relative mt-5 mt-lg-0">
                    <div class="image-block border border-5 border-white shadow-lg" style="height: 600px;">
                        <img src="{{ \App\Models\Setting::get('academics_methodology_image', 'https://images.unsplash.com/photo-1577896851231-70ef18881754?q=80&w=1000&auto=format&fit=crop') }}" alt="Methodology">
                    </div>
                    <div class="image-badge d-none d-md-block">
                        <div class="display-5 fw-bold text-warning mb-1" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_methodology_badge_number', '২৫+') }}</div>
                        <p class="text-navy-deep small fw-bold text-uppercase mb-0" style="letter-spacing: 1px;">{!! nl2br(e(\App\Models\Setting::get('academics_methodology_badge_text', "বছরের শিক্ষাগত\nউৎকর্ষ"))) !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Resources & Facilities -->
<section class="section py-5 bg-dark" style="background-color: var(--navy-deep) !important;">
    <div class="container py-5">
        <div class="text-center mb-5 pb-3">
            <span class="text-warning fw-bold text-uppercase" style="letter-spacing: 2px; font-size: 0.85rem;">{{ \App\Models\Setting::get('academics_facilities_subtitle', 'সুযোগ-সুবিধাসমূহ') }}</span>
            <h2 class="display-5 fw-bold text-white mt-2" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_facilities_title', 'শিক্ষাগত উপকরণ') }}</h2>
            <p class="text-white-50 mx-auto mt-3" style="max-width: 600px;">{{ \App\Models\Setting::get('academics_facilities_desc', 'শিক্ষার্থীদের শেখার অভিজ্ঞতা আরও উন্নত করতে আমরা আধুনিক ও বিশ্বমানের সুযোগ-সুবিধা প্রদান করি।') }}</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="resource-card">
                    <h4 class="fw-bold text-warning mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_facility_1_title', 'আধুনিক ল্যাবরেটরি') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('academics_facility_1_desc', 'পদার্থবিজ্ঞান, রসায়ন, জীববিজ্ঞান এবং কম্পিউটারের জন্য সম্পূর্ণ আধুনিক ল্যাব, যা শিক্ষার্থীদের হাতে-কলমে শিখতে সাহায্য করে।') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="resource-card">
                    <h4 class="fw-bold text-warning mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_facility_2_title', 'সমৃদ্ধ লাইব্রেরি') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('academics_facility_2_desc', 'পড়াশোনার জন্য একটি শান্ত পরিবেশ যেখানে অসংখ্য বই, জার্নাল এবং ডিজিটাল রিসোর্সের বিশাল সংগ্রহ রয়েছে।') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="resource-card">
                    <h4 class="fw-bold text-warning mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_facility_3_title', 'স্মার্ট ক্লাসরুম') }}</h4>
                    <p class="text-white-50 small mb-0">{{ \App\Models\Setting::get('academics_facility_3_desc', 'প্রতিটি ক্লাসরুমে ইন্টারেক্টিভ স্মার্টবোর্ড এবং ডিজিটাল লার্নিং টুলস রয়েছে যা শিক্ষাকে আনন্দদায়ক করে তোলে।') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section py-5">
    <div class="container py-5 text-center">
        <h2 class="display-5 fw-bold text-dark mb-4" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('academics_cta_title', 'আপনার শিক্ষাজীবন শুরু করুন') }}</h2>
        <p class="text-muted fs-5 mb-5 mx-auto" style="max-width: 700px;">{{ \App\Models\Setting::get('academics_cta_desc', 'আগামী শিক্ষাবর্ষের ভর্তি কার্যক্রম চলছে। আজই যোগাযোগ করুন।') }}</p>
        
        <div class="d-flex flex-column flex-sm-row justify-content-center gap-3">
            <a href="{{ route('admission') }}" class="btn btn-warning btn-lg rounded-pill px-5 py-3 fw-bold shadow-sm d-inline-flex align-items-center justify-content-center">
                Apply for Admission <i class="bi bi-arrow-right ms-2"></i>
            </a>
            <a href="{{ route('contact') }}" class="btn btn-outline-dark btn-lg rounded-pill px-5 py-3 fw-bold d-inline-flex align-items-center justify-content-center">
                Contact Admissions
            </a>
        </div>
    </div>
</section>

@endsection
