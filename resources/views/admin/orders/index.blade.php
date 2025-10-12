@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')

@section('content_header')
<h1>Quản lý đơn hàng</h1>
@stop

@section('content')
@php $map = $statusMap ?? []; @endphp

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-12 col-sm-6 col-lg">
        <div class="stats-card stats-pending">
            <div class="stats-content">
                <div class="stats-number">{{ $counts['pending'] ?? 0 }}</div>
                <div class="stats-label">Chờ xác nhận</div>
            </div>
            <div class="stats-icon">
                <i class="bi bi-clipboard-check"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="stats-card stats-processing">
            <div class="stats-content">
                <div class="stats-number">{{ $counts['processing'] ?? 0 }}</div>
                <div class="stats-label">Chờ lấy hàng</div>
            </div>
            <div class="stats-icon">
                <i class="bi bi-box-seam"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="stats-card stats-shipped">
            <div class="stats-content">
                <div class="stats-number">{{ $counts['shipped'] ?? 0 }}</div>
                <div class="stats-label">Chờ giao hàng</div>
            </div>
            <div class="stats-icon">
                <i class="bi bi-truck"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="stats-card stats-delivered">
            <div class="stats-content">
                <div class="stats-number">{{ $counts['delivered'] ?? 0 }}</div>
                <div class="stats-label">Đã giao</div>
            </div>
            <div class="stats-icon">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg">
        <div class="stats-card stats-cancelled">
            <div class="stats-content">
                <div class="stats-number">{{ $counts['cancelled'] ?? 0 }}</div>
                <div class="stats-label">Đã hủy</div>
            </div>
            <div class="stats-icon">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body p-0">
        <div class="card-footer">
            <ul class="nav nav-pills-custom gap-2 flex-wrap">
                <li class="nav-item"><a class="nav-link {{ empty($currentStatus) ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">Tất cả ({{ $counts['all'] ?? 0 }})</a></li>
                @foreach($map as $key => $label)
                <li class="nav-item"><a class="nav-link pill-{{ $key }} {{ ($currentStatus === $key) ? 'active' : '' }}" href="{{ route('admin.orders.index', ['status' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Danh sách đơn hàng</span>
            <form method="GET" class="d-flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Tìm theo mã, email, SĐT..." style="max-width: 260px;">
                @if($currentStatus)
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                @endif
                <button class="btn btn-outline-secondary btn-sm">Tìm</button>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle w-100" id="ordersTable">
                <thead class="table-info">
                    <tr>
                        <th>STT</th>
                        <th>Khách hàng</th>
                        <th style="min-width: 250px;">Sản phẩm</th>
                        <th>Trạng thái</th>
                        <th>Tổng tiền</th>
                        <th>Ngày tạo</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <div class="fw-semibold">{{ $order->customer_name }}</div>
                                <div class="small text-muted">{{ $order->customer_phone }} • {{ $order->customer_email }}</div>
                            </div>
                        </td>
                        <td>
                            @foreach($order->order_items as $index => $item)
                            @if($index < 2)
                                @php
                                $product=$item->product;
                                $img = optional($product->product_images->first())->image_url ?? null;
                                if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                                $img = asset($img);
                                }
                                $img = $img ?: 'https://via.placeholder.com/50';
                                @endphp
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <img src="{{ $img }}" class="rounded border" style="width:50px;height:50px;object-fit:cover;" alt="{{ $product->name ?? '' }}">
                                    <div class="small">
                                        <div class="text-truncate" style="max-width: 180px;">{{ $product->name ?? 'Sản phẩm' }}</div>
                                        <div class="text-muted">Số lượng: <strong>{{ $item->quantity }}</strong></div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                                @if($order->order_items->count() > 2)
                                <div class="small text-muted">+{{ $order->order_items->count() - 2 }} sản phẩm khác</div>
                                @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="min-width:160px;">
                                    @foreach($map as $key => $label)
                                    <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary">Lưu</button>
                            </form>
                        </td>
                        <td>{{ number_format($order->total_price,0,',','.') }}₫</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if (Route::has('invoice.show'))
                            <a href="{{ route('invoice.show', $order->id) }}" class="btn btn-sm btn-outline-secondary">In hóa đơn</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">Không có đơn hàng</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-2">{{ $orders->withQueryString()->links() }}</div>
        </div>
    </div>

    @push('styles')
    <style>
        /* Modern Stats Cards */
        .stats-card {
            background: #fff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 100px;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .stats-content {
            flex: 1;
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 4px;
            color: #fff;
        }

        .stats-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
        }

        /* Color variants */
        .stats-pending {
            background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%);
        }

        .stats-processing {
            background: linear-gradient(135deg, #42A5F5 0%, #2196F3 100%);
        }

        .stats-shipped {
            background: linear-gradient(135deg, #26C6DA 0%, #00BCD4 100%);
        }

        .stats-delivered {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
        }

        .stats-cancelled {
            background: linear-gradient(135deg, #EF5350 0%, #F44336 100%);
        }

        /* Enhanced nav pills */
        .nav-pills-custom .nav-link {
            background: #f8f9fa;
            color: #6c757d;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-pills-custom .nav-link:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        .nav-pills-custom .nav-link.active {
            background: linear-gradient(135deg, #ff6b35 0%, #EE4D2D 100%);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
        }

        /* Colored pills per status matching stats cards */
        .nav-pills-custom .pill-pending.active {
            background: linear-gradient(135deg, #FFA726 0%, #FF9800 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(255, 167, 38, 0.3);
        }

        .nav-pills-custom .pill-processing.active {
            background: linear-gradient(135deg, #42A5F5 0%, #2196F3 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(66, 165, 245, 0.3);
        }

        .nav-pills-custom .pill-shipped.active {
            background: linear-gradient(135deg, #26C6DA 0%, #00BCD4 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(38, 198, 218, 0.3);
        }

        .nav-pills-custom .pill-delivered.active {
            background: linear-gradient(135deg, #66BB6A 0%, #4CAF50 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(102, 187, 106, 0.3);
        }

        .nav-pills-custom .pill-cancelled.active {
            background: linear-gradient(135deg, #EF5350 0%, #F44336 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(239, 83, 80, 0.3);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/table.css') }}">
    @endpush
    @endsection