# 🎯 Hướng Dẫn Tích Hợp MoMo Payment Gateway

## 📋 Tổng Quan

Tài liệu này hướng dẫn chi tiết cách tích hợp MoMo Payment Gateway vào hệ thống Shop Phụ Kiện Thời Trang.

## ✅ Các Thành Phần Đã Được Tích Hợp

### 1. **MoMoService** (`app/Services/MoMoService.php`)
- Service xử lý tạo payment request
- Verify signature từ MoMo
- Giao tiếp với MoMo API

### 2. **MomoController** (`app/Http/Controllers/MomoController.php`)
- `createPayment()`: Tạo payment request
- `returnPayment()`: Xử lý callback khi user hoàn tất thanh toán
- `notifyPayment()`: Xử lý IPN (webhook) từ MoMo

### 3. **Routes** (`routes/web.php`)
```php
// MOMO Routes
Route::get('/checkout/momo', [MomoController::class, 'createPayment'])->name('momo.create');
Route::get('/checkout/momo/return', [MomoController::class, 'returnPayment'])->name('momo.return');
Route::post('/checkout/momo/notify', [MomoController::class, 'notifyPayment'])->name('momo.notify');
```

### 4. **Configuration Files**
- `config/momo.php`: Cấu hình MoMo credentials
- `config/services.php`: Thêm MoMo vào services

## 🔧 Cấu Hình

### Bước 1: Đăng Ký Tài Khoản MoMo Business

1. Truy cập: https://business.momo.vn/
2. Đăng ký tài khoản doanh nghiệp
3. Hoàn tất thủ tục xác minh
4. Lấy thông tin:
   - **Partner Code**
   - **Access Key**
   - **Secret Key**

### Bước 2: Cấu Hình File `.env`

Thêm các dòng sau vào file `.env`:

```env
# MoMo Payment Gateway Configuration
MOMO_PARTNER_CODE=your_partner_code_here
MOMO_ACCESS_KEY=your_access_key_here
MOMO_SECRET_KEY=your_secret_key_here

# MoMo Endpoint (Test/Production)
# Test environment
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create

# Production environment (uncomment when going live)
# MOMO_ENDPOINT=https://payment.momo.vn/v2/gateway/api/create

# MoMo Callback URLs
MOMO_RETURN_URL=${APP_URL}/checkout/momo/return
MOMO_NOTIFY_URL=${APP_URL}/checkout/momo/notify
```

### Bước 3: Test Environment (Sandbox)

Để test, bạn có thể sử dụng thông tin test của MoMo:

```env
# MoMo Test Credentials (Sandbox)
MOMO_PARTNER_CODE=MOMOBKUN20180529
MOMO_ACCESS_KEY=klm05TvNBzhg7h7j
MOMO_SECRET_KEY=at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
```

**Lưu ý:** Đây là thông tin test công khai của MoMo. Khi chuyển sang production, phải thay bằng credentials thật.

### Bước 4: Cấu Hình Webhook/IPN URL

Trong trang quản lý MoMo Business:

1. Vào **Cấu hình API**
2. Thiết lập **IPN URL**: `https://yourdomain.com/checkout/momo/notify`
3. Thiết lập **Return URL**: `https://yourdomain.com/checkout/momo/return`

**Quan trọng:** URL phải là HTTPS và có thể truy cập công khai từ internet.

## 🚀 Cách Sử Dụng

### Luồng Thanh Toán

1. **User chọn sản phẩm** → Thêm vào giỏ hàng
2. **Checkout** → Nhập địa chỉ giao hàng
3. **Chọn phương thức thanh toán** → Chọn "MoMo"
4. **Đặt hàng** → Hệ thống tạo order và redirect đến MoMo
5. **Thanh toán trên MoMo** → User quét QR hoặc nhập thông tin
6. **Callback** → MoMo gọi về hệ thống để cập nhật trạng thái
7. **Hoàn tất** → User được redirect về trang đơn hàng

### Code Flow

```php
// 1. User chọn payment method = 'momo' trong checkout form
// 2. CheckoutController@place tạo order và gọi MoMoService

if ($paymentMethod === 'momo') {
    $momoService = new MoMoService();
    $orderInfo = "Thanh toán đơn hàng #" . $order->id;
    $result = $momoService->createPayment($order->id, $total, $orderInfo);

    if (isset($result['payUrl'])) {
        return redirect($result['payUrl']); // Redirect đến MoMo
    }
}

// 3. MoMo xử lý thanh toán và callback về MomoController@returnPayment
// 4. Verify signature và cập nhật payment status
```

## 🧪 Testing

### Test với MoMo Sandbox

1. Sử dụng credentials test ở trên
2. Chạy ứng dụng local với ngrok để có HTTPS URL:
   ```bash
   ngrok http 8000
   ```
3. Cập nhật `APP_URL` trong `.env` với URL ngrok
4. Cập nhật IPN URL và Return URL trong MoMo Business Portal
5. Thực hiện thanh toán test

### Test Payment Flow

