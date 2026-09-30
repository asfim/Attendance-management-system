{{-- ================= SITE HEADER (Sticky Wrapper) ================= --}}
<div class="site-header">
<!-- ================= TOP BAR ================= -->
<div class="topbar py-2 d-none d-lg-block">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap">

            <div>
                <span class="me-3">
                    <i class="bi bi-telephone"></i>
                    {{ \App\Models\Setting::get('school_phone', '+880 1XXXXXXXXX') }}
                </span>

                <span>
                    <i class="bi bi-envelope"></i>
                    {{ \App\Models\Setting::get('school_email', 'info@school.edu.bd') }}
                </span>
            </div>

            <div class="mt-1 mt-md-0">
                <span>EIIN: {{ \App\Models\Setting::get('school_eiin', '123456') }}</span>

                @if(\App\Models\Setting::get('social_facebook'))
                <a href="{{ \App\Models\Setting::get('social_facebook') }}" target="_blank">
                    <i class="bi bi-facebook"></i>
                </a>
                @else
                <a href="#">
                    <i class="bi bi-facebook"></i>
                </a>
                @endif

                @if(\App\Models\Setting::get('social_youtube'))
                <a href="{{ \App\Models\Setting::get('social_youtube') }}" target="_blank">
                    <i class="bi bi-youtube"></i>
                </a>
                @else
                <a href="#">
                    <i class="bi bi-youtube"></i>
                </a>
                @endif
            </div>

        </div>
    </div>
</div>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
            @if(\App\Models\Setting::get('site_logo'))
                <img src="{{ asset(\App\Models\Setting::get('site_logo')) }}" alt="Logo">
            @else
                <div class="logo-box">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
            @endif
        </a>

        <button class="navbar-toggler px-2 py-1" style="font-size: 0.875rem;" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon" style="width: 1.2em; height: 1.2em;"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">হোম</a>
                </li>
                <!-- About -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('about') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">আমাদের সম্পর্কে</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('about') }}">প্রতিষ্ঠান পরিচিতি</a></li>
                        <li><a class="dropdown-item" href="{{ route('principal-message') }}">প্রধান শিক্ষকের বাণী</a></li>
                    </ul>
                </li>
                <!-- Academic -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->is('academics*') || request()->routeIs('departments') || request()->routeIs('calendar') ? 'active' : '' }}" href="#" data-bs-toggle="dropdown">শিক্ষাকার্যক্রম</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('academics') }}">শ্রেণিসমূহ (Programs)</a></li>
                        <li><a class="dropdown-item" href="{{ route('departments') }}">বিভাগসমূহ (Departments)</a></li>
                        <li><a class="dropdown-item" href="{{ route('calendar') }}">একাডেমিক ক্যালেন্ডার</a></li>
                        <li><a class="dropdown-item" href="{{ route('faculty') }}">শিক্ষকবৃন্দ</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('campus') ? 'active' : '' }}" href="{{ route('campus') }}">ক্যাম্পাস</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">গ্যালারি</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notice') ? 'active' : '' }}" href="{{ route('notice') }}">নোটিশ</a>
                </li>
                <!-- Admission -->
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admission') ? 'active' : '' }}" href="{{ route('admission') }}">ভর্তি</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('result') ? 'active' : '' }}" href="{{ route('result') }}">ফলাফল</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">যোগাযোগ</a>
                </li>
            </ul>
            <a href="{{ route('login') }}" class="btn btn-admission ms-lg-3">
                <i class="bi bi-box-arrow-in-right"></i> লগইন
            </a>
        </div>
    </div>
</nav>
</div>{{-- .site-header --}}


