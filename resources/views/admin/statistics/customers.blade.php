@extends('layouts.admin')
@section('title', 'Thống kê Khách hàng')
@section('content_header')
<span class="fw-semibold">Thống kê Khách hàng</span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3">
        <h4 class="fw-semibold m-0">Phân tích Khách hàng</h4>
        <x-admin.breadcrumbs :items="[['name' => 'Trang chủ'], ['name' => 'Thống kê'], ['name' => 'Khách hàng']]" />
    </div>

    <!-- Filters -->
    <div class="card m-3">
        <div class="card-header">
            <h5 class="mb-0">Bộ lọc</h5>
        </div>
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc dữ liệu
                        </button>
                        <button type="button" class="btn btn-success" id="exportExcel">
                            <i class="fas fa-file-excel"></i> Excel
                        </button>
                        <button type="button" class="btn btn-danger" id="exportPdf">
                            <i class="fas fa-file-pdf"></i> PDF
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
                <div class="card-body d-flex justify-content-between align-items-center summary-card">
                    <div>
                        <h4 class="mb-0" id="totalCustomers">-</h4>
                        <p class="mb-0">Tổng khách hàng</p>
                    </div>
                    <div class="opacity-75" style="font-size:28px;"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-success text-white">
                <div class="card-body d-flex justify-content-between align-items-center summary-card">
                    <div>
                        <h4 class="mb-0" id="activeCustomers">-</h4>
                        <p class="mb-0">Khách hàng có mua hàng</p>
                    </div>
                    <div class="opacity-75" style="font-size:28px;"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-info text-white">
                <div class="card-body d-flex justify-content-between align-items-center summary-card">
                    <div>
                        <h4 class="mb-0" id="totalRevenue">-</h4>
                        <p class="mb-0">Tổng doanh thu</p>
                    </div>
                    <div class="opacity-75" style="font-size:28px;"><i class="fas fa-coins"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card bg-warning text-white">
                <div class="card-body d-flex justify-content-between align-items-center summary-card">
                    <div>
                        <h6 class="mb-1" id="topSpenderName">-</h6>
                        <h4 class="mb-0" id="topSpenderAmount">-</h4>
                        <p class="mb-0 small">Khách hàng chi tiêu cao nhất</p>
                    </div>
                    <div class="opacity-75" style="font-size:28px;"><i class="fas fa-crown"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Data Table -->
    <div class="card m-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách khách hàng</h5>
            <div>
                <input type="search" id="customerSearch" class="form-control form-control-sm" 
                       placeholder="Tìm kiếm khách hàng..." style="width: 250px;">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle" id="customersTable">
                    <thead class="table-info">
                        <tr>
                            <th style="width:60px;" class="text-center">STT</th>
                            <th>Tên khách hàng</th>
                            <th>Email</th>
                            <th>Ngày đăng ký</th>
                            <th>Số đơn hàng</th>
                            <th>Tổng chi tiêu</th>
                            <th>Giá trị đơn TB</th>
                            <th>Tần suất mua (ngày)</th>
                            <th>Đơn hàng cuối</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <!-- Data will be loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Customer Segmentation Chart -->
    <div class="row m-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Phân khúc khách hàng theo chi tiêu</h5>
                </div>
                <div class="card-body">
                    <canvas id="customerSegmentChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Xu hướng đăng ký khách hàng</h5>
                </div>
                <div class="card-body">
                    <canvas id="customerTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/table.css') }}">
<style>
  /* Đồng bộ kích thước 4 card tổng quan */
  #summaryCards .card .summary-card { min-height: 96px; }
  @media (min-width: 992px) { #summaryCards .card .summary-card { min-height: 104px; } }
  /* Giới hạn vùng text để icon không rớt dòng */
  #summaryCards .card .summary-card > div:first-child { max-width: calc(100% - 44px); }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<script>
let customersData = [];

$(document).ready(function() {
    loadCustomerData();
    
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        loadCustomerData();
    });

    $('#customerSearch').on('keyup', function() {
        filterTable();
    });

    $('#exportExcel').on('click', function() {
        exportData('excel');
    });

    $('#exportPdf').on('click', function() {
        exportData('pdf');
    });
});

