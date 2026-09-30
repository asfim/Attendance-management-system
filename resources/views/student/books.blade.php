@extends('layouts.app')

@section('title', 'My Library Books')

@section('content')
<style>
.book-card {
    border-radius: 16px;
    border: 1px solid var(--bs-border-color);
    transition: transform 0.2s, box-shadow 0.2s;
}
.book-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
}
.book-icon-shape {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-10">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold m-0"><i class="fa-solid fa-book-open text-info me-2"></i>My Library Books</h5>
                <p class="text-muted fs-7 mb-0">View all your borrowed books, due dates, and borrowing history</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-arrow-left me-1"></i>Back
            </a>
        </div>

        {{-- Summary Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #0ea5e9 !important;">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Currently Borrowed</div>
                    <div class="fw-bold fs-5 text-info">{{ $activeIssued->count() }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #22c55e !important;">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Returned</div>
                    <div class="fw-bold fs-5 text-success">{{ $returned->count() }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #6366f1 !important;">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Total History</div>
                    <div class="fw-bold fs-5 text-primary">{{ $bookIssues->count() }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm p-3" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
                    <div class="text-muted fs-8 text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Total Fine</div>
                    <div class="fw-bold fs-5 text-danger">৳{{ number_format($totalFine, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Active Borrowed Books Grid --}}
        <div class="mb-4">
            <h6 class="fw-bold text-secondary mb-3"><i class="fa-solid fa-book-bookmark text-info me-2"></i>Currently Issued Books</h6>

            @if($activeIssued->count())
                <div class="row g-3">
                    @foreach($activeIssued as $issue)
                        @php
                            $isOverdue = $issue->due_date && \Carbon\Carbon::now()->greaterThan($issue->due_date) && strtolower($issue->status) !== 'returned';
                        @endphp
                        <div class="col-md-6">
                            <div class="card book-card shadow-sm h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start gap-3 mb-3">
                                        <div class="book-icon-shape {{ $isOverdue ? 'bg-danger bg-opacity-10 text-danger' : 'bg-info bg-opacity-10 text-info' }}">
                                            <i class="fa-solid fa-book"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <h6 class="fw-bold text-dark-emphasis mb-1">{{ $issue->book->title ?? 'Book Title' }}</h6>
                                                @if($isOverdue)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 fs-8">
                                                        <i class="fa-solid fa-triangle-exclamation me-1"></i>Overdue
                                                    </span>
                                                @else
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fs-8">
                                                        <i class="fa-solid fa-clock me-1"></i>Issued
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted fs-7">
                                                <i class="fa-solid fa-user-pen me-1"></i>Author: {{ $issue->book->author ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2 pt-2 border-top fs-8">
                                        <div class="col-6">
                                            <span class="text-muted">ISBN:</span>
                                            <span class="fw-semibold text-dark-emphasis">{{ $issue->book->isbn ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span class="text-muted">Rack No:</span>
                                            <span class="fw-semibold text-dark-emphasis">{{ $issue->book->rack_no ?? 'N/A' }}</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted">Issued Date:</span>
                                            <span class="fw-semibold text-dark-emphasis">{{ $issue->issue_date ? $issue->issue_date->format('d M, Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="col-6 text-end">
                                            <span class="text-muted">Due Date:</span>
                                            <span class="fw-semibold {{ $isOverdue ? 'text-danger fw-bold' : 'text-primary' }}">
                                                {{ $issue->due_date ? $issue->due_date->format('d M, Y') : 'N/A' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                    <div class="card-body p-4 text-center">
                        <i class="fa-solid fa-book-open text-muted mb-2 d-block" style="font-size: 2rem; opacity: 0.3;"></i>
                        <h6 class="text-muted mb-0">No active books currently borrowed.</h6>
                    </div>
                </div>
            @endif
        </div>

        {{-- Borrowing History Table --}}
        @if($bookIssues->count())
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-transparent border-bottom py-3 px-4">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Borrowing History Log</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="fs-7 text-secondary border-bottom">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Book Title</th>
                                    <th>Author</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Return Date</th>
                                    <th class="text-end">Fine</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="fs-7">
                                @foreach($bookIssues as $i => $item)
                                    @php
                                        $statusClass = match(strtolower($item->status)) {
                                            'returned' => 'bg-success bg-opacity-10 text-success border border-success border-opacity-25',
                                            'overdue'  => 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25',
                                            default    => 'bg-info bg-opacity-10 text-info border border-info border-opacity-25',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                        <td class="fw-bold text-dark-emphasis">
                                            <i class="fa-solid fa-book text-info me-2 fs-8"></i>{{ $item->book->title ?? 'Book' }}
                                        </td>
                                        <td class="text-muted">{{ $item->book->author ?? 'N/A' }}</td>
                                        <td class="text-muted">{{ $item->issue_date ? $item->issue_date->format('d M, Y') : '-' }}</td>
                                        <td class="text-muted">{{ $item->due_date ? $item->due_date->format('d M, Y') : '-' }}</td>
                                        <td class="text-muted">{{ $item->return_date ? $item->return_date->format('d M, Y') : '-' }}</td>
                                        <td class="text-end fw-semibold {{ $item->fine_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                            ৳{{ number_format($item->fine_amount, 2) }}
                                        </td>
                                        <td class="text-center pe-4">
                                            <span class="badge fs-8 {{ $statusClass }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
