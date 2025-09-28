<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function index()
    {
        // After login: auto-complete pending add-to-cart
        if (auth()->check() && session()->has('pending_add_to_cart')) {
            $pending = session('pending_add_to_cart');
            session()->forget('pending_add_to_cart');
            if (!empty($pending['product_id']) && !empty($pending['qty'])) {
                $cart = session('cart', []);
                $pid = (string)$pending['product_id'];
                $qty = max(1, (int)$pending['qty']);
                $cart[$pid] = ['product_id' => (int)$pid, 'qty' => ($cart[$pid]['qty'] ?? 0) + $qty];
                session(['cart' => $cart]);
                return redirect()->route('cart.index')->with('status', 'Đã thêm vào giỏ sau khi đăng nhập');
            }
        }
        // Đề xuất: mỗi lần load, lấy ngẫu nhiên 1 sản phẩm cho mỗi danh mục (tối đa 6)
        $categoryIds = Product::where('status', 1)
            ->distinct()
            ->pluck('category_id')
            ->shuffle()
            ->take(6);

        $products = collect();
        foreach ($categoryIds as $cid) {
            $p = Product::with(['category','product_images'])
                ->where('status', 1)
                ->where('category_id', $cid)
                ->inRandomOrder()
                ->first();
            if ($p) { $products->push($p); }
        }

        // Nếu chưa đủ 6, bổ sung ngẫu nhiên các sản phẩm còn lại
        if ($products->count() < 6) {
            $excludeIds = $products->pluck('id');
            $fill = Product::with(['category','product_images'])
                ->where('status', 1)
                ->whereNotIn('id', $excludeIds)
                ->inRandomOrder()
                ->take(6 - $products->count())
                ->get();
            $products = $products->concat($fill);
        }

        // Ảnh nền cho banner New Arrivals (sản phẩm mới nhất)
        $newProduct = Product::with('product_images')
            ->where('status', 1)
            ->latest('id')
            ->first();
        $newBannerImage = optional(optional($newProduct)->product_images->first())->image_url;

        // Ảnh nền cho banner Best Sellers (tồn kho nhiều nhất)
        $bestProduct = Product::with('product_images')
            ->where('status', 1)
            ->orderByDesc('stock')
            ->first();
        $bestBannerImage = optional(optional($bestProduct)->product_images->first())->image_url;

        // Chuẩn hóa URL nếu là đường dẫn tương đối
        $normalize = function ($url) {
            if (!$url) return null;
            return Str::startsWith($url, ['http://','https://','/']) ? $url : asset($url);
        };
        $newBannerImage = $normalize($newBannerImage);
        $bestBannerImage = $normalize($bestBannerImage);

        return view('home', compact('products','newBannerImage','bestBannerImage'));
    }
}
