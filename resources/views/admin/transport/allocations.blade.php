@extends('layouts.app')

@section('title', 'Transport Allocations')

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-users text-primary me-2"></i>Transport Allocations
            </h2>
            <p class="text-muted mb-0">List of all active transport allocations</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#allocateTransportModal">
                <i class="fa-solid fa-plus me-1"></i> Allocate Transport
            </button>
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

    <div class="card shadow mb-4 border-0" style="border-radius: 15px;" id="allocations-card">
        <div class="card-header d-flex justify-content-between align-items-center py-3 px-4" style="border-radius: 15px 15px 0 0;">
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
                    <span class="input-group-text border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="dt-search" class="form-control ps-0 border-start-0" placeholder="Search allocations..." style="border-radius: 0 6px 6px 0;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="allocations-table">
                    <thead class="bg-light fs-7 text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Student</th>
                            <th>Route & Stop</th>
                            <th class="text-center">Monthly Fee (৳)</th>
                            <th class="text-center">Effective From</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocations as $index => $allocation)
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td class="px-3 py-3">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $allocation->studentProfile->photoUrl() }}" class="rounded-circle me-3" width="40" height="40" alt="Student Photo">
                                        <div>
                                            <div class="fw-bold fs-7 text-dark-emphasis">{{ $allocation->studentProfile->user->name }}</div>
                                            <div class="text-muted fs-8">Roll: {{ $allocation->studentProfile->roll_no }} | Class: {{ $allocation->studentProfile->schoolClass->name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <div class="fw-bold fs-7">
                                        <i class="fa-solid fa-route text-primary me-1"></i> {{ $allocation->route->route_name }}
                                    </div>
                                    <div class="text-muted fs-8">
                                        @if($allocation->stop)
                                            <i class="fa-solid fa-map-pin text-info me-1"></i> {{ $allocation->stop->stop_name }}
                                        @else
                                            <i class="fa-solid fa-map-pin text-muted me-1"></i> No specific stop
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3 text-center fs-7">
                                    <span class="fw-bold text-success">৳{{ number_format($allocation->monthly_fee, 2) }}</span>
                                </td>
                                <td class="py-3 text-center text-muted fs-7">
                                    {{ $allocation->effective_from ? $allocation->effective_from->format('d M, Y') : 'N/A' }}
                                </td>
                                <td class="py-3 text-center">
                                    @if($allocation->status == 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">
                                            <i class="fa-solid fa-circle-check me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border fs-8">
                                            Released
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editAllocationModal{{ $allocation->id }}" title="Edit Transport Allocation">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        @if($allocation->status == 'active')
                                            <form action="{{ route('admin.transport.allocations.release', $allocation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to release/cancel this transport allocation? Future months will not be billed.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Release/Cancel Transport">
                                                    <i class="fa-solid fa-bus-slash"></i> Release
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.transport.allocations.update', $allocation->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Re-activate Transport">
                                                    <i class="fa-solid fa-rotate-left"></i> Re-activate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users fa-3x mb-3" style="opacity: 0.2"></i>
                                    <h5>No Transport Allocations Found</h5>
                                    <p>Assign transport to students from their profile.</p>
                                </td>
                            </tr>
                        @endforelse
                        <tr id="emptyRow" style="display: none;">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-magnifying-glass fa-3x mb-3" style="opacity: 0.2"></i>
                                <h5>No Matching Allocations Found</h5>
                                <p class="mb-0">Try searching for a different name, class, route, or stop.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white" style="border-radius: 0 0 15px 15px !important; border-top: 1px solid #dee2e6;">
            <span class="fs-8 text-muted" id="dt-info">Showing 0 to 0 of 0 entries</span>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm m-0" id="dt-pagination">
                    <!-- Pagination buttons dynamically rendered via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Allocate Transport Modal -->
<div class="modal fade" id="allocateTransportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.transport.allocate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-user-check me-2 text-primary"></i>Allocate Transport to Student
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-semibold fs-7 mb-0">Select Student(s) <span class="text-danger">*</span></label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="select_all_students" onclick="toggleSelectAllStudents(this.checked)">
                                    <label class="form-check-label fs-8 text-secondary" for="select_all_students" style="user-select: none;">Select All</label>
                                </div>
                            </div>
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-body-tertiary border-end-0">
                                    <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                                </span>
                                <input type="text" id="alloc_student_search" class="form-control border-start-0 ps-0" placeholder="Type student name, roll, or admission no..." oninput="filterAllocStudentSelectOptions(this.value)">
                            </div>
                            <div id="alloc_student_list_container" class="border rounded p-3 mb-2" style="max-height: 250px; overflow-y: auto;  border-radius: 8px;">
                                @forelse($students as $st)
                                    <div class="form-check d-flex align-items-center mb-2 student-list-item">
                                        <input class="form-check-input me-3 student-checkbox" type="checkbox" name="student_profile_ids[]" value="{{ $st->id }}" id="student_check_{{ $st->id }}">
                                        <label class="form-check-label d-flex align-items-center cursor-pointer w-100" for="student_check_{{ $st->id }}" style="user-select: none; cursor: pointer;">
                                            <img src="{{ $st->photoUrl() }}" class="rounded-circle me-3" width="36" height="36" alt="Student Photo" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($st->user?->name ?? 'Student') }}&background=random&color=fff'">
                                            <div>
                                                <span class="fw-semibold d-block" style="font-size: 0.9rem;">{{ $st->user?->name }}</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">Roll: {{ $st->roll_no }} | Class: {{ $st->schoolClass->name ?? 'N/A' }}</span>
                                            </div>
                                        </label>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-3">No active unallocated students found.</div>
                                @endforelse
                            </div>
                            <small class="text-muted fs-8 mt-1 d-block">
                                <i class="fa-solid fa-users me-1"></i><span id="alloc_student_count_badge">{{ count($students) }}</span> unallocated students found
                            </small>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Select Route <span class="text-danger">*</span></label>
                                <select name="route_id" id="alloc_route_id" class="form-select" onchange="loadStopsForAllocTransport(this.value)" required>
                                    <option value="">Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{ $route->id }}">
                                            {{ $route->route_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Select Stop <span class="text-danger">*</span></label>
                                <select name="stop_id" id="alloc_stop_id" class="form-select" onchange="updateAllocTransportFee()" required disabled>
                                    <option value="">Select Stop</option>
                                </select>
                            </div>

                            <input type="hidden" name="monthly_fee" id="alloc_monthly_fee" value="0.00">

                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Effective From <span class="text-danger">*</span></label>
                                <input type="date" name="effective_from" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate Transport</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterAllocStudentSelectOptions(searchText) {
    const search = searchText.toLowerCase();
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

    document.getElementById('alloc_student_count_badge').textContent = count;
}

function toggleSelectAllStudents(isChecked) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        const item = cb.closest('.student-list-item');
        if (item.style.display !== 'none') {
            cb.checked = isChecked;
        }
    });
}

