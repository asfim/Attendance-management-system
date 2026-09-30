@extends('layouts.app', ['title' => 'Due Report - EduERP', 'header' => 'Due Report'])

@section('content')
<div class="row g-3">
    <!-- Filter Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 glass-card">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="mb-1 fw-bold text-primary"><i class="fa-solid fa-filter me-2"></i>Filter Due Report</h5>
                    <p class="text-secondary mb-0" style="font-size: 0.85rem;">Select a class to generate the report</p>
                </div>
                <form action="{{ route('admin.fees.due-report') }}" method="GET" class="d-flex gap-2">
                    <select name="class_id" class="form-select border shadow-sm rounded-3" style="min-width: 200px;" required>
                        <option value="">-- Select Class --</option>
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" {{ isset($selectedClassId) && $selectedClassId == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary rounded-3 shadow-sm px-4"><i class="fa-solid fa-search me-2"></i>Generate</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Results Card -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent p-4 border-bottom d-flex justify-content-between align-items-center d-print-none">
                <h5 class="mb-0 fw-bold"><i class="fa-solid fa-file-invoice-dollar text-danger me-2"></i>Outstanding Dues</h5>
                @if (isset($selectedClassId) && $dueStudents->count() > 0)
                    <button class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="fa-solid fa-print me-2"></i>Print Report</button>
                @endif
            </div>
            
            <div class="card-body p-0">
                @if (!isset($selectedClassId))
                    <div class="p-5 text-center text-secondary">
                        <i class="fa-solid fa-hand-holding-dollar opacity-25 mb-3" style="font-size: 4rem;"></i>
                        <h5 class="fw-bold text-dark">No Class Selected</h5>
                        <p class="mb-0">Please select a class from the filter above to generate the due report.</p>
                    </div>
                @elseif ($dueStudents->count() === 0)
                    <div class="p-5 text-center text-success">
                        <i class="fa-regular fa-face-smile opacity-50 mb-3" style="font-size: 4rem;"></i>
                        <h5 class="fw-bold">All Clear!</h5>
                        <p class="mb-0 text-secondary">There are no outstanding dues for this class.</p>
                    </div>
                @else
                    @include('admin.reports.partials.print_header', [
                        'title' => 'Outstanding Dues Report',
                        'subtitle' => 'Class: ' . ($classes->where('id', $selectedClassId)->first()->name ?? 'N/A')
                    ])
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-secondary" style="font-size: 0.85rem;">
                                <tr>
                                    <th class="ps-4">Student</th>
                                    <th>Roll No</th>
                                    <th>Phone</th>
                                    <th class="text-end">Total Billed</th>
                                    <th class="text-end">Total Paid</th>
                                    <th class="text-end pe-4 text-danger">Total Due</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dueStudents as $student)
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $student->name }}</td>
                                        <td>{{ $student->roll_no }}</td>
                                        <td>{{ $student->phone }}</td>
                                        <td class="text-end">৳{{ number_format($student->totalBilled, 0) }}</td>
                                        <td class="text-end text-success">৳{{ number_format($student->totalPaid, 0) }}</td>
                                        <td class="text-end pe-4 text-danger fw-bold">৳{{ number_format($student->totalDue, 0) }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.fees.collection', ['class_id' => $selectedClassId, 'student_id' => $student->id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-hand-holding-dollar me-1"></i> Collect
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end ps-4">Grand Totals:</td>
                                    <td class="text-end">৳{{ number_format($dueStudents->sum('totalBilled'), 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($dueStudents->sum('totalPaid'), 0) }}</td>
                                    <td class="text-end pe-4 text-danger fs-6">৳{{ number_format($dueStudents->sum('totalDue'), 0) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @include('admin.reports.partials.print_footer')
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body { background: #fff !important; }
        .sidebar, .navbar-custom, .d-print-none, .glass-card, td:last-child, th:last-child, header, footer, .breadcrumb { display: none !important; }
        .main-content { margin-left: 0 !important; padding-top: 0 !important; width: 100% !important; }
        .card { box-shadow: none !important; border: none !important; }
        .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
        .table th, .table td { border: 1px solid #dee2e6 !important; padding: 0.5rem !important; color: #000 !important; }
    }
</style>
@endpush
@endsection
