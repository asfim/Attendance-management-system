@extends('layouts.app')

@section('content')
<div class="card glass-card border p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold m-0">Student History</h5>
    </div>

    <!-- Filters Form -->
    <form action="{{ route('admin.students.history.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="class_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="session_id" class="form-select" onchange="this.form.submit()">
                <option value="">All Sessions</option>
                @foreach($sessions as $sess)
                    <option value="{{ $sess->id }}" {{ request('session_id') == $sess->id ? 'selected' : '' }}>{{ $sess->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search name, email, or admission no..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-magnifying-glass me-2"></i>Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Admission No</th>
                    <th>Roll</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Class/Section</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $st)
                    <tr>
                        <td><span class="badge bg-light text-dark fw-bold border">{{ $st->admission_no }}</span></td>
                        <td>{{ $st->roll_no }}</td>
                        <td>
                            @if ($st->photo_path)
                                <img src="{{ asset('storage/' . $st->photo_path) }}" alt="Photo"
                                    class="rounded-circle object-fit-cover border border-secondary border-opacity-25"
                                    style="width: 36px; height: 36px;">
                            @else
                                <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold"
                                    style="width: 36px; height: 36px;">
                                    {{ strtoupper(substr($st->user->name, 0, 1)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $st->user->name }}</div>
                            <div class="text-muted fs-7">{{ $st->user->email }}</div>
                        </td>
                        <td>{{ $st->schoolClass->name }} - {{ $st->section->name }}</td>
                        <td>
                            <a href="{{ route('admin.students.history.show', $st->id) }}" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-clock-rotate-left me-1"></i> View History
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No students found matching filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-3">
        {{ $students->appends(request()->query())->links() }}
    </div>
</div>
@endsection
