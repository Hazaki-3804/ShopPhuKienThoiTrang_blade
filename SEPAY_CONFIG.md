# Hướng dẫn cấu hình SePay

## 1. Thêm các biến môi trường vào file `.env`

Thêm các dòng sau vào file `.env` của bạn:

```env
# SePay Configuration
SEPAY_ACCOUNT_NUMBER=your_account_number
SEPAY_ACCOUNT_NAME=YOUR ACCOUNT NAME
SEPAY_BANK_CODE=VCB
SEPAY_BANK_NAME=Vietcombank
SEPAY_WEBHOOK_SECRET=your_webhook_secret_key
```

## 2. Giải thích các biến

- **SEPAY_ACCOUNT_NUMBER**: Số tài khoản ngân hàng nhận tiền
- **SEPAY_ACCOUNT_NAME**: Tên chủ tài khoản (viết hoa, không dấu)
- **SEPAY_BANK_CODE**: Mã ngân hàng (VCB, TCB, MB, ACB, v.v.)
- **SEPAY_BANK_NAME**: Tên đầy đủ ngân hàng
- **SEPAY_WEBHOOK_SECRET**: Khóa bí mật để xác thực webhook từ SePay

## 3. Cách hoạt động

1. Khách hàng chọn phương thức thanh toán **SePay**
2. Hệ thống tạo đơn hàng và hiển thị thông tin chuyển khoản:
   - Số tài khoản
   - Tên chủ tài khoản
   - Số tiền cần chuyển
   - Nội dung chuyển khoản (format: `NANGTHOSHOP {order_id}`)
3. Khách hàng thực hiện chuyển khoản qua app ngân hàng
4. SePay gửi webhook về server khi phát hiện giao dịch
5. Hệ thống tự động xác nhận thanh toán và cập nhật trạng thái đơn hàng

## 4. Webhook URL

Để nhận thông báo từ SePay, bạn cần cấu hình webhook URL trong dashboard SePay:

```
https://your-domain.com/sepay/callback
```

**Lưu ý**: URL này phải là HTTPS và có thể truy cập công khai từ internet.

## 5. Test thanh toán

### Môi trường Development:
- Sử dụng tài khoản ngân hàng thật của bạn
- Chuyển khoản số tiền nhỏ để test
- Kiểm tra log để debug: `storage/logs/laravel.log`

### Môi trường Production:
- Đảm bảo webhook URL hoạt động
- Kiểm tra SSL certificate
- Monitor log thường xuyên

## 6. Các route đã được tạo

```php
// Trang hiển thị thông tin chuyển khoản
Route::get('/sepay/payment', [SePayController::class, 'paymentPage'])->name('sepay.payment.page');

// Tạo thanh toán SePay
Route::post('/sepay/create-payment', [SePayController::class, 'createPayment'])->name('sepay.create');

// Lấy thông tin ngân hàng
Route::get('/sepay/bank-transfer-info', [SePayController::class, 'getBankTransferInfo'])->name('sepay.bank.info');

// Webhook callback từ SePay
Route::post('/sepay/callback', [SePayController::class, 'callback'])->name('sepay.callback');

// Return URL sau khi chuyển khoản
Route::get('/sepay/return', [SePayController::class, 'returnUrl'])->name('sepay.return');

// Kiểm tra trạng thái thanh toán
Route::get('/sepay/check-status', [SePayController::class, 'checkStatus'])->name('sepay.check.status');
```

## 7. Troubleshooting

### Webhook không hoạt động:
- Kiểm tra webhook URL có đúng không
- Kiểm tra signature verification
- Xem log tại `storage/logs/laravel.log`

### Thanh toán không được xác nhận tự động:
- Kiểm tra nội dung chuyển khoản có đúng format không
- Kiểm tra webhook secret key
- Kiểm tra SePay dashboard xem có nhận được transaction không

### Lỗi khi tạo payment:
- Kiểm tra các biến môi trường trong `.env`
- Chạy `php artisan config:clear` để clear cache config
- Kiểm tra log errors

## 8. Bảo mật

- **KHÔNG** commit file `.env` lên Git
- Giữ `SEPAY_WEBHOOK_SECRET` bí mật
- Sử dụng HTTPS cho production
- Validate signature trong webhook callback
- Log tất cả các giao dịch để audit

## 9. Support

Nếu có vấn đề, vui lòng:
1. Kiểm tra log tại `storage/logs/laravel.log`
2. Kiểm tra SePay dashboard
3. Liên hệ support SePay nếu cần
