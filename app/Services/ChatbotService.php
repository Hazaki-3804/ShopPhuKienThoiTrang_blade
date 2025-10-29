<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Order;
use App\Models\ShippingFee;
use App\Models\Discount;
use App\Models\User;
use App\Models\Category;
use App\Services\ProductImageService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatbotService
{
    protected $productImageService;

    public function __construct(ProductImageService $productImageService)
    {
        $this->productImageService = $productImageService;
    }
    /**
     * Xử lý câu hỏi về danh mục sản phẩm
     */
    public function handleCategoryQuestions($message)
    {
        $categories = \App\Models\Category::all();
        
        if ($categories->isEmpty()) {
            return "Hiện tại shop chưa có danh mục sản phẩm nào. Vui lòng quay lại sau!";
        }

        $response = "Các danh mục sản phẩm tại shop Nàng Thơ:\n\n";
        foreach ($categories as $category) {
            $productCount = \App\Models\Product::where('category_id', $category->id)
                ->where('status', 1)
                ->count();
            $response .= "• {$category->name} ({$productCount} sản phẩm)\n";
        }
        
        $response .= "\nBạn muốn xem sản phẩm nào? Hãy cho mình biết nhé!";
        
        return $response;
    }

    /**
     * Xử lý câu hỏi về theo dõi đơn hàng
     */
    public function handleOrderTrackingQuestions($message)
    {
        $message = strtolower($message);
        
        // Tìm mã đơn hàng trong tin nhắn
        if (preg_match('/#([A-Z0-9]+)/', $message, $matches)) {
            $orderCode = $matches[1];
            $order = Order::where('id', $orderCode)->first();
            
            if ($order) {
                $response = "Thông tin đơn hàng #{$order->id}:\n\n";
                $response .= "Khách hàng: {$order->customer_name}\n";
                $response .= "SĐT: {$order->customer_phone}\n";
                $response .= "Địa chỉ: {$order->shipping_address}\n";
                $response .= "Tổng tiền: " . number_format($order->total_price, 0, ',', '.') . "đ\n";
                $response .= "Trạng thái: {$order->status_text}\n";
                $response .= "Ngày đặt: " . $order->created_at->format('d/m/Y H:i');
                
                return $response;
            }
            
            return "Không tìm thấy đơn hàng với mã #{$orderCode}. Vui lòng kiểm tra lại!";
        }
        
        // Nếu user đã đăng nhập, hiển thị đơn hàng gần nhất
        if (\Illuminate\Support\Facades\Auth::check()) {
            $recentOrder = Order::where('user_id', \Illuminate\Support\Facades\Auth::id())
                ->latest()
                ->first();
            
            if ($recentOrder) {
                return "Đơn hàng gần nhất của bạn:\n\n" .
                       "Mã đơn: #{$recentOrder->id}\n" .
                       "Trạng thái: {$recentOrder->status_text}\n" .
                       "Tổng tiền: " . number_format($recentOrder->total_price, 0, ',', '.') . "đ\n" .
                       "Ngày đặt: " . $recentOrder->created_at->format('d/m/Y H:i');
            }
            
            return "Bạn chưa có đơn hàng nào. Hãy mua sắm ngay!";
        }
        
        return "Để kiểm tra đơn hàng, bạn có thể:\n" .
               "• Đăng nhập tài khoản\n" .
               "• Cung cấp mã đơn hàng (VD: #A1234)\n" .
               "• Gọi hotline: 0123.456.789";
    }

    /**
     * Xử lý câu hỏi về sản phẩm
     */
    public function handleProductQuestions($message)
    {
        $message = strtolower($message);
        
        // Từ khóa sản phẩm
        $productKeywords = [
            'mắt kính' => ['mắt kính', 'kính', 'glasses'],
            'dây chuyền' => ['dây chuyền', 'vòng cổ', 'necklace'],
            'kẹp tóc' => ['kẹp tóc', 'kẹp', 'hair clip'],
            'túi xách' => ['túi xách', 'túi', 'bag'],
            'nhẫn' => ['nhẫn', 'ring'],
            'bông tai' => ['bông tai', 'khuyên tai', 'earring'],
            'vòng tay' => ['vòng tay', 'bracelet'],
            'móng tay giả' => ['móng tay giả', 'nail']
        ];

        $foundProduct = null;
        $productType = null;

        // Tìm loại sản phẩm được nhắc đến
        foreach ($productKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $foundProduct = $type;
                    $productType = $keyword;
                    break 2;
                }
            }
        }

        if ($foundProduct) {
            return $this->getProductInfo($foundProduct, $message);
        }
        
        // Tìm kiếm thông minh nếu không tìm thấy từ khóa cụ thể
        $smartProducts = $this->smartProductSearch($message);
        if ($smartProducts->isNotEmpty()) {
            return $this->generateSmartProductResponse($smartProducts, $message);
        }

        return null;
    }

    /**
     * Lấy thông tin sản phẩm
     */
    private function getProductInfo($productType, $message)
    {
        // Tìm sản phẩm theo tên
        $products = Product::with(['category', 'product_images', 'reviews'])
            ->where('name', 'LIKE', "%{$productType}%")
            ->where('status', 1)
            ->inRandomOrder()
            ->take(3)
            ->get();

        if ($products->isEmpty()) {
            return "Xin lỗi, hiện tại shop chưa có sản phẩm {$productType} nào. Bạn có thể xem các sản phẩm khác nhé! 😊";
        }

        $response = "Dưới đây là {$products->count()} mẫu {$productType} mà mình tìm thấy:\n";
        
        foreach ($products as $product) {
            $response .= "<b>Tên sản phẩm:</b> {$product->name}\n";
            $response .= "<b>Giá:</b> " . number_format($product->price, 0, ',', '.') . "đ\n";
            $response .= "<b>Còn lại:</b> {$product->stock} sản phẩm\n";
            
            // if ($product->description) {
            //     $response .= "<b>Mô tả:</b> " . substr($product->description, 0, 100) . "...\n";
            // }
            
            // Đánh giá trung bình
            $avgRating = $product->reviews->avg('rating');
            if ($avgRating) {
                $response .= "Đánh giá: " . number_format($avgRating, 1) . "/5\n";
            }
        }
        $response .= "Để biết thêm thông tin về sản phẩm, bạn vui lòng nhấn vào nút sản phẩm hoặc thanh tìm kiếm để xem nhiều mẫu hơn.\n";


        // Xử lý câu hỏi cụ thể về hình ảnh
        if (str_contains($message, 'hình') || str_contains($message, 'ảnh') || str_contains($message, 'cận cảnh')) {
            if ($products->isNotEmpty()) {
                $firstProduct = $products->first();
                $imageHtml = $this->productImageService->generateImageHtml($firstProduct->id, $firstProduct->name);
                $response .= "\n" . $imageHtml;
                $response .= "\nXem chi tiết: " . $this->productImageService->generateProductLink($firstProduct->id);
            } else {
                $response .= "\nBạn muốn xem hình ảnh sản phẩm nào? Vui lòng cho mình biết tên sản phẩm cụ thể nhé! 📸";
            }
        }

        if (str_contains($message, 'chất liệu') || str_contains($message, 'làm bằng gì')) {
            $response .= "\nVề chất liệu: Tất cả sản phẩm của shop đều được làm từ chất liệu cao cấp, an toàn cho da.";
        }

        if (str_contains($message, 'dễ gãy') || str_contains($message, 'bền')) {
            $response .= "\nVề độ bền: Sản phẩm được kiểm tra chất lượng kỹ càng trước khi giao hàng.";
        }

        return $response;
    }

    /**
     * Xử lý câu hỏi về giá và tồn kho
     */
    public function handlePriceStockQuestions($message)
    {
        $message = strtolower($message);
        
        if (str_contains($message, 'giá') || str_contains($message, 'bao nhiêu')) {
            // Lấy sản phẩm gần đây nhất từ session hoặc context
            $recentProducts = session('recent_products', []);
            
            if (!empty($recentProducts)) {
                $product = Product::find($recentProducts[0]);
                if ($product) {
                    $response = "{$product->name}\n";
                    $response .= "Giá: " . number_format($product->price, 0, ',', '.') . "đ\n";
                    $response .= "Còn lại: {$product->stock} sản phẩm\n";
                    
                    // Hiển thị hình ảnh chính
                    $mainImage = $this->productImageService->getMainImage($product->id);
                    $response .= "Hình ảnh: {$mainImage}\n";
                    
                    // Kiểm tra giảm giá
                    $discount = $this->getActiveDiscount($product->id);
                    if ($discount) {
                        $discountedPrice = $this->calculateDiscountedPrice($product->price, $discount);
                        $response .= "Giá ưu đãi: " . number_format($discountedPrice, 0, ',', '.') . "đ\n";
                        $response .= "Mã giảm giá: **{$discount->code}**\n";
                    }
                    
                    return $response;
                }
            }
        }

        if (str_contains($message, 'còn hàng') || str_contains($message, 'tồn kho')) {
            return "Để kiểm tra tồn kho chính xác, bạn vui lòng cho mình biết sản phẩm cụ thể nhé!";
        }

        if (str_contains($message, 'ship') || str_contains($message, 'giao hàng') || str_contains($message, 'freeship')|| str_contains($message, 'phí vận chuyển')) {
            return "Phí giao hàng:\n\n" .
                   "Nội thành Vĩnh Long: 15.000đ\n" .
                   "Lân cận (< 50km): 25.000đ\n" .
                   "Toàn quốc: 35.000đ\n\n" .
                   "MIỄN PHÍ SHIP cho đơn hàng từ 500.000đ!\n" .
                   "Thời gian giao: 1-7 ngày tùy khu vực";
        }

        if (str_contains($message, 'combo') || str_contains($message, 'mua nhiều')) {
            return "Ưu đãi mua nhiều:\n\n" .
                   "Mua 2 sản phẩm: Giảm 5%\n" .
                   "Mua 3 sản phẩm: Giảm 10% + Freeship\n" .
                   "Mua 5 sản phẩm: Giảm 15% + Freeship + Quà tặng\n\n" .
                   "Lưu ý: Áp dụng tự động khi thanh toán!";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi về giao hàng
     */
    public function handleShippingQuestions($message)
    {
        $message = strtolower($message);

        if (str_contains($message, 'giao hàng') || str_contains($message, 'ship')||str_contains($message, 'phí ship')||str_contains($message, 'phí vận chuyển')) {
            if (str_contains($message, 'bao lâu') || str_contains($message, 'thời gian')) {
                return "Thời gian giao hàng:\n- Nội thành Vĩnh Long: 1-2 ngày\n- Lân cận: 2-3 ngày\n- Toàn quốc: 3-7 ngày\n\nShop sẽ gọi xác nhận trước khi giao hàng nhé!";
            }

            if (str_contains($message, 'phí') || str_contains($message, 'giá')) {
                $shippingFees = ShippingFee::where('status', true)->orderBy('priority')->get();
                $response = "Bảng phí giao hàng:\n";
                
                foreach ($shippingFees as $fee) {
                    $response .= " {$fee->getAreaTypeLabel()}: ";
                    if ($fee->is_free_shipping) {
                        $response .= "Miễn phí\n";
                    } else {
                        $response .= number_format($fee->base_fee, 0, ',', '.') . "đ\n";
                    }
                }
                
                $response .= "\nMiễn phí ship cho đơn hàng từ 500.000đ!";
                return $response;
            }
        }

        // Kiểm tra đơn hàng với mã cụ thể
        if (str_contains($message, 'kiểm tra đơn') || str_contains($message, 'đơn hàng') || preg_match('/#[A-Z0-9]+/', $message)) {
            // if(Auth::check()){
            // // Tìm mã đơn hàng trong tin nhắn
            // if (preg_match('/#([A-Z0-9]+)/', $message, $matches)) {
            //     $orderCode = $matches[1];
            //     $order = Order::where('id', $orderCode)->first();
                
            //     if ($order) {
            //         $response = "Thông tin đơn hàng #{$order->id}:\n";
            //         $response .= "Khách hàng: {$order->customer_name}\n";
            //         $response .= "SĐT: {$order->customer_phone}\n";
            //         $response .= "Địa chỉ: {$order->shipping_address}\n";
            //         $response .= " Tổng tiền: " . number_format($order->total_price, 0, ',', '.') . "đ\n";
            //         $response .= "Trạng thái: {$order->status_text}\n";
            //         $response .= "Ngày đặt: " . $order->created_at->format('d/m/Y H:i');
                    
            //         return $response;
            //     }
                
            //     return "Không tìm thấy đơn hàng với mã #{$orderCode}. Vui lòng kiểm tra lại mã đơn hàng!";
            // }
            // }
            
            if (Auth::check()) {
                $recentOrder = Order::where('user_id', Auth::id())
                    ->latest()
                    ->first();
                
                if ($recentOrder) {
                    return "Đơn hàng gần nhất:\n" .
                           "Mã đơn: #{$recentOrder->id}\n" .
                           "Trạng thái: {$recentOrder->status_text}\n" .
                           "Tổng tiền: " . number_format($recentOrder->total_price, 0, ',', '.') . "đ\n" .
                           "Ngày đặt: " . $recentOrder->created_at->format('d/m/Y H:i')."\n".
                           "Để biết thêm thông tin của các đơn hàng bạn có thể truy cập vào <a style='color:orange;' href=".route('users.order.index').">đây</a>";
                }
            }
            
            return "Để kiểm tra đơn hàng, bạn có thể:\n" .
                   "Đăng nhập tài khoản\n" .
                   "Cung cấp mã đơn hàng (VD: #A1234)\n" .
                   "Gọi hotline: 0779089258";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi về thanh toán
     */
    public function handlePaymentQuestions($message)
    {
        $message = strtolower($message);

        if (str_contains($message, 'thanh toán') || str_contains($message, 'payment')) {
            $response = "Các hình thức thanh toán:\n";
            $response .= "COD (Thanh toán khi nhận hàng)\n";
            $response .= "Chuyển khoản ngân hàng\n";
            $response .= "Ví điện tử: Momo, ZaloPay\n";
            $response .= "Thẻ tín dụng/ghi nợ\n";
            $response .= "Tất cả đều an toàn và bảo mật!";
            
            return $response;
        }

        if (str_contains($message, 'momo')) {
            return "Thanh toán Momo: Có hỗ trợ! Bạn có thể thanh toán qua ví Momo rất tiện lợi và nhanh chóng. ";
        }

        if (str_contains($message, 'cod')) {
            return "COD (Cash on Delivery): Có nhận! Bạn thanh toán khi nhận hàng, rất an toàn và tiện lợi. 💵";
        }

        if (str_contains($message, 'hóa đơn')) {
            return "Hóa đơn: Shop xuất hóa đơn VAT cho tất cả đơn hàng. Bạn vui lòng cung cấp thông tin công ty khi đặt hàng nhé!";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi về đổi trả và bảo hành
     */
    public function handleReturnWarrantyQuestions($message)
    {
        $message = strtolower($message);

        if (str_contains($message, 'đổi') || str_contains($message, 'trả') || str_contains($message, 'return')) {
            $response = "Chính sách đổi trả:\n";
            $response .= "Thời gian: 7 ngày kể từ khi nhận hàng\n";
            $response .= "Điều kiện:\n";
            $response .= "   - Sản phẩm còn nguyên vẹn, chưa sử dụng\n";
            $response .= "   - Còn đầy đủ bao bì, nhãn mác\n";
            $response .= "   - Có hóa đơn mua hàng\n\n";
            $response .= "Phí ship đổi trả: Shop hỗ trợ nếu lỗi do shop\n";
            $response .= "Liên hệ: Gọi hotline để được hỗ trợ nhanh nhất!";
            
            return $response;
        }

        if (str_contains($message, 'bảo hành') || str_contains($message, 'warranty')) {
            return "Chính sách bảo hành:\n\n" .
                   "Phụ kiện kim loại: 3 tháng\n" .
                   "Mắt kính: 6 tháng\n" .
                   "Sản phẩm điện tử: 12 tháng\n\n" .
                   "Bảo hành miễn phí lỗi kỹ thuật, không bao gồm hư hỏng do sử dụng sai cách.";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi về khuyến mãi
     */
    public function handlePromotionQuestions($message)
    {
        $message = strtolower($message);

        if (str_contains($message, 'giảm giá') || str_contains($message, 'khuyến mãi') || str_contains($message, 'sale')) {
            $activeDiscounts = Discount::where('status', 1)
                ->where('start_date', '<=', Carbon::now())
                ->where('end_date', '>=', Carbon::now())
                ->where('used_quantity', '<', 'quantity')
                ->take(5)
                ->get();

            if ($activeDiscounts->isEmpty()) {
                return "Hiện tại chưa có chương trình khuyến mãi nào đang diễn ra. Bạn theo dõi fanpage để cập nhật ưu đãi mới nhất nhé! 📢";
            }

            $response = "Khuyến mãi đang diễn ra:\n";
            
            foreach ($activeDiscounts as $discount) {
                $response .= "{$discount->code}\n";
                if ($discount->description) {
                    $response .= " {$discount->description}\n";
                }
                
                if ($discount->discount_type === 'percentage') {
                    $response .= "Giảm {$discount->discount_value}%\n";
                } else {
                    $response .= "Giảm " . number_format($discount->discount_value, 0, ',', '.') . "đ\n";
                }
                
                $response .= "Hết hạn: " . $discount->end_date->format('d/m/Y') . "\n";
            }

            return $response;
        }

        if (str_contains($message, 'mã giảm giá') || str_contains($message, 'voucher')) {
            return "Cách sử dụng mã giảm giá:\n\n" .
                   "Thêm sản phẩm vào giỏ hàng\n" .
                   "Nhập mã vào ô 'Mã giảm giá'\n" .
                   "Nhấn 'Áp dụng'\n" .
                   "Hoàn tất thanh toán\n\n" .
                   "Lưu ý: Mỗi mã chỉ sử dụng 1 lần/khách hàng!";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi tương tác và tư vấn
     */
    public function handleConsultationQuestions($message)
    {
        $message = strtolower($message);

        // Tư vấn theo khuôn mặt
        if (str_contains($message, 'mặt tròn') || str_contains($message, 'khuôn mặt') || str_contains($message, 'phù hợp')) {
            $response = "Tư vấn theo khuôn mặt: \n";
            
            if (str_contains($message, 'tròn')) {
                $response .= "Mặt tròn của bạn phù hợp với:\n";
                $response .= " Kính gọng vuông, chữ nhật\n";
                $response .= " Kính aviator, cat-eye\n";
                $response .= " Bông tai dài, hình giọt nước\n";
                $response .= " Dây chuyền dài, mặt pendant\n\n";
                
                // Gợi ý sản phẩm cụ thể
                $products = Product::where('name', 'LIKE', '%kính%')
                    ->where('status', 1)
                    ->take(2)
                    ->get();
                    
                if ($products->isNotEmpty()) {
                    $response .= "Gợi ý sản phẩm:\n";
                    foreach ($products as $product) {
                        $response .= "• {$product->name} - " . number_format($product->price, 0, ',', '.') . "đ\n";
                    }
                }
            } else {
                $response .= "Mặt tròn: Kính vuông, chữ nhật\n";
                $response .= "Mặt vuông: Kính tròn, oval\n";
                $response .= "Mặt dài: Kính to, gọng dày\n";
                $response .= "Mặt trái tim: Kính mắt mèo, aviator\n\n";
                $response .= "Bạn cho mình biết khuôn mặt để tư vấn cụ thể hơn nhé!";
            }
            
            return $response;
        }

        // Tư vấn quà tặng với ngân sách
        if (str_contains($message, 'tặng') || str_contains($message, 'quà')) {
            $response = "Gợi ý quà tặng:\n\n";
            
            // Phân tích ngân sách từ tin nhắn
            if (preg_match('/([0-9]+).*(?:k|000|triệu)/', $message, $matches)) {
                $budget = intval($matches[1]);
                if (str_contains($message, 'k') || str_contains($message, '000')) {
                    $budget *= 1000;
                } elseif (str_contains($message, 'triệu')) {
                    $budget *= 1000000;
                }
                
                $products = Product::where('price', '<=', $budget)
                    ->where('status', 1)
                    ->orderBy('price', 'desc')
                    ->take(3)
                    ->get();
                    
                $response .= " **Trong ngân sách " . number_format($budget, 0, ',', '.') . "đ:**\n";
                foreach ($products as $product) {
                    $response .= "• {$product->name} - " . number_format($product->price, 0, ',', '.') . "đ\n";
                }
                $response .= "\n";
            }
            
            $response .= "Theo đối tượng:\n";
            $response .= "Bạn gái: Dây chuyền, bông tai, vòng tay\n";
            $response .= "Mẹ/Chị: Túi xách, kính thời trang\n";
            $response .= "Em gái: Kẹp tóc, nhẫn xinh\n";
            $response .= "Sinh nhật: Set phụ kiện combo\n\n";
            
            if (!str_contains($message, 'k') && !str_contains($message, '000') && !str_contains($message, 'triệu')) {
                $response .= "Cho mình biết ngân sách để tư vấn phù hợp nhé!**";
            }
            
            return $response;
        }

        // Tư vấn theo màu sắc
        if (str_contains($message, 'màu hồng') || str_contains($message, 'màu')) {
            return "Tư vấn theo màu sắc:\n" .
                   "Màu hồng: Dây chuyền hồng, bông tai hồng pastel\n" .
                   "Màu đen: Kính đen, túi đen sang trọng\n" .
                   "Màu vàng: Nhẫn vàng, vòng tay vàng\n" .
                   "Màu trắng: Phụ kiện ngọc trai, bạc\n\n" .
                   "Bạn thích màu gì nhất?";
        }

        return null;
    }

    /**
     * Xử lý câu hỏi về tài khoản và hỗ trợ
     */
    public function handleAccountSupportQuestions($message)
    {
        $message = strtolower($message);
        if(str_contains($message, 'đăng ký')){
            return "Để có thể đăng ký vào hệ thống của chúng tôi, bạn cần:\n" .
                   "\t1. Vào trang đăng ký\n" .
                   "\t2. Nhấn 'Đăng ký'\n" .
                   "\t3. Nhập email đã đăng ký\n" .
                   "\t4. Nhập mật khẩu\n" .
                   "\t5. Nhấn nút 'Đăng ký'\n".
                   "Sau đó bạn sẽ nhận được email xác nhận\n";
        }
        if(str_contains($message, 'đăng nhập')){
            return "Để có thể đăng nhập vào hệ thống của chúng tôi, bạn cần:\n" .
                   "\t1. Vào trang đăng nhập\n" .
                   "\t2. Nhấn 'Đăng nhập'\n" .
                   "\t3. Nhập email đã đăng ký\n" .
                   "\t4. Nhập mật khẩu\n" .
                   "\t5. Nhấn nút 'Đăng nhập'\n".
                   "Hoặc bạn cũng có thể đăng nhập qua Google, Facebook\n";
        }

        if (str_contains($message, 'quên mật khẩu') || str_contains($message, 'reset password')) {
            return "Nếu bạn lỡ quên mật khẩu, bạn đừng lo lắng nhé!\n" .
                   "Bạn chỉ cần:\n" .
                   "\t1. Vào trang đăng nhập\n" .
                   "\t2. Nhấn 'Quên mật khẩu?'\n" .
                   "\t3. Nhập email đã đăng ký\n" .
                   "\t4. Nhấn nút 'Quên mật khẩu?'\n" .
                   "\t5. Kiểm tra email và làm theo hướng dẫn\n" .
                   "Nếu không thấy email, hãy kiểm tra thư mục spam nhé!";
        }

        if (str_contains($message, 'theo dõi đơn hàng') || str_contains($message, 'track order')) {
            if (Auth::check()) {
                $userOrders = Order::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->take(3)
                    ->get();
                    
                $response = "Đơn hàng của bạn:\n\n";
                
                if ($userOrders->isNotEmpty()) {
                    foreach ($userOrders as $order) {
                        $response .= "Đơn #{$order->id}\n";
                        $response .= "Trạng thái: {$order->status_text}\n";
                        $response .= "Tổng tiền: " . number_format($order->total_price, 0, ',', '.') . "đ\n";
                        $response .= "Ngày đặt: " . $order->created_at->format('d/m/Y') . "\n\n";
                    }
                } else {
                    $response .= "Bạn chưa có đơn hàng nào.\n\n";
                }
                
                $response .= "Xem chi tiết tại: Tài khoản > Đơn hàng của tui\n";
                $response .= "Hoặc nhắn mã đơn hàng để kiểm tra!";
                
                return $response;
            }
            
            return "Để theo dõi đơn hàng:\n" .
                   "Đăng nhập tài khoản\n" .
                   "Cung cấp mã đơn hàng (VD: #A1234)\n" .
                   "Gọi hotline: 0123.456.789";
        }

        if (str_contains($message, 'đổi địa chỉ') || str_contains($message, 'change address')) {
            return "Đổi địa chỉ nhận hàng:\n" .
                   "Trước khi giao: Liên hệ hotline ngay\n" .
                   "Trong tài khoản: Vào 'Thông tin cá nhân' để cập nhật\n" .
                   "Sau khi giao: Không thể thay đổi\n\n" .
                   "Hotline: 0123.456.789";
        }

        if (str_contains($message, 'nhân viên') || str_contains($message, 'hỗ trợ') || str_contains($message, 'support') || str_contains($message, 'liên hệ')) {
            $currentHour = Carbon::now()->hour;
            $isWorkingHours = $currentHour >= 8 && $currentHour <= 22;
            
            $response = "Hỗ trợ khách hàng:\n";
            $response .= "Hotline: 0123.456.789\n";
            
            if ($isWorkingHours) {
                $response .= "Đang online - Sẵn sàng hỗ trợ!\n";
            } else {
                $response .= "Ngoài giờ làm việc - Sẽ phản hồi sớm nhất!\n";
            }
            
            $response .= "\nCác kênh liên hệ:\n";
            $response .= "  Chat trực tiếp: Ngay tại đây\n";
            $response .= " Email: support@nangthoshop.com\n";
            $response .= " Facebook: fb.com/nangthoshop\n";
            $response .= " Zalo: 0123.456.789\n";
            $response .= "Giờ làm việc: 8h-22h hàng ngày\n";
            $response .= "Phản hồi: Trong vòng 5 phút (giờ hành chính)";
            
            return $response;
        }

        return null;
    }

    /**
     * Lấy discount đang hoạt động cho sản phẩm
     */
    private function getActiveDiscount($productId)
    {
        return Discount::whereHas('products', function($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->where('status', 1)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->where('used_quantity', '<', 'quantity')
            ->first();
    }

    /**
     * Tính giá sau khi giảm
     */
    private function calculateDiscountedPrice($originalPrice, $discount)
    {
        if ($discount->discount_type === 'percentage') {
            return $originalPrice * (1 - $discount->discount_value / 100);
        }
        
        return max(0, $originalPrice - $discount->discount_value);
    }

    /**
     * Tìm kiếm sản phẩm thông minh
     */
    public function smartProductSearch($query)
    {
        $query = strtolower($query);
        
        // Từ đồng nghĩa
        $synonyms = [
            'kính' => ['mắt kính', 'glasses', 'kính râm'],
            'túi' => ['túi xách', 'bag', 'handbag'],
            'dây' => ['dây chuyền', 'vòng cổ', 'necklace'],
            'bông' => ['bông tai', 'khuyên tai', 'earring'],
            'vòng' => ['vòng tay', 'bracelet', 'lắc tay'],
            'nhẫn' => ['ring', 'nhẫn đôi'],
            'kẹp' => ['kẹp tóc', 'hair clip', 'cài tóc']
        ];
        
        $searchTerms = [$query];
        
        // Mở rộng từ khóa tìm kiếm
        foreach ($synonyms as $key => $values) {
            if (str_contains($query, $key) || in_array($query, $values)) {
                $searchTerms = array_merge($searchTerms, $values);
                break;
            }
        }
        
        // Tìm kiếm sản phẩm
        $products = Product::where('status', 1)
            ->where(function($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $q->orWhere('name', 'LIKE', "%{$term}%")
                      ->orWhere('description', 'LIKE', "%{$term}%");
                }
            })
            ->with(['category', 'product_images', 'reviews'])
            ->take(5)
            ->get();
            
        return $products;
    }

    /**
     * Tạo response thông minh cho sản phẩm
     */
    public function generateSmartProductResponse($products, $originalQuery)
    {
        if ($products->isEmpty()) {
            return "Không tìm thấy sản phẩm phù hợp với '{$originalQuery}'. Bạn có thể thử:\n" .
                   " Tìm theo danh mục\n" .
                   " Gọi hotline: 0123.456.789\n" .
                   " Chat trực tiếp với nhân viên";
        }
        
        $response = "Tìm thấy {$products->count()} sản phẩm phù hợp với '{$originalQuery}':\n\n";
        
        foreach ($products as $index => $product) {
            $response .= "{$product->name}\n";
            $response .= "Giá: " . number_format($product->price, 0, ',', '.') . "đ\n";
            $response .= "Còn: {$product->stock} sản phẩm\n";
            
            // Đánh giá
            $avgRating = $product->reviews->avg('rating');
            if ($avgRating) {
                $stars = str_repeat('⭐', round($avgRating));
                $response .= " {$stars} (" . number_format($avgRating, 1) . "/5)\n";
            }
            
            // Link sản phẩm
            $response .= "Xem chi tiết: " . $this->productImageService->generateProductLink($product->id) . "\n\n";
        }
        
        $response .= "Cần tư vấn thêm? Hỏi mình về chất liệu, kích thước, hoặc cách phối đồ nhé!";
        
        return $response;
    }
}
