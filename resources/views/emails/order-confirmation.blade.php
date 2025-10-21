<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $order->id }}</title>
    <style>
        /* Lưu ý: nhiều client xóa <style> khi forward. Toàn bộ style quan trọng đã được inline bên dưới. */
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin:0; padding:0; }
    </style>
</head>
<body>
    <div class="email-container" style="max-width:600px;margin:20px auto;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <!-- Header -->
        <div class="email-header" style="background:linear-gradient(135deg,#EE4D2D 0%,#ff6b35 100%);color:#ffffff;padding:30px 20px;text-align:center;">
            <h1 style="margin:0;font-size:24px;">🎉 Đơn hàng của bạn đã được đặt thành công!</h1>
        </div>

        <!-- Body -->
        <div class="email-body" style="padding:30px 20px;">
            <p>Xin chào <strong>{{ $order->customer_name }}</strong>,</p>
            <p>Cảm ơn bạn đã đặt hàng tại <strong>Shop Phụ Kiện Thời Trang</strong>. Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>

            <!-- Thông tin đơn hàng -->
            <div class="order-info" style="background-color:#f8f9fa;border-left:4px solid #EE4D2D;padding:15px;margin-bottom:20px;">
                <p style="margin:5px 0;"><strong>Mã đơn hàng:</strong> #{{ $order->id }}</p>
                <p style="margin:5px 0;"><strong>Ngày đặt hàng:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
                <p style="margin:5px 0;"><strong>Phương thức thanh toán:</strong> 
                    @if($order->payment_method === 'cod')
                        Thanh toán khi nhận hàng (COD)
                    @elseif($order->payment_method === 'momo')
                        Ví điện tử MoMo
                    @elseif($order->payment_method === 'vnpay')
                        VNPay
                    @else
                        {{ $order->payment_method }}
                    @endif
                </p>
            </div>

            <!-- Thông tin giao hàng -->
            <div class="section-title" style="font-size:18px;font-weight:bold;color:#EE4D2D;margin-top:20px;margin-bottom:10px;border-bottom:2px solid #EE4D2D;padding-bottom:5px;">📦 Thông tin giao hàng</div>
            <p style="margin:5px 0;"><strong>Người nhận:</strong> {{ $order->customer_name }}</p>
            <p style="margin:5px 0;"><strong>Số điện thoại:</strong> {{ $order->customer_phone }}</p>
            <p style="margin:5px 0;"><strong>Email:</strong> {{ $order->customer_email }}</p>
            <p style="margin:5px 0;"><strong>Địa chỉ:</strong> {{ $order->shipping_address }}</p>

            <!-- Sản phẩm đã đặt -->
            <div class="section-title" style="font-size:18px;font-weight:bold;color:#EE4D2D;margin-top:20px;margin-bottom:10px;border-bottom:2px solid #EE4D2D;padding-bottom:5px;">🛍️ Sản phẩm đã đặt</div>
            <table class="product-table" width="100%" cellspacing="0" cellpadding="0" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                <thead>
                    <tr>
                        <th align="left" style="padding:12px;border-bottom:1px solid #ddd;background-color:#f8f9fa;font-weight:bold;">Sản phẩm</th>
                        <th align="left" style="padding:12px;border-bottom:1px solid #ddd;background-color:#f8f9fa;font-weight:bold;">Số lượng</th>
                        <th align="left" style="padding:12px;border-bottom:1px solid #ddd;background-color:#f8f9fa;font-weight:bold;">Đơn giá</th>
                        <th align="left" style="padding:12px;border-bottom:1px solid #ddd;background-color:#f8f9fa;font-weight:bold;">Thành tiền</th>
                    </tr>
                </thead>
                <tbody>
                    @php $subtotal = 0; @endphp
                    @foreach($order->order_items as $item)
                        @php
                            $product = $item->product;
                            $itemTotal = $item->price * $item->quantity;
                            $subtotal += $itemTotal;
                        @endphp
                        <tr>
                            <td style="padding:12px;border-bottom:1px solid #ddd;">{{ $product->name ?? 'Sản phẩm' }}</td>
                            <td style="padding:12px;border-bottom:1px solid #ddd;">{{ $item->quantity }}</td>
                            <td style="padding:12px;border-bottom:1px solid #ddd;">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                            <td style="padding:12px;border-bottom:1px solid #ddd;">{{ number_format($itemTotal, 0, ',', '.') }}₫</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Tổng tiền -->
            <div class="total-section" style="background-color:#f8f9fa;padding:15px;border-radius:4px;">
                <div class="total-row" style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span>Tạm tính:</span>
                    <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                </div>
                @if($order->shipping_fee)
                <div class="total-row" style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span>Phí vận chuyển:</span>
                    <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</span>
                </div>
                @endif
                @if($order->insurance_fee)
                <div class="total-row" style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span>Phí bảo hiểm:</span>
                    <span>{{ number_format($order->insurance_fee, 0, ',', '.') }}₫</span>
                </div>
                @endif
                @if($order->discount_amount)
                <div class="total-row" style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span>Giảm giá @if($order->discount_code)({{ $order->discount_code }})@endif:</span>
                    <span style="color:#dc3545;">-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</span>
                </div>
                @endif
                <div class="total-row final" style="display:flex;justify-content:space-between;padding:12px 0;margin-top:8px;border-top:2px solid #EE4D2D;font-size:18px;font-weight:bold;color:#EE4D2D;">
                    <span>Tổng cộng:</span>
                    <span>{{ number_format($order->total_price, 0, ',', '.') }}₫</span>
                </div>
            </div>

            <p style="margin-top: 20px;">Chúng tôi sẽ liên hệ với bạn sớm nhất để xác nhận đơn hàng. Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi.</p>

            <div style="text-align: center;">
                <a href="{{ route('user.orders.show', $order->id) }}" class="button" style="display:inline-block;padding:12px 30px;background-color:#EE4D2D;color:#ffffff !important;text-decoration:none;border-radius:4px;margin-top:20px;font-weight:500;">Xem chi tiết đơn hàng</a>
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer" style="background-color:#f8f9fa;padding:20px;text-align:center;font-size:14px;color:#666;">
            <p style="margin:0 0 6px 0;"><strong>Shop Nàng Thơ - Phụ Kiện Thời Trang</strong></p>
            <p style="margin:0;">Cảm ơn bạn đã tin tưởng và mua sắm tại cửa hàng của chúng tôi!</p>
            <p style="font-size:12px;color:#999;margin-top:10px;">Email này được gửi tự động, vui lòng không trả lời email này.</p>
        </div>
    </div>
</body>
</html>
