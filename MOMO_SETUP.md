# Hướng dẫn cấu hình MoMo Payment Gateway

## Bước 1: Đăng ký tài khoản MoMo Business

1. Truy cập: https://business.momo.vn/
2. Đăng ký tài khoản doanh nghiệp
3. Sau khi được duyệt, truy cập MoMo Developer Portal: https://developers.momo.vn/

## Bước 2: Lấy thông tin API

1. Đăng nhập vào MoMo Developer Portal
2. Tạo ứng dụng mới hoặc sử dụng ứng dụng test
3. Lấy các thông tin sau:
   - **Partner Code**: Mã đối tác
   - **Access Key**: Khóa truy cập
   - **Secret Key**: Khóa bí mật

## Bước 3: Cấu hình trong file .env

Thêm các dòng sau vào file `.env`:

```env
# MoMo Payment Gateway
MOMO_PARTNER_CODE=your_partner_code_here
MOMO_ACCESS_KEY=your_access_key_here
MOMO_SECRET_KEY=your_secret_key_here
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
MOMO_RETURN_URL=${APP_URL}/checkout/momo/return
MOMO_NOTIFY_URL=${APP_URL}/checkout/momo/notify
```

## Bước 4: Test với MoMo Sandbox

Để test, sử dụng thông tin sau (MoMo Test Environment):

```env
MOMO_PARTNER_CODE=MOMOBKUN20180529
MOMO_ACCESS_KEY=klm05TvNBzhg7h7j
MOMO_SECRET_KEY=at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa
MOMO_ENDPOINT=https://test-payment.momo.vn/v2/gateway/api/create
```

**Lưu ý**: Đây là thông tin test, chỉ dùng để phát triển. Khi lên production, cần thay bằng thông tin thật.

## Bước 5: Test thanh toán

1. Thêm sản phẩm vào giỏ hàng
2. Vào checkout → Nhập địa chỉ
3. Tại trang thanh toán, chọn "THAY ĐỔI" ở phương thức thanh toán
4. Chọn "Ví điện tử MoMo"
5. Nhấn "Đặt hàng"
6. Hệ thống sẽ chuyển hướng đến trang thanh toán MoMo

## Bước 6: Cấu hình Webhook (Production)

Khi lên production, cần cấu hình webhook URL trong MoMo Developer Portal:

- **IPN URL (Notify URL)**: `https://yourdomain.com/checkout/momo/notify`
- **Return URL**: `https://yourdomain.com/checkout/momo/return`

## Tài liệu tham khảo

- MoMo Developer Docs: https://developers.momo.vn/v3/
- API Reference: https://developers.momo.vn/v3/docs/payment/api/wallet/onetime
