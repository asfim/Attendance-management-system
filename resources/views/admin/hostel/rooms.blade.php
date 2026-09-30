@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-door-closed text-primary me-2"></i>Hostel Rooms</h4>
            <p class="text-muted fs-7 mb-0">Manage rooms, capacities, and bed fees under each hall/house</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_hostel'))
<button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
            <i class="fa-solid fa-plus me-1"></i> Add New Room
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
            <h6 class="fw-bold m-0 text-primary"><i class="fa-solid fa-list me-2"></i>All Hostel Rooms</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                Total {{ $rooms->count() }} {{ Str::plural('Room', $rooms->count()) }}
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
                            <th>Room Type</th>
                            <th style="width: 5px;">Capacity</th>
                            <th>Available Beds</th>
                            <th>Cost per Bed</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rooms as $index => $r)
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-primary">
                                        <i class="fa-solid fa-building-user me-2"></i>{{ $r->hostel?->name ?? 'N/A' }}
                                    </div>
                                    <span class="text-muted fs-8">{{ $r->hostel?->type }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark-emphasis">Room {{ $r->room_number }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                        {{ $r->room_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 fs-8">
                                        {{ $r->capacity }} Beds
                                    </span>
                                </td>
                                <td>
                                    @if($r->available_beds_count > 0)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i>{{ $r->available_beds_count }} Available
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-20 fs-8">
                                            <i class="fa-solid fa-circle-xmark me-1"></i>FULL (0 Available)
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold text-dark-emphasis">${{ number_format($r->cost_per_bed, 2) }}</span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                onclick="openEditRoomModal({{ json_encode($r) }})">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                        </button>
                                        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_hostel'))
<form action="{{ route('admin.hostel.rooms.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Room {{ $r->room_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                <i class="fa-solid fa-trash-can"></i> Delete
                                            </button>
                                        </form>
@endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-door-closed fs-3 mb-2 d-block text-secondary"></i>
                                    No rooms found. Click "Add New Room" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.hostel.rooms.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-door-closed me-2 text-primary"></i>Add New Room
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Hall / House <span class="text-danger">*</span></label>
                        <select name="hostel_id" class="form-select" required>
                            <option value="">Select Hall / House</option>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control" placeholder="e.g. 101" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" class="form-select" required>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="AC Room">AC Room</option>
                            <option value="Non-AC">Non-AC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Capacity (Total Beds) <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control" placeholder="e.g. 2 or 4" required min="1" value="2">
                        <span class="text-muted fs-8">Beds will automatically be generated for this room up to capacity.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Cost per Bed (Hostel Fee) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="cost_per_bed" class="form-control" placeholder="e.g. 1500.00" required min="0" value="1500.00">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editRoomForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Room
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Hall / House <span class="text-danger">*</span></label>
                        <select name="hostel_id" id="edit_room_hostel_id" class="form-select" required>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Number <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Type <span class="text-danger">*</span></label>
                        <select name="room_type" id="edit_room_type" class="form-select" required>
                            <option value="Standard">Standard</option>
                            <option value="Deluxe">Deluxe</option>
                            <option value="AC Room">AC Room</option>
                            <option value="Non-AC">Non-AC</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Capacity (Total Beds) <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="edit_room_capacity" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Cost per Bed (Hostel Fee) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="cost_per_bed" id="edit_room_cost_per_bed" class="form-control" required min="0">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditRoomModal(room) {
    const form = document.getElementById('editRoomForm');
    form.action = `/admin/hostel/rooms/${room.id}`;

    document.getElementById('edit_room_hostel_id').value = room.hostel_id;
    document.getElementById('edit_room_number').value = room.room_number;
    document.getElementById('edit_room_type').value = room.room_type;
    document.getElementById('edit_room_capacity').value = room.capacity;
    document.getElementById('edit_room_cost_per_bed').value = room.cost_per_bed;

    const editModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
    editModal.show();
}
</script>
@endsection
