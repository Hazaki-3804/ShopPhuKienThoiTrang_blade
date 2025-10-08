<div class="row">
    <!-- Left Column - Order Details -->
    <div class="col-lg-8">
        <!-- Order Info -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>Thông tin đơn hàng
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold">Mã đơn hàng:</td>
                                <td>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Khách hàng:</td>
                                <td>{{ $order->user ? $order->user->name : 'Khách vãng lai' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Email:</td>
                                <td>{{ $order->user ? $order->user->email : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Số điện thoại:</td>
                                <td>{{ $order->user ? ($order->user->phone ?? 'N/A') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td class="fw-bold">Ngày tạo:</td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Cập nhật:</td>
                                <td>{{ $order->updated_at->format('d/m/Y H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Trạng thái:</td>
                                <td>
                                    <span class="badge bg-{{ $order->status_class }} fs-6">
                                        {{ $order->status_text }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Thanh toán:</td>
                                <td>
                                    @switch($order->payment_method)
                                        @case('cod')
                                            <span class="badge bg-warning"><i class="fas fa-money-bill me-1"></i>COD</span>
                                            @break
                                        @case('bank_transfer')
                                            <span class="badge bg-info"><i class="fas fa-university me-1"></i>Chuyển khoản</span>
                                            @break
                                        @case('credit_card')
                                            <span class="badge bg-primary"><i class="fas fa-credit-card me-1"></i>Thẻ tín dụng</span>
                                            @break
                                        @case('e_wallet')
                                            <span class="badge bg-success"><i class="fas fa-wallet me-1"></i>Ví điện tử</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ ucfirst($order->payment_method) }}</span>
                                    @endswitch
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2 text-success"></i>Địa chỉ giao hàng
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->shipping_address }}</p>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mb-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-shopping-bag me-2 text-warning"></i>Sản phẩm đã đặt
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
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
                                        @if($item->product && $item->product->product_images->count() > 0)
                                            <img src="{{ $item->product->product_images->first()->image_url }}" 
                                                 alt="{{ $item->product->name }}" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $item->product ? $item->product->name : 'Sản phẩm đã xóa' }}</div>
                                            @if($item->product)
                                                <small class="text-muted">ID: {{ $item->product->id }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ number_format($item->price, 0, ',', '.') }} VNĐ</td>
                                <td>
                                    <span class="badge bg-primary">{{ $item->quantity }}</span>
                                </td>
                                <td class="fw-bold text-success">
                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }} VNĐ
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="3" class="text-end">Tổng cộng:</th>
                                <th class="text-success fs-6">
                                    {{ number_format($order->total_price, 0, ',', '.') }} VNĐ
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column - Timeline -->
    <div class="col-lg-4">
        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2 text-primary"></i>Lịch sử đơn hàng
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($timeline as $item)
                        <div class="timeline-item {{ $item['completed'] ? 'completed' : '' }} {{ $item['current'] ?? false ? 'current' : '' }}">
                            <div class="timeline-icon bg-{{ $item['color'] }} text-white">
                                <i class="{{ $item['icon'] }}"></i>
                            </div>
                            <div class="timeline-content">
                                <h6 class="mb-1">{{ $item['title'] }}</h6>
                                <p class="mb-1 text-muted small">{{ $item['description'] }}</p>
                                @if(isset($item['date']))
                                    <small class="text-primary fw-bold">
                                        <i class="fas fa-clock me-1"></i>{{ $item['date'] }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 40px;
    margin-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 25px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    z-index: 1;
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    border-left: 3px solid #dee2e6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.timeline-item.completed .timeline-content {
    border-left-color: #28a745;
    background: #f8fff9;
}

.timeline-item.current .timeline-content {
    border-left-color: #007bff;
    background: #e3f2fd;
    box-shadow: 0 4px 8px rgba(0,123,255,0.1);
}

.timeline-item.completed .timeline-icon {
    background: #28a745;
}

.timeline-item.current .timeline-icon {
    background: #007bff;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.7);
    }
    70% {
        box-shadow: 0 0 0 8px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}
</style>
