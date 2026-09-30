@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-door-open text-primary me-2"></i>Class Room Management</h4>
            <p class="text-muted fs-7 mb-0">Create, edit and manage dynamic classrooms and seating capacities</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_academic'))
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addClassroomModal">
            <i class="fa-solid fa-plus me-1"></i> Add Class Room
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

    <!-- Classrooms List Table -->
    <div class="card glass-card">
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-4">
            <h6 class="fw-bold m-0 text-primary"><i class="fa-solid fa-list me-2"></i>All Class Rooms</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                Total {{ $classrooms->count() }} {{ Str::plural('Room', $classrooms->count()) }}
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Room Number</th>
                            <th>Seating Capacity</th>
                            <th>Assigned Routines</th>
                            <th>Exam Schedules</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($classrooms as $index => $room)
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px;">
                                            <i class="fa-solid fa-door-closed fs-6"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark-emphasis">Room {{ $room->room_number }}</div>
                                            
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                        <i class="fa-solid fa-users me-1"></i>{{ $room->capacity }} Seats
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-20 fs-8">
                                        {{ $room->timetables_count }} Routine {{ Str::plural('Slot', $room->timetables_count) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-20 fs-8">
                                        {{ $room->exam_schedules_count }} Exam {{ Str::plural('Schedule', $room->exam_schedules_count) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_academic'))
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="openEditModal({{ json_encode($room) }})">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_academic'))
                                    <form action="{{ route('admin.classrooms.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete Room {{ $room->room_number }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash-can"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-door-closed fs-3 mb-2 d-block text-secondary"></i>
                                    No classrooms found. Click "Add Class Room" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Classroom Modal -->
<div class="modal fade" id="addClassroomModal" tabindex="-1" aria-labelledby="addClassroomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.classrooms.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addClassroomModalLabel">
                        <i class="fa-solid fa-door-open me-2 text-primary"></i>Add New Class Room
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Number / Name <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" class="form-control" placeholder="e.g. 101 or Lab-1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" class="form-control" placeholder="e.g. 40" required min="1" value="40">
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

<!-- Edit Classroom Modal -->
<div class="modal fade" id="editClassroomModal" tabindex="-1" aria-labelledby="editClassroomModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editClassroomForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="editClassroomModalLabel">
                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Class Room
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Room Number / Name <span class="text-danger">*</span></label>
                        <input type="text" name="room_number" id="edit_room_number" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Seating Capacity <span class="text-danger">*</span></label>
                        <input type="number" name="capacity" id="edit_capacity" class="form-control" required min="1">
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
function openEditModal(room) {
    const form = document.getElementById('editClassroomForm');
    form.action = `/admin/classrooms/${room.id}`;
    
    document.getElementById('edit_room_number').value = room.room_number;
    document.getElementById('edit_capacity').value = room.capacity;

    const editModal = new bootstrap.Modal(document.getElementById('editClassroomModal'));
    editModal.show();
}
</script>
@endsection
