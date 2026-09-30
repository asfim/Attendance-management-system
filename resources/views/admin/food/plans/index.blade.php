@extends('layouts.app')

@section('title', 'Food Fee Setup')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i>Food Fee Setup (Plans)
            </h5>
            <p class="text-muted fs-8 mb-0">Configure monthly food fee plans for Residential, Non-Residential, and Special categories</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_food'))
<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
            <i class="fa-solid fa-plus me-1"></i> Add Food Plan
        </button>
@endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 fs-7 mb-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light fs-8 text-secondary">
                        <tr>
                            <th class="ps-4 py-2">#</th>
                            <th class="py-2">Plan Name</th>
                            <th class="py-2">Category</th>
                            <th class="py-2">Class</th>
                            <th class="py-2">Monthly Fee</th>
                            <th class="py-2">Billing Days</th>
                            <th class="py-2 text-center">Status</th>
                            <th class="pe-4 py-2 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $i => $plan)
                            <tr>
                                <td class="ps-4 py-2 text-muted">{{ $i + 1 }}</td>
                                <td class="py-2 fw-bold text-dark-emphasis">{{ $plan->name }}</td>
                                <td class="py-2"><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fs-8">{{ $plan->student_category ?? 'General' }}</span></td>
                                <td class="py-2 text-muted">{{ $plan->schoolClass->name ?? 'All Classes' }}</td>
                                <td class="py-2 fw-bold text-success">৳{{ number_format($plan->monthly_fee, 2) }}</td>
                                <td class="py-2 text-muted">{{ $plan->billing_days }} Days</td>
                                <td class="py-2 text-center">
                                    <span class="badge {{ $plan->status == 'active' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' }} rounded-pill px-2 py-0 fs-8">
                                        {{ ucfirst($plan->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 py-2 text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_food'))
<button class="btn btn-xs btn-outline-primary py-0 px-2 fs-8 me-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $plan->id }}">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_food'))
<button class="btn btn-xs btn-outline-danger py-0 px-2 fs-8" data-bs-toggle="modal" data-bs-target="#deletePlanModal{{ $plan->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
@endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted fs-7">
                                    <i class="fa-solid fa-file-invoice-dollar fa-2x mb-2 d-block opacity-25"></i>
                                    No food plans configured yet. Click "Add Food Plan" to start.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Plan Modal -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.food.plans.store') }}" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2 text-primary"></i>Add Food Fee Plan</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body fs-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Plan Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Residential Plan">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Student Category</label>
                            <input type="text" name="student_category" class="form-control form-control-sm" placeholder="e.g. Residential, Non-Residential">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Monthly Fee (৳) *</label>
                            <input type="number" step="0.01" name="monthly_fee" class="form-control form-control-sm" required placeholder="e.g. 2500">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Billing Days *</label>
                            <input type="number" name="billing_days" class="form-control form-control-sm" value="30" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Target Class</label>
                            <select name="school_class_id" class="form-select form-select-sm">
                                <option value="">All Classes</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Food Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($plans as $plan)
    <!-- Edit Plan Modal -->
    <div class="modal fade" id="editPlanModal{{ $plan->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.food.plans.update', $plan->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit Food Plan</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body fs-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Plan Name *</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $plan->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Student Category</label>
                                <input type="text" name="student_category" class="form-control form-control-sm" value="{{ $plan->student_category }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Monthly Fee (৳) *</label>
                                <input type="number" step="0.01" name="monthly_fee" class="form-control form-control-sm" value="{{ $plan->monthly_fee }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Billing Days *</label>
                                <input type="number" name="billing_days" class="form-control form-control-sm" value="{{ $plan->billing_days }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Target Class</label>
                                <select name="school_class_id" class="form-select form-select-sm">
                                    <option value="">All Classes</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}" {{ $plan->school_class_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" {{ $plan->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $plan->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Plan Modal -->
    <div class="modal fade" id="deletePlanModal{{ $plan->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_food'))
<form action="{{ route('admin.food.plans.destroy', $plan->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pb-3 fs-7">
                        <i class="fa-solid fa-triangle-exclamation text-warning fa-3x mb-2"></i>
                        <h6 class="fw-bold mb-2">Delete Food Plan?</h6>
                        <p class="text-muted fs-8 mb-0">Are you sure you want to delete <strong>{{ $plan->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Delete Plan</button>
                    </div>
                </form>
@endif
            </div>
        </div>
    </div>
@endforeach
@endsection
