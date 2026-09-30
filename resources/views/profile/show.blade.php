@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card glass-card border-0 p-4">
            <h5 class="fw-bold mb-4">Manage My Profile</h5>

            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label text-secondary">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label text-secondary">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <hr class="my-4 border-light border-opacity-10">
                <h6 class="fw-semibold text-primary mb-3">Change Password (Optional)</h6>

                <div class="mb-3">
                    <label for="current_password" class="form-label text-secondary">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password if changing">
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label text-secondary">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New password">
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label text-secondary">Confirm New Password</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-floppy-disk me-2"></i>Save Changes</button>
            </form>
        </div>
    </div>
</div>
@endsection
