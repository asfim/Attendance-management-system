@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0 px-4">
                <h5 class="fw-bold m-0"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Create New Role</h5>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Internal Role Name (Staff Type) <span class="text-danger">*</span></label>
                            <select name="name" class="form-select" required onchange="document.getElementById('display_name_input').value = this.options[this.selectedIndex].text;">
                                <option value="" disabled selected>Select Staff Type / Role</option>
                                @if(isset($roles))
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->display_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="form-text fs-7">This links the role to the staff type.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small">Display Name <span class="text-danger">*</span></label>
                            <input type="text" name="display_name" id="display_name_input" class="form-control" placeholder="e.g. Head Librarian" required>
                            <div class="form-text fs-7">Visible to users.</div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3"><i class="fa-solid fa-shield me-2 text-warning"></i>Assign Initial Permissions</h6>
                    
                    @if(count($groupedPermissions) > 0)
                        <div class="row g-4 mb-4">
                            @foreach($groupedPermissions as $groupName => $perms)
                                <div class="col-md-6">
                                    <div class="card border border-secondary border-opacity-25 shadow-sm h-100">
                                        <div class="card-header bg-light bg-opacity-10 py-2">
                                            <h6 class="fw-bold m-0 text-primary">{{ $groupName }}</h6>
                                        </div>
                                        <div class="card-body p-3">
                                            @foreach($perms as $perm)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}">
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
                            <i class="fa-solid fa-circle-info me-2"></i> No permissions registered yet.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
