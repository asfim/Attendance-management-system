@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-user-check text-primary me-2"></i>Hostel Bed Allocations</h4>
            <p class="text-muted fs-7 mb-0">Assign available hostel beds to students or release active allocations</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#allocateModal">
            <i class="fa-solid fa-plus me-1"></i> Allocate Bed to Student
        </button>
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
            <h6 class="fw-bold m-0 text-primary"><i class="fa-solid fa-list me-2"></i>Active & Past Allocations</h6>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                Total {{ $allocations->count() }} {{ Str::plural('Allocation', $allocations->count()) }}
            </span>
        </div>
        <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3 bg-body-tertiary">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-7 text-muted">Show</span>
                <select id="dt-length" class="form-select form-select-sm" style="width: auto; min-width: 80px; border-radius: 6px;">
                    <option value="30" selected>30</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="150">150</option>
                    <option value="all">All</option>
                </select>
                <span class="fs-7 text-muted">entries</span>
            </div>
            <div class="d-flex align-items-center gap-2" style="max-width: 300px; width: 100%;">
                <span class="fs-7 text-muted">Search:</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text  border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="dt-search" class="form-control ps-0 border-start-0" placeholder="Search allocations..." style="border-radius: 0 6px 6px 0;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle table-hover mb-0" id="allocations-table">
                    <thead>
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Student Name</th>
                            <th style="width: 7px;">Class & Roll</th>
                            <th>Hall / House</th>
                            <th>Room & Bed</th>
                            <th>Allocation Date</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocations as $index => $alloc)
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark-emphasis">
                                        <i class="fa-solid fa-user-graduate text-primary me-2"></i>{{ $alloc->studentProfile?->user?->name ?? 'N/A' }}
                                    </div>
                                    <span class="text-muted fs-8">ADM: {{ $alloc->studentProfile?->admission_no }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold fs-7">{{ $alloc->studentProfile?->schoolClass?->name ?? 'N/A' }}</div>
                                    <span class="text-muted fs-8">Roll: {{ $alloc->studentProfile?->roll_no }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fs-8">
                                        <i class="fa-solid fa-building-user me-1"></i>{{ $alloc->bed?->room?->hostel?->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold fs-7 text-dark-emphasis">Room {{ $alloc->bed?->room?->room_number }}</div>
                                    <span class="text-muted fs-8"><i class="fa-solid fa-bed me-1"></i>{{ $alloc->bed?->bed_number }}</span>
                                </td>
                                <td>
                                    <span class="text-muted fs-8">
                                        <i class="fa-regular fa-calendar me-1"></i>{{ $alloc->allocation_date?->format('M d, Y') ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($alloc->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                            Released
                                        </span>
                                    @endif
                                </td>
                                @php
                                    $editData = [
                                        'id' => $alloc->id,
                                        'student_profile_id' => $alloc->student_profile_id,
                                        'student_name' => $alloc->studentProfile?->user?->name ?? 'N/A',
                                        'student_details' => 'ADM: ' . ($alloc->studentProfile?->admission_no ?? '') . ', Roll: ' . ($alloc->studentProfile?->roll_no ?? ''),
                                        'hostel_id' => $alloc->bed?->room?->hostel_id,
                                        'room_id' => $alloc->bed?->room_id,
                                        'bed_id' => $alloc->bed_id,
                                        'allocation_date' => $alloc->allocation_date?->format('Y-m-d'),
                                        'status' => $alloc->status,
                                        'current_room_number' => $alloc->bed?->room?->room_number ?? '',
                                        'current_room_cost' => $alloc->bed?->room?->cost_per_bed ?? '',
                                        'current_bed_number' => $alloc->bed?->bed_number ?? '',
                                        'current_hostel_name' => $alloc->bed?->room?->hostel?->name ?? '',
                                    ];
                                @endphp
                                <td class="text-end pe-4">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick='openEditAllocationModal(@json($editData))'>
                                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                    </button>

                                    @if($alloc->status === 'active')
                                        <form action="{{ route('admin.hostel.deallocate', $alloc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to release this bed allocation?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Release
                                            </button>
                                        </form>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-user-check fs-3 mb-2 d-block text-secondary"></i>
                                    No hostel allocations found. Click "Allocate Bed to Student" to create one.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white" style="border-radius: 0 0 16px 16px !important;">
            <span class="fs-8 text-muted" id="dt-info">Showing 0 to 0 of 0 entries</span>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm m-0" id="dt-pagination">
                    <!-- Pagination buttons dynamically rendered via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Allocate Bed Modal -->
<div class="modal fade" id="allocateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.hostel.allocate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-check me-2 text-primary"></i>Allocate Bed to Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Student <span class="text-danger">*</span></label>
                        <div class="input-group mb-2">
                            <span class="input-group-text bg-body-tertiary border-end-0">
                                <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                            </span>
                            <input type="text" id="student_search_input" class="form-control border-start-0 ps-0" placeholder="Type student name, roll, or admission no..." oninput="filterStudentSelectOptions(this.value)">
                        </div>
                        <div id="alloc_student_list_container" class="border rounded p-3 mb-2" style="max-height: 250px; overflow-y: auto; border-radius: 8px;">
                            @forelse($students as $st)
                                <div class="form-check d-flex align-items-center mb-2 student-list-item">
                                    <input class="form-check-input me-3 student-radio" type="radio" name="student_profile_id" value="{{ $st->id }}" id="student_check_{{ $st->id }}" required>
                                    <label class="form-check-label d-flex align-items-center cursor-pointer w-100" for="student_check_{{ $st->id }}" style="user-select: none; cursor: pointer;">
                                        <img src="{{ $st->photoUrl() }}" class="rounded-circle me-3" width="36" height="36" alt="Student Photo" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($st->user?->name ?? 'Student') }}&background=random&color=fff'">
                                        <div>
                                            <span class="fw-semibold d-block" style="font-size: 0.95rem;">{{ $st->user?->name }}</span>
                                            <span class="text-muted" style="font-size: 0.8rem;">Roll: {{ $st->roll_no }} | Class: {{ $st->schoolClass->name ?? 'N/A' }}</span>
                                        </div>
                                    </label>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">No unallocated students found.</div>
                            @endforelse
                        </div>
                        <small class="text-muted fs-8 mt-1 d-block">
                            <i class="fa-solid fa-users me-1"></i><span id="student_count_badge">{{ count($students) }}</span> unallocated students found
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Hall / House <span class="text-danger">*</span></label>
                        <select id="alloc_hostel_id" class="form-select" onchange="loadAvailableRoomsForAlloc(this.value)" required>
                            <option value="">Select Hall / House</option>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Room (Available Only) <span class="text-danger">*</span></label>
                        <select id="alloc_room_id" class="form-select" onchange="loadAvailableBedsForAlloc(this.value)" required disabled>
                            <option value="">Select Room</option>
                        </select>
                        <span class="text-muted fs-8" id="alloc_room_hint">Full rooms with 0 available beds will not be displayed.</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Bed <span class="text-danger">*</span></label>
                        <select name="bed_id" id="alloc_bed_id" class="form-select" required disabled>
                            <option value="">Select Bed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Allocation Date <span class="text-danger">*</span></label>
                        <input type="date" name="allocation_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate Bed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Bed Allocation Modal -->
<div class="modal fade" id="editAllocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="editAllocationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Bed Allocation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Student details display (Read only) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Student</label>
                        <input type="text" id="edit_student_display" class="form-control bg-body-tertiary" style="border-radius: 8px;" readonly>
                        <input type="hidden" name="student_profile_id" id="edit_student_profile_id">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Hall / House <span class="text-danger">*</span></label>
                        <select id="edit_hostel_id" class="form-select" onchange="loadAvailableRoomsForEdit(this.value)" required style="border-radius: 8px;">
                            <option value="">Select Hall / House</option>
                            @foreach($hostels as $h)
                                <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Room (Available Only) <span class="text-danger">*</span></label>
                        <select id="edit_room_id" class="form-select" onchange="loadAvailableBedsForEdit(this.value)" required disabled style="border-radius: 8px;">
                            <option value="">Select Room</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Bed <span class="text-danger">*</span></label>
                        <select name="bed_id" id="edit_bed_id" class="form-select" required disabled style="border-radius: 8px;">
                            <option value="">Select Bed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Allocation Date <span class="text-danger">*</span></label>
                        <input type="date" name="allocation_date" id="edit_allocation_date" class="form-control" required style="border-radius: 8px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Status <span class="text-danger">*</span></label>
                        <select name="status" id="edit_status" class="form-select" required style="border-radius: 8px;">
                            <option value="active">Active</option>
                            <option value="released">Released</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterStudentSelectOptions(searchText) {
    const search = (searchText || '').toLowerCase().trim();
    const items = document.querySelectorAll('.student-list-item');

    let count = 0;
    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(search)) {
            item.style.setProperty('display', 'flex', 'important');
            count++;
        } else {
            item.style.setProperty('display', 'none', 'important');
        }
    });

    const badge = document.getElementById('student_count_badge');
    if (badge) badge.innerText = count;
}

function loadAvailableRoomsForAlloc(hostelId) {
    const roomSelect = document.getElementById('alloc_room_id');
    const bedSelect = document.getElementById('alloc_bed_id');

    roomSelect.innerHTML = '<option value="">Loading available rooms...</option>';
    roomSelect.disabled = true;
    bedSelect.innerHTML = '<option value="">Select Bed</option>';
    bedSelect.disabled = true;

    if (!hostelId) return;

    fetch(`/admin/hostel/available-rooms/${hostelId}`)
        .then(res => res.json())
        .then(rooms => {
            if (rooms.length === 0) {
                roomSelect.innerHTML = '<option value="">No rooms with available beds in this hall</option>';
                roomSelect.disabled = true;
                return;
            }

            roomSelect.innerHTML = '<option value="">Select Room</option>';
            rooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `Room ${r.room_number} (${r.available_beds} beds available, Fee: $${r.cost_per_bed})`;
                roomSelect.appendChild(opt);
            });
            roomSelect.disabled = false;
        });
}

