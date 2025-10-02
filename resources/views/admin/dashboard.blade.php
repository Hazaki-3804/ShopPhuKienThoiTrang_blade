@extends('layouts.admin')
@section('title', 'Admin Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
<div class="row g-3">
    <!-- Widgets -->
    <div class="col-12 col-md-3">
        <div class="small-box bg-primary widget fade-up">
            <div class="inner">
                <h3>₫ 45M</h3>
                <p>Total Sales</p>
            </div>
            <div class="icon"><i class="fa-solid fa-sack-dollar"></i></div>
            <a href="#" class="small-box-footer">Chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="small-box bg-success widget fade-up">
            <div class="inner">
                <h3>312</h3>
                <p>Total Orders</p>
            </div>
            <div class="icon"><i class="fa-solid fa-receipt"></i></div>
            <a href="#" class="small-box-footer">Chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="small-box bg-info widget fade-up">
            <div class="inner">
                <h3>128</h3>
                <p>New Customers</p>
            </div>
            <div class="icon"><i class="fa-regular fa-user"></i></div>
            <a href="#" class="small-box-footer">Chi tiết <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
    <div class="col-12 col-md-3">
        <div class="small-box bg-warning widget fade-up">
            <div class="inner">
                <h3>5</h3>
                <p>Low Stock</p>
            </div>
            <div class="icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <a href="#" class="small-box-footer">Xem kho <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>

    <div class="col-12 col-lg-8">
        <div class="card">
            <div class="card-header">Sales Over Time</div>
            <div class="card-body">
                <canvas id="salesChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card">
            <div class="card-header">Top Products</div>
            <div class="card-body">
                <canvas id="topProductsChart" height="120"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">Recent Orders</div>
            <div class="card-body table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(range(1,6) as $i)
                        <tr>
                            <td>#ORD{{ 1000 + $i }}</td>
                            <td>Khách {{ $i }}</td>
                            <td><span class="badge bg-success">Completed</span></td>
                            <td>{{ number_format(rand(199,999)*1000,0,',','.') }}₫</td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary">Xem</button>
                                <button class="btn btn-sm btn-outline-secondary">Cập nhật</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Sales',
                    data: [12, 19, 7, 15, 22, 18, 25],
                    borderColor: '#c39bd3',
                    backgroundColor: 'rgba(195,155,211,.2)',
                    tension: .3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true
                    }
                }
            }
        });
    }
    const topCtx = document.getElementById('topProductsChart');
    if (topCtx) {
        new Chart(topCtx, {
            type: 'doughnut',
            data: {
                labels: ['Bags', 'Hats', 'Glasses', 'Bracelets', 'Necklaces'],
                datasets: [{
                    data: [35, 15, 20, 10, 20],
                    backgroundColor: ['#ffd1dc', '#cfe8ff', '#e6d6ff', '#f6ead4', '#c39bd3']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
</script>
@endpush