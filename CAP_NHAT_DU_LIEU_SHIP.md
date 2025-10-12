# Cập nhật dữ liệu phí vận chuyển

## Bước 1: Chạy lệnh cập nhật dữ liệu

Mở terminal và chạy:

```bash
php artisan db:seed --class=ShippingFeeSeeder
```

Lệnh này sẽ:
- Xóa tất cả quy tắc cũ
- Thêm 6 quy tắc mới hợp lý

## Bước 2: Kiểm tra dữ liệu mới

Truy cập: **Admin → Quản lý phí vận chuyển**

Bạn sẽ thấy 6 quy tắc:

### 1. Miễn phí ship nội thành - Đơn từ 300k ✅
- Khu vực: Nội thành Vĩnh Long
- Khoảng cách: 0-10km
- Đơn tối thiểu: 300.000₫
- Phí: **MIỄN PHÍ**
- Priority: 100 (cao nhất)
- Trạng thái: **Kích hoạt**

### 2. Hỗ trợ ship 20k - Đơn từ 300k (DỰ PHÒNG) ⏸️
- Khu vực: Nội thành Vĩnh Long
- Khoảng cách: 0-10km
- Đơn tối thiểu: 300.000₫
- Phí: 10.000₫ (max 20k)
- Priority: 90
- Trạng thái: **TẮT** (vì đã có miễn phí)

### 3. Phí ship nội thành chuẩn ✅
- Khu vực: Nội thành Vĩnh Long
- Khoảng cách: 0-10km
- Đơn tối thiểu: 0₫
- Phí: **30.000₫**
- Priority: 50
- Trạng thái: **Kích hoạt**

### 4. Hỗ trợ ship 30k - Đơn từ 500k lân cận ✅
- Khu vực: Lân cận
- Khoảng cách: 10-30km
- Đơn tối thiểu: 500.000₫
- Phí: 20.000₫ + 1.000₫/km (max 50k)
- Priority: 80
- Trạng thái: **Kích hoạt**

### 5. Phí ship lân cận chuẩn ✅
- Khu vực: Lân cận
- Khoảng cách: 10-30km
- Đơn tối thiểu: 0₫
- Phí: 40.000₫ + 2.000₫/km (max 100k)
- Priority: 40
- Trạng thái: **Kích hoạt**

### 6. Phí ship toàn quốc ✅
- Khu vực: Toàn quốc
- Khoảng cách: >30km
- Đơn tối thiểu: 99.000₫
- Phí: **50.000₫**
- Priority: 30
- Trạng thái: **Kích hoạt**

## Bước 3: Test hệ thống

### Test 1: Khách ở nội thành Vĩnh Long
**Địa chỉ**: "123 Phường 1, TP Vĩnh Long"

- Đơn 350.000₫ → **Miễn phí** ✅
- Đơn 250.000₫ → **30.000₫** ✅

### Test 2: Khách ở Long Hồ (lân cận)
**Địa chỉ**: "456 Long Hồ, Vĩnh Long"

- Đơn 600.000₫ → **20k + 5×1k = 25.000₫** ✅
- Đơn 400.000₫ → **40k + 5×2k = 50.000₫** ✅

### Test 3: Khách ở TP.HCM (toàn quốc)
**Địa chỉ**: "789 Quận 1, TP.HCM"

- Đơn 150.000₫ → **50.000₫** ✅

## Cách hệ thống xác định khu vực:

Hệ thống tự động phát hiện dựa trên **từ khóa** trong địa chỉ:

### Nội thành (local):
- "phường 1", "phường 2", ..., "phường 9"
- "thành phố vĩnh long", "tp vĩnh long"
- "vĩnh long", "vinh long"

### Lân cận (nearby):
- "long hồ", "long ho"
- "mang thít", "mang thit"
- "vũng liêm", "vung liem"
- "tam bình", "tam binh"
- "trà ôn", "tra on"
- "bình minh", "binh minh"
- "bình tân", "binh tan"

### Toàn quốc (nationwide):
- Tất cả địa chỉ khác

## Lưu ý quan trọng:

1. **Độ ưu tiên (Priority)**:
   - Số càng cao càng được áp dụng trước
   - Quy tắc 100 sẽ được kiểm tra trước quy tắc 90

2. **Khoảng cách**:
   - Hiện tại dùng mặc định 5km
   - Có thể nâng cấp lên Google Maps API sau

3. **Thứ tự kiểm tra**:
   - Kiểm tra khu vực (local/nearby/nationwide)
   - Kiểm tra giá trị đơn hàng
   - Kiểm tra khoảng cách
   - Áp dụng quy tắc đầu tiên phù hợp

## Nếu muốn thay đổi:

### Thay đổi phí miễn phí từ 300k → 500k:
1. Vào **Quản lý phí vận chuyển**
2. Click **Sửa** quy tắc "Miễn phí ship nội thành"
3. Đổi "Đơn tối thiểu" từ 300000 → 500000
4. Lưu

### Thêm quy tắc mới:
1. Click **Thêm quy tắc**
2. Điền thông tin
3. Đặt Priority cao hơn các quy tắc hiện tại nếu muốn ưu tiên
4. Lưu

### Tắt quy tắc tạm thời:
1. Click **Sửa**
2. Đổi "Trạng thái" → **Tắt**
3. Lưu

---

**Hoàn tất!** Hệ thống bây giờ đã tự động tính phí ship theo địa chỉ khách hàng. 🎉
