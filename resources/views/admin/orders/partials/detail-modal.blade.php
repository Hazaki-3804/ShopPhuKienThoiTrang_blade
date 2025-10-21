<div class="order-detail-content">
    <div class="row">
        <!-- Thông tin đơn hàng -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Thông tin đơn hàng</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Mã đơn hàng:</strong></div>
                        <div class="col-sm-8">#{{ $order->id }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Ngày đặt:</strong></div>
                        <div class="col-sm-8">{{ $order->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                        <div class="col-sm-8">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                                $color = $statusColors[$order->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">{{ $statusMap[$order->status] ?? $order->status }}</span>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-4"><strong>Phương thức thanh toán:</strong></div>
                        <div class="col-sm-8">
                            @if($order->payment_method === 'cod')
                                <span class="badge bg-warning text-dark">Thanh toán khi nhận hàng (COD)</span>
                            @elseif($order->payment_method === 'momo')
                                <span class="badge bg-danger">Ví điện tử MoMo</span>
                            @elseif($order->payment_method === 'vnpay')
                                <span class="badge bg-primary">VNPay</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($order->payment_method) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4"><strong>Tổng tiền:</strong></div>
                        <div class="col-sm-8">
                            <span class="h5 text-primary mb-0">{{ number_format($order->total_price, 0, ',', '.') }}₫</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thông tin khách hàng -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Thông tin khách hàng</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Họ tên:</strong><br>
                        {{ $order->customer_name }}
                    </div>
                    <div class="mb-2">
                        <strong>Email:</strong><br>
                        <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a>
                    </div>
                    <div class="mb-2">
                        <strong>Số điện thoại:</strong><br>
                        <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                    </div>
                    <div class="mb-0">
                        <strong>Địa chỉ giao hàng:</strong><br>
                        {{ $order->shipping_address }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sản phẩm đã đặt -->
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-box mr-2"></i>Sản phẩm đã đặt</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subtotal = 0; @endphp
                                @foreach($order->order_items as $item)
                                    @php
                                        $product = $item->product;
                                        $img = optional($product->product_images->first())->image_url ?? null;
                                        if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) {
                                            $img = asset($img);
                                        }
                                        $img = $img ?: 'https://via.placeholder.com/80';
                                        $itemTotal = $item->price * $item->quantity;
                                        $subtotal += $itemTotal;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $img }}" class="rounded border mr-2" style="width:60px;height:60px;object-fit:cover;" alt="{{ $product->name ?? '' }}">
                                                <div>
                                                    <h6 class="mb-0">{{ $product->name ?? 'Sản phẩm' }}</h6>
                                                    @if($product->description)
                                                        <small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-right align-middle">{{ number_format($item->price, 0, ',', '.') }}₫</td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-light text-dark">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="text-right align-middle">
                                            <strong>{{ number_format($itemTotal, 0, ',', '.') }}₫</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="3" class="text-right"><strong>Tạm tính:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong></td>
                                </tr>
                                @if($order->shipping_fee)
                                <tr class="table-light">
                                    <td colspan="3" class="text-right"><strong>Phí vận chuyển:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</strong></td>
                                </tr>
                                @endif
                                @if($order->insurance_fee)
                                <tr class="table-light">
                                    <td colspan="3" class="text-right"><strong>Phí bảo hiểm:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($order->insurance_fee, 0, ',', '.') }}₫</strong></td>
                                </tr>
                                @endif
                                @if($order->discount_amount)
                                <tr class="table-light">
                                    <td colspan="3" class="text-right">
                                        <strong>Giảm giá:</strong>
                                        @if($order->discount_code)
                                            <span class="badge badge-danger ml-1">{{ $order->discount_code }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-danger"><strong>-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</strong></td>
                                </tr>
                                @endif
                                <tr class="table-success">
                                    <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                    <td class="text-right"><strong class="text-primary h5">{{ number_format($order->total_price, 0, ',', '.') }}₫</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
