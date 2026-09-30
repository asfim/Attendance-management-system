<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation Wizard - EduERP</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .wizard-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            width: 100%;
            max-width: 600px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: rgba(255,255,255,0.1);
            z-index: 1;
            transform: translateY(-50%);
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            font-weight: 700;
            color: #94a3b8;
            transition: all 0.3s;
        }

        .step.active {
            background: #4f46e5;
            color: #fff;
            box-shadow: 0 0 15px rgba(79, 70, 229, 0.5);
        }

        .step.completed {
            background: #10b981;
            color: #fff;
        }

        .form-control-custom {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 12px;
            padding: 12px;
        }
        .form-control-custom:focus {
            background: rgba(255,255,255,0.08);
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.25);
            color: #fff;
        }

        .loader-spinner {
            display: none;
            width: 3rem;
            height: 3rem;
            border: 0.35em solid rgba(255,255,255,0.1);
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="wizard-card text-center">
        <h2 class="fw-bold mb-2"><i class="fa-solid fa-graduation-cap me-2 text-indigo-400"></i>EduERP Setup</h2>
        <p class="text-secondary mb-4">Enterprise School Management ERP Installation Wizard</p>
        
        <!-- Step Indicators -->
        <div class="step-indicator">
            <div class="step active" id="step1-indicator">1</div>
            <div class="step" id="step2-indicator">2</div>
            <div class="step" id="step3-indicator">3</div>
        </div>

        <!-- Error Notification -->
        <div class="alert alert-danger d-none" id="error-alert"></div>

        <form id="install-form">
            <!-- Step 1: Welcome & Requirements -->
            <div id="step-1">
                <h4 class="mb-3 text-start">Environment Check</h4>
                <ul class="list-group list-group-flush text-start bg-transparent mb-4">
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10 d-flex justify-content-between">
                        <span>PHP Version 8.2+ Required</span>
                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Passed</span>
                    </li>
                    <li class="list-group-item bg-transparent text-white border-light border-opacity-10 d-flex justify-content-between">
                        <span>Database connection in .env (MySQL)</span>
                        <span class="text-success"><i class="fa-solid fa-circle-check me-1"></i>Configured</span>
                    </li>
                </ul>
                <button type="button" class="btn btn-primary w-100 py-2 fs-5" onclick="nextStep(2)" style="border-radius: 12px; background: #4f46e5; border: none;">Continue to Admin Setup</button>
            </div>

            <!-- Step 2: Super Admin Account Config -->
            <div id="step-2" class="d-none">
                <h4 class="mb-4 text-start">Register Super Admin Account</h4>
                
                <div class="mb-3 text-start">
                    <label for="admin_name" class="form-label text-secondary">Full Name</label>
                    <input type="text" id="admin_name" class="form-control form-control-custom" placeholder="Principal Name" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="admin_email" class="form-label text-secondary">Email Address</label>
                    <input type="email" id="admin_email" class="form-control form-control-custom" placeholder="admin@school.com" required>
                </div>

                <div class="mb-3 text-start">
                    <label for="admin_password" class="form-label text-secondary">Password</label>
                    <input type="password" id="admin_password" class="form-control form-control-custom" placeholder="••••••••" required>
                </div>

                <div class="mb-4 text-start">
                    <label for="admin_password_confirmation" class="form-label text-secondary">Confirm Password</label>
                    <input type="password" id="admin_password_confirmation" class="form-control form-control-custom" placeholder="••••••••" required>
                </div>

                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-outline-secondary w-50 py-2" onclick="prevStep(1)" style="border-radius: 12px;">Back</button>
                    <button type="submit" class="btn btn-primary w-50 py-2" style="border-radius: 12px; background: #4f46e5; border: none;">Run Installation</button>
                </div>
            </div>

            <!-- Step 3: Installation status -->
            <div id="step-3" class="d-none">
                <h4 class="mb-3">Installing System...</h4>
                <p class="text-secondary">EduERP is building the database schema, creating default roles and permissions, and registering the Super Admin account.</p>
                <div class="loader-spinner" id="loader"></div>
                <div class="text-success fs-5 d-none" id="success-message">
                    <i class="fa-solid fa-circle-check fa-3x mb-3 text-emerald-400"></i>
                    <p>Setup Completed Successfully!</p>
                </div>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1;

        function nextStep(step) {
            document.getElementById(`step-${currentStep}`).classList.add('d-none');
            document.getElementById(`step-${step}`).classList.remove('d-none');
            document.getElementById(`step${step}-indicator`).classList.add('active');
            
            if (currentStep < step) {
                document.getElementById(`step${currentStep}-indicator`).classList.add('completed');
            }
            currentStep = step;
        }

        function prevStep(step) {
            document.getElementById(`step-${currentStep}`).classList.add('d-none');
            document.getElementById(`step-${step}`).classList.remove('d-none');
            document.getElementById(`step${currentStep}-indicator`).classList.remove('active');
            document.getElementById(`step${step}-indicator`).classList.remove('completed');
            document.getElementById(`step${step}-indicator`).classList.add('active');
            currentStep = step;
        }

        document.getElementById('install-form').addEventListener('submit', function (e) {
            e.preventDefault();
            
            const password = document.getElementById('admin_password').value;
            const confirm = document.getElementById('admin_password_confirmation').value;
            const errorAlert = document.getElementById('error-alert');

            if (password !== confirm) {
                errorAlert.classList.remove('d-none');
                errorAlert.innerText = 'Passwords do not match.';
                return;
            }

            errorAlert.classList.add('d-none');
            nextStep(3);
            
            // Show loader
            document.getElementById('loader').style.display = 'block';

            // Post Form via fetch
            fetch("{{ route('install.run') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    admin_name: document.getElementById('admin_name').value,
                    admin_email: document.getElementById('admin_email').value,
                    admin_password: password,
                    admin_password_confirmation: confirm
                })
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('loader').style.display = 'none';
                if (data.success) {
                    document.getElementById('success-message').classList.remove('d-none');
                    document.getElementById('step3-indicator').classList.add('completed');
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 2500);
                } else {
                    prevStep(2);
                    errorAlert.classList.remove('d-none');
                    errorAlert.innerText = data.message;
                }
            })
            .catch(err => {
                document.getElementById('loader').style.display = 'none';
                prevStep(2);
                errorAlert.classList.remove('d-none');
                errorAlert.innerText = 'Connection or runtime error occurred: ' + err.message;
            });
        });
    </script>
</body>
</html>
