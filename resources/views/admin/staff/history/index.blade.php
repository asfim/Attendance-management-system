@extends('layouts.app')

@section('content')
<div class="card glass-card border p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Attendance History</h5>
    </div>

    <!-- Filters Form -->
    <form action="{{ route('admin.staff.history.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Staff ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staffs as $staff)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $staff->employeeId() }}</span></td>
                        <td>
                            @if ($staff->photo)
                                <img src="{{ asset('storage/' . $staff->photo) }}" alt="Photo"
                                    class="rounded-circle object-fit-cover border border-secondary border-opacity-25"
                                    style="width: 36px; height: 36px;">
                            @else
                                <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold"
                                    style="width: 36px; height: 36px;">
                                    {{ strtoupper(substr($staff->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $staff->user->name }}</div>
                            <div class="text-muted fs-7">{{ $staff->user->email }}</div>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                {{ $staff->user->role->display_name ?? 'N/A' }}
                            </span>
                        </td>
                        <td>{{ $staff->department ?? 'N/A' }}</td>
                        <td>
                            <a href="{{ route('admin.staff.history.show', $staff->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> View History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No staff found matching filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        {{ $staffs->appends(request()->query())->links() }}
    </div>
</div>
@endsection
