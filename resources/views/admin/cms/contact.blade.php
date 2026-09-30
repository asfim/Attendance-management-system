@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-address-book me-2"></i> Contact Page Settings</h5>
                    <small class="text-muted">Manage contact info, hero text, Google Map, and view submitted form inquiries.</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.cms.contact.messages') }}" class="btn btn-sm btn-info text-white position-relative">
                        <i class="fa-solid fa-envelope me-1"></i> Contact Messages
                        @if(!empty($unreadCount) && $unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $unreadCount }}
                                <span class="visually-hidden">unread messages</span>
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
                </div>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form action="{{ route('admin.cms.contact.update') }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <!-- Hero Section Settings -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-heading me-1"></i> Hero Section</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Hero Title</label>
                            <input type="text" name="contact_hero_title" class="form-control" value="{{ $settings['contact_hero_title'] ?? 'Contact Us' }}" placeholder="Contact Us">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Hero Subtitle</label>
                            <input type="text" name="contact_hero_subtitle" class="form-control" value="{{ $settings['contact_hero_subtitle'] ?? 'Have a question about admissions, academic programs, or campus facilities? We are here to help and would love to hear from you.' }}" placeholder="Subtitle text...">
                        </div>

                        <!-- Contact Details -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-circle-info me-1"></i> Contact Details</h6></div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Phone Number</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '' }}" placeholder="+880 1234 567 890">
                            <small class="text-muted">Shown on contact page and header/footer</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Email Address</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? '' }}" placeholder="info@school.com">
                            <small class="text-muted">Shown on contact page and header/footer</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-secondary fw-semibold">Full Address</label>
                            <textarea name="contact_address" class="form-control" rows="2" placeholder="123 Education Boulevard, Dhaka 1212">{{ $settings['contact_address'] ?? '' }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Office Hours</label>
                            <input type="text" name="contact_office_hours" class="form-control" value="{{ $settings['contact_office_hours'] ?? 'Sunday - Thursday: 8:00 AM - 4:00 PM' }}" placeholder="Sunday - Thursday: 8:00 AM - 4:00 PM">
                        </div>

                        <!-- Dynamic Google Map Settings -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-map-location-dot me-1"></i> Google Maps Configuration</h6></div>
                        <div class="col-12">
                            <label class="form-label text-secondary fw-semibold">Google Maps Embed Link / iFrame URL <span class="text-danger">*</span></label>
                            <textarea name="contact_map_iframe" class="form-control font-monospace" rows="3" placeholder="Paste Google Maps Embed URL (e.g. https://www.google.com/maps/embed?pb=...) or entire <iframe> HTML snippet">{{ $settings['contact_map_iframe'] ?? '' }}</textarea>
                            <small class="text-muted">Tip: Go to Google Maps -> Search location -> Click Share -> Embed a map -> Copy the HTML or the <code>src="..."</code> URL and paste it here.</small>
                        </div>

                        <!-- Social Media Links -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-share-nodes me-1"></i> Social Media Links</h6></div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Facebook URL</label>
                            <input type="url" name="social_facebook" class="form-control" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Instagram URL</label>
                            <input type="url" name="social_instagram" class="form-control" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Twitter / X URL</label>
                            <input type="url" name="social_twitter" class="form-control" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://twitter.com/...">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Contact Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
