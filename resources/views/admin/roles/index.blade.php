@extends('layouts.app')

@section('content')
<div class="row">
    <!-- Roles List -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pt-4 pb-0 px-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-user-shield me-2 text-primary"></i>Roles & Permissions</h5>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Create Role</a>
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Role Name</th>
                                <th>Display Name</th>
                                <th>Permissions Configured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $role->name }}</span>
                                    </td>
                                    <td class="fw-semibold">{{ $role->display_name }}</td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $role->permissions_count }} Permissions</span>
                                        @if($role->name === 'super_admin')
                                            <span class="ms-2 text-muted fs-7">(Bypasses all checks)</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-light" title="Manage Permissions">
                                                <i class="fa-solid fa-sliders text-primary"></i> Manage
                                            </a>
                                            @if(!in_array($role->name, ['super_admin', 'admin', 'teacher', 'student', 'parent']))
                                                <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fa-solid fa-trash"></i></button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Add Permission -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h6 class="fw-bold m-0"><i class="fa-solid fa-key me-2 text-warning"></i>Register New Permission</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted fs-7 mb-4">Register a new permission string that can be assigned to roles. Use snake_case for the internal name.</p>
                <form action="{{ route('admin.roles.permissions.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Internal Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. view_students" required>
                        <div class="form-text fs-7">Used in code: <code class="bg-dark px-1 rounded text-light">hasPermission('view_students')</code></div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small">Display Name</label>
                        <input type="text" name="display_name" class="form-control" placeholder="e.g. View Students" required>
                    </div>
                    <button type="submit" class="btn btn-secondary w-100"><i class="fa-solid fa-plus me-2"></i>Add Permission</button>
                </form>

                <hr class="my-4 border-secondary opacity-25">
                <h6 class="fw-bold mb-3 fs-7 text-uppercase text-muted">Currently Registered ({{ $permissions->count() }})</h6>
                <div class="d-flex flex-wrap gap-1" style="max-height: 250px; overflow-y: auto;">
                    @foreach($permissions as $perm)
                        <span class="badge bg-light text-dark border fw-normal" title="{{ $perm->name }}">{{ $perm->display_name }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
