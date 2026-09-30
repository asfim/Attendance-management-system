@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0 text-light d-flex align-items-center">
                <i class="fa-solid fa-user-group me-2"></i> Staff
                <span
                    class="badge bg-primary bg-opacity-25 text-primary ms-2 rounded-pill fs-7">{{ $totalStaff ?? 0 }}</span>
            </h4>
            <p class="text-muted fs-7 mt-1 mb-0">Teachers and non-teaching staff, with salary structure and payroll.</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_staff'))
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary text-white fw-semibold fs-7 px-3 py-2">
            <i class="fa-solid fa-plus me-1"></i> Add Staff
        </a>
        @endif
    </div>

    <div class="card glass-card border-0">
        <!-- Filter Tabs & Actions -->
        <div
            class="d-flex justify-content-between align-items-center p-3 border-bottom border-secondary border-opacity-25 flex-wrap gap-3">
            @php
                $currentRole = request('role', 'all');
                $tabs = ['all' => 'All'];
                if (isset($roles)) {
                    foreach ($roles as $r) {
                        $tabs[$r->name] = $r->display_name ?? ucfirst(str_replace('_', ' ', $r->name));
                    }
                }
            @endphp

            <div class="d-flex align-items-center gap-1 overflow-auto" style="white-space: nowrap;">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.staff.index', ['role' => $key]) }}"
                        class="btn btn-sm px-3 py-2 fw-semibold {{ $currentRole === $key ? 'btn-primary shadow-sm' : 'btn-link text-body text-decoration-none' }}"
                        style="border-radius: 8px;">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="d-flex align-items-center gap-2">
                <form id="search-form" action="{{ route('admin.staff.index') }}" method="GET" class="m-0 d-flex gap-2">
                    @if (request('role'))
                        <input type="hidden" name="role" value="{{ request('role') }}">
                    @endif
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <span class="input-group-text bg-transparent border-secondary border-opacity-50 text-muted"><i
                                class="fa-solid fa-search"></i></span>
                        <input type="text" id="search-input" name="search"
                            class="form-control bg-transparent border-secondary border-opacity-50 text-light placeholder-muted"
                            placeholder="Search name, code, designation..." value="{{ request('search') }}">
                    </div>

                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-secondary border-opacity-50 px-2 text-body" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside">
                            <i class="fa-solid fa-sliders"></i>
                        </button>
                        <div class="dropdown-menu p-3 shadow-sm" style="min-width: 220px;"
                            onclick="event.stopPropagation()">
                            <h6 class="fs-8 text-muted text-uppercase fw-bold mb-3">Columns</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-image" id="col-image"
                                    checked>
                                <label class="form-check-label fs-7" for="col-image">Image</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-staff" id="col-staff"
                                    checked>
                                <label class="form-check-label fs-7" for="col-staff">Staff</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-code" id="col-code"
                                    checked>
                                <label class="form-check-label fs-7" for="col-code">Code</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-type" id="col-type"
                                    checked>
                                <label class="form-check-label fs-7" for="col-type">Type / Designation</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-contact"
                                    id="col-contact" checked>
                                <label class="form-check-label fs-7" for="col-contact">Contact</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-basic" id="col-basic"
                                    checked>
                                <label class="form-check-label fs-7" for="col-basic">Basic Salary</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-allowance"
                                    id="col-allowance" checked>
                                <label class="form-check-label fs-7" for="col-allowance">Allowances</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input col-toggle" type="checkbox" value="col-status"
                                    id="col-status" checked>
                                <label class="form-check-label fs-7" for="col-status">Status</label>
                            </div>
                        </div>
                    </div>
                </form>

                <a href="{{ route('admin.staff.index') }}"
                    class="btn btn-sm btn-link text-muted text-decoration-none fs-7">Clear</a>

                <select class="form-select form-select-sm bg-transparent border-secondary border-opacity-50 text-light"
                    style="width: auto;">
                    <option value="15">15 / page</option>
                    <option value="50">50 / page</option>
                    <option value="100">100 / page</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0 col-image">Image</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 px-4 border-0 col-staff">Staff</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-code">Code</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-type">Type / Designation
                        </th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-contact">Contact</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-basic">Basic Salary</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-allowance">Allowances</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 col-status">Status</th>
                        <th class="text-uppercase fs-7 text-muted fw-semibold py-3 border-0 text-end px-4 col-actions">
                            Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $s)
                        <tr class="border-bottom border-secondary border-opacity-10"
                            style="transition: background-color 0.2s;">
                            <td class="px-4 py-3 col-image">
                                @if ($s->photo)
                                    <img src="{{ asset('storage/' . $s->photo) }}" alt="Photo"
                                        class="rounded-circle object-fit-cover border border-secondary border-opacity-25"
                                        style="width: 36px; height: 36px;">
                                @else
                                    <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold"
                                        style="width: 36px; height: 36px;">
                                        {{ strtoupper(substr($s->user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 col-staff">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-semibold text-body">{{ $s->user->name }}</div>
                                        <div class="text-muted fs-7">{{ $s->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted col-code">#STF-{{ str_pad($s->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="col-type">
                                <div class="text-body">{{ $s->user->role ? $s->user->role->display_name : 'Unassigned' }}
                                </div>
                                <div class="text-muted fs-7">{{ $s->designation ?? 'N/A' }}</div>
                            </td>
                            <td class="text-muted col-contact">{{ $s->phone ?? 'N/A' }}</td>
                            <td class="text-body col-basic">${{ number_format($s->salary, 2) }}</td>
                            <td class="text-body col-allowance">${{ number_format($s->allowances->sum('amount'), 2) }}
                            </td>
                            <td class="col-status">
                                <span
                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2 py-1 fs-7">Active</span>
                            </td>
                            <td class="text-end px-4 col-actions">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('view_staff'))
                                        <a href="{{ route('admin.staff.show', $s->id) }}"
                                            class="btn btn-sm btn-outline-secondary border-0 text-info p-1"
                                            title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    @endif
                                    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('edit_staff'))
                                        <a href="{{ route('admin.staff.edit', $s->id) }}"
                                            class="btn btn-sm btn-outline-secondary border-0 text-primary p-1"
                                            title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                    @endif
                                    @if (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('delete_staff'))
                                        <form action="{{ route('admin.staff.destroy', $s->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this staff member?');"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-secondary border-0 text-danger p-1"
                                                title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center justify-content-center text-muted my-4">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mb-3"
                                        style="width: 64px; height: 64px;">
                                        <i class="fa-solid fa-user-group fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold text-light mb-1">No staff found</h6>
                                    <p class="fs-7 mb-2">Try a different search or filter.</p>
                                    <a href="{{ route('admin.staff.index') }}"
                                        class="btn btn-link text-primary text-decoration-none fs-7 p-0">Clear filters</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer / Pagination -->
        <div
            class="d-flex justify-content-between align-items-center p-3 border-top border-secondary border-opacity-25 text-muted fs-7">
            <div>
                {{ $staff->firstItem() ?? 0 }}-{{ $staff->lastItem() ?? 0 }} of {{ $staff->total() ?? 0 }}
            </div>
            <div class="d-flex align-items-center gap-3">

                <div class="d-flex gap-1 ms-3">
                    <a href="{{ $staff->previousPageUrl() }}"
                        class="btn btn-sm btn-outline-secondary border-0 p-1 {{ $staff->onFirstPage() ? 'disabled' : '' }}"><i
                            class="fa-solid fa-chevron-left"></i></a>
                    @for ($i = 1; $i <= $staff->lastPage(); $i++)
                        <a href="{{ $staff->url($i) }}"
                            class="btn btn-sm {{ $staff->currentPage() == $i ? 'btn-primary text-white' : 'btn-outline-secondary border-0 text-muted' }} p-1 px-2">{{ $i }}</a>
                    @endfor
                    <a href="{{ $staff->nextPageUrl() }}"
                        class="btn btn-sm btn-outline-secondary border-0 p-1 {{ !$staff->hasMorePages() ? 'disabled' : '' }}"><i
                            class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-bg-secondary:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
        [data-bs-theme="dark"] .hover-bg-secondary:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="dark"] input::placeholder {
            color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.col-toggle');

            // Load saved column preferences on page load
            checkboxes.forEach(cb => {
                const colClass = cb.value;
                const savedState = localStorage.getItem('staff-col-' + colClass);
                
                // If there's a saved state and it's 'false', uncheck and hide
                if (savedState === 'false') {
                    cb.checked = false;
                    const elements = document.querySelectorAll('.' + colClass);
                    elements.forEach(el => el.classList.add('d-none'));
                }
                
                // Listen for changes and save to localStorage
                cb.addEventListener('change', function() {
                    const elements = document.querySelectorAll('.' + colClass);
                    if (this.checked) {
                        elements.forEach(el => el.classList.remove('d-none'));
                        localStorage.setItem('staff-col-' + colClass, 'true');
                    } else {
                        elements.forEach(el => el.classList.add('d-none'));
                        localStorage.setItem('staff-col-' + colClass, 'false');
                    }
                });
            });

            // Live search debounce
            const searchInput = document.getElementById('search-input');
            const searchForm = document.getElementById('search-form');
            let searchTimeout;

            if (searchInput && searchForm) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        searchForm.submit();
                    }, 500); // 500ms debounce
                });

                // Move cursor to end of input on load if there's a value
                if (searchInput.value) {
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                    searchInput.focus();
                }
            }
        });
    </script>
@endpush
