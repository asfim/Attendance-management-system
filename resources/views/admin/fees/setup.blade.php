@extends('layouts.app', ['title' => 'Fee Setup - EduERP', 'header' => 'Fee Setup'])

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h4 class="mb-1 d-flex align-items-center gap-2">
            <i class="fa-regular fa-credit-card text-primary"></i> Fee Setup
        </h4>
        <p class="text-secondary mb-0">Manage fee categories, installments & class amounts for: <span class="text-primary fw-semibold">2026-2027 <i class="fa-solid fa-chevron-down fs-7"></i></span></p>
    </div>
    <div class="col-md-4 text-end">
        <button class="btn btn-dark rounded-pill px-4 fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#createFeeModal">
            <i class="fa-solid fa-plus me-1"></i> Create Fee
        </button>
    </div>
</div>

<!-- Tabs -->
<ul class="nav nav-tabs border-bottom mb-4" id="feeTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-semibold text-primary border-0 border-bottom border-primary border-2 bg-transparent" id="structures-tab" data-bs-toggle="tab" data-bs-target="#structures" type="button" role="tab">Fee Structures</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-semibold text-secondary border-0 bg-transparent hover-text-primary" id="discounts-tab" data-bs-toggle="tab" data-bs-target="#discounts" type="button" role="tab">Tag Discounts</button>
    </li>
</ul>

