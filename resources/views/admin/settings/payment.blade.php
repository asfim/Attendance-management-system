@extends('layouts.app')

@section('content')
<div class="row justify-content-center g-4">
    <div class="col-lg-10">
        <h4 class="fw-bold mb-4">Payment Settings</h4>
        
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-2">
                <i class="fa-solid fa-credit-card text-primary me-2"></i>SSLCommerz Payment Gateway Integration
            </h5>
            <p class="text-muted small mb-4">
                Configure your SSLCommerz merchant API credentials below. Setting these values dynamically enables secure checkout processing on the frontend.
            </p>

            <form action="{{ route('admin.settings.payment.update') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Environment (Sandbox Mode)</label>
                    <select name="sslcommerz_mode" class="form-select">
                        <option value="sandbox" {{ ($settings['sslcommerz_mode'] ?? 'sandbox') == 'sandbox' ? 'selected' : '' }}>Sandbox (Test Mode)</option>
                        <option value="live" {{ ($settings['sslcommerz_mode'] ?? '') == 'live' ? 'selected' : '' }}>Live (Production Mode)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">SSLCommerz Store ID</label>
                    <input type="text" name="sslcommerz_store_id" class="form-control" 
                           value="{{ $settings['sslcommerz_store_id'] ?? '' }}" 
                           placeholder="Enter your SSLCommerz Store ID">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">SSLCommerz Store Password</label>
                    <input type="text" name="sslcommerz_store_password" class="form-control" 
                           value="{{ $settings['sslcommerz_store_password'] ?? '' }}" 
                           placeholder="Enter your SSLCommerz Store Password">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Default Currency</label>
                    <input type="text" name="sslcommerz_currency" class="form-control" 
                           value="{{ $settings['sslcommerz_currency'] ?? 'BDT' }}">
                    <small class="text-muted mt-1 d-block">Usually <span class="text-danger">BDT</span>.</small>
                </div>

                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                    <i class="fa-solid fa-save me-2"></i>Save Configuration
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
