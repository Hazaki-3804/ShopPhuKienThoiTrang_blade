<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use App\Services\ChatbotService;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(ChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Xử lý tin nhắn từ người dùng
     * Luồng: Detect Intent -> Generate Scenario Response -> Enhance with Gemini -> Return
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $userMessage = trim($request->input('message'));
        $userMessageLower = mb_strtolower($userMessage, 'UTF-8');

        try {
            // Bước 1: Phát hiện Intent từ tin nhắn người dùng
            $intent = $this->detectIntent($userMessageLower);
            
            Log::info('Chatbot Intent Detected', [
                'message' => $userMessage,
                'intent' => $intent
            ]);

            // Bước 2: Tạo câu trả lời tình huống dựa trên intent
            $scenarioResponse = $this->generateScenarioResponse($intent, $userMessage, $userMessageLower);

            if (!$scenarioResponse) {
                return response()->json([
                    'message' => 'Xin lỗi, mình chưa hiểu câu hỏi của bạn. Bạn có thể hỏi về sản phẩm, giá cả, giao hàng, thanh toán, hoặc các dịch vụ khác nhé! 😊',
                    'links' => []
                ], 200, [], JSON_UNESCAPED_UNICODE);
            }

            // Bước 3: Đưa qua Gemini API để diễn đạt tự nhiên hơn
            $enhancedResponse = $this->enhanceWithGemini($scenarioResponse, $userMessage, $intent);

            // Lưu lịch sử chat
            $this->saveChatHistory($userMessage, $enhancedResponse);

            return response()->json([
                'message' => $enhancedResponse,
                'links' => []
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            Log::error('Chatbot Error', [
                'message' => $userMessage,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Có lỗi xảy ra khi xử lý yêu cầu của bạn. Vui lòng thử lại!',
                'links' => []
            ], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Phát hiện Intent từ tin nhắn người dùng
     */
    private function detectIntent($messageLower)
    {
        // Intent: Sản phẩm
        $productKeywords = ['sản phẩm', 'mắt kính', 'kính', 'dây chuyền', 'kẹp tóc', 'túi xách', 'nhẫn', 'bông tai', 'vòng tay', 'móng tay giả', 'xem', 'có', 'bán'];
        foreach ($productKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                return 'product';
            }
        }

        // Intent: Danh mục
        if (str_contains($messageLower, 'danh mục') || str_contains($messageLower, 'loại sản phẩm')) {
            return 'category';
        }

        // Intent: Giá cả & Tồn kho
        if (str_contains($messageLower, 'giá') || str_contains($messageLower, 'bao nhiêu') || str_contains($messageLower, 'còn hàng') || str_contains($messageLower, 'tồn kho')) {
            return 'price_stock';
        }

        // Intent: Phí ship
        if (str_contains($messageLower, 'ship') || str_contains($messageLower, 'giao hàng') || str_contains($messageLower, 'phí vận chuyển') || str_contains($messageLower, 'phí giao hàng')) {
            return 'shipping';
        }

        // Intent: Thanh toán
        if (str_contains($messageLower, 'thanh toán') || str_contains($messageLower, 'payment') || str_contains($messageLower, 'momo') || str_contains($messageLower, 'cod') || str_contains($messageLower, 'payos') || str_contains($messageLower, 'vnpay')|| str_contains($messageLower, 'sepay')) {
            return 'payment';
        }

        // Intent: Đổi trả & Bảo hành
        if (str_contains($messageLower, 'đổi') || str_contains($messageLower, 'trả') || str_contains($messageLower, 'bảo hành') || str_contains($messageLower, 'warranty')) {
            return 'return_warranty';
        }

        // Intent: Khuyến mãi
        if (str_contains($messageLower, 'khuyến mãi') || str_contains($messageLower, 'giảm giá') || str_contains($messageLower, 'sale') || str_contains($messageLower, 'voucher')) {
            return 'promotion';
        }

        // Intent: Tư vấn
        if (str_contains($messageLower, 'tư vấn') || str_contains($messageLower, 'gợi ý') || str_contains($messageLower, 'phù hợp') || str_contains($messageLower, 'tặng')) {
            return 'consultation';
        }

        // Intent: Tài khoản & Hỗ trợ
        if (str_contains($messageLower, 'đăng ký') || str_contains($messageLower, 'đăng nhập') || str_contains($messageLower, 'tài khoản') || str_contains($messageLower, 'quên mật khẩu') || str_contains($messageLower, 'hỗ trợ') || str_contains($messageLower, 'liên hệ')) {
            return 'account_support';
        }

        // Intent: Đơn hàng
        if (str_contains($messageLower, 'đơn hàng') || str_contains($messageLower, 'order') || preg_match('/#[A-Z0-9]+/', $messageLower)) {
            return 'order_tracking';
        }

        // Intent: Chào hỏi
        if (str_contains($messageLower, 'xin chào') || str_contains($messageLower, 'chào') || str_contains($messageLower, 'hello') || str_contains($messageLower, 'hi')) {
            return 'greeting';
        }

        // Default: General
        return 'general';
    }

    /**
     * Tạo câu trả lời tình huống dựa trên intent
     */
    private function generateScenarioResponse($intent, $originalMessage, $messageLower)
    {
        switch ($intent) {
            case 'product':
                return $this->chatbotService->handleProductQuestions($originalMessage);

            case 'category':
                return $this->chatbotService->handleCategoryQuestions($originalMessage);

            case 'price_stock':
                return $this->chatbotService->handlePriceStockQuestions($originalMessage);

            case 'shipping':
                return $this->chatbotService->handleShippingQuestions($originalMessage);

            case 'payment':
                return $this->chatbotService->handlePaymentQuestions($originalMessage);

            case 'return_warranty':
                return $this->chatbotService->handleReturnWarrantyQuestions($originalMessage);

            case 'promotion':
                return $this->chatbotService->handlePromotionQuestions($originalMessage);

            case 'consultation':
                return $this->chatbotService->handleConsultationQuestions($originalMessage);

            case 'account_support':
                return $this->chatbotService->handleAccountSupportQuestions($originalMessage);

            case 'order_tracking':
                return $this->chatbotService->handleOrderTrackingQuestions($originalMessage);

            case 'greeting':
                return "Xin chào! Mình là trợ lý ảo của shop Nàng Thơ. Mình có thể giúp bạn về:\n- Thông tin sản phẩm\n- Giá cả và tồn kho\n- Phí giao hàng\n- Thanh toán\n- Khuyến mãi\n- Tư vấn sản phẩm\nBạn cần hỗ trợ gì ạ?";

            default:
                return null;
        }
    }

    /**
     * Đưa câu trả lời qua Gemini API để diễn đạt tự nhiên và hay hơn
     */
    private function enhanceWithGemini($scenarioResponse, $userMessage, $intent)
    {
        // Nếu không có scenario response, trả về null
        if (!$scenarioResponse) {
            return null;
        }

        try {
            $apiKey = Config::get('services.gemini.api_key');
            if (!$apiKey) {
                Log::warning('Gemini API key not configured, returning scenario response as-is');
                return $scenarioResponse;
            }

            // Tạo system instruction để Gemini biết nhiệm vụ
            $systemInstruction = <<<SYS
            Bạn là trợ lý AI thân thiện của shop phụ kiện thời trang Nàng Thơ.

            NHIỆM VỤ:
            - Nhận câu trả lời có sẵn (SCENARIO_RESPONSE) và diễn đạt lại cho tự nhiên, hay và thân thiện nhưng chuyên nghiệp
            - GIỮ NGUYÊN tất cả thông tin quan trọng: tên sản phẩm, giá, số lượng, địa chỉ, số điện thoại, link, v.v.
            - KHÔNG thêm thông tin không có trong SCENARIO_RESPONSE
            - KHÔNG bịa đặt hoặc thay đổi dữ liệu
            - Giữ nguyên format HTML nếu có (như <b>, <ul>, <li>, <img>)
            - Trả lời ngắn gọn, súc tích, dễ hiểu
            - Sử dụng emoji phù hợp để tạo cảm giác thân thiện
            - Không sử dụng emoji
            - Không chào khi trả lời

            NGUYÊN TẮC:
            - Nếu SCENARIO_RESPONSE có danh sách sản phẩm -> GIỮ NGUYÊN tất cả
            - Nếu có giá tiền -> GIỮ NGUYÊN số tiền chính xác
            - Nếu có thông tin liên hệ -> GIỮ NGUYÊN
            - Chỉ cải thiện cách diễn đạt, không thay đổi nội dung
            - Chỉ trả lời bằng tiếng Việt
            - Nếu có nhiều mục thì phải liệt kê từng mục thành từng dòng
            SYS;

            // Tạo prompt
            $prompt = <<<PROMPT
            [USER_QUESTION]
            {$userMessage}

            [INTENT]
            {$intent}

            [SCENARIO_RESPONSE]
            {$scenarioResponse}

            Hãy diễn đạt lại SCENARIO_RESPONSE cho tự nhiên và hay hơn, nhưng GIỮ NGUYÊN tất cả thông tin quan trọng.
            PROMPT;

            // Chuẩn bị payload
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'systemInstruction' => [
                    'parts' => [
                        ['text' => $systemInstruction]
                    ]
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 800,
                    'temperature' => 0.7,
                    'topP' => 0.9,
                    'topK' => 40,
                ],
            ];

            // Gọi Gemini API
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 30,
            ])->withHeaders([
                'Content-Type' => 'application/json',
            ])->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}",
                $payload
            );

            if ($response->failed()) {
                Log::warning('Gemini API failed, returning scenario response', [
                    'status' => $response->status()
                ]);
                return $scenarioResponse;
            }

            $responseData = $response->json();
            $candidates = $responseData['candidates'] ?? [];

            if (!empty($candidates) && isset($candidates[0]['content']['parts'][0]['text'])) {
                $enhancedText = $candidates[0]['content']['parts'][0]['text'];
                return trim($enhancedText);
            }

            // Fallback nếu không có response hợp lệ
            return $scenarioResponse;

        } catch (\Exception $e) {
            Log::error('Gemini Enhancement Error', [
                'error' => $e->getMessage()
            ]);
            // Trả về scenario response gốc nếu có lỗi
            return $scenarioResponse;
        }
    }

    /**
     * Lưu lịch sử chat vào session
     */
    private function saveChatHistory($userMessage, $botResponse)
    {
        $messages = session('chat_messages', []);
        $messages[] = ['role' => 'user', 'content' => $userMessage];
        $messages[] = ['role' => 'bot', 'content' => $botResponse];
        
        // Giới hạn lịch sử 20 tin nhắn gần nhất
        if (count($messages) > 20) {
            $messages = array_slice($messages, -20);
        }
        
        session(['chat_messages' => $messages]);
    }

    /**
     * Chào người dùng khi mở chatbot
     */
    public function greet(Request $request)
    {
        // // Nếu đã có lịch sử chat thì không chào lại
        // if (!empty(session('chat_messages', []))) {
        //     return response()->json([
        //         'message' => '',
        //         'links' => [],
        //         'skip' => true
        //     ]);
        // }

        // // Nếu đã chào trong session thì bỏ qua
        // if (session()->has('chatbot_greeted') && session('chatbot_greeted') === true) {
        //     return response()->json([
        //         'message' => '',
        //         'links' => [],
        //         'skip' => true
        //     ]);
        // }

        // Cooldown 6 giờ
        $last = session('greet_last_at');
        if ($last) {
            try {
                $diffMinutes = now()->diffInMinutes(\Carbon\Carbon::parse($last));
                if ($diffMinutes < 360) {
                    return response()->json([
                        'message' => '',
                        'links' => [],
                        'skip' => true
                    ]);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        session(['chatbot_greeted' => true, 'greet_last_at' => now()->toDateTimeString()]);

        $name = Auth::check() ? (optional(Auth::user())->name ?? 'bạn') : 'bạn';

        $greetings = [
            "Chào {$name} 👋 Mình là Mia – trợ lý của Nàng Thơ. Mình có thể giúp gì cho bạn hôm nay?",
            "Xin chào {$name} 🌟 Mình có thể hỗ trợ bạn tìm phụ kiện phù hợp không?",
            "Hello {$name} 😊 Bạn đang tìm mẫu nào? Mình hỗ trợ ngay!",
        ];

        $allSuggestions = [
            'Xem kính chống tia UV',
            'Túi xách đang giảm giá',
            'Kiểm tra đơn hàng',
            'Tư vấn quà tặng',
            'Xem phụ kiện hot',
        ];

        shuffle($greetings);
        shuffle($allSuggestions);
        $selected = array_slice($allSuggestions, 0, 3);
        $greet = $greetings[0] . "\n\nGợi ý: • " . implode(' • ', $selected);

        return response()->json([
            'message' => $greet,
            'links' => []
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
