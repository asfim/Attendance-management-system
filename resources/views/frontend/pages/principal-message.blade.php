@extends('frontend.layouts.app')

@push('styles')
<style>
    .leadership-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .leadership-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('{!! \App\Models\Setting::get('leadership_hero_bg', 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?q=80&w=2000&auto=format&fit=crop') !!}') center/cover;
        opacity: 0.15;
        mix-blend-mode: overlay;
    }
    .leadership-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
        opacity: 0.7;
    }

    .message-section {
        padding: 80px 0;
        position: relative;
    }
    .message-section:nth-child(even) {
        background-color: #f8fafc;
    }
    
    .leader-photo-wrapper {
        position: relative;
        border-radius: 2rem;
        overflow: hidden;
        box-shadow: 0 25px 50px rgba(10,27,50,0.15);
        display: inline-block;
    }
    .leader-photo-wrapper::before {
        content: '';
        position: absolute;
        inset: 0;
        border: 4px solid var(--accent);
        border-radius: inherit;
        z-index: 2;
        pointer-events: none;
    }
    .leader-photo {
        width: 100%;
        max-width: 400px;
        height: auto;
        display: block;
        transition: transform 0.7s ease;
    }
    .leader-photo-wrapper:hover .leader-photo {
        transform: scale(1.05);
    }

    .quote-mark {
        font-size: 6rem;
        line-height: 0;
        color: rgba(249, 168, 37, 0.2);
        font-family: 'Playfair Display', serif;
        position: relative;
        top: 40px;
        left: -10px;
    }
    
    .message-content {
        font-size: 1.15rem;
        line-height: 1.8;
        color: var(--slate);
        position: relative;
        z-index: 1;
    }
    .message-content::first-letter {
        font-size: 3rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
        float: left;
        margin-right: 8px;
        margin-top: 5px;
        font-family: 'Playfair Display', serif;
    }

    .leader-name {
        font-family: 'Playfair Display', serif;
        color: var(--navy-deep);
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .leader-title {
        color: var(--accent);
        text-transform: uppercase;
        letter-spacing: 2px;
        font-size: 0.9rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')

<!-- Header Section -->
<div class="leadership-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ \App\Models\Setting::get('leadership_hero_badge', 'বার্তা') }}</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{!! \App\Models\Setting::get('leadership_hero_title', 'আমাদের <span class="text-warning">নেতৃত্বের</span> কথা') !!}</h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ \App\Models\Setting::get('leadership_hero_subtitle', 'আমাদের প্রতিষ্ঠানকে সঠিক দিকনির্দেশনা, সততা এবং উৎকর্ষের প্রতিশ্রুতি দিয়ে এগিয়ে নিয়ে যাচ্ছেন।') }}
        </p>
    </div>
</div>

<!-- Chairman Message -->
<section class="message-section bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 text-center text-lg-start">
                <div class="leader-photo-wrapper">
                    <img src="{{ \App\Models\Setting::get('leadership_chairman_photo', 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop') }}" class="leader-photo" alt="Chairman">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ps-lg-4">
                    <span class="quote-mark">"</span>
                    <div class="message-content fst-italic mb-5">
                        {{ \App\Models\Setting::get('leadership_chairman_message', 'আমাদের প্রতিটি শিক্ষার্থীর পরিবারের প্রতি আমাদের একটি সহজ প্রতিশ্রুতি রয়েছে: একটি নিরাপদ, সুশৃঙ্খল এবং অনুপ্রেরণাদায়ক পরিবেশ যেখানে বাচ্চারা নিজেদের বিকশিত করতে পারে। আধুনিক শিক্ষার সাথে সাথে শক্ত নৈতিক মূল্যবোধের ভিত্তি গড়তে আমরা বদ্ধপরিকর।') }}
                    </div>
                    
                    <div style="border-left: 3px solid var(--accent); padding-left: 1.5rem;">
                        <div class="leader-name">{{ \App\Models\Setting::get('leadership_chairman_name', 'ড. আমিনুল করিম') }}</div>
                        <div class="leader-title">{{ \App\Models\Setting::get('leadership_chairman_title', 'চেয়ারম্যান, বোর্ড অব ট্রাস্টিজ') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Principal Message -->
<section class="message-section">
    <div class="container">
        <div class="row align-items-center g-5 flex-lg-row-reverse">
            <div class="col-lg-5 text-center text-lg-end">
                <div class="leader-photo-wrapper">
                    <img src="{{ \App\Models\Setting::get('leadership_principal_photo', 'https://images.unsplash.com/photo-1607746882042-944635dfe10e?q=80&w=600&auto=format&fit=crop') }}" class="leader-photo" alt="Principal">
                </div>
            </div>
            <div class="col-lg-7">
                <div class="pe-lg-4">
                    <span class="quote-mark">"</span>
                    <div class="message-content fst-italic mb-5">
                        {{ \App\Models\Setting::get('leadership_principal_message', 'আমাদের প্রতিটি ক্লাসরুম হলো সম্ভাবনার জায়গা। আমরা শুধু ফলাফলের ওপর নির্ভর করে সফলতা মাপি না, বরং আমাদের শিক্ষার্থীরা সমাজে কতটা সহমর্মিতা, আত্মবিশ্বাস এবং নতুন কিছু শেখার আগ্রহ নিয়ে বেড়ে উঠছে সেটাই আমাদের কাছে বেশি গুরুত্বপূর্ণ।') }}
                    </div>
                    
                    <div style="border-left: 3px solid var(--accent); padding-left: 1.5rem;" class="text-start">
                        <div class="leader-name">{{ \App\Models\Setting::get('leadership_principal_name', 'মিসেস ফারজানা হোসাইন') }}</div>
                        <div class="leader-title">{{ \App\Models\Setting::get('leadership_principal_title', 'অধ্যক্ষ') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
