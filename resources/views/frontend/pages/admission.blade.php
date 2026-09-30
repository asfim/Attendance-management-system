@extends('frontend.layouts.app')

@push('styles')
<style>
    .admission-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        padding: 120px 0 60px;
        position: relative;
        overflow: hidden;
    }
    .admission-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCI+PGNpcmNsZSBjeD0iMiIgY3k9IjIiIHI9IjEiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsMC4xKSIvPjwvc3ZnPg==') repeat;
        opacity: 0.5;
    }
    .admission-card {
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 15px 45px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
        position: relative;
        z-index: 2;
        margin-top: -40px;
    }
    .form-section-title {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.3rem;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px dashed rgba(13, 71, 161, 0.2);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .form-section-title i {
        background: rgba(13, 71, 161, 0.1);
        padding: 8px;
        border-radius: 10px;
        color: var(--primary);
    }
    .form-control-premium, .form-select-premium {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 14px 18px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8fafc;
    }
    .form-control-premium:focus, .form-select-premium:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(13, 71, 161, 0.1);
        background: #fff;
    }
    .form-label-premium {
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
        font-size: 0.9rem;
    }
    .btn-submit-premium {
        background: linear-gradient(135deg, var(--accent) 0%, #f57f17 100%);
        color: #fff;
        border: none;
        padding: 16px 40px;
        border-radius: 30px;
        font-weight: 700;
        font-size: 1.1rem;
        letter-spacing: 1px;
        box-shadow: 0 10px 20px rgba(249, 168, 37, 0.3);
        transition: all 0.3s ease;
        text-transform: uppercase;
    }
    .btn-submit-premium:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 25px rgba(249, 168, 37, 0.4);
        color: #fff;
    }
</style>
@endpush

@section('content')
<!-- Page Header -->
<div class="admission-header text-center text-white">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">Session 2026-2027</span>
        <h1 class="display-4 fw-bold mb-3">অনলাইন ভর্তি ফর্ম</h1>
        <p class="lead text-white-50 max-w-700 mx-auto" style="max-width: 700px;">
            আগামী শিক্ষাবর্ষের জন্য অনলাইনে ভর্তির আবেদন শুরু হয়েছে। অনুগ্রহ করে সঠিক তথ্য দিয়ে নিচের ফর্মটি পূরণ করুন।
        </p>
    </div>
</div>

<section class="section bg-light pb-5 pt-0">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="admission-card p-4 p-md-5">
                    
                    @if(session('success'))
                        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4 d-flex align-items-center">
                            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
                            <div>{{ session('success') }}</div>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                <strong class="mb-0">অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</strong>
                            </div>
                            <ul class="mb-0 ms-3">
                                @foreach($errors->all() as $err)
                                    <li>{{ $err }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admission.submit') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Academic Information -->
                        <div class="mb-5">
                            <h2 class="form-section-title"><i class="bi bi-mortarboard-fill"></i> একাডেমিক তথ্য</h2>
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <label class="form-label-premium">শিক্ষাবর্ষ (Session)</label>
                                    <select name="session_id" class="form-select form-select-premium" required>
                                        <option value="">নির্বাচন করুন</option>
                                        @foreach($sessions as $sess)
                                            <option value="{{ $sess->id }}" {{ (old('session_id') == $sess->id || (!old('session_id') && $sess->is_active)) ? 'selected' : '' }}>{{ $sess->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">ভর্তির শ্রেণি (Class)</label>
                                    <select name="class_id" class="form-select form-select-premium" required>
                                        <option value="">নির্বাচন করুন</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">শাখা (Section)</label>
                                    <select name="section_id" class="form-select form-select-premium" required>
                                        <option value="">নির্বাচন করুন</option>
                                        @foreach($classes->first()->sections ?? [] as $sec)
                                            <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">শিফট (Shift) - যদি থাকে</label>
                                    <select name="shift_id" class="form-select form-select-premium">
                                        <option value="">নির্বাচন করুন (ঐচ্ছিক)</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}" {{ old('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">আবেদনের তারিখ</label>
                                    <input type="date" name="admission_date" class="form-control form-control-premium" value="{{ old('admission_date', now()->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Student Information -->
                        <div class="mb-5">
                            <h2 class="form-section-title"><i class="bi bi-person-badge-fill"></i> শিক্ষার্থীর তথ্য</h2>
                            <div class="row g-4">
                                <div class="col-md-8">
                                    <label class="form-label-premium">শিক্ষার্থীর পূর্ণ নাম</label>
                                    <input type="text" name="name" class="form-control form-control-premium" value="{{ old('name') }}" placeholder="Ex: Md. Abdur Rahman" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">ই-মেইল (ঐচ্ছিক)</label>
                                    <input type="email" name="email" class="form-control form-control-premium" value="{{ old('email') }}" placeholder="student@example.com">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">জন্ম তারিখ</label>
                                    <input type="date" name="dob" class="form-control form-control-premium" value="{{ old('dob') }}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">লিঙ্গ</label>
                                    <select name="gender" class="form-select form-select-premium" required>
                                        <option value="Male">ছেলে (Male)</option>
                                        <option value="Female">মেয়ে (Female)</option>
                                        <option value="Other">অন্যান্য (Other)</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label-premium">রক্তের গ্রুপ</label>
                                    <select name="blood_group" class="form-select form-select-premium">
                                        <option value="">নির্বাচন করুন</option>
                                        <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label-premium">মেডিকেল হিস্ট্রি / এলার্জি (যদি থাকে)</label>
                                    <input type="text" name="medical_info" class="form-control form-control-premium" placeholder="কোনো শারীরিক অসুস্থতা থাকলে উল্লেখ করুন..." value="{{ old('medical_info') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">শিক্ষার্থীর ছবি</label>
                                    <input type="file" name="photo" class="form-control form-control-premium bg-white" accept="image/*">
                                    <div class="form-text mt-2"><i class="bi bi-info-circle"></i> পাসপোর্ট সাইজের পরিষ্কার ছবি আপলোড করুন</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">স্বাক্ষর (ঐচ্ছিক)</label>
                                    <input type="file" name="signature" class="form-control form-control-premium bg-white" accept="image/*">
                                </div>
                            </div>
                        </div>

                        <!-- Guardian Information -->
                        <div class="mb-5">
                            <h2 class="form-section-title"><i class="bi bi-people-fill"></i> অভিভাবকের তথ্য</h2>
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label-premium">অভিভাবকের পূর্ণ নাম</label>
                                    <input type="text" name="parent_name" class="form-control form-control-premium" value="{{ old('parent_name') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">পেশা</label>
                                    <input type="text" name="parent_occupation" class="form-control form-control-premium" value="{{ old('parent_occupation') }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">ফোন নম্বর</label>
                                    <input type="text" name="parent_phone" class="form-control form-control-premium" value="{{ old('parent_phone') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label-premium">ই-মেইল</label>
                                    <input type="email" name="parent_email" class="form-control form-control-premium" value="{{ old('parent_email') }}" required>
                                </div>

                                <div class="col-12">
                                    <label class="form-label-premium">বর্তমান ঠিকানা</label>
                                    <textarea name="parent_address" rows="3" class="form-control form-control-premium" required>{{ old('parent_address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="text-center pt-4">
                            <button type="submit" class="btn btn-submit-premium">
                                <i class="bi bi-send-fill me-2"></i> আবেদন জমা দিন
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
