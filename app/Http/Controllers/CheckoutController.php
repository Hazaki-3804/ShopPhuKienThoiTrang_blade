<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Discount;
use App\Models\ShippingFee;
use App\Services\MoMoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'phường 1',
            'phường 2',
            'phường 3',
            'phường 4',
            'phường 5',
            'phường 6',
            'phường 7',
            'phường 8',
            'phường 9',
            'phường 10',
            'phường trường an',
            'phuong truong an',
            'phường tân hòa',
            'phuong tan hoa',
            'phường tân hội',
            'phuong tan hoi',
            'phường tân ngãi',
            'phuong tan ngai',
            // Tên thành phố
            'thành phố vĩnh long',
            'thanh pho vinh long',
            'tp vĩnh long',
            'tp vinh long',
            'tp. vĩnh long',
            'tp. vinh long',
            'vĩnh long',
            'vinh long'
        ];

        // ========== LÂN CẬN (10-50km) ==========
        // Bao gồm: Các huyện trong tỉnh Vĩnh Long + Đồng bằng sông Cửu Long lân cận
        $nearbyAreas = [
            // === Các huyện trong tỉnh Vĩnh Long ===
            'long hồ',
            'long ho',
            'mang thít',
            'mang thit',
            'vũng liêm',
            'vung liem',
            'tam bình',
            'tam binh',
            'trà ôn',
            'tra on',
            'bình minh',
            'binh minh',
            'bình tán',
            'binh tan',

            // === Đồng Tháp (lân cận) ===
            'đồng tháp',
            'dong thap',
            'cao lãnh',
            'cao lanh',
            'sa đéc',
            'sa dec',
            'hồng ngự',
            'hong ngu',
            'tân hồng',
            'tan hong',
            'lấp vò',
            'lap vo',
            'lai vung',
            'châu thành',
            'chau thanh',
            'tam nông',
            'tam nong',
            'tháp mười',
            'thap muoi',
            'thanh bình',
            'thanh binh',

            // === Cần Thơ (lân cận) ===
            'cần thơ',
            'can tho',
            'ninh kiều',
            'ninh kieu',
            'bình thủy',
            'binh thuy',
            'cái răng',
            'cai rang',
            'ô môn',
            'o mon',
            'thốt nốt',
            'thot not',
            'phong điền',
            'phong dien',
            'cờ đỏ',
            'co do',
            'thới lai',
            'thoi lai',
            'vĩnh thạnh',
            'vinh thanh',

            // === Tiền Giang (lân cận) ===
            'tiền giang',
            'tien giang',
            'mỹ tho',
            'my tho',
            'gò công',
            'go cong',
            'cai lậy',
            'cai lay',
            'tân phước',
            'tan phuoc',
            'cái bè',
            'cai be',
            'châu thành tg',
            'chau thanh tg',

            // === An Giang (lân cận) ===
            'an giang',
            'long xuyên',
            'long xuyen',
            'châu đốc',
            'chau doc',
            'tân châu',
            'tan chau',
            'phú tân',
            'phu tan',
            'an phú',
            'an phu',
            'tịnh biên',
            'tinh bien',
            'tri tôn',
            'tri ton',
            'châu phú',
            'chau phu',
            'chợ mới',
            'cho moi',
            'thoại sơn',
            'thoai son',

            // === Hậu Giang (lân cận) ===
            'hậu giang',
            'hau giang',
            'vị thanh',
            'vi thanh',
            'ngã bảy',
            'nga bay',
            'châu thành a',
            'chau thanh a',
            'châu thành hg',
            'chau thanh hg',
            'phụng hiệp',
            'phung hiep',
            'vị thủy',
            'vi thuy',
            'long mỹ',
            'long my',

            // === Sóc Trăng (lân cận) ===
            'sóc trăng',
            'soc trang',
            'ngã năm',
            'nga nam',
            'kế sách',
            'ke sach',
            'mỹ tú',
            'my tu',
            'thạnh trị',
            'thanh tri',
            'mỹ xuyên',
            'my xuyen',
            'long phú',
            'long phu',

            // === Bến Tre (lân cận) ===
            'bến tre',
            'ben tre',
            'ba tri',
            'bình đại',
            'binh dai',
            'giồng trôm',
            'giong trom',
            'châu thành bt',
            'chau thanh bt',
            'chợ lách',
            'cho lach',
            'mỏ cày',
            'mo cay',
            'thạnh phú',
            'thanh phu',

            // === Trà Vinh (lân cận) ===
            'trà vinh',
            'tra vinh',
            'duyên hải',
            'duyen hai',
            'càng long',
            'cang long',
            'cầu kè',
            'cau ke',
            'tiểu cần',
            'tieu can',
            'châu thành tv',
            'chau thanh tv',
            'cầu ngang',
            'cau ngang',
            'trà cú',
            'tra cu'
        ];

        // ========== TOÀN QUỐC (>50km) ==========
        // Tất cả các tỉnh/thành phố khác
        $nationwideAreas = [
            // === Miền Nam ===
            'hồ chí minh',
            'ho chi minh',
            'sài gòn',
            'saigon',
            'tp.hcm',
            'tphcm',
            'quận 1',
            'quận 2',
            'quận 3',
            'quận 4',
            'quận 5',
            'quận 6',
            'quận 7',
            'quận 8',
            'quận 9',
            'quận 10',
            'quận 11',
            'quận 12',
            'thủ đức',
            'thu duc',
            'bình thạnh',
            'binh thanh',
            'phú nhuận',
            'phu nhuan',
            'tân bình',
            'tan binh',
            'tân phú',
            'tan phu',
            'gò vấp',
            'go vap',
            'bình tân hcm',
            'binh tan hcm',
            'bình chánh',
            'binh chanh',
            'hóc môn',
            'hoc mon',
            'củ chi',
            'cu chi',
            'nhà bè',
            'nha be',
            'cần giờ',
            'can gio',

            'đồng nai',
            'dong nai',
            'biên hòa',
            'bien hoa',
            'long thành',
            'long thanh',
            'bình dương',
            'binh duong',
            'thủ dầu một',
            'thu dau mot',
            'dĩ an',
            'di an',
            'thuận an',
            'thuan an',
            'bà rịa vũng tàu',
            'ba ria vung tau',
            'vũng tàu',
            'vung tau',
            'bà rịa',
            'ba ria',
            'tây ninh',
            'tay ninh',
            'bình phước',
            'binh phuoc',
            'đồng xoài',
            'dong xoai',
            'long an',
            'tân an',
            'tan an',
            'bến lức',
            'ben luc',
            'kiên giang',
            'kien giang',
            'rạch giá',
            'rach gia',
            'phú quốc',
            'phu quoc',
            'hà tiên',
            'ha tien',
            'cà mau',
            'ca mau',
            'năm căn',
            'nam can',
            'bạc liêu',
            'bac lieu',

            // === Miền Trung ===
            'đà nẵng',
            'da nang',
            'quảng nam',
            'quang nam',
            'hội an',
            'hoi an',
            'tam kỳ',
            'tam ky',
            'quảng ngãi',
            'quang ngai',
            'bình định',
            'binh dinh',
            'quy nhơn',
            'quy nhon',
            'phú yên',
            'phu yen',
            'tuy hòa',
            'tuy hoa',
            'khánh hòa',
            'khanh hoa',
            'nha trang',
            'ninh thuận',
            'ninh thuan',
            'phan rang',
            'bình thuận',
            'binh thuan',
            'phan thiết',
            'phan thiet',
            'mũi né',
            'mui ne',
            'lâm đồng',
            'lam dong',
            'đà lạt',
            'da lat',
            'bảo lộc',
            'bao loc',
            'đắk lắk',
            'dak lak',
            'buôn ma thuột',
            'buon ma thuot',
            'đắk nông',
            'dak nong',
            'gia nghĩa',
            'gia nghia',
            'gia lai',
            'pleiku',
            'kon tum',
            'quảng trị',
            'quang tri',
            'đông hà',
            'dong ha',
            'thừa thiên huế',
            'thua thien hue',
            'huế',
            'hue',
            'quảng bình',
            'quang binh',
            'đồng hới',
            'dong hoi',
            'hà tĩnh',
            'ha tinh',
            'nghệ an',
            'nghe an',
            'vinh',
            'thanh hóa',
            'thanh hoa',

            // === Miền Bắc ===
            'hà nội',
            'ha noi',
            'hanoi',
            'hoàn kiếm',
            'hoan kiem',
            'ba đình',
            'ba dinh',
            'đống đa',
            'dong da',
            'hai bà trưng',
            'hai ba trung',
            'hoàng mai',
            'hoang mai',
            'thanh xuân',
            'thanh xuan',
            'long biên',
            'long bien',
            'tây hồ',
            'tay ho',
            'cầu giấy',
            'cau giay',
            'hà đông',
            'ha dong',
            'nam từ liêm',
            'nam tu liem',
            'bắc từ liêm',
            'bac tu liem',

            'hải phòng',
            'hai phong',
            'quảng ninh',
            'quang ninh',
            'hạ long',
            'ha long',
            'cẩm phả',
            'cam pha',
            'hải dương',
            'hai duong',
            'hưng yên',
            'hung yen',
            'bắc ninh',
            'bac ninh',
            'bắc giang',
            'bac giang',
            'thái nguyên',
            'thai nguyen',
            'lạng sơn',
            'lang son',
            'cao bằng',
            'cao bang',
            'hà giang',
            'ha giang',
            'lào cai',
            'lao cai',
            'sapa',
            'sa pa',
            'yên bái',
            'yen bai',
            'tuyên quang',
            'tuyen quang',
            'phú thọ',
            'phu tho',
            'việt trì',
            'viet tri',
            'vĩnh phúc',
            'vinh phuc',
            'bắc kạn',
            'bac kan',
            'thái bình',
            'thai binh',
            'hà nam',
            'ha nam',
            'nam định',
            'nam dinh',
            'ninh bình',
            'ninh binh',
            'hòa bình',
            'hoa binh',
            'sơn la',
            'son la',
            'điện biên',
            'dien bien',
            'lai châu',
            'lai chau'
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
                return 30000; // Trả về phí mặc định nếu bảng chưa tồn tại
            }

            // Lấy các quy tắc phí ship đang hoạt động, sắp xếp theo độ ưu tiên
            $shippingRules = ShippingFee::where('status', true)
                ->orderBy('priority', 'desc')
                ->get();

            // Nếu không có quy tắc nào, trả về phí mặc định
            if ($shippingRules->isEmpty()) {
                return 30000;
            }

            // Tìm quy tắc phù hợp đầu tiên
            foreach ($shippingRules as $rule) {
                // Kiểm tra khu vực - Cho phép quy tắc 'nearby' áp dụng cho cả 'nationwide'
                if ($rule->area_type === 'local' && $areaType !== 'local') {
                    continue; // Quy tắc local chỉ áp dụng cho local
                }

                if ($rule->area_type === 'nearby' && $areaType === 'local') {
                    continue; // Quy tắc nearby không áp dụng cho local
                }

                // Kiểm tra xem quy tắc có áp dụng được không
                if ($rule->isApplicable($distance, $orderValue)) {
                    return $rule->calculateFee($distance, $orderValue);
                }
            }

            // Nếu không có quy tắc nào phù hợp, trả về phí mặc định
            return 30000;
        } catch (\Exception $e) {
            // Nếu có lỗi, log và trả về phí mặc định
            \Log::error('Calculate shipping fee error: ' . $e->getMessage());
            return 30000;
        }
    }

    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
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

        return redirect()->route('checkout.payment');
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
        // Tính phí vận chuyển động từ database
        $shippingFee = $this->calculateShippingFee($subtotal, 5, $areaType);

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

        return view('checkout.payment', [
            'items' => $items,
            'addressData' => $addressData,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'total' => $total,
            'availableVouchers' => $availableVouchers,
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
        // Tính phí vận chuyển động từ database
        $shippingFee = $this->calculateShippingFee($subtotal, 5, $areaType);
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

        DB::transaction(function () use ($items, $addressData, $paymentMethod, $total, $voucherCode, $discount, $appliedDiscountId, $insuranceFee, &$order) {
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

        // Nếu thanh toán MoMo, chuyển hướng đến MoMo
        if ($paymentMethod === 'momo') {
            $momoService = new MoMoService();
            $orderInfo = "Thanh toán đơn hàng #" . $order->id;
            $result = $momoService->createPayment($order->id, $total, $orderInfo);

            if (isset($result['payUrl'])) {
                // Lưu order ID vào session để xử lý callback
                session(['momo_order_id' => $order->id]);
                return redirect($result['payUrl']);
            } else {
                return back()->withErrors(['payment' => 'Không thể kết nối đến MoMo. Vui lòng thử lại.']);
            }
        }

        // Xóa session và cart items cho COD
        session()->forget(['checkout_address', 'checkout_selected']);
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

        // Nếu yêu cầu hóa đơn, chuyển đến trang in hóa đơn
        if ($requestInvoice) {
            return redirect()->route('invoice.show', $order->id);
        }

        // Chuyển về trang chủ với thông báo thành công
        return redirect()->route('shop.index')->with('success', 'Bạn đã đặt hàng thành công!');
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
            }

            // Xóa session và cart items
            session()->forget(['checkout_address', 'checkout_selected', 'momo_order_id']);
            $cart = $this->currentCart();
            if ($cart) {
                // Xóa toàn bộ giỏ hàng sau khi thanh toán MoMo thành công
                CartItem::where('cart_id', $cart->id)->delete();
            }

            return redirect()->route('home')->with('success', 'Thanh toán MoMo thành công! Đơn hàng #' . $orderId . ' đã được xác nhận.');
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
}
