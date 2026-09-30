<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Attendance & HR Suite' }}</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        :root,
        [data-bs-theme="light"] {
            --font-sans: 'Inter', sans-serif;
            --primary-hsl: 220, 90%, 56%;
            --primary-bg: hsl(var(--primary-hsl));
            --primary-hover: hsl(220, 90%, 46%);
            --sidebar-width: 260px;
            --navbar-height: 70px;
            --transition-speed: 0.3s;
            --bs-border-color: #e2e8f0;
            --bs-border-color-rgb: 226, 232, 240;
            --bs-border-color-translucent: rgba(226, 232, 240, 0.5);
        }

        [data-bs-theme="dark"] {
            --primary-hsl: 210, 100%, 66%;
            --primary-bg: hsl(var(--primary-hsl));
            --primary-hover: hsl(210, 100%, 76%);
            --bs-body-bg: #0f172a;
            --bs-body-bg-rgb: 15, 23, 42;
            --bs-body-color: #f1f5f9;
            --bs-body-color-rgb: 241, 245, 249;
            --bs-border-color: #334155;
            --bs-border-color-rgb: 51, 65, 85;
            --bs-border-color-translucent: rgba(51, 65, 85, 0.5);
            --bs-tertiary-bg-rgb: 15, 23, 42;
        }

        [data-bs-theme="dark"] body {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
        }

        [data-bs-theme="dark"] select option,
        [data-bs-theme="dark"] select optgroup {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="light"] .text-light {
            color: var(--bs-body-color) !important;
        }

        html {
            font-size: 14px;
        }

        body {
            font-family: var(--font-sans);
            font-size: 0.9rem;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            overflow-x: hidden;
        }

        /* Global Button Styling */
        .btn,
        .btn.rounded-pill {
            border-radius: 9px !important;
        }

        /* Glassmorphism sidebar & navbar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            background: rgba(var(--bs-tertiary-bg-rgb), 0.85);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(var(--bs-border-color-rgb), 0.15);
            transition: transform var(--transition-speed);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding-top: var(--navbar-height);
            transition: margin-left var(--transition-speed);
        }

        .navbar-custom {
            height: var(--navbar-height);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            background: rgba(var(--bs-body-bg-rgb), 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(var(--bs-border-color-rgb), 0.15);
            transition: left var(--transition-speed);
        }

        /* Custom Cards - Unified Clean Design */
        .card,
        .glass-card,
        .info-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            backdrop-filter: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover,
        .glass-card:hover,
        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .card-header,
        .glass-card>.card-header,
        .info-card>.card-header {
            background-color: transparent;
            border-bottom: 1px solid #e2e8f0;
        }

        .card-footer,
        .glass-card>.card-footer,
        .info-card>.card-footer {
            background-color: transparent;
            border-top: 1px solid #e2e8f0;
        }

        /* Sidebar Nav Links */
        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: var(--bs-body-color);
            text-decoration: none;
            border-radius: 10px;
            margin: 4px 15px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .nav-link-custom i:first-child {
            width: 24px;
            font-size: 1.1rem;
            margin-right: 12px;
            text-align: center;
        }

        .nav-link-custom .fa-chevron-down {
            margin-right: 0 !important;
            width: auto !important;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            background: var(--primary-bg);
            color: #fff !important;
        }

        /* Sidebar Accordion Chevron Animation */
        .nav-link-custom[data-bs-toggle="collapse"] .fa-chevron-down {
            transition: transform 0.3s ease;
        }

        .nav-link-custom[data-bs-toggle="collapse"].collapsed .fa-chevron-down,
        .nav-link-custom[data-bs-toggle="collapse"][aria-expanded="false"] .fa-chevron-down {
            transform: rotate(-90deg);
        }

        /* Micro-animations */
        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        /* ==================== ADMIN RESPONSIVE STYLES ==================== */

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.active {
                transform: translateX(0);
                box-shadow: 5px 0 25px rgba(0,0,0,0.2);
            }

            .main-content {
                margin-left: 0;
            }

            .navbar-custom {
                left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.4);
                z-index: 999;
            }

            .sidebar.active ~ .sidebar-overlay {
                display: block;
            }

            .table-responsive,
            .card-body {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 767px) {
            html {
                font-size: 13px;
            }

            .main-content {
                padding-top: 60px;
            }

            .navbar-custom {
                height: 60px;
                padding: 0 12px;
            }

            .navbar-custom .container-fluid {
                padding: 0 8px;
            }

            .card,
            .glass-card,
            .info-card {
                border-radius: 12px !important;
                margin-bottom: 12px;
            }

            .card-body {
                padding: 14px !important;
            }

            .card-header {
                padding: 12px 14px !important;
            }

            .table thead th {
                padding: 8px 10px !important;
                font-size: 0.68rem !important;
            }

            .table tbody td {
                padding: 8px 10px !important;
                font-size: 0.8rem;
            }

            .info-card {
                margin-bottom: 10px;
            }

            .form-control,
            .form-select {
                font-size: 0.85rem;
                padding: 8px 10px;
            }

            .form-label {
                font-size: 0.8rem;
                margin-bottom: 4px;
            }

            .btn {
                font-size: 0.82rem;
                padding: 7px 14px;
            }

            .btn-sm {
                font-size: 0.75rem;
                padding: 5px 10px;
            }

            h5.fw-bold,
            h4.fw-bold {
                font-size: 1.1rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 4px 8px;
            }

            .pagination {
                flex-wrap: wrap;
                justify-content: center;
            }

            .pagination .page-link {
                padding: 6px 10px;
                font-size: 0.78rem;
            }
        }

        @media (max-width: 575px) {
            html {
                font-size: 12.5px;
            }

            .sidebar {
                width: 240px;
            }

            .navbar-custom {
                height: 55px;
            }

            .main-content {
                padding-top: 55px;
                padding-left: 8px;
                padding-right: 8px;
            }

            .card-body {
                padding: 10px !important;
            }

            .d-flex.gap-2 {
                flex-wrap: wrap;
            }

            .table thead th {
                white-space: normal !important;
                font-size: 0.65rem !important;
                padding: 6px 8px !important;
            }

            .table tbody td {
                font-size: 0.78rem;
                padding: 6px 8px !important;
            }

            .modal-dialog {
                margin: 10px;
                max-width: calc(100% - 20px);
            }
        }

        /* Custom overrides for Accordion and Flash messages */

        .alert-info, .alert-primary {
            background-color: #52A8FF !important;
            color: #fff !important;
        }
        
        .alert-danger {
            background-color: #ef4444 !important;
            color: #fff !important;
            border: none !important;
        }

        .alert-success {
            background-color: #22c55e !important;
            color: #fff !important;
            border: none !important;
        }

        .alert-warning {
            background-color: #eab308 !important;
            color: #fff !important;
            border: none !important;
        }

        .alert-info .btn-close, .alert-primary .btn-close, .alert-danger .btn-close, .alert-success .btn-close, .alert-warning .btn-close {
            filter: brightness(0) invert(1);
        }

        /* Dark Mode Comprehensive Overrides */
        [data-bs-theme="dark"] .form-control,
        [data-bs-theme="dark"] .form-select {
            background-color: #0f172a !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            border-radius: 6px;
        }

        [data-bs-theme="dark"] .form-control::placeholder {
            color: #64748b !important;
        }

        [data-bs-theme="dark"] .form-control:focus,
        [data-bs-theme="dark"] .form-select:focus {
            background-color: #0f172a !important;
            color: #ffffff !important;
            border-color: #60a5fa !important;
            box-shadow: 0 0 0 0.25rem rgba(96, 165, 250, 0.25) !important;
        }

        /* Text utility fixes for dark mode */
        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] h1.text-dark,
        [data-bs-theme="dark"] h2.text-dark,
        [data-bs-theme="dark"] h3.text-dark,
        [data-bs-theme="dark"] h4.text-dark,
        [data-bs-theme="dark"] h5.text-dark,
        [data-bs-theme="dark"] h6.text-dark {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .text-secondary {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .bg-light {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .bg-white,
        [data-bs-theme="dark"] .bg-body {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .btn-light {
            background-color: #334155 !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        [data-bs-theme="dark"] .btn-light:hover {
            background-color: #475569 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .modal-content {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }

        [data-bs-theme="dark"] .modal-header,
        [data-bs-theme="dark"] .modal-footer {
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .dropdown-menu {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5) !important;
        }

        [data-bs-theme="dark"] .dropdown-item {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .dropdown-item:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }

        /* Dark Mode Cards */
        [data-bs-theme="dark"] .card,
        [data-bs-theme="dark"] .glass-card,
        [data-bs-theme="dark"] .info-card {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3) !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .card-header,
        [data-bs-theme="dark"] .glass-card>.card-header,
        [data-bs-theme="dark"] .info-card>.card-header {
            border-bottom: 1px solid #334155 !important;
            background-color: transparent !important;
        }

        [data-bs-theme="dark"] .card-footer,
        [data-bs-theme="dark"] .glass-card>.card-footer,
        [data-bs-theme="dark"] .info-card>.card-footer {
            border-top: 1px solid #334155 !important;
            background-color: transparent !important;
        }

        /* Sleek Action Buttons */
        .action-btn {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px !important;
            border: none !important;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }

        .action-btn-primary {
            background-color: rgba(59, 130, 246, 0.12) !important;
            color: #2563eb !important;
        }
        .action-btn-primary:hover {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        .action-btn-warning {
            background-color: rgba(245, 158, 11, 0.12) !important;
            color: #d97706 !important;
        }
        .action-btn-warning:hover {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }

        .action-btn-success {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
        }
        .action-btn-success:hover {
            background-color: #059669 !important;
            color: #ffffff !important;
        }

        .action-btn-danger {
            background-color: rgba(239, 68, 68, 0.12) !important;
            color: #dc2626 !important;
        }
        .action-btn-danger:hover {
            background-color: #dc2626 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .action-btn-primary {
            background-color: rgba(96, 165, 250, 0.2) !important;
            color: #60a5fa !important;
        }
        [data-bs-theme="dark"] .action-btn-primary:hover {
            background-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .action-btn-warning {
            background-color: rgba(251, 191, 36, 0.2) !important;
            color: #fbbf24 !important;
        }
        [data-bs-theme="dark"] .action-btn-warning:hover {
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .action-btn-success {
            background-color: rgba(52, 211, 153, 0.2) !important;
            color: #34d399 !important;
        }
        [data-bs-theme="dark"] .action-btn-success:hover {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .action-btn-danger {
            background-color: rgba(248, 113, 113, 0.2) !important;
            color: #f87171 !important;
        }
        [data-bs-theme="dark"] .action-btn-danger:hover {
            background-color: #ef4444 !important;
            color: #ffffff !important;
        }

        .btn-subtle-info {
            background-color: rgba(14, 165, 233, 0.12) !important;
            color: #0284c7 !important;
            border: none !important;
        }
        .btn-subtle-info:hover {
            background-color: #0284c7 !important;
            color: #ffffff !important;
        }

        .btn-subtle-success {
            background-color: rgba(16, 185, 129, 0.12) !important;
            color: #059669 !important;
            border: none !important;
        }
        .btn-subtle-success:hover {
            background-color: #059669 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-subtle-info {
            background-color: rgba(56, 189, 248, 0.2) !important;
            color: #38bdf8 !important;
        }
        [data-bs-theme="dark"] .btn-subtle-info:hover {
            background-color: #0284c7 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-subtle-success {
            background-color: rgba(52, 211, 153, 0.2) !important;
            color: #34d399 !important;
        }
        [data-bs-theme="dark"] .btn-subtle-success:hover {
            background-color: #10b981 !important;
            color: #ffffff !important;
        }

        /* Global Table Styling */
        .table {
            --bs-table-bg: transparent !important;
            --bs-table-color: inherit !important;
            --bs-table-border-color: #e2e8f0 !important;
            --bs-table-hover-bg: rgba(0, 0, 0, 0.02) !important;
            font-size: 0.875rem;
            margin-bottom: 0;
            border-collapse: collapse !important;
        }

        .table thead th {
            color: #64748b !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #e2e8f0 !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            font-size: 0.75rem !important;
            letter-spacing: 0.05em !important;
            padding: 12px 16px !important;
            white-space: nowrap !important;
        }

        .table tbody td {
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 12px 16px !important;
            vertical-align: middle !important;
            background-color: transparent !important;
        }

        .table tbody tr {
            border-bottom: 1px solid #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .table {
            --bs-table-bg: transparent !important;
            --bs-table-color: #cbd5e1 !important;
            --bs-table-border-color: #334155 !important;
            --bs-table-hover-bg: rgba(51, 65, 85, 0.4) !important;
        }

        [data-bs-theme="dark"] .table thead th,
        [data-bs-theme="dark"] .table-light {
            color: #94a3b8 !important;
            background-color: #0f172a !important;
            border-bottom: 1px solid #334155 !important;
        }

        [data-bs-theme="dark"] .table tbody td,
        [data-bs-theme="dark"] .table tbody tr {
            border-bottom: 1px solid #334155 !important;
            color: #cbd5e1 !important;
        }
    </style>

    <!-- Custom layout CSS hook -->
    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column" id="sidebar">
        <div class="p-4 border-bottom border-light border-opacity-10 d-flex align-items-center justify-content-between">
            <span class="fs-5 fw-bold text-primary"><i class="fa-solid fa-clock me-2"></i>Attendance Suite</span>
            <button class="btn btn-sm d-lg-none" onclick="toggleSidebar()"><i class="fa-solid fa-times"></i></button>
        </div>

        <div class="overflow-y-auto flex-grow-1 py-3" id="sidebarMenu">

            <div class="px-3 mb-2 text-muted"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                Main Menu
            </div>

            <!-- Dashboard -->
            <a href="{{ route('admin.attendance-suite.dashboard') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge text-primary"></i>Dashboard
            </a>

            <!-- Employee Management -->
            <a href="{{ route('admin.attendance-suite.employees.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.employees.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users text-info"></i>Employee Mgmt
            </a>

            <!-- Attendance Management Collapsible -->
            <a class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.attendance.*') ? 'active' : '' }}"
                data-bs-toggle="collapse" href="#attendanceSubMenu" role="button"
                aria-expanded="{{ request()->routeIs('admin.attendance-suite.attendance.*') ? 'true' : 'false' }}">
                <i class="fa-solid fa-clipboard-user text-success"></i>Attendance Mgmt <i class="fa-solid fa-chevron-down ms-auto" style="font-size: 0.8rem;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.attendance-suite.attendance.*') ? 'show' : '' }}"
                id="attendanceSubMenu" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance-suite.attendance.index') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance-suite.attendance.index') ? 'active' : '' }}" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-play" style="font-size: 0.8rem;"></i> Check In / Check Out
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance-suite.attendance.missing-punches') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance-suite.attendance.missing-punches') ? 'active' : '' }}" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-user-clock" style="font-size: 0.8rem;"></i> Missing Punches
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance-suite.attendance.corrections') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance-suite.attendance.corrections') ? 'active' : '' }}" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-pen-to-square" style="font-size: 0.8rem;"></i> Corrections
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance-suite.attendance.history') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance-suite.attendance.history') ? 'active' : '' }}" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 0.8rem;"></i> Attendance History
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Shift Management -->
            <a href="{{ route('admin.attendance-suite.shifts.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.shifts.*') ? 'active' : '' }}">
                <i class="fa-solid fa-business-time text-warning"></i>Shift Mgmt
            </a>

            <!-- Leave Management -->
            <a href="{{ route('admin.attendance-suite.leaves.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.leaves.*') ? 'active' : '' }}">
                <i class="fa-solid fa-umbrella-beach text-teal"></i>Leave Mgmt
            </a>

            <!-- Holiday Management -->
            <a href="{{ route('admin.attendance-suite.holidays.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.holidays.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-day text-danger"></i>Holiday Mgmt
            </a>

            <!-- Branch & Dept Mgmt -->
            <a href="{{ route('admin.attendance-suite.branches.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.branches.*') ? 'active' : '' }}">
                <i class="fa-solid fa-code-branch text-primary"></i>Branch & Department
            </a>

            <!-- Salary & Payroll -->
            <a href="{{ route('admin.attendance-suite.payroll.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.payroll.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-check-dollar text-success"></i>Salary & Payroll
            </a>

            <!-- Reports & Exports -->
            <a href="{{ route('admin.attendance-suite.reports.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.reports.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie text-secondary"></i>Reports & Exports
            </a>

            <!-- Notifications -->
            <a href="{{ route('admin.attendance-suite.notifications.index') }}"
                class="nav-link-custom {{ request()->routeIs('admin.attendance-suite.notifications.*') ? 'active' : '' }}">
                <i class="fa-solid fa-bell text-warning"></i>Notifications & SMS
            </a>

            <!-- Biometric Integration Divider -->
            <div class="mt-3 mb-2 px-3 text-muted"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                Biometric Hardware
            </div>

            <!-- Biometric Hardware -->
            <a class="nav-link-custom {{ request()->routeIs('admin.attendance.biometric-logs') || request()->routeIs('admin.attendance.live-monitor') ? 'active' : '' }}"
                data-bs-toggle="collapse" href="#biometricMenu" role="button"
                aria-expanded="{{ request()->routeIs('admin.attendance.biometric-logs') || request()->routeIs('admin.attendance.live-monitor') ? 'true' : 'false' }}">
                <i class="fa-solid fa-fingerprint text-info"></i>Biometric Devices <i class="fa-solid fa-chevron-down ms-auto"
                    style="font-size: 0.8rem;"></i>
            </a>
            <div class="collapse {{ request()->routeIs('admin.biometric-devices.*') || request()->routeIs('admin.attendance.biometric-logs') || request()->routeIs('admin.attendance.live-monitor') ? 'show' : '' }}"
                id="biometricMenu" data-bs-parent="#sidebarMenu">
                <ul class="nav flex-column ms-4">
                    <li class="nav-item">
                        <a href="{{ route('admin.biometric-devices.index') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.biometric-devices.*') ? 'active' : '' }}"
                            style="font-size: 0.88rem;">
                            <i class="fa-solid fa-router" style="font-size: 0.8rem;"></i> ZKTeco Devices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.biometric-logs') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance.biometric-logs') ? 'active' : '' }}"
                            style="font-size: 0.88rem;">
                            <i class="fa-solid fa-list-check" style="font-size: 0.8rem;"></i> Device Logs
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.attendance.live-monitor') }}"
                            class="nav-link-custom py-1 {{ request()->routeIs('admin.attendance.live-monitor') ? 'active' : '' }}"
                            style="font-size: 0.88rem;">
                            <i class="fa-solid fa-display" style="font-size: 0.8rem;"></i> Live Push Monitor
                        </a>
                    </li>
                </ul>
            </div>

            <!-- System Administration -->
            <div class="mt-3 mb-2 px-3 text-muted"
                style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                Portals & System
            </div>

            <a href="{{ route('employee.dashboard') }}"
                class="nav-link-custom {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-mobile-screen text-primary"></i>Employee Portal
            </a>

            @if (auth()->user()->isSuperAdmin() || auth()->user()->hasRole('admin'))
                <a href="{{ route('admin.roles.index') }}"
                    class="nav-link-custom {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-shield text-info"></i>Roles & Permissions
                </a>

                <a href="{{ route('admin.settings.index') }}"
                    class="nav-link-custom {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gears text-secondary"></i>System Settings
                </a>

                @if (Route::has('admin.sms-configuration.index'))
                    <a href="{{ route('admin.sms-configuration.index') }}"
                        class="nav-link-custom {{ request()->routeIs('admin.sms-configuration.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-message text-success"></i>SMS Setup
                    </a>
                @endif
            @endif

        </div></div>

        <div class="p-3 border-top border-light border-opacity-10">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100"><i
                        class="fa-solid fa-right-from-bracket me-2"></i>Logout</button>
            </form>
        </div>
    </div>

    <!-- Main Section -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-custom d-flex align-items-center justify-content-between px-4">
            <div class="d-flex align-items-center">
                <button class="btn btn-light d-lg-none me-3" onclick="toggleSidebar()"><i
                        class="fa-solid fa-bars"></i></button>
                <h5 class="m-0 fw-semibold">{{ $header ?? 'Dashboard' }}</h5>
            </div>

            <div class="d-flex align-items-center">

                @if (auth()->user()->hasRole(['super_admin', 'admin']))
                    <!-- Holidays -->
                    <a href="{{ route('admin.holidays.index') }}" class="btn btn-light rounded-circle me-3"
                        title="Holidays">
                        <i class="fa-solid fa-umbrella-beach text-secondary"></i>
                    </a>

                    <!-- Clear Cache -->
                    <form action="{{ route('admin.clear.cache') }}" method="POST" class="m-0 p-0 me-3">
                        @csrf
                        <button type="submit" class="btn btn-light rounded-circle" title="Clear Cache">
                            <i class="fa-solid fa-broom text-secondary"></i>
                        </button>
                    </form>
                @endif

                <!-- Calculator -->
                <div class="dropdown me-3">
                    <button class="btn btn-light rounded-circle" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false" data-bs-auto-close="outside" title="Calculator">
                        <i class="fa-solid fa-calculator text-secondary"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-3"
                        style="width: 280px; border-radius: 16px;">
                        <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-calculator me-2"></i>Calculator
                        </h6>
                        <div class="mb-3">
                            <input type="text" id="calc-display"
                                class="form-control text-end fs-4 fw-bold shadow-sm" value=""
                                style="height: 50px; background-color: #f8f9fa; border: 1px solid #dee2e6; color: #333;"
                                onkeydown="calcKeyDown(event)">
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                            <button type="button" class="btn btn-danger fw-bold py-2"
                                onclick="calcClear()">C</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('(')">(</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend(')')">)</button>
                            <button type="button" class="btn btn-primary fw-bold py-2 shadow-sm"
                                onclick="calcAppend('%')">%</button>

                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('7')">7</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('8')">8</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('9')">9</button>
                            <button type="button" class="btn btn-primary fw-bold py-2 shadow-sm"
                                onclick="calcAppend('/')">Ã·</button>

                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('4')">4</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('5')">5</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('6')">6</button>
                            <button type="button" class="btn btn-primary fw-bold py-2 shadow-sm"
                                onclick="calcAppend('*')">Ã—</button>

                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('1')">1</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('2')">2</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('3')">3</button>
                            <button type="button" class="btn btn-primary fw-bold py-2 shadow-sm"
                                onclick="calcAppend('-')">âˆ’</button>

                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('0')">0</button>
                            <button type="button" class="btn btn-light fw-bold py-2 border shadow-sm"
                                onclick="calcAppend('.')">.</button>
                            <button type="button" class="btn btn-success fw-bold py-2 shadow-sm"
                                onclick="calcCalculate()">=</button>
                            <button type="button" class="btn btn-primary fw-bold py-2 shadow-sm"
                                onclick="calcAppend('+')">+</button>
                        </div>
                    </div>
                </div>

                <!-- Theme toggle -->
                <button class="btn btn-light rounded-circle me-3" id="themeToggleBtn" onclick="toggleTheme()">
                    <i class="fa-solid fa-moon text-secondary"></i>
                </button>

                <!-- Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 border-0 bg-transparent"
                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center"
                            style="width: 36px; height: 36px;">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="d-none d-md-inline fw-semibold text-secondary">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="border-radius: 12px;">
                        <li><a class="dropdown-item py-2" href="{{ route('profile.show') }}"><i
                                    class="fa-solid fa-user me-2"></i>My Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger"><i
                                        class="fa-solid fa-right-from-bracket me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Body -->
        <div class="container-fluid p-4">
            <!-- Alert Messages -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert"
                    style="border-radius: 12px;">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert"
                    style="border-radius: 12px;">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('info'))
                <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm" role="alert"
                    style="border-radius: 12px;">
                    <i class="fa-solid fa-circle-info me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        // Initialize Theme from localStorage
        const currentTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-bs-theme', currentTheme);
        updateToggleBtnIcon(currentTheme);

        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-bs-theme');
            const target = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-bs-theme', target);
            localStorage.setItem('theme', target);
            updateToggleBtnIcon(target);
        }

        function updateToggleBtnIcon(theme) {
            const btn = document.getElementById('themeToggleBtn');
            if (btn) {
                btn.innerHTML = theme === 'dark' ?
                    '<i class="fa-solid fa-sun text-warning"></i>' :
                    '<i class="fa-solid fa-moon text-secondary"></i>';
            }
        }
    </script>

    <script>
        // Calculator Functions
        const calcDisplay = document.getElementById('calc-display');

        function calcAppend(val) {
            if (calcDisplay) calcDisplay.value += val;
        }

        function calcClear() {
            if (calcDisplay) calcDisplay.value = '';
        }

        function calcCalculate() {
            if (calcDisplay) {
                try {
                    // Replace % with /100 for percentage calculation
                    let expression = calcDisplay.value.replace(/%/g, '/100');
                    const result = new Function('return ' + expression)();
                    calcDisplay.value = Number.isFinite(result) ? result : 'Error';
                } catch (e) {
                    calcDisplay.value = 'Error';
                }
            }
        }

        function calcKeyDown(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                calcCalculate();
            }
        }
    </script>

    <!-- View scripts stack -->
    @stack('scripts')
</body>

</html>
