@extends('layouts.app')

@section('content')
<style>
    body {
        background-color: #f1f5f9;
    }
    [data-bs-theme="dark"] body {
        background-color: var(--bs-body-bg) !important;
    }
    .smart-card {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        background: #fff;
    }
    .smart-card .card-body {
        padding: 15px;
    }
    .smart-card .metric-label {
        font-size: 0.95rem;
        color: #333;
        font-weight: 400;
    }
    .smart-card .metric-value {
        font-size: 0.95rem;
        color: #333;
    }
    .smart-card .progress {
        height: 4px;
        border-radius: 0;
        background-color: #e9ecef;
        margin-top: 10px;
    }
    
    .chart-card {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 20px;
        background: #fff;
    }
    .chart-card .card-header {
        background: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 15px 20px;
        font-size: 1.1rem;
        color: #333;
        font-weight: 400;
    }
    .chart-card .card-body {
        padding: 20px;
        position: relative;
    }

    /* Dark Mode Overrides */
    [data-bs-theme="dark"] .smart-card,
    [data-bs-theme="dark"] .chart-card {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        box-shadow: none;
    }
    [data-bs-theme="dark"] .chart-card .card-header {
        background-color: var(--bs-body-bg);
        border-bottom: 1px solid var(--bs-border-color);
        color: var(--bs-body-color);
    }
    [data-bs-theme="dark"] .smart-card .metric-label,
    [data-bs-theme="dark"] .smart-card .metric-value,
    [data-bs-theme="dark"] .smart-card i {
        color: var(--bs-body-color) !important;
    }
    [data-bs-theme="dark"] .smart-card .progress {
        background-color: var(--bs-secondary-bg);
    }
</style>