function loadAvailableBedsForAlloc(roomId) {
    const bedSelect = document.getElementById('alloc_bed_id');
    bedSelect.innerHTML = '<option value="">Loading available beds...</option>';
    bedSelect.disabled = true;

    if (!roomId) return;

    fetch(`/admin/hostel/available-beds/${roomId}`)
        .then(res => res.json())
        .then(beds => {
            if (beds.length === 0) {
                bedSelect.innerHTML = '<option value="">No available beds</option>';
                bedSelect.disabled = true;
                return;
            }

            bedSelect.innerHTML = '<option value="">Select Bed</option>';
            beds.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.bed_number;
                bedSelect.appendChild(opt);
            });
            bedSelect.disabled = false;
        });
}

// ==========================================
// EDIT BED ALLOCATION MODAL LOGIC
// ==========================================
let editModalInstance = null;
let currentAllocation = null;

function openEditAllocationModal(alloc) {
    currentAllocation = alloc;

    document.getElementById('editAllocationForm').action = `/admin/hostel/allocations/${alloc.id}`;
    document.getElementById('edit_student_display').value = `${alloc.student_name} (${alloc.student_details})`;
    document.getElementById('edit_student_profile_id').value = alloc.student_profile_id;
    document.getElementById('edit_allocation_date').value = alloc.allocation_date;
    document.getElementById('edit_status').value = alloc.status;
    document.getElementById('edit_hostel_id').value = alloc.hostel_id || "";

    loadAvailableRoomsForEdit(alloc.hostel_id, alloc.room_id, alloc.bed_id);

    if (!editModalInstance) {
        editModalInstance = new bootstrap.Modal(document.getElementById('editAllocationModal'));
    }
    editModalInstance.show();
}

