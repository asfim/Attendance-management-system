@extends('layouts.app')

@section('content')
<div class="row g-4 mb-4">
    <!-- Student Details -->
    <div class="col-lg-4">
        <div class="card glass-card p-4 border-0 text-center">
            @if($student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" class="rounded-circle mx-auto mb-3 object-fit-cover shadow-sm border border-secondary border-opacity-25" style="width: 80px; height: 80px;" alt="Profile">
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 80px; height: 80px; font-size: 2rem; font-weight: 700;">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            @endif
            <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
            <p class="text-secondary mb-3">{{ $student->schoolClass->name }} | Section: {{ $student->section->name }}</p>
            <span class="badge bg-light text-dark fw-bold border mb-3">Roll: {{ $student->roll_no }}</span>
            <div class="border-top border-light border-opacity-10 pt-3 text-start">
                <div class="mb-2"><span class="text-secondary fw-semibold">Admission No:</span> <span class="float-end">{{ $student->admission_no }}</span></div>
                <div class="mb-2"><span class="text-secondary fw-semibold">Blood Group:</span> <span class="float-end">{{ $student->blood_group ?? 'N/A' }}</span></div>
                <div><span class="text-secondary fw-semibold">Session:</span> <span class="float-end">{{ $student->academicSession->name }}</span></div>
            </div>
        </div>
    </div>

    <!-- Active Noticeboard -->
    <div class="col-lg-8">
        <div class="card glass-card p-4 border-0" style="min-height: 290px;">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-bullhorn text-primary me-2"></i>Active Announcements</h5>
            <div class="list-group list-group-flush">
                @forelse($notices as $notice)
                    <div class="list-group-item bg-transparent px-0 border-light border-opacity-10 py-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1 fw-semibold text-secondary">{{ $notice->title }}</h6>
                            <small class="text-muted">{{ $notice->published_at->format('M d, Y') }}</small>
                        </div>
                        <p class="mb-0 text-muted fs-7">{{ $notice->content }}</p>
                    </div>
                @empty
                    <p class="text-muted text-center my-4">No active notices found.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Fee Invoices -->
    <div class="col-md-6">
        <div class="card glass-card p-4 border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-file-invoice-dollar text-success me-2"></i>Recent Invoices</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Inv Number</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentInvoices as $inv)
                            <tr>
                                <td>{{ $inv->invoice_number }}</td>
                                <td>${{ number_format($inv->grand_total, 2) }}</td>
                                <td>
                                    <span class="badge bg-opacity-10 text-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'unpaid' ? 'danger' : 'warning') }} bg-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'unpaid' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($inv->status !== 'paid')
                                        <a href="{{ route('student.fees.pay', $inv->id) }}" class="btn btn-sm btn-success"><i class="fa-solid fa-credit-card me-1"></i>Pay Now</a>
                                    @else
                                        <span class="text-success"><i class="fa-solid fa-circle-check"></i> Paid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No recent invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Library Book Issues -->
    <div class="col-md-6">
        <div class="card glass-card p-4 border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-book-open text-info me-2"></i>Borrowed Library Books</h5>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($booksIssued as $issue)
                            <tr>
                                <td>{{ $issue->book->title }}</td>
                                <td>{{ $issue->issue_date->format('M d, Y') }}</td>
                                <td>{{ $issue->due_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No active books borrowed.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
