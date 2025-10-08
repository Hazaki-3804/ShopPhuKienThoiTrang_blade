@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')

@section('content_header')
<h1>Quản lý đơn hàng</h1>
@stop

@section('content')
@php $map = $statusMap ?? []; @endphp

<div class="card mb-3">
    <div class="card-body">
        <div class="row text-center g-3 g-md-4">
            <div class="col-6 col-md">
                <div class="tile tile-pending d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="tile-title">Chờ xác nhận</div>
                        <div class="tile-value">{{ $counts['pending'] ?? 0 }}</div>
                    </div>
                    <div class="icon-circle ic-pending"><i class="bi bi-clipboard-check"></i></div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="tile tile-processing d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="tile-title">Chờ lấy hàng</div>
                        <div class="tile-value">{{ $counts['processing'] ?? 0 }}</div>
                    </div>
                    <div class="icon-circle ic-processing"><i class="bi bi-box-seam"></i></div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="tile tile-shipped d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="tile-title">Chờ giao hàng</div>
                        <div class="tile-value">{{ $counts['shipped'] ?? 0 }}</div>
                    </div>
                    <div class="icon-circle ic-shipped"><i class="bi bi-truck"></i></div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="tile tile-delivered d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="tile-title">Đã giao</div>
                        <div class="tile-value">{{ $counts['delivered'] ?? 0 }}</div>
                    </div>
                    <div class="icon-circle ic-delivered"><i class="bi bi-check2-circle"></i></div>
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="tile tile-cancelled d-flex align-items-center justify-content-between gap-2">
                    <div>
                        <div class="tile-title">Đã hủy</div>
                        <div class="tile-value">{{ $counts['cancelled'] ?? 0 }}</div>
                    </div>
                    <div class="icon-circle ic-cancelled"><i class="bi bi-x-circle"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <ul class="nav nav-pills gap-2 flex-wrap">
            <li class="nav-item"><a class="nav-link {{ empty($currentStatus) ? 'active' : '' }}" href="{{ route('orders.index') }}">Tất cả ({{ $counts['all'] ?? 0 }})</a></li>
            @foreach($map as $key => $label)
            <li class="nav-item"><a class="nav-link pill-{{ $key }} {{ ($currentStatus === $key) ? 'active' : '' }}" href="{{ route('orders.index', ['status' => $key]) }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a></li>
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
        <table class="table align-middle" id="ordersTable">
            <thead>
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
                        <div class="fw-semibold">{{ $order->customer_name }}</div>
                        <div class="small text-muted">{{ $order->customer_phone }} • {{ $order->customer_email }}</div>
                    </td>
                    <td>
                        @foreach($order->order_items as $index => $item)
                            @if($index < 2)
                                @php
                                    $product = $item->product;
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
                        <form method="POST" action="{{ route('orders.update', $order) }}" class="d-flex gap-2 align-items-center">
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
                <tr><td colspan="7" class="text-center text-muted">Không có đơn hàng</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-2">{{ $orders->withQueryString()->links() }}</div>
    </div>
</div>

@push('styles')
<style>
/* Enhanced tile styling with better shadows and borders */
.tile{ 
    padding: 18px 20px; 
    border-radius: 16px; 
    border: 1px solid rgba(0,0,0,0.06); 
    background: #fff; 
    box-shadow: 0 4px 16px rgba(0,0,0,0.06), 0 2px 4px rgba(0,0,0,0.04);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.tile:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.1), 0 4px 8px rgba(0,0,0,0.06);
}

.tile-title{ 
    color: #64748b; 
    font-weight: 600; 
    font-size: 0.875rem;
    letter-spacing: 0.3px;
    margin-bottom: 4px;
}

.tile-value{ 
    font-size: 1.75rem; 
    font-weight: 800; 
    color: #1e293b;
    line-height: 1.2;
}

.icon-circle{ 
    width: 48px; 
    height: 48px; 
    border-radius: 12px; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    font-size: 1.3rem;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.tile:hover .icon-circle {
    transform: scale(1.1) rotate(5deg);
}

/* Modern gradient backgrounds per status */
.tile-pending{ 
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border-color: #fde68a;
}

.tile-processing{ 
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    border-color: #bfdbfe;
}

.tile-shipped{ 
    background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%);
    border-color: #a5f3fc;
}

.tile-delivered{ 
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-color: #bbf7d0;
}

.tile-cancelled{ 
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-color: #fecaca;
}

/* Enhanced icon styling with better colors and shadows */
.ic-pending{ 
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
}

.ic-processing{ 
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.ic-shipped{ 
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
}

.ic-delivered{ 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.ic-cancelled{ 
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Enhanced nav pills */
.nav-pills .nav-link{ 
    background: #f8f9fa; 
    color: #6c757d;
    border-radius: 10px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.nav-pills .nav-link:hover {
    background: #e9ecef;
    transform: translateY(-1px);
}

.nav-pills .nav-link.active{ 
    background: linear-gradient(135deg, #ff6b35 0%, #EE4D2D 100%);
    color: #ffffff;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(238, 77, 45, 0.3);
}

/* Colored pills per status with gradients */
.nav-pills .pill-pending.active{ 
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
}

.nav-pills .pill-processing.active{ 
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.nav-pills .pill-shipped.active{ 
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.nav-pills .pill-delivered.active{ 
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.nav-pills .pill-cancelled.active{ 
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}
</style>
@endpush
@endsection