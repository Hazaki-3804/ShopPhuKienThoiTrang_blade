<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\ShippingFee;
use App\Models\Payment;
use App\Services\MoMoService;
use App\Services\PayosService;
use App\Mail\OrderConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    private function currentCart(): ?Cart
    {
        if (!Auth::check()) return null;
        $userId = Auth::id();
        return Cart::firstOrCreate(['user_id' => $userId], ['user_id' => $userId]);
    }

    /**
     * Xác định khu vực dựa trên địa chỉ
     */
    private function detectAreaType($address)
    {
        $address = mb_strtolower($address, 'UTF-8');
        
        // ========== NỘI THÀNH VĨNH LONG (0-10km) ==========
        $localAreas = [
            // Các phường trong TP Vĩnh Long
            'phường 1', 'phường 2', 'phường 3', 'phường 4', 'phường 5',
            'phường 6', 'phường 7', 'phường 8', 'phường 9', 'phường 10',
            'phường trường an', 'phuong truong an',
            'phường tân hòa', 'phuong tan hoa',
            'phường tân hội', 'phuong tan hoi',
            'phường tân ngãi', 'phuong tan ngai',
            // Tên thành phố
            'thành phố vĩnh long', 'thanh pho vinh long',
            'tp vĩnh long', 'tp vinh long',
            'tp. vĩnh long', 'tp. vinh long',
            'vĩnh long', 'vinh long'
        ];
        
        // ========== LÂN CẬN (10-50km) ==========
        // Bao gồm: Các huyện trong tỉnh Vĩnh Long + Đồng bằng sông Cửu Long lân cận
        $nearbyAreas = [
            // === Các huyện trong tỉnh Vĩnh Long ===
            'long hồ', 'long ho',
            'mang thít', 'mang thit',
            'vũng liêm', 'vung liem',
            'tam bình', 'tam binh',
            'trà ôn', 'tra on',
            'bình minh', 'binh minh',
            'bình tán', 'binh tan',
            
            // === Đồng Tháp (lân cận) ===
            'đồng tháp', 'dong thap',
            'cao lãnh', 'cao lanh',
            'sa đéc', 'sa dec',
            'hồng ngự', 'hong ngu',
            'tân hồng', 'tan hong',
            'lấp vò', 'lap vo',
            'lai vung',
            'châu thành', 'chau thanh',
            'tam nông', 'tam nong',
            'tháp mười', 'thap muoi',
            'thanh bình', 'thanh binh',
            
            // === Cần Thơ (lân cận) ===
            'cần thơ', 'can tho',
            'ninh kiều', 'ninh kieu',
            'bình thủy', 'binh thuy',
            'cái răng', 'cai rang',
            'ô môn', 'o mon',
            'thốt nốt', 'thot not',
            'phong điền', 'phong dien',
            'cờ đỏ', 'co do',
            'thới lai', 'thoi lai',
            'vĩnh thạnh', 'vinh thanh',
            
            // === Tiền Giang (lân cận) ===
            'tiền giang', 'tien giang',
            'mỹ tho', 'my tho',
            'gò công', 'go cong',
            'cai lậy', 'cai lay',
            'tân phước', 'tan phuoc',
            'cái bè', 'cai be',
            'châu thành tg', 'chau thanh tg',
            
            // === An Giang (lân cận) ===
            'an giang',
            'long xuyên', 'long xuyen',
            'châu đốc', 'chau doc',
            'tân châu', 'tan chau',
            'phú tân', 'phu tan',
            'an phú', 'an phu',
            'tịnh biên', 'tinh bien',
            'tri tôn', 'tri ton',
            'châu phú', 'chau phu',
            'chợ mới', 'cho moi',
            'thoại sơn', 'thoai son',
            
            // === Hậu Giang (lân cận) ===
            'hậu giang', 'hau giang',
            'vị thanh', 'vi thanh',
            'ngã bảy', 'nga bay',
            'châu thành a', 'chau thanh a',
            'châu thành hg', 'chau thanh hg',
            'phụng hiệp', 'phung hiep',
            'vị thủy', 'vi thuy',
            'long mỹ', 'long my',
            
            // === Sóc Trăng (lân cận) ===
            'sóc trăng', 'soc trang',
            'ngã năm', 'nga nam',
            'kế sách', 'ke sach',
            'mỹ tú', 'my tu',
            'thạnh trị', 'thanh tri',
            'mỹ xuyên', 'my xuyen',
            'long phú', 'long phu',
            
            // === Bến Tre (lân cận) ===
            'bến tre', 'ben tre',
            'ba tri',
            'bình đại', 'binh dai',
            'giồng trôm', 'giong trom',
            'châu thành bt', 'chau thanh bt',
            'chợ lách', 'cho lach',
            'mỏ cày', 'mo cay',
            'thạnh phú', 'thanh phu',
            
            // === Trà Vinh (lân cận) ===
            'trà vinh', 'tra vinh',
            'duyên hải', 'duyen hai',
            'càng long', 'cang long',
            'cầu kè', 'cau ke',
            'tiểu cần', 'tieu can',
            'châu thành tv', 'chau thanh tv',
            'cầu ngang', 'cau ngang',
            'trà cú', 'tra cu'
        ];
        
        // ========== TOÀN QUỐC (>50km) ==========
        // Tất cả các tỉnh/thành phố khác
        $nationwideAreas = [
            // === Miền Nam ===
            'hồ chí minh', 'ho chi minh', 'sài gòn', 'saigon', 'tp.hcm', 'tphcm',
            'quận 1', 'quận 2', 'quận 3', 'quận 4', 'quận 5', 'quận 6', 'quận 7', 
            'quận 8', 'quận 9', 'quận 10', 'quận 11', 'quận 12',
            'thủ đức', 'thu duc', 'bình thạnh', 'binh thanh', 'phú nhuận', 'phu nhuan',
            'tân bình', 'tan binh', 'tân phú', 'tan phu', 'gò vấp', 'go vap',
            'bình tân hcm', 'binh tan hcm', 'bình chánh', 'binh chanh', 'hóc môn', 'hoc mon',
            'củ chi', 'cu chi', 'nhà bè', 'nha be', 'cần giờ', 'can gio',
            
            'đồng nai', 'dong nai', 'biên hòa', 'bien hoa', 'long thành', 'long thanh',
            'bình dương', 'binh duong', 'thủ dầu một', 'thu dau mot', 'dĩ an', 'di an', 'thuận an', 'thuan an',
            'bà rịa vũng tàu', 'ba ria vung tau', 'vũng tàu', 'vung tau', 'bà rịa', 'ba ria',
            'tây ninh', 'tay ninh',
            'bình phước', 'binh phuoc', 'đồng xoài', 'dong xoai',
            'long an', 'tân an', 'tan an', 'bến lức', 'ben luc',
            'kiên giang', 'kien giang', 'rạch giá', 'rach gia', 'phú quốc', 'phu quoc', 'hà tiên', 'ha tien',
            'cà mau', 'ca mau', 'năm căn', 'nam can',
            'bạc liêu', 'bac lieu',
            
            // === Miền Trung ===
            'đà nẵng', 'da nang',
            'quảng nam', 'quang nam', 'hội an', 'hoi an', 'tam kỳ', 'tam ky',
            'quảng ngãi', 'quang ngai',
            'bình định', 'binh dinh', 'quy nhơn', 'quy nhon',
            'phú yên', 'phu yen', 'tuy hòa', 'tuy hoa',
            'khánh hòa', 'khanh hoa', 'nha trang',
            'ninh thuận', 'ninh thuan', 'phan rang',
            'bình thuận', 'binh thuan', 'phan thiết', 'phan thiet', 'mũi né', 'mui ne',
            'lâm đồng', 'lam dong', 'đà lạt', 'da lat', 'bảo lộc', 'bao loc',
            'đắk lắk', 'dak lak', 'buôn ma thuột', 'buon ma thuot',
            'đắk nông', 'dak nong', 'gia nghĩa', 'gia nghia',
            'gia lai', 'pleiku',
            'kon tum',
            'quảng trị', 'quang tri', 'đông hà', 'dong ha',
            'thừa thiên huế', 'thua thien hue', 'huế', 'hue',
            'quảng bình', 'quang binh', 'đồng hới', 'dong hoi',
            'hà tĩnh', 'ha tinh',
            'nghệ an', 'nghe an', 'vinh',
            'thanh hóa', 'thanh hoa',
            
            // === Miền Bắc ===
            'hà nội', 'ha noi', 'hanoi',
            'hoàn kiếm', 'hoan kiem', 'ba đình', 'ba dinh', 'đống đa', 'dong da',
            'hai bà trưng', 'hai ba trung', 'hoàng mai', 'hoang mai', 'thanh xuân', 'thanh xuan',
            'long biên', 'long bien', 'tây hồ', 'tay ho', 'cầu giấy', 'cau giay',
            'hà đông', 'ha dong', 'nam từ liêm', 'nam tu liem', 'bắc từ liêm', 'bac tu liem',
            
            'hải phòng', 'hai phong',
            'quảng ninh', 'quang ninh', 'hạ long', 'ha long', 'cẩm phả', 'cam pha',
            'hải dương', 'hai duong',
            'hưng yên', 'hung yen',
            'bắc ninh', 'bac ninh',
            'bắc giang', 'bac giang',
            'thái nguyên', 'thai nguyen',
            'lạng sơn', 'lang son',
            'cao bằng', 'cao bang',
            'hà giang', 'ha giang',
            'lào cai', 'lao cai', 'sapa', 'sa pa',
            'yên bái', 'yen bai',
            'tuyên quang', 'tuyen quang',
            'phú thọ', 'phu tho', 'việt trì', 'viet tri',
            'vĩnh phúc', 'vinh phuc',
            'bắc kạn', 'bac kan',
            'thái bình', 'thai binh',
            'hà nam', 'ha nam',
            'nam định', 'nam dinh',
            'ninh bình', 'ninh binh',
            'hòa bình', 'hoa binh',
            'sơn la', 'son la',
            'điện biên', 'dien bien',
            'lai châu', 'lai chau'
        ];
        
        // Kiểm tra nội thành (ưu tiên cao nhất)
        foreach ($localAreas as $area) {
            if (strpos($address, $area) !== false) {
                return 'local';
            }
        }
        // Kiểm tra lân cận (ưu tiên thứ 2)
        foreach ($nearbyAreas as $area) {
            if (strpos($address, $area) !== false) {
                return 'nearby';
            }
        }
        // Kiểm tra toàn quốc (ưu tiên thứ 3)
        foreach ($nationwideAreas as $area) {
            if (strpos($address, $area) !== false) {
                return 'nationwide';
            }
        }
        // Mặc định: toàn quốc (nếu không khớp gì)
        return 'nationwide';
    }

    /**
     * Tính phí vận chuyển dựa trên quy tắc trong database
     * Mặc định: khoảng cách 5km, khu vực nội thành
     */
    private function calculateShippingFee($orderValue, $distance = 5, $areaType = 'local')
    {
        try {
            // Kiểm tra xem bảng shipping_fees có tồn tại không
            if (!DB::getSchemaBuilder()->hasTable('shipping_fees')) {
                return 0; // Trả về 0 nếu bảng chưa tồn tại
            }

            // Lấy các quy tắc phí ship đang hoạt động, sắp xếp theo độ ưu tiên
            $shippingRules = ShippingFee::where('status', true)
                ->orderBy('priority', 'desc')
                ->get();

            // Nếu không có quy tắc nào, trả về 0
            if ($shippingRules->isEmpty()) {
                return 0;
            }

            // Tìm quy tắc phù hợp với 2 vòng lặp: vòng 1 tìm khớp chính xác, vòng 2 tìm fallback
            Log::info('=== BẮT ĐẦU TÌM QUY TẮC PHÍ SHIP ===', [
                'total_rules' => $shippingRules->count(),
                'input_distance' => $distance,
                'input_order_value' => $orderValue,
                'input_area_type' => $areaType
            ]);
            
            // VÒNG 1: Tìm quy tắc khớp chính xác với khu vực
            foreach ($shippingRules as $rule) {
                Log::info("Vòng 1 - Kiểm tra quy tắc: {$rule->name}", [
                    'rule_area_type' => $rule->area_type,
                    'rule_min_distance' => $rule->min_distance,
                    'rule_max_distance' => $rule->max_distance,
                    'rule_min_order_value' => $rule->min_order_value
                ]);
                
                // Chỉ kiểm tra quy tắc khớp chính xác với khu vực
                if ($rule->area_type !== $areaType) {
                    Log::info("  ⏭ Bỏ qua (không khớp chính xác khu vực)");
                    continue;
                }
                
                Log::info("  ✓ Khu vực khớp chính xác");

                // Kiểm tra xem quy tắc có áp dụng được không
                if ($rule->isApplicable($distance, $orderValue)) {
                    $calculatedFee = $rule->calculateFee($distance, $orderValue);
                    
                    Log::info('✅ ÁP DỤNG QUY TẮC (Khớp chính xác)', [
                        'rule_name' => $rule->name,
                        'calculated_fee' => $calculatedFee
                    ]);
                    
                    return $calculatedFee;
                } else {
                    Log::info("  ✗ Quy tắc không áp dụng được (kiểm tra isApplicable)");
                }
            }
            
            // VÒNG 2: Nếu không tìm thấy, tìm quy tắc fallback (nearby cho nationwide, hoặc bất kỳ quy tắc nào)
            Log::info('Vòng 1 không tìm thấy, bắt đầu vòng 2 (fallback)');
            
            foreach ($shippingRules as $rule) {
                Log::info("Vòng 2 - Kiểm tra quy tắc: {$rule->name}", [
                    'rule_area_type' => $rule->area_type
                ]);
                
                // Cho phép quy tắc nearby áp dụng cho nationwide
                // Cho phép quy tắc nationwide áp dụng cho mọi khu vực
                // Cho phép quy tắc local áp dụng nếu không có gì khác (last resort)
                $canApply = false;
                
                if ($rule->area_type === 'nationwide') {
                    $canApply = true;
                    Log::info("  ✓ Quy tắc nationwide (áp dụng cho mọi khu vực)");
                } elseif ($rule->area_type === 'nearby') {
                    // Quy tắc nearby có thể áp dụng cho nearby, nationwide, và local (fallback)
                    $canApply = true;
                    Log::info("  ✓ Quy tắc nearby (fallback cho {$areaType})");
                } elseif ($rule->area_type === 'local' && in_array($areaType, ['nearby', 'nationwide'])) {
                    // Quy tắc local có thể áp dụng cho nearby và nationwide (last resort)
                    $canApply = true;
                    Log::info("  ⚠ Quy tắc local (last resort cho {$areaType})");
                }
                
                if (!$canApply) {
                    Log::info("  ⏭ Bỏ qua");
                    continue;
                }

                // Kiểm tra xem quy tắc có áp dụng được không
                // Trong vòng fallback, bỏ qua kiểm tra đơn hàng tối thiểu
                if ($rule->isApplicable($distance, $orderValue, true)) {
                    $calculatedFee = $rule->calculateFee($distance, $orderValue);
                    
                    Log::info('✅ ÁP DỤNG QUY TẮC (Fallback - bỏ qua min order)', [
                        'rule_name' => $rule->name,
                        'calculated_fee' => $calculatedFee,
                        'note' => 'Đã bỏ qua kiểm tra đơn hàng tối thiểu'
                    ]);
                    
                    return $calculatedFee;
                } else {
                    Log::info("  ✗ Quy tắc không áp dụng được (khoảng cách không khớp)");
                }
            }

            // Nếu vẫn không có quy tắc nào, sử dụng phí mặc định
            Log::warning('❌ KHÔNG TÌM THẤY QUY TẮC PHÙ HỢP, dùng phí mặc định');
            
            // Phí mặc định: 15.000₫ + 2.000₫/km (giống quy tắc nearby)
            $defaultFee = 15000 + ($distance * 2000);
            Log::info('Sử dụng phí mặc định', ['fee' => $defaultFee]);
            
            return $defaultFee;
        } catch (\Exception $e) {
            // Nếu có lỗi, log và trả về 0
            Log::error('Calculate shipping fee error: ' . $e->getMessage());
            return 0;
        }
    }

    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        
        // Kiểm tra nếu là "Mua ngay" từ session
        $buyNowItem = session('buy_now_item');
        if ($request->query('buy_now') == 1 && $buyNowItem) {
            $product = Product::with('product_images')
                ->where('status', 1)
                ->find($buyNowItem['product_id']);
            
            if (!$product) {
                session()->forget('buy_now_item');
                return redirect()->route('shop.index')->withErrors(['product' => 'Sản phẩm không tồn tại']);
            }
            
            if ($product->stock < $buyNowItem['qty']) {
                session()->forget('buy_now_item');
                return redirect()->back()->withErrors(['qty' => 'Số lượng vượt quá tồn kho']);
            }
            
            $price = (int) $product->price;
            $qty = (int) $buyNowItem['qty'];
            
            $items = collect([[
                'product' => $product,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ]]);
            
            $total = (int) $items->sum('subtotal');
            
            return view('checkout.index', [
                'items' => $items,
                'total' => $total,
                'selected' => [],
                'is_buy_now' => true,
            ]);
        }
        
        // Logic cũ cho checkout từ giỏ hàng
        $cart = $this->currentCart();
        if (!$cart) return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)$request->query('selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product.product_images'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter();

        if ($items->isEmpty()) {
            // Nếu có truyền selected mà rỗng/không hợp lệ thì quay lại giỏ
            if ($selected->isNotEmpty()) {
                return redirect()->route('cart.index')->withErrors(['cart' => 'Vui lòng chọn sản phẩm hợp lệ để thanh toán']);
            }
            return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);
        }

        $total = (int) $items->sum('subtotal');
        return view('checkout.index', [
            'items' => $items,
            'total' => $total,
            'selected' => $selected->all(),
            'is_buy_now' => false,
        ]);
    }

    public function saveAddress(Request $request)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'customer_address' => ['required', 'string', 'max:500'],
            'province_name' => ['nullable', 'string', 'max:100'],
            'ward_name' => ['nullable', 'string', 'max:100'],
        ]);
        // Lưu thông tin vào session
        session([
            'checkout_address' => $data,
            'checkout_selected' => $request->input('selected', [])
        ]);
        // dd(session('checkout_address'));

        // If AJAX request, return JSON response
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            // Get coordinates for the new address
            $coordinates = $this->getAccurateCustomerCoordinates($data['customer_address']);
            
            return response()->json([
                'success' => true,
                'message' => 'Địa chỉ đã được cập nhật thành công',
                'data' => $data,
                'coordinates' => $coordinates
            ]);
        }

        return redirect()->route('checkout.payment');
    }

    /**
     * Lấy tọa độ chính xác của khách hàng để truyền cho frontend
     * Sử dụng cùng logic với calculateDistance để đảm bảo nhất quán
     */
    private function getAccurateCustomerCoordinates($address)
    {
        try {
            // Ưu tiên tọa độ tỉnh/thành phố nếu địa chỉ thiếu chi tiết
            $provinceCoords = $this->getProvinceCoordinates($address);
            if ($provinceCoords && $this->shouldUseProvinceCoordinates($address)) {
                Log::info('Frontend: Using province coordinates', [
                    'address' => $address,
                    'coords' => $provinceCoords
                ]);
                return $provinceCoords;
            }
            
            // Chuẩn hóa địa chỉ
            $searchAddress = $this->normalizeVietnameseAddress($address);
            
            // Geocoding với Nominatim
            $geocodeUrl = 'https://nominatim.openstreetmap.org/search';
            $geocodeParams = [
                'format' => 'json',
                'q' => $searchAddress,
                'limit' => 10,
                'countrycodes' => 'vn',
                'accept-language' => 'vi',
                'addressdetails' => 1,
                'bounded' => 1,
                'viewbox' => '102.14,8.18,109.46,23.39'
            ];
            
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: ShopNangTho/1.0\r\nAccept-Language: vi\r\n",
                    'timeout' => 5
                ]
            ]);
            
            $geocodeResponse = @file_get_contents($geocodeUrl . '?' . http_build_query($geocodeParams), false, $context);
            
            if ($geocodeResponse !== false) {
                $geocodeData = json_decode($geocodeResponse, true);
                
                if (!empty($geocodeData)) {
                    $bestResult = $this->selectBestGeocodingResult($geocodeData, $address);
                    
                    if ($bestResult) {
                        $lat = floatval($bestResult['lat']);
                        $lng = floatval($bestResult['lon']);
                        
                        // Validate
                        if ($this->isValidVietnamCoordinates($lat, $lng, $address)) {
                            return ['lat' => $lat, 'lng' => $lng];
                        }
                    }
                }
            }
            
            // Fallback: Dùng tọa độ tỉnh/thành phố
            if ($provinceCoords) {
                Log::info('Frontend: Fallback to province coordinates', [
                    'address' => $address,
                    'coords' => $provinceCoords
                ]);
                return $provinceCoords;
            }
            
            // Fallback cuối cùng: Trả về null để frontend tự geocoding
            return null;
            
        } catch (\Exception $e) {
            Log::error('Get customer coordinates error: ' . $e->getMessage());
            
            // Fallback: Dùng tọa độ tỉnh/thành phố
            $provinceCoords = $this->getProvinceCoordinates($address);
            return $provinceCoords;
        }
    }
    
    /**
     * Kiểm tra xem có nên ưu tiên dùng tọa độ tỉnh/thành phố không
     * Nếu địa chỉ chỉ có phường/xã mà không có số nhà, đường cụ thể
     */
    private function shouldUseProvinceCoordinates($address)
    {
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Nếu địa chỉ chỉ có: "Phường X, Tỉnh Y" hoặc "Xã X, Tỉnh Y"
        // Mà không có số nhà, tên đường cụ thể
        // Thì nên dùng tọa độ tỉnh/thành phố
        
        // Kiểm tra có số nhà không
        $hasStreetNumber = preg_match('/^\d+/', $address);
        
        // Kiểm tra có tên đường không (đường, phố, etc.)
        $hasStreetName = preg_match('/(đường|phố|street|road|avenue)/i', $addressLower);
        
        // Nếu không có số nhà VÀ không có tên đường
        // Thì địa chỉ quá chung chung, nên dùng tọa độ tỉnh
        if (!$hasStreetNumber && !$hasStreetName) {
            Log::info('Address lacks street details, should use province coordinates', [
                'address' => $address
            ]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Validate tọa độ có nằm trong vùng hợp lý của Vietnam không
     * Và kiểm tra xem có khớp với tỉnh/thành phố trong địa chỉ không
     */
    private function isValidVietnamCoordinates($lat, $lng, $address)
    {
        // Bounding box Vietnam: 8.18°N - 23.39°N, 102.14°E - 109.46°E
        if ($lat < 8.18 || $lat > 23.39 || $lng < 102.14 || $lng > 109.46) {
            Log::warning('Coordinates outside Vietnam bounding box', [
                'lat' => $lat,
                'lng' => $lng
            ]);
            return false;
        }
        
        // Lấy tọa độ tỉnh/thành phố từ địa chỉ
        $provinceCoords = $this->getProvinceCoordinates($address);
        
        if ($provinceCoords) {
            // Tính khoảng cách giữa geocoded coordinates và tọa độ tỉnh
            $distance = $this->calculateHaversineDistance(
                $lat, $lng,
                $provinceCoords['lat'], $provinceCoords['lng']
            );
            
            // Nếu khoảng cách > 100km, có thể là sai
            // Ví dụ: Địa chỉ Tuyên Quang nhưng geocode ra Trà Vinh
            if ($distance > 100) {
                Log::warning('Geocoded coordinates too far from province center', [
                    'distance_km' => $distance,
                    'geocoded' => ['lat' => $lat, 'lng' => $lng],
                    'province' => $provinceCoords
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Lấy tọa độ chính xác của tỉnh/thành phố Việt Nam
     * Dữ liệu cập nhật theo bản đồ hành chính mới nhất
     */
    private function getProvinceCoordinates($address)
    {
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Tọa độ trung tâm các tỉnh/thành phố Việt Nam (cập nhật 2024)
        $coordinates = [
            // Miền Bắc
            'hà nội' => ['lat' => 21.0285, 'lng' => 105.8542],
            'ha noi' => ['lat' => 21.0285, 'lng' => 105.8542],
            'hải phòng' => ['lat' => 20.8449, 'lng' => 106.6881],
            'hai phong' => ['lat' => 20.8449, 'lng' => 106.6881],
            'quảng ninh' => ['lat' => 21.0064, 'lng' => 107.2925],
            'quang ninh' => ['lat' => 21.0064, 'lng' => 107.2925],
            'bắc ninh' => ['lat' => 21.1861, 'lng' => 106.0763],
            'bac ninh' => ['lat' => 21.1861, 'lng' => 106.0763],
            'hải dương' => ['lat' => 20.9373, 'lng' => 106.3145],
            'hai duong' => ['lat' => 20.9373, 'lng' => 106.3145],
            'hưng yên' => ['lat' => 20.6464, 'lng' => 106.0511],
            'hung yen' => ['lat' => 20.6464, 'lng' => 106.0511],
            'thái bình' => ['lat' => 20.4464, 'lng' => 106.3365],
            'thai binh' => ['lat' => 20.4464, 'lng' => 106.3365],
            'nam định' => ['lat' => 20.4388, 'lng' => 106.1621],
            'nam dinh' => ['lat' => 20.4388, 'lng' => 106.1621],
            'ninh bình' => ['lat' => 20.2506, 'lng' => 105.9745],
            'ninh binh' => ['lat' => 20.2506, 'lng' => 105.9745],
            
            // Miền Trung
            'thanh hóa' => ['lat' => 19.8067, 'lng' => 105.7851],
            'thanh hoa' => ['lat' => 19.8067, 'lng' => 105.7851],
            'nghệ an' => ['lat' => 18.6792, 'lng' => 105.6819],
            'nghe an' => ['lat' => 18.6792, 'lng' => 105.6819],
            'hà tĩnh' => ['lat' => 18.3559, 'lng' => 105.9058],
            'ha tinh' => ['lat' => 18.3559, 'lng' => 105.9058],
            'quảng bình' => ['lat' => 17.4676, 'lng' => 106.6222],
            'quang binh' => ['lat' => 17.4676, 'lng' => 106.6222],
            'quảng trị' => ['lat' => 16.7943, 'lng' => 107.1856],
            'quang tri' => ['lat' => 16.7943, 'lng' => 107.1856],
            'thừa thiên huế' => ['lat' => 16.4637, 'lng' => 107.5909],
            'thua thien hue' => ['lat' => 16.4637, 'lng' => 107.5909],
            'huế' => ['lat' => 16.4637, 'lng' => 107.5909],
            'hue' => ['lat' => 16.4637, 'lng' => 107.5909],
            'đà nẵng' => ['lat' => 16.0544, 'lng' => 108.2022],
            'da nang' => ['lat' => 16.0544, 'lng' => 108.2022],
            'quảng nam' => ['lat' => 15.5394, 'lng' => 108.0191],
            'quang nam' => ['lat' => 15.5394, 'lng' => 108.0191],
            'quảng ngãi' => ['lat' => 15.1214, 'lng' => 108.8044],
            'quang ngai' => ['lat' => 15.1214, 'lng' => 108.8044],
            'bình định' => ['lat' => 13.7830, 'lng' => 109.2196],
            'binh dinh' => ['lat' => 13.7830, 'lng' => 109.2196],
            'phú yên' => ['lat' => 13.0882, 'lng' => 109.0929],
            'phu yen' => ['lat' => 13.0882, 'lng' => 109.0929],
            'khánh hòa' => ['lat' => 12.2585, 'lng' => 109.0526],
            'khanh hoa' => ['lat' => 12.2585, 'lng' => 109.0526],
            'nha trang' => ['lat' => 12.2388, 'lng' => 109.1967],
            
            // Tây Nguyên
            'gia lai' => ['lat' => 13.9830, 'lng' => 108.0000],
            'kon tum' => ['lat' => 14.3497, 'lng' => 108.0005],
            'đắk lắk' => ['lat' => 12.7100, 'lng' => 108.2378],
            'dak lak' => ['lat' => 12.7100, 'lng' => 108.2378],
            'đắk nông' => ['lat' => 12.2646, 'lng' => 107.6098],
            'dak nong' => ['lat' => 12.2646, 'lng' => 107.6098],
            'lâm đồng' => ['lat' => 11.5753, 'lng' => 108.1429],
            'lam dong' => ['lat' => 11.5753, 'lng' => 108.1429],
            'đà lạt' => ['lat' => 11.9404, 'lng' => 108.4583],
            'da lat' => ['lat' => 11.9404, 'lng' => 108.4583],
            
            // Đông Nam Bộ
            'hồ chí minh' => ['lat' => 10.8231, 'lng' => 106.6297],
            'ho chi minh' => ['lat' => 10.8231, 'lng' => 106.6297],
            'sài gòn' => ['lat' => 10.8231, 'lng' => 106.6297],
            'saigon' => ['lat' => 10.8231, 'lng' => 106.6297],
            'bình dương' => ['lat' => 11.3254, 'lng' => 106.4770],
            'binh duong' => ['lat' => 11.3254, 'lng' => 106.4770],
            'đồng nai' => ['lat' => 10.9465, 'lng' => 107.1676],
            'dong nai' => ['lat' => 10.9465, 'lng' => 107.1676],
            'bà rịa vũng tàu' => ['lat' => 10.5417, 'lng' => 107.2429],
            'ba ria vung tau' => ['lat' => 10.5417, 'lng' => 107.2429],
            'vũng tàu' => ['lat' => 10.3460, 'lng' => 107.0843],
            'vung tau' => ['lat' => 10.3460, 'lng' => 107.0843],
            'tây ninh' => ['lat' => 11.3351, 'lng' => 106.1098],
            'tay ninh' => ['lat' => 11.3351, 'lng' => 106.1098],
            'bình phước' => ['lat' => 11.7511, 'lng' => 106.7234],
            'binh phuoc' => ['lat' => 11.7511, 'lng' => 106.7234],
            
            // Đồng bằng sông Cửu Long
            'long an' => ['lat' => 10.6956, 'lng' => 106.2431],
            'tiền giang' => ['lat' => 10.4493, 'lng' => 106.3420],
            'tien giang' => ['lat' => 10.4493, 'lng' => 106.3420],
            'bến tre' => ['lat' => 10.2433, 'lng' => 106.3757],
            'ben tre' => ['lat' => 10.2433, 'lng' => 106.3757],
            'trà vinh' => ['lat' => 9.8124, 'lng' => 106.2992],
            'tra vinh' => ['lat' => 9.8124, 'lng' => 106.2992],
            'vĩnh long' => ['lat' => 10.2397, 'lng' => 105.9571],
            'vinh long' => ['lat' => 10.2397, 'lng' => 105.9571],
            'đồng tháp' => ['lat' => 10.4938, 'lng' => 105.6881],
            'dong thap' => ['lat' => 10.4938, 'lng' => 105.6881],
            'an giang' => ['lat' => 10.5215, 'lng' => 105.1258],
            'kiên giang' => ['lat' => 10.0125, 'lng' => 105.0808],
            'kien giang' => ['lat' => 10.0125, 'lng' => 105.0808],
            'cần thơ' => ['lat' => 10.0452, 'lng' => 105.7469],
            'can tho' => ['lat' => 10.0452, 'lng' => 105.7469],
            'hậu giang' => ['lat' => 9.7579, 'lng' => 105.6412],
            'hau giang' => ['lat' => 9.7579, 'lng' => 105.6412],
            'sóc trăng' => ['lat' => 9.6025, 'lng' => 105.9739],
            'soc trang' => ['lat' => 9.6025, 'lng' => 105.9739],
            'bạc liêu' => ['lat' => 9.2515, 'lng' => 105.7246],
            'bac lieu' => ['lat' => 9.2515, 'lng' => 105.7246],
            'cà mau' => ['lat' => 9.1526, 'lng' => 105.1960],
            'ca mau' => ['lat' => 9.1526, 'lng' => 105.1960],
            
            // Tuyên Quang và các tỉnh miền núi phía Bắc
            'tuyên quang' => ['lat' => 21.8237, 'lng' => 105.2280],
            'tuyen quang' => ['lat' => 21.8237, 'lng' => 105.2280],
            'hà giang' => ['lat' => 22.8025, 'lng' => 104.9784],
            'ha giang' => ['lat' => 22.8025, 'lng' => 104.9784],
            'cao bằng' => ['lat' => 22.6663, 'lng' => 106.2520],
            'cao bang' => ['lat' => 22.6663, 'lng' => 106.2520],
            'bắc kạn' => ['lat' => 22.1474, 'lng' => 105.8348],
            'bac kan' => ['lat' => 22.1474, 'lng' => 105.8348],
            'lạng sơn' => ['lat' => 21.8537, 'lng' => 106.7610],
            'lang son' => ['lat' => 21.8537, 'lng' => 106.7610],
            'lào cai' => ['lat' => 22.4809, 'lng' => 103.9755],
            'lao cai' => ['lat' => 22.4809, 'lng' => 103.9755],
            'yên bái' => ['lat' => 21.7168, 'lng' => 104.8986],
            'yen bai' => ['lat' => 21.7168, 'lng' => 104.8986],
            'thái nguyên' => ['lat' => 21.5671, 'lng' => 105.8252],
            'thai nguyen' => ['lat' => 21.5671, 'lng' => 105.8252],
            'phú thọ' => ['lat' => 21.2680, 'lng' => 105.2045],
            'phu tho' => ['lat' => 21.2680, 'lng' => 105.2045],
            'vĩnh phúc' => ['lat' => 21.3609, 'lng' => 105.5474],
            'vinh phuc' => ['lat' => 21.3609, 'lng' => 105.5474],
            'bắc giang' => ['lat' => 21.2819, 'lng' => 106.1946],
            'bac giang' => ['lat' => 21.2819, 'lng' => 106.1946],
            'sơn la' => ['lat' => 21.3256, 'lng' => 103.9188],
            'son la' => ['lat' => 21.3256, 'lng' => 103.9188],
            'điện biên' => ['lat' => 21.3833, 'lng' => 103.0167],
            'dien bien' => ['lat' => 21.3833, 'lng' => 103.0167],
            'lai châu' => ['lat' => 22.3864, 'lng' => 103.4702],
            'lai chau' => ['lat' => 22.3864, 'lng' => 103.4702],
            'hòa bình' => ['lat' => 20.6861, 'lng' => 105.3131],
            'hoa binh' => ['lat' => 20.6861, 'lng' => 105.3131],
        ];
        
        // Tìm kiếm tỉnh/thành phố trong địa chỉ
        foreach ($coordinates as $province => $coords) {
            if (strpos($addressLower, $province) !== false) {
                Log::info('Found province coordinates', [
                    'province' => $province,
                    'coordinates' => $coords
                ]);
                return $coords;
            }
        }
        
        return null;
    }
    
    /**
     * Chọn kết quả geocoding tốt nhất từ danh sách kết quả
     */
    private function selectBestGeocodingResult($results, $originalAddress)
    {
        if (empty($results)) {
            return null;
        }
        
        // Ưu tiên các loại địa danh theo thứ tự
        $preferredTypes = [
            'village' => 10,      // Xã
            'town' => 9,          // Thị trấn
            'municipality' => 8,  // Thị xã
            'city' => 7,          // Thành phố
            'county' => 6,        // Huyện
            'state' => 5,         // Tỉnh
            'administrative' => 4,
            'hamlet' => 3,
            'suburb' => 2
        ];
        
        $scoredResults = [];
        
        foreach ($results as $result) {
            if (!isset($result['lat']) || !isset($result['lon'])) {
                continue;
            }
            
            $score = 0;
            
            // Điểm từ importance (0-1)
            $importance = floatval($result['importance'] ?? 0);
            $score += $importance * 100;
            
            // Điểm từ type
            $type = $result['type'] ?? '';
            if (isset($preferredTypes[$type])) {
                $score += $preferredTypes[$type] * 10;
            }
            
            // Điểm từ class (ưu tiên place, boundary)
            $class = $result['class'] ?? '';
            if ($class === 'place') {
                $score += 20;
            } elseif ($class === 'boundary') {
                $score += 15;
            }
            
            // Kiểm tra xem display_name có chứa các từ khóa quan trọng không
            $displayName = mb_strtolower($result['display_name'] ?? '', 'UTF-8');
            $addressLower = mb_strtolower($originalAddress, 'UTF-8');
            
            // Tách địa chỉ thành các phần
            $addressParts = preg_split('/[,\s]+/', $addressLower);
            $matchCount = 0;
            foreach ($addressParts as $part) {
                if (strlen($part) > 2 && strpos($displayName, $part) !== false) {
                    $matchCount++;
                }
            }
            $score += $matchCount * 5;
            
            $scoredResults[] = [
                'result' => $result,
                'score' => $score
            ];
        }
        
        // Sắp xếp theo điểm giảm dần
        usort($scoredResults, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        Log::info('Geocoding results scored', [
            'total_results' => count($results),
            'top_3_scores' => array_slice(array_map(function($r) {
                return [
                    'score' => $r['score'],
                    'display_name' => $r['result']['display_name'] ?? 'N/A',
                    'type' => $r['result']['type'] ?? 'N/A'
                ];
            }, $scoredResults), 0, 3)
        ]);
        
        return $scoredResults[0]['result'] ?? null;
    }
    
    /**
     * Chuẩn hóa địa chỉ Việt Nam để geocoding chính xác hơn
     */
    private function normalizeVietnameseAddress($address)
    {
        // Loại bỏ số nhà chi tiết để tập trung vào địa danh chính
        $address = preg_replace('/^\d+[A-Za-z]?,?\s*/', '', $address);
        
        // Chuẩn hóa các từ viết tắt
        $replacements = [
            '/\bTP\.\s*/i' => 'Thành phố ',
            '/\bTp\s*/i' => 'Thành phố ',
            '/\bQ\.\s*/i' => 'Quận ',
            '/\bP\.\s*/i' => 'Phường ',
            '/\bX\.\s*/i' => 'Xã ',
            '/\bTT\.\s*/i' => 'Thị trấn ',
            '/\bTX\.\s*/i' => 'Thị xã ',
            '/\bH\.\s*/i' => 'Huyện ',
        ];
        
        foreach ($replacements as $pattern => $replacement) {
            $address = preg_replace($pattern, $replacement, $address);
        }
        
        // Đặc biệt: Nếu chỉ có phường/xã mà không có tỉnh/thành phố
        // Thêm tỉnh/thành phố vào cuối để geocoding chính xác hơn
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Danh sách tỉnh/thành phố để kiểm tra
        $provinces = [
            'tuyên quang', 'tuyen quang', 'hà nội', 'ha noi', 'hồ chí minh', 'ho chi minh',
            'đà nẵng', 'da nang', 'cần thơ', 'can tho', 'hải phòng', 'hai phong',
            'vĩnh long', 'vinh long', 'an giang', 'bến tre', 'ben tre', 'trà vinh', 'tra vinh'
        ];
        
        $hasProvince = false;
        foreach ($provinces as $province) {
            if (strpos($addressLower, $province) !== false) {
                $hasProvince = true;
                break;
            }
        }
        
        // Nếu không có tỉnh/thành phố, cố gắng thêm vào
        if (!$hasProvince) {
            // Kiểm tra các phường/xã đặc biệt
            if (strpos($addressLower, 'mỹ lâm') !== false || strpos($addressLower, 'my lam') !== false) {
                $address .= ', Tuyên Quang';
            }
        }
        
        // Thêm "Việt Nam" nếu chưa có
        if (!stripos($address, 'việt nam') && !stripos($address, 'vietnam')) {
            $address .= ', Việt Nam';
        }
        
        return trim($address);
    }
    
    /**
     * Tính khoảng cách từ cửa hàng đến địa chỉ khách hàng (đường đi thực tế theo đường bộ Việt Nam)
     */
    private function calculateDistance($customerAddress)
    {
        try {
            // Tọa độ cửa hàng (Vĩnh Long)
            $storeLat = 10.2397;
            $storeLng = 105.9571;
            
            // Kiểm tra bảng tra cứu khoảng cách cố định trước
            $fixedDistance = $this->getFixedDistanceByAddress($customerAddress);
            if ($fixedDistance !== null) {
                Log::info('Using fixed distance for address', [
                    'address' => $customerAddress,
                    'distance_km' => $fixedDistance
                ]);
                return $fixedDistance;
            }
            
            // Cải thiện địa chỉ tìm kiếm - Chuẩn hóa địa chỉ Việt Nam
            $searchAddress = $this->normalizeVietnameseAddress($customerAddress);
            
            Log::info('Normalized address for geocoding', [
                'original' => $customerAddress,
                'normalized' => $searchAddress
            ]);
            
            // Kiểm tra xem có nên ưu tiên tọa độ tỉnh/thành phố không
            // Nếu địa chỉ chỉ có phường/xã mà không đủ chi tiết
            $provinceCoords = $this->getProvinceCoordinates($customerAddress);
            if ($provinceCoords && $this->shouldUseProvinceCoordinates($customerAddress)) {
                Log::info('Address lacks detail, using province coordinates directly', [
                    'address' => $customerAddress,
                    'province_coords' => $provinceCoords
                ]);
                
                $customerLat = $provinceCoords['lat'];
                $customerLng = $provinceCoords['lng'];
                
                $haversineDistance = $this->calculateHaversineDistance($storeLat, $storeLng, $customerLat, $customerLng);
                $roadDistance = $this->applyVietnamRoadFactor($haversineDistance, $customerAddress);
                
                return $roadDistance;
            }
            
            // Bước 1: Geocode địa chỉ khách hàng bằng Nominatim với cải tiến
            $geocodeUrl = 'https://nominatim.openstreetmap.org/search';
            $geocodeParams = [
                'format' => 'json',
                'q' => $searchAddress,
                'limit' => 10, // Tăng limit để có nhiều kết quả
                'countrycodes' => 'vn',
                'accept-language' => 'vi',
                'addressdetails' => 1, // Lấy chi tiết địa chỉ
                'bounded' => 1, // Giới hạn trong Vietnam
                'viewbox' => '102.14,8.18,109.46,23.39' // Bounding box Vietnam
            ];
            
            $context = stream_context_create([
                'http' => [
                    'header' => "User-Agent: ShopNangTho/1.0\r\nAccept-Language: vi\r\n",
                    'timeout' => 5
                ]
            ]);
            
            $geocodeResponse = @file_get_contents($geocodeUrl . '?' . http_build_query($geocodeParams), false, $context);
            
            if ($geocodeResponse === false) {
                Log::warning('Nominatim geocoding failed for address: ' . $customerAddress);
                return $this->estimateDistanceByArea($customerAddress);
            }
            
            $geocodeData = json_decode($geocodeResponse, true);
            
            if (empty($geocodeData)) {
                Log::warning('No geocoding results from Nominatim, trying province coordinates');
                
                // Fallback: Sử dụng tọa độ tỉnh/thành phố
                $provinceCoords = $this->getProvinceCoordinates($customerAddress);
                if ($provinceCoords) {
                    $customerLat = $provinceCoords['lat'];
                    $customerLng = $provinceCoords['lng'];
                    
                    Log::info('Using province coordinates as fallback', [
                        'lat' => $customerLat,
                        'lng' => $customerLng
                    ]);
                    
                    // Tính khoảng cách với tọa độ tỉnh/thành phố
                    $haversineDistance = $this->calculateHaversineDistance($storeLat, $storeLng, $customerLat, $customerLng);
                    $roadDistance = $this->applyVietnamRoadFactor($haversineDistance, $customerAddress);
                    
                    return $roadDistance;
                }
                
                return $this->estimateDistanceByArea($customerAddress);
            }
            
            // Chọn kết quả tốt nhất dựa trên importance và type
            $bestResult = $this->selectBestGeocodingResult($geocodeData, $customerAddress);
            
            if (!$bestResult) {
                Log::warning('No suitable geocoding result found, trying province coordinates');
                
                // Fallback: Sử dụng tọa độ tỉnh/thành phố
                $provinceCoords = $this->getProvinceCoordinates($customerAddress);
                if ($provinceCoords) {
                    $customerLat = $provinceCoords['lat'];
                    $customerLng = $provinceCoords['lng'];
                    
                    Log::info('Using province coordinates as fallback', [
                        'lat' => $customerLat,
                        'lng' => $customerLng
                    ]);
                    
                    // Tính khoảng cách với tọa độ tỉnh/thành phố
                    $haversineDistance = $this->calculateHaversineDistance($storeLat, $storeLng, $customerLat, $customerLng);
                    $roadDistance = $this->applyVietnamRoadFactor($haversineDistance, $customerAddress);
                    
                    return $roadDistance;
                }
                
                return $this->estimateDistanceByArea($customerAddress);
            }
            
            $customerLat = floatval($bestResult['lat']);
            $customerLng = floatval($bestResult['lon']);
            
            // Validate kết quả geocoding - kiểm tra xem có nằm trong Vietnam không
            if (!$this->isValidVietnamCoordinates($customerLat, $customerLng, $customerAddress)) {
                Log::warning('Geocoding result outside expected region, using province coordinates', [
                    'geocoded_lat' => $customerLat,
                    'geocoded_lng' => $customerLng,
                    'address' => $customerAddress
                ]);
                
                // Fallback: Sử dụng tọa độ tỉnh/thành phố
                $provinceCoords = $this->getProvinceCoordinates($customerAddress);
                if ($provinceCoords) {
                    $customerLat = $provinceCoords['lat'];
                    $customerLng = $provinceCoords['lng'];
                    
                    Log::info('Using province coordinates instead', [
                        'lat' => $customerLat,
                        'lng' => $customerLng
                    ]);
                }
            }
            
            Log::info('Selected geocoding result', [
                'lat' => $customerLat,
                'lng' => $customerLng,
                'display_name' => $bestResult['display_name'] ?? 'N/A',
                'type' => $bestResult['type'] ?? 'N/A',
                'importance' => $bestResult['importance'] ?? 'N/A'
            ]);
            
            // Bước 2: Tính khoảng cách đường chim bay
            $haversineDistance = $this->calculateHaversineDistance($storeLat, $storeLng, $customerLat, $customerLng);
            
            // Bước 3: Áp dụng hệ số đường bộ Việt Nam để tính khoảng cách thực tế
            $roadDistance = $this->applyVietnamRoadFactor($haversineDistance, $customerAddress);
            
            Log::info('Distance calculated with Vietnam road factor', [
                'address' => $customerAddress,
                'haversine_km' => $haversineDistance,
                'road_km' => $roadDistance,
                'customer_coords' => [$customerLat, $customerLng]
            ]);
            
            return $roadDistance;
            
        } catch (\Exception $e) {
            Log::error('Calculate distance error: ' . $e->getMessage());
            return $this->estimateDistanceByArea($customerAddress);
        }
    }
    
    /**
     * Bảng tra cứu khoảng cách cố định cho các tuyến đường phổ biến từ Vĩnh Long
     */
    private function getFixedDistanceByAddress($address)
    {
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Bảng khoảng cách thực tế theo đường bộ từ Vĩnh Long (km)
        $distanceTable = [
            // Hà Nội
            'hà nội' => 1650,
            'ha noi' => 1650,
            'hanoi' => 1650,
            
            // TP.HCM
            'hồ chí minh' => 135,
            'ho chi minh' => 135,
            'sài gòn' => 135,
            'saigon' => 135,
            'tp.hcm' => 135,
            'tphcm' => 135,
            
            // Đà Nẵng
            'đà nẵng' => 950,
            'da nang' => 950,
            
            // Cần Thơ
            'cần thơ' => 35,
            'can tho' => 35,
            
            // Hải Phòng
            'hải phòng' => 1700,
            'hai phong' => 1700,
            
            // Huế
            'huế' => 1100,
            'hue' => 1100,
            'thừa thiên huế' => 1100,
            'thua thien hue' => 1100,
            
            // Nha Trang
            'nha trang' => 450,
            'khánh hòa' => 450,
            'khanh hoa' => 450,
            
            // Đà Lạt
            'đà lạt' => 350,
            'da lat' => 350,
            'lâm đồng' => 350,
            'lam dong' => 350,
            
            // Vũng Tàu
            'vũng tàu' => 200,
            'vung tau' => 200,
            'bà rịa vũng tàu' => 200,
            'ba ria vung tau' => 200,
            
            // Các tỉnh Đồng bằng sông Cửu Long
            'đồng tháp' => 50,
            'dong thap' => 50,
            'cao lãnh' => 50,
            'cao lanh' => 50,
            
            'an giang' => 90,
            'long xuyên' => 90,
            'long xuyen' => 90,
            'châu đốc' => 120,
            'chau doc' => 120,
            
            'kiên giang' => 110,
            'kien giang' => 110,
            'rạch giá' => 110,
            'rach gia' => 110,
            
            'cà mau' => 180,
            'ca mau' => 180,
            
            'bạc liêu' => 140,
            'bac lieu' => 140,
            
            'sóc trăng' => 65,
            'soc trang' => 65,
            
            'trà vinh' => 60,
            'tra vinh' => 60,
            
            'bến tre' => 80,
            'ben tre' => 80,
            
            'tiền giang' => 70,
            'tien giang' => 70,
            'mỹ tho' => 70,
            'my tho' => 70,
            
            'hậu giang' => 45,
            'hau giang' => 45,
            'vị thanh' => 45,
            'vi thanh' => 45,
            
            'long an' => 100,
            'tân an' => 100,
            'tan an' => 100,
            
            // Đông Nam Bộ
            'đồng nai' => 150,
            'dong nai' => 150,
            'biên hòa' => 150,
            'bien hoa' => 150,
            
            'bình dương' => 120,
            'binh duong' => 120,
            'thủ dầu một' => 120,
            'thu dau mot' => 120,
            
            'tây ninh' => 180,
            'tay ninh' => 180,
            
            'bình phước' => 220,
            'binh phuoc' => 220,
        ];
        
        // Tìm kiếm trong bảng tra cứu
        foreach ($distanceTable as $keyword => $distance) {
            if (strpos($addressLower, $keyword) !== false) {
                return $distance;
            }
        }
        
        return null; // Không tìm thấy trong bảng tra cứu
    }
    
    /**
     * Áp dụng hệ số đường bộ Việt Nam để chuyển đổi từ khoảng cách chim bay sang đường bộ thực tế
     */
    private function applyVietnamRoadFactor($haversineDistance, $address)
    {
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Hệ số đường bộ khác nhau theo khu vực
        // Đường bộ thực tế = Đường chim bay × Hệ số
        
        // Nội thành/Lân cận (< 100km): Hệ số 1.2-1.3
        if ($haversineDistance < 100) {
            $roadFactor = 1.25;
        }
        // Miền Nam (100-500km): Hệ số 1.3-1.4 (đường tương đối thẳng)
        else if ($haversineDistance < 500) {
            $roadFactor = 1.35;
        }
        // Miền Trung (500-1000km): Hệ số 1.4-1.5 (đường quanh co)
        else if ($haversineDistance < 1000) {
            // Kiểm tra nếu là miền Trung (đường ven biển quanh co hơn)
            if (strpos($addressLower, 'đà nẵng') !== false || 
                strpos($addressLower, 'da nang') !== false ||
                strpos($addressLower, 'huế') !== false ||
                strpos($addressLower, 'hue') !== false ||
                strpos($addressLower, 'quảng') !== false ||
                strpos($addressLower, 'quang') !== false) {
                $roadFactor = 1.5;
            } else {
                $roadFactor = 1.4;
            }
        }
        // Miền Bắc (> 1000km): Hệ số 1.45-1.55 (đường dài, nhiều đèo)
        else {
            // Kiểm tra nếu là miền Bắc miền núi
            if (strpos($addressLower, 'hà nội') !== false ||
                strpos($addressLower, 'ha noi') !== false ||
                strpos($addressLower, 'hanoi') !== false) {
                $roadFactor = 1.48; // Đường cao tốc tương đối thẳng
            } else {
                $roadFactor = 1.55; // Các tỉnh miền núi
            }
        }
        
        $roadDistance = round($haversineDistance * $roadFactor, 1);
        
        return $roadDistance;
    }
    
    /**
     * Tính khoảng cách đường chim bay bằng công thức Haversine
     */
    private function calculateHaversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371; // km
        
        $latDiff = deg2rad($lat2 - $lat1);
        $lngDiff = deg2rad($lng2 - $lng1);
        
        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;
        
        return round($distance, 1);
    }
    
    /**
     * Ước tính khoảng cách dựa trên khu vực trong địa chỉ (sử dụng bảng tra cứu)
     */
    private function estimateDistanceByArea($address)
    {
        // Thử tìm trong bảng tra cứu trước
        $fixedDistance = $this->getFixedDistanceByAddress($address);
        if ($fixedDistance !== null) {
            return $fixedDistance;
        }
        
        $addressLower = mb_strtolower($address, 'UTF-8');
        
        // Nội thành Vĩnh Long
        if (stripos($addressLower, 'vĩnh long') !== false || stripos($addressLower, 'vinh long') !== false) {
            return 5; // 5km cho nội thành Vĩnh Long
        }
        
        // Các tỉnh Đồng bằng sông Cửu Long lân cận
        $nearbyProvinces = [
            'cần thơ' => 35,
            'can tho' => 35,
            'đồng tháp' => 50,
            'dong thap' => 50,
            'tiền giang' => 70,
            'tien giang' => 70,
            'an giang' => 90,
            'hậu giang' => 45,
            'hau giang' => 45,
            'sóc trăng' => 65,
            'soc trang' => 65,
            'trà vinh' => 60,
            'tra vinh' => 60,
            'bến tre' => 80,
            'ben tre' => 80,
            'bạc liêu' => 140,
            'bac lieu' => 140,
            'cà mau' => 180,
            'ca mau' => 180,
            'kiên giang' => 110,
            'kien giang' => 110,
            'long an' => 100
        ];
        
        foreach ($nearbyProvinces as $province => $distance) {
            if (stripos($addressLower, $province) !== false) {
                return $distance;
            }
        }
        
        // TP.HCM
        if (stripos($addressLower, 'hồ chí minh') !== false || 
            stripos($addressLower, 'ho chi minh') !== false ||
            stripos($addressLower, 'sài gòn') !== false ||
            stripos($addressLower, 'saigon') !== false) {
            return 135; // 135km đến TP.HCM theo đường bộ
        }
        
        // Hà Nội
        if (stripos($addressLower, 'hà nội') !== false || 
            stripos($addressLower, 'ha noi') !== false ||
            stripos($addressLower, 'hanoi') !== false) {
            return 1650; // 1650km đến Hà Nội theo đường bộ
        }
        
        // Đà Nẵng
        if (stripos($addressLower, 'đà nẵng') !== false || stripos($addressLower, 'da nang') !== false) {
            return 950;
        }
        
        // Miền Trung khác
        if (stripos($addressLower, 'huế') !== false || stripos($addressLower, 'hue') !== false) {
            return 1100;
        }
        
        // Mặc định cho các địa chỉ không xác định
        Log::warning('Could not determine distance for address: ' . $address . ', using default 100km');
        return 100; // Tăng mặc định lên 100km thay vì 5km để an toàn
    }

    public function payment()
    {
        if (!Auth::check()) return redirect()->route('login');

        $addressData = session('checkout_address');
        if (!$addressData) {
            return redirect()->route('checkout.index')->withErrors(['address' => 'Vui lòng nhập địa chỉ giao hàng']);
        }

        $cart = $this->currentCart();
        if (!$cart) return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)session('checkout_selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product.product_images'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $price * $qty,
            ];
        })->filter();

        if ($items->isEmpty()) {
            return redirect()->route('shop.index')->withErrors(['cart' => 'Giỏ hàng trống']);
        }

        $subtotal = (int) $items->sum('subtotal');
        // Xác định khu vực dựa trên địa chỉ khách hàng
        $customerAddress = $addressData['customer_address'] ?? '';
        $areaType = $this->detectAreaType($customerAddress);
        
        // Tính khoảng cách thực tế từ OpenStreetMap
        $distance = $this->calculateDistance($customerAddress);
        
        // Log để debug
        Log::info('Checkout Payment - Distance Calculation', [
            'address' => $customerAddress,
            'distance' => $distance,
            'area_type' => $areaType,
            'subtotal' => $subtotal
        ]);
        
        // Tính phí vận chuyển động từ database dựa trên khoảng cách thực tế
        $shippingFee = $this->calculateShippingFee($subtotal, $distance, $areaType);
        
        Log::info('Checkout Payment - Shipping Fee', [
            'shipping_fee' => $shippingFee,
            'distance' => $distance,
            'subtotal' => $subtotal
        ]);
        
        // Lấy danh sách voucher từ database
        $availableVouchers = [];
        $productIds = $items->pluck('product.id')->toArray();
        
        // Lấy các chương trình khuyến mãi đang hoạt động
        $activeDiscounts = Discount::where('status', 1)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->with('products')
            ->get();
        foreach ($activeDiscounts as $discount) {
            // Kiểm tra số lượng voucher còn lại
            if ($discount->quantity !== null) {
                $remaining = $discount->quantity - $discount->used_quantity;
                if ($remaining <= 0) {
                    continue; // Bỏ qua voucher đã hết
                }
            }

            // Kiểm tra xem có sản phẩm nào trong giỏ hàng thuộc chương trình khuyến mãi không
            $discountProductIds = $discount->products->pluck('id')->toArray();

            // Nếu chương trình áp dụng cho tất cả sản phẩm (không có sản phẩm cụ thể)
            // hoặc có ít nhất 1 sản phẩm trong giỏ thuộc chương trình
            if (empty($discountProductIds) || count(array_intersect($productIds, $discountProductIds)) > 0) {
                // Tính giá trị giảm
                $discountValue = 0;
                if ($discount->discount_type === 'percent') {
                    $discountValue = ($subtotal * $discount->discount_value) / 100;
                } else {
                    $discountValue = $discount->discount_value;
                }
                $availableVouchers[] = [
                    'code' => $discount->code,
                    'label' => $discount->description ?: 'Giảm giá đặc biệt',
                    'discount' => (int) $discountValue,
                    'min_order' => 0,
                    'discount_type' => $discount->discount_type,
                    'discount_value' => $discount->discount_value,
                    'remaining' => $discount->quantity !== null ? ($discount->quantity - $discount->used_quantity) : null
                ];
            }
        }

        $total = $subtotal + $shippingFee;
        
        // Lấy tọa độ chính xác cho frontend (với error handling)
        $customerCoordinates = null;
        try {
            // Kiểm tra xem có địa chỉ không
            $customerAddress = $addressData['customer_address'] ?? ($addressData['address'] ?? null);
            
            if ($customerAddress && !empty($customerAddress)) {
                Log::info('Getting coordinates for address: ' . $customerAddress);
                $customerCoordinates = $this->getAccurateCustomerCoordinates($customerAddress);
                Log::info('Got coordinates: ' . json_encode($customerCoordinates));
            } else {
                Log::warning('No customer address found in addressData');
            }
        } catch (\Exception $e) {
            Log::error('Error getting customer coordinates: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            $customerCoordinates = null;
        }

        return view('checkout.payment', [
            'items' => $items,
            'addressData' => $addressData,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'availableVouchers' => $availableVouchers,
            'distance' => $distance,
            'customerCoordinates' => $customerCoordinates, // Truyền tọa độ chính xác (có thể null)
        ]);
    }

    public function place(Request $request)
    {
        $paymentMethod = $request->input('payment_method', 'cod');
        $voucherCode = $request->input('voucher_code');
        $requestInvoice = $request->input('request_invoice', false);
        $insurance = $request->input('insurance', 0);
        $addressData = session('checkout_address');
        if (!$addressData) {
            return redirect()->route('checkout.index')->withErrors(['address' => 'Vui lòng nhập địa chỉ giao hàng']);
        }

        $cart = $this->currentCart();
        if (!$cart) return back()->withErrors(['cart' => 'Giỏ hàng trống']);

        $selected = collect((array)session('checkout_selected', []))
            ->map(fn($v) => (int)$v)
            ->filter()->unique()->values();

        $rows = CartItem::where('cart_id', $cart->id)
            ->when($selected->isNotEmpty(), fn($q) => $q->whereIn('product_id', $selected->all()))
            ->with(['product'])
            ->join('products', 'products.id', '=', 'cart_items.product_id')
            ->where('products.status', 1)
            ->select('cart_items.*')
            ->get();

        $items = $rows->map(function ($ci) {
            $p = $ci->product;
            if (!$p) return null;
            $price = (int) $p->price;
            $qty = (int) $ci->quantity;
            return [
                'product' => $p,
                'qty' => $qty,
                'subtotal' => $price * $qty,
            ];
        })->filter();
        if ($items->isEmpty()) return back()->withErrors(['cart' => 'Giỏ hàng không hợp lệ hoặc chưa chọn sản phẩm']);

        // Validate stock
        foreach ($items as $row) {
            if ($row['qty'] < 1 || $row['qty'] > $row['product']->stock) {
                return back()->withErrors(['qty' => 'Số lượng không hợp lệ']);
            }
        }

        $subtotal = (int)$items->sum('subtotal');
        $discount = 0;
        // Xác định khu vực dựa trên địa chỉ khách hàng
        $areaType = $this->detectAreaType($addressData['customer_address']);
        
        // Sử dụng khoảng cách OSRM từ frontend nếu có, nếu không thì tính lại
        $distance = $request->input('osrm_distance');
        if (!$distance || !is_numeric($distance)) {
            // Fallback: Tính khoảng cách bằng backend
            $distance = $this->calculateDistance($addressData['customer_address']);
            Log::info('Using backend calculated distance', ['distance' => $distance]);
        } else {
            $distance = floatval($distance);
            Log::info('Using OSRM distance from frontend', ['distance' => $distance]);
        }
        
        // Tính phí vận chuyển động từ database với khoảng cách thực tế
        $shippingFee = $this->calculateShippingFee($subtotal, $distance, $areaType);
        
        $appliedDiscountId = null;
        
        // Bảo hiểm bảo vệ người tiêu dùng
        $insuranceFee = $insurance ? 1300 : 0;
        
        // Áp dụng voucher từ database
        if ($voucherCode) {
            $voucherDiscount = Discount::where('code', $voucherCode)
                ->where('status', 1)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->with('products')
                ->first();
            
            if ($voucherDiscount) {
                // Kiểm tra số lượng voucher còn lại
                if ($voucherDiscount->quantity !== null) {
                    $remaining = $voucherDiscount->quantity - $voucherDiscount->used_quantity;
                    if ($remaining <= 0) {
                        return back()->withErrors(['voucher' => 'Voucher đã hết số lượng sử dụng']);
                    }
                }
                
                $productIds = $items->pluck('product.id')->toArray();
                $discountProductIds = $voucherDiscount->products->pluck('id')->toArray();
                
                // Kiểm tra voucher có áp dụng cho sản phẩm trong giỏ không
                if (empty($discountProductIds) || count(array_intersect($productIds, $discountProductIds)) > 0) {
                    if ($voucherDiscount->discount_type === 'percent') {
                        $discount = ($subtotal * $voucherDiscount->discount_value) / 100;
                    } else {
                        $discount = $voucherDiscount->discount_value;
                    }
                    $discount = (int) $discount;
                    $appliedDiscountId = $voucherDiscount->id;
                }
            }
        }
        
        $total = $subtotal + $shippingFee - $discount + $insuranceFee;
        $order = null;

        DB::transaction(function() use ($items, $addressData, $paymentMethod, $total, $voucherCode, $discount, $appliedDiscountId, $insuranceFee, $shippingFee, &$order) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'customer_name' => $addressData['customer_name'],
                'customer_email' => $addressData['customer_email'],
                'customer_phone' => $addressData['customer_phone'],
                'shipping_address' => $addressData['customer_address'],
                'total_price' => $total,
                'status' => 'pending',
                'payment_method' => $paymentMethod,
                'discount_id' => $appliedDiscountId,
                'discount_code' => $voucherCode,
                'discount_amount' => $discount,
                'insurance_fee' => $insuranceFee,
                'shipping_fee' => $shippingFee,
            ]);

            foreach ($items as $row) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $row['product']->id,
                    'quantity' => $row['qty'],
                    'price' => $row['product']->price,
                ]);
                // reduce stock
                $row['product']->decrement('stock', $row['qty']);
            }
            // Tăng số lượng voucher đã sử dụng
            if ($appliedDiscountId) {
                $usedDiscount = Discount::find($appliedDiscountId);
                if ($usedDiscount) {
                    $usedDiscount->increment('used_quantity', 1);
                }
            }
        });

        // Gửi email xác nhận đơn hàng
        try {
            $order->load(['order_items.product']);
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            Log::info('Order confirmation email sent successfully', ['order_id' => $order->id, 'email' => $order->customer_email]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'email' => $order->customer_email,
                'error' => $e->getMessage()
            ]);
            // Không throw exception để không ảnh hưởng đến quá trình đặt hàng
        }

        // Nếu thanh toán MoMo, chuyển hướng đến MoMo
        if ($paymentMethod === 'momo') {
            try {
                $momoService = new MoMoService();
                $orderInfo = "Thanh toán đơn hàng #" . $order->id;
                
                Log::info('Creating MoMo payment', [
                    'order_id' => $order->id,
                    'amount' => $total,
                    'order_info' => $orderInfo
                ]);
                $total = 2000;
                $result = $momoService->createPayment($order->id, $total, $orderInfo);
                
                Log::info('MoMo payment response', [
                    'result' => $result
                ]);

                if (isset($result['payUrl'])) {
                    // Lưu order ID vào session để xử lý callback
                    session(['momo_order_id' => $order->id]);
                    Log::info('Redirecting to MoMo', ['payUrl' => $result['payUrl']]);
                    return redirect($result['payUrl']);
                } else {
                    Log::error('MoMo payment failed - no payUrl', [
                        'result' => $result,
                        'order_id' => $order->id
                    ]);
                    
                    $errorMessage = 'Không thể kết nối đến MoMo. Vui lòng thử lại.';
                    if (isset($result['message'])) {
                        $errorMessage .= ' Lỗi: ' . $result['message'];
                    }
                    
                    return back()->withErrors(['payment' => $errorMessage]);
                }
            } catch (\Exception $e) {
                Log::error('MoMo payment exception', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'order_id' => $order->id
                ]);
                return back()->withErrors(['payment' => 'Lỗi khi tạo thanh toán MoMo: ' . $e->getMessage()]);
            }
        }
        if ($paymentMethod === 'payos') {
            // Create PayOS payment link server-side and redirect to checkout URL
            $data = [
                "orderCode" => (int) $order->id,
                // "amount" => (int) $total,
                "amount" => 2000,
                "description" => "Thanh toán đơn hàng #" . $order->id,
                "returnUrl" => route('payos.success'),
                "cancelUrl" => route('payos.cancel'),
            ];
            try {
                $payosService = new PayosService();
                $response = $payosService->createPaymentLink($data);
                if(isset($response['checkoutUrl'])) {
                    return redirect($response['checkoutUrl']);
                }else{
                    return back()->with(['error' => 'Không thể tạo liên kết thanh toán PayOS. Vui lòng thử lại hoặc chọn phương thức thanh toán khác']);
                }
            } catch (\Throwable $th) {
                return back()->with(['error' => 'Không thể tạo liên kết thanh toán PayOS. Vui lòng thử lại hoặc chọn phương thức thanh toán khác']);
            }
        }
        if ($paymentMethod === 'vnpay') {
            return redirect()->route('vnpay.create', ['order_id' => $order->id, 'total' => $total]);
        }
        if ($paymentMethod === 'sepay') {
            return redirect()->route('sepay.create', ['order_id' => $order->id, 'total' => $total]);
        }
        if ($paymentMethod === 'cod') {
            //Ghi dữ liệu cho bảng payments
            Payment::create([
                'order_id' => $order->id,
                'amount' => $total,
                'payment_method' => 'cod',
                'status' => 'pending', 
            ]);
            // Xóa session và cart items cho COD
            session()->forget(['checkout_address', 'checkout_selected', 'buy_now_item']);
            if ($cart) {
                // Xóa các sản phẩm đã đặt hàng khỏi giỏ hàng
                if ($selected->isNotEmpty()) {
                    CartItem::where('cart_id', $cart->id)
                        ->whereIn('product_id', $selected->all())
                        ->delete();
                } else {
                    // Nếu không có sản phẩm được chọn cụ thể, xóa toàn bộ giỏ hàng
                    CartItem::where('cart_id', $cart->id)->delete();
                }
            }
        }

        // Nếu yêu cầu hóa đơn, chuyển đến trang in hóa đơn
        if ($requestInvoice) {
            return redirect()->route('invoice.show', $order->id);
        }

        // Chuyển về trang chủ với thông báo thành công
        return redirect()->route('shop.index')->with('success', 'Bạn đã đặt hàng thành công! Vui lòng kiểm tra email để xem chi tiết đơn hàng.');
    }

    public function momoReturn(Request $request)
    {
        $orderId = $request->input('orderId');
        $resultCode = $request->input('resultCode');

        if ($resultCode == 0) {
            // Thanh toán thành công
            $order = Order::find($orderId);
            if ($order) {
                $order->update(['status' => 'confirmed']);
                
                // Gửi email xác nhận đơn hàng nếu chưa gửi
                try {
                    $order->load(['order_items.product']);
                    Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
                    Log::info('Order confirmation email sent after MoMo payment', ['order_id' => $order->id]);
                } catch (\Exception $e) {
                    Log::error('Failed to send order confirmation email after MoMo payment', [
                        'order_id' => $order->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Xóa session và cart items
            session()->forget(['checkout_address', 'checkout_selected', 'momo_order_id', 'buy_now_item']);
            $cart = $this->currentCart();
            if ($cart) {
                // Xóa toàn bộ giỏ hàng sau khi thanh toán MoMo thành công
                CartItem::where('cart_id', $cart->id)->delete();
            }

            return redirect()->route('home')->with('success', 'Thanh toán MoMo thành công! Đơn hàng #' . $orderId . ' đã được xác nhận. Vui lòng kiểm tra email để xem chi tiết.');
        } else {
            // Thanh toán thất bại
            return redirect()->route('checkout.payment')->withErrors(['payment' => 'Thanh toán MoMo thất bại. Vui lòng thử lại.']);
        }
    }

    public function momoNotify(Request $request)
    {
        $momoService = new MoMoService();
        $data = $request->all();

        if ($momoService->verifySignature($data)) {
            $orderId = $data['orderId'];
            $resultCode = $data['resultCode'];

            if ($resultCode == 0) {
                $order = Order::find($orderId);
                if ($order) {
                    $order->update(['status' => 'confirmed']);
                }
            }

            return response()->json(['message' => 'OK'], 200);
        }

        return response()->json(['message' => 'Invalid signature'], 400);
    }

    /**
     * AJAX endpoint để tính phí vận chuyển với khoảng cách OSRM
     */
    public function calculateShippingFeeAjax(Request $request)
    {
        try {
            $distance = $request->input('osrm_distance');
            $subtotal = $request->input('subtotal', 0);
            $customerAddress = $request->input('customer_address', '');

            if (!$distance || !is_numeric($distance)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Khoảng cách không hợp lệ'
                ]);
            }

            $distance = floatval($distance);
            $areaType = $this->detectAreaType($customerAddress);
            $shippingFee = $this->calculateShippingFee($subtotal, $distance, $areaType);

            Log::info('AJAX Calculate Shipping Fee', [
                'distance' => $distance,
                'subtotal' => $subtotal,
                'area_type' => $areaType,
                'shipping_fee' => $shippingFee
            ]);

            return response()->json([
                'success' => true,
                'shipping_fee' => $shippingFee,
                'distance' => $distance,
                'area_type' => $areaType
            ]);

        } catch (\Exception $e) {
            Log::error('AJAX Calculate Shipping Fee Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tính phí vận chuyển: ' . $e->getMessage()
            ]);
        }
    }
}
