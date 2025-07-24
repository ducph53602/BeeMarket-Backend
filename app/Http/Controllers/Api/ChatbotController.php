<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    /**
     * Xử lý yêu cầu hỏi đáp từ chatbot.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = $request->input('message');
        $geminiApiKey = env('GEMINI_API_KEY');
        $geminiModel = 'gemini-2.5-pro';

        if (empty($geminiApiKey)) {
            return response()->json(['error' => 'Gemini API Key chưa được cấu hình.'], 500);
        }

        $knowledgeBase = "Tôi là chatbot của cửa hàng BeeMarket. Đây là một số thông tin về cửa hàng:\n"
            . "- Chính sách vận chuyển: Miễn phí vận chuyển cho đơn hàng trên 500.000 VNĐ. Thời gian giao hàng 2-5 ngày làm việc.\n"
            . "- Chính sách đổi trả: Được đổi trả trong vòng 7 ngày nếu sản phẩm lỗi hoặc không đúng mô tả.\n"
            . "- Giờ mở cửa: 9h sáng - 9h tối hàng ngày.\n"
            . "- Địa chỉ: 123 Đường ABC, Quận XYZ, Thành phố HCM.\n"
            . "- Sản phẩm: Chúng tôi bán các loại đồ đã qua sử dụng.\n"
            . "Hãy trả lời các câu hỏi dựa trên thông tin trên. Nếu câu hỏi không liên quan, hãy nói rằng bạn chỉ có thể trả lời các câu hỏi về cửa hàng.";

        $userMessage = $request->input('message');

        $fullPrompt = $knowledgeBase . "\n\nNgười dùng hỏi: " . $userMessage . "\n\nTrả lời:";

        try {
            $payload = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt] 
                    ]
                ]
            ]
        ];

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/{$geminiModel}:generateContent?key={$geminiApiKey}", $payload);
            if ($response->successful()) {
                $responseData = $response->json();

                $geminiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? 'Xin lỗi, tôi không thể trả lời câu hỏi này lúc này.';

                return response()->json([
                    'message' => 'Câu trả lời từ AI',
                    'answer' => $geminiResponse,
                    'user_message' => $userMessage,
                ], 200);
            } else {
                Log::error('Lỗi khi gọi Gemini API: ' . $response->status() . ' - ' . $response->body());
                return response()->json([
                    'error' => 'Không thể kết nối với dịch vụ AI. Vui lòng thử lại sau.',
                    'details' => $response->json()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Lỗi trong ChatbotController: ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Đã xảy ra lỗi không mong muốn. Vui lòng thử lại sau.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
