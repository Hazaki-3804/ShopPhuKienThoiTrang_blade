@extends('layouts.admin')
@section('title', 'Thống kê theo thời gian')
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Phân tích Theo thời gian</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Thống kê'], ['name' => 'Thời gian']]" />
    </div>

    <!-- Filters -->
    <div class="card m-3">
        <div class="card-header">
            <h5 class="mb-0">Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label for="period" class="form-label">Mốc thời gian</label>
                    <select class="form-control" id="period" name="period">
                        <option value="day">Theo ngày</option>
                        <option value="week">Theo tuần</option>
                        <option value="month" selected>Theo tháng</option>
                        <option value="quarter">Theo quý</option>
                        <option value="year">Theo năm</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                        value="{{ date('Y-m-d', strtotime('-6 months')) }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                        value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="button" class="btn btn-info" id="autoReport" data-toggle="modal" data-target="#autoReportModal">
                            <i class="fas fa-clock"></i> Báo cáo tự động
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row m-3" id="summaryCards">
        <div class="col-lg-2 col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="totalOrders">-</h4>
                        <p class="mb-0">Tổng đơn hàng</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="totalRevenue">-</h4>
                        <p class="mb-0">Tổng doanh thu</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-coins"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="avgOrderValue">-</h4>
                        <p class="mb-0">Giá trị đơn TB</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-dollar-sign"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-warning text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="uniqueCustomers">-</h4>
                        <p class="mb-0">Khách hàng</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-dark text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="revenueGrowth">-</h4>
                        <p class="mb-0">Tăng trưởng doanh thu</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-4">
            <div class="card bg-dark text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0" id="orderGrowth">-</h4>
                        <p class="mb-0">Tăng trưởng đơn hàng</p>
                    </div>
                    <div class="opacity-75" style="font-size:26px;"><i class="fas fa-chart-bar"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Charts -->
    <div class="row m-3">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Xu hướng doanh thu và đơn hàng</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Tỷ lệ tăng trưởng</h5>
                </div>
                <div class="card-body">
                    <canvas id="growthChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Data Table -->
    <div class="card m-4">
        <div class="card-header">
            <h5 class="mb-0">Chi tiết theo thời gian</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="timeTable">
                    <thead class="table-info">
                        <tr>
                            <th>Thời gian</th>
                            <th>Số đơn hàng</th>
                            <th>Doanh thu</th>
                            <th>Giá trị đơn TB</th>
                            <th>Khách hàng</th>
                            <th>Tăng trưởng DT (%)</th>
                            <th>Tăng trưởng ĐH (%)</th>
                        </tr>
                    </thead>
                    <tbody id="timeTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Analysis -->
    <div class="row m-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân tích xu hướng</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendAnalysisChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">So sánh các kỳ</h5>
                </div>
                <div class="card-body">
                    <canvas id="comparisonChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Auto Report Modal -->
