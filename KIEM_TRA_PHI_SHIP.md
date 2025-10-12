# Kiểm tra và sửa lỗi phí vận chuyển

## Vấn đề: Phí ship vẫn hiển thị 30.000₫

### Các nguyên nhân có thể:

#### 1. Chưa có dữ liệu trong bảng shipping_fees
**Kiểm tra:**
```bash
php artisan tinker
```

Sau đó chạy:
```php
\App\Models\ShippingFee::count()
```

Nếu kết quả = 0 → **Chưa có dữ liệu**

**Giải pháp:**
```bash
php artisan db:seed --class=ShippingFeeSeeder
```

#### 2. Địa chỉ không khớp với danh sách khu vực
**Ví dụ địa chỉ:** "259, Phường Đạo Thạnh, Đồng Tháp"

Hệ thống sẽ tìm từ khóa "đồng tháp" → Khu vực **lân cận**

**Kiểm tra trong log:**
```bash
tail -f storage/logs/laravel.log
```

Tìm dòng:
```
Shipping Fee Calculation
address: 259, Phường Đạo Thạnh, Đồng Tháp
area_type: nearby
subtotal: 825000
```

#### 3. Quy tắc không phù hợp
Với đơn 825.000₫, khu vực lân cận:
- Quy tắc 1: "Hỗ trợ ship 30k - Đơn từ 500k" (Priority 80)
  - ✅ Khu vực: nearby
  - ✅ Đơn: 825k >= 500k
  - ✅ Khoảng cách: 5km (10-30km)
  - **Phí: 20k + 5×1k = 25.000₫**

Nếu vẫn hiển thị 30k → Quy tắc không được kích hoạt

**Kiểm tra:**
```bash
php artisan tinker
```

```php
$rule = \App\Models\ShippingFee::where('name', 'Hỗ trợ ship 30k - Đơn từ 500k lân cận')->first();
echo "Status: " . $rule->status . "\n";
echo "Priority: " . $rule->priority . "\n";
```

### Các bước khắc phục:

#### Bước 1: Chạy seeder
```bash
php artisan db:seed --class=ShippingFeeSeeder
```

#### Bước 2: Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

#### Bước 3: Test lại
1. Vào trang checkout
2. Nhập địa chỉ: "Đồng Tháp" hoặc "Cần Thơ"
3. Xem phí ship

**Kết quả mong đợi:**
- Đơn 825k, Đồng Tháp → **25.000₫** (20k + 5km)
- Đơn 825k, TP Vĩnh Long → **MIỄN PHÍ** (>300k)
- Đơn 825k, TP.HCM → **50.000₫** (toàn quốc)

#### Bước 4: Kiểm tra log
```bash
tail -20 storage/logs/laravel.log
```

Tìm dòng "Shipping Fee Calculation" để xem:
- Địa chỉ đã nhận
- Khu vực đã phát hiện
- Tổng tiền đơn hàng

### Debug nhanh:

Thêm code này vào `CheckoutController::payment()` sau dòng tính phí:

```php
// Sau dòng: $shippingFee = $this->calculateShippingFee(...);
dd([
    'address' => $customerAddress,
    'area_type' => $areaType,
    'subtotal' => $subtotal,
    'shipping_fee' => $shippingFee,
    'rules_count' => \App\Models\ShippingFee::where('status', true)->count()
]);
```

Refresh trang → Sẽ thấy thông tin debug

### Nếu vẫn lỗi:

Kiểm tra xem có lỗi trong code không:

```bash
php artisan tinker
```

```php
$address = "Đồng Tháp";
$controller = new \App\Http\Controllers\CheckoutController();
$reflection = new ReflectionClass($controller);
$method = $reflection->getMethod('detectAreaType');
$method->setAccessible(true);
$result = $method->invoke($controller, $address);
echo "Area type: " . $result; // Phải là "nearby"
```

---

**Lưu ý:** Nếu sau khi chạy seeder mà vẫn hiển thị 30k, có thể do:
1. Bảng shipping_fees trống
2. Tất cả quy tắc đều bị tắt (status = 0)
3. Không có quy tắc nào phù hợp với điều kiện

Trong trường hợp đó, hệ thống sẽ trả về phí mặc định 30.000₫.
