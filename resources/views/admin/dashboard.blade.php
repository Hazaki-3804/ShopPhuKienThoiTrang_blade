@extends('layouts.admin')
@section('title', 'Dashboard - Trang Quản Trị')

@push('styles')
<style>
    .stats-card {
        background: white;
        border-radius: 8px;
        border-left: 4px solid var(--accent-color);
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        padding: 20px;
        height: 100%;
    }
    
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    
    .stats-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 15px;
    }
    
    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .stats-label {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 10px;
    }
    
    .stats-link {
        color: var(--accent-color);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .stats-link:hover {
        text-decoration: underline;
    }
    
    .chart-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: none;
    }
    
    .chart-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        border-radius: 8px 8px 0 0 !important;
        padding: 15px 20px;
    }
    
    .chart-card .card-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        padding: 20px;
    }
    
    .data-table {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .data-table .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 15px 20px;
    }
    
    .data-table .card-header h6 {
        margin: 0;
        color: #495057;
        font-weight: 600;
    }
    
    .table-clean {
        margin: 0;
        font-size: 0.9rem;
    }
    
    .table-clean thead th {
        background: #f8f9fa;
        border-top: none;
        border-bottom: 1px solid #dee2e6;
        color: #495057;
        font-weight: 600;
        padding: 12px 15px;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table-clean tbody td {
        padding: 12px 15px;
        border-top: 1px solid #f1f3f4;
        vertical-align: middle;
    }
    
    .table-clean tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Pending - chờ xử lý */
    .status-pending::before { content: '🟡'; }
    .status-pending {
        background: #fff3cd; /* vàng nhạt */
        color: #856404;      /* chữ vàng đậm / nâu vàng */
    }

    /* Processing - đang xử lý */
    .status-processing::before { content: '🔵'; }
    .status-processing {
        background: #cce5ff; /* xanh dương nhạt */
        color: #004085;      /* xanh dương đậm */
    }

    /* Shipped - đang vận chuyển */
    .status-shipped::before { content: '🟠'; }
    .status-shipped {
        background: #fff3e0; /* cam nhạt, nhẹ nhàng */
        color: #e65100;      /* cam đậm */
    }

    /* Canceled - hủy */
    .status-cancelled::before { content: '🔴'; }
    .status-cancelled {
        background: #f8d7da; /* đỏ nhạt */
        color: #721c24;      /* đỏ đậm */
    }

    /* Delivered - đã giao */
    .status-delivered::before { content: '🟢'; }
    .status-delivered {
        background: #d4edda; /* xanh lá nhạt */
        color: #155724;      /* xanh lá đậm */
    }
    
    .btn-sm-clean {
        padding: 4px 8px;
        font-size: 0.75rem;
        border-radius: 4px;
        border: 1px solid #dee2e6;
        background: white;
        color: #6c757d;
        text-decoration: none;
        margin-right: 5px;
    }
    
    .btn-sm-clean:hover {
        background: #f8f9fa;
        color: #495057;
    }
    
    .loading-spinner {
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0"><i class="fas fa-tachometer-alt text-primary"></i> Tổng quan hệ thống bán hàng</h1>
    <!-- <small class="text-muted">Tổng quan hệ thống bán hàng</small> -->
</div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="--accent-color: #007bff;">
                <div class="stats-icon" style="background: rgba(0,123,255,0.1); color: #007bff;">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stats-number">{{ number_format($stats['total_sales'], 0, ',', '.') }}₫</div>
                <div class="stats-label">Tổng Doanh Thu</div>
                <a href="{{ route('admin.statistics.index') }}" class="stats-link">
                    <i class="fas fa-arrow-right"></i> Chi tiết
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="--accent-color: #28a745;">
                <div class="stats-icon" style="background: rgba(40,167,69,0.1); color: #28a745;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="stats-number">{{ number_format($stats['total_orders']) }}</div>
                <div class="stats-label">Tổng Đơn Hàng</div>
                <a href="{{ route('admin.orders.index') }}" class="stats-link">
                    <i class="fas fa-arrow-right"></i> Chi tiết
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="--accent-color: #17a2b8;">
                <div class="stats-icon" style="background: rgba(23,162,184,0.1); color: #17a2b8;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stats-number">{{ number_format($stats['new_customers']) }}</div>
                <div class="stats-label">Khách Hàng Mới</div>
                <a href="{{ route('admin.customers.index') }}" class="stats-link">
                    <i class="fas fa-arrow-right"></i> Chi tiết
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card" style="--accent-color: #ffc107;">
                <div class="stats-icon" style="background: rgba(255,193,7,0.1); color: #ffc107;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stats-number">{{ number_format($stats['low_stock']) }}</div>
                <div class="stats-label">Sắp Hết Hàng</div>
                <a href="{{ route('admin.products.index') }}" class="stats-link">
                    <i class="fas fa-arrow-right"></i> Xem kho
                </a>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card chart-card">
                <div class="card-header bg-warning">
                    <h6 ><i class="fas fa-chart-line mr-2"></i>Doanh Thu 7 Ngày Qua</h6>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card chart-card">
                <div class="card-header bg-danger">
                    <h6 class="text-white"><i class="fas fa-chart-pie mr-2"></i>Top Sản Phẩm</h6>
                </div>
                <div class="card-body p-0">
                    <div class="chart-container">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Tables Section -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card data-table">
                <div class="card-header bg-primary">
                    <h6 class="text-white"><i class="fas fa-shopping-bag mr-2"></i>Đơn Hàng Gần Đây</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean">
                            <thead>
                                <tr>
                                    <th>Mã Đơn</th>
                                    <th>Khách Hàng</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Tạo</th>
                                    @can('view orders | manage orders')
                                    <th>Thao Tác</th>
                                    @endcan
                                </tr>
                            </thead>
                            @php
                             function getStatus($status)
                                {
                                    switch ($status) {
                                        case 'pending':
                                            return 'Chờ xác nhận';
                                        case 'processing':
                                            return 'Đang xử lý';
                                        case 'shipped':
                                            return 'Đang giao hàng';
                                        case 'delivered':
                                            return 'Đã giao hàng';
                                        case 'cancelled':
                                            return 'Đã hủy';
                                        default:
                                            return 'Không xác định';
                                    }   
                                }
                            @endphp
                            <tbody>
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order['id'] }}</strong></td>
                                    <td>{{ $order['customer_name'] }}</td>
                                    <td><strong>{{ number_format($order['total_amount'], 0, ',', '.') }}₫</strong></td>
                                    <td>
                                        <span class="status-badge status-{{ $order['status'] }}">
                                            {{ getStatus($order['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $order['created_at'] }}</td>
                                    @can('view orders | manage orders')
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order['id']) }}" class="btn-sm-clean">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                    @endcan
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">
                                        Chưa có đơn hàng nào
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card data-table">
                <div class="card-header bg-success">
                    <h6 class="text-white"><i class="fas fa-user-plus mr-2"></i>Khách Hàng Mới</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-clean">
                            <thead>
                                <tr>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Ngày đăng ký</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($newCustomers as $customer)
                                <tr>
                                    <td><strong>{{ $customer['name'] }}</strong></td>
                                    <td><small class="text-muted">{{ $customer['email'] }}</small></td>
                                    <td><small>{{ $customer['created_at'] }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">
                                        Chưa có khách hàng mới
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Render charts with real data
    renderSalesChart();
    renderProductsChart();
});

