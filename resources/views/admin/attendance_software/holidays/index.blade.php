@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-calendar-day text-primary me-2"></i>Holiday & Festival Calendar</h3>
            <p class="text-muted small mb-0">Government Holidays, Company Holidays, Festival Holidays, Weekly Holidays & Custom Calendars</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('admin.attendance-suite.holidays.sync-bd') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-cloud-arrow-down me-1"></i> Auto Sync BD Holidays
                </button>
            </form>
            <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addHolidayModal">
                <i class="fa-solid fa-plus me-1"></i> Add Holiday
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Holidays Grid / List -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Holiday Name</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $h)
                        <tr>
                            <td class="fw-bold text-dark">{{ $h->date?->format('d M, Y (D)') }}</td>
                            <td><span class="fw-semibold text-primary">{{ $h->name }}</span></td>
                            <td>
                                @php
                                    $badge = match($h->type) {
                                        'government' => 'bg-danger',
                                        'company'    => 'bg-primary',
                                        'festival'   => 'bg-success',
                                        'weekly'     => 'bg-secondary',
                                        default      => 'bg-info text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $badge }} px-3 py-1 text-uppercase">{{ $h->type ?? 'COMPANY' }}</span>
                            </td>
                            <td>{{ $h->branch?->name ?? 'All Branches' }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.attendance-suite.holidays.destroy', $h->id) }}" method="POST" onsubmit="return confirm('Remove holiday?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-5">No holidays scheduled in calendar yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Add Holiday -->
<div class="modal fade" id="addHolidayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.holidays.store') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-calendar-plus text-primary me-2"></i>Add Holiday</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Holiday Title <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Independence Day / Eid-ul-Fitr" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Holiday Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="government">Government Holiday</option>
                        <option value="company">Company Holiday</option>
                        <option value="festival">Festival Holiday</option>
                        <option value="weekly">Weekly Holiday</option>
                        <option value="custom">Custom Holiday</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Holiday Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Branch Applicable</label>
                    <select name="branch_id" class="form-select">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Add Holiday</button>
            </div>
        </form>
    </div>
</div>
@endsection
