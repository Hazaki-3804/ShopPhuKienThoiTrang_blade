# Hướng dẫn cài đặt hệ thống phí vận chuyển

## Bước 1: Chạy migration để tạo bảng

Mở terminal và chạy lệnh:

```bash
php artisan migrate
```

Lệnh này sẽ tạo các bảng:
- `shipping_fees` - Lưu quy tắc phí vận chuyển
- `discount_id`, `discount_code`, `discount_amount` trong bảng `orders` - Lưu thông tin voucher

## Bước 2: Thêm dữ liệu mẫu (Tùy chọn)

Chạy seeder để thêm 6 quy tắc phí ship mẫu:

```bash
php artisan db:seed --class=ShippingFeeSeeder
```

Các quy tắc mẫu bao gồm:
1. Miễn phí ship đơn 500k nội thành
2. Hỗ trợ 20k đơn 300k nội thành
3. Phí chuẩn 30k nội thành
4. Hỗ trợ 30k đơn 500k lân cận
5. Phí lân cận 40k + 2k/km
6. Phí toàn quốc 50k

## Bước 3: Truy cập trang quản lý

Đăng nhập admin và vào menu:
**Quản lý phí vận chuyển**

Tại đây bạn có thể:
- Xem danh sách quy tắc
- Thêm quy tắc mới
- Chỉnh sửa quy tắc
- Xóa quy tắc
- Bật/tắt quy tắc

## Lưu ý quan trọng:

### Nếu chưa chạy migration:
- Hệ thống vẫn hoạt động bình thường
- Phí ship mặc định: **30.000₫**
- Không có lỗi xảy ra

### Sau khi chạy migration:
- Phí ship được tính động theo quy tắc
- Ưu tiên quy tắc có `priority` cao
- Nếu không có quy tắc nào phù hợp → Phí mặc định 30k

## Cấu trúc quy tắc phí ship:

### Các trường quan trọng:

1. **name**: Tên quy tắc (VD: "Miễn phí ship đơn 500k")
2. **area_type**: Loại khu vực
   - `local`: Nội thành Vĩnh Long
   - `nearby`: Lân cận
   - `nationwide`: Toàn quốc

3. **min_distance / max_distance**: Khoảng cách áp dụng (km)
   - VD: 0-10km cho nội thành

4. **min_order_value**: Giá trị đơn hàng tối thiểu
   - VD: 300000 = đơn từ 300k

5. **base_fee**: Phí cơ bản (₫)
6. **per_km_fee**: Phí mỗi km (₫)
7. **max_fee**: Phí tối đa (₫)
8. **is_free_shipping**: Miễn phí ship (true/false)
9. **priority**: Độ ưu tiên (số càng cao càng ưu tiên)
10. **status**: Kích hoạt (true/false)

### Công thức tính phí:

```
Phí ship = base_fee + (distance × per_km_fee)
```

Nếu có `max_fee`, phí sẽ không vượt quá giá trị này.

## Ví dụ thực tế:

### Quy tắc 1: Miễn phí ship
- Đơn hàng: 600.000₫
- Khoảng cách: 5km
- Khu vực: Nội thành
- **→ Phí: 0₫** (miễn phí)

### Quy tắc 2: Hỗ trợ ship
- Đơn hàng: 350.000₫
- Khoảng cách: 5km
- Khu vực: Nội thành
- **→ Phí: 10.000₫** (hỗ trợ 20k)

### Quy tắc 3: Phí chuẩn
- Đơn hàng: 200.000₫
- Khoảng cách: 5km
- Khu vực: Nội thành
- **→ Phí: 30.000₫** (phí chuẩn)

## Khắc phục sự cố:

### Lỗi: "Base table or view not found: 1146 Table 'shipping_fees' doesn't exist"
**Giải pháp**: Chạy `php artisan migrate`

### Phí ship không đúng:
1. Kiểm tra độ ưu tiên (priority) của các quy tắc
2. Kiểm tra điều kiện: khoảng cách, giá trị đơn hàng, khu vực
3. Đảm bảo quy tắc đang được kích hoạt (status = true)

### Muốn về phí mặc định 30k:
- Tắt tất cả quy tắc (status = false)
- Hoặc xóa tất cả quy tắc

## Tùy chỉnh:

Bạn có thể tạo quy tắc riêng theo nhu cầu:
- Miễn phí ship cho khách VIP
- Giảm phí ship vào dịp khuyến mãi
- Phí ship khác nhau theo sản phẩm
- Phí ship theo trọng lượng

---

**Lưu ý**: Sau khi thay đổi quy tắc, không cần restart server. Thay đổi có hiệu lực ngay lập tức.
