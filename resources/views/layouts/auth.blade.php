<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Login - Attendify' }}</title>
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            margin: 0;
            overflow-x: hidden;
            color: #f8fafc;
        }

        .auth-container {
            display: flex;
            min-height: 100vh;
        }

        /* Left Panel - Visuals */
        .auth-visual {
            flex: 1;
            display: none;
            position: relative;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .auth-visual {
                display: flex;
                flex-direction: column;
                padding: 4rem;
            }
        }

        .auth-visual-content {
            position: relative;
            z-index: 10;
        }

        .auth-visual h1 {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .auth-visual p {
            font-size: 1.25rem;
            color: #94a3b8;
            max-width: 400px;
            margin-bottom: 0;
        }

        /* Abstract shapes */
        .shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.6;
            z-index: 1;
        }
        .shape-1 {
            width: 600px;
            height: 600px;
            background: rgba(14, 165, 233, 0.35); /* Sky blue */
            top: -10%;
            left: -20%;
            animation: pulse 12s infinite alternate ease-in-out;
        }
        .shape-2 {
            width: 700px;
            height: 700px;
            background: rgba(99, 102, 241, 0.35); /* Indigo */
            bottom: -20%;
            right: -10%;
            animation: pulse 15s infinite alternate-reverse ease-in-out;
        }
        .shape-3 {
            width: 400px;
            height: 400px;
            background: rgba(168, 85, 247, 0.25); /* Purple */
            top: 40%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: pulse 10s infinite alternate ease-in-out;
        }

        /* Floating Icons */
        .floating-icon {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: rgba(255, 255, 255, 0.8);
            z-index: 5;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.3);
        }
        .icon-1 {
            width: 80px; height: 80px; font-size: 2rem;
            top: 25%; right: 15%;
            animation: float 6s infinite ease-in-out;
        }
        .icon-2 {
            width: 100px; height: 100px; font-size: 2.5rem;
            top: 50%; right: 35%; /* Moved from left to right side */
            animation: float 8s infinite alternate-reverse ease-in-out;
        }
        .icon-3 {
            width: 70px; height: 70px; font-size: 1.8rem;
            bottom: 30%; right: 25%;
            animation: float 7s infinite alternate ease-in-out;
        }

        .main-icon-container {
            margin-bottom: 1.5rem;
            display: inline-block;
            position: relative;
            z-index: 10;
        }
        
        .main-icon-container i {
            font-size: 5.5rem;
            background: linear-gradient(135deg, #38bdf8, #818cf8, #c084fc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            filter: drop-shadow(0 10px 15px rgba(56, 189, 248, 0.3));
        }

        /* Right Panel - Form */
        .auth-form-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background-color: #0f172a;
            position: relative;
            z-index: 10;
        }

        @media (min-width: 992px) {
            .auth-form-wrapper {
                max-width: 600px;
                border-left: 1px solid rgba(255, 255, 255, 0.05);
                box-shadow: -20px 0 50px rgba(0,0,0,0.3);
            }
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .auth-logo {
            font-size: 2rem;
            font-weight: 800;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 2rem;
            display: inline-block;
        }

        /* Form Controls */
        .form-control-custom {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            color: #f8fafc;
            border-radius: 12px;
            padding: 14px 18px;
            transition: all 0.3s ease;
            font-weight: 400;
        }
        .form-control-custom::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        .form-control-custom:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: #38bdf8;
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
            color: #fff;
            outline: none;
        }

        .btn-custom {
            background: #fff;
            color: #0f172a;
            border: none;
            padding: 14px;
            font-weight: 700;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 255, 255, 0.2);
            color: #000;
            background: #f8fafc;
        }

        /* Animations */
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.3; }
            100% { transform: scale(1.1); opacity: 0.7; }
        }
    </style>
</head>
<body>

    <div class="auth-container">
        <!-- Left Visual Panel -->
        <div class="auth-visual">
            <!-- Glow Backdrops -->
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            
            <!-- Floating Small Glass Cards -->
            <div class="floating-icon icon-1"><i class="fa-regular fa-clock"></i></div>
            <div class="floating-icon icon-2"><i class="fa-solid fa-users-viewfinder"></i></div>
            <div class="floating-icon icon-3"><i class="fa-solid fa-calendar-check"></i></div>

            <div class="auth-visual-content">
                <!-- Top Logo for Desktop -->
                <div class="d-none d-lg-block">
                    <div class="auth-logo mb-0 text-start">
                        <i class="fa-solid fa-clipboard-user me-2"></i>Attendify
                    </div>
                </div>
            </div>
            
            <div class="auth-visual-content mt-auto mb-4">
                <div class="main-icon-container">
                    <i class="fa-solid fa-fingerprint"></i>
                </div>
                <h1>Next-Gen<br>Attendance.</h1>
                <p>Manage your workforce effortlessly with our smart, real-time tracking platform.</p>
            </div>
        </div>

        <!-- Right Form Panel -->
        <div class="auth-form-wrapper">
            <div class="auth-card">
                <!-- Mobile Logo -->
                <div class="d-lg-none text-center">
                    <div class="auth-logo">
                        <i class="fa-solid fa-clipboard-user me-2"></i>Attendify
                    </div>
                </div>
                
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
