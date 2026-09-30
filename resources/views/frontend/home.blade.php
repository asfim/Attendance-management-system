@extends('frontend.layouts.app')

@section('content')
<!-- ================= HERO ================= -->
<section id="home">

    <div id="heroSlider"
         class="carousel slide carousel-fade"
         data-bs-ride="carousel">

        <div class="carousel-indicators">

            <button type="button"
                    data-bs-target="#heroSlider"
                    data-bs-slide-to="0"
                    class="active"></button>

            <button type="button"
                    data-bs-target="#heroSlider"
                    data-bs-slide-to="1"></button>

            <button type="button"
                    data-bs-target="#heroSlider"
                    data-bs-slide-to="2"></button>

        </div>


        <div class="carousel-inner">
            @for($i=1; $i<=3; $i++)
            @php
                // Get dynamic values for the slide with fallback defaults to old settings or hardcoded values
                $bg = \App\Models\Setting::get("cms_home_hero_bg_{$i}", $i == 1 ? 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=1800&q=85' : ($i == 2 ? 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=1800&q=85' : 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1800&q=85'));
                $badge = \App\Models\Setting::get("cms_home_hero_badge_{$i}", $i == 1 ? \App\Models\Setting::get('site_admission_text', '২০২৬-২৭ শিক্ষাবর্ষে ভর্তি চলছে') : ($i == 2 ? 'আধুনিক শিক্ষা ব্যবস্থা' : 'সেরা ফলাফল'));
                $title = \App\Models\Setting::get("cms_home_hero_title_{$i}", $i == 1 ? \App\Models\Setting::get('cms_home_hero_title', 'শিক্ষাই জাতির মেরুদণ্ড') : ($i == 2 ? 'আপনার সন্তানের উজ্জ্বল ভবিষ্যৎ' : 'আধুনিক সুযোগ সুবিধা'));
                $desc = \App\Models\Setting::get("cms_home_hero_desc_{$i}", $i == 1 ? \App\Models\Setting::get('cms_home_hero_desc', 'জ্ঞান, নৈতিকতা ও আধুনিক শিক্ষার সমন্বয়ে আমরা গড়ে তুলছি আগামী দিনের যোগ্য নাগরিক।') : ($i == 2 ? 'অভিজ্ঞ শিক্ষক, আধুনিক ল্যাব ও সুন্দর শিক্ষার পরিবেশে গড়ে উঠুক আপনার সন্তান।' : 'খেলার মাঠ, লাইব্রেরি, ও সাংস্কৃতিক আবহে একটি পরিপূর্ণ শিক্ষা প্রতিষ্ঠান।'));
                $btn1Text = \App\Models\Setting::get("cms_home_hero_btn1_text_{$i}", $i == 1 ? 'অনলাইনে ভর্তি করুন' : ($i == 2 ? 'আমাদের কার্যক্রম' : 'গ্যালারি দেখুন'));
                $btn1Link = \App\Models\Setting::get("cms_home_hero_btn1_link_{$i}", $i == 1 ? route('admission') : ($i == 2 ? '#programs' : route('gallery')));
                $btn2Text = \App\Models\Setting::get("cms_home_hero_btn2_text_{$i}", $i == 1 ? 'প্রতিষ্ঠান সম্পর্কে' : '');
                $btn2Link = \App\Models\Setting::get("cms_home_hero_btn2_link_{$i}", $i == 1 ? '#about' : '');
            @endphp
            <!-- Slide {{ $i }} -->
            <div class="carousel-item {{ $i == 1 ? 'active' : '' }}">
                <img src="{{ Str::startsWith($bg, 'http') ? $bg : asset($bg) }}" class="d-block w-100 hero-img" alt="Hero Slide {{ $i }}">

                <div class="carousel-caption h-100 d-flex align-items-center">
                    <div class="hero-content text-start">
                        @if($badge)
                        <span class="badge bg-warning text-dark px-3 py-2 mb-3">
                            {{ $badge }}
                        </span>
                        @endif

                        @if($title)
                        <h1>
                            {!! nl2br(e($title)) !!}
                        </h1>
                        @endif

                        @if($desc)
                        <p>
                            {!! nl2br(e($desc)) !!}
                        </p>
                        @endif

                        @if($btn1Text || $btn2Text)
                        <div class="mt-4">
                            @if($btn1Text)
                            <a href="{{ $btn1Link }}" class="btn btn-primary-custom me-2">
                                {{ $btn1Text }} <i class="bi bi-arrow-right"></i>
                            </a>
                            @endif
                            
                            @if($btn2Text)
                            <a href="{{ $btn2Link }}" class="btn btn-light-custom">
                                {{ $btn2Text }}
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endfor
        </div>

        <button class="carousel-control-prev"
                type="button"
                data-bs-target="#heroSlider"
                data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>

        <button class="carousel-control-next"
                type="button"
                data-bs-target="#heroSlider"
                data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>


<!-- ================= QUICK ACCESS ================= -->
<section class="quick-access">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('admission') }}">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        <h6>অনলাইন ভর্তি</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <a href="#notice">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-megaphone"></i>
                        </div>
                        <h6>নোটিশ</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <a href="#result">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-bar-chart"></i>
                        </div>
                        <h6>ফলাফল</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <a href="#">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <h6>ক্লাস রুটিন</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <a href="#">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <h6>ফি পরিশোধ</h6>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ url('/login') }}">
                    <div class="quick-card">
                        <div class="quick-icon">
                            <i class="bi bi-person-lock"></i>
                        </div>
                        <h6>Student Portal</h6>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ================= ABOUT ================= -->
