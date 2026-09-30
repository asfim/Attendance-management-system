@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-book-open-reader me-2"></i> Academics Page Settings</h5>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                <form action="{{ route('admin.cms.academics.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        
                        <!-- Hero Section -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Hero Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Badge Text</label>
                            <input type="text" name="academics_hero_badge" class="form-control" value="{{ $settings['academics_hero_badge'] ?? 'শিক্ষায় উৎকর্ষ' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Background Image</label>
                            <input type="file" name="academics_hero_bg" class="form-control" accept="image/*">
                            @if(isset($settings['academics_hero_bg']))
                                <img src="{{ $settings['academics_hero_bg'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title Line 1</label>
                            <input type="text" name="academics_hero_title1" class="form-control" value="{{ $settings['academics_hero_title1'] ?? 'মননশীলতা বিকাশ,' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Hero Title Line 2 (Gold Text)</label>
                            <input type="text" name="academics_hero_title2" class="form-control" value="{{ $settings['academics_hero_title2'] ?? 'ভবিষ্যৎ নির্মাণ' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Hero Subtitle</label>
                            <textarea name="academics_hero_subtitle" class="form-control" rows="2">{{ $settings['academics_hero_subtitle'] ?? 'আমাদের বিস্তৃত ও যুগোপযোগী শিক্ষাক্রম শিক্ষার্থীদের মধ্যে কৌতূহল জাগাতে, চিন্তাশক্তি বাড়াতে এবং আজীবন শেখার ভিত্তি মজবুত করতে ডিজাইন করা হয়েছে।' }}</textarea>
                        </div>

                        <!-- Educational Stages (Programs) -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Educational Stages (Programs)</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Subtitle</label>
                            <input type="text" name="academics_programs_subtitle" class="form-control" value="{{ $settings['academics_programs_subtitle'] ?? 'আমাদের শিক্ষাক্রম' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Title</label>
                            <input type="text" name="academics_programs_title" class="form-control" value="{{ $settings['academics_programs_title'] ?? 'শিক্ষার স্তরসমূহ' }}">
                        </div>

                        @php
                            $programs = json_decode($settings['academics_programs'] ?? '[]', true);
                            if (empty($programs)) {
                                $programs = [
                                    [
                                        'icon' => 'bi-puzzle',
                                        'title' => $settings['academics_program_1_title'] ?? 'প্রাক-প্রাথমিক',
                                        'desc' => $settings['academics_program_1_desc'] ?? 'ছোট শিশুদের মানসিক, সামাজিক এবং আবেগিক বিকাশের ওপর ফোকাস করা একটি আনন্দদায়ক পদ্ধতি।',
                                        'bullet1' => $settings['academics_program_1_bullet1'] ?? 'বয়স ৩ - ৫ বছর',
                                        'bullet2' => $settings['academics_program_1_bullet2'] ?? 'খেলাধুলা ভিত্তিক পদ্ধতি',
                                        'bullet3' => $settings['academics_program_1_bullet3'] ?? 'সেন্সরি অ্যাক্টিভিটিস'
                                    ],
                                    [
                                        'icon' => 'bi-book',
                                        'title' => $settings['academics_program_2_title'] ?? 'প্রাথমিক',
                                        'desc' => $settings['academics_program_2_desc'] ?? 'সাক্ষরতা, গাণিতিক দক্ষতা এবং পরিবেশগত সচেতনতার শক্তিশালী ভিত্তি গঠন।',
                                        'bullet1' => $settings['academics_program_2_bullet1'] ?? 'প্রথম থেকে পঞ্চম শ্রেণি',
                                        'bullet2' => $settings['academics_program_2_bullet2'] ?? 'প্রজেক্ট-ভিত্তিক লার্নিং',
                                        'bullet3' => $settings['academics_program_2_bullet3'] ?? 'সহশিক্ষামূলক কার্যক্রম'
                                    ],
                                    [
                                        'icon' => 'bi-compass',
                                        'title' => $settings['academics_program_3_title'] ?? 'নিম্ন মাধ্যমিক',
                                        'desc' => $settings['academics_program_3_desc'] ?? 'শিক্ষার্থীদের স্বাধীন চিন্তাশক্তি এবং বিভিন্ন বিষয়ে গভীর ধারণার বিকাশ।',
                                        'bullet1' => $settings['academics_program_3_bullet1'] ?? 'ষষ্ঠ থেকে অষ্টম শ্রেণি',
                                        'bullet2' => $settings['academics_program_3_bullet2'] ?? 'স্টেম (STEM) শিক্ষা',
                                        'bullet3' => $settings['academics_program_3_bullet3'] ?? 'নেতৃত্বের প্রোগ্রাম'
                                    ],
                                    [
                                        'icon' => 'bi-mortarboard',
                                        'title' => $settings['academics_program_4_title'] ?? 'মাধ্যমিক ও উচ্চ মাধ্যমিক',
                                        'desc' => $settings['academics_program_4_desc'] ?? 'জাতীয় বোর্ড পরীক্ষা এবং বিশ্ববিদ্যালয়ে ভর্তির জন্য কঠোর একাডেমিক প্রস্তুতি।',
                                        'bullet1' => $settings['academics_program_4_bullet1'] ?? 'নবম থেকে দ্বাদশ শ্রেণি',
                                        'bullet2' => $settings['academics_program_4_bullet2'] ?? 'বিজ্ঞান, কলা, বাণিজ্য',
                                        'bullet3' => $settings['academics_program_4_bullet3'] ?? 'ক্যারিয়ার গাইডেন্স'
                                    ]
                                ];
                            }
                        @endphp
                        <div class="col-12">
                            <div id="programs-container" class="row">
                                @foreach($programs as $index => $program)
                                    <div class="col-md-3 program-item mb-3">
                                        <div class="card bg-light border-0 h-100">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0 text-secondary">Program <span class="program-number">{{ $index + 1 }}</span></h6>
                                                    <button type="button" class="btn btn-sm btn-danger remove-program"><i class="fa-solid fa-times"></i></button>
                                                </div>
                                                <label class="form-label text-secondary small">Icon Class (e.g. bi-book)</label>
                                                <input type="text" name="academics_programs[{{ $index }}][icon]" class="form-control form-control-sm mb-2" value="{{ $program['icon'] ?? 'bi-book' }}" required>
                                                <label class="form-label text-secondary small">Title</label>
                                                <input type="text" name="academics_programs[{{ $index }}][title]" class="form-control form-control-sm mb-2" value="{{ $program['title'] ?? '' }}" required>
                                                <label class="form-label text-secondary small">Description</label>
                                                <textarea name="academics_programs[{{ $index }}][desc]" class="form-control form-control-sm mb-2" rows="3" required>{{ $program['desc'] ?? '' }}</textarea>
                                                <label class="form-label text-secondary small">Bullet 1</label>
                                                <input type="text" name="academics_programs[{{ $index }}][bullet1]" class="form-control form-control-sm mb-1" value="{{ $program['bullet1'] ?? '' }}">
                                                <label class="form-label text-secondary small">Bullet 2</label>
                                                <input type="text" name="academics_programs[{{ $index }}][bullet2]" class="form-control form-control-sm mb-1" value="{{ $program['bullet2'] ?? '' }}">
                                                <label class="form-label text-secondary small">Bullet 3</label>
                                                <input type="text" name="academics_programs[{{ $index }}][bullet3]" class="form-control form-control-sm" value="{{ $program['bullet3'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" id="add-program" class="btn btn-outline-primary btn-sm mt-2"><i class="fa-solid fa-plus me-1"></i> Add Program</button>
                        </div>

                        <!-- Methodology Section -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Our Methodology Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Subtitle</label>
                            <input type="text" name="academics_methodology_subtitle" class="form-control" value="{{ $settings['academics_methodology_subtitle'] ?? 'আমাদের পদ্ধতি' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Title</label>
                            <input type="text" name="academics_methodology_title" class="form-control" value="{{ $settings['academics_methodology_title'] ?? 'শিক্ষায় একটি সামগ্রিক দৃষ্টিভঙ্গি' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Section Description</label>
                            <textarea name="academics_methodology_desc" class="form-control" rows="2">{{ $settings['academics_methodology_desc'] ?? 'আমাদের বিশ্বাস, প্রকৃত শিক্ষা শুধু পাঠ্যবইয়ে সীমাবদ্ধ নয়। আমরা আধুনিক শিক্ষণ পদ্ধতি এবং নৈতিক মূল্যবোধের সমন্বয়ে শিক্ষার্থীদের প্রস্তুত করি।' }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Section Image</label>
                            <input type="file" name="academics_methodology_image" class="form-control" accept="image/*">
                            @if(isset($settings['academics_methodology_image']))
                                <img src="{{ $settings['academics_methodology_image'] }}" class="img-thumbnail mt-2" style="max-height: 80px;">
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Badge Number</label>
                            <input type="text" name="academics_methodology_badge_number" class="form-control" value="{{ $settings['academics_methodology_badge_number'] ?? '২৫+' }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary">Badge Text</label>
                            <input type="text" name="academics_methodology_badge_text" class="form-control" value="{{ $settings['academics_methodology_badge_text'] ?? 'বছরের শিক্ষাগত উৎকর্ষ' }}">
                        </div>
                        
                        <div class="col-12 mt-2"><label class="form-label text-secondary fw-semibold">Methodology Points (3 Points)</label></div>
                        @for($i=1; $i<=3; $i++)
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body p-3">
                                    <label class="form-label text-secondary small">Point {{ $i }} Title</label>
                                    <input type="text" name="academics_methodology_{{ $i }}_title" class="form-control form-control-sm mb-2" value="{{ $settings['academics_methodology_'.$i.'_title'] ?? '' }}">
                                    <label class="form-label text-secondary small">Point {{ $i }} Description</label>
                                    <textarea name="academics_methodology_{{ $i }}_desc" class="form-control form-control-sm" rows="3">{{ $settings['academics_methodology_'.$i.'_desc'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endfor

                        <!-- Facilities Section -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Resources & Facilities Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Subtitle</label>
                            <input type="text" name="academics_facilities_subtitle" class="form-control" value="{{ $settings['academics_facilities_subtitle'] ?? 'সুযোগ-সুবিধাসমূহ' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Section Title</label>
                            <input type="text" name="academics_facilities_title" class="form-control" value="{{ $settings['academics_facilities_title'] ?? 'শিক্ষাগত উপকরণ' }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Section Description</label>
                            <textarea name="academics_facilities_desc" class="form-control" rows="2">{{ $settings['academics_facilities_desc'] ?? 'শিক্ষার্থীদের শেখার অভিজ্ঞতা আরও উন্নত করতে আমরা আধুনিক ও বিশ্বমানের সুযোগ-সুবিধা প্রদান করি।' }}</textarea>
                        </div>
                        
                        @for($i=1; $i<=3; $i++)
                        <div class="col-md-4">
                            <div class="card bg-light border-0 h-100">
                                <div class="card-body p-3">
                                    <label class="form-label text-secondary small">Facility {{ $i }} Title</label>
                                    <input type="text" name="academics_facility_{{ $i }}_title" class="form-control form-control-sm mb-2" value="{{ $settings['academics_facility_'.$i.'_title'] ?? '' }}">
                                    <label class="form-label text-secondary small">Facility {{ $i }} Description</label>
                                    <textarea name="academics_facility_{{ $i }}_desc" class="form-control form-control-sm" rows="3">{{ $settings['academics_facility_'.$i.'_desc'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        @endfor

                        <!-- CTA Section -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2">Call To Action (CTA)</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">CTA Title</label>
                            <input type="text" name="academics_cta_title" class="form-control" value="{{ $settings['academics_cta_title'] ?? 'আপনার শিক্ষাজীবন শুরু করুন' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">CTA Description</label>
                            <input type="text" name="academics_cta_desc" class="form-control" value="{{ $settings['academics_cta_desc'] ?? 'আগামী শিক্ষাবর্ষের ভর্তি কার্যক্রম চলছে। আজই যোগাযোগ করুন।' }}">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Academics Page Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let programIndex = {{ isset($programs) ? count($programs) : 4 }};
    document.getElementById('add-program').addEventListener('click', function() {
        let container = document.getElementById('programs-container');
        let html = `
            <div class="col-md-3 program-item mb-3">
                <div class="card bg-light border-0 h-100">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 text-secondary">Program <span class="program-number">${programIndex + 1}</span></h6>
                            <button type="button" class="btn btn-sm btn-danger remove-program"><i class="fa-solid fa-times"></i></button>
                        </div>
                        <label class="form-label text-secondary small">Icon Class (e.g. bi-book)</label>
                        <input type="text" name="academics_programs[${programIndex}][icon]" class="form-control form-control-sm mb-2" value="bi-book" required>
                        <label class="form-label text-secondary small">Title</label>
                        <input type="text" name="academics_programs[${programIndex}][title]" class="form-control form-control-sm mb-2" required>
                        <label class="form-label text-secondary small">Description</label>
                        <textarea name="academics_programs[${programIndex}][desc]" class="form-control form-control-sm mb-2" rows="3" required></textarea>
                        <label class="form-label text-secondary small">Bullet 1</label>
                        <input type="text" name="academics_programs[${programIndex}][bullet1]" class="form-control form-control-sm mb-1">
                        <label class="form-label text-secondary small">Bullet 2</label>
                        <input type="text" name="academics_programs[${programIndex}][bullet2]" class="form-control form-control-sm mb-1">
                        <label class="form-label text-secondary small">Bullet 3</label>
                        <input type="text" name="academics_programs[${programIndex}][bullet3]" class="form-control form-control-sm">
                    </div>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        programIndex++;
        updateProgramNumbers();
    });

    document.getElementById('programs-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-program')) {
            e.target.closest('.program-item').remove();
            updateProgramNumbers();
        }
    });

    function updateProgramNumbers() {
        let items = document.querySelectorAll('.program-item');
        items.forEach((item, index) => {
            item.querySelector('.program-number').textContent = index + 1;
        });
    }
</script>
@endpush
