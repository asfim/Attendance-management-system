@extends('layouts.auth')

@section('content')
<h5 class="text-center text-white mb-4" style="font-weight: 500;">Sign in to your account</h5>

@if(session('status'))
    <div class="alert alert-success border-0 text-white bg-success bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info border-0 text-white bg-info bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 text-white bg-danger bg-opacity-25" role="alert" style="border-radius: 12px;">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ $errors->first() }}
    </div>
@endif

<form action="{{ route('login') }}" method="POST">
    @csrf
    
    <div class="mb-3">
        <label for="email" class="form-label text-light fs-6">Email Address</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control form-control-custom ps-5" placeholder="name@school.com" value="{{ old('email') }}" required autofocus>
        </div>
    </div>
    
    <div class="mb-4">
        <div class="d-flex justify-content-between mb-2">
            <label for="password" class="form-label text-light fs-6 m-0">Password</label>
            <a href="{{ route('password.request') }}" class="text-decoration-none text-primary fs-7" style="color: #818cf8 !important;">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary" style="margin-right: -40px; z-index: 5;"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control form-control-custom ps-5 pe-5" placeholder="••••••••" required>
            <button class="btn text-secondary border-0" type="button" style="margin-left: -45px; z-index: 5; padding-right: 15px;" onclick="togglePassword()">
                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-custom w-100 mb-3"><i class="fa-solid fa-right-to-bracket me-2"></i>Login</button>
    
    <div class="text-center">
        <span class="text-secondary fs-7">First time using EduERP? </span>
        <a href="{{ route('install.wizard') }}" class="text-decoration-none text-primary fs-7" style="color: #818cf8 !important;">Run installation</a>
    </div>
</form>

<div class="mt-4 pt-3 border-top border-light border-opacity-10 text-center">
    <span class="text-secondary fs-7 d-block mb-2">Demo Quick-Fill:</span>
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-light text-secondary border-light border-opacity-20 fs-7" onclick="quickFill('admin@school.com', 'admin123')">Admin</button>
        <button type="button" class="btn btn-sm btn-outline-light text-secondary border-light border-opacity-20 fs-7" onclick="quickFill('teacher@school.com', 'teacher123')">Teacher</button>
        <button type="button" class="btn btn-sm btn-outline-light text-secondary border-light border-opacity-20 fs-7" onclick="quickFill('student@school.com', 'student123')">Student</button>
    </div>
</div>

<script>
    function quickFill(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }

    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
