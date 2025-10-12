# Logic Phí Vận Chuyển - Hoàn Chỉnh

## Flow Checkout (Luồng Thanh Toán)

### Bước 1: Chọn sản phẩm từ giỏ hàng
**Route:** `/checkout`  
**Method:** `CheckoutController@index`

- Lấy sản phẩm từ giỏ hàng
- Tính tổng tiền sản phẩm
- Hiển thị form nhập địa chỉ

### Bước 2: Lưu địa chỉ giao hàng
**Route:** `/checkout/save-address`  
**Method:** `CheckoutController@saveAddress`

**Dữ liệu lưu vào session:**
```php
'checkout_address' => [
    'customer_name' => 'Nhựt Khắc',
    'customer_email' => 'khacnhat2004@gmail.com',
    'customer_phone' => '0967523456',
    'customer_address' => '259, Phường Đạo Thạnh, Đồng Tháp',
    'province_name' => 'Đồng Tháp',
    'ward_name' => 'Phường Đạo Thạnh'
]
```

### Bước 3: Trang thanh toán
**Route:** `/checkout/payment`  
**Method:** `CheckoutController@payment`

#### 3.1. Lấy địa chỉ từ session
```php
$addressData = session('checkout_address');
$customerAddress = $addressData['customer_address'];
```

#### 3.2. Xác định khu vực
```php
$areaType = $this->detectAreaType($customerAddress);
```

**Logic phát hiện:**
- Tìm từ khóa trong địa chỉ (chuyển về chữ thường)
- **Nội thành (local):** "phường 1-10", "tp vĩnh long", "vĩnh long"
- **Lân cận (nearby):** "đồng tháp", "cần thơ", "tiền giang", "an giang", "hậu giang", "sóc trăng", "bến tre", "trà vinh"
- **Toàn quốc (nationwide):** "tp.hcm", "hà nội", "đà nẵng", và tất cả tỉnh/thành khác

**Ví dụ:**
- "259, Phường Đạo Thạnh, **Đồng Tháp**" → `nearby`
- "123 **Phường 1**, TP Vĩnh Long" → `local`
- "456 Quận 1, **TP.HCM**" → `nationwide`

#### 3.3. Tính phí vận chuyển
```php
$shippingFee = $this->calculateShippingFee($subtotal, 5, $areaType);
```

**Logic tính phí:**

1. Lấy tất cả quy tắc đang kích hoạt (status = true)
2. Sắp xếp theo priority (cao → thấp)
3. Duyệt từng quy tắc:

**Quy tắc 1: Nội thành Vĩnh Long** (Priority 100)
- ✅ Áp dụng khi: `areaType = 'local'` VÀ `subtotal >= 300.000₫`
- 💰 Phí: **10.000₫** (tối đa 20k)

**Quy tắc 2: Các khu vực khác** (Priority 90)
- ✅ Áp dụng khi: `areaType = 'nearby'` HOẶC `'nationwide'` VÀ `subtotal >= 500.000₫`
- 💰 Phí: **20.000₫** (tối đa 30k)

**Mặc định:** Nếu không có quy tắc nào phù hợp → **30.000₫**

## Ma trận phí vận chuyển

| Địa chỉ | Khu vực | Đơn 250k | Đơn 350k | Đơn 600k |
|---------|---------|----------|----------|----------|
| TP Vĩnh Long | local | 30k | **10k** ✅ | **10k** ✅ |
| Đồng Tháp | nearby | 30k | 30k | **20k** ✅ |
| Cần Thơ | nearby | 30k | 30k | **20k** ✅ |
| TP.HCM | nationwide | 30k | 30k | **20k** ✅ |
| Hà Nội | nationwide | 30k | 30k | **20k** ✅ |

## Code Logic Chi Tiết

### 1. Phát hiện khu vực (detectAreaType)

```php
private function detectAreaType($address)
{
    $address = mb_strtolower($address, 'UTF-8');
    
    // Kiểm tra nội thành (ưu tiên cao nhất)
    if (strpos($address, 'vĩnh long') !== false) {
        return 'local';
    }
    
    // Kiểm tra lân cận
    if (strpos($address, 'đồng tháp') !== false) {
        return 'nearby';
    }
    
    // Kiểm tra toàn quốc
    if (strpos($address, 'tp.hcm') !== false) {
        return 'nationwide';
    }
    
    // Mặc định
    return 'nationwide';
}
```

