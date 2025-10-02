<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\Product;
use App\Models\Category;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        // Validate request
        $request->validate([
            'message' => 'required|string|max:500',
        ]);

        $userMessage = strtolower(trim($request->input('message')));

        try {
            // Lấy API key từ config
            $apiKey = Config::get('services.gemini.api_key');
            if (!$apiKey) {
                Log::error('Gemini API key không được cấu hình');
                return response()->json(['error' => 'Khóa API không được cấu hình'], 500);
            }

            // Từ khóa liên quan
            $relevantKeywords = [
                'xin chào',
                'phụ kiện',
                'thời trang',
                'sản phẩm',
                'danh mục',
                'giá',
                'mua',
                'đặt hàng',
                'đăng ký',
                'đăng nhập',
                'tài khoản',
                'giỏ hàng',
                'khuyến mãi',
                'chính sách',
                'giao hàng',
                'trả hàng',
                'liên hệ',
            ];

            // Kiểm tra câu hỏi có liên quan không
            $isRelevant = false;
            foreach ($relevantKeywords as $keyword) {
                if (str_contains($userMessage, $keyword)) {
                    $isRelevant = true;
                    break;
                }
            }

            // Xử lý yêu cầu đặc biệt
            // if (str_contains($userMessage, 'danh mục') || str_contains($userMessage, 'sản phẩm')) {
            //     $categories = Category::pluck('name')->toArray();
            //     $products = Product::take(5)->get(['name', 'price'])->toArray();
            //     $categoryList = count($categories) > 0 ? implode(', ', $categories) : 'Chưa có danh mục nào.';
            //     $productList = count($products) > 0 ? collect($products)->map(function ($product) {
            //         return "{$product['name']} - Giá: " . number_format($product['price'], 0, ',', '.') . " VNĐ";
            //     })->implode('\n') : 'Chưa có sản phẩm nào.';

            //     $responseMessage = "Danh mục sản phẩm: $categoryList\nSản phẩm nổi bật:\n$productList\nBạn muốn xem chi tiết sản phẩm nào không?";
            //     return response()->json(['message' => $responseMessage, 'links' => []]);
            // }

            if (str_contains($userMessage, 'đăng ký') || str_contains($userMessage, 'tài khoản')) {
                $responseMessage = "Để đăng ký tài khoản, vui lòng nhấn vào liên kết và điền thông tin. Nếu cần hỗ trợ, bạn cứ hỏi mình nhé!";
                return response()->json([
                    'message' => $responseMessage,
                    'links' => ['register' => route('register')] // Truyền URL động
                ]);
            }

            if (str_contains($userMessage, 'đăng nhập')) {
                if (auth()->check()) {
                    return response()->json([
                        'message' => 'Bạn đã đăng nhập rồi. Bạn cần giúp gì thêm không?',
                        'links' => []
                    ]);
                }
                $responseMessage = "Để đăng nhập, vui lòng nhấn vào liên kết và nhập email/mật khẩu. Nếu quên mật khẩu, bạn có thể nhấn 'Quên mật khẩu' để đặt lại.";
                return response()->json([
                    'message' => $responseMessage,
                    'links' => ['login' => route('login')] // Truyền URL động
                ]);
            }

            // Câu hỏi ngoài lề
            if (!$isRelevant) {
                return response()->json([
                    'message' => 'Xin lỗi, mình chỉ trả lời các câu hỏi liên quan đến phụ kiện thời trang, sản phẩm, hoặc tài khoản. Bạn muốn hỏi về sản phẩm hay dịch vụ gì?',
                    'links' => []
                ]);
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
            - Sản phẩm chính: **Túi xách da, ví, kính mát, trang sức tối giản (Minimalist Jewelry)**.
            - Chính sách giao hàng: **Miễn phí vận chuyển cho đơn hàng trên 500.000 VNĐ**.
            - Chính sách đổi/trả: **Đổi trả trong 7 ngày** nếu sản phẩm lỗi.
            ";
            // Gửi yêu cầu đến Gemini API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 150,
                    'temperature' => 0.7,
                    'systemInstruction' => $systemInstruction,
                ],
            ]);

            // Kiểm tra lỗi API
            if ($response->failed()) {
                Log::error('Yêu cầu Gemini API thất bại', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'user_message' => $userMessage,
                ]);
                return response()->json(['error' => 'Không thể nhận phản hồi từ Gemini API', 'links' => []], 500);
            }

            // Lấy phản hồi
            $responseData = $response->json();
            $botMessage = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Không có phản hồi từ Gemini API';

            // Lưu lịch sử
            $messages = session('chat_messages', []);
            $messages[] = ['role' => 'user', 'content' => $userMessage];
            $messages[] = ['role' => 'bot', 'content' => $botMessage];
            session(['chat_messages' => $messages]);

            return response()->json(['message' => $botMessage, 'links' => []]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi giao tiếp với Gemini API', [
                'exception' => $e->getMessage(),
                'user_message' => $userMessage,
            ]);
            return response()->json(['error' => 'Có lỗi xảy ra khi xử lý yêu cầu của bạn', 'links' => []], 500);
        }
    }
}
