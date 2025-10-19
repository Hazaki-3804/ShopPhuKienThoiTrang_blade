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
use App\Services\ChatNLPService;
use App\Services\RetrievalService;
use App\Services\PromptBuilder;

class ChatbotController extends Controller
{
    protected $chatbotService;
    protected $nlp;
    protected $retrieval;
    protected $promptBuilder;
    public function __construct(
        ChatbotService $chatbotService,
        ChatNLPService $nlp,
        RetrievalService $retrieval,
        PromptBuilder $promptBuilder
    ) {
        $this->chatbotService = $chatbotService;
        $this->nlp = $nlp;
        $this->retrieval = $retrieval;
        $this->promptBuilder = $promptBuilder;
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

    /**
     * Paraphrase a base answer via Gemini to create variation while preserving facts.
     */
    private function variabilize(?string $base): ?string
    {
        if (!$base || !is_string($base)) return null;
        try {
            $apiKey = Config::get('services.gemini.api_key');
            if (!$apiKey) return null;

            $sanitize = function ($text) {
                if ($text === null) return '';
                if (!is_string($text)) $text = strval($text);
                if (function_exists('mb_detect_encoding')) {
                    $enc = mb_detect_encoding($text, 'UTF-8, ISO-8859-1, ISO-8859-15, Windows-1252, ASCII', true);
                    if ($enc && $enc !== 'UTF-8') $text = mb_convert_encoding($text, 'UTF-8', $enc);
                }
                if (class_exists('Normalizer')) $text = \Normalizer::normalize($text, \Normalizer::FORM_C);
                $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
                return $text;
            };

            $instruction = "Hãy diễn đạt lại đoạn văn sau bằng tiếng Việt tự nhiên, giữ NGUYÊN Ý và SỐ LIỆU, KHÔNG thêm thông tin mới.\nYêu cầu: ngắn gọn, thân thiện, có thể thay đổi cách mở đầu; tối đa 1 emoji; chỉ trả về câu trả lời, không markdown.";
            $content = "Đoạn văn cần diễn đạt lại:\n\n" . $sanitize($base);

            $payload = [
                'contents' => [[ 'parts' => [[ 'text' => $content ]]]],
                'systemInstruction' => [ 'parts' => [[ 'text' => $instruction ]] ],
                'generationConfig' => [
                    'maxOutputTokens' => 180,
                    'temperature' => 0.9,
                    'topP' => 0.9,
                    'topK' => 40,
                    'candidateCount' => 3,
                ],
            ];

            $jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($jsonBody === false) return null;

            $response = Http::withHeaders([
                'Content-Type' => 'application/json; charset=UTF-8',
                'Accept' => 'application/json; charset=UTF-8',
            ])->withBody($jsonBody, 'application/json')
              ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}");

            if ($response->failed()) return null;

            $data = $response->json();
            if ($data === null) $data = json_decode($response->body(), true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            $cands = $data['candidates'] ?? [];
            $valid = array_values(array_filter($cands, function ($c) {
                return isset($c['content']['parts'][0]['text']) && is_string($c['content']['parts'][0]['text']);
            }));
            if (empty($valid)) return null;
            $pick = $valid[array_rand($valid)];
            $text = $pick['content']['parts'][0]['text'];
            return $sanitize($text);
        } catch (\Throwable $e) {
            return null;
        }
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

            // RAG: NLP parse intent/entities
            $parsed = $this->nlp->parse($originalMessage);
            $intent = $parsed['intent'] ?? 'general';
            $entities = $parsed['entities'] ?? [];

            // RAG: Retrieve data from DB based on intent/entities
            $context = ['intent' => $intent, 'entities' => $entities];
            // Follow-up handling: if user refers to previous topic and no explicit query, reuse last query or recent products
            if (empty($entities['product_query']) && !empty($entities['follow_up'])) {
                $lastQuery = session('last_product_query');
                if ($lastQuery) {
                    $entities['product_query'] = $lastQuery;
                    $context['entities'] = $entities;
                } else {
                    $recentIds = session('recent_products', []);
                    if (!empty($recentIds)) {
                        $context['products'] = $this->retrieval->getProductsByIds($recentIds);
                    }
                }
            }
            if (!empty($entities['product_query'])) {
                $context['products'] = $this->retrieval->findProducts($entities['product_query'], 5);
                // Remember last product query for follow-up turns
                session(['last_product_query' => $entities['product_query']]);
            }
            // If we have a category hint, try category-based retrieval (when no explicit products yet)
            if (empty($context['products']) && !empty($entities['category_hint'])) {
                $context['products'] = $this->retrieval->findProductsByCategoryHint($entities['category_hint'], 5);
            }
            // If still no products, add suggested products as fallback to help LLM suggest alternatives
            if (empty($context['products']) || (is_countable($context['products']) && count($context['products']) === 0)) {
                $context['suggested_products'] = $this->retrieval->getTopProducts(5);
            }
            if ($intent === 'order_tracking') {
                $context['order'] = $this->retrieval->getOrder($entities['order_code'] ?? null);
            }
            if ($intent === 'shipping') {
                $context['shipping_fees'] = $this->retrieval->getShippingFees();
            }
            if ($intent === 'categories') {
                $context['categories'] = $this->retrieval->getCategories();
                // thêm link để dẫn người dùng xem danh mục
                $context['links'] = $this->retrieval->getShopLinks();
            }
            if (in_array($intent, ['auth', 'order_support'])) {
                $context['auth'] = $this->retrieval->getAuthInfo();
                $context['links'] = $this->retrieval->getShopLinks();
            }

            // Build prompt from context + history
            $history = session('chat_messages', []);
            $built = $this->promptBuilder->build($context, $history, $originalMessage);
            $prompt = $built['content'];
            $systemInstruction = $built['system'];
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
                    'maxOutputTokens' => 180,
                    'temperature' => 0.9,
                    'topP' => 0.9,
                    'topK' => 40,
                    'candidateCount' => 3,
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

            // Lấy phản hồi (chọn ngẫu nhiên 1 candidate hợp lệ)
            $responseData = $response->json();
            if ($responseData === null) {
                $raw = $response->body();
                $responseData = json_decode($raw, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            }
            $botMessage = null;
            $candidates = $responseData['candidates'] ?? [];
            if (is_array($candidates) && !empty($candidates)) {
                $valid = array_values(array_filter($candidates, function ($c) {
                    return isset($c['content']['parts'][0]['text']) && is_string($c['content']['parts'][0]['text']);
                }));
                if (!empty($valid)) {
                    $pick = $valid[array_rand($valid)];
                    $botMessage = $pick['content']['parts'][0]['text'];
                }
            }
            if (!$botMessage) {
                // Fallback qua ChatbotService khi không có phản hồi hợp lệ
                $fallback = $this->handleSpecialQuestions($userMessage, $originalMessage);
                if ($fallback) return $fallback;
                $botMessage = 'Mình chưa chắc về câu trả lời. Bạn có thể nói rõ hơn để mình giúp tốt hơn không?';
            }
            if (isset($ensureUtf8)) {
                $botMessage = $ensureUtf8($botMessage);
            }

            // Lưu lịch sử
            $messages = session('chat_messages', []);
            $messages[] = ['role' => 'user', 'content' => isset($ensureUtf8) ? $ensureUtf8($userMessage) : $userMessage];
            $messages[] = ['role' => 'bot', 'content' => $botMessage];
            session([
                'chat_messages' => $messages,
                // Đánh dấu đã chào để tránh chào lại
                'chatbot_greeted' => true,
                'greet_last_at' => now()->toDateTimeString(),
            ]);

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
            $msg = $this->variabilize($productResponse) ?: $productResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 2. Câu hỏi về giá & tồn kho
        $priceResponse = $this->chatbotService->handlePriceStockQuestions($originalMessage);
        if ($priceResponse) {
            $msg = $this->variabilize($priceResponse) ?: $priceResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 3. Câu hỏi về giao hàng
        $shippingResponse = $this->chatbotService->handleShippingQuestions($originalMessage);
        if ($shippingResponse) {
            $msg = $this->variabilize($shippingResponse) ?: $shippingResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 4. Câu hỏi về thanh toán
        $paymentResponse = $this->chatbotService->handlePaymentQuestions($originalMessage);
        if ($paymentResponse) {
            $msg = $this->variabilize($paymentResponse) ?: $paymentResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 5. Câu hỏi về đổi trả & bảo hành
        $returnResponse = $this->chatbotService->handleReturnWarrantyQuestions($originalMessage);
        if ($returnResponse) {
            $msg = $this->variabilize($returnResponse) ?: $returnResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 6. Câu hỏi về khuyến mãi
        $promotionResponse = $this->chatbotService->handlePromotionQuestions($originalMessage);
        if ($promotionResponse) {
            $msg = $this->variabilize($promotionResponse) ?: $promotionResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 7. Câu hỏi tương tác & tư vấn
        $consultationResponse = $this->chatbotService->handleConsultationQuestions($originalMessage);
        if ($consultationResponse) {
            $msg = $this->variabilize($consultationResponse) ?: $consultationResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // 8. Câu hỏi về tài khoản & hỗ trợ
        $accountResponse = $this->chatbotService->handleAccountSupportQuestions($originalMessage);
        if ($accountResponse) {
            $msg = $this->variabilize($accountResponse) ?: $accountResponse;
            return response()->json(
                ['message' => $msg, 'links' => []],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }

        // Xử lý đăng ký/đăng nhập (giữ nguyên logic cũ)
        if (str_contains($userMessage, 'đăng ký')) {
            $responseMessage = "Để đăng ký tài khoản, vui lòng nhấn vào liên kết và điền thông tin. Nếu cần hỗ trợ, bạn cứ hỏi mình nhé!";
            $msg = $this->variabilize($responseMessage) ?: $responseMessage;
            return response()->json(
                [
                    'message' => $msg,
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
            $msg = $this->variabilize($responseMessage) ?: $responseMessage;
            return response()->json(
                ['message' => $msg, 'links' => []],
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

    /**
     * Greet user once per session with personalized message and suggestions
     */
    public function greet(Request $request)
    {
        // Nếu đã có lịch sử chat thì không chào lại
        if (!empty(session('chat_messages', []))) {
            return response()->json([
                'message' => '',
                'links' => [],
                'skip' => true
            ]);
        }
        // Nếu đã chào trong session thì bỏ qua
        if (session()->has('chatbot_greeted') && session('chatbot_greeted') === true) {
            return response()->json([
                'message' => '',
                'links' => [],
                'skip' => true
            ]);
        }
        // Cooldown theo thời gian: nếu đã chào trong 6 giờ gần đây, bỏ qua
        $last = session('greet_last_at');
        if ($last) {
            try {
                $diffMinutes = now()->diffInMinutes(\Carbon\Carbon::parse($last));
                if ($diffMinutes < 360) { // 6 giờ
                    return response()->json([
                        'message' => '',
                        'links' => [],
                        'skip' => true
                    ]);
                }
            } catch (\Throwable $e) {
                // ignore parse errors
            }
        }

        session(['chatbot_greeted' => true, 'greet_last_at' => now()->toDateTimeString()]);

        $name = Auth::check() ? (optional(Auth::user())->name ?? 'bạn') : 'bạn';

        // Lời chào và gợi ý ngẫu nhiên
        $greetings = [
            "Chào {$name} 👋 Mình là Mia – trợ lý của Nàng Thơ. Mình có thể giúp gì cho bạn hôm nay?",
            "Xin chào {$name} 🌟 Mình có thể hỗ trợ bạn tìm phụ kiện phù hợp không?",
            "Hello {$name} 😊 Bạn đang tìm mẫu nào? Mình hỗ trợ ngay!",
        ];
        $allSuggestions = [
            'Xem kính chống tia UV',
            'Túi xách đang giảm giá',
            'Kiểm tra đơn hàng',
            'Tư vấn quà tặng theo ngân sách',
            'Xem phụ kiện đang hot',
            'Gợi ý sản phẩm theo sở thích'
        ];
        shuffle($greetings);
        shuffle($allSuggestions);
        $selected = array_slice($allSuggestions, 0, 3);
        $greet = $greetings[0] . "\n\nGợi ý: • " . implode(' • ', $selected);

        return response()->json([
            'message' => $greet,
            'links' => []
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