function loadAvailableRoomsForEdit(hostelId, selectRoomId = null, selectBedId = null) {
    const roomSelect = document.getElementById('edit_room_id');
    const bedSelect = document.getElementById('edit_bed_id');

    roomSelect.innerHTML = '<option value="">Loading available rooms...</option>';
    roomSelect.disabled = true;
    bedSelect.innerHTML = '<option value="">Select Bed</option>';
    bedSelect.disabled = true;

    if (!hostelId) return;

    fetch(`/admin/hostel/available-rooms/${hostelId}`)
        .then(res => res.json())
        .then(rooms => {
            roomSelect.innerHTML = '<option value="">Select Room</option>';

            const roomIds = rooms.map(r => r.id);

            if (currentAllocation && currentAllocation.hostel_id == hostelId && currentAllocation.room_id) {
                if (!roomIds.includes(Number(currentAllocation.room_id))) {
                    rooms.push({
                        id: Number(currentAllocation.room_id),
                        room_number: currentAllocation.current_room_number,
                        cost_per_bed: currentAllocation.current_room_cost,
                        available_beds: 1
                    });
                }
            }

            if (rooms.length === 0) {
                roomSelect.innerHTML = '<option value="">No rooms available</option>';
                return;
            }

            rooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = `Room ${r.room_number} (Fee: $${r.cost_per_bed})`;
                roomSelect.appendChild(opt);
            });

            roomSelect.disabled = false;

            if (selectRoomId) {
                roomSelect.value = selectRoomId;
                loadAvailableBedsForEdit(selectRoomId, selectBedId);
            }
        });
}

