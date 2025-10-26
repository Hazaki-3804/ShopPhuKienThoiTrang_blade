@extends('layouts.admin')
@section('title', 'Thống kê tổng quan')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-4">
        <h4 class="fw-semibold m-0">Dashboard Thống kê</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Thống kê']]" />
    </div>

    <!-- Quick Stats Cards -->
    <div class="row m-3">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0" id="total-orders">-</h3>
                            <p class="mb-0">Tổng đơn hàng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-shopping-cart fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0" id="total-revenue">-</h3>
                            <p class="mb-0">Tổng doanh thu</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0" id="total-customers">-</h3>
                            <p class="mb-0">Tổng khách hàng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h3 class="mb-0" id="avg-order-value">-</h3>
                            <p class="mb-0">Giá trị đơn TB</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="row m-3">
        <div class="col-md-4">
            <div class="card card-hover h-100">
                <div class="card-body text-center">
                    <i class="fas fa-user-friends fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Thống kê Khách hàng</h5>
                    <p class="card-text">Phân tích hành vi mua hàng, tần suất và giá trị khách hàng</p>
                    <a href="{{ route('admin.statistics.customers') }}" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-hover h-100">
                <div class="card-body text-center">
                    <i class="fas fa-box fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Thống kê Sản phẩm</h5>
                    <p class="card-text">Doanh số bán hàng, sản phẩm bán chạy và phân tích danh mục</p>
                    <a href="{{ route('admin.statistics.products') }}" class="btn btn-success">
                        <i class="fas fa-chart-pie"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-hover h-100">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-alt fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Thống kê Thời gian</h5>
                    <p class="card-text">Xu hướng doanh thu theo ngày, tháng, quý và tỷ lệ tăng trưởng</p>
                    <a href="{{ route('admin.statistics.time') }}" class="btn btn-info">
                        <i class="fas fa-chart-line"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Charts -->
    <div class="row m-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Doanh thu 7 ngày gần đây</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 5 sản phẩm bán chạy</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card-hover {
    transition: transform 0.2s;
}
.card-hover:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    loadDashboardData();
    loadQuickCharts();
});

function loadDashboardData() {
    // Load summary statistics
    $.ajax({
        url: '/admin/statistics/time/data',
        data: {
            period: 'month',
            start_date: moment().subtract(30, 'days').format('YYYY-MM-DD'),
            end_date: moment().format('YYYY-MM-DD')
        },
        success: function(response) {
            if (response.success) {
                $('#total-orders').text(response.summary.total_orders.toLocaleString());
                $('#total-revenue').text(formatCurrency(response.summary.total_revenue));
                $('#total-customers').text(response.summary.unique_customers.toLocaleString());
                $('#avg-order-value').text(formatCurrency(response.summary.avg_order_value));
            }
        }
    });
}

function loadQuickCharts() {
    // Revenue chart for last 7 days
    $.ajax({
        url: '/admin/statistics/time/data',
        data: {
            period: 'day',
            start_date: moment().subtract(7, 'days').format('YYYY-MM-DD'),
            end_date: moment().format('YYYY-MM-DD')
        },
        success: function(response) {
            if (response.success) {
                createRevenueChart(response.data);
            }
        }
    });

    // Top products chart
    $.ajax({
        url: '/admin/statistics/products/chart-data',
        data: {
            start_date: moment().subtract(30, 'days').format('YYYY-MM-DD'),
            end_date: moment().format('YYYY-MM-DD'),
            limit: 5
        },
        success: function(response) {
            if (response.success) {
                createTopProductsChart(response.topProducts);
            }
        }
    });
}

function createRevenueChart(data) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(item => moment(item.period).format('DD/MM')),
            datasets: [{
                label: 'Doanh thu',
                data: data.map(item => item.total_revenue),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + formatCurrency(context.parsed.y);
                        }
                    }
                }
            }
        }
    });
}

function createTopProductsChart(data) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                data: data.map(item => item.total_sold),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + ' sản phẩm';
                        }
                    }
                }
            }
        }
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
@endpush