<section id="about" class="section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                @php $aboutImg = \App\Models\Setting::get('about_welcome_image', 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=1000&q=85'); @endphp
                <img src="{{ Str::startsWith($aboutImg, 'http') ? $aboutImg : asset($aboutImg) }}"
                     class="about-img"
                     alt="School Building">
            </div>

            <div class="col-lg-6">
                <span class="text-primary fw-bold">
                    {{ \App\Models\Setting::get('about_foundation_title', 'আমাদের সম্পর্কে') }}
                </span>
                <h2 class="display-6 fw-bold mt-2">
                    {{ \App\Models\Setting::get('about_welcome_title', 'একটি সুন্দর ভবিষ্যতের জন্য মানসম্মত শিক্ষা') }}
                </h2>

                <p class="text-muted mt-3">
                    {{ \App\Models\Setting::get('about_description', 'Sunrise Model School & College বাংলাদেশের একটি আধুনিক ও স্বনামধন্য শিক্ষা প্রতিষ্ঠান। আমরা শিক্ষার্থীদের একাডেমিক শিক্ষার পাশাপাশি নৈতিকতা, নেতৃত্ব, সৃজনশীলতা ও সামাজিক দায়িত্ববোধে গড়ে তোলার চেষ্টা করি।') }}
                </p>

                <ul class="about-list mt-4">
                    @if(\App\Models\Setting::get('about_core_value_1_title'))
                        <li><i class="bi bi-check-circle-fill"></i> {{ \App\Models\Setting::get('about_core_value_1_title') }}</li>
                    @else
                        <li><i class="bi bi-check-circle-fill"></i> অভিজ্ঞ ও দক্ষ শিক্ষকবৃন্দ</li>
                    @endif

                    @if(\App\Models\Setting::get('about_core_value_2_title'))
                        <li><i class="bi bi-check-circle-fill"></i> {{ \App\Models\Setting::get('about_core_value_2_title') }}</li>
                    @else
                        <li><i class="bi bi-check-circle-fill"></i> আধুনিক শিক্ষা ও প্রযুক্তি সুবিধা</li>
                    @endif

                    @if(\App\Models\Setting::get('about_core_value_3_title'))
                        <li><i class="bi bi-check-circle-fill"></i> {{ \App\Models\Setting::get('about_core_value_3_title') }}</li>
                    @else
                        <li><i class="bi bi-check-circle-fill"></i> নিরাপদ ও সুন্দর ক্যাম্পাস</li>
                    @endif

                    @if(\App\Models\Setting::get('about_core_value_4_title'))
                        <li><i class="bi bi-check-circle-fill"></i> {{ \App\Models\Setting::get('about_core_value_4_title') }}</li>
                    @else
                        <li><i class="bi bi-check-circle-fill"></i> সহশিক্ষা কার্যক্রম ও খেলাধুলা</li>
                    @endif
                </ul>

                <a href="{{ route('about') }}" class="btn btn-primary-custom mt-3">
                    বিস্তারিত জানুন <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>


