@extends('layouts.app')

@section('title', 'Manage Transport Routes')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-route text-primary me-2"></i>Transport Routes
            </h5>
            <p class="text-muted fs-8 mb-0">Manage all vehicle routes and their stops</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_transport'))
<button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addRouteModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Route
        </button>
@endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2 fs-7 mb-3" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2 fs-7 mb-3" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show py-2 fs-7 mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm mb-4 border-0" style="border-radius: 12px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-7">
                    <thead class="bg-light fs-8 text-secondary">
                        <tr>
                            <th class="ps-4 py-2">Route Information</th>
                            <th class="py-2 text-center">Stops</th>
                            <th class="py-2 text-center">Active Allocations</th>
                            <th class="py-2 text-center">Status</th>
                            <th class="pe-4 py-2 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($routes as $route)
                            <tr>
                                <td class="ps-4 py-2">
                                    <div class="fw-bold fs-7 mb-0">
                                        {{ $route->route_name }}
                                        @if($route->route_code)
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border ms-1 fs-8">{{ $route->route_code }}</span>
                                        @endif
                                    </div>
                                    <div class="text-muted fs-8">
                                        <i class="fa-solid fa-location-dot text-success me-1"></i> {{ $route->start_point }}
                                        <i class="fa-solid fa-arrow-right mx-1 fs-8 text-muted"></i>
                                        <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $route->end_point }}
                                    </div>
                                </td>

                                <td class="py-2 text-center">
                                    <a href="{{ route('admin.transport.routes.stops', $route->id) }}" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.4;">
                                        <i class="fa-solid fa-location-dot me-1"></i>{{ $route->stops_count }} {{ $route->stops_count == 1 ? 'Stop' : 'Stops' }}
                                    </a>
                                </td>
                                <td class="py-2 text-center">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.4;">
                                        {{ $route->allocations_count }} Active
                                    </span>
                                </td>
                                <td class="py-2 text-center">
                                    @if($route->status == 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.4;">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0" style="font-size: 0.7rem; line-height: 1.4;">
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 py-2 text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_transport'))
<button class="btn btn-xs btn-outline-primary py-0 px-2 fs-8 me-1" data-bs-toggle="modal" data-bs-target="#editRouteModal{{ $route->id }}">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </button>
                                    @endif
                                    @if($route->allocations_count == 0)
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_transport'))
<button class="btn btn-xs btn-outline-danger py-0 px-2 fs-8" data-bs-toggle="modal" data-bs-target="#deleteRouteModal{{ $route->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
@endif
                                    @else
                                    <button class="btn btn-xs btn-outline-secondary py-0 px-2 fs-8" disabled title="Cannot delete route with allocations">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted fs-7">
                                    <i class="fa-solid fa-route fa-2x mb-2 d-block" style="opacity: 0.2"></i>
                                    <h6>No Routes Found</h6>
                                    <p class="fs-8 mb-0">Start by adding your first transport route.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Route Modal -->
<div class="modal fade" id="addRouteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.transport.routes.store') }}" method="POST">
                @csrf
                <div class="modal-header py-2">
                    <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus me-2 text-primary"></i>Add New Route</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body fs-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Route Name *</label>
                            <input type="text" name="route_name" class="form-control form-control-sm" required placeholder="e.g. Uttara Route">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Route Code (Optional)</label>
                            <input type="text" name="route_code" class="form-control form-control-sm" placeholder="e.g. UT-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Start Point *</label>
                            <input type="text" name="start_point" class="form-control form-control-sm" required placeholder="e.g. House Building">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">End Point *</label>
                            <input type="text" name="end_point" class="form-control form-control-sm" required placeholder="e.g. Madrasa Campus">
                        </div>
                        <input type="hidden" name="default_monthly_fee" value="0.00">
                        <div class="col-md-6">
                            <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-secondary fs-8 fw-semibold">Remarks / Description</label>
                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Any additional information..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Create Route</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach($routes as $route)
    <!-- Edit Route Modal -->
    <div class="modal fade" id="editRouteModal{{ $route->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.transport.routes.update', $route->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header py-2">
                        <h6 class="modal-title fw-bold"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit Route</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body fs-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Route Name *</label>
                                <input type="text" name="route_name" class="form-control form-control-sm" value="{{ $route->route_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Route Code (Optional)</label>
                                <input type="text" name="route_code" class="form-control form-control-sm" value="{{ $route->route_code }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Start Point *</label>
                                <input type="text" name="start_point" class="form-control form-control-sm" value="{{ $route->start_point }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">End Point *</label>
                                <input type="text" name="end_point" class="form-control form-control-sm" value="{{ $route->end_point }}" required>
                            </div>
                            <input type="hidden" name="default_monthly_fee" value="0.00">
                            <div class="col-md-6">
                                <label class="form-label text-secondary fs-8 fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-sm">
                                    <option value="active" {{ $route->status == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ $route->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-secondary fs-8 fw-semibold">Remarks / Description</label>
                                <textarea name="description" class="form-control form-control-sm" rows="2">{{ $route->description }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Update Route</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Route Modal -->
    @if($route->allocations_count == 0)
    <div class="modal fade" id="deleteRouteModal{{ $route->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_transport'))
<form action="{{ route('admin.transport.routes.destroy', $route->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header border-0 pb-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center pb-3 fs-7">
                        <i class="fa-solid fa-triangle-exclamation text-warning fa-3x mb-2"></i>
                        <h6 class="fw-bold mb-2">Delete Route?</h6>
                        <p class="text-muted fs-8 mb-0">Are you sure you want to delete <strong>{{ $route->route_name }}</strong>? This will also delete all stops associated with it.</p>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pt-0 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-3">Delete Route</button>
                    </div>
                </form>
@endif
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
