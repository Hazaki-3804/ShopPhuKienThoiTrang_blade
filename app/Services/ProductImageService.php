<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;

class ProductImageService
{
    /**
     * Lấy hình ảnh chính của sản phẩm
     */
    public function getMainImage($productId)
    {
        $image = ProductImage::where('product_id', $productId)
            ->where('type', 'main')
            ->first();
            
        if (!$image) {
            $image = ProductImage::where('product_id', $productId)->first();
        }
        
        return $image ? asset($image->image_url) : asset('img/no-image.png');
    }

    /**
     * Lấy tất cả hình ảnh của sản phẩm
     */
    public function getAllImages($productId)
    {
        return ProductImage::where('product_id', $productId)
            ->get()
            ->map(function($image) {
                return [
                    'url' => asset($image->image_url),
                    'type' => $image->type
                ];
            });
    }

    /**
     * Tạo HTML hiển thị hình ảnh sản phẩm cho chatbot
     */
    public function generateImageHtml($productId, $productName)
    {
        $images = $this->getAllImages($productId);
        
        if ($images->isEmpty()) {
            return "<p>📷 Hiện tại chưa có hình ảnh cho sản phẩm này.</p>";
        }

        $html = "<div class='product-images'>";
        $html .= "<h4>🖼️ Hình ảnh {$productName}:</h4>";
        
        foreach ($images->take(3) as $index => $image) {
            $html .= "<div class='image-item' style='margin: 10px 0;'>";
            $html .= "<img src='{$image['url']}' alt='{$productName}' style='max-width: 200px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);' />";
            $html .= "<p style='font-size: 12px; color: #666; margin: 5px 0;'>" . ucfirst($image['type']) . "</p>";
            $html .= "</div>";
        }
        
        if ($images->count() > 3) {
            $html .= "<p style='color: #007bff;'>... và " . ($images->count() - 3) . " hình khác</p>";
        }
        
        $html .= "</div>";
        
        return $html;
    }

    /**
     * Tạo link xem chi tiết sản phẩm
     */
    public function generateProductLink($productId)
    {
        return route('shop.show', $productId);
    }
}