function loadCustomerData() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();

    $.ajax({
        url: '/admin/statistics/customers/data',
        data: {
            start_date: startDate,
            end_date: endDate
        },
        beforeSend: function() {
            $('#customersTableBody').html('<tr><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Đang tải...</td></tr>');
        },
        success: function(response) {
            if (response.success) {
                customersData = response.data;
                updateSummaryCards(response.summary);
                renderCustomersTable(response.data);
                createCustomerCharts(response.data);
            }
        },
        error: function() {
            $('#customersTableBody').html('<tr><td colspan="9" class="text-center text-danger">Có lỗi xảy ra khi tải dữ liệu</td></tr>');
        }
    });
}

function updateSummaryCards(summary) {
    $('#totalCustomers').text(summary.total_customers.toLocaleString());
    $('#activeCustomers').text(summary.active_customers.toLocaleString());
    $('#totalRevenue').text(formatCurrency(summary.total_revenue));
    $('#topSpenderName').text(summary.top_spender_name || '-');
    $('#topSpenderAmount').text(formatCurrency(summary.top_spender_amount || 0));
}

function renderCustomersTable(data) {
    let html = '';
    
    data.forEach(function(customer, index) {
        const rank = index + 1;
        const medal = getMedal(rank);
        html += `
            <tr>
                <td class="text-center">${rank} ${medal}</td>
                <td>${customer.name}</td>
                <td>${customer.email}</td>
                <td>${moment(customer.created_at).format('DD/MM/YYYY')}</td>
                <td class="text-center">${customer.total_orders}</td>
                <td class="text-end">${formatCurrency(customer.total_spent)}</td>
                <td class="text-end">${formatCurrency(customer.avg_order_value)}</td>
                <td class="text-center">${customer.purchase_frequency || 0} ngày</td>
                <td>${customer.last_order_date ? moment(customer.last_order_date).format('DD/MM/YYYY') : 'Chưa có'}</td>
            </tr>
        `;
    });
    
    $('#customersTableBody').html(html);
}

function getMedal(rank) {
    if (rank === 1) return '<i class="fas fa-medal" style="color:#FFD700" title="Top 1"></i>';
    if (rank === 2) return '<i class="fas fa-medal" style="color:#C0C0C0" title="Top 2"></i>';
    if (rank === 3) return '<i class="fas fa-medal" style="color:#CD7F32" title="Top 3"></i>';
    return '';
}

function filterTable() {
    const searchTerm = $('#customerSearch').val().toLowerCase();
    
    if (searchTerm === '') {
        renderCustomersTable(customersData);
        return;
    }
    
    const filteredData = customersData.filter(customer => 
        customer.name.toLowerCase().includes(searchTerm) ||
        customer.email.toLowerCase().includes(searchTerm)
    );
    
    renderCustomersTable(filteredData);
}

function createCustomerCharts(data) {
    // Customer segmentation by spending
    const segments = {
        'VIP (>10M)': data.filter(c => c.total_spent > 10000000).length,
        'Trung bình (1M-10M)': data.filter(c => c.total_spent >= 1000000 && c.total_spent <= 10000000).length,
        'Thấp (<1M)': data.filter(c => c.total_spent < 1000000).length
    };

    const segmentCtx = document.getElementById('customerSegmentChart').getContext('2d');
    new Chart(segmentCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(segments),
            datasets: [{
                data: Object.values(segments),
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Customer registration trend (last 12 months)
    const registrationTrend = {};
    data.forEach(customer => {
        const month = moment(customer.created_at).format('YYYY-MM');
        registrationTrend[month] = (registrationTrend[month] || 0) + 1;
    });

    const trendCtx = document.getElementById('customerTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: Object.keys(registrationTrend).sort(),
            datasets: [{
                label: 'Khách hàng mới',
                data: Object.keys(registrationTrend).sort().map(month => registrationTrend[month]),
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
                    beginAtZero: true
                }
            }
        }
    });
}

function exportData(format) {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    
    const url = format === 'excel' 
        ? '/admin/statistics/customers/export/excel'
        : '/admin/statistics/customers/export/pdf';
    
    window.open(`${url}?start_date=${startDate}&end_date=${endDate}`, '_blank');
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}
</script>
@endpush
