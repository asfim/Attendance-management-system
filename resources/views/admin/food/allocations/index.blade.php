@extends('layouts.app')

@section('title', 'Food Allocations')

@section('content')
<div class="container-fluid px-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800">
                <i class="fa-solid fa-users-viewfinder text-primary me-2"></i>Food Allocations
            </h2>
            <p class="text-muted mb-0">List of all food allocations for students</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#allocateFoodModal">
                <i class="fa-solid fa-plus me-1"></i> Allocate Food
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4 border-0 rounded-4" id="allocations-card">
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
                    <span class="input-group-text border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="dt-search" class="form-control ps-0 border-start-0" placeholder="Search allocations..." style="border-radius: 0 6px 6px 0;">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="allocations-table">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4" style="width: 50px;">#</th>
                            <th>Student</th>
                            <th>Class/Section</th>
                            <th>Food Plan</th>
                            <th>Monthly Fee</th>
                            <th>Start Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allocations as $index => $alloc)
                            <tr>
                                <td class="ps-4 text-muted fs-8">{{ $index + 1 }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="ms-2">
                                            <div class="fw-bold">{{ $alloc->studentProfile->user->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">Adm: {{ $alloc->studentProfile->admission_no ?? '-' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $alloc->studentProfile->schoolClass->name ?? '-' }} ({{ $alloc->studentProfile->section->name ?? '-' }})<br>
                                    <small class="text-muted">Roll: {{ $alloc->studentProfile->roll_no ?? '-' }}</small>
                                </td>
                                <td>
                                    <span class="fw-semibold">{{ $alloc->foodPlan->name ?? 'N/A' }}</span>
                                </td>
                                <td>৳{{ number_format($alloc->monthly_fee, 2) }}</td>
                                <td>{{ $alloc->start_date ? \Carbon\Carbon::parse($alloc->start_date)->format('d M Y') : '-' }}</td>
                                <td class="text-center">
                                    @if($alloc->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-20 fs-8">Active</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border fs-8">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        @if($alloc->status == 'active')
                                            <form action="{{ route('admin.food.allocations.release', $alloc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this food allocation? Future months will not be billed.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Cancel/Release Food">
                                                    <i class="fa-solid fa-ban"></i> Cancel
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.food.allocations.update', $alloc->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Re-activate Food">
                                                    <i class="fa-solid fa-rotate-left"></i> Re-activate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-users fa-3x mb-3" style="opacity: 0.2"></i>
                                    <h5>No Food Allocations Found</h5>
                                    <p>Assign food plans to students to see them here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3 bg-white border-0">
            <span class="fs-8 text-muted" id="dt-info">Showing 0 to 0 of 0 entries</span>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm m-0" id="dt-pagination">
                    <!-- Pagination buttons dynamically rendered via JS -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Allocate Food Modal -->
<div class="modal fade" id="allocateFoodModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.food.allocations.allocate') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-utensils me-2 text-primary"></i>Allocate Food to Student
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
                                <i class="fa-solid fa-users me-1"></i><span id="alloc_student_count_badge">{{ count($students ?? []) }}</span> unallocated students found
                            </small>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Select Food Plan <span class="text-danger">*</span></label>
                                <select name="food_plan_id" class="form-select" required>
                                    <option value="">Select Plan</option>
                                    @foreach($foodPlans as $plan)
                                        <option value="{{ $plan->id }}">
                                            {{ $plan->name }} (৳{{ number_format($plan->monthly_fee, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold fs-7">Effective Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Allocate Food</button>
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
</script>

<script>
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
            tr.innerHTML = `<td colspan="9" class="text-center py-5 text-muted">
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
            infoSpan.textContent = `Showing ${startIndex + 1} to ${endIndex} of ${totalRows} entries`;
        }

        renderPagination(maxPages);
    }

    function renderPagination(maxPages) {
        paginationUl.innerHTML = '';

        if (maxPages <= 1) return;

        const createLi = (text, page, isDisabled = false, isActive = false) => {
            const li = document.createElement('li');
            li.className = `page-item ${isDisabled ? 'disabled' : ''} ${isActive ? 'active' : ''}`;
            const a = document.createElement('a');
            a.className = 'page-link';
            a.href = 'javascript:void(0)';
            a.textContent = text;
            if (!isDisabled) {
                a.onclick = () => {
                    currentPage = page;
                    filterAndPaginate();
                };
            }
            li.appendChild(a);
            return li;
        };

        paginationUl.appendChild(createLi('Prev', currentPage - 1, currentPage === 1));

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(maxPages, currentPage + 2);

        if (startPage > 1) {
            paginationUl.appendChild(createLi('1', 1));
            if (startPage > 2) {
                paginationUl.appendChild(createLi('...', null, true));
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            paginationUl.appendChild(createLi(i, i, false, i === currentPage));
        }

        if (endPage < maxPages) {
            if (endPage < maxPages - 1) {
                paginationUl.appendChild(createLi('...', null, true));
            }
            paginationUl.appendChild(createLi(maxPages, maxPages));
        }

        paginationUl.appendChild(createLi('Next', currentPage + 1, currentPage === maxPages));
    }

    if (searchInput) searchInput.addEventListener('input', () => {
        currentPage = 1;
        filterAndPaginate();
    });
    
    if (lengthSelect) lengthSelect.addEventListener('change', () => {
        currentPage = 1;
        filterAndPaginate();
    });

    filterAndPaginate();
});
</script>
@endsection
