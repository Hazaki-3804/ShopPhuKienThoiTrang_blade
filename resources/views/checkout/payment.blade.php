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
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editAddressModal">
                                    <i class="bi bi-pencil-square me-1"></i>Chỉnh sửa
                                </button>
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
                        <strong> Nàng Thơ Shop</strong>
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
                            <input type="checkbox" class="form-check-input me-3 mt-1" id="insurance">
                            <div class="flex-grow-1">
                                <label for="insurance" class="mb-1" style="cursor: pointer;">Bảo hiểm bảo vệ người tiêu dùng</label>
                                <div class="text-muted small">
                                    Giúp bảo vệ bạn khỏi các rủi ro, thiệt hại gây ra bởi sản phẩm được bảo hiểm trong quá trình sử dụng. 
                                    <a href="#" class="text-decoration-none">Tìm hiểu thêm</a>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted" id="insurance-price">1.300₫</span>
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
                                    Nhận từ 10 Th10 - 13 Th10
                                </div>
                                <div class="text-success small mt-1">
                                    Giao hàng nhanh an toàn được giao đến bạn từ ngày 10 Tháng 10 2025 - ngày 13 Tháng 10 2025.
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
                        <div class="d-flex justify-content-between mb-2" id="insurance-row" style="display: none !important;">
                            <span class="text-muted">Bảo hiểm bảo vệ người tiêu dùng</span>
                            <span id="insurance-amount">+1.300₫</span>
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
                        <a href="#" class="text-decoration-none">Điều khoản Nàng Thơ</a>
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
                <input type="hidden" name="insurance" id="insurance-input" value="0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Tổng số tiền ({{ count($items) }} sản phẩm):</span>
                    <strong class="text-danger fs-5" id="footer-total">{{ number_format($total, 0, ',', '.') }}₫</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 small">
                    <span class="text-muted">Tiết kiệm</span>
                    <span class="text-success" id="footer-savings">0₫</span>
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
                    <div class="payment-method-option mb-3 p-3 border rounded" data-method="vnpay" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="vnpay" class="form-check-input me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Ví điện tử VNPAY</div>
                                <div class="text-muted small">Thanh toán qua ví VNPAY</div>
                            </div>
                            <img src="data:image/png;base64,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" alt="VNPAY" style="width: 40px; height: 40px;">
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
<!-- Select2 CSS for address editing -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Select2 styling for modal */
.select2-container--default .select2-selection--single { 
    height: 38px; 
    border-radius: .375rem; 
    border: 1px solid #ced4da; 
}
.select2-container--default .select2-selection--single .select2-selection__rendered { 
    line-height: 38px; 
}
.select2-container--default .select2-selection--single .select2-selection__arrow { 
    height: 36px; 
}
.select2-container { 
    width: 100% !important; 
}

/* Input group styling for modal */
.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    color: #6c757d;
    min-width: 45px;
    justify-content: center;
}
.input-group-text i {
    font-size: 1.1em;
}
.input-group .form-control:not(:first-child) {
    border-left: 0;
}
.input-group .input-group-text:not(:last-child) {
    border-right: 0;
}

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

/* Edit address button styling */
.btn-outline-primary {
    border-color: #0d6efd;
    color: #0d6efd;
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    transition: all 0.15s ease-in-out;
}

.btn-outline-primary:hover {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
}
</style>
@endpush

