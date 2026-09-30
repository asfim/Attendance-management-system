@extends('frontend.layouts.app')

@push('styles')
<style>
    .contact-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .contact-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') repeat;
        opacity: 0.5;
    }

    .contact-info-card {
        background: #fff;
        border-radius: 16px;
        padding: 30px;
        height: 100%;
        border: 1px solid rgba(0,0,0,0.03);
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        transition: all 0.3s ease;
    }
    .contact-info-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
    }
    .info-icon-box {
        width: 60px;
        height: 60px;
        border-radius: 15px;
        background: rgba(13, 71, 161, 0.1);
        color: var(--primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }
    .contact-info-card:hover .info-icon-box {
        background: var(--primary);
        color: #fff;
    }
    .info-title {
        font-family: 'Playfair Display', serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--navy-deep);
        margin-bottom: 10px;
    }
    .info-text {
        color: var(--slate);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 0;
    }

    .contact-form-premium {
        background: #fff;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
    }
    .form-control-premium {
        border-radius: 12px;
        padding: 12px 20px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        background: #f8fafc;
        transition: all 0.3s ease;
    }
    .form-control-premium:focus {
        background: #fff;
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(249, 168, 37, 0.15);
        outline: none;
    }
    .form-label-premium {
        font-weight: 600;
        font-size: 0.85rem;
        color: var(--navy-deep);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
    }
    
    .btn-submit-premium {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        color: #fff;
        border: none;
        padding: 14px 30px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 1rem;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        box-shadow: 0 10px 20px rgba(13, 71, 161, 0.2);
    }
    .btn-submit-premium:hover {
        background: linear-gradient(135deg, var(--navy-deep) 0%, var(--primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 15px 25px rgba(13, 71, 161, 0.3);
    }

    .social-link-premium {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #fff;
        border: 1px solid #e2e8f0;
        color: var(--navy-deep);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        transition: all 0.3s ease;
        margin-right: 10px;
    }
    .social-link-premium:hover {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(13, 71, 161, 0.2);
    }
    
    .map-container {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        height: 450px;
        border: 1px solid rgba(0,0,0,0.05);
    }
</style>
@endpush

@section('content')

<!-- Contact Header -->
<div class="contact-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">Get In Touch</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{!! strip_tags($settings['contact_hero_title'] ?? 'Contact Us') !!}</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ $settings['contact_hero_subtitle'] ?? 'Have a question about admissions, academic programs, or campus facilities? We are here to help and would love to hear from you.' }}
        </p>
    </div>
</div>

<section class="section bg-light py-5">
    <div class="container">
        
        <!-- Contact Info Cards -->
        <div class="row g-4 mb-5 pb-3">
            <div class="col-md-6 col-lg-3">
                <div class="contact-info-card text-center">
                    <div class="info-icon-box mx-auto">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h3 class="info-title">Address</h3>
                    <p class="info-text">{{ $settings['contact_address'] ?? \App\Models\Setting::get('school_address', '123 Education Boulevard, Academic District, Dhaka 1212') }}</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="contact-info-card text-center">
                    <div class="info-icon-box mx-auto">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <h3 class="info-title">Phone</h3>
                    <p class="info-text">{{ $settings['contact_phone'] ?? \App\Models\Setting::get('school_phone', '+880 1234 567 890') }}</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="contact-info-card text-center">
                    <div class="info-icon-box mx-auto">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <h3 class="info-title">Email</h3>
                    <p class="info-text">{{ $settings['contact_email'] ?? \App\Models\Setting::get('school_email', 'info@schoolmanagement.com') }}</p>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="contact-info-card text-center">
                    <div class="info-icon-box mx-auto">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <h3 class="info-title">Office Hours</h3>
                    <p class="info-text">{{ $settings['contact_office_hours'] ?? 'Sunday - Thursday: 8:00 AM - 4:00 PM' }}</p>
                </div>
            </div>
        </div>

        <div class="row g-5 align-items-center">
            
            <!-- Left Side: Form -->
            <div class="col-lg-7">
                <div class="contact-form-premium">
                    <h2 class="font-display fw-bold mb-2 text-dark" style="font-family: 'Playfair Display', serif;">Send us a message</h2>
                    <p class="text-muted mb-4 pb-2">We typically reply within 24 hours.</p>
                    
                    @if(session('success'))
                        <div class="alert alert-success d-flex align-items-center rounded-3 mb-4" role="alert">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-premium">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" class="form-control form-control-premium" placeholder="John" value="{{ old('first_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-premium">Last Name</label>
                                <input type="text" name="last_name" class="form-control form-control-premium" placeholder="Doe" value="{{ old('last_name') }}">
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label form-label-premium">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control form-control-premium" placeholder="john.doe@example.com" value="{{ old('email') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label form-label-premium">Phone Number</label>
                                <input type="text" name="phone" class="form-control form-control-premium" placeholder="+880 1700 000000" value="{{ old('phone') }}">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label form-label-premium">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control form-control-premium" placeholder="e.g. Admission Query" value="{{ old('subject', 'General Inquiry') }}" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label form-label-premium">Your Message <span class="text-danger">*</span></label>
                            <textarea name="message" rows="5" class="form-control form-control-premium resize-none" placeholder="How can we help you today?" required>{{ old('message') }}</textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-submit-premium w-100 py-3">
                            Send Message <i class="bi bi-send-fill ms-2"></i>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Right Side: Socials & Map -->
            <div class="col-lg-5">
                @if(!empty($settings['social_facebook']) || !empty($settings['social_instagram']) || !empty($settings['social_twitter']))
                <div class="mb-5 bg-white p-4 rounded-4 shadow-sm border border-light">
                    <h4 class="font-display fw-bold mb-3 text-dark" style="font-family: 'Playfair Display', serif;">Connect With Us</h4>
                    <p class="text-muted mb-4 text-sm">Follow our social media channels for the latest updates.</p>
                    <div class="d-flex">
                        @if(!empty($settings['social_facebook']))
                        <a href="{{ $settings['social_facebook'] }}" target="_blank" class="social-link-premium">
                            <i class="bi bi-facebook"></i>
                        </a>
                        @endif
                        @if(!empty($settings['social_twitter']))
                        <a href="{{ $settings['social_twitter'] }}" target="_blank" class="social-link-premium">
                            <i class="bi bi-twitter-x"></i>
                        </a>
                        @endif
                        @if(!empty($settings['social_instagram']))
                        <a href="{{ $settings['social_instagram'] }}" target="_blank" class="social-link-premium">
                            <i class="bi bi-instagram"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @endif
                
                @php
                    $mapSetting = $settings['contact_map_iframe'] ?? '';
                    $defaultMapUrl = "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d14602.700311746408!2d90.39706349999999!3d23.794582!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3755c711d13bbec7%3A0xc47f7c3e8e2263f2!2sBanani%2C%20Dhaka!5e0!3m2!1sen!2sbd!4v1714900000000!5m2!1sen!2sbd";
                    
                    if (str_contains($mapSetting, '<iframe') && preg_match('/src="([^"]+)"/', $mapSetting, $matches)) {
                        $mapUrl = $matches[1];
                    } elseif (!empty($mapSetting)) {
                        $mapUrl = trim($mapSetting);
                    } else {
                        $mapUrl = $defaultMapUrl;
                    }
                @endphp
                
                <div class="map-container bg-white p-2">
                    <iframe src="{{ $mapUrl }}" width="100%" height="100%" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            
        </div>
    </div>
</section>
@endsection
