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
                                    <a href="#" class="text-decoration-none" onclick="event.preventDefault();">Tìm hiểu thêm</a>
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

            <!-- Bản đồ và khoảng cách -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div>
                            <i class="bi bi-map text-primary me-2"></i>
                            <strong>Vị trí giao hàng</strong>
                        </div>
                        <div class="badge bg-success">
                            <i class="bi bi-geo-alt-fill me-1"></i>
                            Khoảng cách: <span id="distance-display">Đang tính...</span> km
                        </div>
                    </div>
                    
                    <!-- OpenStreetMap with Leaflet -->
                    <div id="map" style="width: 100%; height: 300px; border-radius: 8px; overflow: hidden; z-index: 1;"></div>
                    
                    <div class="text-muted small mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        <span id="distance-info">Đang tính khoảng cách thực tế theo đường bộ...</span>
                        <span class="ms-2 text-success">
                            <i class="bi bi-check-circle-fill"></i> Sử dụng OpenStreetMap (Miễn phí)
                        </span>
                    </div>
                    <div class="alert alert-info mt-2 mb-0 py-2 px-3 small">
                        <i class="bi bi-lightbulb me-1"></i>
                        <strong>Mẹo:</strong> Nếu vị trí không chính xác, bạn có thể kéo marker B (xanh) đến vị trí đúng trên bản đồ để tính khoảng cách chính xác hơn.
                    </div>
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
                                    <strong class="text-danger" data-shipping-fee>{{ number_format($shippingFee, 0, ',', '.') }}₫</strong>
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
                            <span data-shipping-fee>{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 d-none" id="insurance-row">
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
                            <img src="https://homepage.momocdn.net/fileuploads/svg/momo-file-240411162904.svg" alt="MoMo" style="width: 40px; height: 40px;">
                        </div>
                    </div>
                    <div class="payment-method-option mb-3 p-3 border rounded" data-method="vnpay" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="vnpay" class="form-check-input me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Ví điện tử VNPAY</div>
                                <div class="text-muted small">Thanh toán qua ví VNPAY</div>
                            </div>
                            <img src="https://stcd02206177151.cloud.edgevnpay.vn/assets/images/logo-icon/logo-primary.svg" alt="VNPAY" style="width: 40px; height: 40px;">
                        </div>
                    </div>
                     <div class="payment-method-option mb-3 p-3 border rounded" data-method="payos" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="payos" class="form-check-input me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Ví điện tử PayOS</div>
                                <div class="text-muted small">Thanh toán qua ví PayOS</div>
                            </div>
                            <img src="https://payos.vn/docs/img/logo.svg" alt="PayOS" style="width: 78px; height: 32px;">
                        </div>
                    </div>
                    <div class="payment-method-option mb-3 p-3 border rounded" data-method="sepay" style="cursor: pointer;">
                        <div class="d-flex align-items-center">
                            <input type="radio" name="payment_method_radio" value="sepay" class="form-check-input me-3">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">Chuyển khoản qua ví SePay</div>
                                <div class="text-muted small">Thanh toán qua chuyển khoản ngân hàng</div>
                            </div>
                            <img src="https://sepay.vn//assets/img/logo/sepay-blue-154x50.png" alt="SePay" style="width: 50px; height: 50px; object-fit: contain;">
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
<!-- Leaflet CSS for OpenStreetMap -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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

<!-- Leaflet JS for OpenStreetMap -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine -->
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css" />
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>

<script>
// Initialize OpenStreetMap with Leaflet
// VERSION: 2.0 - Fixed route to follow QL1A exactly
let map;

/**
 * Nội suy điểm giữa 2 waypoints để tạo đường cong mượt
 */
function interpolatePoints(point1, point2, numPoints = 3) {
    const [lat1, lng1] = point1;
    const [lat2, lng2] = point2;
    const points = [];
    
    for (let i = 1; i <= numPoints; i++) {
        const ratio = i / (numPoints + 1);
        const lat = lat1 + (lat2 - lat1) * ratio;
        const lng = lng1 + (lng2 - lng1) * ratio;
        points.push([lat, lng]);
    }
    
    return points;
}

/**
 * Lấy danh sách anchor points (waypoints) trong biên từ Nam -> Bắc của Việt Nam
 * Dùng các thành phố trên QL1A để ép router đi trong VN
 */
function getVietnamAnchors(startLocation, endLocation) {
    const [startLat] = startLocation;
    const [endLat] = endLocation;
    const minLat = Math.min(startLat, endLat);
    const maxLat = Math.max(startLat, endLat);

    const anchors = [
        [10.2397, 105.9571],  // Vĩnh Long
        [10.7756, 106.7019],  // TP.HCM
        [10.9510, 106.8340],  // Biên Hòa
        [10.9289, 108.1022],  // Phan Thiết
        [11.5648, 108.9897],  // Phan Rang
        [12.2388, 109.1967],  // Nha Trang
        [13.0955, 109.2961],  // Tuy Hòa
        [13.7830, 109.2196],  // Quy Nhơn
        [15.1214, 108.8044],  // Quảng Ngãi
        [15.5736, 108.4742],  // Tam Kỳ
        [16.0544, 108.2022],  // Đà Nẵng
        [16.4637, 107.5909],  // Huế
        [16.8167, 107.1000],  // Đông Hà
        [17.4833, 106.6167],  // Đồng Hới
        [18.3333, 105.9000],  // Hà Tĩnh
        [18.6792, 105.6811],  // Vinh
        [19.8067, 105.7761],  // Thanh Hóa
        [20.2506, 105.9745],  // Ninh Bình
        [20.4333, 106.1667],  // Nam Định
        [21.0285, 105.8542],  // Hà Nội
    ];

    // Lọc theo khoảng latitude giữa start và end để không thêm dư thừa
    return anchors.filter(([lat]) => lat > minLat && lat < maxLat);
}

/**
 * Tạo waypoints trung gian để buộc routing đi trong nội địa Vietnam
 * Tránh routing đi qua Lào, Thái Lan, Campuchia
 */
function createVietnamRouteWaypoints(startLocation, endLocation) {
    const waypoints = [L.latLng(startLocation[0], startLocation[1])];
    
    const startLat = startLocation[0];
    const startLng = startLocation[1];
    const endLat = endLocation[0];
    const endLng = endLocation[1];
    
    // Các điểm trung gian dọc theo quốc lộ 1A và đường chính Vietnam
    const vietnamKeyPoints = [
        // Miền Nam
        { name: 'Vĩnh Long', lat: 10.2397, lng: 105.9571, region: 'south' },
        { name: 'Cần Thơ', lat: 10.0452, lng: 105.7469, region: 'south' },
        { name: 'TP.HCM', lat: 10.8231, lng: 106.6297, region: 'south' },
        { name: 'Biên Hòa', lat: 10.9465, lng: 106.8420, region: 'south' },
        { name: 'Phan Thiết', lat: 10.9280, lng: 108.1020, region: 'south' },
        { name: 'Nha Trang', lat: 12.2388, lng: 109.1967, region: 'central' },
        { name: 'Tuy Hòa', lat: 13.0882, lng: 109.2965, region: 'central' },
        { name: 'Quy Nhơn', lat: 13.7830, lng: 109.2196, region: 'central' },
        { name: 'Quảng Ngãi', lat: 15.1214, lng: 108.8044, region: 'central' },
        { name: 'Đà Nẵng', lat: 16.0544, lng: 108.2022, region: 'central' },
        { name: 'Huế', lat: 16.4637, lng: 107.5909, region: 'central' },
        { name: 'Đồng Hới', lat: 17.4676, lng: 106.6222, region: 'central' },
        { name: 'Vinh', lat: 18.6792, lng: 105.6819, region: 'north' },
        { name: 'Thanh Hóa', lat: 19.8067, lng: 105.7851, region: 'north' },
        { name: 'Ninh Bình', lat: 20.2506, lng: 105.9745, region: 'north' },
        { name: 'Nam Định', lat: 20.4388, lng: 106.1621, region: 'north' },
        { name: 'Hải Phòng', lat: 20.8449, lng: 106.6881, region: 'north' },
        { name: 'Hà Nội', lat: 21.0285, lng: 105.8542, region: 'north' },
        { name: 'Thái Nguyên', lat: 21.5671, lng: 105.8252, region: 'north' },
        { name: 'Tuyên Quang', lat: 21.8237, lng: 105.2280, region: 'north' },
    ];
    
    // Tính khoảng cách giữa start và end
    const distance = Math.sqrt(
        Math.pow(endLat - startLat, 2) + Math.pow(endLng - startLng, 2)
    );
    
    console.log('📏 Distance between start and end:', distance);
    
    // Nếu khoảng cách lớn (> 3 độ ~ 330km), thêm waypoints trung gian
    // Để buộc routing đi trong nước Vietnam, không qua biên giới
    if (distance > 3) {
        console.log('🛣️ Long distance route - Adding intermediate waypoints to stay in Vietnam');
        
        // Xác định hướng di chuyển (Bắc -> Nam hay Nam -> Bắc)
        const goingNorth = endLat > startLat;
        
        // Lọc các điểm nằm giữa start và end
        const intermediatePoints = vietnamKeyPoints.filter(point => {
            if (goingNorth) {
                return point.lat > startLat && point.lat < endLat;
            } else {
                return point.lat < startLat && point.lat > endLat;
            }
        });
        
        // Sắp xếp theo latitude
        intermediatePoints.sort((a, b) => {
            return goingNorth ? (a.lat - b.lat) : (b.lat - a.lat);
        });
        
        console.log('🎯 Intermediate points found:', intermediatePoints.map(p => p.name));
        
        // Thêm waypoints trung gian - số lượng tùy thuộc vào khoảng cách
        let numWaypoints;
        if (distance > 10) {
            numWaypoints = 4; // Rất xa (> 1100km): 4 waypoints
        } else if (distance > 7) {
            numWaypoints = 3; // Xa (> 770km): 3 waypoints
        } else if (distance > 5) {
            numWaypoints = 2; // Trung bình (> 550km): 2 waypoints
        } else {
            numWaypoints = 1; // Gần (> 330km): 1 waypoint
        }
        
        numWaypoints = Math.min(numWaypoints, intermediatePoints.length);
        
        if (intermediatePoints.length > 0) {
            const step = Math.floor(intermediatePoints.length / (numWaypoints + 1));
            
            for (let i = 0; i < numWaypoints; i++) {
                const index = (i + 1) * step;
                if (index < intermediatePoints.length) {
                    const point = intermediatePoints[index];
                    waypoints.push(L.latLng(point.lat, point.lng));
                    console.log(`  ➡️ Waypoint ${i+1}: ${point.name} (${point.lat}, ${point.lng})`);
                }
            }
        }
    } else {
        console.log('📍 Short distance route - No intermediate waypoints needed');
    }
    
    // Thêm điểm đích
    waypoints.push(L.latLng(endLocation[0], endLocation[1]));
    
    console.log('✅ Total waypoints:', waypoints.length);
    
    return waypoints;
}

/**
 * Vẽ route theo đường bộ bằng Leaflet Routing Machine
 * Đơn giản và ổn định hơn OSRM trực tiếp
 */
function drawVietnamRoadRoute(map, storeLocation, customerLocation, customerAddress) {
    try {
        console.log('🚀 [drawVietnamRoadRoute] STARTED');
        console.log('📍 Start:', storeLocation, 'End:', customerLocation);
        console.log('🗺️ Map object:', map);
        console.log('📦 L.Routing available:', typeof L.Routing);
        
        if (typeof L.Routing === 'undefined') {
            console.error('❌ Leaflet Routing Machine không load được!');
            alert('❌ Lỗi: Leaflet Routing Machine không load được!');
            return;
        }
        
        // Xóa routing cũ nếu có
        if (window.currentRoutingControl) {
            try {
                map.removeControl(window.currentRoutingControl);
                console.log('🗑️ Đã xóa routing control cũ');
            } catch (e) {
                console.warn('⚠️ Không thể xóa routing cũ:', e);
            }
        }
        
        // Tạo waypoints trung gian để buộc đi theo đường trong nước Vietnam
        const vietnamWaypoints = createVietnamRouteWaypoints(storeLocation, customerLocation);
        
        console.log('🗺️ Vietnam waypoints:', vietnamWaypoints);
        
        // Tạo routing control với OSRM
        const routingControl = L.Routing.control({
            waypoints: vietnamWaypoints,
            router: L.Routing.osrmv1({
                serviceUrl: 'https://router.project-osrm.org/route/v1',
                profile: 'car' // Sử dụng profile 'car' thay vì 'driving'
            }),
            lineOptions: {
                styles: [{ color: '#ee4d2d', weight: 5, opacity: 0.9 }]
            },
            show: false, // Ẩn bảng hướng dẫn
            addWaypoints: false, // Không cho kéo thả waypoint
            routeWhileDragging: false, // Không routing khi drag
            draggableWaypoints: false, // Không cho kéo waypoint
            fitSelectedRoutes: false, // Không tự động zoom về route
            showAlternatives: false,
            createMarker: function() { return null; } // Không tạo marker của routing (dùng marker riêng)
        }).addTo(map);
        
        // Lưu routing control vào window để có thể xóa sau
        window.currentRoutingControl = routingControl;
        console.log('💾 Đã lưu routing control vào window');
        
        console.log('🔗 Đang setup event listeners...');
        
        // Lắng nghe sự kiện routing thành công
        routingControl.on('routesfound', function(e) {
            console.log('🎉 ROUTESFOUND EVENT TRIGGERED!');
            console.log('📊 Event data:', e);
            
            const routes = e.routes;
            console.log('🛣️ Routes:', routes);
            
            if (!routes || routes.length === 0) {
                console.error('❌ Không có routes trong event');
                return;
            }
            
            const summary = routes[0].summary;
            console.log('📋 Summary:', summary);
            
            const distanceKm = (summary.totalDistance / 1000).toFixed(1);
            console.log('🎉 OSRM ROUTING SUCCESS - Khoảng cách thực tế theo đường bộ:', distanceKm, 'km');
            
            // FORCE UPDATE khoảng cách ngay lập tức - Ưu tiên cao nhất
            const distanceSpan = document.getElementById('distance-display');
            console.log('🔍 Tìm element #distance-display:', distanceSpan);
            
            if (distanceSpan) {
                const oldValue = distanceSpan.textContent;
                distanceSpan.textContent = distanceKm;
                console.log('✅ CẬP NHẬT THÀNH CÔNG - Từ:', oldValue, '→ Thành:', distanceKm, 'km (OSRM thực tế)');
            } else {
                console.error('❌ KHÔNG TÌM THẤY #distance-display');
                // Tìm tất cả span có thể chứa khoảng cách
                const allSpans = document.querySelectorAll('span');
                let found = false;
                allSpans.forEach((span, index) => {
                    if (span.id === 'distance-display' || 
                        (span.parentElement && span.parentElement.textContent.includes('Khoảng cách'))) {
                        span.textContent = distanceKm;
                        console.log(`✅ Tìm thấy và cập nhật span ${index}:`, distanceKm, 'km');
                        found = true;
                    }
                });
                
                if (!found) {
                    console.error('💥 KHÔNG THỂ TÌM THẤY ELEMENT NÀO ĐỂ CẬP NHẬT!');
                }
            }
            
            // Lưu khoảng cách vào window và LOCK để tránh ghi đè
            window.currentDistance = parseFloat(distanceKm);
            window.routingCompleted = true;
            window.distanceLocked = true; // LOCK để tránh ghi đè
            console.log('🔒 ROUTING HOÀN TẤT VÀ LOCKED - Khoảng cách chính xác:', distanceKm, 'km');
            
            // Cập nhật thông báo UI
            const distanceInfo = document.getElementById('distance-info');
            if (distanceInfo) {
                distanceInfo.textContent = 'Khoảng cách thực tế theo đường bộ đã được tính toán chính xác';
                distanceInfo.className = 'text-success';
            }
            
            // Gửi khoảng cách chính xác về server để tính phí chính xác
            // Tạo hidden input để lưu khoảng cách OSRM
            let distanceInput = document.getElementById('osrm-distance-input');
            if (!distanceInput) {
                distanceInput = document.createElement('input');
                distanceInput.type = 'hidden';
                distanceInput.id = 'osrm-distance-input';
                distanceInput.name = 'osrm_distance';
                document.getElementById('checkout-form').appendChild(distanceInput);
            }
            distanceInput.value = distanceKm;
            console.log('💾 Lưu khoảng cách OSRM vào form:', distanceKm, 'km');
            
            // Cập nhật phí vận chuyển theo khoảng cách OSRM mới
            // Truyền customerAddress từ closure để đảm bảo dùng địa chỉ đúng
            updateShippingFeeWithOSRMDistance(distanceKm, customerAddress);
            
            // Đặt interval để đảm bảo giá trị không bị thay đổi
            // Clear interval cũ nếu có
            if (distanceLockInterval) {
                clearInterval(distanceLockInterval);
            }
            
            // Enable lock behavior
            distanceLockEnabled = true;
            console.log('🔒 Đã enable distance lock');
            
            distanceLockInterval = setInterval(() => {
                // Only restore if lock is enabled
                if (!distanceLockEnabled) {
                    return;
                }
                
                const currentElement = document.getElementById('distance-display');
                if (currentElement && currentElement.textContent !== distanceKm) {
                    console.warn('⚠️ Phát hiện thay đổi không mong muốn, khôi phục:', distanceKm, 'km');
                    currentElement.textContent = distanceKm;
                }
            }, 1000);
            
            // Dừng interval sau 30 giây
            setTimeout(() => {
                if (distanceLockInterval) {
                    clearInterval(distanceLockInterval);
                    distanceLockInterval = null;
                }
            }, 30000);
        });
        
        // Lắng nghe lỗi
        routingControl.on('routingerror', function(e) {
            console.error('💥 ROUTINGERROR EVENT TRIGGERED!');
            console.error('❌ Routing ERROR:', e);
            console.error('🔍 Error details:', e.error);
            alert('⚠️ Lỗi routing: ' + (e.error ? e.error.message : 'Unknown error'));
        });
        
        // Thêm event listener cho routing start
        routingControl.on('routingstart', function(e) {
            console.log('🏁 ROUTINGSTART EVENT - Bắt đầu tính đường...');
        });
        
        console.log('✅ Setup routing control hoàn tất');
        
        return { routingControl };
        
    } catch (e) {
        console.error('Routing error:', e);
        
        // Fallback: vẽ đường thẳng
        const routePath = [storeLocation, customerLocation];
        const estPolyline = L.polyline(routePath, {
            color: '#ee4d2d',
            weight: 5,
            opacity: 0.85,
            dashArray: '10,5'
        }).addTo(map);
        
        alert('⚠️ Lỗi routing. Hiển thị đường ước tính.');
        return { polyline: estPolyline };
    }
}

/**
 * Tạo đường đi theo Quốc lộ 1A/Cao tốc Bắc-Nam (tọa độ thực tế)
 * VERSION 3.0: Thêm interpolation để đường mượt hơn (chỉ dùng cho fallback)
 */
function createVietnamRoute(startLocation, endLocation, address) {
    const [startLat, startLng] = startLocation;
    const [endLat, endLng] = endLocation;
    
    // Tính khoảng cách latitude
    const latDiff = endLat - startLat;
    
    console.log('=== CREATE VIETNAM ROUTE DEBUG V3 (fallback) ===');
    console.log('Start:', startLocation, 'End:', endLocation);
    console.log('latDiff:', latDiff, 'degrees');
    
    // Bỏ điều kiện "quá gần" - luôn vẽ theo waypoints hoặc OSRM
    // Ngay cả khoảng cách ngắn cũng cần đi theo đường bộ, không vẽ thẳng
    
    // Tọa độ THỰC TẾ các điểm trên Quốc lộ 1A
    // Thêm các thị trấn/huyện lỵ trên QL1A để đường không cắt qua biển
    const ql1aWaypoints = [
        [10.2397, 105.9571],  // Vĩnh Long
        [10.5359, 106.4131],  // Tân An (Long An)
        [10.7756, 106.7019],  // TP.HCM
        [10.9510, 106.8340],  // Biên Hòa (Đồng Nai)
        [11.0833, 107.0833],  // Xuân Lộc (Đồng Nai) - trên QL1A
        [11.3333, 107.5833],  // Hàm Tân (Bình Thuận) - trên QL1A
        [10.9289, 108.1022],  // Phan Thiết (Bình Thuận)
        [11.1667, 108.4167],  // La Gi (Bình Thuận) - trên QL1A
        [11.5648, 108.9897],  // Phan Rang (Ninh Thuận)
        [12.2388, 109.1967],  // Nha Trang (Khánh Hòa)
        [13.0955, 109.2961],  // Tuy Hòa (Phú Yên)
        [13.7830, 109.2196],  // Quy Nhơn (Bình Định)
        [14.3500, 109.0000],  // Bồng Sơn (Bình Định) - trên QL1A
        [15.1214, 108.8044],  // Quảng Ngãi
        [15.5736, 108.4742],  // Tam Kỳ (Quảng Nam)
        [16.0544, 108.2022],  // Đà Nẵng
        [16.4637, 107.5909],  // Huế (Thừa Thiên Huế)
        [16.8167, 107.1000],  // Đông Hà (Quảng Trị)
        [17.4833, 106.6167],  // Đồng Hới (Quảng Bình)
        [18.3333, 105.9000],  // Hà Tĩnh
        [18.6792, 105.6811],  // Vinh (Nghệ An)
        [19.8067, 105.7761],  // Thanh Hóa
        [20.2506, 105.9745],  // Ninh Bình
        [20.4333, 106.1667],  // Nam Định
        [21.0285, 105.8542],  // Hà Nội
    ];
    
    const route = [startLocation];
    
    // Nếu đi lên phía Bắc
    if (latDiff > 0) {
        console.log('Going North, filtering waypoints...');
        // Thêm các waypoint nằm giữa điểm bắt đầu và điểm kết thúc
        let addedCount = 0;
        let lastPoint = startLocation;
        
        for (const waypoint of ql1aWaypoints) {
            const [wpLat, wpLng] = waypoint;
            
            // Chỉ thêm waypoint nằm giữa start và end
            if (wpLat > startLat && wpLat < endLat) {
                // Thêm điểm nội suy giữa lastPoint và waypoint hiện tại
                const interpolated = interpolatePoints(lastPoint, waypoint, 2);
                route.push(...interpolated);
                route.push(waypoint);
                lastPoint = waypoint;
                addedCount++;
            }
        }
        
        // Nội suy từ waypoint cuối đến điểm đích
        if (lastPoint !== startLocation) {
            const interpolated = interpolatePoints(lastPoint, endLocation, 2);
            route.push(...interpolated);
        }
        
        console.log('Added', addedCount, 'waypoints with interpolation');
    }
    // Nếu đi xuống phía Nam
    else {
        // Đảo ngược thứ tự waypoints
        const reversedWaypoints = [...ql1aWaypoints].reverse();
        
        for (const waypoint of reversedWaypoints) {
            const [wpLat, wpLng] = waypoint;
            
            // Chỉ thêm waypoint nằm giữa start và end
            if (wpLat < startLat && wpLat > endLat) {
                route.push(waypoint);
            }
        }
    }
    
    // Thêm điểm đích cuối cùng
    route.push(endLocation);
    
    console.log('=== ROUTE VERSION 2.0 ===');
    console.log('Route created with', route.length, 'waypoints along QL1A');
    console.log('Start:', startLocation);
    console.log('End:', endLocation);
    console.log('Full route:', route);
    
    return route;
}

function initMap() {
    try {
        // Reset tất cả routing flags
        window.currentDistance = null;
        window.routingCompleted = false;
        window.distanceLocked = false;
        console.log('🔄 Reset tất cả flags - Sẵn sàng cho routing mới');
        
        const storeLocation = [10.2397, 105.9571]; // Vĩnh Long coordinates [lat, lng]
        let customerAddress = "{{ addslashes($addressData['customer_address'] ?? '') }}";
        
        // Tọa độ chính xác từ backend (đã validate)
        const backendCoordinates = {!! json_encode($customerCoordinates ?? null) !!};
        
        console.log('=== INIT MAP DEBUG ===');
        console.log('Customer Address:', customerAddress);
        console.log('Address length:', customerAddress.length);
        console.log('Backend Coordinates:', backendCoordinates);
        console.log('$addressData:', {!! json_encode($addressData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!});
        
        // Kiểm tra địa chỉ rỗng
        if (!customerAddress || customerAddress.trim() === '') {
            console.error('⚠️ NO CUSTOMER ADDRESS!');
            alert('Vui lòng nhập địa chỉ giao hàng');
            return;
        }
        
        // Initialize map centered on Vĩnh Long
        map = L.map('map').setView(storeLocation, 13);
        
        // Add OpenStreetMap tile layer (free!)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Custom icons
        const storeIcon = L.divIcon({
            html: '<div style="background: #ee4d2d; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">A</div>',
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        const customerIcon = L.divIcon({
            html: '<div style="background: #26aa99; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">B</div>',
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        
        // Add store marker (save to global variable)
        storeMarker = L.marker(storeLocation, { icon: storeIcon })
            .addTo(map)
            .bindPopup('<b>Cửa hàng Nàng Thơ</b><br>Vĩnh Long, Việt Nam');
        
        // ===== ƯU TIÊN SỬ DỤNG TỌA ĐỘ TỪ BACKEND =====
        if (backendCoordinates && backendCoordinates.lat && backendCoordinates.lng) {
            console.log('✅ Sử dụng tọa độ chính xác từ backend');
            
            const customerLat = parseFloat(backendCoordinates.lat);
            const customerLng = parseFloat(backendCoordinates.lng);
            const customerLocation = [customerLat, customerLng];
            
            // Add customer marker (draggable) - save to global variable
            customerMarker = L.marker(customerLocation, { 
                icon: customerIcon,
                draggable: true,
                title: 'Kéo để điều chỉnh vị trí'
            }).addTo(map);
            
            // Vẽ routing
            console.log('🚀 Bắt đầu routing với tọa độ từ backend...');
            drawVietnamRoadRoute(map, storeLocation, customerLocation, customerAddress);
            
            // Timeout fallback
            const backendDistance = {{ $distance ?? 5 }};
            setTimeout(() => {
                if (!window.routingCompleted) {
                    console.warn('⏰ OSRM Routing timeout - Fallback sử dụng khoảng cách backend:', backendDistance, 'km');
                    const distanceElement = document.getElementById('distance-display');
                    if (distanceElement && distanceElement.textContent === 'Đang tính...') {
                        distanceElement.textContent = backendDistance.toFixed(1);
                    }
                }
            }, 8000);
            
            // Drag events
            customerMarker.on('drag', function(e) {
                const newLocation = e.target.getLatLng();
                const haversineDistance = map.distance(storeLocation, [newLocation.lat, newLocation.lng]) / 1000;
                window.distanceLocked = false;
                const estimatedDistance = haversineDistance * 1.3;
                const distanceElement = document.getElementById('distance-display');
                if (distanceElement) {
                    distanceElement.textContent = estimatedDistance.toFixed(1);
                }
            });
            
            customerMarker.on('dragend', function(e) {
                const newLocation = e.target.getLatLng();
                console.log('🎯 Drag ended - Vị trí mới:', [newLocation.lat, newLocation.lng]);
                if (window.currentRoutingControl) {
                    map.removeControl(window.currentRoutingControl);
                }
                drawVietnamRoadRoute(map, storeLocation, [newLocation.lat, newLocation.lng], customerAddress);
            });
            
            // Fit map
            const bounds = L.latLngBounds([storeLocation, customerLocation]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
        } else {
            // ===== FALLBACK: GEOCODE NẾU BACKEND KHÔNG CÓ TỌA ĐỘ =====
            console.log('⚠️ Backend không có tọa độ, fallback sang Nominatim geocoding');
            
            let searchAddress = customerAddress;
            if (!searchAddress.toLowerCase().includes('việt nam')) {
                searchAddress += ', Việt Nam';
            }
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddress)}&limit=5&countrycodes=vn&accept-language=vi`, {
                headers: {
                    'User-Agent': 'ShopNangTho/1.0',
                    'Accept-Language': 'vi'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const customerLat = parseFloat(data[0].lat);
                    const customerLng = parseFloat(data[0].lon);
                    const customerLocation = [customerLat, customerLng];
                    
                    customerMarker = L.marker(customerLocation, { 
                        icon: customerIcon,
                        draggable: true,
                        title: 'Kéo để điều chỉnh vị trí'
                    }).addTo(map);
                    
                    drawVietnamRoadRoute(map, storeLocation, customerLocation, customerAddress);
                    
                    const backendDistance = {{ $distance ?? 5 }};
                    setTimeout(() => {
                        if (!window.routingCompleted) {
                            const distanceElement = document.getElementById('distance-display');
                            if (distanceElement && distanceElement.textContent === 'Đang tính...') {
                                distanceElement.textContent = backendDistance.toFixed(1);
                            }
                        }
                    }, 8000);
                    
                    customerMarker.on('drag', function(e) {
                        const newLocation = e.target.getLatLng();
                        const haversineDistance = map.distance(storeLocation, [newLocation.lat, newLocation.lng]) / 1000;
                        window.distanceLocked = false;
                        const estimatedDistance = haversineDistance * 1.3;
                        const distanceElement = document.getElementById('distance-display');
                        if (distanceElement) {
                            distanceElement.textContent = estimatedDistance.toFixed(1);
                        }
                    });
                    
                    customerMarker.on('dragend', function(e) {
                        const newLocation = e.target.getLatLng();
                        if (window.currentRoutingControl) {
                            map.removeControl(window.currentRoutingControl);
                        }
                        drawVietnamRoadRoute(map, storeLocation, [newLocation.lat, newLocation.lng], customerAddress);
                    });
                    
                    const bounds = L.latLngBounds([storeLocation, customerLocation]);
                    map.fitBounds(bounds, { padding: [50, 50] });
                    
                } else {
                    console.warn('Không tìm thấy địa chỉ cụ thể, thử tìm theo tỉnh/thành phố');
                    
                    // Fallback: Tìm theo tỉnh/thành phố
                    let fallbackAddress = 'Vĩnh Long, Việt Nam';
                    
                    // Trích xuất tỉnh/thành từ địa chỉ
                    const addressLower = customerAddress.toLowerCase();
                    if (addressLower.includes('vĩnh long') || addressLower.includes('vinh long')) {
                        fallbackAddress = 'Vĩnh Long, Việt Nam';
                    } else if (addressLower.includes('hồ chí minh') || addressLower.includes('sài gòn')) {
                        fallbackAddress = 'Hồ Chí Minh, Việt Nam';
                    } else if (addressLower.includes('hà nội') || addressLower.includes('ha noi')) {
                        fallbackAddress = 'Hà Nội, Việt Nam';
                    } else if (addressLower.includes('cần thơ') || addressLower.includes('can tho')) {
                        fallbackAddress = 'Cần Thơ, Việt Nam';
                    }
                    
                    console.log('Tìm theo fallback:', fallbackAddress);
                    
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(fallbackAddress)}&limit=1`)
                        .then(response => response.json())
                        .then(fallbackData => {
                            if (fallbackData && fallbackData.length > 0) {
                                const customerLat = parseFloat(fallbackData[0].lat);
                                const customerLng = parseFloat(fallbackData[0].lon);
                                const customerLocation = [customerLat, customerLng];
                                
                                // Add customer marker (draggable) - save to global variable
                                customerMarker = L.marker(customerLocation, { 
                                    icon: customerIcon,
                                    draggable: true,
                                    title: 'Kéo để điều chỉnh vị trí'
                                })
                                    .addTo(map);
                                
                                // Lưu khoảng cách backend để fallback
                                const backendDistance = {{ $distance ?? 5 }};
                                let distance = backendDistance;
                                
                                // Vẽ đường đi theo đường bộ Việt Nam (OSRM + anchor VN) TRƯỚC
                                console.log('🚀 Fallback: Bắt đầu routing OSRM để tính khoảng cách chính xác...');
                                drawVietnamRoadRoute(map, storeLocation, customerLocation, customerAddress);
                                
                                // Đặt timeout để kiểm tra nếu routing không hoàn tất trong 8 giây
                                setTimeout(() => {
                                    if (!window.routingCompleted) {
                                        console.warn('⏰ OSRM Routing timeout - Fallback sử dụng khoảng cách backend:', distance, 'km');
                                        const distanceElement = document.getElementById('distance-display');
                                        if (distanceElement && distanceElement.textContent === 'Đang tính...') {
                                            distanceElement.textContent = distance.toFixed(1);
                                            console.log('✅ Fallback: Hiển thị khoảng cách backend:', distance, 'km');
                                        }
                                    }
                                }, 8000);
                                
                                console.log('Fallback: Sử dụng khoảng cách từ backend:', backendDistance, 'km');
                                
                                // Update distance when marker is dragged
                                customerMarker.on('drag', function(e) {
                                    const newLocation = e.target.getLatLng();
                                    if (polyline) {
                                        polyline.setLatLngs([storeLocation, [newLocation.lat, newLocation.lng]]);
                                    }
                                    
                                    // Unlock để cho phép cập nhật khi user drag
                                    window.distanceLocked = false;
                                    
                                    const haversineDistance = map.distance(storeLocation, [newLocation.lat, newLocation.lng]) / 1000;
                                    distance = haversineDistance * 1.3; // Hệ số đường bộ VN
                                    const distanceElement = document.getElementById('distance-display');
                                    if (distanceElement) {
                                        distanceElement.textContent = distance.toFixed(1);
                                        console.log('🖱️ Fallback drag update: Khoảng cách ước tính:', distance.toFixed(1), 'km');
                                    }
                                });
                                
                                // Khi kéo xong marker (fallback)
                                customerMarker.on('dragend', function(e) {
                                    const newLocation = e.target.getLatLng();
                                    console.log('🎯 Fallback drag ended - Vị trí mới:', [newLocation.lat, newLocation.lng]);
                                    
                                    // Popup đã được xóa để giao diện gọn gàng hơn
                                    
                                    // Vẽ lại routing nếu có
                                    if (window.currentRoutingControl) {
                                        map.removeControl(window.currentRoutingControl);
                                    }
                                    drawVietnamRoadRoute(map, storeLocation, [newLocation.lat, newLocation.lng], customerAddress);
                                });
                                
                                // Fit bounds
                                const bounds = L.latLngBounds([storeLocation, customerLocation]);
                                map.fitBounds(bounds, { padding: [50, 50] });
                            } else {
                                // Show store location only
                                
                                // Show notification
                                L.popup()
                                    .setLatLng(storeLocation)
                                    .setContent('<div style="color: #dc3545;"><b>⚠️ Không tìm thấy địa chỉ giao hàng</b><br><small>Địa chỉ: ' + customerAddress + '</small><br><small>Vui lòng nhập địa chỉ chi tiết hơn (số nhà, đường, phường, quận/huyện, tỉnh/thành phố)</small></div>')
                                    .openOn(map);
                            }
                        });
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
            });
        }
        
    } catch (error) {
        console.error('Map initialization error:', error);
        document.getElementById('map').innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 bg-danger text-white"><i class="bi bi-exclamation-triangle me-2"></i>Lỗi tải bản đồ: ' + error.message + '</div>';
    }
}

/**
 * Cập nhật phí vận chuyển dựa trên khoảng cách OSRM
 * @param {number} distanceKm - Khoảng cách theo km
 * @param {string} customerAddress - Địa chỉ khách hàng (optional, dùng địa chỉ hiện tại nếu không truyền)
 */
function updateShippingFeeWithOSRMDistance(distanceKm, customerAddress = null) {
    console.log('🚚 Cập nhật phí vận chuyển với khoảng cách OSRM:', distanceKm, 'km');
    
    // Lấy địa chỉ hiện tại từ trang nếu không truyền vào
    if (!customerAddress) {
        const displayAddress = document.querySelector('.card-body .text-muted.small');
        customerAddress = displayAddress ? displayAddress.textContent.trim() : "{{ addslashes($addressData['customer_address'] ?? '') }}";
    }
    
    console.log('📍 Địa chỉ sử dụng để tính phí:', customerAddress);
    
    // Gửi AJAX request để tính phí mới
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('osrm_distance', distanceKm);
    formData.append('subtotal', {{ $subtotal ?? 0 }});
    formData.append('customer_address', customerAddress);
    
    fetch('{{ route("checkout.calculateShippingFee") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('✅ Phí vận chuyển mới:', data.shipping_fee);
            console.log('📊 Chi tiết:', {
                distance: data.distance,
                area_type: data.area_type,
                shipping_fee: data.shipping_fee
            });
            
            // Cập nhật tất cả element hiển thị phí vận chuyển
            const shippingElements = document.querySelectorAll('[data-shipping-fee]');
            console.log('🔍 Tìm thấy', shippingElements.length, 'elements để cập nhật phí vận chuyển');
            shippingElements.forEach(element => {
                const oldValue = element.textContent;
                element.textContent = new Intl.NumberFormat('vi-VN').format(data.shipping_fee) + '₫';
                console.log('  ✅ Cập nhật:', oldValue, '→', element.textContent);
            });
            
            // Cập nhật biến shippingFee và tổng tiền
            shippingFee = data.shipping_fee;
            window.currentShippingFee = data.shipping_fee;
            
            // Gọi updateTotal để cập nhật tổng tiền
            if (typeof updateTotal === 'function') {
                updateTotal();
                console.log('✅ Đã gọi updateTotal() để cập nhật tổng tiền');
            } else {
                console.warn('⚠️ Hàm updateTotal() không tồn tại');
            }
            
            // Show notification
            showNotification('Phí vận chuyển đã được cập nhật: ' + new Intl.NumberFormat('vi-VN').format(data.shipping_fee) + '₫', 'success');
        } else {
            console.error('❌ Lỗi tính phí vận chuyển:', data.message);
            showNotification('Lỗi tính phí vận chuyển: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('❌ AJAX Error:', error);
        showNotification('Lỗi kết nối khi tính phí vận chuyển', 'error');
    });
}

// Global variables to store markers and intervals
let customerMarker = null;
let storeMarker = null;
let distanceLockInterval = null; // Lưu interval để có thể clear khi cần
let distanceLockEnabled = false; // Flag để enable/disable lock behavior

/**
 * Update map with new address and coordinates from backend
 */
function updateMapWithCoordinates(newAddress, coordinates) {
    console.log('🔄 Cập nhật bản đồ với tọa độ từ backend:', coordinates);
    
    if (!map) {
        console.error('❌ Map chưa được khởi tạo');
        return;
    }
    
    // DISABLE lock behavior immediately to prevent flickering
    distanceLockEnabled = false;
    console.log('🔓 Đã disable distance lock');
    
    // Clear old lock interval to prevent flickering
    if (distanceLockInterval) {
        clearInterval(distanceLockInterval);
        distanceLockInterval = null;
        console.log('🗑️ Đã clear interval cũ');
    }
    
    // Reset routing flags
    window.currentDistance = null;
    window.routingCompleted = false;
    window.distanceLocked = false;
    
    // Update distance display to loading state
    const distanceElement = document.getElementById('distance-display');
    if (distanceElement) {
        distanceElement.textContent = 'Đang tính...';
    }
    
    const storeLocation = [10.2397, 105.9571]; // Vĩnh Long coordinates
    const customerLat = parseFloat(coordinates.lat);
    const customerLng = parseFloat(coordinates.lng);
    const customerLocation = [customerLat, customerLng];
    
    console.log('✅ Tọa độ khách hàng:', customerLocation);
    
    // Remove old customer marker if exists
    if (customerMarker) {
        map.removeLayer(customerMarker);
    }
    
    // Create new customer marker
    const customerIcon = L.divIcon({
        html: '<div style="background: #26aa99; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">B</div>',
        className: 'custom-marker',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
    });
    
    customerMarker = L.marker(customerLocation, { 
        icon: customerIcon,
        draggable: true,
        title: 'Kéo để điều chỉnh vị trí'
    }).addTo(map);
    
    // Remove old routing control if exists
    if (window.currentRoutingControl) {
        try {
            map.removeControl(window.currentRoutingControl);
            console.log('🗑️ Đã xóa routing control cũ');
        } catch (e) {
            console.warn('⚠️ Không thể xóa routing cũ:', e);
        }
    }
    
    // Draw new route
    console.log('🚀 Vẽ routing mới...');
    drawVietnamRoadRoute(map, storeLocation, customerLocation, newAddress);
    
    // Add drag event listeners
    customerMarker.on('drag', function(e) {
        // Chỉ cập nhật nếu routing đã hoàn tất (tránh giật khi đang load)
        if (!window.routingCompleted) {
            return;
        }
        
        const newLocation = e.target.getLatLng();
        const haversineDistance = map.distance(storeLocation, [newLocation.lat, newLocation.lng]) / 1000;
        window.distanceLocked = false;
        const estimatedDistance = haversineDistance * 1.3;
        const distanceElement = document.getElementById('distance-display');
        if (distanceElement) {
            distanceElement.textContent = estimatedDistance.toFixed(1);
        }
    });
    
    customerMarker.on('dragend', function(e) {
        const newLocation = e.target.getLatLng();
        console.log('🎯 Drag ended - Vị trí mới:', [newLocation.lat, newLocation.lng]);
        if (window.currentRoutingControl) {
            map.removeControl(window.currentRoutingControl);
        }
        drawVietnamRoadRoute(map, storeLocation, [newLocation.lat, newLocation.lng], newAddress);
    });
    
    // Fit map bounds to show both markers
    const bounds = L.latLngBounds([storeLocation, customerLocation]);
    map.fitBounds(bounds, { padding: [50, 50] });
    
    console.log('✅ Bản đồ đã được cập nhật thành công');
}

/**
 * Update map with new address after user changes address (fallback with geocoding)
 */
function updateMapWithNewAddress(newAddress) {
    console.log('🔄 Cập nhật bản đồ với địa chỉ mới:', newAddress);
    
    if (!map) {
        console.error('❌ Map chưa được khởi tạo');
        return;
    }
    
    // DISABLE lock behavior immediately to prevent flickering
    distanceLockEnabled = false;
    console.log('🔓 Đã disable distance lock');
    
    // Clear old lock interval to prevent flickering
    if (distanceLockInterval) {
        clearInterval(distanceLockInterval);
        distanceLockInterval = null;
        console.log('🗑️ Đã clear interval cũ');
    }
    
    // Reset routing flags
    window.currentDistance = null;
    window.routingCompleted = false;
    window.distanceLocked = false;
    
    // Update distance display to loading state
    const distanceElement = document.getElementById('distance-display');
    if (distanceElement) {
        distanceElement.textContent = 'Đang tính...';
    }
    
    const storeLocation = [10.2397, 105.9571]; // Vĩnh Long coordinates
    
    // Add "Việt Nam" to search if not present
    let searchAddress = newAddress;
    if (!searchAddress.toLowerCase().includes('việt nam')) {
        searchAddress += ', Việt Nam';
    }
    
    // Geocode the new address
    console.log('🔍 Geocoding địa chỉ:', searchAddress);
    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(searchAddress)}&limit=5&countrycodes=vn&accept-language=vi`, {
        headers: {
            'User-Agent': 'ShopNangTho/1.0',
            'Accept-Language': 'vi'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.length > 0) {
            const customerLat = parseFloat(data[0].lat);
            const customerLng = parseFloat(data[0].lon);
            const customerLocation = [customerLat, customerLng];
            
            console.log('✅ Geocoding thành công:', customerLocation);
            
            // Remove old customer marker if exists
            if (customerMarker) {
                map.removeLayer(customerMarker);
            }
            
            // Create new customer marker
            const customerIcon = L.divIcon({
                html: '<div style="background: #26aa99; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3);">B</div>',
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });
            
            customerMarker = L.marker(customerLocation, { 
                icon: customerIcon,
                draggable: true,
                title: 'Kéo để điều chỉnh vị trí'
            }).addTo(map);
            
            // Remove old routing control if exists
            if (window.currentRoutingControl) {
                try {
                    map.removeControl(window.currentRoutingControl);
                    console.log('🗑️ Đã xóa routing control cũ');
                } catch (e) {
                    console.warn('⚠️ Không thể xóa routing cũ:', e);
                }
            }
            
            // Draw new route
            console.log('🚀 Vẽ routing mới...');
            drawVietnamRoadRoute(map, storeLocation, customerLocation, newAddress);
            
            // Add drag event listeners
            customerMarker.on('drag', function(e) {
                const newLocation = e.target.getLatLng();
                const haversineDistance = map.distance(storeLocation, [newLocation.lat, newLocation.lng]) / 1000;
                window.distanceLocked = false;
                const estimatedDistance = haversineDistance * 1.3;
                const distanceElement = document.getElementById('distance-display');
                if (distanceElement) {
                    distanceElement.textContent = estimatedDistance.toFixed(1);
                }
            });
            
            customerMarker.on('dragend', function(e) {
                const newLocation = e.target.getLatLng();
                console.log('🎯 Drag ended - Vị trí mới:', [newLocation.lat, newLocation.lng]);
                if (window.currentRoutingControl) {
                    map.removeControl(window.currentRoutingControl);
                }
                drawVietnamRoadRoute(map, storeLocation, [newLocation.lat, newLocation.lng], newAddress);
            });
            
            // Fit map bounds to show both markers
            const bounds = L.latLngBounds([storeLocation, customerLocation]);
            map.fitBounds(bounds, { padding: [50, 50] });
            
            console.log('✅ Bản đồ đã được cập nhật thành công');
            
        } else {
            console.warn('⚠️ Không tìm thấy địa chỉ:', searchAddress);
            showNotification('Không tìm thấy địa chỉ. Vui lòng nhập địa chỉ chi tiết hơn.', 'warning');
        }
    })
    .catch(error => {
        console.error('❌ Geocoding error:', error);
        showNotification('Có lỗi xảy ra khi tìm kiếm địa chỉ', 'error');
    });
}

/**
 * Show notification message using existing toast system
 * Note: This uses the same logic as showToast() in public/js/toast.js
 */
function showNotification(message, type = 'success') {
    // Try to use the global showToast function if available
    if (typeof showToast === 'function') {
        showToast(message, type);
        return;
    }
    
    // Fallback: Use the same logic as toast.js
    const toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        console.error('Toast container not found');
        return;
    }

    const bgClass = {
        'success': 'toast-success-light',
        'error': 'toast-error-light',
        'info': 'toast-info-light',
        'warning': 'toast-warning-light'
    }[type] || 'toast-warning-light';

    const icon = {
        'success': '✅',
        'error': '❌',
        'info': 'ℹ️',
        'warning': '⚠️'
    }[type] || '⚠️';

    const delay = {
        'success': 2500,
        'info': 3000,
        'warning': 3500,
        'error': 4000
    }[type] || 3500;

    const toastHtml = `
        <div class="toast align-items-center ${bgClass} p-2 border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="${delay}">
            <div class="d-flex">
                <div class="toast-body">
                    <span class="me-2">${icon}</span> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    
    const newToast = toastContainer.lastElementChild;
    const bsToast = new bootstrap.Toast(newToast, { delay: delay });
    bsToast.show();

    // Remove toast element after it's hidden
    newToast.addEventListener('hidden.bs.toast', function () {
        newToast.remove();
    });
}

// Initialize map when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure DOM is ready
    setTimeout(initMap, 100);
});
</script>

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
    const subtotal = {{ $subtotal ?? 0 }};
    let shippingFee = {{ $shippingFee ?? 0 }}; // Có thể thay đổi khi có OSRM
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
    
    window.updateTotal = function() {
        const insuranceAmount = insuranceEnabled ? insuranceFee : 0;
        const total = subtotal + shippingFee - currentDiscount + insuranceAmount;
        if (finalTotal) finalTotal.textContent = formatCurrency(total);
        if (footerTotal) footerTotal.textContent = formatCurrency(total);
        if (discountAmount) discountAmount.textContent = '-' + formatCurrency(currentDiscount);
        
        // Update insurance row visibility
        const insuranceRow = document.getElementById('insurance-row');
        if (insuranceRow) {
            if (insuranceEnabled) {
                insuranceRow.classList.remove('d-none');
            } else {
                insuranceRow.classList.add('d-none');
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
                paymentMethodText.textContent = 'Thanh toán qua MoMo';
            } else if (selectedMethod === 'payos') {
                paymentMethodText.textContent = 'Thanh toán qua PayOS';
            } else if (selectedMethod === 'sepay') {
                paymentMethodText.textContent = 'Chuyển khoản qua SePay';
            } else {
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

        // Handle form submission with AJAX to update map without page reload
        const editForm = document.getElementById('editAddressForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e){
                e.preventDefault(); // Prevent default form submission
                
                const addrEl = editForm.querySelector('textarea[name="customer_address"]');
                const provText = document.getElementById('edit_province_name').value || $editProv.find('option:selected').text();
                const wardText = document.getElementById('edit_ward_name').value || $editWard.find('option:selected').text();
                const parts = [];
                if (addrEl && addrEl.value) parts.push(addrEl.value.trim());
                if (wardText && wardText.indexOf('Chọn') === -1) parts.push(wardText.trim());
                if (provText && provText.indexOf('Chọn') === -1) parts.push(provText.trim());
                if (addrEl) addrEl.value = parts.join(', ');
                
                // Get form data
                const formData = new FormData(editForm);
                const submitBtn = editForm.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...';
                
                // Submit via AJAX
                fetch(editForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('✅ Địa chỉ đã được lưu:', data);
                        
                        // Update displayed address on page
                        const displayName = document.querySelector('.card-body strong');
                        const displayPhone = document.querySelector('.card-body .text-muted');
                        const displayAddress = document.querySelector('.card-body .text-muted.small');
                        
                        if (displayName) displayName.textContent = formData.get('customer_name');
                        if (displayPhone) displayPhone.textContent = formData.get('customer_phone');
                        if (displayAddress) displayAddress.textContent = formData.get('customer_address');
                        
                        // Close modal
                        const modal = bootstrap.Modal.getInstance(editAddressModal);
                        modal.hide();
                        
                        // Update map with new address and coordinates from backend
                        if (data.coordinates && data.coordinates.lat && data.coordinates.lng) {
                            console.log('✅ Sử dụng tọa độ từ backend:', data.coordinates);
                            updateMapWithCoordinates(formData.get('customer_address'), data.coordinates);
                        } else {
                            console.log('⚠️ Backend không trả về tọa độ, fallback sang geocoding');
                            updateMapWithNewAddress(formData.get('customer_address'));
                        }
                        
                        // Show success message
                        showNotification('Địa chỉ đã được cập nhật thành công!', 'success');
                    } else {
                        console.error('❌ Lỗi lưu địa chỉ:', data.message);
                        showNotification(data.message || 'Có lỗi xảy ra khi lưu địa chỉ', 'error');
                    }
                })
                .catch(error => {
                    console.error('❌ AJAX Error:', error);
                    showNotification('Có lỗi xảy ra khi lưu địa chỉ', 'error');
                })
                .finally(() => {
                    // Restore button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
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
