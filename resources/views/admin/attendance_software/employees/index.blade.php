@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-users text-primary me-2"></i>Employee Management</h3>
            <p class="text-muted small mb-0">Register staff, manage Biometric (Fingerprint/Face ID), branch/dept assignments, & status</p>
        </div>
        <div>
            <a href="{{ route('admin.attendance-suite.employees.create') }}" class="btn btn-primary btn-sm rounded-pill shadow-sm">
                <i class="fa-solid fa-user-plus me-1"></i> Register New Employee
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.attendance-suite.employees.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm rounded-pill" placeholder="Search by name, ID, phone..." value="{{ $search }}">
                </div>
                <div class="col-6 col-md-3">
                    <select name="branch_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select name="department_id" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ $departmentId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" {{ $status == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ $status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-6 col-md-1">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill w-100">Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Employee Table Card -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Employee ID</th>
                        <th>Staff Profile</th>
                        <th>Branch</th>
                        <th>Department & Designation</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                        <tr>
                            <td class="fw-bold text-primary">{{ $emp->employeeId() }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $emp->photoUrl() }}" class="rounded-circle shadow-sm" width="38" height="38" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $emp->user?->name }}</div>
                                        <span class="text-muted small"><i class="fa-solid fa-phone me-1"></i>{{ $emp->phone }}</span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $emp->branchName }}</span></td>
                            <td>
                                <div class="fw-semibold text-dark mb-0">{{ $emp->departmentName }}</div>
                                <span class="text-muted small">{{ $emp->designationTitle }}</span>
                            </td>
                            <td>{{ $emp->joining_date?->format('d M, Y') ?? '--' }}</td>
                            <td>
                                @if($emp->status === 'active')
                                    <span class="badge bg-success rounded-pill px-3 py-1">Active</span>
                                @else
                                    <span class="badge bg-danger rounded-pill px-3 py-1">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <a href="{{ route('admin.attendance-suite.employees.show', $emp->id) }}" class="action-btn action-btn-primary" title="View Profile">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.attendance-suite.employees.edit', $emp->id) }}" class="action-btn action-btn-info" title="Edit Profile">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form action="{{ route('admin.attendance-suite.employees.toggle-status', $emp->id) }}" method="POST" class="d-inline m-0 p-0">
                                        @csrf
                                        <button type="submit" class="action-btn action-btn-{{ $emp->status === 'active' ? 'warning' : 'success' }}" title="Toggle Status ({{ $emp->status === 'active' ? 'Deactivate' : 'Activate' }})">
                                            <i class="fa-solid fa-power-off"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5"><i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i> No employee profiles found matching your search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($employees->hasPages())
            <div class="card-footer bg-transparent border-0 px-4 py-3">
                {{ $employees->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
