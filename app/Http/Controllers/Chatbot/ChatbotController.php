<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Models\Category;
use App\Services\ChatbotService;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }
    public function chat(Request $request)
    {
        // Validate request
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $originalMessage = $request->input('message');
        $userMessage = function_exists('mb_strtolower')
            ? mb_strtolower(trim($originalMessage), 'UTF-8')
            : strtolower(trim($originalMessage));
  Log::info('Encoding check:', [
                'encoding' => mb_detect_encoding($userMessage),
                'message' => $userMessage,
            ]);
        try {
            // Xử lý các câu hỏi chuyên biệt trước
            $specialResponse = $this->handleSpecialQuestions($userMessage, $originalMessage);
            if ($specialResponse) {
                return $specialResponse;
            }
            // Lấy API key từ config
            $apiKey = Config::get('services.gemini.api_key');
            if (!$apiKey) {
                Log::error('Gemini API key không được cấu hình');
                return response()->json(
                    ['error' => 'Khóa API không được cấu hình'],
                    500,
                    [],
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                );
            }

            // Từ khóa liên quan được mở rộng
            $relevantKeywords = [
                'xin chào', 'hello', 'hi',
                'phụ kiện', 'thời trang', 'sản phẩm', 'danh mục',
                'giá', 'bao nhiêu', 'tiền', 'cost', 'price',
                'mua', 'đặt hàng', 'order', 'buy',
                'đăng ký', 'đăng nhập', 'tài khoản', 'account',
                'giỏ hàng', 'cart', 'khuyến mãi', 'giảm giá', 'sale',
                'chính sách', 'policy', 'giao hàng', 'ship', 'delivery',
                'trả hàng', 'đổi', 'return', 'exchange',
                'liên hệ', 'contact', 'hỗ trợ', 'support',
                'mắt kính', 'kính', 'dây chuyền', 'kẹp tóc',
                'túi xách', 'nhẫn', 'bông tai', 'vòng tay',
                'thanh toán', 'payment', 'momo', 'cod',
                'bảo hành', 'warranty', 'tư vấn', 'consult'
            ];
            // Kiểm tra câu hỏi có liên quan không
            $isRelevant = false;
            foreach ($relevantKeywords as $keyword) {
                if (str_contains($userMessage, $keyword)) {
                    $isRelevant = true;
                    break;
                }
            }
            $categories = Category::pluck('name')->toArray();
            $products = Product::take(5)->get(['name', 'price'])->toArray();
            $categoryList = count($categories) > 0 ? $categories : [];
            $productList = count($products) > 0 ? collect($products)->map(function ($product) {
                return "{$product['name']} - Giá: " . number_format($product['price'], 0, ',', '.') . " VNĐ";
            })->implode("\n") : 'Chưa có sản phẩm nào.';
            // Convert category list array to a comma-separated string for system prompt
            $categoryNamesStr = is_array($categoryList) ? implode(', ', $categoryList) : (string)$categoryList;
            // Sử dụng fallback cho Gemini API nếu không có response từ service
            if (!$isRelevant) {
                return response()->json(
                    [
                        'message' => 'Xin lỗi, mình chỉ trả lời các câu hỏi liên quan đến phụ kiện thời trang, sản phẩm, hoặc dịch vụ của shop. Bạn muốn hỏi về sản phẩm hay dịch vụ gì? 😊',
                        'links' => []
                    ],
                    200,
                    [],
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                );
            }

            // Prompt cho Gemini API
            $prompt = "Bạn là một trợ lý AI cho một website bán phụ kiện thời trang. Hãy trả lời câu hỏi sau bằng tiếng Việt, tự nhiên, thân thiện, ngắn gọn và chỉ dựa trên thông tin liên quan đến phụ kiện thời trang, sản phẩm, hoặc dịch vụ của website: $userMessage";
            $systemInstruction = "
            Bạn là một trợ lý AI tên là **Mia**, chuyên về tư vấn **phụ kiện thời trang** cho website.
            Nguyên tắc trả lời:
            1. Luôn nói bằng **tiếng Việt** với giọng điệu **thân thiện, chuyên nghiệp**.
            2. Phản hồi **cực kỳ ngắn gọn** và đi thẳng vào vấn đề.
            3. Không trả lời các câu hỏi ngoài lề (toán học, tin tức, lịch sử,...). Nếu bị hỏi, hãy lịch sự từ chối và mời khách hàng hỏi về sản phẩm.
            4. Thông tin về cửa hàng:
            - Tên cửa hàng: **Nàng Thơ**.
            - Sản phẩm chính: **".$categoryNamesStr."**.
            - Chính sách giao hàng: **Miễn phí vận chuyển cho đơn hàng trên 500.000 VNĐ**.
            - Chính sách đổi/trả: **Đổi trả trong 7 ngày** nếu sản phẩm lỗi.
            ";
            // Ensure proper UTF-8 encoding for the request
            $ensureUtf8 = function ($text) {
                if (!is_string($text)) {
                    $text = (string)$text;
                }
                
                // Remove any BOM and invalid UTF-8 characters
                $text = preg_replace('/[\x00-\x1F\x7F\x80-\x9F\x{FEFF}]/u', '', $text);
                
                // Convert to UTF-8 if not already
                if (!mb_check_encoding($text, 'UTF-8')) {
                    $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, mb_detect_order(), true));
                }
                
                // Normalize line endings and trim
                return trim(preg_replace('/\R+/', ' ', $text));
            };

            // Prepare the request payload
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $ensureUtf8($prompt)]
                        ]
                    ]
                ],
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $ensureUtf8($systemInstruction)]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 150,
                    'temperature' => 0.7,
                ],
            ];

            // Encode to JSON with error handling
            $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            
            if ($jsonBody === false) {
                throw new \RuntimeException('Failed to encode request data to JSON: ' . json_last_error_msg());
            }

            // Make the API request
            $response = Http::withOptions([
                'verify' => false, // Only if you're having SSL issues
                'timeout' => 30,
            ])->withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->withBody($jsonBody, 'application/json')
            ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}");

            // Kiểm tra lỗi API
            if ($response->failed()) {
                Log::error('Yêu cầu Gemini API thất bại', [
                    'status' => $response->status(),
                    'response' => isset($ensureUtf8) ? $ensureUtf8($response->body()) : $response->body(),
                    'user_message' => isset($ensureUtf8) ? $ensureUtf8($userMessage) : $userMessage,
                ]);
                return response()->json(
                    ['error' => 'Xin lỗi, có lỗi xảy ra khi xử lý yêu cầu của bạn. Vui lòng thử lại sau.', 'links' => []],
                    500,
                    [],
                    JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
                );
            }

            // Lấy phản hồi
            $responseData = $response->json();
            if ($responseData === null) {
                $raw = $response->body();
                $responseData = json_decode($raw, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $botMessage = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, có lỗi xảy ra khi xử lý yêu cầu của bạn. Vui lòng thử lại sau.';
            if (isset($ensureUtf8)) {
                $botMessage = $ensureUtf8($botMessage);
            }

            // Lưu lịch sử
            $messages = session('chat_messages', []);
            $messages[] = ['role' => 'user', 'content' => isset($ensureUtf8) ? $ensureUtf8($userMessage) : $userMessage];
            $messages[] = ['role' => 'bot', 'content' => $botMessage];
            session(['chat_messages' => $messages]);

            return response()->json(
                ['message' => $botMessage, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Exception $e) {
            Log::error('Lỗi khi giao tiếp với Gemini API', [
                'exception' => isset($ensureUtf8) ? $ensureUtf8($e->getMessage()) : $e->getMessage(),
                'user_message' => isset($ensureUtf8) ? $ensureUtf8($userMessage) : $userMessage,
            ]);
            return response()->json(
                ['error' => 'Có lỗi xảy ra khi xử lý yêu cầu của bạn', 'links' => []],
                500,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }

    /**
     * Xử lý các câu hỏi chuyên biệt bằng ChatbotService
     */
    private function handleSpecialQuestions($userMessage, $originalMessage)
    {
        // 1. Câu hỏi về sản phẩm
        $productResponse = $this->chatbotService->handleProductQuestions($originalMessage);
        if ($productResponse) {
            // Lưu context sản phẩm vào session
            $this->saveProductContext($originalMessage);
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($productResponse) : $productResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 2. Câu hỏi về giá & tồn kho
        $priceResponse = $this->chatbotService->handlePriceStockQuestions($originalMessage);
        if ($priceResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($priceResponse) : $priceResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 3. Câu hỏi về giao hàng
        $shippingResponse = $this->chatbotService->handleShippingQuestions($originalMessage);
        if ($shippingResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($shippingResponse) : $shippingResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 4. Câu hỏi về thanh toán
        $paymentResponse = $this->chatbotService->handlePaymentQuestions($originalMessage);
        if ($paymentResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($paymentResponse) : $paymentResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 5. Câu hỏi về đổi trả & bảo hành
        $returnResponse = $this->chatbotService->handleReturnWarrantyQuestions($originalMessage);
        if ($returnResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($returnResponse) : $returnResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 6. Câu hỏi về khuyến mãi
        $promotionResponse = $this->chatbotService->handlePromotionQuestions($originalMessage);
        if ($promotionResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($promotionResponse) : $promotionResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 7. Câu hỏi tương tác & tư vấn
        $consultationResponse = $this->chatbotService->handleConsultationQuestions($originalMessage);
        if ($consultationResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($consultationResponse) : $consultationResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 8. Câu hỏi về tài khoản & hỗ trợ
        $accountResponse = $this->chatbotService->handleAccountSupportQuestions($originalMessage);
        if ($accountResponse) {
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($accountResponse) : $accountResponse, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // Xử lý đăng ký/đăng nhập (giữ nguyên logic cũ)
        if (str_contains($userMessage, 'đăng ký')) {
            $responseMessage = "Để đăng ký tài khoản, vui lòng nhấn vào liên kết và điền thông tin. Nếu cần hỗ trợ, bạn cứ hỏi mình nhé!";
            return response()->json(
                [
                    'message' => isset($ensureUtf8) ? $ensureUtf8($responseMessage) : $responseMessage,
                    'links' => ['register' => route('register')]
                ],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // Xử lý danh mục sản phẩm (giữ nguyên logic cũ)
        if (str_contains($userMessage, 'danh mục') || str_contains($userMessage, 'sản phẩm')) {
            $categories = Category::pluck('name')->toArray();
            $responseMessage = "Các danh mục sản phẩm mà shop đang bán:<ul>";
            if (count($categories) > 0) {
                foreach ($categories as $category) {
                    $responseMessage .= "<li>{$category}</li>";
                }
            } else {
                $responseMessage .= "<li>Chưa có danh mục nào.</li>";
            }
            $responseMessage .= "</ul>Bạn muốn xem chi tiết sản phẩm nào không?";
            return response()->json(
                ['message' => isset($ensureUtf8) ? $ensureUtf8($responseMessage) : $responseMessage, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        return null;
    }

    /**
     * Lưu context sản phẩm vào session
     */
    private function saveProductContext($message)
    {
        $productKeywords = [
            'mắt kính' => ['mắt kính', 'kính'],
            'dây chuyền' => ['dây chuyền', 'vòng cổ'],
            'kẹp tóc' => ['kẹp tóc', 'kẹp'],
            'túi xách' => ['túi xách', 'túi'],
            'nhẫn' => ['nhẫn'],
            'bông tai' => ['bông tai', 'khuyên tai'],
            'vòng tay' => ['vòng tay'],
            'móng tay giả' => ['móng tay giả', 'nail']
        ];

        $message = strtolower($message);
        foreach ($productKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    // Tìm sản phẩm và lưu ID vào session
                    $products = Product::where('name', 'LIKE', "%{$type}%")
                        ->where('status', 1)
                        ->pluck('id')
                        ->toArray();
                    
                    if (!empty($products)) {
                        session(['recent_products' => $products]);
                    }
                    return;
                }
            }
        }
    }
}
