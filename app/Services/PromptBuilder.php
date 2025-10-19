<?php

namespace App\Services;

use Illuminate\Support\Str;

class PromptBuilder
{
    /**
     * Xây dựng system instruction và prompt nội dung dựa trên context + history.
     */
    public function build(array $context, array $history, string $userMessage): array
    {
        $intent = $context['intent'] ?? 'general';
        $entities = $context['entities'] ?? [];

        $contextLines = [];

        // Products block
        if (!empty($context['products'])) {
            $contextLines[] = "[PRODUCTS]";
            foreach ($context['products'] as $p) {
                $line = sprintf(
                    "- id:%d | name:%s | price:%s | stock:%d",
                    $p->id,
                    $p->name,
                    number_format($p->price, 0, ',', '.') . 'đ',
                    (int)$p->stock
                );
                $contextLines[] = $line;
            }
        }

        // Order block
        if (!empty($context['order'])) {
            $o = $context['order'];
            $contextLines[] = "[ORDER]";
            $contextLines[] = sprintf(
                "- id:#%s | status:%s | total:%s | created_at:%s",
                $o->id,
                method_exists($o, 'getStatusTextAttribute') ? $o->status_text : ($o->status ?? ''),
                number_format($o->total_price, 0, ',', '.') . 'đ',
                optional($o->created_at)->format('d/m/Y H:i')
            );
        }

        // Shipping fee block
        if (!empty($context['shipping_fees'])) {
            $contextLines[] = "[SHIPPING_FEES]";
            foreach ($context['shipping_fees'] as $fee) {
                $label = method_exists($fee, 'getAreaTypeLabel') ? $fee->getAreaTypeLabel() : $fee->area_type;
                $price = $fee->is_free_shipping ? 'Miễn phí' : (number_format($fee->base_fee, 0, ',', '.') . 'đ');
                $contextLines[] = "- {$label}: {$price}";
            }
        }

        // Categories block
        if (!empty($context['categories'])) {
            $contextLines[] = "[CATEGORIES]";
            foreach ($context['categories'] as $c) {
                $contextLines[] = "- {$c->name}";
            }
        }

        // Links/Auth block
        if (!empty($context['links'])) {
            $contextLines[] = "[LINKS]";
            foreach ($context['links'] as $k => $v) {
                $contextLines[] = "- {$k}: {$v}";
            }
        }
        if (!empty($context['auth'])) {
            $contextLines[] = "[AUTH] authenticated=" . (!empty($context['auth']['authenticated']) ? 'true' : 'false') .
                (isset($context['auth']['user_name']) && $context['auth']['user_name'] ? (" user_name=" . $context['auth']['user_name']) : '');
        }

        // Intent + entities
        $contextLines[] = "[INTENT] {$intent}";
        if (!empty($entities)) {
            $contextLines[] = "[ENTITIES] " . json_encode($entities, JSON_UNESCAPED_UNICODE);
        }

        // History (limit turns)
        $historyTurns = array_slice($history, -10);
        $historyText = '';
        foreach ($historyTurns as $turn) {
            $role = $turn['role'] ?? 'user';
            $content = $turn['content'] ?? '';
            $historyText .= strtoupper($role) . ": " . $content . "\n";
        }

        $systemInstruction = <<<SYS
Bạn là trợ lý AI cho cửa hàng phụ kiện thời trang Nàng Thơ. Trả lời bằng tiếng Việt, tự nhiên, ngắn gọn, thân thiện, dựa trên dữ liệu trong [PRODUCTS], [ORDER], [SHIPPING_FEES], [CATEGORIES], [LINKS], [AUTH] nếu có. Khi không chắc, hãy hỏi lại, không bịa.

Nguyên tắc:
- Luôn ưu tiên thông tin từ context.
- Chỉ nêu giá/tồn kho theo context.
- Có thể gợi ý thêm sản phẩm tương tự trong [PRODUCTS].
- Nếu người dùng hỏi về danh mục, dùng [CATEGORIES].
- Nếu hỏi về đăng nhập/đăng ký/hỗ trợ đặt hàng, dựa vào [LINKS] và [AUTH] để hướng dẫn đúng luồng.
- Không trả lời ngoài phạm vi mua sắm/phụ kiện.
SYS;

        $content = "[CONTEXT]\n" . implode("\n", $contextLines) . "\n\n" .
                   ($historyText ? ("[HISTORY]\n" . $historyText . "\n") : '') .
                   "[USER]\n" . $userMessage;

        return [
            'system' => $systemInstruction,
            'content' => $content,
        ];
    }
}