<div class="tab-content" id="feeTabsContent">
    <!-- Fee Structures Tab -->
    <div class="tab-pane fade show active" id="structures" role="tabpanel">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-3">
                <h6 class="mb-0 fw-semibold text-secondary">Active Fees ({{ $categories->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="accordion accordion-flush" id="feesAccordion">
                    @forelse($categories as $category)
                    <div class="accordion-item border-bottom">
                        <h2 class="accordion-header position-relative" id="heading-{{ $category->id }}">
                            <button class="accordion-button collapsed p-4 border-0 rounded-4 custom-accordion-btn" type="button" data-bs-toggle="collapse" data-bs-target="#fee-{{ $category->id }}" aria-expanded="false" style="padding-right: 120px !important;">
                                <div class="d-flex align-items-center w-100 me-3">
                                    <div class="me-3 text-secondary accordion-arrow-icon">
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold" style="color: inherit;">{{ $category->name }}</h6>
                                        <div class="d-flex align-items-center gap-3 text-muted" style="font-size: 0.85rem;">
                                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 fw-medium border border-primary border-opacity-10">
                                                {{ ucfirst($category->type) }}
                                            </span>
                                            <span><i class="fa-solid fa-users me-1"></i> {{ $category->feeStructures->count() }} classes</span>
                                            <span><i class="fa-solid fa-list-ol me-1"></i> {{ $category->installments_count }} installments</span>
                                        </div>
                                    </div>
                                </div>
                            </button>
                            <div class="position-absolute top-50 translate-middle-y d-flex gap-2" style="right: 20px; z-index: 4;">
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_fee'))
<a href="{{ route('admin.fees.categories.edit', $category->id) }}" class="btn btn-sm btn-light rounded-circle text-secondary shadow-sm" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
@endif
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_fee'))
<form action="{{ route('admin.fees.categories.destroy', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this fee category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light rounded-circle text-danger shadow-sm" title="Delete">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </button>
                                </form>
@endif
                            </div>
                        </h2>
                        <div id="fee-{{ $category->id }}" class="accordion-collapse collapse" data-bs-parent="#feesAccordion">
                            <div class="accordion-body bg-body-tertiary bg-opacity-50 p-4">
                                <!-- Inner Tabs -->
                                <ul class="nav nav-tabs border-bottom mb-4 custom-tabs" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link {{ session('active_tab') === 'installments-' . $category->id ? '' : 'active' }} fw-bold border-0 bg-transparent" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#class-amounts-{{ $category->id }}" type="button">CLASS AMOUNTS ({{ $classes->count() }})</button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link {{ session('active_tab') === 'installments-' . $category->id ? 'active' : '' }} fw-bold border-0 bg-transparent" style="font-size: 0.85rem;" data-bs-toggle="tab" data-bs-target="#installments-{{ $category->id }}" type="button">INSTALLMENTS ({{ $category->installments_count }})</button>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <div class="tab-pane fade {{ session('active_tab') === 'installments-' . $category->id ? '' : 'show active' }}" id="class-amounts-{{ $category->id }}">
                                        <form action="{{ route('admin.fees.categories.update_amounts', $category->id) }}" method="POST">
                                            @csrf
                                            <div class="d-flex justify-content-between align-items-center mb-4">
                                                <h6 class="fw-bold mb-0">Class Amounts</h6>
                                                <button type="button" class="btn btn-link btn-sm text-primary text-decoration-none p-0 fw-medium apply-all-btn" data-target="amounts-grid-{{ $category->id }}">Apply first amount to all</button>
                                            </div>
                                            
                                            <div class="row g-3 mb-4" id="amounts-grid-{{ $category->id }}">
                                                @foreach($classes as $c)
                                                @php 
                                                    $struct = $category->feeStructures->where('class_id', $c->id)->first();
                                                    $amount = $struct ? $struct->amount : '0.00';
                                                @endphp
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="input-group shadow-sm">
                                                        <span class="input-group-text bg-body fw-bold text-body border-secondary border-opacity-25" style="width: 80px; font-size: 0.85rem;">{{ $c->name }}</span>
                                                        <span class="input-group-text bg-body text-secondary border-start-0 border-end-0 border-secondary border-opacity-25 px-2" style="font-size: 0.85rem;">৳</span>
                                                        <input type="number" step="0.01" name="amounts[{{ $c->id }}]" class="form-control bg-body border-start-0 fw-medium border-secondary border-opacity-25 amount-input" value="{{ $amount }}">
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary fw-medium px-4 shadow-sm" style="border-radius: 8px;"><i class="fa-regular fa-floppy-disk me-2"></i> Save Amounts</button>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade {{ session('active_tab') === 'installments-' . $category->id ? 'show active' : '' }}" id="installments-{{ $category->id }}">
                                        @if($category->installments->count() === 0)
                                            <div class="p-5 text-center text-secondary border rounded-3 bg-body shadow-sm border-0">
                                                <i class="fa-regular fa-calendar-days fs-1 mb-3 text-primary opacity-50"></i>
                                                <h6>Installment Configuration</h6>
                                                <p class="mb-4" style="font-size: 0.85rem;">No installments generated yet. Click below to automatically create the {{ $category->installments_count }} installments.</p>
                                                <form action="{{ route('admin.fees.installments.generate', $category->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Auto Generate Installments</button>
                                                </form>
                                            </div>
                                        @else
                                            <div class="row g-3">
                                                @foreach($category->installments as $installment)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="card border border-secondary border-opacity-25 shadow-sm rounded-3 h-100 {{ !$installment->is_active ? 'opacity-50' : '' }}">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                                    <div class="d-flex align-items-center">
                                                                        <form action="{{ route('admin.fees.installments.toggle', $installment->id) }}" method="POST" class="m-0">
                                                                            @csrf
                                                                            <div class="form-check form-switch mb-0">
                                                                                <input class="form-check-input mt-1" type="checkbox" role="switch" onchange="this.form.submit()" {{ $installment->is_active ? 'checked' : '' }} title="Toggle active status" style="cursor:pointer;">
                                                                            </div>
                                                                        </form>
                                                                    </div>
                                                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_fee'))
<form action="{{ route('admin.fees.installments.destroy', $installment->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this installment?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-sm text-danger p-0 bg-transparent border-0"><i class="fa-regular fa-trash-can"></i></button>
                                                                    </form>
@endif
                                                                </div>
                                                                
                                                                <form action="{{ route('admin.fees.installments.update', $installment->id) }}" method="POST">
                                                                    @csrf
                                                                    @method('PUT')
                                                                    <div class="mb-2">
                                                                        <label class="form-label text-secondary mb-1" style="font-size: 0.7rem;">NAME</label>
                                                                        <input type="text" name="name" class="form-control form-control-sm border-secondary border-opacity-25" value="{{ $installment->name }}" required {{ !$installment->is_active ? 'disabled' : '' }}>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-secondary mb-1" style="font-size: 0.7rem;">DUE DATE</label>
                                                                        <input type="date" name="due_date" class="form-control form-control-sm border-secondary border-opacity-25" value="{{ $installment->due_date ? $installment->due_date->format('Y-m-d') : '' }}" {{ !$installment->is_active ? 'disabled' : '' }}>
                                                                    </div>
                                                                    <div class="text-end">
                                                                        <button type="submit" class="btn btn-sm btn-primary w-100 rounded-2 shadow-sm" {{ !$installment->is_active ? 'disabled' : '' }}><i class="fa-regular fa-floppy-disk me-1"></i> Save</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-5 text-center text-secondary">
                        <i class="fa-regular fa-folder-open fs-1 mb-3"></i>
                        <h5>No active fees found</h5>
                        <p>Click "Create Fee" to get started.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Tag Discounts Tab -->
    <div class="tab-pane fade" id="discounts" role="tabpanel">
        <div class="card glass-card border-0 shadow-sm">
            <div class="card-body p-5 text-center text-secondary">
                <i class="fa-solid fa-tags fs-1 mb-3"></i>
                <h5>Tag Discounts</h5>
                <p>Create rule-based discounts for specific student tags (e.g., Scholarship, Sibling).</p>
                <button class="btn btn-outline-primary rounded-pill mt-2"><i class="fa-solid fa-plus me-1"></i> Create Rule</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Fee Modal -->
<div class="modal fade" id="createFeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
            <div class="modal-header border-bottom border-light border-opacity-50 px-4 py-3">
                <h5 class="modal-title fw-bold fs-5 text-body">Create New Fee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.fees.categories.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3" style="max-height: 70vh; overflow-y: auto;">
                    
                    <!-- Fee Name -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Fee Name</label>
                        <input type="text" name="name" class="form-control bg-body-tertiary border-0" placeholder="e.g. Monthly Tuition, Exam Fee, Admission Fee" required>
                    </div>

                    <!-- Frequency -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Frequency</label>
                        <div class="d-flex flex-wrap gap-2">
                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_monthly" value="monthly" checked>
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_monthly">Monthly</label>

                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_yearly" value="yearly">
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_yearly">Yearly</label>

                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_halfyearly" value="half-yearly">
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_halfyearly">Half-Yearly</label>

                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_weekly" value="weekly">
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_weekly">Weekly</label>

                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_onetime" value="one-time">
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_onetime">One-time</label>

                            <input type="radio" class="btn-check freq-radio" name="type" id="freq_custom" value="custom">
                            <label class="btn btn-sm fw-medium freq-btn" for="freq_custom">Custom</label>
                        </div>
                        <input type="hidden" name="installments_count" id="installmentsInput" value="12">
                    </div>

                    <!-- Optional Fee Toggle -->
                    <div class="mb-4 p-3 d-flex justify-content-between align-items-center bg-body-tertiary border-0 rounded-3">
                        <div>
                            <div class="fw-bold text-body" style="font-size: 0.9rem;">Optional Fee</div>
                            <div class="text-secondary" style="font-size: 0.75rem;">Only assigned students will be charged (e.g. Admission, Transport)</div>
                        </div>
                        <div class="form-check form-switch fs-4 mb-0">
                            <input class="form-check-input border-secondary" type="checkbox" role="switch" name="is_optional" style="cursor: pointer;">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Description (optional)</label>
                        <textarea name="description" class="form-control bg-body-tertiary border-0" rows="3" placeholder="Internal notes about this fee category..."></textarea>
                    </div>

                    <!-- Amount Per Class -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-secondary" style="font-size: 0.85rem;">Amount Per Class</label>
                        @foreach($classes as $c)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fw-bold text-body" style="font-size: 0.85rem;">{{ $c->name }}</span>
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-body border-end-0 text-secondary border-light shadow-sm">৳</span>
                                <input type="number" name="amount[{{ $c->id }}]" class="form-control bg-body border-start-0 text-body fw-medium border-light shadow-sm" value="0" min="0">
                            </div>
                        </div>
                        @endforeach
                    </div>

                </div>
                <div class="modal-footer border-top border-light px-4 py-3 bg-body-tertiary bg-opacity-50">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Create Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    .accordion-button:not(.collapsed) {
        background-color: rgba(var(--bs-primary-rgb), 0.05);
        color: var(--bs-primary);
        box-shadow: none;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
    .hover-text-primary:hover {
        color: var(--bs-primary) !important;
    }
    /* Custom Accordion Arrow styles */
    .custom-accordion-btn::after {
        display: none !important; /* Hide default Bootstrap arrow */
    }
    .accordion-arrow-icon {
        transition: transform 0.3s ease;
    }
    .accordion-button:not(.collapsed) .accordion-arrow-icon {
        transform: rotate(90deg); /* Rotate arrow when expanded */
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
    
    /* Enforce global border color var(--bs-border-color) requested by user */
    .border, .border-bottom, .border-top, .border-start, .border-end, 
    .form-control, .form-select, .input-group-text, .accordion-item, 
    .accordion-button, .card, .nav-tabs {
        border-color: var(--bs-border-color) !important;
    }

    .custom-tabs .nav-link {
        color: #6c757d;
    }
    .custom-tabs .nav-link:hover {
        color: var(--bs-primary);
    }
    .custom-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        border-bottom: 2px solid var(--bs-primary) !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        if(e.target.classList.contains('apply-all-btn')) {
            const targetId = e.target.getAttribute('data-target');
            const container = document.getElementById(targetId);
            if(container) {
                const inputs = container.querySelectorAll('.amount-input');
                if(inputs.length > 0) {
                    const firstVal = inputs[0].value;
                    inputs.forEach(input => input.value = firstVal);
                }
            }
        }
    });

    document.querySelectorAll('.freq-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if(this.value === 'monthly') {
                document.getElementById('installmentsInput').value = 12;
            } else if (this.value === 'half-yearly') {
                document.getElementById('installmentsInput').value = 2;
            } else if (this.value === 'weekly') {
                document.getElementById('installmentsInput').value = 52;
            } else {
                document.getElementById('installmentsInput').value = 1;
            }
        });
    });

    @if(session('active_tab'))
    // Restore active tab and accordion
    var tabId = '{{ session('active_tab') }}';
    var accordionId = '{{ session('active_accordion') }}';
    
    if(accordionId) {
        var accordionEl = document.getElementById(accordionId);
        if(accordionEl) {
            var bsCollapse = new bootstrap.Collapse(accordionEl, {
                toggle: false
            });
            bsCollapse.show();
        }
    }

    if(tabId) {
        var tabEl = document.querySelector('button[data-bs-target="#' + tabId + '"]');
        if (tabEl) {
            var tab = new bootstrap.Tab(tabEl);
            tab.show();
        }
    }
    @endif
</script>
@endpush
