@extends('layouts.app')

@section('title', 'Food Settings')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-sliders text-primary me-2"></i>Food Management Settings
            </h5>
            <p class="text-muted fs-8 mb-0">Configure non-consumption deduction rules, minimum days threshold, and billing parameters</p>
        </div>
        <div>
            <a href="{{ route('admin.food.settings.manual') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-download me-1"></i> Download User Manual (PDF)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 fs-7 mb-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-4">
            <form action="{{ route('admin.food.settings.update') }}" method="POST">
                @csrf
                
                {{-- Adjustment Enable Toggle --}}
                <div class="card p-3 mb-4 border" style="border-radius: 10px;">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="food_fee_adjustment_enabled" id="adjToggle" {{ $settings['food_fee_adjustment_enabled'] ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold text-dark-emphasis" for="adjToggle">
                            Enable Food Fee Adjustment Based on Non-Consumption
                        </label>
                    </div>
                    <div class="text-muted fs-8 mt-1 ms-4">
                        When enabled, the system automatically calculates deductions if a student misses food for equal to or more than the minimum required days.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label text-secondary fs-8 fw-semibold">Minimum Non-Consumption Days *</label>
                        <input type="number" name="min_non_consumption_days" class="form-control form-control-sm" value="{{ $settings['min_non_consumption_days'] }}" required min="1" max="31">
                        <div class="text-muted fs-8 mt-1">Example: If set to 10, a student must miss food for at least 10 days before any deduction is applied.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary fs-8 fw-semibold">Billing Days Per Month *</label>
                        <input type="number" name="billing_days" class="form-control form-control-sm" value="{{ $settings['billing_days'] }}" required min="1" max="31">
                        <div class="text-muted fs-8 mt-1">Used to calculate per-day food cost (Monthly Fee ÷ Billing Days). Default is 30.</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary fs-8 fw-semibold">Calculation Method *</label>
                        <select name="calculation_method" class="form-select form-select-sm">
                            <option value="food_day" {{ $settings['calculation_method'] === 'food_day' ? 'selected' : '' }}>Based on Food Day (Daily Absences)</option>
                            <option value="individual_meal" {{ $settings['calculation_method'] === 'individual_meal' ? 'selected' : '' }}>Based on Individual Meal Slots</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label text-secondary fs-8 fw-semibold">Automations & Controls</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="allow_manual_adjustment" id="manualToggle" {{ $settings['allow_manual_adjustment'] ? 'checked' : '' }}>
                            <label class="form-check-label fs-8 text-dark-emphasis" for="manualToggle">
                                Allow Manual Admin Fee Adjustments / Waivers
                            </label>
                        </div>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" name="auto_generate_monthly_fee" id="autoFeeToggle" {{ $settings['auto_generate_monthly_fee'] ? 'checked' : '' }}>
                            <label class="form-check-label fs-8 text-dark-emphasis" for="autoFeeToggle">
                                Auto Generate Monthly Food Fee Invoices
                            </label>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 text-end">
                    <button type="submit" class="btn btn-sm btn-primary px-4">
                        <i class="fa-solid fa-save me-1"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
