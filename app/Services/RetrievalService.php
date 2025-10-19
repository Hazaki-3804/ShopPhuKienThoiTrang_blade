<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Discount;
use App\Models\Order;
use App\Models\ShippingFee;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Category;

class RetrievalService
{
    /**
     * Tìm sản phẩm liên quan theo truy vấn người dùng (V1: LIKE + mô tả)
     */
    public function findProducts(string $query, int $limit = 5)
    {
        $q = trim($query);
        if ($q === '') return collect();

        return Product::with(['product_images', 'reviews'])
            ->where('status', 1)
            ->where(function ($w) use ($q) {
                $w->where('name', 'LIKE', "%{$q}%")
                  ->orWhere('description', 'LIKE', "%{$q}%");
            })
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Lấy discount đang hoạt động cho list product ids
     */
    public function getActiveDiscountsFor(array $productIds)
    {
        if (empty($productIds)) return collect();
        $now = Carbon::now();
        return Discount::where('status', 1)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->whereHas('products', function ($q) use ($productIds) {
                $q->whereIn('product_id', $productIds);
            })
            ->get()
            ->keyBy('id');
    }

    /**
     * Lấy thông tin đơn hàng theo code hoặc đơn gần nhất của user
     */
    public function getOrder(?string $code = null)
    {
        if ($code) {
            return Order::where('id', $code)->first();
        }
        if (Auth::check()) {
            return Order::where('user_id', Auth::id())
                ->latest()
                ->first();
        }
        return null;
    }

    /**
     * Lấy bảng phí ship đang hoạt động
     */
    public function getShippingFees()
    {
        return ShippingFee::where('status', true)->orderBy('priority')->get();
    }

    public function getProductsByIds(array $ids)
    {
        if (empty($ids)) return collect();
        return Product::with(['product_images', 'reviews'])
            ->whereIn('id', $ids)
            ->where('status', 1)
            ->get();
    }

    /**
     * Tìm sản phẩm theo gợi ý danh mục (category hint)
     */
    public function findProductsByCategoryHint(string $hint, int $limit = 5)
    {
        $h = trim($hint);
        if ($h === '') return collect();

        // Tìm category bằng name/slug gần đúng
        $cats = Category::where('name', 'LIKE', "%{$h}%")
            ->orWhere('slug', 'LIKE', "%{$h}%")
            ->pluck('id');
        if ($cats->isEmpty()) return collect();

        return Product::with(['product_images', 'reviews'])
            ->where('status', 1)
            ->whereIn('category_id', $cats)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Lấy một số sản phẩm nổi bật/gần đây để gợi ý
     */
    public function getTopProducts(int $limit = 5)
    {
        return Product::with(['product_images', 'reviews'])
            ->where('status', 1)
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Lấy danh mục sản phẩm đang hoạt động
     */
    public function getCategories()
    {
        return Category::select('id', 'name')->orderBy('name')->get();
    }

    /**
     * Trả về thông tin auth cơ bản và link liên quan
     */
    public function getAuthInfo(): array
    {
        return [
            'authenticated' => Auth::check(),
            'user_name' => Auth::check() ? (Auth::user()->name ?? null) : null,
            'links' => $this->getShopLinks(),
        ];
    }

    /**
     * Link quan trọng trong shop (routes)
     */
    public function getShopLinks(): array
    {
        return [
            'home' => route('home'),
            'shop' => route('shop.index'),
            'cart' => route('cart.index'),
            'checkout' => route('checkout.index'),
            'login' => route('login'),
            'register' => route('register'),
        ];
    }
}
