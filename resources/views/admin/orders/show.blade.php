@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng #' . str_pad($order->id, 6, '0', STR_PAD_LEFT))
@section('content_header')
<span class="fw-semibold"></span>
@stop

@section('content')
<div class="shadow-sm rounded bg-white py-2">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center px-3 mb-3">
        <h4 class="fw-semibold m-0">Chi tiết đơn hàng #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</h4>
        <x-admin.breadcrumbs :items="[
            ['name' => 'Trang chủ'], 
            ['name' => 'Quản lý đơn hàng', 'url' => route('admin.orders.index')], 
            ['name' => 'Chi tiết đơn hàng']
        ]" />
    </div>

    <div class="row px-3">
        <!-- Left Column - Order Details -->
        <div class="col-lg-8">
            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Thông tin đơn hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
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
                                    <td>{{ $order->user ? $order->user->phone : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2 text-success"></i>Địa chỉ giao hàng
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->shipping_address }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-bag me-2 text-warning"></i>Sản phẩm đã đặt
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                                     class="rounded me-3" 
                                                     style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
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
                                    <th class="text-success fs-5">
                                        {{ number_format($order->total_price, 0, ',', '.') }} VNĐ
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Timeline & Actions -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2 text-info"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($order->status !== 'delivered' && $order->status !== 'cancelled')
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                                <i class="fas fa-edit me-1"></i>Cập nhật trạng thái
                            </button>
                        @endif
                        
                        @if($order->status === 'cancelled')
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">
                                <i class="fas fa-trash me-1"></i>Xóa đơn hàng
                            </button>
                        @endif
                        
                        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>In đơn hàng
                        </button>
                        
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>Lịch sử đơn hàng
                    </h5>
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
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Cập nhật trạng thái đơn hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateStatusForm">
                <div class="modal-body">
                    <input type="hidden" id="updateOrderId" value="{{ $order->id }}">
                    <div class="mb-3">
                        <label class="form-label">Trạng thái hiện tại</label>
                        <input type="text" class="form-control" value="{{ $order->status_text }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Trạng thái mới</label>
                        <select class="form-control" id="newStatus" required>
                            <option value="">-- Chọn trạng thái --</option>
                            @if($order->status === 'pending')
                                <option value="processing">Đang xử lý</option>
                                <option value="cancelled">Hủy đơn hàng</option>
                            @elseif($order->status === 'processing')
                                <option value="shipped">Đã giao cho vận chuyển</option>
                                <option value="cancelled">Hủy đơn hàng</option>
                            @elseif($order->status === 'shipped')
                                <option value="delivered">Đã giao thành công</option>
                                <option value="cancelled">Hủy đơn hàng</option>
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú (tùy chọn)</label>
                        <textarea class="form-control" id="statusNote" rows="3" placeholder="Nhập ghi chú về việc thay đổi trạng thái..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Xác nhận xóa đơn hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa đơn hàng <strong>#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong> không?</p>
                <p class="text-danger"><strong>Lưu ý:</strong> Hành động này không thể hoàn tác!</p>
                <input type="hidden" id="deleteOrderId" value="{{ $order->id }}">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteOrder">Xóa đơn hàng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 25px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 20px;
    top: 35px;
    bottom: -25px;
    width: 2px;
    background: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 5px;
    top: 5px;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    z-index: 1;
    border: 3px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    border-left: 4px solid #dee2e6;
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
        box-shadow: 0 0 0 10px rgba(0, 123, 255, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(0, 123, 255, 0);
    }
}

@media print {
    .card-header h5 i,
    .btn,
    .modal,
    .timeline-icon {
        display: none !important;
    }
    
    .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .timeline-content {
        border: 1px solid #ccc !important;
        background: #fff !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Update status form
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            id: $('#updateOrderId').val(),
            status: $('#newStatus').val(),
            note: $('#statusNote').val(),
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        $.post('{{ route("admin.orders.update-status") }}', formData)
            .done(function(response) {
                if (response.success) {
                    $('#updateStatusModal').modal('hide');
                    showAlert('success', response.message);
                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showAlert('danger', response.message);
                }
            })
            .fail(function(xhr) {
                const message = xhr.responseJSON?.message || 'Có lỗi xảy ra!';
                showAlert('danger', message);
            });
    });
    
    // Delete order
    $('#confirmDeleteOrder').on('click', function() {
        const orderId = $('#deleteOrderId').val();
        
        $.ajax({
            url: '{{ route("admin.orders.destroy") }}',
            type: 'DELETE',
            data: {
                id: orderId,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        })
        .done(function(response) {
            if (response.success) {
                $('#deleteOrderModal').modal('hide');
                showAlert('success', response.message);
                // Redirect to orders list after 2 seconds
                setTimeout(function() {
                    window.location.href = '{{ route("admin.orders.index") }}';
                }, 2000);
            } else {
                showAlert('danger', response.message);
            }
        })
        .fail(function(xhr) {
            const message = xhr.responseJSON?.message || 'Có lỗi xảy ra!';
            showAlert('danger', message);
        });
    });
    
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert
        $('body').append(alertHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
    }
});
</script>
@endpush
