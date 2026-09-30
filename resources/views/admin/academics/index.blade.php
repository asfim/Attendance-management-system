@extends('layouts.app')

@section('content')
<style>
.glass-card .section-title {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #0f172a;
    font-weight: 700;
    border-bottom: 2px solid var(--bs-border-color);
    padding-bottom: 10px;
    margin-bottom: 14px;
}
[data-bs-theme="dark"] .glass-card .section-title {
    color: #f3f4f6;
}
.glass-card .form-control, 
.glass-card .form-select {
    border: 1px solid var(--bs-border-color);
    background-color: #ffffff;
    color: #0f172a;
}
[data-bs-theme="dark"] .glass-card .form-control, 
[data-bs-theme="dark"] .glass-card .form-select {
    background-color: #1f2937;
    color: #f3f4f6;
}
.glass-card .form-control:focus, 
.glass-card .form-select:focus {
    border-color: var(--bs-border-color);
    box-shadow: 0 0 0 0.15rem rgba(109, 109, 109, 0.15);
}
.glass-card option {
    background-color: #ffffff;
    color: #0f172a;
}
[data-bs-theme="dark"] .glass-card option {
    background-color: #1f2937;
    color: #f3f4f6;
}
.table th {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
}
[data-bs-theme="dark"] .table th {
    color: #9ca3af;
}
.table td {
    font-size: 0.85rem;
    color: #1e293b;
}
</style>

<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold m-0">Academic Configurator</h5>
            <p class="text-muted fs-7 mb-0">Manage classes, sections, sessions and schedules</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEFT: Academic Sessions Config -->
        <div class="col-md-5">
            <div class="card glass-card border p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-calendar-days me-2"></i>Academic Sessions</div>
                
                <!-- Add Session Form -->
                <form action="{{ route('admin.academics.sessions.store') }}" method="POST" class="mb-3 pb-3 border-bottom border-light">
                    @csrf
                    <!-- Auto-set active session to true -->
                    <input type="hidden" name="is_active" value="1">
                    <div class="row g-2 align-items-center">
                        <div class="col-8">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Session Name (e.g. 2026-2027)" required>
                        </div>
                        <div class="col-4">
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-plus me-1"></i>Add Session</button>
                        </div>
                    </div>
                </form>

                <!-- Academic Registration Settings Toggle -->
                <form action="{{ route('admin.academics.settings.update') }}" method="POST" class="mb-4 pb-3 border-bottom border-light">
                    @csrf
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-7 text-secondary fw-semibold">Show Session in Student Admission</span>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="show_session_in_registration" name="show_session_in_registration" value="1" {{ $showSessionInReg == '1' ? 'checked' : '' }} onchange="this.form.submit()">
                        </div>
                    </div>
                </form>

                <!-- Sessions List -->
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--bs-border-color);">
                                <th>Session Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sessions as $sess)
                                <tr style="border-bottom: 1px solid var(--bs-border-color);">
                                    <td class="fw-bold" style="color: inherit;"><i class="fa-solid fa-calendar-check text-primary me-2"></i>{{ $sess->name }}</td>
                                    <td>
                                        @if($sess->is_active)
                                            <span class="badge bg-success text-white">Active</span>
                                        @else
                                            <span class="badge bg-secondary text-white">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center text-muted py-4">No academic sessions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: Classes & Sections Config -->
        <div class="col-md-7">
            <div class="card glass-card border p-4 h-100">
                <div class="section-title"><i class="fa-solid fa-graduation-cap me-2"></i>Classes &amp; Sections Registry</div>

                <!-- Forms to Add Class or Section -->
                <div class="row g-3 mb-4 pb-4 border-bottom border-light">
                    <!-- Add Class Form -->
                    <div class="col-md-6 border-end border-light">
                        <h6 class="fs-7 fw-bold text-primary mb-2">Create New Class</h6>
                        <form action="{{ route('admin.academics.classes.store') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="name" class="form-control form-control-sm" placeholder="Class Name (e.g. Class 6)" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fa-solid fa-plus me-1"></i>Add Class</button>
                        </form>
                    </div>

                    <!-- Add Section Form -->
                    <div class="col-md-6">
                        <h6 class="fs-7 fw-bold text-success mb-2">Create New Section</h6>
                        <form action="{{ route('admin.academics.sections.store') }}" method="POST">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <select name="class_id" class="form-select form-select-sm" required>
                                        <option value="">Select Class</option>
                                        @foreach($classes as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-3">
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="Name" required>
                                </div>
                                <div class="col-3">
                                    <input type="number" name="capacity" class="form-control form-control-sm" placeholder="Cap" required min="1">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success w-100 text-white"><i class="fa-solid fa-plus me-1"></i>Add Section</button>
                        </form>
                    </div>
                </div>

                <!-- Classes List -->
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--bs-border-color);">
                                <th>Class Name</th>
                                <th>Assigned Sections</th>
                                <th class="text-end">Total Capacity</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classes as $c)
                                <tr style="border-bottom: 1px solid var(--bs-border-color);">
                                    <td class="fw-bold" style="color: inherit;"><i class="fa-solid fa-school text-success me-2"></i>{{ $c->name }}</td>
                                    <td>
                                        @forelse($c->sections as $sec)
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 me-1" style="font-size:0.7rem;">
                                                {{ $sec->name }} ({{ $sec->capacity }})
                                            </span>
                                        @empty
                                            <span class="text-muted fs-8">No sections created</span>
                                        @endforelse
                                    </td>
                                    <td class="text-end fw-bold" style="color: inherit;">{{ $c->sections->sum('capacity') }}</td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.academics.classes.destroy', $c->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to delete this class? This will only work if there are no sections in it.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">No classes registered.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
