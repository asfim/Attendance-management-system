@extends('layouts.app')

@section('title', 'Meal Setup')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-bowl-food text-primary me-2"></i>Meal Setup
            </h5>
            <p class="text-muted fs-8 mb-0">Configure daily meal types (Breakfast, Lunch, Dinner, Snacks)</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_food'))
<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addMealModal">
            <i class="fa-solid fa-plus me-1"></i> Add Meal
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
                            <th class="py-2">Meal Name</th>
                            <th class="py-2">Meal Code</th>
                            <th class="py-2">Meal Time</th>
                            <th class="py-2">Description</th>
                            <th class="py-2 text-center">Status</th>
                            <th class="pe-4 py-2 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($meals as $i => $meal)
                            <tr>
                                <td class="ps-4 py-2 text-muted">{{ $i + 1 }}</td>
                                <td class="py-2 fw-bold text-dark-emphasis">{{ $meal->name }}</td>
                                <td class="py-2"><span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">{{ $meal->code ?? '-' }}</span></td>
                                <td class="py-2 text-muted"><i class="fa-solid fa-clock me-1 fs-8"></i>{{ $meal->time ?? '-' }}</td>
                                <td class="py-2 text-muted fs-8">{{ $meal->description ?? '-' }}</td>
                                <td class="py-2 text-center">
                                    <span class="badge {{ $meal->status == 'active' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25' }} rounded-pill px-2 py-0 fs-8">
                                        {{ ucfirst($meal->status) }}
                                    </span>
                                </td>
                                <td class="pe-4 py-2 text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_food'))
<button class="btn btn-xs btn-outline-primary py-0 px-2 fs-8 me-1" data-bs-toggle="modal" data-bs-target="#editMealModal{{ $meal->id }}">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_food'))
<button class="btn btn-xs btn-outline-danger py-0 px-2 fs-8" data-bs-toggle="modal" data-bs-target="#deleteMealModal{{ $meal->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
@endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted fs-7">
                                    <i class="fa-solid fa-bowl-food fa-2x mb-2 d-block opacity-25"></i>
                                    No meals configured yet. Click "Add Meal" to start.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Meal Modal -->
<div class="modal fade" id="addMealModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.food.meals.store') }}" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2 text-primary"></i>Add New Meal</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body fs-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Meal Name *</label>
                            <input type="text" name="name" class="form-control form-control-sm" required placeholder="e.g. Breakfast">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Meal Code</label>
                            <input type="text" name="code" class="form-control form-control-sm" placeholder="e.g. BF-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Meal Time</label>
                            <input type="text" name="time" class="form-control form-control-sm" placeholder="e.g. 8:00 AM">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Optional details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Meal</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($meals as $meal)
    <!-- Edit Meal Modal -->
    <div class="modal fade" id="editMealModal{{ $meal->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.food.meals.update', $meal->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit Meal</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body fs-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Meal Name *</label>
                                <input type="text" name="name" class="form-control form-control-sm" value="{{ $meal->name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Meal Code</label>
                                <input type="text" name="code" class="form-control form-control-sm" value="{{ $meal->code }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Meal Time</label>
                                <input type="text" name="time" class="form-control form-control-sm" value="{{ $meal->time }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" {{ $meal->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $meal->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-secondary fs-8 fw-semibold">Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2">{{ $meal->description }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Update Meal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Meal Modal -->
    <div class="modal fade" id="deleteMealModal{{ $meal->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_food'))
<form action="{{ route('admin.food.meals.destroy', $meal->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pb-3 fs-7">
                        <i class="fa-solid fa-triangle-exclamation text-warning fa-3x mb-2"></i>
                        <h6 class="fw-bold mb-2">Delete Meal?</h6>
                        <p class="text-muted fs-8 mb-0">Are you sure you want to delete <strong>{{ $meal->name }}</strong>?</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Delete Meal</button>
                    </div>
                </form>
@endif
            </div>
        </div>
    </div>
@endforeach
@endsection
