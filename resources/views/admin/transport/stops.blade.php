@extends('layouts.app')

@section('title', 'Manage Stops - ' . $route->route_name)

@section('content')
<div class="container-fluid px-4">
    <div class="mb-4">
        <a href="{{ route('admin.transport.routes') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Routes
        </a>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h3 mb-0 text-gray-800">
                    <i class="fa-solid fa-map-pin text-info me-2"></i>Route Stops: {{ $route->route_name }}
                </h2>
                <p class="text-muted mb-0">
                    <i class="fa-solid fa-location-dot text-success me-1"></i> {{ $route->start_point }}
                    <i class="fa-solid fa-arrow-right mx-2"></i>
                    <i class="fa-solid fa-location-dot text-danger me-1"></i> {{ $route->end_point }}
                    | Base Fee: ৳{{ number_format($route->default_monthly_fee, 2) }}
                </p>
            </div>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_transport'))
<button type="button" class="btn btn-info text-white" data-bs-toggle="modal" data-bs-target="#addStopModal">
                <i class="fa-solid fa-plus me-1"></i> Add Stop
            </button>
@endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4 border-0" style="border-radius: 15px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3" style="width: 80px;">Order</th>
                            <th class="py-3">Stop Name</th>
                            <th class="py-3 text-center">Pickup Time</th>
                            <th class="py-3 text-center">Drop Time</th>
                            <th class="py-3 text-center">Monthly Fee (৳)</th>
                            <th class="px-4 py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($route->stops as $stop)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="badge bg-secondary rounded-circle p-2 px-3 fs-6">{{ $stop->stop_order }}</span>
                                </td>
                                <td class="py-3 fw-bold fs-6">
                                    {{ $stop->stop_name }}
                                </td>
                                <td class="py-3 text-center text-muted">
                                    {{ $stop->pickup_time ? \Carbon\Carbon::parse($stop->pickup_time)->format('h:i A') : 'N/A' }}
                                </td>
                                <td class="py-3 text-center text-muted">
                                    {{ $stop->drop_time ? \Carbon\Carbon::parse($stop->drop_time)->format('h:i A') : 'N/A' }}
                                </td>
                                <td class="py-3 text-center fw-bold text-success">
                                    {{ number_format($stop->additional_fee, 2) }}
                                </td>
                                <td class="px-4 py-3 text-end">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_transport'))
<button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#editStopModal{{ $stop->id }}">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_transport'))
<button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteStopModal{{ $stop->id }}">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
@endif
                                </td>
                            </tr>

                            <!-- Edit Stop Modal -->
                            <div class="modal fade" id="editStopModal{{ $stop->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.transport.stops.update', $stop->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Stop</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-9">
                                                        <label class="form-label text-secondary">Stop Name *</label>
                                                        <input type="text" name="stop_name" class="form-control" value="{{ $stop->stop_name }}" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label text-secondary">Order *</label>
                                                        <input type="number" name="stop_order" class="form-control" value="{{ $stop->stop_order }}" required min="1">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-secondary">Pickup Time</label>
                                                        <input type="time" name="pickup_time" class="form-control" value="{{ $stop->pickup_time }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label text-secondary">Drop Time</label>
                                                        <input type="time" name="drop_time" class="form-control" value="{{ $stop->drop_time }}">
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label text-secondary">Monthly Fee (৳) *</label>
                                                        <input type="number" step="0.01" name="additional_fee" class="form-control" value="{{ $stop->additional_fee }}" required min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Stop</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Delete Stop Modal -->
                            <div class="modal fade" id="deleteStopModal{{ $stop->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_transport'))
<form action="{{ route('admin.transport.stops.destroy', $stop->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header border-0 pb-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center pb-4">
                                                <i class="fa-solid fa-triangle-exclamation text-warning fa-4x mb-3"></i>
                                                <h4 class="mb-3">Delete Stop?</h4>
                                                <p class="text-muted mb-0">Are you sure you want to delete <strong>{{ $stop->stop_name }}</strong>? This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer justify-content-center border-0 pt-0 mb-3">
                                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger px-4">Delete Stop</button>
                                            </div>
                                        </form>
@endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-map-pin fa-3x mb-3" style="opacity: 0.2"></i>
                                    <h5>No Stops Added</h5>
                                    <p>Start by adding stops to this route.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Stop Modal -->
<div class="modal fade" id="addStopModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.transport.routes.stops.store', $route->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add New Stop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-9">
                            <label class="form-label text-secondary">Stop Name *</label>
                            <input type="text" name="stop_name" class="form-control" required placeholder="e.g. Azampur Bus Stand">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-secondary">Order *</label>
                            <input type="number" name="stop_order" class="form-control" value="{{ count($route->stops) + 1 }}" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Pickup Time</label>
                            <input type="time" name="pickup_time" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary">Drop Time</label>
                            <input type="time" name="drop_time" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-secondary">Monthly Fee (৳) *</label>
                            <input type="number" step="0.01" name="additional_fee" class="form-control" value="0.00" required min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white">Add Stop</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
