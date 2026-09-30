@extends('layouts.app')

@section('content')
<style>
    .message-info-box {
        background-color: #f8f9fa;
        border: 1px solid #e2e8f0;
    }
    [data-bs-theme="dark"] .message-info-box {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    [data-bs-theme="dark"] .message-info-box small {
        color: #94a3b8 !important;
    }
    [data-bs-theme="dark"] .message-info-box span,
    [data-bs-theme="dark"] .message-info-box a {
        color: #f8fafc !important;
    }
    .message-body-box {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        color: #1e293b;
    }
    [data-bs-theme="dark"] .message-body-box {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #e2e8f0 !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card border-0 shadow-sm" style="border-radius:16px;">
            <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <a href="{{ route('admin.cms.contact.messages') }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Messages</a>
                    <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-envelope-open-text me-2"></i> Message Details</h5>
                </div>
                <form action="{{ route('admin.cms.contact.messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash me-1"></i> Delete Message</button>
                </form>
            </div>

            <div class="card-body p-4 p-md-5">
                <!-- Subject & Metadata -->
                <div class="p-4 rounded-3 message-info-box mb-4 shadow-sm">
                    <h4 class="fw-bold mb-3">{{ $message->subject }}</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-semibold mb-1">Sender Name</small>
                            <span class="fw-semibold fs-6"><i class="fa-solid fa-user me-1 text-primary"></i> {{ $message->full_name }}</span>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-semibold mb-1">Email Address</small>
                            <a href="mailto:{{ $message->email }}" class="fw-semibold fs-6 text-decoration-none"><i class="fa-solid fa-envelope me-1 text-primary"></i> {{ $message->email }}</a>
                        </div>
                        @if($message->phone)
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-semibold mb-1">Phone Number</small>
                            <a href="tel:{{ $message->phone }}" class="fw-semibold fs-6 text-decoration-none"><i class="fa-solid fa-phone me-1 text-primary"></i> {{ $message->phone }}</a>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <small class="text-muted d-block fw-semibold mb-1">Received Date & Time</small>
                            <span class="fw-semibold fs-6 text-secondary"><i class="fa-solid fa-clock me-1 text-primary"></i> {{ $message->created_at->format('F d, Y \a\t h:i A') }} ({{ $message->created_at->diffForHumans() }})</span>
                        </div>
                    </div>
                </div>

                <!-- Message Body -->
                <div class="mb-4">
                    <h6 class="text-secondary fw-semibold mb-2"><i class="fa-solid fa-comment-dots me-1"></i> Message Body:</h6>
                    <div class="p-4 rounded-3 message-body-box shadow-sm" style="white-space: pre-wrap; font-size: 1rem; line-height: 1.7; min-height: 150px;">{{ $message->message }}</div>
                </div>

                <!-- Actions / Reply via Email -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="mailto:{{ $message->email }}?subject=Re: {{ urlencode($message->subject) }}" class="btn btn-primary px-4">
                        <i class="fa-solid fa-reply me-2"></i> Reply via Email
                    </a>
                    <a href="{{ route('admin.cms.contact.messages') }}" class="btn btn-outline-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
