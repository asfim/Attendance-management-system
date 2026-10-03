@extends('layouts.auth')

@section('content')
<div class="mb-5">
    <h3 class="text-white mb-2" style="font-weight: 700; font-size: 2rem;">Welcome Back</h3>
    <p class="text-secondary" style="font-size: 1rem;">Enter your credentials to access your dashboard.</p>
</div>

@if(session('status'))
    <div class="alert border-0 text-white bg-success bg-opacity-25 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; border-left: 4px solid #10b981 !important;">
        <i class="fa-solid fa-circle-check fs-5 me-3 text-success"></i>
        <div>{{ session('status') }}</div>
    </div>
@endif

@if(session('info'))
    <div class="alert border-0 text-white bg-info bg-opacity-25 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; border-left: 4px solid #0ea5e9 !important;">
        <i class="fa-solid fa-circle-info fs-5 me-3 text-info"></i>
        <div>{{ session('info') }}</div>
    </div>
@endif

@if($errors->any())
    <div class="alert border-0 text-white bg-danger bg-opacity-25 d-flex align-items-center mb-4" role="alert" style="border-radius: 12px; border-left: 4px solid #ef4444 !important;">
        <i class="fa-solid fa-triangle-exclamation fs-5 me-3 text-danger"></i>
        <div>{{ $errors->first() }}</div>
    </div>
@endif

<form action="{{ route('login') }}" method="POST">
    @csrf
    
    <div class="mb-4 position-relative">
        <label for="email" class="form-label text-light fs-6 fw-semibold mb-2">Email</label>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary position-absolute" style="left: 0; z-index: 5; height: 100%; display: flex; align-items: center; padding-left: 18px;"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control form-control-custom" style="padding-left: 45px;" placeholder="name@company.com" value="{{ old('email') }}" required autofocus>
        </div>
    </div>
    
    <div class="mb-5 position-relative">
        <div class="d-flex justify-content-between mb-2 align-items-center">
            <label for="password" class="form-label text-light fs-6 fw-semibold m-0">Password</label>
            <a href="{{ route('password.request') }}" class="text-decoration-none" style="color: #38bdf8; font-size: 0.9rem; transition: color 0.3s;" onmouseover="this.style.color='#7dd3fc'" onmouseout="this.style.color='#38bdf8'">Forgot password?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-transparent border-0 text-secondary position-absolute" style="left: 0; z-index: 5; height: 100%; display: flex; align-items: center; padding-left: 18px;"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control form-control-custom pe-5" style="padding-left: 45px;" placeholder="••••••••" required>
            <button class="btn text-secondary border-0 position-absolute" type="button" style="right: 0; z-index: 5; height: 100%; display: flex; align-items: center; padding-right: 18px;" onclick="togglePassword()">
                <i class="fa-regular fa-eye" id="togglePasswordIcon"></i>
            </button>
        </div>
    </div>

    <button type="submit" class="btn btn-custom w-100 mb-4 d-flex justify-content-center align-items-center gap-2">
        <span>Sign In</span>
        <i class="fa-solid fa-arrow-right"></i>
    </button>
    
    <div class="text-center mb-4">
        <span class="text-secondary" style="font-size: 0.95rem;">New to Attendify? </span>
        <a href="{{ route('install.wizard') }}" class="text-decoration-none fw-semibold" style="color: #818cf8; font-size: 0.95rem; transition: color 0.3s;" onmouseover="this.style.color='#a5b4fc'" onmouseout="this.style.color='#818cf8'">Run setup</a>
    </div>
</form>

<div class="mt-4 pt-4 border-top text-center" style="border-color: rgba(255,255,255,0.05) !important;">
    <span class="text-secondary d-block mb-3" style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;">Demo Access</span>
    <div class="d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-sm text-light fw-medium" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'" onclick="quickFill('admin@school.com', 'admin123')">Admin</button>
        <button type="button" class="btn btn-sm text-light fw-medium" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'" onclick="quickFill('teacher@school.com', 'teacher123')">Teacher</button>
        <button type="button" class="btn btn-sm text-light fw-medium" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.03)'" onclick="quickFill('student@school.com', 'student123')">Student</button>
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
