@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-bed text-primary me-2"></i>Hostel Beds</h4>
            <p class="text-muted fs-7 mb-0">Manage individual beds, numbers, and availability statuses</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_hostel'))
<button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addBedModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Bed
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

    <div class="card glass-card">
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
            <h6 class="fw-bold m-0 text-primary"><i class="fa-solid fa-list me-2"></i>All Hostel Beds</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                Total {{ $beds->count() }} {{ Str::plural('Bed', $beds->count()) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Hall / House</th>
                            <th>Room Number</th>
                            <th>Bed Number</th>
                            <th>Status</th>
                            <th>Current Student</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($beds as $index => $b)
                            @php
                                $activeAlloc = $b->allocations->where('status', 'active')->first();
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        <i class="fa-solid fa-building-user me-2"></i>{{ $b->room?->hostel?->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark-emphasis">Room {{ $b->room?->room_number }}</div>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark-emphasis"><i class="fa-solid fa-bed me-2 text-secondary"></i>{{ $b->bed_number }}</span>
                                </td>
                                <td>
                                    @if($b->status === 'available')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i>Available
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8">
                                            <i class="fa-solid fa-user-lock me-1"></i>Occupied
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($activeAlloc && $activeAlloc->studentProfile)
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.75rem;">
                                                <i class="fa-solid fa-user-graduate"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold fs-7">{{ $activeAlloc->studentProfile->user?->name }}</div>
                                                <span class="text-muted fs-8">ADM: {{ $activeAlloc->studentProfile->admission_no }}</span>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fs-8">-</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="openEditBedModal({{ json_encode($b) }})">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_hostel'))
<form action="{{ route('admin.hostel.beds.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete {{ $b->bed_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" {{ $b->status === 'occupied' ? 'disabled' : '' }}>
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
@endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-bed fs-3 mb-2 d-block text-secondary"></i>
                                    No beds found. Click "Add New Bed" or create a room to auto-generate beds.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Bed Modal -->
<div class="modal fade" id="addBedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.hostel.beds.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-bed me-2 text-primary"></i>Add New Bed
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Room <span class="text-danger">*</span></label>
                        <select name="room_id" class="form-select" required>
                            <option value="">Select Room</option>
                            @foreach($rooms as $rm)
                                <option value="{{ $rm->id }}">
                                    {{ $rm->hostel?->name }} - Room {{ $rm->room_number }} ({{ $rm->room_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Bed Number / Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="bed_number" class="form-control" placeholder="e.g. Bed 1 or B-101" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Bed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bed Modal -->
<div class="modal fade" id="editBedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editBedForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Bed
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Room <span class="text-danger">*</span></label>
                        <select name="room_id" id="edit_bed_room_id" class="form-select" required>
                            @foreach($rooms as $rm)
                                <option value="{{ $rm->id }}">
                                    {{ $rm->hostel?->name }} - Room {{ $rm->room_number }} ({{ $rm->room_type }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Bed Number / Identifier <span class="text-danger">*</span></label>
                        <input type="text" name="bed_number" id="edit_bed_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Bed Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_bed_status" class="form-select" required>
                            <option value="available">Available</option>
                            <option value="occupied">Occupied</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Bed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditBedModal(bed) {
    const form = document.getElementById('editBedForm');
    form.action = `/admin/hostel/beds/${bed.id}`;
    
    document.getElementById('edit_bed_room_id').value = bed.room_id;
    document.getElementById('edit_bed_number').value = bed.bed_number;
    document.getElementById('edit_bed_status').value = bed.status;

    const editModal = new bootstrap.Modal(document.getElementById('editBedModal'));
    editModal.show();
}
</script>
@endsection
