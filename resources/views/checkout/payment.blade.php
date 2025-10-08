@extends('layouts.app')
@section('title', 'Thanh toán')

@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div class="container d-flex align-items-center py-3">
            <a href="{{ route('checkout.index') }}" class="text-decoration-none text-dark me-3">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h5 class="mb-0 fw-semibold">Thanh toán</h5>
        </div>
    </div>

    <div class="payment-body bg-light">
        <div class="container py-3">
            <!-- Địa chỉ giao hàng -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-geo-alt text-danger fs-5 me-3 mt-1"></i>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <strong>{{ $addressData['customer_name'] }}</strong>
                                    <span class="text-muted ms-2">{{ $addressData['customer_phone'] }}</span>
                                </div>
                                <a href="{{ route('checkout.index') }}" class="text-decoration-none">
                                    <i class="bi bi-chevron-right text-muted"></i>
                                </a>
                            </div>
                            <div class="text-muted small">
                                {{ $addressData['customer_address'] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách sản phẩm -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="badge bg-danger me-2">Mall</span>
                        <strong>Cô Mèm Official Store</strong>
                    </div>
                    
                    @foreach($items as $item)
                        @php
                            $p = $item['product'];
                            $img = optional($p->product_images[0] ?? null)->image_url ?? null;
                            if ($img && !\Illuminate\Support\Str::startsWith($img, ['http://','https://','/'])) { 
                                $img = asset($img); 
                            }
                            $img = $img ?: 'https://picsum.photos/80/80?random=' . ($p->id ?? 1);
                        @endphp
                        <div class="product-item d-flex align-items-center mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <img src="{{ $img }}" alt="{{ $p->name }}" class="product-thumb rounded me-3">
                            <div class="flex-grow-1">
                                <div class="product-name mb-1">{{ $p->name }}</div>
                                <div class="text-muted small">{{ $p->description ? Str::limit($p->description, 50) : '' }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-danger fw-semibold">{{ number_format($item['price'], 0, ',', '.') }}₫</div>
                                <div class="text-muted small">x{{ $item['qty'] }}</div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Bảo hiểm (optional) -->
                    <div class="insurance-section py-3 border-top">
                        <div class="d-flex align-items-start">
                            <input type="checkbox" class="form-check-input me-3 mt-1" id="insurance" disabled>
                            <div class="flex-grow-1">
                                <label for="insurance" class="mb-1">Bảo hiểm bảo vệ người tiêu dùng</label>
                                <div class="text-muted small">
                                    Giúp bảo vệ bạn khỏi các rủi ro, thiệt hại gây ra bởi sản phẩm được bảo hiểm trong quá trình sử dụng. 
                                    <a href="#" class="text-decoration-none">Tìm hiểu thêm</a>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted">1.300₫</span>
                                <span class="text-muted small">x1</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Voucher của Shop -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="bi bi-ticket-perforated text-danger me-2"></i>
                            <span>Voucher của Shop</span>
                        </div>
                        @if(count($availableVouchers) > 0)
                            <a href="#" class="text-decoration-none text-primary" data-bs-toggle="modal" data-bs-target="#voucherModal">
                                <span id="voucher-selected-text">Chọn hoặc nhập mã</span> <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="text-muted small">Không có voucher khả dụng</span>
                        @endif
                    </div>
                    @if(count($availableVouchers) > 0)
                        <div id="voucher-info" class="mt-2 p-2 bg-light rounded" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-success small">
                                    <i class="bi bi-check-circle-fill me-1"></i>
                                    <span id="voucher-label"></span>
                                </div>
                                <a href="#" class="text-danger small" id="remove-voucher">Bỏ chọn</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lời nhắn cho Shop -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <i class="bi bi-chat-dots me-2"></i>
                            <span>Lời nhắn cho Shop</span>
                        </div>
                        <a href="#" class="text-decoration-none text-muted">
                            Để lại lời nhắn <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Hóa đơn điện tử -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-receipt me-2"></i>
                            <span>Hóa đơn điện tử</span>
                            <i class="bi bi-info-circle text-muted ms-1" data-bs-toggle="tooltip" title="Chọn để nhận hóa đơn sau khi đặt hàng"></i>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="requestInvoice" name="request_invoice" value="1">
                            <label class="form-check-label" for="requestInvoice">Yêu cầu</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phương thức vận chuyển -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <strong>Phương thức vận chuyển</strong>
                        <a href="#" class="text-decoration-none">Xem tất cả <i class="bi bi-chevron-right"></i></a>
                    </div>
                    <div class="shipping-option border rounded p-3 bg-light">
                        <div class="d-flex align-items-start">
                            <input type="radio" class="form-check-input me-3 mt-1" name="shipping" checked>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between mb-1">
                                    <strong class="text-success">Nhanh</strong>
                                    <strong class="text-danger">{{ number_format($shippingFee, 0, ',', '.') }}₫</strong>
                                </div>
                                <div class="text-muted small">
                                    <i class="bi bi-truck me-1"></i>
                                    Nhận từ 10 Th10 - 11 Th10
                                </div>
                                <div class="text-success small mt-1">
                                    Nhận Voucher trị giá 15.000₫ nếu đơn hàng được giao đến bạn sau ngày 11 Tháng 10 2025.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-muted small mt-2">
                        <i class="bi bi-check-circle text-success me-1"></i>
                        Được đồng kiểm
                    </div>
                </div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <strong>Phương thức thanh toán</strong>
                        <div class="d-flex align-items-center">
                            <span class="me-2" id="payment-method-text">Thanh toán khi nhận hàng</span>
                            <a href="#" class="text-decoration-none text-primary" data-bs-toggle="modal" data-bs-target="#paymentMethodModal">THAY ĐỔI</a>
                        </div>
                    </div>
                    
                    <!-- Chi tiết thanh toán -->
                    <div class="payment-details">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tổng tiền hàng</span>
                            <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tổng tiền phí vận chuyển</span>
                            <span>{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tổng cộng Voucher giảm giá</span>
                            <span class="text-danger" id="discount-amount">-{{ number_format(0, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="d-flex justify-content-between pt-2 border-top">
                            <strong>Tổng thanh toán</strong>
                            <strong class="text-danger fs-5" id="final-total">{{ number_format($total, 0, ',', '.') }}₫</strong>
                        </div>
                        <div class="text-end text-muted small mt-1">
                            Đã bao gồm thuế
                        </div>
                    </div>

                    <div class="agreement-text mt-3 small text-muted">
                        Nhấn "Đặt hàng" đồng nghĩa với việc bạn đồng ý tuân theo 
                        <a href="#" class="text-decoration-none">Điều khoản Shopee</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer thanh toán -->
    <div class="payment-footer bg-white border-top fixed-bottom">
        <div class="container py-3">
            <form method="POST" action="{{ route('checkout.place') }}" id="checkout-form" onsubmit="return submitCheckoutForm()">
                @csrf
                <input type="hidden" name="payment_method" id="payment-method-input" value="cod">
                <input type="hidden" name="voucher_code" id="voucher-code-input" value="">
                <input type="hidden" name="request_invoice" id="request-invoice-input" value="0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Tổng số tiền ({{ count($items) }} sản phẩm):</span>
                    <strong class="text-danger fs-5" id="footer-total">{{ number_format($total, 0, ',', '.') }}₫</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                    <span>Tiết kiệm</span>
                    <span>{{ number_format(0, 0, ',', '.') }}₫</span>
                </div>
                <button type="submit" class="btn btn-danger w-100 py-2 fw-semibold">
                    Đặt hàng
                </button>
            </form>
        </div>
    </div>

    <!-- Modal chọn voucher -->
    @if(count($availableVouchers) > 0)
    <div class="modal fade" id="voucherModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chọn Voucher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @foreach($availableVouchers as $voucher)
                    <div class="voucher-option mb-3 p-3 border rounded" data-code="{{ $voucher['code'] }}" data-discount="{{ $voucher['discount'] }}" data-label="{{ $voucher['label'] }}" style="cursor: pointer;">
                        <div class="d-flex align-items-start">
                            <input type="radio" name="voucher_radio" value="{{ $voucher['code'] }}" class="form-check-input me-3 mt-1">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="fw-semibold text-danger">{{ $voucher['code'] }}</div>
                                        <div class="text-muted small">{{ $voucher['label'] }}</div>
                                    </div>
                                    <div class="text-danger fw-semibold">-{{ number_format($voucher['discount'], 0, ',', '.') }}₫</div>
                                </div>
                                <div class="small text-muted">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Đơn tối thiểu {{ number_format($voucher['min_order'], 0, ',', '.') }}₫
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirm-voucher">Áp dụng</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal chọn phương thức thanh toán -->
    <div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chọn phương thức thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="payment-method-option mb-3 p-3 border rounded" data-method="cod" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="cod" class="form-check-input me-3" checked>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Thanh toán khi nhận hàng (COD)</div>
                                <div class="text-muted small">Thanh toán bằng tiền mặt khi nhận hàng</div>
                            </div>
                            <i class="bi bi-cash-coin fs-3 text-success"></i>
                        </div>
                    </div>
                    <div class="payment-method-option mb-3 p-3 border rounded" data-method="momo" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="momo" class="form-check-input me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Ví điện tử MoMo</div>
                                <div class="text-muted small">Thanh toán qua ví MoMo</div>
                            </div>
                            <img src="https://upload.wikimedia.org/wikipedia/vi/f/fe/MoMo_Logo.png" alt="MoMo" style="width: 40px; height: 40px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirm-payment-method">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.payment-page {
    min-height: 100vh;
    background: #f5f5f5;
}

.payment-header {
    background: #fff;
    border-bottom: 1px solid #e5e5e5;
}

.payment-body {
    padding-bottom: 180px;
}

.product-thumb {
    width: 80px;
    height: 80px;
    object-fit: cover;
}

.product-name {
    font-size: 0.95rem;
    line-height: 1.4;
}

.card {
    border: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.payment-footer {
    box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
}

.btn-danger {
    background: #ee4d2d;
    border: none;
}

.btn-danger:hover {
    background: #d73211;
}

.shipping-option {
    border-color: #26aa99 !important;
}

.badge {
    font-size: 0.7rem;
    padding: 0.25rem 0.5rem;
}

@media (max-width: 768px) {
    .payment-body {
        padding-bottom: 200px;
    }
}

.shopee-xu-icon {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.payment-details {
    font-size: 0.95rem;
}

.payment-details .border-top {
    margin-top: 0.5rem;
    padding-top: 0.75rem;
}

.agreement-text {
    line-height: 1.5;
}

.text-primary {
    color: #ee4d2d !important;
}

.text-primary:hover {
    color: #d73211 !important;
}

.payment-method-option {
    transition: all 0.2s;
}

.payment-method-option:hover {
    background-color: #f8f9fa;
    border-color: #ee4d2d !important;
}

.payment-method-option.selected {
    border-color: #ee4d2d !important;
    background-color: #fff5f5;
}

.voucher-option {
    transition: all 0.2s;
}

.voucher-option:hover {
    background-color: #f8f9fa;
    border-color: #ee4d2d !important;
}

.voucher-option.selected {
    border-color: #ee4d2d !important;
    background-color: #fff5f5;
}
</style>
@endpush

@push('scripts')
<script>
function submitCheckoutForm() {
    const requestInvoiceCheckbox = document.getElementById('requestInvoice');
    const requestInvoiceInput = document.getElementById('request-invoice-input');
    
    if (requestInvoiceCheckbox && requestInvoiceCheckbox.checked) {
        requestInvoiceInput.value = '1';
    } else {
        requestInvoiceInput.value = '0';
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const subtotal = {{ $subtotal }};
    const shippingFee = {{ $shippingFee }};
    let currentDiscount = 0;
    
    // Voucher handling
    const voucherOptions = document.querySelectorAll('.voucher-option');
    const voucherCodeInput = document.getElementById('voucher-code-input');
    const voucherInfo = document.getElementById('voucher-info');
    const voucherLabel = document.getElementById('voucher-label');
    const voucherSelectedText = document.getElementById('voucher-selected-text');
    const discountAmount = document.getElementById('discount-amount');
    const finalTotal = document.getElementById('final-total');
    const footerTotal = document.getElementById('footer-total');
    const confirmVoucherBtn = document.getElementById('confirm-voucher');
    const removeVoucherBtn = document.getElementById('remove-voucher');
    
    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    }
    
    function updateTotal() {
        const total = subtotal + shippingFee - currentDiscount;
        if (finalTotal) finalTotal.textContent = formatCurrency(total);
        if (footerTotal) footerTotal.textContent = formatCurrency(total);
        if (discountAmount) discountAmount.textContent = '-' + formatCurrency(currentDiscount);
    }
    
    // Click vào voucher option
    voucherOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            voucherOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    // Xác nhận voucher
    if (confirmVoucherBtn) {
        confirmVoucherBtn.addEventListener('click', function() {
            const selectedVoucher = document.querySelector('input[name="voucher_radio"]:checked');
            if (selectedVoucher) {
                const voucherOption = selectedVoucher.closest('.voucher-option');
                const code = voucherOption.dataset.code;
                const discount = parseInt(voucherOption.dataset.discount);
                const label = voucherOption.dataset.label;
                
                voucherCodeInput.value = code;
                currentDiscount = discount;
                
                if (voucherInfo) {
                    voucherInfo.style.display = 'block';
                    voucherLabel.textContent = label;
                }
                if (voucherSelectedText) {
                    voucherSelectedText.textContent = code;
                }
                
                updateTotal();
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('voucherModal'));
                modal.hide();
            }
        });
    }
    
    // Bỏ chọn voucher
    if (removeVoucherBtn) {
        removeVoucherBtn.addEventListener('click', function(e) {
            e.preventDefault();
            voucherCodeInput.value = '';
            currentDiscount = 0;
            
            if (voucherInfo) voucherInfo.style.display = 'none';
            if (voucherSelectedText) voucherSelectedText.textContent = 'Chọn hoặc nhập mã';
            
            const selectedRadio = document.querySelector('input[name="voucher_radio"]:checked');
            if (selectedRadio) selectedRadio.checked = false;
            
            voucherOptions.forEach(opt => opt.classList.remove('selected'));
            updateTotal();
        });
    }
    
    // Payment method handling
    const paymentMethodOptions = document.querySelectorAll('.payment-method-option');
    const paymentMethodInput = document.getElementById('payment-method-input');
    const paymentMethodText = document.getElementById('payment-method-text');
    const confirmBtn = document.getElementById('confirm-payment-method');
    
    paymentMethodOptions.forEach(option => {
        option.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            paymentMethodOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
        });
    });
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            const selectedMethod = document.querySelector('input[name="payment_method_radio"]:checked').value;
            paymentMethodInput.value = selectedMethod;
            
            if (selectedMethod === 'cod') {
                paymentMethodText.textContent = 'Thanh toán khi nhận hàng';
            } else if (selectedMethod === 'momo') {
                paymentMethodText.textContent = 'Ví điện tử MoMo';
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal'));
            modal.hide();
        });
    }
});
</script>
@endpush
@endsection