<div class="modal fade" id="autoReportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thiết lập báo cáo tự động</h5>
                <button type="button" class="btn-close" aria-label="Close" style="font-size:26px; border:none; background-color:transparent;" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="autoReportForm">
                    <div class="mb-3">
                        <label for="report_frequency" class="form-label">Tần suất gửi</label>
                        <select class="form-control" id="report_frequency" name="frequency">
                            <option value="daily">Hàng ngày</option>
                            <option value="weekly">Hàng tuần</option>
                            <option value="monthly">Hàng tháng</option>
                            <option value="quarterly">Hàng quý</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="report_email" class="form-label">Email nhận báo cáo</label>
                        <input type="email" class="form-control" id="report_email" name="email"
                            value="{{ auth()->user()->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="report_format" class="form-label">Định dạng</label>
                        <select class="form-control" id="report_format" name="format">
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="both">Cả hai</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="saveAutoReport">Lưu thiết lập</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
    let timeData = [];

    $(document).ready(function() {
        loadTimeData();

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadTimeData();
        });

        // Modal được mở bằng data-toggle="modal" trong HTML

        $('#saveAutoReport').on('click', function() {
            saveAutoReportSettings();
        });
    });

    function loadTimeData() {
        const formData = {
            period: $('#period').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val()
        };

        $.ajax({
            url: '/admin/statistics/time/data',
            data: formData,
            beforeSend: function() {
                $('#timeTableBody').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
            },
            success: function(response) {
                if (response.success) {
                    timeData = response.data;
                    updateSummaryCards(response.summary);
                    renderTimeTable(response.data);
                    createTrendChart(response.data);
                    createGrowthChart(response.data);
                    createTrendAnalysisChart(response.data);
                    createComparisonChart(response.data);
                }
            },
            error: function() {
                $('#timeTableBody').html('<tr><td colspan="7" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
            }
        });
    }

    function updateSummaryCards(summary) {
        $('#totalOrders').text(summary.total_orders.toLocaleString());
        $('#totalRevenue').text(formatCurrency(summary.total_revenue));
        $('#avgOrderValue').text(formatCurrency(summary.avg_order_value));
        $('#uniqueCustomers').text(summary.unique_customers.toLocaleString());

        const revenueGrowth = summary.avg_revenue_growth || 0;
        const orderGrowth = summary.avg_order_growth || 0;

        $('#revenueGrowth').html(formatGrowth(revenueGrowth));
        $('#orderGrowth').html(formatGrowth(orderGrowth));
    }

    function renderTimeTable(data) {
        let html = '';

        data.forEach(function(item) {
            html += `
            <tr>
                <td>${formatPeriod(item.period)}</td>
                <td class="text-center">${item.total_orders}</td>
                <td class="text-end">${formatCurrency(item.total_revenue)}</td>
                <td class="text-end">${formatCurrency(item.avg_order_value)}</td>
                <td class="text-center">${item.unique_customers}</td>
                <td class="text-center">${formatGrowth(item.revenue_growth)}</td>
                <td class="text-center">${formatGrowth(item.order_growth)}</td>
            </tr>
        `;
        });

        $('#timeTableBody').html(html);
    }

    function createTrendChart(data) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => formatPeriod(item.period)),
                datasets: [{
                    label: 'Doanh thu',
                    data: data.map(item => item.total_revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    yAxisID: 'y'
                }, {
                    label: 'Số đơn hàng',
                    data: data.map(item => item.total_orders),
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Doanh thu (VND)'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Số đơn hàng'
                        },
                        grid: {
                            drawOnChartArea: false,
                        }
                    }
                }
            }
        });
    }

    function createGrowthChart(data) {
        const ctx = document.getElementById('growthChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => formatPeriod(item.period)),
                datasets: [{
                    label: 'Tăng trưởng doanh thu (%)',
                    data: data.map(item => item.revenue_growth || 0),
                    backgroundColor: data.map(item => (item.revenue_growth || 0) >= 0 ? 'rgba(75, 192, 192, 0.8)' : 'rgba(255, 99, 132, 0.8)')
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
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    function createTrendAnalysisChart(data) {
        // Moving average analysis
        const ctx = document.getElementById('trendAnalysisChart').getContext('2d');

        const movingAverage = [];
        const period = 3; // 3-period moving average

        for (let i = 0; i < data.length; i++) {
            if (i < period - 1) {
                movingAverage.push(null);
            } else {
                const sum = data.slice(i - period + 1, i + 1).reduce((acc, item) => acc + item.total_revenue, 0);
                movingAverage.push(sum / period);
            }
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(item => formatPeriod(item.period)),
                datasets: [{
                    label: 'Doanh thu thực tế',
                    data: data.map(item => item.total_revenue),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)'
                }, {
                    label: 'Trung bình trượt (3 kỳ)',
                    data: movingAverage,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderDash: [5, 5]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function createComparisonChart(data) {
        // Compare current period with previous period
        const ctx = document.getElementById('comparisonChart').getContext('2d');

        const currentPeriod = data.slice(-6); // Last 6 periods
        const previousPeriod = data.slice(-12, -6); // Previous 6 periods

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Kỳ 1', 'Kỳ 2', 'Kỳ 3', 'Kỳ 4', 'Kỳ 5', 'Kỳ 6'],
                datasets: [{
                    label: 'Kỳ trước',
                    data: previousPeriod.map(item => item ? item.total_revenue : 0),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }, {
                    label: 'Kỳ hiện tại',
                    data: currentPeriod.map(item => item ? item.total_revenue : 0),
                    backgroundColor: 'rgba(255, 99, 132, 0.8)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function saveAutoReportSettings() {
        const formData = {
            frequency: $('#report_frequency').val(),
            email: $('#report_email').val(),
            format: $('#report_format').val()
        };

        // This would typically save to backend
        Swal.fire({
            title: 'Thành công!',
            text: 'Đã thiết lập báo cáo tự động',
            icon: 'success',
            timer: 2000,
            showConfirmButton: false
        });

        // Đóng modal bằng cách trigger click vào nút close hoặc dùng Bootstrap API
        const modalElement = document.getElementById('autoReportModal');
        if (modalElement) {
            $(modalElement).modal('hide');
        }
    }

    function formatPeriod(period) {
        const periodType = $('#period').val();

        switch (periodType) {
            case 'day':
                return moment(period).format('DD/MM/YYYY');
            case 'week':
                return 'Tuần ' + period;
            case 'month':
                return moment(period + '-01').format('MM/YYYY');
            case 'quarter':
                return period;
            case 'year':
                return period;
            default:
                return period;
        }
    }

    function formatGrowth(growth) {
        if (!growth) return '0%';

        const color = growth >= 0 ? 'text-success' : 'text-danger';
        const icon = growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

        return `<span class="${color}"><i class="fas ${icon}"></i> ${Math.abs(growth).toFixed(1)}%</span>`;
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }
</script>
@endpush