@push('scripts')
<!-- jQuery and Select2 for address editing -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    const insuranceFee = 1300;
    let currentDiscount = 0;
    let insuranceEnabled = false;
    
    // Handle form submission to show loading state
    const checkoutForm = document.getElementById('checkout-form');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xử lý...';
            }
        });
    }
    
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
        const insuranceAmount = insuranceEnabled ? insuranceFee : 0;
        const total = subtotal + shippingFee - currentDiscount + insuranceAmount;
        if (finalTotal) finalTotal.textContent = formatCurrency(total);
        if (footerTotal) footerTotal.textContent = formatCurrency(total);
        if (discountAmount) discountAmount.textContent = '-' + formatCurrency(currentDiscount);
        
        // Update insurance row visibility
        const insuranceRow = document.getElementById('insurance-row');
        if (insuranceRow) {
            if (insuranceEnabled) {
                insuranceRow.style.removeProperty('display');
            } else {
                insuranceRow.style.display = 'none';
            }
        }
        
        // Update savings (tiết kiệm)
        const footerSavings = document.getElementById('footer-savings');
        if (footerSavings) {
            footerSavings.textContent = formatCurrency(currentDiscount);
        }
    }
    
    // Insurance checkbox handling
    const insuranceCheckbox = document.getElementById('insurance');
    const insuranceInput = document.getElementById('insurance-input');
    
    if (insuranceCheckbox) {
        insuranceCheckbox.addEventListener('change', function() {
            insuranceEnabled = this.checked;
            insuranceInput.value = insuranceEnabled ? '1' : '0';
            updateTotal();
        });
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
            
            if (selectedMethod === 'vnpay') {
                paymentMethodText.textContent = 'Thanh toán qua VNPAY';
            } else if (selectedMethod === 'momo') {
                paymentMethodText.textContent = 'Ví điện tử MoMo';
            }else {
                paymentMethodText.textContent = 'Thanh toán khi nhận hàng';
            }
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('paymentMethodModal'));
            modal.hide();
        });
    }
    
    // Edit Address Modal functionality
    const editAddressModal = document.getElementById('editAddressModal');
    if (editAddressModal) {
        editAddressModal.addEventListener('shown.bs.modal', function () {
            initEditAddressSelectors();
        });
    }
    
    function initEditAddressSelectors() {
        const $editProv = $('#edit_province');
        const $editWard = $('#edit_ward');
        
        // Initialize Select2 if available, otherwise use regular selects
        if (typeof $.fn.select2 !== 'undefined') {
            $editProv.select2({ 
                placeholder: '-- Chọn tỉnh thành --', 
                allowClear: true, 
                width: '100%',
                dropdownParent: $('#editAddressModal')
            });
            $editWard.select2({ 
                placeholder: '-- Chọn xã/phường --', 
                allowClear: true, 
                width: '100%',
                dropdownParent: $('#editAddressModal')
            });
        }

        // Load provinces
        fetch('https://provinces.open-api.vn/api/v2/?depth=2')
            .then(r => r.json())
            .then(data => {
                $editProv.empty().append('<option value="">-- Chọn tỉnh --</option>');
                data.forEach(p => { 
                    $editProv.append(`<option value="${p.code}">${p.name}</option>`); 
                });
                if (typeof $.fn.select2 !== 'undefined') {
                    $editProv.trigger('change.select2');
                }
                window.__VN_PROVINCES_EDIT__ = data;
            }).catch(err => console.error('Load provinces failed', err));

        $editProv.on('change', function(){
            const code = $(this).val();
            $editWard.empty().append('<option value="">-- Chọn xã/phường --</option>');
            if (typeof $.fn.select2 !== 'undefined') {
                $editWard.trigger('change.select2');
            }
            
            const data = (window.__VN_PROVINCES_EDIT__ || []).find(p => String(p.code) === String(code));
            if (data) {
                data.wards.forEach(w => { 
                    $editWard.append(`<option value="${w.code}">${w.name}</option>`); 
                });
                if (typeof $.fn.select2 !== 'undefined') {
                    $editWard.trigger('change.select2');
                }
            }
            
            const provText = $editProv.find('option:selected').text();
            document.getElementById('edit_province_name').value = provText && provText.indexOf('Chọn') === -1 ? provText.trim() : '';
        });

        $editWard.on('change', function(){
            const wardText = $editWard.find('option:selected').text();
            document.getElementById('edit_ward_name').value = wardText && wardText.indexOf('Chọn') === -1 ? wardText.trim() : '';
        });

        // Handle form submission
        const editForm = document.getElementById('editAddressForm');
        if (editForm) {
            editForm.addEventListener('submit', function(){
                const addrEl = editForm.querySelector('textarea[name="customer_address"]');
                const provText = document.getElementById('edit_province_name').value || $editProv.find('option:selected').text();
                const wardText = document.getElementById('edit_ward_name').value || $editWard.find('option:selected').text();
                const parts = [];
                if (addrEl && addrEl.value) parts.push(addrEl.value.trim());
                if (wardText && wardText.indexOf('Chọn') === -1) parts.push(wardText.trim());
                if (provText && provText.indexOf('Chọn') === -1) parts.push(provText.trim());
                if (addrEl) addrEl.value = parts.join(', ');
            });
        }
    }
});
</script>
@endpush
<!-- Modal chỉnh sửa địa chỉ -->
<div class="modal fade" id="editAddressModal" tabindex="-1" aria-labelledby="editAddressModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAddressModalLabel">Chỉnh sửa địa chỉ giao hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('checkout.saveAddress') }}" id="editAddressForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Họ và tên</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="customer_name" value="{{ $addressData['customer_name'] }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="customer_email" value="{{ $addressData['customer_email'] }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Số điện thoại</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="customer_phone" value="{{ $addressData['customer_phone'] }}" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Địa chỉ</label>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <select id="edit_province" class="form-select" data-placeholder="-- Chọn tỉnh thành --">
                                        <option value="" disabled selected>-- Chọn tỉnh --</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mt-2 mt-md-0">
                                    <select id="edit_ward" class="form-select" data-placeholder="-- Chọn xã/phường --">
                                        <option value="" disabled selected>-- Chọn xã/phường --</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hidden fields to persist selected names to server-side -->
                            <input type="hidden" name="province_name" id="edit_province_name">
                            <input type="hidden" name="ward_name" id="edit_ward_name">
                            <div class="input-group mt-2">
                                <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                                <textarea name="customer_address" class="form-control" rows="2" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required>{{ $addressData['customer_address'] }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection