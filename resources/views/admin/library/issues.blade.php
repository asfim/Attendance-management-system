@extends('layouts.app')

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold m-0">Book Issues & Returns</h5>
            <p class="text-muted fs-7 mb-0">Record book issues to students/teachers and process returns.</p>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
            <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Please fix the following errors:</h6>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Tab Navigation -->
    <ul class="nav nav-pills gap-2 mb-4 p-2 bg-light bg-opacity-10 rounded-3 shadow-sm border border-light border-opacity-10" id="issuesTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-2 fw-bold" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab" aria-controls="list" aria-selected="true">
                <i class="fa-solid fa-list me-2"></i>Issued Books Records
            </button>
        </li>
        <li class="nav-item" role="presentation">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('create_book'))
<button class="nav-link rounded-2 fw-bold" id="add-tab" data-bs-toggle="tab" data-bs-target="#add" type="button" role="tab" aria-controls="add" aria-selected="false">
                <i class="fa-solid fa-plus-circle me-2"></i>Issue Book to User
            </button>
            @endif
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="issuesTabContent">
        <!-- Tab 1: Issues List -->
        <div class="tab-pane fade show active" id="list" role="tabpanel" aria-labelledby="list-tab">
            <div class="card glass-card border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold text-primary m-0"><i class="fa-solid fa-list me-2"></i>Issued Books Records</h6>
                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ count($issues) }} Records</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Book & ISBN</th>
                                        <th>Borrower</th>
                                        <th>Dates</th>
                                        <th>Fine</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($issues as $issue)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $issue->book->title }}</div>
                                                <div class="text-muted fs-7">ISBN: <code>{{ $issue->book->isbn }}</code></div>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $issue->user->name }}</div>
                                                <div class="text-muted fs-7">{{ $issue->user->role->display_name }}</div>
                                            </td>
                                            <td>
                                                <div class="fs-7"><strong>Issued:</strong> {{ \Carbon\Carbon::parse($issue->issue_date)->format('M d, Y') }}</div>
                                                <div class="fs-7"><strong>Due:</strong> {{ \Carbon\Carbon::parse($issue->due_date)->format('M d, Y') }}</div>
                                                @if($issue->return_date)
                                                    <div class="fs-7 text-success"><strong>Returned:</strong> {{ \Carbon\Carbon::parse($issue->return_date)->format('M d, Y') }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($issue->fine_amount > 0)
                                                    <span class="text-danger fw-bold">৳{{ number_format($issue->fine_amount, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($issue->status === 'returned')
                                                    <span class="badge bg-success bg-opacity-10 text-success">Returned</span>
                                                @elseif($issue->status === 'issued' && \Carbon\Carbon::parse($issue->due_date)->isPast())
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Overdue</span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning">Issued</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($issue->status === 'issued')
                                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#returnModal-{{ $issue->id }}">
                                                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Return Book
                                                    </button>
                                                @else
                                                    <span class="text-muted fs-7">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No issued book records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                </div>
            </div>
        </div>

        <!-- Tab 2: Issue Book Form -->
        <div class="tab-pane fade" id="add" role="tabpanel" aria-labelledby="add-tab">
            <div class="card glass-card border-0 p-4">
                <h6 class="fw-bold text-primary mb-4"><i class="fa-solid fa-key me-2"></i>Issue Book to User</h6>
                <form action="{{ route('admin.library.issue.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-secondary small">Select Book</label>
                                <select name="book_id" class="form-select tom-select" required>
                                    <option value="">Select Book</option>
                                    @foreach($books as $book)
                                        <option value="{{ $book->id }}" {{ $book->available_qty <= 0 ? 'disabled' : '' }}>
                                            {{ $book->title }} (Available: {{ $book->available_qty }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-secondary small">Select Borrower</label>
                                <select name="user_id" class="form-select tom-select" required>
                                    <option value="">Select User</option>
                                    @foreach($users as $usr)
                                        <option value="{{ $usr->id }}">{{ $usr->name }} @if($usr->hasRole('student') && $usr->studentProfile) [ID: {{ $usr->studentProfile->admission_no ?? $usr->studentProfile->roll_no }}] @endif ({{ $usr->role->display_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="form-label text-secondary small">Issue Date</label>
                                    <input type="date" name="issue_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label text-secondary small">Due Date</label>
                                    <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(14)->format('Y-m-d') }}" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-key me-2"></i>Issue Book</button>
                        </form>
            </div>
        </div>
    </div>
</div>

<!-- Return Modals -->
@foreach($issues as $issue)
    @if($issue->status === 'issued')
        <div class="modal fade" id="returnModal-{{ $issue->id }}" tabindex="-1" aria-hidden="true" style="text-align: left;">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-card p-4 border-0">
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-undo me-2 text-primary"></i>Return Book</h5>
                    <p class="text-muted fs-7 mb-4">Confirm return of <strong>{{ $issue->book->title }}</strong> borrowed by <strong>{{ $issue->user->name }}</strong>.</p>
                    
                    <form action="{{ route('admin.library.return', $issue->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-secondary small">Return Date</label>
                            <input type="date" name="return_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label text-secondary small">Fine Amount (if applicable)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-secondary border">৳</span>
                                <input type="number" name="fine_amount" class="form-control" value="0.00" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary w-50" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary w-50">Record Return</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.tom-select').forEach(function(el) {
            new TomSelect(el, {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });
        });
    });
</script>
@endpush