// Render Sales Line Chart with real data
function renderSalesChart() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesData = @json($salesData);
    console.log(salesData)
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.labels,
            datasets: [{
                label: 'Doanh thu (triệu ₫)',
                data: salesData.data,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `Doanh thu: ${context.parsed.y.toFixed(2)} triệu ₫`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0,0,0,0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 11
                        },
                        callback: function(value) {
                            return value + 'M';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6c757d',
                        font: {
                            size: 11
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Render Products Doughnut Chart with real data
function renderProductsChart() {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    const productsData = @json($productsData);
    
    // Check if we have data
    if (!productsData.labels.length) {
        ctx.font = '14px Arial';
        ctx.fillStyle = '#6c757d';
        ctx.textAlign = 'center';
        ctx.fillText('Chưa có dữ liệu', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: productsData.labels,
            datasets: [{
                data: productsData.data,
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#17a2b8',
                    '#ffc107',
                    '#dc3545'
                ],
                borderWidth: 0,
                hoverBorderWidth: 2,
                hoverBorderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 10,
                        font: {
                            size: 11
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const dataset = data.datasets[0];
                                    const value = dataset.data[i];
                                    const total = dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    
                                    return {
                                        text: `${label} (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.backgroundColor[i],
                                        lineWidth: 0,
                                        pointStyle: 'circle'
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    cornerRadius: 6,
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((context.parsed / total) * 100);
                            return `${context.label}: ${context.parsed} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
}

// Responsive chart resize
window.addEventListener('resize', function() {
    Chart.helpers.each(Chart.instances, function(instance) {
        instance.resize();
    });
});
</script>
@endpush