```bash
# 1. Tạo đơn hàng test
# 2. Chọn MoMo payment
# 3. Scan QR code với MoMo app (test)
# 4. Kiểm tra logs
tail -f storage/logs/laravel.log | grep MoMo
```

## 📊 Database Schema

### Bảng `payments`

```sql
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `transaction_code` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

## 🔐 Security

### Signature Verification

MoMo sử dụng HMAC SHA256 để verify signature:

```php
// Trong MoMoService@verifySignature
$rawHash = "accessKey=" . $this->accessKey .
    "&amount=" . $data['amount'] .
    "&extraData=" . $data['extraData'] .
    // ... các field khác
    
$signature = hash_hmac("sha256", $rawHash, $this->secretKey);
return $signature === $data['signature'];
```

**Quan trọng:** 
- Luôn verify signature trước khi xử lý callback
- Không tin tưởng dữ liệu từ client
- Log tất cả các request/response để audit

## 🐛 Troubleshooting

### Lỗi Thường Gặp

#### 1. "Invalid signature"
**Nguyên nhân:** Secret key không đúng hoặc cách tạo signature sai
**Giải pháp:** 
- Kiểm tra lại `MOMO_SECRET_KEY` trong `.env`
- Đảm bảo thứ tự các field trong rawHash đúng theo tài liệu MoMo

#### 2. "Cannot connect to MoMo"
**Nguyên nhân:** Endpoint không đúng hoặc network issue
**Giải pháp:**
- Kiểm tra `MOMO_ENDPOINT` trong `.env`
- Test kết nối: `curl https://test-payment.momo.vn/v2/gateway/api/create`

#### 3. "IPN URL not reachable"
**Nguyên nhân:** MoMo không thể gọi đến webhook URL
**Giải pháp:**
- Đảm bảo URL là HTTPS
- Đảm bảo URL có thể truy cập công khai (không localhost)
- Sử dụng ngrok cho development

#### 4. "Payment not updated after successful payment"
**Nguyên nhân:** IPN callback không được xử lý
**Giải pháp:**
- Kiểm tra logs: `storage/logs/laravel.log`
- Verify IPN URL trong MoMo Business Portal
- Test webhook với Postman

## 📝 Logs và Monitoring

### Kiểm Tra Logs

```bash
# Xem tất cả MoMo logs
tail -f storage/logs/laravel.log | grep "MoMo"

# Xem payment requests
tail -f storage/logs/laravel.log | grep "MoMo Payment Request"

# Xem callbacks
tail -f storage/logs/laravel.log | grep "MoMo Return Callback"
```

### Log Events

Hệ thống log các events sau:
- `MoMo Payment Request`: Khi tạo payment request
- `MoMo Return Callback`: Khi user quay về từ MoMo
- `MoMo IPN Notification`: Khi nhận IPN từ MoMo
- `MoMo Payment Completed`: Khi thanh toán thành công

## 🌐 Production Deployment

### Checklist Trước Khi Go Live

- [ ] Đăng ký tài khoản MoMo Business chính thức
- [ ] Lấy credentials production từ MoMo
- [ ] Cập nhật `.env` với credentials production
- [ ] Thay đổi `MOMO_ENDPOINT` sang production URL
- [ ] Cấu hình IPN URL và Return URL trong MoMo Portal
- [ ] Test kỹ lưỡng trên staging environment
- [ ] Thiết lập monitoring và alerting
- [ ] Backup database trước khi deploy
- [ ] Chuẩn bị rollback plan

### Production Configuration

```env
# Production MoMo Configuration
MOMO_PARTNER_CODE=YOUR_PRODUCTION_PARTNER_CODE
MOMO_ACCESS_KEY=YOUR_PRODUCTION_ACCESS_KEY
MOMO_SECRET_KEY=YOUR_PRODUCTION_SECRET_KEY
MOMO_ENDPOINT=https://payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=https://yourdomain.com/checkout/momo/return
MOMO_NOTIFY_URL=https://yourdomain.com/checkout/momo/notify
```

## 📚 Tài Liệu Tham Khảo

- [MoMo Developer Documentation](https://developers.momo.vn/)
- [MoMo API Reference](https://developers.momo.vn/v3/docs/payment/api/wallet/onetime)
- [MoMo Business Portal](https://business.momo.vn/)

## 💡 Tips và Best Practices

1. **Luôn verify signature** trước khi xử lý callback
2. **Log đầy đủ** để dễ debug
3. **Handle timeout** khi gọi API MoMo
4. **Idempotency**: Đảm bảo xử lý callback nhiều lần không tạo duplicate payment
5. **Error handling**: Xử lý gracefully khi MoMo service down
6. **Test thoroughly**: Test cả success và failure scenarios
7. **Monitor**: Thiết lập monitoring cho payment flow

## 🆘 Support

Nếu gặp vấn đề:
1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Xem lại cấu hình trong `.env`
3. Tham khảo tài liệu MoMo
4. Liên hệ MoMo support: support@momo.vn

---

**Tác giả:** Cascade AI  
**Ngày tạo:** 2025-01-24  
**Phiên bản:** 1.0