<!-- ================= NOTICE + EVENTS ================= -->
<section id="notice" class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span>{{ \App\Models\Setting::get('cms_home_announcement_subtitle', 'সর্বশেষ তথ্য') }}</span>
            <h2>{{ \App\Models\Setting::get('cms_home_announcement_title', 'নোটিশ ও ইভেন্ট') }}</h2>
            <p>{{ \App\Models\Setting::get('cms_home_announcement_desc', 'প্রতিষ্ঠানের সর্বশেষ নোটিশ ও গুরুত্বপূর্ণ ইভেন্টসমূহ এখান থেকে দেখুন।') }}</p>
        </div>

        <div class="row g-4">
            <!-- Notices -->
            <div class="col-lg-7">
                <div class="notice-box">
                    <div class="notice-header d-flex justify-content-between">
                        <h5 class="mb-0">
                            <i class="bi bi-megaphone"></i> সর্বশেষ নোটিশ
                        </h5>
                        <a href="{{ route('notice') }}" class="text-white">
                            সকল নোটিশ →
                        </a>
                    </div>

                    @forelse($notices as $notice)
                        <a href="{{ route('notice.details', $notice->id) }}" class="text-decoration-none text-dark d-block">
                            <div class="notice-item">
                                <div class="date-box">
                                    <strong>{{ \Carbon\Carbon::parse($notice->published_at ?? $notice->created_at)->format('d') }}</strong>
                                    {{ \Carbon\Carbon::parse($notice->published_at ?? $notice->created_at)->format('M') }}
                                </div>
                                <div>
                                    <h6>{{ $notice->title }}</h6>
                                    <p class="text-muted mb-0 small">
                                        {{ Str::limit(strip_tags($notice->content), 60) }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="notice-item">
                            <div>
                                <h6>কোনো নতুন নোটিশ নেই</h6>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Event -->
            <div class="col-lg-5">
                @if($events && $events->count() > 0)
                    @php $event = $events->first(); @endphp
                    <div class="event-card">
                        <img src="{{ $event->image_path ? asset($event->image_path) : 'https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=85' }}"
                             alt="Event">
                        <div class="p-4">
                            <span class="badge bg-primary mb-2">Upcoming Event</span>
                            <h4>{{ $event->title }}</h4>
                            <p class="text-muted">
                                {{ Str::limit(strip_tags($event->description), 100) }}
                            </p>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <a href="{{ route('event.details', $event->id) }}" class="fw-bold text-primary">বিস্তারিত দেখুন →</a>
                                <a href="{{ route('events') }}" class="fw-bold text-primary">সকল ইভেন্ট →</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="event-card">
                        <img src="https://images.unsplash.com/photo-1546519638-68e109498ffc?auto=format&fit=crop&w=900&q=85"
                             alt="Sports">
                        <div class="p-4">
                            <span class="badge bg-primary mb-2">Upcoming Event</span>
                            <h4>Annual Sports Day</h4>
                            <p class="text-muted">
                                বার্ষিক ক্রীড়া প্রতিযোগিতা আগামী মাসে অনুষ্ঠিত হবে। বিস্তারিত জানতে নোটিশ বোর্ডে নজর রাখুন।
                            </p>
                            <a href="{{ route('events') }}" class="fw-bold text-primary">সকল ইভেন্ট দেখুন →</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>


<!-- ================= ACADEMIC PROGRAM ================= -->
<section id="programs" class="section">
    <div class="container">
        <div class="section-title">
            <span>{{ \App\Models\Setting::get('academics_programs_subtitle', 'শিক্ষাকার্যক্রম') }}</span>
            <h2>{{ \App\Models\Setting::get('academics_programs_title', 'আমাদের শিক্ষা কার্যক্রম') }}</h2>
            <p>{{ \App\Models\Setting::get('academics_hero_subtitle', 'শিক্ষার্থীদের বয়স ও শিক্ষাস্তর অনুযায়ী মানসম্মত শিক্ষা কার্যক্রম।') }}</p>
        </div>

        <div class="row g-4 justify-content-center">
            @php
                $programs = json_decode(\App\Models\Setting::get('academics_programs', '[]'), true);
                if (empty($programs)) {
                    $programs = [
                        [
                            'icon' => 'bi-book',
                            'title' => 'প্রাথমিক শাখা',
                            'desc' => '১ম থেকে ৫ম শ্রেণি',
                        ],
                        [
                            'icon' => 'bi-mortarboard',
                            'title' => 'মাধ্যমিক শাখা',
                            'desc' => '৬ষ্ঠ থেকে ১০ম শ্রেণি',
                        ],
                        [
                            'icon' => 'bi-building',
                            'title' => 'উচ্চ মাধ্যমিক',
                            'desc' => 'একাদশ ও দ্বাদশ শ্রেণি',
                        ]
                    ];
                }
            @endphp

            @foreach(array_slice($programs, 0, 4) as $program)
            <div class="col-md-6 col-lg-3">
                <div class="program-card h-100 d-flex flex-column">
                    <div class="program-icon"><i class="bi {{ $program['icon'] ?? 'bi-book' }}"></i></div>
                    <h4>{{ $program['title'] ?? '' }}</h4>
                    <p class="text-muted flex-grow-1">{{ Str::limit($program['desc'] ?? '', 80) }}</p>
                    <a href="{{ route('academics') }}" class="text-primary fw-bold mt-auto">বিস্তারিত →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


<!-- ================= PRINCIPAL MESSAGE ================= -->
<section id="principal" class="section bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5">
                @php
                    $principalPhoto = \App\Models\Setting::get('leadership_principal_photo', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=900&q=85');
                    if (!Str::startsWith($principalPhoto, 'http') && $principalPhoto) {
                        $principalPhoto = asset($principalPhoto);
                    }
                @endphp
                <img src="{{ $principalPhoto }}"
                     class="principal-img"
                     alt="Principal">
            </div>

            <div class="col-lg-7">
                <span class="text-primary fw-bold">{{ \App\Models\Setting::get('leadership_principal_title', 'অধ্যক্ষ') }} এর বাণী</span>
                <h2 class="fw-bold mt-2">শিক্ষার মাধ্যমে সুন্দর সমাজ গড়ে তুলি</h2>
                <p class="quote mt-4">
                    "{{ \App\Models\Setting::get('leadership_principal_message', 'আমাদের প্রতিটি ক্লাসরুম হলো সম্ভাবনার জায়গা। আমরা শুধু ফলাফলের ওপর নির্ভর করে সফলতা মাপি না, বরং আমাদের শিক্ষার্থীরা সমাজে কতটা সহমর্মিতা, আত্মবিশ্বাস এবং নতুন কিছু শেখার আগ্রহ নিয়ে বেড়ে উঠছে সেটাই আমাদের কাছে বেশি গুরুত্বপূর্ণ।') }}"
                </p>
                <h5 class="mb-1">{{ \App\Models\Setting::get('leadership_principal_name', 'মিসেস ফারজানা হোসাইন') }}</h5>
                <p class="text-muted">{{ \App\Models\Setting::get('leadership_principal_title', 'অধ্যক্ষ') }}</p>

                <a href="{{ route('principal-message') }}" class="btn btn-outline-primary mt-3">বিস্তারিত পড়ুন <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>


<!-- ================= STATISTICS ================= -->
<section class="stats section" style="@if(\App\Models\Setting::get('cms_home_stat_bg')) background: linear-gradient(rgba(30, 66, 159, 0.9), rgba(30, 66, 159, 0.9)), url('{{ asset(\App\Models\Setting::get('cms_home_stat_bg')) }}') center/cover no-repeat; @endif">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-lg-3">
                <div class="stat-box">
                    <i class="bi bi-calendar-check"></i>
                    <h3>{{ \App\Models\Setting::get('cms_home_stat_years', '25+') }}</h3>
                    <p class="mb-0">{{ \App\Models\Setting::get('cms_home_stat_years_label', 'বছরের ঐতিহ্য') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box">
                    <i class="bi bi-people"></i>
                    <h3>{{ \App\Models\Setting::get('cms_home_stat_students', '10000+') }}</h3>
                    <p class="mb-0">{{ \App\Models\Setting::get('cms_home_stat_students_label', 'শিক্ষার্থী') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box">
                    <i class="bi bi-person-workspace"></i>
                    <h3>{{ \App\Models\Setting::get('cms_home_stat_faculty', '500+') }}</h3>
                    <p class="mb-0">{{ \App\Models\Setting::get('cms_home_stat_faculty_label', 'শিক্ষক ও কর্মচারী') }}</p>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-box">
                    <i class="bi bi-trophy"></i>
                    <h3>{{ \App\Models\Setting::get('cms_home_stat_success', '95%') }}</h3>
                    <p class="mb-0">{{ \App\Models\Setting::get('cms_home_stat_success_label', 'গড় পাসের হার') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ================= TEACHERS ================= -->
<section id="teachers" class="section">
    <div class="container">
        <div class="section-title">
            <span>আমাদের শিক্ষক</span>
            <h2>অভিজ্ঞ শিক্ষকবৃন্দ</h2>
            <p>দক্ষ ও অভিজ্ঞ শিক্ষকবৃন্দ শিক্ষার্থীদের সঠিক দিকনির্দেশনা প্রদান করছেন।</p>
        </div>

        <div class="row g-4">
            @forelse($teachers as $teacher)
                <div class="col-md-6 col-lg-3">
                    <div class="teacher-card">
                        <img src="{{ $teacher->photoUrl() }}" alt="Teacher">
                        <div class="teacher-info">
                            <h5>{{ $teacher->user->name ?? 'N/A' }}</h5>
                            <p class="text-muted mb-0">{{ $teacher->designation ?? 'Teacher' }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted">
                    <p>কোনো শিক্ষকের তথ্য পাওয়া যায়নি।</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('faculty') }}" class="btn btn-outline-primary px-4 py-2">আরো দেখুন <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
</section>


<!-- ================= RESULT ================= -->
<section id="result" class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span>Academic Result</span>
            <h2>পরীক্ষার ফলাফল</h2>
            <p>Roll ও Registration Number ব্যবহার করে আপনার ফলাফল খুঁজে দেখুন।</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm p-4">
                    <form action="{{ url('/result') }}" method="GET">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">শ্রেণী (Class)</label>
                                <select class="form-select" name="class_id" required>
                                    <option value="">নির্বাচন করুন</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">পরীক্ষা (Exam)</label>
                                <select class="form-select" name="exam_id" required>
                                    <option value="">নির্বাচন করুন</option>
                                    @foreach($exams as $exam)
                                        <option value="{{ $exam->id }}">{{ $exam->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Roll Number</label>
                                <input type="text" name="roll" class="form-control" placeholder="Roll Number" required>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary-custom">
                                    <i class="bi bi-search"></i> ফলাফল দেখুন
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ================= FACILITIES ================= -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <span>{{ \App\Models\Setting::get('cms_home_facility_subtitle', 'আমাদের সুবিধা') }}</span>
            <h2>{{ \App\Models\Setting::get('cms_home_facility_title', 'আধুনিক ক্যাম্পাস সুবিধা') }}</h2>
        </div>
        <div class="row g-4">
            @php
                $rawFacilities = \App\Models\Setting::get('cms_home_facilities', null);
                $facilities = [];

                if ($rawFacilities === null) {
                    // Try to migrate old keys
                    for($i=1; $i<=8; $i++) {
                        $t = \App\Models\Setting::get("cms_home_facility_{$i}_title", '');
                        if($t !== '') {
                            $facilities[] = [
                                'icon' => \App\Models\Setting::get("cms_home_facility_{$i}_icon", ''),
                                'title' => $t,
                                'desc' => \App\Models\Setting::get("cms_home_facility_{$i}_desc", '')
                            ];
                        }
                    }

                    // Default fallback if no old keys exist
                    if (empty($facilities)) {
                        $facilities = [
                            ['icon' => 'bi-book-half', 'title' => 'আধুনিক লাইব্রেরি', 'desc' => 'সমৃদ্ধ বই ও পড়াশোনার পরিবেশ।'],
                            ['icon' => 'bi-pc-display', 'title' => 'কম্পিউটার ল্যাব', 'desc' => 'আধুনিক কম্পিউটার ও ইন্টারনেট সুবিধা।'],
                            ['icon' => 'bi-eyedropper', 'title' => 'বিজ্ঞান ল্যাব', 'desc' => 'ব্যবহারিক শিক্ষার জন্য আধুনিক ল্যাব।'],
                            ['icon' => 'bi-bus-front', 'title' => 'স্কুল পরিবহন', 'desc' => 'নিরাপদ ও নির্ভরযোগ্য পরিবহন ব্যবস্থা।'],
                            ['icon' => 'bi-trophy', 'title' => 'খেলাধুলা', 'desc' => 'বিভিন্ন খেলাধুলা ও ক্রীড়া কার্যক্রম।'],
                            ['icon' => 'bi-heart-pulse', 'title' => 'মেডিকেল সুবিধা', 'desc' => 'শিক্ষার্থীদের প্রাথমিক স্বাস্থ্যসেবা।']
                        ];
                    }
                } else {
                    $facilities = json_decode($rawFacilities, true) ?? [];
                }
            @endphp

            @foreach($facilities as $facility)
                @if(!empty($facility['title']))
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="facility-card h-100 d-flex flex-column">
                        <div class="facility-icon"><i class="bi {{ $facility['icon'] ?? 'bi-check-circle' }}"></i></div>
                        <h5>{{ $facility['title'] }}</h5>
                        <p class="text-muted small flex-grow-1">{{ $facility['desc'] ?? '' }}</p>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>
</section>


<!-- ================= GALLERY ================= -->
<section class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span>Photo Gallery</span>
            
        </div>
        <div class="row g-3">
            @foreach($homeGallery as $item)
            <div class="col-md-4">
                <img src="{{ $item['image'] ?? 'https://via.placeholder.com/900x600' }}" class="gallery-img" alt="{{ $item['title'] ?? 'Gallery Image' }}">
            </div>
            @endforeach
        </div>
        <div class="text-center mt-4">
            <a href="{{ route('gallery') }}" class="btn btn-primary-custom">সকল ছবি দেখুন →</a>
        </div>
    </div>
</section>


<!-- ================= ADMISSION CTA ================= -->
<section id="admission" class="admission-cta">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-warning text-dark mb-3">
                    {{ \App\Models\Setting::get('site_admission_text', 'Admission Open 2026-27') }}
                </span>
                <h2 class="display-6 fw-bold">{{ \App\Models\Setting::get('cms_home_admission_title', 'আপনার সন্তানের সুন্দর ভবিষ্যতের যাত্রা শুরু হোক আজ থেকেই') }}</h2>
                <p class="mb-0">{{ \App\Models\Setting::get('cms_home_admission_desc', 'অনলাইনে ভর্তি আবেদন করুন এবং আপনার সন্তানের জন্য একটি মানসম্মত শিক্ষার পরিবেশ নিশ্চিত করুন।') }}</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <a href="{{ \App\Models\Setting::get('cms_home_admission_btn_link', route('admission')) }}" class="btn btn-warning btn-lg fw-bold me-2">{{ \App\Models\Setting::get('cms_home_admission_btn_text', 'অনলাইন ভর্তি') }}</a>
            </div>
        </div>
    </div>
</section>


<!-- ================= CONTACT ================= -->
<section id="contact" class="section bg-light">
    <div class="container">
        <div class="section-title">
            <span>যোগাযোগ</span>
            <h2>আমাদের সাথে যোগাযোগ করুন</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-geo-alt"></i></div>
                    <h5>ঠিকানা</h5>
                    <p class="text-muted mb-0">{{ \App\Models\Setting::get('school_address', 'ঢাকা, বাংলাদেশ') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-telephone"></i></div>
                    <h5>ফোন</h5>
                    <p class="text-muted mb-0">{{ \App\Models\Setting::get('school_phone', '+880 1XXXXXXXXX') }}</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card">
                    <div class="contact-icon"><i class="bi bi-envelope"></i></div>
                    <h5>ই-মেইল</h5>
                    <p class="text-muted mb-0">{{ \App\Models\Setting::get('school_email', 'info@school.edu.bd') }}</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
