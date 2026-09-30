@extends('layouts.auth')

@section('content')
<h5 class="text-center text-white mb-3" style="font-weight: 500;">Set New Password</h5>

@if($errors->any())
    <div class="alert alert-danger border-0 text-white bg-danger bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
    </div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    
    <div class="mb-3">
        <label for="email" class="form-label text-light fs-6">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control form-control-custom ps-5" placeholder="name@school.com" value="{{ $email ?? old('email') }}" required autofocus>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label text-light fs-6">New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control form-control-custom ps-5" placeholder="••••••••" required>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label text-light fs-6">Confirm New Password</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control form-control-custom ps-5" placeholder="••••••••" required>
        </div>
    </div>

    <button type="submit" class="btn btn-custom w-100 mb-3">Update Password</button>
</form>
@endsection
