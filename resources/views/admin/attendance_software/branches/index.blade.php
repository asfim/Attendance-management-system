@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-code-branch text-primary me-2"></i>Branch & Department Management</h3>
            <p class="text-muted small mb-0">Multi-Branch support, Department hierarchy, Designations, & Employee Transfer logs</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addBranchModal">
                <i class="fa-solid fa-plus me-1"></i> Add Branch
            </button>
            <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#addDeptModal">
                <i class="fa-solid fa-plus me-1"></i> Add Department
            </button>
            <button class="btn btn-outline-secondary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#transferModal">
                <i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Employee Transfer
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-pills mb-4 gap-2" id="branchTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active rounded-pill fw-semibold" id="branches-tab" data-bs-toggle="pill" data-bs-target="#branches" type="button"><i class="fa-solid fa-building me-1"></i> Branches ({{ $branches->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill fw-semibold" id="departments-tab" data-bs-toggle="pill" data-bs-target="#departments" type="button"><i class="fa-solid fa-sitemap me-1"></i> Departments ({{ $departments->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill fw-semibold" id="designations-tab" data-bs-toggle="pill" data-bs-target="#designations" type="button"><i class="fa-solid fa-id-badge me-1"></i> Designations ({{ $designations->count() }})</button>
        </li>
        <li class="nav-item">
            <button class="nav-link rounded-pill fw-semibold" id="transfers-tab" data-bs-toggle="pill" data-bs-target="#transfers" type="button"><i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Employee Transfers</button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="branchTabContent">
        <!-- 1. Branches Tab -->
        <div class="tab-pane fade show active" id="branches">
            <div class="row g-4">
                @forelse($branches as $branch)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 h-100 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">{{ $branch->code }}</span>
                                <span class="badge bg-success rounded-pill">Active</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $branch->name }}</h5>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1"></i>{{ $branch->address ?? 'No address set' }}</p>
                            <hr class="my-2 border-light">
                            <div class="d-flex justify-content-between small text-muted">
                                <span><i class="fa-solid fa-phone me-1"></i>{{ $branch->phone ?? '--' }}</span>
                                <span><i class="fa-solid fa-users me-1"></i>{{ $branch->staff_count }} Staff</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center text-muted py-5">No branches registered yet. Click "Add Branch" above!</div>
                @endforelse
            </div>
        </div>

        <!-- 2. Departments Tab -->
        <div class="tab-pane fade" id="departments">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th>Branch</th>
                                <th>Total Staff</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $dept)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $dept->code ?? 'N/A' }}</span></td>
                                    <td class="fw-bold text-dark">{{ $dept->name }}</td>
                                    <td>{{ $dept->branch?->name ?? 'Main Office' }}</td>
                                    <td><span class="badge bg-info text-dark">{{ $dept->staff_count }} Members</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">No departments found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 3. Designations Tab -->
        <div class="tab-pane fade" id="designations">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Designation Title</th>
                                <th>Department</th>
                                <th>Total Staff</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($designations as $desig)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $desig->title }}</td>
                                    <td>{{ $desig->department?->name ?? 'General' }}</td>
                                    <td><span class="badge bg-primary">{{ $desig->staff_count }}</span></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No designations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 4. Employee Transfers Tab -->
        <div class="tab-pane fade" id="transfers">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="table-responsive p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>From Branch/Dept</th>
                                <th>To Branch/Dept</th>
                                <th>Transfer Date</th>
                                <th>Approved By</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transfers as $tr)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $tr->staff?->user?->name }} ({{ $tr->staff?->employeeId() }})</td>
                                    <td>{{ $tr->fromBranch?->name ?? 'Head Office' }} / {{ $tr->fromDepartment?->name ?? 'IT' }}</td>
                                    <td class="fw-semibold text-primary">{{ $tr->toBranch?->name ?? 'N/A' }} / {{ $tr->toDepartment?->name ?? 'N/A' }}</td>
                                    <td>{{ $tr->transfer_date?->format('d M, Y') }}</td>
                                    <td>{{ $tr->approver?->name ?? 'Admin' }}</td>
                                    <td>{{ $tr->reason ?? '--' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No transfer records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Add Branch -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.branches.store') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-building text-primary me-2"></i>Add New Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Chittagong Branch" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Branch Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. BR-CTG">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" class="form-control" placeholder="+880170000000">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="branch@company.com">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Address</label>
                    <textarea name="address" class="form-control" rows="2" placeholder="Full address"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Branch</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Add Department -->
<div class="modal fade" id="addDeptModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.departments.store') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-sitemap text-primary me-2"></i>Add Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. HR & Admin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Branch</label>
                    <select name="branch_id" class="form-select">
                        <option value="">Select Branch (Optional)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Department Code</label>
                    <input type="text" name="code" class="form-control" placeholder="e.g. HR">
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Department</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Employee Transfer -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.attendance-suite.transfers.store') }}" method="POST" class="modal-content border-0 shadow rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-arrow-right-arrow-left text-primary me-2"></i>Employee Transfer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Select Employee <span class="text-danger">*</span></label>
                    <select name="staff_profile_id" class="form-select" required>
                        <option value="">-- Choose Employee --</option>
                        @foreach($allStaff as $s)
                            <option value="{{ $s->id }}">{{ $s->user?->name }} ({{ $s->employeeId() }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Transfer To Branch</label>
                        <select name="to_branch_id" class="form-select">
                            <option value="">-- Select Branch --</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}">{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-semibold">Transfer To Department</label>
                        <select name="to_department_id" class="form-select">
                            <option value="">-- Select Department --</option>
                            @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" name="transfer_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Transfer Reason</label>
                    <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Branch expansion / Promotion"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Execute Transfer</button>
            </div>
        </form>
    </div>
</div>
@endsection
