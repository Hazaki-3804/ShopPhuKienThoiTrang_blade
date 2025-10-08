@extends('layouts.admin')
@section('title', 'Thống kê Sản phẩm')
@section('content_header')
<span class="fw-semibold">Thống kê Sản phẩm</span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Phân tích Sản phẩm</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Thống kê'], ['name' => 'Sản phẩm']]" />
    </div>

    <!-- Filters -->
    <div class="card m-3">
        <div class="card-header">
            <h5 class="mb-0">Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ date('Y-m-d', strtotime('-30 days')) }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label for="category_id" class="form-label">Danh mục</label>
                    <select class="form-control" id="category_id" name="category_id">
                        <option value="">-- Tất cả danh mục --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        <button type="button" class="btn btn-success" id="exportExcel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row m-3" id="summaryCards">
        <div class="col-lg-3 col-md-6">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="totalProducts">-</h4>
                    <p class="mb-0">Tổng sản phẩm</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="productsSold">-</h4>
                    <p class="mb-0">Sản phẩm đã bán</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="totalRevenue">-</h4>
                    <p class="mb-0">Tổng doanh thu</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h4 class="mb-0" id="totalQuantitySold">-</h4>
                    <p class="mb-0">Tổng số lượng bán</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row m-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Top 10 sản phẩm bán chạy</h5>
                </div>
                <div class="card-body">
                    <canvas id="topProductsChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Doanh thu theo danh mục</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryRevenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Data Table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Chi tiết sản phẩm</h5>
            <div>
                <input type="search" id="productSearch" class="form-control form-control-sm" 
                       placeholder="Tìm kiếm sản phẩm..." style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="productsTable">
                    <thead class="table-info">
                        <tr>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá gốc</th>
                            <th>Giá bán TB</th>
                            <th>Số lượng bán</th>
                            <th>Doanh thu</th>
                            <th>Lợi nhuận ước tính</th>
                            <th>Tỷ lệ lợi nhuận</th>
                            <th>Tồn kho</th>
                        </tr>
                    </thead>
                    <tbody id="productsTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Performance Analysis -->
    <div class="row m-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân tích hiệu suất sản phẩm</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
let productsData = [];
let chartData = {};

$(document).ready(function() {
    loadProductData();
    loadChartData();
    
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadProductData();
        loadChartData();
    });

    $('#productSearch').on('keyup', function() {
        filterTable();
    });

    $('#exportExcel').on('click', function() {
        exportData();
    });
});

function loadProductData() {
    const formData = {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        category_id: $('#category_id').val()
    };

    $.ajax({
        url: '/admin/statistics/products/data',
        data: formData,
        beforeSend: function() {
            $('#productsTableBody').html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        },
        success: function(response) {
            if (response.success) {
                productsData = response.data;
                updateSummaryCards(response.summary);
                renderProductsTable(response.data);
                createPerformanceChart(response.data);
            }
        },
        error: function() {
            $('#productsTableBody').html('<tr><td colspan="9" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
        }
    });
}

function loadChartData() {
    const formData = {
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        limit: 10
    };

    $.ajax({
        url: '/admin/statistics/products/chart-data',
        data: formData,
        success: function(response) {
            if (response.success) {
                chartData = response;
                createTopProductsChart(response.topProducts);
                createCategoryRevenueChart(response.categoryRevenue);
            }
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalProducts').text(summary.total_products.toLocaleString());
    $('#productsSold').text(summary.products_sold.toLocaleString());
    $('#totalRevenue').text(formatCurrency(summary.total_revenue));
    $('#totalQuantitySold').text(summary.total_quantity_sold.toLocaleString());
}

function renderProductsTable(data) {
    let html = '';
    
    data.forEach(function(product) {
        const profitMarginClass = product.profit_margin > 20 ? 'text-success' : 
                                 product.profit_margin > 10 ? 'text-warning' : 'text-danger';
        
        html += `
            <tr>
                <td>${product.name}</td>
                <td>${product.category_name}</td>
                <td class="text-end">${formatCurrency(product.price)}</td>
                <td class="text-end">${formatCurrency(product.avg_selling_price)}</td>
                <td class="text-center">${product.total_sold}</td>
                <td class="text-end">${formatCurrency(product.total_revenue)}</td>
                <td class="text-end">${formatCurrency(product.estimated_profit)}</td>
                <td class="text-center ${profitMarginClass}">${product.profit_margin.toFixed(1)}%</td>
                <td class="text-center">${product.stock}</td>
            </tr>
        `;
    });
    
    $('#productsTableBody').html(html);
}

function filterTable() {
    const searchTerm = $('#productSearch').val().toLowerCase();
    
    if (searchTerm === '') {
        renderProductsTable(productsData);
        return;
    }
    
    const filteredData = productsData.filter(product => 
        product.name.toLowerCase().includes(searchTerm) ||
        product.category_name.toLowerCase().includes(searchTerm)
    );
    
    renderProductsTable(filteredData);
}

function createTopProductsChart(data) {
    const ctx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.map(item => item.name.length > 20 ? item.name.substring(0, 20) + '...' : item.name),
            datasets: [{
                label: 'Số lượng bán',
                data: data.map(item => item.total_sold),
                backgroundColor: 'rgba(54, 162, 235, 0.8)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            const item = data[context.dataIndex];
                            return 'Doanh thu: ' + formatCurrency(item.revenue);
                        }
                    }
                }
            }
        }
    });
}

function createCategoryRevenueChart(data) {
    const ctx = document.getElementById('categoryRevenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(item => item.name),
            datasets: [{
                data: data.map(item => item.revenue),
                backgroundColor: [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
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
                            return context.label + ': ' + formatCurrency(context.parsed);
                        }
                    }
                }
            }
        }
    });
}

function createPerformanceChart(data) {
    // Create scatter plot: Revenue vs Quantity Sold
    const ctx = document.getElementById('performanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'scatter',
        data: {
            datasets: [{
                label: 'Sản phẩm',
                data: data.map(product => ({
                    x: product.total_sold,
                    y: product.total_revenue,
                    label: product.name
                })),
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                borderColor: 'rgba(255, 99, 132, 1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Số lượng bán'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Doanh thu'
                    },
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
                        title: function(context) {
                            return context[0].raw.label;
                        },
                        label: function(context) {
                            return [
                                'Số lượng: ' + context.parsed.x,
                                'Doanh thu: ' + formatCurrency(context.parsed.y)
                            ];
                        }
                    }
                }
            }
        }
    });
}

function exportData() {
    const formData = new URLSearchParams({
        start_date: $('#start_date').val(),
        end_date: $('#end_date').val(),
        category_id: $('#category_id').val() || ''
    });
    
    window.open('/admin/statistics/products/export/excel?' + formData.toString(), '_blank');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}
</script>
@endpush