<div class="container-fluid px-0">
    <!-- KPI Cards -->
    <div class="row">
        <!-- Card 1 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-user-graduate me-2"></i> Total Students
                        </div>
                        <div class="metric-value">{{ $total_students }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-info" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-chalkboard-teacher me-2"></i> Total Teachers
                        </div>
                        <div class="metric-value">{{ $total_teachers }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 100%; background-color: #00bcd4;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 3 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-users-gear me-2"></i> Total Staff
                        </div>
                        <div class="metric-value">{{ $total_staff }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 100%; background-color: #2196f3;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 4 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-money-bill-wave me-2"></i> Total Fee Collected
                        </div>
                        <div class="metric-value">৳{{ number_format($total_fee_collected, 2) }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-danger" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 5 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-users me-2"></i> Total Parents
                        </div>
                        <div class="metric-value">{{ $total_parents }}</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-secondary" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card 6 -->
        <div class="col-md-4 col-sm-6">
            <div class="card smart-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center metric-label">
                            <i class="fa-solid fa-calendar-check me-2"></i> Student Present Today
                        </div>
                        <div class="metric-value">{{ $today_student_attendance }}%</div>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $today_student_attendance }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 1 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card chart-card">
                <div class="card-header">
                    Fees Collection & Expenses For {{ $currentMonth }}
                </div>
                <div class="card-body" style="height: 350px; position: relative;">
                    <canvas id="monthlyBarChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card chart-card">
                <div class="card-header">
                    Income - {{ $currentMonth }}
                </div>
                <div class="card-body" style="height: 350px; position: relative;">
                    <canvas id="incomeDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-md-8">
            <div class="card chart-card">
                <div class="card-header">
                    Fees Collection & Expenses For Session {{ $currentSession }}
                </div>
                <div class="card-body" style="height: 350px; position: relative;">
                    <canvas id="yearlyLineChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card chart-card">
                <div class="card-header">
                    Expense - {{ $currentMonth }}
                </div>
                <div class="card-body" style="height: 350px; position: relative;">
                    <canvas id="expenseDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Overview Panels -->
    <style>
        .overview-panel {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .overview-panel .panel-heading {
            padding: 15px 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
            color: #333;
        }
        .overview-panel .panel-body {
            padding: 20px;
        }
        .overview-item {
            margin-bottom: 15px;
        }
        .overview-item:last-child {
            margin-bottom: 0;
        }
        .overview-label {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .overview-progress {
            height: 4px;
            background: #f5f5f5;
            border-radius: 0;
        }
        
        .mini-stat-card {
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            padding: 15px;
        }
        .mini-stat-icon {
            width: 48px;
            height: 48px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 1.25rem;
            color: #333;
        }
        .mini-stat-info {
            flex: 1;
        }
        .mini-stat-title {
            font-size: 0.85rem;
            color: #666;
            margin-bottom: 2px;
        }
        .mini-stat-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        /* Dark Mode for Bottom Panels */
        [data-bs-theme="dark"] .overview-panel,
        [data-bs-theme="dark"] .mini-stat-card {
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            box-shadow: none;
        }
        [data-bs-theme="dark"] .overview-panel .panel-heading {
            border-bottom: 1px solid var(--bs-border-color);
            color: var(--bs-body-color);
        }
        [data-bs-theme="dark"] .overview-label,
        [data-bs-theme="dark"] .mini-stat-title,
        [data-bs-theme="dark"] .mini-stat-value,
        [data-bs-theme="dark"] .mini-stat-icon,
        [data-bs-theme="dark"] .mini-stat-icon i {
            color: var(--bs-body-color) !important;
        }
        [data-bs-theme="dark"] .overview-progress {
            background-color: var(--bs-secondary-bg);
        }
        [data-bs-theme="dark"] .mini-stat-icon {
            border-color: var(--bs-border-color);
        }
    </style>
    
    <div class="row">
        <!-- Fees Overview -->
        <div class="col-md-3">
            <div class="overview-panel">
                <div class="panel-heading">Fees Overview</div>
                <div class="panel-body">
                    @php $fTotal = $feesOverview['total'] > 0 ? $feesOverview['total'] : 1; @endphp
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $feesOverview['unpaid'] }} UNPAID</span> <span>{{ round(($feesOverview['unpaid'] / $fTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-primary" style="width: {{ ($feesOverview['unpaid'] / $fTotal) * 100 }}%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $feesOverview['partial'] }} PARTIAL</span> <span>{{ round(($feesOverview['partial'] / $fTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-info" style="width: {{ ($feesOverview['partial'] / $fTotal) * 100 }}%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $feesOverview['paid'] }} PAID</span> <span>{{ round(($feesOverview['paid'] / $fTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-info" style="width: {{ ($feesOverview['paid'] / $fTotal) * 100 }}%"></div></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Enquiry Overview -->
        <div class="col-md-3">
            <div class="overview-panel">
                <div class="panel-heading">Enquiry Overview</div>
                <div class="panel-body">
                    <div class="overview-item">
                        <div class="overview-label"><span>0 ACTIVE</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-danger" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 WON</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-warning" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 PASSIVE</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-warning" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 LOST</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-warning" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 DEAD</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-warning" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Library Overview -->
        <div class="col-md-3">
            <div class="overview-panel">
                <div class="panel-heading">Library Overview</div>
                <div class="panel-body">
                    <div class="overview-item">
                        <div class="overview-label"><span>0 DUE FOR RETURN</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-success" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 RETURNED</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-success" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>ISSUED OUT OF</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-light" style="width: 0%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>0 AVAILABLE OUT OF</span> <span>0%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-light" style="width: 0%"></div></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Student Today Attendance -->
        <div class="col-md-3">
            <div class="overview-panel">
                <div class="panel-heading">Student Today Attendance</div>
                <div class="panel-body">
                    @php $aTotal = $attendanceOverview['total'] > 0 ? $attendanceOverview['total'] : 1; @endphp
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $attendanceOverview['present'] }} PRESENT</span> <span>{{ round(($attendanceOverview['present'] / $aTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-primary" style="width: {{ ($attendanceOverview['present'] / $aTotal) * 100 }}%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $attendanceOverview['late'] }} LATE</span> <span>{{ round(($attendanceOverview['late'] / $aTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-primary" style="width: {{ ($attendanceOverview['late'] / $aTotal) * 100 }}%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $attendanceOverview['absent'] }} ABSENT</span> <span>{{ round(($attendanceOverview['absent'] / $aTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-primary" style="width: {{ ($attendanceOverview['absent'] / $aTotal) * 100 }}%"></div></div>
                    </div>
                    <div class="overview-item">
                        <div class="overview-label"><span>{{ $attendanceOverview['half_day'] }} HALF DAY</span> <span>{{ round(($attendanceOverview['half_day'] / $aTotal) * 100, 2) }}%</span></div>
                        <div class="progress overview-progress"><div class="progress-bar bg-primary" style="width: {{ ($attendanceOverview['half_day'] / $aTotal) * 100 }}%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mini Stat Cards (Roles Only) -->
    <div class="row">
        @php
            $rolesToShow = ['Super Admin', 'Admin', 'Teacher', 'Accountant', 'Librarian', 'Receptionist'];
            $roleIcons = [
                'Super Admin' => 'fa-user-tie',
                'Admin' => 'fa-user-shield',
                'Teacher' => 'fa-chalkboard-user',
                'Accountant' => 'fa-calculator',
                'Librarian' => 'fa-book-open-reader',
                'Receptionist' => 'fa-phone-volume'
            ];
            $roleKeys = [
                'Super Admin' => 'super_admin',
                'Admin' => 'admin',
                'Teacher' => 'teacher',
                'Accountant' => 'accountant',
                'Librarian' => 'librarian',
                'Receptionist' => 'receptionist'
            ];
        @endphp
        @foreach($rolesToShow as $roleName)
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6 col-12">
                <div class="mini-stat-card">
                    <div class="mini-stat-icon"><i class="fa-solid {{ $roleIcons[$roleName] }}"></i></div>
                    <div class="mini-stat-info">
                        <div class="mini-stat-title">{{ $roleName }}</div>
                        <div class="mini-stat-value">{{ $roleCounts[$roleKeys[$roleName]] ?? 0 }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from server
        const monthlyData = @json($monthlyChartData);
        const yearlyData = @json($yearlyChartData);
        const incomeData = @json($incomeByCategory);
        const expenseData = @json($expenseByCategory);
        
        // Colors matching Smart School
        const colorIncome = '#8bc34a'; // Green
        const colorExpense = '#f44336'; // Red
        const donutColors = [
            '#8bc34a', // Green
            '#ffca28', // Yellow
            '#26c6da', // Cyan
            '#ab47bc', // Purple
            '#42a5f5', // Blue
            '#ffa726', // Orange
            '#ec407a'  // Pink
        ];
        
        // 1. Monthly Bar Chart
        new Chart(document.getElementById('monthlyBarChart'), {
            type: 'bar',
            data: {
                labels: monthlyData.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: monthlyData.income,
                        backgroundColor: colorIncome,
                        barPercentage: 0.5,
                        categoryPercentage: 0.8
                    },
                    {
                        label: 'Expense',
                        data: monthlyData.expense,
                        backgroundColor: colorExpense,
                        barPercentage: 0.5,
                        categoryPercentage: 0.8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#e0e0e0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 2. Yearly Line Chart
        new Chart(document.getElementById('yearlyLineChart'), {
            type: 'line',
            data: {
                labels: yearlyData.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: yearlyData.income,
                        borderColor: colorIncome,
                        backgroundColor: 'rgba(139, 195, 74, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Expense',
                        data: yearlyData.expense,
                        borderColor: colorExpense,
                        backgroundColor: 'rgba(244, 67, 54, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#e0e0e0' }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // 3. Income Donut Chart
        new Chart(document.getElementById('incomeDonutChart'), {
            type: 'doughnut',
            data: {
                labels: incomeData.labels.length ? incomeData.labels : ['No Data'],
                datasets: [{
                    data: incomeData.data.length ? incomeData.data : [1],
                    backgroundColor: incomeData.data.length ? donutColors : ['#e0e0e0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });

        // 4. Expense Donut Chart
        new Chart(document.getElementById('expenseDonutChart'), {
            type: 'doughnut',
            data: {
                labels: expenseData.labels.length ? expenseData.labels : ['No Data'],
                datasets: [{
                    data: expenseData.data.length ? expenseData.data : [1],
                    backgroundColor: expenseData.data.length ? donutColors : ['#e0e0e0'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endsection
