<!-- ================= FOOTER ================= -->
<footer>
    <div class="container py-5">
        <div class="row g-5">
            <!-- About -->
            <div class="col-lg-4">
                <div class="d-flex align-items-center mb-3">
                    <div>
                        @if(\App\Models\Setting::get('site_logo'))
                            <img src="{{ asset(\App\Models\Setting::get('site_logo')) }}" alt="Logo" style="max-height: 60px; margin-right: 15px;">
                        @else
                            <div class="logo-box">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                        @endif
                    </div>
                  
                </div>
                <p>
                    মানসম্মত শিক্ষা, নৈতিকতা ও আধুনিক
                    প্রযুক্তির সমন্বয়ে আমরা গড়ে তুলছি
                    আগামী দিনের যোগ্য নাগরিক।
                </p>
                <div class="mt-3">
                    <a href="{{ \App\Models\Setting::get('social_facebook', '#') }}" class="social-btn"><i class="bi bi-facebook"></i></a>
                    <a href="{{ \App\Models\Setting::get('social_youtube', '#') }}" class="social-btn"><i class="bi bi-youtube"></i></a>
                    <a href="{{ \App\Models\Setting::get('social_instagram', '#') }}" class="social-btn"><i class="bi bi-instagram"></i></a>
                </div>
            </div>
            <!-- Quick Links -->
            <div class="col-6 col-lg-2">
                <h5>Quick Links</h5>
                <a href="{{ route('home') }}">হোম</a>
                <a href="{{ route('home') }}#about">আমাদের সম্পর্কে</a>
                <a href="{{ route('home') }}#programs">শিক্ষাকার্যক্রম</a>
                <a href="{{ route('home') }}#notice">নোটিশ</a>
                <a href="{{ route('home') }}#result">ফলাফল</a>
                <a href="{{ route('home') }}#contact">যোগাযোগ</a>
            </div>
            <!-- Student -->
            <div class="col-6 col-lg-3">
                <h5>Student Services</h5>
                <a href="{{ url('/login') }}">Student Portal</a>
                <a href="{{ url('/login') }}">Parent Portal</a>
                <a href="{{ route('home') }}#result">Exam Result</a>
                <a href="{{ route('admission') }}">Online Admission</a>
            </div>
            <!-- Contact -->
            <div class="col-lg-3">
                <h5>যোগাযোগ</h5>
                <p><i class="bi bi-geo-alt me-2"></i> {{ \App\Models\Setting::get('school_address', 'ঢাকা, বাংলাদেশ') }}</p>
                <p><i class="bi bi-telephone me-2"></i> {{ \App\Models\Setting::get('school_phone', '+880 1XXXXXXXXX') }}</p>
                <p><i class="bi bi-envelope me-2"></i> {{ \App\Models\Setting::get('school_email', 'info@school.edu.bd') }}</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <small>© {{ date('Y') }} {{ \App\Models\Setting::get('school_name', 'Sunrise Model School & College') }}. All Rights Reserved.</small>
                </div>
                <div class="col-md-6 text-md-end">
                    <small>Developed with ❤️ in Bangladesh</small>
                </div>
            </div>
        </div>
    </div>
</footer>
