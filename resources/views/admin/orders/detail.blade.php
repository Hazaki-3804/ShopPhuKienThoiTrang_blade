@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng #' . $order->id)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Chi tiết đơn hàng #{{ $order->id }}</h1>
    <a href="{{ route('orders.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>
@stop

@section('content')
<div class="row">
    <!-- Order Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin đơn hàng</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Mã đơn hàng:</strong></div>
                    <div class="col-sm-9">#{{ $order->id }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Ngày đặt:</strong></div>
                    <div class="col-sm-9">{{ $order->created_at->format('d/m/Y H:i:s') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Trạng thái:</strong></div>
                    <div class="col-sm-9">
                        @php
                            $statusMap = [
                                'pending' => ['Chờ xác nhận', 'warning'],
                                'processing' => ['Chờ lấy hàng', 'info'],
                                'shipped' => ['Chờ giao hàng', 'primary'],
                                'delivered' => ['Đã giao', 'success'],
                                'cancelled' => ['Đã hủy', 'danger']
                            ];
                            [$label, $color] = $statusMap[$order->status] ?? ['Unknown', 'secondary'];
                        @endphp
                        <span class="badge bg-{{ $color }} fs-6">{{ $label }}</span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-3"><strong>Tổng tiền:</strong></div>
                    <div class="col-sm-9">
                        <span class="h5 text-primary">{{ number_format($order->total_price, 0, ',', '.') }}₫</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Sản phẩm đã đặt</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Đơn giá</th>
                                <th>Số lượng</th>
                                <th>Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->order_items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @php
                                            $product = $item->product;
                                            $img = optional($product->product_images->first())->image_url ?? null;
                                            if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                                                $img = asset($img);
                                            }
                                            $img = $img ?: 'https://via.placeholder.com/80';
                                        @endphp
                                        <img src="{{ $img }}" class="rounded border me-3" style="width:80px;height:80px;object-fit:cover;" alt="{{ $product->name ?? '' }}">
                                        <div>
                                            <h6 class="mb-1">{{ $product->name ?? 'Sản phẩm' }}</h6>
                                            @if($product->description)
                                                <small class="text-muted">{{ Str::limit($product->description, 100) }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($item->price, 0, ',', '.') }}₫</td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $item->quantity }}</span>
                                </td>
                                <td>
                                    <strong>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}₫</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-active">
                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                <th class="text-primary">{{ number_format($order->total_price, 0, ',', '.') }}₫</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin khách hàng</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Họ tên:</strong><br>
                    {{ $order->customer_name }}
                </div>
                <div class="mb-3">
                    <strong>Email:</strong><br>
                    <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a>
                </div>
                <div class="mb-3">
                    <strong>Số điện thoại:</strong><br>
                    <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                </div>
                @if($order->customer_address)
                <div class="mb-3">
                    <strong>Địa chỉ giao hàng:</strong><br>
                    {{ $order->customer_address }}
                </div>
                @endif
                @if($order->notes)
                <div class="mb-3">
                    <strong>Ghi chú:</strong><br>
                    <em>{{ $order->notes }}</em>
                </div>
                @endif
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Tiến trình đơn hàng</h5>
            </div>
            <div class="card-body">
                @php
                    $statusFlow = [
                        'pending' => ['label' => 'Chờ xác nhận', 'icon' => 'fas fa-clock', 'color' => 'warning'],
                        'processing' => ['label' => 'Chờ lấy hàng', 'icon' => 'fas fa-box', 'color' => 'info'],
                        'shipped' => ['label' => 'Chờ giao hàng', 'icon' => 'fas fa-truck', 'color' => 'primary'],
                        'delivered' => ['label' => 'Đã giao', 'icon' => 'fas fa-check-circle', 'color' => 'success'],
                        'cancelled' => ['label' => 'Đã hủy', 'icon' => 'fas fa-times-circle', 'color' => 'danger']
                    ];
                    
                    $currentStatusIndex = array_search($order->status, array_keys($statusFlow));
                    $isCancelled = $order->status === 'cancelled';
                @endphp

                <div class="timeline">
                    @foreach($statusFlow as $status => $info)
                        @if($status === 'cancelled')
                            @continue
                        @endif
                        @php
                            $statusIndex = array_search($status, array_keys($statusFlow));
                            $isActive = $statusIndex <= $currentStatusIndex && !$isCancelled;
                            $isCurrent = $status === $order->status;
                        @endphp
                        
                        <div class="timeline-item {{ $isActive ? 'active' : '' }} {{ $isCurrent ? 'current' : '' }}">
                            <div class="timeline-marker">
                                <i class="{{ $info['icon'] }} {{ $isActive ? 'text-' . $info['color'] : 'text-muted' }}"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title {{ $isActive ? 'text-' . $info['color'] : 'text-muted' }}">
                                    {{ $info['label'] }}
                                </h6>
                                @if($isCurrent)
                                    <small class="text-muted">{{ $order->updated_at->format('d/m/Y H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    
                    @if($isCancelled)
                        <div class="timeline-item active current cancelled">
                            <div class="timeline-marker">
                                <i class="fas fa-times-circle text-danger"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="timeline-title text-danger">Đã hủy</h6>
                                <small class="text-muted">{{ $order->updated_at->format('d/m/Y H:i') }}</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Thao tác</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('orders.update', $order) }}" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="mb-3">
                        <label class="form-label">Cập nhật trạng thái:</label>
                        <select name="status" class="form-select">
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Chờ lấy hàng</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Chờ giao hàng</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Đã giao</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save"></i> Cập nhật trạng thái
                    </button>
                </form>

                @if (Route::has('invoice.show'))
                <a href="{{ route('invoice.show', $order->id) }}" class="btn btn-outline-secondary w-100 mb-2">
                    <i class="fas fa-print"></i> In hóa đơn
                </a>
                @endif

                <button type="button" class="btn btn-outline-info w-100" onclick="window.print()">
                    <i class="fas fa-print"></i> In chi tiết đơn hàng
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding: 0;
    }

    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 30px;
        border-left: 2px solid #e9ecef;
    }

    .timeline-item:last-child {
        border-left: 2px solid transparent;
        padding-bottom: 0;
    }

    .timeline-item.active {
        border-left-color: #28a745;
    }

    .timeline-item.cancelled {
        border-left-color: #dc3545;
    }

    .timeline-marker {
        position: absolute;
        left: -12px;
        top: 0;
        width: 24px;
        height: 24px;
        background: #fff;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        z-index: 1;
    }

    .timeline-item.active .timeline-marker {
        border-color: #28a745;
        background: #28a745;
        color: white;
    }

    .timeline-item.cancelled .timeline-marker {
        border-color: #dc3545;
        background: #dc3545;
        color: white;
    }

    .timeline-item.current .timeline-marker {
        animation: pulse 2s infinite;
        box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
    }

    .timeline-item.cancelled.current .timeline-marker {
        animation: pulse-red 2s infinite;
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    @keyframes pulse-red {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
        }
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }

    .timeline-content {
        padding-top: 2px;
    }

    .timeline-title {
        margin-bottom: 5px;
        font-weight: 600;
    }

    .timeline-item:not(.active) .timeline-title {
        color: #6c757d !important;
    }

    .timeline-item:not(.active) .timeline-marker {
        background: #f8f9fa;
        color: #6c757d;
    }

    @media print {
        .btn, .card-header, .sidebar, .navbar {
            display: none !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        .timeline-item.current .timeline-marker {
            animation: none;
        }
    }
</style>
@endpush

@endsection
