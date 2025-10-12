# Hướng dẫn tính phí vận chuyển theo địa chỉ khách hàng

## Hiện trạng:
- ✅ Hệ thống đã có bảng `shipping_fees` với quy tắc phí ship
- ✅ Đã có logic tính phí trong `CheckoutController`
- ❌ **CHƯA** tự động xác định khu vực dựa trên địa chỉ khách hàng
- ❌ **CHƯA** tính khoảng cách thực tế

## Cách hoạt động hiện tại:
```php
// Mặc định: khoảng cách 5km, khu vực nội thành
$shippingFee = $this->calculateShippingFee($subtotal, 5, 'local');
```

## Để tính phí theo địa chỉ, cần:

### Phương án 1: Xác định khu vực theo từ khóa trong địa chỉ (ĐƠN GIẢN)

```php
private function detectAreaType($address)
{
    $address = mb_strtolower($address, 'UTF-8');
    
    // Danh sách phường/xã nội thành Vĩnh Long
    $localAreas = [
        'phường 1', 'phường 2', 'phường 3', 'phường 4', 'phường 5',
        'phường 6', 'phường 7', 'phường 8', 'phường 9',
        'thành phố vĩnh long', 'tp vĩnh long', 'tp. vĩnh long'
    ];
    
    // Danh sách huyện lân cận
    $nearbyAreas = [
        'long hồ', 'mang thít', 'vũng liêm', 'tam bình',
        'trà ôn', 'bình minh', 'bình tân'
    ];
    
    // Kiểm tra nội thành
    foreach ($localAreas as $area) {
        if (strpos($address, $area) !== false) {
            return 'local';
        }
    }
    
    // Kiểm tra lân cận
    foreach ($nearbyAreas as $area) {
        if (strpos($address, $area) !== false) {
            return 'nearby';
        }
    }
    
    // Mặc định: toàn quốc
    return 'nationwide';
}

// Sử dụng:
$addressData = session('checkout_address');
$areaType = $this->detectAreaType($addressData['customer_address']);
$shippingFee = $this->calculateShippingFee($subtotal, 5, $areaType);
```

### Phương án 2: Tích hợp Google Maps API (CHÍNH XÁC)

**Bước 1: Lấy API Key**
- Truy cập: https://console.cloud.google.com/
- Tạo project mới
- Enable: Distance Matrix API
- Tạo API Key

**Bước 2: Thêm vào .env**
```env
GOOGLE_MAPS_API_KEY=your_api_key_here
SHOP_ADDRESS="Vĩnh Long, Việt Nam"
```

**Bước 3: Tạo Service**
```php
// app/Services/GoogleMapsService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class GoogleMapsService
{
    private $apiKey;
    private $shopAddress;

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
        $this->shopAddress = config('services.google_maps.shop_address');
    }

    public function calculateDistance($destination)
    {
        $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
            'origins' => $this->shopAddress,
            'destinations' => $destination,
            'key' => $this->apiKey,
            'language' => 'vi'
        ]);

        $data = $response->json();

        if ($data['status'] === 'OK' && isset($data['rows'][0]['elements'][0])) {
            $element = $data['rows'][0]['elements'][0];
            
            if ($element['status'] === 'OK') {
                // Khoảng cách tính bằng km
                $distanceInMeters = $element['distance']['value'];
                $distanceInKm = $distanceInMeters / 1000;
                
                return $distanceInKm;
            }
        }

        // Nếu không tính được, trả về khoảng cách mặc định
        return 5;
    }
}
```

**Bước 4: Sử dụng trong CheckoutController**
```php
use App\Services\GoogleMapsService;

private function calculateShippingFeeByAddress($orderValue, $address)
{
    // Xác định khu vực
    $areaType = $this->detectAreaType($address);
    
    // Tính khoảng cách (nếu có Google Maps API)
    $distance = 5; // Mặc định
    
    if (config('services.google_maps.api_key')) {
        $mapsService = new GoogleMapsService();
        $distance = $mapsService->calculateDistance($address);
    }
    
    return $this->calculateShippingFee($orderValue, $distance, $areaType);
}
```

### Phương án 3: Cho khách chọn khu vực (ĐƠN GIẢN NHẤT)

**Thêm dropdown trong form checkout:**
```html
<select name="shipping_area" class="form-control" required>
    <option value="local">Nội thành Vĩnh Long (Miễn phí từ 300k)</option>
    <option value="nearby">Lân cận (Hỗ trợ ship từ 500k)</option>
    <option value="nationwide">Toàn quốc</option>
</select>
```

**Xử lý trong Controller:**
```php
$areaType = $request->input('shipping_area', 'local');
$shippingFee = $this->calculateShippingFee($subtotal, 5, $areaType);
```

## Khuyến nghị:

### Giai đoạn 1 (Ngay lập tức):
✅ Sử dụng **Phương án 1** - Xác định theo từ khóa
- Đơn giản, không tốn chi phí
- Chính xác 80-90%
- Dễ bảo trì

### Giai đoạn 2 (Sau này):
✅ Nâng cấp lên **Phương án 2** - Google Maps API
- Chính xác 100%
- Tính khoảng cách thực tế
- Chi phí: ~$5-10/tháng (miễn phí 200 requests/ngày)

## Cập nhật dữ liệu mẫu:

Chạy lệnh để cập nhật quy tắc phí ship:
```bash
php artisan db:seed --class=ShippingFeeSeeder
```

Quy tắc mới:
1. **Miễn phí ship nội thành - Đơn từ 300k** (Priority 100) ✅
2. Hỗ trợ ship 20k - Đơn từ 300k (Priority 90, TẮT)
3. **Phí ship nội thành chuẩn - 30k** (Priority 50) ✅
4. **Hỗ trợ ship lân cận - Đơn từ 500k** (Priority 80) ✅
5. **Phí ship lân cận chuẩn** (Priority 40) ✅
6. **Phí ship toàn quốc - 50k** (Priority 30) ✅

## Ví dụ thực tế:

### Khách ở nội thành Vĩnh Long:
- Đơn 350k → **Miễn phí** ✅
- Đơn 250k → **30k** ✅

### Khách ở Long Hồ (lân cận):
- Đơn 600k, 15km → **20k + 15×1k = 35k** (max 50k) ✅
- Đơn 400k, 15km → **40k + 15×2k = 70k** ✅

### Khách ở TP.HCM (toàn quốc):
- Đơn 150k → **50k** ✅

---

**Lưu ý**: Hiện tại hệ thống dùng khoảng cách mặc định 5km và khu vực 'local'. 
Để tính chính xác, cần implement một trong 3 phương án trên.