### 2. Tính phí vận chuyển (calculateShippingFee)

```php
private function calculateShippingFee($orderValue, $distance, $areaType)
{
    // Lấy quy tắc theo priority
    $rules = ShippingFee::where('status', true)
        ->orderBy('priority', 'desc')
        ->get();
    
    foreach ($rules as $rule) {
        // Kiểm tra khu vực
        if ($rule->area_type === 'local' && $areaType !== 'local') {
            continue; // Quy tắc local chỉ cho local
        }
        
        if ($rule->area_type === 'nearby' && $areaType === 'local') {
            continue; // Quy tắc nearby không cho local
        }
        
        // Kiểm tra điều kiện
        if ($orderValue >= $rule->min_order_value) {
            return $rule->base_fee; // Trả về phí
        }
    }
    
    return 30000; // Phí mặc định
}
```

## Ví dụ thực tế

### Case 1: Khách ở Vĩnh Long, đơn 350k
```
Địa chỉ: "123 Phường 1, TP Vĩnh Long"
→ detectAreaType() → 'local'
→ Subtotal: 350.000₫

Kiểm tra quy tắc:
1. Quy tắc 1 (Priority 100):
   - area_type = 'local' ✅
   - min_order_value = 300.000₫ ✅
   - 350k >= 300k ✅
   → Phí: 10.000₫ ✅

Kết quả: 10.000₫
```

### Case 2: Khách ở Đồng Tháp, đơn 600k
```
Địa chỉ: "259, Phường Đạo Thạnh, Đồng Tháp"
→ detectAreaType() → 'nearby'
→ Subtotal: 600.000₫

Kiểm tra quy tắc:
1. Quy tắc 1 (Priority 100):
   - area_type = 'local' ❌ (khách ở nearby)
   - Skip

2. Quy tắc 2 (Priority 90):
   - area_type = 'nearby' ✅
   - min_order_value = 500.000₫ ✅
   - 600k >= 500k ✅
   → Phí: 20.000₫ ✅

Kết quả: 20.000₫
```

### Case 3: Khách ở TP.HCM, đơn 400k
```
Địa chỉ: "456 Quận 1, TP.HCM"
→ detectAreaType() → 'nationwide'
→ Subtotal: 400.000₫

Kiểm tra quy tắc:
1. Quy tắc 1 (Priority 100):
   - area_type = 'local' ❌
   - Skip

2. Quy tắc 2 (Priority 90):
   - area_type = 'nearby' ✅ (áp dụng cho cả nationwide)
   - min_order_value = 500.000₫ ❌
   - 400k < 500k ❌
   - Skip

Không có quy tắc nào phù hợp
→ Phí mặc định: 30.000₫

Kết quả: 30.000₫
```

## Lưu ý quan trọng

### 1. Thứ tự ưu tiên
- Priority cao được kiểm tra trước
- Quy tắc đầu tiên phù hợp sẽ được áp dụng
- Không kiểm tra quy tắc tiếp theo

### 2. Khu vực
- **local**: Chỉ áp dụng cho nội thành Vĩnh Long
- **nearby**: Áp dụng cho lân cận VÀ toàn quốc
- **nationwide**: Cũng dùng quy tắc nearby

### 3. Phí mặc định
- Nếu không có quy tắc nào: **30.000₫**
- Nếu bảng trống: **30.000₫**
- Nếu có lỗi: **30.000₫**

### 4. Cập nhật quy tắc
Để thay đổi phí ship, chỉnh sửa trong **Admin → Quản lý phí vận chuyển**:
- Thay đổi giá trị đơn tối thiểu
- Thay đổi phí ship
- Bật/tắt quy tắc
- Thay đổi độ ưu tiên

---

**Tóm tắt:**
- ✅ Logic rõ ràng, dễ hiểu
- ✅ Tự động phát hiện khu vực
- ✅ Linh hoạt quản lý trong admin
- ✅ An toàn với fallback 30k
