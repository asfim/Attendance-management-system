@extends('frontend.layouts.app')

@push('styles')
<style>
    .dept-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%);
        padding: 140px 0 80px;
        position: relative;
        overflow: hidden;
    }
    .dept-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=2000&auto=format&fit=crop') center/cover;
        opacity: 0.15;
        mix-blend-mode: overlay;
    }
    .dept-header::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: radial-gradient(circle at center, transparent 0%, var(--navy-deep) 100%);
        opacity: 0.7;
    }

    .dept-card {
        background: #fff;
        border-radius: 1.5rem;
        padding: 40px 30px;
        border: 1px solid rgba(13, 71, 161, 0.08); /* subtle navy border */
        box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        height: 100%;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        overflow: hidden;
        z-index: 1;
        display: flex;
        flex-direction: column;
    }
    .dept-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 150px; height: 150px;
        background: radial-gradient(circle at top right, rgba(249, 168, 37, 0.1), transparent 70%);
        z-index: -1;
        border-radius: 0 1.5rem 0 100%;
        transition: all 0.4s ease;
    }
    .dept-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0; height: 5px;
        background: linear-gradient(90deg, var(--navy-deep), var(--primary));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        z-index: -1;
    }
    .dept-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 50px rgba(13, 71, 161, 0.12);
        border-color: rgba(13, 71, 161, 0.2);
    }
    .dept-card:hover::after {
        transform: scaleX(1);
    }
    .dept-card:hover::before {
        transform: scale(1.5);
        background: radial-gradient(circle at top right, rgba(249, 168, 37, 0.15), transparent 70%);
    }
    
    .dept-icon {
        width: 75px;
        height: 75px;
        background: linear-gradient(135deg, rgba(13, 71, 161, 0.1), rgba(25, 118, 210, 0.05));
        color: var(--navy-deep);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin-bottom: 30px;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        position: relative;
        box-shadow: inset 0 0 0 1px rgba(13, 71, 161, 0.1);
    }
    .dept-card:hover .dept-icon {
        background: linear-gradient(135deg, var(--navy-deep), var(--primary));
        color: #fff;
        transform: translateY(-5px) rotate(5deg);
        box-shadow: 0 10px 20px rgba(13, 71, 161, 0.2);
    }

    .subject-badge {
        background: rgba(13, 71, 161, 0.05);
        color: var(--navy-deep);
        padding: 6px 14px;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 8px;
        margin-bottom: 10px;
        display: inline-block;
        border: 1px solid rgba(13, 71, 161, 0.1);
        transition: all 0.3s ease;
    }
    .dept-card:hover .subject-badge {
        background: #fff;
        border-color: var(--primary);
        box-shadow: 0 2px 10px rgba(13, 71, 161, 0.1);
    }
    .subject-badge:hover {
        background: var(--navy-deep) !important;
        color: #fff !important;
        border-color: var(--navy-deep) !important;
    }
</style>
@endpush

@section('content')

<!-- Hero Section -->
<div class="dept-header text-center text-white" style="@if(\App\Models\Setting::get('departments_hero_bg')) background: linear-gradient(135deg, var(--primary) 0%, var(--navy-deep) 100%), url('{{ \App\Models\Setting::get('departments_hero_bg') }}') center/cover; @endif">
    <div class="container position-relative z-index-1">
        <span class="badge bg-warning text-dark px-3 py-2 mb-3 rounded-pill fw-bold" style="letter-spacing: 1px;">{{ \App\Models\Setting::get('departments_hero_badge', 'Areas of Study') }}</span>
        <h1 class="display-4 fw-bold mb-3" style="font-family: 'Playfair Display', serif;">{{ \App\Models\Setting::get('departments_hero_title1', 'Academic') }} <span class="text-warning">{{ \App\Models\Setting::get('departments_hero_title2', 'Departments') }}</span></h1>
        <p class="lead text-white-75 mx-auto" style="max-width: 700px;">
            {{ \App\Models\Setting::get('departments_hero_subtitle', 'Explore our diverse academic departments, offering comprehensive curricula designed to equip students with knowledge and skills for the future.') }}
        </p>
    </div>
