@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold m-0"><i class="fa-solid fa-graduation-cap text-primary me-2"></i>{{ $student->user->name }} - Academic Dashboard</h5>
    <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-2"></i>Back to Parent Dashboard</a>
</div>

<div class="row g-4 mb-4">
    <!-- Student Detail card -->
    <div class="col-md-4">
        <div class="card glass-card p-4 border-0">
            <h6 class="fw-bold text-primary mb-3">Student Profile Info</h6>
            <div class="mb-2"><span class="text-secondary fw-semibold">Admission No:</span> <span class="float-end">{{ $student->admission_no }}</span></div>
            <div class="mb-2"><span class="text-secondary fw-semibold">Roll:</span> <span class="float-end">{{ $student->roll_no }}</span></div>
            <div class="mb-2"><span class="text-secondary fw-semibold">Class:</span> <span class="float-end">{{ $student->schoolClass->name }}</span></div>
            <div class="mb-2"><span class="text-secondary fw-semibold">Section:</span> <span class="float-end">{{ $student->section->name }}</span></div>
            <div class="mb-2"><span class="text-secondary fw-semibold">Gender:</span> <span class="float-end">{{ $student->gender }}</span></div>
            <div class="mb-2"><span class="text-secondary fw-semibold">Blood Group:</span> <span class="float-end">{{ $student->blood_group ?? 'N/A' }}</span></div>
            <div><span class="text-secondary fw-semibold">DOB:</span> <span class="float-end">{{ $student->dob->format('M d, Y') }}</span></div>
        </div>
    </div>

    <!-- Fee Invoices card -->
    <div class="col-md-8">
        <div class="card glass-card p-4 border-0">
            <h6 class="fw-bold text-primary mb-3">Fee Invoices & Payments</h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $inv->invoice_number }}</td>
                                <td>${{ number_format($inv->grand_total, 2) }}</td>
                                <td>${{ number_format($inv->paid_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-opacity-10 text-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'unpaid' ? 'danger' : 'warning') }} bg-{{ $inv->status === 'paid' ? 'success' : ($inv->status === 'unpaid' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($inv->status) }}
                                    </span>
                                </td>
                                <td>{{ $inv->due_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No invoices found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card glass-card border-0 p-4">
    <h6 class="fw-bold text-primary mb-3">Weekly Timetable / Class Routine</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Room</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @forelse($routine as $slot)
                    <tr>
                        <td class="fw-semibold text-secondary">{{ $slot->day_of_week }}</td>
                        <td>{{ $slot->subject->name }}</td>
                        <td>{{ $slot->staffProfile->user->name ?? 'N/A' }}</td>
                        <td>{{ $slot->classroom->room_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($slot->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($slot->end_time)->format('h:i A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No routine timetables configured for this section.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
