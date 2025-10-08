<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .invoice-header {
            border-bottom: 3px solid #ee4d2d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            color: #ee4d2d;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .invoice-title {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .table th {
            background: #ee4d2d;
            color: white;
            font-weight: 600;
        }
        
        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .grand-total {
            font-size: 20px;
            font-weight: bold;
            color: #ee4d2d;
        }
        
        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-6">
                    <div class="company-name">Shop Nàng Thơ</div>
                    <div class="text-muted">Phụ kiện thời trang</div>
                    <div class="mt-2">
                        <small>Địa chỉ: Vĩnh Long, Việt Nam</small><br>
                        <small>Điện thoại: 0123 456 789</small><br>
                        <small>Email: shopnangthoo@gmail.com</small>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <div class="invoice-title">HÓA ĐƠN</div>
                    <div><strong>Mã đơn hàng:</strong> #{{ $order->id }}</div>
                    <div><strong>Ngày:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                    <div><strong>Trạng thái:</strong> 
                        <span class="badge bg-{{ $order->status_class }}">{{ $order->status_text }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="invoice-info">
            <div class="row">
                <div class="col-6">
                    <h6 class="fw-bold mb-2">Thông tin khách hàng</h6>
                    <div><strong>Họ tên:</strong> {{ $order->customer_name }}</div>
                    <div><strong>Email:</strong> {{ $order->customer_email }}</div>
                    <div><strong>Điện thoại:</strong> {{ $order->customer_phone }}</div>
                </div>
                <div class="col-6">
                    <h6 class="fw-bold mb-2">Địa chỉ giao hàng</h6>
                    <div>{{ $order->shipping_address }}</div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Sản phẩm</th>
                    <th class="text-center">Số lượng</th>
                    <th class="text-end">Đơn giá</th>
                    <th class="text-end">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->order_items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-end">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                    <td class="text-end">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}₫</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
            <div class="row mb-2">
                <div class="col-8 text-end"><strong>Tạm tính:</strong></div>
                <div class="col-4 text-end">
                    @php
                        $subtotal = $order->order_items->sum(function($item) {
                            return $item->price * $item->quantity;
                        });
                    @endphp
                    {{ number_format($subtotal, 0, ',', '.') }}₫
                </div>
            </div>
            <div class="row mb-2">
                <div class="col-8 text-end"><strong>Phí vận chuyển:</strong></div>
                <div class="col-4 text-end">30.000₫</div>
            </div>
            <div class="row mb-2">
                <div class="col-8 text-end"><strong>Giảm giá:</strong></div>
                <div class="col-4 text-end text-danger">
                    @php
                        $discount = ($subtotal + 30000) - $order->total_price;
                    @endphp
                    -{{ number_format($discount, 0, ',', '.') }}₫
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-8 text-end"><strong class="grand-total">TỔNG CỘNG:</strong></div>
                <div class="col-4 text-end grand-total">{{ number_format($order->total_price, 0, ',', '.') }}₫</div>
            </div>
            <div class="row mt-2">
                <div class="col-12 text-end">
                    <small class="text-muted">Phương thức thanh toán: 
                        <strong>{{ $order->payment_method === 'momo' ? 'MoMo' : 'COD' }}</strong>
                    </small>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="footer-note">
            <p class="mb-1"><strong>Cảm ơn quý khách đã mua hàng!</strong></p>
            <p class="mb-0 small">Hóa đơn này được tạo tự động từ hệ thống</p>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4 no-print">
            <button onclick="window.print()" class="btn btn-danger btn-lg me-2">
                <i class="bi bi-printer"></i> In hóa đơn
            </button>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary btn-lg">
                Về trang chủ
            </a>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</body>
</html>
