@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-circle-info me-2"></i> About Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form action="{{ route('admin.cms.about.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">

                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Hero Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Page Title</label>
                            <input type="text" class="form-control" name="about_page_title" value="{{ $settings['about_page_title'] ?? 'আমাদের সম্পর্কে -' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Badge Text</label>
                            <input type="text" class="form-control" name="about_hero_badge" value="{{ $settings['about_hero_badge'] ?? 'আমাদের গল্প জানুন' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Background Image</label>
                            <input type="file" class="form-control" name="about_hero_bg" accept="image/*">
                            @if(isset($settings['about_hero_bg']))
                                <img src="{{ $settings['about_hero_bg'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Hero Subtitle</label>
                            <textarea name="about_hero_subtitle" class="form-control" rows="2">{{ $settings['about_hero_subtitle'] ?? 'শিক্ষায় উৎকর্ষের এক অনন্য ঐতিহ্য, চরিত্র গঠনে প্রতিশ্রুতিবদ্ধ এবং এমন একটি শিক্ষাঙ্গন যা শিক্ষার্থীদের দ্রুত পরিবর্তনশীল বিশ্বের জন্য প্রস্তুত করে।' }}</textarea>
                        </div>

                        <div class="col-12 mt-2"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Welcome / About Section (Also appears on Home)</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Title</label>
                            <input type="text" class="form-control" name="about_foundation_title" value="{{ $settings['about_foundation_title'] ?? 'আমাদের ভিত্তি' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Welcome Title</label>
                            <input type="text" class="form-control" name="about_welcome_title" value="{{ $settings['about_welcome_title'] ?? 'কৌতূহল ও চরিত্র গঠনের এক অনন্য প্রাঙ্গণ' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Description Paragraph 1</label>
                            <textarea name="about_description" class="form-control" rows="4">{{ $settings['about_description'] ?? '১৯৯৯ সাল থেকে, সানরাইজ মডেল স্কুল হাজার হাজার শিক্ষার্থীকে প্রাথমিক থেকে উচ্চ মাধ্যমিক পর্যন্ত সুশিক্ষা দিয়ে আসছে। আমরা বিশ্বাস করি প্রকৃত শিক্ষা শুধু বইয়ের পাতায় সীমাবদ্ধ নয়।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Description Paragraph 2</label>
                            <textarea name="about_description2" class="form-control" rows="4">{{ $settings['about_description2'] ?? 'আমাদের শ্রেণীকক্ষগুলো এমনভাবে সাজানো হয়েছে যাতে শিক্ষার্থীরা নিজেরাই নতুন কিছু শিখতে পারে। আমরা শিক্ষার্থীদের শুধু ভবিষ্যতের ক্যারিয়ারের জন্যই নয়, বরং একটি অর্থবহ জীবনের জন্যও প্রস্তুত করি।' }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Years of Excellence (e.g. 25)</label>
                            <input type="number" class="form-control" name="about_years" value="{{ $settings['about_years'] ?? '২৫' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Years Text</label>
                            <input type="text" class="form-control" name="about_years_text" value="{{ $settings['about_years_text'] ?? 'বছরের শিক্ষাগত উৎকর্ষ' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Welcome Image</label>
                            <input type="file" class="form-control" name="about_welcome_image" accept="image/*">
                            @if(isset($settings['about_welcome_image']))
                                <img src="{{ $settings['about_welcome_image'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>

                        <div class="col-12 mt-2"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Mission &amp; Vision</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Mission Title</label>
                            <input type="text" name="about_mission_title" class="form-control mb-2" value="{{ $settings['about_mission_title'] ?? 'আমাদের লক্ষ্য' }}">
                            <label class="form-label text-secondary">Our Mission</label>
                            <textarea name="about_mission" class="form-control" rows="3">{{ $settings['about_mission'] ?? 'শিক্ষার্থীদের স্বাধীন চিন্তাবিদ হিসেবে গড়ে তোলা, যারা জ্ঞান, সততা এবং আত্মবিশ্বাসের সাথে বিশ্বায়নের এই যুগে নেতৃত্ব দিতে পারবে।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Vision Title</label>
                            <input type="text" name="about_vision_title" class="form-control mb-2" value="{{ $settings['about_vision_title'] ?? 'আমাদের রূপকল্প' }}">
                            <label class="form-label text-secondary">Our Vision</label>
                            <textarea name="about_vision" class="form-control" rows="3">{{ $settings['about_vision'] ?? 'আগামীর ভবিষ্যৎ ও যোগ্য নেতা গড়ার লক্ষ্যে একটি আধুনিক, যুগোপযোগী ও বিশ্বাসযোগ্য শিক্ষাপ্রতিষ্ঠান হিসেবে নিজেদের প্রতিষ্ঠিত করা।' }}</textarea>
                        </div>



                        <div class="col-12 mt-2">
                            <h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Core Values (What Drives Us)</h6>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary">Section Subtitle</label>
                                    <input type="text" name="about_core_values_subtitle" class="form-control" value="{{ $settings['about_core_values_subtitle'] ?? 'আমাদের অনুপ্রেরণা' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary">Section Title</label>
                                    <input type="text" name="about_core_values_title" class="form-control" value="{{ $settings['about_core_values_title'] ?? 'আমাদের মূলনীতিসমূহ' }}">
                                </div>
                            </div>
                        </div>
                        @for($i=1; $i<=4; $i++)
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Value {{ $i }} Title</label>
                            <input type="text" name="about_core_value_{{ $i }}_title" class="form-control mb-2" value="{{ $settings['about_core_value_'.$i.'_title'] ?? '' }}">
                            <label class="form-label text-secondary">Value {{ $i }} Description</label>
                            <textarea name="about_core_value_{{ $i }}_desc" class="form-control" rows="3">{{ $settings['about_core_value_'.$i.'_desc'] ?? '' }}</textarea>
                        </div>
                        @endfor

                        <div class="col-12 mt-2">
                            <h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Our Timeline (Journey / History)</h6>
                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <label class="form-label text-secondary">Timeline Subtitle</label>
                                    <input type="text" name="about_timeline_subtitle" class="form-control" value="{{ $settings['about_timeline_subtitle'] ?? 'আমাদের ইতিহাস' }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-secondary">Timeline Title</label>
                                    <input type="text" name="about_timeline_title" class="form-control" value="{{ $settings['about_timeline_title'] ?? 'বছরের অর্জন' }}">
                                </div>
                            </div>
                        </div>
                        @for($i=1; $i<=4; $i++)
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Timeline {{ $i }} Year</label>
                            <input type="text" name="about_timeline_{{ $i }}_year" class="form-control mb-2" value="{{ $settings['about_timeline_'.$i.'_year'] ?? '' }}" placeholder="e.g. 1999">
                            <label class="form-label text-secondary">Timeline {{ $i }} Title</label>
                            <input type="text" name="about_timeline_{{ $i }}_title" class="form-control mb-2" value="{{ $settings['about_timeline_'.$i.'_title'] ?? '' }}">
                            <label class="form-label text-secondary">Timeline {{ $i }} Description</label>
                            <textarea name="about_timeline_{{ $i }}_desc" class="form-control" rows="3">{{ $settings['about_timeline_'.$i.'_desc'] ?? '' }}</textarea>
                        </div>
                        @endfor

                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save About Page Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
