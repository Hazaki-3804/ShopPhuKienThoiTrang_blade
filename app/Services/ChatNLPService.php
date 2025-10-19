<?php

namespace App\Services;

class ChatNLPService
{
    /**
     * Phân loại intent và trích xuất entity cơ bản từ câu người dùng.
     * V1: Heuristic + có thể nâng cấp dùng LLM sau.
     */
    public function parse(string $message): array
    {
        $m = trim($message);
        $ml = function_exists('mb_strtolower') ? mb_strtolower($m, 'UTF-8') : strtolower($m);

        $intent = 'small_talk';
        $entities = [
            'product_query' => null,
            'order_code' => null,
            'location' => null,
            'budget' => null,
            'follow_up' => false,
            'category_hint' => null,
        ];

        // Order code like #A1234 or 1234
        if (preg_match('/#?([A-Za-z0-9]{4,})/u', $m, $mm) && (str_contains($ml, 'đơn') || str_contains($ml, 'order') || str_contains($ml, 'mã'))) {
            $intent = 'order_tracking';
            $entities['order_code'] = $mm[1];
        }

        // Budget
        if (preg_match('/(\d+)(\s*)(k|000|triệu)/ui', $ml, $bm)) {
            $intent = 'consultation';
            $num = (int)$bm[1];
            $unit = $bm[3];
            $entities['budget'] = $unit === 'triệu' ? $num * 1000000 : $num * 1000;
        }

        // Product-related if asks about xem, tư vấn, sản phẩm, kính, túi, v.v.
        if ($intent === 'small_talk') {
            $productHints = ['sản phẩm', 'mắt kính', 'kính', 'túi', 'túi xách', 'dây chuyền', 'kẹp tóc', 'nhẫn', 'bông tai', 'vòng tay'];
            foreach ($productHints as $h) {
                if (str_contains($ml, $h)) {
                    $intent = (str_contains($ml, 'giá') || str_contains($ml, 'bao nhiêu') || str_contains($ml, 'còn hàng')) ? 'price_stock' : 'product_info';
                    $entities['product_query'] = $m; // full text for retrieval
                    break;
                }
            }
        }

        // Category hint extraction (synonyms)
        $categoryMap = [
            'kính' => ['kính', 'mắt kính', 'kinh', 'mat kinh'],
            'dây chuyền' => ['dây chuyền', 'vòng cổ', 'day chuyen', 'vong co'],
            'túi xách' => ['túi xách', 'túi', 'balo mini', 'tui xach', 'tui'],
            'kẹp tóc' => ['kẹp tóc', 'kep toc'],
            'nhẫn' => ['nhẫn', 'nhan'],
            'bông tai' => ['bông tai', 'khuyên tai', 'bong tai', 'khuyen tai'],
            'vòng tay' => ['vòng tay', 'vong tay'],
        ];
        foreach ($categoryMap as $key => $syns) {
            foreach ($syns as $syn) {
                if (str_contains($ml, $syn)) {
                    $entities['category_hint'] = $key;
                    break 2;
                }
            }
        }

        // Categories / Auth / Order support / Shipping / Payment / Return / Promotion
        if ($intent === 'small_talk') {
            if (str_contains($ml, 'danh mục') || str_contains($ml, 'loại sản phẩm') || str_contains($ml, 'category')) {
                $intent = 'categories';
            }
            if (str_contains($ml, 'đăng nhập') || str_contains($ml, 'đăng ký') || str_contains($ml, 'login') || str_contains($ml, 'register')) {
                $intent = 'auth';
            }
            if (str_contains($ml, 'đặt hàng') || str_contains($ml, 'hỗ trợ đặt hàng') || str_contains($ml, 'mua hàng') || str_contains($ml, 'thanh toán') || str_contains($ml, 'checkout') || str_contains($ml, 'giỏ hàng')) {
                $intent = 'order_support';
            }
            if (str_contains($ml, 'giao hàng') || str_contains($ml, 'ship') || str_contains($ml, 'phí ship')) $intent = 'shipping';
            if (str_contains($ml, 'thanh toán') || str_contains($ml, 'momo') || str_contains($ml, 'cod')) $intent = 'payment';
            if (str_contains($ml, 'đổi') || str_contains($ml, 'trả') || str_contains($ml, 'bảo hành')) $intent = 'return_warranty';
            if (str_contains($ml, 'khuyến mãi') || str_contains($ml, 'giảm giá') || str_contains($ml, 'sale') || str_contains($ml, 'voucher')) $intent = 'promotion';
            if (str_contains($ml, 'đăng nhập') || str_contains($ml, 'tài khoản') || str_contains($ml, 'quên mật khẩu')) $intent = 'account_support';
            if (str_contains($ml, 'tư vấn')) $intent = 'consultation';
        }

        // Follow-up detection: người dùng ám chỉ nội dung trước (mơ hồ)
        $followPhrases = [
            'cái này', 'loại đó', 'mẫu này', 'mẫu đó', 'vài mẫu', 'mẫu đi',
            'cho tôi xem vài mẫu', 'có mẫu nào', 'gợi ý vài mẫu', 'xem thêm mẫu'
        ];
        foreach ($followPhrases as $fp) {
            if (str_contains($ml, $fp)) {
                $entities['follow_up'] = true;
                break;
            }
        }

        // Greeting detection
        if ($intent === 'small_talk' && preg_match('/\b(xin chào|chào|hello|hi)\b/ui', $ml)) {
            $intent = 'greeting';
        }

        return compact('intent', 'entities');
    }
}
