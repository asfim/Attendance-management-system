@extends('layouts.app')

@section('content')
<style>
.dashboard-card {
    border: none;
    border-radius: 15px;
    background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}
.dashboard-card:hover {
    transform: translateY(-5px);
}
.icon-box {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}
[data-bs-theme="dark"] .dashboard-card {
    background: linear-gradient(135deg, rgba(30,41,59,0.7), rgba(15,23,42,0.9));
    border: 1px solid rgba(255,255,255,0.05);
}
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold m-0"><i class="fa-solid fa-chart-pie text-primary me-2"></i>Financial Summary</h4>
            <p class="text-muted fs-7 mb-0">Overview of Expenses, Donations, and Purchases</p>
        </div>
        <div>
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn btn-primary shadow-sm"><i class="fa-solid fa-plus me-2"></i>New Expense</a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <!-- Available Balance -->
        <div class="col-xl-3 col-sm-6">
            <div class="card dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-7 fw-semibold text-uppercase">Available Balance</p>
                        <h3 class="fw-bold mb-0 {{ $availableBalance >= 0 ? 'text-success' : 'text-danger' }}">
                            ৳{{ number_format($availableBalance, 2) }}
                        </h3>
                    </div>
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Donations -->
        <div class="col-xl-3 col-sm-6">
            <div class="card dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-7 fw-semibold text-uppercase">Total Donations</p>
                        <h3 class="fw-bold mb-0 text-primary">
                            ৳{{ number_format($totalDonations, 2) }}
                        </h3>
                    </div>
                    <div class="icon-box bg-primary bg-opacity-10 text-primary">
                        <i class="fa-solid fa-hand-holding-heart"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="col-xl-3 col-sm-6">
            <div class="card dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-7 fw-semibold text-uppercase">Total Expenses</p>
                        <h3 class="fw-bold mb-0 text-danger">
                            ৳{{ number_format($totalExpenses, 2) }}
                        </h3>
                    </div>
                    <div class="icon-box bg-danger bg-opacity-10 text-danger">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Purchases -->
        <div class="col-xl-3 col-sm-6">
            <div class="card dashboard-card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1 fs-7 fw-semibold text-uppercase">Total Purchases</p>
                        <h3 class="fw-bold mb-0 text-warning">
                            ৳{{ number_format($totalPurchases, 2) }}
                        </h3>
                    </div>
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Monthly Expenses Chart -->
        <div class="col-lg-8">
            <div class="card dashboard-card h-100 p-4">
                <h6 class="fw-bold mb-4">Monthly Expenses Trend (Last 6 Months)</h6>
                <canvas id="monthlyExpenseChart" height="100"></canvas>
            </div>
        </div>
        
        <!-- Expenses By Category -->
        <div class="col-lg-4">
            <div class="card dashboard-card h-100 p-4">
                <h6 class="fw-bold mb-4">Expenses by Category</h6>
                <canvas id="categoryExpenseChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const textColor = isDarkMode ? '#cbd5e1' : '#475569';
        const gridColor = isDarkMode ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

        // Monthly Trend
        const ctxMonthly = document.getElementById('monthlyExpenseChart').getContext('2d');
        new Chart(ctxMonthly, {
            type: 'line',
            data: {
                labels: {!! json_encode($monthlyLabels) !!},
                datasets: [{
                    label: 'Expenses',
                    data: {!! json_encode($monthlyData) !!},
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor }, grid: { color: gridColor } }
                }
            }
        });

        // Category Chart
        const ctxCategory = document.getElementById('categoryExpenseChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($categoryLabels) !!},
                datasets: [{
                    data: {!! json_encode($categoryData) !!},
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#06b6d4'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom', labels: { color: textColor, padding: 20 } }
                }
            }
        });
    });
</script>
@endsection