function loadStopsForAllocTransport(routeId) {
    const stopSelect = document.getElementById('alloc_stop_id');
    const feeInput = document.getElementById('alloc_monthly_fee');

    stopSelect.innerHTML = '<option value="">Loading stops...</option>';
    stopSelect.disabled = true;

    if (!routeId) {
        feeInput.value = '0.00';
        return;
    }

    feeInput.value = '0.00';

    fetch(`/admin/transport/route-stops/${routeId}`)
        .then(res => res.json())
        .then(stops => {
            if (stops.length === 0) {
                stopSelect.innerHTML = '<option value="">No stops defined for this route</option>';
                stopSelect.disabled = true;
                return;
            }

            stopSelect.innerHTML = '<option value="">Select Stop</option>';
            stops.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.setAttribute('data-fee', s.additional_fee);
                opt.textContent = `${s.stop_name} (Fee: ৳${parseFloat(s.additional_fee).toFixed(2)})`;
                stopSelect.appendChild(opt);
            });
            stopSelect.disabled = false;
        });
}

function updateAllocTransportFee() {
    const stopSelect = document.getElementById('alloc_stop_id');
    const feeInput = document.getElementById('alloc_monthly_fee');

    if (stopSelect.selectedIndex > 0) {
        const stopOption = stopSelect.options[stopSelect.selectedIndex];
        const fee = parseFloat(stopOption.getAttribute('data-fee') || 0);
        feeInput.value = fee.toFixed(2);
    } else {
        feeInput.value = '0.00';
    }
}
</script>

