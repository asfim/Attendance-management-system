@extends('layouts.app')

@section('content')
<style>
    .student-list-toolbar {
        border-top: 1px solid var(--bs-border-color);
        padding-top: 1rem;
    }

    .student-live-search {
        position: relative;
        width: min(100%, 340px);
    }

    .student-live-search .form-control {
        min-height: 42px;
        padding-left: 2.65rem;
        padding-right: 5.5rem;
        border-radius: 12px !important;
        border-color: var(--bs-border-color);
        background-color: var(--bs-body-bg);
    }

    .student-live-search .search-icon {
        position: absolute;
        z-index: 2;
        top: 50%;
        left: 1rem;
        color: var(--bs-primary);
        transform: translateY(-50%);
        pointer-events: none;
    }

    .student-live-search .search-count {
        position: absolute;
        top: 50%;
        right: 2.75rem;
        color: var(--bs-secondary-color);
        font-size: 0.72rem;
        transform: translateY(-50%);
        pointer-events: none;
    }

    .student-live-search .clear-search {
        position: absolute;
        z-index: 2;
        top: 50%;
        right: 0.65rem;
        padding: 0;
        color: var(--bs-secondary-color);
        background: transparent;
        border: 0;
        transform: translateY(-50%);
    }

    .student-live-search .clear-search:hover {
        color: var(--bs-danger);
    }

    @media (max-width: 575px) {
        .student-live-search {
            width: 100%;
        }
    }
