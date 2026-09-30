@extends('layouts.auth')

@section('content')
<h5 class="text-center text-white mb-3" style="font-weight: 500;">Reset Password</h5>
<p class="text-secondary text-center mb-4 fs-7">Enter your email address and we'll send you a link to reset your password.</p>

@if(session('status'))
    <div class="alert alert-success border-0 text-white bg-success bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 text-white bg-danger bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST">
    @csrf
    
    <div class="mb-4">
        <label for="email" class="form-label text-light fs-6">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control form-control-custom ps-5" placeholder="name@school.com" required autofocus>
        </div>
    </div>

    <button type="submit" class="btn btn-custom w-100 mb-3">Send Reset Link</button>
    
    <div class="text-center">
        <a href="{{ route('login') }}" class="text-decoration-none text-primary fs-7" style="color: #818cf8 !important;"><i class="fa-solid fa-arrow-left me-2"></i>Back to login</a>
    </div>
</form>
@endsection