@foreach($allocations as $allocation)
<!-- Edit Transport Allocation Modal -->
<div class="modal fade" id="editAllocationModal{{ $allocation->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.transport.allocations.update', $allocation->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-pen me-2 text-primary"></i>Edit Transport Allocation
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7 text-secondary">Student</label>
                        <input type="text" class="form-control" value="{{ $allocation->studentProfile->user->name ?? 'Deleted Student' }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Route <span class="text-danger">*</span></label>
                        <select name="route_id" id="edit_route_id_{{ $allocation->id }}" class="form-select" onchange="loadStopsForEditTransport({{ $allocation->id }}, this.value)" required>
                            <option value="">Select Route</option>
                            @foreach($routes as $route)
                                <option value="{{ $route->id }}" {{ $allocation->route_id == $route->id ? 'selected' : '' }}>
                                    {{ $route->route_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Select Stop <span class="text-danger">*</span></label>
                        <select name="stop_id" id="edit_stop_id_{{ $allocation->id }}" class="form-select" onchange="updateEditTransportFee({{ $allocation->id }})" required>
                            <option value="">Select Stop</option>
                            @if($allocation->route)
                                @foreach($allocation->route->stops as $stop)
                                    <option value="{{ $stop->id }}" data-fee="{{ $stop->additional_fee }}" {{ $allocation->stop_id == $stop->id ? 'selected' : '' }}>
                                        {{ $stop->stop_name }} (Fee: ৳{{ number_format($stop->additional_fee, 2) }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <input type="hidden" name="monthly_fee" id="edit_monthly_fee_{{ $allocation->id }}" value="{{ $allocation->monthly_fee }}">

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-7">Effective From <span class="text-danger">*</span></label>
                        <input type="date" name="effective_from" class="form-control" value="{{ $allocation->effective_from ? $allocation->effective_from->format('Y-m-d') : date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
function loadStopsForEditTransport(allocId, routeId) {
    const stopSelect = document.getElementById(`edit_stop_id_${allocId}`);
    const feeInput = document.getElementById(`edit_monthly_fee_${allocId}`);

    stopSelect.innerHTML = '<option value="">Loading stops...</option>';
    stopSelect.disabled = true;

    if (!routeId) {
        feeInput.value = '0.00';
        return;
    }

    feeInput.value = '0.00';

    fetch(`/admin/transport/route-stops/${routeId}`)
        .then(res => res.json())
        .then(stops => {
            if (stops.length === 0) {
                stopSelect.innerHTML = '<option value="">No stops defined for this route</option>';
                stopSelect.disabled = true;
                return;
            }

            stopSelect.innerHTML = '<option value="">Select Stop</option>';
            stops.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.id;
                opt.setAttribute('data-fee', s.additional_fee);
                opt.textContent = `${s.stop_name} (Fee: ৳${parseFloat(s.additional_fee).toFixed(2)})`;
                stopSelect.appendChild(opt);
            });
            stopSelect.disabled = false;
        });
}

function updateEditTransportFee(allocId) {
    const stopSelect = document.getElementById(`edit_stop_id_${allocId}`);
    const feeInput = document.getElementById(`edit_monthly_fee_${allocId}`);

    if (stopSelect.selectedIndex > 0) {
        const stopOption = stopSelect.options[stopSelect.selectedIndex];
        const fee = parseFloat(stopOption.getAttribute('data-fee') || 0);
        feeInput.value = fee.toFixed(2);
    } else {
        feeInput.value = '0.00';
    }
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

    // Check if table only has empty row
    if (originalRows.length === 1 && originalRows[0].querySelector('td[colspan]')) {
        return;
    }

    let filteredRows = originalRows.filter(row => row.id !== 'emptyRow');
    let currentPage = 1;
    let pageSize = 30;

    const searchInput = document.getElementById('dt-search');
    const lengthSelect = document.getElementById('dt-length');
    const infoSpan = document.getElementById('dt-info');
    const paginationUl = document.getElementById('dt-pagination');

    function filterAndPaginate() {
        const query = (searchInput.value || '').toLowerCase().trim();

        filteredRows = originalRows.filter(row => {
            if (row.id === 'emptyRow') return false;
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
            const tr = document.getElementById('emptyRow');
            if (tr) {
                tr.style.display = '';
                tbody.appendChild(tr);
            } else {
                const trFallback = document.createElement('tr');
                trFallback.innerHTML = `<td colspan="7" class="text-center py-5 text-muted">
                    <i class="fa-solid fa-magnifying-glass fs-3 mb-2 d-block text-secondary"></i>
                    No matching allocations found for "${query}"
                </td>`;
                tbody.appendChild(trFallback);
            }
        } else {
            // Hide the empty search results row
            const tr = document.getElementById('emptyRow');
            if (tr) tr.style.display = 'none';

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
                (totalRows < (originalRows.length - 1) ? ` (filtered from ${originalRows.length - 1} total entries)` : '');
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

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            filterAndPaginate();
        });
    }

    if (lengthSelect) {
        lengthSelect.addEventListener('change', function() {
            currentPage = 1;
            filterAndPaginate();
        });
    }

    filterAndPaginate();
});
</script>
@endsection