function loadAvailableBedsForEdit(roomId, selectBedId = null) {
    const bedSelect = document.getElementById('edit_bed_id');
    bedSelect.innerHTML = '<option value="">Loading available beds...</option>';
    bedSelect.disabled = true;

    if (!roomId) return;

    fetch(`/admin/hostel/available-beds/${roomId}`)
        .then(res => res.json())
        .then(beds => {
            bedSelect.innerHTML = '<option value="">Select Bed</option>';

            const bedIds = beds.map(b => b.id);

            if (currentAllocation && currentAllocation.room_id == roomId && currentAllocation.bed_id) {
                if (!bedIds.includes(Number(currentAllocation.bed_id))) {
                    beds.push({
                        id: Number(currentAllocation.bed_id),
                        bed_number: currentAllocation.current_bed_number,
                        status: 'occupied'
                    });
                }
            }

            if (beds.length === 0) {
                bedSelect.innerHTML = '<option value="">No available beds</option>';
                return;
            }

            beds.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.id;
                opt.textContent = b.bed_number + (b.status === 'occupied' ? ' (Current)' : '');
                bedSelect.appendChild(opt);
            });

            bedSelect.disabled = false;

            if (selectBedId) {
                bedSelect.value = selectBedId;
            }
        });
}

// ==========================================
// CLIENT-SIDE LIVE DATATABLE SEARCH & PAGINATION
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('allocations-table');
    if (!table) return;
    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    const originalRows = Array.from(tbody.querySelectorAll('tr'));

    if (originalRows.length === 1 && originalRows[0].querySelector('td[colspan]')) {
        return;
    }

    let filteredRows = [...originalRows];
    let currentPage = 1;
    let pageSize = 30;

    const searchInput = document.getElementById('dt-search');
    const lengthSelect = document.getElementById('dt-length');
    const infoSpan = document.getElementById('dt-info');
    const paginationUl = document.getElementById('dt-pagination');

    function filterAndPaginate() {
        const query = (searchInput.value || '').toLowerCase().trim();

        filteredRows = originalRows.filter(row => {
            if (!query) return true;

            const cellsText = Array.from(row.querySelectorAll('td'))
                .map(td => td.textContent.toLowerCase())
                .join(' ');

            return cellsText.includes(query);
        });

        const sizeVal = lengthSelect.value;
        pageSize = sizeVal === 'all' ? filteredRows.length : parseInt(sizeVal, 10);

        const totalRows = filteredRows.length;
        const maxPages = Math.max(1, Math.ceil(totalRows / pageSize));

        if (currentPage > maxPages) {
            currentPage = maxPages;
        }

        const startIndex = (currentPage - 1) * pageSize;
        const endIndex = Math.min(startIndex + pageSize, totalRows);
        const pageRows = filteredRows.slice(startIndex, endIndex);

        tbody.innerHTML = '';
        if (totalRows === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td colspan="8" class="text-center py-5 text-muted">
                <i class="fa-solid fa-magnifying-glass fs-3 mb-2 d-block text-secondary"></i>
                No matching allocations found for "${query}"
            </td>`;
            tbody.appendChild(tr);
        } else {
            pageRows.forEach((row, idx) => {
                const indexTd = row.querySelector('td:first-child');
                if (indexTd) {
                    indexTd.textContent = startIndex + idx + 1;
                }
                tbody.appendChild(row);
            });
        }

        if (totalRows === 0) {
            infoSpan.textContent = 'Showing 0 to 0 of 0 entries';
        } else {
            infoSpan.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} entries` +
                (totalRows < originalRows.length ? ` (filtered from ${originalRows.length} total entries)` : '');
        }

        renderPagination(maxPages);
    }

    function renderPagination(maxPages) {
        paginationUl.innerHTML = '';

        if (maxPages <= 1) return;

        // Prev Button
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
        prevLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                filterAndPaginate();
            }
        });
        paginationUl.appendChild(prevLi);

        // Page Number Buttons
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(maxPages, startPage + 4);

        for (let p = startPage; p <= endPage; p++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === p ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${p}</a>`;
            li.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = p;
                filterAndPaginate();
            });
            paginationUl.appendChild(li);
        }

        // Next Button
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === maxPages ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < maxPages) {
                currentPage++;
                filterAndPaginate();
            }
        });
        paginationUl.appendChild(nextLi);
    }

    searchInput.addEventListener('input', function() {
        currentPage = 1;
        filterAndPaginate();
    });

    lengthSelect.addEventListener('change', function() {
        currentPage = 1;
        filterAndPaginate();
    });

    filterAndPaginate();
});
</script>
@endsection
