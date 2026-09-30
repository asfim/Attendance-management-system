@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm" style="border-radius: 16px;">
            <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                <h5 class="mb-0 fw-bold text-primary"><i class="fa-solid fa-screwdriver-wrench me-2"></i>{{ $page }} Page Settings</h5>
            </div>
            <div class="card-body p-5 text-center">
                <i class="fa-solid fa-person-digging text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                <h4 class="mt-4 text-secondary">Under Construction</h4>
                <p class="text-muted">The dynamic settings for the {{ $page }} page are not implemented yet.<br>This placeholder will be updated with actual form fields soon.</p>
                <a href="{{ route('admin.cms.home') }}" class="btn btn-primary mt-3"><i class="fa-solid fa-arrow-left me-2"></i>Back to Home Settings</a>
            </div>
        </div>
    </div>
</div>
@endsection
