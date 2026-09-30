@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-chalkboard-user me-2"></i> Principal Message Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form action="{{ route('admin.cms.principal-message.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">

                        <!-- Hero Section -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Hero Section</h6></div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Badge Text</label>
                            <input type="text" class="form-control" name="leadership_hero_badge" value="{{ $settings['leadership_hero_badge'] ?? 'বার্তা' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title</label>
                            <input type="text" class="form-control" name="leadership_hero_title" value="{{ $settings['leadership_hero_title'] ?? 'আমাদের <span class=\'text-warning\'>নেতৃত্বের</span> কথা' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Subtitle</label>
                            <textarea name="leadership_hero_subtitle" class="form-control" rows="2">{{ $settings['leadership_hero_subtitle'] ?? 'আমাদের প্রতিষ্ঠানকে সঠিক দিকনির্দেশনা, সততা এবং উৎকর্ষের প্রতিশ্রুতি দিয়ে এগিয়ে নিয়ে যাচ্ছেন।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Background Image</label>
                            <input type="file" class="form-control" name="leadership_hero_bg" accept="image/*">
                            @if(isset($settings['leadership_hero_bg']))
                                <img src="{{ $settings['leadership_hero_bg'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>

                        <!-- Chairman Message -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Chairman Section</h6></div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Chairman Name</label>
                            <input type="text" class="form-control" name="leadership_chairman_name" value="{{ $settings['leadership_chairman_name'] ?? 'ড. আমিনুল করিম' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Chairman Title</label>
                            <input type="text" class="form-control" name="leadership_chairman_title" value="{{ $settings['leadership_chairman_title'] ?? 'চেয়ারম্যান, বোর্ড অব ট্রাস্টিজ' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Chairman Message</label>
                            <textarea name="leadership_chairman_message" class="form-control" rows="4">{{ $settings['leadership_chairman_message'] ?? 'আমাদের প্রতিটি শিক্ষার্থীর পরিবারের প্রতি আমাদের একটি সহজ প্রতিশ্রুতি রয়েছে: একটি নিরাপদ, সুশৃঙ্খল এবং অনুপ্রেরণাদায়ক পরিবেশ যেখানে বাচ্চারা নিজেদের বিকশিত করতে পারে। আধুনিক শিক্ষার সাথে সাথে শক্ত নৈতিক মূল্যবোধের ভিত্তি গড়তে আমরা বদ্ধপরিকর।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Chairman Photo <small class="text-muted">(Recommended: 600x800px or Portrait)</small></label>
                            <input type="file" class="form-control" name="leadership_chairman_photo" accept="image/*">
                            @if(isset($settings['leadership_chairman_photo']))
                                <img src="{{ $settings['leadership_chairman_photo'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>

                        <!-- Principal Message -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Principal Section</h6></div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Principal Name</label>
                            <input type="text" class="form-control" name="leadership_principal_name" value="{{ $settings['leadership_principal_name'] ?? 'মিসেস ফারজানা হোসাইন' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Principal Title</label>
                            <input type="text" class="form-control" name="leadership_principal_title" value="{{ $settings['leadership_principal_title'] ?? 'অধ্যক্ষ' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Principal Message</label>
                            <textarea name="leadership_principal_message" class="form-control" rows="4">{{ $settings['leadership_principal_message'] ?? 'আমাদের প্রতিটি ক্লাসরুম হলো সম্ভাবনার জায়গা। আমরা শুধু ফলাফলের ওপর নির্ভর করে সফলতা মাপি না, বরং আমাদের শিক্ষার্থীরা সমাজে কতটা সহমর্মিতা, আত্মবিশ্বাস এবং নতুন কিছু শেখার আগ্রহ নিয়ে বেড়ে উঠছে সেটাই আমাদের কাছে বেশি গুরুত্বপূর্ণ।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Principal Photo <small class="text-muted">(Recommended: 600x800px or Portrait)</small></label>
                            <input type="file" class="form-control" name="leadership_principal_photo" accept="image/*">
                            @if(isset($settings['leadership_principal_photo']))
                                <img src="{{ $settings['leadership_principal_photo'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
