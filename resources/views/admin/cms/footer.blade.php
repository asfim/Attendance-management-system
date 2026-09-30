@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-square-bottom me-2"></i> Footer Settings</h5>
                    <small class="text-muted">Customize footer branding, about text, contact details, social media links, and copyright text.</small>
                </div>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
            </div>
            <div class="card-body p-4 p-md-5">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show"><i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                <form action="{{ route('admin.cms.footer.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-4">
                        <!-- Branding & About Section -->
                        <div class="col-12"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-flag me-1"></i> Footer Branding &amp; About</h6></div>
                        
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">School Name / Title</label>
                            <input type="text" name="school_name" class="form-control" value="{{ $settings['school_name'] ?? 'Meridian International School & College' }}" placeholder="Meridian International School & College">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">Website Logo</label>
                            <input type="file" name="site_logo" class="form-control" accept="image/*">
                            @if(!empty($settings['site_logo']))
                                <div class="mt-2 p-2 bg-dark rounded d-inline-block">
                                    <img src="{{ $settings['site_logo'] }}" height="40" alt="Current Logo">
                                </div>
                            @endif
                        </div>

                        <div class="col-12">
                            <label class="form-label text-secondary fw-semibold">Footer About / Description Text</label>
                            <textarea name="footer_about_text" class="form-control" rows="3" placeholder="Shaping future leaders through excellence in academics, character and innovation since 1999.">{{ $settings['footer_about_text'] ?? 'Shaping future leaders through excellence in academics, character and innovation since 1999.' }}</textarea>
                            <small class="text-muted">Short paragraph displayed under the footer logo.</small>
                        </div>

                        <!-- Contact Details in Footer -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-circle-info me-1"></i> Footer Contact Information</h6></div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Phone Number</label>
                            <input type="text" name="contact_phone" class="form-control" value="{{ $settings['contact_phone'] ?? '+880 1711-000-222' }}" placeholder="+880 1711-000-222">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Email Address</label>
                            <input type="email" name="contact_email" class="form-control" value="{{ $settings['contact_email'] ?? 'info@schoolmanagement.com' }}" placeholder="info@schoolmanagement.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Short Campus Address</label>
                            <input type="text" name="contact_address" class="form-control" value="{{ $settings['contact_address'] ?? '123 Education Boulevard, Dhaka' }}" placeholder="123 Education Boulevard, Dhaka">
                        </div>

                        <!-- Social Media Links -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-share-nodes me-1"></i> Footer Social Media Links</h6></div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Facebook URL</label>
                            <input type="url" name="social_facebook" class="form-control" value="{{ $settings['social_facebook'] ?? '' }}" placeholder="https://facebook.com/your-school">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Instagram URL</label>
                            <input type="url" name="social_instagram" class="form-control" value="{{ $settings['social_instagram'] ?? '' }}" placeholder="https://instagram.com/your-school">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-secondary fw-semibold">Twitter / X URL</label>
                            <input type="url" name="social_twitter" class="form-control" value="{{ $settings['social_twitter'] ?? '' }}" placeholder="https://twitter.com/your-school">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">YouTube Channel URL</label>
                            <input type="url" name="social_youtube" class="form-control" value="{{ $settings['social_youtube'] ?? '' }}" placeholder="https://youtube.com/@your-school">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fw-semibold">LinkedIn URL</label>
                            <input type="url" name="social_linkedin" class="form-control" value="{{ $settings['social_linkedin'] ?? '' }}" placeholder="https://linkedin.com/company/your-school">
                        </div>

                        <!-- Copyright Notice -->
                        <div class="col-12 mt-4"><h6 class="text-secondary fw-semibold mb-0 border-bottom pb-2"><i class="fa-solid fa-copyright me-1"></i> Copyright &amp; Footer Bottom Bar</h6></div>
                        <div class="col-12">
                            <label class="form-label text-secondary fw-semibold">Copyright Statement</label>
                            <input type="text" name="footer_copyright" class="form-control" value="{{ $settings['footer_copyright'] ?? '© ' . date('Y') . ' Meridian International School & College. All rights reserved.' }}" placeholder="© 2026 Meridian International School & College. All rights reserved.">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary px-5"><i class="fa-solid fa-save me-2"></i>Save Footer Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
