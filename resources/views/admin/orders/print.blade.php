<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { font-size: 12px; }
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table th {
            background-color: #007bff;
            color: white;
        }
        .total-section {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 15px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <!-- Print Button -->
        <div class="no-print mb-3">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> In hóa đơn
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                <i class="fas fa-times"></i> Đóng
            </button>
        </div>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="company-info">
                <h2 class="text-primary mb-1">SHOP NÀNG THƠ</h2>
                <p class="mb-1">Phụ kiện thời trang cao cấp</p>
                <p class="mb-1">📍 Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM</p>
                <p class="mb-0">📞 Hotline: 0123.456.789 | 📧 Email: info@shopnangTho.com</p>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <h4 class="text-primary">HÓA ĐƠN BÁN HÀNG</h4>
                    <p class="mb-1"><strong>Số hóa đơn:</strong> #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                    <p class="mb-1"><strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="col-md-6 text-end">
                    <span class="badge status-badge 
                        @switch($order->status)
                            @case('pending') bg-secondary @break
                            @case('processing') bg-warning @break
                            @case('shipped') bg-info @break
                            @case('delivered') bg-success @break
                            @case('cancelled') bg-danger @break
                        @endswitch
                    ">
                        {{ $order->status_text }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="invoice-details">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">Thông tin khách hàng</h5>
                    <p class="mb-1"><strong>Họ tên:</strong> {{ $order->user ? $order->user->name : 'Khách vãng lai' }}</p>
                    <p class="mb-1"><strong>Email:</strong> {{ $order->user ? $order->user->email : 'N/A' }}</p>
                    <p class="mb-1"><strong>Điện thoại:</strong> {{ $order->user ? $order->user->phone : 'N/A' }}</p>
                    <p class="mb-0"><strong>Địa chỉ:</strong> {{ $order->shipping_address ?? ($order->user ? $order->user->address : 'N/A') }}</p>
                </div>
                <div class="col-md-6">
                    <h5 class="text-primary mb-3">Thông tin đơn hàng</h5>
                    <p class="mb-1"><strong>Phương thức thanh toán:</strong> 
                        @switch($order->payment_method)
                            @case('cod') Thanh toán khi nhận hàng @break
                            @case('bank_transfer') Chuyển khoản ngân hàng @break
                            @case('credit_card') Thẻ tín dụng @break
                            @case('e_wallet') Ví điện tử @break
                            @default {{ ucfirst($order->payment_method) }}
                        @endswitch
                    </p>
                    <p class="mb-1"><strong>Trạng thái thanh toán:</strong> 
                        <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-warning' }}">
                            {{ $order->payment_status == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                        </span>
                    </p>
                    @if($order->tracking_number)
                    <p class="mb-0"><strong>Mã vận đơn:</strong> {{ $order->tracking_number }}</p>
                    @endif
                </div>
            </div>
            
            @if($order->notes)
            <div class="mt-3">
                <h6 class="text-primary">Ghi chú:</h6>
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Order Items -->
        <div class="mb-4">
            <h5 class="text-primary mb-3">Chi tiết sản phẩm</h5>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th>Sản phẩm</th>
                            <th width="100" class="text-center">Số lượng</th>
                            <th width="120" class="text-end">Đơn giá</th>
                            <th width="120" class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->order_items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($item->product && $item->product->product_images->count() > 0)
                                    <img src="{{ asset($item->product->product_images->first()->image_url) }}" 
                                         alt="{{ $item->product_name }}" 
                                         class="me-3" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    @endif
                                    <div>
                                        <strong>{{ $item->product_name }}</strong>
                                        @if($item->product_variant)
                                        <br><small class="text-muted">{{ $item->product_variant }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                            <td class="text-end">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}₫</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Total Section -->
        <div class="row">
            <div class="col-md-6">
                <!-- Empty space for balance -->
            </div>
            <div class="col-md-6">
                <div class="total-section">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span>{{ number_format($order->order_items->sum(function($item) { return $item->price * $item->quantity; }), 0, ',', '.') }}₫</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Giảm giá:</span>
                        <span class="text-danger">-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</span>
                    </div>
                    @endif
                    @if($order->shipping_fee > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</span>
                    </div>
                    @endif
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong class="fs-5">Tổng cộng:</strong>
                        <strong class="fs-5 text-primary">{{ number_format($order->total_price, 0, ',', '.') }}₫</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-5 pt-4 border-top text-center">
            <p class="mb-1"><strong>Cảm ơn quý khách đã mua hàng tại Shop Nàng Thơ!</strong></p>
            <p class="mb-0 text-muted">Mọi thắc mắc xin liên hệ hotline: 0123.456.789</p>
        </div>
    </div>

    <script>
        // Auto print when page loads (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