</div>

<!-- Departments Grid -->
<section class="section py-5 bg-light min-vh-100">
    <div class="container py-5">
        
        <div class="row g-4">
            
            @php
                $departments = json_decode(\App\Models\Setting::get('departments_list', '[]'), true);
                if (empty($departments)) {
                    $departments = [
                        [
                            'icon' => 'bi-rocket-takeoff', 'title' => 'Department of Science', 'desc' => 'Fostering analytical thinking and innovation through hands-on laboratory experiences and theoretical physics, chemistry, and biology.',
                            'subject1' => 'Physics', 'subject2' => 'Chemistry', 'subject3' => 'Biology', 'subject4' => 'Adv. Math'
                        ],
                        [
                            'icon' => 'bi-bar-chart-fill', 'title' => 'Department of Commerce', 'desc' => 'Preparing future business leaders with a strong foundation in economics, accounting, and modern business management strategies.',
                            'subject1' => 'Accounting', 'subject2' => 'Economics', 'subject3' => 'Finance', 'subject4' => 'Business Org.'
                        ],
                        [
                            'icon' => 'bi-globe-americas', 'title' => 'Department of Humanities', 'desc' => 'Exploring human culture, history, and society to develop empathetic, articulate, and well-rounded global citizens.',
                            'subject1' => 'History', 'subject2' => 'Geography', 'subject3' => 'Sociology', 'subject4' => 'Literature'
                        ],
                        [
                            'icon' => 'bi-pc-display', 'title' => 'Computer Science', 'desc' => 'Equipping students with modern programming skills, computational logic, and technology literacy for the digital age.',
                            'subject1' => 'Programming', 'subject2' => 'Data Structs', 'subject3' => 'IT Systems', 'subject4' => 'Web Dev'
                        ],
                        [
                            'icon' => 'bi-translate', 'title' => 'Department of Languages', 'desc' => 'Fostering excellent communication skills and literary appreciation in both native and foreign languages.',
                            'subject1' => 'English', 'subject2' => 'Bengali', 'subject3' => 'Arabic', 'subject4' => 'French'
                        ],
                        [
                            'icon' => 'bi-dribbble', 'title' => 'Physical Education', 'desc' => 'Promoting health, fitness, and teamwork through rigorous sports training and physical activities.',
                            'subject1' => 'Athletics', 'subject2' => 'Team Sports', 'subject3' => 'Fitness', 'subject4' => 'Health'
                        ]
                    ];
                }
            @endphp

            @foreach($departments as $dept)
            <div class="col-lg-4 col-md-6">
                <div class="dept-card">
                    <div class="dept-icon">
                        <i class="bi {{ $dept['icon'] ?? 'bi-book' }}"></i>
                    </div>
                    <h3 class="font-display text-2xl text-dark mb-3 fw-bold" style="font-family: 'Playfair Display', serif;">{{ $dept['title'] ?? '' }}</h3>
                    <p class="text-muted small leading-relaxed mb-4 flex-grow-1">{{ $dept['desc'] ?? '' }}</p>
                    <div class="border-top pt-4 mt-auto">
                        <p class="text-warning font-monospace small text-uppercase tracking-widest fw-bold mb-3" style="letter-spacing: 1px;">Core Subjects</p>
                        <div class="d-flex flex-wrap">
                            @if(!empty($dept['subject1'])) <span class="subject-badge">{{ $dept['subject1'] }}</span> @endif
                            @if(!empty($dept['subject2'])) <span class="subject-badge">{{ $dept['subject2'] }}</span> @endif
                            @if(!empty($dept['subject3'])) <span class="subject-badge">{{ $dept['subject3'] }}</span> @endif
                            @if(!empty($dept['subject4'])) <span class="subject-badge">{{ $dept['subject4'] }}</span> @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

@endsection
