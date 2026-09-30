@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-building-user text-primary me-2"></i>Hostel Halls / Houses</h4>
            <p class="text-muted fs-7 mb-0">Manage hostel halls, houses, and building locations</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_hostel'))
<button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addHallModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Hall / House
        </button>
@endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 text-white" role="alert" style="background-color: #ef4444 !important;">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        @forelse($hostels as $h)
            <div class="col-md-6 col-lg-4">
                <div class="card glass-card h-100 p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 mb-2">
                                {{ $h->type }} Hostel
                            </span>
                            <h5 class="fw-bold m-0 text-dark-emphasis">{{ $h->name }}</h5>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-circle" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <button class="dropdown-item fs-7" onclick="openEditHallModal({{ json_encode($h) }})">
                                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Hall
                                    </button>
                                </li>
                                <li>
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_hostel'))
<form action="{{ route('admin.hostel.halls.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $h->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item fs-7 text-danger">
                                            <i class="fa-solid fa-trash-can me-2"></i>Delete Hall
                                        </button>
                                    </form>
@endif
                                </li>
                            </ul>
                        </div>
                    </div>

                    <p class="text-muted fs-7 mb-4">
                        <i class="fa-solid fa-location-dot me-1 text-danger"></i>{{ $h->address ?? 'No address provided' }}
                    </p>

                    <div class="row g-2 pt-3 border-top mt-auto text-center">
                        <div class="col-6 border-end">
                            <span class="fs-4 fw-bold text-primary">{{ $h->rooms_count }}</span>
                            <div class="text-muted fs-8">Total Rooms</div>
                        </div>
                        <div class="col-6">
                            <span class="fs-4 fw-bold text-success">{{ $h->beds_count }}</span>
                            <div class="text-muted fs-8">Total Beds</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card glass-card text-center py-5 text-muted">
                    <i class="fa-solid fa-building-user fs-2 mb-3 text-secondary"></i>
                    No hostel halls or houses found. Click "Add New Hall / House" to create one.
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Add Hall Modal -->
<div class="modal fade" id="addHallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.hostel.halls.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-building-user me-2 text-primary"></i>Add New Hall / House
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Hall / House Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Shahidullah Hall" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Hostel Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="Boys">Boys</option>
                            <option value="Girls">Girls</option>
                            <option value="Combined">Combined</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Address / Building Location</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="e.g. North Campus, Block A"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Hall</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Hall Modal -->
<div class="modal fade" id="editHallModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editHallForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Hall / House
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Hall / House Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_hall_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Hostel Type <span class="text-danger">*</span></label>
                        <select name="type" id="edit_hall_type" class="form-select" required>
                            <option value="Boys">Boys</option>
                            <option value="Girls">Girls</option>
                            <option value="Combined">Combined</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Address / Building Location</label>
                        <textarea name="address" id="edit_hall_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Hall</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditHallModal(hall) {
    const form = document.getElementById('editHallForm');
    form.action = `/admin/hostel/halls/${hall.id}`;
    
    document.getElementById('edit_hall_name').value = hall.name;
    document.getElementById('edit_hall_type').value = hall.type;
    document.getElementById('edit_hall_address').value = hall.address || '';

    const editModal = new bootstrap.Modal(document.getElementById('editHallModal'));
    editModal.show();
}
</script>
@endsection