</style>
<div class="card glass-card border-0 p-4 mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <h5 class="fw-bold m-0">Student Registry</h5>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_student'))
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Admit New Student</a>
        @endif
    </div>

    <!-- Filters Form -->
    <form action="{{ route('admin.students.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <select name="shift_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Shifts</option>
                @foreach($shifts as $shift)
                    <option value="{{ $shift->id }}" {{ request('shift_id') == $shift->id ? 'selected' : '' }}>{{ $shift->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="session_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                @foreach($sessions as $sess)
                    <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>{{ $sess->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search name, email, or admission no..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Filter</button>
        </div>
    </form>

    <!-- Student Table Container -->
    <div class="student-list-toolbar d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-3 gap-3">
        <h6 class="mb-0 fw-bold text-muted">Student List</h6>
        <div class="student-live-search" role="search">
            <i class="fa-solid fa-bolt search-icon" aria-hidden="true"></i>
            <input type="search" id="instantSearch" class="form-control shadow-none" placeholder="Search visible students..." aria-label="Search visible students">
            <span id="instantSearchCount" class="search-count" aria-live="polite"></span>
            <button type="button" id="clearInstantSearch" class="clear-search d-none" aria-label="Clear live search" title="Clear search">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Admission No</th>
                        {{-- <th>Roll</th> --}}
                        <th>Name</th>
                        <th>Class/Section</th>
                        <th>Shift</th>
                        {{-- <th>Parent Details</th> --}}
                        <th>Status</th>
                        <th>Hostel</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $st)
                        <tr class="student-data-row">
                            <td>
                                @if ($st->photo_path)
                                    <img src="{{ asset('storage/' . $st->photo_path) }}" alt="Photo"
                                        class="rounded-circle object-fit-cover border border-secondary border-opacity-25"
                                        style="width: 36px; height: 36px;">
                                @else
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold"
                                        style="width: 36px; height: 36px;">
                                        {{ strtoupper(substr($st->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark fw-bold border">{{ $st->admission_no }}</span></td>
                            {{-- <td>{{ $st->roll_no }}</td> --}}
                            <td>
                                <div class="fw-semibold">{{ $st->user->name }}</div>
                                <div class="text-muted fs-7">{{ $st->user->email }}</div>
                            </td>
                            <td>{{ $st->schoolClass->name }} - {{ $st->section->name }}</td>
                            <td>{{ $st->shift ? $st->shift->name : '-' }}</td>
                            {{-- <td>
                                @if($st->parent)
                                    <div>{{ $st->parent->user->name }}</div>
                                    <div class="text-muted fs-7">{{ $st->parent->phone }}</div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td> --}}
                            <td>
                                <span class="badge bg-opacity-10 text-{{ $st->status === 'active' ? 'success' : 'danger' }} bg-{{ $st->status === 'active' ? 'success' : 'danger' }}">
                                    {{ ucfirst($st->status) }}
                                </span>
                            </td>
                            <td>
                                @if($st->hostelAllocation)
                                    @if($st->hostelAllocation->status === 'active')
                                        <span class="badge bg-success bg-opacity-10 text-success"><i class="fa-solid fa-bed me-1"></i>Allocated</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-person-walking-arrow-right me-1"></i>Released</span>
                                    @endif
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_student'))
                                    <a href="{{ route('admin.students.show', $st->id) }}" class="btn btn-sm btn-light" title="View Student Profile & ID Card">
                                        <i class="fa-solid fa-eye text-primary"></i>
                                    </a>
                                    @endif
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_student'))
                                    <a href="{{ route('admin.students.edit', $st->id) }}" class="btn btn-sm btn-light"><i class="fa-solid fa-pencil"></i></a>
                                    @endif
                                    {{--
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_student'))
                                    <button type="button" class="btn btn-sm btn-light text-danger" onclick="confirmDelete({{ $st->id }})"><i class="fa-solid fa-trash"></i></button>
                                    @endif
                                    --}}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No students found matching filters.</td>
                        </tr>
                    @endforelse
                    <tr id="instantSearchEmpty" class="d-none">
                        <td colspan="9" class="text-center py-4 text-muted">No visible students match your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{ $students->appends(request()->query())->links() }}


</div>

<!-- Modal deletion forms -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('instantSearch');
        const clearSearch = document.getElementById('clearInstantSearch');
        const resultCount = document.getElementById('instantSearchCount');
        const emptyState = document.getElementById('instantSearchEmpty');
        const rows = Array.from(document.querySelectorAll('.student-data-row'));

        function updateLiveSearch() {
            const query = searchInput.value.trim().toLowerCase();
            let visibleCount = 0;

            rows.forEach(function (row) {
                const matches = !query || row.textContent.toLowerCase().includes(query);
                row.classList.toggle('d-none', !matches);
                if (matches) visibleCount++;
            });

            clearSearch.classList.toggle('d-none', !query);
            resultCount.textContent = query ? visibleCount + ' found' : '';
            emptyState.classList.toggle('d-none', !query || visibleCount > 0);
        }

        searchInput.addEventListener('input', updateLiveSearch);
        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            updateLiveSearch();
            searchInput.focus();
        });
    });

    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this student profile?")) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/students/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    }

    function printCard(studentId) {
        const printArea = document.getElementById('printArea-' + studentId);

        // Open a new printable window with styling preserved
        const printWindow = window.open('', '_blank', 'width=800,height=600');
        printWindow.document.write('<ht' + 'ml><he' + 'ad><ti' + 'tle>Print Student ID Card</ti' + 'tle>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">');
        printWindow.document.write('<sty' + 'le>');
        printWindow.document.write('body { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; background: #fff; margin: 0; padding: 20px; font-family: "Inter", sans-serif; }');
        printWindow.document.write('.print-card-section { display: flex; flex-direction: column; gap: 20px; align-items: center; }');
        printWindow.document.write('.student-id-card { page-break-inside: avoid; -webkit-print-color-adjust: exact; print-color-adjust: exact; }');
        printWindow.document.write('</sty' + 'le></he' + 'ad><bo' + 'dy>');
        printWindow.document.write('<div class="print-card-section">' + printArea.innerHTML + '</div>');
        printWindow.document.write('</bo' + 'dy></ht' + 'ml>');
        printWindow.document.write('<scr' + 'ipt>window.onload = function() { setTimeout(function() { window.print(); window.close(); }, 500); }</scr' + 'ipt>');
        printWindow.document.close();
    }
</script>
@endsection
