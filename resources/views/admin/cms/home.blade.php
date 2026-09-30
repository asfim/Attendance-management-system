@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-house me-2"></i>Homepage Settings</h5>
            </div>
            <div class="card-body p-4">
                
                <form action="{{ route('admin.cms.home.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- HERO SECTION -->
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-image me-2"></i>Hero Section (3 Slides)</h6>
                    
                    <div class="row g-4 mb-4">
                        @for($i=1; $i<=3; $i++)
                        <div class="col-md-12">
                            <div class="card bg-light border">
                                <div class="card-header fw-bold">Slide {{ $i }}</div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-12">
                                            <label class="form-label">Background Image (cms_home_hero_bg_{{ $i }})</label>
                                            <input type="file" class="form-control" name="cms_home_hero_bg_{{ $i }}" accept="image/*">
                                            @if(isset($settings['cms_home_hero_bg_'.$i]))
                                                <img src="{{ $settings['cms_home_hero_bg_'.$i] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Badge Text</label>
                                            <input type="text" class="form-control" name="cms_home_hero_badge_{{ $i }}" value="{{ $settings['cms_home_hero_badge_'.$i] ?? ($i == 1 ? (\App\Models\Setting::get('site_admission_text', '২০২৬-২৭ শিক্ষাবর্ষে ভর্তি চলছে')) : ($i == 2 ? 'আধুনিক শিক্ষা ব্যবস্থা' : 'সেরা ফলাফল')) }}">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Title</label>
                                            <!-- Migrate old title for slide 1 -->
                                            <input type="text" class="form-control" name="cms_home_hero_title_{{ $i }}" value="{{ $settings['cms_home_hero_title_'.$i] ?? ($i == 1 ? ($settings['cms_home_hero_title'] ?? 'শিক্ষাই জাতির মেরুদণ্ড') : ($i == 2 ? 'আপনার সন্তানের উজ্জ্বল ভবিষ্যৎ' : 'আধুনিক সুযোগ সুবিধা')) }}">
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Description</label>
                                            <!-- Migrate old desc for slide 1 -->
                                            <textarea class="form-control" name="cms_home_hero_desc_{{ $i }}" rows="2">{{ $settings['cms_home_hero_desc_'.$i] ?? ($i == 1 ? ($settings['cms_home_hero_desc'] ?? 'জ্ঞান, নৈতিকতা ও আধুনিক শিক্ষার সমন্বয়ে আমরা গড়ে তুলছি আগামী দিনের যোগ্য নাগরিক।') : ($i == 2 ? 'অভিজ্ঞ শিক্ষক, আধুনিক ল্যাব ও সুন্দর শিক্ষার পরিবেশে গড়ে উঠুক আপনার সন্তান।' : 'খেলার মাঠ, লাইব্রেরি, ও সাংস্কৃতিক আবহে একটি পরিপূর্ণ শিক্ষা প্রতিষ্ঠান।')) }}</textarea>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Primary Button Text</label>
                                            <input type="text" class="form-control" name="cms_home_hero_btn1_text_{{ $i }}" value="{{ $settings['cms_home_hero_btn1_text_'.$i] ?? ($i == 1 ? 'অনলাইনে ভর্তি করুন' : ($i == 2 ? 'আমাদের কার্যক্রম' : 'গ্যালারি দেখুন')) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Primary Button Link</label>
                                            <input type="text" class="form-control" name="cms_home_hero_btn1_link_{{ $i }}" value="{{ $settings['cms_home_hero_btn1_link_'.$i] ?? ($i == 1 ? route('admission') : ($i == 2 ? '#programs' : route('gallery'))) }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Secondary Button Text (Optional)</label>
                                            <input type="text" class="form-control" name="cms_home_hero_btn2_text_{{ $i }}" value="{{ $settings['cms_home_hero_btn2_text_'.$i] ?? ($i == 1 ? 'প্রতিষ্ঠান সম্পর্কে' : '') }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Secondary Button Link</label>
                                            <input type="text" class="form-control" name="cms_home_hero_btn2_link_{{ $i }}" value="{{ $settings['cms_home_hero_btn2_link_'.$i] ?? ($i == 1 ? '#about' : '') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <hr class="text-muted">





                    <!-- STATISTICS SECTION -->
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-chart-simple me-2"></i>Statistics Section</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Background Image</label>
                            <input type="file" class="form-control" name="cms_home_stat_bg" accept="image/*">
                            @if(isset($settings['cms_home_stat_bg']))
                                <img src="{{ $settings['cms_home_stat_bg'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Years of Excellence Value</label>
                            <input type="text" class="form-control" name="cms_home_stat_years" value="{{ $settings['cms_home_stat_years'] ?? '25+' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Years of Excellence Label</label>
                            <input type="text" class="form-control" name="cms_home_stat_years_label" value="{{ $settings['cms_home_stat_years_label'] ?? 'বছরের ঐতিহ্য' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Students Count Value</label>
                            <input type="text" class="form-control" name="cms_home_stat_students" value="{{ $settings['cms_home_stat_students'] ?? '10000+' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Students Count Label</label>
                            <input type="text" class="form-control" name="cms_home_stat_students_label" value="{{ $settings['cms_home_stat_students_label'] ?? 'শিক্ষার্থী' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Faculty Members Value</label>
                            <input type="text" class="form-control" name="cms_home_stat_faculty" value="{{ $settings['cms_home_stat_faculty'] ?? '500+' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Faculty Members Label</label>
                            <input type="text" class="form-control" name="cms_home_stat_faculty_label" value="{{ $settings['cms_home_stat_faculty_label'] ?? 'শিক্ষক ও কর্মচারী' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Success Value</label>
                            <input type="text" class="form-control" name="cms_home_stat_success" value="{{ $settings['cms_home_stat_success'] ?? '95%' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Academic Success Label</label>
                            <input type="text" class="form-control" name="cms_home_stat_success_label" value="{{ $settings['cms_home_stat_success_label'] ?? 'গড় পাসের হার' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Clubs & Activities</label>
                            <input type="number" class="form-control" name="cms_home_stat_clubs" value="{{ $settings['cms_home_stat_clubs'] ?? '50' }}">
                        </div>
                    </div>

                    <hr class="text-muted">

                    <!-- FACILITIES SECTION -->
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-building me-2"></i>Facilities Section</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Section Subtitle</label>
                            <input type="text" class="form-control" name="cms_home_facility_subtitle" value="{{ $settings['cms_home_facility_subtitle'] ?? 'আমাদের সুবিধা' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Section Title</label>
                            <input type="text" class="form-control" name="cms_home_facility_title" value="{{ $settings['cms_home_facility_title'] ?? 'আধুনিক ক্যাম্পাস সুবিধা' }}">
                        </div>
                        <div class="col-12" id="facilities-container">
                            <div class="row g-3" id="facilities-list">
                                @php
                                    $rawFacilities = $settings['cms_home_facilities'] ?? null;
                                    $facilities = [];
                                    
                                    if ($rawFacilities === null) {
                                        // Try to migrate from old format
                                        for($i=1; $i<=8; $i++) {
                                            $t = $settings['cms_home_facility_'.$i.'_title'] ?? '';
                                            if($t !== '') {
                                                $facilities[] = [
                                                    'icon' => $settings['cms_home_facility_'.$i.'_icon'] ?? '',
                                                    'title' => $t,
                                                    'desc' => $settings['cms_home_facility_'.$i.'_desc'] ?? ''
                                                ];
                                            }
                                        }

                                        // Fallback to defaults if entirely empty and no old settings exist
                                        if(empty($facilities)) {
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

                                @foreach($facilities as $index => $facility)
                                <div class="col-md-4 facility-item">
                                    <div class="p-3 border rounded h-100 bg-light position-relative">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-facility" title="Remove"><i class="bi bi-trash"></i></button>
                                        <label class="form-label fw-bold">Icon (Bootstrap Class)</label>
                                        <input type="text" class="form-control form-control-sm mb-2" name="cms_home_facilities[{{$index}}][icon]" value="{{ $facility['icon'] ?? '' }}">
                                        
                                        <label class="form-label fw-bold">Title</label>
                                        <input type="text" class="form-control form-control-sm mb-2" name="cms_home_facilities[{{$index}}][title]" value="{{ $facility['title'] ?? '' }}">
                                        
                                        <label class="form-label fw-bold">Description</label>
                                        <textarea class="form-control form-control-sm" name="cms_home_facilities[{{$index}}][desc]" rows="2">{{ $facility['desc'] ?? '' }}</textarea>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-12 mt-3">
                            <button type="button" id="add-facility-btn" class="btn btn-outline-primary btn-sm"><i class="bi bi-plus-circle"></i> Add More Facility</button>
                        </div>
                    </div>
                    <hr class="text-muted">

                    <!-- ADMISSION CTA SECTION -->
                    <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-bullhorn me-2"></i>Admission CTA Section</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="cms_home_admission_title" value="{{ $settings['cms_home_admission_title'] ?? 'আপনার সন্তানের সুন্দর ভবিষ্যতের যাত্রা শুরু হোক আজ থেকেই' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="cms_home_admission_desc" rows="2">{{ $settings['cms_home_admission_desc'] ?? 'অনলাইনে ভর্তি আবেদন করুন এবং আপনার সন্তানের জন্য একটি মানসম্মত শিক্ষার পরিবেশ নিশ্চিত করুন।' }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Button Text</label>
                            <input type="text" class="form-control" name="cms_home_admission_btn_text" value="{{ $settings['cms_home_admission_btn_text'] ?? 'অনলাইন ভর্তি' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Button Link</label>
                            <input type="text" class="form-control" name="cms_home_admission_btn_link" value="{{ $settings['cms_home_admission_btn_link'] ?? route('admission') }}">
                        </div>
                    </div>


                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-save me-2"></i>Save Homepage Settings</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let facilityIndex = {{ count($facilities ?? []) }};
    const container = document.getElementById('facilities-list');
    const addBtn = document.getElementById('add-facility-btn');

    if (addBtn) {
        addBtn.addEventListener('click', function() {
            const html = `
            <div class="col-md-4 facility-item">
                <div class="p-3 border rounded h-100 bg-light position-relative">
                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2 remove-facility" title="Remove"><i class="bi bi-trash"></i></button>
                    <label class="form-label fw-bold">Icon (Bootstrap Class)</label>
                    <input type="text" class="form-control form-control-sm mb-2" name="cms_home_facilities[${facilityIndex}][icon]" value="bi-check-circle">
                    
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" class="form-control form-control-sm mb-2" name="cms_home_facilities[${facilityIndex}][title]" value="New Facility">
                    
                    <label class="form-label fw-bold">Description</label>
                    <textarea class="form-control form-control-sm" name="cms_home_facilities[${facilityIndex}][desc]" rows="2"></textarea>
                </div>
            </div>`;
            container.insertAdjacentHTML('beforeend', html);
            facilityIndex++;
        });
    }

    if (container) {
        container.addEventListener('click', function(e) {
            if (e.target.closest('.remove-facility')) {
                e.target.closest('.facility-item').remove();
            }
        });
    }
});
</script>
@endpush
