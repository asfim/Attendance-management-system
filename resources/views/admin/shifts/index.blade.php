@extends('layouts.app')

@section('title', 'Time & Shift Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title">Manage Time & Shifts</h4>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addShiftModal">
                    Add New Time Setting
                </button>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>In Time (Start)</th>
                                <th>Out Time (End)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shifts as $shift)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $shift->name }}</td>
                                <td>{{ $shift->code }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                                <td>
                                    <span class="badge {{ $shift->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($shift->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editShiftModal{{ $shift->id }}">Edit</button>
                                    <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>

                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No time settings found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@foreach($shifts as $shift)
    <!-- Edit Modal -->
    <div class="modal fade" id="editShiftModal{{ $shift->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.shifts.update', $shift->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Time Setting</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Shift Name</label>
                            <input type="text" class="form-control" name="name" value="{{ $shift->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Shift Code</label>
                            <input type="text" class="form-control" name="code" value="{{ $shift->code }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">In Time (Start)</label>
                            <input type="time" class="form-control" name="start_time" value="{{ $shift->start_time }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Out Time (End)</label>
                            <input type="time" class="form-control" name="end_time" value="{{ $shift->end_time }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active" {{ $shift->status == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ $shift->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Edit Modal -->
@endforeach

<!-- Add Modal -->
<div class="modal fade" id="addShiftModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.shifts.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Time Setting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Shift Name</label>
                        <input type="text" class="form-control" name="name" placeholder="e.g. Morning Shift" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Shift Code</label>
                        <input type="text" class="form-control" name="code" placeholder="e.g. MORN" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">In Time (Start)</label>
                        <input type="time" class="form-control" name="start_time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Out Time (End)</label>
                        <input type="time" class="form-control" name="end_time" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Add Modal -->
@endsection
