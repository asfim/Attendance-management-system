@extends('layouts.app')

@section('title', 'Edit Fee Category')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Edit Fee Category</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.fees.setup') }}" class="text-decoration-none text-muted">Fees Setup</a></li>
                    <li class="breadcrumb-item active text-primary" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.fees.setup') }}" class="btn btn-outline-secondary rounded-pill shadow-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('admin.fees.categories.update', $category->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Fee Name -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Fee Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-lg border" value="{{ $category->name }}" placeholder="e.g. Monthly Tuition, Exam Fee" required>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <!-- Frequency -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Frequency <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <input type="radio" class="btn-check" name="type" id="edit_freq_monthly" value="monthly" {{ $category->type == 'monthly' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_monthly">Monthly</label>

                                        <input type="radio" class="btn-check" name="type" id="edit_freq_yearly" value="yearly" {{ $category->type == 'yearly' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_yearly">Yearly</label>

                                        <input type="radio" class="btn-check" name="type" id="edit_freq_halfyearly" value="half-yearly" {{ $category->type == 'half-yearly' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_halfyearly">Half-Yearly</label>

                                        <input type="radio" class="btn-check" name="type" id="edit_freq_weekly" value="weekly" {{ $category->type == 'weekly' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_weekly">Weekly</label>

                                        <input type="radio" class="btn-check" name="type" id="edit_freq_onetime" value="one-time" {{ $category->type == 'one-time' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_onetime">One-time</label>

                                        <input type="radio" class="btn-check" name="type" id="edit_freq_custom" value="custom" {{ $category->type == 'custom' ? 'checked' : '' }}>
                                        <label class="btn btn-sm fw-medium freq-btn" for="edit_freq_custom">Custom</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="{{ route('admin.fees.setup') }}" class="btn btn-light rounded-pill px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fa-solid fa-save me-1"></i> Update Fee</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Enforce global border color var(--bs-border-color) requested by user */
    .border, .border-bottom, .border-top, .border-start, .border-end, 
    .form-control, .form-select, .input-group-text, .card {
        border-color: var(--bs-border-color) !important;
    }

    .freq-btn {
        color: #5a6a85;
        border: 1px solid transparent;
        border-radius: 6px;
        background-color: #ffffff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 8px 16px;
        cursor: pointer;
    }
    .freq-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(var(--bs-primary-rgb), 0.15);
        color: var(--bs-primary);
        border-color: rgba(var(--bs-primary-rgb), 0.3) !important;
    }
    .btn-check:checked + .freq-btn {
        color: var(--bs-primary);
        border-color: var(--bs-primary) !important;
        background-color: rgba(var(--bs-primary-rgb), 0.1);
        box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.2);
        transform: scale(1.02);
    }
</style>
@endpush
