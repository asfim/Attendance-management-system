@extends('layouts.app')

@section('content')
<div class="card glass-card border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Teacher Registry</h5>
        <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Register New Teacher</a>
    </div>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Teacher Name</th>
                    <th>Email</th>
                    <th>Designation</th>
                    <th>Salary</th>
                    <th>Joining Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($teachers as $t)
                    <tr>
                        <td class="fw-semibold">{{ $t->user->name }}</td>
                        <td>{{ $t->user->email }}</td>
                        <td>{{ $t->designation }}</td>
                        <td>${{ number_format($t->salary, 2) }}</td>
                        <td>{{ $t->joining_date->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-opacity-10 text-success bg-success">Active</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No teachers registered yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
