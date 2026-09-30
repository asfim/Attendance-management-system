@extends('layouts.app', ['header' => $reportTitle])

@section('content')
    <div class="d-flex justify-content-end mb-4 d-print-none">
        <button onclick="window.print()" class="btn btn-outline-secondary shadow-sm"><i class="fa-solid fa-print me-2"></i>Print Report</button>
    </div>

    @include('admin.reports.partials.print_header', [
        'title' => 'Fees Report',
        'subtitle' => 'Category: ' . $categoryName . (request('class_id') ? ' &mdash; Class: ' . ($classes->where('id', request('class_id'))->first()->name ?? '') : '')
    ])

    <div class="row g-4 mb-4">
        <!-- Summary Cards -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 summary-box">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fa-solid fa-money-bill-wave text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-6">Total Billed</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalAmount, 2) }} ৳</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 summary-box">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fa-solid fa-hand-holding-dollar text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-6">Total Paid</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalPaid, 2) }} ৳</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 summary-box">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fa-solid fa-file-invoice-dollar text-danger fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1 fs-6">Total Due</p>
                        <h4 class="mb-0 fw-bold">{{ number_format($totalDue, 2) }} ৳</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm rounded-4 summary-box">
        <div class="card-header bg-transparent border-0 py-3 d-print-none">
            <h5 class="mb-3 fw-bold"><i class="fa-solid fa-list text-primary me-2"></i> {{ $categoryName }} Details</h5>
            <form action="{{ url()->current() }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search name, roll, reg no..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="class_id" id="class_id" class="form-select">
                        <option value="">Select Class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="section_id" id="section_id" class="form-select">
                        <option value="">Select Section</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}" title="Due Date">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100 mb-1 mb-md-0" type="submit" style="max-width: 120px;"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                    @if(request('search') || request('class_id') || request('section_id') || request('date'))
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary"><i class="fa-solid fa-times me-1"></i> Clear</a>
                    @endif
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 custom-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Student</th>
                            <th>Class (Section)</th>
                            <th>Invoice #</th>
                            <th>Due Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            @php
                                $student = $item->invoice->studentProfile ?? null;
                                $user = $student->user ?? null;
                                $class = $student->schoolClass ?? null;
                                $itemDue = $item->amount - $item->paid_amount;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            @if($user && $user->profile_picture)
                                                <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile" class="rounded-circle w-100 h-100" style="object-fit: cover;">
                                            @else
                                                <span class="text-secondary fw-bold">{{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->name ?? 'N/A' }}</div>
                                            <div class="text-muted small">
                                                ID: {{ $student->student_id ?? 'N/A' }} 
                                                @if($student->roll_no) | Roll: {{ $student->roll_no }} @endif
                                                @if($student->admission_no) | Reg: {{ $student->admission_no }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{ $class->name ?? 'N/A' }} 
                                    @if($student && $student->section)
                                        ({{ $student->section->name }})
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.fees.invoices.show', $item->invoice_id) }}" class="text-decoration-none">
                                        #{{ $item->invoice->invoice_number ?? $item->invoice_id }}
                                    </a>
                                </td>
                                <td>
                                    @if($item->due_date)
                                        {{ \Carbon\Carbon::parse($item->due_date)->format('d M, Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">{{ number_format($item->amount, 2) }}</td>
                                <td class="text-end text-success fw-semibold">{{ number_format($item->paid_amount, 2) }}</td>
                                <td class="text-end text-danger fw-semibold">{{ number_format($itemDue, 2) }}</td>
                                <td class="text-center">
                                    @if ($item->status == 'paid')
                                        <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check me-1"></i> Paid</span>
                                    @elseif ($item->status == 'partial')
                                        <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-half-stroke me-1"></i> Partial</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-clock me-1"></i> Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-folder-open fs-1 text-secondary mb-3 opacity-50"></i>
                                    <p class="mb-0">No fee records found for {{ $categoryName }}.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($items->hasPages())
                <div class="px-4 py-3 border-top d-print-none">
                    {{ $items->links('pagination::bootstrap-5') }}
                </div>
            @endif
            @include('admin.reports.partials.print_footer')
        </div>
    </div>

    <style>
        .summary-box {
            background-color: var(--bs-body-bg);
            transition: all 0.3s ease;
        }
        [data-bs-theme="dark"] .summary-box {
            background-color: #1e1e2d;
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .custom-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: var(--bs-secondary);
        }
        [data-bs-theme="dark"] .custom-table th {
            background-color: #1a1a27;
            border-bottom-color: #2b2b40;
        }
        [data-bs-theme="dark"] .custom-table td {
            border-bottom-color: #2b2b40;
        }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
        .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }

        @media print {
            body { background-color: #fff !important; }
            .sidebar, .navbar-custom, .d-print-none, header, footer, .breadcrumb { display: none !important; }
            .main-content { margin-left: 0 !important; padding-top: 0 !important; width: 100% !important; }
            .summary-box { border: 1px solid #000 !important; box-shadow: none !important; }
            .custom-table th, .custom-table td { color: #000 !important; border-color: #000 !important; }
            .badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section_id');
            const classes = @json($classes);

            function populateSections() {
                const classId = classSelect.value;
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                
                const selectedClass = classes.find(c => c.id == classId);
                if (selectedClass && selectedClass.sections) {
                    selectedClass.sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        if (section.id == '{{ request("section_id") }}') {
                            option.selected = true;
                        }
                        sectionSelect.appendChild(option);
                    });
                }
            }

            classSelect.addEventListener('change', populateSections);

            if (classSelect.value) {
                populateSections();
            }
        });
    </script>
@endsection
