@extends('layouts.app')
@section('title', 'Thanh toán SePay')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-bank me-2"></i>
                        Thông tin chuyển khoản SePay
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Vui lòng chuyển khoản theo thông tin bên dưới để hoàn tất đơn hàng
                    </div>

                    <div class="bank-info p-4 bg-light rounded mb-4">
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold">Ngân hàng:</div>
                            <div class="col-8">
                                <strong class="text-primary">{{ $transaction['bank_name'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold">Số tài khoản:</div>
                            <div class="col-8">
                                <div class="d-flex align-items-center">
                                    <strong class="text-danger fs-5 me-2" id="account-number">{{ $transaction['account_number'] ?? 'N/A' }}</strong>
                                    <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('account-number')">
                                        <i class="bi bi-clipboard"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold">Chủ tài khoản:</div>
                            <div class="col-8">
                                <strong>{{ $transaction['account_name'] ?? 'N/A' }}</strong>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-4 fw-semibold">Số tiền:</div>
                            <div class="col-8">
                                <div class="d-flex align-items-center">
                                    <strong class="text-success fs-4 me-2" id="amount">{{ number_format($transaction['amount'], 0, ',', '.') }}₫</strong>
                                    <button class="btn btn-sm btn-outline-success" onclick="copyToClipboard('amount')">
                                        <i class="bi bi-clipboard"></i> Sao chép
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4 fw-semibold">Nội dung CK:</div>
                            <div class="col-8">
                                <div class="d-flex align-items-center">
                                    <strong class="text-info me-2" id="transfer-content">{{ $transaction['transfer_content'] ?? 'N/A' }}</strong>
                                    <button class="btn btn-sm btn-outline-info" onclick="copyToClipboard('transfer-content')">
                                        <i class="bi bi-clipboard"></i> Sao chép
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Vui lòng nhập chính xác nội dung này để hệ thống tự động xác nhận thanh toán
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code VietQR -->
                    <div class="text-center mb-4">
                        <div class="qr-placeholder bg-white border rounded p-3 d-inline-block">
                            @php
                                $bankCode = strtoupper($transaction['bank_code'] ?? 'MB');
                                $accountNumber = $transaction['account_number'] ?? '';
                                $amount = $transaction['amount'] ?? 0;
                                $transferContent = $transaction['transfer_content'] ?? '';
                                $accountName = $transaction['account_name'] ?? '';
                                
                                // Tạo URL VietQR chuẩn
                                $qrUrl = "https://img.vietqr.io/image/{$bankCode}-{$accountNumber}-compact2.jpg?amount={$amount}&addInfo=" . urlencode($transferContent) . "&accountName=" . urlencode($accountName);
                            @endphp
                            <img src="{{ $qrUrl }}" 
                                 alt="QR Code chuyển khoản" 
                                 class="img-fluid"
                                 style="max-width: 250px;"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/250x250?text=QR+Code+Error';">
                            <p class="small text-muted mt-2 mb-0">Quét mã QR để chuyển khoản nhanh</p>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-clock-history me-2"></i>
                        <strong>Lưu ý:</strong> Sau khi chuyển khoản thành công, hệ thống sẽ tự động xác nhận trong vòng 1-5 phút. 
                        Bạn có thể đóng trang này và kiểm tra trạng thái đơn hàng trong mục "Đơn hàng của tôi".
                    </div>

                    <div class="d-flex gap-2 justify-content-center">
                        <a href="{{ route('sepay.return', ['order_id' => $transaction['order_id'], 'status' => 'pending']) }}" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>
                            Đã chuyển khoản
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house me-2"></i>
                            Về trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Copy to clipboard function
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.textContent.trim();
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i> Đã sao chép';
        btn.classList.add('btn-success');
        btn.classList.remove('btn-outline-primary', 'btn-outline-success', 'btn-outline-info');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            if (elementId === 'account-number') {
                btn.classList.add('btn-outline-primary');
            } else if (elementId === 'amount') {
                btn.classList.add('btn-outline-success');
            } else {
                btn.classList.add('btn-outline-info');
            }
        }, 2000);
    }).catch(err => {
        console.error('Copy failed:', err);
        alert('Không thể sao chép. Vui lòng sao chép thủ công.');
    });
}

// Tự động kiểm tra trạng thái thanh toán
let checkInterval;
let checkCount = 0;
const maxChecks = 60;
const orderId = {{ $transaction['order_id'] ?? 0 }};

function checkPaymentStatus() {
    if (checkCount >= maxChecks) {
        clearInterval(checkInterval);
        console.log('Đã hết thời gian kiểm tra tự động');
        return;
    }
    
    checkCount++;
    
    fetch('/api/payment/check/' + orderId)
        .then(response => response.json())
        .then(data => {
            console.log('Payment status:', data);
            
            if (data.status === 'completed') {
                clearInterval(checkInterval);
                showSuccessNotification();
                
                setTimeout(() => {
                    window.location.href = '/checkout/sepay-return?order_id=' + orderId + '&status=completed';
                }, 3000);
            }
        })
        .catch(error => {
            console.error('Error checking payment status:', error);
        });
}

function showSuccessNotification() {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '400px';
    notification.innerHTML = '<div class="d-flex align-items-center"><i class="bi bi-check-circle-fill fs-3 me-3 text-success"></i><div><h5 class="mb-1">Thanh toán thành công!</h5><p class="mb-0">Đơn hàng của bạn đã được xác nhận. Đang chuyển hướng...</p></div></div><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    
    document.body.appendChild(notification);
}

if (orderId > 0) {
    checkInterval = setInterval(checkPaymentStatus, 5000);
    checkPaymentStatus();
}
</script>
@endpush

@push('styles')
<style>
.bank-info {
    border-left: 4px solid #0d6efd;
}

.qr-placeholder {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endpush
@endsection
