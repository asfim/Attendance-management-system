@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-pen-to-square me-2 text-primary"></i>Edit Role & Permissions</h5>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Internal Role Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" value="{{ $role->name }}" disabled>
                            <div class="form-text fs-7">Internal name cannot be changed.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" class="form-control" value="{{ $role->display_name }}" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold m-0"><i class="fa-solid fa-shield-halved me-2 text-warning"></i>Configure Access Privileges</h6>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                            <label class="form-check-label text-secondary small" for="selectAllPermissions">Select All</label>
                        </div>
                    </div>
                    
                    @if(count($groupedPermissions) > 0)
                        <div class="row g-4 mb-4">
                            @foreach($groupedPermissions as $groupName => $perms)
                                <div class="col-md-6">
                                    <div class="card border border-secondary border-opacity-25 shadow-sm h-100">
                                        <div class="card-header bg-light bg-opacity-10 py-2 d-flex justify-content-between align-items-center">
                                            <h6 class="fw-bold m-0 text-primary">{{ $groupName }}</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            @foreach($perms as $perm)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}" {{ in_array($perm->id, $rolePermissions) ? 'checked' : '' }}>
                                                    <label class="form-check-label d-flex flex-column" style="cursor:pointer;" for="perm_{{ $perm->id }}">
                                                        <span class="fw-semibold" style="font-size: 0.85rem;">{{ $perm->display_name }}</span>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert bg-warning bg-opacity-10 text-warning border-0 mb-4">
                            <i class="fa-solid fa-circle-info me-2"></i> No permissions registered yet. Add them in the roles index.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Update Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('selectAllPermissions').addEventListener('change', function() {
        const isChecked = this.checked;
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = isChecked;
        });
    });
</script>
@endpush
@endsection
